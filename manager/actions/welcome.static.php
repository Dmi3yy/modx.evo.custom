<?php if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

unset($_SESSION['itemname']); // clear this, because it's only set for logging purposes

if($modx->hasPermission('settings') && (!isset($settings_version) || $settings_version!=$modx_version)) {
    // seems to be a new install - send the user to the configuration page
    echo '<script type="text/javascript">document.location.href="index.php?a=17";</script>';
    exit;
}

$uid = $modx->getLoginUserID();

$script = <<<JS
        <script type="text/javascript">
        function hideConfigCheckWarning(key){
            var myAjax = new Ajax('index.php?a=118',
            {
                method: 'post',
                data: 'action=setsetting&key=_hide_configcheck_' + key + '&value=1'
            });
            myAjax.addEvent('onComplete', function(resp)
            {
                fieldset = $(key + '_warning_wrapper').getParent().getParent();
                var sl = new Fx.Slide(fieldset);
                sl.slideOut();
            });
            myAjax.request();
        }
        </script>

JS;
$modx->regClientScript($script);

// set placeholders
$modx->setPlaceholder('theme',$manager_theme ? $manager_theme : '');
$modx->setPlaceholder('home', $_lang["home"]);
$modx->setPlaceholder('logo_slogan',$_lang["logo_slogan"]);
$modx->setPlaceholder('site_name',$site_name);
$modx->setPlaceholder('welcome_title',$_lang['welcome_title']);

// setup message info
if($modx->hasPermission('messages')) {
		include_once MODX_MANAGER_PATH.'includes/messageCount.inc.php';
		$_SESSION['nrtotalmessages'] = $nrtotalmessages;
		$_SESSION['nrnewmessages'] = $nrnewmessages;

    $msg = '<a href="index.php?a=10"><img src="'.$_style['icons_mail_large'].'" /></a>
    <span style="color:#909090;font-size:15px;font-weight:bold">&nbsp;'.$_lang["inbox"].($_SESSION['nrnewmessages']>0 ? " (<span style='color:red'>".$_SESSION['nrnewmessages'].'</span>)':'').'</span><br />';
    if($_SESSION['nrnewmessages']>0)
    {
        $msg .= '<span class="comment">'
             . sprintf($_lang["welcome_messages"], $_SESSION['nrtotalmessages'], "<span style='color:red;'>".$_SESSION['nrnewmessages']."</span>").'</span>';
    }
    else
    {
        $msg .= '<span class="comment">' . $_lang["messages_no_messages"] . '</span>';
    }
    $modx->setPlaceholder('MessageInfo',$msg);
}

// setup icons
if($modx->hasPermission('new_user')||$modx->hasPermission('edit_user')) {
	$src = get_icon($_lang['security'], 75, $_style['icons_security_large'], $_lang['user_management_title']);
	$modx->setPlaceholder('SecurityIcon',$src);
}
if($modx->hasPermission('new_web_user')||$modx->hasPermission('edit_web_user')) { 
	$src = get_icon($_lang['web_users'], 99, $_style['icons_webusers_large'], $_lang['web_user_management_title']);
	$modx->setPlaceholder('WebUserIcon',$src);
}
if($modx->hasPermission('new_module') || $modx->hasPermission('edit_module')) {
	$src = get_icon($_lang['modules'], 106, $_style['icons_modules_large'], $_lang['manage_modules']);
	$modx->setPlaceholder('ModulesIcon',$src);
}
if($modx->hasPermission('new_template') || $modx->hasPermission('edit_template') || $modx->hasPermission('new_snippet') || $modx->hasPermission('edit_snippet') || $modx->hasPermission('new_plugin') || $modx->hasPermission('edit_plugin') || $modx->hasPermission('manage_metatags')) {
	$src = get_icon($_lang['elements'], 76, $_style['icons_resources_large'], $_lang['element_management']);
	$modx->setPlaceholder('ResourcesIcon',$src);
}
if($modx->hasPermission('bk_manager')) {
	$src = get_icon($_lang['backup'], 93, $_style['icons_backup_large'], $_lang['bk_manager']);
	$modx->setPlaceholder('BackupIcon',$src);
}
if($modx->hasPermission('help')) {
	$src = get_icon($_lang['help'], 9, $_style['icons_help_large'], $_lang['bk_manager']);
	$modx->setPlaceholder('HelpIcon',$src);
}

