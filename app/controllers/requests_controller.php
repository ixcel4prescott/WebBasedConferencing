<?

class RequestsController extends AppController
{
  var $uses        = Array('RequestView', 'Request', 'RequestType', 'RequestStatus', 'Account', 'Bridge', 'Room', 
			   'RoomView', 'Salesperson', 'DialinNumber', 'Status', 'User', 'Pci',
			   'SpectelClient', 'SpectelConference', 'Pin');

  var $components  = Array('Pagination', 'Email', 'RequestHandler');
  var $helpers     = Array('Html', 'Pagination', 'Time'); 
  var $permissions = Array('status' => GROUP_RESELLERS_AND_ADMINS,
        'index' => GROUP_RESELLERS_AND_ADMINS,
        'user' => GROUP_ALL,
        'view' => GROUP_RESELLERS_AND_ADMINS);

  var $statuses = Array( 'pending'   => REQSTATUS_PENDING,
			 'approved'  => REQSTATUS_APPROVED,
			 'denied'    => REQSTATUS_DENIED,
			 'canceled'  => REQSTATUS_CANCELLED,
			 'completed' => REQSTATUS_COMPLETED,
			 'failed'    => REQSTATUS_FAILED );

  var $reseller_only_requests = Array(REQTYPE_ACCOUNT_STATUS_CHANGE, REQTYPE_ROOM_STATUS_CHANGE,  
				      REQTYPE_ACCOUNT_REASSIGN);

  //-----------------------------------------------------------------------------
  //
  // Public actions
  //
  //-----------------------------------------------------------------------------

  // Generic function for the approve/deny/cancel actions
  function status($status=null)
  {
    $this->layout = 'ajax';
    Configure::write('debug', 0); 
    set_time_limit(0);

    if(isset($this->statuses[$status]) && !empty($this->data)) {
      $user = $this->Session->read('User');
      
      $criteria = Array('Request.id' => $this->data['Request']['id']);
      
      if($user['User']['level_type'] != 'reseller')
	$criteria['NOT'] = Array('type' => $this->reseller_only_requests);

      if($request = $this->Request->find($criteria)) { 
	$handler = $this->Request->handler($request); 

	// If a method exists then we are doing work inline and for only one request,
	// otherwise we are going to update statuses and let the backend handle it
	if(method_exists($this, $handler)) { 	  
	  $rv = call_user_func(Array($this, $handler), $request, $this->statuses[$status]);

	  if($request['Request']['status'] != $rv) {
	    $request['Request']['status'] = $rv;
	    $request['RequestStatus']     = $this->RequestStatus->read(null, $rv);
	    $this->Request->updateStatus($request);
	  }

	} else {
	  $request_ids = Array($request['Request']['id']);

	  if(!empty($this->data['Request']['apply_similar'])) 
	    $request_ids = array_merge($request_ids, $this->Request->similarRequests($request));

	  $request['RequestStatus'] = $this->RequestStatus->read(null, $rv);

	  $this->Request->updateStatuses($request_ids, $this->statuses[$status], $user['User']['id'], !empty($this->data['Request']['comments']) ? $this->data['Request']['comments'] : null);
	  $this->Request->notifyUsers($request);
	}
	
	$this->set('request', $request);
	$this->set('user',    $user);      
	$this->set('type',    $status);
      }
    }
  }

  function index($id=null) 
  {
    $user = $this->Session->read('User');

    $this->set('id', $id);

    $is_ajax = $this->RequestHandler->isAjax();
    if($is_ajax) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
    }    
    $this->set('is_ajax', $is_ajax);

    $criteria = a();
    if($user['User']['level_type'] != 'reseller')
      $criteria['NOT'] = Array('RequestType.name' => $this->reseller_only_requests);

    $this->set('types', $this->RequestType->generateList($criteria, 'name', null, '{n}.RequestType.id', '{n}.RequestType.name'));

    $statuses = $this->RequestStatus->generateList(null, 'name', null, '{n}.RequestStatus.id', '{n}.RequestStatus.name');
    unset($statuses[-1]);
    $this->set('statuses', $statuses);

    $criteria = Array();

    if(!$user['User']['ic_employee']) {
      $criteria['RequestView.resellerid'] = Array();

      foreach($user['ResellerGroup']['Reseller'] as $r)
	$criteria['RequestView.resellerid'][] = $r['resellerid'];
    }

