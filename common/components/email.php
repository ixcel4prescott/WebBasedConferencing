<?php 

/**
 * Proxy Component Object for PHPMailer
 */
class EmailComponent
{
  var $m;
  var $controller;

  function startup(&$controller)
  {
    $this->controller = $controller;

    common_vendor('phpmailer/class.phpmailer');
    $this->m = new PHPMailer;

    $this->Host = MAILER_HOST;
    $this->Mailer = 'smtp';

    $this->From     = MAILER_FROM;
    $this->FromName = MAILER_FROMNAME;
  }

  // These accessors just delegate to the php mailer object, see docs for more info
  function __set($name, $value)
  {
    $this->m->{$name} = $value;
  }
    
  function __get($name)
  {
    if (isset($this->m->{$name})) {
      return $this->m->{$name};
    }
  }
  
  function __call($method, $args)
  {
    if (method_exists($this->m, $method)) {
      return call_user_func_array(array($this->m, $method), $args);
    }
  }

  function Send()
  {
    $rv = $this->m->Send();

    if(!$rv)
      $this->controller->systemLog('Email Error', $this->m->ErrorInfo);

    return $rv;
  }
  
  // Util to render templates
  function render($template, $layout)
  {
    $tmp = $this->controller->layout;
    $this->controller->autoRender = false;
    $this->controller->layout = $layout;
    ob_start();
    $this->controller->render($template);
    $rv = ob_get_clean();
    $this->controller->layout = $tmp;
    $this->controller->autoRender = 'auto';
    return $rv;
  }
  
  // Utils to render and set bodies
  function renderBody($template, $layout=EMAIL_LAYOUT) 
  {
    $this->Body = $this->render($template, $layout);
    $this->IsHTML(true);
  }

  function renderAltBody($template, $layout=EMAIL_ALT_LAYOUT)
  {
    $this->AltBody = $this->render($template, $layout);
  }
}
?>