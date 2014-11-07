<?

class BaseController extends controller
{
  var $uses       = array();
  var $helpers    = array();
  var $components = array();

  // stopwatch for timing actions
  var $actionStart;

  // define actions that we omit from checkAccess()
  var $publicActions = array();

  // define actions that must be over https
  var $secureActions = false;

  // Make sure we always have system available for logging
  function __construct() 
  {
    if(MAINTENANCE_MODE) {
      $this->uses = Array();
    } else {
      if(!in_array('System', $this->uses))
	$this->uses[] = 'System';

      if(!in_array('DiffLog', $this->uses))
	$this->uses[] = 'DiffLog';
    }    

    if(!in_array('Session', $this->components))
      $this->components[] = 'Session';

    if(!in_array('Html', $this->helpers))
      $this->helpers[] = 'Html'; 

    if(!in_array('Javascript', $this->helpers))
      $this->helpers[] = 'Javascript';

    parent::__construct();
  }

  // Helper to return a proper float microtime
  private function microtime()
  {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
  }

  // Inserts a record into system log
  function systemLog($category=null, $comments=null)
  {    
    if ($this->Session->check('User')){
      $user = $this->Session->read('User');
      $userid = $user['User']['id'];
    }else{
      $userid = null;
    }

    return $this->System->log($_SERVER['SERVER_NAME'], $this->name, $this->action, $_SERVER['REQUEST_METHOD'], $this->params, $_SERVER['REMOTE_ADDR'], $userid, $category, $comments);
  }

  function diffLog($entity, $op, $object_id, $new=null, $old=null)
  {
    if($this->Session->check('User')) {
      $user = $this->Session->read('User');
      $userid = $user['User']['id'];
    } else {
      $userid = null;
    }
    
    return $this->DiffLog->log($_SERVER['HTTP_HOST'], $entity, $op, $object_id, $_SERVER['REMOTE_ADDR'], $userid, $new, $old);
  }

  function beforeFilter()
  {
    if(MAINTENANCE_MODE && $_SERVER['REQUEST_URI'] != '/maintenance') { 
      $this->redirect('/maintenance');
      exit();
    } elseif(!MAINTENANCE_MODE && $_SERVER['REQUEST_URI'] == '/maintenance') { 
      $this->redirect('/');
      exit();
    } elseif(!MAINTENANCE_MODE) {
      $this->actionStart = microtime(true);

      if(is_array($this->secureActions)) 
	$secure = isset($this->secureActions[$this->action]) ? $this->secureActions[$this->action] : false;
      else
	$secure = $this->secureActions;

      $is_https = env('HTTPS');
    
      // check both that we are not accessing: 
      //  - a secure url over http
      //  - an insecure url over https
      if( ($secure && empty($is_https)) || (!$secure && !empty($is_https))) {
	$this->redirect(sprintf('%s://%s/%s',  $secure ? 'https' : 'http', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
	exit();
      }

      if(!$this->Session->check('User')&& !in_array($this->action, $this->publicActions)) {
	// Force the user to login
	$this->Session->setFlash('You must login first');
	$this->systemLog('login redirect');
	$this->redirect(sprintf('/users/login?back=%s', $_SERVER['REQUEST_URI']));
	exit();
      } elseif($this->Session->check('User') && !$this->isAllowed()) {      
	// Force the user to login
	$this->Session->setFlash('Permission Denied');
	$this->systemLog('permission denied');
	$this->redirect('/');
	exit();      
      }    
    }
  }

  // Log how long action took
  function afterFilter()
  {
    if(!MAINTENANCE_MODE && in_array('System', $this->uses)) {
      $action_time = microtime(true) - $this->actionStart;
      $this->systemLog('action profile', sprintf('%.04fs', $action_time));
    }
  }
}

