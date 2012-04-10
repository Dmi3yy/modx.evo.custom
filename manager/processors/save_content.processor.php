<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('save_document')) {
	$e->setError(3);
	$e->dumpError();
}

fix_tv_nest('ta,introtext,pagetitle,longtitle,menutitle,description,alias,link_attributes');

// preprocess POST values
$id              = is_numeric($_POST['id']) ? $_POST['id'] : '';
$introtext       = $modx->db->escape($_POST['introtext']);
$content         = $modx->db->escape($_POST['ta']);
$pagetitle       = $modx->db->escape($_POST['pagetitle']);
$longtitle       = $modx->db->escape($_POST['longtitle']);
$menutitle       = $modx->db->escape($_POST['menutitle']);
$description     = $modx->db->escape($_POST['description']);
$alias           = $modx->db->escape($_POST['alias']);
$link_attributes = $modx->db->escape($_POST['link_attributes']);
$isfolder        = $_POST['isfolder'];
$richtext        = $_POST['richtext'];
$published       = $_POST['published'];
$parent          = $_POST['parent'] != '' ? $_POST['parent'] : 0;
$template        = $_POST['template'];
$menuindex       = !empty($_POST['menuindex']) ? $_POST['menuindex'] : 0;
$searchable      = $_POST['searchable'];
$cacheable       = $_POST['cacheable'];
$syncsite        = $_POST['syncsite'];
$pub_date        = $_POST['pub_date'];
$unpub_date      = $_POST['unpub_date'];
$document_groups = (isset($_POST['chkalldocs']) && $_POST['chkalldocs'] == 'on') ? array() : $_POST['docgroups'];
$type            = $_POST['type'];
$keywords        = $_POST['keywords'];
$metatags        = $_POST['metatags'];
$contentType     = $modx->db->escape($_POST['contentType']);
$contentdispo    = intval($_POST['content_dispo']);
$donthit         = intval($_POST['donthit']);
$hidemenu        = intval($_POST['hidemenu']);

if (trim($pagetitle) == '')
{
	if ($type == "reference") $pagetitle = $_lang['untitled_weblink'];
	else                      $pagetitle = $_lang['untitled_resource'];
}

// get table names
$tbl_document_groups            = $modx->getFullTableName('document_groups');
$tbl_documentgroup_names        = $modx->getFullTableName('documentgroup_names');
$tbl_member_groups              = $modx->getFullTableName('member_groups');
$tbl_membergroup_access         = $modx->getFullTableName('membergroup_access');
$tbl_keyword_xref               = $modx->getFullTableName('keyword_xref');
$tbl_site_content               = $modx->getFullTableName('site_content');
$tbl_site_content_metatags      = $modx->getFullTableName('site_content_metatags');
$tbl_site_tmplvar_contentvalues = $modx->getFullTableName('site_tmplvar_contentvalues');
$tbl_site_tmplvar_templates     = $modx->getFullTableName('site_tmplvar_templates');

switch($_POST['mode'])
{
	case '73':
	case '27':
		$actionToTake = 'edit';
		break;
	default:
		$actionToTake = 'new';
}

