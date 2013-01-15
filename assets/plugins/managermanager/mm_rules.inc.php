<?php
//group comment_top
mm_widget_showimagetvs(); // Показываем превью ТВ
mm_renameField("log", "Дочерние ресурсы отображаются в дереве");
mm_changeFieldHelp("log", "Это поле используется для папок с большим числом вложенных страниц");
mm_createTab("Для SEO", "seo", "", "", "", "");
mm_moveFieldsToTab("titl,keyw,desc,seoOverride,noIndex", "seo", "", "");
mm_widget_tags("keyw",","); // Give blog tag editing capabilities to the "documentTags (3)" TV
//group comment_bottom
//mm_hideFields('menuindex,show_in_menu,menutitle,which_editor,is_folder,is_richtext,log,searchable,cacheable,clear_cache,parent,description,link_attributes,introtext,longtitle,pagetitle,alias,content,template', '1', '5');
?>
