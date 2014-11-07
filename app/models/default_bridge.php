<?

class DefaultBridge extends AppModel
{
   var $primaryKey  = 'acctgrpid';
   var $useTable    = 'default_bridge';
   
   var $validate    = Array( 'webinterpoint_url' => VALID_URL );
}