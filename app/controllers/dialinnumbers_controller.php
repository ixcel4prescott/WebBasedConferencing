<?

class DialinNumbersController extends AppController
{
  var $uses         = Array('DialinNumber', 'Reseller', 'Bridge');
  var $helpers     = Array('Pagination');
  var $components  = Array('Pagination');
  var $permissions = GROUP_IC_RESELLERS_AND_ADMINS;

  function create()
  {
    $this->set('resellers', $this->Reseller->buildList());

    $bridges = $this->Bridge->generateList(Array('active'=>1), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
    $this->set('bridges', $bridges);

    $aligned_resellers = a();
    if(!empty($this->data)) {

      $this->data['DialinNumber']['bridge'] = $bridges[$this->data['DialinNumber']['bridgeid']];

      if(isset($this->data['DialinNumber']['resellers']))
	$aligned_resellers = $this->data['DialinNumber']['resellers'];

      if($this->DialinNumber->save($this->data)) {
	$this->diffLog('DialinNumber', DIFFLOG_OP_CREATE, $this->DialinNumber->getLastInsertId(), $this->data['DialinNumber']);
	$this->Session->setFlash('Dialin number created');
	$this->redirect('/dialinnumbers/view/' . $this->DialinNumber->getLastInsertId());
      }
    } else {
      $this->data = Array('DialinNumber' => Array('active' => 1));
    }

    $this->set('aligned_resellers', $aligned_resellers);
  }

  function edit($id=null)
  {
    if($dialin_number = $this->DialinNumber->read(null, $id)) {
      $this->set('dialin_number', $dialin_number);

      $this->set('resellers', $this->Reseller->buildList());

      $bridges = $this->Bridge->generateList(Array('active'=>1), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
      $this->set('bridges', $bridges);

      $aligned_resellers = a();

      if(!empty($this->data)) {

	$this->data['DialinNumber']['bridge'] = $bridges[$this->data['DialinNumber']['bridgeid']];
	 
	if(isset($this->data['DialinNumber']['resellers']))
	  $aligned_resellers = $this->data['DialinNumber']['resellers'];

	if($this->DialinNumber->save($this->data)) {
	  $this->diffLog('DialinNumber', DIFFLOG_OP_UPDATE, $id, $dialin_number['DialinNumber'], $this->data['DialinNumber']);
	  $this->Session->setFlash('Dialin number updated');
	  $this->redirect('/dialinnumbers/view/' . $id);
	}
      } else {
	$this->data = $dialin_number;
	$aligned_resellers = $this->DialinNumber->getAlignedResellers($id);
      }

      $this->set('aligned_resellers', $aligned_resellers);
    } else {
      $this->Session->setFlash('Dialin number not found');
      $this->redirect('/dialinnumbers');
    }
  }

  function index()
  {
    $criteria = Array();

    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $criteria['OR'] = Array('DialinNumber.description' => 'LIKE %' . $query . '%', 
			      'DialinNumber.tollfreeno'  => 'LIKE %' . $query . '%',
			      'DialinNumber.tollno'      => 'LIKE %' . $query . '%');
    } else {
      $query = '';
    }
    $this->set('query', $query);

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'description'));
    $this->set('data', $this->DialinNumber->findAll($criteria, NULL, $order, $limit, $page));
  }

  function view($id=null)
  {
    if($dialin_number = $this->DialinNumber->read(null, $id)) {
      $this->set('dialin_number', $dialin_number);
      $this->set('resellers', $this->DialinNumber->getResellers($id));
    } else {
      $this->Session->setFlash('Dialin number not found');
      $this->redirect('/dialinnumbers');
    }
  }
}
