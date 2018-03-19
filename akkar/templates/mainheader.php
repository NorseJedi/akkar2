<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              mainheader.php                             #
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
	<title>'.$config['arrgruppenavn'].' ~::~ AKKAR-'.$config['version'].' ~::~</title>

';
if ((browsertype() == 'ie') && (is_file('styles/'.$config['style'].'/iestyle.css'))) {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/iestyle.css" type="text/css">';
} elseif ((browsertype() == 'opera') && (is_file('styles/'.$config['style'].'/operastyle.css'))) {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/operastyle.css" type="text/css">';
} else {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/style.css" type="text/css">';
}

if (browsertype() == 'ie') {
	echo '<link rel="shortcut icon" href="images/favicon.ico">';
} else {
	echo '<link rel="icon" href="images/favicon.png" type="image/png">';
}

echo '
	<link rel="StyleSheet" href="styles/'.$config['style'].'/common.css" type="text/css">

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
';
	include_once('scripts/js_vars.php');
echo '
	<script language="JavaScript" type="text/javascript" src="scripts/animbuttons.js.php"></script>
	<script language="JavaScript" type="text/javascript" src="scripts/overlib.js"></script>
	<script language="JavaScript" type="text/javascript" src="scripts/overlib_draggable.js"></script>
';
if ($config['use_overlib_fade'] == 1) {
	echo '
	<script language="JavaScript" type="text/javascript" src="scripts/overlib_fade.js"></script>
	';
}
echo '
	<script language="JavaScript" type="text/javascript" src="scripts/functions.js" charset="windows-1252"></script>
</head>
<body'; if (basename($_SERVER['PHP_SELF']) == 'login.php') { echo ' onLoad="javascript:document.loginform.brukernavn.focus();"'; } echo '>
<noscript>
<div align="center" style="border: 2px solid red;padding: 5px;margin: 5px;">
<br /><br /><br /><h4 class="nospace"><span class="red">CRITICAL CLIENT ERROR</span><br />JavaScript is <u>required</u> for AKKAR to function.<br /></h4>
<p style="font-size: 8pt;">If your browser supports JavaScript you must enable it in your browser settings.
<br />If your browser does not support JavaScript, you\'ll have to switch to a browser that does - AKKAR recommends <a href="http://www.mozilla.org/products/firefox/" target="_blank">Firefox</a>.</p>
<br /><br /><br /></div>
</noscript>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<table class="main" border="0">
	<tr>
		<td>
';
	if ($_SESSION['message']) {
		echo '
			<table class="statuswindow" align="center">
			<tr>
				<td><strong>'.$LANG['MISC']['message'].'</strong></td>
			</tr>
			<tr>
				<td><em>'.$_SESSION['message'].'</em></td>
			</tr>
			</table>
		';
		unset($_SESSION['message']);
	}
echo '
		</td>
		<td class="banner">
			<img src="'.$styleimages['logo'].'" alt="'.$arrgruppenavn.'">
		</td>
	</tr>
	<tr>
		<td class="navbar" nowrap>
';
include('nav.php');
echo '
		</td>
		<td class="maincol">
		<h1 align="center">'; if (!$spillnavn) { echo $config['arrgruppenavn']; } else { echo $spillnavn; } echo '</h1>
';
if (basename($_SERVER['PHP_SELF']) != 'login.php') {
	echo '
		<div align="right">
		'.button('help', '', 'onClick', 'javascript:openHjelp(\''.$hjelpemne.'\')').'
		'.button('print', '', 'onClick', 'window.open(\''.htmlentities($whereiam).'&utskrift=yes\'); return false;').'
		</div>
	';
}
?>
