<?php

class BridgeSettings extends AppModel
{
  var $primaryKey = 'id';
  var $useTable   = 'bridge_settings';
  
  private function getCriteriaAndFields($bridge){
    switch ($bridge){
      case 3:
        $criteria = array('oci is not null');
        $fields =  'id as id, symbol as symbol, category as category,
                    setting as setting, value as value, oci as bridgevalue,
                    description as description';
        break;
      case 4:
        $criteria = array('spectel is not null');
        $fields =   'id as id, symbol as symbol, category as category,
                    setting as setting, value as value, spectel as bridgevalue,
                    description as description';
        break;
      case 7:
        $criteria = array('bt is not null');
        $fields =  'id as id, symbol as symbol, category as category,
                    setting as setting, value as value, bt as bridgevalue,
                    description as description';
        break;
      case 10:
        $criteria = array('intercall is not null');
        $fields =  'id as id, symbol as symbol, category as category,
                    setting as setting, value as value, intercall as bridgevalue,
                    description as description';
        break;
      case 14:
        $criteria = array('spectel_atl is not null');
        $fields =  'id as id, symbol as symbol, category as category,
                    setting as setting, value as value, spectel_atl as bridgevalue,
                    description as description';
        break;
      case 15:
        $criteria = array('spectel_fr is not null');
        $fields =  'id as id, symbol as symbol, category as category,
                    setting as setting, value as value, spectel_fr as bridgevalue,
                    description as description';
        break;

      default:
        $criteria = array('1' => '2');
    }
    return array('criteria' => $criteria, 'fields' => $fields);
    
  
  }
  
  function getBridgeSettings($bridge){

    $rv = $this->getCriteriaAndFields($bridge);
    $rawBridgeSettings = $this->findAll($rv['criteria'], $rv['fields']);
    $BridgeSettings = array();
    foreach($rawBridgeSettings as $i){
      $check = 0;
      foreach($BridgeSettings as $setting => $settingValue){
        if ($settingValue['symbol'] == $i[0]['symbol'] and $settingValue['bridgevalue'] == $i[0]['bridgevalue']){
          $check = $setting;
        }
      }
      if ($check == 0){
        $BridgeSettings[$i[0]['id']] = $i[0];
      }
    }
    return $BridgeSettings;
  }
  
  function getBridgeSettingDescriptions($bridge){
    $rv = $this->getCriteriaAndFields($bridge);
    $rawBridgeSettingsDesc = $this->findAll($rv['criteria'], $rv['fields']);
    foreach($rawBridgeSettingsDesc as $i){
      $BridgeSettingsDesc[$i[0]['symbol']][$i[0]['value']] = $i[0]['description'];
    }
    return $BridgeSettingsDesc;
  }
}