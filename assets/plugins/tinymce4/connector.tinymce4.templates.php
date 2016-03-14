<?php
// Get Template from resource for TinyMCE4
// Based on get_template.php for TinyMCE3 by Yamamoto
//
// Changelog:
// @author Deesen / updated: 12.03.2016

$self = 'assets/plugins/tinymce4/connector.tinymce4.templates.php';
$base_path = str_replace($self, '', str_replace('\\', '/', __FILE__));

define('MODX_API_MODE','true');
define('IN_MANAGER_MODE','true');
include_once("{$base_path}index.php");
include_once("{$base_path}assets/lib/class.modxRTEbridge.php");

$bridge = new modxRTEbridge('tinymce4', __FILE__);
$templatesArr = $bridge->getTemplateChunkList();    // $templatesArr could be modified/bridged now for different editors before sending

// Make output a real JavaScript file!
header('Content-type: application/x-javascript');
header('pragma: no-cache');
header('expires: 0');
echo json_encode($templatesArr);