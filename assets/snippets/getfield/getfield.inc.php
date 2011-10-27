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

unset($docid, $field, $parent, $parentLevel, $topid);

# GetField functions
# ---------------------------------------------

# gfGetFieldContent
# Returns the inherited value of any content field
if (!function_exists(gfGetFieldContent))
{
	function gfGetFieldContent($modx,$gfIntDocId,$gfStrDocField)
	{
	/* apparently in 0.9.2.1 the getTemplateVarOutput function doesn't work as expected and doesn't return INHERITED value; this is probably to be fixed for next release; see http://modxcms.com/bugs/task/464
		$gfArrTV = $modx->getTemplateVarOutput($gfStrDocField,$gfIntDocId);
		return $gfArrTV[$gfStrDocField];
	*/

		while ($gfArrParent = $modx->getDocument($gfIntDocId,'parent'))
		{
			$gfArrTV = $modx->getTemplateVar($gfStrDocField,'*',$gfIntDocId);
			if (($gfArrTV['value'] && substr($gfArrTV['value'],0,8) != '@INHERIT') or !$gfArrTV['value']) // tv default value is overriden (including empty)
			{
				$output = $modx->getTemplateVarOutput($gfStrDocField,$gfIntDocId);
				$output = $output[$gfStrDocField];
				break;
			}
			else // there is no parent with default value overriden
			{
				$output = trim(substr($gfArrTV['value'],8));
			}
			$gfIntDocId = $gfArrParent['parent']; // move up one document in document tree
		} // end while

		return $output;
	}
}

# gfGetParentId
# Returns the parent document ID
if (!function_exists(gfGetParentId))
{
	function gfGetParentId($modx, $gfIntDocId, $gfIntTopDocId, $gfIntParentLevel)
	{
		# build an array of document ancestors IDs
		$gfArrParentIds = array (); // initialize;
		$gfArrParentIds[] = $gfIntDocId; // add the specified document ID on first place

		// get IDs of all parents back to root of the document tree
		while (($gfArrParent = $modx->getDocument($gfIntDocId,'parent')) && ($gfArrParent['parent'] != 0))
		{
			$gfIntDocId = $gfArrParent['parent']; // move up one document in the document tree
			$gfArrParentIds[] = $gfIntDocId; // add parent ID to the array
		} // end while
		unset($gfIntDocId, $gfArrParent);

		$gfIntParentsCount = count($gfArrParentIds); // number of the parents

		# determine the ID of the specified parent
		switch ($gfIntTopDocId)
		{
			case 0: // not set or set to the root of the document tree

				($gfIntParentLevel && ($gfIntParentLevel < $gfIntParentsCount)) ?
					($gfIntDocId = $gfArrParentIds[$gfIntParentLevel]) : // find parent in specified levels up
					($gfIntDocId = $gfArrParentIds[$gfIntParentsCount - 1]); // if not set return the topmost (ultimate) parent

				break;

			default: // set to any other document

				$gfIntParentKey = array_search($gfIntTopDocId, $gfArrParentIds); // find the index of parent
				switch ($gfIntParentKey)
				{
					case 0: // not an ancestor or the document itself

						$gfIntDocId = 0;
						break;

					default: // parent is above the document in document tree

						($gfIntParentLevel && ($gfIntParentLevel < $gfIntParentKey)) ?
							($gfIntDocId = $gfArrParentIds[$gfIntParentLevel]) : // find parent in specified levels up
							($gfIntDocId = $gfArrParentIds[$gfIntParentKey - 1]);
				} // end switch
		} // end switch
		unset($gfIntTopDocId, $gfIntParentLevel, $gfIntParentsCount, $gfIntParentKey, $gfArrParentIds);

		return $gfIntDocId;
	}
}
?>
