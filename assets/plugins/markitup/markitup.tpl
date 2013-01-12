//<?php
/**
 * markItUp!
 * 
 * Universal markup editor
 *
 * @category 	plugin
 * @version 	1.1.6.1
 * @license 	MIT/GPL
 * @internal	@properties 
 * @internal	@events OnRichTextEditorRegister,OnRichTextEditorInit,OnChunkFormPrerender,OnTempFormPrerender
 * @internal	@modx_category Manager and Admin
 *
 * yama
 */

// Set the name of the plugin folder
	$plugindir = "markitup";

// Set path and base setting variables
	$params['markitup_path'] = MODX_BASE_PATH . 'assets/plugins/'.$plugindir . '/'; 
	$params['markitup_url']  = MODX_BASE_URL  . 'assets/plugins/'.$plugindir . '/'; 
	$params['elements']     = '';

include_once $params['markitup_path'] .'/markitup.functions.php';

// Handle event
$e = &$modx->Event; 
switch ($e->name)
{
	case 'OnRichTextEditorRegister': // register only for backend
		$e->output("markItUp");
		break;
		
	case 'OnChunkFormPrerender':
	case 'OnTempFormPrerender':
		$markitup_init = get_markitup_init($params);
		$e->output($markitup_init);
		break;
		
	case 'OnRichTextEditorInit':
		if($editor !== 'markItUp') return;
		$params['elements']     = $elements;
		$markitup_init = get_markitup_init($params, 'id');
		$e->output($markitup_init);
		break;
   default :    
      return; // stop here - this is very important. 
      break; 
}
