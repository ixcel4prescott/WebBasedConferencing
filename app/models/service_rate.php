<?php

class ServiceRate extends AppModel
{
  var $name       = 'ServiceRate';
  var $useTable   = 'servicerates';
  var $primaryKey = 'id';

  function toFixed($id) 
  {
    $service_rate = $this->read(null, $id);

    if(isset($service_rate['ServiceRate']))
      foreach($service_rate['ServiceRate'] as $k => &$v) {
	if(!in_array($k, Array('id', 'name')) && !is_null($v))
	  $v  = sprintf('%.04f', (float)$v / 10000.0);
      }

    return $service_rate;
  }

  private function formatRate($num)
  {
    return sprintf($num - intval($num) ? "%.1f" : "%d", $num);
  }

  function createRate($reseller, $data) 
  {
    $type = $data['ServiceRate']['webinterpoint_rate_type'];
    unset($data['ServiceRate']['webinterpoint_rate_type']);

    $paired = Array( 'rsvlesstoll'     => 'rsvless', 
		     'meetmetoll'      => 'meetmetollfree', 
		     'eventmeetmetoll' => 'eventmeetme' );
		    
    $prefix = $this->ratePrefix($reseller);

    $new_servicerate = Array();
    
    foreach($data['ServiceRate'] as $k => $v)
      $new_servicerate[$k] = (int)((float)$v * 10000.0);

    // Make sure the toll rates are not 0
    foreach($paired as $k => $v)
      if($new_servicerate[$k] == 0)
	$new_servicerate[$k] = $new_servicerate[$v];
  
    // force these rates
    $new_servicerate['operdialout']  = $new_servicerate['meetmetollfree'];
    $new_servicerate['eventdialout'] = $new_servicerate['eventmeetme'];
 
    if($type == 'flat')
      $webinterpoint_rate = $new_servicerate['webinterpointflat']/10000.0;
    else
      $webinterpoint_rate = $new_servicerate['webinterpointppm']/100.0;

    $new_servicerate['name'] = sprintf('%s-R%s|T%s|O%s|E%s|W%s',  
				       $prefix,
				       $this->formatRate($new_servicerate['rsvless']/100.0), 
				       $this->formatRate($new_servicerate['rsvlesstoll']/100.0), 				       
				       $this->formatRate($new_servicerate['meetmetollfree']/100.0), 
				       $this->formatRate($new_servicerate['eventmeetme']/100.0), 
				       $this->formatRate($webinterpoint_rate));

    if($type == 'flat')
      $new_servicerate['name'] .= 'Flat';

    return $this->save($new_servicerate);
  }

  function ratePrefix($reseller) 
  {
    return !empty($reseller['rateprefix']) ? $reseller['rateprefix'] : $reseller['racctprefix'];
  }

  function forElement($reseller) 
  {
    $criteria = Array('OR' => Array('ServiceRate.name' => 'LIKE ' . $this->ratePrefix($reseller) . '%', 
				    'name'             => 'LIKE ALL-%'));

    return $this->generateList($criteria, 'name ASC', null, '{n}.ServiceRate.id', '{n}.ServiceRate.name');
  }
}