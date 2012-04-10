<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('logs')) {
	$e->setError(3);
	$e->dumpError();
}
?>
<h1><?php echo $_lang["view_sysinfo"]; ?></h1>

<script type="text/javascript">
	function viewPHPInfo() {
		dontShowWorker = true; // prevent worker from being displayed
		window.location.href="index.php?a=200";
	};
</script>

<!-- server -->
<div class="sectionHeader">Server</div><div class="sectionBody" id="lyr2">
		<table border="0" cellspacing="2" cellpadding="2">
		<?php echo render_tr($_lang['modx_version'],$modx_version);?>
		<?php echo render_tr($_lang['release_date'],$modx_release_date);?>
		<?php echo render_tr('phpInfo()','<a href="#" onclick="viewPHPInfo();return false;">' . $_lang['view'] . '</a>');?>
		<?php echo render_tr($_lang['udperms_title'],($use_udperms==1 ? $_lang['enabled'] : $_lang['disabled']));?>
		<?php echo render_tr($_lang['servertime'],strftime('%H:%M:%S', time()));?>
		<?php echo render_tr($_lang['localtime'],strftime('%H:%M:%S', time()+$server_offset_time));?>
		<?php echo render_tr($_lang['serveroffset'],$server_offset_time/(60*60) . ' h');?>
		<?php echo render_tr($_lang['database_name'],str_replace('`','',$dbase));?>
		<?php echo render_tr($_lang['database_server'],$database_server);?>
		<?php echo render_tr($_lang['database_version'],$modx->db->getVersion());?>
		<?php
			$rs = $modx->db->query("show variables like 'character_set_database'");
			$charset = $modx->db->getRow($rs, 'num');
			echo render_tr($_lang['database_charset'],$charset[1]);
		?>
		<?php
			$rs = $modx->db->query("show variables like 'collation_database'");
			$collation = $modx->db->getRow($rs, 'num');
			echo render_tr($_lang['database_collation'],$collation[1]);
		?>
		<?php echo render_tr($_lang['table_prefix'],$table_prefix);?>
		<?php echo render_tr($_lang['cfg_base_path'],MODX_BASE_PATH);?>
		<?php echo render_tr($_lang['cfg_base_url'],MODX_BASE_URL);?>
		<?php echo render_tr($_lang['cfg_manager_url'],MODX_MANAGER_URL);?>
		<?php echo render_tr($_lang['cfg_manager_path'],MODX_MANAGER_PATH);?>
		<?php echo render_tr($_lang['cfg_site_url'],MODX_SITE_URL);?>
		</table>
   </div>

