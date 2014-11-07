<?

class AccountView extends AppModel
{
  var $name       = 'AccountView';
  var $useTable   = 'accountgroup_view';
  var $primaryKey = 'acctgrpid';

  var $hasOne = Array( 'DefaultBridge'        => Array( 'className'  => 'DefaultBridge', 
                                                        'foreignKey' => 'acctgrpid' ), 
		       
		       'Branding'             => Array( 'className'  => 'Branding', 
                                                        'foreignKey' => 'acctgrpid' ),
 
                       'AccountBillingMethod' =>  Array( 'className'  => 'AccountBillingMethod', 
                                                         'foreignKey' => 'acctgrpid' ));

  var $belongsTo = Array( 'Product' =>  Array( 'className'  => 'Product', 
                                               'foreignKey' => 'default_product_id' ));

  var $hasAndBelongsToMany = Array('BillingContact' => Array('className'             => 'Contact',
							     'joinTable'             => 'accountgroup_contacts',
							     'foreignKey'            => 'acctgrpid',
							     'associationForeignKey' => 'billing_contact_id',
							     'conditions'            => '',
							     'order'                 => '',
							     'limit'                 => '',
							     'unique'                => true,
							     'finderQuery'           => '',
							     'deleteQuery'           => '' ), 

				   'CorporateContact' => Array('className'             => 'Contact',
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

  // FIXME this should be on racctprefix
  function resellerSub($resellerid)
  {
    switch ($resellerid){
    case '1':
      return 'IC';
      break;
    case '2':
      return 'Usercentric';
      break;
    case '3':
      return 'at_conferencing';
      break;
    case '5':
      return 'Usercentric';
      break;
    case '40':
      return 'ccu';
      break;
    default:
      return 'IC';
      break;
    }
  }

  function get($acctgrpid, $user) 
  {
    $account = $this->read(null, $acctgrpid);

    if($account) {
      if(!is_null($user['Resellers'])) {
        if(!in_array($account['AccountView']['resellerid'], $user['Resellers']))
	      return null;
      }
      if($user['User']['level_type'] == SALESPERSON_LEVEL and 
        ($user['Salespersons'] == null or 
        !in_array($account['AccountView']['salespid'], $user['Salespersons']))){
          return null;
      }
    }
    return $account;
  }

}
