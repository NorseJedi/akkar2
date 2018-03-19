<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               userinfo.php                              #
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
$person = get_arrangor($_SESSION['person_id']);
$person['bilde'] = mugshot($person);

if (!$person['telefon']) {
	$person['telefon'] = $LANG['MISC']['none'];
}
if (!$person['mobil']) {
	$person['mobil'] = $LANG['MISC']['none'];
}
if (!$person['email']) {
	$person['email'] = $LANG['MISC']['none'];
} else {
	$person['email'] = '<a href="mailto:'.$person['email'].'">'.$person['email'].'</a>';
}
$plevels = array('5'=>$LANG['MISC']['organizer'], '10'=>$LANG['MISC']['coordinator'], '20'=>$LANG['MISC']['administrator']);
if (!$person['brukernavn']) {
	$brukernavn = 'N/A';
	$passord = 'N/A';
	$plevel = 'N/A';
} else {
	$brukernavn = $person['brukernavn'];
	$passord = '<em>&lt;'.$LANG['MISC']['encrypted'].'&gt;</em>';
	$plevel = $plevels[$person['level']];
}
$dato = explode('-', $person['fodt']);
$then = $dato[0].$dato[1].$dato[2];
$now = date('Ymd');
$tmpalder = $now-$then;
if (strlen($tmpalder) == 4) {
	$person['alder'] = '0 '.$LANG['MISC']['years_old'];
	} elseif (strlen($tmpalder) == 5) {
	$person['alder'] = substr($tmpalder,0,1).' '.$LANG['MISC']['years_old'];
} else {
	$person['alder'] = substr($tmpalder,0,2).' '.$LANG['MISC']['years_old'];
}
$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
$fodt = abs($dato[2]).'. '.substr($mndliste[abs($dato[1])], 0, 3).' '.$dato[0];

$oppgaver = get_mine_oppgaver();
$aktive_spill = get_aktive_spill('start DESC');

include('header.php');
echo '
	<table width="100%" cellspacing="5" border="0">
';
if ($config['motd']) {
	echo '
		<tr>
			<td class="bordered" style="padding: 0;" colspan="2">
				<h5 class="highlight" align="center">'.$LANG['MISC']['motd'].'</h5>
				<div style="padding: 0.5em; text-align: center">'.nl2br(customtagged_text($config['motd'])).'</div>
			</td>
		</tr>
	';
}
echo '
		<tr>
			<td width="90%" rowspan="2" class="bordered" style="padding: 0;">
';

echo '
			<h5 class="highlight" align="center">'.$LANG['MISC']['my_active_tasks'].'</h5>
			<br>
';
include('print_oppgaver.php');
echo '


				<div align="center">
				'.button('tasks', 'oppgaver.php').'
				</div>
			</td>
			<td style="padding: 0;height: 15em;" valign="top">

<table align="center" border="0" cellspacing="0" class="bordered">
	<tr>
		<td colspan="3" class="highlight" align="center">'.$person['fornavn'].' '.$person['etternavn'].'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['birthdate'].'</td>
		<td nowrap>'.$fodt.'</td>
		<td rowspan="7" class="nospace"><a href="javascript:javascript:openInfowindow(\'./mugshots.php?person_id='.$person['person_id'].'\');"><img src="'.$person['bilde'].'" alt="'.$person['fornavn'].' '.$person['etternavn'].'" class="foto" height="75" width="60" style="margin-right: 2px;"></a></td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['age'].'</td>
		<td nowrap="nowrap">'.$person['alder'].'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['address'].'</td>
		<td nowrap="nowrap">'.$person['adresse'].'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['zipcode'].'/'.$LANG['MISC']['region'].'</td>
		<td nowrap="nowrap">'.$person['postnr'].' '.$person['poststed'].'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['telephone'].'</td>
		<td nowrap="nowrap">'.$person['telefon'].'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['cellphone'].'</td>
		<td nowrap="nowrap">'.$person['mobil'].'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['email'].'</td>
		<td colspan="2">'.$person['email'].'</td>
	</tr>
	<tr>
		<td class="highlight" style="padding: 5px;" colspan="3" align="center">
		'.button('edit', 'editperson.php?person_id='.$person['person_id']).'
		</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['username'].'</td>
		<td>'.$brukernavn.'</td>
		<td class="nospace">'.button('edit', './editbruker.php?person_id='.$person['person_id'].'&amp;action=nytt_brukernavn').'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['password'].'</td>
		<td>'.$passord.'</td>
		<td class="nospace">'.button('change', './editbruker.php?person_id='.$person['person_id'].'&amp;action=nytt_passord').'</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['userlevel'].'</td>
		<td>'.$plevel.'</td>
	</tr>
	<tr>
		<td class="highlight" colspan="3">&nbsp;</td>
	</tr>
