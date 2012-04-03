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
		<span class="version">MODx Cache Extender 0.4b for MODx Evolution 1.0.5, 31.03.2011 by thebat053, email denis053@gmail.com</span>
        <div id="mainheader">
        	<h1 class="pngfix" id="logo"><span>MODx Cache Extender</span></h1>
        </div>
    </div>

</div>
<!-- end header -->

<div id="contentarea">
    <div class="container_12">        
        <!-- start content -->
<?php
	class CacheExtenderInstaller {
		var $revision = '2';
		var $cacheMode = 'part';
		var $modifyFlag;
		var $permission;
		var $silent = false;
		var $permissionsBase = array();
		var $errors = false;

		function __construct(){
			$this->engine();
		}
		
		function engine(){
//			echo(phpversion());
			echo("<h2>CacheExtender v0.4b for MODx Evolution 1.0.5<br />By thebat053, denis053@gmail.com</h2>");
//			$this->basicInstall();
//				$this->clearCache();
//			return;
//			$this->uninstall();
//			return;
			if(isset($_GET['action'])){
				switch($_GET['action']){
					case 'basic_install':
						echo("<span class='green'>Performing basic install...</span><br />");
						$this->silent = true;
						$this->basicInstall();
						if(!$this->errors){
							$this->silent = false;
							$this->basicInstall();
						}
						break;
					case 'advanced_install':
						echo("<span class='green'>Performing advanced install...</span><br />");
						$this->silent = true;
						$this->basicInstall();
						$this->advancedInstall();
						if(!$this->errors){
							$this->silent = false;
							$this->basicInstall();
							$this->advancedInstall();
						}
						break;							
					case 'full_install':
						echo("<span class='green'>Performing full install...</span><br />");
						$this->cacheMode = 'full';
						$this->silent = true;
						$this->basicInstall();
						$this->advancedInstall();
						$this->fullInstall();
						if(!$this->errors){
							$this->silent = false;
							$this->basicInstall();
							$this->advancedInstall();
							$this->fullInstall();
						}
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
<div class="list1"><input type="radio" name="action" value="basic_install"></div><div class="list2"><h3>Basic Install</h3>
<p>Quick basic install.<br />
Cache Extender will patch just one file of MODx in this type of installation.<br />
With a large number of pages memory size used by MODx decreases by about three times.<br />
<a onclick="ShowHide(\'basic\');return false;" href="#">View actions for manual installing</a></p>
<div id="basic" style="display:none;">
<p><b>Patch file:</b> <i>../processors/cache_sync.class.processor.php</i><br />
<b>Path actions</b><br />
In function <i>buildCache</i> <b>adding call</b> to <i>processDocumentCacheExtended</i>.<br />
<b>Adding function:</b> <i>getParentsCacheExtended</i><br />
<b>Adding function:</b> <i>processDocumentCacheExtended</i><br />
Cache Extender work mode will be set to <b>"part"</b>.
<br /><br /></p></div>
</div>
<div class="list1"><input type="radio" name="action" value="advanced_install"></div><div class="list2"><h3>Advanced Install</h3>
<p>Advanced Install.<br />
Cache Extender will proceed all actions of a quick installation and some other actions.
This type of installation decreases memory use and improve performance.<br />
<a onclick="ShowHide(\'advanced\');return false;" href="#">View actions for manual installing</a></p>
<div id="advanced" style="display:none;">
<p><b>Patch file:</b> <i>../includes/document.parser.class.inc.php</i><br />
<b>Patch actions</b><br />
<b>Replacing function:</b> <i>getChildIds</i><br />
<b>Replacing function:</b> <i>rewriteUrls</I><br />
<br /></p></div> 
</div>

<div class="list1"><input type="radio" name="action" value="full_install"></div><div class="list2"><h3>Expert Install</h3>
<p>Expert Install.<br />
Cache Extender will proceed all actions of a quick and advanced installations and some other actions.<br />
This type of installation gets almost no memory use in cases of large number of pages. 
It improves performance in several times with compare of other types of installations. 
Cache Extender will scan <i>assets</i> and <i>manager</i> directories and patch all snippets and plugins that he can. <br />
<a onclick="ShowHide(\'full\');return false;" href="#">View actions for manual installing</a></p>
<div id="full" style="display:none;">
<p><b>Patch actions</b><br />
<b>Scanning</b> <i>/manager</i> and <i>/assets</i> folders for any php snippets and modules.<br />
<b>Patching</b><br />
<b>Patching Method:</b><br />
<b>Replace</b> all constructions like:<br />
<i>function getChildIDs($IDs, $depth){</i><br /><br />
<b>with</b>:<br />
<i>function getChildIDs($IDs, $depth){<br />
return $modx->getChildIds($IDs, $depth);<br /><br /></i>
<b>Replace</b> all calls array_.... (<i>array_search</i>, <i>array_key_exists</i> e.t.c.) like:<br />
<i>$childKey= array_search($childId, $modx->documentListing);</i><br /><br />
<b>with</b>:<br />
<i>$childKey= $modx->documentListing->array_search($childId, $modx->documentListing);</i><br /><br />
<b>All</b> replacing calls must have <b>$modx->documentListing</b>!<br /><br />
Cache Extender work mode will be set to <b>"full"</b>.
</p></div>
<br /></div>

<div class="list1"><input type="radio" name="action" value="uninstall"></div><div class="list2"><h3>Uninstall</h3>
<p>Uninstall.<br />
Cache Extender will uninstall all files and cancel all changes.<br />
<a onclick="ShowHide(\'uninstall\');return false;" href="#">View actions for manual uninstalling</a></p>
<div id="uninstall" style="display:none;">
<p><b>Uninstall actions</b><br />
<b>Replacing</b> all changed files from backups like </i>cache_sync.class.processor.cacheextender.backup.php</i>.<br />
</p>
</div></div>
<div class="clear">&nbsp;</div>
<input type="submit" value="Proceed selected">
</form>
					');
			}
			$this->footer();
		}
		
		function basicInstall(){
			$this->msg('patching_core');
			$this->patchFile('../processors/cache_sync.class.processor.php', array($this, 'patchCacheSync'));
			if(!$this->silent){
				if(!@copy('files/cache_sync.create.class.php', '../processors/cache_sync.create.class.php')){
					$this->msg('cant_write_file', '../processors/cache_sync.create.class.php');
					$this->uninstall();
					return false;
				}
				if(!@copy('files/cache_sync.wrapper.class.php', '../processors/cache_sync.wrapper.class.php')){
					$this->msg('cant_write_file', '../processors/cache_sync.wrapper.class.php');
					$this->uninstall();
					return false;
				}
			}
			return true;
		}

		function advancedInstall(){
			$this->patchFile('../includes/document.parser.class.inc.php', array($this, 'patchDocumentParser'));
		}

		function fullInstall(){
			$this->msg('scan_stuff');

			$this->patchFiles('../../assets/', array($this, 'patchSnippetsFullInstall'));

			//patching document.parser.class.inc.php for array_....
			$filename = '../includes/document.parser.class.inc.php';
			$this->msg('patching', $filename);
			if(!$file = @file_get_contents($filename)){
				$this->msg('cant_open_file', $filename);
				$this->uninstall();
				return false;
			}
			$this->modifyFlag = false;
			$file = $this->processArray_($file, '\$this->documentListing');
			if(!$this->modifyFlag)
				$this->msg('already_patched', $filename); //this file already patched
			else {
				if(!is_writable($filename)) $this->checkPermissions($filename);
				if(!$this->silent){
					if(!@file_put_contents($filename, $file)){
						$this->msg('cant_write_file', $filename);
						$this->uninstall();
						return false;
					}
				}
				$this->msg('patching_ok', $filename);
			}
//			$file = preg_replace('/\$cacheMode\s*=\s*\'(.*)\'\s*;/i', 'full', $file);
		}

		function patchFile($filename, $patchfunc){
			$this->msg('patching', $filename);
			$backupname = preg_replace("/(.php)$/i", ".cacheextender.backup$1", $filename);
			if(!file_exists($filename)){
				$this->msg('file_not_found', $filename, true);
				$this->uninstall();
				return false;
			}
			if(!file_exists($backupname)){
				$this->msg('creating_backup', $backupname);
				if(!is_writable($this->getDirName($backupname))) $this->checkPermissions($this->getDirName($backupname));
				if(!$this->silent){
					if(!@copy($filename, $backupname)){
						$this->msg('cant_create_backup', $backupname);
						$this->uninstall();
						return false;
					}
				}
			}
			if(!$file = @file_get_contents($filename)){
				$this->msg('cant_open_file', $filename);
				$this->uninstall();
				return false;
			}
			if(preg_match("/CacheExtender[\s]+revision:\s*([0-9]+)/i", $file, $revision)){
				if((int)$revision[1]){
					$this->msg('already_patched', $filename); //this file already patched
					return false;
				}
			}
			$file = call_user_func($patchfunc, $filename, $file);
			$file = $this->setRevision($file);
			if(!is_writable($filename)) $this->checkPermissions($filename);
			if(!$this->silent){
				if(!@file_put_contents($filename, $file)){
					$this->msg('cant_write_file', $filename);
					$this->uninstall();
					return false;
				}
			}
			$this->msg('patching_ok', $filename);
		}

		function patchFiles($dir, $patchfunc){
        	if($dir[strlen($dir)-1] != '/')
            	$dir .= '/';
        	$nDir = @opendir($dir);
        	if(!$nDir)
				$this->msg('cant_scan_directory', $dir);
			else {
				while($filep = readdir($nDir)){
            		if($filep != '.' && $filep != '..'){
                		$filename = $dir.$filep;
						if(!is_dir($filename)){
							if(preg_match('/.php$/i', $filename) && !preg_match('/cacheextender.backup.php$/i', $filename)){
								if(!$file = @file_get_contents($filename)){
									$this->msg('cant_open_file', $filename);
									$this->uninstall();
									return false;
								}
								if(preg_match("/CacheExtender[\s]+revision:\s*([0-9]+)/i", $file, $revision)){
									if((int)$revision[1]){
										$this->msg('already_patched', $filename); //this file already patched
										return false;
									}
								}
								if($file = call_user_func($patchfunc, $filename, $file)){
									$this->msg('patching', $filename);
									$backupname = preg_replace("/(.php)$/i", ".cacheextender.backup$1", $filename);
									if(!file_exists($backupname)){
										$this->msg('creating_backup', $backupname);
										if(!is_writable($this->getDirName($backupname))) $this->checkPermissions($this->getDirName($backupname));
										if(!$this->silent){
											if(!@copy($filename, $backupname)){
												$this->msg('cant_create_backup', $backupname);
												$this->uninstall();
												return false;
											}
										}
									}
									$file = $this->setRevision($file);
									if(!is_writable($filename)) $this->checkPermissions($filename);
									if(!$this->silent){
										if(!@file_put_contents($filename, $file)){
											$this->msg('cant_write_file', $filename);
											$this->uninstall();
											return false;
										}
									}
									$this->msg('patching_ok', $filename);
								}
							}
						} else {
                    		$this->patchFiles($filename, $patchfunc);
                		}
					}
				}
        		closedir($nDir);
			}
//        	return $file;
		}


		function patchCacheSync($filename, $file){ //patch cache_sync.class.processor.php
			$file = $this->insertFile($filename, $file, 'synccache', 'files/cache_sync.class.processor.insert.php');
			preg_match('/\$tmpPath[\s]*=[\s]*\'\'[\s]*;/i', $file, $match);
			if(!$pos = strpos($file, $match[0])){
				$msg('file_content_mismatch', $filename);
				$this->uninstall();
				return false;
			}
			//$file = substr($file, 0, $pos - 1).''.substr($file, $pos -1);
			$count = 0;
			$flag = false;
			$pos1 = $pos;
			while($count > 0 || $flag == false){
				$symb = substr($file, $pos1, 1);
				if($symb == '{'){
					$count++;
					$flag = true;
				}
				if($symb == '}')
					$count--;
				$pos1++;
				if($pos1 >= strlen($file)){
					$this->msg('file_content_mismatch', $filename);
					$this->uninstall();
					return false;
				}
			}
			$file = substr($file, 0, $pos1).'*/'.substr($file, $pos1);
			$file = substr($file, 0, $pos)."\n/*	Modified by thebat053\n*	CacheExtender\n*	CacheExtender revision:".$this->revision."\n*/\n\$this->processDocumentCacheExtended(\$modx, \$tmpPHP, \$config);\n/*".substr($file, $pos);
			$file = str_replace('current_cache_mode', $this->cacheMode, $file);
			return $file;
		}

		function patchDocumentParser($filename, $file){ //patch document.parser.class.inc.php
			$file = $this->commentFunction($filename, $file, 'DocumentParser', array('getChildIds', 'rewriteUrls'));
			$file = $this->insertFile($filename, $file, 'DocumentParser', 'files/document.parser.class.inc.insert.php');
			return $file;
//			$file = $this->commentFunction($filename $file, ''
//			preg_match('');
		}

		function patchSnippetsFullInstall($filename, $file){
//			$this->msg('file inside:', $filename);
			$this->modifyFlag = false;

			// process getchildIds
			if(!preg_match('/function[\s]+getchildids\s*\(.*(\$\w+).*,.*(\$\w+).*,.*(\$\w+).*\)/i', $file, $match)) 
				if(!preg_match('/function[\s]+getchildids\s*\(.*(\$\w+).*,.*(\$\w+).*\)/i', $file, $match))
					$match = null;
			if($match){
				$fields = array();
				$fields[0] = $match[1];
				$fields[1] = $match[2];
				if(isset($match[3])){
					$fields[2] = $match[3];
					if(stripos($match[3], 'parents')) //catalogView compatibility
						$fields[3] = $match[3];
				}
				if(stripos($match[2], 'child')){
					$fields[1] = -1;
					$fields[2] = $match[2];
				}
				$pos = strpos($file, $match[0]);
				if($pos){
					$pos = strpos($file, '{', $pos);
					if($pos){
						if(stripos($fields[0], 'id')){
							$fieldstr = '';
							$symb = '';
							foreach($fields as $field){
								$fieldstr .= $symb.$field;
								$symb=', ';
							}
							$file = substr($file, 0, $pos+1)."\nglobal \$modx; return \$modx->getChildIds(".$fieldstr."); // modified by thebat053, CacheExtender revision:".$this->revision." \n".substr($file, $pos+1);
//							echo($file);
							$this->modifyFlag = true;
						}
					}
				}
			}
			// process array_
			$file = $this->processArray_($file, '\$modx->documentListing');
			if($this->modifyFlag)
				return $file;
			else
				return null;
		}

		function processArray_($file, $modxPointer){
			while(preg_match('/.*(?<!'.$modxPointer.'->)((?:array_key_exists)|(?:array_search)|(?:array_values)|(?:array_keys)|(?:array_push)|(?:array_shift)|(?:array_pop))\s*\((.*'.$modxPointer.'.*)\)/i', $file, $match)){
				$matchstr = str_replace($match[1], str_replace('\\', '', $modxPointer).'->'.$match[1], $match[0]);
				$file = str_replace($match[0], $matchstr, $file); //.'// CacheExtender revision:1 '
				$this->modifyFlag = true;
			}
			return $file;
		}
		
		function insertFile($filename, $file, $class, $sourceName){
			if(!$source = @file_get_contents($sourceName)){
				$this->msg('cant_open_file', $sourceName);
				$this->uninstall();
				return false;
			}
			if(!preg_match("/class[\s]+".$class."\s*{/i", $file, $match)){
				$this->msg('file_content_mismatch', $filename);
				$this->uninstall();
				return false;
			}
			$pos = strpos($file, $match[0]) + strlen($match[0]);
			$count = 1;
			while($count > 0){
				$symb = substr($file, $pos, 1);
				if($symb == '{')
					$count++;
				if($symb == '}')
					$count--;
				$pos++;
				if($pos >= strlen($file)){
					$this->msg('file_content_mismatch', $filename);
					$this->uninstall();
					return false;
				}
			}
			$file = substr($file, 0, $pos - 1).$source.substr($file, $pos -1);
			return $file;
		}

		function commentFunction($filename, $file, $class, $func){
			foreach($func as $function){
				if(!preg_match("/class[\s]+".$class."\s*{/is", $file, $match)){
					$this->msg('file_content_mismatch', $filename);
					$this->uninstall();
					return false;
				}
				$pos = $pos1 = strpos($file, $match[0]) + strlen($match[0]);
				$count = 1;
				$flag = true;
				while($count > 0 || $flag == false){
					$symb = substr($file, $pos, 1);
					if($symb == '{'){
						$flag = true;
						$count++;
					}
					if($symb == '}')
						$count--;
					$pos++;
					if($pos >= strlen($file)){
						$this->msg('file_content_mismatch', $filename);
						$this->uninstall();
						return false;
					}
				}
				$str = substr($file, $pos1, $pos-$pos1);
				if(!preg_match("/function[\s]+".$function."\s*\(.*\)/i", $str, $match)){
					$this->msg('file_content_mismatch', $filename);
					$this->uninstall();
					return false;
				}
				if(!$pos = strpos($str, $match[0])){
					$this->msg('file_content_mismatch', $filename);
					$this->uninstall();
					return false;
				}
				$pos = $pos1 = $pos1 + $pos;
				$count = 0;
				$flag = false;
				while($count > 0 || $flag == false){
					$symb = substr($file, $pos, 1);
					if($symb == '{'){
						$flag = true;
						$count++;
					}
					if($symb == '}')
						$count--;
					$pos++;
					if($pos >= strlen($file)){
						$this->msg('file_content_mismatch', $filename);
						$this->uninstall();
						return false;
					}
				}
				$file = substr($file, 0, $pos)."*/".substr($file, $pos);
				$file = substr($file, 0, $pos1)."\n/*	Modified by thebat053\n*	CacheExtender\n*	CacheExtender revision:".$this->revision."\n*/\n/*".substr($file, $pos1);				
			}
			return $file;
		}

		function checkDir($filename){
			preg_match('/.*\//', $filename, $match);
			$this->permission = decoct(@fileperms($match[0]));
			chmod ($match[0], 0777);
//			print_r($this->permission);
//			die();
//			$this->permission = 
		}
		
		function uncheckDir($param, $filename){
			preg_match('/.*\//', $filename, $match);
			chmod ($match[0], $this->permission);
			return $param;
		}

		function checkFile($filename){
			$this->permission = decoct(@fileperms($match[0]));
			@chmod ($match[0], 0777);
		}
		
		function uncheckFile($param, $filename){
			@chmod ($match[0], $this->permission);
			return $param;
		}

		function checkPermissions($path){
			if(!$path) {
				$this->msg('');
				return;
			}
			$this->errors = true;
			if(in_array($path, $this->permissionsBase)) return;
			array_push($this->permissionsBase, $path);
			if(substr($path, strlen($path)-1, 1) == '/')
				$this->msg('set_permissions_directory', $path, true);
			else
				$this->msg('set_permissions_file', $path, true);
		}

		function getDirName($filename){
			preg_match('/.*\//', $filename, $match);
			return $match[0];
		}

		function msg($msg, $param = null, $forced = false){
			switch($msg){
				case "patching_core":
					$msg = "<b>Patching Core.</b>";
					break;
				case "cant_write_file":
					$msg = "<span class='red'>Error: Cant' write file: </span><param><br /><span class='red'>Check permissions!</span>";
					break;
				case "scan_stuff":
					$msg = "<b>Scan Snippets and Modules.</b>";
					break;
				case "patching":
					$msg = "Patching: <param>";
					break;
				case "cant_open_file":
					$msg = "<span class='red'>Error: Can't open file: </span><param><br /><span class='red'>Check permissions!</span>";
					break;
				case "already_patched":
					$msg = "File already patched: <param>";
					break;
				case "patching_ok":
					$msg = "<span class='green'>Patching successfull:</span> <param>";
					break;
				case "file_not_found":
					$msg = "<span class='red'>Error: File not found: </span><param>";
					break;
				case "creating_backup":
					$msg = "Creating backup: <param>";
					break;
				case "cant_create_backup":
					$msg = "<span class='red'>Error: Can't create backup: </span><param><br /><span class='red'>Check directory permissions!</span>";
					break;
				case "cant_scan_directory":
					$msg = "Cant' scan directory: <param>";
					break;
				case "file_content_mismatch":
					$msg = "<span class='red'>Error: File content mismatch! Possible incompatible MODx version!</span>";
					break;
				case "deleting_file":
					$msg = "Deleting file: <param>";
					break;
				case "uninstall_errors":
					$msg = "<span class='red'>There is errors during uninstall.</span>";
					break;
				case "restoring_file":
					$msg = "Restoring file from backup: <param>";
					break;
				case "cant_restore_file":
					$msg = "<span class='red'>Error: Can't restore file from backup: </span> <param><br /><span class='red'>Check permissions!</span>";
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
					$msg ="<br /><br /><a href='?'>Back to Main</a><br />";
					break;				
				case "set_permissions_directory":
					$msg = "<span class='red'>Please set permission (chmod 777) to directory:</span> <param>";
					break;
				case "set_permissions_file":
					$msg = "<span class='red'>Please set permission (chmod 777) to file:</span> <param>";
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
		
		function uninstall(){
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

		function uninstallDir($dir, $errors = false){
        	if($dir[strlen($dir)-1] != '/')
            	$dir .= '/';
        	$nDir = @opendir($dir);
        	if(!$nDir)
				$this->msg('cant_scan_directory', $dir);
			else {
				while($filep = readdir($nDir)){
            		if($filep != '.' && $filep != '..'){
                		$filename = $dir.$filep;
						if(!is_dir($filename)){
							if(preg_match('/cacheextender.backup.php$/i', $filename)){
								$filename1 = preg_replace("/(.cacheextender.backup.php)$/i", ".php", $filename);
								$this->msg('restoring_file', $filename1);
								@unlink($filename1);
								if(!@rename($filename, $filename1)){
									$this->msg('cant_restore_file', $filename1);
									$errors = true;
								}
							}
						} else {
                    		$errors = $this->uninstallDir($filename, $errors);
                		}
					}
				}
        		closedir($nDir);
			}
			return $errors;
		}

		function clearCache(){
			$this->msg('clearing_cache');
			$dir = '../../assets/cache/';
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

		function setRevision($file){
			$file = str_replace('<cacheextender_revision>', $this->revision, $file);
			return $file;
		}

		function footer(){
			echo('<br /><br /><b>Notice:</b> If there are permission error during installation, set correct permission and re-run installation.<br /><br />
			<b>Important!</b> You must uninstall current type of installation before install any other!
			</div><!-- // content -->
    </div>
</div><!-- // contentarea -->

<br />
<div id="footer">
    <div id="footer-inner">

        <div class="container_12">
            &copy; 2011 the <a href="http://master-53.ru/blog/index/cacheextender-dlya-modx-evolution-1.0.5.html" target="_blank" style="color: green; text-decoration:underline">MODx Cache Extender</a> for MODx Content Management Framework (CMF). Cache Extender is a free as is software.<br />It\'s under GNU GPL license.
</div>
    </div>
</div>

<!-- end install screen-->

</body>
</html>
			');
		}
		
	}
$engine = new CacheExtenderInstaller();
?>
