# For backward compatibilty with early versions
#::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

# 090-091

ALTER TABLE `{PREFIX}site_content` ADD COLUMN `publishedon` int(20) NOT NULL DEFAULT '0' COMMENT 'Date the document was published' AFTER `deletedby`;

ALTER TABLE `{PREFIX}site_content` ADD COLUMN `publishedby` int(10) NOT NULL DEFAULT '0' COMMENT 'ID of user who published the document' AFTER `publishedon`;

ALTER TABLE `{PREFIX}site_plugins` MODIFY COLUMN `properties` text COMMENT 'Default Properties';

ALTER TABLE `{PREFIX}site_snippets` MODIFY COLUMN `properties` text COMMENT 'Default Properties';

ALTER TABLE `{PREFIX}site_tmplvar_templates`
 DROP INDEX `idx_tmplvarid`,
 DROP INDEX `idx_templateid`,
 ADD PRIMARY KEY (`tmplvarid`, `templateid`);

ALTER TABLE `{PREFIX}user_roles` ADD COLUMN `view_unpublished` int(1) NOT NULL DEFAULT '0' AFTER `web_access_permissions`;

#091-092

#092-095

ALTER TABLE `{PREFIX}categories` MODIFY COLUMN `category` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}categories` MODIFY COLUMN `category` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}event_log` MODIFY COLUMN `source` varchar(50) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}event_log` MODIFY COLUMN `description` text;

ALTER TABLE `{PREFIX}manager_users` MODIFY COLUMN `username` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}site_content` 
 MODIFY COLUMN `pagetitle` varchar(255) NOT NULL default '',
 MODIFY COLUMN `alias` varchar(255) default '',
 MODIFY COLUMN `introtext` text COMMENT 'Used to provide quick summary of the document',
 MODIFY COLUMN `content` mediumtext,
 MODIFY COLUMN `menutitle` varchar(255) NOT NULL DEFAULT '' COMMENT 'Menu title';

ALTER TABLE `{PREFIX}site_content` ADD COLUMN `link_attributes` varchar(255) NOT NULL DEFAULT '' COMMENT 'Link attriubtes' AFTER `alias`;

ALTER TABLE `{PREFIX}site_htmlsnippets` MODIFY COLUMN `snippet` mediumtext;

ALTER TABLE `{PREFIX}site_modules`
 MODIFY COLUMN `name` varchar(50) NOT NULL DEFAULT '',
 MODIFY COLUMN `disabled` tinyint(4) NOT NULL DEFAULT '0',
 MODIFY COLUMN `icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'url to module icon',
 MODIFY COLUMN `resourcefile` varchar(255) NOT NULL DEFAULT '' COMMENT 'a physical link to a resource file',
 MODIFY COLUMN `createdon` int(11) NOT NULL DEFAULT '0',
 MODIFY COLUMN `editedon` int(11) NOT NULL DEFAULT '0',
 MODIFY COLUMN `guid` varchar(32) NOT NULL DEFAULT '' COMMENT 'globally unique identifier',
 MODIFY COLUMN `properties` text,
 MODIFY COLUMN `modulecode` mediumtext COMMENT 'module boot up code';

ALTER TABLE `{PREFIX}site_module_access`
 MODIFY COLUMN `module` int(11) NOT NULL DEFAULT '0',
 MODIFY COLUMN `usergroup` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `{PREFIX}site_module_depobj`
 MODIFY COLUMN `module` int(11) NOT NULL DEFAULT '0',
 MODIFY COLUMN `resource` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `{PREFIX}site_plugins`
 MODIFY COLUMN `plugincode` mediumtext,
 MODIFY COLUMN `moduleguid` varchar(32) NOT NULL DEFAULT '' COMMENT 'GUID of module from which to import shared parameters';

ALTER TABLE `{PREFIX}site_plugin_events`
 MODIFY COLUMN `evtid` int(10) NOT NULL DEFAULT '0';

ALTER TABLE `{PREFIX}site_plugin_events` ADD COLUMN `priority` INT(10) NOT NULL default '0' COMMENT 'determines the run order of the plugin' AFTER `evtid`;

ALTER TABLE `{PREFIX}site_snippets`
 MODIFY COLUMN `snippet` mediumtext,
 MODIFY COLUMN `moduleguid` varchar(32) NOT NULL DEFAULT '' COMMENT 'GUID of module from which to import shared parameters';

ALTER TABLE `{PREFIX}site_templates`
 MODIFY COLUMN `icon` varchar(255) NOT NULL default '' COMMENT 'url to icon file',
 MODIFY COLUMN `content` mediumtext;

ALTER TABLE `{PREFIX}site_tmplvars`
 MODIFY COLUMN `name` varchar(50) NOT NULL default '',
 MODIFY COLUMN `elements` text,
 MODIFY COLUMN `display` varchar(20) NOT NULL DEFAULT '' COMMENT 'Display Control',
 MODIFY COLUMN `display_params` text COMMENT 'Display Control Properties',
 MODIFY COLUMN `default_text` text;

ALTER TABLE `{PREFIX}site_tmplvar_contentvalues`
 MODIFY COLUMN `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id',
 MODIFY COLUMN `value` text;

