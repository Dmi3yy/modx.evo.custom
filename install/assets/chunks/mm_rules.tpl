/**
 * mm_rules
 * 
 * Default ManagerManager rules.
 * 
 * @category	chunk
 * @version 	1.0.5
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal 	@modx_category Js
 * @internal    @overwrite false
 * @internal    @installset base, sample
 */

// more example rules are in assets/plugins/managermanager/example_mm_rules.inc.php

mm_widget_showimagetvs(); // Показываем превью ТВ

mm_renameField('log', 'Дочерние ресурсы отображаются в дереве');
mm_changeFieldHelp('log', 'Это поле используется для папок с большим числом вложенных страниц');

mm_createTab('Для SEO', 'seo', '', '', '', '');
mm_moveFieldsToTab('titl,keyw,desc,seoOverride', 'seo', '', '');
mm_widget_tags('keyw',','); // Give blog tag editing capabilities to the 'documentTags (3)' TV


//mm_createTab('Изображения', 'photos', '', '', '', '850');
//mm_moveFieldsToTab('images,photos', 'photos', '', '');

//mm_hideFields('longtitle,description,link_attributes,menutitle,content', '', '6,7');

//mm_hideTemplates('0,5,8,9,11,12', '2,3');

//mm_hideTabs('settings, access', '2');

//mm_widget_evogallery(1, Галерея, 1, 3);   // подключаем галерею 
//mm_galleryLink($fields, $roles, $templates, $moduleid);
//mm_widget_evogallery($moduleid, $title, $roles, $templates);