// friendly url alias checks
if ($modx->config['friendly_urls'])
{	// auto assign alias
	if (!$alias && $modx->config['automatic_alias'])
	{
		$alias = strtolower($modx->stripAlias(trim($pagetitle)));
		if(!$modx->config['allow_duplicate_alias'])
		{
			if(0 != $modx->db->getValue($modx->db->select('COUNT(id)',$tbl_site_content,"id<>'{$id}' AND alias='{$alias}'")))
			{
				$cnt = 1;
				$tempAlias = $alias;
				while(0 != $modx->db->getValue($modx->db->select('COUNT(id)',$tbl_site_content,"id<>'{$id}' AND alias='{$tempAlias}'")))
				{
					$tempAlias = $alias;
					$tempAlias .= $cnt;
					$cnt++;
				}
				$alias = $tempAlias;
			}
		}
	}
	// check for duplicate alias name if not allowed
	elseif ($alias && !$allow_duplicate_alias)
	{
		$alias = $modx->stripAlias($alias);
		if ($use_alias_path) {
			// only check for duplicates on the same level if alias_path is on
			$docid = $modx->db->getValue($modx->db->select('id',$tbl_site_content,"id<>'{$id}' AND alias='{$alias}' AND parent={$parent} LIMIT 1"));
		} else {
			$docid = $modx->db->getValue($modx->db->select('id',$tbl_site_content,"id<>'{$id}' AND alias='{$alias}' LIMIT 1"));
		}
		if ($docid > 0)
		{
			if ($actionToTake == 'edit')
			{
				$modx->manager->saveFormValues(27);
				$url = "index.php?a=27&id={$id}";
			}
			else
			{
				$modx->manager->saveFormValues(4);
				$url = 'index.php?a=4';
			}
			include_once "header.inc.php";
			$modx->webAlert(sprintf($_lang["duplicate_alias_found"], $docid, $alias), $url);
			include_once "footer.inc.php";
			exit;
		}
	}
	// strip alias of special characters
	elseif ($alias)
	{
		$alias = $modx->stripAlias($alias);
	}
}
elseif ($alias)
{
	$alias = $modx->stripAlias($alias);
}

// determine published status
$currentdate = time();

if(empty($pub_date))
{
	$pub_date = 0;
}
else
{
	$pub_date = $modx->toTimeStamp($pub_date);
	if(empty($pub_date))
	{
		$modx->manager->saveFormValues(27);
		$url = "index.php?a=27&id={$id}";
		include_once "header.inc.php";
		$modx->webAlert($_lang["mgrlog_dateinvalid"],$url);
		include_once "footer.inc.php";
		exit;
	}
	elseif($pub_date < $currentdate)
	{
		$published = 1;
	}
	elseif ($pub_date > $currentdate)
	{
		$published = 0;
	}
}

if(empty($unpub_date))
{
	$unpub_date = 0;
}
else
{
	$unpub_date = $modx->toTimeStamp($unpub_date);
	if(empty($unpub_date))
	{
		$modx->manager->saveFormValues(27);
		$url = "index.php?a=27&id={$id}";
		include_once "header.inc.php";
		$modx->webAlert($_lang["mgrlog_dateinvalid"],$url);
		include_once "footer.inc.php";
		exit;
	}
	elseif ($unpub_date < $currentdate)
	{
		$published = 0;
	}
}

// ensure that user has not made this document inaccessible to themselves
if($_SESSION['mgrRole'] != 1 && is_array($document_groups))
{
	$document_group_list = implode(',', $document_groups);
	$document_group_list = implode(',', array_filter(explode(',',$document_group_list), 'is_numeric'));
	if(!empty($document_group_list))
	{
		$from="{$tbl_membergroup_access} mga, {$tbl_member_groups} mg";
		$where = " mga.membergroup = mg.user_group AND mga.documentgroup IN({$document_group_list}) AND mg.member = {$_SESSION['mgrInternalKey']}";
		$count = $modx->db->getValue($modx->db->select('COUNT(mg.id)',$from,$where));
		if($count == 0)
		{
			if ($actionToTake == 'edit')
			{
				$modx->manager->saveFormValues(27);
				$url = "index.php?a=27&id={$id}";
				include_once "header.inc.php";
				$modx->webAlert(sprintf($_lang["resource_permissions_error"]), $url);
				include_once "footer.inc.php";
				exit;
			}
			else
			{
				$modx->manager->saveFormValues(4);
				$url = "index.php?a=4";
				include_once "header.inc.php";
				$modx->webAlert(sprintf($_lang["resource_permissions_error"]), $url);
				include_once "footer.inc.php";
				exit;
			}
		}
	}
}

