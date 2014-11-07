<?
define('MYCA_ROOT', dirname(__FILE__) . '/..');
define('COMMON_DIR', MYCA_ROOT . '/common');

if(!function_exists('common')) {
  function common($path) {
    require_once(COMMON_DIR . '/' . $path . '.php');
  }
}

define('AUTH_TOKEN', 'd960a3def5904ac1b2cb284d9c5e8b8e');

class MycaBackend
{
  var $stdin;
  var $stdout;
  var $stderr;

  var $uses = Array('Backend', 'Request', 'RequestStatus', 'RequestData', 
      'Reseller', 'DialinNumber', 'DiffLog', 'Account', 'Room', 'User', 'Pin', 
      'SpectelReservation', 'SpectelClient', 
      'Salesperson', 
      'WebinterpointAccount', 'SmartCloudAccount', 'Contact', 'FreeTrial', 
      'Proposal');

  var $created_rooms    = Array();
  var $uncommitted_pins = Array();

  //-----------------------------------------------------------------------------
  //
  // Initialization
  //
  //-----------------------------------------------------------------------------
  function __construct($args)
  {
    $this->args        = $args;
    $this->start_time  = microtime(true);
    $this->stdin       = fopen('php://stdin', 'r');
    $this->stdout      = fopen('php://stdout', 'w');
    $this->stderr      = fopen('php://stderr', 'w');

    $this->do_throttle = false;

    $this->initCake();

    common('config/bootstrap');
    if(MAINTENANCE_MODE) {
      die('maintenance');
    }
    
    //echo "\n>>> Initializing the Models";
    $this->initModels();

    if(!BACKEND_DEBUG) {
      error_reporting(E_ERROR | E_PARSE | E_NOTICE);
      set_error_handler(Array($this, 'error'));
    }

    common('vendors/phpmailer/class.phpmailer');

    $this->statuses = Array();
    foreach($this->RequestStatus->findAll() as $s)
      $this->statuses[$s['RequestStatus']['id']] = $s['RequestStatus'];

    register_shutdown_function(Array($this, 'end'));
  }

  // CakePHP Init - "borrowed" from acl.php of CakePHP source distribution
  protected function initCake()
  {
    define('DS', DIRECTORY_SEPARATOR);
    if(function_exists('ini_set')) {
      ini_set('display_errors', '1');
      //ini_set('error_reporting', '7');
      ini_set('max_execution_time',0);
    }

    $app  = 'app';
    $core = null;
    $root = MYCA_ROOT;

    if(strlen($app) && $app[0] == DS) {
      $cnt = substr_count($root, DS);
      $app = str_repeat('..' . DS, $cnt) . $app;
    }

    define('ROOT', $root.DS);

    define('APP_DIR', $app);
    define('DEBUG', 1);
    define('CAKE_CORE_INCLUDE_PATH', ROOT);
    //define('DATASOURCE', $dataSource);
    define('APP_PATH', ROOT . DS . APP_DIR . DS);
    define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);

    if (function_exists('ini_set')) {
      ini_set('include_path', ini_get('include_path').
	      PATH_SEPARATOR.CAKE_CORE_INCLUDE_PATH.DS.
	      PATH_SEPARATOR.CORE_PATH.DS.
	      PATH_SEPARATOR.ROOT.DS.APP_DIR.DS.
	      PATH_SEPARATOR.APP_DIR.DS.
	      PATH_SEPARATOR.APP_PATH);
    }

