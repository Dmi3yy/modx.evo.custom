<?php
/**
 *	MODX Document Parser
 *	Function: This class contains the main document parsing functions
 *
 */

class DocumentParser {
    var $db; // db object
    var $event, $Event; // event object
    var $pluginEvent = array();
    var $config= null;
    var $rs;
    var $result;
    var $sql;
    var $table_prefix;
    var $debug;
    var $documentIdentifier;
    var $documentMethod;
    var $documentGenerated;
    var $documentContent;
    var $tstart;
    var $mstart;
    var $minParserPasses;
    var $maxParserPasses;
    var $documentObject;
    var $templateObject;
    var $snippetObjects;
    var $stopOnNotice;
    var $executedQueries;
    var $queryTime;
    var $currentSnippet;
    var $aliases;
    var $entrypage;
    var $documentListing;
    var $dumpSnippets;
    var $snipCode;
    var $chunkCache;
    var $snippetCache;
    var $contentTypes;
    var $dumpSQL;
    var $queryCode;
    var $virtualDir;
    var $placeholders;
    var $sjscripts = array();
    var $jscripts = array();
    var $loadedjscripts = array();
    var $documentMap;
    var $forwards= 3;
    var $referenceListing;
    var $documentMap_cache;
    var $safeMode;
    var $qs_hash;

    // constructor
	function DocumentParser()
	{
		if(!isset($_REQUEST['id']))
		{
			$_REQUEST['q'] = substr($_SERVER['REQUEST_URI'],strlen(MODX_BASE_URL));
			if(strpos($_REQUEST['q'],'?')) $_REQUEST['q'] = substr($_REQUEST['q'],0,strpos($_REQUEST['q'],'?'));
		}
		if($_REQUEST['q']=='index.php') $_REQUEST['q'] = '';
		
		$this->loadExtension('DBAPI') or die('Could not load DBAPI class.'); // load DBAPI class
		// events
		$this->event= new SystemEvent();
		$this->Event= & $this->event; //alias for backward compatibility
		$this->minParserPasses = 1; // min number of parser recursive loops or passes
		$this->maxParserPasses = 10; // max number of parser recursive loops or passes
		$this->dumpSQL = false;
		$this->dumpSnippets = false; // feed the parser the execution start time
		$this->stopOnNotice = false;
		$this->safeMode     = false;
		// set track_errors ini variable
		@ ini_set('track_errors', '1'); // enable error tracking in $php_errormsg
		// Don't show PHP errors to the public
		if($this->checkSession()===false) @ini_set('display_errors','0');
	}

	// loads an extension from the extenders folder
	function loadExtension($extname)
	{
		global $database_type;
		
		switch ($extname)
		{
			// Database API
			case 'DBAPI' :
				if(include_once(MODX_BASE_PATH . "manager/includes/extenders/dbapi.{$database_type}.class.inc.php"))
				{
					$this->db= new DBAPI;
					$this->dbConfig= & $this->db->config; // alias for backward compatibility
					return true;
				}
				else return false;
				break;
			// Manager API
			case 'ManagerAPI' :
				if(include_once(MODX_BASE_PATH . 'manager/includes/extenders/manager.api.class.inc.php'))
				{
					$this->manager= new ManagerAPI;
					return true;
				}
				else return false;
				break;
			default :
				return false;
		}
	}
	
	function executeParser()
	{
		ob_start();
		//error_reporting(0);
		if (version_compare(phpversion(), '5.0.0', '>='))
		{
			set_error_handler(array(& $this,'phpError'), E_ALL);
		}
		else
		{
			set_error_handler(array(& $this,'phpError'));
		}
		
		if(!empty($_SERVER['QUERY_STRING']))
		{
			if($_GET['id']) unset($_GET['id']);
			if(0 < count($_GET)) $this->qs_hash = '_' . urlencode(join('&',$_GET));
			else $this->qs_hash = '';
		}
		
		// get the settings
		$this->db->connect();
		$this->getSettings();
		
		$this->documentOutput = $this->get_static_pages();
		if(!empty($this->documentOutput))
		{
			$this->documentOutput = $this->parseDocumentSource($this->documentOutput);
			$this->invokeEvent('OnWebPagePrerender');
			echo $this->documentOutput;
			$this->invokeEvent('OnWebPageComplete');
			exit;
		}
		
		// IIS friendly url fix
		if ($this->config['friendly_urls'] == 1 && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false)
		{
			$url= $_SERVER['QUERY_STRING'];
			$err= substr($url, 0, 3);
			if ($err == '404' || $err == '405')
			{
				$k= array_keys($_GET);
				unset ($_GET[$k[0]]);
				unset ($_REQUEST[$k[0]]); // remove 404,405 entry
				$_SERVER['QUERY_STRING']= $qp['query'];
				$qp= parse_url(str_replace($this->config['site_url'], '', substr($url, 4)));
				if (!empty ($qp['query']))
				{
					parse_str($qp['query'], $qv);
					foreach ($qv as $n => $v)
					{
						$_REQUEST[$n]= $_GET[$n]= $v;
					}
				}
				$_SERVER['PHP_SELF']= $this->config['base_url'] . $qp['path'];
				$_REQUEST['q']= $_GET['q']= $qp['path'];
			}
		}
		
		// check site settings
		if (!$this->checkSiteStatus())
		{
			header('HTTP/1.0 503 Service Unavailable');
			if (!$this->config['site_unavailable_page'])
			{
				// display offline message
				$this->documentContent= $this->config['site_unavailable_message'];
				$this->outputContent();
				exit; // stop processing here, as the site's offline
			}
			else
			{
				// setup offline page document settings
				$this->documentMethod= 'id';
				$this->documentIdentifier= $this->config['site_unavailable_page'];
			}
		}
		else
		{
			// make sure the cache doesn't need updating
			$this->checkPublishStatus();
			
			// find out which document we need to display
			$this->documentMethod= $this->getDocumentMethod();
			$this->documentIdentifier= $this->getDocumentIdentifier($this->documentMethod);
		}
		
		if ($this->documentMethod == 'none')
		{
			$this->documentMethod= 'id'; // now we know the site_start, change the none method to id
		}
		elseif ($this->documentMethod == 'alias')
		{
			$this->documentIdentifier= $this->cleanDocumentIdentifier($this->documentIdentifier);
		}
		
		if ($this->documentMethod == 'alias')
		{
			// Check use_alias_path and check if $this->virtualDir is set to anything, then parse the path
			if ($this->config['use_alias_path'] == 1)
			{
				$alias= (strlen($this->virtualDir) > 0 ? $this->virtualDir . '/' : '') . $this->documentIdentifier;
				if (isset($this->documentListing[$alias]))
				{
					$this->documentIdentifier= $this->documentListing[$alias];
				}
				else
				{
					$this->sendErrorPage();
				}
			}
			else
			{
				$this->documentIdentifier= $this->documentListing[$this->documentIdentifier];
			}
			$this->documentMethod= 'id';
		}
		
		// invoke OnWebPageInit event
		$this->invokeEvent('OnWebPageInit');
		
		$this->prepareResponse();
	}
	
	function prepareResponse()
	{
		// we now know the method and identifier, let's check the cache
		$this->documentContent= $this->checkCache($this->documentIdentifier);
		if ($this->documentContent != '')
		{
			$this->invokeEvent('OnLoadWebPageCache'); // invoke OnLoadWebPageCache  event
		}
		else
		{
			// get document object
			$this->documentObject= $this->getDocumentObject($this->documentMethod, $this->documentIdentifier);
			
			// validation routines
			if ($this->documentObject['deleted'] == 1)
			{
				$this->sendErrorPage();
			}
			//  && !$this->checkPreview()
			if ($this->documentObject['published'] == 0)
			{
			// Can't view unpublished pages
				if (!$this->hasPermission('view_unpublished'))
				{
					$this->sendErrorPage();
				}
				else
				{
					// Inculde the necessary files to check document permissions
					include_once ($this->config['base_path'] . 'manager/processors/user_documents_permissions.class.php');
					$udperms= new udperms();
					$udperms->user= $this->getLoginUserID();
					$udperms->document= $this->documentIdentifier;
					$udperms->role= $_SESSION['mgrRole'];
					// Doesn't have access to this document
					if (!$udperms->checkPermissions())
					{
						$this->sendErrorPage();
					}
				}
			}
			// check whether it's a reference
			if($this->documentObject['type'] == 'reference')
			{
				if(is_numeric($this->documentObject['content']))
				{
					// if it's a bare document id
					$this->documentObject['content']= $this->makeUrl($this->documentObject['content']);
				}
				elseif(strpos($this->documentObject['content'], '[~') !== false)
				{
					// if it's an internal docid tag, process it
					$this->documentObject['content']= $this->rewriteUrls($this->documentObject['content']);
				}
				$this->sendRedirect($this->documentObject['content'], 0, '', 'HTTP/1.0 301 Moved Permanently');
			}
			// check if we should not hit this document
			if($this->documentObject['donthit'] == 1)
			{
				$this->config['track_visitors']= 0;
			}
			// get the template and start parsing!
			if(!$this->documentObject['template'])
			{
				$this->documentContent= '[*content*]'; // use blank template
			}
			else
			{
				$tbl_site_templates = $this->getFullTableName('site_templates');
				$result= $this->db->select('content',$tbl_site_templates,"id = '{$this->documentObject['template']}'");
				$rowCount= $this->db->getRecordCount($result);
				if($rowCount > 1)
				{
					$this->messageQuit('Incorrect number of templates returned from database', $sql);
				}
				elseif($rowCount == 1)
				{
					$row= $this->db->getRow($result);
					$this->documentContent= $row['content'];
				}
			}
			// invoke OnLoadWebDocument event
			$this->invokeEvent('OnLoadWebDocument');
			
			// Parse document source
			$this->documentContent= $this->parseDocumentSource($this->documentContent);
		}
		register_shutdown_function(array (
		& $this,
		'postProcess'
		)); // tell PHP to call postProcess when it shuts down
		$this->outputContent();
	}
	
	function outputContent($noEvent= false)
	{
		
		$this->documentOutput= $this->documentContent;
		
		if ($this->documentGenerated           == 1
		 && $this->documentObject['cacheable'] == 1
		 && $this->documentObject['type']      == 'document'
		 && $this->documentObject['published'] == 1)
		{
			if (!empty($this->sjscripts)) $this->documentObject['__MODxSJScripts__'] = $this->sjscripts;
			if (!empty($this->jscripts))  $this->documentObject['__MODxJScripts__'] = $this->jscripts;
		}
		
		// check for non-cached snippet output
		if (strpos($this->documentOutput, '[!') !== false)
		{
			// Parse document source
			$passes = $this->minParserPasses;
			
			for ($i= 0; $i < $passes; $i++)
			{
				if($i == ($passes -1)) $st= md5($this->documentOutput);
				
				$this->documentOutput = str_replace(array('[!','!]'), array('[[',']]'), $this->documentOutput);
				$this->documentOutput = $this->parseDocumentSource($this->documentOutput);
				
				if($i == ($passes -1) && $i < ($this->maxParserPasses - 1))
				{
					$et = md5($this->documentOutput);
					if($st != $et) $passes++;
				}
			}
		}
		
		// Moved from prepareResponse() by sirlancelot
		// Insert Startup jscripts & CSS scripts into template - template must have a <head> tag
		if ($js= $this->getRegisteredClientStartupScripts())
		{
			// change to just before closing </head>
			// $this->documentContent = preg_replace("/(<head[^>]*>)/i", "\\1\n".$js, $this->documentContent);
			$this->documentOutput= preg_replace("/(<\/head>)/i", $js . "\n\\1", $this->documentOutput);
		}
		
		// Insert jscripts & html block into template - template must have a </body> tag
		if ($js= $this->getRegisteredClientScripts())
		{
			$this->documentOutput= preg_replace("/(<\/body>)/i", $js . "\n\\1", $this->documentOutput);
		}
		// End fix by sirlancelot
		
		// remove all unused placeholders
		if (strpos($this->documentOutput, '[+') > -1)
		{
			$matches= array ();
			preg_match_all('~\[\+(.*?)\+\]~', $this->documentOutput, $matches);
			if ($matches[0])
			$this->documentOutput= str_replace($matches[0], '', $this->documentOutput);
		}
		
		if(strpos($this->documentOutput,'[~')!==false) $this->documentOutput = $this->rewriteUrls($this->documentOutput);
		
		// send out content-type and content-disposition headers
		if (IN_PARSER_MODE == 'true')
		{
			$type = $this->documentObject['contentType'];
			if(empty($type)) $type = 'text/html';
			
			header("Content-Type: {$type}; charset={$this->config['modx_charset']}");
			//            if (($this->documentIdentifier == $this->config['error_page']) || $redirect_error)
			//                header('HTTP/1.0 404 Not Found');
			if ($this->documentObject['content_dispo'] == 1)
			{
				if ($this->documentObject['alias'])
				{
					$name= urldecode($this->documentObject['alias']);
				}
				else
				{
					// strip title of special characters
					$name= $this->documentObject['pagetitle'];
					$name= strip_tags($name);
					$name= preg_replace('/&.+?;/', '', $name); // kill entities
					$name= preg_replace('/\s+/', '-', $name);
					$name= preg_replace('|-+|', '-', $name);
					$name= trim($name, '-');
				}
				$header= 'Content-Disposition: attachment; filename=' . $name;
				header($header);
			}
		}
		
		$totalTime= ($this->getMicroTime() - $this->tstart);
		$queryTime= $this->queryTime;
		$phpTime= $totalTime - $queryTime;
		
		$queryTime= sprintf("%2.4f s", $queryTime);
		$totalTime= sprintf("%2.4f s", $totalTime);
		$phpTime= sprintf("%2.4f s", $phpTime);
		$source= $this->documentGenerated == 1 ? 'database' : 'cache';
		$queries= isset ($this->executedQueries) ? $this->executedQueries : 0;
		if(function_exists('memory_get_peak_usage'))
		{
			$total_mem = $this->nicesize(memory_get_peak_usage() - $this->mstart);
		}
		else
		{
			$total_mem = $this->nicesize(memory_get_usage() - $this->mstart);
		}
		
		$out =& $this->documentOutput;
		if ($this->dumpSQL)
		{
			$out .= $this->queryCode;
		}
		if ($this->dumpSnippets)
		{
			$out .= $this->snipCode;
		}
		$out= str_replace('[^q^]', $queries, $out);
		$out= str_replace('[^qt^]', $queryTime, $out);
		$out= str_replace('[^p^]', $phpTime, $out);
		$out= str_replace('[^t^]', $totalTime, $out);
		$out= str_replace('[^s^]', $source, $out);
		$out= str_replace('[^m^]', $total_mem, $out);
		//$this->documentOutput= $out;
		
		// invoke OnWebPagePrerender event
		if (!$noEvent)
		{
			$this->invokeEvent('OnWebPagePrerender');
		}
		echo $this->documentOutput;
		ob_end_flush();
	}
	
