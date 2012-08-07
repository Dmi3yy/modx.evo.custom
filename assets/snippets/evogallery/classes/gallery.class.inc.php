<?php
/*---------------------------------------------------------------------------
* Gallery - Contains functions for generating a listing of gallery thumbanils
*                   while controlling various display aspects.
*--------------------------------------------------------------------------*/
class Gallery
{
	var $config;  // Array containing snippet configuration values

	/**
	* Class constructor, set configuration parameters
	*/
	function Gallery($params)
	{
		global $modx;

		$this->config = $params;

		$this->galleriesTable = 'portfolio_galleries';
	}

	/**
	* Determine what action was requested and process request
	*/
	function execute()
	{
		$output = '';

		$this->config['type'] = isset($this->config['type']) ? $this->config['type'] : 'simple-list';

		if ($this->config['includeAssets'])
			$this->getConfig($this->config['type']);

		if ($this->config['display'] == 'galleries')
			$output = $this->renderGalleries();
		elseif ($this->config['display'] == 'single')
			$output = $this->renderSingle();
        else
			$output = $this->renderImages();

		return $output;
	}

	/**
	* Generate a listing of document galleries
	*/
	function renderGalleries()
	{
		global $modx;

		// Retrieve chunks/default templates from disk
		$tpl = ($this->config['tpl'] == '') ? file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.default.txt') : $modx->getChunk($this->config['tpl']);
		$item_tpl = ($this->config['itemTpl'] == '') ? file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.default.txt') : $modx->getChunk($this->config['itemTpl']);
		$item_tpl_first = ($this->config['itemTplFirst'] == '') ? @file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.first.txt') : $modx->getChunk($this->config['itemTplFirst']);
		$item_tpl_alt = ($this->config['itemTplAlt'] == '') ? @file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.alt.txt') : $modx->getChunk($this->config['itemTplAlt']);
		$item_tpl_last = ($this->config['itemTplLast'] == '') ? @file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.last.txt') : $modx->getChunk($this->config['itemTplLast']);

		// Hide/show docs based on configuration
		$docSelect = '';
		if ($this->config['docId'] != '*' && !empty($this->config['docId']))
		{
			if (strpos($this->config['docId'], ',') !== false)
			{
				$docSelect = 'parent IN ('.$this->config['docId'].')';
			}
			else
				$docSelect = 'parent = ' . $this->config['docId'];
		}
		if ($this->config['excludeDocs'] > 0)
		{
			$excludeDocs = '';
			if (strpos($this->config['excludeDocs'], ',') !== false)
			{
				$excludeDocs = 'parent NOT IN ('.$this->config['excludeDocs'].')';
			}
			else
				$excludeDocs .= 'parent != ' . $this->config['excludeDocs'];
			if (!empty($docSelect))
				$docSelect.= ' AND ';
			$docSelect.= $excludeDocs;
		}

		$phx = new PHxParser();  // Instantiate PHx

		$items = '';

		// Retrieve list of documents under the requested id
		$filter = " WHERE deleted = '0' AND published = '1' AND type = 'document' AND hidemenu <= '" . $this->config['ignoreHidden'] . "'";
		if (!empty($docSelect))
			$filter.=' AND '.$docSelect;
	
		if ($this->config['paginate']) {
			//Retrieve total records
			$totalRows = $modx->db->getValue('select count(*) from '.$modx->getFullTableName('site_content').$filter);
			if (!empty($this->config['limit']) && $totalRows>$this->config['limit'])
				$totalRows = $this->config['limit'];
			$limit = $this->paginate($totalRows);
			if (!empty($limit))
				$limit = ' limit '.$limit;
		} else
			$limit = !empty($this->config['limit']) ? ' limit '.$this->config['limit'] : "";
		$result = $modx->db->query("select id, pagetitle, longtitle, description, alias, pub_date, introtext, editedby, editedon, publishedon, publishedby, menutitle from " . $modx->getFullTableName('site_content') . $filter. ' order by '. $this->config['gallerySortBy'] . ' ' . $this->config['gallerySortDir'] . $limit);
		$recordCount = $modx->db->getRecordCount($result);
		if ($recordCount > 0)
		{
		    $count = 1;
			while ($row = $modx->fetchRow($result))
			{
				$item_phx = new PHxParser();

				// Get total number of images for total placeholder
				$total_result = $modx->db->select("filename", $modx->getFullTableName($this->galleriesTable), "content_id = '" . $row['id'] . "'");
                $total = $modx->db->getRecordCount($total_result);

				// Fetch first image for each gallery, using the image sort order/direction
				$image_result = $modx->db->select("filename", $modx->getFullTableName($this->galleriesTable), "content_id = '" . $row['id'] . "'", $this->config['sortBy'] . ' ' . $this->config['sortDir'], '1');
				if ($modx->db->getRecordCount($image_result) > 0)
				{
					$image = $modx->fetchRow($image_result);
					foreach ($image as $name => $value)
						if ($name=='filename')
							$item_phx->setPHxVariable($name, rawurlencode(trim($value)));
						else
							$item_phx->setPHxVariable($name, trim($value));
					$item_phx->setPHxVariable('images_dir', $this->config['galleriesUrl'] . $row['id'] . '/');
					$item_phx->setPHxVariable('thumbs_dir', $this->config['galleriesUrl'] . $row['id'] . '/thumbs/');
					$item_phx->setPHxVariable('original_dir', $this->config['galleriesUrl'] . $row['id'] . '/original/');
					$item_phx->setPHxVariable('plugin_dir', $this->config['snippetUrl'] . $this->config['type'] . '/');

					foreach ($row as $name => $value)
						$item_phx->setPHxVariable($name, trim($value));
                    
                    // Get template variable output for row and set variables as needed
                    $row_tvs = $modx->getTemplateVarOutput('*',$row['id']);
					foreach ($row_tvs as $name => $value)
						$item_phx->setPHxVariable($name, trim($value));

					$item_phx->setPHxVariable('total', $total);

    				if(!empty($item_tpl_first) && $count == 1){
        				$items .= $item_phx->Parse($item_tpl_first);
    				} else if(!empty($item_tpl_last) && $count == $recordCount){
        				$items .= $item_phx->Parse($item_tpl_last);
    				} else if(!empty($item_tpl_alt) && $count % $this->config['itemAltNum'] == 0){
        				$items .= $item_phx->Parse($item_tpl_alt);
    				} else {
        				$items .= $item_phx->Parse($item_tpl);
    				}

				}
				$count++;
			}
		}

		$phx->setPHxVariable('items', $items);
		$phx->setPHxVariable('plugin_dir', $this->config['snippetUrl'] . $this->config['type'] . '/');

		return $phx->Parse($tpl);  // Pass through PHx;
	}

