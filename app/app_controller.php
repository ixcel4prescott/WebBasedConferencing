<?
common('base_controller');

class AppController extends BaseController
{
  var $permissions   = GROUP_ALL;
  var $secureActions = true;
  
  function __construct()
  {
    $this->secureActions = DEBUG == 0;
    parent::__construct();
  }

  protected function addHistory($name)
  {
    if($this->Session->check('history'))
      $history = $this->Session->read('history');
    else
      $history = Array();

    for($i=0; $i<count($history); $i++) {         
      if($history[$i]['url'] == $_SERVER['REQUEST_URI']) {
	unset($history[$i]);
	break;
      }
    }

    array_unshift($history, Array('name' => $name, 'url' => $_SERVER['REQUEST_URI']));

    while(count($history) > MAX_HISTORY_SIZE) 
      array_pop($history);

    $this->Session->write('history', $history);
  }

  protected function isAllowed()
  {
    $rv     = false;
    $user   = $this->Session->read('User');

    $groups = Array('account'      => GROUP_ACCOUNTS, 
		    'accountgroup' => GROUP_ACCOUNTGROUPS, 
		    'admin'        => GROUP_ADMINS, 
		    'reseller'     => GROUP_RESELLERS, 
            'salesperson'  => GROUP_SALESPEOPLE,
            'reseller_admin' => GROUP_RESELLER_ADMINS);

    $user_group = $groups[$user['User']['level_type']];

    if(is_int($this->permissions)) {
      if($this->permissions & $user_group)
	$rv = true;

      if(($this->permissions & GROUP_IC) && $user['User']['ic_employee'] == 0)
	$rv = false;
      
    } elseif(is_array($this->permissions)) {
      if(isset($this->permissions[$this->action]) && ($this->permissions[$this->action] & $user_group)) {
	$rv = true;
	
	if(($this->permissions[$this->action] & GROUP_IC) && $user['User']['ic_employee'] == 0)
	  $rv = false;	
      }
    } 
    
    return $rv;
  }
}
