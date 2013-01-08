<?php
	setlocale (LC_ALL, 'ru_RU.UTF-8');
	$captcha=2;
	$moderated=1;
	$customfields='name,email,answer';
	$validate='name:Вы не написали своё имя,email:Неправильный e-mail:email,content:Вы не заполнили поле сообщения';
	$cssFile='assets/snippets/jot/css/faq.css';
	
	$onBeforePOSTProcess='antispam';
	$onSetFormOutput='antispam';
	
	$tplForm='@CODE:
<div class="jot-form-wrap">
<a name="jf[+jot.link.id+]"></a>
<h3 class="jot-reply-title">[+form.edit:is=`1`:then=`Изменить вопрос`:else=`Задать вопрос`+]</h3>
[+form.error:isnt=`0`:then=`
<div class="jot-err">
[+form.error:select=`
&-3=Вы пытаетесь отправить одно и то же сообщение. Возможно вы нажали кнопку отправки более одного раза.
&-2=Ваше сообщение было отклонено.
&-1=Ваше сообщение сохранёно, оно будет опубликовано после просмотра администратором.
&1=Вы пытаетесь отправить одно и то же сообщение. Возможно вы нажали кнопку отправки более одного раза.
&2=Вы ввели неправильный защитный код.
&3=Вы можете отправлять сообщения не чаще [+jot.postdelay+] секунд.
&4=Ваше сообщение было отклонено.
&5=[+form.errormsg:ifempty=`Вы не заполнили все требуемые поля`+]
`+]
</div>
`:strip+]
[+form.confirm:isnt=`0`:then=`
<div class="jot-cfm">
[+form.confirm:select=`
&1=Ваше сообщение опубликовано.
&2=Ваше сообщение сохранёно, оно будет опубликовано после просмотра администратором.
&3=Сообщение сохранено.
`+]
</div>
`:strip+]
<form method="post" action="[+form.action:esc+]#jf[+jot.link.id+]" class="jot-form">
	<input name="JotForm" type="hidden" value="[+jot.id+]" />
	<input name="JotNow" type="hidden" value="[+jot.seed+]" />
	<input name="parent" type="hidden" value="[+form.field.parent+]" />
	
	<div class="jot-controls">
		<label for="name-[+jot.link.id+]">Ваше имя:</label>
		<input tabindex="[+jot.seed:math=`?+1`+]" name="name" type="text" size="40" value="[+form.field.custom.name:esc+]" id="name-[+jot.link.id+]" />
	</div>
	<div class="jot-controls">
		<label for="email-[+jot.link.id+]">Ваш email:</label>
		<input tabindex="[+jot.seed:math=`?+2`+]" name="email" type="text" size="40" value="[+form.field.custom.email:esc+]" id="email-[+jot.link.id+]" />
	</div>
	<div class="jot-controls">
		<label for="title-[+jot.link.id+]">Тема вопроса:</label>
		<input tabindex="[+jot.seed:math=`?+3`+]" name="title" type="text" size="40" value="[+form.field.title:esc+]" id="title-[+jot.link.id+]" />
	</div>
	<div class="jot-controls">
		<label for="content-[+jot.link.id+]">Вопрос:</label>
		<textarea tabindex="[+jot.seed:math=`?+4`+]" name="content" cols="50" rows="6" id="content-[+jot.link.id+]">[+form.field.content:esc+]</textarea>
	</div>

	[+form.moderation:is=`1`:then=`
	<div class="jot-controls">
		<label for="answer-[+jot.link.id+]">Ответ:</label>
		<textarea tabindex="[+jot.seed:math=`?+8`+]" name="answer" cols="50" rows="6" id="answer-[+jot.link.id+]">[+form.field.custom.answer:esc+]</textarea>
	</div>
	`+]

	
	[+jot.captcha:is=`1`:then=`
	<div class="jot-controls">
		<a href="[+jot.link.current:esc+]" onclick="onclick=document.captcha.src=src+\'?rand=\'+Math.random(); return false;" title="Если код не читается, нажмите сюда, 
		чтобы сгенерировать новый"><img src="[(base_url)]manager/includes/veriword.php?rand=[+jot.seed+]" name="captcha" class="jot-captcha" width="148" height="60" alt="" /></a><br />
		<label for="vericode-[+jot.link.id+]">Код:</label>
		<input type="text" name="vericode" id="vericode-[+jot.link.id+]" style="width:150px" size="20" />
	</div>
	`+]

	<div class="jot-form-actions">
		<input tabindex="[+jot.seed:math=`?+5`+]" class="jot-btn jot-btn-submit" type="submit" value="[+form.edit:is=`1`:then=`Сохранить`:else=`Отправить`+]" />
		[+form.edit:is=`1`:then=`
		<input tabindex="[+jot.seed:math=`?+6`+]" class="jot-btn jot-btn-cancel" type="button" onclick="history.go(-1);return false;" value="Отмена" />
		`+]
	</div>
