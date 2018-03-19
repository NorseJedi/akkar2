<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               editplott.php                             #
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

$plott_id = $_REQUEST['plott_id'];

include('header.php');

if ($_GET['rediger_medlem']) {
	if ($_GET['type'] == 'rolle') {
		$medlem = get_plott_rolle($_GET['rediger_medlem'], $plott_id, $spill_id);
		$medlemslink = '<a href="./visrolle.php?rolle_id='.$medlem['rolle_id'].'&amp;spill_id='.$medlem['spill_id'].'">'.$medlem['navn'].'</a>';
		$spiller = get_person($medlem['spiller_id']);
		$spillerlink = '<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
	} else {
		$medlem = get_plott_gruppe($_GET['rediger_medlem'], $plott_id, $spill_id);
		$medlemslink = '<a href="./visgruppe.php?gruppe_id='.$medlem['medlem_id'].'&amp;spill_id='.$medlem['spill_id'].'">'.$medlem['navn'].'</a>';
		$spillerlink = '&nbsp;';
	}
	$plott = get_plott($plott_id, $spill_id);
	echo '
		<h2 align="center">'.$LANG['MISC']['edit_plotrelation'].'</h2>
		<h3 align="center">'.$plott['navn'].'</h3>
		<br>
		<form name="editplottrolleform" action="./visplott.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="redigert_medlem" value="'.$_GET['rediger_medlem'].'">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="plott_id" value="'.$plott_id.'">
		<input type="hidden" name="type" value="'.$_GET['type'].'">
		<table border="0" align="center" width="50%" cellspacing="0">
			<tr class="highlight">
		';
		if ($_GET['type'] == 'rolle') {
			echo '
				<td>'.$LANG['MISC']['character'].'</td>
				<td>'.$LANG['MISC']['player'].'</td>
			';
		} else {
			echo '
				<td colspan="2">'.$LANG['MISC']['group'].'</td>
			';
		}
		echo '
			</tr>
			<tr>
				<td>'.$medlemslink.'</td>
				<td>'.$spillerlink.'</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr class="highlight">
				<td colspan="2">'.$LANG['MISC']['relation'].'</td>
			</tr>
			<tr>
				<td colspan="2"><textarea rows="'.get_numrows($medlem['tilknytning'], 5).'" cols="75" id="tilknytning" name="tilknytning">'.htmlentities($medlem['tilknytning']).'</textarea>
			</tr>
			<tr>
				<td align="left">
					'.inputsize_less('tilknytning', 1).'
				</td>
				<td align="right">
					'.inputsize_more('tilknytning', 1).'
				</td>
			</tr>
	';
} else {

if ($_GET['nytt_plott']) {
	$plott = array(
	'spill_id'=>$spill_id,
	'navn'=>'',
	'beskrivelse'=>'',
	);
	echo '
		<h2 align="center">'.$LANG['MISC']['new_plot'].'</h2>
		<br>
		<form name="editplottform" action="./visplott.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="nytt_plott" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<table border="0" align="center" width="50%">
	';
} else {
	$plott = get_plott($plott_id, $spill_id);
	echo '
		<h2 align="center">'.$LANG['MISC']['edit_plot'].'</h2>
		<h3 align="center">'.$plott['navn'].'</h3>
		<br>
		<form name="editplottform" action="./visplott.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="edited" value="'.$plott_id.'">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<table border="0" align="center" width="50%">
	';
}
foreach ($plott as $key=>$value) {
	switch ($key) {
		case 'spill_id':
		case 'plott_id':
		case 'oppdatert':
			break;
		case 'navn':
			echo '
			<tr>
				<td><strong>'.$LANG['DBFIELD']['navn'].'</strong></td>
				<td><input type="text" size="20" name="'.$key.'" value="'.$value.'"></td>
			</tr>
			';
			break;
		default:
			echo '
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><strong>'.$LANG['MISC']['description'].'</strong></td>
			</tr>
			<tr>
				<td colspan="2"><textarea cols="75" rows="'.get_numrows($value, 5).'" id="'.$key.'" name="'.$key.'">'.htmlentities(stripslashes($value)).'</textarea></td>
			</tr>
					<tr>
					<td align="left">
					'.inputsize_less($key, 1).'
					</td>
					<td align="right">
					'.inputsize_more($key, 1).'
					</td>
					</tr>
			';
	}
}
}
echo '
</table>
<table align="center">
	<tr>
		<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
</form>
';
if ($_GET['rediger_medlem']) {
	echo '
		<table class="bordered" width="70%" align="center">
			<tr>
				<td>
	';
	if ($_GET['type'] == 'rolle') {
		$rolle_id = $medlem['rolle_id'];
		echo character_sheet($rolle_id, $spill_id, 1);
	} else {
		$gruppe_id = $medlem['gruppe_id'];
		include('print_gruppe.php');
	}
	echo '
				</td>
			</tr>
		</table>
	';
}


include('footer.php');
?>