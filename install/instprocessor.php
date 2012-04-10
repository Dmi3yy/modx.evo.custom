<?php
global $moduleName;
global $moduleVersion;
global $moduleSQLBaseFile;
global $moduleSQLDataFile;

global $moduleChunks;
global $moduleTemplates;
global $moduleSnippets;
global $modulePlugins;
global $moduleModules;
global $moduleTVs;

global $errors;

$create = false;

// set timout limit
@ set_time_limit(120); // used @ to prevent warning when using safe mode?

$base_path = str_replace("\\", '/', realpath('../')) . '/';
if(@file_exists("{$base_path}autoload.php")) include_once("{$base_path}autoload.php");

require_once('functions.php');

echo "<p>{$_lang['setup_database']}</p>\n";

$installMode= intval($_POST['installmode']);
$installData = $_POST['installdata'] == "1" ? 1 : 0;

// get db info from post
$database_server = $_POST['databasehost'];
$database_user = $_SESSION['databaseloginname'];
$database_password = $_SESSION['databaseloginpassword'];
$database_collation = ($_POST['database_collation']!=='') ? $_POST['database_collation'] : 'utf8_general_ci';
$database_charset = substr($database_collation, 0, strpos($database_collation, '_'));
$database_connection_charset = $_POST['database_connection_charset'];
$database_connection_method = $_POST['database_connection_method'];
if(strpos($database_connection_method, '[+') !== false) $database_connection_method = 'SET CHARACTER SET';
$dbase = $_POST['database_name'];
$table_prefix = $_POST['tableprefix'];
$adminname = $_POST['cmsadmin'];
$adminemail = $_POST['cmsadminemail'];
$adminpass = $_POST['cmspassword'];
$managerlanguage = $_POST['managerlanguage'];
//}

// get base path and url
define('MODX_API_MODE', true);
require_once("{$base_path}manager/includes/initialize.inc.php");
startCMSSession();
$database_type = 'mysql';
include_once("{$base_path}manager/includes/document.parser.class.inc.php");
$modx = new DocumentParser;
$modx->db->connect();

$tbl_site_content = getFullTableName('site_content');
$tbl_site_plugins = getFullTableName('site_plugins');
$tbl_system_settings = getFullTableName('system_settings');
$tbl_site_templates = getFullTableName('site_templates');
$tbl_site_tmplvars = getFullTableName('site_tmplvars');
$tbl_site_tmplvar_templates = getFullTableName('site_tmplvar_templates');
$tbl_site_htmlsnippets = getFullTableName('site_htmlsnippets');
$tbl_site_modules = getFullTableName('site_modules');
$tbl_site_plugin_events = getFullTableName('site_plugin_events');
$tbl_system_eventnames = getFullTableName('system_eventnames');
$tbl_site_snippets = getFullTableName('site_snippets');
$tbl_active_users = getFullTableName('active_users');

// check status of Inherit Parent Template plugin
if ($installMode != 0)
{
	$rs = mysql_query("SELECT properties, disabled FROM {$tbl_site_plugins} WHERE name='Inherit Parent Template'");
	$row = mysql_fetch_assoc($rs);
	if(!$row)
	{
		// not installed
		$auto_template_logic = 'system';
	}
	else
	{
		if($row['disabled'] == 1)
		{
			// installed but disabled
			$auto_template_logic = 'system';
		}
		else
		{
			// installed, enabled .. see how it's configured
			$properties = parseProperties($row['properties']);
			if(isset($properties['inheritTemplate']))
			{
				if($properties['inheritTemplate'] == 'From First Sibling')
				{
					$auto_template_logic = 'sibling';
				}
			}
		}
	}
}
if(!isset($auto_template_logic)) $auto_template_logic = 'system';

