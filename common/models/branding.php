<?
class Branding extends AppModel
{
  var $name        = 'Branding';
  var $useTable    = 'brandings';
  var $primaryKey  = 'acctgrpid';

  var $validate = Array('webinterpoint_url' => VALID_URL);
}