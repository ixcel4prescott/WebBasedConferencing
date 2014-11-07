<?

class UsageController extends AppController
{
  var $uses       = Array('Usage', 'Account', 'Room');
  var $components = Array('Pagination');
  var $helpers    = Array('Html', 'Pagination'); 

  var $permissions = GROUP_ALL;
  
  var $export_keys = Array('accountid', 'startdate', 'starttime', 'calltype_text', 'invoicenum', 
			   'billingcode', 'participant_type', 'minutes', 'dnis', 'ani', 'username');
  
  var $export_headers = Array( 'accountid'        => 'Confirmation Number', 
			       'startdate'        => 'Start Date', 
			       'starttime'        => 'Start Time', 
			       'calltype_text'    => 'Call Type', 
			       'invoicenum'       => 'Invoice', 
			       'billingcode'      => 'Billing Code',
			       'participant_type' => 'Caller Type', 
			       'minutes'          => 'Minutes', 
			       'dnis'             => 'Dialed Number', 
			       'ani'              => 'Incoming Number', 
			       'username'         => 'Username' );

  function format_export($row) 
  {
    return Array( 'accountid'        => $row['Usage']['accountid'], 
		  'startdate'        => date('m/d/Y', $row['Usage']['starttime_t']),
		  'starttime'        => date('h:i:s', $row['Usage']['starttime_t']),
		  'calltype_text'    => $row['Usage']['calltype_text'], 
		  'invoicenum'       => $row['Usage']['invoicenum'], 
		  'billingcode'      => $row['Usage']['billingcode'], 
		  'participant_type' => $row['Usage']['participant_type'], 
		  'minutes'          => $row['Usage']['minutes'], 
		  'dnis'             => format_phone($row['Usage']['dnis']), 
		  'ani'              => format_phone($row['Usage']['ani']), 
		  'username'         => $row['Usage']['username'] );

  }

  private function build_filter($criteria) 
  {

    $start = !empty($_GET['start']) ? $_GET['start'] : '01/01/2000';
    $end   = !empty($_GET['end']) ? $_GET['end'] : date('m/d/Y');

    $start_time = $start . ' 00:00:00';
    $end_time   = $end . ' 23:59:59';
    
    $criteria['Usage.starttime_t'] = 'BETWEEN ' . strtotime($start_time) . ' AND ' . strtotime($end_time);

    return $criteria;
  }

  function account($acctgrpid=null)
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);

    if($account = $this->Account->get($acctgrpid, $user)) {
      $this->set('account', $account);

      $criteria = $this->build_filter(Array('acctgrpid' => $acctgrpid));
       
      list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'starttime_t DESC'));      

      if(!empty($_GET['export'])) {
	list($limit, $page) = Array(null, null);
	Configure::write('debug', 0);
	$this->layout = 'ajax';
      }

      $data = $this->Usage->findAll($criteria, NULL, $order, $limit, $page);      

      if(!empty($_GET['export'])) {

	$filename = sprintf('Account %s Usage %s to %s', 
			    $acctgrpid, 
			    str_replace('/', '-', $_GET['start']), 
			    str_replace('/', '-', $_GET['end']));
	export_csv($filename, $this->export_keys, $this->export_headers, $data, Array($this, 'format_export'));
	die;
      } else { 
	$this->set('data', $data);
      }

    } else {
      $this->Session->setFlash('Account not found');
      $this->redirect('/');
    }
  }

  function room($accountid=null) 
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);

    if($room = $this->Room->get($accountid, $user)) {
      $this->set('room', $room);

      $criteria = $this->build_filter(Array('accountid' => $accountid));

      list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'starttime_t DESC'));      

      if(!empty($_GET['export'])) {
        list($limit, $page) = Array(null, null);
        Configure::write('debug', 0);
        $this->layout = 'ajax';
      }

      $data = $this->Usage->findAll($criteria, NULL, $order, $limit, $page);      

      if(!empty($_GET['export'])) {
        $filename = sprintf('Room %s Usage %s to %s',
          $accountid,
          str_replace('/', '-', $_GET['start']),
          str_replace('/', '-', $_GET['end']));
        export_csv($filename, $this->export_keys, $this->export_headers, $data, Array($this, 'format_export'));
        die;
      } else { 
        $this->set('data', $data);
      }

    } else {
      $this->Session->setFlash('Room not found');
      $this->redirect('/');
    }
  } 
}