<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if (!$modx->hasPermission('save_web_user')) {
	$e->setError(3);
	$e->dumpError();
}

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
$gender = !empty($_POST['gender']) ? $_POST['gender'] : 0;
$photo = $modx->db->escape($_POST['photo']);
$comment = $modx->db->escape($_POST['comment']);
$roleid = !empty($_POST['role']) ? $_POST['role'] : 0;
$failedlogincount = !empty($_POST['failedlogincount']) ? $_POST['failedlogincount'] : 0;
$blocked = !empty($_POST['blocked']) ? $_POST['blocked'] : 0;
$blockeduntil = !empty($_POST['blockeduntil']) ? ConvertDate($_POST['blockeduntil']) : 0;
$blockedafter = !empty($_POST['blockedafter']) ? ConvertDate($_POST['blockedafter']) : 0;
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

$tbl_web_users           = $modx->getFullTableName('web_users');
$tbl_web_user_attributes = $modx->getFullTableName('web_user_attributes');
$tbl_web_groups          = $modx->getFullTableName('web_groups');

switch ($_POST['mode']) {
	case '87' : // new user
		// check if this user name already exist
		if (!$rs = $modx->db->select('id',$tbl_web_users,"username='$newusername'")) {
			webAlert("An error occurred while attempting to retrieve all users with username {$newusername}.");
			exit;
		}
		$limit = $modx->db->getRecordCount($rs);
		if ($limit > 0) {
			webAlert("User name is already in use!");
			exit;
		}

		// check if the email address already exist
		if (!$rs = $modx->db->select('id',$tbl_web_user_attributes,"email='$email'"))
		{
			webAlert("An error occurred while attempting to retrieve all users with email {$email}.");
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
		if ($specifiedpassword != "" && $passwordgenmethod == 'spec')
		{
			if (strlen($specifiedpassword) < 6)
			{
				webAlert("Password is too short!");
				exit;
			}
			else
			{
				$newpassword = $specifiedpassword;
			}
		}
		elseif ($specifiedpassword == "" && $passwordgenmethod == 'spec')
		{
			webAlert("You didn't specify a password for this user!");
			exit;
		}
		elseif ($passwordgenmethod == 'g') {
			$newpassword = generate_password(8);
		} else {
			webAlert("No password generation method specified!");
			exit;
		}

		// invoke OnBeforeWUsrFormSave event
		$modx->invokeEvent("OnBeforeWUsrFormSave", array (
			"mode" => "new",
			"id" => $id
		));

		// create the user account
		$sql = "INSERT INTO $tbl_web_users (username, password)
						VALUES('" . $newusername . "', md5('" . $newpassword . "'));";
		$rs = $modx->db->query($sql);
		if (!$rs) {
			webAlert("An error occurred while attempting to save the user.");
			exit;
		}
		// now get the id
		if (!$key = mysql_insert_id()) {
			//get the key by sql
		}

		$sql = "INSERT INTO $tbl_web_user_attributes (internalKey, fullname, role, email, phone, mobilephone, fax, zip, state, country, gender, dob, photo, comment, blocked, blockeduntil, blockedafter)
						VALUES($key, '$fullname', '$roleid', '$email', '$phone', '$mobilephone', '$fax', '$zip', '$state', '$country', '$gender', '$dob', '$photo', '$comment', '$blocked', '$blockeduntil', '$blockedafter');";
		$rs = $modx->db->query($sql);
		if (!$rs) {
			webAlert("An error occurred while attempting to save the user's attributes.");
			exit;
		}

		// Save User Settings
		saveUserSettings($key);

		// invoke OnWebSaveUser event
		$modx->invokeEvent("OnWebSaveUser", array (
			"mode" => "new",
			"userid" => $key,
			"username" => $newusername,
			"userpassword" => $newpassword,
			"useremail" => $email,
			"userfullname" => $fullname
		));

		// invoke OnWUsrFormSave event
		$modx->invokeEvent("OnWUsrFormSave", array (
			"mode" => "new",
			"id" => $key
		));

		/*******************************************************************************/
		// put the user in the user_groups he/ she should be in
		// first, check that up_perms are switched on!
		if ($use_udperms == 1) {
			if (count($user_groups) > 0) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$sql = "INSERT INTO $tbl_web_groups (webgroup, webuser) values('" . intval($user_groups[$i]) . "', '" . $key . "')";
					$rs = $modx->db->query($sql);
					if (!$rs) {
						webAlert("An error occurred while attempting to add the user to a web group.");
						exit;
					}
				}
			}
		}
		// end of user_groups stuff!

		if ($passwordnotifymethod == 'e') {
			sendMailMessage($email, $newusername, $newpassword, $fullname);
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "88&id=$id" : "87";
				$header = "Location: index.php?a={$a}&stay=" . $_POST['stay'];
			} else {
				$header = "Location: index.php?a=99";
			}
			header($header);
		} else {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "88&id=$key" : "87";
				$stayUrl = "index.php?a={$a}&stay=" . $_POST['stay'];
			} else {
				$stayUrl = "index.php?a=99";
			}
			
			include_once "header.inc.php";
