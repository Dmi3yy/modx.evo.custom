<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/**
 * Sitemap
 * 
 * Outputs a machine readable site map for search engines and robots.
 *
 * @category snippet
 * @version 1.0.11 (2012-10-01)
 * @license LGPL
 * @author Grzegorz Adamiak [grad], ncrossland, DivanDesign (http://www.DivanDesign.biz)
 * @internal @modx_category Navigation
 * 
 * @param startid {integer} - Id of the 'root' document from which the sitemap starts. Default: 0.
 * @param format {string} - Which format of sitemap to use: sp (Sitemap Protocol used by Google), txt (text file with list of URLs), ror (Resource Of Resources). Default: sp.
 * @param seeThruUnpub {0; 1} - See through unpublished documents. Default: 1.
 * @param priority {string} - Name of TV which sets the relative priority of the document. If there is no such TV, this parameter will not be used. Default: 'sitemap_priority'.
 * @param changefreq {string} - Name of TV which sets the change frequency. If there is no such TV this parameter will not be used. Default: 'sitemap_changefreq'.
 * @param excludeTemplates {comma separated string} - Documents based on which templates should not be included in the sitemap. Comma separated list with names of templates. Default: ''.
 * @param excludeTV {string} - Name of TV (boolean type) which sets document exclusion form sitemap. If there is no such TV this parameter will not be used. Default: 'sitemap_exclude'.
 * @param xsl {string; integer} - URL to the XSL style sheet or doc ID of the XSL style sheet. Default: ''.
 * @param excludeWeblinks {0; 1} - Should weblinks be excluded? You may not want to include links to external sites in your sitemap, and Google gives warnings about multiple redirects to pages within your site. Default: 0.
 */
 
 
/*
Supports the following formats:

- Sitemap Protocol used by Google Sitemaps
  (http://www.google.com/webmasters/sitemaps/)

- URL list in text format
  (e.g. Yahoo! submission)


Changelog:
# 1.0.11 (2012-10-01) by DivanDesign (http://www.DivanDesign.biz)
+ Document will be excluded from sitemap when changefreq parameter equals 'exclude'.
* [(site_url)] (without alias) is using now for the start page ($modx->config['start_page']) document url.
# 1.0.10 (2012-02-08) by DivanDesign (http://www.DivanDesign.biz)
+ Snippet can see through unpublished documents (by default). See the «seeThruUnpub» parameter.
* Minor changes of code and comments (see the code).
# 1.0.9 (2010-06-09) by ncrossland
- update metadata format for use in ModX 1.0.x installer
# 1.0.8 (2008-08-21)
- excludeTemplates can now also be specified as a template ID instead of template name. 
  Useful if you change the names of your templates frequently. (ncrossland)
  e.g. &excludeTemplates=`myTemplateName,3,4`
# 1.0.7 (2008-07-30)
- Unpublished and deleted documents were showing up in the sitemap. Even though they could not be viewed, 
  they were showing up as broken links to search engines. (ncrossland)
# 1.0.6 (2008-02-28)
- Add optional parameter (excludeWeblinks) to exclude weblinks from the sitemap, since they often point to external
  sites (which don't belong on your sitemap), or redirecting to other internal pages (which are already
  in the sitemap). Google Webmaster Tools generates warnings for excessive redirects.	
  Default is false - e.g. default behaviour remains unchanged. (ncrossland)
# 1.0.5 (2008-02-24)
- Modification about non searchable documents, as suggested by forum user JayBee
  (http://modxcms.com/forums/index.php/topic,5754.msg99895.html#msg99895)
# 1.0.4 (2008-02-06) by Bert Catsburg, bert@catsburg.com
- Added display option 'ulli'. 
  An <ul><li> list of all published documents.
# 1.0.3 (2007-05-16)
- Added ability to specify the XSL URL - you don't always need one and it 
  seems to create a lot of support confusion!
  It is now a parameter (&xsl=``) which can take either an alias or a doc ID (ncrossland)
- Modifications suggested by forum users Grad and Picachu incorporated
  (http://modxcms.com/forums/index.php/topic,5754.60.html)
# 1.0.2 (2006-07-12)
- Reworked fetching of template variable value to
  get INHERITED value.
# 1.0.1
- Reworked fetching of template variable value,
  now it gets computed value instead of nominal;
  however, still not the inherited value.
# 1.0
- First public release.

TODO:
- provide output for ROR
--------------------------------------------------
*/

