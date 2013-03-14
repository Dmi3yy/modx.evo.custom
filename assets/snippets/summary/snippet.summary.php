<?php
  /**
 * summary
 *
 * @category snippet, DocLister
 * @internal @modx_category content
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @see https://github.com/AgelxNash/DocLister/blob/master/core/controller/extender/summary.extender.inc
 * @see http://wiki.modx.im/evolution:snippets:doclister:extender:summary
 * @date 14.03.2012
 * @version 1.0.0
 */
 if(!defined('MODX_BASE_PATH')) {die('HACK?');}
 
$dir = MODX_BASE_PATH. (isset($dir) ? $dir : 'assets/snippets/summary/');
$summary = $dir . "class.summary.php";

if(file_exists($summary) && !class_exists('SummaryText',false)){
		include_once($summary);
}

if(class_exists('SummaryText',false)){
  $action = array();
  
  if(!isset($tags)){
  	$action[]='notags';
  }
  
  if(isset($noparser)){
  	$action[]='noparser';
  }
  
  if(isset($len)){
  	$action[]='len'.((int)$len>0 ? ':'.(int)$len : '');
  }
  
  $action=implode(",",$action);
  
  $summary = new SummaryText($text,$action);
  $out = $summary->run();
  unset($summary);
}else{
	$out = $text;
}

return $out;
?>