<?php
	// Returns comment count
	function countcomments_mode(&$object) {
		global $modx;
		$output = $object->provider->GetCommentCount($object->config["docids"],$object->config["tagids"],1,$object->config["userids"]);
		$object->config["html"]["count-comments"] = $output;
		if ($object->config["output"]) return $output;
	}
?>
