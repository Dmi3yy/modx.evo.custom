<?php
/**
 * @name        CodeMirror
 * @description JavaScript library that can be used to create a relatively pleasant editor interface
 *
 * @released    Jan 29, 2012
 * @CodeMirror  2.21
 *
 * @required    MODx 0.9.6.3+
 *              CodeMirror  2.21 : pl
 *
 * @confirmed   MODx Evolution 1.0.5
 *
 * @author      hansek from www.modxcms.cz  <http://www.modxcms.cz>
 *
 * @see         http://codemirror.net/
 */

global $content;

$textarea_name = 'post';
$object_name  = $content['name'];
$obect_type  = substr($evt->name, 2, 1);

$mode = 'htmlmixed';

$theme = 'default';

/*
 * Default Plugin configuration
 */
$indentUnit             = (isset($indentUnit)               ? $indentUnit               : 4);
$tabMode                = (isset($tabMode)                  ? $tabMode                  : 'shift');
$lineNumbers            = (isset($lineNumbers)              ? $lineNumbers              : true);
$matchBrackets          = (isset($matchBrackets)            ? $matchBrackets            : true);

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
        break;

    case 'OnSnipFormRender'   :
    case 'OnPluginFormRender' :
    case 'OnModFormRender'    :
        $mode  = 'php';
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

// object identifier for cursor position save in cookie
$object_id = md5($obect_type.'-'.$object_name);

if (('none' == $rte) && $mode) {
    $output = <<< HEREDOC
    <script src="{$_CM_URL}lib/codemirror.js"></script>
    <link rel="stylesheet" href="{$_CM_URL}lib/codemirror.css">
    <link rel="stylesheet" href="{$_CM_URL}theme/{$theme}.css">

    <script src="{$_CM_URL}mode/xml/xml.js"></script>
    <script src="{$_CM_URL}mode/javascript/javascript.js"></script>
    <script src="{$_CM_URL}mode/css/css.js"></script>
    <script src="{$_CM_URL}mode/clike/clike.js"></script>
    <script src="{$_CM_URL}mode/htmlmixed/htmlmixed.js"></script>
    <script src="{$_CM_URL}custom/php.js"></script>

    <link rel="stylesheet" href="{$_CM_URL}codemirror.plugin.css">
    <script src="{$_CM_URL}codemirror.plugin.js"></script>

    <script src="{$_CM_URL}custom/complete.js"></script>

    <script type="text/javascript">

        /*
         * Custom event handler
         */
        var myEventHandler = function(instance, event) {

            // CTRL + S
            if(event.ctrlKey == true && event.keyCode == 83) {
                try {
                    if(document.getElementById('Button1')) {
                        document.getElementById('Button1').getElementsByTagName('a')[0].onclick();
                    }
                }
                catch(event) {}

                return event.stop();
            }

            // CTRL + E
            if(event.ctrlKey == true && event.keyCode == 69) {
                try {
                    if(document.getElementById('Button1')) {
                        document.getElementById('Button1').getElementsByTagName('select')[0].options[1].selected = true;
                        document.getElementById('Button1').getElementsByTagName('a')[0].onclick();
                    }
                }
                catch(event) {}

                return event.stop();
            }
/*
            // CTRL + SPACE
            if(event.keyCode == 32 && (event.ctrlKey || event.metaKey) && !event.altKey) {
                event.stop();
                return startComplete();
            }
*/
        }

        /*
         * Save of cursor position
         */
        var positionHolder = function() {
            if(myCodeMirror) {
                myCodeMirror.setLineClass(hlLine, null);
                hlLine = myCodeMirror.setLineClass(myCodeMirror.getCursor().line, "activeline");

                position = myCodeMirror.getCursor(false).line +'|'+ myCodeMirror.getCursor(false).ch;

                setCookie('{$object_id}', position);
            }
        }

        /*
         * Main CodeMirror initialization
         */
        var myTextArea = document.getElementsByName('$textarea_name')[0];
        var myCodeMirror = CodeMirror.fromTextArea(myTextArea, {
            mode: '{$mode}',
            theme: '{$theme}',
            indentUnit: {$indentUnit},
            tabMode: '{$tabMode}',
            lineNumbers: {$lineNumbers},
            matchBrackets: {$matchBrackets},
            onKeyEvent: myEventHandler,
            onCursorActivity: positionHolder
        });

        var hlLine = myCodeMirror.setLineClass(0, "activeline");

        /*
         * Restore of cursor position
         */
        pos = getCookie('{$object_id}');
        if(pos) {
            pos = pos.split('|');

            myCodeMirror.setCursor(pos[0] , pos[1]);
        }

    </script>
HEREDOC;

    $modx->Event->output($output);
}
