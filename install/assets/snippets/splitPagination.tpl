//<?php
/**
 * splitPagination
 *
 * Create split pagination for large Ditto result sets
 * 
 * @category	snippet
 * @version 	2.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category Navigation
 * @author		Nick Crossland - rckt.co.uk. Based on the function written by Aaron Hall, evilwalrus.org.
 */
 

/* 

PURPOSE
When a Ditto result set is large, the pagination can grow too large to fit comfortably and usably on the page. 
This snippet provides alternative "Google" style pagination, where a maximum number of pages are shown - the 
first and last pages of the results, and the pages immediately surrounding the current page.
 e.g. instead Ditto's default pagination output of

[1] [2] [3] [4] [5] [6] [7] [8] [9] [10] [*11*] [12] [13] [14] [15] [16] [17] [18] [19] [20]

(current page is 11) this snippet produces

[1] [2] ... [8] [9] [10] [*11*] [12] [13] [14] ... [19] [20]


REQUIREMENTS
This snippet is tested with Ditto 2.1 in ModX Evo 1.0.x


INSTALLATION
Create a new snippet named splitPagination, and paste the contents of this file into it. 


USAGE
Place the snippet immediately following a Ditto call. 

e.g. 1 

[[splitPagination]]
will just create a placeholder named "splitPages" - to insert the pagination bar into the correct place of your document use the [+splitPages+] placeholder

e.g.2
[[splitPagination? &id=`myDitto` &pagesToShow=`10` &constantEndCount=`3` &ellipses=`myChunk` &return=`1`]]
will output the pagination directly, using data obtained from the Ditto call with the ID of "myDitto". 
It will display a maximum of 10 links to pages, with three before/after the ellipses at the beginning and end. 
The HTML to use for the ellipses will be obtained from the "myChunk" chunk.


PARAMETERS

 Param: id

    Purpose:
    If there is more than one Ditto call on a page, or your Ditto call has the ID paramater set, this is required to 
	know which Ditto ID to use.

    Options:
    Ditto ID parameter
	
	Default:
    <none>
	
	
	
 Param: pagesToShow

    Purpose:
    Maximum number of page links to show. Any pages above this number will be replaced by ellipses. 
	Sets of ellipses are included in this count, to ensure the total size of the pagination remains largely 
	the same regardless of current position.

    Options:
    Number
	
	Default:
    15	
	


 Param: constantEndCount

    Purpose:
    Number of pages links to display at the beginning and end of the results set, before and after the ellipses
	e.g. set to 2, the results would display:	
	[1] [2] ... [99] [100]
	
	or set to 4, the results would display:
	[1] [2] [3] [4] ... [97] [98] [99] [100]	

    Options:
    Number
	
	Default:
    2	
	
	
 Param: return

    Purpose:
    Output the pagination directly from the location of the snippet, rather than to a placeholder

    Options:
    Boolean
	
	Default:
    0	


	
 Param: tplEllipses

    Purpose:
    Chunk name, or HTML that should be used to indicate a break in the page numbering. ModX doesn't work very
	well (as of 1.0.x) with = sign within parameters, so if you wish to use HTML containing = sign, it is 
	suggested to put it in a chunk rather than supply HTML directly.

    Options:
    Chunk name or HTML
	
	Default:
    <span class="splitPagination">...</span>	
	

 Param: tplCurrent

    Purpose:
    Chunk name, or HTML that should be used for the current results page. ModX doesn't work very
	well (as of 1.0.x) with = sign within parameters, so if you wish to use HTML containing = sign, it is 
	suggested to put it in a chunk rather than supply HTML directly.
	
	The [+page+] placeholder will be replaced by the page number
	The [+class+] placeholder will be replace by a class representing first or last items if this applies (see firstClass and lastClass params)

    Options:
    Chunk name or HTML
	
	Default:
    <span class="ditto_currentpage ditto_page_[+page+][+class+]">[+page+]</span>		
	
	

 Param: tplPageLink

    Purpose:
    Chunk name, or HTML that should be used for links to all results page (other than current). ModX doesn't work very
	well (as of 1.0.x) with = sign within parameters, so if you wish to use HTML containing = sign, it is 
	suggested to put it in a chunk rather than supply HTML directly.
	
	The [+page+] placeholder will be replaced by the page number
	The [+url+] placeholder will be replaced by the link URL
	The [+class+] placeholder will be replace by a class representing first or last items if this applies (see firstClass and lastClass params)

    Options:
    Chunk name or HTML
	
	Default:
    <a class="ditto_page ditto_page_[+page+][+class+]" href="[+url+]">[+page+]</a>



 Param: firstClass

    Purpose:
    Class applied to the first page link
	
    Options:
    HTML class
	
	Default:
    first



 Param: lastClass

    Purpose:
    Class applied to the last page link
	
    Options:
    HTML class
	
	Default:
    last
	

---------------------------------------------------------------------------------------
The following parameters are available as manual overrides, however they are normally 
populated with correct values automatically.
	
	
 Param: total

    Purpose:
   	Total number of results from the Ditto query - usually obtained automatically from Ditto. Setting manually may have
	unpredictable results!

    Options:
    Number
	
	Default:
    From Ditto results	
	
	
	
 Param: start

    Purpose:
   	Start position of results from the Ditto query - usually obtained automatically from Ditto. Setting manually may have
	unpredictable results!

    Options:
    Number
	
	Default:
    From Ditto results		
	
	
	
 Param: display

    Purpose:
   	Number of results per page to display - usually obtained automatically from Ditto. Setting manually may have
	unpredictable results!

    Options:
    Number
	
	Default:
    From Ditto results	
	
	
	
 Param: currentPage

    Purpose:
   	Override current page. Usually automatically calculated. Setting manually may have
	unpredictable results!

    Options:
    Number
	
	Default:
    From URL	


	
 Param: landing

    Purpose:
   	Landing page for pagination links. 

    Options:
    Document ID
	
	Default:
    Current page
	
	


 


*/



