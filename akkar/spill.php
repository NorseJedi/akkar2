<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                 spill.php                               #
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

if ($_GET['deaktiviser'] || $_GET['aktiviser']) {
	oppdater_spillstatus();
	header('Location: ./spill.php');
	exit();
} elseif ($_GET[slett_spill]) {
	if (is_admin()) {
		slett_spill();
		$_SESSION['message'] = $LANG['MESSAGE']['game_deleted'];
		header('Location: ./spill.php');
		exit();
	}
}

include('header.php');

$spill = get_spill();
$fields = get_fields($table_prefix.'spill');

echo '
	<h2 align="center">'.$LANG['MISC']['games'].'</h2>
	<br>
';

if (count($spill) == 0) {
	echo '<h4 align="center">'.$LANG['MISC']['no_games'].'</h4>';
} else {
	echo '
		<table border="0" cellpadding="5" cellspacing="0" align="center">
			<tr class="highlight">
	';
	foreach ($fields as $fieldname) {
		switch($fieldname) {
			case 'spill_id';
			case 'rollemal';
			case 'paameldingsmal';
			case 'rollekonsept';
				break;
			default:
				$sorting = get_sorting('./spill.php', $fieldname, 'spillorder');
				echo '<td nowrap>'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>';
		}
	}
	echo '
		<td colspan="4">&nbsp;</td>
		</tr>
	';
	foreach ($spill as $spill_id=>$spillinfo) {
		echo '<tr>';
		foreach ($spillinfo as $key=>$value) {
			switch($key) {
				case 'spill_id';
				case 'rollemal';
				case 'paameldingsmal';
				case 'rollekonsept';
					break;
				case 'start';
				case 'slutt';
					echo '<td>'.ucfirst(strftime($config['long_dateformat'], $value)).'</td>';
					break;
				case 'status';
					if (strtolower($value) == 'aktiv') {
						echo '<td><span class="green">'.$LANG['MISC']['active'].'</span></td>';
					} else {
						echo '<td><span class="red">'.$LANG['MISC']['inactive'].'</span></td>';
					}
					break;
				case 'navn';
					echo '<td><a href="./visspill.php?spill_id='.$spill_id.'">'.$value.'</a></td>';
					break;
				default:
					echo '<td>'.$value.'</td>';
			}
		}
		if (strtolower($spillinfo['status']) == 'aktiv') {
			echo '
			<td class="nospace"><button onClick="javascript:window.location=\'./spill.php?deaktiviser='.$spill_id.'\';">'.$LANG['MISC']['deactivate'].'</button></td>
			';
		} else {
			echo '
			<td class="nospace" align="center"><button onClick="javascript:window.location=\'./spill.php?aktiviser='.$spill_id.'\';">'.$LANG['MISC']['activate'].'</button></td>
			';
		}
		echo '
			<td class="nospace"><button onClick="javascript:window.location=\'./editspill.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['edit'].'</button></td>
		';
		if (is_admin()) {
			echo '
				<td class="nospace"><button class="red" onClick="javascript:confirmDeleteSpill(\''.addslashes($spillinfo['navn']).'\', \'./spill.php?slett_spill='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
			';
		}
		echo '
			</td>
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
		<td><button onClick="javascript:window.location=\'./editspill.php?nyttspill=yes\';">'.$LANG['MISC']['create_game'].'</button></td>
	</tr>
</table>
';

include('footer.php');
?>
