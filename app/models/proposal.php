<?

class Proposal extends AppModel
{
  var $name        = 'Proposal';
  var $useTable    = 'proposals';

  var $belongsTo = Array('Salesperson' =>
      array('className' => 'Salesperson',
          'foreignKey' => 'salespid'));
}
