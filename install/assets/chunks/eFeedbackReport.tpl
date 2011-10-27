/**
 * eFeedbackReport
 * 
 * eFeedbackReport  шаблон отправки на почту
 * 
 * @category	chunk
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal @modx_category Forms
 */
 
<p>Прислано человеком, с именем: [+name+] . Подробности ниже:</p>
<table>
<tr valign="top"><td>Имя:</td><td>[+name+]</td></tr>
<tr valign="top"><td>E-mail:</td><td>[+email+]</td></tr>
<tr valign="top"><td>Номер телефона:</td><td>[+phone+]</td></tr>
<tr valign="top"><td>Текст сообщения:</td><td>[+comments+]</td></tr>
</table>
<p>Можно использовать ссылку для ответа: <a href="mailto:[+email+]?subject=RE:[+subject+]">[+email+]</a></p>

