<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                 login.php                               #
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
define('IN_AKKAR', true);
include('common.php');
if (is_logged_in()) {
	header('Location: ./userinfo.php');
	exit();
}

if ($_GET['resetpw']) {
	reset_password($_GET['resetpw']);
	exit();
}

$_SESSION['validator'] = sha1(strrev(md5(uniqid(rand(), true))));
include('header.php');
echo '
<script language="JavaScript" type="text/javascript" src="scripts/md5.js"></script>
<script language="JavaScript" type="text/javascript">
	var validator =\''.$_SESSION['validator'].'\';
	function validate_loginform() {
		if (document.loginform.brukernavn.value == \'\') {
			window.alert(\''.$LANG['JSBOX']['enter_username'].'\');
			document.loginform.brukernavn.focus();
			return false;
		}
		if (document.getElementById(\'fpass\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['enter_password'].'\');
			document.getElementById(\'fpass\').focus();
			return false;
		}
		document.loginform.passord.value = hex_md5(validator + hex_md5(document.getElementById(\'fpass\').value));
		document.getElementById(\'fpass\').value = \'\';
		return true;
	}
	
	function passwordreset() {
		if (document.loginform.brukernavn.value == \'\') {
			window.alert(\''.$LANG['JSBOX']['enter_username_or_email'].'\');
			document.loginform.brukernavn.focus();
			return false;
		} else {
			window.location=\'./login.php?resetpw=\' + document.loginform.brukernavn.value;
			return false;
		}
	}
	
</script>
<h3 align="center">'.$LANG['MISC']['login'].'</h3>
<div style="padding-top: 1em;padding-bottom: 10em;">
<form name="loginform" method="post" action="userinfo.php">
<input type="hidden" name="passord" value="">
<table align="center" class="bordered" cellspacing="0">
	<tr class="highlight">
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr class="highlight">
		<td><strong>'.$LANG['MISC']['username'].'</strong></td>
		<td><input type="text" size="20" tabindex="1" name="brukernavn"></td>
	</tr>
	<tr class="highlight">
		<td><strong>'.$LANG['MISC']['password'].'</strong></td>
		<td><input type="password" size="'; if (browsertype() == 'ie') { echo '22'; } else { echo '20'; } echo '" tabindex="2" id="fpass"></td>
	</tr>
	<tr class="highlight">
		<td colspan="2" align="center"><input type="checkbox" tabindex="3" name="husk" onClick="javascript:if (document.loginform.husk.checked) { return window.confirm(\''.$LANG['JSBOX']['login_cookie'].'\'); }"> '.$LANG['MISC']['remember_me'].'</td>
	</tr>
	<tr class="highlight">
		<td colspan="2" align="center">
			<table>
				<tr>
					<td align="right"><button type="button" onclick="javascript:return passwordreset();" tabindex="5">'.$LANG['MISC']['reset_password'].'</button></td>
					<td align="left"><button type="submit" tabindex="4" onClick="javascript:return validate_loginform();">'.$LANG['MISC']['login'].'</button></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
';
if ($_SESSION['resetmsg']) {
	echo '<h4 align="center">'.$_SESSION['resetmsg'].'</h4>';
	unset($_SESSION['resetmsg']);
}
if ($_SESSION['failed_attempt'] > 0) {
	echo '<h4 align="center">'.$LANG['ERROR']['login_failed'].'<br>'.$LANG['MISC']['failed_attempts'].': '.$_SESSION['failed_attempt'].'</h4>';
}
echo '
</div>
';
include('footer.php');
?>