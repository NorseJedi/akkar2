/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               functions.js                              #
#                            -------------------                          #
#                                                                         #
#   copyright (C) 2004 Roy W. Andersen                                    #
#   email: ensnared@gmail.com                                             #
#                                                                         #
###########################################################################

###########################################################################
#                                                                         #
#  This program is free software; you can redistribute it and/or modify   #
#  it under the terms of the GNU General Public License as published by   #
#  the Free Software Foundation; either version 2 of the License, or      #
#  (at your option) any later version.                                    #
#                                                                         #
###########################################################################
*/

// IE handles the CSS visibility-property differently than everyone else.
switch (browsertype) {
	case "ie":
	var tr_showproperty = 'block';
	var tbody_showproperty = 'block';
	break;
	default:
	var tr_showproperty = 'table-row';
	var tbody_showproperty = 'table-row-group';
}

// Generic delete confirmation
function confirmDelete(object, url) {
	if (window.confirm(str_replace('<object>', object, msg_confirm_delete)) == false) {
		return false;
	} else {
		window.location=url;
	}
}

// Delete directory confirmation
function confirmDeleteDir(object, url) {
	if (window.confirm(str_replace('<object>', object, msg_confirm_delete_dir)) == false) {
		return false;
	} else {
		window.location=url;
	}
}

// Delete game confirmation
function confirmDeleteSpill(navn, url) {
	if (window.confirm(msg_confirm_delete_game) == false) {
		return false;
	} else {
		window.location=url;
	}
}

// Confirm character-lock override
function confirmOverride (url) {
	if (window.confirm(msg_confirm_override_lock) == false) {
		return false;
	} else {
		window.location=url;
	}
}

// Generic confirmation
function confirmAction(tekst, url) {
	if (window.confirm(tekst) == false) {
		return false;
	} else {
		window.location=url;
	}
}

/*
// Browsertype detection. We generally only need to distinguish between IE, Opera and "everyone else"
// This function is now obsolete, we now detect the browsertype serverside only and pass a variable to the client.
function browsertype() {
	if (navigator.userAgent.indexOf('MSIE') > -1) {
		return "ie";
	}
	if (navigator.userAgent.indexOf('Opera') > -1) {
		return "opera";
	}
	return "mozilla";
}
*/

// Very simple Email-validation.
function validMail(address) {
	var expr = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	if (expr.test(address)) {
		return true;
	} else {
		return false;
	}
}

// str_replace() - just like the PHP version. Used mainly in the convert_funky_letters() function, which is also performed serverside.
function str_replace(match, replacement, string) {
	var result = '';
	rexp = new RegExp(match, 'gi');
	result = string.replace(rexp, replacement);
	return result;
}

// Convert weird characters pasted from MS Word which oddly enough break form-submissions only on Internet Explorer
function convert_funky_letters(form) {
	for(i = 0; i < form.length;i++){
		el=form[i];
		if ((el.type == 'text') || (el.type == 'textarea')) {
			el.value = str_replace("–", '-', str_replace('”', '"', str_replace("…",'...', str_replace("’", "'", str_replace("™", '(TM)', str_replace("©", '(C)', str_replace("®", '(R)', el.value)))))));
		}
	}
	return false;
}

// Check if a file exists before uploading it to the filesystem
function file_exists(file) {
	var pos = Math.max(file.lastIndexOf('/'), file.lastIndexOf('\\'));
	var basename = file.substr(pos + 1).toLowerCase();
	if (filer[basename] == true) {
		return true;
	}
	return false;
}

// Hide an element
function hide(element) {
	element.style.visibility = 'hidden';
	element.style.display = 'none';
}

// Unhide/show an element
function show(element, type) {
	element.style.visibility = 'visible';
	element.style.display = type;
}

// Open the help-window. Opera doesn't allow us to close the old one first should it be open, other browsers do.
function openHjelp(emne) {
	if (browsertype != "opera") {
		myRef = window.open('','akkar_hjelp');
		myRef.close();
	}
	window.open('hjelp.php?hjelp=' + emne, 'akkar_hjelp', 'menubar=no, resizable=yes, left=0, top=0, toolbar=no, scrollbars=yes, status=no, height=480, width=480');
}

