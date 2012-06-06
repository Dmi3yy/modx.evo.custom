<?
/**************************************
/** 
* Diff plugin for Modx Evo
*
* en: Class to work with the history of changes in snippets, chunks, templates, modules and plugins
* ru: Класс для работы с историей изменений в сниппетах, чанках, шаблонах, модулях и плагинах
* 
* Сохранение элемента
* <code>
* $Diff=new ElementVer($modx,'template',$folderPlugin);
* $Diff->save($modx->Event->params['id'],'post');
* </code>
*
* Удаление элемента
* <code>
* $Diff=new ElementVer($modx,'snippet',$folderPlugin);
* $Diff->del($modx->Event->params['id']);
* </code>
*
* Вывод формы с версиями
* <code>
* $Diff=new ElementVer($modx,'snippet',$folderPlugin);
* $out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
* $modx->Event->output($out);
* </code>
*
* @version 2.2
* @author Borisov Evgeniy aka Agel Nash (agel_nash@xaker.ru)
* @date 06.06.2012
* @copyright 2012 Agel Nash
* @link http://agel-nash.ru
* @license http://www.opensource.org/licenses/lgpl-3.0.html LGPL 3.0
*
* @category plugin
* @internal @event OnTempFormDelete,OnTempFormSave,OnTempFormRender,OnSnipFormDelete,OnSnipFormSave,OnSnipFormRender,OnPluginFormDelete,OnPluginFormSave,OnPluginFormRender,OnModFormDelete,OnModFormSave,OnModFormRender,OnChunkFormDelete,OnChunkFormSave,OnChunkFormRender,OnDocFormDelete,OnDocFormRender,OnDocFormSave
* @internal @properties &idBlock=ID блока;text;Version &folderPlugin=Папка плагина;text;diff &which_jquery=Подключить jQuery;list;Не подключать,/assets/js/,google code,custom url;/assets/js/ &js_src_type=Свой url к библиотеке jQuery;text; &jqname=Имя Jquery переменной в noConflict;text;j &lang=Локализация;list;en,ru;ru
* @internal @modx_category Manager and Admin
*
* @todo Добавить в параметры возможность выбрать историю каких элементов сохранять
* @todo Автоматическое определение локализации
* @todo Вынести папки с историей в /assets/cache/
*/
/*************************************/
class ElementVer implements langVer{
	/** @var string Файл со списокм версий и описаний всех элементов */
	public $verfile='';
	/** @var string Название папки с плагином */
	public $dir='';
	/** @var class Экземпляр парсера modx */
	private $modx;
	/** @var string Текущий режим с которым работаем */
	private $active='';
	/** @var string  Имя jQuery переменной с которой дальше будем работать */
	private $jqname='';
	/** @var string Текущий элемент с которым работаем */
	private $ver=0;
	
	/**
	* Конструктор класса
	* Название папки можно было бы и не передавать, но т.к. в админке modx все равно прописать ее необходимо, то не будем писать лишний код
	* @param class $modx экземпляр парсера modx
	* @param string $active тип элемента с которым будем работать (snippet | template | plugin | module | chunk)
	* @param string $dir название папки с плагином
	* @param string $ver файл в котором будут храниться все версии
    */
	function __construct(&$modx,$active,$dir,$ver='version.inc'){
		$this->modx=$modx;
		if(!(is_object($this->modx) && isset($this->modx->Event->name))){
			exit(langVer::err_nomodx);
		}
		if(in_array($active,array('snippet','template','plugin','module','chunk','document'))){
			$this->active=$active;
		}else{
			exit(langVer::err_mode);
		}
		
		/*
		* en: Still have to specify the folder name in the parameter plug-in
		* ru: Все равно придется указывать название папки в параметре плагина
		*
		$dir=pathinfo(__FILE__);
		if(!defined('__DIR__')) { 
			$dir=explode("\\",$dir['dirname']);
		}else{
			$dir=explode("/",$dir['dirname']);
		}
		$this->dir=end($dir);
		*/
		$this->dir=$dir;
		$this->verfile=$ver;
	}

