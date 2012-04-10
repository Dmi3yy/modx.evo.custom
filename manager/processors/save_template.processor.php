<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_template')) {	
	$e->setError(3);
	$e->dumpError();	
}
$id = intval($_POST['id']);
$template     = $modx->db->escape($_POST['post']);
$templatename = $modx->db->escape(trim($_POST['templatename']));
$description  = $modx->db->escape($_POST['description']);
$locked = $_POST['locked']=='on' ? 1 : 0 ;

$tbl_site_templates = $modx->getFullTableName('site_templates');

//Kyle Jaebker - added category support
if (empty($_POST['newcategory']) && $_POST['categoryid'] > 0) {
    $categoryid = $modx->db->escape($_POST['categoryid']);
} elseif (empty($_POST['newcategory']) && $_POST['categoryid'] <= 0) {
    $categoryid = 0;
} else {
    include_once "categories.inc.php";
    $catCheck = checkCategory($modx->db->escape($_POST['newcategory']));
    if ($catCheck) {
        $categoryid = $catCheck;
    } else {
        $categoryid = newCategory($_POST['newcategory']);
    }
}

if($templatename=="") $templatename = "Untitled template";

switch ($_POST['mode']) {
    case '19':
    
		// invoke OnBeforeTempFormSave event
		$modx->invokeEvent("OnBeforeTempFormSave",
								array(
									"mode"	=> "new",
									"id"	=> $id
							));	
							
		// disallow duplicate names for new templates
		$rs = $modx->db->select('COUNT(id)', $tbl_site_templates, "templatename = '{$templatename}'");
		$count = $modx->db->getValue($rs);
		if($count > 0)
		{
			$modx->event->alert(sprintf($_lang['duplicate_name_found_general'], $_lang['template'], $templatename));
			// prepare a few request/post variables for form redisplay...
			$_REQUEST['a'] = '19';
			$_POST['locked'] = isset($_POST['locked']) && $_POST['locked'] == 'on' ? 1 : 0;
			$_POST['category'] = $categoryid;
			$_GET['stay'] = $_POST['stay'];
			include 'header.inc.php';
			include(MODX_BASE_PATH.'manager/actions/mutate_templates.dynamic.php');
			include 'footer.inc.php';
			exit;
		}

		//do stuff to save the new doc
		$field = array();
		$field['templatename'] = $templatename;
		$field['description'] = $description;
		$field['content']      = $template;
		$field['locked']       = $locked;
		$field['category']     = $categoryid;
		$rs = $modx->db->insert($field,$tbl_site_templates);
		if(!$rs)
		{
			echo "\$rs not set! New template not saved!";
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

			// invoke OnTempFormSave event
			$modx->invokeEvent("OnTempFormSave",
									array(
										"mode"	=> "new",
										"id"	=> $newid
								));

			// empty cache
			$modx->clearCache();
			// finished emptying cache - redirect
			if($_POST['stay']!='')
			{
				$a = ($_POST['stay']=='2') ? "16&id=$newid":"19";
				$header="Location: index.php?a={$a}&stay={$_POST['stay']}";
			}
			else
			{
				$header="Location: index.php?a=76";
			}
			header($header);
		}
        break;
    case '16':

		// invoke OnBeforeTempFormSave event
		$modx->invokeEvent("OnBeforeTempFormSave",
								array(
									"mode"	=> "upd",
									"id"	=> $id
							));	   
		
		// disallow duplicate names for new templates
		$rs = $modx->db->select('COUNT(id)',$tbl_site_templates,"templatename = '{$templatename}' AND id != '{$id}'");
		$count = $modx->db->getValue($rs);
		if($count > 0)
		{
			$modx->event->alert(sprintf($_lang['duplicate_name_found_general'], $_lang['template'], $templatename));
			// prepare a few request/post variables for form redisplay...
			$_REQUEST['a'] = '16';
			$_POST['locked'] = isset($_POST['locked']) && $_POST['locked'] == 'on' ? 1 : 0;
			$_POST['category'] = $categoryid;
			$_GET['stay'] = $_POST['stay'];
			include 'header.inc.php';
			include(MODX_BASE_PATH . 'manager/actions/mutate_templates.dynamic.php');
			include 'footer.inc.php';
			exit;
		}
		
		//do stuff to save the edited doc
		$field = array();
		$field['templatename'] = $templatename;
		$field['description']  = $description;
		$field['content']      = $template;
		$field['locked']       = $locked;
		$field['category']     = $categoryid;
		$rs = $modx->db->update($field,$tbl_site_templates,"id='{$id}'");
		if(!$rs)
		{
			echo "\$rs not set! Edited template not saved!";
		}
		else
		{
			// invoke OnTempFormSave event
			$modx->invokeEvent("OnTempFormSave",
									array(
										"mode"	=> "upd",
										"id"	=> $id
								));	    		

			// first empty the cache
			$modx->clearCache();
			// finished emptying cache - redirect
			if($_POST['stay']!='')
			{
				$a = ($_POST['stay']=='2') ? "16&id=$id":"19";
				$header="Location: index.php?a={$a}&stay={$_POST['stay']}";
			}
			else
			{
				$header="Location: index.php?a=76";
			}
			header($header);
		}
        break;
    default:
	?>
	Erm... You supposed to be here now?
	<?php
}
