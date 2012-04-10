<?php
if(IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('change_password')) {
	$e->setError(3);
	$e->dumpError();
}
?>

<h1><?php echo $_lang['change_password']?></h1>
<div id="actions">
	<ul class="actionButtons">
		<li><a href="#" onclick="documentDirty=false; document.userform.save.click();"><img src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']?></a></li>
	  <li id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=2';"><img src="<?php echo $_style["icons_cancel"] ?>" /> <?php echo $_lang['cancel']?></a></li>
	</ul>
</div>
<div class="sectionBody">
	<form action="index.php" method="post" name="userform">
	<input type="hidden" name="a" value="34>" />
	<p><?php echo $_lang['change_password_message']?></p>
	<table border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td><?php echo $_lang["username"]?>:</td>
		<td><b><?php echo $modx->getLoginUserName();?></b></td>
	</tr>
	<tr>
		<td><?php echo $_lang['change_password_new']?>:</td>
		<td><input onchange="documentDirty=true;" type="password" name="pass1" class="inputBox" style="width:150px" value=""></td>
	</tr>
	<tr>
		<td><?php echo $_lang['change_password_confirm']?>:</td>
		<td><input onchange="documentDirty=true;" type="password" name="pass2" class="inputBox" style="width:150px" value=""></td>
	</tr>
	</table>

	<input type="submit" name="save" style="display:none">
</form>
</div>
