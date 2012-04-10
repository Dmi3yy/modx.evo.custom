<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('delete_role')) {
	$e->setError(3);
	$e->dumpError();
}
$id=$_GET['id'];

if($id==1){
	echo "The role you are trying to delete is the admin role. This role cannot be deleted!";
	exit;
}

$tbl_user_attributes = $modx->getFullTableName('user_attributes');
$tb_user_roles = $modx->getFullTableName('user_roles');

$rs = $modx->db->select('count(id)',$tbl_user_attributes,"role={$id}");
if(!$rs) {
	echo "Something went wrong while trying to find users with this role...";
	exit;
} 
if($modx->db->getValue($rs)>0){
	echo "There are users with this role. It can't be deleted.";
	exit;
}

// delete the attributes
$rs = $modx->db->delete($tb_user_roles,"id={$id}");
if(!$rs) {
	echo "Something went wrong while trying to delete the role...";
	exit;
} else {
	header("Location: index.php?a=86");
}
