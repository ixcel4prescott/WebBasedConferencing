<?
require_once(COMMON_DIR . '/models/report_base.php');

class TopX extends reportBase
{
  var $useTable    = 'top_x';
  var $useDbConfig = 'billing';
}