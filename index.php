<?php
/*
*************************************************************************
	MODx Content Management System and PHP Application Framework 
	Managed and maintained by Raymond Irving, Ryan Thrash and the
	MODx community
*************************************************************************
	MODx is an opensource PHP/MySQL content management system and content
	management framework that is flexible, adaptable, supports XHTML/CSS
	layouts, and works with most web browsers, including Safari.

	MODx is distributed under the GNU General Public License	
*************************************************************************

	MODx CMS and Application Framework ("MODx")
	Copyright 2005 and forever thereafter by Raymond Irving & Ryan Thrash.
	All rights reserved.

	This file and all related or dependant files distributed with this filie
	are considered as a whole to make up MODx.

	MODx is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	MODx is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with MODx (located in "/assets/docs/"); if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

	For more information on MODx please visit http://modx.com/
	
**************************************************************************
    Originally based on Etomite by Alex Butter
**************************************************************************
*/	

/**
 * Initialize Document Parsing
 * -----------------------------
 */

// get start time
$mtime = explode(' ',microtime());
$tstart = $mtime[1] + $mtime[0];
$mstart = memory_get_usage();
$cwd = str_replace('\\','/',dirname(__FILE__)) . '/';
if(file_exists("{$cwd}autoload.php")) include_once("{$cwd}autoload.php");

// harden it
require_once("{$cwd}manager/includes/protect.inc.php");
require_once("{$cwd}manager/includes/initialize.inc.php");
// get the required includes
if(!isset($database_type))
{
	$conf_path = "{$cwd}manager/includes/config.inc.php";
	if(file_exists($conf_path)) include_once($conf_path);
	// Be sure config.inc.php is there and that it contains some important values
	if((!isset($lastInstallTime) || $lastInstallTime===NULL) && !isset($database_type))
	{
		show_install();
		exit;
	}
}

set_parser_mode();
startCMSSession();

// initiate a new document parser
include_once(MODX_MANAGER_PATH.'includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$etomite = &$modx; // for backward compatibility

$modx->tstart = $tstart;
$modx->mstart = $mstart;

// execute the parser if index.php was not included
if(!MODX_API_MODE) $modx->executeParser();
