//<?php

/**
 * devStat
 * 
 * Logging developer info
 *
 * @category plugin
 * @version 1.0
 * @internal @properties &alert_time=Критичное время генерации;int;3 &alert_query=Критичное число запросов;int;100
 * @internal @events OnWebPagePrerender
 * @internal @legacy_names devStat
 * @author Andchir <http://wdevblog.net.ru>
 */  

$e = &$modx->Event;

if ($e->name == 'OnWebPagePrerender'){
  
  if(!isset($alert_time)) $alert_tim = 3;
  if(!isset($alert_query)) $alert_query = 100;
  
  $totalTime = ($modx->getMicroTime() - $modx->tstart);
  $queryTime = $modx->queryTime;
  $phpTime = $totalTime - $queryTime;

  $queryTime = sprintf("%2.4f сек.", $queryTime);
  $totalTime = sprintf("%2.4f сек.", $totalTime);
  $phpTime = sprintf("%2.4f сек.", $phpTime);
  $source = $modx->documentGenerated == 1 ? "базы данных" : "кэша";
  $queries = isset ($modx->executedQueries) ? $modx->executedQueries : 0;
  
  if($totalTime >= $alert_time || $queries >= $alert_query){
    $page_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $info_mess = '<a href="'.$page_url.'" target="_blank">'.$page_url.'</a>: ';
    $info_mess .= "<b>MySQL</b>: $queryTime, $queries запрос(ов), <b>PHP</b>: $phpTime, <b>общее время</b>: $totalTime, документ получен из <b>$source</b>";
    $modx->logEvent(45,1,$info_mess,"devStat: $queries / $totalTime");
  }

}


//?>