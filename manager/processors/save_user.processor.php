<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if (!$modx->hasPermission('save_user')) {
	$e->setError(3);
	$e->dumpError();
}

$tbl_user_attributes = $modx->getFullTableName('user_attributes');
$tbl_manager_users = $modx->getFullTableName('manager_users');
$tbl_member_groups = $modx->getFullTableName('member_groups');

$id = intval($_POST['id']);
$oldusername = $_POST['oldusername'];
$newusername = !empty ($_POST['newusername']) ? trim($_POST['newusername']) : "New User";
$fullname = $modx->db->escape($_POST['fullname']);
$genpassword = $_POST['newpassword'];
$passwordgenmethod = $_POST['passwordgenmethod'];
$passwordnotifymethod = $_POST['passwordnotifymethod'];
$specifiedpassword = $_POST['specifiedpassword'];
$email = $modx->db->escape($_POST['email']);
$oldemail = $_POST['oldemail'];
$phone = $modx->db->escape($_POST['phone']);
$mobilephone = $modx->db->escape($_POST['mobilephone']);
$fax = $modx->db->escape($_POST['fax']);
$dob = !empty ($_POST['dob']) ? ConvertDate($_POST['dob']) : 0;
$country = $_POST['country'];
$state = $modx->db->escape($_POST['state']);
$zip = $modx->db->escape($_POST['zip']);
$gender = !empty ($_POST['gender']) ? $_POST['gender'] : 0;
$photo = $modx->db->escape($_POST['photo']);
$comment = $modx->db->escape($_POST['comment']);
$roleid = !empty ($_POST['role']) ? $_POST['role'] : 0;
$failedlogincount = $_POST['failedlogincount'];
$blocked = !empty ($_POST['blocked']) ? $_POST['blocked'] : 0;
$blockeduntil = !empty ($_POST['blockeduntil']) ? ConvertDate($_POST['blockeduntil']) : 0;
$blockedafter = !empty ($_POST['blockedafter']) ? ConvertDate($_POST['blockedafter']) : 0;
$user_groups = $_POST['user_groups'];

// verify password
if ($passwordgenmethod == "spec" && $_POST['specifiedpassword'] != $_POST['confirmpassword']) {
	webAlert("Password typed is mismatched");
	exit;
}

// verify email
if ($email == '' || !preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i", $email)) {
	webAlert("E-mail address doesn't seem to be valid!");
	exit;
}

// verify admin security
if ($_SESSION['mgrRole'] != 1) {
	// Check to see if user tried to spoof a "1" (admin) role
	if ($roleid == 1)
	{
		if(!$modx->hasPermission('edit_role')
		    || !$modx->hasPermission('save_role')
		    || !$modx->hasPermission('delete_role')
		    || !$modx->hasPermission('new_role')
		    )
			{
				webAlert("Illegal attempt to create/modify administrator by non-administrator!");
				exit;
			}
	}
	// Verify that the user being edited wasn't an admin and the user ID got spoofed
	if ($rs = $modx->db->select('role',$tbl_user_attributes,"internalKey={$id}")) {
		if (0 < $modx->db->getRecordCount($rs))
		{	// There should only be one if there is one
			$row = $modx->db->getRow($rs);
			if ($row['role'] == 1) {
				webAlert("You cannot alter an administrative user.");
				exit;
			}
		}
	}
}

