<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               historikk.php                             #
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

include('header.php');


if ($_REQUEST['vis']) {
	$vis = $_REQUEST['vis'];
}
if (!$vis) {
	$vis = 'spillere';
}

if ($vis == 'spillere') {
	echo '<h2 align="center">'.$LANG['MISC']['player_history'].'</h2>';
} else {
	echo '<h2 align="center">'.$LANG['MISC']['game_history'].'</h2>';
}

$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:window.location=\'historikk.php?vis=spill\';">'.$LANG['MISC']['game_history'].'</button></td>
		<td><button onClick="javascript:window.location=\'historikk.php?vis=spillere\';">'.$LANG['MISC']['player_history'].'</button></td>
	</tr>
</table>
';

switch ($vis) {
	case 'spillere':
		$spillere = get_spillere();
		$numspillere = count($spillere);
		if ($numspillere == 0) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_players'].'</h4>
				'.$buttons;
		} else {
		echo $buttons.'<br>';
		$fields = array(
			'etternavn',
			'fornavn',
			'spill',
			'roller'
		);
		echo '
			<table border="0" cellpadding="5" cellspacing="0" align="center">
				<tr valign="top" class="highlight">
		';
		foreach ($fields as $fieldname) {
			switch ($fieldname) {
				case 'etternavn':
				case 'fornavn':
					$sorting = get_sorting('./historikk.php?spill_id='.$spill_id, $fieldname, 'personorder');
					echo '<td>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>';
					break;
				default:
					echo '<td>'.$LANG['DBFIELD'][$fieldname].'</td>';
					break;
			}
		}
		echo '
			<td>&nbsp;</td>
			</tr>
		';
		foreach ($spillere as $spiller_id=>$spiller) {
			$spill = get_spillhistorikk($spiller['person_id']);
			if ($spill) {
				foreach ($spill as $spillinfo) {
					$historikk[$spillinfo['spill_id']]['spillnavn'] = $spillinfo['navn'];
					$historikk[$spillinfo['spill_id']]['spill_id'] = $spillinfo['spill_id'];
				}
			} else {
				$historikk[0]['spillnavn'] = $LANG['MISC']['none'];
			}
			unset($spill);
			echo '
				<tr>
					<td rowspan="'.count($historikk).'" nowrap><a href="./visperson.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spiller['etternavn'].'</a></td>
					<td rowspan="'.count($historikk).'" nowrap><a href="./visperson.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spiller['fornavn'].'</a></td>
			';
			$j = 0;
			foreach ($historikk as $data) {
				if ($j != 0) {
					echo '
						<tr>
					';
				}
				if (strtolower($data['spillnavn']) != $LANG['MISC']['none']) {
					echo '
						<td nowrap><a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$data['spill_id'].'">'.$data['spillnavn'].'</a></td>
						<td>
					';
					$roller = get_spiller_roller($spiller['person_id'], $data['spill_id']);
					if (!$roller) {
						echo $LANG['MISC']['none'];
					} else {
						foreach ($roller as $rolle) {
							echo '<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.stripslashes($rolle['navn']).'</a><br>';
						}
						echo '
							</td>
						';
					}
				} else {
					echo '
						<td>'.$LANG['MISC']['none'].'</td>
						<td>'.$LANG['MISC']['none'].'</td>
					';
				}
				if ($j != 0) {
					echo '
						</tr>
					';
				} else {
					echo '
						<td rowspan="'.count($historikk).'" nowrap>
							<button onClick="javascript:window.location=\'./visperson.php?person_id='.$spiller['person_id'].'\';">'.$LANG['MISC']['playersheet'].'</button>
						</td>
					</tr>
					';
				}
				$j++;
			}
			echo '
				<tr>
					<td colspan="5" class="bb"></td>
				</tr>
			';
			unset($historikk, $spillhistorikk, $rollehistorikk);
		}
		echo '
			</table>
			'.$buttons;
		}
		break;
	case 'spill':
		$spill = get_spill();
		$numspill = count($spill);
		if ($numspill == 0) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_games'].'</h4>
				'.$buttons;
		} else {
		echo $buttons.'<br>';
		$fields = array(
			'spill',
			'spillstart',
			'spillslutt',
			'spiller',
			'roller',
		);
		echo '
			<table border="0" cellpadding="5" cellspacing="0" align="center">
				<tr valign="top" class="highlight">
		';
		foreach ($fields as $fieldname) {
			switch ($fieldname) {
				case 'spill':
					$sorting = get_sorting('./historikk.php?vis=spill', 'navn', 'spillorder');
					echo '<td>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>';
					break;
				case 'spillstart':
				case 'spillslutt':
					$sorting = get_sorting('./historikk.php?vis=spill', $fieldname, 'spillorder');
					echo '<td>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>';
					break;
				default:
					echo '<td>'.$LANG['DBFIELD'][$fieldname].'</td>';
					break;
			}
		}
		echo '
			</tr>
		';
		foreach ($spill as $spillinfo) {
			$spillere = get_paameldte($spillinfo['spill_id']);
			if (!$spillere) {
			echo '
				<tr>
					<td rowspan="1" nowrap><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>
					<td rowspan="1" nowrap title="'.ucfirst(strftime($config['long_dateformat'], $spillinfo['start'])).'">'.ucfirst(strftime($config['medium_dateformat'], $spillinfo['start'])).'</td>
					<td rowspan="1" nowrap title="'.ucfirst(strftime($config['long_dateformat'], $spillinfo['slutt'])).'">'.ucfirst(strftime($config['medium_dateformat'], $spillinfo['slutt'])).'</td>
					<td colspan="1" nowrap>'.$LANG['MISC']['no_players'].'</td>
					<td colspan="1" nowrap>'.$LANG['MISC']['no_characters'].'</td>
				</tr>
				';
			} else {
				$rows = count($spillere)+1;
				echo '
					<tr>
						<td rowspan="$rows" nowrap><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>
						<td rowspan="$rows" nowrap title="'.ucfirst(strftime($config['long_dateformat'], $spillinfo['start'])).'">'.ucfirst(strftime($config['medium_dateformat'], $spillinfo['start'])).'</td>
						<td rowspan="$rows" nowrap title="'.ucfirst(strftime($config['long_dateformat'], $spillinfo['slutt'])).'">'.ucfirst(strftime($config['medium_dateformat'], $spillinfo['slutt'])).'</td>
				';
				foreach ($spillere as $spiller) {
					$roller = get_spiller_roller($spiller['person_id'], $spillinfo['spill_id']);
					echo '
						<tr>
							<td nowrap><a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spillinfo['spill_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a></td>
							<td>
					';
					if (!$roller) {
						echo $LANG['MISC']['none'];
					} else {
						foreach ($roller as $rolle) {
							echo '<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.stripslashes($rolle['navn']).'</a><br>';
						}
					}
					echo '
							</td>
						</tr>
					';
				}
			}
			echo '
				<tr>
					<td colspan="5" class="bb"></td>
				</tr>
			';
			unset($historikk, $spillhistorikk, $rollehistorikk);
		}
		echo '
			</table>
			'.$buttons;
		}
		break;
}

include('footer.php');

?>