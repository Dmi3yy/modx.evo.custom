/*
 * ACE Plugin 0.01 by Dmi3yy
 * Usage:
 *          - Put all files coming with that archive into assets/plugins/ace
 *          - Create new empty Plugin in MODx and paste this code (omit the first and the last line).
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
        ta.id = 'textarea';
        with($(ta)) {          
          addClass('ace');
        }
    }
</script>
<script type="text/javascript" src="{$plugindir}/set.js"></script> 
<style>
input, select {color:#000 !important;}
</style>


HEREDOC;
$e->output($output);