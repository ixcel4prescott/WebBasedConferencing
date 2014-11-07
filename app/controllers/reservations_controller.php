<?

class ReservationsController extends AppController
{
  var $uses     = Array('Reservation', 'ReservationOperator', 'Operator', 'Room');
  var $helpers  = Array('Text');
/*
Possibly not used DC -2012/09/13
  function index()
  {
    $year  = !empty($_GET['year']) ? $_GET['year'] : date('Y');
    $month = !empty($_GET['month']) ? $_GET['month'] : date('m');

    $this->set('year', $year);
    $this->set('month', $month);

    $days_in_month  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $start_of_month = mktime(0, 0, 0, $month, 1, $year);
    $end_of_month   = mktime(0, 0, 0, $month, $days_in_month, $year); 

    $start = date('Y-m-d H:i:s', strtotime(sprintf('-%d days', date('w', $start_of_month)), $start_of_month));
    $this->set('start', $start);
    
    $end = date('Y-m-d H:i:s', strtotime(sprintf('+%d days', 6-date('w', $end_of_month)), $end_of_month));
    $this->set('end', $end);

    // Other filters
    $company = !empty($_GET['company']) ? $_GET['company'] : '';
    $this->set('company', $company);

    $date_scheduled = !empty($_GET['date_scheduled']) ? $_GET['date_scheduled'] : '';
    $this->set('date_scheduled', $date_scheduled);
    
    $statuses = !empty($_GET['statuses']) ? $_GET['statuses'] : Array('Active', 'Billable', 'Billed', 'Complete');
    $this->set('statuses', $statuses);

    $operators = !empty($_GET['operators']) ? $_GET['operators'] : Array();
    $this->set('operators', $operators);
    
    $services = !empty($_GET['services']) ? $_GET['services'] : Array();
    $this->set('services', $services);

    $date_scheduled = !empty($_GET['date_scheduled']) ? $_GET['date_scheduled'] : '';
    $this->set('date_scheduled', $date_scheduled);
       
    // Data for selects
    $this->set('operator_list',    $this->Operator->buildOperatorList());
    $this->set('status_list',      $this->Reservation->statuses);
    $this->set('service_list',     $this->Reservation->services);
    $this->set('num_reservations', $this->Reservation->numReservationsBetween($start, $end, $statuses));
    $this->set('reservations',     $this->Reservation->reservationsBetween($start, $end, $company, $statuses, 
									   $operators, $services, $date_scheduled));
  }
  
  function day($year=null, $month=null, $day=null)
  {
    if(!$year) 
      $year = date('Y');

    if(!$month) 
      $month = date('m');

    if(!$day) 
      $day = date('d');
    
    $this->set('reservations', $this->Reservation->getReservationsByHour($year, $month, $day));
  }

  function view($reservation_id=null)
  {
    if($reservation = $this->Reservation->read(null, $reservation_id)) { 
      $this->set('reservation', $reservation);
      $this->set('operators', 
		 $this->ReservationOperator->findAll(Array('ReservationGroupID' => $reservation['Reservation']['ReservationGroupID']), 
						     null, 'OperatorOrder'));

      $room = null;
      if(!empty($reservation['Reservation']['ConfirmNumber']))
	$room = $this->Room->read(null, $reservation['Reservation']['ConfirmNumber']);
	  
      $this->set('room', $room);

    } else {
      $this->Session->setFlash('Reservation not found');
      $this->redirect('/reservations');
    }
  }

  function listindex($year=null, $month=null, $day=null)
  {
    if(!$year) 
      $year = date('Y');

    if(!$month) 
      $month = date('m');

    if(!$day) 
      $day = date('d');

    if(isset($_GET['lite'])) {
      $this->set('litelayout', 1);
      $this->layout = 'reservationlist';
    } else {
      $this->set('litelayout', 0);
    }
  	
    $this->set('myDate', "$month/$day/$year");
    $this->set('myYear', $year);
    $this->set('myMonth', $month);
    $this->set('myDay', $day);
 	
    $this->set('Reservations', $this->Reservation->getReservations($year, $month, $day));
  }  

  function utilization()
  {
    $year     = !empty($_GET['year'])     ? $_GET['year']     : date('Y');
    $month    = !empty($_GET['month'])    ? $_GET['month']    : date('m');
    $bridgeid = !empty($_GET['bridgeid']) ? $_GET['bridgeid'] : null;

    $this->set('year',       $year);
    $this->set('month',      $month);    
    $this->set('bridgeid',   $bridgeid);    
    $this->set('total_days', cal_days_in_month(CAL_GREGORIAN, $month, $year));    
    $this->set('data',       $this->Reservation->utilization($year, $month, $bridgeid));
  }
*/
}
