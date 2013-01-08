JotX - улучшенный и дополненный Jot
-----------------------------------

### Конфигурация из файла. 
Все параметры и шаблоны можно прописывать в одном файле.

```php
    [!JotX? &config=`faq` !] - Вопрос-Ответ
    [!JotX? &config=`tree` !] - Древовидные комментарии
    [!JotX? &config=`tree-ajax` !] - Древовидные комментарии с аякс
```

### Режимы вынесены в файлы.

```php
    [!JotX? &action=`lastcomments` !] - Последние комментарии
```

### Новые параметры.

* **notifyEmails** - подписка на определенные адреса
* **subjectEmails** - заголовок писем для этой рассылки
* **subscriber** - имя получателя для этой рассылки, если не указано (по умолчанию "подписчик")
* **tplNotifyEmails** - шаблон для этой рассылки

    ```php
    [!JotX? &notifyEmails=`user1@site.ru:Подписчик 1,user2@site.ru:Подписчик 2,user3@site.ru` !]

* **docids** - список docid, можно указывать диапазоны
* **tagids** - список tagid, через запятую
* **userids** - список id пользователей, через запятую. Для веб-пользователей - отрицательные.
* **limit** - ограничение количества комментариев

    ```php
    [!JotX? &action=`lastcomments` &limit=`10` !] - 10 последних комментариев со всего сайта
    [!JotX? &docids=`*` &sortby=`rand()` &limit=`1` !] - случайный комментарий со всего сайта
    [!JotX? &docids=`1,2,5-10,20*,30-35,40**,` !] - так тоже можно :)

Параметры docids и tagids используются для вывода данных, docid и tagid - для ввода текущих, поэтому они разделены

* **depth** - глубина древовидных комментариев (по умолчанию 10)
* **upc** - как считать userpostcount (0 - не считать, 1(по умолчанию) - считать для всего сайта , 2 - считать для текущей страницы)
* **tplNavPage,tplNavPageCur,tplNavPageSpl** (разделитель) - шаблоны для постраничной навигации
* **js и jsFile** - аналоги css и cssFile

### События. 
В каждом из двух классов свои.

onBeforeConfiguration,onBeforeRunActions,onRunActions,onConfiguration,onBeforeFirstRun,
onFirstRun,onSubscriptionCheck,onDeleteComment,onGetCommentFields,onBeforeSaveComment,
onSaveComment,onGetSubscriptions,onBeforeGetSubscriptions,onBeforeGetUserInfo,
onBeforeNotify,onBeforeSubscribe,onBeforeUnsubscribe,onBeforeValidateFormField,
onValidateFormFieldFail,onBeforePOSTProcess,onProcessForm,onBeforeProcessPassiveActions,
onProcessPassiveActions,onBeforeGetCommentCount,onBeforeGetComments,onGetComments,
onReturnOutput,onSetDefaultOutput,onBeforeGetUserPostCount,onSetFormOutput,onSetCommentsOutput

### Плагины на события. 
Их можно подгружать как из сниппетов, так и из файлов. Можно прописывать через запятую.

```php
    [!JotX? &onBeforeValidateFormField=`nolink,onlyrus` !]
```

В состав входят плагины:

* **subscribe** (события: onBeforeFirstRun,onSaveComment,onBeforeRunActions,onBeforeProcessPassiveActions,onGetSubscriptions,onBeforeGetUserInfo,onBeforeNotify) - 
подписка гостей сайта на уведомления о новых комментариях. Также необходимы 2 исправления в шаблонах: чекбокс и текст об отписке, см. пример в tree.config.php
* **ajax** (события: onSetCommentsOutput,onSetFormOutput,onReturnOutput) - загрузка всего через аякс
* **antispam** (события: onBeforePOSTProcess,onSetFormOutput) - борьба с ботами путём добавления скрытого поля-ловушки
* **nolink** (событие: onBeforeValidateFormField) - запретить ссылки в комментариях
* **onlyrus** (событие: onBeforeValidateFormField) - запретить нерусский спам
* **notifyfaq** (события: onProcessForm,onBeforeNotify) - уведомление пользователю об ответе на вопрос в FAQ
* **rss** (события: onBeforeProcessPassiveActions,onSetCommentsOutput) - добавляет ссылку на RSS-ленту

Будут и другие.

### Прочие исправления.

* Система уведомлений объединена и переделана под PHPMailer
* Оптимизированы запросы в базу, в том числе и для userpostcount. Поля пользователей объединены с полями комментариев.
* Исправлены старые баги с удалением/добавлением полей
* Постраничная пагинация, в древовидных комментариях она тоже работает, если включить
* Всякие мелочи, типа граватаров
