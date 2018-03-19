<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               editnotat.php                             #
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

$notat = get_kalnotat($_GET['edit_notat']);
$person = get_person($notat['person_id']);

for ($i = 1; $i <= 31; $i++) {
	$dagliste[$i] = $i;
}
$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = 2037; $i >= 1970; $i--) {
	$aarliste[$i] = $i;
}

echo '
	<script language="JavaScript" type="text/javascript">
		function validate_notat() {
			if (!validDate(document.editnotat.notat_dag.value, document.editnotat.notat_mnd.value, document.editnotat.notat_aar.value)) {
				window.alert(\''.$LANG['JSBOX']['invalid_date'].'\');
				return false;
			}
			if (document.editnotat.tekst.value == \'\') {
				window.alert(\''.$LANG['JSBOX']['note_text'].'\');
				document.editnotat.tekst.focus();
				return false;
			}
			return true;
		}
	</script>
	<h2 align="center">'.$LANG['MISC']['edit_note'].'</h2>
	<h3 align="center">'.ucfirst(strftime($config['long_dateformat'], jdtounix($notat['juliandc']))).'</h3>
	<br>
	<form name="editnotat" action="./kalender.php" method="post">
	<input type="hidden" name="month" value="'.$_GET['month'].'">
	<input type="hidden" name="year" value="'.$_GET['year'].'">
	<input type="hidden" name="edited_notat" value="'.$notat['notat_id'].'">
	<table align="center" class="bordered" cellspacing="0" cellpadding="3">
		<tr class="highlight">
			<td colspan="2">'.$LANG['MISC']['entered_by'].' '.$person['fornavn'].' '.$person['etternavn'].'</td>
		</tr>
		<tr>
			<td colspan="2" class="highlight" >'.$LANG['MISC']['date'].': <select name="notat_dag"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dagliste, strftime('%d', jdtounix($notat['juliandc']))).'</select> <select name="notat_mnd"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mndliste, strftime('%m', jdtounix($notat['juliandc']))).'</select> <select name="notat_aar"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, strftime('%Y', jdtounix($notat['juliandc']))).'</select></td>
		</tr>
		<tr class="highlight">
			<td colspan="2"><textarea id="tekst" name="tekst" cols="75" rows="'.get_numrows($notat['tekst'], 5).'">'.$notat['tekst'].'</textarea></td>
		</tr>
		<tr class="highlight">
			<td align="left">
				'.inputsize_less('tekst', 1).'
			</td>
			<td align="right">
				'.inputsize_more('tekst', 1).'
			</td>
		</tr>
	</table>
	<table align="center" cellspacing="0" cellpadding="0">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
			<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td><button type="submit" onClick="javascript:return validate_notat();">'.$LANG['MISC']['save'].'</button></td>
		</tr>
	</table>
	</form>
';

include('footer.php');

?>