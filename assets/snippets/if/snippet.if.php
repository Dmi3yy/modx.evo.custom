<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
** if snippet
** [[if&is=`[*id*]:=:4:or:[*parent*]:in:5,6,5,7,8,9` &then=`[[if&is=`0||=||0` &then=`true` &else=`false` &separator=`||`]]` &else=`@TPL:else`]]
** [[if?is=`[*id*]:is:1:or:[*id*]:is:2:and:[*parent*]:is:5:or:[*parent*]:in:2,3,4` &then=`true` &else=`false`]]
**
** Все выражения обрабатываются по логике (....:or:is:.... ) :and: (...:!empty:.....)
** выражение and делит условие на 2 части, которые в конце в конце сравниваются к true
**
** Пример №1
** Выводить акцию нужно только в каталоге с ID = 5
** [[if?is=`[*parent*]:=:5` &then=`@TPL:akcia`]]
**
** Пример №2
** Выводить акцию нужно только в каталоге с ID = 5 или в каталогас с шаблоном №7,8,9
** [[if?is=`[*parent*]:=:5:or:[*template*]:in:7,8,9` &then=`@TPL:akcia`]]
**
** Пример №3
** Выводить акцию нужно только в каталоге с ID = 5 и только в ресурсе с шаблоном №2
** [[if?is=`[*parent*]:=:5:and:[*template*]:=:7` &then=`@TPL:akcia`]]
**
** Пример №4
** Выводить акцию нужно только в каталоге с ID = 5 и ( только в ресурсе с шаблоном №2  или в других шаблонах но с ТВ `show_akcia`=1
** [[if?is=`[*parent*]:=:5:and:[*template*]:=:7:or:[*show_akcia*]:=1` &then=`@TPL:akcia`]]
**
** Пример №5
** Выводить акцию только для товаров с ценой в диапазоне >300$     <=700$
** [[if?is=`[*price*]:>:300:and:[*price*]:<=:700` &then=`@TPL:akcia`]]
**
**
**  Пример №6
**  Выводить при кратности записи дитто 3
**  [[if?is=`[+ditto_iteration+]:%:3` &then=`true` &else=`false`]]
**
**  Пример №7
**  Выводить при кратности записи дитто 3 но с умножением значения
**  [[if?is=`[+ditto_iteration+]*2:%:3` &then=`true` &else=`false` &math=`on`]]
**
**  Пример №8
**  Выводить значение математического выражения
**  [[if?is=`[+ditto_iteration+]*2` &math=`on`]]
**
**  только с пропатченым парсером MODx:
**  [[if?is=`[*id*]:>:2` &then=`<a href="[~[*id*]~]">[*pagetitle*]</a>`]]
**
**  Операторы:
**  (is,=) , (not,!=) , (>,gt) , (<,lt) , (>=,gte) , (lte,<=) , (isempty,empty) , (not_empty,!empty)
**  (null, is_null) , (in_array, inarray, in) , (not_in,!in)
**
**
**
** ===============================================================================================
** Вкусности
** [[if?is=`eval('global $iteration;$iteration++;echo $iteration;')` &math=`on`]]   // итерация в Ditto,Wayfinder и других каталожниках
** [[if?is=`:is:` &then=`@eval: echo str_replace('<br/>','','[*pagetitle*]');`]]    // 'главное<br/> меню' -> 'главное меню' 
** [[if?is=`:is:` &then=`@eval: echo number_format('[*price*]', 2, ',', ' ');`]]    // '1000000,89' -> '1 000 000,89'
**
**
**  @Author: Bumkaka
**  RussAndRussky.org.ua
**/
  //echo $is;
$s=empty($separator)?':':$separator;
$opers=explode($s,$is);
$subject=$opers[0];
$eq=true;
$and=false;
for ($i=1;$i<count($opers);$i++){
  	if ($opers[$i]=='or') {$or=true;$part_eq=$eq;$eq=true;continue;}
    if ($or) {$subject=$opers[$i];$or=false;continue;}
  
    if ($opers[$i]=='and') {
      $lp=1;
      $and=true;
      if (!empty($part_eq)){if ($part_eq||$eq){$left_part=true;}} else {$left_part=$eq?true:false;}
      $eq=true;unset($part_eq);
      continue;
    }
	if ($and) {$subject=$opers[$i];$and=false;continue;}
	
	$operator = $opers[$i];
	$operand  = $opers[$i+1];
  
	if (isset($subject)) {
		if (!empty($operator)) {
      if ($math=='on' && !empty($subject)) {eval('$subject='.$subject.';');}
			$operator = strtolower($operator);
      
			switch ($operator) {
   
        case '%':
        $output = ($subject %$operand==0) ? true: false;$i++;
        break;
       
				case '!=':
				case 'not':$output = ($subject != $operand) ? true: false;$i++;
					break;
				case '<':
				case 'lt':$output = ($subject < $operand) ? true : false;$i++;
					break;
				case '>':
				case 'gt':$output = ($subject > $operand) ? true : false;$i++;
					break;
				case '<=':
				case 'lte':$output = ($subject <= $operand) ? true : false;$i++;
					break;
				case '>=':
				case 'gte':$output = ($subject >= $operand) ? true : false;$i++;
					break;
				case 'isempty':
				case 'empty':$output = empty($subject) ? true : false;
					break;
				case '!empty':
				case 'notempty':
				case 'isnotempty':$output = empty($subject) || $subject == '' ? false : true;
					break;
				case 'isnull':
				case 'null':$output = $subject == null || strtolower($subject) == 'null' ? true : false;
					break;
				case 'inarray':
				case 'in_array':
				case 'in':
					$operand = explode(',',$operand);
					$output = in_array($subject,$operand) ? true : false;
					$i++;
					break;
				 case 'not_in':
				 case '!in':
				 case '!inarray':
					$operand = explode(',',$operand);
					$output = in_array($subject,$operand) ? false : true;
					$i++;
					break;
			  
				case '==':
				case '=':
				case 'eq':
				case 'is':
				default:
        $output = ((string)$subject == (string)$operand) ? true : false;
        $i++;
				break;
			}     
     
			$eq=$output?$eq:false;
   
		}
	}
}
if ($lp==1){
  if ($left_part) {
	if (!empty($part_eq)){
    	if ($part_eq||$eq){$output=$then;}
  	} else {
    	$output=$eq?$then:$else;
  	}
  } 
  else 
  {
    $output=$else;
  }
} else {
	if (!empty($part_eq)){
		if ($part_eq||$eq){
			$output=$then;
		}
	} else {$output=$eq?$then:$else;}
}
if (strpos($output,'@TPL:')!==FALSE){$output='{{'.(str_replace('@TPL:','',$output)).'}}';}

if (substr($output,0,6) == "@eval:") {
  ob_start();
	eval(substr($output,6));
	$output = ob_get_contents();  
	ob_end_clean(); 
}
if (empty($then)&&empty($else)) {
  if ($math=='on') {eval('$subject='.$subject.';');}
  return $subject;
}

return $output;
?>