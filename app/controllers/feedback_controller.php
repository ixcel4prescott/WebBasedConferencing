<?

class FeedbackController extends AppController
{
  var $uses = Array('Feedback');
  var $components = Array('Email');

  function index()
  {
    $this->set('types', Array('Bug Report' => 'Bug Report', 'Feedback' => 'Feedback'));
    
    if(!empty($this->data)) {
      $this->Feedback->set($this->data);
      if($this->Feedback->validates($this->data)) {
	$user = $this->Session->read('User');
       
	$this->set('user', $user);
	$this->set('feedback', $this->data);
       
	$this->Email->Subject = 'MyCA Admin ' . $this->data['Feedback']['type'];
	$this->Email->AddAddress('it-support@infiniteconferencing.com');

	$this->Email->From     = $user['User']['email'];
	$this->Email->FromName = $user['User']['name'];

	if(is_uploaded_file($this->data['Feedback']['screenshot']['tmp_name']))
	  $this->Email->AddAttachment($this->data['Feedback']['screenshot']['tmp_name'], $this->data['Feedback']['screenshot']['name']);

	$this->Email->renderBody('email\feedback');
	$this->Email->Send();

	$this->Session->setFlash('Your feedback has been submitted and will be reviewed shortly');
	$this->redirect('/');
      }
    }
  }
}