// Open the info-window. Opera doesn't allow us to close the old one first should it be open, other browsers do.
function openInfowindow(url, xsize, ysize) {
	if (!xsize) { xsize = 500; }
	if (!ysize) { ysize = 640; }
	if (url.indexOf('?') > 0) {
		url = url + '&infowindow=yes';
	} else {
		url = url + '?infowindow=yes';
	}
	if (browsertype != "opera") {
		winRef = window.open('','infowindow');
		winRef.close();
	}
	window.open(url, 'infowindow', 'menubar=no, resizable=yes, left=0, top=0, toolbar=no, scrollbars=yes, status=no, height=' + ysize + ', width=' + xsize);
}

// Filter the lists by hiding and showing the rows containing filtered text
function filter_list(filter, type) {
	var filter = filter.toLowerCase();
	cells = document.getElementsByTagName('td');
	for (i = 0; i < cells.length; i++) {
		var id = cells[i].id;
		if (id.indexOf(type + '_') > -1) {
			var title = document.getElementById(id).title.toLowerCase();
			if (title.indexOf(filter) == -1) {
				hide(document.getElementById(id).parentNode);
			} else {
				show(document.getElementById(id).parentNode, tr_showproperty);
			}
		}
	}
	filterboxes = document.getElementsByTagName('input');
	for (j = 0; j < filterboxes.length; j++) {
		var title = filterboxes[j].title;
		var titlecheck = type + '_filter';
		if ((title != titlecheck) && (title.indexOf('_filter') > -1)) {
			if (filter == '') {
				filterboxes[j].disabled = false;
			} else {
				filterboxes[j].disabled = true;
			}
		}
	}
}

// Function to show or hide things like navbar-sections and filterboxes
function showhide(element) {
	var current_vis = document.getElementById(element).style.visibility;
	if ((current_vis == 'visible') || (!current_vis)) {
		setCookie(ckprefix + '_' + str_replace(' ', '_', element),'hidden');
		var new_vis = 'hidden';
	} else {
		setCookie(ckprefix + '_' + str_replace(' ', '_', element),'visible');
		var new_vis = 'visible';
	}
	if (new_vis == 'visible') {
		show(document.getElementById(element), tbody_showproperty);
	} else {
		hide(document.getElementById(element));
	}
}

// Date validation, based on validDate() by Justin Klein Keane  Copyright (C) 2004
// http://www.madirish.net/tech.php?section=1&article=118
function validDate(day, month, year) {
	var myRegex = new RegExp("^[0-9]{4}\-([0][0-9]|[1][0-2])\-([0-2][0-9]|[3][0-1])$");
	if ((year%4 == 0) && (day > 29) && (month == 2)) {
		return false;
	}
	else if ((year%4 != 0) && (day > 28) && (month == 2)) {
		return false;
	}
	else if ((day > 30) && (month == 4 || month == 6 || month == 9 || month == 11)) {
		return false;
	}
	else {
		return true;
	}
	return true;
}


//////////////////////
// Cookie-functions //
//////////////////////

/**
* Sets a Cookie with the given name and value.
*
* name       Name of the cookie
* value      Value of the cookie
* [expires]  Expiration date of the cookie (default: end of current session)
* [path]     Path where the cookie is valid (default: path of calling document)
* [domain]   Domain where the cookie is valid
*              (default: domain of calling document)
* [secure]   Boolean value indicating if the cookie transmission requires a
*              secure transmission
*/
function setCookie(name, value, expires, path, domain, secure)
{
	document.cookie= name + "=" + escape(value) +
	((expires) ? "; expires=" + expires.toGMTString() : "") +
	((path) ? "; path=" + path : "") +
	((domain) ? "; domain=" + domain : "") +
	((secure) ? "; secure" : "");
}

/**
* Gets the value of the specified cookie.
*
* name  Name of the desired cookie.
*
* Returns a string containing value of specified cookie,
*   or null if cookie does not exist.
*/
function getCookie(name)
{
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1)
	{
		begin = dc.indexOf(prefix);
		if (begin != 0) return null;
	}
	else
	{
		begin += 2;
	}
	var end = document.cookie.indexOf(";", begin);
	if (end == -1)
	{
		end = dc.length;
	}
	return unescape(dc.substring(begin + prefix.length, end));
}

/**
* Deletes the specified cookie.
*
* name      name of the cookie
* [path]    path of the cookie (must be same as path used to create cookie)
* [domain]  domain of the cookie (must be same as domain used to create cookie)
*/
function deleteCookie(name, path, domain)
{
	if (getCookie(name))
	{
		document.cookie = name + "=" +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		"; expires=Thu, 01-Jan-70 00:00:01 GMT";
	}
}
