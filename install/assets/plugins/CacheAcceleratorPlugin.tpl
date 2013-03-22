//<?php
/**
 * CacheAcceleratorPlugin
 *
 * Clear CacheAccelerator cache files and managing autocache
 *
 * @category    plugin
 * @version     0.4b
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      thebat053
 * @internal    @properties &only_manual=Only manual clear;list;yes,no;no &autoCache=Autocache;list;enabled,disabled;disabled &autoCacheSnippets=Snippets to Autocache ('ditto,jot');string;all &autoCacheExcludeSnippets=Snippets to exclude ('snippet1, snippet2');string;include &dropCacheGroups=Groups to clear ('default, news');string;all &dropCacheFields=Drop cache fields;string;JotForm||post,true,2||,publish,2||,unpublish,2||,delete,2||,edit,2 &showSnippets=Show snippets log;list;full,short,no;no &showSystemData=Show system data;list;yes,no;no
 * @internal    @events OnLoadWebDocument,OnWebPagePrerender,OnCacheUpdate,OnSiteRefresh
 * @internal    @modx_category
 * @internal    @installset base
 */
if(!function_exists('processInjection')){
    function processInjection($str, $autoCacheSnippets, $autoCacheExcludeSnippets, $dropCacheFields, $showSnippets){
        $pos = 0;
        while($pos = strpos($str, '[!', $pos)){
            if($pos1 = strpos($str, '!]', $pos)){
                $pos3 = $pos1;
                $pos2 = strpos($str, '?', $pos);
                if($pos2 && $pos1 > $pos2){
                    $pos1 = $pos2;
                    $offs = 1;
                } else
                    $offs = 0;
                $pos+=2;
                $snippetToCache = trim(substr($str, $pos, $pos1 - $pos));
                $symb = strtolower($snippetToCache[0]);
                if($symb >= 'a' && $symb <= 'z' && strtolower($snippetToCache) != 'cacheaccelerator'){
                    if(!is_null($autoCacheSnippets)){
                        if(!in_array($snippetToCache, $autoCacheSnippets)){
                            continue;
                        }
                    }
                    if(!is_null($autoCacheExcludeSnippets)){
                        if(in_array($snippetToCache, $autoCacheExcludeSnippets)){
                            continue;
                        }
                    }
                    $dcfStr = ($dropCacheFields != '') ? ' &dropCacheField=`'.str_replace(',', ';', $dropCacheFields).'`' : '';
                    $logMessages = ($showSnippets != 'no') ? ' &logMessages=`1`' : '';
                    if($showSnippets == 'short')
                        echo('CacheAccelerator Caching: '.$snippetToCache.'<br />');
                    if($showSnippets == 'full')
                        echo('CacheAccelerator Caching: '.$snippetToCache.', Call: [!CacheAccelerator? &snippetToCache=`'.$snippetToCache.'`'.$logMessages.' &cacheId=`'.md5(substr($str, $pos, $pos3 - $pos)).'`'.$dcfStr.substr($str, $pos1, $pos3 - $pos1).'!]<br /><br />');

                    $str = substr($str, 0, $pos).'CacheAccelerator? &snippetToCache=`'.$snippetToCache.'`'.$logMessages.' &cacheId=`'.md5(substr($str, $pos, $pos3 - $pos)).'`'.$dcfStr.substr($str, $pos1 + $offs);
                }
            }
        }
        return $str;
    }
}

$only_manual = isset($only_manual) ? $only_manual : 'no';
$e = &$modx->Event;

if($e->name == 'OnWebPagePrerender'){
    if($showSystemData == 'yes'){
//        if($modx->checkSession()){

            $out = "<div style='clear:both'>&nbsp;</div>MySQL: [^qt^], [^q^] request(s), PHP: [^p^], total: [^t^], Memory: [^m^], document retrieved from [^s^]. ";
            $totalTime= ($modx->getMicroTime() - $modx->tstart);
            $queryTime= $modx->queryTime;
            $phpTime= $totalTime - $queryTime;

            $queryTime= sprintf("%2.4f s", $queryTime);
            $totalTime= sprintf("%2.4f s", $totalTime);
            $phpTime= sprintf("%2.4f s", $phpTime);
            $phpMemory = (memory_get_peak_usage(true) / 1024 / 1024) . " MB";
            $source= $modx->documentGenerated == 1 ? "database" : "cache";
            $queries= isset ($modx->executedQueries) ? $modx->executedQueries : 0;
            $out= str_replace("[^m^]", $phpMemory, $out);
            $out= str_replace("[^q^]", $queries, $out);
            $out= str_replace("[^qt^]", $queryTime, $out);
            $out= str_replace("[^p^]", $phpTime, $out);
            $out= str_replace("[^t^]", $totalTime, $out);
            $out= str_replace("[^s^]", $source, $out);
            $this->documentOutput .= $out;
//        }
    }
    return;
}

if($autoCache == 'enabled' && $e->name == 'OnLoadWebDocument'){
    if($autoCacheSnippets != '' && $autoCacheSnippets != 'all'){
        $autoCacheSnippets = trim(preg_replace('|\\s*(?:'.preg_quote(',').')\\s*|', ',', $autoCacheSnippets));
        $autoCacheSnippets = explode(",", $autoCacheSnippets);
    } else
        $autoCacheSnippets = null;
    if($autoCacheExcludeSnippets != ''){
        $autoCacheExcludeSnippets = trim(preg_replace('|\\s*(?:'.preg_quote(',').')\\s*|', ',', $autoCacheExcludeSnippets));
        $autoCacheExcludeSnippets = explode(",", $autoCacheExcludeSnippets);
    } else
        $autoCacheExcludeSnippets = null;
    foreach($modx->chunkCache as $key => $chunk)
        $modx->chunkCache[$key] = processInjection($chunk, $autoCacheSnippets, $autoCacheExcludeSnippets, $dropCacheFields, $showSnippets);
    $modx->documentContent = processInjection($modx->documentContent, $autoCacheSnippets, $autoCacheExcludeSnippets, $dropCacheFields, $showSnippets);
    $modx->documentObject['content'] = processInjection($modx->documentObject['content'], $autoCacheSnippets, $autoCacheExcludeSnippets, $dropCacheFields, $showSnippets);
    return;
}

if (($e->name == 'OnCacheUpdate' && $only_manual=='no') || ($e->name == 'OnSiteRefresh' && $only_manual=='yes')) {
    $path_to_cacheengine = $modx->config['base_path']."assets/plugins/cacheaccelerator/";
    $path_to_cache = $modx->config['base_path']."assets/plugins/cacheaccelerator/cache/";
    require_once $path_to_cacheengine."fileCache.php";
    $cache = fileCache::GetInstance(84600*7,$path_to_cache);
    if($dropCacheGroups != '' && $dropCacheGroups != 'all'){
        $dropCacheGroups = trim(preg_replace('|\\s*(?:'.preg_quote(',').')\\s*|', ',', $dropCacheGroups));
        $dropCacheGroups = explode(",", $dropCacheGroups);
        $cache->deleteCache(0, $dropCacheGroups);
    } else
        $cache->deleteCache(0);
    return;
}