switch ($_POST['mode']) {
	case '11' : // new user
		// check if this user name already exist
		if (!$rs = $modx->db->select('id',$tbl_manager_users,"username='{$newusername}'"))
		{
			webAlert("An error occurred while attempting to retrieve all users with username $newusername.");
			exit;
		}
		$limit = $modx->db->getRecordCount($rs);
		if ($limit > 0) {
			webAlert("User name is already in use!");
			exit;
		}

		// check if the email address already exist
		if (!$rs = $modx->db->select('id',$tbl_user_attributes,"email='{$email}'"))
		{
			webAlert("An error occurred while attempting to retrieve all users with email $email.");
			exit;
		}
		$limit = $modx->db->getRecordCount($rs);
		if ($limit > 0) {
			$row = $modx->db->getRow($rs);
			if ($row['id'] != $id) {
				webAlert("Email is already in use!");
				exit;
			}
		}

		// generate a new password for this user
		if ($specifiedpassword != '' && $passwordgenmethod == "spec") {
			if (strlen($specifiedpassword) < 6) {
				webAlert("Password is too short!");
				exit;
			} else {
				$newpassword = $specifiedpassword;
			}
		}
		elseif ($specifiedpassword == '' && $passwordgenmethod == "spec") {
			webAlert("You didn't specify a password for this user!");
			exit;
		}
		elseif ($passwordgenmethod == 'g') {
			$newpassword = generate_password(8);
		} else {
			webAlert("No password generation method specified!");
			exit;
		}

		// invoke OnBeforeUserFormSave event
		$modx->invokeEvent("OnBeforeUserFormSave", array (
			"mode" => "new",
			"id" => $id
		));

		// build the SQL
		$sql = "INSERT INTO {$tbl_manager_users} (username, password)
						VALUES('{$newusername}', md5('{$newpassword}'))";
		$rs = $modx->db->query($sql);
		if (!$rs) {
			webAlert("An error occurred while attempting to save the user.");
			exit;
		}
		// now get the id
		if (!$key = $modx->db->getInsertId()) {
			//get the key by sql
		}

		$sql = "INSERT INTO {$tbl_user_attributes} (internalKey, fullname, role, email, phone, mobilephone, fax, zip, state, country, gender, dob, photo, comment, blocked, blockeduntil, blockedafter)
						VALUES($key, '$fullname', '$roleid', '$email', '$phone', '$mobilephone', '$fax', '$zip', '$state', '$country', '$gender', '$dob', '$photo', '$comment', '$blocked', '$blockeduntil', '$blockedafter');";
		$rs = $modx->db->query($sql);
		if (!$rs) {
			webAlert("An error occurred while attempting to save the user's attributes.");
			exit;
		}

		// Save User Settings
		saveUserSettings($key);

		// invoke OnManagerSaveUser event
		$modx->invokeEvent("OnManagerSaveUser", array (
			"mode" => "new",
			"userid" => $key,
			"username" => $newusername,
			"userpassword" => $newpassword,
			"useremail" => $email,
			"userfullname" => $fullname,
			"userroleid" => $roleid
		));

		// invoke OnUserFormSave event
		$modx->invokeEvent("OnUserFormSave", array (
			"mode" => "new",
			"id" => $key
		));
		
		/*******************************************************************************/
		// put the user in the user_groups he/ she should be in
		// first, check that up_perms are switched on!
		if ($use_udperms == 1) {
			if (count($user_groups) > 0) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$sql = "INSERT INTO {$tbl_member_groups} (user_group, member) values('" . intval($user_groups[$i]) . "', $key)";
					$rs = $modx->db->query($sql);
					if (!$rs) {
						webAlert("An error occurred while attempting to add the user to a user_group.");
						exit;
					}
				}
			}
		}
		// end of user_groups stuff!

		if ($passwordnotifymethod == 'e') {
			sendMailMessage($email, $newusername, $newpassword, $fullname);
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "12&id=$id" : "11";
				$header = "Location: index.php?a=" . $a . "&stay=" . $_POST['stay'];
			} else {
				$header = "Location: index.php?a=75";
			}
			header($header);
			exit;
		} else {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "12&id=$key" : "11";
				$stayUrl = "index.php?a=" . $a . "&stay=" . $_POST['stay'];
			} else {
				$stayUrl = "index.php?a=75";
			}
			
			include_once "header.inc.php";