// get the document, but only if it already exists
if ($actionToTake != "new") {
	$rs = $modx->db->select('parent', $tbl_site_content, "id={$id}");
	$limit = $modx->db->getRecordCount($rs);
	if ($limit > 1)
	{
		$e->setError(6);
		$e->dumpError();
	}
	if ($limit < 1)
	{
		$e->setError(7);
		$e->dumpError();
	}
	$existingDocument = $modx->db->getRow($rs);
}

// check to see if the user is allowed to save the document in the place he wants to save it in
if ($use_udperms == 1)
{
	if ($existingDocument['parent'] != $parent)
	{
		include_once "./processors/user_documents_permissions.class.php";
		$udperms = new udperms();
		$udperms->user = $modx->getLoginUserID();
		$udperms->document = $parent;
		$udperms->role = $_SESSION['mgrRole'];

		if (!$udperms->checkPermissions())
		{
			if ($actionToTake == 'edit')
			{
				$modx->manager->saveFormValues(27);
				$url = "index.php?a=27&id={$id}";
				include_once "header.inc.php";
				$modx->webAlert(sprintf($_lang['access_permission_parent_denied'], $docid, $alias), $url);
				include_once "footer.inc.php";
				exit;
			}
			else
			{
				$modx->manager->saveFormValues(4);
				$url = "index.php?a=4";
				include_once "header.inc.php";
				$modx->webAlert(sprintf($_lang['access_permission_parent_denied'], $docid, $alias), $url);
				include_once "footer.inc.php";
				exit;
			}
		}
	}
}

