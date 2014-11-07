<?

class SpectelReservation extends AppModel
{
  var $primaryKey  = 'ReservationRef';
  var $useTable    = 'Reservation';
  var $useDbConfig = 'spectel';

  function buildCriteria($query, $client_ref)
  {
    $filter = sprintf('SpectelReservation.ClientRef=%d', $client_ref);

    if($query) {
      $fquery = '%' . implode('%', preg_split('/\s+/', mssql_escape($query), -1, PREG_SPLIT_NO_EMPTY)) . '%';
      $filter .= sprintf(' AND (("S" + CONVERT(VARCHAR(MAX), ConferenceRef)) LIKE "%s" OR ConferenceName LIKE "%s" OR 
                             ContactName LIKE "%s" OR UsesCPIN LIKE "%s" OR UsesMPIN LIKE "%s")',
			 $fquery, $fquery, $fquery, $fquery, $fquery);
    }

    return $filter;
  }
  
  function searchTotal($query, $client_ref)
  {
    $sql = 'SELECT COUNT(*) AS count
            FROM Reservation AS SpectelReservation
            JOIN Conference AS SpectelConference ON SpectelReservation.ReservationRef = SpectelConference.ReservationRef
            WHERE %s';

    $rv = $this->sql($sql, $this->buildCriteria($query, $client_ref));
    return $rv[0]['count'];
}

  function search($query, $client_ref, $order, $limit, $page)
  {
    $sql = 'SELECT * 
            FROM (
              SELECT TOP %d * 
              FROM (
                SELECT TOP %d "S" + CONVERT(VARCHAR(MAX), ConferenceRef) AS accountid, ConferenceRef, SpectelConference.ReservationRef, 
                  ConferenceName, ContactName, UsesCPIN, UsesMPIN
                FROM Reservation AS SpectelReservation
                JOIN Conference AS SpectelConference ON SpectelReservation.ReservationRef = SpectelConference.ReservationRef
                WHERE %s ORDER BY %s) 
              AS Set1 ORDER BY %s) 
            AS Set2 ORDER BY %s';

    $rorder = preg_replace('/__tmp_asc__/', ' DESC', preg_replace('/\s+DESC/i', ' ASC', preg_replace('/\s+ASC/i', '__tmp_asc__', $order)));
    $offset = ($page-1) * $limit;

    return $this->sql($sql, $limit, $offset + $limit, $this->buildCriteria($query, $client_ref), $order, 
		      str_replace('SpectelReservation', 'Set1', $rorder), str_replace('SpectelReservation', 'Set2', $order));
  }

  function getReservations($reservation_refs)
  {
    $sql = 'SELECT "S" + CONVERT(VARCHAR(MAX), ConferenceRef) AS accountid, ConferenceRef, SpectelConference.ReservationRef, ConferenceName, 
              ContactName, UsesCPIN, UsesMPIN, Reservation.ClientRef
            FROM Conference AS SpectelConference
            JOIN Reservation ON Reservation.ReservationRef = SpectelConference.ReservationRef
            WHERE Reservation.ReservationRef IN (' . implode(', ', $reservation_refs) . ')';

    return $this->sql($sql);
  }

  function move($reservation, $client)
  {
    $data = Array('ClientRef' => $client['SpectelClient']['ClientRef']);

    if($rv = $this->update($reservation, $data)) { 

      // NB: We need to update SchapiConference and SchapiReservation
      $this->sql('UPDATE SchapiReservation SET UserName="%s" WHERE ReservationRef=%d', 
		 $client['SpectelClient']['AdHocAccountReference'], $reservation['SpectelReservation']['ReservationRef']);

      $this->sql('UPDATE SchapiConference SET UserName="%s" WHERE ConferenceRef IN (SELECT ConferenceRef FROM Conference WHERE ReservationRef=%d)', 
		 $client['SpectelClient']['AdHocAccountReference'], $reservation['SpectelReservation']['ReservationRef']);

    }
    
    return $rv;
  }
}