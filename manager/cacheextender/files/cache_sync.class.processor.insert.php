/*
*	Modified by thebat053
*	CacheExtender 0.1a
*	CacheExtender revision:<cacheextender_revision>
*/
    var $childs = array(); //modified by thebat053

    function getParentsCacheExtended($id, $path = '') { // modx:returns child's parent
        global $modx;
		if(!isset($this->childs)) $this->childs = array();
        if(empty($this->aliases)) {
            $sql = "SELECT id, IF(alias='', id, alias) AS alias, parent FROM ".$modx->getFullTableName('site_content');
            $qh = $modx->db->query($sql);
            if ($qh && $modx->db->getRecordCount($qh) > 0)  {
                while ($row = $modx->db->getRow($qh)) {
                    $this->aliases[$row['id']] = $row['alias'];
                    $this->parents[$row['id']] = $row['parent'];
					if(isset($row['parent'])){
						if(isset($this->childs[$row['parent']]))
							$this->childs[$row['parent']] .= '||'.$row['id'];
						else
							$this->childs[$row['parent']] = $row['id'];
					}
                }
            }
        }
        if (isset($this->aliases[$id])) {
            $path = $this->aliases[$id] . ($path != '' ? '/' : '') . $path;
            return $this->getParents($this->parents[$id], $path);
        }
        return $path;
    }
	
	function processDocumentCacheExtended($modx, &$tmpPHP, $config){
		$cacheMode = 'current_cache_mode'; //part or full. if full, array $d is indexed and cached too and you must replace all array_.. calls to $this->documentListing->array...
		$report = false;
		$cacheFileName = MODX_BASE_PATH.'assets/cache/siteCache.dat'; //name of the extended cache file
		$cacheFileNameUrl = MODX_BASE_PATH.'assets/cache/siteCacheUrl.dat'; //name of the extended cache file
		global $modx;
        if($report && $modx->checkSession()){
			echo("<br /><b>Cache Extended Active</b><br />");
			$start = $this->getMicroTime();
        }
        $tmpPath = '';
		include_once('cache_sync.create.class.php');
		$extCache = new CacheFill();
		if($cacheMode == 'part')
			$d = '$d = array(';
		else
			$extCacheUrl = new CacheFillUrl();
		$this->getParentsCacheExtended(-1); //precache childs ids $this->childs

		//caching top of tree
		if(isset($this->childs[0]))
			$childs = explode('||', $this->childs[0]);
		else
			$childs = array();
		$extCache->addIndex(0, $extCache->add(serialize(array(0, 'index', '', 0, $childs))));

        $sql = 'SELECT IF(alias=\'\', id, alias) AS alias, id, contentType, parent FROM '.$modx->getFullTableName('site_content').' WHERE deleted=0 ORDER BY parent, menuindex';
        $rs = $modx->db->query($sql);
        $limit_tmp = $modx->db->getRecordCount($rs);
        for ($i_tmp=0; $i_tmp<$limit_tmp; $i_tmp++) {
            $tmp1 = $modx->db->getRow($rs);
            if ($config['friendly_urls'] == 1 && $config['use_alias_path'] == 1) {
                $tmpPath = $this->getParentsCacheExtended($tmp1['parent']);
                $alias= (strlen($tmpPath) > 0 ? "$tmpPath/" : '').$tmp1['alias'];
                $alias= $modx->db->escape($alias);
				if($cacheMode == 'part')
					$d .= '"'.(string)$alias.'"=>'.$tmp1['id'].',';
            	else
					$extCacheUrl->addIndex($alias, $tmp1['id']);
			}
            else {
				if($cacheMode == 'part')
                	$d .= '"'.(string)$modx->db->escape($tmp1['alias']).'"=>'.(int)$tmp1['id'].',';
            	else
					$extCacheUrl->addIndex($modx->db->escape($tmp1['alias']), $tmp1['id']);
			}
			if(isset($this->childs[$tmp1['id']]))
				$childs = explode('||', $this->childs[$tmp1['id']]);
			else
				$childs = array();
			$extCache->addIndex((int)$tmp1['id'], $extCache->add(serialize(array($tmp1['id'], $modx->db->escape($tmp1['alias']), $modx->db->escape($tmpPath), $tmp1['parent'], $childs))));
        }
		$extCache->flush($cacheFileName);
		$tmpPHP .= '$cacheFileName = "'.$cacheFileName.'";'."\n";
		if($cacheMode == 'part'){
			$tmpPHP .= $d.');'."\n";
		} else {
			$extCacheUrl->flush($cacheFileNameUrl);
			$tmpPHP .= '$cacheFileNameUrl = "'.$cacheFileNameUrl.'";'."\n";
		}
		$tmpPHP .= '$cacheMode = "'.$cacheMode.'";'."\n";
		$tmpPHP .= "\n"."include(MODX_MANAGER_PATH . '/processors/cache_sync.wrapper.class.php');"."\n";
        if($report && $modx->checkSession()){
   			$totalTime= ($this->getMicroTime() - $start);
        	$totalTime= sprintf("%2.4f s", $totalTime);
			echo("<b>Reindex completed!</b> Pages processed: ".$limit_tmp.", Total time: ".$totalTime.", Memory used: ".(memory_get_peak_usage(true) / 1024 / 1024)." MB<br /><br />");
		}
    }
	
    function getMicroTime() {
        list ($usec, $sec)= explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }
