<?

class FreeTrial extends AppModel
{
  var $name        = 'FreeTrial';
  var $useTable    = 'free_trials';
  //Get the salesperson as well.
  var $recursive =2;

  var $belongsTo = Array('Proposal' =>
      array('className' => 'Proposal',
          'foreignKey' => 'proposal_id'),
      'Room' => array('className' => 'Room',
          'foreignKey' => 'audio_accountid')
  );
}
