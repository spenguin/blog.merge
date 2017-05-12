/*!
 * formBox - basic form loader built on fancybox
 * version: 1.0.0 (15 May 2014)
 * @requires jQuery v1.6 or later
 * Waits x seconds after page load, then displays formURL in a lightbox. Uses a cookie to only show once in y days
 * http://www.signupto.com 
 */
 
/* //// SET PARAMETERS HERE //// */

/* Set the form URL */
var formURL = 'https://forms.sign-up.to/36973/5547'

/* Width and height of the box */
var boxWidth = 450;
var boxHeight = 350;

/* Time in milliseconds before the pop-over should appear */
var openTime = 15000;

/* Time in days before pop-over should appear again */
var hideDays = 14;

/* //// END PARAMETERS //// */



function openBox() {
	$("#hidden_link").fancybox({ 'width': boxWidth, 'height': boxHeight }).trigger('click');
}

function createCookie(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		var expires = "; expires=" + date.toGMTString();
	} else
		var expires = "";
	document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ')
		c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0)
			return c.substring(nameEQ.length, c.length);
	}
	return null;
}

function doCookie() {
	if (readCookie('popForm')) {
		return;
	} else {
		createCookie('popForm', 'true', hideDays);
		setTimeout(openBox, openTime);
	}
}

$(document).ready(function() {

var link1 = "https://www.123-reg.co.uk/blog/learn-to-build-a-business-website-with-wordpress/";
var link2 = "https://www.123-reg.co.uk/blog/7-simple-yet-vital-seo-first-steps/";
var link3 = "https://www.123-reg.co.uk/blog/how-to-start-a-uk-online-business-ebook/";

	if(window.location.href != link1 && window.location.href != link2 && window.location.href != link3){
		$("body").append('<a id="hidden_link" class="fancybox fancybox.iframe" style="display:none;" href="' + formURL + '"></a>');
		$('.fancybox').fancybox();
		doCookie();
	}	
});
