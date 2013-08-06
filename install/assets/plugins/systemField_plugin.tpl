//<?php
/**
 * systemField
 * 
 * The plugin allows you to add "system" data in a separate field, which will be displayed along with the page content.
 *
 * @category    plugin
 * @version     1.3
 * @author		Andhir
 * @internal    @properties &separator=Separator;string;<!-- hr --> &open_text=Open link text;string;system content &position=System content position;list;before,after;after
 * @internal    @events OnDocFormRender
 * @internal    @installset base
 * @internal    @disabled 1
 */
/*
systemField 1.3 plugin for MODx Evo
The plugin allows you to add "system" data in a separate field, which will be displayed along with the page content.
by Andhir
Сделано для студии "Симпл дрим" - http://www.simpledream.ru/
-----------------------------------------
System event: OnDocFormRender
Configurtion:
&separator=Separator;string;<!-- hr --> &open_text=Open link text;string;system content &position=System content position;list;before,after;after
*/

defined('IN_MANAGER_MODE') or die();

if(empty($separator)) $separator = '<!-- hr -->';
if(empty($open_text)) $open_text = 'system content';
if(empty($position)) $position = 'after';

$get_action = isset($_GET['a']) ? $_GET['a'] : 27;

$e = &$modx->Event;

if ($e->name == 'OnDocFormRender') {

$output = <<< OUT

<!-- systemField -->
<script type="text/javascript">

var SFloadAction = navigator.userAgent.indexOf('AppleWebKit')>-1 ? 'load' : 'domready';
var SFposition = '{$position}';

//window.addEvent(SFloadAction, function(){
  
  var SFseparator = '{$separator}';
  var SFsourceContentField = $('ta');
  var SFsaveButton = $('Button1').getFirst('a');
  var SFcontent = SFsourceContentField.value;
  var SFsepPos = SFcontent.indexOf(SFseparator);
  
  function SFonSaveContent(){
    /*
    if(typeof(tinyMCE)!='undefined'){
      var SFoutContent = tinyMCE.get('ta').getContent()+SFseparator+SFtextarea.value;
      tinyMCE.get('ta').setContent(SFoutContent,{format : 'html'});
    }else{
    */
      if(SFtextarea.value.length>0){
        if(SFposition=='after')
          SFsourceContentField.value += SFseparator+SFtextarea.value;
        else
          SFsourceContentField.value = $('SFcontent').value+SFseparator+SFsourceContentField.value;
      }
    //}
  }
  
  if(SFsepPos>-1){
    var SFsystemValue = SFposition=='after' ? SFcontent.substr(SFsepPos+SFseparator.length) : SFcontent.substr(0,SFsepPos);
    SFsourceContentField.value = SFposition=='after' ? SFcontent.substr(0,SFsepPos) : SFcontent.substr(SFsepPos+SFseparator.length);
  }else{
    var SFsystemValue = '';
  }
  
  var SFlinkOpen = new Element('a',{
    href: '#open',
    events: {
      click: function(){
        if(SFtextarea.style.display=='none')
          SFtextarea.style.display='inline';
        else
          SFtextarea.style.display='none';
        return false;
      }
    },
    styles: {display:'block', margin: '10px 0'}
  });
  var br = new Element('br');
  var SFtextarea = new Element('textarea',{
    cols: 300,
    rows: 12,
    id: 'SFcontent',
    styles: {width: '400px', height: '200px', display: 'none'},
    value: SFsystemValue
  });
  
  $('ta').getParent().adopt(SFlinkOpen.appendText('{$open_text}'),SFtextarea);
  
  if($('which_editor')!=null){
    $('which_editor')
    .removeEvents('change')
    .removeProperty('onchange')
    .addEvent('change',function(){
        documentDirty=false;
      	document.mutate.a.value = {$get_action};
        document.mutate.newtemplate.value = $('template').value;
      	document.mutate.which_editor.value = this.value;
        document.mutate.mode.value = 4; 
        SFsubmit();
    });
  }
  
  $('template')
  .removeEvents('change')
  .removeProperty('onchange')
  .addEvent('change',function(){
    documentDirty=false;
		document.mutate.a.value = {$get_action};
		document.mutate.newtemplate.value = this.value;
		SFsubmit();
  });
  
//});
window.addEvent('domready', function(){
  if(navigator.userAgent.indexOf('MSIE')>-1 && navigator.userAgent.indexOf('MSIE 9')==-1) {
    $('mutate').addEvent('submit',SFonSaveContent);
    SFsubmit = function(){document.mutate.save.click();}
  }else{
    SFsubmit = function(){
      $('mutate').addEvent('submit',SFonSaveContent);
      document.mutate.save.click();
    }
  }
  
  SFsaveButton
  .removeEvents('click')
  .removeProperty('onclick')
  .addEvent('click',function(){
    documentDirty = false;
    SFsubmit();
    return false;
  });
});
</script>
<!-- /systemField -->

OUT;

$e->output($output);

}
