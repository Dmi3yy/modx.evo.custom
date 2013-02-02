 //<?php
 /*
 * TreeTabs  
 *
 * Written By Bumkaka - 15 Sep 2012
 *
 * @category 	plugin
 * @version 	1.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @properties &setting_tabs=ID ресурсов для закладок;text; &setting_tabs_spec_id=ID спец закладки;text; &setting_tabs_spec_chunk=Чанк настройки;text;TreeTabs &width=ширина блока закладок;text;400 &name_main=Титл первой закладки;text;Главная &show_parent=показывать родителя<br/> (1 - да/0 - нет);list;1,0;0  * @internal	@events OnManagerPageInit
 * @internal    @installset base
 * @internal    @disabled 1
 *
 * Deafault chunk treetabs:
	Editable chunk</name>
	edit_chunk||41||site header||1,3</element>
	</section>
	Run</name>
	run_chunk||6||modul №1</element>
	run_module||5||shop||1,2,3</element>
 */
GLOBAL $_lang,$table_prefix,$dbase,$manager_theme;
$site_name=$modx->config['site_name'];
$manager_theme=$modx->config['manager_theme'];
if (!empty($setting_tabs_spec_chunk)) {$chunk = $modx->getChunk($setting_tabs_spec_chunk);}
if (isset($_REQUEST['tree'])) {$_SESSION['mrgShowTree'] = $_REQUEST['tree']=='undefined'?0:$_REQUEST['tree'];}
$mrgShowTree=empty($_SESSION['mrgShowTree'])?0:$_SESSION['mrgShowTree'];