	/**
	* Generate a listing of thumbnails/images for gallery/slideshow display
	*/
	function renderImages()
	{
		global $modx;

		// Retrieve chunks/default templates from disk
		$tpl = ($this->config['tpl'] == '') ? file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.default.txt') : $modx->getChunk($this->config['tpl']);
		$item_tpl = ($this->config['itemTpl'] == '') ? file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.default.txt') : $modx->getChunk($this->config['itemTpl']);
		$item_tpl_first = ($this->config['itemTplFirst'] == '') ? @file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.first.txt') : $modx->getChunk($this->config['itemTplFirst']);
		$item_tpl_alt = ($this->config['itemTplAlt'] == '') ? @file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.alt.txt') : $modx->getChunk($this->config['itemTplAlt']);
		$item_tpl_last = ($this->config['itemTplLast'] == '') ? @file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.last.txt') : $modx->getChunk($this->config['itemTplLast']);

		$docSelect = '';
		if ($this->config['docId'] != '*' && !empty($this->config['docId']))
		{
			if (strpos($this->config['docId'], ',') !== false)
			{
				$docSelect = 'content_id IN ('.$this->config['docId'].')';
			}
			else
				$docSelect = 'content_id = ' . $this->config['docId'];
		}
		if ($this->config['excludeDocs'] > 0)
		{
			$excludeDocs = '';
			if (strpos($this->config['excludeDocs'], ',') !== false)
			{
				$excludeDocs = 'content_id NOT IN ('.$this->config['excludeDocs'].')';
			}
			else
				$excludeDocs .= 'content_id != ' . $this->config['excludeDocs'];
			if (!empty($docSelect))
				$docSelect.= ' AND ';
			$docSelect.= $excludeDocs;
		}

		if (!empty($this->config['tags']))
		{
            $mode = (!empty($this->config['tagMode']) ? $this->config['tagMode'] : 'AND');
            foreach (explode(',', $this->config['tags']) as $tag) {
            	$tagSelect .= "keywords LIKE '%" . trim($tag) . "%' ".$mode." ";
            }
            $tagSelect = rtrim($tagSelect, ' '.$mode.' ');
			if (!empty($docSelect))
				$docSelect.=' AND ';
            $docSelect .= "(".$tagSelect.")";
		}

		$phx = new PHxParser();  // Instantiate PHx

		$items = '';
		$limit = '';
		$where = !empty($docSelect)?' WHERE '.$docSelect.' ':'';
		if ($this->config['paginate']) {
			//Retrieve total records
			$totalRows = $modx->db->getValue('select count(*) from '.$modx->getFullTableName($this->galleriesTable).$where.(!empty($this->config['limit']) ? ' limit '.$this->config['limit'] : ""));
			$limit = $this->paginate($totalRows);
			if (!empty($limit))
				$limit = ' limit '.$limit;
		} else
			$limit = !empty($this->config['limit']) ? ' limit '.$this->config['limit'] : "";
		// Retrieve photos from the database table
		$result = $modx->db->query("select * from ". $modx->getFullTableName($this->galleriesTable). $where. ' order by '. $this->config['sortBy'] . ' ' . $this->config['sortDir']. $limit);
        $recordCount = $modx->db->getRecordCount($result);
		if ($recordCount > 0)
		{
            $count = 1;		    
			while ($row = $modx->fetchRow($result))
			{
				$item_phx = new PHxParser();
				foreach ($row as $name => $value)
					if ($name=='filename')
						$item_phx->setPHxVariable($name, rawurlencode(trim($value)));
					else
						$item_phx->setPHxVariable($name, trim($value));
				$imgsize = getimagesize($this->config['galleriesPath'] . $row['content_id'] . '/' . $row['filename']); 
				$item_phx->setPHxVariable('width',$imgsize[0]); 
				$item_phx->setPHxVariable('height',$imgsize[1]); 
				$item_phx->setPHxVariable('image_withpath', $this->config['galleriesUrl'] . $row['content_id'] . '/' . $row['filename']);
				$item_phx->setPHxVariable('images_dir', $this->config['galleriesUrl'] . $row['content_id'] . '/');
				$item_phx->setPHxVariable('thumbs_dir', $this->config['galleriesUrl'] . $row['content_id'] . '/thumbs/');
				$item_phx->setPHxVariable('original_dir', $this->config['galleriesUrl'] . $row['content_id'] . '/original/');
				$item_phx->setPHxVariable('plugin_dir', $this->config['snippetUrl'] . $this->config['type'] . '/');
				if(!empty($item_tpl_first) && $count == 1){
    				$items .= $item_phx->Parse($item_tpl_first);
				} else if(!empty($item_tpl_last) && $count == $recordCount){
    				$items .= $item_phx->Parse($item_tpl_last);
				} else if(!empty($item_tpl_alt) && $count % $this->config['itemAltNum'] == 0){
    				$items .= $item_phx->Parse($item_tpl_alt);
				} else {
    				$items .= $item_phx->Parse($item_tpl);
				}
				$count++;
			}
		}
		$phx->setPHxVariable('items', $items);
		$phx->setPHxVariable('plugin_dir', $this->config['snippetUrl'] . $this->config['type'] . '/');

		return $phx->Parse($tpl);  // Pass through PHx;
	}

