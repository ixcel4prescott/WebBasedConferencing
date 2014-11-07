<?php

class SystemController extends AppController
{
   var $uses        = Array('System');
   var $helpers     = Array('Pagination');
   var $components  = Array('Pagination');

   var $permissions = GROUP_IC_RESELLERS_AND_ADMINS;

   function logs()
   {
     $this->set('hosts',       $this->System->hostList());
     $this->set('users',       $this->System->userList());
     $this->set('controllers', $this->System->controllerList());
     $this->set('actions',     $this->System->actionList());
     $this->set('categories',  $this->System->categoryList());

     $criteria = null;
     if(!empty($_GET)) {
       $criteria = a();

       if(!empty($_GET['created'])) {
				 $criteria['DAY(System.created)']   = date('d', strtotime($_GET['created']));
				 $criteria['MONTH(System.created)'] = date('m', strtotime($_GET['created']));
				 $criteria['YEAR(System.created)']  = date('Y', strtotime($_GET['created']));
       }
			 $criteria['System.ip_addr']  = '<> 64.95.122.117';
				 
       if(!empty($_GET['host']))
	 $criteria['System.host'] = $_GET['host'];

       if(!empty($_GET['userid']))
	 $criteria['System.userid'] = $_GET['userid'];

       if(!empty($_GET['ip_addr']))
	 $criteria['System.ip_addr'] = sprintf('LIKE %s', str_replace(a('?', '*'), a('_', '%'), $_GET['ip_addr']));
  
       if(!empty($_GET['controller']))
	 $criteria['System.controller'] = $_GET['controller'];

       if(!empty($_GET['action']))
	 $criteria['System.action'] = $_GET['action'];

       if(!empty($_GET['category']))
	 $criteria['System.category'] = $_GET['category'];

       if(!empty($_GET['comments']))
	 $criteria['System.comments'] = sprintf('LIKE %%%s%%', $_GET['comments']);
     }
     
     list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'created', 'direction', 'DESC'));
     $this->set('data', $this->System->findAll($criteria, NULL, $order, $limit, $page));
   }
}