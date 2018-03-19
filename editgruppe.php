<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              editgruppe.php                             #
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

if ((!$_REQUEST['spill_id'] || !$_REQUEST['gruppe_id']) && !$_REQUEST['nygruppe']) {
	exit('Ingen gruppe valgt.');
} else {
	$gruppe_id = $_REQUEST['gruppe_id'];
}
include('header.php');


if ($_GET['nygruppe']) {
	$gruppe = array(
	'spill_id'=>$spill_id,
	'navn'=>'',
	'beskrivelse'=>'',
	'medlemsinfo'=>'',
	);
	echo '
		<h3 align="center">'.$LANG['MISC']['new_group'].'</h3>
		<br>
		<form name="editgruppeform" action="./visgruppe.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="ny_gruppe" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<table border="0" align="center" width="50%">
	';
} else {
	$gruppe = get_gruppe($gruppe_id, $spill_id);
	echo '
		<h3 align="center">'.$LANG['MISC']['edit_group'].'<br>'.$gruppe['navn'].'</h3>
		<br>
		<form name="editgruppeform" action="./visgruppe.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="edited" value="'.$gruppe_id.'">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<table border="0" align="center" width="50%">
	';
}
foreach ($gruppe as $key=>$value) {
	switch ($key) {
		case 'spill_id':
		case 'gruppe_id':
			break;
		case 'medlemsinfo':
			echo '
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><strong>'.$LANG['MESSAGE']['groupmember_info'].'</strong></td>
			</tr>
			<tr>
				<td colspan="2"><textarea cols="75" rows="'.get_numrows($value, 5).'" id="'.$key.'" name="'.$key.'">'.htmlentities(stripslashes($value)).'</textarea></td>
			</tr>
					<tr>
					<td align="left">
					'.inputsize_less($key, 1).'
					</td>
					<td align="right">
					'.inputsize_more($key, 1).'
					</td>
					</tr>
			';
			break;
		case 'beskrivelse':
			echo '
			<tr>
				<td><strong>'.$LANG['DBFIELD'][$key].'</strong></td>
				<td><input type="text" name="'.$key.'" size="50" value="'.$value.'"></td>
			</tr>
			';
			break;
		default:
			echo '
			<tr>
				<td><strong>'.$LANG['DBFIELD'][$key].'</strong></td>
				<td><input type="text" size="20" name="'.$key.'" value="'.$value.'"></td>
			</tr>
			';
	}
}
echo '
</table>
<table align="center">
	<tr>
		<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
</form>
';



include('footer.php');
?>