<?php
$base_path = str_replace('\\','/',realpath('../../')) . '/';
define("IN_MANAGER_MODE", "true");
define('MODX_API_MODE',true);
include_once("{$base_path}index.php");
$modx->db->connect();

include("{$base_path}manager/includes/settings.inc.php");
include_once "{$base_path}manager/includes/version.inc.php";
include_once "{$base_path}manager/includes/log.class.inc.php";

// Initialize System Alert Message Queque
if (!isset($_SESSION['SystemAlertMsgQueque'])) $_SESSION['SystemAlertMsgQueque'] = array();
$SystemAlertMsgQueque = &$_SESSION['SystemAlertMsgQueque'];

// include_once the error handler
include_once "{$base_path}manager/includes/error.class.inc.php";
$e = new errorHandler;

// initiate the content manager class
$modx->loadExtension("ManagerAPI");
$modx->getSettings();

$username = $modx->db->escape($_REQUEST['username']);
$givenPassword = $modx->db->escape($_REQUEST['password']);
$captcha_code = $_REQUEST['captcha_code'];
$rememberme= $_REQUEST['rememberme'];
$failed_allowed = $modx->config["failed_login_attempts"];

$tbl_user_settings = $modx->getFullTableName("user_settings");
$tbl_manager_users = $modx->getFullTableName('manager_users');
$tbl_user_attributes = $modx->getFullTableName('user_attributes');
$tbl_user_roles = $modx->getFullTableName('user_roles');

// invoke OnBeforeManagerLogin event
$modx->invokeEvent("OnBeforeManagerLogin",
                        array(
                            "username"      => $username,
                            "userpassword"  => $givenPassword,
                            "rememberme"    => $rememberme
                        ));

$field = "{$tbl_manager_users}.*, {$tbl_user_attributes}.*";
$from = "{$tbl_manager_users},{$tbl_user_attributes}";
$where = "BINARY {$tbl_manager_users}.username='{$username}' and {$tbl_user_attributes}.internalKey={$tbl_manager_users}.id";

$rs = $modx->db->select($field,$from,$where);
$limit = $modx->db->getRecordCount($rs);

if($limit==0 || $limit>1) {
    jsAlert($e->errors[900]);
    return;
}

$row = $modx->db->getRow($rs);

$internalKey            = $row['internalKey'];
$dbasePassword          = $row['password'];
$failedlogins           = $row['failedlogincount'];
$blocked                = $row['blocked'];
$blockeduntildate       = $row['blockeduntil'];
$blockedafterdate       = $row['blockedafter'];
$registeredsessionid    = $row['sessionid'];
$role                   = $row['role'];
$lastlogin              = $row['lastlogin'];
$nrlogins               = $row['logincount'];
$fullname               = $row['fullname'];
$email                  = $row['email'];

// get the user settings from the database
$rs = $modx->db->select('setting_name, setting_value',$tbl_user_settings,"user='{$internalKey}' AND setting_value!=''");
while ($row = $modx->db->getRow($rs))
{
    ${$row['setting_name']} = $row['setting_value'];
}
// blocked due to number of login errors.
if($failedlogins>=$failed_allowed && $blockeduntildate>time()) {
        @session_destroy();
        session_unset();
        jsAlert($e->errors[902]);
        return;
}

// blocked due to number of login errors, but get to try again
if($failedlogins>=$failed_allowed && $blockeduntildate<time()) {
    $sql = "UPDATE {$tbl_user_attributes} SET failedlogincount='0', blockeduntil='".(time()-1)."' where internalKey={$internalKey}";
    $rs = $modx->db->query($sql);
}

// this user has been blocked by an admin, so no way he's loggin in!
if($blocked=="1") {
    @session_destroy();
    session_unset();
    jsAlert($e->errors[903]);
    return;
}

// blockuntil: this user has a block until date
if($blockeduntildate>time()) {
    @session_destroy();
    session_unset();
    jsAlert("You are blocked and cannot log in! Please try again later.");
    return;
}

// blockafter: this user has a block after date
if($blockedafterdate>0 && $blockedafterdate<time()) {
    @session_destroy();
    session_unset();
    jsAlert("You are blocked and cannot log in! Please try again later.");
    return;
}

// allowed ip
if ($allowed_ip) {
        if(($hostname = gethostbyaddr($_SERVER['REMOTE_ADDR'])) && ($hostname != $_SERVER['REMOTE_ADDR'])) {
          if(gethostbyname($hostname) != $_SERVER['REMOTE_ADDR']) {
            jsAlert("Your hostname doesn't point back to your IP!");
            return;
          }
        }
        if(!in_array($_SERVER['REMOTE_ADDR'], explode(',',str_replace(' ','',$allowed_ip)))) {
          jsAlert("You are not allowed to login from this location.");
          return;
        }
}

// allowed days
if ($allowed_days) {
    $date = getdate();
    $day = $date['wday']+1;
    if (strpos($allowed_days,"$day")===false) {
        jsAlert("You are not allowed to login at this time. Please try again later.");
        return;
    }
}

// invoke OnManagerAuthentication event
$rt = $modx->invokeEvent("OnManagerAuthentication",
                        array(
                            "userid"        => $internalKey,
                            "username"      => $username,
                            "userpassword"  => $givenPassword,
                            "savedpassword" => $dbasePassword,
                            "rememberme"    => $rememberme
                        ));

