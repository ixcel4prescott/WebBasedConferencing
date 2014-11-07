<?

class Room extends AppModel
{
  var $name        = 'Room';
  var $useTable    = 'account';
  var $primaryKey  = 'accountid';

  var $error_msg;

  var $validate = Array( 'number'             => '/^\d+$/',
			 'bridgeid'           => VALID_NOT_EMPTY,
			 'productid'          => VALID_NOT_EMPTY,
			 'dialinNoid'         => VALID_NOT_EMPTY,
			 'contact'            => '/^\w+/',
			 'roomstat'           => VALID_NOT_EMPTY,
			 'lang'               => VALID_NOT_EMPTY,
			 'maximumconnections' => VALID_NUMBER,
			 'scheduletype'       => VALID_NOT_EMPTY,
			 'securitytype'       => VALID_NOT_EMPTY,
			 'startmode'          => VALID_NOT_EMPTY,
			 'namerecording'      => VALID_NOT_EMPTY,
			 'entryannouncement'  => VALID_NOT_EMPTY,
			 'exitannouncement'   => VALID_NOT_EMPTY,
			 'endingsignal'       => VALID_NOT_EMPTY,
			 'dtmfsignal'         => VALID_NOT_EMPTY,
			 'recordingsignal'    => VALID_NOT_EMPTY,
			 'digitentry1'        => VALID_NOT_EMPTY,
			 'digitentry2'        => VALID_NOT_EMPTY,
			 'servicerate'        => VALID_NOT_EMPTY,
             //'billing_method_id'  => VALID_NUMBER, Not sure if this validation is really needed
			 'canada'             => VALID_NUMBER );
  
  var $belongsTo = Array( 'Account'      => Array( 'className'  => 'AccountView',
						   'foreignKey' => 'acctgrpid' ),
			 
			  'Bridge'       => Array( 'className'  => 'Bridge',
						   'foreignKey' => 'bridgeid' ),
				
			  'DialinNumber' => Array( 'className'  => 'DialinNumber',
						   'foreignKey' => 'dialinNoid' ),
				
			  'ServiceRate'  => Array( 'className'  => 'ServiceRate', 
						   'foreignKey' => 'servicerate' ),

			  'Status'       => Array( 'className'  => 'Status', 
						   'foreignKey' => 'roomstat' ));      
  

  var $hasAndBelongsToMany = array('Contact' => array('className'             => 'Contact',
						      'joinTable'             => 'contact_accounts',
						      'foreignKey'            => 'accountid',
						      'associationForeignKey' => 'contact_id',
						      'conditions'            => '',
						      'order'                 => '',
						      'limit'                 => '',
						      'unique'                => true,
						      'finderQuery'           => '',
						      'deleteQuery'           => '' ));

  #TODO: Need to clarify these relationships.
  var $hasOne = array('WebinterpointRoom' => array('className' => 'WebinterpointRoom',
                                                   'foreignKey' => 'web_accountid'),
					  'SmartCloudRoom' => array('className' => 'SmartCloudRoom',
                                           'foreignKey' => 'web_accountid'),					   
                      'WebexRoom' => array('className' => 'WebexRoom',
                                           'foreignKey' => 'web_accountid'),
                      'LiveMeetingRoom' => array('className' => 'LiveMeetingRoom',
                                           'foreignKey' => 'web_accountid'),
                      'WebinterpointInfo' => array('className' => 'WebinterpointRoom',
                                           'foreignKey' => 'audio_accountid'),
                      'WebexInfo' => array('className' => 'WebexRoom',
                                           'foreignKey' => 'audio_accountid'),
                      'LiveMeetingInfo' => array('className' => 'LiveMeetingRoom',
                                           'foreignKey' => 'audio_accountid'));
  
  // Maps of symbols for various fields
  var $room_statuses     = Array( STATUS_ACTIVE    => 'Active', 
				  STATUS_SUSPENDED => 'Suspended', 
				  STATUS_CANCELLED => 'Cancelled', 
				  STATUS_TRIAL     => 'Trial' );
				  
// Should be Replaced by model usage.
  var $schedule_types    = Array( OCI_BRIDGEID       => Array( 0 => 'One Time',  1 => 'Start Now',  2 => 'Standing',  3 => 'Repeating' ), 
				  SPECTEL_BRIDGEID   => Array( 0 => 'Unattended', 1 => 'Attended', 2 => 'Flex Flow' ), 
				  SPECTELATL_BRIDGEID   => Array( 0 => 'Unattended', 1 => 'Attended', 2 => 'Flex Flow' ),
          SPECTELFR_BRIDGEID   => Array( 0 => 'Unattended', 1 => 'Attended', 2 => 'Flex Flow' ),
				  INTERCALL_BRIDGEID => Array(), 
				  BT_BRIDGEID        => Array() );

  var $security_types    = Array( OCI_BRIDGEID       => Array( 0 => 'No PIN',  1 => 'PIN Optional',  2 => 'PIN Required',  3 => 'Invitation Required' ), 
				  SPECTEL_BRIDGEID   => Array( 1 => 'No Pin',  2 => 'PIN Required'), 
				  SPECTELATL_BRIDGEID   => Array( 1 => 'No Pin',  2 => 'PIN Required'),
          SPECTELFR_BRIDGEID   => Array( 1 => 'No Pin',  2 => 'PIN Required'),
				  INTERCALL_BRIDGEID => Array(),
				  BT_BRIDGEID        => Array(), );

