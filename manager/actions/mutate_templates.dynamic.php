 <?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

switch((int) $_REQUEST['a'])
{
	case 16:
	if(!$modx->hasPermission('edit_template'))
	{
		$e->setError(3);
		$e->dumpError();
	}
	break;
case 19:
	if(!$modx->hasPermission('new_template'))
	{
		$e->setError(3);
		$e->dumpError();
	}
	break;
default:
	$e->setError(3);
	$e->dumpError();
}

if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
{
	$id = $_REQUEST['id'];
	// check to see the template editor isn't locked
	$tbl_active_users = $modx->getFullTableName('active_users');
	$rs = $modx->db->select('internalKey, username',$tbl_active_users,"action=16 AND id={$id}");
	if($modx->db->getRecordCount($rs)>1)
	{
		while ($row = $modx->db->getRow($rs))
		{
			if($row['internalKey'] != $modx->getLoginUserID())
			{
				$msg = sprintf($_lang['lock_msg'],$row['username'],'template');
				$e->setError(5, $msg);
				$e->dumpError();
			}
		}
	} // end check for lock
}
else
{
    $id='';
}

$content = array();
if(isset($_REQUEST['id']) && $_REQUEST['id']!='' && is_numeric($_REQUEST['id'])) {
	$tbl_site_templates = $modx->getFullTableName('site_templates');
	$rs = $modx->db->select('*',$tbl_site_templates,"id={$id}");
	$total = $modx->db->getRecordCount($rs);
	if($total > 1)
	{
		echo "Oops, something went terribly wrong...<p>";
		print "More results returned than expected. Which sucks. <p>Aborting.";
		exit;
	}
	if($total < 1)
	{
		echo "Oops, something went terribly wrong...<p>";
		print "No database record has been found for this template. <p>Aborting.";
		exit;
	}
	$content = $modx->db->getRow($rs);
	$_SESSION['itemname']=$content['templatename'];
	if($content['locked']==1 && $_SESSION['mgrRole']!=1)
	{
		$e->setError(3);
		$e->dumpError();
	}
}
else
{
	$_SESSION['itemname']="New template";
}

$content = array_merge($content, $_POST);
if($content['content']=='')
{
	$content['content'] =<<< EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <base href="[(site_url)]" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>[*pagetitle*]|[(site_name)]</title>
  <meta name="description" content="[*description*]" />
</head>
<body>
  <h1>[*pagetitle*]</h1>
  <div>[*content*]</div>
</body>
</html>
EOT;
}

?>
<script type="text/javascript">
function duplicaterecord(){
	if(confirm("<?php echo $_lang['confirm_duplicate_record'] ?>")==true) {
		documentDirty=false;
		document.location.href="index.php?id=<?php echo $_REQUEST['id']; ?>&a=96";
	}
}

function deletedocument() {
	if(confirm("<?php echo $_lang['confirm_delete_template']; ?>")==true) {
		documentDirty=false;
		document.location.href="index.php?id=" + document.mutate.id.value + "&a=21";
	}
}

</script>

<form name="mutate" method="post" action="index.php" enctype="multipart/form-data">
<?php
	// invoke OnTempFormPrerender event
	$evtOut = $modx->invokeEvent("OnTempFormPrerender",array("id" => $id));
	if(is_array($evtOut)) echo implode("",$evtOut);
