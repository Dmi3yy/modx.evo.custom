<?php

/* Retrieved from http://wiki.modxcms.com/index.php/PHx/CustomModifiers
    * description: returns the 7bit representation of a string
    * usage: [+string:7bit+] 
*/

$text = mb_convert_encoding($output,'HTML-ENTITIES',mb_detect_encoding($output));
$text = preg_replace(array('/&szlig;/','/&(..)lig;/','/&([aouAOU])uml;/','/&(.)[^;]*;/'),array('ss',"$1","$1".'e',"$1"),$text);
return $text;

?>