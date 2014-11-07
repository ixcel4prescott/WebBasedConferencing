<?
require_once(COMMON_DIR . '/models/report_base.php');

class YearToDateByAccount extends reportBase
{
  var $useTable    = 'year_to_date_by_account';
  var $useDbConfig = 'billing';
  var $primaryKey  = 'acctgrpid';
  
  var $belongsTo = Array( 'Account' => Array( 'className'  => 'Account',
                       'foreignKey' => 'acctgrpid' ));
}