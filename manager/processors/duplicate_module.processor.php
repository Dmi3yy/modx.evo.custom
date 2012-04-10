<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('new_module')) {	
	$e->setError(3);
	$e->dumpError();	
}
$id=$_GET['id'];

// duplicate module
$tbl_site_modules = $modx->getFullTableName('site_modules');
$tpl = $_lang['duplicate_title_string'];
$sql = "INSERT INTO {$tbl_site_modules} (name, description, disabled, category, wrap, icon, enable_resource, resourcefile, createdon, editedon, guid, enable_sharedparams, properties, modulecode) 
		SELECT REPLACE('{$tpl}','[+title+]',name) AS 'name', description, disabled, category, wrap, icon, enable_resource, resourcefile, createdon, editedon, '".createGUID()."' as 'guid', enable_sharedparams, properties, modulecode 
		FROM {$tbl_site_modules} WHERE id={$id}";
$rs = $modx->db->query($sql);

if($rs) $newid = $modx->db->getInsertId(); // get new id
else {
	echo "A database error occured while trying to duplicate module: <br /><br />".mysql_error();
	exit;
}

// duplicate module dependencies
$tbl_site_module_depobj = $modx->getFullTableName('site_module_depobj');
$sql = "INSERT INTO {$tbl_site_module_depobj} (module, resource, type)
		SELECT  '$newid', resource, type  
		FROM {$tbl_site_module_depobj} WHERE module={$id}";
$rs = $modx->db->query($sql);

if(!$rs){
	echo "A database error occured while trying to duplicate module dependencies: <br /><br />".mysql_error();
	exit;
}

// duplicate module user group access
$tbl_site_module_access = $modx->getFullTableName('site_module_access');
$sql = "INSERT INTO {$tbl_site_module_access} (module, usergroup)
		SELECT  '$newid', usergroup  
		FROM {$tbl_site_module_access} WHERE module={$id}";
$rs = $modx->db->query($sql);

if(!$rs){
	echo "A database error occured while trying to duplicate module user group access: <br /><br />".mysql_error();
	exit;
}

// finish duplicating - redirect to new module
header("Location: index.php?r=2&a=108&id={$newid}");



// create globally unique identifiers (guid)
function createGUID(){
	srand((double)microtime()*1000000);
	$r = rand() ;
	$u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
	$m = md5 ($u);
	return $m;
}
