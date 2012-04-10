<?php
//:: MODx Installer Setup file 
//:::::::::::::::::::::::::::::::::::::::::
require_once('../manager/includes/version.inc.php');

$moduleName = 'MODX';
$moduleVersion = $modx_branch.' '.$modx_version;
$moduleRelease = $modx_release_date;
$moduleSQLBaseFile = "setup.sql";
$moduleSQLDataFile = "setup.data.sql";
$chunkPath = $setupPath .'/assets/chunks';
$snippetPath = $setupPath .'/assets/snippets';
$pluginPath = $setupPath .'/assets/plugins';
$modulePath = $setupPath .'/assets/modules';
$templatePath = $setupPath .'/assets/templates';
$tvPath = $setupPath .'/assets/tvs';

@ $conn = mysql_connect($database_server, $database_user, $database_password);
if (function_exists('mysql_set_charset'))
{
	mysql_set_charset($database_connection_charset);
}
@ mysql_select_db(trim($dbase, '`'), $conn);

// setup Template template files - array : name, description, type - 0:file or 1:content, parameters, category
$mt = &$moduleTemplates;
if(is_dir($templatePath) && is_readable($templatePath)) {
		$d = dir($templatePath);
		while (false !== ($tplfile = $d->read()))
		{
			if(substr($tplfile, -4) != '.tpl') continue;
			$params = parse_docblock($templatePath, $tplfile);
			if(is_array($params) && (count($params)>0))
			{
				$description = empty($params['version']) ? $params['description'] : "<strong>{$params['version']}</strong> {$params['description']}";
				
				if($installMode===1 && compare_check($params)=='same') continue;
					
				$mt[] = array
				(
					$params['name'],
					$description,
					// Don't think this is gonna be used ... but adding it just in case 'type'
					$params['type'],
					"$templatePath/{$params['filename']}",
					$params['modx_category'],
					$params['lock_template'],
					array_key_exists('installset', $params) ? preg_split("/\s*,\s*/", $params['installset']) : false
				);
			}
		}
		$d->close();
}

// setup Template Variable template files
$mtv = &$moduleTVs;
if(is_dir($tvPath) && is_readable($tvPath)) {
		$d = dir($tvPath);
    while (false !== ($tplfile = $d->read())) {
			if(substr($tplfile, -4) != '.tpl') continue;
			$params = parse_docblock($tvPath, $tplfile);
        if(is_array($params) && (count($params)>0)) {
				$description = empty($params['version']) ? $params['description'] : "<strong>{$params['version']}</strong> {$params['description']}";
				
				if($installMode===1 && compare_check($params)=='same') continue;
					
            $mtv[] = array(
					$params['name'],
					$params['caption'],
					$description,
					$params['input_type'],
					$params['input_options'],
					$params['input_default'],
					$params['output_widget'],
					$params['output_widget_params'],
					"$templatePath/{$params['filename']}", /* not currently used */
					$params['template_assignments'], /* comma-separated list of template names */
					$params['modx_category'],
                $params['lock_tv'],  /* value should be 1 or 0 */
                array_key_exists('installset', $params) ? preg_split("/\s*,\s*/", $params['installset']) : false
				);
			}
		}
		$d->close();
}

// setup chunks template files - array : name, description, type - 0:file or 1:content, file or content
$mc = &$moduleChunks;
if(is_dir($chunkPath) && is_readable($chunkPath)) {
		$d = dir($chunkPath);
		while (false !== ($tplfile = $d->read())) {
			if(substr($tplfile, -4) != '.tpl') {
				continue;
			}
			$params = parse_docblock($chunkPath, $tplfile);
			if(is_array($params) && count($params) > 0) {
			
				if($installMode===1 && compare_check($params)=='same') continue;
				
            $mc[] = array(
                $params['name'],
                $params['description'],
                "$chunkPath/{$params['filename']}",
                $params['modx_category'],
                array_key_exists('overwrite', $params) ? $params['overwrite'] : 'true',
                array_key_exists('installset', $params) ? preg_split("/\s*,\s*/", $params['installset']) : false
            );
			}
		}
		$d->close();
}

// setup snippets template files - array : name, description, type - 0:file or 1:content, file or content,properties
$ms = &$moduleSnippets;
if(is_dir($snippetPath) && is_readable($snippetPath)) {
		$d = dir($snippetPath);
		while (false !== ($tplfile = $d->read())) {
			if(substr($tplfile, -4) != '.tpl') {
				continue;
			}
			$params = parse_docblock($snippetPath, $tplfile);
			if(is_array($params) && count($params) > 0) {
				$description = empty($params['version']) ? $params['description'] : "<strong>{$params['version']}</strong> {$params['description']}";
				
				if($installMode===1 && compare_check($params)=='same') continue;
				
            $ms[] = array(
                $params['name'],
                $description,
                "$snippetPath/{$params['filename']}",
                $params['properties'],
                $params['modx_category'],
                array_key_exists('installset', $params) ? preg_split("/\s*,\s*/", $params['installset']) : false
            );
			}
		}
		$d->close();
}

// setup plugins template files - array : name, description, type - 0:file or 1:content, file or content,properties
$mp = &$modulePlugins;
if(is_dir($pluginPath) && is_readable($pluginPath))
{
	$d = dir($pluginPath);
	while (false !== ($tplfile = $d->read()))
	{
		if(substr($tplfile, -4) != '.tpl')
		{
			continue;
		}
		$params = parse_docblock($pluginPath, $tplfile);
		if(is_array($params) && 0 < count($params))
		{
		
			if(!empty($params['version'])) $description = "<strong>{$params['version']}</strong> {$params['description']}";
			else                           $description = $params['description'];
			
			if($installMode===1 && compare_check($params)=='same') continue;
		
			$mp[] = array(
				$params['name'],
				$description,
				"$pluginPath/{$params['filename']}",
				$params['properties'],
				$params['events'],
				$params['guid'],
				$params['modx_category'],
				$params['legacy_names'],
				array_key_exists('installset', $params) ? preg_split("/\s*,\s*/", $params['installset']) : false
			);
		}
	}
	$d->close();
}

// setup modules - array : name, description, type - 0:file or 1:content, file or content,properties, guid,enable_sharedparams
$mm = &$moduleModules;
if(is_dir($modulePath) && is_readable($modulePath)) {
		$d = dir($modulePath);
		while (false !== ($tplfile = $d->read())) {
			if(substr($tplfile, -4) != '.tpl') {
				continue;
			}
			$params = parse_docblock($modulePath, $tplfile);
			if(is_array($params) && count($params) > 0) {
				$description = empty($params['version']) ? $params['description'] : "<strong>{$params['version']}</strong> {$params['description']}";
				
				if($installMode===1 && compare_check($params)=='same') continue;
				
            $mm[] = array(
                $params['name'],
                $description,
                "$modulePath/{$params['filename']}",
                $params['properties'],
                $params['guid'],
                intval($params['shareparams']),
                $params['modx_category'],
                array_key_exists('installset', $params) ? preg_split("/\s*,\s*/", $params['installset']) : false
            );
			}
		}
		$d->close();
}

// setup callback function
$callBackFnc = "clean_up";
	
