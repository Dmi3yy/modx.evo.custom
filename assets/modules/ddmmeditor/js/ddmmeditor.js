/**
 * ddmmeditor.js
 * @version 1.2 (2012-02-22)
 * 
 * Описание класса для работы с правилами.
 *
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 **/

var groupTemplate = $('<div class="group"><div class="title"><span>New group</span> <a href="#Edit" class="false editButton"></a></div><div class="titleButtons"><a href="#Hide" class="false hideButton"><span>+</span><span>&minus;</span></a> <a class="false deleteButton" href="#Remove">×</a></div></div>');
//Автозаполнение
function split(val){return val.split( /,\s*/ );}

function extractLast(term){return split(term).pop();}

function tvAutocomplite(input_field){
	input_field.live("keydown", function(event){
		if (event.keyCode === $.ui.keyCode.TAB
				&& $(this).data("autocomplete").menu.active){
			event.preventDefault();
	}}).autocomplete({
		minLength : 0,
		source : function(request, response) {
			response($.ui.autocomplete.filter($.parseJSON(tvsAutocomplite), extractLast(request.term)));
		},
		focus : function() {return false;},
		select : function(event, ui) {
			var terms = split(this.value);
			terms.pop();
			terms.push(ui.item.value);
			//terms.push("");
			this.value = terms.join(",");
			return false;
		}
	});
}

$(function(){
	//Блокируем переход по псевдо ссылкам
	$.ddTools.body.on('click.false', 'a.false', function(event){event.preventDefault();});
	
	Rules.constructorRules(rulesJSON);//Создаем массив с правилами
	var ajaxLoader = $('.ajaxLoader');
	
	//Создаём список с новыми правилами
	$('#new_rule a').on('click', function(){
		//Если список ещё не создан, то создаём его
		if($('#new_role_select').length == 0){
			$('.actionButtons').after($.ddTools.parseChunkAssoc(newRuleTpl, {options: newRuleSelectOut}));//Добавляем список с правилами
		}else{
			$('#new_role_select').remove();
		}
		return false;
	});
	
	//Создаём форму с выбранным правилом
	$('#new_role_select').live('click', function(){
		$('#new_role_select').remove();//Удаляем список с правилами
		if ($('.group.default').length == 0)
			$('#new_group').trigger('click', ['default']);
		
		Rules.newRule($('option:selected', $(this)).text(), '', $('.group.default'));//Создаём в массиве правило
	});

	//Удаляем форму
	$('input.del').live('click', function(){
		$(this).parent('form.ruleForm').remove();
	});

	//Сохраняем файл 
	$('#save_rules a').on('click', function(){
		ajaxLoader.show();
		//Подготавливаем массив
		Rules.save();
		//Отправляем массив на сервер
		$.ajax({
			url: document.location.href,
			type: 'POST',
			data: {rules:rulesSave},
			success: function(data){
				ajaxLoader.hide();
				alert(data);
			}
		});
		return false;
	});
	
	//Создание группы правил
	$('#new_group').on('click', function(event, groupClass){
		if (!groupClass) groupClass='';
		var group = groupTemplate.clone();
		group.addClass(groupClass);
		group.appendTo('#rules_cont');
		group.sortable({
			handle: 'span.fieldName',
			cancel: '.title span',
			connectWith: '.group',
			cursor: 'n-resize',
			axis: 'y',
			placeholder: "ui-state-highlight",
			start: function(event, ui){$('.ui-state-highlight').css('height', ui.item.height());}
		});
		return false;
	});
	
	//Сворачивание группы
	$.ddTools.body.on('click', '.group:not(.default) .hideButton', function(){
		var $this = $(this);
		var group = $this.parents('.group:first');
		var forms = group.find('form');
		forms.animate({
			opacity: 'toggle',
			height: 'toggle'
		});
		forms.promise().done(function(){
			group.toggleClass('closed');
			forms.css('display', '');
		});
	});

	//При дабл-клике по имени группы тоже обеспечиваем сворачивание-разворачивание
	$.ddTools.body.on('dblclick', '.group:not(.default) .title span', function(){
		$(this).parents('.group:first').find('.hideButton').trigger('click');
	});
	
	//Переименование группы
	$.ddTools.body.on('click', '.group .title .editButton', function(){
		var $title = $(this).siblings('span');
		var name = prompt('Введите название группы.', $title.text());
		if (name != '' && name != null){
			$title.text(name);
			$title.parents('.group:first').removeClass('default');
		}
		return false;
	});
	
	//Удаление группы
	$('.group .deleteButton').live('click', function(){
		var $group = $(this).parents('.group:first');
		if (confirm('Удалить группу правил «' + $group.find('.title span').text() + '»?')){
			$group.remove();
		}
	});
	
	//Создаём вкладки
	$('#tabs').tabs();
	//Сортировка групп
	$('#rules_cont').sortable({
		handle: 'div.title span',
		cursor: 'n-resize',
		axis: 'y',
		placeholder: "ui-state-highlight",
		start: function(event, ui){$('.ui-state-highlight').css('height', ui.item.height());}
	});
	//Сортировка внутри групп
	$('#rules_cont .group').sortable({
		handle: 'span.fieldName',
		cancel: '.title span',
		connectWith: '.group',
		cursor: 'n-resize',
		axis: 'y',
		placeholder: "ui-state-highlight",
		start: function(event, ui){$('.ui-state-highlight').css('height', ui.item.height());}
	});
});