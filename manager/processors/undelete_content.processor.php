<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('delete_document'))
{
	$e->setError(3);
	$e->dumpError();
}

$id = intval($_REQUEST['id']);

// check permissions on the document
if(!check_group_perm($id)) disp_access_permission_denied();

$tbl_site_content = $modx->getFullTableName('site_content');

// get the timestamp on which the document was deleted.
$where = "id='{$id}' AND deleted=1";
$rs = $modx->db->select('deletedon',$tbl_site_content,$where);
if(mysql_num_rows($rs)!=1)
{
	echo "Couldn't find document to determine it's date of deletion!";
	exit;
}
else
{
	$deltime = $modx->db->getValue($rs);
}

$children = array();
getChildren($id);

$field = array();
$field['deleted']   = '0';
$field['deletedby'] = '0';
$field['deletedon'] = '0';

if(0 < count($children))
{
	$docs_to_undelete = implode(' ,', $children);
	$rs = $modx->db->update($field,$tbl_site_content,"id IN({$docs_to_undelete})");
	if(!$rs)
	{
		echo "Something went wrong while trying to set the document's children to undeleted status...";
		exit;
	}
}
//'undelete' the document.
$rs = $modx->db->update($field,$tbl_site_content,"id=$id");
if(!$rs)
{
	echo "Something went wrong while trying to set the document to undeleted status...";
	exit;
}
else
{
	// empty cache
	$modx->clearCache();
	// finished emptying cache - redirect
	$pid = $modx->db->getValue($modx->db->select('parent',$tbl_site_content,"id='{$id}'"));
	$page = (isset($_GET['page'])) ? "&page={$_GET['page']}" : '';
	if($pid!=='0') $header="Location: index.php?r=1&a=3&id={$pid}&tab=0{$page}";
	else           $header="Location: index.php?a=2&r=1";
	header($header);
}



function getChildren($parent)
{
	global $children;
	global $deltime,$modx;
	
	$db->debug = true;
	$tbl_site_content = $modx->getFullTableName('site_content');
	$rs = $modx->db->select('id',$tbl_site_content,"parent={$parent} AND deleted=1 AND deletedon={$deltime}");
	if($modx->db->getRecordCount($rs)>0)
	{
		// the document has children documents, we'll need to delete those too
		while($row=$modx->db->getRow($rs))
		{
			$children[] = $row['id'];
			getChildren($row['id']);
			//echo "Found childNode of parentNode $parent: ".$row['id']."<br />";
		}
	}
}

function check_group_perm($id)
{
	global $modx;
	include_once MODX_MANAGER_PATH . 'processors/user_documents_permissions.class.php';
	$udperms = new udperms();
	$udperms->user = $modx->getLoginUserID();
	$udperms->document = $id;
	$udperms->role = $_SESSION['mgrRole'];
	return $udperms->checkPermissions();
}

function disp_access_permission_denied()
{
	global $_lang;
	include_once('header.inc.php');
	?><div class="sectionHeader"><?php echo $_lang['access_permissions']; ?></div>
	<div class="sectionBody">
	<p><?php echo $_lang['access_permission_denied']; ?></p>
	<?php
	include_once('footer.inc.php');
	exit;
}
