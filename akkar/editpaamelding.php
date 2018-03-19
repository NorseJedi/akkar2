<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            editpaamelding.php                           #
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

$spillinfo = get_spillinfo($spill_id);
$mal_id = $spillinfo['paameldingsmal'];
$mal = get_paameldingsmal($spill_id);
if (!$mal) {
	$mal = array();
}

for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mnder = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = date('Y'); $i >= date('Y')-99; $i--) {
	$aarliste[$i] = $i;
}
for ($i = 0; $i <= 23; $i++) {
	if ($i > 9) {
		$timer[$i] = $i;
	} else {
		$timer[$i] = '0'.$i;
	}
}
for ($i = 0; $i <= 59; $i++) {
	if ($i > 9) {
		$minutter[$i] = $i;
	} else {
		$minutter[$i] = '0'.$i;
	}
}
echo '
	<form name="editpaameldingform" action="vispaamelding.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
';
if ($_REQUEST['nypaamelding']) {
	if ($_REQUEST['nyperson']) {
		$person = array();
		$person['bilde'] = $styleimages['no_mugshot_m'];
		echo '
			<h2 align="center">'.$LANG['MISC']['new_player'].' + '.$LANG['MISC']['registration'].'</h2>
			<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
			<br>
			<input type="hidden" name="ny_person" value="yes">
			<input type="hidden" name="whereiwas" value="vispaamelding.php">
		';
		include('form_edit_person.php');
	} else {
		$person = get_person($_POST['person_id']);
		echo '
			<h2 align="center">'.$LANG['MISC']['new_registration'].'</h2>
			<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
			<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
			<input type="hidden" name="person_id" value="'.$person['person_id'].'">
			<br>
		';
	}
	$paamelding = array();
	$paamelding['paameldt'] = time();
	$paamelding['betalt'] = 0;
	foreach ($mal as $key=>$value) {
		$paamelding[$key] = '';
	}
	echo '
		<input type="hidden" name="ny_paamelding" value="yes">
	';
	$buttons = '
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button type="button" tabindex="'.$tabindex++.'" onClick="javascript:window.location=\'./paameldinger.php?spill_id='.$spill_id.'\';">Avbryt</button></td>
				<td><button type="reset" tabindex="'.$tabindex++.'">Reset</button></td>
				<td><button type="submit" tabindex="'.$tabindex++.'" onClick="javascript:return validate_paamelding();">Lagre</button></td>
			</tr>
		</table>
	';
} else {
	$person = get_person($_GET['person_id']);
	$paamelding = get_paamelding($_GET['person_id'], $_GET['spill_id']);
	echo '
	<h2 align="center">'.$LANG['MISC']['edit_registration'].'</h2>
	<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
	<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
	<br>
	<input type="hidden" name="person_id" value="'.$paamelding['person_id'].'">
	<input type="hidden" name="edited" value="yes">
	';
	$buttons = '
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button type="button" tabindex="102" onClick="javascript:window.location=\'./vispaamelding.php?person_id='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'\';">'.$LANG['MISC']['cancel'].'</button></td>
				<td><button type="button" tabindex="101" onClick="javascript:document.editpaameldingform.reset();">'.$LANG['MISC']['reset'].'</button></td>
				<td><button type="submit" tabindex="100" onClick="javascript:return validate_paamelding();">'.$LANG['MISC']['save'].'</button></td>
			</tr>
		</table>
	';
}

