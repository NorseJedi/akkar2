<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              editperson.php                             #
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
include('header.php');


if ($_GET['nyperson']) {
	$person = array();
	$person['bilde'] = $styleimages['no_mugshot_m'];
	if ($_GET['type']) {
		$person['type'] = $_GET['type'];
	} else {
		$person['type'] = 'spiller';
	}
} else {
	$person = get_person($_GET['person_id']);
	$person['bilde'] = mugshot($person);
	$dato = explode('-', $person['fodt']);
	$dag = $dato[2];
	$mnd = $dato[1];
	$aar = $dato[0];
	if ($person['telefon'] == 0) {
		$person['telefon'] = '';
	}
	if ($person['mobil'] == 0) {
		$person['mobil'] = '';
	}
}
$buttons = '
<table align="center">
	<tr>
		<td align="right"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td align="right"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td align="left"><button type="submit" onClick="javascript:return validate_person();">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
';
echo '
<script language="JavaScript" type="text/javascript">
function checkDisable(check, element) {
	if (check.checked == true) {
		element.disabled = true;
	} else {
		element.disabled = false;
	}
}
</script>
';
if ($_GET['nyperson']) {
	if ($person['type'] == 'spiller') {
		echo '
		<h3 align="center">'.$LANG['MISC']['new_player'].'</h3>
		<br>
		';
	} else {
		echo '
		<h3 align="center">'.$LANG['MISC']['new_organizer'].'</h3>
		<br>
		';
	}
	echo '
		<form name="editperson" action="visperson.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
		'.$buttons.'
		<input type="hidden" name="ny_person" value="yes">
	';
	if ($person['type']) {
		echo '
			<input type="hidden" name="person[type]" value="'.$person['type'].'">
		';
	}
} else {
	echo '
		<h3 align="center">'.$LANG['MISC']['edit_person'].'<br>'.$person['fornavn'].' '.$person['etternavn'].'</h3>
		<br>
		<form name="editperson" action="visperson.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
		'.$buttons.'
		<input type="hidden" name="edited" value="yes">
	';
}
$arrownum = 0;

include('form_edit_person.php');
echo $buttons.'
	</form>
';
include('footer.php');
?>
