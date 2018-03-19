<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               visrolle.php                              #
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
if ($_POST['edited']) {
	oppdater_rolle();
	$_SESSION['message'] = $LANG['MESSAGE']['character_updated'];
	header('Location: ./visrolle.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_POST['ny']) {
	$rolle_id = opprett_rolle();
	$_SESSION['message'] = $LANG['MESSAGE']['character_created'];
	header('Location: ./visrolle.php?rolle_id='.$rolle_id.'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_GET['overfor_forslag']) {
	$rolle_id = overfor_rolleforslag($_GET['rolle_id'], $_GET['spill_id']);
	$_SESSION['message'] = $LANG['MESSAGE']['character_suggestion_approved'];
	header('Location: ./visrolle.php?rolle_id='.$rolle_id.'&spill_id='.$_GET['spill_id']);
	exit();
} elseif ($_POST['deaktiviser_rolle']) {
	deaktiviser_rolle($_POST['deaktiviser_rolle'], $_POST['spill_id'], $_POST['status_tekst']);
	$_SESSION['message'] = $LANG['MESSAGE']['character_deactivated'];
	header('Location: ./visrolle.php?rolle_id='.$_POST['deaktiviser_rolle'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_GET['reaktiviser_rolle']) {
	reaktiviser_rolle($_GET['reaktiviser_rolle'], $_GET['spill_id']);
	$_SESSION['message'] = $LANG['MESSAGE']['character_reactivated'];
	header('Location: ./visrolle.php?rolle_id='.$_GET['reaktiviser_rolle'].'&spill_id='.$_GET['spill_id']);
	exit();
} elseif ($_POST['oppdater_status']) {
	oppdater_rollestatus($_POST['rolle_id'], $spill_id, $_POST['status_tekst']);
	$_SESSION['message'] = $LANG['MESSAGE']['deactivation_updated'];
	header('Location: ./visrolle.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id']);
	exit();
}

$rolle_id = $_REQUEST['rolle_id'];

if (!$spill_id && !$rolle_id) {
	exit($LANG['ERROR']['no_char_or_game_selected']);
}
include('header.php');

echo character_sheet($rolle_id, $spill_id);

include('footer.php');
?>