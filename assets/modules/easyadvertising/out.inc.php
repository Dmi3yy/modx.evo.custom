<?php
if (!function_exists('out')) {
	function out($arr) {
		
		foreach ($arr as $k=>$v) 
			${$k} = $v;
		
		$out = '
			<table width="650px" border="0" cellspacing="0" cellpadding="0"><tr>
				<td width="325px">Позиция:<br /><input size="5" type="text" name="pos" value="'.$pos.'" /></td>
				<td>
					Рекламная зона:
					<span class="dright"><input type="checkbox" name="published" '.$checked.' class="pub">Опубликовано</span><br />
					<input size="20" type="text" name="area" value="'.$area.'" />
				</td>
			</tr></table>
			
			<table class="top" width="650px" border="0" cellspacing="0" cellpadding="0"> 
			<tr><td>Шаблоны URL:<br />
			(каждый шаблон на новой строке)<br />
			<textarea name="template">'.$template.'</textarea></td>
			<td>Шаблоны исключений URL:<br />
			(каждый шаблон на новой строке)<br />
			<textarea name="ex_template">'.$ex_template.'</textarea></td></tr>
			</table><br />
			Описание:<br /><input class="long" type="text" name="description" value="'.$description.'" /><br />
			Линк:<br /><input class="long" type="text" name="link" value="'.$link.'" /><br />
			Файл:<br />
			
			<div id="mhelp-cont">Вместо файла можно вставлять более сложный html-код.<br />Ссылки для перехода можно писать явно <b>&lt;a href="..."&gt;...&lt;/a&gt;</b>,<br />но для того, чтобы ссылки подставлялись автоматически из поля <b>"Линк"</b><br />заменяйте начало и окончание ссылок плэйсхолдерами<br /><b>[+link_start+]</b> и <b>[+link_finish+]</b> соответственно.</div>
			
			<textarea class="longimg" name="content" id="ef-img">'.$content.'</textarea><button id="ef-link">Выбрать</button><button id="mhelp">Помощь</button>
			<div id="ef-ef"></div><div id="eadvt_preview"></div><br />
			
			<table width="650px" border="0" cellspacing="0" cellpadding="0"> 
			<tr>
			<td width="325px">
			Дата начала публикации:<br />
			<input id="pub_date" name="pub_date" class="DatePicker" value="'.$pub_date.'" /></td><td>
			Дата окончания публикации:<br />
			<input id="unpub_date" name="unpub_date" class="DatePicker" value="'.$unpub_date.'" /></td>
			</tr>
			</table>
			
			<table class="top" width="650px" border="0" cellspacing="0" cellpadding="0"> 
			<tr>
				<td width="325">
					Показы:<br />
					<input type="checkbox" name="counted" '.$checkedcount.' value="1" /> Подсчитывать<br />
					<input type="text" name="count_view" value="'.$count_view.'" /><br />
					План показов (0 - бесконечно): <br /><input type="text" name="total_view" value="'.$total_view.'" />
				</td>
			
				<td>
					Переходы:<br />
					<input type="checkbox" name="jump_counted" '.$jump_counted.' value="1" /> Подсчитывать<br />
					<input type="text" name="jump_count" value="'.$jump_count.'" /><br />
					План кликов (0 - бесконечно): <br /><input type="text" name="total_jump" value="'.$total_jump.'" />
				</td>
			</tr>
			</table>
			';
			
		return $out;	

	}
}	
	