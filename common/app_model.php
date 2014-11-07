<?php
/* SVN FILE: $Id: app_model.php 4409 2007-02-02 13:20:59Z phpnut $ */
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 4409 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-02-02 07:20:59 -0600 (Fri, 02 Feb 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Application model for Cake.
 *
 * This is a placeholder class.
 * Create the same file in app/app_model.php
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package		cake
 * @subpackage	cake.cake
 */

class AppModel extends Model {
  var $clean_data = true;

  function beforeValidate()
  {
    if($this->clean_data)
      $this->data = clean_data($this->data);

    return true;
  }

  function beforeSave()
  {
    if($this->clean_data)
      $this->data = clean_data($this->data);

    return true;
  }

  function escape($s)
  {
    return mssql_escape($s);
  }

  function update($old_record, $new_data)
  {
    foreach($new_data as $k => $v)
      $old_record[$this->name][$k] = $v;

    $saved = $this->save($old_record, false, array_keys($new_data));

    if($saved)
      $rv = $old_record;
    else
      $rv = null;

    return $rv;
  }

  function sql()
  {
    $args = func_get_args();
    $sql  = call_user_func_array('sprintf', $args);

    $out = Array();
    foreach($this->query($sql) as $i)
      $out[] = $i[0];
    
    return $out;
  }

  function sproc($name, $args, $out=null)
  {
    $params = Array();    
    foreach($args as $k => $v)
      $params[] = sprintf('@%s=%s', $k, (is_null($v) ? 'NULL' : "'".$this->escape($v)."'"));

    $sql = sprintf('EXEC %s %s', $name, implode(', ', $params));

    if($out) {
      $declares = Array();
      $oparams  = Array();
      $rparams  = Array();

      foreach($out as $k => $v) {
	$declares[] = sprintf('DECLARE @%s %s;', $k, $v);
	$oparams[]  = sprintf('@%s AS %s', $k, $k);		
	$rparams[]   = sprintf('@%s=@%s OUTPUT', $k, $k);
      }

      $sql = sprintf("%s\n%s, %s;\nSELECT %s;", implode("\n", $declares), $sql, implode(', ', $rparams), implode(', ', $oparams));
    } else {
      $sql .= ';';
    }

    $rv = pluck($this->query($sql, 'false'), 0);

    if($out)
      $rv = $rv[0];

    return $rv;
  }
}
?>