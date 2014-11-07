<?

class System extends AppModel
{
  var $name        = 'System';
  var $useTable    = 'myca_log';

  var $belongsTo = Array( 'User' => Array('className'  => 'User', 
					  'foreignKey' => 'userid'));

  function hostList()
  {
    $rv = a();
    foreach($this->query('SELECT DISTINCT host FROM myca_log ORDER BY host') as $i)
      $rv[$i[0]['host']] =  $i[0]['host'];

    return $rv;
  }

  function userList()
  {
    $rv = $this->query('SELECT DISTINCT myca_users.id, myca_users.name
                        FROM myca_log
                        JOIN myca_users ON myca_users.id = myca_log.userid
                        ORDER BY myca_users.name');
    $out = a();
    foreach($rv as $v)
      $out[$v[0]['id']] = $v[0]['name'];

    return $out;
  }

  function controllerList()
  {
    $rv = $this->query('SELECT DISTINCT controller FROM myca_log ORDER BY controller');

    $out = a();
    foreach($rv as $v)
      $out[$v[0]['controller']] = $v[0]['controller'];

    return $out;
  }

  function actionList()
  {
    $rv = $this->query('SELECT DISTINCT action FROM myca_log ORDER BY action');

    $out = a();
    foreach($rv as $v)
      $out[$v[0]['action']] = $v[0]['action'];

    return $out;
  }

  function categoryList()
  {
    $rv = a();
    foreach($this->query('SELECT DISTINCT category FROM myca_log ORDER BY category') as $i)
      $rv[$i[0]['category']] =  $i[0]['category'];

    return $rv;
  }

  function log($host, $controller, $action, $method, $params, $ip_addr, $user, $category, $comments)
  {
    $data = array('System' => array('host'       => $host,
				    'controller' => $controller,
				    'action'     => $action, 
				    'method'     => $method,
				    'pass'       => isset($params['pass']) ? json_encode($params['pass']) : null,
				    'url'        => isset($params['url']) ? json_encode($params['url']) : null, 
				    'ip_addr'    => $ip_addr, 
				    'userid'     => $user, 
				    'category'   => $category, 
				    'comments'   => $comments
				    )
		  );

    $rv = $this->save($data, false);
    $this->id = null;

    return $rv;
  }
}