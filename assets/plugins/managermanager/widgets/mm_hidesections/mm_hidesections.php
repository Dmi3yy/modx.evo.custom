<?php
/**
 * mm_hideSections
 * @version 1.1 (2012-11-13)
 * 
 * Hides sections.
 * 
 * @uses ManagerManager plugin 0.4.
 * 
 * @link http://code.divandesign.biz/modx/mm_hidesections/1.1
 * 
 * @copyright 2012
 */

function mm_hideSections($sections, $roles = '', $templates = ''){
	global $modx;
	$e = &$modx->Event;
	
	// if we've been supplied with a string, convert it into an array
	$sections = makeArray($sections);
	
	// if the current page is being edited by someone in the list of roles, and uses a template in the list of templates
	if (useThisRule($roles, $templates)){
		$output = "//  -------------- mm_hideSections :: Begin ------------- \n";
		
		foreach($sections as $section){
			switch ($section){
				case 'content':
					$output .= '
					$j("#content_header").hide();
					$j("#content_body").hide();
					';
				break;
				
				case 'tvs':
					$output .= '
					$j("#tv_header").hide();
					$j("#tv_body").hide();
					';
				break;
				
				case 'access': // These have moved to tabs in 1.0.1
					$output .= '
					$j("#sectionAccessHeader").hide();
					$j("#sectionAccessBody").hide();';
				break;
			}
			
			$output .= "//  -------------- mm_hideSections :: End ------------- \n";
			
			$e->output($output . "\n");
		}
	}
}
?>