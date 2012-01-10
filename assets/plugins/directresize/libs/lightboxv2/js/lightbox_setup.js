/**  
 *  lightbox-setup.js by doze 
 *
 *  YOU DON'T NEED TO EDIT THIS FILE
 *
 *  This script is used to hide the thumbnails while javascripts are
 *  loading to prevent the images from opening in blank new windows, etc.
 *
 *  The code is hevily based of the setup file in Slidebox v0.4.1 
 *  by Olivier Ramonat (http://olivier.ramonat.free.fr/slidebox/)
 *  
 *  The following function depends on your lightbox images being wrapped
 *  in a element that has a class of "hideImage"
 *  
 *  Example:
 *
 *  <div class="hideImage"><a href=".image.jpg"
 *  rel="lightbox[29]"><img src="thumbnail.jpg" /></div>
 */
 
if (document.getElementById) {
   // createStyleRule(".hideImage", "visibility:hidden");
}

// lightbox calls lightbox_end_init after the page has loaded

function lightbox_end_init() {
   setElementStyleByClassName("hideImage", "visibility", "visible");
}

/*	dynamicCSS.js v1.0 <http://www.bobbyvandersluis.com/articles/dynamicCSS.php>
	Copyright 2005 Bobby van der Sluis
	This software is licensed under the CC-GNU LGPL <http://creativecommons.org/licenses/LGPL/2.1/>
*/

function createStyleRule(selector, declaration) {
	if (!document.getElementsByTagName || !(document.createElement || document.createElementNS)) return;
	var agt = navigator.userAgent.toLowerCase();
	var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
	var is_iewin = (is_ie && (agt.indexOf("win") != -1));
	var is_iemac = (is_ie && (agt.indexOf("mac") != -1));
	if (is_iemac) return; // script doesn't work properly in IE/Mac
	var head = document.getElementsByTagName("head")[0]; 
	var style = (typeof document.createElementNS != "undefined") ?  document.createElementNS("http://www.w3.org/1999/xhtml", "style") : document.createElement("style");
	if (!is_iewin) {
		var styleRule = document.createTextNode(selector + " {" + declaration + "}");
		style.appendChild(styleRule); // bugs in IE/Win
	}
	style.setAttribute("type", "text/css");
	style.setAttribute("media", "screen"); 
	head.appendChild(style);
	if (is_iewin && document.styleSheets && document.styleSheets.length > 0) {
		var lastStyle = document.styleSheets[document.styleSheets.length - 1];
		if (typeof lastStyle.addRule == "object") { // bugs in IE/Mac and Safari
			lastStyle.addRule(selector, declaration);
		}
	}
}

/*	DOM manipulation functions;
	Can only be used after a page has fully loaded
*/

function setElementStyleById(id, propertyName, propertyValue) {
	if (!document.getElementById) return;
	var el = document.getElementById(id);
	if (el) el.style[propertyName] = propertyValue;
}

function setElementStyle(element, propertyName, propertyValue) {
	if (!document.getElementsByTagName) return;
	var el = document.getElementsByTagName(element);
	for (var i = 0; i < el.length; i++) {
		el[i].style[propertyName] = propertyValue;
	}
}

function setElementStyleByClassName(cl, propertyName, propertyValue) {
	if (!document.getElementsByTagName) return;
	var re = new RegExp("(^| )" + cl + "( |$)");
	var el = document.all ? document.all : document.getElementsByTagName("body")[0].getElementsByTagName("*"); // fix for IE5.x
	for (var i = 0; i < el.length; i++) {
		if (el[i].className && el[i].className.match(re)) {
			el[i].style[propertyName] = propertyValue;
		}
	}
}