  var $start_modes       = Array( OCI_BRIDGEID       => Array( 0 => 'Interactive', 1 => 'Music awaiting chair', 2 => 'Music to presentation', 3 => 'Music manual control' ),
				  SPECTEL_BRIDGEID   => Array( 0 => 'Interactive', 1 => 'Music' ), 
				  SPECTELATL_BRIDGEID   => Array( 0 => 'Interactive', 1 => 'Music' ),
          SPECTELFR_BRIDGEID   => Array( 0 => 'Interactive', 1 => 'Music' ),
				  INTERCALL_BRIDGEID => Array( 0 => 'Interactive', 1 => 'Music' ),
				  BT_BRIDGEID        => Array( 0 => 'Interactive', 1 => 'Music awaiting chair', 2 => 'Music to presentation'));

  var $announcements     = Array( OCI_BRIDGEID       => Array( 0 => 'Off', 1 => 'Tone', 2 => 'System Message', 3 => 'Personal Message' ), 
				  SPECTEL_BRIDGEID   => Array( 0 => 'Off', 1 => 'System Message', 2 => 'Tone', 3 => 'Personal Message', 4 => 'Both'), 
				  SPECTELATL_BRIDGEID   => Array( 0 => 'Off', 1 => 'System Message', 2 => 'Tone', 3 => 'Personal Message', 4 => 'Both'),
          SPECTELFR_BRIDGEID   => Array( 0 => 'Off', 1 => 'System Message', 2 => 'Tone', 3 => 'Personal Message', 4 => 'Both'),
				  INTERCALL_BRIDGEID => Array( 0 => 'Off', 1 => 'Tone', 2 => 'Personal Message', 3 => 'Both' ), 
				  BT_BRIDGEID        => Array( 0 => 'None', 1 => 'Tone', 2 => 'Personal Message') );

  // NB: ICAM had a long standing bug where it had a key 1 that is not valid
  var $recording_signals = Array( OCI_BRIDGEID       => Array( 0 => 'None', 1 => 'Play System Message', 2 => 'Play System Message' ),
				  SPECTEL_BRIDGEID   => Array( 0 => 'None', 1 => 'Tone', 2 => 'Play System Message' ), 
				  SPECTELATL_BRIDGEID   => Array( 0 => 'None', 1 => 'Tone', 2 => 'Play System Message' ),
          SPECTELFR_BRIDGEID   => Array( 0 => 'None', 1 => 'Tone', 2 => 'Play System Message' ),
				  INTERCALL_BRIDGEID => Array(),
				  BT_BRIDGEID        => Array() );

  var $ending_signals    = Array( 0 => 'None', 1 => 'Tone', 2 => 'System Message' );
  var $dtmf_signals      = Array( 0 => 'None', 1 => 'Tone', 2 => 'System Message' );
  var $digit_entries     = Array( 0 => 'No Prompt', 1 => 'Chair Only', 2 => 'All' );
  var $languages         = Array( 0 => 'Default Language', 1 => 'English (US)' );


  var $spectel_namerecording_options = Array( 1 => 'Off', 
					      2 => 'Individual', 
					      3 => 'Conference', 
					      4 => 'Operator Only' );

  // Where rooms will be migrated to
  var $migration_targets = Array( OCI_BRIDGEID       => Array(SPECTEL_BRIDGEID, SPECTELATL_BRIDGEID), 
				  SPECTEL_BRIDGEID   => Array(OCI_BRIDGEID, SPECTELATL_BRIDGEID), 
				  SPECTELATL_BRIDGEID => Array(OCI_BRIDGEID, SPECTEL_BRIDGEID),
          SPECTELFR_BRIDGEID => Array(),
				  INTERCALL_BRIDGEID => Array(),
				  BT_BRIDGEID        => Array() );

  function __construct($id = false, $table = null, $ds = null)
  {
    // We only need these on the biz side of the site
    if(BIZ_SIDE) {
    }

    parent::__construct($id, $table, $ds);

    loadModel('Oci');
    $this->Oci = new Oci();

    loadModel('Spectel');
    $this->Spectel = new Spectel();
	
	  loadModel('SpectelAtl');
	  $this->SpectelAtl = new SpectelAtl();

    loadModel('SpectelFr');
    $this->SpectelFr = new SpectelFr();
	
    loadModel('Intercall');
    $this->Intercall = new Intercall();

    loadModel('Bt');
    $this->Bt = new Bt();

    loadModel('WebinterpointAccount');
    $this->WebinterpointAccount = new WebinterpointAccount();
	
	loadModel('SmartCloudAccount');
    $this->SmartCloudAccount = new SmartCloudAccount();

    loadModel('Request');
    $this->Request = new Request();
  }

  function getErrorMessage($bridgeid) {
    $rv = null;

    switch($bridgeid) { 
    case OCI_BRIDGEID: 
      $rv = $this->Oci->error_msg;
      break;
      
    case SPECTEL_BRIDGEID:
      $rv = $this->Spectel->error_msg;
      break;
    case SPECTELATL_BRIDGEID:
      $rv = $this->SpectelAtl->error_msg;
      break;	
    case SPECTELFR_BRIDGEID:
      $rv = $this->SpectelFr->error_msg;
      break;  
      
    case INTERCALL_BRIDGEID: 
      $rv = $this->Intercall->error_msg;
      break;

    case BT_BRIDGEID: 
      $rv = $this->Bt->error_msg;
      break;

    case WEBINTERPOINT_BRIDGEID: 
      $rv = $this->WebinterpointAccount->error_msg;
      break;
	  
	case SMARTCLOUD_BRIDGEID: 
      $rv = $this->SmartCloudAccount->error_msg;
      break;  
      
    case WEBEX_BRIDGEID: 
      $rv = $this->WebexRoom->error_msg;
      break;
      
    case LIVE_MEETING_BRIDGEID: 
      $rv = $this->WebexRoom->error_msg;
      break;

    default: 
      $rv = 'Unknown bridge';
      break;
    } 

    return $rv;
  }

