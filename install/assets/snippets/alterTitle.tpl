//<?php
/**
 * alterTitle
 * 
 * Pagetitle если Longtitle пуст 
 *
 * @category 	snippet
 * @version 	1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 * @internal    @installset base, sample
 */

<?php
//[[alterTitle? &id = `[+id+]`]] к примеру для вывода в Ditto
$id = isset($id) ? (int) $id : 0;
if ($id) {
	$arr = $modx->getPageInfo($id,1,'pagetitle,longtitle');
} else {
	$arr['pagetitle'] = $modx->documentObject['pagetitle'];
	$arr['longtitle'] = $modx->documentObject['longtitle'];
}
$title = strlen($arr['longtitle']) ? $arr['longtitle'] : $arr['pagetitle']; 
return $title;
?>
