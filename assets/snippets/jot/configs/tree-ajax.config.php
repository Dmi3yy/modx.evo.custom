<?php
	setlocale (LC_ALL, 'ru_RU.UTF-8');
	$customfields='name,email';
	$validate='name:Вы не написали своё имя,email:Неправильный e-mail:email,content:Вы не заполнили поле комментария';
	$action='tree';
	$sortby='createdon:a';
	$cssFile='assets/snippets/jot/css/tree.css';
	$js=1;
	$jsFile='assets/snippets/jot/js/tree.js';
	//$pagination=0;
	
	$onBeforePOSTProcess='antispam';
	$onSetFormOutput='antispam,ajax';
	$onBeforeValidateFormField='nolink';

	$onBeforeFirstRun='subscribe';
	$onSaveComment='subscribe';
	$onBeforeRunActions='subscribe';
	$onBeforeProcessPassiveActions='subscribe';
	$onGetSubscriptions='subscribe';
	$onBeforeGetUserInfo='subscribe';
	$onBeforeNotify='subscribe';
	
	$onSetCommentsOutput='ajax';
	$onReturnOutput='ajax';
	
	$tplForm = '@CODE:
<div id="respond-[+jot.link.id+]" class="jot-form-wrap">
<a name="jf[+jot.link.id+]"></a>
<h3 class="jot-reply-title"><a class="jot-btn jot-reply-cancel" href="[~[*id*]~]#jf[+jot.link.id+]" id="cancel-comment-link-[+jot.link.id+]" rel="nofollow">Отменить</a>[+form.edit:is=`1`:then=`Изменить комментарий`:else=`Добавить комментарий`+]</h3>
<script type="text/javascript">document.getElementById("cancel-comment-link-[+jot.link.id+]").style.display = "none"</script>
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
&4=Вы отписались от уведомлений.
`+]
</div>
`:strip+]
<form method="post" action="[+form.action:esc+]#jf[+jot.link.id+]" class="jot-form">
	<input name="JotForm" type="hidden" value="[+jot.id+]" />
	<input name="JotNow" type="hidden" value="[+jot.seed+]" />
	<input name="parent" type="hidden" value="[+form.field.parent+]" id="comment-parent-[+jot.link.id+]" />
	
	[+form.moderation:is=`1`:then=`
	<div class="jot-info">
		<b>Создан:</b> [+form.field.createdon:date=`%d %b %Y в %H:%M`+]<br />
		<b>Автор:</b> [+form.field.createdby:userinfo=`username`:ifempty=`[+jot.guestname+]`+]<br />
		<b>IP:</b> [+form.field.secip+]<br />
		<b>Опубликовано:</b> [+form.field.published:select=`0=Нет&1=Да`+]<br />
		[+form.field.publishedon:gt=`0`:then=`
		<b>Дата публикации:</b> [+form.field.publishedon:date=`%d %b %Y в %H:%M`+]<br />
		<b>Опубликовал:</b> [+form.field.publishedby:userinfo=`username`:ifempty=` - `+]<br />
		`+]
		[+form.field.editedon:gt=`0`:then=`
		<b>Дата изменения:</b> [+form.field.editedon:date=`%d %b %Y в %H:%M`+]<br />
		<b>Редактировал:</b> [+form.field.editedby:userinfo=`username`:ifempty=` -`+]<br />
		`+]
	</div>
	`:strip+]
	
	<div class="jot-controls">
		<input tabindex="[+jot.seed:math=`?+3`+]" name="title" type="text" size="40" value="[+form.field.title:esc+]" placeholder="Заголовок (необязательно)" />
	</div>
	<div class="jot-controls">
		<textarea tabindex="[+jot.seed:math=`?+4`+]" name="content" cols="50" rows="6" id="content-[+jot.link.id+]" placeholder="Введите комментарий...">[+form.field.content:esc+]</textarea>
	</div>
	
	[+form.guest:is=`1`:then=`
	<div class="jot-controls">
		<div class="jot-input-prepend">
			<span class="jot-add-on"><i class="jot-icon-user"></i></span><input tabindex="[+jot.seed:math=`?+1`+]" name="name" type="text" size="40" value="[+form.field.custom.name:esc+]" placeholder="Ваше имя" title="Ваше имя" />
		</div>
		<div class="jot-input-prepend">
			<span class="jot-add-on"><i class="jot-icon-mail"></i></span><input tabindex="[+jot.seed:math=`?+2`+]" name="email" type="text" size="40" value="[+form.field.custom.email:esc+]" placeholder="Email (не публикуется)" title="Email (не публикуется)" />
		</div>
	</div>
	`+]
	
	<div class="jot-form-actions">
		<button tabindex="[+jot.seed:math=`?+5`+]" class="jot-btn jot-btn-submit" type="submit">[+form.edit:is=`1`:then=`Сохранить`:else=`Отправить`+]</button>
		[+form.edit:is=`1`:then=`
		<button tabindex="[+jot.seed:math=`?+6`+]" class="jot-btn jot-btn-cancel" onclick="history.go(-1);return false;">Отмена</button>
		`+]
		[+jot.user.id:is=`0`:then=`
		<label class="jot-checkbox">
			[+form.subscribed:is=`0`:then=`<input type="checkbox" name="subscribe" value="1" /> Уведомлять меня о новых комментариях по E-mail`+]
			[+form.subscribed:is=`1`:then=`Вы уже подписаны на уведомления о новых комментариях`+]
		</label>
		`+]
	</div>
