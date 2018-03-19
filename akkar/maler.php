<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                 maler.php                               #
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

if ($_GET['slettmal']) {
	if (is_admin()) {
		slett_mal();
		header('Location: ./maler.php');
		exit();
	}
}

include('header.php');

$maler = get_maler();

echo '
	<h2 align="center">'.$LANG['MISC']['templates'].'</h2>
	<br>
';

if (count($maler) == 0) {
	echo '<h4 align="center">'.$LANG['MISC']['no_templates'].'</h4>';
} else {
	echo '
		<table border="0" cellpadding="3" cellspacing="0" align="center">
			<tr class="highlight">
	';
	$sorting = get_sorting('./maler.php','navn','malorder');
	echo '
				<td>'.$LANG['MISC']['name'].' '.$sorting.'</td>
	';
	$sorting = get_sorting('./maler.php', 'type', 'malorder');
	echo '
				<td nowrap>'.$LANG['MISC']['type'].' '.$sorting.'</td>
				<td>'.$LANG['MISC']['used_for_game'].'</td>
				<td align="center">&nbsp;</td>
			</tr>
	';
	foreach ($maler as $mal) {
		echo '<tr>';
		foreach ($mal as $key=>$value) {
			switch ($key) {
				case 'mal_id';
					break;
				case 'navn';
					$sorting = get_sorting('./maler.php', 'navn', 'malorder');
					echo '<td><a href="./vismal.php?mal_id='.$mal['mal_id'].'">'.$value.'</a></td>';
					break;
				default;
					echo '<td>'.$LANG['DBFIELD'][$value].'</td>';
			}
		}
		if ($spill = get_malspill($mal['mal_id'])) {
			$spillstring = '';
			echo '<td>';
			foreach ($spill as $spillinfo) {
				$spillstring .= '<a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a>, ';
			}
			echo substr(trim($spillstring), 0, -1).'</td>';
		} else {
			echo '
				<td>'.$LANG['MISC']['unused'].'</td>
			';
		}
		echo '
			<td>
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td class="nospace"><button class="red" onClick="javascript:confirmDelete(\'malen '.addslashes($mal['navn']).'\', \'./maler.php?slettmal='.$mal['mal_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
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
<script language="JavaScript" type="text/javascript">
function validateNymalform() {
	if (document.nymalform.navn.value == \'\' || document.nymalform.navn.value == \'Navn\') {
		window.alert(\''.$LANG['JSBOX']['template_name'].'\');
		document.nymalform.navn.value = \'\';
		document.nymalform.navn.focus();
		return false;
	}
	if (document.nymalform.type.value == \'\') {
		window.alert(\''.$LANG['JSBOX']['template_type'].'\');
		document.nymalform.type.focus();
		return false;
	}
	return true;
}
</script>
<form name="nymalform" action="./vismal.php" method="post">
<input type="hidden" name="nymal" value="yes">
<table align="center" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3" class="bt">&nbsp;</td>
	</tr>
	<tr>
		<td class="highlight">'.$LANG['MISC']['name'].'</td>
		<td class="highlight">'.$LANG['MISC']['type'].'</td>
		<td class="highlight">&nbsp;</td>
	</tr>
	<tr>
		<td><input type="text" name="navn" size="15" onClick="javascript:document.nymalform.navn.value = \'\';"></td>
		<td><select name="type">
			<option value="" style="margin-bottom: 1em; font-style: italic;">- '.$LANG['MISC']['select'].' -</option>
			<option value="rolle">'.$LANG['DBFIELD']['rolle'].'</option>
			<option value="paamelding">'.$LANG['DBFIELD']['paamelding'].'</option>
			</select>
		</td>
		<td><button type="submit" onClick="javascript:return validateNymalform();">'.$LANG['MISC']['create_template'].'</button></td>
	</tr>
</table>
</form>
';

include('footer.php');
?>
