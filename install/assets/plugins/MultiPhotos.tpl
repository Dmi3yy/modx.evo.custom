//<?php
/**
 * MultiPhotos
 * 
 * Добавление нескольких фотографий к странице
 *
 * @category 	plugin
 * @version 	1.26
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Temus (temus3@gmail.com)
 * @internal	@properties &tvIds=TV Ids;text;&templ=Template;text;&role=Role;text;&resize=Resize: enable;list;true,false;false;&crop=Resize: cropping;list;true,false;true;&prefix=Resize: prefix;text;s_;&th_width=Resize: width;text;&th_height=Resize: height;text;&auto_big=Resize: auto big img;list;true,false;false;&auto_small=Resize: auto small img;list;true,false;false;&w=Preview: width;text;&h=Preview: height;text;&thumbUrl=PHPThumb URL;text;
 * @internal	@events OnDocFormRender,OnBeforeDocFormSave
 * @internal    @installset base
 * @internal    @legacy_names MultiPhotos
 */
 
///defined('IN_MANAGER_MODE') or die();

global $content,$default_template,$tmplvars;
$tvIds = isset($tvIds) ? $tvIds : 111;
$w = isset($w) ? $w : 160;
$h = isset($h) ? $h : 120;
$templ = isset($templ) ? explode(',',$templ) : false;
$role = isset($role) ? explode(',',$role) : false;
$style = (isset($w) || isset($h)) ? "'max-width':'{$w}px','max-height':'{$h}px','cursor':'pointer'" : '';
$site = $modx->config['site_url'];
$thumbUrl = isset($thumbUrl) ? 'url = (url != "") ? ("'.$thumbUrl.'?src="+escape(url)+"&w='.$w.'&h='.$h.'") : url; ' : 'url = (url != "" && url.search(/http:\/\//i) == -1) ? ("'.$site.'" + url) : url;';
$cur_templ = isset($_POST['template']) ? $_POST['template'] : (isset($content['template']) ? $content['template'] : $default_template);
$cur_role = $_SESSION['mgrRole'];
if (($templ && !in_array($cur_templ,$templ)) || ($role && !in_array($cur_role,$role))) return;

$resize = isset($resize)&&($resize=='true') ? 1 : 0;
$crop = isset($crop)&&($crop=='true') ? 1 : 0;
$prefix = isset($prefix) ? $prefix : 's_';
$auto_big = isset($auto_big)&&($auto_big=='true') ? 1 : 0;
$auto_small = isset($auto_small)&&($auto_small=='true') ? 1 : 0;

$lang['insert']='Вставить';
$lang['url']='Путь:';
$lang['link']='Ссылка или большая картинка:';
$lang['title']='Название:';

