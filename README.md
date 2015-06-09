Сборка обзавеласть собственным сайтом: http://modx.com.ua
Так же рекоменую скачивать сборку с сайта так как на гитхаб не всегда стабильный релиз!!!

текущая версия 1.1b-d7.1.1  (09.06.2015)
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