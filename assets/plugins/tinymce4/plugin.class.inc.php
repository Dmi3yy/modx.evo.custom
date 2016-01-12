<?php

class TinyMCE4
{
	var $params;
	
	function TinyMCE4()
	{
		global $modx;
		$current_path = str_replace('\\','/',dirname(__FILE__)).'/';
		if(strpos($current_path,MODX_BASE_PATH)!==false)
		{
			$path = substr($current_path,strlen(MODX_BASE_PATH));
		}
		else exit('Error');
		$this->params = $modx->event->params;
		$this->params['mce_path'] = MODX_BASE_PATH . $path; 
		$this->params['mce_url']  = MODX_BASE_URL  . $path; 
	}
	
	function get_mce_script()
	{
		global $modx, $_lang;
		$params = & $this->params;
		$mce_path = $params['mce_path'];
		$mce_url  = $params['mce_url'];
		$lang_code = $this->get_lang($modx->config['manager_language']);
		$elements = array();
		foreach($params['elements'] as $v)
		{
			$elements[] = "textarea#{$v}";
		}
		$params['elements'] = join(',', $elements);
		
		if($modx->isBackend() || (intval($_GET['quickmanagertv']) == 1 && isset($_SESSION['mgrValidated'])))
		{
			$params['frontend'] = false;
		}
		else
		{
			$params['frontend'] = true;
		}
		
		$cfg = array();
		$cfg['selector']     = $params['elements'];
		$cfg['content_css']  = "{$mce_url}style/content.css";
    	$cfg['document_base_url'] = MODX_SITE_URL;
		/*templates: [
		    {title: 'Test template 1', content: 'Test 1'},
		    {title: 'Test template 2', content: 'Test 2'}
		]*/
		
		$sfArray[] = array('title'=>'Paragraph','format'=>'p');
		$sfArray[] = array('title'=>'Header 1','format'=>'h1');
        $sfArray[] = array('title'=>'Header 2','format'=>'h2');
        $sfArray[] = array('title'=>'Header 3','format'=>'h3');
        $sfArray[] = array('title'=>'Header 4','format'=>'h4');
        $sfArray[] = array('title'=>'Header 5','format'=>'h5');
        $sfArray[] = array('title'=>'Header 6','format'=>'h6');
        $sfArray[] = array('title'=>'Div','format'=>'div');
		$sfArray[] = array('title'=>'Pre','format'=>'pre');
		if(isset($params['style_formats'])) {	
			$styles_formats = explode('|', $params['style_formats']);
			foreach ($styles_formats as $val) {
				$style = explode(',', $val);
				$sfArray[] = array('title'=>$style['0'], 'selector'=>'a,strong,em,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,tr,span,img', 'classes'=>$style['1']);
			}
		}
		$cfg['style_formats'] = json_encode($sfArray);

		$cfg['relative_urls'] = false;
		$cfg['image_caption'] = true;
    	$cfg['menubar'] = false;
    	$cfg['toolbar_items_size'] = 'small';
    	$cfg['image_advtab'] = true;
    	$cfg['plugins'] = "advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen spellchecker insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor codesample colorpicker textpattern imagetools paste modxlink";
    	$cfg['paste_word_valid_elements'] =  'a,p,b,strong,i,em,h1,h2,h3,h4,h5,h6,table,tbody,th,td,tr,tfooter,br,hr';
    	//template forecolor backcolor           
    	$cfg['toolbar1'] = "undo redo | cut copy paste | searchreplace | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | styleselect";
    	$cfg['toolbar2'] = "link unlink anchor image media codesample table | hr removeformat | subscript superscript charmap | nonbreaking | visualchars visualblocks print preview fullscreen code";


		if($lang_code!=='en')
			$cfg['language_url'] = "{$mce_url}tinymce/langs/{$lang_code}.js";
		
		foreach($cfg as $k=>$v)
		{
			if(strpos($v,"'")!==false)
				$v = str_replace("'", "\\'", $v);
			if (in_array($k, array('style_formats'))){
				$cfg[$k] = "    {$k}:{$v}";
			}else{
				$cfg[$k] = "    {$k}:'{$v}'";
			}
		}

		$ph['mce_url'] = $mce_url;
		$ph['init'] = join(",\n",$cfg);
		$tpl = file_get_contents("{$mce_path}tpl/tinymce_init.tpl");
		$rs = $modx->parseText($tpl,$ph);
		
		return $rs;
	}
	
