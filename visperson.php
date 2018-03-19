<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               visperson.php                             #
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
	oppdater_personinfo();
	$_SESSION['message'] .= $LANG['MESSAGE']['person_updated'];
	header('Location: ./'.$_POST['whereiwas'].'?person_id='.$_POST['person_id'].'&spill_id='.$spill_id);
	exit();
} elseif ($_POST['ny_person']) {
	$person_id = opprett_person();
	$_SESSION['message'] .= $LANG['MESSAGE']['person_created'];
	header('Location: ./visperson.php?person_id='.$person_id.'&spill_id='.$spill_id);
	exit();
} elseif ($_GET['tilarrangor']) {
	if (is_koordinator()) {
		spiller_til_arrangor();
		$_SESSION['message'] = $LANG['MESSAGE']['person_status_updated'];
	}
	header('Location: ./visperson.php?person_id='.$_GET['tilarrangor'].'&spill_id='.$spill_id);
	exit();
} elseif ($_GET['tilspiller']) {
	if (is_koordinator() && !is_last_admin($_GET['tilspiller'])) {
		arrangor_til_spiller();
		$_SESSION['message'] = $LANG['MESSAGE']['person_status_updated'];
	}
	header('Location: ./visperson.php?person_id='.$_GET['tilspiller'].'&spill_id='.$spill_id);
	exit();
} elseif ($_POST['opprett_bruker']) {
	if (is_koordinator()) {
		opprett_bruker();
		$_SESSION['message'] = $LANG['MESSAGE']['useraccount_created'];
	}
	header('Location: ./visperson.php?person_id='.$_POST['person_id']);
	exit();
} elseif ($_POST['nytt_brukernavn']) {
	if (is_modifiable($_POST['person_id'])) {
		nytt_brukernavn();
		$_SESSION['message'] = $LANG['MESSAGE']['username_updated'];
	}
	header('Location: ./'.$_POST['whereiwas'].'?person_id='.$_POST['person_id']);
	exit();
} elseif ($_POST['nytt_passord']) {
	if (is_modifiable()) {
		if (nytt_passord()) {
			$_SESSION['message'] = $LANG['MESSAGE']['password_changed'];
		} else {
			$_SESSION['message'] = $LANG['MESSAGE']['password_change_error'];
		}
	}
	header('Location: ./'.$_POST['whereiwas'].'?person_id='.$_POST['person_id']);
	exit();
} elseif ($_POST['nytt_level']) {
	if (is_modifiable() && is_koordinator() && !is_last_admin($_POST['person_id'])) {
		if (nytt_level()) {
			$_SESSION['message'] = $LANG['MESSAGE']['userlevel_updated'];
		} else {
			$_SESSION['message'] = $LANG['MESSAGE']['userlevel_update_error'];
		}
	}
	header('Location: ./'.$_POST['whereiwas'].'?person_id='.$_POST['person_id']);
	exit();
} elseif ($_GET['unlock_bruker']) {
	if (is_admin()) {
		unlock_bruker();
		$_SESSION['message'] = $LANG['MESSAGE']['account_unlocked'];
	}
	header('Location: ./visperson.php?person_id='.$_GET['unlock_bruker']);
	exit();
} elseif($_GET['lock_bruker']) {
	if (is_admin() && !is_last_admin($_GET['lock_bruker'])) {
		lock_bruker();
		$_SESSION['message'] = $LANG['MESSAGE']['account_locked'];
	}
	header('Location: ./visperson.php?person_id='.$_GET['lock_bruker']);
	exit();
}

include('header.php');
$person_id = $_GET['person_id'];
echo person_sheet($person_id);
$person = get_person($person_id);

