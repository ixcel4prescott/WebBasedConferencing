<?
class Spectel extends AppModel
{
  var $useTable     = false;
  var $useDbConfig  = 'spectel';

  var $error_msg    = null;

  function createRoom($room)
  {
    // Get company by resellerid
    $company = $this->getCompany($room);
    if(!$company) {
      if(!$this->createCompany($room)) { 
	return false;
      }
      
      $company = $this->getCompany($room);

      if(!$company) { 
	$this->error_msg = 'Could not get company after creation';
	return false;
      }
    }

    // Get client by acctgrpid
    $client = $this->getClient($room);
    if(!$client) {
      if(!$this->createClient($company, $room)) {
	if(!$this->error_msg)
	  return false;
      }

      $client = $this->getClient($room);

      if(!$client) {
	$this->error_msg = 'Could not get client after creation';
	return false;
      }
    }

    // Check company/client alignments
    if($company['CompanyRef'] != $client['CompanyRef'])
      $this->updateClientAlignment($company, $client);

    // Check that a conference doesnt exist by the given CEC/PEC
    if($conference = $this->getConferenceByCode($room['cec'], $room['pec'])) {
      $this->error_msg = sprintf('Conference S%s exists with given (CEC,PEC) = (%s,%s)', $conference, $room['cec'], $room['pec']);
      return false;
    }

    if($room['scheduletype'] == 1)
      $reservation = $this->createConferenceOA($client, $room);
    else 
      $reservation = $this->createConferenceRL($client, $room);

    if($reservation) {
      if($conference = $this->getConferenceByReservation($reservation['ReservationRef'])) {
	$rv = 'S' . $conference;
      } else {
	$this->error_msg = 'Could not get conference by ReservationRef: ' . $reservation['ReservationRef'];
	$rv = null;
      }
    } else {
      $rv = null;
    }
 
    return $rv;
  }

  function now()
  {
    return date('m/d/Y h:i:s a');
  }

  function sanitize($s, $max=null)
  {
    return preg_replace("/'/", '', $max ? substr($s, 0, $max) : $s );
  }

  function getCompany($room)
  {
    $rv = $this->query("SELECT * FROM Company WHERE CompanyExternalID='{$room['resellerid']}'", 'false');
    return $rv ? $rv[0][0] : null;
  }

  function createCompany($room)
  {
    $company = $this->sanitize($room['reseller_company'], 50);
    $contact = $this->sanitize($room['reseller_contact'], 50);
    $email   = $this->sanitize($room['reseller_email'], 50);

    $sql = "DECLARE @RetCode INT
            DECLARE @Information UDTComment
	    DECLARE @CompanyRef INT

	    execute @RetCode = p_cspi_AddCompany 
            @CompanyRef = @CompanyRef output,
	    @Name = '{$company}',
	    @AddressLine1 = '',
	    @AddressLine2 = '',
	    @AddressLine3 = '',
	    @AddressLine4 = '',
	    @Phone = '973-671-0040',
	    @Fax = '973-218-0708',
	    @EMail = '{$email}',
	    @ExternalID = {$room['resellerid']},
	    @ContactName = '{$contact}',
	    @DisabledInd = 0,
	    @Information = @Information output,
	    @WholesalerRef = 0,
	    @ResellerRef = 0,
	    @ExternalString = '';
	    SELECT @RetCode AS RetCode, @CompanyRef AS CompanyRef, @Information AS Information";
    
    $rv = $this->query($sql, 'false');
    if($rv && $rv[0][0]['RetCode'] != 0) { 
      $this->error_msg = 'Could not create client: ' . $rv[0][0]['Information'];
      $rv = false;
    }
    
    return $rv;
  }

  function getClient($room)
  {
    $rv = $this->query("SELECT * FROM Client WHERE ClientMainICC='{$room['acctgrpid']}'", 'false');
    return $rv ? $rv[0][0] : null;
  }
  
