<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                spillere.php                             #
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

if ($_GET['slett_spiller']) {
	slett_spiller();
	$_SESSION[message] = $LANG['MESSAGE']['player_deleted'];
	header('Location: ./spillere.php');
	exit();
}

include('header.php');
echo '<h2 align="center">'.$LANG['MISC']['players'].'</h2>';
if ($spillere = get_spillere()) {
	$mail_liste = $config['arrgruppemail'].',';
	foreach ($spillere as $spiller) {
		if ($spiller['email']) {
			$mail_liste .= $spiller['email'].',';
		}
	}
	$mail_liste = substr(trim($mail_liste), 0, -1);

	$buttons = '
	<table align="center">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><button onClick="javascript:window.location=\'./sendmail.php?sendto=spillere\';">'.$LANG['MISC']['mail_all'].'</button></td>
			<td><button onClick="javascript:window.location=\'./utskrifter.php?print=spillere&spill_id='.$spill_id.'\';">'.$LANG['MISC']['printouts'].'</button></td>
			<td><button onClick="javascript:window.location=\'editperson.php?nyperson=yes\';">'.$LANG['MISC']['create_player'].'</button></td>
		</tr>
	</table>
	';
}
if (!$spillere) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_players'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button onClick="javascript:window.location=\'editperson.php?nyperson=yes\';">'.$LANG['MISC']['create_player'].'</button></td>
			</tr>
		</table>
	';
} else {

	$numspillere = count($spillere);
	$fieldnames = get_fields($table_prefix.'personer');
	$tabindex = 1;
	foreach ($fieldnames as $key=>$fieldname) {
		if (!in_array($fieldname, $config['fields_not_in_person_lists'])) {
			$field[$key] = $fieldname;
		}
	}
	echo '
		<h4 align="center">'.$numspillere.' '.$LANG['MISC']['player_s'].'</h4>
		'.$buttons.'
		<br>
	';
	if (!$_REQUEST[utskrift]) {
		echo '
		<form method="post" action="spillere.php" name="nyvisningform">
		<input type="hidden" name="nypersonvis" value="yes">
		<input type="hidden" name="spillerorder" value="'.$spillerorder.'">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		';
		foreach ($field as $fieldname) {
			if ($fieldname == 'fodt') {
				echo '
					<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="personvis['.$fieldname.']"'; if ($_SESSION['personvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['birthdate'].'</td>
				';
			} else {
				echo '<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="personvis['.$fieldname.']"'; if ($_SESSION['personvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['DBFIELD'][$fieldname].'</td>';
			}
			check_for_new_row($j, 5);
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
				<td colspan="'.count($_SESSION['personvis']).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
	';
	foreach ($field as $fieldname) {
		if ($_SESSION['personvis'][$fieldname]) {
			switch($fieldname) {
				case 'fodt':
					$sorting = get_sorting('./spillere.php?spill_id='.$spill_id, $fieldname, 'personorder');
					echo '
						<td nowrap>'.$LANG['MISC']['birthdate'].' '.$sorting.'</td>
					';
					break;
				case 'alder':
					$sorting = get_sorting('./spillere.php?spill_id='.$spill_id, 'fodt', 'personorder');
					echo '
						<td nowrap>'.$LANG['MISC']['age'].' '.$sorting.'</td>
					';
					break;
				case 'bilde':
					$sorting = get_sorting('./spillere.php?spill_id='.$spill_id, 'fodt', 'personorder');
					echo '
						<td nowrap align="center">'.$LANG['MISC']['picture'].' '.$sorting.'</td>
					';
					break;
				default:
					$sorting = get_sorting('./spillere.php?spill_id='.$spill_id, $fieldname, 'personorder');
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
		if ($_SESSION['personvis'][$fieldname]) {
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
	foreach ($spillere as $spiller_id=>$spiller) {
		$fodt = explode('-', $spiller['fodt']);
		$then = $fodt[0].$fodt[1].$fodt[2];
		$now = date('Ymd');
		$tmpalder = $now-$then;
		if (strlen($tmpalder) == 4) {
			$spiller['alder'] = '0 '.$LANG['MISC']['years_old'];
 		} elseif (strlen($tmpalder) == 5) {
			$spiller['alder'] = substr($tmpalder,0,1).' '.$LANG['MISC']['years_old'];
		} else {
			$spiller['alder'] = substr($tmpalder,0,2).' '.$LANG['MISC']['years_old'];
		}
		unset($dato,$fodt,$then,$now,$tmpalder);
		$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
		echo '<tr>';
		foreach ($spiller as $fieldname => $value) {
			if (in_array($fieldname, $field)) {
				if ($_SESSION['personvis'][$fieldname]) { 
					switch($fieldname) {
						case 'email':
							if ($value) {
								echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$value.'"><a href="mailto:'.$value.'">'.$value.'</a></td>'; 
							} else {
								echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'fornavn':
						case 'etternavn':
							echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$value.'" nowrap><a href="./visperson.php?person_id='.$spiller['person_id'].'">'.$value.'</a></td>'; 
							break;
						case 'telefon':
						case 'mobil':
							if ($value) {
								echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$value.'">'.$value.'</td>';
							} else {
								echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'fodt':
							$dato = explode('-',$value);
							$fodt = abs($dato[2]).'. '.substr($mndliste[abs($dato[1])], 0, 3).' '.$dato[0];
							echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$fodt.'" nowrap>'.$fodt.'</td>'; 
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
						case 'mailpref':
							echo '
								<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$LANG['DBFIELD'][$value].'" nowrap>'.$LANG['DBFIELD'][$value].'</td>
							';
							break;
						case 'bilde':
							if (!$value) {
								echo '
									<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$LANG['MISC']['none'].'" align="center" nowrap><a href="javascript:void(0);" onClick="javascript:openInfowindow(\'./mugshots.php?person_id='.$spiller['person_id'].'\');">'.$LANG['MISC']['none'].'</a></td>
								';
							} else {
								echo '
									<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$value.'" align="center" nowrap><img src="'.$styleimages['icon_image'].'" onClick="javascript:return overlib(\'<div align=center><img src=images/personer/'.$value.'\><br><br><button type=button onClick=javascript:openInfowindow(\\\'mugshots.php?person_id='.$spiller['person_id'].'\\\');>'.$LANG['MISC']['mugshots'].'</button></div>\', WIDTH, 120, OFFSETX, 0, CAPTION, \''.$spiller['fornavn'].' '.$spiller['etternavn'].'\');"></td>
								';
							}
							break;
						default:
							echo '<td id="'.$fieldname.'_p'.$spiller['person_id'].'" title="'.$value.'">'.$value.'</td>'; 
					}
				}
			}
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
			<td class="nospace" nowrap><button onClick="javascript:window.location=\'./editperson.php?person_id='.$spiller['person_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		';
		if (is_koordinator()) {
			echo '
			<td class="nospace" nowrap><button class="red" onClick="confirmDelete(\''.addslashes($spiller['fornavn'].' '.$spiller['etternavn']).'\', \'./spillere.php?slett_spiller='.$spiller['person_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
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