?>
			<h1><?php echo $_lang['web_user_title']; ?></h1>
			
			<div id="actions">
			<ul class="actionButtons">
				<li><a href="<?php echo $stayUrl ?>"><img src="<?php echo $_style["icons_save"] ?>" /> <?php echo $_lang['close']; ?></a></li>
			</ul>
			</div>
			
			<div class="sectionHeader"><?php echo $_lang['web_user_title']; ?></div>
			<div class="sectionBody">
			<div id="disp">
			<p>
			<?php echo sprintf($_lang["password_msg"], $newusername, $newpassword); ?>
			</p>
			</div>
			</div>
		<?php
			include_once "footer.inc.php";
		}
		break;
	case '88' : // edit user
		// generate a new password for this user
		if ($genpassword == 1) {
			if ($specifiedpassword != "" && $passwordgenmethod == "spec") {
				if (strlen($specifiedpassword) < 6) {
					webAlert("Password is too short!");
					exit;
				} else {
					$newpassword = $specifiedpassword;
				}
			}
			elseif ($specifiedpassword == "" && $passwordgenmethod == "spec") {
				webAlert("You didn't specify a password for this user!");
				exit;
			}
			elseif ($passwordgenmethod == 'g') {
				$newpassword = generate_password(8);
			} else {
				webAlert("No password generation method specified!");
				exit;
			}
			$updatepasswordsql = ", password=MD5('$newpassword') ";
		}
		if ($passwordnotifymethod == 'e') {
			sendMailMessage($email, $newusername, $newpassword, $fullname);
		}

		// check if the username already exist
		if (!$rs = $modx->db->select('id',$tbl_web_users,"username='$newusername'")) {
			webAlert("An error occurred while attempting to retrieve all users with username $newusername.");
			exit;
		}
		$limit = mysql_num_rows($rs);
		if ($limit > 0) {
			$row = $modx->db->getRow($rs);
			if ($row['id'] != $id) {
				webAlert("User name is already in use!");
				exit;
			}
		}

		// check if the email address already exists
		if (!$rs = $modx->db->select('internalKey',$tbl_web_user_attributes,"email='$email'")) {
			webAlert("An error occurred while attempting to retrieve all users with email $email.");
			exit;
		}
		$limit = mysql_num_rows($rs);
		if ($limit > 0) {
			$row = $modx->db->getRow($rs);
			if ($row['internalKey'] != $id) {
				webAlert("Email is already in use!");
				exit;
			}
		}

		// invoke OnBeforeWUsrFormSave event
		$modx->invokeEvent("OnBeforeWUsrFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		// update user name and password
		$sql = "UPDATE $tbl_web_users SET username='$newusername'" . $updatepasswordsql . " WHERE id=$id";
		if (!$rs = $modx->db->query($sql)) {
			webAlert("An error occurred while attempting to update the user's data.");
			exit;
		}
		
		$sql = "UPDATE $tbl_web_user_attributes SET 
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

		// Save User Settings
		saveUserSettings($id);

		// invoke OnWebSaveUser event
		$modx->invokeEvent("OnWebSaveUser", array (
			"mode" => "upd",
			"userid" => $id,
			"username" => $newusername,
			"userpassword" => $newpassword,
			"useremail" => $email,
			"userfullname" => $fullname,
			"oldusername" => (($oldusername != $newusername
		) ? $oldusername : ""), "olduseremail" => (($oldemail != $email) ? $oldemail : "")));

		// invoke OnWebChangePassword event
		if ($updatepasswordsql)
			$modx->invokeEvent("OnWebChangePassword", array (
				"userid" => $id,
				"username" => $newusername,
				"userpassword" => $newpassword
			));

		// invoke OnWUsrFormSave event
		$modx->invokeEvent("OnWUsrFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		/*******************************************************************************/
		// put the user in the user_groups he/ she should be in
		// first, check that up_perms are switched on!
		if ($use_udperms == 1) {
			// as this is an existing user, delete his/ her entries in the groups before saving the new groups
			$rs = $modx->db->delete($tbl_web_groups,"webuser={$id}");
			if (!$rs) {
				webAlert("An error occurred while attempting to delete previous user_groups entries.");
				exit;
			}
			if (count($user_groups) > 0) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$sql = "INSERT INTO $tbl_web_groups (webgroup, webuser) VALUES('" . intval($user_groups[$i]) . "', '$id')";
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

		if ($genpassword == 1 && $passwordnotifymethod == 's') {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "88&id={$id}" : "87";
				$stayUrl = "index.php?a=" . $a . "&stay=" . $_POST['stay'];
			} else {
				$stayUrl = "index.php?a=99";
			}
			
			include_once "header.inc.php";
?>
			<h1><?php echo $_lang['web_user_title']; ?></h1>
			
			<div id="actions">
			<ul class="actionButtons">
				<li><a href="<?php echo $stayUrl ?>"><img src="<?php echo $_style["icons_save"] ?>" /> <?php echo $_lang['close']; ?></a></li>
			</ul>
			</div>
			
			<div class="sectionHeader"><?php echo $_lang['web_user_title']; ?></div>
			<div class="sectionBody">
			<div id="disp">
				<p><?php echo sprintf($_lang["password_msg"], $newusername, $newpassword); ?></p>
			</div>
			</div>
		<?php

			include_once "footer.inc.php";
		} else {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "88&id=$id" : "87";
				$header = "Location: index.php?a=" . $a . "&stay=" . $_POST['stay'];
			} else {
				$header = "Location: index.php?a=99";
			}
			header($header);
		}
		break;
	default :
		webAlert("Unauthorized access");
		exit;
}

