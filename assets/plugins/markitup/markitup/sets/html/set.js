// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Html tags
// http://en.wikipedia.org/wiki/html
// ----------------------------------------------------------------------------
// Basic set. Feel free to add more tags
// ----------------------------------------------------------------------------
mySettings = {
    nameSpace:       "html", // Useful to prevent multi-instances CSS conflict
	onShiftEnter:	{keepDefault:false, replaceWith:'<br />\n'},
	onCtrlEnter:	{keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
	onTab:			{keepDefault:false, openWith:'	 '},
	markupSet: [
		{name:'Заголовок 1', key:'1', openWith:'<h1(!( class="[![Class]!]")!)>', closeWith:'</h1>', placeHolder:'Текст...' },
		{name:'Заголовок 2', key:'2', openWith:'<h2(!( class="[![Class]!]")!)>', closeWith:'</h2>', placeHolder:'Текст...' },
		{name:'Заголовок 3', key:'3', openWith:'<h3(!( class="[![Class]!]")!)>', closeWith:'</h3>', placeHolder:'Текст...' },
		{name:'Заголовок 4', key:'4', openWith:'<h4(!( class="[![Class]!]")!)>', closeWith:'</h4>', placeHolder:'Текст...' },
		{name:'Заголовок 5', key:'5', openWith:'<h5(!( class="[![Class]!]")!)>', closeWith:'</h5>', placeHolder:'Текст...' },
		{name:'Заголовок 6', key:'6', openWith:'<h6(!( class="[![Class]!]")!)>', closeWith:'</h6>', placeHolder:'Текст...' },
		{name:'Абзац', openWith:'<p(!( class="[![Class]!]")!)>', closeWith:'</p>' },
		{separator:'---------------' },
		{name:'Полужирный', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)' },
		{name:'Курсив', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)' },
		{name:'Зачеркнутый', key:'S', openWith:'<del>', closeWith:'</del>' },
		{separator:'---------------' },
		{name:'Маркированный список', openWith:'<ul>\n', closeWith:'</ul>\n' },
		{name:'Нумерованный список', openWith:'<ol>\n', closeWith:'</ol>\n' },
		{name:'Пункт списка', openWith:'<li>', closeWith:'</li>' },
		{separator:'---------------' },
		{name:'Цитата', openWith:'<q>', closeWith:'</q>'}, 
		{name:'Код', openWith:'<code>', closeWith:'</code>'}, 
		{separator:'---------------' },
		{name:'Выбрать изображение', className:'pictures', 
			beforeInsert:function() {
				OpenBrowser('images');
			}
		},
		{name:'Изображение', className:'picture', key:'P', replaceWith:'<img src="[![Путь:!:http://]!]" alt="[![Описание]!]" />' },
		{name:'Ссылка на файл', className:'files', 
			beforeInsert:function() {
				OpenBrowser('files');
			}
		},
		{name:'Ссылка', className:'link', key:'L', openWith:'<a href="[![Ссылка:!:http://]!]"(!( title="[![Title]!]")!)>', closeWith:'</a>', placeHolder:'Текст ссылки...' },
		{separator:'---------------' },
		{name:'Очистить', className:'clean', replaceWith:function(markitup) { return markitup.selection.replace(/<(.*?)>/g, "") } },
		{name:'Предпросмотр', call:'preview', className:'preview' }
	]
}