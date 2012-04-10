<?php

/* Datbase API object of MySQL
 * Written by Raymond Irving June, 2005
 *
 */

class DBAPI {

	var $conn;
	var $config;
	var $isConnected;
	
	/**
	* @name:  DBAPI
	*
	*/
	function DBAPI($host='',$dbase='', $uid='',$pwd='',$prefix=NULL,$charset='',$connection_method='SET CHARACTER SET')
	{
		global $database_server,$dbase,$database_user,$database_password,$table_prefix,$database_connection_charset,$database_connection_method;
		
		$this->config['host']    = $host    ? $host    : $database_server;
		$this->config['dbase']   = $dbase   ? $dbase   : $dbase;
		$this->config['user']    = $uid     ? $uid     : $database_user;
		$this->config['pass']    = $pwd     ? $pwd     : $database_password;
		$this->config['charset'] = $charset ? $charset : $database_connection_charset;
		$this->config['connection_method'] = (isset($database_connection_method) ? $database_connection_method : $connection_method);
		$this->_dbconnectionmethod = &$this->config['connection_method'];
		$this->config['table_prefix'] = ($prefix !== NULL) ? $prefix : $table_prefix;
		$this->initDataTypes();
	}
		
		/**
		* @name:  initDataTypes
		* @desc:  called in the constructor to set up arrays containing the types
		*         of database fields that can be used with specific PHP types
		*/
	function initDataTypes()
	{
		$this->dataTypes['numeric'] = array (
			'INT',
			'INTEGER',
			'TINYINT',
			'BOOLEAN',
			'DECIMAL',
			'DEC',
			'NUMERIC',
			'FLOAT',
			'DOUBLE PRECISION',
			'REAL',
			'SMALLINT',
			'MEDIUMINT',
			'BIGINT',
			'BIT'
		);
		$this->dataTypes['string'] = array (
			'CHAR',
			'VARCHAR',
			'BINARY',
			'VARBINARY',
			'TINYBLOB',
			'BLOB',
			'MEDIUMBLOB',
			'LONGBLOB',
			'TINYTEXT',
			'TEXT',
			'MEDIUMTEXT',
			'LONGTEXT',
			'ENUM',
			'SET'
		);
		$this->dataTypes['date'] = array (
			'DATE',
			'DATETIME',
			'TIMESTAMP',
			'TIME',
			'YEAR'
		);
	}
		
