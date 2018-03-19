<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              arrangorer.php                             #
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

if ($_GET['slett_arrangor']) {
	if (is_modifiable($_POST['slett_arrangor']) && is_koordinator()) {
		slett_arrangor();
		$_SESSION['message'] = $LANG['MESSAGE']['organizer_deleted'];
	}
	header('Location: ./arrangorer.php');
	exit();
}

include('header.php');

echo '<h2 align="center">'.$LANG['MISC']['organizers'].'</h2>';

$arrangorer = get_arrangorer();
$numarrangorer = count($arrangorer);
$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="center"><button onClick="javascript:window.location=\'./sendmail.php?sendto=arrangorer\';">'.$LANG['MISC']['mail_all'].'</button></td>
			<td><button onClick="javascript:window.location=\'./utskrifter.php?print=arrangorer&spill_id='.$spill_id.'\';">'.$LANG['MISC']['printouts'].'</button></td>
		<td><button onClick="javascript:window.location=\'editperson.php?nyperson=yes&amp;type=arrangor\';">'.$LANG['MISC']['create_organizer'].'</button></td>
	</tr>
</table>
';
if ($numarrangorer == 0) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_organizers'].'</h4>
		'.$buttons.'
	';
} else {
	$fieldnames_person = get_fields($table_prefix.'personer');
	$fieldnames_bruker = get_fields($table_prefix.'brukere');
	$fieldnames = array_merge($fieldnames_person, $fieldnames_bruker);
	$config['fields_not_in_person_lists'][] = 'bruker_id';
	$config['fields_not_in_person_lists'][] = 'passord';
	$config['fields_not_in_person_lists'][] = 'secret';
	$config['fields_not_in_person_lists'][] = 'nowlog';
	$config['fields_not_in_person_lists'][] = 'fingerprint';
	$tabindex = 1;
	foreach ($fieldnames as $key=>$fieldname) {
		if (!in_array($fieldname, $config['fields_not_in_person_lists'])) {
			$field[$key] = $fieldname;
		}
	}
	echo '
		<h4 align="center">'.$numarrangorer.' '.$LANG['MISC']['organizer_s'].'</h4>
		'.$buttons.'
		<br>
	';
	if (!$_REQUEST['utskrift']) {
		echo '
		<form method="post" action="arrangorer.php" name="nyvisningform">
		<input type="hidden" name="nyarrangorvis" value="yes">
		<input type="hidden" name="arrangororder" value="'.$arrangororder.'">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		';
		foreach ($field as $fieldname) {
			switch ($fieldname) {
				case 'fodt':
					echo '
						<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="arrangorvis['.$fieldname.']"'; if ($_SESSION['arrangorvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['birthdate'].'</td>
					';
					break;
				case 'lastlog':
					echo '
						<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="arrangorvis['.$fieldname.']"'; if ($_SESSION['arrangorvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['last_logon'].'</td>
					';
					break;
				case 'locked':
					echo '
						<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="arrangorvis['.$fieldname.']"'; if ($_SESSION['arrangorvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['account_status'].'</td>
					';
					break;
				case 'level':
					echo '
						<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="arrangorvis['.$fieldname.']"'; if ($_SESSION['arrangorvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['userlevel'].'</td>
					';
					break;
				default:
					echo '<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="arrangorvis['.$fieldname.']"'; if ($_SESSION['arrangorvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['DBFIELD'][$fieldname].'</td>';
			}
			check_for_new_row($count, 5);
		}
		echo '
		<tr><td colspan="5" align="center">
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
				<td colspan="'.count($_SESSION['arrangorvis']).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
	';
	foreach ($field as $fieldname) {
		if ($_SESSION['arrangorvis'][$fieldname]) {
			switch($fieldname) {
				case 'fodt':
					$sorting = get_sorting('./arrangorer.php', $fieldname, 'arrangororder');
					echo '
						<td nowrap>'.$LANG['MISC']['birthdate'].' '.$sorting.'</td>
					';
					break;
				case 'alder':
					$sorting = get_sorting('./arrangorer.php', 'fodt', 'arrangororder');
					echo '
						<td nowrap>'.$LANG['MISC']['age'].' '.$sorting.'</td>
					';
					break;
				case 'lastlog':
					$sorting = get_sorting('./arrangorer.php', $fieldname, 'arrangororder');
					echo '
						<td align="center" nowrap>'.$LANG['MISC']['last_logon'].' '.$sorting.'</td>
					';
					break;
				case 'locked':
					$sorting = get_sorting('./arrangorer.php', $fieldname, 'arrangororder');
					echo '
						<td nowrap>'.$LANG['MISC']['account_status'].' '.$sorting.'</td>
					';
					break;
				case 'level':
					$sorting = get_sorting('./arrangorer.php', $fieldname, 'arrangororder');
					echo '
						<td nowrap>'.$LANG['MISC']['userlevel'].' '.$sorting.'</td>
					';
					break;
				default:
					$sorting = get_sorting('./arrangorer.php', $fieldname, 'arrangororder');
					echo '
						<td nowrap>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>
					';
			}
		}
	}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
	';
	foreach ($field as $fieldname) {
		if ($_SESSION['arrangorvis'][$fieldname]) {
			echo '
				<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['DBFIELD'][$fieldname]).'" type="text" id="'.$fieldname.'_filter" title="'.$fieldname.'_filter" onkeyup="javascript:filter_list(this.value, \''.$fieldname.'\');"></td>
			';
		}
	}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($arrangorer as $arrangor_id=>$arrangor) {
		$dato = explode("-", $arrangor['fodt']);
		$fodt = explode("-", $arrangor['fodt']);
		$then = $fodt[0].$fodt[1].$fodt[2];
		$now = date('Ymd');
		$tmpalder = $now-$then;
		if (strlen($tmpalder) == 4) {
			$arrangor['alder'] = '0 '.$LANG['MISC']['years_old'];
 		} elseif (strlen($tmpalder) == 5) {
			$arrangor['alder'] = substr($tmpalder,0,1).' '.$LANG['MISC']['years_old'];
		} else {
			$arrangor['alder'] = substr($tmpalder,0,2).' '.$LANG['MISC']['years_old'];
		}
		unset($dato,$fodt,$then,$now,$tmpalder);
		$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
		echo '<tr>';
		foreach ($arrangor as $fieldname => $value) {
			if (in_array($fieldname, $field)) {
				if ($_SESSION['arrangorvis'][$fieldname]) { 
					switch($fieldname) {
						case 'email':
							if ($value) {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$value.'"><a href="mailto:'.$value.'">'.$value.'</a></td>'; 
							} else {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'fornavn':
						case 'etternavn':
							echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$value.'" nowrap><a href="./visperson.php?person_id='.$arrangor['person_id'].'">'.$value.'</a></td>'; 
							break;
						case 'telefon':
						case 'mobil':
							if ($value) {
								echo '<td nowrap="nowrap" id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$value.'">'.$value.'</td>';
							} else {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
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
								<td id="'.$fieldname.'_p'.$arrangor['person_id'].'" title="'.$LANG['DBFIELD'][$value].'" nowrap><img src="'.$styleimages['symb_'.$fm].'"></td>
							';
							break;
						case 'mailpref':
							echo '
								<td id="'.$fieldname.'_p'.$arrangor['person_id'].'" title="'.$LANG['DBFIELD'][$value].'" nowrap>'.$LANG['DBFIELD'][$value].'</td>
							';
							break;
						case 'fodt':
							$dato = explode("-",$value);
							$fodt = abs($dato[2]).'. '.substr($mndliste[abs($dato[1])], 0, 3).' '.$dato[0];
							echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$fodt.'" nowrap>'.$fodt.'</td>'; 
							break;
						case 'bilde':
							if (!$value) {
								echo '
									<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$LANG['MISC']['none'].'" align="center" nowrap><a href="javascript:void(0);" onClick="javascript:openInfowindow(\'./mugshots.php?person_id='.$arrangor['person_id'].'\');">'.$LANG['MISC']['none'].'</a></td>
								';
							} else {
								echo '
									<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$value.'" align="center" nowrap><img src="'.$styleimages['icon_image'].'" onClick="javascript:return overlib(\'<div align=center><img src=images/personer/'.rawurlencode($value).'><br><br><button type=button onClick=javascript:openInfowindow(\\\'mugshots.php?person_id='.$arrangor['person_id'].'\\\');>'.$LANG['MISC']['mugshots'].'</button></div>\', WIDTH, 120, OFFSETX, 0, CAPTION, \''.$arrangor['fornavn'].' '.$arrangor['etternavn'].'\');"></td>
								';
							}
							break;
						case 'lastlog':
							if (!$arrangor['brukernavn']) {
								$lastlog = $LANG['MISC']['no_account'];
								$lastlog_value = $lastlog;
							} elseif ($value > time()) {
								$lastlog = '<span class="green">'.$LANG['MISC']['now'].'</span>';
								$lastlog_value = $LANG['MISC']['now'];
							} elseif (!$value) {
								$lastlog = '<span class="red">'.$LANG['MISC']['never'].'</span>';
								$lastlog_value = $LANG['MISC']['never'];
							} else {
								$lastlog = ucfirst(strftime($config['short_dateformat'].' (%H:%M)', $value));
								$lastlog_value = $lastlog;
							}
							echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$lastlog_value.'" align="center">'.$lastlog.'</td>';
							break;
						case 'locked':
							if (!$arrangor['brukernavn']) {
								$status = $LANG['MISC']['no_account'];
								$status_value = $status;
							} elseif ($value == 0) {
								$status = $LANG['MISC']['active'];
								$status_value = $status;
							} else {
								$status = '<span class="red">'.$LANG['MISC']['locked'].'</span>';
								$status_value = $LANG['MISC']['locked'];
							}
							echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$status_value.'">'.$status.'</td>';
							break;
						case 'level':
							if (!$arrangor['brukernavn']) {
								$level = $LANG['MISC']['no_account'];
							} else {
								$levels = array("5"=>$LANG['MISC']['organizer'], "10"=>$LANG['MISC']['coordinator'], "20"=>$LANG['MISC']['administrator']);
								$level = $levels[$value];
							}
							echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$level.'">'.$level.'</td>';
							break;
						case 'brukernavn':
							if (!$value) {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$LANG['MISC']['no_account'].'">'.$LANG['MISC']['no_account'].'</td>'; 
							} else {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$value.'">'.$value.'</td>'; 
							}
							break;
						default:
							if (!$value) {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>'; 
							} else {
								echo '<td id="'.$fieldname.'_a'.$arrangor['person_id'].'" title="'.$value.'" nowrap>'.$value.'</td>'; 
							}
					}
				}
			}
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nospace" nowrap><button onClick="javascript:window.location=\'./editperson.php?person_id='.$arrangor['person_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		';
		if (is_koordinator() && is_modifiable($arrangor['person_id']) && !is_last_admin($arrangor['person_id'])) {
			echo '
			<td class="nospace" nowrap="nowrap"><button class="red" onClick="return confirmAction(\''.$LANG['JSBOX']['confirm_delete_organizer'].'\',\'./arrangorer.php?slett_arrangor='.$arrangor['person_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
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
		'.$buttons.'
	';
}

include('footer.php');

?>