?>
<input type="hidden" name="a" value="20">
<input type="hidden" name="id" value="<?php echo $_REQUEST['id'];?>">
<input type="hidden" name="mode" value="<?php echo (int) $_REQUEST['a'];?>">

	<h1><?php echo $_lang['template_title']; ?></h1>

    <div id="actions">
    	  <ul class="actionButtons">
    		  <li id="Button1">
    			<a href="#" onclick="documentDirty=false; document.mutate.save.click();saveWait('mutate');">
    			  <img src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']?>
    			</a>
    			  <span class="and"> + </span>
    			<select id="stay" name="stay">
    			  <option id="stay1" value="1" <?php echo $_REQUEST['stay']=='1' ? ' selected=""' : ''?> ><?php echo $_lang['stay_new']?></option>
    			  <option id="stay2" value="2" <?php echo $_REQUEST['stay']=='2' ? ' selected="selected"' : ''?> ><?php echo $_lang['stay']?></option>
    			  <option id="stay3" value=""  <?php echo $_REQUEST['stay']=='' ? ' selected=""' : ''?>  ><?php echo $_lang['close']?></option>
    			</select>
    		  </li>
    		  <?php
    			if ($_REQUEST['a'] == '16') { ?>
    		  <li id="Button2"><a href="#" onclick="duplicaterecord();"><img src="<?php echo $_style["icons_resource_duplicate"] ?>" /> <?php echo $_lang["duplicate"]; ?></a></li>
    		  <li id="Button3" class="disabled"><a href="#" onclick="deletedocument();"><img src="<?php echo $_style["icons_delete_document"]?>" /> <?php echo $_lang['delete']?></a></li>
    		  <?php } else { ?>
    		  <li id="Button3"><a href="#" onclick="deletedocument();"><img src="<?php echo $_style["icons_delete_document"]?>" /> <?php echo $_lang['delete']?></a></li>
    		  <?php } ?>
    		  <li id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=76';"><img src="<?php echo $_style["icons_cancel"]?>" /> <?php echo $_lang['cancel']?></a></li>
    	  </ul>
    </div>

<script type="text/javascript" src="media/script/tabpane.js"></script>

<div class="sectionBody">