	/**
	* Generate a listing of a single thumbnail/image for gallery/slideshow display
	*/
	function renderSingle()
	{
		global $modx;

		// Retrieve chunks/default templates from disk
		$tpl = ($this->config['tpl'] == '') ? file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.default.txt') : $modx->getChunk($this->config['tpl']);
		$item_tpl = ($this->config['itemTpl'] == '') ? file_get_contents($this->config['snippetPath'] . $this->config['type'] . '/tpl.item.default.txt') : $modx->getChunk($this->config['itemTpl']);

		$picSelect = '';
		if ($this->config['picId'] != '*' && !empty($this->config['picId']))
		{
				$picSelect = "id = '" . $this->config['picId'] . "'";
		}

		$phx = new PHxParser();  // Instantiate PHx

		$items = '';

		// Retrieve photos from the database table
	    $result = $modx->db->select("*", $modx->getFullTableName($this->galleriesTable), $picSelect);
		if ($modx->db->getRecordCount($result) > 0)
		{
			while ($row = $modx->fetchRow($result))
			{
				$item_phx = new PHxParser();
				foreach ($row as $name => $value)
					if ($name=='filename')
						$item_phx->setPHxVariable($name, rawurlencode(trim($value)));
					else
						$item_phx->setPHxVariable($name, trim($value));
				$item_phx->setPHxVariable('images_dir', $this->config['galleriesUrl'] . $row['content_id'] . '/');
				$item_phx->setPHxVariable('thumbs_dir', $this->config['galleriesUrl'] . $row['content_id'] . '/thumbs/');
				$item_phx->setPHxVariable('original_dir', $this->config['galleriesUrl'] . $row['content_id'] . '/original/');
				$item_phx->setPHxVariable('plugin_dir', $this->config['snippetUrl'] . $this->config['type'] . '/');
				$items .= $item_phx->Parse($item_tpl);
			}
		}

		$phx->setPHxVariable('items', $items);
		$phx->setPHxVariable('plugin_dir', $this->config['snippetUrl'] . $this->config['type'] . '/');

		return $phx->Parse($tpl);  // Pass through PHx;
	}

