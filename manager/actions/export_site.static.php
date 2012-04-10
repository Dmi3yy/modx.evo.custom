<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('export_static'))
{
	$e->setError(3);
	$e->dumpError();
}

// figure out the base of the server, so we know where to get the documents in order to export them
?>

<h1><?php echo $_lang['export_site_html']; ?></h1>
<div class="sectionBody">
<?php

if(!isset($_POST['export']))
{
	echo '<p>'.$_lang['export_site_message'].'</p>';
?>

<fieldset style="padding:10px;border:1px solid #ccc;"><legend style="font-weight:bold;"><?php echo $_lang['export_site']; ?></legend>
<form action="index.php" method="post" name="exportFrm">
<input type="hidden" name="export" value="export" />
<input type="hidden" name="a" value="83" />
<style type="text/css">
table.settings {width:100%;}
table.settings td.head {white-space:nowrap;vertical-align:top;padding-right:20px;font-weight:bold;}
</style>
<table class="settings" cellspacing="0" cellpadding="2">
  <tr>
    <td class="head"><?php echo $_lang['export_site_cacheable']; ?></td>
    <td><input type="radio" name="includenoncache" value="1" checked="checked"><?php echo $_lang['yes'];?>
		<input type="radio" name="includenoncache" value="0"><?php echo $_lang['no'];?></td>
  </tr>
  <tr>
    <td class="head">エクスポート対象</td>
    <td><input type="radio" name="target" value="0" checked="checked">更新されたページのみ
		<input type="radio" name="target" value="1">全てのページ</td>
  </tr>
  <tr>
    <td class="head">文字列を置換(置換前)</td>
    <td><input type="text" name="repl_before" value="<?php echo $modx->config['site_url']; ?>" style="width:300px;" /></td>
  </tr>
  <tr>
    <td class="head">文字列を置換(置換後)</td>
    <td><input type="text" name="repl_after" value="<?php echo $modx->config['site_url']; ?>" style="width:300px;" /></td>
  </tr>
  <tr>
    <td class="head"><?php echo $_lang['export_site_prefix']; ?></td>
    <td><input type="text" name="prefix" value="<?php echo $modx->config['friendly_url_prefix']; ?>" /></td>
  </tr>
  <tr>
    <td class="head"><?php echo $_lang['export_site_suffix']; ?></td>
    <td><input type="text" name="suffix" value="<?php echo $modx->config['friendly_url_suffix']; ?>" /></td>
  </tr>
  <tr>
    <td class="head"><?php echo $_lang['export_site_maxtime']; ?></td>
    <td><input type="text" name="maxtime" value="60" />
		<br />
		<small><?php echo $_lang['export_site_maxtime_message']; ?></small>
	</td>
  </tr>
</table>

<ul class="actionButtons">
	<li><a href="#" onclick="document.exportFrm.submit();"><img src="<?php echo $_style["icons_save"] ?>" /> <?php echo $_lang["export_site_start"]; ?></a></li>
</ul>
</form>
</fieldset>

<?php
}
else
{
	$export = new EXPORT_SITE();
	
	$maxtime = (is_numeric($_POST['maxtime'])) ? $_POST['maxtime'] : 30;
	@set_time_limit($maxtime);
	$exportstart = $export->get_mtime();

	$tbl_site_content = $modx->getFullTableName('site_content');
	$filepath = $modx->config['base_path'] . 'assets/export/';
	if(!is_writable($filepath))
	{
		echo $_lang['export_site_target_unwritable'];
		include "footer.inc.php";
		exit;
	}
	elseif(strpos($modx->config['base_path'],$filepath)===0 && 0 <= strlen(str_replace($filepath,'',$modx->config['base_path'])))
	{
		echo '/manager/ ディレクトリより上の階層にはファイルを出力できません。';
		include "footer.inc.php";
		exit;
	}
	elseif($modx->config['rb_base_dir'] === $filepath)
	{
		echo $modx->config['base_url'] . $modx->config['rb_base_url'] . ' ディレクトリにはファイルを出力できません。';
		include "footer.inc.php";
		exit;
	}
	
	$noncache = $_POST['includenoncache']==1 ? '' : 'AND cacheable=1';
	
	// Support export alias path
	
	if($modx->config['friendly_urls']==1 && $modx->config['use_alias_path']==1)
	{
		$where = "deleted=0 AND ((published=1 AND type='document') OR (isfolder=1)) {$noncache}";
		$rs  = $modx->db->select('count(id) as total',$tbl_site_content,$where);
		$row = $modx->db->getRow($rs);
		$total = $row['total'];
		printf($_lang['export_site_numberdocs'], $total);
		$n = 1;
		$export->exportDir(0, $filepath, $n, $total);

	}
	else
	{
		$prefix = $_POST['prefix'];
		$suffix = $_POST['suffix'];
	
	// Modified for export alias path  2006/3/24 end
		$fields = 'id, alias, pagetitle';
		$where = "deleted=0 AND published=1 AND type='document' {$noncache}";
		$rs = $modx->db->select($fields,$tbl_site_content,$where);
		$total = $modx->db->getRecordCount($rs);
		printf($_lang['export_site_numberdocs'], $total);

		for($i=0; $i<$total; $i++)
		{
			$row=$modx->db->getRow($rs);

			$id = $row['id'];
			printf($_lang['export_site_exporting_document'], $i+1, $total, $row['pagetitle'], $id);
			$row['alias'] = urldecode($row['alias']);
			$alias = $row['alias'];
		
			if(empty($alias))
			{
				$filename = $prefix.$id.$suffix;
			}
			else
			{
				$pa = pathinfo($alias); // get path info array
				$tsuffix = !empty($pa[extension]) ? '':$suffix;
				$filename = $prefix.$alias.$tsuffix;
			}
			// get the file
			$somecontent = @file_get_contents(MODX_SITE_URL . "index.php?id={$id}");
			if($somecontent === false)
			{
				// save it
				$filename = $filepath . $filename;
				// Write $somecontent to our opened file.
				$repl_before = $_POST['repl_before'];
				$repl_after  = $_POST['repl_after'];
				$somecontent = str_replace($repl_before,$repl_after,$somecontent);
				if(file_put_contents($filename, $somecontent) === FALSE)
				{
					echo ' <span class="fail">'.$_lang["export_site_failed"]."</span> ".$_lang["export_site_failed_no_writee"].'<br />';
					exit;
				}
				echo ' <span class="success">'.$_lang['export_site_success'].'</span><br />';
			}
			else
			{
				echo ' <span class="fail">'.$_lang["export_site_failed"]."</span> ".$_lang["export_site_failed_no_retrieve"].'<br />';
			}
		}
	}
	$exportend = $export->get_mtime();
	$totaltime = ($exportend - $exportstart);
	printf ('<p>'.$_lang["export_site_time"].'</p>', round($totaltime, 3));
?>
<ul class="actionButtons">
	<li><a href="#" onclick="document.location.href='index.php?a=7';"><img src="<?php echo $_style["icons_cancel"] ?>" /> <?php echo $_lang["close"]; ?></a></li>
</ul>
<?php
}



