<?

class Pci extends AppModel
{
   var $primaryKey  = 'acctgrpid';
   var $useTable    = 'pci';

   var $validate = Array( 'name_on_card'   => VALID_NOT_EMPTY,
			  'street_address'    => VALID_NOT_EMPTY,
			  'postal_code'       => VALID_NOT_EMPTY,
			  'card_type'            => VALID_NOT_EMPTY,
			  'cvv'               => VALID_NOT_EMPTY,
			  'first_name' => VALID_NOT_EMPTY,
			  'last_name'  => VALID_NOT_EMPTY,
			  'city'      => VALID_NOT_EMPTY,
			  'state'     => VALID_NOT_EMPTY,
			  'country'   => VALID_NOT_EMPTY,
			  'phone_number'     => VALID_NOT_EMPTY);

   var $key = 'PciPair';

   function shouldValidate($i)
   {
     if(!empty($i['Pci']['creditcard']) || !empty($i['Pci']['ccholdername']) || !empty($i['Pci']['ccholderzip']) || !empty($i['Pci']['ccholderstreet']) || 
	!empty($i['Pci']['ccexpire_month']) || !empty($i['Pci']['creditcard']))
       $rv = true;
     else
       $rv = false;

     return $rv;
   }

   function beforeValidate()
   {
     parent::beforeValidate();

     $this->data['Pci']['card_number'] = preg_replace('/\W/', '', $this->data['Pci']['card_number']);

     if(!$this->validateCC($this->data['Pci']['card_number']))
       $this->invalidate('card_number');

     if(empty($this->data['Pci']['ccexpire_month']) || empty($this->data['Pci']['ccexpire_year'])) {
       $this->invalidate('ccexpire');
       $this->invalidate('ccexpire_month');
       $this->invalidate('ccexpire_year');
     } else if(strtotime(sprintf('%s-%s-01', $this->data['Pci']['ccexpire_year'], $this->data['Pci']['ccexpire_month'])) < time()) {
       $this->invalidate('ccexpire');
     }

    return true;
   }

   function save($i)
   {
     $this->set($i);
     
     if($this->validates($i)) {
       if(isset($i['Pci']))
         $i = $i['Pci'];

       $data = Array('acctgrpid'      => $i['acctgrpid'],
		      'name_on_card'          => $i['name_on_card'],
              'first_name'            => $i['first_name'],
              'last_name'             => $i['last_name'],
		      'street_address'        => $i['street_address'],
		      'postal_code'           => $i['postal_code'],
		      'city'                  => $i['city'], 
		      'state'                 => $i['state'], 
		      'country'               => $i['country'], 
              'phone_number'          => $i['phone_number'],
		      'card_type'             => $i['card_type'], 
              'card_number'           => $i['card_number'],
		      'cvv'                   => $i['cvv'], 
              'expiration_date_month' => (int)$i['ccexpire_month'],
              'expiration_date_year'  => (int)$i['ccexpire_year']);

       $headers = Array('AUTH_TOKEN: ' . AUTH_TOKEN); 

       set_time_limit(0);
       $url  = BILLING_CREDIT_CARD_WEBHOOK_URL . $i['acctgrpid'];
       $rv = json_decode(http_post($url, $data, $headers), true);
       if (!empty($rv)){
         foreach ($rv as $key => $value){
           $this->invalidate($key);
         }
       } else {
         $rv = null;
       }
       $this->notify();
     } else {
       $rv = null;
     }

     return $rv;
   }   

   function wipe($i)
   {
     //Might be going away
     if(isset($i['Pci']))
       $i = $i['Pci'];

     return $this->sproc('WipePCI', Array('acctgrpid' => $i['acctgrpid']));
   }
   
   function notify()
   {
     $layout = VIEWS . 'layouts' . DS . NOTIFICATION_LAYOUT . '.thtml';
     $view   = NOTIFICATIONS_PATH . DS . 'billing_update.thtml';

     if(is_file($view)) {
       $out = render_template($layout, $view, Array('request' => $request));
    
       common_vendor('phpmailer/class.phpmailer');
       $mailer = new PHPMailer();

       $mailer->From     = MAILER_FROM;
       $mailer->FromName = MAILER_FROMNAME;
       $mailer->Host     = MAILER_HOST;
       $mailer->Mailer   = 'smtp';
       $mailer->Subject  = '[MyCA] Billing Update';

       $mailer->AddAddress(BILLING_EMAIL);
       $mailer->Body = $out;
       $mailer->IsHTML(true);

       $rv = $mailer->Send();
     }
   }

   function fetch($acctgrpid, $password)
   {
     list($rv) = $this->sproc('FetchPCI', Array('acctgrpid' => $acctgrpid, 'password' => $password));
     return $rv;
   }

   function validateCC($code)
   {
     $rv = false;
     
     if(!empty($code) and $type = $this->getCCType($code)){
       
       // http://en.wikipedia.org/wiki/Luhn_algorithm
       $sum  = 0;
       $code = strrev($code);

       for ($i=0; $i<strlen($code); $i++) {
	 $c = $code[$i];

	 if($i%2) {
	   $c *= 2;

	   if($c>9)
	     $c -= 9;
	 }

	 $sum += $c;
       }

       $rv = ($sum % 10) == 0;
     }

     return $rv;
   }

   function hasActivePci($acctgrpid)
   {
     return $this->findCount(Array('acctgrpid' => $acctgrpid, 'active' => 1)) > 0;
   }

   function getCCType($code)
   {
     $rv = null;

     if($code[0] == '4') {
       $rv = 'Visa';
     } elseif(substr($code,0,2) >= 51 && substr($code,0,2) <= 55) {
       $rv = 'Mastercard';
     } elseif(substr($code,0,2) == '34' || substr($code,0,2) == '37') {
       $rv = 'AmericanExpress';
     } elseif(substr($code, 0, 4) == '6011') {
       $rv = 'Discover';
     }

     return $rv;
   }
}