	function get_lang($lang)
	{
		switch(strtolower($lang))
		{
			case 'bulgarian'             : $lc = 'bg_BG'; break;
			case 'czech'                 : $lc = 'cs'; break;
			case 'danish'                : $lc = 'da'; break;
			case 'german'                : $lc = 'de'; break;
			case 'spanish-utf8'          :
			case 'spanish'               : $lc = 'es'; break;
			case 'persian'               : $lc = 'fa'; break;
			case 'finnish'               : $lc = 'fi'; break;
			case 'francais'              :
			case 'francais-utf8'         : $lc = 'fr_FR'; break;
			case 'hebrew'                : $lc = 'he_IL'; break;
			case 'italian'               : $lc = 'it'; break;
			case 'japanese-utf8'         :
			case 'japanese-euc'          : $lc = 'ja'; break;
			case 'nederlands-utf8'       :
			case 'nederlands'            : $lc = 'nl'; break;
			case 'norsk'                 : $lc = 'nb_NO'; break;
			case 'polish-utf8'           :
			case 'polish'                : $lc = 'pl'; break;
			case 'portuguese-br'         : $lc = 'pt_BR'; break;
			case 'portuguese'            : $lc = 'pt_PT'; break;
			case 'russian'               :
			case 'russian-utf8'          : $lc = 'ru'; break;
			case 'svenska'               :
			case 'svenska-utf8'          : $lc = 'sv'; break;
			case 'chinese'               :
			case 'simple_chinese-gb2312' : $lc = 'zh_CN'; break;
			default             : $lc = 'en';
		}
		return $lc;
	}
	
	function get_skin_names()
	{
		global $modx, $_lang, $usersettings, $settings;
		$params = $this->params;
		$mce_path = $params['mce_path'];
		
		$skin_dir = "{$mce_path}tiny_mce/themes/advanced/skins/";
		switch($modx->manager->action)
		{
			case '11':
			case '12':
			case '74':
				$selected = $this->selected(empty($params['mce_editor_skin']));
				$option[] = '<option value="' . $value . '"' . $selected . '>' . "{$_lang['mce_theme_global_settings']}</option>";
				break;
		}
		foreach(glob("{$skin_dir}*",GLOB_ONLYDIR) as $dir)
		{
			$dir = str_replace('\\','/',$dir);
			$skin_name = substr($dir,strrpos($dir,'/')+1);
			$skins[$skin_name][] = 'default';
			$styles = glob("{$dir}/ui_*.css");
			if(is_array($styles) && 0 < count($styles))
			{
				foreach($styles as $css)
				{
					$skin_variant = substr($css,strrpos($css,'_')+1);
					$skin_variant = substr($skin_variant,0,strrpos($skin_variant,'.'));
					$skins[$skin_name][] = $skin_variant;
				}
			}
			foreach($skins as $k=>$o);
			{
				$v = '';
				foreach($o as $v)
				{
					if($v==='default') $value = $k;
					else               $value = "{$k}:{$v}";
					$selected = $this->selected($value == $params['mce_editor_skin']);
					$option[] = '<option value="' . $value . '"' . $selected . '>' . "{$value}</option>";
				}
			}
		}
		return join("\n",$option);
	}
	
	function selected($cond = false)
	{
		if($cond !== false) return ' selected="selected"';
		else                return '';
	}
	
	function checked($cond = false)
	{
		if($cond !== false) return ' checked="checked"';
		else                return '';
	}
	
