<?

class DiffLog extends AppModel
{
   var $primaryKey  = 'id';
   var $useTable    = 'myca_diff_log';

   var $belongsTo  = Array('User' => Array('className'  => 'User', 
					   'foreignKey' => 'userid' ));

   var $op_map = Array( DIFFLOG_OP_CREATE   => 'Create',
			DIFFLOG_OP_UPDATE   => 'Update',
			DIFFLOG_OP_DELETE   => 'Delete', 
			DIFFLOG_OP_ROLLBACK => 'Rollback' );
   function entityList()
   {
     $rv = a();

     foreach($this->query('SELECT DISTINCT entity FROM myca_diff_log ORDER BY entity ASC') as $i) 
       $rv[$i[0]['entity']] = $i[0]['entity'];

     return $rv;
   }

  function hostList()
  {
    $rv = a();
    foreach($this->query('SELECT DISTINCT host FROM myca_diff_log ORDER BY host') as $i)
      $rv[$i[0]['host']] =  $i[0]['host'];

    return $rv;
  }

  function userList()
  {
    $rv = $this->query('SELECT DISTINCT myca_users.id, myca_users.name
                        FROM myca_diff_log
                        JOIN myca_users ON myca_users.id = myca_diff_log.userid
                        ORDER BY myca_users.name');
    $out = a();
    foreach($rv as $v)
      $out[$v[0]['id']] = $v[0]['name'];

    return $out;
  }

  function log($host, $entity, $op, $object_id, $ip_addr, $user, $new, $old)
  {
    if($new === null)
      $new = a();
    
    if($old === null)
      $old = a();

    $diff = aa( 'new', a(), 'old', a() );

    foreach($new as $k => $v) {
      if(!isset($old[$k]) && !is_null($new[$k])) { // An added field that wasnt present previously
    	$diff['new'][$k] = $v;
      } elseif(isset($old[$k]) && $v != $old[$k]) { // A field that has changed
    	$diff['new'][$k] = $v;
    	$diff['old'][$k] = $old[$k];
      }
    }

    if($new || $old) {
      $rv = $this->save(Array('DiffLog' => Array( 'host'      => $host,
						  'entity'    => $entity,
						  'op'        => $op,			     
						  'object_id' => $object_id,
						  'ip_addr'   => $ip_addr, 
						  'userid'    => $user, 
						  'diff'      => json_encode($diff))), false);    
      $this->id = null;
    } else {
      $rv = true;
    }
    
    return $rv;
  }
}