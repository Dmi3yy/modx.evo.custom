<?php
	// Returns subscriptions count
	function countsubscriptions_mode(&$object) {
		global $modx;
		$output = count($object->provider->getSubscriptions($object->config["docids"],$object->config["tagids"]));
		$object->config["html"]["count-subscriptions"] = $output;
		if ($object->config["output"]) return $output;
	}
?>