class EXPORT_SITE
{
	function EXPORT_SITE()
	{
	}
	
	function get_mtime()
	{
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		return $mtime;
	}
	
	function removeDirectoryAll($directory)
	{
		$directory = rtrim($directory,'/');
		// if the path is not valid or is not a directory ...
		if(strpos($directory,MODX_BASE_PATH)===false) return FALSE;
		
		if(!file_exists($directory) || !is_dir($directory))
		{
			return FALSE;
		}
		elseif(!is_readable($directory))
		{
			return FALSE;
		}
		else
		{
			foreach(glob($directory . '/*') as $path)
			{
				if(is_dir($path)) $this->removeDirectoryAll($path);// call myself
				else              @unlink($path);
			}
		}
		return (@rmdir($directory));
	}

	function writeAPage($docid, $filepath)
	{
		global  $modx,$_lang;
		
		$src = @file_get_contents(MODX_SITE_URL . "index.php?id={$docid}");
		if($src !== false)
		{
			$repl_before = $_POST['repl_before'];
			$repl_after  = $_POST['repl_after'];
			$src = str_replace($repl_before,$repl_after,$src);
			$result = @file_put_contents($filepath,$src);
			if($result !== false)
			{
				echo ' <span class="success">'.$_lang["export_site_success"].'</span><br />';
			}
			else
			{
				echo ' <span class="fail">'.$_lang["export_site_failed"]."</span> " . $_lang["export_site_failed_no_write"] . ' - ' . $filepath . '</span><br />';
				return FALSE;
			}
		}
		else
		{
			echo ' <span class="fail">'.$_lang["export_site_failed"]."</span> ".$_lang["export_site_failed_no_retrieve"].'</span><br />';
//			return FALSE;
		}
		return TRUE;
	}

