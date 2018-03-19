<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             editdeadline.php                            #
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

if (!is_koordinator()) {
	header('Location: '.$whereiwas);
	exit();
}
echo '
		<form name="editdeadline" action="./visspill.php" method="post">
';

if ($_GET['ny_deadline']) {
	$deadline = array();
	echo '
		<h2 align="center">'.$LANG['MISC']['new_deadline'].'</h2>
		<br>
		<input type="hidden" name="ny_deadline" value="yes">
	';
} else {
	$deadline = get_deadline($_GET['deadline_id'], $spill_id);
	$dag = strftime('%d', $deadline['deadline']);
	$mnd = strftime('%m',  $deadline['deadline']);
	$aar = strftime('%Y',  $deadline['deadline']);
	echo '
		<h2 align="center">'.$LANG['MISC']['edit_deadline'].'</h2>
		<h3 align="center">'.$deadline['tekst'].'</h3>
		<br>
		<input type="hidden" name="edited_deadline" value="'.$deadline['deadline_id'].'">
	';
}

for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mnder = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = date('Y'); $i <= 2037; $i++) {
	$aarliste[$i] = $i;
}

echo '
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<table align="center" class="bordered" cellspacing="0" cellpadding="3">
		<tr>
			<td class="highlight">'.$LANG['MISC']['action'].'</td>
			<td><input type="text" name="tekst" maxlength="30" size="30" value="'.$deadline['tekst'].'"></td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['deadline'].'</td>
			<td><select id="dag" name="dag"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, $dag).'</select> <select id="mnd" name="mnd"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mnder, $mnd).'</select> <select id="aar" name="aar"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, $aar).'</select></td>
		</tr>
	</table>
	<table align="center" cellspacing="0" cellpadding="0">
		<tr>
			<td>&nbsp;</td>
		</tr>
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