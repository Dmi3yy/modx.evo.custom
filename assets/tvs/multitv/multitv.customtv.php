<?php
/**
 * multiTV
 *
 * @category 	customtv
 * @version 	1.5.6
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author		Jako (thomas.jakobi@partout.info)
 *
 * @internal    description: <strong>1.5.6</strong> Transform template variables into a sortable multi item list.
 * @internal    input option code: @INCLUDE/assets/tvs/multitv/multitv.customtv.php
 */
if (IN_MANAGER_MODE != 'true') {
	die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');
}

// set customtv (base) path
define(MTV_PATH, 'assets/tvs/multitv/');
define(MTV_BASE_PATH, MODX_BASE_PATH . MTV_PATH);

if (!class_exists('multiTV')) {
	include MTV_BASE_PATH . 'multitv.class.php';
}

$multiTV = new multiTV($row);
echo $multiTV->generateScript();

//echo '<pre>'.print_r($row, TRUE).'</pre>';
?>