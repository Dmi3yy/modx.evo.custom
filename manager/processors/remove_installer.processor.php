<?php
/**
 * Installer remover processor
 * --------------------------------
 * This little script will be used by the installer to remove
 * the install folder from the web root after an install. Having
 * the install folder present after an install is considered a
 * security risk
 *
 * This file is mormally called from the installer
 *
 */
define('MODX_API_MODE', true);
$base_path = str_replace('\\','/',realpath('../../')) . '/';
include_once("{$base_path}index.php");

$install_dir = "{$base_path}install";
if(isset($_GET['rminstall']))
{
	if(is_dir($install_dir))
	{
		if(!rmdirRecursive($install_dir))
		{
			$msg = 'An error occured while attempting to remove the install folder';
			echo "<script>alert('{$msg}');</script>";
		}
	}
}
echo "<script>window.location='../index.php?a=2';</script>";

// rmdirRecursive - detects symbollic links on unix
function rmdirRecursive($path,$followLinks=false)
{
	$files = scandir($path) ;
	foreach ($files as $entry)
	{
		if (is_file("{$path}/{$entry}") || ((!$followLinks) && is_link("{$path}/{$entry}")))
		{
			@unlink( "{$path}/{$entry}" );
		}
		elseif (is_dir("{$path}/{$entry}") && $entry!='.' && $entry!='..')
		{
			rmdirRecursive("{$path}/{$entry}"); // recursive
		}
	}
	return @rmdir($path);
}
