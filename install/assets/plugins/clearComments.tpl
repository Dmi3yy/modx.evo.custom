//<?php
/**
 * Clear Comments
 *
 * Remove comments of documents when you empty MODx Trash Can.
 *
 * @category    plugin
 * @version     1.0
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      AHHP ~ Boplo.ir
 * @internal    @events OnEmptyTrash
 * @internal    @modx_category
 * @internal    @properties 
 * @internal    @installset base
 */
/**
 * Clear Comments Plugin: Remove comments of documents when you empty MODx Trash Can.
 * Version: 1.0
 * Event: "OnEmptyTrash"
 * Requirement: Jot Snippet
 * Work on: Evolution
 *
 * Author: AHHP ~ Boplo.ir
 * Date: 21 August 2009
*/

defined('IN_MANAGER_MODE') or die();
if($modx->Event->name == "OnEmptyTrash")
{
	$where = 'uparent IN(' .join(',' , $ids). ')';
	$jot_content = $modx->getFullTableName('jot_content');
	$jot_fields = $modx->getFullTableName('jot_fields');
	$jot_subscriptions = $modx->getFullTableName('jot_subscriptions');
	
	
	// If `jot_fields` exists
	if($modx->db->getRecordCount( $modx->db->query("SHOW TABLES LIKE '$jot_fields'") ) == 1)
	{
		// `jot_fields` stores fields by comments ID so we need to get comments ID that are removing.
		$commentsIds = array();
		$select = $modx->db->select("id", $jot_content, $where);
		while($commentRow = $modx->db->getRow($select, 'num'))
			$commentsIds[] = $commentRow[0];
		
		if(count($commentsIds) > 0)
			$modx->db->delete($jot_fields, 'id IN(' .join(",",$commentsIds). ')');
		
		unset($select, $commentsIds);
	}
	
	$modx->db->delete($jot_content, $where);
	$modx->db->delete($jot_subscriptions, $where);
}