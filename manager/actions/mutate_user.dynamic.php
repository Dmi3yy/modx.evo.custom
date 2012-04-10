<?php
if (IN_MANAGER_MODE != "true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

switch((int) $_REQUEST['a']) {
  case 12:
    if (!$modx->hasPermission('edit_user')) {
      $e->setError(3);
      $e->dumpError();
    }
    break;
  case 11:
    if (!$modx->hasPermission('new_user')) {
      $e->setError(3);
      $e->dumpError();
    }
    break;
  default:
    $e->setError(3);
    $e->dumpError();  
}

$user = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

// check to see the snippet editor isn't locked
$tbl_active_users = $modx->getFullTableName('active_users');
$rs = $modx->db->select('internalKey, username',$tbl_active_users,"action='12' AND id='{$user}'");
if ($modx->db->getRecordCount($rs) > 1)
{
	while($lock = $modx->db->getRow($rs))
	{
		if ($lock['internalKey'] != $modx->getLoginUserID())
		{
			$msg = sprintf($_lang["lock_msg"], $lock['username'], "user");
			$e->setError(5, $msg);
			$e->dumpError();
		}
	}
}
// end check for lock

if ($_REQUEST['a'] == '12')
{
	// get user attribute
	$tbl_user_attributes = $modx->getFullTableName('user_attributes');
	$rs = $modx->db->select('*',$tbl_user_attributes,"internalKey={$user}");
	$limit = $modx->db->getRecordCount($rs);
	if($limit > 1)     {echo 'More than one user returned!<p>';exit;}
	elseif($limit < 1) {echo 'No user returned!<p>';exit;}
	$userdata = $modx->db->getRow($rs);
	
	// get user settings
	$tbl_user_settings = $modx->getFullTableName('user_settings');
	$rs = $modx->db->select('*',$tbl_user_settings,"user={$user}");
	$usersettings = array ();
	while ($row = $modx->db->getRow($rs))
	{
		$usersettings[$row['setting_name']] = $row['setting_value'];
	}
	
	// manually extract so that user display settings are not overwritten
	foreach ($usersettings as $k => $v)
	{
		switch($k)
		{
			case 'manager_language':
			case 'manager_theme':
				break;
			default:
				${$k} = $v;
		}
	}
	
	$tbl_manager_users = $modx->getFullTableName('manager_users');
	// get user name
	$rs = $modx->db->select('*',$tbl_manager_users,"id={$user}");
	$limit = $modx->db->getRecordCount($rs);
	if($limit > 1)     {echo "More than one user returned while getting username!<p>"; exit;}
	elseif($limit < 1) {echo "No user returned while getting username!<p>"; exit;}
	$usernamedata = $modx->db->getRow($rs);
	$_SESSION['itemname'] = $usernamedata['username'];
}
else
{
	$userdata = array ();
	$usersettings = array ();
	$usernamedata = array ();
	$_SESSION['itemname'] = "New user";
}

// restore saved form
$formRestored = false;
if ($modx->manager->hasFormValues()) {
	$modx->manager->loadFormValues();
	// restore post values
	$userdata = array_merge($userdata, $_POST);
	$userdata['dob'] = ConvertDate($userdata['dob']);
	$usernamedata['username'] = $userdata['newusername'];
	$usernamedata['oldusername'] = $_POST['oldusername'];
	$usersettings = array_merge($usersettings, $userdata);
	$usersettings['allowed_days'] = is_array($_POST['allowed_days']) ? implode(",", $_POST['allowed_days']) : "";
	extract($usersettings, EXTR_OVERWRITE);
}

// include the country list language file
$_country_lang = array();
include_once "lang/country/english_country.inc.php";
if($manager_language!="english" && file_exists($modx->config['base_path']."manager/includes/lang/country/".$manager_language."_country.inc.php")){
    include_once "lang/country/".$manager_language."_country.inc.php";
}

$displayStyle = ($_SESSION['browser'] !== 'ie') ? 'table-row' : 'block';
?>
<script type="text/javascript" src="media/calendar/datepicker.js"></script>
<script type="text/javascript">
window.addEvent('domready', function() {
	var dpOffset = <?php echo $modx->config['datepicker_offset']; ?>;
	var dpformat = "<?php echo $modx->config['datetime_format']; ?>";
	new DatePicker($('dob'), {'yearOffset': -90,'yearRange':1,'format':dpformat});
	if ($('blockeduntil')) {
		new DatePicker($('blockeduntil'), {'yearOffset': dpOffset,'format':dpformat + ' hh:mm:00'});
		new DatePicker($('blockedafter'), {'yearOffset': dpOffset,'format':dpformat + ' hh:mm:00'});
	}
});

function changestate(element) {
	documentDirty=true;
	currval = eval(element).value;
	if(currval==1) {
		eval(element).value=0;
	} else {
		eval(element).value=1;
	}
}

function changePasswordState(element) {
	currval = eval(element).value;
	if(currval==1) {
		document.getElementById("passwordBlock").style.display="block";
	} else {
		document.getElementById("passwordBlock").style.display="none";
	}
}

function changeblockstate(element, checkelement) {
	currval = eval(element).value;
	if(currval==1) {
		if(confirm("<?php echo $_lang['confirm_unblock']; ?>")==true){
			document.userform.blocked.value=0;
			document.userform.blockeduntil.value="";
			document.userform.blockedafter.value="";
			document.userform.failedlogincount.value=0;
			blocked.innerHTML="<b><?php echo $_lang['unblock_message']; ?></b>";
			blocked.className="TD";
			eval(element).value=0;
		} else {
			eval(checkelement).checked=true;
		}
	} else {
		if(confirm("<?php echo $_lang['confirm_block']; ?>")==true){
			document.userform.blocked.value=1;
			blocked.innerHTML="<b><?php echo $_lang['block_message']; ?></b>";
			blocked.className="warning";
			eval(element).value=1;
		} else {
			eval(checkelement).checked=false;
		}
	}
}

function resetFailed() {
	document.userform.failedlogincount.value=0;
	document.getElementById("failed").innerHTML="0";
}

function deleteuser() {
<?php if($_GET['id']==$modx->getLoginUserID()) { ?>
	alert("<?php echo $_lang['alert_delete_self']; ?>");
<?php } else { ?>
	if(confirm("<?php echo $_lang['confirm_delete_user']; ?>")==true) {
		document.location.href="index.php?id=" + document.userform.id.value + "&a=33";
	}
<?php } ?>
}

// change name
function changeName(){
	if(confirm("<?php echo $_lang['confirm_name_change']; ?>")==true) {
		var e1 = document.getElementById("showname");
		var e2 = document.getElementById("editname");
		e1.style.display = "none";
		e2.style.display = "<?php echo $displayStyle; ?>";
	}
};

// showHide - used by custom settings
function showHide(what, onoff){
	var all = document.getElementsByTagName( "*" );
	var l = all.length;
	var buttonRe = what;
	var id, el, stylevar;

	if(onoff==1) {
		stylevar = "<?php echo $displayStyle; ?>";
	} else {
		stylevar = "none";
	}

	for ( var i = 0; i < l; i++ ) {
		el = all[i]
		id = el.id;
		if ( id == "" ) continue;
		if (buttonRe.test(id)) {
			el.style.display = stylevar;
		}
	}
};

</script>


<form action="index.php" method="post" name="userform" enctype="multipart/form-data">
<input type="hidden" name="a" value="32" />
<?php

// invoke OnUserFormPrerender event
$evtOut = $modx->invokeEvent("OnUserFormPrerender", array (
	"id" => $user
));
if (is_array($evtOut))
	echo implode("", $evtOut);
?>
<input type="hidden" name="mode" value="<?php echo $_GET['a'] ?>">
<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
<input type="hidden" name="blockedmode" value="<?php echo ($userdata['blocked']==1 || ($userdata['blockeduntil']>time() && $userdata['blockeduntil']!=0)|| ($userdata['blockedafter']<time() && $userdata['blockedafter']!=0) || $userdata['failedlogins']>3) ? "1":"0" ?>" />

<h1><?php echo $_lang['user_title']; ?></h1>
    <div id="actions">
    	  <ul class="actionButtons">
    		  <li id="Button1">
    			<a href="#" onclick="documentDirty=false; document.userform.save.click();">
    			  <img src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']?>
    			</a>
    			  <span class="and"> + </span>				
    			<select id="stay" name="stay">
    			  <option id="stay1" value="1" <?php echo selected($_REQUEST['stay']=='1');?> ><?php echo $_lang['stay_new']?></option>
    			  <option id="stay2" value="2" <?php echo selected($_REQUEST['stay']=='2');?> ><?php echo $_lang['stay']?></option>
    			  <option id="stay3" value=""  <?php echo selected($_REQUEST['stay']=='');?>  ><?php echo $_lang['close']?></option>
    			</select>		
    		  </li>
    		  <?php
    			if ($_REQUEST['a'] == '12') { ?>
    		  <li id="Button3" class="disabled"><a href="#" onclick="deleteuser();"><img src="<?php echo $_style["icons_delete_document"]?>" /> <?php echo $_lang['delete']?></a></li>
    		  <?php } else { ?>
    		  <li id="Button3"><a href="#" onclick="deleteuser();"><img src="<?php echo $_style["icons_delete_document"]?>" /> <?php echo $_lang['delete']?></a></li>
    		  <?php } ?>	
    		  <li id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=75';"><img src="<?php echo $_style["icons_cancel"]?>" /> <?php echo $_lang['cancel']?></a></li>
    	  </ul>
    </div>
<!-- Tab Start -->
<div class="sectionBody">
<link type="text/css" rel="stylesheet" href="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>style.css<?php echo "?$theme_refresher";?>" />
<style type="text/css">
	table.settings {border-collapse:collapse;width:100%;}
	table.settings tr {border-bottom:1px dotted #ccc;}
	table.settings th {font-size:inherit;vertical-align:top;text-align:left;}
	table.settings th,table.settings td {padding:5px;}
	table.settings td input[type=text] {width:250px;}
</style>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<div class="tab-pane" id="userPane">
	<script type="text/javascript">
		tpUser = new WebFXTabPane(document.getElementById( "userPane" ), <?php echo (($modx->config['remember_last_tab'] == 2) || ($_GET['stay'] == 2 )) ? 'true' : 'false'; ?> );
	</script>
    <div class="tab-page" id="tabGeneral">
    	<h2 class="tab"><?php echo $_lang["settings_general"] ?></h2>
    	<script type="text/javascript">tpUser.addTabPage( document.getElementById( "tabGeneral" ) );</script>
		<table class="settings">
		  <tr>
			<td colspan="3">
				<span id="blocked" class="warning"><?php if($userdata['blocked']==1 || ($userdata['blockeduntil']>time() && $userdata['blockeduntil']!=0) || $userdata['failedlogins']>3) { ?><b><?php echo $_lang['user_is_blocked']; ?></b><?php } ?></span><br />
			</td>
		  </tr>
		  <?php if(!empty($userdata['id'])) { ?>
		  <tr id="showname" style="display: <?php echo ($_GET['a']=='12' && (!isset($usernamedata['oldusername'])||$usernamedata['oldusername']==$usernamedata['username'])) ? $displayStyle : 'none';?> ">
			<td colspan="3">
				<img src="<?php echo $_style['icons_user'] ?>" alt="." />&nbsp;<b><?php echo !empty($usernamedata['oldusername']) ? $usernamedata['oldusername']:$usernamedata['username']; ?></b> - <span class="comment"><a href="#" onclick="changeName();return false;"><?php echo $_lang["change_name"]; ?></a></span>
				<input type="hidden" name="oldusername" value="<?php echo htmlspecialchars(!empty($usernamedata['oldusername']) ? $usernamedata['oldusername']:$usernamedata['username']); ?>" />
			</td>
		  </tr>
		  <?php } ?>
		  <tr id="editname" style="display:<?php echo $_GET['a']=='11'||(isset($usernamedata['oldusername']) && $usernamedata['oldusername']!=$usernamedata['username']) ? $displayStyle : 'none' ; ?>">
			<td><?php echo $_lang['username']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="newusername" class="inputBox" value="<?php echo htmlspecialchars($usernamedata['username']); ?>" onchange='documentDirty=true;' maxlength="100" required /></td>
		  </tr>
		  <tr>
			<td valign="top"><?php echo $_GET['a']=='11' ? $_lang['password'].":" : $_lang['change_password_new'].":" ; ?></td>
			<td>&nbsp;</td>
			<td><label><input name="newpasswordcheck" type="checkbox" onclick="changestate(document.userform.newpassword);changePasswordState(document.userform.newpassword);"<?php echo $_REQUEST['a']=="11" ? " checked disabled": "" ; ?>><input type="hidden" name="newpassword" value="<?php echo $_REQUEST['a']=="11" ? 1 : 0 ; ?>" onchange="documentDirty=true;" /></label><br />
				<span style="display:<?php echo $_REQUEST['a']=="11" ? "block": "none" ; ?>" id="passwordBlock">
				<fieldset style="width:300px">
				<legend><b><?php echo $_lang['password_gen_method']; ?></b></legend>
				<label><input type=radio name="passwordgenmethod" value="g" <?php echo $_POST['passwordgenmethod']=="spec" ? "" : 'checked="checked"'; ?> /><?php echo $_lang['password_gen_gen']; ?></label><br />
				<label><input type=radio name="passwordgenmethod" value="spec" <?php echo $_POST['passwordgenmethod']=="spec" ? 'checked="checked"' : ""; ?>><?php echo $_lang['password_gen_specify']; ?></label><br />
				<div style="padding-left:20px">
				<label for="specifiedpassword" style="width:120px"><?php echo $_lang['change_password_new']; ?>:</label>
				<input type="password" name="specifiedpassword" onchange="documentdirty=true;" onkeypress="document.userform.passwordgenmethod[1].checked=true;" size="20" /><br />
				<label for="confirmpassword" style="width:120px"><?php echo $_lang['change_password_confirm']; ?>:</label>
				<input type="password" name="confirmpassword" onchange="documentdirty=true;" onkeypress="document.userform.passwordgenmethod[1].checked=true;" size="20" /><br />
				<small><span class="warning" style="font-weight:normal"><?php echo $_lang['password_gen_length']; ?></span></small>
				</div>
				</fieldset>
				<br />
				<fieldset style="width:300px">
				<legend><b><?php echo $_lang['password_method']; ?></b></legend>
				<label><input type=radio name="passwordnotifymethod" value="e" <?php echo $_POST['passwordnotifymethod']=="e" ? 'checked="checked"' : ""; ?> /><?php echo $_lang['password_method_email']; ?></label><br />
				<label><input type=radio name="passwordnotifymethod" value="s" <?php echo $_POST['passwordnotifymethod']=="e" ? "" : 'checked="checked"'; ?> /><?php echo $_lang['password_method_screen']; ?></label>
				</fieldset>
				</span>
			</td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_full_name']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="fullname" class="inputBox" value="<?php echo htmlspecialchars($userdata['fullname']); ?>" onchange="documentDirty=true;" /></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_email']; ?>:</td>
			<td>&nbsp;</td>
			<td>
			<input type="text" name="email" class="inputBox" value="<?php echo htmlspecialchars($userdata['email']); ?>" onchange="documentDirty=true;" required />
			<input type="hidden" name="oldemail" value="<?php echo htmlspecialchars(!empty($userdata['oldemail']) ? $userdata['oldemail']:$userdata['email']); ?>" />
			</td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_role']; ?>:</td>
			<td>&nbsp;</td>
			<td>
<?php
$tbl_user_roles = $modx->getFullTableName('user_roles');
if($_SESSION['mgrRole'] == 1)
{
	$where = '';
}
elseif($modx->hasPermission('edit_role')
    && $modx->hasPermission('save_role')
    && $modx->hasPermission('delete_role')
    && $modx->hasPermission('new_role')
    )
{
	$where = '';
}
elseif(!$modx->hasPermission('edit_role') && $_GET['id']==$modx->getLoginUserID())
{
	$where = 'edit_role=0 AND save_role=0 AND delete_role=0 AND new_role=0';
}
else
{
	$where = 'id != 1';
}
$rs = $modx->db->select('name, id',$tbl_user_roles,$where);
?>
		<select name="role" class="inputBox" onchange='documentDirty=true;' style="width:300px">
		<?php

while ($row = $modx->db->getRow($rs))
{
	if ($_REQUEST['a']=='11')
	{
		$selectedtext = selected($row['id'] == '1');
	}
	else
	{
		$selectedtext = selected($row['id'] == $userdata['role']);
	}
?>
			<option value="<?php echo $row['id']; ?>"<?php echo $selectedtext; ?>><?php echo $row['name']; ?></option>
		<?php
}
?>
		</select>
			</td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_phone']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="phone" class="inputBox" value="<?php echo htmlspecialchars($userdata['phone']); ?>" onchange="documentDirty=true;" /></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_mobile']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="mobilephone" class="inputBox" value="<?php echo htmlspecialchars($userdata['mobilephone']); ?>" onchange="documentDirty=true;" /></td>
		  </tr>		  
		  <tr>	  
			<td><?php echo $_lang['user_fax']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="fax" class="inputBox" value="<?php echo htmlspecialchars($userdata['fax']); ?>" onchange="documentDirty=true;" /></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_state']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="state" class="inputBox" value="<?php echo htmlspecialchars($userdata['state']); ?>" onchange="documentDirty=true;" /></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_zip']; ?>:</td>
			<td>&nbsp;</td>
			<td><input type="text" name="zip" class="inputBox" value="<?php echo htmlspecialchars($userdata['zip']); ?>" onchange="documentDirty=true;" /></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_country']; ?>:</td>
			<td>&nbsp;</td>
			<td>
			<select size="1" name="country" onchange="documentDirty=true;">
            <?php $chosenCountry = isset($_POST['country']) ? $_POST['country'] : $userdata['country']; ?>
			<option value="" <?php echo selected(empty($chosenCountry)); ?> >&nbsp;</option>
				<?php
				foreach ($_country_lang as $key => $country)
				{
					echo '<option value="' . $key . '"'.selected(isset($chosenCountry) && $chosenCountry == $key) .">{$country}</option>\n";
				}
				?>
            </select>
            </td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_dob']; ?>:</td>
			<td>&nbsp;</td>
			<td>
				<input type="text" id="dob" name="dob" class="DatePicker" value="<?php echo ($userdata['dob'] ? $modx->toDateFormat($userdata['dob'],'dateOnly'):""); ?>" onblur='documentDirty=true;'>
				<a onclick="document.userform.dob.value=''; return true;" onmouseover="window.status='<?php echo $_lang['remove_date']; ?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img align="absmiddle" src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="<?php echo $_lang['remove_date']; ?>"></a>
			</td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_gender']; ?>:</td>
			<td>&nbsp;</td>
			<td><select name="gender" onchange="documentDirty=true;">
				<option value=""></option>
				<option value="1" <?php echo selected($userdata['gender']=='1'); ?>><?php echo $_lang['user_male']; ?></option>
				<option value="2" <?php echo selected($userdata['gender']=='2'); ?>><?php echo $_lang['user_female']; ?></option>
				</select>
			</td>
		  </tr>
		  <tr>
			<td valign="top"><?php echo $_lang['comment']; ?>:</td>
			<td>&nbsp;</td>
			<td>
				<textarea type="text" name="comment" class="inputBox"  rows="5" onchange="documentDirty=true;"><?php echo htmlspecialchars($userdata['comment']); ?></textarea>
			</td>
		  </tr>
		<?php if($_GET['a']=='12') { ?>
		  <tr>
			<td><?php echo $_lang['user_logincount']; ?>:</td>
			<td>&nbsp;</td>
			<td><?php echo $userdata['logincount'] ?></td>
		  </tr>
		  <?php
		      if(!empty($userdata['lastlogin']))
		      {
		           $lastlogin = $modx->toDateFormat($userdata['lastlogin']+$server_offset_time);
		      }
		      else $lastlogin = '-';
		  ?>
		  <tr>
			<td><?php echo $_lang['user_prevlogin']; ?>:</td>
			<td>&nbsp;</td>
			<td><?php echo $lastlogin ?></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_failedlogincount']; ?>:</td>
			<td>&nbsp;<input type="hidden" name="failedlogincount"  onchange='documentDirty=true;' value="<?php echo $userdata['failedlogincount']; ?>"></td>
			<td><span id='failed'><?php echo $userdata['failedlogincount'] ?></span>&nbsp;&nbsp;&nbsp;[<a href="javascript:resetFailed()"><?php echo $_lang['reset_failedlogins']; ?></a>]</td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_block']; ?>:</td>
			<td>&nbsp;</td>
			<td><label><input name="blockedcheck" type="checkbox" onclick="changeblockstate(document.userform.blocked, document.userform.blockedcheck);"<?php echo ($userdata['blocked']==1||($userdata['blockeduntil']>time() && $userdata['blockeduntil']!=0)) ? " checked": "" ; ?>><input type="hidden" name="blocked" value="<?php echo ($userdata['blocked']==1||($userdata['blockeduntil']>time() && $userdata['blockeduntil']!=0))?1:0; ?>"></label></td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_blockeduntil']; ?>:</td>
			<td>&nbsp;</td>
			<td>
				<input type="text" id="blockeduntil" name="blockeduntil" class="DatePicker" value="<?php echo ($userdata['blockeduntil'] ? $modx->toDateFormat($userdata['blockeduntil']):""); ?>" onblur='documentDirty=true;' readonly="readonly">
				<a onclick="document.userform.blockeduntil.value=''; return true;" onmouseover="window.status='<?php echo $_lang['remove_date']; ?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img align="absmiddle" src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="<?php echo $_lang['remove_date']; ?>" /></a>
			</td>
		  </tr>
		  <tr>
			<td><?php echo $_lang['user_blockedafter']; ?>:</td>
			<td>&nbsp;</td>
			<td>
				<input type="text" id="blockedafter" name="blockedafter" class="DatePicker" value="<?php echo ($userdata['blockedafter'] ? $modx->toDateFormat($userdata['blockedafter']):""); ?>" onblur='documentDirty=true;' readonly="readonly">
				<a onclick="document.userform.blockedafter.value=''; return true;" onmouseover="window.status='<?php echo $_lang['remove_date']; ?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img align="absmiddle" src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="<?php echo $_lang['remove_date']; ?>" /></a>
			</td>
		  </tr>
		<?php

}
?>
		</table>
		<?php if($_GET['id']==$modx->getLoginUserID()) { ?><p><?php echo $_lang['user_edit_self_msg']; ?></p><?php } ?>
	</div>
	<!-- Settings -->
    <div class="tab-page" id="tabSettings">
    	<h2 class="tab"><?php echo $_lang["settings_users"] ?></h2>
    	<script type="text/javascript">tpUser.addTabPage( document.getElementById( "tabSettings" ) );</script>
        <table class="settings">
	  <tr>
	    <th><?php echo $_lang["language_title"] ?></th>
	    <td> <select name="manager_language" size="1" class="inputBox" onchange="documentDirty=true">
	    <option value=""><?php echo $_lang["user_use_config"]; ?></option>
	    <?php
