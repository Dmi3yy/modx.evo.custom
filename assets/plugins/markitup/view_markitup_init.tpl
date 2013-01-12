<script type="text/javascript">window.jQuery || document.write('<script type="text/javascript" src="[+markitup_url+]jquery-1.3.2.min.js"><\/script>');</script>
<!-- markItUp! -->
<script type="text/javascript" src="[+markitup_url+]markitup/jquery.markitup.pack.js"></script>
<!-- markItUp! toolbar settings -->
<script type="text/javascript" src="[+markitup_url+]markitup/sets/html/set.js"></script>
<!-- markItUp! skin -->
<link rel="stylesheet" type="text/css" href="[+markitup_url+]markitup/skins/markitup/style.css" />
<!--  markItUp! toolbar skin -->
<link rel="stylesheet" type="text/css" href="[+markitup_url+]markitup/sets/html/style.css" />
<script type="text/javascript">
<!--
var lastType,lastImageCtrl,lastFileCtrl;
function OpenBrowser(type) {
	var width = screen.width * 0.7;
	var height = screen.height * 0.7;
				
	var iLeft = (screen.width  - width) / 2 ;
	var iTop  = (screen.height - height) / 2 ;

	var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
	sOptions += ',width=' + width ;
	sOptions += ',height=' + height ;
	sOptions += ',left=' + iLeft ;
	sOptions += ',top=' + iTop ;

	var oWindow = window.open( '[+modx_browser_url+]browser.html?Type=' + type, 'FCKBrowseWindow', sOptions ) ;
	lastType = type;
}

var $j = jQuery.noConflict();
$j(document).ready(function() {
	// Add markItUp! to your textarea in one line
	// $('textarea').markItUp( { Settings }, { OptionalExtraSettings } );
	$j('[+textarea+]').markItUp(mySettings);
});
function SetUrl(url, width, height, alt) {
	if(lastFileCtrl) {
		var c = document.mutate[lastFileCtrl];
		if(c) c.value = url;
		lastFileCtrl = '';
	} else if(lastImageCtrl) {
		var c = document.mutate[lastImageCtrl];
		if(c) c.value = url;
		lastImageCtrl = '';
	} else {
		if (lastType=='images') { $j.markItUp({ replaceWith:'<img src="'+url+'" alt="" />' }); }
		if (lastType=='files') { $j.markItUp({ openWith:'<a href="'+url+'">', closeWith:'</a>', placeHolder:url.substr(url.lastIndexOf("/") + 1) }); }
	}
	lastType = '';
}
-->
</script>