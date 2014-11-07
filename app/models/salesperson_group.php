<?php

class SalespersonGroup extends AppModel
{
  var $name        = 'SalespersonGroup';
  var $useTable    = 'salesperson_groups';
  var $primaryKey  = 'user_id';

  var $hasAndBelongsToMany = Array( 'Salesperson' => Array( 'className'             => 'SalespersonView', 
							    'joinTable'             => 'salespeople_to_salesperson_groups', 
							    'foreignKey'            => 'user_id', 
							    'order'                 => 'name ASC',
							    'associationForeignKey' => 'salesperson_id',
							    'unique'                => true ));
}