</table>
			</td>
		</tr>
		<tr>
			<td style="padding: 0;vertical-align: top; padding-top: 1em;">
	<table class="bordered" width="100%" cellspacing="0">
		<tr>
			<td class="highlight" align="center"><h4 class="table">'.$LANG['MISC']['coming_week'].'</h4></td>
		</tr>
		<tr>
			<td>
';

for ($i = 0; $i < 7; $i++) {
	$timestamp = strtotime($i.' days');
	$jdtime = unixtojd($timestamp);
	$dato = strftime('%Y', $timestamp).'-'.strftime('%m', $timestamp).'-'.strftime('%d', $timestamp);
	if ($spill = get_spill()) {
		foreach ($spill as $spillinfo) {
			$start = strftime('%Y', $spillinfo['start']).'-'.strftime('%m', $spillinfo['start']).'-'.strftime('%d', $spillinfo['start']);
			$slutt = strftime('%Y', $spillinfo['slutt']).'-'.strftime('%m', $spillinfo['slutt']).'-'.strftime('%d', $spillinfo['slutt']);
			if (($start == $slutt) && ($start == $dato)) {
				$dagentry = true;
				$output .= '<li><strong>'.$LANG['MISC']['game'].':</strong> <a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></li>';
			} elseif ($start == $dato) {
				$dagentry = true;
				$output .= '<li><strong>'.$LANG['MISC']['game_start'].':</strong> <a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></li>';
			} elseif ($slutt == $dato) {
				$dagentry = true;
				$output .= '<li><strong>'.$LANG['MISC']['game_end'].':</strong> <a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></li>';
			}
			unset($start,$slutt);
		}
	}
	if ($spilldeadlines = get_spilldeadlines_for_dag($timestamp)) {
		$dagentry = true;
		foreach ($spilldeadlines as $deadline) {
			$spillinfo = get_spillinfo($deadline['spill_id']);
			$output .= '<li>'.gamedeadline_icon($deadline).'&nbsp;<strong>'.$spillinfo['navn'].':</strong> '.$deadline['tekst'].'</li>';
		}
	}
	if ($oppgaver = get_mine_oppgaver()) {
		foreach ($oppgaver as $oppgave) {
			$jddato = unixtojd($oppgave['deadline']);
			if ($jddato == unixtojd($timestamp)) {
				$dagentry = true;
				$output .= '<li>'.taskdeadline_icon($oppgave).'&nbsp;<strong>'.$LANG['MISC']['task'].'</strong> '.$oppgave['tekst'].'</li>';
			}
		}
	}
	if ($notater = get_kalnotater($jdtime)) {
		$dagentry = true;
		foreach ($notater as $notat) {
			$person = get_person($notat['person_id']);
				$output .= '<li>'.small_note_icon($notat).'&nbsp;'.$LANG['MISC']['note_by'].' <a href="./visperson.php?person_id='.$person['person_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a></li>';
		}
	}
	if ($dagentry) {
	echo '
		<strong>'.ucfirst(strftime($config['long_dateformat'], $timestamp)).'</strong>
		<ul style="margin-top:0;margin-bottom:0;">
		'.$output;
		$entries++;
	}
	echo '</ul>';
	unset($output,$dagentry);
	
}
if (!$entries) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_entries'].'</h4>
		<br>
	';
}

echo '
		<div align="center">
		'.button('calendar', './kalender.php').'
		</div>
			</td>
		</tr>
	</table>
			
			</td>
		</tr>
	</table>
	<table width="100%" cellspacing="5">
		<tr>
			<td class="bordered" style="padding: 0;">

		<table cellspacing="0" cellpadding="0" align="center" width="100%">
		<tr>
			<td align="center" class="highlight"><h5>'.$LANG['MISC']['new_or_updated'].'</h5></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
