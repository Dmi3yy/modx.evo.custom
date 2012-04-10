<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('new_chunk')) {
	$e->setError(3);
	$e->dumpError();
}
$id=$_GET['id'];

// duplicate htmlsnippet
$tbl_site_htmlsnippets = $modx->getFullTableName('site_htmlsnippets');
$tpl = $_lang['duplicate_title_string'];
$sql = "INSERT INTO $tbl_site_htmlsnippets (name, description, snippet, category)
		SELECT REPLACE('{$tpl}','[+title+]',name) AS 'name', description, snippet, category
		FROM {$tbl_site_htmlsnippets} WHERE id={$id}";
$rs = $modx->db->query($sql);

if($rs) $newid = $modx->db->getInsertId(); // get new id
else {
	echo "A database error occured while trying to duplicate variable: <br /><br />".mysql_error();
	exit;
}

// finish duplicating - redirect to new chunk
header("Location: index.php?a=78&id={$newid}");
