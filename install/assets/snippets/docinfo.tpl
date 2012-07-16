//<?php
/**
 * DocInfo
 * 
 * Берем любое поле из любого документа (меньше запросов по сравнению с GetField)
 *
 * @category 	snippet
 * @version 	1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 */

/* 
	*	Returns any document field or template variable from any document
	*	[[DocInfo? &docid=`15` &field=`pagetitle`]] - Для стандартных полей ресурса
	*	[[DocInfo? &docid=`10` &tv=`1` &field=`tvname`]] - Для TV-параметров ресурса добавляем &tv=`1`
	*/
$docid = (isset($docid) && (int)$docid>0) ? (int)$docid : $modx->documentIdentifier;
$field = (isset($field)) ? $field : 'pagetitle';
$output='';
if(isset($tv) && $tv==1){
   $tv=$modx->getTemplateVar($field,'*',$docid,1);
   if($tv['value']!=''){
      $output=$tv['value'];
   }else{
      $output=$tv['defaultText'];
   }
}else{
   $doc=$modx->getPageInfo($docid,'1',$field);
   $output=$doc[$field];
}
return $output;