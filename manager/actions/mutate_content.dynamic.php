<?php
if (IN_MANAGER_MODE != 'true') die('<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.');

// check permissions
switch ($_REQUEST['a']) {
	case 27:
		if (!$modx->hasPermission('edit_document')) {
			$modx->config['remember_last_tab'] = 0;
			$e->setError(3);
			$e->dumpError();
		}
		$modx->remove_locks();
		break;
	case 85:
	case 72:
	case 4:
		if (!$modx->hasPermission('new_document')) {
			$e->setError(3);
			$e->dumpError();
		} elseif(isset($_REQUEST['pid']) && $_REQUEST['pid'] != '0') {
			// check user has permissions for parent
			include_once(MODX_MANAGER_PATH.'processors/user_documents_permissions.class.php');
			$udperms = new udperms();
			$udperms->user = $modx->getLoginUserID();
			$udperms->document = empty($_REQUEST['pid']) ? 0 : $_REQUEST['pid'];
			$udperms->role = $_SESSION['mgrRole'];
			if (!$udperms->checkPermissions()) {
				$e->setError(3);
				$e->dumpError();
			}
		}
		break;
	default:
		$e->setError(3);
		$e->dumpError();
}


if (isset($_REQUEST['id'])) $id = (int)$_REQUEST['id'];
else                        $id = 0;

if ($manager_theme)
        $manager_theme .= '/';
else    $manager_theme  = '';

// Get table names (alphabetical)
$tbl_active_users               = $modx->getFullTableName('active_users');
$tbl_categories                 = $modx->getFullTableName('categories');
$tbl_document_group_names       = $modx->getFullTableName('documentgroup_names');
$tbl_member_groups              = $modx->getFullTableName('member_groups');
$tbl_membergroup_access         = $modx->getFullTableName('membergroup_access');
$tbl_document_groups            = $modx->getFullTableName('document_groups');
$tbl_keyword_xref               = $modx->getFullTableName('keyword_xref');
$tbl_site_content               = $modx->getFullTableName('site_content');
$tbl_site_content_metatags      = $modx->getFullTableName('site_content_metatags');
$tbl_site_keywords              = $modx->getFullTableName('site_keywords');
$tbl_site_metatags              = $modx->getFullTableName('site_metatags');
$tbl_site_templates             = $modx->getFullTableName('site_templates');
$tbl_site_tmplvar_access        = $modx->getFullTableName('site_tmplvar_access');
$tbl_site_tmplvar_contentvalues = $modx->getFullTableName('site_tmplvar_contentvalues');
$tbl_site_tmplvar_templates     = $modx->getFullTableName('site_tmplvar_templates');
$tbl_site_tmplvars              = $modx->getFullTableName('site_tmplvars');

if ($action == 27) {
	//editing an existing document
	// check permissions on the document
	include_once(MODX_MANAGER_PATH.'processors/user_documents_permissions.class.php');
	$udperms = new udperms();
	$udperms->user = $modx->getLoginUserID();
	$udperms->document = $id;
	$udperms->role = $_SESSION['mgrRole'];

	if (!$udperms->checkPermissions()) {
?>
<br /><br />
<div class="sectionHeader"><?php echo $_lang['access_permissions']?></div>
<div class="sectionBody">
	<p><?php echo $_lang['access_permission_denied']?></p>
<?php
		include(MODX_MANAGER_PATH.'includes/footer.inc.php');
		exit;
	}
}

// Check to see the document isn't locked
$rs = $modx->db->select('internalKey, username',$tbl_active_users,"action=27 AND id='{$id}'");
if (1 < $modx->db->getRecordCount($rs))
{
	while($lock = $modx->db->getRow($rs))
	{
		if ($lock['internalKey'] != $modx->getLoginUserID())
		{
			$msg = sprintf($_lang['lock_msg'], $lock['username'], 'document');
			$e->setError(5, $msg);
			$e->dumpError();
		}
	}
}

// get document groups for current user
if ($_SESSION['mgrDocgroups']) {
	$docgrp = implode(',', $_SESSION['mgrDocgroups']);
}

if ($id != 0)
{
	$access  = "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
	$access .= !$docgrp ? '' : " OR dg.document_group IN ({$docgrp})";
	$from = "{$tbl_site_content} AS sc LEFT JOIN {$tbl_document_groups} AS dg ON dg.document=sc.id";
	$rs = $modx->db->select('DISTINCT sc.*',$from, "sc.id='{$id}' AND ({$access})");
	$limit = $modx->db->getRecordCount($rs);
	if ($limit > 1)
	{
		$e->setError(6);
		$e->dumpError();
	}
	if ($limit < 1)
	{
		$e->setError(3);
		$e->dumpError();
	}
	$content = $modx->db->getRow($rs);
}
else
{
	$content = array();
}

// restore saved form
$formRestored = false;
if ($modx->manager->hasFormValues())
{
	$modx->manager->loadFormValues();
	$formRestored = true;
}

// retain form values if template was changed
// edited to convert pub_date and unpub_date
// sottwell 02-09-2006
if ($formRestored == true || isset ($_REQUEST['newtemplate']))
{
	$content = array_merge($content, $_POST);
	$content['content'] = $_POST['ta'];
	if (empty ($content['pub_date'])) unset ($content['pub_date']);
	else $content['pub_date'] = $modx->toTimeStamp($content['pub_date']);
	if (empty ($content['unpub_date'])) unset ($content['unpub_date']);
	else $content['unpub_date'] = $modx->toTimeStamp($content['unpub_date']);
}

// increase menu index if this is a new document
if (empty($_REQUEST['id']))
{
	if (is_null($auto_menuindex) || $auto_menuindex)
	{
		$pid = intval($_REQUEST['pid']);
		$content['menuindex'] = $modx->db->getValue($modx->db->select('count(id)',$tbl_site_content,"parent='{$pid}'")) + 1;
	}
	else
	{
		$content['menuindex'] = 0;
	}
}

if($_REQUEST['a'] == '4') $content['richtext'] = $modx->config['use_editor'];

if (isset ($_POST['which_editor']))
{
	$which_editor = $_POST['which_editor'];
}
$dayNames   = "['" . join("','",explode(',',$_lang['day_names'])) . "']";
$monthNames = "['" . join("','",explode(',',$_lang['month_names'])) . "']";
?>
<script type="text/javascript" src="media/calendar/datepicker.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
window.addEvent('domready', function(){
	var dpOffset = <?php echo $modx->config['datepicker_offset']; ?>;
	var dpformat = "<?php echo $modx->config['datetime_format']; ?>" + ' hh:mm:00';
	var dayNames = <?php echo $dayNames;?>;
	var monthNames = <?php echo $monthNames;?>;
	new DatePicker($('pub_date'),   {'yearOffset': dpOffset,'format':dpformat,'dayNames':dayNames,'monthNames':monthNames});
	new DatePicker($('unpub_date'), {'yearOffset': dpOffset,'format':dpformat,'dayNames':dayNames,'monthNames':monthNames});

	if( !window.ie6 ) {
	    $$('img[src=<?php echo $_style["icons_tooltip_over"]?>]').each(function(help_img) {
            help_img.removeProperty('onclick');
            help_img.removeProperty('onmouseover');
            help_img.removeProperty('onmouseout');
            help_img.setProperty('title', help_img.getProperty('alt') );
            help_img.setProperty('class', 'tooltip' );
            if (window.ie) help_img.removeProperty('alt');
	    });
	    new Tips($$('.tooltip'),{className:'custom'} );
	}
});

