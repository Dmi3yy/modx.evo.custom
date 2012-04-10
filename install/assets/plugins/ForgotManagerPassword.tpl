//<?php
/**
 * Forgot Manager Login
 * 
 * 管理画面のログインパスワードを忘れた時に、一時的に無条件ログインできるURLを発行
 *
 * @category 	plugin
 * @version 	1.1.5
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@events OnWebPageInit,OnBeforeManagerLogin,OnManagerAuthentication,OnManagerLoginFormRender 
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base
 */

if(!class_exists('ForgotManagerPassword'))
{
	class ForgotManagerPassword
	{
		function ForgotManagerPassword()
		{
			$this->errors = array();
			$this->checkLang();
		}
	
		function getLink()
		{
			global $_lang;
			
			$link = <<<EOD
<a id="ForgotManagerPassword-show_form" href="index.php?action=show_form">{$_lang['forgot_your_password']}</a>
EOD;
			return $link;
		}
			
		function getForm()
		{
			global $_lang;
			
			$form = <<< EOD
<label id="FMP-email_label" for="FMP_email">{$_lang['account_email']}:</label>
<input id="FMP-email" type="text" />
<button id="FMP-email_button" type="button" onclick="window.location = 'index.php?action=send_email&email='+document.getElementById('FMP-email').value;">{$_lang['send']}</button>
EOD;
			return $form;
		}
		
		/* Get user info including a hash unique to this user, password, and day */
		function getUser($user_id=false, $username='', $email='', $hash='')
		{
			global $modx, $_lang;
			
			if($user_id !== false) $user_id = $modx->db->escape($user_id);
			$username = $modx->db->escape($username);
			$email    = $modx->db->escape($email);
			$emaail   = $modx->db->escape($hash);
			
			$tbl_manager_users   = $modx->getFullTableName('manager_users');
			$tbl_user_attributes = $modx->getFullTableName('user_attributes');
			$site_id = $modx->config['site_id'];
			$today = date('Yz'); // Year and day of the year
			$wheres = array();
			$where = '';
			$user = null;
			
			$user_id  = ($user_id == false) ? false : $modx->db->escape($user_id);
			if(!empty($username))  { $wheres[] = "usr.username = '{$username}'"; }
			if(!empty($username))  { $wheres[] = "usr.username = '{$username}'"; }
			if(!empty($email))     { $wheres[] = "attr.email = '{$email}'"; }
			if(!empty($hash))      { $wheres[] = "MD5(CONCAT(usr.username,usr.password,'{$site_id}','{$today}')) = '{$hash}'"; } 
			
			if($wheres)
			{
				$where = implode(' AND ',$wheres);
				$field = "usr.id, usr.username, attr.email, MD5(CONCAT(usr.username,usr.password,'{$site_id}','{$today}')) AS hash";
				$from = "{$tbl_manager_users} usr INNER JOIN {$tbl_user_attributes} attr ON usr.id = attr.internalKey";
				if($result = $modx->db->select($field,$from,$where,'',1))
				{
					if($modx->db->getRecordCount($result)==1)
					{
						$user = $modx->db->getRow($result);
					}
				}
			}
			
			if($user == null) { $this->errors[] = $_lang['could_not_find_user']; }
			
			return $user;
		}
		
		/* Send an email with a link to login */
		function sendEmail($to)
		{
			global $modx, $_lang;
			
			$user = $this->getUser(0, '', $to);
			if(!$user['username']) return;
			
			$body = <<< EOT
{$_lang['forgot_password_email_intro']}

{$modx->config['site_url']}index.php?name={$user['username']}&hash={$user['hash']}
{$_lang['forgot_password_email_link']}

{$_lang['forgot_password_email_instructions']}
{$_lang['forgot_password_email_fine_print']}
EOT;
			include_once MODX_MANAGER_PATH . 'includes/controls/modxmailer.inc.php';
			$mail = new MODxMailer();
			$mail->Subject = $_lang['password_change_request'];
			$mail->Body    = $body;
			$mail->IsHTML(false);
			$mail->AddAddress($to);
			$result = $mail->send();
			
			if(!$result) $this->errors[] = $_lang['error_sending_email'];
			return $result;
		}
		
		function unblockUser($user_id)
		{
			global $modx, $_lang;
			
			$tbl_user_attributes = $modx->getFullTableName('user_attributes');
			$modx->db->update('blocked=0,blockeduntil=0,failedlogincount=0', $tbl_user_attributes, "internalKey='{$user_id}'");
			
			if(!$modx->db->getAffectedRows()) { $this->errors[] = $_lang['user_doesnt_exist']; return; }
			
			return true;
		}
		
		function checkLang()
		{
			global $_lang;
			
			$eng = array();
			$eng['forgot_your_password'] = 'Forgot your password?';
			$eng['account_email'] = 'Account email';
			$eng['send'] = 'Send';
			$eng['password_change_request'] = 'Password change request';
			$eng['forgot_password_email_intro'] = 'A request has been made to change the password on your account.';
			$eng['forgot_password_email_link'] = 'Click here to complete the process.';
			$eng['forgot_password_email_instructions'] = 'From there you will be able to change your password from the My Account menu.';
			$eng['forgot_password_email_fine_print'] = '* The URL above will expire once you change your password or after today.';
			$eng['error_sending_email'] = 'Error sending email';
			$eng['could_not_find_user'] = 'Could not find user';
			$eng['user_doesnt_exist'] = 'User does not exist';
			$eng['email_sent'] = 'Email sent';
			
			foreach($eng as $key=>$value)
			{
				if(empty($_lang[$key])) { $_lang[$key] = $value; }
			}  
		}
		
		function getErrorOutput()
		{
			$output = '';
			
			if($this->errors)
			{
				$output = '<span class="error">'.implode('</span><span class="errors">', $this->errors).'</span>';
			}
			return $output;
		}
	}
}

