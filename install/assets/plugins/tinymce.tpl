//<?php
/**
 * TinyMCE Rich Text Editor
 * 
 * Javascript WYSIWYG Editor
 *
 * @category 	plugin
 * @version 	3.4.9
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &customparams=Custom Parameters;textarea;valid_elements : "*[*]", &mce_formats=Block Formats;text;p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre &entity_encoding=Entity Encoding;list;named,numeric,raw;named &entities=Entities;text; &mce_path_options=Path Options;list;Site config,Absolute path,Root relative,URL,No convert;Site config &mce_resizing=Advanced Resizing;list;true,false;true &disabledButtons=Disabled Buttons;text; &link_list=Link List;list;enabled,disabled;enabled &webtheme=Web Theme;list;simple,editor,creative,custom;simple &webPlugins=Web Plugins;text;style,advimage,advlink,searchreplace,contextmenu,paste,fullscreen,xhtmlxtras,media &webButtons1=Web Buttons 1;text;undo,redo,selectall,|,pastetext,pasteword,|,search,replace,|,hr,charmap,|,image,link,unlink,anchor,media,|,cleanup,removeformat,|,fullscreen,code,help &webButtons2=Web Buttons 2;text;bold,italic,underline,strikethrough,sub,sup,|,|,blockquote,bullist,numlist,outdent,indent,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,|,styleprops &webButtons3=Web Buttons 3;text; &webButtons4=Web Buttons 4;text; &webAlign=Web Toolbar Alignment;list;ltr,rtl;ltr &width=Width;text;100% &height=Height;text;400
 * @internal	@events OnRichTextEditorRegister,OnRichTextEditorInit,OnInterfaceSettingsRender 
 * @internal	@modx_category Manager and Admin
 * @internal    @legacy_names TinyMCE
 * @internal    @installset base
 *
 * @author Jeff Whitfield
 * @author Mikko Lammi / updated: 03/09/2010
 * @author yama  / updated: 2011/06/17
 */

// Set the name of the plugin folder
$plugin_dir = "tinymce";
$mce_version = '3.4.9';

// Set path and base setting variables
if(!isset($mce_path))
{ 
	$mce_path = MODX_BASE_PATH . 'assets/plugins/'.$plugin_dir . '/'; 
	$mce_url  = MODX_BASE_URL  . 'assets/plugins/'.$plugin_dir . '/'; 
}
$params = $modx->event->params;
$params['mce_path']         = $mce_path;
$params['mce_url']          = $mce_url;

include_once $mce_path . 'functions.php';

$mce = new TinyMCE($params);

// Handle event
$e = &$modx->event; 
switch ($e->name)
{
	case "OnRichTextEditorRegister": // register only for backend
		$e->output("TinyMCE");
		break;

	case "OnRichTextEditorInit": 
		if($editor!=="TinyMCE") return;
		
		$params['mce_version']     = $mce_version;
		$params['css_selectors']   = $modx->config['tinymce_css_selectors'];
		$params['use_browser']     = $modx->config['use_browser'];
		$params['editor_css_path'] = $modx->config['editor_css_path'];
		
		if($modx->isBackend() || (intval($_GET['quickmanagertv']) == 1 && isset($_SESSION['mgrValidated'])))
		{
			$params['theme']           = $modx->config['tinymce_editor_theme'];
			$params['mce_editor_skin'] = $modx->config['mce_editor_skin'];
			$params['mce_entermode']   = $modx->config['mce_entermode'];
			$params['language']        = get_mce_lang($modx->config['manager_language']);
			$params['frontend']        = false;
			$params['custom_plugins']  = $modx->config['tinymce_custom_plugins'];
			$params['custom_buttons1'] = $modx->config['tinymce_custom_buttons1'];
			$params['custom_buttons2'] = $modx->config['tinymce_custom_buttons2'];
			$params['custom_buttons3'] = $modx->config['tinymce_custom_buttons3'];
			$params['custom_buttons4'] = $modx->config['tinymce_custom_buttons4'];
			$params['toolbar_align']   = $modx->config['manager_direction'];
			$params['webuser']         = null;
			
			$html = $mce->get_mce_script($params);
		}
		else
		{
			$frontend_language = isset($modx->config['fe_editor_lang']) ? $modx->config['fe_editor_lang']:'';
			$webuser = (isset($modx->config['rb_webuser']) ? $modx->config['rb_webuser'] : null);
			
			$params['theme']           = $webtheme;
			$params['webuser']         = $webuser;
			$params['language']        = get_mce_lang($frontend_language);
			$params['frontend']        = true;
			$params['custom_plugins']  = $webPlugins;
			$params['custom_buttons1'] = $webButtons1;
			$params['custom_buttons2'] = $webButtons2;
			$params['custom_buttons3'] = $webButtons3;
			$params['custom_buttons4'] = $webButtons4;
			$params['toolbar_align']   = $webAlign;
			
			$html = $mce->get_mce_script($params);
		}
		$e->output($html);
		break;

	case "OnInterfaceSettingsRender":
		global $usersettings,$settings;
		switch ($modx->manager->action)
		{
    		case 11:
        		$mce_settings = array();
        		break;
    		case 12:
        		$mce_settings = $usersettings;
        		break;
    		case 17:
        		$mce_settings = $settings;
        		break;
    		default:
        		$mce_settings = $settings;
        		break;
    	}
    	
		$params['theme']            = $mce_settings['tinymce_editor_theme'];
		$params['mce_editor_skin']  = $mce_settings['mce_editor_skin'];
		$params['mce_entermode']    = $mce_settings['mce_entermode'];
		$params['css_selectors']    = $mce_settings['tinymce_css_selectors'];
		$params['custom_plugins']   = $mce_settings['tinymce_custom_plugins'];
		$params['custom_buttons1']  = $mce_settings['tinymce_custom_buttons1'];
		$params['custom_buttons2']  = $mce_settings['tinymce_custom_buttons2'];
		$params['custom_buttons3']  = $mce_settings['tinymce_custom_buttons3'];
		$params['custom_buttons4']  = $mce_settings['tinymce_custom_buttons4'];
    	
		$html = $mce->get_mce_settings($params);
		$e->output($html);
		break;

   default :    
      return; // stop here - this is very important. 
      break; 
}
