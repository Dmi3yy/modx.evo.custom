<?php

$theme           = 'editor';
$custom_plugins  = 'inlinepopups,autosave,save,advlist,style,fullscreen,advimage,paste,advlink,media,contextmenu,table';
$custom_buttons1 = 'undo,redo,|,bold,forecolor,backcolor,strikethrough,formatselect,fontsizeselect,pastetext,pasteword,code,|,fullscreen,help';
$custom_buttons2 = 'image,media,link,unlink,anchor,|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,blockquote,outdent,indent,|,table,hr,|,styleprops,removeformat';
$css_selectors   = '左寄せ=justifyleft;右寄せ=justifyright';
$mce_editor_skin = 'default';

$params['theme']       = (empty($params['theme']))           ? $theme : $params['theme'];
$ph['custom_plugins']  = (empty($params['custom_plugins']))  ? $custom_plugins  : $params['custom_plugins'];
$ph['custom_buttons1'] = (empty($params['custom_buttons1'])) ? $custom_buttons1 : $params['custom_buttons1'];
$ph['custom_buttons2'] = (empty($params['custom_buttons2'])) ? $custom_buttons2 : $params['custom_buttons2'];
$ph['css_selectors']   = (is_null($params['css_selectors'])) ? $css_selectors   : $params['css_selectors'];
$ph['custom_buttons3'] = $params['custom_buttons3'];
$ph['custom_buttons4'] = $params['custom_buttons4'];
$ph['mce_entermode'] = (!isset($params['mce_entermode'])) ? 'p' : $params['mce_entermode'];
