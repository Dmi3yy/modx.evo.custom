<?php

//---------------------------------------------------------------------------------
// mm_widget_evogallery
//--------------------------------------------------------------------------------- 
function mm_widget_evogallery($moduleid, $title='', $roles='', $templates='') {
	
	global $modx, $content, $mm_fields;
	$e = &$modx->Event;
	
	if (useThisRule($roles, $templates)) {
		
		//Include language file
		$langpath = $modx->config['base_path'] . "assets/modules/evogallery/lang/";
		//First include english
		if (file_exists($langpath.'english.inc.php'))
			include $langpath.'english.inc.php' ;
		//Include current manager language
		if (file_exists($langpath.$modx->config['manager_language'].'.inc.php'))
			include $langpath.$modx->config['manager_language'].'.inc.php';
		
		$title = empty($title) ? $_lang['mm_tab_name'] : $title;
	
		//TODO: Add iframe autoheight
		if (isset($content['id']))
			$iframecontent = '<iframe id="mm_evogallery" src="'.$modx->config['site_url'].'manager/index.php?a=112&id='.$moduleid.'&onlygallery=1&action=view&content_id='.$content['id'].'" style="width:100%;height:600px;" scrolling="auto" frameborder="0"></iframe>';
		else
			$iframecontent = '<p class="warning">'.$_lang['mm_save_required'].'</p>';
		
		mm_createTab($title, 'evogallery', '', '', '<strong>Управление изображениями</strong>');

                $output = "\$j('#table-evogallery').append('<tr><td>$iframecontent</td></tr>');";
    
		
	} // end if
	
	$e->output($output . "\n");	// Send the output to the browser
	
}

?>