// save tree folder state
if (parent.tree) parent.tree.saveFolderState();

function changestate(element) {
	currval = eval(element).value;
	if (currval==1) {
		eval(element).value=0;
	} else {
		eval(element).value=1;
	}
	documentDirty=true;
}

function deletedocument() {
	if (confirm("<?php echo $_lang['confirm_delete_resource']?>")==true) {
		document.location.href="index.php?id=" + document.mutate.id.value + "&a=6";
	}
}

function undeletedocument() {
	if (confirm("<?php echo $_lang['confirm_undelete']?>")==true) {
		document.location.href="index.php?id=" + document.mutate.id.value + "&a=63";
	}
}

function duplicatedocument(){
    if(confirm("<?php echo $_lang['confirm_resource_duplicate']?>")==true) {
        document.location.href="index.php?id=<?php echo $_REQUEST['id']?>&a=94";
    }
}

function resetpubdate() {
	if(document.mutate.pub_date.value!=''||document.mutate.unpub_date.value!='') {
		if (confirm("公開開始日時・公開終了日時をリセットします")==true) {
			document.mutate.pub_date.value='';
			document.mutate.unpub_date.value='';
		}
	}
	documentDirty=true;
}

var allowParentSelection = false;
var allowLinkSelection = false;

function enableLinkSelection(b) {
	parent.tree.ca = "link";
	var closed = "<?php echo $_style["tree_folder"] ?>";
	var opened = "<?php echo $_style["icons_set_parent"] ?>";
	if (b) {
		document.images["llock"].src = opened;
		allowLinkSelection = true;
	}
	else {
		document.images["llock"].src = closed;
		allowLinkSelection = false;
	}
}

function setLink(lId) {
	if (!allowLinkSelection) {
		window.location.href="index.php?a=3&id="+lId;
		return;
	}
	else {
		documentDirty=true;
		document.mutate.ta.value=lId;
	}
}

function enableParentSelection(b) {
	parent.tree.ca = "parent";
	var closed = "<?php echo $_style["tree_folder"] ?>";
	var opened = "<?php echo $_style["icons_set_parent"] ?>";
	if (b) {
		document.images["plock"].src = opened;
		allowParentSelection = true;
	}
	else {
		document.images["plock"].src = closed;
		allowParentSelection = false;
	}
}

function setParent(pId, pName) {
	if (!allowParentSelection) {
		window.location.href="index.php?a=3&id="+pId;
		return;
	}
	else {
		if (pId==0 || checkParentChildRelation(pId, pName)) {
			documentDirty=true;
			document.mutate.parent.value=pId;
			var elm = document.getElementById('parentName');
			if (elm) {
				elm.innerHTML = (pId + " (" + pName + ")");
			}
		}
	}
}

// check if the selected parent is a child of this document
function checkParentChildRelation(pId, pName) {
	var sp;
	var id = document.mutate.id.value;
	var tdoc = parent.tree.document;
	var pn = (tdoc.getElementById) ? tdoc.getElementById("node"+pId) : tdoc.all["node"+pId];
	if (!pn) return;
	if (pn.id.substr(4)==id) {
		alert("<?php echo $_lang['illegal_parent_self']?>");
		return;
	}
	else {
		while (pn.getAttribute("p")>0) {
			pId = pn.getAttribute("p");
			pn = (tdoc.getElementById) ? tdoc.getElementById("node"+pId) : tdoc.all["node"+pId];
			if (pn.id.substr(4)==id) {
				alert("<?php echo $_lang['illegal_parent_child']?>");
				return;
			}
		}
	}
	return true;
}

function clearKeywordSelection() {
	var opt = document.mutate.elements["keywords[]"].options;
	for (i = 0; i < opt.length; i++) {
		opt[i].selected = false;
	}
}

function clearMetatagSelection() {
	var opt = document.mutate.elements["metatags[]"].options;
	for (i = 0; i < opt.length; i++) {
		opt[i].selected = false;
	}
}

var curTemplate = -1;
var curTemplateIndex = 0;
function storeCurTemplate() {
	var dropTemplate = document.getElementById('template');
	if (dropTemplate) {
		for (var i=0; i<dropTemplate.length; i++) {
			if (dropTemplate[i].selected) {
				curTemplate = dropTemplate[i].value;
				curTemplateIndex = i;
			}
		}
	}
}
function changeTemplate() {
	var dropTemplate = document.getElementById('template');
	if (dropTemplate) {
		for (var i=0; i<dropTemplate.length; i++) {
			if (dropTemplate[i].selected) {
				newTemplate = dropTemplate[i].value;
				break;
			}
		}
	}
	if (curTemplate == newTemplate) {return;}

	if ((num_of_tv==0) || (documentDirty==false)) {
		documentDirty=false;
		document.mutate.a.value = <?php echo $action?>;
		document.mutate.newtemplate.value = newTemplate;
		document.mutate.submit();
	} else if (confirm('<?php echo $_lang['tmplvar_change_template_msg']?>')) {
		documentDirty=false;
		document.mutate.a.value = <?php echo $action?>;
		document.mutate.newtemplate.value = newTemplate;
		document.mutate.submit();
	} else {
		dropTemplate[curTemplateIndex].selected = true;
	}
}

// Added for RTE selection
function changeRTE() {
	var whichEditor = document.getElementById('which_editor');
	if (whichEditor) {
		for (var i = 0; i < whichEditor.length; i++) {
			if (whichEditor[i].selected) {
				newEditor = whichEditor[i].value;
				break;
			}
		}
	}
	var dropTemplate = document.getElementById('template');
	if (dropTemplate) {
		for (var i = 0; i < dropTemplate.length; i++) {
			if (dropTemplate[i].selected) {
				newTemplate = dropTemplate[i].value;
				break;
			}
		}
	}

	documentDirty=false;
	document.mutate.a.value = <?php echo $action?>;
	document.mutate.newtemplate.value = newTemplate;
	document.mutate.which_editor.value = newEditor;
	document.mutate.submit();
}

/* ]]> */
</script>

<form name="mutate" id="mutate" class="content" method="post" enctype="multipart/form-data" action="index.php">
<?php
// invoke OnDocFormPrerender event
$evtOut = $modx->invokeEvent('OnDocFormPrerender', array(
	'id' => $id
));
if (is_array($evtOut)) echo implode('', $evtOut);

