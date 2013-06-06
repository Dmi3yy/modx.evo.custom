<?php
/**
 * @name        CodeMirror
 * @description JavaScript library that can be used to create a relatively pleasant editor interface
 *
 * @released    Jun 5, 2013
 * @CodeMirror  3.13
 *
 * @required    MODx 0.9.6.3+
 *              CodeMirror  3.13 : pl
 *
 * @confirmed   MODx Evolution 1.10.0
 *
 * @author      Mihanik71 
 *
 * @see         https://github.com/Mihanik71/CodeMirror-MODx
 */
global $content;
$textarea_name = 'post';
$mode = 'htmlmixed';
$lang = 'htmlmixed';
/*
 * Default Plugin configuration
 */
$theme                  = (isset($theme)                    ? $theme                    : 'default');
$indentUnit             = (isset($indentUnit)               ? $indentUnit               : 4);
$tabSize                = (isset($tabSize)                  ? $tabSize                  : 4);
$lineWrapping           = (isset($lineWrapping)             ? $lineWrapping             : false);
$matchBrackets          = (isset($matchBrackets)            ? $matchBrackets            : false);
$activeLine           	= (isset($activeLine)             	? $activeLine            	: false);
$emmet					= (($emmet == 'true')? '<script src="'.$_CM_URL.'cm/emmet-compressed.js"></script>' : "");
/*
 * This plugin is only valid in "text" mode. So check for the current Editor
 */
$prte   = (isset($_POST['which_editor']) ? $_POST['which_editor'] : '');
$srte   = ($modx->config['use_editor'] ? $modx->config['which_editor'] : 'none');
$xrte   = $content['richtext'];
/*
 * Switch event
 */
