<?php

class RoomView extends AppModel
{
  var $name       = 'RoomView';
  var $useTable   = 'room_view';
  var $primaryKey = 'accountid';
	
  var $belongsTo = Array('Account'      => Array( 'className'  => 'Account',
						  'foreignKey' => 'acctgrpid' ),
			 
			 'Bridge'       => Array( 'className'  => 'Bridge',
						  'foreignKey' => 'bridgeid' ),

			 'DialinNumber' => Array( 'className'  => 'DialinNumber',
						  'foreignKey' => 'dialinNoid' ),

			 'ServiceRate'  => Array( 'className'  => 'ServiceRate', 
						  'foreignKey' => 'servicerate' ),
       'DefaultBridge'      => Array( 'className'  => 'DefaultBridge',
              'foreignKey' => 'acctgrpid' ),
			 );

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

  var $hasOne = array('WebinterpointRoom' => array('className' => 'WebinterpointRoom',
                                                   'foreignKey' => 'web_accountid'),
					  'SmartCloudRoom' => array('className' => 'SmartCloudRoom',
                                           'foreignKey' => 'web_accountid'),						   
                      'WebexRoom' => array('className' => 'WebexRoom',
                                           'foreignKey' => 'web_accountid'),
                      'LiveMeetingRoom' => array('className' => 'LiveMeetingRoom',
                                           'foreignKey' => 'web_accountid'),
                      'WebexInfo' => array('className' => 'WebexRoom',
                                           'foreignKey' => 'audio_accountid'),
                      'LiveMeetingInfo' => array('className' => 'LiveMeetingRoom',
                                           'foreignKey' => 'audio_accountid'));

  function get($accountid, $user) 
  {
    $room= $this->read(null, $accountid);

    if($room) {
      if(!is_null($user['Resellers'])) {
        if(!in_array($room['RoomView']['resellerid'], $user['Resellers']))
	      return null;
      }
      if($user['User']['level_type'] == SALESPERSON_LEVEL and 
        ($user['Salespersons'] == null or 
        !in_array($room['RoomView']['salespid'], $user['Salespersons']))){
          return null;
      }
    }
    return $room;
  }
}
