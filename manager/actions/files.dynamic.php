<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('file_manager')) {
	$e->setError(3);
	$e->dumpError();
}

if(isset($_SESSION['token'])) $token['session'] = strval($_SESSION['token']);
if(isset($_REQUEST['token'])) $token['request'] = strval($_REQUEST['token']);
$token = uniqid('');
$_SESSION['token'] = $token;
// settings
$excludes = array('.', '..', 'cgi-bin', '.svn');
$editablefiles = array('.txt', '.php', '.shtml', '.html', '.htm', '.xml', '.js', '.css', '.pageCache', '.htaccess', $friendly_url_suffix);
$inlineviewablefiles = array('.txt', '.php', '.html', '.htm', '.xml', '.js', '.css', '.pageCache', '.access', $friendly_url_suffix);
$viewablefiles = array('.jpg', '.gif', '.png', '.ico');
// Mod added by Raymond
$enablefileunzip = true;
$enablefiledownload = true;
$newfolderaccessmode = $new_folder_permissions ? octdec($new_folder_permissions) : 0777;
$new_file_permissions = $new_file_permissions ? octdec($new_file_permissions) : 0666;
// End Mod -  by Raymond
// make arrays from the file upload settings
$upload_files = explode(',',$upload_files);
$upload_images = explode(',',$upload_images);
$upload_media = explode(',',$upload_media);
$upload_flash = explode(',',$upload_flash);
// now merge them
$uploadablefiles = array_merge($upload_files,$upload_images,$upload_media,$upload_flash);
$count = count($uploadablefiles);
for($i=0; $i<$count; $i++) {
	$uploadablefiles[$i] = ".".$uploadablefiles[$i]; // add a dot :)
}
// end settings

// get the current work directory
if(isset($_REQUEST['path']) && !empty($_REQUEST['path'])) {
        $_REQUEST['path'] = str_replace('..','',$_REQUEST['path']);
	$startpath = is_dir($_REQUEST['path']) ? $_REQUEST['path'] : removeLastPath($_REQUEST['path']) ;
} else {
	$startpath = $filemanager_path;
}

// Raymond: get web start path for showing pictures
$rf = realpath($filemanager_path);
$rw = realpath('../');
$webstart_path = str_replace('\\','/',str_replace($rw,'',$rf));
if(substr($webstart_path,0,1)=='/') $webstart_path = '..'.$webstart_path;
else $webstart_path = '../'.$webstart_path;

