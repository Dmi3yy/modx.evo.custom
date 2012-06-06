//<?php
/**
 * EasyAdvertising
 *
 * снипет вывода рекламы на сайте
 *
 * @category	snippet
 * @version 	1.02 
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties
 * @internal	@modx_category Content
 */
/*
*  EasyAdvertising - снипет вывода рекламы на сайте
*  Версия 1.02 (17.05.2012)
*  Авторы:  Леха.com
*           thebat053
*  ver.1.02 lo-pata (s.vlksm@gmail.com)
*
*  Параметры:
*  limit	- сколько блоков выводить
*  area		- рекламная зона
*  sort		- "rnd", по-умолчанию сортируется по позиции
*  regCss	- подключение стилей из чанка, можно использовать @FILE: для подключения из файла
*  regJs	- аналогично regCss, только подключение javascript
*  noJs		- по-умолчанию для показа flash-баннеров подключается swfobject.js, 
*				этот параметр позволяет его отключить
*				(путь к файлу assets/modules/easyadvertising/flash)
*  noCss	- по-умолчанию для показа flash-баннеров подключается eadvt.css, 
*				этот параметр позволяет его отключить
*				(путь к файлу assets/modules/easyadvertising/css)
*/
if (!class_exists("EasyAdvertising")) {
    class EasyAdvertising {
		
		public $js; 
        public $css; 
        public $mx;
		
		function __construct($modx) {
			$this->js = '';
			$this->css = '';
			$this->mx = $modx;
		}
		
		function is_show_advrt ($url, $template, $ex_template) {
			if (!empty($template)) {
				if ($this->find_template($url, $template)) 
					return !$this->find_template($url, $ex_template);
			} else
				return !$this->find_template($url, $ex_template);
		}
		   
		function find_template($url, $template) {
            $cur_id = $this->mx->documentIdentifier;
            $temp = explode("\r\n", $template);
			foreach ($temp as $value) {
				$value = trim($value);
				if ($value != '') {
                    if (substr($value,-1) == '*' && (int)$value > 0) { // если указан id раздела с документами
                        $par_arr = $this->mx->getParentIds($cur_id); 
                        if (substr($value,-2) == '**') // включая страницу раздела
                            $par_arr[] = $cur_id;
                        if (in_array((int)$value, $par_arr)) 
                            return true;
                    } else {
                        if (is_int($value)) {// если в строке шаблона указан ид, а не урл
                            if ($cur_id == (int)$value)
                                return true;
                            else if ($this->mx->getConfig('friendly_urls') == 1)
                                $value = $this->mx->makeUrl((int)$value, '', '', 'full');
                        } 
                        if (stripos( $url, $value) !== false) 
                            return true;
                    }    
				}    
			}
			return false;
		}
		
		function get_banner($row, $modUrl, $protocol="http://") {
			$lnk = '<a target="_blank" href="'.
				($row['jump_counted'] == 1 ? $protocol.$_SERVER['HTTP_HOST'].'/click.php?id='.$row['id'] : $row['link']).'">';
			$row['content'] = htmlspecialchars_decode($row['content']);
			if (!is_file($row['content'])) {	// html-код
			
                if (strpos($row['content'], '[+link_start+]'))
					$banner = str_replace(array('[+link_start+]','[+link_finish+]') , array($lnk,'</a>'), $row['content']);	
				else 
					$banner = $lnk.$row['content'].'</a>';	
			
            } else {							// путь к файлу
				
                $ext = pathinfo($row['content'], PATHINFO_EXTENSION);
				if ($ext == 'swf') {
					
					$alt = '<a href="http://www.adobe.com/go/getflashplayer">
							<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" 
							alt="Get Adobe Flash player" /></a>';
					
					//рисуем ссылку, если нужно считать переходы или если заполнено поле "линк" (т.е. не зашита в баннер)
					if ($row['jump_counted'] == 1 || !empty($row['link'])) { 
						$banner = '<div id="eadvt_wrapper'.$row['id'].'">'.$lnk.'</a><div id="eadvt'.$row['id'].'">'.$alt.'</div></div>';
					} else 
						$banner = '<div id="eadvt'.$row['id'].'">'.$alt.'</div>';
					
					$fl = getimagesize($row['content']);
					$this->js .= "swfobject.embedSWF('".$row['content']."', 'eadvt".$row['id']."', '".$fl[0]."', '".$fl[1]."', '6.0.65', '".$modUrl."flash/expressInstall.swf', {}, {wmode:'opaque',quality:'high'});\n";
					$this->css .= "#eadvt_wrapper".$row['id']." {width:".$fl[0]."px; height:".$fl[1]."px} \n";
				} else 
					$banner = $lnk.'<img src="'.$row['content'].'" alt="banner '.$row['id'].'" /></a>';	
			
            }
            
			return $banner;	
		}	
	}
}

