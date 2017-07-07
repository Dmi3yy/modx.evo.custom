Сайт сборки: http://modx.com.ua

Актуальная версия: https://github.com/dmi3yy/modx.evo.custom/releases
1.2.1-d9.1.8 (07.07.2017)
=======================================================
- update MODxRE2_dropdown theme, refactor html form on elements and others pages
- update FormLister
- update Doclister
- fix errors for php 7.2 
- first step to composer
- fix pdf ico
- fix smtp (now normal work in port != 25)
- fix snippet IF (brocken in 9.1.6 now work &else)
- add for codemirror (dark-one theme, auto on in dark-mode), now you can set шт settings font size
- refactor action buttons
- fix OnManagerNodePrerender, OnManagerWelcomeHome, OnManagerMenuPrerender
- fix. .pdf.html to .pdf in URL
- add parent and menuindex in open document from drag
- update Datapicker 
- [F] when modx installed in folder, request of home page with parameters causes endless redirect
- KCFinder thumb generation fixed on some php7 systems
- [Fix] #1377 $modx->getDocumentChildrenTVars()
- [F]  check for 'register_globals' fixed #1370


1.2.1-d9.1.7 (21.06.2017)
=======================================================
- Add search in 3 level menu (http://take.ms/nQaUK)
- remove mutools from login page. 
- fix for iOS (now normail work)
- fix some nitices for php 7.2 
- fix errors founded in 1.2.1-d9.1.7(bugs in Firefox, bugs with size tree, bug with keepalive and more)


1.2.1-d9.1.6  (18.06.2017)
=======================================================
- refactor MODxRE_dropdownmenu theme.  x4 faster works (http://take.ms/Wwgbq)
- refactor wait.static - now save documents, setting faster more then 1sec (only in new theme)
- remember url for work in manager now with reload not redirect to welcom page (only in new theme)
- Dark mode for new theme, swith by button in tree  (http://take.ms/99K0D)
- new Dashboard, now all change with plugin, and no more style in code inside plugin (https://gist.github.com/dmi3yy/d065729c3c37eb3f17eab247ae1dd10d) (only in new theme)
- delete alterTitle (Preparations for merging into Evolution CMS)
- delete .htaccess (autocreate if not, when save settings) (Preparations for merging into Evolution CMS)
- rename robots.txt to sample_robots.txt (Preparations for merging into Evolution CMS)
- rename some fields into ENG (Preparations for merging into Evolution CMS)
- contextMenu for elements in Codemirror (http://take.ms/opW8t) (only in new theme)
- Drag&Drop documents in Tree (only in new theme)
- fix for normal work: [*placeholder[(system_variabnle)]*]
- DropDown 3 Level in Top menu (http://take.ms/Dm9qZ)
- [F] fix save assignedTv in Template
- KcFinder. png background fix
- Update TinyMce to 4.6.3
- phpthumb: fix default quality of image to 85 for best in googlepagespeed
- update Doclister (редирект с doc.html?page=1 на doc.html)



1.2.1-d9.1.5  (22.05.2017)
=======================================================
- refactor onManagerNodePrerender (now can full castomize tree, only for MODxRE_dropdownMenu)
- update IF 
- update DocLister to 2.3.1
- update DLBuildMenu to 1.2
- alias_visible fix - select children of hidden parents when building cache
- Fix install check db version
- update tinyMCE to 4.6.1



1.2.1-d9.1.4  (02.05.2017)
=======================================================
- MODxRE2 dropdownMenu , обновленна все иконки FA, убраны 2 фрейма с дерева и меню
- fix OnParseProperties event #1325
- fix Ditto + Cross references = not working
- fix Broken parser logic
- fix ddselectdocuments with jquery 3.1
- Managermanager work with tinymce4 (use richtext from modx settings)


1.2.1-d9.1.3  (18.04.2017)
=======================================================
- добавленна новая тема MODxRE2 dropdownMenu (пока не по умолчанию)
- ajax поиск по админке в том числе и по элементам (в теме MODxRE2 dropdownMenu)
- Обновлен DocLister
- Обновлен FormLister 
- исправлен баг с https, теперь в site_url пишется коректно с https
- исправлена ошибка с параметрами из модуля в плагин
- .json добавлен в исключения для alias
- Fix siblings templates
- Refactor for DLBuildMenu. Less mysql queries
- Fix admin role for access to category manager
- fix for aliaslistingfolder no need check for docAlias for search id

1.2.1-d9.1.2  (21.03.2017)
=======================================================
- delete (Wayfinder) use DLBuildMenu
- delete (Breadcrumbs) use DLcrumbs
- delete (Ditto) use DocLister
- delete (eForm) use FormLister (for SHK http://modx.im/blog/questions/4888.html#comment40634)
- Fix empty params on install (assets/)
- H1 - H3 in tinyMCE4 by default
- fix Cattegory for access
- 9.1.1 (all from off Evolution)


1.2.1-d9.1.0  (11.01.2017)
=======================================================
- security fix in phpMailer
- bug fix in tv options with label 
- fix Extras module for work with php7


1.2-d8.1.6  (29.11.2016)
=======================================================
- security fix 
- sefeEval in Ditto and if 
- update DocLister to 2.3.0
- Мелкие исправления и рефактор кода


1.2-d8.1.5  (28.10.2016)
=======================================================
- add plugin ElementsInTree (turnof by default)
- restyle instal and modules. 
- restyle resurces 
- real fix setting for Plugins, Modules
- fix Extras now work on HTTPS 
- delete all themes, exept MODxRE2


1.1-d8.1.3  (04.10.2016)
=======================================================
- new WeclomePage
- fix OnWebPagePrerender for normal work with Angular in modx
- update sample site
- fix setting for Plugins, Modules
- add class for image in tinyMCE4 



1.1-d8.0  (11.04.2016)
=======================================================
- update from modx evo all feachres
- Wayfinder hideSubMenus still applies parentClass to empty menus #345
- Force default template´s selectable = 1
- Avoid trim spaces from Chunks (allows clean indented HTML)
- jQuery into core
- Fix sort by category
- update tinyMCE
- update Doclister from github
- https://github.com/modxcms/evolution/pull/583


1.1RC-d7.1.6  (25.02.2016)
=======================================================
- rename folders libs to lib 
- add DocLister 
- fix error if no folders and turn on AliasListing for Folders
- fix phpthumb for image with ext jpeg
- turn on TinyMCE4 after install 
- scrollWork: console.log deleted and fix bug 
- добавлен Укрианский язык для админки
- исправлена ошибка с системными плейсхолдерами(была в версии 7.1.5 затянул с оф ветки)
- добавленна поддержка темы MODxPress (можно экшены теперь переносить в папку темы и менять под себя)
- исправлен язык в tinyMCE теперь подхватывает нужный
- исправленно Tiny MCE Emmet - конфликт с HTML5 тегами header и footer
- Fix render properties menu


1.1RC-d7.1.5  (09.01.2016)
=======================================================
- TinyMCE4 (нужно вынести настройки в настройки в остальном все ок)
- KCFinder 2.54 + поддержка работы на мобаил
- добавил в DocManager обработку @EVAL
- fix Капча 2 eForm #194


1.1b-d7.1.4  (29.12.2015)
=======================================================
- Поддержка PHP7
- Обновление install с mysql до  mysqli (для php7)
- eForm валидный html для  input  с типом file и image 
- Обновление robots.txt
- Замена __autoload на spl_autoload_register
- fix рендер параметров для типа list-multi
- изменение функции rewriteUrls по ошибке (для php7) 
- ctrl + S, cmd + S теперь работают в админке для сохранения 


1.1b-d7.1.3  (02.11.2015)
=======================================================
- используем Mysqli по умолчанию (для новой установки, при обновлении меняем в файле config.inc.php руками)
- mainIframe remember scroll position after Save
- update CodeMirror main script to 5.6 (Now work on iOS)  
- fix login.tpl for mobile
- fix windows width on mobile


1.1b-d7.1.2  (06.07.2015)
=======================================================
- Breadcrumbs 1.0.5 (добавил параметр ignoreAliasVisible)
- Ditto fix &start=0 
- fix lib resourse.php (fix edit document)
- codemirror add in settings indentWithTabs
- revert getTagsFromContent (fix for [[snippet]]> )


1.1b-d7.1.1  (09.06.2015)
=======================================================
- Исправление ошибок версии 1.1b-d7.1 (Некорректная обработка результатов плагинов, Class 'SMTP' not found, Refactor - $modx->getTagsFromContent() Speed up!!!,)
- eForm добавил проверку номера телефона
- Откат PHPMailer с девелоперской до стабильной версии 5.2.9
- Добавление события OnDocFormUnDelete
- Фикс от долгой загрузки огромных веток в админке, в дереве документов
- add mm_minimizablesections (http://modx.im//blog/addons/3429.html)


1.1b-d7.1  (08.03.2015)
=======================================================
- В $modx->sendmail добавил возможность прикрепления файлов
- Обновил PHPMailer класс до последней версии
- Исправил баг с путем к кастомному login.tpl шаблону
- Исправил некорректное определение типа юзера (веб или манагер) в методе $modx->logEvent
- Поправил верстку в табах на странице редактирования документа
- Убрал лишние переносы строк и пробелы в основном кеш-файле
- Добавил дозагрузку несуществующих чанков из базы
- Исправил загрузку параметров по умолчанию у сниппета
- Обернул класс skynccache в проверку class_exists, чтобы можно было подменить кешер
- Добавил новое событие OnMakePageCacheKey
- Переформатировал страницу с информацией об ошибке
- Добавил поддержку параметров в Backtrace стеке на странице с ошибкой
- Добавил новое событие OnParseProperties в котором можно модифицировать параметры плагина/сниппета перед выполнением
- Добавил новые события OnBeforeLoadDocumentObject, OnAfterLoadDocumentObject
- В событие OnLoadDocumentObject теперь передаются параметры method, identifier и documentObject (подробнее: https://github.com/dmi3yy/modx.evo.custom/pull/210)
- В метод sendErrorPage теперь добавлен параметр для игнорирования вызова события onPageNotFound, чтобы с плагином кастомной маршрутизации не приходилось куралесить и сайт не уходил в рекурсию.
- Добавил новый метод checkSQLconnect для проверки соединения с базой
- Добавлено новое событие onBeforeLoadExtension для регистрации новых экстендеров
- Исправил ошибку с созданием нового модуля (параметры по умолчанию перезаписывают guid модуля)
- Исправил косяк с загрузкой индивидуального конфига к phpmailer из экстендера MODxMailer
- Исправил ошибку в DBAPI::update() с установкой значения NULL

1.1b-d7.0.17  (25.09.2014)
=======================================================
- add ssl setting for SMTP
- fix CodeMirror local history conflict
- add tinyMCE theme Circuit


Отличия от Ветки 1.0.X
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
