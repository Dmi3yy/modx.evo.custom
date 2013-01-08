<?php
function delthread(&$object,$params){
	global $modx;
	
	$id = $object->fields['id'];
	$rs = $modx->db->makeArray($modx->db->select('id, parent', $object->tbl["content"]));
	$parents = array();
	foreach($rs as $v) $parents[$v['parent']][] = $v['id'];
	$ids = getchilds($id,$parents);
	if (!empty($ids)) {
		$ids = implode(",", $ids);
		$modx->db->delete($object->tbl["content"],"id IN($ids)");
		$modx->db->delete($object->tbl["fields"],"id IN($ids)");
	}
}
function getchilds($id,$parents,$ids=array()){
	if (isset($parents[$id])) {
		foreach($parents[$id] as $v){
			$ids[$v] = $v;
			$ids += getchilds($v,$parents,$ids);
		}
	}
	return $ids;
}
?>