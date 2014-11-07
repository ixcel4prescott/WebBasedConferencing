<?
class CodrsController extends AppController
{
  var $uses       = Array('Codr', 'Account', 'Room');
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
          $criteria['Codr.resellerid'] = $user['Resellers'];
        } else if ($user['User']['ic_employee']){
          //IC reseller with no reseller listing show all
        } else {
          //hide everything
          $criteria['Codr.resellerid'] = '';
        }
        break;
      case 'salesperson':
        if(!is_null($user['Salespersons'])) {
          //if there are salespids to filter then put these in the filter
          $criteria['Account.salespid'] = $user['Salespersons'];
        } else {
          //restrict everything else
          $criteria['Codr.resellerid'] = '';
        }
        break;
      default:
        //restricted users 
        $criteria['Codr.resellerid'] = '';
        break;
    }
      
    if(!empty($_GET['acctgrpid'])){
      $criteria['Codr.acctgrpid'] = $_GET['acctgrpid'];
      $this->set('account', $_GET['acctgrpid']);
    } else {
      $this->set('account', '');
    }
    
    if(!empty($_GET['account'])){
      $criteria['Codr.acctgrpid'] = "LIKE {$_GET['account']}%";
      $this->set('account', $_GET['account']);
    }
    
    if(!empty($_GET['accountid'])){
      $criteria['Codr.accountid'] = $_GET['accountid'];
      $this->set('confirmation', $_GET['accountid']);
    } else {
      $this->set('confirmation', '');
    }
    if(!empty($_GET['confirmation'])){
      $criteria['Codr.accountid'] = "LIKE {$_GET['confirmation']}%";
      $this->set('confirmation', $_GET['confirmation']);
    }
    
    if(!empty($_GET['startdate'])&&!empty($_GET['enddate'])){
      $criteria['Codr.conference_start'] = 'BETWEEN '.$_GET['startdate']. ' 00:00:00 AND '.$_GET['enddate'].' 23:59:59';
      $this->set('startdate', $_GET['startdate']);
    } else if (!empty($_GET['startdate'])&&empty($_GET['enddate'])){
      $criteria['Codr.conference_start'] = '> '.$_GET['startdate']. ' 00:00:00';
      $this->set('startdate', $_GET['startdate']);
    } else if (empty($_GET['startdate'])&&!empty($_GET['enddate'])){
      $criteria['Codr.conference_start'] = '< '.$_GET['enddate'].' 23:59:59';
      $this->set('startdate', $_GET['startdate']);

    } else {
      $this->set('startdate', '');
      $this->set('enddate', '');
    }

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('sortBy', 'conference_start', 'direction', 'DESC'));
    if(!empty($_GET['export']))
      $limit = 5000;

    $data = $this->Codr->findAll($criteria, null, $order, $limit, $page );
    if(!empty($_GET['export'])) {
      if(!empty($_GET['codr_id'])||!empty($_GET['acctgrpid'])||!empty($_GET['accountid'])){
        $detailString = '';
        if (!empty($_GET['acctgrpid'])){
          $detailString = ' - '.$_GET['acctgrpid'];
        }
        if (!empty($_GET['accountid'])){
          $detailString = $detailString.' - '.$_GET['accountid'];
        }
        $filename = sprintf('CDR Report%s ', $detailString) . date('Y-m-d');
      } else {
        $filename = 'CODR Report ' . date('Y-m-d');
      }
      $keys     = Array('acctgrpid','accountid', 'conference_name', 'conference_start',
              'conference_end', 'billing_code', 'bridge_id');
      $headers  = Array('acctgrpid'        => 'Account',
              'accountid'        => 'Confirmation Number',
              'conference_name'  => 'Conference Name',
              'conference_start' => 'Conference Start',
              'conference_end'   => 'Conference End',
              'billing_code'     => 'Billing Code',
              'bridge_id'        => 'Bridge ID');
      export_csv($filename, $keys, $headers, pluck($data, 'Codr'));
      die;
    } else {
      $this->set('data', $data);
    }
  }
}
