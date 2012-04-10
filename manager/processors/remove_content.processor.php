<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('delete_document')) {
	$e->setError(3);
	$e->dumpError();
}

$tbl_site_content = $modx->getFullTableName('site_content');
$rs = $modx->db->select('id',$tbl_site_content,'deleted=1');
$ids = array();
if($modx->db->getRecordCount($rs)>0)
{
	while($row=$modx->db->getRow($rs))
	{
		array_push($ids, @$row['id']);
	}
}

// invoke OnBeforeEmptyTrash event
$modx->invokeEvent("OnBeforeEmptyTrash",
						array(
							"ids"=>$ids
						));

// remove the document groups link.
$tbl_document_groups = $modx->getFullTableName('document_groups');
$sql = "DELETE {$tbl_document_groups}
		FROM {$tbl_document_groups}
		INNER JOIN {$tbl_site_content} ON {$tbl_site_content}.id = {$tbl_document_groups}.document
		WHERE {$tbl_site_content}.deleted=1";
$modx->db->query($sql);

// remove the TV content values.
$tbl_site_tmplvar_contentvalues = $modx->getFullTableName('site_tmplvar_contentvalues');
$sql = "DELETE {$tbl_site_tmplvar_contentvalues}
		FROM {$tbl_site_tmplvar_contentvalues}
		INNER JOIN {$tbl_site_content} ON {$tbl_site_content}.id = {$tbl_site_tmplvar_contentvalues}.contentid
		WHERE {$tbl_site_content}.deleted=1";
$modx->db->query($sql);

//'undelete' the document.
$rs = $modx->db->delete($tbl_site_content,'deleted=1');
if(!$rs) {
	echo "Something went wrong while trying to remove deleted documents!";
	exit;
} else {
	// invoke OnEmptyTrash event
	$modx->invokeEvent("OnEmptyTrash",
						array(
							"ids"=>$ids
						));

	// empty cache
	$modx->clearCache(); // first empty the cache
	// finished emptying cache - redirect
	header("Location: index.php?r=1&a=7");
}
