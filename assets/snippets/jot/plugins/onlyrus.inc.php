<?php
function onlyrus(&$object,$params){
	global $modx;
	
	if ($params['name'] == 'content' && !preg_match('/[а-яА-Я]{1,}/', $params['value']))  return array(0,'Sorry, comments are closed');

}
?>