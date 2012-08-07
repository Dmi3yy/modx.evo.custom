<?php
function nolink(&$object,$params){
	global $modx;
	
	if ($params['name'] != 'email' && preg_match ('/href|url|http|www|\.ru|\.com|\.net|\.info|\.org/i', $params['value'])) return array(0,'Ссылки запрещены');

}
?>