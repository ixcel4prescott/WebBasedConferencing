<?php

class Reports extends AppModel
{
  var $name        = 'Reports';
  var $useTable    = 'icsummary_daily';

  function Get_URL_Map()
  {
    $sql = 'SELECT * FROM myca_url_map;';
    $results = $this->query($sql);
    if ($results){
      for($i=0; $i<count($results); $i++){
	$return[$results[$i][0]['id']]['label'] = $results[$i][0]['label'];
	$return[$results[$i][0]['id']]['url'] = $results[$i][0]['url_template'];
      }

      return $return;
    }
  }

  function UsageYears()
  //DC this is used but it might be going.
  {
    $rv = $this->sql('SELECT DISTINCT YEAR(date) AS year FROM icsummary_daily');
    return pluck($rv, 'year');
  }

  function Usage_YTD($year, $level_type, $reseller_ids=null, $salespids=null) 
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    if($level_type == 'reseller' && is_null($reseller_ids)) {
      $sql = "SELECT * FROM icsummary_daily_total_ytd WHERE date like '%/$year' ORDER BY date;";

    } elseif ($level_type == 'reseller') {
      $sql = "SELECT TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7) AS date, SUM(ics.rl_confcount) AS rl_count, SUM(ics.rl_minutes)
		AS rl_minutes, SUM(ics.rl_cost) AS rl_cost, SUM(ics.oa_confcount) AS oa_count, SUM(ics.oa_minutes) AS oa_minutes, SUM(ics.oa_cost) AS oa_cost,
		SUM(ics.wb_confcount) AS wb_count, SUM(ics.wb_minutes) AS wb_minutes, SUM(ics.wb_cost) AS wb_cost, SUM(ics.rl_confcount)
		+ SUM(ics.oa_confcount) + SUM(ics.wb_confcount) AS total_count, SUM(ics.rl_minutes) + SUM(ics.oa_minutes) + SUM(ics.wb_minutes)
		AS total_minutes, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) AS total_cost, SUM(ics.enhanced_count) AS enhanced_count,
		SUM(ics.enhanced_cost) AS enhanced_cost, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) + SUM(ics.enhanced_cost)
		AS grandtotal_cost
	      FROM salesperson 
                INNER JOIN accountgroup ON salesperson.salespid = accountgroup.salespid 
                INNER JOIN icsummary_daily ics ON accountgroup.acctgrpid = ics.acctgrpid
	      WHERE (YEAR(ics.[date]) = $year) AND (salesperson.resellerid IN ($reseller_list))
	      GROUP BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)
	      ORDER BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)";

    } elseif ($level_type == 'salesperson') {
      $sql = "SELECT TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7) AS date, SUM(ics.rl_confcount) AS rl_count, SUM(ics.rl_minutes)
		AS rl_minutes, SUM(ics.rl_cost) AS rl_cost, SUM(ics.oa_confcount) AS oa_count, SUM(ics.oa_minutes) AS oa_minutes, SUM(ics.oa_cost) AS oa_cost,
		SUM(ics.wb_confcount) AS wb_count, SUM(ics.wb_minutes) AS wb_minutes, SUM(ics.wb_cost) AS wb_cost, SUM(ics.rl_confcount)
		+ SUM(ics.oa_confcount) + SUM(ics.wb_confcount) AS total_count, SUM(ics.rl_minutes) + SUM(ics.oa_minutes) + SUM(ics.wb_minutes)
		AS total_minutes, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) AS total_cost, SUM(ics.enhanced_count) AS enhanced_count,
		SUM(ics.enhanced_cost) AS enhanced_cost, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) + SUM(ics.enhanced_cost)
		AS grandtotal_cost
	      FROM salesperson 
                INNER JOIN accountgroup ON salesperson.salespid = accountgroup.salespid 
                INNER JOIN icsummary_daily ics ON accountgroup.acctgrpid = ics.acctgrpid
	      WHERE (YEAR(ics.[date]) = $year) AND (salesperson.salespid IN ($salespid_list))
	      GROUP BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)
	      ORDER BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)";

    } else {
      return Array();
    }

    return $this->query($sql);
  }

  function Usage_YTDbyWeek($year, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    if ($level_type == 'reseller' && is_null($reseller_ids)){
      $sql = "SELECT * FROM icsummary_daily_total_ytd WHERE date like '%/$year' ORDER BY date;";
    }elseif ($level_type == 'reseller'){
      $sql = "SELECT TOP 100 PERCENT { fn WEEK(ics.[date]) } AS date, SUM(ics.rl_confcount) AS rl_count, SUM(ics.rl_minutes) AS rl_minutes, SUM(ics.rl_cost) AS rl_cost,
	        SUM(ics.oa_confcount) AS oa_count, SUM(ics.oa_minutes) AS oa_minutes, SUM(ics.oa_cost) AS oa_cost, SUM(ics.wb_confcount) AS wb_count,
		SUM(ics.wb_minutes) AS wb_minutes, SUM(ics.wb_cost) AS wb_cost, SUM(ics.rl_confcount) + SUM(ics.oa_confcount) + SUM(ics.wb_confcount) AS total_count, 
                SUM(ics.rl_minutes) + SUM(ics.oa_minutes) + SUM(ics.wb_minutes) AS total_minutes, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) AS total_cost, 
                SUM(ics.enhanced_count) AS enhanced_count, SUM(ics.enhanced_cost) AS enhanced_cost, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) + SUM(ics.enhanced_cost) AS grandtotal_cost
	      FROM salesperson 
              INNER JOIN accountgroup ON salesperson.salespid = accountgroup.salespid 
              INNER JOIN icsummary_daily ics ON accountgroup.acctgrpid = ics.acctgrpid
	      WHERE (YEAR(ics.[date]) = $year) AND (salesperson.resellerid IN ($reseller_list))
	      GROUP BY { fn WEEK(ics.[date]) }
	      ORDER BY { fn WEEK(ics.[date]) }";

    }elseif ($level_type == 'salesperson'){
      $sql = "SELECT TOP 100 PERCENT { fn WEEK(ics.[date]) } AS date, SUM(ics.rl_confcount) AS rl_count, SUM(ics.rl_minutes) AS rl_minutes, SUM(ics.rl_cost) AS rl_cost,
		SUM(ics.oa_confcount) AS oa_count, SUM(ics.oa_minutes) AS oa_minutes, SUM(ics.oa_cost) AS oa_cost, SUM(ics.wb_confcount) AS wb_count,
		SUM(ics.wb_minutes) AS wb_minutes, SUM(ics.wb_cost) AS wb_cost, SUM(ics.rl_confcount) + SUM(ics.oa_confcount) + SUM(ics.wb_confcount) AS total_count, 
                SUM(ics.rl_minutes) + SUM(ics.oa_minutes) + SUM(ics.wb_minutes) AS total_minutes, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) AS total_cost, 
                SUM(ics.enhanced_count) AS enhanced_count, SUM(ics.enhanced_cost) AS enhanced_cost, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) + SUM(ics.enhanced_cost) AS grandtotal_cost
	      FROM salesperson 
                INNER JOIN accountgroup ON salesperson.salespid = accountgroup.salespid 
                INNER JOIN icsummary_daily ics ON accountgroup.acctgrpid = ics.acctgrpid
	      WHERE (YEAR(ics.[date]) = $year) AND (salesperson.salespid IN ($salespid_list))
	      GROUP BY { fn WEEK(ics.[date]) }
	      ORDER BY { fn WEEK(ics.[date]) }";

    }else{
      //I have no freaking idea
      return Array();
    }

    return $this->query($sql);
  }

  function Usage_MTD($year, $month, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    if ($level_type == 'reseller' && is_null($reseller_ids)){
      $sql = "SELECT * FROM icsummary_daily_total_mtd WHERE YEAR(date) = $year AND MONTH(date) = $month ORDER BY date;";

    }elseif ($level_type == 'reseller'){
      $sql = "SELECT TOP 100 PERCENT dbo.icsummary_daily.[date], COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count,
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
		FROM dbo.salesperson 
                  INNER JOIN dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid 
                  INNER JOIN dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
		WHERE YEAR(dbo.icsummary_daily.date) = $year AND MONTH(dbo.icsummary_daily.date) = $month AND dbo.salesperson.resellerid IN ($reseller_list)
		GROUP BY dbo.icsummary_daily.[date]
		ORDER BY dbo.icsummary_daily.[date]";

    }elseif ($level_type == 'salesperson'){
      $sql = "SELECT TOP 100 PERCENT dbo.icsummary_daily.[date], COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count,
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
	      FROM dbo.salesperson 
                INNER JOIN dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid 
                INNER JOIN dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
	      WHERE YEAR(dbo.icsummary_daily.date) = $year AND MONTH(dbo.icsummary_daily.date) = $month AND dbo.salesperson.salespid IN ($salespid_list)
	      GROUP BY dbo.icsummary_daily.[date]
	      ORDER BY dbo.icsummary_daily.[date]";

    }else{
      //I have no freaking idea
      return Array();
    }

    $myData = $this->query($sql);
    return $myData;
  }

  function Usage_Week($year, $month, $day, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    $start = date('Y-m-d 00:00:00', strtotime(sprintf("%d-%02d-%02d -1 day", $year, $month, $day)));
    $end   = date('Y-m-d 00:00:00', strtotime(sprintf("%d-%02d-%02d -7 day", $year, $month, $day)));

    $sql = "SELECT icsummary_daily.[date], COALESCE(SUM(rl_minutes), 0) AS rl_minutes,
                                           COALESCE(SUM(oa_minutes), 0) AS oa_minutes,
                                           COALESCE(SUM(wb_minutes), 0) AS wb_minutes, 
                                           COALESCE(SUM(rl_minutes), 0) + 
                                             COALESCE(SUM(oa_minutes), 0) + 
                                             COALESCE(SUM(wb_minutes), 0) AS total_minutes
            FROM icsummary_daily
            JOIN accountgroup ON accountgroup.acctgrpid = icsummary_daily.acctgrpid
            WHERE icsummary_daily.[date]<='%s' AND icsummary_daily.[date]>='%s' %s
            GROUP BY icsummary_daily.[date]
            ORDER BY icsummary_daily.[date]";

    if ($level_type == 'reseller' && is_null($reseller_ids))
      $where = '';
    elseif($level_type == 'reseller')
      $where = sprintf('AND icsummary_daily.resellerid IN (%s)', $reseller_list);
    elseif($level_type == 'salesperson')
      $where = sprintf('AND accountgroup.salespid IN (%s)', $salespid_list);
    else 
      $where = 'AND 1=0'; // catch all 

    return $this->sql($sql, $start, $end, $where);
  }

  function Usage_SingleDay($year, $month, $day, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    $where = "YEAR(date) = $year AND MONTH(date) = $month AND DAY(date) = $day";

    if ($level_type == 'reseller' && is_null($reseller_ids)){
      $where .= " AND resellerid <> 3";
    }elseif ($level_type == 'reseller'){
      $where .= " AND resellerid IN ($reseller_list)";
    }elseif ($level_type == 'salesperson'){
      $where .= " AND salespid IN ($salespid_list)";
    }else{
      //I have no freaking idea
      return Array();
    }

    $sql = "SELECT * FROM icsummary_daily_view WHERE $where;";

    $myData = $this->query($sql);

    return $myData;
  }

  function Usage_ConferenceList($year, $month, $day, $account, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    $where = "YEAR(date) = $year AND MONTH(date) = $month AND DAY(date) = $day AND acctgrpid='$account'";

    if ($level_type == 'reseller' && is_null($reseller_ids)){
      $where .= " AND resellerid <> 3";
    }elseif ($level_type == 'reseller'){
      $where .= " AND resellerid IN ($reseller_list)";
    }elseif ($level_type == 'salesperson'){
      $where .= " AND salespid IN ($salespid_list)";
    }else{
      //I have no freaking idea
      return Array();
    }

    $sql = "SELECT * FROM icbilltab_daily WHERE $where;";

    return $this->query($sql);
  }

  function Usage_ConferenceListByWeek($year, $week, $account, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    $where = "YEAR(date) = $year AND { fn WEEK([date]) } = $week AND acctgrpid='$account'";

    if ($level_type == 'reseller' && is_null($reseller_ids)){
      $where .= " AND resellerid <> 3";
    }elseif ($level_type == 'reseller'){
      $where .= " AND resellerid IN ($reseller_list)";
    }elseif ($level_type == 'salesperson'){
      $where .= " AND salespid IN ($salespid_list)";
    }else{
      //I have no freaking idea
      return Array();
    }

    $sql = "SELECT * FROM icbilltab_daily WHERE $where;";

    return $this->query($sql);
  }

  function Usage_SingleWeek($year, $week, $level_type, $reseller_ids=null, $salespids=null)
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    $where = "YEAR(date) = $year AND { fn WEEK([date]) } = $week";

    if ($level_type == 'reseller' && is_null($reseller_ids)){
      $where .= " AND icsummary_daily.resellerid <> 3";
    }elseif ($level_type == 'reseller'){
      $where .= " AND icsummary_daily.resellerid IN ($reseller_list)";
    }elseif ($level_type == 'salesperson'){
      $where .= " AND salespid IN ($salespid_list)";
    }else{
      //I have no freaking idea
      return Array();
    }

    $sql = "SELECT icsummary_daily.[date], icsummary_daily.acctgrpid AS acctgrpid, accountgroup.bcompany AS company, accountgroup.salespid,
              salesperson.resellerid, COALESCE (SUM(icsummary_daily.rl_confcount), 0) AS rl_count, COALESCE (SUM(icsummary_daily.rl_minutes), 0)
              AS rl_minutes, COALESCE (SUM(icsummary_daily.rl_cost), 0) AS rl_cost, COALESCE (SUM(icsummary_daily.oa_confcount), 0) AS oa_count,
              COALESCE (SUM(icsummary_daily.oa_minutes), 0) AS oa_minutes, COALESCE (SUM(icsummary_daily.oa_cost), 0) AS oa_cost,
              COALESCE (SUM(icsummary_daily.wb_confcount), 0) AS wb_count, COALESCE (SUM(icsummary_daily.wb_minutes), 0) AS wb_minutes,
              COALESCE (SUM(icsummary_daily.wb_cost), 0) AS wb_cost, 
              COALESCE (SUM(icsummary_daily.rl_confcount), 0) + COALESCE (SUM(icsummary_daily.oa_confcount), 0) + COALESCE (SUM(icsummary_daily.wb_confcount), 0) AS total_count,
              COALESCE (SUM(icsummary_daily.rl_minutes), 0) + COALESCE (SUM(icsummary_daily.oa_minutes), 0)
                + COALESCE (SUM(icsummary_daily.wb_minutes), 0) AS total_minutes, 
              COALESCE (SUM(icsummary_daily.rl_cost), 0) + COALESCE (SUM(icsummary_daily.oa_cost), 0) + COALESCE (SUM(icsummary_daily.wb_cost), 0) AS total_cost,
              COALESCE (SUM(icsummary_daily.enhanced_count), 0) AS enhanced_count, COALESCE (SUM(icsummary_daily.enhanced_cost), 0) AS enhanced_cost,
              COALESCE (SUM(icsummary_daily.rl_cost), 0) + COALESCE (SUM(icsummary_daily.oa_cost), 0) + COALESCE (SUM(icsummary_daily.wb_cost), 0)
                + COALESCE (SUM(icsummary_daily.enhanced_cost), 0) AS grandtotal_cost
	    FROM  salesperson 
              INNER JOIN accountgroup ON salesperson.salespid = accountgroup.salespid 
              INNER JOIN icsummary_daily ON accountgroup.acctgrpid = icsummary_daily.acctgrpid
	    WHERE $where
	    GROUP BY icsummary_daily.[date], icsummary_daily.acctgrpid, accountgroup.bcompany, accountgroup.salespid, salesperson.resellerid
	    ORDER BY icsummary_daily.[date], icsummary_daily.acctgrpid;";

    return $this->query($sql);
  }

  function BuildChartData($rawData, $dateFormat=null, $model=null)
  
  //DC Might need to change this back
  {
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
	$theDate = date($dateFormat,strtotime($dataLine[0]['date']));
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

  function TopX($type, $sort_field, $sort_order, $count, $year, $month, $level_type, $reseller_ids=null, $salespids=null)
  {
    if(is_null($reseller_ids))
      $where = ' AND icsd.resellerid=1';
    else
      $where = sprintf(' AND icsd.resellerid IN (%s)', implode(',', $reseller_ids));

    if($salespids) 
      $where .= sprintf(' AND salesperson.salespid IN (%s)', implode(',', $salespids));

    $order = $sort_field . ' ' . $sort_order;
    if($sort_field != 'delta')
      $order .= ', delta';

    $this_month_start = sprintf('%s-%02d-01 ', $year, $month);
    $this_month_end   = sprintf('%s-%02d-%d', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));

    $prev_month_ts    = strtotime(sprintf('%s-%02d-01 -1 month', $year, $month));
    $prev_year        = date('Y', $prev_month_ts);
    $prev_month       = date('m', $prev_month_ts);
    
    $prev_month_start = sprintf('%s-%02d-01', $prev_year, $prev_month);
    $prev_month_end   = sprintf('%s-%02d-%d', $prev_year, $prev_month, cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year));

    $sql = "SELECT TOP {$count} resellerid, salesperson_id, acctgrpid, contact, phone, email, salesperson_name, company, current_data, previous_data,
                   (current_data - previous_data) AS delta, 
                   COALESCE(CONVERT(FLOAT, (current_data - previous_data)) / CONVERT(FLOAT, NULLIF(previous_data, 0)), 1.0) * 100.0 AS percent_change
            FROM (
              SELECT salesperson.resellerid AS resellerid, 
              	 salesperson.salespid AS salesperson_id, 
            	 icsd.acctgrpid AS acctgrpid, 
                 accountgroup.bcontact AS contact, 
                 accountgroup.phone AS phone,
                 accountgroup.email AS email,
            	 salesperson.name AS salesperson_name, 
            	 accountgroup.bcompany AS company, 
            
                 COALESCE((SELECT SUM({$type})
            	    FROM icsummary_daily 
            	    WHERE date >= '%s' AND date <= '%s' AND acctgrpid = icsd.acctgrpid), 0) AS current_data, 

                 COALESCE((SELECT SUM({$type})
                        FROM icsummary_daily 
            	        WHERE date >= '%s' AND date <= '%s' AND acctgrpid = icsd.acctgrpid), 0) AS previous_data
            
              FROM icsummary_daily icsd 
              INNER JOIN accountgroup ON icsd.acctgrpid = accountgroup.acctgrpid 
              INNER JOIN salesperson ON accountgroup.salespid = salesperson.salespid 
              WHERE (icsd.[date] >= '%s') AND (icsd.[date] <= '%s') {$where}
              GROUP BY salesperson.resellerid, salesperson.salespid,  salesperson.name, icsd.acctgrpid, accountgroup.bcompany,
                accountgroup.bcontact, accountgroup.phone, accountgroup.email
            ) AS X
            ORDER BY {$order}";

    return $this->sql($sql, $this_month_start, $this_month_end, $prev_month_start, $prev_month_end, $prev_month_start, $this_month_end);
  }

  function TopSales($type, $sort_field, $sort_order, $range, $level_type, $reseller_ids=null, $salespids=null) 
  {
    $reseller_list = $reseller_ids ? implode(',', $reseller_ids) : null;
    $salespid_list = $salespids ? implode(',', $salespids) : null;

    // Measure options = cost|minutes
    // Order options = current_data|delta|acctgrpid|resellerid|salesperson_id|salesperson_name|company
    $range_double = $range * 2;
    if($level_type == 'reseller' && $salespid_list) {
      $where = "AND sp.resellerid=1 AND salesperson.salespid IN ($salespid_list)";
    } elseif($level_type == 'reseller' && is_null( $reseller_ids)) {
      $where = 'AND sp.resellerid=1';
    } elseif($level_type == 'reseller') {
      $where = "AND sp.resellerid IN ($reseller_list)";
    } else {
      // Not Implemented
      return Array();
    }
		
    $order = $sort_field . ' ' . $sort_order;
    if($sort_field != 'delta')
      $order .= ', delta';
		
    $sql = "SELECT resellerid, salesperson_id, salesperson_name, current_data, previous_data, (current_data-previous_data) AS delta, 
            COALESCE(CONVERT(FLOAT, (current_data - previous_data)) / CONVERT(FLOAT, NULLIF(previous_data, 0)), 1.0) * 100.0 AS percent_change,
            (SELECT COUNT(*)
            	FROM         dbo.accountgroup_view INNER JOIN
	                     dbo.myca_diff_log ON dbo.myca_diff_log.entity = 'Account' AND dbo.myca_diff_log.op = 0 AND 
	                     dbo.myca_diff_log.object_id = dbo.accountgroup_view.acctgrpid LEFT OUTER JOIN
	                     dbo.icsummary_daily ON dbo.accountgroup_view.acctgrpid = dbo.icsummary_daily.acctgrpid
	        WHERE dbo.accountgroup_view.salespid=salesperson_id AND dbo.myca_diff_log.created BETWEEN CURRENT_TIMESTAMP - {$range} AND CURRENT_TIMESTAMP
	    ) AS newaccounts
	
            FROM (
                SELECT sp.resellerid AS resellerid, sp.salespid AS salesperson_id, MIN(sp.name) AS salesperson_name,
    	          COALESCE((SELECT SUM({$type})
                            FROM icsummary_daily 
                              INNER JOIN accountgroup ON icsummary_daily.acctgrpid = accountgroup.acctgrpid 
                              INNER JOIN salesperson ON accountgroup.salespid = salesperson.salespid 
                            WHERE date <= CURRENT_TIMESTAMP AND date >= CURRENT_TIMESTAMP - $range AND salesperson.salespid = sp.salespid), 0) AS current_data,
      	          COALESCE((SELECT SUM({$type})
                            FROM icsummary_daily 
                              INNER JOIN accountgroup ON icsummary_daily.acctgrpid = accountgroup.acctgrpid 
                              INNER JOIN salesperson ON accountgroup.salespid = salesperson.salespid 
                            WHERE date <= CURRENT_TIMESTAMP - %d AND date >= CURRENT_TIMESTAMP - %d AND salesperson.salespid = sp.salespid), 0) AS previous_data
    	        FROM icsummary_daily icsd 
                INNER JOIN accountgroup ON icsd.acctgrpid = accountgroup.acctgrpid 
                INNER JOIN salesperson sp ON accountgroup.salespid = sp.salespid
    	        WHERE (icsd.[date] <= CURRENT_TIMESTAMP) AND (icsd.[date] >= CURRENT_TIMESTAMP - %d) {$where}
    	        GROUP BY sp.resellerid, sp.salespid) AS X
    	    ORDER BY {$order}";

    return $this->sql($sql, $range, 2*$range, $range);
  }

  function ResellerYears()
  {
    $rv = $this->sql('SELECT DISTINCT YEAR(date) AS year FROM icsummary_daily_ytd_byReseller');
    return pluck($rv, 'year');
  }

  function Reseller_YTD($year, $month=null, $resellerid=null)
  {
    if($resellerid) {
      if($month==0){
	$sql = "SELECT * FROM icsummary_daily_ytd_byReseller WHERE YEAR(date)=$year AND resellerid=$resellerid ORDER BY date,name;";
      } else {
	$sql = "SELECT * FROM icsummary_daily_ytd_byReseller WHERE YEAR(date)=$year AND MONTH(date)=$month AND resellerid=$resellerid ORDER BY name, date;";
      }
    } else {
      if($month==NULL){
	$sql = "SELECT * FROM icsummary_daily_ytd_totalResellers WHERE YEAR(date)=$year ORDER BY date;";
      } elseif($month==0) {
	$sql = "SELECT * FROM icsummary_daily_ytd_byReseller WHERE YEAR(date)=$year ORDER BY  rl_minutes+oa_minutes+wb_minutes desc;";
      } else {
	$sql = "SELECT * FROM icsummary_daily_ytd_byReseller WHERE YEAR(date)=$year AND MONTH(date)=$month ORDER BY  rl_minutes+oa_minutes+wb_minutes desc;";
      }
    }

    return $this->query($sql);
  }
}