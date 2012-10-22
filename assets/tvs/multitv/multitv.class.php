<?php
/**
 * multiTV
 *
 * @category 	classfile
 * @version 	1.4.7
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @author		Jako (thomas.jakobi@partout.info)
 *
 * @internal    description: <strong>1.4.7</strong> Transform template variables into a sortable multi item list.
 */
if (!function_exists('renderFormElement')) {
	include MODX_BASE_PATH . 'manager/includes/tmplvars.inc.php';
}

class multiTV {

	public $tvName = '';
	public $tvID = 0;
	public $tvCaption = '';
	public $tvDescription = '';
	public $tvDefault = '';
	public $tvValue = '';
	public $display = '';
	public $fieldnames = array();
	public $fieldtypes = array();
	public $fields = array();
	public $templates = array();
	public $language = array();
	public $configuration = array();

	// Init
	function multiTV($tvDefinitions) {
		global $modx;

		if (isset($tvDefinitions['name'])) {
			$this->tvName = $tvDefinitions['name'];
			$this->tvID = $tvDefinitions['id'];
			$this->tvCaption = $tvDefinitions['caption'];
			$this->tvDescription = $tvDefinitions['description'];
			$this->tvDefault = $tvDefinitions['default_text'];
			$this->tvValue = $tvDefinitions['value'];
		} else {
			$modx->messageQuit('No multiTV definitions set');
		}
		$settings = array();
		include ($this->includeFile($this->tvName));
		$this->tvSettings($settings);
		$language = array();
		include ($this->includeFile($modx->config['manager_language'], 'language'));
		$this->language = $language;
	}

	// Return the include path of a configuration/template/whatever file
	function includeFile($name, $type = 'config', $extension = '.inc.php') {

		$folder = (substr($type, -1) != 'y') ? $type . 's/' : substr($folder, 0, -1) . 'ies/';
		$allowedConfigs = glob(MTV_BASE_PATH . $folder . '*.' . $type . $extension);
		$configs = array();
		foreach ($allowedConfigs as $config) {
			$configs[] = preg_replace('=.*/' . $folder . '([^.]*).' . $type . $extension . '=', '$1', $config);
		}

		if (in_array($name, $configs)) {
			return MTV_BASE_PATH . $folder . $name . '.' . $type . $extension;
		} else {
			if (file_exists(MTV_BASE_PATH . $folder . 'default.' . $type . $extension)) {
				return MTV_BASE_PATH . $folder . 'default.' . $type . $extension;
			} else {
				return 'Allowed ' . $name . ' and default multiTV ' . $type . ' file "' . MTV_BASE_PATH . $folder . 'default.' . $type . $extension . '" not found. Did you upload all files?';
			}
		}
	}

	// Initialize customtv settings
	function tvSettings($settings) {
		$this->fields = $settings['fields'];
		$this->fieldnames = array_keys($this->fields);
		$this->fieldtypes = array();
		foreach ($this->fields as $field) {
			$this->fieldtypes[] = $field['type'];
		}
		$this->templates = $settings['templates'];
		$this->display = $settings['display'];
		$this->configuration['csvseparator'] = isset($settings['configuration']['csvseparator']) ? $settings['configuration']['csvseparator'] : ',';
		$this->configuration['enablePaste'] = isset($settings['configuration']['enablePaste']) ? $settings['configuration']['enablePaste'] : TRUE;
		$this->configuration['enableClear'] = isset($settings['configuration']['enableClear']) ? $settings['configuration']['enableClear'] : TRUE;
	}