ALTER TABLE `{PREFIX}site_tmplvar_templates` MODIFY COLUMN `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id';

ALTER TABLE `{PREFIX}site_tmplvar_templates` ADD COLUMN `rank` integer(11) NOT NULL DEFAULT '0' AFTER `templateid`;

ALTER TABLE `{PREFIX}system_eventnames`
 MODIFY COLUMN  `name` varchar(50) NOT NULL DEFAULT '',
 MODIFY COLUMN `service` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'System Service number';

ALTER TABLE `{PREFIX}system_settings` MODIFY COLUMN `setting_value` text;

ALTER TABLE `{PREFIX}user_attributes`
 MODIFY COLUMN `country` varchar(5) NOT NULL DEFAULT '',
 MODIFY COLUMN `state` varchar(25) NOT NULL DEFAULT '',
 MODIFY COLUMN `zip` varchar(25) NOT NULL DEFAULT '',
 MODIFY COLUMN `fax` varchar(100) NOT NULL DEFAULT '',
 MODIFY COLUMN `photo` varchar(255) NOT NULL DEFAULT '' COMMENT 'link to photo',
 MODIFY COLUMN `comment` varchar(255) NOT NULL DEFAULT '' COMMENT 'short comment';

ALTER TABLE `{PREFIX}user_settings` MODIFY COLUMN `setting_value` text;

ALTER TABLE `{PREFIX}user_messages` MODIFY COLUMN `message` text;

ALTER TABLE `{PREFIX}user_roles` ADD COLUMN `publish_document` int(1) NOT NULL DEFAULT '0' AFTER `save_document`;

ALTER TABLE `{PREFIX}web_users`
 MODIFY COLUMN `username` varchar(100) NOT NULL DEFAULT '',
 MODIFY COLUMN `cachepwd` varchar(100) NOT NULL DEFAULT '' COMMENT 'Store new unconfirmed password' AFTER `password`;

ALTER TABLE `{PREFIX}web_user_attributes`
 MODIFY COLUMN `country` varchar(25) NOT NULL DEFAULT '',
 MODIFY COLUMN `zip` varchar(25) NOT NULL DEFAULT '',
 MODIFY COLUMN `fax` varchar(100) NOT NULL DEFAULT '',
 MODIFY COLUMN `photo` varchar(255) NOT NULL DEFAULT '' COMMENT 'link to photo';

ALTER TABLE `{PREFIX}web_user_settings` MODIFY COLUMN `setting_value` text;

#095-096

ALTER TABLE `{PREFIX}user_roles`
 ADD COLUMN `edit_chunk` int(1) NOT NULL DEFAULT '0' AFTER `delete_snippet`,
 ADD COLUMN `new_chunk` int(1) NOT NULL DEFAULT '0' AFTER `edit_chunk`,
 ADD COLUMN `save_chunk` int(1) NOT NULL DEFAULT '0' AFTER `new_chunk`,
 ADD COLUMN `delete_chunk` int(1) NOT NULL DEFAULT '0' AFTER `save_chunk`,
 ADD COLUMN `import_static` int(1) NOT NULL DEFAULT '0' AFTER `view_unpublished`,
 ADD COLUMN `export_static` int(1) NOT NULL DEFAULT '0' AFTER `import_static`;

ALTER TABLE `{PREFIX}web_user_attributes`
 MODIFY COLUMN `state` varchar(25) NOT NULL DEFAULT '',
 MODIFY COLUMN `zip` varchar(25) NOT NULL DEFAULT '';

#096-0961

#0961-0963

ALTER TABLE `{PREFIX}user_roles` ADD COLUMN `empty_trash` int(1) NOT NULL DEFAULT '0' AFTER `delete_document`;

#0963-1.0.0

#1.0.3-1.0.4

ALTER TABLE `{PREFIX}user_roles` ADD COLUMN `remove_locks` int(1) NOT NULL DEFAULT '0';

#1.0.4-1.0.5

ALTER TABLE `{PREFIX}member_groups` ADD UNIQUE INDEX `ix_group_member` (`user_group`,`member`);

ALTER TABLE `{PREFIX}user_attributes` MODIFY COLUMN `comment` text;

ALTER TABLE `{PREFIX}web_groups` ADD UNIQUE INDEX `ix_group_user` (`webgroup`,`webuser`);

ALTER TABLE `{PREFIX}web_user_attributes` MODIFY COLUMN `comment` text;

# Set the private manager group flag

UPDATE {PREFIX}documentgroup_names AS dgn
  LEFT JOIN {PREFIX}membergroup_access AS mga ON mga.documentgroup = dgn.id
  LEFT JOIN {PREFIX}webgroup_access AS wga ON wga.documentgroup = dgn.id
  SET dgn.private_memgroup = (mga.membergroup IS NOT NULL),
      dgn.private_webgroup = (wga.webgroup IS NOT NULL);


UPDATE `{PREFIX}site_plugins` SET `disabled` = '1' WHERE `name` IN ('Bottom Button Bar');

UPDATE `{PREFIX}site_plugins` SET `disabled` = '1' WHERE `name` IN ('Inherit Parent Template');

UPDATE `{PREFIX}system_settings` SET `setting_value` = '' WHERE `setting_name` = 'settings_version';

UPDATE `{PREFIX}system_settings` SET `setting_value` = '0' WHERE `setting_name` = 'validate_referer' AND `setting_value` = '00';

# start related to #MODX-1321

UPDATE `{PREFIX}site_content` SET `type`='reference', `contentType`='text/html' WHERE `type`='' AND `content` REGEXP '^https?://([-\w\.]+)+(:\d+)?/?';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/xml' WHERE `type`='' AND `alias` REGEXP '\.(rss|xml)$';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/javascript' WHERE `type`='' AND `alias` REGEXP '\.js$';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/css' WHERE `type`='' AND `alias` REGEXP '\.css$';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/html' WHERE `type`='';

#1.0.5-1.0.6

ALTER TABLE `{PREFIX}site_content` MODIFY COLUMN `template` int(10) NOT NULL default '0';

ALTER TABLE `{PREFIX}site_content` ADD INDEX `typeidx` (`type`);

ALTER TABLE `{PREFIX}system_settings` DROP PRIMARY KEY;

ALTER TABLE `{PREFIX}system_settings` DROP INDEX `setting_name`;

ALTER TABLE `{PREFIX}system_settings` ADD PRIMARY KEY (`setting_name`);

ALTER TABLE `{PREFIX}user_settings` DROP PRIMARY KEY;

ALTER TABLE `{PREFIX}user_settings` ADD PRIMARY KEY (`user`, `setting_name`);

ALTER TABLE `{PREFIX}web_user_settings` DROP PRIMARY KEY;

ALTER TABLE `{PREFIX}web_user_settings` ADD PRIMARY KEY (`webuser`, `setting_name`);

ALTER TABLE `{PREFIX}site_plugin_events` DROP PRIMARY KEY;

ALTER TABLE `{PREFIX}site_plugin_events` ADD PRIMARY KEY (`pluginid`, `evtid`);

ALTER TABLE `{PREFIX}active_users` MODIFY COLUMN `ip` varchar(50) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}site_tmplvar_contentvalues` ADD FULLTEXT `value_ft_idx` (`value`);

#1.0.10-1.0.11

ALTER TABLE `{PREFIX}user_attributes`
 ADD COLUMN `street` varchar(255) NOT NULL DEFAULT '' AFTER `country`,
 ADD COLUMN `city` varchar(255) NOT NULL DEFAULT '' AFTER `street`;

ALTER TABLE `{PREFIX}web_user_attributes`
 ADD COLUMN `street` varchar(255) NOT NULL DEFAULT '' AFTER `country`,
 ADD COLUMN `city` varchar(255) NOT NULL DEFAULT '' AFTER `street`;

ALTER TABLE `{PREFIX}site_content` ADD COLUMN `alias_visible` INT(2) NOT NULL DEFAULT '1' COMMENT 'Hide document from alias path';
