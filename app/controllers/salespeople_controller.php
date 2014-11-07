<?

class SalespeopleController extends AppController
{
  var $uses       = Array('Salesperson', 'Reseller');
  var $components = Array('Pagination');
  var $helpers    = Array('Pagination'); 

  var $permissions = GROUP_IC_RESELLERS;

  function create() 
  {
    $this->set('resellers', $this->Reseller->buildList());

    if(!empty($this->data)) {

      $people = Array();
      foreach($this->data['Salesperson']['person'] as $p) {
	if(!empty($p['firstname']) && !empty($p['lastname']) && is_numeric($p['commission'])) {
	  $people[] = $p['firstname'][0] . ' ' . $p['lastname'] . ' ' . $p['commission'] . '%';

	  if(!isset($this->data['Salesperson']['accountmanager']))
	    $this->data['Salesperson']['accountmanager'] = $p['firstname'] . ' ' . $p['lastname'];
	}
      }
      
      $reseller_prefixes = $this->Reseller->generateList(null, null, null, '{n}.Reseller.resellerid', '{n}.Reseller.racctprefix');

      if($people && !empty($this->data['Salesperson']['resellerid'])) {
	$reseller_prefix = $reseller_prefixes[$this->data['Salesperson']['resellerid']];
	$this->data['Salesperson']['name'] = $reseller_prefix . '-'  . implode(' - ', $people);	
      }

      $this->Salesperson->set($this->data);
      if($this->Salesperson->validates($this->data)) { 
	if($this->Salesperson->save($this->data)) { 	
	  $this->Session->setFlash('Salesperson created');
	  $this->redirect('/salespeople/view/' . $this->Salesperson->getLastInsertID());
	} else {
	  $this->Session->setFlash('Could not save salesperson');
	}
      }
    } else {
      $this->data = Array('Salesperson' => Array('person' => Array(0 => Array('firstname'  => '',
									      'lastname'   => '',
									      'commission' => ''),
								   1 => Array('firstname'  => '',
									      'lastname'   => '',
									      'commission' => ''),
								   2 => Array('firstname'  => '',
									      'lastname'   => '',
									      'commission' => ''))));
    }
  }

  function edit($salespid=null) 
  {
    if($salesperson = $this->Salesperson->read(null, $salespid)) {
      $this->set('salesperson', $salesperson);

      $this->set('resellers', $this->Reseller->buildList());

      if(!empty($this->data)) { 
	if($this->Salesperson->save($this->data)) { 
	  $this->Session->setFlash('Salesperson updated');
	  $this->redirect('/salespeople/view/' . $salespid);
	}

      } else {
	$this->data = $salesperson;
      }

    } else {
      $this->Session->setFlash('Salesperson not found');
      $this->redirect('/salespeople');
    }
  }

  function index() 
  {
    $criteria = Array();
    
    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $criteria['OR'] = Array('Salesperson.name' => 'LIKE %' . $query . '%', 
			      'Reseller.name'    => 'LIKE %' . $query . '%',
			      'Reseller.rdesc'   => 'LIKE %' . $query . '%', 
			      'accountmanager'   => 'LIKE %' . $query . '%');
    } else {
      $query = '';
    }
    $this->set('query', $query);

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'name'));
    $this->set('data', $this->Salesperson->findAll($criteria, NULL, $order, $limit, $page));
  }

  function view($salespid=null)
  {
    if($salesperson = $this->Salesperson->read(null, $salespid)) {
      $this->set('salesperson', $salesperson);
    } else {
      $this->Session->setFlash('Salesperson not found');
      $this->redirect('/salespeople');
    }
  }
}