$activelang = !empty($usersettings['manager_language']) ? $usersettings['manager_language'] : '';
$dir = dir("includes/lang");
while ($file = $dir->read()) {
	if (strpos($file, ".inc.php") > 0) {
		$endpos = strpos($file, ".");
		$languagename = trim(substr($file, 0, $endpos));
		$selectedtext = $languagename == selected($activelang);
?> 
                <option value="<?php echo $languagename; ?>" <?php echo $selectedtext; ?>><?php echo ucwords(str_replace("_", " ", $languagename)); ?></option> 
                <?php

	}
}
$dir->close();
?> 
              </select>
              <div><?php echo $_lang["language_message"]; ?></div>
         </td>
	  </tr>
          <tr>
            <th><?php echo $_lang["mgr_login_start"] ?></th>
            <td ><input onchange="documentDirty=true;" type='text' maxlength='50' style="width: 100px;" name="manager_login_startup" value="<?php echo isset($_POST['manager_login_startup']) ? $_POST['manager_login_startup'] : $usersettings['manager_login_startup']; ?>">
            <div><?php echo $_lang["mgr_login_start_message"] ?></div>
            </td>
          </tr>
          <tr>
            <th><?php echo $_lang["allow_mgr_access"] ?></th>
            <td>
            	<label><input onchange="documentDirty=true;" type="radio" name="allow_manager_access" value="1" <?php echo !isset($usersettings['allow_manager_access'])||$usersettings['allow_manager_access']==1 ? 'checked="checked"':'' ; ?> /> <?php echo $_lang['yes']; ?></label><br />
            	<label><input onchange="documentDirty=true;" type="radio" name="allow_manager_access" value="0" <?php echo isset($usersettings['allow_manager_access']) && $usersettings['allow_manager_access']==0 ? 'checked="checked"':'' ; ?> /> <?php echo $_lang['no']; ?></label>
            	<div><?php echo $_lang["allow_mgr_access_message"] ?></div>
            </td>
          </tr>
          <tr>
            <th><?php echo $_lang["login_allowed_ip"] ?></th>
            <td ><input onchange="documentDirty=true;"  type="text" maxlength='255' style="width: 300px;" name="allowed_ip" value="<?php echo $usersettings['allowed_ip']; ?>" />
            <div><?php echo $_lang["login_allowed_ip_message"] ?></div>
            </td>
          </tr>
          <tr>
            <th><?php echo $_lang["login_allowed_days"] ?></th>
            <td>
            <label><?php echo checkbox('allowed_days[]','1',$_lang['sunday'],   strpos($usersettings['allowed_days'],'1')!==false);?></label>
            <label><?php echo checkbox('allowed_days[]','2',$_lang['monday'],   strpos($usersettings['allowed_days'],'2')!==false);?></label>
            <label><?php echo checkbox('allowed_days[]','3',$_lang['tuesday'],  strpos($usersettings['allowed_days'],'3')!==false);?></label>
            <label><?php echo checkbox('allowed_days[]','4',$_lang['wednesday'],strpos($usersettings['allowed_days'],'4')!==false);?></label>
            <label><?php echo checkbox('allowed_days[]','5',$_lang['thursday'], strpos($usersettings['allowed_days'],'5')!==false);?></label>
            <label><?php echo checkbox('allowed_days[]','6',$_lang['friday'],   strpos($usersettings['allowed_days'],'6')!==false);?></label>
            <label><?php echo checkbox('allowed_days[]','7',$_lang['saturday'], strpos($usersettings['allowed_days'],'7')!==false);?></label>
            <div><?php echo $_lang["login_allowed_days_message"]; ?></div>
            </td>
          </tr>
          <tr>
          <th><?php echo $_lang["manager_theme"]?></th>
            <td> <select name="manager_theme" size="1" class="inputBox" onchange="documentDirty=true;document.userform.theme_refresher.value = Date.parse(new Date())">
		<option value=""><?php echo $_lang["user_use_config"]; ?></option>
