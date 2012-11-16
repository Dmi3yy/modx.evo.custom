<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$tvname = isset($tvname) ? $tvname : 'files';
$outerTpl = isset($outerTpl) ? $modx->getChunk($outerTpl) : '<div class="files">[+files+]</div>';
$rowTpl = isset($rowTpl) ? $modx->getChunk($rowTpl) : '<p><img src="[+icon+]" alt="[+alt+]" title="[+alt+]" />&nbsp;<a href="[+url+]" id="file_[+num+]">[+title+]</a></p>';
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
$fileArr=json_decode($tvv);
$fileRes=array();
$num=1;
foreach ($fileArr as $v) {
	$pathinfo = pathinfo($v[0]);
	$ext = strtolower($pathinfo['extension']);
	switch($ext) {
		case 'gif': case 'jpg': case 'jpeg': case 'png': case 'tif': case 'tiff':  case 'bmp':
			$icon = 'icon_image';
			$alt = 'Изображение';
			break;
		case 'xls':
			$icon = 'icon_excel';
			$alt = 'Microsoft Excel';
			break;
		case 'html': case 'htm':
			$icon = 'icon_html';
			$alt = 'HTML';
			break;
		case 'pdf':
			$icon = 'icon_pdf';
			$alt = 'Adobe PDF';
			break;
		case 'ppt':
			$icon = 'icon_pp';
			$alt = 'Microsoft Powerpoint';
			break;
		case 'rtf': case 'txt': case 'css': case 'js': case 'xml':
			$icon = 'icon_text';
			$alt = 'Текст';
			break;
		case 'doc':
			$icon = 'icon_word';
			$alt = 'Microsoft Word';
			break;
		case 'zip': case 'rar': case 'tar': case '7z':
			$icon = 'icon_zip';
			$alt = 'Файл архива';
			break;
		default:
			$icon = 'icon_generic';
			$alt = 'Файл';
			break;
	}
	$icon=MODX_BASE_URL.'assets/fileicons/'.$icon.'.gif';
	$fields = array ('[+url+]','[+title+]','[+num+]','[+icon+]','[+alt+]');
	$values = array ($v[0],$v[1],$num,$icon,$alt);
	$fileRes[$num] = str_replace($fields, $values, $rowTpl);
	$num++;
}
$output = $fid ? $fileRes[$fid] : implode('',$fileRes);
if (isset($random)) $output = $fileRes[array_rand($fileRes)];
if ($output) return str_replace('[+files+]',$output,$outerTpl);
return '';
?>