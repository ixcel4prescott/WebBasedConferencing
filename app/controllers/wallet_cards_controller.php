<?

class WalletCardsController extends AppController
{
  var $uses        = Array('Account', 'Room');
  var $permissions = GROUP_IC_ALL;

  function beforeFilter() 
  {
    parent::beforeFilter();
    vendor('fpdf/fpdf');    
  }

  private function generateWalletCard($doc, $account, $room)
  {
    $doc->AddPage();
    $doc->SetMargins(0, 0, 0);

    $header = Array();
    $header[] = $room['Contact'][0]['first_name'] . ' ' . $room['Contact'][0]['last_name'];
    $header[] = $account['Account']['bcompany'];
    $header[] = $room['Contact'][0]['address1'];

    if(!empty($room['Contact'][0]['address2']))
      $header[] = $room['Contact'][0]['address2'];

    if(!empty($room['Contact'][0]['address3']))
      $header[] = $room['Contact'][0]['address3'];

    $header[] = sprintf('%s, %s %s', $room['Contact'][0]['city'],  $room['Contact'][0]['state'],  $room['Contact'][0]['zip']);
    $header[] = $room['Contact'][0]['country'];
   
    // Contact header -- 1/72 is 1 pt rounded to 0.15 line height
    $doc->SetFont('Arial', '', 10);

    $doc->setXY(27, 40);
    $doc->MultiCell(0, 4, implode("\n", $header), 0, 'L');    

    $doc->setXY(27, 90);
    $doc->Cell(0, 4, sprintf('Dear %s,', $room['Contact'][0]['first_name']));

    // Card name & fields
    $doc->SetFont('Arial', 'B', 10);

    $doc->setXY(145, 212);
    $doc->Cell(0, 4, $room['Room']['contact']);

    $doc->SetFont('Arial', 'B', 8);

    $doc->setXY(145, 216);
    $doc->Cell(0, 4, $account['Account']['bcompany']);

    $doc->setXY(118, 236);
    $doc->Cell(0, 3, $room['DialinNumber']['tollfreeno']);

    $doc->setXY(158, 236);
    $doc->Cell(0, 3, $room['DialinNumber']['tollno']);

    $doc->setXY(118, 245);
    $doc->Cell(0, 3, $room['Room']['cec']);

    $doc->setXY(158, 245);
    $doc->Cell(0, 3, $room['Room']['pec']);

    if ($room['WebinterpointInfo']['audio_accountid'] == $room['Room']['accountid']){
        $doc->setXY(118, 253);
        $doc->Cell(0, 3, $account['Branding']['webinterpoint_url']);
    }
    elseif ($room['WebexInfo']['audio_accountid'] == $room['Room']['accountid']){
        $doc->setXY(118, 253);
        $doc->Cell(0, 3, $room['WebexInfo']['url']);
    }
    elseif ($room['LiveMeetingInfo']['audio_accountid'] == $room['Room']['accountid']){
        $doc->setXY(118, 253);
        $doc->Cell(0, 3, $room['LiveMeetingInfo']['url']);
    }
  }
  
  function account($acctgrpid=null)
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);

    if($account = $this->Account->get($acctgrpid, $user)) {
      if(!empty($account['Branding']['webinterpoint_url'])) {

	$criteria = Array( 'Room.acctgrpid'      => $acctgrpid, 
			   'Room.roomstat'       => 0, 
			   'Room.isopassist'     => 0, 
			   'Room.isevent'        => 0, 
			   'Room.bridgeid'       => $account['DefaultBridge']['bridge_id']);

	$doc = new FPDF('P', 'mm', 'Letter');     

	foreach($this->Room->findAll($criteria, null, 'Room.accountid') as $room)
	  if(!empty($room['Contact'][0]['id']))
	    $this->generateWalletCard($doc, $account, $room);

	$doc->Output();

	die;
      } else {
	$this->Session->setFlash('Please make sure this account has branding information before printing wallet cards');
	$this->redirect('/accounts/view/' . $acctgrpid);	
      }
    } else {
      $this->Session->setFlash('Account not found');
      $this->redirect('/');
    }
  }

  function room($accountid=null) 
  {
    $user = $this->Session->read('User');
    $this->set('user', $user);

    if($room = $this->Room->get($accountid, $user)) {      
      if(!empty($room['Contact'][0]['id'])) {
	$account = $this->Account->read(null, $room['Room']['acctgrpid']);
	if(!empty($account['Branding']['webinterpoint_url'])) {      
	  $doc = new FPDF('P', 'mm', 'Letter');
	  $this->generateWalletCard($doc, $account, $room);
	  $doc->Output();

	  die;
	} else {
	  $this->Session->setFlash('Please make sure this account has branding information before printing wallet cards');
	  $this->redirect('/accounts/view/' . $room['Room']['acctgrpid']);	
	}
      } else {
	$this->Session->setFlash('Please make sure this room has a contact before printing wallet cards');
	$this->redirect('/rooms/view/' . $accountid);
      }      
    } else {
      $this->Session->setFlash('Room not found');
      $this->redirect('/');      
    }
  }
}
