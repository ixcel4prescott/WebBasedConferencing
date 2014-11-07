<?

class User extends AppModel
{
  var $name         = 'User';
  var $useTable     = 'myca_users';

  var $validate = array( 'name'          => VALID_NOT_EMPTY,
			 'company_name'  => VALID_NOT_EMPTY,
			 'email'         => VALID_EMAIL,
			 'password'      => '/......+/' );

  var $hasOne = Array('SalespersonGroup' => Array( 'className'  => 'SalespersonGroup', 
						   'foreignKey' => 'user_id' ),
		      'ResellerGroup'    => Array( 'className'  => 'ResellerGroup', 
						   'foreignKey' => 'user_id' )
		      );

  function beforeValidate() 
  {
    parent::beforeValidate();
    
    if(!empty($this->data['User']['password']) && isset($this->data['User']['confirm_password']) && 
       $this->data['User']['password'] != $this->data['User']['confirm_password'])
      $this->invalidate('password_mismatch');
	  
    return true;
  }
  
  function authenticate($ident, $password)
  {
    $user = $this->find(Array('OR'  => Array('User.username' => $ident, 
					     'User.email'    => $ident), 
			      'NOT' => Array('User.level_type' => Array('accountgroup', 'account'))));

    if($user && sha1($user['User']['salt'] . $password) == $user['User']['salted_password'])
      return $user;
    
    return false;
  }
  
  function generatePassword($syllables=2) 
  {
    return generate_password($syllables);
  }
}