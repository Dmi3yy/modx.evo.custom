<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_template')) {
	$e->setError(3);
	$e->dumpError();
}

$id = intval($_POST['id']);
$name = $modx->db->escape(trim($_POST['name']));
$description = $modx->db->escape($_POST['description']);
$caption = $modx->db->escape($_POST['caption']);
$type = $modx->db->escape($_POST['type']);
$elements = $modx->db->escape($_POST['elements']);
$default_text = $modx->db->escape($_POST['default_text']);
$rank = isset ($_POST['rank']) ? $modx->db->escape($_POST['rank']) : 0;
$display = $modx->db->escape($_POST['display']);
$params = $modx->db->escape($_POST['params']);
$locked = $_POST['locked']=='on' ? 1 : 0 ;

$tbl_site_tmplvars = $modx->getFullTableName('site_tmplvars');

//Kyle Jaebker - added category support
if(empty($_POST['newcategory']) && $_POST['categoryid'] > 0)
{
	$categoryid = $modx->db->escape($_POST['categoryid']);
}
elseif(empty($_POST['newcategory']) && $_POST['categoryid'] <= 0)
{
	$categoryid = 0;
}
else
{
	include_once "categories.inc.php";
	$catCheck = checkCategory($modx->db->escape($_POST['newcategory']));
	if ($catCheck)
	{
		$categoryid = $catCheck;
	}
	else
	{
		$categoryid = newCategory($_POST['newcategory']);
	}
}

if($name=='')     $name = 'Untitled variable';
if($caption =='') $caption  = $name;
switch ($_POST['mode'])
{
    case '300':
		// invoke OnBeforeTVFormSave event
		$modx->invokeEvent("OnBeforeTVFormSave",
								array(
									"mode"	=> "new",
									"id"	=> $id
							));
		if(check_exist_name($name)!==false)
		{
			$url = "index.php?a=300";
			$msg = sprintf($_lang['duplicate_name_found_general'], $_lang['tv'], $name);
			$modx->manager->saveFormValues(300);
			include_once "header.inc.php";
			$modx->webAlert($msg, $url);
			include_once "footer.inc.php";
			exit;
		}
		if(check_reserved_names($name)!==false)
		{
			$url = "index.php?a=300";
			$msg = sprintf($_lang['reserved_name_warning'], $name);
			$modx->manager->saveFormValues(300);
			include_once "header.inc.php";
			$modx->webAlert($msg, $url);
			include_once "footer.inc.php";
			exit;
		}
		
		// Add new TV
		$field = array();
		$field['name'] = $name;
		$field['description'] = $description;
		$field['caption'] = $caption;
		$field['type'] = $type;
		$field['elements'] = $elements;
		$field['default_text'] = $default_text;
		$field['display'] = $display;
		$field['display_params'] = $params;
		$field['rank'] = $rank;
		$field['locked'] = $locked;
		$field['category'] = $categoryid;
		$rs = $modx->db->insert($field,$tbl_site_tmplvars);
		if(!$rs)
		{
			echo "\$rs not set! New variable not saved!";
		}
		else
		{
			// get the id
			$newid = $modx->db->getInsertId();
			if(!$newid)
			{
				echo "Couldn't get last insert key!";
				exit;
			}

			// save access permissions
			saveTemplateAccess();
			saveDocumentAccessPermissons();

			// invoke OnTVFormSave event
			$modx->invokeEvent("OnTVFormSave",
									array(
										"mode"	=> "new",
										"id"	=> $newid
								));

			// empty cache
			$modx->clearCache(); // first empty the cache
			// finished emptying cache - redirect
			if($_POST['stay']!='')
			{
				$a = ($_POST['stay']!='0') ? "301&id={$newid}":"300";
				$header="Location: index.php?a={$a}&stay={$_POST['stay']}";
			}
			else
			{
				$header="Location: index.php?a=76";
			}
			header($header);
		}
        break;
    case '301':
		// invoke OnBeforeTVFormSave event
		$modx->invokeEvent("OnBeforeTVFormSave",
								array(
									"mode"	=> "upd",
									"id"	=> $id
							));
		if(check_exist_name($name)!==false)
		{
			$url = "index.php?id={$id}&a=301";
			$msg = sprintf($_lang['duplicate_name_found_general'], $_lang['tv'], $name);
			$modx->manager->saveFormValues(301);
			include_once "header.inc.php";
			$modx->webAlert($msg, $url);
			include_once "footer.inc.php";
			exit;
		}
		if(check_reserved_names($name)!==false)
		{
			$url = "index.php?id={$id}&a=301";
			$msg = sprintf($_lang['reserved_name_warning'], $name);
			$modx->manager->saveFormValues(301);
			include_once "header.inc.php";
			$modx->webAlert($msg, $url);
			include_once "footer.inc.php";
			exit;
		}
    	// update TV
    	$was_name = $modx->db->getValue($modx->db->select('name',$tbl_site_tmplvars,"id='{$id}'"));
    	$reserve_name_replace = ($was_name!==$field['name']) ? true : false;
		$field = array();
		$field['name']           = $name;
		$field['description']    = $description;
		$field['caption']        = $caption;
		$field['type']           = $type;
		$field['elements']       = $elements;
		$field['default_text']   = $default_text;
		$field['display']        = $display;
		$field['display_params'] = $params;
		$field['rank']           = $rank;
		$field['locked']         = $locked;
		$field['category']       = $categoryid;
		$rs = $modx->db->update($field,$tbl_site_tmplvars,"id='{$id}'");
		if(!$rs)
		{
			echo "\$rs not set! Edited variable not saved!";
		}
		else
		{
			$modx->db->update("content=REPLACE(content,'[*{$was_name}*]','[*{$name}*]')",$modx->getFullTableName('site_content'));
			$modx->db->update("content=REPLACE(content,'[*{$was_name}*]','[*{$name}*]')",$modx->getFullTableName('site_templates'));
			$modx->db->update("snippet=REPLACE(snippet,'[*{$was_name}*]','[*{$name}*]')",$modx->getFullTableName('site_htmlsnippets'));
			$modx->db->update("content=REPLACE(content,'[*{$was_name}:','[*{$name}:')",  $modx->getFullTableName('site_content'));
			$modx->db->update("content=REPLACE(content,'[*{$was_name}:','[*{$name}:')",  $modx->getFullTableName('site_templates'));
			$modx->db->update("snippet=REPLACE(snippet,'[*{$was_name}:','[*{$name}:')",  $modx->getFullTableName('site_htmlsnippets'));
			// save access permissions
			saveTemplateAccess();
			saveDocumentAccessPermissons();
			// invoke OnTVFormSave event
			$modx->invokeEvent("OnTVFormSave",
									array(
										"mode"	=> "upd",
										"id"	=> $id
								));
			// empty cache
			$modx->clearCache(); // first empty the cache
			// finished emptying cache - redirect
			if($_POST['stay']!='')
			{
				$a = ($_POST['stay']!='0') ? "301&id={$id}":"300";
				$header="Location: index.php?a={$a}&stay={$_POST['stay']}";
			}
			else
			{
				$header = 'Location: index.php?a=76';
			}
			header($header);
		}
        break;
    default:
	?>
	Erm... You supposed to be here now?
	<?php
}

