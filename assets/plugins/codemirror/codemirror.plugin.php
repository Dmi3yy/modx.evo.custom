<?php
global $content;

$textarea_name = 'post';
$object_name  = $content['name'];
$obect_type  = substr($evt->name, 2, 1);
$mode = 'htmlmixed';

/*
 * Default Plugin configuration
 */
 
$theme                  = (isset($theme)                    ? $theme                    : 'default');
$indentUnit             = (isset($indentUnit)               ? $indentUnit               : 4);
$tabSize                = (isset($tabSize)                  ? $tabSize                  : 4);
$lineWrapping           = (isset($lineWrapping)             ? $lineWrapping             : false);
$matchBrackets        	= (isset($matchBrackets)            ? $matchBrackets           	: false);
$activeLine           	= (isset($activeLine)               ? $activeLine			    : false);
$selectionMatches       = (isset($selectionMatches)         ? $selectionMatches         : false);

/*
 * This plugin is only valid in "text" mode. So check for the current Editor
 */
$prte   = (isset($_POST['which_editor']) ? $_POST['which_editor'] : '');
$srte   = ($modx->config['use_editor'] ? $modx->config['which_editor'] : 'none');
$xrte   = $content['richtext'];

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
		switch($contentType){
			case "text/css":
				$mode = "text/css";
			break;
			case "text/javascript":
				$mode = "text/javascript";
			break;
			case "application/json":
				$mode = "application/json";
			break;
			default:
				$mode = "htmlmixed";
		}
        break;

    case 'OnSnipFormRender'   :
    case 'OnPluginFormRender' :
    case 'OnModFormRender'    :
        $mode  = 'application/x-httpd-php-open';
        $rte   = ($prte ? $prte : 'none');
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
	<link rel="stylesheet" href="{$_CM_URL}cm/addon.css">
	<link rel="stylesheet" href="{$_CM_URL}cm/theme/{$theme}.css">
	<script src="{$_CM_URL}cm/lib/codemirror.js"></script>
	<script src="{$_CM_URL}cm/addon.js"></script>
	<script src="{$_CM_URL}cm/addon/selection/active-line.js"></script>
	<script src="{$_CM_URL}cm/addon/search/searchcursor.js"></script>
	<script src="{$_CM_URL}cm/addon/search/match-highlighter.js"></script>
	<script src="{$_CM_URL}cm/addon/fold/foldcode.js"></script>
	<script src="{$_CM_URL}cm/addon/fold/brace-fold.js"></script>
	<script src="{$_CM_URL}cm/addon/fold/xml-fold.js"></script>
	<script src="{$_CM_URL}cm/addon/mode/overlay.js"></script>

	<script src="{$_CM_URL}cm/mode/xml/xml.js"></script>
	<script src="{$_CM_URL}cm/mode/javascript/javascript.js"></script>
	<script src="{$_CM_URL}cm/mode/css/css.js"></script>
	<script src="{$_CM_URL}cm/mode/clike/clike.js"></script>
	<script src="{$_CM_URL}cm/mode/htmlmixed/htmlmixed.js"></script>
	<script src="{$_CM_URL}cm/mode/php/php.js"></script>

	<script type="text/javascript">
		CodeMirror.defineMode("MODx", function(config, parserConfig) {
			var mustacheOverlay = {
				token: function(stream, state) {
					var ch;
					if (stream.match("[[")) {
						while ((ch = stream.next()) != null)
							if (ch == "]" && stream.next() == "]") break;
						stream.eat("]");
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
							if (ch == "!" && stream.next() == "]") break;
						stream.eat("]");
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
					while (stream.next() != null && !stream.match("[[", false) && !stream.match("{{", false) && !stream.match("[*", false) && !stream.match("[+", false) && !stream.match("[!", false) && !stream.match("[(", false) && !stream.match("[~", false) && !stream.match("[^", false)) {}
					return null;
				}
			};
			return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "{$mode}"), mustacheOverlay);
		});
		var config = {
			mode: 'MODx',
			theme: '{$theme}',
			indentUnit: {$indentUnit},
			tabSize: '{$tabSize}',
			lineNumbers: true,
			matchBrackets:{$matchBrackets},
			onKeyEvent: myEventHandler,
			lineWrapping: '{$lineWrapping}',
			gutters: ["CodeMirror-linenumbers", "breakpoints"],
			styleActiveLine: {$activeLine},
			highlightSelectionMatches: {$selectionMatches},
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
		var myCodeMirror = CodeMirror.fromTextArea(myTextArea, config);
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
