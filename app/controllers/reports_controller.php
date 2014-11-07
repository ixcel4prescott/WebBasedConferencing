<?

class ReportsController extends AppController
{
  var $uses        = Array('RequestView', 'NewAccountReport', 'RapidviewReport', 'System', 'Reports', 'Monthlysummaryicdata',
                           'Monthlysummarydata', 'Monthlysummarybyspdata', 'Dailysummaryicdata',
                           'Dailysummarydata', 'Dailysummarybyspdata', 'Singledaysummarydata', 'Conferencedata', 'User',
                           'RateChangeSummary', 'ReportUsageByDate', 'AgentAccount',
                           'YearToDate', 'MonthToDate', 'MonthToDateByAccount','MonthToDateByReseller', 'MonthToDateBySalesperson',
                           'YearToDateByReseller', 'YearToDateBySalesperson', 'TopX');

  var $helpers     = Array('Html', 'Time', 'Pagination');
  var $components  = Array('Pagination');

  var $permissions = Array( 'index'                => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'rate_change'          => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'rate_change_summary'  => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'usage'                => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'usageByWeek'          => GROUP_IC_RESELLERS_AND_SALESPEOPLE,
                            'ytd'                  => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'mtd'                  => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'dailysummary'         => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'weeklysummary'        => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'conferencelist'       => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'conferencelistbyweek' => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'topx'                 => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'topx2'                => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'topxbymonth'          => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'salespeople'          => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'topsales'             => GROUP_RESELLERS,
                            'resellers'            => GROUP_IC_RESELLERS,
                            'new_accounts'         => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'rapidview'            => GROUP_RESELLERS_AND_SALESPEOPLE,
                            'usage_by_date'        => GROUP_IC_RESELLERS,
                            'agent_accounts'       => GROUP_IC_RESELLERS
                            );

  function index()
  {
    $this->setAction('usage');
  }

  function agent_accounts()
  {
    /* Shows all of the agent accounts owned by Infinite. This should only be
     * viewed by users with infinite reseller authority.
     */
    $criteria = Array();
    $query    = '';

    if(!empty($_GET['query'])) {
      $query = $this->AgentAccount->escape($_GET['query']);

      $criteria['OR'] = Array( 'acctgrpid'   => 'LIKE %'.$query.'%',
                               'company'     => 'LIKE %'.$query.'%',
                               'contact'     => 'LIKE %'.$query.'%',
                               'agent_name'  => 'LIKE %'.$query.'%',
                               'salesperson' => 'LIKE %'.$query.'%' );
    }

    $this->set('query', $query);

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('modelClass', 'AgentAccount', 'sortBy', 'acctgrpid'));

    if(!empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
      $limit = null;
      $page  = null;
    }

    $data = $this->AgentAccount->findAll($criteria, null, $order, $limit, $page);

