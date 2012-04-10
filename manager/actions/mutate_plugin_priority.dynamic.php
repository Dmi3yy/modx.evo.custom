<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_plugin')) {
	$e->setError(3);
	$e->dumpError();
}

$tbl_site_plugin_events = $modx->getFullTableName('site_plugin_events');
$tbl_system_eventnames  = $modx->getFullTableName('system_eventnames');
$tbl_site_plugins       = $modx->getFullTableName('site_plugins');

$updateMsg = '';

if(isset($_POST['listSubmitted']))
{
	$updateMsg .= '<span class="warning" id="updated">Updated!<br /><br /> </span>';
	
	foreach ($_POST as $listName=>$listValue)
	{
		if ($listName == 'listSubmitted') continue;
		$orderArray = explode(',', $listValue);
		$listName = ltrim($listName, 'list_');
		if (count($orderArray) > 0)
		{
			foreach($orderArray as $key => $item)
			{
				if ($item == '') continue;
				$pluginId = ltrim($item, 'item_');
				$field['priority'] = $key;
				$modx->db->update($field,$tbl_site_plugin_events,"pluginid={$pluginId} and evtid={$listName}");
			}
		}
	}
	// empty cache
	$modx->clearCache(); // first empty the cache
}

$field = "sysevt.name as 'evtname', sysevt.id as 'evtid', pe.pluginid, plugs.name, pe.priority";
$from  = "$tbl_system_eventnames sysevt";
$from .= " INNER JOIN {$tbl_site_plugin_events} pe ON pe.evtid = sysevt.id";
$from .= " INNER JOIN {$tbl_site_plugins} plugs ON plugs.id = pe.pluginid";
$rs = $modx->db->select($field,$from,'plugs.disabled=0','sysevt.name,pe.priority');
$total = $modx->db->getRecordCount($rs);

$insideUl = 0;
$preEvt = '';
$evtLists = '';
$sortables = array();
if($total>1)
{
	for ($i=0;$i<$total;$i++)
	{
		$plugins = mysql_fetch_assoc($rs);
		if ($preEvt !== $plugins['evtid'])
		{
			$sortables[] = $plugins['evtid'];
			$evtLists .= $insideUl? '</ul><br />': '';
			$evtLists .= '<strong>'.$plugins['evtname'].'</strong><br /><ul id="'.$plugins['evtid'].'" class="sortableList">';
			$insideUl = 1;
		}
		$evtLists .= '<li id="item_'.$plugins['pluginid'].'">'.$plugins['name'].'</li>';
		$preEvt = $plugins['evtid'];
	}
}

$evtLists .= '</ul>';

$header = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
	<title>MODX</title>
	<meta http-equiv="Content-Type" content="text/html; charset=' . $modx_manager_charset . '" />
	<link rel="stylesheet" type="text/css" href="media/style/' . $manager_theme . '/style.css" />
	<script type="text/javascript" src="media/script/mootools/mootools.js"></script>

	<style type="text/css">
        .topdiv {border: 0;}
		.subdiv {border: 0;}
		li {list-style:none;}
		.tplbutton {text-align: right;}
		ul.sortableList
		{
			padding-left: 20px;
			margin: 0px;
			width: 300px;
			font-family: Arial, sans-serif;
		}

		ul.sortableList li
		{
			font-weight: bold;
			cursor: move;
            color: #444444;
            padding: 3px 5px;
			margin: 4px 0px;
            border: 1px solid #CCCCCC;
			background-image: url("' . $style_path . 'misc/fade.gif");
			background-repeat: repeat-x;
		}
        #sortableListForm {display:none;}
	</style>
    <script type="text/javascript">
        function save() {
        	setTimeout("document.sortableListForm.submit()",1000);
    	}
    		
    	window.addEvent(\'domready\', function() {';
foreach ($sortables as $list) {
	
	$header .= 'new Sortables($(\''.$list.'\'), {
	               initialize: function() {
                        $$(\'#'.$list.' li\').each(function(el, i)
                        {
                            el.setStyle(\'padding\', \'3px 5px\');
                            el.setStyle(\'font-weight\', \'bold\');
                            el.setStyle(\'width\', \'300px\');
                            el.setStyle(\'background-color\', \'#ccc\');
                            el.setStyle(\'cursor\', \'move\');
                        });
                    }
                    ,onComplete: function() {
           	var id = null;
           	var list = this.serialize(function(el) {
            id = el.getParent().id;
           	return el.id;
           });
           $(\'list_\' + id).value = list;
                    }
                });' ."\n";
}
	$header .= '});
</script>
</head>
<body ondragstart="return false;">

<h1>'.$_lang['plugin_priority_title'].'</h1>

<div id="actions">
   <ul class="actionButtons">
       	<li><a href="#" onclick="save();"><img src="'.$_style["icons_save"].'" /> '.$_lang['save'].'</a></li>
		<li><a href="#" onclick="document.location.href=\'index.php?a=76\';"><img src="'.$_style["icons_cancel"].'" /> '.$_lang['cancel'].'</a></li>
	</ul>
</div>

<div class="sectionHeader">'.$_lang['plugin_priority'].'</div>
<div class="sectionBody">
<p>'.$_lang['plugin_priority_instructions'].'</p>
';

echo $header;

echo $updateMsg . '<span class="warning" style="display:none;" id="updating">Updating...<br /><br /> </span>';

echo $evtLists;

echo '<form action="" method="post" name="sortableListForm" style="display: none;">
            <input type="hidden" name="listSubmitted" value="true" />';
            
foreach ($sortables as $list)
{
	echo '<input type="text" id="list_'.$list.'" name="list_'.$list.'" value="" />';
}
            
echo '	</form>
	</div>
';
