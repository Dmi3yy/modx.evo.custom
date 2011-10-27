<?php

// Retrieved from http://wiki.modxcms.com/index.php/PHx/CustomModifiers
// description: parse bb code (also escapes all html and MODx tags characters)
// usage: [+variable:bbcode+] 
 
$string = preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", htmlspecialchars($output));
$string = preg_replace('~\[b\](.+?)\[/b\]~is', '<b>\1</b>', $string);
$string = preg_replace('~\[i\](.+?)\[/i\]~is', '<i>\1</i>', $string);
$string = preg_replace('~\[u\](.+?)\[/u\]~is', '<u>\1</u>', $string);
$string = preg_replace('~\[link\]www.(.+?)\[/link\]~is', '<a href="http://www.\1">www.\1</a>', $string);
$string = preg_replace('~\[link\](.+?)\[/link\]~is', '<a href="\1">\1</a>', $string);
$string = preg_replace('~\[link=(.+?)\](.+?)\[/link\]~is', '<a href="\1">\2</a>', $string);
$string = preg_replace('~\[url\]www.(.+?)\[/url\]~is', '<a href="http://www.\1">www.\1</a>', $string);
$string = preg_replace('~\[url\](.+?)\[/url\]~is', '<a href="\1">\1</a>', $string);
$string = preg_replace('~\[url=(.+?)\](.+?)\[/url\]~is', '<a href="\1">\2</a>', $string);
$string = preg_replace('~\[img\](.+?)\[/img\]~is', '<img src="\1" alt="[image]" style="margin: 5px 0px 5px 0px" />', $string);
$string = preg_replace('~\[img-l\](.+?)\[/img\]~is', '<img src="\1" alt="[image]" style="border: thin solid #DFE5F2; FLOAT: left; MARGIN-RIGHT: 20px" />', $string);
$string = preg_replace('~\[img-r\](.+?)\[/img\]~is', '<img src="\1" alt="[image]" style="border: thin solid #DFE5F2; FLOAT: right; MARGIN-LEFT: 20px;" />', $string);
$string = str_replace(array("[","]","`"),array("&#91;","&#93;","&#96;"),$string);
return $string;


?>
