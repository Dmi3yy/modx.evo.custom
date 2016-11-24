CREATE TABLE IF NOT EXISTS `{PREFIX}active_users` (
  `internalKey` int(9) NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `lasthit` int(20) NOT NULL default '0',
  `id` int(10) default NULL,
  `action` varchar(10) NOT NULL default '',
  `ip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`internalKey`)
) ENGINE=MyISAM COMMENT='Contains data about active users.';

CREATE TABLE IF NOT EXISTS `{PREFIX}active_user_locks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `internalKey` int(9) NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `firsthit` int(20) NOT NULL default '0',
  `lasthit` int(20) NOT NULL default '0',
  `element` int(1) NOT NULL default '0',
  PRIMARY KEY(`id`),
  UNIQUE INDEX  ix_element_id (`internalKey`,`element`,`id`)
) ENGINE=MyISAM COMMENT='Contains data about all elements that are locked by active users.';

CREATE TABLE IF NOT EXISTS `{PREFIX}categories` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `category` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY(`id`)
) ENGINE=MyISAM COMMENT='Categories to be used snippets,tv,chunks, etc';

CREATE TABLE IF NOT EXISTS `{PREFIX}document_groups` (
  `id` int(10) NOT NULL auto_increment,
  `document_group` int(10) NOT NULL default '0',
  `document` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `document` (`document`),
  KEY `document_group` (`document_group`)
) ENGINE=MyISAM COMMENT='Contains data used for access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}documentgroup_names` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(245) NOT NULL default '',
  `private_memgroup` tinyint DEFAULT 0 COMMENT 'determine whether the document group is private to manager users',
  `private_webgroup` tinyint DEFAULT 0 COMMENT 'determines whether the document is private to web users',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM COMMENT='Contains data used for access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}event_log` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `eventid` integer DEFAULT 0,
  `createdon` integer NOT NULL DEFAULT 0,
  `type` tinyint NOT NULL DEFAULT 1 COMMENT '1- information, 2 - warning, 3- error',
  `user` integer NOT NULL DEFAULT 0 COMMENT 'link to user table',
  `usertype` tinyint NOT NULL DEFAULT 0 COMMENT '0 - manager, 1 - web',
  `source` varchar(50) NOT NULL DEFAULT '',
  `description` text,
  PRIMARY KEY(`id`),
  KEY `user`(`user`)
) ENGINE=MyISAM COMMENT='Stores event and error logs';


