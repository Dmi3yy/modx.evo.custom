/**
 * Protect
 *
 * плагин для защиты страниц от неавторизованных пользователей
 *
 * @category	plugin
 * @version	1.01 
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnWebPageInit
 * @internal    @disabled 1
 */
/*
*
* Protect плагин для защиты страниц от неавторизованных пользователей
* version 1.01
* Author: Andrew Vakulenko (ShCoder)
* Copyright: 2012 Andrew Vakulenko
* email: sitemart@gmail.com
* events OnWebPageInit
*
* add id of Protected Page:
* &protectedID=set protected page ID;text;
*
*/

if(empty($protectedID)) return;

$e = $modx->event;

if ($e->name == 'OnWebPageInit') {
	$currentID = $modx->documentIdentifier;
	$errorPage = $modx->getConfig('unauthorized_page');
	$errorPage = $modx->makeUrl($errorPage);

	$parents = $modx->getParentIds($currentID);
	if(in_array($protectedID, $parents) || ($currentID == $protectedID)) {
		if(!isset($_SESSION['mgrValidated'])) {
			$modx->sendRedirect($errorPage);
		}
	}
}
