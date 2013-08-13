//<?php
/**
 * TinyMCE Rich Text Editor
 * 
 * Javascript WYSIWYG Editor
 *
 * @category 	plugin
 * @version 	3.5.8
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &customparams=Custom Parameters;textarea; &mce_formats=Block Formats;text;p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre &entity_encoding=Entity Encoding;list;named,numeric,raw;named &entities=Entities;text; &mce_path_options=Path Options;list;rootrelative,docrelative,fullpathurl;docrelative &mce_resizing=Advanced Resizing;list;true,false;true &disabledButtons=Disabled Buttons;text; &link_list=Link List;list;enabled,disabled;enabled &webtheme=Web Theme;list;simple,editor,creative,custom;simple &webPlugins=Web Plugins;text;style,advimage,advlink,searchreplace,contextmenu,paste,fullscreen,nonbreaking,xhtmlxtras,visualchars,media,youtubeIframe &webButtons1=Web Buttons 1;text;undo,redo,selectall,|,pastetext,pasteword,|,search,replace,|,nonbreaking,hr,charmap,|,image,link,unlink,anchor,media,youtubeIframe,|,cleanup,removeformat,|,fullscreen,code,help &webButtons2=Web Buttons 2;text;bold,italic,underline,strikethrough,sub,sup,|,|,blockquote,bullist,numlist,outdent,indent,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,|,styleprops &webButtons3=Web Buttons 3;text; &webButtons4=Web Buttons 4;text; &webAlign=Web Toolbar Alignment;list;ltr,rtl;ltr &width=Width;text;100% &height=Height;text;400
 * @internal	@events OnRichTextEditorRegister,OnRichTextEditorInit,OnInterfaceSettingsRender 
 * @internal	@modx_category Manager and Admin
 * @internal    @legacy_names TinyMCE
 * @internal    @installset base, sample
 *
 * Written By Jeff Whitfield
 * and Mikko Lammi / updated: 12/01/2011
 * and yama  / updated: 05/19/2010
 */

require MODX_BASE_PATH.'assets/plugins/tinymce/plugin.tinymce.php';