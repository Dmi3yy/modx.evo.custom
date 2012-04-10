//<?php
/**
 * TopicPath
 *
 * カスタマイズの自由度が高いパン屑リスト
 * 
 * @category	snippet
 * @version 	1.0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &theme=Theme;list;raw,list;raw
 * @internal	@modx_category Navigation
 */

include_once($modx->config['base_path'] . 'assets/snippets/topicpath/topicpath.class.inc.php');
$topicpath = new TopicPath();
return $topicpath->getTopicPath();