?>
			<h1><?php echo $_lang['user_title']; ?></h1>

			<div id="actions">
			<ul class="actionButtons">
				<li><a href="<?php echo $stayUrl ?>"><img src="<?php echo $_style["icons_save"] ?>" /> <?php echo $_lang['close']; ?></a></li>
			</ul>
			</div>

			<div class="sectionHeader"><?php echo $_lang['user_title']; ?></div>
			<div class="sectionBody">
			<div id="disp">
			<p>
			<?php
			if($_POST['passwordgenmethod'] !== 'spec')
				echo sprintf($_lang["password_msg"], $newusername, $newpassword);
			else
				echo sprintf($_lang["password_msg"], $newusername, '**************');
			?>
			</p>
			</div>
			</div>
		<?php

			include_once "footer.inc.php";
		}
		break;

	case '12' : // edit user
		// generate a new password for this user
		if ($genpassword == 1) {
			if ($specifiedpassword != '' && $passwordgenmethod == "spec") {
				if (strlen($specifiedpassword) < 6) {
					webAlert("Password is too short!");
					exit;
				} else {
					$newpassword = $specifiedpassword;
				}
			}
			elseif ($specifiedpassword == '' && $passwordgenmethod == "spec") {
				webAlert("You didn't specify a password for this user!");
				exit;
			}
			elseif ($passwordgenmethod == 'g') {
				$newpassword = generate_password(8);
			} else {
				webAlert("No password generation method specified!");
				exit;
			}
			$updatepasswordsql = ", password=MD5('{$newpassword}') ";
		}

		// check if the username already exist
		if (!$rs = $modx->db->select('id',$tbl_manager_users,"username='{$newusername}'")) {
			webAlert("An error occurred while attempting to retrieve all users with username $newusername.");
			exit;
		}
		$limit = $modx->db->getRecordCount($rs);
		if ($limit > 0) {
			$row = $modx->db->getRow($rs);
			if ($row['id'] != $id) {
				webAlert("User name is already in use!");
				exit;
			}
		}

		// check if the email address already exists
		if (!$rs = $modx->db->select('internalKey',$tbl_user_attributes,"email='{$email}'")) {
			webAlert("An error occurred while attempting to retrieve all users with email $email.");
			exit;
		}
		$limit = $modx->db->getRecordCount($rs);
		if ($limit > 0) {
			$row = $modx->db->getRow($rs);
			if ($row['internalKey'] != $id) {
				webAlert("Email is already in use!");
				exit;
			}
		}

		// invoke OnBeforeUserFormSave event
		$modx->invokeEvent("OnBeforeUserFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		// update user name and password
		$sql = "UPDATE $tbl_manager_users SET username='{$newusername}' {$updatepasswordsql} WHERE id={$id}";
		if (!$rs = $modx->db->query($sql)) {
			webAlert("An error occurred while attempting to update the user's data.");
			exit;
		}

		$sql = "UPDATE $tbl_user_attributes SET
					fullname='" . $fullname . "',
					role='$roleid',
					email='$email',
					phone='$phone',
					mobilephone='$mobilephone',
					fax='$fax',
					zip='$zip' ,
					state='$state',
					country='$country',
					gender='$gender',
					dob='$dob',
					photo='$photo',
					comment='$comment',
					failedlogincount='$failedlogincount',
					blocked=$blocked,
					blockeduntil='$blockeduntil',
					blockedafter='$blockedafter'
					WHERE internalKey=$id";
		if (!$rs = $modx->db->query($sql)) {
			webAlert("An error occurred while attempting to update the user's attributes.");
			exit;
		}

		// Save user settings
		saveUserSettings($id);

		// invoke OnManagerSaveUser event
		$modx->invokeEvent("OnManagerSaveUser", array (
			"mode" => "upd",
			"userid" => $id,
			"username" => $newusername,
			"userpassword" => $newpassword,
			"useremail" => $email,
			"userfullname" => $fullname,
			"userroleid" => $roleid,
			"oldusername" => (($oldusername != $newusername
		) ? $oldusername : ''), "olduseremail" => (($oldemail != $email) ? $oldemail : '')));

		// invoke OnManagerChangePassword event
		if ($updatepasswordsql)
			$modx->invokeEvent("OnManagerChangePassword", array (
				"userid" => $id,
				"username" => $newusername,
				"userpassword" => $newpassword
			));

		if ($passwordnotifymethod == 'e') {
			sendMailMessage($email, $newusername, $newpassword, $fullname);
		}

		// invoke OnUserFormSave event
		$modx->invokeEvent("OnUserFormSave", array (
			"mode" => "upd",
			"id" => $id
		));
		$modx->clearCache();
		/*******************************************************************************/
		// put the user in the user_groups he/ she should be in
		// first, check that up_perms are switched on!
		if ($use_udperms == 1) {
			// as this is an existing user, delete his/ her entries in the groups before saving the new groups
			$sql = "DELETE FROM {$tbl_member_groups} WHERE member=$id;";
			$rs = $modx->db->query($sql);
			if (!$rs) {
				webAlert("An error occurred while attempting to delete previous user_groups entries.");
				exit;
			}
			if (count($user_groups) > 0) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$sql = "INSERT INTO {$tbl_member_groups} (user_group, member) values(" . intval($user_groups[$i]) . ", $id)";
					$rs = $modx->db->query($sql);
					if (!$rs) {
						webAlert("An error occurred while attempting to add the user to a user_group.<br />$sql;");
						exit;
					}
				}
			}
		}
		// end of user_groups stuff!
		/*******************************************************************************/
		if ($id == $modx->getLoginUserID() && ($genpassword !==1 && $passwordnotifymethod !='s')) {
?>
			<body bgcolor='#efefef'>
			<script language="JavaScript">
			alert("<?php echo $_lang["user_changeddata"]; ?>");
			top.location.href='index.php?a=8';
			</script>
			</body>
		<?php
			exit;
		}
		unset($_SESSION['mgrUsrConfigSet']);
		$modx->getSettings();
		if ($id == $modx->getLoginUserID() && $_SESSION['mgrRole'] !== $roleid)
		{
			include_once "header.inc.php";
			$_SESSION['mgrRole'] = $roleid;
			$modx->webAlert('変更したロールの権限設定を読み込むために、再ログインしてください。','index.php?a=75');
			include_once "footer.inc.php";
			exit;
		}
		if ($genpassword == 1 && $passwordnotifymethod == 's') {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "12&id=$id" : "11";
				$stayUrl = "index.php?a=" . $a . "&stay=" . $_POST['stay'];
			} else {
				$stayUrl = "index.php?a=75";
			}
			
			include_once "header.inc.php";
