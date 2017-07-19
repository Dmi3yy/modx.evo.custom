<?php
if(!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') {
	exit();
}
unset($_SESSION['itemname']); // clear this, because it's only set for logging purposes
// Catch $_REQUEST['searchid'] for compatibility
if(isset($_REQUEST['searchid'])) {
	$_REQUEST['searchfields'] = $_REQUEST['searchid'];
	$_POST['searchfields'] = $_REQUEST['searchid'];
}
?>

	<script language="javascript">
		var actions = {
			cancel: function() {
				documentDirty = false;
				document.location.href = 'index.php?a=2';
			}
		};
	</script>

	<h1>
		<i class="fa fa-search"></i><?= $_lang['search_criteria'] ?>
	</h1>

<?= $_style['actionbuttons']['static']['cancel'] ?>

	<div class="section">
		<div class="sectionBody">
			<form action="index.php?a=71" method="post" name="searchform" enctype="multipart/form-data">
				<table width="100%" border="0">
					<tr>
						<td width="180"><?= $_lang['search_criteria_top'] ?></td>
						<td width="0">&nbsp;</td>
						<td width="120">
							<input name="searchfields" type="text" size="50" value="<?= (isset($_REQUEST['searchfields']) ? $_REQUEST['searchfields'] : '') ?>" />
						</td>
						<td><?= $_lang['search_criteria_top_msg'] ?></td>
					</tr>
					<tr>
						<td width="120"><?= $_lang['search_criteria_template_id'] ?></td>
						<td width="20">&nbsp;</td>
						<?php
						$rs = $modx->db->select('*', $modx->getFullTableName('site_templates'));
						$option[] = '<option value="">No selected</option>';
						$templateid = (isset($_REQUEST['templateid']) && $_REQUEST['templateid'] !== '') ? intval($_REQUEST['templateid']) : '';
						$selected = $templateid === 0 ? ' selected="selected"' : '';
						$option[] = '<option value="0"' . $selected . '>(blank)</option>';
						while($row = $modx->db->getRow($rs)) {
							$templatename = htmlspecialchars($row['templatename'], ENT_QUOTES, $modx->config['modx_charset']);
							$selected = $row['id'] == $templateid ? ' selected="selected"' : '';
							$option[] = sprintf('<option value="%s"%s>%s(%s)</option>', $row['id'], $selected, $templatename, $row['id']);
						}
						$tpls = sprintf('<select name="templateid">%s</select>', join("\n", $option));
						?>
						<td width="120"><?= $tpls ?></td>
						<td><?= $_lang['search_criteria_template_id_msg'] ?></td>
					</tr>
					<tr>
						<td>URL</td>
						<td>&nbsp;</td>
						<td>
							<input name="url" type="text" size="50" value="<?= (isset($_REQUEST['url']) ? $_REQUEST['url'] : '') ?>" />
						</td>
						<td><?= $_lang['search_criteria_url_msg'] ?></td>
					</tr>
					<tr>
						<td><?= $_lang['search_criteria_content'] ?></td>
						<td>&nbsp;</td>
						<td>
							<input name="content" type="text" size="50" value="<?= (isset($_REQUEST['content']) ? $_REQUEST['content'] : '') ?>" />
						</td>
						<td><?= $_lang['search_criteria_content_msg'] ?></td>
					</tr>
					<tr>
						<td colspan="4">
							<ul class="actionButtons">
								<li>
									<a class="default" href="javascript:;" onClick="document.searchform.submitok.click();"><i class="<?= $_style["actions_search"] ?>"></i> <span><?= $_lang['search'] ?></span>
									</a>
								</li>
								<li>
									<a href="index.php?a=2"><i class="<?= $_style["actions_cancel"] ?>"></i> <span><?= $_lang['cancel'] ?></span>
									</a>
								</li>
							</ul>
						</td>
					</tr>
				</table>
				<input type="submit" value="Search" name="submitok" style="display:none" />
			</form>
		</div>
	</div>
