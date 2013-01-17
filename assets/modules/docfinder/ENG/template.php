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
	// 
	// 
	// ----------------------------------------------------------------
	// :: Copyright & Licencing
	// ----------------------------------------------------------------
	// 
	//   GNU General Public License (GPL - http://www.gnu.org/copyleft/gpl.html)
	// 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php echo $dir; ?> lang="<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>"> 
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="language" content="<?php echo $lang; ?>" />
		<meta name="author" content="Bogdan Günther - Medianotions, www.medianotions.de" />
		
		<title>Doc Finder 1.6</title>
		
		<!-- loading Manager Theme -->
		<link rel="stylesheet" type="text/css" href="media/style<?php echo $theme; ?>/style.css" />
		
		<!-- loading Doc Finder CSS -->
		<link rel="stylesheet" type="text/css" href="../assets/modules/docfinder/styles.css" />

		<!-- Mootools JS Framework (http://www.mootools.net) -->
		<script src="../assets/modules/docfinder/js/mootools-1.11.js" type="text/javascript"></script>
		
		<!-- loading Manager JS functions -->
		<script src="media/script/mootools/moodx.js" type="text/javascript"></script>
		
		<!-- loading Doc Finder JS functions -->
		<script type="text/javascript" src="../assets/modules/docfinder/js/functions.js"></script>
		
		<!-- loading modified tab script -->
		<script type="text/javascript" src="../assets/modules/docfinder/js/tabpane.js"></script>
		
		<!-- loading sortable table script by phatfusion (http://www.phatfusion.net) -->
		<script type="text/javascript" src="../assets/modules/docfinder/js/sortableTable/sortableTable.js"></script>
		
		<!-- loading Vista-like Ajax Calendar script by R. Schoo (http://www.base86.com) -->
		<script type="text/javascript" src="../assets/modules/docfinder/js/vlaCal.v2/jslib/vlaCal-v2.js"></script>
		<link type="text/css" media="screen" href="../assets/modules/docfinder/js/vlaCal.v2/styles/vlaCal-v2.css" rel="stylesheet" />
		<link type="text/css" media="screen" href="../assets/modules/docfinder/js/vlaCal.v2/styles/vlaCal-v2-apple_widget.css" rel="stylesheet" />
	</head>

	<body>
		<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post" target="main" id="docfinder" name="docfinder">
			
			<!-- headline -->
			<h1>Doc Finder 1.6</h1>
			<div id="actions">
				<ul class="actionButtons">
					<li id="Button1"><a href="#" onclick="document.location.href='index.php?a=106';"><img src="media/style/MODxCarbon/images/icons/stop.png" /> Close Doc Finder</a></li>
				</ul>
			</div>
			
			<!-- search options headline -->
			<div class="sectionHeader">Search options</div>
			
			<!-- search options content -->
			<div class="sectionBody">
				
				<?php if($modx->config['modx_charset']!="UTF-8") { ; ?>
				<!-- charset warning -->
				<p class="warning"><strong>Warning:</strong> Doc Finder needs the MODx charset to be set to UTF-8 to run properly. Search and replace for special characters will not work correctly.</p><br>
				<?php } ; ?>
					
				<!-- Tab page container -->
				<div class="tab-pane" id="tabPaneSearchOptions">
					<script type="text/javascript">
						var tpSearchOptions=new WebFXTabPane($('tabPaneSearchOptions'));
					</script>
					
					<!-- Tab pane search and replace -->
					<div class="tab-page" id="tpSearchOptions_searchAndReplace">
						
						<h2 class="tab">Search &amp; replace</h2>
						
						<!-- string search line -->
						<p>
							<!-- Label and input field -->
							<label for="searchstring" class="text_label" id="search_field_text">String search:</label>
							<input type="text" class="text" name="searchstring" id="searchstring" value="<?php echo $search['string']; ?>" onkeyup="triggerSubmitButtons();" />
						
							<!-- help button search -->
							<?php $helpText="You can use AND, OR and NOT in your search and blanks for whole word searches. Alternatively you can use regular expressions if you activate it in the advanced search. Use ALL to get all results – if you are looking for a date range for example."; ?>
							<img src="media/style<?php echo $theme; ?>/images/icons/b02_trans.gif" onmouseover="this.src='media/style<?php echo $theme; ?>/images/icons/b02.gif';" onmouseout="this.src='media/style<?php echo $theme; ?>/images/icons/b02_trans.gif';" alt="<?php echo $helpText; ?>" class="icon help" onclick="alert(this.alt);" />
						
							<!-- search history -->
							<span id="search_history_box">
								<?php echo printHistory("search"); ?>
							</span>
						
							<!-- hidden fields for form and session control -->						
							<input type="hidden" name="checkform" id="checkform" value="load" />
							<input type="hidden" name="update_session" id="update_session" value="0" />
						</p>
						
						<!-- string replace -->
						<p class="replace">
							<!-- Label and input field -->
							<label for="replacestring" class="text_label">String replace:</label>
							<input type="text" class="text" name="replacestring" id="replacestring" value="<?php echo $searchOptions['replace']; ?>" />
							
							<!-- hidden field replace mode -->						
							<input type="hidden" name="replace_mode" id="replace_mode" value="" />

							<!-- help button replace -->
							<?php $helpText="Replaces all instances of the search string with the replace string. Can not be combined with logical operators or regular expressions. The document id is excluded by the replacement."; ?>
							<img src="media/style<?php echo $theme; ?>/images/icons/b02_trans.gif" onmouseover="this.src='media/style<?php echo $theme; ?>/images/icons/b02.gif';" onmouseout="this.src='media/style<?php echo $theme; ?>/images/icons/b02_trans.gif';" alt="<?php echo $helpText; ?>" class="icon help" onclick="alert(this.alt);" />
							
							<!-- replace history -->
							<span id="replace_history_box">
								<?php echo printHistory("replace"); ?>
							</span>
						</p>
						
						<script type="text/javascript">
							tpSearchOptions.addTabPage($('tpSearchOptions_searchAndReplace'));
						</script>
					</div>
					
					<!-- Tab pane search and replace -->
					<div class="tab-page" id="tpSearchOptions_generalOptions">
						
						<h2 class="tab">General options</h2>
						
						<!-- search places -->
						<p class="options">
							<strong class="text_label">Search places:</strong>
							<label><input type="checkbox" class="checkbox" name="search_place_selector" id="search_place_selector" <?php echo $searchOptions['search_place_selector']; ?>  onclick="checkboxSelector(this.id);" /><strong>[All / None]</strong></label>
							<?php 
								foreach($searchPlacesArray as $searchPlace)
								{
									?>
										<label><input type="checkbox" class="checkbox" name="search_place_<?php echo $searchPlace['id']; ?>" id="search_place_<?php echo $searchPlace['id']; ?>" <?php echo $searchOptions['search_place_'.$searchPlace['id']]; ?> /><?php echo $searchPlace['name']; ?></label>
									<?php 
								}
							; ?>
						</p>
						
						<!-- search options -->
						<p class="options">
							<strong class="text_label">Search options:</strong>
							<label><input type="checkbox" class="checkbox" name="case_sensitive" <?php echo $searchOptions['case_sensitive']; ?> />Case sensitive</label>
							<label><input type="checkbox" class="checkbox" name="regular_expression" id="regular_expression" <?php echo $searchOptions['regular_expression']; ?> />Regular expression</label>
							<label><input type="checkbox" class="checkbox" name="sortable_tables" id="sortable_tables" <?php echo $searchOptions['sortable_tables']; ?> />Sortable tables</label>
							
							<!-- help sortable tables -->
							<?php $helpText="Disable the sortable tables for better performance especially when using Internet Explorer."; ?>
							<img src="media/style<?php echo $theme; ?>/images/icons/b02_trans.gif" onmouseover="this.src='media/style<?php echo $theme; ?>/images/icons/b02.gif';" onmouseout="this.src='media/style<?php echo $theme; ?>/images/icons/b02_trans.gif';" alt="<?php echo $helpText; ?>" class="icon help" onclick="alert(this.alt);" />
						</p>
						
						<script type="text/javascript">
							tpSearchOptions.addTabPage($('tpSearchOptions_generalOptions'));
						</script>
					</div>
					
					<!-- Tab pane document options -->
					<div class="tab-page" id="tpSearchOptions_DocumentOptions">
						
						<h2 class="tab">Document options</h2>
						
						<!-- document parents -->
						<p class="parents">							
							<label for="parents" class="text_label">Document parents:</label>
							<input type="text" class="text" name="parents" id="parents" value="<?php echo $search['parents']; ?>" />

							<!-- help button -->
							<?php $helpText="Enter parent IDs here. You can define more than one parent by separating the parent IDs with commas."; ?>
							<img src="media/style<?php echo $theme; ?>/images/icons/b02_trans.gif" onmouseover="this.src='media/style<?php echo $theme; ?>/images/icons/b02.gif';" onmouseout="this.src='media/style<?php echo $theme; ?>/images/icons/b02_trans.gif';" alt="<?php echo $helpText; ?>" class="icon help" onclick="alert(this.alt);" />
						</p>
						
						<!-- search in -->
						<p class="options search_in">
							<strong class="text_label">Search in:</strong>
							<label><input type="checkbox" class="checkbox" name="documents_search_in_selector" id="documents_search_in_selector" <?php echo $searchOptions['documents_search_in_selector']; ?>  onclick="checkboxSelector(this.id);" /><strong>[All / None]</strong></label>
							<label><input type="checkbox" class="checkbox" id="df_id" name="df_id" <?php echo $searchOptions['id']; ?> />ID</label>
							<label><input type="checkbox" class="checkbox" id="pagetitle" name="pagetitle" <?php echo $searchOptions['pagetitle']; ?> />Pagetitle</label>
							<label><input type="checkbox" class="checkbox" id="longtitle" name="longtitle" <?php echo $searchOptions['longtitle']; ?> />Longtitle</label>
							<label><input type="checkbox" class="checkbox" id="description" name="description" <?php echo $searchOptions['description']; ?> />Description</label>
							<label><input type="checkbox" class="checkbox" id="alias" name="alias" <?php echo $searchOptions['alias']; ?> />Alias</label>
							<label><input type="checkbox" class="checkbox" id="introtext" name="introtext" <?php echo $searchOptions['introtext']; ?> />Introtext</label>
							<label><input type="checkbox" class="checkbox" id="menutitle" name="menutitle" <?php echo $searchOptions['menutitle']; ?> />Menutitle</label>
							<label><input type="checkbox" class="checkbox" id="content" name="content" <?php echo $searchOptions['content']; ?> />Content</label>
							<label><input type="checkbox" class="checkbox" id="tvs" name="tvs" <?php echo $searchOptions['tvs']; ?> />TVs</label>
						</p>
						
						<script type="text/javascript">
							tpSearchOptions.addTabPage($('tpSearchOptions_DocumentOptions'));
						</script>
					</div>
					
					<!-- Tab pane resource options -->
					<div class="tab-page" id="tpSearchOptions_ResourceOptions">
						
						<h2 class="tab">Resource options</h2>
						
						<!-- search in -->
						<p class="options search_in">
							<strong class="text_label">Search in:</strong>
							<label><input type="checkbox" class="checkbox" name="resources_search_in_selector" id="resources_search_in_selector" <?php echo $searchOptions['resources_search_in_selector']; ?>  onclick="checkboxSelector(this.id);" /><strong>[All / None]</strong></label>
							<label><input type="checkbox" class="checkbox" id="resources_id" name="resources_id" <?php echo $searchOptions['resources_id']; ?> />ID</label>
							<label><input type="checkbox" class="checkbox" id="resources_name" name="resources_name" <?php echo $searchOptions['resources_name']; ?> />Name</label>
							<label><input type="checkbox" class="checkbox" id="resources_description" name="resources_description" <?php echo $searchOptions['resources_description']; ?> />Description</label>
							<label><input type="checkbox" class="checkbox" id="resources_other" name="resources_other" <?php echo $searchOptions['resources_other']; ?> />All other fields</label>
						</p>
							
						<script type="text/javascript">
							tpSearchOptions.addTabPage($('tpSearchOptions_ResourceOptions'));
						</script>
					</div>
					
					<!-- Tab pane date options -->
					<div class="tab-page" id="tpSearchOptions_DateOptions">
						
						<h2 class="tab">Date options</h2>
						
						<!-- date range created on -->
						<p class="date_range createdon">
							<label for="createdon_start" class="text_label">Created between:</label>
							<input type="text" class="text" name="createdon_start" id="createdon_start" value="<?php echo $searchOptions['createdon_start']; ?>" />
							<img src="media/style<?php echo $theme; ?>/images/icons/delete.gif" alt="Clear date" class="icon delete" onclick="$('createdon_start').value='';" />
							<input type="text" class="text" name="createdon_end" id="createdon_end" value="<?php echo $searchOptions['createdon_end']; ?>" />
							<img src="media/style<?php echo $theme; ?>/images/icons/delete.gif" alt="Clear date" class="icon delete" onclick="$('createdon_end').value='';" />
							
							<!-- help button replace -->
							<?php $helpText="Set a date range for the creation date. Set only the start date to get a from-search or only the end date to get an until-search."; ?>
							<img src="media/style<?php echo $theme; ?>/images/icons/b02_trans.gif" onmouseover="this.src='media/style<?php echo $theme; ?>/images/icons/b02.gif';" onmouseout="this.src='media/style<?php echo $theme; ?>/images/icons/b02_trans.gif';" alt="<?php echo $helpText; ?>" class="icon help" onclick="alert(this.alt);" />
						</p>

						<!-- date range edited on -->
						<p class="date_range editedon">
							<label for="editedon_start" class="text_label">Edited between:</label>
							<input type="text" class="text" name="editedon_start" id="editedon_start" value="<?php echo $searchOptions['editedon_start']; ?>" />
							<img src="media/style<?php echo $theme; ?>/images/icons/delete.gif" alt="Clear date" class="icon delete" onclick="$('editedon_start').value='';" />
							<input type="text" class="text" name="editedon_end" id="editedon_end" value="<?php echo $searchOptions['editedon_end']; ?>" />
							<img src="media/style<?php echo $theme; ?>/images/icons/delete.gif" alt="Clear date" class="icon delete" onclick="$('editedon_end').value='';" />
							
							<!-- help button replace -->
							<?php $helpText="Set a date range for the last edited date.. Set only the start date to get an from-search or only the end date to get an until-search."; ?>
							<img src="media/style<?php echo $theme; ?>/images/icons/b02_trans.gif" onmouseover="this.src='media/style<?php echo $theme; ?>/images/icons/b02.gif';" onmouseout="this.src='media/style<?php echo $theme; ?>/images/icons/b02_trans.gif';" alt="<?php echo $helpText; ?>" class="icon help" onclick="alert(this.alt);" />
						</p>
							
						<script type="text/javascript">
							tpSearchOptions.addTabPage($('tpSearchOptions_DateOptions'));
						</script>
					</div>
					
				</div>
				
				<!-- search and replace buttons -->
				<p id="submit" class="submit_passive">
					
					<ul class="actionButtons">
						<!-- search submit button-->
						<li id="Button1">
							<a href="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" id="submit_search">
								Search <img src="media/style<?php echo $theme; ?>/images/icons/save.png" class="go" alt="Click or press ENTER to start search." />
								<input type="submit" value="Search" />
							</a>
						</li>
					
						<!-- replace submit button-->
						<li>
							<a href="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" id="submit_replace">
								Replace <img src="media/style<?php echo $theme; ?>/images/icons/save.png" class="replace" alt="Click or press ENTER to start search." />
							</a>
						</li>
					</ul>
					
				</p>
				
			</div>
			
			<!-- results part -->
			<div class="sectionHeader">
				
				<!-- AJAX results -->
				<span id="results_info">No</span> results <span id="results_string">for &ldquo;<strong id="search_string"><?php echo htmlspecialchars($search['string']); ?></strong>&rdquo;</span> <span id="replace_info">replaced by &ldquo;<strong id="replace_string"></strong>&rdquo;</span>
				
				<!-- AJAX time info -->
				<span id="time_info">(<span id="time">&ndash;</span> seconds)</span>
				
				<!-- Number of entries selector -->
				<span id="entries">
					Number of entries:
					<label><input type="radio" class="radio" name="entries" value="50" <?php echo $searchOptions['entries_50']; ?> />50</label>
					<label><input type="radio" class="radio" name="entries" value="100" <?php echo $searchOptions['entries_100']; ?> />100</label>
					<label><input type="radio" class="radio" name="entries" value="All" <?php echo $searchOptions['entries_All']; ?> />All</label>
				</span>
			</div>
			<div class="sectionBody">
				
				<!-- AJAX load indicator -->
				<div id="ajax_load_indicator">
					<img src="../assets/modules/docfinder/images/ajax-loader.gif" width="220" height="19" alt="Ajax Loader" class="ajax_loader" />
					<span>Searching ...</span> <a href="#" id="cancel_search" title="Cancel search"><img src="media/style<?php echo $theme; ?>/images/icons/delete.gif" alt="Cancel search" /> Cancel search</a>				
				</div>
				
				<!-- AJAX results container -->
				<div id="results_container"><?php printResultTabs($search, $searchOptions, $theme, $searchPlacesArray); ?></div>	
			</div>	
			
		</form>
	</body>
</html>