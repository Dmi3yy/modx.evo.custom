<?php

error_reporting(0); // Set E_ALL for debuging

define('MODX_INC_PATH', '../../../../../manager/includes/');
require_once MODX_INC_PATH."protect.inc.php"; 
include_once MODX_INC_PATH."config.inc.php"; 
include_once MODX_INC_PATH."document.parser.class.inc.php";

$modx = new DocumentParser();
$modx->getSettings();

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';

function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

$opts = array(
	'locale' => 'ru_RU.UTF-8',
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => $modx->getConfig('rb_base_dir').'images/', 
			'URL'           => '../assets/images/', 
			'uploadOrder' => array('allow', 'deny'), 
			'uploadAllow' => array('image'), # allow any images
			'uploadMaxSize' => $modx->getConfig('upload_maxsize'),
			'accessControl' => 'access'
		),
		array(
			'driver'        => 'LocalFileSystem', 
			'path'          => $modx->getConfig('rb_base_dir').'flash/', 
			'URL'           => '../assets/flash/', 
			'uploadOrder' => array('allow', 'deny'),
			'uploadAllow' => array('application/x-shockwave-flash', 'application/flash-video'), # only swf and flv
			'uploadMaxSize' => $modx->getConfig('upload_maxsize'), 
			'accessControl' => 'access'
			
		)		
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

