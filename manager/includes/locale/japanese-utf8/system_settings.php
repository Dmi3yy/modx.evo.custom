<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!function_exists('mysql_set_charset'))
{
	$_lang['settings_after_install'] .= '<br /><strong style="color:red;">この環境では日本語以外の文字(中国語・韓国語・一部の機種依存文字など)を入力できません。</strong>対応が必要な場合は、サーバ環境のUTF-8エンコードの扱いを整備したうえで、dbapi.mysql.class.inc.phpのescape関数の処理を書き換えてください。mb_convert_encodingの処理を行なっている行が2行ありますので、これを削除します。';
}
$simple_version = str_replace('.','',$settings_version);
$simple_version = substr($simple_version,0,3);
run_update($simple_version);

if(!isset($modx->config['manager_theme']) || substr($settings_version,0,4)=='0.9.')
{
	$manager_theme = 'MODxCarbon';
}

function run_update($version)
{
	global $modx;
	
	$version = intval($version);
	
	if($version < 100)
	{
		update_tbl_system_eventnames('100');
	}
	
	if($version < 102)
	{
		update_tbl_system_eventnames('102');
	}
	
	if($version < 104)
	{
		update_tbl_user_roles();
	}
	
	if($version < 105)
	{
		update_tbl_system_eventnames('105');
		update_tbl_user_attributes();
		update_tbl_web_user_attributes();
		update_tbl_member_groups();
		update_tbl_web_groups();
		update_tbl_system_settings();
	}
	
	if($version < 106)
	{
		update_config_custom_contenttype();
		update_config_default_template_method();
		update_tbl_member_groups();
	}
}

function update_config_custom_contenttype()
{
	global $modx,$custom_contenttype;
	
	$search[] = 'text/css,text/html,text/javascript,text/plain,text/xml';
	$search[] = 'application/rss+xml,application/pdf,application/msword,application/excel,text/html,text/css,text/xml,text/javascript,text/plain';
	$replace  = 'application/rss+xml,application/pdf,application/vnd.ms-word,application/vnd.ms-excel,text/html,text/css,text/xml,text/javascript,text/plain';
	
	foreach($search as $v)
	{
		if($v == $modx->config['custom_contenttype']) $custom_contenttype = $replace;
	}
}

function update_config_default_template_method()
{
	global $modx,$auto_template_logic;
	
	$tbl_site_plugins = $modx->getFullTableName('site_plugins');
	$rs = $modx->db->select('properties,disabled', $tbl_site_plugins, "`name`='Inherit Parent Template'");
	$row = $modx->db->getRow($rs);
	if($row)
	{
		$modx->db->update("`disabled`='1'", $tbl_site_plugins, "`name` IN ('Inherit Parent Template')");
	}
	if(!$row || !isset($modx->config['auto_template_logic'])) $auto_template_logic = 'sibling'; // not installed
	else
	{
		if($row['disabled'] == 1) $auto_template_logic = 'sibling'; // installed but disabled
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

function update_tbl_user_roles()
{
	global $modx;
	$tbl_user_roles = $modx->getFullTableName('user_roles');
	
	$data = $modx->db->getTableMetaData($tbl_user_roles);
	if($data['remove_locks'] == false)
	{
		$sql = "ALTER TABLE {$tbl_user_roles} ADD COLUMN `remove_locks` int(1) NOT NULL DEFAULT '0'";
		$modx->db->query($sql);
		$modx->db->update("`remove_locks` = '1'", $tbl_user_roles, "`id` =1");
	}
}

function update_tbl_member_groups()
{
	global $modx;
	$tbl_member_groups = $modx->getFullTableName('member_groups');
	
	$sql = "SHOW INDEX FROM {$tbl_member_groups}";
	$rs = $modx->db->query($sql);
	$find_index = 'notfind';
	while($row = $modx->db->getRow($rs))
	{
		if($row['Key_name']=='ix_group_member') $find_index = 'find';
	}
	if($find_index=='notfind')
	{
		$sql = "ALTER TABLE {$tbl_member_groups} ADD UNIQUE INDEX `ix_group_member` (`user_group`,`member`)";
		$modx->db->query($sql);
	}
}

function update_tbl_web_groups()
{
	global $modx;
	$tbl_web_groups = $modx->getFullTableName('web_groups');
	
	$sql = "SHOW INDEX FROM {$tbl_web_groups}";
	$rs = $modx->db->query($sql);
	$find_index = 'notfind';
	while($row = $modx->db->getRow($rs))
	{
		if($row['Key_name']=='ix_group_user') $find_index = 'find';
	}
	if($find_index=='notfind')
	{
		$sql = "ALTER TABLE {$tbl_web_groups} ADD UNIQUE INDEX `ix_group_user` (`webgroup`,`webuser`)";
		$modx->db->query($sql);
	}
}

function update_tbl_system_eventnames($version)
{
	global $modx;
	$tbl_system_eventnames = $modx->getFullTableName('system_eventnames');
	
	switch($version)
	{
		case '100':
			$sql = "REPLACE INTO {$tbl_system_eventnames} (id,name,service,groupname) VALUES
			          ('100', 'OnStripAlias',             '1','Documents'),
			          ('201', 'OnManagerWelcomePrerender','2',''),
			          ('202', 'OnManagerWelcomeHome',     '2',''),
			          ('203', 'OnManagerWelcomeRender',   '2','')";
			break;
		case '102':
			$sql = "REPLACE INTO {$tbl_system_eventnames} (id,name,service,groupname) VALUES
			          ('204', 'OnBeforeDocDuplicate',     '1','Documents'),
			          ('205', 'OnDocDuplicate',           '1','Documents')";
			break;
		case '105':
			$sql = "REPLACE INTO {$tbl_system_eventnames} (id,name,service,groupname) VALUES
			          ('9','OnWebChangePassword','3',''),
			          ('14','OnManagerSaveUser','2',''),
			          ('16','OnManagerChangePassword','2',''),
			          ('206','OnManagerMainFrameHeaderHTMLBlock','2','')";
			break;
	}
	$modx->db->query($sql);
}

function update_tbl_user_attributes()
{
	global $modx;
	$tbl_user_attributes     = $modx->getFullTableName('user_attributes');
	
	$sql = "ALTER TABLE {$tbl_user_attributes} 
	        MODIFY COLUMN `state` varchar(25) NOT NULL default '',
	        MODIFY COLUMN `zip` varchar(25) NOT NULL default '',
	        MODIFY COLUMN `comment` text;";
	$modx->db->query($sql);
}

function update_tbl_web_user_attributes()
{
	global $modx;
	$tbl_web_user_attributes = $modx->getFullTableName('web_user_attributes');
	
	$sql = "ALTER TABLE {$tbl_web_user_attributes} 
	        MODIFY COLUMN `state` varchar(25) NOT NULL default '',
	        MODIFY COLUMN `zip` varchar(25) NOT NULL default '',
	        MODIFY COLUMN `comment` text;";
	$modx->db->query($sql);
}

function update_tbl_system_settings()
{
	global $modx;
	$tbl_system_settings     = $modx->getFullTableName('system_settings');
	$modx->db->update("`setting_value` = '0'", $tbl_system_settings, "`setting_name` = 'validate_referer' AND `setting_value` = '00'");
}
