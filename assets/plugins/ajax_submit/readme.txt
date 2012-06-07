
ajaxSubmit 1.0.1
плагин и сниппет для MODx 1.x

Andchir
http://modx-shopkeeper.ru/

Используется класс Zend_Dom_Query (http://framework.zend.com/).

Требуются расширения: php-xml, php-dom.

---------------------------------

Описание:

Отправка "аяксом" любой формы.
Плагин вырезает всё лишнее из содержимого страницы, оставляя только вывод нужного сниппета, при аякс-запросах.

---------------------------------

Установка:

1. Поместите папку ajax_submit в папку с плагинами assets/plugins/.

2. Установить плагин ajaxSubmit. Для этого в системе управления открыть "Элементы" -> "Управление элементами" -> "Плагины" -> "Создать плагин".

   Название плагина:
   ajaxSubmit
   
   Описание:
   Плагин вырезает всё лишнее из содержимого страницы, оставляя только вывод нужного сниппета, при аякс-запросах.
   
   Код плагина взять из файла install/ajax_submit_plugins.tpl. Первую строку ("//<?php") можно не копировать.
   
   Конфигурация плагина:
   &post_signal=Post signal name;string;ajax_submit &check_referer=Check referer;list;yes,no;yes
   
   Системные события:
   OnLoadWebDocument, OnLoadWebPageCache
   
3. Установить сниппет (не обязательно, см. ниже). Для этого перейти "Элементы" -> "Управление элементами" -> "Сниппеты" -> "Новый сниппет".

   Название сниппета:
   ajaxSubmit
   
   Описание:
   Сниппет отправляет данные формы аякс-запросом.
   
   Код сниппета взять из файла install/ajax_submit_snippet.tpl. В первой строке убрать символы "//" чтобы получилось так: "<?php" (без кавычек).

4. Вставить вызов сниппета перед формой или написать свой JS-скрипт (см. пример ниже). 

---------------------------------

Примечание:

1. Сниппет устанавливать не обязательно. Можно написать свою функцию на JavaScript на примере того, что выводит сниппет:

<script type="text/javascript">
<!--
function as_setAction(){
    jQuery("#feedbackForm form:first")
    .unbind('submit')
    .bind('submit',function(){
        jQuery.ajax({
          url: window.location.href,
          type: "post",
          data: jQuery(this).serialize()+"&ajax_submit=#feedbackForm",
          dataType: 'html',
          success: function(response){
            if(response=='success' || response.length <= 0){
                jQuery("#feedbackForm").html("Спасибо! Ваше письмо отправлено.");
            }else{
                jQuery("#feedbackForm").html(response);
            }
          }
        });
        return false;
    });
}
jQuery(document).bind('ready',as_setAction);
//-->
</script>

2. Возможна также автоматическая установка плагина и сниппета ajaxSubmit при установке MODx. Для этого перед установкой MODx нужно загрузить папку папку ajax_submit в папку с плагинами assets/plugins/.
Пересместить файл ajax_submit_plugins.tpl в папку install/assets/plugins/.
Пересместить файл ajax_submit_snippet.tpl в папку install/assets/snippets/.
Установить MODx.

---------------------------------

Параметры сниппета:

id - идентификатор. Полезно, если сниппет вызывается несколько раз на одной странице.
noJQuery - Не загружать jQuery (1|0). По умолчанию выключено (0).
form - селектор (формат CSS) формы. Например: form:first.
postSignal - имя элемента POST, по которому будет определяться, что нам нужен только отдельный участок HTML-кода, а не вся страница. По умолчанию `ajax_submit`.
container - селектор контейнера формы.
msgElem - селектор елемента в котором выводятся сообщения об ошибках заполнения полей.
msgMinLength - минимальная длина сообщения в msgElem. Если число символов меньше или ровно этому значению, то будет показано сообщение (succesMessage).
succesMessage - Сообщение, которое будет выведено после удачной отправке формы.

---------------------------------

Callback JavaScript функции:

as_reqStartCallback() - перед ajax-запросом.
as_reqCompletCallback() - после выволнения ajax-запроса.
as_successCallback() - завершена отправка формы (нет сообщений проверки заполнения полей).

Если функции с этими именами созданы, они будут вызваны в соответствующий момент. При использовании параметра &id к названиям функций нужно добавить этот ID.

---------------------------------

Примеры использования:

[[ajaxSubmit? &container=`#feedbackForm`]]

<div id="feedbackForm">

[!eForm? &vericode=`1`&formid=`feedbackForm`&tpl=`feedbackForm`&report=`feedbackReport`&subject=`Письмо с сайта`!]

</div>

---------

[[ajaxSubmit? &container=`#loginForm`]]

<div id="loginForm">

[!WebLogin? &tpl=`weblogin`!]

</div>

---------

[[ajaxSubmit? &form=`#feedbackForm`&msgElem=`div.error`&succesMessage=`Письмо успешно отправлено.`]]

[!eForm? &vericode=`1`&formid=`feedbackForm`&tpl=`feedbackForm`&report=`feedbackReport`&subject=`Письмо с сайта`!]

<script type="text/javascript">
function as_successCallback(){
    $('#feedbackForm').get(0).reset();
}
</script>

-- чанк "feedbackForm" --

<div class="error">[+validationmessage+]</div>

<form id="feedbackForm" method="post" action="[~[*id*]~]">
...
</form>

---------

[[ajaxSubmit? &container=`#shopOrderForm`&msgMinLength=`159`]]

...

---------------------------------