CREATE TABLE IF NOT EXISTS `{PREFIX}keyword_xref` (
  `content_id` int(11) NOT NULL default '0',
  `keyword_id` int(11) NOT NULL default '0',
  KEY `content_id` (`content_id`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=MyISAM COMMENT='Cross reference bewteen keywords and content';


CREATE TABLE IF NOT EXISTS `{PREFIX}manager_log` (
  `id` int(10) NOT NULL auto_increment,
  `timestamp` int(20) NOT NULL default '0',
  `internalKey` int(10) NOT NULL default '0',
  `username` varchar(255) default NULL,
  `action` int(10) NOT NULL default '0',
  `itemid` varchar(10) default '0',
  `itemname` varchar(255) default NULL,
  `message` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains a record of user interaction.';

CREATE TABLE IF NOT EXISTS `{PREFIX}manager_users` (
  `id` int(10) NOT NULL auto_increment,
  `username` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM COMMENT='Contains login information for backend users.';

CREATE TABLE IF NOT EXISTS `{PREFIX}member_groups` (
  `id` int(10) NOT NULL auto_increment,
  `user_group` int(10) NOT NULL default '0',
  `member` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE INDEX `ix_group_member` (`user_group`,`member`)
) ENGINE=MyISAM COMMENT='Contains data used for access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}membergroup_access` (
  `id` int(10) NOT NULL auto_increment,
  `membergroup` int(10) NOT NULL default '0',
  `documentgroup` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains data used for access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}membergroup_names` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(245) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM COMMENT='Contains data used for access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_content` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(20) NOT NULL default 'document',
  `contentType` varchar(50) NOT NULL default 'text/html',
  `pagetitle` varchar(255) NOT NULL default '',
  `longtitle` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `alias` varchar(245) default '',
  `link_attributes` varchar(255) NOT NULL default '' COMMENT 'Link attriubtes',
  `published` int(1) NOT NULL default '0',
  `pub_date` int(20) NOT NULL default '0',
  `unpub_date` int(20) NOT NULL default '0',
  `parent` int(10) NOT NULL default '0',
  `isfolder` int(1) NOT NULL default '0',
  `introtext` text COMMENT 'Used to provide quick summary of the document',  
  `content` mediumtext,
  `richtext` tinyint(1) NOT NULL default '1',
  `template` int(10) NOT NULL default '0',
  `menuindex` int(10) NOT NULL default '0',
  `searchable` int(1) NOT NULL default '1',
  `cacheable` int(1) NOT NULL default '1',
  `createdby` int(10) NOT NULL default '0',
  `createdon` int(20) NOT NULL default '0',
  `editedby` int(10) NOT NULL default '0',
  `editedon` int(20) NOT NULL default '0',
  `deleted` int(1) NOT NULL default '0',
  `deletedon` int(20) NOT NULL default '0',
  `deletedby` int(10) NOT NULL default '0',
  `publishedon` int(20) NOT NULL default '0' COMMENT 'Date the document was published',
  `publishedby` int(10) NOT NULL default '0' COMMENT 'ID of user who published the document',
  `menutitle` varchar(255) NOT NULL DEFAULT '' COMMENT 'Menu title',
  `donthit` tinyint(1) NOT NULL default '0' COMMENT 'Disable page hit count',
  `haskeywords` tinyint(1) NOT NULL default '0' COMMENT 'has links to keywords',
  `hasmetatags` tinyint(1) NOT NULL default '0' COMMENT 'has links to meta tags',
  `privateweb` tinyint(1) NOT NULL default '0' COMMENT 'Private web document',
  `privatemgr` tinyint(1) NOT NULL default '0' COMMENT 'Private manager document',
  `content_dispo` tinyint(1) NOT NULL default '0' COMMENT '0-inline, 1-attachment',
  `hidemenu` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide document from menu',
  `alias_visible` INT(2) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `parent` (`parent`),
  KEY aliasidx (`alias`),
  KEY typeidx (`type`),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
) ENGINE=MyISAM COMMENT='Contains the site document tree.';


CREATE TABLE IF NOT EXISTS `{PREFIX}site_content_metatags` (
  `content_id` int(11) NOT NULL default '0',
  `metatag_id` int(11) NOT NULL default '0',
  KEY `content_id` (`content_id`),
  KEY `metatag_id` (`metatag_id`)
) ENGINE=MyISAM COMMENT='Reference table between meta tags and content';


CREATE TABLE IF NOT EXISTS `{PREFIX}site_htmlsnippets` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default 'Chunk',
  `editor_type` integer NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
  `editor_name` VARCHAR(50) NOT NULL DEFAULT 'none',
  `category` integer NOT NULL DEFAULT '0' COMMENT 'category id',
  `cache_type`	tinyint(1) NOT NULL default '0' COMMENT 'Cache option',
  `snippet` mediumtext,
  `locked` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains the site chunks.';


CREATE TABLE IF NOT EXISTS `{PREFIX}site_keywords` (
  `id` int(11) NOT NULL auto_increment,
  `keyword` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=MyISAM COMMENT='Site keyword list';


CREATE TABLE IF NOT EXISTS `{PREFIX}site_metatags` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `tag` varchar(50) NOT NULL DEFAULT '' COMMENT 'tag name',
  `tagvalue` varchar(255) NOT NULL DEFAULT '',
  `http_equiv` tinyint NOT NULL DEFAULT 0 COMMENT '1 - use http_equiv tag style, 0 - use name',
  PRIMARY KEY(`id`)
) ENGINE=MyISAM COMMENT='Site meta tags';


CREATE TABLE IF NOT EXISTS `{PREFIX}site_modules` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '0',
  `editor_type` integer NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
  `disabled` tinyint NOT NULL DEFAULT '0',
  `category` integer NOT NULL DEFAULT '0' COMMENT 'category id',
  `wrap` tinyint NOT NULL DEFAULT '0',
  `locked` tinyint NOT NULL default '0',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'url to module icon',
  `enable_resource` tinyint NOT NULL DEFAULT '0' COMMENT 'enables the resource file feature',
  `resourcefile` varchar(255) NOT NULL DEFAULT '' COMMENT 'a physical link to a resource file',
  `createdon` integer NOT NULL DEFAULT '0',  
  `editedon` integer NOT NULL DEFAULT '0',
  `guid` varchar(32) NOT NULL DEFAULT '' COMMENT 'globally unique identifier',
  `enable_sharedparams` tinyint NOT NULL DEFAULT '0',
  `properties` text,
  `modulecode` mediumtext COMMENT 'module boot up code',
  PRIMARY KEY(`id`)
) ENGINE=MyISAM COMMENT='Site Modules';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_module_depobj` (
  `id` integer NOT NULL AUTO_INCREMENT,
  `module` integer NOT NULL DEFAULT 0,
  `resource` integer NOT NULL DEFAULT 0,
  `type` integer(2) NOT NULL DEFAULT 0 COMMENT '10-chunks, 20-docs, 30-plugins, 40-snips, 50-tpls, 60-tvs',
  PRIMARY KEY(`id`)
) ENGINE=MyISAM COMMENT='Module Dependencies';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_module_access` (
  `id` integer UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` integer NOT NULL DEFAULT 0,
  `usergroup` integer NOT NULL DEFAULT 0,
  PRIMARY KEY(`id`)
) ENGINE=MyISAM COMMENT='Module users group access permission';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_plugins` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `description` varchar(255) NOT NULL default 'Plugin',
  `editor_type` integer NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
  `category` integer NOT NULL DEFAULT '0' COMMENT 'category id',
  `cache_type` tinyint(1) NOT NULL default '0' COMMENT 'Cache option',
  `plugincode` mediumtext,
  `locked` tinyint(4) NOT NULL default '0',
  `properties` text COMMENT 'Default Properties',  
  `disabled` tinyint NOT NULL DEFAULT '0' COMMENT 'Disables the plugin',
  `moduleguid` varchar(32) NOT NULL default '' COMMENT 'GUID of module from which to import shared parameters',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains the site plugins.';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_plugin_events` (
  `pluginid` INT(10) NOT NULL,
  `evtid` INT(10) NOT NULL default 0,
  `priority` INT(10) NOT NULL default 0 COMMENT 'determines plugin run order',
  PRIMARY KEY ( `pluginid` , `evtid` )
) ENGINE=MyISAM COMMENT='Links to system events';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_snippets` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `description` varchar(255) NOT NULL default 'Snippet',
  `editor_type` integer NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
  `category` integer NOT NULL DEFAULT '0' COMMENT 'category id',
  `cache_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Cache option',
  `snippet` mediumtext,
  `locked` tinyint(4) NOT NULL default '0',
  `properties` text COMMENT 'Default Properties',  
  `moduleguid` varchar(32) NOT NULL default '' COMMENT 'GUID of module from which to import shared parameters',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains the site snippets.';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_templates` (
  `id` int(10) NOT NULL auto_increment,
  `templatename` varchar(100) NOT NULL default '',
  `description` varchar(255) NOT NULL default 'Template',
  `editor_type` integer NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
  `category` integer NOT NULL DEFAULT '0' COMMENT 'category id',
  `icon` varchar(255) NOT NULL default '' COMMENT 'url to icon file',
  `template_type` integer NOT NULL DEFAULT '0' COMMENT '0-page,1-content',
  `content` mediumtext,
  `locked` tinyint(4) NOT NULL default '0',
  `selectable` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains the site templates.';

CREATE TABLE IF NOT EXISTS `{PREFIX}system_eventnames` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL default '',
  `service` tinyint NOT NULL default '0' COMMENT 'System Service number',
  `groupname` varchar(20) NOT NULL default '',
  PRIMARY KEY(`id`)
) ENGINE=MyISAM COMMENT='System Event Names.';

