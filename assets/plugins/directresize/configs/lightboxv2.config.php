<?php

$tpl = <<<HTML
<a href="[+dr.bigPath+]" rel="lightbox" title="[+dr.title+]">
	<img src="[+dr.thumbPath+]" width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]" />
</a>
HTML;

$header  = 	'
<link rel="stylesheet" href="'.DIRECTRESIZE_PATH.'libs/lightboxv2/css/lightbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/lightboxv2/js/lightbox_setup.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'lang/english.lightboxv2.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/lightboxv2/js/prototype.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/lightboxv2/js/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/lightboxv2/js/lightbox.js"></script>
';

/*
Uncomment the line below if you use Maxigallery with lightboxv2 effect in the same site
Раскомментируйте строку ниже, если вы используете Maxigallery с эффектом lightboxv2 на одном сайте
*/
//$maxigallery_jscss_packs = "lightboxv2";

$lightbox_mode = 2;

$allow_from="assets/images";

$resize_method = 0;
?>