?>
<style type="text/css">
.warning {color:#c00;}
</style>
<h1><?php echo $_lang['manage_files']?></h1>

<div class="sectionBody">
<script type="text/javascript" src="media/script/multifile.js"></script>
<script type="text/javascript">
var current_path = '<?php echo $startpath;?>';

function viewfile(url) {
	document.getElementById('imageviewer').style.border="1px solid #000080";
	document.getElementById('imageviewer').src=url;
}

function setColor(o,state){
	if (!o) return;
	if(state && o.style) o.style.backgroundColor='#eeeeee';
	else if (o.style) o.style.backgroundColor='transparent';
}

function confirmDelete() {
	return confirm("<?php echo $_lang['confirm_delete_file'] ?>");
}

function confirmDeleteFolder() {
	return confirm("<?php echo str_replace('file','folder',$_lang['confirm_delete_file']) ?>");
}

function confirmUnzip() {
	return confirm("<?php echo $_lang['confirm_unzip_file'] ?>");
}

function getFolderName(a){
	var f;
	f=window.prompt('Enter New Folder Name:','')
	if (f) a.href+=escape(f);
	return (f) ? true:false;
}

function getFileName(a){
	var f;
	f=window.prompt('Enter New File Name:','')
	if (f) a.href+=escape(f);
	return (f) ? true:false;
}

function deleteFolder (folder) {
    if (confirmDeleteFolder())
    {
        window.location.href="index.php?a=31&mode=deletefolder&path="+current_path+"&folderpath="+current_path+'/'+folder+"&token=<?php echo $token;?>";
        return false;
    }
}

function deleteFile(file) {
    if (confirmDelete())
    {
        window.location.href="index.php?a=31&mode=delete&path="+current_path+'/'+file+"&token=<?php echo $token;?>";
        return false;
    }
}
</script>
<?php
if(!empty($_FILES['userfile'])) {

  for ($i = 0; $i <= count($_FILES['userfile']['tmp_name']); $i++) {
   if(!empty($_FILES['userfile']['tmp_name'][$i])) {
     $userfiles[$i]['tmp_name'] = $_FILES['userfile']['tmp_name'][$i];
     $userfiles[$i]['error'] = $_FILES['userfile']['error'][$i];
            $name = $_FILES['userfile']['name'][$i];
            if($modx->config['clean_uploaded_filename']) {
                $nameparts = explode('.', $name);
                $nameparts = array_map(array($modx, 'stripAlias'), $nameparts);
                $name = implode('.', $nameparts);
            }
            $userfiles[$i]['name'] = $name;
     $userfiles[$i]['type'] = $_FILES['userfile']['type'][$i];
   }
  }

  foreach((array)$userfiles as $userfile) {

	// this seems to be an upload action.
	printf("<p>".$_lang['files_uploading']."</p>", $userfile['name'], substr($startpath, strlen($filemanager_path), strlen($startpath)));
	echo $userfile['error']==0 ? "<p>".$_lang['files_file_type'].$userfile['type'].", ".$modx->nicesize($userfile['tmp_name']).'</p>' : '';

	$userfilename = $userfile['tmp_name'];

	if (is_uploaded_file($userfilename)) {
	  // file is uploaded file, process it!
		if(!in_array(getExtension($userfile['name']), $uploadablefiles)) {
			echo '<p><span class="warning">'.$_lang['files_filetype_notok'].'</span></p>';
		} else {
			if(@move_uploaded_file($userfile['tmp_name'], $_POST['path'].'/'.$userfile['name'])) {
					// Ryan: Repair broken permissions issue with file manager
					if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN')
						@chmod($_POST['path']."/".$userfile['name'], $new_file_permissions);
					// Ryan: End
					echo '<p><span class="success">'.$_lang['files_upload_ok'].'</span></p>';

					// invoke OnFileManagerUpload event
					$modx->invokeEvent('OnFileManagerUpload',
						array(
							'filepath'	=> $_POST['path'],
							'filename'	=> $userfile['name']
					));
					// Log the change
					logFileChange('upload', $_POST['path'].'/'.$userfile['name']);
			} else {
				echo '<p><span class="warning">'.$_lang['files_upload_copyfailed'].'</span> '.$_lang["files_upload_permissions_error"].'</p>';
			}
		}
        } else {
		echo '<br /><span class="warning"><b>'.$_lang['files_upload_error'].':</b>';
	  switch($userfile['error']){
	   case 0: //no error; possible file attack!
		 echo $_lang['files_upload_error0'];
		 break;
	   case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
		 echo $_lang['files_upload_error1'];
		 break;
	   case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
		 echo $_lang['files_upload_error2'];
		 break;
	   case 3: //uploaded file was only partially uploaded
		 echo $_lang['files_upload_error3'];
		 break;
	   case 4: //no file was uploaded
		 echo $_lang['files_upload_error4'];
		 break;
	   default: //a default error, just in case!  :)
		 echo $_lang['files_upload_error5'];
		 break;
		}
		echo '</span><br />';
	}
	echo '<hr/>';
    }
}

if($_POST['mode']=='save') {
	echo $_lang['editing_file'];
	$filename = $_POST['path'];
	$content = $_POST['content'];

	// Write $content to our opened file.
	if (file_put_contents($filename, $content) === FALSE) {
	   echo '<span class="warning"><b>'.$_lang['file_not_saved'].'</b></span><br /><br />';
	} else {
	   echo '<span class="success"><b>'.$_lang['file_saved'].'</b></span><br /><br />';
	   $_REQUEST['mode'] = 'edit';
	}

	// Log the change
	logFileChange('modify', $filename);
}


if($_REQUEST['mode']=='delete')
{
	printf($_lang['deleting_file'], str_replace('\\', '/', $_REQUEST['path']));
	$file = $_REQUEST['path'];
	if(!isset($_GET['token']) || $token['request']!==$token['session'])
	{
	   echo '<span class="warning"><b>'.$_lang['file_not_deleted'].'</b></span><br /><br />';
	}
	elseif(!@unlink($file))
	{
	   echo '<span class="warning"><b>'.$_lang['file_not_deleted'].'</b></span><br /><br />';
	}
	else
	{
	   echo '<span class="success"><b>'.$_lang['file_deleted'].'</b></span><br /><br />';
	}

	// Log the change
	logFileChange('delete', $file);
}

if($startpath==="{$filemanager_path}manager" || $startpath==="{$filemanager_path}assets/backup")
{
	echo 'このディレクトリは参照できません。';
	exit;
}

$tpl = '<img src="[+style_path+]tree/[+image+]" align="absmiddle" alt="" />[+subject+] ';
$ph = array();
$ph['style_path'] = $style_path;
// To Top Level with folder icon to the left
if($startpath==$filemanager_path || $startpath.'/' == $filemanager_path)
{
	$ph['image']   = 'deletedfolder.gif';
	$ph['subject'] = '<span style="color:#bbb;cursor:default;">Top</span>';
}
else
{
	$ph['image']   = 'folder.gif';
	$ph['subject'] = '<a href="index.php?a=31&mode=drill&path=' . $filemanager_path . '">Top</b></a> / ';
}
echo $modx->parsePlaceholder($tpl,$ph);

$len = strlen($filemanager_path);
if(substr($startpath, $len, strlen($startpath))=='')
	$topic_path = '/';
else
{
	$topic_path = substr($startpath, $len, strlen($startpath));
	$pieces = explode('/',rtrim($topic_path,'/'));
	$path = '';
	$count = count($pieces);
	foreach($pieces as $i=>$v)
	{
		if(empty($v)) continue;
		$path .=  rtrim($v,'/') . '/';
		if(1<$count)
		{
			$href = 'index.php?a=31&mode=drill&path=' . urlencode($filemanager_path.$path);
			$pieces[$i] = '<a href="' . $href . '">' . trim($v,'/') . '</a>';
		}
		else $pieces[$i] = trim($v,'/');
		$count--;
	}
	$topic_path = join(' / ', $pieces);
}

?> <b><?php echo $topic_path; ?></b>
<?php
// check to see user isn't trying to move below the document_root
if(substr(strtolower(str_replace('//','/',$startpath."/")), 0, $len)!=strtolower(str_replace('//','/',$filemanager_path.'/'))) {
	echo $_lang['files_access_denied']?>
</div>

<?php
	exit;
}

// Unzip .zip files - by Raymond
if ($enablefileunzip && $_REQUEST['mode']=='unzip' && is_writable($startpath))
{
	if(!$err=@unzip(realpath("$startpath/".$_REQUEST['file']),realpath($startpath)))
	{
		echo '<span class="warning"><b>'.$_lang['file_unzip_fail'].($err===0? 'Missing zip library (php_zip.dll / zip.so)':'').'</b></span><br /><br />';
	}
	else
	{
		echo '<span class="success"><b>'.$_lang['file_unzip'].'</b></span><br /><br />';
	}
}
// End Unzip - Raymond


// New Folder & Delete Folder option - Raymond
if (is_writable($startpath))
{
	// Delete Folder
	if($_REQUEST['mode']=='deletefolder')
	{
		$folder = $_REQUEST['folderpath'];
		if(!isset($_GET['token']) || $token['request']!==$token['session'])
		{
		   echo '<span class="warning"><b>'.$_lang['file_folder_not_deleted'].'</b></span><br /><br />';
		}
		elseif(!@rmdir($folder))
		{
			echo '<span class="warning"><b>'.$_lang['file_folder_not_deleted'].'</b></span><br /><br />';
		}
		else
		{
			echo '<span class="success"><b>'.$_lang['file_folder_deleted'].'</b></span><br /><br />';
		}
	}

	// Create folder here
	if($_REQUEST['mode']=='newfolder')
	{
		$old_umask = umask(0);
		$foldername = str_replace('..\\','',str_replace('../','',$_REQUEST['name']));
		if(!mkdirs("{$startpath}/{$foldername}",$newfolderaccessmode))
		{
			echo '<span class="warning"><b>',$_lang['file_folder_not_created'],'</b></span><br /><br />';
		}
		else
		{
			if (!@chmod($startpath.'/'.$foldername,$newfolderaccessmode))
			{
				echo '<span class="warning"><b>'.$_lang['file_folder_chmod_error'].'</b></span><br /><br />';
			}
			else
			{
				echo '<span class="success"><b>'.$_lang['file_folder_created'].'</b></span><br /><br />';
			}
		}
		umask($old_umask);
	}
	// Create file here
	if($_REQUEST['mode']=='newfile')
	{
		$old_umask = umask(0);
		$filename = str_replace('..\\','',str_replace('../','',$_REQUEST['name']));
		$filename = $modx->db->escape($filename);
		
		if(!in_array(getExtension($filename), $uploadablefiles))
		{
			echo '<span class="warning"><b>'.$_lang['files_filetype_notok'].'</b></span><br /><br />';
		}
		elseif(preg_match('@(\\\\|\/|\:|\;|\,|\*|\?|\"|\<|\>|\||\?)@',$filename)!==0)
		{
			echo '<span class="warning"><b>ファイル名が不正です。</b></span><br /><br />';
		}
		else
		{
			$rs = file_put_contents("{$startpath}/{$filename}",'');
			if($rs===false)
			{
				echo '<span class="warning"><b>',$_lang['file_folder_not_created'],'</b></span><br /><br />';
			}
			else
			{
				echo '<span class="success"><b>テキストファイルを作成しました。</b></span><br /><br />';
			}
			umask($old_umask);
		}
	}
}
// End New Folder - Raymond

$filesize = 0;
$files = 0;
$folders = 0;
$dirs_array = array();
$files_array = array();
if(strlen(MODX_BASE_PATH) < strlen($filemanager_path)) $len--;

echo '<br />';
?>
<table>
<tr>
<td style="width:300px;"><b><?php echo $_lang['files_filename']?></b></td>
<td><b><?php echo $_lang['files_modified']?></b></td>
<td><b><?php echo $_lang['files_filesize']?></b></td>
<td><b><?php echo $_lang['files_fileoptions']?></b></td>
</tr>
<?php
ls($startpath);
echo "\n\n\n";
if($folders==0 && $files==0) {
	echo '<tr><td colspan="4"><img src="' . $style_path . 'tree/deletedfolder.gif" /><span style="color:#888;cursor:default;"> This directory is empty.</span></td></tr>';
}
?></table><?php

echo $_lang['files_directories'],': <b>',$folders,'</b> ';
echo $_lang['files_files'],': <b>',$files,'</b> ';
echo $_lang['files_data'],': <b><span dir="ltr">',$modx->nicesize($filesizes),'</span></b> ';
echo $_lang['files_dirwritable'],' <b>',is_writable($startpath)==1 ? $_lang['yes'].'.' : $_lang['no'].'.'
?></b>
<div align="center">
<img src="<?php echo $_style['tx']; ?>" id="imageviewer" />
</div>
<br /><hr />
<?php
if (((@ini_get("file_uploads") == true) || get_cfg_var("file_uploads") == 1) && is_writable($startpath)) {
	@ini_set("upload_max_filesize", $upload_maxsize); // modified by raymond
?>
<form enctype="multipart/form-data" action="index.php" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo isset($upload_maxsize)? $upload_maxsize:3145728; ?>">
<input type="hidden" name="a" value="31">
<input type="hidden" name="path" value="<?php echo $startpath?>">

<div id="uploader">
<span style="width:300px;"><?php echo $_lang['files_uploadfile_msg']?></span>
<input id="file_elem" type="file" name="bogus"  style="height: 19px;">
<input type="submit" value="<?php echo $_lang['files_uploadfile']?>">

<div id="files_list"></div>
<script type="text/javascript">
	var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 10 );
	multi_selector.addElement( document.getElementById( 'file_elem' ) );
</script>
</div>
</form>

<?php
} else {
	echo "<p>".$_lang['files_upload_inhibited_msg']."</p>";
}

