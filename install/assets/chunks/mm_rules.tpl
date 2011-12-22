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

// example of how PHP is allowed - check that a TV named documentTags exists before creating rule
if($modx->db->getValue("SELECT COUNT(id) FROM " . $modx->getFullTableName('site_tmplvars') . " WHERE name='keyw'")) {
    mm_widget_tags('keyw',' '); // Give blog tag editing capabilities to the 'documentTags (3)' TV
}

mm_widget_showimagetvs(); // Always give a preview of Image TVs

mm_renameField('log', 'Дочерние ресурсы отображаются в дереве');
mm_changeFieldHelp('log', 'Это поле используется для папок с большим числом вложенных страниц');

mm_createTab('Для SEO', 'seo', '', '', '', '');
mm_moveFieldsToTab('titl,keyw,desc,seoOverride', 'seo', '', '');

//mm_createTab('Изображения', 'photos', '', '', '', '850');
//mm_moveFieldsToTab('images,photos', 'photos', '', '');

//mm_hideFields('longtitle,description,link_attributes,menutitle,content', '', '6,7');

//mm_hideTemplates('0,5,8,9,11,12', '2,3');

//mm_hideTabs('settings, access', '2');
