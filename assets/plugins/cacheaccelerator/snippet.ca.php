<?php
//Функция сравнения. Работает в соответствии с функцией сравнения Ditto
if(!function_exists('cacheFieldsCompare')) {
    function cacheFieldsCompare ($param1, $param2, $param3){
/*
1 or != Не равно
2 or =  Равно
3 or <  Меньше чем 
4 or >  Больше чем
5 or <= Меньше чем или равно
6 or >= Больше чем или равно
7   Содержит
8   Не содержит
*/
        switch($param3){
           case 1:
              return $param1 != $param2;
              break;
           case 2:
              return $param1 == $param2;
              break; 
           case 3:
              return $param1 < $param2;
              break; 
           case 4:
              return $param1 > $param2;
              break; 
           case 5:
              return $param1 <= $param2;
              break;
           case 6:
              return $param1 >= $param2;
              break;
           case 7:
              return stristr($param1, $param2);
              break;
           case 8:
              return !stristr($param1, $param2);   
        }  
    }
}


if(!function_exists('isUserRole')){
  function isUserRole($array){
      $output = false;
      if(!isset($_SESSION['mgrPermissions']['name'])) return $output;
      $userRole = $_SESSION['mgrPermissions']['name'];
      foreach($array as $val){
          if($userRole == $val){
            $output = true;
            break;
          }
      }
      return $output;
  }
}

$snippetToCache = isset($snippetToCache) ? $snippetToCache : null;
$noHead = isset($noHead) ? $noHead : 0;
$nocache = isset($nocache)? $nocache : 0; //флаг необходимости сброса кеша
$checkURL = isset($checkURL) ? $checkURL : true;
$logMessages = isset($logMessages) ? $logMessages : false;
$cacheId = isset($cacheId) ? $cacheId : 'myCache';
$url = $_SERVER["REQUEST_URI"]; //текущий урл, включается в ключ для кеширования
$cacheKey = $checkURL ? $cacheId.$url : $cacheId;
$path_to_cacheengine=$modx->config['base_path']."assets/plugins/cacheaccelerator/"; //путь к директории с CacheAccelerator
$path_to_cache=$modx->config['base_path']."assets/plugins/cacheaccelerator/cache/"; //путь к директории для хранения кеша (может быть произвольым)
require_once ($path_to_cacheengine."fileCache.php"); //запрос класса fileCache
$cache = fileCache::GetInstance(84600*7,$path_to_cache);//создание инстанции класса fileCache
$cacheId = isset($cacheId) ? $cacheId : $modx->documentIdentifier; //проверка cacheId, если нет, то ID страницы
$tmp = explode(':', $cacheId); //извлечение группы из cacheId
if(isset($tmp[1])){
    $cacheId = trim($tmp[1]);
    $cacheGroup = trim($tmp[0]);
} else 
    $cacheGroup = 'default';
if(isset($dropCacheGroups) && $dropCacheGroups != 'all'){ //создание списка групп для сброса по условию
    $dropCacheGroups = trim(preg_replace('|\\s*(?:'.preg_quote(',').')\\s*|', ',', $dropCacheGroups));
    $dropCacheGroups = explode(",", $dropCacheGroups);
} else
    $dropCacheGroups = null;

//обработка флага принудительной очистки кеша
if((int)$clearCache){
    if($logMessages) echo("Clearing cache...");
    $cache->deleteCache(0, $dropCacheGroups);
    return;
}

//обработка групп пользователей и ролей менеджеров, для которых кеширование не производится (администраторы сайта, модераторы и тд)
$noCacheGroups = isset($noCacheGroups) ? $noCacheGroups : "";
$noCacheRoles = isset($noCacheRoles) ? $noCacheRoles : "";
if($noCacheGroups) $nocache = $modx->isMemberOfWebGroup(explode("||",$noCacheGroups)) ? 2 : $nocache;
if($noCacheRoles) 
    $nocache = isUserRole(explode("||",$noCacheRoles)) ? 2 : $nocache;
else
    $nocache = $modx->checkSession() ? 2 : $nocache;


if($nocache == 2){
       if($logMessages) echo("No caching for this web group.");
}

