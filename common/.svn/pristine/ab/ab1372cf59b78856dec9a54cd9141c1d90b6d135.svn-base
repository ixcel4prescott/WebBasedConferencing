<?

class Bt extends AppModel
{
  var $useTable  = false;
  var $error_msg = null;
  var $output;

  var $start_modes = Array( 0 => 'INTERACTIVE',
			    1 => 'MUSIC_AWAITING_CHAIR',
			    2 => 'MUSIC_TO_PRESENTATION' );

  var $announcement_syms = Array( 0 => 'NONE', 
				  1 => 'TONE', 
				  2 => 'NAME' );

  var $basic_features = Array( 'namerecording',
			       'endonchairhangup',
			       'dialout',
			       'digitentry1' );

  function buildFeatures($room) {
    $rv = Array();

    if(isset($room['startmode']))
      $rv[] = sprintf('startmode=%s', $this->start_modes[$room['startmode']]);
    
    foreach(Array('entryannouncement', 'exitannouncement') as $f)
      if(isset($room[$f]))
	$rv[] = sprintf('%s=%s', $f, $this->announcement_syms[$room[$f]]);

    foreach($this->basic_features as $f)
      if(isset($room[$f]))
	$rv[] = sprintf('%s=%d', $f, $room[$f]);
      
    return $rv;
  }

  function saveAccount($account, $update_only=false)
  {
    $args = Array('a' => $account['Account']['acctgrpid']);

    if($update_only)
      $args['u'] = null;

    $exit_status = spawn('bt'.DS.'save_bt_account.py', $args, $this->output, SCRIPT_ROOT);    

    if($exit_status === 0) {
      $rv = true;      
    } else {
      $rv = false;
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function disableAccount($account)
  {
    $args        = Array('a' => $account['Account']['acctgrpid']);
    $exit_status = spawn('bt'.DS.'disable_bt_account.py', $args, $this->output, SCRIPT_ROOT);    

    if($exit_status === 0) {
      $rv = true;      
    } else {
      $rv = false;
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function saveContact($contact, $update_only=false)
  {
    $args = Array('t' => $contact['Contact']['id']);

    if($update_only)
      $args['u'] = null;

    $exit_status = spawn('bt'.DS.'save_bt_contact.py', $args, $this->output, SCRIPT_ROOT);    

    if($exit_status === 0) {
      $rv = true;      
    } else {
      $rv = false;
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function disableContact($contact)
  {
    $args        = Array('t' => $contact['Contact']['id']);
    $exit_status = spawn('bt'.DS.'disable_bt_contact.py', $args, $this->output, SCRIPT_ROOT);    

    if($exit_status === 0) {
      $rv = true;      
    } else {
      $rv = false;
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function createRoom($room) 
  {
    $rv = null;

    $args = Array( 't'   => $room['contact_id'],
		   'cec' => $room['cec'],
		   'pec' => $room['pec'],
		   'm'   => $room['maximumconnections'],
		   'f'   => $this->buildFeatures($room) );

    $exit_status = spawn('bt'.DS.'save_bt_meeting.py', $args, $this->output, SCRIPT_ROOT);    

    if($exit_status === 0) {      
      if(preg_match('/Created room: (MR\d+)/', $this->output[1], $matches) > 0) {
	$rv = $matches[1];
      } else {
	$this->error_msg = 'Could not extract confirmation number for newly created room';
      }
      
    } else {
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function updateRoom($room, $data)
  {
    $rv = false;

    $args = Array( 'c' => $room['Room']['accountid'],
		   'f' => $this->buildFeatures($data), 
		   'u' => null );

    if(isset($data['maximumconnections']))
      $args['m'] = $data['maximumconnections'];

    $exit_status = spawn('bt'.DS.'save_bt_meeting.py', $args, $this->output, SCRIPT_ROOT);

    if($exit_status === 0) {
      $rv = true;      
    } else {
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }
  
  function activateRoom($room)
  {
    $rv          = false;
    $args        = Array('c' => $room['Room']['accountid']);
    $exit_status = spawn('bt'.DS.'reactivate_bt_meeting.py', $args, $this->output, SCRIPT_ROOT);

    if($exit_status === 0) {
      $rv = true;
    } else {
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function suspendRoom($room)
  {
    $rv          = false;
    $args        = Array('c' => $room['Room']['accountid']);
    $exit_status = spawn('bt'.DS.'suspend_bt_meeting.py', $args, $this->output, SCRIPT_ROOT);

    if($exit_status === 0) {
      $rv = true;
    } else {
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }

  function cancelRoom($room)
  {
    $rv          = false;
    $args        = Array('c' => $room['Room']['accountid']);
    $exit_status = spawn('bt'.DS.'cancel_bt_meeting.py', $args, $this->output, SCRIPT_ROOT);

    if($exit_status === 0) {
      $rv = true;
    } else {
      $this->error_msg = $this->output[2];
    }

    return $rv;    
  }

  function pull($accountid)
  {
    return null;
  }
}