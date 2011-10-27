<?php

/*
Retrieved from http://wiki.modxcms.com/index.php/PHx/CustomModifiers
    *  description: Removes the http:// from a URL, to create a display-friendly web address
    * usage: [+string:nohttp+] 
	
	*/
	
$url = str_replace('http://', '', $output);
return $url;
?>