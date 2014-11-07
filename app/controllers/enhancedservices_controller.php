<?php

class EnhancedservicesController extends AppController
{
  var $uses = Array('Enhancedservices');

  var $permissions = GROUP_ALL;
  //Commenting out for now since this is based off of the old billing system.
  //DC 2012/09/12
/*  private function buildUserLists()
  {
    $user = $this->Session->read('User');

    $reseller_ids = null;

    if(isset($user['ResellerGroup']['Reseller']))
      foreach($user['ResellerGroup']['Reseller'] as $i)
	$reseller_ids[] = $i['resellerid'];

    $salespids = null;

    if(isset($user['SalespersonGroup']['Salesperson']))
      foreach($user['SalespersonGroup']['Salesperson'] as $i)
	$salespids[] = $i['salespid'];
    
    return a($user, $reseller_ids, $salespids);
  }

  function viewbyaccount($account=null, $year=null, $month=null, $day=null)
  {
    $this->pageTitle = 'Enhanced Services';

    if($account) {
      list($user, $reseller_ids, $salespids) = $this->buildUserLists();

      $criteria = Array('acctgrpid' => $account);

      if (!$year) 
	$year = date('Y');
			
      $criteria['YEAR(date)'] = $year;
			
      if($month)
	$criteria['MONTH(date)'] = $month;

      if($day)
	$criteria['DAY(date)'] = $day;

      $this->set('myYear', $year);
      $this->set('myMonth', $month);
      $this->set('myDay', $day);
      $this->set('myAccount', $account);

      $criteria['status'] = a('billable', 'billed', 'complete');

      if($reseller_ids && !in_array(IC_RESELLERID, $reseller_ids)) {
	$criteria['resellerid'] = $reseller_ids;
      } elseif($salespids) {
	$criteria['salespid'] = $salespids;	
      }
		
      $dates = $this->Enhancedservices->findAll($criteria);
      $this->set('dates', $dates);
    } else {
      return $this->redirect('/');
    }
  }

  function viewbyaccountbyweek($account=null, $year=null, $week=null)
  {
    $this->pageTitle = 'Enhanced Services';

    if($account) {
      list($user, $reseller_ids, $salespids) = $this->buildUserLists();

      $criteria = Array('acctgrpid' => $account);

      if (!$year) 
	$criteria['YEAR(date)'] = date('Y');
			
      if($week)
	$criteria['fn WEEK([date])'] = $week;

      $this->set('myYear', $year);
      $this->set('myWeek', $week);
      $this->set('myAccount', $account);

      $criteria['status'] = a('billable', 'billed', 'complete');
      
      if($reseller_ids && !in_array(IC_RESELLERID, $reseller_ids)) {
	$criteria['resellerid'] = $reseller_ids;
      } elseif($salespids) {
	$criteria['salespid'] = $salespids;
      }

      $dates = $this->Enhancedservices->findAll($criteria);
      $this->set('dates', $dates);
    }
  }

 */
}
?>