    if(!empty($_POST)) {
      if($this->data['RequestView']['type'] !== '')
	$criteria['RequestView.type'] = $this->data['RequestView']['type'];	

      if($this->data['RequestView']['status'] !== '')
	$criteria['RequestView.status'] = $this->data['RequestView']['status'];

      if($user['User']['ic_employee'] && $this->data['RequestView']['other_resellers'] == 0)
	$criteria['RequestView.resellerid'] = IC_RESELLERID;
      
    } else {

      if($user['User']['level_type'] == 'reseller')
	$status_filter = REQSTATUS_PENDING;
      else
	$status_filter = REQSTATUS_APPROVED;
      
      $criteria['RequestView.status'] = $status_filter;
      $this->data = Array('RequestView' => Array('status' => $status_filter));

      if($user['User']['ic_employee'])
	$this->data['RequestView']['other_resellers'] = 1;
    }

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'created', 'direction', 'ASC'));
    $this->set('requests', $this->RequestView->findAll($criteria, NULL, $order, $limit, $page));
  } 
  
  function user()
  {
    $this->layout = 'ajax';
    Configure::write('debug', 0); 

    $data = Array();

    if($user = $this->Session->read('User')) {
      $counts = $this->Request->userCount($user['User']['id']);

      foreach($counts as $c) 
	if($c['cnt'])
	  $data[$c['name']] = $c['cnt'];
    }

    $this->set('data', $data);
  }

  function view($id=null)
  {
    $this->layout = 'request';
    Configure::write('debug', 0); 

    if($request = $this->Request->read(null, $id)) {
      $this->set('request', $request);

      $user = $this->Session->read('User');

      $this->set('read_only', $user['User']['level_type'] == 'salesperson' || ($user['User']['level_type'] != 'reseller' && in_array($request['Request']['type'], $this->reseller_only_requests)));

      if($request['Request']['status'] == REQSTATUS_PENDING)
	$type = 'authorize';
      else
	$type = low(r(' ', '_', $request['RequestStatus']['name']));
	
      if($request['Request']['signed_off_by'])
	$this->set('user', $this->User->read(null, $request['Request']['signed_off_by']));
      else
	$this->set('user', Array('User'=>Array('name' => 'Unknown', 'id' => 0)));
      
      $this->set('type', $type);
      $this->set('header', 'Request');

      $handler = 'request_' . low(r(' ', '_', $request['RequestType']['name']));

      if(method_exists($this, $handler))
	call_user_func(Array($this, $handler), $request);
    }
  }

  //-----------------------------------------------------------------------------
  //
  // These are the handlers for when a request is viewed
  //
  //-----------------------------------------------------------------------------
  private function request_account_reassignment($request)
  {
    $this->set('header', 'Account Reassignment');

    $this->set('old', $this->Salesperson->read(null, $request['RequestData']['from']));
    $this->set('new', $this->Salesperson->read(null, $request['RequestData']['to']));
    $this->render('account_reassignment');
  }

  private function request_account_status_change($request)
  {
    $this->set('header', 'Account Status Change');

    $this->set('account_statuses', $this->Status->generateList(null, 'description ASC', null, '{n}.Status.acctstat', '{n}.Status.description'));
    $this->render('account_status_change');
  }

  private function request_room_creation($request)
  {
    $this->set('header', 'Room Creation');
 
    if($request['Request']['status'] == REQSTATUS_APPROVED)
      $this->set('type', 'automated');

    $this->set('schedule_types', isset($this->Room->schedule_types[$request['RequestData']['bridgeid']]) ? $this->Room->schedule_types[$request['RequestData']['bridgeid']] : 
	       $this->Room->schedule_types[OCI_BRIDGEID]);

    $this->set('security_types', isset($this->Room->security_types[$request['RequestData']['bridgeid']]) ? $this->Room->security_types[$request['RequestData']['bridgeid']] : 
	       $this->Room->security_types[OCI_BRIDGEID]);

    $this->set('start_modes',    isset($this->Room->start_modes[$request['RequestData']['bridgeid']]) ? $this->Room->start_modes[$request['RequestData']['bridgeid']] : 
	       $this->Room->start_modes[OCI_BRIDGEID]);

    $this->set('announcements',  isset($this->Room->announcements[$request['RequestData']['bridgeid']]) ? $this->Room->announcements[$request['RequestData']['bridgeid']] : 
	       $this->Room->announcements[OCI_BRIDGEID]);

    $this->set('recording_signals', isset($this->Room->recording_signals[$request['RequestData']['bridgeid']]) ? $this->Room->recording_signals[$request['RequestData']['bridgeid']] : 
	       $this->Room->recording_signals[OCI_BRIDGEID]);

    $this->set('room_statuses',     $this->Status->generateList(null, 'description ASC', null, 
								'{n}.Status.acctstat', '{n}.Status.description'));
    $this->set('ending_signals',    $this->Room->ending_signals);
    $this->set('dtmf_signals',      $this->Room->dtmf_signals);
    $this->set('digit_entries',     $this->Room->digit_entries);
    $this->set('languages',         $this->Room->languages);

    $this->DialinNumber->recursive = 0;
    $this->set('dialin_number', $this->DialinNumber->read(null, $request['RequestData']['dialinNoid']));
    $this->set('bridges',       Symbols::$bridges);

    $this->render('room_create');
  }

  private function request_room_closure($request)
  {
    if($request['Request']['status'] == REQSTATUS_APPROVED)
      $this->set('type', 'automated');

    $this->set('header', 'Room Close');
    $this->set('room', $this->RoomView->read(null, $request['Request']['accountid']));
    $this->set('room_statuses', $this->Status->generateList(null, 'description ASC', null, 
							    '{n}.Status.acctstat', '{n}.Status.description'));
    $this->render('room_close');
  }

  private function request_room_update($request) 
  {
    $this->set('header', 'Room Update');
    
    if($request['Request']['status'] == REQSTATUS_APPROVED) {
      if(!$request['Request']['manual']) 
	$this->set('type', 'automated');
      else
	$this->set('type', 'manual');
    }

    $room = $this->Room->read(null, $request['Request']['accountid']);
    $this->set('room', $room);

    $this->set('schedule_types', isset($this->Room->schedule_types[$request['RequestData']['bridgeid']]) ? $this->Room->schedule_types[$request['RequestData']['bridgeid']] : 
	       $this->Room->schedule_types[OCI_BRIDGEID]);

    $this->set('security_types', isset($this->Room->security_types[$request['RequestData']['bridgeid']]) ? $this->Room->security_types[$request['RequestData']['bridgeid']] : 
	       $this->Room->security_types[OCI_BRIDGEID]);

    $this->set('start_modes',    isset($this->Room->start_modes[$request['RequestData']['bridgeid']]) ? $this->Room->start_modes[$request['RequestData']['bridgeid']] : 
	       $this->Room->start_modes[OCI_BRIDGEID]);

    $this->set('announcements',  isset($this->Room->announcements[$request['RequestData']['bridgeid']]) ? $this->Room->announcements[$request['RequestData']['bridgeid']] : 
	       $this->Room->announcements[OCI_BRIDGEID]);

    $this->set('recording_signals', isset($this->Room->recording_signals[$request['RequestData']['bridgeid']]) ? $this->Room->recording_signals[$request['RequestData']['bridgeid']] : 
	       $this->Room->recording_signals[OCI_BRIDGEID]);

    $this->set('room_statuses',     $this->Status->generateList(null, 'description ASC', null, 
								'{n}.Status.acctstat', '{n}.Status.description'));
    $this->set('ending_signals',    $this->Room->ending_signals);
    $this->set('dtmf_signals',      $this->Room->dtmf_signals);
    $this->set('digit_entries',     $this->Room->digit_entries);
    $this->set('languages',         $this->Room->languages);

    $this->DialinNumber->recursive = 0;
    $this->set('dialin_numbers', $this->DialinNumber->generateList(null, 'description ASC', null, '{n}.DialinNumber.id', '{n}.DialinNumber.description'));
    $this->set('bridges',        Symbols::$bridges);
    
    $this->render('room_update');
  }

  private function request_room_status_change($request) 
  {
    $this->set('header', 'Room Status Change');

    if($request['Request']['status'] == REQSTATUS_APPROVED && ($request['RequestData']['roomstat'] == 1 || $request['RequestData']['roomstat'] == 2))
      $this->set('type', 'automated');

    $this->set('room', $this->RoomView->read(null, $request['Request']['accountid']));
    $this->set('room_statuses', $this->Status->generateList(null, 'description ASC', null, 
							    '{n}.Status.acctstat', '{n}.Status.description'));

    $this->render('room_status_change');
  }

  private function request_billing_payment($request)
  {
    $this->set('header', 'Billing Payment');

     if($request['Request']['status'] == REQSTATUS_PENDING)
      $this->set('type', 'manual');

    $this->render('billing_payment');
  }

  private function request_code_migration($request)
  {
    if($request['Request']['status'] == REQSTATUS_APPROVED)
      $this->set('type', 'automated');

    $this->set('header', 'Code Migration');
    $this->render('code_migration');
  }

  private function request_registration($request)
  {
    $this->set('header', 'New Client Account Registration');
    $this->render('registration');    
  }

  private function request_reservation($request)
  {
    $this->set('reservation', $request['RequestData']);

    $participants = Array();
    for($i=0; isset($request['RequestData'][sprintf('participant%d_name', $i)]); $i++)
      $participants[] = Array('name'      => $request['RequestData'][sprintf('participant%d_name', $i)],
			      'phone'     => $request['RequestData'][sprintf('participant%d_phone', $i)],
			      'alt_phone' => $request['RequestData'][sprintf('participant%d_alt_phone', $i)]);
      
    $this->set('participants', $participants);
    
    $this->set('header', 'Conference Reservation');
    $this->render('reservation');
  }

  private function request_spectel_move($request)
  {
    $this->set('header', 'Spectel Room Move');
    
    $this->set('dest',       $this->SpectelClient->read(null, $request['RequestData']['ClientRef']));
    $this->set('src',        $this->SpectelClient->read(null, $request['RequestData']['src']));
    $this->set('conference', $this->SpectelConference->find(Array('ReservationRef' => $request['RequestData']['ReservationRef'])));
    
    $this->render('spectel_move');
  }

  private function request_pin_creation($request)
  {
    $this->set('header', 'PIN Creation');
    $this->set('bridges', Symbols::$bridges);

    $this->render('pin_creation');    
  }

  private function request_pin_update($request)
  {
    $this->set('header', 'PIN Update');
    $this->set('pin', $this->Pin->read(null, $request['RequestData']['id']));
    $this->set('bridges', Symbols::$bridges);

    $this->render('pin_update');    
  }

  private function request_pin_deletion($request)
  {
    $this->set('header', 'PIN Deletion');
    $this->set('pin', $this->Pin->read(null, $request['RequestData']['id']));
    $this->set('bridges', Symbols::$bridges);    

    $this->render('pin_deletion');    
  }

  //-----------------------------------------------------------------------------
  //
  // Inline handlers, these do not get stuck back in queue for backend
  //
  //-----------------------------------------------------------------------------

  private function approved_room_update($request, $status)
  {
    if($status == REQSTATUS_COMPLETED) { 
      $room = $this->Room->read(null, $request['Request']['accountid']);

      // Block fields that shouldnt be updated
      if(isset($request['RequestData']['acctgrpid']))
	unset($request['RequestData']['acctgrpid']);

      if($new_room = $this->Room->update($room, $request['RequestData'])) {
	$this->diffLog('Room', DIFFLOG_OP_UPDATE, $room['Room']['accountid'], $new_room['Room'], $room['Room']);
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }

    } else {
      $rv = $status;
    }

    return $rv;
  }  

  private function pending_registration($request, $status)
  {
    if($status == REQSTATUS_APPROVED) {
      // setting these for email component to pickup
      $this->set('user', $request['User']);
	    
      $this->Email->Subject = 'MyConferenceAdmin New User Registration';
      $this->Email->AddAddress($request['User']['email'], $request['User']['name']);
      $this->Email->renderBody('email\registration', CLIENTS_EMAIL_LAYOUT);
      
      if($this->Email->Send()) { 
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = $status;
    }

    return $rv;
  }
  
  private function pending_reservation($request, $status)
  {
    if($status == REQSTATUS_APPROVED) {

      // setting these for email component to pickup
      $this->set('user', $request['User']);
      $this->set('reservation', $request['RequestData']);

      $participants = Array();
      for($i=0; isset($request['RequestData'][sprintf('participant%d_name', $i)]); $i++)
	$participants[] = Array('name'      => $request['RequestData'][sprintf('participant%d_name', $i)],
				'phone'     => $request['RequestData'][sprintf('participant%d_phone', $i)],
				'alt_phone' => $request['RequestData'][sprintf('participant%d_alt_phone', $i)]);
      
      $this->set('participants', $participants);
	    
      $this->Email->Subject = 'MyConferenceAdmin Reservation Confirmation';
      $this->Email->AddAddress($request['User']['email'], $request['User']['name']);
      $this->Email->renderBody('email\reservation', CLIENTS_EMAIL_LAYOUT);
      
      if($this->Email->Send()) { 
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = $status;
    }

    return $rv;    
  }

  private function pending_billing_payment($request, $status)
  {
    if($status == REQSTATUS_COMPLETED) {
      
      $this->set('pci', $this->Pci->read(null, $request['Request']['acctgrpid']));
      $this->set('request', $request);
	    
      $this->Email->Subject = 'MyConferenceAdmin Payment Confirmation';
      $this->Email->AddAddress($request['User']['email'], $request['User']['name']);
      $this->Email->renderBody('email\billing_payment', CLIENTS_EMAIL_LAYOUT);
      
      if($this->Email->Send()) { 
	$rv = REQSTATUS_COMPLETED;
      } else {
	$rv = REQSTATUS_FAILED;
      }
    } else {
      $rv = $status;
    }

    return $rv;        
  }
}