// open db connection
$setupPath = realpath(dirname(__FILE__));
include "{$setupPath}/setup.info.php";
include "{$setupPath}/sqlParser.class.php";
$sqlParser = new SqlParser();
$sqlParser->prefix = $table_prefix;
$sqlParser->adminname = $adminname;
$sqlParser->adminpass = $adminpass;
$sqlParser->adminemail = $adminemail;
$sqlParser->connection_charset = $database_connection_charset;
$sqlParser->connection_collation = $database_collation;
$sqlParser->connection_method = $database_connection_method;
$sqlParser->managerlanguage = $managerlanguage;
$sqlParser->autoTemplateLogic = $auto_template_logic;
$sqlParser->mode = ($installMode < 1) ? 'new' : 'upd';

$sqlParser->ignoreDuplicateErrors = true;

// install/update database
echo "<p>" . $_lang['setup_database_creating_tables'];
if ($moduleSQLBaseFile) {
	$sqlParser->process($moduleSQLBaseFile);
	// display database results
	if ($sqlParser->installFailed == true) {
		$errors += 1;
		echo "<span class=\"notok\"><b>" . $_lang['database_alerts'] . "</span></p>";
		echo "<p>" . $_lang['setup_couldnt_install'] . "</p>";
		echo "<p>" . $_lang['installation_error_occured'] . "<br /><br />";
		for ($i = 0; $i < count($sqlParser->mysqlErrors); $i++) {
			echo "<em>" . $sqlParser->mysqlErrors[$i]["error"] . "</em>" . $_lang['during_execution_of_sql'] . "<span class='mono'>" . strip_tags($sqlParser->mysqlErrors[$i]["sql"]) . "</span>.<hr />";
		}
		echo "</p>";
		echo "<p>" . $_lang['some_tables_not_updated'] . "</p>";
		return;
	} else {
		echo "<span class=\"ok\">".$_lang['ok']."</span></p>";
	}
}

// write the config.inc.php file if new installation
echo "<p>" . $_lang['writing_config_file'];
$src = file_get_contents('config.inc.tpl');
$ph['database_type']               = 'mysql';
$ph['database_server']             = $database_server;
$ph['database_user']               = modx_escape($database_user);
$ph['database_password']           = modx_escape($database_password);
$ph['database_connection_charset'] = $database_connection_charset;
$ph['database_connection_method']  = $database_connection_method;
$ph['dbase']                       = $dbase;
$ph['table_prefix']                = $table_prefix;
$ph['lastInstallTime']             = time();
$ph['site_sessionname']            = (!isset ($site_sessionname)) ? 'SN' . uniqid('') : $site_sessionname;
$ph['https_port']                  = '443';

$src = parse($src, $ph);
$config_path = "{$base_path}manager/includes/config.inc.php";
$config_saved = (@ file_put_contents($config_path, $src));

// try to chmod the config file go-rwx (for suexeced php)
@chmod($config_path, 0404);

if ($config_saved === false)
{
	echo '<span class="notok">' . $_lang['failed'] . "</span></p>";
	$errors += 1;
?>
	<p><?php echo $_lang['cant_write_config_file']?><span class="mono">manager/includes/config.inc.php</span></p>
	<textarea style="width:400px; height:160px;">
	<?php echo $configString; ?>
	</textarea>
	<p><?php echo $_lang['cant_write_config_file_note']?></p>
<?php
}
else
{
	echo '<span class="ok">' . $_lang['ok'] . "</span></p>";
}

// generate new site_id and set manager theme to MODxCarbon
if ($installMode == 0)
{
	$siteid = uniqid('');
	mysql_query("REPLACE INTO {$tbl_system_settings} (setting_name,setting_value) VALUES('site_id','$siteid'),('manager_theme','MODxCarbon')");
}
else
{
	// update site_id if missing
	$ds = mysql_query("SELECT setting_name,setting_value FROM {$tbl_system_settings} WHERE setting_name='site_id'");
	if ($ds)
	{
		$r = mysql_fetch_assoc($ds);
		$siteid = $r['setting_value'];
		if ($siteid == '' || $siteid = 'MzGeQ2faT4Dw06+U49x3')
		{
			$siteid = uniqid('');
			mysql_query("REPLACE INTO {$tbl_system_settings} (setting_name,setting_value) VALUES('site_id','$siteid')");
		}
	}
}