    if(!empty($_GET['export'])) {

      $filename = sprintf('Agent Accounts Report - %s', date('Y-m-d'));

      $keys     = Array('acctgrpid', 'agent_name', 'salesperson', 'company', 'contact');

      $headers  = Array( 'acctgrpid'        => 'Account Number',
                         'agent_name'       => 'Agent Name',
                         'salesperson'      => 'Salesperson',
                         'company'          => 'Company',
                         'contact'          => 'Contact' );

      export_csv($filename, $keys, $headers, pluck($data, 'AgentAccount'));

      die;
    } else {
      $this->set('data', $data);
    }
  }

  function new_accounts($year=null, $month=null)
  {
    $user = $this->Session->read('User');

    $criteria = Array();

    if($user['User']['level_type'] != 'salesperson' && 
        !is_null($user['Resellers']))
      $criteria['NewAccountReport.resellerid'] = $user['Resellers'];
    elseif($user['User']['level_type'] == 'salesperson')
      $criteria['NewAccountReport.salespid'] = $user['Salespersons'];

    if(empty($_GET['start']))
      $_GET['start'] = date('m/1/Y', strtotime('this month'));

    if(empty($_GET['end']))
      $_GET['end'] = date('m/t/Y', strtotime('now'));

    $start_time=$_GET['start'].' 00:00:00';
    $end_time=$_GET['end'].' 23:59:59';

    $criteria['NewAccountReport.created'] = 'BETWEEN ' . $start_time. ' AND ' . $end_time;


    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('modelClass', 'NewAccountReport', 'sortBy', 'created', 'direction', 'DESC'));

    if(!empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';
      $limit = null;
      $page  = null;
    }

    $data = $this->NewAccountReport->findAll($criteria, null, $order, $limit, $page);
    if(!empty($_GET['export'])) {

      $filename = sprintf('New Account Report - %s to %s',
                          str_replace('/', '-', $_GET['start']),
                          str_replace('/', '-', $_GET['end']));

      $keys     = Array('acctgrpid', 'company', 'salesperson_name', 'created', 'minutes', 'cost');

      $headers  = Array( 'acctgrpid'        => 'Account Number',
                         'company'          => 'Company',
                         'salesperson_name' => 'Salesperson',
                         'created'          => 'Created On',
                         'minutes'          => 'Minutes',
                         'cost'             => 'Cost' );

      export_csv($filename, $keys, $headers, pluck($data, 'NewAccountReport'));

      die;
    } else {
      $this->set(compact('data', 'month', 'year', 'prev', 'next'));
    }
  }
 
  function usage($year=null, $month=null, $day=null)
  {
    if(!$year)
      $year = date('Y');

    //Removing branch to deprecated logic DC - 2012/09/11
    //if($account)
    //  $this->setAction('conferencelist', $year, $month, $day, $account);
    if($day)
      $this->setAction('dailysummary', $year, $month, $day);
    elseif ($month)
      $this->setAction('mtd', $year, $month);
    else
      $this->setAction('ytd', $year);
  }