function saveTemplateAccess()
{
	global $id,$newid;
	global $modx;

	if($newid) $id = $newid;
	$templates =  $_POST['template']; // get muli-templates based on S.BRENNAN mod

	// update template selections
	$tbl_site_tmplvar_templates = $modx->getFullTableName('site_tmplvar_templates');

    $getRankArray = array();

    $getRank = $modx->db->select('templateid,rank', $tbl_site_tmplvar_templates, "tmplvarid={$id}");

	while($row = $modx->db->getRow($getRank))
	{
		$getRankArray[$row['templateid']] = $row['rank'];
	}
	$modx->db->delete($tbl_site_tmplvar_templates,"tmplvarid={$id}");
	for($i=0;$i<count($templates);$i++)
	{
		$setRank = ($getRankArray[$templates[$i]]) ? $getRankArray[$templates[$i]] : 0;
		$field = array();
		$field['tmplvarid']  = $id;
		$field['templateid'] = $templates[$i];
		$field['rank']       = $setRank;
		$modx->db->insert($field,$tbl_site_tmplvar_templates);
	}
}

function saveDocumentAccessPermissons()
{
	global $modx,$id,$newid,$use_udperms;
	
	$tbl_site_tmplvar_access = $modx->getFullTableName('site_tmplvar_access');

	if($newid) $id = $newid;
	$docgroups = $_POST['docgroups'];

	// check for permission update access
	if($use_udperms==1)
	{
		// delete old permissions on the tv
		$rs = $modx->db->delete($tbl_site_tmplvar_access,"tmplvarid={$id}");
		if(!$rs)
		{
			echo "An error occurred while attempting to delete previous template variable access permission entries.";
			exit;
		}
		if(is_array($docgroups))
		{
			foreach ($docgroups as $dgkey=>$value)
			{
				$field['tmplvarid'] = $id;
				$field['documentgroup'] = stripslashes($value);
				$rs = $modx->db->insert($field,$tbl_site_tmplvar_access);
				if(!$rs)
				{
					echo "An error occured while attempting to save template variable acess permissions.";
					exit;
				}
			}
		}
	}
}

function check_exist_name($name)
{	// disallow duplicate names for new tvs
	global $modx;
	$tbl_site_tmplvars = $modx->getFullTableName('site_tmplvars');
	$where = "name='{$name}'";
	if($_POST['mode']==301) {$where = $where . " AND id!={$_POST['id']}";}
	$rs = $modx->db->select('COUNT(id)',$tbl_site_tmplvars,$where);
	$count = $modx->db->getValue($rs);
	if($count > 0) return true;
	else           return false;
}

function check_reserved_names($name)
{	// disallow reserved names
	global $modx;
	
	$reserved_names = explode(',', 'id,type,contentType,pagetitle,longtitle,description,alias,link_attributes,published,pub_date,unpub_date,parent,isfolder,introtext,content,richtext,template,menuindex,searchable,cacheable,createdby,createdon,editedby,editedon,deleted,deletedon,deletedby,publishedon,publishedby,menutitle,donthit,haskeywords,hasmetatags,privateweb,privatemgr,content_dispo,hidemenu');
	if(in_array($name,$reserved_names))
	{
		$_POST['name'] = '';
		return true;
	}
	else return false;
}