</form>
</div>
	';

	$tplComments = '@CODE:
<div class="jot-comment">
	<a name="jc[+jot.link.id+][+comment.id+]"></a>
	<div class="jot-row [+chunk.rowclass+] [+comment.published:is=`0`:then=`jot-row-up`+]">
		<div class="jot-comment-head">
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
			<div class="jot-avatar" [+comment.createdby:ne=`0`:then=`title="Ответов: [+comment.userpostcount+]"`+]><img src="http://www.gravatar.com/avatar/[+comment.email:ifempty=`[+comment.custom.email+]`:lcase:md5+]?s=24&amp;d=mm&amp;r=g" alt="" /></div>
			<span class="jot-name">[+comment.username:ifempty=`[+comment.custom.name:ifempty=`[+jot.guestname+]`:esc+]`+] [+jot.moderation.enabled:is=`1`:then=`<span class="jot-extra"><a target="_blank" href="http://www.ripe.net/perl/whois?searchtext=[+comment.secip+]">([+comment.secip+])</a></span>`+]</span>
			<span class="jot-date">[+comment.createdon:date=`%d %b %Y в %H:%M`+]</span>
			<span class="jot-perma"><a rel="nofollow" title="Ссылка на комментарий" href="[+jot.link.current+]#jc[+jot.link.id+][+comment.id+]">#<!--[+comment.postnumber+]--></a></span>
			[+comment.depth:lt=`[+jot.depth+]`:then=`
			<span class="jot-reply"><a rel="nofollow" href="[+comment.parentlink+]#jf[+jot.link.id+]" onclick="return addComment.moveForm(\'[+jot.link.id+]\', \'[+comment.id+]\')">Ответить</a></span>
			`+]
		</div>
		<div class="jot-comment-entry" id="comment-[+jot.link.id+]-[+comment.id+]">
			[+comment.title:length:ne=`0`:then=`<div class="jot-subject">[+comment.title:esc+]</div>`+]
			<div class="jot-message">[+comment.content:wordwrap:esc:nl2br+]</div>
		</div>
	</div>
	<div class="jot-children">
		[+jot.wrapper+]
	</div>
</div>
	';
	
	$tplNav = '@CODE:
<a name="jotnav[+jot.link.id+]"></a>
<div class="jot-nav">
	<a rel="nofollow" class="jot-btn jot-show-all" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=0#jotnav[+jot.link.id+]">Просмотреть все</a>
	[+jot.page.current:gt=`1`:then=`
	<a rel="nofollow" class="jot-btn" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.current:math=`?-1`+]#jotnav[+jot.link.id+]">&laquo; Предыдущяя</a>
	`+]
	[+jot.pages+]
	[+jot.page.current:lt=`[+jot.page.total+]`:then=`
	<a rel="nofollow" class="jot-btn" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.current:math=`?+1`+]#jotnav[+jot.link.id+]">Следующая &raquo;</a>
	`+]
</div>
	';
	
	$tplNavPage = '@CODE:
	<a rel="nofollow" class="jot-btn" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.num+]#jotnav[+jot.link.id+]">[+jot.page.num+]</a>
	';
	
	$tplNavPageCur = '@CODE:
	<a rel="nofollow" class="jot-btn jot-btn-active" href="[+jot.link.navigation:esc+][+jot.querykey.navigation+]=[+jot.page.num+]#jotnav[+jot.link.id+]">[+jot.page.num+]</a>
	';

?>