function clear_wites($str){
	$str = str_replace("\r\n",'',$str);
	$str = str_replace("\n",'',$str);
	$str=nl2br(str_replace(' ','',$str));
  $str=str_replace('
','',$str);
  $str=str_replace('	','',$str);
	return $str;
}

function get_permission($act,$name,$chunk){
    GLOBAL $modx;
    if ($_SESSION['mgrRole']==1) {return true;}
    $section=explode('</section>',$chunk);
      foreach($section as $key=>$value){
    	$arr=explode('</name>',$value);
    	$elements=explode('</element>',$arr[1]);
    	foreach($elements as $key2=>$value2){
            $element=explode('||',$value2);
            $act1=clear_wites($element[0]);
            $name1=clear_wites($element[1]);
            if ($act1==$act&&$name1==$name){
                if (empty($element[3])) return true;
                $roles=explode(',',$element[3]);
                if (in_array($_SESSION['mgrRole'],$roles )) {return true;}
                
            }
        }
	  }
     return false; 
}

                    
if ($action==78 && $_GET['mode']=='treetabs'){
    if (get_permission('edit_chunk',$_GET['id'],$chunk)){
        include_once "header.inc.php";
        $asset_path = $modx->config['base_path'] . 'assets/plugins/treetabs/actions/chunk.php';
        include_once $asset_path;
        include_once "footer.inc.php";
	} 
    exit;
}    

if ($action==10001){
    if (get_permission('run_chunk',$_GET['id'],$chunk)){
		include_once(MODX_BASE_PATH.'assets/snippets/ditto/classes/phx.parser.class.inc.php');
		$res = $modx->db->select("name", $modx->getFullTableName('site_htmlsnippets'),"id='".$_GET['id']."'");
		if($modx->db->getRecordCount($res)) {
		$name = $modx->db->getValue($res);
		$phx = new PHxParser();
		$tpl=$modx->getChunk($name);
		$output=$phx->Parse($tpl);
			}
		echo $output;
    }
	exit;
}


if($action==1 && $_GET['f']=='nodes' && $_GET['parent']==$setting_tabs_spec_id){
 
    $section=explode('</section>',$chunk);
    foreach($section as $key=>$value){
        $arr=explode('</name>',$value);
        
        $elements=explode('</element>',$arr[1]);
        $out='';
        foreach($elements as $key2=>$value2){
           $strings=clear_wites($value2);
            if (!empty($strings)){
        	 $element=explode('||',$value2);
            $roles=explode(',',$element[3]);
                
            if (empty($element[3])||in_array($_SESSION['mgrRole'],$roles )||$_SESSION['mgrRole']==1) {
            

               $do_action=clear_wites($element[0]);
 
                switch($do_action){
                    case 'edit_chunk':$act="parent.main.location.href='index.php?a=78&id={$element[1]}&mode=treetabs'";break;
                    case 'run_chunk':$act="parent.main.location.href='index.php?a=10001&id={$element[1]}&mode=treetabs'";break;
                    case 'run_module':$act="parent.main.location.href='index.php?a=112&id={$element[1]}'";break;         
                }
                    
$out .= <<< EOF
<li style="list-style: disc outside url(media/style/{$manager_theme}/images/misc/li.gif);">
<span title="{$element[1]}" class="treeNode" onmouseout="setHoverClass(this, 0);" onmouseover="setHoverClass(this, 1);" 
onclick="{$act};setSelected(this);">
<span class="publishedNode">{$element[2]}</span>
</span></li>
EOF;
                    
            }
            }
        }

      $output.=empty($out)?'':'<div class="sectionHeader">'.$arr[0].'</div><div class="sectionBody"><ul>'.$out.'</ul></div>';
    }
    echo '<br/>'.$output;
    exit;
}





if($action!=1 || $_GET['f']!=='tree') return; 

ob_start();
include("media/style/".$manager_theme."/style.php");
include_once "frames/tree.php";
$content = ob_get_contents();  
ob_end_clean(); 

$script .= <<< EOF
  
function str_replace(search, replace, subject) {return subject.split(search).join(replace);}

    function create_new(){
     $$('h2.selected').each(function(el){idname=str_replace('tab','',el.id);});  
        if (idname=='0') {top.main.document.location.href='index.php?a=4';  }
        else {
        if (idname=='{$setting_tabs_spec_id}') {top.main.document.location.href='index.php?a=4&pid='+idname;  }
        else  {top.main.document.location.href='index.php?a=4&pid='+idname;  }
        }
    }
function rem(data){
  $$('h2.selected').each(function(el){idname=str_replace('tab','',el.id);});
  if (idname!=0){
  data=str_replace('>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;','>',data);
  }
  rpcLoadData(data);
}
EOF;

switch($show_parent){
    case '0':
$script .= <<< EOF


function changeTree(tree,el) { 
  idname=0;
	$$('.tab').removeClass('selected');
	$$(el).addClass('selected');
	$$('h2.selected').each(function(el){idname=str_replace('tab','',el.id);});
    indent=(idname=='0')?1:2;
    restoreTree(tree);
}

function hide_node(){
	idname=0;
	$$('h2.selected').each(function(el){idname='node'+str_replace('tab','',el.id);});
	if (idname=='node0'){
		$$('h2.tab').each(function(el){
			idn='node'+str_replace('tab','',el.id);
			if (idn!='node0') { 
        
         $$('#treeRoot #'+idn).setStyle('display','none');
      }
		});
	} 
}
EOF;

$s_restore_tree = "
    function restoreTree(tree) {
idname=0;
      $$('h2.selected').each(function(el){idname=str_replace('tab','',el.id);});
idname=(idname=='') ?0:idname;
  	  indent=(idname=='0') ?1:2;
      rpcNode = $('treeRoot');
      new Ajax('index.php?a=1&f=nodes&indent='+indent+'&parent='+idname+'&expandAll=2&tree='+tree, {method: 'get',onComplete:rem}).request();
    }

     function old_restoreTree() {
    
    ";
    
    break;
    
    case '1':
    
$script .= <<< EOF

function changeTree(tree,el) { 
	$$('.tab').removeClass('selected');
	$$(el).addClass('selected');
    restoreTree(tree);
}

function hide_node(){
	idname=0;
	$$('h2.selected').each(function(el){idname=str_replace('tab','',el.id);});
	if (idname=='0'){
		$$('h2.tab').each(function(el){
			idn='node'+str_replace('tab','',el.id);
			if (idn!='node0') {  $$('#treeRoot #'+idn).setStyle('display','none');}
		});

	} else {
		$('treeRoot').getChildren().each(function(el){el.setStyle('display','none');});
		node_name='node'+idname;
     $$('#treeRoot #'+node_name).setStyle('display','block');
		//$(node_name).setStyle('display','block');
	}

}
EOF;
    $s_restore_tree = "
function restoreTree(tree) {
        rpcNode = $('treeRoot');
        new Ajax('index.php?a=1&f=nodes&indent=1&parent=0&expandAll=2&tree='+tree, {method: 'get',onComplete:rpcLoadData}).request();
    }
function old_restoreTree() {";
    
    break;
    
}
$style = '<style>
#treeHolder{border-top: 1px solid #E0E0E0;top:-2px;position:relative;}
.dynamic-tab-pane-control .tab-row .tab.selected{
border-color:#E0E0E0 #C0C0C0 #f4f4f4 #E0E0E0;
background:none repeat scroll 0 0 #F4f4f4}
.dynamic-tab-pane-control .tab-row .tab.selected span {
  background-color: #F4f4f4;
}
.treeframebody{background:none;}
</style>';

$tab_ID=explode(',',$setting_tabs);

    foreach($tab_ID as $key=>$value){
     
      $doc = $modx->getDocument($value, '*', 1);
      if (empty($doc)) $doc = $modx->getDocument($value, '*', 0);
      $tabs_c.='<h2  p="0" style="padding:3px 6px" id="tab'.$doc['id'].'" class="tab '.($mrgShowTree==$doc['id']?'selected':'').'"   onclick="if (ca==\'move\' && '.(int)$show_parent.'==0) {try {parent.main.setMoveValue('.$doc['id'].', \''.$doc['pagetitle'].'\');} catch (event){}} else {changeTree('.$doc['id'].',this);}"><span >'.$doc['pagetitle'].'</span><span id="node'.$doc['id'].'" p="0" style="display:none2"></span></h2>';
    }
$tabs_f='<h2 onclick="if (ca==\'move\'  && '.(int)$show_parent.'==0) {try {parent.main.setMoveValue(0, \''.$name_main.'\');} catch (event){}} else {changeTree(0,this);}" style="padding:3px 6px" id="tab0" class="tab '.($_SESSION['mrgShowTree']=='0'?'selected':'').'" ><span>'.$name_main.'</span></h2>';


$tabs_s= $style.'<div class="dynamic-tab-pane-control tab-pane"><div class="tab-row" style="width:'.$width.'px">';
$tabs=$tabs_s.$tabs_f.$tabs_c.'</div></div><br/>';

$content=str_replace("top.main.document.location.href='index.php?a=4'","create_new();",$content);
$content=str_replace('<div id="treeHolder">',$tabs.'<div id="treeHolder">',$content);
$content=str_replace("window.addEvent('load', function(){",$script."window.addEvent('load', function(){",$content);
$content=str_replace("function restoreTree() {",$s_restore_tree,$content);
$content=str_replace("rpcNode.innerHTML = typeof response=='object' ? response.responseText : response ;","rpcNode.innerHTML = typeof response=='object' ? response.responseText : response ;hide_node();",$content);
 
echo $content;
exit;