	/**
	* @name:  connect
	*
	*/
	function connect($host = '', $dbase = '', $uid = '', $pwd = '', $persist = 0)
	{
		global $modx;
		if(!$uid)   $uid   = $this->config['user'];
		if(!$pwd)   $pwd   = $this->config['pass'];
		if(!$host)  $host  = $this->config['host'];
		if(!$dbase) $dbase = $this->config['dbase'];
		$tstart = $modx->getMicroTime();
		$safe_count = 0;
		while(!$this->conn && $safe_count<3)
		{
			if($persist!=0) $this->conn = mysql_pconnect($host, $uid, $pwd);
			else            $this->conn = mysql_connect($host, $uid, $pwd, true);
			
			if(!$this->conn)
			{
				$request_uri = $_SERVER['REQUEST_URI'];
				$request_uri = htmlspecialchars($request_uri, ENT_QUOTES);
				$ua          = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
				$referer     = htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES);
				$logtitle = 'Missing to create the database connection!';
				$msg = "{$logtitle}<br />{$request_uri}<br />{$ua}<br />{$referer}";
				$modx->logEvent(0, 2,$msg,$logtitle);
				sleep(3);
				$safe_count++;
			}
		}
		if(!$this->conn)
		{
			$modx->messageQuit('Failed to create the database connection!');
			exit;
		}
		else
		{
			$dbase = str_replace('`', '', $dbase); // remove the `` chars
			if (!@ mysql_select_db($dbase, $this->conn))
			{
				$modx->messageQuit("Failed to select the database '{$dbase}'!");
				exit;
			}
			@mysql_query("{$this->config['connection_method']} {$this->config['charset']}", $this->conn);
			$tend = $modx->getMicroTime();
			$totaltime = $tend - $tstart;
			if ($modx->dumpSQL)
			{
				$msg = sprintf("Database connection was created in %2.4f s", $totaltime);
				$modx->queryCode .= '<fieldset style="text-align:left;"><legend>Database connection</legend>' . "{$msg}</fieldset>";
			}
			if (function_exists('mysql_set_charset'))
			{
				mysql_set_charset($this->config['charset']);
			}
			$this->isConnected = true;
			// FIXME (Fixed by line below):
			// this->queryTime = this->queryTime + $totaltime;
			$modx->queryTime += $totaltime;
		}
	}
	
	/**
	* @name:  disconnect
	*
	*/
	function disconnect()
	{
		@ mysql_close($this->conn);
	}
	
	function escape($s)
	{
		if (empty ($this->conn) || !is_resource($this->conn))
		{
			$this->connect();
		}
		if (function_exists('mysql_set_charset') && $this->conn)
		{
			$s = mysql_real_escape_string($s, $this->conn);
		}
		elseif ($this->config['charset']=='utf8' && $this->conn)
		{
			$s = mb_convert_encoding($s, 'eucjp-win', 'utf-8');
			$s = mysql_real_escape_string($s, $this->conn);
			$s = mb_convert_encoding($s, 'utf-8', 'eucjp-win');
		}
		else
		{
			$s = mysql_escape_string($s);
		}
		return $s;
	}
	
	/**
	* @name:  query
	* @desc:  Mainly for internal use.
	* Developers should use select, update, insert, delete where possible
	*/
	function query($sql)
	{
		global $modx;
		if (empty ($this->conn) || !is_resource($this->conn))
		{
			$this->connect();
		}
		$tstart = $modx->getMicroTime();
		if (!$result = @ mysql_query($sql, $this->conn))
		{
			$modx->messageQuit('Execution of a query to the database failed - ' . $this->getLastError(), $sql);
		}
		else
		{
			$tend = $modx->getMicroTime();
			$totaltime = $tend - $tstart;
			$modx->queryTime = $modx->queryTime + $totaltime;
			if ($modx->dumpSQL)
			{
				$backtraces = debug_backtrace();
				$modx->queryCode .= '<fieldset style="text-align:left"><legend>Query ' . ++$this->executedQueries . " - " . sprintf("%2.4f s", $totaltime) . '</legend>' . $sql . '<br />src : ' . $backtraces[0]['file'] . '<br />line : ' . $backtraces[0]['line'] . '</fieldset>';
			}
			$modx->executedQueries = $modx->executedQueries + 1;
			return $result;
		}
	}
	
	/**
	* @name:  delete
	*
	*/
	function delete($from,$where='',$limit='')
	{
		if(!$from) return false;
		else
		{
			$table = $from;
			if($where != '') $where = "WHERE {$where}";
			if($limit != '') $limit = "LIMIT {$limit}";
			return $this->query("DELETE FROM {$table} {$where} {$limit}");
		}
	}
	
	/**
	* @name:  select
	*
	*/
	function select($fields = "*", $from = '', $where = '', $orderby = '', $limit = '')
	{
		if(!$from) return false;
		else
		{
			if($where !== '')   $where   = "WHERE {$where}";
			if($orderby !== '') $orderby = "ORDER BY {$orderby}";
			if($limit !== '')   $limit   = "LIMIT {$limit}";
			return $this->query("SELECT {$fields} FROM {$from} {$where} {$orderby} {$limit}");
		}
	}
	
	/**
	* @name:  update
	*
	*/
	function update($fields, $table, $where = '')
	{
		if(!$table) return false;
		else
		{
			if (!is_array($fields)) $pairs = $fields;
			else 
			{
				foreach ($fields as $key => $value)
				{
					$pair[] = "{$key}='{$value}'";
				}
				$pairs = join(',',$pair);
			}
			if($where != '') $where = "WHERE {$where}";
			return $this->query("UPDATE {$table} SET {$pairs} {$where}");
		}
	}
	
	/**
	* @name:  insert
	* @desc:  returns either last id inserted or the result from the query
	*/
	function insert($fields, $intotable, $fromfields = "*", $fromtable = '', $where = '', $limit = '')
	{
		if(!$intotable) return false;
		else
		{
			if (!is_array($fields)) $pairs = $fields;
			else
			{
				$keys = array_keys($fields);
				$keys = implode(',', $keys) ;
				$values = array_values($fields);
				$values = "'" . implode("','", $values) . "'";
				$pairs = "({$keys}) ";
				if(!$fromtable && $values) $pairs .= "VALUES({$values})";
				if ($fromtable)
				{
					if($where !== '') $where = "WHERE {$where}";
					if($limit !== '') $limit = "LIMIT {$limit}";
					$sql = "SELECT {$fromfields} FROM {$fromtable} {$where} {$limit}";
				}
			}
			$rt = $this->query("INSERT INTO {$intotable} {$pairs} {$sql}");
			$lid = $this->getInsertId();
			return $lid ? $lid : $rt;
		}
	}
	
	/**
	* @name:  getInsertId
	*
	*/
	function getInsertId($conn=NULL)
	{
		if(!is_resource($conn)) $conn =& $this->conn;
		return mysql_insert_id($conn);
	}
	
	/**
	* @name:  getAffectedRows
	*
	*/
	function getAffectedRows($conn=NULL)
	{
		if (!is_resource($conn)) $conn =& $this->conn;
		return mysql_affected_rows($conn);
	}
	
	/**
	* @name:  getLastError
	*
	*/
	function getLastError($conn=NULL)
	{
		if (!is_resource($conn)) $conn =& $this->conn;
		return mysql_error($conn);
	}
	
	/**
	* @name:  getRecordCount
	*
	*/
	function getRecordCount($ds)
	{
		return (is_resource($ds)) ? mysql_num_rows($ds) : 0;
	}
	
	/**
	* @name:  getRow
	* @desc:  returns an array of column values
	* @param: $dsq - dataset
	*
	*/
	function getRow($ds, $mode = 'assoc')
	{
		if($ds)
		{
			switch($mode)
			{
				case 'assoc':return mysql_fetch_assoc($ds);             break;
				case 'num'  :return mysql_fetch_row($ds);               break;
				case 'both' :return mysql_fetch_array($ds, MYSQL_BOTH); break;
				default     :
					global $modx;
					$modx->messageQuit("Unknown get type ({$mode}) specified for fetchRow - must be empty, 'assoc', 'num' or 'both'.");
			}
		}
	}
	
	/**
	* @name:  getColumn
	* @desc:  returns an array of the values found on colun $name
	* @param: $dsq - dataset or query string
	*/
	function getColumn($name, $dsq)
	{
		if (!is_resource($dsq)) $dsq = $this->query($dsq);
		if ($dsq)
		{
			$col = array ();
			while ($row = $this->getRow($dsq))
			{
				$col[] = $row[$name];
			}
			return $col;
		}
	}
	
	/**
	* @name:  getColumnNames
	* @desc:  returns an array containing the column $name
	* @param: $dsq - dataset or query string
	*/
	function getColumnNames($dsq)
	{
		if (!is_resource($dsq)) $dsq = $this->query($dsq);
		if ($dsq)
		{
			$names = array ();
			$limit = mysql_num_fields($dsq);
			for ($i = 0; $i < $limit; $i++)
			{
				$names[] = mysql_field_name($dsq, $i);
			}
			return $names;
		}
	}
		
	/**
	* @name:  getValue
	* @desc:  returns the value from the first column in the set
	* @param: $dsq - dataset or query string
	*/
	function getValue($dsq)
	{
		if (!is_resource($dsq)) $dsq = $this->query($dsq);
		if ($dsq)
		{
			$r = $this->getRow($dsq, 'num');
			return $r[0];
		}
	}
	
	/**
	* @name:  getXML
	* @desc:  returns an XML formay of the dataset $ds
	*/
	function getXML($dsq)
	{
		if (!is_resource($dsq)) $dsq = $this->query($dsq);
		$xmldata = "<xml>\r\n<recordset>\r\n";
		while ($row = $this->getRow($dsq, 'both'))
		{
			$xmldata .= "<item>\r\n";
			for ($j = 0; $line = each($row); $j++)
			{
				if ($j % 2)
				{
					$xmldata .= "<{$line[0]}>{$line[1]}</{$line[0]}>\r\n";
				}
			}
			$xmldata .= "</item>\r\n";
		}
		$xmldata .= "</recordset>\r\n</xml>";
		return $xmldata;
	}
	
	/**
	* @name:  getTableMetaData
	* @desc:  returns an array of MySQL structure detail for each column of a
	*         table
	* @param: $table: the full name of the database table
	*/
	function getTableMetaData($table)
	{
		$metadata = false;
		if (!empty ($table))
		{
			$sql = "SHOW FIELDS FROM {$table}";
			if ($ds = $this->query($sql))
			{
				while ($row = $this->getRow($ds))
				{
					$fieldName = $row['Field'];
					$metadata[$fieldName] = $row;
				}
			}
		}
		return $metadata;
	}
	
	/**
	* @name:  prepareDate
	* @desc:  prepares a date in the proper format for specific database types
	*         given a UNIX timestamp
	* @param: $timestamp: a UNIX timestamp
	* @param: $fieldType: the type of field to format the date for
	*         (in MySQL, you have DATE, TIME, YEAR, and DATETIME)
	*/
	function prepareDate($timestamp, $fieldType = 'DATETIME')
	{
		$date = '';
		if (!$timestamp !== false && $timestamp > 0)
		{
			switch ($fieldType)
			{
				case 'DATE' :
				$date = date('Y-m-d', $timestamp);
				break;
				case 'TIME' :
				$date = date('H:i:s', $timestamp);
				break;
				case 'YEAR' :
				$date = date('Y', $timestamp);
				break;
				case 'DATETIME' :
				default :
				$date = date('Y-m-d H:i:s', $timestamp);
				break;
			}
		}
		return $date;
	}
	
	/**
	* @name:  getHTMLGrid
	* @param: $params: Data grid parameters
	*         columnHeaderClass
	*         tableClass
	*         itemClass
	*         altItemClass
	*         columnHeaderStyle
	*         tableStyle
	*         itemStyle
	*         altItemStyle
	*         columns
	*         fields
	*         colWidths
	*         colAligns
	*         colColors
	*         colTypes
	*         cellPadding
	*         cellSpacing
	*         header
	*         footer
	*         pageSize
	*         pagerLocation
	*         pagerClass
	*         pagerStyle
	*
	*/
	function getHTMLGrid($dsq, $params)
	{
		global $base_path;
		if (!is_resource($dsq)) $dsq = $this->query($dsq);
		if ($dsq)
		{
			include_once (MODX_MANAGER_PATH . 'includes/controls/datagrid.class.php');
			$grd = new DataGrid('', $dsq);
			
			$grd->noRecordMsg = $params['noRecordMsg'];
			
			$grd->columnHeaderClass = $params['columnHeaderClass'];
			$grd->cssClass = $params['cssClass'];
			$grd->itemClass = $params['itemClass'];
			$grd->altItemClass = $params['altItemClass'];
			
			$grd->columnHeaderStyle = $params['columnHeaderStyle'];
			$grd->cssStyle = $params['cssStyle'];
			$grd->itemStyle = $params['itemStyle'];
			$grd->altItemStyle = $params['altItemStyle'];
			
			$grd->columns = $params['columns'];
			$grd->fields = $params['fields'];
			$grd->colWidths = $params['colWidths'];
			$grd->colAligns = $params['colAligns'];
			$grd->colColors = $params['colColors'];
			$grd->colTypes = $params['colTypes'];
			$grd->colWraps = $params['colWraps'];
			
			$grd->cellPadding = $params['cellPadding'];
			$grd->cellSpacing = $params['cellSpacing'];
			$grd->header = $params['header'];
			$grd->footer = $params['footer'];
			$grd->pageSize = $params['pageSize'];
			$grd->pagerLocation = $params['pagerLocation'];
			$grd->pagerClass = $params['pagerClass'];
			$grd->pagerStyle = $params['pagerStyle'];
			
			return $grd->render();
		}
	}
	
	/**
	* @name:  makeArray
	* @desc:  turns a recordset into a multidimensional array
	* @return: an array of row arrays from recordset, or empty array
	*          if the recordset was empty, returns false if no recordset
	*          was passed
	* @param: $rs Recordset to be packaged into an array
	*/
	function makeArray($rs='')
	{
		if(!$rs) return false;
		$rsArray = array();
		$qty = $this->getRecordCount($rs);
		for ($i = 0; $i < $qty; $i++)
		{
			$rsArray[] = $this->getRow($rs);
		}
		return $rsArray;
	}
	
	/**
	* @name	getVersion
	* @desc	returns a string containing the database server version
	*
	* @return string
	*/
	function getVersion()
	{
		return mysql_get_server_info();
	}
}