if (is_writable($startpath))
{
	$ph = array();
	$ph['style_path'] = $style_path;
	$tpl = '<img src="[+style_path+]tree/[+image+]" align="absmiddle" alt="" />[+subject+]<br />';
	$ph['image']   = 'folder.gif';
	$ph['subject'] = ' <a href="index.php?a=31&mode=newfolder&path='.urlencode($startpath).'&name=" onclick="return getFolderName(this);"><b>'.$_lang['add_folder'].'</b></a>';
	echo $modx->parsePlaceholder($tpl,$ph);
	
	$ph['image']   = 'page-html.gif';
	$ph['subject'] = ' <a href="index.php?a=31&mode=newfile&path='.urlencode($startpath).'&name=" onclick="return getFileName(this);"><b>テキストファイルを新規作成</b></a>';
	echo $modx->parsePlaceholder($tpl,$ph);
}


?>


</div>

<?php

if($_REQUEST['mode']=="edit" || $_REQUEST['mode']=="view") {
?>

<div class="sectionHeader" id="file_editfile"><?php echo $_REQUEST['mode']=="edit" ? $_lang['files_editfile'] : $_lang['files_viewfile']?></div>
<div class="sectionBody">
<?php
$filename=$_REQUEST['path'];
$buffer = file_get_contents($filename);
// Log the change
logFileChange('view', $filename);
if($buffer===false) {
	echo 'Error opening file for reading.';
	exit;
}

?>
<form action="index.php" method="post" name="editFile">
<input type="hidden" name="a" value="31" />
<input type="hidden" name="mode" value="save" />
<input type="hidden" name="path" value="<?php echo $_REQUEST['path']?>" />
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><textarea dir="ltr" style="width:100%; height:370px;" name="content" class="phptextarea"><?php echo htmlentities($buffer,ENT_COMPAT,$modx_manager_charset)?></textarea></td>
  </tr>
</table>
</form>
<?php

if($_REQUEST['mode']=="edit") {
?>
<br />
<ul class="actionButtons">
	<li><a href="#" onclick="document.editFile.submit();"><img src="<?php echo $_style["icons_save"] ?>" /> <?php echo $_lang['save']?></a></li>
	<li><a href="index.php?a=31&path=<?php echo urlencode($_REQUEST['path'])?>"><img src="<?php echo $_style["icons_cancel"] ?>" /> <?php echo $_lang['cancel']?></a></li>
</ul>
<?php } ?>
</div>
<?php
}

