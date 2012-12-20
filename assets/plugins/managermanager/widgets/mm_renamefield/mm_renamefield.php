<?php
/**
 * mm_renameField
 * @version 1.1 (2012-11-13)
 *
 * Change the label for an element.
 * 
 * @uses ManagerManager plugin 0.4.
 * 
 * @link http://code.divandesign.biz/modx/mm_renamefield/1.1
 * 
 * @copyright 2012
 */

function mm_renameField($field, $newlabel, $roles='', $templates='', $newhelp=''){
	global $mm_fields, $modx;
	$e = &$modx->Event;
	
	// if the current page is being edited by someone in the list of roles, and uses a template in the list of templates
	if ($e->name == 'OnDocFormRender' && useThisRule($roles, $templates)){
		$output = "//  -------------- mm_renameField :: Begin ------------- \n";
		
		switch ($field){
			// Exceptions
			case 'keywords':
				$output .= '$j("select[name*=keywords]").siblings("span.warning").html("'.jsSafe($newlabel).'");';
			break;
			
			case 'metatags':
				$output .= '$j("select[name*=metatags]").siblings("span.warning").html("'.jsSafe($newlabel).'");';
			break;
			
			case 'hidemenu':
			case 'show_in_menu':
				$output .= '$j("input[name=hidemenucheck]").siblings("span.warning").html("'.jsSafe($newlabel).'");';
			break;
			
			case 'which_editor':
				$output .= '$j("#which_editor").prev("span.warning").html("'.jsSafe($newlabel).'");';
			break;
			
			case 'content':
				$output .= '$j("#content_header").html("'.jsSafe($newlabel).'")';
			break;
			
			// Ones that follow the regular pattern
			default:
				if (isset($mm_fields[$field])){
					$fieldtype = $mm_fields[$field]['fieldtype'];
					$fieldname = $mm_fields[$field]['fieldname'];
					$output .= '$j("'.$fieldtype.'[name='.$fieldname.']").parents("td:first").prev("td").children("span.warning").empty().prepend("'.jsSafe($newlabel).'");';
				}
			break;
		}
		
		$output .= "//  -------------- mm_renameField :: End ------------- \n";
		
		$e->output($output . "\n");
		
		// If new help has been supplied, do that too
		if ($newhelp != ''){
			mm_changeFieldHelp($field, $newhelp, $roles, $templates);
		}
	}
}
?>