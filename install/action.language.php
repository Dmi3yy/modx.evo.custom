<?php
$ph['lang_options']        = get_lang_options();
$ph['_lang_begin']         = $_lang['begin'];
$ph['_lang_btnnext_value'] = $_lang['btnnext_value'];
echo  parse(get_src_content(),$ph);



function get_langs()
{
	$langs = array();
	foreach(glob('lang/*.inc.php') as $path)
	{
		$langs[] = substr($path,5,strpos($path,'.inc.php')-5);
	}
	sort($langs);
	return $langs;
}

function get_lang_options()
{
	$langs = get_langs();
	
	foreach ($langs as $language)
	{
		$abrv_language = explode('-',$language);
		$option[] = '<option value="' . $language . '"'. (($abrv_language[0] == 'japanese') ? ' selected="selected"' : null) .'>' . ucwords($abrv_language[0]). '</option>'."\n";
	}
	return join("\n",$option);
}

function get_src_content()
{
	$src = <<< EOT
<form name="install" id="install_form" action="index.php?action=mode" method="post">
    <h2>Choose language:&nbsp;&nbsp;</h2>
    <select name="language">
    [+lang_options+]
    </select>
        <p class="buttonlinks">
            <a style="display:inline;" href="javascript:document.getElementById('install_form').submit();" title="[+_lang_begin+]"><span>[+_lang_btnnext_value+]</span></a>
        </p>
</form>
EOT;
	return $src;
}
