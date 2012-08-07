//<?php
/**
 * ajaxSubmit
 *
 * Ajax sending of any form
 *
 * @category    plugin
 * @version     1.0
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Andchir
 * @internal    @properties &post_signal=Post signal name;string;ajax_submit &check_referer=Check referer;list;yes,no;yes
 * @internal    @events OnLoadWebDocument,OnLoadWebPageCache
 * @internal    @modx_category
 */

if(!isset($post_signal)) $post_signal = 'ajax_submit';
if(!isset($check_referer)) $check_referer = 'yes';

$e = $modx->Event;

if ($e->name == 'OnLoadWebDocument' || $e->name == 'OnLoadWebPageCache'){
  
  $output = '';
  
  $referer_valid = $check_referer=='yes' ? in_array(strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST']),array(7,8)) : true;
  
  if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $referer_valid){
    
    if(isset($_POST[$post_signal])){
        
        class exDocumentParser extends DocumentParser {
          function sendRedirect() {
            return true;
          }
          function innerHTML($node){
            $doc = new DOMDocument();
            foreach ($node->childNodes as $child)
                $doc->appendChild($doc->importNode($child, true));
            return $doc->saveHTML();
          }
        }
        
        $xpath = $_POST[$post_signal];
        
        define(AS_PATH, MODX_BASE_PATH.'assets/plugins/ajax_submit/');
        define(AS_URL_PATH, MODX_BASE_URL.'assets/plugins/ajax_submit/');
        
        $parser = new exDocumentParser;
        $parser->db->connect();
        $parser->getSettings();
        $parser->config = $modx->config;
        $parser->documentObject = $modx->documentObject;
        $parser->documentIdentifier = $modx->documentIdentifier;
        $parser->aliasListing = $modx->aliasListing;
        $parser->snippetCache = $modx->snippetCache;
        $parser->chunkCache = $modx->chunkCache;
        $parser->documentListing = $modx->documentListing;
        $parser->documentMap = $modx->documentMap;
        
        $html = $modx->documentContent;
        $html = $parser->mergeChunkContent($html);
        $html = $parser->mergeDocumentContent($html);
        $html = $parser->mergeSettingsContent($html);
        $html = $parser->parseDocumentSource($html);
        if(strpos($html, '[!') > -1){
            $html = str_replace(array('[!','!]'),array('[[',']]'),$html);
            $html = $parser->parseDocumentSource($html);
        }
        $html = $parser->rewriteUrls($html);
        
        if($modx->config['modx_charset']=="UTF-8"){
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
        }
        
        //search snippet content in html
        require_once AS_PATH.'Dom/Query.php';
        $dom = new Zend_Dom_Query($html);
        $results = $dom->query($xpath);
        if (count($results) > 0){
            $output = $parser->innerHTML($results->current());
            $output = trim($output);
        }
        
        if(!$output) $output = 'success';
        
        echo $output;
        exit;
        
    }
    
  }
  
}
