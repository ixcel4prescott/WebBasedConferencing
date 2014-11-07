<?
class ContactView extends AppModel
{
  var $name        = 'ContactView';
  var $useTable    = 'contacts_view';
  var $primaryKey  = 'id';

  var $belongsTo = Array( 'Account'      => Array( 'className'  => 'Account',
						   'foreignKey' => 'acctgrpid' ),

			  'Status'       => Array( 'className'  => 'Status', 
						   'foreignKey' => 'status' ) );

}