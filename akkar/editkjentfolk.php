<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             editkjentfolk.php                           #
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

if ($_GET['edit_kjentrolle']) {
	$rolle = get_rolle($_GET['rolle_id'], $spill_id);
	$kjentdata = get_kjentfolk_data($_GET['rolle_id'], $_GET['edit_kjentrolle'], $spill_id);
	$kjentrolle = get_rolle($kjentdata['kjent_rolle_id'], $spill_id);
	$kjentspiller = get_person($kjentrolle['spiller_id']);
	$kjentspiller['bilde'] = mugshot($kjentspiller);

	echo '
		<h2 align="center">'.$LANG['MISC']['edit_acquaintance'].'</h2>
		<h3 align="center">'.$rolle['navn'].'</h3>
		<br>
		<form name="editkjentfolk" action="./viskjentfolk.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
		<input type="hidden" name="spill_id" value="'.$rolle['spill_id'].'">
		<input type="hidden" name="oppdater_kjentfolk" value="'.$kjentrolle['rolle_id'].'">
		<table width="50%" class="bordered" border="0" align="center">
			<tr>
				<td rowspan="5" colspan="2">
					<img src="'.$kjentspiller['bilde'].'" width="60" heigth="75" class="foto">
				</td>
				<td class="highlight" width="90%" colspan="2">
					<a href="./visrolle.php?rolle_id='.$kjentrolle['rolle_id'].'&amp;spill_id='.$kjentrolle['spill_id'].'">'.$kjentrolle['navn'].'</a> (<a href="./vispaamelding.php?person_id='.$kjentspiller['person_id'].'&amp;spill_id='.$spill_id.'">'.$kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].'</a>)
				</td>
			</tr>
			<tr>
				<td valign="top">
					<strong>'.$LANG['MISC']['relation'].'</strong> <input type="text" name="kjentgrunn" value="'.$kjentdata['kjentgrunn'].'">
				</td>
				<td> <strong>'.$LANG['MISC']['level'].'</strong> 
				<select name="level">
					<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
					<option value="1"'; if ($kjentdata['level'] == '1') { echo ' selected'; } echo '>'.$LANG['MISC']['intimate'].'</option>
					<option value="2"'; if ($kjentdata['level'] == '2') { echo ' selected'; } echo '>'.$LANG['MISC']['medium'].'</option>
					<option value="3"'; if ($kjentdata['level'] == '3') { echo ' selected'; } echo '>'.$LANG['MISC']['barely'].'</option>
				</select>
				</td>
			</tr>
			<tr>
				<td colspan="3" align="center">
					<button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button>
					<button type="reset">'.$LANG['MISC']['reset'].'</button>
					<button type="submit">'.$LANG['MISC']['save'].'</button>
				</td>
			</tr>
		</table>
		</form>
	';
}

include('footer.php');
?>
