<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

/**
 *	Secure Manager Documents
 *	This script will mark manager documents as private
 *
 *	A document will be marked as private only if a manager user group 
 *	is assigned to the document group that the document belongs to.
 *
 */

function secureMgrDocument($docid='')
{
	global $modx;
	$tbl_site_content       = $modx->getFullTableName('site_content');
	$tbl_document_groups    = $modx->getFullTableName('document_groups');
	$tbl_membergroup_access = $modx->getFullTableName('membergroup_access');
	
	$modx->db->query("UPDATE {$tbl_site_content} SET privatemgr = 0 WHERE ".($docid>0 ? "id='$docid'":"privatemgr = 1"));
	$sql =  "SELECT DISTINCT sc.id 
			 FROM {$tbl_site_content} sc
			 LEFT JOIN {$tbl_document_groups} dg ON dg.document = sc.id
			 LEFT JOIN {$tbl_membergroup_access} mga ON mga.documentgroup = dg.document_group
			 WHERE ".($docid>0 ? " sc.id='$docid' AND ":"")."mga.id>0";
	$ids = $modx->db->getColumn("id",$sql);
	if(count($ids)>0) {
		$modx->db->query("UPDATE {$tbl_site_content} SET privatemgr = 1 WHERE id IN (".implode(", ",$ids).")");	
	}
}
?>