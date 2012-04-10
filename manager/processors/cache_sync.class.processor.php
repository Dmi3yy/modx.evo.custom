<?php
// cache & synchronise class

class synccache {
	var $cachePath;
	var $showReport;
	var $deletedfiles = array();
	var $aliases = array();
	var $parents = array();
	var $target;

	function synccache()
	{
		if(empty($this->target))      $this->target = 'pagecache,sitecache';
		if(defined('MODX_BASE_PATH')) $this->cachePath = MODX_BASE_PATH . 'assets/cache/';
	}
	
	function setTarget($target)
	{
		$this->target = $target;
	}
	
	function setCachepath($path) {
		$this->cachePath = rtrim($path,'/') . '/';
	}

	function setReport($bool) {
		$this->showReport = $bool;
	}

	function escapeDoubleQuotes($s) {
		$q1 = array("\\","\"","\r","\n","\$");
		$q2 = array("\\\\","\\\"","\\r","\\n","\\$");
		return str_replace($q1,$q2,$s);
	}

	function escapeSingleQuotes($s) {
		$q1 = array("\\","'");
		$q2 = array("\\\\","\\'");
		return str_replace($q1,$q2,$s);
	}

	function getParents($id, $path = '') { // modx:returns child's parent
		global $modx;
		if(empty($this->aliases))
		{
			$fields = "id, IF(alias='', id, alias) AS alias, parent";
			$tbl_site_content = $modx->getFullTableName('site_content');
			$qh = $modx->db->select($fields,$tbl_site_content);
			if ($qh && $modx->db->getRecordCount($qh) > 0)
			{
				while ($row = $modx->db->getRow($qh))
				{
					$this->aliases[$row['id']] = $row['alias'];
					$this->parents[$row['id']] = $row['parent'];
				}
			}
		}
		if (isset($this->aliases[$id]))
		{
			$path = $this->aliases[$id] . ($path != '' ? '/' : '') . $path;
			return $this->getParents($this->parents[$id], $path);
		}
		return $path;
	}

	function emptyCache($modx = null)
	{
		$instance_name = '';
		if(is_object($modx))
		{
			$instance_name = get_class($modx);
		}
		$instance_name = strtolower($instance_name);
		if($instance_name!=='documentparser') global $modx;
		
		if(!isset($this->cachePath))
		{
			echo "Cache path not set.";
			exit;
		}
		
		if(strpos($this->target,'pagecache')!==false) $result = $this->emptyPageCache('pageCache');
		if(strpos($this->target,'sitecache')!==false) $this->buildCache($modx);
		$this->publish_time_file($modx);
		if(isset($result) && $this->showReport==true) $this->showReport($result);
	}
	
	function emptyPageCache($target)
	{
		$filesincache = 0;
		$deletedfilesincache = 0;
		$pattern = realpath($this->cachePath)."/*.{$target}.php";
		$pattern = str_replace('\\','/',$pattern);
		$files = glob($pattern,GLOB_NOCHECK);
		$filesincache = ($files[0] !== $pattern) ? count($files) : 0;
		$deletedfiles = array();
		if(is_array($files) && 0 < $filesincache)
		{
			while ($file = array_shift($files))
			{
				$name = basename($file);
				if (strpos($name,".{$target}")!==false && !in_array($name, $deletedfiles))
				{
					$deletedfilesincache++;
					$deletedfiles[] = $name;
					unlink($file);
				}
			}
		}
		return array($filesincache,$deletedfilesincache,$deletedfiles);
	}

	function showReport($info)
	{
		list($filesincache,$deletedfilesincache,$deletedfiles) = $info;
		// finished cache stuff.
		global $_lang;
		printf($_lang['refresh_cache'], $filesincache, $deletedfilesincache);
		$limit = count($deletedfiles);
		if($limit > 0)
		{
			echo '<p>'.$_lang['cache_files_deleted'].'</p><ul>';
			for($i=0;$i<$limit; $i++)
			{
				echo '<li>',$deletedfiles[$i],'</li>';
			}
			echo '</ul>';
		}
	}
	
