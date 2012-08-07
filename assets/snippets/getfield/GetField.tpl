<?php
/*
==================================================
	GetField
==================================================

Returns any document field or template variable from any document or any of its parents.

Author: Grzegorz Adamiak [grad]
Version: 1.3 beta @2006-11-08 14:40:04
License: LGPL
MODx: 0.9.2.1+

See GetField.txt for instructions and version history.
--------------------------------------------------
*/

/* Parameters
----------------------------------------------- */

# $docid [ int ]
# ID of the document for which to get a field content.
# Default: current document

$gfIntDocId = (isset($docid)) ? $docid : $modx->documentIdentifier;

# $field [ string ]
# Name of the field for which to get the content:
# - any of the document object fields (http://modxcms.com/the-document-object.html)
# - template variable
# Default: 'pagetitle'

$gfStrDocField = (isset($field)) ? trim($field) : 'pagetitle';

# $parent [ 0 | 1 ]
# If set to 1, the snippet will return value for the document parent.
# Default: 0

$gfBoolParent = (isset($parent)) ? $parent : 0;

# $parentLevel [ int ]
# Specifies how high in the document tree to search for the parent of the document:
# - $parentLevel = 0 - returns the ultimate parent (right under site root)
# - $parentLevel = 1 - returns the direct parent
# Default: 0

$gfIntParentLevel = (isset($parentLevel) && is_int((int) $parentLevel)) ? $parentLevel : 0;

# $topid [ int ]
# Id of the topmost document in the document tree under which to search for a parent. Used only with $parent set to 1.
# Default: 0

$gfIntTopDocId = (isset($topid) && is_int((int) $topid)) ? $topid : 0;

/* Do not edit the code below!
----------------------------------------------- */

# Include logic
include_once('assets/snippets/getfield/getfield.inc.php');

# Get parent document ID
if ($gfBoolParent)
	$gfIntDocId = gfGetParentId($modx, $gfIntDocId, $gfIntTopDocId, $gfIntParentLevel);

# Get content of the field
$output = gfGetFieldContent($modx,$gfIntDocId,$gfStrDocField);

unset($gfIntDocId, $gfStrDocField, $gfBoolParent, $gfIntParentLevel, $gfIntTopDocId);

return $output;
?>