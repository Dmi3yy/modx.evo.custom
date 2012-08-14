<?php
	// Display comments
	function comments_mode(&$object) {
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
			
			// Get total number of comments
			$commentTotal = $object->provider->GetCommentCount($object->config["docids"],$object->config["tagids"],$view,$object->config["userids"]);
			$limit = $object->config["limit"];
			$commentTotal = ($limit>0 && $limit<$commentTotal) ? $limit : $commentTotal;
			$pagination = (isset($_GET[$object->config["querykey"]["navigation"]]) && $_GET[$object->config["querykey"]["navigation"]] == 0) ? 0 : $object->config["pagination"];
			
			// Apply pagination if enabled
			if ($pagination > 0) {
				$pageLength = ($limit>0 && $limit<$pagination) ? $limit : $pagination;
				$pageTotal = ceil($commentTotal / $pageLength);
				$pageCurrent = isset($_GET[$object->config["querykey"]["navigation"]]) ? $_GET[$object->config["querykey"]["navigation"]]: 1;
				if ( ($pageCurrent < 1) || ($pageCurrent > $pageTotal) ) { $pageCurrent = 1; };
				$pageOffset = (($pageCurrent*$pageLength)-$pageLength);
				$navStart = ($pageOffset+1);
				$navEnd = ($pageOffset+$pageLength) > $commentTotal ? $commentTotal : ($pageOffset+$pageLength);
			} else {
				$pageLength = $limit;
				$pageOffset = 0;
				$pageTotal = 1;
				$pageCurrent = 1;
				$navStart = 0;
				$navEnd = $commentTotal;
			}
			
			// Navigation
			$object->config['nav'] = array('total'=>$commentTotal,'start'=>$navStart,'end'=> $navEnd);
			$object->config['page'] = array('length'=>$pageLength,'total'=>$pageTotal,'current'=>$pageCurrent);
			
			// Render Moderation Options
			if ($object->isModerator) { 
				$tpl = new CChunkie($object->templates["moderate"]);
				$tpl->AddVar('jot',$object->config);
				$object->config["html"]["moderate"] = $tpl->Render();
			}
			
			// Get comments
			$array_comments = $object->provider->GetComments($object->config["docids"],$object->config["tagids"],$view,$object->config["upc"],$object->config["sortby"],$pageOffset,$pageLength,$object->config["userids"]);
			
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
			
			// Render subscription options
			$tpl = new CChunkie($object->templates["subscribe"]);
			$tpl->AddVar('jot',$object->config);
			$object->config["html"]["subscribe"] = $output_subscribe = $tpl->Render();
			
			// Render comments
			$count = count($array_comments);
			$comments = array();
			
			// Comment Numbering
			for ($i = 0; $i < $count; $i++) {
				$num = ($object->config["numdir"]) ? $commentTotal - ($pageOffset + $i) :  $pageOffset + ($i+1);
				$array_comments[$i]["postnumber"] = $num;
			}
			for ($i = 0; $i < $count; $i++) {
				$chunk["rowclass"] = $object->getChunkRowClass($i+1,$array_comments[$i]["createdby"]);
				$tpl = new CChunkie($object->templates["comments"]);
				$tpl->AddVar('jot',$object->config);
				$tpl->AddVar('comment',$array_comments[$i]);
				$tpl->AddVar('chunk',$chunk);
				$comments[] = $tpl->Render();
			}
			$object->config["html"]["comments"] = join("",$comments);
			
			//onSetCommentsOutput event
			if (null !== ($output = $object->doEvent("onSetCommentsOutput"))) return $output;
			
			$output_comments = $object->config["html"]["subscribe"] . $object->config["html"]["moderate"] .
			$object->config["html"]["navigation"] . $object->config["html"]["comments"] . $object->config["html"]["navigation"];
		}
		if ($object->config["output"]) return $output_comments;
	}
?>
