<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$ph['_lang_cleaningup']     = $_lang['cleaningup'];
$ph['_lang_actioncomplete'] = $_lang['actioncomplete'];

if($_REQUEST['r']==10)
	$ph['reload'] = 'top.location.startrefresh(10);';
elseif($_REQUEST['dv']==1 && $_REQUEST['id']!='')
	$ph['reload'] = "document.location.href='index.php?a=3&id={$_REQUEST['id']};'";
else
	$ph['reload'] = 'document.location.href="index.php?a=2"';

$tpl = get_tpl();
$haystack = array('[+_lang_cleaningup+]','[+_lang_actioncomplete+]','[+reload+]');
echo str_replace($haystack,$ph,$tpl);

function get_tpl()
{
	$tpl = <<< EOT
<h1>[+_lang_cleaningup+]</h1>
<div class="sectionBody">
	<p>[+_lang_actioncomplete+]</p>
	<script type="text/javascript">
	function goHome()
	{
		[+reload+]
	}
	x=window.setTimeout('goHome()',300);
	</script>
</div>
EOT;
	return $tpl;
}
