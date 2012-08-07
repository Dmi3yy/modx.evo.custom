//<?php
/**
 * sitemap
 * 
 * google-sitemap.xml
 *
 * @category 	snippet
 * @version 	1.0.8
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties 
 * @internal	@modx_category add
 */


/*
==================================================
	sitemap
==================================================

Outputs a machine readable site map for search
engines and robots. Supports the following
formats:

- Sitemap Protocol used by Google Sitemaps
  (http://www.google.com/webmasters/sitemaps/)

- URL list in text format
  (e.g. Yahoo! submission)

Author: Grzegorz Adamiak [grad]
Version: 1.0.8 @ 22-AUG-2008
License: LGPL
MODx: 0.9.2.1, 0.9.6.1

History:
# 1.0.8
- excludeTemplates can now also be specified as a template ID instead of template name. 
  Useful if you change the names of your templates frequently. (ncrossland)
  e.g. &excludeTemplates=`myTemplateName,3,4`
# 1.0.7
- Unpublished and deleted documents were showing up in the sitemap. Even though they could not be viewed, 
  they were showing up as broken links to search engines. (ncrossland)
# 1.0.6
- Add optional parameter (excludeWeblinks) to exclude weblinks from the sitemap, since they often point to external
  sites (which don't belong on your sitemap), or redirecting to other internal pages (which are already
  in the sitemap). Google Webmaster Tools generates warnings for excessive redirects.	
  Default is false - e.g. default behaviour remains unchanged. (ncrossland)
# 1.0.5
- Modification about non searchable documents, as suggested by forum user JayBee
  (http://modxcms.com/forums/index.php/topic,5754.msg99895.html#msg99895)
# 1.0.4 (By Bert Catsburg, bert@catsburg.com)
- Added display option 'ulli'. 
  An <ul><li> list of all published documents.
# 1.0.3
- Added ability to specify the XSL URL - you don't always need one and it 
  seems to create a lot of support confusion!
  It is now a parameter (&xsl=``) which can take either an alias or a doc ID (ncrossland)
- Modifications suggested by forum users Grad and Picachu incorporated
  (http://modxcms.com/forums/index.php/topic,5754.60.html)
# 1.0.2
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

/* Parameters
----------------------------------------------- */

# $startid [ int ]
# Id of the 'root' document from which the sitemap
# starts.
# Default: 0

$startid = (isset($startid)) ? $startid : 0;

# $format [ sp | txt | ror ]
# Which format of sitemap to use:
# - sp <- Sitemap Protocol used by Google
# - txt <- text file with list of URLs
# TODO - ror <- Resource Of Resources
# Default: sp

$format = (isset($format) && ($format != 'ror')) ? $format : 'sp';

# $priority [ str ]
# Name of TV which sets the relative priority of
# the document. If there is no such TV, this
# parameter will not be used.
# Default: sitemap_priority

$priority = (isset($priority)) ? $priority : 'sitemap_priority';

# $changefreq [ str ]
# Name of TV which sets the change frequency. If
# there is no such TV this parameter will not be
# used.
# Default: sitemap_changefreq

$changefreq = (isset($changefreq)) ? $changefreq : 'sitemap_changefreq';

# $excludeTemplates [ str ]
# Documents based on which templates should not be
# included in the sitemap. Comma separated list
# with names of templates.
# Default: empty

$excludeTemplates = (isset($excludeTemplates)) ? $excludeTemplates : array();

# $excludeTV [ str ]
# Name of TV (boolean type) which sets document
# exclusion form sitemap. If there is no such TV
# this parameter will not be used.
# Default: 'sitemap_exclude'

$excludeTV = (isset($excludeTV)) ? $excludeTV : 'sitemap_exclude';

# $xsl [ str ] 
# URL to the XSL style sheet
# or
# $xsl [ int ]
# doc ID of the XSL style sheet

$xsl = (isset($xsl)) ? $xsl : '';
if (is_numeric($xsl)) { $xsl = $modx->makeUrl($xsl); }


# $excludeWeblinks [ bool ]
# Should weblinks be excluded?
# You may not want to include links to external sites in your sitemap,
# and Google gives warnings about multiple redirects to pages 
# within your site.
# Default: false
$excludeWeblinks = (isset($excludeWeblinks)) ? $excludeWeblinks : false;


/* End parameters
----------------------------------------------- */

# get list of documents
# ---------------------------------------------
$docs = getDocs($modx,$startid,$priority,$changefreq,$excludeTV);


