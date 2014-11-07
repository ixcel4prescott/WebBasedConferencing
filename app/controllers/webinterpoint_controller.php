<?

class WebinterpointController extends AppController
{
/*
Possibly not used DC -2012/09/13
  var $uses = Array('WebinterpointAccount', 'Room', 'Request');
  
  function create($accountid=null)
  {
    $this->layout = 'ajax';
    Configure::write('debug', 0);

    $user = $this->Session->read('User');
    $this->set('user', $user);

    $rv = false;

    if($room = $this->Room->get($accountid, $user)) {
      if($this->data) {
	$this->Request->saveRequest(REQTYPE_WEBINTERPOINT_CREATION, $user['User']['id'], $room['Room']['acctgrpid'], 
				    $room['Room']['accountid'], null, REQSTATUS_APPROVED);
	$rv = true;
      }
    }

    $this->set('rv', $rv);
  }
 */
}