	// invoke modx renderFormElement and change the output (to multiTV demands)
	function renderMultiTVFormElement($fieldType, $fieldName, $fieldElements, $fieldClass, $fieldDefault) {
		$fieldName .= '_mtv';
		$currentScript = array();
		$currentClass = array();
		switch ($fieldType) {
			case 'url' : {
					$fieldType = 'text';
					break;
				}
			case 'richtext' : {
					$fieldType = 'textarea';
					break;
				}
		}
		$formElement = renderFormElement($fieldType, 0, '', $fieldElements, '', '', array());
		$formElement = preg_replace('/( tvtype=\"[^\"]+\")/', '', $formElement); // remove tvtype attribute
		$formElement = preg_replace('/(<label[^>]*><\/label>)/', '', $formElement); // remove empty labels
		$formElement = preg_replace('/( id=\"[^\"]+)/', ' id="[+tvid+]' . $fieldName, $formElement); // change id attributes
		$formElement = preg_replace('/( name=\"[^\"]+)/', ' name="[+tvid+]' . $fieldName, $formElement); // change name attributes
		preg_match('/(<script.*?script>)/s', $formElement, $currentScript); // get script
		if (isset($currentScript[1])) { // the tv script is only included for the first tv that is using them (tv with image or file type)
			$formElement = preg_replace('/(<script.*?script>)/s', '', $formElement); // remove the script tag
			$currentScript[1] = preg_replace('/function SetUrl.*script>/s', '</script>', $currentScript[1]); // remove original SetUrl function
			$formElement = $formElement . $currentScript[1]; // move the script tag to the end
		}
		preg_match('/<.*class=\"([^\"]*)/s', $formElement, $currentClass); // get current classes
		$formElement = preg_replace('/class=\"[^\"]*\"/s', '', $formElement, 1); // remove all classes
		if ($fieldDefault != '') {
			$formElement = preg_replace('/(<\w+)/', '$1 alt="' . $fieldDefault . '"', $formElement, 1); // add alt to first tag (the input)
			$fieldClass .= ' setdefault';
		}
		$fieldClass = (isset($currentClass[1])) ? $currentClass[1] . ' ' . $fieldClass : $fieldClass;
		$formElement = preg_replace('/(<\w+)/', '$1 class="' . $fieldClass . '"', $formElement, 1); // add class to first tag (the input)
		$formElement = preg_replace('/<label for=[^>]*>([^<]*)<\/label>/s', '<label class="inlinelabel">$1</label>', $formElement); // add label class
		$formElement = preg_replace('/(onclick="BrowseServer[^\"]+\")/', 'class="browseimage ' . $fieldClass . '"', $formElement, 1); // remove imagebrowser onclick script
		$formElement = preg_replace('/(onclick="BrowseFileServer[^\"]+\")/', 'class="browsefile ' . $fieldClass . '"', $formElement, 1); // remove filebrowser onclick script
		$formElement = str_replace('document.forms[\'mutate\'].elements[\'tv0\'].value=\'\';document.forms[\'mutate\'].elements[\'tv0\'].onblur(); return true;', '$j(this).prev(\'input\').val(\'\').trigger(\'change\');', $formElement); // change datepicker onclick script
		$formElement = preg_replace('/( onmouseover=\"[^\"]+\")/', '', $formElement); // delete onmouseover attribute
		$formElement = preg_replace('/( onmouseout=\"[^\"]+\")/', '', $formElement); // delete onmouseout attribute
		$formElement = str_replace(array('&nbsp;'), ' ', $formElement); // change whitespace
		$formElement = str_replace(array('style="width:100%;"', 'style="width:100%"', ' width="100%"', '  width="100"', '<br />', 'onchange="documentDirty=true;"', " checked='checked'"), array(''), $formElement); // remove unused atrributes and tags
		return trim($formElement);
	}

