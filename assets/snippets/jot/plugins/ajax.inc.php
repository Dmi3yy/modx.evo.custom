<?php
function ajax(&$object,$params){
	global $modx;

	switch($object->event) {
		case "onSetCommentsOutput":
			if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
				$object->config["html"]["subscribe"] = '<div id="subscribe-'.$object->_idshort.'">'.$object->config["html"]["subscribe"].'</div>';
				$object->config["html"]["moderate"] = '<div id="moderate-'.$object->_idshort.'">'.$object->config["html"]["moderate"].'</div>';
				$object->config["html"]["navigation"] = '<div class="navigation-'.$object->_idshort.'">'.$object->config["html"]["navigation"].'</div>';
				$object->config["html"]["comments"] = '<div id="comments-'.$object->_idshort.'">'.$object->config["html"]["comments"].'</div>';
			}
			break;
		case "onSetFormOutput":
			if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
				$object->config["html"]["form"] = '<div id="form-'.$object->_idshort.'">'.$object->config["html"]["form"].'</div>';
			}
			break;
		case "onReturnOutput":
			if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
				$modx->regClientStartupScript('<script type="text/javascript">window.jQuery || document.write(\'<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"><\/script>\');</script>');
				$modx->regClientStartupScript($modx->config["base_url"].'assets/snippets/jot/js/ajax.js');
				$modx->regClientStartupScript('<script type="text/javascript">jQuery(document).ready(function() { jotAjax("'.$object->_idshort.'"); });</script>');
			}
			else {
				$res = str_replace('onclick="history.go(-1);return false;"','',$object->config["html"]["form"])
					."|!|~|!|". $object->config["html"]["comments"]
					."|!|~|!|". $object->config["html"]["moderate"]
					."|!|~|!|". $object->config["html"]["navigation"]
					."|!|~|!|". $object->config["html"]["subscribe"];
				die($res);
			}
			break;
	}
}

?>