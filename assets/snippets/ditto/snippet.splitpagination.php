<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/* 

e.g. 1 

[[splitPagination]]
will just create a placeholder named "splitPages" - to insert the pagination bar into the correct place of your document use the [+splitPages+] placeholder

e.g.2
[[splitPagination? &id=`myDitto` &pagesToShow=`10` &constantEndCount=`3` &ellipses=`myChunk` &return=`1`]]
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
?>