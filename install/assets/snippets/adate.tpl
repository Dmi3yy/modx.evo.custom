//<?php
/**
 * aDate
 * 
 * Вывод даты с русским названием месяца
 *
 * @category 	snippet
 * @version 	0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 */

//пример: [[aDate? &date=`[*createdon*]` &date2=`[*pub_date*]`]]

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
echo $date[0].'&nbsp;'.$m.'&nbsp;'.$date[2];
