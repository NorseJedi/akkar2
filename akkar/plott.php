<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                 plott.php                               #
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

if ($_GET['slett_plott']) {
	slett_plott();
	$_SESSION['message'] = $LANG['MESSAGE']['plot_deleted'];
	header('Location: ./plott.php?spill_id='.$_GET['spill_id']);
	exit();
}

if (!$spill_id) {
	echo $LANG['ERROR']['no_game_selected'];
	exit();
}

include('header.php');

if ($_REQUEST['rolle_id']) {
	print_plottinfo_rolle_small($_REQUEST['rolle_id'], $spill_id);
	exits();
}

$spillplott = get_spillplott($spill_id);
$fields = get_fields($table_prefix.'plott');

echo '
	<h2 align="center">'.$LANG['MISC']['plots'].'</h2>
	<br>
';

if (count($spillplott) == 0) {
	echo '<h4 align="center">'.$LANG['MISC']['no_plots'].'</h4>';
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
			case 'plott_id':
				break;
			case 'oppdatert':
				echo '<td>&nbsp;</td>';
			default:
				echo '<td style="white-space:nowrap">'.$LANG['DBFIELD'][$fieldname].'</td>';
		}
	}
	echo '
		<td colspan="2">&nbsp;</td>
		</tr>
		<tbody id="filters">
		<tr class="highlight">
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['name']).'" type="text" id="navn_filter" title="navn_filter" onkeyup="javascript:filter_list(this.value, \'navn\');"></td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['descritption']).'" type="text" id="beskrivelse_filter" title="beskrivelse_filter" onkeyup="javascript:filter_list(this.value, \'beskrivelse\');"></td>
		<td>&nbsp;</td>
		<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['updated']).'" type="text" id="oppdatert_filter" title="oppdatert_filter" onkeyup="javascript:filter_list(this.value, \'oppdatert\');"></td>
		<td colspan="2">&nbsp;</td>
		</tr>
		</tbody>
	';
	foreach ($spillplott as $plott_id=>$plott) {
		echo '<tr>';
		foreach ($plott as $key=>$value) {
			switch($key) {
				case 'spill_id':
				case 'plott_id':
					break;
				case 'oppdatert':
					echo '
						<td class="nospace" nowrap>'.info_icon($plott['navn'], $plott['beskrivelse']).'</td>
						<td id="oppdatert_p'.$plott['plott_id'].'s'.$plott['spill_id'].'" title="'.ucfirst(strftime($config['short_dateformat'], $value)).'" onClick="javascript:return overlib(\''.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $value)).'\', CAPTION, \''.$LANG['MISC']['updated'].'\');">'.ucfirst(strftime($config['short_dateformat'], $value)).'</td>';
					break;
				case 'navn':
					echo '
					<td id="navn_p'.$plott['plott_id'].'s'.$plott['spill_id'].'" title="'.$value.'" style="white-space: nowrap"><a href="./visplott.php?plott_id='.$plott['plott_id'].'&amp;spill_id='.$plott['spill_id'].'&amp;infowindow='.$infowindow.'">'.$value.'</a></td>
					';
					break;
				default:
					if (strlen($value) > 50) {
						$shortvalue = substr($value, 0, 50).'...';
					} else {
						$shortvalue = $value;
					}
					echo '<td id="beskrivelse_p'.$plott['plott_id'].'s'.$plott['spill_id'].'" title="'.$value.'" style="white-space: nowrap">'.$shortvalue.'</td>';
			}
		}
		echo '
			<td class="nospace" align="right" nowrap>
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td class="nospace" nowrap><button type="button" onClick="javascript:window.location=\'./editplott.php?plott_id='.$plott['plott_id'].'&amp;spill_id='.$plott['spill_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
						<td class="nospace" nowrap><button type="button" class="red" onClick="javascript:return confirmDelete(\''.addslashes($plott['navn']).'\', \'./plott.php?slett_plott='.$plott['plott_id'].'&amp;spill_id='.$plott['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
					</tr>
				</table>
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
		<td><button onClick="javascript:window.location=\'./editplott.php?nytt_plott=yes&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['create_plot'].'</button></td>
	</tr>
</table>
';

include('footer.php');
?>
