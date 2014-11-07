<?
require_once(COMMON_DIR . '/models/report_base.php');

class MonthToDateByReseller extends reportBase
{
  var $useTable    = 'month_to_date_by_reseller';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'resellerid';

  var $hasOne = array('Reseller' => Array( 'className'  => 'Reseller',
  						'foreignKey' => 'resellerid'));

  function getMonthToDateByReseller($Resellerid, $monthStart, $monthEnd){
    $reselleridIn = "'$Resellerid[0]'";
    foreach ($Resellerid as $value){
      $reselleridIn = $reselleridIn.", '$value'";
    }

    $sql = "SELECT [MonthToDateByReseller].[date] as date,
      Sum([MonthToDateByReseller].[reservationless_minutes]) as reservationless_minutes,
      Sum([MonthToDateByReseller].[reservationless_cost]) as reservationless_cost,
      Sum([MonthToDateByReseller].[operator_assisted_minutes]) as operator_assisted_minutes,
      Sum([MonthToDateByReseller].[operator_assisted_cost]) as operator_assisted_cost,
      Sum([MonthToDateByReseller].[web_minutes]) as web_minutes,
      Sum([MonthToDateByReseller].[web_cost]) as web_cost,
      Sum([MonthToDateByReseller].[flat_count]) as flat_count,
      Sum([MonthToDateByReseller].[flat_cost]) as flat_cost,
      Sum([MonthToDateByReseller].[enhanced_service_count]) as enhanced_service_count,
      Sum([MonthToDateByReseller].[enhanced_service_cost]) as enhanced_service_cost,
      Sum([MonthToDateByReseller].[other_count]) as other_count,
      Sum([MonthToDateByReseller].[other_cost]) as other_cost,
      Sum([MonthToDateByReseller].[total_minutes]) as total_minutes,
      Sum([MonthToDateByReseller].[total_cost]) as total_cost
      FROM [month_to_date_by_reseller] AS [MonthToDateByReseller]
      WHERE [MonthToDateByReseller].[DATE] BETWEEN '".$monthStart."' AND '".$monthEnd."'
      AND [MonthToDateByReseller].[resellerid] IN (".$reselleridIn.")
      group by [MonthToDateByReseller].[date]
      ORDER BY [MonthToDateByReseller].[DATE] ASC";
    $rawData = $this->openSQLSession($sql);
    $refinedData = Array();
    foreach($rawData as $rowNum =>$row){
      $refinedData[$rowNum]['MonthToDateByReseller'] = $row[0];
    }
    return $refinedData;
  }
}