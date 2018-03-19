<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                roller.php                               #
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
	slett_rolle();
	$_SESSION['message'] = $LANG['MESSAGE']['character_deleted'];
	header('Location: ./roller.php?spill_id='.$spill_id);
	exit();
}

$hjelpemne = $spill_id;
include('header.php');
echo '<h2 align="center">'.$LANG['MISC']['characters'].'</h2>';

if ($_GET['vis'] == 'inaktive') {
	$roller = get_roller($spill_id, 'inaktive');
	echo '<h3 align="center">'.$LANG['MISC']['inactives'].'</h3>';
} elseif ($_GET['vis'] == 'alle') {
	$roller = get_roller($spill_id, 'alle');
	echo '<h3 align="center">'.$LANG['MISC']['all'].'</h3>';
} else {
	$roller = get_roller($spill_id);
	echo '<h3 align="center">'.$LANG['MISC']['actives'].'</h3>';
}
$numroller = count($roller);
$mal = get_rollemal($spill_id);
$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
';
if ($spill_id) {
	$buttons .= '
		<td><button onClick="javascript:window.location=\'./hentroller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['transfer_characters'].'</button></td>
		<td><button onClick="javascript:window.location=\'./sendroller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['send_characters'].'</button></td>
	';
}
$buttons .= '
		<td><button onClick="javascript:window.location=\'./utskrifter.php?print=roller&spill_id='.$spill_id.'\';">'.$LANG['MISC']['printouts'].'</button></td>
		<td><button onClick="javascript:window.location=\'editrolle.php?spill_id='.$spill_id.'&amp;nyrolle=yes\';">'.$LANG['MISC']['create_character'].'</button></td>
	</tr>
</table>
';
$buttons2 = '
	<table align="center">
		<tr>
			<td colspan="3" align="center"><button onClick="javascript:window.location=\'./roller.php?spill_id='.$spill_id.'&amp;vis=inaktive\';">'.$LANG['MISC']['inactives'].'</button>
			<td colspan="3" align="center"><button onClick="javascript:window.location=\'./roller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['actives'].'</button>
			<td colspan="3" align="center"><button onClick="javascript:window.location=\'./roller.php?spill_id='.$spill_id.'&amp;vis=alle\';">'.$LANG['MISC']['all'].'</button>
		</tr>
	</table>
