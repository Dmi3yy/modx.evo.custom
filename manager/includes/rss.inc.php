<?php
 /*
 *  MODx Manager Home Page Implmentation by pixelchutes (www.pixelchutes.com)
 *  Based on kudo's kRSS Module v1.0.72
 *
 *  Written by: kudo, based on MagpieRSS
 *  Contact: kudo@kudolink.com
 *  Created: 11/05/2006 (November 5)
 *  For: MODx cms (modxcms.com)
 *  Name: kRSS
 *  Version (MODx Module): 1.0.72
 *  Version (Magpie): 0.72
 */

/* Configuration
---------------------------------------------- */
// Here you can set the urls to retrieve the RSS from. Simply add a $urls line following the numbering progress in the square brakets.

$urls['modx_news_content']             = $rss_url_news;
$urls['modx_security_notices_content'] = $rss_url_security;

// How many items per Feed?
$itemsNumber = '3';

/* End of configuration
NO NEED TO EDIT BELOW THIS LINE
---------------------------------------------- */

// include MagPieRSS
require_once($modx->config['base_path'] . 'manager/media/rss/rss_fetch.inc');

$feedData = array();

// create Feed
foreach ($urls as $section=>$url)
{
	$output = '';
	// While getting RSS, SESSION is closed temporarily.  
	if ( !headers_sent() )
	{
		$tmp_sessionname=session_name();
		session_write_close();
	}
	$rss = @fetch_rss($url);
	if ( isset($tmp_sessionname) )
	{
		session_start($tmp_sessionname);
	}
	if( !$rss )
	{
		$feedData[$section] = 'Failed to retrieve ' . $url;
		continue;
	}
	$output .= '<ul>';
	
	$items = array_slice($rss->items, 0, $itemsNumber);
	foreach ($items as $item)
	{
		$href    = $item['link'];
		$title   = $item['title'];
		$pubdate = $item['pubdate'];
		$pubdate = $modx->toDateFormat(strtotime($pubdate));
		$description = strip_tags($item['description']);
		if (strlen($description) > 199)
		{
			$description = substr($description, 0, 200);
			$description .= '...<br />Read <a href="'.$href.'" target="_blank">more</a>.';
		}
		$output .= '<li><a href="'.$href.'" target="_blank">'.$title.'</a> - <b>'.$pubdate.'</b><br />'.$description.'</li>';
	}
	$output .= '</ul>';
	$feedData[$section] = $output;
}
return $feedData;
