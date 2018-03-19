<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               editrolle.php                             #
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
$rolle_id = $_REQUEST['rolle_id'];
$arrownum = 0;
if ($_GET['override_lock']) {
	unlock_rolle($rolle_id, $spill_id);
	$_SESSION['message'] = $LANG['MESSAGE']['characterlock_override'];
	header('Location: ./editrolle.php?rolle_id='.$rolle_id.'&spill_id='.$spill_id);
	exit();
} elseif (($locked = check_lock_rolle($rolle_id, $spill_id)) && ($locked[1] != $_SESSION['person_id'])) {
	include('header.php');
	$arrangor = get_person($locked[1]);
	echo '
		<div align="center">
		<h3>'.$LANG['MESSAGE']['character_locked_by'].' '.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</h3>
		<h4>'.$LANG['MESSAGE']['lock_created_at'].' '.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $locked[0])).'</h4>
		<br>
		<button onClick="javascript:return confirmOverride(\'./editrolle.php?rolle_id='.$rolle_id.'&amp;spill_id='.$spill_id.'&amp;override_lock=yes\');">'.$LANG['MISC']['override'].'</button>
		</div>
	';
	exits();
}

include('header.php');
echo '
	<form name="editrolle" action="visrolle.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
';

if ($_GET['deaktiviser_rolle'] || $_GET['edit_status']) {
	if ($_GET['deaktiviser_rolle']) {
		$rolle = get_rolle($_GET['deaktiviser_rolle'], $spill_id);
		echo '
			<input type="hidden" name="spill_id" value="'.$spill_id.'">
			<input type="hidden" name="deaktiviser_rolle" value="'.$rolle['rolle_id'].'">
			<h2 align="center">'.$LANG['MISC']['deactivate'].'</h2>
		';
	} else {
		$rolle = get_rolle($_GET['rolle_id'], $spill_id);
		echo '
			<input type="hidden" name="spill_id" value="'.$spill_id.'">
			<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
			<input type="hidden" name="oppdater_status" value="yes">
			<h2 align="center">'.$LANG['MISC']['edit_deactivate_cause'].'</h2>
		';
	}
	$arrownum++;
	echo '
		<h3 align="center">'.$rolle['navn'].'</h3>
		<br>
		<table align="center">
			<tr>
				<td colspan="2" class="highlight">'.$LANG['MISC']['deactivate_cause'].'</td>
			</tr>
			<tr>
				<td colspan="2"><textarea id="status_tekst" name="status_tekst" cols="75" rows="5">'.htmlentities(stripslashes($rolle['status_tekst'])).'</textarea></td>
			</tr>
			<tr>
			<td align="left">
			'.inputsize_less('status_tekst', $arrownum).'
			</td>
			<td align="right">
			'.inputsize_more('status_tekst', $arrownum).'
			</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
							<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
							<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		</form>
	';
	exits();
}

