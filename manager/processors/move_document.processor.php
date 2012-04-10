<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('edit_document'))   {$e->setError(3);$e->dumpError();}

// ok, two things to check.
// first, document cannot be moved to itself
// second, new parent must be a folder. If not, set it to folder.
if($_REQUEST['id']==$_REQUEST['new_parent']) {$e->setError(600); $e->dumpError();}
if($_REQUEST['id']=='')                      {$e->setError(601); $e->dumpError();}
if($_REQUEST['new_parent']=='')              {echo '<script type="text/javascript">parent.tree.ca = "open";</script>';$e->setError(602); $e->dumpError();}

$tbl_site_content = $modx->getFullTableName('site_content');
$doc_id = $_REQUEST['id'];
if(strpos($doc_id,','))
{
	$doc_ids = explode(',',$doc_id);
	$doc_id = substr($doc_id,0,strpos($doc_id,','));
}
$rs = $modx->db->select('parent',$tbl_site_content,"id='{$doc_id}'");
if(!$rs)
{
	echo "An error occured while attempting to find the document's current parent.";
}
$current_parent = $modx->db->getValue($rs);
$new_parent = intval($_REQUEST['new_parent']);

// check user has permission to move document to chosen location

if ($use_udperms == 1)
{
	if ($current_parent != $new_parent)
	{
		include_once MODX_MANAGER_PATH . 'processors/user_documents_permissions.class.php';
		$udperms = new udperms();
		$udperms->user = $modx->getLoginUserID();
		$udperms->document = $new_parent;
		$udperms->role = $_SESSION['mgrRole'];

		 if (!$udperms->checkPermissions())
		 {
			include_once('header.inc.php');
			?>
			<script type="text/javascript">parent.tree.ca = '';</script>
			<br /><br /><div class="sectionHeader"><?php echo $_lang['access_permissions']; ?></div>
			<div class="sectionBody">
			<p><?php echo $_lang['access_permission_parent_denied']; ?></p>
			</div>
			<?php
			include_once('footer.inc.php');
			exit;
		}
	}
}
$children= allChildren($doc_id);

if (!array_search($new_parent, $children))
{
	$rs = $modx->db->update('isfolder=1',$tbl_site_content,"id={$new_parent}");
	if(!$rs)
	{
		echo "An error occured while attempting to change the new parent to a folder.";
	}

	// increase menu index
	if (is_null($modx->config['auto_menuindex']) || $modx->config['auto_menuindex'])
	{
		$menuindex = $modx->db->getValue($modx->db->select('max(menuindex)',$tbl_site_content,"parent='{$new_parent}'"))+1;
	}
	else $menuindex = 0;

	$user_id = $modx->getLoginUserID();
	$now     = time();
	if(isset($doc_ids) || 0<count($doc_ids))
	{
		foreach($doc_ids as $v)
		{
			update_parentid($v,$new_parent,$user_id,$menuindex,$now);
			$menuindex++;
		}
	}
	else
	{
		update_parentid($doc_id,$new_parent,$user_id,$menuindex,$now);
	}

	// finished moving the document, now check to see if the old_parent should no longer be a folder.
	$rs = $modx->db->select('count(id)',$tbl_site_content,"parent={$current_parent}");
	if(!$rs)
	{
		echo "An error occured while attempting to find the old parents' children.";
	}
	$row = $modx->db->getRow($rs);
	$limit = $row['count(id)'];

	if(!$limit>0)
	{
		$rs = $modx->db->update('isfolder=0',$tbl_site_content,"id={$current_parent}");
		if(!$rs)
		{
			echo 'An error occured while attempting to change the old parent to a regular document.';
		}
	}
}
else
{
	echo 'You cannot move a document to a child document!';
}
$modx->clearCache();
if($new_parent!==0) $header="Location: index.php?a=3&id={$new_parent}&tab=0&r=1";
else         $header="Location: index.php?a=2&r=1";
header($header);
exit;



function allChildren($docid)
{
	global $modx;
	$tbl_site_content = $modx->getFullTableName('site_content');
	$children= array();
	if(!$rs = $modx->db->select('id',$tbl_site_content,"parent={$docid}"))
	{
		echo "An error occured while attempting to find all of the document's children.";
	}
	else
	{
		if ($numChildren= $modx->db->getRecordCount($rs))
		{
			while ($child= $modx->db->getRow($rs))
			{
				$children[]= $child['id'];
				$nextgen= array();
				$nextgen= allChildren($child['id']);
				$children= array_merge($children, $nextgen);
			}
		}
	}
	return $children;
}

function update_parentid($doc_id,$new_parent,$user_id,$menuindex,$now)
{
	global $modx;
	$tbl_site_content = $modx->getFullTableName('site_content');
	$field['parent']    = $new_parent;
	$field['editedby']  = $user_id;
	$field['menuindex'] = $menuindex;
	$rs = $modx->db->update($field,$tbl_site_content,"id={$doc_id}");
	if(!$rs)
	{
		echo "An error occured while attempting to move the document to the new parent.";
	}
}
