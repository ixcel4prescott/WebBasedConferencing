<?

class UsersController extends AppController 
{
  var $uses        = Array('User', 'Reseller', 'Salesperson', 'SalespersonGroup', 'ResellerGroup', 'RequestView', 'DiffLog');
  var $components  = Array('Email', 'Pagination');
  var $helpers     = Array('Html', 'Form', 'Pagination', 'Time'); 

  var $permissions = Array( 'create'   => GROUP_IC_RESELLERS, 
			    'edit'     => GROUP_IC_RESELLERS, 
			    'index'    => GROUP_IC_RESELLERS_AND_ADMINS, 
			    'view'     => GROUP_IC_RESELLERS_AND_ADMINS, 
			    'password' => GROUP_ALL, 
			    'forgot'   => GROUP_ALL,
			    'login'    => GROUP_ALL,
			    'logout'   => GROUP_ALL );

  // actions accessible w/o login
  var $publicActions = array('login', 'forgot', 'verify', 'reset');

  function create() {
    $this->set('resellers', $this->Reseller->buildList());

    foreach($this->Salesperson->findAll(null, null, 'Salesperson.name ASC', null, null, 0) as $i)
      $salespeople[$i['Salesperson']['salespid']] = $i['Salesperson']['name'];

    $this->set('salespeople', $salespeople);

    $grouped_resellers   = a();
    $grouped_salespeople = a();

    if(!empty($this->data)) {
      $valid = true;
      $this->User->validate['level_type'] = VALID_NOT_EMPTY;

      if(isset($this->data['Reseller']['Reseller']))
	$grouped_resellers = $this->data['Reseller']['Reseller'];

      if(isset($this->data['Salesperson']['Salesperson']))
	$grouped_salespeople = $this->data['Salesperson']['Salesperson'];

      if(!empty($this->data['User']['username']) && $this->User->findByUsername($this->data['User']['username']))
	$this->User->invalidate('username_taken');

      if($this->User->findByEmail($this->data['User']['email']))
	$this->User->invalidate('email_taken');

      $this->User->set($this->data);
      if($this->User->validates($this->data)) {

	if($this->data['User']['level_type'] == 'reseller' || $this->data['User']['level_type'] == 'salesperson' || $this->data['User']['level_type'] == 'admin')
	  $this->data['User']['layout'] = 'business';
	else
	  $this->data['User']['layout'] = 'client';	  

	$this->data['User']['salt']            = generate_token();
	$this->data['User']['salted_password'] = sha1($this->data['User']['salt'] . $this->data['User']['password']);

	if($this->User->save($this->data)) {
	  $id = $this->User->getLastInsertId();

	  // Create group for user
	  $type = null;
	  $assoc = null;

	  if($this->data['User']['level_type'] == 'reseller' || $this->data['User']['level_type'] == 'admin' || $this->data['User']['level_type'] == 'reseller_admin') {
	    $type = 'ResellerGroup';
	    $assoc = 'Resller';
	  } else if($this->data['User']['level_type'] == 'salesperson') {
	    $type = 'SalespersonGroup';
	    $assoc = 'Salesperson';
	  }

	  if($assoc && empty($this->data[$assoc]))
	    $this->data[$assoc][$assoc] = a(-1);

	  if($type) {
	    $group = $this->$type->read(null, $id);
	    $this->data[$type] = aa('user_id', $id, 'name', $this->data['User']['name']);
	    $this->$type->save($this->data);
	  }

	  $this->diffLog('User', DIFFLOG_OP_CREATE, $id, $this->data['User']);

	  $this->Session->setFlash('User created');
	  $this->redirect("/users/view/{$id}");
	} else {
	  $this->Session->setFlash('User creation failed');
	}
      }
    }

    $this->set('grouped_resellers', $grouped_resellers);
    $this->set('grouped_salespeople', $grouped_salespeople);
  }

