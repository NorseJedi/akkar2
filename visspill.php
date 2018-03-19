<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               visspill.php                              #
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
	header('Location: ./visspill.php?spill_id='.$_REQUEST['spill_id']);
	exit();
} elseif ($_POST['edited']) {
	oppdater_spillinfo();
	$_SESSION['message'] = $LANG['MESSAGE']['game_updated'];
	header('Location: ./visspill.php?spill_id='.$_REQUEST['spill_id']);
	exit();
} elseif ($_POST['nytt']) {
	$spill_id = nytt_spill();
	$_SESSION['message'] = $LANG['MESSAGE']['game_created'];
	header('Location: ./visspill.php?spill_id='.$spill_id);
	exit();
} elseif ($_POST['ny_deadline']) {
	if (is_koordinator()) {
		opprett_deadline($spill_id);
		$_SESSION['message'] .= $LANG['MESSAGE']['deadline_created'];
	}
	header('Location: ./visspill.php?spill_id='.$spill_id);
	exit();
} elseif ($_POST['edited_deadline']) {
	if (is_koordinator()) {
		oppdater_deadline($_POST['edited_deadline'], $spill_id);
		$_SESSION['message'] .= $LANG['MESSAGE']['deadline_updated'];
	}
	header('Location: ./visspill.php?spill_id='.$spill_id);
	exit();
} elseif ($_GET['slett_deadline']) {
	if (is_koordinator()) {
		slett_deadline($_GET['slett_deadline'], $spill_id);
		$_SESSION['message'] = $LANG['MESSAGE']['deadline_deleted'];
	}
	header('Location: ./visspill.php?spill_id='.$spill_id);
	exit();
}

if (!$_REQUEST['spill_id']) {
	exit($LANG['ERROR']['no_game_selected']);
} else {
	$spill_id = $_REQUEST['spill_id'];
}
include('header.php');

$spillinfo = get_spillinfo($_REQUEST['spill_id']);
$maler = get_maler();

echo '
	<h2 align="center">'.$LANG['MISC']['gameinfo'].'</h2>
	<h3 align="center">'.$spillinfo['navn'].'</h3>
	<br>
	<table border="0" align="center" width="50%">
';
foreach ($spillinfo as $key=>$value) {
	switch ($key) {
		case 'spill_id';
		case 'navn';
			break;
		case 'paameldingsmal';
			$mal = get_malinfo($value);
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['registrationtemplate'].'</strong></td>
				<td>'.$mal['navn'].'</td>
			</tr>
			';
			break;
		case 'rollemal';
			$mal = get_malinfo($value);
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['chartemplate'].'</strong></td>
				<td>'.$mal['navn'].'</td>
			</tr>
			';
			break;
		case 'status';
			echo '
				<tr>
					<td><strong>'.$LANG['MISC']['status'].'</strong></td>
			';
			if (strtolower($value) == 'aktiv') {
				echo '<td><span class="green">'.$LANG['MISC']['active'].'</span></td>';
			} else {
				echo '<td><span class="red">'.$LANG['MISC']['inactive'].'</span></td>';
			}
			echo '
			</tr>
			';
			break;
		case 'start';
		case 'slutt';
			echo '
			<tr>
				<td><strong>'.$LANG['DBFIELD'][$key].'</strong></td>
				<td>'.ucfirst(strftime($config['long_dateformat'], $value)).'</td>
			</tr>
			';
			break;
		case 'rollekonsept';
			if ($value == 1) {
				$value = $LANG['MISC']['yes'];
			} else {
				$value = $LANG['MISC']['no'];
			}
			echo '
			<tr>
				<td><strong>'.$LANG['MISC']['use_concept'].'</strong></td>
				<td>'.$value.'</td>
			</tr>
			';
			break;
		default:
			echo '
			<tr>
				<td><strong>'.$LANG['DBFIELD'][$key].'</strong></td>
				<td>'.$value.'</td>
			</tr>
			';
	}
}
echo '
	</table>
	<br>
';

echo '
<table align="center">
	<tr>
		<td><button onClick="javascript:window.location=\'./spill.php\'">'.$LANG['MISC']['games'].'</button></td>
		<td><button onClick="javascript:window.location=\'./editspill.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['edit'].'</button></td>
';
if (strtolower($spillinfo[status]) == 'aktiv') {
	echo '<td><button onClick="javascript:window.location=\'./visspill.php?deaktiviser='.$spill_id.'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['deactivate'].'</button></td>';
} else {
	echo '<td><button onClick="javascript:window.location=\'./visspill.php?aktiviser='.$spill_id.'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['activate'].'</button></td>';
}
if (is_admin()) {
	echo '<td><button class="red" onClick="javascript:confirmDeleteSpill(\''.addslashes($spillinfo['navn']).'\', \'./spill.php?slett_spill='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>';
}

