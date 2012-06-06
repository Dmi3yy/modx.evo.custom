<?php
interface langVer
{
   const err_nomodx = 'Нет доступа к MODX API';
	const err_mode = 'Установлен некорректный режим';
	const err_loadjs = 'Для начала необходимо инициализировать jQuery';
	const err_noload = 'Не удалось загрузить данные';
	const err_fatalload = 'Произошла ошибка во время загрузки';
	const err_del = 'Произошла ошибка во время удаления';
	
	const form_nameblock = 'Версии';
	const form_descver = 'Описание изменений';
	const form_savever = 'сохранить эту версию';
	const form_beforever = 'Предыдущая версия сниппета';
	const form_noversion = 'Других версий этого сниппета нет';
	const form_nodesc = 'без описания';
	
	const word_del = 'Удалить';
	const word_load = 'Загрузить';
	const word_ver = 'Версия';
}
?>