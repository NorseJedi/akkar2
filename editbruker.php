<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              editbruker.php                             #
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

$person = get_person($_GET['person_id']);
$brukere = get_brukere();
switch ($_GET['action']) {
	case 'opprett_bruker':
		$suggest = explode('@', $person['email']);
		$suggest = $suggest[0];
		$levels = array('1'=>$LANG['MISC']['player'], '5'=>$LANG['MISC']['organizer'], '10'=>$LANG['MISC']['coordinator'], '20'=>$LANG['MISC']['administrator']);
		echo '
			<script language="javascript" type="text/javascript">
				var brukere = new Array(';
				$foo = 0;
				foreach ($brukere as $bruker) {
					if ($foo++) echo ',';
					echo '\''.$bruker['brukernavn'].'\'';
				} 
				
				echo ');
				function validate() {
					for (i=0; i<brukere.length; i++) {
						if (document.editbrukerform.nybrukernavn.value == brukere[i]) {
							window.alert(\''.$LANG['JSBOX']['username_taken'].'\');
							document.editbrukerform.nybrukernavn.focus();
							return false;
						}

					}
					if (document.editbrukerform.nybrukernavn.value == \'\') {
						window.alert(\''.$LANG['JSBOX']['enter_username'].'\');
						document.editbrukerform.nybrukernavn.focus();
						return false;
					}
					if (document.editbrukerform.nypassord.value == \'\') {
						window.alert(\''.$LANG['JSBOX']['enter_password'].'\');
						document.editbrukerform.nypassord.focus();
						return false;
					}
					if (document.editbrukerform.nypassord.value != document.editbrukerform.nyconfirm.value) {
						window.alert(\''.$LANG['JSBOX']['password_mismatch'].'\');
						document.editbrukerform.nypassord.focus();
						return false;
					}
					if (document.editbrukerform.nylevel.value == \'\') {
						window.alert(\''.$LANG['JSBOX']['select_userlevel'].'\');
						document.editbrukerform.nylevel.focus();
						return false;
					}
					return true;
				}
			
			</script>
			<h2 align="center">'.$LANG['MISC']['create_account'].'</h2>
			<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
			<br>
			<form name="editbrukerform" action="visperson.php" method="post">
			<input type="hidden" name="person_id" value="'.$person['person_id'].'">
			<input type="hidden" name="opprett_bruker" value="yes">
			<table align="center" cellspacing="0">
				<tr>
					<td class="highlight">'.$LANG['MISC']['username'].'</td>
					<td><input type="text" name="nybrukernavn" value="'.$suggest.'"></td>
				</tr>
				<tr>
					<td class="highlight">'.$LANG['MISC']['password'].'</td>
					<td><input type="password" name="nypassord"></td>
				</tr>
				<tr>
					<td class="highlight">'.$LANG['MISC']['confirm_password'].'</td>
					<td><input type="password" name="nyconfirm"></td>
				</tr>
		';
		if (!$person['type'] == 'arrangor') {
			echo '
				<tr>
					<td class="highlight">'.$LANG['MISC']['userlevel'].'</td>
					<td><select name="nylevel">
						<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
		';
		foreach ($levels as $level=>$rank) {
			echo '<option value="'.$level.'">'.$rank.'</option>';
		}
		echo '
						</select>
					</td>
				</tr>
			';
		} else {
			echo '
				<tr>
					<td class="highlight">'.$LANG['MISC']['userlevel'].'</td>
					<td><input type="hidden" name="nylevel" value="1" />'.$LANG['MISC']['player'].'</td>
				</tr>
			';
		}
		echo '
				<tr>
					<td colspan="2">
						<table align="center" cellspacing="0">
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td class="nospace"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
								<td class="nospace"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
								<td class="nospace"><button type="submit" onClick="javascript:return validate();">'.$LANG['MISC']['save'].'</button></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</form>
		';
		break;
	case 'nytt_brukernavn':
		$suggest = explode('@', $person['email']);
		$suggest = $suggest[0];
		$bruker = get_bruker($person['person_id']);
		echo '
			<script language="javascript" type="text/javascript">
				function validate() {
					if (document.editbrukerform.nybrukernavn.value == \'\') {
						window.alert(\''.$LANG['JSBOX']['enter_username'].'\');
						document.editbrukerform.nybrukernavn.focus();
						return false;
					}
					return true;
				}
			
			</script>
			<h2 align="center">'.$LANG['MISC']['change_username'].'</h2>
			<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
			<br>
			<form name="editbrukerform" action="visperson.php" method="post">
			<input type="hidden" name="person_id" value="'.$person['person_id'].'">
			<input type="hidden" name="nytt_brukernavn" value="yes">
			<input type="hidden" name="whereiwas" value="'.$whereiwas.'">
			<table align="center" cellspacing="0">
				<tr>
					<td class="highlight" style="vertical-align: middle">'.$LANG['MISC']['current_username'].'</td>
					<td>'.$bruker['brukernavn'].'</td>
				</tr>
				<tr>
					<td class="highlight" style="vertical-align: middle">'.$LANG['MISC']['new_username'].'</td>
					<td><input type="text" name="nybrukernavn" value="'.$suggest.'"></td>
				</tr>
				<tr>
					<td colspan="2">
						<table align="center" cellspacing="0">
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td class="nospace"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
								<td class="nospace"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
								<td class="nospace"><button type="submit" onClick="javascript:return validate();">'.$LANG['MISC']['save'].'</button></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</form>
		';
		break;
	case 'nytt_passord':
		$bruker = get_bruker($person['person_id']);
		echo '
			<script language="javascript" type="text/javascript">
				function validate() {
					if (document.editbrukerform.nypassord.value == \'\') {
						window.alert(\''.$LANG['JSBOX']['enter_password'].'\');
						document.editbrukerform.nypassord.focus();
						return false;
					}
					if (document.editbrukerform.nypassord.value != document.editbrukerform.nyconfirm.value) {
						window.alert(\''.$LANG['JSBOX']['password_mismatch'].'\');
						document.editbrukerform.nypassord.focus();
						return false;
					}
					return true;
				}
			
			</script>
			<h2 align="center">'.$LANG['MISC']['change_password'].'</h2>
			<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
			<br>
			<form name="editbrukerform" action="visperson.php" method="post">
			<input type="hidden" name="person_id" value="'.$person['person_id'].'">
			<input type="hidden" name="nytt_passord" value="yes">
			<input type="hidden" name="whereiwas" value="'.$whereiwas.'">
			<table align="center" cellspacing="0">
				<tr>
					<td class="highlight" style="vertical-align: middle">'.$LANG['MISC']['username'].'</td>
					<td>'.$bruker['brukernavn'].'</td>
				</tr>
				<tr>
					<td class="highlight" style="vertical-align: middle">'.$LANG['MISC']['password'].'</td>
					<td><input type="password" name="nypassord"></td>
				</tr>
				<tr>
					<td class="highlight" style="vertical-align: middle">'.$LANG['MISC']['confirm_password'].'</td>
					<td><input type="password" name="nyconfirm"></td>
				</tr>
				<tr>
					<td colspan="2">
						<table align="center" cellspacing="0">
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td class="nospace"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
								<td class="nospace"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
								<td class="nospace"><button type="submit" onClick="javascript:return validate();">'.$LANG['MISC']['save'].'</button></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</form>
		';
		break;
	case 'nytt_level':
		$bruker = get_bruker($person['person_id']);
		$levels = array('5'=>$LANG['MISC']['organizer'], '10'=>$LANG['MISC']['coordinator'], '20'=>$LANG['MISC']['administrator']);
		echo '
			<script language="javascript" type="text/javascript">
				function validate() {
					if (document.editbrukerform.nylevel.value == \'\') {
						window.alert(\''.$LANG['JSBOX']['select_userlevel'].'\');
						document.editbrukerform.nylevel.focus();
						return false;
					}
					return true;
				}
			
			</script>
			<h2 align="center">'.$LANG['MISC']['change_userlevel'].'</h2>
			<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
			<br>
			<form name="editbrukerform" action="visperson.php" method="post">
			<input type="hidden" name="person_id" value="'.$person['person_id'].'">
			<input type="hidden" name="nytt_level" value="yes">
			<input type="hidden" name="whereiwas" value="'.$whereiwas.'">
			<table align="center" cellspacing="0">
				<tr>
					<td class="highlight">'.$LANG['MISC']['userlevel'].'</td>
					<td><select name="nylevel">
						<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
		';
		foreach ($levels as $level=>$rank) {
			echo '<option value="'.$level.'"'; if ($bruker['level'] == $level) { echo ' selected'; } echo '>'.$rank.'</option>';
		}
		echo '
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table align="center" cellspacing="0">
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td class="nospace"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
								<td class="nospace"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
								<td class="nospace"><button type="submit" onClick="javascript:return validate();">'.$LANG['MISC']['save'].'</button></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</form>
		';
		break;
}

include('footer.php');
?>