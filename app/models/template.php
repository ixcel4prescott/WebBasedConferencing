<?
class Template extends AppModel
{
   var $primaryKey  = 'id';
   var $useTable    = 'icamemailtemplate';

   function templates($reseller, $classid)
   {
     $sql = 'SELECT icamemailtemplate.* 
             FROM icamemailtemplate
             JOIN icamemailtemplateMap ON icamemailtemplate.id = icamemailtemplateMap.templateid
             WHERE icamemailtemplateMap.resellerid=%d AND icamemailtemplate.classid = %d';
     
     $rv = Array();
     
     foreach($this->query(sprintf($sql, $reseller, $classid)) as $i)
       $rv[$i[0]['id']] = $i[0]['name'];

     return $rv;
   }
}