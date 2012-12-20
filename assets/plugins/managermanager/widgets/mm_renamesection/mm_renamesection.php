<?php
/**
 * mm_renameSection
 * @version 1.1 (2012-11-13)
 * 
 * Rename a section.
 * 
 * @uses ManagerManager plugin 0.4.
 * 
 * @link http://code.divandesign.biz/modx/mm_renamesection/1.1
 * 
 * @copyright 2012
 */

function mm_renameSection($section, $newname, $roles = '', $templates = ''){
	global $modx;
	$e = &$modx->Event;
	
	// if the current page is being edited by someone in the list of roles, and uses a template in the list of templates
	if ($e->name == 'OnDocFormRender' && useThisRule($roles, $templates)){
		$output = "//  -------------- mm_renameSection :: Begin ------------- \n";
		
		switch ($section){
			case 'content':
				$output .= '$j("div#content_header").empty().prepend("'.jsSafe($newname).'");' . "\n";
			break;
			
			case 'tvs':
				$output .= '
				$j("div#tv_header").empty().prepend("'.jsSafe($newname).'");
				' ;
			break;
			
			case 'access': // These have moved to tabs in 1.0.1
				$output .= '$j("div#sectionAccessHeader").empty().prepend("'.jsSafe($newname).'");' . "\n";
			break;
		}
		
		$output .= "//  -------------- mm_renameSection :: End ------------- \n";
		
		$e->output($output . "\n");
	}
}
?>