	function postProcess()
	{
		// if the current document was generated, cache it!
		if ($this->documentGenerated           == 1
		 && $this->documentObject['cacheable'] == 1
		 && $this->documentObject['type']      == 'document'
		 && $this->documentObject['published'] == 1)
		{
			$tbl_document_groups = $this->getFullTableName('document_groups');
			$docid = $this->documentIdentifier;
			
			// invoke OnBeforeSaveWebPageCache event
			$this->invokeEvent('OnBeforeSaveWebPageCache');
			// get and store document groups inside document object. Document groups will be used to check security on cache pages
			$dsq = $this->db->select('document_group', $tbl_document_groups, "document='{$docid}'");
			$docGroups= $this->db->getColumn('document_group', $dsq);
			
			// Attach Document Groups and Scripts
			if (is_array($docGroups))
			{
				$this->documentObject['__MODxDocGroups__'] = implode(',', $docGroups);
			}
			
			$cacheContent  = "<?php die('Unauthorized access.'); ?>\n";
			$cacheContent .= serialize($this->documentObject);
			$cacheContent .= "<!--__MODxCacheSpliter__-->{$this->documentContent}";
			$base_path = $this->config['base_path'];
			$page_cache_path = "{$base_path}assets/cache/docid_{$docid}{$this->qs_hash}.pageCache.php";
			file_put_contents($page_cache_path, $cacheContent);
		}
		
		// invoke OnLogPageView event
		if ($this->config['track_visitors'] == 1)
		{
			$this->invokeEvent('OnLogPageHit');
		}
		
		// Useful for example to external page counters/stats packages
		$this->invokeEvent('OnWebPageComplete');
		
		// end post processing
	}
	
	function getMicroTime()
	{
		list ($usec, $sec)= explode(' ', microtime());
		return ((float) $usec + (float) $sec);
	}
	
	function sendRedirect($url, $count_attempts= 0, $type= '', $responseCode= '')
	{
		if (empty($url))
		{
			return false;
		}
		else
		{
			if ($count_attempts == 1)
			{
				// append the redirect count string to the url
				$currentNumberOfRedirects= isset ($_REQUEST['err']) ? $_REQUEST['err'] : 0;
				if ($currentNumberOfRedirects > 3)
				{
					$this->messageQuit("Redirection attempt failed - please ensure the document you're trying to redirect to exists. <p>Redirection URL: <i>{$url}</i></p>");
				}
				else
				{
					$currentNumberOfRedirects += 1;
					if (strpos($url, '?') > 0)
					{
						$url .= "&err={$currentNumberOfRedirects}";
					}
					else
					{
						$url .= "?err={$currentNumberOfRedirects}";
					}
				}
			}
			if ($type == 'REDIRECT_REFRESH')
			{
				$header= 'Refresh: 0;URL=' . $url;
			}
			elseif ($type == 'REDIRECT_META')
			{
				$header= '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $url . '" />';
				echo $header;
				exit;
			}
			elseif($type == 'REDIRECT_HEADER' || empty ($type))
			{
				// check if url has /$base_url
				global $base_url, $site_url;
				if (substr($url, 0, strlen($base_url)) == $base_url)
				{
					// append $site_url to make it work with Location:
					$url= $site_url . substr($url, strlen($base_url));
				}
				if (strpos($url, "\n") === false)
				{
					$header= 'Location: ' . $url;
				}
				else
				{
					$this->messageQuit('No newline allowed in redirect url.');
				}
			}
			if ($responseCode && (strpos($responseCode, '30') !== false))
			{
				header($responseCode);
			}
			header($header);
			exit();
		}
	}
	
	function sendForward($id, $responseCode= '')
	{
		if ($this->forwards > 0)
		{
			$this->forwards= $this->forwards - 1;
			$this->documentIdentifier= $id;
			$this->documentMethod= 'id';
			$this->documentObject= $this->getDocumentObject('id', $id);
			if ($responseCode)
			{
				header($responseCode);
			}
			$this->prepareResponse();
		}
		else
		{
			header('HTTP/1.0 500 Internal Server Error');
			die('<h1>ERROR: Too many forward attempts!</h1><p>The request could not be completed due to too many unsuccessful forward attempts.</p>');
		}
		exit();
	}
	
	function sendErrorPage()
	{
		// invoke OnPageNotFound event
		$this->invokeEvent('OnPageNotFound');
		
		if($this->config['error_page']) $dist = $this->config['error_page'];
		else                            $dist = $this->config['site_start'];
		
		$this->sendForward($dist, 'HTTP/1.0 404 Not Found');
	}
	
	function sendUnauthorizedPage()
	{
		// invoke OnPageUnauthorized event
		$_REQUEST['refurl'] = $this->documentIdentifier;
		$this->invokeEvent('OnPageUnauthorized');
		
		if($this->config['unauthorized_page']) $dist = $this->config['unauthorized_page'];
		elseif($this->config['error_page'])    $dist = $this->config['error_page'];
		else                                   $dist = $this->config['site_start'];
		
		$this->sendForward($dist , 'HTTP/1.1 401 Unauthorized');
	}

	function get_static_pages()
	{
		$filepath = $_SERVER['REQUEST_URI'];
		if(strpos($filepath,'?')!==false) $filepath = substr($filepath,0,strpos($filepath,'?'));
		$filepath = substr($filepath,strlen($this->config['base_url']));
		if(substr($filepath,-1)==='/' || empty($filepath)) $filepath .= 'index.html';
		$filepath = $this->config['base_path'] . 'assets/static_pages/' . $filepath;
		if(file_exists($filepath)!==false)
		{
			$ext = strtolower(substr($filepath,strrpos($filepath,'.')));
			switch($ext)
			{
				case '.html':
				case '.htm':
					$mime_type = 'text/html'; break;
				case '.css':
					$mime_type = 'text/css'; break;
				case '.js':
					$mime_type = 'text/javascript'; break;
				case '.txt':
					$mime_type = 'text/plain'; break;
				case '.jpg':
				case '.jpeg':
				case '.png':
				case '.gif':
					if($ext==='.ico') $mime_type = 'image/x-icon';
					else
					{
						$info = getImageSize($filepath);
						$mime_type = $info['mime'];
					}
					header("Content-type: {$mime_type}");
					readfile($filepath);
					exit;
				default:
					exit;
			}
			header("Content-type: {$mime_type}");
			$src = file_get_contents($filepath);
		}
		else $src = false;
		
		return $src;
	}
	