?>
			<h1><?php echo $_lang['user_title']; ?></h1>

			<div id="actions">
			<ul class="actionButtons">
				<li><a href="<?php echo ($id == $modx->getLoginUserID()) ? 'index.php?a=8' : $stayUrl; ?>"><img src="<?php echo $_style["icons_save"] ?>" /> <?php echo ($id == $modx->getLoginUserID()) ? $_lang['logout'] : $_lang['close']; ?></a></li>
			</ul>
			</div>

			<div class="sectionHeader"><?php echo $_lang['user_title']; ?></div>
			<div class="sectionBody">
			<div id="disp">
			<p>
			<?php echo sprintf($_lang["password_msg"], $newusername, $newpassword).(($id == $modx->getLoginUserID()) ? ' '.$_lang['user_changeddata'] : ''); ?>
			</p>
			</div>
			</div>
		<?php
			
			include_once "footer.inc.php";
		} else {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "12&id={$id}" : "11";
				$header = "Location: index.php?a={$a}&stay={$_POST['stay']}";
			} else {
				$header = "Location: index.php?a=75";
			}
			header($header);
			exit;
		}
		break;
	default :
		webAlert("Unauthorized access");
		exit;
}

// Send an email to the user
function sendMailMessage($email, $uid, $pwd, $ufn)
{
	global $modx;
	$message = sprintf($modx->config['signupemail_message'], $uid, $pwd); // use old method
	$ph['uid']    = $uid;
	$ph['pwd']    = $pwd;
	$ph['ufn']    = $ufn;
	$ph['sname']  = $modx->config['site_name'];
	$ph['saddr']  = $modx->config['emailsender'];
	$ph['semail'] = $modx->config['emailsender'];
	$ph['surl']   = $modx->config['site_url'] . 'manager/';
	$message = $modx->parsePlaceholder($message,$ph);

	include_once MODX_BASE_PATH."manager/includes/controls/modxmailer.inc.php";
	$mail = new MODxMailer();
	$mail->IsMail();
	$mail->IsHTML(0);
	$mail->From		= $modx->config['emailsender'];
	$mail->FromName	= $modx->config['site_name'];
	$mail->Subject	= $modx->config['emailsubject'];
	$mail->Body		= $message;
	$mail->AddAddress($email);
	$rs = $mail->Send();
	if ($rs === false) //ignore mail errors in this cas
	{
		webAlert("{$email} - {$_lang['error_sending_email']}");
		exit;
	}
}

