<?

// NB: This is the actual account table not the view
class Account extends AppModel
{
  var $name        = 'Account';
  var $useTable    = 'accountgroup';
  var $primaryKey  = 'acctgrpid';

  var $validate = Array( 'acctgrpid'              => '/^\w+$/',
			 'salespid'               => VALID_NOT_EMPTY,
			 'dialinNoid'             => VALID_NOT_EMPTY,
			 'bcontact_first_name'    => '/^\w+/',
			 'bcontact_last_name'     => '/^\w+/',			 
			 'bcompany'               => '/^\w+/',
			 'baddr1'                 => '/^\w+/', 
			 'email'                  => VALID_EMAIL,
			 'city'                   => '/^\w+/', 
			 'state'                  => '/^\w+/', 
			 'zip'                    => VALID_NOT_EMPTY, 
			 'phone'                  => VALID_NOT_EMPTY, 
 			 'corpcontact_first_name' => '/^\w+/',
			 'corpcontact_last_name'  => '/^\w+/',			 
			 'corpphone'              => VALID_NOT_EMPTY, 
			 'corpemail'              => VALID_EMAIL,			 
			 'invoice_terms_id'                  => VALID_NOT_EMPTY,
                         'billing_method_id'      => VALID_NUMBER,
			 'invdelmeth'             => VALID_NOT_EMPTY,
			 'default_servicerate'    => VALID_NOT_EMPTY,
			 'time_zone'              => VALID_NOT_EMPTY,
			 'country'           => VALID_NOT_EMPTY);

  var $output;

  function __construct($id = false, $table = null, $ds = null)
  {
    // We only need these on the biz side of the site
    if(BIZ_SIDE) {
      $this->belongsTo = Array( 'Salesperson'           => Array( 'className'  => 'Salesperson', 
								  'foreignKey' => 'salespid'), 

				'ServiceRate'           => Array( 'className'  => 'ServiceRate', 
								  'foreignKey' => 'default_servicerate'), 

				'Status'                => Array( 'className'  => 'Status', 
								  'foreignKey' => 'acctstat' ),

				'InvoiceDeliveryMethod' => Array( 'className'  => 'InvoiceDeliveryMethod', 
								  'foreignKey' => 'invdelmeth' ),
								  

        'State' => Array( 'className'  => 'State',
								  'foreignKey' => 'state' ),

				'InvoiceType'           => Array( 'className'  => 'InvoiceType', 
								  'foreignKey' => 'invoicetype' ), 
				);

      $this->hasOne = Array( 'DefaultBridge' => Array( 'className'  => 'DefaultBridge', 
						       'foreignKey' => 'acctgrpid' ), 

			     'Branding'      => Array( 'className'  => 'Branding', 
						       'foreignKey' => 'acctgrpid' ));

      $this->hasAndBelongsToMany = array('BillingContact' => array('className'             => 'Contact',
								   'joinTable'             => 'accountgroup_contacts',
								   'foreignKey'            => 'acctgrpid',
								   'associationForeignKey' => 'billing_contact_id',
								   'conditions'            => '',
								   'order'                 => '',
								   'limit'                 => '',
								   'unique'                => true,
								   'finderQuery'           => '',
								   'deleteQuery'           => '' ), 
					 'CorporateContact' => array('className'             => 'Contact',
								     'joinTable'             => 'accountgroup_contacts',
								     'foreignKey'            => 'acctgrpid',
								     'associationForeignKey' => 'corp_contact_id',
								     'conditions'            => '',
								     'order'                 => '',
								     'limit'                 => '',
								     'unique'                => true,
								     'finderQuery'           => '',
								     'deleteQuery'           => '' ), 

                                         'BillingMethod' => Array( 'className'             => 'BillingMethod',
                                                                   'joinTable'             => 'accountgroup_billing_method', 
                                                                   'foreignKey'            => 'acctgrpid',
                                                                   'associationForeignKey' => 'billing_method_id' ),
                                         
                                         'BillingFrequency' => Array( 'className'             => 'BillingFrequency',
                                                                      'joinTable'             => 'accountgroup_billing_method', 
                                                                      'foreignKey'            => 'acctgrpid',
                                                                      'associationForeignKey' => 'billing_frequency_id' ));
    }

    parent::__construct($id, $table, $ds);

    loadModel('Bt');
    $this->Bt = new Bt();
  }

  function beforeValidate()
  {
    parent::beforeValidate();

    // Validate bcompany to be unique
    if(!empty($this->data['Account']['bcompany'])) {
      $criteria = Array('Account.bcompany' => $this->data['Account']['bcompany']);

      if(!empty($this->data['Account']['acctgrpid']))
	$criteria['Account.acctgrpid'] = '<>' . $this->data['Account']['acctgrpid'];

      if($this->findCount($criteria)>0)
	$this->invalidate('bcompany');
    }

    if(isset($this->data['Account']['same_as_billing']) && $this->data['Account']['same_as_billing']) {
      unset($this->validate['corpcontact']);
      unset($this->validate['corpphone']);
      unset($this->validate['corpemail']);
    } elseif(isset($this->data['Account']['corpemail']) && !validate_email_list($this->data['Account']['corpemail'])) {
      $this->invalidate('corpemail');
    }

    return true;
  }

  function beforeSave() 
  {
    parent::beforeSave();

    if(empty($this->data['Account']['default_canada']))
      $this->data['Account']['default_canada'] = 0;

    if(isset($this->data['Account']['same_as_billing']) && $this->data['Account']['same_as_billing']) {
      $this->data['Account']['corpcontact'] = $this->data['Account']['bcontact'];
      $this->data['Account']['corpphone'] = $this->data['Account']['phone'];
      $this->data['Account']['corpemail'] = $this->data['Account']['email'];
    }

    return true;
  }