<?php
		$dir = dir("media/style/");
		while ($file = $dir->read()) {
			if ($file != "." && $file != ".." && is_dir("media/style/$file") && substr($file,0,1) != '.') {
				$themename = $file;
				$attr = 'value="'.$themename.'" ';
					$attr .= selected(isset($usersettings['manager_theme']) && $themename == $usersettings['manager_theme']);
				echo "\t\t<option ".rtrim($attr).'>'.ucwords(str_replace("_", " ", $themename))."</option>\n";
			}
		}
		$dir->close();
?>
             </select><input type="hidden" name="theme_refresher" value="">
             <div><?php echo $_lang["manager_theme_message"];?></div></td>
          </tr>
          <tr>
            <th><?php echo $_lang["filemanager_path_title"]?></th>
            <td>
              <input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 300px;" name="filemanager_path" value="<?php echo htmlspecialchars(isset($usersettings['filemanager_path']) ? $usersettings['filemanager_path']:""); ?>">
              <div><?php echo $_lang["filemanager_path_message"];?></div>
              </td>
          </tr>
          <tr>
            <th><?php echo $_lang["uploadable_images_title"]?></th>
            <td>
              <input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 250px;" name="upload_images" value="<?php echo isset($usersettings['upload_images']) ? $usersettings['upload_images'] : "" ; ?>">
              &nbsp;&nbsp; <label><input onchange="documentDirty=true;" type="checkbox" name="default_upload_images" value="1" <?php echo isset($usersettings['upload_images']) ? '' : 'checked' ; ?>  /> <?php echo $_lang["user_use_config"]; ?></label>
              <div><?php echo $_lang["uploadable_images_message"].$_lang["user_upload_message"]?></div>
            </td>
          </tr>
          <tr>
            <th><?php echo $_lang["uploadable_media_title"]?></th>
            <td>
              <input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 250px;" name="upload_media" value="<?php echo isset($usersettings['upload_media']) ? $usersettings['upload_media'] : "" ; ?>">
				&nbsp;&nbsp; <label><input onchange="documentDirty=true;" type="checkbox" name="default_upload_media" value="1" <?php echo isset($usersettings['upload_media']) ? '' : 'checked' ; ?>  /> <?php echo $_lang["user_use_config"]; ?></label>
				<div><?php echo $_lang["uploadable_media_message"].$_lang["user_upload_message"]?></div>
            </td>
          </tr>
          <tr>
            <th><?php echo $_lang["uploadable_flash_title"]?></th>
            <td>
              <input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 250px;" name="upload_flash" value="<?php echo isset($usersettings['upload_flash']) ? $usersettings['upload_flash'] : "" ; ?>">
            &nbsp;&nbsp; <label><input onchange="documentDirty=true;" type="checkbox" name="default_upload_flash" value="1" <?php echo isset($usersettings['upload_flash']) ? '' : 'checked' ; ?>  /> <?php echo $_lang["user_use_config"]; ?></label>
            <div><?php echo $_lang["uploadable_flash_message"].$_lang["user_upload_message"]?></div>
            </td>
          </tr>
          <tr>
            <th><?php echo $_lang["uploadable_files_title"]?></th>
            <td>
              <input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 250px;" name="upload_files" value="<?php echo isset($usersettings['upload_files']) ? $usersettings['upload_files'] : "" ; ?>">
            &nbsp;&nbsp; <label><input onchange="documentDirty=true;" type="checkbox" name="default_upload_files" value="1" <?php echo isset($usersettings['upload_files']) ? '' : 'checked' ; ?>  /> <?php echo $_lang["user_use_config"]; ?></label>
            <div><?php echo $_lang["uploadable_files_message"].$_lang["user_upload_message"]?></div>
            </td>
          </tr>
          <tr class='row2'>
            <th><?php echo $_lang["upload_maxsize_title"]?></th>
            <td>
              <input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 300px;" name="upload_maxsize" value="<?php echo isset($usersettings['upload_maxsize']) ? $usersettings['upload_maxsize'] : "" ; ?>">
              <div><?php echo $_lang["upload_maxsize_message"]?></div>
            </td>
          </tr>
          <tr id='editorRow0' style="display: <?php echo $use_editor==1 ? $displayStyle : 'none' ; ?>">
            <th><?php echo $_lang["which_editor_title"]?></th>
            <td>
				<select name="which_editor" onchange="documentDirty=true;">
				<option value=""><?php echo $_lang["user_use_config"]; ?></option>
					<?php

