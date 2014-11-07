<?php

class RequestGroupsController extends AppController
{
  var $uses        = Array('RequestGroup', 'RequestType', 'RequestStatus', 'User');
  var $permissions = GROUP_IC_RESELLERS_AND_ADMINS;

  function create()
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    if($this->data)
      $this->RequestGroup->save($this->data);

    $this->set('groups', $this->RequestGroup->generateList(null, 'RequestGroup.name ASC', null, '{n}.RequestGroup.id', '{n}.RequestGroup.name'));
  }

  function events($id=null)
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    if($group = $this->RequestGroup->read(null, $id)) {
      $this->set('group',    $group);
      $this->set('events',   $this->RequestGroup->getEvents($id));
      $this->set('types',    $this->RequestType->generateList(null, 'RequestType.name ASC', null, '{n}.RequestType.id', '{n}.RequestType.name'));
      $this->set('statuses', $this->RequestStatus->generateList(null, 'RequestStatus.name ASC', null, '{n}.RequestStatus.id', '{n}.RequestStatus.name'));
    }
  }

  function index()
  {
    $this->set('groups',   $this->RequestGroup->generateList(null, 'RequestGroup.name ASC', null, '{n}.RequestGroup.id', '{n}.RequestGroup.name'));
    $this->set('users',    $this->User->generateList(Array('User.level_type' => Array('admin', 'reseller', 'salesperson')), 'User.name ASC', null, '{n}.User.id', '{n}.User.name'));
    $this->set('types',    $this->RequestType->generateList(null, 'RequestType.name ASC', null, '{n}.RequestType.id', '{n}.RequestType.name'));
    $this->set('statuses', $this->RequestStatus->generateList(null, 'RequestStatus.name ASC', null, '{n}.RequestStatus.id', '{n}.RequestStatus.name'));
  }

  function update_events()
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    $events = Array();
    if($this->data) {
      $this->RequestGroup->addEvent($this->data['Event']['request_group_id'], $this->data['Event']['status'], $this->data['Event']['type']);
      $events = $this->RequestGroup->getEvents($this->data['Event']['request_group_id']);
    }

    $this->set('types',    $this->RequestType->generateList(null, 'RequestType.name ASC', null, '{n}.RequestType.id', '{n}.RequestType.name'));
    $this->set('statuses', $this->RequestStatus->generateList(null, 'RequestStatus.name ASC', null, '{n}.RequestStatus.id', '{n}.RequestStatus.name'));
    $this->set('events',   $events);
    $this->render('events');
  }

  function update_users()
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    if($this->data) {
      // this is such a hack, cakephp fails to generate valid sql  if there is no data besides the pk so we need to pass the name
      $group = $this->RequestGroup->read(null, $this->data['RequestGroup']['id']);
      $this->data['RequestGroup']['name'] = $group['RequestGroup']['name'];
      $this->RequestGroup->save($this->data);
    }

    $this->set('data', Array('status' => true));
  }

  function remove_event()
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    $events = Array();
    if($this->data) {
      $this->RequestGroup->removeEvent($this->data['Event']['request_group_id'], $this->data['Event']['status'], $this->data['Event']['type']);
      $events = $this->RequestGroup->getEvents($this->data['Event']['request_group_id']);
    }

    $this->set('types',    $this->RequestType->generateList(null, 'RequestType.name ASC', null, '{n}.RequestType.id', '{n}.RequestType.name'));
    $this->set('statuses', $this->RequestStatus->generateList(null, 'RequestStatus.name ASC', null, '{n}.RequestStatus.id', '{n}.RequestStatus.name'));
    $this->set('events',   $events);
    $this->render('events');
  }

  function view($id=null)
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    $selected = Array();

    if($group = $this->RequestGroup->read(null, $id)) {
      foreach($group['NotifiedUser'] as $u)
	$selected[] = $u['id'];
    }

    $this->set('selected', $selected);

    $this->set('users', $this->User->generateList(Array('User.level_type' => Array('admin', 'reseller', 'salesperson')), 'User.name ASC', null, '{n}.User.id', '{n}.User.name'));
    $this->set('types',    $this->RequestType->generateList(null, 'RequestType.name ASC', null, '{n}.RequestType.id', '{n}.RequestType.name'));
    $this->set('statuses', $this->RequestStatus->generateList(null, 'RequestStatus.name ASC', null, '{n}.RequestStatus.id', '{n}.RequestStatus.name'));
  }
}