<?php
if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}

switch($modx->manager->action) {
	case 78:
		if(!$modx->hasPermission('edit_chunk')) {
			$modx->webAlertAndQuit($_lang["error_no_privileges"]);
		}
		break;
	case 77:
		if(!$modx->hasPermission('new_chunk')) {
			$modx->webAlertAndQuit($_lang["error_no_privileges"]);
		}
		break;
	default:
		$modx->webAlertAndQuit($_lang["error_no_privileges"]);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

// Get table names (alphabetical)
$tbl_site_htmlsnippets = $modx->getFullTableName('site_htmlsnippets');

// check to see the snippet editor isn't locked
if($lockedEl = $modx->elementIsLocked(3, $id)) {
	$modx->webAlertAndQuit(sprintf($_lang['lock_msg'], $lockedEl['username'], $_lang['chunk']));
}
// end check for lock

// Lock snippet for other users to edit
$modx->lockElement(3, $id);

$content = array();
if(isset($_REQUEST['id']) && $_REQUEST['id'] != '' && is_numeric($_REQUEST['id'])) {
	$rs = $modx->db->select('*', $tbl_site_htmlsnippets, "id='{$id}'");
	$content = $modx->db->getRow($rs);
	if(!$content) {
		$modx->webAlertAndQuit("Chunk not found for id '{$id}'.");
	}
	$_SESSION['itemname'] = $content['name'];
	if($content['locked'] == 1 && $_SESSION['mgrRole'] != 1) {
		$modx->webAlertAndQuit($_lang["error_no_privileges"]);
	}
} else if(isset($_REQUEST['itemname'])) {
	$content['name'] = $_REQUEST['itemname'];
} else {
	$_SESSION['itemname'] = $_lang["new_htmlsnippet"];
	$content['category'] = intval($_REQUEST['catid']);
}

if($modx->manager->hasFormValues()) {
	$modx->manager->loadFormValues();
}

if(isset($_POST['which_editor'])) {
	$which_editor = $_POST['which_editor'];
} else {
	$which_editor = $content['editor_name'] != 'none' ? $content['editor_name'] : 'none';
}

$content = array_merge($content, $_POST);

// Add lock-element JS-Script
$lockElementId = $id;
$lockElementType = 3;
require_once(MODX_MANAGER_PATH . 'includes/active_user_locks.inc.php');

// Print RTE Javascript function
?>
<script language="javascript" type="text/javascript">
	// Added for RTE selection
	function changeRTE() {
		var whichEditor = document.getElementById('which_editor');
		if(whichEditor) for(var i = 0; i < whichEditor.length; i++) {
			if(whichEditor[i].selected) {
				newEditor = whichEditor[i].value;
				break;
			}
		}

		documentDirty = false;
		document.mutate.a.value = <?php echo $action ?>;
		document.mutate.which_editor.value = newEditor;
		document.mutate.submit();
	}

	var actions = {
		save: function() {
			documentDirty = false;
			form_save = true;
			document.mutate.save.click();
		},
		duplicate: function() {
			if(confirm("<?php echo $_lang['confirm_duplicate_record'] ?>") === true) {
				documentDirty = false;
				document.location.href = "index.php?id=<?php echo $_REQUEST['id'] ?>&a=97";
			}
		},
		delete: function() {
			if(confirm("<?php echo $_lang['confirm_delete_htmlsnippet'] ?>") === true) {
				documentDirty = false;
				document.location.href = "index.php?id=" + document.mutate.id.value + "&a=80";
			}
		},
		cancel: function() {
			documentDirty = false;
			document.location.href = 'index.php?a=76';
		}
	};

	document.addEventListener('DOMContentLoaded', function() {
		var h1help = document.querySelector('h1 > .help');
		h1help.onclick = function() {
			document.querySelector('.element-edit-message').classList.toggle('show')
		}
	});

</script>

<form class="htmlsnippet" id="mutate" name="mutate" method="post" action="index.php">
	<?php

	// invoke OnChunkFormPrerender event
	$evtOut = $modx->invokeEvent('OnChunkFormPrerender', array(
		'id' => $id,
	));
	if(is_array($evtOut)) {
		echo implode('', $evtOut);
	}

	?>
	<input type="hidden" name="a" value="79" />
	<input type="hidden" name="id" value="<?php echo $_REQUEST['id']; ?>" />
	<input type="hidden" name="mode" value="<?php echo $modx->manager->action; ?>" />

	<h1>
		<i class="fa fa-th-large"></i><?php echo $_lang['htmlsnippet_title']; ?><i class="fa fa-question-circle help"></i>
	</h1>

	<?php echo $_style['actionbuttons']['dynamic']['element'] ?>

	<div class="tab-pane" id="chunkPane">
		<script type="text/javascript">
			tpChunk = new WebFXTabPane(document.getElementById("chunkPane"), <?php echo $modx->config['remember_last_tab'] == 1 ? 'true' : 'false'; ?> );
		</script>

		<div class="tab-page" id="tabGeneral">
			<h2 class="tab"><?php echo $_lang["settings_general"] ?></h2>
			<script type="text/javascript">tpChunk.addTabPage(document.getElementById("tabGeneral"));</script>

			<div class="element-edit-message alert alert-info">
				<?php echo $_lang['htmlsnippet_msg'] ?>
			</div>

			<div class="form-group">
				<div class="row form-row">
					<label class="col-md-3 col-lg-2"><?php echo $_lang['htmlsnippet_name'] ?></label>
					<div class="col-md-9 col-lg-10">
						<input name="name" type="text" maxlength="100" value="<?php echo $modx->htmlspecialchars($content['name']) ?>" class="form-control form-control-lg" onchange="documentDirty=true;" />
						<script>if(!document.getElementsByName("name")[0].value) document.getElementsByName("name")[0].focus();</script>
						<small class="form-text text-danger hide" id='savingMessage'></small>
					</div>
				</div>
				<div class="row form-row">
					<label class="col-md-3 col-lg-2"><?php echo $_lang['htmlsnippet_desc'] ?></label>
					<div class="col-md-9 col-lg-10">
						<input name="description" type="text" maxlength="255" value="<?php echo $modx->htmlspecialchars($content['description']) ?>" class="form-control" onchange="documentDirty=true;" />
					</div>
				</div>
				<div class="row form-row">
					<label class="col-md-3 col-lg-2"><?php echo $_lang['existing_category'] ?></label>
					<div class="col-md-9 col-lg-10">
						<select name="categoryid" class="form-control" onchange="documentDirty=true;">
							<option>&nbsp;</option>
							<?php
							include_once(MODX_MANAGER_PATH . 'includes/categories.inc.php');
							foreach(getCategories() as $n => $v) {
								echo "\t\t\t\t" . '<option value="' . $v['id'] . '"' . ($content['category'] == $v['id'] || (empty($content['category']) && $_POST['categoryid'] == $v['id']) ? ' selected="selected"' : '') . '>' . $modx->htmlspecialchars($v['category']) . "</option>\n";
							}
							?>
						</select>
					</div>
				</div>
				<div class="row form-row">
					<label class="col-md-3 col-lg-2"><?php echo $_lang['new_category'] ?></label>
					<div class="col-md-9 col-lg-10">
						<input name="newcategory" type="text" maxlength="45" value="<?php echo isset($content['newcategory']) ? $content['newcategory'] : '' ?>" class="form-control" onChange="documentDirty=true;" />
					</div>
				</div>
			</div>
			<?php if($modx->hasPermission('save_role')): ?>
				<div class="form-group">
					<label>
						<input name="locked" type="checkbox"<?php echo $content['locked'] == 1 || $content['locked'] == 'on' ? ' checked="checked"' : '' ?> value="on" /> <?php echo $_lang['lock_htmlsnippet'] ?></label>
					<small class="form-text text-muted"><?php echo $_lang['lock_htmlsnippet_msg']; ?></small>
				</div>
			<?php endif; ?>

			<!-- HTML text editor start -->
			<label><?php echo $_lang['chunk_code']; ?></label>
			<div class="row form-group">
				<textarea dir="ltr" class="phptextarea" id="post" name="post" rows="20" onChange="documentDirty=true;"><?php echo isset($content['post']) ? $modx->htmlspecialchars($content['post']) : $modx->htmlspecialchars($content['snippet']) ?></textarea>
			</div>
			<!-- HTML text editor end -->

			<span class="warning"><?php echo $_lang['which_editor_title'] ?></span>
			<select id="which_editor" name="which_editor" size="1" onchange="changeRTE();">
				<option value="none"<?php echo $which_editor == 'none' ? ' selected="selected"' : '' ?>><?php echo $_lang['none'] ?></option>
				<?php
				// invoke OnRichTextEditorRegister event
				$evtOut = $modx->invokeEvent('OnRichTextEditorRegister');
				if(is_array($evtOut)) {
					foreach($evtOut as $i => $editor) {
						echo "\t" . '<option value="' . $editor . '"' . ($which_editor == $editor ? ' selected="selected"' : '') . '>' . $editor . "</option>\n";
					}
				}
				?>
			</select>
		</div>

		<?php

		// invoke OnChunkFormRender event
		$evtOut = $modx->invokeEvent('OnChunkFormRender', array(
			'id' => $id,
		));
		if(is_array($evtOut)) {
			echo implode('', $evtOut);
		}
		?>
	</div>
	<input type="submit" name="save" style="display:none;" />
</form>
<?php
// invoke OnRichTextEditorInit event
if($use_editor == 1) {
	$evtOut = $modx->invokeEvent('OnRichTextEditorInit', array(
		'editor' => $which_editor,
		'elements' => array(
			'post',
		),
	));
	if(is_array($evtOut)) {
		echo implode('', $evtOut);
	}
}
?>