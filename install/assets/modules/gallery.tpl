// <?php
/**
 * EvoGallery
 * 
 * Gallery Management Module
 * 
 * @category	module
 * @version 	1.1 Beta 1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties	&docId=Root Document ID;integer;0 &phpthumbImage=PHPThumb config for images in JSON;textarea;{'w': 940, 'h': 940, 'q': 95} &phpthumbThumb=PHPThumb config for thumbs in JSON;textarea;{'w': 175, 'h': 175, 'q': 75} &savePath=Save path;string;assets/galleries &keepOriginal=Keep original images;list;Yes,No;Yes &randomFilenames=Random filenames;list;Yes,No;No 
 * @internal	@guid 23636a8c613426979b9dea1ff0415abf
 * @internal	@shareparams 1
 * @internal	@dependencies requires files located at /assets/modules/evogallery/
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base, sample
 */

/**
 * EvoGallery
 * Gallery Management Module
 * Written by Brian Stanback
 * jQuery rewrite and updates by Jeff Whitfield <jeff@collabpad.com>
 */

$params['modulePath'] = $modx->config['base_path'].'assets/modules/evogallery/';
include_once($params['modulePath'] . "classes/maketable.class.inc.php");
include_once($params['modulePath'] . "classes/management.class.inc.php");

if (class_exists('GalleryManagement'))
	$manager = new GalleryManagement($params);
else
	$modx->logEvent(1, 3, 'Error loading Portfolio Galleries management module');

$manager->checkGalleryTable();

echo $manager->execute();
