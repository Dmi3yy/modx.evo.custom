<?php
/**
 * api:		php
 * title:	upgrade.php
 * description:	Emulates functions from new PHP versions on older interpreters.
 * version:	17
 * license:	Public Domain
 * url:		http://freshmeat.net/projects/upgradephp
 * type:	functions
 * category:	library
 * priority:	auto
 * load_if:     (PHP_VERSION<5.2)
 * sort:	-255
 * provides:	upgrade-php, api:php5, json
 *

/**
 * write-at-once file access (counterpart to file_get_contents)
 *
 * @param  integer $filename
 * @param  mixed   $content  
 * @param  integer $flags 
 * @param  mixed   $resource
 * @return integer
 */
if (!function_exists("file_put_contents")) {
   function file_put_contents($filename, $content, $flags=0, $resource=NULL) {

      #-- prepare
      $mode = ($flags & FILE_APPEND ? "a" : "w" ) ."b";
      $incl = $flags & FILE_USE_INCLUDE_PATH;
      $length = strlen($content);
//      $resource && trigger_error("EMULATED file_put_contents does not support \$resource parameter.", E_USER_ERROR);
      
      #-- write non-scalar?
      if (is_array($content) || is_object($content)) {
         $content = implode("", (array)$content);
      }

      #-- open for writing
      $f = fopen($filename, $mode, $incl);
      if ($f) {
      
         // locking
         if (($flags & LOCK_EX) && !flock($f, LOCK_EX)) {
            return fclose($f) && false;
         }

         // write
         $written = fwrite($f, $content);
         fclose($f);
         
         #-- only report success, if completely saved
         return($length == $written);
      }
   }
}

/**
 * file-related constants
 *
 */
if (!defined("FILE_USE_INCLUDE_PATH")) { define("FILE_USE_INCLUDE_PATH", 1); }
if (!defined("FILE_APPEND")) { define("FILE_APPEND", 8); }

#-- more new constants for 5.0
if (!defined("E_STRICT")) { define("E_STRICT", 2048); }  // _STRICT is a special case of _NOTICE (_DEBUG)

/**
 * @since unknown
 */
if (!defined("E_RECOVERABLE_ERROR")) { define("E_RECOVERABLE_ERROR", 4096); }

/**
 * Lowercase first character.
 *
 * @param string
 * @return string
 */
if (!function_exists("lcfirst")) {
   function lcfirst($str) {
      return strlen($str) ? strtolower($str[0]) . substr($str, 1) : "";
   }
}

/**
 * return array of filenames in a given directory
 * (only works for local files)
 *
 * @param  string $dirname  
 * @param  bool   $desc  
 * @return array
 */
if (!function_exists("scandir")) {
   function scandir($dirname, $desc=0) {
   
      #-- check for file:// protocol, others aren't handled
      if (strpos($dirname, "file://") === 0) {
         $dirname = substr($dirname, 7);
         if (strpos($dirname, "localh") === 0) {
            $dirname = substr($dirname, strpos($dirname, "/"));
         }
      }
      
      #-- directory reading handle
      if ($dh = opendir($dirname)) {
         $ls = array();
         while ($fn = readdir($dh)) {
            $ls[] = $fn;  // add to array
         }
         closedir($dh);
         
         #-- sort filenames
         if ($desc) {
            rsort($ls);
         }
         else {
            sort($ls);
         }
         return $ls;
      }

      #-- failure
      return false;
   }
}