$regCss		= (isset($regCss) && !empty($regCss)) ? $regCss : '';
$regJs		= (isset($regJs) && !empty($regJs)) ? $regJs : '';
$noCss		= isset($noCss) ? (int)$noCss : 0;
$noJs		= isset($noJs) ? (int)$noJs : 0;

$ea = new EasyAdvertising($modx);
$mod_table = $modx->getFullTableName("site_easyadvt");
$modUrl = 'assets/modules/easyadvertising/';
$url=$modx->getConfig('server_protocol')."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$out = '';
$i = 0; //инициализация счетчика рекламных блоков

$limit = isset($limit) ? (int)$limit : 0;
$where = "published = 1 AND pub_date < ".time()." AND (unpub_date > ".time()." OR unpub_date = 0 ) ";
$where .= "AND area = '".(isset($area) ? trim($area) : '')."' ";
$where .= "AND (count_view < total_view OR total_view = 0)";
$where .= "AND (jump_count < total_jump OR total_jump = 0)";
$sort = isset($sort) ? ((strtoupper($sort) == "RND") ? "RAND()" : $sort) : "pos ASC";

$sql = "SELECT 
			id, pos, template, ex_template, content, counted, count_view, 
			total_view, jump_counted, total_jump, link 
		FROM ".$mod_table."
		WHERE ".$where."
		ORDER BY ".$sort;
$res = $modx->db->query($sql);

while (($row = $modx->db->getRow($res)) && ($i < $limit || $limit == 0)) {
	if ($ea->is_show_advrt($url, $row['template'], $row['ex_template'])) {
		if (($row['count_view'] < $row['total_view'] || $row['total_view'] == 0) 
			&& ($row['jump_count'] < $row['total_jump'] || $row['total_jump'] == 0)) { // если удовлетворяет условиям кол-ва показов и кликов
			$out .= $ea->get_banner($row, $modUrl, $modx->getConfig('server_protocol').'://');
			$i = ($limit != 0) ? ++$i : $i;
			if ($row['counted'] == 1) 
				$modx->ui[] = $row['id'];
		}
	}
}

if (isset($regCss) && !empty($regCss)) {
	if (substr($regCss,0,6) == '@FILE:') 
		$modx->regClientCSS($regCss);
	else 
		$modx->regClientCSS('<style type="text/css">'.$modx->getChunk($regCss).'</style>');
} else if (!empty($ea->css) && $noCss != 1) {
	$modx->regClientCSS($modUrl.'css/eadvt.css');
	$modx->regClientCSS('<style type="text/css">'.$ea->css.'</style>');
}

if (isset($regJs) && !empty($regJs)) {
	if (substr($regJs,0,6) == '@FILE:') 
		$modx->regClientScript($regJs);
	else 
		$modx->regClientScript('<script type="text/javascript">'.$modx->getChunk($regJs).'</script>');
} else if (!empty($ea->js) && $noJs != 1) {
	$modx->regClientScript($modUrl.'flash/swfobject.js');
	$script = '<script type="text/javascript">'.$ea->js.'</script>';
	$modx->regClientScript($script);
}	

return $out;