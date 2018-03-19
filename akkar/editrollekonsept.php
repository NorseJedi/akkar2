<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                           editrollekonsept.php                          #
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
$spill_id = $_REQUEST['spill_id'];
$konsept_id = $_REQUEST['konsept_id'];

include('header.php');
echo '
	<form name="editrolle" action="visrollekonsept.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
';
if ($_GET['nytt_konsept']) {
	if (!$spill_id) {
		$spill_liste = get_spill();
		echo '
			</form>
			<form name="velgspillform" action="editrollekonsept.php" method="get">
			<input type="hidden" name="nyrolle" value="yes">
			<table align="center">
				<tr class="highlight">
					<td colspan="2">'.$LANG['MISC']['select_game'].'</td>
				</tr>
				<tr>
					<td><select name="spill_id">
						<option value="0" class="selectname">- '.$LANG['MISC']['select'].' -</option>
		';
		foreach ($spill_liste as $spill) {
			echo '<option value="'.$spill['spill_id'].'">'.$spill['navn'].'</option>';
		}
		echo '
						</select>
					</td>
					<td><button type="submit">'.$LANG['MISC']['continue'].'</button></td>
				</tr>
			</table>
			</form>
		';
		exits();
	}
	$rollekonsept = array('tittel'=>'', 'konsept'=>'');
	echo '
	<input type="hidden" name="nytt_konsept" value="yes">
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<h2 align="center">'.$LANG['MISC']['new_character_concept'].'</h2>
	';
} else {
	$rollekonsept = get_rollekonsept($konsept_id, $spill_id);
	echo '
		<input type="hidden" name="edited" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="konsept_id" value="'.$rollekonsept['konsept_id'].'">
		<h2 align="center">'.$LANG['MISC']['edit_character_concept'].'</h2>
		<h3 align="center">'.$rolle['tittel'].'</h3>
	';
}

$buttons = '
<table align="center">
	<tr>
		<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
';
if (!$_GET['nytt_konsept']) {
	$buttons .= '
		<td><button type="button" onClick="javascript:window.location=\'./visrollekonsept.php?konsept_id='.$konsept_id.'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['character_concepts'].'</button></td>
	';
}
$buttons .= '
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
';

$j = 0;
echo '
	'.$buttons.'
	<br>
	<table border="0" align="center" width="0">
';
	if ($paameldte = get_paameldte_og_arrangorer($spill_id)) {
		foreach ($paameldte as $paameldt) {
			$paameldingsliste[$paameldt['person_id']] = $paameldt['fornavn'].' '.$paameldt['etternavn'];
		}
	} else {
		$paameldingsliste = array();
	}
	unset($paameldte);
	if ($roller = get_roller($spill_id)) {
		foreach ($roller as $rolle) {
			$rolleliste[$rolle['rolle_id']] = $rolle['navn'];
		}
	} else {
		$rolleliste = array();
	}
	unset($roller);
	$arrangorer = get_arrangorer();
	foreach ($arrangorer as $arrangor) {
		$arrangorliste[$arrangor['person_id']] = $arrangor['fornavn'].' '.$arrangor['etternavn'];
	}
	unset($arrangorer);
	echo '
		<tr>
			<td><strong>'.$LANG['MISC']['title'].'</strong></td>
			<td><input type="text" maxlength="255" name="tittel" value="'.htmlspecialchars($rollekonsept['tittel']).'"></td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
			<td><select name="arrangor_id">
				<option value="0" class="selectname">'.$LANG['MISC']['select'].'</option>'.print_liste($arrangorliste, $rollekonsept['arrangor_id']).'</select>
			</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['player'].'</strong></td>
			<td><select name="spiller_id">
				<option value="0" class="selectname">'.$LANG['MISC']['select'].'</option>'.print_liste($paameldingsliste, $rollekonsept['spiller_id']).'</select>
			</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['character'].'</strong></td>
			<td><select name="rolle_id">
				<option value="0" class="selectname">'.$LANG['MISC']['select'].'</option>'.print_liste($rolleliste, $rollekonsept['rolle_id']).'</select>
			</td>
		</tr>
		<tr>
			<td colspan="2"><strong>'.$LANG['MISC']['description'].'</strong></td>
		</tr>
		<tr>
			<td colspan="2"><textarea cols="75" rows="'.get_numrows($rollekonsept['konsept'], 5).'" id="konsept" name="konsept">'.htmlspecialchars($rollekonsept['konsept']).'</textarea></td>
		</tr>
		<tr>
		<td align="left">
		'.inputsize_less('konsept', 1).'
		</td>
		<td align="right">
		'.inputsize_more('konsept', 1).'
		</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
			'.$validatefunc.'
			'.$buttons.'
			</td>
		</tr>
</table>
</form>
';

include('footer.php');
?>