  function get($accountid, $user)
  {
    $room = $this->read(null, $accountid);

    if($room) {
      if(!is_null($user['Resellers'])) {

        if(!isset($room['Account']))
          $room['Account'] = $this->Account->read(null, 
            $room['Room']['acctgrpid']);

	    if(!in_array($room['Account']['resellerid'], $user['Resellers']))
	      return null;
      }

      if($user['User']['level_type'] == SALESPERSON_LEVEL and 
        ($user['Salespersons'] == null or 
        !in_array($room['Account']['salespid'], $user['Salespersons']))){
          return null;
      }
   }
    return $room;
  }

  function beforeValidate()
  {
    parent::beforeValidate();
    
    if(isset($this->data['Room']['number']) && ($this->data['Room']['number'] < 1 || $this->data['Room']['number'] > MAX_ROOMS))
      $this->invalidate('number');

    if($this->data['Room']['bridgeid'] == SPECTEL_BRIDGEID || $this->data['Room']['bridgeid'] == SPECTELATL_BRIDGEID || $this->data['Room']['bridgeid'] == SPECTELFR_BRIDGEID)
      $this->validate['namerecording'] = VALID_NOT_EMPTY;

    if(!empty($this->data['Room']['confname']))
      $this->validate['confname'] = '/^[^&]+$/';

    if(empty($this->data['Room']['accountid']) && !empty($this->data['Room']['cec'])) {
      $this->validate['cec'] = '/^\d{6,}$/';

      // if($this->checkCodeStrength($this->data['Room']['cec']) <= CODE_STRENGTH)
      // 	$this->invalidate('cec'); 
    }

    if(empty($this->data['Room']['accountid']) && !empty($this->data['Room']['pec'])) {
      $this->validate['pec'] = '/^\d{6,}$/';

      // if($this->checkCodeStrength($this->data['Room']['pec']) <= CODE_STRENGTH)
      // 	$this->invalidate('pec');
    }

    if(empty($this->data['Room']['accountid']) && (!empty($this->data['Room']['cec']) || !empty($this->data['Room']['pec'])))
      $this->checkCollision($this->data);

    if(!empty($this->data['Room']['emailrpt']) && empty($this->data['Room']['email']))
      $this->invalidate('emailrpt');

    if(($this->data['Room']['bridgeid'] == SPECTEL_BRIDGEID || $this->data['Room']['bridgeid'] == SPECTELATL_BRIDGEID || $this->data['Room']['bridgeid'] == SPECTELFR_BRIDGEID) && isset($this->data['Room']['scheduletype']) && $this->data['Room']['scheduletype'] == 1) {
      $this->validate['duration'] = VALID_NUMBER;
      
      $this->validate['startdate_date'] = '/\d{2}\/\d{2}\/\d{4}/';

      if(empty($this->data['Room']['startdate_date']) || empty($this->data['Room']['startdate_hour']) || empty($this->data['Room']['startdate_min']) || empty($this->data['Room']['startdate_meridian'])) {
	$this->data['Room']['startdate'] = '';
	$this->invalidate('startdate');
	$this->invalidate('startdate_date');
	$this->invalidate('startdate_hour');
	$this->invalidate('startdate_min');
	$this->invalidate('startdate_meridian');
      } else {
	$t = strtotime($this->data['Room']['startdate_date'] . ' ' . $this->data['Room']['startdate_hour'] . ':' . $this->data['Room']['startdate_min'] . ' ' . $this->data['Room']['startdate_meridian']);

	if(time() >= $t + 300) {
	  $this->invalidate('startdate');
	  $this->invalidate('startdate_date');
	  $this->invalidate('startdate_hour');
	  $this->invalidate('startdate_min');
	  $this->invalidate('startdate_meridian');
	}
      }
    }

    if(empty($this->data['Room']['canada']))
      $this->data['Room']['canada'] = 0;

    return true;
  }

  // Common cases of bad codes:
  //  - repeating digits
  //  - sequential digits
  //
  function checkCodeStrength($code)
  {
    $score       = 0;
    $len         = strlen($code);
    $seen_digits = Array();

    // Reward passwords over 6 digits and penalize passwords under
    if($len > 6)
      $score -= ($len-6);
    elseif($len < 6)
      $score += (6 - $len);

    for($i=0; $i<$len; $i++) {
      // Check for repeating digits
      if(in_array($code[$i], $seen_digits))
	$score++;
      else
	$seen_digits[] = $code[$i];
      
      // Check if this digit is sequential to the previous
      if(($i < $len-1) && abs($code[$i] - $code[$i+1]) == 1)
	$score++;  
    }
    
    // Make sure score is normalized to range
    $score = min($score, $len);
    $score = max($score, 0);

    return 1 - (float)$score / (float)$len;
  }

  function checkCollision($room)
  {
    $colliding_criteria = Array('OR'  => Array('Room.cec' => Array(), 
					       'Room.pec' => Array()));

    if(!empty($room['Room']['cec'])) {
      $colliding_criteria['OR']['Room.cec'][] = $room['Room']['cec'];
      $colliding_criteria['OR']['Room.pec'][] = $room['Room']['cec'];
    }

    if(!empty($room['Room']['pec'])) {
      $colliding_criteria['OR']['Room.cec'][] = $room['Room']['pec'];
      $colliding_criteria['OR']['Room.pec'][] = $room['Room']['pec'];
    }
    
    if(!empty($colliding_criteria['OR']['Room.cec']) || !empty($colliding_criteria['OR']['Room.cec'])) {
      foreach($this->findAll($colliding_criteria) as $i) {

	if($i['Room']['cec'] == $room['Room']['cec'] || $i['Room']['pec'] == $room['Room']['cec'])
	  $this->invalidate('cec');

	if($i['Room']['cec'] == $room['Room']['pec'] || $i['Room']['pec'] == $room['Room']['pec'])
	  $this->invalidate('pec');
      }
    }
  }

