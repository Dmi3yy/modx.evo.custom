<?php
/*---------------------------------------------------------------------------
* GalleryManagement Class - Contains functions for: viewing, uploading, and
*                           editing product galleries.
*
* Add the following after session_name($site_sessionname); in config.inc.php

	if (isset($_REQUEST[$site_sessionname])) {
		session_id($_REQUEST[$site_sessionname]);
	}

* Some server configurations will require the following inside the .htaccess
* file within the manager directory/

	<IfModule mod_security.c>
	SecFilterEngine Off
	SecFilterScanPOST Off
	</IfModule>

*--------------------------------------------------------------------------*/
class GalleryManagement
{
	var $config;  // Array containing module configuration values

	/**
	* Class constructor, set configuration parameters
	*/
	function GalleryManagement($params)
	{
		global $modx;

		$this->config = $params;
		$this->config['urlPath'] = $modx->config['base_url'].rtrim($this->config['savePath'],'/');
		$this->config['savePath'] = $modx->config['base_path'].rtrim($this->config['savePath'],'/');

		$this->mainTemplate = 'template.html.tpl';
		$this->headerTemplate = 'header.html.tpl';
		$this->listingTemplate = 'gallery_listing.html.tpl';
		$this->uploadTemplate = 'gallery_upload.html.tpl';
		$this->editTemplate = 'image_edit.html.tpl';
		$this->galleryHeaderTemplate = 'gallery_header.html.tpl';

		$this->galleriesTable = 'portfolio_galleries';

		$this->current = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $modx->config['base_url'] . MGR_DIR.'/index.php';
		$this->a = $_GET['a'];
		$this->id = $_GET['id'];
		
		$this->loadLanguage();
	}

	/**
	* Determine what action was requested and process request
	*/
	function execute()
	{
		global $modx;

		$old_umask = umask(0);


		if (isset($_GET['edit']))
		{
			$tpl = $this->editImage();  // Display single image edit form
		}
		else
		{
			  // View/uplaod galleries and gallery images
			if (isset($_GET['onlygallery']))
				$output = $this->viewGallery();
			else
				$output = $this->viewListing();

			// Get contents of js script and replace necessary action URL
			$tplparams = array(
				'params' => '"id": "' . $this->id . '", "a": "' . $this->a . '", "' . session_name() . '": "' . session_id() . '"',
				'base_path' => $modx->config['base_url'] . 'assets/modules/evogallery/',
				'base_url' => $modx->config['base_url'],
				'content_id' => $content_id,
			);
			$js = $this->processTemplate('js.tpl', $tplparams);

    		$tplparams = array(
				'base_url' => $modx->config['base_url'],
				'content' => $output,
				'js' => $js
			);

			$tpl = $this->processTemplate($this->mainTemplate, $tplparams);
		}


		umask($old_umask);

		return $tpl;
	}

	/**
	* Edit an image's details
	*/
	function editImage()
	{
		global $modx;

		$this_page = $this->current . '?a=' . $this->a . '&amp;id=' . $this->id;

		$contentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];
		$url = $modx->config['base_url'].$this->config['savePath'];
		$id = isset($_GET['edit']) ? intval($_GET['edit']) : '';

		$result = $modx->db->select('id, filename, title, description, keywords', $modx->getFullTableName($this->galleriesTable), "id = '" . $id . "'");
		$info = $modx->fetchRow($result);

        /* Get keyword tags */
		$sql = "SELECT `keywords` FROM ".$modx->getFullTableName($this->galleriesTable);

		$keywords = $modx->dbQuery($sql);
		$all_docs = $modx->db->makeArray( $keywords );

		$foundTags = array();
		foreach ($all_docs as $theDoc) {
			$theTags = explode(",", $theDoc['keywords']);
			foreach ($theTags as $t) {
				$foundTags[trim($t)]++;
			}
		}

		// Sort the TV values (case insensitively)
		uksort($foundTags, 'strcasecmp');

		$lis = '';
		foreach($foundTags as $t=>$c) {
		    if($t != ''){
    			$lis .= '<li title="'.sprintf($this->lang['used_times'],$c).'">'.htmlentities($t, ENT_QUOTES, $modx->config['modx_charset'], false).($display_count?' ('.$c.')':'').'</li>';
		    }
		}

		$keyword_tagList = '<ul class="mmTagList" id="keyword_tagList">'.$lis.'</ul>';

