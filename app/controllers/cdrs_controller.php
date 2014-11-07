<?
class CdrsController extends AppController
{
  var $uses       = Array('Cdr', 'Account', 'Room', 'ServiceType', 'CountryCode');
  var $components = Array('Pagination');
  var $helpers    = Array('Html', 'Pagination');

  function index($resellerId=null, $codrID=null)
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);

    $criteria = array();
    
    switch ($user['User']['level_type']) {
      case 'reseller':
      case 'admin':
        if(!is_null($user['Resellers'])) {
          //if there are reseller ids to filter then put these in the filter
          $criteria['Cdr.resellerid'] = $user['Resellers'];
        } else if ($user['User']['ic_employee']){
          //IC reseller with no reseller listing show all
        } else {
          //hide everything
          $criteria['Cdr.resellerid'] = '';
        }
        break;
      case 'salesperson':
        if(!is_null($user['Salespersons'])) {
          //if there are salespids to filter then put these in the filter
          $criteria['Account.salespid'] = $user['Salespersons'];
        } else {
          //restrict everything else
          $criteria['Cdr.resellerid'] = '';
        }
        break;
      default:
        //restricted users 
        $criteria['Codr.resellerid'] = '';
        break;
    }
    
    if(!empty($_GET['codr_id'])){
      $criteria['Cdr.codr_id'] = $_GET['codr_id'];
      $this->set('codr_id', $_GET['codr_id']);
    } else {
      $this->set('codr_id', '');
    }
    if(!empty($_GET['acctgrpid'])){
      $criteria['Cdr.acctgrpid'] = $_GET['acctgrpid'];
      $this->set('account', $_GET['acctgrpid']);
    } else {
      $this->set('account', '');
    }
    if(!empty($_GET['account'])){
      $criteria['Cdr.acctgrpid'] = "LIKE {$_GET['account']}%";
      $this->set('account', $_GET['account']);
    }
    if(!empty($_GET['accountid'])){
      $criteria['Cdr.accountid'] = $_GET['accountid'];
      $this->set('confirmation', $_GET['accountid']);
    } else {
      $this->set('confirmation', '');
    }
    if(!empty($_GET['confirmation'])){
      $criteria['Cdr.accountid'] = "LIKE {$_GET['confirmation']}%";
      $this->set('confirmation', $_GET['confirmation']);
    }
    
    if(!empty($_GET['startdate'])&&!empty($_GET['enddate'])){
      $criteria['Cdr.call_start'] = 'BETWEEN '.$_GET['startdate']. ' 00:00:00 AND '.$_GET['enddate'].' 23:59:59';
      $this->set('startdate', $_GET['startdate']);
    } else if (!empty($_GET['startdate'])&&empty($_GET['enddate'])){
      $criteria['Cdr.call_start'] = '> '.$_GET['startdate']. ' 00:00:00';
      $this->set('startdate', $_GET['startdate']);
    } else if (empty($_GET['startdate'])&&!empty($_GET['enddate'])){
      $criteria['Cdr.call_start'] = '< '.$_GET['enddate'].' 23:59:59';
      $this->set('startdate', $_GET['startdate']);

    } else {
      $this->set('startdate', '');
      $this->set('enddate', '');
    }

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'call_start', 'direction', 'DESC'));
    if(!empty($_GET['export']))
      $limit = 5000;
      
    $data = $this->Cdr->findAll($criteria, null, $order, $limit, $page );
    if(!empty($_GET['export'])) {
      if(!empty($_GET['codr_id'])||!empty($_GET['acctgrpid'])||!empty($_GET['accountid'])){
        $detailString = '';
        if (!empty($_GET['codr_id'])){
          $detailString = ' - '.$_GET['codr_id'];
        }
        if (!empty($_GET['acctgrpid'])){
          $detailString = $detailString.' - '.$_GET['acctgrpid'];
        }
        if (!empty($_GET['accountid'])){
          $detailString = $detailString.' - '.$_GET['accountid'];
        }
        $filename = sprintf('CDR Report%s ', $detailString) . date('Y-m-d');
      } else {
        $filename = 'CDR Report ' . date('Y-m-d');
      }
      $keys     = Array('acctgrpid', 'accountid', 'billing_code', 'call_start', 'call_end', 'minutes', 'dnis_country_code',
                    'dnis_city_number', 'ani_country_code', 'ani_city_number', 'call_type', 'ppm',
                    'call_cost');
      $headers  = Array('acctgrpid'         => 'Account',
              'accountid'         => 'Confirmation Number',
              'billing_code'      => 'Billing Code',
              'call_start'        => 'Call Start',
              'call_end'          => 'Call End',
              'minutes'           => 'Minutes',
              'dnis_country_code' => 'DNIS Country Code',
              'dnis_city_number'  => 'DNIS City Number',
              'ani_country_code'  => 'ANI Country Code',
              'ani_city_number'   => 'ANI City Code',
              'call_type'         => 'Call Type',
              'ppm'               => 'PPM',
              'call_cost'         => 'Call Cost');

      export_csv($filename, $keys, $headers, pluck($data, 'Cdr'));
      die;
    } else {
      $this->set('data', $data);
      $this->set('serviceTypes', $this->ServiceType->generateList(null, null, null,'{n}.ServiceType.code', '{n}.ServiceType.description'));
    }
  }
  function view($cdrID=null)
  {
    $user = $this->Session->read('User');
    $criteria = $criteria = array('id' => $cdrID);
    $data = $this->Cdr->find($criteria);
    if ($data['Cdr']['call_type'] != 'WEBI'){
      $this->Cdr->bindModel(array('belongsTo' => array('DNISCountryCode' => array('className'    => 'CountryCode',
                                                                                  'foreignKey'   => 'dnis_country_code',
                                                                                  'conditions'    => 'DNISCountryCode.npa ='.substr($data['Cdr']['dnis_city_number'],0,3)
                                          ))));
      $this->Cdr->bindModel(array('belongsTo' => array('ANICountryCode' => array('className'    => 'CountryCode',
                                                                                 'foreignKey'   => 'ani_country_code',
                                                                                 'conditions'    => 'ANICountryCode.npa ='.substr($data['Cdr']['ani_city_number'],0,3)
                                          ))));
    }
    if($user['User']['level_type'] == SALESPERSON_LEVEL
        && !in_array($data['Account']['salespid'], $user['Salespersons'])){
      $this->Session->setFlash('CDR not found');
      $this->redirect('/cdrs');
    }

    $data = $this->Cdr->find($criteria);
    
    $this->set('data', $data);
    $this->set('serviceTypes', $this->ServiceType->generateList(null, null, null,'{n}.ServiceType.code', '{n}.ServiceType.description'));
  }
}
