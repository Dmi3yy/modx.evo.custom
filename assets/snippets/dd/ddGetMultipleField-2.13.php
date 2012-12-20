<?php
/** 
 * ddGetMultipleField.php
 * @version 2.13 (2012-09-03)
 * 
 * Выводит поля, сформированные виджетом mm_ddMultipleFiles для плагина ManagerManager.
 * 
 * @uses Если поле необходимо получать, используется сниппет ddGetDocumentField 2.2.
 * @uses Если необходимо типографировать, используется сниппет ddTypograph 1.4.
 * 
 * @param field {separated string} - Строка, содержащая значения с разделителями.
 * @param getField {string} - Имя поля документа, значение которого необходимо получить.
 * @param getId {integer} - ID документа, значение поля которого нужно получить.
 * @param getPublished {0; 1} - Опубликован ли документ, значения поля которого нужно получить. По умолчанию: 1.
 * @param splY {string} - Разделитель между строками в исходной строке. По умолчанию: '||'.
 * @param splX {string} - Разделитель между колонками в исходной строке. По умолчанию: '::'.
 * @param num {integer} - Номер строки, которую нужно вернуть (начиная с которой необходимо возвращать). По умолчанию: 0.
 * @param vals {separated string} - ID строк (значения первой колонки), которые нужно получить. Форамат: строка, разделённая '||' между значениями. По умолчанию: ''.
 * @param count {integer; 'all'} - Количество возвращаемых строк. По умолчанию: 'all'.
 * @param colNum {comma separated string; 'all'} - Номера колонк, которые нужно вернуть. По умолчанию: 'all'.
 * @param sortDir {'ASC'; 'DESC'; 'RAND'; 'REVERSE'; ''} - Направление сортировки. По умолчанию: ''.
 * @param sortBy {comma separated string} - Номер колонки (нумеруются с ноля), по которой необходимо сортировать. Для множественной сортировки параметры указываются через запятую (например: '0,1'). По умолчанию: '0'.
 * @param glueY {string} - Разделитель при выводе между строками. По умолчанию: ''.
 * @param glueX {string} - Разделитель при выводе между колонками. По умолчанию: ''.
 * @param removeEmptyRows {0; 1} - Удалять пустые строки? По умолчанию: 1. 
 * @param removeEmptyCols {0; 1} - Удалять пустые колонки? По умолчанию: 1.
 * @param typographing {0; 1} - Нужно ли типографировать значения? По умолчанию: 0.
 * @param urlencode {0; 1} - Надо URL-кодировать строку? По умолчанию: 0.
 * @param format {'JSON'; ''} - Формат, в котором возвращать результат. По умолчанию: ''.
 * @param tplY {string: chunkName} - Шаблон для вывода строк (параметр format должен быть пустым). Доступные плэйсхолдеры: [+row_number+] (выводит номер строки, начиная с 1), [+val0+],[+val1+],…. По умолчанию: ''.
 * @param tplX {comma separated string: chunkName; 'null'} - Список шаблонов для вывода колонок, через запятую. Если шаблонов меньше, чем колонок, для всех недостающих выставляется последний указанный шаблон. Значение 'null' — без шаблона. Доступный плэйсхолдер: [+val+]. По умолчанию: ''.
 * @param tplWrap {string: chunkName} - Шаблон внешней обёртки. Доступные плэйсхолдеры: [+wrapper+]. По умолчанию: ''.
 * @param placeholders {separated string} - Дополнительные данные, которые необходимо передать (видны только в tplWrap!). Формат: строка, разделённая '::' между парой ключ-значение и '||' между парами. По умолчанию: ''.
 * 
 * @link http://code.divandesign.biz/modx/ddgetmultiplefield/2.13
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

//Если задано имя поля, которое необходимо получить
if (isset($getField)){
	$field = $modx->runSnippet('ddGetDocumentField', array(
		'id' => $getId,
		'published' => $getPublished,
		'field' => $getField
	));
}
//Если задано значение поля
if (isset($field) && $field != ""){
	$splY = isset($splY) ? $splY : '||';
	$splX = isset($splX) ? $splX : '::';
	$num = (!isset($num) || !is_numeric($num)) ? '0' : $num;
	$vals = isset($vals) ? explode('||', $vals) : false;
	$count = (!isset($count) || !is_numeric($count)) ? 'all' : $count;
	$colNum = isset($colNum) ? explode(',', $colNum) : 'all';
	//Хитро-мудро для array_intersect_key
	if (is_array($colNum)) $colNum = array_combine($colNum, $colNum);
	$sortDir = isset($sortDir) ? strtoupper($sortDir) : false;
	$sortBy = isset($sortBy) ? $sortBy : '0';
	$glueY = isset($glueY) ? $glueY : '';
	$glueX = isset($glueX) ? $glueX : '';
	$removeEmptyRows = ($removeEmptyRows == '0') ? false : true;
	$removeEmptyCols = ($removeEmptyCols == '0') ? false : true;
	$typographing = ($typographing == '1') ? true : false;
	$format = isset($format) ? $format : '';
	$tplX = isset($tplX) ? explode(',', $tplX) : false;
	
	//Разбиваем на строки
	$res = explode($splY, $field);
	
	//Перебираем строки, разбиваем на колонки
	foreach ($res as $key => $val){
		$res[$key] = explode($splX, $val);
		
		//Если необходимо получить какие-то конкретные значения
		if ($vals){
			//Если текущего значения в списке нет, сносим нафиг
			if (!in_array($res[$key][0], $vals)) unset($res[$key]);
		}
		
		//Если нужно получить какую-то конкретную колонку (также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее)
		if ($colNum != 'all' && isset($res[$key])){
			//Выбираем только необходимые колонки + Сбрасываем ключи массива
			$res[$key] = array_values(array_intersect_key($res[$key], $colNum));
		}
		
		//Если нужно удалять пустые строки (также проверяем на то, что строка вообще существует, т.к. она могла быть уже удалена ранее)
		if ($removeEmptyRows && isset($res[$key])){
			//Если строка пустая, удаляем
			if (strlen(implode('', $res[$key])) == 0) unset($res[$key]);
		}
	}

	//Сбрасываем ключи массива (пригодится для выборки конкретного значения)
	$res = array_values($res);
	
	//Если шаблоны колонок заданы, но их не хватает
	if ($tplX){
		if (($temp = count($res[0]) - count($tplX)) > 0){
			//Дозабьём недостающие последним
			$tplX = array_merge($tplX, array_fill($temp - 1, $temp, $tplX[count($tplX) - 1]));
		}
		
		$tplX = str_replace('null', '', $tplX);
	}
	
	$result = '';

	//Если что-то есть (могло ничего не остаться после удаления пустых и/или получения по значениям)
	if (count($res) > 0){
		//Если надо сортировать
		if ($sortDir){
			//Если надо в случайном порядке - шафлим
			if ($sortDir == 'RAND'){
				shuffle($res);
			//Если надо просто в обратном порядке
			}else if ($sortDir == 'REVERSE'){
				$res = array_reverse($res);
			}else{
				if(!function_exists('ddMasHoarSort')){
					/**
					 * Функция сортировки многомерного массива по методу Хоара (по нескольким полям одновременно).
					 * @version 1.0 (2011)
					 * 
					 * @param array $arr - исходный массив
					 * @param array $key - массив ключей
					 * @param int $direct - направление сортировки 1|-1
					 * @param int $i - счётчик (внутренняя переменная для рекурсии)
					 * 
					 * @return array
					 */
					function ddMasHoarSort($arr, $key, $direct, $i = 0){
						//В качестве эталона получаем сортируемое значение (по первому условию сортировки) первого элемента
						$tek = $arr[0][$key[$i]];
						
						$masLeft = array();
						$masRight = array();
						$masCent = array();
					
						foreach ($arr as $val){
							//Сравниваем текущее значение со значением эталонного
							$cmpRes = strcmp($val[$key[$i]], $tek);
					
							//Если меньше эталона, отбрасываем в массив меньших
							if ($cmpRes * $direct < 0){
								$masLeft[] = $val;
							//Если больше - в массив больших
							}else if ($cmpRes * $direct > 0){
								$masRight[] = $val;
							//Если раво - в центральный
							}else{
								$masCent[] = $val;
							}
						}
						
						//Массивы меньших и массивы больших прогоняем по тому же алгоритму (если в них что-то есть)
						$masLeft = (count($masLeft) > 1) ? ddMasHoarSort($masLeft, $key, $direct, $i) : $masLeft;
						$masRight = (count($masRight) > 1) ? ddMasHoarSort($masRight, $key, $direct, $i) : $masRight;
						//Массив одинаковых прогоняем по следующему условию сортировки (если есть условие и есть что сортировать)
						$masCent = ((count($masCent) > 1) && $key[$i + 1]) ? ddMasHoarSort($masCent, $key, $direct, $i + 1) : $masCent;
					
						//Склеиваем отсортированные меньшие, средние и большие
						return array_merge($masLeft, $masCent, $masRight);
					}
				}
				//Сортируем результаты
				$sortDir = ($sortDir == 'ASC') ? 1 : -1;
				$res = ddMasHoarSort($res, explode(',', $sortBy), $sortDir);
			}
		}
		
		//Обрабатываем слишком большой индекс
		if (!$res[$num]) $num = count($res) - 1;
		
		//Если нужны все элементы
		if ($count == 'all'){
			$res = array_slice($res, $num);
		}else{
			$res = array_slice($res, $num, $count);
		}
	
		//Если вывод в формате JSON
		if ($format == 'JSON'){
			//Добавляем 'val' к названиям колонок
/* 			foreach ($res as $key => $val){
				$res[$key] = array();
				//Перебираем колонки
				foreach ($val as $k => $v) $res[$key]['val'.$k] = $v;
			} */
			
			//Если нужно выводить только одну колонку
			if ($colNum != 'all' && count($colNum) == 1){
				$res = array_map('implode', $res);
			}
			
			//Если нужно получить какой-то конкретный элемент, а не все
			if ($count == '1'){
				$result = json_encode($res[$num]);
			}else{
				$result = json_encode($res);
			}
			
			//Это чтобы модекс не воспринимал как вызов сниппета
			$result = strtr($result, array('[[' => '[ [', ']]' => '] ]'));
		}else{
			/*//Если вывод в формате изображения
			if ($format == 'img'){
				foreach ($res as $key => $val) $res[$key] = '<img src="'.$val['val1'].'" alt="'.$val['val0'].'" />';
			//Если вывод в формате ссылки
			}else if ($format == 'link'){
				foreach ($res as $key => $val) $res[$key] = '<a href="'.$val['val1'].'">'.$val['val0'].'</a>';
			//Если вывод по шаблону
			}else */
			if (isset($tplY)){
				//Перебираем строки
				foreach ($res as $key => $val){
					$res[$key] = array();
					//Перебираем колонки
					foreach ($val as $k => $v){
						//Если нужно удалять пустые значения
						if ($removeEmptyCols && !strlen($v)){
							$res[$key]['val'.$k] = '';
						}else{
							//Если есть шаблоны значений колонок
							if ($tplX && strlen($tplX[$k])){
								$res[$key]['val'.$k] = $modx->parseChunk($tplX[$k], array('val' => $v), '[+', '+]');
							}else{
								$res[$key]['val'.$k] = $v;
							}
						}
					}
					$res[$key]['row_number'] = $key + 1;
					$res[$key] = $modx->parseChunk($tplY, $res[$key], '[+', '+]');
				}
			}else{
				foreach ($res as $key => $val){
					//Если есть шаблоны значений колонок
					if ($tplX){
						foreach ($val as $k => $v){
							if ($removeEmptyCols && !strlen($v)){
								unset($val[$k]);
							}else{
								if ($tplX && strlen($tplX[$k]))
									$val[$k] = $modx->parseChunk($tplX[$k], array('val' => $v), '[+', '+]');
							}
						}
					}
					$res[$key] = implode($glueX, $val);
				}
			}
			$result = implode($glueY, $res);
		}
	
		//Если оборачивающий шаблон задан, парсим его
		if (isset($tplWrap)){
			$res = array();
			
			//Элемент массива 'wrapper' должен находиться самым первым, иначе дополнительные переданные плэйсхолдеры в тексте не найдутся! 
			$res['wrapper'] = $result;
			
			//Если есть дополнительные данные
			if (isset($placeholders)){
				$arrPlaceholders = array();
				//Разбиваем по парам
				$placeholders = explode('||', $placeholders);
				foreach ($placeholders as $val){
					//Разбиваем на ключ-значение
					$val = explode('::', $val);
					$res[$val[0]] = $val[1];
				}
			}
			$result = $modx->parseChunk($tplWrap, $res, '[+','+]');
		}
	
		//Если нужно типографировать
		if ($typographing) $result = $modx->runSnippet('ddTypograph', array('text' => $result));
		//Если нужно URL-кодировать строку
		if ($urlencode) $result = rawurlencode($result);
	}
	return $result;
}
?>