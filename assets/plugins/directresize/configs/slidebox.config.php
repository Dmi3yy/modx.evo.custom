<?php

$tpl = <<<HTML
<a href="[+dr.bigPath+]" rel="lightbox" title="[+dr.title+]">
	<img src="[+dr.thumbPath+]" width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]" />
</a>
HTML;

$header  = 	'
<link rel="stylesheet" href="'.DIRECTRESIZE_PATH.'libs/slidebox/style.css" type="text/css" media="screen" />
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/slidebox/slidebox_setup.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'lang/english.slidebox.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/slidebox/prototype.js"></script>
<script type="text/javascript" src="'.DIRECTRESIZE_PATH.'libs/slidebox/slidebox.js"></script>
<!--[if gte IE 5.5]>
<![if lt IE 7]>
<style type="text/css">
* html #overlay{
background-color: #333;
back\ground-color: transparent;
background-image: url('.DIRECTRESIZE_PATH.'libs/slidebox/blank.gif);
filter: progid:DXImageTransform.Microsoft.AlphaImageLoader (src="'.DIRECTRESIZE_PATH.'libs/slidebox/overlay.png", sizingMethod="scale");
</style>
<![endif]>
<![endif]-->
';

						
/*
Uncomment the line below if you use Maxigallery with slidebox effect in the same site
Раскомментируйте строку ниже, если вы используете Maxigallery с эффектом slidebox на одном сайте
*/
//$maxigallery_jscss_packs = "slidebox";

$lightbox_mode = 2;

$allow_from="assets/images";

$resize_method = 0;
?>