<!-- recent documents -->
<div class="sectionHeader"><?php echo $_lang["activity_title"]; ?></div><div class="sectionBody" id="lyr1">
		<?php echo $_lang["sysinfo_activity_message"]; ?><p>
		<style type="text/css">
			table.grid {border-collapse:collapse;width:100%;}
			table.grid td {padding:4px;border:1px solid #ccc;}
			table.grid a {display:block;}
		</style>
		<table class="grid">
			<thead>
			<tr>
				<td><b><?php echo $_lang["id"]; ?></b></td>
				<td><b><?php echo $_lang["resource_title"]; ?></b></td>
				<td><b><?php echo $_lang["sysinfo_userid"]; ?></b></td>
				<td><b><?php echo $_lang["datechanged"]; ?></b></td>
			</tr>
			</thead>
			<tbody>
		<?php
		$field = 'id, pagetitle, editedby, editedon';
		$tbl_site_content = $modx->getFullTableName('site_content');
		$rs = $modx->db->select($field,$tbl_site_content,'deleted=0','editedon DESC',20);
		$limit = $modx->db->getRecordCount($rs);
		if($limit<1)
		{
			echo "<p>{$_lang['no_edits_creates']}</p>";
		}
		else
		{
			$tbl_manager_users = $modx->getFullTableName('manager_users');
			$i = 0;
			$where = '';
			while($content = $modx->db->getRow($rs))
			{
				if($where !== "id={$content['editedby']}")
				{
					$where = "id={$content['editedby']}";
					$rs2 = $modx->db->select('username',$tbl_manager_users,$where);
					if($modx->db->getRecordCount($rs2)==0) $user = '-';
					else
					{
						$r = $modx->db->getRow($rs2);
						$user = $r['username'];
					}
				}
				$bgcolor = ($i % 2) ? '#EEEEEE' : '#FFFFFF';
				echo "<tr bgcolor='$bgcolor'><td style='text-align:right;'>".$content['id']."</td><td><a href='index.php?a=3&id=".$content['id']."'>".$content['pagetitle']."</a></td><td>".$user."</td><td>".$modx->toDateFormat($content['editedon']+$server_offset_time)."</td></tr>";
				$i++;
			}
		}
		?>
		</tbody>
         </table>
   </div>


<!-- database -->
<div class="sectionHeader"><?php echo $_lang['database_tables']; ?></div><div class="sectionBody" id="lyr4">
		<p><?php echo $_lang['table_hoverinfo']; ?></p>
		<table class="grid">
		 <thead>
		 <tr>
			<td width="160"><b><?php echo $_lang["database_table_tablename"]; ?></b></td>
			<td width="40" align="right"><b><?php echo $_lang["database_table_records"]; ?></b></td>
			<td width="120" align="right"><b><?php echo $_lang["database_table_datasize"]; ?></b></td>
			<td width="120" align="right"><b><?php echo $_lang["database_table_overhead"]; ?></b></td>
			<td width="120" align="right"><b><?php echo $_lang["database_table_effectivesize"]; ?></b></td>
			<td width="120" align="right"><b><?php echo $_lang["database_table_indexsize"]; ?></b></td>
			<td width="120" align="right"><b><?php echo $_lang["database_table_totalsize"]; ?></b></td>
		  </tr>
		  </thead>
		  <tbody>
<?php
	$rs = $modx->db->query("SHOW TABLE STATUS FROM $dbase LIKE '{$table_prefix}%'");
	$limit = $modx->db->getRecordCount($rs);
	for ($i = 0; $i < $limit; $i++) {
		$log_status = $modx->db->getRow($rs);
		$bgcolor = ($i % 2) ? '#EEEEEE' : '#FFFFFF';
?>
		  <tr bgcolor="<?php echo $bgcolor; ?>" title="<?php echo $log_status['Comment']; ?>" style="cursor:default">
			<td><b style="color:#009933"><?php echo $log_status['Name']; ?></b></td>
			<td align="right"><?php echo $log_status['Rows']; ?></td>

<?php
	// enable record deletion for certain tables
	// sottwell@sottwell.com
	// 08-2005
	if($modx->hasPermission('settings')
	  && (   $log_status['Name'] == "`{$table_prefix}event_log`"
	      || $log_status['Name'] == "`{$table_prefix}log_access`"
	      || $log_status['Name'] == "`{$table_prefix}log_hosts`"
	      || $log_status['Name'] == "`{$table_prefix}log_visitors`"
	      || $log_status['Name'] == "`{$table_prefix}manager_log`"
	     )
	  )
	  {
		echo "<td dir='ltr' align='right'>";
		echo "<a href='index.php?a=54&mode=$action&u=".$log_status['Name']."' title='".$_lang['truncate_table']."'>".$modx->nicesize($log_status['Data_length']+$log_status['Data_free'])."</a>";
		echo "</td>";
	}
	else {
		echo "<td dir='ltr' align='right'>".$modx->nicesize($log_status['Data_length']+$log_status['Data_free'])."</td>";
	}

	if($modx->hasPermission('settings')) {
		echo  "<td align='right'>".($log_status['Data_free']>0 ? "<a href='index.php?a=54&mode=$action&t=".$log_status['Name']."' title='".$_lang['optimize_table']."' ><span dir='ltr'>".$modx->nicesize($log_status['Data_free'])."</span></a>" : "-")."</td>";
	}
	else {
		echo  "<td dir='ltr' align='right'>".($log_status['Data_free']>0 ? $modx->nicesize($log_status['Data_free']) : "-")."</td>";
	}
?>
			<td dir='ltr' align="right"><?php echo $modx->nicesize($log_status['Data_length']-$log_status['Data_free']); ?></td>
			<td dir='ltr' align="right"><?php echo $modx->nicesize($log_status['Index_length']); ?></td>
			<td dir='ltr' align="right"><?php echo $modx->nicesize($log_status['Index_length']+$log_status['Data_length']+$log_status['Data_free']); ?></td>
		  </tr>
<?php
		$total = $total+$log_status['Index_length']+$log_status['Data_length'];
		$totaloverhead = $totaloverhead+$log_status['Data_free'];
	}
?>
		  <tr bgcolor="#e0e0e0">
			<td valign="top"><b><?php echo $_lang['database_table_totals']; ?></b></td>
			<td colspan="2">&nbsp;</td>
			<td dir='ltr' align="right" valign="top"><?php echo $totaloverhead>0 ? "<b style='color:#990033'>".$modx->nicesize($totaloverhead)."</b><br />(".number_format($totaloverhead)." B)" : "-"; ?></td>
			<td colspan="2">&nbsp;</td>
			<td dir='ltr' align="right" valign="top"><?php echo "<b>".$modx->nicesize($total)."</b><br />(".number_format($total)." B)"; ?></td>
		  </tr>
		  </tbody>
		</table>
<?php
	if($totaloverhead>0) { ?>
		<p><?php echo $_lang['database_overhead']; ?></p>
		<?php } ?>
</div>

<!-- online users -->
<div class="sectionHeader"><?php echo $_lang['onlineusers_title']; ?></div><div class="sectionBody" id="lyr5">

		<?php
		$html = $_lang["onlineusers_message"].'<b>'.strftime('%H:%M:%S', time()+$server_offset_time).'</b>):<br /><br />
                <table class="grid">
                  <thead>
                    <tr>
                      <td><b>'.$_lang["onlineusers_user"].'</b></td>
                      <td><b>'.$_lang["onlineusers_userid"].'</b></td>
                      <td><b>'.$_lang["onlineusers_ipaddress"].'</b></td>
                      <td><b>'.$_lang["onlineusers_lasthit"].'</b></td>
                      <td><b>'.$_lang["onlineusers_action"].'</b></td>
                      <td><b>'.$_lang["onlineusers_actionid"].'</b></td>		
                    </tr>
                  </thead>
                  <tbody>
        ';
		
		$timetocheck = (time()-(60*20));

		include_once "actionlist.inc.php";
		$tbl_active_users = $modx->getFullTableName('active_users');
		
		$rs = $modx->db->select('*',$tbl_active_users,"lasthit>{$timetocheck}",'username ASC');
		$limit = $modx->db->getRecordCount($rs);
		if($limit<1) {
			$html = "<p>".$_lang['no_active_users_found']."</p>";
		} else {
			while($activeusers = $modx->db->getRow($rs))
			{
				$currentaction = getAction($activeusers['action'], $activeusers['id']);
				$webicon = ($activeusers['internalKey']<0)? "<img align='absmiddle' src='media/style/{$manager_theme}/images/tree/globe.gif' alt='Web user'>":"";
				$html .= "<tr bgcolor='#FFFFFF'><td><b>".$activeusers['username']."</b></td><td>$webicon&nbsp;".abs($activeusers['internalKey'])."</td><td>".$activeusers['ip']."</td><td>".strftime('%H:%M:%S', $activeusers['lasthit']+$server_offset_time)."</td><td>$currentaction</td><td align='right'>".$activeusers['action']."</td></tr>";
			}
		}
		echo $html;
		?>
		</tbody>
		</table>
</div>
<?php
function render_tr($label,$content)
{
	global $modx;
	$ph['label'] = $label;
	$ph['content'] = $content;
	$tpl = <<< EOT
<tr>
<td width="150">[+label+]
<td width="20">&nbsp;</td>
<td style="font-weight:bold;">[+content+]</td>
</tr>
EOT;
	return $modx->parsePlaceholder($tpl,$ph);
}