/*
  function usageByWeek($year=null, $week=null, $account=null)
  {
  //Probably not used
    if(!$year)
      $year = date('Y');

    if($account)
      $this->setAction('conferencelistbyweek', $year, $week, $account);
    elseif ($week)
      $this->setAction('weeklysummary', $year, $week);
    else
      $this->setAction('usageByWeek_YTD', $year);
  }
 */

  function format_ytd_export($row)
  // Formatter for ytd export
  {
    return Array( 'date'            => $row['date'],

                  'reservationless_minutes'        => $row['reservationless_minutes'],
                  'operator_assisted_minutes'      => $row['operator_assisted_minutes'],
                  'web_minutes'                    => $row['web_minutes'],
                  'total_minutes'                  => $row['total_minutes'],

                  'reservationless_cost'           => $row['reservationless_cost'],
                  'operator_assisted_cost'         => $row['operator_assisted_cost'],
                  'web_cost'                       => $row['web_cost'],

                  'flat_cost'                      => $row['flat_cost'],
                  'enhanced_service_cost'          => $row['enhanced_service_cost'],
                  'other_cost'                     => $row['other_cost'],

                  'total_cost'                     => $row['total_cost'] );
  }
 
  function format_reseller_export($row)
  {
    return Array( 'date'                           => $row['date'],
                  'resellerid'                     => $row['resellerid'],
                  'reservationless_minutes'        => $row['reservationless_minutes'],
                  'operator_assisted_minutes'      => $row['operator_assisted_minutes'],
                  'web_minutes'                    => $row['web_minutes'],
                  'total_minutes'                  => $row['total_minutes'],

                  'reservationless_cost'           => $row['reservationless_cost'],
                  'operator_assisted_cost'         => $row['operator_assisted_cost'],
                  'web_cost'                       => $row['web_cost'],

                  'flat_cost'                      => $row['flat_cost'],
                  'enhanced_service_cost'          => $row['enhanced_service_cost'],
                  'other_cost'                     => $row['other_cost'],

                  'total_cost'                     => $row['total_cost'] );
  }
 
  function ytd($year=null)
  {
    $this->pageTitle = 'Usage Report: YTD';

    $user = $this->Session->read('User');

    if(!$year)
      $year = date('Y');

    $this->set('myYear', $year);

    $criteria = array();

    switch ($user['User']['level_type']) {
    //When there is a case match PHP executes all statements INCLUDING statements in proceeding cases until a break is found or till it 
    //reaches the end of the switch block. This is how the reseller, admin, and reseller_admin all share the same logic. 
    case 'reseller':
    case 'reseller_admin':
    case 'admin':
      //reseller logic here
      $model = 'YearToDateByReseller';
    if(!is_null($user['Resellers'])) {
      //if there are reseller ids to filter then put these in the filter
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
      //salesperson logic here I will probably need to work this out with Marc since salespid isn't on CODR
      $model = 'YearToDateBySalesperson';
      if(!is_null($user['Salespersons'])) {
        //if there are reseller ids to filter then put these in the filter
        $data =$this->YearToDateBySalesperson->getYearToDateBySalesperson($user['Salespersons'], $year);
      } else {
        //restrict everything else?
        $data =$this->YearToDateBySalesperson->getYearToDateBySalesperson('', $year);
      }
      break;
    default:
      //restricted logic here
      break;
    }

    if(!empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';

      $filename = sprintf('Year to Date Report for %d- %s', $year, date('Ymd'));

      $keys     = Array( 'date',
                         'reservationless_minutes', 'operator_assisted_minutes', 'web_minutes', 'total_minutes',
                         'reservationless_cost', 'operator_assisted_cost', 'web_cost',
                         'flat_cost', 'enhanced_service_cost', 'other_cost', 'total_cost' );

      $headers  = Array( 'date'                         => 'Date',
                         'reservationless_minutes'      => 'Reservationless Minutes',
                         'operator_assisted_minutes'    => 'Operator-Assisted Minutes',
                         'web_minutes'                  => 'Web Minutes',
                         'total_minutes'                => 'Total Minutes',
                         'reservationless_cost'         => 'Reservationless Cost',
                         'operator_assisted_cost'       => 'Operator-Assisted Cost',
                         'web_cost'                     => 'Web Cost',
                         'flat_cost'                    => 'Flat Cost',
                         'enhanced_service_cost'        => 'Enhanced Services Cost',
                         'other_cost'                   => 'Other Cost',
                         'total_cost'                   => 'Grand Total');

      export_csv($filename, $keys, $headers, pluck($data, $model), Array($this, 'format_ytd_export'));

      die;
    } else {
      $this->set('dates', $data);
      $this->set('model', $model);
    }
  }

/*
  function usageByWeek_YTD($year=null)
  //Probably not used
  {
    $this->pageTitle = 'Usage Report By Week: YTD';

    $user = $this->Session->read('User');

    if(!$year)
      $year = date('Y');

    $this->set('myYear', $year);

    $data = $this->Reports->Usage_YTDbyWeek($year, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->set('dates', $data);
    $this->set('chart', $this->Reports->BuildChartData($data));

    $this->render('ytdbyweek');
  }
 */

  function format_mtd_export($row)
  {
  // Formatter for mtd export
    return Array( 'date'            => $row['date'],

                  'reservationless_minutes'        => $row['reservationless_minutes'],
                  'operator_assisted_minutes'      => $row['operator_assisted_minutes'],
                  'web_minutes'                    => $row['web_minutes'],
                  'total_minutes'                  => $row['total_minutes'],

                  'reservationless_cost'           => $row['reservationless_cost'],
                  'operator_assisted_cost'         => $row['operator_assisted_cost'],
                  'web_cost'                       => $row['web_cost'],

                  'flat_cost'                      => $row['flat_cost'],
                  'enhanced_service_cost'          => $row['enhanced_service_cost'],
                  'other_cost'                     => $row['other_cost'],

                  'total_cost'                     => $row['total_cost'] );
  }

  function mtd($year=null, $month=null)
  {
    $this->pageTitle = 'Usage Report: MTD';

    $user = $this->Session->read('User');

    if(!$year)
      $year = date('Y');

    if (!$month)
      $month = date('m');

    $this->set('myYear', $year);
    $this->set('myMonth', $month);

    $focusMonth = month_boundaries($month, $year);
    $criteria = array();

    switch ($user['User']['level_type']) {
    case 'reseller':
    case 'reseller_admin':
    case 'admin':
      //reseller logic here
      if(!is_null($user['Resellers'])) {
        //if there are reseller ids to filter then put these in the filter
        $model = 'MonthToDateByReseller';
        $data =$this->MonthToDateByReseller->getMonthToDateByReseller($user['Resellers'],
                                                                      $focusMonth['start_of_month'],
                                                                      $focusMonth['end_of_month']);
      } else if ($user['User']['ic_employee']){
        //IC reseller with no reseller listing show all
        $model = 'MonthToDate';
        $criteria['MonthToDate.DATE'] = 'BETWEEN '.$focusMonth['start_of_month'].' AND '.$focusMonth['end_of_month'];
        $data = $this->MonthToDate->findAll($criteria, null, 'MonthToDate.DATE ASC');
      } else {
        //restrict everything else?
      }
    break;
    case 'salesperson':
      $model = 'MonthToDateBySalesperson';
      if(!is_null($user['Salespersons'])) {
        //if there are reseller ids to filter then put these in the filter
        $data =$this->MonthToDateBySalesperson->getMonthToDateBySalesperson($user['Salespersons'],
                                                                            $focusMonth['start_of_month'],
                                                                            $focusMonth['end_of_month']);
      } else {
        //restrict everything else?
      }
      break;
    default:
      //restricted logic here
      break;
    }

    if(!empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';

      $filename = sprintf('Month to Date Report for %02d-%d - %s', $month, $year, date('Ymd'));

      $keys     = Array( 'date',
                         'reservationless_minutes', 'operator_assisted_minutes', 'web_minutes', 'total_minutes',
                         'reservationless_cost', 'operator_assisted_cost', 'web_cost',
                         'flat_cost', 'enhanced_service_cost', 'other_cost', 'total_cost' );

      $headers  = Array( 'date'                         => 'Date',
                         'reservationless_minutes'      => 'Reservationless Minutes',
                         'operator_assisted_minutes'    => 'Operator-Assisted Minutes',
                         'web_minutes'                  => 'Web Minutes',
                         'total_minutes'                => 'Total Minutes',
                         'reservationless_cost'         => 'Reservationless Cost',
                         'operator_assisted_cost'       => 'Operator-Assisted Cost',
                         'web_cost'                     => 'Web Cost',
                         'flat_cost'                    => 'Flat Cost',
                         'enhanced_service_cost'        => 'Enhanced Services Cost',
                         'other_cost'                   => 'Other Cost',
                         'total_cost'                   => 'Grand Total');

      export_csv($filename, $keys, $headers, pluck($data, $model), Array($this, 'format_mtd_export'));

      die;
    } else {
      $this->set('dates', $data);
      $this->set('model', $model);
    }
  }

  function format_dailysummary_export($row)
  {
  // Formatter for dailysummary export
    return Array( 'acctgrpid'       => $row['acctgrpid'],
                  'company_name'    => $row['company_name'],

                  'reservationless_minutes'        => $row['reservationless_minutes'],
                  'operator_assisted_minutes'      => $row['operator_assisted_minutes'],
                  'web_minutes'                    => $row['web_minutes'],
                  'total_minutes'                  => $row['total_minutes'],

                  'reservationless_cost'           => $row['reservationless_cost'],
                  'avg_reservationless_ppm'        => ($row['reservationless_cost']/$row['reservationless_minutes']),
                  'operator_assisted_cost'         => $row['operator_assisted_cost'],
                  'avg_operator_assisted_ppm'      => ($row['operator_assisted_cost']/$row['operator_assisted_minutes']),
                  'web_cost'                       => $row['web_cost'],

                  'flat_cost'                      => $row['flat_cost'],
                  'enhanced_service_cost'          => $row['enhanced_service_cost'],
                  'other_cost'                      => $row['other_cost'],

                  'total_cost'                     => $row['total_cost'] );
  }

  function dailysummary($year=null, $month=null, $day=null)
  {
    $this->pageTitle = 'Usage Report: Daily Summary';

    $user = $this->Session->read('User');

    if (!$year)
      $year = date('Y');

    if (!$month)
      $month = date('m');

    if (!$day)
      $day = date('d')-1;

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myDay', $day);
    $criteria = array();
    
    $startDt = $month.'/'.$day.'/'.$year.' 00:00:00';
    $endDt = $month.'/'.$day.'/'.$year.' 23:59:59';

    switch ($user['User']['level_type']) {
    case 'reseller':
    case 'reseller_admin':
    case 'admin':
      //reseller logic here
      if(!is_null($user['Resellers'])) {
        //if there are reseller ids to filter then put these in the filter
        $criteria['MonthToDateByAccount.DATE'] = 'BETWEEN '.$month.'/'.$day.'/'.$year.' 00:00:00 AND '.$month.'/'.$day.'/'.$year.' 23:59:59';
        $criteria['MonthToDateByAccount.resellerid'] = $user['Resellers'];
        $model = 'MonthToDateByAccount';
      } else if ($user['User']['ic_employee']){
        //IC reseller with no reseller listing show all
        $criteria['MonthToDateByAccount.DATE'] = 'BETWEEN '.$month.'/'.$day.'/'.$year.' 00:00:00 AND '.$month.'/'.$day.'/'.$year.' 23:59:59';
        $model = 'MonthToDateByAccount';
      } else {
        //hide everything
      }
    break;
    case 'salesperson':
      //salesperson logic here I will probably need to work this out with Marc since salespid isn't on CODR
      $model = 'MonthToDateByAccount';
      if(!is_null($user['Salespersons'])) {
        //if there are reseller ids to filter then put these in the filter
        $criteria['MonthToDateByAccount.DATE'] = 'BETWEEN '.$month.'/'.$day.'/'.$year.' 00:00:00 AND '.$month.'/'.$day.'/'.$year.' 23:59:59';
        $criteria['MonthToDateByAccount.salespid'] = $user['Salespersons'];
        $model = 'MonthToDateByAccount';
      } else {
      }
      break;
    default:
      break;
    }

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('modelClass', $model,
                                                                              'sortByClass', $model,
                                                                              'sortBy', $this->{$model}->primaryKey,
                                                                              'direction', 'ASC'));
    if(!empty($_GET['export']))
      $limit = 5000;
    $data = $this->{$model}->findAll($criteria, null, $order, $limit, $page);

    if(!empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';

      $filename = sprintf('Daily Summary for %02d-%02d-%d - %s', $month, $day, $year, date('Ymd'));

      $keys     = Array( 'acctgrpid', 'company_name', 'date',
                         'reservationless_minutes', 'operator_assisted_minutes', 'web_minutes', 'total_minutes',
                         'reservationless_cost', 'avg_reservationless_ppm', 'operator_assisted_cost', 'avg_operator_assisted_ppm', 'web_cost',
                         'flat_cost', 'enhanced_service_cost', 'other_cost', 'total_cost' );

      $headers  = Array( 'acctgrpid'                    => 'Account Number',
                         'company_name'                 => 'Company Name',
                         'reservationless_minutes'      => 'Reservationless Minutes',
                         'operator_assisted_minutes'    => 'Operator-Assisted Minutes',
                         'web_minutes'                  => 'Web Minutes',
                         'total_minutes'                => 'Total Minutes',
                         'reservationless_cost'         => 'Reservationless Cost',
                         'avg_reservationless_ppm'      => 'Avg Reservationless PPM',
                         'operator_assisted_cost'       => 'Operator-Assisted Cost',
                         'avg_operator_assisted_ppm'    => 'Avg Operator Assisted PPM',
                         'web_cost'                     => 'Web Cost',
                         'flat_cost'                    => 'Flat Cost',
                         'enhanced_service_cost'        => 'Enhanced Services Cost',
                         'other_cost'                    => 'Other Cost',
                         'total_cost'                   => 'Grand Total');

      export_csv($filename, $keys, $headers, pluck($data, $model), Array($this, 'format_dailysummary_export'));

      die;
    } else {
      $this->set('dates', $data);
      $this->set('model', $model);
    }
  }

