<?php

// This is the template to markup your thumbnails. See readme for possible placeholders.
$tpl = <<<HTML

<a title="[+dr.title+]" rel="example_group" href="[+dr.bigPath+]">
	<img width="[+dr.thumbWidth+]" height="[+dr.thumbHeight+]" title="[+dr.title+]" alt="[+dr.alt+]"  class="[+dr.class+]" style="[+dr.style+]" align="[+dr.align+]" src="[+dr.thumbPath+]" >
</a>


HTML;

// All CSS and JS files and all other code that we need in our <HEAD> tag.
$header  = 	'

{{fancy}}

';

// Mode for lightbox links generating. 2 means that links will be genereted for all images with the allowed paths.
$lightbox_mode = 2;

// Watermarking. Uncomment 3 lines below if you are need it.
//$thumb_use_watermark = TRUE;
//$thumb_watermark_img = DIRECTRESIZE_PATH.'images/zoom.png';
//$thumb_watermark_type = "image";

// Very important parameter - paths to folders, where the images will be proccesed. Use comma as separator. You can use remote paths with http://.
$allow_from="assets/images";

// Method for thumbs generating. 0 means that firstly image is reduced, then it is cropped to fit in the rectangle thumb width х thumb height.
$resize_method = 3;


?>