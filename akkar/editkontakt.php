<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              editkontakt.php                            #
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


if ($_GET['nykontakt']) {
	$kontakt = array();
	$kontakt['bilde'] = $styleimages['no_mugshot_m'];
} else {
	$kontakt = get_kontakt($_GET['kontakt_id']);
	$kontakt['bilde'] = mugshot($kontakt);
	if ($kontakt['telefon'] == 0) {
		$kontakt['telefon'] = '';
	}
	if ($kontakt['mobil'] == 0) {
		$kontakt['mobil'] = '';
	}
	if ($kontakt['fax'] == 0) {
		$kontakt['fax'] = '';
	}
}
$tabindex = 1; 
$buttons = '
<table align="center">
	<tr>
		<td align="right"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td align="right"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td align="left"><button type="submit" onClick="javascript:return validate_kontakt();">'.$LANG['MISC']['save'].'</button></td>
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
if ($_GET['nykontakt']) {
	echo '
		<h3 align="center">'.$LANG['MISC']['new_contact'].'</h3>
		<br>
		<form name="editkontakt" action="viskontakt.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
		'.$buttons.'
		<input type="hidden" name="ny_kontakt" value="yes">
	';
} else {
	echo '
		<h3 align="center">'.$LANG['MISC']['edit_contact'].'<br>'.$kontakt['navn'].'</h3>
		<br>
		<form name="editkontakt" action="viskontakt.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
		'.$buttons.'
		<input type="hidden" name="edited" value="yes">
	';
}

echo '
<script language="JavaScript" type="text/javascript">
	function validate_kontakt() {
		if (document.getElementById(\'navn\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['contact_name'].'\');
			document.getElementById(\'navn\').focus();
			return false;
		}
		return true;
	}
';
if ($config['use_autoregion']) {
	$postnrliste = db_select('zipcodemap', 'zipcode!=\'\'');
	echo 'var postnummer = new Array('.count($postnrliste).');
		';
	foreach ($postnrliste as $value) {
		echo 'postnummer[\''.$value['zipcode'].'\']="'.$value['region'].'";
		';
	}
}
echo '
</script>
';

if ($_GET['kontakt_id']) {
	echo '
	<input type="hidden" name="kontakt_id" value="'.$_GET['kontakt_id'].'">
	<input type="hidden" name="whereiwas" value="'.$whereiwas.'">
	';
}
echo '
	<input type="hidden" name="spill_id" value="'.$_GET['spill_id'].'">
	<table border="0" align="center" width="50%">
		<tr>
			<td rowspan="9" align="center"><img class="foto" src="'.$kontakt['bilde'].'" height="150" width="120" alt="'.$kontakt['navn'].'">
			<br><input type="file" name="bilde" size="15">
			<br><input type="checkbox" name="slettbilde" onClick="checkDisable(document.editkontakt.slettbilde, document.editkontakt.bilde);"'; if (is_int(strpos($kontakt['bilde'], $styleimages['no_mugshot_f'])) || is_int(strpos($kontakt['bilde'], $styleimages['no_mugshot_m']))) { echo ' disabled'; } echo '><span class="small">'.$LANG['MISC']['delete_picture'].'</span>
			</td>
			<td nowrap><strong>'.$LANG['MISC']['name'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="20" maxlength="255" id="navn" name="kontakt[navn]" value="'.$kontakt['navn'].'"> '.$mand_mark.'</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['DBFIELD']['kontaktperson'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="20" maxlength="255" id="kontaktperson" name="kontakt[kontaktperson]" value="'.$kontakt['kontaktperson'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['address'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="20" maxlength="255" id="adresse" name="kontakt[adresse]" value="'.$kontakt['adresse'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['zipcode'].'/'.$LANG['MISC']['region'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="4" maxlength="255" id="postnr" name="kontakt[postnr]" value="'.$kontakt['postnr'].'"'; if ($config['use_autoregion']) { echo ' onChange="javascript:if (postnummer[this.value]) { document.getElementById(\'poststed\').value=postnummer[this.value]; document.getElementById(\'poststed\').disabled=true; } else { document.getElementById(\'poststed\').value=\''.$kontakt['poststed'].'\'; document.getElementById(\'poststed\').disabled=false; }"'; } echo '> <input type="text" tabindex="'.$tabindex++.'" size="15" maxlength="255" id="poststed" name="kontakt[poststed]" value="'.$kontakt['poststed'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['telephone'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($kontakt['telefon'], 10).'" maxlength="255" id="telefon" name="kontakt[telefon]" value="'.$kontakt['telefon'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['cellphone'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($kontakt['mobil'], 10).'" maxlength="255" id="mobil" name="kontakt[mobil]" value="'.$kontakt['mobil'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['fax'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($kontakt['fax'], 10).'" maxlength="255" id="fax" name="kontakt[fax]" value="'.$kontakt['fax'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['email'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($kontakt['email'], 20).'" maxlength="255" id="email" name="kontakt[email]" value="'.$kontakt['email'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['website'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($kontakt['webside'], 30).'" maxlength="255" id="email" name="kontakt[webside]" value="'.$kontakt['webside'].'"></td>
		</tr>
		<tr>
			<td colspan="3"><strong>'.$LANG['MISC']['description'].'</strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<textarea id="beskrivelse" tabindex="'.$tabindex++.'" name="kontakt[beskrivelse]" rows="5" cols="75">'.stripslashes($kontakt['beskrivelse']).'</textarea>
			</td>
		</tr>
		<tr>
			<td align="left">
				'.inputsize_less('beskrivelse', 1).'
			</td>
			<td>&nbsp;</td>
			<td align="right">
				'.inputsize_more('beskrivelse', 1).'
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3"><strong>'.$LANG['MISC']['notes'].'</strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<textarea id="notater" tabindex="'.$tabindex++.'" name="kontakt[notater]" rows="5" cols="75">'.stripslashes($kontakt['notater']).'</textarea>
		</td>
		</tr>
		<tr>
			<td align="left">
				'.inputsize_less('notater', 2).'
			</td>
			<td>&nbsp;</td>
			<td align="right">
				'.inputsize_more('notater', 2).'
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
	</table>
	'.$buttons.'
	</form>';
?>
