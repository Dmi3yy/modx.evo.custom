<?php
require "../../../manager/includes/protect.inc.php"; 
include "../../../manager/includes/config.inc.php";
include "../../../manager/includes/document.parser.class.inc.php";
include "out.inc.php";

$modx = new DocumentParser();

foreach ($_POST as $k=>$v) 
	${$k} = htmlspecialchars($v);
	
$mod_table = $modx->getFullTableName("site_easyadvt"); 

function GetTimeStamp($date) {
    if ($date != "") {
		$a = explode('+',$date);
		$b = explode('.', $a[0]);
		$c[] = substr($a[1],0,2);	
		$c[] = substr($a[1],-2);	
		$dat = array_merge($b, $c);
        $output = mktime($dat[3], $dat[4], 0, $dat[1], $dat[0], $dat[2]);
    } else $output = "";
    return $output;
}

if (isset($action)) {
	switch ($action) {
		case 'del':
			if ((int)$id > 0) {
				$sql = "DELETE FROM ".$mod_table." WHERE id = ".(int)$id;
				$out = ($modx->db->query($sql)) ? 1 : -1; 
			} else 
				$out = -1;	
		break;
		
		case 'add':
			$id = (int)$id;
			if ($id > 0) {//редактирование записи
			
				$data = $modx->db->getRow($modx->db->query("SELECT * FROM ".$mod_table." WHERE id = ".$id));
				
				foreach ($data as $k=>$v) 
					${$k} = htmlspecialchars_decode($v);
	
				$id = $data['id'];
				$pos = $data['pos'];
				$template = $data['template'];
				$ex_template = $data['ex_template'];
				$description = $data['description'];
				$area = $data['area'];
				$link = $data['link'];
				$published = $data['published'];
				$pub_date = ($data['pub_date'] != 0) ? date("d.m.Y H:i", $data['pub_date']) : '';
				$unpub_date = ($data['unpub_date'] != 0) ? date("d.m.Y H:i", $data['unpub_date']) : '';
				$count_view = $data['count_view'];
				$total_view = $data['total_view'];
				$jump_count = $data['jump_count'];
				$total_jump = $data['total_jump'];
				$content = $data['content'];
				$checkedcount = ($counted == 1) ? ' checked="checked"' : '';
				$checked = ($published == 1) ? ' checked="checked"' : '';
				$jump_counted = ($jump_counted == 1) ? ' checked="checked"' : '';

			} else {//если запись новая

				$id = '';
				$pos = 0;
				$template = '';
				$ex_template = '';
				$description = '';
				$area = '';
				$link = '';
				$published = 0;
				$pub_date= date("d.m.Y H:i");
				$unpub_date = "";
				$count_view = 0;
				$total_view = 0;
				$jump_count = 0;
				$total_jump = 0;
				$content = "";
				$checkedcount = ' checked="checked"';
				$checked = ' checked="checked"';
				$jump_counted = ' checked="checked"';
			}
			
			$out = out(array(
					'id'			=> $id,
					'pos'			=> $pos,
					'template'		=> $template,
					'ex_template'	=> $ex_template,
					'description'	=> $description,
					'area'			=> $area,
					'link'			=> $link,
					'published'		=> $published,
					'pub_date'		=> $pub_date,
					'unpub_date'	=> $unpub_date,
					'count_view'	=> $count_view,
					'total_view'	=> $total_view,
					'jump_count'	=> $jump_count,
					'content'		=> $content,
					'checkedcount'	=> $checkedcount,
					'checked'		=> $checked,
					'jump_counted'	=> $jump_counted,
					'total_jump'	=> $total_jump
				));
			
			$js = '<script type="text/javascript">
			jq("input.DatePicker").datetimepicker({dateFormat: "dd.mm.yy",timeFormat: "hh:mm"});
			
			jq("#ef-link").button();
			jq("#mhelp").button({
				icons: {
					primary: "ui-icon-help"
				},
				text: false
			});	
			
			jq("#ef-link").on("click", function() {
				jq("#ef-ef").elfinder({
					lang: "ru",
					url : "'.$modUrl.'elfinder/php/connector.php", 
					getFileCallback:function(file){
						jq("[name=\"content\"]").val(file.substring(3));
						jq("#ef-ef").dialog("close");
					}
				}).dialog({
					width: "auto",
					modal: true,
					resizable: false,
					title: "Выберите файл"
				});
			});	
			
			jq("#mhelp").on("click", function() {
				jq("#mhelp-cont").dialog({
					width: "auto",
					modal: true,
					resizable: false,
					title: "Помощь"
				});
			});
			
			</script>';
			
			$out = $js.'<form id="add_row">'.$out.'</form>';

		break;
		
		case 'save':
			
			$pos = (int)$pos;		
			$published = (isset($published)) ? 1 : 0;
			$counted = (isset($counted)) ? 1 : 0;
			$jump_counted = (isset($jump_counted)) ? 1 : 0;
			$pub_date = ($pub_date != "") ? GetTimeStamp($pub_date) : 0;	
			$unpub_date = ($unpub_date != "") ? GetTimeStamp($unpub_date) : 0;
			$jump_count = (int)$jump_count;
			$count_view = (int)$count_view;
			$total_view = (int)$total_view;
			$total_jump = (int)$total_jump;
			$id = ($id > 0) ? $id : 'NULL';
			
			$sql = "INSERT INTO $mod_table 
					VALUES (".$id.", ".$pos.", '".$template."', '".$ex_template."', '".$area."', '".$description."', 
							'".$link."', ".$published.", ".$pub_date.", ".$unpub_date.", ".$counted.", ".$count_view.", 
							".$total_view.", ".$jump_counted.", ".$jump_count.", ".$total_jump.", '".$content."') 
					ON DUPLICATE KEY UPDATE 
							pos=".$pos.", template='".$template."', ex_template='".$ex_template."', area='".$area."', 
							description='".$description."', link='".$link."', published=".$published.", pub_date=".$pub_date.", 
							unpub_date=".$unpub_date.", counted=".$counted.", count_view=".$count_view.", 
							total_view=".$total_view.", jump_counted=".$jump_counted.", jump_count=".$jump_count.", 
							total_jump=".$total_jump.", content='".$content."'";
			
			$out = ($modx->db->query($sql)) ? 1 : -1; 
			
		break;
		
	}
	
	echo $out;
	die();
	
}