  function getConfName($company, $contact)
  {
    return preg_replace("/'/", '', (substr($company, 0, 10) . '_' . $contact));
  }

  function genCode($len=DEFAULT_CODE_LEN)
  {
    $data = Array( 'length' => $len          );
    $out  = Array( 'rv'     => 'VARCHAR(32)' );
    
    $rv = $this->sproc('GenerateCode', $data, $out);
 
    return $rv['rv'];
  }

  function isValidAccountId($a)
  {
    return preg_match('/S\d+|A\d+|20\d+|I\d+|MR\d+|OAI\d+/', $a)>0;
  }

  function accountid2bridge($a)
  {
    $rv     = UNSPECIFIED_BRIDGEID;
    $length = strlen($a);
    
    if($this->isValidAccountid($a)) { 
      if($a[0] == 'S')
	$rv = SPECTEL_BRIDGEID;
	  elseif($a[0] == 'A')
	$rv = SPECTELATL_BRIDGEID;
    elseif(substr($a, 0, 2) == 'FR')
  $rv = SPECTELFR_BRIDGEID;
      elseif($a[0] == 'I')
	$rv = INTERCALL_BRIDGEID;
      elseif(substr($a, 0, 2) == 'MR')
	$rv = BT_BRIDGEID;
      elseif(substr($a, 0, 3) == 'OAI')
	$rv = OUTSOURCED_BRIDGEID;
      elseif($length >= 14 && substr($a, 0, 2) == '20' && $a[8] == '1')
	$rv = OCI_BRIDGEID;   
      elseif($length >= 14 && substr($a, 0, 2) == '20' && $a[8] == '2')
	$rv = AT1_BRIDGEID;
      elseif($length >= 14 && substr($a, 0, 2) == '20' && $a[8] == '3')
	$rv = AT2_BRIDGEID;
    }

    return $rv;
  }

  function validateSettings($bridge_id, $settings)
  {
    // Massage settings such as namerecording
    // (name recording must be enabled for personal message announcements)
    switch($bridge_id) {  
    case OCI_BRIDGEID:
      if($settings['entryannouncement'] == 3 || $settings['exitannouncement'] == 3)
	$settings['namerecording'] = 1;
      break;

    case SPECTEL_BRIDGEID:
      if($settings['entryannouncement'] == 3 || $settings['exitannouncement'] == 3)
	$settings['namerecording'] = 3;
      break;
	  
    case SPECTELATL_BRIDGEID:
      if($settings['entryannouncement'] == 3 || $settings['exitannouncement'] == 3)
	$settings['namerecording'] = 3;
      break;

    case SPECTELFR_BRIDGEID:
      if($settings['entryannouncement'] == 3 || $settings['exitannouncement'] == 3)
  $settings['namerecording'] = 3;
      break;
      
    case INTERCALL_BRIDGEID:
      if($settings['entryannouncement'] == 2 || $settings['entryannouncement'] == 3 || 
	  $settings['exitannouncement'] == 2 || $settings['exitannouncement'] == 3 )
	$settings['namerecording'] = 1;
      break;
      
    case BT_BRIDGEID:
      if($settings['entryannouncement'] == 2 || $settings['exitannouncement'] == 2)
	$settings['namerecording'] = 1;
      break;

    default:
      break;
    }

    return $settings;
  }
  
  function createRoom($user, $data)
  {
    $sql = "select replace(name, '@', '') as parameter from sys.all_parameters where object_id = (SELECT id FROM sysobjects WHERE name = 'CreateRoom')";
    $rawValidParams = $this->query($sql);
    $validParams = array();
    foreach($rawValidParams as $rowkey => $rowvalue)
      $validParams[$rowkey] = $rowvalue[0]['parameter'];

    foreach($data as $param => $value){
      if (array_search($param, $validParams))
        $cleanData[$param] = $value;
    }

    $cleanData['creator']  = $user['User']['id'];
    $cleanData['topic']  = $data['contact'];

    $out = Array( 'rv'      => 'BIT',
                  'message' => 'VARCHAR(200)' );

    $rv = $this->sproc('CreateRoom', $cleanData, $out);

    return $rv['rv'];
  }

  function doCreateRoom($account, $room, $debug=FALSE)
  {
    $rv       = null;
    //$conf_num = null;

    if (!$debug) {
    if(empty($room['confname']))
      $room['confname'] = $this->getConfName($room['company'], $room['contact']);

    if(empty($room['cec']))
      $room['cec'] = $this->genCode();

    if(empty($room['pec']))
      $room['pec'] = $this->genCode();

    $room = $this->validateSettings($room['bridgeid'], $room);

    if(!empty($room['cec']) && !empty($room['pec'])) {
      switch($room['bridgeid']) {  
      case OCI_BRIDGEID:
	$conf_num = $this->Oci->createRoom($room);
	break;

      case SPECTEL_BRIDGEID:
	$conf_num = $this->Spectel->createRoom($room);
	break;
	
	  case SPECTELATL_BRIDGEID:
	 $conf_num = $this->SpectelAtl->createRoom($room);
	 break;

    case SPECTELFR_BRIDGEID:
   $conf_num = $this->SpectelFr->createRoom($room);
   break;

      case INTERCALL_BRIDGEID:
	$conf_num = $this->Intercall->createRoom($room);
	break;

      case BT_BRIDGEID:
	$conf_num = $this->Bt->createRoom($room);
	break;

      default:
	break;
      }

      if($conf_num) {    

	// Make sure we dont overwrite any notes
	foreach(Array('note1', 'note2', 'note3', 'note4') as $i)
	  if(isset($room[$i]) && empty($room[$i]))
	    unset($room[$i]);

	$room['accountid']     = $conf_num;
	$room['uifn']          = $account['Account']['default_uifn'];
	$room['rateid']        = $account['Account']['default_rateid'];
	$room['webinterpoint'] = 0;
	$room['smartcloud'] = 0;

	$this->id = null;

	// Try REALLY hard to save since we created a room on the bridge
	// (Yes, Im serious.  This can probably go away now)
	for($i=0; $i<10; $i++) { 
	  if($this->save($room, false)) {
	    $rv = Array('Room' => $room);
	    break;
	  }
	}

	if(!$rv)
	  $this->error_msg = sprintf('Saving newly created room %s to account table failed', $conf_num); 

      } else {
	$this->error_msg = $this->getErrorMessage($room['bridgeid']);
      }
    } else {
      $this->error_msg = 'Code generation failure';
    }

    return $rv;
    } else {  //Debug Branch
        //Need to fetch a room under ICTEST and return it. It would probably be nice 
        //  to match up the bridge
        $criteria = Array('Room.acctgrpid' => 'ICTEST',
                          'Room.roomstat' => 0,
                          'Room.bridgeid' => $room['bridgeid']);
        $existing_room = $this->find($criteria, NULL);
        return $existing_room; //Should be a room from ICTEST.
    }
  }

