<?

class WebexRoom extends AppModel
{
  var $name        = 'WebexRoom';
  var $useTable    = 'account_webex';
  var $primaryKey  = 'web_accountid';
  var $error_msg = null;
  
  var $validate = Array( 'web_accountid' => 'VALID_NOT_EMPTY');

  var $belongsTo = Array('audio_room' => Array('className'  => 'Room',
                                               'foreignKey' => 'audio_accountid'),
                         'web_room'   => Array('className'  => 'Room',
                                               'foreignKey' => 'web_accountid'));

  function createRoom($room) 
  {
    $rv = null;

    //FIXME: Remove the debug flag to point at production
    $args = Array( 't'   => $room['contact_id'],
                   's'   => 'WEBEX');
    $exit_status = spawn('bt'.DS.'save_bt_web_meeting.py', $args, $this->output, SCRIPT_ROOT);

    if($exit_status === 0) {      
      if(preg_match('/Created room: (W\d+)/', $this->output[1], $matches) > 0) {
	$rv = $matches[1];
      } else {
	$this->error_msg = 'Could not extract confirmation number for newly created room';
      }
      
    } else {
      $this->error_msg = $this->output[2];
    }

    return $rv;
  }
}
