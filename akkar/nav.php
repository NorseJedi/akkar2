<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                  nav.php                                #
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
if (!defined('IN_AKKAR')) {
	exit('Access violation.');
}
if (is_logged_in()) {
	// Only do this if we've got a valid logged in user
	echo '
		<table class="navtable" cellspacing="0">
	';
	if (browsertype() == 'ie') {
	// Need an additional table for spacing if client is IE (or the navbar will look ugly)
		echo '
			<tr><td>
			<table style="text-align:center;">
			<tr>
			<td>
		';
	}
	// The links below are marked if they are the current page - maybe JavaScript will do this stuff sometime in the future
	echo '
		<tr>
			<td class="navheader" nowrap>
				<a class="nav" href="userinfo.php">'.$LANG['MISC']['home'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
			<form action="./userinfo.php" method="post" name="logout" style="padding: 0; margin: 0;">
			<input type="hidden" name="logout" value="yes">
			<a class="nav" href="javascript:document.logout.submit();">'.$LANG['MISC']['logout'].'</a>
			</form>
			</td>
		</tr>
		<tr>
			<td class="navend">
				&nbsp;
			</td>
		</tr>
		<tr>
			<td class="navheader">
				<a class="nav" href="" onClick="javascript:showhide(\'nav_general\');return false;">'.$LANG['MISC']['general'].'</a>
			</td>
		</tr>
		<tbody id="nav_general">
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'oppgaver.php') { echo 'active'; } echo 'nav" href="oppgaver.php">'.$LANG['MISC']['tasks'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'spill.php') { echo 'active'; } echo 'nav" href="spill.php">'.$LANG['MISC']['games'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'kalender.php') { echo 'active'; } echo 'nav" href="kalender.php">'.$LANG['MISC']['calendar'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'filsystem.php') { echo 'active'; } echo 'nav" href="filsystem.php">'.$LANG['MISC']['filesystem'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'arrangorer.php') { echo 'active'; } echo 'nav" href="arrangorer.php">'.$LANG['MISC']['organizers'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'spillere.php') { echo 'active'; } echo 'nav" href="spillere.php">'.$LANG['MISC']['players'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'kontakter.php') { echo 'active'; } echo 'nav" href="kontakter.php">'.$LANG['MISC']['contacts'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if ((basename($_SERVER['PHP_SELF']) == 'roller.php') && ($spill_id == 0)) { echo 'active'; } echo 'nav" href="roller.php?spill_id=0">'.$LANG['MISC']['characters'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="'; if (basename($_SERVER['PHP_SELF']) == 'historikk.php') { echo 'active'; } echo 'nav" href="historikk.php">'.$LANG['MISC']['history'].'</a>
			</td>
		</tr>
	';
	if ($config['publicforum']) {
		// Link to the public forum if it's defined
		echo '
		<tr>
			<td class="naventry" nowrap>
				<a class="nav" href="'.$config['publicforum'].'" target="_blank">'.$LANG['MISC']['publicforum'].'</a>
			</td>
		</tr>
		';
	}
	if ($config['secretforum']) {
		// Link to the private forum if it's defined
		echo '
			<tr>
				<td class="naventry" nowrap>
					<a class="nav" href="'.$config['secretforum'].'" target="_blank">'.$LANG['MISC']['privateforum'].'</a>
				</td>
			</tr>
		';
	}
	echo '
		</tbody>
		<tr>
			<td class="navend">
				&nbsp;
			</td>
		</tr>
	';
	// Get the currently active games
	$allespill = get_aktive_spill('start DESC');
	if ($allespill) {
		// If we have any active games we'll build navigations for them all
		foreach ($allespill as $spill) {
			echo '
				<tr>
					<td class="navheader" nowrap>
						<a class="'; if($spill_id == $spill['spill_id']) { echo 'active'; } echo'nav" href="./visspill.php?spill_id='.$spill['spill_id'].'">'.substr($spill['navn'], 0, 20); if (strlen($spill['navn']) > 20) { echo '...'; } echo '</a>
					</td>
				</tr>
			';
			if ($spill_id == $spill['spill_id']) {
				echo '
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if ((basename($_SERVER['PHP_SELF']) == 'filvedlegg.php') && ($_REQUEST['vedlagt'] == 'spill')) { echo 'active'; } echo'nav" href="filvedlegg.php?spill_id='.$spill['spill_id'].'&amp;vedlagt=spill">'.$LANG['MISC']['game_attachments'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'paameldinger.php') { echo 'active'; } echo'nav" href="paameldinger.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['registrations'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'roller.php') { echo 'active'; } echo'nav" href="roller.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['characters'].'</a>
						</td>
					</tr>
				';
				if ($spill['rollekonsept']) {
					// Only link to Character Concepts if the game actually uses that sybsystem
					echo '
						<tr>
							<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'rollekonsept.php') { echo 'active'; } echo'nav" href="rollekonsept.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['character_concepts'].'</a>
							</td>
						</tr>
					';
				}
				echo '
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'plott.php') { echo 'active'; } echo'nav" href="plott.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['plots'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'hentroller.php') { echo 'active'; } echo'nav" href="hentroller.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['character_transfer'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'rollefordeling.php') { echo 'active'; } echo'nav" href="rollefordeling.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['character_assignment'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'grupper.php') { echo 'active'; } echo'nav" href="grupper.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['groups'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'rolleforslag.php') { echo 'active'; } echo'nav" href="rolleforslag.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['character_suggestions'].'</a>
						</td>
					</tr>
					<tr>
						<td class="naventry" nowrap>
							<a class="'; if (basename($_SERVER['PHP_SELF']) == 'betaling.php') { echo 'active'; } echo'nav" href="betaling.php?spill_id='.$spill['spill_id'].'">'.$LANG['MISC']['payments'].'</a>
						</td>
					</tr>
				';
				if (is_admin()) {
					// Give administrators the links to make HTML forms for outside submissions
					echo '
						<tr>
							<td class="naventry"><hr></td>
						</tr>
						<tr>
							<td class="naventry" nowrap>
								<a class="'; if (basename($_SERVER['PHP_SELF']) == 'mkrolleskjema.php') { echo 'active'; } echo'nav" href="mkrolleskjema.php?spill_id='.$spill['spill_id'].'&amp;nozip=yes">'.$LANG['MISC']['character_form'].'</a>
							</td>
						</tr>
						<tr>
							<td class="naventry" nowrap>
								<a class="'; if (basename($_SERVER['PHP_SELF']) == 'mkpaameldingskjema.php') { echo 'active'; } echo'nav" href="mkpaameldingskjema.php?spill_id='.$spill['spill_id'].'&amp;nozip=yes">'.$LANG['MISC']['registration_form'].'</a>
							</td>
						</tr>
						<tr>
							<td class="naventry" nowrap>
								<a class="'; if (basename($_SERVER['PHP_SELF']) == 'mkkombiskjema.php') { echo 'active'; } echo'nav" href="mkkombiskjema.php?spill_id='.$spill['spill_id'].'&amp;nozip=yes">'.$LANG['MISC']['combi_form'].'</a>
							</td>
						</tr>
					';
				}
			}
			echo '
				<tr>
					<td class="navend">
						&nbsp;
					</td>
				</tr>
			';
		}
	}
	if (is_file('websitenav.php')) {
		// Include the custom website nav-file if it exists (Skyggenes Dal uses one such file to link to editable pages on their main website - you can too ;)
		include_once('websitenav.php');
		$websitenav_included = true;
	}
	if (is_admin()) {
		// Add the administrator navigation components (configuration, templates, backup, etc)
		echo '
			<tr>
				<td class="navheader">
					<a class="nav" href="" onClick="javascript:showhide(\'nav_admin\');return false;">'.$LANG['MISC']['administration'].'</a>
				</td>
			</tr>
			<tbody id="nav_admin">
			<tr>
				<td class="naventry" nowrap>
					<a class="'; if (basename($_SERVER['PHP_SELF']) == 'konfigurasjon.php') { echo 'active'; } echo'nav" href="konfigurasjon.php">'.$LANG['MISC']['configuration'].'</a>
				</td>
			</tr>
			<tr>
				<td class="naventry" nowrap>
					<a class="'; if (basename($_SERVER['PHP_SELF']) == 'maler.php') { echo 'active'; } echo'nav" href="maler.php">'.$LANG['MISC']['templates'].'</a>
				</td>
			</tr>
			<tr>
				<td class="naventry" nowrap>
					<a class="'; if (basename($_SERVER['PHP_SELF']) == 'dbbackup.php') { echo 'active'; } echo'nav" href="dbbackup.php">'.$LANG['MISC']['backup'].'</a>
				</td>
			</tr>
			<tr>
				<td class="naventry" nowrap>
					<a class="'; if (basename($_SERVER['PHP_SELF']) == 'dbrestore.php') { echo 'active'; } echo'nav" href="dbrestore.php">'.$LANG['MISC']['restore'].'</a>
				</td>
			</tr>
			<tr>
				<td class="naventry" nowrap>
					<a class="'; if (basename($_SERVER['PHP_SELF']) == 'selvtest.php') { echo 'active'; } echo'nav" href="selvtest.php">'.$LANG['MISC']['selftest'].'</a>
				</td>
			</tr>
			</tbody>
			<tr>
				<td class="navend">
					&nbsp;
				</td>
			</tr>
		';
	}
	// Show the info-section - who the client is logged in as, and who else are logged in (if any)
	echo '
		<tr>
			<td class="navheader">'.$LANG['MISC']['info'].'</td>
		</tr>
		<tr>
			<td class="naventry">'.$LANG['MISC']['logged_on_as'].'</td>
		</tr>
		<tr>
			<td class="naventry"><a class="nav" href="./visperson.php?person_id='.$_SESSION['person_id'].'&amp;spill_id='.$spill_id.'">'.$_SESSION['navn'].'</a></td>
		</tr>
		<tr>
			<td class="naventry">&nbsp;</td>
		</tr>
		<tr>
			<td class="naventry">'.$LANG['MISC']['others_logged_on'].'</td>
		</tr>
		<tr>
			<td class="naventry">
	';
	if ($logged_on = logged_on_now()) {
		// List each other logged in user
		foreach ($logged_on as $bruker) {
			echo '<a class="nav" href="./visperson.php?person_id='.$bruker['person_id'].'&amp;spill_id='.$spill_id.'">'.$bruker['navn'].'</a><br>';
		}
	} else {
		echo $LANG['MISC']['nobody'];
	}
	echo '
			</td>
		</tr>
		<tr>
			<td class="navend">
				&nbsp;
			</td>
		</tr>
	';
	if (browsertype() == 'ie') {
		// Close the IE specific additional table
		echo '
			</td>
			</tr>
			</table>
			</td></tr>
		';
	}
	echo '
		</table>

	';
} else {
	// If client isn't a logged in user we don't show a hell of a lot of links, only the link to this page (the header) and to the link to the groups main website
	echo '
		<table class="navtable" cellspacing="0">
	';
	if (browsertype() == 'ie') {
		echo '
			<tr><td>
			<table style="text-align:center;">
			<tr>
			<td>
		';
	}
	echo '
		<tr>
			<td class="navheader" nowrap>
				<a class="nav" href="./">'.$config['arrgruppenavn'].'</a>
			</td>
		</tr>
		<tr>
			<td class="naventry" nowrap>
				<a class="nav" href="'.$config['arrgruppeurl'].'">'.$LANG['MISC']['homepage'].'</a>
			</td>
		</tr>
		<tr>
			<td class="navend" nowrap>
				&nbsp;
			</td>
		</tr>
	';
	if (browsertype() == 'ie') {
		echo '
			</td>
			</tr>
			</table>
			</td></tr>
		';
	}
	echo '
		</table>
	';
}
?>