';
if (!$roller) {
	echo $buttons2.'
		<h4 align="center">'.$LANG['MISC']['no_characters'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button onClick="javascript:window.location=\'editrolle.php?spill_id='.$spill_id.'&amp;nyrolle=yes\';">'.$LANG['MISC']['create_character'].'</button></td>
			</tr>
		</table>
	';
} else {
	$fieldnames = get_fields($table_prefix.'roller');
	$field = array();
	$tabindex = 1;
	if ($spill_id) {
		foreach ($mal as $fieldname=>$fieldinfo) {
			if ((in_array($fieldname, $fieldnames)) && (!in_array($fieldinfo['type'], $config['types_not_in_lists']))) {
				$field[$fieldname] = $fieldinfo['fieldtitle'];
			}
		}
	}
	echo $buttons2.'
		<h4 align="center">'.$numroller.' '.$LANG['MISC']['character_s'].'</h4>
		'.$buttons.'
		<br>
	';
	if ((!$_REQUEST['utskrift']) && ($spill_id != 0)) {
		echo '
		<form method="post" action="roller.php" name="nyvisningform">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="nyrollevis" value="yes">
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
		<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis[grupper]"'; if ($_SESSION['rollevis']['grupper']) { echo ' checked'; } echo '>'.$LANG['MISC']['groups'].'</td>
		';
		$j = 5;
		foreach ($field as $fieldname => $fieldtitle) {
			echo '
				<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="rollevis['.$fieldname.']"'; if ($_SESSION['rollevis'][$fieldname]) { echo ' checked'; } echo '>'.$fieldtitle.'</td>
			';
			if (is_int($j / 5)) {
				echo '</tr><tr>';
			}
			$j++;
		}
		echo '
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
		<table border="0" cellpadding="3" cellspacing="0" width="90%" align="center">
			<tr class="noprint">
				<td colspan="'.count($_SESSION['rollevis']).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
		';
		if ($_SESSION['rollevis']['navn']) {
			$sorting = get_sorting('./roller.php?spill_id='.$spill_id, 'navn', 'rolleorder');
			echo '
				<td nowrap>'.$LANG['MISC']['name'].' '.$sorting.'</td>
			';
		}
		if ($_SESSION['rollevis']['spiller_id']) {
			$sorting = get_sorting('./roller.php?spill_id='.$spill_id, 'spiller_id', 'rolleorder');
			echo '
				<td nowrap>'.$LANG['MISC']['player'].' '.$sorting.'</td>
			';
		}
		if ($_SESSION['rollevis']['arrangor_id']) {
			$sorting = get_sorting('./roller.php?spill_id='.$spill_id, 'arrangor_id', 'rolleorder');
			echo '
				<td nowrap>'.$LANG['MISC']['organizer'].' '.$sorting.'</td>
			';	
		}
		if ($spill_id != 0) {
			if ($_SESSION['rollevis']['grupper']) {
				echo '
					<td nowrap>'.$LANG['MISC']['groups'].'</td>
				';
			}
			foreach ($field as $fieldname => $fieldtitle) {
				if ($_SESSION['rollevis'][$fieldname]) {
					$sorting = get_sorting('./roller.php?spill_id='.$spill_id, $fieldname, 'rolleorder');
					echo '
						<td nowrap>'.$fieldtitle.' '.$sorting.'</td>
					';
				}
			}
		} else {
			$sorting = get_sorting('./roller.php?spill_id='.$spill_id, 'spill_id', 'rolleorder');
			echo '
				<td nowrap>'.$LANG['MISC']['game'].' '.$sorting.'</td>
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
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="15" type="text" id="arrangor_filter" title="arrangor_filter" onkeyup="javascript:filter_list(this.value, \'arrango\');"></td>
		';
	}
	if ($spill_id != 0) {
		if ($_SESSION['rollevis']['grupper']) {
			echo '
				<td><input class="filterbox" tabindex="'.$tabindex++.'" size="15" type="text" id="grupper_filter" title="grupper_filter" onkeyup="javascript:filter_list(this.value, \'grupper\');"></td>
			';
		}
		foreach ($field as $fieldname=>$fieldtitle) {
			if ($_SESSION['rollevis'][$fieldname]) {
				echo '
					<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($fieldtitle).'" type="text" id="'.$fieldname.'_filter" title="'.$fieldname.'_filter" onkeyup="javascript:filter_list(this.value, \''.$fieldname.'\');"></td>
				';
			}
		}
	} else {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['game']).'" type="text" id="spill_filter" title="spill_filter" onkeyup="javascript:filter_list(this.value, \'spill\');"></td>
		';
	}
	echo '
		<td colspan="3">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($roller as $rolle_id=>$rolle) {
	
		echo '<tr title="'.$rolle['navn'].'">';
		
		$spiller = get_person($rolle['spiller_id']);
		$arrangor = get_person($rolle['arrangor_id']);
		if ($_SESSION['rollevis']['navn']) { echo '<td id="navn_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$rolle['navn'].'" nowrap><a href="visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>'; }
		if ($_SESSION['rollevis']['spiller_id']) {
			if (!$spiller) {
				echo '<td id="spiller_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>';
			} else {
				echo '<td id="spiller_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$spiller['fornavn'].' '.$spiller['etternavn'].'" nowrap><a href="visperson.php?person_id='.$spiller['person_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a></td>';
			}
		}
		if ($_SESSION['rollevis']['arrangor_id']) {
			if (!$arrangor) {
				echo '<td id="arrangor_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>';
			} else {
				echo '<td id="arrangor_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'" nowrap><a href="visperson.php?person_id='.$arrangor['person_id'].'">'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</a></td>';
			}
		}
		if (($_SESSION['rollevis']['grupper']) && ($spill_id != 0)) {
			$grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id']);
			if (!$grupper) {
				echo '<td id="grupper_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$LANG['MISC']['none'].'"nowrap>'.$LANG['MISC']['none'].'</td>';
			} else {
				echo '<td id="grupper_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'"';
				foreach ($grupper as $gruppe) {
					$elm_title .= '$gruppe[navn], ';
					$gruppenavn .= '<a href="visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a>, ';
				}
				echo ' title="'.substr(trim($elm_title), 0, -1).'">'.substr(trim($gruppenavn), 0, -1);
				unset ($gruppenavn, $elm_title);
				echo '</td>';
			}
		}
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
								if (is_array($values)) {
									foreach ($values as $thisval) {
										$show .= $thisval.', ';
									}
								} else {
									$show .= $values;
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
			echo '<td id="spill_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$spillinfo['navn'].'" nowrap><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>';
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
		';
		if (!$rolle['status']) {
			echo '
				<td class="nospace"><button type="button" tabindex="'.$tabindex++.'" onClick="javascript:window.location=\'editrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\'">'.$LANG['MISC']['edit'].'</button></td>
			';
		} else {
			echo '
				<td class="nospace">&nbsp;</td>
			';
		}
		echo '
				<td class="nospace"><button type="button" tabindex="'.$tabindex++.'" onClick="javascript:window.location=\'./filvedlegg.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vedlagt=rolle\';">'.$LANG['MISC']['attachments'].'</button></td>
				<td class="nospace"><button type="button" tabindex="'.$tabindex++.'" class="red" onClick="javascript:return confirmDelete(\''.addslashes($rolle['navn'].' ('.$spillnavn.')').'\', \'./roller.php?slett_rolle='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
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
