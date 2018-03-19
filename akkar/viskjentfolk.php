<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             viskjentfolk.php                            #
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

if ($_POST['ny_kjentfolk']) {
	opprett_kjentfolk($_POST['rolle_id'], $_POST['ny_kjentfolk'], $_POST['spill_id'], $_POST['level'], $_POST['kjentgrunn']);
	$_SESSION['message'] = $LANG['MESSAGE']['acquaintance_created'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_GET['slett_kjentfolk']) {
	slett_kjentfolk();
	$_SESSION['message'] = $LANG['MESSAGE']['acquaintance_deleted'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_GET['rolle_id'].'&spill_id='.$_GET['spill_id']);
	exit();
} elseif ($_GET['slett_kjentgruppe']) {
	slett_kjentgruppe();
	$_SESSION['message'] = $LANG['MESSAGE']['group_acquaintance_deleted'];
	header('Location: ./viskjentfolk.php?vis=kjentgrupper&rolle_id='.$_GET['rolle_id'].'&spill_id='.$_GET['spill_id']);
	exit();
} elseif ($_POST['ny_kjentfolk_liste']) {
	oppdater_kjentfolk_liste();
	$_SESSION['message'] = $LANG['MESSAGE']['acquaintances_updated'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id'].'&vis=roller_liste');
	exit();
} elseif ($_POST['ny_kjentgrupper_liste']) {
	oppdater_kjentgrupper_liste();
	$_SESSION['message'] = $LANG['MESSAGE']['group_acquaintances_updated'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id'].'&vis=grupper_liste');
	exit();
} elseif ($_POST['ny_folkkjent_liste']) {
	oppdater_folkkjent_liste();
	$_SESSION['message'] = $LANG['MESSAGE']['reverse_acquaintances_updated'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['kjent_rolle_id'].'&spill_id='.$_POST['spill_id'].'&vis=folkkjent');
	exit();
} elseif ($_POST['ny_kjentgruppe']) {
	opprett_kjentgruppe();
	$_SESSION['message'] = $LANG['MESSAGE']['group_acquaintance_created'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id'].'&vis=kjentgrupper');
	exit();
} elseif ($_POST['oppdater_kjentfolk']) {
	oppdater_kjentfolk();
	$_SESSION['message'] = $LANG['MESSAGE']['entry_updated'];
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_POST['import'] > 0) {
	if (importer_kjentfolk($_POST['import'], $_POST['rolle_id'], $_POST['spill_id'])) {
		$_SESSION['message'] .= $LANG['MESSAGE']['acquaintances_imported'];
	} else {
		$_SESSION['message'] .= $LANG['MESSAGE']['no_acquaintances_to_import'];
	}
	header('Location: ./viskjentfolk.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id'].'&vis='.$_POST['vis'].'');
	exit();
}

if (!$_GET['vis']) {
	$vis = 'kjentroller';
} else {
	$vis = strtolower($_GET['vis']);
}

$hjelpemne = $vis;
include('header.php');

$rolle = get_rolle($_GET['rolle_id'], $spill_id);
$andre_spill = get_rolle_spill($rolle['rolle_id'], $spill_id);

$pagebuttons = '
	<table align="center">
		<tr>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vis=grupper\';">'.$LANG['MISC']['character_groups'].'</button></td>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vis=kjentgrupper\';">'.$LANG['MISC']['acquainted_groups'].'</button></td>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vis=grupper_liste\';">'.$LANG['MISC']['acquainted_groups_list'].'</button></td>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vis=kjentroller\';">'.$LANG['MISC']['acquainted_characters'].'</button></td>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vis=roller_liste\';">'.$LANG['MISC']['acquainted_characters_list'].'</button></td>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vis=folkkjent\';">'.$LANG['MISC']['acquainted_characters_reverse_list'].'</button></td>
		</tr>
		<tr>
			<td colspan="6" class="nospace">
				<table align="center">
					<tr>
						<td class="nospace"><button onClick="javascript:window.location=\'./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['charactersheet'].'</button></td>
';
//if ($vis != 'folkkjent') {
$dloadbuttons = '
						<td class="nospace"><button onClick="javascript:window.location=\'./download.php?rtf='.$vis.'&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['download'].' (.rtf)</button></td>
						<td class="nospace"><button onClick="javascript:window.location=\'./download.php?pdf='.$vis.'&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['download'].' (.pdf)</button></td>
						<td class="nospace"><button onClick="javascript:window.location=\'./download.php?txt='.$vis.'&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['download'].' (.txt)</button></td>
';
//}
	$pagebuttons .= '
%%DOWNLOAD%%
					</tr>
				</table>
			</td>
		</tr>
	';
	if ((($vis == 'kjentroller') || ($vis == 'roller_liste')) && (is_array($andre_spill))) {
		$pagebuttons .= '
		<tr>
			<td colspan="6">
				<form name="import_kjentfolkform" action="viskjentfolk.php" method="post">
				<input type="hidden" name="spill_id" value="'.$spill_id.'">
				<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
				<input type="hidden" name="vis" value="'.$vis.'">
				<table align="center">
					<tr>
						<td class="nospace"><h4 class="nospace">'.$LANG['MISC']['import_from_game'].':&nbsp;</h4></td>
						<td class="nospace"><select name="import">
							<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
							'.print_liste($andre_spill, 0).'
							</select>
						</td>
						<td class="nospace"><button type="submit">'.$LANG['MISC']['import'].'</button></td>
					</tr>
				</table>
				</form>
			</td>
		</tr>
		';
	}
	$pagebuttons .= '
	</table>
';
echo '
	<h2 align="center">'.$LANG['MISC']['acquaintances'].'</h2>
	<h3 align="center">'.$rolle['navn'].'</h3>
	<br>
';

if ($vis == 'kjentroller') {
	$kjentfolk = get_rolle_kjentfolk($rolle['rolle_id'], $rolle['spill_id']);
	if ($kjentfolk) {
		$pagebuttons = str_replace('%%DOWNLOAD%%', $dloadbuttons, $pagebuttons);
	} else {
		$pagebuttons = str_replace('%%DOWNLOAD%%', '', $pagebuttons);
	}
	echo '
		'.$pagebuttons.'
		<br>
		<h5 align="center">'.$LANG['MESSAGE']['acquaintances'].'</h5>
		<br>
	';
	if (!$kjentfolk) {
		echo '
			<h4 align="center">'.$LANG['MISC']['no_acquaintances'].'</h4>
			<br><br>
		';
	} else {
		echo '
			<br>
		<table width="100%">
			<tr>
		';
		foreach($kjentfolk as $kjentdata) {
			$kjentspiller = get_person($kjentdata['spiller_id']);
			if (!$kjentspiller) {
				$spillernavn = $LANG['MISC']['none'];
			} else {
				$spillernavn = '<a href="./vispaamelding.php?person_id='.$kjentspiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].'</a>';
			}
			$beskrivelse = $kjentdata[beskrivelse . $kjentdata['level']];
			$kjentspiller['bilde'] = mugshot($kjentspiller);
			echo '
				<td width="50%">
				<table width="100%" class="bordered">
					<tr>
						<td rowspan="3">
							<img src="'.$kjentspiller['bilde'].'" width="60" heigth="75" class="foto">
					</td>
					<td class="highlight" width="90%" style="height: 1em;">
						<a href="./visrolle.php?rolle_id='.$kjentdata['kjent_rolle_id'].'&amp;spill_id='.$kjentdata['spill_id'].'">'.$kjentdata['navn'].'</a> ('.$spillernavn.')
					</td>
				</tr>
				<tr>
					<td valign="top" style="height: 1em;">
						'.$kjentdata['kjentgrunn'].'
					</td>
				</tr>
				<tr>
					<td valign="top">'.nl2br($beskrivelse).'</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<button onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;slett_kjentfolk='.$kjentdata['kjent_rolle_id'].'\';">'.$LANG['MISC']['remove'].'</button>
						<button onClick="javascript:window.location=\'./editkjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;edit_kjentrolle='.$kjentdata['kjent_rolle_id'].'\';">'.$LANG['MISC']['edit'].'</button>
					</td>
				</tr>
			</table>
			</td>
		';
		check_for_new_row($j, 2);
		}
		echo '
			</tr>
		</table>
		';
	}
	echo '
		<script language="JavaScript" type="text/javascript">
			function validate(form) {
				if (form.ny_kjentfolk.value == \'\') {
					window.alert(\''.$LANG['JSBOX']['select_character'].'\');
					form.ny_kjentfolk.focus();
					return false;
				}
				if (form.level.value == \'\') {
					window.alert(\''.$LANG['JSBOX']['select_acquaintance_level'].'\');
					form.level.focus();
					return false;
				}
				if (form.kjentgrunn.value == \'\') {
					window.alert(\''.$LANG['JSBOX']['select_acquaintance_relation'].'\');
					form.kjentgrunn.focus();
					return false;
				}
				return true;
			}
		</script>
		<form name="nykjentfolkform" action="./viskjentfolk.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
		<input type="hidden" name="spill_id" value="'.$rolle['spill_id'].'">
		<table align="center" cellspacing="0" class="bt">
			<tr>
				<td colspan="4" align="center"><h4 class="table">'.$LANG['MISC']['new_acquaintance'].'</h4></td>
			</tr>
			<tr class="highlight">
				<td>'.$LANG['MISC']['character'].'</td>
				<td>'.$LANG['MISC']['level'].'</td>
				<td>'.$LANG['MISC']['relation'].'</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					<select name="ny_kjentfolk"><option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
	';
	$spillroller = get_roller($spill_id);
	foreach ($spillroller as $spillrolle) {
		if ($spillrolle[rolle_id] != $rolle['rolle_id']) {
			if (!get_kjentfolk_data($rolle['rolle_id'], $spillrolle['rolle_id'], $spillrolle['spill_id'])) {
				$spiller = get_person($spillrolle['spiller_id']);
				if ($spiller) {
					$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'];
				} else {
					$spillernavn = $LANG['MISC']['none'];
				}
				echo '<option value="'.$spillrolle['rolle_id'].'">'.$spillrolle['navn'].' ('.$spillernavn.')</option>';
			}
		}
	}
	echo '
					</select>
				</td>
				<td>
					<select name="level">
						<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
						<option value="1">'.$LANG['MISC']['intimate'].'</option>
						<option value="2">'.$LANG['MISC']['medium'].'</option>
						<option value="3">'.$LANG['MISC']['barely'].'</option>
					</select>
				</td>
				<td>
					<input type="text" name="kjentgrunn">
				</td>
				<td>
					<button type="submit" onClick="javascript:return validate(document.nykjentfolkform);">'.$LANG['MISC']['create'].'</button>
				</td>
			</tr>
		</table>
		</form>
	';
} elseif ($vis == 'grupper') {
	$grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id']);

	if ($grupper) {
		$pagebuttons = str_replace('%%DOWNLOAD%%', $dloadbuttons, $pagebuttons);
	} else {
		$pagebuttons = str_replace('%%DOWNLOAD%%', '', $pagebuttons);
	}
	echo '
		'.$pagebuttons.'
		<br>
		<h5 align="center">'.$LANG['MESSAGE']['acquaintances_groupmembers'].'</h5>
		<br>
	';
	if (!$grupper) {
		echo '
		<h4 align="center">'.$LANG['MISC']['no_group_memberships'].'</h4>
		<br><br>
	';
	} else {
		echo '
		<table width="100%" border="0">
		<tr>
		';
		foreach ($grupper as $gruppe) {
			echo '
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center"><h3 class="table"><a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a></h3></td>
			</tr>
			<tr>
			';
			$j = 0;
			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			foreach ($medlemmer as $medlem) {
				if ($medlem['rolle_id'] != $rolle['rolle_id']) {
					$spiller = get_person($medlem['spiller_id']);
					if (!$spiller) {
						$spillernavn = $LANG['MISC']['none'];
					} else {
						$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'];
					}
					$spiller['bilde'] = mugshot($spiller);
					echo '
					<td width="50%">
					<table width="100%" class="bordered" border="0">
						<tr>
							<td rowspan="3">
								<img src="'.$spiller['bilde'].'" width="60" heigth="75" class="foto">
						</td>
						<td class="highlight" width="90%" style="height: 1em;">
							<a href="./visrolle.php?rolle_id='.$medlem['rolle_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$medlem['navn'].'</a> (<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spillernavn.'</a>)
						</td>
					</tr>
					<tr>
						<td valign="top">'.nl2br($medlem['beskrivelse_gruppe']).'</td>
					</tr>
					</table>
					</td>
					';
					check_for_new_row($j, 2);
				}
			}
		}
		echo '
				</tr>
			</table>
		';
	}
} elseif ($vis == 'roller_liste') {
	$buttons = '
	<table align="center">
		<tr>
			<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
			<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
		</tr>
	</table>
	';
	echo str_replace('%%DOWNLOAD%%', $dloadbuttons, $pagebuttons).'
	<br>
	<h5 align="center">'.$LANG['MESSAGE']['acquaintances_list'].'</h5>
	<br>
	<script language="JavaScript" type="text/javascript">
		function activate_fields(rolle_id) {
			if (document.kjentfolkform.elements[rolle_id + \'\[kjent\]\'].checked == true) {
				document.kjentfolkform.elements[rolle_id + \'\[level\]\'][0].disabled = false;
				document.kjentfolkform.elements[rolle_id + \'\[level\]\'][1].disabled = false;
				document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].disabled = false;
				document.kjentfolkform.elements[rolle_id + \'\[kjentgrunn\]\'].disabled = false;
				if ((document.kjentfolkform.elements[rolle_id + \'\[level\]\'][0].checked == false) && (document.kjentfolkform.elements[rolle_id + \'\[level\]\'][1].checked == false) && (document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].checked == false)) {
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].checked = true;
				}
			} else {
				document.kjentfolkform.elements[rolle_id + \'\[level\]\'][0].disabled = true;
				document.kjentfolkform.elements[rolle_id + \'\[level\]\'][1].disabled = true;
				document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].disabled = true;
				document.kjentfolkform.elements[rolle_id + \'\[kjentgrunn\]\'].disabled = true;
			}
		}
	</script>
	<form name="kjentfolkform" action="./viskjentfolk.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
	<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
	<input type="hidden" name="spill_id" value="'.$rolle['spill_id'].'">
	<input type="hidden" name="ny_kjentfolk_liste" value="yes">
	'.$buttons.'
	<table align="center" cellspacing="0">
		<tr class="highlight">
			<td>'.$LANG['MISC']['character'].'</td>
			<td>'.$LANG['MISC']['acquainted'].'</td>
			<td>'.$LANG['MISC']['intimate'].'</td>
			<td>'.$LANG['MISC']['medium'].'</td>
			<td>'.$LANG['MISC']['barely'].'</td>
			<td>'.$LANG['MISC']['relation'].'</td>
		</tr>
	';
	$spillroller = get_roller($spill_id);
	foreach ($spillroller as $spillrolle) {
		if ($spillrolle['rolle_id'] != $rolle['rolle_id']) {
			$kjentdata = get_kjentfolk_data($rolle['rolle_id'], $spillrolle['rolle_id'], $spillrolle['spill_id']);
			$spiller = get_person($spillrolle['spiller_id']);
			if (!$spiller) {
				$spillernavn = $LANG['MISC']['none'];
			} else {
				$spillernavn = '<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
			}
			echo '
			<tr>
				<td><a href="./visrolle.php?rolle_id='.$spillrolle['rolle_id'].'&amp;spill_id='.$spillrolle['spill_id'].'">'.$spillrolle['navn'].'</a> ('.$spillernavn.')</td>
				<td align="center"><input type="checkbox" name="'.$spillrolle['rolle_id'].'[kjent]"'; if ($kjentdata) { echo ' checked'; } echo ' onClick="javascript:activate_fields(\''.$spillrolle['rolle_id'].'\');"></td>
				<td align="center"><input type="radio" name="'.$spillrolle['rolle_id'].'[level]" value="1"'; if ($kjentdata['level'] == 1) { echo ' checked'; } if (!$kjentdata) { echo ' disabled'; } echo '></td>
				<td align="center"><input type="radio" name="'.$spillrolle['rolle_id'].'[level]" value="2"'; if ($kjentdata['level'] == 2) { echo ' checked'; } if (!$kjentdata) { echo ' disabled'; } echo '></td>
				<td align="center"><input type="radio" name="'.$spillrolle['rolle_id'].'[level]" value="3"'; if ($kjentdata['level'] == 3) { echo ' checked'; } if (!$kjentdata) { echo ' disabled'; } echo '></td>
				<td><input type="text" name="'.$spillrolle['rolle_id'].'[kjentgrunn]" value="'.$kjentdata['kjentgrunn'].'"'; if (!$kjentdata) { echo ' disabled'; } echo '></td>
			</tr>
			';
		}
	}
	echo '
		</table>
		'.$buttons.'
		</form>
	';
} elseif ($vis == 'grupper_liste') {
		$buttons = '
		<table align="center">
			<tr>
				<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
				<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
			</tr>
		</table>
		'.str_replace('%%DOWNLOAD%%', $dloadbuttons, $pagebuttons).'
		<br>
		<h5 align="center">'.$LANG['MESSAGE']['acquainted_groups_list'].'</h5>
		<br>
		<br>
		<script language="JavaScript" type="text/javascript">
			function activate_fields(gruppe_id) {
				if (document.kjentfolkform.elements[gruppe_id + \'\[kjent\]\'].checked == true) {
					document.kjentfolkform.elements[gruppe_id + \'\[kjentgrunn\]\'].disabled = false;
				} else {
					document.kjentfolkform.elements[gruppe_id + \'\[kjentgrunn\]\'].disabled = true;
				}
			}
		</script>
		<form name="kjentfolkform" action="./viskjentfolk.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
		<input type="hidden" name="spill_id" value="'.$rolle['spill_id'].'">
		<input type="hidden" name="ny_kjentgrupper_liste" value="yes">
		';
		if ($grupper = get_grupper($rolle['spill_id'])) {
			echo $buttons.'
				<table align="center" cellspacing="0">
				<tr class="highlight">
					<td>'.$LANG['MISC']['group'].'</td>
					<td>'.$LANG['MISC']['description'].'</td>
					<td>'.$LANG['MISC']['acquainted'].'</td>
					<td>'.$LANG['MISC']['relation'].'</td>
				</tr>
			';
			$kjentdata = get_kjentgrupper($rolle['rolle_id'], $spill_id);
			foreach ($grupper as $gruppe) {
				if (!rolle_er_medlem($rolle['rolle_id'], $gruppe['gruppe_id'], $gruppe['spill_id'])) {
					echo '
					<tr>
						<td><a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a></td>
						<td>'.nl2br($gruppe['beskrivelse']).'</td>
						<td align="center"><input type="checkbox" name="'.$gruppe['gruppe_id'].'[kjent]"'; if ($kjentdata[$gruppe['gruppe_id']]) { echo ' checked'; } echo ' onClick="javascript:activate_fields(\''.$gruppe['gruppe_id'].'\');"></td>
						<td><input type="text" name="'.$gruppe['gruppe_id'].'[kjentgrunn]" value="'.$kjentdata[$gruppe['gruppe_id']]['kjentgrunn'].'"'; if (!$kjentdata[$gruppe['gruppe_id']]) { echo ' disabled'; } echo '></td>
					</tr>
					';
				}
			}
			echo '
				</table>
				'.$buttons.'
				</form>
			';
		} else {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_groups'].'</h4>
			';
		
		
		}
} elseif ($vis == 'kjentgrupper') {
	$grupper = get_kjentgrupper($rolle['rolle_id'], $rolle['spill_id']);

	if ($grupper) {
		$pagebuttons = str_replace('%%DOWNLOAD%%', $dloadbuttons, $pagebuttons);
	} else {
		$pagebuttons = str_replace('%%DOWNLOAD%%', '', $pagebuttons);
	}
	echo $pagebuttons.'
	<br>
	<h5 align="center">'.$LANG['MESSAGE']['acquainted_groups'].'</h5>
	<br>
	';
	if (!$grupper) {
		echo '
			<h4 align="center">'.$LANG['MISC']['no_acquainted_groups'].'</h4>
			<br><br>
		';
	} else {
		echo '
		<table width="100%" border="0">
		<tr>
		';
		foreach ($grupper as $gruppe) {
			echo '
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
				</tr>
			<tr>
				<td colspan="3" align="center"><h3 class="table"><a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a></h3></td>
			</tr>
			<tr>
				<td colspan="3" align="center"><h5 class="table">'.$gruppe['kjentgrunn'].'</h5></td>
			</tr>
			<tr>
				<td colspan="3" align="center">
					<button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?vis='.$vis.'&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$spill_id.'&amp;slett_kjentgruppe='.$gruppe['gruppe_id'].'\';">'.$LANG['MISC']['remove'].'</button>
				</td>
			</tr>
			<tr>
			';
			$j = 0;
			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				echo '
					<td align="center" colspan="3"><h4 class="table">'.$LANG['MISC']['empty_group'].'</h4><br><br></td>
				';
			} else {
				foreach ($medlemmer as $medlem) {
					if ($medlem['rolle_id'] != $rolle['rolle_id']) {
						$spiller = get_person($medlem['spiller_id']);
						if (!$spiller) {
							$spillernavn = $LANG['MISC']['none'];
						} else {
							$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'];
						}
						$spiller['bilde'] = mugshot($spiller);
						echo '
						<td width="50%">
						<table width="100%" class="bordered">
							<tr>
								<td rowspan="3">
									<img src="'.$spiller['bilde'].'" width="60" heigth="75" class="foto">
								</td>
							<td class="highlight" width="90%" style="height: 1em;">
								<a href="./visrolle.php?rolle_id='.$medlem['rolle_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$medlem['navn'].'</a> (<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spillernavn.'</a>)
							</td>
						</tr>
						<tr>
							<td valign="top">'.nl2br($medlem['beskrivelse_gruppe']).'</td>
						</tr>
					</table>
					</td>
					';
					check_for_new_row($j, 2);
					}
				}
			}
		}
		echo '
			</tr>
		</table>
		';
	}
	echo '
		<script language="JavaScript" type="text/javascript">
			function validate(form) {
				if (form.ny_kjentgruppe.value == \'\') {
					window.alert(\''.$LANG['JSBOX']['select_group'].'\');
					form.ny_kjentgruppe.focus();
					return false;
				}
				if (form.kjentgrunn.value == \'\') {
					window.alert(\''.$LANG['JSBOX']['select_group_acquaintance_relation'].'\');
					form.kjentgrunn.focus();
					return false;
				}
				return true;
			}
		</script>
		<form name="nykjentgruppeform" action="./viskjentfolk.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
		<input type="hidden" name="spill_id" value="'.$rolle['spill_id'].'">
		<table align="center" cellspacing="0" class="bt">
			<tr>
				<td colspan="4" align="center"><h4 class="table">'.$LANG['MISC']['new_acquainted_group'].'</h4></td>
			</tr>
			<tr class="highlight">
				<td>'.$LANG['MISC']['group'].'</td>
				<td>'.$LANG['MISC']['relation'].'</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					<select name="ny_kjentgruppe"><option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
	';
	$grupper = get_grupper($spill_id);
	foreach ($grupper as $gruppe) {
		if (!rolle_er_medlem($rolle['rolle_id'], $gruppe['gruppe_id'], $rolle['spill_id'])) {
			if (!get_kjentgruppe_data($rolle['rolle_id'], $gruppe['gruppe_id'], $gruppe['spill_id'])) {
				echo '<option value="'.$gruppe['gruppe_id'].'">'.$gruppe['navn'].'</option>';
			}
		}
	}
	echo '
					</select>
				</td>
				<td>
					<input type="text" name="kjentgrunn">
				</td>
				<td>
					<button type="submit" onClick="javascript:return validate(document.nykjentgruppeform);">'.$LANG['MISC']['create'].'</button>
				</td>
			</tr>
		</table>
		</form>
	';
} elseif ($vis == 'folkkjent') {
		$buttons = '
		<table align="center">
			<tr>
				<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
				<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
			</tr>
		</table>
		';
		echo '
		'.str_replace('%%DOWNLOAD%%', '', $pagebuttons).'
		<br>
		<h5 align="center">'.$LANG['MESSAGE']['reverse_acquaintances_list'].'</h5>
		<br>
		<script language="JavaScript" type="text/javascript">
			function activate_fields(rolle_id) {
				if (document.kjentfolkform.elements[rolle_id + \'\[kjent\]\'].checked == true) {
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][0].disabled = false;
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][1].disabled = false;
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].disabled = false;
					document.kjentfolkform.elements[rolle_id + \'\[kjentgrunn\]\'].disabled = false;
					if ((document.kjentfolkform.elements[rolle_id + \'\[level\]\'][0].checked == false) && (document.kjentfolkform.elements[rolle_id + \'\[level\]\'][1].checked == false) && (document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].checked == false)) {
						document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].checked = true;
					}
				} else {
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][0].disabled = true;
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][1].disabled = true;
					document.kjentfolkform.elements[rolle_id + \'\[level\]\'][2].disabled = true;
					document.kjentfolkform.elements[rolle_id + \'\[kjentgrunn\]\'].disabled = true;
				}
			}
		</script>
		<form name="kjentfolkform" action="./viskjentfolk.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="kjent_rolle_id" value="'.$rolle['rolle_id'].'">
		<input type="hidden" name="spill_id" value="'.$rolle['spill_id'].'">
		<input type="hidden" name="ny_folkkjent_liste" value="yes">
		'.$buttons.'
		<table align="center" cellspacing="0">
			<tr class="highlight">
				<td nowrap>'.$LANG['MISC']['character'].'</td>
				<td nowrap>'.$LANG['MISC']['acquainted'].'</td>
				<td nowrap>'.$LANG['MISC']['intimate'].'</td>
				<td nowrap>'.$LANG['MISC']['medium'].'</td>
				<td nowrap>'.$LANG['MISC']['barely'].'</td>
				<td nowrap>'.$LANG['MISC']['relation'].'</td>
			</tr>
		';
		$spillroller = get_roller($spill_id);
		foreach ($spillroller as $spillrolle) {
			if ($spillrolle['rolle_id'] != $rolle['rolle_id']) {
				$kjentdata = get_kjentfolk_data($spillrolle['rolle_id'], $rolle['rolle_id'], $spillrolle['spill_id']);
				$spiller = get_person($spillrolle['spiller_id']);
				if (!$spiller) {
					$spillernavn = $LANG['MISC']['none'];
				} else {
					$spillernavn = '<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
				}
				echo '
				<tr>
					<td><a href="./visrolle.php?rolle_id='.$spillrolle['rolle_id'].'&amp;spill_id='.$spillrolle['spill_id'].'">'.$spillrolle['navn'].'</a> ('.$spillernavn.')</td>
					<td align="center" nowrap><input type="checkbox" name="'.$spillrolle['rolle_id'].'[kjent]"'; if ($kjentdata) { echo ' checked'; } echo ' onClick="javascript:activate_fields(\''.$spillrolle['rolle_id'].'\');"></td>
					<td align="center" nowrap><input type="radio" name="'.$spillrolle['rolle_id'].'[level]" value="1"'; if ($kjentdata['level'] == 1) { echo ' checked'; } if (!$kjentdata) { echo ' disabled'; } echo '></td>
					<td align="center" nowrap><input type="radio" name="'.$spillrolle['rolle_id'].'[level]" value="2"'; if ($kjentdata['level'] == 2) { echo ' checked'; } if (!$kjentdata) { echo ' disabled'; } echo '></td>
					<td align="center" nowrap><input type="radio" name="'.$spillrolle['rolle_id'].'[level]" value="3"'; if ($kjentdata['level'] == 3) { echo ' checked'; } if (!$kjentdata) { echo ' disabled'; } echo '></td>
					<td align="center" nowrap><input type="text" name="'.$spillrolle['rolle_id'].'[kjentgrunn]" value="'.$kjentdata['kjentgrunn'].'"'; if (!$kjentdata) { echo ' disabled'; } echo '></td>
				</tr>
				';
			}
		}
		echo '
			</table>
			'.$buttons.'
			</form>
		';
}
include('footer.php');
?>