';

$nye_filer = get_opd_filer($_SESSION['previouslog']);
if ($nye_filer) {
	echo '
		<tr class="highlight">
			<td align="center">'.$LANG['MISC']['filesystem'].'</td>
		</tr>
		<tr>
			<td>
				<table border="0" width="100%" cellspacing="0" cellpadding="0">
	';
	foreach ($nye_filer as $nyfil) {
		echo '
					<tr>
						<td><a href="./visfil.php?fil_id='.$nyfil['fil_id'].'">'.$nyfil['navn'].'</a>'.info_icon($LANG['MISC']['description'], $nyfil['beskrivelse']).'</td>
						<td style="text-align:center;white-space:nowrap">('.strftime($config['medium_dateformat'].' - %H:%M', $nyfil['oppdatert']).')</td>
						<td style="text-align:right">'.button('download', './download.php?fil_id='.$nyfil['fil_id']).'</td>
					</tr>
		';
	}
	echo '
				</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
	';
}
if ((!$aktive_spill) && (!$nye_filer)) {
	echo '
		<tr>
			<td align="center"><h5>'.$LANG['MISC']['no_updates'].'</h5></td>
		</tr>
	';

} elseif ($aktive_spill) {
	foreach ($aktive_spill as $spillinfo) {
		$nye_paameldinger[$spillinfo['spill_id']] = get_opd_paameldinger($_SESSION['previouslog'], $spillinfo['spill_id']);
		$nye_roller[$spillinfo['spill_id']] = get_opd_roller($_SESSION['previouslog'], $spillinfo['spill_id']);
		$nye_rollekonsept[$spillinfo['spill_id']] = get_opd_rollekonsept($_SESSION['previouslog'], $spillinfo['spill_id']);
		$nye_rolleforslag[$spillinfo['spill_id']] = get_opd_rolleforslag($_SESSION['previouslog'], $spillinfo['spill_id']);
		$nye_plott[$spillinfo['spill_id']] = get_opd_plott($_SESSION['previouslog'], $spillinfo['spill_id']);
		echo '
			<tr class="highlight">
				<td align="center">'.$spillinfo['navn'].'</td>
			</tr>
		';
		if ((!$nye_paameldinger[$spillinfo['spill_id']]) && (!$nye_roller[$spillinfo['spill_id']]) && (!$nye_plott[$spillinfo['spill_id']]) && (!$nye_rolleforslag[$spillinfo['spill_id']]) && (!$nye_rollekonsept[$spillinfo['spill_id']])) {
			echo '
				<tr>
					<td align="center"><h6>'.$LANG['MISC']['no_updates'].'</h6></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			';
		} else {
			if ($nye_paameldinger[$spillinfo['spill_id']]) {
				echo '
					<tr>
						<td align="center"><h6>'.$LANG['MISC']['new_registrations'].':</h6></td>
					</tr>
				';
				foreach ($nye_paameldinger[$spillinfo['spill_id']] as $paamelding) {
					echo '
						<tr>
							<td><a href="./vispaamelding.php?person_id='.$paamelding['person_id'].'&amp;spill_id='.$spillinfo['spill_id'].'">'.$paamelding['fornavn'].' '.$paamelding['etternavn'].'</a> ('.strftime($config['medium_dateformat'].' - %H:%M', $paamelding['paameldt']).')</td>
						</tr>
					';
				}
				echo '
					<tr>
						<td>&nbsp;</td>
					</tr>
				';
			}
			if ($nye_rollekonsept[$spillinfo['spill_id']]) {
				echo '
					<tr>
						<td align="center"><h6>'.$LANG['MISC']['new_character_concepts'].':</h6></td>
					</tr>
				';
				foreach ($nye_rollekonsept[$spillinfo['spill_id']] as $rollekonsept) {
					echo '
						<tr>
							<td><a href="./visrollekonsept.php?konsept_id='.$rollekonsept['konsept_id'].'&amp;spill_id='.$rollekonsept['spill_id'].'">'.$rollekonsept['tittel'].'</a> ('.strftime($config['medium_dateformat'].' - %H:%M', $rollekonsept['oppdatert']).')</td>
						</tr>
					';
				}
				echo '
					<tr>
						<td>&nbsp;</td>
					</tr>
				';
			}
			if ($nye_roller[$spillinfo['spill_id']]) {
				echo '
					<tr>
						<td align="center"><h6>'.$LANG['MISC']['new_characters'].':</h6></td>
					</tr>
				';
				foreach ($nye_roller[$spillinfo['spill_id']] as $rolle) {
					echo '
						<tr>
							<td><a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a> ('.strftime($config['medium_dateformat'].' - %H:%M', $rolle['oppdatert']).')</td>
						</tr>
					';
				}
				echo '
					<tr>
						<td>&nbsp;</td>
					</tr>
				';
			}
			if ($nye_rolleforslag[$spillinfo['spill_id']]) {
				echo '
					<tr>
						<td align="center"><h6>'.$LANG['MISC']['new_character_suggestions'].':</h6></td>
					</tr>
				';
				foreach ($nye_rolleforslag[$spillinfo['spill_id']] as $rolle) {
					echo '
						<tr>
							<td><a href="./visrolleforslag.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a> ('.strftime($config['medium_dateformat'].' - %H:%M', $rolle['oppdatert']).')</td>
						</tr>
					';
				}
				echo '
					<tr>
						<td>&nbsp;</td>
					</tr>
				';
			}
			if ($nye_plott[$spillinfo['spill_id']]) {
				echo '
					<tr>
						<td align="center"><h6>'.$LANG['MISC']['new_plots'].':</h6></td>
					</tr>
				';
				foreach ($nye_plott[$spillinfo['spill_id']] as $plott) {
					echo '
						<tr>
							<td><a href="./visplott.php?plott_id='.$plott['plott_id'].'&amp;spill_id='.$plott['spill_id'].'">'.$plott['navn'].'</a> ('.strftime($config['medium_dateformat'].' - %H:%M', $plott['oppdatert']).')</td>
						</tr>
					';
				}
				echo '
					<tr>
						<td>&nbsp;</td>
					</tr>
				';
			}
			echo '
				<tr>
					<td>&nbsp;</td>
				</tr>
			';
		}
	}
}
echo '
			</table>
			</td>
			<td class="bordered" style="padding: 0;">
				<h5 align="center" class="highlight">'.$LANG['MISC']['my_characters'].'</h5>
				<br>
