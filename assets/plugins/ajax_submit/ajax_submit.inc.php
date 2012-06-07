<?php

/*

ajaxSubmit 1.0.1 snippet for MODx 1.x
Andchir <http://modx-shopkeeper.ru/>

*/

$output = '';

define(AS_FPATH, MODX_BASE_PATH.'assets/plugins/ajax_submit/');
define(AS_URL_FPATH, MODX_BASE_URL.'assets/plugins/ajax_submit/');

$id = isset($id) ? $id : '';
$noJQuery = isset($noJQuery) ? $noJQuery : false;
$form = isset($form) ? $form : '';
$method = isset($method) ? $method : 'post';
$postSignal = isset($postSignal) ? $postSignal : 'ajax_submit';
$container = isset($container) ? $container : '';
$msgElem = isset($msgElem) ? $msgElem : 'div.error';
$msgMinLength = isset($msgMinLength) ? (int) $msgMinLength : 0;
$succesMessage = isset($succesMessage) ? $succesMessage : 'Спасибо! Ваше письмо отправлено.';
if($container && !$form) $form = $container.' form:first';

$outContainer = $container ? $container : $msgElem;

if(!$noJQuery){
  $modx->regClientStartupScript(AS_URL_FPATH."jquery-1.5.1.min.js",array('name'=>'jquery','version'=>'1.5.1','plaintext'=>false));
}

$request_uri = $_SERVER['REQUEST_URI'];

$output = <<< OUT
<script type="text/javascript">
<!--
function {$id}as_setAction(){
    jQuery("{$form}")
    .unbind('submit')
    .live('submit',function(){
        if(typeof({$id}as_reqStartCallback)=='function') {$id}as_reqStartCallback();
        var as_params = jQuery(this).serialize()+"&{$postSignal}={$outContainer}";
        jQuery.ajax({
          url: "{$request_uri}",
          type: "{$method}",
          data: as_params,
          dataType: 'html',
          success: function(response){
            if(typeof({$id}as_reqCompletCallback)=='function') {$id}as_reqCompletCallback();
            if(response=='success' || response.length <= {$msgMinLength}){
                jQuery("{$outContainer}").html("{$succesMessage}");
                if(typeof({$id}as_successCallback)=='function') {$id}as_successCallback();
            }else{
                jQuery("{$outContainer}").html(response);
            }
          },
          error: function(jqXHR,textStatus,errorThrown){
            alert(textStatus+' '+jqXHR.status+' '+errorThrown);
          }
        });
        return false;
    });
}
jQuery(document).bind('ready',{$id}as_setAction);
//-->
</script>
OUT;



return $output;

?>