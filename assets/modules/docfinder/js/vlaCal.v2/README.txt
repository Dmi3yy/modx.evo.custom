Vista-like Ajax Calendar version 2

Author  : R. Schoo aka rcz
Email   : rcz@base86.com
Website : http://www.base86.com
Script	: http://dev.base86.com/scripts/vista-like_ajax_calendar_version_2.html

----------------------------------- LICENSE -----------------------------------

Licensed under the Creative Commons Attribution- NonCommercial 3.0 License. 
What that means is: Use these files however you want, but don't redistribute 
without the proper credits and do not use this for commercial purposes.


------------------------------------ USAGE ------------------------------------

The vlaCalendar is both mootools version 1.11 and 1.2b2 beta compatible. 
Include the javascript files
    * mootools-release-v1.11.js and vlaCal-v2.js
      OR
    * mootools-beta-1.2b2.js and vlaCal-v2-for-mootools-beta-1.2b2.js
within the head of your HTML document.

Include either the compressed or normal version of both files. The normal 
versions contain whitespace and comments useful for developing purposes. The 
default path in which the files reside is jslib/ but they could ofcourse be 
moved to where ever it suit your needs.

Same story for the stylesheets. Include vlaCal-v2.css and other style files 
also within the head of your HTML document. The default path in which they 
reside is styles/.

Instantiation of the calandar or datepicker classes needs to happen after the 
DOM is ready. This is done by using the mootools domready event. This event 
has to be included in the head.

Both calendar and datepicker have a variety of options to style and format the
calendar to your needs. This is done by providing properties while instantiat-
ing the class. All properties are optional and reside in a javascript object 
which is passed as the second argument. An object in javascript is a collection 
of key-value pairs separated by commas and contained within curly-brackets {}.
For more information about options view the examples and the property list.

The PHP files, used to create calendar HTML, reside in the default inc/ 
directory. If you want to use a different path you will need to change the 
default filepath in the vlaCalendar javascript file or provide the filepath 
property.

For option property information and examples visit the script homepage:
http://dev.base86.com/scripts/vista-like_ajax_calendar_version_2.html