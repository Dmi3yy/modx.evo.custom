var $j = jQuery.noConflict();

var lastImageCtrl;
var lastFileCtrl;
var rteOptions;
var dataTableLanguage;

if (!String.prototype.supplant) {
	String.prototype.supplant = function(o) {
		return this.replace(/{([^{}]*)}/g,
				function(a, b) {
					var r = o[b];
					return typeof r === 'string' || typeof r === 'number' ? r : a;
				}
		);
	};
}

function SetUrl(url, width, height, alt) {
	if (lastFileCtrl) {
		var fileCtrl = $j('#' + lastFileCtrl);
		fileCtrl.val(url);
		fileCtrl.trigger('change');
		lastFileCtrl = '';
	} else if (lastImageCtrl) {
		var imageCtrl = $j('#' + lastImageCtrl);
		imageCtrl.val(url);
		imageCtrl.trigger('change');
		lastImageCtrl = '';
	} else {
		return;
	}
}

(function($) {

	$.fn.transformField = function(options) {

		// Create some defaults, extending them with any options that were provided
		var settings = $.extend({
			mode: '',
			fieldsettings: '',
			language: ''
		}, options);

		return this.each(function() {

			var field = $(this);
			var tvid = field.attr('id');
			var data = new Object();
			var fieldHeading = $('#' + tvid + 'heading');
			var fieldNames = settings.fieldsettings['fieldnames'];
			var fieldTypes = settings.fieldsettings['fieldtypes'];
			var fieldCsvSeparator = settings.fieldsettings['csvseparator'];
			var fieldList = $('#' + tvid + 'list');
			var fieldListElement = fieldList.find('li:first');
			var fieldListElementEmpty = fieldListElement.clone();
			var fieldEdit = $('#' + tvid + 'edit');
			var fieldClear = $('#' + tvid + 'clear');
			var fieldPaste = $('#' + tvid + 'paste');
			var fieldPasteForm = $('#' + tvid + 'pasteform');
			var fieldPasteArea = $('#' + tvid + 'pastearea');
			var fieldListCounter = 1;
			var pasteBox;

			function DuplicateElement(el, count) {
				var clone = el.clone(true).hide();
				var elementId;
				clone.find('[id]').each(function() {
					elementId = $(this).attr('id');
					$(this).attr('id', elementId + (count));
				});
				clone.find('[name]').each(function() {
					$(this).attr('name', $(this).attr('name') + (count));
				});
				addElementEvents(clone);

				// clear inputs/textarea
				var inputs = clone.find(':input');
				inputs.each(function() {
					var type = $(this).attr('type');
					switch (type) {
						case 'button':
							break;
						case 'reset':
							break;
						case 'submit':
							break;
						case 'checkbox':
						case 'radio':
							$(this).attr('checked', false);
							break;
						default:
							$(this).val('');
					}
				});
				return clone;
			}

			function saveMultiValue() {
				var multiElements = fieldList.children('li');
				data.values = [];
				multiElements.each(function() {
					var multiElement = $(this);
					var fieldValues = new Object();
					$.each(fieldNames, function() {
						var fieldInput = multiElement.find('[name^="' + tvid + this + '_mtv"][type!="hidden"]');
						var fieldValue = fieldInput.getValue();
						fieldValues[this] = fieldValue;
						if (fieldInput.hasClass('image')) {
							setThumbnail(fieldValue, fieldInput.attr('name'), multiElement);
						}
						if (fieldInput.hasClass('setdefault') && fieldInput.getValue() === '') {
							fieldInput.setValue(fieldInput.attr('alt').supplant({
								i: data.settings.autoincrement,
								alias: $('[name="alias"]').getValue()

							}));
							data.settings.autoincrement++;
						}
					});
					data.values.push(fieldValues);
				});
				field.setValue($.toJSON({
					fieldValue: data.values,
					fieldSettings: data.settings
				}));
			}

			function prepareMultiValue() {
				var jsonValue = $.evalJSON(field.val().replace(/&#x005B;/g, '[').replace(/&#x005D;/g, ']').replace(/&#x007B;/g, '{').replace(/&#x007B;/g, '}'));
				if (jsonValue) {
					if (jsonValue.constructor === Array) {
						data.value = jsonValue;
						if (!data.settings) {
							data.settings = new Object();
						}
						data.settings.autoincrement = data.value.length + 1;
					} else {
						data.value = jsonValue.fieldValue;
						data.settings = jsonValue.fieldSettings;
					}
				} else {
					data.value = [];
					data.settings.autoincrement = 1;
				}
			}

			function addElementEvents(el) {
				// datepicker
				el.find('.DatePicker').click(function() {
					var picker = $(this).datetimepicker({
						changeMonth: true,
						changeYear: true,
						dateFormat: 'dd-mm-yy',
						timeFormat: 'h:mm:ss'
					});
					picker.datepicker('show');
				});
				// file field browser
				el.find('.browsefile').click(function() {
					var field = $(this).prev('input').attr('id');
					BrowseFileServer(field);
					return false;
				});

				// image field browser
				el.find('.browseimage').click(function() {
					var field = $(this).prev('input').attr('id');
					BrowseServer(field);
					return false;
				});
				// add element
				el.find('.copy').click(function() {
					var clone = DuplicateElement(fieldListElementEmpty, fieldListCounter);
					$(this).parents('.element').after(clone);
					clone.show('fast', function() {
						$(this).removeAttr('style');
					});
					saveMultiValue();
					fieldListCounter++;
					return false;
				});
				// remove element
				el.find('.remove').click(function() {
					if (fieldList.find('.element').length > 1) {
						$(this).parents('.element').hide('fast', function() {
							$(this).remove();
							saveMultiValue();
						});
					} else {
						// clear inputs/textarea
						var inputs = $(this).parent().find('[name]');
						inputs.each(function() {
							var type = $(this).attr('type');
							switch (type) {
								case 'button':
									break;
								case 'reset':
									break;
								case 'submit':
									break;
								case 'checkbox':
								case 'radio':
									$(this).attr('checked', false);
									break;
								default:
									$(this).val('');
							}
						});
					}
					return false;
				});
				// change field
				el.find('[name]').bind('change keyup mouseup', function() {
					saveMultiValue();
					return false;
				});
			}

			function setThumbnail(path, name, el) {
				var thumbPath = path.split('/');
				var thumbName = thumbPath.pop();
				var thumbId = name.replace(/^(.*?)(\d*)$/, '#$1preview$2');
				if (thumbName !== '') {
					el.find(thumbId).html('<img src="../' + thumbPath.join('/') + '/.thumb_' + thumbName + '" />');
				} else {
					el.find(thumbId).html('');
				}
			}

			function prefillInputs() {
				if (data.value) {
					if (settings.mode === 'single') {
						data.value = [data.value[0]];
					}
					$.each(data.value, function() {
						var values = this;
						if (fieldListCounter === 1) {
							$.each(values, function(key, value) {
								var fieldName = (typeof key === 'number') ? fieldNames[key] : key;
								var fieldInput = fieldListElement.find('[name^="' + tvid + fieldName + '_mtv"][type!="hidden"]');
								fieldInput.setValue(values[key]);
								if (fieldInput.hasClass('image')) {
									setThumbnail(values[key], fieldInput.attr('name'), fieldListElement);
								}
								if (fieldInput.hasClass('setdefault') && fieldInput.getValue() === '') {
									fieldInput.setValue(fieldInput.attr('alt').supplant({
										i: data.settings.autoincrement,
										alias: $('[name="alias"]').getValue()
									}));
									data.settings.autoincrement++;
								}
							});
						} else {
							var clone = DuplicateElement(fieldListElementEmpty, fieldListCounter);
							clone.show();
							fieldList.append(clone);
							$.each(values, function(key, value) {
								var fieldName = (typeof key === 'number') ? fieldNames[key] : key;
								var fieldInput = clone.find('[name^="' + tvid + fieldName + '_mtv"][type!="hidden"]');
								fieldInput.setValue(values[key]);
								if (fieldInput.hasClass('image')) {
									setThumbnail(values[key], fieldInput.attr('name'), clone);
								}
								if (fieldInput.hasClass('setdefault') && fieldInput.getValue() === '') {
									fieldInput.setValue(fieldInput.attr('alt').supplant({
										i: data.settings.autoincrement,
										alias: $('[name="alias"]').getValue()
									}));
									data.settings.autoincrement++;
								}
							});
						}
						fieldListCounter++;
					});
				}

			}

			if (!field.hasClass('transformed')) {
				// reset all event
				fieldClear.find('a').click(function() {
					var answer = confirm(tvlanguage.confirmclear);
					if (answer) {
						fieldList.children('li').remove();
						field.val('[]');
						fieldClear.hide();
						fieldPaste.hide();
						fieldHeading.hide();
						fieldEdit.show();
					}
					return false;
				});

				// start edit event
				fieldEdit.find('a').click(function() {
					var clone = fieldListElementEmpty.clone(true);
					fieldList.append(clone);
					field.val('[]');
					fieldList.show();
					fieldClear.show();
					fieldPaste.show();
					fieldHeading.show();
					fieldEdit.hide();
					// sortable
					fieldList.sortable({
						stop: function() {
							saveMultiValue();
						},
						axis: 'y',
						helper: 'clone'
					});
					addElementEvents(clone);
					return false;
				});

				// paste box
				pasteBox = fieldPaste.find('a').click(function(e) {
					e.preventDefault();
					$.colorbox({
						inline: true,
						href: $(this).attr('href'),
						width: '500px',
						height: '350px',
						onClosed: function() {
							fieldPasteArea.html('');
						},
						close: '',
						open: true,
						opacity: '0.35',
						initialWidth: '0',
						initialHeight: '0',
						overlayClose: false
					});
				});

				// close paste box
				fieldPasteForm.find('.cancel').click(function() {
					pasteBox.colorbox.close();
					return false;
				});

				// save pasted form
				fieldPasteForm.find('.replace, .append').click(function() {
					var pastedArray = [];
					var mode = $(this).attr('class');
					var pasteas = $('input:radio[name=pasteas]:checked').val();
					var clean;
					switch (pasteas) {
						case 'google':
							clean = fieldPasteArea.htmlClean({
								allowedTags: ['div', 'span']
							});
							clean.find('div').each(function() {
								var pastedRow = [];
								var tableData = $(this).html().split('<span></span>');
								if (tableData.length > 0) {
									var i = 0;
									$.each(tableData, function() {
										if (fieldTypes[i] === 'thumb') {
											pastedRow.push('');
											i++;
										}
										pastedRow.push($.trim(this));
										i++;
									});
									pastedArray.push(pastedRow);
								}
							});
							break;
						case 'csv':
							clean = fieldPasteArea.htmlClean({
								allowedTags: ['div', 'p']
							});
							clean.find('div, p').each(function() {
								var pastedRow = [];
								// CSV Parser credit goes to Brian Huisman, from his blog entry entitled "CSV String to Array in JavaScript": http://www.greywyvern.com/?post=258
								for (var tableData = $(this).html().split(fieldCsvSeparator), x = tableData.length - 1, tl; x >= 0; x--) {
									if (tableData[x].replace(/"\s+$/, '"').charAt(tableData[x].length - 1) === '"') {
										if ((tl = tableData[x].replace(/^\s+"/, '"')).length > 1 && tl.charAt(0) === '"') {
											tableData[x] = tableData[x].replace(/^\s*"|"\s*$/g, '').replace(/""/g, '"');
										} else if (x) {
											tableData.splice(x - 1, 2, [tableData[x - 1], tableData[x]].join(fieldCsvSeparator));
										} else
											tableData = tableData.shift().split(fieldCsvSeparator).concat(tableData);
									} else
										tableData[x].replace(/""/g, '"');
								}
								if (tableData.length > 0) {
									var i = 0;
									$.each(tableData, function() {
										if (fieldTypes[i] === 'thumb') {
											pastedRow.push('');
											i++;
										}
										pastedRow.push($.trim(this));
										i++;
									});
									pastedArray.push(pastedRow);
								}
							});
							break;
						case 'word':
						default:
							clean = fieldPasteArea.htmlClean({
								allowedTags: ['table', 'tbody', 'tr', 'td']
							}).html();
							clean = clean.replace(/\n/mg, '').replace(/.*<table>/mg, '<table>').replace(/<\/table>.*/mg, '</table>');
							$(clean).find('tr').each(function() {
								var pastedRow = [];
								var tableData = $(this).find('td');
								if (tableData.length > 0) {
									var i = 0;
									tableData.each(function() {
										if (fieldTypes[i] === 'thumb') {
											pastedRow.push('');
											i++;
										}
										pastedRow.push($(this).text());
										i++;
									});
									pastedArray.push(pastedRow);
								}
							});
							break;
					}
					fieldList.find('li:gt(0)').remove();
					fieldListCounter = 1;
					if (mode === 'append') {
						pastedArray = $.merge(data.value, pastedArray);
					}
					prefillInputs(pastedArray);
					saveMultiValue();
					pasteBox.colorbox.close();
					return false;
				});
			}

			// transform the input
			if (field.val() !== '@INHERIT') {
				if (!field.hasClass('transformed')) {
					prepareMultiValue();

					field.hide();
					fieldEdit.hide();
					addElementEvents(fieldListElement);

					// sortable
					if (settings.mode !== 'single') {
						fieldList.sortable({
							stop: function() {
								saveMultiValue();
							},
							axis: 'y',
							helper: 'clone'
						});
					}
					prefillInputs(data.value);
					field.addClass('transformed');
				}

			} else {
				fieldHeading.hide();
				fieldList.hide();
				field.hide();
				fieldClear.hide();
				fieldPaste.hide();
			}
		});
	};
})(jQuery);

(function($) {

	$.fn.transformDatatable = function(options) {

		// Create some defaults, extending them with any options that were provided
		var settings = $.extend({
			fieldsettings: '',
			language: ''
		}, options);

		return this.each(function() {

			var field = $(this);
			var tvid = field.attr('id');
			var data = new Object();
			var fieldHeading = $('#' + tvid + 'heading');
			var fieldTable = $('#' + tvid + 'table');
			var fieldEdit = $('#' + tvid + 'edit');
			var fieldClear = $('#' + tvid + 'clear');
			var fieldPaste = $('#' + tvid + 'paste');
			var fieldEditForm = $('#' + tvid + 'editform');
			var fieldEditArea = $('#' + tvid + 'editarea');
			var tableAppend = '<img alt="' + settings.language.append + ' " src="../assets/tvs/multitv/css/images/add.png" /> ' + settings.language.append;
			var tableEdit = '<img alt="' + settings.language.edit + ' " src="../assets/tvs/multitv/css/images/application_form_edit.png" /> ' + settings.language.edit;
			var tableRemove = '<img alt="' + settings.language.remove + ' " src="../assets/tvs/multitv/css/images/delete.png" /> ' + settings.language.remove;
			var tableButtons = $('<ul>').addClass('actionButtons');
			var tableButtonAppend = $('<li>').attr('id', tvid + 'tableAppend').append($('<a>').attr('href', '#').html(tableAppend));
			var tableButtonEdit = $('<li>').attr('id', tvid + 'tableEdit').append($('<a>').attr('href', '#').addClass('disabled').html(tableEdit));
			var tableButtonRemove = $('<li>').attr('id', tvid + 'tableRemove').append($('<a>').attr('href', '#').addClass('disabled').html(tableRemove));
			var tableClasses = settings.fieldsettings['tableClasses'];
			var radioTabs = settings.fieldsettings['radioTabs'];
			var editBox;

			function clearInputs(el) {
				el.find('.tabEditor').each(function() {
					var editorId = $(this).attr('id');
					tinyMCE.execCommand('mceRemoveControl', false, editorId);
				});
				var inputs = el.find(':input');
				inputs.each(function() {
					var inputtype = $(this).attr('type');
					var inputid = $(this).attr('id');
					switch (inputtype) {
						case 'button':
							break;
						case 'reset':
							break;
						case 'submit':
							break;
						case 'checkbox':
						case 'radio':
							$(this).attr('checked', false);
							break;
						default:
							$(this).val('');
					}
				});
			}

			function saveMultiValue() {

				function compare(a, b) {
					if (a.MTV_RowId < b.MTV_RowId)
						return -1;
					if (a.MTV_RowId > b.MTV_RowId)
						return 1;
					return 0;
				}

				var currentValue = fieldTable.fnGetData();
				var saveValue = new Array();

				currentValue.sort(compare);

				$.each(currentValue, function() {
					var row = new Object();
					$.each(this, function(key, value) {
						if (key !== 'DT_RowId' && key !== 'MTV_RowId' && key.substr(0, 9) !== 'mtvRender') {
							row[key] = value;
						}
					});
					saveValue.push(row);
				});
				if (saveValue.length) {
					field.setValue($.toJSON({
						fieldValue: saveValue,
						fieldSettings: data.settings
					}));
				} else {
					field.setValue('');
				}
			}

			function prepareMultiColumns() {
				$.each(settings.fieldsettings.fieldcolumns, function(key, value) {
					if (this.render) {
						this.mRender = function(data, type, full) {
							return full[this.render];
						};
					}
				});
			}

			function prepareMultiValue() {
				var jsonValue = $.evalJSON(field.val().replace(/&#x005B;/g, '[').replace(/&#x005D;/g, ']').replace(/&#x007B;/g, '{').replace(/&#x007B;/g, '}'));
				if (jsonValue) {
					if (jsonValue.constructor === Array) {
						data.value = jsonValue;
						if (!data.settings) {
							data.settings = new Object();
						}
						data.settings.autoincrement = data.value.length + 1;
					} else {
						data.value = jsonValue.fieldValue;
						data.settings = jsonValue.fieldSettings;
					}
				} else {
					data.value = [];
					data.settings.autoincrement = 1;
				}
				$.each(data.value, function(key, value) {
					this.DT_RowId = key + 1;
					this.MTV_RowId = key + 1;
				});
			}

			function setThumbnail(path, name, el) {
				var thumbPath = path.split('/');
				var thumbName = thumbPath.pop();
				var thumbId = name.replace(/^(.*?)(\d*)$/, '#$1preview$2');
				if (thumbName !== '') {
					el.find(thumbId).html('<img src="../' + thumbPath.join('/') + '/.thumb_' + thumbName + '" />');
				} else {
					el.find(thumbId).html('');
				}
			}

			function addElementEvents(el) {
				// datepicker
				el.find('.DatePicker').click(function() {
					var picker = $(this).datetimepicker({
						changeMonth: true,
						changeYear: true,
						dateFormat: 'dd-mm-yy',
						timeFormat: 'h:mm:ss'
					});
					picker.datepicker('show');
				});
				// file field browser
				el.find('.browsefile').click(function() {
					var field = $(this).prev('input').attr('id');
					BrowseFileServer(field);
					return false;
				});

				// image field browser
				el.find('.browseimage').click(function() {
					var field = $(this).prev('input').attr('id');
					BrowseServer(field);
					return false;
				});
			}

			// open edit box
			function editRow(mode, selector) {
				if (selector && mode === 'edit') {
					var lineValue = fieldTable.fnGetData(selector);
					$.each(lineValue, function(key, value) {
						var fieldInput = $('#' + tvid + key + '_mtv');
						if (fieldInput.hasClass('image')) {
							setThumbnail(value, fieldInput.attr('name'), fieldEditArea);
						}
						$('#' + tvid + key + '_mtv').setValue(value);
					});
				} else {
					fieldEditForm.find('.formtabradio:first').addClass('active').find('input[type="radio"]').attr('checked', 'checked');
				}
				fieldEditForm.find('.mode').hide();
				fieldEditForm.find('.mode.' + mode).show();
				fieldEditForm.find('.editformtabs').easytabs({
					defaultTab: 'li:first-child',
					animate: false
				}).bind('easytabs:after', function() {
					editBox.colorbox.resize();
					fieldEditForm.find('.formtabradio input[type="radio"]').attr('checked', false);
					fieldEditForm.find('.formtabradio.active input[type="radio"]').attr('checked', 'checked');
				});
				fieldEditForm.find('.formtabradio input[type="radio"]').click(function() {
					$(this).siblings('a').click();
				});
				$.colorbox({
					inline: true,
					href: '#' + tvid + 'editform',
					width: '640px',
					close: '',
					open: true,
					opacity: '0.35',
					initialWidth: '0',
					initialHeight: '0',
					overlayClose: false,
					scrolling: false,
					onComplete: function() {
						if (!fieldEditArea.children('form').length) {
							fieldEditArea.wrapInner('<form/>');
						}
						if (lineValue && lineValue.fieldTab) {
							fieldEditArea.find('.editformtabs').easytabs('select', '#' + tvid + 'tab_radio_' + lineValue.fieldTab);
							fieldEditArea.find('.formtabradio input[type="radio"]').attr('checked', false);
							fieldEditArea.find('.formtabradio.active input[type="radio"]').attr('checked', 'checked');
						}
						fieldEditArea.find('.tabEditor').each(function() {
							var editorId = $(this).attr('id');
							tinyMCE.execCommand('mceAddControl', false, editorId);
							tinyMCE.DOM.setStyle(tinyMCE.DOM.get(editorId + '_ifr'), 'height', '200px');
							tinyMCE.DOM.setStyle(tinyMCE.DOM.get(editorId + '_tbl'), 'height', 'auto');
							tinyMCE.DOM.setStyle(tinyMCE.DOM.get(editorId + '_ifr'), 'width', '100%');
							tinyMCE.DOM.setStyle(tinyMCE.DOM.get(editorId + '_tbl'), 'width', '100%');
						});
						editBox.colorbox.resize();
					},
					onCleanup: function() {
						clearInputs(fieldEditArea);
					}
				});
			}

			// save/append edit box
			function saveRow(mode) {
				tinyMCE.triggerSave();
				var values = new Object();
				var saveTab = fieldEditForm.find('[name^="' + tvid + 'tab_radio_mtv"]').getValue();
				values.fieldTab = (saveTab !== '') ? saveTab : '';
				fieldEditArea.find(':input').each(function(i) {
					var key = $(this).attr('name').replace(/tv.\d(.*)_mtv/, '$1');
					if (key !== '') {
						values[key] = $(this).val();
					}
				});
				$.ajax({
					url: "../assets/tvs/multitv/multitv.connector.php",
					data: {
						action: 'preparevalue',
						id: $('form#mutate [name="id"]').val(),
						tvid: tvid,
						value: $.toJSON(values)
					},
					dataType: 'json',
					type: 'POST',
					success: function(answer) {
						answer = $.parseJSON(answer.msg);
						values = answer.fieldValue[0];
						if (mode === 'edit') {
							var selected = fieldTable.find('.row_selected')[0];
							var lineValue = fieldTable.fnGetData(selected);
							values.MTV_RowId = lineValue.MTV_RowId;
							values.DT_RowId = lineValue.DT_RowId;
							fieldTable.fnUpdate(values, selected);
						} else {
							values.MTV_RowId = fieldTable.fnGetData().length + 1;
							values.DT_RowId = fieldTable.fnGetData().length + 1;
							fieldTable.fnAddData(values);
						}
						clearInputs(fieldEditArea);
						saveMultiValue();
						editBox.colorbox.close();
						return false;
					},
					error: function(answer) {
						alert(answer.msg);
						return false;
					}
				});
			}

			// remove row
			function removeRow(selector) {
				$(selector).removeClass('row_selected');
				tableButtonEdit.find('a').addClass('disabled');
				tableButtonRemove.find('a').addClass('disabled');
				fieldTable.fnDeleteRow(selector);
				saveMultiValue();
			}

			// toggle row
			function toggleRow(row) {
				if (!$(row).hasClass('toggle')) {
					$(row).addClass('toggle').click(function() {
						if ($(this).hasClass('row_selected')) {
							$(this).removeClass('row_selected');
							tableButtonEdit.find('a').addClass('disabled');
							tableButtonRemove.find('a').addClass('disabled');
						}
						else {
							fieldTable.$('tr.row_selected').removeClass('row_selected');
							$(this).addClass('row_selected');
							tableButtonEdit.find('a').removeClass('disabled');
							tableButtonRemove.find('a').removeClass('disabled');
						}
					});
				}
			}

			// context menu
			function contextMenu(row, id) {
				if (!$(row).hasClass('context')) {
					$(row).addClass('context').contextMenu('context-menu-' + id, {
						tableEdit: {
							click: function(element) {
								fieldTable.$('tr.row_selected').removeClass('row_selected');
								$(element[0]).addClass('row_selected');
								tableButtonEdit.find('a').removeClass('disabled');
								tableButtonRemove.find('a').removeClass('disabled');
								editRow('edit', element[0]);
							},
							link: tableEdit
						},
						tableAppend: {
							click: function(element) {
								editRow('append', element[0]);
							},
							link: tableAppend
						},
						tableRemove: {
							click: function(element) {
								removeRow(element[0]);
							},
							link: tableRemove
						}
					});
				}
			}

			// transform the input
			if (field.val() !== '@INHERIT') {
				if (!field.hasClass('transformed')) {
					prepareMultiColumns();
					prepareMultiValue();
					field.hide();
					fieldEdit.hide();
					fieldTable.dataTable({
						sDom: '<"clear">lfrtip',
						aaData: data.value,
						aoColumns: settings.fieldsettings.fieldcolumns,
						bAutoWidth: false,
						oLanguage: dataTableLanguage,
						fnRowCallback: function(nRow, aData, iDisplayIndex) {
							contextMenu(nRow, iDisplayIndex);
							toggleRow(nRow);
						}
					}).rowReordering({
						fnAfterMove: function() {
							saveMultiValue();
							fieldTable.fnDraw();
						}
					}).addClass(tableClasses);

					// buttons above datatable
					fieldTable.parent().prepend(tableButtons);
					tableButtons.append(tableButtonAppend, tableButtonRemove, tableButtonEdit);

					// remove row event
					tableButtonRemove.find('a').click(function(e) {
						e.preventDefault();
						if ($(this).hasClass('disabled')) {
							return false;
						}
						removeRow(fieldTable.find('.row_selected')[0]);
					});

					// edit/append row event
					editBox = tableButtonEdit.add(tableButtonAppend).find('a').click(function(e) {
						e.preventDefault();
						if ($(this).hasClass('disabled')) {
							return false;
						}
						editRow($(this).parent().attr('id').replace(/tv\d+table/, '').toLowerCase(), fieldTable.find('.row_selected')[0]);
					});

					// save/append edit box
					fieldEditForm.find('.edit,.append').click(function() {
						saveRow($(this).hasClass('edit') ? 'edit' : 'append');
					});

					// close edit box
					fieldEditForm.find('.cancel').click(function() {
						editBox.colorbox.close();
						return false;
					});

					addElementEvents(fieldEditForm);
					field.addClass('transformed');
				}

			} else {
				fieldHeading.hide();
				fieldTable.hide();
				field.hide();
				fieldClear.hide();
				fieldPaste.hide();
			}
		});
	};
})(jQuery);
