var vlaCalendar = new Class({
	'slideDuration': 500,
	'fadeDuration': 500,
	'transition': Fx.Transitions.Quart.easeOut,
	
	'startMonday': false,
	'leadingZero': true,
	'twoDigitYear': false,
	'filePath': 'inc/',
	
	'style': '',
	
	initialize: function(_container, _arguments) {
		//Add the provided arguments to this object by extending
		if(_arguments) $extend(this, _arguments);
		
		this.loading = false;
		this.container = _container = $(_container);
		var _class = this;
		
		//Insert the base into the container and initialize elements
		this.u('base', '', function() { 
			_class.mainLoader = _container.getElement('div[class=loaderA]');
			_class.tempLoader = _container.getElement('div[class=loaderB]');
			_class.label 	  = _container.getElement('span[class=label]');
			_class.arrowLeft  = _container.getElement('div[class=arrowLeft]');
			_class.arrowRight = _container.getElement('div[class=arrowRight]');				
			_class.initializeCalendarFunctions();
		}, _container);
	},
	
	initializeCalendarFunctions: function() {
		this.resetArrows();
		
		//Retrieve data (label, timestamp etc) which are stored as a Json string in a table attribute
		var vars = Json.evaluate(this.mainLoader.getElement('table').getProperty('summary'));
		var _class = this; 
		
		//Change the label
		this.label.removeClass('noHover').setHTML(vars.label)
			.onclick = vars.parent ? function() { _class.u(vars.parent, 'ts=' + vars.ts + '&parent=' + vars.current, function() { _class.fade(); }); } : null;
			
		//Add arrow click events
		if(vars.hide_left_arrow) this.hideLeftArrow();
		else if(vars.hide_right_arrow) this.hideRightArrow();
		
		this.arrowLeft.onclick = function() { _class.u(vars.current, 'ts=' + vars.pr_ts, function() { _class.slideLeft(); }); }
		this.arrowRight.onclick = function() { _class.u(vars.current, 'ts=' + vars.nx_ts, function() { _class.slideRight(); }); }		
		
		//Add cell click events
		var clickables = this.mainLoader.getElements('td');
		switch(vars.current) {
			case 'month':
				if(this.picker) {
					clickables.each(function(_clickable) {
						_clickable.onclick = function() { _class.pick(Json.evaluate(_clickable.getProperty('date'))); }
					});
				}
				break;
			case 'year':
				clickables.each(function(_clickable) {
					_clickable.onclick = function() { _class.u('month', 'ts=' + _clickable.getProperty('ts'), function() { _class.fade(); }); }
				});
				break;
			case 'decade':
				this.label.addClass('noHover');
				clickables.each(function(_clickable) {
					_clickable.onclick = function() { _class.u('year', 'ts=' + _clickable.getProperty('ts') + '&m_ts=' + _clickable.getProperty('m_ts'), function() { _class.fade(); }); }
				});
				break;
		}
	},
	
	//Ajax update function which handles all requests
	u: function(_url, _pars, _onComplete, _id) {
		var _class = this;
		if(!this.loading) {
			this.loading = true;
			//if(this.arrowLeft) this.resetArrows();
			new Ajax( this.filePath + _url + '.php',
					  { method: 'post',
						update: _id ? _id : this.tempLoader,
						data: _pars + '&picker=' + (this.picker ? 1 : 0) + '&startMonday=' + (this.startMonday ? 1 : 0) + '&leadingZero=' + (this.leadingZero ? 1 : 0)
							   + '&twoDigitYear=' + (this.twoDigitYear ? 1 : 0) + '&style=' +  this.style,
						onComplete: function() { _onComplete(); _class.loading = false; },
						evalScripts: true } ).request();
		}
	},
	
	slideLeft: function() {
		var _class = this;
		this.loading = true;
		this.tempLoader.effect('margin-left', {duration: this.slideDuration, transition: this.transition}).start(-164, 0);
		this.mainLoader.effect('margin-left', {duration: this.slideDuration, transition: this.transition}).start(0, 164).
			addEvent('onComplete', function() { _class.loading = false; } );
		this.switchLoaders();
	},
	
	slideRight: function() {
		var _class = this;
		this.loading = true;
		this.mainLoader.effect('margin-left', {duration: this.slideDuration, transition: this.transition}).start(0, -164);
		this.tempLoader.effect('margin-left', {duration: this.slideDuration, transition: this.transition}).start(164, 0).
			addEvent('onComplete', function() { _class.loading = false; } );
		this.switchLoaders();
	},
	
	fade: function() {
		var _class = this;
		this.loading = true;
		this.tempLoader.setStyles({'opacity': 0, 'margin-left': 0});
		this.mainLoader.effect('opacity', {duration: this.fadeDuration, transition: this.transition}).start(1, 0);
		this.tempLoader.effect('opacity', {duration: this.fadeDuration, transition: this.transition}).start(0, 1)
			.addEvent('onComplete',function() {
				_class.tempLoader.setStyles({'opacity': 1, 'margin-left': -999});
				_class.loading = false;
			});
		this.switchLoaders();
	},
	
	switchLoaders: function() {
		this.mainLoader = this.mainLoader.className == 'loaderA' ? this.container.getElement('div[class=loaderB]') : this.container.getElement('div[class=loaderA]');
		this.tempLoader = this.tempLoader.className == 'loaderA' ? this.container.getElement('div[class=loaderB]') : this.container.getElement('div[class=loaderA]');
		this.initializeCalendarFunctions();
	},
	
	resetArrows: function() {
		this.arrowLeft.setStyle('visibility', 'visible');
		this.arrowRight.setStyle('visibility', 'visible');
	},
	
	hideLeftArrow: function() {
		this.arrowLeft.setStyle('visibility', 'hidden');
	},
	
	hideRightArrow: function() {
		this.arrowRight.setStyle('visibility', 'hidden');
	} 
});

