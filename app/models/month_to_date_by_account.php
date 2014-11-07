<?
require_once(COMMON_DIR . '/models/report_base.php');

class MonthToDateByAccount extends reportBase
{
  var $useTable    = 'month_to_date_by_account';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'acctgrpid';
}