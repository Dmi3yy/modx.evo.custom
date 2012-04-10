<?php
// ** START FOR MODx
$base_path = str_replace('\\','/',realpath('../../../')) . '/';
define('IN_MANAGER_MODE', 'true');
define('MODX_API_MODE',true);
include_once("{$base_path}index.php");
if(!isset($_SESSION['mgrValidated']))
{
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
$modx->db->connect();
include("{$base_path}manager/includes/settings.inc.php");
include("{$base_path}manager/includes/user_settings.inc.php");

$IMConfig['modx']['folder_permissions'] = octdec($new_folder_permissions);
// ** END FOR MODX
	
/**
 * Image Manager configuration file.
 * @author $Author: Wei Zhuo $
 * @version $Id: config.inc.php 27 2004-04-01 08:31:57Z Wei Zhuo $
 * @package ImageManager
 */
$IMConfig['base_dir'] = $rb_base_dir;
$IMConfig['base_url'] = $rb_base_url;
$IMConfig['safe_mode'] = false;
define('IMAGE_CLASS', 'GD');
$IMConfig['thumbnail_prefix'] = '.';
$IMConfig['thumbnail_dir'] = '.thumbs';
$IMConfig['allow_new_dir'] = true;
$IMConfig['allow_upload'] = true;
$IMConfig['validate_images'] = true;
$IMConfig['default_thumbnail'] = 'img/default.gif';
$IMConfig['thumbnail_width'] = 96;
$IMConfig['thumbnail_height'] = 96;
$IMConfig['tmp_prefix'] = '.editor_';
