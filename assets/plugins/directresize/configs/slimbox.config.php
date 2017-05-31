<?php

$tpl = <<<HTML
<a href="[+dr.bigPath+]" rel="lightbox" title="[+dr.title+]">
	<img src="[+dr.thumbPath+]" width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]" />
</a>
HTML;

$header  = 	'
<link rel="stylesheet" href="'.DIRECTRESIZE_PATH.'libs/slimbox/css/slimbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/slimbox/js/mootools.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'lang/english.slimbox.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/slimbox/js/slimbox.js"></script>
';
									
/*
Uncomment the line below if you use Maxigallery with slimbox effect in the same site
Раскомментируйте строку ниже, если вы используете Maxigallery с эффектом slimbox на одном сайте
*/
//$maxigallery_jscss_packs = "slimbox";

$lightbox_mode = 2;

$allow_from="assets/images";

$resize_method = 0;
?>