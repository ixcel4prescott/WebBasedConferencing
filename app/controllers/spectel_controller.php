<?

class SpectelController extends AppController
{
  var $uses       = Array('SpectelClient', 'SpectelReservation', 'Request');
  var $components = Array('Pagination', 'RequestHandler');
  var $helpers    = Array('Pagination'); 

  var $permissions = GROUP_IC_RESELLERS_AND_ADMINS;

  function index()
  {
    $is_ajax = $this->RequestHandler->isAjax();
    if($is_ajax) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
    }
    $this->set('is_ajax', $is_ajax);

    $criteria = Array();

    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $criteria['OR'] = Array( 'SpectelClient.ClientName'    => 'LIKE %' . $query . '%', 
			       'SpectelClient.ClientMainICC' => 'LIKE %' . $query . '%',
			       'SpectelClient.ContactName'   => 'LIKE %' . $query . '%' );
    } else {
      $query = '';
    }
    $this->set('query', $query);

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'ClientRef'));
    $this->set('data', $this->SpectelClient->findAll($criteria, NULL, $order, $limit, $page));
  }

  function view($client_ref=null)
  {
    if($client = $this->SpectelClient->read(null, $client_ref)) { 
      $this->set('client', $client);

      $is_ajax = $this->RequestHandler->isAjax();
      if($is_ajax) {
	Configure::write('debug', 0);
	$this->layout = 'ajax';
      }
      $this->set('is_ajax', $is_ajax);
      
      $is_post = $this->RequestHandler->isPost();

      $key = sprintf('Selections.spectel.%d', $client_ref);
      if($this->Session->check($key))
	$selections = $this->Session->read($key);
      else
	$selections = Array();
      $this->set('selections', $selections);

      if($is_post && !$is_ajax) { 
	if($selections) 
	  $this->redirect('/spectel/move/' . $client_ref);
	else
	  $this->Session->setFlash('Please select one or more rooms to move');
      }

      $query = !empty($_GET['query']) ? mssql_escape($_GET['query']) : '';
      $this->set('query', !empty($_GET['query']) ? stripslashes($_GET['query']) : '' );

      $total = $this->SpectelReservation->searchTotal($query, $client['SpectelClient']['ClientRef']);

      list($order, $limit, $page) = $this->Pagination->init(null, null, aa('modelClass', 'SpectelReservation', 'total', $total, 'sortBy', 'accountid'));
      $this->set('data', $this->SpectelReservation->search($query, $client['SpectelClient']['ClientRef'], $order, $limit, $page));
    } else {
      $this->Session->setFlash('Client not found');
      $this->redirect('/spectel');
    }
  }

  function select($client_ref=null)
  {
    if($client = $this->SpectelClient->read(null, $client_ref)) {
      $this->set('client', $client);

      if($is_ajax = $this->RequestHandler->isAjax()) {
	Configure::write('debug', 0);
	$this->layout = 'ajax';
      }
      $this->set('is_ajax', $is_ajax);

      $key = sprintf('Selections.spectel.%d', $client_ref);
      if($this->Session->check($key))
	$selections = $this->Session->read($key);
      else
	$selections = Array();

      if(!empty($_POST)) {
	if($_POST['reservation_ref'] == 'all') { 
	  foreach($this->SpectelReservation->findAll(Array('SpectelReservation.ClientRef' => $client_ref)) as $i)
	    if(array_search($i['SpectelReservation']['ReservationRef'], $selections) === False)
	      $selections[] = $i['SpectelReservation']['ReservationRef'];
	  
	} elseif($_POST['reservation_ref'] == 'none') { 
	  $selections = Array();

	} elseif(isset($_POST['value']) && !empty($_POST['value'])) {
	  if(array_search($_POST['reservation_ref'], $selections) === False)
	    $selections[] = $_POST['reservation_ref'];

	} elseif(isset($_POST['value'])) {
	  $idx = array_search($_POST['reservation_ref'], $selections);
	  if($idx !== false)
	    unset($selections[$idx]);
	} 
	  
	$this->set('selections', $selections);

	$this->Session->write($key, $selections);
      }
    } else {
      $this->Session->flash('Client not found');
      $this->redirect('/spectel');
    }
  }

  function move($client_ref=null)
  {
    if($client = $this->SpectelClient->read(null, $client_ref)) { 
      $this->set('client', $client);

      $user = $this->Session->read('User');

      $key = sprintf('Selections.spectel.%d', $client_ref);
      if($this->Session->check($key)) 
	$selections = $this->Session->read($key);
      else
	$selections = null;

      if($selections) { 
	$conferences = $this->SpectelReservation->getReservations($selections);
	$this->set('conferences', $conferences);

	if(!empty($this->data)) { 
	  $dest = !empty($this->data['SpectelReservation']['ClientRef']) ? $this->SpectelClient->read(null, $this->data['SpectelReservation']['ClientRef']) : null;

	  $reservations = Array();

	  if(isset($this->data['SpectelReservation']['ReservationRef'])) {
	    foreach($this->data['SpectelReservation']['ReservationRef'] as $r)
	      if(!empty($r))
		$reservations[] = $r;
	  }

	  if($dest && $reservations) {
	    foreach($reservations as $r) {
	      $data = Array('ReservationRef' => $r, 
			    'ClientRef'      => $dest['SpectelClient']['ClientRef'], 
			    'src'            => $this->data['SpectelClient']['src'] );


	      $this->Request->saveRequest(REQTYPE_SPECTEL_MOVE, $user['User']['id'], null, null, $data, 
					  $user['User']['level_type'] == 'reseller' ? REQSTATUS_APPROVED : REQSTATUS_PENDING);
	    }

	    $this->Session->setFlash('Your requests have been submitted');
	    $this->redirect('/spectel');
	  } else {

	    if(!$dest)
	      $this->SpectelClient->invalidate('ClientRef');

	    if(empty($reservations))
	      $this->Session->setFlash('Please select one or more rooms to move');

	  } 	  
	} else { 
	  $this->data = Array('SpectelReservation' => Array('ReservationRef' => $selections), 
			      'SpectelClient'     => Array('src' => $client_ref));
	}
      } else {
	$this->Session->setFlash('Please select one or more rooms to move');
	$this->redirect('/spectel/view/' . $client_ref);
      }
    } else {
      $this->Session->setFlash('Client not found');
      $this->redirect('/spectel');
    }
  }

  function search()
  {
    $this->layout = 'ajax';
    Configure::write('debug', 0);
    
    if(!empty($this->data)) {
      $criteria = Array( 'SpectelClient.ClientRef' => '<> ' . $this->data['SpectelClient']['src'],
			 'OR' => Array( 'SpectelClient.ClientName'    => 'LIKE %' . $this->data['SpectelReservation']['ClientRef'] . '%', 
					'SpectelClient.ClientMainICC' => 'LIKE %' . $this->data['SpectelReservation']['ClientRef'] . '%',
					'SpectelClient.ContactName'   => 'LIKE %' . $this->data['SpectelReservation']['ClientRef'] . '%' ));
      $max = 25;
      $clients = $this->SpectelClient->findAll($criteria, null, 'SpectelClient.ClientRef ASC', $max);
      $this->set('clients', $clients);
      $this->set('too_many', count($clients) == $max ? true : false);
    }
  }
}
