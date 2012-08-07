<?php
class CJotDataDb {
	var $fields = array();
	var $cfields = array();
	var $isNew;
	var $tbl = array();
	var $events = array();
	var $path;
	var $event;
	
	function CJotDataDb() {
		global $modx;
		$this->tbl["check"] = $GLOBALS['table_prefix']."jot_fields";
		$this->tbl["content"] = $modx->getFullTableName('jot_content');
		$this->tbl["subscriptions"] = $modx->getFullTableName('jot_subscriptions');
		$this->tbl["fields"] = $modx->getFullTableName('jot_fields');
		$this->isNew = false;
	}
	
	function Set($field, $value){
		$this->fields[$field]=$value; return true;
	}
	
	function Get($field){
		return $this->fields[$field];
	}
	
	function getFields() {
		$returnFields = $this->fields;
		$returnFields["custom"] = $this->cfields;
		return $returnFields;
	}
	
	function setCustom($field, $value){
		$this->cfields[$field] = $value; return true;
	}
	
	function getCustom($field){
		return $this->cfields[$field];
	}
	
	function FirstRun($path) {
		global $modx;
		
		//onBeforeFirstRun event
		if (null !== ($output = $this->doEvent("onBeforeFirstRun"))) return;
		
		$jot = $this->tbl["check"];
		$rs = $modx->db->query("SHOW TABLES LIKE '".$jot."'");
		$count = $modx->db->getRecordCount($rs);
		
		if ($count==0) {
			$fh = fopen($path."includes/jot.install.db.sql", 'r');
			$idata = '';
			while (!feof($fh)) {
				$idata .= fread($fh, 1024);
			}
			fclose($fh);
			$idata = str_replace("\r", '', $idata);
			$idata = str_replace('{PREFIX}',$GLOBALS['table_prefix'], $idata);
			$sql_array = explode("\n\n", $idata);
			foreach($sql_array as $sql_entry) {
				$sql_do = trim($sql_entry, "\r\n; ");
				$modx->db->query($sql_do);	
			}
		}
		
		//onFirstRun event
		$this->doEvent("onFirstRun");
	}
	
	function getCustomFieldsArray($id_values) {
		global $modx;
		$custom = array();
		$tbl = $this->tbl["fields"];
		
		if (is_array($id_values)) {
			$idstring = "'" . implode("','",$id_values) . "'";
		} else {
			$idstring = "'" . $id_values . "'";
		}
		$rs = $modx->db->query("select id, label, content from $tbl where id IN (" . $idstring . ")");
		while ($row = $modx->db->getRow($rs)) {
			$custom[$row['id']][$row['label']] = $row['content'];
		}
		return $custom;		
	}
	
	function Comment($id=0){
		global $modx;
		$this->isNew = $id == 0;
		if(!$this->isNew){
			
			// Standard Fields
			$tbl = $this->tbl["content"];
			$rs = $modx->db->query("select * from $tbl where id = $id");
			$this->fields = $modx->db->getRow($rs);
			$this->fields['id'] = $id;		
			
			// Custom Fields
			$cust = $this->getCustomFieldsArray($id);
			$this->cfields = $cust[$id];
			if (!is_array($this->cfields)) $this->cfields = array();
		}
		else {		
			$this->fields = array(
				'title' => 'new comment',
				'tagid' => '',
				'published' => 1,
				'uparent' => 0,
				'parent' => 0,
				'flags' => '',
				'secip' => '',
				'sechash' => '',
				'content' => '',
				'mode' => 0,
				'createdby' => 0,
				'createdon' => 0,
				'editedby' => 0,
				'editedon' => 0,
				'deleted' => 0,
				'deletedon' => 0,
				'deletedby' => 0,
				'publishedon' => 0,
				'publishedby' => 0
		    );
		}
		//onGetCommentFields event
		$this->doEvent("onGetCommentFields",array("id"=>$id));
	}
	
