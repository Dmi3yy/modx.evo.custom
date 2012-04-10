<?php

// logger class.
/*

Usage:

include_once "log.class.inc.php"; // include_once the class
$log = new logHandler;	// create the object
$log->initAndWriteLog($msg); // write $msg to log, and populate all other fields as best as possible
$log->initAndWriteLog($msg, $internalKey, $username, $action, $id, $itemname); // write $msg and other data to log

*/

class logHandler {
	// Single variable for a log entry
	var $entry = array();

	function logError($msg) {
		include_once dirname(__FILE__)."/error.class.inc.php";
		$e = new errorHandler;
		$e->setError(9, "Logging error: ".$msg);
		$e->dumpError();
		return;
	}

	function initAndWriteLog($msg="", $internalKey="", $username="", $action="", $itemid="", $itemname="") {
		global $modx;
		$this->entry['msg'] = $msg;	// writes testmessage to the object
        $this->entry['action'] = empty($action)? (int) $_REQUEST['a'] : $action;    // writes the action to the object

		// User Credentials
		$this->entry['internalKey'] = $internalKey == "" ? $modx->getLoginUserID() : $internalKey;
		$this->entry['username'] = $username == "" ? $modx->getLoginUserName() : $username;

        $this->entry['itemId'] = empty($itemid) ? (int) $_REQUEST['id'] : $itemid;  // writes the id to the object
		if($this->entry['itemId'] == 0) $this->entry['itemId'] = "-"; // to stop items having id 0

		$this->entry['itemName'] = $itemname == "" ? $_SESSION['itemname'] : $itemname;	// writes the id to the object
		if($this->entry['itemName'] == "") $this->entry['itemName'] = "-"; // to stop item name being empty

		$this->writeToLog();
		return;
	}

	// function to write to the log
	// collects all required info, and
	// writes it to the logging table
	function writeToLog() {
		global $modx;
		
		$tbl_manager_log = $modx->getFullTableName('manager_log');
		
		if($this->entry['internalKey'] == "") {
			$this->logError("internalKey not set.");
			return;
		}
        if(empty($this->entry['action'])) {
			$this->logError("action not set.");
			return;
		}
		if($this->entry['msg'] == "") {
			include_once "actionlist.inc.php";
			$this->entry['msg'] = getAction($this->entry['action'], $this->entry['itemId']);
			if($this->entry['msg'] == "") {
				$this->logError("couldn't find message to write to log.");
				return;
			}
		}
		
		$fields['timestamp']   = time();
		$fields['internalKey'] = $modx->db->escape($this->entry['internalKey']);
		$fields['username']    = $modx->db->escape($this->entry['username']);
		$fields['action']      = $this->entry['action'];
		$fields['itemid']      = $this->entry['itemId'];
		$fields['itemname']    = $modx->db->escape($this->entry['itemName']);
		$fields['message']     = $modx->db->escape($this->entry['msg']);
		
		if(!$insert_id = $modx->db->insert($fields,$tbl_manager_log)) {
			$this->logError("Couldn't save log to table! ".mysql_error());
			return true;
		}
		else
		{
			$limit = ($modx->config['manager_log_limit']) ? intval($modx->config['manager_log_limit']) : 2000;
			$trim  = ($modx->config['manager_log_trim'])  ? intval($modx->config['manager_log_trim']) : 100;
			if(($insert_id % $trim) == 0)
			{
				$this->purge_manager_log($limit,$trim);
			}
		}
	}
	
	function purge_manager_log($limit=2000, $trim=100)
	{
		global $modx;
		
		if($limit < $trim) $trim = $limit;
		
		$tbl_manager_log = $modx->getFullTableName("manager_log");
		$sql = "SELECT COUNT(id) as count FROM {$tbl_manager_log}";
		$rs = $modx->db->query($sql);
		if($rs) $row = $modx->db->getRow($rs);
		$over = $row['count'] - $limit;
		if(0 < $over)
		{
			$trim = ($over + $trim);
			$sql = "DELETE FROM {$tbl_manager_log} LIMIT {$trim}";
			$modx->db->query($sql);
			$sql = "OPTIMIZE TABLE {$tbl_manager_log}";
			$modx->db->query($sql);
		}
	}
}
