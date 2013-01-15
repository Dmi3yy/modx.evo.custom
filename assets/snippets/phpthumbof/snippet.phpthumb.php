<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
//[[phpthumb? &input=`[+tvimagename+]` &options=`w_255,h=200`]]
if($input == '' || !file_exists($_SERVER['DOCUMENT_ROOT']."/".$input))
    {return 'assets/snippets/phpthumbof/noimage.png';}
else{   
     $replace  = Array("," => "&", "_" => "=");
    $options  = strtr($options, $replace);
    $options .= "&f=jpg&q=96";
    $opt = $options;
    $path_parts=pathinfo($input);
    //$pt = $modx->getPageInfo($modx->documentIdentifier);
    require_once $_SERVER['DOCUMENT_ROOT']."/assets/snippets/phpthumbof/phpthumb.class.php";
    $phpThumb = new phpthumb();
    $phpThumb->setSourceFilename($input); 
    $options = explode("&", $options);
    foreach ($options as $value) {
       $thumb = explode("=", $value);
       $phpThumb->setParameter($thumb[0], $thumb[1]);
       $op[$thumb[0]]=$thumb[1];
    }
  if ($seourl==1) {$outputFilename = $_SERVER['DOCUMENT_ROOT']."/assets/cache/phpthumbof/".md5($input.$opt).'.'.$op['f'];}
  else {
            $outputFilename = $_SERVER['DOCUMENT_ROOT']."/assets/cache/phpthumbof/".$path_parts['filename']."_w".$op['w'].'-h'.$op['h'].'.'.$op['f'];}
  
  
    if (!file_exists($outputFilename))
       if ($phpThumb->GenerateThumbnail())
           $phpThumb->RenderToFile($outputFilename) ;
    $res = explode("/assets", $outputFilename); 
    $res = "/assets".$res[1];
    return $res;
}
?>