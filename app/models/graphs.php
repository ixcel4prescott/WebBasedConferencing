<?php

class Graphs extends AppModel
{
  var $name        = 'Graphs';
  var $useTable    = 'icsummary_daily';

  var $validate = array();

  function Usage_YTD($year, $resellerid_list=null, $salespid_list=null){
    if ($resellerid_list){
      $sql = "SELECT * FROM icsummary_daily_ytd WHERE date like '%/$year' AND resellerid IN ($resellerid_list);";
    }elseif ($salespid_list){
      $sql = "SELECT     TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7) AS date, SUM(ics.rl_confcount) AS rl_count, SUM(ics.rl_minutes) 
					                      AS rl_minutes, SUM(ics.rl_cost) AS rl_cost, SUM(ics.oa_confcount) AS oa_count, SUM(ics.oa_minutes) AS oa_minutes, SUM(ics.oa_cost) AS oa_cost, 
					                      SUM(ics.wb_confcount) AS wb_count, SUM(ics.wb_minutes) AS wb_minutes, SUM(ics.wb_cost) AS wb_cost, SUM(ics.rl_confcount) 
					                      + SUM(ics.oa_confcount) + SUM(ics.wb_confcount) AS total_count, SUM(ics.rl_minutes) + SUM(ics.oa_minutes) + SUM(ics.wb_minutes) 
					                      AS total_minutes, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) AS total_cost, SUM(ics.enhanced_count) AS enhanced_count, 
					                      SUM(ics.enhanced_cost) AS enhanced_cost, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) + SUM(ics.enhanced_cost) 
					                      AS grandtotal_cost
					FROM         salesperson INNER JOIN
					                      accountgroup ON salesperson.salespid = accountgroup.salespid INNER JOIN
					                      icsummary_monthly ics ON accountgroup.acctgrpid = ics.acctgrpid
					WHERE     (YEAR(ics.[date]) = $year) AND (salesperson.salespid IN ($salespid_list))
					GROUP BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)
					ORDER BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)";
    }else{
      $sql = "SELECT * FROM icsummary_daily_total_ytd WHERE date like '%/$year';";
    }

    $myData = $this->query($sql);
    return $myData;
  }

  function Usage_MTD($year, $month, $resellerid_list=null, $salespid_list=null){
    if ($resellerid_list){
      $sql = "SELECT * FROM icsummary_daily_mtd WHERE YEAR(date) = $year AND MONTH(date) = $month AND resellerid IN ($resellerid_list);";
    }elseif ($salespid_list){
      $sql = "SELECT     TOP 100 PERCENT dbo.icsummary_daily.[date], COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count,
	                      COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) AS rl_minutes, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) AS rl_cost,
	                      COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) AS oa_count, COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) AS oa_minutes,
	                      COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) AS oa_cost, COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS wb_count,
	                      COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS wb_minutes, COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS wb_cost,
	                      COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0)
	                      + COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS total_count, COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0)
	                      + COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS total_minutes,
	                      COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0)
	                      + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS total_cost, COALESCE (SUM(dbo.icsummary_daily.enhanced_count), 0) AS enhanced_count,
	                      COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS enhanced_cost, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0)
	                      + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0)
	                      + COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS grandtotal_cost
				FROM         dbo.salesperson INNER JOIN
	                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
	                      dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
				WHERE YEAR(dbo.icsummary_daily.date) = $year AND MONTH(dbo.icsummary_daily.date) = $month AND dbo.salesperson.salespid IN ($salespid_list)
				GROUP BY dbo.icsummary_daily.[date]
				ORDER BY dbo.icsummary_daily.[date]";

    }else{
      $sql = "SELECT * FROM icsummary_daily_total_mtd WHERE YEAR(date) = $year AND MONTH(date) = $month;";
    }

    $myData = $this->query($sql);
    return $myData;
  }

  function Usage_SingleDay($year, $month, $day, $resellerid_list=null, $salespid_list=null){
    $where = "YEAR(date) = $year AND MONTH(date) = $month AND DAY(date) = $day";
    if ($resellerid_list){
      $where .= " AND resellerid IN ($resellerid_list)";
    }elseif ($salespid_list){
      $where .= " AND salespid IN ($salespid_list)";
    }else{
      $where .= " AND resellerid <> 3";
    }

    $sql = "SELECT * FROM icsummary_daily_view WHERE $where;";

    $myData = $this->query($sql);
    return $myData;
  }

  function Usage_ConferenceList($year, $month, $day, $account, $resellerid_list=null, $salespid_list=null){
    $where = "YEAR(date) = $year AND MONTH(date) = $month AND DAY(date) = $day AND acctgrpid='$account'";
    if ($resellerid_list) $where .= " AND resellerid IN ($resellerid_list)";
    if ($salespid_list) $where .= " AND salespid IN ($salespid_list)";

    $sql = "SELECT * FROM icbilltab_daily WHERE $where;";

    $myData = $this->query($sql);
    return $myData;
  }

  function temp(){
    $query = "SELECT * FROM table WHERE col1 = ? AND col2 = ?";
    $this->query($query, array($value1, $value2));
  }

  function BuildChartData($rawData, $dateFormat=null, $model=null){
    $iCount = 0;
    $chart['chart_data'][0][$iCount] = '';
    $chart['chart_data'][1][$iCount] = 'RL Mins';
    $chart['chart_data'][2][$iCount] = 'OA Mins';
    $chart['chart_data'][3][$iCount] = 'Web Mins';
    
    if(!$model){
      $model = 'MyData';
    }

    foreach ($rawData as $dataLine){
      $iCount++;

      if ($dateFormat){
	$theDate = date($dateFormat,strtotime($dataLine[$model]['date']));
      }else{
	$theDate = $dataLine[$model]['date'];
      }
      $chart['chart_data'][0][$iCount] = $theDate;
      $chart['chart_data'][1][$iCount] = $dataLine[$model]['reservationless_minutes'];
      $chart['chart_data'][2][$iCount] = $dataLine[$model]['operator_assisted_minutes'];
      $chart['chart_data'][3][$iCount] = $dataLine[$model]['web_minutes'];
    }
    return $chart;
  }

  function BuildChartData_MonthlyAccount($rawData,$dateformat='d'){
    $iCount = 0;
    $chart['chart_data'][0][$iCount] = 'Date';
    $chart['chart_data'][1][$iCount] = 'RL Mins';
    $chart['chart_data'][2][$iCount] = 'OA Mins';
    $chart['chart_data'][3][$iCount] = 'Web Mins';

    foreach ($rawData as $dataLine){
      $iCount++;
      $chart['chart_data'][0][$iCount] = date($dateformat,strtotime($dataLine['MonthToDateByAccount']['date']));
      $chart['chart_data'][1][$iCount] = $dataLine['MonthToDateByAccount']['reservationless_minutes'];
      $chart['chart_data'][2][$iCount] = $dataLine['MonthToDateByAccount']['operator_assisted_minutes'];
      $chart['chart_data'][3][$iCount] = $dataLine['MonthToDateByAccount']['web_minutes'];
    }
    return $chart;
  }

  function Usage_MonthlyAccount($year, $month, $account){
    $where = "YEAR(date) = $year AND MONTH(date) = $month AND acctgrpid = '$account'";

    $sql = "SELECT * FROM icsummary_daily_view WHERE $where;";

    $myData = $this->query($sql);
    return $myData;
  }
}