	/**
	* Функция генирации пути к папки 
	* @param bool $full какой путь к папке получить: с http или относительно корня веб-сервера. По умолчанию относительно корня.
	* @param bool $mode Включить в путь папку с названием режим с которым сейчас работаем (чанк, шаблон, и т.д.) По умолчанию вместе с папкой
	* @return string Путь к папке 
    */
	public function GVD($full=true,$mode=true){
		$dir=($full?$this->modx->config['base_path']:$this->modx->config['site_url']).'assets/plugins/'.$this->dir.'/'.($mode?($this->active.'/'):'');
		return $dir;
	}
	
	/**
	* Функция которая инъектит javascript код сгенерированный функцией {@link render}
	* @see {@link render}
	* @param string $idBlock ID HTML блока который будем вставлять на страницу. По умолчанию Version
	* @param string $which_jquery Тип подключения jquery к странице (google code | /assets/js/ | custom url | none). По умолчанию /assets/js/
	* @param string $jqname Имя jQuery переменной с которой дальше будем работать. По умолчанию j
	* @param string $url Адрес по которому будем грузить jQuery библиотеку если which_jquery установлен в custom url. По умолчанию пусто.
	* @return string HTML 
    */
	public function loadJs($idBlock='Version',$which_jquery='/assets/js/',$jqname='j',$url=''){
		$js_include='';
		$this->jqname=$jqname;
		switch ($which_jquery){
			case 'google code':{
				$js_include  = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
				break;
			}
			case '/assets/js/':{
				$js_include  = '<script src="'.$this->modx->config['site_url']. '/assets/js/jquery-1.4.4.min.js" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
				break;
			}
			case 'custom url':{
				if($url!=''){
					$js_include  = '<script src="'.$url.'" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
				}else{
					$js_include='';
				}
				break;
			}
			default:{ //no include;
				$js_include='';
			}
		}
		$js_include.=$this->render($idBlock);
		return $js_include;
	}
	
	/**
	* Во время сохранения элемента сохраняем его версию и данные
	* @param int $id ID элемента
	* @param string $postname имя POST переменной в которой передается содержимое элемента. По умолчанию post
	* @param string $descV имя POST переменной от куда брать описание текущей версии. По умолчанию descVersion
	* @param string $save имя POST переменной обозначающий сохранять ли текущую версию. По умолчанию savev
	* @return bool статус сохранения истории элемента
    */
	public function save($id,$postname='post',$descV='descVersion',$save='savev'){
		if(!(isset($_POST[$postname]) && $_POST[$postname]!='')){
			return false;
		}
		$desc=isset($_POST[$descV])?$_POST[$descV]:'';
		
		if(!isset($_POST[$save])){
			return false;
		}
		$put=base64_encode($_POST[$postname]);
		$dir=$this->GVD(true,true);
		if(!is_dir($dir.$id)) {
			if(!mkdir($dir.$id,0777,true)){
				return false;
			}
		}
		
		$flag=false;
		$file=md5($put);
		if(!file_exists($dir.$id.'/'.md5($put))){
			$count=file_put_contents($dir.$id.'/'.md5($put),$put);
			if($count<=0){
				return false;
			}
			$flag=true;
		}
		if($flag || $desc!=''){
			if(file_exists($dir.'/'.$this->verfile)){
				$data=unserialize(file_get_contents($dir.'/'.$this->verfile));
				$ver=$data[$id]['last'];
				if($flag){
					$data[$id]['last']++;
					$ver++;
					$data[$id][$ver]['file']=$file;
				}
				$data[$id][$ver]['desc']=$desc;
			}else{
				$data[$id]['last']=1;
				$data[$id][1]['desc']=$desc;
				$data[$id][1]['file']=$file;
			}
			$count=file_put_contents($dir.$this->verfile,serialize($data));
			if($count<=0){
				return false;
			}
		}
		return true;
	}
	
	/**
	* Во время удаления элемента удаляем всю его историю
	* @param int $id ID элемента
	* @return bool статус удаления всей истории элемента
    */
	public function del($id){
		$dir=$this->GVD(true,true);
		if(!file_exists($dir.'/'.$this->verfile)){
			return false;
		}
		$data=unserialize(file_get_contents($dir.'/'.$this->verfile));
		if(!isset($data[$id]['last'])){
			return false;
		}
		unset($data[$id]['last']);
		foreach($data[$id] as $iditem=>$item){
			if(!unlink($dir.$id.'/'.$item['file'])){
				return false;
			}
		}
		unset($data[$id]);
		if(is_dir($dir.$id)){
			if(!rmdir($dir.$id.'/')){
				return false;
			}
		}
		$count=file_put_contents($dir.'/'.$this->verfile,serialize($data));
		if($count<=0){
			return false;
		}
		return true;
	}
	
