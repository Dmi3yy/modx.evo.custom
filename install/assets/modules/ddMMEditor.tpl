// <?php 
/**
 * ddMMEditor
 * 
 * Редактор файла конфигурации для ManagerManager
 * 
 * @category	module
 * @version 	1.2.3
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties	 
 * @internal	@guid 	
 * @internal	@modx_category add
 */

//<?php
/**
 * ddMMEditor module
 * @version 1.2.3 (2012-08-24)
 *
 * Редактор файла конфигурации для ManagerManager.
 * 
 * @link http://code.divandesign.biz/modx/ddmmeditor/1.2.3
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

//Защищаем файл
if(!$modx) return;
else if($modx->getLoginUserType() != 'manager') return;
else{
	//Сравниваем url сайта из конфига с реальным (в качестве длины берём длину из конфига, чтобы лишнее не смотреть)
	$site_url = $modx->config['site_url'];
	if (strncasecmp($site_url, $_SERVER['HTTP_REFERER'], strlen($site_url)) != 0) return;
}

$ver = '1.2.3';

$fileName = MODX_BASE_PATH.'assets/plugins/managermanager/mm_rules.inc.php';//Полный адрес файла
if (isset($_POST['rules'])) $saveMas = $_POST['rules'];//Сохраняем пост в массив

//Если массив с постом не пустой, то запускаем сохранение
if (isset($saveMas)){
	//Добавляем в массив открытие и закрытие php кода
	array_unshift($saveMas,'<?php');
	array_push($saveMas, "?\>");

	//Открываем файл
	if(!$file = fopen($fileName, 'w')){
		echo "Can't open file";
		return;
	}
	//Перебираем массив со строками
	foreach ($saveMas as $value){
		$line = stripslashes($value."\n");//Разэкранируем строку
		//Записываем строку в файл
		if (fwrite($file, $line) === false) {
			echo "Can't write string";
			return;
		}
	}
	fclose($file);//Закрываем файл
	echo "Write success";
	return;
}

//Считываем файл
$config = file($fileName);
$site_url = $modx->config['site_url'];
$rules = array();
$group = '';
//Перебираем файл по строкам
foreach ($config as $line){
	$line = trim($line);
	
	if ($line == '<?php' || $line == '?>' || $line == '') continue;
	
	//Создаём группу
	if (strncasecmp($line, '//group', 7) == 0){
		$group = substr($line, 8);
		if (!isset($rules[$group])) $rules[$group] = array();
		continue;
	}
	
	switch ($group){
		case 'comment_top':
		case 'comment_bottom':
			$rules[$group][] = str_replace(array('"',"'"),'\"',$line).'\n';
			break;
		default:
			$temp = array();
			$sepF = strpos($line, '(');
			$sepL = strrpos($line, ')');
			$temp['name'] = substr($line, 0, $sepF);
			$temp['param'] = substr($line, $sepF + 1, ($sepL - $sepF - 1));
			$temp['param'] = str_replace('"', "&#34;", $temp['param']);
			$rules[$group][] = $temp;
	}
}

if ($rules['comment_top']) $rules['comment_top'] = implode('', $rules['comment_top']);
if ($rules['comment_bottom']) $rules['comment_bottom'] = implode('', $rules['comment_bottom']);

//Преобразуем в JSON, экранируем \'
$rules = str_replace("'","\'",json_encode($rules));

//Создаём объект ролей
$roles = json_encode($modx->db->makeArray($modx->db->select("id, name", $modx->getFullTableName('user_roles'), "", "id ASC")));
//Создаём объект шаблонов
$templates = $modx->db->makeArray($modx->db->select("id, templatename AS name", $modx->getFullTableName('site_templates'), "", "templatename ASC"));
array_unshift($templates, array('id' => 0, 'name' => 'blank'));
$templates = json_encode($templates);

//Получаем все используемые tv
$sql = "SELECT `name` FROM {$modx->getFullTableName('site_tmplvars')} GROUP BY `name` ASC";
$temp = $modx->db->makeArray($modx->db->query($sql));
$fields = array();
foreach($temp as $value) $fields[] = $value['name'];

//Добавим поля документа
$fields[] = 'pagetitle';
$fields[] = 'longtitle';
$fields[] = 'description';
$fields[] = 'alias';
$fields[] = 'link_attributes';
$fields[] = 'introtext';
$fields[] = 'template';
$fields[] = 'menutitle';
$fields[] = 'menuindex';
$fields[] = 'show_in_menu';
$fields[] = 'hide_menu';
$fields[] = 'parent';
$fields[] = 'is_folder';
$fields[] = 'is_richtext';
$fields[] = 'log';
$fields[] = 'published';
$fields[] = 'pub_date';
$fields[] = 'unpub_date'; 
$fields[] = 'searchable'; 
$fields[] = 'cacheable';
$fields[] = 'clear_cache';
$fields[] = 'content_type';
$fields[] = 'content_dispo'; 
$fields[] = 'keywords';
$fields[] = 'metatags';
$fields[] = 'content';
$fields[] = 'which_editor';
$fields[] = 'resource_type'; 
$fields[] = 'weblink';
// print_r($fields);
$fields = json_encode($fields);

//Получаем название темы админки
$theme = $modx->db->select('setting_value', $modx->getFullTableName('system_settings'), 'setting_name=\'manager_theme\'', '');
if ($modx->db->getRecordCount($theme)) {
	$theme = $modx->db->getRow($theme);
	$theme = ($theme['setting_value'] <> '') ? '/' . $theme['setting_value'] : '';
}

//Формируем вывод
$output = '<html>
<head>';
$output .= '<base href="'.$site_url.'" />
<script type=text/javascript>';
$output .= "var rulesJSON = '".$rules."';";
$output .= "var rolesJSON = '".$roles."';";
$output .= "var templatesJSON = '".$templates."';";
$output .= "var tvsAutocomplite = '".$fields."';";
$output .= '
</script>
<link rel="stylesheet" type="text/css" href="'.$modx->config['site_manager_url'].'/media/style'.$theme.'/style.css" />
<link rel="stylesheet" type="text/css" href="'.$site_url.'/assets/modules/ddmmeditor/css/general.css" />
<script src="'.$site_url.'assets/modules/ddmmeditor/js/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="'.$site_url.'assets/modules/ddmmeditor/js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
<script src="'.$site_url.'assets/modules/ddmmeditor/js/jquery.ddTools-1.7.2.min.js" type="text/javascript"></script>
<script src="'.$site_url.'assets/modules/ddmmeditor/js/ddmmeditor.class.js" type="text/javascript"></script>
<script src="'.$site_url.'assets/modules/ddmmeditor/js/ddmmeditor.js" type="text/javascript"></script>
</head>
<body>
	<h1>Редактор файла конфигурации для ManagerManager.<span id="ver"> '.$ver.'</span></h1>
	<div id="actions">
		<ul class="actionButtons">
			<li id="new_rule"><a href="#">Новое правило</a></li>
			<li id="new_group"><a href="#">Новая группа</a></li>
			<li id="save_rules"><a href="#">Сохранить</a></li>
		</ul>
	</div>
	<div class="sectionBody">
		<div id="tabs" class="dynamic-tab-pane-control">
			<ul class="tab_cont tab-row">
				<li class="tab"><a href="#rules">Правила</a></li>
				<li class="tab"><a href="#manual">Ручные установки</a></li>
			</ul>
			<div id="rules" class="tab-page">
				<div id="rules_cont">
				</div>
			</div>
			<div id="manual" class="tab-page">
				<h3>В начало</h3>
				<p>Этот код будет размещен до всех правил.</p>
				<textarea id="comment_top"></textarea>
				<h3>В конец</h3>
				<p>Этот код будет размещен после всех правил.</p>
				<textarea id="comment_bottom"></textarea>
			</div>
		</div>
		<div class="ajaxLoader"></div>
	</div>
	<div class="ddFooter">
		<div style="float: left;"><a href="http://code.divandesign.biz/modx/ddmmeditor/'.$ver.'" target="_blank">Документация</a></div>
		<address>Created by <a href="http://www.DivanDesign.biz" target="_blank">DivanDesign</a></address>
	</div>
	<div class="clear"></div>
</body>
</html>';
echo $output;
//?>