</form>
</div>
	';

	$tplComments='@CODE:
<div class="jot-comment">
	<a name="jc[+jot.link.id+][+comment.id+]"></a>
	<div class="jot-row [+chunk.rowclass+] [+comment.published:is=`0`:then=`jot-row-up`+]">
		<div class="jot-comment-head">
			[+jot.moderation.enabled:is=`1`:then=`<span class="jot-extra"><a target="_blank" href="http://www.ripe.net/perl/whois?searchtext=[+comment.secip+]">([+comment.secip+])</a></span>`+]
			<span class="jot-perma"><a rel="nofollow" title="Ссылка на вопрос" href="[+jot.link.current+]#jc[+jot.link.id+][+comment.id+]">#[+comment.postnumber+]</a> |</span>
			<span class="jot-date">[+comment.createdon:date=`%d %b %Y в %H:%M`+]</span>
			<span class="jot-name">[+comment.username:ifempty=`[+comment.custom.name:ifempty=`[+jot.guestname+]`:esc+]`+]:</span>
			[+comment.title:length:ne=`0`:then=`<span class="jot-subject">[+comment.title:esc+]</span>`+]
		</div>
		<div class="jot-comment-entry">
			<div class="jot-mod">
				[+jot.user.canedit:is=`1`:and:if=`[+comment.createdby+]`:is=`[+jot.user.id+]`:or:if=`[+jot.moderation.enabled+]`:is=`1`:then=`
					<a class="jot-btn jot-btn-edit" href="[+jot.link.edit:esc+][+jot.querykey.id+]=[+comment.id+]#jf[+jot.link.id+]" title="Изменить"><i class="jot-icon-edit"></i> Изменить</a>
				`:strip+]
				[+jot.moderation.enabled:is=`1`:then=`
					[+comment.published:is=`0`:then=`<a class="jot-btn jot-btn-pub" href="[+jot.link.publish:esc+][+jot.querykey.id+]=[+comment.id+]#jotmod[+jot.link.id+]" title="Показать"><i class="jot-icon-pub"></i> Показать</a>`+]
					[+comment.published:is=`1`:then=`<a class="jot-btn jot-btn-unpub" href="[+jot.link.unpublish:esc+][+jot.querykey.id+]=[+comment.id+]#jotmod[+jot.link.id+]" title="Скрыть"><i class="jot-icon-unpub"></i> Скрыть</a>`+]
					<a class="jot-btn jot-btn-del" href="[+jot.link.delete:esc+][+jot.querykey.id+]=[+comment.id+]#jotmod[+jot.link.id+]" onclick="return confirm(\'Вы действительно хотите удалить это сообщение?\')" title="Удалить"><i class="jot-icon-del"></i> Удалить</a>
				`:strip+]
			</div>
			<div class="jot-message">[+comment.content:wordwrap:esc:nl2br+]</div>
		</div>
		[+comment.custom.answer:length:ne=`0`:then=`
		<div class="jot-answer">
			<span class="jot-answer-author">[(site_name)]:</span>
			[+comment.custom.answer:wordwrap:esc:nl2br+]
		</div>
		`+]
	</div>
</div>
	';
	
	$tplNav='@CODE:
<a name="jotnav[+jot.id+]"></a>
<div class="jot-nav">
	[+jot.page.current:gt=`1`:then=`
	<a rel="nofollow" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=1#jotnav[+jot.id+]">Первая страница</a> |
	<a rel="nofollow" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.current:math=`?-1`+]#jotnav[+jot.id+]">Предыдущяя страница</a> | 
	`+]
	Показаны сообщения с <b>[+jot.nav.start+]</b> по <b>[+jot.nav.end+]</b> из <b>[+jot.nav.total+]</b> 
	[+jot.page.current:lt=`[+jot.page.total+]`:then=`
	| <a rel="nofollow" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.current:math=`?+1`+]#jotnav[+jot.id+]">Следующая страница</a>
	| <a rel="nofollow" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.total+]#jotnav[+jot.id+]">Последняя страница</a>
	`+]
</div>
	';
?>