<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                visfil.php                               #
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

if ($_POST['move']) {
	if (move_file($_POST['move'], $_POST['dir'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['file_moved'];
		header('Location: ./visfil.php?fil_id='.$_POST['move']);
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['file_move_error'];
		header('Location: ./visfil.php?fil_id='.$_POST['move']);
	}
	exit();
} elseif ($_POST['edited']) {
	if (update_file($_POST['edited'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['file_updated'];
		header('Location: ./visfil.php?fil_id='.$_POST['edited']);
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['file_update_error'];
		header('Location: ./visfil.php?fil_id='.$_POST['edited']);
	}
	exit();
} elseif ($_FILES) {
	if (replace_file($_POST['fil_id'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['file_updated'];
		header('Location: ./visfil.php?fil_id='.$_POST['fil_id']);
	} else {
		$_SESSION['message'] .= $LANG['MESSAGE']['file_update_error'];
		header('Location: ./visfil.php?fil_id='.$_POST['fil_id']);
	}
	exit();
}

include('header.php');

echo '
	<h2 align="center">'.$LANG['MISC']['fileinfo'].'</h2>
	<br>
';

$fil = get_fil($_GET['fil_id']);

echo '
	<table align="center" class="bordered" cellspacing="0">
		<tr>
			<td class="highlight">'.$LANG['MISC']['filename'].'</td>
			<td><a href="./download.php?fil_id='.$fil['fil_id'].'">'.$fil['navn'].'</a></td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['dir'].'</td>
			<td>'.$fil['dir'].'</td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['filetype'].'</td>
			<td>'.$fil['type'].'</td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['filesize'].'</td>
			<td>'.get_human_readable_size(filesize($config['filsystembane'].''.$fil['dir'].''.$fil['navn'])).'</td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['updated'].'</td>
			<td>'.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $fil['oppdatert'])).'</td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['description'].'</td>
			<td>'.$fil['beskrivelse'].'</td>
		</tr>
	</table>
	<form name="movefileform" action="./visfil.php" method="POST">
	<table align="center" cellspacing="0" border="0">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="nospace"><input type="hidden" name="move" value="'.$fil['fil_id'].'">
				<select name="dir">
					<option value="/"'; if ($fil['dir'] == '/') { echo ' selected'; } echo '>/</option>
				';
				if ($dirs = get_fs_dirtree('/')) {
					foreach ($dirs as $dir) {
						echo '<option value="'.$dir.'/"'; if ($fil['dir'] == $dir.'/') { echo ' selected'; } echo '>'.$dir.'</option>';
					}
				}
				echo '
				</select>
			</td>
			<td class="nospace"><button type="button" onClick="javascript:document.movefileform.submit();">'.$LANG['MISC']['move'].'</button></td>
			<td>&nbsp;</td>
			<td class="nospace"><button type="button" onClick="javascript:window.location=\'./editfil.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		</tr>
	</table>
	</form>
	<form name="updatefileform" enctype="multipart/form-data" action="visfil.php" method="post">
	<input type="hidden" name="fil_id" value="'.$fil['fil_id'].'">
	<input type="hidden" name="dir" value="'.$fil['dir'].'">
	<table align="center" cellspacing="0">
		<tr>
			<td class="nospace"><input type="file" name="nyfil"></td>
			<td><button type="submit" onclick="javascript:if (document.updatefileform.nyfil.value == \'\') { window.alert(\''.$LANG['JSBOX']['no_file'].'\'); return false; } else { return true; }">'.$LANG['MISC']['replace'].'</button></td>
		</tr>
	</table>
	</form>
	<table align="center" cellspacing="0">
		<tr>
			<td colspan="3" align="center"><button type="button" onClick="window.location=\'./filsystem.php\';">'.$LANG['MISC']['filesystem'].'</button></td>
		</tr>
	</table>
';

$vedlagt_roller = get_fil_vedlagt($fil['fil_id'], 'rolle');
$vedlagt_grupper = get_fil_vedlagt($fil['fil_id'], 'gruppe');
$vedlagt_spill = get_fil_vedlagt($fil['fil_id'], 'spill');
$vedlagt_rollekonsept = get_fil_vedlagt($fil['fil_id'], 'rollekonsept');

if ($vedlagt_roller || $vedlagt_grupper || $vedlagt_spill || $vedlagt_rollekonsept) {
echo '
	<table align="center" cellspacing="0">
	<tr>
		<td>&nbsp;</td>
	</tr>
	';
	
if ($vedlagt_rollekonsept) {
	echo '
		<tr>
			<td align="center"><h4 class="table">'.$LANG['MISC']['attached_to_concepts'].':</h4></td>
		</tr>
		<tr>
			<td>
		<table align="center" cellspacing="0" width="100%">
			<tr class="highlight">
				<td>'.$LANG['MISC']['game'].'</td>
			</tr>
	';
	foreach ($vedlagt_rollekonsept as $konseptvedlegg) {
		$spillinfo = get_spillinfo($konseptvedlegg['spill_id']);
		echo '
			<tr>
				<td><a href="./rollekonsept.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>
			</tr>
		';
	}
	echo '
		</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
	';
}
if ($vedlagt_roller) {
	echo '
		<tr>
			<td align="center"><h4 class="table">'.$LANG['MISC']['attached_to_characters'].':</h4></td>
		</tr>
		<tr>
			<td>
		<table align="center" cellspacing="0" width="100%">
			<tr class="highlight">
				<td>'.$LANG['MISC']['character'].'</td>
				<td>'.$LANG['MISC']['player'].'</td>
				<td>'.$LANG['MISC']['game'].'</td>
			</tr>
	';
	foreach ($vedlagt_roller as $rollevedlegg) {
		$rolle = get_rolle($rollevedlegg['vedlegg_id'], $rollevedlegg['spill_id']);
		$spillinfo = get_spillinfo($rollevedlegg['spill_id']);
		$spiller = get_person($rolle['spiller_id']);
		if ($spiller) {
			$spillernavn = '<a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
		} else {
			$spillernavn = $LANG['MISC']['none'];
		}
		echo '
			<tr>
				<td><a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>
				<td>'.$spillernavn.'</td>
				<td><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>
			</tr>
		';
	}
	echo '
		</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
	';
}

if ($vedlagt_grupper) {
	echo '
		<tr>
			<td align="center"><h4 class="table">'.$LANG['MISC']['attached_to_groups'].':</h4></td>
		</tr>
		<tr>
			<td>
		<table align="center" cellspacing="0" width="100%">
			<tr class="highlight">
				<td>'.$LANG['MISC']['group'].'</td>
				<td>'.$LANG['MISC']['game'].'</td>
			</tr>
	';
	foreach ($vedlagt_grupper as $gruppevedlegg) {
		$gruppe = get_gruppe($gruppevedlegg['vedlegg_id'], $gruppevedlegg['spill_id']);
		$spillinfo = get_spillinfo($gruppevedlegg['spill_id']);
		echo '
			<tr>
				<td><a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a></td>
				<td><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>
			</tr>
		';
	}
	echo '
		</table>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
	';
}

if ($vedlagt_spill) {
	echo '
		<tr>
			<td align="center"><h4 class="table">'.$LANG['MISC']['attached_to_games'].':</h4></td>
		</tr>
		<tr>
			<td>
		<table align="center" cellspacing="0" width="100%">
			<tr class="highlight">
				<td>'.$LANG['MISC']['game'].'</td>
			</tr>
	';
	foreach ($vedlagt_spill as $spillvedlegg) {
		$spillinfo = get_spillinfo($spillvedlegg['spill_id']);
		echo '
			<tr>
				<td><a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a></td>
			</tr>
		';
	}
	echo '
		</table>
			</td>
		</tr>
	';
}
echo '
	</table>
';
} else {
	echo '
		<h4 align="center">'.$LANG['MISC']['attached_to_nothing'].'</h4>
	';
}

include('footer.php');

?>