// setup modules
if($modx->hasPermission('exec_module')) {
	// Each module
	if ($_SESSION['mgrRole'] != 1)
	{
		// Display only those modules the user can execute
		$tbl_site_modules       = $modx->getFullTableName('site_modules');
		$tbl_site_module_access = $modx->getFullTableName('site_module_access');
		$tbl_member_groups      = $modx->getFullTableName('member_groups');
		$field = 'DISTINCT sm.id, sm.name, mg.member';
		$from  = "{$tbl_site_modules} AS sm";
		$from .= " LEFT JOIN {$tbl_site_module_access} AS sma ON sma.module = sm.id";
		$from .= " LEFT JOIN {$tbl_member_groups} AS mg ON sma.usergroup = mg.user_group";
		$where = "(mg.member IS NULL OR mg.member={$uid}) AND sm.disabled != 1";
		$rs = $modx->db->select($field,$from,$where,'sm.editedon DESC');
	}
	else
	{
		// Admins get the entire list
		$rs = $modx->db->select('id,name,icon', $modx->getFullTableName('site_modules'), 'disabled != 1', 'editedon DESC');
	}
	while ($content = $modx->db->getRow($rs))
	{
		if(empty($content['icon'])) $content['icon'] = $_style['icons_modules'];
		$action = 'index.php?a=112&amp;id='.$content['id'];
		$modulemenu[] = get_icon($content['name'], $action, $content['icon'], $content['name']);
	}
}
$modules = '';
if(0<count($modulemenu)) $modules = join("\n",$modulemenu);
$modx->setPlaceholder('Modules',$modules);

// do some config checks
if (($modx->config['warning_visibility'] == 0 && $_SESSION['mgrRole'] == 1) || $modx->config['warning_visibility'] == 1)
{
    include_once "config_check.inc.php";
    $modx->setPlaceholder('settings_config',$_lang['settings_config']);
    $modx->setPlaceholder('configcheck_title',$_lang['configcheck_title']);
    if($config_check_results != $_lang['configcheck_ok']) {    
    $modx->setPlaceholder('config_check_results',$config_check_results);
    $modx->setPlaceholder('config_display','block');
    }
    else {
        $modx->setPlaceholder('config_display','none');
    }
} else {
    $modx->setPlaceholder('config_display','none');
}
if(!empty($modx->config['rss_url_news']) || !empty($modx->config['rss_url_security']))
{
	$feedData = include_once "rss.inc.php"; 
}
if(!empty($modx->config['rss_url_news']))
{
	$modx_news_content = $feedData['modx_news_content'];
}
else $modx_news_content = '-';
if(!empty($modx->config['rss_url_security']))
{
	$modx_security_notices_content = $feedData['modx_security_notices_content'];
}
else $modx_security_notices_content = '-';

// modx news
$modx->setPlaceholder('modx_news',$_lang["modx_news_tab"]);
$modx->setPlaceholder('modx_news_title',$_lang["modx_news_title"]);
$modx->setPlaceholder('modx_news_content',$modx_news_content);

// security notices
$modx->setPlaceholder('modx_security_notices',$_lang["security_notices_tab"]);
$modx->setPlaceholder('modx_security_notices_title',$_lang["security_notices_title"]);
$modx->setPlaceholder('modx_security_notices_content',$modx_security_notices_content);

// recent document info
$html = $_lang["activity_message"].'<br /><br /><ul>';
$field = 'id, pagetitle, description, editedon, editedby';
$tbl_site_content = $modx->getFullTableName('site_content');
$where = "deleted=0 AND editedby={$uid}";
$rs = $modx->db->select($field,$tbl_site_content,$where,'editedon DESC',10);
$limit = $modx->db->getRecordCount($rs);
if($limit<1)
{
    $html .= '<li>'.$_lang['no_activity_message'].'</li>';
}
else
{
	for ($i = 0; $i < $limit; $i++)
	{
		$row = $modx->db->getRow($rs);
		if($i==0)
		{
			$syncid = $row['id'];
		}
        
		$html.='<li><b>' . $modx->toDateFormat($row['editedon']) . '</b> - [' . $row['id'] .'] <a href="index.php?a=3&amp;id='.$row['id'].'">'.$row['pagetitle'].'</a>'.($row['description']!='' ? ' - '.$row['description'] : '')
		.'</li>';
	}
}
$html.='</ul>';
$modx->setPlaceholder('recent_docs',$_lang['recent_docs']);
$modx->setPlaceholder('activity_title',$_lang['activity_title']);
$modx->setPlaceholder('RecentInfo',$html);

// user info
$modx->setPlaceholder('info',$_lang['info']);
$modx->setPlaceholder('yourinfo_title',$_lang['yourinfo_title']);
if(!empty($_SESSION['mgrLastlogin']))
{
     $Lastlogin = $modx->toDateFormat($_SESSION['mgrLastlogin']+$server_offset_time);
}
else $Lastlogin = '-';

