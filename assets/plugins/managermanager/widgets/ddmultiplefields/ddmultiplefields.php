<?php
/** 
 * mm_ddMultipleFields
 * @version 4.3.3 (2012-09-17)
 * 
 * Позволяет добавлять произвольное количество полей (TV) к одному документу (записывается в одно через разделители).
 *
 * @param tvs {comma separated string} - Имена TV, для которых необходимо применить виджет.
 * @param roles {comma separated string} - Роли, для которых необходимо применить виждет, пустое значение — все роли. По умолчанию: ''.
 * @param templates {comma separated string} - Id шаблонов, для которых необходимо применить виджет, пустое значение — все шаблоны. По умолчанию: ''.
 * @param coloumns {comma separated string} - Типы колонок (field — колонка типа поля, text — текстовая колонка, id — скрытое поле с уникальным идентификатором, select — список с выбором значений (см. coloumnsData)). По умолчанию: 'field'.
 * @param coloumnsTitle {comma separated string} - Названия колонок. По умолчанию: ''.
 * @param colWidth {comma separated string} - Ширины колонок (может быть задана одна ширина). По умолчанию: 180;
 * @param splY {string} - Разделитель между строками. По умолчанию: '||'.
 * @param splX {string} - Разделитель между колонками. По умолчанию: '::'.
 * @param imgW {integer} - Максимальная ширина превьюшки. По умолчанию: 300.
 * @param imgH {integer} - Максимальная высота превьюшки. По умолчанию: 100.
 * @param minRow {integer} - Минимальное количество строк. По умолчанию: 0.
 * @param maxRow {integer} - Максимальное количество строк. По умолчанию: 0 (без лимита).
 * @param coloumnsData {separated string} - Список возможных значений для полей в формате json, через ||. По умолчанию: ''.
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

function mm_ddMultipleFields($tvs='', $roles='', $templates='', $coloumns='field', $coloumnsTitle='', $colWidth='180', $splY='||', $splX='::', $imgW=300, $imgH=100, $minRow=0, $maxRow=0, $coloumnsData=''){

	global $modx, $content;
	$e = &$modx->Event;
	
	if ($e->name == 'OnDocFormRender' && useThisRule($roles, $templates)){
		$output = '';

		$site = $modx->config['site_url'];
		$widgetDir = $site.'assets/plugins/managermanager/widgets/ddmultiplefields/';
		
		if ($coloumnsData){
			$coloumnsDataTemp = explode('||', $coloumnsData);
			$coloumnsData = array();
			foreach ($coloumnsDataTemp as $value){
				//Евалим знение и записываем результат или исходное значени
				$eval = @eval($value);
				$coloumnsData[] = $eval ? addslashes(json_encode($eval)) : $value;
			}
			//Сливаем в строку, что бы передать на клиент
			$coloumnsData = implode('||', $coloumnsData);
		}

		//Стиль превью изображения
		$stylePrewiew = "max-width:{$imgW}px; max-height:{$imgH}px; margin: 4px 0; cursor: pointer;";

		// Which template is this page using?
		if (isset($content['template'])) {
			$page_template = $content['template'];
		} else {
			// If no content is set, it's likely we're adding a new page at top level. 
			// So use the site default template. This may need some work as it might interfere with a default template set by MM?
			$page_template = $modx->config['default_template']; 
		}

		$tvsMas = array();
		// Does this page's template use any image or file or text TVs?
		$tvsTemp = tplUseTvs($page_template, $tvs, 'image');
		if ($tvsTemp){
			foreach($tvsTemp as $v){
				$v['type'] = 'image';
				array_push($tvsMas,$v);
			}
		}
		$tvsTemp = tplUseTvs($page_template, $tvs, 'file');
		if ($tvsTemp){
			foreach($tvsTemp as $v){
				$v['type'] = 'file';
				array_push($tvsMas,$v);
			}
		}
		$tvsTemp = tplUseTvs($page_template, $tvs, 'text');
		if ($tvsTemp){
			foreach($tvsTemp as $v){
				$v['type'] = 'text';
				array_push($tvsMas,$v);
			}
		}

		if (count($tvsMas) == 0){
			return;
		}

		$output .= "// ---------------- mm_ddMultipleFields :: Begin ------------- \n";
		//General functions
		$output .= '
//Если ui-sortable ещё не подключён, подключим
if (!$j.ui || !$j.ui.sortable){'.includeJs($widgetDir.'jquery-ui-1.8.13.custom.min.js').'}

//Проверяем на всякий случай (если вдруг вызывается пару раз)
if (!ddMultiple){
'.includeCss($widgetDir.'ddmultiplefields.css').'
var ddMultiple = {
	//Обновляет мульти-поле, берёт значение из оригинального поля
	updateField: function(id){
		//Если есть текущее поле
		if (ddMultiple[id].currentField){
			//Задаём значение текущему полю (берём у оригинального поля), запускаем событие изменения
			ddMultiple[id].currentField.val($j.trim($j("#"+id).val())).trigger("change.ddEvents");
			//Забываем текущее поле (ибо уже обработали)
			ddMultiple[id].currentField = false;
		}
	},
	//Обновляет оригинальное поле TV, собирая данные по мульти-полям
	updateTv: function(id){
		var masRows = new Array();
		//Перебираем все строки
		$j("#"+id+"ddMultipleField .ddFieldBlock").each(function(){
			var $this = $j(this),
				masCol = new Array(),
				id_field = {index: false, val: false, $field: false};
			
			//Перебираем все колонки, закидываем значения в массив
			$this.find(".ddField").each(function(index){
				//Если поле с типом id
				if (ddMultiple[id].coloumns[index] == "id"){
					id_field.index = index;
					id_field.$field = $j(this);
					
					//Сохраняем значение поля
					id_field.val = id_field.$field.val();
					//Если значение пустое, то генерим
					if (id_field.val == "") id_field.val = (new Date).getTime();
					
					//Обнуляем значение
					id_field.$field.val("");
				}
				//Собираем значения строки в массив
				masCol.push($j.trim($j(this).val()));
			});
			
			var col = masCol.join(ddMultiple[id].splX);
			if (col.length != ((masCol.length - 1) * ddMultiple[id].splX.length)){
				//Проверяем было ли поле с id
				if (id_field.index !== false){
					//Записываем значение в поле
					id_field.$field.val(id_field.val);
					//Обновляем значение в массиве
					masCol[id_field.index] = id_field.val;
					//Пересобираем строку
					col = masCol.join(ddMultiple[id].splX);
				}
				masRows.push(col);
			}
		});

		//Записываем значение в оригинальное поле
		$j("#"+id).attr("value", ddMultiple.maskQuoutes(masRows.join(ddMultiple[id].splY)));

	},
	//Инициализация
	//Принимает id оригинального поля, его значения и родителя поля
	init: function(id, val, target){
		//Делаем таблицу мульти-полей, вешаем на таблицу функцию обновления оригинального поля
		var $ddMultipleField = $j("<table class=\"ddMultipleField\" id=\""+id+"ddMultipleField\"></table>").appendTo(target).
								on("change.ddEvents", function(){ddMultiple.updateTv(id);});
		
		//Если заголовков хватает
		if (ddMultiple[id].coloumnsTitle.length == ddMultiple[id].coloumns.length){
			var text = "";
			//Создадим шапку
			$j.each(ddMultiple[id].coloumnsTitle, function(key, val){
				text += "<th>"+val+"</th>";
			});
			$j("<tr><th></th>"+text+"<th></th></tr>").appendTo($ddMultipleField);
		}
		
		//Делаем новые мульти-поля
		var arr = val.split(ddMultiple[id].splY);
		
		//Проверяем на максимальное и минимальное количество строк
		if (ddMultiple[id].maxRow && arr.length > ddMultiple[id].maxRow) arr.length = ddMultiple[id].maxRow;
		else if (ddMultiple[id].minRow && arr.length < ddMultiple[id].minRow) arr.length = ddMultiple[id].minRow;
		
		for (var i=0, len=arr.length; i<len; i++){
			ddMultiple.makeFieldRow(id, arr[i]);
		}
		
		//Создаём кнопку +
		ddMultiple.makeAddButton(id);
		
		//Добавляем возможность перетаскивания
		$ddMultipleField.sortable({
			items: "tr:has(td)",
			handle: ".ddSortHandle",
			cursor: "n-resize",
			axis: "y",
/*			tolerance: "pointer",*/
/*			containment: "parent",*/
			placeholder: "ui-state-highlight",
			start: function(event, ui){
				ui.placeholder.html("<td colspan=\""+(ddMultiple[id].coloumns.length+2)+"\"><div></div></td>").find("div").css("height", ui.item.height());
			},
			stop: function(event, ui){
				//Находим родителя таблицы, вызываем функцию обновления поля
				ui.item.parents(".ddMultipleField:first").trigger("change.ddEvents");
				ddMultiple.moveAddButton(id);
			}
		});
		//Запускаем обновление, если были ограничения
		if (ddMultiple[id].maxRow || ddMultiple[id].minRow) $ddMultipleField.trigger("change.ddEvents");
		
	},
	//Функция создания строки
	//Принимает id и данные строки
	makeFieldRow: function(id, val){
		//Проверяем привышает ли количество строк максимальное
		if (ddMultiple[id].maxRow && $j("#"+id+"ddMultipleField .ddFieldBlock").length >= ddMultiple[id].maxRow) return;
		var $fieldBlock = $j("<tr class=\"ddFieldBlock "+id+"ddFieldBlock\" ><td class=\"ddSortHandle\"><div></div></td></tr>").appendTo($j("#"+id+"ddMultipleField"));//.on("change.ddEvents",function(){ddMultiple.updateTv(id);});
		
		//Разбиваем переданное значение на колонки
		val = ddMultiple.maskQuoutes(val).split(ddMultiple[id].splX);
		
		var $field;

		//Перебираем колонки
		$j.each(ddMultiple[id].coloumns, function(key){
			if (!val[key]) val[key] = "";
			if (!ddMultiple[id].coloumnsTitle[key]) ddMultiple[id].coloumnsTitle[key] = "";
			if (!ddMultiple[id].colWidth[key] || ddMultiple[id].colWidth[key] == "") ddMultiple[id].colWidth[key] = ddMultiple[id].colWidth[key - 1];
		
			var col = ddMultiple.makeFieldCol($fieldBlock);

			//По умолчанию создаём поле как текстовое
			$field = ddMultiple.makeText(val[key], ddMultiple[id].coloumnsTitle[key], ddMultiple[id].colWidth[key], col);

			//Если текущая колонка является полем
			if(ddMultiple[id].coloumns[key] == "field"){
				ddMultiple[id].makeFieldFunction(id, col);

				//If is file or image
				if (ddMultiple[id].browseFuntion){
					//Create Attach browse button
					$j("<input class=\"ddAttachButton\" type=\"button\" value=\"Вставить\" />").insertAfter($field).on("click", function(){
						ddMultiple[id].currentField = $j(this).siblings(".ddField");
						ddMultiple[id].browseFuntion(id);
					});
				}
			}else if (ddMultiple[id].coloumns[key] == "id"){
				if (!($field.val())){
					$field.val((new Date).getTime());
				}
				col.hide();
			}else if(ddMultiple[id].coloumns[key] == "select"){
				$field.remove();
				ddMultiple.makeSelect(val[key], ddMultiple[id].coloumnsTitle[key], ddMultiple[id].coloumnsData[key], ddMultiple[id].colWidth[key], col);
			}
		
		});

		//Create DeleteButton
		ddMultiple.makeDeleteButton(id, ddMultiple.makeFieldCol($fieldBlock));

		//При изменении и загрузке
		$j(".ddField", $fieldBlock).on("load.ddEvents change.ddEvents",function(){
			$j(this).parents(".ddMultipleField:first").trigger("change.ddEvents");
		});
		
		//Специально для полей, содержащих изображения необходимо инициализировать
		$j(".ddFieldCol:has(.ddField_image) .ddField", $fieldBlock).trigger("change.ddEvents");
		
		return $fieldBlock;
	},
	//Создание колонки поля
	makeFieldCol: function(fieldRow){
		return $j("<td class=\"ddFieldCol\"></td>").appendTo(fieldRow);
	},
	//Make delete button
	makeDeleteButton: function(id, fieldCol){
		$j("<input class=\"ddDeleteButton\" type=\"button\" value=\"×\" />").appendTo(fieldCol).on("click", function(){
			//Проверяем на минимальное количество строк
			if (ddMultiple[id].minRow && $j("#"+id+"ddMultipleField .ddFieldBlock").length <= ddMultiple[id].minRow) return;
			var $this = $j(this),
				$par = $this.parents(".ddFieldBlock:first"),
				$table = $this.parents(".ddMultipleField:first");
			
			//Отчистим значения полей
			$par.find(".ddField").val("");

			//Если больше одной строки, то можно удалить текущую строчку
			if ($par.siblings(".ddFieldBlock").length > 0){
				$par.fadeOut(300,function(){
					//Если контейнер имеет кнопку добалвения, перенесём её
					if ($par.find(".ddAddButton").length > 0){
						ddMultiple.moveAddButton(id, $par.prev(".ddFieldBlock"));
					}
					$par.remove();
					//Инициализируем событие изменения
					$table.trigger("change.ddEvents");
					return;
				});
			}
			//Инициализируем событие изменения
			$table.trigger("change.ddEvents");
		});
	},
	//Функция создания кнопки +, вызывается при инициализации
	makeAddButton: function(id){
		$j("<input class=\"ddAddButton\" type=\"button\" value=\"+\" />").appendTo($j("#"+id+"ddMultipleField .ddFieldBlock:last .ddFieldCol:last")).on("click", function(){
			//Вешаем на кнопку создание новой строки
			$j(this).appendTo(ddMultiple.makeFieldRow(id, "").find(".ddFieldCol:last"));
			$j(this).parents(".ddMultipleField:first").trigger("change.ddEvents");
		});
	},
	//Перемещение кнопки +
	moveAddButton: function(id, target){
		//Если не передали, куда вставлять, вставляем в самый конец
		if (!target) target = $j("#"+id+"ddMultipleField .ddFieldBlock:last");

		//Находим кнопку добавления и переносим куда надо
		$j("#"+id+"ddMultipleField .ddAddButton:first").appendTo(target.find(".ddFieldCol:last"));
	},
	//Make text field
	makeText: function(value, title, width, fieldCol){
		return $j("<input type=\"text\" value=\""+value+"\" title=\""+title+"\" style=\"width:"+width+"px;\" class=\"ddField\" />").appendTo(fieldCol);
	},
	//Make image field
	makeImage: function(id, fieldCol){
		// Create a new preview and Attach a browse event to the picture, so it can trigger too
		$j("<div class=\"ddField_image\"><img src=\"\" style=\""+ddMultiple[id].imageStyle+"\" /></div>").appendTo(fieldCol).hide().find("img").on("click", function(){
			fieldCol.find(".ddAttachButton").trigger("click");
		}).on("load.ddEvents", function(){
			//Удаление дерьма, блеать (превьюшка, оставленная от виджета showimagetvs)
			$j("#"+id+"PreviewContainer").remove();
		});

		//Находим поле, привязываем события
		$j(".ddField", fieldCol).on("change.ddEvents load.ddEvents", function(){
			var $this = $j(this), url = $this.val();

			url = (url != "" && url.search(/http:\/\//i) == -1) ? ("'.$site.'" + url) : url;

			//If field not empty
			if (url != ""){
				//Show preview
				$this.siblings(".ddField_image").show().find("img").attr("src", url);
			}else{
				//Hide preview
				$this.siblings(".ddField_image").hide();
			}
		})/*.trigger("change.ddEvents")*/;
	},
	//Функция создания списка
	makeSelect: function(value, title, data, width, fieldCol){
		var $select = $j("<select class=\"ddField\">");
		if (data){
			var dataMas = $j.parseJSON(data);
			var options = "";
			$j.each(dataMas, function(index){
				options += "<option value=\""+ dataMas[index][0] +"\">" + (dataMas[index][1] ? dataMas[index][1] : dataMas[index][0]) +"</option>";
			});
			$select.append(options);
		}
		if (value) $select.val(value);
		return $select.appendTo(fieldCol);
	},
	//Функция ничего не делает
	makeNull: function(id, fieldCol){return false;},
	//Маскирует кавычки
	maskQuoutes: function(text){
		text = text.replace(/"/g, "&#34;");
		text = text.replace(/\'/g, "&#39;");
		return text;
	}
};
//If we have imageTVs on this page, modify the SetUrl function so it triggers a "change" event on the URL field
if (typeof(SetUrl) != "undefined") {
	var OldSetUrl = SetUrl; // Copy the existing Image browser SetUrl function						
	SetUrl = function(url, width, height, alt){	// Redefine it to also tell the preview to update
		if(lastFileCtrl) {
			var c = $j(document.mutate[lastFileCtrl]);
		} else if(lastImageCtrl) {
			var c = $j(document.mutate[lastImageCtrl]);
		}
		OldSetUrl(url, width, height, alt);
		c.trigger("change");
	};
}
}
		';

		foreach ($tvsMas as $tv){
			if ($tv['type'] == 'image'){
				$browseFuntion = 'BrowseServer';
				$makeFieldFunction = 'makeImage';
			}else if($tv['type'] == 'file'){
				$browseFuntion = 'BrowseFileServer';
				$makeFieldFunction = 'makeNull';
			}else{
				$browseFuntion = 'false';
				$makeFieldFunction = 'makeNull';
			} 
			$output .= '
//Attach new load event
$j("#tv'.$tv['id'].'").on("load.ddEvents", function(event){
	var $this = $j(this), //Оригинальное поле
		id = $this.attr("id");//id оригинального поля

	//Проверим на существование (возникали какие-то непонятные варианты, при которых два раза вызов был)
	if (!ddMultiple[id]){
		//Инициализация текущего объекта с правилами
		ddMultiple[id] = {
			splY: "'.$splY.'",
			splX: "'.$splX.'",
			coloumns: "'.$coloumns.'".split(","),
			coloumnsTitle: "'.$coloumnsTitle.'".split(","),
			coloumnsData: \''.$coloumnsData.'\'.split("||"),
			colWidth: "'.$colWidth.'".split(","),
			imageStyle: "'.$stylePrewiew.'",
			minRow: parseInt("'.$minRow.'", 10),
			maxRow: parseInt("'.$maxRow.'", 10),
			makeFieldFunction: ddMultiple.'.$makeFieldFunction.',
			browseFuntion: '.$browseFuntion.'
		};

		//Скрываем оригинальное поле
		$this.removeClass("imageField").addClass("originalField").hide();

		//Назначаем обработчик события при изменении (необходимо для того, чтобы после загрузки фотки адрес вставлялся в нужное место)
		$this.on("change.ddEvents", function(){
			//Обновляем текущее мульти-поле
			ddMultiple.updateField($this.attr("id"));
		});
		
		//Если это файл или изображение, cкрываем оригинальную кнопку
		if (ddMultiple[id].browseFuntion){$this.next("input[type=button]").hide();}

		//Создаём мульти-поле
		ddMultiple.init(id, $this.val(), $this.parent());
	}
}).trigger("load");
			';
		}

		$output .= "\n// ---------------- mm_ddMultipleFields :: End -------------";

		$e->output($output . "\n");
	}
} // end of widget

?>