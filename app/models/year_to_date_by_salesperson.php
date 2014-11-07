<?
require_once(COMMON_DIR . '/models/report_base.php');

class YearToDateBySalesperson extends reportBase
{
  var $useTable    = 'year_to_date_by_salesperson';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'salespid';
  
  function getYearToDateBySalesperson($Salespid, $year){
  $salespidIn = "'$Salespid[0]'";
  foreach ($Salespid as $value){
    $salespidIn = $salespidIn.", '$value'";
  }

  $sql = "SELECT [YearToDateBySalesperson].[date] as date,
  Sum([YearToDateBySalesperson].[reservationless_minutes]) as reservationless_minutes,
  Sum([YearToDateBySalesperson].[reservationless_cost]) as reservationless_cost,
  Sum([YearToDateBySalesperson].[operator_assisted_minutes]) as operator_assisted_minutes,
  Sum([YearToDateBySalesperson].[operator_assisted_cost]) as operator_assisted_cost,
  Sum([YearToDateBySalesperson].[web_minutes]) as web_minutes,
  Sum([YearToDateBySalesperson].[web_cost]) as web_cost,
  Sum([YearToDateBySalesperson].[flat_count]) as flat_count,
  Sum([YearToDateBySalesperson].[flat_cost]) as flat_cost,
  Sum([YearToDateBySalesperson].[enhanced_service_count]) as enhanced_service_count,
  Sum([YearToDateBySalesperson].[enhanced_service_cost]) as enhanced_service_cost,
  Sum([YearToDateBySalesperson].[other_count]) as other_count,
  Sum([YearToDateBySalesperson].[other_cost]) as other_cost,
  Sum([YearToDateBySalesperson].[total_minutes]) as total_minutes,
  Sum([YearToDateBySalesperson].[total_cost]) as total_cost
FROM [year_to_date_by_salesperson] AS [YearToDateBySalesperson]
WHERE [YearToDateBySalesperson].[DATE] BETWEEN '01/01/$year 00:00:00' AND '12/31/$year 23:59:59'
AND [YearToDateBySalesperson].[salespid] IN (".$salespidIn.")
group by [YearToDateBySalesperson].[date]
ORDER BY [YearToDateBySalesperson].[DATE] ASC";
    $rawData = $this->openSQLSession($sql);
    
    $refinedData = Array();
    foreach($rawData as $rowNum =>$row){
      $refinedData[$rowNum]['YearToDateBySalesperson'] = $row[0];
    }
    return $refinedData;
  }
}