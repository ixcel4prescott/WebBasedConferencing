<?
require_once(COMMON_DIR . '/models/report_base.php');

class MonthToDateBySalesperson extends reportBase
{
  var $useTable    = 'month_to_date_by_salesperson';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'salespid';
  
  function getMonthToDateBySalesperson($Salespid, $monthStart, $monthEnd){
    $salespidIn = "'$Salespid[0]'";
    foreach ($Salespid as $value){
      $salespidIn = $salespidIn.", '$value'";
    }

    $sql = "SELECT [MonthToDateBySalesperson].[date] as date,
      Sum([MonthToDateBySalesperson].[reservationless_minutes]) as reservationless_minutes,
      Sum([MonthToDateBySalesperson].[reservationless_cost]) as reservationless_cost,
      Sum([MonthToDateBySalesperson].[operator_assisted_minutes]) as operator_assisted_minutes,
      Sum([MonthToDateBySalesperson].[operator_assisted_cost]) as operator_assisted_cost,
      Sum([MonthToDateBySalesperson].[web_minutes]) as web_minutes,
      Sum([MonthToDateBySalesperson].[web_cost]) as web_cost,
      Sum([MonthToDateBySalesperson].[flat_count]) as flat_count,
      Sum([MonthToDateBySalesperson].[flat_cost]) as flat_cost,
      Sum([MonthToDateBySalesperson].[enhanced_service_count]) as enhanced_service_count,
      Sum([MonthToDateBySalesperson].[enhanced_service_cost]) as enhanced_service_cost,
      Sum([MonthToDateBySalesperson].[other_count]) as other_count,
      Sum([MonthToDateBySalesperson].[other_cost]) as other_cost,
      Sum([MonthToDateBySalesperson].[total_minutes]) as total_minutes,
      Sum([MonthToDateBySalesperson].[total_cost]) as total_cost
      FROM [month_to_date_by_salesperson] AS [MonthToDateBySalesperson]
      WHERE [MonthToDateBySalesperson].[DATE] BETWEEN '".$monthStart."' AND '".$monthEnd."'
      AND [MonthToDateBySalesperson].[salespid] IN (".$salespidIn.")
      group by [MonthToDateBySalesperson].[date]
      ORDER BY [MonthToDateBySalesperson].[DATE] ASC";
    $rawData = $this->openSQLSession($sql);
    $refinedData = Array();
    foreach($rawData as $rowNum =>$row){
      $refinedData[$rowNum]['MonthToDateBySalesperson'] = $row[0];
    }
    return $refinedData;
  }
}