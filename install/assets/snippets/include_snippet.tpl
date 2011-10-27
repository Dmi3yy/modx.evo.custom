//<?php

/**
 * include
 *  
 * Include non-cached chunks and snippets from files
 *  
 * @category 	   snippet
 * @version 	   1.1
 * @license 	   http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	   @properties 
 * @internal	   @modx_category
 */

$output = '';

#############################################
if(!empty($file)){

  if(file_exists(MODX_BASE_PATH.$file))
    $output = include MODX_BASE_PATH.$file;
  else
    $output = 'File not found: '.$file;

#############################################
}else if(!empty($chunk)){
  
  if(!function_exists('fetchTpl')){
    function fetchTpl($tpl){
    	global $modx;
      $template = "";
    	if(substr($tpl, 0, 6) == "@FILE:"){
    	  $tpl_file = MODX_BASE_PATH . substr($tpl, 6);
    		$template = file_get_contents($tpl_file);
    	}else if(substr($tpl, 0, 6) == "@CODE:"){
    		$template = substr($tpl, 6);
    	}else if($modx->getChunk($tpl) != ""){
    		$template = $modx->getChunk($tpl);
    	}else{
    		$template = false;
    	}
    	return $template;
    }
  }
  
  if(!isset($parse)) $parse = true;
  
  $output = fetchTpl($chunk);
  if($parse){
    $output = str_replace(array('[!','!]'),array('[[',']]'),$output);
    $output = $modx->parseDocumentSource($output);
  }

#############################################
}else if(!empty($placeholder)){

  $output = isset($modx->placeholders[$placeholder]) ? $modx->placeholders[$placeholder] : '';
  
#############################################
}else if(!empty($session)){

  $output = isset($_SESSION[$session]) && (!is_array($_SESSION[$session])) ? (string) $_SESSION[$session] : '';
  
}

return $output;