// Install Templates
if (isset ($_POST['template']) || $installData)
{
	echo "<h3>" . $_lang['templates'] . ":</h3> ";
	$selTemplates = $_POST['template'];
	foreach ($moduleTemplates as $k=>$moduleTemplate)
	{
		$installSample = in_array('sample', $moduleTemplate[6]) && $installData == 1;
		if(in_array($k, $selTemplates) || $installSample)
		{
			$name = modx_escape($moduleTemplate[0]);
			$desc = modx_escape($moduleTemplate[1]);
			$category = modx_escape($moduleTemplate[4]);
			$locked = modx_escape($moduleTemplate[5]);
			$filecontent = $moduleTemplate[3];
			if (!file_exists($filecontent))
			{
				echo "<p>&nbsp;&nbsp;$name: <span class=\"notok\">" . $_lang['unable_install_template'] . " '$filecontent' " . $_lang['not_found'] . ".</span></p>";
			}
			else
			{
				// Create the category if it does not already exist
				$category_id = getCreateDbCategory($category, $sqlParser);
				
				// Strip the first comment up top
				$template = preg_replace("/^.*?\/\*\*.*?\*\/\s+/s", '', file_get_contents($filecontent), 1);
				$template = modx_escape($template);
				
				// See if the template already exists
				$rs = mysql_query("SELECT * FROM {$tbl_site_templates} WHERE templatename='$name'");
				
				if (mysql_num_rows($rs))
				{
					if (!@ mysql_query("UPDATE {$tbl_site_templates} SET content='$template', description='$desc', category=$category_id, locked='$locked'  WHERE templatename='$name';"))
					{
						$errors += 1;
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['upgraded'] . "</span></p>";
				}
				else
				{
					if (!@ mysql_query("INSERT INTO {$tbl_site_templates} (templatename,description,content,category,locked) VALUES('$name','$desc','$template',$category_id,'$locked');"))
					{
						$errors += 1;
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['installed'] . "</span></p>";
				}
			}
		}
	}
}

// Install Template Variables
if (isset ($_POST['tv']) || $installData)
{
	echo "<h3>" . $_lang['tvs'] . ":</h3> ";
	$selTVs = $_POST['tv'];
	foreach ($moduleTVs as $k=>$moduleTV)
	{
		$installSample = in_array('sample', $moduleTV[12]) && $installData == 1;
		if(in_array($k, $selTVs) || $installSample)
		{
			$name = modx_escape($moduleTV[0]);
			$caption = modx_escape($moduleTV[1]);
			$desc = modx_escape($moduleTV[2]);
			$input_type = modx_escape($moduleTV[3]);
			$input_options = modx_escape($moduleTV[4]);
			$input_default = modx_escape($moduleTV[5]);
			$output_widget = modx_escape($moduleTV[6]);
			$output_widget_params = modx_escape($moduleTV[7]);
			$filecontent = $moduleTV[8];
			$assignments = $moduleTV[9];
			$category = modx_escape($moduleTV[10]);
			$locked = modx_escape($moduleTV[11]);
			
			// Create the category if it does not already exist
			$category = getCreateDbCategory($category, $sqlParser);
			
			$rs = mysql_query("SELECT * FROM {$tbl_site_tmplvars} WHERE name='$name'");
			if (mysql_num_rows($rs))
			{
				$insert = true;
				while($row = mysql_fetch_assoc($rs))
				{
					if (!@ mysql_query("UPDATE {$tbl_site_tmplvars} SET type='$input_type', caption='$caption', description='$desc', category=$category, locked=$locked, elements='$input_options', display='$output_widget', display_params='$output_widget_params', default_text='$input_default' WHERE id={$row['id']};")) {
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					$insert = false;
				}
				echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['upgraded'] . "</span></p>";
			}
			else
			{
				$q = "INSERT INTO {$tbl_site_tmplvars} (type,name,caption,description,category,locked,elements,display,display_params,default_text) VALUES('$input_type','$name','$caption','$desc',$category,$locked,'$input_options','$output_widget','$output_widget_params','$input_default');";
				if (!@ mysql_query($q))
				{
					echo "<p>" . mysql_error() . "</p>";
					return;
				}
				echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['installed'] . "</span></p>";
			}
			
			// add template assignments
			$assignments = explode(',', $assignments);
			if (count($assignments) > 0)
			{
				// remove existing tv -> template assignments
				$ds=mysql_query("SELECT id FROM {$tbl_site_tmplvars} WHERE name='$name' AND description='$desc';");
				$row = mysql_fetch_assoc($ds);
				$id = $row["id"];
				mysql_query("DELETE FROM {$tbl_site_tmplvar_templates} WHERE tmplvarid = '{$id}'");
				
				// add tv -> template assignments
				foreach ($assignments as $assignment)
				{
					$template = modx_escape($assignment);
					$ts = mysql_query("SELECT id FROM {$tbl_site_templates} WHERE templatename='$template';");
					if ($ds && $ts)
					{
						$tRow = mysql_fetch_assoc($ts);
						$templateId = $tRow['id'];
						mysql_query("INSERT INTO {$tbl_site_tmplvar_templates} (tmplvarid, templateid) VALUES($id, $templateId)");
					}
				}
			}
		}
	}
}

// Install Chunks
if (isset ($_POST['chunk']) || $installData)
{
	echo "<h3>" . $_lang['chunks'] . ":</h3> ";
	$selChunks = $_POST['chunk'];
	foreach ($moduleChunks as $k=>$moduleChunk)
	{
		$installSample = in_array('sample', $moduleChunk[5]) && $installData == 1;
		if(in_array($k, $selChunks) || $installSample)
		{
			$name      = modx_escape($moduleChunk[0]);
			$desc      = modx_escape($moduleChunk[1]);
			$category  = modx_escape($moduleChunk[3]);
			$overwrite = modx_escape($moduleChunk[4]);
			$filecontent = $moduleChunk[2];
			
			if (!file_exists($filecontent))
			{
				echo "<p>&nbsp;&nbsp;$name: <span class=\"notok\">" . "{$_lang['unable_install_chunk']} '{$filecontent}' {$_lang['not_found']}</span></p>";
			}
			else
			{
				// Create the category if it does not already exist
				$category_id = getCreateDbCategory($category, $sqlParser);
				
				$chunk = preg_replace("/^.*?\/\*\*.*?\*\/\s+/s", '', file_get_contents($filecontent), 1);
				$chunk = modx_escape($chunk);
				$rs = mysql_query("SELECT * FROM {$tbl_site_htmlsnippets} WHERE name='$name'");
				$count_original_name = mysql_num_rows($rs);
				if($overwrite == 'false')
				{
					$newname = $name . '-' . str_replace('.', '_', $modx_version);
					$rs = mysql_query("SELECT * FROM {$tbl_site_htmlsnippets} WHERE name='$newname'");
					$count_new_name = mysql_num_rows($rs);
				}
				$update = $count_original_name > 0 && $overwrite == 'true';
				if ($update)
				{
					if (!@ mysql_query("UPDATE {$tbl_site_htmlsnippets} SET snippet='$chunk', description='$desc', category=$category_id WHERE name='$name';"))
					{
						$errors += 1;
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['upgraded'] . "</span></p>";
				}
				elseif($count_new_name == 0)
				{
					if($count_original_name > 0 && $overwrite == 'false')
					{
						$name = $newname;
					}
					if (!@ mysql_query("INSERT INTO {$tbl_site_htmlsnippets} (name,description,snippet,category) VALUES('$name','$desc','$chunk',$category_id);"))
					{
						$errors += 1;
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['installed'] . "</span></p>";
				}
			}
		}
	}
}

// Install Modules
if (isset ($_POST['module']) || $installData)
{
	echo "<h3>" . $_lang['modules'] . ":</h3> ";
	$selModules = $_POST['module'];
	foreach ($moduleModules as $k=>$moduleModule)
	{
		$installSample = in_array('sample', $moduleModule[7]) && $installData == 1;
		if(in_array($k, $selModules) || $installSample)
		{
			$name = modx_escape($moduleModule[0]);
			$desc = modx_escape($moduleModule[1]);
			$filecontent = $moduleModule[2];
			$properties = modx_escape($moduleModule[3]);
			$guid = modx_escape($moduleModule[4]);
			$shared = modx_escape($moduleModule[5]);
			$category = modx_escape($moduleModule[6]);
			if (!file_exists($filecontent))
			{
				echo "<p>&nbsp;&nbsp;$name: <span class=\"notok\">" . "{$_lang['unable_install_module']} '{$filecontent}' {$_lang['not_found']}</span></p>";
			}
			else
			{
				// Create the category if it does not already exist
				$category = getCreateDbCategory($category, $sqlParser);
				
				$module = end(preg_split("/(\/\/)?\s*\<\?php/", file_get_contents($filecontent), 2));
				// remove installer docblock
				$module = preg_replace("/^.*?\/\*\*.*?\*\/\s+/s", '', $module, 1);
				$module = modx_escape($module);
				$rs = mysql_query("SELECT * FROM {$tbl_site_modules} WHERE name='$name'");
				if (mysql_num_rows($rs))
				{
					$row = mysql_fetch_assoc($rs);
					$props = propUpdate($properties,$row['properties']);
					if (!@ mysql_query("UPDATE {$tbl_site_modules} SET modulecode='$module', description='$desc', properties='$props', enable_sharedparams='$shared' WHERE name='$name';"))
					{
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['upgraded'] . "</span></p>";
				}
				else
				{
					if (!@ mysql_query("INSERT INTO {$tbl_site_modules} (name,description,modulecode,properties,guid,enable_sharedparams,category) VALUES('$name','$desc','$module','$properties','$guid','$shared', $category);"))
					{
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['installed'] . "</span></p>";
				}
			}
		}
	}
}

// Install Plugins
if (isset ($_POST['plugin']) || $installData)
{
	echo "<h3>" . $_lang['plugins'] . ":</h3> ";
	$selPlugs = $_POST['plugin'];
	foreach ($modulePlugins as $k=>$modulePlugin)
	{
		$installSample = in_array('sample', $modulePlugin[8]) && $installData == 1;
		if(in_array($k, $selPlugs) || $installSample)
		{
			$name = modx_escape($modulePlugin[0]);
			$desc = modx_escape($modulePlugin[1]);
			$filecontent = $modulePlugin[2];
			$properties = modx_escape($modulePlugin[3]);
			$events = explode(",", $modulePlugin[4]);
			$guid = modx_escape($modulePlugin[5]);
			$category = modx_escape($modulePlugin[6]);
			$leg_names = '';
			if(array_key_exists(7, $modulePlugin))
			{
				// parse comma-separated legacy names and prepare them for sql IN clause
				$leg_names = "'" . implode("','", preg_split('/\s*,\s*/', modx_escape($modulePlugin[7]))) . "'";
			}
			if(!file_exists($filecontent))
			{
				echo "<p>&nbsp;&nbsp;$name: <span class=\"notok\">" . $_lang['unable_install_plugin'] . " '$filecontent' " . $_lang['not_found'] . ".</span></p>";
			}
			else 
			{
				// disable legacy versions based on legacy_names provided
				if(!empty($leg_names))
				{
					$update_query = "UPDATE {$tbl_site_plugins} SET disabled='1' WHERE name IN ($leg_names);";
					$rs = mysql_query($update_query);
				}
				
				// Create the category if it does not already exist
				$category = getCreateDbCategory($category, $sqlParser);
				
				$plugin = end(preg_split("@(//)?\s*\<\?php@", file_get_contents($filecontent), 2));
				// remove installer docblock
				$plugin = preg_replace("@^.*?/\*\*.*?\*/\s+@s", '', $plugin, 1);
				$plugin = modx_escape($plugin);
				$rs = mysql_query("SELECT * FROM {$tbl_site_plugins} WHERE name='$name' AND disabled='0'");
				if(mysql_num_rows($rs))
				{
					$insert = true;
					while($row = mysql_fetch_assoc($rs))
					{
						$props = propUpdate($properties,$row['properties']);
						if($row['description'] == $desc)
						{
							if(!@ mysql_query("UPDATE {$tbl_site_plugins} SET plugincode='$plugin', description='$desc', properties='$props' WHERE id={$row['id']};"))
							{
								echo "<p>" . mysql_error() . "</p>";
								return;
							}
							$insert = false;
						}
						else
						{
							if(!@ mysql_query("UPDATE {$tbl_site_plugins} SET disabled='1' WHERE id={$row['id']};"))
							{
								echo "<p>".mysql_error()."</p>";
								return;
							}
						}
					}
					if($insert === true)
					{
						if($props) $properties = $props;
						if(!@mysql_query("INSERT INTO {$tbl_site_plugins} (name,description,plugincode,properties,moduleguid,disabled,category) VALUES('$name','$desc','$plugin','$properties','$guid','0',$category);"))
						{
							echo "<p>".mysql_error()."</p>";
							return;
						}
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['upgraded'] . "</span></p>";
				}
				else
				{
					if(!@ mysql_query("INSERT INTO {$tbl_site_plugins} (name,description,plugincode,properties,moduleguid,category) VALUES('$name','$desc','$plugin','$properties','$guid',$category);"))
					{
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['installed'] . "</span></p>";
				}
				// add system events
				if(count($events) > 0)
				{
				$ds=mysql_query("SELECT id FROM {$tbl_site_plugins} WHERE name='$name' AND description='$desc';");
					if($ds)
					{
						$row = mysql_fetch_assoc($ds);
						$id = $row["id"];
						// remove existing events
						mysql_query("DELETE FROM {$tbl_site_plugin_events} WHERE pluginid = '{$id}'");
						// add new events
						mysql_query("INSERT INTO {$tbl_site_plugin_events} (pluginid, evtid) SELECT '{$id}' as 'pluginid',se.id as 'evtid' FROM {$tbl_system_eventnames} se WHERE name IN ('" . implode("','", $events) . "')");
					}
				}
			}
		}
	}
}

// Install Snippets
if (isset ($_POST['snippet']) || $installData)
{
	echo "<h3>" . $_lang['snippets'] . ":</h3> ";
	$selSnips = $_POST['snippet'];
	foreach ($moduleSnippets as $k=>$moduleSnippet)
	{
		$installSample = in_array('sample', $moduleSnippet[5]) && $installData == 1;
		if(in_array($k, $selSnips) || $installSample)
		{
			$name = modx_escape($moduleSnippet[0]);
			$desc = modx_escape($moduleSnippet[1]);
			$filecontent = $moduleSnippet[2];
			$properties  = modx_escape($moduleSnippet[3]);
			$category    = modx_escape($moduleSnippet[4]);
			if (!file_exists($filecontent))
			{
				echo '<p>&nbsp;&nbsp;' . $name . ': <span class="notok">' . $_lang['unable_install_snippet'] . " '$filecontent' " . $_lang['not_found'] . '.</span></p>';
			}
			else
			{
				// Create the category if it does not already exist
				$category = getCreateDbCategory($category, $sqlParser);
				
				$snippet = end(preg_split("@(//)?\s*\<\?php@", file_get_contents($filecontent)));
				// remove installer docblock
				$snippet = preg_replace("@^.*?/\*\*.*?\*/\s+@s", '', $snippet, 1);
				$snippet = modx_escape($snippet);
				$rs = mysql_query("SELECT * FROM {$tbl_site_snippets} WHERE name='$name'");
				if (mysql_num_rows($rs))
				{
					$row = mysql_fetch_assoc($rs);
					$props = propUpdate($properties,$row['properties']);
					if (!@ mysql_query("UPDATE {$tbl_site_snippets} SET snippet='$snippet', description='$desc', properties='$props' WHERE name='$name';"))
					{
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['upgraded'] . "</span></p>";
				}
				else
				{
					if (!@ mysql_query("INSERT INTO {$tbl_site_snippets} (name,description,snippet,properties,category) VALUES('$name','$desc','$snippet','$properties',$category);"))
					{
						echo "<p>" . mysql_error() . "</p>";
						return;
					}
					echo "<p>&nbsp;&nbsp;$name: <span class=\"ok\">" . $_lang['installed'] . "</span></p>";
				}
			}
		}
	}
}

// install data
if ($installData && $moduleSQLDataFile)
{
	echo "<p>" . $_lang['installing_demo_site'];
	$sqlParser->process($moduleSQLDataFile);
	if ($sqlParser->installFailed == true)
	{
		$errors += 1;
		echo "<span class=\"notok\"><b>" . $_lang['database_alerts'] . "</span></p>";
		echo "<p>" . $_lang['setup_couldnt_install'] . "</p>";
		echo "<p>" . $_lang['installation_error_occured'] . "<br /><br />";
		for ($i = 0; $i < count($sqlParser->mysqlErrors); $i++)
		{
			echo "<em>" . $sqlParser->mysqlErrors[$i]["error"] . "</em>" . $_lang['during_execution_of_sql'] . "<span class='mono'>" . strip_tags($sqlParser->mysqlErrors[$i]["sql"]) . "</span>.<hr />";
		}
		echo "</p>";
		echo "<p>" . $_lang['some_tables_not_updated'] . "</p>";
		return;
	}
	else
	{
		echo "<span class=\"ok\">".$_lang['ok']."</span></p>";
	}
}

// call back function
if ($callBackFnc != '') $callBackFnc ($sqlParser);

// Setup the MODx API -- needed for the cache processor
// initiate a new document parser
include_once("{$base_path}index.php");

$modx->clearCache(); // always empty cache after install

// try to chmod the cache go-rwx (for suexeced php)
@chmod("{$base_path}assets/cache/siteCache.idx.php", 0600);
@chmod("{$base_path}assets/cache/sitePublishing.idx.php", 0600);

// remove any locks on the manager functions so initial manager login is not blocked
mysql_query("TRUNCATE TABLE {$tbl_active_users}");

// andrazk 20070416 - release manager access
if (file_exists("{$base_path}assets/cache/installProc.inc.php"))
{
	@chmod("{$base_path}assets/cache/installProc.inc.php", 0755);
	unlink("{$base_path}assets/cache/installProc.inc.php");
}

// setup completed!
echo "<p><b>" . $_lang['installation_successful'] . "</b></p>";
echo "<p>" . $_lang['to_log_into_content_manager'] . "</p>";
if ($installMode == 0)
{
	echo '<p><img src="img/ico_info.png" align="left" style="margin-right:10px;" />' . $_lang['installation_note'] . "</p>";
}
else
{
	echo '<p><img src="img/ico_info.png" align="left" style="margin-right:10px;" />' . $_lang['upgrade_note'] . "</p>";
}



function getFullTableName($table_name)
{
	return "`{$_POST['database_name']}`.`{$_POST['tableprefix']}{$table_name}`";
}

function parseProperties($propertyString)
{
	$parameter= array ();
	if (!empty($propertyString))
	{
		$tmpParams= explode('&', $propertyString);
		for ($x= 0; $x < count($tmpParams); $x++)
		{
			if (strpos($tmpParams[$x], '=', 0))
			{
				$pTmp= explode('=', $tmpParams[$x]);
				$pvTmp= explode(';', trim($pTmp[1]));
				if ($pvTmp[1] == 'list' && $pvTmp[3] != '')
				{
					$parameter[trim($pTmp[0])]= $pvTmp[3]; //list default
				}
				elseif ($pvTmp[1] != 'list' && $pvTmp[2] != '')
				{
					$parameter[trim($pTmp[0])]= $pvTmp[2];
				}
			}
		}
	}
	return $parameter;
}

function result($status='ok',$ph=array())
{
	$ph['status'] = $status;
	$ph['name']   = ($ph['name']) ? "&nbsp;&nbsp;{$ph['name']} : " : '';
	if(!isset($ph['msg'])) $ph['msg'] = '';
	$tpl = '<p>[+name+]<span class="[+status+]">[+msg+]</span></p>';
	return parse($tpl,$ph);
}
