<?

class Oci extends AppModel
{
  var $useTable    = false;
  var $useDbConfig = 'octave';

  var $error_msg = null;
  var $output;

  function createRoom($room)
  {
    $rv = null;

    if(!isset($room['lang']))
      $room['lang'] = 0;

    $args = Array( 'a'   => $room['acctgrpid'],
		   't'   => $room['contact'],
		   'c'   => $room['company'],
		   'cec' => $room['cec'],
		   'pec' => $room['pec'],
		   'r'   => $room['confname'],
		   'm'   => $room['maximumconnections'],

		   'n'   => Array( 'note1=' . (isset($room['note1']) ? $room['note1'] : ''),
				   'note2=' . (isset($room['note2']) ? $room['note2'] : ''),
				   'note3=' . (isset($room['note3']) ? $room['note3'] : ''),
				   'note4=' . (isset($room['note4']) ? $room['note4'] : '') ), 

		   'f'   => Array('startmode='             . (isset($room['startmode']) ? $room['startmode'] : 0),
				  'namerecording='         . (isset($room['namerecording']) ? $room['namerecording'] : 0),
				  'endonchairhangup='      . (isset($room['endonchairhangup']) ? $room['endonchairhangup'] : 0),
				  'dialout='               . (isset($room['dialout']) ? $room['dialout'] : 0),
				  'record_playback='       . (isset($room['record_playback']) ? $room['record_playback'] : 0),
				  'digitentry1='           . (isset($room['digitentry1']) ? $room['digitentry1'] : 0),
				  'confirmdigitentry1='    . (isset($room['confirmdigitentry1']) ? $room['confirmdigitentry1'] : 0),
				  'digitentry2='           . (isset($room['digitentry2']) ? $room['digitentry2'] : 0),
				  'confirmdigitentry2='    . (isset($room['confirmdigitentry2']) ? $room['confirmdigitentry2'] : 0),
				  'muteallduringplayback=' . (isset($room['muteallduringplayback']) ? 
							      $room['muteallduringplayback'] : 0),
				  'entryannouncement='     . (isset($room['entryannouncement']) ? $room['entryannouncement'] : 0),
				  'exitannouncement='      . (isset($room['exitannouncement']) ? $room['exitannouncement'] : 0),
				  'endingsignal='          . (isset($room['endingsignal']) ? $room['endingsignal'] : 0),
				  'dtmfsignal='            . (isset($room['dtmfsignal']) ? $room['dtmfsignal'] : 0),            
				  'recordingsignal='       . (isset($room['recordingsignal']) ? $room['recordingsignal'] : 0),
				  'securitytype='          . (isset($room['securitytype']) ? $room['securitytype'] : 0),
				  'lang='                  . (isset($room['lang']) ? $room['lang'] : 0) )
		   );

    $rv = spawn('oci'.DS.'create_room_oci.pl', $args, $this->output, SCRIPT_ROOT);

    if($rv === 0) {
      if(!$rv = $this->getConfirmationNumber($room))
	$this->error_msg = 'Could not get confirmation number after OCI creation';
    } else {
      $this->error_msg = $this->output[2];
      $rv = null;
    }

    return $rv;
  }

  function getConfirmationNumber($room)
  {
    $sql = 'SELECT RoomView.ConfirmationNumber as ConfirmationNumber
            FROM RoomView  WITH (NOLOCK)
            INNER JOIN ConferenceEntryCode WITH (NOLOCK) ON RoomView.ConferenceId = ConferenceEntryCode.ConferenceId 
            WHERE EntryCodeDTMF IN ("%s", "%s")';

    $rv = $this->query(sprintf($sql, $room['cec'], $room['pec']));

    return $rv ? $rv[0][0]['ConfirmationNumber'] : null;
  }

