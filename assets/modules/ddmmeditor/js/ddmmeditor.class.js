/**
 * ddmmeditor.class.js
 * @version 1.2.3 (2012-08-24)
 * 
 * Описание класса для работы с правилами.
 *
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 **/

//Массив с правилами
var Rules;
//Создаём массив для вывода сохранённых данных
var rulesSave = new Array();
//Шаблон формы для правила
var ruleFormTpl = '<form action="javascript:void(0);" class="ruleForm [+className+]">[+content+]<input type="button" class="del" value="x" /></form>';
//Шаблон поля для выбора новых правил
var newRuleTpl = '<select id="new_role_select" size="10">[+options+]</select>';

//Спивок создаваемых правил
var newRuleSelect = new Array(
	'mm_hideFields',
	'mm_renameField',
	'mm_requireFields',
	'mm_default',
	'mm_changeFieldHelp',
	'mm_hideTemplates',
	'mm_inherit',
	'mm_synch_fields',
	'mm_renameTab',
	'mm_hideTabs',
	'mm_createTab',
	'mm_moveFieldsToTab',
	'mm_widget_tags',
	'mm_widget_showimagetvs',
	'mm_widget_colors',
	'mm_widget_accessdenied',
	'mm_ddMaxLength',
	'mm_ddMultipleFields',
	'mm_ddPatternField',
	'mm_ddGMap',
	'mm_ddYMap',
	'mm_ddSetFieldValue',
	'mm_ddResizeImage',
	'mm_ddAutoFolders',
	'mm_ddNumericFields'
);

var newRuleSelectOut = '';
$.each(newRuleSelect, function(){
	newRuleSelectOut += '<option>' + this + '</option>';
});

//Функция для расширения дочерних функций родительской
function extend(Child, Parent){
	var F = new Function();
	F.prototype = Parent.prototype;
	
	Child.prototype = new F();
	Child.prototype.constructor = Child;
	Child.superclass = Parent.prototype;
}

