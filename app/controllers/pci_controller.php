<?

class PciController extends AppController
{
  var $uses = Array('Pci', 'Account', 'State', 'Country', 'CreditCard');

  function update($acctgrpid=null)
  {
    if($acctgrpid) {
      $user = $this->Session->read('User');

      if($account = $this->Account->get($acctgrpid, $user)) {
	    $this->set('account', $account);
        $states = $this->State->generateList(null,'name ASC', null,
          '{n}.State.abbrev',  '{n}.State.name');
        unset($states['XX']);
        $this->set('states', $states);
        $this->set('countries', aa('US', 'United States', 'CA', 'Canada'));
        
        $cc = $this->CreditCard->find(Array('acctgrpid' => $acctgrpid, 'active' => 1), null, 'created_on DESC');
	    $this->set('cc', $cc);	
        $this->set('authenticate', 0);

        if(!empty($this->data) && array_key_exists('card_number', $this->data['Pci'])) {
          $this->set('authenticate', 1);
          $this->data['Pci']['last_four'] = substr($this->data['Pci']['card_number'], -4);
          if(!empty($this->data['Pci']['ccexpire_month']) and !empty($this->data['Pci']['ccexpire_year'])){
            $this->data['Pci']['expiration_date'] = mktime(0, 0, 0, $this->data['Pci']['ccexpire_month'], 1, $this->data['Pci']['ccexpire_year']);
          }

          if($rv = $this->Pci->save($this->data)) {
            $this->set('save_messages', $rv);
            if($rv['status'] == 'ok'){
              $data = Array('acctgrpid' => $acctgrpid, 'billcc' => $this->data['Account']['billcc']);
		      if($this->Account->save($data)) {
		        $this->diffLog('Account', DIFFLOG_OP_UPDATE, $acctgrpid, $data, $account['Account']);
		      }
	          $this->Session->setFlash('Credit card information has been updated');
	          $this->redirect('/accounts/view/' . $acctgrpid);
            } else if ($rv['authorized'] == 0) {
	          $this->Session->setFlash('Not authorized');
            } else {
	          $this->Session->setFlash('Credit card update failed');
            }
          } else {
	        $this->Session->setFlash('Credit card update failed');
          }
        } elseif(!empty($this->data) && 
                 array_key_exists('billcc', $this->data['Account']) &&
                 $this->data['Account']['billcc'] != $account['Account']['billcc']) {
            if ($this->data['Account']['billcc'] ==1 && empty($cc)){
	          $this->Session->setFlash('No credit card is present for this account');
              $this->Account->invalidate('billcc');
            } else {
              $data = Array('acctgrpid' => $acctgrpid, 'billcc' => $this->data['Account']['billcc']);
		      if($this->Account->save($data))
		        $this->diffLog('Account', DIFFLOG_OP_UPDATE, $acctgrpid, $data, $account['Account']);
	          $this->Session->setFlash('Credit card billing status has been updated');
	          $this->redirect('/accounts/view/' . $acctgrpid);
            }
	    } elseif (!empty($this->data)) {
	          $this->Session->setFlash('No changes made to credit card information');
	          $this->redirect('/accounts/view/' . $acctgrpid);
	    } else {
          if (empty($cc))
            $this->set('authenticate', 1);
	      $this->data = $account;
	    }
      } else {
        $this->Session->setFlash('Account not found');
	    $this->redirect('/accounts');	
      }
    } else {
      $this->redirect('/accounts');
    }
  }

  /*
 * No longer used. DC - 2012-09-12
  function view($acctgrpid=null)
  {
    if($acctgrpid) {
      $user = $this->Session->read('User');

      if($account = $this->Account->get($acctgrpid, $user)) {
	$this->set('account', $account);

	if(!empty($this->data) && !empty($this->data['Pci']['password'])) {
	  if($pci = $this->Pci->fetch($acctgrpid, $this->data['Pci']['password'])) {
	    $this->set('pci', $pci);
	  } else {
	    $this->Session->setFlash('Could not decrypt credit card with specified password');
	  }
	}

      } else {
	$this->Session->setFlash('Account not found');
	$this->redirect('/accounts');	
      }
    } else {
      $this->redirect('/accounts');
    }
  } */
}