	function Save(){
		global $modx;
		
		foreach($this->fields as $n=>$v) { $this->fields[$n] = $modx->db->escape($v);}
			
		if($this->isNew){

			$this->fields['id'] = $modx->db->insert($this->fields,$this->tbl["content"]);
			
			//onBeforeSaveComment event
			$this->doEvent("onBeforeSaveComment");
			
			foreach($this->cfields as $n=>$v) { 
				$insert = array(
					'id' => $this->fields['id'],
					'label' => $n,
					'content' => $modx->db->escape($v)
				);
				$modx->db->insert($insert,$this->tbl["fields"]);
			}
			
			$this->isNew = false;
		} else {
			$id=$this->fields['id'];
			$modx->db->update($this->fields, $this->tbl["content"], "id=$id");
			
			//onBeforeSaveComment event
			$this->doEvent("onBeforeSaveComment");
			
			foreach($this->cfields as $n=>$v) { 
				$update = array(
					'id' => $id,
					'label' => $n,
					'content' => $modx->db->escape($v)
				);
				//if (!$modx->db->update($update, $this->tbl["fields"], "id=$id and label='".$update["label"]."'")) $modx->db->insert($update,$this->tbl["fields"]);
				
				$modx->db->update($update,$this->tbl["fields"], "id=$id and label='".$update["label"]."'");
				$query=$modx->db->query("SELECT * FROM ".$this->tbl["fields"]." WHERE id=$id and label='".$update["label"]."'");
				$limit = $modx->db->getRecordCount($query); 
				if ($limit<1) {$modx->db->insert($update,$this->tbl["fields"]);}
			}
		}
		
		//onSaveComment event
		$this->doEvent("onSaveComment");
	}
	
	function Delete(){
		global $modx;
		if($this->isNew) return;
		$id=$this->fields['id'];
		
		//onDeleteComment event
		if (null === $this->doEvent("onDeleteComment")) {
			$modx->db->delete($this->tbl["content"],"id=$id");
			$modx->db->delete($this->tbl["fields"],"id=$id");
		}
		$this->isNew=true;
	}
	
	function hasPosted($interval,$user) {
		global $modx;
		$chktime = strtotime("-".$interval." seconds");
		$sql = 'SELECT count(id) as post FROM '.$this->tbl["content"].' WHERE sechash = "'.$user['sechash'].'" AND createdon > '.$chktime;
		$returnValue = intval($modx->db->getValue($sql));
		if ($returnValue > 0 ) { return true; } else { return false; }
	}
	
	function getUserPostCount($docid,$tagid) {
		global $modx;
		
		//onBeforeGetUserPostCount event
		if (null !== ($output = $this->doEvent("onBeforeGetUserPostCount",array("docid"=>$docid,"tagid"=>$tagid)))) return $output;
		
		$sql = $modx->db->query('SELECT createdby,COUNT(id) FROM ' . $this->tbl["content"] . ' WHERE published = 1 ' . $this->sqlPart($docid,$tagid) . ' GROUP BY createdby');
		$counts = $modx->db->makeArray($sql);
		$userpostcount = array();
		foreach($counts as $v) $userpostcount[$v['createdby']] = $v['COUNT(id)'];
		
		return $userpostcount;
	}
	
	function GetCommentCount($docid,$tagid,$viewtype) {
		global $modx;
		
		//onBeforeGetCommentCount event
		if (null !== ($output = $this->doEvent("onBeforeGetCommentCount",array("docid"=>$docid,"tagid"=>$tagid,"viewtype"=>$viewtype)))) return $output;
		
		switch ($viewtype) {
			case 2:
				$where = "published >= 0 "; // Mixed
				break;
			case 0:
				$where = "published = 0 "; // Unpublished
				break;
			case 1:
			default:
				$where = "published = 1 "; // Published
		}
		$sql = 'SELECT count(id) FROM ' . $this->tbl["content"] . ' WHERE ' . $where . $this->sqlPart($docid,$tagid);
		return intval($modx->db->getValue($sql));
	}
			
	function getOrderByDirection($dir = "a") {
		switch($dir) {
			case "d": return "desc";
			case "a":
			default:
   		return "asc"; 
		}
	}
	
