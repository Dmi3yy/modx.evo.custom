
http://modx-shopkeeper.ru

---------------------------------------------

Поддерживается только кодировка UTF-8. 

---------------------------------------------

Что добавлено/исправлено:

1. Русский перевод (языковой файл).

2. Добавлен параметр для WebLogin и WebSignup:
   &alerttpl - имя чанка для вывода сообщений об ошибках заполения полей и т.п.
   В это чанке доступен плейсхолдер [+msq+] для вывода тектса сообщения.

3. Письма отправляются с использованием класс PHPMailer. Рекомендуется обновить класс до поcледней версии. Для этого скачайте (http://sourceforge.net/projects/phpmailer/files/phpmailer%20for%20php5_6/) и змените файл manager/includes/controls/class.phpmailer.php.

4. В WebSignup добавлено событие OnWUsrFormRender для поддержки плагина addWebUserFields.

5. Убран, раздражающий, focus() на поле логина авторизации.

---------------------------------------------


Пример вызова WebLogin:

[!WebLogin? &tpl=`weblogin`&alerttpl=`weblogin_alerttpl`&loginhomeid=`1`&logouthomeid=`1`!]



Пример вызова WebSignup:

[!WebSignup? &tpl=`FormSignup`&alerttpl=`websignup_alerttpl`&groups=`Customers`&useCaptcha=`1`!]