	function getSettings()
	{
		if(!isset($this->config) || !is_array($this->config) || empty ($this->config))
		{
			if(file_exists(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php'))
			{
				$included= include_once (MODX_BASE_PATH . 'assets/cache/siteCache.idx.php');
			}
			if(!isset($included) || !is_array($this->config) || empty ($this->config))
			{
				include_once MODX_MANAGER_PATH . 'processors/cache_sync.class.processor.php';
				$cache = new synccache();
				$cache->setCachepath(MODX_BASE_PATH . 'assets/cache/');
				$cache->setReport(false);
				$rebuilt = $cache->buildCache($this);
				$included = false;
				if($rebuilt && file_exists(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php'))
				{
					$included= include_once(MODX_BASE_PATH . 'assets/cache/siteCache.idx.php');
				}
				if(!$included)
				{
					$result= $this->db->select('setting_name, setting_value',$this->getFullTableName('system_settings'));
					while ($row= $this->db->getRow($result, 'both'))
					{
						$this->config[$row[0]]= $row[1];
					}
				}
			}
			// added for backwards compatibility - garry FS#104
			$this->config['etomite_charset'] = & $this->config['modx_charset'];
			
			// store base_url and base_path inside config array
			$this->config['base_url']= MODX_BASE_URL;
			$this->config['base_path']= MODX_BASE_PATH;
			if(!isset($this->config['site_url']) || empty($this->config['site_url']))
			{
				$this->config['site_url']= MODX_SITE_URL;
			}
		}
		// load user setting if user is logged in
		$tbl_user_settings     = $this->getFullTableName('user_settings');
		$usrSettings= array();
		if ($id= $this->getLoginUserID())
		{
			$usrType= $this->getLoginUserType();
			if (isset ($usrType) && $usrType == 'manager')
			{
				$usrType= 'mgr';
			}
			
			if ($usrType == 'mgr' && $this->isBackend())
			{
				// invoke the OnBeforeManagerPageInit event, only if in backend
				$this->invokeEvent('OnBeforeManagerPageInit');
			}
			if (isset ($_SESSION["{$usrType}UsrConfigSet"]) && 0 < count($_SESSION["{$usrType}UsrConfigSet"]))
			{
				$usrSettings= & $_SESSION["{$usrType}UsrConfigSet"];
			}
			else
			{
				if ($usrType == 'web')
				{
					$from  = $this->getFullTableName('web_user_settings');
					$where ="webuser='{$id}'";
				}
				else
				{
					$from  = $tbl_user_settings;
					$where = "user='{$id}'";
				}
				$result= $this->db->select('setting_name, setting_value',$from,$where);
				while ($row= $this->db->getRow($result, 'both'))
				{
					$usrSettings[$row[0]]= $row[1];
				}
				if (isset ($usrType))
				{
					$_SESSION[$usrType . 'UsrConfigSet']= $usrSettings; // store user settings in session
				}
			}
		}
		if($this->isFrontend() && $mgrid= $this->getLoginUserID('mgr'))
		{
			$musrSettings= array ();
			if(isset ($_SESSION['mgrUsrConfigSet']))
			{
				$musrSettings= & $_SESSION['mgrUsrConfigSet'];
			}
			else
			{
				if($result= $this->db->select('setting_name, setting_value',$tbl_user_settings,"user='{$mgrid}'"))
				{
					while ($row= $this->db->getRow($result, 'both'))
					{
						$usrSettings[$row[0]]= $row[1];
					}
					$_SESSION['mgrUsrConfigSet']= $musrSettings; // store user settings in session
				}
			}
			if(!empty ($musrSettings))
			{
				$usrSettings= array_merge($musrSettings, $usrSettings);
			}
		}
		$this->config= array_merge($this->config, $usrSettings);
	}
	
	function getDocumentMethod()
	{
		// function to test the query and find the retrieval method
		if(isset($_REQUEST['q']))        return 'alias';
		elseif(isset ($_REQUEST['id']))  return 'id';
		else                             return 'none';
	}
	
	function getDocumentIdentifier($method)
	{
		// function to test the query and find the retrieval method
		$docIdentifier= $this->config['site_start'];
		switch ($method)
		{
			case 'alias' :
				$docIdentifier= $this->db->escape($_REQUEST['q']);
				break;
			case 'id' :
				if (!is_numeric($_REQUEST['id']))
				{
					$this->sendErrorPage();
				}
				else
				{
					$docIdentifier= intval($_REQUEST['id']);
				}
				break;
		}
		return $docIdentifier;
	}
	
	// check for manager login session
	function checkSession()
	{
		if(isset($_SESSION['mgrValidated']) && !empty($_SESSION['mgrValidated']))
		{
			return true;
		}
		else return false;
	}

	function checkPreview()
	{
		if ($this->checkSession() == true)
		{
			if (isset ($_REQUEST['z']) && $_REQUEST['z'] == 'manprev')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	// check if site is offline
	function checkSiteStatus()
	{
		$siteStatus= $this->config['site_status'];
		if($siteStatus == 1)
		{
			return true; // site online
		}
		elseif($siteStatus == 0 && $this->checkSession())
		{
			return true; // site offline but launched via the manager
		}
		else
		{
			return false; // site is offline
		}
	}
	
	function cleanDocumentIdentifier($qOrig)
	{
		if(empty($qOrig)) $qOrig = $this->config['site_start'];
		$q = trim($qOrig,'/');
		/* Save path if any */
		/* FS#476 and FS#308: only return virtualDir if friendly paths are enabled */
		if ($this->config['use_alias_path'] == 1)
		{
			$this->virtualDir = dirname($q);
			$this->virtualDir = ($this->virtualDir == '.') ? '' : $this->virtualDir;
			$q = end(explode('/', $q));
		}
		else
		{
			$this->virtualDir= '';
		}
		
		$q = preg_replace('@^' . $this->config['friendly_url_prefix'] . '@',  '', $q);
		$q = preg_replace('@'  . $this->config['friendly_url_suffix'] . '$@', '', $q);
		if (is_numeric($q) && !$this->documentListing[$q])
		{ /* we got an ID returned, check to make sure it's not an alias */
			/* FS#476 and FS#308: check that id is valid in terms of virtualDir structure */
			if ($this->config['use_alias_path'] == 1)
			{
				if (
					(
						($this->virtualDir != '' && !$this->documentListing[$this->virtualDir . '/' . $q])
						||
						($this->virtualDir == '' && !$this->documentListing[$q])
					)
					&&
					(
						($this->virtualDir != '' && in_array($q, $this->getChildIds($this->documentListing[$this->virtualDir], 1)))
						||
						($this->virtualDir == '' && in_array($q, $this->getChildIds(0, 1)))
					))
				{
					$this->documentMethod = 'id';
					return $q;
				}
				else
				{ /* not a valid id in terms of virtualDir, treat as alias */
					$this->documentMethod = 'alias';
					return $q;
				}
			}
			else
			{
				$this->documentMethod = 'id';
				return $q;
			}
		}
		else
		{ /* we didn't get an ID back, so instead we assume it's an alias */
			if ($this->config['friendly_alias_urls'] != 1)
			{
				$q= $qOrig;
			}
			$this->documentMethod= 'alias';
			return $q;
			}
		}

	function checkCache($id)
	{
		if(isset($this->config['cache_enabled']) && $this->config['cache_enabled'] == 0) return ''; // jp-edition only
		$cacheFile = "{$this->config['base_path']}assets/cache/docid_{$id}{$this->qs_hash}.pageCache.php";
		if(file_exists($cacheFile))
		{
			$flContent = file_get_contents($cacheFile, false);
		}
		if(!file_exists($cacheFile) || empty($flContent))
		{
			$this->documentGenerated = 1;
			return '';
		}
		
		$this->documentGenerated = 0;
		
		$flContent = substr($flContent, 37); // remove php header
		$a = explode('<!--__MODxCacheSpliter__-->', $flContent, 2);
		if(count($a) == 1)
		{
			return $a[0]; // return only document content
		}
		
		$docObj = unserialize(trim($a[0])); // rebuild document object
		// add so - check page security(admin(mgrRole=1) is pass)
		if(!(isset($_SESSION['mgrRole']) && $_SESSION['mgrRole'] == 1) 
		    && $docObj['privateweb'] && isset ($docObj['__MODxDocGroups__']))
		{
			$pass = false;
			$usrGrps = $this->getUserDocGroups();
			$docGrps = explode(',', $docObj['__MODxDocGroups__']);
			// check is user has access to doc groups
			if(is_array($usrGrps))
			{
				foreach ($usrGrps as $k => $v)
				{
					if(in_array($v, $docGrps))
					{
						$pass = true;
						break;
					}
				}
			}
			// diplay error pages if user has no access to cached doc
			if(!$pass)
			{
				if($this->config['unauthorized_page'])
				{
					// check if file is not public
					$tbl_document_groups = $this->getFullTableName('document_groups');
					$secrs = $this->db->select('id', $tbl_document_groups, "document='{$id}'",'',1);
					if($secrs)
					{
						$seclimit = $this->db->getRecordCount($secrs);
					}
				}
				if($seclimit > 0)
				{
					// match found but not publicly accessible, send the visitor to the unauthorized_page
					$this->sendUnauthorizedPage();
				}
				else
				{
					// no match found, send the visitor to the error_page
					$this->sendErrorPage();
				}
			}
			// Grab the Scripts
			if(isset($docObj['__MODxSJScripts__'])) $this->sjscripts = $docObj['__MODxSJScripts__'];
			if(isset($docObj['__MODxJScripts__']))  $this->jscripts  = $docObj['__MODxJScripts__'];
			
			// Remove intermediate variables
			unset($docObj['__MODxDocGroups__'], $docObj['__MODxSJScripts__'], $docObj['__MODxJScripts__']);
		}
		$this->documentObject = $docObj;
		return $a[1]; // return document content
	}
	
	function checkPublishStatus()
	{
		$tbl_site_content      = $this->getFullTableName('site_content');
		$tbl_site_htmlsnippets = $this->getFullTableName('site_htmlsnippets');
		$cacheRefreshTime = 0;
		$cache_path= "{$this->config['base_path']}assets/cache/sitePublishing.idx.php";
		include_once($cache_path);
		$timeNow= time() + $this->config['server_offset_time'];
		
		if ($timeNow < $cacheRefreshTime || $cacheRefreshTime == 0) return;
		
		// now, check for documents that need publishing
		$fields = "published='1', publishedon=pub_date";
		$where = "pub_date <= {$timeNow} AND pub_date!=0 AND published=0";
		$rs = $this->db->update($fields,$tbl_site_content,$where);
		
		// now, check for documents that need un-publishing
		$fields = "published='0', publishedon='0'";
		$where = "unpub_date <= {$timeNow} AND unpub_date!=0 AND published=1";
		$rs = $this->db->update($fields,$tbl_site_content,$where);
	
		// now, check for chunks that need publishing
		$fields = "published='1'";
		$where = "pub_date <= {$timeNow} AND pub_date!=0 AND published=0";
		$rs = $this->db->update($fields,$tbl_site_htmlsnippets,$where);
		
		// now, check for chunks that need un-publishing
		$fields = "published='0'";
		$where = "unpub_date <= {$timeNow} AND unpub_date!=0 AND published=1";
		$rs = $this->db->update($fields,$tbl_site_htmlsnippets,$where);
	
		unset($this->chunkCache);
	
		// clear the cache
		$this->clearCache();
		
		// update publish time file
		$timesArr= array ();
		$rs = $this->db->select('MIN(pub_date) AS minpub',$tbl_site_content,"{$timeNow} < pub_date");
		$minpub= $this->db->getValue($rs);
		if ($minpub != NULL)
		{
			$timesArr[]= $minpub;
		}
		
		$rs = $this->db->select('MIN(unpub_date) AS minunpub',$tbl_site_content,"{$timeNow} < unpub_date");
		$minunpub= $this->db->getValue($rs);
		if ($minunpub != NULL)
		{
			$timesArr[]= $minunpub;
		}
		
		$rs = $this->db->select('MIN(pub_date) AS minpub',$tbl_site_htmlsnippets,"{$timeNow} < pub_date");
		$minpub= $this->db->getValue($rs);
		if ($minpub != NULL)
		{
			$timesArr[]= $minpub;
		}
		
		$rs = $this->db->select('MIN(unpub_date) AS minunpub',$tbl_site_htmlsnippets,"{$timeNow} < unpub_date");
		$minunpub= $this->db->getValue($rs);
		if ($minunpub != NULL)
		{
			$timesArr[]= $minunpub;
		}
		
		if (count($timesArr) > 0) $nextevent = min($timesArr);
		else                      $nextevent = 0;
		
		$content = '<?php $cacheRefreshTime=' . $nextevent . ';';
		file_put_contents($cache_path, $content);
	}
	
	// mod by Raymond
	function mergeDocumentContent($template)
	{
		$replace= array ();
		preg_match_all('~\[\*(.*?)\*\]~', $template, $matches);
		$variableCount= count($matches[1]);
		$basepath= $this->config['base_path'] . 'manager/includes/';
		include_once("{$basepath}tmplvars.format.inc.php");
		include_once("{$basepath}tmplvars.commands.inc.php");
		for ($i= 0; $i < $variableCount; $i++)
		{
			$key= $matches[1][$i];
			$key= substr($key, 0, 1) == '#' ? substr($key, 1) : $key; // remove # for QuickEdit format
			$value= $this->documentObject[$key];
			if (is_array($value))
			{
				$value= getTVDisplayFormat($value[0], $value[1], $value[2], $value[3], $value[4]);
			}
			$replace[$i]= $value;
		}
		$template= str_replace($matches[0], $replace, $template);
		return $template;
	}
		
	function mergeSettingsContent($template)
	{
		$replace= array ();
		$matches= array ();
		if(preg_match_all('~\[\(([a-z\_]*?)\)\]~', $template, $matches))
		{
			$total= count($matches[1]);
			for($i= 0; $i < $total; $i++)
			{
				if(isset($this->config[$matches[1][$i]]))
				{
					$replace[$i]= $this->config[$matches[1][$i]];
				}
			}
			
			$template= str_replace($matches[0], $replace, $template);
		}
		return $template;
	}
	
	function mergeChunkContent($content)
	{
		$replace= array ();
		$matches= array ();
		if (preg_match_all('~{{(.*?)}}~', $content, $matches))
		{
			$total= count($matches[1]);
			for ($i= 0; $i < $total; $i++)
			{
				$name = $matches[1][$i];
				if (isset ($this->chunkCache[$name]))
				{
					$replace[$i]= $this->chunkCache[$name];
				}
				else
				{
					$escaped_name = $this->db->escape($name);
					$where = "`name`='{$escaped_name}' AND `published`='1'";
					$result= $this->db->select('snippet',$this->getFullTableName('site_htmlsnippets'),$where);
					$limit= $this->db->getRecordCount($result);
					if ($limit < 1)
					{
						$this->chunkCache[$name]= '';
						$replace[$i]= '';
					}
					else
					{
						$row= $this->db->getRow($result);
						$this->chunkCache[$name]= $row['snippet'];
						$replace[$i]= $row['snippet'];
					}
				}
			}
			$content= str_replace($matches[0], $replace, $content);
		}
		return $content;
	}
	
	// Added by Raymond
	function mergePlaceholderContent($content)
	{
		$replace= array ();
		$matches= array ();
		if(preg_match_all('~\[\+(.*?)\+\]~', $content, $matches))
		{
			$cnt= count($matches[1]);
			for ($i= 0; $i < $cnt; $i++)
			{
				$v= '';
				$key= $matches[1][$i];
				if (is_array($this->placeholders) && isset($this->placeholders[$key]))
				{
					$v= $this->placeholders[$key];
				}
				if ($v === '')
				{
					unset ($matches[0][$i]); // here we'll leave empty placeholders for last.
				}
				else
				{
					$replace[$i]= $v;
				}
			}
			$content= str_replace($matches[0], $replace, $content);
		}
		return $content;
	}

    // evalPlugin
	function evalPlugin($pluginCode, $params)
	{
		$etomite= $modx= & $this;
		$modx->event->params = $params; // store params inside event object
		if (is_array($params))
		{
			extract($params, EXTR_SKIP);
		}
		ob_start();
		$result = eval($pluginCode);
		$msg= ob_get_contents();
		ob_end_clean();
		if ($msg && isset ($php_errormsg))
		{
			if (!strpos($php_errormsg, 'Deprecated'))
			{   // ignore php5 strict errors
				// log error
				$request_uri = getenv('REQUEST_URI');
				$request_uri = 'REQUEST_URI = ' . htmlspecialchars($request_uri, ENT_QUOTES) . '<br />';
				if(isset($this->documentIdentifier))
				{
					$docid = "ID = {$this->documentIdentifier}<br />";
				}
//				$bt = $this->get_backtrace(debug_backtrace()) . '<br />';
				$log = "<b>{$php_errormsg}</b><br />{$msg}<br />{$request_uri}{$docid}";
				$plugin = $this->event->activePlugin . ' - Plugin';
				$this->logEvent(1, 3, $log, $plugin);
				if ($this->isBackend())
				{
					$this->event->alert("An error occurred while loading. Please see the event log for more information.<p>{$msg}</p>");
				}
			}
		}
		else
		{
			echo $msg . $result;
		}
		unset ($modx->event->params);
	}
	
	function evalSnippet($snippet, $params)
	{
		$etomite= $modx= & $this;
		if(isset($params) && is_array($params))
		{
			foreach($params as $k=>$v)
			{
				if($v==='false')    $params[$k] = false;
				elseif($v==='true') $params[$k] = true;
			}
		}
		$modx->event->params = $params; // store params inside event object
		if (is_array($params))
		{
			extract($params, EXTR_SKIP);
		}
		ob_start();
		$result= eval($snippet);
		$msg= ob_get_contents();
		ob_end_clean();
		if ($msg && isset ($php_errormsg))
		{
			if (strpos(strtolower($php_errormsg), 'deprecated')===false)
			{
				// ignore php5 strict errors
				// log error
				$request_uri = getenv('REQUEST_URI');
				$request_uri = 'REQUEST_URI = ' . htmlspecialchars($request_uri, ENT_QUOTES) . '<br />';
				$docid = "ID = {$this->documentIdentifier}<br />";
//				$bt = $this->get_backtrace(debug_backtrace()) . '<br />';
				$log = "<b>{$php_errormsg}</b><br />{$msg}<br />{$request_uri}{$docid}";
				$snip = $this->currentSnippet . ' - Snippet<br />';
				$this->logEvent(1, 3, $log,$snip);
				if ($this->isBackend())
				{
					$this->event->alert("An error occurred while loading. Please see the event log for more information<p>{$msg}</p>");
				}
			}
		}
		unset ($modx->event->params);
		return $msg . $result;
	}
	
	function get_backtrace($backtrace)
	{
		$str  = '<table>';
		$str .= '<tr align="center"><td>#</td><td>call</td><td>path</td></tr>';
		foreach ($backtrace as $key => $val)
		{
			$key++;
			$path = str_replace('\\','/',$val['file']);
			if(strpos($path,MODX_BASE_PATH)===0) $path = substr($path,strlen(MODX_BASE_PATH));
			$str .= "<tr><td>{$key}</td>";
			$str .= "<td>{$val['function']}()</td>";
			$str .= "<td>{$path} on line {$val['line']}</td></tr>";
		}
		$str .= '</table>';
		return $str;
	}

	function evalSnippets($documentSource)
	{
		$etomite= & $this;
		
		$stack = $documentSource;
		unset($documentSource);
		
		$passes = $this->minParserPasses;
		
		for($i= 0; $i < $passes; $i++)
		{
			if($i == ($passes -1)) $bt = md5($stack);
			$pieces = array();
			$pieces = explode('[[', $stack);
			$stack = '';
			$loop_count = 0;
			foreach($pieces as $piece)
			{
				if($loop_count < 1)                 $result = $piece;
				elseif(strpos($piece,']]')===false) $result = '[[' . $piece;
				else                                $result = $this->_get_snip_result($piece);
				
				$stack .= $result;
				$loop_count++; // End of foreach loop
			}
			if($i == ($passes -1) && $i < ($this->maxParserPasses - 1))
			{
				if($bt != md5($stack)) $passes++;
			}
		}
		return $stack;
	}
	
	function _get_snip_result($piece)
	{
		$snip_call        = $this->_split_snip_call($piece);
		$snip_name        = $snip_call['name'];
		$except_snip_call = $snip_call['except_snip_call'];
		
		$snippetObject = $this->_get_snip_properties($snip_call);
		
		$params   = array ();
		$this->currentSnippet = $snippetObject['name'];
		
		if(isset($snippetObject['properties'])) $params = $this->parseProperties($snippetObject['properties']);
		else                                    $params = '';
		// current params
		if(!empty($snip_call['params']))
		{
			$snip_call['params'] = ltrim($snip_call['params'], '?');
			
			$i = 0;
			$limit = 50;
			$params_stack = $snip_call['params'];
			while(!empty($params_stack) && $i < $limit)
			{
				list($pname,$params_stack) = explode('=',$params_stack,2);
				$params_stack = trim($params_stack);
				$delim = substr($params_stack, 0, 1);
				$temp_params = array();
				switch($delim)
				{
					case '`':
					case '"':
					case "'":
						$params_stack = substr($params_stack,1);
						list($pvalue,$params_stack) = explode($delim,$params_stack,2);
						$params_stack = trim($params_stack);
						if(substr($params_stack, 0, 2)==='//')
						{
							$params_stack = strstr($params_stack, "\n");
						}
						break;
					default:
						if(strpos($params_stack, '&')!==false)
						{
							list($pvalue,$params_stack) = explode('&',$params_stack,2);
						}
						else $pvalue = $params_stack;
						$pvalue = trim($pvalue);
				}
				if($delim !== "'")
				{
					$pvalue = (strpos($pvalue,'[*')!==false) ? $this->mergeDocumentContent($pvalue) : $pvalue;
				}
				
				$pname  = str_replace('&amp;', '', $pname);
				$pname  = trim($pname);
				$pname  = trim($pname,'&');
				$params[$pname] = $pvalue;
				$params_stack = trim($params_stack);
				if($params_stack!=='') $params_stack = '&' . ltrim($params_stack,'&');
				$i++;
			}
			unset($temp_params);
		}
		$executedSnippets = $this->evalSnippet($snippetObject['content'], $params);
		if($this->dumpSnippets == 1)
		{
			$this->snipCode .= '<fieldset><legend><b>' . $snippetObject['name'] . '</b></legend><textarea style="width:60%;height:200px">' . htmlentities($executedSnippets,ENT_NOQUOTES,$this->config['modx_charset']) . '</textarea></fieldset>';
		}
		return $executedSnippets . $except_snip_call;
	}
	
	function _split_snip_call($src)
	{
		list($call,$snip['except_snip_call']) = explode(']]', $src, 2);
		if(strpos($call, '?') !== false && strpos($call, "\n")!==false && strpos($call, '?') < strpos($call, "\n"))
		{
			list($snip['name'],$snip['params']) = explode('?',$call,2);
		}
		elseif(strpos($call, '?') !== false && strpos($call, "\n")!==false && strpos($call, "\n") < strpos($call, '?'))
		{
			list($snip['name'],$snip['params']) = explode("\n",$call,2);
		}
		elseif(strpos($call, '?') !== false)
		{
			list($snip['name'],$snip['params']) = explode('?',$call,2);
		}
		elseif((strpos($call, '&') !== false) && (strpos($call, '=') !== false) && (strpos($call, '?') === false))
		{
			list($snip['name'],$snip['params']) = explode("&",$call,2);
			$snip['params'] = '&' . $snip['params'];
		}
		elseif(strpos($call, "\n") !== false)
		{
			list($snip['name'],$snip['params']) = explode("\n",$call,2);
		}
		else
		{
			$snip['name'] = $call;
			$snip['params'] = '';
		}
		$snip['name'] = trim($snip['name']);
		return $snip;
	}
	
	function _get_snip_properties($snip_call)
	{
		$snip_name  = $snip_call['name'];
		
		if(isset($this->snippetCache[$snip_name]))
		{
			$snippetObject['name']    = $snip_name;
			$snippetObject['content'] = $this->snippetCache[$snip_name];
			if(isset($this->snippetCache[$snip_name . 'Props']))
			{
				$snippetObject['properties'] = $this->snippetCache[$snip_name . 'Props'];
			}
		}
		else
		{
			$tbl_snippets  = $this->getFullTableName('site_snippets');
			$esc_snip_name = $this->db->escape($snip_name);
			// get from db and store a copy inside cache
			$result= $this->db->select('name,snippet,properties',$tbl_snippets,"name='{$esc_snip_name}'");
			$added = false;
			if($this->db->getRecordCount($result) == 1)
			{
				$row = $this->db->getRow($result);
				if($row['name'] == $snip_name)
				{
					$snippetObject['name']       = $row['name'];
					$snippetObject['content']    = $this->snippetCache[$snip_name]           = $row['snippet'];
					$snippetObject['properties'] = $this->snippetCache[$snip_name . 'Props'] = $row['properties'];
					$added = true;
				}
			}
			if($added === false)
			{
				$snippetObject['name']       = $snip_name;
				$snippetObject['content']    = $this->snippetCache[$snip_name] = 'return false;';
				$snippetObject['properties'] = '';
			}
		}
		return $snippetObject;
	}
	
	function makeFriendlyURL($pre, $suff, $path) {
		$elements = explode('/',$path);
		$alias    = array_pop($elements);
		$dir      = implode('/', $elements);
		unset($elements);
		if((strpos($alias, '.') !== false))
		{
			if(isset($this->config['suffix_mode']) && $this->config['suffix_mode']==1) $suff = ''; // jp-edition only
		}
		//container_suffix
		return ($dir !== '' ? $dir . '/' : '') . $pre . $alias . $suff;
	}

	function set_aliases()
	{
		$path_aliases = MODX_BASE_PATH . 'assets/cache/aliases.pageCache.php';
		if(file_exists($path_aliases))
		{
			$src = file_get_contents($path_aliases);
			$this->aliases = unserialize($src);
		}
		else
		{
			$aliases= array ();
			foreach ($this->aliasListing as $doc)
			{
				$aliases[$doc['id']]= (strlen($doc['path']) > 0 ? $doc['path'] . '/' : '') . $doc['alias'];
			}
			file_put_contents($path_aliases,serialize($aliases));
			$this->aliases = $aliases;
		}
		return $this->aliases;
	}

	function rewriteUrls($documentSource)
	{
		// rewrite the urls
		$pieces = preg_split('/(\[~|~\])/',$documentSource);
		$maxidx = sizeof($pieces);
		$documentSource = '';
		if(empty($this->referenceListing))
		{
			$this->referenceListing = array();
			$res = $this->db->select('id,content', $this->getFullTableName('site_content'), "type='reference'");
			$rows = $this->db->makeArray($res);
			foreach($rows as $row)
			{
				extract($row);
				$this->referenceListing[$id] = $content;
			}
		}
		
		if ($this->config['friendly_urls'] == 1)
		{
			if(!isset($this->aliases) || empty($this->aliases))
				$aliases = $this->set_aliases();
			else
				$aliases = $this->aliases;
			
			$use_alias = $this->config['friendly_alias_urls'];
			$prefix    = $this->config['friendly_url_prefix'];
			$suffix    = $this->config['friendly_url_suffix'];
			
			for ($idx = 0; $idx < $maxidx; $idx++)
			{
				$documentSource .= $pieces[$idx];
				$idx++;
				if ($idx < $maxidx)
				{
					$target = trim($pieces[$idx]);
					if(preg_match("/^[0-9]+$/",$this->referenceListing[$target]))
						$target = $this->referenceListing[$target];
					elseif(preg_match("/^[0-9]+$/",$target))
						$target = $aliases[$target];
					else $target = $this->parseDocumentSource($target);
					
					if(preg_match('@^https?://@', $this->referenceListing[$target]))
					                                        $path = $this->referenceListing[$target];
					elseif($aliases[$target] && $use_alias) $path = $this->makeFriendlyURL($prefix, $suffix, $aliases[$target]);
					else                                    $path = $this->makeFriendlyURL($prefix, $suffix, $target);
					$documentSource .= $path;
				}
			}
			unset($aliases);
		}
		else
		{
			for ($idx = 0; $idx < $maxidx; $idx++)
			{
				$documentSource .= $pieces[$idx];
				$idx++;
				if ($idx < $maxidx)
				{
					$target = trim($pieces[$idx]);
					if(isset($this->referenceListing[$target]) && preg_match("/^[0-9]+$/",$this->referenceListing[$target]))
						$target = $this->referenceListing[$target];
					
					if($target === $this->config['site_start'])
						$path = 'index.php';
					elseif(isset($this->referenceListing[$target]) && preg_match('@^https?://@', $this->referenceListing[$target]))
						$path = $this->referenceListing[$target];
					else
						$path = 'index.php?id=' . $target;
					$documentSource .= $path;
				}
			}
        }
        return $documentSource;
    }

	/**
	* name: getDocumentObject  - used by parser
	* desc: returns a document object - $method: alias, id
	*/
	function getDocumentObject($method, $identifier)
	{
		$tbl_site_content= $this->getFullTableName("site_content");
		$tbl_document_groups= $this->getFullTableName("document_groups");
		// allow alias to be full path
		if($method == 'alias')
		{
			$identifier = $this->cleanDocumentIdentifier($identifier);
			$method = $this->documentMethod;
		}
		if($method == 'alias' && $this->config['use_alias_path'] && isset($this->documentListing[$identifier]))
		{
			$identifier = $this->documentListing[$identifier];
			$method = 'id';
		}
		// get document groups for current user
		if ($docgrp= $this->getUserDocGroups())
		{
			$docgrp= implode(',', $docgrp);
		}
		// get document (add so)
		if($this->isFrontend()) $access= "sc.privateweb=0";
		else                    $access= "sc.privatemgr=0";
		if($docgrp) $access .= " OR dg.document_group IN ({$docgrp})";
		$access .= " OR 1='{$_SESSION['mgrRole']}'";
		
		$from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg ON dg.document = sc.id";
		$where ="sc.{$method}='{$identifier}' AND ($access)";
		$result= $this->db->select('sc.*',$from,$where,'',1);
		if ($this->db->getRecordCount($result) < 1)
		{
			if ($this->config['unauthorized_page'])
			{
				// method may still be alias, while identifier is not full path alias, e.g. id not found above
				if ($method === 'alias')
				{
					$field = 'dg.id';
					$from = "{$tbl_document_groups} dg, {$tbl_site_content} sc";
					$where =  "dg.document = sc.id AND sc.alias = '{$identifier}'";
				}
				else
				{
					$field = 'id';
					$from = $tbl_document_groups;
					$where =  "document = '{$identifier}'";
				}
				// check if file is not public
				$seclimit= $this->db->getRecordCount($this->db->select($field,$from,$where,'',1));
			}
			if ($seclimit > 0)
			{
				// match found but not publicly accessible, send the visitor to the unauthorized_page
				$this->sendUnauthorizedPage();
			}
			else
			{
				$this->sendErrorPage();
			}
		}
		
		# this is now the document :) #
		$documentObject= $this->db->getRow($result);
		
		// load TVs and merge with document - Orig by Apodigm - Docvars
		$tbl_site_tmplvars = $this->getFullTableName('site_tmplvars');
		$tbl_site_tmplvar_templates = $this->getFullTableName('site_tmplvar_templates');
		$tbl_site_tmplvar_contentvalues = $this->getFullTableName('site_tmplvar_contentvalues');
		
		$field = "tv.name, IF(tvc.value!='',tvc.value,tv.default_text) as value,tv.display,tv.display_params,tv.type";
		$from  = "{$tbl_site_tmplvars} tv ";
		$from .= "INNER JOIN {$tbl_site_tmplvar_templates} tvtpl ON tvtpl.tmplvarid = tv.id ";
		$from .= "LEFT JOIN {$tbl_site_tmplvar_contentvalues} tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '{$documentObject['id']}'";
		$where = "tvtpl.templateid = '{$documentObject['template']}'";
		$rs = $this->db->select($field,$from,$where);
		$rowCount= $this->db->getRecordCount($rs);
		if ($rowCount > 0)
		{
			while ($row= $this->db->getRow($rs))
			{
				$tmplvars[$row['name']]= array
				(
					$row['name'],
					$row['value'],
					$row['display'],
					$row['display_params'],
					$row['type']
				);
			}
			$documentObject= array_merge($documentObject, $tmplvars);
		}
		return $documentObject;
	}
	
	/**
	* name: parseDocumentSource - used by parser
	* desc: return document source aftering parsing tvs, snippets, chunks, etc.
	*/
	function parseDocumentSource($source)
	{
		$passes= $this->minParserPasses;
		for ($i= 0; $i < $passes; $i++)
		{
			// get source length if this is the final pass
			if ($i == ($passes -1)) $bt= md5($source);
			if ($this->dumpSnippets == 1)
			{
				$this->snipCode .= "<fieldset><legend><b style='color: #821517;'>PARSE PASS " . ($i +1) . "</b></legend>The following snippets (if any) were parsed during this pass.<div style='width:100%' align='center'>";
			}
			
			// invoke OnParseDocument event
			$this->documentOutput= $source; // store source code so plugins can
			$this->invokeEvent('OnParseDocument'); // work on it via $modx->documentOutput
			$source= $this->documentOutput;
			
			// combine template and document variables
			if(strpos($source,'[*')!==false) $source= $this->mergeDocumentContent($source);
			// replace settings referenced in document
			if(strpos($source,'[(')!==false) $source= $this->mergeSettingsContent($source);
			// replace HTMLSnippets in document
			if(strpos($source,'{{')!==false) $source= $this->mergeChunkContent($source);
			// insert META tags & keywords
			$source= $this->mergeDocumentMETATags($source);
			// find and merge snippets
			if(strpos($source,'[[')!==false) $source= $this->evalSnippets($source);
			// find and replace Placeholders (must be parsed last) - Added by Raymond
			if(strpos($source,'[+')!==false) $source= $this->mergePlaceholderContent($source);
			if ($this->dumpSnippets == 1)
			{
				$this->snipCode .= '</div></fieldset>';
			}
			if ($i == ($passes -1) && $i < ($this->maxParserPasses - 1))
			{
				// check if source length was changed
				if ($bt != md5($source))
				{
					$passes++; // if content change then increase passes because
				}
			} // we have not yet reached maxParserPasses
			if(strpos($source,'[~')!==false) $source = $this->rewriteUrls($source);//yama
		}
		return $source;
	}
	
	/***************************************************************************************/
	/* API functions																/
	/***************************************************************************************/

	function getParentIds($id, $height= 10)
	{
		$parents= array ();
		while( $id && 0<$height)
		{
			$current_id = $id;
			$id = $this->aliasListing[$id]['parent'];
			if(!$id)
			{
				break;
			}
			if(strlen($this->aliasListing[$current_id]['path']))
			{
				$pkey = $this->aliasListing[$current_id]['path'];
			}
			else
			{
				$pkey = $this->aliasListing[$id]['alias'];
			}
			if(!strlen($pkey))
			{
				$pkey = $id;
			}
			$parents[$pkey] = $id;
			$height--;
		}
		return $parents;
	}

	function set_documentMap_cache()
	{
		$path_documentmapcache = MODX_BASE_PATH . 'assets/cache/documentmap.pageCache.php';
		if(file_exists($path_documentmapcache))
		{
			$src = file_get_contents($path_documentmapcache);
			$this->documentMap_cache = unserialize($src);
		}
		else
		{
			$documentMap_cache= array ();
			foreach ($this->documentMap as $document)
			{
				foreach ($document as $p => $c)
				{
					$documentMap_cache[$p][] = $c;
				}
			}
			file_put_contents($path_documentmapcache,serialize($documentMap_cache));
			$this->documentMap_cache = $documentMap_cache;
		}
		return $this->documentMap_cache;
	}

	function getChildIds($id, $depth= 10, $children= array ())
	{
		// Initialise a static array to index parents->children
		if(!count($this->documentMap_cache))
			$documentMap_cache = $this->set_documentMap_cache();
		else
			$documentMap_cache = $this->documentMap_cache;
		
		// Get all the children for this parent node
		if (isset($documentMap_cache[$id]))
		{
			$depth--;
			
			foreach ($documentMap_cache[$id] as $childId)
			{
				$pkey = $this->aliasListing[$childId]['alias'];
				if(strlen($this->aliasListing[$childId]['path']))
				{
					$pkey = "{$this->aliasListing[$childId]['path']}/{$pkey}";
				}
				
				if (!strlen($pkey)) $pkey = $childId;
				$children[$pkey] = $childId;
				
				if ($depth)
				{
					$children += $this->getChildIds($childId, $depth);
				}
			}
		}
		return $children;
	}

	# Displays a javascript alert message in the web browser
	function webAlert($msg, $url= '')
	{
		$msg= addslashes($this->db->escape($msg));
		if (substr(strtolower($url), 0, 11) == 'javascript:')
		{
			$act= '__WebAlert();';
			$fnc= 'function __WebAlert(){' . substr($url, 11) . '};';
		}
		else
		{
			$act= $url ? "window.location.href='" . addslashes($url) . "';" : '';
		}
		$html= "<script>{$fnc} window.setTimeout(\"alert('{$msg}');{$act}\",100);</script>";
		if ($this->isFrontend())
		{
			$this->regClientScript($html);
		}
		else
		{
			echo $html;
		}
	}

    # Returns true if user has the currect permission
    function hasPermission($pm) {
        $state= false;
        $pms= $_SESSION['mgrPermissions'];
        if ($pms)
            $state= ($pms[$pm] == 1);
        return $state;
    }

    # Add an a alert message to the system event log
	function logEvent($evtid, $type, $msg, $source= 'Parser')
	{
		$evtid= intval($evtid);
		if ($type < 1) $type= 1; // Types: 1 = information, 2 = warning, 3 = error
		if (3 < $type) $type= 3;
		$msg= $this->db->escape($msg);
		$source= $this->db->escape($source);
		if (function_exists('mb_substr'))
		{
			$source = mb_substr($source, 0, 50 , $this->config['modx_charset']);
		}
		else
		{
			$source = substr($source, 0, 50);
		}
		$LoginUserID = $this->getLoginUserID();
		if ($LoginUserID == '' || $LoginUserID===false) $LoginUserID = '-';
		
		$fields['eventid']     = $evtid;
		$fields['type']        = $type;
		$fields['createdon']   = time();
		$fields['source']      = $source;
		$fields['description'] = $msg;
		$fields['user']        = $LoginUserID;
		$insert_id = $this->db->insert($fields,$this->getFullTableName('event_log'));
		if(isset($this->config['send_errormail']) && $this->config['send_errormail'] !== '0')
		{
			if($this->config['send_errormail'] <= $type)
			{
				$subject = 'エラー発生通知 from ' . $this->config['site_name'];
				$this->sendmail($subject,$source);
			}
		}
		if (!$insert_id)
		{
			echo 'Error while inserting event log into database.';
			exit();
		}
		else
		{
			$trim  = (isset($this->config['event_log_trim']))  ? intval($this->config['event_log_trim']) : 100;
			if(($insert_id % $trim) == 0)
			{
				$limit = (isset($this->config['event_log_limit'])) ? intval($this->config['event_log_limit']) : 2000;
				$this->purge_event_log($limit,$trim);
			}
		}
	}
	
	function sendmail($params=array(), $msg='')
	{
		if(isset($params) && is_string($params))
		{
			if(strpos($params,'=')===false)
			{
				if(strpos($params,'@')!==false) $p['sendto']  = $params;
				else                            $p['subject'] = $params;
			}
			else
			{
				$params_array = explode(',',$params);
				foreach($params_array as $k=>$v)
				{
					$k = trim($k);
					$v = trim($v);
					$p[$k] = $v;
				}
			}
		}
		else
		{
			$p = $params;
			unset($params);
		}
		include_once $this->config['base_path'] . 'manager/includes/controls/modxmailer.inc.php';
		$mail = new MODxMailer();
		$mail->IsMail();
		$mail->IsHTML(0);
		$mail->From     = (!isset($p['from']))     ? $this->config['emailsender']  : $p['from'];
		$mail->FromName = (!isset($p['fromname'])) ? $this->config['site_name']    : $p['fromname'];
		$mail->Subject  = (!isset($p['subject']))  ? $this->config['emailsubject'] : $p['subject'];
		$sendto         = (!isset($p['sendto']))   ? $this->config['emailsender']  : $p['sendto'];
		$mail->Body     = $msg;
		$mail->AddAddress($sendto);
		$rs = $mail->Send();
		return $rs;
	}
	
	function purge_event_log($limit=2000, $trim=100)
	{
		if($limit < $trim) $trim = $limit;
		
		$tbl_event_log = $this->getFullTableName('event_log');
		$count = $this->db->getValue($this->db->select('COUNT(id)',$tbl_event_log));
		$over = $count - $limit;
		if(0 < $over)
		{
			$trim = ($over + $trim);
			$this->db->delete($tbl_event_log,'',$trim);
			$this->db->query($sql);
		}
	}
	
	function remove_locks($action=27,$limit_time=86400)
	{
		$limit_time = time() - $limit_time;
		$action     = intval($action);
		$tbl_active_users = $this->getFullTableName('active_users');
		$this->db->delete($tbl_active_users,"action={$action} and lasthit < {$limit_time}");
	}

    # Returns true if parser is executed in backend (manager) mode
    function isBackend() {
		if(defined('IN_MANAGER_MODE') && IN_MANAGER_MODE == 'true')
		{
			return true;
		}
		else return false;
    }

	# Returns true if parser is executed in frontend mode
	function isFrontend()
	{
		if(defined('IN_MANAGER_MODE') && IN_MANAGER_MODE == 'true')
		{
			return false;
		}
		else return true;
	}
	
	function getAllChildren($id= 0, $sort= 'menuindex', $dir= 'ASC', $fields= 'id, pagetitle, description, parent, alias, menutitle')
	{
		$tbl_site_content= $this->getFullTableName('site_content');
		$tbl_document_groups= $this->getFullTableName('document_groups');
		// modify field names to use sc. table reference
		$fields= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
		$sort= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort)));
		// get document groups for current user
		if ($docgrp= $this->getUserDocGroups())
		{
			$docgrp= implode(',', $docgrp);
		}
		// build query
		$context = ($this->isFrontend() ? 'web' : 'mgr');
		$cond    = ($docgrp) ? "OR dg.document_group IN ({$docgrp}) OR 1='{$_SESSION['mgrRole']}'" : '';
		$from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
		$where = "sc.parent = '{$id}' AND (sc.private{$context}=0 {$cond}) GROUP BY sc.id";
		$orderby = "{$sort} {$dir}";
		$result= $this->db->select("DISTINCT {$fields}",$from,$where,$orderby);
		$resourceArray= array ();
		for ($i= 0; $i < $this->db->getRecordCount($result); $i++)
		{
			$resourceArray[] = $this->db->getRow($result);
		}
		return $resourceArray;
	}
	
	function getActiveChildren($id= 0, $sort= 'menuindex', $dir= 'ASC', $fields= 'id, pagetitle, description, parent, alias, menutitle')
	{
		$tbl_site_content    = $this->getFullTableName('site_content');
		$tbl_document_groups = $this->getFullTableName('document_groups');
		
		// modify field names to use sc. table reference
		$fields= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
		$sort= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort)));
		// get document groups for current user
		if ($docgrp= $this->getUserDocGroups())
		{
			$docgrp= implode(",", $docgrp);
		}
		// build query
		if($this->isFrontend()) $context = 'sc.privateweb=0';
		else                    $context = "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
		$cond = ($docgrp) ? " OR dg.document_group IN ({$docgrp})" : '';
		
		$fields = "DISTINCT {$fields}";
		$from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
		$where = "sc.parent = '{$id}' AND sc.published=1 AND sc.deleted=0 AND ({$context} {$cond}) GROUP BY sc.id";
		$orderby = "{$sort} {$dir}";
		$result= $this->db->select($fields,$from,$where,$orderby);
		$resourceArray= array ();
		for ($i= 0; $i < $this->db->getRecordCount($result); $i++)
		{
			$resourceArray[] = $this->db->getRow($result);
		}
		return $resourceArray;
	}
	
