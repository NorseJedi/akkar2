<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             paameldinger.php                            #
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
if ($_GET['slett_paamelding']) {
	if (is_koordinator()) {
		slett_paamelding();
		$_SESSION['message'] = $LANG['MESSAGE']['registration_deleted'];
	}
	header('Location: ./paameldinger.php?spill_id='.$spill_id);
	exit();
}

include('header.php');

echo '<h2 align="center">'.$LANG['MISC']['registrations'].'</h2>';
$paameldinger = get_paameldte($spill_id);
$mail_liste = $config['arrgruppemail'].',';
if (!$paameldinger) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_registrations'].'</h4>
		<br>
	';
} else {
foreach ($paameldinger as $paamelding) {
	if ($paamelding['email']) {
		$mail_liste .= $paamelding['email'].',';
	}
}
$mail_liste = substr($mail_liste, 0, -1);
$numpaameldinger = count($paameldinger);
$mal = get_paameldingsmal($spill_id);
$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="center"><button onClick="javascript:window.location=\'./sendmail.php?sendto=paameldte&spill_id='.$spill_id.'\';">'.$LANG['MISC']['mail_all'].'</button></td>
		<td><button onClick="javascript:window.location=\'./utskrifter.php?print=roller&spill_id='.$spill_id.'\';">'.$LANG['MISC']['printouts'].'</button></td>
		<td><button onClick="javascript:window.location=\'#nypaamelding\';">'.$LANG['MISC']['create_registration'].'</button></td>
	</tr>
</table>
';

if ($numpaameldinger == 0) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_registrations'].'</h4>
		'.$buttons;
} else {
	$fieldnames = get_fields($table_prefix.'paameldinger');
	$fieldnames2 = get_fields($table_prefix.'personer');
	$field = array();
	$tabindex = 1;
	foreach ($mal as $fieldname=>$fieldinfo) {
		if ((in_array($fieldname, $fieldnames)) && (!in_array($fieldinfo['type'], $config['types_not_in_lists']))) {
			$field[$fieldname] = $mal[$fieldname]['fieldtitle'];
		}
	}
	$allstaticfields = array_merge($fieldnames2, $fieldnames);
	echo '
		<h4 align="center">'.$numpaameldinger.' '.$LANG['MISC']['registration_s'].'</h4>
		'.$buttons.'
		<br>
	';
	if ((!$_REQUEST['utskrift']) && ($spill_id != 0)) {
		echo '
		<form method="post" action="paameldinger.php" name="nyvisningform">
		<input type="hidden" name="nypaameldingvis" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="paameldingorder" value="'.$paameldingorder.'">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		';
		foreach ($allstaticfields as $fieldname) {
			if ((!strstr($fieldname, 'field')) && (!in_array($fieldname, $config['fields_not_in_person_lists']))) {
				switch ($fieldname) {
					case 'type':
					case 'person_id':
					case 'spill_id':
						break;
					case 'rolle_id':
						echo '
							<td align="left" nowrap><input type="checkbox" tabindex="'.$tabindex++.'" name="paameldingvis['.$fieldname.']"'; if ($_SESSION['paameldingvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['character'].'</td>
						';
						check_for_new_row($j, 5);
						break;
					case 'fodt':
						echo '
							<td align="left" nowrap><input type="checkbox" tabindex="'.$tabindex++.'" name="paameldingvis['.$fieldname.']"'; if ($_SESSION['paameldingvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['birthdate'].'</td>
						';
						check_for_new_row($j, 5);
						break;
					default:
						echo '
							<td align="left" nowrap><input type="checkbox" tabindex="'.$tabindex++.'" name="paameldingvis['.$fieldname.']"'; if ($_SESSION['paameldingvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['DBFIELD'][$fieldname].'</td>
						';
						check_for_new_row($j, 5);
				}
			}
		}
		foreach ($field as $fieldname => $fieldtitle) {
			echo '
				<td align="left" nowrap><input type="checkbox" tabindex="'.$tabindex++.'" name="paameldingvis['.$fieldname.']"'; if ($_SESSION['paameldingvis'][$fieldname]) { echo ' checked'; } echo '>'.$fieldtitle.'</td>
			';
			check_for_new_row($j, 5);
		}
		echo '
			<td align="left" nowrap><input type="checkbox" tabindex="'.$tabindex++.'" name="paameldingvis[rolle]"'; if ($_SESSION['paameldingvis']['rolle']) { echo ' checked'; } echo '>'.$LANG['MISC']['character'].'</td>
		';
		check_for_new_row($j, 5);
		echo '
		</tr>
		<tr><td colspan="6" align="center">
			<button tabindex="'.$tabindex++.'" onClick="javascript:document.nyvisningform.submit();">'.$LANG['MISC']['change_view'].'</button>
		</td>
		</tr>
		</table>
		</form>
		';
	}
	echo '
		<table border="0" cellpadding="3" cellspacing="0" align="center" width="90%">
			<tr class="noprint">
				<td colspan="'.count($_SESSION['personvis']).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
		';
		foreach ($allstaticfields as $fieldname) {
			if ((!strstr($fieldname, 'field')) && (!in_array($fieldname, $config['fields_not_in_person_lists']))) {
				if ($_SESSION['paameldingvis'][$fieldname]) {
					$sorting = get_sorting('./paameldinger.php?spill_id='.$spill_id, $fieldname, 'paameldingorder');
					switch ($fieldname) {
						case 'annet':
							break;
						case 'fodt':
							echo '
								<td nowrap>'.$LANG['MISC']['birthdate'].' '.$sorting.'</td>
							';
							break;
						case 'paameldt':
							echo '
								<td nowrap>'.$LANG['MISC']['registration_time'].' '.$sorting.'</td>
							';
							break;
						default:
							echo '
								<td nowrap>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>
							';
					}
				}
			}
		}
		foreach ($field as $fieldname => $fieldtitle) {
			if ($_SESSION['paameldingvis'][$fieldname]) {
				$sorting = get_sorting('./paameldinger.php?spill_id='.$spill_id, $fieldname, 'paameldingorder');
				echo '
					<td nowrap>'.$fieldtitle.' '.$sorting.'</td>
				';
			}
		}
		if ($_SESSION['paameldingvis']['annet']) {
			echo '
				<td nowrap>'.$LANG['DBFIELD']['annet'].' '.$sorting.'</td>
			';
		}
		if ($_SESSION['paameldingvis']['rolle']) {
			echo '
				<td nowrap>'.$LANG['MISC']['character'].'</td>
			';
		}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
	';
	foreach ($allstaticfields as $fieldname) {
		if ((!strstr($fieldname, 'field')) && (!in_array($fieldname, $config['fields_not_in_person_lists']))) {
			if (trim($fieldname) != 'annet') {
				if ($_SESSION['paameldingvis'][$fieldname]) {
					echo '
						<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['DBFIELD'][$fieldname]).'" type="text" id="'.$fieldname.'_filter" title="'.$fieldname.'_filter" onkeyup="javascript:filter_list(this.value, \''.$fieldname.'\');"></td>
					';
				}
			}
		}
		}
	foreach ($field as $fieldname=>$fieldtitle) {
		if ($_SESSION['paameldingvis'][$fieldname]) {
			echo '
				<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['DBFIELD'][$fieldname]).'" type="text" id="'.$fieldname.'_filter" title="'.$fieldname.'_filter" onkeyup="javascript:filter_list(this.value, \''.$fieldname.'\');"></td>
			';
		}
	}
	if ($_SESSION['paameldingvis']['annet']) {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="10" type="text" id="annet_filter" title="annet_filter" onkeyup="javascript:filter_list(this.value, \'annet\');"></td>
		';
	}
	if ($_SESSION['paameldingvis']['rolle']) {
		echo '
			<td><input class="filterbox" tabindex="'.$tabindex++.'" size="10" type="text" id="rolle_filter" title="rolle_filter" onkeyup="javascript:filter_list(this.value, \'rolle\');"></td>
		';
	}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($paameldinger as $person_id=>$paamelding) {
		echo '<tr>';
		foreach ($paamelding as $fieldname => $value) {
			if (!in_array($fieldname, $config['fields_not_in_person_lists'])) {
				if ($_SESSION['paameldingvis'][$fieldname]) {
					$malinfo = $mal[$fieldname];
					if (is_array($malinfo) && !in_array($malinfo['type'], $config['types_not_in_lists'])) {
						$extras = explode(';', $malinfo['extra']);
						switch($malinfo['type']) {
							case 'calc':
								$extras = explode(';', $mal[$fieldname]['extra']);
								$calc = get_calc_formula($paamelding[$mal[$extras[0]]['fieldname']], $extras[1]);
								@eval('\$calcresult = '.$calc.';');
								echo '<td nowrap id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$calcresult.'" align="center">'.$calcresult.'</td>';
								break;
							case 'check':
								if ($value != 0) {
									echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$extras[0].'" nowrap>'.$extras[0].'</td>';
								} else {
									echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$extras[1].'" nowrap>'.$extras[1].'</td>';
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
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.substr(trim($show), 0, -1).'" nowrap>'.substr(trim($show), 0, -1).'</td>';
								unset($show, $thisval, $values);
								break;
							default:
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'" nowrap>'.$value.'</td>';
						}
					} else {
						switch ($fieldname) {
						case 'fornavn':
						case 'etternavn':
							echo '
								<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'" nowrap><a href="./vispaamelding.php?person_id='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'">'.$value.'</a></td>
							';
							break;
						case 'paameldt':
							$paameldt = ucfirst(strftime($config['medium_dateformat'], $value));
							echo '
								<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$paameldt.'" nowrap>'.$paameldt.'</td>
							';
							break;
						case 'fodt':
							$dato = explode('-', $value);
							$fodt = abs($dato[2]).'. '.substr($mndliste[abs($dato[1])], 0, 3).' '.$dato[0];
							echo '
								<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$fodt.'" nowrap>'.$fodt.'</td>
							';
							break;
						case 'alder':
							$fodt = explode('-', $paamelding['fodt']);
							$then = $fodt[0].$fodt[1].$fodt[2];
							$now = date('Ymd');
							$tmpalder = $now-$then;
							if (strlen($tmpalder) == 4) {
								$value = '0 '.$LANG['MISC']['years_old'];
					 		} elseif (strlen($tmpalder) == 5) {
								$value = substr($tmpalder,0,1).' '.$LANG['MISC']['years_old'];
							} else {
								$value = substr($tmpalder,0,2).' '.$LANG['MISC']['years_old'];
							}
							unset($dato,$fodt,$then,$now,$tmpalder);
							echo '
								<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'" nowrap>'.$value.'</td>
							';
							break;
						case 'kjonn':
							if ($value == 'han') {
									$fm = 'male';
							} elseif ($value == 'hun') {
									$fm = 'female';
							} else {
									$fm = 'unknown_gender';
									$LANG['DBFIELD'][$value] = $LANG['MISC']['unknown'];
							}
							echo '
								<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$LANG['DBFIELD'][$value].'" nowrap><img src="'.$styleimages['symb_'.$fm].'"></td>
							';
							break;
						case 'mobil':
						case 'telefon':
							if ($value) {
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'" nowrap>'.$value.'</td>';
							} else {
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'email':
							if ($value) {
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'"><a href="mailto:'.$value.'">'.$value.'</a></td>'; 
							} else {
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'mailpref':
							echo '
								<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$LANG['DBFIELD'][$value].'" nowrap>'.$LANG['DBFIELD'][$value].'</td>
							';
							break;
						case 'betalt':
							if ($value) {
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$LANG['MISC']['yes'].'" nowrap>'.$LANG['MISC']['yes'].'</td>';
							} else {
								echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$LANG['MISC']['no'].'" nowrap>'.$LANG['MISC']['no'].'</td>';
							}
							break;
						case 'bilde':
							if (!$value) {
								echo '
									<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$LANG['MISC']['none'].'" align="center" nowrap><a href="javascript:void(0);" onClick="javascript:openInfowindow(\'./mugshots.php?person_id='.$paamelding['person_id'].'\');">'.$LANG['MISC']['none'].'</a></td>
								';
							} else {
								echo '
									<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'" align="center" nowrap><img src="'.$styleimages['icon_image'].'" onClick="javascript:return overlib(\'<div align=center><img src=images/personer/'.rawurlencode($value).'><br><br><button type=button onClick=javascript:openInfowindow(\\\'mugshots.php?person_id='.$paamelding['person_id'].'\\\');>'.$LANG['MISC']['mugshots'].'</button></div>\', WIDTH, 120, OFFSETX, 0, CAPTION, \''.$paamelding['fornavn'].' '.$paamelding['etternavn'].'\');"></td>
								';
							}
							break;
						default:
							echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'" title="'.$value.'" nowrap>'.$value.'</td>';
						}
					}
				}
			}
		}
		if ($_SESSION['paameldingvis']['rolle']) {
			$roller = get_spiller_roller($paamelding['person_id'], $paamelding['spill_id']);
			if (!$roller) {
				echo '<td id="rolle_p'.$paamelding['person_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>'; 
			} else {
				unset($result, $result_value);
				if (count($roller) > 1) {
					$roller_num = '('.count($roller).') ';	
				}
				foreach ($roller as $rolle) {
					$result .= '<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$spill_id.'">'.stripslashes($rolle['navn']).'</a>, ';
					$result_value .= $rolle['navn'].', ';
				}
				echo '<td id="rolle_p'.$paamelding['person_id'].'" title="'.trim($result_value).'">'.$roller_num.substr(trim($result), 0, -1).'</td>';
				unset($roller_num);
			}
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nospace"><button onClick="javascript:window.location=\'editpaamelding.php?person_id='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		';
		if (is_koordinator()) {
			echo '
						<td class="nospace"><button class="red" onClick="javascript:return confirmDelete(\''.addslashes($LANG['MISC']['this_registration']).'\', \'./paameldinger.php?slett_paamelding='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
			';
		}
		echo '
					</tr>
				</table>
			</td>
		</tr>';
	}
	echo '
		</table>
		'.$buttons;
}
}
echo '
	<br><br>
	<a name="nypaamelding"></a>
';
if ($spillere = get_spillere()) {
	echo '
		<form name="nypaamelding" action="./editpaamelding.php" method="post">
		<input type="hidden" name="nypaamelding" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<table align="center" cellspacing="0">
			<tr>
				<td class="highlight">'.$LANG['MISC']['player'].'</td>
				<td class="highlight">&nbsp;</td>
			</tr>
			<tr>
				<td><select name="person_id">
					<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
	';
	foreach ($spillere as $spiller) {
		if (!get_paamelding($spiller['person_id'], $spill_id)) {
			echo '
				<option value="'.$spiller['person_id'].'">'.$spiller['etternavn'].', '.$spiller['fornavn'].'</option>
			';
		}
	}
	echo '
				</select>
				</td>
				<td><button type="submit">'.$LANG['MISC']['register'].'</button>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2"><h4 class="table">- '.$LANG['MISC']['or'].' -</h4></td>
			</tr>
	';
} else {
	echo '
		<table align="center" cellspacing="0">
			<tr>
				<td align="center" style="padding-bottom: 3em;"><h4>'.$LANG['MISC']['no_players'].'</h4></td>
			</tr>
	';
}
echo '
		<tr>
			<td align="center" colspan="2">
				<button type="button" onClick="javascript:window.location=\'./editpaamelding.php?spill_id='.$spill_id.'&amp;nypaamelding=yes&amp;nyperson=yes\';">'.$LANG['MISC']['create_player'].' + '.$LANG['MISC']['registration'].'</button>
			</td>
		</tr>
	</table>
	</form>
	';
include('footer.php');

?>
