<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              hentroller.php                             #
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

if ($_POST['hent_roller']) {
	hent_roller();
	$_SESSION['message'] = $LANG['MESSAGE']['characters_transfered'];
	header('Location: ./hentroller.php?spill_id='.$spill_id);
	exit();
}

include('header.php');

echo '
	<h2 align="center">'.$LANG['MISC']['character_transfer'].'</h2>
';
$spillinfo = get_spillinfo($spill_id);
$mal_id = $spillinfo['rollemal'];
unset($spillinfo);
if ($alleroller = get_roller(0)) {
	foreach ($alleroller as $rolle) {
		if (($rolle['spill_id'] != $spill_id) && (!get_rolle($rolle['rolle_id'], $spill_id))) {
			$roller[] = $rolle;
		}
	}
	$numroller = count($roller);
}
$buttons = '
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button type="reset" onClick="return confirm(\''.$LANG['JSBOX']['confirm_reset'].'\');">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['transfer_selected'].'</button></td>
	</tr>
</table>
';

if (!$roller) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_characters'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
			</tr>
		</table>
	';
} else {
	echo '
		<h4 align="center">'.$numroller.' '.$LANG['MISC']['character_s'].'</h4>
		<br />
		<h5 align="center">'.$LANG['MESSAGE']['red_players_not_registered'].'</h5>
		<form name="hentrolleform" action="./hentroller.php" method="post">
		'.$buttons.'
		<br>
	';
	echo '
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="hent_roller" value="yes">
		<table border="0" cellspacing="0" align="center">
			<tr class="noprint">
				<td colspan="4" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
		<td>&nbsp;</td>
	';
	$sorting = get_sorting('./hentroller.php?spill_id='.$spill_id, 'navn', 'rolleorder');
	echo '
		<td nowrap>'.$LANG['MISC']['character'].' '.$sorting.'</td>
	';
	$sorting = get_sorting('./hentroller.php?spill_id='.$spill_id,'spill_id','rolleorder');
	echo '
		<td nowrap>'.$LANG['MISC']['game'].' '.$sorting.'</td>
	';
	echo '
		<td nowrap>'.$LANG['MISC']['player'].'</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
		<td>&nbsp;</td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['character']).'" type="text" id="navn_filter" title="navn_filter" onkeyup="javascript:filter_list(this.value, \'navn\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['game']).'" type="text" id="spill_filter" title="spill_filter" onkeyup="javascript:filter_list(this.value, \'spill\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['player']).'" type="text" id="spiller_filter" title="spiller_filter" onkeyup="javascript:filter_list(this.value, \'spiller\');"></td>
		</tr>
		</tbody>
	';

	foreach ($roller as $rolle) {
		$spillinfo = get_spillinfo($rolle['spill_id']);
		$spiller = get_person($rolle['spiller_id']);
		echo '<tr>';
		echo '
			<td nowrap>
			<input type="checkbox"'; if ($mal_id != $spillinfo['rollemal']) { echo ' onClick="javascript:if (this.checked == true) { return confirm(\''.$LANG['JSBOX']['character_transfer_template_mismatch'].'\') };" '; } echo ' name="'.$rolle['rolle_id'].'_'.$rolle['spill_id'].'">
			</td>
			<td id="navn_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$rolle['navn'].'" nowrap><a href="visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>
		';
		echo '<td id="spill_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$spillinfo['navn'].'" nowrap><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>';
		if ((!get_paamelding($spiller['person_id'], $spill_id)) && (!get_arrangor($spiller['person_id']))) {
			echo '
				<td id="spiller_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$spiller['fornavn'].' '.$spiller['etternavn'].'" nowrap><a href="visperson.php?person_id='.$spiller['person_id'].'"><span class="red">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</span></a></td>
			';
		} else {
			echo '
				<td id="spiller_r'.$rolle['rolle_id'].'s'.$rolle['spill_id'].'" title="'.$spiller['fornavn'].' '.$spiller['etternavn'].'" nowrap><a href="visperson.php?person_id='.$spiller['person_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a></td>
			';
		}
		echo '
		</tr>';
	}
	echo '
		</table>
		'.$buttons.'
		</form>
	';
}
include('footer.php');

?>