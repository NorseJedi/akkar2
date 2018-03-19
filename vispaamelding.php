<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             vispaamelding.php                           #
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
	oppdater_paamelding();
	$_SESSION['message'] .= $LANG['MESSAGE']['registration_updated'];
	header('Location: ./vispaamelding.php?person_id='.$_POST['person_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_POST['ny_paamelding']) {
	opprett_paamelding();
	$_SESSION['message'] .= $LANG['MESSAGE']['registration_created'];
	header('Location: ./vispaamelding.php?person_id='.$person_id.'&spill_id='.$_POST['spill_id']);
	exit();
}

include('header.php');

$person_id = $_GET['person_id'];

echo person_sheet($_GET['person_id']);
echo registration_sheet($_GET['person_id'], $_GET['spill_id']);



include('footer.php');
?>
