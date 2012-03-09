//<?php
/**
 * TvTable
 * 
 * Добавление к странице таблицы данных
 *
 * @category 	plugin
 * @version 	1.11
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author      Temus (temus3@gmail.com)
 * @internal	@properties &tvIds=TV Ids;text;&templ=Template;text;&role=Role;text;&loadfile=Load csv file;list;true,false;false;
 * @internal	@events OnDocFormRender,OnBeforeDocFormSave
 * @internal    @legacy_names TvTable
 */
 
//defined('IN_MANAGER_MODE') or die();

global $content,$default_template,$tmplvars;
$tvIds = isset($tvIds) ? $tvIds : 102;
$templ = isset($templ) ? explode(',',$templ) : false;
$role = isset($role) ? explode(',',$role) : false;
$cur_templ = isset($_POST['template']) ? $_POST['template'] : (isset($content['template']) ? $content['template'] : $default_template);
$cur_role = $_SESSION['mgrRole'];
if (($templ && !in_array($cur_templ,$templ)) || ($role && !in_array($cur_role,$role))) return;

$loadfile = isset($loadfile)&&($loadfile=='true') ? "this.box.adopt(new Element('input',{'type':'file','name':'file_'+fid,'styles':{'margin-top':'5px'}}));" : "";

$e = &$modx->Event;
if ($e->name == 'OnDocFormRender') {
$output = <<< OUT
<!-- TvTable -->
<script type="text/javascript">
window.ie9=window.XDomainRequest && window.performance; window.ie=window.ie && !window.ie9; /* IE9 patch */
var TvTable = new Class({
	initialize: function(fid){
		this.fid = $(fid);
		var tvtArr = (this.fid.value) ? Json.evaluate(this.fid.value) : [null,null];
		this.fid.setStyle('display','none');
		this.box = new Element('div',{'class':'tvtEditor'});
		this.fid.getParent().adopt(this.box);
		this.addHeader(tvtArr[0]);
		for (var row=1;row<tvtArr.length;row++) this.addItem(tvtArr[row]);
		{$loadfile}
	},
	build: function(val){
		return new Element('input',{'type':'text','styles':{'width':'100px'},'value':val,'events':{'keyup':function(){this.setEditor();documentDirty=true;}.bind(this)}});
	},
	addHeader: function(values,elem){
		var rowDiv = new Element('div',{'class':'tvtrow','styles':{'background':'#f0f0ee','padding':'5px 0','white-space':'nowrap'}});
		this.box.adopt(rowDiv);
		if (!values) values=['',''];
		this.cols=values.length;
		for (var i=0;i<this.cols;i++) rowDiv.adopt(this.build(values[i]));
		rowDiv.adopt(new Element('input',{'type':'button','value':'>>','events':{
			'click':function(){
				this.cols++;
				this.box.getElements('div.tvtrow').each(function(item){this.build('').injectAfter(item.getElements('input[type=text]').getLast());}.bind(this));
				this.setEditor();
			}.bind(this)
		}}));
		rowDiv.adopt(new Element('input',{'type':'button','value':'<<','events':{
			'click':function(){
				if (rowDiv.getElements('input[type=text]').length>2){
					this.cols--;
					this.box.getElements('div.tvtrow').each(function(item){item.getElements('input[type=text]').getLast().remove();});
					this.setEditor();
				}
			}.bind(this)
		}}));
	},
	addItem: function(values,elem){
		var rowDiv = new Element('div',{'class':'tvtrow','styles':{'white-space':'nowrap'}});
		if (elem) {rowDiv.injectAfter(elem);} else {this.box.adopt(rowDiv);}
		for (var i=0;i<this.cols;i++) rowDiv.adopt(this.build((values) ? values[i] : ''));
		rowDiv.adopt(new Element('input',{'type':'button','value':'+','events': {
			'click':function(){this.addItem(null,rowDiv);}.bind(this)
		}}));
		if (this.box.getElements('div.tvtrow').length>2) rowDiv.adopt(new Element('input',{'type':'button','value':'-','events':{
			'click':function(){rowDiv.remove();this.setEditor();}.bind(this)
		}}));
	},
	setEditor: function(){
		var tvtArr=new Array();
		this.box.getElements('div.tvtrow').each(function(item){
			var itemsArr=new Array();
			var inputs=item.getElements('input[type=text]');
			inputs.each(function(item){itemsArr.push(item.value);});
			tvtArr.push(itemsArr);
		});
		this.fid.value = Json.toString(tvtArr);
	}
});
window.addEvent('domready', function(){
	var tvIds = [$tvIds];
	for (var i=0;i<tvIds.length;i++){
		var fid = 'tv'+ tvIds[i];
		if($(fid)!=null) {var modxTvTable=new TvTable(fid);}
	}
});
</script>
<!-- /TvTable -->
OUT;
$e->output($output);
}
if ($e->name == 'OnBeforeDocFormSave'){
$tvIds=explode(',',$tvIds);
foreach ($tvIds as $tvid) {
	$fname='file_tv'.$tvid;
	if (isset($_FILES[$fname]) && is_uploaded_file($_FILES[$fname]['tmp_name']) && is_readable($_FILES[$fname]['tmp_name'])) {
		$file=fopen($_FILES[$fname]['tmp_name'],'r');
		while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {$dataArr[]=$data;}
		fclose($file);
		$tmplvars[$tvid][1]=str_replace('\\/', '/', json_encode($dataArr));
	}
}
}
//?>