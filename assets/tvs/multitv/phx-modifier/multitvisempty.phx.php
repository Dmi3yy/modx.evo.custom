<?php
/*
 * description: check whether the multiTV is not empty
 * usage: [+phx:multitvisnotempty=`tvname|docid`:then=`xxx`:else=`yyy`+]
 * tvname = name of the multiTV
 * docid = id of the document with the multiTV. For use i.e. in ditto (defaults to current document)
 * mode = to get content from unpublished + published rscs, as option, mode = 1 (default) = published only, 0 = unpublished only, 2 = both
 */

$options = explode('|', $options);
$tvname = $tvname;
$docid = isset($docid) ? $docid : $modx->documentIdentifier;
$mode = isset($mode) ? $mode : 1;

//get published
if (($mode == 1) || ($mode == 2)) {
	$tvOutput = $modx->getTemplateVarOutput(array($tvname), $docid);
}

//get unpublished
if ((($tvOutput == false) && ($mode == 2)) || ($mode == 0)) {
	$tvOutput = $modx->getTemplateVarOutput(array($tvname), $docid, '0');
}

$tvOutput = json_decode($tvOutput, TRUE);
if ($tvOutput['fieldValue']) {
	$tvOutput = $tvOutput['fieldValue'];
}
$condition[] = intval($tvOutput == '[]' || $tvOutput == '');
?>
