<?php

// MySQL Dump Parser
// SNUFFKIN/ Alex 2004

class SqlParser {
	var $prefix, $mysqlErrors;
	var $conn, $installFailed, $sitename, $adminname, $adminemail, $adminpass, $managerlanguage;
	var $mode;
	var $dbVersion;
    var $connection_charset, $connection_collation, $autoTemplateLogic,$ignoreDuplicateErrors;

	function SqlParser() {
		$this->prefix = 'modx_';
		$this->adminname = 'admin';
		$this->adminpass = 'password';
		$this->adminemail = 'example@example.com';
		$this->connection_charset = 'utf8';
		$this->connection_collation = 'utf8_general_ci';
		$this->ignoreDuplicateErrors = false;
		$this->managerlanguage = 'english';
		$this->autoTemplateLogic = 'system';
	}

	function process($filename) {
	    global $modx,$modx_version;

		$this->dbVersion = 3.23; // assume version 3.23
		if(function_exists("mysql_get_server_info")) {
			$ver = mysql_get_server_info();
			$this->dbVersion = (float) $ver; // Typecasting (float) instead of floatval() [PHP < 4.2]
		}
		
		// check to make sure file exists
		if (!file_exists($filename)) {
			$this->mysqlErrors[] = array("error" => "File '$filename' not found");
			$this->installFailed = true ;
			return false;
		}

		$idata = file_get_contents($filename);

		$idata = str_replace("\r", '', $idata);

		// check if in upgrade mode
		if ($this->mode=="upd") {
			// remove non-upgradeable parts
			$s = strpos($idata,"non-upgrade-able[[");
			$e = strpos($idata,"]]non-upgrade-able")+17;
			if($s && $e) $idata = str_replace(substr($idata,$s,$e-$s)," Removed non upgradeable items",$idata);
		}
		
		if(version_compare($this->dbVersion,'4.1.0', '>='))
		{
			$char_collate = "DEFAULT CHARSET={$this->connection_charset} COLLATE {$this->connection_collation}";
			$idata = str_replace('ENGINE=MyISAM', "ENGINE=MyISAM {$char_collate}", $idata);
		}
		
		// replace {} tags
		$ph = array();
		$ph['PREFIX']            = $this->prefix;
		$ph['ADMINNAME']         = $this->adminname;
		$ph['ADMINFULLNAME']     = substr($this->adminemail,0,strpos($this->adminemail,'@'));
		$ph['ADMINEMAIL']        = $this->adminemail;
		$ph['ADMINPASS']         = $this->adminpass;
		$ph['MANAGERLANGUAGE']   = $this->managerlanguage;
		$ph['AUTOTEMPLATELOGIC'] = $this->autoTemplateLogic;
		$ph['DATE_NOW']          = time();
		$idata = parse($idata,$ph,'{','}');
		
		$sql_array = preg_split('@;[ \t]*\n@', $idata);
		
		$num = 0;
		foreach($sql_array as $sql_entry)
		{
			$sql_do = trim($sql_entry, "\r\n; ");
			$num++;
			if ($sql_do) mysql_query($sql_do);
			if(mysql_error())
			{
				// Ignore duplicate and drop errors - Raymond
				if ($this->ignoreDuplicateErrors)
				{
					if (mysql_errno() == 1060 || mysql_errno() == 1061 || mysql_errno() == 1091) continue;
				}
				// End Ignore duplicate
				$this->mysqlErrors[] = array("error" => mysql_error(), "sql" => $sql_do);
				$this->installFailed = true;
			}
		}
	}

}
