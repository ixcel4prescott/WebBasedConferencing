<?php 

class AuditController extends AppController
{
  var $uses        = Array('DiffLog', 'Account', 'Pin', 'Room', 'ServiceRate', 'Account', 'User');
  var $helpers     = Array('Pagination', 'Time');
  var $components  = Array('Pagination');
  var $permissions = GROUP_IC_RESELLERS_AND_ADMINS;
  
  function index()
  {
    $this->set('op_map',   $this->DiffLog->op_map);
    $this->set('hosts',    $this->DiffLog->hostList());
    $this->set('users',    $this->DiffLog->userList());
    $this->set('entities', $this->DiffLog->entityList());

    $criteria = null;
    if(!empty($_GET)) {
      $criteria = a();
 
      if(!empty($_GET['created'])) {
	$criteria['DAY(DiffLog.created)']   = date('d', strtotime($_GET['created']));
	$criteria['MONTH(DiffLog.created)'] = date('m', strtotime($_GET['created']));
	$criteria['YEAR(DiffLog.created)']  = date('Y', strtotime($_GET['created']));
      }

      if(!empty($_GET['host']))
	$criteria['DiffLog.host'] = $_GET['host'];
       
      if(!empty($_GET['userid']))
	$criteria['DiffLog.userid'] = $_GET['userid'];

      if(isset($_GET['op']) && $_GET['op'] !== '')
	$criteria['DiffLog.op'] = $_GET['op'];

      if(isset($_GET['entity']) && $_GET['entity'] !== '')
	$criteria['DiffLog.entity'] = $_GET['entity'];

      if(isset($_GET['object_id']) && $_GET['object_id'] !== '')
	$criteria['DiffLog.object_id'] = $_GET['object_id'];
       
      if(!empty($_GET['ip_addr']))
	$criteria['DiffLog.ip_addr'] = sprintf('LIKE %s', str_replace(a('?', '*'), a('_', '%'), $_GET['ip_addr']));
    }
      
    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'created', 'direction', 'DESC'));
    $this->set('data', $this->DiffLog->findAll($criteria, NULL, $order, $limit, $page));
  }

  function rollback()
  {
    if(!empty($this->data)) {
      if($diff = $this->DiffLog->read(null, $this->data['DiffLog']['id'])) {
	$this->DiffLog->id = null;

	if($diff['DiffLog']['op'] == DIFFLOG_OP_UPDATE) {
	  $entity = $diff['DiffLog']['entity'];
	  $data = json_decode($diff['DiffLog']['diff'], true);
	
	  $rollback = Array();
	  foreach($data['new'] as $k => $v)
	    if(!isset($data['old'][$k]))
	      $rollback[$k] = null;
	    else 
	      $rollback[$k] = $data['old'][$k];
	
	  $rollback[$this->$entity->primaryKey] = $diff['DiffLog']['object_id'];
	  $this->$entity->save($rollback, false);

	  unset($rollback[$this->$entity->primaryKey]);

	  $this->diffLog($diff['DiffLog']['entity'], DIFFLOG_OP_ROLLBACK, $diff['DiffLog']['object_id'], $rollback, $data['new']);
	  $this->Session->setFlash('The data has been rolled back');
	  $this->redirect(sprintf('/audit/view/%s/%s/%d', $diff['DiffLog']['entity'], $diff['DiffLog']['object_id'], $this->DiffLog->getLastInsertId()));
	} else {
	  $this->Session->setFlash('Object can not be rolled back');
	  $this->redirect(sprintf('/audit/view/%s/%s/%d', $diff['DiffLog']['entity'], $diff['DiffLog']['object_id'], $diff['DiffLog']['id']));	  
	}

      } else {
	$this->Session->setFlash('Audit log not found');
	$this->redirect('/audit');
      }
    } else {
      $this->redirect('/audit');
    }
  }

  function view($entity=null, $object_id=null, $id=null)
  {
    if($entity && $object_id && $id) {
      $this->set('op_map',   $this->DiffLog->op_map);
     
      $diff = $this->DiffLog->read(null, $id);
      $this->set('diff', $diff);

      $others = $this->DiffLog->findAll(Array('DiffLog.id' => '<>' . $id, 'DiffLog.entity' => $entity, 'DiffLog.object_id' => $object_id), null, 'DiffLog.created ASC');
      $this->set('others', $others);

      $this->set('prev', $this->DiffLog->find(Array('DiffLog.id' => '<' . $id, 'DiffLog.entity' => $entity, 'DiffLog.object_id' => $object_id), null, 'DiffLog.id DESC'));
      $this->set('next', $this->DiffLog->find(Array('DiffLog.id' => '>' . $id, 'DiffLog.entity' => $entity, 'DiffLog.object_id' => $object_id), null, 'DiffLog.id ASC'));
    } else {
      $this->Session->setFlash('Audit log not found');
      $this->redirect('/audit');
    }
  }
}