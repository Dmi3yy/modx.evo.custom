<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
// For overriding documents, create a new template variabe (TV) named seoOverride with the following options:
//    Input Type: DropDown List Menu
//    Input Option Values: Disabled==-1||Base Name==0||Append Extension==1||Folder==2
//    Default Value: -1

//  # Include the following in your .htaccess file
//  # Replace "example.com" &  "example\.com" with your domain info
//  RewriteCond %{HTTP_HOST} .
//  RewriteCond %{HTTP_HOST} !^www\.example\.com [NC]
//  RewriteRule (.*) http://www.example.com/$1 [R=301,L] 

// Begin plugin code
$e = &$modx->event;

if ($e->name == 'OnWebPageInit') 
{
   $documentIdentifier = $modx->documentIdentifier;

   if ($documentIdentifier)  // Check for 404 error
   {
      $myProtocol = ($_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
      $s = $_SERVER['REQUEST_URI'];
      $parts = explode("?", $s);  

      $alias = $modx->aliasListing[$documentIdentifier]['alias'];
      if ($makeFolders)
      {
         if ($emptyFolders)
         {
            $result = $modx->db->select('isfolder', $modx->getFullTableName('site_content'), 'id = ' . $documentIdentifier);
            $isfolder = $modx->db->getValue($result);
         }
         else
         {
            $isfolder = (count($modx->getChildIds($documentIdentifier, 1)) > 0) ? 1 : 0;
         }
      }

      if ($override && $overrideOption = $modx->getTemplateVarOutput($overrideTV, $documentIdentifier))
      {
         switch ($overrideOption[$overrideTV])
         {
            case 0:
               $isoverride = 1;
               break;
            case 1:
               $isfolder = 0;
               break;
            case 2:
               $makeFolders = 1;
               $isfolder = 1;
         }
      }

      if ($isoverride)
      {
         $strictURL = preg_replace('/[^\/]+$/', $alias, $modx->makeUrl($documentIdentifier));
      }
      elseif ($isfolder && $makeFolders)
      {
         $strictURL = preg_replace('/[^\/]+$/', $alias, $modx->makeUrl($documentIdentifier)) . "/";
      }
      else
      {
         $strictURL = $modx->makeUrl($documentIdentifier);
      }

      $myDomain = $myProtocol . "://" . $_SERVER['HTTP_HOST'];
      $newURL = $myDomain . $strictURL;
      $requestedURL = $myDomain . $parts[0];

      if ($documentIdentifier == $modx->config['site_start'])
      {
         if ($requestedURL != $modx->config['site_url'])
         {
            // Force redirect of site start
            header("HTTP/1.1 301 Moved Permanently");
            $qstring = preg_replace("#(^|&)(q|id)=[^&]+#", '', $parts[1]);  // Strip conflicting id/q from query string
            if ($qstring) header('Location: ' . $modx->config['site_url'] . '?' . $qstring);
            else header('Location: ' . $modx->config['site_url']);
            exit(0);
         }
      }
      elseif ($parts[0] != $strictURL)
      {
         // Force page redirect
         header("HTTP/1.1 301 Moved Permanently");
         $qstring = preg_replace("#(^|&)(q|id)=[^&]+#", '', $parts[1]);  // Strip conflicting id/q from query string
         if ($qstring) header('Location: ' . $strictURL . '?' . $qstring);
         else header('Location: ' . $strictURL);
         exit(0);
      }
   }
}
elseif ($e->name == 'OnWebPagePrerender')
{
   if ($editDocLinks)
   {
      $myDomain = $_SERVER['HTTP_HOST'];
      $furlSuffix = $modx->config['friendly_url_suffix'];
      $baseUrl = $modx->config['base_url'];
      $o = &$modx->documentOutput; // get a reference of the output

      // Reduce site start to base url
      $overrideAlias = $modx->aliasListing[$modx->config['site_start']]['alias'];
      $overridePath = $modx->aliasListing[$modx->config['site_start']]['path'];
      $o = preg_replace("#((href|action)=\"|$myDomain)($baseUrl)?($overridePath/)?$overrideAlias$furlSuffix#", '${1}' . $baseUrl, $o);

      if ($override)
      {
         // Replace manual override links
         $sql = "SELECT tvc.contentid as id, tvc.value as value FROM " . $modx->getFullTableName('site_tmplvars') . " tv ";
         $sql .= "INNER JOIN " . $modx->getFullTableName('site_tmplvar_templates') . " tvtpl ON tvtpl.tmplvarid = tv.id ";
         $sql .= "LEFT JOIN " . $modx->getFullTableName('site_tmplvar_contentvalues') . " tvc ON tvc.tmplvarid = tv.id ";
         $sql .= "LEFT JOIN " . $modx->getFullTableName('site_content') . " sc ON sc.id = tvc.contentid ";
         $sql .= "WHERE sc.published = 1 AND tvtpl.templateid = sc.template AND tv.name = '$overrideTV'";
         $results = $modx->dbQuery($sql);
         while ($row = $modx->fetchRow($results))
         {
            $overrideAlias = $modx->aliasListing[$row['id']]['alias'];
            $overridePath = $modx->aliasListing[$row['id']]['path'];
            switch ($row['value'])
            {
               case 0:
                  $o = preg_replace("#((href|action)=\"($baseUrl)?($overridePath/)?|$myDomain$baseUrl$overridePath/?)$overrideAlias$furlSuffix#", '${1}' . $overrideAlias, $o);
                  break;
               case 2:
                  $o = preg_replace("#((href|action)=\"($baseUrl)?($overridePath/)?|$myDomain$baseUrl$overridePath/?)$overrideAlias$furlSuffix/?#", '${1}' . rtrim($overrideAlias, '/') . '/', $o);
                  break;
            }
         }
      }

      if ($makeFolders)
      {
         if ($emptyFolders)
         {
            // Populate isfolder array
            $isfolder_arr = array();
            $result = $modx->db->select('id', $modx->getFullTableName('site_content'), 'published > 0 AND isfolder > 0');
            while ($row = $modx->db->getRow($result))
               $isfolder_arr[$row['id']] = true;
         }

         // Replace container links
         foreach ($modx->documentListing as $id)
         {
            if ((is_array($isfolder_arr) && isset($isfolder_arr[$id])) || count($modx->getChildIds($id, 1)))
            {
               $overrideAlias = $modx->aliasListing[$id]['alias'];
               $overridePath = $modx->aliasListing[$id]['path'];
               $o = preg_replace("#((href|action)=\"($baseUrl)?($overridePath/)?|$myDomain$baseUrl$overridePath/?)$overrideAlias$furlSuffix/?#", '${1}' . rtrim($overrideAlias, '/') . '/', $o);
            }
         }
      }
   }
}
?>