	/****************************************************************************/
	/*  PUBLISH TIME FILE                                                       */
	/****************************************************************************/
	function publish_time_file($modx)
	{
		// update publish time file
		$tbl_site_content      = $modx->getFullTableName('site_content');
		$tbl_site_htmlsnippets = $modx->getFullTableName('site_htmlsnippets');
		$timesArr = array();
		$current_time = time();
		
		$result = $modx->db->select('MIN(pub_date) AS minpub',$tbl_site_content, "{$current_time} < pub_date");
		if(!$result)
		{
			echo "Couldn't determine next publish event!";
		}
		
		$minpub = $modx->db->getValue($result);
		if($minpub!=NULL)
		{
			$timesArr[] = $minpub;
		}
		
		$result = $modx->db->select('MIN(unpub_date) AS minunpub',$tbl_site_content, "{$current_time} < unpub_date");
		if(!$result)
		{
			echo "Couldn't determine next unpublish event!";
		}
		$minunpub = $modx->db->getValue($result);
		if($minunpub!=NULL)
		{
			$timesArr[] = $minunpub;
		}
		
		$result = $modx->db->select('MIN(pub_date) AS minpub',$tbl_site_htmlsnippets, "{$current_time} < pub_date");
		if(!$result)
		{
			echo "Couldn't determine next publish event!";
		}
		
		$minpub = $modx->db->getValue($result);
		if($minpub!=NULL)
		{
			$timesArr[] = $minpub;
		}
		
		$result = $modx->db->select('MIN(unpub_date) AS minunpub',$tbl_site_htmlsnippets, "{$current_time} < unpub_date");
		if(!$result)
		{
			echo "Couldn't determine next unpublish event!";
		}
		$minunpub = $modx->db->getValue($result);
		if($minunpub!=NULL)
		{
			$timesArr[] = $minunpub;
		}
		
		if(count($timesArr)>0) $nextevent = min($timesArr);
		else                   $nextevent = 0;
		
		// write the file
		$cache_path = $this->cachePath.'sitePublishing.idx.php';
		$content = '<?php $cacheRefreshTime='.$nextevent.';';
		
		$rs = file_put_contents($cache_path, $content);
		
		if (!$rs)
		{
			echo "Cannot open file ({$filename})";
			exit;
		}
	}
	
	/**
	* build siteCache file
	* @param  DocumentParser $modx
	* @return boolean success
	*/
	function buildCache($modx)
	{
		$content = "<?php\n";
		
		// SETTINGS & DOCUMENT LISTINGS CACHE
		
		$content .= $this->_get_settings($modx); // get settings
		$content .= $this->_get_aliases($modx);  // get aliases modx: support for alias path
		$content .= $this->_get_content_types($modx); // get content types
		$content .= $this->_get_chunks($modx);   // WRITE Chunks to cache file
		$content .= $this->_get_snippets($modx); // WRITE snippets to cache file
		$content .= $this->_get_plugins($modx);  // WRITE plugins to cache file
		$content .= $this->_get_events($modx);   // WRITE system event triggers
		
		// close and write the file
		$content .= "\n";
		$content = str_replace(array("\x0d\x0a", "\x0a", "\x0d"), "\x0a", $content);
		
		// invoke OnBeforeCacheUpdate event
		if ($modx) $modx->invokeEvent('OnBeforeCacheUpdate');
		
		if(!file_put_contents($this->cachePath.'siteCache.idx.php', $content))
		{
			echo 'Cannot write main MODX cache file! Make sure the "' . $this->cachePath . '" directory is writable!';
			exit;
		}
		
		// invoke OnCacheUpdate event
		if ($modx) $modx->invokeEvent('OnCacheUpdate');
		
		return true;
	}
	