	/**
	* Получаем данные об элементе из файла с историей
	* @param int $id ID элемента
    * @access private
	* @return string HTML код с содержимым таблицы вида <tr><td>...data...</td></tr>
    */
	private function getDataVer($id){
		$out=array();
		$flag=true;
		$data=array();
		$dir=$this->GVD(true,true);
		
		if(!file_exists($dir.$this->verfile)){
			$flag=false;
		}else{
			$data=unserialize(file_get_contents($dir.$this->verfile));
		}
		if(isset($data[$id]) && $flag){
			$this->ver=$data[$id]['last'];
			unset($data[$id]['last']);
			
			foreach($data[$id] as $iditem=>$desc){
				$tmp='';
				if($desc['desc']==''){
					$tmp=langVer::form_nodesc;
				}else{
					$tmp=htmlspecialchars($desc['desc']);
				}
				if($iditem!=$this->ver){
					$out[$iditem]=langVer::word_ver.' '.$iditem.': <i>'.$tmp.'</i> ';
					$out[$iditem].=' &nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="delversion" rel="'.$desc['file'].'">'.langVer::word_del.'</a> | <a href="#" class="loadversion" rel="'.$desc['file'].'">'.langVer::word_load.' </a> ';
				}else{
					$out[$iditem]='<strong>'.langVer::word_ver.' '.$iditem.': <i>'.$tmp.'</i></strong>';
				}
			}
		}
		
		if(count($out)>0){
			$out=array_reverse($out);
			$out=$this->modx->makeList($out);
		}else{
			$out="<p>".langVer::form_noversion."</p>";
		}
		$out='<tr><td>'.str_replace("'","\"",$out).'</td></tr>';
		return $out;
	}
	