$edt = isset ($usersettings["which_editor"]) ? $usersettings["which_editor"] : '';
// invoke OnRichTextEditorRegister event
$evtOut = $modx->invokeEvent("OnRichTextEditorRegister");
echo "<option value='none'" . selected($edt == 'none') . ">" . $_lang["none"] . "</option>\n";
if (is_array($evtOut))
	for ($i = 0; $i < count($evtOut); $i++) {
		$editor = $evtOut[$i];
		echo "<option value='$editor'" . selected($edt == $editor) . ">$editor</option>\n";
	}
?>
				</select>
				<div><?php echo $_lang["which_editor_message"]?></div>
			</td>
          </tr>
          <tr id='editorRow14' class="row3" style="display: <?php echo $use_editor==1 ? $displayStyle : 'none' ; ?>">
            <th><?php echo $_lang["editor_css_path_title"]?></th>
            <td><input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 250px;" name="editor_css_path" value="<?php echo isset($usersettings["editor_css_path"]) ? $usersettings["editor_css_path"] : "" ; ?>" />
            <div><?php echo $_lang["editor_css_path_message"]?></div>
			</td>
          </tr>
          <tr id='rbRow1' class='row3' style="display: <?php echo $use_browser==1 ? $displayStyle : 'none' ; ?>">
            <th><?php echo $_lang["rb_base_dir_title"]?></th>
            <td><input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 300px;" name="rb_base_dir" value="<?php echo isset($usersettings["rb_base_dir"]) ? $usersettings["rb_base_dir"]:""; ?>" />
            <div><?php echo $_lang["rb_base_dir_message"]?></div>
              </td>
          </tr>
          <tr id='rbRow4' class='row3' style="display: <?php echo $use_browser==1 ? $displayStyle : 'none' ; ?>">
            <th><?php echo $_lang["rb_base_url_title"]?></th>
            <td><input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 300px;" name="rb_base_url" value="<?php echo isset($usersettings["rb_base_url"]) ? $usersettings["rb_base_url"]:""; ?>" />
            <div><?php echo $_lang["rb_base_url_message"]?></div>
              </td>
          </tr>
		  <tr class='row1'>
            <td colspan="2" style="padding:0;">
		        <?php

