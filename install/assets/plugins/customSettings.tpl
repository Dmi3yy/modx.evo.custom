//<?php
/**
 * customSettings
 * 
 * Plugin to add your system settings
 *
 * @category    plugin
 * @version     1.0
 * @author      Andchir <andchir@gmaail.com>
 * @internal    @properties &settings=Settings;textarea;Example custom setting~custom_st_example
 * @internal    @events OnSiteSettingsRender
 * @internal    @modx_category
 * @internal    @installset base
 */

$e = &$modx->Event;
$output = "";

if ($e->name == 'OnSiteSettingsRender'){

$settingsArr = !empty($settings) ? explode('||',$settings) : array('Example custom setting~custom_st_example');

$output .= '<table>';

foreach($settingsArr as $key => $st_row){
    $st_label_arr = explode('~',$st_row);
    $custom_st_label = trim($st_label_arr[0]);
    $custom_st_name = isset($st_label_arr[1]) ? $st_label_arr[1] : 'custom_st';
    $custom_st_value = isset($st_label_arr[1]) && isset($modx->config[$st_label_arr[1]]) ? trim($modx->config[$st_label_arr[1]]) : '';
    $output .= '
      <tr>
        <td nowrap="nowrap" class="warning" width="200">'.$custom_st_label.'</td>
        <td><input type="text" value="'.$custom_st_value.'" name="'.$custom_st_name.'" style="width: 350px;" onchange="documentDirty=true;" /></td>
      </tr>
    ';
}

$output .= '</table>';

}

$e->output($output);