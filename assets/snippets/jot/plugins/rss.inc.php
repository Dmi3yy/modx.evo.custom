<?php
function rss(&$object,$params){
	global $modx;

	$rss_link = $object->preserveUrl($modx->documentIdentifier,'',array_merge($object->_link,array($object->config["querykey"]["navigation"]=>NULL,$object->config["querykey"]["action"]=>'rss')));
	
	$rss_link_tpl = '<a class="jot-btn jot-rss" href="'.$rss_link.'"><i class="jot-icon-rss"></i> RSS</a>';

	$rss_header = '<?xml version="1.0" encoding="'.$modx->config['modx_charset'].'" ?>
	<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
		<channel>
		<title>Комментарии к: '.$modx->documentObject["pagetitle"].'</title>
		<link>'.$modx->makeUrl($modx->documentIdentifier,'','','full').'</link>
		<description>Комментарии к: '.$modx->documentObject["pagetitle"].'</description>
		<language>ru</language>
		<ttl>120</ttl>';

	$rss_tpl = '@CODE:
		<item>
			<title>[+comment.title:length:ne=`0`:then=`[+comment.title:esc+]`:else=`От: [+comment.username:ifempty=`[+comment.custom.name:ifempty=`[+jot.guestname+]`:esc+]`+]`+]</title>
			<link>'.$modx->makeUrl($modx->documentIdentifier,'','','full').'#jc[+jot.link.id+][+comment.id+]</link>
			<description><![CDATA[ [+comment.content:wordwrap:esc+] ]]></description>
			<pubDate>[+comment.createdon:date=`%a, %d %b %Y %T %z`+]</pubDate>
			<guid isPermaLink="false">'.$modx->makeUrl($modx->documentIdentifier,'','','full').'#jc[+jot.link.id+][+comment.id+]</guid>
			<dc:creator>[+comment.username:ifempty=`[+comment.custom.name:ifempty=`[+jot.guestname+]`:esc+]`+]</dc:creator>
		</item>';

	$rss_footer = '
	</channel>
	</rss>';

	switch($object->event) {
		case "onBeforeProcessPassiveActions":
			if ($object->config["mode"]["active"]=="rss") {
				$object->templates["comments"] = $rss_tpl;
				$object->config["sortby"] = "createdon:d";
				$object->config["pagination"] = 0;
				if (!$object->config["limit"]) $object->config["limit"] = 20;
				$object->config["mode"]["passive"] = "comments";
				setlocale(LC_TIME,'');
			}
			break;
		case "onSetCommentsOutput":
			if ($object->config["mode"]["active"]=="rss") {
				$res= $rss_header . $object->config["html"]["comments"] . $rss_footer;
				header('Content-Type: application/rss+xml; charset=' . $modx->config["modx_charset"]); 
				die($res);
			} else {
				$object->config["html"]["comments"] .= $rss_link_tpl;
			}
			break;
	}
}

?>