		$tplparams = array(
			'action' => $this_page . '&action=view&content_id=' . $contentId . (isset($_GET['onlygallery'])?'&onlygallery=1':''),
			'id' => $info['id'],
			'filename' => urlencode($info['filename']),
			'image' => $this->config['urlPath'] .'/' .$contentId . '/thumbs/' . rawurlencode($info['filename']),
			'title' => $info['title'],
			'description' => $info['description'],
			'keywords' => $info['keywords'],
			'keyword_tagList' => $keyword_tagList
		);
				
		$tpl = $this->processTemplate($this->editTemplate, $tplparams);

		return $tpl;
	}

	/**
	* Display a searchable/sortable listing of documents
	*/
	function viewListing()
	{
		global $modx;

		$this_page = $this->current . '?a=' . $this->a . '&id=' . $this->id;

		$tplparams = array();

		$parentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];

		// Get search filter values
		$filter = '';
		if (isset($_GET['query']))
		{
			$search = $modx->db->escape($modx->stripTags($_GET['query']));
			$filter .= "WHERE (";
			$filter .= "c.pagetitle LIKE '%" . $search . "%' OR ";
			$filter .= "c.longtitle LIKE '%" . $search . "%' OR ";
			$filter .= "c.description LIKE '%" . $search . "%' OR ";
			$filter .= "c.introtext LIKE '%" . $search . "%' OR ";
			$filter .= "c.content LIKE '%" . $search . "%' OR ";
			$filter .= "c.alias LIKE '%" . $search . "%'";
			$filter .= ")";
			$header = $this->header($this->lang['search_results']);
		}
		else
		{
			$filter = "WHERE c.parent = '" . $parentId . "'";
			$header = $this->header();
		}

		$_GET['orderby'] = isset($_GET['orderby']) ? $_GET['orderby'] : 'c.menuindex';
		$_GET['orderdir'] = isset($_GET['orderdir']) ? $_GET['orderdir'] : 'ASC';

		// Check for number of records per page preferences and define global setting
		if (is_numeric($_GET['pageSize']))
		{
			setcookie("pageSize", $_GET['pageSize'], time() + 3600000);
			$maxPageSize = $_GET['pageSize'];
		}
		else
		{
			if (is_numeric($_COOKIE['pageSize']))
				$maxPageSize = $_COOKIE['pageSize'];
			else
				$maxPageSize = 100;
		}
		define('MAX_DISPLAY_RECORDS_NUM', $maxPageSize);

		$table = new MakeTable();  // Instantiate a new instance of the MakeTable class

		// Get document count
		$query = "SELECT COUNT(c.id) FROM " . $modx->getFullTableName('site_content') . " AS c " . $filter;
		$numRecords = $modx->db->getValue($query);

		// Execute the main table query with MakeTable sorting and paging features
		$query = "SELECT c.id, c.pagetitle, c.longtitle, c.editedon, c.isfolder, COUNT(g.id) as photos FROM " . $modx->getFullTableName('site_content') . " AS c " .
		         "LEFT JOIN " . $modx->getFullTableName($this->galleriesTable) . " AS g ON g.content_id = c.id " .
		         $filter . " GROUP BY c.id" . $table->handleSorting() . $table->handlePaging();

		if ($ds = $modx->db->query($query))
		{
			// If the query was successful, build our table array from the rows
			while ($row = $modx->db->getRow($ds))
			{
				$documents[] = array(
					'pagetitle' => '<a href="' . $this_page . '&action=view&content_id=' . $row['id'] . '" title="'.$this->lang['click_view_photos'].'">' . $row['pagetitle'] . ' (' . $row['id'] . ')</a>',
					'longtitle' => ($row['longtitle'] != '') ? stripslashes($row['longtitle']) : '-',
					'photos' => $row['photos'],
					'editedon' => ($row['editedon'] > 0) ? strftime('%m-%d-%Y', $row['editedon']) : '-',
				);
			}
		}

		if (is_array($documents))  // Ensure data was returned
		{
			// Create the table header definition with each header providing a link to sort by that field
			$documentTableHeader = array(
				'pagetitle' => $table->prepareOrderByLink('c.pagetitle', $this->lang['title']),
				'longtitle' => $table->prepareOrderByLink('c.longtitle', $this->lang['long_title']),
				'photos' => $table->prepareOrderByLink('photos', $this->lang['N_photos']),
				'editedon' => $table->prepareOrderByLink('c.editedon', $this->lang['last_edited']),
			);

			$table->setActionFieldName('id');  // Field passed in link urls

			// Table styling options
			$table->setTableClass('documentsTable');
			$table->setRowHeaderClass('headerRow');
			$table->setRowRegularClass('stdRow');
			$table->setRowAlternateClass('altRow');

			// Generate the paging navigation controls
			if ($numRecords > MAX_DISPLAY_RECORDS_NUM)
				$table->createPagingNavigation($numRecords);

			$table_html = $table->create($documents, $documentTableHeader);  // Generate documents table
			$table_html = str_replace('[~~]?', $this_page . '&action=view&', $table_html);  // Create page target
		}
		elseif (isset($_GET['query']))
		{
			$table_html = '<p>'.$this->lang['no_docs_found'].'</p>';  // No records were found
		}
		else
		{
			$table_html = '<p class="first">'.$this->lang['no_children'].'</p>';
		}

		$tplparams['table'] = $table_html;

		if (isset($_GET['query']))
			$tplparams['gallery'] = '';
		else
			$tplparams['gallery'] = $this->viewGallery();
		
		$tpl = $this->processTemplate($this->listingTemplate, $tplparams);
		return $header . $tpl;
	}

	/**
	* View/manage photos for a particular document
	*/
	function viewGallery()
	{
		global $modx;

		$this_page = $this->current . '?a=' . $this->a . '&id=' . $this->id;

		$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];  // Get document id

		// Verify session and retrieve document information
		$result = $modx->db->select('pagetitle, longtitle, parent', $modx->getFullTableName('site_content'), "id = '" . $content_id . "'");
		if ($modx->db->getRecordCount($result) > 0)
		{
			$info = $modx->fetchRow($result);

			if (!isset($_GET['onlygallery']))
			{
				$tplparams['title'] = $info['pagetitle'];
				if ($info['parent'] > 0)
					$tplparams['back_url'] = htmlentities($this_page . '&action=view&content_id=' . $info['parent']);
				else
					$tplparams['back_url'] = htmlentities($this_page . '&action=view');
				$galleryheader = $this->processTemplate($this->galleryHeaderTemplate, $tplparams);

				$target_dir = $this->config['savePath'] . '/' . $content_id . '/';
			} else
				$galleryheader = '<div id="content">';

			if (isset($_POST['cmdsort']) && isset($_POST['sort']))  // Update image sort order
			{
				$sortnum = 0; 
				foreach ($_POST['sort'] as $key => $id)
				{
					$sortnum++; 
					$id = intval($id);
					$modx->db->update("sortorder='" . $sortnum . "'", $modx->getFullTableName($this->galleriesTable), "id='" . $id . "'");
				}
			}
			elseif (isset($_GET['delete']))  // Delete requested image
			{
				$id = intval($_GET['delete']);
				$rs = $modx->db->select('filename', $modx->getFullTableName($this->galleriesTable), "id='" . $id . "'");
                if ($modx->db->getRecordCount($result) > 0)
				{
					$filename = $modx->db->getValue($rs);

					if (file_exists($target_dir . 'thumbs/' . $filename))
						unlink($target_dir . 'thumbs/' . $filename);
					if (file_exists($target_dir . 'original/' . $filename))
						unlink($target_dir . 'original/' . $filename);
					if (file_exists($target_dir . $filename))
						unlink($target_dir . $filename);

					// Remove record from database
					$modx->db->delete($modx->getFullTableName($this->galleriesTable), "id='" . $id . "'");
				}
			}
			elseif (isset($_POST['edit']))  // Update image information
			{
				$fields['title'] = isset($_POST['title']) ? addslashes($_POST['title']) : '';
				$fields['description'] = isset($_POST['description']) ? addslashes($_POST['description']) : '';
				$fields['keywords'] = isset($_POST['keywords']) ? addslashes($_POST['keywords']) : '';
				$modx->db->update($fields, $modx->getFullTableName($this->galleriesTable), "id='" . intval($_POST['edit']) . "'");
			}

			// Get contents of upload script and replace necessary action URL
			$tplparams = array(
				'self' => urlencode(html_entity_decode($this_page . '&content_id=' . $content_id)),
				'action' => $this->current,
				'params' => '"id": "' . $this->id . '", "a": "' . $this->a . '", "' . session_name() . '": "' . session_id() . '"',
				'uploadparams' => '"action": "upload", "js": "1", "content_id": "' . $content_id . '"',
				'base_path' => $modx->config['base_url'] . 'assets/modules/evogallery/',
				'base_url' => $modx->config['base_url'],
				'content_id' => $content_id,
				'thumbs' => $this->config['urlPath'] . '/' . $content_id . '/thumbs/',
				'upload_maxsize' => $modx->config['upload_maxsize']
			);

			$upload_script = $this->processTemplate('upload.js.tpl', $tplparams);

			$tplparams = array(
				'title' => stripslashes($info['pagetitle']),
				'upload_script' => $upload_script
			);


			// Read through project files directory and show thumbs
			$thumbs = '';
			$result = $modx->db->select('id, filename, title, description, keywords', $modx->getFullTableName($this->galleriesTable), 'content_id=' . $content_id, 'sortorder ASC');
			while ($row = $modx->fetchRow($result))
			{
				$thumbs .= "<li><div class=\"thbSelect\"><a class=\"select\" href=\"#\">".$this->lang['select']."</a></div><div class=\"thbButtons\"><a href=\"" . $this_page . "&action=edit&content_id=$content_id&edit=" . $row['id'] . (isset($_GET['onlygallery'])?"&onlygallery=1":"") ."\" class=\"edit\">".$this->lang['edit']."</a><a href=\"$this_page&action=view&content_id=$content_id&delete=" . $row['id'] . "\" class=\"delete\">".$this->lang['delete']."</a></div><img src=\"" . $this->config['urlPath'] . '/' . $content_id . '/thumbs/' . rawurlencode($row['filename']) . "\" alt=\"" . htmlentities(stripslashes($row['filename'])) . "\" class=\"thb\" /><input type=\"hidden\" name=\"sort[]\" value=\"" . $row['id'] . "\" /></li>\n";
			}

			$tplparams['gallery_header'] = $galleryheader;
			$tplparams['action'] = $this_page . '&action=view&content_id=' . $content_id . (isset($_GET['onlygallery'])?'&onlygallery=1':'');
			$tplparams['thumbs'] = $thumbs;

			$tpl = $this->processTemplate($this->uploadTemplate, $tplparams);

			return $tpl;
		}
	}

	/**
	* Display management header
	*/
	function header($title = '')
	{
		global $modx;

		$this_page = $this->current . '?a=' . $this->a . '&id=' . $this->id;

		$parentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : $this->config['docId'];

		if (isset($_GET['query']))
			$search = '<label for="query">'.$this->lang['search'].':</label> <input type="text" name="query" id="query" value="' . $_GET['query'] . '" />';
		else
			$search = '<label for="query">'.$this->lang['search'].':</label> <input type="text" name="query" id="query" />';

		// Generate breadcrumbs
		$result = $modx->db->select('id, pagetitle, parent', $modx->getFullTableName('site_content'), 'id=' . $parentId);
		$row = $modx->fetchRow($result);
		$breadcrumbs = '<a href="' . $this_page . '&action=view&content_id=' . $row['id'] . '" title="'.$this->lang['click_view_categories'].'">' . stripslashes($row['pagetitle']) . '</a>';
		while ($row['id'] > $this->config['docId'])
		{
			$row = $modx->fetchRow($modx->db->select('id, pagetitle, parent', $modx->getFullTableName('site_content'), 'id=' . $row['parent']));
			$breadcrumbs = '<a href="' . $this_page . '&action=view&content_id=' . $row['id'] . '" title="'.$this->lang['click_view_categories'].'">' . stripslashes($row['pagetitle']) . '</a> &raquo; ' . $breadcrumbs;
		}

		$tplparams = array(
			'breadcrumbs' => $breadcrumbs,
			'search' => $search,
			'action' => $this_page,
			'a' => $this->a,
			'id' => $this->id
		);

		if ($title == '')
			$tplparams['title'] = '';
		else
			$tplparams['title'] = '<h2>' . $title . '</h2>';

		$tpl = $this->processTemplate($this->headerTemplate, $tplparams);

		return $tpl;
	}

	/**
	* Resize a given image
	*/
	function resizeImage($filename, $target, $params)
	{
		global $modx;
		
		if (!class_exists('phpthumb'))
		{
			include 'classes/phpthumb/phpthumb.class.php';
			include 'classes/phpthumb/phpThumb.config.php';
		}
		
		$phpthumb = new phpThumb();
			
		if (!empty($PHPTHUMB_CONFIG))
		{
			foreach ($PHPTHUMB_CONFIG as $key => $value)
			{
				$keyname = 'config_'.$key;
				$phpthumb->setParameter($keyname, $value);
			}
		}
		//Set output format as input or jpeg if not supperted
		$ext = strtolower(substr(strrchr($filename, '.'), 1));
		if (in_array($ext,array('jpg','jpeg','png','gif')))
			$phpthumb->setParameter('f',$ext);
		else
			$phpthumb->setParameter('f','jpeg');
		$phpthumb->setParameter('config_document_root', rtrim($modx->config['base_path'],'/'));
		foreach($params as $key=>$value)
			$phpthumb->setParameter($key,$value);
		$phpthumb->setSourceFilename($filename);
		// generate & output thumbnail
		if ($phpthumb->GenerateThumbnail())
			$phpthumb->RenderToFile($target);
		unset($phpthumb);
	}		

	/**
	* Determine the number of days in a given month/year
	*/
	function checkGalleryTable()
	{
                global $modx;
                $sql = "CREATE TABLE IF NOT EXISTS " . $modx->getFullTableName($this->galleriesTable) . " (" .
			"`id` int(11) NOT NULL auto_increment PRIMARY KEY, " .
			"`content_id` int(11) NOT NULL, " .
			"`filename` varchar(255) NOT NULL, " .
			"`title` varchar(255) NOT NULL, " .
			"`description` TEXT NOT NULL, " .
			"`keywords` TEXT NOT NULL, " .
			"`sortorder` smallint(7) NOT NULL default '0'" .
                ")";
                $modx->db->query($sql);
    }
		
	/**
	* Load language file
	*/
	function loadLanguage()
	{
		global $modx;
		$langpath = $this->config['modulePath'].'lang/';
		//First load english lang by defaule
		$fname = $langpath.'english.inc.php';
		if (file_exists($fname))
		{
			include($fname);
		}
		//And now load current lang file
		$fname = $langpath.$modx->config['manager_language'].'.inc.php';
		if (file_exists($fname))
		{
			include($fname);
		}
		$this->lang = $_lang;
		unset($_lang);
	}
    
	/**
	* Replace placeholders in template
	*/
	function processTemplate($tplfile, $params)
	{
		$tpl = file_get_contents($this->config['modulePath'] . 'templates/' . $tplfile);
		//Parse placeholders
		foreach($params as $key=>$value)
		{
			$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
		}
		//Parse lang placeholders
		foreach ($this->lang as $key=>$value)
		{
			$tpl = str_replace('[+lang.'.$key.'+]', $value, $tpl);
		}
		return $tpl;
	}
	
	/**
	* Execute Ajax action
	*/
	function executeAction()
	{
		global $modx;
		switch($_REQUEST['action'])
		{
			case 'upload':
				return $this->uploadFile();
				break;
			case 'deleteall':
				$mode = isset($_POST['mode'])?$_POST['mode']:'';
				$ids = isset($_POST['action_ids'])?$modx->db->escape($_POST['action_ids']):'';
				$ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->deleteImages($mode,$ids);
				break;
			case 'regenerateall':
				$mode = isset($_POST['mode'])?$_POST['mode']:'';
				$ids = isset($_POST['action_ids'])?$modx->db->escape($_POST['action_ids']):'';
				$ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->regenerateImages($mode,$ids);
				break;
			case 'move':
				$mode = isset($_POST['mode'])?$_POST['mode']:'';
				$target = isset($_POST['target'])?intval($_POST['target']):0;
				$ids = isset($_POST['action_ids'])?$modx->db->escape($_POST['action_ids']):'';
				$ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->moveImages($mode,$ids,$target);
				break;
			case 'getids':
				$field = isset($_GET['field'])?$modx->db->escape($_GET['field']):'id';
				$mode = isset($_GET['mode'])?$_GET['mode']:'';
				$ids = isset($_GET['action_ids'])?$modx->db->escape($_GET['action_ids']):'';
				$ids = explode(',',$ids);
				foreach($ids as $key=>$value)
					$ids[$key] = intval($value);
				return $this->getIDs($field, $mode, $ids);
				break;
			case 'fake';
				sleep(1);
				break;
		}
	}
	
	/**
	* Decode PHPThumb configuration
	*/
	function getPhpthumbConfig($params)
	{
		return json_decode(str_replace("'","\"",$params),true);	
	}
	
	/**
	* Check and create folders for images
	*/
	function makeFolders($target_dir) {
		global $modx;

		$new_folder_permissions = octdec($modx->config['new_folder_permissions']);
		$keepOriginal = $this->config['keepOriginal']=='Yes';

		if (!file_exists($target_dir))
			mkdir($target_dir, $new_folder_permissions);
		if (!file_exists($target_dir . 'thumbs'))
			mkdir($target_dir . 'thumbs', $new_folder_permissions);
		if ($keepOriginal && !file_exists($target_dir . 'original'))
			mkdir($target_dir . 'original', $new_folder_permissions);
	}

	/**
	* Upload file
	*/
	function uploadFile()
	{
		global $modx;
		
		if (is_uploaded_file($_FILES['Filedata']['tmp_name'])){
			$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : $params['docId'];  // Get document id3_get_frame_long_name(string frameId)
			$target_dir = $this->config['savePath'] . '/' . $content_id . '/';
			$target_fname = $_FILES['Filedata']['name'];
			$keepOriginal = $this->config['keepOriginal']=='Yes';
			
			$path_parts = pathinfo($target_fname);
			
			if ($this->config['randomFilenames']=='Yes') {
				$target_fname = $this->getRandomString(8).'.'.$path_parts['extension'];
			}
			elseif ($modx->config['clean_uploaded_filename']) {
				$target_fname = $modx->stripAlias($path_parts['filename']).'.'.$path_parts['extension'];
			}
			
			$target_file = $target_dir . $target_fname;
			$target_thumb = $target_dir . 'thumbs/' . $target_fname;
			$target_original = $target_dir . 'original/' . $target_fname;
			
			// Check for existence of document/gallery directories
			$this->makeFolders($target_dir);
	
			$movetofile = $keepOriginal?$target_original:$target_dir.uniqid();
			// Copy uploaded image to final destination
			if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $movetofile))
			{
				
				$this->resizeImage($movetofile, $target_file, $this->getPhpthumbConfig($this->config['phpthumbImage']));  // Create and save main image
				$this->resizeImage($movetofile, $target_thumb, $this->getPhpthumbConfig($this->config['phpthumbThumb']));  // Create and save thumb
				
				$new_file_permissions = octdec($modx->config['new_file_permissions']);
				chmod($target_file, $new_file_permissions);
				chmod($target_thumb, $new_file_permissions);
				if ($keepOriginal)
					chmod($target_original, $new_file_permissions);
				else
					unlink($movetofile);
			}

			if (isset($_POST['edit']))
			{
				// Replace mode
				
				// Delete existing image
				$id = intval($_POST['edit']);
				$oldfilename = $modx->db->getValue($modx->db->select('filename',$modx->getFullTableName('portfolio_galleries'),'id='.$id));
				if(!empty($oldfilename) && $oldfilename !== $target_fname){
					if (file_exists($target_dir . 'thumbs/' . $oldfilename))
						unlink($target_dir . 'thumbs/' . $oldfilename);
					if (file_exists($target_dir . 'original/' . $oldfilename))
						unlink($target_dir . 'original/' . $oldfilename);
					if (file_exists($target_dir . $oldfilename))
						unlink($target_dir . $oldfilename);
				}
				
				// Update record in the database
				$fields = array(
					'filename' => $modx->db->escape($target_fname)
				);
				$modx->db->update($fields, $modx->getFullTableName('portfolio_galleries'), "id='".$id."'");
				
			} else
			{
				// Find the last order position
				$rs = $modx->db->select('sortorder', $modx->getFullTableName('portfolio_galleries'), 'content_id="'.$content_id.'"', 'sortorder DESC', '1');
				if ($modx->db->getRecordCount($rs) > 0)
					$pos = $modx->db->getValue($rs) + 1;
				else
					$pos = 1; 

				// Create record in the database
				$fields = array(
					'content_id' => $content_id,
					'filename' => $modx->db->escape($target_fname),
					'sortorder' => $pos
				);
				$modx->db->insert($fields, $modx->getFullTableName('portfolio_galleries'));
				$id = $modx->db->getInsertId();
			}
			
			//return new filename
			return json_encode(array('result'=>'ok','filename'=>$target_fname,'id'=>$id));
		}
		
	}
	
	/**
	* Get SQL Where condition given mode and ids
	*/
	function getWhereClassByMode($mode = 'id', $ids = array())
	{
		$where = '';
		switch ($mode)
		{
			case 'id':
				if (!sizeof($ids))
					return false;
				$where = 'id in ('.implode(',',$ids).')';
				break;
			case 'all':
				$where = '';
				break;
			case 'contentid':
				if (!sizeof($ids))
					return false;
				$where = 'content_id in ('.implode(',',$ids).')';
				break;
			default:
				return false;
		}
		return $where;
	}
		
	/**
	* Delete given images
	*/
	function deleteImages($mode = 'id', $ids = array())
	{
		global $modx;
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
			
		$ds = $modx->db->select('id, filename, content_id',$modx->getFullTablename($this->galleriesTable),$where);
		while ($row = $modx->db->getRow($ds))
		{
			$target_dir = $this->config['savePath'].'/'.$row['content_id'].'/';
			if (file_exists($target_dir . 'thumbs/' . $row['filename']))
				unlink($target_dir . 'thumbs/' . $row['filename']);
			if (file_exists($target_dir . 'original/' . $row['filename']))
				unlink($target_dir . 'original/' . $row['filename']);
			if (file_exists($target_dir . $row['filename']))
				unlink($target_dir . $row['filename']);
		}
		$modx->db->delete($modx->getFullTablename($this->galleriesTable),$where);
		return true;
	}
	
	/**
	* Regenerate given images from original (if exists)
	*/
	function regenerateImages($mode = 'id', $ids = array())
	{
		global $modx;
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
		$ds = $modx->db->select('id, filename, content_id',$modx->getFullTablename($this->galleriesTable),$where);
		while ($row = $modx->db->getRow($ds))
		{
			$target_dir = $this->config['savePath'].'/'.$row['content_id'].'/';
			$orininal_file = $target_dir . 'original/' . $row['filename']; 
			if (file_exists($orininal_file))
			{
				$this->resizeImage($orininal_file, $target_dir . $row['filename'], $this->getPhpthumbConfig($this->config['phpthumbImage']));  // Create and save main image
				$this->resizeImage($orininal_file, $target_dir . 'thumbs/' . $row['filename'], $this->getPhpthumbConfig($this->config['phpthumbThumb']));  // Create and save thumb
			}	
		}
		return true;
	}
	
	/**
	* Move images to target doc
	*/
	function moveImages($mode = 'id', $ids = array(), $target = 0)
	{
		global $modx;
		if ($target==0)
			return false;
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
		$target_dir = $this->config['savePath'].'/'.$target.'/';
		$this->makeFolders($target_dir);
		
		$ds = $modx->db->select('id, filename, content_id',$modx->getFullTablename($this->galleriesTable),$where);
		while ($row = $modx->db->getRow($ds))
		{
			//Move files
			$source_dir = $this->config['savePath'].'/'.$row['content_id'].'/';
			if (file_exists($source_dir.$row['filename']))
				if (!rename($source_dir.$row['filename'], $target_dir.$row['filename']))
					return false;
			if (file_exists($source_dir.'thumbs/'.$row['filename']))
				if (!rename($source_dir.'thumbs/'.$row['filename'], $target_dir.'thumbs/'.$row['filename']))
					return false;
			if (file_exists($source_dir.'original/'.$row['filename']))
				if (!rename($source_dir.'original/'.$row['filename'], $target_dir.'original/'.$row['filename']))
					return false;
		}
		$modx->db->update(array('content_id' => $target), $modx->getFullTablename($this->galleriesTable), $where);
		return true;
	}

	/**
	* Get Ids of $field (id or content_id)
	*/
	function getIDs($field, $mode, $ids)
	{
		global $modx;
		$result_ids = array();
		$where = $this->getWhereClassByMode($mode, $ids);
		if ($where===false)
			return false;
		if (!empty($where))
			$where=' WHERE '.$where;
		$ds = $modx->db->query('SELECT DISTINCT '.$field.' FROM '.$modx->getFullTablename($this->galleriesTable).$where);
		while ($row = $modx->db->getRow($ds))
		{
			$result_ids[] = $row[$field];
		}	
		return json_encode(array('result'=>'ok','ids'=>$result_ids));
	}

	/**
	* Generate random strings, copied from MaxiGallery
	*/
	function getRandomString($length){
		$str = "";
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		srand((double)microtime()*1000000);
		$i = 0;
		while ($i <= $length) {
			$num = rand(0,61);
			$tmp = substr($salt, $num, 1);
			$str = $str . $tmp;
			$i++;
		}
		return $str;
	}

}
?>
