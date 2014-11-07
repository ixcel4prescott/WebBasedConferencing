<?

class TimeZonesController extends AppController
{
  var $uses = Array('TimeZone');

  function get_time_zones()
  {
    $this->layout = 'ajax';
    Configure::write('debug', 0); 

     
    $time_zones = $this->TimeZone->generateList(Array('bt_recognized' => '1',
                                                       'country' => $this->params['form']['country']),
                                                 'description ASC', 
                                                 null, 
                                                 '{n}.TimeZone.zone_name', 
                                                 '{n}.TimeZone.description');
    if (!is_array($time_zones)){
      $time_zones = $this->TimeZone->generateList(Array('bt_recognized' => '1'),
                                                  'description ASC', 
                                                  null, 
                                                  '{n}.TimeZone.zone_name', 
                                                  '{n}.TimeZone.description');
    }

    $this->set('time_zones', $time_zones);
    $this->set('type', $this->params['form']['type']);
  }
}

