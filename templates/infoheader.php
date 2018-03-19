<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              infoheader.php                             #
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
if (!defined('IN_AKKAR')) {
	exit('Access violation.');
}

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
	<title>'.$config['arrgruppenavn'].' AKKAR-'.$config['version'].'</title>
';
if ((browsertype() == 'ie') && (is_file('styles/'.$config['style'].'/iestyle.css')))
{
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/iestyle.css" type="text/css">';
}
elseif ((browsertype() == 'opera') && (is_file('styles/'.$config['style'].'/operastyle.css')))
{
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/operastyle.css" type="text/css">';
}
else
{
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/style.css" type="text/css">';
}
echo '
	<link rel="StyleSheet" href="styles/'.$config['style'].'/common.css" type="text/css">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
';
include_once('scripts/js_vars.php');
echo '
	<script language="JavaScript" type="text/javascript" src="scripts/animbuttons.js.php"></script>
	<script language="JavaScript" type="text/javascript" src="scripts/overlib.js"></script>
	<script language="JavaScript" type="text/javascript" src="scripts/functions.js" charset="windows-1252"></script>
<style type="text/css">
button,select,form.noprint {
	display: none;
	visibility: hidden;
}

.visible {
	display: block;
	visibility: visible;
}
</style>
</head>
<body>
<table class="main" style="margin-top: 15px;" align="center">
	<tr>
		<td class="maincol">
		<h1 align="center">'.$livenavn.'</h1>
		<h2 align="center">'.$config['arrgruppenavn'].'</h2>
';
?>
