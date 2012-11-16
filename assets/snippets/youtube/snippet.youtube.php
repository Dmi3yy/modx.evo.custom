<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
//SETTINGS
$width = isset($width)?$width:425;
$height = isset($height)?$height:350;

// YouTube API settings
$related = isset($related)?$related:true;
$autoplay = isset($autoplay)?$autoplay:false;
$loop = isset($loop)?$loop:false;
$disablekb = isset($disablekb)?$disablekb:false;
$egm = isset($egm)?$egm:false;
$border = isset($border)?$border:false;
$color1 = isset($color1)?$color1:'';
$color2 = isset($color2)?$color2:'';

// Javascript
$js = isset($js)?$js:true;
$swfobject_url = isset($swfobject_url)?$swfobject_url:'/assets/js/swfobject/swfobject.js';
$swfobject_express_url = isset($swfobject_url)?$swfobject_url:'/assets/js/swfobject/expressInstall.swf';
$missing = isset($missing)?$missing:'<p>This video requires the free Flash plugin.</p>';

// If a URL is supplied, work out the ID part of it
if (isset($url) && $ytid = (getIdFromUrl(urldecode($url))) ) {
   // $ytid is already set
} else if (isset($id)) {
   $ytid =  $id;
} else {   // No URL or ID supplied
   return;   
}

// Ensure the "related" value is appropriate for the YouTube URL
$related_val = ($related)?'&rel=1':'&rel=0';
$autoplay_val = ($autoplay)?'&autoplay=1':'&autoplay=0';
$loop_val = ($loop)?'&loop=1':'&loop=0';
$disablekb_val = ($disablekb)?'&disablekb=1':'&disablekb=0';
$egm_val = ($egm)?'&egm=1':'&egm=0';
$border_val = ($border)?'&border=1':'&border=0';
$color1_val = (!empty($color1))?'&color1='.$color1:'';
$color2_val = (!empty($color2))?'&color2='.$color2:'';

// Construct the YouTube URL
$yturl =' http://www.youtube.com/v/'.$ytid.$related_val.$autoplay_val.$loop_val.$disablekb_val.$egm_val.$border_val.$color1_val.$color2_val.'&hl=en';

// HTML template, based on cross-browser recommendation from SWFobject project
$html = '
   <object id="'.$ytid.'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'.$width.'" height="'.$height.'">
        <param name="movie" value="'.$yturl.'" />
        <!--[if !IE]>-->
        <object type="application/x-shockwave-flash" data="'.$yturl.'" width="'.$width.'" height="'.$height.'">
        <!--<![endif]-->
          '.$missing.'
        <!--[if !IE]>-->
        </object>
        <!--<![endif]-->
      </object>
';


// If we want to use javascript to check Flash version, etc.
if ($js) {
   // Register the SWFObject script
   $modx->regClientStartupScript($swfobject_url);
   
   // Activate the content with JS
   $script = '
   <script type="text/javascript">
    swfobject.registerObject("'.$ytid.'", "8.0.0", "'.$swfobject_express_url.'");
   </script>
   ';
   $modx->regClientStartupScript($script);
}

return $html;

if(!function_exists('getIdFromUrl')) {
  function getIdFromUrl($url) {
    $url_parts = parse_url($url);
    parse_str($url_parts['query']);
    if(isset($v)) {
      return $v;    
    } else {
      return false;   
    }
  }
}
?>