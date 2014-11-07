<?

class Feedback extends AppModel
{
  var $useTable   = false;
  var $validate   = Array('subject' => VALID_NOT_EMPTY,
			  'body'    => VALID_NOT_EMPTY);
}