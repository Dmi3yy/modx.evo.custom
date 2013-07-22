<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
# Created By Raymond Irving 2004
# 
# changed by Andchir 2010
# 
#::::::::::::::::::::::::::::::::::::::::
# Params:	
#
#	&loginhomeid 	- (Optional)
#		redirects the user to first authorized page in the list.
#		If no id was specified then the login home page id or 
#		the current document id will be used
#
#	&logouthomeid 	- (Optional)
#		document id to load when user logs out	
#
#	&pwdreqid 	- (Optional)
#		document id to load after the user has submited
#		a request for a new password
#
#	&pwdactid 	- (Optional)
#		document id to load when the after the user has activated
#		their new password
#
#	&logintext		- (Optional) 
#		Text to be displayed inside login button (for built-in form)
#
#	&logouttext 	- (Optional)
#		Text to be displayed inside logout link (for built-in form)
#	
#	&tpl			- (Optional)
#		Chunk name or document id to as a template
# 
# &alerttpl - template for alert messages
#				  
#	Note: Templats design:
#			section 1: login template
#			section 2: logout template 
#			section 3: password reminder template 
#
#			See weblogin.tpl for more information
#
# Examples:
#
#	[[WebLogin? &loginhomeid=`8` &logouthomeid=`1`]] 
#
#	[[WebLogin? &loginhomeid=`8,18,7,5` &tpl=`Login`]] 

# Set Snippet Paths 
$snipPath = $modx->config['base_path'] . "assets/snippets/";

# check if inside manager
if ($m = $modx->insideManager()) {
	return ''; # don't go any further when inside manager
}

# deprecated params - only for backward compatibility
if(isset($loginid)) $loginhomeid=$loginid;
if(isset($logoutid)) $logouthomeid = $logoutid;
if(isset($template)) $tpl = $template;

if(!isset($alerttpl)) $alerttpl = '';


# Snippet customize settings
$liHomeId	= isset($loginhomeid)? explode(",",$loginhomeid):array($modx->config['login_home'],$modx->documentIdentifier);
$loHomeId	= isset($logouthomeid)? $logouthomeid:$modx->documentIdentifier;
$pwdReqId	= isset($pwdreqid)? $pwdreqid:0;
$pwdActId	= isset($pwdactid)? $pwdactid:0;
$loginText	= isset($logintext)? $logintext:'Login';
$logoutText	= isset($logouttext)? $logouttext:'Logout';
$tpl		= isset($tpl)? $tpl:"";

# System settings
$webLoginMode   = isset($_REQUEST['webloginmode'])? $_REQUEST['webloginmode']: '';
$isPostBack     = count($_POST) && (isset($_POST['cmdweblogin']) || isset($_POST['cmdweblogin_x']));
$isLogOut       = $webLoginMode=='lo' ? 1:0;
$isPWDActivate	= $webLoginMode=='actp' && !$isPostBack ? 1:0;
$txtPwdRem 	= isset($_REQUEST['txtpwdrem'])? $_REQUEST['txtpwdrem']: 0;
$isPWDReminder	= $isPostBack && $txtPwdRem=='1' ? 1:0;

$site_id = isset($site_id)? $site_id: '';
$cookieKey = substr(md5($site_id."Web-User"),0,15);

# Start processing
require $snipPath."weblogin/lang/russian-UTF-8.php";
require_once $snipPath."weblogin/weblogin.common.inc.php";
require_once MODX_MANAGER_PATH."/includes/crypt.class.inc.php";

if ($isPWDActivate || $isPWDReminder || $isLogOut || $isPostBack) {
	# include the logger class
	include_once MODX_MANAGER_PATH . "/includes/log.class.inc.php";
	include_once $snipPath."weblogin/weblogin.processor.inc.php";
}

include_once $snipPath."weblogin/weblogin.inc.php";

# Return
return $output;
?>
