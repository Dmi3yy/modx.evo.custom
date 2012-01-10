/*
	Slimbox v1.3 - The ultimate lightweight Lightbox clone
	by Christophe Beyls (http://www.digitalia.be) - MIT-style license.
	Inspired by the original Lightbox v2 by Lokesh Dhakar.
*/

var Lightbox = {

	init: function(fileLoadingImage, fileBottomNavCloseImage, nextLinkImage, previousLinkImage, resizeDuration, resizeTransition, imageNrDesc, imageNrSep, nextKeys, prevKeys, closeKeys) {
		this.options = {}; //change start by doze
		this.options.resizeDuration = resizeDuration;
		this.options.resizeTransition = resizeTransition;
		this.options.initialWidth = 250;
		this.options.initialHeight = 250;
		this.options.animateCaption = true;
		this.options.previousLinkImage = previousLinkImage;
		this.options.nextLinkImage = nextLinkImage;
		this.options.closeLinkImage = fileBottomNavCloseImage;
		this.options.loadingImage = fileLoadingImage;
		this.options.imageNrDesc = imageNrDesc;
		this.options.imageNrSep = imageNrSep;
		this.options.nextKeys = nextKeys;
		this.options.prevKeys = prevKeys;
		this.options.closeKeys = closeKeys; //change end by doze
		
		this.anchors = [];
		$each(document.links, function(el){
			if (el.rel && el.rel.test(/^lightbox/i)){
				el.onclick = this.click.pass(el, this);
				this.anchors.push(el);
			}
		}, this);
		this.eventKeyDown = this.keyboardListener.bindAsEventListener(this);
		this.eventPosition = this.position.bind(this);

		this.overlay = new Element('div').setProperty('id', 'lbOverlay').injectInside(document.body);
		
		this.center = new Element('div').setProperty('id', 'lbCenter').setStyles({width: this.options.initialWidth+'px', height: this.options.initialHeight+'px', marginLeft: '-'+(this.options.initialWidth/2)+'px', display: 'none', background: '#fff url(' + this.options.loadingImage + ') no-repeat center'}).injectInside(document.body); //changed by doze
		this.image = new Element('div').setProperty('id', 'lbImage').injectInside(this.center);
		this.prevLink = new Element('a').setProperties({id: 'lbPrevLink', href: '#'}).setStyle('display', 'none').injectInside(this.image);
		this.nextLink = this.prevLink.clone().setProperty('id', 'lbNextLink').injectInside(this.image);
		this.prevLink.onclick = this.previous.bind(this);
		this.prevLink.setProperty('onmouseover','this.style.backgroundImage="url(' + this.options.previousLinkImage + ')"; this.style.backgroundRepeat="no-repeat"; this.style.backgroundPosition="left 15%";'); // added by doze
		var previousLinkImage = this.options.previousLinkImage; // added by doze
		this.prevLink.onmouseover = function() { this.style.backgroundImage="url(" + previousLinkImage + ")"; this.style.backgroundRepeat="no-repeat"; this.style.backgroundPosition="left 15%"; }; // added for IE by doze
		this.prevLink.setProperty('onmouseout','this.style.backgroundImage="";'); // added by doze
		this.prevLink.onmouseout = function() { this.style.backgroundImage=""; }; // added for IE by doze
		this.nextLink.onclick = this.next.bind(this);
		this.nextLink.setProperty('onmouseover','this.style.backgroundImage="url(' + this.options.nextLinkImage + ')"; this.style.backgroundRepeat="no-repeat"; this.style.backgroundPosition="right 15%";'); // added by doze
		var nextLinkImage = this.options.nextLinkImage; // added by doze
		this.nextLink.onmouseover = function() { this.style.backgroundImage="url(" + nextLinkImage + ")"; this.style.backgroundRepeat="no-repeat"; this.style.backgroundPosition="right 15%"; }; // added for IE by doze
		this.nextLink.setProperty('onmouseout','this.style.backgroundImage="";'); // added by doze
		this.nextLink.onmouseout = function() { this.style.backgroundImage=""; }; // added for IE by doze
		
		this.bottomContainer = new Element('div').setProperty('id', 'lbBottomContainer').setStyle('display', 'none').injectInside(document.body);
		this.bottom = new Element('div').setProperty('id', 'lbBottom').injectInside(this.bottomContainer);
		this.closeLink = new Element('a').setProperties({id: 'lbCloseLink', href: '#'}).injectInside(this.bottom); // changed by doze
		this.closeLink.onclick = this.overlay.onclick = this.close.bind(this); // changed by doze
		this.closeLink.setStyle('background', 'transparent url(' + this.options.closeLinkImage + ') no-repeat center'); // added by doze
		this.caption = new Element('div').setProperty('id', 'lbCaption').injectInside(this.bottom);
		this.number = new Element('div').setProperty('id', 'lbNumber').injectInside(this.bottom);
		new Element('div').setStyle('clear', 'both').injectInside(this.bottom);
		
		var nextEffect = this.nextEffect.bind(this);
		this.fx = {
			overlay: this.overlay.effect('opacity', {duration: 500}).hide(),
			resize: this.center.effects({duration: this.options.resizeDuration, transition: this.options.resizeTransition, onComplete: nextEffect}),
			image: this.image.effect('opacity', {duration: 500, onComplete: nextEffect}),
			bottom: this.bottom.effect('margin-top', {duration: 400, onComplete: nextEffect})
		};
		
		this.preloadPrev = new Image();
		this.preloadNext = new Image();
	},

	click: function(link){
		if (link.rel.length == 8) return this.show(link.href, link.title);

		var j, imageNum, images = [];
		this.anchors.each(function(el){
			if (el.rel == link.rel){
				for (j = 0; j < images.length; j++) if(images[j][0] == el.href) break;
				if (j == images.length){
					images.push([el.href, el.title]);
					if (el.href == link.href) imageNum = j;
				}
			}
		}, this);
		return this.open(images, imageNum);
	},

	show: function(url, title){
		return this.open([[url, title]], 0);
	},

	open: function(images, imageNum){
		this.images = images;
		this.position();
		this.setup(true);
		this.top = window.getScrollTop() + (window.getHeight() / 15);
		this.center.setStyles({top: this.top+'px', display: ''});
		this.fx.overlay.start(0.8);
		return this.changeImage(imageNum);
	},

	position: function(){
		this.overlay.setStyles({top: window.getScrollTop()+'px', height: window.getHeight()+'px'});
	},

	setup: function(open){
		var elements = $A(document.getElementsByTagName('object'));
		if (window.ie) elements.extend(document.getElementsByTagName('select'));
		elements.each(function(el){ el.style.visibility = open ? 'hidden' : ''; });
		var fn = open ? 'addEvent' : 'removeEvent';
		window[fn]('scroll', this.eventPosition)[fn]('resize', this.eventPosition);
		document[fn]('keydown', this.eventKeyDown);
		this.step = 0;
	},

	keyboardListener: function(e) {
		//start change by doze
		if (e == null) { // ie
			var keycode = e.keyCode;
		} else { // mozilla
			var keycode = e.which;
		}

		var key = String.fromCharCode(keycode).toLowerCase();
		
		var i=0;
 
		for (i=0;i<this.options.nextKeys.length;i++){
			if(this.options.nextKeys[i] == key){
				this.next();
			}
		}

		for (i=0;i<this.options.prevKeys.length;i++){
			if(this.options.prevKeys[i] == key){
				this.previous(); break;
			}
		}
		
		for (i=0;i<this.options.closeKeys.length;i++){
			if(this.options.closeKeys[i] == key){
				this.close(); break;
			}
		}
		
		// end change by doze 
		
		/*switch(e.keyCode) {
			case 27: case 88: case 67: this.close(); break;
			case 37: case 80: this.previous(); break;	
			case 39: case 78: this.next();
		}*/
	},

	previous: function(){
		return this.changeImage(this.activeImage-1);
	},

	next: function(){
		return this.changeImage(this.activeImage+1);
	},

	changeImage: function(imageNum){
		if (this.step || (imageNum < 0) || (imageNum >= this.images.length)) return false;
		this.step = 1;
		this.activeImage = imageNum;

		this.bottomContainer.style.display = this.prevLink.style.display = this.nextLink.style.display = 'none';
		this.fx.image.hide();
		this.center.className = 'lbLoading';

		this.preload = new Image();
		this.preload.onload = this.nextEffect.bind(this);
		this.preload.src = this.images[imageNum][0];
		return false;
	},

	nextEffect: function(){
		switch (this.step++){
		case 1:
			this.center.className = '';
			this.image.style.backgroundImage = 'url('+this.images[this.activeImage][0]+')';
			this.image.style.width = this.bottom.style.width = this.preload.width+'px';
			this.image.style.height = this.prevLink.style.height = this.nextLink.style.height = this.preload.height+'px';

			this.caption.setHTML(this.images[this.activeImage][1] || '');
			this.number.setHTML((this.images.length == 1) ? '' : this.options.imageNrDesc + ' '+(this.activeImage+1)+' '+this.options.imageNrSep+' '+this.images.length); // changed by doze
			
			if (this.activeImage) this.preloadPrev.src = this.images[this.activeImage-1][0];
			if (this.activeImage != (this.images.length - 1)) this.preloadNext.src = this.images[this.activeImage+1][0];
			if (this.center.clientHeight != this.image.offsetHeight){
				this.fx.resize.start({height: this.image.offsetHeight});
				break;
			}
			this.step++;
		case 2:
			if (this.center.clientWidth != this.image.offsetWidth){
				this.fx.resize.start({width: this.image.offsetWidth, marginLeft: -this.image.offsetWidth/2});
				break;
			}
			this.step++;
		case 3:
			this.bottomContainer.setStyles({top: (this.top + this.center.clientHeight)+'px', height: '0px', marginLeft: this.center.style.marginLeft, display: ''});
			this.fx.image.start(1);
			break;
		case 4:
			if (this.options.animateCaption){
				this.fx.bottom.set(-this.bottom.offsetHeight);
				this.bottomContainer.style.height = '';
				this.fx.bottom.start(0);
				break;
			}
			this.bottomContainer.style.height = '';
		case 5:
			if (this.activeImage) this.prevLink.style.display = '';
			if (this.activeImage != (this.images.length - 1)) this.nextLink.style.display = '';
			this.step = 0;
		}
	},

	close: function(){
		if (this.step < 0) return;
		this.step = -1;
		if (this.preload){
			this.preload.onload = Class.empty;
			this.preload = null;
		}
		for (var f in this.fx) this.fx[f].stop();
		this.center.style.display = this.bottomContainer.style.display = 'none';
		this.fx.overlay.chain(this.setup.pass(false, this)).start(0);
		return false;
	}
};

Window.onDomReady(Lightbox.init.pass(new Array(fileLoadingImage, fileBottomNavCloseImage, nextLinkImage, previousLinkImage, resizeDuration, resizeTransition, imageNrDesc, imageNrSep, nextKeys, prevKeys, closeKeys), Lightbox)); //changed by doze
