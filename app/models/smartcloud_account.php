<?

class SmartCloudAccount extends AppModel
{
   var $primaryKey = 'id';
   var $useTable   = 'smartcloud_accounts';

   var $output;
   var $error_msg = null;

   function findAccount($room)
   {
     return $this->find(Array('cec' => $room['Room']['cec'], 'pec' => $room['Room']['pec']));
   }

   function createAccount($room) 
   {
        
     $args = Array( 'a' => $room['acctgrpid'],
		    'c' => $room['audio_accountid'],
		    'C' => $room['cec'],
		    'P' => $room['pec'], 
		    'p' => $room['maximumconnections'],
			'd' => $xml);
			
	  exec("python create_room_SC.py", $args);
	  
      $exit_status = spawn('smartcloud'.DS.'create_room_smartcloud.pl', $args, $this->output, SCRIPT_ROOT);
     
     $rv = null;
     if($exit_status === 0 && preg_match('/Created room: (WI\d+)/', $this->output[1], $matches) > 0) {
       $rv = $matches[1];
     } else {
       $this->error_msg = 'Could not create SmartCloud room: ' + $this->output[2];
     }

     return $rv;
   } 

   function deleteAccount($room) 
   {
     $args = Array( 'C' => $room['Room']['cec'],
		    'P' => $room['Room']['pec'] );
     
     $exit_status = spawn('smartcloud'.DS.'delete_room_smartcloud.pl', $args, $this->output, SCRIPT_ROOT);

     $rv = false;
     if($exit_status === 0) {
       $rv = true;
     } else {
       $this->error_msg = 'Could not create smartcloud room: ' + $this->output[2];
     }

     return $rv;
   }

   function updateAccount($room, $data) 
   {
     $args = Array('C' => $room['Room']['cec'],
            'p' => array_key_exists('maximumconnections', $data) ? $data['maximumconnections'] : $room['Room']['maximumconnections'] );

     pr($args);

     $exit_status = spawn('smartcloud'.DS.'update_room_smartcloud.pl', $args, $this->output, SCRIPT_ROOT);

     pr($this->error_msg);
     
     $rv = false;
     if($exit_status === 0) {
       $rv = true;
     } else {
       $this->error_msg = 'Could not update smartcloud room: ' + $this->output[2];
     }

     return $rv;
   }
}

