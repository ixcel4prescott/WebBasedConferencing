<?

class Codr extends AppModel
{
  var $useTable    = 'CODRs';
  var $useDbConfig = 'billing';

  var $belongsTo = Array('Account' => Array('className' => 'AccountSummary',
        'foreignKey' => 'acctgrpid', 
        'associationForeignKey' => 'acctgrpid'));
}