';

if (!$aktive_spill) {
	echo '
		<h5 align="center">'.$LANG['MISC']['no_characters_in_active_games'].'</h5>
		<br><br>
	';
} else {
	$ingen_roller = true;
	foreach ($aktive_spill as $spillinfo) {
		$spillroller = get_mine_roller($spillinfo['spill_id']);
		if ($spillroller) {
			$ingen_roller = false;
			echo '
				<table cellspacing="0" align="center" width="100%" border="0">
			';
			foreach ($spillroller as $spill_id=>$roller) {
				$spillinfo = get_spillinfo($spill_id);
				echo '
					<tr class="highlight">
						<td colspan="4" align="center">'.$spillinfo['navn'].'</td>
					</tr>
				';
				foreach ($roller as $rolle) {
				echo '
					<tr>
						<td><a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>
						<td class="nospace" width="1">'.button('edit', 'editrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id']).'</td>
						<td class="nospace" width="1">'.button('attachments', './filvedlegg.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vedlagt=rolle').'</td>
						<td class="nospace" width="1">'.button('delete', './roller.php?slett_rolle='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'], 'onClick', 'return confirmDelete(\''.addslashes($rolle['navn'].' for '.$spillnavn).'\')').'</td>
</tr>
				';
				}
				echo '
					<tr>
						<td colspan="4">&nbsp;</td>
					</tr>
				';
			}
			echo '
				</table>
			';
		}
	}
	if ($ingen_roller) {
		echo '
		<h5 align="center">'.$LANG['MISC']['no_characters_in_active_games'].'</h5>
		<br><br>
		';
	}
}
echo '
			</td>
		</tr>
	</table>
';


include('footer.php');

?>
