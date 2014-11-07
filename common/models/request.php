<?

class Request extends AppModel
{
  var $name         = 'Request';
  var $useTable     = 'requests';

  var $cacheQueries = false;

  var $validate = array();
  
  var $belongsTo = array( 'User'          => Array('className'  => 'User', 
						   'foreignKey' => 'creator'),
			  
			  'Account'       => Array('className'  => 'Account',
						   'foreignKey' => 'acctgrpid' ),

			  'RequestType'   => array('className'  => 'RequestType', 
						   'foreignKey' => 'type'),
			  
			  'RequestStatus' => array('className'  => 'RequestStatus', 
						   'foreignKey' => 'status'));

  var $hasMany = array('RequestData' => array('className'  => 'RequestData',
  					      'foreignKey' => 'requests_id'));

  function afterFind($results)
  {
    foreach($results as &$i) {
      // fold request data into associative array
      if(isset($i['RequestData'])) {
	$data = $i['RequestData'];
	$i['RequestData'] = a();

	foreach($data as $d) {
	  if(substr($d['field'], -2, 2) == '[]') { 	 
	    $i['RequestData'][substr($d['field'], 0, -2)][] = $d['value'];
	  } else {
	    $i['RequestData'][$d['field']] = $d['value'];
	  }
	}
      }
    }

    return $results;
  }

  function saveRequest($type, $creator, $group, $account, $data=null, $status=REQSTATUS_PENDING, 
		       $manual=false, $notify=true, $effective_date=null)
  {
    if(is_null($data))
      $data = Array();

    $request = Array('Request'     => aa('type',           $type, 
					 'acctgrpid',      $group, 
					 'creator',        $creator, 
					 'accountid',      $account, 
					 'status',         $status, 
					 'manual',         $manual ? 1:0, 
					 'effective_date', $effective_date), 
		     'RequestData' => $data);

    $this->id = null;
    $this->save($request);
    $request['Request']['id'] = $this->getLastInsertID();    
    $this->id = null;

    if($request['Request']['id']) {

      if($data)
	$this->RequestData->saveAll($request);

      if($notify)
	$this->notifyUsers($request);
    }

    return $request['Request']['id'];
  }

  function updateStatus($request, $notify=true) 
  {
    $this->id = null;
    if($rv = $this->save($request, true, Array('status')))
      $this->notifyUsers($request);

    return $rv;
  }

  function handler($request)
  {
    $status = low($request['RequestStatus']['name']);
    $type   = low(r(' ', '_', $request['RequestType']['name']));
    return sprintf('%s_%s', $status, $type);
  }

  // This function does not automatically notify users
  function updateStatuses($request_ids, $status, $signed_off_by, $comments) 
  {
    $sql = 'UPDATE requests SET status=%d, signed_off_by=%d, comments="%s" WHERE id IN (%s)';
    return $this->execute(sprintf($sql, $status, $signed_off_by, mssql_escape($comments), implode(',', $request_ids)));
  }
   
  function getNotifiedUsers(&$request)
  {
    $sql = 'SELECT myca_users.name, myca_users.email
            FROM myca_users
            JOIN request_groups_users ON myca_users.id = request_groups_users.user_id
            JOIN requests_request_groups ON requests_request_groups.request_group_id = request_groups_users.request_group_id
            WHERE requests_request_groups.type = %d AND (requests_request_groups.status = %d OR requests_request_groups.status=-1)';

    $request['NotifiedUsers'] = Array();
    foreach($this->query(sprintf($sql, $request['RequestType']['id'], $request['RequestStatus']['id'])) as $u)
      $request['NotifiedUsers'][] = $u[0];
  }

  function notifyUsers($request)
  {
    $rv = true;

    if(empty($request['RequestStatus'])) {
      $status = $this->RequestStatus->read(null, $request['Request']['status']);
      $request['RequestStatus'] = $status['RequestStatus'];
    }

    if(empty($request['RequestType'])) {
      $type = $this->RequestType->read(null, $request['Request']['type']);
      $request['RequestType'] = $type['RequestType'];
    }

    $layout = COMMON_DIR . DS . 'views' . DS . 'layouts' . DS . NOTIFICATION_LAYOUT . '.thtml';
    $view   = NOTIFICATIONS_PATH . DS . low(r(' ', '_', $request['RequestType']['name'])) . '.thtml';

    $this->getNotifiedUsers($request);

    if(is_file($view) && !empty($request['NotifiedUsers'])) {
      $out = render_template($layout, $view, Array('request' => $request));
    
      common_vendor('phpmailer/class.phpmailer');
      $mailer = new PHPMailer();

      $mailer->From     = MAILER_FROM;
      $mailer->FromName = MAILER_FROMNAME;
      $mailer->Host     = MAILER_HOST;
      $mailer->Mailer   = 'smtp';
      $mailer->Subject  = sprintf('[MyCA] %s %s in queue', $request['RequestStatus']['name'], $request['RequestType']['name']);

      foreach($request['NotifiedUsers'] as $user)
	if(!empty($user['email'])) 
	  $mailer->AddAddress($user['email'], $user['name']);

      $mailer->Body = $out;
      $mailer->IsHTML(true);

      $rv = $mailer->Send();
    }

    return $rv;
  }

  function similarRequests($request)
  {
    $sql = "SELECT DISTINCT id
            FROM requests
            WHERE requests.acctgrpid='%s' AND requests.type=%d AND requests.status=%d AND requests.creator=%d AND requests.accountid <> '%s'";
    $params = Array($request['Request']['acctgrpid'], $request['Request']['type'], $request['Request']['status'], $request['Request']['creator'], $request['Request']['accountid']);

    switch($request['Request']['type']) { 
    case REQTYPE_RATE_CHANGE:
      $sql .= " AND id IN (SELECT requests_id from requests_data WHERE requests_data.requests_id = requests.id AND requests_data.field = 'servicerate' AND requests_data.value = '%s')";

      $params = array_merge($params, Array($request['RequestData']['servicerate']));

      break;

    case REQTYPE_ROOM_MOVE:
      $sql .= " AND id IN (SELECT requests_id from requests_data WHERE requests_data.requests_id = requests.id AND requests_data.field = 'acctgrpid' AND requests_data.value = '%s') AND 
	           id IN (SELECT requests_id from requests_data WHERE requests_data.requests_id = requests.id AND requests_data.field = 'servicerate' AND requests_data.value = '%d')";
      
      $params = array_merge($params, Array($request['RequestData']['acctgrpid'],  $request['RequestData']['servicerate']));
      break;

    default:
      break;
    }
    
    return pluck(call_user_func_array(Array($this, 'sql'), array_merge(Array($sql), $params)), 'id');
  }
  
  function userCount($user_id)
  {
    $request_statuses = Array(REQSTATUS_APPROVED, REQSTATUS_PENDING);

    $sql = 'SELECT requests_status.name, COUNT(*) AS cnt
            FROM requests
            JOIN requests_status ON requests_status.id = requests.status
            WHERE creator=%d AND status IN (%s)
            AND (effective_date <= CURRENT_TIMESTAMP
            OR effective_date is NULL)
            GROUP BY requests_status.name';
    
    return $this->sql($sql, $user_id, implode(',', $request_statuses));
  }
}