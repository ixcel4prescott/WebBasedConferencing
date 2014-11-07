<?

class RequestData extends AppModel
{
  var $name         = 'RequestData';
  var $useTable     = 'requests_data';

  function saveAll($request)
  {
    $sql = 'INSERT INTO requests_data ([requests_id], [field], [value]) %s;';

    $rows = Array();
    foreach($request['RequestData'] as $k => $v) {
      if(is_array($v)) {
	foreach($v as $i)
	  $rows[] = sprintf('SELECT %d, "%s[]", "%s"', $request['Request']['id'], mssql_escape($k), mssql_escape($i));	  
      } else {
	$rows[] = sprintf('SELECT %d, "%s", "%s"', $request['Request']['id'], mssql_escape($k), mssql_escape($v));
      }
    }

    return $this->execute(sprintf($sql, implode(' UNION ALL ', $rows)));
  }

  function updateData($request, $data)
  {
    $sql = 'UPDATE requests_data SET [value]="%s" WHERE [requests_id]=%d AND [field]="%s"';

    foreach($data as $k => $v)
      $this->execute(sprintf($sql, mssql_escape($v), $request['Request']['id'], mssql_escape($k)));
  }

  function appendData($request, $data)
  {
    $dummy_request = Array( 'Request'     => Array( 'id' => $request['Request']['id'] ), 
			    'RequestData' => $data );

    return $this->saveAll($dummy_request);
  } 
}