    require('cake'.DS.'basics.php');
    require('cake'.DS.'config'.DS.'paths.php');
    require(CONFIGS.'core.php');
    uses('object', 'configure', 'neat_array', 'session', 'security', 'inflector', 'model'.DS.'connection_manager',
	 'model'.DS.'datasources'.DS.'dbo_source', 'model'.DS.'model');
    require(APP.'app_model.php');
  }

  protected function initModels()
  {
    foreach($this->uses as $m) {
      //echo "\n>>> Initializing $m";
      loadModel($m);
      $this->{$m} = new $m();
    }
  }

  //-----------------------------------------------------------------------------
  //
  // Utilities
  //
  //-----------------------------------------------------------------------------

  function lock($lock_file)
  {
    $rv = false;

    if(!file_exists($lock_file) || (microtime() - filectime($lock_file)) > BACKEND_RUN_WINDOW)
      $rv = touch($lock_file);

    return $rv;
  }

  function unlock($lock_file)
  {
    return @unlink($lock_file);
  }

  function log()
  {
    $args = func_get_args();
    $this->systemLog(call_user_func_array('sprintf', $args));
  }

  function debug()
  {
    if(BACKEND_DEBUG) {
      $args = func_get_args();
      call_user_func_array(Array($this, 'log'), $args);
    }
  }

  function error($errno, $str, $file, $line)
  {
    $errors = array ( E_ERROR   => 'Error',
		      E_PARSE   => 'Parse Error',
		      E_NOTICE  => 'Notice');

    if(array_key_exists($errno, $errors))
      $this->log('%s: "%s" in file %s at line %d', $errors[$errno], $str, $file, $line);
  }

  function systemLog($msg)
  {
    if(BACKEND_DEBUG) {
      $rv = fwrite($this->stderr, sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $msg));
    } else {
      $rv = $this->Backend->save(Array('created'    => now(),
				       'message'    => $msg));
      $this->Backend->id = null;
    }

    return $rv;
  }

  function diffLog($user_id, $entity, $op, $object_id, $new=null, $old=null)
  {
    return $this->DiffLog->log(BACKEND_HOST, $entity, $op, $object_id, BACKEND_ADDR, $user_id, $new, $old);
  }

  function notify($addr, $subject, $msg)
  {
    $mailer = new PHPMailer();

    $mailer->From     = MAILER_FROM;
    $mailer->FromName = MAILER_FROMNAME;
    $mailer->Host     = MAILER_HOST;
    $mailer->Mailer   = 'smtp';
    $mailer->Subject  = '[MyCA Backend] ' . $subject;

    $mailer->AddAddress($addr);

    $mailer->Body = $msg;
    $mailer->IsHTML(false);

    return $mailer->Send();
  }

  // FIXME move to callback in myca
  function emailRooms($user, $rooms)
  {
    $mailer = new PHPMailer();

    $mailer->From     = MAILER_FROM;
    $mailer->FromName = MAILER_FROMNAME;
    $mailer->Host     = MAILER_HOST;
    $mailer->Mailer   = 'smtp';

    $mailer->AddAddress($user['User']['email'], $user['User']['name']);

    if($user['User']['level_type'] == 'infinite' || $user['User']['level_type'] == 'reseller') {
      $args = Array('rooms' => $rooms, 'recipient' => $user['User']['name']);
      $mailer->Subject = '[MyCA] Room Creation';
      $layout = VIEWS . DS . 'layouts' . DS . EMAIL_LAYOUT . '.thtml';
      $view   = VIEWS . DS . 'rooms' . DS . 'email' . DS . 'create.thtml';      

      $mailer->Body = render_template($layout, $view, $args);
      $mailer->IsHTML(true);
      $mailer->Send();
    }
  }

  function emailPins($pins)
  {
    $mailer = new PHPMailer();
    
    $mailer->From     = MAILER_FROM;
    $mailer->FromName = MAILER_FROMNAME;
    $mailer->Host     = MAILER_HOST;
    $mailer->Mailer   = 'smtp';
    $mailer->Subject  = '[MyCA] PIN Creation';

    if(!BACKEND_DEBUG)
      $mailer->AddAddress(CLIENTCARE_SUPPORT_EMAIL, 'Client Care');
    else 
      $mailer->AddAddress('mvolpe@infiniteconferencing.com', 'Marc');

    $layout = VIEWS . DS . 'layouts' . DS . EMAIL_LAYOUT . '.thtml';
    $view   = VIEWS . DS . 'pins' . DS . 'email' . DS . 'pin.thtml';      
    $mailer->Body = render_template($layout, $view, Array('pins' => $pins));

    $mailer->IsHTML(true);
    $mailer->Send();
  }

  //-----------------------------------------------------------------------------
  //
  // Main Run Loop
  //
  //-----------------------------------------------------------------------------
  function run()
  {
    if($this->lock(BACKEND_LOCK)) {
      //$this->log('Run started');

      $criteria = Array('Request.status' => REQSTATUS_APPROVED,
            'Request.manual' => 0,
            'OR' => Array('Request.effective_date' => '<= ' . date('Y-m-d H:i:s'), 
                    'effective_date' => null),
            'Request.type' => Array( REQTYPE_ACCOUNT_CREATION,
							   REQTYPE_ACCOUNT_UPDATE,
							   REQTYPE_ACCOUNT_STATUS_CHANGE,
							   REQTYPE_ACCOUNT_REASSIGN,
							   REQTYPE_CONTACT_CREATION,
							   REQTYPE_CONTACT_UPDATE,
							   REQTYPE_CONTACT_STATUS_CHANGE,
							   REQTYPE_RATE_CHANGE,
							   REQTYPE_ROOM_UPDATE,
							   REQTYPE_ROOM_STATUS_CHANGE,
							   REQTYPE_ROOM_MOVE,
							   REQTYPE_ROOM_CREATE,
							   REQTYPE_ROOM_PULL,
							   REQTYPE_CODE_MIGRATION,
							   REQTYPE_PIN_CREATION,
							   REQTYPE_PIN_DELETION,
							   REQTYPE_PIN_UPDATE,
							   REQTYPE_RESELLER_CREATE,
							   REQTYPE_RESELLER_UPDATE, 
							   REQTYPE_SPECTEL_MOVE,
							   REQTYPE_WEBINTERPOINT_CREATION, 
							   REQTYPE_WEBINTERPOINT_DELETION,
							   REQTYPE_SMARTCLOUD_CREATION,
							   REQTYPE_SMARTCLOUD_DELETION,
							   REQTYPE_EMAIL,
                               REQTYPE_ROOM_SYNC,
                               REQTYPE_WEB_ROOM_CREATION,
                               REQTYPE_CONVERSION_ATTEMPT )//,
            //'Request.id' => Array(
//1649376, 1649377, 1649378, 1649379, 1649380
            
            
            );
     if(BACKEND_DEBUG)
       $criteria['Request.acctgrpid'] = 'LIKE ICT%';
      
      $handled = 0;
      while((time() < $this->start_time + (BACKEND_RUN_WINDOW-30)) && 
	        $request = $this->Request->find($criteria, null, 'Request.priority ASC, RequestType.priority ASC, NEWID() ASC')) {
        $handler = $this->Request->handler($request);
       // echo "\n>>> Handler : $handler";

	if(method_exists($this, $handler)) {
	  $rv = call_user_func(Array($this, $handler), $request);
	  $request['Request']['tries']++;

	  $this->Request->id = null;  // Make sure current request pk is wiped out
	  if($rv != $request['Request']['status']) {
	    if($rv == REQSTATUS_FAILED && $request['Request']['tries'] < BACKEND_MAX_TRIES) {

	      $this->log('Request ID %d FAILED but will be RETRIED for attempt %d', $request['Request']['id'], $request['Request']['tries']+1);
	      $this->Request->save($request); // update tries count

	    } else {
	      $this->log('Updating request ID %d from %s to %s', $request['Request']['id'], $request['RequestStatus']['name'], $this->statuses[$rv]['name']);
	      
	      $request['Request']['status'] = $rv;
	      $request['RequestStatus']     = $this->statuses[$rv];

	      $this->Request->updateStatus($request);
	      $handled++;
	    }	 
	  }

	  if($this->do_throttle) { 
	    sleep(BACKEND_THROTTLE_SLEEP);
	    $this->do_throttle = false;
	  }
	 
	} else {
	  $this->log('Handler not found: %s', $handler);
	}
      }
      
      if($handled) {
	$stop_time = microtime(true);
	$this->log('Handled %d requests in %0.2f seconds', $handled, $stop_time-$this->start_time);
      }

      foreach($this->created_rooms as $group)
	if($group['rooms'])
	  $this->emailRooms($group['user'], $group['rooms']);

      if($this->uncommitted_pins)
	$this->emailPins($this->uncommitted_pins);

    } else {
      $this->log('Could not obtain lock');
      $this->notify('mvolpe@infiniteconferencing.com', 'MyCA Backend Error', sprintf('Could not obtain lock: %s', BACKEND_LOCK));
    }
  }

  function end()
  {
    $this->unlock(BACKEND_LOCK);
  }

  //-----------------------------------------------------------------------------
  //
  // These are the handlers for when a request is approved/completed
  //   NB: These functions perform their action and return the request status
  //
  //-----------------------------------------------------------------------------

  private function approved_account_creation($request)
  {
    if($account = $this->Account->read(null, $request['Request']['acctgrpid'])) {
      $rv = $this->Account->syncAccount($account, false) ? REQSTATUS_COMPLETED : REQSTATUS_FAILED;

      if($rv == REQSTATUS_FAILED)
	$this->log('Could not sync account: ' . $this->Account->error_msg);

    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_account_update($request)
  {
    if($account = $this->Account->read(null, $request['Request']['acctgrpid'])) {
      $rv = $this->Account->syncAccount($account, true) ? REQSTATUS_COMPLETED : REQSTATUS_FAILED;

      if($rv == REQSTATUS_FAILED)
	$this->log('Could not sync account: ' . $this->Account->error_msg);

    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_account_reassignment($request)
  {
    if($account = $this->Account->read(null, $request['Request']['acctgrpid'])) {
      $data = Array('salespid' => $request['RequestData']['to']);

      if($new_account = $this->Account->update($account, $data)) {
	$this->diffLog($request['Request']['creator'], 'Account', DIFFLOG_OP_UPDATE, $account['Account']['acctgrpid'], $new_account['Account'], $account['Account']);
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_contact_creation($request)
  {
    if($contact = $this->Contact->read(null, $request['RequestData']['contact_id'])) {
      $rv = $this->Contact->syncContact($contact, false) ? REQSTATUS_COMPLETED : REQSTATUS_FAILED;

      if($rv == REQSTATUS_FAILED)
	$this->log('Could not sync contact: ' . $this->Contact->error_msg);

    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_contact_update($request)
  {
    if($contact = $this->Contact->read(null, $request['RequestData']['contact_id'])) {
      $rv = $this->Contact->syncContact($contact, true) ? REQSTATUS_COMPLETED : REQSTATUS_FAILED;

      if($rv == REQSTATUS_FAILED)
	$this->log('Could not sync contact: ' . $this->Contact->error_msg);

    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_contact_status_change($request)
  {
    if($contact = $this->Contact->read(null, $request['RequestData']['contact_id'])) {

      if($this->Contact->changeStatus($contact, $request['RequestData']['status'], $request['RequestData']['reason'])) {
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
	$this->log('Contact status change failed');
      }

    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_room_update($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 

      $bridge_data = $this->Room->pull($request['Request']['acctgrpid'], $request['Request']['accountid'], false);
      foreach($bridge_data as $k => $v)
	$room['Room'][$k] = $v;

      if($new_room = $this->Room->doUpdateRoom($room, $request['RequestData'])) {

	  // if a callback URL was passed, POST to the callback URL with the accountid
	  if(!empty($request['RequestData']['callback_url'])) {
	    
	    $post_data         = Array( 'request' => $request['Request']['id'] );
	    $callback_response = http_post($request['RequestData']['callback_url'], $post_data, Array('AUTH_TOKEN: ' . AUTH_TOKEN));

	    if($callback_response != 'ok')	      
	      $this->log('Room update callback "%s" failed for room %s', 			 
			 $request['RequestData']['callback_url'], $room['Room']['accountid']);
	  }


	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $new_room['Room'], $room['Room']);
	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('Room update failed: ' . $this->Room->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_rate_change($request)
  {
    if($request['Request']['accountid']) {
      if($room = $this->Room->read(null, $request['Request']['accountid'])) {
	$data = Array('servicerate' => $request['RequestData']['servicerate'], 'canada' => $request['RequestData']['canada']);

	if($new_room = $this->Room->update($room, $data)) {
	  $this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $new_room['Room'], $room['Room']);
	  $rv = REQSTATUS_COMPLETED;
	} else {
	  $rv = REQSTATUS_FAILED;
	}
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      if($account = $this->Account->read(null, $request['Request']['acctgrpid'])) { 
	$data = Array('default_servicerate' => $request['RequestData']['servicerate']);
      
	if(isset($request['RequestData']['default_canada']))
	  $data['default_canada'] = $request['RequestData']['default_canada'];

	if($new_account = $this->Account->update($account, $data)) {
	  $this->diffLog($request['Request']['creator'], 'Account', DIFFLOG_OP_UPDATE, $account['Account']['acctgrpid'], $new_account['Account'], $account['Account']);
	  $rv = REQSTATUS_COMPLETED;
	} else {
	  $rv = REQSTATUS_FAILED;
	}
      } else {
	$rv = REQSTATUS_FAILED;
      }
    }

    return $rv;
  }

  private function approved_room_move($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 
      $data = Array('acctgrpid' => $request['RequestData']['acctgrpid'], 'servicerate' => $request['RequestData']['servicerate']);

      if($new_room = $this->Room->update($room, $data)) {
	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $new_room['Room'], $room['Room']);
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_room_creation($request)
  {
    if($user = $this->User->read(null, $request['Request']['creator'])) {

      $account     = $this->Account->read(null, $request['RequestData']['acctgrpid']);
      $salesperson = $this->Salesperson->read(null, $account['Account']['salespid']);      
      $reseller    = $this->Reseller->read(null, $salesperson['Salesperson']['resellerid']);

      if($account && $salesperson && $reseller) { 

	if(empty($request['RequestData']['confname'])) 
	  $request['RequestData']['confname'] = $this->Room->getConfName($request['RequestData']['company'], $request['RequestData']['contact']);

	$request['RequestData']['resellerid'] = $salesperson['Salesperson']['resellerid'];

	// FIXME this is fugly, pass reseller where needed (at least i moved it here where it is appropriate)
	if(!empty($reseller['Reseller']['rdesc']))
	  $request['RequestData']['reseller_company'] = $reseller['Reseller']['rdesc'] . '_' . $reseller['Reseller']['racctprefix'];
	else
	  $request['RequestData']['reseller_company'] = $reseller['Reseller']['name'] . '_' . $reseller['Reseller']['racctprefix'];

	$request['RequestData']['reseller_contact'] = $reseller['Reseller']['contact'];
	$request['RequestData']['reseller_email']   = $reseller['Reseller']['remail'];

	if($room = $this->Room->doCreateRoom($request, $request['RequestData'])) {

	  if($request['RequestData']['bridgeid'] == OCI_BRIDGEID)
	    $this->do_throttle = true;

	  // Dont send out email for migrations
	  if($request['Request']['type'] != REQTYPE_CODE_MIGRATION && 
             empty($request['Request']['callback_url'])) {

        if(!BACKEND_DEBUG) {
	    if(!isset($this->created_rooms[$user['User']['id']]))
	      $this->created_rooms[$user['User']['id']] = Array('user' => $user, 'rooms' => Array());

	    $this->created_rooms[$user['User']['id']]['rooms'][] = $room;
        }
	  }

	  $this->log('Created room: %s', $room['Room']['accountid']);

	  $this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_CREATE, $room['Room']['accountid'], $room['Room']);

          // Set billing method
          $billing_method_id = isset($request['RequestData']['billing_method_id']) ? 
            $request['RequestData']['billing_method_id'] : null;

          $billing_frequency_id = isset($request['RequestData']['billing_frequency_id']) ? 
            $request['RequestData']['billing_frequency_id'] : null;

          $flat_rate_charge = isset($request['RequestData']['flat_rate_charge']) ? 
            $request['RequestData']['flat_rate_charge'] : null;
          
          if($this->Room->setBillingMethod($room, $billing_method_id, $billing_frequency_id, $flat_rate_charge) != 0) {
            $this->log('Could not set billing method for %s', $room['Room']['accountid']);
          }
	 
	  // Associate the room with the specified contact or the corp contact of the account
	  if(!empty($request['RequestData']['contact_id'])) {	    
	    if($contact = $this->Contact->read(null, $request['RequestData']['contact_id']))
	      if($this->Contact->associateRoom($contact, $room) != 0) {
		$this->log('Could not associate room %s with contact %d', $room['Room']['accountid'], $contact['Contact']['id']);
	      }
	  }

	  // Update request with accountid
	  $this->Request->id = null;
	  $request['Request']['accountid'] = $room['Room']['accountid'];
	  $this->Request->save($request);	

	  // create a migration request, but only if not running in debug mode.
      if (!BACKEND_DEBUG) {
	  if($request['Request']['type'] != REQTYPE_CODE_MIGRATION && !$request['RequestData']['isopassist'] && !$request['RequestData']['isevent']) {
	    foreach($this->Room->migration_targets[$room['Room']['bridgeid']] as $i)	   
	      $this->Request->saveRequest(REQTYPE_CODE_MIGRATION, $request['Request']['creator'], $request['Request']['acctgrpid'], 
					  $room['Room']['accountid'], Array('bridgeid' => $i), REQSTATUS_APPROVED);
	  }
      }

	  // create web room request
      if($request['Request']['type'] != REQTYPE_CODE_MIGRATION 
          && !empty($request['RequestData']['productid']) 
          && $request['RequestData']['productid'] != AUDIO_ONLY) {
 
          //populate generic parameters first
          $web_room_data = Array('audio_accountid'      => $room['Room']['accountid'],
                                 'acctgrpid'            => $room['Room']['acctgrpid'],
                                 'maximumconnections'   => $room['Room']['maximumconnections'],
                                 'contact'              => $room['Room']['contact'],
                                 'billingcode'          => $room['Room']['billingcode'],
                                 'email'                => $room['Room']['email'],
                                 'company'              => $room['Room']['company'],
                                 'billing_method_id'    => $billing_method_id,
                                 'billing_frequency_id' => $billing_frequency_id,
                                 'flat_rate_charge'     => $flat_rate_charge,
                                 'note1'                => $room['Room']['note1'],
                                 'note2'                => $room['Room']['note2'],
                                 'note3'                => $room['Room']['note3'],
                                 'note4'                => $room['Room']['note4']);

	      if(!empty($request['RequestData']['contact_id'])) 
              $web_room_data['contact_id'] = $request['RequestData']['contact_id'];

          switch($request['RequestData']['productid'])
          {
          case AUDIO_WEBINTERPOINT:
              $web_room_data['cec'] = $room['Room']['cec'];
              $web_room_data['pec'] = $room['Room']['pec'];
              $web_room_data['bridgeid'] = WEBINTERPOINT_BRIDGEID; 
              break;
//01-13-2013 Added SmartCloud Audio			  
		 case AUDIO_SMARTCLOUD:
              $web_room_data['cec'] = $room['Room']['cec'];
              $web_room_data['pec'] = $room['Room']['pec'];
              $web_room_data['bridgeid'] = SMARTCLOUD_BRIDGEID; 
              break;  
			  
          case AUDIO_WEBEX:
              $web_room_data['bridgeid'] = WEBEX_BRIDGEID; 
              break;
          case AUDIO_LIVE_MEETING:
              $web_room_data['bridgeid'] = LIVE_MEETING_BRIDGEID; 
              break;
          default:
              $web_room_data['cec'] = $room['Room']['cec'];
              $web_room_data['pec'] = $room['Room']['pec'];
              $web_room_data['bridgeid'] = WEBINTERPOINT_BRIDGEID; 
              break;
	      }
          if($this->Room->createWebRoom($user, $web_room_data) != 0)
              $this->log('Web Room Creation Request failed for room %s', $room['Room']['accountid']);
      }          

	  // if a callback URL was passed, POST to the callback URL with the accountid
      if(!BACKEND_DEBUG){
	  if(!empty($request['RequestData']['callback_url'])) {	    
	    $post_data         = Array( 'request' => $request['Request']['id'] );
	    $callback_response = http_post($request['RequestData']['callback_url'], $post_data, Array('AUTH_TOKEN: ' . AUTH_TOKEN));

	    if($callback_response != 'ok')	      
	      $this->log('Room creation callback "%s" failed for room %s', 			 
			 $request['RequestData']['callback_url'], $room['Room']['accountid']);
	  }
      }

	  $rv = REQSTATUS_COMPLETED;
	} else {
	  $this->log('Room creation failed: ' . $this->Room->error_msg);
	  $this->RequestData->appendData($request, Array('reason' => $this->Room->error_msg));
	  $rv = REQSTATUS_FAILED;

	}
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }

  private function approved_code_migration($request)
  {
    $this->Salesperson->recursive = $this->Reseller->recursive = 0;

    $room        = $this->Room->read(null, $request['Request']['accountid']);
    $account     = $this->Account->read(null, $room['Room']['acctgrpid']);
    $salesperson = $this->Salesperson->read(null, $account['Account']['salespid']);      
    $reseller    = $this->Reseller->read(null, $salesperson['Salesperson']['resellerid']);
    
    if($room && $account && $salesperson && $reseller) {

      if(empty($request['RequestData']['bridgeid'])) {

	$migrated_bridges = Array($room['Room']['bridgeid']);
	foreach($this->Room->findMigrations($room, true) as $m)
	  $migrated_bridges[] = $m['Room']['bridgeid'];
 
	$found_open_migration = false;
	foreach($this->Room->migration_targets[$room['Room']['bridgeid']] as $i) {
	  if(!in_array($i, $migrated_bridges)) {
	    $this->RequestData->appendData($request, Array('bridgeid' => $i));
	    $request['RequestData']['bridgeid'] = $i;
	    $found_open_migration = true;
	    break;
	  }
	}
	
	if(!$found_open_migration)
	  return REQSTATUS_COMPLETED;
      }

      foreach($room['Room'] as $k => $v)
	if($k != 'bridgeid' && $k != 'tstamp' && empty($request['RequestData'][$k]))
	  $request['RequestData'][$k] = $v;

      foreach($this->Room->migrateSettings($request['RequestData']['bridgeid'], $room['Room']['bridgeid'], $request['RequestData']) as $k => $v)
	$request['RequestData'][$k] = $v;

      if(empty($request['RequestData']['dialinNoid'])) {
	$default_dialin = $this->DialinNumber->getDefault($reseller['Reseller']['resellerid'], Symbols::$bridges[$request['RequestData']['bridgeid']]);

	if($default_dialin)
	  $request['RequestData']['dialinNoid'] = $default_dialin['id'];
      }
                 
      if(!empty($room['Contact']))
	$request['RequestData']['contact_id'] = $room['Contact'][0]['id'];

      unset($request['RequestData']['tstamp']);

      return $this->approved_room_creation($request);
     
    } else {
      $this->log(sprintf('Could not pull information for code migration: %s:%s', $request['Request']['acctgrpid'], $request['Request']['accountid']));
      return REQSTATUS_FAILED;
    }
  }

  private function approved_account_status_change($request)
  {
    if($account = $this->Account->read(null, $request['Request']['acctgrpid'])) {
      if($this->Account->changeStatus($account, $request['RequestData']['status'], $request['RequestData']['reason'])) {
      	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
	$this->log('Account status change failed');      
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_room_status_change($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) {
      
      $reason = !empty($request['RequestData']['status_change_reason']) ? $request['RequestData']['status_change_reason'] : null;

      if($this->Room->changeStatus($room, $request['RequestData']['roomstat'], $reason)) {

	if($room['Room']['bridgeid'] == OCI_BRIDGEID)
	  $this->do_throttle = true;

	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('Room status change failed: ' . $this->Room->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_room_pull($request)
  {
    if($this->Account->read(null, $request['Request']['acctgrpid'])) {
      if(!$this->Room->read(null, $request['Request']['accountid'])) {
	if($room = $this->Room->pull($request['Request']['acctgrpid'], $request['Request']['accountid'])) { 

	  if($this->Room->save($room, false)) { 
	    $this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_CREATE, $room['accountid'], $room);
	    $rv = REQSTATUS_COMPLETED;
	  } else {
	    $rv = REQSTATUS_FAILED;
	  }

	} else {
	  $this->log('Could not pull room: ' . $request['Request']['accountid']);	  	  
	  $rv = REQSTATUS_FAILED;
	}
      } else {
	$this->log('Confirmation number already found in account table: ' . $request['Request']['accountid']);	  
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $this->log('Could not find account: ' . $request['Request']['acctgrpid']);
      $rv = REQSTATUS_FAILED;
    }
	
    return $rv;
  }

  private function approved_reseller_create($request)
  {
    $this->Reseller->id = null;
    if($this->Reseller->save($request['RequestData'])) {
      $reseller_id = $this->Reseller->getLastInsertId();
      $this->diffLog($request['Request']['creator'], 'Reseller', DIFFLOG_OP_CREATE, $reseller_id, $request['RequestData']);
      $rv = REQSTATUS_COMPLETED;
    } else {
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }  

  private function approved_reseller_update($request)
  {
    if($reseller = $this->Reseller->read(null, $request['RequestData']['resellerid'])) {
      if($new_reseller = $this->Reseller->update($reseller, $request['RequestData'])) {
	$this->diffLog($request['Request']['creator'], 'Reseller', DIFFLOG_OP_UPDATE, $reseller['Reseller']['resellerid'], 
		       $new_reseller['Reseller'], $reseller['Reseller']);

	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }

  private function approved_pin_creation($request)
  {
    if($pin = $this->Pin->createPin($request['RequestData'])) {

      if($pin['Pin']['bridgeid'] == OCI_BRIDGEID) {
	$this->uncommitted_pins[] = $pin;
	$this->do_throttle = true;
      }

      $this->diffLog($request['Request']['creator'], 'Pin', DIFFLOG_OP_CREATE, $pin['Pin']['id'], $pin['Pin']);
      $rv = REQSTATUS_COMPLETED;

    } else {
      $this->log('PIN creation failed: ' . $this->Pin->error_msg);
      $rv = REQSTATUS_FAILED;
    }
   
    return $rv;
  }

  private function approved_pin_update($request) 
  {
    if($pin = $this->Pin->read(null, $request['RequestData']['id'])) {
      if($new_pin = $this->Pin->updatePin($pin, $request['RequestData'])) {

	if($pin['Pin']['bridgeid'] == OCI_BRIDGEID) {
	  $this->uncommitted_pins[] = $pin;
	  $this->do_throttle = true;
	}

	$this->diffLog($request['Request']['creator'], 'Pin', DIFFLOG_OP_UPDATE, $pin['Pin']['id'], $new_pin['Pin'], $pin['Pin']);
	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('PIN update failed: ' . $this->Pin->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_pin_deletion($request)
  {
    if($pin = $this->Pin->read(null, $request['RequestData']['id'])) {
      if($new_pin = $this->Pin->deletePin($pin)) {
	$this->diffLog($request['Request']['creator'], 'Pin', DIFFLOG_OP_UPDATE, $pin['Pin']['id'], $new_pin['Pin'], $pin['Pin']);

	if($pin['Pin']['bridgeid'] == OCI_BRIDGEID)
	  $this->do_throttle = true;

	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('PIN deletion failed: ' . $this->Pin->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

/*  private function approved_spectel_move($request) 
  {
    $reservation = $this->SpectelReservation->read(null, $request['RequestData']['ReservationRef']);
    $client      = $this->SpectelClient->read(null, $request['RequestData']['ClientRef']);

    if($reservation && $client) {
      if($new_reservation = $this->SpectelReservation->move($reservation, $client)) { 
	$this->diffLog($request['Request']['creator'], 'SpectelReservation', DIFFLOG_OP_UPDATE, 
		       $reservation['SpectelReservation']['ReservationRef'], 
		       $new_reservation['SpectelReservation'], $reservation['SpectelReservation']);
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  } */

  private function approved_webinterpoint_creation($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 
      if($this->WebinterpointAccount->createAccount($room)) {

	$data = Array('webinterpoint' => 1);
	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $data, $room['Room']);

	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('WebDialogs Room Creation Failed: ' . $this->WebinterpointAccount->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $this->log('Could not create Webinterpoint Account for nonexistant room ' . $request['Request']['accountid']);
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }
  
 //01-13-2014 Added for SmartCloud Room creation
  
  private function approved_smartcloud_creation($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 
      if($this->SmartCloudAccount->createAccount($room)) {

	$data = Array('smartcloud' => 1);
	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $data, $room['Room']);

	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('SmartCloud Room Creation Failed: ' . $this->SmartCloudAccount->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $this->log('Could not create SmartCloud Account for nonexistant room ' . $request['Request']['accountid']);
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }
 
  private function approved_webinterpoint_deletion($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 
      if($this->WebinterpointAccount->deleteAccount($room)) {

	$data = Array('webinterpoint' => 0);
	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $data, $room['Room']);

	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('WebDialogs Room Deletion Failed: ' . $this->WebinterpointAccount->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $this->log('Could not delete Weinterpoint Account for nonexistant room ' . $request['Request']['accountid']);
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }
  //01-13-2014 Added for SmartCloud Room deletion
  private function approved_smartcloud_deletion($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 
      if($this->SmartCloudAccount->deleteAccount($room)) {

	$data = Array('smartcloud' => 0);
	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $data, $room['Room']);

	$rv = REQSTATUS_COMPLETED;
      } else {
	$this->log('WebDialogs Room Deletion Failed: ' . $this->SmartCloudAccount->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $this->log('Could not delete SmartCloud Account for nonexistant room ' . $request['Request']['accountid']);
      $rv = REQSTATUS_FAILED;
    }
    
    return $rv;
  }
   
  private function approved_room_sync($request)
  {
    if($room = $this->Room->read(null, $request['Request']['accountid'])) { 
      if($new_room = $this->Room->sync($room)) { 

	$this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], 
		       $new_room['Room'], $room['Room']);

	$rv = REQSTATUS_COMPLETED;      

      } else {
	$this->log('Room sync failed, ' . $this->Room->error_msg);
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $this->log('Room sync failed, %s not found in account table',  $request['Request']['accountid']);
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_email($request)
  {
    $args = Array( 'subject' => $request['RequestData']['subject'] );

    if(empty($request['RequestData']['from']))
      $args['from'] = sprintf('%s<%s>', MAILER_FROMNAME, MAILER_FROM);
    
    if(!empty($request['RequestData']['body']))
      $args['body'] = $request['RequestData']['body'];

    if(!empty($request['RequestData']['body-path']))
      $args['body-path'] = $request['RequestData']['body-path'];
    
    foreach(Array('to', 'cc', 'bcc') as $f) {
      if(!empty($request['RequestData'][$f])) {
	$args[$f] = preg_split('/\s*[,;]\s*/', $request['RequestData'][$f], -1, PREG_SPLIT_NO_EMPTY);	
      }
    }

    if(!empty($request['RequestData']['attach']))
      $args['attach'] = preg_split('/[,;]/', $request['RequestData']['attach'], -1, PREG_SPLIT_NO_EMPTY);	
    
    $output      = null;
    $exit_status = spawn('email'.DS.'send_email.pl', $args, $output, SCRIPT_ROOT);    

    if($exit_status === 0) {
      $rv = REQSTATUS_COMPLETED;      
    } else {
      $this->log('Email sending failed: ' . $output[2]);
      $rv = REQSTATUS_FAILED;
    }

    return $rv;
  }

  private function approved_web_room_creation($request)
  {
    if($user = $this->User->read(null, $request['Request']['creator'])) {
       $account     = $this->Account->read(null, $request['RequestData']['acctgrpid']);
       $salesperson = $this->Salesperson->read(null, $account['Account']['salespid']);      
       $reseller    = $this->Reseller->read(null, $salesperson['Salesperson']['resellerid']);



       if($account && $salesperson && $reseller) {
	      if($room = $this->Room->doCreateWebRoom($request['RequestData'], BACKEND_DEBUG)) {
	         if(empty($request['Request']['callback_url'])) {
                if(!BACKEND_DEBUG) {
	            if(!isset($this->created_rooms[$user['User']['id']]))
	               $this->created_rooms[$user['User']['id']] = Array('user' => $user, 'rooms' => Array());
	            $this->created_rooms[$user['User']['id']]['rooms'][] = $room;
                }
             }
	         $this->log('Created web room: %s', $room['Room']['accountid']);
	         $this->diffLog($request['Request']['creator'], 'Room', DIFFLOG_OP_CREATE, $room['Room']['accountid'], $room['Room']);
            
             // Set billing method
             $billing_method_id = isset($request['RequestData']['billing_method_id']) ? 
                $request['RequestData']['billing_method_id'] : null;

             $billing_frequency_id = isset($request['RequestData']['billing_frequency_id']) ? 
                $request['RequestData']['billing_frequency_id'] : null;

             $flat_rate_charge = isset($request['RequestData']['flat_rate_charge']) ? 
                $request['RequestData']['flat_rate_charge'] : null;
          
             if($this->Room->setBillingMethod($room, $billing_method_id, $billing_frequency_id, $flat_rate_charge) != 0) {
                $this->log('Could not set billing method for %s', $room['Room']['accountid']);
             }
            
	         // Associate the room with the specified contact or the corp contact of the account
	         if(!empty($request['RequestData']['contact_id'])) {	    
	            if($contact = $this->Contact->read(null, $request['RequestData']['contact_id']))
	               if($this->Contact->associateRoom($contact, $room, BACKEND_DEBUG) != 0) {
		              $this->log('Could not associate room %s with contact %d', $room['Room']['accountid'], $contact['Contact']['id']);
	               }
	         }

	         // Update request with accountid
	         $this->Request->id = null;
	         $request['Request']['accountid'] = $room['Room']['accountid'];
	         $this->Request->save($request);	

	         // if a callback URL was passed, POST to the callback URL with the accountid
             if(!BACKEND_DEBUG){
	         if(!empty($request['RequestData']['callback_url'])) {	    
	            $post_data         = Array( 'request' => $request['Request']['id'] );
	            $callback_response = http_post($request['RequestData']['callback_url'], $post_data, Array('AUTH_TOKEN: ' . AUTH_TOKEN));

	            if($callback_response != 'ok')	      
	               $this->log('Room creation callback "%s" failed for room %s', 			 
			          $request['RequestData']['callback_url'], $room['Room']['accountid']);
	         }
             }

	         $rv = REQSTATUS_COMPLETED;
	      } else {
	         $this->log('Room creation failed: ' . $this->Room->error_msg);
	         $this->RequestData->appendData($request, Array('reason' => $this->Room->error_msg));
	         $rv = REQSTATUS_FAILED;
          }
       } else {
          $rv = REQSTATUS_FAILED;
       }
    } else {
      $rv = REQSTATUS_FAILED;
    }
    return $rv;
  }

  private function approved_conversion_attempt($request)
  {
    //Send out two emails. One to the free trial user and another to the
    // the assigned salesperson. The goal is to get the user into a paid
    // account.

    $free_trial = $this->FreeTrial->findByAudioAccountid($request['Request']['accountid']);
    if(empty($free_trial['Proposal']['converted_on']) && $free_trial['Room']['room_stat'] == ROOMSTAT_ACTIVE){
      //Build out the conversion email
      $convert_mailer = new PHPMailer();
      $convert_mailer->From     = MAILER_FROM;
      $convert_mailer->FromName = MAILER_FROMNAME;
      $convert_mailer->Host     = MAILER_HOST;
      $convert_mailer->Mailer   = 'smtp';
      $convert_mailer->AddAddress($free_trial['FreeTrial']['email']);
      $subject = $free_trial['FreeTrial']['first_name']. 
        $free_trial['FreeTrial']['last_name'].', your free trial is about to end!';
      $convert_mailer->Subject  = $subject;
      //TODO: Need the body
      $layout = VIEWS . DS . 'layouts' . DS . CONVERSION_EMAIL_LAYOUT . '.thtml';
      $view   = VIEWS . DS . 'free_trials' . DS . 'email' . DS . 'conversion_attempt.thtml';      
      $convert_mailer->Body = render_template($layout, $view, Array('free_trial' => $free_trial));
      $convert_mailer->IsHTML(True);

      //Build out the salesperson email
      $sales_mailer = new PHPMailer();
      $sales_mailer->From     = MAILER_FROM;
      $sales_mailer->FromName = MAILER_FROMNAME;
      $sales_mailer->Host     = MAILER_HOST;
      $sales_mailer->Mailer   = 'smtp';
      //$sales_mailer->AddAddess($free_trial['Proposal']['Salesperson']['email']);
      $sales_mailer->AddAddress($free_trial['FreeTrial']['Proposal']['Salesperson']['email']);
      $sales_mailer->Subject  = '[Free Trial] : Conversion attempt sent';
      $layout = VIEWS . DS . 'layouts' . DS . EMAIL_LAYOUT . '.thtml';
      $view   = VIEWS . DS . 'free_trials' . DS . 'email' . DS . 'sales_conversion.thtml';      
      $sales_mailer->Body = render_template($layout, $view, Array('free_trial' => $free_trial));
      $sales_mailer->IsHTML(true);

      if($convert_mailer->Send() && $sales_mailer->Send()){
	    $this->log('Conversion Attempt sent.');
        return REQSTATUS_COMPLETED;
      } else {
	    $this->log('Conversion Attempt failed: Email not sent.');
        return REQSTATUS_FAILED;
      }
    } else {
	  $this->log('Conversion Attempt failed: Proposal already converted.');
      return REQSTATUS_FAILED;
    }
  }
}

function main($argv)
{
  echo "\n>>> " . date('Y-m-d H:i:s') . "\n";

  $args = array_slice($argv, 1);
    
  define('BACKEND_DEBUG', array_search('-d', $args) !== false);

  //echo "\n>>> Creating MycaBackend Instance";
  $myca = new MycaBackend($args);
  //echo "\n>>> Created MycaBackend Instance";
  $myca->run();
}

main($argv);