switch($modx->Event->name) {
    case 'OnTempFormRender'   :
        $object_name = $content['templatename'];
    case 'OnChunkFormRender'  :
        $rte   = ($prte ? $prte : 'none');
        break;

    case 'OnDocFormRender'    :
        $textarea_name    = 'ta';
        $object_name = $content['pagetitle'];
        $xrte  = (('htmlmixed' == $mode) ? $xrte : 0);
        $rte   = ($prte ? $prte : ($content['id'] ? ($xrte ? $srte : 'none') : $srte));
		$contentType = $content['contentType'];
		/*
		* Switch contentType for doc
		*/
		switch($contentType){
			case "text/css":
				$mode = "text/css";
				$lang = "css";
			break;
			case "text/javascript":
				$mode = "text/javascript";
				$lang = "javascript";
			break;
			case "application/json":
				$mode = "application/json";
				$lang = "javascript";
			break;
		}
        break;

    case 'OnSnipFormRender'   :
    case 'OnPluginFormRender' :
    case 'OnModFormRender'    :
        $mode  = 'application/x-httpd-php-open';
        $rte   = ($prte ? $prte : 'none');
		$lang = "php";
        break;

    case 'OnManagerPageRender':
        if ((31 == $action) && (('view' == $_REQUEST['mode']) || ('edit' == $_REQUEST['mode']))) {
            $textarea_name = 'content';
            $rte   = 'none';
        }
        break;

    default:
        $this->logEvent(1, 2, 'Undefined event : <b>'.$modx->Event->name.'</b> in <b>'.$this->Event->activePlugin.'</b> Plugin', 'CodeMirror Plugin : '.$modx->Event->name);
}
if (('none' == $rte) && $mode) {
    $output = '';
    $output .= <<< HEREDOC
	<link rel="stylesheet" href="{$_CM_URL}cm/lib/codemirror.css">
	<link rel="stylesheet" href="{$_CM_URL}cm/theme/{$theme}.css">
	<script src="{$_CM_URL}cm/lib/codemirror-compressed.js"></script>
	<script src="{$_CM_URL}cm/addon-compressed.js"></script>
	<script src="{$_CM_URL}cm/mode/{$lang}-compressed.js"></script>
	{$emmet}
	
	<script type="text/javascript">
		// Add mode MODx for syntax highlighting. Dfsed on $mode
		CodeMirror.defineMode("MODx-{$mode}", function(config, parserConfig) {
			var mustacheOverlay = {
				token: function(stream, state) {
					var ch;
					if (stream.match("[[")) {
						while ((ch = stream.next()) != null)
							if (ch == "?" || (ch == "]"&& stream.next() == "]")) break;
						return "modxSnippet";
					}
					if (stream.match("{{")) {
						while ((ch = stream.next()) != null)
							if (ch == "}" && stream.next() == "}") break;
						stream.eat("}");
						return "modxChunk";
					}
					if (stream.match("[*")) {
						while ((ch = stream.next()) != null)
							if (ch == "*" && stream.next() == "]") break;
						stream.eat("]");
						return "modxTv";
					}
					if (stream.match("[+")) {
						while ((ch = stream.next()) != null)
							if (ch == "+" && stream.next() == "]") break;
						stream.eat("]");
						return "modxPlaceholder";
					}
					if (stream.match("[!")) {
						while ((ch = stream.next()) != null)
							if (ch == "?" || (ch == "!"&& stream.next() == "]")) break;
						return "modxSnippetNoCache";
					}
					if (stream.match("[(")) {
						while ((ch = stream.next()) != null)
							if (ch == ")" && stream.next() == "]") break;
						stream.eat("]");
						return "modxVariable";
					}
					if (stream.match("[~")) {
						while ((ch = stream.next()) != null)
							if (ch == "~" && stream.next() == "]") break;
						stream.eat("]");
						return "modxUrl";
					}
					if (stream.match("[^")) {
						while ((ch = stream.next()) != null)
							if (ch == "^" && stream.next() == "]") break;
						stream.eat("]");
						return "modxConfig";
					}
					if (stream.match("&")) {
						while ((ch = stream.next()) != null)
							if (ch == "=") break;
						stream.eat("=");
						return "attribute";
					}
					if (stream.match("!]")) {
						return "modxSnippet";
					}
					if (stream.match("]]")) {
						return "modxSnippetNoCache";
					}
					while (stream.next() != null && !stream.match("[[", false) && !stream.match("&", false) && !stream.match("{{", false) && !stream.match("[*", false) && !stream.match("[+", false) && !stream.match("[!", false) && !stream.match("[(", false) && !stream.match("[~", false) && !stream.match("[^", false) && !stream.match("!]", false) && !stream.match("]]", false)) {}
					return null;
				}
			};
			return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "{$mode}"), mustacheOverlay);
		});
		//Basic settings
		var config = {
			mode: 'MODx-{$mode}',
			theme: '{$theme}',
			indentUnit: {$indentUnit},
			tabSize: {$tabSize},
			lineNumbers: true,
			matchBrackets: {$matchBrackets},
			onKeyEvent: myEventHandler,
			lineWrapping: {$lineWrapping},
			gutters: ["CodeMirror-linenumbers", "breakpoints"],
			styleActiveLine: {$activeLine},
			indentWithTabs: true,
			extraKeys:{
				"Ctrl-Space": function(cm){
					foldFunc_html(cm, cm.getCursor().line);
				},
				"F11": function(cm) {
					setFullScreen(cm, !isFullScreen(cm));
				},
				"Esc": function(cm) {
					if (isFullScreen(cm)) setFullScreen(cm, false);
				}
			}
		};
		var foldFunc_html = CodeMirror.newFoldFunction(CodeMirror.tagRangeFinder);
		var myTextArea = document.getElementsByName('{$textarea_name}')[0];
		var myCodeMirror = (CodeMirror.fromTextArea(myTextArea, config));
		$$('.tab-row .tab').addEvents({
			click: function() {
				myCodeMirror.refresh();
			}
		});
		myCodeMirror.on("gutterClick", function(cm, n) {
			var info = cm.lineInfo(n);
			cm.setGutterMarker(n, "breakpoints", info.gutterMarkers ? null : makeMarker());
		});
		function makeMarker() {
			var marker = document.createElement("div");
			marker.style.color = "#822";
			marker.innerHTML = "●";
			return marker;
		}
    </script>
HEREDOC;
    $modx->Event->output($output);
}