	function _get_settings($modx)
	{
		$tbl_system_settings    = $modx->getFullTableName('system_settings');
		
		$rs = $modx->db->select('setting_name,setting_value',$tbl_system_settings);
		$tmpPHP = '$c=&$this->config;' . "\n";
		$tpl = '$c[[+key+]] = "[+value+]";';
		$row = array();
		while($row = $modx->db->getRow($rs))
		{
			$search  = array('[+key+]','[+value+]');
			$replace = array("'{$row['setting_name']}'",$this->escapeDoubleQuotes($row['setting_value']));
			$tmpPHP .= str_replace($search,$replace,$tpl) . "\n";
		}
		return $tmpPHP;
	}
	
	function _get_aliases($modx)
	{
		$tbl_system_settings    = $modx->getFullTableName('system_settings');
		$tbl_site_content       = $modx->getFullTableName('site_content');
		
		$tmpPHP  = '$this->aliasListing = array();' . "\n";
		$tmpPHP .= '$a = &$this->aliasListing;' . "\n";
		$tmpPHP .= '$d = &$this->documentListing;' . "\n";
		$tmpPHP .= '$m = &$this->documentMap;' . "\n";
		
		$friendly_urls = $modx->db->getValue($modx->db->select('setting_value',$tbl_system_settings,"setting_name='friendly_urls'"));
		if($friendly_urls==1)
		{
			$use_alias_path = $modx->db->getValue($modx->db->select('setting_value',$tbl_system_settings,"setting_name='use_alias_path'"));
		}
		$fields = "IF(alias='', id, alias) AS alias, id, contentType, parent";
		$where  = 'deleted=0 ORDER BY parent, menuindex';
		$rs = $modx->db->select($fields,$tbl_site_content,$where);
		$row = array();
		$path = '';
		while ($row = $modx->db->getRow($rs))
		{
			if ($friendly_urls == 1 && $use_alias_path == 1)
			{
				$path = $this->getParents($row['parent']);
				$alias_path= (strlen($path) > 0 ? "{$path}/" : '').$row['alias'];
			}
			else
			{
				$alias_path = $row['alias'];
			}
			$alias_path = $modx->db->escape($alias_path);
			$alias = $modx->db->escape($row['alias']);
			$docid = $row['id'];
			$path = $modx->db->escape($path);
			$parent = $row['parent'];
			$tmpPHP .= '$' . "d['{$alias_path}'] = {$docid};\n";
			$tmpPHP .= '$' . "a[{$docid}] = array('id' => {$docid}, 'alias' => '{$alias}', 'path' => '{$path}', 'parent' => {$parent});\n";
			$tmpPHP .= '$' . "m[] = array('{$parent}' => '{$docid}');\n";
			$modx->documentListing[$alias_path] = $docid;
			$modx->aliasListing[$docid] = array('id' => $docid, 'alias' => $alias, 'path' => $path, 'parent' => $parent);
			$modx->documentMap[] = array($parent => $docid);
		}
		return $tmpPHP;
	}
	
	function _get_content_types($modx)
	{
		$tbl_site_content       = $modx->getFullTableName('site_content');
		
		$rs = $modx->db->select('id, contentType',$tbl_site_content,"contentType != 'text/html'");
		$tmpPHP = '$c = &$this->contentTypes;' . "\n";
		$row = array();
		while ($row = $modx->db->getRow($rs))
		{
			$tmpPHP .= '$c['.$row['id'].']'." = '".$row['contentType']."';\n";
		}
		return $tmpPHP;
	}
	
	function _get_chunks($modx)
	{
		$tbl_site_htmlsnippets  = $modx->getFullTableName('site_htmlsnippets');
		
		$rs = $modx->db->select('name,snippet',$tbl_site_htmlsnippets, "`published`='1'");
		$tmpPHP = '$c = &$this->chunkCache;' . "\n";
		$row = array();
		while ($row = $modx->db->getRow($rs))
		{
			$tmpPHP .= '$c[\''.$modx->db->escape($row['name']).'\']'." = '".$this->escapeSingleQuotes($row['snippet'])."';\n";
		}
		return $tmpPHP;
	}
	
