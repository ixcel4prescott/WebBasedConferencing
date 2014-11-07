<?

class Operator extends AppModel
{
   var $primaryKey = 'LoginName';
   var $useTable   = 'UserLogon';

   function buildOperatorList()
   {
     $rv = Array();

     $ops = $this->generateList(Array('isOperator' => 1), 'LoginName ASC', null , 
				'{n}.Operator.LoginName', '{n}.Operator.LoginName');
     
     foreach($ops as $k => $v) {
       $parts = explode(' ', $k);
       $rv[$parts[0]] = $v;
     }
     
     return $rv;
   }
}
