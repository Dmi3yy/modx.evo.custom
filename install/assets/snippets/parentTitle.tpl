//<?php
/**
 * parentTitle
 * 
 * Берем имя родителя
 *
 * @category 	snippet
 * @version 	1.3
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 */


/*
[!parentTitle? &getID = `[*parent*]`!]
*/

//specify page id you want to get data for
$getID = $getID;
//get content field for page id
$tvOutput = $modx->getTemplateVarOutput('pagetitle', $getID);
$content = $tvOutput['pagetitle'];

//output the data to wherever the snippet is being called
return $content;