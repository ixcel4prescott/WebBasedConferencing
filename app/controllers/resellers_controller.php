<?

class ResellersController extends AppController
{
  var $uses        = Array('Reseller', 'Request');
  var $components  = Array('Pagination');
  var $helpers     = Array('Pagination', 'Time'); 

  var $permissions = GROUP_IC_RESELLERS;

  function create()
  {
    $user = $this->Session->read('User');

    $this->set('report_types', $this->Reseller->report_types);

    if(!empty($this->data)) {
      $this->Reseller->set($this->data);
      if($this->Reseller->validates($this->data)) {

	if($user['User']['level_type'] == 'reseller') {
	  if($this->Reseller->save($this->data)) {
	    $reseller_id = $this->Reseller->getLastInsertId();
	    $this->diffLog('Reseller', DIFFLOG_OP_CREATE, $reseller_id, $this->data['Reseller']);
	    $this->Session->setFlash('Reseller created');
	    $this->redirect('/resellers/view/' . $reseller_id);
	  }
	} else {
	  if($this->Request->saveRequest(REQTYPE_RESELLER_CREATE, $user['User']['id'], null, null, $this->data['Reseller'])) {
	    $this->Session->setFlash('Your request has been submitted');
	    $this->redirect('/resellers');
	  } else {
	    $this->Session->setFlash('Request submission failed');
	  }
	}
      }
    } else {
      $this->data = Array('Reseller' => Array( 'contact'               => 'Accounts Receivable', 
					       'rateid'               => 4, 
					       'billingincr'          => 60,
					       'discountid'           => null,
					       'default_rateid'       => 1,
					       'uifn'                 => 129,
					       'active'               => 1,
					       'emailusagerpts'       => 1,
					       'reporttype'           => null,
					       'agidgen'              => 1, 
					       'agidlen'              => 5, 
					       'agidlast'             => 0 ));
    }
  }

  function edit($id=null)
  {
    $user = $this->Session->read('User');

    if($reseller = $this->Reseller->read(null, $id)) {
      $this->set('reseller', $reseller);

      $this->set('report_types', $this->Reseller->report_types);

      if(!empty($this->data)) {
	$this->Reseller->set($this->data);
	if($this->Reseller->validates($this->data)) {

	  if($user['User']['level_type'] == 'reseller') {
	    if($new_reseller = $this->Reseller->update($reseller, $this->data['Reseller'])) {
	      $this->diffLog('Reseller', DIFFLOG_OP_UPDATE, $id, $new_reseller['Reseller'], $reseller['Reseller']);
	      $this->redirect('/resellers/view/' . $id);
	    }
	  } else {
	    if($this->Request->saveRequest(REQTYPE_RESELLER_UPDATE, $user['User']['id'], null, null, $this->data['Reseller'])) {
	      $this->Session->setFlash('Your request has been submitted');
	      $this->redirect('/resellers/view/' . $id);
	    } else {
	      $this->Session->setFlash('Request submission failed');
	    }
	  }

	}
      } else {
	$this->data = $reseller;
      }
    } else {
      $this->Session->setFlash('Reseller not found');
      $this->redirect('/resellers');
    }
  }

  function index()
  {
    $user = $this->Session->read('User');

    $criteria = Array();
    
    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $criteria['OR'] = Array('Reseller.name'        => 'LIKE %' . $query . '%', 
			      'Reseller.contact'     => 'LIKE %' . $query . '%', 
			      'Reseller.racctprefix' => 'LIKE %' . $query . '%', 
			      'Reseller.rphone'      => 'LIKE %' . $query . '%', 
			      'Reseller.remail'      => 'LIKE %' . $query . '%' );
    } else {
      $query = '';
    }
    $this->set('query', $query);

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'resellerid'));
    $this->set('data', $this->Reseller->findAll($criteria, NULL, $order, $limit, $page));
  }

  function view($id=null) 
  {
    if($reseller = $this->Reseller->read(null, $id)) {
      $this->set('reseller',     $reseller);
    } else {
      $this->Session->setFlash('Reseller not found');
      $this->redirect('/resellers');
    }
  }
}
