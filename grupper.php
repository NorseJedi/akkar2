<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                grupper.php                              #
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

if ($_GET[slett_gruppe]) {
	slett_gruppe();
	$_SESSION[message] = $LANG['MESSAGE']['group_deleted'];
	header('Location: ./grupper.php?spill_id='.$_GET['spill_id']);
	exit();
}

if (!$spill_id) {
	echo $LANG['ERROR']['no_game_selected'];
	exit();
}

include('header.php');

if ($_REQUEST['rolle_id']) {
	print_gruppeinfo_small($_REQUEST['rolle_id'], $spill_id);
	exits();
}
$grupper = get_grupper($spill_id);
$fields = get_fields($table_prefix.'grupper');

echo '
	<h2 align="center">'.$LANG['MISC']['groups'].'</h2>
	<br>
';

if (!$grupper) {
	echo '<h4 align="center">'.$LANG['MISC']['no_groups'].'</h4>';
} else {
	echo '
		<table border="0" cellpadding="3" cellspacing="0" align="center" width="90%">
			<tr class="noprint">
				<td colspan="6" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr class="highlight">
	';
	foreach ($fields as $fieldname) {
		switch($fieldname) {
			case 'spill_id':
			case 'gruppe_id':
			case 'medlemsinfo':
				break;
			default:
				echo '<td style="white-space:nowrap">'.ucwords($fieldname).'</td>';
		}
	}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['name']).'" type="text" id="navn_filter" title="navn_filter" onkeyup="javascript:filter_list(this.value, \'navn\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['descritption']).'" type="text" id="beskrivelse_filter" title="beskrivelse_filter" onkeyup="javascript:filter_list(this.value, \'beskrivelse\');"></td>
		<td colspan="2">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($grupper as $gruppe_id=>$gruppe) {
		echo '<tr>';
		foreach ($gruppe as $key=>$value) {
			switch($key) {
				case 'spill_id':
				case 'gruppe_id':
				case 'medlemsinfo':
					break;
				case 'navn':
					echo '<td id="navn_g'.$gruppe['gruppe_id'].'s'.$gruppe['spill_id'].'" title="'.$value.'" style="white-space: nowrap" width="19%"><a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$value.'</a></td>';
					break;
				default:
					echo '<td id="beskrivelse_g'.$gruppe['gruppe_id'].'s'.$gruppe['spill_id'].'" title="'.$value.'" style="white-space: nowrap" width="80%">'.broken_text($value, 59).'</td>';
			}
		}
		echo '
			<td class="nospace" nowrap>'.info_icon($gruppe['navn'], $gruppe['beskrivelse']).'</td>
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nospace" nowrap><button type="button" onClick="javascript:window.location=\'./editgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
						<td class="nospace" nowrap><button type="button" onClick="javascript:window.location=\'./filvedlegg.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'&amp;vedlagt=gruppe\';">'.$LANG['MISC']['attachments'].'</button></td>
						<td class="nospace" nowrap><button type="button" class="red" onClick="javascript:confirmDelete(\''.addslashes($gruppe['navn']).'\', \'./grupper.php?slett_gruppe='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
					</tr>
				</table>
			</td>
		';
		echo '
			</tr>
		';
	}
	echo '
	</table>
	';
}
echo '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:window.location=\'./editgruppe.php?nygruppe=yes&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['create_group'].'</button></td>
	</tr>
</table>
';

include('footer.php');
?>