	function getDocumentChildren($parentid= 0, $published= 1, $deleted= 0, $fields= '*', $where= '', $sort= 'menuindex', $dir= 'ASC', $limit= '')
	{
		$tbl_site_content= $this->getFullTableName('site_content');
		$tbl_document_groups= $this->getFullTableName('document_groups');
		// modify field names to use sc. table reference
		$fields = 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
		if($where != '') $where= "AND {$where}";
		// get document groups for current user
		if ($docgrp= $this->getUserDocGroups()) $docgrp= implode(',', $docgrp);
		// build query
		$access  = $this->isFrontend() ? 'sc.privateweb=0' : "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
		$access .= !$docgrp ? '' : " OR dg.document_group IN ({$docgrp})";
		$from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
		$where = "sc.parent = '{$parentid}' AND sc.published={$published} AND sc.deleted={$deleted} {$where} AND ({$access}) GROUP BY sc.id";
		$sort = ($sort != '') ? 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort))) : '';
		$orderby = $sort ? "{$sort} {$dir}" : '';
		$result= $this->db->select("DISTINCT {$fields}",$from,$where,$orderby,$limit);
		$resourceArray= array ();
		for ($i= 0; $i < $this->db->getRecordCount($result); $i++)
		{
			$resourceArray[] = $this->db->getRow($result);
		}
		return $resourceArray;
	}
	
	function getDocuments($ids= array (), $published= 1, $deleted= 0, $fields= '*', $where= '', $sort= 'menuindex', $dir= 'ASC', $limit= '')
	{
		if (count($ids) == 0)
		{
			return false;
		}
		else
		{
			$tbl_site_content= $this->getFullTableName('site_content');
			$tbl_document_groups= $this->getFullTableName('document_groups');
			
			// modify field names to use sc. table reference
			$fields= 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $fields)));
			if($sort !== '')  $sort = 'sc.' . implode(',sc.', preg_replace("/^\s/i", '', explode(',', $sort)));
			if ($where != '') $where= "AND {$where}";
			// get document groups for current user
			if ($docgrp= $this->getUserDocGroups()) $docgrp= implode(',', $docgrp);
			$context = ($this->isFrontend()) ? 'web' : 'mgr';
			$cond = $docgrp ? "OR dg.document_group IN ({$docgrp})" : '';
			
			$fields = "DISTINCT {$fields}";
			$from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
			$ids_str = implode(',',$ids);
			$where = "(sc.id IN ({$ids_str}) AND sc.published={$published} AND sc.deleted={$deleted} {$where}) AND (sc.private{$context}=0 {$cond} OR 1='{$_SESSION['mgrRole']}') GROUP BY sc.id";
			$orderby = ($sort) ? "{$sort} {$dir}" : '';
			$result= $this->db->select($fields,$from,$where,$orderby,$limit);
			$resourceArray= array ();
			for ($i= 0; $i < $this->db->getRecordCount($result); $i++)
			{
				$resourceArray[] = $this->db->getRow($result);
			}
			return $resourceArray;
		}
	}

	function getDocument($id= 0, $fields= '*', $published= 1, $deleted= 0)
	{
		if ($id == 0) return false;
		else
		{
			$tmpArr[]= $id;
			$docs= $this->getDocuments($tmpArr, $published, $deleted, $fields, '', '', '', 1);
			
			if ($docs != false) return $docs[0];
			else                return false;
		}
	}

	function getPageInfo($docid= 0, $active= 1, $fields= 'id, pagetitle, description, alias')
	{
		if($docid == 0) return false;
		else
		{
			$tbl_site_content    = $this->getFullTableName('site_content');
			$tbl_document_groups = $this->getFullTableName('document_groups');
			
			// modify field names to use sc. table reference
			$fields = preg_replace("/\s/i", '',$fields);
			$fields = 'sc.' . implode(',sc.', explode(',', $fields));
			
			$published = ($active == 1) ? 'AND sc.published=1 AND sc.deleted=0' : '';
			
			// get document groups for current user
			if($docgrp= $this->getUserDocGroups())
			{
				$docgrp= implode(',', $docgrp);
			}
			if($this->isFrontend()) $context = 'sc.privateweb=0';
			else                    $context = "1='{$_SESSION['mgrRole']}' OR sc.privatemgr=0";
			$cond   =  ($docgrp) ? "OR dg.document_group IN ({$docgrp})" : '';
			
			$from = "{$tbl_site_content} sc LEFT JOIN {$tbl_document_groups} dg on dg.document = sc.id";
			$where = "(sc.id={$docid} {$published}) AND ({$context} {$cond})";
			$result = $this->db->select($fields,$from,$where,'',1);
			$pageInfo = $this->db->getRow($result);
			return $pageInfo;
		}
	}

	function getParent($pid= -1, $active= 1, $fields= 'id, pagetitle, description, alias, parent')
	{
		if ($pid == -1)
		{
			$pid= $this->documentObject['parent'];
			return ($pid == 0) ? false : $this->getPageInfo($pid, $active, $fields);
		}
		elseif ($pid == 0)
		{
			return false;
		}
		else
		{
			// first get the child document
			$child= $this->getPageInfo($pid, $active, "parent");
			
			// now return the child's parent
			$pid= ($child['parent']) ? $child['parent'] : 0;
			
			return ($pid == 0) ? false : $this->getPageInfo($pid, $active, $fields);
		}
	}
		
	function getSnippetId()
	{
		if ($this->currentSnippet)
		{
			$tbl_site_snippets = $this->getFullTableName("site_snippets");
			$snip = $this->db->escape($this->currentSnippet);
			$rs= $this->db->select('id', $tbl_site_snippets, "name='{$snip}'",'',1);
			$row= @ $this->db->getRow($rs);
			if ($row['id']) return $row['id'];
		}
		return 0;
	}
		
	function getSnippetName()
	{
		return $this->currentSnippet;
	}
	
    function clearCache($params=array()) {
    	if(opendir(MODX_BASE_PATH . 'assets/cache')!==false)
    	{
    		$showReport = ($params['showReport']) ? $params['showReport'] : false;
    		$target = ($params['target']) ? $params['target'] : 'pagecache,sitecache';
    		
			include_once MODX_MANAGER_PATH . 'processors/cache_sync.class.processor.php';
			$sync = new synccache();
			$sync->setCachepath(MODX_BASE_PATH . 'assets/cache/');
			$sync->setReport($showReport);
			$sync->setTarget($target);
			$sync->emptyCache(); // first empty the cache
			return true;
		}
		else return false;
    }

	function makeUrl($id, $alias= '', $args= '', $scheme= '')
	{
		$url= '';
		$virtualDir= '';
		$f_url_prefix = $this->config['friendly_url_prefix'];
		$f_url_suffix = $this->config['friendly_url_suffix'];
		if (!is_numeric($id))
		{
			$this->messageQuit("'{$id}' is not numeric and may not be passed to makeUrl()");
		}
		if ($args != '' && $this->config['friendly_urls'] == 1)
		{
			// add ? to $args if missing
			$c= substr($args, 0, 1);
			if (strpos($f_url_prefix, '?') === false)
			{
				if ($c == '&')     $args= '?' . ltrim($args, '&');
				elseif ($c != '?') $args= '?' . $args;
			}
			else
			{
				if ($c == '?')     $args= '&' . ltrim($args, '?');
				elseif ($c != '&') $args= '&' . $args;
			}
		}
		elseif ($args != '')
		{
			// add & to $args if missing
			$c= substr($args, 0, 1);
			if ($c == '?')     $args= '&' . ltrim($args, '?');
			elseif ($c != '&') $args= '&' . $args;
		}
		if ($this->config['friendly_urls'] == 1)
		{
			$alPath = '';
			if(empty($alias))
			{
				$alias = $id;
				if ($this->config['friendly_alias_urls'] == 1)
				{
					$al= $this->aliasListing[$id];
					if(!empty ($al['path'])) $alPath = $al['path'] . '/';
					if ($al && $al['alias']) $alias  = $al['alias'];
				}
			}
			
			if((strpos($alias, '.') !== false) && (isset($this->config['suffix_mode']) && $this->config['suffix_mode']==1))
			{
				$f_url_suffix = ''; // jp-edition only
			}
		}
		if ($this->config['friendly_urls'] == 1)
		{
			$url = $alPath . $f_url_prefix . $alias . $f_url_suffix . $args;
		}
		else
		{
			$url= "index.php?id={$id}{$args}";
		}
		
		$host= $this->config['base_url'];
		// check if scheme argument has been set
		if ($scheme != '')
		{
			// for backward compatibility - check if the desired scheme is different than the current scheme
			if (is_numeric($scheme) && $scheme != $_SERVER['HTTPS'])
			{
				$scheme= ($_SERVER['HTTPS'] ? 'http' : 'https');
			}
		
			// to-do: check to make sure that $site_url incudes the url :port (e.g. :8080)
			$host= ($scheme == 'full') ? $this->config['site_url'] : $scheme . '://' . $_SERVER['HTTP_HOST'] . $host;
		}
		
		if ($this->config['xhtml_urls'])
		{
			$url = preg_replace("/&(?!amp;)/",'&amp;', $host . $virtualDir . $url);
		}
		else
		{
			$url = $host . $virtualDir . $url;
		}
		$rs = $this->invokeEvent('OnMakeUrl',
				array(
					"id"    => $id,
					"alias" => $alias,
					"args"  => $args,
					"scheme"=> $scheme,
					"url"   => $url
				)
			);
		if (!empty($rs))
		{
			$url = end($rs);
		}
		return $url;
	}
		
	function getConfig($name= '')
	{
		if(empty($this->config[$name])) return false;
		else                            return $this->config[$name];
	}
		
	function getVersionData()
	{
		include_once($this->config["base_path"] . 'manager/includes/version.inc.php');
		$v= array ();
		$v['version']= $modx_version;
		$v['branch']= $modx_branch;
		$v['release_date']= $modx_release_date;
		$v['full_appname']= $modx_full_appname;
		return $v;
	}

	function makeList($array,$ulroot='root',$ulprefix='sub_',$type='',$ordered= false,$tablevel= 0)
	{
		// first find out whether the value passed is an array
		if (!is_array($array)) return "<ul><li>Bad list</li></ul>";
		
		$tabs= '';
		for ($i= 0; $i < $tablevel; $i++)
		{
			$tabs .= "\t";
		}
		
		$tag = ($ordered == true) ? 'ol' : 'ul';
		
		if(!empty($type)) $typestr= " style='list-style-type: {$type}'";
		else              $typestr= '';
		
		$listhtml= "{$tabs}<{$tag} class='{$ulroot}'{$typestr}>\n";
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$line = $this->makeList($value, "{$ulprefix}{$ulroot}", $ulprefix, $type, $ordered, $tablevel +2);
				$listhtml .= "{$tabs}\t<li>{$key}\n{$line}{$tabs}\t</li>\n";
			}
			else
			{
				$listhtml .= "{$tabs}\t<li>{$value}</li>\n";
			}
		}
		$listhtml = "{$tabs}</{$tag}>\n";
		return $listhtml;
	}
		
	function runSnippet($snippetName, $params= array ())
	{
		if (isset ($this->snippetCache[$snippetName]))
		{
			$snippet= $this->snippetCache[$snippetName];
			$properties= $this->snippetCache["{$snippetName}Props"];
		}
		else
		{ // not in cache so let's check the db
			$tbl_site_snippets = $this->getFullTableName('site_snippets');
			$esc_name = $this->db->escape($snippetName);
			$result= $this->db->select('name,snippet,properties',$tbl_site_snippets,"name='{$esc_name}'");
			if ($this->db->getRecordCount($result) == 1)
			{
				$snippet= $this->snippetCache[$snippetName]= $row['snippet'];
				$properties= $this->snippetCache["{$snippetName}Props"]= $row['properties'];
			}
			else
			{
				$snippet= $this->snippetCache[$snippetName]= "return false;";
				$properties= '';
			}
		}
		// load default params/properties
		$parameters= $this->parseProperties($properties);
		$parameters= array_merge($parameters, $params);
		// run snippet
		return $this->evalSnippet($snippet, $parameters);
	}
		
	function getChunk($chunkName)
	{
		if(isset($this->chunkCache[$chunkName]))
		{
			$t= $this->chunkCache[$chunkName];
			return $t;
		}
	}
	
	function parseChunk($chunkName, $chunkArr, $prefix= '{', $suffix= '}',$mode='chunk')
	{
		if (!is_array($chunkArr)) return false;
		
		if($mode==='chunk') $src= $this->getChunk($chunkName);
		else                $src = $chunkName;
		
		foreach ($chunkArr as $key => $value)
		{
			$src= str_replace("{$prefix}{$key}{$suffix}", $value, $src);
		}
		return $src;
	}

	function parsePlaceholder($src='', $ph=array(), $left= '[+', $right= '+]',$mode='ph')
	{ // jp-edition only
		if(!$ph) return $src;
		return $this->parseChunk($src, $ph, $left, $right, $mode);
	}
	
	function getUserData()
	{
		include_once($this->config["base_path"] . "manager/includes/extenders/getUserData.extender.php");
		return $tmpArray;
	}
		
	function toDateFormat($timestamp = 0, $mode = '')
	{
		$timestamp = trim($timestamp);
		$timestamp = intval($timestamp);
		
		switch($this->config['datetime_format'])
		{
			case 'YYYY/mm/dd':
				$dateFormat = '%Y/%m/%d';
				break;
			case 'dd-mm-YYYY':
				$dateFormat = '%d-%m-%Y';
				break;
			case 'mm/dd/YYYY':
				$dateFormat = '%m/%d/%Y';
				break;
		}
		
		if (empty($mode))
		{
			$strTime = $this->mb_strftime($dateFormat . " %H:%M:%S", $timestamp);
		}
		elseif ($mode == 'dateOnly')
		{
			$strTime = $this->mb_strftime($dateFormat, $timestamp);
		}
		elseif ($mode == 'formatOnly')
		{
			$strTime = $dateFormat;
		}
		return $strTime;
	}
	
	function toTimeStamp($str)
	{
		$str = trim($str);
		if (empty($str)) return '';
		
		switch($this->config['datetime_format'])
		{
			case 'YYYY/mm/dd':
				if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}[0-9 :]*$/', $str))
				{
					return '';
				}
				list ($Y, $m, $d, $H, $M, $S) = sscanf($str, '%4d/%2d/%2d %2d:%2d:%2d');
				break;
			case 'dd-mm-YYYY':
				if (!preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}[0-9 :]*$/', $str))
				{
					return '';
				}
				list ($d, $m, $Y, $H, $M, $S) = sscanf($str, '%2d-%2d-%4d %2d:%2d:%2d');
				break;
			case 'mm/dd/YYYY':
				if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}[0-9 :]*$/', $str))
				{
					return '';
				}
				list ($m, $d, $Y, $H, $M, $S) = sscanf($str, '%2d/%2d/%4d %2d:%2d:%2d');
				break;
		}
		if (!$H && !$M && !$S)
		{
			$H = 0;
			$M = 0;
			$S = 0;
		}
		$timeStamp = mktime($H, $M, $S, $m, $d, $Y);
		$timeStamp = intval($timeStamp);
		return $timeStamp;
	}
	
	function mb_strftime($format='%Y/%m/%d', $timestamp='')
	{
		$a = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
		$A = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
		$w         = strftime('%w', $timestamp);
		$p = array('am'=>'AM', 'pm'=>'PM');
		$P = array('am'=>'am', 'pm'=>'pm');
		$ampm = (strftime('%H', $timestamp) < 12) ? 'am' : 'pm';
		if($timestamp==='') return '';
		if(substr(PHP_OS,0,3) == 'WIN') $format = str_replace('%-', '%#', $format);
		$pieces    = preg_split('@(%[\-#]?[a-zA-Z%])@',$format,null,PREG_SPLIT_DELIM_CAPTURE);
		
		$str = '';
		foreach($pieces as $v)
		{
		if    ($v == '%a')             $str .= $a[$w];
		elseif($v == '%A')             $str .= $A[$w];
		elseif($v == '%p')             $str .= $p[$ampm];
		elseif($v == '%P')             $str .= $P[$ampm];
		elseif(strpos($v,'%')!==false) $str .= strftime($v, $timestamp);
		else                           $str .= $v;
		}
		return $str;
	}
	
	#::::::::::::::::::::::::::::::::::::::::
	# Added By: Raymond Irving - MODx
	#
	
	function getDocumentChildrenTVars($parentid= 0, $tvidnames= array (), $published= 1, $docsort= 'menuindex', $docsortdir= 'ASC', $tvfields= '*', $tvsort= 'rank', $tvsortdir= 'ASC')
	{
		$docs= $this->getDocumentChildren($parentid, $published, 0, '*', '', $docsort, $docsortdir);
		if (!$docs) return false;
		else
		{
			$result= array ();
			// get user defined template variables
			$fields= ($tvfields == '') ? 'tv.*' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", '', explode(',', $tvfields)));
			$tvsort= ($tvsort == '') ? '' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", '', explode(',', $tvsort)));
			if ($tvidnames == '*') $query= 'tv.id<>0';
			else
			{
				$join_tvidnames = implode("','", $tvidnames);
				$query  = is_numeric($tvidnames[0]) ? 'tv.id' : 'tv.name';
				$query .= " IN ('{$join_tvidnames}')";
			}
			if ($docgrp= $this->getUserDocGroups())
			{
				$docgrp= implode(',', $docgrp);
			}
			$tbl_site_tmplvars              = $this->getFullTableName('site_tmplvars');
			$tbl_site_tmplvar_templates     = $this->getFullTableName('site_tmplvar_templates');
			$tbl_site_tmplvar_contentvalues = $this->getFullTableName('site_tmplvar_contentvalues');
			$docCount= count($docs);
			for ($i= 0; $i < $docCount; $i++)
			{
				$tvs= array ();
				$docRow= $docs[$i];
				$docid= $docRow['id'];
				
				$fields  = "{$fields}, IF(tvc.value!='',tvc.value,tv.default_text) as value";
				$from    = "{$tbl_site_tmplvars} tv INNER JOIN {$tbl_site_tmplvar_templates} tvtpl ON tvtpl.tmplvarid = tv.id";
				$from   .= " LEFT JOIN {$tbl_site_tmplvar_contentvalues} tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '{$docid}'";
				$where   = "{$query} AND tvtpl.templateid = {$docRow['template']}";
				$orderby = ($tvsort) ? "{$tvsort} {$tvsortdir}" : '';
				$rs= $this->db->select($fields,$from,$where,$orderby);
				$total= $this->db->getRecordCount($rs);
				for ($x= 0; $x < $total; $x++)
				{
					$tvs[] = @ $this->db->getRow($rs);
				}
				
				// get default/built-in template variables
				ksort($docRow);
				foreach ($docRow as $key => $value)
				{
					if ($tvidnames == '*' || in_array($key, $tvidnames))
					{
						$tvs[] = array ('name'=>$key, 'value'=>$value);
					}
				}
				if (count($tvs)) $result[] = $tvs;
			}
			return $result;
		}
	}
		
	function getDocumentChildrenTVarOutput($parentid= 0, $tvidnames= array (), $published= 1, $docsort= 'menuindex', $docsortdir= 'ASC')
	{
		$docs= $this->getDocumentChildren($parentid, $published, 0, '*', '', $docsort, $docsortdir);
		if (!$docs) return false;
		else
		{
			$result= array ();
			for ($i= 0; $i < count($docs); $i++)
			{
				$tvs= $this->getTemplateVarOutput($tvidnames, $docs[$i]["id"], $published);
				if ($tvs) $result[$docs[$i]['id']]= $tvs; // Use docid as key - netnoise 2006/08/14
			}
			return $result;
		}
	}
	
	// Modified by Raymond for TV - Orig Modified by Apodigm - DocVars
	# returns a single TV record. $idnames - can be an id or name that belongs the template that the current document is using
	function getTemplateVar($idname= '', $fields= '*', $docid= '', $published= 1)
	{
		if ($idname == '') return false;
		else
		{
			$result= $this->getTemplateVars(array($idname), $fields, $docid, $published, '', ''); //remove sorting for speed
			return ($result != false) ? $result[0] : false;
		}
	}

	# returns an array of TV records. $idnames - can be an id or name that belongs the template that the current document is using
	function getTemplateVars($idnames=array(),$fields='*',$docid= '',$published= 1,$sort='rank',$dir='ASC')
	{
		if (($idnames!='*' && !is_array($idnames)) || count($idnames) == 0)
		{
			return false;
		}
		else
		{
			$result= array ();
			
			// get document record
			if ($docid == '')
			{
				$docid = $this->documentIdentifier;
				$resource= $this->documentObject;
			}
			else
			{
				$resource= $this->getDocument($docid, '*', $published);
				if (!$resource) return false;
			}
			// get user defined template variables
			$fields= ($fields == '') ? 'tv.*' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", '', explode(',', $fields)));
			$sort= ($sort == '') ? '' : 'tv.' . implode(',tv.', preg_replace("/^\s/i", '', explode(',', $sort)));
			if ($idnames == '*')
			{
				$where= 'tv.id<>0';
			}
			else
			{
				$tvnames = $this->db->escape(implode("\t", $idnames));
				$tvnames = str_replace("\t","','",$tvnames);
				$where = (is_numeric($idnames[0])) ? 'tv.id' : "tv.name IN ('{$tvnames}')";
			}
			if ($docgrp= $this->getUserDocGroups())
			{
				$docgrp= implode(',', $docgrp);
			}
			$tbl_site_tmplvars              = $this->getFullTableName('site_tmplvars');
			$tbl_site_tmplvar_templates     = $this->getFullTableName('site_tmplvar_templates');
			$tbl_site_tmplvar_contentvalues = $this->getFullTableName('site_tmplvar_contentvalues');
			$fields= "{$fields}, IF(tvc.value!='',tvc.value,tv.default_text) as value";
			$from  = "{$tbl_site_tmplvars} tv";
			$from .= " INNER JOIN {$tbl_site_tmplvar_templates} tvtpl  ON tvtpl.tmplvarid = tv.id";
			$from .= " LEFT JOIN {$tbl_site_tmplvar_contentvalues} tvc ON tvc.tmplvarid=tv.id AND tvc.contentid='{$docid}'";
			$where = "{$where} AND tvtpl.templateid={$resource['template']}";
			if ($sort)
			{
				 $orderby = "{$sort} {$dir}";
			}
			else $orderby = '';
			$rs= $this->db->select($fields,$from,$where,$orderby);
			while($row = $this->db->getRow($rs))
			{
				$result[] = $row;
			}
			
			// get default/built-in template variables
			ksort($resource);
			foreach ($resource as $key => $value)
			{
				if ($idnames == '*' || in_array($key, $idnames))
				{
					$result[] = array ('name'=>$key,'value'=>$value);
				}
			}
			return $result;
		}
	}

    # returns an associative array containing TV rendered output values. $idnames - can be an id or name that belongs the template that the current document is using
    function getTemplateVarOutput($idnames= array (), $docid= '', $published= 1, $sep='') {
        if (count($idnames) == 0) {
            return false;
        } else {
            $output= array ();
            $vars= ($idnames == '*' || is_array($idnames)) ? $idnames : array ($idnames);
            $docid= intval($docid) ? intval($docid) : $this->documentIdentifier;
            $result= $this->getTemplateVars($vars, '*', $docid, $published, '', '', $sep); // remove sort for speed
            if ($result == false)
                return false;
            else {
		$baspath= $this->config["base_path"] . "manager/includes";
		include_once $baspath . "/tmplvars.format.inc.php";
		include_once $baspath . "/tmplvars.commands.inc.php";
		for ($i= 0; $i < count($result); $i++) {
			$row= $result[$i];
			if (!$row['id'])
				$output[$row['name']]= $row['value'];
			else	$output[$row['name']]= getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'], $docid, $sep);
		}
		return $output;
            }
        }
    }

