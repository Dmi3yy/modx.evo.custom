<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_document')) {
    $e->setError(3);
    $e->dumpError();
}

if(isset($_REQUEST['id']))
{
	$id = intval($_REQUEST['id']);
}
elseif(isset($_REQUEST['batch']))
{
	$id = join(',',$_REQUEST['batch']);
}
else
{
	$e->setError(2);
	$e->dumpError();
}

// check permissions on the document
include_once(MODX_MANAGER_PATH . 'processors/user_documents_permissions.class.php');
$udperms = new udperms();
$udperms->user = $modx->getLoginUserID();
$udperms->document = $id;
$udperms->role = $_SESSION['mgrRole'];

if(!$udperms->checkPermissions())
{
	show_perm_error();
    exit;
}

echo get_src_js();
$parent = get_parentid($id);
echo get_src_content($id,$parent);



function get_src_content($id,$parent)
{
	global $_lang,$_style;
	$redirect = $parent==0 ? 'index.php?a=2' : "index.php?a=3&amp;id={$parent}&amp;tab=0";
	$src = <<< EOT
<h1>{$_lang['move_resource_title']}</h1>
<div id="actions">
	<ul class="actionButtons">
	  <li><a href="#" onclick="document.newdocumentparent.submit();"><img src="{$_style["icons_save"]}" /> {$_lang['save']}</a></li>
	  <li><a href="#" onclick="documentDirty=false;document.location.href='{$redirect}'"><img src="{$_style["icons_cancel"]}" /> {$_lang['cancel']}</a></li>
	</ul>
</div>

<div class="sectionHeader">{$_lang['move_resource_title']}</div>
<div class="sectionBody">
<p>{$_lang['move_resource_message']}</p>
<form method="post" action="index.php" name='newdocumentparent'>
<input type="hidden" name="a" value="52">
<input type="hidden" name="id" value="{$id}">
<p>{$_lang['resource_to_be_moved']}: <b>{$id}</b></p>
<p><span id="parentName" class="warning">{$_lang['move_resource_new_parent']}</span></p>
<input type="hidden" name="new_parent" value="" class="inputBox">
<br />
<input type="save" value="Move" style="display:none">
</form>
</div>
EOT;
	return $src;
}

function batch_move()
{
	global $modx;
	print_r($_REQUEST['batch']);
	foreach($_REQUEST['batch'] as $v)
	{
		$ids[] = sprintf("id='%s'",$modx->db->escape($v));
	}
	$where = join(' OR ', $ids);
	echo $where;
	$tblsc = $modx->getFullTableName('site_content');
	$rs = $modx->db->select('pagetitle', $tblsc, $where);
	while($row=$modx->db->getRow($rs))
	{
		echo $row['pagetitle'] . '<br />';
	}
}

function get_src_js()
{
	global $_lang;
	$src = <<< EOT
<script language="javascript">
parent.tree.ca = "move";
function setMoveValue(pId, pName) {
    if (pId==0 || checkParentChildRelation(pId, pName)) {
        document.newdocumentparent.new_parent.value=pId;
        document.getElementById('parentName').innerHTML = "{$_lang['new_parent']}: <b>" + pId + "</b> (" + pName + ")";
    }
}

// check if the selected parent is a child of this document
function checkParentChildRelation(pId, pName) {
    var sp;
    var id = document.newdocumentparent.id.value;
    var tdoc = parent.tree.document;
    var pn = (tdoc.getElementById) ? tdoc.getElementById("node"+pId) : tdoc.all["node"+pId];
    if (!pn) return;
    if (pn.id.substr(4)==id) {
        alert("{$_lang['illegal_parent_self']}");
        return;
    }
    else {
        while (pn.p>0) {
            pn = (tdoc.getElementById) ? tdoc.getElementById("node"+pn.p) : tdoc.all["node"+pn.p];
            if (pn.id.substr(4)==id) {
                alert("{$_lang['illegal_parent_child']}");
                return;
            }
        }
    }
    return true;
}
</script>

EOT;
	return $src;
}

function show_perm_error()
{
	global $_lang;
	$src = <<< EOT
<br /><br /><div class="sectionHeader">{$_lang['access_permissions']}</div><div class="sectionBody">
<p>{$_lang['access_permission_denied']}</p>
EOT;
	echo $src;
    include("footer.inc.php");
}

function get_parentid($id)
{
	global $modx;
	if(strpos($id,',')) $id = substr($id,0,strpos($id,','));
	$rs = $modx->db->select('parent', $modx->getFullTableName('site_content'), 'id='.$id);
	return $modx->db->getValue($rs);
}
