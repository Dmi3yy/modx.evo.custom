<?php
define('MODX_MANAGER_PATH', '../../../manager/'); //relative path for manager folder
require_once(MODX_MANAGER_PATH . 'includes/config.inc.php'); //config
require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');

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
	// document with docId editable?
	$docObj = $modx->getDocuments(array($docid), 1, 0, 'id');
	if (count($docObj)) {
		// get the settings for the multiTV
		$tvSettings = $modx->getTemplateVar($tvid, '*', $docid);
		if ($tvSettings && $tvSettings[elements] = '@INCLUDE/assets/tvs/multitv/multitv.customtv.php') {
			$multiTV = new multiTV($tvSettings);
			//die(print_r($multiTV, TRUE));
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
	$answer['msg'] = 'Illegal parameter!';
}
echo json_encode($answer);
exit();
?>