switch ($actionToTake)
{
	case 'new' :

		// invoke OnBeforeDocFormSave event
		$modx->invokeEvent("OnBeforeDocFormSave", array (
			"mode" => "new",
			"id" => $id
		));
		
		// deny publishing if not permitted
		if (!$modx->hasPermission('publish_document')) {
			$pub_date = 0;
			$unpub_date = 0;
			$published = 0;
		}

		$publishedon = ($published ? time() : 0);
		$publishedby = ($published ? $modx->getLoginUserID() : 0);

		$field = array();
		$field['introtext']       = $introtext;
		$field['content']         = $content;
		$field['pagetitle']       = $pagetitle;
		$field['longtitle']       = $longtitle;
		$field['type']            = $type;
		$field['description']     = $description;
		$field['alias']           = $alias;
		$field['link_attributes'] = $link_attributes;
		$field['isfolder']        = $isfolder;
		$field['richtext']        = $richtext;
		$field['published']       = $published;
		$field['parent']          = $parent;
		$field['template']        = $template;
		$field['menuindex']       = $menuindex;
		$field['searchable']      = $searchable;
		$field['cacheable']       = $cacheable;
		$field['createdby']       = $modx->getLoginUserID();
		$field['createdon']       = time();
		$field['editedby']        = $modx->getLoginUserID();
		$field['editedon']        = time();
		$field['publishedby']     = $publishedby;
		$field['publishedon']     = $publishedon;
		$field['pub_date']        = $pub_date;
		$field['unpub_date']      = $unpub_date;
		$field['contentType']     = $contentType;
		$field['content_dispo']   = $contentdispo;
		$field['donthit']         = $donthit;
		$field['menutitle']       = $menutitle;
		$field['hidemenu']        = $hidemenu;
		$rs = $modx->db->insert($field,$tbl_site_content);
		if(!$rs)
		{
			$modx->manager->saveFormValues(27);
			echo "An error occured while attempting to save the new document: " . $modx->db->getLastError();
			exit;
		}
		if (!$key = $modx->db->getInsertId())
		{
			$modx->manager->saveFormValues(27);
			echo "Couldn't get last insert key!";
			exit;
		}
		
		$tmplvars = get_tmplvars();
		
		$tvChanges = array();
		$field = '';
		foreach ($tmplvars as $field => $value)
		{
			if (is_array($value)) {
				$tvId = $value[0];
				$tvVal = $value[1];
				$tvChanges[] = array('tmplvarid' => $tvId, 'contentid' => $key, 'value' => $modx->db->escape($tvVal));
			}
		}
		if(!empty($tvChanges))
		{
			foreach ($tvChanges as $tv)
			{
				$rs = $modx->db->insert($tv, $tbl_site_tmplvar_contentvalues);
			}
		}

		// document access permissions
		$docgrp_save_attempt = false;
		if ($use_udperms == 1 && is_array($document_groups))
		{
			$new_groups = array();
			foreach ($document_groups as $value_pair)
			{
				// first, split the pair (this is a new document, so ignore the second value
				$group = intval(substr($value_pair,0,strpos($value_pair,',')));
				// @see manager/actions/mutate_content.dynamic.php @ line 1138 (permissions list)
				$new_groups[] = "({$group},{$key})";
			}
			$saved = true;
			if (!empty($new_groups))
			{
				$sql = 'INSERT INTO '.$tbl_document_groups.' (document_group, document) VALUES '. implode(',', $new_groups);
				$saved = $modx->db->query($sql) ? $saved : false;
				$docgrp_save_attempt = true;
			}
		}
		else
		{
			$isManager = $modx->hasPermission('access_permissions');
			$isWeb     = $modx->hasPermission('web_access_permissions');
			if($use_udperms && !($isManager || $isWeb) && $parent != 0)
			{
				// inherit document access permissions
				$sql = "INSERT INTO {$tbl_document_groups} (document_group, document) SELECT document_group, {$key} FROM {$tbl_document_groups} WHERE document = {$parent}";
				$saved = $modx->db->query($sql);
				$docgrp_save_attempt = true;
			}
		}
		if ($docgrp_save_attempt && !$saved)
		{
			$modx->manager->saveFormValues(27);
			echo "An error occured while attempting to add the document to a document_group.";
			exit;
		}


		// update parent folder status
		if ($parent != 0)
		{
			$rs = $modx->db->update('isfolder = 1', $tbl_site_content, 'id='.$_REQUEST['parent']);
			if (!$rs)
			{
				echo "An error occured while attempting to change the document's parent to a folder.";
			}
		}

		// save META Keywords
		saveMETAKeywords($key);

		// invoke OnDocFormSave event
		$header=''; // Redirect header
		$modx->invokeEvent("OnDocFormSave", array (
			"mode" => "new",
			"id" => $key
		));

		// secure web documents - flag as private
		include "{$base_path}manager/includes/secure_web_documents.inc.php";
		secureWebDocument($key);

		// secure manager documents - flag as private
		include "{$base_path}manager/includes/secure_mgr_documents.inc.php";
		secureMgrDocument($key);

		if($syncsite == 1) $modx->clearCache();

		// redirect/stay options
		if ( empty($header) )
		{
			if ($_POST['stay'] != '')
			{
				if ($_POST['mode'] == "72") // weblink
				{
					$a = ($_POST['stay'] == '2') ? "27&id={$key}" : "72&pid={$parent}";
				}
				elseif ($_POST['mode'] == "4") // document
				{
					$a = ($_POST['stay'] == '2') ? "27&id={$key}" : "4&pid={$parent}";
				}
				$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
			}
			else
			{
				if($parent!=='0')
				{
					$header = "Location: index.php?a=3&id={$parent}&tab=0&r=1";
				}
				else
				{
					$header = "Location: index.php?a=3&id={$key}&r=1";
				}
			}
		}
		header($header);
		exit;
		break;
	case 'edit' :

		// get the document's current parent
		$rs = $modx->db->select('parent', $tbl_site_content, 'id='.$_REQUEST['id']);
		if (!$rs) {
			$modx->manager->saveFormValues(27);
			echo "An error occured while attempting to find the document's current parent.";
			exit;
		}
		
		$row = $modx->db->getRow($rs);
		$oldparent = $row['parent'];
		$doctype = $row['type'];

		$url = "index.php?a=27&id={$id}";
		if ($id == $site_start && $published == 0)
		{
			$modx->manager->saveFormValues(27);
			include_once "header.inc.php";
			$modx->webAlert('Document is linked to site_start variable and cannot be unpublished!',$url);
			include_once "footer.inc.php";
			exit;
		}
		$today= time();
		if ($id == $site_start && ($pub_date > $today || $unpub_date != "0"))
		{
			$modx->manager->saveFormValues(27);
			include_once "header.inc.php";
			$modx->webAlert('Document is linked to site_start variable and cannot have publish or unpublish dates set!',$url);
			include_once "footer.inc.php";
			exit;
		}
		if ($parent == $id)
		{
			$modx->manager->saveFormValues(27);
			include_once "header.inc.php";
			$modx->webAlert("Document can not be it's own parent!",$url);
			include_once "footer.inc.php";
			exit;
		}
		// check to see document is a folder
		$rs = $modx->db->select('COUNT(id)', $tbl_site_content, 'parent='. $_REQUEST['id']);
		if (!$rs)
		{
			$modx->manager->saveFormValues(27);
			include_once "header.inc.php";
			$modx->webAlert("An error occured while attempting to find the document's children.",$url);
			include_once "footer.inc.php";
			exit;
		}
		$row = $modx->db->getRow($rs);
		if ($row['COUNT(id)'] > 0)
		{
			$isfolder = '1';
		}

		// set publishedon and publishedby
		$was = $modx->db->getRow($modx->db->select('published,pub_date,unpub_date,publishedon,publishedby,alias', $tbl_site_content, "id='{$id}'"));

		// keep original publish state, if change is not permitted
		if (!$modx->hasPermission('publish_document'))
		{
			$published  = $was['published'];
			$pub_date   = $was['pub_date'];
			$unpub_date = $was['unpub_date'];
		}
		else
		{
			// if it was changed from unpublished to published
			if(!empty($pub_date) && $pub_date<=time() && $published)
			{
				$publishedon = $pub_date;
				$publishedby = $was['publishedby'];
			}
			elseif (0<$was['publishedon'] && $published)
			{
				$publishedon = $was['publishedon'];
				$publishedby = $was['publishedby'];
			}
			elseif(!$published)
			{
				$publishedon = 0;
				$publishedby = 0;
			}
			else
			{
				$publishedon = time();
				$publishedby = $modx->getLoginUserID();
			}
		}
		
		// invoke OnBeforeDocFormSave event
		$modx->invokeEvent("OnBeforeDocFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		// update the document
		$fields = array();
		$fields['introtext']       = $introtext;
		$fields['content']         = $content;
		$fields['pagetitle']       = $pagetitle;
		$fields['longtitle']       = $longtitle;
		$fields['type']            = $type;
		$fields['description']     = $description;
		$fields['alias']           = $alias;
		$fields['link_attributes'] = $link_attributes;
		$fields['isfolder']        = $isfolder;
		$fields['richtext']        = $richtext;
		$fields['published']       = $published;
		$fields['pub_date']        = $pub_date;
		$fields['unpub_date']      = $unpub_date;
		$fields['parent']          = $parent;
		$fields['template']        = $template;
		$fields['menuindex']       = $menuindex;
		$fields['searchable']      = $searchable;
		$fields['cacheable']       = $cacheable;
		$fields['editedby']        = $modx->getLoginUserID();
		$fields['editedon']        = time();
		$fields['publishedon']     = $publishedon;
		$fields['publishedby']     = $publishedby;
		$fields['contentType']     = $contentType;
		$fields['content_dispo']   = $contentdispo;
		$fields['donthit']         = $donthit;
		$fields['menutitle']       = $menutitle;
		$fields['hidemenu']        = $hidemenu;
		$rs = $modx->db->update($fields,$tbl_site_content,"id='{$id}'");
		if (!$rs)
		{
			echo "An error occured while attempting to save the edited document. The generated SQL is: <i> {$sql} </i>.";
		}
		
		// update template variables
		$tmplvars = get_tmplvars();
		$rs = $modx->db->select('id, tmplvarid', $tbl_site_tmplvar_contentvalues, "contentid={$id}");
		$tvIds = array ();
		while ($row = $modx->db->getRow($rs))
		{
			$tvIds[$row['tmplvarid']] = $row['id'];
		}
		$tvDeletions = array();
		$tvChanges = array();
		foreach ($tmplvars as $field => $value)
		{
			if (!is_array($value))
			{
				if (isset($tvIds[$value])) $tvDeletions[] = $tvIds[$value];
			}
			else
			{
				$tvId = $value[0];
				$tvVal = $value[1];

				if (isset($tvIds[$tvId]))
				{
					$tvChanges[] = array(array('tmplvarid' => $tvId, 'contentid' => $id, 'value' => $modx->db->escape($tvVal)), array('id' => $tvIds[$tvId]));
				}
				else
				{
					$tvAdded[] = array('tmplvarid' => $tvId, 'contentid' => $id, 'value' => $modx->db->escape($tvVal));
				}
			}
		}

		if (!empty($tvDeletions))
		{
			$where = 'id IN('.implode(',', $tvDeletions).')';
			$rs = $modx->db->delete($tbl_site_tmplvar_contentvalues, $where);
		}
			
		if (!empty($tvAdded))
		{
			foreach ($tvAdded as $tv)
			{
				$rs = $modx->db->insert($tv, $tbl_site_tmplvar_contentvalues);
			}
		}
		
		if (!empty($tvChanges))
		{
			foreach ($tvChanges as $tv)
			{
				$rs = $modx->db->update($tv[0], $tbl_site_tmplvar_contentvalues, 'id='.$tv[1]['id']);
			}
		}

		// set document permissions
		if ($use_udperms == 1 && is_array($document_groups))
		{
			$new_groups = array();
			// process the new input
			foreach ($document_groups as $value_pair)
			{
				list($group, $link_id) = explode(',', $value_pair); // @see manager/actions/mutate_content.dynamic.php @ line 1138 (permissions list)
				$new_groups[$group] = $link_id;
			}

			// grab the current set of permissions on this document the user can access
			$isManager = intval($modx->hasPermission('access_permissions'));
			$isWeb     = intval($modx->hasPermission('web_access_permissions'));
			$fields = 'groups.id, groups.document_group';
			$from   = "{$tbl_document_groups} AS groups LEFT JOIN {$tbl_documentgroup_names} AS dgn ON dgn.id = groups.document_group";
			$where  = "((1={$isManager} AND dgn.private_memgroup) OR (1={$isWeb} AND dgn.private_webgroup)) AND groups.document = {$id}";
			$rs = $modx->db->select($fields,$from,$where);
			$old_groups = array();
			while ($row = $modx->db->getRow($rs))
			{
				$old_groups[$row['document_group']] = $row['id'];
			}
			// update the permissions in the database
			$insertions = $deletions = array();
			foreach ($new_groups as $group => $link_id)
			{
				$group = intval($group);
				if (array_key_exists($group, $old_groups))
				{
					unset($old_groups[$group]);
					continue;
				}
				elseif ($link_id == 'new')
				{
					$insertions[] = "({$group},{$id})";
				}
			}
			$saved = true;
			if (!empty($insertions))
			{
				$sql_insert = 'INSERT INTO '.$tbl_document_groups.' (document_group, document) VALUES '.implode(',', $insertions);
				$saved = $modx->db->query($sql_insert) ? $saved : false;
			}
			if (!empty($old_groups))
			{
				$where = 'id IN (' . implode(',', $old_groups) . ')';
				$saved = $modx->db->delete($tbl_document_groups,$where) ? $saved : false;
			}
			// necessary to remove all permissions as document is public
			if ((isset($_POST['chkalldocs']) && $_POST['chkalldocs'] == 'on'))
			{
				$sql_delete = "DELETE FROM {$tbl_document_groups}";
				$where = "document={$id}";
				$saved = $modx->db->delete($tbl_document_groups,$where) ? $saved : false;
			}
			if (!$saved)
			{
				$modx->manager->saveFormValues(27);
				echo "An error occured while saving document groups.";
				exit;
			}
		}

		// do the parent stuff
		if ($parent != 0)
		{
			$rs = $modx->db->update('isfolder = 1', $tbl_site_content, "id={$_REQUEST['parent']}");
			if (!$rs)
			{
				echo "An error occured while attempting to change the new parent to a folder.";
			}
		}

		// finished moving the document, now check to see if the old_parent should no longer be a folder
		$rs = $modx->db->select('COUNT(id)', $tbl_site_content, "parent={$oldparent}");
		if (!$rs)
		{
			echo "An error occured while attempting to find the old parents' children.";
		}
		$row = $modx->db->getRow($rs);
		$limit = $row['COUNT(id)'];

		if ($limit == 0)
		{
			$rs = $modx->db->update('isfolder = 0', $tbl_site_content, "id={$oldparent}");
			if (!$rs)
			{
				echo "An error occured while attempting to change the old parent to a regular document.";
			}
		}

		// save META Keywords
		saveMETAKeywords($id);

		// invoke OnDocFormSave event
		$header=''; // Redirect header
		$modx->invokeEvent("OnDocFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		// secure web documents - flag as private
		include "{$base_path}manager/includes/secure_web_documents.inc.php";
		secureWebDocument($id);

		// secure manager documents - flag as private
		include "{$base_path}manager/includes/secure_mgr_documents.inc.php";
		secureMgrDocument($id);

		if($published  != $was['published'])    $clearcache['target'] = 'pagecache,sitecache';
		elseif($was['alias']==$fields['alias']) $clearcache['target'] = 'pagecache';
		else                                    $clearcache['target'] = 'pagecache,sitecache';
		if ($syncsite == 1)
		{
			$modx->clearCache($clearcache);
		}
		
		if ( empty($header) )
		{
			if ($_POST['refresh_preview'] == '1')
			{
				$header = "Location: ../index.php?id={$id}&z=manprev";
			}
			else
			{
				if ($_POST['stay'] != '')
				{
					$id = $_REQUEST['id'];
					if ($type == "reference")
					{
						// weblink
						$a = ($_POST['stay'] == '2') ? "27&id={$id}" : "72&pid={$parent}";
					}
					else
					{
						// document
						$a = ($_POST['stay'] == '2') ? "27&id={$id}" : "4&pid={$parent}";
					}
					$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
				}
				elseif($isfolder==='1' && $parent!=='0')
				{
					$header = "Location: index.php?a=3&id={$parent}&tab=0&r=1";
				}
				elseif($isfolder==='1' && $parent==='0')
				{
					$header = "Location: index.php?a=3&id={$id}&tab=0&r=1";
				}
				elseif($isfolder==='0' && $parent!=='0')
				{
					$header = "Location: index.php?a=3&id={$parent}&r=1&tab=0";
				}
				else
				{
					$header = "Location: index.php?a=3&id={$id}&r=1";
				}
			}
		}
		header($header);
		exit;
	default :
		header("Location: index.php?a=7");
		exit;
}

// -- Save META Keywords --
function saveMETAKeywords($id) {
	global $modx, $keywords, $metatags,$tbl_keyword_xref,$tbl_site_content,$tbl_site_content_metatags;
	
	if ($modx->hasPermission('edit_doc_metatags'))
	{
		// keywords - remove old keywords first
		$modx->db->delete($tbl_keyword_xref, "content_id={$id}");
		for ($i = 0; $i < count($keywords); $i++) {
			$kwid = $keywords[$i];
			$flds = array (
				'content_id' => $id,
				'keyword_id' => $kwid
			);
			$modx->db->insert($flds, $tbl_keyword_xref);
		}
		// meta tags - remove old tags first
		$modx->db->delete($tbl_site_content_metatags, "content_id={$id}");
		for ($i = 0; $i < count($metatags); $i++) {
			$kwid = $metatags[$i];
			$flds = array (
				'content_id' => $id,
				'metatag_id' => $kwid
			);
			$modx->db->insert($flds, $tbl_site_content_metatags);
		}
		$flds = array (
			'haskeywords' => (count($keywords) ? 1 : 0),
			'hasmetatags' => (count($metatags) ? 1 : 0)
		);
		$modx->db->update($flds, $tbl_site_content, "id={$id}");
	}
}

function get_tmplvars()
{
	global $modx;
	
	$tbl_site_tmplvars              = $modx->getFullTableName('site_tmplvars');
	$tbl_site_tmplvar_contentvalues =  $modx->getFullTableName('site_tmplvar_contentvalues');
	$tbl_site_tmplvar_access        = $modx->getFullTableName('site_tmplvar_access');
	$tbl_site_tmplvar_templates     = $modx->getFullTableName('site_tmplvar_templates');
	$template = $_POST['template'];
	$id       = is_numeric($_POST['id']) ? $_POST['id'] : '';
	
	// get document groups for current user
	if ($_SESSION['mgrDocgroups'])
	{
		$docgrp = implode(',', $_SESSION['mgrDocgroups']);
	}
	
	$field = "DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value";
	$from = "{$tbl_site_tmplvars} AS tv ";
	$from .= "INNER JOIN {$tbl_site_tmplvar_templates} AS tvtpl ON tvtpl.tmplvarid = tv.id ";
	$from .= "LEFT JOIN {$tbl_site_tmplvar_contentvalues} AS tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '{$id}' ";
	$from .= "LEFT JOIN {$tbl_site_tmplvar_access} tva ON tva.tmplvarid=tv.id  ";
	$tva_docgrp = ($docgrp) ? "OR tva.documentgroup IN ({$docgrp})" : '';
	$where = "tvtpl.templateid = '{$template}' AND (1='{$_SESSION['mgrRole']}' OR ISNULL(tva.documentgroup) {$tva_docgrp})";
	$orderby = 'tv.rank';
	$rs = $modx->db->select($field,$from,$where,$orderby);
	
	$tmplvars = array ();
	while ($row = $modx->db->getRow($rs))
	{
		$tmplvar = '';
		$tvid = "tv{$row['id']}";
		if(!isset($_POST[$tvid]))
		{
			if($row['type']!=='checkbox' && $row['type']!=='listbox-multiple')
			{
				continue;
			}
		}
		switch ($row['type'])
		{
			case 'url':
				$tmplvar = $_POST[$tvid];
				if($_POST["{$tvid}_prefix"] != '--')
				{
					$tmplvar = str_replace(array ('feed://','ftp://','http://','https://','mailto:'), '', $tmplvar);
					$tmplvar = $_POST["{$tvid}_prefix"] . $tmplvar;
				}
				break;
			case 'file':
				$tmplvar = $_POST[$tvid];
				break;
			default:
				if(is_array($_POST[$tvid]))
				{
					// handles checkboxes & multiple selects elements
					$feature_insert = array ();
					$lst = $_POST[$tvid];
					foreach($lst as $v)
					{
						$feature_insert[count($feature_insert)] = $v;
					}
					$tmplvar = implode('||', $feature_insert);
				}
				else
				{
					$tmplvar = $_POST[$tvid];
				}
		}
		// save value if it was modified
		if (strlen($tmplvar) > 0 && $tmplvar != $row['default_text'])
		{
			$tmplvars[$row['id']] = array (
				$row['id'],
				$tmplvar
			);
		}
		else
		{
			// Mark the variable for deletion
			$tmplvars[$row['name']] = $row['id'];
		}
	}
	return $tmplvars;
}

function fix_tv_nest($target)
{
	foreach(explode(',',$target) as $name)
	{
		$tv = ($name !== 'ta') ? $name : 'content';
		$s = "[*{$tv}*]";
		$r = "[ *{$tv}* ]";
		$_POST[$name] = str_replace($s,$r,$_POST[$name]);
	}
}