function ls($curpath)
{
	global $_lang,$style_path;
	global $excludes, $editablefiles, $inlineviewablefiles, $viewablefiles, $enablefileunzip, $enablefiledownload, $uploadablefiles, $folders, $files, $filesizes, $len, $dirs_array, $files_array, $webstart_path, $modx;
	$dircounter = 0;
	$filecounter = 0;
	$curpath = str_replace('//','/',$curpath.'/');

	if (!is_dir($curpath))
	{
		echo 'Invalid path "',$curpath,'"<br />';
		return;
	}
	$dir = dir($curpath);

	// first, get info
	while ($file = $dir->read())
	{
		$newpath = $curpath.$file;
		if(is_dir($newpath))
		{
			$dirs_array[$dircounter]['dir'] = $newpath;
			$dirs_array[$dircounter]['stats'] = lstat($newpath);
			if($file==='..'||$file==='.') continue;
			elseif(!in_array($file, $excludes) && $newpath!==MODX_BASE_PATH . 'manager' && $newpath!==MODX_BASE_PATH . 'assets/backup')
			{
				$dirs_array[$dircounter]['text'] = '<img src="' . $style_path.'tree/folder.gif" align="absmiddle" alt="" /> <a href="index.php?a=31&mode=drill&path='.urlencode($newpath).'"><b>'.$file.'</b></a>';
				$dirs_array[$dircounter]['delete'] = is_writable($curpath) ? '<span style="width:20px"><a href="javascript: deleteFolder(\''.urlencode($file).'\');"><img src="'.$style_path.'icons/delete.gif" alt="'.$_lang['file_delete_folder'].'" title="'.$_lang['file_delete_folder'].'" /></a></span>' : '';
			}
			else
			{
				$dirs_array[$dircounter]['text'] = '<img src="'.$style_path.'tree/deletedfolder.gif" align="absmiddle" alt="" /> <span style="color:#bbb;">'.$file . '</span>';
				$dirs_array[$dircounter]['delete'] = is_writable($curpath) ? '<span style="width:20px" class="disabledImage"><img src="'.$style_path.'icons/delete.gif" alt="'.$_lang['file_delete_folder'].'" title="'.$_lang['file_delete_folder'].'" /></span>' : '';
			}

			// increment the counter
			$dircounter++;
		}
		else
		{
			$type=getExtension($newpath);
			$files_array[$filecounter]['file'] = $newpath;
			$files_array[$filecounter]['stats'] = lstat($newpath);
			$files_array[$filecounter]['text'] = '<img src="'.$style_path.'tree/page-html.gif" align="absmiddle" alt="" />'.$file;
			$files_array[$filecounter]['view'] = (in_array($type, $viewablefiles)) ?
			'<span style="cursor:pointer; width:20px;" onclick="viewfile(\''.$webstart_path.substr($newpath, $len, strlen($newpath)).'\');"><img src="'.$style_path.'icons/context_view.gif" align="absmiddle" alt="'.$_lang['files_viewfile'].'" title="'.$_lang['files_viewfile'].'" /></span>' : (($enablefiledownload && in_array($type, $uploadablefiles))? '<a href="'.$webstart_path.implode('/', array_map('rawurlencode', explode('/', substr($newpath, $len, strlen($newpath))))).'" style="cursor:pointer; width:20px;"><img src="'.$style_path . 'misc/ed_save.gif" align="absmiddle" alt="'.$_lang['file_download_file'].'" title="'.$_lang['file_download_file'].'" /></a>':'<span class="disabledImage"><img src="'.$style_path . 'icons/context_view.gif" align="absmiddle" alt="'.$_lang['files_viewfile'].'" title="'.$_lang['files_viewfile'].'" /></span>');
			$files_array[$filecounter]['view'] = (in_array($type, $inlineviewablefiles)) ? '<span style="width:20px;"><a href="index.php?a=31&mode=view&path='.urlencode($newpath).'"><img src="'.$style_path . 'icons/context_view.gif" align="absmiddle" alt="'.$_lang['files_viewfile'].'" title="'.$_lang['files_viewfile'].'" /></a></span>' : $files_array[$filecounter]['view'] ;
			$files_array[$filecounter]['unzip'] = ($enablefileunzip && $type=='.zip') ? '<span style="width:20px;"><a href="index.php?a=31&mode=unzip&path='.$curpath.'&file='.urlencode($file).'" onclick="return confirmUnzip();"><img src="'.$style_path . 'icons/unzip.gif" align="absmiddle" alt="'.$_lang['file_download_unzip'].'" title="'.$_lang['file_download_unzip'].'" /></a></span>' : '' ;
			$files_array[$filecounter]['edit'] = (in_array($type, $editablefiles) && is_writable($curpath) && is_writable($newpath)) ? '<span style="width:20px;"><a href="index.php?a=31&mode=edit&path='.urlencode($newpath).'#file_editfile"><img src="'.$style_path . 'icons/save.png" align="absmiddle" alt="'.$_lang['files_editfile'].'" title="'.$_lang['files_editfile'].'" /></a></span>' : '<span class="disabledImage"><img src="'.$style_path . 'icons/save.png" align="absmiddle" alt="'.$_lang['files_editfile'].'" title="'.$_lang['files_editfile'].'" /></span>';
            $files_array[$filecounter]['delete'] = is_writable($curpath) && is_writable($newpath) ? '<span style="width:20px;"><a href="javascript:deleteFile(\''.urlencode($file).'\');"><img src="'.$style_path . 'icons/delete.gif" align="absmiddle" alt="'.$_lang['file_delete_file'].'" title="'.$_lang['file_delete_file'].'" /></a></span>' : '<span class="disabledImage"><img src="'.$style_path . 'icons/delete.gif" align="absmiddle" alt="'.$_lang['file_delete_file'].'" title="'.$_lang['file_delete_file'].'" /></span>';

			// increment the counter
			$filecounter++;
		}
	}
	$dir->close();

	// dump array entries for directories
	$folders = count($dirs_array);
	sort($dirs_array); // sorting the array alphabetically (Thanks pxl8r!)
	for($i=0; $i<$folders; $i++)
	{
		$filesizes += $dirs_array[$i]['stats']['7'];
		echo '<tr style="cursor:default;" onmouseout="setColor(this,0)" onmouseover="setColor(this,1)">';
		echo '<td>',$dirs_array[$i]['text'],'</td>';
		echo '<td>',$modx->toDateFormat($dirs_array[$i]['stats']['9']),'</td>';
		echo '<td dir="ltr">',$modx->nicesize($dirs_array[$i]['stats']['7']),'</td>';
		echo '<td>';
		echo $dirs_array[$i]['delete'];
		echo '</td>';
		echo '</tr>';
	}

	// dump array entries for files
	$files = count($files_array);
	sort($files_array); // sorting the array alphabetically (Thanks pxl8r!)
	for($i=0; $i<$files; $i++)
	{
		$filesizes += $files_array[$i]['stats']['7'];
		echo '<tr onmouseout="setColor(this,0)" onmouseover="setColor(this,1)">';
		echo '<td>',$files_array[$i]['text'],'</td>';
		echo '<td>',$modx->toDateFormat($files_array[$i]['stats']['9']),'</td>';
		echo '<td dir="ltr">',$modx->nicesize($files_array[$i]['stats']['7']),'</td>';
		echo '<td>';
		echo $files_array[$i]['unzip'];
		echo $files_array[$i]['view'];
		echo $files_array[$i]['edit'];
		echo $files_array[$i]['delete'];
		echo '</td>';
		echo '</tr>';
	}
	return;
}

