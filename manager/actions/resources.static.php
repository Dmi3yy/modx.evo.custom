<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<script type="text/javascript">
	function confirmDelete() {
		return confirm("<?php echo $_lang['confirm_delete_category'] ?>");
	}
	function deleteCategory(catid) {
	    if (confirmDelete())
	    {
	        window.location.href="index.php?a=501&catId="+catid;
	        return false;
	    }
	}
</script>

<h1><?php echo $_lang['element_management']; ?></h1>

<div class="sectionBody">
<div class="tab-pane" id="resourcesPane">
	<script type="text/javascript">
		tpResources = new WebFXTabPane( document.getElementById( "resourcesPane" ), <?php echo $modx->config['remember_last_tab'] == 0 ? 'false' : 'true'; ?> );
	</script>

<!-- Templates -->
<?php 	if($modx->hasPermission('new_template') || $modx->hasPermission('edit_template')) { ?>
    <div class="tab-page" id="tabTemplates">
    	<h2 class="tab"><?php echo $_lang["manage_templates"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabTemplates" ) );</script>
		<p><?php echo $_lang['template_management_msg']; ?></p>
		<ul class="actionButtons">
			<li><a href="index.php?a=19"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['new_template']; ?></a></li>
		</ul>
		<?php echo createResourceList('site_templates',16,'templatename'); ?>
	</div>
<?php } ?>

<!-- Template variables -->
<?php 	if($modx->hasPermission('new_template') || $modx->hasPermission('edit_template')) { ?>
    <div class="tab-page" id="tabVariables">
    	<h2 class="tab"><?php echo $_lang["tmplvars"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabVariables" ) );</script>
		<!--//
			Modified By Raymond for Template Variables
			Added by Apodigm 09-06-2004- DocVars - web@apodigm.com
		-->
		<p><?php echo $_lang['tmplvars_management_msg']; ?></p>
			<ul class="actionButtons">
				<li><a href="index.php?a=300"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['new_tmplvars']; ?></a></li>
            </ul>
            <?php echo createResourceList('site_tmplvars',301); ?>
	</div>
<?php } ?>

<!-- chunks -->
<?php 	if($modx->hasPermission('new_chunk') || $modx->hasPermission('edit_chunk')) { ?>
    <div class="tab-page" id="tabChunks">
    	<h2 class="tab"><?php echo $_lang["manage_htmlsnippets"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabChunks" ) );</script>
		<p><?php echo $_lang['htmlsnippet_management_msg']; ?></p>

		<ul class="actionButtons">
			<li><a href="index.php?a=77"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['new_htmlsnippet']; ?></a></li>
		</ul>
		<?php echo createResourceList('site_htmlsnippets',78); ?>
	</div>
<?php } ?>

<!-- snippets -->
<?php 	if($modx->hasPermission('new_snippet') || $modx->hasPermission('edit_snippet')) { ?>
    <div class="tab-page" id="tabSnippets">
    	<h2 class="tab"><?php echo $_lang["manage_snippets"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabSnippets" ) );</script>
		<p><?php echo $_lang['snippet_management_msg']; ?></p>

		<ul class="actionButtons">
			<li><a href="index.php?a=23"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['new_snippet']; ?></a></li>
		</ul>
		<?php echo createResourceList('site_snippets',22); ?>
	</div>
<?php } ?>

<!-- plugins -->
<?php 	if($modx->hasPermission('new_plugin') || $modx->hasPermission('edit_plugin')) { ?>
    <div class="tab-page" id="tabPlugins">
    	<h2 class="tab"><?php echo $_lang["manage_plugins"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabPlugins" ) );</script>
		<p><?php echo $_lang['plugin_management_msg']; ?></p>

		<ul class="actionButtons">
			<li><a href="index.php?a=101"><img src="<?php echo $_style["icons_add"] ?>" /> <?php echo $_lang['new_plugin']; ?></a></li>
			<?php if($modx->hasPermission('save_plugin')) { ?><li><a href="index.php?a=100"><img src="<?php echo $_style["icons_edit_document"] ?>" /> <?php echo $_lang['plugin_priority']; ?></a></li><?php } ?>
		</ul>
		<?php echo createResourceList('site_plugins',102); ?>
	</div>
<?php } ?>

<!-- category view -->
    <div class="tab-page" id="tabCategory">
    	<h2 class="tab"><?php echo $_lang["element_categories"] ?></h2>
    	<script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabCategory" ) );</script>
		<p><?php echo $_lang['category_msg']; ?></p>
		<br />
		<ul>
		<?php echo createCategoryList(); ?>
		</ul>
	</div>
</div>
</div>

<?php
function createResourceList($resourceTable,$action,$nameField = 'name')
{
	global $modx, $_lang;
	
	$tbl_elm = $modx->getFullTableName($resourceTable);
	$tbl_categories = $modx->getFullTableName('categories');
	
	switch($resourceTable)
	{
		case 'site_plugins':
			$add_field = "{$tbl_elm}.disabled,";
			break;
		case 'site_htmlsnippets':
			$add_field = "{$tbl_elm}.published,";
			break;
		default:
			$add_field = '';
	}
	
	$fields = "{$add_field} {$tbl_elm}.{$nameField} as name, {$tbl_elm}.id, {$tbl_elm}.description, {$tbl_elm}.locked, if(isnull({$tbl_categories}.category),'{$_lang['no_category']}',{$tbl_categories}.category) as category";
	$from   ="{$tbl_elm} left join {$tbl_categories} on {$tbl_elm}.category = {$tbl_categories}.id";
	if($resourceTable == 'site_plugins')
	{
		$orderby = "{$tbl_elm}.disabled ASC,6,2";
	}
	elseif($resourceTable == 'site_htmlsnippets')
	{
		$orderby = "{$tbl_elm}.published DESC,6,2";
	}
	else $orderby = '5,1';

	$rs = $modx->db->select($fields,$from,'',$orderby);
	$limit = $modx->db->getRecordCount($rs);
	if($limit<1)
	{
		return $_lang['no_results'];
	}
	$preCat = '';
	$insideUl = 0;
	$output = '<ul>';
	while($row = $modx->db->getRow($rs))
	{
		$row['category'] = stripslashes($row['category']); //pixelchutes
		if ($preCat !== $row['category'])
		{
			$output .= $insideUl? '</ul>': '';
			$output .= '<li><strong>'.$row['category'].'</strong><ul>';
			$insideUl = 1;
		}
		if ($resourceTable === 'site_plugins')
		{
			$class = $row['disabled'] ? 'class="disabledPlugin"' : '';
		}
		elseif ($resourceTable === 'site_htmlsnippets')
		{
			$class = ($row['published']==='0') ? 'class="unpublished"' : '';
		}
		$tpl  = '<li><span [+class+]><a href="index.php?id=[+id+]&amp;a=[+action+]">[+name+]<small>([+id+])</small></a>[+rlm+]</span>';
		$tpl .= ' [+description+][+locked+]</li>';
		$ph['class'] = $class;
		$ph['id'] = $row['id'];
		$ph['action'] = $action;
		$ph['name'] = $row['name'];
		$ph['rlm'] = $modx_textdir ? '&rlm;' : '';
		$ph['description'] = $row['description'];
		$ph['locked'] = $row['locked'] ? ' <em>('.$_lang['locked'].')</em>' : '';
		foreach($ph as $k=>$v)
		{
			$k = '[+' . $k . '+]';
			$tpl = str_replace($k,$v,$tpl);
		}
		$output .= $tpl;
	
		$preCat = $row['category'];
	}
	$output .= $insideUl? '</ul>': '';
	$output .= '</ul>';
	return $output;
}

function createCategoryList()
{
	global $modx, $_lang;
	
	$displayInfo = array();
	$hasPermission = 0;
	if($modx->hasPermission('edit_plugin') || $modx->hasPermission('new_plugin'))
	{
		$displayInfo['plugin'] = array('table'=>'site_plugins','action'=>102,'name'=>$_lang['manage_plugins']);
		$hasPermission = 1;
	}
	if($modx->hasPermission('edit_snippet') || $modx->hasPermission('new_snippet'))
	{
		$displayInfo['snippet'] = array('table'=>'site_snippets','action'=>22,'name'=>$_lang['manage_snippets']);
		$hasPermission = 1;
	}
	if($modx->hasPermission('edit_chunk') || $modx->hasPermission('new_chunk'))
	{
		$displayInfo['htmlsnippet'] = array('table'=>'site_htmlsnippets','action'=>78,'name'=>$_lang['manage_htmlsnippets']);
		$hasPermission = 1;
	}
	if($modx->hasPermission('edit_template') || $modx->hasPermission('new_template'))
	{
		$displayInfo['templates'] = array('table'=>'site_templates','action'=>16,'name'=>$_lang['manage_templates']);
		$displayInfo['tmplvars'] = array('table'=>'site_tmplvars','action'=>301,'name'=>$_lang['tmplvars']);
		$hasPermission = 1;
	}
	if($modx->hasPermission('edit_module') || $modx->hasPermission('new_module'))
	{
		$displayInfo['modules'] = array('table'=>'site_modules','action'=>108,'name'=>$_lang['modules']);
		$hasPermission = 1;
	}
	
	//Category Delete permission check
	$delPerm = 0;
	if($modx->hasPermission('save_plugin') ||
		$modx->hasPermission('save_snippet') ||
		$modx->hasPermission('save_chunk') ||
		$modx->hasPermission('save_template') ||
		$modx->hasPermission('save_module'))
	{
		$delPerm = 1;
	}
	
	if($hasPermission)
	{
		$finalInfo = array();
		
		foreach ($displayInfo as $n => $v)
		{
			$tbl_elm = $modx->getFullTableName($v['table']);
			$tbl_categories = $modx->getFullTableName('categories');
			if($v['table'] == 'site_templates')   $fields = 'templatename as name, ';
			elseif($v['table'] == 'site_plugins') $fields = "{$tbl_elm}.disabled, name, ";
			elseif($v['table'] == 'site_htmlsnippets') $fields = "{$tbl_elm}.published, name, ";
			else                                  $fields = 'name, ';
			$fields .= "{$tbl_elm}.id, description, locked, {$tbl_categories}.category, {$tbl_categories}.id as catid";
			
			$from = "{$tbl_elm} left join {$tbl_categories} on {$tbl_elm}.category = {$tbl_categories}.id";
			$orderby = ($v['table'] == 'site_plugins') ? "{$tbl_elm}.disabled ASC,6,2" : '5,1';
			$rs = $modx->db->select($fields,$from,'',$orderby);
			$limit = $modx->db->getRecordCount($rs);
			if($limit>0)
			{
				while($row = $modx->db->getRow($rs))
				{
					$row['type'] = $v['name'];
					$row['action'] = $v['action'];
					if (empty($row['category']))
					{
						$row['category'] = $_lang['no_category'];
					}
					$finalInfo[] = $row;
				}
			}
		}
		
		foreach($finalInfo as $n => $v)
		{
			$category[$n] = $v['category'];
			$name[$n] = $v['name'];
		}
		
		array_multisort($category, SORT_ASC, $name, SORT_ASC, $finalInfo);
		
		$preCat = '';
		$insideUl = 0;
		foreach($finalInfo as $n => $v)
		{
			if ($preCat !== $v['category'])
			{
				echo $insideUl? '</ul>': '';
				if ($v['category'] == $_lang['no_category'] || !$delPerm)
				{
					echo '<li><strong>'.$v['category'].'</strong><ul>';
				}
				else
				{
					echo '<li><strong>'.$v['category'].'</strong> (<a href="javascript:deleteCategory(\'' . $v['catid'] . '\');">'.$_lang['delete'].'</a>)<ul>';
				}
				$insideUl = 1;
			}
			$ph = array();
			if(array_key_exists('disabled',$v) && $v['disabled'])
			{
				$ph['class'] = ' class="disabledPlugin"';
			}
			if(array_key_exists('published',$v) && $v['published']==='0')
			{
				$ph['class'] = ' class="unpublished"';
			}
			else $ph['class'] = '';
			$ph['id'] = $v['id'];
			$ph['action'] = $v['action'];
			$ph['name'] = $v['name'];
			$ph['type'] = $v['type'];
			$ph['description'] = (!empty($v['description'])) ? ' - '.$v['description'] : '';
			$ph['locked'] = ($v['locked']) ? ' <em>(' . $_lang['locked'] . ')</em>' : '';
			$tpl = '<li><span [+class+]><a href="index.php?id=[+id+]&amp;a=[+action+]">[+name+]</a></span> ([+type+])[+description+][+locked+]</li>';
			foreach($ph as $k=>$value)
			{
				$k = '[+' . $k . '+]';
				$tpl = str_replace($k,$value,$tpl);
			}
			echo $tpl;
			$preCat = $v['category'];
		}
		echo $insideUl? '</ul>': '';
	}
}
