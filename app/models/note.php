<?

class Note extends AppModel
{
   var $primaryKey  = 'id';
   var $useTable    = 'myca_notes';

   var $belongsTo  = Array('User' => Array('className'  => 'User', 
					   'foreignKey' => 'user_id' ));

   var $validate   = Array( 'title' => VALID_NOT_EMPTY, 
			    'body'  => VALID_NOT_EMPTY );

   function get($entity, $object_id)
   {
     return $this->findAll(Array('Note.entity' => $entity, 'Note.object_id' => $object_id), null, 'Note.sticky DESC, Note.created DESC', 20);
   }
}