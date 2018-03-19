<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               kontakter.php                             #
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

if ($_GET['slett_kontakt']) {
	slett_kontakt();
	$_SESSION['message'] = $LANG['MESSAGE']['contact_deleted'];
	header('Location: ./kontakter.php');
	exit();
}

include('header.php');
echo '<h2 align="center">'.$LANG['MISC']['contacts'].'</h2>';
$kontakter = get_kontakter();
$buttons = '
	<table align="center">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><button onClick="javascript:window.location=\'./sendmail.php?sendto=kontakter\';">'.$LANG['MISC']['mail_all'].'</button></td>
			<td><button onClick="javascript:window.location=\'./utskrifter.php?print=kontakter&spill_id='.$spill_id.'\';">'.$LANG['MISC']['printouts'].'</button></td>
			<td><button onClick="javascript:window.location=\'editkontakt.php?nykontakt=yes\';">'.$LANG['MISC']['create_contact'].'</button></td>
		</tr>
	</table>
	';
if (!$kontakter) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_contacts'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button onClick="javascript:window.location=\'editkontakt.php?nykontakt=yes\';">'.$LANG['MISC']['create_contact'].'</button></td>
			</tr>
		</table>
	';
} else {
	$numkontakter = count($kontakter);
	$fieldnames = get_fields($table_prefix.'kontakter');
 	$tabindex = 1;
 	foreach ($fieldnames as $key=>$fieldname) {
		if (!in_array($fieldname, $config['fields_not_in_contacts_list'])) {
			$field[$key] = $fieldname;
		}
	}
	echo '
		<h4 align="center">'.$numkontakter.' '.$LANG['MISC']['contact_s'].'</h4>
		'.$buttons.'
		<br>
	';
	if (!$_REQUEST['utskrift']) {
		echo '
		<form method="post" action="kontakter.php" name="nyvisningform">
		<input type="hidden" name="nykontaktvis" value="yes">
		<input type="hidden" name="kontaktorder" value="'.$kontaktorder.'">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		';
		foreach ($field as $fieldname) {
			echo '<td align="left"><input type="checkbox" tabindex="'.$tabindex++.'" name="kontaktvis['.$fieldname.']"'; if ($_SESSION['kontaktvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['DBFIELD'][$fieldname].'</td>';
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
				<td colspan="'.count($_SESSION['kontaktvis']).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
	';
	foreach ($field as $fieldname) {
		if ($_SESSION['kontaktvis'][$fieldname]) {
			$sorting = get_sorting('./kontakter.php?spill_id='.$spill_id, $fieldname, 'kontaktorder');
			echo '
				<td nowrap>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>
			';
		}
	}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
	';
	foreach ($field as $fieldname) {
		if ($_SESSION['kontaktvis'][$fieldname]) {
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
	foreach ($kontakter as $kontakt_id=>$kontakt) {
		echo '<tr>';
		foreach ($kontakt as $fieldname => $value) {
			if (in_array($fieldname, $field)) {
				if ($_SESSION['kontaktvis'][$fieldname]) { 
					switch($fieldname) {
						case 'email':
							if ($value) {
								echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$value.'"><a href="mailto:'.$value.'">'.$value.'</a></td>'; 
							} else {
								echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'navn':
							echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$value.'" nowrap><a href="./viskontakt.php?kontakt_id='.$kontakt['kontakt_id'].'">'.$value.'</a></td>'; 
							break;
						case 'telefon':
						case 'mobil':
						case 'fax':
							if ($value) {
								echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$value.'">'.$value.'</td>';
							} else {
								echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'webside':
							if ($value) {
								echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$value.'"><a href="'.$value.'" target="_blank">'.$value.'</a></td>';
							} else {
								echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$LANG['MISC']['none'].'">'.$LANG['MISC']['none'].'</td>';
							}
							break;
						case 'bilde':
							if (!$value) {
								echo '
									<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$LANG['MISC']['none'].'" align="center" nowrap="nowrap">'.$LANG['MISC']['none'].'</td>
								';
							} else {
								echo '
									<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$value.'" align="center" nowrap="nowrap"><img src="'.$styleimages['icon_image'].'" onClick="javascript:return overlib(\'<div align=center><img src=images/personer/'.$value.'\><br></div>\', WIDTH, 120, OFFSETX, 0, CAPTION, \''.$kontakt['navn'].'\');"></td>
								';
							}
							break;
						default:
							echo '<td id="'.$fieldname.'_c'.$kontakt['kontakt_id'].'" title="'.$value.'" nowrap="nowrap">'.$value.'</td>'; 
					}
				}
			}
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
			<td class="nospace" nowrap><button onClick="javascript:window.location=\'./editkontakt.php?kontakt_id='.$kontakt['kontakt_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		';
		if (is_koordinator()) {
			echo '
			<td class="nospace" nowrap><button class="red" onClick="confirmDelete(\''.addslashes($kontakt['navn']).' ('.$LANG['MISC']['contact'].')\',\'./kontakter.php?slett_kontakt='.$kontakt['kontakt_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
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

include('footer.php');

?>