// invoke OnInterfaceSettingsRender event
$evtOut = $modx->invokeEvent("OnInterfaceSettingsRender");
if (is_array($evtOut))
	echo implode("", $evtOut);
?>
            </td>
          </tr>
		</table>
	</div>
	<!-- Photo -->
    <div class="tab-page" id="tabPhoto">
    	<h2 class="tab"><?php echo $_lang["settings_photo"] ?></h2>
    	<script type="text/javascript">tpUser.addTabPage( document.getElementById( "tabPhoto" ) );</script>
    	<script type="text/javascript">
			function OpenServerBrowser(url, width, height ) {
				var iLeft = (screen.width  - width) / 2 ;
				var iTop  = (screen.height - height) / 2 ;

				var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes" ;
				sOptions += ",width=" + width ;
				sOptions += ",height=" + height ;
				sOptions += ",left=" + iLeft ;
				sOptions += ",top=" + iTop ;

				var oWindow = window.open( url, "FCKBrowseWindow", sOptions ) ;
			}
			function BrowseServer() {
				var w = screen.width * 0.7;
				var h = screen.height * 0.7;
				OpenServerBrowser("<?php echo $base_url; ?>manager/media/browser/mcpuk/browser.html?Type=images&Connector=<?php echo $base_url; ?>manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=<?php echo $base_url; ?>", w, h);
			}
			function SetUrl(url, width, height, alt){
				document.userform.photo.value = url;
				document.images['iphoto'].src = "<?php echo $base_url; ?>" + url;
			}
		</script>
        <table class="settings">
          <tr>
            <th><?php echo $_lang["user_photo"] ?></th>
            <td><input onchange="documentDirty=true;" type='text' maxlength='255' style="width: 150px;" name="photo" value="<?php echo htmlspecialchars($userdata['photo']); ?>" /> <input type="button" value="<?php echo $_lang['insert']; ?>" onclick="BrowseServer();" />
            <div><?php echo $_lang["user_photo_message"]; ?></div>
              <?php
              	if(!empty($userdata['photo']))
              	{
              	?>
              <img name="iphoto" src="<?php echo MODX_SITE_URL . $userdata['photo']; ?>" />
              	<?php
              	}
              	?>
            </td>
          </tr>
		</table>
	</div>
