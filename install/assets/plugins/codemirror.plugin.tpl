//<?
/**
 * CodeMirror
 * 
 * подсветка кода в админке
 *
 * @category 	plugin
 * @version 	0.04
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties
 * @internal	@events OnTempFormRender,OnChunkFormRender,OnSnipFormRender,OnPluginFormRender,OnModFormRender
 * @internal	@modx_category Search
 * @internal    @legacy_names Search Highlighting
 * @internal    @installset base, sample
 */

/*
 * CodeMirror Plugin 0.04 by Dmi3yy
 * Usage:
 *          - Put all files coming with that archive into assets/plugins/codemirror
 *          - Create new empty Plugin in MODx and paste this code (omit the first and the last line).
 *          - Select one or more events in this list to use CodePress:
 *               OnDocFormRender (Documents editor - work only if Richtext turn-off)
 *               OnTempFormRender (Template editor)
 *               OnChunkFormRender (Chunk editor)
 *               OnSnipFormRender (Snippet editor)
 *               OnPluginFormRender (Plugin editor)
 *               OnModFormRender (Module editor) 
 *
 */

global $content;

$plugindir = MODX_BASE_URL.'assets/plugins/codemirror/';
$e = &$modx->Event;

switch($e->name) {
  case 'OnDocFormRender':
    if($content['richtext'] || ($content['type']=='reference')) return; 
    $ta = 'ta';
    break;
  case 'OnTempFormRender':
  case 'OnChunkFormRender':
  case 'OnSnipFormRender':
  case 'OnPluginFormRender':
  case 'OnModFormRender':
    $ta = 'post';
    break;
  default:
    return;
}



// Javascript Code
$output = <<<HEREDOC

<script type="text/javascript">
    var ta = document.getElementsByName('$ta')[0];
    if(ta && (ta.type=='textarea')) {
        ta.id = 'code';
        with($(ta)) {          
          addClass('codemirror');
        }
    }
</script>

<link rel="stylesheet" href="{$plugindir}/cm.css" type="text/css" /> 
<script type="text/javascript" src="{$plugindir}/cm.js"></script> 
<script type="text/javascript" src="{$plugindir}set.js"></script> 

HEREDOC;
$e->output($output);