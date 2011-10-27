// <?php 
/**
 * Мас.перемещение
 * 
 * массовое перемещение документов
 * 
 * @category	module
 * @version 	0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties	 
 * @internal	@guid 	
 * @internal	@shareparams 1
 * @internal	@dependencies requires files located at /assets/modules/fast_content_csv/
 * @internal	@modx_category add
 */



echo "
<style type='text/css'>
<!--
.table {
	width:100%;
	margin:0 auto;
	border-collapse:collapse;
	font-size:11px;
	font-family:Verdana;
}
.table td {
	padding:5px;
	border:1px dotted #aaa;
	}
.table th {
	padding:5px;
	background:#aaf;
	border:1px solid #eee;
	}
-->
</style>

<script type='text/javascript'>

checked=false;
function checkedAll () {
	var frm = document.getElementById('main');
	 if (checked == false)
          {
           checked = true
          }
        else
          {
          checked = false
          }
	for (var i =0; i < frm.elements.length; i++) 
	{
	 frm.elements[i].checked = checked;
	}
      }
</script>

</script>

";
class MassiveMove {

	private static $url;
	
	function __construct() {
		global $modx;
		$this->modx = &$modx;
		$this->a = $_GET['a'];
		$this->sc = $this->modx->getFullTableName('site_content');
		$this->id = $_GET['id'];
		$parent = isset($_GET['parent']) ? $_GET['parent'] : 0;
		$do = isset($_GET['do']) ? $_GET['do'] : '';
		if ($do != '') {
			$this->updateTree();
			}
		self::$url = 'index.php?a='.$this->a.'&id='.$this->id;
		
		$this->getFolders($parent);
		$this->crumbs = array();
		}
		
	function clearCache() {
		
		include_once MODX_BASE_PATH . 'manager/processors/cache_sync.class.processor.php';
		$sync = new synccache();
		$sync->setCachepath(MODX_BASE_PATH . "assets/cache/");
		$sync->setReport(false);
		$sync->emptyCache();
	}
	function alias2pagetitle($alias) {
	$res = $this->modx->db->select('pagetitle', $this->sc, 'alias="'.$alias.'"');
		if ($this->modx->db->getRecordCount($res)) {
			$pagetitle = $this->modx->db->getValue($res);
		}		
		return $pagetitle;
	}
	function alias2id($alias) {
	$res = $this->modx->db->select('id', $this->sc, 'alias="'.$alias.'"');
		if ($this->modx->db->getRecordCount($res)) {
			$id = $this->modx->db->getValue($res);
		}		
		return $id;
	}
	function getFolders($parent=0) {
		$sql = "SELECT id, pagetitle, isfolder, parent FROM ".$this->sc." WHERE parent='".$parent."' ORDER by menuindex ASC";
		$result = $this->modx->db->query($sql);
		$arrayIDs = $this->modx->db->makeArray($result);
		// build breadcrumbs if not at top site level
		if ($parent != 0) 
		{
			$parentIDs = $this->modx->getParentIds($parent,1);
			foreach ($parentIDs as $key=>$value) {
				$pathKey = $key;
				}
			$pathway = explode('/',$pathKey);
			foreach ($pathway as $path) {
				$this->crumbs[$this->alias2id($path)] = $this->alias2pagetitle($path);
				}
			
			$parentName = $this->modx->getDocument($parent,"pagetitle");
			$this->crumbs[$parent] = $parentName['pagetitle'];
			$goBack = $this->modx->getParent($parent,'','id');
			echo "<a href='".self::$url."&parent=".$goBack['id']."'>Назад</a><br />
			";
			// show breadcrumbs
			if (is_array($this->crumbs)) {		
				foreach ($this->crumbs as $id=>$pagetitle)
				{
					if (!empty($pagetitle)) { echo "<a href='".self::$url."&parent=".$id."'>".$pagetitle."</a> &raquo; "; }
				}
			}
		}
		echo "
		<form method='post' action='".self::$url."&do=move' name='changeparent' id='main'>
		<table class='table'><tr>
			<th width='5%'><input type='checkbox' name='checkall' onclick='checkedAll();' /></th>
			<th width='5%'>id</th>
			<th width='90%'>pagetitle</th>
			</tr>
			
			";
			foreach ($arrayIDs as $resource) {
				echo "<tr>
				<td valign='middle'>";
					if ($resource['isfolder'] != 1) {
					echo "<input type='checkbox' class='all' name='ids[]' value='".$resource['id']."' />";
					}
				echo "</td>
				<td valign='middle'>".$resource['id']."</td>
				<td valign='middle'>";
				if ($resource['isfolder'] == 1) {
				echo "<a href='".self::$url."&parent=".$resource['id']."'>".$resource['pagetitle']."</a>";
				} else {
					echo $resource['pagetitle'];
					}
				echo "</td>
				</tr>";
				}
			echo "</table>
			<input type='hidden' name='previousparent' value='".$parent."' />
			Куда переместить: <input type='text' name='newparent' size='10' /> <br />
			<input type='submit' value='Переместить' />
		</form>";
		}
		
	function updateTree() {
		echo "Папки <b>";
		foreach ($_POST['ids'] as $id) {
			echo $id.', ';
			$fields = array("parent" => $_POST['newparent']);
			$this->modx->db->update($fields, $this->sc, "id = '".$id."'");
			}
		echo "</b> перемещены в папку ".$_POST['newparent']."<br />";
		// check previous folder, if any documents exist. if NO - remove container flag, else do nothing
			$this->checkPreviousParentFolder($_POST['previousparent']);
		// make new resource a container
			$fields = array("isfolder" => 1);
			$this->modx->db->update($fields, $this->sc, "id = '".$_POST['newparent']."'");
		// eupdate cache
			$this->clearCache();
		// reload documents tree
			echo '<script type="text/javascript">
				top.mainMenu.reloadtree();
			</script>';
		// remove breadcrumbs
		$this->crumbs = array(); 
	}
	function checkPreviousParentFolder($id) {
		$children = $this->modx->getDocumentChildren($id);
		if (empty($children)) {
			$fields = array("isfolder" => 0);
			$this->modx->db->update($fields, $this->sc, "id='".$id."'");
			} else {
				return true;
				}
	}
}
//initializing object
$MasMove = new MassiveMove();