if ($_GET['nyrolle']) {
	if (!$spill_id) {
		$spill_liste = get_spill();
		echo '
			</form>
			<form name="velgspillform" action="editrolle.php" method="get">
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
	$fields = get_fields($table_prefix.'roller'); // Get fieldnames from the character table
	$spillinfo = get_spillinfo($spill_id); // Get game information for the game (we only need the mal_id for the character template
	$mal_id = $spillinfo['rollemal']; // Set the $mal_id
	$mal = get_rollemal($spill_id); // Get the template
	$rolle = get_dummy_rolle($spill_id); // Get an empty character array
	$rolle['arrangor_id'] = $_SESSION['person_id'];
	if ($_GET['spiller_id']) {
		$rolle['spiller_id'] = $_GET['spiller_id'];
	}
	echo '
	<input type="hidden" name="ny" value="yes">
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<h2 align="center">'.$LANG['MISC']['new_character'].'</h2>
	<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
	';
} else {
	lock_rolle($rolle_id, $spill_id);
	$rolle = get_rolle($rolle_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$mal = get_rollemal($spill_id);
	echo '
		<input type="hidden" name="edited" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="rolle_id" value="'.$rolle_id.'">
		<h2 align="center">'.$LANG['MISC']['edit_character'].'</h2>
		<h3 align="center">'.$rolle['navn'].'</h3>
		<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
	';
}

$validatefunc = '
	<script language="JavaScript" type="text/javascript">
	function check_dots(fieldname, num, max) {
		for (i = 1; i <= max; i ++) {
		    if (i <= num) {
		    	document.getElementById(\'mal_\' + fieldname + \'_\' + i).checked = true;
			} else {
		    	document.getElementById(\'mal_\' + fieldname + \'_\' + i).checked = false;
		    }
		}
	}
	function validate_rolle() {
		if (document.getElementById(\'navn\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['character_name'].'\');
			document.getElementById(\'navn\').focus();
			return false;
		}
';


$buttons = '
<table align="center">
	<tr>
';
if (!$_GET['nyrolle']) {
	$buttons .= '
		<td><button type="button" tabindex="103" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button type="button" tabindex="102" onClick="javascript:window.location=\'./visrolle.php?rolle_id='.$rolle_id.'&amp;spill_id='.$spill_id.'&amp;unlock=yes\';">'.$LANG['MISC']['charactersheet'].'</button></td>
	';
}
$buttons .= '
		<td><button type="reset" tabindex="101" onClick="javascript:return window.confirm(\''.$LANG['JSBOX']['confirm_reset'].'\');">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit" tabindex="100" onClick="javascript:return validate_rolle();">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
';

$j = 0;
echo $buttons.'<br>';

if ($spillinfo['rollekonsept']) {
	if (($_GET['nyrolle'] && $_GET['konsept_id']) || (!$_GET['nyrolle'])) {
	echo '
		<table class="bordered" align="center" width="0">
			<tr class="highlight">
				<td colspan="2">'.$LANG['MISC']['character_concept'].'</td>
			</tr>
	';
	if ($_GET['nyrolle'] && $_GET['konsept_id']) {
		$konsept = get_rollekonsept($_GET['konsept_id'], $spill_id);
	} else {
		$konsept = get_konsept_rolle($rolle_id, $spill_id);
	}
	if ($konsept) {
		if ($konseptspiller = get_person($konsept['spiller_id'])) {
			$spillerlink = '<a href="" onClick="javascript:document.editrolle.spiller_id.value='.$konseptspiller['person_id'].'; return false;">'.$konseptspiller['fornavn'].' '.$konseptspiller['etternavn'].'</a>';
		} else {
			$spillerlink = $LANG['MISC']['none'];
		}
		if ($konseptarrangor = get_person($konsept['arrangor_id'])) {
			$arrangorlink = '<a href="" onClick="javascript:document.editrolle.arrangor_id.value='.$konseptarrangor['person_id'].'; return false;">'.$konseptarrangor['fornavn'].' '.$konseptarrangor['etternavn'].'</a>';
		} else {
			$arrangorlink = $LANG['MISC']['none'];
		}
		echo '
			<tr>
				<td width="5"><strong>'.$LANG['MISC']['organizer'].':</strong></td>
				<td>'.$arrangorlink.'</td>
			<tr>
			<tr>
				<td width="5"><strong>'.$LANG['MISC']['player'].':</strong></td>
				<td>'.$spillerlink.'</td>
			<tr>
				<td colspan="2">
				<input type="hidden" name="konsept_id" value="'.$konsept['konsept_id'].'">
				<strong>'.$konsept['tittel'].'</strong>
				</td>
			</tr>
			<tr>
				<td colspan="2">'.nl2br($konsept['konsept']).'</td>
			</tr>
		';
	} else {
		echo '
			<tr>
				<td colspan="2"><strong>'.$LANG['MESSAGE']['no_characterconcept'].'</strong></td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
		';
	}
	echo '
		</table>
		<br>
	';
}
}

echo '
	<table align="center" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><button type="button" onClick="javascript:openInfowindow(\'./plott.php?spill_id='.$rolle['spill_id'].'&amp;rolle_id='.$rolle['rolle_id'].'\',500,400);">'.$LANG['MISC']['view_plot'].'...</button></td>
			<td><button type="button" onClick="javascript:openInfowindow(\'./grupper.php?spill_id='.$rolle['spill_id'].'&amp;rolle_id='.$rolle['rolle_id'].'\',500,500);">'.$LANG['MISC']['view_group'].'...</button></td>
		</tr>
	</table>
	<table border="0" align="center" width="0">
';
$tabindex = 1;
foreach ($rolle as $fieldname => $value) {
	$value = stripslashes($value);
	if (strstr($fieldname, 'field')) {
		$value = stripslashes($value);
		$fieldinfo = $mal[$fieldname];
		$extras = explode(';',$fieldinfo['extra']);
		if ($fieldinfo['mand'] == 1) {
			if ($fieldinfo['type'] == 'radio') {
				$validatefunc .= 'if (';
				for ($i = 1; $i < $extras[0]+1; $i++) {
					$validatefunc .= $ifand.'(document.getElementById(\'mal_'.$fieldname.'_'.$extras[$i].'\').checked == false)';
					$ifand = ' && ';
				}
				$validatefunc .= ') {
					window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
					document.getElementById(\'mal_'.$fieldname.'_'.$extras[1].'\').focus();
					return false;
				}
				';
				unset($ifand);
			} elseif ($fieldinfo['type'] == 'dots') {
				$validatefunc .= 'if (';
				for ($i = 1; $i < $extras[0]+1; $i++) {
					$validatefunc .= $ifand.'(document.getElementById(\'mal_'.$fieldname.'_'.$i.'\').checked == false)';
					$ifand = ' && ';
				}
				$validatefunc .= ') {
					window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
					document.getElementById(\'mal_'.$fieldname.'_1\').focus();
					return false;
				}
				';
				unset($ifand);
			} else {
				$validatefunc .= '
				if (document.getElementById(\'mal_'.$fieldname.'\').value == \'\') {
					window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
					document.getElementById(\'mal_'.$fieldname.'\').focus();
					return false;
				}
				';
			}
		}
		if (!$fieldinfo['hjelp']) {
			$fieldinfo['hjelp'] = $LANG['HELPTIP']['no_help'];
		}
		switch ($fieldinfo['type']) {
			case 'inline':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><input type="text" size="'.$extras[0].'" id="mal_'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']" value="'.htmlspecialchars($value).'">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).' '; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
					</tr>
				';
				break;
			case 'inlinebox':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>
							<table cellspacing="0" cellpadding="0">
							<tr>
							<td class="nospace"><textarea cols="'.$extras[1].'" rows="'.get_numrows($value, $extras[0]).'" id="mal_'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']">'.$value.'</textarea>'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
							<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
							</tr>
							</table>
						</td>
					</tr>
				';
				break;
			case 'box':
				$arrownum++;
				echo '
					<tr>
						<td colspan="2">
							<table align="center" border="0">
								<tr>
									<td colspan="2">
										<div style="float: right;">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</div><strong>'.$fieldinfo['fieldtitle'].'</strong>'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '
									</td>
								</tr>
								<tr>
									<td colspan="2"><textarea cols="75" rows="'.get_numrows($value, $extras[0]).'" id="mal_'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']">'.$value.'</textarea></td>
								</tr>
								<tr>
									<td align="left">
										'.inputsize_less('mal_'.$fieldname, $arrownum).'
									</td>
									<td align="right">
										'.inputsize_more('mal_'.$fieldname, $arrownum).'
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				';
				break;
			case 'listsingle':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><select id="mal_'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']">
							<option value="" style="margin-bottom: 1em; font-style: italic;">- '.$LANG['MISC']['select'].' -</option>';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						echo '<option value="'.$extras[$i].'"'; if (strtolower($value) == strtolower($extras[$i])) { echo ' selected'; } echo '>'.$extras[$i].'</option>';
					}
				echo '</select>'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']); if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
					</tr>
				';
				break;
			case 'listmulti':
				if ($value) {
					$thisval = unserialize($value);
					if (!is_array($thisval)) {
						$thisval = array();
					}
				} else {
					$thisval = array();
				}
				echo '
					<tr>
						<td valign="top"><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><select id="mal_'.$fieldname.'" tabindex="'.$tabindex++.'" name="'.$fieldname.'[]" size="'.(count($extras)-1).'" multiple>';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						echo '<option value="'.$extras[$i].'"'; if (in_array($extras[$i], $thisval)) { echo ' selected'; } echo '>'.$extras[$i].'</option>';
					}
				echo '</select>'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']); if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
					</tr>
				';
				break;
			case 'radio':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>
				';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					echo '<input type="radio" id="mal_'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']" value="'.$extras[$i].'"'; if (strtolower($value) == strtolower($extras[$i])) { echo ' checked'; } echo '>'.$extras[$i].' ';
				}
				echo hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']); if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
					</tr>
				';
				break;
			case 'check':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><input value="1" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']" type="checkbox"'; if ($value != 0) { echo ' checked'; } echo '>'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
					</tr>
				';
				break;
			case 'calc':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><em>('.$LANG['MISC']['auto_generated'].')</em></td>
					</tr>
				';
				break;
			case 'dots':
			    echo '
			        <tr>
			            <td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
			            <td>
				';
				for ($i = 1; $i <= $extras[0]; $i++) {
				    echo '<input id="mal_'.$fieldname.'_'.$i.'" type="radio" name="rolle['.$fieldname.']_'.$i.'" value="'.$i.'" onClick="javascript:check_dots(\''.$fieldname.'\', '.$i.', '.$extras[0].')"'; if ($i <= $value) { echo ' checked'; } echo '>';
				}
				echo '&nbsp;&nbsp;&nbsp;'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).' '; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
					</tr>
				';
				break;
			case 'header':
			        echo '
			                <tr>
			                        <td colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
					</tr>
				';
				break;
			case 'separator':
			        echo '
			                <tr>
			                        <td colspan="2"><hr size="2"></td>
					</tr>
				';
				break;
		}
	} else {
		switch($fieldname) {
			case 'oppdatert':
			case 'rolle_id':
			case 'locked':
			case 'bilde':
			case 'spill_id':
			case 'status':
			case 'status_id':
			case 'status_tekst':
				break;
			case 'spiller_id':
				if ($paameldte = get_paameldte_og_arrangorer($spill_id)) {
					foreach ($paameldte as $paameldt) {
						$paameldingsliste[$paameldt['person_id']] = $paameldt['fornavn'].' '.$paameldt['etternavn'];
					}
				} else {
					$paameldingsliste = array();
				}
				unset($paameldte);
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['player'].'</strong></td>
						<td><select id="'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']"><option value="0" class="selectname">'.$LANG['MISC']['select'].'</option>'.print_liste($paameldingsliste, $rolle['spiller_id']).'</select>'.hjelp_icon($LANG['MISC']['player'], $LANG['HELPTIP']['select_character_player']).'
						<button type="button" onClick="javascript:openInfowindow(\'./vispaamelding.php?spill_id='.$rolle['spill_id'].'&amp;person_id=\' + document.editrolle.spiller_id.value);">'.$LANG['MISC']['view_registration'].'...</button>
						</td>
				';
				break;
			case 'arrangor_id':
				$arrangorer = get_arrangorer();
				foreach ($arrangorer as $arrangor) {
					$arrangorliste[$arrangor['person_id']] = $arrangor['fornavn'].' '.$arrangor['etternavn'];
				}
				unset($arrangorer);
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
						<td><select id="'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']"><option value="0" class="selectname">'.$LANG['MISC']['select'].'</option>'.print_liste($arrangorliste, $rolle['arrangor_id']).'</select>'.hjelp_icon($LANG['MISC']['organizer'], $LANG['HELPTIP']['select_character_organizer']).'
						</td>
				';
				break;
			case 'intern_info':
			case 'beskrivelse1':
			case 'beskrivelse2':
			case 'beskrivelse3':
			case 'beskrivelse_gruppe':
				$arrownum++;
				echo '
					<tr>
						<td colspan="2">
							<table align="center" border="0">
								<tr>
									<td colspan="2">
										<div style="float: right;">'.hjelp_icon($LANG['DBFIELD'][$fieldname], $LANG['HELPTIP'][$fieldname]).'</div><strong>'.$LANG['DBFIELD'][$fieldname].'</strong>
									</td>
								</tr>
								<tr>
									<td colspan="2"><textarea cols="75" rows="'.get_numrows($value, 5).'" id="'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']">'.htmlspecialchars($value).'</textarea></td>
								</tr>
								<tr>
									<td align="left">
										'.inputsize_less($fieldname, $arrownum).'
									</td>
									<td align="right">
										'.inputsize_more($fieldname, $arrownum).'
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				';
				break;
			case 'navn':
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['name'].'</strong></td>
						<td><input type="text" maxlength="255" id="'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']" value="'.htmlspecialchars($value).'">'.hjelp_icon($LANG['MISC']['name'], $LANG['HELPTIP']['character_name']).'</td>
					</tr>
				';
				break;
			default:
				echo '
					<tr>
						<td><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
						<td><input type="text" id="'.$fieldname.'" tabindex="'.$tabindex++.'" name="rolle['.$fieldname.']" value="'.htmlspecialchars($value).'"></td>
					</tr>
				';
		}
	}
}
$validatefunc .= '
		return true;
	}
	</script>
';
echo '
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