  function createWebRoom($user, $data)
  {
      $sql = "select replace(name, '@', '') as parameter from sys.all_parameters where object_id = (SELECT id FROM sysobjects WHERE name = 'CreateWebRoom')";
      $rawValidParams = $this->query($sql);
      $validParams = array();
      foreach($rawValidParams as $rowkey => $rowvalue)
          $validParams[$rowkey] = $rowvalue[0]['parameter']; 

      foreach($data as $param => $value){
          if (array_search($param, $validParams))
              $cleanData[$param] = $value;
      }

    $cleanData['creator']  = $user['User']['id'];
    $cleanData['topic']  = $data['contact'];

    $out = Array( 'rv'      => 'BIT',
                  'message' => 'VARCHAR(200)' );

    $rv = $this->sproc('CreateWebRoom', $cleanData, $out);

    return $rv['rv'];
  }


  function doCreateWebRoom($room, $debug=FALSE)
  {
      $rv       = null;
      $conf_num = null;

      //null out columns that don't apply to web rooms
      $room['rateid']                = null;
      $room['servicerate']           = null;
      $room['ratemultiplier']        = null;
      $room['scheduletype']          = null;
      $room['securitytype']          = null;
      $room['startmode']             = null;
      $room['endonchairhangup']      = null;
      $room['dialout']               = null;
      $room['record_playback']       = null;
      $room['entryannouncement']     = null;
      $room['exitannouncement']      = null;
      $room['endingsignal']          = null;
      $room['dtmfsignal']            = null;
      $room['recordingsignal']       = null;
      $room['digitentry1']           = null;
      $room['confirmdigitentry1']    = null;
      $room['digitentry2']           = null;
      $room['confirmdigitentry2']    = null;
      $room['muteallduringplayback'] = null;
      $room['uifn']                  = null;
      $room['dialinNoid']            = null;

      if (!$debug){
      switch($room['bridgeid']) 
      {
          case WEBINTERPOINT_BRIDGEID:
              $conf_num = $this->WebinterpointAccount->createAccount($room);
              if (isset($conf_num)){
                  $room['WebinterpointRoom'] = Array('web_accountid' => $conf_num,
                                                 'audio_accountid' => $room['audio_accountid'],
                                                 'service_provider' => 'INF');
              }
              break;
			  
			case SMARTCLOUD_BRIDGEID:
              $conf_num = $this->SmartCloudAccount->createAccount($room);
              if (isset($conf_num)){
                  $room['SmartCloudRoom'] = Array('web_accountid' => $conf_num,
                                                 'audio_accountid' => $room['audio_accountid'],
                                                 'service_provider' => 'INF');
              }
              break;  
			  
          case WEBEX_BRIDGEID:
              $conf_num = $this->WebexRoom->createRoom($room);
              $room['WebexRoom'] = Array('web_accountid' => $conf_num,
                                         'audio_accountid' => $room['audio_accountid']);
              break;
              
          case LIVE_MEETING_BRIDGEID:
              $conf_num = $this->LiveMeetingRoom->createRoom($room);
              $room['LiveMeetingRoom'] = Array('web_accountid' => $conf_num,
                                         'audio_accountid' => $room['audio_accountid']);
              break;
          default:
              break;
      }

      if($conf_num) {
          //Don't overwrite any notes
	      foreach(Array('note1', 'note2', 'note3', 'note4') as $i)
	          if(isset($room[$i]) && empty($room[$i]))
	              unset($room[$i]);

	      $room['accountid'] = $conf_num;

          $this->save($room, false);
          $rv = Array('Room' => $room);
          switch($room['bridgeid']) 
          {
          case WEBINTERPOINT_BRIDGEID:
              $this->WebinterpointRoom->save($room['WebinterpointRoom'], false);
              break;
		  case SMARTCLOUD_BRIDGEID:
              $this->SmartCloudRoom->save($room['SmartCloudRoom'], false);
              break;  
          case WEBEX_BRIDGEID:
              $this->WebexRoom->save($room['WebexRoom'], false);
              break;
          case LIVE_MEETING_BRIDGEID:
              $this->LiveMeetingRoom->save($room['LiveMeetingRoom'], false);
              break;
          default:
              break;
          }

          if (!$rv)
	          $this->error_msg = sprintf('Saving newly created room %s to account table failed', $conf_num); 
      } else {
          $this->error_msg = $this->getErrorMessage($room['bridgeid']);
      }

      return $rv;
      } else { //Debug Branch
          //get a room in ICTEST and return it. Probably should throw in some logic 
          //    that will match it up to the correct bridge
          echo "pre doCreateWebRoom debug query";
          $criteria = Array('Room.acctgrpid' => 'ICTEST',
                            'Room.roomstat' => 0,
                            'Room.bridgeid' => $room['bridgeid']);
          $existing_room = $this->find($criteria, NULL);
          echo "post doCreateWebRoom debug query";
          pr($existing_room);
          return $existing_room; //Should be a room from ICTEST.
      }
  }
          
        
  function migrateSettings($dest_bridgeid, $src_bridgeid, $orig_settings)
  {
    // Yes I'm serious... The alternatives werent pretty, maybe this is better in a SQL table.  
    // See feature_support_matrix.xlsx (hopefully someone kept a copy)
    // Be thankful i was explicit, OK?
    //
    // mapping is $settings_map[$src][$dest][$setting]
    
    require('room.settings_map.php');
    
    $settings = null;
    if(isset($settings_map[$src_bridgeid]) && $settings_map[$src_bridgeid][$dest_bridgeid]) {       
      // Run through the map
      $map      = $settings_map[$src_bridgeid][$dest_bridgeid];
      $settings = Array('bridgeid' => $dest_bridgeid);
      foreach($orig_settings as $k => $v) {
	if(isset($map[$k])) {
	  $settings[$k] = isset($map[$k][$v]) ? $map[$k][$v] : $map[$k][null];
	}	
      }

      // Bridge specific massaging
      switch($dest_bridgeid) {
      case OCI_BRIDGEID:
	$settings['scheduletype'] = 2; // Standing
	break;

      case SPECTEL_BRIDGEID:
	$settings['scheduletype']      = 0; // Unattended
	$settings['bill_code_prompt']  = 0;
	$settings['conference_viewer'] = 1;
	break;
	
      case SPECTELATL_BRIDGEID:
	$settings['scheduletype']      = 0; // Unattended
	$settings['bill_code_prompt']  = 0;
	$settings['conference_viewer'] = 1;
	break;

        case SPECTELFR_BRIDGEID:
  $settings['scheduletype']      = 0; // Unattended
  $settings['bill_code_prompt']  = 0;
  $settings['conference_viewer'] = 1;
  break;

      case INTERCALL_BRIDGEID:
	$settings['dialinNoid'] = 558; // OSBR1 Generic Dialin
	break;

      default:
	break;      
      }
      
      // Anything else
      if(isset($orig['startdate']))
	$settings['startdate'] = $orig['startdate'];
 
      if(isset($orig['duration']))
	$settings['duration'] = $orig['duration'];
    }

    return $settings;
  }

