<?php 
	// ---------------------------------------------------------------
	// :: Doc Finder
	// ----------------------------------------------------------------
	//   
	// 	Short Description: 
	//         Ajax powered search and replace for the manager.
	// 
	//   Version:
	//         1.6
	// 
	//   Created by:
	// 	    Bogdan Günther (http://www.medianotions.de - bg@medianotions.de)
	//   Перевод: SpaceW (http://spacew.habrahabr.ru/), RBE-Studio
	// 
	// ----------------------------------------------------------------
	// :: Copyright & Licencing
	// ----------------------------------------------------------------
	// 
	//   GNU General Public License (GPL - http://www.gnu.org/copyleft/gpl.html)
	// 
	//
		function rusAtr($sResults) {
		$patterns[0] = "/Pagetitle/";
		$patterns[1] = "/Longtitle/";
		$patterns[2] = "/Description/";
		$patterns[3] = "/Alias/";
		$patterns[4] = "/Introtext/";
		$patterns[5] = "/Menutitle/";
		$patterns[6] = "/Content/";
		$patterns[7] = "/Templatename/";
		$patterns[8] = "/Name/";
		$patterns[9] = "/Elements/";
		$patterns[10] = "/Default_text/";
		$patterns[11] = "/Snippet/";
		$patterns[12] = "/Plugincode/";
		$patterns[13] = "/Modulecode/";
		$patterns[14] = "/Document/";
		$patterns[15] = "/Template/";
		$patterns[16] = "/TV/";
		$patterns[17] = "/Chunk/";
		$patterns[18] = "/Snippet/";
		$patterns[19] = "/Plugin/";
		$patterns[20] = "/Module/";	
		$replacements[0] = "Заголовок";
		$replacements[1] = "Расширенный заголовок";
		$replacements[2] = "Описание";
		$replacements[3] = "Псевдоним";
		$replacements[4] = "Аннотация";
		$replacements[5] = "Пункт меню";
		$replacements[6] = "Содержимое";
		$replacements[7] = "Имя шаблона";
		$replacements[8] = "Имя";
		$replacements[9] = "Элементы";
		$replacements[10] = "Значение по умолчанию";
		$replacements[11] = "Сниппет";
		$replacements[12] = "Код плагина";
		$replacements[13] = "Код модуля";
		$replacements[14] = "Документ";
		$replacements[15] = "Шаблон";
		$replacements[16] = "TV";
		$replacements[17] = "Чанк";
		$replacements[18] = "Сниппет";
		$replacements[19] = "Плагин";
		$replacements[20] = "Модуль";
		$searchResultsRUS = preg_replace($patterns, $replacements, $sResults);
		return $searchResultsRUS;
		}
		
		function rusNames($sName) {
		if ($sName == "Combined View"){
		$rusName = "Все вместе";
		}
		elseif ($sName == "Documents"){
		$rusName = "Документы";
		}
		elseif ($sName == "Templates") {
		$rusName = "Шаблоны";
		}
		elseif ($sName == "TVs") {
		$rusName = "TV параметры";
		}
		elseif ($sName == "Chunks") {
		$rusName = "Чанки";
		}
		elseif ($sName == "Snippets") {
		$rusName = "Сниппеты";
		}
		elseif ($sName == "Plugins") {
		$rusName = "Плагины";
		}
		elseif ($sName == "Modules") {
		$rusName = "Модули";
		}
		elseif ($sName == "Document") {
		$rusName = "Документ";
		}
		elseif ($sName == "Template") {
		$rusName = "Шаблон";
		}
		elseif ($sName == "TV") {
		$rusName = "TV параметр";
		}
		elseif ($sName == "Chunk") {
		$rusName = "Чанк";
		}
		elseif ($sName == "Snippet") {
		$rusName = "Сниппет";
		}
		elseif ($sName == "Plugin") {
		$rusName = "Плагин";
		}
		elseif ($sName == "Module") {
		$rusName = "Модуль";
		}
		else{
		$rusName = $sName;
		}
		echo $rusName;
		}
		
		
	function printResultTabs($search, $searchOptions, $theme, $searchPlacesArray)
	{		
		// if the search string is empty put message and return
		if(!$search['string'])
		{
			?>
				<p class="empty">Search string is empty.</p>

				<script type="text/javascript">
					// refresh results head info
					$('results_info').setText("No");
					$('results_string').setStyles('display: none;');
					$('time_info').setStyles('display: none;');
					$('replace_info').setStyles('display: none;');
				</script>
			<?php 

			return;
		}
		
		// build tabs
		?>
			<div class="tab-pane" id="tabPaneResults">
				<script type="text/javascript">var tpResults=null; tpResults=new WebFXTabPane($('tabPaneResults'));</script>

				<?php 
					$resultsTotal=0;

					// loop through tabs
					foreach($searchPlacesArray as $tab)
					{
						$id=$tab['id'];
						$name=$tab['name'];
						
						// skip non active search places
						if($searchOptions['search_place_'.$id]!='checked="checked"') continue;

						// get results
						$results[$id]=printResultTables($id, $search, $searchOptions, $theme, false, $searchPlacesArray);

						// print tab only if there are hits
						if($results[$id]['hits'])
						{ 
							?>
								<!-- Tab: <?php echo $name; ?> -->
								<div class="tab-page" id="tab<?php echo $id; ?>">
									<h2 class="tab"><?php echo rusNames($name); ?> (<span id="hits<?php echo $id; ?>"></span>)</h2>									
									<?php echo $results[$id]['html']; ?>

									<script type="text/javascript">
										tpResults.addTabPage($('tab<?php echo $id; ?>'));
										$('hits<?php echo $id; ?>').setText("<?php echo $results[$id]['hits']; ?>");
									</script>
								</div>
							<?php  

							$resultsTotal+=$results[$id]['hits'];
						}
					}

					if($resultsTotal==0)
					{
						?>
							<p class="empty">Ничего не найдено.</p>
						<?php 
					}
					else // print combined view
					{
						// set ID and name
						$id="CombinedView";
						$name="Combined View";
						
						// get results
						$results[$id]=printResultTables($id, $search, $searchOptions, $theme, $results, $searchPlacesArray);
						
						?>
							<!-- Tab: Combined View -->
							<div class="tab-page" id="tab<?php echo $id; ?>">
								<h2 class="tab"><?php echo rusNames($name); ?> (<span id="hits<?php echo $id; ?>"></span>)</h2>
								
								<?php echo $results[$id]['html']; ?>
								
								<script type="text/javascript">
									tpResults.addTabPage($('tab<?php echo $id; ?>'));
									$('hits<?php echo $id; ?>').setText('<?php echo $resultsTotal; ?>');
								</script>
							</div>
						<?php 
					}
				?>

			</div>
		<?php 

		// adapt results headline
		?>
			<script type="text/javascript">
				$('results_string').setStyles('display: inline;');
				$('results_info').setText('<?php echo $resultsTotal; ?>');
			</script>
		<?php 

		// adapt results headline if there was a string replace
		if($searchOptions['replace_mode'])
		{
			?>
				<script type="text/javascript">
					// refresh results head info
					$('replace_string').setText("<?php echo $searchOptions['replace']; ?>");
					$('replace_info').setStyles('display: inline;');

					<?php  if($counter>0) { ?>
					// refresh doc tree in the left frame
					if(parent.tree) parent.tree.location.reload();
					<?php  } ?>
				</script>
			<?php 
		}
		else
		{
			?>
				<script type="text/javascript">
					// show replace info
					$('replace_info').setStyles('display: none;');
				</script>
			<?php 
		}
	}


	function printResultTables($area, $search, $searchOptions, $theme, $results, $searchPlacesArray)
	{
		global $modx;

		// start ouput buffering
		ob_start();
		
		// define action IDs
		$actionIDsArray['DocAndTVV']['edit']=27;
		$actionIDsArray['DocAndTVV']['info']=3;
		$actionIDsArray['Templates']['edit']=16;
		$actionIDsArray['TVs']['edit']=301;
		$actionIDsArray['Chunks']['edit']=78;
		$actionIDsArray['Snippets']['edit']=22;
		$actionIDsArray['Plugins']['edit']=102;
		$actionIDsArray['Modules']['edit']=108;
	
	
	
		// set sortable class if sortable tables are enabled
		if($searchOptions['sortable_tables']) $sortableClass="sortable";

		switch($area)
		{
			// print Documents and TV values results table
			case "DocAndTVV":

				// get results
				$searchResultsArray=getDocAndTVVResults($search, $searchOptions);
				
				// print table top
				if($searchResultsArray)
				{
					?>
						<table class="DocAndTVV" id="<?php echo $area; ?>">
							<thead>
								<tr>
									<th class="no align_right <?php echo $sortableClass; ?>" axis="number">#</th>
									<th class="type">Тип</th>
									<th class="id align_right <?php echo $sortableClass; ?>" axis="number">ID</th>
									<th class="title <?php echo $sortableClass; ?>" axis="string">Заголовок</th>
									<th class="title <?php echo $sortableClass; ?>" axis="string">Расширенный заголовок</th>
									<th class="title <?php echo $sortableClass; ?>" axis="date">Создан</th>
									<th class="title <?php echo $sortableClass; ?>" axis="date">Редактировался</th>
									<?php  if($search['string']!="ALL") { ?>
									<th class="found_in <?php echo rusAtr($sortableClass);  ?>" axis="string">Найдено в</th>
									<?php  } ?>
									<th class="functions">Действие</th>
								</tr>
							</thead>
							
							<tbody>
					<?php 
				}
				
				// set edit and info IDs
				$editActionID=$actionIDsArray[$area]['edit'];
				$infoActionID=$actionIDsArray[$area]['info'];

				// print results
				$counter=0;
				if($searchResultsArray) foreach($searchResultsArray as $searchResults)
				{
					// set URLs
					$urlEdit="index.php?a=".$editActionID."&amp;id=".$searchResults['id'];
					$urlInfo="index.php?a=".$infoActionID."&amp;id=".$searchResults['id'];
					$urlOpen=$modx->makeUrl(intval($searchResults['id']));

					// set CSS classes
					if($searchResults['hidemenu']) $pagetitle_class.=" hidemenu";
					if($searchResults['published']) $pagetitle_class.=" published";

					// check document type
					if($modx->getDocumentChildren($searchResults['id'])) $iconName="folder.gif";
					else $iconName="page.gif";
					// output result
					

					?>
						<tr>
							<td class="no align_right"><?php echo $counter+1; ?></td>
							<td class="type"><img src="media/style<?php echo $theme; ?>/images/tree/<?php echo $iconName; ?>" alt="" /></td>
							<td class="id align_right"><?php echo $searchResults['id']; ?></td>
							<td class="pagetitle<?php echo $pagetitle_class; ?>">
								<strong><a href="<?php echo $urlEdit; ?>"><?php echo $searchResults['pagetitle']; ?></a></strong>
								<small><?php echo printDocumentBranch($searchResults['parentsArray']); ?></small>
							</td>
							<td class="longtitle"><?php echo $searchResults['longtitle']; ?></td>
							<td class="createdon"><?php echo date("d/m/Y", $searchResults['createdon']); ?></td>
							<td class="editedon"><?php echo date("d/m/Y", $searchResults['editedon']); ?></td>
							<?php  if($search['string']!="ALL") { ?>
							<td class="found_in"><?php echo rusAtr(substr($searchResults['found_in'], 2)); ?></td>
							<?php  } ?>
							<td class="functions">
								<a href="<?php echo $urlEdit; ?>" title="Редактировать"><img src="media/style<?php echo $theme; ?>/images/icons/save.png" alt="Редатировать" /></a>
								<a href="<?php echo $urlInfo; ?>" title="Информация"><img src="media/style<?php echo $theme ;?>/images/tree/page-html.gif" alt="Информация" /></a>								
								<a href="<?php echo $urlOpen; ?>" title="Превью" target="_blank"><img src="media/style<?php echo $theme; ?>/images/icons/page_white_magnify.png" alt="Превью" /></a>
							</td>
						</tr>
					<?php 

					$pagetitle_class="";
					$counter++;
				}
				else
				{
					?>
						<p class="empty">Ничего не найдено.</p>
					<?php 
				}

				// print table bottom
				if($searchResultsArray)
				{
					?>	
							</tbody>
						</table>
						
						<?php  if($searchOptions['sortable_tables']) { ?>
						<script type="text/javascript">
							window.addEvent('domready', function() {
								// activate sortable table
								var DocAndTVV_Table=new sortableTable('<?php echo $area; ?>');
							});
						</script>
						<?php  } ?>
					<?php 
				}

			break;

			// print Template results table
			case "Templates":
			case "TVs":
			case "Chunks":
			case "Snippets":
			case "Plugins":
			case "Modules":

				// get results
				$searchResultsArray=getResourcesResults($area, $search, $searchOptions);

				// print table top
				if($searchResultsArray)
				{
					?>
						<table class="Resources" id="<?php echo $area; ?>_table">
							<thead>
								<tr>
									<th class="no align_right <?php echo $sortableClass; ?>" axis="number">#</th>
									<th class="align_right id <?php echo $sortableClass; ?>" axis="number">ID</th>
									<th class="name <?php echo $sortableClass; ?>" axis="string">Имя</th>
									<th class="description <?php echo $sortableClass; ?>" axis="string">Описание</th>
									<?php  if($search['string']!="ALL") { ?>
									<th class="found_in <?php echo rusAtr($sortableClass); ?>" axis="string">Найдено в</th>
									<?php  } ?>
									<th class="functions">Действие</th>
								</tr>
							</thead>
							
							<tbody>
					<?php 
				}

				// set edit id
				$editActionID=$actionIDsArray[$area]['edit'];

				// print results
				$counter=0;
				if($searchResultsArray) foreach($searchResultsArray as $searchResults)
				{
					// set URLs
					$urlEdit="index.php?a=".$editActionID."&amp;id=".$searchResults['id'];

					// take care of different name IDs
					if($searchResults['templatename']) $searchResults['name']=$searchResults['templatename']

					// output result
					?>
						<tr>
							<td class="no align_right"><?php echo $counter+1; ?></td>
							<td class="id align_right"><?php echo $searchResults['id']; ?></td>
							<td class="name"><a href="<?php echo $urlEdit?>"><?php echo $searchResults['name']; ?></a></td>
							<td class="description"><?php echo $searchResults['description']; ?></td>
							<?php  if($search['string']!="ALL") { ?>
							<td class="found_in"><?php echo rusAtr(substr($searchResults['found_in'], 2)); ?> </td>
							<?php  } ?>
							<td class="functions">
								<a href="<?php echo $urlEdit?>" title="Редактировать"><img src="media/style<?php echo $theme; ?>/images/icons/save.png" alt="Редатировать" /></a>
							</td>
						</tr>
					<?php 

					$pagetitle_class="";
					$counter++;
				}
				else
				{
					?>
						<p class="empty">Ничего не найдено.</p>
					<?php 
				}

				// print table bottom
				if($searchResultsArray)
				{
					?>
							</tbody>
						</table>
						
						<?php  if($searchOptions['sortable_tables']) { ?>
						<script type="text/javascript">
							window.addEvent('domready', function() {
								// activate sortable table
								var Resources_Table=new sortableTable('<?php echo $area; ?>_table');
							});
						</script>
						<?php  } ?>
					<?php 
				}

			break;
			
			// print Combined View
			case "CombinedView":
			
				// print table top
				?>
					<table class="Resources" id="<?php echo $area; ?>">
						<thead>
							<tr>
								<th class="no align_right <?php echo $sortableClass; ?>" axis="number">#</th>
								<th class="align_right id <?php echo $sortableClass; ?>" axis="number">ID</th>
								<th class="name <?php echo $sortableClass; ?>" axis="string">Тип</th>
								<th class="name <?php echo $sortableClass; ?>" axis="string">Заголовок / Имя</th>
								<th class="description <?php echo $sortableClass; ?>" axis="string">Расширенный заголовок / Описание</th>
								<th class="title <?php echo $sortableClass; ?>" axis="date">Создан</th>
								<th class="title <?php echo $sortableClass; ?>" axis="date">Редактировался</th>
								<?php  if($search['string']!="ALL") { ?>
								<th class="found_in <?php echo rusAtr($sortableClass); ?>" axis="string">Найденов в</th>
								<?php  } ?>
								<th class="functions">Действие</th>
							</tr>
						</thead>
				
						<tbody>
				<?php 
			
				// loop through search places
				foreach($searchPlacesArray as $section)
				{
					// ger search results
					$searchResultsArray=$results[$section['id']]['array'];
					
					// set edit and info IDs
					$editActionID=$actionIDsArray[$section['id']]['edit'];
					$infoActionID=$actionIDsArray[$section['id']]['info'];

					if($searchResultsArray) foreach($searchResultsArray as $searchResults)
					{
						// set values for the documents
						if($section['id']=="DocAndTVV")
						{
							// set URLs
							$urlEdit="index.php?a=".$editActionID."&amp;id=".$searchResults['id'];
							$urlInfo="index.php?a=".$infoActionID."&amp;id=".$searchResults['id'];
							$urlOpen=$modx->makeUrl(intval($searchResults['id']));
							
							// set values
							$name=$searchResults['pagetitle'];
							$description=$searchResults['longtitle'];
							$createdon=date("d-m-Y", $searchResults['createdon']);
							$editedon=date("d-m-Y", $searchResults['editedon']);
						}
						else // set values for the resources
						{
							// set URLs
							$urlEdit="index.php?a=".$editActionID."&amp;id=".$searchResults['id'];
							
							// set values
							if($searchResults['templatename']) $searchResults['name']=$searchResults['templatename'];
							$name=$searchResults['name'];							
							$description=$searchResults['description'];
							if($searchResults['createdon']) $createdon=date("d-m-Y", $searchResults['createdon']);
							else $createdon="&ndash;";
							if($searchResults['editedon']) $editedon=date("d-m-Y", $searchResults['editedon']);
							else $editedon="&ndash;";
						}
						
						// set common values
						$type=substr($section['name'], 0, strlen($section['name'])-1);

						// take care of different name IDs
						if($searchResults['templatename']) $searchResults['name']=$searchResults['templatename']

						// output result
						?>
							<tr>
								<td class="no align_right"><?php echo $counter+1; ?></td>
								<td class="id align_right"><?php echo $searchResults['id']; ?></td>
								<td class="type"><?php rusNames($type); ?></td>
								<td class="name"><a href="<?php echo $urlEdit?>"><?php echo $name; ?></a></td>
								<td class="description"><?php echo $description; ?></td>
								<td class="createdon"><?php echo $createdon; ?></td>
								<td class="editedon"><?php echo $editedon; ?></td>
								<?php  if($search['string']!="ALL") { ?>
								<td class="found_in"><?php echo rusAtr(substr($searchResults['found_in'], 2)); ?></td>
								<?php  } ?>
								<td class="functions">
									<a href="<?php echo $urlEdit; ?>" title="Редактировать"><img src="media/style<?php echo $theme; ?>/images/icons/save.png" alt="Редатировать" /></a>
									<?php  if($section['id']=="DocAndTVV") { ?>
										<a href="<?php echo $urlInfo; ?>" title="Информация"><img src="media/style<?php echo $theme; ?>/images/tree/page-html.gif" alt="Информация" /></a>								
										<a href="<?php echo $urlOpen; ?>" title="Превью" target="_blank"><img src="media/style<?php echo $theme; ?>/images/icons/page_white_magnify.png" alt="Превью" /></a>
									<?php  } ?>
								</td>
							</tr>
						<?php 

						$pagetitle_class="";
						$counter++;
					}
				}
				
				// print table bottom
				?>
						</tbody>
					</table>
										
					<?php  if($searchOptions['sortable_tables']) { ?>
					<script type="text/javascript">
						window.addEvent('domready', function() {
							// activate sortable table
							var CombinedView_Table=new sortableTable('<?php echo $area; ?>');
						});
					</script>
					<?php  } ?>
				<?php 
				
			break;
		}

		// save search hits
		$results['hits']=$counter;

		// save HTML output
		$results['html']=ob_get_clean();
		
		// save results arrays
		$results['array']=$searchResultsArray;

		return $results;
	}

	function getDocAndTVVResults($search, $searchOptions)
	{
		global $modx;

		// set SQL data selection 
		$sqlSelection="id, pagetitle, longtitle, published, hidemenu, description, alias, introtext, menutitle, content, createdon, editedon";
		
		// set search fields
		$searchFieldArray=split(", ", $sqlSelection);
		
		// search in all defined fields
		$dbTable=$modx->getFullTableName("site_content");
		foreach($searchFieldArray as $searchField)
		{
			// Filter: Check where to search in and where to skip
			if(!$searchOptions['id'] and $searchField=="id") continue;
			if(!$searchOptions['pagetitle'] and $searchField=="pagetitle") continue;
			if(!$searchOptions['longtitle'] and $searchField=="longtitle") continue;
			if(!$searchOptions['description'] and $searchField=="description") continue;
			if(!$searchOptions['alias'] and $searchField=="alias") continue;
			if(!$searchOptions['introtext'] and $searchField=="introtext") continue;
			if(!$searchOptions['menutitle'] and $searchField=="menutitle") continue;
			if(!$searchOptions['content'] and $searchField=="content") continue;
			
			// Filter: createdon and editedon are not searched directly
			if($searchField=="createdon" or $searchField=="editedon") continue;
			

			// get SQL WHERE else continue
			if($search['string']!="ALL")
			{
				$sqlWhere=getSqlWhere($search['string'], $searchField, $searchOptions);
				if(!$sqlWhere) continue;
			}
			
			// complete SQL query
			$sql="SELECT $sqlSelection FROM $dbTable $sqlWhere";

			// query DB via MODx DB API
			$result=$modx->db->query($sql);

			// get rows
			while($row=$modx->fetchRow($result))
			{
				// check created date range
				if($row['createdon']<$searchOptions['createdon_start_time'] or $row['createdon']>$searchOptions['createdon_end_time']) continue;
				
				// check edited date range
				if($row['editedon']<$searchOptions['editedon_start_time'] or $row['editedon']>$searchOptions['editedon_end_time']) continue;

				$id=$row['id'];

				// checkParents
				$parents=getAllParents($id);
				if(checkparents($search['parentsArray'], $parents))
				{
					// save results in our results array
					$searchResultsArray[$id]['id']=$id;
					$searchResultsArray[$id]['pagetitle']=$row['pagetitle'];
					$searchResultsArray[$id]['longtitle']=$row['longtitle'];
					$searchResultsArray[$id]['createdon']=$row['createdon'];
					$searchResultsArray[$id]['editedon']=$row['editedon'];
					$searchResultsArray[$id]['published']=$row['published'];
					$searchResultsArray[$id]['hidemenu']=$row['hidemenu'];
					$searchResultsArray[$id]['found_in'].=", ".ucfirst($searchField);
					$searchResultsArray[$id]['parentsArray']=$parents;

					// replace
					if($searchOptions['replace_mode'] and $searchField!="id") replace($search['string'], $searchOptions['replace'], $id, $searchField, $row[$searchField], $dbTable);
				}
			}

			// jump to next search array key
			next($search);
		}

		// search in TVs if required
		if($searchOptions['tvs'] and $search['string']!="ALL")
		{
			$tableTV_Names=$modx->getFullTableName("site_tmplvars");
			$tableTV_Content=$modx->getFullTableName("site_tmplvar_contentvalues");

			// get SQL WHERE
			$sqlWhere=getSqlWhere($search['string'], 'value', $searchOptions);

			// complete SQL query
			$sqlTVNames="SELECT id, name FROM $tableTV_Names";
			$sqlTVContent="SELECT contentid, value, tmplvarid, id FROM $tableTV_Content $sqlWhere";
			
			// query DB via MODx DB API
			$resultTVNames=$modx->db->query($sqlTVNames);
			$resultTVContent=$modx->db->query($sqlTVContent);

			// get rows TV names
			while($row=$modx->fetchRow($resultTVNames))
			{
				$id=$row['id'];
				$name=$row['name'];

				$TV_Names[$id]['id']=$id;
				$TV_Names[$id]['name']=$name;
			}

			// get rows TV content
			while($row=$modx->fetchRow($resultTVContent))
			{
				$id=$row['contentid'];
				
				// checkParents
				$parents=getAllParents($id);
				if(checkparents($search['parentsArray'], $parents))
				{
					$TV_varID=$row['tmplvarid'];
					$TV_ID=$row['id'];
					$document=$modx->getDocument($id);
					
					// check created date range
					if($document['createdon']<$searchOptions['createdon_start_time'] or $document['createdon']>$searchOptions['createdon_end_time']) continue;

					// check edited date range
					if($document['editedon']<$searchOptions['editedon_start_time'] or $document['editedon']>$searchOptions['editedon_end_time']) continue;
					
					// save results in our results array
					$searchResultsArray[$id]['id']=$id;
					$searchResultsArray[$id]['pagetitle']=$document['pagetitle'];
					$searchResultsArray[$id]['longtitle']=$document['longtitle'];
					$searchResultsArray[$id]['published']=$document['published'];
					$searchResultsArray[$id]['hidemenu']=$document['hidemenu'];
					$searchResultsArray[$id]['createdon']=$document['createdon'];
					$searchResultsArray[$id]['editedon']=$document['editedon'];
					$searchResultsArray[$id]['found_in'].=", tv_".$TV_Names[$TV_varID]['name'];
					
					// replace
					if($searchOptions['replace_mode'] and $searchField!="id") replace($search['string'], $searchOptions['replace'], $TV_ID, 'value', $row['value'], $tableTV_Content);
				}
			}
		}

		return $searchResultsArray;
	}

	function getResourcesResults($area, $search, $searchOptions)
	{
		global $modx;

		// determine DB tables
		$dbShortTableArray['Templates']="site_templates";
		$dbShortTableArray['TVs']="site_tmplvars";
		$dbShortTableArray['Chunks']="site_htmlsnippets";
		$dbShortTableArray['Snippets']="site_snippets";
		$dbShortTableArray['Plugins']="site_plugins";
		$dbShortTableArray['Modules']="site_modules";
		$dbShortTable=$dbShortTableArray[$area];

		// determine SQL data selection 
		$sqlSelectionArray['Templates']="id, templatename, description, content";
		$sqlSelectionArray['TVs']="id, name, description, type, elements, display, display_params, default_text";
		$sqlSelectionArray['Chunks']="id, name, description, snippet";
		$sqlSelectionArray['Snippets']="id, name, description, snippet, properties, moduleguid";
		$sqlSelectionArray['Plugins']="id, name, description, plugincode, properties, moduleguid";
		$sqlSelectionArray['Modules']="id, name, description, modulecode, properties, guid, resourcefile";
		$sqlSelection=$sqlSelectionArray[$area];

		// set search fields
		$searchFieldArray=split(", ", $sqlSelection);

		// search in all defined fields
		$dbTable=$modx->getFullTableName($dbShortTable);
		foreach($searchFieldArray as $searchField)
		{
			// Filter: Check where to search in and where to skip
			if(!$searchOptions['resources_id'] and $searchField=="id") continue;
			if(!$searchOptions['resources_name'] and ($searchField=="name" or $searchField=="templatename")) continue;
			if(!$searchOptions['resources_description'] and $searchField=="description") continue;
			if(!$searchOptions['resources_other'] and $searchField!="id" and $searchField!="name" and $searchField!="templatename" and $searchField!="description") continue;
			
			// get SQL WHERE else continue
			if($search['string']!="ALL")
			{
				$sqlWhere=getSqlWhere($search['string'], $searchField, $searchOptions);
				if(!$sqlWhere) continue;
			}

			// complete SQL query
			$sql="SELECT $sqlSelection FROM $dbTable $sqlWhere";

			// query DB via MODx DB API
			$result=$modx->db->query($sql);

			// get rows
			while($row=$modx->fetchRow($result))
			{
				$id=$row['id'];

				// save results in our results array
				foreach($searchFieldArray as $searchFieldStore)
				{
					$searchResultsArray[$id][$searchFieldStore]=$row[$searchFieldStore];
				}
				$searchResultsArray[$id]['found_in'].=", ".ucfirst($searchField);

				// replace
				if($searchOptions['replace_mode'] and $searchField!="id") replace($search['string'], $searchOptions['replace'], $id, $searchField, $row[$searchField], $dbTable);

				// jump to next row array key
				next($row);
			}
		}

		return $searchResultsArray;
	}

	function replace($searchString, $replaceString, $id, $searchFieldName, $searchFieldString, $table)
	{
		global $modx, $searchOptions;

		// take care of case _in_sensitive searches and do the PHP string replace
		if($searchOptions['case_sensitive']) $searchFieldStringReplaced=str_replace($searchString, $replaceString, $searchFieldString);
		else $searchFieldStringReplaced=str_ireplace($searchString, $replaceString, $searchFieldString);

		$searchFieldStringReplaced=mysql_real_escape_string($searchFieldStringReplaced);
		
		// update Database
		$replaceResult=$modx->db->update($searchFieldName.'="'.$searchFieldStringReplaced.'"', $table, 'id="'.$id.'"');
		
		return $replaceResult;
	}

	function getSqlWhere($searchString, $searchField, $searchOptions)
	{
		// prepare SQL Query
		$sqlWhere=mysql_real_escape_string($searchString);
		
		// take care of ID search
		if($searchField=="id" and is_numeric($searchString)) return "WHERE ".$searchField."=".$searchString;
		else if($searchField=="id") return false;

		// take care of regular expressions
		if($searchOptions['regular_expression']) return "WHERE "."$searchField REGEXP '".$sqlWhere."'";

		// take care of case sensitive search
		if($searchOptions['case_sensitive']) $caseSensitiveSQL="BINARY";
		else $caseSensitiveSQL="";

		// take care of AND NOT
		$sqlWhere=str_replace(" AND NOT ", " NOT ", $sqlWhere);

		// put together the SQL
		$sqlWhere=" $caseSensitiveSQL $searchField LIKE '%".$sqlWhere."%'";
		
		// take care of ANDs
		$sqlWhere=str_replace(" AND ", "%' AND $searchField LIKE '%", $sqlWhere);
		// take care of ANDs some possible problems with AND
		$sqlWhere=str_replace("'%AND ", "'%", $sqlWhere);
		$sqlWhere=str_replace("'% AND ", "'%", $sqlWhere);
		$sqlWhere=str_replace(" AND%'", "%'", $sqlWhere);
		$sqlWhere=str_replace(" AND %'", "%'", $sqlWhere);

		// take care of ORs
		$sqlWhere=str_replace(" OR ", "%' OR $searchField LIKE '%", $sqlWhere);
		// take care of ANDs some possible problems with OR
		$sqlWhere=str_replace("'%OR ", "'%", $sqlWhere);
		$sqlWhere=str_replace("'% OR ", "'%", $sqlWhere);
		$sqlWhere=str_replace(" OR%'", "%'", $sqlWhere);
		$sqlWhere=str_replace(" OR %'", "%'", $sqlWhere);

		// take care of NOTs
		$sqlWhere=str_replace(" NOT ", "%' AND NOT $searchField LIKE '%", $sqlWhere);

		// take care of the results limit
		if($searchOptions['entries_50']) $limit=" LIMIT 50";
		else if($searchOptions['entries_100']) $limit=" LIMIT 100";
		$sqlWhere.=$limit;
		
		return "WHERE ".$sqlWhere;
	}

	function checkparents($searchParentsArray, $parents)
	{
		// return if there are no parents
		if(!$searchParentsArray) return true;

		// check if the result document is within the searched parents and if so return true
		foreach($searchParentsArray as $searchParent) if(isset($parents[$searchParent])) return true;

		// otherwise ...
		return false;
	}

	function getAllParents($id)
	{
		global $modx;

		// go up document tree
		$counter=1;
		while($document=$modx->getParent($id))
		{
			$id=$document['id'];
			$parents[$id]=$id;
		}

		// don't forget the root parent
		$parents[0]=0;

		return $parents;
	}

	function printDocumentBranch($parentsArray)
	{
		global $modx;

		ob_start();

		// print Site
		?> &gt; <a href="index.php?a=2"><?php echo $modx->config['site_name']; ?> (0)</a><?php 

		if(is_array($parentsArray)) 
		{
			// reverse branch array create output
			$parentsArray=array_reverse($parentsArray);

			foreach($parentsArray as $id)
			{
				$document=$modx->getDocument($id);
				$urlEdit="index.php?a=27&amp;id=".$id;

				// print parents
				if($id>0) { ?> &gt; <a href="<?php echo $urlEdit; ?>"><?php echo $document['pagetitle']; ?> (<?php echo $id?>)</a><?php  }
			}
		}

		$output.=ob_get_clean();
		return substr($output, 5);
	}

	function printHistory($type)
	{
		// print select box start
		?>
			<select id="<?php echo $type; ?>_history" onchange="$('<?php echo $type; ?>string').value=$('<?php echo $type; ?>_history').value;">
		<?php 

		if($_SESSION[$type.'_history'])
		{
			// get and unique history
			$historyArray=explode(";", $_SESSION[$type.'_history']);
			$historyArray=array_unique($historyArray);

			// build new history
			foreach($historyArray as $entry)
			{
				if($entry=="") continue;

				$newhistoryString.=";".$entry;
			}

			// save new history string
			$_SESSION[$type.'_history']=$newhistoryString;

			// output
			?>
				<option value="">История <?php if (ucfirst($type) == "Search"){echo "поиска";} elseif (ucfirst($type) == "Replace") {echo "замены";} ?> ...</option>
			<?php 

			foreach($historyArray as $entry)
			{
				$entry=htmlentities($entry, false, "UTF-8");
				if($entry=="") continue;

				?>
					<option value="<?php echo $entry; ?>"><?php echo $entry; ?></option>
				<?php 
			}
		}
		else
		{
			?>
				<option value=""><?php echo ucfirst($type); ?> История (пусто)</option>
			<?php 
		}

		// print select box end
		?>
			</select>
		<?php 
	}

		
?>