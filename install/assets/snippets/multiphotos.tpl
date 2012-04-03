<?php
/**
 * MultiPhotos
 *
 * MultiPhotos
 *
 * @category 	snippet
 * @version 	1.26
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Temus (temus3@gmail.com)
 * @internal	@modx_category add
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
if (!$tvv || $tvv=='[]') return;
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
#################### PAGINATION ####################
$count_per_page = isset($display) ? $display : 10;
$tplLinkNext = '<a href="[+link+]">>></a>';
$tplLinkPrev = '<a href="[+link+]"><<</a>';
$tplLinkNav = '<div class="mp_pages">[+linkprev+] [+pages+] [+linknext+]</div>';
$mp_pagecount=ceil(count($fotoRes)/$count_per_page);
if (!empty($pagination) && $mp_pagecount > 1){
	$mp_currentpage = isset($_GET["page"]) ? intval($_GET["page"]): 1;
	if ($mp_currentpage > $mp_pagecount || $mp_currentpage < 1) { $mp_currentpage = 1; }
	$char = ($modx->config['friendly_urls'] == 0) ? "&" : "?";
	$url = $modx->makeurl($modx->documentObject["id"],'',$char.'page=');
	$prevpage = $mp_currentpage-1;	$nextpage = $mp_currentpage+1;
	$linkprev = ($prevpage>0) ? str_replace("[+link+]",$url.$prevpage,$tplLinkPrev) : '';
	$linknext = ($nextpage>$mp_pagecount) ? '' : str_replace("[+link+]",$url.$nextpage,$tplLinkNext);
	$tplPages = str_replace("[+linkprev+]",$linkprev,$tplLinkNav);
	$tplPages = str_replace("[+linknext+]",$linknext,$tplPages);
	$pages='';
	for ($i=1;$i<=$mp_pagecount;$i++){
		$pages .= ($i==$mp_currentpage) ? '<span class="mp_currentpage">'.$i.'</span>' : '<a class="mp_page" href="'.$url.$i.'">'.$i.'</a>';
		$pages .= ($i==$mp_pagecount) ? '' : ' | ';
	}
	$tplPages=str_replace("[+pages+]",$pages,$tplPages);
	$fotoRes=array_slice($fotoRes,$count_per_page*$mp_currentpage-$count_per_page,$count_per_page);
	$outerTpl .= $tplPages;
}
#####################################################
$output = $fid ? $fotoRes[$fid] : implode('',$fotoRes);
if (isset($random)) $output = $fotoRes[array_rand($fotoRes)];
if ($output) return str_replace('[+photos+]',$output,$outerTpl);
?>