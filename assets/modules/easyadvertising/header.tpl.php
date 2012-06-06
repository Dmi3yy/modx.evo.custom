<?php
if (!defined('IN_MANAGER_MODE') || (defined('IN_MANAGER_MODE') && (!IN_MANAGER_MODE || IN_MANAGER_MODE == 'false'))) die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  lang="en" xml:lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $theme; ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modUrl; ?>css/easy.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modUrl; ?>css/flexigrid.pack.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modUrl; ?>css/<?php echo $ui_theme; ?>/jquery-ui-1.8.20.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modUrl; ?>css/jquery-ui-timepicker-addon.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modUrl; ?>elfinder/css/elfinder.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modUrl; ?>elfinder/css/theme.css" />
	
	<script type="text/javascript" src="<?php echo $modUrl; ?>js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="<?php echo $modUrl; ?>js/flexigrid.js"></script>
	<script type="text/javascript" src="<?php echo $modUrl; ?>js/jquery-ui-1.8.20.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo $modUrl; ?>js/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="<?php echo $modUrl; ?>js/jquery-ui-timepicker-ru.js"></script>
	<script type="text/javascript" src="<?php echo $modUrl; ?>elfinder/js/elfinder.min.js"></script>
	<script type="text/javascript" src="<?php echo $modUrl; ?>elfinder/js/i18n/elfinder.ru.js"></script>
		
	<script type="text/javascript">
	
		function postForm(action, id, tab) {
			document.module.action.value=action;
			if (id != null) document.module.item_id.value=id;
			if (tab != null) document.module.tab_id.value=tab;
			document.module.submit();
		}
		
		function uiMess(text, title) {
			var id = 'uiMess';
			jq("body").append('<div id="'+id+'"></div>');
			jq("#"+id).html(text).dialog({
				width		: 'auto',
				modal		: true,
				title		: title,
				buttons		: {
					"Ok" : function () {
						jq(this).dialog("close");
					}
				},
				close: function() {
					jq(this).remove();
				}
			});
		}
		
		function delRow(id) {
			jq.post(
				'<?php echo $modUrl; ?>getData.php',
				{id : id, action : 'del'}, 
				function(data) {
					if (data == 1)
						jq("#flex").flexReload();	
					else 
						uiMess('При попытке удаления произошла ошибка.', 'Ошибка');
				}
			);
		}
		
		function addRow(id) {
			jq.post(
				'<?php echo $modUrl; ?>getData.php',
				{id : id, action : 'add', theme : '<?php echo $theme; ?>', modUrl : '<?php echo $modUrl; ?>'}, 
				function(data) {
					var frm = '<div id="mform">'+data+'</div>';
					jq("body").append(frm);
					jq("#mform").dialog({
						autoOpen: true,
						width: 'auto',
						modal: true,
						title: 'Баннер', 
						buttons: {
							"Просмотр" : function() {
								var frm = jq("#add_row").serialize(); 
								jq.post(
									'<?php echo $modUrl; ?>preview.php',
									frm, 
									function(data) {
										uiMess(data, 'Просмотр');
									}
								);
							},
							"Сохранить" : function() {
								var frm = jq("#add_row").serialize();
								jq.post(
									'<?php echo $modUrl; ?>getData.php',
									frm+'&action=save&id='+id,
									function(data) {
										if (data == 1)
											jq("#flex").flexReload();	
										else 
											uiMess('При попытке сохранинея данных произошла ошибка.', 'Ошибка');
									}
								);
								jq(this).dialog("close");
							},
							"Отмена" : function() {
								jq(this).dialog("close");
							}
						},
						close: function() {
							jq(this).remove();
						}
					})
				}
			);
		}
		
		var jq = jQuery.noConflict();
		jq(document).ready(function(){
			
			jq("table#flex").flexigrid({
				url: '<?php echo $modUrl; ?>getData.php',
				dataType: 'json',
				colModel : [
						{display: 'id', name : 'id', width : <?php echo $w[0]; ?>, sortable : true, align: 'left'},
						{display: 'Позиция', name : 'pos', width : <?php echo $w[1]; ?>, sortable : true, align: 'left'},
						{display: 'Шаблон URL', name : 'template', width : <?php echo $w[2]; ?>, sortable : true, align: 'left'},
						{display: 'Зона', name : 'area', width : <?php echo $w[3]; ?>, sortable : true, align: 'left'},
						{display: 'Описание', name : 'description', width : <?php echo $w[4]; ?>, sortable : true, align: 'left'},
						{display: 'Старт', name : 'pub_date', width : <?php echo $w[5]; ?>, sortable : true, align: 'center'},
						{display: 'Финиш', name : 'unpub_date', width : <?php echo $w[6]; ?>, sortable : true, align: 'center'},
						{display: 'Активен', name : 'published', width : <?php echo $w[7]; ?>, sortable : true, align: 'center'},
						{display: 'Показов', name : 'count_view', width : <?php echo $w[8]; ?>, sortable : true, align: 'left'},
						{display: 'План', name : 'total_view', width : <?php echo $w[9]; ?>, sortable : true, align: 'left'},
						{display: 'Переходов', name : 'jump_count', width : <?php echo $w[10]; ?>, sortable : true, align: 'left'},
						{display: '', name : 'edit', width : 20, sortable : false, align: 'center'},
						{display: '', name : 'del', width : 20, sortable : false, align: 'center'}
						],
				searchitems : [
						{display: 'Шаблон URL', name : 'template', isdefault: true},
						{display: 'Рекламная зона', name : 'area'},
						{display: 'Описание', name : 'description'}
						],
				sortname: 'pos',
				sortorder: 'asc',
				usepager: true,
				useRp: true,
				rp: 50,
				height: <?php echo $table_height; ?>,
				showTableToggleBtn: true
			});   
		
		});
	
	</script>
 
</head>
<body>
 
<br />
<div class="sectionHeader">Easy Advertising - управление рекламой на сайте</div>
 
    <div class="sectionBody">
 
		<form name="module" method="post">
			<input name="action" type="hidden" value="" />
			<input name="item_id" type="hidden" value="" />
			<input name="tab_id" type="hidden" value="" />
