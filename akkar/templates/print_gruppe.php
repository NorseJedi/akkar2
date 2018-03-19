<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             print_gruppe.php                            #
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
if (!defined('IN_AKKAR')) {
	exit('Access violation.');
}

$gruppe = get_gruppe($gruppe_id, $spill_id);

echo '
	<h2 align="center">'.$LANG['MISC']['groupsheet'].'</h2>
	<h3 align="center">'.$gruppe['navn'].'</h3>
	<br>
	<table border="0" align="center" width="80%">
';
if (basename($_SERVER['PHP_SELF']) == 'visgruppe.php') {
	echo '
		<tr>
			<td>
				<table>
	';
}
foreach ($gruppe as $key=>$value) {
	switch ($key) {
		case 'spill_id':
		case 'gruppe_id':
		case 'navn':
			break;
		case 'medlemsinfo':
			if (!$value) {
				$value = $LANG['MISC']['none'];
			}
			echo '
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><strong>'.$LANG['MESSAGE']['groupmember_info'].'</strong></td>
			</tr>
			<tr>
				<td colspan="2">'.nl2br($value).'</td>
			</tr>
			';
			break;
		default:
			if (!$value) {
				$value = $LANG['MISC']['none'];
			}
			echo '
			<tr>
				<td width="5%"><strong>'.$LANG['DBFIELD'][$key].'</strong></td>
				<td>'.nl2br($value).'</td>
			</tr>
			';
	}
}
echo '
</table>
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button onClick="javascript:window.location=\'./grupper.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['groups'].'</button></td>
		<td><button type="button" onClick="javascript:window.location=\'./filvedlegg.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'&amp;vedlagt=gruppe\';">'.$LANG['MISC']['group_attachments'].'</button></td>
		<td><button onClick="javascript:window.location=\'./editgruppe.php?gruppe_id='.$gruppe_id.'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['edit'].'</button></td>
		<td><button class="red" onClick="javascript:confirmDelete(\''.$gruppe['navn'].'\', \'./grupper.php?slett_gruppe='.$gruppe_id.'&amp;spill_id='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
	</tr>
</table>
</form>
';
$medlemmer = get_gruppe_roller($gruppe_id, $spill_id);
if (!$medlemmer) {
	echo '
		<h4 align="center">'.$LANG['MISC']['empty_group'].'</h4>
	';
} else {
	echo '<h4 align="center">'.$LANG['MISC']['group_members'].'</h4>
		<table align="center" cellspacing="0">
			<tr class="highlight">
				<td><strong>'.$LANG['MISC']['character'].'</strong></td>
				<td><strong>'.$LANG['MISC']['player'].'</strong></td>
				<td>&nbsp;</td>
			</tr>
	';
	foreach ($medlemmer as $rolle_id=>$rolle) {
		$spiller = get_person($rolle['spiller_id']);
		echo '
			<tr>
				<td>
					<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a>
				</td>
				<td>
					<a href="./vispaamelding.php?person_id='.$rolle['spiller_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>
				</td>
				<td>
					<button onClick="javascript:window.location=\'./visgruppe.php?gruppe_id='.$gruppe_id.'&amp;spill_id='.$spill_id.'&amp;fjern_medlem='.$rolle['rolle_id'].'\';">'.$LANG['MISC']['remove'].'</button>
				</td>
			</tr>
		';
	}
	echo '
		</table>
	';
}
if (basename($_SERVER['PHP_SELF']) == 'visgruppe.php') {
	echo '
		</td>
		<td class="bordered">
	';
	print_plottinfo_gruppe($gruppe_id, $spill_id);
	echo '
		</td>
		</tr>
	</table>
	';
}

?>