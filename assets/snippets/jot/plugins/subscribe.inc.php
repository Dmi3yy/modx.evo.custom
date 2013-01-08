<?php
function subscribe(&$object,$params){
	global $modx;
	
	$tbl = $modx->db->config["table_prefix"]."jot_subscriptions_guest";
	switch($object->event) {
		case "onBeforeFirstRun":
			/* создание таблицы если не создана */
			$query = $modx->db->query("SHOW TABLES LIKE '".$tbl."'");
			$returnValue = $modx->db->getRecordCount($query);
			if ($returnValue==0) {
				$sql = "CREATE TABLE IF NOT EXISTS $tbl (
					`id` mediumint(10) NOT NULL auto_increment,
					`uparent` mediumint(10) NOT NULL,
					`tagid` varchar(50) NOT NULL,
					`parent` int(10) NOT NULL default '0',
					`username` varchar(255) NOT NULL DEFAULT '',
					`email` varchar(255) NOT NULL DEFAULT '',
					`hash` varchar(255) NOT NULL DEFAULT '',
					PRIMARY KEY  (`id`),
					KEY `uparent` (`uparent`),
					KEY `tagid` (`tagid`),
					KEY `parent` (`parent`),
					KEY `email` (`email`),
					KEY `hash` (`hash`)
					) ENGINE=MyISAM;";
				$modx->db->query($sql);
			}
			break;
		case "onSaveComment":
			/* подписаться */
			if (intval($_POST["subscribe"]) == 1 && isset($object->cfields["email"])) {
				$email = strtolower(trim($modx->db->escape($object->cfields["email"])));
				$username = trim($modx->db->escape($object->cfields["name"]));
				$hash = md5($object->fields["uparent"]."&".$object->fields["tagid"]."&".$email);
				$sql = 'SELECT count(id) FROM '.$tbl.' WHERE uparent = "'.$object->fields["uparent"].'" AND tagid = "'.$object->fields["tagid"].'" AND parent = "'.$object->fields["parent"].'" AND email = "'.$email.'"';
				$returnValue = intval($modx->db->getValue($sql));
				if ($returnValue<1) {
					$modx->db->insert(array("uparent"=>$object->fields["uparent"],"tagid"=>$object->fields["tagid"],"parent"=>$object->fields["parent"],"username"=>$username,"email"=>$email,"hash"=>$hash),$tbl);
					setcookie('jot-hash', $hash, time() + 30000000, MODX_BASE_URL);
				}
			}
			break;
		case "onBeforeRunActions":
			/* включить рассылку */
			$object->config["subscription"]["enabled"] = 1;
			break;
		case "onBeforeProcessPassiveActions":
			/* выключить рассылку */
			if (!$object->config["subscribe"]) $object->config["subscription"]["enabled"] = 0;
			/* чекбокс */
			$hash = isset($_COOKIE['jot-hash']) ? $_COOKIE['jot-hash'] : '';
			$object->form["subscribed"] = (intval($_POST["subscribe"]) == 1) ? 1 : 0;
			if ($object->config["user"]["id"] == 0 && !empty($hash)) {
				$sql = 'SELECT count(id) FROM '.$tbl.' WHERE hash = "'.$modx->db->escape($hash).'"';
				$returnValue = intval($modx->db->getValue($sql));
				if ($returnValue > 0) $object->form["subscribed"] = 1;
			}
			/* отписаться */
			if ($object->config["mode"]["active"] == "unsubscribe" && !empty($_GET["hash"])) {
				$hash = $modx->db->escape($_GET["hash"]);
				$query = $modx->db->delete($tbl,'hash="'.$hash.'"');
				if ($query) {
					setcookie('jot-hash', '', time() - 3600, MODX_BASE_URL);
					$object->form["confirm"] = 4;
					$object->form["subscribed"] = 0;
				}
			}
			break;
		case "onGetSubscriptions":
			/* получить подписчиков */
			$subscriptions = $params["subscriptions"];
			$guests = array();
			$query = $modx->db->query("select id,username,email,hash from $tbl where id>0 ". $object->sqlPart($params["docid"],$params["tagid"]));	
			while ($row = $modx->db->getRow($query)) {
				$guests["guest".$row["id"]] = $row;
				$subscriptions[] = "guest".$row["id"];
			}
			$object->guests = $guests;
			return $subscriptions;
			break;
		case "onBeforeGetUserInfo":
			/* получить данные подписчика */
			if (strpos((string)$params["userid"],"guest") !== false) return $object->provider->guests[$params['userid']];
			break;
		case "onBeforeNotify":
			/* не отправлять свой комментарий */
			$hash = isset($params["user"]["hash"]) ? $params["user"]["hash"] : '';
			if (intval($_POST["subscribe"]) == 1 || (isset($_COOKIE['jot-hash']) && $_COOKIE['jot-hash'] == $hash) ) return true;
			/* добавить хэш в шаблон */
			if ($hash) $params["tpl"]->template = str_replace('[+jot.link.unsubscribe+]','[+jot.link.unsubscribe+]&hash='.$hash,$params["tpl"]->template);
			elseif (!$object->config["subscribe"] && $params["action"]=="notify") return true;
			break;
	}
}
?>