<?

class HomeController extends AppController
{
  var $uses = Array('Room', 'RequestView', 'Reports', 'Reservation');
  
  function index()
  {
    $user = $this->Session->read('User');

    $this->set('user', $user);

    if($user['User']['level_type'] == 'salesperson' || $user['User']['level_type'] == 'reseller')
      $this->set('data', $this->Reports->Usage_Week(date('Y'), date('m'), date('d'), $user['User']['level_type'], 
						    $user['Resellers'], $user['Salespersons']));
    else
      $this->set('data', null);
    
    $today   = mktime(0, 0, 0, date('m'), date('d'), date('Y')); 
    $weekday = date('w');
    $start   = date('Y-m-d H:i:s', strtotime(sprintf('-%d days', $weekday), $today));
    $end     = date('Y-m-d H:i:s', strtotime(sprintf('+%d days', 6-$weekday), $today));

    $this->set('start', $start);

    if($user['User']['ic_employee'])
      $this->set('reservations', $this->Reservation->operatorSchedule($start, $end, $user['User']['name']));
    else 
      $this->set('reservations', null);

    $this->set('requests', $this->RequestView->findAll(Array('creator' => $user['User']['id']), null, 'created DESC', 10));

    $this->set('changelog', file_exists(CHANGELOG_PATH) ? fopen(CHANGELOG_PATH, 'r') : null);
  }
  
  function check_code()
  {
    $this->layout = 'ajax';
    Configure::write('debug', 0);

    $rv = false;
    
    if(!empty($this->data['Room']['code']))
      $rv = $this->Room->isCodeAvailable($this->data['Room']['code']);

    $this->set('rv', $rv);
  }
}