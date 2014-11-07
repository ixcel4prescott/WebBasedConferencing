<?php

class ResellerGroup extends AppModel
{
  var $name        = 'ResellerGroup';
  var $useTable    = 'reseller_groups';
  var $primaryKey  = 'user_id';

  var $hasAndBelongsToMany = Array( 'Reseller' => Array( 'className'             => 'ResellerView',
							 'joinTable'             => 'reseller_groups_to_resellers',
							 'foreignKey'            => 'user_id',
							 'order'                 => 'name ASC',
							 'associationForeignKey' => 'reseller_id',
							 'unique'                => true ));
}