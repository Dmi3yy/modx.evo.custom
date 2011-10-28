<?php
/**
 * TvTable
 *
 * TvTable
 *
 * @category 	snippet
 * @version 	1.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Temus (temus3@gmail.com)
 * @internal	@modx_category add
 */

$tvname = isset($tvname) ? $tvname : 'prices';
$classname = isset($classname) ? $classname : 'pricelist';

if (isset($id)) {
	$tvt = $modx->getTemplateVar($tvname,'*',$id);
	$tvv = $tvt['value'];
} else {
	$id = $modx->documentObject['id']; 
	$tvt = $modx->documentObject[$tvname];
	$tvv = $tvt[1];
}
if (!$tvv || $tvv=='[["",""],["",""]]') return;
$tvtArr=json_decode($tvv);

$output='<table class="'.$classname.'">'."\n";
$output .='<tr>'."\n";
for($i=0; $i<count($tvtArr[0]); $i++) $output .='<th'.($i ? '' : ' class="first"').'>'.$tvtArr[0][$i].'</th>'."\n";
$output.='</tr>'."\n";
for($row=1; $row<count($tvtArr); $row++) {
	$output .='<tr'.(($row%2) ? '' : ' class="altrow"').'>'."\n";
	for($i=0; $i<count($tvtArr[$row]); $i++) $output .='<td'.($i ? '' : ' class="first"').'>'.$tvtArr[$row][$i].'</td>'."\n";
	$output.='</tr>'."\n";
}
$output.='</table>';
return $output;
?>