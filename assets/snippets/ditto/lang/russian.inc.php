<?php
/**
 * Ditto Snippet - language strings for use in the snippet
 * Filename:       assets/snippets/ditto/lang/russian.inc.php
 * Language:       Russian
 * Encoding:       Windows-1251
 * Translated by:  Russian MODx community, Jaroslav Sidorkin, based on translation by modx.ru
 * Date:           9 May 2010
 * Version:        2.1.0
*/
setlocale (LC_ALL, 'ru_RU.CP1251');
$_lang['language'] = "russian";

$_lang['abbr_lang'] = "ru";

$_lang['file_does_not_exist'] = "￭￥ ￱￳￹￥￱￲￢￳￥￲. ￏ￮￦￠￫￳￩￱￲￠, ￯￰￮￢￥￰￼￲￥ ￴￠￩￫.";

$_lang['extender_does_not_exist'] = "- ￤￠￭￭￮￥ ￰￠￱￸￨￰￥￭￨￥ ￮￲￱￳￲￱￲￢￳￥￲. ￏ￮￦￠￫￳￩￱￲￠, ￯￰￮￢￥￰￼￲￥ ￥￣￮.";

$_lang['default_template'] = <<<TPL

    <div class="ditto_item" id="ditto_item_[+id+]">
        <h3 class="ditto_pageTitle"><a href="[~[+id+]~]">[+pagetitle+]</a></h3>
        <div class="ditto_documentInfo">￀￢￲￮￰: <strong>[+author+]</strong> ￮￲ [+date+]</div>
        <div class="ditto_introText">[+introtext+]</div>
    </div>

TPL;
$_lang['missing_placeholders_tpl'] = 'ￂ ￮￤￭￮￬ ￨￧ ￸￠￡￫￮￭￮￢ Ditto (￷￠￭￪￮￢) ￭￥￤￮￱￲￠￥￲ ￲￥￣￮￢, ￯￰￮￢￥￰￼￲￥ ￱￫￥￤￳￾￹￨￩ ￸￠￡￫￮￭: <br /><br /><hr /><br /><br />';

$_lang["bad_tpl"] = "<p>&[+tpl+] ￨￫￨ ￭￥ ￱￮￤￥￰￦￨￲ ￪￠￪￨￵-￫￨￡￮ ￯￫￥￩￱￵￮￫￤￥￰￮￢, ￨￫￨ ￿￢￫￿￥￲￱￿ ￭￥￢￥￰￭￻￬ ￭￠￧￢￠￭￨￥￬ ￷￠￭￪￠, ￡￫￮￪￮￬ ￪￮￤￠ ￨￫￨ ￨￬￥￭￥￬ ￴￠￩￫￠. ￏ￮￦￠￫￳￩￱￲￠, ￯￰￮￢￥￰￼￲￥ ￥￣￮.</p>";

$_lang['no_documents'] = '<p>ￇ￠￯￨￱￥￩ ￭￥ ￭￠￩￤￥￭￮.</p>';

$_lang['resource_array_error'] = 'ￎ￸￨￡￪￠ ￬￠￱￱￨￢￠ ￰￥￱￳￰￱￮￢';
 
$_lang['prev'] = "&lt; ￭￠￧￠￤";

$_lang['next'] = "￤￠￫￥￥ &gt;";

$_lang['button_splitter'] = "|";

$_lang['default_copyright'] = "[(site_name)] 2009";

$_lang['invalid_class'] = "ￍ￥￢￥￰￭￻￩ ￪￫￠￱￱ Ditto. ￏ￮￦￠￫￳￩￱￲￠, ￯￰￮￢￥￰￼￲￥ ￥￣￮.";

$_lang['none'] = "ￍ￥￲";

$_lang['edit'] = "￐￥￤￠￪￲￨￰￮￢￠￲￼";

$_lang['dateFormat'] = "%d.%b.%y %H:%M";

// Debug Tab Names

$_lang['info'] = "￈￭￴￮￰￬￠￶￨￿";

$_lang['modx'] = "MODx";

$_lang['fields'] = "ￏ￮￫￿";

$_lang['templates'] = "￘￠￡￫￮￭￻";

$_lang['filters'] = "ￔ￨￫￼￲￰￻";

$_lang['prefetch_data'] = "ￏ￰￥￤￢￠￰￨￲￥￫￼￭￻￥ ￤￠￭￭￻￥";

$_lang['retrieved_data'] = "ￏ￮￫￳￷￥￭￭￻￥ ￤￠￭￭￻￥";

// Debug Text

$_lang['placeholders'] = "ￏ￫￥￩￱￵￮￫￤￥￰￻";

$_lang['params'] = "ￏ￠￰￠￬￥￲￰￻";

$_lang['basic_info'] = "ￎ￱￭￮￢￭￠￿ ￨￭￴￮￰￬￠￶￨￿";

$_lang['document_info'] = "￈￭￴￮￰￬￠￶￨￿ ￮ ￰￥￱￳￰￱￥";

$_lang['debug'] = "ￎ￲￫￠￤￪￠";

$_lang['version'] = "ￂ￥￰￱￨￿";

$_lang['summarize'] = "ￗ￨￱￫￮ ￢￻￢￮￤￨￬￻￵ ￧￠￯￨￱￥￩ (summarize):";

$_lang['total'] = "ￂ￱￥￣￮ ￢ ￡￠￧￥ ￤￠￭￭￻￵:";

$_lang['sortBy'] = "￑￮￰￲￨￰￮￢￠￲￼ ￯￮ (sortBy):";

$_lang['sortDir'] = "ￏ￮￰￿￤￮￪ ￱￮￰￲￨￰￮￢￪￨ (sortDir):";

$_lang['start'] = "ￍ￠￷￠￲￼ ￱";
	 
$_lang['stop'] = "ￇ￠￪￮￭￷￨￲￼ ￭￠";

$_lang['ditto_IDs'] = "ID";

$_lang['ditto_IDs_selected'] = "ￂ￻￡￰￠￭￭￻￥ ID";

$_lang['ditto_IDs_all'] = "ￂ￱￥ ID";

$_lang['open_dbg_console'] = "ￎ￲￪￰￻￲￼ ￪￮￭￱￮￫￼ ￮￲￫￠￤￪￨";

$_lang['save_dbg_console'] = "￑￪￠￷￠￲￼ ￮￲￷￥￲ ￮￲￫￠￤￪￨";

?>