<?php
//TODO: сделать поиск по уму пока сделаю что б одно поле было для id,longtitle,pagetitle,alias далее нужно думаю добавить что б и в елементах искало
if(isset($_REQUEST['submitok'])) {
	$tbl_site_content = $modx->getFullTableName('site_content');

	$searchfields = htmlentities($_POST['searchfields'], ENT_QUOTES, $modx_manager_charset);
	$searchlongtitle = $modx->db->escape($_REQUEST['searchfields']);
	$search_alias = $modx->db->escape($_REQUEST['searchfields']);
	$templateid = isset($_REQUEST['templateid']) && $_REQUEST['templateid'] !== '' ? intval($_REQUEST['templateid']) : '';
	$searchcontent = $modx->db->escape($_REQUEST['content']);

	$sqladd = "";

	// Handle Input "Search by exact URL"
	$idFromAlias = false;
	if(isset($_REQUEST['url']) && $_REQUEST['url'] !== '') {
		$url = $modx->db->escape($_REQUEST['url']);
		$friendly_url_suffix = $modx->config['friendly_url_suffix'];
		$base_url = $modx->config['base_url'];
		$site_url = $modx->config['site_url'];
		$url = preg_replace('@' . $friendly_url_suffix . '$@', '', $url);
		if($url[0] === '/') {
			$url = preg_replace('@^' . $base_url . '@', '', $url);
		}
		if(substr($url, 0, 4) === 'http') {
			$url = preg_replace('@^' . $site_url . '@', '', $url);
		}
		$idFromAlias = $modx->getIdFromAlias($url);
	}

	// Handle Input "Search in main fields"
	if($searchfields != '') {
		if(ctype_digit($searchfields)) {
			$sqladd .= "id='{$searchfields}'";
		}
		if($idFromAlias) {
			$sqladd .= $sqladd != '' ? ' OR ' : '';
			$sqladd .= "id='{$idFromAlias}'";
		}

		$sqladd = $sqladd ? "({$sqladd})" : $sqladd;

		if(!ctype_digit($searchfields)) {
			$sqladd .= $sqladd != '' ? ' AND' : '';
			$sqladd .= " pagetitle LIKE '%{$searchfields}%'";
			$sqladd .= " OR longtitle LIKE '%{$searchlongtitle}%'";
			$sqladd .= " OR description LIKE '%{$searchlongtitle}%'";
			$sqladd .= " OR introtext LIKE '%{$searchlongtitle}%'";
			$sqladd .= " OR menutitle LIKE '%{$searchlongtitle}%'";
			$sqladd .= " OR alias LIKE '%{$search_alias}%'";
		}
	} else if($idFromAlias) {
		$sqladd .= " id='{$idFromAlias}'";
	}

	// Handle Input "Search by template ID"
	if($templateid !== '') {
		$sqladd .= $sqladd != '' ? ' AND' : '';
		$sqladd .= " template='{$templateid}'";
	}

	// Handle Input "Search by content"
	if($searchcontent !== '') {
		$sqladd .= $sqladd != '' ? ' AND' : '';
		$sqladd .= $searchcontent != '' ? " content LIKE '%{$searchcontent}%'" : '';
	}

	$fields = 'id, contenttype, pagetitle, longtitle, description, introtext, menutitle, deleted, published, isfolder, type';
	$where = $sqladd;

	if($where) {
		$rs = $modx->db->select($fields, $tbl_site_content, $where, 'id');
		$limit = $modx->db->getRecordCount($rs);
	} else {
		$limit = 0;
	}

	?>
	<div class="section">
		<div class="sectionHeader"><?= $_lang['search_results'] ?></div>
		<div class="sectionBody">
			<?php
			if($_GET['ajax'] != 1) {

				if($limit < 1) {
					echo $_lang['search_empty'];
				} else {
					printf('<p>' . $_lang['search_results_returned_msg'] . '</p>', $limit);
					?>
					<script type="text/javascript" src="media/script/tablesort.js"></script>
					<table border="0" cellpadding="2" cellspacing="0" class="sortabletable sortable-onload-2 rowstyle-even" id="table-1" width="90%">
						<thead>
						<tr bgcolor="#CCCCCC">
							<th width="20"></th>
							<th class="sortable"><b><?= $_lang['search_results_returned_id'] ?></b></th>
							<th class="sortable"><b><?= $_lang['search_results_returned_title'] ?></b></th>
							<th class="sortable"><b><?= $_lang['search_results_returned_desc'] ?></b></th>
							<th width="20"></th>
						</tr>
						</thead>
						<tbody>
						<?php
						// icons by content type
						$icons = array(
							'application/rss+xml' => $_style["tree_page_rss"],
							'application/pdf' => $_style["tree_page_pdf"],
							'application/vnd.ms-word' => $_style["tree_page_word"],
							'application/vnd.ms-excel' => $_style["tree_page_excel"],
							'text/css' => $_style["tree_page_css"],
							'text/html' => $_style["tree_page_html"],
							'text/plain' => $_style["tree_page"],
							'text/xml' => $_style["tree_page_xml"],
							'text/javascript' => $_style["tree_page_js"],
							'image/gif' => $_style["tree_page_gif"],
							'image/jpg' => $_style["tree_page_jpg"],
							'image/png' => $_style["tree_page_png"]
						);

						while($row = $modx->db->getRow($rs)) {
							// figure out the icon for the document...
							$icon = "";
							if($row['type'] == 'reference') {
								$icon .= $_style["tree_linkgo"];
							} elseif($row['isfolder'] == 0) {
								$icon .= isset($icons[$row['contenttype']]) ? $icons[$row['contenttype']] : $_style["tree_page_html"];
							} else {
								$icon .= $_style['tree_folder_new'];
							}

							$tdClass = "";
							if($row['published'] == 0) {
								$tdClass .= ' class="unpublishedNode"';
							}
							if($row['deleted'] == 1) {
								$tdClass .= ' class="deletedNode"';
							}
							?>
							<tr>
								<td align="center">
									<a href="index.php?a=3&id=<?= $row['id'] ?>" title="<?= $_lang['search_view_docdata'] ?>"><i class="<?= $_style['icons_resource_overview'] ?>" /></i></a>
								</td>
								<td><?= $row['id'] ?></td>
								<?php
								if(function_exists('mb_strlen') && function_exists('mb_substr')) {
									?>
									<td<?= $tdClass ?>>
										<a href="index.php?a=27&id=<?= $row['id'] ?>"><?= mb_strlen($row['pagetitle'], $modx_manager_charset) > 70 ? mb_substr($row['pagetitle'], 0, 70, $modx_manager_charset) . "..." : $row['pagetitle'] ?></a>
									</td>
									<td<?= $tdClass ?>><?= mb_strlen($row['description'], $modx_manager_charset) > 70 ? mb_substr($row['description'], 0, 70, $modx_manager_charset) . "..." : $row['description'] ?></td>
									<?php
								} else {
									?>
									<td<?= $tdClass ?>><?= strlen($row['pagetitle']) > 20 ? substr($row['pagetitle'], 0, 20) . '...' : $row['pagetitle'] ?></td>
									<td<?= $tdClass ?>><?= strlen($row['description']) > 35 ? substr($row['description'], 0, 35) . '...' : $row['description'] ?></td>
									<?php
								}
								?>
								<td align="center"><?= $icon ?></td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
					<?php

				}
			} else {
				$output = '';

				//docs
				$docscounts = $modx->db->getRecordCount($rs);
				if($docscounts > 0) {
					$output .= '<li><b><i class="fa fa-sitemap"></i> ' . $_lang["manage_documents"] . ' (' . $docscounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=27&id=' . $row['id'] . '" id="content_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['pagetitle'] . ' <small>(' . $row['id'] . ')</small>', $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}

				//templates
				$rs = $modx->db->select("id,templatename", $modx->getFullTableName('site_templates'), "`id` like '%" . $searchfields . "%' 
        OR `templatename` like '%" . $searchfields . "%' 
        OR `description` like '%" . $searchfields . "%' 
        OR `content` like '%" . $searchfields . "%'");
				$templatecounts = $modx->db->getRecordCount($rs);
				if($templatecounts > 0) {
					$output .= '<li><b><i class="fa fa-newspaper-o"></i> ' . $_lang["manage_templates"] . ' (' . $templatecounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=16&id=' . $row['id'] . '" id="templates_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['templatename'], $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}

				//tvs
				$rs = $modx->db->select("id,name", $modx->getFullTableName('site_tmplvars'), "`id` like '%" . $searchfields . "%' 
        OR `name` like '%" . $searchfields . "%' 
        OR `description` like '%" . $searchfields . "%' 
        OR `type` like '%" . $searchfields . "%' 
        OR `elements` like '%" . $searchfields . "%' 
        OR `display` like '%" . $searchfields . "%' 
        OR `display_params` like '%" . $searchfields . "%' 
        OR `default_text` like '%" . $searchfields . "%'");
				$tvscounts = $modx->db->getRecordCount($rs);
				if($tvscounts > 0) {
					$output .= '<li><b><i class="fa fa-list-alt"></i> ' . $_lang["settings_templvars"] . ' (' . $tvscounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=301&id=' . $row['id'] . '" id="tmplvars_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['name'], $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}
				//

				//Chunks
				$rs = $modx->db->select("id,name", $modx->getFullTableName('site_htmlsnippets'), "`id` like '%" . $searchfields . "%' 
        OR `name` like '%" . $searchfields . "%' 
        OR `description` like '%" . $searchfields . "%'     
        OR `snippet` like '%" . $searchfields . "%'");
				$chunkscounts = $modx->db->getRecordCount($rs);
				if($chunkscounts > 0) {
					$output .= '<li><b><i class="fa fa-th-large"></i> ' . $_lang["manage_htmlsnippets"] . ' (' . $chunkscounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=78&id=' . $row['id'] . '" id="htmlsnippets_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['name'], $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}

				//Snippets
				$rs = $modx->db->select("id,name", $modx->getFullTableName('site_snippets'), "`id` like '%" . $searchfields . "%' 
        OR `name` like '%" . $searchfields . "%' 
        OR `description` like '%" . $searchfields . "%' 
        OR `snippet` like '%" . $searchfields . "%'  
        OR `properties` like '%" . $searchfields . "%'      
        OR `moduleguid` like '%" . $searchfields . "%'");
				$snippetscounts = $modx->db->getRecordCount($rs);
				if($snippetscounts > 0) {
					$output .= '<li><b><i class="fa fa-code"></i> ' . $_lang["manage_snippets"] . ' (' . $snippetscounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=22&id=' . $row['id'] . '" id="snippets_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['name'], $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}
				//plugins
				$rs = $modx->db->select("id,name", $modx->getFullTableName('site_plugins'), "`id` like '%" . $searchfields . "%' 
        OR `name` like '%" . $searchfields . "%' 
        OR `description` like '%" . $searchfields . "%' 
        OR `plugincode` like '%" . $searchfields . "%'  
        OR `properties` like '%" . $searchfields . "%'      
        OR `moduleguid` like '%" . $searchfields . "%'");
				$pluginscounts = $modx->db->getRecordCount($rs);
				if($pluginscounts > 0) {
					$output .= '<li><b><i class="fa fa-plug"></i> ' . $_lang["manage_plugins"] . ' (' . $pluginscounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=102&id=' . $row['id'] . '" id="plugins_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['name'], $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}
				//modules
				$rs = $modx->db->select("id,name", $modx->getFullTableName('site_modules'), "`id` like '%" . $searchfields . "%' 
        OR `name` like '%" . $searchfields . "%' 
        OR `description` like '%" . $searchfields . "%' 
        OR `modulecode` like '%" . $searchfields . "%'  
        OR `properties` like '%" . $searchfields . "%'  
        OR `guid` like '%" . $searchfields . "%'      
        OR `resourcefile` like '%" . $searchfields . "%'");
				$modulescounts = $modx->db->getRecordCount($rs);
				if($modulescounts > 0) {
					$output .= '<li><b><i class="fa fa-cogs"></i> ' . $_lang["modules"] . ' (' . $modulescounts . ')</b></li>';
					while($row = $modx->db->getRow($rs)) {
						$output .= '<li><a href="index.php?a=108&id=' . $row['id'] . '" id="modules_' . $row['id'] . '" target="main">' . highlightingCoincidence($row['name'], $_REQUEST['searchfields']) . $_style['icons_external_link'] . '</a></li>';
					}
				}

				echo $output ? '<div class="ajaxSearchResults"><ul>' . $output . '</ul></div>' : '1';
			}

			?>
		</div>
	</div>
	<?php
}

function highlightingCoincidence($text, $search) {
	$regexp = '!(' . str_replace(array(
			'(',
			')'
		), array(
			'\(',
			'\)'
		), $search) . ')!isu';
	return preg_replace($regexp, '<span class="text-danger">$1</span>', $text);
}