$html = '
    <p>'.$_lang["yourinfo_message"].'</p>
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="150">'.$_lang["yourinfo_username"].'</td>
        <td width="20">&nbsp;</td>
        <td><b>'.$modx->getLoginUserName().'</b></td>
      </tr>
      <tr>
        <td>'.$_lang["yourinfo_role"].'</td>
        <td>&nbsp;</td>
        <td><b>'.$_SESSION['mgrPermissions']['name'].'</b></td>
      </tr>
      <tr>
        <td>'.$_lang["yourinfo_previous_login"].'</td>
        <td>&nbsp;</td>
        <td><b>' . $Lastlogin . '</b></td>
      </tr>
      <tr>
        <td>'.$_lang["yourinfo_total_logins"].'</td>
        <td>&nbsp;</td>
        <td><b>'.($_SESSION['mgrLogincount']+1).'</b></td>
      </tr>
    </table>
';
$modx->setPlaceholder('UserInfo',$html);

// online users
$modx->setPlaceholder('online',$_lang['online']);
$modx->setPlaceholder('onlineusers_title',$_lang['onlineusers_title']);
$timetocheck = (time()-(60*20));//+$server_offset_time;

include_once "actionlist.inc.php";
$tbl_active_users = $modx->getFullTableName('active_users');
$rs = $modx->db->select('*',$tbl_active_users,"lasthit>'{$timetocheck}'",'username ASC');
$limit = $modx->db->getRecordCount($rs);
if($limit<2)
{
	$html = "<p>".$_lang['no_active_users_found']."</p>";
}
else
{
	$html = '<p>' . $_lang["onlineusers_message"].'<b>'.strftime('%H:%M:%S', time()+$server_offset_time).'</b>)</p>';
	$html .= '
	<table border="0" cellpadding="1" cellspacing="1" width="100%" bgcolor="#ccc">
	<thead>
	<tr>
	<td><b>'.$_lang["onlineusers_user"].'</b></td>
	<td><b>'.$_lang["onlineusers_userid"].'</b></td>
	<td><b>'.$_lang["onlineusers_ipaddress"].'</b></td>
	<td><b>'.$_lang["onlineusers_lasthit"].'</b></td>
	<td><b>'.$_lang["onlineusers_action"].'</b></td>
	</tr>
	</thead>
	<tbody>
	';
	while ($row = $modx->db->getRow($rs))
	{
		$currentaction = getAction($row['action'], $row['id']);
		$webicon = ($row['internalKey']<0)? '<img src="' . $style_path . 'tree/globe.gif" alt="Web user" />':'';
		$html.= "<tr bgcolor='#FFFFFF'><td><b>".$row['username']."</b></td><td>{$webicon}&nbsp;".abs($row['internalKey'])."</td><td>".$row['ip']."</td><td>".strftime('%H:%M:%S', $row['lasthit']+$server_offset_time)."</td><td>{$currentaction}</td></tr>";
	}
        $html.= '
                </tbody>
                </table>
        ';
    }
$modx->setPlaceholder('OnlineInfo',$html);

// load template file
global $tpl;
// invoke event OnManagerWelcomePrerender
$evtOut = $modx->invokeEvent('OnManagerWelcomePrerender');
if(is_array($evtOut)) {
    $output = implode('',$evtOut);
    $modx->setPlaceholder('OnManagerWelcomePrerender', $output);
}

if(!isset($tpl) || empty($tpl))
{
	$tplFile = MODX_BASE_PATH . 'assets/templates/manager/welcome.html';
	if(!file_exists($tplFile))
	{
		$tplFile = MODX_BASE_PATH . 'manager/media/style/' . $modx->config['manager_theme'] . '/manager/welcome.html';
	}
	$tpl = file_get_contents($tplFile);
}

// invoke event OnManagerWelcomeHome
$evtOut = $modx->invokeEvent('OnManagerWelcomeHome');
if(is_array($evtOut)) {
    $output = implode('',$evtOut);
    $modx->setPlaceholder('OnManagerWelcomeHome', $output);
}

// invoke event OnManagerWelcomeRender
$evtOut = $modx->invokeEvent('OnManagerWelcomeRender');
if(is_array($evtOut)) {
    $output = implode('',$evtOut);
    $modx->setPlaceholder('OnManagerWelcomeRender', $output);
}

// merge placeholders
$tpl = $modx->parseDocumentSource($tpl);
if ($js= $modx->getRegisteredClientScripts()) {
	$tpl .= $js;
}
$tpl = preg_replace('~\[\+(.*?)\+\]~', '', $tpl); //cleanup
echo $tpl;



function get_icon($title,$action,$icon_path,$alt='')
{
	if(is_int($action)) $action = 'index.php?a=' . $action;
	$icon = '<a class="hometblink" href="'.$action.'" alt="'.$alt.'"><img src="' . $icon_path . '" /><br />' . $title . "</a>\n";
	return '<span class="wm_button" style="border:0">' . $icon . '</span>';
}
