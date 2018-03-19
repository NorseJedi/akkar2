<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               visgruppe.php                             #
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
if ($_POST['edited']) {
	oppdater_gruppeinfo();
	$_SESSION['message'] = $LANG['MESSAGE']['group_updated'];
	header('Location: ./visgruppe.php?gruppe_id='.$_REQUEST['edited'].'&spill_id='.$_REQUEST['spill_id']);
	exit();
} elseif ($_POST['ny_gruppe']) {
	$gruppe_id = opprett_gruppe();
	$_SESSION['message'] = $LANG['MESSAGE']['group_created'];
	header('Location: ./visgruppe.php?gruppe_id='.$gruppe_id.'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_GET['fjern_medlem']) {
	fjern_gruppe_medlem();
	$_SESSION['message'] = $LANG['MESSAGE']['character_removed_from_group'];
	header('Location: ./visgruppe.php?gruppe_id='.$_GET['gruppe_id'].'&spill_id='.$_GET['spill_id']);
	exit();
} elseif ($_POST['ny_medlem']) {
	ny_gruppe_medlem();
	$_SESSION['message'] = $LANG['MESSAGE']['character_added_to_group'];
	header('Location: ./visgruppe.php?gruppe_id='.$_POST['gruppe_id'].'&spill_id='.$_POST['spill_id']);
	exit();
}

if (!$_REQUEST['spill_id'] || !$_REQUEST['gruppe_id']) {
	exit($LANG['ERROR']['no_group_selected']);
} else {
	$gruppe_id = $_REQUEST['gruppe_id'];
}
include('header.php');

include('print_gruppe.php');

echo '
<form class="noprint" name="nymedlemform" action="visgruppe.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
<input type="hidden" name="gruppe_id" value="'.$gruppe_id.'">
<input type="hidden" name="spill_id" value="'.$spill_id.'">
<table align="center" style="margin-top: 2em;">
	<tr>
		<td colspan="2" class="highlight">'.$LANG['MISC']['new_groupmember'].'</td>
	</tr>
	<tr>
		<td><select name="ny_medlem">
			<option value="" class="selectname" align="center">- '.$LANG['MISC']['select'].' -</option>
';
$roller = get_roller($spill_id);
foreach ($roller as $rolle) {
	$rolle_grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id']);
	if (!$rolle_grupper[$gruppe_id]) {
		$spiller = get_person($rolle['spiller_id']);
		if (!$spiller) {
			$spillernavn = $LANG['MISC']['none'];
		} else {
			$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'];
		}
		echo '<option value="'.$rolle['rolle_id'].'">'.$rolle['navn'].' ('.$spillernavn.')</option>';
	}
}
echo '
		</td>
		<td><button type="submit">'.$LANG['MISC']['add'].'</button></td>
	</tr>
</table>
</form>
';

include('footer.php');
?>