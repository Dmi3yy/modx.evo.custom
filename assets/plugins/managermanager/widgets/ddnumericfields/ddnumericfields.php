<?php
/** 
 * mm_ddNumericFields
 * @version 1.0 (2012-05-05)
 * 
 * Позволяет сделать возможным ввод в tv только цифр.
 *
 * @param $tvs {comma separated string} - Имена TV, для которых необходимо применить виджет.
 * @param $roles {comma separated string} - Роли, для которых необходимо применить виждет, пустое значение — все роли. По умолчанию: ''.
 * @param $templates {comma separated string} - Id шаблонов, для которых необходимо применить виджет, пустое значение — все шаблоны. По умолчанию: ''.
 * @param $allowFloat {0; 1} - Можно ли вводить числа с плавающей запятой? По умолчанию: 1.
 * @param $decimals {integer} - Количество цифр после запятой (0 — любое). По умолчанию: 0.
 * 
 * @link http://code.divandesign.biz/modx/mm_ddnumericfields/1.0
 * 
 * @copyright 2012, DivanDesign
 * http://www.DivanDesign.ru
 */

function mm_ddNumericFields($tvs='', $roles='', $templates='', $allowFloat = 1, $decimals = 0){

	global $modx, $content;
	$e = &$modx->Event;
	
	if ($e->name == 'OnDocFormRender' && useThisRule($roles, $templates)){
		$output = '';
		
		// Which template is this page using?
		if (isset($content['template'])) {
			$page_template = $content['template'];
		} else {
			// If no content is set, it's likely we're adding a new page at top level. 
			// So use the site default template. This may need some work as it might interfere with a default template set by MM?
			$page_template = $modx->config['default_template']; 
		}

		$tvs = tplUseTvs($page_template, $tvs);
		if ($tvs == false){
			return;
		}
		
		if ($decimals == 0) $decimals = 'false';
		
		$widgetDir = $modx->config['site_url'].'assets/plugins/managermanager/widgets/ddnumericfields/';
		
		$output .= "// ---------------- mm_ddNumericFields :: Begin ------------- \n";
		//General functions
		$output .= '
//Если $.ddTools ещё не подключён, подключим
if (!$j.ddTools){'.includeJs($widgetDir.'jquery.ddTools-1.7.4.min.js').'}
//Запрещённые символы
var ddNumericFieldsChars = "!@#$%^&*()+=[]\\\';/{}|\":<>?~` abcdefghijklmnopqrstuvwxyzабвгдеёжзийклмнопрстуфхцчшщьъыэюяABCDEFGHIJKLMNOPQRSTUVWXYZАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЪЫЭЮЯ";
//Если числа с плавающей запятой нельзя вводить, убиваем эти символы
if (!'.$allowFloat.') ddNumericFieldsChars += ".,";
		';

		foreach ($tvs as $tv){
			$output .= '
//При изменении tv убиваем жёстко
$j("#tv'.$tv['id'].'").on("change.ddEvents", function(){
	var $this = $j(this);
	
	//Если разрешён ввод числ с плавающей запятой
	if ('.$allowFloat.'){
		$this.val($j.ddTools.parseFloat($this.val(), '.$decimals.'));
	}else{
		$this.val($j.ddTools.parseInt($this.val()));
	}
//При вводе в tv
}).on("keypress.ddEvents", function(event){
	var key;
	
	if (!event.charCode) key = String.fromCharCode(event.which);
	else key = String.fromCharCode(event.charCode);
	
	if (ddNumericFieldsChars.indexOf(key) != -1) event.preventDefault();
	//Отсекаем ctrl+v
	if (event.ctrlKey && key == "v") event.preventDefault();
});
			';
		}

		$output .= "\n// ---------------- mm_ddNumericFields :: End -------------";

		$e->output($output . "\n");
	}
}
?>