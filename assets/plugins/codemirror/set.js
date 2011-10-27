CodeMirror.fromTextArea(document.getElementById("code"), {
        lineNumbers: false,
		matchBrackets: false,
		mode: "application/x-httpd-php",
        indentUnit: 8,
        indentWithTabs: true,
        enterMode: "keep",
        tabMode: "shift"       
   });
