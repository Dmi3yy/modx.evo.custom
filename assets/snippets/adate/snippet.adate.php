<?php
//пример: [[aDate? &date=`[*createdon*]` &date2=`[*pub_date*]`]]
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$output = "";
$format = "%d.%m.%Y";
if (isset($date2) && $date2>0 ) {
    $output = strftime($format,$date2);
}
else{
    $output = strftime($format,$date);
}

$date=explode(".", $output);
switch ($date[1]){
case 1: $m='января'; break;
case 2: $m='февраля'; break;
case 3: $m='марта'; break;
case 4: $m='апреля'; break;
case 5: $m='мая'; break;
case 6: $m='июня'; break;
case 7: $m='июля'; break;
case 8: $m='августа'; break;
case 9: $m='сентября'; break;
case 10: $m='октября'; break;
case 11: $m='ноября'; break;
case 12: $m='декабря'; break;
}
return $date[0].'&nbsp;'.$m.'&nbsp;'.$date[2];
?>