	function get_mce_settings()
	{
		global $modx, $_lang, $usersettings, $settings;
		$params = & $this->params;
		$mce_path = $params['mce_path'];
		
		switch ($modx->manager->action)
		{
    		case 11:
        		$mce_settings = array();
        		break;
    		case 12:
    		case 74:
        		$mce_settings = $usersettings;
    			if(!empty($usersettings['tinymce_editor_theme']))
    			{
    				$usersettings['tinymce_editor_theme'] = $settings['tinymce_editor_theme'];
    			}
        		break;
    		case 17:
        		$mce_settings = $settings;
        		break;
    		default:
        		$mce_settings = $settings;
        		break;
    	}
		$params['theme']              = $mce_settings['tinymce_editor_theme'];
		$params['mce_editor_skin']    = $mce_settings['mce_editor_skin'];
		$params['mce_entermode']      = $mce_settings['mce_entermode'];
		$params['mce_element_format'] = $mce_settings['mce_element_format'];
		$params['mce_schema']         = $mce_settings['mce_schema'];
		$params['css_selectors']      = $mce_settings['tinymce_css_selectors'];
		$params['custom_plugins']     = $mce_settings['tinymce_custom_plugins'];
		$params['custom_buttons1']    = $mce_settings['tinymce_custom_buttons1'];
		$params['custom_buttons2']    = $mce_settings['tinymce_custom_buttons2'];
		$params['custom_buttons3']    = $mce_settings['tinymce_custom_buttons3'];
		$params['custom_buttons4']    = $mce_settings['tinymce_custom_buttons4'];
		$params['mce_template_docs']  = $mce_settings['mce_template_docs'];
		$params['mce_template_chunks']= $mce_settings['mce_template_chunks'];
		
		// language settings
		if (! @include_once("{$mce_path}lang/".$modx->config['manager_language'].'.inc.php'))
		{
			include_once("{$mce_path}lang/english.inc.php");
		}
	
		include_once("{$mce_path}settings/default_params.php");
		$ph += $_lang;
		
		switch($modx->manager->action)
		{
			case '11';
			case '12';
			case '74';
			$selected = empty($params['theme']) ? '"selected"':'';
			$theme_options .= '<option value="" ' . $selected . '>' . $_lang['mce_theme_global_settings'] . "</option>\n";
		}
		$themes['simple']   = $_lang['mce_theme_simple'];
		$themes['editor']   = $_lang['mce_theme_editor'];
		$themes['creative'] = $_lang['mce_theme_creative'];
		$themes['logic']    = $_lang['mce_theme_logic'];
		$themes['advanced'] = $_lang['mce_theme_advanced'];
		$themes['legacy']   = (!empty($_lang['mce_theme_legacy'])) ? $_lang['mce_theme_legacy'] : 'legacy';
		$themes['custom']   = $_lang['mce_theme_custom'];
		foreach ($themes as $key => $value)
		{
			$selected = $this->selected($key == $params['theme']);
			$key = '"' . $key . '"';
			$theme_options .= "<option value={$key}{$selected}>{$value}</option>\n";
		}
		$ph['display'] = ($_SESSION['browser']==='modern') ? 'table-row' : 'block';
		$ph['display'] = $modx->config['use_editor']==1 ? $ph['display']: 'none';
		
		$ph['theme_options'] = $theme_options;
		$ph['skin_options']  = $this->get_skin_names();
		
		$ph['entermode_options'] = '<label><input name="mce_entermode" type="radio" value="p" '.  $this->checked($ph['mce_entermode']=='p') . '/>' . $_lang['mce_entermode_opt1'] . '</label><br />';
		$ph['entermode_options'] .= '<label><input name="mce_entermode" type="radio" value="br" '. $this->checked($ph['mce_entermode']=='br') . '/>' . $_lang['mce_entermode_opt2'] . '</label>';
		switch($modx->manager->action)
		{
			case '11':
			case '12':
			case '74':
			$ph['entermode_options']  .= '<br />';
			$ph['entermode_options']  .= '<label><input name="mce_entermode" type="radio" value="" '.  $this->checked(empty($params['mce_entermode'])) . '/>' . $_lang['mce_theme_global_settings'] . '</label><br />';
			break;
		}
		
		$ph['element_format_options'] = '<label><input name="mce_element_format" type="radio" value="xhtml" '.  $this->checked($ph['mce_element_format']=='xhtml') . '/>XHTML</label><br />';
		$ph['element_format_options'] .= '<label><input name="mce_element_format" type="radio" value="html" '. $this->checked($ph['mce_element_format']=='html') . '/>HTML</label>';
		switch($modx->manager->action)
		{
			case '11':
			case '12':
			case '74':
			$ph['element_format_options']  .= '<br />';
			$ph['element_format_options']  .= '<label><input name="mce_element_format" type="radio" value="" '.  $this->checked(empty($params['mce_element_format'])) . '/>' . $_lang['mce_theme_global_settings'] . '</label><br />';
			break;
		}
		
		$ph['schema_options'] = '<label><input name="mce_schema" type="radio" value="html4" '.  $this->checked($ph['mce_schema']=='html4') . '/>HTML4(XHTML)</label><br />';
		$ph['schema_options'] .= '<label><input name="mce_schema" type="radio" value="html5" '. $this->checked($ph['mce_schema']=='html5') . '/>HTML5</label>';
		switch($modx->manager->action)
		{
			case '11':
			case '12':
			case '74':
			$ph['schema_options']  .= '<br />';
			$ph['schema_options']  .= '<label><input name="mce_schema" type="radio" value="" '.  $this->checked(empty($params['mce_schema'])) . '/>' . $_lang['mce_theme_global_settings'] . '</label><br />';
			break;
		}
		
		$gsettings = file_get_contents("{$mce_path}inc/gsettings.html.inc");
		
		foreach($ph as $name => $value)
		{
			$name = '[+' . $name . '+]';
			$gsettings = str_replace($name, $value, $gsettings);
		}
		return $gsettings;
	}
	
