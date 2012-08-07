<?php

/*
 * Title: Distinct
 * Purpose:
 *  	Return only distinct / unique results, based on a fieldname supplied as &distinct parameter
 * Version: 1.0.1
 * Author: Nick Crossland (ncrossland)
 * Installation: Put file "distinct.extender.inc.php" into /assets/snippets/ditto/extenders
 * Usage: In the Ditto call, add "distinct" to the extenders param, and specify the &distinct parameter with the name(s) of the field which you would like to be unique.
 *        If you would like to make the combined values of more than one field unique, separate them with commas
 * e.g. [[Ditto? &tpl=`myTemplate` &extenders=`distinct` &distinct=`pagetitle`]] -- will return only unique page titles.
 * e.g. [[Ditto? &tpl=`myTemplate` &extenders=`distinct` &distinct=`pagetitle,pub_date`]] -- will return only unique page titles for each date.
 *
 * Changelog:
 * 1.0: initial release
 * 1.0.1: Bugfixes only
*/


$distinct = isset($distinct) ? $distinct : false;
/*
	Param: distinct

	Purpose:
 	What field should we search for being distinct?

	Options:
	Fieldname
	
	Default:
	"default"
*/


// If no fieldname value has been supplied, don't do anything else
if ($distinct === false) {
	return false;	
}

// It would be nice if this was class based, so it doesn't pollute the global namespace
// But - create an array of document values we've seen, and the fieldnames we're making distinct
global $seen;
global $distinct_fieldname;
$distinct_fieldname = explode(',',$distinct); 

// Remove any extra spaces from the fieldnames (in case they have been supplied with commas and spaces)
$distinct_fieldname = array_map('trim', $distinct_fieldname);	


// The filter function
if (!function_exists("makeDistinct")) {
	
	function makeDistinct($resource) {
		global $seen;
		global $distinct_fieldname;
		
		// Make a unique string based on the fieldname and value of each field we've been asked to make distinct
		$distinct_string = '';
		foreach ($distinct_fieldname as $f) {
			$distinct_string .= '~'. $f.'|'.$resource[$f];
		}
			
		// Check if this string has been seen yet -- if it has, don't include it in the results
		if (isset($seen[$distinct_string]) && ($seen[$distinct_string])==true ) {	// If this value of the fieldname has been seen before, remove it from the list
			return false;
		} else {
			$seen[$distinct_string] = true;	// Otherwise, remember the value has been seen, and allow it in the list (this time)
			return true;
		}	
		
	}
}

// Add the custom function
$filters["custom"]["distinct"] =  array(implode(',',$distinct_fieldname) ,"makeDistinct"); 


?>