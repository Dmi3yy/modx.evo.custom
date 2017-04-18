<?php

if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
header("X-XSS-Protection: 0");
$_SESSION['browser'] = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 1') !== false) ? 'legacy_IE' : 'modern';

$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

if(!isset($modx->config['manager_menu_height'])) {
	$modx->config['manager_menu_height'] = '48';
}
if(!isset($modx->config['manager_tree_width'])) {
	$modx->config['manager_tree_width'] = '320';
}
$modx->invokeEvent('OnManagerPreFrameLoader', array('action' => $action));

if(isset($_SESSION['onLoginForwardToAction']) && is_int($_SESSION['onLoginForwardToAction'])) {
	$initMainframeAction = $_SESSION['onLoginForwardToAction'];
	unset($_SESSION['onLoginForwardToAction']);
} else {
	$initMainframeAction = 2; // welcome.static
}

$bodyClass = '';

if(isset($_COOKIE['MODX_positionSideBar'])) {
	$MODX_positionSideBar = $_COOKIE['MODX_positionSideBar'];
} else {
	$MODX_positionSideBar = $modx->config['manager_tree_width'];
}

if(!$MODX_positionSideBar) {
	$bodyClass .= 'sidebar-closed';
}

?>
<!DOCTYPE html>
<html <?php echo (isset($modx_textdir) && $modx_textdir ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"'; ?>>
<head>
	<title><?php echo $site_name ?>- (MODX CMS Manager)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset ?>" />
	<link rel="stylesheet" type="text/css" href="media/style/common/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" />
	<style>
		#tree { width: <?php echo $MODX_positionSideBar ?>px }
		#main, #resizer { left: <?php echo $MODX_positionSideBar ?>px }
	</style>
</head>
<body class="<?php echo $bodyClass ?>">
<div id="frameset">
	<div id="resizer">
		<a class="switcher"><i class="fa fa-chevron-left"></i></a> <i class="handler"></i></div>
	<div id="mainMenu">
		<iframe name="mainMenu" src="index.php?a=1&amp;f=menu" scrolling="no" frameborder="0"></iframe>
	</div>
	<div id="tree">
		<iframe name="tree" src="index.php?a=1&amp;f=tree" scrolling="no" frameborder="0"></iframe>
	</div>
	<div id="main">
		<iframe name="main" id="mainframe" src="index.php?a=<?php echo $initMainframeAction; ?>" scrolling="auto" frameborder="0" onload="if (mainMenu.stopWork()) mainMenu.stopWork(); scrollWork();"></iframe>
	</div>
	<div class="dropdown"></div>
	<div id="searchresult"></div>
	<script type="text/javascript">

		var reloadmenu = function() {
			mainMenu.reloadmenu()
		};

		mainMenu.reloadtree = function() {
			mainMenu.reloadtree()
		};

		//save scrollPosition
		function getQueryVariable(variable, query) {
			var vars = query.split('&');
			for(var i = 0; i < vars.length; i++) {
				var pair = vars[i].split('=');
				if(decodeURIComponent(pair[0]) == variable) {
					return decodeURIComponent(pair[1]);
				}
			}
		}

		function scrollWork() {
			var frm = document.getElementById("mainframe").contentWindow;
			currentPageY = localStorage.getItem('page_y');
			pageUrl = localStorage.getItem('page_url');
			if(currentPageY === undefined) {
				localStorage.setItem('page_y', 0);
			}
			if(pageUrl === null) {
				pageUrl = frm.location.search.substring(1);
			}
			if(getQueryVariable('a', pageUrl) == getQueryVariable('a', frm.location.search.substring(1))) {
				if(getQueryVariable('id', pageUrl) == getQueryVariable('id', frm.location.search.substring(1))) {
					frm.scrollTo(0, currentPageY);
				}
			}

			frm.onscroll = function() {
				if(frm.pageYOffset > 0) {
					localStorage.setItem('page_y', frm.pageYOffset);
					localStorage.setItem('page_url', frm.location.search.substring(1));
				}
			}

			function ExtractNumber(value) {
				var n = parseInt(value);
				return n == null || isNaN(n) ? 0 : n
			}
		}

	</script>
	<?php
	$modx->invokeEvent('OnManagerFrameLoader', array('action' => $action));
	?>
</div>
</body>
</html>