if (!function_exists("generatePagination")) {
	
function generatePagination($currentPage, $totalResults, $resultsPerPage, $pagesToShow, $constantEndCount) {
	
	// How many pages are there altogether?
	$totalPages = ceil($totalResults / $resultsPerPage);
	
	// How far through are we?
	$pagesBefore = $currentPage - 1;
	$pagesAfter = $totalPages - $currentPage;
	
	// An array of tabs to display (made up of page numbers)
	$tabArr = array();
	
	// If we need to split the pagination
	if($totalPages > $pagesToShow) {
		
		// If we're currently more than half way through
		if($pagesBefore > $pagesToShow/2) {
			
			// Always show pages 1 and 2 (or whatever the constant end count is), then a separator
			for($i=1; $i<=$constantEndCount; $i++) { $tabArr[] = $i; }
			$tabArr[] = 0;
				
			// If the number of pages after the current page is more than the number of available slots
			if($pagesAfter >= $pagesToShow/2) {
				$empty_slots_before = floor( ($pagesToShow - ($constantEndCount*2 /*there are two ends*/) - 1 /*the current page*/) / 2 /*only half*/) -1  /*the set of dots counts as one slot*/;
			} else {
				$empty_slots_before = $pagesToShow - count($tabArr) - 1 /*current page*/ - $pagesAfter /*How many after*/;
			}
			
			// Insert the tabs	
			for($i=($currentPage-$empty_slots_before); $i<$currentPage; $i++) { $tabArr[] = $i; }	
			
		} else {
			for($i=1; $i<$currentPage; $i++) { $tabArr[] = $i; }
		}
		
		// Insert current page
		$tabArr[] = $currentPage;		
		
		// If we're less than half way through
		if($pagesAfter > $pagesToShow/2) {
			
			// How many more slots do we want to show after the current page?
			$empty_slots_after = $pagesToShow - count($tabArr) - $constantEndCount - 1/*the set of dots*/;
			
			// Insert the tabs	
			for($i=($currentPage+1); $i<=$currentPage+$empty_slots_after; $i++) { $tabArr[] = $i; }
			
			// Add a separator
			$tabArr[] = 0;
			
			// Always show pages last 2 pages (or whatever the constant end count is)
			for($i=$totalPages-$constantEndCount+1; $i<=$totalPages; $i++) { $tabArr[] = $i; }

		} else {
			for($i=($currentPage+1); $i<=$totalPages; $i++) { $tabArr[] = $i; }
		}
		
	} else { // No need to split the pagination
		for($i=1;$i<=$totalPages;$i++) { $tabArr[] = $i; }
	}
			
	return $tabArr;
	
	} // end function
} // end if




