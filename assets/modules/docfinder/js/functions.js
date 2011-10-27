// ---------------------------------------------------------------
// :: Doc Finder
// ----------------------------------------------------------------
//   
// 	Short Description: 
//         Ajax powered search and replace for the manager.
// 
//   Version:
//         1.6
// 
//   Created by:
// 	    Bogdan Günther (http://www.medianotions.de - bg@medianotions.de)
// 
// 
// ----------------------------------------------------------------
// :: Copyright & Licencing
// ----------------------------------------------------------------
// 
//   GNU General Public License (GPL - http://www.gnu.org/copyleft/gpl.html)
// 
// 


// define global parameters
var ajaxRequest, delay, timer, oldSearchstring;
var time=0;

window.addEvent('load', function() {
	
	// check search string and set submit buttons
	triggerSubmitButtons();
	
	// init date pickers
	new vlaDatePicker('createdon_start', {
		style: 'apple_widget',
		offset: { x: 3, y: -1 },
		separator: '-',
		filePath: '../assets/modules/docfinder/js/vlaCal.v2/inc/'
	});
	
	new vlaDatePicker('createdon_end', {
		style: 'apple_widget',
		offset: { x: 3, y: -1 },
		separator: '-',
		filePath: '../assets/modules/docfinder/js/vlaCal.v2/inc/'
	});
	
	new vlaDatePicker('editedon_start', {
		style: 'apple_widget',
		offset: { x: 3, y: -1 },
		separator: '-',
		filePath: '../assets/modules/docfinder/js/vlaCal.v2/inc/'
	});
	
	new vlaDatePicker('editedon_end', {
		style: 'apple_widget',
		offset: { x: 3, y: -1 },
		separator: '-',
		filePath: '../assets/modules/docfinder/js/vlaCal.v2/inc/'
	});
	
	
	// get searchstring
	oldSearchstring=$('searchstring').value;
	
	
	// catch submit
	$('docfinder').addEvent('submit', function(e) {
		new Event(e).stop();
		$('replace_mode').value=0;
		startAjaxSearch();
	});
	
	// catch submit button
	$('submit_search').addEvent('click', function(e) {
		new Event(e).stop();
		$('replace_mode').value=0;
		startAjaxSearch();
	});
	
	// catch replace button
	$('submit_replace').addEvent('click', function(e) {
		new Event(e).stop();
		$('replace_mode').value=1;
		startAjaxSearch();
	});
		
			
	// cancel search and AJAX search request
	$('cancel_search').addEvent('click', function(e) {
		new Event(e).stop();
		
		// clear delay and AJAX request
		if(delay) $clear(delay);
		if(ajaxRequest) ajaxRequest.cancel();
		
		// hide AJAX load inidcator
		$('ajax_load_indicator').setStyles('display: none;');
		
		// set text to results container and show it
		$('results_container').setText('Search canceled.');
		$('results_container').setStyles('display: inline;');
		
		// stop timer
		$clear(timer); time=0;
		
		// set results info
		$('results_info').setText("0");
	});
	
	// toggle search field text
	function toggleRegExpSearch()
	{
		if($('regular_expression').getValue()==false) $('search_field_text').setText('String search:');
		else $('search_field_text').setText('RegExp search:');
	}

	// catch event for regular_expression
	$('regular_expression').addEvent('click', function(e) {
		e=new Event(e);
		toggleRegExpSearch();
	});
	
	// set search field text when page loads
	if($('regular_expression').getValue()=="on") toggleRegExpSearch();
	
});


