<?

class Cdr extends AppModel
{
  var $useTable    = 'CDRs';
  var $useDbConfig = 'billing';

  var $belongsTo = Array('Account' => Array('className' => 'AccountSummary',
        'foreignKey' => 'acctgrpid', 
        'associationForeignKey' => 'acctgrpid'));
}