# filter out documents by template or TV
# ---------------------------------------------
// get all templates
$select = $modx->db->select("id, templatename", $modx->getFullTableName('site_templates'));
while ($query = $modx->db->getRow($select)) {
	$allTemplates[$query['id']] = $query['templatename'];
}

$remainingTemplates = $allTemplates;

// get templates to exclude, and remove them from the all templates list
if (!empty ($excludeTemplates)) {
	
	$excludeTemplates = explode(",", $excludeTemplates);	
	
	// Loop through each template we want to exclude
	foreach ($excludeTemplates as $template) {
		$template = trim($template);
		
		// If it's numeric, assume it's an ID, and remove directly from the $allTemplates array
		if (is_numeric($template) && isset($remainingTemplates[$template])) {
			unset($remainingTemplates[$template]);
		} else if (trim($template) && in_array($template, $remainingTemplates)) { // If it's text, and not empty, assume it's a template name
			unset($remainingTemplates[array_search($template, $remainingTemplates)]);			
		}
	} // end foreach
}

$output= array();
// filter out documents which shouldn't be included
foreach ($docs as $doc)
{
	if (isset($remainingTemplates[$doc['template']]) && !$doc[$excludeTV] && $doc['published'] && $doc['template']!=0 && $doc['searchable']) {
		if (!$excludeWeblinks || ($excludeWeblinks && $doc['type'] != 'reference')) {
			$output[] = $doc;		
		}
	}
}
$docs = $output;
unset ($output, $allTemplates, $excludeTemplates);


# build sitemap in specified format
# ---------------------------------------------

switch ($format)
{
	// Next case added in version 1.0.4
	case 'ulli': // UL List
		$output .= "<ul class=\"sitemap\">\n";
		// TODO: Sort the array on Menu Index
		// TODO: Make a nested ul-li based on the levels in the document tree.
		foreach ($docs as $doc)
		{
			$s  = "  <li class=\"sitemap\">";
			$s .= "<a href=\"[(site_url)][~" . $doc['id'] . "~]\" class=\"sitemap\">" . $doc['pagetitle'] . "</a>";
			$s .= "</li>\n";
			$output .= $s;
		} // end foreach
		$output .= "</ul>\n";
		break;
		
	case 'txt': // plain text list of URLs

		foreach ($docs as $doc)
		{
			$url = '[(site_url)][~'.$doc['id'].'~]';

			$output .= $url."\n";
		} // end foreach
		break;

	case 'ror': // TODO
	default: // Sitemap Protocol

	
	$output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	if ($xsl != '') {
		$output .='<?xml-stylesheet type="text/xsl" href="'.$xsl.'"?>'."\n";
	}
	$output .='<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	
	
	foreach ($docs as $doc)	{
		$url = '[(site_url)][~'.$doc['id'].'~]';
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
	} // end foreach
	$output .= '</urlset>';

} // end switch

return $output;

# functions
# ---------------------------------------------

# gets (inherited) value of template variable
function getTV($modx,$docid,$doctv)
{
/* apparently in 0.9.2.1 the getTemplateVarOutput function doesn't work as expected and doesn't return INHERITED value; this is probably to be fixed for next release; see http://modxcms.com/bugs/task/464
	$output = $modx->getTemplateVarOutput($tv,$docid);
	return $output[$tv];
*/
	
	while ($pid = $modx->getDocument($docid,'parent'))
	{
		$tv = $modx->getTemplateVar($doctv,'*',$docid);
		if (($tv['value'] && substr($tv['value'],0,8) != '@INHERIT') or !$tv['value']) // tv default value is overriden (including empty)
		{
			$output = $tv['value'];
			break;
		}
		else // there is no parent with default value overriden 
		{
			$output = trim(substr($tv['value'],8));
		}
		$docid = $pid['parent']; // move up one document in document tree
	} // end while
	
	return $output;
}

# gets list of published documents with properties
function getDocs($modx,$startid,$priority,$changefreq,$excludeTV)
{
	// get children documents
	$docs = $modx->getActiveChildren($startid,'menuindex','asc','id,editedon,template,published,searchable,pagetitle,type'); 
	// add sub-children to the list
	foreach ($docs as $key => $doc)
	{
		$id = $doc['id'];
		$docs[$key][$priority] = getTV($modx,$id,$priority); // add priority property
		$docs[$key][$changefreq] = getTV($modx,$id,$changefreq); // add changefreq property
		$docs[$key][$excludeTV] = getTV($modx,$id,$excludeTV); // add excludeTV property
		
		if ($modx->getActiveChildren($id))
			$docs = array_merge($docs, getDocs($modx,$id,$priority,$changefreq,$excludeTV));
	} // end foreach
	return $docs;
}