var vlaDatePicker = vlaCalendar.extend({
	'separateInput': false,
	'separator': '/',
	'format': 'd/m/y',
	'openWith': null,
	'alignX': 'right',
	'alignY': 'inputTop',
	'offset': { 'x': 0, 'y': 0 },
	'style': '',
	'ieTransitionColor' : '#ffffff',
	
	initialize: function(_element, _arguments) {
		//Add the provided arguments to this object by extending
		if(_arguments) $extend(this, _arguments);
		
		this.element = $(_element);
		
		//Check if the user wants multiple input
		if(this.separateInput) {
			this.element.day   = this.element.getElement('input[name=' + this.separateInput.day + ']');
			this.element.month = this.element.getElement('input[name=' + this.separateInput.month + ']');
			this.element.year  = this.element.getElement('input[name=' + this.separateInput.year + ']');
		}
		
		//Create the picker and calendar and inject in in the body
		this.picker = new Element('div', { 'class': 'vlaCalendarPicker' + (this.style != '' ? ' ' + this.style : '') }).injectTop($E('body'));
		this.pickerContent = new Element('div', { 'class': 'pickerBackground' }).injectTop(this.picker);
		this.position();
		this.parent(this.pickerContent);
		
		//Add events for showing and hiding the picker
		var _class = this;
		(this.openWith ? $(this.openWith) : this.element)
			.addEvent('focus',  function() { _class.show(); })
			.addEvent('click',  function() { _class.openWith ? _class.toggle() : _class.show(); })
			.addEvent('change', function() { _class.hide(); });
		
		//If the datepicker is visible an outside click makes it hide
		document.addEvent('click', function(e) { if(_class.outsideHide && _class.outsideClick(e, _class.picker)) _class.hide(); });
		
		this.visible = false;
		this.outsideHide = false;
	},
	
	position: function() {
		//Determine where the picker needs to be positioned
		var top, left;
		switch(this.alignX) {
			case 'left':
				left = this.element.getLeft();
				break;
			case 'center':
				var pickerMiddle = (parseInt(this.pickerContent.getStyle('width')) / 2);
				if(pickerMiddle == 0) pickerMiddle = 83;
				left = this.element.getLeft() + (this.element.getSize().size.x / 2) - pickerMiddle -
						((parseInt(this.pickerContent.getStyle('padding-left')) + parseInt(this.pickerContent.getStyle('padding-right'))) / 2);
				break;
			case 'right': default:
				left = this.element.getLeft() + this.element.getSize().size.x;
				break;
		}
		switch(this.alignY) {
			case 'bottom':
				top = this.element.getTop() + this.element.getSize().size.y;
				break;
			case 'top': 
				top = this.element.getTop() - parseInt(this.pickerContent.getStyle('height')) - 
					(parseInt(this.pickerContent.getStyle('padding-top')) + parseInt(this.pickerContent.getStyle('padding-bottom')));
				break;
			case 'inputTop': default:
				top = this.element.getTop();
		}
		
		if(this.isNumber(this.offset.x)) left += this.offset.x;
		if(this.isNumber(this.offset.y)) top += this.offset.y;
		
		this.picker.setStyles({ 'top': top, 'left': left });
	},
	
	show: function() {
		this.position();
		if(!this.visible) {
			this.visible = true;
			var _class = this;
			this.picker.setStyles({ 'opacity': 0, 'display': 'inline' });
			if(window.ie7) this.picker.setStyle('background-color', this.ieTransitionColor); // <- Ugly transition fix for IE browsers
			this.picker.effect('opacity', {duration: this.fadeDuration, transition: this.transition}).start(0, 1)
				.addEvent('onComplete', function() { if(window.ie7) _class.picker.setStyle('background-color', 'transparent'); _class.outsideHide = true; });
		}
	},
	
	hide: function() {
		if(this.visible) {
			this.visible = false;
			var _class = this;
			if(window.ie7) this.picker.setStyle('background-color', this.ieTransitionColor); // <- Ugly transition fix for IE browsers
			this.picker.effect('opacity', { duration: this.fadeDuration, transition: this.transition }).start(1, 0)
				.addEvent('onComplete', function() { _class.picker.setStyle('display', 'none'); _class.outsideHide = false; } );
		}
	},
	
	toggle: function() {
		if(this.visible) this.hide();
		else this.show();
	},
	
	pick: function(_date) {
		if(this.separateInput) {
			if(this.element.day) this.element.day.value	= _date.day;
			if(this.element.month) this.element.month.value = _date.month;
			if(this.element.year) this.element.year.value = _date.year;
			this.hide();
		} else {
			switch(this.format) {
				case "m/d/y": this.element.value = _date.month + this.separator + _date.day + this.separator + _date.year; break;
				case "y/m/d": this.element.value = _date.year + this.separator + _date.month + this.separator + _date.day; break;
				case "y/d/m": this.element.value = _date.year + this.separator +  _date.day + this.separator + _date.month; break;
				case "d/m/y": default: this.element.value = _date.day + this.separator + _date.month + this.separator + _date.year;
			}
			this.hide();
		}
	},
	
	isNumber: function(number) {
		return (number >= 0) || (number < 0) ? true : false;
	},
	
	outsideClick: function(event, element) {
		var mousePos = this.getMousePos(event);
		var elementData = element.getCoordinates();
		
		return (mousePos.x > elementData.left && mousePos.x < (elementData.left + elementData.width)) &&
			   (mousePos.y > elementData.top  && mousePos.y < (elementData.top + elementData.height)) ? false : true;
	},
	
	getMousePos: function(e) {	
		if(document.all) {
			return { 'x': window.event.clientX + window.getScrollLeft(),
					 'y': window.event.clientY + window.getScrollTop() };
		} else {
			return { 'x': e.pageX,
					 'y': e.pageY };
		}
	}
});