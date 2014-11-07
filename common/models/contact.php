<?

class Contact extends AppModel
{
  var $name        = 'Contact';
  var $useTable    = 'contacts';
  var $primaryKey  = 'id';

  var $validate = Array( 'first_name' => '/^\w+/', 
			 'last_name'  => '/^\w+/',
			 'phone'      => '/^\d+/',
			 'email'      => VALID_EMAIL, 
			 'company'    => '/^\w+/',
			 'address1'   => '/^\w+/',
			 'city'       => '/^\w+/',
			 'zip'        => VALID_NOT_EMPTY,
			 'country'    => '/^[A-Z]{2}/', 
			 'time_zone'  => VALID_NOT_EMPTY);

  var $belongsTo = Array( 'Account'      => Array( 'className'  => 'Account',
						   'foreignKey' => 'acctgrpid' ),

			  'Status'       => Array( 'className'  => 'Status', 
						   'foreignKey' => 'status' ) );

  var $hasAndBelongsToMany = array('Room' => array('className'            => 'Room',
						   'joinTable'            => 'contact_accounts',
						   'foreignKey'           => 'contact_id',
						   'associationForeignKey'=> 'accountid', 
						   'conditions'           => 'Room.roomstat IN (0,1)' ));

  

  var $error_msg;

  function __construct($id=false, $table=null, $ds=null)
  {
    parent::__construct($id, $table, $ds);

    loadModel('Bt');
    $this->Bt = new Bt();
  }

  function beforeValidate()
  {
    parent::beforeValidate();
    
    if(!empty($this->data['Contact']['country']) && ($this->data['Contact']['country'] == 'US' || $this->data['Contact']['country'] == 'CA')) {
      $this->validate['state'] = '/[A-Z]{2}/';
    }

    return true;
  }

  function get($contact_id, $user)
  {
    $contact = $this->read(null, $contact_id);
    if($user['User']['level_type'] == SALESPERSON_LEVEL and 
        ($user['Salespersons'] == null or 
        !in_array($contact['Account']['salespid'], $user['Salespersons']))){
          return null;
    }
    return $contact;
  }

  function createContact($user, $account, $data) 
  {
    $data['creator']   = $user['User']['id'];
    $data['acctgrpid'] = $account['Account']['acctgrpid'];

    $out = Array( 'id'      => 'VARCHAR(50)', 
		  'message' => 'VARCHAR(200)' );

    $rv = $this->sproc('CreateContact', $data, $out);
 
    return $rv['id'];
  }
  
  function updateContact($user, $contact, $data) 
  {
    $data['creator'] = $user['User']['id'];
    $data['id']      = $contact['Contact']['id'];

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('UpdateContact', $data, $out);
    
    return $rv['rv'];
  }

  function associateRoom($contact, $room)
  {
    $data = Array( 'accountid'  => $room['Room']['accountid'],
		   'contact_id' => $contact['Contact']['id'] );

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('AssociateRoomWithContact', $data, $out);
 
    return $rv['rv'];
  }

  function fullName($contact)
  {
    $rv = $contact['Contact']['first_name'];

    if(!empty($contact['Contact']['middle_name']))
      $rv .= (' ' . $contact['Contact']['middle_name']);

    $rv .= (' ' . $contact['Contact']['last_name']);
    
    return $rv;
  }

  function syncContact($contact, $update_only=false)
  {
    $rv = $this->Bt->saveContact($contact, $update_only);
    $this->error_msg = $this->Bt->output[2];   
    return $rv;
  }

  function suspendContact($user, $contact, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'    => $user['User']['id'],
		   'contact_id' => $contact['Contact']['id'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('SuspendContact', $data, $out);
 
    return $rv['rv'];
  }

  function activateContact($user, $contact, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'    => $user['User']['id'],
		   'contact_id' => $contact['Contact']['id'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('ActivateContact', $data, $out);
 
    return $rv['rv'];
  }

  function cancelContact($user, $contact, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator' => $user['User']['id'],
		   'contact_id' => $contact['Contact']['id'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('CancelContact', $data, $out);
 
    return $rv['rv'];
  }

  // This function is used to request the update
  function updateStatus($user, $contact, $status, $reason=null, $effective_date=null)
  {
    switch($status) {
    case STATUS_ACTIVE:
      $rv = $this->activateContact($user, $contact, $reason, $effective_date);
      break;
      
    case STATUS_SUSPENDED:
      $rv = $this->suspendContact($user, $contact, $reason, $effective_date);
      break;

    case STATUS_CANCELLED:
      $rv = $this->cancelContact($user, $contact, $reason, $effective_date);
      break;

    default:
      $rv = false;
      break;
    }

    return $rv;
  }

  // This function is used to complete the request
  function changeStatus($contact, $status, $reason=null)
  {
    switch($status) {
    case STATUS_ACTIVE:
      $rv         = true;
      $sproc_name = 'MarkContactActive';
      break;
      
    case STATUS_SUSPENDED:
      $rv         = true;
      $sproc_name = 'MarkContactSuspended';
      break;

    case STATUS_CANCELLED:
      // FIXME since we cannot enable contacts, dont disable
      $rv         = true;
      $sproc_name = 'MarkContactCancelled';
      break;

    default:
      $rv         = false;
      $sproc_name = null;
      break;
    }

    if($rv) {
      if($sproc_name)
	$this->sproc($sproc_name, Array('contact_id' => $contact['Contact']['id']));

    }

    return $rv;
  }

  function getDomains($acctgrpid){
    $sql = "(select distinct substring(email, (charindex('@',email) + 1), (len(email) - charindex('@',email) + 1))
          from contacts where acctgrpid = '$acctgrpid' and email is not null and email <> '')
          union
          (select distinct substring(email, (charindex('@',email) + 1), (len(email) - charindex('@',email) + 1))
          from accountgroup where acctgrpid = '$acctgrpid' and email is not null and email <> '')
          union
          (select distinct substring(email, (charindex('@',email) + 1), (len(email) - charindex('@',email) + 1))
          from account where acctgrpid = '$acctgrpid' and email is not null and email <> '')";
    $rawDomains = $this->query($sql);
    $domains = array();
    foreach($rawDomains as $key => $row){
      $cleanedDomain = preg_replace("/,.*/", "", $row[0]['computed']);
      if (!in_array($cleanedDomain, $domains) && $cleanedDomain <> 'infiniteconferencing.com'){
        $domains[$key] = $cleanedDomain;
      } else {
      }
    }
    
    return $domains;

  }
}
