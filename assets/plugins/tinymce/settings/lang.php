<?php
function get_mce_lang($lang)
{
	switch($lang)
	{
		case 'japanese-utf8'         :
		case 'japanese-euc'          : $js_lang = 'ja'; break;
		case 'english'               :
		case 'english-british'       : $js_lang = 'en'; break;
		default                      : $js_lang = 'en';
	}
	return $js_lang;
}
