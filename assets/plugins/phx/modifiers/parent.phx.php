<?php

/* Retrived from http://wiki.modxcms.com/index.php/PHx/CustomModifiers
    * description: get specified document field from parent document (id)
    * usage: [+variable:parent=`field`+]
    * defaults to pagetitle 
*/	
	
$field = (strlen($options)>0) ? $options : 'pagetitle';
$parent = $modx->getParent($output,1,$field);
return $parent[$field];

?>