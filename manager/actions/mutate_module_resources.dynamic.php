<?php
if (IN_MANAGER_MODE != 'true') die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('edit_module')) {
	$e->setError(3);
	$e->dumpError();
}

if (isset($_REQUEST['id']))
        $id = (int)$_REQUEST['id'];
else    $id = 0;

// Get table names (alphabetical)
$tbl_active_users       = $modx->getFullTableName('active_users');
$tbl_site_content       = $modx->getFullTableName('site_content');
$tbl_site_htmlsnippets  = $modx->getFullTableName('site_htmlsnippets');
$tbl_site_module_depobj = $modx->getFullTableName('site_module_depobj');
$tbl_site_modules       = $modx->getFullTableName('site_modules');
$tbl_site_plugins       = $modx->getFullTableName('site_plugins');
$tbl_site_snippets      = $modx->getFullTableName('site_snippets');
$tbl_site_templates     = $modx->getFullTableName('site_templates');
$tbl_site_tmplvars      = $modx->getFullTableName('site_tmplvars');

// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();

// check to see the  editor isn't locked
$rs = $modx->db->select('internalKey, username',$tbl_active_users,"action=108 AND id='{$id}'");
$limit = $modx->db->getRecordCount($rs);
if($limit>1) {
	while($lock = $modx->db->getRow($rs))
	{
		if($lock['internalKey']!=$modx->getLoginUserID())
		{
			$msg = sprintf($_lang['lock_msg'], $lock['username'], 'module');
			$e->setError(5, $msg);
			$e->dumpError();
		}
	}
}
// end check for lock

// make sure the id's a number
if(!is_numeric($id)) {
	echo "Passed ID is not a valid number!";
	exit;
}

// take action
switch ($_REQUEST['op']) {
	case 'add':
		$opids = explode(',',$_REQUEST['newids']);
		if (count($opids)>0){
			// 1-snips, 2-tpls, 3-tvs, 4-chunks, 5-plugins, 6-docs
			$rt = strtolower($_REQUEST["rt"]);
			if ($rt == 'chunk') $type = 10;
			if ($rt == 'doc')   $type = 20;
			if ($rt == 'plug')  $type = 30;
			if ($rt == 'snip')  $type = 40;
			if ($rt == 'tpl')   $type = 50;
			if ($rt == 'tv')    $type = 60;
			$sql = 'INSERT INTO '.$tbl_site_module_depobj.' (module, resource, type) VALUES ';
			for($i=0;$i<count($opids);$i++) {
				if ($i != 0) $sql .= ',';
				$opids[$i] = intval($opids[$i]);
				$sql.="('$id',".$opids[$i].",$type)";
			}
			$modx->db->query('DELETE FROM '.$tbl_site_module_depobj.' WHERE module=\''.$id.'\' AND resource IN ('.implode(',',$opids).') AND type=\''.$type.'\'');
			$ds = $modx->db->query($sql);
			if(!$ds){
				echo '<script type="text/javascript">'.
				     'function jsalert(){ alert(\'An error occured while trying to update the database. \''.mysql_error().');'.
				     'setTimeout(\'jsalert()\',100)'.
				     '</script>';
			}
		}
		break;
	case 'del':
		$opids = $_REQUEST['depid'];
		for($i=0;$i<count($opids);$i++) {
			$opids[$i]=intval($opids[$i]); // convert ids to numbers
		}
		// get resources that needs to be removed
		$ds = $modx->db->query("SELECT * FROM ".$tbl_site_module_depobj." WHERE id IN (".implode(",",$opids).")");
		if ($ds) {
			// loop through resources and look for plugins and snippets
			$i=0; $plids=array(); $snid=array();
			while ($row=mysql_fetch_assoc($ds)){
				if($row['type']=='30') $plids[$i]=$row['resource'];
				if($row['type']=='40') $snids[$i]=$row['resource'];
			}
			// get guid
			$ds = $modx->db->select('*', $tbl_site_modules, "id='{$id}'");
			if($ds) {
				$row = $modx->fetchRow($ds);
				$guid = $row['guid'];
			}
			// reset moduleguid for deleted resources
			if (($cp=count($plids)) || ($cs=count($snids))) {
				if ($cp) $modx->db->query('UPDATE '.$tbl_site_plugins.' SET moduleguid=\'\' WHERE id IN ('.implode(',', $plids).') AND moduleguid=\''.$guid.'\'');
				if ($cs) $modx->db->query('UPDATE '.$tbl_site_snippets.' SET moduleguid=\'\' WHERE id IN ('.implode(',', $snids).') AND moduleguid=\''.$guid.'\'');
				// reset cache
				$modx->clearCache();
			}
		}
		$sql = 'DELETE FROM '.$tbl_site_module_depobj.' WHERE id IN ('.implode(',', $opids).')';
		$modx->db->query($sql);
		break;
}

