<?

class GraphsController extends AppController
{
  var $uses        = Array('Reports', 'Graphs', 'YearToDate', 'YearToDateBySalesperson',
                           'YearToDateByReseller', 'MonthToDate', 'MonthToDateBySalesperson',
                           'MonthToDateByReseller', 'MonthToDateByAccount', 'Account');
  var $layout      = 'ajax';
  var $permissions = GROUP_ALL;

  private function buildUserLists()
  {
    $user = $this->Session->read('User');

    $reseller_ids = null;

    if(isset($user['ResellerGroup']['Reseller']))
      foreach($user['ResellerGroup']['Reseller'] as $i)
	$reseller_ids[] = $i['resellerid'];

    $salespids = null;

    if(isset($user['SalespersonGroup']['Salesperson']))
      foreach($user['SalespersonGroup']['Salesperson'] as $i)
	$salespids[] = $i['salespid'];
    
    return a($user, $reseller_ids, $salespids);
  }

  function usage($year=null, $month=null, $day=null)
  {
    if(!$year)
      $year = date('Y');

    //Commenting out since the conferencelist isn't used anymore
    //if ($account){
    //  $this->setAction('conferencelist',$year,$month,$day,$account);
    if ($day){
      $this->setAction('dailysummary',$year,$month,$day);
    }elseif ($month){
      $this->setAction('mtd',$year, $month);
    }else{
      $this->setAction('ytd', $year);
    }
  }
  function ytd($year=null)
  {
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    if(!$year)
      $year = date('Y');

    $this->set('myYear', $year);

    $criteria = array();

     switch ($user['User']['level_type']) {
      case 'reseller':
      case 'admin':
        if(!is_null($user['Resellers'])) {
          //if there are reseller ids to filter then put these in the filter
          $model = 'YearToDateByReseller';
          $data =$this->YearToDateByReseller->getYearToDateByReseller($user['Resellers'], $year);
        } else if ($user['User']['ic_employee']){
          //IC reseller with no reseller listing show all
          $criteria['YearToDate.DATE'] = 'BETWEEN 01/01/'.$year. ' 00:00:00 AND 12/31/'.$year.' 23:59:59';
          $model = 'YearToDate';
          $data = $this->YearToDate->findAll($criteria, null, 'YearToDate.DATE ASC');
        } else {
          //hide everything
        }
        break;
      case 'salesperson':
        if(!is_null($user['Salespersons'])) {
          //if there are salespids to filter then put these in the filter
          $model = 'YearToDateBySalesperson';
          $data =$this->YearToDateBySalesperson->getYearToDateBySalesperson($user['Salespersons'], $year);
        } else {
          //restrict everything else?
        }
        break;
      default:
        //restricted logic here
        break;
    }
    
    $myData = $this->YearToDate->findAll($criteria, null, 'YearToDate.DATE ASC');
    $this->set('chart', $this->Graphs->BuildChartData($data, null, $model));
  }