  function createClient($company, $room)
  {
    $company_s = $this->sanitize($room['company'] . '_' . $room['acctgrpid'], 50);
    $contact = $this->sanitize($room['contact'], 50);
    $email   = $this->sanitize($room['email'], 50);
    
    $sql = "Declare @RetCode int
            Declare @ClientRef int
	    Declare @AdhocAccReference UDTAccountRef
	    Declare @Information UDTComment
			
	    execute @RetCode = p_cspi_AddClient 
            @ClientRef = @ClientRef output,
	    @CompanyRef = '{$company['CompanyRef']}',
	    @Name = '{$company_s}',
	    @AddressLine1 = '',
	    @AddressLine2 = '',
	    @AddressLine3 = '',
	    @AddressLine4 = '',
	    @Phone = '973-617-0040',
	    @Fax = '973-218-0708',
	    @EMail = '{$email}',
	    @ContactName = '{$contact}',
	    @ContactPhone = '973-617-0040',
	    @ContactFax = '973-218-0708',
	    @ContactEMail = '{$email}',
	    @AdhocAccReference = @AdhocAccReference output,
	    @ExternalID = 0,
	    @CompanyExternalID = 0,
	    @DisabledInd = 0,
	    @Information = @Information output,
	    @HelplessInd = 0,
	    @Apassword = '',
	    @TimeZoneType = 0,
	    @ClientMainICC = '{$room['acctgrpid']}',
	    @BlockDialoutInd = 1,
	    @ClientExternalString = '{$room['acctgrpid']}',
	    @ClientTitle = '',
	    @ClientJobTitle = '',
	    @ClientLanguageType = 1,
	    @SecondaryContactPhone = '',
	    @MobileContactPhone = '',
	    @Comment = '',
	    @DistinguishedName = '',
	    @ClientStatusType = 1,
	    @PreferredDDI = '';
	    SELECT @RetCode AS RetCode, @ClientRef AS ClientRef, @AdhocAccReference AS AdhocAccRegerence, @Information AS Information";

    $rv = $this->query($sql, 'false');

    // If a new client was created with an existing company name, the call will fail with an id of 0
    if($rv && $rv[0][0]['RetCode'] != 0) {
      $this->error_msg = 'Could not create client: ' . $rv[0][0]['Information'];
      $rv = false;
    }
    
    return $rv;
  }

  function updateClientAlignment($company, $client)
  {
    return $this->execute("UPDATE Client SET CompanyRef='{$company['CompanyRef']}' WHERE ClientRef='{$client['ClientRef']}'");
  }

