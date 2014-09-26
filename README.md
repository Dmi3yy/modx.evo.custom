Сборка обзавеласть собственным сайтом: http://modx.com.ua
Так же рекоменую скачивать сборку с сайта так как на гитхаб не всегда стабильный релиз!!!

!!Ченжлог не содержит те правки которые не отличаются с офф версией а так же основной ветки моего форка

текущая версия 1.1b-d7.0.17  (25.09.2014)
=======================================================
- add ssl setting for SMTP
- fix CodeMirror local history conflict
- add tinyMCE theme Circuit


Отличия от Master branch
=======================================================
- FRAMSET -> iframe (need restore modx_textdir) now work tree only left (thanks Mihanik71, 64j)
- Move Folder frame to Theme forder (now can do any change and not take core files)
- add new default theme D3X
- refactor Search - group 5 fields in one
- refactor topMenu - now easy change (thanks Bumkaka)
- AliasListing only for Folders - on/off in settings(Friendly URL tab)
- add custom tv in Select (not need use @INCLUDE... only select from list)
- events: OnDocFormTemplateRender, OnFileBrowserUpload
- api: $modx->getTpl
- add for mm moveCategoryToTab