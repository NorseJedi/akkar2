<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              viskontakt.php                             #
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
	oppdater_kontaktinfo();
	$_SESSION['message'] .= $LANG['MESSAGE']['contact_updated'];
	header('Location: ./viskontakt.php?kontakt_id='.$_POST['kontakt_id'].'&spill_id='.$spill_id);
	exit();
} elseif ($_POST['ny_kontakt']) {
	$kontakt_id = opprett_kontakt();
	$_SESSION['message'] .= $LANG['MESSAGE']['contact_created'];
	header('Location: ./viskontakt.php?kontakt_id='.$kontakt_id.'&spill_id='.$spill_id);
	exit();
}

include('header.php');
$kontakt_id = $_GET['kontakt_id'];


$kontakt = get_kontakt($kontakt_id);
$kontakt['bilde'] = mugshot($kontakt);

$buttons = '
<table align="center">
		<tr>
			<td align="right"><button onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
			<td align="right"><button onClick="javascript:window.location=\'./kontakter.php\';">'.$LANG['MISC']['contacts'].'</button></td>
			<td align="center"><button class="red" onClick="javascript:confirmDelete(\''.addslashes($kontakt['navn'].' ('.$LANG['MISC']['contact'].')').'\', \'./kontakter.php?slett_kontakt='.$kontakt['kontakt_id'].'&amp;spill_id='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
			<td align="left"><button onClick="javascript:window.location=\'editkontakt.php?kontakt_id='.$kontakt['kontakt_id'].'&amp;spill_id='.$spill_id.'&amp;whereiwas='.basename($_SERVER['PHP_SELF']).'\';">'.$LANG['MISC']['edit'].'</button></td>
		</tr>
	</table>
';
echo '
	<h2 align="center">'.$LANG['MISC']['contactsheet'].'</h2>
	<h3 align="center">'.$kontakt['navn'].'</h3>
	<br>
	<table border="0" align="center" width="50%">
		<tr>
			<td rowspan="8"><img class="foto" src="'.$kontakt['bilde'].'" height="150" width="120" alt="'.$kontakt['navn'].'"></td>
			<td><strong>'.$LANG['MISC']['contact_person'].'</strong></td>
			<td>'.$kontakt['kontaktperson'].'</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['address'].'</strong></td>
			<td>'.$kontakt['adresse'].'</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['zipcode'].'/'.$LANG['MISC']['region'].'</strong></td>
			<td>'.$kontakt['postnr'].' '.$kontakt['poststed'].'</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['telephone'].'</strong></td>
			<td>'; if (!$kontakt['telefon']) { echo $LANG['MISC']['none']; } else { echo $kontakt['telefon']; } echo '</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['fax'].'</strong></td>
			<td>'; if (!$kontakt['fax']) { echo $LANG['MISC']['none']; } else { echo $kontakt['fax']; } echo '</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['cellphone'].'</strong></td>
			<td>'; if (!$kontakt['mobil']) { echo $LANG['MISC']['none']; } else { echo $kontakt['mobil']; } echo '</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['email'].'</strong></td>
			<td>'; if ($kontakt['email'] == '') { echo $LANG['MISC']['none']; } else { echo '<a href="mailto:'.$kontakt['email'].'">'.$kontakt['email'].'</a>'; } echo '</td>
		</tr>
		<tr>
			<td><strong>'.$LANG['MISC']['website'].'</strong></td>
			<td>'; if ($kontakt['webside'] == '') { echo $LANG['MISC']['none']; } else { echo '<a href="'.$kontakt['webside'].'" target="_blank">'.$kontakt['webside'].'</a>'; } echo '</td>
		</tr>
		<tr>
			<td colspan="3"><strong>'.$LANG['MISC']['description'].'</strong></td>
		</tr>
		<tr>
			<td colspan="3">'; if ($kontakt['beskrivelse'] == '') { echo $LANG['MISC']['none']; } else { echo nl2br(stripslashes($kontakt['beskrivelse'])); } echo '</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3"><strong>'.$LANG['MISC']['notes'].'</strong></td>
		</tr>
		<tr>
			<td colspan="3">'; if ($kontakt['notater'] == '') { echo $LANG['MISC']['none']; } else { echo nl2br(stripslashes($kontakt['notater'])); } echo '</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
	</table>
	'.$buttons.'
';
include('footer.php');
?>
