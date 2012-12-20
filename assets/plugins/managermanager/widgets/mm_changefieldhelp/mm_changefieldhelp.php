<?php
/**
 * mm_changeFieldHelp
 * @version 1.1 (2012-11-13)
 *
 * Change the help text of a field.
 * 
 * @uses ManagerManager plugin 0.4.
 * 
 * @link http://code.divandesign.biz/modx/mm_changefieldhelp/1.1
 * 
 * @copyright 2012
 */

function mm_changeFieldHelp($field, $helptext='', $roles='', $templates=''){
	global $mm_fields, $modx;
	$e = &$modx->Event;

	if ($helptext == ''){
		return;
	}

	// if the current page is being edited by someone in the list of roles, and uses a template in the list of templates
	if ($e->name == 'OnDocFormRender' && useThisRule($roles, $templates)){
		$output = "//  -------------- mm_changeFieldHelp :: Begin ------------- \n";
		
		// What type is this field?
		if (isset($mm_fields[$field])){
			$fieldtype = $mm_fields[$field]['fieldtype'];
			$fieldname = $mm_fields[$field]['fieldname'];
			
			//Is this TV?
			if ($mm_fields[$field]['tv']){
				$output .= '$j("'.$fieldtype.'[name='.$fieldname.']").parents("td:first").prev("td").children("span.comment").html("'.jsSafe($helptext).'");';
				//Or document field
			}else{
				// Give the help button an ID, and modify the alt/title text
				$output .= '$j("'.$fieldtype.'[name='.$fieldname.']").siblings("img[style:contains(\'cursor:help\')]").attr("id", "'.$fieldname.'-help").attr("alt", "'.jsSafe($helptext).'").attr("title", "'.jsSafe($helptext).'"); ';
			}
		}
		
		$output .= "//  -------------- mm_changeFieldHelp :: End ------------- \n";
		
		$e->output($output . "\n");
	}
}
?>