	function build_mce_init($plugins,$buttons1,$buttons2,$buttons3,$buttons4)
	{
		global $modx;
		$params = $this->params;
		$mce_path = $params['mce_path'];
		$mce_url  = $params['mce_url'];
		
		$ph['refresh_seed'] = filesize("{$mce_path}tiny_mce/tiny_mce.js");
		$ph['mce_url'] = $mce_url;
		$ph['elmList'] = implode(',', $params['elements']);
		$ph['width'] = (!empty($params['width'])) ? $params['width'] : '100%';
		$ph['height'] = (!empty($params['height'])) ? $params['height'] : '300';
		$ph['language'] = (empty($params['language'])) ? 'en' : $params['language'];
		if(strpos($modx->config['mce_editor_skin'],':')!==false)
		{
			list($skin,$skin_variant) = explode(':',$modx->config['mce_editor_skin']);
		}
		else $skin = $modx->config['mce_editor_skin'];
		$ph['skin']     = $skin;
		if($skin_variant) $ph['skin_variant'] = $skin_variant;
		else              $ph['skin_variant'] = '';
		
		$ph['document_base_url'] = MODX_SITE_URL;
		switch($params['mce_path_options'])
		{
			case 'Site config':
			case 'siteconfig':
				if($modx->config['strip_image_paths']==1)
				{
					$ph['relative_urls']      = 'true';
					$ph['remove_script_host'] = 'true';
					$ph['convert_urls']       = 'true';
				}
				else
				{
					$ph['relative_urls']      = 'false';
					$ph['remove_script_host'] = 'false';
					$ph['convert_urls']       = 'true';
				}
				break;
			case 'Root relative':
			case 'docrelative':
				$ph['relative_urls']      = 'true';
				$ph['remove_script_host'] = 'true';
				$ph['convert_urls']       = 'true';
				break;
			case 'Absolute path':
			case 'rootrelative':
				$ph['relative_urls']      = 'false';
				$ph['remove_script_host'] = 'true';
				$ph['convert_urls']       = 'true';
				break;
			case 'URL':
			case 'fullpathurl':
				$ph['relative_urls']      = 'false';
				$ph['remove_script_host'] = 'false';
				$ph['convert_urls']       = 'true';
				break;
			case 'No convert':
			default:
				$ph['relative_urls']      = 'true';
				$ph['remove_script_host'] = 'true';
				$ph['convert_urls']       = 'false';
		}
		
		if($modx->config['mce_entermode']!=='br' && $modx->manager->action !== '78')
		{
			$ph['forced_root_block']  = 'p';
			$ph['force_p_newlines']   = 'true';
			$ph['force_br_newlines']  = 'false';
		}
		else
		{
			$ph['forced_root_block']  = '';
			$ph['force_p_newlines']   = 'false';
			$ph['force_br_newlines']  = 'true';
		}
		$ph['element_format']          = $modx->config['mce_element_format'];
		$ph['schema']                  = $modx->config['mce_schema'];
		
		$ph['toolbar_align']           = $params['toolbar_align'];
		$ph['file_browser_callback']   = 'mceOpenServerBrowser';
		$ph['plugins']                 = $plugins;
		$ph['buttons1']                = $buttons1;
		$ph['buttons2']                = $buttons2;
		$ph['buttons3']                = $buttons3;
		$ph['buttons4']                = $buttons4;
		$ph['mce_formats']             = (empty($params['mce_formats'])) ? 'p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre,address' : $params['mce_formats'];
		$ph['css_selectors']           = (empty($params['css_selectors'])) ? $modx->config['tinymce_css_selectors'] : $params['css_selectors'];
		$ph['disabledButtons']         = isset($params['disabledButtons'])?$params['disabledButtons']:'';
		$ph['mce_resizing']            = $params['mce_resizing'];
		$ph['date_format']             = $modx->toDateFormat(null, 'formatOnly');
		$ph['time_format']             = '%H:%M:%S';
		$ph['entity_encoding']         = $params['entity_encoding'];
		$ph['terminate']               = (!empty($params['customparams'])) ? ',' : '';
		$ph['customparams']            = rtrim($params['customparams'], ',');
		$content_css[] = "{$mce_url}style/content.css";
		if     (preg_match('@^/@', $params['editor_css_path']))
		{
			$content_css[] = $params['editor_css_path'];
		}
		elseif (preg_match('@^http://@', $params['editor_css_path']))
		{
			$content_css[] = $params['editor_css_path'];
		}
		elseif ($params['editor_css_path']!=='')
		{
			$content_css[] = MODX_SITE_URL . $params['editor_css_path'];
		}
		$ph['content_css']             = join(',', $content_css);
		$ph['link_list']               = ($params['link_list']=='enabled') ? "'{$mce_url}js/tinymce.linklist.php'" : 'false';
		
		$ph['tpl_list']                = "{$mce_url}js/get_template.php";
	
		$mce_init = file_get_contents("{$mce_path}js/mce_init.js.inc");
		
		foreach($ph as $name => $value)
		{
			$name = '[+' . $name . '+]';
			$mce_init = str_replace($name, $value, $mce_init);
		}
		return $mce_init;
	}
	
	function build_tiny_callback()
	{
		global $modx;
		$params = $this->params;
		$mce_path = $params['mce_path'];
		$mce_url  = $params['mce_url'];
		
		$ph['cmsurl']  = MODX_BASE_URL . 'manager/media/browser/mcpuk/browser.php?editor=tinymce';
		$modx_fb = file_get_contents("{$mce_path}js/modx_fb.js.inc");
		
		foreach($ph as $name => $value)
		{
			$name = '[+' . $name . '+]';
			$modx_fb = str_replace($name, $value, $modx_fb);
		}
		return $modx_fb;
	}
}