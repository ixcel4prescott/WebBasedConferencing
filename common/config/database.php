<?

class DATABASE_CONFIG
{
 /*  Testing:  */
 
  var $default = Array( 'driver'   => 'mssql',
			'connect'  => 'mssql_connect',
			'host'     => '192.168.2.198',
			'login'    => 'dansong',
			'password' => 'staswero',
			'database' => 'ICDev',
			'prefix'   => '',
			'persistent' => false);  
 
/*
  var $default = Array( 'driver'   => 'mssql',
			'connect'  => 'mssql_connect',
			'host'     => '192.168.100.194',
			'login'    => 'myca',
			'password' => 'r00tb33r',
			'database' => 'IC',
			'prefix'   => '',
			'persistent' => false);
*/
  var $billing = Array( 'driver'   => 'mssql',
			'connect'  => 'mssql_connect',
			'host'     => '65.91.73.165',
			'login'    => 'myca',
			'password' => 'r00tb33r',
			'database' => 'Billing',
			'prefix'   => '',
			'persistent' => false);


  var $octave = Array( 'driver'     => 'mssql',
		       'connect'    => 'mssql_connect',
		       'host'       => '192.168.51.11',
		       'login'      => 'mvolpe',
		       'password'   => 'tatercha',
		       'database'   => 'Octave',
		       'prefix'     => '',
		       'persistent' => true);

  var $spectel = Array( 'driver'   => 'mssql',
			'connect'  => 'mssql_connect',
			'host'     => '65.91.73.149', 
			'login'    => 'sa',
			'password' => 'na+asha1776',
			'database' => 'BSRes2',
			'prefix'   => '',
			'persistent' => false);
			
  var $spectel_atl = Array( 'driver' => 'mssql',
                     'connect' => 'mssql_connect',
					 'host'    => '192.168.10.98',
					 'login'   => 'sa',
					 'password' => '08conf3!',
					 'database' => 'BSRes2',
					 'prefix'   => '',
					 'persistent' => false);

  var $spectel_fr = Array( 'driver' => 'mssql',
                     'connect' => 'mssql_connect',
					 'host'    => '65.91.73.150',
					 'login'   => 'spectel',
					 'password' => 'spectel',
					 'database' => 'BSRes2',
					 'prefix'   => '',
					 'persistent' => false);
}