  // DEPRECATED -- For BT
  // This function checks if an update must hit the bridge.
  // that is if its a safe update and only must hit account table
  function isSafeUpdate($room, $data)
  {
    $rv = true;
    $safe_fields = a('bridgeid', 'dialinNoid', 'contact', 'company', 'email', 'emailrpt', 
		     'lang', 'reservationcomments', 'note1', 'note2', 'note3', 'note4', 'billingcode');

    $diff = array_diff_assoc($data, $room['Room']);
    foreach($diff as $k => $v) {

      if($k == 'conference_viewer' || $k == 'bill_code_prompt') {
	$rv = false;
	break;
      }

      if(!in_array($k, $safe_fields))
	$rv = false;
    }

    return $rv;
  }

  function updateRoom($user, $data)
  {
    $sql = "select replace(name, '@', '') as parameter from sys.all_parameters where object_id = (SELECT id FROM sysobjects WHERE name = 'UpdateRoom')";
    $rawValidParams = $this->query($sql);
    $validParams = array();
    foreach($rawValidParams as $rowkey => $rowvalue)
      $validParams[$rowkey] = $rowvalue[0]['parameter'];

    foreach($data as $param => $value){
      if (array_search($param, $validParams))
        $cleanData[$param] = $value;
    }

    $cleanData['creator']  = $user['User']['id'];
    $cleanData['topic']  = $data['contact'];

    $out = Array( 'rv'      => 'BIT',
                  'message' => 'VARCHAR(200)' );

    $rv = $this->sproc('UpdateRoom', $cleanData, $out);

    return $rv['rv'];
  }
  
  function doUpdateRoom($room, $data)
  {
    $rv   = null;
    $data = $this->validateSettings($room['Room']['bridgeid'], $data);

    switch($room['Room']['bridgeid']) {  
    case OCI_BRIDGEID:
      $rv = $this->Oci->updateRoom($room, $data);
      break;
      
    case SPECTEL_BRIDGEID:
      $rv = $this->Spectel->updateRoom($room, $data);
      break;
	  
    case SPECTELATL_BRIDGEID:
      $rv = $this->SpectelAtl->updateRoom($room, $data);
      break;

    case SPECTELFR_BRIDGEID:
      $rv = $this->SpectelFr->updateRoom($room, $data);
      break;
      
    case INTERCALL_BRIDGEID:
      $rv = $this->Intercall->updateRoom($room, $data);
      break;

    case BT_BRIDGEID:
    case WEBEX_BRIDGEID:
    case LIVE_MEETING_BRIDGEID:
      $rv = $this->Bt->updateRoom($room, $data);
      break;

    case WEBINTERPOINT_BRIDGEID:
      $rv = $this->WebinterpointAccount->updateAccount($room, $data);
      break;
	  
	case SMARTCLOUD_BRIDGEID:
      $rv = $this->SmartCloudAccount->updateAccount($room, $data);
      break;

    default:
      $rv = true;
      break;
    }

    if($rv) {

      if(isset($room['Room']['tstamp']))
	unset($room['Room']['tstamp']);

      $rv = $this->update($room, $data);      
    } else {
      $this->error_msg = $this->getErrorMessage($room['Room']['bridgeid']);
    }

    return $rv;
  }