CREATE TABLE IF NOT EXISTS `{PREFIX}system_settings` (
  `setting_name` varchar(50) NOT NULL default '',
  `setting_value` text,
  PRIMARY KEY (`setting_name`)
) ENGINE=MyISAM COMMENT='Contains Content Manager settings.';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_tmplvar_access` (
  `id` int(10) NOT NULL auto_increment,
  `tmplvarid` int(10) NOT NULL default '0',
  `documentgroup` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains data used for template variable access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_tmplvar_contentvalues` (
	`id` int(11) NOT NULL auto_increment,
	`tmplvarid` int(10) NOT NULL default '0' COMMENT 'Template Variable id',
	`contentid` int(10) NOT NULL default '0' COMMENT 'Site Content Id',
	`value` mediumtext,
	PRIMARY KEY  (id),
	KEY idx_tmplvarid (tmplvarid),
	KEY idx_id (contentid),
	FULLTEXT KEY `value_ft_idx` (`value`)
) ENGINE=MyISAM COMMENT='Site Template Variables Content Values Link Table';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_tmplvar_templates` (
	`tmplvarid` int(10) NOT NULL default '0' COMMENT 'Template Variable id',
	`templateid` int(11) NOT NULL default '0',
	`rank` int(11) NOT NULL default '0',
	PRIMARY KEY (`tmplvarid`, `templateid`)
) ENGINE=MyISAM COMMENT='Site Template Variables Templates Link Table';

CREATE TABLE IF NOT EXISTS `{PREFIX}site_tmplvars` (
	`id` INT(11) NOT NULL auto_increment,
	`type` varchar(50) NOT NULL default '',
	`name` varchar(100) NOT NULL default '',
	`caption` varchar(80) NOT NULL default '',
	`description` varchar(255) NOT NULL default '',
	`editor_type` integer NOT NULL DEFAULT '0' COMMENT '0-plain text,1-rich text,2-code editor',
	`category` integer NOT NULL DEFAULT '0' COMMENT 'category id',
	`locked` tinyint(4) NOT NULL default '0',
	`elements` text,
	`rank` int(11) NOT NULL default '0',
	`display` varchar(20) NOT NULL default '' COMMENT 'Display Control',
	`display_params` text COMMENT 'Display Control Properties',
	`default_text` text,
	PRIMARY KEY  (id),
	KEY `indx_rank`(`rank`)
) ENGINE=MyISAM COMMENT='Site Template Variables';

CREATE TABLE IF NOT EXISTS `{PREFIX}user_attributes` (
  `id` int(10) NOT NULL auto_increment,
  `internalKey` int(10) NOT NULL default '0',
  `fullname` varchar(100) NOT NULL default '',
  `role` int(10) NOT NULL default '0',
  `email` varchar(100) NOT NULL default '',
  `phone` varchar(100) NOT NULL default '',
  `mobilephone` varchar(100) NOT NULL default '',
  `blocked` int(1) NOT NULL default '0',
  `blockeduntil` int(11) NOT NULL default '0',
  `blockedafter` int(11) NOT NULL default '0',
  `logincount` int(11) NOT NULL default '0',
  `lastlogin` int(11) NOT NULL default '0',
  `thislogin` int(11) NOT NULL default '0',
  `failedlogincount` int(10) NOT NULL default '0',
  `sessionid` varchar(100) NOT NULL default '',
  `dob` int(10) NOT NULL DEFAULT '0',
  `gender` int(1) NOT NULL DEFAULT '0' COMMENT '0 - unknown, 1 - Male 2 - female',
  `country` varchar(5) NOT NULL default '',
  `street` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(25) NOT NULL default '',
  `zip` varchar(25) NOT NULL default '',
  `fax` varchar(100) NOT NULL default '',
  `photo` varchar(255) NOT NULL default '' COMMENT 'link to photo',
  `comment` text,  
  PRIMARY KEY  (`id`),
  KEY `userid` (`internalKey`)
) ENGINE=MyISAM COMMENT='Contains information about the backend users.';

CREATE TABLE IF NOT EXISTS `{PREFIX}user_messages` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(15) NOT NULL default '',
  `subject` varchar(60) NOT NULL default '',
  `message` text,
  `sender` int(10) NOT NULL default '0',
  `recipient` int(10) NOT NULL default '0',
  `private` tinyint(4) NOT NULL default '0',
  `postdate` int(20) NOT NULL default '0',
  `messageread` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains messages for the Content Manager messaging system.';

CREATE TABLE IF NOT EXISTS `{PREFIX}user_roles` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `frames` int(1) NOT NULL default '0',
  `home` int(1) NOT NULL default '0',
  `view_document` int(1) NOT NULL default '0',
  `new_document` int(1) NOT NULL default '0',
  `save_document` int(1) NOT NULL default '0',
  `publish_document` int(1) NOT NULL default '0',
  `delete_document` int(1) NOT NULL default '0',
  `empty_trash` int(1) NOT NULL default '0',
  `action_ok` int(1) NOT NULL default '0',
  `logout` int(1) NOT NULL default '0',
  `help` int(1) NOT NULL default '0',
  `messages` int(1) NOT NULL default '0',
  `new_user` int(1) NOT NULL default '0',
  `edit_user` int(1) NOT NULL default '0',
  `logs` int(1) NOT NULL default '0',
  `edit_parser` int(1) NOT NULL default '0',
  `save_parser` int(1) NOT NULL default '0',
  `edit_template` int(1) NOT NULL default '0',
  `settings` int(1) NOT NULL default '0',
  `credits` int(1) NOT NULL default '0',
  `new_template` int(1) NOT NULL default '0',
  `save_template` int(1) NOT NULL default '0',
  `delete_template` int(1) NOT NULL default '0',
  `edit_snippet` int(1) NOT NULL default '0',
  `new_snippet` int(1) NOT NULL default '0',
  `save_snippet` int(1) NOT NULL default '0',
  `delete_snippet` int(1) NOT NULL default '0',
  `edit_chunk` int(1) NOT NULL default '0',
  `new_chunk` int(1) NOT NULL default '0',
  `save_chunk` int(1) NOT NULL default '0',
  `delete_chunk` int(1) NOT NULL default '0',
  `empty_cache` int(1) NOT NULL default '0',
  `edit_document` int(1) NOT NULL default '0',
  `change_password` int(1) NOT NULL default '0',
  `error_dialog` int(1) NOT NULL default '0',
  `about` int(1) NOT NULL default '0',
  `file_manager` int(1) NOT NULL default '0',
  `assets_files` int(1) NOT NULL default '0',
  `assets_images` int(1) NOT NULL default '0',
  `save_user` int(1) NOT NULL default '0',
  `delete_user` int(1) NOT NULL default '0',
  `save_password` int(11) NOT NULL default '0',
  `edit_role` int(1) NOT NULL default '0',
  `save_role` int(1) NOT NULL default '0',
  `delete_role` int(1) NOT NULL default '0',
  `new_role` int(1) NOT NULL default '0',
  `access_permissions` int(1) NOT NULL default '0',
  `bk_manager` int(1) NOT NULL DEFAULT 0,
  `new_plugin` int(1) NOT NULL DEFAULT 0,
  `edit_plugin` int(1) NOT NULL DEFAULT 0,
  `save_plugin` int(1) NOT NULL DEFAULT 0,
  `delete_plugin` int(1) NOT NULL DEFAULT 0,
  `new_module` int(1) NOT NULL DEFAULT 0,
  `edit_module` int(1) NOT NULL DEFAULT 0,
  `save_module` int(1) NOT NULL DEFAULT 0,
  `delete_module` int(1) NOT NULL DEFAULT 0,
  `exec_module` int(1) NOT NULL DEFAULT 0,
  `view_eventlog` int(1) NOT NULL DEFAULT 0,
  `delete_eventlog` int(1) NOT NULL DEFAULT 0,
  `manage_metatags` int(1) NOT NULL DEFAULT 0 COMMENT 'manage site meta tags and keywords',
  `edit_doc_metatags` int(1) NOT NULL DEFAULT 0 COMMENT 'edit document meta tags and keywords' ,
  `new_web_user` int(1) NOT NULL default '0',
  `edit_web_user` int(1) NOT NULL default '0',
  `save_web_user` int(1) NOT NULL default '0',
  `delete_web_user` int(1) NOT NULL default '0',
  `web_access_permissions` int(1) NOT NULL default '0',
  `view_unpublished` int(1) NOT NULL default '0',
  `import_static` int(1) NOT NULL default '0',
  `export_static` int(1) NOT NULL default '0',
  `remove_locks` int(1) NOT NULL default '0',
  `display_locks` int(1) NOT NULL default '0',
  `change_resourcetype` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains information describing the user roles.';

CREATE TABLE IF NOT EXISTS `{PREFIX}user_settings` (
  `user` integer NOT NULL,
  `setting_name` varchar(50) NOT NULL default '',
  `setting_value` text,
  PRIMARY KEY ( `user` , `setting_name` ),
  KEY `setting_name` (`setting_name`),
  KEY `user` (`user`)
) ENGINE=MyISAM COMMENT='Contains backend user settings.';

CREATE TABLE IF NOT EXISTS `{PREFIX}web_groups` (
  `id` int(10) NOT NULL auto_increment,
  `webgroup` int(10) NOT NULL default '0',
  `webuser` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE INDEX `ix_group_user` (`webgroup`,`webuser`)
) ENGINE=MyISAM COMMENT='Contains data used for web access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}webgroup_access` (
  `id` int(10) NOT NULL auto_increment,
  `webgroup` int(10) NOT NULL default '0',
  `documentgroup` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM COMMENT='Contains data used for web access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}webgroup_names` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(245) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM COMMENT='Contains data used for web access permissions.';

CREATE TABLE IF NOT EXISTS `{PREFIX}web_user_attributes` (
  `id` int(10) NOT NULL auto_increment,
  `internalKey` int(10) NOT NULL default '0',
  `fullname` varchar(100) NOT NULL default '',
  `role` int(10) NOT NULL default '0',
  `email` varchar(100) NOT NULL default '',
  `phone` varchar(100) NOT NULL default '',
  `mobilephone` varchar(100) NOT NULL default '',
  `blocked` int(1) NOT NULL default '0',
  `blockeduntil` int(11) NOT NULL default '0',
  `blockedafter` int(11) NOT NULL default '0',
  `logincount` int(11) NOT NULL default '0',
  `lastlogin` int(11) NOT NULL default '0',
  `thislogin` int(11) NOT NULL default '0',
  `failedlogincount` int(10) NOT NULL default '0',
  `sessionid` varchar(100) NOT NULL default '',
  `dob` int(10) NOT NULL DEFAULT '0',
  `gender` int(1) NOT NULL DEFAULT '0' COMMENT '0 - unknown, 1 - Male 2 - female',
  `country` varchar(25) NOT NULL default '',
  `street` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(25) NOT NULL default '',
  `zip` varchar(25) NOT NULL default '',
  `fax` varchar(100) NOT NULL default '',
  `photo` varchar(255) NOT NULL default '' COMMENT 'link to photo',
  `comment` text,  
  PRIMARY KEY  (`id`),
  KEY `userid` (`internalKey`)
) ENGINE=MyISAM COMMENT='Contains information for web users.';

CREATE TABLE IF NOT EXISTS `{PREFIX}web_users` (
  `id` int(10) NOT NULL auto_increment,
  `username` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `cachepwd` varchar(100) NOT NULL default '' COMMENT 'Store new unconfirmed password',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `{PREFIX}web_user_settings` (
  `webuser` integer NOT NULL,
  `setting_name` varchar(50) NOT NULL default '',
  `setting_value` text,
  PRIMARY KEY ( `webuser` , `setting_name` ),
  KEY `setting_name` (`setting_name`),
  KEY `webuserid` (`webuser`)
) ENGINE=MyISAM COMMENT='Contains web user settings.';


# upgrade-able[[ - This block of code will be executed during upgrades

# For backward compatibilty with early versions
#::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

ALTER TABLE `{PREFIX}site_content` 
  ADD COLUMN `publishedon` int(20) NOT NULL DEFAULT '0' COMMENT 'Date the document was published' AFTER `deletedby`;

ALTER TABLE `{PREFIX}site_content`
  ADD COLUMN `publishedby` int(10) NOT NULL DEFAULT '0' COMMENT 'ID of user who published the document' AFTER `publishedon`;

ALTER TABLE `{PREFIX}site_content`
  ADD COLUMN `link_attributes` varchar(255) NOT NULL DEFAULT '' COMMENT 'Link attriubtes' AFTER `alias`;

ALTER TABLE `{PREFIX}site_content`
  ADD COLUMN `alias_visible` INT(2) NOT NULL DEFAULT '1' COMMENT 'Hide document from alias path';

ALTER TABLE `{PREFIX}site_htmlsnippets`
  ADD COLUMN `editor_name` VARCHAR(50) NOT NULL DEFAULT 'none' AFTER `editor_type`;

ALTER TABLE `{PREFIX}site_plugin_events`
  ADD COLUMN `priority` INT(10) NOT NULL default '0' COMMENT 'determines the run order of the plugin' AFTER `evtid`;

ALTER TABLE `{PREFIX}site_templates`
  ADD COLUMN `selectable` TINYINT(4) NOT NULL DEFAULT '1' AFTER `locked`;

ALTER TABLE `{PREFIX}site_tmplvar_templates`
  ADD COLUMN `rank` integer(11) NOT NULL DEFAULT '0' AFTER `templateid`;

ALTER TABLE `{PREFIX}user_attributes`
  ADD COLUMN `street` varchar(255) NOT NULL DEFAULT '' AFTER `country`;

ALTER TABLE `{PREFIX}user_attributes`
  ADD COLUMN `city` varchar(255) NOT NULL DEFAULT '' AFTER `street`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `edit_chunk`          INT(1) NOT NULL DEFAULT '0' AFTER `delete_snippet`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `new_chunk`           INT(1) NOT NULL DEFAULT '0' AFTER `edit_chunk`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `save_chunk`          INT(1) NOT NULL DEFAULT '0' AFTER `new_chunk`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `delete_chunk`        INT(1) NOT NULL DEFAULT '0' AFTER `save_chunk`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `empty_trash`         INT(1) NOT NULL DEFAULT '0' AFTER `delete_document`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `view_unpublished`    INT(1) NOT NULL DEFAULT '0' AFTER `web_access_permissions`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `import_static`       INT(1) NOT NULL DEFAULT '0' AFTER `view_unpublished`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `export_static`       INT(1) NOT NULL DEFAULT '0' AFTER `import_static`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `remove_locks`        INT(1) NOT NULL DEFAULT '0' AFTER `export_static`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `display_locks`       INT(1) NOT NULL DEFAULT '0' AFTER `remove_locks`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `publish_document`    INT(1) NOT NULL DEFAULT '0' AFTER `save_document`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `change_resourcetype` INT(1) NOT NULL DEFAULT '0' AFTER `remove_locks`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `assets_images`       INT(1) NOT NULL DEFAULT '1' AFTER `file_manager`;

ALTER TABLE `{PREFIX}user_roles`
  ADD COLUMN `assets_files`        INT(1) NOT NULL DEFAULT '1' AFTER `assets_images`;

ALTER TABLE `{PREFIX}web_user_attributes`
  ADD COLUMN `street` varchar(255) NOT NULL DEFAULT '' AFTER `country`;

ALTER TABLE `{PREFIX}web_user_attributes`
  ADD COLUMN `city` varchar(255) NOT NULL DEFAULT '' AFTER `street`;

# Set the private manager group flag

UPDATE {PREFIX}documentgroup_names AS dgn
  LEFT JOIN {PREFIX}membergroup_access AS mga ON mga.documentgroup = dgn.id
  LEFT JOIN {PREFIX}webgroup_access AS wga ON wga.documentgroup = dgn.id
  SET dgn.private_memgroup = (mga.membergroup IS NOT NULL),
      dgn.private_webgroup = (wga.webgroup IS NOT NULL);


UPDATE `{PREFIX}site_plugins` SET `disabled` = '1' WHERE `name` IN ('Bottom Button Bar');

UPDATE `{PREFIX}site_plugins` SET `disabled` = '1' WHERE `name` IN ('Inherit Parent Template');

UPDATE `{PREFIX}system_settings` SET `setting_value` = '0' WHERE `setting_name` = 'validate_referer' AND `setting_value` = '00';

# start related to #MODX-1321

UPDATE `{PREFIX}site_content` SET `type`='reference', `contentType`='text/html' WHERE `type`='' AND `content` REGEXP '^https?://([-\w\.]+)+(:\d+)?/?';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/xml' WHERE `type`='' AND `alias` REGEXP '\.(rss|xml)$';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/javascript' WHERE `type`='' AND `alias` REGEXP '\.js$';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/css' WHERE `type`='' AND `alias` REGEXP '\.css$';

UPDATE `{PREFIX}site_content` SET `type`='document', `contentType`='text/html' WHERE `type`='';

ALTER TABLE `{PREFIX}active_users`
  MODIFY COLUMN `ip` varchar(50) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}documentgroup_names`
  MODIFY COLUMN `name` varchar(245) NOT NULL default '';

ALTER TABLE `{PREFIX}event_log`
  MODIFY COLUMN `source` varchar(50) NOT NULL DEFAULT '',
  MODIFY COLUMN `description` text;

ALTER TABLE `{PREFIX}categories`
  MODIFY COLUMN `category` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}manager_users`
  MODIFY COLUMN `username` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `{PREFIX}membergroup_names`
 MODIFY COLUMN `name` varchar(245) NOT NULL default '';

ALTER TABLE `{PREFIX}site_content`
  MODIFY COLUMN `pagetitle` varchar(255) NOT NULL default '',
  MODIFY COLUMN `alias` varchar(245) default '',
  MODIFY COLUMN `introtext` text COMMENT 'Used to provide quick summary of the document',
  MODIFY COLUMN `content` mediumtext,
  MODIFY COLUMN `menutitle` varchar(255) NOT NULL DEFAULT '' COMMENT 'Menu title',
  MODIFY COLUMN `template` int(10) NOT NULL default '0';

ALTER TABLE `{PREFIX}site_htmlsnippets`
  MODIFY COLUMN `snippet` mediumtext;

ALTER TABLE `{PREFIX}site_module_access`
  MODIFY COLUMN `module` int(11) NOT NULL DEFAULT '0',
  MODIFY COLUMN `usergroup` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `{PREFIX}site_module_depobj`
  MODIFY COLUMN `module` int(11) NOT NULL DEFAULT '0',
  MODIFY COLUMN `resource` int(11) NOT NULL DEFAULT '0';

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

ALTER TABLE `{PREFIX}site_plugin_events`
  MODIFY COLUMN `evtid` int(10) NOT NULL DEFAULT '0';

ALTER TABLE `{PREFIX}site_plugins`
  MODIFY COLUMN `properties` text COMMENT 'Default Properties',
  MODIFY COLUMN `plugincode` mediumtext,
  MODIFY COLUMN `moduleguid` varchar(32) NOT NULL DEFAULT '' COMMENT 'GUID of module from which to import shared parameters';

ALTER TABLE `{PREFIX}site_snippets`
  MODIFY COLUMN `properties` text COMMENT 'Default Properties',
  MODIFY COLUMN `snippet` mediumtext,
  MODIFY COLUMN `moduleguid` varchar(32) NOT NULL DEFAULT '' COMMENT 'GUID of module from which to import shared parameters';

ALTER TABLE `{PREFIX}site_templates`
 MODIFY COLUMN `icon` varchar(255) NOT NULL default '' COMMENT 'url to icon file',
 MODIFY COLUMN `content` mediumtext;

ALTER TABLE `{PREFIX}site_tmplvar_contentvalues`
 MODIFY COLUMN `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id',
 MODIFY COLUMN `value` mediumtext;

ALTER TABLE `{PREFIX}site_tmplvar_templates`
  MODIFY COLUMN `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id';

ALTER TABLE `{PREFIX}site_tmplvars`
 MODIFY COLUMN `name` varchar(50) NOT NULL default '',
 MODIFY COLUMN `elements` text,
 MODIFY COLUMN `display` varchar(20) NOT NULL DEFAULT '' COMMENT 'Display Control',
 MODIFY COLUMN `display_params` text COMMENT 'Display Control Properties',
 MODIFY COLUMN `default_text` text;

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
 MODIFY COLUMN `comment` text;

ALTER TABLE `{PREFIX}user_messages` MODIFY COLUMN `message` text;

ALTER TABLE `{PREFIX}user_settings` MODIFY COLUMN `setting_value` text;

ALTER TABLE `{PREFIX}web_users`
 MODIFY COLUMN `username` varchar(100) NOT NULL DEFAULT '',
 MODIFY COLUMN `cachepwd` varchar(100) NOT NULL DEFAULT '' COMMENT 'Store new unconfirmed password' AFTER `password`;

ALTER TABLE `{PREFIX}web_user_settings` MODIFY COLUMN `setting_value` text;

ALTER TABLE `{PREFIX}web_user_attributes`
  MODIFY COLUMN `country` varchar(25) NOT NULL DEFAULT '',
  MODIFY COLUMN `state` varchar(25) NOT NULL DEFAULT '',
  MODIFY COLUMN `zip` varchar(25) NOT NULL DEFAULT '',
  MODIFY COLUMN `fax` varchar(100) NOT NULL DEFAULT '',
  MODIFY COLUMN `photo` varchar(255) NOT NULL DEFAULT '' COMMENT 'link to photo',
  MODIFY COLUMN `comment` text;

ALTER TABLE `{PREFIX}webgroup_names`
 MODIFY COLUMN `name` varchar(245) NOT NULL default '';

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

ALTER TABLE `{PREFIX}site_tmplvar_contentvalues` ADD FULLTEXT `value_ft_idx` (`value`);

ALTER TABLE `{PREFIX}site_tmplvar_templates` DROP INDEX `idx_tmplvarid`;

ALTER TABLE `{PREFIX}site_tmplvar_templates` DROP INDEX `idx_templateid`;

ALTER TABLE `{PREFIX}site_tmplvar_templates` DROP PRIMARY KEY;

ALTER TABLE `{PREFIX}site_tmplvar_templates` ADD PRIMARY KEY (`tmplvarid`, `templateid`);

ALTER TABLE `{PREFIX}member_groups` ADD UNIQUE INDEX `ix_group_member` (`user_group`,`member`);

ALTER TABLE `{PREFIX}web_groups` ADD UNIQUE INDEX `ix_group_user` (`webgroup`,`webuser`);

# ]]upgrade-able