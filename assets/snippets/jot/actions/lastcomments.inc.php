<?php
	// Display comments
	function lastcomments_mode(&$object) {
		global $modx;
		
		$output_comments = NULL;
		// Check if viewing is allowed
		if($object->canView) {
			
			// Get comments
			$limit = ($object->config["limit"]>0) ? $object->config["limit"] : 3;
			$array_comments = $object->provider->GetComments("*",$object->config["tagids"],1,$object->config["upc"],$object->config["sortby"],0,$limit,$object->config["userids"]);
			
			// Render comments
			$count = count($array_comments);
			$comments = array();
			
			// Comment Numbering
			for ($i = 0; $i < $count; $i++) {
				$num = ($object->config["numdir"]) ? $count-$i :  $i+1;
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
			
			$output_comments = $object->config["html"]["comments"];
		}
		if ($object->config["output"]) return $output_comments;
	}
?>