// check if plugin authenticated the user
if (!$rt||(is_array($rt) && !in_array(TRUE,$rt))) {
    // check user password - local authentication
    if($dbasePassword != md5($givenPassword)) {
            jsAlert($e->errors[901]);
            $newloginerror = 1;
    }
}

if($use_captcha==1) {
	if (!isset ($_SESSION['veriword'])) {
		jsAlert('Captcha is not configured properly.');
		return;
	}
	elseif ($_SESSION['veriword'] != $captcha_code) {
        jsAlert($e->errors[905]);
        $newloginerror = 1;
    }
}

if($newloginerror) {
	//increment the failed login counter
    $failedlogins += 1;
    $sql = "update {$tbl_user_attributes} SET failedlogincount='$failedlogins' where internalKey=$internalKey";
    $rs = $modx->db->query($sql);
    if($failedlogins>=$failed_allowed) {
		//block user for too many fail attempts
        $sql = "update {$tbl_user_attributes} SET blockeduntil='".(time()+($blocked_minutes*60))."' where internalKey=$internalKey";
        $rs = $modx->db->query($sql);
    } else {
		//sleep to help prevent brute force attacks
        $sleep = (int)$failedlogins/2;
        if($sleep>5) $sleep = 5;
        sleep($sleep);
    }
	@session_destroy();
	session_unset();
    return;
}

$_SESSION['usertype'] = 'manager'; // user is a backend user

// get permissions
$_SESSION['mgrShortname'] = $username;
$_SESSION['mgrFullname'] = $fullname;
$_SESSION['mgrEmail'] = $email;
$_SESSION['mgrValidated'] = 1;
$_SESSION['mgrInternalKey'] = $internalKey;
$_SESSION['mgrFailedlogins'] = $failedlogins;
$_SESSION['mgrLastlogin'] = $lastlogin;
$_SESSION['mgrLogincount'] = $nrlogins; // login count
$_SESSION['mgrRole'] = $role;
$rs = $modx->db->select('* ',$tbl_user_roles,"id={$role}");
$row = $modx->db->getRow($rs);
$_SESSION['mgrPermissions'] = $row;

// successful login so reset fail count and update key values
if(isset($_SESSION['mgrValidated']))
{
	$now = time();
	$currentsessionid = session_id();
	$field = "failedlogincount=0, logincount=logincount+1, lastlogin=thislogin, thislogin={$now}, sessionid='{$currentsessionid}'";
    $sql = "update {$tbl_user_attributes} SET {$field} where internalKey={$internalKey}";
    $rs = $modx->db->query($sql);
}

// get user's document groups
$dg='';
$i=0;
$tbl_member_groups = $modx->getFullTableName('member_groups');
$tbl_membergroup_access = $modx->getFullTableName('membergroup_access');
$field ='uga.documentgroup as documentgroup';
$from = "{$tbl_member_groups} ug INNER JOIN {$tbl_membergroup_access} uga ON uga.membergroup=ug.user_group";
$rs = $modx->db->select($field,$from,"ug.member={$internalKey}");
while ($row = $modx->db->getRow($rs,'num'))
{
	$dg[$i++]=$row[0];
}
$_SESSION['mgrDocgroups'] = $dg;

if($rememberme == '1')
{
    $_SESSION['modx.mgr.session.cookie.lifetime']= intval($modx->config['session.cookie.lifetime']);
	
	// Set a cookie separate from the session cookie with the username in it.
	// Are we using secure connection? If so, make sure the cookie is secure
	global $https_port;
	
	$secure = (  (isset ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || $_SERVER['SERVER_PORT'] == $https_port);
	if ( version_compare(PHP_VERSION, '5.2', '<') ) {
		setcookie('modx_remember_manager', $_SESSION['mgrShortname'], time()+60*60*24*365, MODX_BASE_URL, '; HttpOnly' , $secure );
	} else {
		setcookie('modx_remember_manager', $_SESSION['mgrShortname'], time()+60*60*24*365, MODX_BASE_URL, NULL, $secure, true);
	}
}
else
{
    $_SESSION['modx.mgr.session.cookie.lifetime']= 0;
	
	// Remove the Remember Me cookie
	setcookie ('modx_remember_manager', "", time() - 3600, MODX_BASE_URL);
}

$log = new logHandler;
$log->initAndWriteLog("Logged in", $modx->getLoginUserID(), $_SESSION['mgrShortname'], "58", "-", "MODX");

// invoke OnManagerLogin event
$modx->invokeEvent("OnManagerLogin",
                        array(
                            "userid"        => $internalKey,
                            "username"      => $username,
                            "userpassword"  => $givenPassword,
                            "rememberme"    => $rememberme
                        ));

// check if we should redirect user to a web page
$id = $modx->db->getValue($modx->db->select('setting_value',$tbl_user_settings,"user='$internalKey' AND setting_name='manager_login_startup'"));
if(isset($id) && $id>0) {
    $header = 'Location: '.$modx->makeUrl($id,'','','full');
    if($_POST['ajax']==1) echo $header;
    else header($header);
}
else {
    $header = 'Location: '.$modx->config['site_url'].'manager/';
    if($_POST['ajax']==1) echo $header;
    else header($header);
}

// show javascript alert
function jsAlert($msg){
	global $modx;
    if($_POST['ajax']==1) echo $msg."\n";
    else {
        echo "<script>window.setTimeout(\"alert('".addslashes($modx->db->escape($msg))."')\",10);history.go(-1)</script>";
    }
}
