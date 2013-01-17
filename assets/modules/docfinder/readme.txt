---------------------------------------------------------------
:: Doc Finder
----------------------------------------------------------------
  
	Short Description: 
        Ajax powered search and replace for the manager.

  Version:
        1.6

  Created by:
	    Bogdan Günther (http://www.medianotions.de - bg@medianotions.de)


----------------------------------------------------------------
:: Description
----------------------------------------------------------------

	At some point big MODx sites are difficult to handle with 
	the document tree in the manager. Or you just want to check 
	all the documents of your site containing a specific text?

	Doc Finder offers a fast Ajax powered search and replace for 
	the manager with neatly arranged results and one click edit.
	
	
----------------------------------------------------------------
:: Copyright & Licencing
----------------------------------------------------------------

  GNU General Public License (GPL - http://www.gnu.org/copyleft/gpl.html)


-----------------------------------------------------------------
:: Installation:
-----------------------------------------------------------------

	1. Copy the folder docfinder to /assets/modules/.

	2. Open the Modules > Manage Modules in the manager.

	3. Create a new module called Doc Finder 1.6.

	4. Insert 
		
			include($modx->config['base_path']."assets/modules/docfinder/index.php");
		
		as module code.

	5. Save the new Doc Finder module.

	6. Reload the manager to make Doc Finder appear in the modules section.


----------------------------------------------------------------
:: Live demo:
----------------------------------------------------------------

	Try the life demo at trymodx.com:

	URL: 		http://docfinder.trymodx.com/manager/
	Login: 	demo_user / demo_user
 

---------------------------------------------------------------
:: Changelog:
---------------------------------------------------------------

	08-April-10 (1.6)
		-- Updated appearance for MODx Evolution
		-- Updated and bugfixed calendar funtions. (Contribution by goldsky) 

	22-January-09 (1.5.2)
		-- Replaced PHP short tags with PHP long tags. (Contribution by goldsky)
		-- Removed OnBeforeDocFormSave and OnDocFormSave events in the replace function after controversial formu discussions wether this event calls belog there or not.

	01-December-08 (1.5.1)
		-- Added OnBeforeDocFormSave and OnDocFormSave events to the replace function.

	13-September-08 (1.5)
		** Resources added to Doc Finder. You can search and replace now in Templates, Template Variables, Chunks, Snippets, Plugins, and Modules.
		** JavaScript table sorting added using Mootools and "sortable table" by phatfusion (www.phatfusion.net)
		** Added the possibility to search within date ranges for ceated on and edited on date fields with AJAX date picker by R. Schoo (http://www.base86.com).
		-- Splitted search options in differnet tabs to gain a better overview
		-- Added an the option "Search content only" to optionally narrow down the search to the content of documents and resources only.
		-- Restructured and optimized plenty of code.

	23-Jun-08 (1.2.1)
		-- Fixed a HTML encoding problem that would display special characters wrong if the manager frameset would not use UTF-8 as charset.
		-- Fixed a PHP bug that displayed special characters wrong in the search history and the replace history
		-- Fixed a JS bug that prevented the search history and the replace history to work properly in some cases.
		-- Fixed a PHP bug related to case sensitive search.

	16-Jun-08 (1.2)
		** Replace functionality added.
		-- Added a history for the replace terms.
		-- Adapted results header info.
		-- Added a counter row to the results table.
		-- Fixed some CSS issues with Internet Explorer 7
		-- Replaced "info", "edit" and "open" texts in the results table with icons.
		-- Fixed a PHP bug that would cause a wrong display of published and hidemenu classes if the result was found in the documents TVs only.
		-- Fixed a PHP bug that caused SQL errors with mySQL encodings different then UTF-8.
		-- Fixed a PHP bug in the branch print function.

	19-Apr-08 (1.1)
		** Search within parents added.
		** Regular expressions are supported now.
		-- Search history shows empty status now.
		-- Fixed info link in the results list.
		-- Fixed a PHP bug: NOT and AND NOT in the search string are working now.
		-- Fixed a PHP bug that would cause a SQL error in case sensitive searches including the document id.
		-- Fixed a couple of HTML errors.
		-- Addes a Link to "Site > Home" for the branch with the id=0.

	05-Apr-08 (1.0)
		** Ajax powered results loading.
		** Remembers your search options for the whole session.
		** Edit, view or open a document with one click.
		** See the position of the document in the document tree.
		** See in which TV the search string has been found
		** Use AND, OR or NOT in your search.
		** Search in all TVs or just in specific ones.