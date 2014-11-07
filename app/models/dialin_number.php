<?php

class DialinNumber extends AppModel
{
   var $primaryKey  = 'id';
   var $useTable    = 'dialinNo';
   
   var $validate = array( 'description' => VALID_NOT_EMPTY,
			  'bridge'      => VALID_NOT_EMPTY, 
			  'tollfreeno'  => '/\(\d{3}\) \d{3}\-\d{4}/', 
			  'tollno'      => '/\(\d{3}\) \d{3}\-\d{4}/');

   function beforeValidate()
   {
     if(empty($this->data['DialinNumber']['resellers']))
       $this->invalidate('resellers');

     return true;
   }

   function afterSave()
   {
     $id = $this->getID();
     if(!$id)
       $id = $this->getLastInsertId();
     
     $this->alignResellers($id, $this->data['DialinNumber']['resellers']);

     $this->__insertID = $id;
      
     return true;
   }

   function alignResellers($dialinNoid, $resellerids)
   {
     $this->query(sprintf('DELETE FROM dialinNoMap where dialinNoid=%d', $dialinNoid));
 
    $sql = 'INSERT INTO dialinNoMap ([resellerid], [dialinNoid]) %s;';

    $rows = Array();
    foreach($resellerids as $resellerid)
      $rows[] = sprintf('SELECT %d, %d', $resellerid, $dialinNoid);

    return $this->execute(sprintf($sql, implode(' UNION ALL ', $rows)));
   }

   function getAlignedResellers($dialinNoid)
   {
     $rv = Array();

     foreach($this->query(sprintf('SELECT resellerid FROM dialinNoMap WHERE dialinNoid=%d', $dialinNoid)) as $i)
       $rv[] = $i[0]['resellerid'];

     return $rv;
   }

   function getResellers($dialinNoid)
   {
     $rv = Array();
     $sql = 'SELECT * FROM reseller
             JOIN dialinNoMap ON dialinNoMap.resellerid = reseller.resellerid
             WHERE dialinNoMap.dialinNoid=%d ORDER BY reseller.name';

     foreach($this->query(sprintf($sql, $dialinNoid)) as $i)
       $rv[] = $i[0];

     return $rv;
   }

   function setDefault($dialinNoid, $resellerids)
   {
     $rv = false;

     $resellerids = implode(',', $resellerids);

     $deactivate_sql = 'UPDATE dialinNoMap
                        SET [default]=0
                        FROM dialinNoMap, dialinNo
                        WHERE dialinNoMap.dialinNoid = dialinNo.id AND dialinNoMap.resellerid IN (%s) AND dialinNo.bridge="%s" AND dialinNoMap.[default]=1';

     $activate_sql = 'UPDATE dialinNoMap
                      SET [default]=1
                      WHERE dialinNoMap.resellerid IN (%s) AND dialinNoid=%d';

     if($dialin = $this->read(null, $dialinNoid)) {
       if($this->query(sprintf($deactivate_sql, $resellerids, $dialin['DialinNumber']['bridge'])) && 
	  $this->query(sprintf($activate_sql, $resellerids, $dialinNoid)))
	 $rv = true;
     } 

     return $rv;
   }

   function get($resellerid, $bridge) {
     $sql = 'SELECT dialinNo.id, dialinNo.description, dialinNo.tollfreeno, dialinNo.tollno, dialinNoMap.[default]
             FROM dialinNo
             JOIN dialinNoMap ON dialinNoMap.dialinNoid = dialinNo.id
             WHERE dialinNoMap.resellerid=%d AND dialinNo.bridge="%s"
             ORDER BY dialinNo.description';

     return $this->query(sprintf($sql, $resellerid, $bridge));
   }

   function getDefault($resellerid, $bridge)
   {
     $sql = 'SELECT TOP 1 dialinNo.id, dialinNo.description, dialinNo.tollfreeno, dialinNo.tollno, dialinNoMap.[default]
             FROM dialinNo
             JOIN dialinNoMap ON dialinNoMap.dialinNoid = dialinNo.id
             WHERE dialinNoMap.resellerid=%d AND dialinNo.bridge="%s" AND dialinNoMap.[default]=1';

     $rv = $this->query(sprintf($sql, $resellerid, $bridge));
     if($rv)
       $rv = $rv[0][0];

     return $rv;
   }
}