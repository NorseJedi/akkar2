<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            rollefordeling.php                           #
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

if ($_POST['ny_fordeling']) {
	oppdater_rollefordeling($_POST['vis']);
	$_SESSION['message'] = $LANG['MESSAGE']['character_assignment_updated'];
	header('Location: ./rollefordeling.php?spill_id='.$spill_id.'&vis='.$_REQUEST['vis']);
	exit();
}

if ($_REQUEST['vis']) {
	$vis = $_REQUEST['vis'];
} else {
	$vis = 'alle';
}

$hjelp = $vis;
include('header.php');

echo '<h2 align="center">'.$LANG['MISC']['character_assignment'].'</h2>';
$roller = get_roller($spill_id);
if (!$roller) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_characters'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button type="button" onClick="javascript:window.location=\'roller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['characters'].'</button></td>
			</tr>
		</table>
	';
} else {
	$spillere = get_paameldte_og_arrangorer($spill_id);
	$arrangorer = get_arrangorer();
	foreach ($spillere as $spiller) {
		$spillerliste[$spiller['person_id']] = $spiller['fornavn'].' '.$spiller['etternavn'];
	}
	foreach ($arrangorer as $arrangor) {
		$arrangorliste[$arrangor['person_id']] = $arrangor['fornavn'].' '.$arrangor['etternavn'];
	}
	foreach ($roller as $rolle) {
		$rolleliste[$rolle['rolle_id']] = $rolle['navn'];
	}
	$numroller = count($roller);
	$buttons = '
		<table align="center">
		<tr>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td><button type="button" onClick="javascript:window.location=\'roller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['characters'].'</button></td>
	';
	if ($vis == 'alle') {
		$buttons .= '
		<td><button type="button" onClick="javascript:window.location=\'rollefordeling.php?spill_id='.$spill_id.'&amp;vis=sjekk\';">'.$LANG['MISC']['check_assignment'].'</button></td>
		';
	} else {
		$buttons .= '
		<td><button type="button" onClick="javascript:window.location=\'rollefordeling.php?spill_id='.$spill_id.'&amp;vis=alle\';">'.$LANG['MISC']['back'].'</button></td>
		';
	}
	$buttons .= '
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
		</tr>
		</table>
	';
	echo '
		<h4 align="center">'.$numroller.' '.$LANG['MISC']['character_s'].'</h4>
		<form name="rollefordelingform" action="./rollefordeling.php" method="post">
		'.$buttons.'
		<br>
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="ny_fordeling" value="yes">
		<input type="hidden" name="vis" value="'.$vis.'">
	';
	switch ($vis) {
		case 'alle':
			echo '
				<table border="0" cellpadding="5" cellspacing="0" align="center">
					<tr valign="top" class="highlight">
				';
				$sorting = get_sorting('./rollefordeling.php?spill_id='.$spill_id, 'navn', 'rolleorder');
				echo '<td nowrap>'.$LANG['MISC']['character'].' '.$sorting.'</td>';
			
				$sorting = get_sorting('./rollefordeling.php?spill_id='.$spill_id, 'spiller_id', 'rolleorder');
				echo '<td nowrap>'.$LANG['MISC']['player'].' '.$sorting.'</td>';
			
				$sorting = get_sorting('./rollefordeling.php?spill_id='.$spill_id, 'arrangor_id', 'rolleorder');
				echo '<td nowrap>'.$LANG['MISC']['organizer'].' '.$sorting.'</td>';
			
				echo '
					</tr>
			';
			foreach ($roller as $rolle) {
				echo '
					<tr>
						<td><a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>
						<td><select name="'.$rolle['rolle_id'].'[spiller]">
							<option value="" class="selectname">'.$LANG['MISC']['none'].'</option>'.print_liste($spillerliste, $rolle['spiller_id']).'</select>
						</td>
						<td><select name="'.$rolle['rolle_id'].'[arrangor]">
							<option value="">'.$LANG['MISC']['none'].'</option>'.print_liste($arrangorliste, $rolle['arrangor_id']).'</select>
						</td>
					</tr>
				';
			}
			echo '
				</table>
				'.$buttons.'
				</form>
			';
		break;
		case 'sjekk':
			foreach ($spillere as $spiller) {
				if (!$arrangorliste[$spiller[person_id]]) {
					if (!get_spiller_roller($spiller['person_id'], $spill_id)) {
						$ingen_rolle[$spiller['person_id']] = $spiller;
					}
				}
			}
			if (!$ingen_rolle) {
				echo '
					<h4 align="center">'.$LANG['MESSAGE']['no_unassigned_players'].'</h4>
					<br>
				';
			} else {
				foreach ($roller as $rolle) {
					if (!$rolle['spiller_id']) {
						$ledige_roller[$rolle['rolle_id']] = $rolle['navn'];
					}
				}
				echo '
					<table align="center" cellspacing="0" cellpadding="3">
						<tr class="highlight">
							<td>'.$LANG['MISC']['unassigned_players'].'</td>
							<td>'.$LANG['MISC']['paid'].'</td>
							<td>'.$LANG['MISC']['available_characters'].'</td>
						</tr>
				';
				foreach ($ingen_rolle as $person) {
					$paamelding = get_paamelding($person['person_id'], $spill_id);
					echo '
						<tr>
							<td><a href="javascript:openInfowindow(\'./vispaamelding.php?person_id='.$person['person_id'].'&amp;spill_id='.$spill_id.'\');">'.$person['fornavn'].' '.$person['etternavn'].'</a></td>
							<td>
					';
					if (!$paamelding['betalt']) {
						echo '<span class="red">'.$LANG['MISC']['no'].'</span>';
					} else {
						echo '<span class="green">'.$LANG['MISC']['yes'].'</span>';
					}
					echo '
						</td>
						<td>
					';
					if ($ledige_roller) {
						echo '
							<select name="'.$person['person_id'].'">
								<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>'.print_liste($ledige_roller, 0).'</select>
						';
					} else {
						echo $LANG['MISC']['none'].' &gt;&gt; <button type="button" onClick="javascript:window.location=\'./editrolle.php?spill_id='.$spill_id.'&amp;spiller_id='.$person['person_id'].'&amp;nyrolle=yes\';">'.$LANG['MISC']['create'].'</button>
						';
					}
					echo '
								
							</td>
						</tr>
					';
				}
				echo '
					</table>
					'.$buttons.'
					</form>
				';
			}
		break;
	}
}

include('footer.php');

?>