<?php
if ($use_udperms == 1)
{
	$groupsarray = array ();

	if ($_GET['a'] == '12')
	{ // only do this bit if the user is being edited
		$tbl_member_groups = $modx->getFullTableName('member_groups');
		$memberid = $_GET['id'];
		$rs = $modx->db->select('*',$tbl_member_groups,"member={$memberid}" );
		$limit = $modx->db->getRecordCount($rs);
		for ($i = 0; $i < $limit; $i++)
		{
			$currentgroup = $modx->db->getRow($rs);
			$groupsarray[$i] = $currentgroup['user_group'];
		}
	}

	// retain selected doc groups between post
	if (is_array($_POST['user_groups']))
	{
		foreach ($_POST['user_groups'] as $n => $v)
		{
			$groupsarray[] = $v;
		}
	}
?>
	<!-- Access -->
	<div class="tab-page" id="tabAccess">
		<h2 class="tab"><?php echo $_lang["access_permissions"] ?></h2>
		<script type="text/javascript">tpUser.addTabPage( document.getElementById( "tabAccess" ) );</script>
		<div class="sectionHeader"><?php echo $_lang['access_permissions']; ?></div>
		<div class="sectionBody">
		<?php
			echo "<p>" . $_lang['access_permissions_user_message'] . "</p>";
			$tbl_membergroup_names = $modx->getFullTableName('membergroup_names');
			$rs = $modx->db->select('name, id',$tbl_membergroup_names,'','name');
			$tpl = '<label><input type="checkbox" name="user_groups[]" value="[+id+]" [+checked+] />[+name+]</label><br />';
			while($row = $modx->db->getRow($rs))
			{
				$src = $tpl;
				$ph = array();
				$ph['id'] = $row['id'];
				$ph['checked'] = in_array($row['id'], $groupsarray) ? 'checked="checked"' : '';
				$ph['name'] = $row['name'];
				$src = $modx->parsePlaceholder($src,$ph);
				echo $src;
			}
		?>
		</div>
	</div>
<?php
}
?>
</div>
</div>
<input type="submit" name="save" style="display:none">
<?php

// invoke OnUserFormRender event
$evtOut = $modx->invokeEvent("OnUserFormRender", array (
	"id" => $user
));
if (is_array($evtOut))
	echo implode("", $evtOut);
?>
</form>
<?php
function selected($cond=false)
{
	if($cond) return ' selected="selected"';
}

// converts date format dd-mm-yyyy to php date
function ConvertDate($date) {
	global $modx;
	if ($date == "") { return "0"; }
	else             { return $modx->toTimeStamp($date); }
}

function checkbox($name,$value,$label,$cond)
{
	global $modx;
	$tpl = '<label><input onchange="documentDirty=true;" type="checkbox" name="[+name+]" value="[+value+]" [+checked+] />[+label+]</label>';
	$ph['name'] = $name;
	$ph['value'] = $value;
	$ph['label'] = $label;
	$ph['checked'] = checked($cond);
	return $modx->parsePlaceholder($tpl,$ph);
}

function checked($cond=false)
{
	if($cond===true) return 'checked="checked"';
}
