<?

class PinsController extends AppController
{
  var $uses        = Array('Pin', 'Account', 'Request');
  var $helpers     = Array('Pagination');
  var $components  = Array('Pagination', 'Email', 'RequestHandler');
  var $permissions = GROUP_ALL;
  
   function create($acctgrpid=null)
   {
     $user = $this->Session->read('User');

     if($acctgrpid) {
       if($account = $this->Account->get($acctgrpid, $user)) { 

	 $this->set('acctgrpid', $acctgrpid);
	 $this->set('bridges', Array(OCI_BRIDGEID => 'icbr1', SPECTEL_BRIDGEID => 'icbr2'));

	 $user = $this->Session->read('User');

	 if(!empty($this->data)) {
       
	   $this->Pin->set($this->data);
	   if($this->Pin->validates($this->data)) {	   

	     if($this->Request->saveRequest(REQTYPE_PIN_CREATION, $user['User']['id'], $acctgrpid, null, $this->data['Pin'], REQSTATUS_APPROVED)) { 
	       $this->Session->setFlash('Your request has been submitted');
	       $this->redirect('/pins/index/' . $acctgrpid);
	     } else {
	       $this->Session->setFlash('Request submission failed');
	     }

	     $this->redirect('/pins/index/' . $acctgrpid);
	   }
	 } else {
	   $this->data = Array('Pin' => Array( 'pin'     => $this->Pin->generate(), 
					       'company' => $acctgrpid ));
	 }
       } else {
	 $this->Session->setFlash('Account not found');
	 $this->redirect('/accounts');	 
       }
     } else {
       $this->Session->setFlash('Account information not passed');
       $this->redirect('/accounts');
     }
   }
   
   function delete($id=null)
   {
     $user = $this->Session->read('User');
 
     if($pin = $this->Pin->read(null, $id)) {       
       if ($user['User']['level_type'] == SALESPERSON_LEVEL and
           ($user['Salespersons'] == null or
           !in_array($pin['Account']['salespid'], $user['Salespersons']))){
         $this->Session->setFlash('PIN not found');
         $this->redirect('/accounts');
       }
       $this->set('pin', $pin);
       $this->set('bridges', Symbols::$bridges);

       if(!empty($this->data)) {  
	 if($this->Request->saveRequest(REQTYPE_PIN_DELETION, $user['User']['id'], $pin['Pin']['company'], null, $this->data['Pin'], REQSTATUS_APPROVED)) { 
	   $this->Session->setFlash('Your request has been submitted');
	   $this->redirect('/pins/view/' . $id);
	 } else {
	   $this->Session->setFlash('Request submission failed');
	 }
       } else {
	 $this->data = $pin;
       }
     } else {
       $this->Session->setFlash('PIN not found');
       $this->redirect('/accounts');
     }
   }

   function edit($id=null)
   {
     $user = $this->Session->read('User');

     if($pin = $this->Pin->read(null, $id)) {
       $this->set('bridges', Array(OCI_BRIDGEID => 'icbr1', SPECTEL_BRIDGEID => 'icbr2'));
       if ($user['User']['level_type'] == SALESPERSON_LEVEL and
           ($user['Salespersons'] == null or
           !in_array($pin['Account']['salespid'], $user['Salespersons']))){
         $this->Session->setFlash('PIN not found');
         $this->redirect('/accounts');
       }

       if(!empty($this->data)) {
	 $this->Pin->set($this->data);
	 if($this->Pin->validates($this->data)) {

	   if($this->Request->saveRequest(REQTYPE_PIN_UPDATE, $user['User']['id'], $pin['Pin']['company'], null, $this->data['Pin'], REQSTATUS_APPROVED)) { 
	     $this->Session->setFlash('Your request has been submitted');
	     $this->redirect('/pins/view/' . $id);
	   } else {
	     $this->Session->setFlash('Request submission failed');
	   }
	   
	 }	 
       } else {
	 $this->data = $pin;
       }

     } else {       
       $this->Session->setFlash('PIN not found');
       $this->redirect('/accounts');
     }
   }

