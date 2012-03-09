//<?php
/**
 * custom Template
 * 
 * Newly created Resources use the template configured in plugin 
 *
 * @category    plugin
 * @version     0.1
 * @author		Dmi3yy (dmi3yy@gmail.com)
 * @internal    @properties &customTemplates=customTemplates;text;100,101|102,103
 * @internal    @events OnDocFormPrerender 
 */
 
/*
 * custom Template
 *
 * Written By Dmi3yy - 13 Sep 2011
 *
 *
 * Configuration:
 * check the OnDocFormPrerender event
 * add properties &customTemplates=customTemplates;text;100,101|102,103
 * Version 0.1
 *
 */

global $content;
$e = &$modx->Event;
$cusTemp=explode('|',$customTemplates);
switch($e->name) {
    case 'OnDocFormPrerender':        
      
        
        if ($parent = $modx->getPageInfo($_REQUEST['pid'],0,'template')) {

              foreach ($cusTemp as $value) {
                  $tpl=explode(',',$value);
                  if ($parent['template'] == $tpl[0]){ $content['template'] = $tpl[1];} 
              }   
                 
        }
     
        break;
    default:
        break;
}