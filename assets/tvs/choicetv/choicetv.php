<?php
/**
 * @copyright 2013 Dmi3yy
 * @version 0.2
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Dmi3yy <dmi3yy@gmail.com>
 * @link http://dmi3yy.com author home page
 */
$sql = 'SELECT DISTINCT `value` FROM '.$modx->getFullTableName('site_tmplvar_contentvalues').' WHERE tmplvarid = '.$field_id;
$result = $modx->db->query( $sql );  

while( $row = $modx->db->getRow( $result ) ) {  
    $selected = ($row['value']==$field_value) ?  'selected="selected"' : '';
    $output .= "<option value='".$row['value']."' ".$selected.">".$row['value']."</option>";  
}  
echo '
<script type="text/javascript">
function inpChange'.$field_id.'(obj){
    var el'.$field_id.', s'.$field_id.', n'.$field_id.', v'.$field_id.';
    el'.$field_id.'=obj.options;
    n'.$field_id.'=el'.$field_id.'.selectedIndex;
    v'.$field_id.'=el'.$field_id.'[n'.$field_id.'].value;
    s'.$field_id.'="tv'.$field_id.'";
    if(v'.$field_id.'=="???"){
        if(document.getElementById(s'.$field_id.')){
            document.getElementById(s'.$field_id.').style.visibility="visible";
        }
    }else{
        if(document.getElementById(s'.$field_id.')){
            document.getElementById(s'.$field_id.').value=v'.$field_id.';
        }
    };
};
</script>
<style type="text/css">
    #tv'.$field_id.' {visibility:hidden;}
</style>
    <select name="select'.$field_id.'" id="select'.$field_id.'" style="width: 278px;" onchange="inpChange'.$field_id.'(this);">
        <option value="">Выбрать вариант</option>
        '.$output.'
        <option value="???">Добавить вариант</option>
    </select>
    <input class="choicetv" type="text" name="tv'.$field_id.'" id="tv'.$field_id.'" value="'.$field_value.'" />
';
?>