<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<title>MODx Cache Extender Install</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css" type="text/css" media="screen" />
	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript">
		function ShowHide(obj){
//			$("#" + obj).toggle();
/*			if($("#" + obj).is(':visible')){
				$("#" + imgobj).attr("src", showimg);
			} else {
				$("#" + imgobj).attr("src", hideimg);
			}*/
			$("#" + obj).animate({"height": "toggle"}, { duration: 200});
		}
   	</script>    
        
        </head>

<body>
<!-- start install screen-->
<div id="header">

    <div class="container_12">
        <span class="help"><a href="http://community.modx-cms.ru/blog/dev/1625.html" title="Help with installing of Cache Extender">Help!</a></span>
		<span class="version">MODx Cache Accelerator 0.4b for MODx Evolution 1.0.5, 01.04.2011 by thebat053, email denis053@gmail.com</span>
        <div id="mainheader">
        	<h1 class="pngfix" id="logo"><span>MODx Cache Accelerator</span></h1>
        </div>
    </div>

</div>
<!-- end header -->

<div id="contentarea">
    <div class="container_12">        
        <!-- start content -->
<?php
	class CacheAcceleratorInstaller {
		var $revision = '2';
		var $cacheMode = 'part';
		var $modifyFlag;
		var $permission;
		var $silent = false;
		var $permissionsBase = array();
		var $errors = false;
		var $modx;

		function __construct(){
			global $modx;
			$this->modx = $modx;
			$this->engine();
		}
		
		function engine(){
			echo("<h2>Cache Accelerator v0.4b for MODx Evolution 1.0.5<br />By thebat053, denis053@gmail.com</h2>");
			if(isset($_GET['action'])){
				switch($_GET['action']){
					case 'auto_install':
						echo("<span class='green'>Performing basic install...</span><br />");
						$this->autoInstall();
						break;
					case "uninstall":
						$this->uninstall();
						break;
				}
				$this->clearCache();
				$this->msg('completed');
				$this->msg('back', null, true);
			} else {
					echo('
<h2>Choose action:</h2><br />
<form action="" method="GET">
<div class="list1"><input type="radio" name="action" value="auto_install" ></div><div class="list2"><h3>Automatic Install</h3>
<p>Quick automatic install.<br />
Installer will add <i>CacheAccelerator</i> snippet and <i>CacheAcceleratorPlugin</i> plugin and set events for them.</p></div>

<div class="list1"><input type="radio" name="action" value="manual" disabled></div><div class="list2"><h3>Manual Install</h3>
<p>Manual install.<br />
in the manager MODx choose <i>Elements</i> -> <i>Elements management</i> -> <i>Snippets</i> -> <i>New Snippet</i>. <br />Create a new snippet called <i>CacheAccelerator</i>. The contents of file <i>CacheAccelerator.tpl</i> should be copied there. The created snippet should be saved. <br />After this you ought to go to inlay <i>Plug-ins</i> and click <i>Create plug-in</i>. The new plug-in should be called <i>CacheAcceleratorPlugin</i>. Copy the contents of file <i>CacheAcceleratorPlugin.tpl</i>.
<br />Attention! After copying the plug-in contents go to inlay <b>System events</b>, where you should set the check button for events <b>OnCacheUpdate</b>, <b>OnLoadWebDocument</b>, <b>OnSiteRefresh</b>, <b>OnWebPagePrerender</b> from the section  <b>Cache Service Events</b>! <br />
In configuration tab set configuration by copy configuration line from file <i>CacheAcceleratorPlugin.properties.txt</i>.<br />
Push the button <i>Save</i>. <br />The installation of CacheAccelerator is over.</p>
</div>
<div class="list1"><input type="radio" name="action" value="uninstall"></div><div class="list2"><h3>Uninstall</h3>
<p>Uninstall.<br />
Uninstall <i>CacheAccelerator</i> from MODx.
</p></div>

<div class="clear">&nbsp;</div>
<input type="submit" value="Proceed selected">
</form>
					');
			}
			$this->footer();
		}
		
		function autoInstall(){
			if(!$snippet = @file_get_contents('CacheAccelerator.tpl')){
				$this->msg('cant_find_file', 'CacheAccelerator.tpl');
				$this->uninstall();
				return false;
			}
			if(!$plugin = @file_get_contents('CacheAcceleratorPlugin.tpl')){
				$this->msg('cant_find_file', 'CacheAcceleratorPlugin.tpl');
				$this->uninstall();
				return false;
			}
			if(!$pluginProperties = @file_get_contents('CacheAcceleratorPlugin.properties.txt')){
				$this->msg('cant_find_file', 'CacheAcceleratorPlugin.properties.txt');
				$this->uninstall();
				return false;
			}
			if(!$this->addSnippet('CacheAccelerator', '0.4b', $snippet)){
				$this->uninstall();
				return false;
			}
			if(!$this->addPlugin('CacheAcceleratorPlugin', '0.4b', $plugin, array('OnCacheUpdate','OnLoadWebDocument','OnWebPagePrerender','OnSiteRefresh'), $pluginProperties)){
				$this->uninstall();
				return false;
			}
			if(!is_writable('../cache'))
				$this->msg('set_chmod');
			return true;
		}
		
		function uninstall(){
			$this->msg('uninstall');//CacheAccelerator
			$this->deleteSnippet('CacheAccelerator');
			$this->deletePlugin('CacheAcceleratorPlugin');
			$this->deletePlugin('CacheAcceleratorClear');
			$this->msg('back', null, true);
			$this->footer();
			die();
//			include ("");
		}

		function addSnippet($name, $description, $content, $properties = '', $moduleGuid = ''){
			$this->msg('add_snippet', $name);
			$res = $this->modx->db->select('id', $this->modx->getFullTableName('site_snippets'), "name='".$name."'");
			if($this->modx->db->getRecordCount($res)){
				$this->msg('snippet_already_exists', $name);
				return false;
			}
			$fields = array('name' => $name,
							'description' => $description,
							'snippet' => $this->modx->db->escape($content),
							'properties' => $this->modx->db->escape($properties),
							'moduleguid' => $moduleGuid,
							);
			if(!$this->modx->db->insert($fields, $this->modx->getFullTableName('site_snippets'))){
				$this->msg('snippet_add_error', $name);
				return false;
			}
			return true;
		}

		function addPlugin($name, $description, $content, $events = null, $properties = '', $moduleGuid = ''){
			$this->msg('add_plugin', $name);
			$res = $this->modx->db->select('id', $this->modx->getFullTableName('site_plugins'), "name='".$name."'");
			if($this->modx->db->getRecordCount($res)){
				$this->msg('plugin_already_exists', $name);
				return false;
			}
			$fields = array('name' => $name,
							'description' => $description,
							'plugincode' => $this->modx->db->escape($content),
							'properties' => $this->modx->db->escape($properties),
							'moduleguid' => $moduleGuid,
							);
			if(!$this->modx->db->insert($fields, $this->modx->getFullTableName('site_plugins'))){
				$this->msg('plugin_add_error', $name);
				return false;
			}
			$res = $this->modx->db->select('id', $this->modx->getFullTableName('site_plugins'), "name='".$name."'");
			if(!$this->modx->db->getRecordCount($res)){
				$this->msg('plugin_add_error', $name);
				return false;
			}
			$pluginId = $this->modx->db->getValue($res);
			if(!$events)
				return true;
			foreach($events as $event){
				$res = $this->modx->db->select('id', $this->modx->getFullTableName('system_eventnames'), "name='".$event."'");
				if(!$this->modx->db->getRecordCount($res)){
					$this->msg('cant_find_event', $event);
					return false;
				}
				$eventId = $this->modx->db->getValue($res);
				$fields = array('pluginid' => $pluginId,
								'evtid' => $eventId,
								);
				if(!$this->modx->db->insert($fields, $this->modx->getFullTableName('site_plugin_events'))){
					$this->msg('cant_add_event_to_plugin', $name.' event: '.$event);
					return false;
				}
			}
			return true;			
		}

		function deleteSnippet($name){
			$sql = "DELETE FROM ".$this->modx->getFullTableName('site_snippets')." WHERE name='".$name."';";
			$this->msg('delete_snippet', $name);
			if(!$this->modx->db->query($sql)){
				$this->msg('snippet_delete_error', $name);
				return false;
			}
			return true;
		}

		function deletePlugin($name){
			$this->msg('delete_plugin', $name);
			$res = $this->modx->db->select('id', $this->modx->getFullTableName('site_plugins'), "name='".$name."'");
			if(!$this->modx->db->getRecordCount($res)){
				$this->msg('plugin_delete_error', $name);
				return false;
			}
			$pluginId = $this->modx->db->getValue($res);
			$sql = "DELETE FROM ".$this->modx->getFullTableName('site_plugin_events'). " WHERE pluginid=".$pluginId.";";
			if(!$this->modx->db->query($sql)){
				$this->msg('plugin_delete_error', $name);
				return false;
			}
			$sql = "DELETE FROM ".$this->modx->getFullTableName('site_plugins')." WHERE id=".$pluginId.";";
			if(!$this->modx->db->query($sql)){
				$this->msg('plugin_delete_error', $name);
				return false;
			}
			return true;			
		}

		function msg($msg, $param = null, $forced = false){
//echo($msg);
			switch($msg){
				case "patching_core":
					$msg = "<b>Patching Core.</b>";
					break;
				case "uninstall_errors":
					$msg = "<span class='red'>There is errors during uninstall.</span>";
					break;
				case "clearing_cache":
					$msg = "<b>Clearing MODx Cache.</b>";
					break;
				case "cant_find_cache":
					$msg = "<span class='red'>Error: Can't find MODx Cache directory. Possible incompatible MODx version or permissions problem!</span>";
					break;
				case "cant_clear_cache":
					$msg = "<span class='red'>Error: Can't clear MODx Cache. Possible incompatible MODx version or permissions problem!</span>";
					break;
				case "completed":
					$msg = "<span class='green'>Actions successfully completed!</span>";
					break;
				case "uninstall":
					$msg = "<span class='red'>Performing uninstall...</span>";
					break;
				case "back":
					$msg = "<br /><br /><a href='?'>Back to Main</a><br />";
					break;
				case "cant_find_file":
					$msg = "<span class='red'>Error: Cant't find file:</span> <i><param></i>";
					break;
				case "snippet_already_exists":
					$msg = "Snippet <i><param></i> already exists";
					break;
				case "snippet_add_error":
					$msg = "<span class='red'>Error: Can't add snippet:</span> <i><param></i>";
					break;
				case "plugin_already_exists":
					$msg = "Plugin <i><param></i> already exists";
					break;
				case "plugin_add_error":
					$msg = "<span class='red'>Error: Can't add plugin:</span> <i><param></i>";
					break;
				case "cant_find_event":
					$msg = "<span class='red'>Error: Can't find event <b><i><param></i></b></span>";
					break;
				case "cant_add_event_to_plugin":
					$msg = "<span class='red'>Error: Can't add event to plugin:</span> <i><param></i>";
					break;
				case "snippet_delete_error":
					$msg = "<span class='red'>Error: Can't delete snippet:</span> <i><param></i>";
					break;
				case "plugin_delete_error":
					$msg = "Warning: Can't delete plugin:</span> <i><param></i>. Maybe it's not exists.";
					break;
				case "delete_snippet":
					$msg = "Deleting snippet: <param>";
					break;
				case "delete_plugin":
					$msg = "Deleting plugin: <param>";
					break;
				case "add_snippet":
					$msg = "Adding snippet: <param>";
					break;
				case "add_plugin":
					$msg = "Adding plugin: <param>";
					break;
				case "set_chmod":
					$msg = "<b>Please</b> set permission 777 to <i>assets/plugins/cacheaccelerator/cache/</i> (<b>chmod 777</b>)";
					break;
				default:
					$msg = "Unknown exception... <param>";
			}

			if(!$this->silent || $forced){
				if($param)
					echo(str_replace('<param>', "<i>".$param."</i>", $msg)."<br />");
				else
					echo($msg."<br />");
				flush ();
			}
		}
		//call_user_func
		
		function suninstallfff(){
			$this->silent = false;
			$this->msg('uninstall');
			$errors = false;
			if($this->uninstallDir('../../assets/'))
				$errors = true;
			if($this->uninstallDir('../'))
				$errors = true;
			$this->msg('deleting_file', '../processors/cache_sync.create.class.php');
			@unlink('../processors/cache_sync.create.class.php');
			$this->msg('deleting_file', '../processors/cache_sync.wrapper.class.php');
			@unlink('../processors/cache_sync.wrapper.class.php');
			$this->clearCache();
			if($errors)
				$this->msg('uninstall_errors');
			$this->msg('back');
			$this->footer();
			die();
		}

		function clearCache(){
			$this->msg('clearing_cache');
			$dir = MODX_BASE_PATH.'assets/cache/';
//			$dir = '../../../cache/';
	        if(!$nDir = opendir($dir)){
				$this->msg('cant_find_cache', $dir);
				return false;
			}
        	while (false!==($file = readdir($nDir))) {
            	if ($file != "." && $file != "..") {
                	if (!is_dir($dir.$file)) {
                    	if(!@unlink($dir.$file)){
							$this->msg('cant_clear_cache', $dir.$file);
						}
                	}
            	}
        	}
        	closedir($nDir);
			return true;
		}

		function footer(){
			echo('</div><!-- // content -->
    </div>
</div><!-- // contentarea -->

<br />
<div id="footer">
    <div id="footer-inner">

        <div class="container_12">
            &copy; 2011 the <a href="http://master-53.ru/blog/index/cacheextender-dlya-modx-evolution-1.0.5.html" target="_blank" style="color: green; text-decoration:underline">MODx Cache Accelerator</a> for MODx Content Management Framework (CMF). Cache Accelerator is a free as is software.<br />It\'s under GNU GPL license.
</div>
    </div>
</div>

<!-- end install screen-->

</body>
</html>
			');
		}
		
	}
define("IN_ETOMITE_PARSER", "true"); // provides compatibility with etomite 0.6 and maybe later versions
define("IN_PARSER_MODE", "false");
define("IN_MANAGER_MODE", "false");
define('MODX_API_MODE', true);

include_once(dirname(__FILE__)."/../../../cache/siteManager.php");
require_once('../../../../'.MGR_DIR.'/includes/protect.inc.php');
include ('../../../../'.MGR_DIR.'/includes/config.inc.php');
include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$engine = new CacheAcceleratorInstaller();

?>
