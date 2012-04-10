<?php
if(!isset($_GET['target']) || empty($_GET['target'])) return;
if(strpos($_GET['target'],'..')!==false || strpos($_GET['target'],'~')!==false) return;

$target = $_GET['target'];
$jsdir = str_replace('\\','/',realpath(dirname(__FILE__))) . "/{$target}/";
if(!file_exists($jsdir) || !is_dir($jsdir)) return;
if(!function_exists('scandir')) include_once('../upgradephp/upgrade.php');
$files = scandir($jsdir,1);
if(0<count($files) && $files!=='..' && $files!=='.')
{
	header('Content-type: text/javascript');
	readfile("{$jsdir}{$files[0]}");
}