  function edit($id=null) {

    $this->User->recursive=2;
    $user = $this->User->read(null, $id);
    $this->set('user', $user);

    $this->set('resellers', $this->Reseller->buildList());

    foreach($this->Salesperson->findAll(null, null, 'Salesperson.name ASC', null, null, 0) as $i)
      $salespeople[$i['Salesperson']['salespid']] = $i['Salesperson']['name'];

    $this->set('salespeople', $salespeople);

    $grouped_resellers   = a();
    $grouped_salespeople = a();

    if(!empty($this->data)) {

      if(isset($this->data['Reseller']['Reseller']))
	$grouped_resellers = $this->data['Reseller']['Reseller'];

      if(isset($this->data['Salesperson']['Salesperson']))
	$grouped_salespeople = $this->data['Salesperson']['Salesperson'];

      $this->User->validate['level_type'] = VALID_NOT_EMPTY;

      if(!empty($this->data['User']['username'])) {
	$found_username = $this->User->findByUsername($this->data['User']['username']);
	if($found_username && $found_username['User']['id'] != $id)
	  $this->User->invalidate('username_taken');
      }

      $found_email = $this->User->findByEmail($this->data['User']['email']);
      if($found_email && $found_email['User']['id'] != $id)
	$this->User->invalidate('email_taken');

      if($this->User->save($this->data)) {

	// Create group for user
	$type  = null;
	$assoc = null;

	if($this->data['User']['level_type'] == 'reseller' || $this->data['User']['level_type'] == 'admin' || $this->data['User']['level_type'] == 'reseller_admin') {
	  $type = 'ResellerGroup';
	  $assoc = 'Resller';
	} else if($this->data['User']['level_type'] == 'salesperson') {
	  $type = 'SalespersonGroup';
	  $assoc = 'Salesperson';
	}

	if($assoc && empty($this->data[$assoc]))
	  $this->data[$assoc][$assoc] = a(-1);

	if($type) {
	  $group = $this->$type->read(null, $id);
	  $this->data[$type] = aa('user_id', $id, 'name', $this->data['User']['name']);
	  $this->$type->save($this->data);
	}

	$this->diffLog('User', DIFFLOG_OP_UPDATE, $id, $this->data['User'], $user['User']);

	$this->Session->setFlash('User updated');
	$this->redirect("/users/view/{$id}");
      }

    } else {
      $this->data = $user;

      if(isset($user['SalespersonGroup']['Salesperson'])) {
	foreach($user['SalespersonGroup']['Salesperson'] as $i)
	  $grouped_salespeople[] = $i['salespid'];
      }

      if(isset($user['ResellerGroup']['Reseller'])) {
	foreach($user['ResellerGroup']['Reseller'] as $i)
	  $grouped_resellers[] = $i['resellerid'];
      }
    }

    $this->set('grouped_resellers', $grouped_resellers);
    $this->set('grouped_salespeople', $grouped_salespeople);
  }

  function forgot() {
    if(!empty($this->data)) {
      if(!empty($this->data['User']['email'])) {
	
	$user = $this->User->FindByEmail($this->data['User']['email']);
	if($user && $user['User']['active']>0) {
	  
	  
	  $user['User']['verification_code'] = generate_token();
	  $user['User']['verified']          = 0;

	  if($this->User->save($user)) {
	    $this->set('user', $user['User']);
	    $this->set('recipient', $user['User']['name']);
	    $this->Email->Subject = 'MyConferenceAdmin Password Recovery';
	    $this->Email->AddAddress($user['User']['email'], $user['User']['name']);
	    $this->Email->renderBody('email\forgot');
	    $this->Email->Send();
	  
	    $this->Session->setFlash('Your password has been sent to the email address on file.  Please follow the instructions to reactivate your account.');
	    $this->redirect('/users/login');
	  } else {
	    $this->Session->setFlash('An error occurred');
	  }	    
	} else {
	  $this->Session->setFlash('Email address was not found');
	}

      } else {
	$this->User->invalidate('email'); 
      }
    }
  }
  