  function updateRoom($room, $data)
  {
    $rv = null;

    if(!isset($room['lang']))
      $room['lang'] = 0;

    // FIXME  default to existing ala create
    $args = Array( 'a'   => $room['Room']['acctgrpid'],
		   'c'   => $room['Room']['accountid'],

 		   'f'   => Array('confname='              . $data['confname'],
				  'maximumconnections='    . (isset($data['maximumconnections']) ? 
							      $data['maximumconnections'] : $room['Room']['maximumconnections']),

				  'startmode='             . (isset($data['startmode']) ? 
							      $data['startmode'] : $room['Room']['startmode']),

				  'namerecording='         . (isset($data['namerecording']) ? 
							      $data['namerecording'] : $room['Room']['namerecording']),

				  'endonchairhangup='      . (isset($data['endonchairhangup']) ? 
							      $data['endonchairhangup'] : $room['Room']['endonchairhangup']),

				  'dialout='               . (isset($data['dialout']) ? 
							      $data['dialout'] : $room['Room']['dialout']),

				  'record_playback='       . (isset($data['record_playback']) ? 
							      $data['record_playback'] : $room['Room']['record_playback']),

				  'digitentry1='           . (isset($data['digitentry1']) ? 
							      $data['digitentry1'] : $room['Room']['digitentry1']),

				  'confirmdigitentry1='    . (isset($data['confirmdigitentry1']) ? 
							      $data['confirmdigitentry1'] : $room['Room']['confirmdigitentry1']),

				  'digitentry2='           . (isset($data['digitentry2']) ? 
							      $data['digitentry2'] : $room['Room']['digitentry2']),

				  'confirmdigitentry2='    . (isset($data['confirmdigitentry2']) ? 
							      $data['confirmdigitentry2'] : $room['Room']['confirmdigitentry2']),

				  'muteallduringplayback=' . (isset($data['muteallduringplayback']) ?
							      $data['muteallduringplayback'] : 
							      $room['Room']['muteallduringplayback']),

				  'entryannouncement='     . (isset($data['entryannouncement']) ? 
							      $data['entryannouncement'] : $room['Room']['entryannouncement']),

				  'exitannouncement='      . (isset($data['exitannouncement']) ? 
							      $data['exitannouncement'] : $room['Room']['exitannouncement']),

				  'endingsignal='          . (isset($data['endingsignal']) ? 
							      $data['endingsignal'] : $room['Room']['endingsignal']),

				  'dtmfsignal='            . (isset($data['dtmfsignal']) ? 
							      $data['dtmfsignal'] : $room['Room']['dtmfsignal']),           

				  'recordingsignal='       . (isset($data['recordingsignal']) ? 
							      $data['recordingsignal'] : $room['Room']['recordingsignal']),

				  'securitytype='          . (isset($data['securitytype']) ? 
							      $data['securitytype'] : $room['Room']['securitytype']),

				  'lang='                  . (isset($data['lang']) ? $data['lang'] : $room['Room']['lang']),
				  'note1='                 . (isset($data['note1']) ? $data['note1'] : $room['Room']['note1']),
				  'note2='                 . (isset($data['note2']) ? $data['note2'] : $room['Room']['note2']),
				  'note3='                 . (isset($data['note3']) ? $data['note3'] : $room['Room']['note3']),
				  'note4='                 . (isset($data['note4']) ? $data['note4'] : $room['Room']['note4']) )
		   );

    $rv = spawn('oci'.DS.'update_room_oci.pl', $args, $this->output, SCRIPT_ROOT);

    if($rv === 0) {
      $rv = true;
    } else {
      $this->error_msg = $this->output[2];
      $rv = null;
    }

    return $rv;
  }

  function activateRoom($room)
  {
    return true;
  }

  function suspendRoom($room)
  {
    $exit_status = spawn('oci'.DS.'suspendroom.pl', Array('c' => $room['Room']['accountid']), $this->output, SCRIPT_ROOT);

    if($exit_status === 0) {
      $rv = true;
    } else {
      $this->error_msg = $this->output[2];
      $rv = false;
    }

    return $rv;
  }