	function GetComments($docid,$tagid,$viewtype,$upc,$sort,$offset,$length) {
		global $modx;
		$tbl = $this->tbl["content"];
		$where = NULL;
		if ($length > 0 ) { $limit = " limit $offset, $length"; }
		
		$orderby = " order by createdon desc ";
		$tblcustom = "";
		if (strlen($sort) > 3) {
			$orderby = array();
			$tblcustom = array();
			$obparts = explode(",", $sort);
			$c = 0;
			foreach ($obparts as $obpart) {
				$x = explode(":", $obpart);
				if($x[0]{0} == "#") {
					$c++;
					$fld = str_replace("#","",$x[0]);
					$tblcustom[] = "left join " . $this->tbl["fields"] . " as " . "c" . $c . " on c". $c . ".id = a.id and c". $c . ".label = '" . $fld . "'";
					$orderby[] = "c" . $c . ".content" . " " . $this->getOrderByDirection($x[1]);
				} else {
				$orderby[] = $x[0] . " " . $this->getOrderByDirection($x[1]);
				}
			}
			$orderby = " order by " . implode(", ",$orderby);
			$tblcustom = implode(" ",$tblcustom);
		} 

		switch ($viewtype) {
			case 2:
				$where = " and published >= 0 "; // Mixed
				break;
			case 0:
				$where = " and published = 0 "; // Unpublished
				break;
			case 1:
			default:
				$where = " and published = 1 "; // Published
		}
		
		$user_data_tbl="left join " . $modx->getFullTableName('manager_users') . " as mu on mu.id=a.createdby "
			."left join " . $modx->getFullTableName('user_attributes') . " as mua on mua.internalKey=mu.id ";
		$webuser_data_tbl="left join " . $modx->getFullTableName('web_users') . " as wu on wu.id=-a.createdby "
			."left join " . $modx->getFullTableName('web_user_attributes') . " as wua on wua.internalKey=wu.id ";
		
		$sql = "(select a.*,mu.username,mua.fullname,mua.email,mua.role,mua.gender,mua.country,mua.photo
		from " . $tbl . " as a " .$user_data_tbl." ".$tblcustom. " where a.createdby>=0 " . $this->sqlPart($docid,$tagid) . "and mode = '0' " . $where . ") 
		union (select a.*,wu.username,wua.fullname,wua.email,wua.role,wua.gender,wua.country,wua.photo  
		from " . $tbl . " as a " .$webuser_data_tbl." ".$tblcustom. " where a.createdby<0 " . $this->sqlPart($docid,$tagid) . "and mode = '0' " . $where . ")"
		. $orderby . $limit;
		
		//onBeforeGetComments event
		if (null !== ($output = $this->doEvent("onBeforeGetComments",array("docid"=>$docid,"tagid"=>$tagid,"viewtype"=>$viewtype,"upc"=>$upc,"sort"=>$sort,"offset"=>$offset,"length"=>$length,"sql"=>&$sql)))) return $output;
		
