
/*  slidebox-setup.js */

/*  Slidebox v0.4.1 Slideshow based on Lightbox JS
 *  Copyright (C) 2006 Olivier Ramonat
 *  
 *  For details, see the Slidebox web site : 
 *    http://olivier.ramonat.free.fr/slidebox/
 *  
 *  Slidebox is a script used to make a slideshow on the current page.  It
 *  is based on Lightbox JS, a simple script to overlay images.
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *  
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *  
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor,
 *  Boston, MA  02110-1301  USA
 *
 */

//  Your images should be hidden when page is loading.
//  The following function depends on your links to slidebox images
//  having a class of "hideImage"
//  e.g., <p class="hideImage"><a href=".image.jpg"
//  rel="lightbox_captions"><img src="thumbnail.jpg" /></p>
//  or <div class="hideImage"><a href=".image.jpg"
//  rel="lightbox_captions"><img src="thumbnail.jpg" /></div>

if (document.getElementById) {
  // createStyleRule(".hideImage", "visibility:hidden");
}

// slidebox calls slidebox_end_init after the page has loaded

function slidebox_end_init() {
   setElementStyleByClassName("hideImage", "visibility", "visible");
// to call any other javascript function after slidebox is intitalized,
// insert a line here, e.g., yourfunction();
}

// following lines commented by doze (moved to slidebox_lang files)

// If you would like to use a loading image, point to it in the next line
// var loadingImage = 'images/loading.gif';
// var closeButton  = 'images/close2.gif';

// Display a message
// var objuserMessage = 'Slidebox (press x to quit)';

// Uncomment these lines to use gif arrows
// next_link_image = 'images/next.gif';
// previous_link_image = 'images/previous.gif';

// Use the following format in your XML file :
// Add an image number to each display (optional)
// repeat your first image parameters as the last to enable an endless loop
// when pressing "Next".
// <?xml version="1.0" encoding="ISO-8859-1"?>
// <response>
// <source id='http://olivier.ramonat.free.fr/img/crw_0362_rj.jpg'>
// <caption>Monsieur Chat</caption>
// <number>1</number>
// </source>
// <source id='http://olivier.ramonat.free.fr/img/mesnager-dal001_rj.jpg'>
// <number>2</number>
// <caption>Mesnager</caption>
// </source>
// <source id='http://olivier.ramonat.free.fr/img/PA-615.jpg'>
// <caption>SpaceInvader</caption>
// <number>3</number>
// </source>
// <source id='http://olivier.ramonat.free.fr/img/crw_0362_rj.jpg'>
// <caption>Monsieur Chat</caption>
// <number>1</number>
// </response>

/*--------------------------------------------------------------------------*/
// END OF SLIDEBOX CONFIGURATION

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