// Save User Settings
function saveUserSettings($id)
{
	global $modx;

	// array of post values to ignore in this function
	$ignore = array(
		'id',
		'oldusername',
		'oldemail',
		'newusername',
		'fullname',
		'newpassword',
		'newpasswordcheck',
		'passwordgenmethod',
		'passwordnotifymethod',
		'specifiedpassword',
		'confirmpassword',
		'email',
		'phone',
		'mobilephone',
		'fax',
		'dob',
		'country',
		'state',
		'zip',
		'gender',
		'photo',
		'comment',
		'role',
		'failedlogincount',
		'blocked',
		'blockeduntil',
		'blockedafter',
		'user_groups',
		'mode',
		'blockedmode',
		'stay',
		'save',
		'theme_refresher'
	);

	// determine which settings can be saved blank (based on 'default_{settingname}' POST checkbox values)
	$defaults = array(
		'upload_images',
		'upload_media',
		'upload_flash',
		'upload_files'
	);

	// get user setting field names
	$settings= array ();
	foreach ($_POST as $n => $v)
	{
		if(is_array($v)) $v = implode(',', $v);
		if(in_array($n, $ignore) || (!in_array($n, $defaults) && trim($v) == '')) continue; // ignore blacklist and empties

		//if ($config[$n] == $v) continue; // ignore commonalities in base config

		$settings[$n] = $v; // this value should be saved
	}

	foreach ($defaults as $k)
	{
		if (isset($settings["default_{$k}"]) && $settings["default_{$k}"] == '1')
		{
			unset($settings[$k]);
		}
		unset($settings["default_{$k}"]);
	}

	$tbl_user_settings = $modx->getFullTableName('user_settings');

	$modx->db->delete($tbl_user_settings, "user={$id}");

	$savethese = array();
	foreach ($settings as $k => $v)
	{
		$v = $modx->db->escape($v);
		$savethese[] = "({$id}, '{$k}', '{$v}')";
	}

	$values = implode(', ', $savethese);
	$sql = "INSERT INTO {$tbl_user_settings} (user, setting_name, setting_value) VALUES {$values}";
	$rs = $modx->db->query($sql);
	if (!$rs) die('Failed to update user settings!');
}

// converts date format dd-mm-yyyy to php date
function ConvertDate($date) {
	global $modx;
	if ($date == '') {return '0';}
	else {}          {return $modx->toTimeStamp($date);}
}

// Web alert -  sends an alert to web browser
function webAlert($msg) {
	global $id, $modx;
	$mode = $_POST['mode'];
	$url = "index.php?a={$mode}" . ($mode == '12' ? "&id={$id}" : '');
	$modx->manager->saveFormValues($mode);
	include_once "header.inc.php";
	$modx->webAlert($msg, $url);
	include_once "footer.inc.php";
}

// Generate password
function generate_password($length = 10) {
	$allowable_characters = 'abcdefghjkmnpqrstuvxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$ps_len = strlen($allowable_characters);
	mt_srand((double) microtime() * 1000000);
	$pass = '';
	for ($i = 0; $i < $length; $i++)
	{
		$pass .= $allowable_characters[mt_rand(0, $ps_len -1)];
	}
	return $pass;
}