echo '
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<table border="0" align="center" cellspacing="0" cellpadding="3">
';
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
	function validate_paamelding() {
';
if ($_REQUEST['nyperson']) {
	$validatefunc .= '
		if (validate_person() == false) {
			return false;
		}
	';
} else {
	$validatefunc .= '
	';
}

if (!$_REQUEST['nypaamelding']) {
	$validatefunc .= '
		if (document.getElementById(\'paamelding_dag\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['registration_time'].'\');
			document.getElementById(\'paamelding_dag\').focus();
			return false;
		}
		if (document.getElementById(\'paamelding_mnd\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['registration_time'].'\');
			document.getElementById(\'paamelding_mnd\').focus();
			return false;
		}
		if (document.getElementById(\'paamelding_aar\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['registration_time'].'\');
			document.getElementById(\'paamelding_aar\').focus();
			return false;
		}
		if (document.getElementById(\'paamelding_time\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['registration_time'].'\');
			document.getElementById(\'paamelding_time\').focus();
			return false;
		}
		if (document.getElementById(\'paamelding_min\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['registration_time'].'\');
			document.getElementById(\'paamelding_min\').focus();
			return false;
		}
	';
}
$tabindex = 1;
foreach ($paamelding as $fieldname => $value) {
	$value = stripslashes($value);
	if (is_int(strpos($fieldname, 'field'))) {
		if (!$value) {
			$value = 'Ingen';
		}
		$fieldinfo = $mal[$fieldname];
		$extras = explode(';',$fieldinfo['extra']);
		if ($fieldinfo['mand'] == 1) {
			switch ($fieldinfo['type']) {
				case 'radio':
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
					break;
				case 'dots':
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
					break;
				default:
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
						<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right" nowrap><input tabindex="'.$tabindex++.'" type="text" id="mal_'.$fieldname.'" name="paamelding['.$fieldname.']" value="'.$value.'" size="'.$extras[0].'"></td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
					</tr>
				';
				break;
			case 'inlinebox':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right"><textarea tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'" name="paamelding['.$fieldname.']" cols="'.$extras[1].'" rows="'.$extras[0].'">'.$value.'</textarea></td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
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
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } echo '</td>
									<td class="nospace" align="right">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
								</tr>
								<tr>
									<td colspan="2"><textarea tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'" name="paamelding['.$fieldname.']" cols="75" rows="'.$extras[1].'">'.$value.'</textarea></td>
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
				';
				break;
			case 'listsingle':
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right" nowrap><select tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'" name="paamelding['.$fieldname.']">
							<option value="" class="selectname">- '.$LANG['MISC']['select'].' '.$fieldinfo['fieldtitle'].' -</option>';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					echo '<option value="'.$extras[$i].'"'; if ($paamelding[$fieldname] == $extras[$i]) { echo ' selected'; } echo '>'.$extras[$i].'</option>';
				}
				echo '</select></td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
					</tr>
				';
				break;
			case 'listmulti':
				$values = unserialize($value);
				unset($value);
				if (!is_array($values)) {
					$values[0] = '';
				}
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right" nowrap><select tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'" name="'.$fieldname.'[]'.'" size="'.(count($extras)-1).'" multiple>';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					echo '<option value="'.$extras[$i].'"'; if (in_array($extras[$i], $values)) { echo ' selected'; } echo '>'.$extras[$i].'</option>';
				}
				echo '</select></td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
					</tr>
				';
				break;
			case 'radio':
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right" nowrap>
				';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					echo '<input type="radio" tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'_'.$extras[$i].'" name="paamelding['.$fieldname.']" value="'.$extras[$i].'"'; if (strtolower($value) == strtolower($extras[$i])) { echo ' checked'; } echo '>'.ucwords(stripslashes($extras[$i])).' '; 
				}
				echo '
						</td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
					</tr>
				';
				break;
			case 'check':
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right" nowrap><input tabindex="'.$tabindex++.'" value="1" name="paamelding['.$fieldname.']" type="checkbox"'; if ($value != 0) { echo ' checked'; } echo '></td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
					</tr>
				';
				break;
			case 'calc':
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td align="right"><em>('.$LANG['MISC']['auto_generated'].')</em></td>
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
				    echo '<input id="mal_'.$fieldname.'_'.$i.'" tabindex="'.$tabindex++.'" type="radio" name="paamelding['.$fieldname.']_'.$i.'" value="'.$i.'" onClick="javascript:check_dots(\''.$fieldname.'\', '.$i.', '.$extras[0].')"'; if ($i <= $value) { echo ' checked'; } echo '>';
				}
				echo '
						</td>
						<td class="nospace">'.hjelp_icon($fieldinfo['fieldtitle'], $fieldinfo['hjelp']).'</td>
						<td class="nospace">'; if ($fieldinfo['mand'] == 1) { echo $mand_mark; } else { echo '&nbsp;'; } echo '</td>
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
		switch ($fieldname) {
			case 'spill_id':
			case 'annet':
			case 'rolle_id':
				break;
			case 'betalt':
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$LANG['MISC']['paid'].'</strong></td>
						<td align="right" nowrap><input tabindex="'.$tabindex++.'" name="paamelding[betalt]" type="checkbox"'; if ($value != 0) { echo ' checked'; } echo '></td>
						<td class="nospace">'.hjelp_icon($LANG['MISC']['paid'], $LANG['HELPTIP']['registration_paid_checkbox']).'</td>
					</tr>
				';
				break;
			case 'paameldt':
				echo '
					<tr>
						<td align="left" nowrap><strong>'.$LANG['MISC']['registration_time'].'</strong></td>
						<td class="nospace" align="right" nowrap>
							<table cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td ><span class="small">'.$LANG['MISC']['date'].':</span></td>
									<td align="right"><select id="paamelding_dag" tabindex="'.$tabindex++.'" name="paamelding[dag]"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, strftime('%d', $value)).'</select> <select id="paamelding_mnd" tabindex="'.$tabindex++.'" name="paamelding[mnd]"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mnder, strftime('%m', $value)).'</select> <select id="paamelding_aar" tabindex="'.$tabindex++.'" name="paamelding[aar]"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, strftime('%Y', $value)).'</select>
									</td>
								</tr>
								<tr>
									<td class="nospace"><span class="small">'.$LANG['MISC']['time'].':</span></td>
									<td class="nospace" align="right"><select id="paamelding_time" tabindex="'.$tabindex++.'" name="paamelding[time]"><option value="" class="selectname">'.$LANG['MISC']['hour'].'</option>'.print_liste($timer, strftime('%H', $value)).'</select> <select id="paamelding_min" tabindex="'.$tabindex++.'" name="paamelding[min]"><option value="" class="selectname">'.$LANG['MISC']['minute'].'</option>'.print_liste($minutter, strftime('%M', $value)).'</select>
									</td>
								</tr>
							</table>
						<td class="nospace">'.hjelp_icon($LANG['MISC']['registration_time'], $LANG['HELPTIP']['registration_time']).'</td>
						<td class="nospace">'.$mand_mark.'<br>'.$mand_mark.'</td>
						</td>
					</tr>
				';
				break;
		}
	}
}
$validatefunc .= '
		return true;
	}
	</script>
';
$arrownum++;
echo '
		<tr>
			<td colspan="2">
				<table align="center" border="0">
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><strong>'.$LANG['MISC']['generic_info'].'</strong></td>
						<td class="nospace" align="right">'.hjelp_icon($LANG['MISC']['generic_info'], $LANG['HELPTIP']['generic_info']).'</td>
			
					</tr>
					<tr>
						<td colspan="2"><textarea id="annet" tabindex="'.$tabindex++.'" name="paamelding[annet]" rows="'.get_numrows($paamelding['annet'], 5).'" cols="75">'.stripslashes($paamelding[annet]).'</textarea></td>
					</tr>
					<tr>
						<td align="left">
							'.inputsize_less('annet', $arrownum).'
						</td>
						<td align="right">
							'.inputsize_more('annet', $arrownum).'
						</td>
					</tr>
				</table>
			</td>
		</tr>
</table>
'.$validatefunc.'
'.$buttons.'
</form>
';

include('footer.php');
?>
