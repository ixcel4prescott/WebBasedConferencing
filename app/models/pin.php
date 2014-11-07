<?

class Pin extends AppModel
{
   var $useTable    = 'pin_codes';

   var $validate = Array( 'bridgeid'   => '/^\d+$/',
			  'acctgrpid'  => VALID_NOT_EMPTY, 
			  'first_name' => VALID_NOT_EMPTY, 
			  'last_name'  => VALID_NOT_EMPTY,
			  'pin'        => '/^\d{4,}$/' );

   var $belongsTo = Array( 'Account'      => Array( 'className'  => 'AccountView',
						   'foreignKey' => 'company' ));

   function beforeValidate()
   {

     if($found = $this->find(Array('pin' => $this->data['Pin']['pin'], 'active' => 1 ))) { 

       // if the pin is found on the same bridge, invalid
       if($found['Pin']['bridgeid'] == $this->data['Pin']['bridgeid'] && $found['Pin']['external_id'] != $this->data['Pin']['external_id']) 
	 $this->invalidate('pin');
       
       // if pin is found on a different bridge and a different company, invalid
       elseif($found['Pin']['bridgeid'] != $this->data['Pin']['bridgeid'] && $found['Pin']['company'] != $this->data['Pin']['company'])
	 $this->invalidate('pin');
     }

     if(!in_array($this->data['Pin']['bridgeid'], Array(OCI_BRIDGEID, SPECTEL_BRIDGEID)))
       $this->invalidate('bridgeid');

     return true;
   }
   
   function generate($len=7) 
   {
     $min = (int)pow(10, $len-1);
     $max = (int)pow(10, $len)-1;
     
     list($usec, $sec) = explode(' ', microtime());
     mt_srand((float) $sec + ((float) $usec * 100000));

     $code = (string)mt_rand($min, $max);
     
     $iterations = 0;
     do {
       $found = $this->find(aa('pin', $code, 'active', 1));
       $code = (string)mt_rand($min, $max);
       $iterations++;
     } while($found && $iterations < 1000);
     
     return $iterations == 1000 ? false : $code;
   }

   private function pin_script($bridgeid) 
   {
     $script = null;

     switch($bridgeid) {
     case SPECTEL_BRIDGEID:
       $script = 'avaya'.DS.'spectel_pins.pl';
       break;

     case OCI_BRIDGEID:
       $script = 'oci'.DS.'oci_pins.pl';
       break;

     default:
       break;
     }

     return $script;
   }

   function createPin($pin)
   {
     $rv = null;

     if($script = $this->pin_script($pin['bridgeid'])) {
       $args = Array( 'm' => 'create',
		      'a' => $pin['company'],
		      'f' => $pin['first_name'],
		      'l' => $pin['last_name'],
		      'p' => $pin['pin'] );

       $exit_status = spawn($script, $args, $this->output, SCRIPT_ROOT);
     
       if($exit_status === 0) {
	 if(preg_match('/PIN Id: (\d+)/', $this->output[1], $matches)) {

	   $rv = $this->find(Array('external_id' => $matches[1], 'bridgeid' => $pin['bridgeid']));
 	  
	 } else {
	   $this->error_msg = 'Could not pull Pin ID for PIN: ' . $pin;	   
	 }
       } else {
	 $this->error_msg = $this->output[2];
       }
     } else {
       $this->error_msg = 'unknown bridgeid: ' . $bridgeid;
     }
     
     return $rv;
   }

   function updatePin($pin, $data) 
   {
     $rv = null;

     if($script = $this->pin_script($pin['Pin']['bridgeid'])) {
       $args = Array( 'm' => 'update',
		      'u' => $data['external_id'], 
		      'f' => $data['first_name'],
		      'l' => $data['last_name'],
		      'a' => $data['company'],
		      'p' => $data['pin'] );

       $exit_status = spawn($script, $args, $this->output, SCRIPT_ROOT);
       if($exit_status === 0) {

	 $rv = $this->read(null, $pin['Pin']['id']);

       } else {
	 $this->error_msg = $this->output[2];
       }

     } else {
       $this->error_msg = 'unknown bridgeid: ' . $bridgeid;
     }
     
     return $rv;
   }

   function deletePin($pin) 
   {
     $rv = null;

     if($script = $this->pin_script($pin['Pin']['bridgeid'])) {
       $args = Array( 'm' => 'delete',
		      'u' => $pin['Pin']['external_id'] );

       $exit_status = spawn($script, $args, $this->output, SCRIPT_ROOT);
       if($exit_status === 0) {

	 $rv = $this->read(null, $pin['Pin']['id']);

       } else {
	 $this->error_msg = $this->output[2];
       }

     } else {
       $this->error_msg = 'unknown bridgeid: ' . $bridgeid;
     }
     
     return $rv;     
   }
}
