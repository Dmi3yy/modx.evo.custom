/**
 * EvoGallery
 *
 * Plugin for delete images on empty trash
 *
 * @category	plugin
 * @version	1.1 Beta 1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@guid 23636a8c613426979b9dea1ff0415abf
 * @internal    @events OnEmptyTrash
 * @internal    @disabled 1
 */

if (!isset($params['modulePath'])) $params['modulePath'] = $modx->config['base_path'].'assets/modules/evogallery/';
include_once($params['modulePath'] . "classes/management.class.inc.php");
if (class_exists('GalleryManagement'))
	$manager = new GalleryManagement($params);
else
	$modx->logEvent(1, 3, 'Error loading Portfolio Galleries management module');
$e =& $modx->event;
switch ($e->name ) {
    case 'OnEmptyTrash':
		$manager->deleteImages('contentid',$ids);
		break ;
    default:
        return ;
}
