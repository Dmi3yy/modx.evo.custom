<?php
/*  
 * description: check whether the multiTV is not empty
 * usage: [+phx:multitvisnotempty=`tvname|docid`:then=`xxx`:else=`yyy`+]
 * tvname = name of the multiTV
 * docid = id of the document with the multiTV. For use i.e. in ditto (defaults to current document)
 */

$options = explode('|', $options);
$options[1] = isset($options[1]) ? $options[1] : $modx->documentIdentifier;
$tvOutput = $modx->getTemplateVarOutput(array($options[0]), $options[1]);
$tvOutput = trim($tvOutput[$options[0]]);
$condition[] = intval($tvOutput != '[]' && $tvOutput != '');
?>
