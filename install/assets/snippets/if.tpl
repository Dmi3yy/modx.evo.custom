//<?php
/**
 * if
 * 
 * Выводит строку из параметра yes, если в параметре in не пусто; иначе выводит строку из параметра no
 *
 * @category 	snippet
 * @version 	0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 */

/**
** if snippet
** Выводит строку из параметра then, если в параметр if равен is ; иначе выводит строку из параметра else
** !Не может использоваться в поле "Содержание документа"
** !Если в строках указан непосредственно html код, необходимо заменять "=" на "@eq"; следить, чтобы
    не было знака "&"
**
**  @Author: dmi3yy (dmi3yy@gmail.com)
**/

$if = isset($if) ? $if : ''; //ожидается значение TV в виде [*tvname*]
$is = isset($is) ? $is : ''; //ожидается значение с чем сравнивать  
$then = isset($then) ? $then : ''; //код или чанк в виде @CHUNK
$else = isset($else) ? $else : ''; //код или чанк в виде @CHUNK


//echo substr($in,1);

//выводим код, в зависимости от пустоты TV
if (trim($if) ==  trim($is)) { //уточнить условия
  if (substr($then, 0, 6) == '@CHUNK') {
     $then = $modx->getChunk(trim(substr($then, 7)));
  }  
  $then = str_replace('@eq', '=', $then);
  return $then;
} else {
	if (substr($else, 0, 6) == '@CHUNK') {
	  $else = $modx->getChunk(trim(substr($else, 7)));
	}
  $else = str_replace('@eq', '=', $else);
  return $else;
}