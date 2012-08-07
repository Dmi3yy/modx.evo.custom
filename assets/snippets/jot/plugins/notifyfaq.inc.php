<?php
function notifyfaq(&$object,$params){
	global $modx;
	
	$subject = "На ваш вопрос ответили";
	$template = "
Уважаемый(ая) [+recipient.username+], на ваш вопрос написан ответ:
-----
Вопрос: 
[+comment.title+]
[+comment.content+]

Ответ:
[+comment.custom.answer+]
-----

С уважением,
Администратор сайта
	";
	switch($object->event) {
		case "onProcessForm":
			if ($params["saveComment"] && !empty($params["pObj"]->cfields["answer"])) {
				$temp = $object->config["authorid"];
				$object->config["authorid"] = $params["pObj"]->Get("createdby");
				$object->doNotify($params["pObj"]->Get("id"),"notifyauthor");
				$object->config["authorid"] = $temp;
			}
			break;
		case "onBeforeNotify":
			if (!empty($params["comment"]["custom"]["answer"])) {
				$params["tpl"]->template = $template;
				$params["subject"] = $subject;
				if ($params["comment"]["createdby"]==0) {
					$params["user"]["username"] = $params["comment"]["custom"]["name"];
					$params["user"]["email"]  = $params["comment"]["custom"]["email"];
				}
			}
			break;
	}
}
?>