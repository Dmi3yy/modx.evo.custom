//<?php
/**
 * isset
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
** isset snippet
** Выводит строку из параметра yes, если в параметре in не пусто; иначе выводит строку из параметра no
** !Не может использоваться в поле "Содержание документа"
** !Если в строках указан непосредственно html код, необходимо заменять "=" на "@eq"; следить, чтобы
    не было знака "&"
**
**  @Author: alooze (a.looze@gmail.com)
**/

$in = isset($in) ? $in : ''; //ожидается значение TV в виде [*tvname*]
$yes = isset($yes) ? $yes : ''; //код или чанк в виде @CHUNK
$no = isset($no) ? $no : ''; //код или чанк в виде @CHUNK


//echo substr($in,1);

//выводим код, в зависимости от пустоты TV
if (trim($in) != '' && $in != '0') { //уточнить условия
  if (substr($yes, 0, 6) == '@CHUNK') {
     $yes = $modx->getChunk(trim(substr($yes, 7)));
  }
  $yes = str_replace('@eq', '=', $yes);
  return $yes;
} else {
  if (substr($no, 0, 6) == '@CHUNK') {
	$no = $modx->getChunk(trim(substr($no, 7)));
  }
  $no = str_replace('@eq', '=', $no);
  return $no;
}