function removeLastPath($string) {
   $pos = false;
   $search = "/";
   if (is_int(strpos($string, $search))) {
	   $endPos = strlen($string);
	   while ($endPos > 0) {
		   $endPos = $endPos - 1;
		   $pos = strpos($string, $search, $endPos);
		   if (is_int($pos)) {
			   break;
		   }
	   }
   }
   if (is_int($pos)) {
	   $len = strlen($search);
	   return substr($string, 0, $pos);
   }
	return $string;
}

function getExtension($string) {
   $pos = false;
   $search = '.';
   if (is_int(strpos($string, $search)))
   {
	   $endPos = strlen($string);
	   while ($endPos > 0) {
		   $endPos = $endPos - 1;
		   $pos = strpos($string, $search, $endPos);
		   if (is_int($pos)) {
			   break;
		   }
	   }
   }
   if (is_int($pos)) {
	   $len = strlen($search);
	   return substr($string, $pos);
   }
	return $string;
}

function mkdirs($strPath, $mode){ // recursive mkdir function
	if (is_dir($strPath)) return true;
	$pStrPath = dirname($strPath);
	if (!mkdirs($pStrPath, $mode)) return false;
	return @mkdir($strPath);
}

function logFileChange($type, $filename)
{
	//global $_lang;

	include_once('log.class.inc.php');
	$log = new logHandler();

	switch ($type)
	{
		case 'upload':		$string = 'Uploaded File'; break;
		case 'delete':		$string = 'Deleted File'; break;
		case 'modify':		$string = 'Modified File'; break;
		default:		$string = 'Viewing File'; break;
	}

	$string = sprintf($string, $filename);
	$log->initAndWriteLog($string, '', '', '', $type, $filename);

	// HACK: change the global action to prevent double logging
	// @see manager/index.php @ 915
	global $action; $action = 1;
}

