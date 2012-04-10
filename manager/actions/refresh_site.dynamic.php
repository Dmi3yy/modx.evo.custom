<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

$now = time();
$tbl_site_content = $modx->getFullTableName('site_content');

$where = "pub_date < {$now} AND pub_date!=0 AND published=0 AND ({$now} < unpub_date or unpub_date=0)";
$rs = $modx->db->update(array('published'=>'1'),$tbl_site_content,$where);
$num_rows_pub = $modx->db->getAffectedRows();

$where = "unpub_date < {$now} AND unpub_date!=0 AND published=1";
$rs = $modx->db->update(array('published'=>'0'),$tbl_site_content,$where);
$num_rows_unpub = $modx->db->getAffectedRows();

?>

<script type="text/javascript">
doRefresh(1);
</script>
<h1><?php echo $_lang['refresh_title']; ?></h1>
<div class="sectionBody">
<?php

if(0<$num_rows_pub)   printf('<p>'.$_lang["refresh_published"].'</p>', $num_rows_pub);
if(0<$num_rows_unpub) printf('<p>'.$_lang["refresh_unpublished"].'</p>', $num_rows_unpub);

$params['showReport'] = true;
$modx->clearCache($params);

// invoke OnSiteRefresh event
$modx->invokeEvent("OnSiteRefresh");

?>
</div>
