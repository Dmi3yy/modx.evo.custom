
/*HotKeys*/

myEventHandler = function(instance, event) {
  if(event.ctrlKey == true && event.keyCode == 83) {
		try {
			if(document.getElementById('Button1')) {
				document.getElementById('Button1').getElementsByTagName('a')[0].onclick();
			}
		}
		catch(event) {}

		return event.stop();
	}
	if(event.ctrlKey == true && event.keyCode == 69) {
		try {
			if(document.getElementById('Button1')) {
				document.getElementById('Button1').getElementsByTagName('select')[0].options[1].selected = true;
				document.getElementById('Button1').getElementsByTagName('a')[0].onclick();
			}
		}
		catch(event) {}

		return event.stop();
	}
	if(event.ctrlKey == true && event.keyCode == 66) {
		try {
			if(document.getElementById('Button1')) {
				document.getElementById('Button1').getElementsByTagName('select')[0].options[0].selected = true;
				document.getElementById('Button1').getElementsByTagName('a')[0].onclick();
			}
		}
		catch(event) {}

		return event.stop();
	}
	if(event.ctrlKey == true && event.keyCode == 81) {
		try {
			if(document.getElementById('Button1')) {
				document.getElementById('Button1').getElementsByTagName('select')[0].options[2].selected = true;
				document.getElementById('Button1').getElementsByTagName('a')[0].onclick();
			}
		}
		catch(event) {}
		return event.stop();
	}
}

/*FullScreen*/

function isFullScreen(cm) {
	return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
}
function winHeight() {
	return window.innerHeight || (document.documentElement || document.body).clientHeight;
}
function setFullScreen(cm, full) {
	var wrap = cm.getWrapperElement();
	var actions = document.getElementById('actions');
	if (full) {
		wrap.className += " CodeMirror-fullscreen";
		wrap.style.height = winHeight() + "px";
		document.documentElement.style.overflow = "hidden";
		top.mainMenu.hideTreeFrame();
		actions.className += " action-opacity";
	} else {
		wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
		wrap.style.height = "";
		document.documentElement.style.overflow = "";
		top.mainMenu.defaultTreeFrame();
		actions.className = actions.className.replace(" action-opacity", "");
	}
	cm.refresh();
}
CodeMirror.on(window, "resize", function() {
	var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
	if (!showing) return;
	showing.CodeMirror.getWrapperElement().style.height = winHeight() + "px";
});
