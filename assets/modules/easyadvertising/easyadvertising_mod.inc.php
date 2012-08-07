<?php
/*
*  EasyAdvertising - снипет вывода рекламы на сайте
*  Версия 1.02 
*  Авторы:  Леха.com
*  ver.1.02 lo-pata (s.vlksm@gmail.com)
*
*/
if (!defined('IN_MANAGER_MODE') || (defined('IN_MANAGER_MODE') && (!IN_MANAGER_MODE || IN_MANAGER_MODE == 'false'))) die();

$dbname = $modx->db->config['dbase']; //имя базы данных
$dbprefix = $modx->db->config['table_prefix']; //префикс таблиц
$charset = $modx->db->config['charset']; //кодировка БД
$mod_table = $dbprefix."site_easyadvt"; //таблица модуля
$theme = $modx->config['manager_theme']; //тема админки
$ui_theme = (isset($ui_theme) && $ui_theme != '') ? $ui_theme : 'smoothness'; // тема для jquery-ui, из конфига модуля

$baseUrl = $modx->config['site_url'];
$modUrl = $baseUrl.'assets/modules/easyadvertising/';

// параметры таблицы по-умолчанию - ширина колонок и высота таблицы
$params = array(40,40,150,60,100,100,100,45,50,50,55);
if (isset($cols) && $cols != '') {
	$width = explode(',', str_replace(' ', '', $cols));
	for ($i=0; $i<11; $i++) 
		$w[$i] = ((int)$width[$i] > 0) ? (int)$width[$i] : $params[$i];
} else 
	$w = $params;
	
$table_height = !empty($height) ? $height : 500;

include "header.tpl.php";
 
$action = isset($_POST['action']) ? $_POST['action'] : '';
 
switch($action) {

////Установка модуля (создание таблицы в БД)
	case 'install':
		$sql = '
			CREATE TABLE IF NOT EXISTS '.$mod_table.' (
			id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			pos int(8) NOT NULL,
			template text CHARACTER SET '.$charset.' NOT NULL,
			ex_template text CHARACTER SET '.$charset.' NOT NULL,
			area varchar(255) CHARACTER SET '.$charset.' NOT NULL,
			description text CHARACTER SET '.$charset.' NOT NULL,
			link varchar(255) CHARACTER SET '.$charset.' NOT NULL,
			published tinyint(1) UNSIGNED NOT NULL,
			pub_date int(20) NOT NULL,
			unpub_date int(20) NOT NULL,
			counted int(1) UNSIGNED NOT NULL,
			count_view int(20) UNSIGNED NOT NULL,
			total_view int(20) UNSIGNED NOT NULL,
			jump_counted tinyint(1) UNSIGNED NOT NULL,
			jump_count int(20) UNSIGNED NOT NULL,
			total_jump int(20) UNSIGNED NOT NULL,
			content text CHARACTER SET '.$charset.' NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET='.$charset.'';

		$modx->db->query($sql);
		header("Location: $_SERVER[REQUEST_URI]");
	break;
	  
	////Удаление таблицы модуля
	case "uninstall":
		$sql = "DROP TABLE ".$mod_table;
		$modx->db->query($sql);
		header("Location: $_SERVER[REQUEST_URI]");
	break;
	 
	////Перезагрузка страницы (сброс $_POST)
	case 'reload':
		header("Location: $_SERVER[REQUEST_URI]");
	break;
	 
	////Страница модуля
	default:
		$s = "show tables from ".$dbname." like '".$mod_table."'";
		if ($modx->db->getRecordCount($modx->db->query("show tables from ".$dbname." like '".$mod_table."'"))==0)
		
			echo '<ul class="actionButtons"><li><a href="#" onclick="postForm(\'install\',null);return false;"><img src="media/style/'.$theme.'/images/icons/save.png" align="absmiddle" />Установить модуль</a></li></ul>';
		
		else {
		
			$but_add = '<ul class="actionButtons"><li><a href="#" onclick="addRow(0);return false;"><img src="media/style/'.$theme.'/images/icons/add.png" align="absmiddle" />Добавить</a></li></ul>';
			
			$tab1 = '<table id="flex" style="display: none"></table>';

			$footer = '
			<br /><br />
			<div class="dright">
				<ul class="actionButtons">
					<li><a href="#" onclick="if(confirm(\'Вы уверены?\n\rВсе данные будут удалены без возможности восстановления.\')){postForm(\'uninstall\',null)};return false;">
						<img src="media/style/'.$theme.'/images/icons/delete.png" align="absmiddle" />Удалить модуль</a>
					</li>
				</ul>
			</div>
			<div class="clear"></div>
			';
			
			echo $but_add.'</form>'.$tab1.$footer;
		  }
		  
	break;
} 
 
echo '
		</div>
	</body>
</html>
';