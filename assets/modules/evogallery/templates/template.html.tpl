<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Gallery Management Module</title>
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/templates/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/uploadify/uploadify.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/overlay/overlay-minimal.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="[+base_url+]assets/modules/evogallery/js/tags/tags.css" />
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/jquery-ui-1.8.13.custom.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/tools.overlay.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/uploadify/swfobject.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
	<script type="text/javascript" src="[+base_url+]assets/modules/evogallery/js/tags/tags.js"></script>
	[+js+]
</head>
<body>
<div id="actions-popup">
	<div id="galcontrols">
		<h4>[+lang.in_all_gallery+]:</h4><ul><li><a id="cmdAllDel" href="#">[+lang.delete_all+]</a></li><li><a id="cmdAllRegenerate" href="#">[+lang.regenerate_all+]</a></li></ul>
	</div>
	<div id="doccontrols">
		<h4>[+lang.in_this_doc+]:</h4><ul><li><a id="cmdCntDel" href="#">[+lang.delete_images+]</a></li><li><a id="cmdCntRegenerate" href="#">[+lang.regenerate_images+]</a></li><li><a id="cmdCntMoveTo" href="#">[+lang.move_to+]</a></li></ul>
	</div>
</div>
	<div id="actions-menu" class="awesome">[+lang.actions+]</div>
[+content+]
</div>
<div class="overlay" id="overlay"> 
	  <div class="contentWrap"></div>  
</div>
<div class="popup" id="operation-popup"> 
    <div class="status">[+lang.please_wait+]</div>
		<div class="progress"></div>
		<div class="close">		
			<input type="button" value="[+lang.close+]" class="awesome" name="close" />
		</div>
</div>
</body>
</html>
