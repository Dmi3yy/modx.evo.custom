<?php

/*
 * modifier shk_widget for PHx
 * by Andchir
 */

if(!isset($options)) $options = '';
if(!isset($output)) $output = '';

$shk_options = $options ? explode(':',$options) : array('select','none'); //select | radio | checkbox | radioimage
$shk_value = $output;
$shk_format = $shk_options[0];
$tvname = isset($shk_options[1]) ? $shk_options[1] : 'none';

$is_catalog = in_array('desc_page',$shk_options)==false;
$docid = $is_catalog ? '[+id+]' : '[*id*]';
$first_selected = in_array('first_selected',$shk_options);
$wraptag = in_array('wraptag',$shk_options) ? 'div' : '';
$function = 'jQuery.additOpt(this)';

$shk_output = '';

switch($shk_format){

    case "select":
        $options = "";
        $cnt = 0;
        $value = !empty($shk_value) ? explode("||",$shk_value) : array();
        if(count($value)>0){
            foreach($value as $val){
                list($item,$itemvalue) = explode("==",$val);
                $selected = $cnt==0 && $first_selected ? ' selected="selected"' : '';
                $options .= "\n\t".'<option value="'.$cnt.'__'.$itemvalue.'"'.$selected.'>'.$item.'</option>';
                $cnt++;
            }
            $shk_output .= "\n".'<select class="addparam" name="'.$tvname.'__'.$docid.'" onchange="'.$function.'">'.$options."\n".'</select>'."\n";
        }
    break;

    case "radio":
        $otag = $wraptag ? "<$wraptag>" : "";
        $ctag = $wraptag ? "</$wraptag>" : "";
        $value = !empty($shk_value) ? explode("||",$shk_value) : array();
        $cnt = 0;
        foreach($value as $val){
            list($item,$itemvalue) = explode("==",$val);
            $selected = $cnt==0 && $first_selected ? ' checked="checked"' : '';
            $shk_output .= "\n".$otag.'<input class="addparam" type="radio" name="'.$tvname.'__'.$docid.'" value="'.$cnt.'__'.$itemvalue.'" id="'.$tvname.$docid.$cnt.'"'.$selected.' onclick="'.$function.'" /> <label for="'.$tvname.$docid.$cnt.'">'.$item.'</label>'.$ctag;
            $cnt++;
        }
    break;

    case "checkbox":
        $otag = $wraptag ? "<$wraptag>" : "";
        $ctag = $wraptag ? "</$wraptag>" : "";
        $value = !empty($shk_value) ? explode("||",$shk_value) : array();
        $cnt = 0;
        foreach($value as $val){
            list($item,$itemvalue) = explode("==",$val);
            $selected = $cnt==0 && $first_selected ? ' checked="checked"' : '';
            $shk_output .= "\n".$otag.'<input class="addparam" type="checkbox" name="'.$tvname.'__'.$docid.'__'.$cnt.'" value="'.$cnt.'__'.$itemvalue.'" id="'.$tvname.$docid.$cnt.'"'.$selected.' onclick="'.$function.'" /> <label for="'.$tvname.$docid.$cnt.'">'.$item.'</label>'.$ctag;
            $cnt++;
        }
    break;

    case "radioimage":
        $tbl_content = $is_catalog ? $modx->getFullTableName('catalog') : $modx->getFullTableName('site_content');
        $tbl_tmplvar_contentvalues = $is_catalog ? $modx->getFullTableName('catalog_tmplvar_contentvalues') : $modx->getFullTableName('site_tmplvar_contentvalues');
        $otag = $wraptag ? "<$wraptag>" : "";
        $ctag = $wraptag ? "</$wraptag>" : "";
        $tvc_id = str_replace('||',',',$shk_value);
        if(!empty($tvc_id)){
            $res1 = $modx->db->select("cnt.pagetitle, tvc.value", $tbl_content." cnt, ".$tbl_tmplvar_contentvalues." tvc", "tvc.id IN ($tvc_id) AND tvc.contentid = cnt.id");
            $cnt = 0;
            while($tvImg = $modx->db->getRow($res1)){
                $selected = $cnt==0 && $first_selected ? ' checked="checked"' : '';
                list($name,$src) = array($tvImg['pagetitle'],$tvImg['value']);
                $shk_output .= "\n  ".$otag.'<input class="addparam" type="radio" name="'.$tvname.'__'.$docid.'" value="'.$cnt.'__0__'.$name.'" id="'.$tvname.$docid.$cnt.'"'.$selected.' onclick="'.$function.'" />';
                $shk_output .= '<label for="'.$tvname.$docid.$cnt.'" title="'.$name.'"><img src="'.$src.'" alt="'.$name.'" /></label>'.$ctag;
                $cnt++;
            }
        }
    break;

}

return $shk_output;

?>