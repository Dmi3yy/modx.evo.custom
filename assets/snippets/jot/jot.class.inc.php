<?php
class CJot {
	var $name;
	var $config = array();
	var $parameters = array();
	var $_ctime;
	var $_provider;
	var $_instance;
	var $templates = array();
	var $_link = array();
	var $output;
	var $event;
	
	function CJot() {
		global $modx;
		$path = strtr(realpath(dirname(__FILE__)), '\\', '/');
		include_once($path . '/includes/jot.db.class.inc.php');
		if (!class_exists('CChunkie'))
			include_once($path . '/includes/chunkie.class.inc.php');
		$this->name = $this->config["snippet"]["name"] = "Jot";
		$this->client = $modx->getUserData();
		$this->_ctime = time();
		$this->_check = 0;
		$this->provider = new CJotDataDb;
		$this->form = array();
	}
	
	function Get($field) {
		return $this->parameters[$field];
	}
	
	function Set($field, $value) {
		$this->parameters[$field] = $value;
	}

	function UniqueId($docid = 0,$tagid = '') {
		// Creates a unique hash / id
		$id[] = $docid."&".$tagid."&";
		foreach ($this->parameters as $n => $v) { $id[] = $n.'='.($v); }
		return md5(join('&',$id));
	}

	function Run() {
		global $modx;
		
		$this->config["path"] = $this->Get("path");
		$this->provider->path = $this->Get("path");
		
		//onBeforeConfiguration event
		$this->doEvent("onBeforeConfiguration");
		
		//DB events
		$this->provider->events["onBeforeFirstRun"] = $this->Get("onBeforeFirstRun");
		$this->provider->events["onFirstRun"] = $this->Get("onFirstRun");
		$this->provider->events["onDeleteComment"] = $this->Get("onDeleteComment");
		$this->provider->events["onGetCommentFields"] = $this->Get("onGetCommentFields");
		$this->provider->events["onBeforeSaveComment"] = $this->Get("onBeforeSaveComment");
		$this->provider->events["onSaveComment"] = $this->Get("onSaveComment");
		$this->provider->events["onSubscriptionCheck"] = $this->Get("onSubscriptionCheck");
		$this->provider->events["onGetSubscriptions"] = $this->Get("onGetSubscriptions");
		$this->provider->events["onBeforeSubscribe"] = $this->Get("onBeforeSubscribe");
		$this->provider->events["onBeforeUnsubscribe"] = $this->Get("onBeforeUnsubscribe");
		$this->provider->events["onBeforeGetUserPostCount"] = $this->Get("onBeforeGetUserPostCount");
		$this->provider->events["onBeforeGetCommentCount"] = $this->Get("onBeforeGetCommentCount");
		$this->provider->events["onBeforeGetComments"] = $this->Get("onBeforeGetComments");
		$this->provider->events["onGetComments"] = $this->Get("onGetComments");
		
		// Add input parameters (just for debugging purposes)
		$this->config["snippet"]["input"] = $this->parameters; 
				
		// General settings
		$this->config["docid"] = !is_null($this->Get("docid")) ? intval($this->Get("docid")):$modx->documentIdentifier;
		$this->config["tagid"] = !is_null($this->Get("tagid")) ? preg_replace("/[^A-z0-9_\-]/",'',$this->Get("tagid")):'';
		$this->config["docids"] = !is_null($this->Get("docids")) ? $this->processDocs($this->Get("docids")) : $this->config["docid"];
		$this->config["tagids"] = !is_null($this->Get("tagids")) ? $this->processTags($this->Get("tagids")) : $this->config["tagid"];
		$this->config["pagination"] = !is_null($this->Get("pagination")) ? $this->Get("pagination") : 10; // Set pagination (0 = disabled, # = comments per page)
		$this->config["captcha"] = !is_null($this->Get("captcha")) ? intval($this->Get("captcha")) : 0; // Set captcha (0 = disabled, 1 = enabled, 2 = enabled for not logged in users)
		$this->config["postdelay"] = !is_null($this->Get("postdelay")) ? $this->Get("postdelay") : 15; // Set post delay in seconds
		$this->config["guestname"] = !is_null($this->Get("guestname")) ? $this->Get("guestname") : "Гость"; // Set guestname if none is specified
		$this->config["subscriber"] = !is_null($this->Get("subscriber")) ? $this->Get("subscriber") : "подписчик";
		$this->config["subscribe"] = !is_null($this->Get("subscribe")) ? intval($this->Get("subscribe")) : 0;
		$this->config["numdir"] = !is_null($this->Get("numdir")) ? intval($this->Get("numdir")) : 1;
		$this->config["placeholders"] = !is_null($this->Get("placeholders")) ? intval($this->Get("placeholders")) : 0;
		$this->config["authorid"] = !is_null($this->Get("authorid")) ? intval($this->Get("authorid")) : intval($modx->documentObject["createdby"]);
		$this->config["title"] = !is_null($this->Get("title")) ? $this->Get("title") : '';
		$this->config["subject"]["subscribe"] = !is_null($this->Get("subjectSubscribe")) ? $this->Get("subjectSubscribe") : "Новый комментарий";
		$this->config["subject"]["moderate"] = !is_null($this->Get("subjectModerate")) ? $this->Get("subjectModerate") : "Новый комментрий в модерируемом разделе";
		$this->config["subject"]["author"] = !is_null($this->Get("subjectAuthor")) ? $this->Get("subjectAuthor") : "Новый комментарий на ваш материал";
		$this->config["subject"]["emails"] = !is_null($this->Get("subjectEmails")) ? $this->Get("subjectEmails") : $this->config["subject"]["subscribe"];
		$this->config["debug"] = !is_null($this->Get("debug")) ? intval($this->Get("debug")) : 0;
		$this->config["output"] = !is_null($this->Get("output")) ? intval($this->Get("output")) : 1;
		$this->config["validate"] = !is_null($this->Get("validate")) ? $this->Get("validate") : "content:Вы не заполнили поле сообщения";
		$this->config["upc"] = !is_null($this->Get("upc")) ? intval($this->Get("upc")) : 1;
		$this->config["limit"] = !is_null($this->Get("limit")) ? intval($this->Get("limit")) : 0;
		$this->config["depth"] = !is_null($this->Get("depth")) ? intval($this->Get("depth")) : 10;
		$notifyEmails = !is_null($this->Get("notifyEmails")) ? explode(",",$this->Get("notifyEmails")) : array();
		foreach($notifyEmails as $notifyEmail) {
			$notifyProp = explode(":",$notifyEmail,2);
			$notifyProp[0] = trim($notifyProp[0]);
			$this->config["notifyEmails"][] = $notifyProp[0];
			$this->config["notifyNames"][$notifyProp[0]] = isset($notifyProp[1]) ? $notifyProp[1] : $this->config["subscriber"];
		}
		
		// CSS Settings (basic)
		$this->config["css"]["include"] = !is_null($this->Get("css")) ? intval($this->Get("css")) : 1;
		$this->config["css"]["file"] = !is_null($this->Get("cssFile")) ? $this->Get("cssFile") : "assets/snippets/jot/css/jot.css";
		$this->config["css"]["rowalt"] = !is_null($this->Get("cssRowAlt")) ? $this->Get("cssAltRow") : "jot-row-alt";
		$this->config["css"]["rowme"] = !is_null($this->Get("cssRowMe")) ? $this->Get("cssRowMe") : "jot-row-me";
		$this->config["css"]["rowauthor"] = !is_null($this->Get("cssRowAuthor")) ? $this->Get("cssRowAuthor") : "jot-row-author";
		
		// JS Settings
		$this->config["js"]["include"] = !is_null($this->Get("js")) ? intval($this->Get("js")) : 0;
		$this->config["js"]["file"] = !is_null($this->Get("jsFile")) ? $this->Get("jsFile") : "";
		
		// Security
		$this->config["user"]["mgrid"] = intval($_SESSION['mgrInternalKey']);
		$this->config["user"]["usrid"] = intval($_SESSION['webInternalKey']);
		$this->config["user"]["id"] = (	$this->config["user"]["usrid"] > 0 ) ? (-$this->config["user"]["usrid"]) : $this->config["user"]["mgrid"];

		$this->config["user"]["host"] = $this->client['ip'];
		$this->config["user"]["ip"] = $this->client['ip'];
		$this->config["user"]["agent"] = $this->client['ua'];
		$this->config["user"]["sechash"] = md5($this->config["user"]["id"].$this->config["user"]["host"].$this->config["user"]["ip"].$this->config["user"]["agent"]);
		
		// Automatic settings
		$this->_instance = $this->config["id"] = $this->UniqueId($this->config["docid"],$this->config["tagid"]);
		$this->_idshort = substr($this->_instance,0,8);
		if($this->config["captcha"] == 2) { if ($this->config["user"]["id"]) {	$this->config["captcha"] = 0;} else { $this->config["captcha"] = 1;} }
		$this->config["seed"] = rand(1000,10000);
		$this->config["doc.pagetitle"] = $modx->documentObject["pagetitle"];
		$this->config["customfields"] = $this->Get("customfields") ? explode(",",$this->Get("customfields")):array("name","email"); // Set names of custom fields
		$this->config["sortby"] = !is_null($this->Get("sortby")) ? strtolower($this->Get("sortby")) : "createdon:d";		
		if ($this->config["sortby"] != 'rand()') $this->config["sortby"] = $this->validateSortString($this->config["sortby"]);
								
		// Set access groups
		$this->config["permissions"]["post"] = !is_null($this->Get("canpost")) ? explode(",",$this->Get("canpost")):array();
		$this->config["permissions"]["view"] = !is_null($this->Get("canview")) ? explode(",",$this->Get("canview")):array();
		$this->config["permissions"]["edit"] = !is_null($this->Get("canedit")) ? explode(",",$this->Get("canedit")):array();
		$this->config["permissions"]["moderate"] = !is_null($this->Get("canmoderate")) ? explode(",",$this->Get("canmoderate")):array();
		$this->config["permissions"]["trusted"] = !is_null($this->Get("trusted")) ? explode(",",$this->Get("trusted")):array();
		
		// Moderation
		$this->config["moderation"]["type"] = !is_null($this->Get("moderated")) ? intval($this->Get("moderated")) : 0;
		$this->config["moderation"]["notify"] = !is_null($this->Get("notify")) ? intval($this->Get("notify")) : 1;
		$this->config["moderation"]["notifyAuthor"] = !is_null($this->Get("notifyAuthor")) ? intval($this->Get("notifyAuthor")) : 0;
		
		// Access Booleans
		// TODO Add logic for manager groups
		$this->isModerator = $this->config["moderation"]["enabled"] = intval($modx->isMemberOfWebGroup($this->config["permissions"]["moderate"] ) || $modx->checkSession());
		$this->isTrusted = $this->config["moderation"]["trusted"] = intval($modx->isMemberOfWebGroup($this->config["permissions"]["trusted"] ) || $this->isModerator);
		$this->canPost = $this->config["user"]["canpost"] = ((count($this->config["permissions"]["post"])==0) || $modx->isMemberOfWebGroup($this->config["permissions"]["post"]) || $this->isModerator) ? 1 : 0;
		$this->canView = $this->config["user"]["canview"] = ((count($this->config["permissions"]["view"])==0) || $modx->isMemberOfWebGroup($this->config["permissions"]["view"]) || $this->isModerator) ? 1 : 0;
		$this->canEdit = $this->config["user"]["canedit"] = intval($modx->isMemberOfWebGroup($this->config["permissions"]["edit"]) || $this->isModerator);
		
		// Templates
		$this->templates["form"] = !is_null($this->Get("tplForm")) ? $this->Get("tplForm") : $this->config["path"]."/templates/chunk.form.inc.html";
		$this->templates["comments"] = !is_null($this->Get("tplComments")) ? $this->Get("tplComments") : $this->config["path"]."/templates/chunk.comment.inc.html";
		$this->templates["navigation"] = !is_null($this->Get("tplNav")) ? $this->Get("tplNav") : $this->config["path"]."/templates/chunk.navigation.inc.html";
		$this->templates["moderate"] = !is_null($this->Get("tplModerate")) ? $this->Get("tplModerate") : $this->config["path"]."/templates/chunk.moderate.inc.html";
		$this->templates["subscribe"] = !is_null($this->Get("tplSubscribe")) ? $this->Get("tplSubscribe") : $this->config["path"]."/templates/chunk.subscribe.inc.html";
		$this->templates["notify"] = !is_null($this->Get("tplNotify")) ? $this->Get("tplNotify") : $this->config["path"]."/templates/chunk.notify.inc.txt";				
		$this->templates["notifymoderator"] = !is_null($this->Get("tplNotifyModerator")) ? $this->Get("tplNotifyModerator") : $this->config["path"]."/templates/chunk.notify.moderator.inc.txt";
		$this->templates["notifyauthor"] = !is_null($this->Get("tplNotifyAuthor")) ? $this->Get("tplNotifyAuthor") : $this->config["path"]."/templates/chunk.notify.author.inc.txt";
		$this->templates["notifyemails"] = !is_null($this->Get("tplNotifyEmails")) ? $this->Get("tplNotifyEmails") : $this->templates["notify"];
		$this->templates["navPage"] = !is_null($this->Get("tplNavPage")) ? $this->Get("tplNavPage") : $this->config["path"]."/templates/chunk.nav.page.inc.html";
		$this->templates["navPageCur"] = !is_null($this->Get("tplNavPageCur")) ? $this->Get("tplNavPageCur") : $this->config["path"]."/templates/chunk.nav.pagecur.inc.html";
		$this->templates["navPageSpl"] = !is_null($this->Get("tplNavPageSpl")) ? $this->Get("tplNavPageSpl") : '';
		
		// Querystring keys
		$this->config["querykey"]["action"] = "jot".$this->_idshort;
		$this->config["querykey"]["navigation"] = "jn".$this->_idshort;
		$this->config["querykey"]["id"] = "jid".$this->_idshort;
		$this->config["querykey"]["view"] = "jv".$this->_idshort;
		
		// Querystring values
		$this->config["query"]["action"] = $_GET[$this->config["querykey"]["action"]];
		$this->config["query"]["navigation"] = intval($_GET[$this->config["querykey"]["navigation"]]);
		$this->config["query"]["id"] = intval($_GET[$this->config["querykey"]["id"]]);
		$this->config["query"]["view"] = intval($_GET[$this->config["querykey"]["view"]]);
		
		// Form options
		$this->isPostback = $this->config["form"]["postback"] = ($_POST["JotForm"] == $this->_instance) ? 1 : 0;
		
		// Field validation array
		$valStrings = explode(",",$this->config["validate"]);
		$valFields = array();
		foreach($valStrings as $valString) {
			$valProp = explode(":",$valString,3);
			$valField = array();
			$valField["validation"] = "required";

			foreach($valProp as $i => $v) {
				if ($i==1) $valField["msg"] = $v;
				if ($i==2) $valField["validation"] = $v;
			}
			
			$valFields[$valProp[0]][] = $valField;
		}
		$this->config["form"]["validation"] = $valFields;
		
		//onConfiguration event
		$this->doEvent("onConfiguration");
		
		//-- Initialize form array()
		$this->form = array();
		$this->form["source"] = $this->config["query"]["id"];
		$this->form["guest"] = ($this->config["user"]["id"]) ? 0 : 1;
		$this->form["field"] = array("custom" => array());
		$this->form["error"] = 0;
		$this->form["confirm"] = 0;
		$this->form["published"] = 0;
		$this->form["badwords"] = 0;
		$this->form["edit"] = 0;
		$this->form["save"] = 0;
		$this->form["field"]["parent"] = intval($_GET['parent']);
		
		// Modes
		$this->config["mode"]["type"] = "comments";
		$this->config["mode"]["active"] = $this->config["query"]["action"];
		$this->config["mode"]["passive"] = str_replace('-','',$this->Get("action"));
		
		// Generated links
		$this->_link = array($this->config["querykey"]["action"]=>NULL,$this->config["querykey"]["id"]=>NULL);
		$this->config["link"]["id"] = $this->_idshort;
		$this->config["link"]["current"] = $this->preserveUrl($modx->documentIdentifier,'',$this->_link);
		$this->config["link"]["navigation"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["navigation"]=>NULL)),true);
		$this->config["link"]["subscribe"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'subscribe')));
		$this->config["link"]["unsubscribe"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'unsubscribe')));
		$this->config["link"]["save"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'save',$this->config["querykey"]["id"]=>$this->config["query"]["id"])));
		$this->config["link"]["edit"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'edit')),true);
		$this->config["link"]["delete"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'delete')),true);
		$this->config["link"]["view"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["view"]=>NULL)),true);
		$this->config["link"]["publish"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'publish')),true);
		$this->config["link"]["unpublish"] = $this->preserveUrl($modx->documentIdentifier,'',array_merge($this->_link,array($this->config["querykey"]["action"]=>'unpublish')),true);
		
		$this->provider->FirstRun($this->config["path"]); // Check for first run
		
		// Badwords
		$this->config["badwords"]["enabled"] = !is_null($this->Get("badwords")) ? 1 : 0;
		$this->config["badwords"]["type"] = !is_null($this->Get("bw")) ? intval($this->Get("bw")) : 1;
		if($this->config["badwords"]["enabled"]) {
			$badwords = $this->Get("badwords");
			$badwords = preg_replace("~([\n\r\t\s]+)~","",$badwords);
			$this->config["badwords"]["words"] = explode(",",$badwords);
			$this->config["badwords"]["regexp"] = "~" . implode("|",$this->config["badwords"]["words"]) . "~iu";
		}
				
		// Moderation
		if ($this->isModerator) {
			$this->config["moderation"]["view"] = $view = isset($_GET[$this->config["querykey"]["view"]]) ? $this->config["query"]["view"]: 2;
		}
		
		// Subscription
		$this->config["subscription"]["enabled"] = 0;
		$this->config["subscription"]["status"] = 0;
		if ($this->config["user"]["id"] && $this->config["subscribe"]) {
			$this->config["subscription"]["enabled"] = 1;
			$isSubscribed = $this->provider->hasSubscription($this->config["docid"],$this->config["tagid"], $this->config["user"]);
			if ($isSubscribed) $this->config["subscription"]["status"] = 1;
		}
		
		$commentId = $this->config["query"]["id"];
		
		//onBeforeRunActions event
		$this->doEvent("onBeforeRunActions");
		
		// Active action
		switch ($this->config["mode"]["active"]) {
			case "delete":
				$this->doModerate('delete',$commentId);
				break;
			case "publish":
				$this->doModerate('publish',$commentId);
				break;
			case "unpublish":
				$this->doModerate('unpublish',$commentId);
				break;
			case "edit":
				if ($this->isModerator) {
					$this->doModerate('edit',$commentId); 
					break;
				} else {
					$this->form["edit"] = 1;
				}
			case "save":
			 	if ($this->isModerator) {
					$this->doModerate('save',$commentId);
					break;
				} else {
					$this->form["edit"] = 1;
					$this->form["save"] = 1;
				}
			case "move":
				break;
			case "subscribe":
				if ($this->config["subscription"]["enabled"] == 1) {
					if ($this->config["subscription"]["status"] == 0) {
						$this->provider->Subscribe($this->config["docid"],$this->config["tagid"],$this->config["user"]);
						$this->config["subscription"]["status"] = 1;
					}
				}
				break;
			case "unsubscribe":
				if ($this->config["subscription"]["enabled"] == 1) {
					if ($this->config["subscription"]["status"] == 1) {
						$this->provider->Unsubscribe($this->config["docid"],$this->config["tagid"],$this->config["user"]);
						$this->config["subscription"]["status"] = 0;
					}
				}
				break;
		}
		
		//onRunActions event
		$this->doEvent("onRunActions");
		
		// Form Processing
		$frmCommentId = ($this->form["edit"]) ? $commentId : 0;
		$this->processForm($frmCommentId);	
	
		//onBeforeProcessPassiveActions event
		$this->doEvent("onBeforeProcessPassiveActions");
		
		// Passive Action					
		$actionPath = $this->config["path"].'actions/' . $this->config["mode"]["passive"] . '.inc.php';
		$object = & $this;
		if(is_file($actionPath)) {
			include_once $actionPath;
			$modeName = $this->config["mode"]["passive"] . '_mode';
			if(function_exists($modeName)) $this->output = $modeName($object);
		}
		
		//onProcessPassiveActions event
		$this->doEvent("onProcessPassiveActions");
		
		if ($this->config["debug"]) {
			$this->output .= '<br /><hr /><b>'.$this->name.' : Debug</b><hr /><pre style="overflow: auto;background-color: white;font-weight: bold;">';
			$this->output .= $this->getOutputDebug($this->config,"jot");
			$this->output .= '</pre><hr />';
	  }
		
		// Dump config into placeholders?
		if ($this->config["placeholders"]) $this->setPlaceholders($this->config,"jot");
		
		// Include stylesheet if needed
		if ($this->config["css"]["include"]) $modx->regClientCSS($modx->config["base_url"].$this->config["css"]["file"]);
		
		// Include JS if needed
		if ($this->config["js"]["include"]) $modx->regClientStartupScript($modx->config["base_url"].$this->config["js"]["file"]);
		
		//onReturnOutput event
		$this->doEvent("onReturnOutput");
		
		return $this->output;
	}
	
	// Output snippet values in debug format
	function getOutputDebug($value = '', $key = '', $path = '') {
		$keypath = !empty($path) ? $path . "." . $key : $key;
	    $output = array();
		if (is_array($value)) { 
			foreach ($value as $subkey => $subval) {
				$output[] = $this->getOutputDebug($subval, $subkey, $keypath);
            }
		} else { 
			$output[] = '<span style="color: navy;">'.$keypath.'</span> = <span style="color: maroon;">'.htmlspecialchars($value).'</span><br />';	
		}
		return implode("",$output);
	}
	
	// Create placeholders in MODx from arrays
	function setPlaceholders($value = '', $key = '', $path = '') {
		global $modx;
		$keypath = !empty($path) ? $path . "." . $key : $key;
	    $output = array();
		if (is_array($value)) { 
			foreach ($value as $subkey => $subval) {
				$this->setPlaceholders($subval, $subkey, $keypath);
            }
		} else {
			if (strlen($this->config["tagid"]) > 0) {$keypath .= ".".$this->config["tagid"]; }
			$modx->setPlaceholder($keypath,$value);	
		}
	}
	

	function processForm($id=0) {
		global $modx;
		
		// Comment
		$id = intval($id);
		$pObj = $this->provider;
		$formMode = $this->config["mode"]["passive"];	
		$saveComment = 1;
		$this->form["action"] = $this->config["link"]["current"];
		if ($id && $pObj->isValidComment($this->config["docid"],$this->config["tagid"],$id) && $this->canEdit) {
			$pObj->Comment($id);
			if (($pObj->Get("createdby") == $this->config["user"]["id"]) || $this->isModerator) {
				$this->form["action"] = $this->config["link"]["save"];
				$this->form['guest'] = ($pObj->Get("createdby") == 0 && $this->form["save"] != 1) ? 1 : 0;
				$this->form["field"] = $pObj->getFields();
				$this->config["mode"]["passive"] =  "form";
			} else {
				$this->form['edit'] = 0;
				$this->form['save'] = 0;
				$saveComment = 0;
			}
		} else {
			$pObj->Comment(0); // fix for update/new problem
		}
	
		// If this is not a postback or a false edit then return.
		if (!$this->isPostback || !$saveComment) return;
		
		// If we get here switch passive mode back and let the save option decide the final passive mode
		$this->config["mode"]["passive"] = $formMode;
						
		//-- Get Post Objects
		$chkPost = array();
		$valFields = array();
		
		//onBeforePOSTProcess event
		if (null !== ($output = $this->doEvent("onBeforePOSTProcess",array("id"=>$id,"pObj"=>&$pObj,"saveComment"=>&$saveComment)))) return;
		
		// For every field posted loop
		foreach($_POST as $n=>$v) {
			
			// Stripslashes if needed
			if (get_magic_quotes_gpc()) { $v = stripslashes($v); }
			
			// Validate fields and store error level + msg in array
			$valFields[] = $this->validateFormField($n,$v);
			
			// Store field data
			switch($n) {
				case 'title': // Title field
					if ($v == '' && $this->config["title"]) $v = "Re: " . $this->config["title"];
					$this->form["field"]["title"] = $v;
					$pObj->Set("title",$v); 
					break;
				case 'content': // Content field
					$this->form["field"]["content"] = $v;
					$pObj->Set("content",$v); 
					break;
 				case 'parent': // Parent field
					$this->form["field"]["parent"] = intval($v);
					$pObj->Set("parent",intval($v)); 
					break;
				default: // Custom fields
					if (in_array($n, $this->config["customfields"])) {
						$this->form["field"]["custom"][$n] = $v;
						$pObj->SetCustom($n,$v);
					} else {
						$this->form["field"][$n] = $v;
					}
			}
			
			//-- Detect bad words
			if ($this->config["badwords"]["enabled"]) $this->form['badwords'] = $this->form['badwords'] + preg_match_all($this->config["badwords"]["regexp"],$v,$matches);
			
			//-- 
			$chkPost[] = $n.'='.($v);
			
		} // --	
		
		//-- Double Post Capture
		$chkPost = md5(join('&',$chkPost));
		if ($_SESSION['JotLastPost'] == $chkPost) {
			$this->form['error'] = 1;
			$this->form['confirm'] = 0;
			$saveComment = 0;
		} else {
			$_SESSION['JotLastPost'] = $chkPost;
		}
		
		//-- Security check (Post Delay?)
		if ($saveComment && $this->form['error'] == 0 && $this->config["postdelay"] != 0 && $pObj->hasPosted($this->config["postdelay"],$this->config["user"])) {
			$this->form['error'] = 3; // Post to fast (within delay)
			return;
		};

		//-- Captcha/Veriword
		if ($saveComment && !(($this->config["captcha"] == 0 || isset($_POST['vericode']) && isset($_SESSION['veriword']) && $_SESSION['veriword'] == $_POST['vericode']))) {
			$this->form['error'] = 2; // Veriword / Captcha incorrect
			unset($pObj);
			return;
		} else {
			$_SESSION['veriword'] = md5($this->config["seed"]);
		}
		
		//-- Validate fields
		if ($saveComment) {
			foreach($valFields as $valid) {
				if (!$valid[0]) {
					$this->form['error'] = 5;
					$this->form['errormsg'] = $valid[1];
					$this->form['confirm'] = 0;
					$saveComment = 0;
					return;
				}
			}
		}
			
		// Everything OK so far
		if ($saveComment) {
			$this->form['confirm'] = 1;
			$this->form['published'] = 1;
		}
		
		//-- Check publish settings (moderations)
		if ($saveComment && $this->config["moderation"]["type"] && !$this->isTrusted) {
			$this->form['confirm'] = 2;
			$this->form['published'] = 0;
		} 
		
		// Badwords detection logic
		if ($saveComment && $this->form["badwords"] && $this->config["badwords"]["enabled"] && !$this->isTrusted) {
			switch($this->config["badwords"]["type"]) {
				case 2: // Post Rejected
					$this->form['error'] = 4;
					$this->form['confirm'] = 0;
					$saveComment = 0;
					break;
				case 1:  // Post Not Published
					$this->form['published'] = 0;
					$this->form['confirm'] = 2; // Post Not Published
					break;
				}
		}
		
		// If published or unpublished save the comment, else do nothing.
		if (!$id) {
			// this is a new post
			$pObj->Set("createdon",$this->_ctime);
			$pObj->Set("createdby",$this->config["user"]["id"]);
			$pObj->Set("secip",$this->config["user"]["ip"]);
			$pObj->Set("sechash",$this->config["user"]["sechash"]);
			$pObj->Set("uparent",$this->config["docid"]);
			$pObj->Set("tagid",$this->config["tagid"]);
		} else {
			// edit/save
			$pObj->Set("editedon",$this->_ctime);
			$pObj->Set("editedby",$this->config["user"]["id"]);
		}
		
		$pObj->Set("published",$this->form['published']);
		if ($saveComment) $pObj->Save();
		
		// Edit mode logic
		if ($saveComment && $this->form["save"]) { 
			$this->form["moderation"] = 0;
			$this->form["edit"] = 0;
			if($this->form["confirm"]==1) $this->form["confirm"] = 3;
			$this->form["action"] = $this->config["link"]["current"];
		}
		
		if ($this->form["edit"]) { $this->config["mode"]["passive"] = "form"; }
		
		// Notify Subscribers
		if ($saveComment && $this->form['published']>0 && $this->config["subscription"]["enabled"]) $this->doNotify($pObj->Get("id"),"notify");
		
		// Notify Moderators
		if ($saveComment && (($this->form['published']==0 && $this->config["moderation"]["notify"]==1) || ($this->form['published'] >0 && $this->config["moderation"]["notify"]==2)))
			$this->doNotify($pObj->Get("id"),"notifymoderator");
		
		// Notify Author
		if ($saveComment && $this->config["moderation"]["notifyAuthor"]) $this->doNotify($pObj->Get("id"),"notifyauthor");
		
		// Notify Emails
		if ($saveComment && !empty($this->config["notifyEmails"])) $this->doNotify($pObj->Get("id"),"notifyemails");
		
		// If no error occured clear fields.
		if ($this->form['error'] <= 0 ) $this->form["field"] = array();
		
		//onProcessForm event
		if (null !== ($output = $this->doEvent("onProcessForm",array("id"=>$id,"pObj"=>&$pObj,"saveComment"=>$saveComment)))) return;
		
		// Destroy Comment Object and return form array()
		unset($pObj);
		return;
	}
	
	// Notifications
	function doNotify($commentid=0,$action="notify") {
		global $modx;
		
		// Get comment fields
		$cObj = $this->provider;
		$cObj->Comment($commentid);
		$comment = $cObj->getFields();
		unset($cObj);
		
		switch ($action) {
			case "notify":
				$user_ids = $this->provider->getSubscriptions($this->config["docid"],$this->config["tagid"]);
				$subject = $this->config["subject"]["subscribe"];
				break;
			case "notifymoderator":
				$user_ids = $this->getMembersOfWebGroup($this->config["permissions"]["moderate"]);
				$subject = $this->config["subject"]["moderate"];
				break;
			case "notifyauthor":
				$user_ids = array($this->config["authorid"]);
				$subject = $this->config["subject"]["author"];
				break;
			case "notifyemails":
				$user_ids = $this->config["notifyEmails"];
				$subject = $this->config["subject"]["emails"];
				break;
		}

		include_once MODX_BASE_PATH . "manager/includes/controls/class.phpmailer.php";
		
		foreach ($user_ids as $user_id){
			if ($this->config["user"]["id"] !== $user_id) {
				if ($action == "notifyemails") {
					$user = array();
					$user["email"] = $user_id;
					$user["username"] = $this->config["notifyNames"][$user_id];
				} else {
					$user = $this->getUserInfo($user_id);
				}

				$tpl = new CChunkie($this->templates[$action]);
				$tpl->AddVar("siteurl","http://".$_SERVER["SERVER_NAME"]);
				
				//onBeforeNotify event
				if (null === $this->doEvent("onBeforeNotify",array("commentid"=>$commentid,"action"=>$action,"tpl"=>&$tpl,"subject"=>&$subject,"comment"=>&$comment,"user"=>&$user))) {
					$tpl->AddVar("jot",$this->config);
					$tpl->AddVar("comment",$comment);
					$tpl->AddVar("recipient",$user);
					$mail = new PHPMailer();
					$mail->IsMail();
					$mail->CharSet = $modx->config["modx_charset"]; 
					$mail->IsHTML(false);
					$mail->From = $modx->config["emailsender"];
					$mail->FromName = $modx->config["site_name"];
					$mail->Subject = $subject;
					$mail->Body = $tpl->Render();
					$mail->AddAddress($user["email"]);
					$mail->Send();
				}
			}
		}
	}
	
	// Moderation
	function doModerate($action = '',$id = 0) {
		$output = NULL;
		$pObj = $this->provider;
		if ($this->isModerator && $pObj->isValidComment($this->config["docid"],$this->config["tagid"],$id)) {
			switch ($action) {
				case "delete":
					$pObj->Comment($id);
					$pObj->Delete();
					break;
				case "publish":
					$pObj->Comment($id);
					$pObj->Set("publishedon",$this->_ctime);
					$pObj->Set("publishedby",$this->config["user"]["id"]);
					$pObj->Set("published",1);
					$pObj->Save();
					if ($this->config["subscription"]["enabled"]) $this->doNotify($id,"notify");
					break;
				case "edit":
					$this->form["moderation"] = 1;
					$this->form["edit"] = 1;
					break;		
				case "save":
					$this->form["moderation"] = 1;
					$this->form["edit"] = 1;
					$this->form["save"] = 1;
					break;							
				case "unpublish":
					$pObj->Comment($id);
					$pObj->Set("publishedon",$this->_ctime);
					$pObj->Set("publishedby",$this->config["user"]["id"]);
					$pObj->Set("published",0);
					$pObj->Save();
					break;
				}
			
		}
		unset($pObj);
		return $output;
	}

	// Templating
	function getChunkRowClass($count,$userid) {
		$rowstyle = ($count%2) ? "jot-row-alt" : "";
		if ( $this->config["user"]["id"] == $userid && ($userid != 0)) {
			$rowstyle .= " jot-row-me";
		} elseif ( $this->config["authorid"] == $userid && ($userid != 0) ) {
			$rowstyle .= " jot-row-author";
		} 
		return $rowstyle;
	}
	
	// Validate a field
	function validateFormField($name = '', $value = '') {
		$returnValue = array(1,"");
		$validateFields = $this->config["form"]["validation"];
		
		//onBeforeValidateFormField event
		if (null !== ($output = $this->doEvent("onBeforeValidateFormField",array("name"=>$name,"value"=>$value)))) return $output;
		
		// Validation Exists?
		if (!array_key_exists($name, $validateFields))
			return $returnValue;
			
		// Load field validation array
		$validations = $validateFields[$name];
		
		// Loop validation array
		foreach($validations as $validation) {
			switch ($validation["validation"]) {
				// email validation
				case "email": $re = "~^(?:[a-z0-9_-]+?\.)*?[a-z0-9_-]+?@(?:[a-z0-9_-]+?\.)*?[a-z0-9_-]+?\.[a-z0-9]{2,5}$~i"; break;
				// simple required field validation
				case "required": $re = "~.+~s";break;
				// simple number validation
				case "number": $re = "~^\d+$~";break;
				// custom regexp pattern
				default: $re = $validation["validation"]; break;
			}
			
			// if not a match return error msg
			if (!preg_match($re,$value)) {
				//onValidateFormFieldFail event
				if (null !== ($output = $this->doEvent("onValidateFormFieldFail",array("name"=>$name,"value"=>$value,"validation"=>$validation)))) return $output;
				return array(0,$validation["msg"]);
			}
		}
		return $returnValue;					
	}
		
	// Validates and returns a special sort string so the data provider can handle this.
	function validateSortString($strSort = '') {
		$z = array();
		$xObj = $this->provider;
		$xObj->Comment();
		$y = explode(",",$strSort); // suggested sort fields
		$x = $xObj->getFields(); // actual available sort fields
		$x2 = $this->config["customfields"]; // actual available custom sort fields
		unset($xObj);
		
		// for each suggested sort
		foreach ($y as $i) {
			$i = trim($i);
			if(strlen($i)>2) {
				// get direction
				$dir = substr($i, -2);
				// get fieldname
				$name = substr($i,0,(strlen($i)-2));
				// if this is a custom field prefix with '#' so data provider can detect it.
				if (in_array($name, $x2)) { $z[] = "#".$name.$dir; }
				// if normal field
				elseif (array_key_exists($name, $x)) { $z[] = $name.$dir; }
			}
		}
		return implode(",",$z);
	}
	
	
	// Returns an array containing webusers which are a member of the specified group(s).
	function getMembersOfWebGroup($groupNames=array()) {
		global $modx;
		$usrIDs = array();
		$tbl = $modx->getFullTableName("webgroup_names");
		$tbl2 = $modx->getFullTableName("web_groups");
		$sql = "SELECT distinct wg.webuser
						FROM $tbl wgn
						INNER JOIN $tbl2 wg ON wg.webgroup=wgn.id AND wgn.name IN ('" . implode("','",$groupNames) . "')";
		$usrRows = $modx->db->getColumn("webuser", $sql);
		foreach ($usrRows as $v) $usrIDs[] = -intval($v);
		return $usrIDs;
	}	
	
	// MODx UserInfo enhanced
	function getUserInfo($userid = 0,$field = NULL) {
		global $modx;
		
		//onBeforeGetUserInfo event
		if (null !== ($output = $this->doEvent("onBeforeGetUserInfo",array("userid"=>$userid,"field"=>$field)))) return $output;
		
		if (intval($userid) < 0) {
			$user = $modx->getWebUserInfo(-($userid));
		} else {
			$user = $modx->getUserInfo($userid);
		}
		if ($field) {	return $user[$field]; }
		return $user;
	}	
	
	// MODx makeUrl enhanced: preserves querystring.
	function preserveUrl($docid = '', $alias = '', $array_values = array(), $suffix = false) {
		global $modx;
		$array_get = $_GET;
		$urlstring = array();
		
		unset($array_get["id"]);
		unset($array_get["q"]);
		
		$array_url = array_merge($array_get, $array_values);
		foreach ($array_url as $name => $value) {
			if (!is_null($value)) {
			  $urlstring[] = $name . '=' . urlencode($value);
			}
		}
		
		$url = join('&',$urlstring);
		if ($suffix) {
			if (empty($url)) { $url = "?"; }
			 else { $url .= "&"; }
		}

		return $modx->makeUrl($docid, $alias, $url);
	}
	
	// invoke events
	function doEvent($event,$params=array()) {
		global $modx;
		$this->event = $event;
		$event = $this->Get($event);
		if (!$event) return null;
		$plugins=explode(',',$event);
		$object = & $this;
		$result = null;
		foreach ($plugins as $plugin) {
			if(function_exists($plugin)) {
				if (null !== ($output = $plugin($object,$params))) $result = $output;
			} else {
				$pluginPath = $this->config["path"].'plugins/' . $plugin . '.inc.php';
				if(is_file($pluginPath)) {
					include $pluginPath;
					if(function_exists($plugin)) {
						if (null !== ($output = $plugin($object,$params))) $result = $output;
					}
				}
			}
		}
		$this->event = '';
		return $result;
	}
	
	function processDocs($docids) {
		global $modx;
		$idarray = array();
		if ($docids != '*') $values = explode(',',$docids);
		else return $docids;
		
		/* parse values, and check for invalid entries */
		foreach ($values as $value) {
			/* value is a range */
			if (preg_match('/^[\d]+\-[\d]+$/', trim($value))) {
				$match = explode('-', $value);
				$loop = $match[1] - $match[0];
				for ($i = 0; $i <= $loop; $i++) {
					$idarray[] = $i + intval($match[0]);
				}
			}
			/* value is a group for immediate children */
			elseif (preg_match('/^[\d]+\*$/', trim($value), $match)) {
				$match = rtrim($match[0], '*');
				$idarray[] = intval($match);
				$children = $modx->getChildIds($match,1);
				foreach ($children as $v) $idarray[] =  intval($v);
			}
			/* value is a group for ALL children */
			elseif (preg_match('/^[\d]+\*\*$/', trim($value), $match)) {
				$match = rtrim($match[0], '**');
				$idarray[] = intval($match);
				$children = $modx->getChildIds($match);
				foreach ($children as $v) $idarray[] =  intval($v);
			}
			/* value is a single document */
			elseif (preg_match('/^[\d]+$/', trim($value), $match)) {
				$idarray[] = intval($match[0]);
			}
		}
		if (empty($idarray)) return $modx->documentIdentifier;
		return $idarray;
	}
	
	function processTags($tagids) {
		global $modx;
		$idarray = array();
		if ($tagids != '*') $values = explode(',',$tagids);
		else return $tagids;
		
		foreach ($values as $value) {
			$value = preg_replace("/[^A-z0-9_\-]/",'',$value);
			if (!empty($value)) $idarray[] = $value;
		}
		
		if (empty($idarray)) return '';
		return $idarray;
	}
}
?>