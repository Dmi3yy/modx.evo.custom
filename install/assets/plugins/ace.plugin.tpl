//<?
/**
 * ACE
 * 
 * подсветка кода в админке
 *
 * @category 	plugin
 * @version 	0.01
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &theme=theme;list;clouds,clouds_midnight,cobalt,crimson_editor,dawn,eclipse,idle_fingers,kr_theme,merbivore,merbivore_soft,mono_industrial,monokai,pastel_on_dark,solarized_dark,solarized_light,textmate,twilight,vibrant_ink;crimson_editor &gutter=gutter;list;true,false;false &fontSize=fontSize;list;10px,11px,12px,14px,16px;12px &showInvisibles=showInvisibles;list;true,false;false &useSoftTabs=useSoftTabs;list;true,false;true
 * @internal	@events OnTempFormRender,OnChunkFormRender,OnSnipFormRender,OnPluginFormRender,OnModFormRender
 * @internal    @legacy_names ACE
 * @internal    @installset base, sample
 */

/*
 * ACE Plugin 0.01 by Dmi3yy
 * Usage:
 *          - Put all files coming with that archive into assets/plugins/ace
 *          - Create new empty Plugin in MODx and paste this code (omit theg first and the last line).
 *          - Select one or more events in this list to use ACE:
 *               OnTempFormRender (Template editor)
 *               OnChunkFormRender (Chunk editor)
 *               OnSnipFormRender (Snippet editor)
 *               OnPluginFormRender (Plugin editor)
 *               OnModFormRender (Module editor) 
 *
 */

global $content;

$plugindir = MODX_BASE_URL.'assets/plugins/ace/';
$e = &$modx->Event;

switch($e->name) {
  case 'OnDocFormRender':
    if($content['richtext'] || ($content['type']=='reference')) return; 
    $ta = 'ta';
    break;
  case 'OnTempFormRender':
  case 'OnChunkFormRender':
     $ta = 'post';
     $mode = 'html';
     break;
  case 'OnSnipFormRender':
  case 'OnPluginFormRender':
  case 'OnModFormRender':
    $mode = 'php';
    $ta = 'post';
    break;
  default:
    return;
}


$output = <<<HEREDOC

<script type="text/javascript">
    var ta = document.getElementsByName('$ta')[0];
    if(ta && (ta.type=='textarea')) {
        ta.id = 'textarea';
    }

function inject() {
    var baseUrl = "/assets/plugins/ace/src/";
    function load(path, module, callback) {
        path = baseUrl + path;
        if (!load.scripts[path]) {
            load.scripts[path] = {
                loaded: false,
                callbacks: [ callback ]
            };

            var head = document.getElementsByTagName('head')[0];
            var s = document.createElement('script');

            function c() {
                if (window.__ace_shadowed__ && window.__ace_shadowed__.define.modules[module]) {
                    load.scripts[path].loaded = true;
                    load.scripts[path].callbacks.forEach(function(callback) {
                        callback();
                    });
                } else {
                    setTimeout(c, 50);
                }
            };
            s.src = path;
            head.appendChild(s);

            c();
        } else if (load.scripts[path].loaded) {
            callback();
        } else {
            load.scripts[path].callbacks.push(callback);
        }
    };

    load.scripts = {};
    window.__ace_shadowed_load__ = load;

    load('ace.js', 'text!ace/css/editor.css', function() {
        var ace = window.__ace_shadowed__;
        ace.options.mode = "$mode";
        ace.options.theme = "$theme";
        ace.options.gutter = "$gutter";
        ace.options.fontSize = "$fontSize";
        ace.options.softWrap = "off";
        ace.options.showPrintMargin = "false";
        ace.options.useSoftTabs = "$useSoftTabs";
        ace.options.showInvisibles = "$showInvisibles";
        
        
        
        var Event = ace.require('pilot/event');
        var areas = document.getElementsByTagName("textarea");
        for (var i = 0; i < areas.length; i++) {
            Event.addListener(areas[i], "click", function(e) {
                if (e.detail == 3) {
                    ace.transformTextarea(e.target);
                }
            });
        }
    });
}

// Call the inject function to load the ace files.
inject();

var textAce;
function initAce() {
    var ace = window.__ace_shadowed__;
    // Check if the ace.js file was loaded already, otherwise check back later.
    if (ace && ace.transformTextarea) {
        var t = document.querySelector("textarea");
        textAce = ace.transformTextarea(t);
        textAce.setDisplaySettings(false);
    } else {
        setTimeout(initAce, 100);
    }
}
// Transform the textarea on the page into an ace editor.
initAce();
</script> 

<style>
input, select {color:#000 !important;}
</style>

HEREDOC;
$e->output($output);