<?php

class EmailTemplateComponent extends Object
{
  var $controller;
  var $template;
  var $room;

  function startup(&$controller)
  {
    $this->controller = $controller;
  }

  function render($template, $room, $account)
  {
    $this->template  = $template;
    $this->room      = $room;
    $this->branding  = $account['Branding'];
    $template_data   = file_get_contents(EMAIL_TEMPLATE_PATH . $template['Template']['filename']);
    $data = preg_replace_callback('/\[(\w+)\.(\w+)\]/', Array($this, 'variable_callback'), $template_data);

    return $data;
  }

  function variable_callback($parts)
  {
    $rv = '';

    if($parts[1] == 'account') { 
      $rv = $this->room['RoomView'][$parts[2]];
      
   } else if($parts[1] == 'dialinNo') { 
      $rv = $this->room['DialinNumber'][$parts[2]];
    } else if($parts[1] == 'contact') { 
      $rv = $this->room['Contact'][0][$parts[2]];
    } else if($parts[1] == 'webex') { 
      $rv = $this->room['WebexInfo'][$parts[2]];
    } else if($parts[1] == 'weblm') { 
      $rv = $this->room['LiveMeetingInfo'][$parts[2]];
    } else if($parts[1] == 'branding') { 
      if(!empty($this->branding[$parts[2]])){
         $rv = $this->branding[$parts[2]];
      } else {
         $rv = DEFAULT_WEBINTERPOINT_URL;
      }
    }
    
    return $rv;
  }
}