   function import($acctgrpid=null)
   {
     set_time_limit(0);
 
     $user = $this->Session->read('User');

     if($account = $this->Account->get($acctgrpid, $user)) { 
       $this->set('bridges', Array(OCI_BRIDGEID => 'icbr1', SPECTEL_BRIDGEID => 'icbr2'));

       if(!empty($this->data)) {
	 if(!empty($this->data['Pin']['bridgeid'])) { 

	   $data = a();

	   switch($this->data['Form']['type']) {
	   case 'paste':
	     $rows = preg_split('/\r\n/', $this->data['Data']['Rows']);

	     foreach($rows as $i)
	       if($i)
		 $data[] = explode("\t", $i);

	     break;

	   case 'csv':
	     if($_FILES['upload']['error'] == UPLOAD_ERR_OK) {
	       $f = fopen($_FILES['upload']['tmp_name'], 'r');

	       while(($row = fgetcsv($f)) !== false) {
		 if($row)
		   $data[] = $row;
	       }
	     }

	     break;
	   }

	   if($data) {
	     // Build a map of the header => col index from row 0
	     $map = a();
	     foreach($data[0] as $idx => $col)
	       $map[strtolower($col)] = $idx;
      
	     // Make sure the map built contains the fields we were expecting
	     $missing_fields = a();

	     if(!array_key_exists('first name', $map))
	       $missing_fields[] = 'First Name';

	     if(!array_key_exists('last name', $map))
	       $missing_fields[] = 'Last Name';

	     if(!$missing_fields) {

	       // Dummy row needed
	       $rows = a(a());
	       $errors = a();
	
	       for($i=1; $i<count($data) && $i<MAX_PINS+1; $i++) {
	   
		 $row = Array( 'company'    => $acctgrpid,
			       'first_name' => $data[$i][$map['first name']],
			       'last_name'  => $data[$i][$map['last name']], 
			       'bridgeid'   => $this->data['Pin']['bridgeid'] );

		 if(isset($map['pin']) && !empty($data[$i][$map['pin']]))
		   $row['pin'] = $data[$i][$map['pin']];
		 else
		   $row['pin'] = $this->Pin->generate();

		 $this->Pin->set($row);
		 if($this->Pin->validates($row)) {
		   $rows[$i]   = $row;
		 } else {
		   $errors[$i] = array_keys($this->Pin->invalidFields());
		 }
	       }
	       
	       $this->set('rows', $rows);
	       $this->set('errors', $errors);

	       if(!$errors && count($rows)>1) {
		 for($i=1; $i<count($rows); $i++) 
		   $this->Request->saveRequest(REQTYPE_PIN_CREATION, $user['User']['id'], $acctgrpid, null, $rows[$i], REQSTATUS_APPROVED);

		 $this->Session->setFlash('Your requests have been submitted');
		 $this->redirect('/pins/index/' . $acctgrpid);
	       }
	     } else {
	       $this->Session->setFlash('The following columns were missing from the first row: ' . implode(', ', $missing_fields));
	     }

	   } else {
	     $this->Session->setFlash('Please specify one or more pins to import');
	   }
	 } else {
	   $this->Pin->invalidate('bridgeid');
	 }
       }    
     } else {
       $this->Session->setFlash('Account not found');
       $this->redirect('/accounts');
     }
   }

   function index($acctgrpid=null)
   {
     $user = $this->Session->read('User');

     if($account = $this->Account->get($acctgrpid, $user)) { 

       $this->set('bridges', Symbols::$bridges);

       $criteria = Array('Pin.active' => 1);

       if(!empty($_GET['query'])) {
	 $query = $_GET['query'];

	 $criteria['OR'] = Array('Pin.first_name'   => "LIKE %{$query}%",
				 'Pin.last_name'    => "LIKE %{$query}%",
				 'Pin.company  '    => "LIKE %{$query}%",
				 'Pin.pin'          => "LIKE %{$query}%" );
       } else {
	 $query = '';
       }
       $this->set('query', $query);
     
       if($acctgrpid) {
	 $this->set('acctgrpid', $acctgrpid);
	 $criteria['Pin.company'] = $acctgrpid;
       }

       list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'id'));
       $this->set('data', $this->Pin->findAll($criteria, NULL, $order, $limit, $page));
     } else {
       $this->Session->setFlash('Account not found');
       $this->redirect('/accounts');
     }
	 
	 if($is_ajax = $this->RequestHandler->isAjax() || !empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
    }
    $this->set('is_ajax', $is_ajax);
	
	if(!empty($_GET['export']))
      list($limit, $page) = Array(null, null);
	
	
    $data = $this->Pin->findAll($criteria, NULL, $order, $limit, $page);
	
	foreach($data as &$i){
	  $i['Pin']['bridge_desc'] = Symbols::$bridges[$i['Pin']['bridgeid']];
	  }
	  
	if(!empty($_GET['export'])) {
      $filename = 'PINs_' . date('Y-m-d');
      $keys     = Array('pin', 'first_name', 'last_name', 'bridge_desc');
      $headers  = Array('pin'   => 'PIN',
                        'first_name' => 'First Name',
                        'last_name'         => 'Last Name',
                        'bridge_desc' => 'Bridge'
						);

      export_csv($filename, $keys, $headers, pluck($data, 'Pin'));
	  
	  die;
	  } else {
	     $this->set('data', $data);
		 }
   }
   
    
    
   function view($id=null)
   {
     $user = $this->Session->read('User');
     if($pin = $this->Pin->read(null, $id)) {
       if ($user['User']['level_type'] == SALESPERSON_LEVEL and
           ($user['Salespersons'] == null or
           !in_array($pin['Account']['salespid'], $user['Salespersons']))){
         $this->Session->setFlash('PIN not found');
         $this->redirect('/accounts');
       }
       $this->set('pin', $pin);
       $this->set('bridges', Symbols::$bridges);
     } else {
       $this->Session->setFlash('PIN not found');
       $this->redirect('/accounts');
     }
   }
}
