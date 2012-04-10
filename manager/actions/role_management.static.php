<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('edit_user')) {
	$e->setError(3);
	$e->dumpError();
}
?>
<br />
<!-- User Roles -->

<h1><?php echo $_lang['role_management_title']; ?></h1>

<div class="sectionBody">
<p><?php echo $_lang['role_management_msg']; ?></p>

<ul>
	<li><a href="index.php?a=38"><?php echo $_lang['new_role']; ?></a></li>
</ul>
<br />
<ul>
<?php

$tbl_user_roles = $modx->getFullTableName('user_roles');
$rs = $modx->db->select('name, id, description',$tbl_user_roles,'','name');
$total = $modx->db->getRecordCount($rs);
if($total<1){
	echo "The request returned no roles!</div>";
	exit;
	include_once "footer.inc.php";
}
while($row = $modx->db->getRow($rs))
{
	if($row['id']==1)
	{
?>
	<li><span style="width: 200px"><i><?php echo "({$row['id']}) {$row['name']}"; ?></i></span> - <i><?php echo $_lang['administrator_role_message']; ?></i></li>
<?php
	}
	else
	{
?>
	<li><span style="width: 200px"><a href="index.php?id=<?php echo $row['id']; ?>&a=35"><?php echo "({$row['id']}) {$row['name']}"; ?></a></span> - <?php echo $row['description']; ?></li>
<?php
	}
}

?>
</ul>
</div>