# returns the full table name based on db settings
	function getFullTableName($tbl)
	{
		$dbase = trim($this->db->config['dbase'],'`');
		return "`{$dbase}`.`{$this->db->config['table_prefix']}{$tbl}`";
	}

    # return placeholder value
    function getPlaceholder($name) {
        return $this->placeholders[$name];
    }

    # sets a value for a placeholder
    function setPlaceholder($name, $value) {
        $this->placeholders[$name]= $value;
    }

    # set arrays or object vars as placeholders
    function toPlaceholders($subject, $prefix= '') {
        if (is_object($subject)) {
            $subject= get_object_vars($subject);
        }
        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                $this->toPlaceholder($key, $value, $prefix);
            }
        }
    }

    function toPlaceholder($key, $value, $prefix= '') {
        if (is_array($value) || is_object($value)) {
            $this->toPlaceholders($value, "{$prefix}{$key}.");
        } else {
            $this->setPlaceholder("{$prefix}{$key}", $value);
        }
    }

    # returns the virtual relative path to the manager folder
    function getManagerPath() {
        return $this->config['base_url'] . 'manager/';
    }

    # returns the virtual relative path to the cache folder
    function getCachePath() {
        return $this->config['base_url'] . 'assets/cache/';
    }

	# sends a message to a user's message box
	function sendAlert($type, $to, $from, $subject, $msg, $private= 0)
	{
		$tbl_manager_users = $this->getFullTableName('manager_users');
		$private= ($private) ? 1 : 0;
		if (!is_numeric($to))
		{
			// Query for the To ID
			$rs= $this->db->select('id',$tbl_manager_users,"username='{$to}'");
			if ($this->db->getRecordCount($rs))
			{
				$rs= $this->db->getRow($rs);
				$to= $rs['id'];
			}
		}
		if (!is_numeric($from))
		{
			// Query for the From ID
			$rs= $this->db->select('id',$tbl_manager_users,"username='{$from}'");
			if ($this->db->getRecordCount($rs))
			{
				$rs= $this->db->getRow($rs);
				$from= $rs['id'];
			}
		}
		// insert a new message into user_messages
		$f['id']          = '';
		$f['type']        = $type;
		$f['subject']     = $subject;
		$f['message']     = $msg;
		$f['sender']      = $from;
		$f['recipient']   = $to;
		$f['private']     = $private;
		$f['postdate']    = time();
		$f['messageread'] = 0;
		$rs= $this->db->insert($f,$this->getFullTableName('user_messages'));
	}
	
	# Returns current user id
	function getLoginUserID($context= '')
	{
		if ($context && isset ($_SESSION["{$context}Validated"]))
		{
			return $_SESSION["{$context}InternalKey"];
		}
		elseif ($this->isFrontend() && isset ($_SESSION['webValidated']))
		{
			return $_SESSION['webInternalKey'];
		}
		elseif ($this->isBackend() && isset ($_SESSION['mgrValidated']))
		{
			return $_SESSION['mgrInternalKey'];
		}
		else return false;
	}

    # Returns current user name
    function getLoginUserName($context= '') {
        if (!empty($context) && isset ($_SESSION[$context . 'Validated'])) {
            return $_SESSION[$context . 'Shortname'];
        }
        elseif ($this->isFrontend() && isset ($_SESSION['webValidated'])) {
            return $_SESSION['webShortname'];
        }
        elseif ($this->isBackend() && isset ($_SESSION['mgrValidated'])) {
            return $_SESSION['mgrShortname'];
        }
        else return false;
    }

    # Returns current login user type - web or manager
    function getLoginUserType() {
        if ($this->isFrontend() && isset ($_SESSION['webValidated'])) {
            return 'web';
        }
        elseif ($this->isBackend() && isset ($_SESSION['mgrValidated'])) {
            return 'manager';
        } else {
            return '';
        }
    }

	# Returns a record for the manager user
	function getUserInfo($uid)
	{
		$tbl_manager_users = $this->getFullTableName('manager_users');
		$tbl_user_attributes = $this->getFullTableName('user_attributes');
		$field = 'mu.username, mu.password, mua.*';
		$from  = "{$tbl_manager_users} mu INNER JOIN {$tbl_user_attributes} mua ON mua.internalkey=mu.id";
		$rs= $this->db->select($field,$from,"mu.id = '$uid'");
		$limit= $this->db->getRecordCount($rs);
		if ($limit == 1)
		{
			$row= $this->db->getRow($rs);
			if (!$row['usertype']) $row['usertype']= 'manager';
			return $row;
		}
		else return false;
	}
	
	# Returns a record for the web user
	function getWebUserInfo($uid)
	{
		$tbl_web_users = $this->getFullTableName('web_users');
		$tbl_web_user_attributes = $this->getFullTableName('web_user_attributes');
		$field = 'wu.username, wu.password, wua.*';
		$from = "{$tbl_web_users} wu INNER JOIN {$tbl_web_user_attributes} wua ON wua.internalkey=wu.id";
		$rs= $this->db->select($field,$from,"wu.id='$uid'");
		$limit= $this->db->getRecordCount($rs);
		if ($limit == 1)
		{
			$row= $this->db->getRow($rs);
			if (!$row['usertype']) $row['usertype']= 'web';
			return $row;
		}
		else return false;
	}

    # Returns an array of document groups that current user is assigned to.
    # This function will first return the web user doc groups when running from frontend otherwise it will return manager user's docgroup
    # Set $resolveIds to true to return the document group names
	function getUserDocGroups($resolveIds= false)
	{
		$dg  = array(); // add so
		$dgn = array();
		if($this->isFrontend() && isset($_SESSION['webDocgroups']) && !empty($_SESSION['webDocgroups']) && isset($_SESSION['webValidated']))
		{
			$dg = $_SESSION['webDocgroups'];
			if(isset($_SESSION['webDocgrpNames']))
			{
				$dgn = $_SESSION['webDocgrpNames']; //add so
			}
		}
		if(isset($_SESSION['mgrDocgroups']) && !empty($_SESSION['mgrDocgroups']) && isset($_SESSION['mgrValidated']))
		{
			$dg = array_merge($dg, $_SESSION['mgrDocgroups']);
			if(isset($_SESSION['mgrDocgrpNames']))
			{
				$dgn = array_merge($dgn, $_SESSION['mgrDocgrpNames']);
			}
		}
		if(!$resolveIds)
		{
			return $dg;
		}
		elseif(!empty($dgn) || empty($dg))
		{
			return $dgn; // add so
		}
		elseif(is_array($dg))
		{
			// resolve ids to names
			$dgn = array ();
			$tbl_dgn = $this->getFullTableName('documentgroup_names');
			$imploded_dg = implode(',', $dg);
			$ds = $this->db->select('name', $tbl_dgn, "id IN ({$imploded_dg})");
			while ($row = $this->db->getRow($ds))
			{
				$dgn[count($dgn)] = $row['name'];
			}
			// cache docgroup names to session
			if($this->isFrontend()) $_SESSION['webDocgrpNames'] = $dgn;
			else                    $_SESSION['mgrDocgrpNames'] = $dgn;
			return $dgn;
		}
	}
	
	# Change current web user's password - returns true if successful, oterhwise return error message
	function changeWebUserPassword($oldPwd, $newPwd)
	{
		$rt= false;
		if ($_SESSION["webValidated"] == 1)
		{
			$tbl_web_users= $this->getFullTableName("web_users");
			$uid = $this->getLoginUserID();
			$ds= $this->db->select('id,username,password', $tbl_web_users, "`id`='{$uid}'");
			$limit= $this->db->getRecordCount($ds);
			if ($limit == 1)
			{
				$row= $this->db->getRow($ds);
				if ($row["password"] == md5($oldPwd))
				{
					if (strlen($newPwd) < 6)
					{
						return 'Password is too short!';
					}
					elseif ($newPwd == '')
					{
						return "You didn't specify a password for this user!";
					}
					else
					{
						$newPwd = $this->db->escape($newPwd);
						$this->db->update("password = md5('{$newPwd}')", $tbl_web_users, "id='{$uid}'");
						// invoke OnWebChangePassword event
						$this->invokeEvent("OnWebChangePassword",
						array
						(
							"userid" => $row["id"],
							"username" => $row["username"],
							"userpassword" => $newPwd
						));
						return true;
					}
				}
				else
				{
					return 'Incorrect password.';
				}
			}
		}
	}
	
	# returns true if the current web user is a member the specified groups
	function isMemberOfWebGroup($groupNames= array ())
	{
		if (!is_array($groupNames)) return false;
		
		// check cache
		$grpNames= isset ($_SESSION['webUserGroupNames']) ? $_SESSION['webUserGroupNames'] : false;
		if (!is_array($grpNames))
		{
			$tbl_webgroup_names= $this->getFullTableName("webgroup_names");
			$tbl_web_groups= $this->getFullTableName("web_groups");
			$uid = $this->getLoginUserID();
			$sql= "SELECT wgn.name
			FROM {$tbl_webgroup_names} wgn
			INNER JOIN {$tbl_web_groups} wg ON wg.webgroup=wgn.id AND wg.webuser='{$uid}'";
			$grpNames= $this->db->getColumn("name", $sql);
			
			// save to cache
			$_SESSION['webUserGroupNames']= $grpNames;
		}
		foreach ($groupNames as $k => $v)
		{
			if (in_array(trim($v), $grpNames)) return true;
		}
		return false;
	}

    # Registers Client-side CSS scripts - these scripts are loaded at inside the <head> tag
    function regClientCSS($src, $media='') {
        if (empty($src) || isset ($this->loadedjscripts[$src]))
            return '';
        $nextpos= max(array_merge(array(0),array_keys($this->sjscripts)))+1;
        $this->loadedjscripts[$src]['startup']= true;
        $this->loadedjscripts[$src]['version']= '0';
        $this->loadedjscripts[$src]['pos']= $nextpos;
        if (strpos(strtolower($src), "<style") !== false || strpos(strtolower($src), "<link") !== false) {
            $this->sjscripts[$nextpos]= $src;
        } else {
            $this->sjscripts[$nextpos]= "\t" . '<link rel="stylesheet" type="text/css" href="'.$src.'" '.($media ? 'media="'.$media.'" ' : '').'/>';
        }
    }

    # Registers Client-side JavaScript 	- these scripts are loaded at the end of the page unless $startup is true
	function regClientScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false), $startup= false)
	{
		if (empty($src)) return ''; // nothing to register
		
		if (!is_array($options))
		{
			if (is_bool($options))  // backward compatibility with old plaintext parameter
				$options = array('plaintext'=>$options);
			elseif (is_string($options)) // Also allow script name as 2nd param
				$options = array('name'=>$options);
			else
				$options = array();
		}
		$name      = isset($options['name'])      ? strtolower($options['name']) : '';
		$version   = isset($options['version'])   ? $options['version'] : '0';
		$plaintext = isset($options['plaintext']) ? $options['plaintext'] : false;
		$key       = !empty($name)                ? $name : $src;
		
		$useThisVer= true;
		if (isset($this->loadedjscripts[$key]))
		{ // a matching script was found
			// if existing script is a startup script, make sure the candidate is also a startup script
			if ($this->loadedjscripts[$key]['startup'])
				$startup= true;
			
			if (empty($name))
			{
				$useThisVer= false; // if the match was based on identical source code, no need to replace the old one
			}
			else
			{
				$useThisVer = version_compare($this->loadedjscripts[$key]['version'], $version, '<');
			}
			
			if ($useThisVer)
			{
				if ($startup==true && $this->loadedjscripts[$key]['startup']==false)
				{
					// remove old script from the bottom of the page (new one will be at the top)
					unset($this->jscripts[$this->loadedjscripts[$key]['pos']]);
				}
				else
				{
					// overwrite the old script (the position may be important for dependent scripts)
					$overwritepos= $this->loadedjscripts[$key]['pos'];
				}
			}
			else
			{ // Use the original version
				if ($startup==true && $this->loadedjscripts[$key]['startup']==false)
				{
					// need to move the exisiting script to the head
					$version= $this->loadedjscripts[$key][$version];
					$src= $this->jscripts[$this->loadedjscripts[$key]['pos']];
					unset($this->jscripts[$this->loadedjscripts[$key]['pos']]);
				}
				else
				{
					return ''; // the script is already in the right place
				}
			}
		}
		
		if ($useThisVer && $plaintext!=true && (strpos(strtolower($src), "<script") === false))
			$src= "\t" . '<script type="text/javascript" src="' . $src . '"></script>';
		if ($startup)
		{
			$pos= isset($overwritepos) ? $overwritepos : max(array_merge(array(0),array_keys($this->sjscripts)))+1;
			$this->sjscripts[$pos]= $src;
		}
		else
		{
			$pos= isset($overwritepos) ? $overwritepos : max(array_merge(array(0),array_keys($this->jscripts)))+1;
			$this->jscripts[$pos]= $src;
		}
		$this->loadedjscripts[$key]['version']= $version;
		$this->loadedjscripts[$key]['startup']= $startup;
		$this->loadedjscripts[$key]['pos']= $pos;
	}
	
    function regClientStartupHTMLBlock($html) {$this->regClientScript($html, true, true);} // Registers Client-side Startup HTML block
    function regClientHTMLBlock($html)        {$this->regClientScript($html, true);} // Registers Client-side HTML block
    
	# Registers Startup Client-side JavaScript - these scripts are loaded at inside the <head> tag
	function regClientStartupScript($src, $options= array('name'=>'', 'version'=>'0', 'plaintext'=>false))
	{
	                                           $this->regClientScript($src, $options, true);
	}
	
    # Remove unwanted html tags and snippet, settings and tags
    function stripTags($html, $allowed= '')
    {
        $t= strip_tags($html, $allowed);
        $t= preg_replace('~\[\*(.*?)\*\]~', '', $t); //tv
        $t= preg_replace('~\[\[(.*?)\]\]~', '', $t); //snippet
        $t= preg_replace('~\[\!(.*?)\!\]~', '', $t); //snippet
        $t= preg_replace('~\[\((.*?)\)\]~', '', $t); //settings
        $t= preg_replace('~\[\+(.*?)\+\]~', '', $t); //placeholders
        $t= preg_replace('~{{(.*?)}}~', '', $t); //chunks
        return $t;
    }

    # add an event listner to a plugin - only for use within the current execution cycle
    function addEventListener($evtName, $pluginName) {
	    if (!$evtName || !$pluginName)
		    return false;
	    if (!isset($this->pluginEvent[$evtName]))
		    $this->pluginEvent[$evtName] = array();
	    return $this->pluginEvent[$evtName][] = $pluginName; // return array count
    }

    # remove event listner - only for use within the current execution cycle
    function removeEventListener($evtName, $pluginName='') {
        if (!$evtName)
            return false;
        if ( $pluginName == '' ){
            unset ($this->pluginEvent[$evtName]);
            return true;
        }else{
            foreach($this->pluginEvent[$evtName] as $key => $val){
                if ($this->pluginEvent[$evtName][$key] == $pluginName){
                    unset ($this->pluginEvent[$evtName][$key]);
                    return true;
                }
            }
        }
        return false;
    }

    # remove all event listners - only for use within the current execution cycle
    function removeAllEventListener() {
        unset ($this->pluginEvent);
        $this->pluginEvent= array ();
    }

	# invoke an event. $extParams - hash array: name=>value
	function invokeEvent($evtName, $extParams= array ())
	{
		if ($this->safeMode == true)               return false;
		if (!$evtName)                             return false;
		if (!isset ($this->pluginEvent[$evtName])) return false;
		
		$el= $this->pluginEvent[$evtName];
		$results= array ();
		$numEvents= count($el);
		if ($numEvents > 0)
		{
			for ($i= 0; $i < $numEvents; $i++)
			{ // start for loop
				$pluginName= $el[$i];
				$pluginName = stripslashes($pluginName);
				// reset event object
				$e= & $this->event;
				$e->_resetEventObject();
				$e->name= $evtName;
				$e->activePlugin= $pluginName;
				
				// get plugin code
				if (isset ($this->pluginCache[$pluginName]))
				{
					$pluginCode= $this->pluginCache[$pluginName];
					$pluginProperties= isset($this->pluginCache["{$pluginName}Props"]) ? $this->pluginCache["{$pluginName}Props"] : '';
				}
				else
				{
					$fields = '`name`, plugincode, properties';
					$tbl_site_plugins = $this->getFullTableName('site_plugins');
					$where = "`name`='{$pluginName}' AND disabled=0";
					$result= $this->db->select($fields,$tbl_site_plugins,$where);
					if ($this->db->getRecordCount($result) == 1)
					{
						$row= $this->db->getRow($result);
						
						$pluginCode                      = $row['plugincode'];
						$this->pluginCache[$row['name']] = $row['plugincode']; 
						$pluginProperties= $this->pluginCache["{$row['name']}Props"]= $row['properties'];
					}
					else
					{
						$pluginCode                      = 'return false;';
						$this->pluginCache[$pluginName]  = 'return false;';
						$pluginProperties= '';
					}
				}
				
				// load default params/properties
				$parameter= $this->parseProperties($pluginProperties);
				if (!empty($extParams))
					$parameter= array_merge($parameter, $extParams);
				
				// eval plugin
				$this->evalPlugin($pluginCode, $parameter);
				$e->setAllGlobalVariables();
				if ($e->_output != '')
					$results[]= $e->_output;
				if ($e->_propagate != true)
					break;
			}
		}
		$e->activePlugin= '';
		return $results;
	}

	# parses a resource property string and returns the result as an array
	function parseProperties($propertyString)
	{
		$parameter= array ();
		if (empty($propertyString)) return $parameter;
		
		$tmpParams= explode('&', $propertyString);
		foreach ($tmpParams as $tmpParam)
		{
			if (strpos($tmpParam, '=') !== false)
			{
				$pTmp  = explode('=', $tmpParam);
				$pvTmp = explode(';', trim($pTmp[1]));
				if ($pvTmp[1] == 'list' && $pvTmp[3] != '')
				{
					$parameter[trim($pTmp[0])]= $pvTmp[3]; //list default
				}
				elseif ($pvTmp[1] != 'list' && $pvTmp[2] != '')
				{
					$parameter[trim($pTmp[0])]= $pvTmp[2];
				}
			}
		}
		foreach($parameter as $k=>$v)
		{
			$v = str_replace('%3D','=',$v);
			$v = str_replace('%26','&',$v);
			$parameter[$k] = $v;
		}
		return $parameter;
	}

	// - deprecated db functions
	function dbConnect()                 {$this->db->connect();$this->rs= $this->db->conn;}
	function dbQuery($sql)               {return $this->db->query($sql);}
	function recordCount($rs)            {return $this->db->getRecordCount($rs);}
	function fetchRow($rs,$mode='assoc') {return $this->db->getRow($rs, $mode);}
	function affectedRows($rs)           {return $this->db->getAffectedRows($rs);}
	function insertId($rs)               {return $this->db->getInsertId($rs);}
	function dbClose()                   {$this->db->disconnect();}
	
    // deprecated
	function insideManager()
	{
		$m= false;
		if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE == 'true')
		{
			$m= true;
			if(defined('SNIPPET_INTERACTIVE_MODE') && SNIPPET_INTERACTIVE_MODE == 'true')
			{
				$m= "interact";
			}
			elseif(defined('SNIPPET_INSTALL_MODE') && SNIPPET_INSTALL_MODE == 'true')
			{
				$m= "install";
			}
		}
		return $m;
	}

    function putChunk($chunkName) {return $this->getChunk($chunkName);}// deprecated alias name >.<
    function getDocGroups() {return $this->getUserDocGroups();} // deprecated
	function changePassword($o, $n) {return changeWebUserPassword($o, $n);} // deprecated

    function mergeDocumentMETATags($template) {
        if ($this->documentObject['haskeywords'] == 1) {
            // insert keywords
            $keywords = $this->getKeywords();
            if (is_array($keywords) && count($keywords) > 0) {
	            $keywords = implode(", ", $keywords);
	            $metas= "\t<meta name=\"keywords\" content=\"$keywords\" />\n";
            }

	    // Don't process when cached
	    $this->documentObject['haskeywords'] = '0';
        }
        if ($this->documentObject['hasmetatags'] == 1) {
            // insert meta tags
            $tags= $this->getMETATags();
            foreach ($tags as $n => $col) {
                $tag= strtolower($col['tag']);
                $tagvalue= $col['tagvalue'];
                $tagstyle= $col['http_equiv'] ? 'http-equiv' : 'name';
                $metas .= "\t<meta $tagstyle=\"$tag\" content=\"$tagvalue\" />\n";
            }

	    // Don't process when cached
	    $this->documentObject['hasmetatags'] = '0';
        }
	if (isset($metas) && $metas) $template = preg_replace("/(<head>)/i", "\\1\n\t" . trim($metas), $template);
        return $template;
    }

    function getMETATags($id= 0) {
        if ($id == 0) {
            $id= $this->documentObject['id'];
        }
        $sql= "SELECT smt.* " .
        "FROM " . $this->getFullTableName("site_metatags") . " smt " .
        "INNER JOIN " . $this->getFullTableName("site_content_metatags") . " cmt ON cmt.metatag_id=smt.id " .
        "WHERE cmt.content_id = '$id'";
        $ds= $this->db->query($sql);
        $limit= $this->db->getRecordCount($ds);
        $metatags= array ();
        if ($limit > 0) {
            for ($i= 0; $i < $limit; $i++) {
                $row= $this->db->getRow($ds);
                $metatags[$row['name']]= array (
                    "tag" => $row['tag'],
                    "tagvalue" => $row['tagvalue'],
                    "http_equiv" => $row['http_equiv']
                );
            }
        }
        return $metatags;
    }

	function userLoggedIn()
	{
		$userdetails= array ();
		if ($this->isFrontend() && isset ($_SESSION['webValidated']))
		{
			// web user
			$userdetails['loggedIn']= true;
			$userdetails['id']= $_SESSION['webInternalKey'];
			$userdetails['username']= $_SESSION['webShortname'];
			$userdetails['usertype']= 'web'; // added by Raymond
			return $userdetails;
		}
		elseif($this->isBackend() && isset ($_SESSION['mgrValidated']))
		{
			// manager user
			$userdetails['loggedIn']= true;
			$userdetails['id']= $_SESSION['mgrInternalKey'];
			$userdetails['username']= $_SESSION['mgrShortname'];
			$userdetails['usertype']= 'manager'; // added by Raymond
			return $userdetails;
		}
		else
		{
			return false;
		}
	}

    function getKeywords($id= 0) {
        if ($id == 0) {
            $id= $this->documentObject['id'];
        }
        $tblKeywords= $this->getFullTableName('site_keywords');
        $tblKeywordXref= $this->getFullTableName('keyword_xref');
        $sql= "SELECT keywords.keyword FROM " . $tblKeywords . " AS keywords INNER JOIN " . $tblKeywordXref . " AS xref ON keywords.id=xref.keyword_id WHERE xref.content_id = '$id'";
        $result= $this->db->query($sql);
        $limit= $this->db->getRecordCount($result);
        $keywords= array ();
        if ($limit > 0) {
            for ($i= 0; $i < $limit; $i++) {
                $row= $this->db->getRow($result);
                $keywords[]= $row['keyword'];
            }
        }
        return $keywords;
    }

    /***************************************************************************************/
    /* End of API functions								       */
    /***************************************************************************************/

    function phpError($nr, $text, $file, $line) {
        if (error_reporting() == 0 || $nr == 0 || ($nr == 8 && $this->stopOnNotice == false)) {
            return true;
        }
        if (is_readable($file)) {
            $source= file($file);
            $source= htmlspecialchars($source[$line -1]);
        } else {
            $source= '';
        } //Error $nr in $file at $line: <div><code>$source</code></div>
        $this->messageQuit("PHP Parse Error", '', true, $nr, $file, $source, $text, $line);
    }

    function messageQuit($msg= 'unspecified error', $query= '', $is_error= true, $nr= '', $file= '', $source= '', $text= '', $line= '') {

        $version= isset ($GLOBALS['version']) ? $GLOBALS['version'] : '';
		$release_date= isset ($GLOBALS['release_date']) ? $GLOBALS['release_date'] : '';
        $request_uri = $_SERVER['REQUEST_URI'];
        $request_uri = htmlspecialchars($request_uri, ENT_QUOTES);
        $ua          = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
        $referer     = htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES);
        $str = "
              <html><head><title>MODX Content Manager $version &raquo; $release_date</title>
              <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
              <style>td, body { font-size: 12px; font-family:Verdana; }</style>
              </head><body>
              ";
        if ($is_error) {
            $str .= "<h3 style='color:red'>&laquo; MODX Parse Error &raquo;</h3>
                    <table border='0' cellpadding='1' cellspacing='0'>
                    <tr><td colspan='3'>MODX encountered the following error while attempting to parse the requested resource:</td></tr>
                    <tr><td colspan='3'><b style='color:red;'>&laquo; $msg &raquo;</b></td></tr>";
        } else {
            $str .= "<h3 style='color:#003399'>&laquo; MODX Debug/ stop message &raquo;</h3>
                    <table border='0' cellpadding='1' cellspacing='0'>
                    <tr><td colspan='3'>The MODX parser recieved the following debug/ stop message:</td></tr>
                    <tr><td colspan='3'><b style='color:#003399;'>&laquo; $msg &raquo;</b></td></tr>";
        }

        if (!empty ($query)) {
            $str .= "<tr><td colspan='3'><b style='color:#999;font-size: 12px;'>SQL:<span id='sqlHolder'>$query</span></b>
                    </td></tr>";
        }

        if ($text != '') {

            $errortype= array (
                E_ERROR => "Error",
                E_WARNING => "Warning",
                E_PARSE => "Parsing Error",
                E_NOTICE => "Notice",
                E_CORE_ERROR => "Core Error",
                E_CORE_WARNING => "Core Warning",
                E_COMPILE_ERROR => "Compile Error",
                E_COMPILE_WARNING => "Compile Warning",
                E_USER_ERROR => "User Error",
                E_USER_WARNING => "User Warning",
                E_USER_NOTICE => "User Notice",
                E_STRICT => "E_STRICT",
                E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
                E_DEPRECATED => "E_DEPRECATED",
                E_USER_DEPRECATED => "E_USER_DEPRECATED"
            );

            $str .= "<tr><td colspan='3'></td></tr><tr><td colspan='3'><b>PHP error debug</b></td></tr>";

            $str .= "<tr><td valign='top'>Error: </td>";
            $str .= "<td colspan='2'>$text</td><td></td>";
            $str .= "</tr>";

            $str .= "<tr><td valign='top'>Error type/ Nr.: </td>";
            $str .= "<td colspan='2'>" . $errortype[$nr] . " - $nr</td><td></td>";
            $str .= "</tr>";

            $str .= "<tr><td>File: </td>";
            $str .= "<td colspan='2'>$file</td><td></td>";
            $str .= "</tr>";

            $str .= "<tr><td>Line: </td>";
            $str .= "<td colspan='2'>$line</td><td></td>";
            $str .= "</tr>";
            if ($source != '') {
                $str .= "<tr><td valign='top'>Line $line source: </td>";
                $str .= "<td colspan='2'>$source</td><td></td>";
                $str .= "</tr>";
            }
        }

        $str .= "<tr><td colspan='3'></td></tr><tr><td colspan='3'><b>Basic info</b></td></tr>";

        $str .= "<tr><td valign='top'>REQUEST_URI: </td>";
        $str .= "<td colspan='2'>$request_uri</td>";
        $str .= "</tr>";

        $str .= "<tr><td valign='top'>ID: </td>";
        $str .= "<td colspan='2'>" . $this->documentIdentifier . "</td>";
        $str .= "</tr>";

        if(!empty($this->currentSnippet))
        {
            $str .= "<tr><td>Current Snippet: </td>";
            $str .= '<td colspan="2">' . $this->currentSnippet . '</td>';
            $str .= "</tr>";
        }

        if(!empty($this->event->activePlugin))
        {
            $str .= "<tr><td>Current Plugin: </td>";
            $str .= '<td colspan="2">' . $this->event->activePlugin . '(' . $this->event->name . ')' . '</td>';
            $str .= "</tr>";
        }

        $str .= "<tr><td>Referer: </td>";
        $str .= '<td colspan="2">' . $referer . '</td>';
        $str .= "</tr>";

        $str .= "<tr><td>User Agent: </td>";
        $str .= '<td colspan="2">' . $ua . '</td>';
        $str .= "</tr>";

        $str .= '<tr><td colspan="2"></td></tr>';
        $str .= '<tr><td colspan="2"><b>Parser timing</b></td></tr>';

        $str .= "<tr><td>MySQL: </td>";
        $str .= '<td colspan="2"><i>[^qt^] ([^q^] Requests</i>)</td>';
        $str .= "</tr>";

        $str .= "<tr><td>PHP: </td>";
        $str .= '<td colspan="2"><i>[^p^]</i></td>';
        $str .= "</tr>";

        $str .= "<tr><td>Total: </td>";
        $str .= '<td colspan="2"><i>[^t^]</i></td>';
        $str .= "</tr>";

        $str .= "</table>";
        $str .= "</body></html>";

        $totalTime= ($this->getMicroTime() - $this->tstart);

		if(function_exists('memory_get_peak_usage'))
		{
			$total_mem = $this->nicesize(memory_get_peak_usage() - $this->mstart);
		}
		else
		{
			$total_mem = $this->nicesize(memory_get_usage() - $this->mstart);
		}
        $queryTime= $this->queryTime;
        $phpTime= $totalTime - $queryTime;
        $queries= isset ($this->executedQueries) ? $this->executedQueries : 0;
        $queryTime= sprintf("%2.4f s", $queryTime);
        $totalTime= sprintf("%2.4f s", $totalTime);
        $phpTime= sprintf("%2.4f s", $phpTime);

        $str= str_replace("[^q^]", $queries, $str);
        $str= str_replace("[^qt^]", $queryTime, $str);
        $str= str_replace("[^p^]", $phpTime, $str);
        $str= str_replace("[^t^]", $totalTime, $str);
        $str= str_replace("[^m^]", $total_mem, $str);

        if(isset($php_errormsg) && !empty($php_errormsg)) $str = "<b>{$php_errormsg}</b><br />\n{$str}";
		$str .= '<br />' . $this->get_backtrace(debug_backtrace());

        // Log error
        if($source!=='') $source = 'Parser - ' . $source;
        else             $source = 'Parser';
        $this->logEvent(0, 3, $str,$source);
        if($nr == E_DEPRECATED) return true;

        // Set 500 response header
        header('HTTP/1.1 500 Internal Server Error');

        // Display error
        if (isset($_SESSION['mgrValidated'])) echo $str;
        else  echo 'Error';
        ob_end_flush();

        // Make sure and die!
        exit();
    }

    function getRegisteredClientScripts() {
        return implode("\n", $this->jscripts);
    }

    function getRegisteredClientStartupScripts() {
        return implode("\n", $this->sjscripts);
    }
    
	/**
	 * Format alias to be URL-safe. Strip invalid characters.
	 *
	 * @param string Alias to be formatted
	 * @return string Safe alias
	 */
    function stripAlias($alias) {
        // let add-ons overwrite the default behavior
        $results = $this->invokeEvent('OnStripAlias', array ('alias'=>$alias));
        if (!empty($results)) {
            // if multiple plugins are registered, only the last one is used
            return end($results);
        } else {
            // default behavior: strip invalid characters and replace spaces with dashes.
            $alias = strip_tags($alias); // strip HTML
//          $alias = preg_replace('/[^\.A-Za-z0-9 _-]/', '', $alias); // strip non-alphanumeric characters
//          $alias = preg_replace('/\s+/', '-', $alias); // convert white-space to dash
//          $alias = preg_replace('/-+/', '-', $alias);  // convert multiple dashes to one
//          $alias = trim($alias, '-'); // trim excess
            $alias = urlencode($alias);
            return $alias;
        }
    }
    
	function nicesize($size) {
		$a = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$pos = 0;
		while ($size >= 1024) {
			   $size /= 1024;
			   $pos++;
		}
		return round($size,2)." ".$a[$pos];
	}
    // End of class.
}

