<?php

define('MODX_API_MODE', true);

include_once("../../../../index.php");

$modx->db->connect();

if(empty ($modx->config)) {
	$modx->getSettings();
}

$modx->invokeEvent("OnWebPageInit");

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
	$modx->sendRedirect($modx->config['site_url']);
}

header('content-type: application/json');

if(isset($_REQUEST['a'])) {
	switch($_REQUEST['a']) {
		case '76': {

			if($_REQUEST['tab'] == 0) {
				$sql = $modx->db->query('SELECT t1.*, t1.templatename AS name
					FROM ' . $modx->getFullTableName('site_templates') . ' AS t1
					#LEFT JOIN ' . $modx->getFullTableName('categories') . ' AS t2 ON t2.id=t1.category
					ORDER BY t1.templatename ASC');
				$a = 16;
			} else if($_REQUEST['tab'] == 1) {
				$sql = $modx->db->query('SELECT t1.*
					FROM ' . $modx->getFullTableName('site_tmplvars') . ' AS t1
					#LEFT JOIN ' . $modx->getFullTableName('categories') . ' AS t2 ON t2.id=t1.category
					ORDER BY t1.name ASC');
				$a = 301;
			} else if($_REQUEST['tab'] == 2) {
				$sql = $modx->db->query('SELECT t1.*
					FROM ' . $modx->getFullTableName('site_htmlsnippets') . ' AS t1
					#LEFT JOIN ' . $modx->getFullTableName('categories') . ' AS t2 ON t2.id=t1.category
					ORDER BY t1.name ASC');
				$a = 78;
			} else if($_REQUEST['tab'] == 3) {
				$sql = $modx->db->query('SELECT t1.*
					FROM ' . $modx->getFullTableName('site_snippets') . ' AS t1
					#LEFT JOIN ' . $modx->getFullTableName('categories') . ' AS t2 ON t2.id=t1.category
					ORDER BY t1.name ASC');
				$a = 22;
			} else if($_REQUEST['tab'] == 4) {
				$sql = $modx->db->query('SELECT t1.*
					FROM ' . $modx->getFullTableName('site_plugins') . ' AS t1
					#LEFT JOIN ' . $modx->getFullTableName('categories') . ' AS t2 ON t2.id=t1.category
					ORDER BY t1.name ASC');
				$a = 102;
			}

			while($row = $modx->db->getRow($sql)) {
				echo '<li><a href="index.php?a=' . $a . '&id=' . $row['id'] . '" target="main">' . $row['name'] . ' (' . $row['id'] . ')</a></li>';
			}

			break;
		}
	}
}
