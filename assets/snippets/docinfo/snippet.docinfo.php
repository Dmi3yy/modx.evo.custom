<?php
/**
 * DocInfo
 *
 * @category  parser
 * @version   0.2
 * @license     GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @param string $field Значение какого поля необходимо достать
 * @param int $docid ID документа
 * @param int $tv является ли поле TV параметром (0 - нет || 1 - да)
 * @param int $render Преобразовывать ли значение TV параметра в соответствии с его визуальным компонентом
 * @return string Значение поля документа или его TV параметра
 * @author akool, Agel_Nash <Agel_Nash@xaker.ru>
 *
 * @TODO перечислить в сниппете имена всех стандартных полей документа (Избавиться от параметра tv)
 * @TODO getTemplateVarOutput не применяет визуальный компонент к TV параметрам у которых значение совпадает со значением по умолчанию
 * 
 * @example
*       [[DocInfo? &docid=`15` &field=`pagetitle`]]
*       [[DocInfo? &docid=`10` &tv=`1` &field=`tvname`]]
*       [[DocInfo? &docid=`3` &tv=`1` &field=`tvname` &render=`1`]]
*/
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
$docid = (isset($docid) && (int)$docid>0) ? (int)$docid : $modx->documentIdentifier;
$field = (isset($field)) ? $field : 'pagetitle';
$render = ) ? $render : 0;
$output = '';
if(isset($tv) && 1==$tv){
        if(isset($render) && 1==$render){
                $tv = $modx->getTemplateVarOutput($field, $docid);
                $output = $tv[$field];
        }else{
           $tv = $modx->getTemplateVar($field,'*',$docid);
           $output = ($tv['value']!='') ? $tv['value'] : $tv['defaultText'];
   }
}else{
   $doc = $modx->getPageInfo($docid,'1',$field);
   $output = $doc[$field];
}
return $output;
?>