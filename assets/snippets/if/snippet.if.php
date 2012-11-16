<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
** if snippet
** [[if&is=`[*id*]:=:4:or:[*parent*]:in:5,6,5,7,8,9` &then=`[[if&is=`0||=||0` &then=`true` &else=`false` &separator=`||`]]` &else=`@TPL:else`]]
**/
$s=empty($separator)?':':$separator;
$opers=explode($s,$is);
$subject=$opers[0];
$eq=true;
$and=false;
for ($i=1;$i<count($opers);$i++){
  	if ($opers[$i]=='or') {$or=true;$part_eq=$eq;$eq=true;continue;}
    if ($or) {$subject=$opers[$i];$or=false;continue;}
  
    if ($opers[$i]=='and') {
      $and=true;
      if (!empty($part_eq)){if ($part_eq||$eq){$left_part=true;}} else {$left_part=$eq?true:false;}
      $eq=true;unset($part_eq);
      continue;
    }
	if ($and) {$subject=$opers[$i];$and=false;continue;}
	
	$operator = $opers[$i];
	$operand  = $opers[$i+1];
  if ($math=='on') {eval('$subject='.$subject.';');}
	if (isset($subject)) {
		if (!empty($operator)) {
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
				case 'isnotempty':$output = !empty($subject) && $subject != '' ? true : false;
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
				default:$output = ($subject == $operand) ? true : false;$i++;
					break;
			}     
			$eq=$output?$eq:false;
		}
	}
}
if (!empty($left_part)){
  if ($left_part) {
	if (!empty($part_eq)){if ($part_eq||$eq){$output=$then;}} else {$output=$eq?$then:$else;}
  } 
  else 
  {$output=$else;}
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
unset($is,$then,$else,$output,$opers,$subject,$eq,$operand,$chunk,$part_eq);
?>