<?php
$base_path = str_replace('\\','/',realpath('../../../../')) . '/';
require_once("{$base_path}manager/includes/protect.inc.php");
require_once("{$base_path}manager/includes/initialize.inc.php");
require_once("{$base_path}manager/includes/config.inc.php");
startCMSSession();
if(!isset($_SESSION['mgrValidated']))
{
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}

$rb = new FBROWSER();
$ph = array();
$ph['seturl_js'] = $rb->seturl_js();
$output = $rb->render_fbrowser($ph);
echo $output;

class FBROWSER
{
	function seturl_js()
	{
		$seturl_js_filename = 'seturl_js_'  . htmlspecialchars($_GET['editor']) . '.inc';
		$seturl_js_path = MODX_BASE_PATH . 'assets/plugins/';
		
		if(file_exists($seturl_js_path . $seturl_js_filename))
		{
			$result = file_get_contents($seturl_js_path . $seturl_js_filename);
		}
		else
		{
			$editor_path = htmlspecialchars($_GET['editorpath'], ENT_QUOTES);
			switch($_GET['editor'])
			{
				case 'tinymce' :
				case 'tinymce3':
					$editor_path = rtrim($editor_path, '/') . '/';
					$result = file_get_contents('seturl_js_tinymce.inc');
					$result = str_replace('[+editor_path+]', $editor_path, $result);
					break;
				default:
				$result = '<script src="seturl.js" type="text/javascript"></script>' . "\n";
			}
		}
		return $result;
	}
	
	function render_fbrowser($ph)
	{
		$browser_html = file_get_contents('browser.html.inc');
		$browser_html2 = $browser_html;
		foreach($ph as $name => $value)
		{
			$name = '[+' . $name . '+]';
			$browser_html = str_replace($name, $value, $browser_html);
		}
		return $browser_html;
			}
}
