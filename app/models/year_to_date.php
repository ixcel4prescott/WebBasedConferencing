<?
require_once(COMMON_DIR . '/models/report_base.php');

class YearToDate extends reportBase
{
  var $useTable    = 'year_to_date';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'date';
}