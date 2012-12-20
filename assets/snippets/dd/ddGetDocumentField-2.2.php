<?php
/** 
 * ddGetDocumentField.php
 * @version 2.2 (2012-03-10)
 * 
 * Получает необходимое поле документа по id (и TV в том числе)
 * 
 * @uses Если необходимо типографировать, используется сниппет ddTypograph.
 * 
 * @todo Пролема данной версии заключается в том, что она получает данные либо по опубликованному документу, либо по неопубликованному. Надо сделать так, чтобы по любым получал.
 * 
 * @param id {integer} - id документа, если не задан - текущий.
 * @param field {comma separated string} - Поля документа, которые надо получить.
 * @param alternateField {comma separated string} - Поля, которые надо отображать в случае, если значение основного поля пустое. По умолчанию: ''.
 * @param numericNames {0; 1} - Выводить имена полей в качестве порядкового номера (например: 'field0', 'field1' и т.д.). По умолчанию: 0.
 * @param published {0; 1} - Опубликован ли документ. По умолчанию: 1.
 * @param typographing {0; 1} - Нужно ли типографировать? По умолчанию: 0.
 * @param screening {0; 1} - Надо ли экранировать спец. символы? По умолчанию: 0.
 * @param urlencode {0; 1} - Надо URL-кодировать строку? По умолчанию: 0.
 * @param tpl {string} - Шаблон (имя чанка), ко которому выводить. По умолчанию: ''.
 * @param glue {string} - Разделитель при выводе между значениями. По умолчанию: ''.
 * @param format {string} - Формат, в котором возвращать результат. Доступные значения: 'JSON'. По умолчанию: ''.
 * @param placeholders {separated string} - Дополнительные данные, которые необходимо передать. Формат: строка, разделённая '::' между парой ключ-значение и '||' между парами.  По умолчанию: ''.
 * @param mode {string} - Режим работы. Значения: 'ajax'. В этом случае id берётся из массива $_REQUEST. Используйте параметр securityFields! По умолчанию: ''.
 * @param securityFields {separated string} - Поля и значения, по которым происходит проверка безопасности. Формат: поле:значение|поле:значение. По умолчанию: ''. 
 * 
 * @link http://code.divandesign.biz/modx/ddgetdocumentfield/2.2
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

//Если поля передали
if (isset($field)){
	$numericNames = ($numericNames == '1') ? true : false;
	$published = ($published == '0') ? 0 : 1;
	$screening = ($screening == '1') ? true : false;
	$urlencode = ($urlencode == '1') ? true : false;
	$typographing = ($typographing == '1') ? true : false;
	$glue = isset($glue) ? $glue : '';
	$format = isset($format) ? $format : '';

	//Если данные нужно получать аяксом
	if ($mode == 'ajax'){
		$id = $_REQUEST['id'];
		
		//Если заданы поля для проверки безопасности
		if (isset($securityFields)){
			//Получаем имена полей безопасности и значения
			$securityFields = explode('|', $securityFields);
			$securityVals = array();
	
			foreach ($securityFields as $key => $val){
				$temp = explode(':', $val);
				$securityFields[$key] = $temp[0];
				$securityVals[$temp[0]] = $temp[1];
			}
			
			//Получаем значения полей безопасности у конкретного документа
			//TODO: Надо бы сделать получение полей безопасности вместе с обычными полями и последующую обработку, но пока влом
			$docSecurityFields = $modx->getTemplateVarOutput($securityFields, $id, $published);
			
			//Если по каким-то причинам ничего не получили, нахуй с пляжу
			if (!$docSecurityFields || count($docSecurityFields) == 0) return;
			
			//Перебираем полученные значения, если хоть одно не совпадает с условием, выкидываем
			foreach ($docSecurityFields as $key => $val){
				if ($val != $securityVals[$key]) return;
			}
		}
	}else{
		$id = isset($id) ? $id : $modx->documentIdentifier;
	}
	
	//Получаем все необходимые поля
	$field = explode(',', $field);
	$result = $modx->getTemplateVarOutput($field, $id, $published);
	
	//Если по каким-то причинам ничего не получилось, нахуй с пляжу
	if (!$result) return;
	
	//Если заданы альтернативные поля
	//TODO: Можно переделать на получение альтернативных полей сразу с основными, а потом обрабатывать, но как-то влом
	if (isset($alternateField)){
		$alternateField = explode(',', $alternateField);
		$alter = $modx->getTemplateVarOutput($alternateField, $id, $published);
	}
	
	$resultStr = ''; $emptyResult = true; $i = 0;

	//Перебираем полученные результаты
	foreach ($result as $key => $value){
		//Если значение поля пустое, пытаемся получить альтернативное поле (и сразу присваиваем) и если оно НЕ пустое, запомним 
		if (($result[$key] != '') || isset($alternateField) && (($result[$key] = $alter[$alternateField[array_search($key, $field)]]) != '')){
			$emptyResult = false;
		}
		
		//Если имена полей надо преобразовывать в цифровые
		if ($numericNames){
			//Запоминаем имя по номеру
			$result['field'.$i] = $result[$key];
			//Убиваем старое (ибо зачем нам дубликаты?)
			unset($result[$key]);
		}
		
		$i++;
	}

	//Если результаты непустые
	if (!$emptyResult){
		//Если вывод в формате JSON
		if ($format == 'JSON'){
			$resultStr = json_encode($result);
		//Если задан шаблон
		}else if (isset($tpl)){
			//Если есть дополнительные данные
			if (isset($placeholders)){
				//Разбиваем по парам
				$placeholders = explode('||', $placeholders);
				foreach ($placeholders as $val){
					//Разбиваем на ключ-значение
					$val = explode('::', $val);
					$result[$val[0]] = $val[1];
				}
			}
	
			$resultStr = $modx->parseChunk($tpl, $result,'[+','+]');
		}else{
			//TODO: При необходимости надо будет обработать удаление пустых значений
			$resultStr = implode($glue, $result);
		}
			
		//Если нужно типографировать
		if ($typographing) $resultStr = $modx->runSnippet('ddTypograph', array('text' => $resultStr));
		
		//Если надо экранировать спец. символы
		if ($screening){
			$resultStr = str_replace("\r\n",' ',$resultStr);
			$resultStr = str_replace("\n",' ',$resultStr);
			$resultStr = str_replace("\r",' ',$resultStr);
			$resultStr = str_replace(chr(9),' ',$resultStr);
			$resultStr = str_replace('  ',' ',$resultStr);
			$resultStr = str_replace('[+','\[\+',$resultStr);
			$resultStr = str_replace('+]','\+\]',$resultStr);
			$resultStr = str_replace("'","\'",$resultStr);
			$resultStr = str_replace('"','\"',$resultStr);
		}
		
		//Если нужно URL-кодировать строку
		if ($urlencode) $resultStr = rawurlencode($resultStr);
	}
	
	return $resultStr;
}
?>