  function createAccount($user, $salesperson, $data) 
  {
    $data['creator']  = $user['User']['id'];
    $data['salespid'] = $salesperson['Salesperson']['salespid'];

    $out = Array( 'id'      => 'VARCHAR(50)', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('CreateAccount', $data, $out);
 
    return $rv['id'];
  }
  
  function updateAccount($user, $account, $data) 
  {
    $data['creator']   = $user['User']['id'];
    $data['acctgrpid'] = $account['Account']['acctgrpid'];

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('UpdateAccount', $data, $out);
    
    return $rv['rv'];
  }

  function updateDefaultBridge($account, $bridge)
  {
    $data = Array( 'acctgrpid' => $account['Account']['acctgrpid'], 
		   'bridge_id' => $bridge['Bridge']['id'] );

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('UpdateAccountDefaultBridge', $data, $out);
    
    return $rv['rv'];    
  }

  function updateContacts($account, $billing_contact_id, $corp_contact_id)
  {
    $data = Array( 'acctgrpid'          => $account['Account']['acctgrpid'], 
		   'billing_contact_id' => $billing_contact_id,
		   'corp_contact_id'    => $corp_contact_id);

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('UpdateAccountContacts', $data, $out);
    
    return $rv['rv'];        
  }

  function setBillingMethod($account, $billing_method_id, $billing_frequency_id=null, $flat_rate_charge=null)
  {
    $data = Array( 'acctgrpid'            => $account['Account']['acctgrpid'], 
		   'billing_method_id'    => $billing_method_id,
		   'billing_frequency_id' => $billing_frequency_id,
                   'flat_rate_charge'     => $flat_rate_charge);

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(200)' );
    
    $rv = $this->sproc('SetAccountBillingMethod', $data, $out);
    
    return $rv['rv'];
  }

  function get($acctgrpid, $user)
  {
    $account = $this->read(null, $acctgrpid);

    if($account) {
      //empty arrays are not null according to is_null.
      //It seems that much of this is a giant hack. This should probably be 
      //redone -DC 2012/09/04
      if(!is_null($user['Resellers'])) {
        if(!isset($account['Salesperson']))
          $account['Salesperson'] = $this->Salesperson->read(null, 
            $account['Account']['salespid']);
        if(!in_array($account['Salesperson']['resellerid'], $user['Resellers']))
	      return null;
      }
      if($user['User']['level_type'] == SALESPERSON_LEVEL and 
        ($user['Salespersons'] == null or 
        !in_array($account['Account']['salespid'], $user['Salespersons']))){
          return null;
      }
    }

    return $account;
  }

  function suspendAccount($user, $account, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'   => $user['User']['id'], 
		   'acctgrpid' => $account['Account']['acctgrpid'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('SuspendAccount', $data, $out);
 
    return $rv['rv'];
  }

  function activateAccount($user, $account, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'   => $user['User']['id'], 
		   'acctgrpid' => $account['Account']['acctgrpid'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('ActivateAccount', $data, $out);
 
    return $rv['rv'];
  }

  function cancelAccount($user, $account, $reason=null, $effective_date=null)
  {
    $data = Array( 'creator'   => $user['User']['id'], 
		   'acctgrpid' => $account['Account']['acctgrpid'] );

    if($reason)
      $data['reason'] = $reason;

    if($effective_date)
      $data['effective_date'] = $effective_date;

    $out = Array( 'rv'      => 'BIT', 
		  'message' => 'VARCHAR(50)' );
    
    $rv = $this->sproc('CancelAccount', $data, $out);
 
    return $rv['rv'];
  }

  // This function is used to request the update
  function updateStatus($user, $account, $status, $reason=null, $effective_date=null)
  {
    switch($status) {
    case STATUS_ACTIVE:
      $rv = $this->activateAccount($user, $account, $reason, $effective_date);
      break;
      
    case STATUS_SUSPENDED:
      $rv = $this->suspendAccount($user, $account, $reason, $effective_date);
      break;

    case STATUS_CANCELLED:
      $rv = $this->cancelAccount($user, $account, $reason, $effective_date);
      break;

    default:
      $rv = false;
      break;
    }

    return $rv;
  }

  function changeStatus($account, $status, $reason=null)
  {
    switch($status) {
    case STATUS_ACTIVE:
      $rv         = true;
      $sproc_name = 'MarkAccountActive';
      break;
      
    case STATUS_SUSPENDED:
      $rv         = true;
      $sproc_name = 'MarkAccountSuspended';
      break;

    case STATUS_CANCELLED:
      // FIXME since we cannot enable accounts, dont disable
      $rv         = true;
      $sproc_name = 'MarkAccountCancelled';
      break;

    default:
      $rv         = false;
      $sproc_name = null;
      break;
    }

    if($rv && $sproc_name)
      $this->sproc($sproc_name, Array('acctgrpid' => $account['Account']['acctgrpid']));

    return $rv;
  }

  function syncAccount($account, $update_only=false)
  {
    $rv = $this->Bt->saveAccount($account, $update_only);
    $this->error_msg = $this->Bt->output[2];   
    return $rv;
  }
  
  function getAccountAddress($account)
  {
    
    $accountAddress = Array('address1' => $account['Account']['baddr1'],
                            'address2' => $account['Account']['baddr2'],
                            'city' => $account['Account']['city'],
                            'state' => $account['Account']['state'],
                            'zip' => $account['Account']['zip'],
                            'country' => $account['Account']['country'],
                            'time_zone' => $account['Account']['time_zone']);
                            
    return $accountAddress;
  }
}