  function cancelRoom($room)
  {
    if($this->checkConfirmationNumber($room['Room']['accountid'])) {
      $exit_status = spawn('oci'.DS.'delete_room_oci.pl', Array('c' => $room['Room']['accountid']), $this->output, SCRIPT_ROOT);

      if($exit_status === 0) {
	$rv = true;
      } else {
	$this->error_msg = $this->output[2];
	$rv = false;
      }
    } else {
      $rv = true;
    }

    return $rv;
  }

  function checkConfirmationNumber($accountid)
  {
    $sql = "SELECT 1 AS found
            FROM Conference WITH (NOLOCK)
            INNER JOIN ConfSchedule  WITH (NOLOCK) ON Conference.ConferenceId = ConfSchedule.ConferenceId
            WHERE ConfSchedule.ConfirmationNumber = '%s'";
    
    return $this->sql($sql, $accountid);
  }

  function pull($accountid)
  {
    $rv = null;

    $row = $this->sql("SELECT Conference.*, cec.EntryCodeDTMF AS cec, pec.EntryCodeDTMF AS pec,
                       CONVERT(datetime, CONVERT(VARCHAR(10), Conference.StartDate, 110) + ' ' + CONVERT(VARCHAR(10), Conference.StartTime, 108)) AS startdate
                       FROM Conference WITH (NOLOCK)
                       LEFT OUTER JOIN ConferenceEntryCode AS cec WITH (NOLOCK) ON cec.ConferenceId = Conference.ConferenceId AND cec.CECEnum=3
                       LEFT OUTER JOIN ConferenceEntryCode AS pec WITH (NOLOCK) ON pec.ConferenceId = Conference.ConferenceId AND pec.CECEnum=1
                       JOIN ConfSchedule WITH (NOLOCK) ON  ConfSchedule.ConferenceId = Conference.ConferenceId
                       WHERE ConfSchedule.ConfirmationNumber='%s'", $accountid);
    
    if($row) { 
      $rv = Array( 'bridgeid'              => 3,
		   'confname'              => $row[0]['ConfName'],
		   'duration'              => $row[0]['Duration'],
		   'startdate'             => $row[0]['startdate'],
		   'cec'                   => $row[0]['cec'],
		   'pec'                   => $row[0]['pec'],
		   'maximumconnections'    => $row[0]['MaxParticipants'],
		   'scheduletype'          => $row[0]['SchedTypeEnum'],
		   'securitytype'          => $row[0]['SecurityMethodEnum'],
		   'startmode'             => $row[0]['StartProcEnum'],
		   'namerecording'         => $row[0]['bRecordNames'],
		   'endonchairhangup'      => $row[0]['bChairHangup'],
		   'dialout'               => $row[0]['bAllowChairDialout'],
		   'record_playback'       => $row[0]['bAllowUARecordPlayback'],
		   'entryannouncement'     => $row[0]['EntryProcEnum'],
		   'exitannouncement'      => $row[0]['ExitProcEnum'],
		   'endingsignal'          => $row[0]['EndConfProcEnum'],
		   'dtmfsignal'            => $row[0]['OtherProcEnum'],
		   'recordingsignal'       => $row[0]['RecordingProcEnum'],
		   'muteallduringplayback' => $row[0]['bMutePlayback'],
		   'digitentry1'           => $row[0]['eCDRDigitRequired'],
		   'confirmdigitentry1'    => $row[0]['bBypassCDRDigitConfirmation'],
		   'digitentry2'           => $row[0]['eCDRDigitRequired2'],
		   'confirmdigitentry2'    => $row[0]['bBypassCDRDigitConfirmation2'], 
		   'note1'                 => $row[0]['AuxData1'],
		   'note2'                 => $row[0]['AuxData2'],
		   'note3'                 => $row[0]['AuxData3'],
		   'note4'                 => $row[0]['AuxData4'] );
    } else {
      $this->error_msg = sprintf('Account ID %s not found on bridge', $accountid);
    }
    
    return $rv;
  }
}