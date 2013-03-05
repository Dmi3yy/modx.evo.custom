<?php
/*
	Written by Jeff Whitfield (bravado) - Gallery Link
	Adds a link to the Gallery module for the given page.
	@version: 0.1 - added TVs handling.
*/

function mm_galleryLink($fields, $roles='', $templates='', $moduleid=''){
	global $mm_fields, $modx, $content;
	$e = &$modx->Event;

	// if we've been supplied with a string, convert it into an array
	$fields = makeArray($fields);

	// if the current page is being edited by someone in the list of roles, and uses a template in the list of templates
	if (useThisRule($roles, $templates)) {

        $output = " // ----------- Gallery Link -------------- \n";

		foreach ($fields as $field) {
			//ignore for now
			switch ($field) {

				// ignore fields that can't be converted
				case 'keywords':
				case 'metatags':
				case 'hidemenu':
				case 'which_editor':
				case 'template':
				case 'menuindex':
				case 'show_in_menu':
				case 'parent':
				case 'is_folder':
				case 'is_richtext':
				case 'log':
				case 'searchable':
				case 'cacheable':
				case 'clear_cache':
				case 'content_type':
				case 'content_dispo':
				case 'which_editor':
					$output .='';
				break;

				// default if not ignored
				default:
				if (isset($mm_fields[$field]))  { // Check the fields exist,  so we're not writing JS for elements that don't exist
            		$output .= 'var pid'.$mm_fields[$field]['fieldname'].' = "'.(!empty($content['id']) ? $content['id'] : 'false').'";'."\n";

                	$output .= 'var gl'.$mm_fields[$field]['fieldname'].' = $j("'.$mm_fields[$field]['fieldtype'].'[name='.$mm_fields[$field]['fieldname'].']");'."\n";
                	$output .= 'if(pid'.$mm_fields[$field]['fieldname'].' != \'false\'){'."\n";
                    $output .= '    var galleryLink = $j(\'<a href="' . $modx->config['base_url'] . 'manager/index.php?a=112&id='.$moduleid.'&action=view&content_id=\'+pid'.$mm_fields[$field]['fieldname'].'+\'">Manage Photos</a>\').insertAfter(gl'.$mm_fields[$field]['fieldname'].');'."\n";
                	$output .= '} else {'."\n";
                    $output .= '    var galleryLink = $j(\'<p class="warning">You must save this page before you can manage the photos associated with it.</p>\').insertAfter(gl'.$mm_fields[$field]['fieldname'].');'."\n";
                	$output .= '}'."\n";
					$output .= 'gl'.$mm_fields[$field]['fieldname'].'.hide();'."\n";
				} 				
				break;
			}
		}
		$e->output($output . "\n");
	} // end if
} // end function


?>