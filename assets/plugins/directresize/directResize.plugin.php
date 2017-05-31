<?php
/**
 * directResize
 *
 * Fully customizable plugin with a number of functions like: automatic thumbnails creation, watermarking (text or transparent PNG), using config files to set plugin parameters, big images openning with AJAX (lightbox, slimbox, highslide...), fully templateable output, thumbnails for WYSIWYG-editor etc...
 *
 * @category    plugin
 * @version     0.9.0
 * @author		Metaller
 * @author		PATRIOT
 * @internal    @events OnWebPagePrerender,OnCacheUpdate,OnBeforeDocFormSave,OnDocFormPrerender
 * @internal    @properties &config=Configuration;text;highslide;highslide &clearCache=Clear cache;list;0,1,2;2 &excludeDocs=Do not run on documents (,);string;
 * @internal    @installset base
 */

define('DIRECTRESIZE_PATH', "assets/plugins/directresize/");
define('DIRECTRESIZE_GALLERYDIR', "assets/drgalleries/");
include_once $modx->config['base_path'].DIRECTRESIZE_PATH."directResize.php";

global $content;

// BOF add excluded docs by AKA
if ($excludeDocs) {
	$excludes = explode(',', $excludeDocs);
	if (in_array($modx->documentIdentifier, $excludes)) return;
}
// EOF add excluded docs by AKA
		
$e = &$modx->Event;
switch ($e->name) {
  case "OnBeforeDocFormSave":
  		$content = ConvertFromBackend($_POST['ta']);
  		//if (isset($_POST[which_editor]) && $_POST[which_editor]!="none" && strlen($_POST['ta'])>0) $content['content']  = ConvertFromBackend($_POST['ta'], false);
    break;
    
	case "OnWebPagePrerender":
			//$modx->documentObject[content] = RenderOnFrontend($modx->documentObject[content], $config);
			$modx->documentOutput = RenderOnFrontend($modx->documentOutput, $config);
	break;
		
	case "OnDocFormPrerender":
		// Плагин инициируется только в визуальном редакторе. Без редактора замены больших картинок на превью нет
	         if (($modx->config['which_editor'] != "none" && empty($_POST)) || (isset($_POST['which_editor']) && $_POST['which_editor']!="none"))
		{		
			$content['content'] = RenderOnFrontend($content['content'], $config);
		}
		else
		{
			if (strlen($_POST['ta'])>0)  $content['content']  = ConvertFromBackend($_POST['ta'], false);
		}
	break;

	case "OnCacheUpdate":
			ClearDRCache($clearCache);
	break;	
	
	default :
		return;
	break;
}