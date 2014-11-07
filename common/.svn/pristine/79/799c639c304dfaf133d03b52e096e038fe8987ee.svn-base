<?

class Intercall extends AppModel
{
  var $useTable  = false;
  var $error_msg = null;
  var $output;

  // These tables are used to map the numeric values from the account
  // table to the symbols used by the webservice

  var $announcement_syms = Array( 0 => 'SILENCE', 
				  1 => 'TONE', 
				  2 => 'NAME',
				  3 => 'TONE_NAME' );

  function buildFeatures($room) {

    $rv = Array('phone-pac=true');

    if(isset($room['startmode'])) {
      $rv[] = sprintf('quick-start=%s', $room['startmode'] == 0 ? 'yes' : 'no');
      $rv[] = 'quick-start-conf=no';
    }
    
    if(isset($room['endonchairhangup'])) {
      $rv[] = sprintf('auto-continuation=%s', $room['endonchairhangup'] == 0 ? 'yes' : 'no');
      $rv[] = 'auto-continuation-conf=no';
    }

    if(isset($room['namerecording'])) {
      $rv[] = sprintf('name-record=%s', $room['namerecording'] == 1 ? 'yes' : 'no');
      $rv[] = 'name-record-config=no';
    }

    if(isset($room['dialout']))
      $rv[] = sprintf('dialout-permission=%s', $room['dialout'] == 1 ? 'yes' : 'no');

    if(isset($room['entryannouncement']))
      $rv[] = sprintf('entry-announcement=%s', $this->announcement_syms[$room['entryannouncement']]);

    if(isset($room['exitannouncement']))
      $rv[] = sprintf('exit-announcement=%s', $this->announcement_syms[$room['exitannouncement']]);

    return $rv;
  }

  function createRoom($room) 
  {
    $rv = null;

    $args = Array( 'a'          => $room['acctgrpid'],
		   't'          => $room['contact'],
		   'cec'        => $room['cec'],
		   'pec'        => $room['pec'],
		   'dialinNoid' => $room['dialinNoid'],
		   'm'          => $room['maximumconnections'],
		   'f'          => $this->buildFeatures($room) );

    $exit_status = spawn('intercall'.DS.'create_room_intercall.pl', $args, $this->output, SCRIPT_ROOT);    

    if($exit_status === 0) {
      
      if(preg_match('/Created room: (I\d+)/', $this->output[1], $matches) > 0) {
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

    $args = Array( 'a'          => $room['Room']['acctgrpid'],
		   'c'          => $room['Room']['accountid'],
		   't'          => isset($data['contact']) ? $data['contact'] : $room['Room']['contact'],
		   'cec'        => $room['Room']['cec'],
		   'pec'        => $room['Room']['pec'],
		   'dialinNoid' => $room['Room']['dialinNoid'],
		   'm'          => isset($data['maximumconnections']) ? $data['maximumconnections'] : $room['Room']['maximumconnections'],
		   'f'          => $this->buildFeatures($data) );

    $exit_status = spawn('intercall'.DS.'update_room_intercall.pl', $args, $this->output, SCRIPT_ROOT);

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
    $exit_status = spawn('intercall'.DS.'activate_room_intercall.pl', $args, $this->output, SCRIPT_ROOT);

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
    $exit_status = spawn('intercall'.DS.'suspend_room_intercall.pl', $args, $this->output, SCRIPT_ROOT);

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
    $exit_status = spawn('intercall'.DS.'delete_room_intercall.pl', $args, $this->output, SCRIPT_ROOT);

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