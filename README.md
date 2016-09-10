Сборка обзавеласть собственным сайтом: http://modx.com.ua
Так же рекоменую скачивать сборку с сайта так как на гитхаб не всегда стабильный релиз!!!

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