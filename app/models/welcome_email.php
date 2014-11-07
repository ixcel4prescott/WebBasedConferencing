<?

class WelcomeEmail extends AppModel
{
   var $primaryKey = 'id';
   var $useTable   = false;


   var $validate = Array( 'class_spectel'    => VALID_NOT_EMPTY,
			  'template_spectel' => VALID_NOT_EMPTY,
			  'class_oci'        => VALID_NOT_EMPTY,
			  'template_oci'     => VALID_NOT_EMPTY,
			  'sender'           => VALID_NOT_EMPTY );

   function beforeValidate()
   {     
      if($this->data['WelcomeEmail']['sender'] == 'other')
       $this->validate['other_sender'] = VALID_EMAIL;
     

      foreach($this->data['WelcomeEmail']['add_recipients'] as $i)
	if(!empty($i) && preg_match(VALID_EMAIL, $i) == 0)
	  $this->invalidate('add_recipients');

      foreach($this->data['WelcomeEmail']['add_bccs'] as $i)
	if(!empty($i) && preg_match(VALID_EMAIL, $i) == 0)
	  $this->invalidate('add_bccs');

     return true;
   }
}