/*
*	Modified by thebat053
*	CacheExtender 0.1a
*	CacheExtender revision:<cacheextender_revision>
*/
	var $cacheExtender = true; //modified by thebat053

    function rewriteUrls($documentSource) {
        // rewrite the urls
        if ($this->config['friendly_urls'] == 1) {
        	$in= '!\[\~([0-9]+)\~\]!ise'; // Use preg_replace with /e to make it evaluate PHP
			preg_match_all ($in, $documentSource, $matches);
            $aliases= array ();
			foreach ($matches[1] as $match){
				$item = $this->aliasListing[$match];
                $aliases[$match]= (strlen($item['path']) > 0 ? $item['path'] . '/' : '') . $item['alias'];
			}
            $isfriendly= ($this->config['friendly_alias_urls'] == 1 ? 1 : 0);
            $pref= $this->config['friendly_url_prefix'];
            $suff= $this->config['friendly_url_suffix'];
            $thealias= '$aliases[\\1]';
            $found_friendlyurl= "\$this->makeFriendlyURL('$pref','$suff',$thealias)";
            $not_found_friendlyurl= "\$this->makeFriendlyURL('$pref','$suff','" . '\\1' . "')";
            $out= "({$isfriendly} && isset({$thealias}) ? {$found_friendlyurl} : {$not_found_friendlyurl})";
            $documentSource= preg_replace($in, $out, $documentSource);
        } else {
            $in= '!\[\~([0-9]+)\~\]!is';
            $out= "index.php?id=" . '\1';
            $documentSource= preg_replace($in, $out, $documentSource);
        }
        return $documentSource;
    }


	function getChildIds($id, $depth = 10, $children = array(), $strictchilds = null){
		if(!is_array($id))
			$id = array($id);
		foreach($id as $chid){
			$children += $this->getChildIdsRec($chid, $depth, array(), $strictchilds);
		}

		return $children;
	}

    function getChildIdsRec($id, $depth, $children = array(), $strictchilds = null) {
        // Get all the children for this parent node
        if (isset($this->documentMap_cache[$id])) {
            $depth--;
            $tmp = $this->documentMap_cache[$id];
			foreach ($tmp as $childId) {
				if($strictchilds)
                	if(!in_array($childId, $strictchilds))
                    	continue;
                $pkey = (strlen($this->aliasListing[$childId]['path']) ? "{$this->aliasListing[$childId]['path']}/" : '') . $this->aliasListing[$childId]['alias'];
                if (!strlen($pkey)) $pkey = "{$childId}";
                    $children[$pkey] = $childId;

                if ($depth || $depth < 0) {
                    $children += $this->getChildIdsRec($childId, $depth, array(), $strictchilds);
                }
            }
        }
        return $children;
    }
/*
*	End of modification
*/
