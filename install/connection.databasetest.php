<?php

$host = $_POST['host'];
$uid  = $_POST['uid'];
$pwd  = $_POST['pwd'];
$installMode = $_POST['installMode'];

require_once('functions.php');
require_once('lang.php');
$output = $_lang['status_checking_database'];
if (!$conn = @ mysql_connect($host, $uid, $pwd))
{
	$output .= span_fail($_lang['status_failed']);
}
else
{
	if(get_magic_quotes_gpc())
	{
		$_POST['database_name']              = stripslashes($_POST['database_name']);
		$_POST['tableprefix']                = stripslashes($_POST['tableprefix']);
		$_POST['database_collation']         = stripslashes($_POST['database_collation']);
		$_POST['database_connection_method'] = stripslashes($_POST['database_connection_method']);
	}
	$database_name = modx_escape($_POST['database_name']);
	$database_name = str_replace('`', '', $database_name);
	$tableprefix   = modx_escape($_POST['tableprefix']);
	$database_collation = modx_escape($_POST['database_collation']);
	$database_connection_method = modx_escape($_POST['database_connection_method']);
	$tbl_site_content = "{$database_name}.`{$tableprefix}site_content`";
	
	if (!@ mysql_select_db($database_name, $conn))
	{
		// create database
		$database_charset = substr($database_collation, 0, strpos($database_collation, '_'));
		
		if (function_exists('mysql_set_charset'))
		{
			mysql_set_charset($database_charset);
		}
		$query = "CREATE DATABASE `{$database_name}` CHARACTER SET {$database_charset} COLLATE {$database_collation}";
		
		if(!@ mysql_query($query)) $output .= span_fail($_lang['status_failed_could_not_create_database']);
		else                       $output .= span_pass($_lang['status_passed_database_created']);
	}
	elseif(($installMode == 0) && (@ mysql_query("SELECT COUNT(id) FROM {$tbl_site_content}")))
		$output .= span_fail($_lang['status_failed_table_prefix_already_in_use']);
	else
		$output .= span_pass($_lang['status_passed']);
}

echo $output;

function span_pass($str)
{
	return '<span id="database_pass" style="color:#388000;">' . $str . '</span>';
}

function span_fail($str)
{
	return '<span id="database_fail" style="color:#FF0000;">' . $str . '</span>';
}
