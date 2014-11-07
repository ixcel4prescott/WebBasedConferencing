<? 

class AccountServiceRatesSummary extends AppModel
{
   var $useTable  = 'accountgroup_service_rates_summary';
   var $belongsTo = Array('ServiceType' => Array( 'className'  => 'ServiceType', 
                                                  'foreignKey' => 'service_type_id' ));
}