	/**
	* Формируем html с JavaScript'ом для отображения текста в нужных местах
    * @access private
	* @return string HTML 
    */
	private function render($idBlock){
		$output='';
		$tabs=true;
		if($this->jqname==''){
			exit(langVer::err_loadjs);
		}
		switch($this->active){
			case 'snippet':{
				$js_tab_object='tpSnippet';
				$id=$this->modx->Event->params['id'];
				$lastTab='tabProps';
				$name='post';
				break;
			}
			case 'template':{
				$js_tab_object='tpResources';
				$lastTab='tabAssignedTVs';
				$name='post';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'plugin':{
				$js_tab_object='tpSnippet';
				$lastTab='tabEvents';
				$name='post';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'module':{
				$js_tab_object='tpModule';
				$lastTab='tabDepend';
				$name='post';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'chunk':{
				$tabs=false;
				$name='post';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'document':{
				$js_tab_object='tpSettings';
				$lastTab='tabSettings';
				$name='ta';
				$id=$this->modx->Event->params['id'];
				break;
			}
			default:{
				exit(langVer::err_mode);
			}
		}
		if($tabs){
			$output=$this->getDataVer($id);
			$output = '<div class="tab-page" id="tab'.$idBlock.'"><h2 class="tab">'.langVer::form_nameblock.'</h2><table width="90%" border="0" cellspacing="0" cellpadding="0" >'.$output.'</table></div>';
			$output=str_replace(array("\n", "\t", "\r"), '', $output);
			
			$output = "<script type=\"text/javascript\">
			mm_lastTab = '".$lastTab."'; 
			\$".$this->jqname."('div#'+mm_lastTab).after('".$output."'); 
			mm_lastTab = 'tab".$idBlock."'; ".
			$js_tab_object.".addTabPage( document.getElementById( \"tab".$idBlock."\" ) ); 
			\$".$this->jqname."('div.sectionBody:first').before('<div class=\"sectionBody\"><p><strong>".langVer::form_descver.":</strong></p><input type=\"text\" name=\"descVersion\" style=\"width:100%\"></p><p><input type=\"checkbox\" name=\"savev\" checked /> ".langVer::form_savever."</p></div>');
			\$".$this->jqname."('.loadversion').click(function(el){
			alert('".$this->GVD(false,false)."version.ajax.php?mode=load&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."');
				\$".$this->jqname.".ajax({
					url: '".$this->GVD(false,false)."version.ajax.php?mode=load&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."',
					 cache: false,
					error: function(){
						alert('".langVer::err_noload."');
					},
					success: function(html){
						if(html!=''){
							if(\$".$this->jqname."('.oldver').length){
								\$".$this->jqname."('.oldver').val(html);
							}else{
								\$".$this->jqname."('textarea[name=".$name."]').after('<div style=\"padding:1px 1px 5px 1px; width:100%; height:16px;background-color:#eeeeee; border-top:1px solid #e0e0e0;margin-top:5px\"><span style=\"float:left;color:#707070;font-weight:bold; padding:3px\">".langVer::form_beforever."</span></div><textarea dir=\"ltr\" name=\oldver\" class=\"phptextarea oldver\" style=\"width:100%; height:370px;\" wrap=\"off\" onchange=\"documentDirty=true;\">'+html+'</textarea>');
							}
						}else{
							alert('".langVer::err_fatalload."');
						}
					}
				});
			});
			\$".$this->jqname."('.delversion').click(function(el){
				\$".$this->jqname.".ajax({
					url: '".$this->GVD(false,false)."version.ajax.php?mode=del&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."',
					 cache: false,
					context:\$".$this->jqname."(this).parent('li'),
					error: function(){
						alert('".langVer::err_noload."');
					},
					success: function(html){
						if(html!=''){
							\$".$this->jqname."(this).remove();
						}else{
							alert('".langVer::err_del."');
						}
					}
				});
			});
			</script>";
		}else{
			$output=$this->getDataVer($id);
			$output = '<div class="sectionBody"><h2 class="tab">'.langVer::form_nameblock.'</h2><table width="90%" border="0" cellspacing="0" cellpadding="0" >'.$output.'</table></div>';
			$output=str_replace(array("\n", "\t", "\r"), '', $output); 
			
			$output.="<script type=\"text/javascript\">
			\$".$this->jqname."('div.sectionBody:first table tr:last').before('<tr><td style=\"padding-top:5px\" valign=\"top\" align=\"left\"><p>".langVer::form_descver.":</p></td><td style=\"padding-top:5px\" valign=\"top\" align=\"left\"><span style=\"font-family:\'Courier New\', Courier, mono\">&nbsp; </span><input type=\"text\" style=\"width:300px\" name=\"descVersion\" ></p></td></tr><tr><td colspan=\"2\" style=\"padding-top:5px\" valign=\"top\" align=\"left\"><p><input type=\"checkbox\" name=\"savev\" checked /> ".langVer::form_savever."</p></td></tr>'); 
			
			\$".$this->jqname."('.loadversion').click(function(el){
			
				\$".$this->jqname.".ajax({
					url: '".$this->GVD(false,false)."version.ajax.php?mode=load&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."',
					 cache: false,
					error: function(){
						alert('".langVer::err_noload."');
					},
					success: function(html){
						if(html!=''){
							if(\$".$this->jqname."('.oldver').length){
								\$".$this->jqname."('.oldver').val(html);
							}else{
								\$".$this->jqname."('textarea[name=".$name."]').after('<div style=\"padding:1px 1px 5px 1px; width:100%; height:16px;background-color:#eeeeee; border-top:1px solid #e0e0e0;margin-top:5px\"><span style=\"float:left;color:#707070;font-weight:bold; padding:3px\">".langVer::form_beforever."</span></div><textarea dir=\"ltr\" name=\oldver\" class=\"phptextarea oldver\" style=\"width:100%; height:370px;\" wrap=\"off\" onchange=\"documentDirty=true;\">'+html+'</textarea>');
							}
						}else{
							alert('".langVer::err_fatalload."');
						}
					}
				});
			});
			\$".$this->jqname."('.delversion').click(function(el){
				\$".$this->jqname.".ajax({
					url: '".$this->GVD(false,false)."version.ajax.php?mode=del&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."',
					 cache: false,
					context:\$".$this->jqname."(this).parent('li'),
					error: function(){
						alert('".langVer::err_noload."');
					},
					success: function(html){
						if(html!=''){
							\$".$this->jqname."(this).remove();
						}else{
							alert('".langVer::err_del."');
						}
					}
				});
			});
			</script>";
			
		}
		return $output;
	}
}
?>