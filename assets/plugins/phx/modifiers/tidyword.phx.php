<?php
// Get the Word infested input
$text = $output;

// Remove font tags
$text = strip_selected_tags($text, "<font>");

// Remove weird quotes and accents
// http://uk3.php.net/manual/en/function.preg-replace.php#64828
$text = preg_replace('/([\xc0-\xdf].)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 192) * 64 + (ord(substr('$1', 1, 1)) - 128)) . ';'", $text);
$text = preg_replace('/([\xe0-\xef]..)/se', "'&#' . ((ord(substr('$1', 0, 1)) - 224) * 4096 + (ord(substr('$1', 1, 1)) - 128) * 64 + (ord(substr('$1', 2, 1)) - 128)) . ';'", $text);

// Strip inline styles
$text = strip_styles($text);

// Remove  class="MsoNormal"
$text = str_replace('class="MsoNormal"', '', $text);

// Return it
return $text;



/**
* strip_selected_tags ( string str [, string strip_tags[, strip_content flag]] )
* ---------------------------------------------------------------------
* Like strip_tags() but inverse; the strip_tags tags will be stripped, not kept.
* strip_tags: string with tags to strip, ex: "<a><p><quote>" etc.
* strip_content flag: TRUE will also strip everything between open and closed tag
* http://uk3.php.net/manual/en/function.preg-replace.php#71266
*/
function strip_selected_tags($str, $tags = "", $stripContent = false)
{
    preg_match_all("/<([^>]+)>/i", $tags, $allTags, PREG_PATTERN_ORDER);
    foreach ($allTags[1] as $tag) {
    $replace = "%(<$tag.*?>)(.*?)(<\/$tag.*?>)%is";
        if ($stripContent) {
            $str = preg_replace($replace,'',$str);
        }
            $str = preg_replace($replace,'${2}',$str);
    }
    return $str;
}




// Remove styles
// http://uk3.php.net/manual/en/function.preg-replace.php#63219

function strip_styles($source=NULL) {
  $exceptions = str_replace(',', '|', 'text-align');

  /* First we want to fix anything that might potentially break the styler stripper, sow e try and replace
   * in-text instances of : with its html entity replacement.
   */

  function Replacer($text) {
    $check = array (
        '@:@s',
    );
    $replace = array(
        '&#58;',
    );

    return preg_replace($check, $replace, $text[0]);
  }

  $source = preg_replace_callback('@>(.*)<@Us', 'Replacer', $source);
  $regexp = '@([^;"]+)?(?<!'. $exceptions. ')(?<!\>\w):(?!\/\/(.+?)\/|<|>)((.*?)[^;"]+)(;)?@is';
  $source = preg_replace($regexp, '', $source);
  $source = preg_replace('@[a-z]*=""@is', '', $source);

  return $source;
}


?>
?>