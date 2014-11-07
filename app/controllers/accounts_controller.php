<?

class AccountsController extends AppController
{
  var $uses = Array( 'AccountView', 'Account', 'Request', 'Pci', 
    'ResellerGroup', 'SalespersonGroup', 'Reseller', 'Salesperson', 'Reports', 
    'Graphs', 'Status', 'InvoiceDeliveryMethod', 'InvoiceTerms', 'BillingMethod',
    'DialinNumber', 'DefaultBridge', 'Bridge', 'Room', 'RoomView', 'Note', 
    'RequestView', 'SicCode', 'CompanySize', 'Bridge', 'State', 'Contact', 
    'Branding', 'BillingFrequency', 'YearToDateByAccount', 
    'MonthToDateByAccount', 'YearToDateByAccount', 'AccountServiceRatesSummary',
    'CreditCard', 'TimeZone', 'Country', 'Product');

  var $components = Array('Pagination', 'RequestHandler');
  var $helpers = Array('Html', 'Pagination', 'Time');

  var $permissions = Array('reassign_salesperson' => GROUP_RESELLER_ADMIN_ONLY,
    'create' => GROUP_ALL,
    'edit' => GROUP_ALL,
    'invoice' => GROUP_ALL,
    'view' => GROUP_ALL,
    'status' => GROUP_ALL,
    'index' => GROUP_ALL);

