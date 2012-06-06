<?php
require_once "manager/includes/protect.inc.php";
include_once "manager/includes/config.inc.php";
include_once "manager/includes/document.parser.class.inc.php";

$modx = new DocumentParser();

$mod_table = $modx->getFullTableName("site_easyadvt"); //таблица модуля

// куда переходить, если в таблице не найден линк
$error_link = 'http://'.$_SERVER['HTTP_HOST'].'/page_not_found';

/*
кому 
1. лень один раз прописать линк руками 
2. не жалко сервер (лишние запросы)
3. хочет чтобы линк на страницу 404 брался из конфига MODx
могут расскомментировать :)

$sql = "SELECT setting_value FROM ".$modx->getFullTableName('system_settings')." WHERE setting_name='error_page'";
$error_page = $modx->db->getValue($modx->db->query($sql));
$error_link = $modx->makeUrl((int)$error_page, '', '', 'full');
*/

$id = (int)$_GET['id'];

if ($id <= 0 ) 
	$modx->sendRedirect($error_link, 0, '', 'HTTP/1.1 404 Not found');
else {
	
	$sql = "SELECT link FROM ".$mod_table." WHERE id = ".$id;
	$link = $modx->db->getValue($modx->db->query($sql));
	
	if (empty($link)) 
		$modx->sendRedirect($error_link, 0, '', 'HTTP/1.1 404 Not found');
	else {
		$sql = "UPDATE ".$mod_table." SET jump_count=jump_count+1 WHERE id=".$id;
		$modx->db->query($sql);	
		$modx->sendRedirect($link);
	}
	
}