  function getConference($accountid)
  {
    $rv          = null;
    $confirm_num = null;

    if($accountid[0] == 'S')
      $confirm_num = substr($accountid, 1);

    if($confirm_num) {
      $rv = $this->execute("SELECT Conference.*, 
                            SchapiConference.*, 
                            CONVERT(BIT, (SchapiConference.AppFlags & 2)) AS ConfViewerInd,
                            scheduletype = CASE 
 		              WHEN Conference.ConferenceType=16 THEN 2
           	              WHEN Conference.DemandInd=1 THEN 0
     		              WHEN Conference.DemandInd=0 THEN 1
		              ELSE NULL
		            END,
                            CONVERT(BIT, (SchapiConference.Flags & 8)) AS endonchairhangup,
                            CONVERT(BIT, (SchapiConference.Flags & 16)) AS startmode,
                            CONVERT(BIT, (SchapiConference.Flags & 8192)) AS bill_code_prompt,
                            dialout = CASE
               	              WHEN CONVERT(BIT, (SchapiConference.AppFlags & 32))=0 THEN 1
	                      ELSE 0
	                    END
                            FROM Conference WITH(NOLOCK)
                            JOIN SchapiConference WITH(NOLOCK) ON SchapiConference.ConferenceRef = Conference.ConferenceRef 
                            WHERE Conference.ConferenceRef={$confirm_num}");
    } else {
      $this->error_msg = 'Invalid accountid passed: ' . $accountid;
    }

    return $rv ? $rv[0][0] : null;
  }
  
  function getConferenceByCode($cec, $pec=null)
  {
    if($cec && $pec)
      $sql = "SELECT ConferenceRef FROM Conference WHERE UsesMPIN='{$cec}' AND UsesCPIN='{$pec}'";
    else
      $sql = "SELECT ConferenceRef FROM Conference WHERE UsesMPIN='{$cec}' OR UsesCPIN='{$cec}'";

    $rv = $this->query($sql, 'false');
    return $rv ? $rv[0][0]['ConferenceRef'] : null;
  }

  function getConferenceByReservation($reservation_ref)
  {
    $sql = "SELECT ConferenceRef FROM Conference WHERE ReservationRef={$reservation_ref}";
    $rv = $this->query($sql, 'false');
    return $rv ? $rv[0][0]['ConferenceRef'] : null;
  }
	
  function createConferenceRL($client, $room)
  {
    $now = $this->now();
    
    if(!$room['maximumconnections'])
      $room['maximumconnections'] = 49;
    elseif($room['maximumconnections'] == 1)
      $room['maximumconnections']++;
  
    $iAttended = 0;
    $iDemand = 1;

    $iDTMFCommand = 0;
    if($room['scheduletype'] == 2)
      $iDTMFCommand = 1;

    $iDialout = 1;
    if($room['dialout'] == 1)
      $iDialout = 0;

    if(!isset($room['bill_code_prompt']))
      $room['bill_code_prompt'] = 0;

    if(!isset($room['converence_viewer']))
      $room['conference_viewer'] = 0;

    $sql = "Declare @Information UDTComment            
            Declare @ReservationRef int
            Declare @ConfirmNum int
            Declare @DDI UDTDDI
            Declare @RetCode int
            
            execute @RetCode = p_cspi_S700_MakeBooking
            @ClientAccount = '{$client['AdHocAccountReference']}', 
            @ConferenceName = '{$room['confname']}',
            @StartDateTime = '{$now}',
            @Duration = 1440,
            @CodeDuration = 0,
            @MaxLines = {$room['maximumconnections']},
            @EntryToneType = '{$room['entryannouncement']}', 
            @ExitToneType = '{$room['exitannouncement']}', 
            @ModeratorInd = 1,
            @AutoPINInd = 0,
            @ContactPhone = '0000000000',
            @CPIN = '{$room['pec']}',
            @MPIN = '{$room['cec']}',
            @Information = @Information output,
            @ReservationRef = @ReservationRef  output,
            @ConfirmNum = @ConfirmNum output,
            @ExtPortsAllowed = 0,
            @ModHangupInd = {$room['endonchairhangup']},
            @MusicInd = {$room['startmode']},
            @SecurityInd = 1,
            @AttendedInd = {$iAttended},
            @EnabledInd = 1,
            @QandAInd = 0,
            @PollingInd = 0,
            @DemandInd = {$iDemand},
            @BillCodeInd = {$room['bill_code_prompt']},
            @ExtDurationAllowed = 0,
            @InProgressInd = 0,
            @DataInd = 0,
            @ConfViewerInd = {$room['conference_viewer']},
            @ExtRecordInd = 0,
            @AuxCodeInd = 0,
            @BlockDialOutInd = {$iDialout},
            @GlobalInd = 0,
            @SelfRegInd = 0,
            @TimeZone = '',
            @PINmode = {$room['securitytype']},
            @PINListName = 0,
            @NRPmode = {$room['namerecording']},
            @NRPAnnun = 3,
            @MusicSrc = 0,
            @BlastMode = 1,
            @BlastAnnun = 0,
            @DialList = '',
            @BillingConfID = '',
            @SignInName = '',
            @ClientTimeZone = 0,
            @GlobalID = 0,
            @GlobalIDHi = 0,
            @DTMFCommandSet = {$iDTMFCommand},
            @HelplessInd = 0,
            @DisableOptionModInd = 0, 
            @ConferenceComments = '',
            @PromptSet = 0,
            @NtfType = 0,
            @NtfConfirmationInd = 0,
            @NtfCancellationInd = 0,
            @NtfChangeInd = 0,
            @NtfReminderInd = 0,
            @NtfAttendanceInd = 0,
            @NtfComments = '',
            @NtfContactFax = '',
            @NtfContactEMail = '',
            @CabinetRef = 0,
            @DDI = @DDI output;
            SELECT @ReservationRef AS ReservationRef, @ConfirmNum AS ConfirmNum, @RetCode AS RetCode, @Information AS Information;";

    $rv = $this->execute($sql);
    if($rv && $rv[0][0]['RetCode'] != 0) {
      $this->error_msg = 'Could not create conference: ' .  $rv[0][0]['Information'];
      $rv = null;
    } else {
      $rv = $rv[0][0];
    }

    return $rv;
  }

  function createConferenceOA($client, $room)
  {
    $now = $this->now();

    if(!$room['maximumconnections'])
      $room['maximumconnections'] = 49;
    elseif($room['maximumconnections'] == 1)
      $room['maximumconnections']++;

    if(!isset($room['bill_code_prompt']))
      $room['bill_code_prompt'] = 0;

    if(!isset($room['conference_viewer']))
      $room['conference_viewer'] = 0;

    // NB if conference viewer is requested, create it as an unattended conference so codes are preserved
    $iDemand   = 0;
    $iAttended = ($room['conference_viewer'] == 0) ? 1 : 0;

    $iDTMFCommand = 0;
    if($room['scheduletype'] == 2)
      $iDTMFCommand = 1;

    $iDialout = 1;
    if($room['dialout'] == 1)
      $iDialout = 0;

    // Check if the room start time has passed and bump up a min from current if so
    if(time() > strtotime($room['startdate']))
      $room['startdate'] = date('Ymd H:i:s', time() + 60);

    $sql = "Declare @Information UDTComment            
            Declare @ReservationRef int
            Declare @ConfirmNum int
            Declare @DDI UDTDDI
            Declare @RetCode int
            
            execute @RetCode = p_cspi_S700_MakeBooking
            @ClientAccount = '{$client['AdHocAccountReference']}', 
            @ConferenceName = '{$room['confname']}',
            @StartDateTime = '{$room['startdate']}',
            @Duration = {$room['duration']},
            @CodeDuration = 0,
            @MaxLines = {$room['maximumconnections']},
            @EntryToneType = '{$room['entryannouncement']}', 
            @ExitToneType = '{$room['exitannouncement']}', 
            @ModeratorInd = 1,
            @AutoPINInd = 0,
            @ContactPhone = '0000000000',
            @CPIN = '{$room['pec']}',
            @MPIN = '{$room['cec']}',
            @Information = @Information output,
            @ReservationRef = @ReservationRef  output,
            @ConfirmNum = @ConfirmNum output,
            @ExtPortsAllowed = 0,
            @ModHangupInd = {$room['endonchairhangup']},
            @MusicInd = {$room['startmode']},
            @SecurityInd = 1,
            @AttendedInd = {$iAttended},
            @EnabledInd = 1,
            @QandAInd = 0,
            @PollingInd = 0,
            @DemandInd = {$iDemand},
            @BillCodeInd = {$room['bill_code_prompt']},
            @ExtDurationAllowed = 0,
            @InProgressInd = 0,
            @DataInd = 0,
            @ConfViewerInd = {$room['conference_viewer']},
            @ExtRecordInd = 0,
            @AuxCodeInd = 0,
            @BlockDialOutInd = {$iDialout},
            @GlobalInd = 0,
            @SelfRegInd = 0,
            @TimeZone = '',
            @PINmode = {$room['securitytype']},
            @PINListName = 0,
            @NRPmode = {$room['namerecording']},
            @NRPAnnun = 3,
            @MusicSrc = 0,
            @BlastMode = 1,
            @BlastAnnun = 0,
            @DialList = '',
            @BillingConfID = '',
            @SignInName = '',
            @ClientTimeZone = 0,
            @GlobalID = 0,
            @GlobalIDHi = 0,
            @DTMFCommandSet = {$iDTMFCommand},
            @HelplessInd = 0,
            @DisableOptionModInd = 0, 
            @ConferenceComments = '',
            @PromptSet = 0,
            @NtfType = 0,
            @NtfConfirmationInd = 0,
            @NtfCancellationInd = 0,
            @NtfChangeInd = 0,
            @NtfReminderInd = 0,
            @NtfAttendanceInd = 0,
            @NtfComments = '',
            @NtfContactFax = '',
            @NtfContactEMail = '',
            @CabinetRef = 0,
            @DDI = @DDI output;
            SELECT @ReservationRef AS ReservationRef, @ConfirmNum AS ConfirmNum, @RetCode AS RetCode, @Information AS Information;";

    $rv = $this->execute($sql);
    if($rv && $rv[0][0]['RetCode'] != 0) {
      $this->error_msg = 'Could not create conference: ' . $rv[0][0]['Information'];
      $rv = null;
    } else {
      $rv = $rv[0][0];
    }

    return $rv;
  }

  function updateRoom($room, $data) 
  {
    $rv          = null;
    $confirm_num = null;

    if($room['Room']['accountid'][0] == 'S')
      $confirm_num = substr($room['Room']['accountid'], 1);
    
    if($confirm_num) {
      if($spectel_room = $this->pull($room['Room']['accountid'])) { 

	if($spectel_room['scheduletype'] != 1 || strtotime($spectel_room['startdate']) > time()) { 

	  if(isset($data['scheduletype']))
	    unset($data['scheduletype']);

	  // If request data is only partial, not full record, pull in additional info

	  // honor bridge first
	  foreach($spectel_room as $k => $v)
	    if(!array_key_exists($k, $data))
	      $data[$k] = $v;

	  // pull in aditional info if not on bridge
	  foreach($room['Room'] as $k => $v)
	    if(!array_key_exists($k, $data))
	      $data[$k] = $v;

	  if(!isset($data['bill_code_prompt']))
	    $data['bill_code_prompt'] = 0;

	  // Fill in data missing from account table
	  if(!isset($data['converence_viewer']))
	    $data['conference_viewer'] = $spectel_room['conference_viewer'];

	  if(empty($data['confname']))
	    $data['confname'] = $spectel_room['confname'];
	
	  if(empty($data['startdate']))
	    $data['startdate'] = $spectel_room['startdate'];

	  if(empty($data['duration']))
	    $data['duration'] = $spectel_room['duration'];

	  $cec = !empty($spectel_room['cec']) ? sprintf("'%s'", $spectel_room['cec']) : 'NULL';
	  $pec = !empty($spectel_room['pec']) ? sprintf("'%s'", $spectel_room['pec']) : 'NULL';

	  if($spectel_room['scheduletype'] == 1) {
	    // NB if conference viewer is requested, create it as an unattended conference so codes are preserved
	    $iDemand   = 0;
	    $iAttended = ($data['conference_viewer'] == 0) ? 1 : 0;
	  } else {
	    $iAttended = 0;
	    $iDemand   = 1;
	  }
      
	  $iDTMFCommand = 0;
	  if($data['scheduletype'] == 2)
	    $iDTMFCommand = 1;

	  $iDialout = 1;
	  if($data['dialout'] == 1)
	    $iDialout = 0;

	  $sql = "Declare @RetCode int
              Declare @ConfirmNum int
              Declare @Information UDTComment

              Declare @ConferenceName UDTName
              Declare @StartDateTime datetime
              Declare @CPIN UDTPIN
              Declare @MPIN UDTPIN

              set @ConfirmNum = {$confirm_num}
              set @MPIN = {$cec}
              set @CPIN = {$pec}

              execute @RetCode = p_cspi_S700_EditBooking
              @ConfirmNum = @ConfirmNum output,
              @ConferenceName = '{$data['confname']}',
              @StartDateTime = '{$data['startdate']}',
              @Duration = {$data['duration']},
              @CodeDuration = 0,
              @MaxLines = {$data['maximumconnections']},
              @EntryToneType = {$data['entryannouncement']},
              @ExitToneType = {$data['exitannouncement']},
              @ModeratorInd = 1,
              @AutoPINInd = 0,
              @ContactPhone = '0000000000',
              @CPIN = @CPIN output,
              @MPIN = @MPIN output,
              @Information = @Information output,
              @ExtDurationAllowed= 0,
              @ExtPortsAllowed = 0,
              @ModHangupInd = {$data['endonchairhangup']},
              @MusicInd = {$data['startmode']},
              @SecurityInd = 0,
              @AttendedInd = {$iAttended},
              @EnabledInd = 1,
              @QandAInd = 0,
              @PollingInd = 0,
              @DemandInd = {$iDemand},
              @BillCodeInd = {$data['bill_code_prompt']},
              @InProgressInd = 0,
              @DataInd = 0,
              @ConfViewerInd = {$data['conference_viewer']},
              @ExtRecordInd = 0,
              @SecureCodeInd = 0,
              @AuxCodeInd = 0,
              @BlockDialOutInd = {$iDialout},
              @GlobalInd = 0,
              @SelfRegInd = 0,
              @TimeZone = '',
              @PINmode = {$data['securitytype']},
              @PINListName = 0,
              @NRPmode = {$data['namerecording']},
              @NRPAnnun = 3,
              @MusicSrc = 0,
              @BlastMode = 1,
              @BlastAnnun = 0,
              @DialList = '',
              @BillingConfID = '',
              @SignInName = '',
              @ClientTimeZone = 0,
              @GlobalID = 0,
              @GlobalIDHi = 0,
              @DTMFCommandSet= {$iDTMFCommand};
              SELECT @RetCode AS RetCode, @ConfirmNum AS ConfirmNum, @Information AS Information;";

	  if($rv = $this->execute($sql)) { 
	    if($rv[0][0]['RetCode'] != 0) {
	      $this->error_msg = 'Could not update conference: ' . $rv[0][0]['Information'];
	      $rv = null;
	    } else {
	      $rv = $rv[0][0];
	    }
	  } else {
	    // we cant update rooms after the conf startdate so just silently pass this through
	    // we shouldnt get here anyway because we are disabling inputs and checking for safe updates
	    $rv = true;
	  }
	} else {
	  $this->error_msg = 'Cannot update room settings after startdate';
	  $rv = true;
	}
      } else { 
	$this->error_msg = 'No such room found on bridge with confirmation number: ' . $room['Room']['accountid'];
	$rv = null;
      }
    } else {
      $this->error_msg = 'Invalid accountid passed: ' . $room['Room']['accountid'];
      $rv = null;
    }

    return $rv;
  }

  function pull($accountid)
  {
    $rv = null;

    if($row = $this->getConference($accountid)) { 
      $rv = Array( 'bridgeid'           => 4,
		   'confname'           => $row['ConferenceName'], 
		   'duration'           => $row['ConferenceOriginalDuration'],
		   'startdate'          => $row['ConferenceStartDateTime'],
		   'cec'                => $row['UsesMPIN'],
		   'pec'                => $row['UsesCPIN'],
		   'maximumconnections' => $row['NumberOfParticipants'], 
		   'scheduletype'       => $row['scheduletype'],		    
		   'securitytype'       => $row['PINMode'],
		   'namerecording'      => $row['NrpMode'],
		   'entryannouncement'  => $row['EntryToneType'],
		   'exitannouncement'   => $row['ExitToneType'],
		   'startmode'          => $row['startmode'],
		   'endonchairhangup'   => $row['endonchairhangup'],
		   'dialout'            => $row['dialout'],
		   'bill_code_prompt'   => $row['bill_code_prompt'],
		   'conference_viewer'  => $row['ConfViewerInd'] );

    } else {
      $this->error_msg = sprintf('Account ID %s not found on bridge', $accountid);
    }

    return $rv;
  }

  function activateRoom($room)
  {
    if(SPECTEL_SUSPEND) { 
      $data = $room['Room'];
      $data['maximumconnections'] = 100;
      return $this->updateRoom($room, $data);
    } else {
      return true;
    }
  }

  function cancelRoom($room)
  {
    $rv          = null;
    $confirm_num = null;

    if($room['Room']['accountid'][0] == 'S')
      $confirm_num = substr($room['Room']['accountid'], 1);
    
    if($confirm_num) {
      $sql = "Declare @RetCode int
              Declare @ConfirmNum int
              Declare @Information UDTComment

              execute @RetCode = p_cspi_S700_DeleteBooking
              @ConfirmNum = {$confirm_num},
              @Information = @Information output;
              SELECT @RetCode AS RetCode, @Information AS Information;";

      $rv = $this->execute($sql);
      if($rv && $rv[0][0]['RetCode'] != 0) {
	$this->error_msg = 'Could not cancel conference: ' . $rv[0][0]['Information'];
      }
    } else {
      $this->error_msg = 'Invalid accountid passed: ' . $room['Room']['accountid'];
    }

    return $rv;
  }

  function suspendRoom($room)
  {
    if(SPECTEL_SUSPEND) { 
      $data = $room['Room'];
      $data['maximumconnections'] = 2;
      return $this->updateRoom($room, $data);
    } else {
      return true;
    }
  }
}