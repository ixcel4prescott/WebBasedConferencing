<?php

class Reseller extends AppModel
{
  var $name        = 'Reseller';
  var $useTable    = 'reseller';
  var $primaryKey  = 'resellerid';
  
  var $validate    = Array( 'name'          => VALID_NOT_EMPTY, 
			    'contact'        => VALID_NOT_EMPTY, 
			    'raddress1'      => VALID_NOT_EMPTY,
			    'rcity'          => VALID_NOT_EMPTY, 			   
			    'rstate'         => VALID_NOT_EMPTY, 
			    'racctprefix'    => VALID_NOT_EMPTY );

  var $hasMany = Array( 'Salesperson' => Array( 'className'  => 'Salesperson',
  						'foreignKey' => 'resellerid',
  						'order'      => 'Salesperson.name' )
  			);
  
  var $hasAndBelongsToMany = Array( 'DialinNumber' => Array( 'className'             => 'DialinNumber',
  							     'joinTable'             => 'dialinNoMap',
  							     'foreignKey'            => 'resellerid',
  							     'associationForeignKey' => 'dialinNoid',
  							     'conditions'            => 'active=1',
  							     'order'                 => 'description' )
  				    );

  var $report_types = Array( null => 'No Reports',
  				 'PDF'    => 'PDF', 
			     'XLS'    => 'XLS', 
			     'XLStab' => 'XLStab' );

  function beforeValidate()
  {
    parent::beforeValidate();

    if(empty($this->data['Reseller']['resellerid']) && !empty($this->data['Reseller']['racctprefix']) && empty($this->data['Reseller']['agent']) &&
       $this->find(Array('Reseller.racctprefix' => $this->data['Reseller']['racctprefix'])))
      $this->invalidate('racctprefix');

    $emails = explode(',', $this->data['Reseller']['remail']);
    if(count($emails) == 0) {
      $this->invalidate('remail');
    } else {
      foreach($emails as $e)
	if(preg_match(VALID_EMAIL, $e) == 0) 
	  $this->invalidate('remail');
    }

    return true;
  }

  function buildList() 
  {
    $resellers = Array();

    foreach($this->findAll(Array('active' => 1), null, 'Reseller.agent ASC,Reseller.name ASC', null, null, 0) as $i) {
      if($i['Reseller']['agent']) 
	$base = $i['Reseller']['rdesc'];
      else
	$base = $i['Reseller']['name'];

      $resellers[$i['Reseller']['resellerid']] = sprintf('%s%s: %s', $i['Reseller']['agent'] ? '(A)' : '', $i['Reseller']['racctprefix'], $base);
    }

    return $resellers;
  }
}