<div class="tab-pane" id="templatesPane">
	<script type="text/javascript">
		tpResources = new WebFXTabPane( document.getElementById( "templatesPane" ), <?php echo (($modx->config['remember_last_tab'] == 2) || ($_GET['stay'] == 2 )) ? 'true' : 'false'; ?> );
	</script>

	<div class="tab-page" id="tabTemplate">
    	<h2 class="tab"><?php echo $_lang["template_edit_tab"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabTemplate" ) );</script>

	<div style="margin-bottom:10px;">
	<?php echo "\t" . $_lang['template_msg']; ?>
	</div>
	<div style="margin-bottom:10px;">
	<b><?php echo $_lang['template_name']; ?></b>
	<input name="templatename" type="text" maxlength="100" value="<?php echo htmlspecialchars($content['templatename']);?>" class="inputBox" style="width:300px;" onChange='documentDirty=true;'>
	<span class="warning" id='savingMessage'></span>
	</div>
	<!-- HTML text editor start -->
	<div style="width:100%;position:relative">
	    <div style="padding:3px 8px; overflow:hidden;zoom:1; background-color:#eeeeee; border:1px solid #c3c3c3; border-bottom:none;margin-top:5px;">
	    	<span style="float:left;font-weight:bold;"><?php echo $_lang['template_code']; ?></span>
		</div>
        <textarea dir="ltr" name="post" class="phptextarea" style="width:100%; height: 370px;" onChange='documentDirty=true;'><?php echo isset($content['post']) ? htmlspecialchars($content['post']) : htmlspecialchars($content['content']); ?></textarea>
	</div>
	<!-- HTML text editor end -->
	<input type="submit" name="save" style="display:none">
	</div>
<?php
if ($_REQUEST['a'] == '16')
{
	$tbl_site_tmplvar_templates = $modx->getFullTableName('site_tmplvar_templates');
	$tbl_site_tmplvars          = $modx->getFullTableName('site_tmplvars');
	$tbl_categories             = $modx->getFullTableName('categories');
	$field = "tv.name as 'name', tv.id as 'id', tpl.templateid as tplid, tpl.rank, if(isnull(cat.category),'{$_lang['no_category']}',cat.category) as category, tv.description as 'desc'";
	$from  = "{$tbl_site_tmplvar_templates} tpl";
	$from .= " INNER JOIN {$tbl_site_tmplvars} tv ON tv.id = tpl.tmplvarid";
	$from .= " LEFT JOIN {$tbl_categories} cat ON tv.category = cat.id";
	$where = "tpl.templateid='{$id}'";
	$orderby = 'tpl.rank, tv.rank, tv.id';
	$rs = $modx->db->select($field,$from,$where,$orderby);
	$total = $modx->db->getRecordCount($rs);
?>
	
	<div class="tab-page" id="tabAssignedTVs">
		<h2 class="tab"><?php echo $_lang["template_assignedtv_tab"] ?></h2>
		<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabAssignedTVs" ) );</script>
		<?php echo "<p>{$_lang['template_tv_msg']}</p>"; ?>
		<div class="sectionHeader">
			<?php echo $_lang["template_assignedtv_tab"];?>
		</div>
		<div class="sectionBody">
<?php
	if($total>0)
	{
		$tvList = '<ul>';
		while ($row = $modx->db->getRow($rs))
		{
			$desc = $row['desc'] ? " ({$row['desc']})" : '';
			$tvList .= '<li><a href="index.php?id=' . $row['id'] . '&amp;a=301">'.$row['name'] . '</a>' . $desc . '</li>';
		}
		$tvList .= '</ul>';
	}
	else
	{
		$tvList = $_lang['template_no_tv'];
	}
	echo $tvList;
?>
			<ul class="actionButtons" style="margin-top:15px;">
<?php
	$query = $_GET['id'] ? '&amp;tpl=' . intval($_GET['id']) : '';
?>
				<li><a href="index.php?&amp;a=300<?php echo $query;?>"><img src="<?php echo $_style['icons_add'];?>" /> <?php echo $_lang['new_tmplvars'];?></a></li>
<?php
	if($modx->hasPermission('save_template') && $total > 1)
	{
		echo '<li><a href="index.php?a=117&amp;id=' . $_REQUEST['id'] . '">' . $_lang['template_tv_edit'] . '</a></li>';
	}
?>
		</ul>
		</div>
</div>
<?php
}
?>

<div class="tab-page" id="tabInfo">
<h2 class="tab"><?php echo $_lang['settings_properties'];?></h2>
<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabInfo" ) );</script>
<table>
	  <tr>
		<th align="left"><?php echo $_lang['existing_category']; ?>:</th>
		<td align="left"><select name="categoryid" style="width:300px;" onChange='documentDirty=true;'>
				<option>&nbsp;</option>
		        <?php
		            include_once "categories.inc.php";
					$ds = getCategories();
					if($ds) foreach($ds as $n=>$v)
					{
						echo "<option value='".$v['id']."'".($content["category"]==$v["id"]? " selected='selected'":"").">".htmlspecialchars($v["category"])."</option>";
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<th align="left" valign="top" style="padding-top:5px;"><?php echo $_lang['new_category']; ?>:</th>
		<td align="left" valign="top" style="padding-top:5px;"><input name="newcategory" type="text" maxlength="45" value="<?php echo isset($content['newcategory']) ? $content['newcategory'] : '' ?>" class="inputBox" style="width:300px;" onChange='documentDirty=true;'></td>
	</tr>
	<tr>
		<th align="left"><?php echo $_lang['template_desc']; ?>:&nbsp;&nbsp;</th>
		<td align="left"><textarea name="description" onChange="documentDirty=true;" style="padding:0;height:4em;"><?php echo htmlspecialchars($content['description']);?></textarea></td>
	</tr>
	  <tr>
	    <td align="left" colspan="2">
	    <label><input name="locked" type="checkbox" <?php echo $content['locked']==1 ? "checked='checked'" : "" ;?> class="inputBox"> <?php echo $_lang['lock_template']; ?> <span class="comment"><?php echo $_lang['lock_template_msg']; ?></span></label></td>
	  </tr>
</table>
</div>

<?php
// invoke OnTempFormRender event
$evtOut = $modx->invokeEvent("OnTempFormRender",array("id" => $id));
if(is_array($evtOut)) echo implode("",$evtOut);
?>
</form>
</div>
