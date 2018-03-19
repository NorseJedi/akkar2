<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             rollekonsept.php                            #
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

if ($_GET['slett_rollekonsept']) {
	slett_rollekonsept();
	$_SESSION['message'] = $LANG['MESSAGE']['character_concept_deleted'];
	header('Location: ./rollekonsept.php?spill_id='.$spill_id);
	exit();
}

include('header.php');

echo '<h2 align="center">'.$LANG['MISC']['character_concepts'].'</h2>';
$rollekonsept = get_roller_konsept($spill_id);
$numkonsept = count($rollekonsept);
$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:window.location=\'./filvedlegg.php?konsept_id=2&spill_id='.$spill_id.'&vedlagt=rollekonsept\';">'.$LANG['MISC']['charconcept_attachments'].'</button></td>
		<td><button onClick="javascript:window.location=\'./sendrollekonsept.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['send_character_concepts'].'</button></td>
		<td><button onClick="javascript:window.location=\'./editrollekonsept.php?spill_id='.$spill_id.'&amp;nytt_konsept=yes\';">'.$LANG['MISC']['create_character_concept'].'</button></td>
	</tr>
</table>
';

if (!$rollekonsept) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_character_concepts'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button onClick="javascript:window.location=\'./filvedlegg.php?konsept_id=2&spill_id='.$spill_id.'&vedlagt=rollekonsept\';">'.$LANG['MISC']['charconcept_attachments'].'</button></td>
				<td><button onClick="javascript:window.location=\'./editrollekonsept.php?spill_id='.$spill_id.'&amp;nytt_konsept=yes\';">'.$LANG['MISC']['create_character_concept'].'</button></td>
			</tr>
		</table>
	';
} else {
	echo '
		<h4 align="center">'.$numkonsept.' '.$LANG['MISC']['concept_s'].'</h4>
		'.$buttons.'
		<br>
	';
	echo '
		<table border="0" cellpadding="3" cellspacing="0" align="center" width="90%">
			<tr class="noprint">
				<td colspan="5" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
		';
		$sorting = get_sorting('./rollekonsept.php?spill_id='.$spill_id, 'tittel', 'rollekonseptorder');
		echo '
			<td nowrap>'.$LANG['MISC']['concept'].' '.$sorting.'</td>
		';

		$sorting = get_sorting('./rollekonsept.php?spill_id='.$spill_id, 'arrangor_id', 'rollekonseptorder');
		echo '
			<td nowrap>'.$LANG['MISC']['organizer'].' '.$sorting.'</td>
		';

		$sorting = get_sorting('./rollekonsept.php?spill_id='.$spill_id, 'rolle_id', 'rollekonseptorder');
		echo '
			<td nowrap>'.$LANG['MISC']['character'].' '.$sorting.'</td>
		';

		$sorting = get_sorting('./rollekonsept.php?spill_id='.$spill_id, 'spiller_id', 'rollekonseptorder');
		echo '
			<td nowrap>'.$LANG['MISC']['player'].' '.$sorting.'</td>
		';

		echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['concept']).'" type="text" id="konsept_filter" title="konsept_filter" onkeyup="javascript:filter_list(this.value, \'konsept\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['organizer']).'" type="text" id="arrangor_filter" title="arrangor_filter" onkeyup="javascript:filter_list(this.value, \'arrangor\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['character']).'" type="text" id="rolle_filter" title="rolle_filter" onkeyup="javascript:filter_list(this.value, \'rolle\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['player']).'" type="text" id="spiller_filter" title="spiller_filter" onkeyup="javascript:filter_list(this.value, \'spiller\');"></td>

		<td colspan="2">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($rollekonsept as $konsept) {
		echo '<tr>';
		if ($spiller = get_person($konsept['spiller_id'])) {
			$spillerlink = '<a href="./visperson.php?person_id='.$spiller['person_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
			$spiller = $spiller['fornavn'].' '.$spiller['etternavn'];
		} else {
			$spillerlink = $LANG['MISC']['none'];
			$spiller = $LANG['MISC']['none'];
		}
		if ($arrangor = get_person($konsept['arrangor_id'])) {
			$arrangorlink = '<a href="./visperson.php?person_id='.$arrangor['person_id'].'">'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</a>';
			$arrangor = $arrangor['fornavn'].' '.$arrangor['etternavn'];
		} else {
			$arrangorlink = $LANG['MISC']['none'];
			$arrangor = $LANG['MISC']['none'];
		}
		if ($rolle = get_rolle($konsept['rolle_id'], $spill_id)) {
			$rollelink = '<a href="./visrolle.php?rolle_id='.$konsept['rolle_id'].'&amp;spill_id='.$konsept['spill_id'].'">'.$rolle['navn'].'</a>';
			$rolle = $rolle['navn'];
		} else {
			$rollelink = $LANG['MISC']['none'];
			$rolle =  $LANG['MISC']['none'];
		}
		echo '
			<td id="konsept_rk'.$konsept['konsept_id'].'s'.$konsept['spill_id'].'" title="'.substr($konsept['tittel'], 0, 25).'" nowrap><a href="./visrollekonsept.php?konsept_id='.$konsept['konsept_id'].'&amp;spill_id='.$konsept['spill_id'].'">'.substr($konsept['tittel'], 0, 25).''; if (strlen($konsept['tittel']) > 25) { echo '...'; } echo '</a></td>
			<td id="arrangor_rk'.$konsept['konsept_id'].'s'.$konsept['spill_id'].'" title="'.$arrangor.'" nowrap>'.$arrangorlink.'</td>
			<td id="rolle_rk'.$konsept['konsept_id'].'s'.$konsept['spill_id'].'" title="'.$rolle.'" nowrap>'.$rollelink.'</td>
			<td id="spiller_rk'.$konsept['konsept_id'].'s'.$konsept['spill_id'].'" title="'.$spiller.'" nowrap>'.$spillerlink.'</td>

			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./editrollekonsept.php?konsept_id='.$konsept['konsept_id'].'&amp;spill_id='.$konsept['spill_id'].'\'">'.$LANG['MISC']['edit'].'</button></td>
						<td class="nospace"><button type="button" class="red" onClick="javascript:return confirmDelete(\''.htmlentities(addslashes($konsept['tittel'])).'\',\'./rollekonsept.php?slett_rollekonsept='.$konsept['konsept_id'].'&amp;spill_id='.$konsept['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
					</tr>
				</table>
			</td>
		</tr>';
	}
	echo '
		</table>
		'.$buttons.'
	';
}

include('footer.php');

?>
