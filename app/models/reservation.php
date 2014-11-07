<?

class Reservation extends AppModel
{
  var $name       = 'Reservation';
  var $useTable   = 'Reservation';
  var $primaryKey = 'ReservationID';

  var $statuses = Array('Active', 'Billable', 'Billed', 'Cancelled', 'Complete', 'Inqueue', 'Modified');
  var $services = Array('OpAssistIn'              => 'OpAssist Dial-In', 
			'OpAssistOut'             => 'OpAssist Dial-Out',
			'Enhanced'                => 'Enhanced Service', 
			'Event'                   => 'Event', 
			'AutoEvent'               => 'Auto-Event',
			'Automated'               => 'Automated',
			'Merge'                   => 'Merge',
			'Playback'                => 'Playback',
			'LinkLine'                => 'LinkLine',
			'Presentation'            => 'Presentation',				 
			'QandA'                   => 'Q & A',
			'Polling'                 => 'Polling',
			'Lock'                    => 'Security/Conference Lock',
			'OpMonitoring'            => 'Operator Monitoring',
			'Communications'          => 'Communications Line',
			'SubConference'           => 'Sub-Conference',
			'Music'                   => 'Music-on-Hold',
			'RollCall'                => 'Roll Call',
			'Tones'                   => 'Entry/Exit Tones',
			'PasswordProtect'         => 'Password Protection',
			'DigitalReplay'           => 'Digital Replay',
			'LiveReport'              => 'Participation Report', 
			'Transcription'           => 'Transcription',
			'WavFile'                 => 'Wav File', 
			'WebRecording'            => 'Web Recording', 
			'AudioEditing'            => 'Audio Editng', 
			'BrandedDialIn'           => 'Branded Dial-In',
			'MakeTape'                => 'Tape', 
			'CD'                      => 'CD', 
			'MP3'                     => 'MP3', 
			'WebRecordingEditing'     => 'Web Recording Editing', 
			'SetupFee'                => 'Set-Up Fee', 
			'BrandedLogin'            => 'Branded Log-in Page', 
			'WebCast'                 => 'Web Cast', 
			'EMSRegistration'         => 'EMS Registration Package', 
			'EMSHTML'                 => 'EMS HTML & Email', 
			'EMSBlastEmail'           => 'EMS Blast Email', 
			'EMSReminder'             => 'EMS Reminder Email',
			'EMSRegistranProcessing'  => 'EMS Registrant Processing', 
			'EMSContentEditing'       => 'EMS Content Editing', 
			'EMSPostConferenceSurvey' => 'EMS Post-Conference Survey' );

