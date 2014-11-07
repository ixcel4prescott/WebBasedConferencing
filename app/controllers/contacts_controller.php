<?

class ContactsController extends AppController
{
  var $uses       = Array('ContactView', 'Contact', 'Account', 'State', 'Country', 'Bridge', 'Status', 'TimeZone');
  var $components = Array('Pagination', 'RequestHandler');
  var $helpers    = Array('Html', 'Pagination', 'Time', 'Text');

  var $permissions = GROUP_ALL;

  function create($acctgrpid=null)
  {
    $this->pageTitle = 'Create a Contact';

    if($acctgrpid) {
      $user = $this->Session->read('User');
      if($account = $this->Account->get($acctgrpid, $user)) {
        $this->set('account', $account);
        $this->set('accountAddress', $this->Account->getAccountAddress($account));
        // force us/ca to top
        $country_order = "CASE WHEN (iso_alpha2 = 'US') THEN 0 WHEN (iso_alpha2 = 'CA') THEN 1 ELSE 99 END, name ASC";
        $this->set('titles', Symbols::$titles);
        $this->set('states', $this->State->generateList(null, 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
        $this->set('us_states', $this->State->generateList("country = 'US'", 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
        $this->set('provinces', $this->State->generateList("country = 'CA'", 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
        $this->set('countries', $this->Country->generateList(null, $country_order, null, '{n}.Country.iso_alpha2', '{n}.Country.name'));
        $this->set('domains', $this->Contact->getDomains($acctgrpid));
        $this->set('time_zones', $this->TimeZone->generateList("bt_recognized= '1'", 'description ASC', null, '{n}.TimeZone.zone_name', '{n}.TimeZone.description'));

        if(!empty($this->data)) {
          $this->Contact->set($this->data);
          if($this->Contact->validates($this->data)) {
            //we are no longer copying from the account at the stored procedure level.
            $this->data['Contact']['copy_from_account'] = 0;
            if($contact_id = $this->Contact->createContact($user, $account, $this->data['Contact'])) {
              $this->diffLog('Contact', DIFFLOG_OP_CREATE, $contact_id, $this->data['Contact']);
              $this->Session->setFlash('Contact created');
              $this->redirect('/contacts/view/' . $contact_id);
            } else {
              $this->Session->setFlash('Contact creation failed');
            }
          }
        } else {
          $this->data['Contact']['phone']   = $account['Account']['phone'];
          $this->data['Contact']['company'] = $account['Account']['bcompany'];
        }
      } else {
        $this->Session->setFlash('Account not found');
        $this->redirect('/contacts');
      }
    } else {
      $this->Session->setFlash('No account specified');
      $this->redirect('/contacts');
    }
  }

  function edit($contact_id=null)
  {
    $this->pageTitle = 'Edit Contact';

    if($contact_id) {
      $user = $this->Session->read('User');

      if($contact = $this->Contact->get($contact_id, $user)) {
        $this->set('contact', $contact);

        $full_name = $this->Contact->fullName($contact);
        $this->pageTitle .= ' : ' . $full_name;
        $this->set('full_name', $full_name);

        // force us/ca to top
        $country_order = "CASE WHEN (iso_alpha2 = 'US') THEN 0 WHEN (iso_alpha2 = 'CA') THEN 1 ELSE 99 END, name ASC";

  $this->set('titles', Symbols::$titles);
  $this->set('states', $this->State->generateList(null, 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
  $this->set('us_states', $this->State->generateList("country = 'US'", 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
  $this->set('provinces', $this->State->generateList("country = 'CA'", 'name ASC', null, '{n}.State.abbrev', '{n}.State.name'));
  $this->set('countries', $this->Country->generateList(null, $country_order, null, '{n}.Country.iso_alpha2', '{n}.Country.name'));
  $this->set('domains', $this->Contact->getDomains($contact['Contact']['acctgrpid']));
  $this->set('time_zones', $this->TimeZone->generateList("bt_recognized= '1'", 'description ASC', null, '{n}.TimeZone.zone_name', '{n}.TimeZone.description'));

        if(!empty($this->data)) {
          $this->Contact->set($this->data);
          if($this->Contact->validates($this->data)) {

            if(!$this->Contact->updateContact($user, $contact, $this->data['Contact'])) {
              $this->diffLog('Contact', DIFFLOG_OP_UPDATE, $contact_id, $this->data['Contact']);
              $this->Session->setFlash('Contact updated');
              $this->redirect('/contacts/view/' . $contact_id);
            } else {
              $this->Session->setFlash('Contact update failed');
            }
          }
        } else {
          $this->data = $contact;
        }

      } else {
        $this->Session->setFlash('Contact not found');
        $this->redirect('/contacts');
      }
    } else {
      $this->Session->setFlash('No contact specified');
      $this->redirect('/contacts');
    }
  }

  function index($acctgrpid=null)
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);

    $criteria = Array();
    if(!is_null($user['Resellers']))
      $criteria['ContactView.resellerid'] = $user['Resellers'];

    $account = null;
    $this->Account->recursive=0;

    if($acctgrpid && ($account = $this->Account->get($acctgrpid, $user))) {
      $criteria['ContactView.acctgrpid'] = $acctgrpid;
    }
    $this->set('account', $account);

    if($is_ajax = $this->RequestHandler->isAjax() || !empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
      $criteria['ContactView.status'] = Array(0,1);
    }
    $this->set('is_ajax', $is_ajax);

    if(!empty($_GET['query'])) {
      $query = $_GET['query'];
      $fquery = implode('%', preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY));

      $criteria['OR'] = Array( 'ContactView.first_name + \' \' + ContactView.last_name' => "LIKE {$fquery}%",
                               'ContactView.phone'                                      => "LIKE {$fquery}%",
                               'ContactView.city'                                       => "LIKE {$fquery}%",
                               'ContactView.email'                                      => "LIKE {$fquery}%" );

    } else {
      $query = '';
    }
    $this->set('query', $query);

    if($user['User']['level_type'] == SALESPERSON_LEVEL){
      //Only show the contacts that a salesperson owns. We don't want them 
      //seeing the contact info of the accounts that they don't own.
      $criteria['ContactView.salespid'] = $user['Salespersons'];
    }

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, Array('sortBy' => 'last_name'));

    if(!empty($_GET['export']))
      list($limit, $page) = Array(null, null);

    $data = $this->ContactView->findAll($criteria, NULL, $order, $limit, $page);

    if(!empty($_GET['export']) && 
      $user['User']['level_type'] != SALESPERSON_LEVEL) {
      $filename = sprintf('%s - Contacts %s', $acctgrpid, date('Y-m-d'));

      $keys     = Array('id', 'title', 'first_name', 'middle_name', 'last_name', 'position',
                        'email', 'phone', 'city', 'state', 'zip', 'country');

      $headers  = Array( 'id'        => 'Contact ID',
                         'title'       => 'Title',
                         'first_name'  => 'First Name',
                         'middle_name' => 'Middle Name',
                         'last_name'   => 'Last Name',
                         'position'    => 'Position',
                         'email'       => 'Email',
                         'phone'       => 'Phone Number',
                         'city'        => 'City',
                         'state'       => 'State',
                         'zip'         => 'Postal Code',
                         'country'     => 'Country' );

      export_csv($filename, $keys, $headers, pluck($data, 'ContactView'));

      die; 
    } elseif(!empty($_GET['export']) &&
      $user['User']['level_type'] == SALESPERSON_LEVEL) {
      //Sales shouldn't be able to export this data
      $this->Session->setFlash('Not authorized');
      $this->redirect('/contacts');
    } else {
      $this->set('data', $data);
    }
  }

  function status($contact_id=null)
  {
    $this->pageTitle = 'Change Contact Status';

    if($contact_id) {
      $user = $this->Session->read('User');

      if($contact = $this->Contact->get($contact_id, $user)) {
        $this->set('contact', $contact);

        $full_name = $this->Contact->fullName($contact);
        $this->pageTitle .= ' : ' . $full_name;
        $this->set('full_name', $full_name);

        $this->set('statuses',  $this->Status->generateList(null, 'description ASC', null,
                                                            '{n}.Status.acctstat', '{n}.Status.description'));

        if(!empty($this->data)) {
          $valid_effective_date = preg_match(VALID_DATE, $this->data['Contact']['effective_date']);

          if($this->data['Contact']['status'] != $contact['Contact']['status'] &&
             !empty($this->data['Contact']['reason']) &&
             $valid_effective_date ) {

            $effective_date = $this->data['Contact']['effective_date'] . ' 00:00:00';

            $rv = $this->Contact->updateStatus($user,
                                               $contact,
                                               $this->data['Contact']['status'],
                                               $this->data['Contact']['reason'],
                                               $effective_date);

            if(!$rv) {
              $this->Session->setFlash('Your request has been submitted');
              $this->redirect('/contacts/index/' . $contact['Contact']['acctgrpid']);
            } else {
              $this->Session->setFlash('Could not update contact status');
            }

          } else {
            if(empty($this->data['Contact']['reason']))
              $this->Contact->invalidate('reason');

            if($this->data['Contact']['status'] == $contact['Contact']['status'])
              $this->Contact->invalidate('status');

            if(!$valid_effective_date)
              $this->Contact->invalidate('effective_date');
          }
        }

      } else {
        $this->Session->setFlash('Contact not found');
        $this->redirect('/contacts');
      }
    } else {
      $this->Session->setFlash('No contact specified');
      $this->redirect('/contacts');
    }
  }

  function view($contact_id=null)
  {
    $this->pageTitle = 'Contact';

    if($contact_id) {
      $user = $this->Session->read('User');

      if($contact = $this->Contact->get($contact_id, $user)) {
        $this->set('contact', $contact);

        $full_name = $this->Contact->fullName($contact);

        $this->addHistory(sprintf('Contact %d - %s', $contact['Contact']['id'], $full_name));

        $this->pageTitle .= ' : ' . $full_name;
        $this->set('full_name', $full_name);

        $this->set('state', $this->State->findByAbbrev($contact['Contact']['state']));
        $this->set('country', $this->Country->findByIsoAlpha2($contact['Contact']['country']));
        $this->set('time_zone', $this->TimeZone->find(Array('zone_name' => $contact['Contact']['time_zone']), 'description', null, null));
        $this->set('bridges', $this->Bridge->generateList(null, null, null, '{n}.Bridge.id', '{n}.Bridge.name'));
        $this->set('statuses',  $this->Status->generateList(null, 'description ASC', null,
                                                            '{n}.Status.acctstat', '{n}.Status.description'));
      } else {
        $this->Session->setFlash('Contact not found');
        $this->redirect('/contacts');
      }
    } else {
      $this->Session->setFlash('No contact specified');
      $this->redirect('/contacts');
    }
  }
}