$_SESSION['itemname'] = to_safestr($content['pagetitle']);
?>
<input type="hidden" name="a" value="5" />
<input type="hidden" name="id" value="<?php echo $content['id']?>" />
<input type="hidden" name="mode" value="<?php echo (int) $_REQUEST['a']?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo isset($upload_maxsize) ? $upload_maxsize : 3145728?>" />
<input type="hidden" name="refresh_preview" value="0" />
<input type="hidden" name="newtemplate" value="" />

<fieldset id="create_edit">
	<h1><?php
			if ($id!=0) echo $_lang['edit_resource_title'];
			else        echo $_lang['create_resource_title'];
		?></h1>

<div id="actions">
	  <ul class="actionButtons">
		  <li id="Button1">
			<a href="#" onclick="documentDirty=false; document.mutate.save.click();">
              <img alt="icons_save" src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']?>
			</a><span class="and"> + </span>
			<select id="stay" name="stay">
			  <?php if ($modx->hasPermission('new_document')) { ?>		
			  <option id="stay1" value="1" <?php echo $_REQUEST['stay']=='1' ? ' selected=""' : ''?> ><?php echo $_lang['stay_new']?></option>
			  <?php } ?>
			  <option id="stay2" value="2" <?php echo $_REQUEST['stay']=='2' ? ' selected="selected"' : ''?> ><?php echo $_lang['stay']?></option>
			  <option id="stay3" value=""  <?php echo $_REQUEST['stay']=='' ? ' selected=""' : ''?>  ><?php echo $_lang['close']?></option>
			</select>		
		  </li>
		  <?php
            if ($_REQUEST['a'] !== '4' && $_REQUEST['a'] !== '72')
            { ?>
          <li id="Button6"><a href="#" onclick="duplicatedocument();"><img src="<?php echo $_style["icons_resource_duplicate"] ?>" alt="icons_resource_duplicate" /> <?php echo $_lang['duplicate']?></a></li>
          <?php
          if($content['deleted'] === '0')
          {
          ?>
          <li id="Button3"><a href="#" onclick="deletedocument();"><img src="<?php echo $_style["icons_delete_document"] ?>" alt="icons_delete_document" /> <?php echo $_lang['delete']; ?></a></li>
          <?php } else { ?>
          <li id="Button3"><a href="#" onclick="undeletedocument();"><img src="<?php echo $_style["icons_undelete_resource"] ?>" alt="icons_undelete_document" /> <?php echo $_lang['undelete_resource']?></a></li>
          <?php } ?>
		  <?php } ?>
          <li id="Button4"><a href="#" onclick="
<?php
				if(isset($content['parent']) && $content['parent']!=='0')
				{
					if($content['isfolder']=='0')
					{
					echo "document.location.href='index.php?a=3&id={$content['parent']}&tab=0';";
					}
					else
					{
					echo "document.location.href='index.php?a=3&id={$id}&tab=0';";
					}
				}
				elseif($content['isfolder']=='1' && $content['parent']=='0')
				{
					echo "document.location.href='index.php?a=3&id={$id}&tab=0';";
				}
				elseif($_GET['pid'])
				{
					$_GET['pid'] = intval($_GET['pid']);
					echo "document.location.href='index.php?a=3&id={$_GET['pid']}&tab=0';";
				}
				else
				{
					echo "document.location.href='index.php?a=2';";
				}
?>
          	"><img alt="icons_cancel" src="<?php echo $_style["icons_cancel"] ?>" /> <?php echo $_lang['cancel']?></a></li>
          <?php
            if ($_REQUEST['a'] !== '4' && $_REQUEST['a'] !== '72') { ?>
          <li id="Button5"><a href="#" onclick="window.open('<?php echo $modx->makeUrl($id); ?>','previeWin');"><img alt="icons_preview_resource" src="<?php echo $_style["icons_preview_resource"] ?>" /> <?php echo $_lang['preview']?></a></li>
          <?php } ?>
	  </ul>
</div>

<!-- start main wrapper -->
<div class="sectionBody">
<script type="text/javascript" src="media/script/tabpane.js"></script>