		return $this->GetCommentsArray($sql,$docid,$tagid,$upc);
	}
	
	function GetCommentsArray($query,$docid,$tagid,$upc) {
		global $modx;
		$rs = $modx->db->query($query);	
		$comments = array();
		$ids = array();
		while ($row = $modx->db->getRow($rs)) {
			$ids[] = $row["id"];
			$comments[] = $row;
		}
		
		$custom = $this->getCustomFieldsArray($ids);
		
		switch ($upc) {
			case 2:
				$userpostcount = $this->getUserPostCount($docid,$tagid);
				break;
			case 0:
				$userpostcount = array();
				break;
			case 1:
			default:
				$userpostcount = $this->getUserPostCount("*",$tagid);
		}
		
		$arrComments = array();
		foreach($comments as $comment) {
			$comment["custom"] = $custom[$comment["id"]];
			$comment["userpostcount"] = intval($userpostcount[$comment["createdby"]]);
			$arrComments[] = $comment;
		}
		
		//onGetComments event
		if (null !== ($output = $this->doEvent("onGetComments",array("arrComments"=>$arrComments,"docid"=>$docid,"tagid"=>$tagid,"upc"=>$upc)))) return $output;
		
		return $arrComments;
	}
	
	function hasSubscription($docid = 0,$tagid = '', $user = array()) {
		global $modx;
		
		$sql = 'SELECT count(id) as subscription FROM '.$this->tbl["subscriptions"].' WHERE userid = "'.$user['id'].'" AND uparent = "'.$docid.'" AND tagid = "'.$tagid.'"';
		$returnValue = intval($modx->db->getValue($sql));
		
		//onSubscriptionCheck event
		if (null !== ($output = $this->doEvent("onSubscriptionCheck",array("docid"=>$docid,"tagid"=>$tagid,"user"=>$user)))) return $output;
		
		if ($returnValue > 0 ) { return true; } else { return false; }
	}
	
	
	function getSubscriptions($docid = 0,$tagid = '') {
		global $modx;
		
		//onBeforeGetSubscriptions event
		if (null !== ($output = $this->doEvent("onBeforeSubscriptions",array("docid"=>$docid,"tagid"=>$tagid)))) return $output;
		
		$tbl = $this->tbl["subscriptions"];
		$subscriptions = array();
		$rs = $modx->db->query("select userid from $tbl where id>0 ". $this->sqlPart($docid,$tagid));	
		$usrRows = $modx->db->getColumn("userid", $rs);
		foreach ($usrRows as $v) $subscriptions[] = intval($v);
		
		//onGetSubscriptions event
		if (null !== ($output = $this->doEvent("onGetSubscriptions",array("docid"=>$docid,"tagid"=>$tagid,"subscriptions"=>$subscriptions)))) return $output;
		
		return $subscriptions;
	}
	
	function Subscribe($docid = 0,$tagid = '', $user = array()){
		global $modx;
		$tbl=$this->tbl["subscriptions"];
		$fields["uparent"] = $docid;
		$fields["tagid"] = $tagid;
		$fields["userid"] = $user["id"];
		//onBeforeSubscribe event
		if (null === $this->doEvent("onBeforeSubscribe",array("docid"=>$docid,"tagid"=>$tagid,"user"=>$user)))
			$modx->db->insert($fields,$tbl);
	}
	
	function Unsubscribe($docid = 0,$tagid = '', $user = array()) {
		global $modx;
		$userid = $user["id"];
		//onBeforeUnsubscribe event
		if (null === $this->doEvent("onBeforeUnsubscribe",array("docid"=>$docid,"tagid"=>$tagid,"user"=>$user)))
			$modx->db->delete($this->tbl["subscriptions"],"userid='$userid' and uparent='$docid' and tagid = '$tagid'");
	}
	
	function isValidComment($docid = 0,$tagid = '', $commentid = 0) {
		global $modx;
		$sql = 'select count(id) FROM '.$this->tbl["content"].' WHERE id = "'. $commentid .'" AND uparent = "'.$docid.'" AND tagid = "'.$tagid.'"';
		return intval($modx->db->getValue($sql));
	}
	
	// invoke events
	function doEvent($event,$params=array()) {
		global $modx;
		$this->event = $event;
		$event = $this->events[$event];
		if (!$event) return null;
		$plugins=explode(',',$event);
		$object = & $this;
		$result = null;
		foreach ($plugins as $plugin) {
			if(function_exists($plugin)) {
				if (null !== ($output = $plugin($object,$params))) $result = $output;
			} else {
				$pluginPath = $this->path . 'plugins/' . $plugin . '.inc.php';
				if(is_file($pluginPath)) {
					include $pluginPath;
					if(function_exists($plugin)) {
						if (null !== ($output = $plugin($object,$params))) $result = $output;
					}
				}
			}
		}
		$this->event = '';
		return $result;
	}
	
	function sqlPart($docid,$tagid) {
		if (is_array($docid)) {
			$docids = "and uparent IN (" . implode(",",$docid) . ") ";
		} elseif ($docid != "*") {
			$docids = "and uparent = " . $docid . " ";
		} else {
			$docids = "";
		}
		if (is_array($tagid)) {
			$tagids = "and tagid IN ('" . implode("','",$tagid) . "') ";
		} elseif ($tagid == "") {
			$tagids = "and tagid = '' ";
		} elseif ($tagid != "*") {
			$tagids = "and tagid = '" . $tagid . "' ";
		} else {
			$tagids = "";
		}
		return $docids.$tagids;
	}
}
?>