// load record
$rs = $modx->db->select('*',$tbl_site_modules,"id='{$id}'");
$limit = $modx->db->getRecordCount($rs);
if($limit>1) {
	echo "<p>Multiple modules sharing same unique id. Please contact the Site Administrator.<p>";
	exit;
}
elseif($limit<1) {
	echo "<p>Module not found for id '$id'.</p>";
	exit;
}
$content = $modx->db->getRow($rs);
$_SESSION['itemname']=$content['name'];
if($content['locked']==1 && $_SESSION['mgrRole']!=1) {
	$e->setError(3);
	$e->dumpError();
}

?>
<style type="text/css">
a.searchtoolbarbtn {float:left;width:120px;margin-top:2px;width:170px}
</style>
<script type="text/javascript">

	function removeDependencies() {
		if(confirm("<?php echo $_lang['confirm_delete_record']; ?>")==true) {
			documentDirty=false;
			document.mutate.op.value="del";
			document.mutate.submit();
		}
	};

	function addSnippet(){
		openSelector("snip","m","setResource");
	};

	function addDocument(){
		openSelector("doc","m","setResource");
	};

	function addTemplate(){
		openSelector("tpl","m","setResource");
	};

	function addTV(){
		openSelector("tv","m","setResource");
	};

	function addChunk(){
		openSelector("chunk","m","setResource");
	};

	function addPlugin(){
		openSelector("plug","m","setResource");
	};

	function setResource(rt,ids){
		if(ids.length==0) return;
		document.mutate.op.value = "add";
		document.mutate.rt.value = rt;
		document.mutate.newids.value = ids.join(",");
		document.mutate.submit();
	};

	function openSelector(resource,mode,callback,w,h){
		var win
		w = w ? w:600;
		h = h ? h:400;
		url = "index.php?a=84&sm="+mode+"&rt="+resource+"&cb="+callback
		// center on parent
		if (window.screenX) {
			var x = window.screenX + (window.outerWidth - w) / 2;
			var y = window.screenY + (window.outerHeight - h) / 2;
		} else {
			var x = (screen.availWidth - w) / 2;
			var y = (screen.availHeight - h) / 2;
		}
		self.chkBoxArray = {}; //reset checkbox array;
		win = window.open(url,"resource_selector","left="+x+",top="+y+",height="+h+",width="+w+",status=yes,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,location=no");
		win.opener = self;
	};
</script>

<form name="mutate" method="post" action="index.php">
<input type="hidden" name="a" value="113" />
<input type="hidden" name="op" value="" />
<input type="hidden" name="rt" value="" />
<input type="hidden" name="newids" value="" />
<input type="hidden" name="id" value="<?php echo $content['id'];?>" />
	<h1><?php echo $_lang['module_resource_title']; ?></h1>

<div id="actions">
	<ul class="actionButtons">
		<li><a href="index.php?a=106"><img src="<?php echo $_style["icons_cancel"]?>" /> <?php echo $_lang['cancel']; ?></a>
	</ul>
</div>

<div class="sectionHeader"><?php echo $content["name"]." - ".$_lang['module_resource_title']; ?></div>
<div class="sectionBody">
<p><img src="<?php echo $_style["icons_modules"] ?>" alt="" align="left" /><?php echo $_lang['module_resource_msg']; ?></p>
<br />
<!-- Dependencies -->
	<ul class="actionButtons">
		<li><a href="#" onclick="addSnippet();return false;"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['add_snippet']; ?></a></li>
		<li><a href="#" onclick="addPlugin();return false;"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['add_plugin']; ?></a></li>
	</ul>
		<?php
			$sql = "SELECT smd.id,COALESCE(ss.name,sp.name) as 'name'," .
					"CASE smd.type " .
					" WHEN 30 THEN 'Plugin' " .
					" WHEN 40 THEN 'Snippet' " .
					"END as 'type' " .
					"FROM ".$tbl_site_module_depobj." smd ".
					"LEFT JOIN ".$tbl_site_plugins." sp ON sp.id = smd.resource AND smd.type = '30' ".
					"LEFT JOIN ".$tbl_site_snippets." ss ON ss.id = smd.resource AND smd.type = '40' ".
					"WHERE smd.module=$id ORDER BY smd.type,name ";
			$ds = $modx->db->query($sql);
			if (!$ds){
				echo "An error occured while loading module dependencies.";
			}
			else {
				include_once $base_path."manager/includes/controls/datagrid.class.php";
				$grd = new DataGrid('',$ds,0); // set page size to 0 t show all items
				$grd->noRecordMsg = $_lang["no_records_found"];
				$grd->cssClass="grid";
				$grd->columnHeaderClass="gridHeader";
				$grd->itemClass="gridItem";
				$grd->altItemClass="gridAltItem";
				$grd->columns=$_lang["element_name"]." ,".$_lang["type"];
				$grd->colTypes = "template:<label><input type='checkbox' name='depid[]' value='[+id+]'> [+value+]</label>";
				$grd->fields="name,type";
				$grd->colWidths='200';
				echo $grd->render();
			}
		?>
	<ul class="actionButtons">
		<li><a style="margin-bottom:10px;" href="#" onclick="removeDependencies();return false;"><img src="<?php echo $_style["icons_delete_document"]?>" /> <?php echo $_lang['remove']; ?></a></li>
	</ul>
</div>
<input type="submit" name="save" style="display:none">
</form>
