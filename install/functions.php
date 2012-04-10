<?php
function modx_escape($s)
{
	global $database_connection_charset;
	if (function_exists('mysql_set_charset'))
	{
		$s = mysql_real_escape_string($s);
	}
	elseif ($database_connection_charset=='utf8')
	{
		$s = mb_convert_encoding($s, 'eucjp-win', 'utf-8');
		$s = mysql_real_escape_string($s);
		$s = mb_convert_encoding($s, 'utf-8', 'eucjp-win');
	}
	else
	{
		$s = mysql_escape_string($s);
	}
	return $s;
}

function compare_check($params)
{
	global $table_prefix;
	
	$name_field  = 'name';
	$name        = $params['name'];
	$mode        = 'version_compare';
	if($params['version'])
	{
		$new_version = $params['version'];
	}
	//print_r($params);
	switch($params['category'])
	{
		case 'template':
			$table = $table_prefix . 'site_templates';
			$name_field = 'templatename';
			$mode       = 'desc_compare';
			break;
		case 'tv':
			$table = $table_prefix . 'site_tmplvars';
			$mode  = 'desc_compare';
			break;
		case 'chunk':
			$table = $table_prefix . 'site_htmlsnippets';
			$mode  = 'name_compare';
			break;
		case 'snippet':
			$table = $table_prefix . 'site_snippets';
			$mode  = 'version_compare';
			break;
		case 'plugin':
			$table = $table_prefix . 'site_plugins';
			$mode  = 'version_compare';
			break;
		case 'module':
			$table = $table_prefix . 'site_modules';
			$mode  = 'version_compare';
			break;
	}
	$sql = "SELECT * FROM `{$table}` WHERE `{$name_field}`='{$name}'";
	if($params['category']=='plugin') $sql .= " AND `disabled`='0'";
	$rs = mysql_query($sql);
	if(!$rs) echo "An error occurred while executing a query: ".mysql_error();
	else     
	{
		$row = mysql_fetch_assoc($rs);
		$count = mysql_num_rows($rs);
		if($count===1)
		{
			$new_version_str = ($new_version) ? '<strong>' . $new_version . '</strong> ':'';
			$new_desc    = $new_version_str . $params['description'];
			$old_desc    = $row['description'];
			$old_version = substr($old_desc,0,strpos($old_desc,'</strong>'));
			$old_version = strip_tags($old_version);
			if($mode == 'version_compare' && $old_version === $new_version)
			{
				                            $result = 'same';
			}
			elseif($mode == 'name_compare') $result = 'same';
			elseif($old_desc === $new_desc) $result = 'same';
			else                            $result = 'diff';
		}
		elseif($count < 1)                  $result = 'no exists';
	}
if($params['category']=='chunk')
{
//echo '$old_version=' . $old_version . '<br />';
//echo '$new_version=' . $new_version . '<br />';
}
	
	
	return $result;
}

function parse_docblock($element_dir, $filename)
{
	$params = array();
	$fullpath = $element_dir . '/' . $filename;
	if(is_readable($fullpath))
	{
		$tpl = @fopen($fullpath, 'r');
		if($tpl)
		{
			$params['filename'] = $filename;
			$docblock_start_found = false;
			$name_found = false;
			$description_found = false;
			$docblock_end_found = false;
			
			while(!feof($tpl))
			{
				$line = fgets($tpl);
				if(!$docblock_start_found)
				{
					// find docblock start
					if(strpos($line, '/**') !== false)
					{
						$docblock_start_found = true;
					}
					continue;
				}
				elseif(!$name_found)
				{
					// find name
					$ma = null;
					if(preg_match("/^\s+\*\s+(.+)/", $line, $ma))
					{
						$params['name'] = trim($ma[1]);
						$name_found = !empty($params['name']);
					}
					continue;
				}
				elseif(!$description_found)
				{
					// find description
					$ma = null;
					if(preg_match("/^\s+\*\s+(.+)/", $line, $ma))
					{
						$params['description'] = trim($ma[1]);
						$description_found = !empty($params['description']);
					}
					continue;
				}
				else
				{
					$ma = null;
					if(preg_match("/^\s+\*\s+\@([^\s]+)\s+(.+)/", $line, $ma))
					{
						$param = trim($ma[1]);
						$val = trim($ma[2]);
						if(!empty($param) && !empty($val))
						{
							if($param == 'internal')
							{
								$ma = null;
								if(preg_match("/\@([^\s]+)\s+(.+)/", $val, $ma))
								{
									$param = trim($ma[1]);
									$val = trim($ma[2]);
								}
								//if($val !== '0' && (empty($param) || empty($val))) {
								if(empty($param))
								{
									continue;
								}
							}
							$params[$param] = $val;
						}
					}
					elseif(preg_match("/^\s*\*\/\s*$/", $line))
					{
						$docblock_end_found = true;
						break;
					}
				}
			}
			@fclose($tpl);
		}
	}
	return $params;
}


