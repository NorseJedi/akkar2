<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             print_oppgaver.php                          #
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

if (basename($_SERVER['PHP_SELF']) == 'userinfo.php') {
	$compact = true;
}
if (!$oppgaver) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_tasks'].'</h4>
		<br>
	';
} else {
	echo '
		<table align="center" cellspacing="0" cellpadding="0" border="0" width="90%">
	';
	foreach ($oppgaver as $oppgave) {
		$opprettet_av = get_person($oppgave['opprettet_av']);
		$utforer = get_person($oppgave['utfores_av']);
		if (!$utforer) {
			$utfores_av = '<span class="red">'.$LANG['MISC']['none'].'</span>';
		} else {
			$utfores_av = '<a href="./visperson.php?person_id='.$utforer['person_id'].'">'.$utforer['fornavn'].' '.$utforer['etternavn'].'</a>';
		}
		if (!$oppgave['utfort']) {
			$status = $LANG['MISC']['not_completed'];
		} else {
			$status = $LANG['MISC']['completed'].' '.ucfirst(strftime($config['long_dateformat'], $oppgave['utfort']));
		}
		if ($compact) {
			echo '
				<tr>
					<td style="padding-bottom: 2em;">
			<table align="center" class="bordered" cellspacing="0" width="100%">
				<tr class="highlight">
					<td>'.$LANG['MISC']['deadline'].': '.ucfirst(strftime($config['long_dateformat'], $oppgave['deadline'])).'</td>
					<td class="nospace" align="right"><button type="button" onClick="javascript:window.location=\'./editoppgave.php?vis='.$vis.'&amp;action=rapport&amp;oppgave_id='.$oppgave['oppgave_id'].'\';">'.$LANG['MISC']['complete'].'</button></td>
				</tr>
				<tr class="highlight">
					<td colspan="2">'.$LANG['MISC']['created'].' '.ucfirst(strftime($config['long_dateformat'], $oppgave['opprettet'])).' '.$LANG['MISC']['by'].' <a href="./visperson.php?person_id='.$opprettet_av['person_id'].'">'.$opprettet_av['fornavn'].' '.$opprettet_av['etternavn'].'</a></td>
				</tr>
				<tr>
					<td colspan="2" style="padding-bottom: 1em;">'.nl2br(str_replace('"', "'", kal_convert($oppgave['oppgavetekst']))).'</td>
				</tr>
			</table>
					</td>
				</tr>
			';
		} else {
		echo '
			<tr>
				<td style="padding-bottom:3em;">
			<table align="center" class="bordered" cellspacing="0" width="100%">
				<tr class="highlight">
					<td>'.$LANG['MISC']['created'].' '.ucfirst(strftime($config['long_dateformat'], $oppgave['opprettet'])).' '.$LANG['MISC']['by'].' <a href="./visperson.php?person_id='.$opprettet_av['person_id'].'">'.$opprettet_av['fornavn'].' '.$opprettet_av['etternavn'].'</a></td>
					<td>'.$LANG['MISC']['deadline'].': '.ucfirst(strftime($config['long_dateformat'], $oppgave['deadline'])).'</td>
				</tr>
				<tr class="highlight">
					<td>'.$LANG['MISC']['assigned_to'].' '.$utfores_av.'</td>
					<td>'.$LANG['MISC']['status'].': '.$status.'
				</tr>
				<tr>
					<td colspan="2"><strong>'.$LANG['MISC']['description'].':</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="padding-bottom: 1em">'.stripslashes(nl2br(str_replace('"', "'", kal_convert($oppgave['oppgavetekst'])))).'</td>
				</tr>
		';
		if ($oppgave['utfort']) {
			echo '
				<tr>
					<td colspan="2" class="bt"><strong>'.$LANG['MISC']['report'].':</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="padding-bottom: 1em">'.stripslashes(nl2br(str_replace('"', "'", kal_convert($oppgave['resultat'])))).'</td>
				</tr>
			';
		}
		echo '
				<tr>
					<td colspan="2" class="nospace">
						<table cellspacing="0" align="center" border="0">
							<tr>
								<td class="nospace"><button type="button" onClick="javascript:confirmDelete(\''.$LANG['MISC']['this_task'].'\', \'./oppgaver.php?vis='.$vis.'&amp;slett='.$oppgave['oppgave_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
		';
		if (!$oppgave['utfores_av'] && !$oppgave['utfort']) {
			echo '
								<td class="nospace"><button type="button" onClick="javascript:window.location=\'./oppgaver.php?vis='.$vis.'&amp;rekvirer='.$oppgave['oppgave_id'].'\';">'.$LANG['MISC']['aquire'].'</button></td>
			';
		} elseif (($oppgave['utfores_av'] == $_SESSION['person_id']) && !$oppgave['utfort']) {
			echo '
								<td class="nospace"><button type="button" onClick="javascript:window.location=\'./editoppgave.php?vis='.$vis.'&amp;action=rapport&amp;oppgave_id='.$oppgave['oppgave_id'].'\';">'.$LANG['MISC']['complete'].'</button></td>
								<td class="nospace"><button type="button" onClick="javascript:window.location=\'./oppgaver.php?vis='.$vis.'&amp;frigi='.$oppgave['oppgave_id'].'\';">'.$LANG['MISC']['release'].'</button></td>
			';
		} elseif (!$oppgave['utfort']) {
			echo '
								<td class="nospace"><button type="button" onClick="javascript:confirmAction(\''.$LANG['JSBOX']['aquire_assigned_task'].'\', \'./oppgaver.php?vis='.$vis.'&amp;rekvirer='.$oppgave['oppgave_id'].'\');">'.$LANG['MISC']['aquire'].'</button></td>
			';
		}
		echo '
								<td class="nospace"><button type="button" onClick="javascript:window.location=\'./editoppgave.php?vis='.$vis.'&amp;action=edit&amp;oppgave_id='.$oppgave['oppgave_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		';
		if (is_koordinator() && !$oppgave['utfort']) {
			echo '
								<td>&nbsp;</td>
								<td class="nospace">
									<form name="delegerform'.$oppgave['oppgave_id'].'" action="./oppgaver.php" method="post">
									<input type="hidden" name="oppgave_id" value="'.$oppgave['oppgave_id'].'">
									<input type="hidden" name="vis" value="'.$vis.'">
									<select name="deleger_til">
									<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
			';
			$arrangorer = get_arrangorer();
			foreach ($arrangorer as $arrangor) {
				$arrangorliste[$arrangor['person_id']] = $arrangor['fornavn'].' '.$arrangor['etternavn'];
			}
			echo print_liste($arrangorliste, $oppgave['utfores_av']);
			unset($arrangorer, $arrangorliste);
			echo '
									</select>
								</td>
								<td class="nospace">'.button('assign', 'javascript:document.delegerform'.$oppgave['oppgave_id'].'.submit();').'
								</form>
								</td>
			';
		}
		echo '
						</table>
					</td>
				</tr>
			</table>
				</td>
			</tr>
		';
		}
	}
	echo '
		</table>
	';
}
?>
