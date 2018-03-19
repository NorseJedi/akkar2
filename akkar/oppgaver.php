<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               oppgaver.php                              #
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

if ($_POST['opprett']) {
	opprett_oppgave();
	$_SESSION['message'] = $LANG['MESSAGE']['task_created'];
	header('Location: ./oppgaver.php?vis='.$_POST['vis']);
	exit();
} elseif ($_GET['slett']) {
	slett_oppgave();
	$_SESSION['message'] = $LANG['MESSAGE']['task_deleted'];
	header('Location: ./oppgaver.php?vis='.$_GET['vis']);
	exit();
} elseif ($_GET['rekvirer']) {
	tildel_oppgave($_GET['rekvirer'], $_SESSION['person_id']);
	$_SESSION['message'] = $LANG['MESSAGE']['task_aquired'];
	header('Location: ./oppgaver.php?vis='.$_GET['vis']);
	exit();
} elseif ($_GET['frigi']) {
	tildel_oppgave($_GET['frigi'], 0);
	$_SESSION['message'] = $LANG['MESSAGE']['task_released'];
	header('Location: ./oppgaver.php?vis='.$_GET['vis']);
	exit();
} elseif ($_POST['deleger_til']) {
	tildel_oppgave($_POST['oppgave_id'], $_POST['deleger_til']);
	$_SESSION['message'] = $LANG['MESSAGE']['task_assigned'];
	header('Location: ./oppgaver.php?vis='.$_POST['vis']);
	exit();
} elseif ($_POST['edited']) {
	oppdater_oppgave();
	$_SESSION['message'] = $LANG['MESSAGE']['task_updated'];
	header('Location: ./oppgaver.php?vis='.$_POST['vis']);
	exit();
} elseif ($_POST['utfort']) {
	fullfor_oppgave();
	$_SESSION['message'] = $LANG['MESSAGE']['task_complete'];
	header('Location: ./oppgaver.php?vis='.$_POST['vis']);
	exit();
}


$hjelpemne = $_REQUEST['vis'];
include('header.php');

echo '
	<h2 align="center">'.$LANG['MISC']['tasks'].'</h2>
';

$buttons = '
	<table align="center" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
			<td><button type="button" onClick="javascript:window.location=\'./oppgaver.php?vis=alle\';">'.$LANG['MISC']['all_active'].'</button></td>
			<td><button type="button" onClick="javascript:window.location=\'./oppgaver.php?vis=utforte\';">'.$LANG['MISC']['all_completed'].'</button></td>
			<td><button type="button" onClick="javascript:window.location=\'./oppgaver.php?vis=mine\';">'.$LANG['MISC']['my_active'].'</button></td>
			<td><button type="button" onClick="javascript:window.location=\'./oppgaver.php?vis=mine_utforte\';">'.$LANG['MISC']['my_completed'].'</button></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" align="center"><button type="button" onClick="javascript:window.location=\'./editoppgave.php?action=opprett\';">'.$LANG['MISC']['create_task'].'</button></td>
		</tr>
	</table>
';
if ($_GET['vis']) {
	$vis = $_GET['vis'];
}
switch ($vis) {
	case 'utforte':
		echo '
			<h3 align="center">'.$LANG['MISC']['all_completed'].'</h3>
			<br>
		';
		$oppgaver = get_utforte_oppgaver();
		break;
	case 'mine':
		echo '
			<h3 align="center">'.$LANG['MISC']['my_active'].'</h3>
			<br>
		';
		$oppgaver = get_mine_oppgaver();
		break;
	case 'mine_utforte':
		echo '
			<h3 align="center">'.$LANG['MISC']['my_completed'].'</h3>
			<br>
		';
		$oppgaver = get_mine_utforte_oppgaver();
		break;
	default:
		echo '
			<h3 align="center">'.$LANG['MISC']['all_active'].'</h3>
			<br>
		';
		$oppgaver = get_oppgaver();
}
echo $buttons.'<br>';

include('print_oppgaver.php');
include('footer.php');
?>
