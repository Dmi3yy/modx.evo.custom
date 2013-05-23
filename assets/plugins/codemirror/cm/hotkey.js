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