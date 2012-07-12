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
 */

/*
[[alterTitle? &id = `[+id+]`]] к примеру для вывода в Ditto
*/
$id = isset($id) ? $id : $modx->documentIdentifier;  
$arr = $modx->getPageInfo($id,1,'pagetitle,longtitle');
$title = (strlen($arr["longtitle"])>0) ? $arr["longtitle"] : $arr["pagetitle"]; 
return $title;