  function reset($verification_code){
     //Reset and Verify forgotten password.
	 
     $user = $this->User->findByVerificationCode($verification_code);
	 if(empty($user)) {
      $this->Session->setFlash('Verification code not found');
	  $this->redirect('/users/login');
      }
	
	if ($user['User']['verified']){
	   $this->Session->setFlash('User '. $user['User']['email']. ' has already used verification code '.$user['User']['verification_code'].
	                              ' for password reset. We could not verify your request.');
		$this->redirect('/users/login');
		}
	
	if(!empty($this->data)){
	
	   $valid =true;
	   
	   if(!empty($this->data['User']['password'])) {
	   
	      if(!$this->User->validates($this->data)) {
	          $this->User->invalidate('password');
	          $valid = false;
            }
			
		  if($this->data['User']['password'] != $this->data['User']['confirm_password']) {
	         $this->User->invalidate('confirm_password');
	         $valid = false;
            }
			
	      $wpfile = '../weak-passwords.txt';
	      $weakpassword = file($wpfile, FILE_IGNORE_NEW_LINES);
          $password = strtolower($this->data['User']['password']);
	      foreach ($weakpassword as $value){
	          if($value == $password){
	             $this->User->invalidate('password');
			     $this->set('errorMessage', 'Password is insecure, please enter a different password.');
		         $valid = false;
		         break;
	   } 
    }

	      if ( strlen(count_chars($this->data['User']['password'], 3)) < 3)
	     {
		    $this->User->invalidate('password');
			$this->set('errorMessage', 'Password must have at least 3 unique characters.');
			$valid = false;
		 }
			
		  if($valid){
		    $user['User']['salt'] = generate_token();
			$user['User']['salted_password'] = sha1($user['User']['salt'] . $this->data['User']['password']);
			$user['User']['verified'] = 1;
			
		      if($this->User->save($user)) {
	             $this->Session->setFlash('Password changed');
				 $this->redirect('/users/login');
	          } else {
	            $this->Session->setFlash('Password change failed');
	            }
	       }
		 
		 }
		}
		 
	}

 
  function index() {
    $criteria=NULL;

    if(!empty($_GET['query'])) {
    $query = $_GET['query'];
    $fquery = implode('%', preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY));

    $criteria['OR'] = Array('User.name' => "LIKE {$fquery}%",
             'User.company_name'        => "LIKE {$fquery}%",
             'User.email'               => "LIKE {$fquery}%", );
    } else {
      $query = '';
    }
    $this->set('query', $query);
 
    $active_only = isset($_GET['active_only']) ? $_GET['active_only'] : 1;
    $this->set('active_only', $active_only);

    if($active_only)
      $criteria['User.active'] = 1;
    
    $this->set('group', Array( 'Account', 'Accountgroup', 'Admin', 'Reseller', 'Salesperson'));

    if(!empty($_GET['group'])) {
      $group                       = $_GET['group'];
      $criteria['User.level_type'] = $group;
    } else {
      $group = null;
    }
    $this->set('selected_grp', $group);

