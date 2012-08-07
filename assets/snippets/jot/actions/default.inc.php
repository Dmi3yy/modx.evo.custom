<?php
	// Display default
	@include_once(dirname(__FILE__).'/form.inc.php');
	@include_once(dirname(__FILE__).'/comments.inc.php');
	
	function default_mode(&$object) {
		global $modx;
		
		//onSetDefaultOutput event
		if (null !== ($output = $object->doEvent("onSetDefaultOutput"))) return $output;
		
		if(function_exists('comments_mode')) $output = comments_mode($object);
		if(function_exists('form_mode')) $output .= form_mode($object);
		return $output;
	}
?>