/*
  function weeklysummary($year=null, $week=null)
  {
  //Probably not used
    $this->pageTitle = 'Usage Report: Daily Summary';

    $user = $this->Session->read('User');

    if (!$year)
      $year = date('Y');

    if (!$week)
      $week = date('W');

    $this->set('myYear', $year);
    $this->set('myWeek', $week);

    $data = $this->Reports->Usage_SingleWeek($year, $week, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);
    $this->set('dates', $data);
  }

  function conferencelist($year=null, $month=null, $day=null, $account=null){
  //Probably not used
    $this->pageTitle = 'Usage Report: Conference List';

    $user = $this->Session->read('User');

    if (!$year)
      $year = date('Y');

    if (!$month)
      $month = date('m');

    if (!$day)
      $day = date('d')-1;

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myDay', $day);
    $this->set('myAccount', $account);

    $data = $this->Reports->Usage_ConferenceList($year, $month, $day, $account, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    if(!empty($_GET['export'])) {

      Configure::write('debug', 0);
      $this->layout = 'ajax';

      $filename = sprintf('%s - Usage %s-%s-%d', $account, $year, $month, $day);

      $keys     = Array('accountid', 'calltype_text', 'billingcode', 'date', 'starttime', 'dnis', 'ani', 'username', 'minutes');

      $headers  = Array( 'accountid'   => 'Confirmation Number',
                         'calltype_text' => 'Call Type',
                         'billingcode'   => 'Billing Code',
                         'date'          => 'Date',
                         'starttime'     => 'Start Time',
                         'dnis'          => 'Number Dialed',
                         'ani'           => 'Incoming Number',
                         'username'      => 'Username',
                         'minutes'       => 'Duration' );

      export_csv($filename, $keys, $headers, pluck($data, 0));

      die;
    } else {
      $this->set('dates', $data);
    }
  }

  function conferencelistbyweek($year=null, $week=null, $account=null){
  //Probably not used
    $this->pageTitle = 'Usage Report: Conference List';

    $user = $this->Session->read('User');

    if (!$year)
      $year = date('Y');

    if (!$week)
      $week = date('W');

    $this->set('myYear', $year);
    $this->set('myWeek', $week);
    $this->set('myAccount', $account);

    $data = $this->Reports->Usage_ConferenceListByWeek($year, $week, $account, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);
    $this->set('dates', $data);
  }
 */

  function topx($salespid=null) {
    $user = $this->Session->read('User');
    $criteria = Array();
    switch ($user['User']['level_type']) {
    case 'reseller':
    case 'admin':
      //reseller logic here
      if(!is_null($user['Resellers'])) {
        //if there are reseller ids to filter then put these in the filter
        $criteria['TopX.resellerid'] = $user['Resellers'];
      } else if ($user['User']['ic_employee']){
        //IC reseller with no reseller listing show all
      } else {
        //hide everything
        $criteria['TopX.resellerid'] = '';
      }
    break;
    case 'salesperson':
      //salesperson logic here I will probably need to work this out with Marc since salespid isn't on CODR
      if(!is_null($user['Salespersons'])) {
        //if there are reseller ids to filter then put these in the filter
        $criteria['TopX.salespid'] = $user['Salespersons'];
      } else {
        $criteria['TopX.salespid'] = '';
      }
      break;
    default:
      //restricted logic here
      $criteria['TopX.resellerid'] = '';
      $criteria['TopX.salespid'] = '';
      break;
    }

    if ($salespid)
      $criteria['TopX.salespid'] = $salespid;

    $measure = (!empty($_GET['measure'])) ? $_GET['measure'] : 'minutes';
    $type    = (!empty($_GET['type']))    ? $_GET['type']    : '';
    $count   = (!empty($_GET['count']))   ? $_GET['count']   : '40';
    $year    = (!empty($_GET['year']))    ? $_GET['year']    : date('Y');
    $month   = (!empty($_GET['month']))   ? $_GET['month']   : date('m');
    $sort    = (!empty($_GET['sort']))    ? $_GET['sort']    : 'percent_change';
    $order   = (!empty($_GET['order']))   ? $_GET['order']   : 'desc';

    $criteria['Topx.date'] = $month.'/1/'.$year;
    $fields = "TopX.acctgrpid as acctgrpid,
               TopX.salespid as salespid,
               TopX.company_name as company_name,
               TopX.account_manager as account_manager";
    switch ($type){
    case '':
      $fields=$fields.", TopX.current_total_$measure as current_data,
                           TopX.previous_total_$measure as previous_data,
                           TopX.difference_total_$measure as delta,
                           TopX.percent_change_total_$measure as percent_change";
      break;

    case 'reservationless':
      $fields=$fields.", TopX.current_reservationless_$measure as current_data,
                           TopX.previous_reservationless_$measure as previous_data,
                           TopX.difference_reservationless_$measure as delta,
                           TopX.percent_change_reservationless_$measure as percent_change";
      break;

    case 'opassist':
      $fields=$fields.", TopX.current_operator_assisted_$measure as current_data,
                           TopX.previous_operator_assisted_$measure as previous_data,
                           TopX.difference_operator_assisted_$measure as delta,
                           TopX.percent_change_operator_assisted_$measure as percent_change";
      break;

    case 'audio':
      $fields="rl_$measure+oa_$measure";
      break;

    case 'web':
      $fields=$fields.", TopX.current_web_$measure as current_data,
                           TopX.previous_web_$measure as previous_data,
                           TopX.difference_web_$measure as delta,
                           TopX.percent_change_web_$measure as percent_change";
      break;

    case 'enhanced':
      $measure = 'cost';
      $fields=$fields.", TopX.current_enhanced_service_$measure as current_data,
                           TopX.previous_enhanced_service_$measure as previous_data,
                           TopX.difference_enhanced_service_$measure as delta,
                           TopX.percent_change_enhanced_service_$measure as percent_change";
      break;
        
    case 'flat':
      $measure = 'cost';
      $fields=$fields.", TopX.current_flat_$measure as current_data,
                           TopX.previous_flat_$measure as previous_data,
                           TopX.difference_flat_$measure as delta,
                           TopX.percent_change_flat_$measure as percent_change";
      break;

    case 'other':
      $measure = 'cost';
      $fields=$fields.", TopX.current_other_$measure as current_data,
                           TopX.previous_other_$measure as previous_data,
                           TopX.difference_other_$measure as delta,
                           TopX.percent_change_other_$measure as percent_change";
      break;
    }

    $data = $this->TopX->findAll($criteria, $fields, $sort.' '.$order, $count);

    $this->set('current_month', date('m'));
    $this->set('current_year',  date('Y'));
    $this->set('month', $month);
    $this->set('year', $year);
    $this->set('count', $count);
    $this->set('measure', $measure);
    $this->set('data', $data);
  }

  function topsales($measure='minutes', $type='all', $field='delta', $sort='desc', $range=30){
    $user = $this->Session->read('User');

    switch($type){
    case 'all':
      $field_summary="rl_$measure+oa_$measure+wb_$measure";
      break;
    case 'reservationless':
      $field_summary="rl_$measure";
      break;
    case 'opassist':
      $field_summary="oa_$measure";
      break;
    case 'audio':
      $field_summary="rl_$measure+oa_$measure";
      break;
    case 'web':
      $field_summary="wb_$measure";
      break;
    case 'enhanced':
      $measure = 'cost';
      $field_summary="enhanced_$measure";
      break;
    }

    $page_title = "TOP Salespersons: measured by $measure, sorted by $field $sort, $range day window";

    $order = "$field $sort";

    $myData = $this->Reports->TopSales($field_summary, $field, $sort, $range, $user['User']['level_type'], $user['Resellers'], $user['Salespersons']);

    $this->pageTitle = $page_title;
    $this->set('myMeasure', $measure);
    $this->set('myType', $type);
    $this->set('myOrder', $order);
    $this->set('myField', $field);
    $this->set('mySort', $sort);
    $this->set('myRange', $range);
    $this->set('myData', $myData);
  }

  function resellers($year=null, $month=0, $resellerid=null){
    $this->pageTitle = 'Resellers Report:YTD';

    $user = $this->Session->read('User');
    
    if(!$year)
      $year = date('Y');

    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myReseller', $resellerid);

//     $myData = $this->Reports->Reseller_YTD($year, $month, $resellerid);
    if ($month){
      $focusMonth = month_boundaries($month, $year);
      $model = 'YearToDateByReseller';
      $criteria['YearToDateByReseller.DATE'] = 'BETWEEN '.$focusMonth['start_of_month'].' AND '.$focusMonth['end_of_month'];
      $exportTitle = 'Month';
      if ($resellerid)
        $criteria['YearToDateByReseller.resellerid'] = $resellerid;

      $data = $this->YearToDateByReseller->findAll($criteria, null, 'YearToDateByReseller.DATE ASC');
    } else {
      $model = 'YearToDateByReseller';
      $exportTitle = 'Year';
      $data =$this->YearToDateByReseller->getYearToDateAllReseller($year);
    }

    if(!empty($_GET['export'])) {
      Configure::write('debug', 0);
      $this->layout = 'ajax';

      $filename = sprintf('Reseller %s to Date Report for %d- %s', $exportTitle, $year, date('Ymd'));

      $keys     = Array( 'date', 'resellerid',
                         'reservationless_minutes', 'operator_assisted_minutes', 'web_minutes', 'total_minutes',
                         'reservationless_cost', 'operator_assisted_cost', 'web_cost',
                         'flat_cost', 'enhanced_service_cost', 'other_cost', 'total_cost' );

      $headers  = Array( 'date'                         => 'Date',
                         'resellerid'                   => 'Reseller ID',
                         'reservationless_minutes'      => 'Reservationless Minutes',
                         'operator_assisted_minutes'    => 'Operator-Assisted Minutes',
                         'web_minutes'                  => 'Web Minutes',
                         'total_minutes'                => 'Total Minutes',
                         'reservationless_cost'         => 'Reservationless Cost',
                         'operator_assisted_cost'       => 'Operator-Assisted Cost',
                         'web_cost'                     => 'Web Cost',
                         'flat_cost'                    => 'Flat Cost',
                         'enhanced_service_cost'        => 'Enhanced Services Cost',
                         'other_cost'                   => 'Other Cost',
                         'total_cost'                   => 'Grand Total');
      if (!$month){
        array_splice($keys, 1, 1);
        array_splice($headers, 1, 1);
      }

      export_csv($filename, $keys, $headers, pluck($data, $model), Array($this, 'format_reseller_export'));

      die;
    } else {
      $this->set('dates', $data);
      $this->set('model', $model);
      //$this->set('chart', $this->Reports->BuildChartData($myData, 'Reseller_YTD'))
    }
  }