// Send an email to the user
function sendMailMessage($email, $uid, $pwd, $ufn) {
	global $modx,$websignupemail_message;
	global $emailsubject, $emailsender;
	global $site_name, $site_start, $site_url;
	$message = sprintf($websignupemail_message, $uid, $pwd); // use old method
	// replace placeholders
	$ph['uid'] = $uid;
	$ph['pwd'] = $pwd;
	$ph['ufn'] = $ufn;
	$ph['sname'] = $site_name;
	$ph['saddr'] = $emailsender;
	$ph['semail'] = $emailsender;
	$ph['surl'] = $site_url;
	$message = $modx->parsePlaceholder($message,$ph);
	include_once MODX_BASE_PATH . 'manager/includes/controls/modxmailer.inc.php';
	$mail = new MODxMailer();
	$mail->IsMail();
	$mail->IsHTML(0);
	$mail->From		= $emailsender;
	$mail->FromName	= $modx->config['site_name'];
	$mail->Subject	=  $emailsubject;
	$mail->Body		= $message;
	$mail->AddAddress($email);
	if ($mail->Send() === false) //ignore mail errors in this cas
	{
		webAlert("Error while sending mail to $email");
		exit;
	}
}

// Save User Settings
function saveUserSettings($id) {
	global $modx;
	
	$tbl_web_user_settings = $modx->getFullTableName('web_user_settings');

	$modx->db->delete($tbl_web_user_settings, "webuser='$id'");

	$settings = array (
		"login_home",
		"allowed_ip",
		"allowed_days"
	);
	
	foreach($settings as $name)
	{
		$value = $_POST[$name];
		if (is_array($value)) $value = implode(',', $value);
		if ($value != '')
		{
			$field = array();
			$field['webuser']       = $id;
			$field['setting_name']  = $name;
			$field['setting_value'] = $modx->db->escape($value);
			$modx->db->insert($field,$tbl_web_user_settings);
		}
	}
}

// converts date format dd-mm-yyyy to php date
function ConvertDate($date) {
	global $modx;
	if ($date == "") {return "0";}
	else {}          {return $modx->toTimeStamp($date);}
}

// Web alert -  sends an alert to web browser
function webAlert($msg) {
	global $id, $modx;
	global $dbase, $table_prefix;
	$mode = $_POST['mode'];
	$url = "index.php?a=$mode" . ($mode == '88' ? "&id={$id}" : '');
	$modx->manager->saveFormValues($mode);
	include_once "header.inc.php";
	$modx->webAlert($msg, $url);
	include_once "footer.inc.php";
}

// Generate password
function generate_password($length = 10) {
	$allowable_characters = "abcdefghjkmnpqrstuvxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	$ps_len = strlen($allowable_characters);
	mt_srand((double) microtime() * 1000000);
	$pass = "";
	for ($i = 0; $i < $length; $i++)
	{
		$pass .= $allowable_characters[mt_rand(0, $ps_len -1)];
	}
	return $pass;
}
