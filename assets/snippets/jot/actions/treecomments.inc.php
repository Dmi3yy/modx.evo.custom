<?php
	// Display comments
	function treecomments_mode(&$object) {
		global $modx;
		
		$output_comments = NULL;
		// Check if viewing is allowed
		if($object->canView) {
			
			// View (Moderation)
			$view = 1;
			if ($object->isModerator) { 
				$view = $object->config["moderation"]["view"];
				$object->config["moderation"]["unpublished"] = $object->provider->GetCommentCount($object->config["docids"],$object->config["tagids"],0,$object->config["userids"]);
				$object->config["moderation"]["published"] = $object->provider->GetCommentCount($object->config["docids"],$object->config["tagids"],1,$object->config["userids"]);
				$object->config["moderation"]["mixed"] = $object->provider->GetCommentCount($object->config["docids"],$object->config["tagids"],2,$object->config["userids"]);
			}
			
			// Render Moderation Options
			if ($object->isModerator) { 
				$tpl = new CChunkie($object->templates["moderate"]);
				$tpl->AddVar('jot',$object->config);
				$object->config["html"]["moderate"] = $tpl->Render();
			}
			
			// Render subscription options
			$tpl = new CChunkie($object->templates["subscribe"]);
			$tpl->AddVar('jot',$object->config);
			$object->config["html"]["subscribe"] = $tpl->Render();
			
			// Get comments
			$array_comments = $object->provider->GetComments($object->config["docids"],$object->config["tagids"],$view,$object->config["upc"],$object->config["sortby"],0,0,$object->config["userids"]);	
			
			// Get comments count
			$count = count($array_comments);
			
			// Comment Numbering
			for ($i = 0; $i < $count; $i++) {
				$num = ($object->config["numdir"]) ? $count-$i :  $i+1;
				if (substr($object->config["sortby"], -1)=='a') $array_comments[$i]["postnumber"] = $count - $num + 1;
				else $array_comments[$i]["postnumber"] = $num;
			}
			
			// Get tree
			$tree = array();
			foreach ($array_comments as $row) {
				$tree[(int) $row['parent']][] = $row;
			}
			unset($array_comments);
			
			// Get first level comments count
			$commentTotal = count($tree[0]);
			
			// Get total number of comments
			$limit = $object->config["limit"];
			$commentTotal = ($limit>0 && $limit<$commentTotal) ? $limit : $commentTotal;
			$pagination = (isset($_GET[$object->config["querykey"]["navigation"]]) && $_GET[$object->config["querykey"]["navigation"]] == 0) ? 0 : $object->config["pagination"];
			
			// Apply pagination if enabled
			if ($pagination > 0) {
				$pageLength = ($limit>0 && $limit<$pagination) ? $limit : $pagination;
				$pageTotal = ceil($commentTotal / $pageLength);
				$pageCurrent = isset($_GET[$object->config["querykey"]["navigation"]]) ? intval($_GET[$object->config["querykey"]["navigation"]]): 1;
				if ( ($pageCurrent < 1) || ($pageCurrent > $pageTotal) ) { $pageCurrent = 1; };
				$pageOffset = (($pageCurrent*$pageLength)-$pageLength);
				$navStart = ($pageOffset+1);
				$navEnd = ($pageOffset+$pageLength) > $commentTotal ? $commentTotal : ($pageOffset+$pageLength);
			} else {
				$pageLength = $commentTotal;
				$pageOffset = 0;
				$pageTotal = 1;
				$pageCurrent = 1;
				$navStart = 0;
				$navEnd = $commentTotal;
			}
			
			if (is_array($tree[0]))  $tree[0] = array_slice($tree[0],$pageOffset,$pageLength);
			
			// Navigation
			$object->config['nav'] = array('total'=>$commentTotal,'start'=>$navStart,'end'=> $navEnd);
			$object->config['page'] = array('length'=>$pageLength,'total'=>$pageTotal,'current'=>$pageCurrent);
			
			// Render navigation
			if (($pagination > 0) && ($pageTotal > 1) ) {
				$tpl = new CChunkie($object->templates["navigation"]);
				$tplPage = $tpl->getTemplate($object->templates["navPage"]);
				$tplPageCur = $tpl->getTemplate($object->templates["navPageCur"]);
				$tplPageSpl = $tpl->getTemplate($object->templates["navPageSpl"]);
				$pages = '';
				for ($i = 1; $i <= $pageTotal; $i++) {
					$pages .= ($i == $pageCurrent) ? str_replace('[+jot.page.num+]',$i,$tplPageCur) : str_replace('[+jot.page.num+]',$i,$tplPage);
					if ($i< $pageTotal) $pages .= $tplPageSpl;
				}
				$tpl->template = str_replace('[+jot.pages+]',$pages,$tpl->template);
				$tpl->AddVar('jot',$object->config);
				$object->config["html"]["navigation"] = $tpl->Render();
			}
			
			if(!function_exists('treeRender')){
			function treeRender($tree, $pid, &$object, $depth) {
				global $modx;
				if (empty($tree[$pid])) return;
				$res = '';
				foreach ($tree[$pid] as $k=>$row) {
					$chunk["rowclass"] = $object->getChunkRowClass($k+1,$row["createdby"]);
					$tpl = new CChunkie($object->templates["comments"]);
					$row["parentlink"] = $object->preserveUrl($modx->documentIdentifier,'',array_merge($object->_link,array('parent'=>$row["id"])));
					$row["depth"] = $depth;
					$tpl->AddVar('jot',$object->config);
					$tpl->AddVar('comment',$row);
					$tpl->AddVar('chunk',$chunk);
					if (isset($tree[$row['id']]) &&  $object->config["depth"] > $depth) {
						$tpl->AddVar('jot.wrapper',treeRender($tree, $row['id'], $object, $depth+1));
					}else{
						$tpl->AddVar('jot.wrapper','');
					}
					$res .= $tpl->Render();
				}
				return $res;
			}
			}
			
			/* Render comments */
			$object->config["html"]["comments"] = treeRender($tree, 0 , $object, 1);
			unset($tree);
			
			//onSetCommentsOutput event
			if (null !== ($output = $object->doEvent("onSetCommentsOutput"))) return $output;
			
			$output_comments = $object->config["html"]["subscribe"] . $object->config["html"]["moderate"] .
			$object->config["html"]["navigation"] . $object->config["html"]["comments"] . $object->config["html"]["navigation"];
		}
		if ($object->config["output"]) return $output_comments;
	}
?>
