<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('new_document') || !$modx->hasPermission('save_document')) {
	$e->setError(3);
	$e->dumpError();
}

// check the document doesn't have any children
$id=$_GET['id'];
$children = array();

// check permissions on the document
include_once "./processors/user_documents_permissions.class.php";
$udperms = new udperms();
$udperms->user = $modx->getLoginUserID();
$udperms->document = $id;
$udperms->role = $_SESSION['mgrRole'];
$udperms->duplicateDoc = true;

if(!$udperms->checkPermissions()) {
	include "header.inc.php";
	?><div class="sectionHeader"><?php echo $_lang['access_permissions']; ?></div>
	<div class="sectionBody">
	<p><?php echo $_lang['access_permission_denied']; ?></p>
	<?php
	include("footer.inc.php");
	exit;
}

// Run the duplicator
$id = duplicateDocument($id);
$modx->clearCache($clearcache);

// finish cloning - redirect
$tbl_site_content = $modx->getFullTableName('site_content');
$pid = $modx->db->getValue($modx->db->select('parent',$tbl_site_content,"id='{$id}'"));
if($pid==0) $header = "Location: index.php?r=1&a=3&id={$id}";
else        $header = "Location: index.php?r=1&a=3&id={$pid}&tab=0";
header($header);

function duplicateDocument($docid, $parent=null, $_toplevel=0)
{
	global $modx,$_lang;
	$tbl_site_content = $modx->getFullTableName('site_content');
	
	// invoke OnBeforeDocDuplicate event
	$evtOut = $modx->invokeEvent('OnBeforeDocDuplicate', array(
		'id' => $docid
	));

	// if( !in_array( 'false', array_values( $evtOut ) ) ){}
	// TODO: Determine necessary handling for duplicateDocument "return $newparent" if OnBeforeDocDuplicate were able to conditially control duplication 
	// [DISABLED]: Proceed with duplicateDocument if OnBeforeDocDuplicate did not return false via: $event->output('false');

	$myChildren = array();
	$userID = $modx->getLoginUserID();

	// Grab the original document
	$rs = $modx->db->select('*', $tbl_site_content, 'id='.$docid);
	$content = $modx->db->getRow($rs);

	unset($content['id']); // remove the current id.

	// Once we've grabbed the document object, start doing some modifications
	if ($_toplevel == 0)
	{
		$content['pagetitle'] = str_replace('[+title+]',$content['pagetitle'],$_lang['duplicate_title_string']);
		$content['alias'] = null;
	}
	elseif($modx->config['friendly_urls'] == 0 || $modx->config['allow_duplicate_alias'] == 0)
	{
		$content['alias'] = null;
	}

	// change the parent accordingly
	if ($parent !== null) $content['parent'] = $parent;

	// Change the author
	$content['createdby'] = $userID;
	$content['createdon'] = time();
	// Remove other modification times
	$content['editedby'] = $content['editedon'] = $content['deleted'] = $content['deletedby'] = $content['deletedon'] = 0;

    // Set the published status to unpublished by default (see above ... commit #3388)
    $content['published'] = 0;
    $content['pub_date']  = 0;
    $content['unpub_date']  = 0;
    $content['publishedon'] = 0;

	// Escape the proper strings
	$content['pagetitle'] = $modx->db->escape($content['pagetitle']);
	$content['longtitle'] = $modx->db->escape($content['longtitle']);
	$content['description'] = $modx->db->escape($content['description']);
	$content['introtext'] = $modx->db->escape($content['introtext']);
	$content['content'] = $modx->db->escape($content['content']);
	$content['menutitle'] = $modx->db->escape($content['menutitle']);

	// increase menu index
	if (is_null($auto_menuindex) || $auto_menuindex)
	{
		$pid = $content['parent'];
		$pid = intval($content['parent']);
		$content['menuindex'] = $modx->db->getValue($modx->db->select('max(menuindex)',$tbl_site_content,"parent='{$pid}'"))+1;
	}

	// Duplicate the Document
	$new_id = $modx->db->insert($content, $tbl_site_content);

	// duplicate document's TVs & Keywords
	if($modx->config['show_meta']==1) duplicateKeywords($docid, $new_id);
	duplicateTVs($docid, $new_id);
	duplicateAccess($docid, $new_id);
	
	// invoke OnDocDuplicate event
	$evtOut = $modx->invokeEvent('OnDocDuplicate', array(
		'id' => $docid,
		'new_id' => $new_id
	));

	// Start duplicating all the child documents that aren't deleted.
	$_toplevel++;
	$rs = $modx->db->select('id', $tbl_site_content, "parent={$docid} AND deleted=0", 'id ASC');
	if ($modx->db->getRecordCount($rs))
	{
		while ($row = $modx->db->getRow($rs))
		{
			duplicateDocument($row['id'], $new_id, $_toplevel);
		}
	}

	// return the new doc id
	return $new_id;
}

// Duplicate Keywords
function duplicateKeywords($oldid,$newid){
	global $modx;

	$tblkw = $modx->getFullTableName('keyword_xref');

	$modx->db->insert(
		array('content_id'=>'', 'keyword_id'=>''), $tblkw, // Insert into
		"{$newid}, keyword_id", $tblkw, "content_id={$oldid}" // Copy from
	);
}

// Duplicate Document TVs
function duplicateTVs($oldid,$newid){
	global $modx;

	$tbltvc = $modx->getFullTableName('site_tmplvar_contentvalues');

	$modx->db->insert(
		array('contentid'=>'', 'tmplvarid'=>'', 'value'=>''), $tbltvc, // Insert into
		"{$newid}, tmplvarid, value", $tbltvc, "contentid={$oldid}" // Copy from
	);
}

// Duplicate Document Access Permissions
function duplicateAccess($oldid,$newid){
	global $modx;

	$tbldg = $modx->getFullTableName('document_groups');

	$modx->db->insert(
		array('document'=>'', 'document_group'=>''), $tbldg, // Insert into
		"{$newid}, document_group", $tbldg, "document={$oldid}" // Copy from
	);
}