$(function(){
	//Массив всех правил
	var rulesMas = new Array();
	//Индекс последнего созданного объекта
	var indexParam = 0;
	//Объект со значениями ролей
	var rolesOdj = $.parseJSON(rolesJSON);
	//Объект со значениями шаблонов
	var templatesOdj = $.parseJSON(templatesJSON);
	
/**start*****Глобальный объект управления правилами*/
	Rules = {
		//Создаём правила из всего объекта
		constructorRules: function(rulesJSON){
			var rulesObj = $.parseJSON(rulesJSON);
			//Перебираем объект
			$.each(rulesObj, function(groupName, elem){
				if (groupName == 'comment_top' || groupName == 'comment_bottom'){
					Rules.newComment(elem, groupName);
				}else{
					var group = Rules.newGroup(groupName);
					$.each(elem, function(key, val){
						Rules.newRule(val.name, val.param, group);
					});
				}
			});
		},
		//Создаёт новую группу
		newGroup: function(groupName){
			var group = groupTemplate.clone();
			$('.title span', group).text(groupName);
			group.addClass('closed');
			group.appendTo('#rules_cont');
			
			return group;
		},
		//Создаёт новое правило
		newRule: function(ruleName, ruleParam, group){
			var masParam;
			//Проверяем переданны ли параметры, если нет, то создаём пустой массив с параметрами
			if(ruleParam){
				ruleParam = ruleParam.substr(1, ruleParam.length - 2);
				masParam = ruleParam.split("','");
			}else{
				masParam = new Array();
			}
			
			//Берём имя параметра и создаём определённый класс
			switch(ruleName){
				case 'mm_hideFields':
					rulesMas[indexParam] = new HideFields(masParam);
				break;
				case 'mm_renameField':
					rulesMas[indexParam] = new RenameField(masParam);
				break;
				case 'mm_requireFields':
					rulesMas[indexParam] = new RequireFields(masParam);
				break;
				case 'mm_default':
					rulesMas[indexParam] = new mmDefault(masParam);
				break;
				case 'mm_changeFieldHelp':
					rulesMas[indexParam] = new ChangeFieldHelp(masParam);
				break;
				case 'mm_hideTemplates':
					rulesMas[indexParam] = new HideTemplates(masParam);
				break;
				case 'mm_inherit':
					rulesMas[indexParam] = new Inherit(masParam);
				break;
				case 'mm_synch_fields':
					rulesMas[indexParam] = new SynchFields(masParam);
				break;
				case 'mm_renameTab':
					rulesMas[indexParam] = new RenameTab(masParam);
				break;
				case 'mm_hideTabs':
					rulesMas[indexParam] = new HideTabs(masParam);
				break;
				case 'mm_createTab':
					rulesMas[indexParam] = new CreateTab(masParam);
				break;
				case 'mm_moveFieldsToTab':
					rulesMas[indexParam] = new MoveFieldsToTab(masParam);
				break;
				case 'mm_widget_tags':
					rulesMas[indexParam] = new WidgetTags(masParam);
				break;
				case 'mm_widget_showimagetvs':
					rulesMas[indexParam] = new WidgetShowimagetvs(masParam);
				break;
				case 'mm_widget_colors':
					rulesMas[indexParam] = new WidgetColors(masParam);
				break;
				case 'mm_widget_accessdenied':
					rulesMas[indexParam] = new WidgetAccessdenied(masParam);
				break;
				case 'mm_ddMaxLength':
					rulesMas[indexParam] = new ddMaxLength(masParam);
				break;
				case 'mm_ddMultipleFields':
					rulesMas[indexParam] = new ddMultipleFields(masParam);
				break;
				case 'mm_ddPatternField':
					rulesMas[indexParam] = new ddPatternField(masParam);
				break;
				case 'mm_ddGMap':
					rulesMas[indexParam] = new ddGMap(masParam);
				break;
				case 'mm_ddYMap':
					rulesMas[indexParam] = new ddYMap(masParam);
				break;
				case 'mm_ddSetFieldValue':
					rulesMas[indexParam] = new ddSetFieldValue(masParam);
				break;
				case 'mm_ddResizeImage':
					rulesMas[indexParam] = new ddResizeImage(masParam);
				break;
				case 'mm_ddAutoFolders':
					rulesMas[indexParam] = new ddAutoFolders(masParam);
				break;
				case 'mm_ddNumericFields':
					rulesMas[indexParam] = new ddNumericFields(masParam);
				break;
			}
			Rules.render(indexParam, group);
			indexParam++;
		},
		//Выводим правила
		render: function(indexRender, group){
			//Формируем html-элемент правила
			var elem = $(rulesMas[indexRender].render());
			//Запомним ссылку объект правила
			elem.data('ddRuleIndex', indexRender);
			//Запомним ссылку на html-элемент правила
			rulesMas[indexRender].html = elem;

			//Проверяем на наличие группы
//			if (!group.length){
//				group = $('.group.default');
//				if (group.length == 0){
//					$('#new_group').trigger('click', ['default']);
//					group = $('.group.default');
//				}
//			}
			//Автозаполнение
			tvAutocomplite(elem.find('.input_field'));
			//Выводим правило
			elem.appendTo(group);
		},
		//Сохранение
		save: function(){
			rulesSave = new Array();
			//Перебираем все группы
			$('.group').each(function(){
				var $this = $(this);
				var groupName = $this.find('.title span').text();
				rulesSave.push('//group ' + groupName);
				
				//Перебираем все формы в текущей группе
				$('form.ruleForm', $this).each(function(){
					$this = $(this);
					//Запоминаем индекс формы
					var indexSave = $this.data('ddRuleIndex');
					//Вызываем фукнция сохранения формы 
					rulesSave.push(rulesMas[indexSave].save());
				});
			});
			
			Rules.saveComment();
		},
		//Добавляем коментарии в поле
		newComment: function(str, field){
			$('#'+field).val(str);
		},
		//Сохраняем комментарии
		saveComment: function(){
			rulesSave.unshift("//group comment_top\n" + $('#comment_top').val());
			rulesSave.push("//group comment_bottom\n" + $('#comment_bottom').val());
		}
	};
/**end*****Глобальный объект управления правилами*/

/**start*****Родительские конструкторы правил*/
	//Конструктор родительского класса
	function ruleParent(name, params){
		//Имя
		this.name = (name && $.type(name) == 'string') ? name : '';
		//Массив с параметрами
		this.params = (params && $.isArray(params)) ? params : new Array();
	}
	
	ruleParent.prototype.render = function(){
		var outMas = new Array();
		//Перебираем параметры, запускаем для них render()
		$.each(this.params, function(key, val){
			outMas[key] = val.render();
		});

		return $.ddTools.parseChunkAssoc(ruleFormTpl, {
			className: this.name,
			content: '<span class="fieldName">' + this.name + ': </span>' + outMas.join('')
		});
	};
	
	ruleParent.prototype.save = function(){
		//Создаем массив со значениями полей
		var outSaveMas = new Array(), html = this.html;
		
		//Вызываем сохранение объекта, передаём ему HTML элемент
		$.each(this.params, function(key, val){
			outSaveMas[key] = val.save(html);
		});
		
		//Формируем строку и возвращаем её
		return this.name + "('" + outSaveMas.join("','") + "');";
	};
/**end*****Родительские конструкторы правил*/

/**start*****Классы правил*/
	/**start*****Поля*/
	//Конструктор класса 'mm_hideFields'
	function HideFields(masParam){
		//Запускаем конструктор родителя
		HideFields.superclass.constructor.apply(this, ['mm_hideFields']);
		
		this.params.push(new Fields(masParam[0]));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
	}
	extend(HideFields, ruleParent);
	
	//Конструктор класса 'mm_renameField'
	function RenameField(masParam){
		//Запускаем конструктор родителя
		RenameField.superclass.constructor.apply(this, ['mm_renameField']);

		//mm_renameField($field, $newlabel, $roles, $templates, $newhelp)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('newLabel', masParam[1], 'New label', '', true));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
		this.params.push(new ddInput('newHelp', masParam[4], 'New help'));
	}
	extend(RenameField, ruleParent);
	
	//Конструктор класса 'mm_requireFields'
	function RequireFields(masParam){
		//Запускаем конструктор родителя
		RequireFields.superclass.constructor.apply(this, ['mm_requireFields']);

		//mm_requireFields($fields, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
	}
	extend(RequireFields, ruleParent);
	
	//Конструктор класса 'mm_default'
	function mmDefault(masParam){
		//Запускаем конструктор родителя
		mmDefault.superclass.constructor.apply(this, ['mm_default']);
		
		//mm_default($field, $value, $roles, $templates, $eval)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('value', masParam[1], 'Value'));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
		this.params.push(new ddInput('eval', masParam[4], 'Eval'));
	}
	extend(mmDefault, ruleParent);
	
	//Конструктор класса 'mm_changeFieldHelp'
	function ChangeFieldHelp(masParam){
		//Запускаем конструктор родителя
		ChangeFieldHelp.superclass.constructor.apply(this, ['mm_changeFieldHelp']);
		
		//mm_changeFieldHelp($field, $helptext, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('helpText', masParam[1], 'Help text', '', true));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
	}
	extend(ChangeFieldHelp, ruleParent);
	
	//Конструктор класса 'mm_hideTemplates'
	function HideTemplates(masParam){
		//Запускаем конструктор родителя
		HideTemplates.superclass.constructor.apply(this, ['mm_hideTemplates']);

		//mm_hideTemplates($tplIds, $roles, $templates)
		this.params.push(new ddInput('tplIds', masParam[0], 'Templates Ids', '', true));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
	}
	extend(HideTemplates, ruleParent);
	
	//Конструктор класса 'mm_inherit'
	function Inherit(masParam){
		//Запускаем конструктор родителя
		Inherit.superclass.constructor.apply(this, ['mm_inherit']);

		//mm_inherit($fields, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
	}
	extend(Inherit, ruleParent);
	
	//Конструктор класса 'mm_synch_fields'
	function SynchFields(masParam){
		//Запускаем конструктор родителя
		SynchFields.superclass.constructor.apply(this, ['mm_synch_fields']);

		//mm_synch_fields($fields, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
	}
	extend(SynchFields, ruleParent);
	/**end*****Поля*/
	
	/**start*****Вкладки*/
	//Конструктор класса 'mm_renameTab'
	function RenameTab(masParam){
		//Запускаем конструктор родителя
		RenameTab.superclass.constructor.apply(this, ['mm_renameTab']);
		
		//mm_renameTab($tab, $newlabel, $roles, $templates)
		this.params.push(new ddInput('tab', masParam[0], 'Tab', '', true));
		this.params.push(new ddInput('newLabel', masParam[1], 'New label', '', true));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
	}
	extend(RenameTab, ruleParent);
	
	//Конструктор класса 'mm_hideTabs'
	function HideTabs(masParam){
		//Запускаем конструктор родителя
		HideTabs.superclass.constructor.apply(this, ['mm_hideTabs']);

		//mm_hideTabs($tabs, $roles, $templates)
		this.params.push(new ddInput('tabs', masParam[0], 'Tabs', '', true));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
	}
	extend(HideTabs, ruleParent);
	
	//Конструктор класса 'mm_createTab'
	function CreateTab(masParam){
		//Запускаем конструктор родителя
		CreateTab.superclass.constructor.apply(this, ['mm_createTab']);
		
		//mm_createTab($name, $id, $roles, $templates, $intro, $width)
		this.params.push(new ddInput('nametab', masParam[0], 'Name', '', true));
		this.params.push(new ddInput('id', masParam[1], 'Id', '', true));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
		this.params.push(new ddInput('intro', masParam[4], 'Intro'));
		this.params.push(new ddInput('mmwidth', masParam[5], 'Width', '680', false, 'number'));
	}
	extend(CreateTab, ruleParent);
	
	//Конструктор класса 'mm_moveFieldsToTab'
	function MoveFieldsToTab(masParam){
		//Запускаем конструктор родителя
		MoveFieldsToTab.superclass.constructor.apply(this, ['mm_moveFieldsToTab']);

		//mm_moveFieldsToTab($fields, $newtab_id, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('newtab_id', masParam[1], 'New tab id', '', true));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
	}
	extend(MoveFieldsToTab, ruleParent);
	/**end*****Вкладки*/
	
	/**start*****Виджеты*/
	//Конструктор класса 'mm_widget_tags'
	function WidgetTags(masParam){
		//Запускаем конструктор родителя
		WidgetTags.superclass.constructor.apply(this, ['mm_widget_tags']);
		
		//mm_widget_tags($fields, $delimiter, $source, $display_count, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('delimiter', masParam[1], 'Delimiter', ','));
		this.params.push(new ddInput('source', masParam[2], 'Source'));
		this.params.push(new ddCheckbox('display_count', masParam[3], 'Display count'));
		this.params.push(new Roles(masParam[4]));
		this.params.push(new Templates(masParam[5]));
	}
	extend(WidgetTags, ruleParent);
	
	//Конструктор класса 'mm_widget_showimagetvs'
	function WidgetShowimagetvs(masParam){
		//Запускаем конструктор родителя
		WidgetShowimagetvs.superclass.constructor.apply(this, ['mm_widget_showimagetvs']);
		
		//mm_widget_showimagetvs($fields, $w, $h, $thumbnailerUrl, $roles, $templates)
		this.params.push(new Fields(masParam[0], 'TVs', '', false));
		this.params.push(new ddInput('w', masParam[1], 'Width', '300', false, 'number'));
		this.params.push(new ddInput('h', masParam[2], 'Height', '100', false, 'number'));
		this.params.push(new ddInput('thumbnailerUrl', masParam[3], 'Thumbnailer URL'));
		this.params.push(new Roles(masParam[4]));
		this.params.push(new Templates(masParam[5]));
	}
	extend(WidgetShowimagetvs, ruleParent);
	
	//Конструктор класса 'mm_widget_colors'
	function WidgetColors(masParam){
		//Запускаем конструктор родителя
		WidgetColors.superclass.constructor.apply(this, ['mm_widget_colors']);

		//mm_widget_colors($fields, $default, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('mmdefault', masParam[1], 'Default', '#ffffff', false, 'color'));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
	}
	extend(WidgetColors, ruleParent);
	
	//Конструктор класса 'mm_widmm_widget_accessdeniedget_colors'
	function WidgetAccessdenied(masParam){
		//Запускаем конструктор родителя
		WidgetAccessdenied.superclass.constructor.apply(this, ['mm_widget_accessdenied']);
		
		//mm_widget_accessdenied($ids, $message, $roles)
		this.params.push(new ddInput('ids', masParam[0], 'Ids', '', true));
		this.params.push(new ddInput('message', masParam[1], 'Message'));
		this.params.push(new Roles(masParam[2]));
	}
	extend(WidgetAccessdenied, ruleParent);
	
	//Конструктор класса 'mm_ddMaxLength v1.0'
	function ddMaxLength(masParam){
		//Запускаем конструктор родителя
		ddMaxLength.superclass.constructor.apply(this, ['mm_ddMaxLength']);
		
		//mm_ddMaxLength($tvs, $roles, $templates, $length)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddInput('mmlength', masParam[3], 'Length', '150', true, 'number'));
	}
	extend(ddMaxLength, ruleParent);
	
	//Конструктор класса 'mm_ddMultipleFields v4.0'
	function ddMultipleFields(masParam){
		//Запускаем конструктор родителя
		ddMultipleFields.superclass.constructor.apply(this, ['mm_ddMultipleFields']);
		
		//mm_ddMultipleFields($tvs, $roles, $templates, $coloumns, $coloumnsTitle, $colWidth, $splY, $splX, $imgW, $imgH, $minRow, $maxRow, $coloumnsData)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddInput('coloumns', masParam[3], 'Columns', 'field'));
		this.params.push(new ddInput('coloumnsTitle', masParam[4], 'Column titles'));
		this.params.push(new ddInput('colWidth', masParam[5], 'Column widths', '180'));
		this.params.push(new ddInput('splY', masParam[6], 'splY', '||', true));
		this.params.push(new ddInput('splX', masParam[7], 'splX', '::'));
		this.params.push(new ddInput('imgW', masParam[8], 'imgW', '300', false, 'number'));
		this.params.push(new ddInput('imgH', masParam[9], 'imgH', '100', false, 'number'));
		this.params.push(new ddInput('minRow', masParam[10], 'minRow', '0', false, 'number'));
		this.params.push(new ddInput('maxRow', masParam[11], 'maxRow', '0', false, 'number'));
		this.params.push(new ddInput('coloumnsData', masParam[12], 'СoloumnsData'));
	}
	extend(ddMultipleFields, ruleParent);
	
	//Конструктор класса 'mm_ddPatternField'
	function ddPatternField(masParam){
		//Запускаем конструктор родителя
		ddPatternField.superclass.constructor.apply(this, ['mm_ddPatternField']);
		
		//mm_ddPatternField($tvs, $roles, $templates, $column, $spl, $width, $splspl)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddInput('coloumn', masParam[3], 'Coloumn', '2', true, 'number'));
		this.params.push(new ddInput('spl', masParam[4], 'Spl', '||'));
		this.params.push(new ddInput('width', masParam[5], 'Width', '100'));
		this.params.push(new ddInput('splspl', masParam[6], 'SplSpl', '++'));
	}
	extend(ddPatternField, ruleParent);
	
	//Конструктор класса 'mm_ddGMap'
	function ddGMap(masParam){
		//Запускаем конструктор родителя
		ddGMap.superclass.constructor.apply(this, ['mm_ddGMap']);
		
		//mm_ddGMap($tvs, $roles, $templates, $w, $h)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddInput('w', masParam[3], 'W', 'auto'));
		this.params.push(new ddInput('h', masParam[4], 'H', '400', false, 'number'));
		this.params.push(new ddCheckbox('hideField', masParam[5], 'Hide input', '1'));
	}
	extend(ddGMap, ruleParent);
	
	//Конструктор класса 'mm_ddYMap'
	function ddYMap(masParam){
		//Запускаем конструктор родителя
		ddYMap.superclass.constructor.apply(this, ['mm_ddYMap']);
		
		//mm_ddYMap($tvs, $roles, $templates, $w, $h)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddInput('w', masParam[3], 'W', 'auto'));
		this.params.push(new ddInput('h', masParam[4], 'H', '400', false, 'number'));
		this.params.push(new ddCheckbox('hideField', masParam[5], 'Hide input', '1'));
	}
	extend(ddYMap, ruleParent);
	
	//Конструктор класса 'mm_ddSetFieldValue'
	function ddSetFieldValue(masParam){
		//Запускаем конструктор родителя
		ddSetFieldValue.superclass.constructor.apply(this, ['mm_ddSetFieldValue']);
		
		//mm_ddSetFieldValue($field, $value, $roles, $templates)
		this.params.push(new Fields(masParam[0]));
		this.params.push(new ddInput('value', masParam[1], 'Value'));
		this.params.push(new Roles(masParam[2]));
		this.params.push(new Templates(masParam[3]));
	}
	extend(ddSetFieldValue, ruleParent);
	
	//Конструктор класса 'mm_ddResizeImage'
	function ddResizeImage(masParam){
		//Запускаем конструктор родителя
		ddResizeImage.superclass.constructor.apply(this, ['mm_ddResizeImage']);
		
		//mm_ddResizeImage($tvs, $roles, $templates, $width, $height, $cropping, $suffix, $replaceFieldVal, $background, $multipleField, $colNum, $splY, $splX, $num)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddInput('width', masParam[3], 'Width', false, 'number'));
		this.params.push(new ddInput('height', masParam[4], 'Height', false, 'number'));
//		this.params.push(new ddInput('cropping', masParam[5], 'Cropping', 'crop_resized'));
		this.params.push(new ddSelect('cropping', masParam[5], 'Cropping', 'crop_resized', new Array({'id': '0', 'name': 'No'}, {'id': '1', 'name': 'Yes'}, {'id': 'crop_resized', 'name': 'Crop resized'}, {'id': 'fill_resized', 'name': 'Fill resized'})));
		this.params.push(new ddInput('suffix', masParam[6], 'Suffix', '_ddthumb'));
		this.params.push(new ddCheckbox('replaceFieldVal', masParam[7], 'ReplaceFieldVal'));
		this.params.push(new ddInput('background', masParam[8], 'Background', '#FF0000', false, 'color'));
		this.params.push(new ddCheckbox('multipleField', masParam[9], 'MultipleField'));
		this.params.push(new ddInput('colNum', masParam[10], 'ColNum', '0', false, 'number'));
		this.params.push(new ddInput('splY', masParam[11], 'SplY', '||'));
		this.params.push(new ddInput('splX', masParam[12], 'SplX', '::'));
		this.params.push(new ddInput('num', masParam[13], 'Num', 'all'));
	}
	extend(ddResizeImage, ruleParent);
	
	//Конструктор класса 'mm_ddAutoFolders'
	function ddAutoFolders(masParam){
		//Запускаем конструктор родителя
		ddAutoFolders.superclass.constructor.apply(this, ['mm_ddAutoFolders']);
		
		//mm_ddResizeImage($ddRoles, $ddTemplates, $ddParent, $ddDateSource, $ddYearTpl, $ddMonthTpl, $ddYearPub, $ddMonthPub)
		this.params.push(new Roles(masParam[0]));
		this.params.push(new Templates(masParam[1]));
		this.params.push(new ddInput('parent', masParam[2], 'Parent', '', true, 'number'));
		this.params.push(new Fields(masParam[3], 'dateSourse', 'pub_date'));
		this.params.push(new Templates(masParam[4], 'yearTpl', '0'));
		this.params.push(new Templates(masParam[5], 'monthTpl', '0'));
		this.params.push(new ddCheckbox('yearPub', masParam[6]));
		this.params.push(new ddCheckbox('monthPub', masParam[7]));
	}
	extend(ddAutoFolders, ruleParent);
	
	//Конструктор класса 'ddNumericFields'
	function ddNumericFields(masParam){
		//Запускаем конструктор родителя
		ddNumericFields.superclass.constructor.apply(this, ['mm_ddNumericFields']);
		
		//mm_ddNumericFields($tvs='', $roles='', $templates='', $allowFloat = 1, $decimals = 0)
		this.params.push(new Fields(masParam[0], 'TVs'));
		this.params.push(new Roles(masParam[1]));
		this.params.push(new Templates(masParam[2]));
		this.params.push(new ddCheckbox('allowFloat', masParam[3], 'Allow float', '1'));
		this.params.push(new ddInput('decimals', masParam[4], 'Decimals', 0));
		
	}
	extend(ddNumericFields, ruleParent);
	/**end*****Виджеты*/
/**end*****Классы правил*/

/**start*****Классы параметров*/
	//Общий класс для всех параметров
	function ddParam(name, value, displayName, defaultValue){
		//Имя поля
		this.name = (name && $.type(name) == 'string') ? name : 'param';
		//Уникальный ID (пригодится для поиска и пр)
		this.id = this.name + parseInt(Math.random() * 100000);
		//Название поля
		this.displayName = (displayName && $.type(displayName) == 'string') ? displayName : this.name;
		//Значение по умолчанию
		this.defaultValue = ($.type(defaultValue) != 'undefined') ? defaultValue : '';
		//Значение поля
		this.value = ($.type(value) != 'undefined') ? value : this.defaultValue;
	}
	
	//Конструктор общего класса ddInput
	function ddInput(name, value, displayName, defaultValue, required, type){
		//Запускаем родительский конструктор с необходимыми параметрами
		ddInput.superclass.constructor.apply(this, [name, value, displayName, defaultValue]);
		
		if (required){
			this.required = true;
			this.displayName += '*';
		}else{
			//По умолчанию не обязательно для заполнения
			this.required = false;
		}
		
		this.type = (type && $.type(type) == 'string') ? type : 'text';
	}
	extend(ddInput, ddParam);
	
	ddInput.prototype.render = function(){
		var required = this.required ? 'required' : '';
		
		return '<label>' + this.displayName + ': </label><input id="' + this.id + '" class="' + this.name + '" type="' + this.type + '" value="' + this.value + '" ' + required + ' />';
	};
	
	ddInput.prototype.save = function(htmlObj){
		return $('#' + this.id, htmlObj).val();
	};

	//Конструктор общего класса ddCheckbox
	function ddCheckbox(name, value, displayName, defaultValue){
		//Значение по умолчанию, с ним всё просто
		defaultValue = (defaultValue == '1') ? true : false;
		
		//Если значение не задано, либо задано пустым
		if ($.type(value) == 'undefined' || $.trim(value) == ''){
			//Значит берём значение по умолчанию
			value = defaultValue;
		}else{
			//Если задано, то обрабатываем
			value = (value == '1') ? true : false;
		}
		
		//Запускаем родительский конструктор с необходимыми параметрами
		ddCheckbox.superclass.constructor.apply(this, arguments);
	}
	extend(ddCheckbox, ddParam);
	
	ddCheckbox.prototype.render = function(){
		var checked = (this.value) ? 'checked' : '';
		
		return '<label>' + this.displayName + ': </label><input id="' + this.id + '" class="' + this.name + '" type="checkbox" ' + checked + ' />';
	};
	
	ddCheckbox.prototype.save = function(htmlObj){
		return $("#" + this.id, htmlObj).is(':checked') ? '1' : '0';
	};
	
	//Конструктор общего класса ddSelect
	function ddSelect(name, value, displayName, defaultValue, valObj, prefixArr){
		//Запускаем родительский конструктор с необходимыми параметрами
		ddSelect.superclass.constructor.apply(this, [name, value, displayName, defaultValue]);
		
		//Объект со значениями
		this.valueObject = valObj;
		//Массив префиксов (если необходимо добавлять отрицания или ещё что)
		this.prefixArr = $.isArray(prefixArr) ? prefixArr : new Array('');
	}
	extend(ddSelect, ddParam);
	
	ddSelect.prototype.render = function(){
		//Запоминаем выбранную роль
		var selectRole = this.value;
		//Префиксы для добавления к списку
		var prefixMas = this.prefixArr;
		var selectedRole = '';
		
		//Создаём список
		var outRender = '<label>' + this.displayName + ': </label><select id="' + this.id + '" class="' + this.name + '">';
		//Добавим значение «Все»
		outRender += '<option value="-1">Все</option>';
		
		//Перебираем объект с ролями
		for (var prefix in prefixMas){
			$.each(this.valueObject, function(key, val){
				//Если выбранная роль совпадает с элементом объекта, то делаем роль текущей
				selectedRole = (selectRole == prefixMas[prefix] + val.id ? 'selected="selected"' : '');
				
				outRender += '<option ' + selectedRole + ' value="' + prefixMas[prefix] + val.id + '">' + prefixMas[prefix] + ' ' + val.name + ' (' + val.id + ')</option>';
			});
		}
		
		outRender += '</select>';
		
		return outRender;
	};
	
	ddSelect.prototype.save = function(htmlObj){
		if($('#' + this.id, htmlObj).val() != '-1'){
			return $('#' + this.id, htmlObj).val();
		}else{
			return '';
		}
	};
	
	//Конструктор класса Field
	function Fields(value, displayName, defaultValue, required){
		displayName = (displayName && $.type(displayName) == 'string') ? displayName : 'Fields';
		
		//Запускаем родительский конструктор с необходимыми параметрами (по умолчанию обязательно для заполнения)
		Fields.superclass.constructor.apply(this, ['input_field', value, displayName, defaultValue, ($.type(required) != 'undefined' && !required) ? false : true]);
	}
	extend(Fields, ddInput);
	
	//Конструктор класса Roles
	function Roles(value, displayName, defaultValue){
		displayName = (displayName && $.type(displayName) == 'string') ? displayName : 'Role';
		
		Roles.superclass.constructor.apply(this, ['select_role', value, displayName, defaultValue, rolesOdj, new Array('', '!')]);
	}
	extend(Roles, ddSelect);
	
	//Конструктор класса Templates
	function Templates(value, displayName, defaultValue){
		displayName = (displayName && $.type(displayName) == 'string') ? displayName : 'Template';
		
		Templates.superclass.constructor.apply(this, ['select_template', value, displayName, defaultValue, templatesOdj, new Array('', '!')]);
	}
	extend(Templates, ddSelect);
/**end*****Классы параметров*/
});