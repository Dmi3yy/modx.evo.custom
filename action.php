<?php
$base_path = str_replace('\\','/',dirname(__FILE__)) . '/';
define('MODX_API_MODE', true);
require_once("{$base_path}index.php");
$modx->db->connect();
$modx->getSettings();
$modx->invokeEvent('OnWebPageInit');
if(isset($_GET['include']))
{
	$path = $_GET['include'];
	if(strpos($path, 'manager/')===0 && substr($path,strrpos($path,'.'))==='.php')
	{
		$path = MODX_BASE_PATH . $path;
		if(file_exists($path)) include_once($path);
	}
}