/* Parameters */
$startid = (isset($startid)) ? $startid : 0;
$seeThruUnpub = (isset($seeThruUnpub) && $seeThruUnpub == '0') ? false : true;
$format = (isset($format) && ($format != 'ror')) ? $format : 'sp';
$priority = (isset($priority)) ? $priority : 'sitemap_priority';
$changefreq = (isset($changefreq)) ? $changefreq : 'sitemap_changefreq';
$excludeTemplates = (isset($excludeTemplates)) ? $excludeTemplates : array();
$excludeTV = (isset($excludeTV)) ? $excludeTV : 'sitemap_exclude';
$xsl = (isset($xsl)) ? $xsl : '';
if (is_numeric($xsl)){ $xsl = $modx->makeUrl($xsl); }
$excludeWeblinks = (isset($excludeWeblinks)) ? $excludeWeblinks : false;
/* End parameters */

# get list of documents
$docs = getDocs($modx, $startid, $priority, $changefreq, $excludeTV, $seeThruUnpub);


# filter out documents by template or TV
# ---------------------------------------------
// get all templates
$select = $modx->db->select("id, templatename", $modx->getFullTableName('site_templates'));
while ($query = $modx->db->getRow($select)){
	$allTemplates[$query['id']] = $query['templatename'];
}

$remainingTemplates = $allTemplates;

// get templates to exclude, and remove them from the all templates list
if (!empty ($excludeTemplates)){
	
	$excludeTemplates = explode(",", $excludeTemplates);	
	
	// Loop through each template we want to exclude
	foreach ($excludeTemplates as $template){
		$template = trim($template);
		
		// If it's numeric, assume it's an ID, and remove directly from the $allTemplates array
		if (is_numeric($template) && isset($remainingTemplates[$template])){
			unset($remainingTemplates[$template]);
		}else if (trim($template) && in_array($template, $remainingTemplates)){ // If it's text, and not empty, assume it's a template name
			unset($remainingTemplates[array_search($template, $remainingTemplates)]);			
		}
	}
}

$output = array();
// filter out documents which shouldn't be included
foreach ($docs as $doc){
	//by template, excludeTV, published, searchable
	if (isset($remainingTemplates[$doc['template']]) && !$doc[$excludeTV] && $doc[$changefreq] != 'exclude' && $doc['published'] && $doc['template'] != 0 && $doc['searchable']){
		//exclude weblinks
		if (!$excludeWeblinks || ($excludeWeblinks && $doc['type'] != 'reference')){
			$output[] = $doc;
		}
	}
}
$docs = $output;
unset ($output, $allTemplates, $excludeTemplates);


# build sitemap in specified format
# ---------------------------------------------

