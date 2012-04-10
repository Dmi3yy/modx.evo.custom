<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('new_plugin')) {	
	$e->setError(3);
	$e->dumpError();	
}
$id=$_GET['id'];

// duplicate Plugin
$tbl_site_plugins = $modx->getFullTableName('site_plugins');
$tpl = $_lang['duplicate_title_string'];
$sql = "INSERT INTO {$tbl_site_plugins} (name, description, disabled, moduleguid, plugincode, properties, category) 
		SELECT REPLACE('{$tpl}','[+title+]',name) AS 'name', description, disabled, moduleguid, plugincode, properties, category 
		FROM {$tbl_site_plugins} WHERE id={$id}";
$rs = $modx->db->query($sql);

if($rs) $newid = $modx->db->getInsertId(); // get new id
else {
	echo "A database error occured while trying to duplicate plugin: <br /><br />".mysql_error();
	exit;
}

// duplicate Plugin Event Listeners
$tbl_site_plugin_events = $modx->getFullTableName('site_plugin_events');
$sql = "INSERT INTO {$tbl_site_plugin_events} (pluginid,evtid,priority)
		SELECT $newid, evtid, priority
		FROM {$tbl_site_plugin_events} WHERE pluginid={$id}";
$rs = $modx->db->query($sql);

if (!$rs) {
	echo "A database error occured while trying to duplicate plugin events: <br /><br />".mysql_error();
	exit;
}

// finish duplicating - redirect to new plugin
header("Location: index.php?a=102&id={$newid}");
