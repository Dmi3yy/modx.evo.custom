<?php
if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
/*********************/
$sd = isset($_REQUEST['dir']) ? '&dir=' . $_REQUEST['dir'] : '&dir=DESC';
$sb = isset($_REQUEST['sort']) ? '&sort=' . $_REQUEST['sort'] : '&sort=createdon';
$pg = isset($_REQUEST['page']) ? '&page=' . (int) $_REQUEST['page'] : '';
$add_path = $sd . $sb . $pg;
/**********************/

$url = '';

if($_REQUEST['r'] == 10) {

} else if($_REQUEST['dv'] == 1 && $_REQUEST['id'] != '') {
	$url = "index.php?a=3&id=" . $_REQUEST['id'] . $add_path;
} else {
	$url = "index.php?a=2";
}
?>

<h1><?php echo $_lang['cleaningup']; ?></h1>

<div class="section">
	<div class="sectionBody">
		<p><?php echo $_lang['actioncomplete']; ?></p>
		<script type="text/javascript">
			x = window.setTimeout(function() {
				top.mainMenu.startrefresh(<? echo($_REQUEST['r'] ? $_REQUEST['r'] : 0) ?>);
				document.location.href = '<?php echo $url ?>';
			}, 200);
		</script>
	</div>
</div>