echo '
	</tr>
</table>
';


echo '
	<br><br>
	<h3 align="center">'.$LANG['MISC']['deadlines'].'</h3>
';

$deadlines = get_deadlines($spill_id);
if (!$deadlines) {
	echo '
		<h4 align="center">'.$LANG['MISC']['none'].'</h4>
	';
} else {
	echo '
		<table align="center" cellspacing="0">
			<tr class="highlight">
				<td>'.$LANG['MISC']['action'].'</td>
				<td>'.$LANG['MISC']['deadline'].'</td>
				<td colspan="2">&nbsp;</td>
			</tr>
	';
	foreach ($deadlines as $deadline) {
		echo '
			<tr>
				<td><strong>'.$deadline['tekst'].'</strong></td>
				<td>'.ucfirst(strftime($config['long_dateformat'], $deadline['deadline'])).'</td>
		';
		if (is_koordinator()) {
			echo '
				<td class="nospace"><button onClick="javascript:window.location=\'./editdeadline.php?deadline_id='.$deadline['deadline_id'].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['edit'].'</button></td>
				<td class="nospace"><button class="red" onClick="javascript:return confirmDelete(\''.addslashes($deadline['tekst']).' ('.$LANG['MISC']['deadline'].')\', \'./visspill.php?spill_id='.$spill_id.'&amp;slett_deadline='.$deadline['deadline_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
			';
		} else {
			echo '
				<td class="nospace">&nbsp;</td>
				<td class="nospace">&nbsp;</td>
			';
		}
		echo '
			</tr>
		';
	}
	echo '
		</table>
	';
}
	if (is_koordinator()) {
		echo '
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button onClick="javascript:window.location=\'./editdeadline.php?spill_id='.$spill_id.'&amp;ny_deadline=yes\';">'.$LANG['MISC']['create_deadline'].'</button>
			</tr>
		</table>
		';
	}



if (is_array(get_paameldinger($spill_id))) {
	$antall_paameldinger = count(get_paameldinger($spill_id));
} else {
	$antall_paameldinger = 0;
}

if (is_array(get_roller($spill_id))) {
	$antall_roller = count(get_roller($spill_id));
} else {
	$antall_roller = 0;
}

if (is_array(get_roller_konsept($spill_id))) {
	$antall_rollekonsept = count(get_roller_konsept($spill_id));
} else {
	$antall_rollekonsept = 0;
}

if (is_array(get_roller_forslag($spill_id))) {
	$antall_rolleforslag = count(get_roller_forslag($spill_id));
} else {
	$antall_rolleforslag = 0;
}
if (is_array(get_grupper($spill_id))) {
	$antall_grupper = count(get_grupper($spill_id));
} else {
	$antall_grupper = 0;
}
if (is_array(get_spillplott($spill_id))) {
	$antall_plott = count(get_spillplott($spill_id));
} else {
	$antall_plott = 0;
}

echo '
	<br>
	<br>
	<br>
	<h3 align="center">'.$LANG['MISC']['gamedata'].'</h3>
	<table align="center">
		<tr>
			<td><a href="./paameldinger.php?spill_id='.$spill_id.'">'.$LANG['MISC']['registrations'].'</a></td>
			<td><strong>'.$antall_paameldinger.'</strong></td>
		</tr>
		<tr>
			<td><a href="./roller.php?spill_id='.$spill_id.'">'.$LANG['MISC']['characters'].'</a></td>
			<td><strong>'.$antall_roller.'</strong></td>
		</tr>
';
	if ($spillinfo['rollekonsept']) {
	echo '
		<tr>
			<td><a href="./rollekonsept.php?spill_id='.$spill_id.'">'.$LANG['MISC']['character_concepts'].'</a></td>
			<td><strong>'.$antall_rollekonsept.'</strong></td>
		</tr>
	';
	}
echo '
		<tr>
			<td><a href="./rolleforslag.php?spill_id='.$spill_id.'">'.$LANG['MISC']['character_suggestions'].'</a></td>
			<td><strong>'.$antall_rolleforslag.'</strong></td>
		</tr>
		<tr>
			<td><a href="./grupper.php?spill_id='.$spill_id.'">'.$LANG['MISC']['groups'].'</a></td>
			<td><strong>'.$antall_grupper.'</strong></td>
		</tr>
		<tr>
			<td><a href="./plott.php?spill_id='.$spill_id.'">'.$LANG['MISC']['plots'].'</a></td>
			<td><strong>'.$antall_plott.'</strong></td>
		</tr>
	</table>
';
include('footer.php');
?>
