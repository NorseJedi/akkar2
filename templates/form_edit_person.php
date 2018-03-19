<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                           form_edit_person.php                          #
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

for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mnder = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = date('Y'); $i >= date('Y')-99; $i--) {
	$aarliste[$i] = $i;
}
echo '
<script language="JavaScript" type="text/javascript">
	function validate_person() {
		if (document.getElementById(\'fornavn\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['firstname'].'\');
			document.getElementById(\'fornavn\').focus();
			return false;
		}
		if (document.getElementById(\'etternavn\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['surname'].'\');
			document.getElementById(\'etternavn\').focus();
			return false;
		}
		if (document.getElementById(\'dag\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['birthdate'].'\');
			document.getElementById(\'dag\').focus();
			return false;
		}
		if (document.getElementById(\'mnd\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['birthdate'].'\');
			document.getElementById(\'mnd\').focus();
			return false;
		}
		if (document.getElementById(\'aar\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['birthdate'].'\');
			document.getElementById(\'aar\').focus();
			return false;
		}
		if (!validDate(document.getElementById(\'dag\').value, document.getElementById(\'mnd\').value, document.getElementById(\'aar\').value)) {
			window.alert(\''.$LANG['JSBOX']['invalid_date'].'\');
			return false;
		}
		if (!document.getElementById(\'kjonn_han\').checked && !document.getElementById(\'kjonn_hun\').checked) {
			window.alert(\''.$LANG['JSBOX']['gender'].'\');
			document.getElementById(\'kjonn_han\').focus();
			return false;
		}
		if (document.getElementById(\'mailpref\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['mail_preference'].'\');
			document.getElementById(\'mailpref\').focus();
			return false;
		}
		if (document.getElementById(\'email\').value == \'\' && document.getElementById(\'mailpref\').value == \'email\') {
			window.alert(\''.$LANG['JSBOX']['email_pref_noaddress'].'\');
			document.getElementById(\'email\').focus();
			return false;
		}
		if (document.getElementById(\'mailpref\').value == \'post\') {
			if (document.getElementById(\'adresse\').value == \'\') {
				window.alert(\''.$LANG['JSBOX']['snailmail_pref_noaddress'].'\');
				document.getElementById(\'adresse\').focus();
				return false;
			}
			if (document.getElementById(\'postnr\').value == \'\') {
				window.alert(\''.$LANG['JSBOX']['snailmail_pref_noaddress'].'\');
				document.getElementById(\'postnr\').focus();
				return false;
			}
			if (document.getElementById(\'poststed\').value == \'\') {
				window.alert(\''.$LANG['JSBOX']['snailmail_pref_noaddress'].'\');
				document.getElementById(\'poststed\').focus();
				return false;
			}
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


if ($_GET['person_id']) {
	echo '
	<input type="hidden" name="person_id" value="'.$_GET['person_id'].'">
	<input type="hidden" name="whereiwas" value="'.$whereiwas.'">
	';
}
if (!$person['bilde']) {
	$person['bilde'] = mugshot($person);
}
if (!$tabindex) {
		$tabindex = 1;
}
echo '
	<input type="hidden" name="spill_id" value="'.$_GET['spill_id'].'">
	<table border="0" align="center" width="50%">
		<tr>
			<td rowspan="9" align="center"><img class="foto" src="'.$person['bilde'].'" height="150" width="120" alt="'.$person['fornavn'].' '.$person['etternavn'].'">
';
if (!$person) {
	echo '
			<br><input type="file" name="bilde" size="15">
	';
} else {
	echo '
			<br><button type="button" onClick="javascript:openInfowindow(\'./mugshots.php?person_id='.$person['person_id'].'\')">'.$LANG['MISC']['mugshots'].'</button>
	';
}
echo '
			</td>
			<td nowrap><strong>'.$LANG['MISC']['firstname'].'</strong> <span class="tiny">('.$LANG['MISC']['and_middlename'].')</span></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="20" maxlength="255" id="fornavn" name="person[fornavn]" value="'.$person['fornavn'].'"> '.$mand_mark.'</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['surname'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="20" maxlength="255" id="etternavn" name="person[etternavn]" value="'.$person['etternavn'].'"> '.$mand_mark.'</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['birthdate'].'</strong></td>
			<td nowrap>
			<select id="dag" tabindex="'.$tabindex++.'" name="person[dag]"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, $dag).'</select> <select id="mnd" tabindex="'.$tabindex++.'" name="person[mnd]"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mnder, $mnd).'</select> <select id="aar" tabindex="'.$tabindex++.'" name="person[aar]"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, $aar).'</select> '.$mand_mark.'
			</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['gender'].'</strong></td>
			<td nowrap>
			<input type="radio" id="kjonn_han" tabindex="'.$tabindex++.'" name="person[kjonn]" value="han"'; if ($person['kjonn'] == 'han') { echo ' checked'; } echo '>'.$LANG['MISC']['male'].' <img src="'.$styleimages['symb_male'].'">
			<input type="radio" id="kjonn_hun" tabindex="'.$tabindex++.'" name="person[kjonn]" value="hun"'; if ($person['kjonn'] == 'hun') { echo ' checked'; } echo '>'.$LANG['MISC']['female'].' <img src="'.$styleimages['symb_female'].'"> '.$mand_mark.'
			</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['address'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="20" maxlength="255" id="adresse" name="person[adresse]" value="'.$person['adresse'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['zipcode'].'/'.$LANG['MISC']['region'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="4" maxlength="255" id="postnr" name="person[postnr]" value="'.$person['postnr'].'"'; if ($config['use_autoregion']) { echo ' onChange="javascript:if (postnummer[this.value]) { document.getElementById(\'poststed\').value=postnummer[this.value]; document.getElementById(\'poststed\').disabled=true; } else { document.getElementById(\'poststed\').value=\''.$person['poststed'].'\'; document.getElementById(\'poststed\').disabled=false; }"'; } echo '> <input type="text" tabindex="'.$tabindex++.'" size="15" maxlength="255" id="poststed" name="person[poststed]" value="'.$person['poststed'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['telephone'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($person['telefon'], 10).'" maxlength="255" id="telefon" name="person[telefon]" value="'.$person['telefon'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['cellphone'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($person['mobil'], 10).'" maxlength="255" id="mobil" name="person[mobil]" value="'.$person['mobil'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['email'].'</strong></td>
			<td><input type="text" tabindex="'.$tabindex++.'" size="'.get_fieldsize($person['email'], 20).'" maxlength="255" id="email" name="person[email]" value="'.$person['email'].'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['mail_preference'].'</strong></td>
			<td><select id="mailpref" tabindex="'.$tabindex++.'" name="person[mailpref]">
				<option class="selectname" value="">- '.$LANG['MISC']['select'].' -</option>
				<option value="email"'; if ($person['mailpref'] == 'email') { echo ' selected'; } echo '>'.$LANG['MISC']['email'].'</option>
				<option value="post"'; if ($person['mailpref'] == 'post') { echo ' selected'; } echo '>'.$LANG['MISC']['snailmail'].'</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<table align="center" border="0">
					<tr>
						<td colspan="2">
							<strong>'.$LANG['MISC']['special_considerations'].'</strong>
						</td>
					</tr>
					<tr>
						<td colspan="2">
						<textarea id="hensyn" tabindex="'.$tabindex++.'" name="person[hensyn]" rows="5" cols="75">'.stripslashes($person['hensyn']).'</textarea>
						</td>
					</tr>
					';
					$arrownum++;
					echo '
					<tr>
						<td align="left">
						'.inputsize_less('hensyn', $arrownum).'
						</td>
						<td align="right">
						'.inputsize_more('hensyn', $arrownum).'
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">
				<table align="center" border="0">
					<tr>
						<td colspan="2">
							<strong>'.$LANG['MISC']['internal_notes'].'</strong>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<textarea id="intern_info" tabindex="'.$tabindex++.'" name="person[intern_info]" rows="5" cols="75">'.stripslashes($person['intern_info']).'</textarea>
						</td>
					</tr>
					';
					$arrownum++;
					echo '
					<tr>
						<td align="left">
						'.inputsize_less('intern_info', $arrownum).'
						</td>
						<td align="right">
						'.inputsize_more('intern_info', $arrownum).'
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
	</table>
';
?>