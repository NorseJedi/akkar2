<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             rolleforslag.php                            #
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

if ($_GET['slett_rolle']) {
	slett_rolleforslag();
	$_SESSION['message'] = $LANG['MESSAGE']['character_suggestion_deleted'];
	header('Location: ./rolleforslag.php?spill_id='.$spill_id);
	exit();
}

include('header.php');

echo '<h2 align="center">'.$LANG['MISC']['character_suggestions'].'</h2>';
$roller = get_roller_forslag($spill_id);
$numroller = count($roller);
$mal = get_rollemal($spill_id);

$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:window.location=\'editrolleforslag.php?spill_id='.$spill_id.'&amp;nyrolle=yes\';">'.$LANG['MISC']['create_character_suggestion'].'</button></td>
	</tr>
</table>
';

if (!$roller) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_character_suggestions'].'</h4>
		'.$buttons.'
	';
} else {
	$fieldnames = get_fields($table_prefix.'rolleforslag');
	$field = array();
	$tabindex = 1;
	if ($spill_id) {
		foreach ($mal as $fieldname=>$fieldinfo) {
			if ((in_array($fieldname, $fieldnames)) && (!in_array($fieldinfo['type'], $config['types_not_in_lists']))) {
				$field[$fieldname] = $fieldinfo['fieldtitle'];
			}
		}
	}
	echo '
		<h4 align="center">'.$numroller.' '.$LANG['MISC']['character_suggestion_s'].'</h4>
		'.$buttons.'
		<br>
	';
	if ((!$_REQUEST['utskrift']) && ($spill_id != 0)) {
		echo '
		<form method="post" action="rolleforslag.php" name="nyvisningform">
		<input type="hidden" name="nyrollevis" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="rolleorder" value="'.$rolleorder.'">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis[navn]"'; if ($_SESSION['rollevis']['navn']) { echo ' checked'; } echo '>'.$LANG['MISC']['name'].'</td>
		<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis[spiller_id]"'; if ($_SESSION['rollevis']['spiller_id']) { echo ' checked'; } echo '>'.$LANG['MISC']['player'].'</td>
		<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis[arrangor_id]"'; if ($_SESSION['rollevis']['arrangor_id']) { echo ' checked'; } echo '>'.$LANG['MISC']['organizer'].'</td>
		';
		$j = 4;
		if ($field) {
			foreach ($field as $fieldname => $fieldtitle) {
				echo '
				<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis['.$fieldname.']"'; if ($_SESSION['rollevis'][$fieldname]) { echo ' checked'; } echo '>'.$fieldtitle.'</td>
				';
				if (is_int($j / 5)) {
					echo '</tr><tr>';
				}
				$j++;
			}
		}
		echo '
		<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis[godkjent]"'; if ($_SESSION['rollevis']['godkjent']) { echo ' checked'; } echo '>'.$LANG['MISC']['approved'].'</td>
		<tr><td colspan="7" align="center">
		<button tabindex="'.$tabindex++.'" onClick="javascript:document.nyvisningform.submit();">'.$LANG['MISC']['change_view'].'</button>
		</td>
		</tr>
		</table>
		</form>
		';
	} elseif ($spill_id == 0) {
		echo '<div align="center" class="small">('.$LANG['MESSAGE']['view_selection_availability'].')<br><br></div>';
	}
	echo '
		<table border="0" cellpadding="3" cellspacing="0" align="center" width="90%">
			<tr class="noprint">
				<td colspan="'.count($_SESSION['rollevis']).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
		';
		if ($_SESSION['rollevis']['navn']) {
			$sorting = get_sorting('./rolleforslag.php?spill_id='.$spill_id, 'navn', 'rolleorder');
			echo '
			<td nowrap>'.$LANG['MISC']['name'].' '.$sorting.'</td>
			';
		}
		if ($_SESSION['rollevis']['spiller_id']) {
			$sorting = get_sorting('./rolleforslag.php?spill_id='.$spill_id, 'spiller_id', 'rolleorder');
			echo '
			<td nowrap>'.$LANG['MISC']['player'].' '.$sorting.'</td>
			';
		}
		if ($_SESSION['rollevis']['arrangor_id']) {
			$sorting = get_sorting('./rolleforslag.php?spill_id='.$spill_id, 'arrangor_id', 'rolleorder');
			echo '
			<td nowrap>'.$LANG['MISC']['organizer'].' '.$sorting.'</td>
			';
		}
		if ($spill_id != 0) {
			if ($field) {
				foreach ($field as $fieldname => $fieldtitle) {
					if ($_SESSION['rollevis'][$fieldname]) {
						$sorting = get_sorting('./rolleforslag.php?spill_id='.$spill_id, $fieldname, 'rolleorder');
						echo '
						<td nowrap>'.$fieldtitle.' '.$sorting.'</td>
						';
					}
				}
			}
		} else {
			$sorting = get_sorting('./rolleforslag.php?spill_id='.$spill_id, 'spill_id', 'rolleorder');
			echo '
			<td nowrap align="center">'.$LANG['MISC']['game'].' '.$sorting.'</td>
			';
		}
		if ($_SESSION['rollevis']['godkjent']) {
			$sorting = get_sorting('./rolleforslag.php?spill_id='.$spill_id, 'godkjent', 'rolleorder');
			echo '
			<td nowrap align="center">'.$LANG['MISC']['approved'].' '.$sorting.'</td>
			';
		}
	echo '
		<td colspan="3">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
	';
	if ($_SESSION['rollevis']['navn']) {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="15" type="text" id="navn_filter" title="navn_filter" onkeyup="javascript:filter_list(this.value, \'navn\');"></td>
		';
	}
	if ($_SESSION['rollevis']['spiller_id']) {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="15" type="text" id="spiller_filter" title="spiller_filter" onkeyup="javascript:filter_list(this.value, \'spiller\');"></td>
		';
	}
	if ($_SESSION['rollevis']['arrangor_id']) {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="15" type="text" id="arrangor_filter" title="arrangor_filter" onkeyup="javascript:filter_list(this.value, \'arrangor\');"></td>
		';
	}
	if ($spill_id != 0) {
		foreach ($field as $fieldname=>$fieldtitle) {
			if ($_SESSION['rollevis'][$fieldname]) {
				echo '
					<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($fieldtitle).'" type="text" id="'.$fieldname.'_filter" title="'.$fieldname.'_filter" onkeyup="javascript:filter_list(this.value, \''.$fieldname.'\');"></td>
				';
			}
		}
	} else {
		echo '
			<td align="center"><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['game']).'" type="text" id="spill_filter" title="spill_filter" onkeyup="javascript:filter_list(this.value, \'spill\');"></td>
		';
	}
	if ($_SESSION['rollevis']['godkjent']) {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['approved']).'" type="text" id="godkjent_filter" title="godkjent_filter" onkeyup="javascript:filter_list(this.value, \'godkjent\');"></td>
		';
	}
	echo '
		<td colspan="3">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($roller as $rolle_id=>$rolle) {
		echo '<tr>';
		if ($rolle['spiller'] > 0) {
			$spiller = get_person($rolle['spiller']);
			$spillernavn = '<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
		} else {
			$spillernavn = $rolle['spiller'];
		}
		$arrangor = get_person($rolle['arrangor_id']);
		if ($_SESSION['rollevis']['navn']) { echo '<td id="navn_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$rolle['navn'].'" nowrap><a href="./visrolleforslag.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>'; }
		if ($_SESSION['rollevis']['spiller_id']) { echo '<td id="spiller_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$rolle['spiller'].'" nowrap>'.$spillernavn.'</td>'; }
		if ($_SESSION['rollevis']['arrangor_id']) { echo '<td id="arrangor_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'" nowrap><a href="./visperson.php?person_id='.$arrangor['person_id'].'">'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</a></td>'; }
		foreach ($rolle as $fieldname => $value) {
			if ($field[$fieldname]) {
				if ($_SESSION['rollevis'][$fieldname]) {
					switch ($mal[$fieldname]['type']) {
						case 'calc':
							$extras = explode(';', $mal[$fieldname]['extra']);
							$calc = get_calc_formula($rolle[$mal[$extras[0]]['fieldname']], $extras[1]);
							@eval('\$calcresult = '.$calc.';');
							echo '<td nowrap id="'.$fieldname.'_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$calcresult.'" align="center">'.$calcresult.'</td>';
							break;
						case 'check':
							if ($value != 0) {
								echo '<td id="'.$fieldname.'_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$extras[0].'" nowrap>'.$extras[0].'</td>';
							} else {
								echo '<td id="'.$fieldname.'_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$extras[1].'" nowrap>'.$extras[1].'</td>';
							}
							break;
						case 'listmulti':
							if (!$value) {
								$show = $LANG['MISC']['none'].'.';
							} else {
								$values = unserialize($value);
								foreach ($values as $thisval) {
									$show .= $thisval.', ';
								}
							}
							echo '<td id="'.$fieldname.'_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.substr(trim($show), 0, -1).'" nowrap>'.substr(trim($show), 0, -1).'</td>';
							unset($show, $thisval, $values);
							break;
						default:
							echo '<td id="'.$fieldname.'_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$value.'" nowrap>'.$value.'</td>';
					}
				}
			}
		}
		if ($spill_id == 0) {
			$spillinfo = get_spillinfo($rolle['spill_id']);
			echo '<td id="spill_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$spillinfo['navn'].'" align="center" nowrap><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>';
		}
		if ($_SESSION['rollevis']['godkjent']) {
			if ($rolle['godkjent']) {
				$godkjent = unserialize($rolle['godkjent']);
				$godkjent = '<a href="./visrolle.php?rolle_id='.$godkjent[2].'&amp;spill_id='.$spill_id.'">'.$LANG['MISC']['yes'].'</a>';
				$godkjent_value = $LANG['MISC']['yes'];
			} else {
				$godkjent = $LANG['MISC']['no'];
				$godkjent_value = $LANG['MISC']['no'];
			}
			echo '<td align="center" id="godkjent_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$godkjent_value.'" nowrap>'.$godkjent.'</td>';
		}
		if ($rolle['godkjent']) {
			$editlink = 'javascript:confirmAction(\''.$LANG['JSBOX']['edit_approved_character'].'\',\'./editrolleforslag.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\');';
			$godkjennlink = 'javascript:confirmAction(\''.$LANG['JSBOX']['character_already_approved'].'\', \'./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;overfor_forslag=yes\');';
		} else {
			$editlink = 'javascript:window.location=\'./editrolleforslag.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';';
			$godkjennlink = 'javascript:window.location="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;overfor_forslag=yes";';
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nospace"><button type="button" onClick="'.$editlink.'">'.$LANG['MISC']['edit'].'</button></td>
						<td class="nospace"><button type="button" onClick="'.$godkjennlink.'">'.$LANG['MISC']['approve'].'</button></td>
						<td class="nospace"><button type="button" class="red" onClick="javascript:return confirmDelete(\''.addslashes($rolle['navn'].' ('.$spillnavn.')').'\',\'./rolleforslag.php?slett_rolle='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
					</tr>
				</table>
			</td>
		</tr>';
	}
	echo '
		</table>
		'.$buttons;
}

include('footer.php');

?>