$e = &$modx->Event;
if ($e->name == 'OnDocFormRender') {
require_once(MODX_MANAGER_PATH.'includes/tmplvars.inc.php');
$modx_script = renderFormElement('image',0,'','','');
preg_match('/(<script[^>]*?>.*?<\/script>)/si', $modx_script, $matches);
$output = $matches ? $matches[0] : '';
$output .= <<< OUT
<!-- MultiPhotos -->
<style type="text/css">
.fotoitem {border:1px solid #e3e3e3; margin:0 0 5px; padding:2px 5px 5px 5px; position:relative; overflow:hidden; white-space:nowrap; zoom:1}
.fotoitem span {display:inline-block; padding-top:3px;}
.fotoitem input {line-height:1.1; vertical-align:middle;}
.fotoimg {position:absolute; right:0; padding-top:3px;}
</style>
<script type="text/javascript">
window.ie9=window.XDomainRequest && window.performance; window.ie=window.ie && !window.ie9; /* IE9 patch */
var MultiPhotos = new Class({
	initialize: function(fid){
		this.name = fid;
		this.fid = $(fid);
		var hpArr = (this.fid.value && this.fid.value!='[]') ? Json.evaluate(this.fid.value) : [null];
		this.fid.setStyle('display','none');
		this.box = new Element('div',{'class':'fotoEditor'});
		this.fid.getParent().adopt(this.box);
		this.foto=0;
		for (var f=0;f<hpArr.length;f++) this.addItem(hpArr[f]);
		if (typeof(SetUrl) != 'undefined') {
			this.OrigSetUrl = SetUrl;				
			SetUrl = function(url, width, height, alt) {
				var lastfoto = lastImageCtrl;
				this.OrigSetUrl(url, width, height, alt);
				if ($(lastfoto)!=null) $(lastfoto).fireEvent('change');
			}.bind(this)
		}
		this.sort=new Sortables(this.box,{
			onStart: function(el){el.setStyles({'background':'#f0f0f0','opacity':1});},
			onComplete: function(el){el.setStyle('background','none');this.setEditor();}.bind(this)
		});	
		this.box.getElements('div.fotoitem').setStyle('cursor','move');
		this.box.getElements('input[type=text]').addEvent('click',function(){this.focus();});
	},
	br: function(){return new Element('br');},
	sp: function(text){return new Element('span').setText(text);},
	addItem: function(values,elem){
		this.foto++;
		var f = this.foto;
		var rowDiv = new Element('div',{'class':'fotoitem'});
		if (elem) {rowDiv.injectAfter(elem);} else {this.box.adopt(rowDiv);}
		if (!values) values=['','','']; 
		var imgURL = new Element('input',{'type':'text','name':'foto_'+this.name+'_'+f,'id':'foto_'+this.name+'_'+f,'class':'imageField','value':values[0],'events':{
			'change':function(){
				var url = imgURL.value;
				{$thumbUrl}
				var imgDiv=$('foto_'+this.name+'_'+f+'_'+'PrContainer');
				if (imgDiv!=null) imgDiv.remove();
				if (url != "") {
					new Element('div',{'class':'fotoimg','id':'foto_'+this.name+'_'+f+'_'+'PrContainer','styles':{'width':'{$w}px'}}).injectTop(rowDiv).adopt(
						new Element('img',{'src':url,'styles':{ $style },'events':{
							'click':function(){BrowseServer('foto_'+this.name+'_'+f)}.bind(this)
						}})
					);
				}
				this.setEditor();
			}.bind(this)
		}});
		var bInsert = new Element('input',{'type':'button','value':'{$lang['insert']}','events':{
			'click':function(){BrowseServer('foto_'+this.name+'_'+f)}.bind(this)
		}});
		var linkURL = new Element('input',{'type':'text','name':'link_'+this.name+'_'+f,'id':'link_'+this.name+'_'+f,'class':'imageField','value':values[1],'events':{
			'change':function(){this.setEditor();}.bind(this)
		}});
		var bInsertLink = new Element('input',{'type':'button','value':'{$lang['insert']}','events':{
			'click':function(){BrowseServer('link_'+this.name+'_'+f)}.bind(this)
		}});
		var imgName = new Element('input',{'type':'text','class':'imageField','value':values[2],'events':{
			'keyup':function(){this.setEditor();documentDirty=true;}.bind(this)
		}});
		var bAdd = new Element('input',{'type':'button','value':'+','events': {
			'click':function(){this.addItem(null,rowDiv);}.bind(this)
		}});
		rowDiv.adopt(this.sp('{$lang['url']}'),this.br(),imgURL,bInsert,this.br(),this.sp('{$lang['link']}'),this.br(),linkURL,bInsertLink,this.br());
		rowDiv.adopt(this.sp('{$lang['title']}'),this.br(),imgName,bAdd);
		if (this.box.getElements('div.fotoitem').length>1) rowDiv.adopt(new Element('input',{'type':'button','value':'-','events':{
			'click':function(){rowDiv.remove();this.setEditor();}.bind(this)
		}}));
		imgURL.fireEvent('change');
	},
	setEditor: function(){
		var hpArr=new Array();
		this.box.getElements('div.fotoitem').each(function(item){
			var itemsArr=new Array();
			var inputs=item.getElements('input[type=text]');
			var noempty=false;
			inputs.each(function(item){itemsArr.push(item.value); if (item.value) noempty=true;});
			if (noempty) hpArr.push(itemsArr);
		});
		this.fid.value = Json.toString(hpArr);
	}
});
window.addEvent('domready', function(){
	var tvIds = [$tvIds];
	for (var i=0;i<tvIds.length;i++){
		var fid = 'tv'+ tvIds[i];
		if($(fid)!=null) {var modxMultiPhotos=new MultiPhotos(fid);}
	}
});
</script>
<!-- /MultiPhotos -->
OUT;
$e->output($output);
}
if ($e->name == 'OnBeforeDocFormSave'){
$tvIds=explode(',',$tvIds);
foreach ($tvIds as $tvid) {
	if (empty($th_width) && empty($th_height)) return;
	if (!$resize || !isset($tmplvars[$tvid]) || empty($tmplvars[$tvid][1])) continue;
	$fotoArr=json_decode($tmplvars[$tvid][1]);
	@set_time_limit(0);
	foreach ($fotoArr as $k=>&$v) {
		if (!empty($v[1]) && $auto_small) $v[0]=$v[1];
		if (!empty($v[0])){
			$filename = basename($v[0]);
			$dirname = str_replace($filename,'',$v[0]);
			if (!($auto_small && !empty($v[1])) && ($prefix==substr($filename, 0, strlen($prefix)) || $prefix==substr($dirname, -strlen($prefix)))) continue;
			$new_path = '../'.$dirname.$prefix.$filename;
			$imgInfo = @getImageSize('../'.$v[0]);
			if (!is_array($imgInfo)) continue;
			ob_start();
			$img_width = $imgInfo[0];
			$img_height = $imgInfo[1];
			$width=$img_width;
			$height=$img_height;
			$posX=0;
			$posY=0;	
			$ratio = $img_height / $img_width;
			if (!$th_height) $th_h=round($th_width*$ratio); else $th_h=$th_height;
			if (!$th_width) $th_w=round($th_height/$ratio); else $th_w=$th_width;
			$th_ratio = $th_h / $th_w;
			if ($crop) {
				if ($ratio > $th_ratio) {
					$height=round($img_width*$th_ratio);
					$posY=round(($img_height-$height)/2);
				}
				if ($ratio < $th_ratio) {
					$width=round($img_height/$th_ratio);
					$posX=round(($img_width-$width)/2);
				}
			}
			else {
				if ($ratio > $th_ratio) $th_w=round($th_h/$ratio);
				if ($ratio < $th_ratio) $th_h=round($th_w*$ratio);
			}
			switch($imgInfo[2]){
			case 1:
				$src = ImageCreateFromGif('../'.$v[0]);
				$dst = ImageCreateTrueColor($th_w, $th_h);
				ImageCopyResampled($dst, $src, 0, 0, $posX, $posY, $th_w, $th_h, $width, $height);
				ImageGif($dst,$new_path);
				break;
			case 2:
				$src = ImageCreateFromJpeg('../'.$v[0]);
				$dst = ImageCreateTrueColor($th_w, $th_h);
				ImageCopyResampled($dst, $src, 0, 0, $posX, $posY, $th_w, $th_h, $width, $height);
				ImageJpeg($dst,$new_path,90);
				break;
			case 3:
				$src = ImageCreateFromPng('../'.$v[0]);
				$dst = ImageCreateTrueColor($th_w, $th_h);
				imagesavealpha($dst, true);
				$cc=imagecolorallocatealpha($dst, 255, 255, 255, 127);
				imagefill($dst, 0, 0, $cc); 
				ImageCopyResampled($dst, $src, 0, 0, $posX, $posY, $th_w, $th_h, $width, $height);
				ImagePng($dst,$new_path);
				break;
			}
			imagedestroy($src);
			imagedestroy($dst);
			if (empty($v[1]) && $auto_big) $v[1]=$v[0];
			$v[0]=$dirname.$prefix.$filename;
			ob_end_clean();
		}
	}
	$tmplvars[$tvid][1]=str_replace('\\/', '/', json_encode($fotoArr));
}
}