// by patrick_allaert - php user notes
function unzip($file, $path)
{
	global $newfolderaccessmode;
	// added by Raymond
	$r = substr($path,strlen($path)-1,1);
	if ($r!='\\'||$r!='/') $path .='/';
	if (!extension_loaded('zip'))
	{
		return 0;
	}
	// end mod
	$zip = zip_open($file);
	if ($zip)
	{
		$old_umask = umask(0);
		while ($zip_entry = zip_read($zip))
		{
			if (zip_entry_filesize($zip_entry) > 0)
			{
				// str_replace must be used under windows to convert "/" into "\"
				$complete_path = $path.str_replace('/','\\',dirname(zip_entry_name($zip_entry)));
				$complete_name = $path.str_replace ('/','\\',zip_entry_name($zip_entry));
				if(!file_exists($complete_path))
				{
					$tmp = '';
					foreach(explode('\\',$complete_path) AS $k)
					{
						$tmp .= $k.'\\';
						if(!file_exists($tmp))
						{
							@mkdir($tmp, $newfolderaccessmode);
						}
					}
				}
				if (zip_entry_open($zip, $zip_entry, 'r'))
				{
					$fd = fopen($complete_name, 'w');
					fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
					fclose($fd);
					zip_entry_close($zip_entry);
				}
			}
		}
		umask($old_umask);
		zip_close($zip);
		return true;
	}
	zip_close($zip);
}