$sortBy = isset($sortname) ? $sortname : 'id';
$sortDir = isset($sortorder) ? $sortorder : 'ASC';
$sort = $sortBy." ".$sortDir;
$where = (isset($qtype) && isset($query) && $query!='') ? "WHERE ".$qtype." LIKE '%".$query."%'" : "";
$rp = isset($rp) ? (int)$rp : 0;

$sql = "SELECT *
		FROM ".$mod_table."
		".$where." 
		ORDER BY ".$sort ."
		LIMIT ".($page-1)*$rp.", ".$rp;
$res = $modx->db->query($sql);

$arr = array();

$arr['page'] = $page;
$arr['total'] = $modx->db->getValue($modx->db->query("select count(*) from $mod_table"));
$arr['rows'] = array();
 
while ($r = $modx->db->getRow($res)) {

	$r['published'] = ($r['published'] == 1) ? '<span class="ok">Да</b>' : '<span class="bad">Нет</span>';
	$r['area'] = ($r['area'] == "") ? '<span class="bad">нет</span>' : '<span class="ok">'.$r['area'].'</span>';
	
	$class = (($r['count_view'] >= $r['total_view']) && ($r['total_view'] > 0)) ? ' class="bad"' : '';
	$r['count_view'] = '<span'.$class.'>'.$r['count_view'].'</span>';
	
	$class = (($r['jump_count'] >= $r['total_jump']) && ($r['total_jump'] > 0)) ? ' class="bad"' : '';
	$r['jump_count'] = '<span'.$class.'>'.$r['jump_count'].'</span>';
	
	$r['template'] = nl2br($r['template']);
	
	$class = ($r['pub_date'] > time()) ? ' class="bad"' : '';
	$r['pub_date'] = '<span'.$class.'>'.(($r['pub_date'] != 0) ? date("d.m.Y H:i", $r['pub_date']) : '').'</span>';
	
	$class = ($r['unpub_date'] > time()) ? '' : ' class="bad"';
	$r['unpub_date'] = '<span'.$class.'>'.(($r['unpub_date'] != 0) ? date("d.m.Y H:i", $r['unpub_date']) : '').'</span>';
	
	$edit = '<a href="#" title="Редактировать" onclick="addRow('.$r['id'].');return false;"><img src="media/style/MODxCarbon/images/icons/logging.gif" align="absmiddle" /></a>';
	$del = '<a href="#" title="Удалить" onclick="if(confirm(\'Вы уверены?\')){delRow('.$r['id'].')};return false"><img src="media/style/MODxCarbon/images/icons/delete.png" align="absmiddle" /></a>';
	
	$arr['rows'][] = array(
		'id' => $r['id'],
		'cell' => array($r['id'], $r['pos'], $r['template'], $r['area'], $r['description'], $r['pub_date'], $r['unpub_date'], $r['published'], $r['count_view'], $r['total_view'], $r['jump_count'], $edit, $del)
	);
	$i++; 
}	
$out = json_encode($arr);
				
echo $out;