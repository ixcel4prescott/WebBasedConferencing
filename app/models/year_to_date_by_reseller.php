<?
require_once(COMMON_DIR . '/models/report_base.php');

class YearToDateByReseller extends reportBase
{
  var $useTable    = 'year_to_date_by_reseller';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'resellerid';

  var $hasOne = array('Reseller' => Array( 'className'  => 'Reseller',
  						'foreignKey' => 'resellerid'));
  						
  function getYearToDateByReseller($Resellerid, $year){
    $reselleridIn = "'$Resellerid[0]'";
    foreach ($Resellerid as $value){
      $reselleridIn = $reselleridIn.", '$value'";
    }

    $sql = "SELECT [YearToDateByReseller].[date] as date,
      Sum([YearToDateByReseller].[reservationless_minutes]) as reservationless_minutes,
      Sum([YearToDateByReseller].[reservationless_cost]) as reservationless_cost,
      Sum([YearToDateByReseller].[operator_assisted_minutes]) as operator_assisted_minutes,
      Sum([YearToDateByReseller].[operator_assisted_cost]) as operator_assisted_cost,
      Sum([YearToDateByReseller].[web_minutes]) as web_minutes,
      Sum([YearToDateByReseller].[web_cost]) as web_cost,
      Sum([YearToDateByReseller].[flat_count]) as flat_count,
      Sum([YearToDateByReseller].[flat_cost]) as flat_cost,
      Sum([YearToDateByReseller].[enhanced_service_count]) as enhanced_service_count,
      Sum([YearToDateByReseller].[enhanced_service_cost]) as enhanced_service_cost,
      Sum([YearToDateByReseller].[other_count]) as other_count,
      Sum([YearToDateByReseller].[other_cost]) as other_cost,
      Sum([YearToDateByReseller].[total_minutes]) as total_minutes,
      Sum([YearToDateByReseller].[total_cost]) as total_cost
      FROM [year_to_date_by_reseller] AS [YearToDateByReseller]
      WHERE [YearToDateByReseller].[DATE] BETWEEN '01/01/$year 00:00:00' AND '12/31/$year 23:59:59'
      AND [YearToDateByReseller].[resellerid] IN (".$reselleridIn.")
      group by [YearToDateByReseller].[date]
      ORDER BY [YearToDateByReseller].[DATE] ASC";
    $rawData = $this->openSQLSession($sql);

    $refinedData = Array();
    foreach($rawData as $rowNum =>$row){
      $refinedData[$rowNum]['YearToDateByReseller'] = $row[0];
    }
    return $refinedData;
  }

  function getYearToDateAllReseller($year){
    $sql = "SELECT [YearToDateByReseller].[date] as date,
      Sum([YearToDateByReseller].[reservationless_minutes]) as reservationless_minutes,
      Sum([YearToDateByReseller].[reservationless_cost]) as reservationless_cost,
      Sum([YearToDateByReseller].[operator_assisted_minutes]) as operator_assisted_minutes,
      Sum([YearToDateByReseller].[operator_assisted_cost]) as operator_assisted_cost,
      Sum([YearToDateByReseller].[web_minutes]) as web_minutes,
      Sum([YearToDateByReseller].[web_cost]) as web_cost,
      Sum([YearToDateByReseller].[flat_count]) as flat_count,
      Sum([YearToDateByReseller].[flat_cost]) as flat_cost,
      Sum([YearToDateByReseller].[enhanced_service_count]) as enhanced_service_count,
      Sum([YearToDateByReseller].[enhanced_service_cost]) as enhanced_service_cost,
      Sum([YearToDateByReseller].[other_count]) as other_count,
      Sum([YearToDateByReseller].[other_cost]) as other_cost,
      Sum([YearToDateByReseller].[total_minutes]) as total_minutes,
      Sum([YearToDateByReseller].[total_cost]) as total_cost
      FROM [year_to_date_by_reseller] AS [YearToDateByReseller]
      WHERE [YearToDateByReseller].[DATE] BETWEEN '01/01/$year 00:00:00' AND '12/31/$year 23:59:59'
      group by [YearToDateByReseller].[date]
      ORDER BY [YearToDateByReseller].[DATE] ASC";
    $rawData = $this->openSQLSession($sql);

    $refinedData = Array();
    foreach($rawData as $rowNum =>$row){
      $refinedData[$rowNum]['YearToDateByReseller'] = $row[0];
    }
    return $refinedData;
  }
}