<?
class ServiceratesController extends AppController
{
  /*
Possibly not used DC -2012/09/13
var $uses = Array('ServiceRate', 'Account', 'Room', 'Salesperson', 'Reseller');

  private function formatRate($r)
  {
    return (int)((float)$r * 10000.0);
  }

  private function buildFilter($reseller)
  {
    $filter = Array('AND' => Array('OR' => Array('ServiceRate.name' => 'LIKE ' . $this->ServiceRate->ratePrefix($reseller) . '%', 
						 'name'             => 'LIKE ALL-%' )));

    foreach($this->data['ServiceRate'] as $k => $v)
      if(!empty($v))
	$filter[$k] = $this->formatRate($v);

    return $filter;
  }

  private function buildRates($reseller, $current_rate)
  {
    $filter = $this->buildFilter($reseller);

    $rates        = a();
    $show_custom  = true;

    foreach($this->ServiceRate->findAll($filter, null, 'name ASC') as $r) {
      $rates[$r['ServiceRate']['id']] = $r['ServiceRate']['name'];

      if(substr($r['ServiceRate']['name'], 0, 3) != 'ALL')
	$show_custom = false;
    }
      
    if($show_custom) {
      $rates[-1] = 'Custom Rate';
      $current_rate = -1;
    }

    return Array('rates' => $rates, 'current_rate' => $current_rate);
  }


  function beforeFilter()
  {
    parent::beforeFilter();
    $this->layout = 'ajax';
    Configure::write('debug', 0);
  }
 
  // Public actions
  function rate($id=null) 
  {
    $rate = null;
    if($id) {
      $rv = $this->ServiceRate->read(null, $id);
      $rate = $rv['ServiceRate'];
    }

    $this->set('rate', $rate);
  }

  // FOR ACCOUNTS ----------------------------------------------------------------------------
  function account()
  {
    if(!empty($this->data)) {
      $this->Salesperson->recursive = 0;
      $salesperson = $this->Salesperson->read(null, $this->data['Account']['salespid']);

      $this->Reseller->recursive = 0;
      $reseller = $this->Reseller->read(null, $salesperson['Salesperson']['resellerid']);

      if(!empty($this->data['Account']['acctgrpid'])) {
	$this->Account->recursive = 0;
	$account = $this->Account->read(null, $this->data['Account']['acctgrpid']);
	$current_rate = $account['Account']['default_servicerate'];
      } else {
	$current_rate = $reseller['Reseller']['servicerateid'];
      }

      $this->set($this->buildRates($reseller['Reseller'], $current_rate));
    }
  }

  // FOR ROOMS ------------------------------------------------------------------------------
  function room()
  {
    if(!empty($this->data)) {
      // For edit action, we have a servicerate, else use account default_servicerate for create
      if(!empty($this->data['Room']['accountid'])) {
	$room = $this->Room->read(null, $this->data['Room']['accountid']);
	$salespid = $room['Account']['salespid'];
	$current_rate = $room['Room']['servicerate'];
      } else {
	$this->Account->recursive = 0;
	$account = $this->Account->read(null, $this->data['Room']['acctgrpid']);
	$salespid = $account['Account']['salespid'];
	$current_rate = $account['Account']['default_servicerate'];
      }

      $this->Salesperson->recursive = 0;
      $salesperson = $this->Salesperson->read(null, $salespid);

      $this->Reseller->recursive = 0;
      $reseller = $this->Reseller->read(null, $salesperson['Salesperson']['resellerid']);

      $this->set($this->buildRates($reseller['Reseller'], $current_rate));
    }
  }*/
}
