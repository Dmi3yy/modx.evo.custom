/**
 * EasyAdvertising
 *
 * плагин для управления рекламой на сайте
 *
 * @category	plugin
 * @version	1.02 
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnParseDocument
 * @internal    @disabled 1
 */
/*
*  Easy Advertising -                      
*  плагин для управления рекламой на сайте 
*  Версия 1.02
*  Автор Леха.com
*  mod. lo-pata (s.vlksm@gmail.com)  
*  вешать на событие OnParseDocument
*/
if (count($modx->ui)) {
    $sql = "UPDATE ".$modx->getFullTableName("site_easyadvt")." SET count_view = count_view + 1 WHERE id IN (".implode(',',$modx->ui).")";
    $modx->db->query($sql);
    $modx->ui = array();
}