<div class="tab-pane" id="documentPane">
	<script type="text/javascript">
	tpSettings = new WebFXTabPane(document.getElementById("documentPane"), <?php echo (($modx->config['remember_last_tab'] == 2) || ($_GET['stay'] == 2 )) ? 'true' : 'false'; ?> );
	</script>
	<!-- General -->
	<div class="tab-page" id="tabGeneral">
		<h2 class="tab"><?php echo $_lang['settings_general']?></h2>
		<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabGeneral" ) );</script>

		<table width="99%" border="0" cellspacing="5" cellpadding="0">
			<tr style="height: 24px;">
				<td width="100" align="left">
					<span class="warning"><?php echo $_lang['resource_title']?></span>
				</td>
				<td>
					<?php echo input_text('pagetitle',to_safestr($content['pagetitle']),'spellcheck="true" required');?>
					<?php echo tooltip($_lang['resource_title_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td>
					<span class="warning"><?php echo $_lang['long_title']?></span>
				</td>
				<td>
					<?php echo input_text('longtitle',to_safestr($content['longtitle']),'spellcheck="true"');?>
					<?php echo tooltip($_lang['resource_long_title_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td>
					<span class="warning"><?php echo $_lang['resource_description']?></span>
				</td>
				<td>
					<?php echo input_text('description',to_safestr($content['description']),'spellcheck="true"');?>
					<?php echo tooltip($_lang['resource_description_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td>
					<span class="warning"><?php echo $_lang['resource_alias']?></span>
				</td>
				<td>
					<?php echo input_text('alias',to_safestr(urldecode($content['alias'])),'','100');?>
					<?php echo tooltip($_lang['resource_alias_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td>
					<span class="warning"><?php echo $_lang['link_attributes']?></span>
				</td>
				<td>
					<?php echo input_text('link_attributes',to_safestr($content['link_attributes']));?>
					<?php echo tooltip($_lang['link_attributes_help']);?>
				</td>
			</tr>
				
<?php
	if ($content['type'] == 'reference' || $_REQUEST['a'] == '72')
	{ // Web Link specific
?>
            <tr style="height: 24px;">
            	<td>
            		<span class="warning"><?php echo $_lang['weblink']?></span>
            		<img name="llock" src="<?php echo $_style["tree_folder"] ?>" alt="tree_folder" onclick="enableLinkSelection(!allowLinkSelection);" style="cursor:pointer;" />
            	</td>
				<td>
					<?php $content['content'] = !empty($content['content']) ? strip_tags(stripslashes($content['content'])) : 'http://';?>
					<?php echo input_text('ta',$content['content']);?>
					<?php echo tooltip($_lang['resource_weblink_help']);?>
				</td>
			</tr>
				
<?php
	}
?>
			<tr style="height: 24px;">
				<td valign="top" width="100">
					<span class="warning"><?php echo $_lang['resource_summary']?></span>
				</td>
                <td valign="top">
                	<textarea name="introtext" class="inputBox" rows="3" cols="" onchange="documentDirty=true;"><?php echo to_safestr($content['introtext'])?></textarea>
                	<?php echo tooltip($_lang['resource_summary_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td>
					<span class="warning"><?php echo $_lang['page_data_template']?></span>
				</td>
				<td>
					<select id="template" name="template" class="inputBox" onchange="changeTemplate();" style="width:308px">
					<option value="0">(blank)</option>
<?php
				$from = "{$tbl_site_templates} t LEFT JOIN {$tbl_categories} c ON t.category = c.id";
				$rs = $modx->db->select('t.templatename, t.id, c.category',$from,'', 'c.category, t.templatename ASC');

				$currentCategory = '';
				while ($row = $modx->db->getRow($rs))
				{
					$thisCategory = $row['category'];
					if($thisCategory == null)
					{
						$thisCategory = $_lang["no_category"];
					}
					if($thisCategory != $currentCategory)
					{
						if($closeOptGroup)
						{
							echo "\t\t\t\t\t</optgroup>\n";
						}
						echo "\t\t\t\t\t<optgroup label=\"$thisCategory\">\n";
						$closeOptGroup = true;
					}
					else
					{
						$closeOptGroup = false;
					}
					if (isset($_REQUEST['newtemplate']))
					{
						$selectedtext = $row['id'] == $_REQUEST['newtemplate'] ? ' selected="selected"' : '';
					}
					else
					{
						if (isset ($content['template']))
						{
							$selectedtext = $row['id'] == $content['template'] ? ' selected="selected"' : '';
						}
						else
						{
							switch($auto_template_logic)
							{
								case 'sibling':
									if(!isset($_GET['pid']))
									{
										break; // default_template is already set
									}
									elseif($sibl = $modx->getDocumentChildren($_REQUEST['pid'], 1, 0, 'template', '', 'menuindex', 'ASC', 1))
									{
										$default_template = $sibl[0]['template'];
										break;
									}
									elseif($sibl = $modx->getDocumentChildren($_REQUEST['pid'], 0, 0, 'template', '', 'menuindex', 'ASC', 1))
									{
										$default_template = $sibl[0]['template'];
										break;
									}
								case 'parent':
									if ($parent = $modx->getPageInfo($_REQUEST['pid'], 0, 'template'))
									{
										$default_template = $parent['template'];
										break;
									}
								case 'system':
									default:
									// default_template is already set
							}
							$selectedtext = $row['id'] == $default_template ? ' selected="selected"' : '';
						}
					}
					echo "\t\t\t\t\t".'<option value="'.$row['id'].'"'.$selectedtext.'>'.$row['templatename']."</option>\n";
					$currentCategory = $thisCategory;
				}
				if($thisCategory != '')
				{
					echo "\t\t\t\t\t</optgroup>\n";
				}
?>
					</select>
					<?php echo tooltip($_lang['page_data_template_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td style="width:100px;">
					<span class="warning"><?php echo $_lang['resource_opt_menu_title']?></span>
				</td>
				<td>
					<?php echo input_text('menutitle',to_safestr($content['menutitle']));?>
					<?php echo tooltip($_lang['resource_opt_menu_title_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td style="width:100px;">
					<span class="warning"><?php echo $_lang['resource_opt_menu_index']?></span>
				</td>
				<td>
					<table cellpadding="0" cellspacing="0" style="width:333px;">
						<tr>
							<td>
								<?php echo input_text('menuindex',$content['menuindex'],'class="number" style="width:40px;"','5');?>
								<input type="button" value="&lt;" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();documentDirty=true;" />
								<input type="button" value="&gt;" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();documentDirty=true;" />
								<?php echo tooltip($_lang['resource_opt_menu_index_help']);?>
							</td>
							<td style="text-align:right;">
								<span class="warning"><?php echo $_lang['resource_opt_show_menu']?></span>&nbsp;
								<?php
									$cond = ($content['hidemenu']!=1);
									echo input_checkbox('hidemenu',$cond);
									echo input_hidden('hidemenu',!$cond);
									echo tooltip($_lang['resource_opt_show_menu_help']);?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2"><div class="split"></div></td>
			</tr>
			<tr style="height: 24px;">
				<td valign="top">
					<span class="warning"><?php echo $_lang['resource_parent']?></span>
				</td>
				<td valign="top">
<?php
				$parentlookup = false;
				if (isset ($_REQUEST['id']))
				{
					if ($content['parent'] == 0) $parentname   = $site_name;
					else                         $parentlookup = $content['parent'];
				}
				elseif (isset ($_REQUEST['pid']))
				{
					if ($_REQUEST['pid'] == 0)   $parentname   = $site_name;
					else                         $parentlookup = $_REQUEST['pid'];
				}
				elseif (isset($_POST['parent']))
				{
					if ($_POST['parent'] == 0)   $parentname = $site_name;
					else                         $parentlookup = $_POST['parent'];
				}
				else
				{
					                             $parentname = $site_name;
					                             $content['parent'] = 0;
				}
				if($parentlookup !== false && is_numeric($parentlookup))
				{
					$rs = $modx->db->select('pagetitle',$tbl_site_content,"id='{$parentlookup}'");
					$limit = $modx->db->getRecordCount($rs);
					if ($limit != 1)
					{
						$e->setError(8);
						$e->dumpError();
					}
					$parentrs = $modx->db->getRow($rs);
					$parentname = $parentrs['pagetitle'];
				}
?>
					&nbsp;<img alt="tree_folder" name="plock" src="<?php echo $_style["tree_folder"] ?>" onclick="enableParentSelection(!allowParentSelection);" style="cursor:pointer;" />
					<b><span id="parentName" onclick="enableParentSelection(!allowParentSelection);" style="cursor:pointer;" >
					<?php echo isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']?> (<?php echo $parentname?>)</span></b>
					<?php echo tooltip($_lang['resource_parent_help']);?>
					<input type="hidden" name="parent" value="<?php echo isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']?>" onchange="documentDirty=true;" />
				</td>
			</tr>
		</table>
<?php
		if ($content['type'] == 'document' || $_REQUEST['a'] == '4')
		{
?>
		<!-- Content -->
		<div class="sectionHeader" id="content_header"><?php echo $_lang['resource_content']?></div>
		<div class="sectionBody" id="content_body">
<?php
			if (($_REQUEST['a'] == '4' || $_REQUEST['a'] == '27') && $use_editor == 1 && $content['richtext'] == 1)
			{
				$htmlContent = $content['content'];
?>
		<div>
			<textarea id="ta" name="ta" cols="" rows="" style="width:100%; height: 400px;" onchange="documentDirty=true;"><?php echo htmlspecialchars($htmlContent)?></textarea>
			<span class="warning"><?php echo $_lang['which_editor_title']?></span>
			<select id="which_editor" name="which_editor" onchange="changeRTE();">
				<option value="none"><?php echo $_lang['none']?></option>
<?php
				// invoke OnRichTextEditorRegister event
				$evtOut = $modx->invokeEvent("OnRichTextEditorRegister");
				if (is_array($evtOut))
				{
					for ($i = 0; $i < count($evtOut); $i++)
					{
						$editor = $evtOut[$i];
						echo "\t\t\t",'<option value="',$editor,'"',($which_editor == $editor ? ' selected="selected"' : ''),'>',$editor,"</option>\n";
					}
				}
?>
			</select>
		</div>
<?php
				$replace_richtexteditor = array(
					'ta',
				);
			}
			else
			{
				echo "\t".'<div><textarea class="phptextarea" id="ta" name="ta" style="width:100%; height: 400px;" onchange="documentDirty=true;">',htmlspecialchars($content['content']),'</textarea></div>'."\n";
			}
?>
		</div><!-- end .sectionBody -->
<?php
		}
?>

<?php
		if (($content['type'] == 'document' || $_REQUEST['a'] == '4') || ($content['type'] == 'reference' || $_REQUEST['a'] == 72))
		{
?>
		<!-- Template Variables -->
			<div class="sectionHeader" id="tv_header"><?php echo $_lang['settings_templvars']?></div>
			<div class="sectionBody tmplvars" id="tv_body">
<?php
				$template = $default_template;
				if (isset ($_REQUEST['newtemplate'])) {
					$template = $_REQUEST['newtemplate'];
				} else {
					if (isset ($content['template']))
						$template = $content['template'];
				}
				$session_mgrRole = $_SESSION['mgrRole'];
				$where_docgrp = !$docgrp ? '' : " OR tva.documentgroup IN ({$docgrp})";
				
				$fields = "DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value";
				$from = trim("
					{$tbl_site_tmplvars}                         AS tv 
					INNER JOIN {$tbl_site_tmplvar_templates}     AS tvtpl ON tvtpl.tmplvarid = tv.id 
					LEFT  JOIN {$tbl_site_tmplvar_contentvalues} AS tvc   ON tvc.tmplvarid   = tv.id AND tvc.contentid='{$id}'
					LEFT  JOIN {$tbl_site_tmplvar_access}        AS tva   ON tva.tmplvarid   = tv.id
					");
				$where = trim("
					tvtpl.templateid='{$template}'
					AND (1='{$session_mgrRole}' OR ISNULL(tva.documentgroup) {$where_docgrp})
					");
				$rs = $modx->db->select($fields,$from,$where,'tvtpl.rank,tv.rank, tv.id');
				$num_of_tv = $modx->db->getRecordCount($rs);
				echo '<script type="text/javascript">var num_of_tv=' . $num_of_tv . ';</script>';
				if ($num_of_tv > 0)
				{
					echo "\t".'<table style="position:relative;" border="0" cellspacing="0" cellpadding="3" width="96%">'."\n";
					require_once(MODX_MANAGER_PATH.'includes/tmplvars.inc.php');
					require_once(MODX_MANAGER_PATH.'includes/tmplvars.commands.inc.php');
					while($row = $modx->db->getRow($rs))
					{
						// Go through and display all Template Variables
						if ($row['type'] == 'richtext' || $row['type'] == 'htmlarea')
						{
							// Add richtext editor to the list
							if (is_array($replace_richtexteditor))
							{
								$replace_richtexteditor = array_merge($replace_richtexteditor, array('tv' . $row['id']));
							}
							else
							{
								$replace_richtexteditor = array('tv' . $row['id']);
							}
						}
						// splitter
						if ($i > 0 && $i < $num_of_tv)
							echo "\t\t",'<tr><td colspan="2"><div class="split"></div></td></tr>',"\n";
						
						// post back value
						if(array_key_exists('tv'.$row['id'], $_POST))
						{
							if($row['type'] == 'listbox-multiple')
							{
								$tvPBV = implode('||', $_POST['tv'.$row['id']]);
							}
							else
							{
								$tvPBV = $_POST['tv'.$row['id']];
							}
						}
						else
						{
							$tvPBV = $row['value'];
						}

						$zindex = $row['type'] == 'date' ? '100' : '500';
						echo "\t\t",'<tr style="height: 24px;"><td valign="top" width="150"><span class="warning">',$row['caption'],"</span>\n",
						     "\t\t\t",'<br /><span class="comment">',$row['description'],"</span></td>\n",
                             "\t\t\t",'<td valign="top" style="position:relative;',($row['type'] == 'date' ? 'z-index:{$zindex};' : ''),'">',"\n",
                             "\t\t\t",renderFormElement($row['type'], $row['id'], $row['default_text'], $row['elements'], $tvPBV, '', $row),"\n",
						     "\t\t</td></tr>\n";
					}
					echo "\t</table>\n";
				}
				else
				{
					// There aren't any Template Variables
					echo "\t<p>".$_lang['tmplvars_novars']."</p>\n";
				}
			?>
			</div>
			<!-- end .sectionBody .tmplvars -->
		<?php } ?>

	</div><!-- end #tabGeneral -->

	<!-- Settings -->
	<div class="tab-page" id="tabSettings">
		<h2 class="tab"><?php echo $_lang['settings_page_settings']?></h2>
		<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabSettings" ) );</script>

		<table width="99%" border="0" cellspacing="5" cellpadding="0">
		<?php $pub_disabled = disabled(!$modx->hasPermission('publish_document') || $id==$modx->config['site_start']); ?>
			<tr style="height: 24px;">
				<td width="150"><span class="warning"><?php echo $_lang['resource_opt_published']?></span></td>
				<td>
				<?php
					$cond = (isset($content['published']) && $content['published']==1) || (!isset($content['published']) && $publish_default==1);
					echo input_checkbox('published',$cond);
					echo input_hidden('published',$cond);
					echo tooltip($_lang['resource_opt_published_help']);
				?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td width="150"><span class="warning"><?php echo $_lang['page_data_publishdate']?></span></td>
				<td>
				<?php
					$content['pub_date'] = (isset($content['pub_date']) && $content['pub_date']!='0') ? $modx->toDateFormat($content['pub_date']) : '';
				?>
				<input type="text" id="pub_date" pattern="^[0-9\/\:\- ]+$" <?php echo $pub_disabled ?> name="pub_date" class="DatePicker" value="<?php echo $content['pub_date'];?>" onblur="documentDirty=true;" />
                <a onclick="document.mutate.pub_date.value=''; documentDirty=true; return true;" onmouseover="window.status='<?php echo $_lang['remove_date']?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand;">
				<img src="<?php echo $_style["icons_cal_nodate"] ?>" alt="<?php echo $_lang['remove_date']?>" /></a>
				<?php echo tooltip($_lang['page_data_publishdate_help']);?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td style="color: #555;font-size:10px"><em> <?php echo $modx->config['datetime_format']; ?> HH:MM:SS</em></td>
			</tr>
			<tr style="height: 24px;">
				<td><span class="warning"><?php echo $_lang['page_data_unpublishdate']?></span></td>
				<td>
				<?php
					$content['unpub_date'] = (isset($content['unpub_date']) && $content['unpub_date']!='0') ? $modx->toDateFormat($content['unpub_date']) : '';
				?>
				<input type="text" id="unpub_date" pattern="^[0-9\/\:\- ]+$" <?php echo $pub_disabled ?> name="unpub_date" class="DatePicker" value="<?php echo $content['unpub_date'];?>" onblur="documentDirty=true;" />
				<a onclick="document.mutate.unpub_date.value=''; documentDirty=true; return true;" onmouseover="window.status='<?php echo $_lang['remove_date']?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand">
				<img src="<?php echo $_style["icons_cal_nodate"] ?>" alt="<?php echo $_lang['remove_date']?>" /></a>
				<?php echo tooltip($_lang['page_data_unpublishdate_help']);?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td style="color: #555;font-size:10px"><em> <?php echo $modx->config['datetime_format']; ?> HH:MM:SS</em></td>
			</tr>
			<tr>
				<td colspan="2"><div class="split"></div></td>
			</tr>
		
<?php

if ($_SESSION['mgrRole'] == 1 || $_REQUEST['a'] != '27' || $_SESSION['mgrInternalKey'] == $content['createdby']) {
?>
			<tr style="height: 24px;"><td><span class="warning"><?php echo $_lang['resource_type']?></span></td>
				<td><select name="type" class="inputBox" onchange="documentDirty=true;" style="width:200px">

                    <option value="document"<?php echo (($content['type'] == 'document' || $_REQUEST['a'] == '85' || $_REQUEST['a'] == '4') ? ' selected="selected"' : "");?> ><?php echo $_lang["resource_type_webpage"];?></option>
                    <option value="reference"<?php echo (($content['type'] == 'reference' || $_REQUEST['a'] == '72') ? ' selected="selected"' : "");?> ><?php echo $_lang["resource_type_weblink"];?></option>
					</select>
					<?php echo tooltip($_lang['resource_type_message']);?>
					</td>
				</tr>
<?php
				if($content['type'] !== 'reference' && $_REQUEST['a'] !== '72')
				{
?>
			<tr style="height: 24px;"><td><span class="warning"><?php echo $_lang['page_data_contentType']?></span></td>
				<td><select name="contentType" class="inputBox" onchange="documentDirty=true;" style="width:200px">
			<?php
				if (!$content['contentType'])
					$content['contentType'] = 'text/html';
				$custom_contenttype = (isset ($custom_contenttype) ? $custom_contenttype : "text/html,text/plain,text/xml");
				$ct = explode(",", $custom_contenttype);
				for ($i = 0; $i < count($ct); $i++) {
					echo "\t\t\t\t\t".'<option value="'.$ct[$i].'"'.($content['contentType'] == $ct[$i] ? ' selected="selected"' : '').'>'.$ct[$i]."</option>\n";
				}
			?>
				</select>
				<?php echo tooltip($_lang['page_data_contentType_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;"><td><span class="warning"><?php echo $_lang['resource_opt_contentdispo']?></span></td>
				<td><select name="content_dispo" size="1" onchange="documentDirty=true;" style="width:200px">
					<option value="0"<?php echo !$content['content_dispo'] ? ' selected="selected"':''?>><?php echo $_lang['inline']?></option>
					<option value="1"<?php echo $content['content_dispo']==1 ? ' selected="selected"':''?>><?php echo $_lang['attachment']?></option>
				</select>
				<?php echo tooltip($_lang['resource_opt_contentdispo_help']);?>
				</td>
			</tr>
<?php
				}
?>
			<tr>
				<td colspan="2"><div class="split"></div></td>
			</tr>
<?php
} else {
    if ($content['type'] != 'reference' && $_REQUEST['a'] != '72') {
    	// non-admin managers creating or editing a document resource
?>
            <input type="hidden" name="contentType" value="<?php echo isset($content['contentType']) ? $content['contentType'] : "text/html"?>" />
            <input type="hidden" name="type" value="document" />
            <input type="hidden" name="content_dispo" value="<?php echo isset($content['content_dispo']) ? $content['content_dispo'] : '0'?>" />
<?php
    } else {
    	// non-admin managers creating or editing a reference (weblink) resource
?>
            <input type="hidden" name="type" value="reference" />
            <input type="hidden" name="contentType" value="text/html" />
<?php
    }
}//if mgrRole
?>

			<tr style="height: 24px;">
				<td width="150"><span class="warning"><?php echo $_lang['resource_opt_folder']?></span></td>
				<td>
				<?php
					$cond = ($content['isfolder']==1||$_REQUEST['a']=='85');
					echo input_checkbox('isfolder',$cond);
					echo input_hidden('isfolder',$cond);
					echo tooltip($_lang['resource_opt_folder_help']);?>
				</td>
			</tr>
<?php
					if($content['type'] !== 'reference' && $_REQUEST['a'] !== '72')
					{
?>
			<tr style="height: 24px;">
				<td><span class="warning"><?php echo $_lang['resource_opt_richtext']?></span></td>
				<td>
				<?php
					$disabled = ($use_editor!=1) ? ' disabled="disabled"' : '';
					$cond = (!isset($content['richtext']) || $content['richtext']!=0 || $_REQUEST['a']!='27');
					echo input_checkbox('richtext',$cond,$disabled);
					echo input_hidden('richtext',$cond);
					echo tooltip($_lang['resource_opt_richtext_help']);?>
				</td>
			</tr>
<?php
					}
?>
			<tr style="height: 24px;">
				<td width="150"><span class="warning"><?php echo $_lang['track_visitors_title']?></span></td>
				<td>
				<?php
					$cond = ($content['donthit']!=1);
					echo input_checkbox('donthit',$cond);
					echo input_hidden('donthit',!$cond);
					echo tooltip($_lang['resource_opt_trackvisit_help']);?>
				</td>
			</tr>
			<tr style="height: 24px;">
				<td><span class="warning"><?php echo $_lang['page_data_searchable']?></span></td>
				<td>
				<?php
					$cond = ((isset($content['searchable']) && $content['searchable']==1) || (!isset($content['searchable']) && $search_default==1));
					echo input_checkbox('searchable',$cond);
					echo input_hidden('searchable',$cond);
					echo tooltip($_lang['page_data_searchable_help']);?>
				</td>
			</tr>
<?php
				if($content['type'] !== 'reference' && $_REQUEST['a'] !== '72')
				{
?>
			<tr style="height: 24px;">
				<td><span class="warning"><?php echo $_lang['page_data_cacheable']?></span></td>
				<td>
				<?php
					$cond = ((isset($content['cacheable']) && $content['cacheable']==1) || (!isset($content['cacheable']) && $cache_default==1));
					$disabled = ($cache_enabled==0) ? ' disabled="disabled"' : '';
					echo input_checkbox('cacheable',$cond,$disabled);
					echo input_hidden('cacheable',$cond);
					echo tooltip($_lang['page_data_cacheable_help']);?>
				</td>
			</tr>
<?php
				}
?>
			<tr style="height: 24px;">
				<td><span class="warning"><?php echo $_lang['resource_opt_emptycache']?></span></td>
				<td>
				<?php
					$disabled = ($cache_enabled==0) ? ' disabled="disabled"' : '';
					echo input_checkbox('syncsite',true,$disabled);
					echo input_hidden('syncsite');
					echo tooltip($_lang['resource_opt_emptycache_help']);?>
				</td>
			</tr>
		</table>
	</div><!-- end #tabSettings -->

<?php
	if ($modx->hasPermission('edit_doc_metatags') && $modx->config['show_meta']) {
	// get list of site keywords
	$keywords = array();
	$ds = $modx->db->select('id,keyword', $tbl_site_keywords, '', 'keyword ASC');
	$limit = $modx->db->getRecordCount($ds);
	if ($limit > 0) {
		for ($i = 0; $i < $limit; $i++) {
			$row = $modx->db->getRow($ds);
			$keywords[$row['id']] = $row['keyword'];
		}
	}
	// get selected keywords using document's id
	if (isset ($content['id']) && count($keywords) > 0) {
		$keywords_selected = array();
		$ds = $modx->db->select('keyword_id', $tbl_keyword_xref, "content_id='{$content['id']}'");
		$limit = $modx->db->getRecordCount($ds);
		if ($limit > 0) {
			for ($i = 0; $i < $limit; $i++) {
				$row = $modx->db->getRow($ds);
				$keywords_selected[$row['keyword_id']] = ' selected="selected"';
			}
		}
	}

	// get list of site META tags
	$metatags = array();
	$ds = $modx->db->select('*', $tbl_site_metatags);
	$limit = $modx->db->getRecordCount($ds);
	if ($limit > 0) {
		for ($i = 0; $i < $limit; $i++) {
			$row = $modx->db->getRow($ds);
			$metatags[$row['id']] = $row['name'];
		}
	}
	// get selected META tags using document's id
	if (isset ($content['id']) && count($metatags) > 0) {
		$metatags_selected = array();
		$ds = $modx->db->select('metatag_id', $tbl_site_content_metatags, "content_id='{$content['id']}'");
		$limit = $modx->db->getRecordCount($ds);
		if ($limit > 0)
		{
			for ($i = 0; $i < $limit; $i++)
			{
				$row = $modx->db->getRow($ds);
				$metatags_selected[$row['metatag_id']] = ' selected="selected"';
			}
		}
	}
	?>
	<!-- META Keywords -->
	<div class="tab-page" id="tabMeta">
		<h2 class="tab"><?php echo $_lang['meta_keywords']?></h2>
		<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabMeta" ) );</script>

		<table width="99%" border="0" cellspacing="5" cellpadding="0">
		<tr style="height: 24px;"><td><?php echo $_lang['resource_metatag_help']?><br /><br />
			<table border="0" style="width:inherit;">
			<tr>
				<td><span class="warning"><?php echo $_lang['keywords']?></span><br />
				<select name="keywords[]" multiple="multiple" size="16" class="inputBox" style="width: 200px;" onchange="documentDirty=true;">
				<?php
					$keys = array_keys($keywords);
					for ($i = 0; $i < count($keys); $i++) {
						$key = $keys[$i];
						$value = $keywords[$key];
						$selected = $keywords_selected[$key];
						echo "\t\t\t\t".'<option value="'.$key.'"'.$selected.'>'.$value."</option>\n";
					}
				?>
				</select>
				<br />
				<input type="button" value="<?php echo $_lang['deselect_keywords']?>" onclick="clearKeywordSelection();" />
				</td>
				<td><span class="warning"><?php echo $_lang['metatags']?></span><br />
				<select name="metatags[]" multiple="multiple" size="16" class="inputBox" style="width: 220px;" onchange="documentDirty=true;">
				<?php
					$keys = array_keys($metatags);
					for ($i = 0; $i < count($keys); $i++) {
						$key = $keys[$i];
						$value = $metatags[$key];
						$selected = $metatags_selected[$key];
						echo "\t\t\t\t".'<option value="'.$key.'"'.$selected.'>'.$value."</option>\n";
					}
				?>
				</select>
				<br />
				<input type="button" class="button" value="<?php echo $_lang['deselect_metatags']?>" onclick="clearMetatagSelection();" />
				</td>
			</tr>
			</table>
			</td>
		</tr>
		</table>
	</div><!-- end #tabMeta -->
<?php
}

/*******************************
 * Document Access Permissions */
if ($use_udperms == 1)
{
	$groupsarray = array();
	
	if($_REQUEST['a'] == '27')       $docid = $id;
	elseif(!empty($_REQUEST['pid'])) $docid = $_REQUEST['pid'];
	else                             $docid = $content['parent'];
	
	if ($docid > 0)
	{
		// Load up, the permissions from the parent (if new document) or existing document
		$rs = $modx->db->select('id, document_group',$tbl_document_groups,"document='{$docid}'");
		while ($currentgroup = $modx->db->getRow($rs))
		{
			$groupsarray[] = $currentgroup['document_group'].','.$currentgroup['id'];
		}
		// Load up the current permissions and names
		$field = 'dgn.*, groups.id AS link_id';
		$from  = "{$tbl_document_group_names} AS dgn LEFT JOIN {$tbl_document_groups} AS groups ON groups.document_group = dgn.id  AND groups.document = {$docid}";
	}
	else
	{
		// Just load up the names, we're starting clean
		$field = '*, NULL AS link_id';
		$from  = $tbl_document_group_names;
	}
	// Query the permissions and names from above
	$rs = $modx->db->select($field,$from,'','name');

	$isManager = $modx->hasPermission('access_permissions');
	$isWeb     = $modx->hasPermission('web_access_permissions');

	// Setup Basic attributes for each Input box
	$inputAttributes['type']    = 'checkbox';
	$inputAttributes['class']   = 'checkbox';
	$inputAttributes['name']    = 'docgroups[]';
	$inputAttributes['onclick'] = 'makePublic(false)';
	
	$permissions = array(); // New Permissions array list (this contains the HTML)
	$permissions_yes = 0; // count permissions the current mgr user has
	$permissions_no = 0; // count permissions the current mgr user doesn't have

	// retain selected doc groups between post
	if (isset($_POST['docgroups']))
		$groupsarray = array_merge($groupsarray, $_POST['docgroups']);

	// Loop through the permissions list
	while($row = $modx->db->getRow($rs))
	{
		// Create an inputValue pair (group ID and group link (if it exists))
		$inputValue = $row['id'].','.($row['link_id'] ? $row['link_id'] : 'new');
		$inputId    = 'group-'.$row['id'];

		$checked    = in_array($inputValue, $groupsarray);
		if ($checked) $notPublic = true; // Mark as private access (either web or manager)

		// Skip the access permission if the user doesn't have access...
		if ((!$isManager && $row['private_memgroup'] == '1') || (!$isWeb && $row['private_webgroup'] == '1'))
			continue;

		// Setup attributes for this Input box
		$inputAttributes['id']    = $inputId;
		$inputAttributes['value'] = $inputValue;
		if ($checked)
		        $inputAttributes['checked'] = 'checked';
		else    unset($inputAttributes['checked']);

		// Create attribute string list
		$inputString = array();
		foreach ($inputAttributes as $k => $v)
		{
			$inputString[] = $k.'="'.$v.'"';
		}

		// Make the <input> HTML
        $inputHTML = '<input '.implode(' ', $inputString).' />' . "\n";

		// does user have this permission?
		$from = "{$tbl_membergroup_access} mga, {$tbl_member_groups} mg";
		$where = "mga.membergroup = mg.user_group AND mga.documentgroup = {$row['id']} AND mg.member = {$_SESSION['mgrInternalKey']}";
		$rsp = $modx->db->select('COUNT(mg.id)',$from,$where);
		$count = $modx->db->getValue($rsp);
		if($count > 0) {
			++$permissions_yes;
		} else {
			++$permissions_no;
		}
		$permissions[] = "\t\t".'<li>'.$inputHTML.'<label for="'.$inputId.'">'.$row['name'].'</label></li>';
	}
	// if mgr user doesn't have access to any of the displayable permissions, forget about them and make doc public
	if($_SESSION['mgrRole'] != 1 && ($permissions_yes == 0 && $permissions_no > 0)) {
		$permissions = array();
	}

	// See if the Access Permissions section is worth displaying...
	if (!empty($permissions)) {
		// Add the "All Document Groups" item if we have rights in both contexts
		if ($isManager && $isWeb)
			array_unshift($permissions,"\t\t".'<li><input type="checkbox" class="checkbox" name="chkalldocs" id="groupall"' . checked(!$notPublic) . ' onclick="makePublic(true);" /><label for="groupall" class="warning">' . $_lang['all_doc_groups'] . '</label></li>');
		// Output the permissions list...
?>
<!-- Access Permissions -->
<div class="tab-page" id="tabAccess">
	<h2 class="tab" id="tab_access_header"><?php echo $_lang['access_permissions']?></h2>
	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabAccess" ) );</script>
	<script type="text/javascript">
		/* <![CDATA[ */
		function makePublic(b) {
			var notPublic = false;
			var f = document.forms['mutate'];
			var chkpub = f['chkalldocs'];
			var chks = f['docgroups[]'];
			if (!chks && chkpub) {
				chkpub.checked=true;
				return false;
			} else if (!b && chkpub) {
				if (!chks.length) notPublic = chks.checked;
				else for (i = 0; i < chks.length; i++) if (chks[i].checked) notPublic = true;
				chkpub.checked = !notPublic;
			} else {
				if (!chks.length) chks.checked = (b) ? false : chks.checked;
				else for (i = 0; i < chks.length; i++) if (b) chks[i].checked = false;
				chkpub.checked = true;
			}
		}
		/* ]]> */
	</script>
	<p><?php echo $_lang['access_permissions_docs_message']?></p>
	<ul>
	<?php echo implode("\n", $permissions)."\n"; ?>
	</ul>
</div><!--div class="tab-page" id="tabAccess"-->
<?php
	} // !empty($permissions)
	elseif($_SESSION['mgrRole'] != 1 && ($permissions_yes == 0 && $permissions_no > 0)
	   && ($_SESSION['mgrPermissions']['access_permissions'] == 1
	   || $_SESSION['mgrPermissions']['web_access_permissions'] == 1))
	{
?>
	<p><?php echo $_lang["access_permissions_docs_collision"];?></p>
<?php

	}
}
/* End Document Access Permissions *
 ***********************************/
?>

<input type="submit" name="save" style="display:none" />
<?php

// invoke OnDocFormRender event
$evtOut = $modx->invokeEvent('OnDocFormRender', array(
	'id' => $id,
));
if (is_array($evtOut)) echo implode('', $evtOut);
?>
</div><!--div class="tab-pane" id="documentPane"-->
</div><!--div class="sectionBody"-->
</fieldset>
</form>

<script type="text/javascript">
    storeCurTemplate();
</script>
<?php
	if (($_REQUEST['a'] == '4' || $_REQUEST['a'] == '27') && $use_editor == 1 && $content['richtext'] == 1) {
		if (is_array($replace_richtexteditor)) {
			// invoke OnRichTextEditorInit event
			$evtOut = $modx->invokeEvent('OnRichTextEditorInit', array(
				'editor' => $which_editor,
				'elements' => $replace_richtexteditor
			));
			if (is_array($evtOut))
				echo implode('', $evtOut);
		}
	}

function to_safestr($str)
{
	return htmlspecialchars(stripslashes($str));
}

function input_text($name,$value,$other='',$maxlength='255')
{
	global $modx;
	
	$ph['name']      = $name;
	$ph['value']     = $value;
	$ph['maxlength'] = $maxlength;
	$ph['other']     = $other;
	
	$tpl = '<input name="[+name+]" type="text" maxlength="[+maxlength+]" value="[+value+]" class="inputBox" onchange="documentDirty=true;" [+other+] />';
	return $modx->parsePlaceholder($tpl,$ph);
}

function input_checkbox($name,$checked,$other='')
{
	global $modx;
	$ph['name']    = $name;
	$ph['checked'] = ($checked) ? 'checked="checked"' : '';
	$ph['other']   = $other;
	$ph['resetpubdate'] = ($name == 'published') ? 'resetpubdate();' : '';
	if($name === 'published')
	{
		$id = (isset($_REQUEST['id'])) ? (int)$_REQUEST['id'] : 0;
		if(!$modx->hasPermission('publish_document') || $id==$modx->config['site_start'])
		{
			$ph['other'] = 'disabled="disabled"';
		}
	}
	$tpl = '<input name="[+name+]check" type="checkbox" class="checkbox" [+checked+] onclick="changestate(document.mutate.[+name+]);[+resetpubdate+]" [+other+] />';
	return $modx->parsePlaceholder($tpl,$ph);
}

function checked($cond=false)
{
	if($cond) return ' checked="checked"';
}

function disabled($cond=false)
{
	if($cond) return ' disabled="disabled"';
}

function tooltip($msg)
{
	global $modx,$_style;
	
	$ph['icons_tooltip'] = "'{$_style['icons_tooltip']}'";
	$ph['icons_tooltip_over'] = $_style['icons_tooltip_over'];
	$ph['msg'] = $msg;
	$tpl = '&nbsp;&nbsp;<img src="[+icons_tooltip_over+]" onmouseover="this.src=[+icons_tooltip+];" onmouseout="this.src=\'[+icons_tooltip_over+]\'" alt="[+msg+]" onclick="alert(this.alt);" style="cursor:help;" />';
	return $modx->parsePlaceholder($tpl,$ph);
}

function input_hidden($name,$cond=true)
{
	global $modx;
	
	$ph['name']  = $name;
	$ph['value'] = ($cond) ? '1' : '0';
	$tpl = '<input type="hidden" name="[+name+]" class="hidden" value="[+value+]" onchange="documentDirty=true;" />';
	return $modx->parsePlaceholder($tpl,$ph);
}
