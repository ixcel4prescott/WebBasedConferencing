<?php
	class ErrorHelper extends Helper {
	function showMessage($target) {
		list($model, $field) = explode('/', $target);
 
		if (isset($this->validationErrors[$model][$field])) {
			return $this->validationErrors[$model][$field];
		} else {
			return null;
		}
	}
	
	function showAllMessages($model) {
		if (!empty($this->validationErrors[$model])) {
			$msg = "";
            
			foreach($this->validationErrors[$model] as $error)
				$msg .= $error."<br />";
				
			return $msg;
		} else {
			return null;
		}
	}
}
?>