// SystemEvent Class
class SystemEvent {
    var $name;
    var $_propagate;
    var $_output;
    var $_globalVariables;
    var $activated;
    var $activePlugin;

    function SystemEvent($name= '') {
        $this->_resetEventObject();
        $this->name= $name;
    }

    // used for displaying a message to the user
    function alert($msg) {
        global $SystemAlertMsgQueque;
        if ($msg == '')
            return;
        if (is_array($SystemAlertMsgQueque)) {
            if ($this->name && $this->activePlugin)
                $title= "<div><b>" . $this->activePlugin . "</b> - <span style='color:maroon;'>" . $this->name . "</span></div>";
            $SystemAlertMsgQueque[]= "$title<div style='margin-left:10px;margin-top:3px;'>$msg</div>";
        }
    }

    // used for rendering an out on the screen
    function output($msg) {
        $this->_output .= $msg;
    }

    // get global variables
    function getGlobalVariable($key) {
        if( isset( $GLOBALS[$key] ) )
        {
            return $GLOBALS[$key];
        }
        return false;
    }

    // set global variables
    function setGlobalVariable($key,$val,$now=0) {
        if (! isset( $GLOBALS[$key] ) ) { return false; }
        if ( $now === 1 || $now === 'now' )
        {
            $GLOBALS[$key] = $val;
        }
        else
        {
            $this->_globalVariables[$key]=$val;
        }
        return true;
    }

    // set all global variables
    function setAllGlobalVariables() {
        if ( empty( $this->_globalVariables ) ) { return false; }
        foreach ( $this->_globalVariables as $key => $val )
        {
            $GLOBALS[$key] = $val;
        }
        return true;
    }

    function stopPropagation() {
        $this->_propagate= false;
    }

    function _resetEventObject() {
        unset ($this->returnedValues);
        $this->name= '';
        $this->_output= '';
        $this->_globalVariables=array();
        $this->_propagate= true;
        $this->activated= false;
    }
}