  /*
  function usageByWeek_YTD($year=null)
  //DC Is this needed?
  {
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    if(!$year)
      $year = date('Y');

    $this->set('myYear', $year);

    $myData = $this->Reports->Usage_YTDbyWeek($year, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->set('chart', $this->Graphs->BuildChartData($myData));
    $this->render('ytdbyweek');
  }
   */
  function mtd($year=null, $month=null)
  {
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    if(!$year)
      $year = date('Y');

    if(!$month)
      $month = date('m');

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    
    $focusMonth = month_boundaries($month, $year);
    $criteria = array();

    switch ($user['User']['level_type']) {
      case 'reseller':
      case 'admin':
        if(!is_null($user['Resellers'])) {
          //if there are resellerids to filter then put these in the filter
          $model = 'MonthToDateByReseller';
          $data =$this->MonthToDateByReseller->getMonthToDateByReseller($user['Resellers'], $focusMonth['start_of_month'], $focusMonth['end_of_month']);
        } else if ($user['User']['ic_employee']){
          //IC reseller with no reseller listing show all
          $criteria['MonthToDate.DATE'] = 'BETWEEN '.$focusMonth['start_of_month'].' AND '.$focusMonth['end_of_month'];
          $model = 'MonthToDate';
          $data = $this->MonthToDate->findAll($criteria, null, 'MonthToDate.DATE ASC');
        } else {
          //hide everything
        }
        break;
      case 'salesperson':
        if(!is_null($user['Salespersons'])) {
          //if there are salespids to filter then put these in the filter
          $model = 'MonthToDateBySalesperson';
          $data =$this->MonthToDateBySalesperson->getMonthToDateBySalesperson($user['Salespersons'], $focusMonth['start_of_month'], $focusMonth['end_of_month']);
        } else {
          //restrict everything else?
        }
        break;
      default:
        //restricted logic here
        break;
    }

    $myData = $this->MonthToDate->findAll($criteria, null, 'MonthToDate.DATE ASC');
    $this->set('chart', $this->Graphs->BuildChartData($data, 'm/d', $model));
  }
/*

  function dailysummary($year=null, $month=null, $day=null)
  {
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    if(!$year)
      $year = date('Y');

    if(!$month)
      $month = date('m');

    if(!$day)
      $day = date('d')-1;

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myDay', $day);

    $myData = $this->Reports->Usage_SingleDay($year, $month, $day, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->set('chart', $this->Graphs->BuildChartData($myData));
  }
  function weeklysummary($year=null, $week=null)
  {
  //DC Is this needed?
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    if(!$year)
      $year = date('Y');

    if(!$week)
      $year = date('W');

    $this->set('myYear', $year);
    $this->set('myWeek', $week);

    $myData = $this->Reports->Usage_SingleWeek($year, $week, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->set('chart', $this->Graphs->BuildChartData($myData));
  }

  function conferencelist($year=null, $month=null, $day=null, $account=null)
  {
  //DC Is this needed?
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    if(!$year)
      $year = date('Y');

    if(!$month)
      $month = date('m');

    if(!$day)
      $day = date('d')-1;

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myDay', $day);
    $this->set('myAccount', $account);

    $myData = $this->Reports->Usage_ConferenceList($year, $month, $day, $account, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->set('chart', $this->Graphs->BuildChartData($myData));
  }

  function conferencelistbyweek($year=null, $week=null, $account=null)
  {
  //DC Is this needed?
    list($user, $reseller_ids, $salespids) = $this->buildUserLists();

    $this->pageTitle = 'Usage Report:Conference List';

    if(!$year)
      $year = date('Y');

    if(!$week)
      $week = date('W');

    $this->set('myYear', $year);
    $this->set('myWeek', $week);
    $this->set('myAccount', $account);

    $myData = $this->Reports->Usage_ConferenceListByWeek($year, $week, $account, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->set('chart', $this->Graphs->BuildChartData($myData));
  }
 */
  function usage_monthlyaccount($year=null, $month=null, $acctgrpid=null)
  {
    $user = $this->Session->read('User');
    if(!$year)
      $year = date('Y');

    if(!$month)
      $month = date('m');

    if($account = $this->Account->get($acctgrpid, $user)) {
      $this->set('myYear', $year);
      $this->set('myMonth', $month);

      $focusMonth = month_boundaries($month, $year);
      $mtd_criteria = Array('DATE' => "BETWEEN {$focusMonth['start_of_month']} AND {$focusMonth['end_of_month']}", 'acctgrpid' => $acctgrpid);
      $mtd_data = $this->MonthToDateByAccount->findAll($mtd_criteria, null, 'DATE ASC');
      $this->set('chart', $this->Graphs->BuildChartData_MonthlyAccount($mtd_data));
    }
  }
/*
  function resellers($year=NULL, $month=NULL, $resellerid=NULL)
  {
  //DC Is this needed?
    $this->pageTitle = 'Resellers Report:YTD';

    if(!$year)
      $year = date('Y');

    if($month < 0)
      $month = date('m');

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myReseller', $resellerid);

    $myData = $this->Reports->Reseller_YTD($year,$month,$resellerid);

    $this->set('chart', $this->Graphs->BuildChartData($myData));
  }	
 */
}