  function getReservations($year, $month, $day) 
  {
    $sql = 'SELECT Reservation.*, CONVERT(varchar(5),Reservation.ConferenceTime,8) AS fConferenceTime, ReservationOperators.OperatorName, ReservationOperators.Role
	    FROM Reservation WITH (NOLOCK) 
            LEFT OUTER JOIN ReservationOperators WITH (NOLOCK) ON Reservation.ReservationGroupID = ReservationOperators.ReservationGroupID
	    WHERE Reservation.ReservationStatus IN (\'Active\', \'Billable\', \'Billed\', \'Complete\') AND 
              YEAR(Reservation.ConferenceDate)=%d AND MONTH(Reservation.ConferenceDate)=%d AND DAY(Reservation.ConferenceDate)=%d
	    ORDER BY ConferenceDate, fConferenceTime, OperatorName, CompanyName, Topic';
		
    return $this->sql($sql, $year, $month, $day);
  }

  function getReservationsByHour($year, $month, $day)
  {
    $reservations = Array();

    foreach($this->getReservations($year, $month, $day) as $r)
      $reservations[date('H', strtotime($r['fConferenceTime']))][] = $r;

    return $reservations;
  }

  function buildFilter($start, $end, $company, $statuses, $operators, $services, $date_scheduled) 
  {
    $parts = Array(sprintf("ConferenceDate>='%s'",$this->escape($start)),
		   sprintf("ConferenceDate<='%s'", $this->escape($end)));

    if($company)
      $parts[] = sprintf("(CompanyName LIKE '%%%s%%' OR AccountNumber='%s')", $this->escape($company), $this->escape($company));

    if($statuses) {      
      $q_statuses = Array();
      foreach($statuses as $s)
	$q_statuses[] = sprintf("'%s'", $this->escape($s));

      $parts[] = sprintf("ReservationStatus IN (%s)", implode(',', $q_statuses));
    }

    if($operators) {
      $q_operators = Array();
      foreach($operators as $s)
	$q_operators[] = sprintf("'%s'", $this->escape($s));

      $parts[] = sprintf("OperatorName IN (%s)", implode(',', $q_operators));      
    }
    
    if($services) { 
      $q_services = Array();
      foreach($services as $s)
	$q_services[] = sprintf("%s=1", $s);

      $parts[] = '(' . implode(' OR ', $q_services) . ')';
    }
    
    if($date_scheduled) {
      $parts[]= sprintf("DateScheduled='%s 00:00:00'", $this->escape($date_scheduled));
    }

    return implode(' AND ', $parts);
  }

  function numReservationsBetween($start, $end, $statuses)
  {
    $sql = 'SELECT ConferenceDate AS day, COUNT(*) AS num_reservations
            FROM Reservation WITH (NOLOCK)
            LEFT OUTER JOIN ReservationOperators WITH (NOLOCK) ON ReservationOperators.ReservationGroupID = Reservation.ReservationGroupID
            WHERE %s
            GROUP BY ConferenceDate
            ORDER BY ConferenceDate';

    $rv = Array();
    foreach($this->query(sprintf($sql, $this->buildFilter($start, $end, null, $statuses, null, null, null))) as $i)
      $rv[strtotime($i[0]['day'])] = $i[0]['num_reservations'];

    return $rv;
  }

  function reservationsBetween($start, $end, $company, $statuses, $operators, $services, $date_scheduled)
  {
    $sql = 'SELECT ReservationID, CompanyName, AccountNumber, ContactName, ConferenceDate, 
                   CONVERT(VARCHAR(10), ConferenceTime, 108) AS ConferenceTime, LinesReserved
            FROM Reservation WITH (NOLOCK) 
            LEFT OUTER JOIN ReservationOperators WITH (NOLOCK) ON ReservationOperators.ReservationGroupID = Reservation.ReservationGroupID
            WHERE %s
            ORDER BY ConferenceTime ASC';

    if($company || $operators || $services || $date_scheduled) 
      $where = $this->buildFilter($start, $end, $company, $statuses, $operators, $services, $date_scheduled);
    else
      $where = sprintf("%s AND LinesReserved>=50", $this->buildFilter($start, $end, null, $statuses, null, null, null));

    $rv = Array();
    foreach($this->query(sprintf($sql, $where)) as $i) {
      $conf_ts = strtotime($i[0]['ConferenceDate']);
      $rv[$conf_ts][] = $i[0];
    }

    return $rv;
  }

  function operatorSchedule($start, $end, $operator)
  {
    $name = explode(' ', $operator);

    $sql = "SELECT ReservationID, CompanyName, AccountNumber, ContactName, ConferenceDate, 
                   CONVERT(VARCHAR(10), ConferenceTime, 108) AS ConferenceTime, LinesReserved
            FROM Reservation WITH (NOLOCK) 
            JOIN ReservationOperators WITH (NOLOCK) ON ReservationOperators.ReservationGroupID = Reservation.ReservationGroupID
            WHERE ConferenceDate >= '%s' AND ConferenceDate <= '%s' AND OperatorName='%s' AND 
                  ReservationStatus IN ('Billable', 'Active', 'Billed', 'Complete')";

    $rv = Array();
    foreach($this->sql($sql, $start, $end, $name[0]) as $i) {
      $conf_ts = date('w', strtotime($i['ConferenceDate']));
      $rv[$conf_ts][] = $i;
    }

    return $rv;
  }

  function utilization($year, $month, $bridgeid)
  {
    if(!is_null($bridgeid) && $bridgeid == 3) 
      $bridge_where = "ConfirmNumber LIKE '20%%'";
    elseif(!is_null($bridgeid) && $bridgeid == 4)
      $bridge_where = "ConfirmNumber LIKE 'S%%'";
    else
      $bridge_where = '1=1';

    $sql = "SELECT DAY(ConferenceDate) AS conference_day, 
                   DATEPART(hh, ConferenceTime) AS conference_hour, 
                   SUM(LinesReserved) AS lines_reserved,
                   COUNT(*) AS num_reservations
            FROM Reservation WITH (NOLOCK)
            WHERE Reservation.ReservationStatus IN ('Active', 'Billable', 'Billed', 'Complete') 
              AND YEAR(ConferenceDate)=%d AND MONTH(ConferenceDate)=%d AND DATEPART(hh, ConferenceTime)>=8 AND DATEPART(hh, ConferenceTime)<=20
              AND {$bridge_where}
            GROUP BY DAY(ConferenceDate), DATEPART(hh, ConferenceTime)
            ORDER BY conference_day, conference_hour";

    $rv = Array();
    foreach($this->sql($sql, $year, $month) as $i) {
      if(!isset($rv[$i['conference_day']]))
	$rv[$i['conference_day']] = Array();

      $rv[$i['conference_day']][$i['conference_hour']] = $i;
    }
  
    return $rv;
  }
}
