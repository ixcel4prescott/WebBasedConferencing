<?
class EmailController extends AppController
{
  var $uses       = Array('Account', 'AccountView', 'Room', 'RoomView', 'TemplateClass', 'Template', 
			  'WelcomeEmail', 'TemplateAttachment', 'WelcomeEmailLog');
  var $components = Array('EmailTemplate', 'Email', 'Pagination');
  var $helpers    = Array('Pagination'); 

  var $permissions = GROUP_IC_ALL;

  function index($acctgrpid=null)
  {
    if($acctgrpid) {
      $user = $this->Session->read('User');

      $this->Account->unbindModel(Array('hasMany' => Array('Room')));
      
      //Does the user have access to the account?
      if($account = $this->Account->get($acctgrpid, $user)) {
	$this->set('account', $account);
	
	$key = 'Selections.email.' . $acctgrpid;
	if($selections = $this->Session->read($key)) {
	  $this->set('selections', $selections);

	  $rooms = $this->Room->findAll(Array('accountid' => $selections));
	  $this->set('rooms', $rooms);

	  if(!empty($this->data)) {
	    $data = Array('Email' => Array('acctgrpid' => $acctgrpid, 'accountids' => Array()), 'Spin' => Array());

	    foreach($this->data['Room']['accountid'] as $i)
	      if(!empty($i))
		$data['Email']['accountids'][] = $i;

	    if($data['Email']['accountids']) {
	      $token = generate_token();
	      $this->Session->write($token, $data);
	      $this->redirect('/email/compose/' . $token);
	    } else {
	      $this->Session->setFlash('Please select rooms to send emails to');
	    }

	  } else {
	    $this->data = Array('Room' => Array('accountid' => $selections));
	  }

	} else {
	  $this->Session->setFlash('Please select one or more rooms');
	  $this->redirect('/rooms/select/' . $acctgrpid . '/email');
	}
      } else {
	$this->Session->setFlash('Account not found');
	$this->redirect('/accounts');
      }
    } else {
      $this->redirect('/accounts');
    }
  }

  function compose($token=null)
  {
    if($token && $this->Session->check($token)) {
      $this->set('token', $token);
      $data = $this->Session->read($token);
      $this->set('data', $data);
      $user = $this->Session->read('User');

      //Does the user have access to the account?
      if($account = $this->Account->get($data['Email']['acctgrpid'], $user)) {
        $this->set('account', $account);
        $bridges = Array();
        foreach($data['Email']['accountids'] as $a) {
          $room = $this->Room->read(null, $a);
          if($room['Room']['bridgeid'] == OCI_BRIDGEID)
            $bridges['icbr1'] = Array();
          elseif($room['Room']['bridgeid'] == SPECTEL_BRIDGEID)
            $bridges['icbr2'] = Array();
          elseif($room['Room']['bridgeid'] == INTERCALL_BRIDGEID)
            $bridges['osbr1'] = Array();
          elseif($room['Room']['bridgeid'] == BT_BRIDGEID)
            $bridges['osbr2'] = Array();
        }
        foreach($this->TemplateClass->findAll(
            Array('active' => 1, 'bridge' => array_keys($bridges)), 
            null, 'class ASC') as $i) {
          $bridges[$i['TemplateClass']['bridge']][$i['TemplateClass']['id']] = 
                $i['TemplateClass']['class'];
        }
        $this->set('bridges', $bridges);
        $templates = Array();
        foreach($this->Template->findAll() as $i)
          $templates[$i['Template']['id']] = $i;

        $this->set('templates', $templates);

        if(!empty($this->data)) {
          $this->WelcomeEmail->set($this->data);
          if($this->WelcomeEmail->validates($this->data)) {
            $data['Email'] = array_merge($data['Email'], $this->data['WelcomeEmail']);
            $this->Session->write($token, $data);
            $this->redirect('/email/preview/' . $token);
	      }
        }
      } else {
        $this->Session->setFlash('Account not found');
        $this->redirect('/accounts');
      }
    } else {
      $this->systemLog('Invalid token');
      $this->Session->setFlash('Email token was invalid');
      $this->redirect('/accounts');
    }
  }

  function templates($token=null) 
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';
    