	function getPageName($docid, $alias, $prefix, $suffix)
	{
		if(empty($alias))
		{
			$filename = $prefix.$docid.$suffix;
		}
		else
		{
			$pa = pathinfo($alias); // get path info array
			$tsuffix = !empty($pa['extension']) ? '':$suffix;
			$filename = $prefix.$alias.$tsuffix;
		}
		return $filename;
	}

	function scanDirectory($path, $docnames)
	{
		// if the path has a slash at the end, remove it
		$path = rtrim($path,'/');
		// if the path is not valid or is not a directory ...
		if(strpos($path,MODX_BASE_PATH)===false) return FALSE;
		
		if(!file_exists($path) || !is_dir($path))
		{
			return FALSE;
		}
		elseif(!is_readable($path))
		{
			return FALSE;
		}
		else
		{
			$files = glob($path . '/*');
			if(0 < count($files))
			{
				foreach($files as $filepath)
				{
					$filename = substr($filepath,strlen($path . '/'));
					if(!in_array($filename, $docnames))
					{
						if(is_dir($filepath)) $this->removeDirectoryAll($filepath);
						else                  @unlink($filepath);
					}
				}
			}
			return TRUE;
		}
	}

	function exportDir($dirid, $dirpath, &$i, $total)
	{
		global $_lang;
		global $modx;
		
		$tbl_site_content = $modx->getFullTableName('site_content');
		$fields = "id, alias, pagetitle, isfolder, (content = '' AND template = 0) AS wasNull, editedon, published";
		$noncache = $_POST['includenoncache']==1 ? '' : 'AND cacheable=1';
		$where = "parent = {$dirid} AND deleted=0 AND ((published=1 AND type='document') OR (isfolder=1)) {$noncache}";
		$rs = $modx->db->select($fields,$tbl_site_content,$where);
		$dircontent = array();
		while($row = $modx->db->getRow($rs))
		{
			$row['alias'] = urldecode($row['alias']);
			
			if (!$row['wasNull'])
			{ // needs writing a document
				$docname = $this->getPageName($row['id'], $row['alias'], $modx->config['friendly_url_prefix'], $suffix = $modx->config['friendly_url_suffix']);
				printf($_lang['export_site_exporting_document'], $i++, $total, $row['pagetitle'], $row['id']);
				$filename = $dirpath.$docname;
				if (is_dir($filename))
				{
					$this->removeDirectoryAll($filename);
				}
				if (!file_exists($filename) || (filemtime($filename) < $row['editedon']) || $_POST['target']=='1')
				{
					if($row['published']==1)
					{
						if (!$this->writeAPage($row['id'], $filename)) exit;
					}
					else
					{
						echo ' <span class="fail">'.$_lang["export_site_failed"]."</span> ".$_lang["export_site_failed_no_retrieve"].'<br />';
					}
				}
				else
				{
					echo ' <span class="success">'.$_lang['export_site_success']."</span> ".$_lang["export_site_success_skip_doc"].'<br />';
				}
				$dircontent[] = $docname;
			}
			if ($row['isfolder'])
			{ // needs making a folder
				if(empty($row['alias'])) $row['alias'] = $row['id'];
				$dirname = $dirpath . $row['alias'];
				if(strpos($dirname,MODX_BASE_PATH)===false) return FALSE;
				if (!is_dir($dirname))
				{
					if(file_exists($dirname)) @unlink($dirname);
					mkdir($dirname);
					if ($row['wasNull'])
					{
						printf($_lang['export_site_exporting_document'], $i++, $total, $row['pagetitle'], $row['id']);
						echo ' <span class="success">'.$_lang['export_site_success'].'</span><br />';
					}
				}
				else
				{
					if ($row['wasNull'])
					{
						printf($_lang['export_site_exporting_document'], $i++, $total, $row['pagetitle'], $row['id']);
						echo ' <span class="success">' . $_lang['export_site_success'] . '</span>' . $_lang["export_site_success_skip_dir"] . '<br />';
					}
				}
				$this->exportDir($row['id'], $dirname . '/', $i, $total);
				$dircontent[] = $row['alias'];
			}
		}
		// remove No-MODx files/dirs 
		if (!$this->scanDirectory($dirpath, $dircontent)) exit;
	}
}