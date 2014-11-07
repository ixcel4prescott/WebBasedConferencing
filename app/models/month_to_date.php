<?
require_once(COMMON_DIR . '/models/report_base.php');

class MonthToDate extends reportBase
{
  var $useTable    = 'month_to_date';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'date';
}