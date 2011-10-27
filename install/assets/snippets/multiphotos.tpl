<?php
/**
 * MultiPhotos
 * @category 	snippet
 * @version 	1.22
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Temus (temus3@gmail.com)
 */
 
$tvname = isset($tvname) ? $tvname : 'photos';
$outerTpl = isset($outerTpl) ? $modx->getChunk($outerTpl) : '<div class="thumbs">[+photos+]</div>';
$rowTpl = isset($rowTpl) ? $modx->getChunk($rowTpl) : '<a href="[+link+]" id="thumb_[+num+]"><img src="[+url+]" alt="" title="[+title+]" /></a>';
$fid = isset($fid) ? $fid : false;

if (isset($id)) {
	$tvf = $modx->getTemplateVar($tvname,'*',$id);
	$tvv = $tvf['value'];
} else {
	$id = $modx->documentObject['id']; 
	$tvf = $modx->documentObject[$tvname];
	$tvv = $tvf[1];
}
if (!$tvv) return;
$fotoArr=json_decode($tvv);
$fotoRes=array();
$num=1;
if (!class_exists('PHxParser'))include_once(MODX_BASE_PATH.'assets/snippets/ditto/classes/phx.parser.class.inc.php');
foreach ($fotoArr as $v) {
	$phx = new PHxParser();
	$phx->setPHxVariable('url',$v[0]);
	$phx->setPHxVariable('link',$v[1]);
	$phx->setPHxVariable('title',$v[2]);
	$phx->setPHxVariable('num',$num);
	$fotoRes[$num] = $phx->Parse($rowTpl);
	$num++;
}
$output = $fid ? $fotoRes[$fid] : implode('',$fotoRes);
if (isset($random)) $output = $fotoRes[array_rand($fotoRes)];
if ($output) return str_replace('[+photos+]',$output,$outerTpl);
?>