// perform AJAX request
var startAjaxSearch=function()
{
	if($('searchstring').value=="") return;

	// check if this a replace
	if($('replace_mode').value==1)
	{
		// abort if regular expressions are on
		if($('regular_expression').getValue()=="on") var infoText="The replace function can not be combinded with regular expressions.";
		
		// abort if logical operators are used
		if($('searchstring').value.test("AND") || $('searchstring').value.test("OR") || $('searchstring').value.test("NOT")) var infoText="The replace function can not be combined with logic operators.";
		
		// abort if the ALL operator is used
		if($('searchstring').value=="ALL") var infoText="The replace function can not be combined with the ALL operator.";
		
		// alert abort info, reset replace mode var and cancel function
		if(infoText)
		{
			alert(infoText);
			$('replace_mode').value=0;
			return;
		}
		
		// Ask user for confirmation
		confirmation=confirm("Replace all instances of “"+$('searchstring').value+"” with “"+$('replacestring').value+"”?");
		if(confirmation==false) 
		{
			$('replace_mode').value=0;
			return;
		}
	}
	
	// cancel old requests
	if(ajaxRequest) ajaxRequest.cancel();
	
	// stop old timer
	if(timer) $clear(timer); time=0;

	// start timer
	$('time_info').setStyles('display: inline;');
	$('time').setText('0');
	timer=setTimer.periodical(100);
	
	// hide old results table
	if(window.ie7) $('results_container').setStyles('visibility: hidden;');
	else $('results_container').setStyles('display: none;');

	// show AJAX load inidcator
	$('ajax_load_indicator').setStyles('display: block;');
	
	// set recognition for ajax mode
	$('checkform').value='ajax_get_results';
	
	// set update session recognition
	$('update_session').value=1;
	
	// get search string
	var searchstring=$('searchstring').getValue();
	
	// set results string
	if(searchstring) $('search_string').setText(searchstring);

	// send form and update results
	ajaxRequest=$('docfinder').send({
		update: $('results_container'),
		autoCancel: true,
		evalScripts: true,
		onComplete: function() {
			// hide AJAX load inidcator
			$('ajax_load_indicator').setStyles('display: none;');
			
			// show results table
			$('results_container').setStyles('display: block;');
			
			// update histories
			updateSearchHistory("search");
			if($('replace_mode').value==1) updateSearchHistory("replace");
			
			// clear reset replace mode
			if($('replace_mode').value) $('replace_mode').value=0;
			
			ajaxRequest=false;
		},
		onCancel: function() { 
			
		} 
	});
}


function updateSearchHistory(type)
{
	// set recognition for AJAX mode
	var querystring="checkform=ajax_get_"+type+"History";
	
	// get update via AJAX
	historyAjaxUpdate=new Ajax(window.location.href, {
		method: 'post',
		update: $(type+'_history_box'),
		autoCancel: true,
		evalScripts: false,
		onComplete: function() {
			
		},
		onCancel: function() { 
			
		} 
	}).request(querystring);
}


// refresh time function
var setTimer=function()
{
	if(ajaxRequest)
	{
		// increment timer
		time++;
		$('time').setText(time/10);
	}
	else
	{
		// stop timer
		$clear(timer); time=0;
	}
}

function triggerSubmitButtons()
{
	// set or remove active class depending if if the search string is empty
	if($('searchstring').value!="") $('submit').addClass('submit_active');
	else $('submit').removeClass('submit_active');
}

function checkboxSelector(id)
{
	// Search places
	if(id=="search_place_selector")
	{
		if($(id).checked)
		{
			$('search_place_DocAndTVV').checked=true;
			$('search_place_Templates').checked=true;
			$('search_place_TVs').checked=true;
			$('search_place_Chunks').checked=true;
			$('search_place_Snippets').checked=true;
			$('search_place_Plugins').checked=true;
			$('search_place_Modules').checked=true;
		}
		else
		{
			$('search_place_DocAndTVV').checked=false;
			$('search_place_Templates').checked=false;
			$('search_place_TVs').checked=false;
			$('search_place_Chunks').checked=false;
			$('search_place_Snippets').checked=false;
			$('search_place_Plugins').checked=false;
			$('search_place_Modules').checked=false;
		}
	}
	
	// Documents search in
	if(id=="documents_search_in_selector")
	{
		if($(id).checked)
		{
			$('df_id').checked=true;
			$('pagetitle').checked=true;
			$('longtitle').checked=true;
			$('description').checked=true;
			$('alias').checked=true;
			$('introtext').checked=true;
			$('menutitle').checked=true;
			$('content').checked=true;
			$('tvs').checked=true;
		}
		else
		{
			$('df_id').checked=false;
			$('pagetitle').checked=false;
			$('longtitle').checked=false;
			$('description').checked=false;
			$('alias').checked=false;
			$('introtext').checked=false;
			$('menutitle').checked=false;
			$('content').checked=false;
			$('tvs').checked=false;
		}
	}
	
	// Resources search in
	if(id=="resources_search_in_selector")
	{
		if($(id).checked)
		{
			$('resources_id').checked=true;
			$('resources_name').checked=true;
			$('resources_description').checked=true;
			$('resources_other').checked=true;
		}
		else
		{
			$('resources_id').checked=false;
			$('resources_name').checked=false;
			$('resources_description').checked=false;
			$('resources_other').checked=false;
		}
	}
}