function clean_up($sqlParser) {
		$ids = array();
		$mysqlVerOk = -1;

		if(function_exists("mysql_get_server_info")) {
			$mysqlVerOk = (version_compare(mysql_get_server_info(),"4.0.20")>=0);
		}	
		
		// secure web documents - privateweb 
		mysql_query("UPDATE `".$sqlParser->prefix."site_content` SET privateweb = 0 WHERE privateweb = 1");
		$sql =  "SELECT DISTINCT sc.id 
				 FROM `".$sqlParser->prefix."site_content` sc
				 LEFT JOIN `".$sqlParser->prefix."document_groups` dg ON dg.document = sc.id
				 LEFT JOIN `".$sqlParser->prefix."webgroup_access` wga ON wga.documentgroup = dg.document_group
				 WHERE wga.id>0";
		$ds = mysql_query($sql);
		if(!$ds)
		{
			echo "An error occurred while executing a query: ".mysql_error();
		}
		else {
			while($r = mysql_fetch_assoc($ds)) $ids[]=$r["id"];
			if(count($ids)>0) {
				mysql_query("UPDATE `".$sqlParser->prefix."site_content` SET privateweb = 1 WHERE id IN (".implode(", ",$ids).")");	
				unset($ids);
			}
		}
		
		// secure manager documents privatemgr
		mysql_query("UPDATE `".$sqlParser->prefix."site_content` SET privatemgr = 0 WHERE privatemgr = 1");
		$sql =  "SELECT DISTINCT sc.id 
				 FROM `".$sqlParser->prefix."site_content` sc
				 LEFT JOIN `".$sqlParser->prefix."document_groups` dg ON dg.document = sc.id
				 LEFT JOIN `".$sqlParser->prefix."membergroup_access` mga ON mga.documentgroup = dg.document_group
				 WHERE mga.id>0";
		$ds = mysql_query($sql);
		if(!$ds)
		{
			echo "An error occurred while executing a query: ".mysql_error();
		}
		else {
			while($r = mysql_fetch_assoc($ds)) $ids[]=$r["id"];
			if(count($ids)>0) {
				mysql_query("UPDATE `".$sqlParser->prefix."site_content` SET privatemgr = 1 WHERE id IN (".implode(", ",$ids).")");	
				unset($ids);
			}
		}
}

// Property Update function
function propUpdate($new,$old)
{
	// Split properties up into arrays
	$returnArr = array();
	$newArr = explode("&",$new);
	$oldArr = explode("&",$old);
	
	foreach ($newArr as $k => $v)
	{
		if(!empty($v))
		{
			$tempArr = explode("=",trim($v));
			$returnArr[$tempArr[0]] = $tempArr[1];
		}
	}
	foreach ($oldArr as $k => $v)
	{
		if(!empty($v))
		{
			$tempArr = explode("=",trim($v));
			$returnArr[$tempArr[0]] = $tempArr[1];
		}
	}
	
	// Make unique array
	$returnArr = array_unique($returnArr);
	
	// Build new string for new properties value
	foreach ($returnArr as $k => $v)
	{
		$return .= "&$k=$v ";
	}
	return modx_escape($return);
}

function getCreateDbCategory($category, $sqlParser) {
    $dbase = $sqlParser->dbname;
    $table_prefix = $sqlParser->prefix;
    $category_id = 0;
    if(!empty($category)) {
        $category = modx_escape($category);
        $rs = mysql_query("SELECT id FROM $dbase.`".$table_prefix."categories` WHERE category = '".$category."'");
        if(mysql_num_rows($rs) && ($row = mysql_fetch_assoc($rs)))
        {
            $category_id = $row['id'];
        } else {
            $q = "INSERT INTO $dbase.`".$table_prefix."categories` (`category`) VALUES ('{$category}');";
            $rs = mysql_query($q);
            if($rs) {
                $category_id = mysql_insert_id();
            }
        }
    }
    return $category_id;
}

function parse($src,$ph,$left='[+',$right='+]')
{
	foreach($ph as $k=>$v)
	{
		$k = $left . $k . $right;
		$src = str_replace($k,$v,$src);
	}
	return $src;
}

function is_webmatrix()
{
	return (isset($_SERVER['WEBMATRIXMODE'])) ? true : false;
}

function is_iis()
{
	return (strpos($_SERVER['SERVER_SOFTWARE'],'IIS')) ? true : false;
}