global $_lang;

$output = '';
$event_name = $modx->event->name;
$action   = (empty($_GET['action'])   ? ''    : $_GET['action']);
$username = (empty($_GET['username']) ? false : $_GET['username']);
$to       = (empty($_GET['email'])    ? ''    : $_GET['email']);
$hash     = (empty($_GET['hash'])     ? false : $_GET['hash']);
$forgot   = new ForgotManagerPassword();

if($event_name == 'OnWebPageInit' && isset($_GET['hash']) && isset($_GET['name']))
{
	$url = "{$modx->config['site_url']}manager/processors/login.processor.php?username={$_GET['name']}&hash={$_GET['hash']}";
	header("Location:{$url}");
	exit;
}

if($event_name == 'OnManagerLoginFormRender')
{
	switch($action)
	{
		case 'show_form':
			$output = $forgot->getForm();
			break;
		case 'send_email':
			if($forgot->sendEmail($to))
			{
				$output = $_lang['email_sent'];
			}
			break;
		default:
			$output = $forgot->getLink();
			break;
	}
	
	if($forgot->errors) { $output = $forgot->getErrorOutput() . $forgot->getLink(); }
}

if($event_name == 'OnBeforeManagerLogin')
{
	$user = $forgot->getUser(false, '', '', $hash);
	if($user && is_array($user) && !$forgot->errors)
	{
		$forgot->unblockUser($user['id']);
	}
}

if($event_name == 'OnManagerAuthentication' && $hash && $username)
{
	if($hash) $_SESSION['mgrForgetPassword'] = '1';
	$user = $forgot->getUser(false, '', '', $hash);
	if($user !== null && count($forgot->errors) == 0)
	{
		if(!$hash && $_SESSION['mgrForgetPassword']) unset($_SESSION['mgrForgetPassword']);
		$output =  true;
	}
	else $output = false;
}

$modx->event->output($output);