/*
  function rapidview($report_id=1, $year=null, $month=null)
  {
  //Probably not used
    if ($report_id != null){
      $url_map = $this->Reports->Get_URL_Map();
      $settings = $this->RapidviewReport->ReportSettings($report_id);

      if ($settings){
        list($user, $reseller_ids, $salespids) = $this->buildUserLists();

        $criteria = Array();

        if($user['User']['level_type'] == 'reseller')
          $criteria['RapidviewReport.resellerid'] = $reseller_ids;
        elseif($user['User']['level_type'] == 'salesperson')
          $criteria['RapidviewReport.salespid'] = $salespids;

        if($settings['date_field']) {
          if(empty($_GET['start']))
            $_GET['start'] = date('m/1/Y', strtotime('this month'));

          if(empty($_GET['end']))
            $_GET['end'] = date('m/1/Y', strtotime('next month'));

          $criteria['RapidviewReport.date'] = 'BETWEEN ' . $_GET['start'] . ' AND ' . $_GET['end'];

        }

        list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('modelClass', 'RapidviewReport', 'sortBy', $settings['sort_field'], 'direction', 'ASC'));

        $data = $this->RapidviewReport->findAll($criteria, null, $order, $limit, $page);

        $this->set(compact('data', 'month', 'year', 'prev', 'next', 'settings', 'url_map'));
      }
    }
  }
  function usage_by_date()
  {
  //Probably not used
    if(empty($_GET['start']))
      $_GET['start'] = date('m/d/y', strtotime('now -8 days'));

    $criteria = Array('ReportUsageByDate.confstartdate' => Array());
    for($i=0; $i<7; $i++)
      $criteria['ReportUsageByDate.confstartdate'][] = date('m/d/y', strtotime(sprintf('%s +%d days', $_GET['start'], $i)));

    list($order, $limit, $page) = $this->Pagination->init($criteria, null, aa('modelClass', 'ReportUsageByDate',
                                                                              'sortBy', 'confstartdate',
                                                                              'direction', 'ASC'));

    $data = $this->ReportUsageByDate->findAll($criteria, null, $order, $limit, $page);

    $this->set(compact('data', 'month', 'year', 'prev', 'next'));
  }
*/
   
}
