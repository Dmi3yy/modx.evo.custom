<?php
/* 
	*	Returns any document field or template variable from any document
	*	[!DocInfo? &id=`20`!] — заголовок ресурса с id 20
   *  [!DocInfo? &id=`20` &info=`introtext`!] — содержимое introtext ресурса с id 20
   *  [!DocInfo? &id=`20` &tv=`price`!] — содержимое tv price ресурса с id 20
	*/
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
(isset($id)) ? $id : $id = $modx->documentIdentifier;
(isset($info)) ? $info : $info = 'pagetitle';
(isset($tv)) ? $info = $tv : '';
$o = (isset($tv)) ? $modx->getTemplateVarOutput($info, $id) : $modx->getDocument($id,$info);
return $o[$info];
?>