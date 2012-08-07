<?php
/**
 * phpThumbOf
 *
 * Copyright 2009-2011 by Shaun McCormick <shaun@modx.com>
 * Port to MODx Evolution by Gorbarov Iliya <gorbarov@gmail.com>
 *
 * phpThumbOf is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * phpThumbOf is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * phpThumbOf; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package phpthumbof
 */

/**
 * A custom output filter for phpThumb
 *
 * @package phpthumbof
 */

if (empty($modx)) return '';

if (!class_exists('modPhpThumb')) {
    include($modx->config['base_path'].'assets/snippets/phpthumbof/modphpthumb.class.php');
}

if (empty($input)) {
    echo '/assets/snippets/phpthumbof/noimage.png';
    return '';
}


/* explode tag options */
$ptOptions = array();
$eoptions = explode('&',$options);
foreach ($eoptions as $opt) {
    $opt = explode('=',$opt);
    if (!empty($opt[0])) {
        $ptOptions[$opt[0]] = $opt[1];
    }
}
if (empty($ptOptions['f'])) $ptOptions['f'] = 'jpg';

/* if need add filter */
//$ptOptions['fltr'][] = 'ric|85|85'; 

/* load phpthumb */
$assetsPath = $modx->config['base_path'].'assets/cache/phpthumbof';
$phpThumb = new modPhpThumb($modx, $ptOptions);
$cacheDir = $assetsPath.'/';

/* check to make sure cache dir is writable */
/*if (!is_writable($cacheDir)) {
    if (!$modx->cacheManager->writeTree($cacheDir)) {
        $modx->log(modX::LOG_LEVEL_ERROR,'[phpThumbOf] Cache dir not writable: '.$assetsPath.'cache/');
        return '';
    }
}*/

/* do initial setup */
$phpThumb->initialize();
$phpThumb->setParameter('config_cache_directory',$cacheDir);
$phpThumb->setParameter('config_allow_src_above_phpthumb',true);
$phpThumb->setParameter('allow_local_http_src',true);
$phpThumb->setParameter('config_document_root',MODX_BASE_PATH);
$phpThumb->setCacheDirectory();

/* get absolute url of image */
if (strpos($input,'/') != 0 && strpos($input,'http') != 0) {
    $input = $modx->config['base_url'].$input;
} else {
    $input = urldecode($input);
}

if (file_exists($input)) {

	/* set source */
	$phpThumb->set($input);

	/* setup cache filename that is unique to this tag */
	$inputSanitized = str_replace(array(':','/'),'_',$input);
	$cacheFilename = $inputSanitized;
	$cacheFilename .= '.'.md5($options);
	$cacheFilename .= '.' . (!empty($ptOptions['f']) ? $ptOptions['f'] : 'png');
	$cacheKey = $cacheDir.$cacheFilename;

	/* get cache Url */
	$assetsUrl = $modx->config['site_url'].'assets/cache/phpthumbof';
	$cacheUrl = $assetsUrl.'/'.str_replace($cacheDir,'',$cacheKey);
	//$cacheUrl = str_replace('//','/',$cacheUrl);

	/* ensure we have an accurate and clean cache directory */
	$phpThumb->CleanUpCacheDirectory();

	/* ensure file has proper permissions */
	if (!empty($cacheKey)) {
		$filePerm = (int) '0664';
		@chmod($cacheKey, octdec($filePerm));
	}

	/* check to see if there's a cached file of this already */
	if (file_exists($cacheKey) && !$useS3 && !$expired) {
		echo $cacheUrl;
	} else {
		/* actually make the thumbnail */
		if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
			if ($phpThumb->RenderToFile($cacheKey)) {
				echo $cacheUrl;
			} else {
				echo '/assets/snippets/phpthumbof/noimage.png';
			}
		} else {
			echo '/assets/snippets/phpthumbof/noimage.png';
		}
	}
}else {
			echo '/assets/snippets/phpthumbof/noimage.png';
}
?>