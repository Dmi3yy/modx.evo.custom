<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('delete_document'))
{
	$e->setError(3);
	$e->dumpError();
}

// check the document doesn't have any children
$id=intval($_GET['id']);

// check permissions on the document
if(!check_group_perm($id)) disp_access_permission_denied();

if($id==$modx->config['site_start'])
{
	echo "Document is 'Site start' and cannot be deleted!";
	exit;
}

if($id==$modx->config['site_unavailable_page'])
{
	echo "Document is used as the 'Site unavailable page' and cannot be deleted!";
	exit;
}

$children = array();
getChildren($id);

// invoke OnBeforeDocFormDelete event
$params['id']       = $id;
$params['children'] = $children;
$modx->invokeEvent("OnBeforeDocFormDelete",$params);

$tbl_site_content = $modx->getFullTableName('site_content');
$field = array();
$field['deleted']   = '1';
$field['deletedby'] = $modx->getLoginUserID();
$field['deletedon'] = time();
if(0 < count($children))
{
	$docs_to_delete   = implode(' ,', $children);
	$rs = $modx->db->update($field,$tbl_site_content,"id IN({$docs_to_delete})");
	if(!$rs)
	{
		echo "Something went wrong while trying to set the document's children to deleted status...";
		exit;
	}
}

//ok, 'delete' the document.
$rs = $modx->db->update($field,$tbl_site_content,"id='{$id}'");
if(!$rs)
{
	echo "Something went wrong while trying to set the document to deleted status...";
	exit;
}
else
{
	// invoke OnDocFormDelete event
	$params['id']       = $id;
	$params['children'] = $children; //array()
	$modx->invokeEvent("OnDocFormDelete",$params);

	// empty cache
	$modx->clearCache();
	$pid = $modx->db->getValue($modx->db->select('parent',$tbl_site_content,"id='{$id}'"));
	$page = (isset($_GET['page'])) ? "&page={$_GET['page']}" : '';
	if($pid!=='0') $header="Location: index.php?r=1&a=3&id={$pid}&tab=0{$page}";
	else           $header="Location: index.php?a=2&r=1";
	header($header);
}



function getChildren($parent)
{
	global $modx,$children;

	$tbl_site_content = $modx->getFullTableName('site_content');

	$rs = $modx->db->select('id',$tbl_site_content,"parent='{$parent}' AND deleted='0'");
	if(0 < mysql_num_rows($rs))
	{
		// the document has children documents, we'll need to delete those too
		while($row=$modx->db->getRow($rs))
		{
			if($row['id']==$modx->config['site_start'])
			{
				echo "The document you are trying to delete is a folder containing document {$row['id']}. This document is registered as the 'Site start' document, and cannot be deleted. Please assign another document as your 'Site start' document and try again.";
				exit;
			}
			if($row['id']==$modx->config['site_unavailable_page'])
			{
				echo "The document you are trying to delete is a folder containing document {$row['id']}. This document is registered as the 'Site unavailable page' document, and cannot be deleted. Please assign another document as your 'Site unavailable page' document and try again.";
				exit;
			}
			$children[] = $row['id'];
			getChildren($row['id']);
		}
	}
}

function check_group_perm($id)
{
	global $modx;
	include_once './processors/user_documents_permissions.class.php';
	$udperms = new udperms();
	$udperms->user = $modx->getLoginUserID();
	$udperms->document = $id;
	$udperms->role = $_SESSION['mgrRole'];
	return $udperms->checkPermissions();
}

function disp_access_permission_denied()
{
	global $_lang;
	include "header.inc.php";
	?><div class="sectionHeader"><?php echo $_lang['access_permissions']; ?></div>
	<div class="sectionBody">
	<p><?php echo $_lang['access_permission_denied']; ?></p>
	<?php
	include("footer.inc.php");
	exit;
}