if (($person[type] == 'arrangor') || ($config['calamar_installed'])) {
	echo '
		<hr width="50%">
		<h2 align="center">'.$LANG['MISC']['user_account'].'</h2>
	';
	$bruker = get_bruker($person['person_id']);
	if (!$bruker) {
		echo '
			<div align="center">
			<h4>'.$LANG['MISC']['no_account'].'</h4>
			<br>
		';
		if (is_koordinator()) {
			echo '
			<button onClick="javascript:window.location=\'./editbruker.php?person_id='.$person['person_id'].'&amp;action=opprett_bruker\';">'.$LANG['MISC']['create'].'</button>
			';
		}
		echo '
			<br><br>
			</div>
		';
	} else {
		echo '
			<table align="center">
				<tr>
					<td>&nbsp;</td>
				</tr>
		';
		$levels = array('1'=>$LANG['MISC']['player'], '5'=>$LANG['MISC']['organizer'], '10'=>$LANG['MISC']['coordinator'], '20'=>$LANG['MISC']['administrator']);
		if ($bruker['nowlog'] != 0) {
			$lastlog = '<span class="green">'.$LANG['MISC']['now'].'</span>';
		} elseif (!$bruker['lastlog']) {
			$lastlog = '<span class="red">'.$LANG['MISC']['never'].'</span>';
		} else {
			$lastlog = ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $bruker['lastlog']));
		}
		echo '
			<tr>
				<td><strong>'.$LANG['MISC']['username'].'</strong></td>
				<td>'.$bruker['brukernavn'].'</td>
		';
		if (is_modifiable($bruker['person_id'])) {
			echo '
				<td><button onClick="javascript:window.location=\'./editbruker.php?person_id='.$person['person_id'].'&action=nytt_brukernavn\';">'.$LANG['MISC']['edit'].'</button></td>
			';
		}
		echo '
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['password'].'</strong></td>
		';
		if (!$bruker['passord']) {
			echo '
				<td>'.$LANG['MISC']['none'].'</td>
			';
			if (is_modifiable($bruker['person_id'])) {
				echo '
					<td><button onClick="javascript:window.location=\'./editbruker.php?person_id='.$person['person_id'].'&action=nytt_passord\';">'.$LANG['MISC']['create'].'</button></td>
				';
			}
		} else {
			echo '
				<td><em>&lt;'.$LANG['MISC']['encrypted'].'&gt;</em></td>
			';
			if (is_modifiable($bruker['person_id'])) {
				echo '
					<td><button onClick="javascript:window.location=\'./editbruker.php?person_id='.$person['person_id'].'&action=nytt_passord\';">'.$LANG['MISC']['change'].'</button></td>
				';
			}
		}
		echo '
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['userlevel'].'</strong></td>
				<td>'.$levels[$bruker['level']].'</td>
		';
		if (is_modifiable($bruker['person_id']) && is_koordinator() && !is_last_admin($bruker['person_id']) && ($person['type'] != 'spiller')) {
			echo '
				<td><button onClick="javascript:window.location=\'./editbruker.php?person_id='.$person['person_id'].'&action=nytt_level\';">'.$LANG['MISC']['edit'].'</button></td>
			';
		}
		echo '
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['last_seen'].'</strong></td>
				<td colspan="2">'.$lastlog.'</td>
			</tr>
		';
		if ($bruker['locked']) {
			echo '
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" class="red" align="center"><strong>'.$LANG['MISC']['account_locked'].'</strong></td>
				</tr>
			';
			if (is_admin()) {
				echo '
					<tr>
						<td colspan="3" align="center"><button onClick="javascript:confirmAction(\''.$LANG['JSBOX']['unlock_account'].'\', \'./visperson.php?unlock_bruker='.$person['person_id'].'\');">'.$LANG['MISC']['unlock_account'].'</button></td>
					</tr>
				';
			}
		} elseif (is_admin() && !is_last_admin($person['person_id'])) {
			echo '
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" align="center"><button type="button" onClick="javascript:confirmAction(\''.$LANG['JSBOX']['lock_account'].'\', \'./visperson.php?lock_bruker='.$person['person_id'].'\');">'.$LANG['MISC']['lock_account'].'</button></td>
				</tr>
			';
		}
		echo '
			</table>
		';
	}
}
	echo '
		<hr width="50%">
		<h2 align="center">'.$LANG['MISC']['history'].'</h2>
		<br>
		<table align="center" cellspacing="0" width="50%">
			<tr class="highlight">
				<td>'.$LANG['MISC']['game'].'</td>
				<td>'.$LANG['MISC']['character_s'].'</td>
			</tr>
	';
	$spill = get_spillhistorikk($person['person_id']);
	if ($spill) {
		foreach ($spill as $spillinfo) {
			$historikk[$spillinfo['spill_id']]['spillnavn'] = $spillinfo['navn'];
			$historikk[$spillinfo['spill_id']]['spill_id'] = $spillinfo['spill_id'];
		}
	} else {
		$historikk[0]['spillnavn'] = $LANG['MISC']['none'];
	}
	unset($spill);
	foreach ($historikk as $data) {
		echo '
			<tr>
		';
		if (strtolower($data['spillnavn']) != strtolower($LANG['MISC']['none'])) {
			echo '
				<td nowrap><a href="./vispaamelding.php?person_id='.$person['person_id'].'&amp;spill_id='.$data['spill_id'].'">'.$data['spillnavn'].'</a></td>
				<td>
			';
			if ($roller = get_spiller_roller($person['person_id'], $data['spill_id'])) {
				foreach ($roller as $rolle) {
					echo '<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a><br>';
				}
			} else {
				echo $LANG['MISC']['none'];
			}
			echo '
				</td>
			';
		} else {
			echo '
				<td>'.$LANG['MISC']['none'].'</td>
				<td>'.$LANG['MISC']['none'].'</td>
			';
		}
		echo '
			</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		';
	}
	echo '
		<tr>
			<td>&nbsp;</td>
		</tr>
	</table>';

include('footer.php');
?>