  function suspendRoom($user, $room, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'   => $user['User']['id'], 
		   'accountid' => $room['Room']['accountid'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('SuspendRoom', $data, $out);
 
    return $rv['rv'];
  }

  function setBillingMethod($room, $billing_method_id=null, $billing_frequency_id=null, $flat_rate_charge=null)
  {
    $data = Array( 'accountid'            => $room['Room']['accountid'], 
		   'billing_method_id'    => $billing_method_id,
		   'billing_frequency_id' => $billing_frequency_id,
                   'flat_rate_charge'     => $flat_rate_charge);

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('SetRoomBillingMethod', $data, $out);
    
    return $rv['rv'];
  }

  function activateRoom($user, $room, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'   => $user['User']['id'], 
		   'accountid' => $room['Room']['accountid'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('ActivateRoom', $data, $out);
 
    return $rv['rv'];
  }

  function cancelRoom($user, $room, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'   => $user['User']['id'], 
		   'accountid' => $room['Room']['accountid'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('CancelRoom', $data, $out);
 
    return $rv['rv'];
  }

  // This function is used to request the update
  function updateStatus($user, $room, $status, $reason=null, $effective_date=null)
  {
    switch($status) {
    case STATUS_ACTIVE:
      $rv = $this->activateRoom($user, $room, $reason, $effective_date);
      break;
      
    case STATUS_SUSPENDED:
      $rv = $this->suspendRoom($user, $room, $reason, $effective_date);
      break;

    case STATUS_CANCELLED:
      $rv = $this->cancelRoom($user, $room, $reason, $effective_date);
      break;

    default:
      $rv = false;
      break;
    }

    return $rv;
  }

  function changeStatus($room, $status, $reason=null)
  {
    // FIXME: I believe this is to pass in the audio_accountid to the webinterpoint service.
    // I think we can just change the function to pull this from the WebinterpointRoom property.
    if ($room['Room']['bridgeid'] == WEBINTERPOINT_BRIDGEID)
        $room['Room']['audio_accountid'] = $room['WebinterpointRoom']['audio_accountid'];
	if ($room['Room']['bridgeid'] == SMARTCLOUD_BRIDGEID)
        $room['Room']['audio_accountid'] = $room['SmartCloudRoom']['audio_accountid'];	

    // NB lookup tables would be nice and condense this but the corner
    // cases look pretty nasty
    switch($status) {
    case STATUS_ACTIVE:
      $sproc_name = 'MarkRoomActive';

      if($room['Room']['bridgeid'] == OCI_BRIDGEID)
      	$rv = $this->Oci->activateRoom($room);
      elseif($room['Room']['bridgeid'] == SPECTEL_BRIDGEID)
	$rv = $this->Spectel->activateRoom($room);
	  elseif($room['Room']['bridgeid'] == SPECTELATL_BRIDGEID)
	$rv = $this->SpectelAtl->activateRoom($room);
    elseif($room['Room']['bridgeid'] == SPECTELFR_BRIDGEID)
  $rv = $this->SpectelFr->activateRoom($room);
      elseif($room['Room']['bridgeid'] == INTERCALL_BRIDGEID)
	$rv = $this->Intercall->activateRoom($room);
      elseif($room['Room']['bridgeid'] == BT_BRIDGEID || 
             $room['Room']['bridgeid'] == WEBEX_BRIDGEID || 
			 $room['Room']['bridgeid'] == LIVE_MEETING_BRIDGEID)
	$rv = $this->Bt->activateRoom($room);
      elseif($room['Room']['bridgeid'] == WEBINTERPOINT_BRIDGEID)
	$rv = $this->WebinterpointAccount->createAccount($room['Room']);
    else
    if($room['Room']['bridgeid'] == SMARTCLOUD_BRIDGEID)
	   $rv = $this->SmartCloudAccount->createAccount($room['Room']);	
	 else  
	$rv = true;
      break;

    case STATUS_SUSPENDED:
      $sproc_name = 'MarkRoomSuspended';

      if($room['Room']['bridgeid'] == OCI_BRIDGEID)
	$rv = $this->Oci->suspendRoom($room);
      elseif($room['Room']['bridgeid'] == SPECTEL_BRIDGEID)
	$rv = $this->Spectel->suspendRoom($room);
     elseif($room['Room']['bridgeid'] == SPECTELATL_BRIDGEID)
	$rv = $this->SpectelAtl->suspendRoom($room);
     elseif($room['Room']['bridgeid'] == SPECTELFR_BRIDGEID)
  $rv = $this->SpectelFr->suspendRoom($room);
      elseif($room['Room']['bridgeid'] == INTERCALL_BRIDGEID)
	$rv = $this->Intercall->suspendRoom($room);
      elseif($room['Room']['bridgeid'] == BT_BRIDGEID || 
             $room['Room']['bridgeid'] == WEBEX_BRIDGEID || 
             $room['Room']['bridgeid'] == LIVE_MEETING_BRIDGEID)
	$rv = $this->Bt->suspendRoom($room);
      elseif($room['Room']['bridgeid'] == WEBINTERPOINT_BRIDGEID)
	$rv = $this->WebinterpointAccount->deleteAccount($room);
	else
	if($room['Room']['bridgeid'] == SMARTCLOUD_BRIDGEID)
	 $rv = $this->SmartCloudAccount->deleteAccount($room);
	else  
	 $rv = true;
      break;

    case STATUS_CANCELLED:
      $sproc_name = 'MarkRoomCancelled';

      if($room['Room']['bridgeid'] == OCI_BRIDGEID)
	$rv = $this->Oci->cancelRoom($room);
      elseif($room['Room']['bridgeid'] == SPECTEL_BRIDGEID)
	$rv = $this->Spectel->cancelRoom($room);
	 elseif($room['Room']['bridgeid'] == SPECTELATL_BRIDGEID)
	$rv = $this->SpectelAtl->cancelRoom($room);
   elseif($room['Room']['bridgeid'] == SPECTELFR_BRIDGEID)
  $rv = $this->SpectelFr->cancelRoom($room);
      elseif($room['Room']['bridgeid'] == INTERCALL_BRIDGEID)
	$rv = $this->Intercall->cancelRoom($room);
      elseif($room['Room']['bridgeid'] == BT_BRIDGEID || 
             $room['Room']['bridgeid'] == WEBEX_BRIDGEID || 
             $room['Room']['bridgeid'] == LIVE_MEETING_BRIDGEID)
	$rv = $this->Bt->cancelRoom($room);
      elseif($room['Room']['bridgeid'] == WEBINTERPOINT_BRIDGEID)
	$rv = $this->WebinterpointAccount->deleteAccount($room);
	else
	if($room['Room']['bridgeid'] == SMARTCLOUD_BRIDGEID)
	$rv = $this->SmartCloudAccount->deleteAccount($room);
	else
	$rv = true;
      break;

    case STATUS_TRIAL:
      $rv         = true;
      $sproc_name = null;
      break;
     
    default:
      $rv              = false;
      $sproc_name      = null;
      $this->error_msg = 'Change to unknown room status: ' . $status; 
      break;
    }    

    if($rv) {

      if($sproc_name) {
	$data = Array('accountid' => $room['Room']['accountid']);

	if(!empty($reason)) {
	  if(!empty($room['Room']['note3']))
	    $data['reason'] = sprintf('Status change reason: %s | %s', $reason, $room['Room']['note3']);
	  else
	    $data['reason'] = sprintf('Status change reason: %s', $reason);
	}

	$this->sproc($sproc_name, $data);      
      }

    } else {
      $this->error_msg = $this->getErrorMessage($room['Room']['bridgeid']);
    }

    return $rv;
  }

  function pull($acctgrpid, $accountid, $base_off_migration=true)
  {
    $room     = Array();
    $bridgeid = $this->accountid2bridge($accountid);

    switch($bridgeid) {  
    case OCI_BRIDGEID:
      $data = $this->Oci->pull($accountid);
      break;
      
    case SPECTEL_BRIDGEID:
      $data = $this->Spectel->pull($accountid);
      break;
	  
	case SPECTELATL_BRIDGEID:
      $data = $this->SpectelAtl->pull($accountid);
      break;

  case SPECTELFR_BRIDGEID:
      $data = $this->SpectelFr->pull($accountid);
      break;
      
    case INTERCALL_BRIDGEID:
      $data = $this->Intercall->pull($accountid);
      break;

    case BT_BRIDGEID:
      $data = $this->Bt->pull($accountid);
      break;

    default:
      $data            = null;
      $this->error_msg = 'Unknown bridge id: ' . $bridgeid;
      break;
    }

    if($data) {
      if($base_off_migration && $orig = $this->find(Array('acctgrpid' => $acctgrpid, 
							  'cec'       => $data['cec'], 
							  'pec'       => $data['pec'], 
							  'bridgeid'  => '<>' . $bridgeid ))) { 
	$room = $orig['Room'];
	unset($room['tstamp']);
      } else {
	$room = Array();
      }

      $room['acctgrpid'] = $acctgrpid;
      $room['accountid'] = $accountid;
      
      foreach($data as $k => $v)
	$room[$k] = $v;
    } 

    return $room;
  }
  
  function roomExists($accountid)
  {
    return $this->findCount(Array('accountid' => $accountid)) > 0;
  }

  function sync($room)
  {    
    $rv              = null;
    $this->error_msg = null;

    if($bridge_settings = $this->pull($room['Room']['acctgrpid'], $room['Room']['accountid'], false)) { 

      $data = Array();
      foreach($bridge_settings as $k => $v) {

	if(!is_null($bridge_settings[$k]) && 
	   array_key_exists($k, $room['Room']) &&
	   (!in_array($k, Array('cec', 'pec')) || !empty($bridge_settings[$k])) && // Dont nuke codes (opassit rooms)
	   (is_null($room['Room'][$k]) || (string)$room['Room'][$k] !== (string)$bridge_settings[$k])) { 
	  $data[$k] = $v;
	}
      }

      if($data) {
	if($new_room = $this->update($room, $data)) { 
	  $rv = $new_room;
	} else {
	  $this->error_msg = 'Could not save updated room';
	}
      } else {
	$rv = $room;
      }

    } else {
      $this->error_msg = 'Could not pull room from bridge';	
    }

    return $rv;
  }

  function findMigrations($room, $match_accountgroup=false) {
    $rv = Array();

    if(!empty($room['Room']['cec']) && !empty($room['Room']['pec'])) { 
      $criteria = Array('Room.bridgeid' => '<>' . $room['Room']['bridgeid'], 
			'Room.cec'      => $room['Room']['cec'], 
            'Room.pec'      => $room['Room']['pec'],
            'Bridge.type'   => 'AUDIO' );
     

      if($match_accountgroup)
	$criteria['Room.acctgrpid'] = $room['Room']['acctgrpid'];

      $rv = $this->findAll($criteria);
    }
    
    return $rv;
  }

  function isCodeAvailable($code)
  {
    return $this->findCount(Array('OR' => Array('cec'  => $code, 
						'pec'  => $code,
						'prec' => $code ))) == 0;
  }
}
