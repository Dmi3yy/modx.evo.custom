<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
// Set the name of the plugin folder
$plugin_dir = "tinymce";

// Set path and base setting variables
if(!isset($mce_path))
{ 
	$mce_path = MODX_BASE_PATH . 'assets/plugins/'.$plugin_dir . '/'; 
	$mce_url  = MODX_BASE_URL  . 'assets/plugins/'.$plugin_dir . '/'; 
}

$params['customparams']    = $customparams;
$params['blockFormats']    = $mce_formats;
$params['entity_encoding'] = $entity_encoding;
$params['entities']        = $entities;
$params['pathoptions']     = $mce_path_options;
$params['resizing']        = $mce_resizing;
$params['disabledButtons'] = $disabledButtons;
$params['link_list']       = $link_list;
$params['theme']           = $webtheme;
$params['custom_plugins']  = $webPlugins;
$params['custom_buttons1'] = $webButtons1;
$params['custom_buttons2'] = $webButtons2;
$params['custom_buttons3'] = $webButtons3;
$params['custom_buttons4'] = $webButtons4;
$params['toolbar_align']   = $webAlign;
$params['width']           = $width;
$params['height']          = $height;

$params['mce_path']        = $mce_path;
$params['mce_url']         = $mce_url;

include_once $mce_path . 'lang/tinymce.lang.php';
include_once $mce_path . 'tinymce.functions.php';

$mce = new TinyMCE($params);

// Handle event
$e = &$modx->Event; 
switch ($e->name)
{
	case "OnRichTextEditorRegister": // register only for backend
		$e->output("TinyMCE");
		break;

	case "OnRichTextEditorInit": 
		if($editor!=="TinyMCE") return;
		
		$params['elements']        = $elements;
		$params['css_selectors']   = $modx->config['tinymce_css_selectors'];
		$params['use_browser']     = $modx->config['use_browser'];
		$params['editor_css_path'] = $modx->config['editor_css_path'];
		
		if($modx->isBackend() || (intval($_GET['quickmanagertv']) == 1 && isset($_SESSION['mgrValidated'])))
		{
			$params['theme']           = $modx->config['tinymce_editor_theme'];
			$params['language']        = getTinyMCELang($modx->config['manager_language']);
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
			
			$params['webuser']         = $webuser;
			$params['language']        = getTinyMCELang($frontend_language);
			$params['frontend']        = true;
			
			$html = $mce->get_mce_script($params);
		}
		$e->output($html);
		break;

	case "OnInterfaceSettingsRender":
		global $usersettings,$settings;
		$action = $modx->manager->action;
		switch ($action)
		{
			case 11:
				$mce_settings = '';
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
		
		$params['use_editor']       = $modx->config['base_url'].$modx->config['use_editor'];
        $params['editor_css_path']  = $modx->config['editor_css_path'];
		$params['theme']            = $mce_settings['tinymce_editor_theme'];
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
?>