switch ($format){
	// Next case added in version 1.0.4
	case 'ulli': // UL List
		$output .= "<ul class=\"sitemap\">\n";
		// TODO: Sort the array on Menu Index
		// TODO: Make a nested ul-li based on the levels in the document tree.
		foreach ($docs as $doc){
			$s  = "  <li class=\"sitemap\">";
			$s .= "<a href=\"".($doc['id'] != $modx->config['site_start']) ? '[(site_url)][~'.$doc['id'].'~]' : '[(site_url)]'."\" class=\"sitemap\">" . $doc['pagetitle'] . "</a>";
			$s .= "</li>\n";
			$output .= $s;
		}
		
		$output .= "</ul>\n";
	break;
		
	case 'txt': // plain text list of URLs

		foreach ($docs as $doc){
			$url = ($doc['id'] != $modx->config['site_start']) ? '[(site_url)][~'.$doc['id'].'~]' : '[(site_url)]';

			$output .= $url."\n";
		}
		
	break;

	case 'ror': // TODO
	default: // Sitemap Protocol
		$output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		if ($xsl != ''){
			$output .='<?xml-stylesheet type="text/xsl" href="'.$xsl.'"?>'."\n";
		}
		$output .='<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		
		
		foreach ($docs as $doc)	{
			$url = ($doc['id'] != $modx->config['site_start']) ? '[(site_url)][~'.$doc['id'].'~]' : '[(site_url)]';
			$date = $doc['editedon'];
			$date = date("Y-m-d", $date);
			$docPriority = ($doc[$priority]) ? $doc[$priority] : 0; // false if TV doesn't exist
			$docChangefreq = ($doc[$changefreq]) ? $doc[$changefreq] : 0; // false if TV doesn't exist
	
			$output .= "\t".'<url>'."\n";
			$output .= "\t\t".'<loc>'.$url.'</loc>'."\n";
			$output .= "\t\t".'<lastmod>'.$date.'</lastmod>'."\n";
			$output .= ($docPriority) ? ("\t\t".'<priority>'.$docPriority.'</priority>'."\n") : ''; // don't output anything if TV doesn't exist
			$output .= ($docChangefreq) ? ("\t\t".'<changefreq>'.$docChangefreq.'</changefreq>'."\n") : ''; // don't output anything if TV doesn't exist
			$output .= "\t".'</url>'."\n";
		}
		
		$output .= '</urlset>';

}

return $output;

# functions
# ---------------------------------------------

# gets (inherited) value of templat e variable
//TODO: wtf? In MODx 0.9.2.1 O_o Is this actually?
function getTV($modx, $docid, $doctv){
/* apparently in 0.9.2.1 the getTemplateVarOutput function doesn't work as expected and doesn't return INHERITED value; this is probably to be fixed for next release; see http://modxcms.com/bugs/task/464
	$output = $modx->getTemplateVarOutput($tv,$docid);
	return $output[$tv];
*/
	
	while ($pid = $modx->getDocument($docid, 'parent')){
		$tv = $modx->getTemplateVar($doctv,'*',$docid);
		if (($tv['value'] && substr($tv['value'],0,8) != '@INHERIT') or !$tv['value']){ // tv default value is overriden (including empty)
			$output = $tv['value'];
			break;
		}else{ // there is no parent with default value overriden 
			$output = trim(substr($tv['value'],8));
		}
		
		// move up one document in document tree
		$docid = $pid['parent'];
	}
	
	return $output;
}

# gets list of published documents with properties
function getDocs($modx, $startid, $priority, $changefreq, $excludeTV, $seeThruUnpub){
	//If need to see through unpublished
	if ($seeThruUnpub){
		//Get all children documents, filter later
		$docs = $modx->getAllChildren($startid, 'menuindex', 'asc', 'id,editedon,template,published,searchable,pagetitle,type');
	}else{
		//Get only published children documents
		$docs = $modx->getActiveChildren($startid, 'menuindex', 'asc', 'id,editedon,template,published,searchable,pagetitle,type');
	} 

	// add sub-children to the list
	foreach ($docs as $key => $doc){
		$id = $doc['id'];
		
		$docs[$key][$priority] = getTV($modx, $id, $priority); // add priority property
		$docs[$key][$changefreq] = getTV($modx, $id, $changefreq); // add changefreq property
		$docs[$key][$excludeTV] = getTV($modx, $id, $excludeTV); // add excludeTV property
		
		//TODO: $modx->getAllChildren & $modx->getActiveChildren always return the array
// 		if ($modx->getAllChildren($id)){
			$docs = array_merge($docs, getDocs($modx, $id, $priority, $changefreq, $excludeTV, $seeThruUnpub));
// 		}

	}
	return $docs;
}