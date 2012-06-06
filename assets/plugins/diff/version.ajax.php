<?
/**************************************

/** 
* Diff plugin for Modx Evo
*
* en: Class to work with the history of changes in snippets, chunks, templates, modules and plugins
* ru: Класс для работы с историей изменений в сниппетах, чанках, шаблонах, модулях и плагинах
*
* @version 2.0
* @author Borisov Evgeniy aka Agel Nash (agel_nash@xaker.ru)
* @date 30.05.2012
* @copyright 2012 Agel Nash
* @link http://agel-nash.ru
* 
*/

/*************************************/
header("Content-type: text/html; charset=UTF-8");

$database_type = "";
$database_server = "";
$database_user = "";
$database_password = "";
$dbase = "";
$table_prefix = "";
$base_url = "";
$base_path = "";

if(isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'].'/manager/includes/protect.inc.php')){
	require_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/protect.inc.php'; 
	if (!$rt = @include_once $_SERVER['DOCUMENT_ROOT']."/manager/includes/config.inc.php") {
		return;
    }
}else{
	return;
}

define('MODX_API_MODE', true); 
require_once(MODX_BASE_PATH.'manager/includes/document.parser.class.inc.php'); 
$modx = new DocumentParser; 
/*
*	en: The database we don't need
*	ru: База данных нам не нужна
*/
//$modx->db->connect(); 
$modx->getSettings();
startCMSSession();


$active=isset($_GET['active'])?$_GET['active']:'';

//in_array($_GET['active'],array('snippet','template','plugin','module','chunk'))
if(!(isset($_SESSION['mgrPermissions']['edit_'.$active]) && isset($_SESSION['mgrPermissions']['save_'.$active]) && $_SESSION['mgrPermissions']['save_'.$active]==1 && $_SESSION['mgrPermissions']['edit_'.$active]==1 && isset($_SESSION['mgrPermissions']['delete_'.$active]) && $_SESSION['mgrPermissions']['delete_'.$active]==1)){
	return;
}
	
	
$mode=isset($_GET['mode'])?$_GET['mode']:'';

/*
*	en: __DIR__ Not available on windows machines
*	ru: __DIR__ не доступна на windows машинах
*/
$dir=pathinfo(__FILE__);
$dir=$dir['dirname'];

if(isset($_GET['file']) && $_GET['file']!='' && isset($_GET['id']) && (int)$_GET['id']>0 && file_exists($dir.'/'.$active.'/'.(int)$_GET['id']."/".$_GET['file'])){
	$file=$dir.'/'.$active.'/'.(int)$_GET['id']."/".$_GET['file'];
}else{
	return;
}

switch($mode){
	case 'load':{
		$data=file_get_contents($file);
		if(strlen($data)>0){
			echo base64_decode($data);
		}
		break;
	}
	case 'del':{
		$flag=unlink($file);
		if(!$flag){
			break;
		}
		$data=unserialize(file_get_contents($dir.'/'.$active.'/version.inc'));
		if(!isset($data[(int)$_GET['id']])){
			break;
		}
		$tmp=$data[(int)$_GET['id']];
		unset($tmp['last']);
		foreach($tmp as $id=>$item){
			if($item['file']==$_GET['file']){
				unset($data[(int)$_GET['id']][$id]);
			}
		}
		if(file_put_contents($dir.'/'.$active.'/version.inc',serialize($data))){
			echo 'good';
		}
		break;
	}
}
return;
?>