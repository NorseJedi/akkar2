<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              editoppgave.php                            #
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

if ($_GET['action']) {
	$action = $_GET['action'];
}
if (!$action) {
	$action = 'opprett';
}
include('header.php');

for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = 2037; $i >= 1970; $i--) {
	$aarliste[$i] = $i;
}


switch ($action) {
	case 'opprett':
	case 'edit':
		if ($action == 'opprett') {
			$oppgave = array();
			$oppgave['deadline'] = strtotime('2 weeks');
			$oppgave['opprettet'] = time();
			$oppgave['opprettet_av'] = $_SESSION['person_id'];
			$status = 'N/A';
			echo '
				<h2 align="center">'.$LANG['MISC']['create_task'].'</h2>
				<br>
				<form name="editoppgaveform" action="oppgaver.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
				<input type="hidden" name="opprett" value="yes">
			';
		} else {
			$oppgave = get_oppgave($_GET['oppgave_id']);
			if (!$oppgave['utfort']) {
				$status = $LANG['MISC']['not_completed'];
			} else {
				$status = $LANG['MISC']['completed'].' '.ucfirst(strftime($config['long_dateformat'], $oppgave['utfort']));
			}
			echo '
				<h2 align="center">'.$LANG['MISC']['edit_task'].'</h2>
				<br>
				<form name="editoppgaveform" action="oppgaver.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
				<input type="hidden" name="edited" value="'.$oppgave['oppgave_id'].'">
			';
		}
		$opprettet_av = get_person($oppgave['opprettet_av']);
		$utforer = get_person($oppgave['utfores_av']);
		if (!$utforer) {
			$utfores_av = '<span class="red">'.$LANG['MISC']['none'].'</span>';
		} else {
			$utfores_av = '<a href="./visperson.php?person_id='.$utforer['person_id'].'">'.$utforer['fornavn'].' '.$utforer['etternavn'].'</a>';
		}
		echo '
			<input type="hidden" name="vis" value="'.$_GET['vis'].'">
			<table align="center" cellspacing="0">
				<tr class="highlight">
					<td>'.$LANG['MISC']['created'].': '.ucfirst(strftime($config['long_dateformat'], $oppgave['opprettet'])).' '.$LANG['MISC']['by'].' <a href="./visperson.php?person_id='.$opprettet_av['person_id'].'">'.$opprettet_av['fornavn'].' '.$opprettet_av['etternavn'].'</a></td>
					<td>'.$LANG['MISC']['deadline'].': <select name="deadline_dag"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, strftime('%d', $oppgave['deadline'])).'</select> <select name="deadline_mnd"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mndliste, strftime('%m', $oppgave['deadline'])).'</select> <select name="deadline_aar"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, strftime('%Y', $oppgave['deadline'])).'</select>
				</tr>
				<tr class="highlight">
		';
		if (is_koordinator()) {
			echo '
				<td>
					'.$LANG['MISC']['assigned_to'].' <select name="utfores_av">
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
			';
		} else {
			echo '
					<td>'.$LANG['MISC']['assigned_to'].' '.$utfores_av.'
					<input type="hidden" name="utfores_av" value="'.$oppgave['utfores_av'].'">
					</td>
			';
		}
		echo '
					<td>'.$LANG['MISC']['status'].': '.$status.'
				</tr>
				<tr>
					<td colspan="2"><strong>'.$LANG['MISC']['description'].'</strong></td>
				</tr>
				<tr>
					<td align="center" colspan="2" style="padding-bottom: 1em"><textarea rows="'.get_numrows($oppgave['oppgavetekst'], 2).'" cols="80" id="oppgavetekst" name="oppgavetekst">'.htmlentities($oppgave['oppgavetekst']).'</textarea></td>
				</tr>
				<tr>
					<td align="left">
					'.inputsize_less('oppgavetekst', 1).'
					</td>
					<td align="right">
					'.inputsize_more('oppgavetekst', 1).'
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
		';
		if ($oppgave['utfort']) {
			echo '
				<tr>
					<td colspan="2" class="bt"><strong>'.$LANG['MISC']['report'].'</strong></td>
				</tr>
				<tr>
					<td align="center" colspan="2" style="padding-bottom: 1em"><textarea rows="'.get_numrows($oppgave['resultat'], 2).'" cols="80" id="resultat" name="resultat">'.htmlentities($oppgave['resultat']).'</textarea></td>
				</tr>
				<tr>
					<td align="left">
					'.inputsize_less('resultat', 2).'
					</td>
					<td align="right">
					'.inputsize_more('resultat', 2).'
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			';
		}
		echo '
			</table>
			<table align="center" cellspacing="0">
			<tr>
				<td align="right"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				<td align="right"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
				<td align="left"><button type="submit">'.$LANG['MISC']['save'].'</button></td>
			</tr>
			</table>
			</form>
		';
		break;
	case 'rapport':
		$oppgave = get_oppgave($_GET['oppgave_id']);
		$opprettet_av = get_person($oppgave['opprettet_av']);
		$utforer = get_person($oppgave['utfores_av']);
		if (!$utforer) {
			$utfores_av = '<span class="red">'.$LANG['MISC']['none'].'</span>';
		} else {
			$utfores_av = '<a href="./visperson.php?person_id='.$utforer['person_id'].'">'.$utforer['fornavn'].' '.$utforer['etternavn'].'</a>';
		}
		$status = 'N/A';
		echo '
			<h2 align="center">'.$LANG['MISC']['complete_task'].'</h2>
			<br>
			<form name="editoppgaveform" action="oppgaver.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
			<input type="hidden" name="utfort" value="'.$oppgave['oppgave_id'].'">
			<input type="hidden" name="vis" value="mine_utforte">
			<table align="center" class="bordered" cellspacing="0" width="300">
				<tr class="highlight">
					<td>'.$LANG['MISC']['created'].': '.ucfirst(strftime($config['long_dateformat'], $oppgave['opprettet'])).' '.$LANG['MISC']['by'].' <a href="./visperson.php?person_id='.$opprettet_av['person_id'].'">'.$opprettet_av['fornavn'].' '.$opprettet_av['etternavn'].'</a></td>
					<td>'.$LANG['MISC']['deadline'].': '.ucfirst(strftime($config['long_dateformat'], $oppgave['deadline'])).'</td>
				</tr>
				<tr class="highlight">
					<td>'.$LANG['MISC']['assigned_to'].' '.$utfores_av.'</td>
					<td>'.$LANG['MISC']['status'].': '.$status.'
				</tr>
				<tr>
					<td colspan="2"><strong>'.$LANG['MISC']['description'].'</strong></td>
				</tr>
				<tr>
					<td colspan="2" style="padding-bottom: 1em">'.str_replace('"', "'", kal_convert(nl2br($oppgave['oppgavetekst']))).'</td>
				</tr>
				<tr>
					<td colspan="2" class="bt"><strong>'.$LANG['MISC']['report'].'</strong></td>
				</tr>
				<tr>
					<td align="center" colspan="2" style="padding-bottom: 1em"><textarea rows="'.get_numrows($oppgave['resultat'], 5).'" cols="80" id="resultat" name="resultat"></textarea></td>
				</tr>
				<tr>
					<td align="left">
					'.inputsize_less('resultat', 2).'
					</td>
					<td align="right">
					'.inputsize_more('resultat', 2).'
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			</table>
			<table align="center" cellspacing="0">
				<tr>
					<td align="right"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
					<td align="right"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
					<td align="left"><button type="submit">'.$LANG['MISC']['save'].'</button></td>
				</tr>
			</table>
			</form>
		';
		break;
}

include('footer.php');
?>