    $names = Array('spectel'	=> 'WelcomeEmail/template_spectel', 
		   'oci'	=> 'WelcomeEmail/template_oci', 
		   'osbr1'	=> 'WelcomeEmail/template_osbr1', 
		   'osbr2'	=> 'WelcomeEmail/template_osbr2');
    $templates = Array();
    $name = '';

    if($token && $this->Session->check($token)) {
      if(!empty($this->data)) {
	    $data = $this->Session->read($token);
        $user = $this->Session->read('User');
        //Does the user have access to the account?
        if($account = $this->AccountView->get($data['Email']['acctgrpid'], $user)) {
	      $templates = $this->Template->templates($account['AccountView']['resellerid'], $this->data['Template']['classid']);
	      $name = $names[$this->data['Template']['bridge']];
        }
      }
    } else {
      $this->systemLog('Invalid token');
    }
    
    $this->set('templates', $templates);
    $this->set('name', $name);
  }
  
  function preview($token=null)
  {
    if($token && $this->Session->check($token)) {
      if(!empty($this->data)) {
        $this->redirect('/email/send/' . $token);
      } else {
        $this->set('token', $token);     
        $data = $this->Session->read($token);
        $this->set('data', $data);
        $user = $this->Session->read('User');
        //Does the user have access to the account?
        if($account = $this->Account->get($data['Email']['acctgrpid'], $user)) {
          $this->set('account', $account);      
          $rooms = $this->RoomView->findAll(
              Array('accountid' => $data['Email']['accountids']));
          $this->set('rooms', $rooms);
        } else {
          $this->Session->setFlash('Account not found');
          $this->redirect('/accounts');
        }
      }
    } else {
      $this->systemLog('Invalid token');
      $this->Session->setFlash('Email token was invalid');
      $this->redirect('/accounts');
    }
  }

  function send($token=null)
  {
    if($token && $this->Session->check($token)) {
      $this->set('token', $token);

      $data = $this->Session->read($token);
      $data['Spin']['count'] = 0;
      $this->Session->write($token, $data);
      $this->set('data', $data);
      $user = $this->Session->read('User');
      //Does the user have access to the account?
      if($account = $this->Account->get($data['Email']['acctgrpid'], $user)) {
        $this->set('account', $account);
      } else {
        $this->Session->setFlash('Account not found');
        $this->redirect('/accounts');
      }
    } else {
      $this->systemLog('Invalid token');
      $this->Session->setFlash('Email token was invalid');
      $this->redirect('/accounts');
    }
  }

  function spin($token=null)
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    if($token && $this->Session->check($token)) { 
      $data = $this->Session->read($token);
      $user = $this->Session->read('User');
      
      if($data['Spin']['count'] < count($data['Email']['accountids'])) {
	set_time_limit(0);    

	$log = Array('creator' => $user['User']['id']);

    //Does the user have access to the account?
    if(!$account = $this->Account->get($data['Email']['acctgrpid'], $user)) {
        $this->Session->setFlash('Account not found');
        $this->redirect('/accounts');
    }
	$log['acctgrpid'] = $data['Email']['acctgrpid'];

	$accountid = $data['Email']['accountids'][$data['Spin']['count']];
	$log['accountid'] = $accountid;

	$room = $this->RoomView->read(null, $accountid);

	$bridgeid = $this->Room->accountid2bridge($room['RoomView']['accountid']);
	if($bridgeid == OCI_BRIDGEID)
	  $template_id = $data['Email']['template_oci'];
	elseif($bridgeid == SPECTEL_BRIDGEID)
	  $template_id = $data['Email']['template_spectel'];
	elseif($bridgeid == INTERCALL_BRIDGEID)
	  $template_id = $data['Email']['template_osbr1'];
	elseif($bridgeid == BT_BRIDGEID)
	  $template_id = $data['Email']['template_osbr2'];

	$log['templateid'] = $template_id;

	$template = $this->Template->read(null, $template_id);      

	$this->Email->Subject = $template['Template']['subject'];
	$this->Email->Body    = $this->EmailTemplate->render($template, $room, $account);
	$this->Email->IsHTML(true);

	foreach($this->TemplateAttachment->findAll(Array('templateid' => $template['Template']['id'])) as $a)
	  $this->Email->AddAttachment(EMAIL_ATTACHMENT_PATH . $a['TemplateAttachment']['filename']);

	// Set Sender
	if($data['Email']['sender'] == 'salesperson') {
	  $this->Email->From     = $account['Salesperson']['email'];
	  $this->Email->FromName = $account['Salesperson']['accountmanager'];
	  $log['from'] = sprintf('%s<%s>', $this->Email->FromName, $this->Email->From);
	} elseif($data['Email']['sender'] == 'clientcare') {
	  $this->Email->From     = CLIENTCARE_EMAIL;
	  $this->Email->FromName = 'ClientCare';
	  $log['from'] = sprintf('%s<%s>', $this->Email->FromName, $this->Email->From);
	} elseif($data['Email']['sender'] == 'other') {
	  $this->Email->From = $data['Email']['other_sender'];
	  $log['from']       = $this->Email->From;
	}

	// Add Addresses
	$log['to'] = '';

	if($data['Email']['account_address'] && !empty($account['Account']['email'])) {
	  foreach(explode(',', $account['Account']['email']) as $i) {
	    $this->Email->AddAddress($i);
	    $log['to'] .= ($i . ',');
	  }
	}

	if($data['Email']['contact_address'] && !empty($room['Contact'][0]['email'])) {
	  foreach(explode(',', $room['Contact'][0]['email']) as $i) {
	    $this->Email->AddAddress($i);
	    $log['to'] .= ($i . ',');
	  }
 	}

	foreach($data['Email']['add_recipients'] as $i) {
	  if(!empty($i)) {
	    $this->Email->AddAddress($i);
	    $log['to'] .= ($i . ',');	    
	  }
	}

	// Add BCCs
	$log['bcc'] = '';

	if($data['Email']['salesperson']) { 
	  $this->Email->AddBCC($account['Salesperson']['email'], $account['Salesperson']['accountmanager']);
	  $log['bcc'] .= ($account['Salesperson']['email'] . ',');
	}

	if($data['Email']['operations']) {
	  $this->Email->AddBCC(OPS_EMAIL, 'Ops');
	  $log['bcc'] .= OPS_EMAIL;
	}

	if($data['Email']['clientcare']) {
	  $this->Email->AddBCC(CLIENTCARE_EMAIL, 'Client Care');
	  $log['bcc'] .= CLIENTCARE_EMAIL;
	}

	foreach($data['Email']['add_bccs'] as $i) {
	  if(!empty($i)){
	    $this->Email->AddBCC($i);
	    $log['bcc'] .= ($i . ',');	    
	  }
	}

	$this->Email->Send();

	$log['sent'] = now();
	$this->WelcomeEmailLog->save($log);

	$data['Spin']['count']++;
      }
      $this->Session->write($token, $data);

      if($data['Spin']['count'] < count($data['Email']['accountids'])) {
	$complete = false;
	$percent = round((float)$data['Spin']['count']/(float)count($data['Email']['accountids'])*100.0);
      } else {
	$this->Session->del($token);
	$complete = true;
	$percent = 100;
      }

      $this->set('data', Array('complete' => $complete, 'percent' => $percent . '%'));
    } else {
      $this->systemLog('Invalid token');
      $this->Session->setFlash('Email token was invalid');
      $this->redirect('/accounts');
    }
  }

  function view($token=null, $accountid=null)
  {
    Configure::write('debug', 0);
    $this->layout = 'ajax';

    if($token && $this->Session->check($token) && $accountid) {
      $data = $this->Session->read($token);
      $user = $this->Session->read('User');
      //Does the user have access to the account?
      if(!$account = $this->Account->get($data['Email']['acctgrpid'], $user)) {
        $this->Session->setFlash('Account not found');
        $this->redirect('/accounts');
      }
      $room    = $this->RoomView->read(null, $accountid);
      $bridgeid = $this->Room->accountid2bridge($room['RoomView']['accountid']);
      if($bridgeid == OCI_BRIDGEID)
        $template_id = $data['Email']['template_oci'];
      elseif($bridgeid == SPECTEL_BRIDGEID)
        $template_id = $data['Email']['template_spectel'];
      elseif($bridgeid == INTERCALL_BRIDGEID)
        $template_id = $data['Email']['template_osbr1'];
      elseif($bridgeid == BT_BRIDGEID)
        $template_id = $data['Email']['template_osbr2'];

      $template = $this->Template->read(null, $template_id);      
      $this->set('template', $template);
      $this->set('template_data', $this->EmailTemplate->render($template, $room, $account));
      $room = $this->RoomView->read(null, $accountid);
      $from = '';
      if($data['Email']['sender'] == 'salesperson') {
        $from = htmlentities(sprintf('%s<%s>', $account['Salesperson']['accountmanager'], $account['Salesperson']['email']));
      } elseif($data['Email']['sender'] == 'clientcare') {
        $from = htmlentities(sprintf('%s<%s>', 'ClientCare', CLIENTCARE_EMAIL));
      } elseif($data['Email']['sender'] == 'other') {
        $from = $data['Email']['other_sender'];
      }
      $this->set('from', $from);
      $to = Array();
      if($data['Email']['account_address'] && !empty($account['Account']['email'])) {
        if(!empty($account['Account']['bcontact'])) {
          $to[] = htmlentities(sprintf('%s<%s>', $account['Account']['bcontact'], $account['Account']['email']));
        } else {
          $to[] = $account['Account']['email'];
        }
      }
      
      if($data['Email']['contact_address'] && 
          !empty($room['Contact'][0]['email'])) {
        if(!empty($room['Contact'][0]['first_name']) && 
            !empty($room['Contact'][0]['last_name'])) {
          $to[] = htmlentities(sprintf('%s %s<%s>', 
                $room['Contact'][0]['first_name'], 
                $room['Contact'][0]['last_name'], 
                $room['Contact'][0]['email']));	 
        } else {
          $to[] = $room['Contact'][0]['email'];
        }
      }

      foreach($data['Email']['add_recipients'] as $i) {
        if(!empty($i)) {
          $to[] = $i;
        }
      }

      $this->set('to', implode(', ', $to));
      $bcc = Array();
      if($data['Email']['salesperson'])
        $bcc[] = htmlentities(sprintf('%s<%s>', 
            $account['Salesperson']['accountmanager'], 
            $account['Salesperson']['email']));
      if($data['Email']['operations'])
        $bcc[] = htmlentities(sprintf('%s<%s>', 'Ops',  OPS_EMAIL));
      if($data['Email']['clientcare'])
        $bcc[] = htmlentities(sprintf('%s<%s>', 'Client Care', CLIENTCARE_EMAIL));

      foreach($data['Email']['add_bccs'] as $i) {
        if(!empty($i)){
          $bcc[] = $i;
        }
      }
      $this->set('bcc', implode(', ', $bcc));

    } else {
      $this->systemLog('Invalid token');
      $this->Session->setFlash('Email token was invalid');
      $this->redirect('/accounts');
    }    
  }
  
  function summary($acctgrpid=null){
    $this->pageTitle = 'Welcome Email Summary';
    if($acctgrpid) {
      $user = $this->Session->read('User');
      if($account = $this->Account->get($acctgrpid, $user)){
        $this->set('account', $account);
        $roomCriteria = Array('Room.acctgrpid' => $account['Account']['acctgrpid'], 'Room.roomstat' => '0', 'Room.bridgeid' => $account['DefaultBridge']['bridge_id']);
        $welcomeCriteria = Array('WelcomeEmailLog.acctgrpid' => $acctgrpid, 'sent' => '(select max(sent) from welcome_email_log B where WelcomeEmailLog.acctgrpid = B.acctgrpid and WelcomeEmailLog.accountid = B.accountid)');
        list($order, $limit, $page) = $this->Pagination->init($roomCriteria, null, aa('modelClass', 'Room', 'sortBy', 'accountid', 'direction', 'DESC'));
         
        if($acctgrpid) {
          $user = $this->Session->read('User');
        
          $roomsWEmails =  $this->WelcomeEmailLog->findAll($welcomeCriteria,'accountid, sent', 'WelcomeEmailLog.accountid DESC');
          $rooms = $this->Room->findAll($roomCriteria, 'accountid', $order, $limit, $page ); 
        
          $this->set('roomsWEmails', $roomsWEmails);
          $this->set('rooms', $rooms);
        }
      } else {
          $this->Session->setFlash('Account not found');
          $this->redirect('/accounts');
      }
    } else {
      $this->redirect('/accounts');
    }
  } 
}
