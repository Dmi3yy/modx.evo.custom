/*
 * TreeTabs 1.1
 *
 * Written By Bumkaka - 15 Sep 2012
 *
 * russandrussky.org.ua/zip/treetabs.zip
 */
 
 
 
 1. Скопировать содержимое архива в assets\plugins\
 2. Создать плагин TreeTabs, вставить в него код treetabs.tpl
 
 3. В "Конфигурация плагина"  вставить:
 &setting_tabs=ID ресурсов для закладок (1,2,3,4);text; &setting_tabs_spec_id=ID спец закладки;text;
 &setting_tabs_spec_chunk=Чанк настройки;text;TreeTabs &width=ширина блока закладок;text;400 
 &name_main=Титл первой закладки;text;Главная &show_parent=показывать родителя<br/> (1 - да/0 - нет);text;0 * Version 1.0

 4. Жмём "обновить параметры"
 5. в поле "ID ресурсов для закладок" выставляем необходимые ID 
 6. Если есть необходимость спец закладки:
  - создать ресурс с требуемым имененм закладки
  - добавить его айди в "ID ресурсов для закладок"  и "ID спец закладки"
  - создать чанк на примере chunk-example.txt из архива
  - имя чанка вставить в "Чанк настройки"
  
  
  
  Чанк настройки:
  
действие || ID ресурса || название пункта меню ||  права  доступа</element>
  
  
  
  
Редактируемые шаблоны</name>
edit_chunk||41||шапка сайта||1,3</element>
edit_chunk||42||шапка сайта||1,3</element>


</section>

выполнить</name>
run_chunk||6||модуль №1</element>

</section>

Магазин (модули)</name>
run_module||5||Заказы||1,2,3</element>
run_module||5||Письма</element>
run_module||5||Проведённые||1</element>
run_module||5||Доставки||1,3</element>