	// build the output of multiTV script and css
	function generateScript() {
		global $modx;

		$tvid = "tv" . $this->tvID;
		$tvvalue = ($this->tvValue != '') ? $this->tvValue : '[]';
		$tvvalue = str_replace(array('[[', ']]'), array('[ [', '] ]'), $tvvalue);
		$tvfields = json_encode(array('fieldnames' => $this->fieldnames, 'fieldtypes' => $this->fieldtypes, 'csvseparator' => $this->configuration['csvseparator']));
		$tvlanguage = json_encode($this->language);
		$tvpath = '../' . MTV_PATH;

		// generate tv elements
		$tvcss = '';
		$hasthumb = '';

		switch ($this->display) {
			// horizontal template
			case 'horizontal': {
					$tvheading = '<div id="[+tvid+]heading" class="heading">' . "\r\n";
					foreach ($this->fieldnames as $fieldname) {
						$tvheading .= '<span class="inline ' . $fieldname . '">' . $this->fields[$fieldname]['caption'] . '</span>' . "\r\n";
						$type = (isset($this->fields[$fieldname]['type'])) ? $this->fields[$fieldname]['type'] : 'text';
						$elements = (isset($this->fields[$fieldname]['elements'])) ? $this->fields[$fieldname]['elements'] : '';
						$default = (isset($this->fields[$fieldname]['default'])) ? $this->fields[$fieldname]['default'] : '';
						$tvcss .= '.multitv #[+tvid+]list li.element .inline.' . $fieldname . ', .multitv #[+tvid+]heading .inline.' . $fieldname . ' { width: ' . $this->fields[$fieldname]['width'] . 'px }' . "\r\n";
						switch ($type) {
							case 'thumb': {
									$tvelement .= '<div class="inline tvimage" id="[+tvid+]' . $this->fields[$fieldname]['thumbof'] . '_mtvpreview"></div>';
									$hasthumb = ' hasthumb';
									break;
								}
							case 'date': {
									$tvelement .= $this->renderMultiTVFormElement($type, $fieldname, $elements, 'inline ' . $fieldname, $default) . "\r\n";
									$tvcss .= '.multitv #[+tvid+]list li.element .inline.' . $fieldname . ' { width: ' . strval($this->fields[$fieldname]['width'] - 48) . 'px }' . "\r\n";
									break;
								}
							default: {
									$tvelement .= $this->renderMultiTVFormElement($type, $fieldname, $elements, 'inline ' . $fieldname, $default) . "\r\n";
								}
						}
					}
					$tvheading .= '</div>' . "\r\n";
					// wrap tvelements
					$tvelement = '<li class="element inline' . $hasthumb . '"><div>' . $tvelement;
					$tvelement .= '<a href="#" class="copy" title="[+tvlang.add+]">[+tvlang.add+]</a>' . "\r\n";
					$tvelement .= '<a href="#" class="remove" title="[+tvlang.remove+]">[+tvlang.remove+]</a>' . "\r\n";
					$tvelement .= '</div><div class="clear"></div></li>' . "\r\n";
					break;
				}
			// vertical template
			case 'vertical': {
					$tvheading = '';
					foreach ($this->fieldnames as $fieldname) {
						$type = (isset($this->fields[$fieldname]['type'])) ? $this->fields[$fieldname]['type'] : 'text';
						$elements = (isset($this->fields[$fieldname]['elements'])) ? $this->fields[$fieldname]['elements'] : '';
						$default = (isset($this->fields[$fieldname]['default'])) ? $this->fields[$fieldname]['default'] : '';
						switch ($type) {
							case 'thumb': {
									$tvelement .= '<div class="tvimage" id="[+tvid+]' . $this->fields[$fieldname]['thumbof'] . '_mtvpreview"></div>';
									$hasthumb = ' hasthumb';
									break;
								}
							default: {
									$tvelement .= '<label for="[+tvid+]' . $fieldname . '">' . $this->fields[$fieldname]['caption'] . '</label>';
									$tvelement .= $this->renderMultiTVFormElement($type, $fieldname, $elements, $fieldname, $default) . '<br />' . "\r\n";
								}
						}
					}
					$tvelement = '<li class="element' . $hasthumb . '"><div>' . $tvelement;
					$tvelement .= '<a href="#" class="copy" title="[+tvlang.add+]">[+tvlang.add+]</a>' . "\r\n";
					$tvelement .= '<a href="#" class="remove" title="[+tvlang.remove+]">[+tvlang.remove+]</a>' . "\r\n";
					$tvelement .= '</div><div class="clear"></div></li>' . "\r\n";
					break;
				}
			// horizontal template
			case 'single': {
					$tvheading = '';
					foreach ($this->fieldnames as $fieldname) {
						$type = (isset($this->fields[$fieldname]['type'])) ? $this->fields[$fieldname]['type'] : 'text';
						$elements = (isset($this->fields[$fieldname]['elements'])) ? $this->fields[$fieldname]['elements'] : '';
						$default = (isset($this->fields[$fieldname]['default'])) ? $this->fields[$fieldname]['default'] : '';
						switch ($type) {
							case 'thumb': {
									$tvelement .= '<div class="tvimage" id="[+tvid+]' . $this->fields[$fieldname]['thumbof'] . '_mtvpreview"></div>';
									$hasthumb = ' hasthumb';
									break;
								}
							default: {
									$tvelement .= '<label for="[+tvid+]' . $fieldname . '">' . $this->fields[$fieldname]['caption'] . '</label>';
									$tvelement .= $this->renderMultiTVFormElement($type, $fieldname, $elements, $fieldname, $default) . '<br />' . "\r\n";
								}
						}
					}
					$tvelement = '<li class="element single' . $hasthumb . '"><div>' . $tvelement;
					$tvelement .= '</div><div class="clear"></div></li>' . "\r\n";
					break;
				}
			// inline popup - i.e. if there are too much elements in one row	
			case 'popup': {
					// Todo
					$tvheading = '';
					$tvelement = '';
					break;
				}
		}

		// populate tv template
		$scriptfiles = array();
		$cssfiles = array();
		$settings = array();
		$paste = '';

		if ($this->configuration['enablePaste']) {
			include ($this->includeFile('paste', 'setting'));
			$paste = file_get_contents($this->includeFile('paste', 'template', '.html'));
		} else {
			include ($this->includeFile('default', 'setting'));
		}
		if ($this->configuration['enableClear']) {
			$clear = file_get_contents($this->includeFile('clear', 'template', '.html'));
		}
		foreach ($settings['css'] as $setting) {
			$cssfiles[] = '	<link rel="stylesheet" type="text/css" href="' . $setting . '" />';
		}
		foreach ($settings['scripts'] as $setting) {
			$scriptfiles[] = '	<script type="text/javascript" src="' . $setting . '"></script>';
		}

		// Check for ManagerManager 
		$res = $modx->db->select('*', $modx->getFullTableName('site_plugins'), 'name="ManagerManager" AND disabled=0 ');
		$mmActive = $modx->db->getRow($res);
		if ($mmActive) {
			unset($scriptfiles[0]); // don't include jQuery if ManagerManager is active
		}

		$tvtemplate = file_get_contents($this->includeFile('multitv', 'template', '.html'));

		$placeholder = array();
		$placeholder['cssfiles'] = implode("\r\n", $cssfiles);
		$placeholder['scriptfiles'] = implode("\r\n", $scriptfiles);
		$placeholder['paste'] = $paste;
		$placeholder['clear'] = $clear;
		$placeholder['tvcss'] = $tvcss;
		$placeholder['tvheading'] = $tvheading;
		$placeholder['tvmode'] = $this->display;
		$placeholder['tvfields'] = $tvfields;
		$placeholder['tvlanguage'] = $tvlanguage;
		$placeholder['tvelement'] = $tvelement;
		$placeholder['tvvalue'] = $tvvalue;
		$placeholder['tvid'] = $tvid;
		$placeholder['tvpath'] = $tvpath;

		foreach ($this->language as $key => $value) {
			$placeholder['tvlang.' . $key] = $value;
		}
		foreach ($placeholder as $key => $value) {
			$tvtemplate = str_replace('[+' . $key . '+]', $value, $tvtemplate);
		}
		return $tvtemplate;
	}

}

?>
