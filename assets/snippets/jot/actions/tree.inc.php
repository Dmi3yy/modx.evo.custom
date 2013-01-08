<?php
	// Display default
	@include_once(dirname(__FILE__).'/form.inc.php');
	@include_once(dirname(__FILE__).'/treecomments.inc.php');
	
	function tree_mode(&$object) {
		global $modx;
		
		//onSetDefaultOutput event
		if (null !== ($output = $object->doEvent($object->Get("onSetDefaultOutput"))))	return $output;
		
		if(function_exists('treecomments_mode')) $output = treecomments_mode($object);
		if(function_exists('form_mode')) $output .= form_mode($object);
		return $output;
	}
?>
