<?php
class RequestGroup extends AppModel
{
  var $name         = 'RequestGroup';
  var $useTable     = 'request_groups';

  var $hasAndBelongsToMany = Array( 'NotifiedUser' => Array( 'className'             => 'User', 
							     'joinTable'             => 'request_groups_users', 
							     'foreignKey'            => 'request_group_id', 
							     'associationForeignKey' => 'user_id', 
							     'unique'                => true ) );

  var $validate = Array('name' => VALID_NOT_EMPTY);

  function getEvents($request_group_id)
  {
    $sql = 'SELECT *
            FROM requests_request_groups
            WHERE request_group_id=%d';
    
    return $this->query(sprintf($sql, $request_group_id));
  }

  function addEvent($request_group_id, $status, $type)
  {
    if(!$this->query(sprintf('SELECT 1 FROM requests_request_groups WHERE request_group_id=%d AND status=%d AND type=%d', $request_group_id, $status, $type)))
      $this->execute(sprintf('INSERT INTO requests_request_groups(request_group_id, status, type) VALUES(%d, %d, %d)', $request_group_id, $status, $type));
  }

  function removeEvent($request_group_id, $status, $type)
  {
    $this->execute(sprintf('DELETE FROM requests_request_groups WHERE request_group_id=%d AND status=%d AND type=%d', $request_group_id, $status, $type));    
  }
}
?>