	function _get_snippets($modx)
	{
		$tbl_site_snippets      = $modx->getFullTableName('site_snippets');
		$tbl_site_modules       = $modx->getFullTableName('site_modules');
		
		$fields = 'ss.name,ss.snippet,ss.properties,sm.properties as `sharedproperties`';
		$from = "{$tbl_site_snippets} ss LEFT JOIN {$tbl_site_modules} sm on sm.guid=ss.moduleguid";
		$rs = $modx->db->select($fields,$from);
		$tmpPHP = '$s = &$this->snippetCache;' . "\n";
		$row = array();
		while ($row = $modx->db->getRow($rs))
		{
			$tmpPHP .= '$s[\''.$modx->db->escape($row['name']).'\']'." = '".$this->escapeSingleQuotes($row['snippet'])."';\n";
			// Raymond: save snippet properties to cache
			if ($row['properties']!=""||$row['sharedproperties']!="")
			{
				$properties = $this->escapeSingleQuotes($row['properties'] . ' ' . $row['sharedproperties']);
				$tmpPHP .= '$s[\''.$row['name'].'Props\']'." = '".$properties."';\n";
				// End mod
			}
		}
		return $tmpPHP;
	}
	
	function _get_plugins($modx)
	{
		$tbl_site_modules       = $modx->getFullTableName('site_modules');
		$tbl_site_plugins       = $modx->getFullTableName('site_plugins');
		
		$fields = 'sp.name,sp.plugincode,sp.properties,sm.properties as `sharedproperties`';
		$from = "{$tbl_site_plugins} sp LEFT JOIN {$tbl_site_modules} sm on sm.guid=sp.moduleguid";
		$rs = $modx->db->select($fields,$from,'sp.disabled=0');
		$tmpPHP = '$p = &$this->pluginCache;' . "\n";
		$row = array();
		while ($row = $modx->db->getRow($rs))
		{
			$name = $modx->db->escape($row['name']);
			$plugincode = $this->escapeSingleQuotes($row['plugincode']);
			$properties = $this->escapeSingleQuotes($row['properties'].' '.$row['sharedproperties']);
			$tmpPHP .= '$p[\''.$name.'\']'." = '".$plugincode."';\n";
			if ($row['properties']!=''||$row['sharedproperties']!='')
			{
				$tmpPHP .= '$p[' . "'{$name}Props'] = '{$properties}';\n";
			}
		}
		return $tmpPHP;
	}
	
	function _get_events($modx)
	{
		$tbl_site_plugins       = $modx->getFullTableName('site_plugins');
		$tbl_system_eventnames  = $modx->getFullTableName('system_eventnames');
		$tbl_site_plugin_events = $modx->getFullTableName('site_plugin_events');
		
		$fields  = 'sysevt.name as `evtname`, plugs.name';
		$from    = "{$tbl_system_eventnames} sysevt INNER JOIN {$tbl_site_plugin_events} pe ON pe.evtid = sysevt.id INNER JOIN {$tbl_site_plugins} plugs ON plugs.id = pe.pluginid";
		$where   = 'plugs.disabled=0';
		$orderby = 'sysevt.name,pe.priority';
		$rs = $modx->db->select($fields,$from,$where,$orderby);
		$tmpPHP = '$e = &$this->pluginEvent;' . "\n";
		$events = array();
		$row = array();
		while ($row = $modx->db->getRow($rs))
		{
			if(!$events[$row['evtname']])
			{
				$events[$row['evtname']] = array();
			}
			$events[$row['evtname']][] = $row['name'];
		}
		foreach($events as $evtname => $pluginnames)
		{
			$tmpPHP .= '$e[\''.$evtname.'\'] = array(\''.implode("','",$this->escapeSingleQuotes($pluginnames))."');\n";
		}
		return $tmpPHP;
	}
}
