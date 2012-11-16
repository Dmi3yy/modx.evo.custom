//<?php
/**
 * MultiFiles
 * 
 * Добавление нескольких файлов к странице
 *
 * @category 	plugin
 * @version 	1.0.2
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Temus (temus3@gmail.com)
 * @internal	@properties &tvIds=TV Ids;text;&templ=Template;text;&role=Role;text;
 * @internal	@events OnDocFormRender
 * @internal    @legacy_names MultiFiles
 */
 
//defined('IN_MANAGER_MODE') or die();

global $content,$default_template;
$tvIds = isset($tvIds) ? $tvIds : 101;
$templ = isset($templ) ? explode(',',$templ) : false;
$role = isset($role) ? explode(',',$role) : false;
$cur_templ = isset($_POST['template']) ? $_POST['template'] : (isset($content['template']) ? $content['template'] : $default_template);
$cur_role = $_SESSION['mgrRole'];
if (($templ && !in_array($cur_templ,$templ)) || ($role && !in_array($cur_role,$role))) return;

$lang['insert']='Вставить';
$lang['url']='Адрес:';
$lang['title']='Название:';

$e = &$modx->Event;
if ($e->name == 'OnDocFormRender') {
require_once(MODX_MANAGER_PATH.'includes/tmplvars.inc.php');
$modx_script = renderFormElement('file',0,'','','');
preg_match('/(<script[^>]*?>.*?<\/script>)/si', $modx_script, $matches);
$output = $matches[0];
$output .= <<< OUT
<!-- MultiFiles -->
<style type="text/css">
.fileitem {border:1px solid #e3e3e3; margin:0 0 5px; padding:2px 5px 5px 5px; overflow:hidden; white-space:nowrap; zoom:1}
.fileitem span {display:inline-block; padding-top:3px;}
.fileitem input {line-height:1.1; vertical-align:middle;}
</style>
<script type="text/javascript">
window.ie9=window.XDomainRequest && window.performance; window.ie=window.ie && !window.ie9; /* IE9 patch */
var MultiFiles = new Class({
	initialize: function(fid){
		this.name = fid;
		this.fid = $(fid);
		var fArr = (this.fid.value && this.fid.value!='[]') ? Json.evaluate(this.fid.value) : [null];
		this.fid.setStyle('display','none');
		this.box = new Element('div',{'class':'fileEditor'});
		this.fid.getParent().adopt(this.box);
		this.file=0;
		for (var f=0;f<fArr.length;f++) this.addItem(fArr[f]);
		if (typeof(SetUrl) != 'undefined') {
			this.OrigSetUrl = SetUrl;
			SetUrl = function(url, width, height, alt) {
				var lastFile = lastFileCtrl;
				this.OrigSetUrl(url, width, height, alt);
				if ($(lastFile)!=null) $(lastFile).fireEvent('change');
			}.bind(this)
		}
		this.sort=new Sortables(this.box,{
			onStart: function(el){el.setStyles({'background':'#f0f0f0','opacity':1});},
			onComplete: function(el){el.setStyle('background','none');this.setEditor();}.bind(this)
		});	
		this.box.getElements('div.fileitem').setStyle('cursor','move');
		this.box.getElements('input[type=text]').addEvent('click',function(){this.focus();});
	},
	br: function(){return new Element('br');},
	sp: function(text){return new Element('span').setText(text);},
	addItem: function(values,elem){
		this.file++;
		var f = this.file;
		var rowDiv = new Element('div',{'class':'fileitem'});
		if (elem) {rowDiv.injectAfter(elem);} else {this.box.adopt(rowDiv);}
		if (!values) values=['','']; 
		var fileURL = new Element('input',{'type':'text','name':'file_'+this.name+'_'+f,'id':'file_'+this.name+'_'+f,'class':'fileField','value':values[0],'events':{
			'change':function(){this.setEditor();}.bind(this)
		}});
		var bInsert = new Element('input',{'type':'button','value':'{$lang['insert']}','events':{
			'click':function(){BrowseFileServer('file_'+this.name+'_'+f)}.bind(this)
		}});
		var fileName = new Element('input',{'type':'text','class':'fileField','value':values[1],'events':{
			'keyup':function(){this.setEditor();documentDirty=true;}.bind(this)
		}});
		var bAdd = new Element('input',{'type':'button','value':'+','events': {
			'click':function(){this.addItem(null,rowDiv);}.bind(this)
		}});
		rowDiv.adopt(this.sp('{$lang['url']}'),this.br(),fileURL,bInsert,this.br(),this.sp('{$lang['title']}'),this.br(),fileName,bAdd);
		if (this.box.getElements('div.fileitem').length>1) rowDiv.adopt(new Element('input',{'type':'button','value':'-','events':{
			'click':function(){rowDiv.remove();this.setEditor();}.bind(this)
		}}));
		fileURL.fireEvent('change');
	},
	setEditor: function(){
		var fArr=new Array();
		this.box.getElements('div.fileitem').each(function(item){
			var itemsArr=new Array();
			var inputs=item.getElements('input[type=text]');
			var noempty=false;
			inputs.each(function(item){itemsArr.push(item.value); if (item.value) noempty=true;});
			if (noempty) fArr.push(itemsArr);
		});
		this.fid.value = Json.toString(fArr);
	}
});
window.addEvent('domready', function(){
	var tvIds = [$tvIds];
	for (var i=0;i<tvIds.length;i++){
		var fid = 'tv'+ tvIds[i];
		if($(fid)!=null) {var modxMultiFiles=new MultiFiles(fid);}
	}
});
</script>
<!-- /MultiFiles -->
OUT;
$e->output($output);
}