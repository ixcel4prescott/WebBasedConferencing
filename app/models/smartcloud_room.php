<?

class SmartCloudRoom extends AppModel
{
  var $name        = 'SmartCloudRoom';
  var $useTable    = 'account_smartcloud';
  var $primaryKey  = 'web_accountid';

  var $validate = Array( 'web_accountid' => 'VALID_NOT_EMPTY');


  var $belongsTo = Array('audio_room' => Array('className'  => 'Room',
                                               'foreignKey' => 'audio_accountid'),
                         'web_room'   => Array('className'  => 'Room',
                                               'foreignKey' => 'web_accountid'));

}