/* обработка стоп-полей, дающих сигнал на сброс кеша. в случае совпадения условий, кеш сбрасывается */
if(isset($dropCacheField)){
    $fieldsArray = explode("||", $dropCacheField);
    foreach ($fieldsArray as $field){
//        $field1a = explode(",", $field); //поддержка разделителя ','
        $field1 = explode(";", $field); 
//        if(count($field1a) > count($field1))
//            $field1 = $field1a;
        if($field1[1] && $field[2]){
          if(empty($field1[0])){
             foreach ($_POST as $key => $postField){ 
                  if(cacheFieldsCompare($postField, $field1[1], $field1[2])){
                     $nocache = 1;
                     continue; 
                  } 
             }
             foreach ($_GET as $key => $getField){
                  if(cacheFieldsCompare($getField, $field1[1], $field1[2])){
                     $nocache = 1;
                     continue;
                  }            
             }   
          } else {  
              if(!empty($_POST[$field1[0]])){
                  if(cacheFieldsCompare($_POST[$field1[0]], $field1[1], $field1[2])){
                     $nocache = 1;
                     continue; 
                  } 
              }
              if(!empty($_GET[$field1[0]])){
                  if(cacheFieldsCompare($_GET[$field1[0]], $field1[1], $field1[2])){
                     $nocache = 1;
                     continue;
                  } 
              } 
          }      
        } else {
           if(!empty($_POST[$field1[0]]) || !empty($_GET[$field1[0]]))
              $nocache = 1;
        }   
    }    

//непосредственно сброс кеша при совпадении условий
    if($nocache == 1){
        if($logMessages) echo("<b>Clearing cache...</b>");
        $cache->deleteCache(0, $dropCacheGroups);
    }  
}
if(is_null($snippetToCache)) //возвращаемся, если не указан snippetToCache
    return '';

//запрос результата работы сниппета из кеша
if($nocache == 0){
    $cached = $cache->cache($cacheId.$url, $cacheGroup);
    if(isset($cached)){
        if($logMessages) echo("<b>Cache hit!</b>");
        $this->placeholders = array_merge($this->placeholders, $cached['placeholders']); //установка плейсхолдеров закешированного сниппета
//        $modx->placeholders = $cached['placeholders']; 
        //регистрирование скриптов и css
        if($cached['head']){
            end($cached['head'][0]);
            end($cached['head'][1]);
            do {
                $tmp = current($cached['head'][0]);
                $modx->regClientScript(key($cached['head'][0]), $tmp, $tmp['startup'] == 1 ? true : false);
                end($modx->sjscripts);
                $modx->sjscripts[key($modx->sjscripts)] = current($cached['head'][1]);
                prev($cached['head'][1]);
            } while (prev($cached['head'][0]) !== false);
        }
        return $cached['content']; //возврат результата работы сниппета из кеша
    }
} 

//парсинг зарегистрированных скриптов и css
if(!empty($modx->loadedjscripts) && !$noHead){
    end($modx->loadedjscripts);
    $lastHeadKey = key($modx->loadedjscripts);
} else
    $lastHeadKey = '';
$output = $modx->runSnippet($snippetToCache, $modx->event->params); //непосредственное выолнение сниппета с передачей всех параметров
$head = array(array(), array());
if(!empty($modx->loadedjscripts) && !$noHead){
    if(end($modx->loadedjscripts) !== false){
        end($modx->sjscripts);
        do{
            $tmp = key($modx->loadedjscripts);
            if($tmp != $lastHeadKey){
                $head[0][$tmp] = current($modx->loadedjscripts);
                $head[1][key($modx->sjscripts)] = current($modx->sjscripts);
            } else
                break;
            prev($modx->sjscripts);
        } while (prev($modx->loadedjscripts) !== false);
    }
}
if(!count($head[0]))
    $head = null;

//помещение в кеш результата работы сниппета
if($nocache == 0){
    if($logMessages) echo("Storing to cache...");
    $cache->cache($cacheId.$url, $cacheGroup, array('placeholders' => $modx->placeholders, 'head' => $head, 'content' => $output));
}
//возвращение результата работы сниппета в парсер MODx
return($output);
?>