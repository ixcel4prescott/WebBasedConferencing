<?php

class Salesperson extends AppModel
{
  var $name       = 'Salesperson';
  var $useTable   = 'salesperson';
  var $primaryKey = 'salespid';

  var $validate   = Array('name'           => VALID_NOT_EMPTY, 
			  'resellerid'     => '/^\d+$/',
			  'email'          => VALID_EMAIL, 
			  'accountmanager' => VALID_NOT_EMPTY);

  var $belongsTo  = Array( 'Reseller' => Array( 'className'  => 'Reseller', 
						'foreignKey' => 'resellerid'));		      
  
  // var $hasMany = Array( 'Account' => Array( 'className'  => 'AccountView', 
  // 					    'foreignKey' => 'resellerid', 
  // 					    'order'      => 'company ASC' ));		      

  function salespersonList($resellerids=null)
  {
    $sql = 'SELECT accountmanager AS name
            FROM salesperson
            WHERE %s
            GROUP BY accountmanager
            ORDER BY accountmanager';

    if($resellerids)
      $where = sprintf('resellerid IN (%s)', implode(',', $resellerids));
    else
      $where = '1=1';

    return $this->sql($sql, $where);
  }

  function byAccountManager($accountmanager)
  {
    return pluck($this->sql('SELECT salespid FROM salesperson WHERE accountmanager="%s"', 
			    $this->escape($accountmanager)), 'salespid');
  }
}