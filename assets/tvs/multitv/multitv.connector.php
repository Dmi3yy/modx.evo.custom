<?php
if (file_exists(dirname(__FILE__)."/../assets/cache/siteManager.php")) {
    include_once(dirname(__FILE__)."/../assets/cache/siteManager.php");
}else{
    define('MGR_DIR', 'manager');
}
define('MODX_MANAGER_PATH', '../../../'.MGR_DIR.'/'); //relative path for manager folder

require_once(MODX_MANAGER_PATH . 'includes/config.inc.php'); //config
require_once(MODX_MANAGER_PATH . 'includes/protect.inc.php');

// Setup the MODx API
define('MODX_API_MODE', TRUE);

// initiate a new document parser
include_once(MODX_MANAGER_PATH . '/includes/document.parser.class.inc.php');
$modx = new DocumentParser;

// provide the MODx DBAPI
$modx->db->connect();

// provide the $modx->documentMap and user settings
$modx->getSettings();

// set customtv (base) path
define(MTV_PATH, 'assets/tvs/multitv/');
define(MTV_BASE_PATH, MODX_BASE_PATH . MTV_PATH);

// include classfile
if (!class_exists('multiTV')) {
	include MTV_BASE_PATH . 'multitv.class.php';
}

// retrieve parameter
$action = isset($_POST['action']) ? preg_replace("/[^a-zA-Z0-9_-]+/", "", $_POST['action']) : FALSE;
$docid = isset($_POST['id']) ? intval($_POST['id']) : FALSE;
$tvid = isset($_POST['tvid']) ? intval(str_replace('tv', '', $_POST['tvid'])) : FALSE;

$answer = array();
if ($action && $docid && $tvid) {
	// document exists?
	$res = $modx->db->select('*', $modx->getFullTableName('site_content'), 'id=' . $docid);
	if ($modx->db->getRecordCount($res)) {
		// document with docId editable?
		$docObj = $modx->getPageInfo($docid, 0, '*');
		if ($docObj) {
			// get the settings for the multiTV
			$tvSettings = $modx->getTemplateVar($tvid, '*', $docid, $docObj['published']);
			if ($tvSettings && $tvSettings[elements] = '@INCLUDE/assets/tvs/multitv/multitv.customtv.php') {
				$multiTV = new multiTV($tvSettings);
				$includeFile = $multiTV->includeFile($action, 'processor');
				// processor available?
				if (substr($includeFile, 0, 1) != 'A') {
					include $includeFile;
				} else {
					$answer['error'] = TRUE;
					$answer['msg'] = 'Processor does not exist!';
				}
			} else {
				$answer['error'] = TRUE;
				$answer['msg'] = 'multiTV does not exist!';
			}
		} else {
			$answer['error'] = TRUE;
			$answer['msg'] = 'Insufficient rights for this action!';
		}
	} else {
		$answer['error'] = TRUE;
		$answer['msg'] = 'Document does not exists!';
	}
} else {
	$answer['error'] = TRUE;
	$answer['msg'] = 'Illegal parameter!';
}
echo json_encode($answer);
exit();
?>