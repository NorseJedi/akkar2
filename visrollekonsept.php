<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            visrollekonsept.php                          #
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

if ($_POST['edited']) {
	oppdater_rollekonsept();
	$_SESSION['message'] = $LANG['MESSAGE']['character_concept_updated'];
	header('Location: ./visrollekonsept.php?konsept_id='.$_POST['konsept_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_POST['nytt_konsept']) {
	$konsept_id = opprett_rollekonsept();
	$_SESSION['message'] = $LANG['MESSAGE']['character_concept_created'];
	header('Location: ./visrollekonsept.php?konsept_id='.$konsept_id.'&spill_id='.$_POST['spill_id']);
	exit();
}

$konsept_id = $_REQUEST['konsept_id'];

if (!$spill_id && !$konsept_id) {
	exit($LANG['ERROR']['no_char_or_game_selected']);
}
include('header.php');

$rollekonsept = get_rollekonsept($konsept_id, $spill_id);
if ($arrangor = get_person($rollekonsept['arrangor_id'])) {
	$arrangorlink = '<a href="visperson.php?person_id='.$arrangor['person_id'].'">'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</a>';
} else {
	$arrangorlink = $LANG['MISC']['none'];
}
if ($spiller = get_person($rollekonsept['spiller_id'])) {
	$spillerlink = '<a href="vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$rollekonsept['spill_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
} else {
	$spillerlink = $LANG['MISC']['none'];
}

if ($rolle = get_rolle($rollekonsept['rolle_id'], $spill_id)) {
	$rollelink = '<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a>';
} else {
	$rollelink = $LANG['MISC']['none'];
}
echo '
	<h2 align="center">'.$LANG['MISC']['character_concept'].'</h2>
	<h3 align="center">'.$rollekonsept['tittel'].'</h3>
	<br>
	<table border="0" align="center" width="50%">
	<tr>
		<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
		<td nowrap>'.$arrangorlink.'</td>
	</tr>
	<tr>
		<td><strong>'.$LANG['MISC']['player'].'</strong></td>
		<td nowrap>'.$spillerlink.'</td>
	</tr>

	<tr>
		<td><strong>'.$LANG['MISC']['character'].'</strong></td>
		<td nowrap>'.$rollelink.'</td>
	</tr>

	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2"><strong>'.$LANG['MISC']['description'].'</strong></td>
	</tr>
	<tr>
		<td colspan="2">'.nl2br(stripslashes($rollekonsept['konsept'])).'</td>
	</tr>
</table>
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button type="button" onClick="javascript:window.location=\'./rollekonsept.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['character_concepts'].'</button></td>
	';
	if (!$rollekonsept['rolle_id']) {
		echo '
		<td><button onClick="javascript:window.location=\'./editrolle.php?spill_id='.$spill_id.'&amp;nyrolle=yes&amp;konsept_id='.$rollekonsept['konsept_id'].'\'">'.$LANG['MISC']['create_character'].'</button></td>
		';
	}
	echo '
		<td><button onClick="javascript:window.location=\'./editrollekonsept.php?konsept_id='.$konsept_id.'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['edit'].'</button></td>
		<td><button class="red" onClick="javascript:return confirmDelete(\''.addslashes($konsept['tittel']).'\', \'./rollekonsept.php?slett_rollekonsept='.$konsept_id.'&amp;spill_id='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
	</tr>
</table>
';




include('footer.php');
?>