  function create()
  {
    $this->pageTitle = 'Create an Account';

    $user = $this->Session->read('User');

    //Get the list of salespeople for the user
    $salespeople = Null;
    if($user['User']['level_type'] == 'reseller' || 
      $user['User']['level_type'] == 'admin' ||
      $user['User']['level_type'] == 'reseller_admin') {
        $reseller_ids = pluck($user['ResellerGroup']['Reseller'], 
          'resellerid');
      $salespeople = $this->Salesperson->generateList(
        Array('Salesperson.resellerid' => $reseller_ids),
          'name ASC', null, '{n}.Salesperson.salespid', '{n}.Salesperson.name'
      );
    } elseif($user['User']['level_type'] == 'salesperson') {
      $salesperson_ids = pluck($user['SalespersonGroup']['Salesperson'], 
        'salespid');
      $salespeople = $this->Salesperson->generateList(
        Array('Salesperson.salespid' => $salesperson_ids),
          'name ASC', null, '{n}.Salesperson.salespid', '{n}.Salesperson.name'
      );
    }
    $this->set('salespeople', $salespeople);

    $this->set('billing_methods',
         $this->BillingMethod->generateList(null, 'description ASC', null,
           '{n}.BillingMethod.id', '{n}.BillingMethod.description'));

    $this->set('billing_frequencies',
         $this->BillingFrequency->generateList(null, 'description ASC', null,
           '{n}.BillingFrequency.id', '{n}.BillingFrequency.description'));

    $this->set('us_states', $this->State->generateList("country = 'US'",
      'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
    $this->set('provinces', $this->State->generateList("country = 'CA'",
      'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
    $this->set('states', $this->State->generateList(null, 'name ASC', null,
      '{n}.State.abbrev', '{n}.State.name'));

    $country_order = "CASE WHEN (iso_alpha2 = 'US') THEN 0 WHEN (iso_alpha2 = 'CA') THEN 1 ELSE 99 END, name ASC";
    $this->set('countries', 
         $this->Country->generateList(null, $country_order, null, '{n}.Country.iso_alpha2', '{n}.Country.name'));

    $this->set('sic_codes',
             $this->SicCode->generateList(null, 'title ASC', null,
              '{n}.SicCode.code', '{n}.SicCode.title'));

    $this->set('company_size',
             $this->CompanySize->generateList(null, 'id ASC', null,
                '{n}.CompanySize.id', '{n}.CompanySize.range'));

    $this->set('invoice_delivery_methods',
             $this->InvoiceDeliveryMethod->generateList(null, 'invdelmeth ASC', null,
                    '{n}.InvoiceDeliveryMethod.invdelmeth', '{n}.InvoiceDeliveryMethod.description'));

    //FIXME: Horid hack to hide 1.5 finance rates Need to fix this eventually
    $exclude_1_5 = "id not in (4, 5)";
    $this->set('invoice_terms',
             $this->InvoiceTerms->generateList($exclude_1_5, 'description ASC', null,
                 '{n}.InvoiceTerms.id', '{n}.InvoiceTerms.description'));

    $bridges = $this->Bridge->generateList(Array('active'=>1, 'type'=>AUDIO_BRIDGE), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
    $this->set('bridges', $bridges);
    $this->set('time_zones', $this->TimeZone->generateList("bt_recognized= '1'", 'description ASC', null, '{n}.TimeZone.zone_name', '{n}.TimeZone.description'));
    
    $this->set('products', $this->Product->generateList(null, null, null, '{n}.Product.code', '{n}.Product.name'));

    $dialin_numbers   = a();
    $dialin_map       = a();
    $default_dialin   = null;
    $generated_id     = true;

    $this->set('default_bridge', empty($this->data) ? SPECTEL_BRIDGEID : null);

    if(!empty($this->data) && !empty($this->data['Account']['salespid'])) {
      $this->set('initial_ajax', true);

      // get reseller from selected salesperson to pull info for reseller
      $salesperson = $this->Salesperson->find(aa('salespid', $this->data['Account']['salespid']));

      foreach($this->DialinNumber->get($salesperson['Reseller']['resellerid'],
               $bridges[$this->data['Account']['default_bridge']]) as $i) {

  $dialin_numbers[$i[0]['id']] = $i[0]['description'];
  $dialin_map[$i[0]['id']]     = a($i[0]['tollfreeno'], $i[0]['tollno']);

  if($i[0]['default'])
    $default_dialin = $i[0]['id'];
      }

      $generated_id = $salesperson['Reseller']['agidgen'];
    }

    // defaults to sending emails on new accounts
    if(empty($this->data) || (!empty($this->data) && !isset($_POST['manual']))) {
      $this->data['Account']['invdelmeth']         = 1;
      $this->data['Branding']['webinterpoint_url'] = DEFAULT_WEBINTERPOINT_URL;
	  $this->data['Branding']['smartcloud_url'] = DEFAULT_SMARTCLOUD_URL;
	  $this->data['Account']['default_product_id'] = AUDIO_WEBINTERPOINT;
    }

    $this->set('dialin_numbers', $dialin_numbers);
    $this->set('dialin_map', $dialin_map);
    $this->set('default_dialin', $default_dialin);
    $this->set('generated_id', $generated_id);

    // The submit button was disabled and clicked to post
    if(!empty($this->data) && isset($_POST['manual'])) {

      if(!$generated_id)
        $this->Account->validate['acctgrpid'] = VALID_NOT_EMPTY;

      $this->Account->set($this->data);
      $valid_account_data = $this->Account->validates($this->data);

      $this->Branding->set($this->data);
      $valid_branding_data = $this->Branding->validates($this->data);

      if($valid_account_data && $valid_branding_data) {
        if($acctgrpid = $this->Account->createAccount($user, $salesperson, $this->data['Account'])) {
          $this->diffLog('Account', DIFFLOG_OP_CREATE, $acctgrpid, $this->data['Account']);
          if(!empty($this->data['Branding']['webinterpoint_url'])) {
            $this->data['Branding']['acctgrpid'] = $acctgrpid;
            $this->Branding->save($this->data);
          }
		  if(!empty($this->data['Branding']['smartcloud_url'])) {
            $this->data['Branding']['acctgrpid'] = $acctgrpid;
            $this->Branding->save($this->data);
          }
          $this->Session->setFlash(sprintf('Account %s created', $acctgrpid));
          $this->redirect('/pci/update/' . $acctgrpid);

        } else {
          $this->Session->setFlash('Account creation failed');
        }
      }
    }
  }

  function edit($acctgrpid=null)
  {
   
    $this->pageTitle = 'Update an Account';
	
    if($acctgrpid) {
      $user = $this->Session->read('User');
	  
      if($account = $this->Account->get($acctgrpid, $user)) {
        $this->set('account', $account);
        $this->Contact->unbindModel(Array(
          'hasAndBelongsToMany' => Array('Room'),
          'belongsTo' => Array('Account')));
        $account_contacts = $this->Contact->findAll(Array(
          'Contact.acctgrpid' => $account['Account']['acctgrpid']),
          null, 'last_name ASC, first_name ASC');
        $contacts = Array();
        foreach($account_contacts as $c)
          $contacts[$c['Contact']['id']] = $this->Contact->fullName($c);
        $this->set('contacts', $contacts);
		
        $reseller = $this->Reseller->find(aa(
          'resellerid', $account['Salesperson']['resellerid']));
  
        $this->set('us_states', $this->State->generateList("country = 'US'", 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
  $this->set('provinces', $this->State->generateList("country = 'CA'", 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
  $this->set('states',
       $this->State->generateList(null, 'name ASC', null,
                '{n}.State.abbrev', '{n}.State.name'));

  $country_order = "CASE WHEN (iso_alpha2 = 'US') THEN 0 WHEN (iso_alpha2 = 'CA') THEN 1 ELSE 99 END, name ASC";
  $this->set('countries', 
       $this->Country->generateList(null, $country_order, null, '{n}.Country.iso_alpha2', '{n}.Country.name'));

  $this->set('sic_codes',
       $this->SicCode->generateList(null, 'title ASC', null,
            '{n}.SicCode.code', '{n}.SicCode.title'));

  $this->set('company_size',
       $this->CompanySize->generateList(null, 'id ASC', null,
                '{n}.CompanySize.id', '{n}.CompanySize.range'));

        $this->set('billing_methods',
                   $this->BillingMethod->generateList(null, 'description ASC', null,
                                                      '{n}.BillingMethod.id', '{n}.BillingMethod.description'));

        $this->set('billing_frequencies',
                   $this->BillingFrequency->generateList(null, 'description ASC', null,
                                                         '{n}.BillingFrequency.id', '{n}.BillingFrequency.description'));

  $this->set('invoice_delivery_methods',
       $this->InvoiceDeliveryMethod->generateList(null, 'invdelmeth ASC', null,
                    '{n}.InvoiceDeliveryMethod.invdelmeth', '{n}.InvoiceDeliveryMethod.description'));

  //FIXME: Horid hack to hide 1.5 finance rates Need to fix this eventually
  $exclude_1_5 = "id not in (4, 5)";
  $this->set('invoice_terms',
       $this->InvoiceTerms->generateList($exclude_1_5, 'description ASC', null,
                '{n}.InvoiceTerms.id', '{n}.InvoiceTerms.description'));

  $bridges = $this->Bridge->generateList(Array('active'=>1, 'type'=>AUDIO_BRIDGE), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
  $this->set('bridges', $bridges);
  $this->set('time_zones', $this->TimeZone->generateList("bt_recognized= '1'", 'description ASC', null, '{n}.TimeZone.zone_name', '{n}.TimeZone.description'));
  $this->set('products', $this->Product->generateList(null, null, null, '{n}.Product.code', '{n}.Product.name'));

        $this->set('has_pci', $this->Pci->hasActivePci($acctgrpid));

  if(!empty($this->data['Account']['default_bridge'])) {
    $default_bridge = $this->data['Account']['default_bridge'];
  } else {
    $default_bridge = $this->DefaultBridge->read(null, $acctgrpid);

    if($default_bridge) {
      $default_bridge = $default_bridge['DefaultBridge']['bridge_id'];
    } else {
      // guessing based on the first created room
      $first_room = $this->Room->find(Array('roomstat' => 0), null, 'roomstatdate ASC');
      $default_bridge = $first_room ? $first_room['Room']['bridgeid'] : SPECTEL_BRIDGEID;
    }
  }

  $this->set('default_bridge', $default_bridge);

  $dialin_numbers = a();
  $dialin_map     = a();
  $default_dialin = null;

  foreach($this->DialinNumber->get($reseller['Reseller']['resellerid'], $bridges[$default_bridge]) as $i) {
    $dialin_numbers[$i[0]['id']] = $i[0]['description'];
    $dialin_map[$i[0]['id']] = a($i[0]['tollfreeno'], $i[0]['tollno']);

    if($i[0]['default'])
      $default_dialin = $i[0]['id'];
  }

  // check for misaligned dialin number
  $this->set('dialin_number_error', false);

  // when building dialin_numbers above, honor default dialin number UNLESS
  //  - there was another specified from post
  /// - there is a valid account default for the currently selected bridge
  if(!empty($this->data['Account']['dialinNoid']) && isset($dialin_numbers[$this->data['Account']['dialinNoid']])) {
    $default_dialin = $this->data['Account']['dialinNoid'];
  } elseif($account['Account']['dialinNoid'] && isset($dialin_numbers[$account['Account']['dialinNoid']])) {
    $default_dialin = $account['Account']['dialinNoid'];
  } elseif($account['Account']['dialinNoid']) {
    $this->systemLog('dialin number alignment error', $acctgrpid);
    $this->set('dialin_number_error', true);
  }

  $this->set('dialin_numbers', $dialin_numbers);
  $this->set('dialin_map',     $dialin_map);
  $this->set('default_dialin', $default_dialin);

  if(!empty($this->data) && isset($_POST['manual'])) {
    
    $this->Account->set($this->data);
    $valid_account_data = $this->Account->validates($this->data);

    $this->Branding->set($this->data);
    $valid_branding_data = $this->Branding->validates($this->data);

    if($valid_account_data && $valid_branding_data) {
            $billing_contact_id = $this->data['Account']['bcontact_id'];
            $corp_contact_id    = $this->data['Account']['corpcontact_id'];

            unset($this->data['Account']['bcontact_id']);
            unset($this->data['Account']['corpcontact_id']);

      if(!$this->Account->updateAccount($user, $account, $this->data['Account'])) {

        $this->diffLog('Account', DIFFLOG_OP_UPDATE, $acctgrpid, $this->data['Account'], $account['Account']);
        $this->Session->setFlash('Account updated');

              $this->Account->updateContacts($account, $billing_contact_id, $corp_contact_id);

        if(!empty($this->data['Branding']['webinterpoint_url'])) {
    $this->data['Branding']['acctgrpid'] = $acctgrpid;
    $this->Branding->save($this->data);
        }
		if(!empty($this->data['Branding']['smartcloud_url'])) {
    $this->data['Branding']['acctgrpid'] = $acctgrpid;
    $this->Branding->save($this->data);
        }
		
        $this->redirect('/accounts/view/' . $acctgrpid);
      } else {
        $this->Session->setFlash('Account update failed');
      }
    }

  } elseif(empty($this->data)) {
    $account['Account']['creditcard'] = '';
    $account['Account']['cvv2'] = '';

    $this->data = $account;
    $this->data['DefaultBridge']['bridge_id'] = $default_bridge;

          if(!empty($account['BillingContact']))
            $this->data['Account']['bcontact_id'] = $account['BillingContact'][0]['id'];

          if(!empty($account['CorporateContact']))
            $this->data['Account']['corpcontact_id'] = $account['CorporateContact'][0]['id'];

    if(empty($account['Branding']['webinterpoint_url']))
      $this->data['Branding']['webinterpoint_url'] = DEFAULT_WEBINTERPOINT_URL;
	if(empty($account['Branding']['smartcloud_url']))
      $this->data['Branding']['smartcloud_url'] = DEFAULT_SMARTCLOUD_URL;
  } 
    } else {
  $this->Session->setFlash('Account not found');
  $this->redirect('/accounts');
      }
    } else {
      $this->redirect('/accounts');
    }
  }
 
  function reassign_salesperson($acctgrpid=null){
    $this->pageTitle = 'Salesperson Reassignment';
	 
	if($acctgrpid) {
      $user = $this->Session->read('User');

      if($account = $this->Account->get($acctgrpid, $user)) {
        $this->set('account', $account);
		
        $reseller = $this->Reseller->find(aa('resellerid', $account['Salesperson']['resellerid']));
        $this->set('reseller', $reseller);
		 
        $salespeople = map_pluck($reseller['Salesperson'], 'salespid', 'name');
        $this->set('salespeople', $salespeople);
		  
        if(!empty($this->data) && isset($_POST['manual'])) {
        // Salesperson reassignment by non-reseller user goes to request
          if(!empty($this->data['Account']['salespid']) && $account['Account']['salespid'] != $this->data['Account']['salespid']) {
            $req_data = aa('from', $account['Account']['salespid'], 'to', $this->data['Account']['salespid']);
            $this->Request->saveRequest(REQTYPE_ACCOUNT_REASSIGN, $user['User']['id'], $acctgrpid, null, $req_data, REQSTATUS_APPROVED);
            $this->Session->setFlash('Salesperson Updated.');		   
            $this->redirect('/accounts/view/' . $acctgrpid);
          } else { $this->Session->setFlash('Please select a different salesperson.');}
		 
        }
      } else {
        $this->Session->setFlash('Account not found');
        $this->redirect('/accounts');
      }
  } else {
    $this->redirect('/accounts');
  }
}
 
  function index()
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);
    
    //Limit the accounts of the user has aligned resellers
    $criteria = Array();
    if(!is_null($user['Resellers']))
      $criteria['AccountView.resellerid'] = $user['Resellers'];

    if($is_ajax = $this->RequestHandler->isAjax() || !empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
    }
    $this->set('is_ajax', $is_ajax);

    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $fquery = implode('%', preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY));

      $criteria['OR'] = Array( 'AccountView.acctgrpid'                  => "LIKE {$fquery}%",
             'AccountView.company'                    => "LIKE {$fquery}%",
             'AccountView.salesperson_accountmanager' => "LIKE {$fquery}%",
             'AccountView.dialinNo_description'       => "LIKE {$fquery}%",
             'AccountView.dialinNo_tollfreeno'        => "LIKE {$fquery}%",
             'AccountView.dialinNo_tollno'            => "LIKE {$fquery}%",
             'AccountView.default_bridge'             => "LIKE {$fquery}%" );
    } else {
      $query = '';
    }
    $this->set('query', $query);

    $this->set('salespeople', $this->Salesperson->salespersonList($user['Resellers']));
    if(!empty($_GET['salesperson'])) {
      $salesperson                      = $_GET['salesperson'];
      $criteria['AccountView.salespid'] = $this->Salesperson->byAccountManager($_GET['salesperson']);
    } else {
      $salesperson = null;
    }
    $this->set('salesperson', $salesperson);

    $active_only = isset($_GET['active_only']) ? $_GET['active_only'] : 1;
    $this->set('active_only', $active_only);

    if($active_only)
      $criteria['AccountView.acctstat'] = 0;

    if($user['User']['level_type'] == SALESPERSON_LEVEL && empty($_GET['embed'])){
      //Only export the accounts that a salesperson owns. We don't want them seeing the
      //seeing the contact info of the accounts that they don't own.
      $criteria['AccountView.salespid'] = $user['Salespersons'];
    } 

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, Array('sortBy' => 'acctgrpid'));

    if(!empty($_GET['export']))
      list($limit, $page) = Array(null, null);

    $this->AccountView->unbindModel(Array('hasAndBelongsToMany' => Array('BillingContact', 'CorporateContact', 'BillingMethod', 'BillingFrequency'),
        'hasOne'              => Array('DefaultBridge')));

    $data = $this->AccountView->findAll($criteria, NULL, $order, $limit, $page);

    if(!empty($_GET['export'])) {
      $filename = 'Accounts ' . date('Y-m-d');

      //The "additional" arrays are so that the more sensitive pieces of
      //data don't get exported when a salesperson exports the data. 
      $customer_keys = Array('acctgrpid', 'reseller_name', 'company', 
        'addr1', 'addr2', 'city', 'state', 'zip');
      $sensitive_keys = Array('contact', 'phone', 'fax', 'email');
      $product_keys = Array('acctstatus', 'salesperson_accountmanager', 
          'dialinNo_description', 'dialinNo_tollfreeno', 'dialinNo_tollno', 
          'default_bridge');

      $customer_headers  = Array('acctgrpid' => 'Account Number',
        'reseller_name' => 'Reseller',
        'company' => 'Company',
        'addr1' => 'Address 1',
        'addr2' => 'Address 2',
        'city' => 'City',
        'state' => 'State',
        'zip' => 'ZIP');
      $sensitive_headers = Array('contact' => 'Contact',
        'phone' => 'Phone',
        'fax' => 'Fax',
        'email' => 'Email');
      $product_headers = Array('acctstatus' => 'Account Status',
        'salesperson_accountmanager' => 'Account Manager',
        'dialinNo_description' => 'Dialin Number',
        'dialinNo_tollfreeno' => 'Toll-Free Number',
        'dialinNo_tollno' => 'Toll Number',
        'default_bridge' => 'Bridge' );

      if($user['User']['level_type'] == SALESPERSON_LEVEL){
        $keys = array_merge($customer_keys, $product_keys);
        $headers = array_merge($customer_headers, $product_headers);
      } else {
        $keys = array_merge($customer_keys, $sensitive_keys, $product_keys);
        $headers = array_merge($customer_headers, $sensitive_headers, 
          $product_headers);
      }

      export_csv($filename, $keys, $headers, pluck($data, 'AccountView'));

      die;
    } else {
      $this->set('data', $data);
    }
  }

  function invoice($acctgrpid)
  {
    $year = $_GET['invoiceYear'];
    $month = (strlen($_GET['invoiceMonth']) > 1) ? $_GET['invoiceMonth'] : '0'.$_GET['invoiceMonth'];
    $filename = $acctgrpid."-".$year."-".$month.".pdf";

    $user = $this->Session->read('User');

    $this->Account->unbindModel(Array('hasMany' => Array('Room')));
    if($account = $this->Account->get($acctgrpid, $user)) {
      $reseller = $this->Reseller->find(
        Array('resellerid' => $account['Salesperson']['resellerid']));
      $invoice_path = sprintf('//65.91.73.164/Invoices/%s/%s/%s/%s', 
        $reseller['Reseller']['racctprefix'], $year, $month, $filename);
      if(file_exists($invoice_path)) {
        Configure::write('debug', 0);
        $this->layout = 'ajax';

        // send the right headers
        header("Content-Type: application/pdf");
        header("Content-Length: " . filesize($invoice_path));
        header("Cache-Control:  maxage=1");
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=$acctgrpid-$year-$month.pdf");

        $fp = fopen($invoice_path, 'rb');

        // dump the picture and stop the script
        fpassthru($fp);
	    die;

      } else {
        $this->Session->setFlash(sprintf('Invoice %s not found', $filename));
        $this->redirect('/accounts/view/' . $acctgrpid);
      }
    } else {
      $this->Session->setFlash('Account not found');
      $this->redirect('/accounts');
    }
  }

  function status($acctgrpid=null)
  {
    if($acctgrpid) {
      $user = $this->Session->read('User');
      $this->set('account_statuses', $this->Status->generateList(null,
        'description ASC', null, '{n}.Status.acctstat', 
        '{n}.Status.description'));
      $this->Account->recursive=0;
      if($account = $this->Account->get($acctgrpid, $user)) {
        $this->set('account', $account);

        if(!empty($this->data)) {
          $valid_effective_date = preg_match(VALID_DATE, 
            $this->data['Account']['effective_date']);

          if($account['Account']['acctstat'] != $this->data['Account']['status'] &&
            !empty($this->data['Account']['reason']) &&
            $valid_effective_date) {
              $effective_date = $this->data['Account']['effective_date'] . ' 00:00:00';

              $rv = $this->Account->updateStatus($user,
                $account,
                $this->data['Account']['status'],
                $this->data['Account']['reason'],
                $effective_date);
              if(!$rv) {
                $this->Session->setFlash('Your request has been submitted');
                $this->redirect('/accounts/view/' . $acctgrpid);
              } else {
                $this->Session->setFlash('Could not update account status');
              }
            } else {
              if($account['Account']['acctstat'] == $this->data['Account']['status'])
                $this->Account->invalidate('status');
              if(empty($this->data['Account']['reason']))
                $this->Account->invalidate('reason');
              if(!$valid_effective_date)
                $this->Account->invalidate('effective_date');
            }
        } else {
          $this->data = $account;
        }
      } else {
        $this->Session->setFlash('Account not found');
        $this->redirect('/accounts');
      }
    } else {
      $this->redirect('/accounts');
    }
  }

  function view($acctgrpid=null, $year=null, $month=null, $day=null)
  {
    $this->pageTitle = 'Account Profile';
    $this->set('account_s', $acctgrpid);

    $user = $this->Session->read('User');
    $this->set('user', $user);

    $account_criteria  = Array('AccountView.acctgrpid' => $acctgrpid);
    if(!is_null($user['Resellers']))
      $account_criteria['AccountView.resellerid'] = $user['Resellers'];

    if($account = $this->AccountView->find($account_criteria)) {
      $this->set('account', $account);

      $this->addHistory(sprintf('Account %s - %s', $account['AccountView']['acctgrpid'], $account['AccountView']['company']));

      $this->set('owned_by_other',
      $user['User']['level_type'] == 'salesperson' && !in_array($account['AccountView']['salespid'], $user['Salespersons']));

      $year       = !$year ? date('Y') : mssql_escape($year);
      $short_year = substr($year, strlen($year)-2, 2);
      $month      = !$month ? date('m') : mssql_escape($month);
      $day        = !$day ? date('d')-1 : mssql_escape($day);

      $this->set('year', $year);
      $this->set('month', $month);

      // Build base criteria for queries
      $ytd_criteria = Array('DATE' => "BETWEEN 01/01/{$year} 00:00:00 AND 12/31/{$year} 23:59:59", 'acctgrpid' => $acctgrpid);
      $focusMonth = month_boundaries($month, $year);
      $mtd_criteria = Array('DATE' => "BETWEEN {$focusMonth['start_of_month']} AND {$focusMonth['end_of_month']}", 'acctgrpid' => $acctgrpid);

        $this->set('ytd_dates', $this->YearToDateByAccount->findAll($ytd_criteria, null, 'DATE ASC'));
        $mtd_data = $this->MonthToDateByAccount->findAll($mtd_criteria, null, 'DATE ASC');
        $this->set('mtd_dates', $mtd_data);
        
      $this->Contact->unbindModel(Array('hasAndBelongsToMany' => Array('Room'), 'belongsTo' => Array('Account')));

      $this->set('service_rates_summary', 
                 $this->AccountServiceRatesSummary->findAll(Array('acctgrpid' => $acctgrpid), null, 'ServiceType.description'));

      $this->set('contacts', $this->Contact->findAll(Array('acctgrpid' => $acctgrpid), null, 'last_name, first_name'));
      $this->set('requests', $this->RequestView->findAll(Array('acctgrpid' => $acctgrpid), null, 'created DESC', 20));
      $this->set('bridges', Symbols::$bridges);
      $reseller =  $this->Reseller->find(Array('resellerid' => $account['AccountView']['resellerid']));
      //$this->set('invoices', $this->AccountView->invoiceList($acctgrpid, $reseller));
      //$this->set('chart', $this->Graphs->BuildChartData_MonthlyAccount($this->Graphs->Usage_MonthlyAccount($year, $month ,$acctgrpid)));
      $this->set('chart', $this->Graphs->BuildChartData_MonthlyAccount($mtd_data));
      $this->set('credit_card', $this->CreditCard->find(Array('acctgrpid' => $acctgrpid, 'active' => 1), null, 'created_on DESC'));
      $this->set('notes', $this->Note->get('Account', $acctgrpid));
      $this->set('time_zone', $this->TimeZone->find(Array('zone_name' => $account['AccountView']['time_zone']), 'description', null, null));
      $this->set('country', $this->Country->find(Array('iso_alpha2' => $account['AccountView']['country']), 'name', null, null));
    } else {
      $this->Session->setFlash('Account not found');
      $this->redirect('/accounts');
    }
  }
} 