	/**
	* Get configuration settings for the selected gallery/slideshow type
	*/
	function getConfig($type)
	{
		global $modx;

		if (file_exists($this->config['snippetPath'] . $type . '/tpl.config.txt'))
		{
			$register = 0;

			$config = file($this->config['snippetPath'] . $type . '/tpl.config.txt');
			foreach ($config as $line)
			{
				$line = trim($line);

				if ($line == '')
					$register = 0;
				elseif (strpos($line, '@SCRIPT') === 0)
					$register = 1;
				elseif (strpos($line, '@CSS') === 0)
					$register = 2;
				elseif (strpos($line, '@EXTSCRIPT') === 0)
					$register = 3;
				elseif (strpos($line, '@EXTCSS') === 0)
					$register = 4;
				else
				{
					switch ($register)
					{
						case 1:
							$modx->regClientStartupScript($this->config['snippetUrl'] . $type . '/' . $line);
							break;
						case 2:
							$modx->regClientCSS($this->config['snippetUrl'] . $type . '/' . $line);
							break;
						case 3:
							$modx->regClientStartupScript($line);
							break;
						case 4:
							$modx->regClientCSS($line);
							break;
					}
				}
			}
		}
	}

	/**
	* Replace placeholders in template
	*/
	function processTemplate($tpl, $params)
	{
		//Parse placeholders
		foreach($params as $key=>$value)
		{
			$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
		}
		return $tpl;
	}

	/**
	*  Set pagination's placeholders
	*  Return string with limit values for SQL query
	*/
	function paginate($totalRows) {
		global $modx;
		if (!$this->config['paginate'])
			return "";

		$pageUrl = !empty($this->config['id'])?$this->config['id'].'_page':'page';
		$page = isset($_GET[$pageUrl])?intval($_GET[$pageUrl]):1;
		$rowsPerPage = $this->config['show'];
		$totalPages = ceil($totalRows/$rowsPerPage);
		$previous = $page - 1;
		$next = $page + 1;
		$start = ($page-1)*$rowsPerPage;
		if ($start<0)
			$start = 0;
		$stop = $start + $rowsPerPage - 1;
		if ($stop>=$totalRows)
			$stop = $totalRows - 1;

		$split = "";
		if ($previous > 0 && $next <= $totalPages)
			$split = $paginateSplitterCharacter;

		$previoustpl = '';
		$previousplaceholder = '';
		if ($previous > 0)
			$previoustpl = 'tplPaginatePrevious';
		elseif ($this->config['paginateAlwaysShowLinks'])
			$previoustpl = 'tplPaginatePreviousOff';
		if (!empty($previoustpl))
			$previousplaceholder = $this->processTemplate($this->config[$previoustpl],
															array('url'=>$modx->makeUrl($modx->documentIdentifier,'',($previous!=1?"$pageUrl=$previous":"")),
																'PaginatePreviousText'=>$this->config['paginatePreviousText']));			
		$nexttpl = '';
		$nextplaceholder = '';
		if ($next <= $totalPages)
			$nexttpl = 'tplPaginateNext';
		elseif ($this->config['paginateAlwaysShowLinks'])
			$nexttpl = 'tplPaginateNextOff';
		if (!empty($nexttpl))
			$nextplaceholder = $this->processTemplate($this->config[$nexttpl],
														array('url'=>$modx->makeUrl($modx->documentIdentifier,'',($next!=1?"$pageUrl=$next":"")),
																'PaginateNextText'=>$this->config['paginateNextText']));			

		$pages = '';
		for ($i=1;$i<=$totalPages;$i++) {
			if ($i != $page) {
				$pages .= $this->processTemplate($this->config['tplPaginatePage'],
												array('url'=>$modx->makeUrl($modx->documentIdentifier,'',($i!=1?"$pageUrl=$i":"")),'page'=>$i));
			} else {
				$modx->setPlaceholder($this->config['id']."currentPage", $i);
				$pages .= $this->processTemplate($this->config['tplPaginateCurrentPage'], array('page'=>$i));
			}
		}
		$modx->setPlaceholder($this->config['id']."next", $nextplaceholder);
		$modx->setPlaceholder($this->config['id']."previous", $previousplaceholder);
		$modx->setPlaceholder($this->config['id']."splitter", $split);
		$modx->setPlaceholder($this->config['id']."start", $start+1);
		$modx->setPlaceholder($this->config['id']."stop", $stop+1);
		$modx->setPlaceholder($this->config['id']."total", $totalRows);
		$modx->setPlaceholder($this->config['id']."pages", $pages);
		$modx->setPlaceholder($this->config['id']."perPage", $rowsPerPage);
		$modx->setPlaceholder($this->config['id']."totalPages", $totalPages);
		return $start.','.($stop-$start+1);
	}	

}
?>