// ----------------------------------------------------------------------------------------------------------------







// Define some paramaters
$id = isset($id) ? $id.'_' : '';
$total = isset($total) ? $total : $modx->getPlaceholder($id."total");
$start = isset($start) ? $start : $modx->getPlaceholder($id."start");
$display = isset($display) ? $display : $modx->getPlaceholder($id."perPage");
$currentPage = isset($currentPage) ? $currentPage : $modx->getPlaceholder($id."current");
$landing = isset($tagDocumentID) ? $tagDocumentID : $modx->documentObject['id'];

$pagesToShow = isset($pagesToShow) && is_numeric($pagesToShow) ? $pagesToShow : 15;
$constantEndCount = isset($constantEndCount) && is_numeric($constantEndCount) ? $constantEndCount : 2;


$tplEllipses = isset($tplEllipses) ? $modx->getChunk($tplEllipses) : '';
$tplEllipses = !empty($tplEllipses) ? $tplEllipses : '<span class="splitPagination">...</span>';

$tplCurrent = isset($tplCurrent) ? $modx->getChunk($tplCurrent) : '';
$tplCurrent = !empty($tplCurrent) ? $tplCurrent : '<span class="ditto_currentpage ditto_page_[+page+][+class+]">[+page+]</span>';

$tplPageLink = isset($tplPageLink) ? $modx->getChunk($tplPageLink) : '';
$tplPageLink = !empty($tplPageLink) ? $tplPageLink : '<a class="ditto_page ditto_page_[+page+][+class+]" href="[+url+]">[+page+]</a>';

$firstClass = isset($firstClass) ? $firstClass: 'first';
$lastClass = isset($lastClass) ? $lastClass: 'last';


$return = isset($return) ? $return : 0;

// Do nothing if there's nothing to do
if ($total == 0 || $display==0 || $total <= $display) {
	return false;
}

// What page are we on?
$page = ceil($start/$display);

// Generate the pagination
$paginationArray = generatePagination($page, $total, $display, $pagesToShow, $constantEndCount);

// Create empty placeholder for output
$ph = "";

// Build the output
foreach($paginationArray as $page) {
	$inc = ($page-1)*$display;
	
	// First or last classes
	if ($page == 1) { 
			$newClass = ' ' . $firstClass;
	} else if ($page == ceil($total/$display)) {
		$newClass = ' ' . $lastClass;
	} else {
		$newClass = '';
	}
	
	// Insert into output
	if($page == 0) {
		$ph .= $tplEllipses; // print an elipse, representing pages that aren't displayed
	} else if ($inc==$_GET[$id.'start']) {
		$newLink = str_replace('[+class+]', $newClass , $tplCurrent);
		$ph .= str_replace('[+page+]', $page, $newLink);	
	} else {
		$newLink = str_replace('[+url+]', ditto::buildURL("start=".$inc,$landing,$id) , $tplPageLink);		
		$newLink = str_replace('[+class+]', $newClass , $newLink);
		$ph .= str_replace('[+page+]', $page, $newLink);	
	}
}

// Set the placeholder
$modx->setPlaceholder($id."splitPages",$ph);

// Or return the output
if ($return) return $ph;