//<?php
/**
 * truncate
 * 
 * обрезание (усечение) длины строки 
 *
 * @category 	snippet
 * @version 	1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 */

/**
[[truncate? &text=`[+content+]` &len=`100`]]
**/


 $lenf = $len; //Заменяет символы перевода строки на HTML тег 
   $order = array("\r\n", "\n", "\r");
   $replace = '<br />';
   $what = str_replace($order, $replace, $text);
   if (strlen($what) > $lenf) {
       $what = preg_replace('/^(.{' . $lenf . ',}? ).*$/is', '$1', $what) . '...'; 
   } 
   return $what;