    list($order, $limit, $page) = $this->Pagination->init($criteria);
    $this->set('data', $this->User->findAll($criteria, NULL, $order, $limit, $page));
  }

  function login() {
    $this->pageTitle = 'Login';
    $this->layout = 'login';

    if(!empty($this->data)) {
      if($user = $this->User->authenticate($this->data['User']['email'], $this->data['User']['password'])) {
	if($user['User']['active'] > 0){
	  if($user['User']['verified'] > 0) {

	    $this->User->id = $user['User']['id'];
	    $this->User->saveField('logins', $user['User']['logins']+1);

	    $resellerids = Array();
	    $user['ResellerGroup'] = $this->ResellerGroup->read(null, $user['User']['id']);
	    if($user['ResellerGroup'])
	      foreach($user['ResellerGroup']['Reseller'] as $r)
		$resellerids[] = $r['resellerid'];

	    $salespids = Array();
	    $user['SalespersonGroup'] = $this->SalespersonGroup->read(null, $user['User']['id']);
	    if($user['SalespersonGroup']) {
	      foreach($user['SalespersonGroup']['Salesperson'] as $s) {
		$salespids[] = $s['salespid'];
		$resellerids[] = $s['resellerid'];	    
	      }
	    }

	    if($user['User']['ic_employee']) {
	      $this->Session->write('ic_employee', true);
	      $resellerids = null;
	    }

	    $user['Resellers']   = $resellerids;
	    $user['Salespersons'] = $salespids;
	  
	    $theme = $user['User']['theme'];
	    if(!file_exists("css/themes/$theme"))
	      $theme = 'default';

	    $this->Session->write('User', $user);
	    $this->Session->write('theme', $theme);

	    $this->systemLog('login success');					

	    if($this->data['User']['Remember']) {	      
	      setcookie('myca_email', $user['User']['email'], time() + LOGIN_COOKIE_EXPIRY);
	      setcookie('myca_password', $user['User']['password'], time() + LOGIN_COOKIE_EXPIRY);	      
	    }

	    if(!empty($_GET['back']))
	      $this->redirect($_GET['back']);
	    else
	      $this->redirect('/');

	  } else {
	    $this->systemLog('login failure', 'not verfied ['.$this->data['User']['email'].'].');
	    $this->Session->setFlash('Please check your email for account verficiation instructions to verify this account');
	  }
	} else {
	  $this->systemLog('login failure', 'inactive ['.$this->data['User']['email'].'].');
	  $this->Session->setFlash('The login credentials you supplied could not be recognized');
	}
      }else{
	$this->systemLog('login failure', 'invalid credentials ['.$this->data['User']['email'].'|'.$this->data['User']['password'].'].');
	$this->Session->setFlash('The login credentials you supplied could not be recognized');
      }
    } else {
      if(!$this->Session->check('User')) { 
	if(isset($_COOKIE['myca_email']))
	  $this->data['User']['email'] = $_COOKIE['myca_email'];
	
	if(isset($_COOKIE['myca_password']))
	  $this->data['User']['password'] = $_COOKIE['myca_password'];
      } else {
	$this->redirect('/');
      }
    }
  }

  function logout() {
    $this->Session->delete('User');
    $this->Session->delete('theme');
    $this->Session->delete('ic_employee');
    $this->Session->delete('history');
    $this->redirect('/users/login');
  }
  
  function password() {
    if(!empty($this->data)) {

      $current_user = $this->Session->read('User');
      $valid = true;

      if(!$this->User->authenticate($current_user['User']['email'], $this->data['User']['old_password'])) {
	     $this->User->invalidate('old_password');
	     $valid = false;
      }

      if(!$this->User->validates($this->data)) {
	     $this->User->invalidate('password');         
	      $valid = false;
      }

      if($this->data['User']['password'] != $this->data['User']['confirm_password']) {
	      $this->User->invalidate('confirm_password');
	       $valid = false;
      }
	  
	  $wpfile = '../weak-passwords.txt';
	  $weakpassword = file($wpfile, FILE_IGNORE_NEW_LINES);
      $password = strtolower($this->data['User']['password']);
	  foreach ($weakpassword as $value){
	     if($value == $password){
	        $this->User->invalidate('password');
			$this->set('errorMessage', 'Password is insecure, please enter a different password.');
		    $valid = false;
		    break;
	   } 
    }

	   if ( strlen(count_chars($this->data['User']['password'], 3)) < 3)
	     {
		    $this->User->invalidate('password');
			$this->set('errorMessage', 'Password must have at least 3 unique characters.');
			$valid = false;
		 }

     if($valid) {
	$this->User->id = $current_user['User']['id'];
	$user = $this->User->read();
	$user['User']['salt']            = generate_token();
	$user['User']['salted_password'] = sha1($user['User']['salt'] . $this->data['User']['password']);

	if($this->User->save($user)) {
	  $this->Session->setFlash('Password changed');
	} else {
	  $this->Session->setFlash('Password change failed');
	}	  
      }
    }
  }

  function view($id=null)
  {
    if($user = $this->User->read(null, $id)) {
      $this->set('user', $user);
      $this->set('requests', $this->RequestView->findAll(Array('creator' => $user['User']['id']), null, 'created DESC', 20));
      $this->set('diffs', $this->DiffLog->findAll(Array('DiffLog.userid' => $user['User']['id']), null, 'DiffLog.created DESC', 20));
      $this->set('op_map', $this->DiffLog->op_map);
    } else {
      $this->Session->setFlash('A user was not found by that ID');
      $this->redirect('/users');	
    }
  }

  function verify($verification_code)
  {
    if($user = $this->User->findByVerificationCode($verification_code)) { 
      
      $user['User']['verified'] = 1;
      if($this->User->save($user)) { 
	$this->Session->setFlash('Your account has been verified, you may now log in');
      } else {
	$this->Session->setFlash('An error occurred verifing this account');
      }
    } else {
      $this->Session->setFlash('Verification code not found');
    }
    
    $this->redirect('/users/login');
  }
}
