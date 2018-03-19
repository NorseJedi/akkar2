<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               editspill.php                             #
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
if (!$_REQUEST['spill_id'] && !$_GET['nyttspill']) {
	exit($LANG['ERROR']['no_game_selected']);
}

include('header.php');

$maler = get_maler();

for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mnder = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = 2037; $i >= 1970; $i--) {
	$aarliste[$i] = $i;
}

echo '
<script language="JavaScript" type="text/javascript">
	function validate() {
		if (document.editspillform.navn.value == \'\') {
			window.alert(\''.$LANG['JSBOX']['game_name'].'\');
			document.editspillform.navn.focus();
			return false;
		}
		if (document.editspillform.rollemal.value == \'0\') {
			window.alert(\''.$LANG['JSBOX']['select_char_template'].'\');
			document.editspillform.rollemal.focus();
			return false;
		}
		if (document.editspillform.paameldingsmal.value == \'0\') {
			window.alert(\''.$LANG['JSBOX']['select_registration_template'].'\');
			document.editspillform.paameldingsmal.focus();
			return false;
		}
		return true;
	}

</script>
';

if ($_GET['nyttspill']) {
	$spillinfo = array('navn'=>'', 'start'=>'', 'slutt'=>'', 'rollemal'=>'', 'paameldingsmal'=>'', 'rollekonsept'=>'', 'status'=>$LANG['MISC']['active']);
	echo '
	<h2 align="center">'.$LANG['MISC']['new_game'].'</h2>
	<br>
	<form name="editspillform" method="post" action="visspill.php" onSubmit="javascript:convert_funky_letters(this);">
	<input type="hidden" name="nytt" value="yes">
	';
} else {
	$spillinfo = get_spillinfo($_REQUEST['spill_id']);
	echo '
	<h2 align="center">'.$LANG['MISC']['edit_game'].'</h2>
	<br>
	<form name="editspillform" method="post" action="visspill.php" onSubmit="javascript:convert_funky_letters(this);">
	<input type="hidden" name="spill_id" value="'.$_REQUEST['spill_id'].'">
	<input type="hidden" name="edited" value="yes">
	';

}
echo '
	<table border="0" align="center" width="50%">
';
foreach ($spillinfo as $key=>$value) {
	switch ($key) {
		case 'spill_id':
			break;
		case 'rollemal':
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['chartemplate'].'</strong></td>
				<td><select name="rollemal">
					<option value="0" class="selectname">- '.$LANG['MISC']['select'].' -</option>
			';
			foreach ($maler as $mal) {
				if ($mal[type] == 'rolle') {
					echo '<option value="'.$mal['mal_id'].'"'; if ($mal['mal_id'] == $spillinfo['rollemal']) { echo ' selected'; } echo '>'.$mal['navn'].'</option>';
				}
			}
			echo '
				</select>
				</td>
				<td>'.hjelp_icon($LANG['MISC']['chartemplate'], $LANG['HELPTIP']['game_chartemplate']).'</td>
			</tr>
			';
			break;
		case 'paameldingsmal':
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['registrationtemplate'].'</strong></td>
				<td><select name="paameldingsmal">
					<option value="0" class="selectname">- '.$LANG['MISC']['select'].' -</option>
			';
			foreach ($maler as $mal) {
				if ($mal['type'] == 'paamelding') {
					echo '<option value="'.$mal['mal_id'].'"'; if ($mal['mal_id'] == $spillinfo['paameldingsmal']) { echo ' selected'; } echo '>'.$mal['navn'].'</option>';
				}
			}
			echo '
				</select>
				</td>
				<td>'.hjelp_icon($LANG['MISC']['registrationtemplate'], $LANG['HELPTIP']['game_registrationtemplate']).'</td>
			</tr>
			';
			break;
		case 'rollekonsept':
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['use_concept'].'</strong></td>
				<td><input type="checkbox" name="rollekonsept" value="1"'; if ($value == 1) { echo ' checked'; } echo '</td>
				<td>'.hjelp_icon($LANG['MISC']['use_concept'], $LANG['HELPTIP']['use_concept']).'</td>
			</tr>
			';
			break;
		case 'status':
			echo '
				<tr>
					<td><strong>'.$LANG['MISC']['status'].'</strong></td>
					<td><select name="status">
						<option value="Aktiv"'; if (strtolower($spillinfo['status']) == 'aktiv') { echo ' selected'; } echo '>'.$LANG['MISC']['active'].'</option>
						<option value="Inaktiv"'; if (strtolower($spillinfo['status']) == 'inaktiv') { echo ' selected'; } echo '>'.$LANG['MISC']['inactive'].'</option>
						</select>
					</td>
					<td>'.hjelp_icon($LANG['MISC']['status'], $LANG['HELPTIP']['game_status']).'</td>
				</tr>
			';
			break;
		case 'slutt':
			if ($value) {
				$dag = strftime('%d', $value);
				$mnd = strftime('%m', $value);
				$aar = strftime('%Y', $value);
			} else {
				$dag = 0;
				$mnd = 0;
				$aar = 0;
			}
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['game_endtime'].'</strong></td>
				<td>
					<select name="slutt_day"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, $dag).'</select> <select name="slutt_month"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mnder, $mnd).'</select> <select name="slutt_year"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, $aar).'</select>
				</td>
				<td>'.hjelp_icon($LANG['MISC']['game_endtime'], $LANG['HELPTIP']['game_endtime']).'</td>
			</tr>
			';
			break;
		case 'start':
			if ($value) {
				$dag = strftime('%d', $value);
				$mnd = strftime('%m', $value);
				$aar = strftime('%Y', $value);
			} else {
				$dag = 0;
				$mnd = 0;
				$aar = 0;
			}
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['game_starttime'].'</strong></td>
				<td>
					<select name="start_day"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, $dag).'</select> <select name="start_month"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mnder, $mnd).'</select> <select name="start_year"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, $aar).'</select>
				</td>
				<td>'.hjelp_icon($LANG['MISC']['game_starttime'], $LANG['HELPTIP']['game_starttime']).'</td>
			</tr>
			';
			break;
		case 'navn':
			echo '
			<tr>
				<td><strong>'.$LANG['DBFIELD'][$key].'</strong></td>
				<td><input type="text" name="'.$key.'" value="'.$value.'"></td>
				<td>'.hjelp_icon($LANG['MISC']['name'], $LANG['HELPTIP']['game_name']).'</td>
			</tr>
			';
	}
}
echo '
</table>
<table align="center">
	<tr>
		<td><button onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit" onClick="javascript:return validate(this);">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
</form>
';
include('footer.php');
?>