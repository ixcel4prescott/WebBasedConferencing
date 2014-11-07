<?

class NotesController extends AppController
{
  var $uses       = Array('Note', 'AccountView', 'RoomView');
  var $components = Array('Pagination', 'RequestHandler');
  var $helpers    = Array('Pagination', 'Time'); 

  var $entity_map = Array('Account' => 'AccountView', 
			  'Room'    => 'RoomView');

  function create($entity=null, $object_id=null) 
  {
    $user = $this->Session->read('User');

    if($entity && $object_id) {
      $this->set('entity',    $entity);
      $this->set('object_id', $object_id);
      $user = $this->Session->read('User');

      $model = $this->entity_map[$entity];
      if($obj = $this->$model->get($object_id, $user)){
        if(!empty($this->data)) { 
          if($this->Note->save($this->data)) {
              $this->diffLog('Note', DIFFLOG_OP_CREATE, 
                  $this->Note->getLastInsertId(), $this->data['Note']);
              $this->redirect(!empty($_GET['back']) ? $_GET['back'] : '/');
          }
        } else {
          $this->data = Array( 'Note' => Array( 
              'entity' => $entity, 'object_id' => $object_id, 
              'user_id' => $user['User']['id'], 
              'resellerid' => $obj[$model]['resellerid'] ));
        }
      } else {
        $this->Session->setFlash($entity . ' not found');
        $this->redirect('/');
      }
    }
  }

  function index($entity=null, $object_id=null)
  {
    $user = $this->Session->read('User');

    $criteria = Array();

    if(!is_null($user['Resellers']))
      $criteria['Note.resellerid'] = $user['Resellers'];

    if($user['User']['level_type'] != 'reseller')
      $criteria['Note.user_id'] = $user['User']['id'];

    if($entity) {
      $this->set('entity', $entity);
      $criteria['Note.entity'] = $entity;
    }

    if($object_id) {
      $this->set('object_id', $object_id);
      $criteria['Note.object_id'] = $object_id;
    }

    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $criteria['OR'] = Array('Note.title' => 'LIKE %' . $query . '%', 
			      'Note.body'  => 'LIKE %' . $query . '%');
    } else {
      $query = '';
    }
    $this->set('query', $query);

    list($order, $limit, $page) = $this->Pagination->init($criteria);
    $this->set('data', $this->Note->findAll($criteria, NULL, $order, $limit, $page));
  }

  function spell()
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    ob_start();
    vendor('spellchecker/rpc');
    $output = ob_get_contents();
    ob_end_clean();

    $this->set('output', $output);
  }

  function stick($id=null)
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';
    $data = Array('sticky' => null);
    
    $this->set('id', $id);

    if($note = $this->Note->read(null, $id)) {
      $data['sticky'] = $note['Note']['sticky'];

      if($this->RequestHandler->isPost()) {
	$data['sticky'] = $note['Note']['sticky'] ? 0 : 1;
	if($new_record = $this->Note->update($note, $data)) {
	  $this->diffLog('Note', DIFFLOG_OP_UPDATE, $id, $note['Note'], $data);
	}
      }
    }

    $this->set('data', $data);
  }

  function view($id=null)
  {
    $user = $this->Session->read('User');

    $criteria = Array('Note.id' => $id);
    if(!is_null($user['Resellers']))
      $criteria['Note.resellerid'] = $user['Resellers'];

    if($note = $this->Note->find($criteria)) {
      $model = $this->entity_map[$note['Note']['entity']];
      $object_id = $note['Note']['object_id'];
      $user = $this->Session->read('User');
      if($obj = $this->$model->get($object_id, $user)){
        $this->set('note', $note);
      } else {
        $this->set('note', $note);
        $this->Session->setFlash('Note not found');
        $this->redirect('/notes');
      }
    } else {
      $this->Session->setFlash('Note not found');
      $this->redirect('/notes');
    }
  }
}
