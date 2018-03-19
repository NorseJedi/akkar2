<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                install.php                              #
#                            -------------------                          #
#                                                                         #
#   copyright (C) 2000-2006 Roy W. Andersen                               #
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

# Set the error-reporting to ignore notices. This is default on *nix installations of PHP, but apparently not in Windows...
error_reporting(E_ALL ^ E_NOTICE);

include('scripts/functions.php');
session_start();
if (file_exists('conf/baseconf.php')) {
	# Okay - we have a config file.
	if (file_exists('upgrade.php')) {
		# Okay, we also have an upgrade-file. Let's go there instead.
		header('Location: upgrade.php');
	} else {
		# Well shoot - we've got a config file, and we must have already upgraded. Guess we weren't able to delete ourselves when the process completed. This calls for some manual intervention :)
		echo '
			<h3 align="center">You have to delete <em>install.php</em> from the AKKAR directory before the system will operate.</h3>
			<br />
			<br />
			<button type="button" onclick="javascript:window.location=\'./\';">Done</button>
		';
	}
	exit();
}
# Provide the config file if AKKAR was unable to create it and have the user download it
if ($_REQUEST['get_config']) {
	header("Content-type: text/plain");
	header("Content-disposition: attachment; filename=baseconf.php");
	echo $_SESSION['baseconf'];
	exit();
}

# We need to generate a calendar-thingy here - just for the first users birthdate.
for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mnder = array(1=>"jan", 2=>"feb", 3=>"mar", 4=>"apr", 5=>"may", 6=>"jun", 7=>"jul", 8=>"aug", 9=>"sep", 10=>"oct", 11=>"nov", 12=>"dec");
for ($i = date("Y"); $i >= date("Y")-99; $i--) {
	$aarliste[$i] = $i;
}

# The current version we're installing
$akkar_version = '2.4.6';

echo "
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html40/loose.dtd\">
<html>
<head>
	<title>AKKAR Installation</title>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">

";
if ((browsertype() == "ie") && (is_file("styles/default/iestyle.css"))) {
	echo "<link rel=\"StyleSheet\" href=\"styles/default/iestyle.css\" type=\"text/css\">";
} elseif ((browsertype() == "opera") && (is_file("styles/default/operastyle.css"))) {
	echo "<link rel=\"StyleSheet\" href=\"styles/default/operastyle.css\" type=\"text/css\">";
} else {
	echo "<link rel=\"StyleSheet\" href=\"styles/default/style.css\" type=\"text/css\">";
}

echo '
<link rel="StyleSheet" href="styles/default/common.css" type="text/css">
<link rel="icon" href="images/favicon.png" type="image/png">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

<script language="JavaScript" type="text/javascript">
	// Set the browsertype variable for the needy javascripts
	var browsertype = \''.browsertype().'\';
	
	// Form-validation for the... uh... form :)
	function validate(thisform) {
		if (thisform.sqluser.value == \'\') {
			window.alert(\'MySQL username is missing\');
			thisform.sqluser.focus();
			return false;
		}
		if (thisform.sqlpassword.value == \'\') {
			window.alert(\'MySQL password is missing\');
			thisform.sqlpassword.focus();
			return false;
		}
		if (thisform.sqlbase.value == \'\') {
			window.alert(\'MySQL Database is missing\');
			thisform.sqlbase.focus();
			return false;
		}
		if (thisform.brukernavn.value == \'\') {
			window.alert(\'Username is missing\');
			thisform.brukernavn.focus();
			return false;
		}
		if (thisform.passord.value == \'\') {
			window.alert(\'Password is missing\');
			thisform.passord.focus();
			return false;
		}
		if (thisform.passord.value != thisform.passord2.value) {
			window.alert(\'Passord confirmation does not match password\');
			thisform.passord2.focus();
			return false;
		}
		if (thisform.fornavn.value == \'\') {
			window.alert(\'You must enter your first name\');
			thisform.fornavn.focus();
			return false;
		}
		if (thisform.etternavn.value == \'\') {
			window.alert(\'You must enter your surname\');
			thisform.etternavn.focus();
			return false;
		}
		if ((thisform.dag.value == \'\') || (thisform.mnd.value == \'\') || (thisform.aar.value == \'\')) {
			window.alert(\'Date of birth is missing\');
			thisform.dag.focus();
			return false;
		}
		if (!validDate(thisform.dag.value, thisform.mnd.value, thisform.aar.value)) {
			window.alert(\'The date of birth you entered is an invalid date.\');
			return false;
		}
		if (document.getElementById(\'kjonn\').value == \'\') {
			window.alert(\'You must select your gender.\');
			document.getElementById(\'kjonn\').focus();
			return false;
		}
		if (thisform.adresse.value == \'\') {
			window.alert(\'Address is missing\');
			thisform.adresse.focus();
			return false;
		}
		if (thisform.postnr.value == \'\') {
			window.alert(\'Zipcode is missing\');
			thisform.postnr.focus();
			return false;
		}
		if (thisform.poststed.value == \'\') {
			window.alert(\'Region is missing\');
			thisform.poststed.focus();
			return false;
		}
		return true;
	}
</script>
<script language="JavaScript" type="text/javascript" src="scripts/functions.js"></script>
</head>
<body>
<table class="main" border="0">
	<tr>
		<td class="banner">
			<img src="styles/default/logo.png" alt="AKKAR">
		</td>
	</tr>
	<tr>
		<td class="maincol">

		<h1 align="center">AKKAR '.$akkar_version.'</h1>
		<h2 align="center">Installation</h2>
		<br>
';
if (!$_POST['do_install']) {
	# First load - make the installation-form
	echo '
		<table width="60%" align="center">
			<tr>
				<td>
		<h4>This script will create all tables and an admin-user so you\'ll be able to log on as soon as it\'s completed.
		<br>
		<br>
		You\'ll need a MySQL server, a database and a user-account on that server with all access-rights to the database. If you\'re missing any of these, AKKAR can not be installed.
		<br>
		<br>
		Alle fields are mandatory unless stated otherwise.</h4>
				</td>
			</tr>
		</table>
		<form action="install.php" method="post" name="installform" enctype="multipart/form-data">
		<input type="hidden" name="do_install" value="yes">
		<table align="center">
			<tr class="highlight">
				<td colspan="2">Database</td>
			</tr
			<tr>
				<td>MySQL-server hostname</td><td><input type="text" name="sqlhost" value="localhost"></td>
			</tr>
			<tr>
				<td>Name of the database</td><td><input type="text" name="sqlbase" value="akkar"></td>
			</tr>
			<tr>
				<td>MySQL username</td><td><input type="text" name="sqluser"></td>
			</tr>
			<tr>
				<td>MySQL password</td><td><input type="password" name="sqlpassword"></td>
			</tr>
			<tr>
				<td>Table prefix</td><td><input type="text" name="table_prefix" value="akkar_"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			
			<tr class="highlight">
				<td colspan="2">Language Selection</td>
			</tr
			<tr>
				<td>Language</td>
				<td>
				<select name="lang">
					<option value="eng">English</option>
					<option value="nor">Norwegian (Bokmål)</option>
				</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr class="highlight">
				<td colspan="2">AKKAR Administrator</td>
			<tr>
			<tr>
				<td>Username </td><td><input type="text" name="brukernavn"></td>
			</tr>
			<tr>
				<td>Password </td><td><input type="password" name="passord"></td>
			</tr>
			<tr>
				<td>Confirm password </td><td><input type="password" name="passord2"></td>
			</tr>
			<tr>
				<td class="nospace" colspan="2"><hr></td>
			</tr>
			<tr>
				<td>First name <span class="small">(and middle name)</span></td><td><input type="text" name="fornavn"></td>
			</tr>
			<tr>
				<td>Surname </td><td><input type="text" name="etternavn"></td>
			</tr>
			<tr>
				<td>Birthdate</td>
				<td nowrap><select id="dag" name="dag"><option value="" class="selectname">Day</option>'.print_liste($dager, $dag).'</select> <select id="mnd" name="mnd"><option value="" class="selectname">Month</option>'.print_liste($mnder, $mnd).'</select> <select id="aar" name="aar"><option value="" class="selectname">Year</option>'.print_liste($aarliste, $aar).'</select></td>
			</tr>
			<tr>
				<td>Gender</td>
				<td nowrap>
				<select id="kjonn" name="kjonn">
					<option value="" class="selectname">Select</option>
					<option value="han">Male</option>
					<option value="hun">Female</option>
				</select>
				</td>
			</tr>
			<tr>
				<td>Address </td><td><input type="text" name="adresse"></td>
			</tr>
			<tr>
				<td>Zip/Region </td><td><input type="text" size="4" name="postnr"><input type="text" size="15" name="poststed"></td>
			</tr>
			<tr>
				<td>E-mail <span class="small">(optional)</span></td><td><input type="text" name="email"></td>
			</tr>
			<tr>
				<td>Phone <span class="small">(optional)</span></td><td><input type="text" name="telefon"></td>
			</tr>
			<tr>
				<td>Cellphone <span class="small">(optional)</span></td><td><input type="text" name="mobil"></td>
			</tr>
			<tr class="highlight">
				<td colspan="2">Options</td>
			<tr>
			<tr>
				<td><strong>Add norwegian zipcode-table</strong></td><td><input type="checkbox" name="nor_zipcodes"></td>
			</tr>
			<tr>
				<td class="small" colspan="2">'.nl2br(wordwrap('This will add a table with all zipcodes and regions for Norway, and this table will be used to automatically fill in the region based on the entered zipcode. For non-norwegian users this is of no use.')).'
				</td>
			</tr>
			<tr>
				<td class="nospace" colspan="2"><hr></td>
			</tr>
			<tr>
				<td><strong>Notify author</strong></td><td><input type="checkbox" name="notify_author"></td>
			</tr>
			<tr>
				<td class="small" colspan="2">'.nl2br(wordwrap('This will send an email to me (the author) notifying me about the installation. No information is sent other than the fact that AKKAR has been installed somewhere by someone (examine the last part of the sourcecode of this script to see how the actual Email is being sent). I\'m very curious to see how many are using/trying AKKAR and so I\'d love to be told of such things. If you\'d rather notify me personally instead of letting the install-script do it, you can email me at ensnared@gmail.com')).'</td>
			</tr>
			<tr>
				<td class="nospace" colspan="2"><hr></td>
			</tr>

		</table>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><button type="reset">Reset</button></td>
				<td><button type="submit" onClick="javascript:return validate(document.installform);">Install!</button></td>
			</tr>
		</table>
		</form>
		</td>
	</tr>
</table>
</body>
</html>
';
} else {
	# Form is submitted, time to get our hands dirty
	
	# First, connect to the db, or we won't get very far...
	$connection = mysql_connect($_POST['sqlhost'], $_POST['sqluser'], $_POST['sqlpassword']) or exit(mysql_error());
	
	# Select the database
	$sqlbase = $_POST['sqlbase'];
	mysql_select_db($sqlbase) or exit(mysql_error());

	# Could've used the $_POST entry, but it looks prettier this way
	$table_prefix = $_POST['table_prefix'];

	# The combination PHP/IIS tends to slow things down.
	this_might_take_a_while();
	
	# Create the users table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."brukere`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."brukere` (
		bruker_id int(10) unsigned NOT NULL auto_increment,
		person_id int(10) unsigned NOT NULL DEFAULT '0',
		brukernavn tinytext,
		passord tinytext,
		secret tinytext,
		level int(4) unsigned,
		nowlog int(10) unsigned,
		lastlog int(10) unsigned,
		fingerprint tinytext,
		locked int(1) unsigned DEFAULT '0',
		PRIMARY KEY (bruker_id,person_id)
	)", $connection) or exit(mysql_error());
	
	# Encrypt the password for the admin user and populate the users table with his/her login-info
	$passcrypt = md5($_POST['passord']);
	mysql_query("INSERT INTO `".$table_prefix."brukere` (bruker_id, person_id, brukernavn, passord, level) VALUES (1,1,'".$_POST['brukernavn']."','$passcrypt', '20')", $connection) or exit(mysql_error());

	# Create the config table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."config`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."config` (
		name varchar(255) NOT NULL,
		value text,
		PRIMARY KEY (name)
	)", $connection) or exit(mysql_error());
	
	# Populate the config table with default values
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('arrgruppenavn','AKKAR')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('arrgruppemail','".$_POST['email']."')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('arrgruppeurl','http://akkar.sourceforge.net/')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('publicforum','')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('secretforum','')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('akkar_admin_navn','".$_POST['fornavn']." ".$_POST['etternavn']."')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('akkar_admin_email','".$_POST['email']."')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('style','default')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('max_login_attempts',3)", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('lang','".$_POST['lang']."')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('autologout',600)", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('max_tmp_age','1 day')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('ckprefix','AKKAR')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('ckexpire','1 year')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('ckdir','/')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('filsystembane','filesystem')", $connection) or exit(mysql_error());
	
	# Different default mailtexts for different languages
	if ($_POST['lang'] == 'nor') {
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('defaultrollemailtekst','Hei.\r\n\r\nHer kommer din rolle til spillet [game]. Rollen og eventuelle vedlegg finner du vedlagt denne mailen. Alle vedlegg er komprimert med zip, så du må pakke dem ut med f.eks. WinZip eller liknende program.\r\n\r\nHar du spørsmål eller kommentarer så ta gjerne kontakt med ansvarlig arrangør for rollen din.\r\n\r\nTa også en titt på våre webside, [url], for eventuelle oppdateringer angående spillet.\r\n\r\nVi sees i Legoland!\r\n\r\n-- \r\n[org_group] <[email]>\r\n[url]')", $connection) or exit(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('defaultrollekonseptmailtekst','Hei.\r\n\r\nHer kommer ditt rollekonsept til spillet [game]. Eventuelle vedlegg er komprimert med zip, så du må pakke dem ut med f.eks. WinZip eller liknende program.\r\n\r\nHar du spørsmål eller kommentarer så ta gjerne kontakt snarest.\r\n\r\nTa også en titt på vår webside, [url], for eventuelle oppdateringer angående spillet.\r\n\r\nTittel: [title]\r\nBeskrivelse: [description]\r\n\r\nVi sees i Legoland!\r\n\r\n-- \r\n[org_group] <[email]>\r\n[url]')", $connection) or exit(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('motd','AKKAR er installert!\r\n\r\nDenne meldingen vil vises for alle som logger inn. Den kan fjernes eller endres ved å redigere MOTD på konfigurasjons-siden.')", $connection) or exit(mysql_error());
	} else {
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('defaultrollemailtekst','Hello.\r\n\r\nHere is your character for the upcoming LARP, [game]. The character and any companion-files are attached to this mail. They are all compressed in a zipfile, so you\'ll need WinZip or a similar program to unpack them.\r\n\r\nIf you have any questions or comments about your character, please contact the responsible organizer who is named on the character sheet.\r\n\r\nPlease visit out website, [url], for any updates about the game.\r\n\r\n\r\n-- \r\n[org_group] <[email]>\r\n[url]')", $connection) or exit(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('defaultrollekonseptmailtekst','Hello.\r\n\r\nHere is your character concept for the LARP [game]. Any additional files are attached to this email within a zip-archive, so you\'ll need WinZip or a similar program to extract them.\r\n\r\nIf you have any questions or comments, please do not hesitate to contact us.\r\n\r\nPlease visit out website, [url], for any updates about the game.\r\n\r\nYour concept is:\r\nTitle: [title]\r\nDescription: [description]\r\n\r\n\r\n-- \r\n[org_group] <[email]>\r\n[url]')", $connection) or exit(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('motd','AKKAR is installed!\r\n\r\nThis message will be shown to all users when they have logged in. To remove it or change it, edit the MOTD on the configuration-page.')", $connection) or exit(mysql_error());
	}
	
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('types_not_in_lists','a:4:{i:0;s:3:\"box\";i:1;s:9:\"inlinebox\";i:2;s:6:\"header\";i:3;s:9:\"separator\";}')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('fields_not_in_person_lists','a:5:{i:0;s:9:\"person_id\";i:1;s:4:\"type\";i:2;s:6:\"hensyn\";i:3;s:11:\"intern_info\";i:4;s:5:\"bilde\";}')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('fields_not_in_contacts_list', 'a:4:{i:0;s:10:\"kontakt_id\";i:1;s:11:\"beskrivelse\";i:2;s:7:\"notater\";i:3;s:5:\"bilde\";}')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('primary_exportformat','pdf')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('allow_exportformat_override','0')", $connection) or exit(mysql_error());
	if ($_POST['nor_zipcodes']) {
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('use_autoregion','1')", $connection) or exit(mysql_error());
	} else {
		mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('use_autoregion','0')", $connection) or exit(mysql_error());
	}
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('paperformat','A4')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('long_dateformat', '%A %d. %B %Y')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('medium_dateformat', '%d. %b %y')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('short_dateformat', '%d/%m-%y')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('version','".$akkar_version."')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('send_security_warning','0')", $connection) or exit(mysql_error());
	mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('use_overlib_fade','1')", $connection) or exit(mysql_error());

	# Create the deadlines table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."deadlines`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."deadlines` (
		deadline_id int(10) unsigned NOT NULL auto_increment,
		spill_id int(10) unsigned NOT NULL DEFAULT '0',
		tekst tinytext,
		deadline int(10) unsigned DEFAULT '0',
		PRIMARY KEY (deadline_id,spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the filesystem table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."filsystem`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."filsystem` (
		fil_id int(11) unsigned NOT NULL auto_increment,
		navn tinytext,
		dir tinytext,
		type tinytext,
		beskrivelse tinytext,
		oppdatert tinytext,
		PRIMARY KEY (fil_id)
	)", $connection) or exit(mysql_error());
	
	# Create the attachments table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."filvedlegg`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."filvedlegg` (
		fil_id int(11) unsigned NOT NULL DEFAULT '0',
		vedlegg_id int(11) unsigned NOT NULL DEFAULT '0',
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		vedlagt varchar(48) NOT NULL,
		PRIMARY KEY (fil_id,vedlegg_id,spill_id,vedlagt)
	)", $connection) or exit(mysql_error());
	
	# Create the group memberships table table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."gruppe_roller`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."gruppe_roller` (
		gruppe_id int(11) unsigned NOT NULL DEFAULT '0',
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		rolle_id int(11) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (gruppe_id,spill_id,rolle_id)
	)", $connection) or exit(mysql_error());
	
	# Create the groups table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."grupper`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."grupper` (
		gruppe_id int(11) unsigned NOT NULL auto_increment,
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		navn tinytext,
		beskrivelse text,
		medlemsinfo longtext,
		PRIMARY KEY (gruppe_id,spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the calendar table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."kalender`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."kalender` (
		notat_id int(10) unsigned NOT NULL auto_increment,
		person_id int(10) unsigned DEFAULT '0',
		juliandc tinytext,
		tekst text,
		PRIMARY KEY (notat_id)
	)", $connection) or exit(mysql_error());
	
	# Create the acquaintances table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."kjentfolk`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."kjentfolk` (
		rolle_id int(11) unsigned NOT NULL DEFAULT '0',
		kjent_rolle_id int(11) unsigned NOT NULL DEFAULT '0',
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		level int(1) DEFAULT '0',
		kjentgrunn tinytext,
		type varchar(255) NOT NULL,
		PRIMARY KEY (rolle_id,kjent_rolle_id,spill_id,type)
	)", $connection) or exit(mysql_error());
	
	# Create the contacts table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."kontakter`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."kontakter` (
		kontakt_id int(11) unsigned NOT NULL auto_increment,
		navn tinytext,
		kontaktperson tinytext,
		adresse tinytext,
		postnr tinytext,
		poststed tinytext,
		telefon tinytext,
		fax tinytext,
		mobil tinytext,
		email tinytext,
		webside tinytext,
		beskrivelse longtext,
		notater longtext,
		bilde tinytext,
		PRIMARY KEY (kontakt_id)
	)", $connection) or exit(mysql_error());

	# Create the mugshots table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."mugshots`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."mugshots` (
		person_id int(11) unsigned NOT NULL,
		image varchar(255) NOT NULL,
		type varchar(255),
		PRIMARY KEY (person_id, image)
	)", $connection) or exit(mysql_error());

	# Create the tasks/todo table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."oppgaver`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."oppgaver` (
		oppgave_id int(4) unsigned NOT NULL auto_increment,
		opprettet int(10) unsigned,
		opprettet_av int(4) unsigned,
		deadline int(10) unsigned,
		oppgavetekst longtext,
		utfores_av int(4) unsigned DEFAULT '0',
		utfort int(10) unsigned DEFAULT '0',
		resultat longtext,
		PRIMARY KEY (oppgave_id)
	)", $connection) or exit(mysql_error());
	
	# Create the registrations table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."paameldinger`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."paameldinger` (
		person_id int(11) unsigned NOT NULL DEFAULT '0',
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		paameldt int(10) unsigned,
		betalt int(1),
		annet text,
		PRIMARY KEY (person_id,spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the people table which will contain organisers and players
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."personer`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."personer` (
		person_id int(11) unsigned NOT NULL auto_increment,
		type tinytext,
		etternavn tinytext,
		fornavn tinytext,
		fodt date,
		alder int(1),
		kjonn tinytext,
		adresse tinytext,
		postnr tinytext,
		poststed tinytext,
		telefon tinytext,
		mobil tinytext,
		email tinytext,
		mailpref tinytext,
		hensyn longtext,
		intern_info longtext,
		bilde tinytext,
		PRIMARY KEY (person_id)
	)", $connection) or exit(mysql_error());

	# Populate the table with the administrators personalia
	$fodt = $_POST['aar']."-".$_POST['mnd']."-".$_POST['dag'];
	mysql_query("INSERT INTO `".$table_prefix."personer` (person_id, type, etternavn, fornavn, fodt, kjonn, adresse, postnr, poststed, email, telefon, mobil, mailpref) VALUES (1, 'arrangor', '".$_POST['etternavn']."', '".$_POST['fornavn']."', '$fodt', '".$_POST['kjonn']."', '".$_POST['adresse']."', '".$_POST['postnr']."', '".$_POST['poststed']."', '".$_POST['email']."', '".$_POST['telefon']."', '".$_POST['mobil']."', 'email')", $connection) or exit(mysql_error());

	# Create the plots table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."plott`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."plott` (
		plott_id int(11) unsigned NOT NULL auto_increment,
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		navn tinytext,
		beskrivelse longtext,
		oppdatert int(10) unsigned,
		PRIMARY KEY (plott_id,spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the plot relations table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."plott_medlemmer`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."plott_medlemmer` (
		plott_id int(11) unsigned NOT NULL DEFAULT '0',
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		medlem_id int(11) unsigned NOT NULL DEFAULT '0',
		type varchar(128) NOT NULL,
		tilknytning longtext,
		oppdatert int(10) unsigned,
		PRIMARY KEY (plott_id,spill_id,medlem_id,type)
	)", $connection) or exit(mysql_error());

	# Create the character suggestions table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."rolleforslag`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."rolleforslag` (
		rolle_id int(11) unsigned NOT NULL auto_increment,
		arrangor_id int(11) unsigned,
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		spiller tinytext,
		navn tinytext,
		intern_info longtext,
		beskrivelse1 longtext,
		beskrivelse2 longtext,
		beskrivelse3 longtext,
		beskrivelse_gruppe longtext,
		oppdatert int(10) unsigned,
		locked tinytext,
		godkjent tinytext,
		PRIMARY KEY (rolle_id,spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the character concepts table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."rollekonsept`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."rollekonsept` (
		konsept_id int(10) NOT NULL auto_increment,
		spill_id int(10) unsigned NOT NULL DEFAULT '0',
		rolle_id int(10) unsigned NOT NULL DEFAULT '0',
		arrangor_id int(10) unsigned,
		spiller_id int(10) unsigned,
		tittel tinytext,
		konsept longtext,
		oppdatert int(10) unsigned,
		PRIMARY KEY (konsept_id,spill_id,rolle_id)
	)", $connection) or exit(mysql_error());
	
	# Create the characters table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."roller`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."roller` (
		rolle_id int(11) unsigned NOT NULL auto_increment,
		arrangor_id int(11) unsigned,
		spill_id int(11) unsigned NOT NULL DEFAULT '0',
		spiller_id int(11) unsigned,
		navn tinytext,
		intern_info longtext,
		beskrivelse1 longtext,
		beskrivelse2 longtext,
		beskrivelse3 longtext,
		beskrivelse_gruppe longtext,
		bilde tinytext,
		oppdatert int(10) unsigned,
		status int(10) unsigned NOT NULL DEFAULT '0',
		status_id int(11) unsigned,
		status_tekst longtext,
		locked tinytext,
		PRIMARY KEY (rolle_id,spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the games table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."spill`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."spill` (
		spill_id int(10) unsigned NOT NULL auto_increment,
		navn tinytext,
		start int(10) unsigned,
		slutt int(10) unsigned,
		rollemal int(10) unsigned,
		paameldingsmal int(10) unsigned,
		rollekonsept int(1) unsigned DEFAULT '0',
		status tinytext,
		PRIMARY KEY (spill_id)
	)", $connection) or exit(mysql_error());
	
	# Create the templates table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."tabellmaler`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."tabellmaler` (
		mal_id int(10) unsigned NOT NULL auto_increment,
		navn tinytext,
		type varchar(128),
		PRIMARY KEY (mal_id)
	)", $connection) or exit(mysql_error());
	
	# Create the template-data table
	mysql_query("DROP TABLE IF EXISTS `".$table_prefix."tabellmaler_data`", $connection) or exit(mysql_error());
	mysql_query("CREATE TABLE `".$table_prefix."tabellmaler_data` (
		mal_id int(10) unsigned NOT NULL DEFAULT '0',
		type tinytext,
		fieldname varchar(128) NOT NULL,
		fieldtitle tinytext,
		extra longtext,
		hjelp longtext,
		pri int(11) unsigned,
		mand int(1) unsigned DEFAULT '0',
		intern int(1) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (mal_id,fieldname)
	)", $connection) or exit(mysql_error());

	
	# Create the zipcodes table if they are to be used
	if ($_POST['nor_zipcodes']) {
		mysql_query("UPDATE `".$table_prefix."config` SET value='1' WHERE name='use_autoregion'", $connection) or exit(mysql_error());
		mysql_query("DROP TABLE IF EXISTS `".$table_prefix."zipcodemap`", $connection) or exit(mysql_error());
		mysql_query("CREATE TABLE `".$table_prefix."zipcodemap` (
			zipcode varchar(25) NOT NULL DEFAULT '0000',
			region varchar(25),
			PRIMARY KEY (zipcode)
		)", $connection) or exit(mysql_error());
		
		# Populate the table... lots and lots of entries (this really ought to be in a separate file)
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0043','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0042','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0041','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0040','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0037','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0034','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0033','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0032','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0031','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0030','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0028','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0027','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0026','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0025','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0024','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0023','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0022','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0021','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0020','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0019','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0018','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0017','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0016','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0015','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0014','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0010','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0009','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0001','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0045','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0046','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0047','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0048','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0050','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0051','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0055','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0080','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0085','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0101','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0102','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0103','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0104','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0105','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0106','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0107','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0110','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0111','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0112','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0113','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0114','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0115','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0116','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0117','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0118','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0119','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0120','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0121','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0122','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0123','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0124','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0125','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0128','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0129','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0130','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0131','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0132','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0133','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0134','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0135','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0136','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0137','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0138','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0139','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0150','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0151','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0152','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0153','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0154','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0155','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0157','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0158','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0159','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0160','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0161','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0162','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0164','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0165','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0166','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0167','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0168','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0169','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0170','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0171','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0172','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0173','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0174','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0175','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0176','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0177','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0178','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0179','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0180','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0181','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0182','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0183','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0184','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0185','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0186','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0187','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0188','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0190','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0191','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0192','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0193','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0196','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0198','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0201','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0202','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0203','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0204','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0207','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0208','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0211','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0212','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0213','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0214','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0215','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0216','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0230','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0240','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0241','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0242','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0243','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0244','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0245','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0246','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0247','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0250','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0251','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0253','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0254','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0255','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0256','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0257','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0258','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0259','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0260','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0262','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0263','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0264','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0265','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0266','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0267','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0268','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0270','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0271','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0272','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0273','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0274','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0275','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0276','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0277','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0278','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0280','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0281','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0282','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0283','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0284','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0286','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0287','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0301','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0302','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0303','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0304','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0305','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0306','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0307','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0308','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0309','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0310','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0311','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0312','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0313','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0314','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0315','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0316','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0317','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0318','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0319','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0320','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0323','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0330','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0340','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0341','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0342','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0349','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0350','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0351','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0352','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0353','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0354','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0355','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0356','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0357','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0358','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0359','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0360','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0361','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0362','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0363','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0364','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0365','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0366','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0367','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0368','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0369','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0370','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0371','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0372','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0373','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0374','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0375','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0376','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0377','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0378','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0379','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0380','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0381','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0382','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0383','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0401','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0402','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0403','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0404','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0405','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0406','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0407','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0408','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0409','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0411','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0412','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0413','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0415','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0421','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0422','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0423','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0424','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0425','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0426','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0440','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0441','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0444','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0445','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0450','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0451','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0452','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0454','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0455','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0456','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0457','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0458','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0459','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0460','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0461','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0462','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0463','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0464','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0465','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0467','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0468','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0469','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0470','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0472','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0473','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0474','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0475','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0476','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0477','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0478','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0479','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0480','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0481','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0482','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0483','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0484','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0485','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0486','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0487','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0488','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0489','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0490','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0491','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0492','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0493','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0494','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0495','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0496','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0501','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0502','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0503','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0504','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0505','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0506','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0508','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0509','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0510','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0511','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0512','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0513','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0514','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0515','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0516','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0517','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0518','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0520','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0530','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0531','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0532','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0540','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0550','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0551','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0552','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0553','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0554','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0555','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0556','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0557','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0558','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0559','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0560','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0561','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0562','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0563','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0564','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0565','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0566','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0567','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0568','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0569','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0570','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0571','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0572','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0573','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0574','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0575','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0576','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0577','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0578','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0579','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0580','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0581','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0582','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0583','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0584','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0585','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0586','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0587','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0588','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0589','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0590','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0591','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0592','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0593','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0594','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0595','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0596','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0597','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0598','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0601','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0602','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0603','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0604','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0605','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0606','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0607','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0608','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0609','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0610','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0611','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0612','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0613','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0614','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0616','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0617','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0618','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0619','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0620','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0621','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0622','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0623','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0624','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0630','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0640','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0645','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0650','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0651','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0652','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0653','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0654','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0655','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0656','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0657','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0658','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0659','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0660','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0661','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0662','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0663','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0664','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0665','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0666','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0667','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0668','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0669','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0670','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0671','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0672','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0673','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0674','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0675','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0676','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0677','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0678','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0679','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0680','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0681','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0682','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0683','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0684','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0685','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0686','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0687','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0688','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0689','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0690','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0691','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0692','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0693','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0694','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0701','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0702','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0703','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0705','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0710','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0712','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0750','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0751','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0752','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0753','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0754','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0755','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0756','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0757','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0758','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0759','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0760','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0763','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0764','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0765','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0766','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0767','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0768','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0770','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0771','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0772','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0773','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0774','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0775','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0776','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0777','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0778','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0779','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0781','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0782','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0783','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0784','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0785','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0786','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0787','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0788','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0789','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0790','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0791','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0801','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0805','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0806','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0807','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0840','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0850','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0851','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0852','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0853','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0854','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0855','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0856','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0857','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0858','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0860','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0861','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0862','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0863','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0864','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0870','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0871','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0872','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0873','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0874','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0875','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0876','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0877','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0880','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0881','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0882','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0883','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0884','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0890','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0891','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0901','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0902','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0903','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0904','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0905','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0907','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0913','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0915','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0950','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0951','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0952','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0953','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0954','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0955','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0956','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0957','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0958','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0959','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0960','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0962','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0963','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0964','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0968','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0969','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0970','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0971','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0972','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0973','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0975','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0976','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0977','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0978','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0979','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0980','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0981','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0982','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0983','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0984','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0985','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0986','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0987','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('0988','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1001','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1003','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1005','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1006','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1007','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1008','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1009','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1011','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1051','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1052','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1053','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1054','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1055','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1056','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1061','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1062','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1063','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1064','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1065','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1067','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1068','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1069','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1071','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1081','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1083','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1084','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1086','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1087','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1088','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1089','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1101','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1109','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1112','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1150','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1151','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1152','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1153','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1154','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1155','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1156','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1157','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1158','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1160','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1161','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1162','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1163','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1164','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1165','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1166','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1167','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1168','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1169','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1170','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1172','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1176','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1177','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1178','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1179','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1181','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1182','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1184','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1185','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1187','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1188','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1189','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1201','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1202','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1203','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1204','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1205','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1206','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1207','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1214','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1215','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1250','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1251','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1252','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1253','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1254','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1255','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1256','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1257','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1258','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1259','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1262','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1263','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1266','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1270','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1271','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1272','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1273','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1274','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1275','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1277','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1278','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1279','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1281','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1283','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1284','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1285','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1286','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1290','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1291','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1294','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1295','OSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1300','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1301','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1302','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1303','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1304','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1305','HASLUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1306','BÆRUM POSTTERMINAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1309','RUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1311','KUNSTSENTERET HØVIKODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1312','SLEPENDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1313','VØYENENGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1314','VØYENENGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1316','EIKSMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1317','BÆRUMS VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1318','BEKKESTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1319','BEKKESTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1321','STABEKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1322','HØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1323','HØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1324','LYSAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1325','LYSAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1326','LYSAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1327','LYSAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1330','FORNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1331','FORNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1332','ØSTERÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1333','KOLSÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1334','RYKKINN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1335','SNARØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1336','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1337','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1338','SANDVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1339','VØYENENGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1340','SKUI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1341','SLEPENDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1344','HASLUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1346','GJETTUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1348','RYKKINN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1349','RYKKINN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1350','LOMMEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1351','RUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1352','KOLSÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1353','BÆRUMS VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1354','BÆRUMS VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1356','BEKKESTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1357','BEKKESTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1358','JAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1359','EIKSMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1361','ØSTERÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1362','HOSLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1363','HØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1365','BLOMMENHOLM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1366','LYSAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1367','SNARØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1368','STABEKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1369','STABEKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1371','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1372','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1373','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1375','BILLINGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1376','BILLINGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1377','BILLINGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1378','NESBRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1379','NESBRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1380','HEGGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1381','VETTRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1383','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1384','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1385','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1386','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1387','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1388','BORGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1389','HEGGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1390','VOLLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1391','VOLLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1392','VETTRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1393','VOLLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1394','NESBRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1395','HVALSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1396','BILLINGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1397','NESØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1399','ASKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1400','SKI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1401','SKI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1402','SKI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1403','LANGHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1404','SIGGERUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1405','LANGHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1406','SKI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1407','VINTERBRO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1408','KRÅKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1409','SKOTBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1410','KOLBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1411','KOLBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1412','SOFIEMYR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1413','TÅRNÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1414','TROLLÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1415','OPPEGÅRD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1416','OPPEGÅRD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1417','SOFIEMYR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1419','OPPEGÅRD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1420','SVARTSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1421','TROLLÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1430','ÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1431','ÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1432','ÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1440','DRØBAK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1441','DRØBAK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1442','DRØBAK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1443','OSCARSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1444','SKIPHELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1445','HEER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1450','NESODDTANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1451','NESODDTANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1453','BJØRNEMYR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1454','FAGERSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1455','NORDRE FROGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1458','FJELLSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1459','FJELLSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1470','LØRENSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1471','LØRENSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1472','FJELLHAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1473','LØRENSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1474','NORDBYHAGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1475','FINSTADJORDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1476','RASTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1477','FJELLHAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1480','SLATTUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1481','HAGAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1482','NITTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1483','SKYTTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1484','ÅNEBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1487','TØYENHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1488','HAKADAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1501','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1502','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1503','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1504','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1505','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1506','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1509','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1510','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1511','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1512','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1513','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1514','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1515','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1516','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1517','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1518','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1519','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1520','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1521','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1522','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1523','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1524','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1525','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1526','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1528','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1529','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1530','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1531','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1532','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1533','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1534','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1535','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1536','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1537','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1538','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1539','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1540','VESTBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1541','VESTBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1545','HVITSTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1550','HØLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1555','SON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1556','SON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1560','LARKOLLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1570','DILLING')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1580','RYGGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1581','RYGGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1583','RYGGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1590','RYGGE FLYSTASJON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1591','SPERREBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1592','VÅLER I ØSTFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1593','SVINNDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1596','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1597','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1598','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1599','MOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1601','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1602','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1603','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1604','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1605','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1606','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1607','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1608','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1610','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1612','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1613','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1614','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1615','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1616','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1617','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1618','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1619','FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1620','GRESSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1621','GRESSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1624','GRESSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1625','MANSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1626','MANSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1628','ENGALSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1630','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1631','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1632','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1633','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1634','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1636','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1637','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1639','GAMLE FREDRIKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1640','RÅDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1641','RÅDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1642','SALTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1650','SELLEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1651','SELLEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1653','SELLEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1654','SELLEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1655','SELLEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1657','TORP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1658','TORP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1659','TORP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1661','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1662','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1663','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1664','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1665','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1666','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1667','ROLVSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1670','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1671','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1672','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1673','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1675','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1676','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1678','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1679','KRÅKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1680','SKJÆRHALLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1682','SKJÆRHALLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1683','VESTERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1684','VESTERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1690','HERFØL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1692','NEDGÅRDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1701','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1702','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1703','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1704','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1705','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1706','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1707','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1708','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1709','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1710','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1711','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1712','GRÅLUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1713','GRÅLUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1714','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1715','YVEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1718','GREÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1719','GREÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1720','GREÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1721','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1722','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1723','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1724','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1725','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1726','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1727','SARPSBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1730','ISE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1733','HAFSLUNDSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1734','HAFSLUNDSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1735','VARTEIG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1738','BORGENHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1739','BORGENHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1740','BORGENHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1742','KLAVESTADHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1743','KLAVESTADHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1745','SKJEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1746','SKJEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1747','SKJEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1751','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1752','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1753','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1754','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1755','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1756','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1757','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1758','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1759','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1760','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1761','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1763','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1764','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1765','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1766','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1767','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1768','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1769','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1771','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1772','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1776','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1777','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1778','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1779','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1781','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1782','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1783','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1784','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1785','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1786','HALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1787','BERG I ØSTFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1788','BERG I ØSTFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1789','BERG I ØSTFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1790','TISTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1791','TISTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1792','TISTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1793','TISTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1794','SPONVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1796','KORNSJØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1798','AREMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1799','AREMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1801','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1802','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1803','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1804','SPYDEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1805','TOMTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1806','SKIPTVET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1807','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1808','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1809','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1811','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1812','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1813','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1814','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1815','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1816','SKIPTVET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1820','SPYDEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1823','KNAPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1825','TOMTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1827','HOBØL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1830','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1831','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1832','ASKIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1850','MYSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1851','MYSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1859','SLITU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1860','TRØGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1861','TRØGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1866','BÅSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1867','BÅSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1870','ØRJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1871','ØRJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1875','OTTEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1878','HÆRLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1880','EIDSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1890','RAKKESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1891','RAKKESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1892','DEGERNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1893','DEGERNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1900','FETSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1901','FETSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1903','GAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1910','ENEBAKKNESET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1911','FLATEBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1912','ENEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1914','YTRE ENEBAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1920','SØRUMSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1921','SØRUMSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1923','SØRUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1925','BLAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1927','RÅNÅSFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1929','AULI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1930','AURSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1940','BJØRKELANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1941','BJØRKELANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1950','RØMSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1954','SETSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1960','LØKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1963','FOSSER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('1970','HEMNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2000','LILLESTRØM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2001','LILLESTRØM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2003','LILLESTRØM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2004','LILLESTRØM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2005','RÆLINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2006','LØVENSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2007','KJELLER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2008','FJERDINGBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2009','NORDBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2010','STRØMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2011','STRØMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2013','SKJETTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2014','BLYSTADLIA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2015','LEIRSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2016','FROGNER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2019','SKEDSMOKORSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2020','SKEDSMOKORSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2021','SKEDSMOKORSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2022','GJERDRUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2024','GJERDRUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2025','FJERDINGBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2026','SKJETTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2027','KJELLER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2030','NANNESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2031','NANNESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2032','MAURA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2033','ÅSGREINA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2034','HOLTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2040','KLØFTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2041','KLØFTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2050','JESSHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2051','JESSHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2052','JESSHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2054','MOGREINA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2055','NORDKISA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2056','ALGARHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2057','JESSHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2058','SESSVOLLMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2059','TRANDUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2060','GARDERMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2061','GARDERMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2065','GARDERMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2070','RÅHOLT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2071','RÅHOLT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2072','DAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2073','BØN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2074','EIDSVOLL VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2080','EIDSVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2081','EIDSVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2090','HURDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2091','HURDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2092','MINNESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2093','FEIRING')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2100','SKARNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2101','SKARNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2110','SLÅSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2114','DISENÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2116','SANDER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2120','SAGSTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2123','BRUVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2130','KNAPPER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2133','GARDVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2134','AUSTVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2150','ÅRNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2151','ÅRNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2160','VORMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2162','BRÅRUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2164','SKOGBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2165','HVAM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2166','OPPAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2170','FENSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2201','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2202','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2203','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2204','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2205','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2206','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2208','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2209','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2210','GRANLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2211','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2212','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2213','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2214','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2216','ROVERUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2217','HOKKÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2218','LUNDERSÆTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2219','BRANDVAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2220','ÅBOGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2223','GALTERUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2224','AUSTMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2225','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2226','KONGSVINGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2230','SKOTTERUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2232','TOBØL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2233','VESTMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2235','MATRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2240','MAGNOR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2242','MOROKULIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2256','GRUE FINNSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2260','KIRKENÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2264','GRINDER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2265','NAMNÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2266','ARNEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2270','FLISA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2271','FLISA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2280','GJESÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2283','ÅSNES FINNSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2301','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2302','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2303','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2304','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2305','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2306','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2307','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2308','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2309','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2312','OTTESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2315','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2316','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2317','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2318','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2319','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2320','FURNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2321','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2322','RIDABU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2323','INGEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2324','VANG PÅ HEDMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2325','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2326','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2327','HAMAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2330','VALLSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2332','ÅSVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2334','ROMEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2335','STANGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2336','STANGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2337','TANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2338','ESPA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2340','LØTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2341','LØTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2344','ILSENG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2345','ÅDALSBRUK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2350','NES PÅ HEDMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2353','STAVSJØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2355','GAUPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2360','RUDSHØGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2364','NÆROSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2365','ÅSMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2372','BRØTTUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2380','BRUMUNDDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2381','BRUMUNDDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2390','MOELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2391','MOELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2401','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2402','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2403','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2404','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2405','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2406','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2407','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2408','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2409','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2410','HERNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2411','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2412','SØRSKOGBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2415','HERADSBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2416','JØMNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2418','ELVERUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2420','TRYSIL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2421','TRYSIL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2422','NYBERGSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2423','ØSTBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2425','LJØRDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2427','PLASSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2428','SØRE OSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2429','TØRBERGET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2430','JORDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2432','SLETTÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2435','BRASKEREIDFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2436','VÅLER I SOLØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2437','HASLEMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2438','GRAVBERGET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2440','ENGERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2443','DREVSJØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2446','ELGÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2448','SØMÅDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2450','RENA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2451','RENA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2460','OSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2476','ATNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2477','SOLLIA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2478','HANESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2480','KOPPANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2481','KOPPANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2485','RENDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2500','TYNSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2501','TYNSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2510','TYLLDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2512','KVIKNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2540','TOLGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2542','VINGELEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2544','ØVERSJØDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2550','OS I ØSTERDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2552','DALSBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2555','TUFSINGDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2560','ALVDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2561','ALVDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2580','FOLLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2581','FOLLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2582','GRIMSBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2584','DALHOLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2601','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2602','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2603','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2604','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2605','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2606','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2607','VINGROM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2608','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2609','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2610','MESNALI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2611','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2612','SJUSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2613','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2614','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2615','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2616','LISMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2617','JØRSTADMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2618','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2619','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2624','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2625','FÅBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2626','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2629','LILLEHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2630','RINGEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2631','RINGEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2632','VENABYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2633','FÅVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2634','FÅVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2635','TRETTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2636','ØYER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2637','ØYER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2639','VINSTRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2640','VINSTRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2642','KVAM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2643','SKÅBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2645','SØR-FRON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2646','GÅLÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2647','SØR-FRON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2648','SØR-FRON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2649','ØSTRE GAUSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2651','ØSTRE GAUSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2652','SVINGVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2653','VESTRE GAUSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2656','FOLLEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2657','SVATSUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2658','ESPEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2659','DOMBÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2660','DOMBÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2661','HJERKINN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2662','DOVRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2663','DOVRESKOGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2664','DOVRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2665','LESJA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2666','LORA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2667','LESJAVERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2668','LESJASKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2669','BJORLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2670','OTTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2672','SEL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2673','HØVRINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2674','MYSUSÆTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2675','OTTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2676','HEIDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2677','NEDRE HEIDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2680','VÅGÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2682','LALM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2683','TESSANDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2684','VÅGÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2685','GARMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2686','LOM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2687','BØVERDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2688','LOM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2690','SKJÅK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2693','NORDBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2694','SKJÅK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2695','GROTLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2711','GRAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2712','BRANDBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2713','ROA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2714','JAREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2715','LUNNER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2716','HARESTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2717','GRUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2718','BRANDBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2720','GRINDVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2730','LUNNER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2740','ROA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2742','GRUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2743','HARESTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2750','GRAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2760','BRANDBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2770','JAREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2801','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2802','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2803','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2804','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2805','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2807','HUNNDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2808','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2809','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2810','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2811','HUNNDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2815','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2816','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2817','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2818','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2819','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2821','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2822','BYBRUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2825','GJØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2827','HUNNDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2830','RAUFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2831','RAUFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2832','BIRI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2836','BIRI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2837','BIRISTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2838','SNERTINGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2839','ØVRE SNERTINGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2840','REINSVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2843','EINA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2846','BØVERBRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2847','KOLBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2848','SKREIA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2849','KAPP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2850','LENA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2851','LENA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2853','REINSVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2854','EINA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2857','SKREIA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2858','KAPP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2860','HOV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2861','LANDÅSBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2862','FLUBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2864','FALL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2866','ENGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2867','HOV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2870','DOKKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2879','ODNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2880','NORD-TORPA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2881','AUST-TORPA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2882','DOKKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2890','ETNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2893','ETNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2900','FAGERNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2901','FAGERNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2907','LEIRA I VALDRES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2910','AURDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2917','SKRAUTVÅL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2918','ULNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2920','LEIRA I VALDRES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2923','TISLEIDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2929','BAGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2930','BAGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2933','REINLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2936','BEGNADALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2937','BEGNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2939','HEGGENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2940','HEGGENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2943','ROGNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2950','SKAMMESTEIN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2952','BEITO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2953','BEITOSTØLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2959','RØN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2960','RØN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2966','SLIDRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2967','LOMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2973','RYFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2974','VANG I VALDRES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2975','VANG I VALDRES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2977','ØYE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('2985','TYINKRYSSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3001','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3002','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3003','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3004','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3005','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3006','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3007','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3008','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3011','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3012','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3013','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3014','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3015','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3016','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3017','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3018','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3019','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3020','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3021','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3022','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3023','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3024','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3025','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3026','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3027','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3028','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3029','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3030','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3031','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3032','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3033','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3034','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3035','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3036','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3037','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3038','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3039','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3040','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3041','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3042','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3043','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3044','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3045','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3046','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3047','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3048','DRAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3050','MJØNDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3051','MJØNDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3053','STEINBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3054','KROKSTADELVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3055','KROKSTADELVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3056','SOLBERGELVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3057','SOLBERGELVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3058','SOLBERGMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3060','SVELVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3061','SVELVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3070','SANDE I VESTFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3071','SANDE I VESTFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3075','BERGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3080','HOLMESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3081','HOLMESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3087','BOTNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3088','BOTNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3090','HOF')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3092','SUNDBYFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3095','EIDSFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3101','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3102','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3103','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3104','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3105','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3106','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3107','SEM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3108','VEAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3109','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3110','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3111','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3112','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3113','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3114','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3115','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3116','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3117','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3118','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3120','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3121','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3122','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3123','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3124','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3125','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3126','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3129','TØNSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3131','HUSØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3132','HUSØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3133','DUKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3135','TORØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3137','TORØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3138','SKALLESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3140','BORGHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3141','KJØPMANNSKJÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3142','VESTSKOGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3143','KJØPMANNSKJÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3144','VEIERLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3145','TJØME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3147','VERDENS ENDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3148','HVASSER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3150','TOLVSRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3151','TOLVSRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3152','TOLVSRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3153','TOLVSRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3154','TOLVSRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3157','BARKÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3158','ANDEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3159','MELSOMVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3160','STOKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3161','STOKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3162','ANDEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3163','BORGHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3164','REVETAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3165','TJØME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3166','TOLVSRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3167','ÅSGÅRDSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3169','STOKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3170','SEM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3171','SEM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3172','VEAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3173','VEAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3174','REVETAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3175','RAMNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3177','VÅLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3178','VÅLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3179','ÅSGÅRDSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3180','NYKIRKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3181','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3182','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3183','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3184','BORRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3185','SKOPPUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3186','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3187','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3188','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3189','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3191','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3192','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3193','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3194','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3195','SKOPPUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3196','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3197','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3198','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3199','HORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3201','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3202','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3203','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3204','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3205','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3206','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3207','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3208','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3209','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3210','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3211','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3212','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3213','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3214','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3215','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3216','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3217','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3218','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3219','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3220','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3221','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3222','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3223','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3224','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3225','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3226','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3227','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3228','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3229','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3230','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3231','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3232','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3233','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3234','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3235','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3236','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3237','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3238','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3239','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3241','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3242','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3243','KODAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3244','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3245','KODAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3246','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3247','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3248','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3249','SANDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3251','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3252','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3253','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3254','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3255','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3256','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3257','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3258','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3259','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3260','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3261','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3262','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3263','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3264','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3265','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3267','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3268','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3269','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3270','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3271','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3274','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3275','SVARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3276','SVARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3277','STEINSHOLT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3280','TJODALYNG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3281','TJODALYNG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3282','KVELDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3284','KVELDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3285','LARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3290','STAVERN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3291','STAVERN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3292','STAVERN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3294','STAVERN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3295','HELGEROA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3296','NEVLUNGHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3297','HELGEROA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3300','HOKKSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3301','HOKKSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3320','VESTFOSSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3321','VESTFOSSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3322','DARBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3330','SKOTSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3331','SKOTSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3340','ÅMOT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3341','ÅMOT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3342','ÅMOT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3350','PRESTFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3351','PRESTFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3355','SOLUMSMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3358','NEDRE EGGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3359','EGGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3360','GEITHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3361','GEITHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3370','VIKERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3371','VIKERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3387','SNARUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3400','LIER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3401','LIER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3407','TRANBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3408','TRANBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3410','SYLLING')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3411','SYLLING')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3412','LIERSTRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3420','LIERSKOGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3421','LIERSKOGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3425','REISTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3428','LIER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3430','SPIKKESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3431','SPIKKESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3440','RØYKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3441','RØYKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3442','HYGGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3470','SLEMMESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3471','SLEMMESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3472','BØDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3474','ÅROS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3475','SÆTRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3476','SÆTRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3477','BÅTSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3478','NÆRSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3480','FILTVET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3481','TOFTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3482','TOFTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3483','KANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3484','HOLMSBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3490','KLOKKARSTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3491','KLOKKARSTUA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3501','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3502','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3503','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3504','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3505','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3506','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3510','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3511','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3512','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3513','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3514','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3515','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3516','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3517','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3518','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3519','HØNEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3520','JEVNAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3521','JEVNAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3522','BJONEROA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3523','NES I ÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3524','NES I ÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3525','HALLINGBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3526','HALLINGBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3528','HEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3529','RØYSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3530','RØYSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3531','KROKKLEIVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3533','TYRISTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3534','SOKNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3535','KRØDEREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3536','NORESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3537','KRØDEREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3538','SOLLIHØGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3539','FLÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3540','NESBYEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3541','NESBYEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3550','GOL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3551','GOL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3560','HEMSEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3561','HEMSEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3570','ÅL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3571','ÅL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3575','HOL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3576','HOL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3577','HOVET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3579','TORPO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3580','GEILO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3581','GEILO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3593','USTAOSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3595','HAUGASTØL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3596','HALNE FJELLSTOVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3598','DYRANUT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3599','SANDHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3601','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3602','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3603','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3604','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3605','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3606','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3608','HEISTADMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3610','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3611','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3612','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3613','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3614','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3615','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3616','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3617','KONGSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3618','SKOLLENBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3619','SKOLLENBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3620','FLESBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3621','LAMPELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3622','SVENE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3623','LAMPELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3624','LYNGDAL I NUMEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3626','ROLLAG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3627','VEGGLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3628','VEGGLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3629','NORE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3630','RØDBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3631','RØDBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3632','UVDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3646','HVITTINGFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3647','HVITTINGFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3648','PASSEBEKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3650','TINN AUSTBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3652','HOVIN I TELEMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3656','ATRÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3658','MILAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3660','RJUKAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3661','RJUKAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3665','SAULAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3666','TINN AUSTBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3671','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3672','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3673','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3674','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3675','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3676','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3677','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3678','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3679','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3680','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3681','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3683','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3684','NOTODDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3690','HJARTDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3691','GRANSHERAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3692','SAULAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3697','TUDDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3699','GAUSTATOPPEN TURISTHYTTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3701','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3702','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3703','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3704','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3705','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3706','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3707','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3708','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3709','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3710','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3711','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3712','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3713','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3714','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3715','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3716','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3717','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3718','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3719','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3720','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3721','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3722','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3723','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3724','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3725','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3726','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3727','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3728','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3729','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3730','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3731','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3732','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3733','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3734','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3735','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3736','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3737','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3738','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3739','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3740','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3741','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3742','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3743','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3744','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3746','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3747','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3748','SILJAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3749','SILJAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3750','DRANGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3753','TØRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3760','NESLANDSVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3766','SANNIDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3770','KRAGERØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3780','SKÅTØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3781','JOMFRULAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3782','KRAGERØ SOMMERRUTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3783','KRAGERØ SKJÆRGÅRDSRUTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3784','LANGØY GRUVER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3786','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3787','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3788','STABBESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3789','KRAGERØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3790','HELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3791','KRAGERØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3792','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3793','SANNIDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3794','HELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3795','DRANGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3796','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3797','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3798','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3799','SKIEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3800','BØ I TELEMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3810','GVARV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3812','AKKERHAUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3820','NORDAGUTU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3825','LUNDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3830','ULEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3831','ULEFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3832','LUNDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3833','BØ I TELEMARK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3834','GVARV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3835','SELJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3836','KVITESEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3840','SELJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3841','FLATDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3848','MORGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3849','VRÅLIOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3850','KVITESEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3853','VRÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3854','NISSEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3855','TREUNGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3864','RAULAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3870','FYRESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3880','DALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3882','ÅMDALS VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3883','TREUNGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3884','RAULAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3885','FYRESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3886','DALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3887','VINJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3888','EDLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3890','VINJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3891','HØYDALSMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3893','VINJESVINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3895','EDLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3901','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3902','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3903','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3904','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3905','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3906','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3908','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3909','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3910','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3911','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3912','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3913','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3914','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3915','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3916','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3917','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3918','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3919','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3920','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3921','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3922','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3924','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3925','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3928','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3929','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3930','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3931','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3932','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3933','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3936','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3937','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3940','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3941','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3942','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3943','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3944','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3945','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3946','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3947','LANGANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3948','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3949','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3950','BREVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3960','STATHELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3965','HERRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3966','STATHELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3967','BAMBLE SOMMERRUTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3970','LANGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3991','BREVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3993','LANGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3994','LANGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3995','STATHELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3996','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3997','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3998','PORSGRUNN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('3999','HERRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4001','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4002','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4003','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4004','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4005','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4006','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4007','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4008','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4009','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4010','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4011','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4012','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4013','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4014','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4015','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4016','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4017','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4018','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4019','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4020','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4021','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4022','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4023','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4024','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4025','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4026','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4027','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4028','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4029','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4032','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4033','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4034','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4035','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4041','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4042','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4043','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4044','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4045','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4046','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4047','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4048','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4049','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4050','SOLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4052','RØYNEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4053','RÆGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4054','TJELTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4055','STAVANGER LUFTHAVN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4056','TANANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4064','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4065','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4066','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4067','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4068','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4069','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4070','RANDABERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4076','VASSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4078','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4079','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4081','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4082','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4084','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4085','HUNDVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4086','HUNDVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4087','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4088','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4089','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4090','HAFRSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4091','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4092','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4093','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4095','STAVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4096','RANDABERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4097','SOLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4098','TANANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4100','JØRPELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4102','IDSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4110','FORSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4119','FORSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4120','TAU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4122','FISKÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4123','SØR-HIDLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4124','TAU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4126','JØRPELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4127','LYSEBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4128','FLØYRLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4129','SONGESAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4130','HJELMELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4134','JØSENFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4137','ÅRDAL I RYFYLKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4139','FISTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4146','SKIFTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4148','HJELMELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4150','RENNESØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4152','VESTRE ÅMØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4153','BRIMSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4154','AUSTRE ÅMØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4156','MOSTERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4158','BRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4159','RENNESØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4160','FINNØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4161','FINNØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4163','TALGJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4164','FOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4167','HELGØY I RYFYLKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4168','BYRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4169','SØRBOKN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4170','SJERNARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4173','NORD-HIDLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4174','HELGØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4180','KVITSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4182','SKARTVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4187','OMBO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4198','FOLDØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4200','SAUDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4201','SAUDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4208','SAUDASJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4209','VANVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4230','SAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4233','ERFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4234','JELSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4235','HEBNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4237','SULDALSOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4239','SAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4240','SULDALSOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4244','NESFLATEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4250','KOPERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4260','TORVASTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4262','AVALDSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4264','KVALAVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4265','HÅVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4270','ÅKREHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4272','SANDVE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4274','STOL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4275','SÆVELANDSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4276','VEDAVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4280','SKUDENESHAVN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4291','KOPERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4294','KOPERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4295','VEDAVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4296','ÅKREHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4297','SKUDENESHAVN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4298','TORVASTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4299','AVALDSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4301','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4302','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4303','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4304','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4305','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4306','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4307','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4308','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4309','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4310','HOMMERSÅK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4311','HOMMERSÅK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4312','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4313','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4314','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4315','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4316','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4317','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4318','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4319','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4321','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4322','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4323','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4324','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4325','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4326','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4327','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4328','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4329','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4330','ÅLGÅRD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4332','FIGGJO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4333','OLTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4335','DIRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4339','ÅLGÅRD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4340','BRYNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4342','UNDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4343','ORRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4349','BRYNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4352','KLEPPE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4353','KLEPP STASJON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4354','VOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4355','KVERNALAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4356','KVERNALAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4357','KLEPP STASJON')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4358','KLEPPE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4360','VARHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4362','VIGRESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4363','BRUSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4364','SIREVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4365','NÆRBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4367','NÆRBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4368','VARHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4369','VIGRESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4370','EGERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4375','HELLVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4376','HELLELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4379','EGERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4380','HAUGE I DALANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4381','HAUGE I DALANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4387','BJERKREIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4389','VIKESÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4391','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4392','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4393','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4394','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4395','HOMMERSÅK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4396','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4397','SANDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4400','FLEKKEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4401','FLEKKEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4402','FLEKKEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4403','FLEKKEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4420','ÅNA-SIRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4432','HIDRASUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4434','ANDABELØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4436','GYLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4438','SIRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4440','TONSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4441','TONSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4443','TJØRHOM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4460','MOI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4462','HOVSHERAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4463','UALAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4465','MOI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4473','KVINLOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4480','KVINESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4484','ØYESTRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4485','FEDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4490','KVINESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4491','KVINESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4492','KVINESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4501','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4502','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4503','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4504','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4505','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4506','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4509','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4512','LINDESNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4513','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4514','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4515','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4516','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4517','MANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4519','HOLUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4520','SØR-AUDNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4521','SPANGEREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4523','SØR-AUDNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4524','SØR-AUDNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4525','KONSMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4528','KOLLUNGTVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4529','BYREMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4532','ØYSLEBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4534','MARNARDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4536','BJELLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4540','ÅSERAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4544','FOSSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4550','FARSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4551','FARSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4552','FARSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4553','FARSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4554','FARSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4557','VANSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4558','VANSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4560','VANSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4563','BORHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4575','LYNGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4576','LYNGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4577','LYNGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4579','LYNGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4580','LYNGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4586','KORSHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4588','KVÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4590','SNARTEMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4595','TINGVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4596','EIKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4604','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4605','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4606','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4609','KARDEMOMME BY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4610','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4611','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4612','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4613','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4614','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4615','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4616','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4617','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4618','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4619','MOSBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4620','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4621','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4622','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4623','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4624','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4625','FLEKKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4626','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4628','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4629','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4630','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4631','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4632','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4633','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4634','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4635','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4636','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4637','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4638','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4639','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4640','SØGNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4645','NODELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4646','FINSLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4647','BRENNÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4651','HAMRESANDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4656','HAMRESANDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4657','KJEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4658','TVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4659','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4661','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4662','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4663','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4664','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4665','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4666','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4668','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4669','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4671','MOSBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4673','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4674','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4675','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4676','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4677','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4678','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4679','FLEKKERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4682','SØGNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4683','SØGNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4685','NODELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4686','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4687','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4688','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4689','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4691','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4693','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4696','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4697','KRISTIANSAND S')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4699','TVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4700','VENNESLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4701','VENNESLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4702','VENNESLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4703','VENNESLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4705','ØVREBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4715','ØVREBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4720','HÆGELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4724','IVELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4730','VATNESTRØM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4733','EVJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4734','EVJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4735','EVJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4737','HORNNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4738','EVJEMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4741','BYGLANDSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4742','GRENDI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4745','BYGLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4747','VALLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4748','RYSSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4754','BYKLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4755','HOVDEN I SETESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4760','BIRKELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4766','HEREFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4768','ENGESLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4770','HØVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4780','BREKKESTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4790','LILLESAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4791','LILLESAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4792','LILLESAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4794','LILLESAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4795','BIRKELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4801','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4802','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4803','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4804','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4808','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4809','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4810','EYDEHAVN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4812','KONGSHAVN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4815','SALTRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4816','KOLBJØRNSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4817','HIS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4818','FÆRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4820','FROLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4821','RYKENE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4823','NEDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4824','BJORBEKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4825','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4827','FROLANDS VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4828','MJÅVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4830','HYNNEKLEIV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4832','MYKLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4834','RISDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4835','SKJEGGEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4836','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4838','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4839','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4841','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4842','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4843','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4844','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4846','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4847','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4848','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4849','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4851','SALTRØD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4852','FÆRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4853','HIS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4854','NEDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4855','FROLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4856','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4857','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4858','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4859','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4861','ARENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4862','EYDEHAVN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4863','NELAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4864','ÅMLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4865','ÅMLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4868','SELÅSVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4869','DØLEMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4870','FEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4876','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4877','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4878','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4879','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4884','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4885','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4886','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4887','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4888','HOMBORSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4889','FEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4891','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4892','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4894','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4895','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4896','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4898','GRIMSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4900','TVEDESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4901','TVEDESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4902','TVEDESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4909','SONGE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4910','LYNGØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4912','GJEVING')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4915','VESTRE SANDØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4916','BORØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4920','STAUBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4934','NESGRENDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4950','RISØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4951','RISØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4952','RISØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4953','RISØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4955','RISØR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4971','SUNDEBRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4972','GJERSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4973','VEGÅRSHEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4974','SØNDELED')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4980','GJERSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4985','VEGÅRSHEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4990','SØNDELED')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4993','SUNDEBRU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('4994','AKLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5003','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5004','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5005','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5006','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5007','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5008','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5009','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5010','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5011','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5012','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5013','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5014','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5015','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5016','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5017','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5018','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5019','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5020','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5021','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5031','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5032','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5033','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5034','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5035','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5036','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5037','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5038','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5039','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5041','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5042','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5043','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5045','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5052','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5053','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5054','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5055','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5056','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5057','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5058','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5059','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5063','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5067','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5068','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5072','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5073','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5075','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5081','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5089','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5093','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5094','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5096','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5097','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5098','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5101','EIDSVÅGNESET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5104','EIDSVÅG I ÅSANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5105','EIDSVÅG I ÅSANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5106','ØVRE ERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5107','SALHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5108','HORDVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5109','HYLKJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5111','BREISTEIN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5113','TERTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5114','TERTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5115','ULSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5116','ULSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5117','ULSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5118','ULSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5119','ULSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5121','ULSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5122','MORVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5124','MORVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5131','NYBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5132','NYBORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5134','FLAKTVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5135','FLAKTVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5136','MJØLKERÅEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5137','MJØLKERÅEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5141','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5142','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5143','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5144','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5145','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5146','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5147','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5148','FYLLINGSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5151','STRAUMSGREND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5152','BØNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5155','BØNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5161','LAKSEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5162','LAKSEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5163','LAKSEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5164','LAKSEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5171','LODDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5172','LODDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5173','LODDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5174','MATHOPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5177','BJØRØYHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5178','LODDEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5179','GODVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5183','OLSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5184','OLSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5200','OS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5201','OS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5202','OS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5203','OS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5207','SØFTELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5212','SØFTELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5215','LYSEKLOSTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5216','LEPSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5217','HAGAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5218','NORDSTRØNO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5219','SKORPO FERIEHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5221','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5222','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5223','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5224','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5225','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5226','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5227','NESTTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5229','KALANDSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5231','PARADIS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5232','PARADIS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5235','RÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5236','RÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5238','RÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5239','RÅDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5243','FANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5244','FANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5251','SØREIDGREND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5252','SØREIDGREND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5253','SANDSLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5254','SANDSLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5257','KOKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5258','BLOMSTERDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5259','HJELLESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5260','INDRE ARNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5261','INDRE ARNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5262','ARNATVEIT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5263','TRENGEREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5264','GARNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5265','YTRE ARNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5267','ESPELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5268','HAUKELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5281','VALESTRANDSFOSSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5282','LONEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5283','FOTLANDSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5284','TYSSEBOTNEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5285','BRUVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5286','HAUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5291','VALESTRANDSFOSSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5293','LONEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5295','FOTLANDSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5299','HAUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5300','KLEPPESTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5302','STRUSSHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5303','FOLLESE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5304','HETLEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5305','FLORVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5306','ERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5307','ASK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5310','HAUGLANDSHELLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5314','KJERRGARDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5315','HERDLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5318','STRUSSHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5321','KLEPPESTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5322','KLEPPESTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5323','KLEPPESTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5325','FOLLESE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5326','ASK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5327','HAUGLANDSHELLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5329','FLORVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5331','RONG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5333','TJELDSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5334','HELLESØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5335','HERNAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5336','TJELDSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5337','RONG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5341','STRAUME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5342','STRAUME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5343','STRAUME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5345','KNARREVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5346','ÅGOTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5347','KYSTBASEN ÅGOTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5350','BRATTHOLMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5353','STRAUME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5355','KNARREVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5357','FJELL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5363','ÅGOTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5365','TURØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5371','SKOGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5373','KLOKKARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5374','STEINSLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5378','KLOKKARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5379','STEINSLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5380','TÆLAVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5381','GLESVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5382','SKOGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5384','TORANGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5385','BAKKASUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5387','MØKSTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5388','LITLAKALSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5392','STOREBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5393','STOREBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5394','KOLBEINSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5396','VESTRE VINNESVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5397','BEKKJARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5398','STOLMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5399','BEKKJARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5401','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5402','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5403','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5404','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5405','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5406','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5407','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5408','SAGVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5409','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5410','SAGVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5411','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5412','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5414','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5415','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5416','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5417','STORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5418','FITJAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5419','FITJAR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5420','RUBBESTADNESET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5423','BRANDASUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5427','URANGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5428','FOLDRØYHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5430','BREMNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5437','FINNÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5440','MOSTERHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5443','BØMLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5444','ESPEVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5445','BREMNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5447','MOSTERHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5448','RUBBESTADNESET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5449','BØMLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5450','SUNDE I SUNNHORDLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5451','VALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5452','SANDVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5453','UTÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5454','SÆBØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5455','HALSNØY KLOSTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5457','HØYLANDSBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5458','ARNAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5459','FJELBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5460','HUSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5462','HERØYSUNDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5463','USKEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5464','DIMMELSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5465','USKEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5470','ROSENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5472','SEIMSFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5473','SNILSTVEITØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5474','LØFALLSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5475','ÆNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5476','MAURANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5480','HUSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5484','SÆBØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5486','ROSENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5497','HUGLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5498','MATRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5499','ÅKRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5501','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5502','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5503','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5504','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5505','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5506','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5507','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5508','KARMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5509','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5511','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5513','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5514','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5515','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5516','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5517','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5518','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5519','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5521','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5522','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5523','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5525','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5527','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5528','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5529','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5531','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5532','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5533','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5534','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5535','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5536','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5537','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5538','HAUGESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5541','KOLNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5542','KARMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5544','VORMEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5545','VORMEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5546','RØYKSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5547','UTSIRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5548','FEØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5549','RØVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5550','SVEIO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5551','AUKLANDSHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5554','VALEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5555','FØRDE I HORDALAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5559','SVEIO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5560','NEDSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5561','BOKN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5563','FØRRESFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5565','TYSVÆRVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5566','HERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5567','SKJOLDASTRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5568','VIKEBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5570','AKSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5574','SKJOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5575','AKSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5576','ØVRE VATS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5578','NEDRE VATS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5580','ØLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5582','ØLENSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5583','VIKEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5584','BJOA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5585','SANDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5586','VIKEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5588','ØLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5589','SANDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5590','ETNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5591','ETNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5593','SKÅNEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5594','SKÅNEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5596','MARKHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5598','FJÆRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5600','NORHEIMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5601','NORHEIMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5602','NORHEIMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5604','ØYSTESE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5605','ÅLVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5610','ØYSTESE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5612','STEINSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5614','ÅLVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5620','TØRVIKBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5626','KYSNESSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5627','JONDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5628','HERAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5629','JONDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5630','STRANDEBARM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5632','OMASTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5635','HATLESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5636','VARALDSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5637','ØLVE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5640','EIKELANDSOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5641','FUSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5642','HOLMEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5643','STRANDVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5645','SÆVAREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5646','NORDTVEITGREND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5647','BALDERSHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5649','EIKELANDSOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5650','TYSSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5652','ÅRLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5653','ÅRLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5680','TYSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5682','GODØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5683','REKSTEREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5685','UGGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5687','FLATRÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5690','LUNDEGREND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5693','ÅRBAKKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5694','ONARHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5695','UGGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5696','TYSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5700','VOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5701','VOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5702','VOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5703','VOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5707','EVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5710','SKULESTADMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5712','VOSSESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5713','VOSSESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5715','STALHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5718','MYRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5719','FINSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5721','DALEKVAM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5722','DALEKVAM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5723','BOLSTADØYRI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5724','STANGHELLE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5725','VAKSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5726','VAKSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5727','STAMNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5728','EIDSLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5729','MODALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5730','ULVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5731','ULVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5733','GRANVIN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5734','VALLAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5736','GRANVIN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5741','AURLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5742','FLÅM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5743','FLÅM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5745','AURLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5746','UNDREDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5747','GUDVANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5748','STYVI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5749','BAKKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5750','ODDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5751','ODDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5760','RØLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5763','SKARE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5770','TYSSEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5773','HOVLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5776','NÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5777','GRIMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5778','UTNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5779','UTNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5780','KINSARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5781','LOFTHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5782','KINSARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5783','EIDFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5784','ØVRE EIDFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5785','VØRINGSFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5786','EIDFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5787','LOFTHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5802','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5803','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5804','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5805','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5806','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5807','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5808','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5809','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5811','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5812','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5815','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5816','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5817','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5818','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5819','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5821','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5822','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5824','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5825','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5828','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5829','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5835','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5836','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5837','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5838','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5845','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5846','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5847','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5848','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5849','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5851','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5852','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5853','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5854','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5856','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5857','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5858','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5859','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5861','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5862','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5863','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5864','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5868','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5869','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5871','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5872','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5873','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5876','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5877','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5878','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5879','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5881','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5882','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5883','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5884','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5886','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5888','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5889','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5892','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5893','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5896','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5899','BERGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5902','ISDALSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5903','ISDALSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5904','ISDALSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5906','FREKHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5907','ALVERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5911','ALVERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5912','SEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5913','EIKANGERVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5914','ISDALSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5915','HJELMÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5917','ROSSLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5918','FREKHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5931','MANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5936','MANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5937','BØVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5938','SÆBØVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5939','SLETTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5941','AUSTRHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5943','AUSTRHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5947','FEDJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5948','FEDJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5951','LINDÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5953','FONNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5954','MONGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5955','LINDÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5956','VÅGSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5957','MYKING')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5960','DALSØYRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5961','BREKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5962','BJORDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5966','EIVINDVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5967','EIVINDVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5970','BYRKNESØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5977','ÅNNELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5978','MJØMNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5979','BYRKNESØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5981','MASFJORDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5983','HAUGSVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5984','MATREDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5986','HOSTELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5987','HOSTELAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5991','OSTEREIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5993','OSTEREIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('5994','VIKANES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6001','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6002','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6003','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6004','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6005','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6006','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6007','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6008','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6009','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6010','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6011','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6012','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6013','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6014','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6015','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6016','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6017','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6018','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6019','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6020','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6021','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6022','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6023','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6024','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6025','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6026','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6028','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6029','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6030','LANGEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6035','FISKARSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6036','MAUSEIDVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6037','EIDSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6038','FISKARSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6039','LANGEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6040','VIGRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6045','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6046','ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6050','VALDERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6051','VALDERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6052','GISKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6055','GODØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6057','ELLINGSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6058','VALDERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6059','VIGRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6060','HAREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6062','BRANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6063','HJØRUNGAVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6064','HADDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6065','ULSTEINVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6067','ULSTEINVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6069','HAREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6070','TJØRVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6076','MOLDTUSTRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6080','GURSKØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6082','GURSKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6083','GJERDSVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6084','LARSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6085','LARSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6087','KVAMSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6089','SANDSHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6090','FOSNAVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6092','EGGESBØNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6094','LEINØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6095','BØLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6096','RUNDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6098','NERLANDSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6099','FOSNAVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6100','VOLDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6101','VOLDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6102','VOLDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6110','AUSTEFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6120','FOLKESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6133','LAUVSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6139','FISKÅBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6140','SYVDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6141','ROVDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6142','EIDSÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6143','FISKÅBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6144','SYLTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6146','ÅHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6149','ÅRAM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6150','ØRSTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6151','ØRSTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6160','HOVDEBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6161','HOVDEBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6165','SÆBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6166','SÆBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6170','VARTDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6174','BARSTADVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6183','TRANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6184','STORESTANDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6190','BJØRKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6196','NORANGSFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6200','STRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6201','STRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6210','VALLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6212','LIABYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6213','TAFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6214','NORDDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6215','EIDSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6216','GEIRANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6217','DJUPEVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6218','HELLESYLT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6220','STRAUMGJERDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6222','IKORNNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6224','HUNDEIDVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6230','SYKKYLVEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6238','STRAUMGJERDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6239','SYKKYLVEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6240','ØRSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6249','ØRSKOG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6250','STORDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6259','STORDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6260','SKODJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6263','SKODJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6264','TENNFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6265','VATNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6270','BRATTVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6272','HILDRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6280','SØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6281','SØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6282','BRATTVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6283','VATNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6285','STOREKALVØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6290','HARAMSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6292','KJERSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6293','LONGVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6294','FJØRTOFT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6300','ÅNDALSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6301','ÅNDALSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6309','ÅNDALSNES LEIRPLASS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6310','VEBLUNGSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6315','INNFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6320','ISFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6330','VERMA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6335','TROLLSTIGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6339','ISFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6350','EIDSBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6360','ÅFARNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6363','MITTET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6364','VISTDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6386','MÅNDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6387','VÅGSTRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6390','VESTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6391','TRESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6392','VIKEBUKT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6393','TOMREFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6394','FIKSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6395','REKDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6396','VIKEBUKT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6397','TRESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6398','TOMREFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6399','VESTNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6401','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6402','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6403','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6404','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6405','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6407','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6408','AUREOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6409','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6410','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6411','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6412','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6413','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6414','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6415','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6416','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6418','SEKKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6419','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6421','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6422','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6425','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6429','MOLDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6430','BUD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6433','HUSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6440','ELNESVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6443','TORNES I ROMSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6444','FARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6445','MALMEFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6447','ELNESVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6449','FARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6450','HJELSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6453','KLEIVE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6454','HJELSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6455','KORTGARDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6456','SKÅLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6457','BOLSØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6460','EIDSVÅG I ROMSDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6462','RAUDSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6470','ERESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6472','EIKESDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6475','MIDSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6476','MIDSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6480','AUKRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6481','AUKRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6483','ONA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6484','SANDØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6486','ORTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6487','HARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6488','MYKLEBOST')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6490','EIDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6493','LYNGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6494','VEVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6499','EIDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6501','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6502','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6503','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6504','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6505','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6506','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6507','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6508','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6509','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6510','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6511','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6512','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6514','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6515','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6516','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6517','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6518','KRISTIANSUND N')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6520','FREI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6523','FREI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6529','FREI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6530','AVERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6538','AVERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6539','AVERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6570','SMØLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6571','SMØLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6590','TUSTNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6600','SUNNDALSØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6601','SUNNDALSØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6610','ØKSENDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6611','FURUGRENDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6612','GRØA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6613','GJØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6620','ÅLVUNDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6622','ÅLVUNDFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6628','MEISINGSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6629','TORJULVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6630','TINGVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6631','BATNFJORDSØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6633','GJEMNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6636','ANGVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6637','FLEMMA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6638','OSMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6639','TORVIKBUKT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6640','KVANNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6642','STANGVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6643','BØFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6644','BÆVERFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6645','TODALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6650','SURNADAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6652','SURNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6653','ØVRE SURNADAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6655','VINDØLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6656','SURNADAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6657','RINDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6658','RINDALSSKOGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6659','RINDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6670','ØYDEGARD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6674','KVISVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6680','HALSANAUSTAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6683','VÅGLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6686','VALSØYBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6687','VALSØYFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6688','VÅGLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6689','AURE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6690','AURE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6693','MJOSUNDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6694','FOLDFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6697','VIHALS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6698','LESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6699','KJØRSVIKBUGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6700','MÅLØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6701','MÅLØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6702','MÅLØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6703','MÅLØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6704','DEKNEPOLLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6706','MÅLØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6707','RAUDEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6708','BRYGGJA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6710','RAUDEBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6711','BRYGGJA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6713','ALMENNINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6714','SILDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6715','BARMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6716','HUSEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6717','FLATRAKET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6718','DEKNEPOLLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6719','SKATESTRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6721','SVELGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6723','SVELGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6726','BREMANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6727','BREMANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6728','KALVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6729','KALVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6730','DAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6731','DAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6734','RUGSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6737','ÅLFOTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6740','SELJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6741','SELJE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6750','STADLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6751','STADLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6761','HORNINDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6763','HORNINDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6770','NORDFJORDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6771','NORDFJORDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6772','NORDFJORDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6775','NORDFJORDEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6776','KJØLSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6777','STÅRHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6778','LOTE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6779','HOLMØYANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6781','STRYN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6782','STRYN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6783','STRYN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6784','OLDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6785','STRYN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6788','OLDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6789','LOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6791','OLDEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6792','BRIKSDALSBRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6793','INNVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6795','BLAKSÆTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6796','HOPLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6797','UTVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6798','HJELLEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6799','OPPSTRYN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6800','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6801','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6802','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6803','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6804','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6805','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6806','NAUSTDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6807','FØRDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6816','NAUSTDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6817','NAUSTDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6818','HAUKEDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6819','HOLSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6821','SANDANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6822','SANDANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6823','SANDANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6824','SANDANE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6826','BYRKJELO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6827','BREIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6828','HESTENESØYRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6829','HYEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6841','SKEI I JØLSTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6843','SKEI I JØLSTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6847','VASSENDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6848','FJÆRLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6851','SOGNDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6852','SOGNDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6853','SOGNDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6854','KAUPANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6855','FRØNNINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6856','SOGNDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6857','SOGNDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6858','FARDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6859','SLINDE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6861','LEIKANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6863','LEIKANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6866','GAUPNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6868','GAUPNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6869','HAFSLO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6870','ORNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6871','JOSTEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6872','LUSTER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6873','MARIFJØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6875','HØYHEIMSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6876','SKJOLDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6877','FORTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6878','VEITASTROND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6879','SOLVORN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6881','ÅRDALSTANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6882','ØVRE ÅRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6884','ØVRE ÅRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6885','ÅRDALSTANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6886','LÆRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6887','LÆRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6888','STEINKLEPP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6891','VIK I SOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6893','VIK I SOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6894','VANGSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6895','FEIOS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6896','FRESVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6898','BALESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6899','BALESTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6900','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6901','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6902','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6903','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6904','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6907','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6909','FLORØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6912','KINN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6914','SVANØYBUKT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6915','ROGNALDSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6916','BAREKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6917','BATALDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6918','SØR-SKORPA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6919','TANSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6921','HARDBAKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6924','HARDBAKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6926','KRAKHELLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6927','YTRØYGREND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6928','KOLGROV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6929','HERSVIKBYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6939','STAVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6940','EIKEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6941','EIKEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6942','SVORTEVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6944','STAVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6946','LAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6947','LAVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6951','LEIRVIK I SOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6953','LEIRVIK I SOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6957','HYLLESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6958','SØRBØVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6961','DALE I SUNNFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6963','DALE I SUNNFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6964','KORSSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6966','GUDDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6967','HELLEVIK I FJALER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6968','FLEKKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6969','STRAUMSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6971','SANDE I SUNNFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6973','SANDE I SUNNFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6975','SKILBREI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6977','BYGSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6978','VIKSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6980','ASKVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6981','HOLMEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6982','HOLMEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6983','KVAMMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6984','STONGFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6985','ATLØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6986','VÆRLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6987','BULANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6988','ASKVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6991','HØYANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6993','HØYANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6995','KYRKJEBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('6996','VADHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7003','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7004','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7005','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7006','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7007','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7010','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7011','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7012','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7013','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7014','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7015','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7016','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7018','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7019','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7020','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7021','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7022','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7023','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7024','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7025','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7026','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7027','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7028','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7029','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7030','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7031','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7032','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7033','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7034','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7036','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7037','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7038','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7039','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7040','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7041','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7042','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7043','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7044','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7045','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7046','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7047','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7048','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7049','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7050','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7051','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7052','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7053','RANHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7054','RANHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7056','RANHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7057','JONSVATNET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7058','JAKOBSLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7059','JAKOBSLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7070','BOSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7072','HEIMDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7074','SPONGDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7075','TILLER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7078','SAUPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7079','FLATÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7080','HEIMDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7081','SJETNEMARKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7082','KATTEM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7083','LEINSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7088','HEIMDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7089','HEIMDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7091','TILLER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7092','TILLER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7097','SAUPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7098','SAUPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7099','FLATÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7100','RISSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7101','RISSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7105','STADSBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7110','FEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7112','HASSELVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7113','HUSBYSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7114','RÅKVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7119','STADSBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7120','LEKSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7121','LEKSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7125','VANVIKAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7126','VANVIKAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7127','OPPHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7128','UTHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7129','BREKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7130','BREKSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7140','OPPHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7142','UTHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7150','STORFOSNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7152','KRÅKVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7153','GARTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7156','LEKSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7159','BJUGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7160','BJUGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7165','OKSVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7166','TARVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7167','VALLERSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7168','LYSØYSUNDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7169','ÅFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7170','ÅFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7176','LINESØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7177','REVSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7178','STOKKØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7180','ROAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7190','BESSAKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7194','BRANDSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7200','KYRKSÆTERØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7201','KYRKSÆTERØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7203','VINJEØRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7206','HELLANDSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7211','KORSVEGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7212','KORSVEGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7213','GÅSBAKKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7221','MELHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7223','MELHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7224','MELHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7227','GIMSE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7228','KVÅL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7229','KVÅL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7231','LUNDAMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7232','LUNDAMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7234','LER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7236','HOVIN I GAULDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7238','HOVIN I GAULDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7239','HITRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7240','HITRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7241','ANSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7242','KNARRLAGSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7243','KVENVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7246','SANDSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7247','HESTVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7250','MELANDSJØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7252','DOLMØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7255','SUNDLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7256','HEMNSKJEL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7257','SNILLFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7259','SNILLFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7260','SISTRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7261','SISTRANDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7263','HAMARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7264','HAMARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7266','KVERVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7268','TITRAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7270','DYRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7273','NORDDYRØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7280','SULA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7282','BOGØYVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7284','MAUSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7285','GJÆSINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7286','SØRBURØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7287','SAUØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7288','SOKNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7289','SOKNEDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7290','STØREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7291','STØREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7295','ROGNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7298','BUDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7300','ORKANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7301','ORKANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7302','ORKANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7310','GJØLME')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7315','LENSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7316','LENSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7318','AGDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7319','AGDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7320','FANNREM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7321','FANNREM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7327','SVORKMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7329','SVORKMO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7331','LØKKEN VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7332','LØKKEN VERK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7333','STORÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7334','STORÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7335','JERPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7336','MELDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7338','MELDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7340','OPPDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7341','OPPDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7342','LØNSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7343','VOGNILL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7344','GJEVILVASSHYTTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7345','DRIVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7350','BUVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7351','BUVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7353','BØRSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7354','VIGGJA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7355','EGGKLEIVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7357','SKAUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7358','BØRSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7361','RØROS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7366','RØROS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7370','BREKKEBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7372','GLÅMOS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7374','RØROS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7380','ÅLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7383','HALTDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7384','ÅLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7386','SINGSÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7387','SINGSÅS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7391','RENNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7392','RENNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7393','RENNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7397','RENNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7398','RENNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7399','RENNEBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7400','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7401','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7402','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7403','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7404','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7405','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7406','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7407','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7408','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7409','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7410','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7411','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7412','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7413','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7414','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7415','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7416','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7417','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7418','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7419','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7420','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7421','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7422','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7423','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7424','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7425','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7426','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7427','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7428','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7429','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7430','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7431','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7432','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7433','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7434','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7435','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7436','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7437','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7438','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7439','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7440','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7441','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7442','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7443','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7444','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7445','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7446','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7447','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7448','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7449','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7450','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7451','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7452','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7453','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7456','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7457','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7458','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7459','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7462','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7463','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7464','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7465','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7466','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7467','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7468','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7469','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7471','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7472','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7473','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7474','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7475','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7476','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7477','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7478','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7479','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7481','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7483','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7484','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7485','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7486','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7488','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7489','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7491','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7492','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7493','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7495','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7496','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7498','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7499','TRONDHEIM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7500','STJØRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7501','STJØRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7505','STJØRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7506','STJØRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7508','STJØRDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7510','SKATVAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7517','HELL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7519','ELVARLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7520','HEGRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7525','FLORNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7529','HEGRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7530','MERÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7531','MERÅKER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7533','KOPPERÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7540','KLÆBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7541','KLÆBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7548','TANEM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7549','TANEM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7550','HOMMELVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7551','HOMMELVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7560','VIKHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7562','HUNDHAMAREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7563','MALVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7566','VIKHAMMER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7570','HELL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7580','SELBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7581','SELBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7583','SELBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7584','SELBUSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7586','SELBUSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7590','TYDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7591','TYDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7596','FLAKNAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7600','LEVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7601','LEVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7609','LEVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7610','LEVANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7619','SKOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7620','SKOGN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7622','MARKABYGDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7623','RONGLAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7624','EKNE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7629','YTTERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7630','ÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7631','ÅSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7632','ÅSENFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7633','FROSTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7634','FROSTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7650','VERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7651','VERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7658','VERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7659','VERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7660','VUKU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7663','STIKLESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7670','INDERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7671','INDERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7690','MOSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7701','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7702','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7703','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7704','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7705','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7706','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7707','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7708','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7709','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7710','SPARBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7711','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7712','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7713','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7715','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7716','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7717','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7718','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7724','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7725','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7726','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7728','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7729','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7730','BEITSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7732','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7734','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7735','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7736','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7737','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7738','STEINKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7739','BEITSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7740','STEINSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7742','YTTERVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7744','HEPSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7745','OPPLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7746','HASVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7748','SÆTERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7750','NAMDALSEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7751','NAMDALSEID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7760','SNÅSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7761','SNÅSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7770','FLATANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7771','FLATANGER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7777','NORD-STATLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7790','MALM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7791','MALM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7796','FOLLAFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7797','VERRABOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7800','NAMSOS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7801','NAMSOS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7809','NAMSOS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7817','SALSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7818','LUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7819','FOSSLANDSOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7820','SPILLUM')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7822','BANGSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7856','JØA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7860','SKAGE I NAMDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7863','OVERHALLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7864','OVERHALLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7869','SKAGE I NAMDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7870','GRONG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7871','GRONG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7873','HARRAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7882','NORDLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7884','SØRLI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7890','NAMSSKOGAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7892','TRONES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7893','SKOROVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7896','BREKKVASSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7898','LIMINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7900','RØRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7901','RØRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7940','OTTERSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7944','INDRE NÆRØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7950','ABELVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7960','SALSBRUKET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7970','KOLVEREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7971','KOLVEREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7973','GJERDINGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7976','KONGSMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7977','HØYLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7980','TERRÅK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7981','HARANGSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7982','BINDALSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7985','FOLDEREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7990','NAUSTBUKTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7993','GUTVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('7994','LEKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8001','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8002','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8003','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8004','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8005','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8006','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8007','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8008','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8009','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8010','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8011','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8012','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8013','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8014','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8015','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8016','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8019','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8020','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8021','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8022','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8023','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8026','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8027','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8028','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8029','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8030','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8031','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8032','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8037','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8038','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8039','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8041','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8047','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8048','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8049','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8050','TVERLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8056','SALTSTRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8058','TVERLANDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8063','VÆRØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8064','RØST')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8070','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8071','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8072','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8073','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8074','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8075','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8076','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8079','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8084','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8086','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8087','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8088','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8089','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8091','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8092','BODØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8093','KJERRINGØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8094','FLEINVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8095','HELLIGVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8096','BLIKSVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8097','GIVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8098','LANDEGODE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8099','JAN MAYEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8100','MISVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8102','SKJERSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8103','BREIVIK I SALTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8108','MISVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8110','MOLDJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8114','TOLLÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8118','MOLDJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8120','NYGÅRDSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8128','YTRE BEIARN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8130','SANDHORNØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8135','SØRARNØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8136','NORDARNØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8138','INNDYR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8140','INNDYR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8145','STORVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8146','REIPÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8149','NEVERDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8150','ØRNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8151','ØRNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8157','MELØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8158','BOLGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8159','STØTT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8160','GLOMFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8161','GLOMFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8168','ENGAVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8170','ENGAVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8171','SVARTISEN GÅRD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8178','HALSA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8181','MYKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8182','MELFJORDBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8184','ÅGSKARDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8185','VÅGAHOLMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8186','TJONGSFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8187','JEKTVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8188','NORDVERNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8189','GJERSVIKGRENDA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8190','SØRFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8193','RØDØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8195','GJERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8196','SELSØYVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8197','STORSELSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8198','NORDNESØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8200','FAUSKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8201','FAUSKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8202','FAUSKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8205','FAUSKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8210','FAUSKE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8215','VALNESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8220','RØSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8226','STRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8230','SULITJELMA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8231','SULITJELMA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8232','STRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8233','VALNESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8250','ROGNAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8251','ROGNAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8255','RØKLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8256','RØKLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8260','INNHAVET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8261','INNHAVET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8264','ENGAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8266','MØRSVIKBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8270','DRAG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8271','DRAG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8273','NEVERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8274','MUSKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8275','STORJORD I TYSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8276','ULVSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8281','LEINESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8283','LEINESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8285','LEINES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8286','NORDFOLD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8288','BOGØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8289','VÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8290','SKUTVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8294','HAMARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8297','TRANØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8298','HAMARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8300','SVOLVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8301','SVOLVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8305','SVOLVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8309','KABELVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8310','KABELVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8311','HENNINGSVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8312','HENNINGSVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8313','KLEPPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8314','GIMSØYSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8315','LAUKVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8316','LAUPSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8317','STRØNSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8320','SKROVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8322','BRETTESNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8323','STORFJELL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8324','DIGERMULEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8325','TENGELFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8328','STOREMOLLA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8340','STAMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8352','SENNESVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8360','BØSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8370','LEKNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8372','GRAVDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8373','BALLSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8375','LEKNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8376','LEKNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8377','GRAVDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8378','STAMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8380','RAMBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8382','NAPP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8384','SUND I LOFOTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8387','FREDVANG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8388','RAMBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8390','REINE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8392','SØRVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8393','SØRVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8398','REINE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8400','SORTLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8401','SORTLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8405','SORTLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8407','GODFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8408','SANDSET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8409','GULLESFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8413','KVITNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8414','HENNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8415','HENNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8426','BARKESTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8428','TUNSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8430','MYRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8432','ALSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8438','STØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8439','MYRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8445','MELBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8446','MELBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8447','LONKAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8448','MYRLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8450','STOKMARKNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8452','STOKMARKNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8455','STOKMARKNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8459','MELBU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8465','STRAUMSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8469','BØ I VESTERÅLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8470','BØ I VESTERÅLEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8475','STRAUMSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8480','ANDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8481','BLEIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8483','ANDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8484','RISØYHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8485','DVERBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8487','BØGARD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8488','NØSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8489','NORDMELA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8493','RISØYHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8501','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8502','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8503','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8504','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8505','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8506','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8507','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8508','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8509','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8510','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8511','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8512','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8513','ANKENESSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8514','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8515','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8516','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8517','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8518','NARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8520','ANKENESSTRAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8522','BEISFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8523','ELVEGARD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8530','BJERKVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8531','BJERKVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8533','BOGEN I OFOTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8534','LILAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8535','TÅRSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8536','EVENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8539','BOGEN I OFOTEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8540','BALLANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8542','KJELDEBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8543','KJELDEBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8546','BALLANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8550','LØDINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8551','LØDINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8581','VESTBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8587','STORÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8590','KJØPSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8591','KJØPSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8601','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8602','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8603','GRUBHEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8604','SELFORS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8605','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8606','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8607','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8608','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8609','YTTEREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8610','GRUBHEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8611','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8613','SELFORS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8614','YTTEREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8615','SKONSENG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8616','BÅSMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8617','DALSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8618','ANDFISKÅ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8622','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8624','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8626','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8629','SVARTISDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8630','STORFORSHEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8634','MO I RANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8635','POLARSIRKELEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8638','STORFORSHEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8640','HEMNESBERGET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8641','HEMNESBERGET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8642','FINNEIDFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8643','BJERKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8646','KORGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8647','BLEIKVASSLIA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8648','KORGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8651','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8654','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8655','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8656','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8657','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8658','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8659','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8661','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8663','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8664','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8665','MOSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8672','ELSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8679','SUNDØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8680','TROFORS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8681','TROFORS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8690','HATTFJELLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8691','HATTFJELLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8700','NESNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8701','NESNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8720','VIKHOLMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8723','HUSBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8724','SAURA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8725','UTSKARPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8726','UTSKARPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8730','BRATLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8732','ALDRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8733','STUVLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8735','STOKKVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8740','NORD-SOLVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8742','SELVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8743','INDRE KVARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8750','TONNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8752','KONSVIKOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8753','KONSVIKOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8762','SLENESET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8764','LOVUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8766','LURØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8767','LURØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8770','TRÆNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8800','SANDNESSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8801','SANDNESSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8805','SANDNESSJØEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8813','KOPARDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8820','DØNNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8826','NORDØYVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8827','DØNNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8830','VANDVE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8842','BRASØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8844','SANDVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8850','HERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8851','HERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8852','HERØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8854','AUSTBØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8860','TJØTTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8865','TRO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8870','VISTHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8880','BÆRØYVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8883','HUSVIKA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8890','LEIRFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8891','LEIRFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8900','BRØNNØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8901','BRØNNØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8905','BRØNNØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8910','BRØNNØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8920','SØMNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8921','SØMNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8922','SØMNA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8960','HOMMELSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8961','HOMMELSTØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8976','VEVELSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8978','HESSTUN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8980','VEGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8981','VEGA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('8985','YLVINGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9001','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9002','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9003','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9006','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9007','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9008','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9009','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9010','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9011','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9012','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9013','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9014','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9015','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9016','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9017','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9018','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9019','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9020','TROMSDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9022','KROKELVDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9024','TOMASJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9027','RAMFJORDBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9030','SJURSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9034','OLDERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9037','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9038','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9040','NORDKJOSBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9042','LAKSVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9043','JØVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9046','OTEREN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9049','NORDKJOSBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9050','STORSTEINNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9054','MALANGSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9055','MEISTERVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9056','MORTENHALS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9057','VIKRAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9059','STORSTEINNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9060','LYNGSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9062','FURUFLATEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9064','SVENSBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9068','NORD-LENANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9069','LYNGSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9100','KVALØYSLETTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9103','SKULSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9106','STRAUMSBUKTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9107','TROMVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9110','SOMMARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9118','BRENSHOLMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9119','SOMMARØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9120','VENGSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9128','TUSSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9130','HANSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9131','KÅRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9132','STAKKVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9134','HANSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9135','VANNVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9136','VANNAREID')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9137','VANNVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9138','KARLSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9140','REBBENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9141','MJØLVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9142','SKIBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9143','SKIBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9144','SAMUELSBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9146','OLDERDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9147','BIRTAVARRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9148','OLDERDALEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9151','STORSLETT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9152','SØRKJOSEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9153','ROTSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9156','STORSLETT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9159','HAVNNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9161','BURFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9162','SØRSTRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9163','JØKELFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9164','KVÆNANGSFJELLET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9169','BURFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9170','LONGYEARBYEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9171','LONGYEARBYEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9172','ISFJORD PÅ SVALBARD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9173','NY-ÅLESUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9174','HOPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9175','SVEAGRUVA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9176','BJØRNØYA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9177','HORNSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9178','BARENTSBURG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9179','PYRAMIDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9180','SKJERVØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9181','HAMNEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9182','SEGLVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9184','REINFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9185','SPILDRA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9186','ANDSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9189','SKJERVØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9190','AKKARVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9192','ARNØYHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9193','NIKKEBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9194','LAUKSLETTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9195','ÅRVIKSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9197','ULØYBUKT')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9251','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9252','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9253','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9254','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9255','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9256','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9257','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9258','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9259','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9260','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9261','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9262','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9263','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9265','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9266','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9267','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9268','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9269','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9270','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9271','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9272','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9273','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9274','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9275','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9276','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9277','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9278','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9279','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9280','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9281','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9282','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9283','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9284','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9285','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9286','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9287','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9288','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9290','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9291','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9292','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9293','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9294','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9295','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9296','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9297','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9298','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9299','TROMSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9300','FINNSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9302','ROSSFJORDSTRAUMEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9303','SILSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9304','VANGSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9305','FINNSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9306','FINNSNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9310','SØRREISA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9311','BRØSTADBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9315','SØRREISA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9316','BRØSTADBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9321','MOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9322','KARLSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9325','BARDUFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9326','BARDUFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9327','BARDUFOSS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9329','MOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9334','ØVERBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9335','ØVERBYGD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9336','RUNDHAUG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9350','SJØVEGAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9355','SJØVEGAN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9360','BARDU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9365','BARDU')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9370','SILSAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9372','GIBOSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9373','BOTNHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9379','GRYLLEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9380','GRYLLEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9381','TORSKEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9382','GIBOSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9384','SKALAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9385','SKALAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9386','SENJAHOPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9387','SENJAHOPEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9388','FJORDGARD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9389','HUSØY I SENJA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9391','STONGLANDSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9392','STONGLANDSEIDET')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9393','FLAKSTADVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9395','KALDFARNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9402','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9403','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9404','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9405','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9406','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9407','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9408','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9409','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9411','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9414','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9415','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9419','SØRVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9420','LUNDENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9423','GRØTAVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9424','KJØTTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9425','SANDSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9426','BJARKØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9427','MELØYVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9430','SANDTORG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9436','KONGSVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9439','EVENSKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9440','EVENSKJER')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9441','FJELLDAL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9442','RAMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9443','MYKLEBOSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9444','HOL I TJELDSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9445','TOVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9446','GROVFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9447','GROVFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9448','RAMSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9450','HAMNVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9451','HAMNVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9453','KRÅKRØHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9454','ÅNSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9455','ENGENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9465','TENNEVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9466','TENNEVOLL')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9470','GRATANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9471','GRATANGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9475','BORKENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9476','BORKENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9477','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9478','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9479','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9480','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9481','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9482','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9483','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9484','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9485','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9486','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9487','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9488','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9489','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9490','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9491','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9496','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9497','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9498','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9499','HARSTAD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9501','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9502','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9503','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9504','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9505','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9506','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9507','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9508','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9509','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9510','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9511','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9512','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9513','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9514','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9515','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9516','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9517','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9518','ALTA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9519','KVIBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9520','KAUTOKEINO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9521','KAUTOKEINO')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9525','MAZE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9526','SUOLOVUOPMI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9531','KVALFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9532','HAKKSTABBEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9533','KONGSHUS')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9536','KORSFJORDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9540','TALVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9545','LANGFJORDBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9550','ØKSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9551','ØKSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9580','BERGSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9582','NUVSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9583','LANGFJORDHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9584','SØR-TVERRFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9585','SANDLAND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9586','LOPPA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9587','SKAVNAKK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9590','HASVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9593','BREIVIKBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9595','SØRVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9600','HAMMERFEST')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9609','HØNSEBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9610','RYPEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9613','HAMMERFEST')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9615','HAMMERFEST')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9616','HAMMERFEST')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9620','KVALSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9621','KVALSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9624','REVSNESHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9650','AKKARFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9653','HELLEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9657','KÅRHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9663','SKARVFJORDHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9664','SANDØYBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9670','TUFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9672','INGØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9690','HAVØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9691','HAVØYSUND')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9692','MÅSØY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9700','LAKSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9709','PORSANGMOEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9710','INDRE BILLEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9711','LAKSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9712','LAKSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9713','RUSSENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9714','SNEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9715','KOKELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9716','BØRSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9717','VEIDNESKLUBBEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9722','SKOGANVARRE')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9730','KARASJOK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9735','KARASJOK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9740','LEBESBY')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9742','KUNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9750','HONNINGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9751','HONNINGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9755','HONNINGSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9760','NORDVÅGEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9763','SKARSVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9764','NORDKAPP')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9765','GJESVÆR')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9768','REPVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9770','MEHAMN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9771','SKJÅNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9772','LANGFJORDNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9773','NERVEI')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9775','GAMVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9782','DYFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9783','NORDMANNSET I LAKSEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9790','KJØLLEFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9800','VADSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9802','VESTRE JAKOBSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9810','VESTRE JAKOBSELV')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9811','VADSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9815','VADSØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9820','VARANGERBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9826','SIRMA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9840','VARANGERBOTN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9845','TANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9846','TANA')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9900','KIRKENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9910','BJØRNEVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9912','HESSENG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9914','BJØRNEVATN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9915','KIRKENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9916','HESSENG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9917','KIRKENES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9925','SVANVIK')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9930','NEIDEN')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9934','BUGØYFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9935','BUGØYNES')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9950','VARDØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9951','VARDØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9952','VARDØ')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9960','KIBERG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9980','BERLEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9981','BERLEVÅG')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9982','KONGSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9990','BÅTSFJORD')", $connection) or die(mysql_error());
		mysql_query("INSERT INTO `".$table_prefix."zipcodemap` VALUES ('9991','BÅTSFJORD')", $connection) or die(mysql_error());
	}	

	# Create the basic configuration file in a session variable
	$_SESSION['baseconf'] = '<?php
if (!defined(\'IN_AKKAR\')) {
	exit(\'Access violation.\');
}

// This file generated automatically by AKKAR.
// Do not edit unless you know what you\'re doing.

$sqlserver = \''.$_POST['sqlhost'].'\';
$sqlbase = \''.$_POST['sqlbase'].'\';
$sqluser = \''.$_POST['sqluser'].'\';
$sqlpasswd = \''.$_POST['sqlpassword'].'\';
$table_prefix = \''.$table_prefix.'\';

?>
';
	if (is_writeable('./conf')) {
		# Yay, we can write to the config-dir - let's put the file in there 
		$fp = fopen('./conf/baseconf.php', 'w');
		fwrite($fp, $_SESSION['baseconf']);
		fclose($fp);
		
		# All done, so it's safe to delete install.php and upgrade.php
		$delete_self = true;
		
		# Tell the user we're done as well
		echo '
			<table align="center" width="60%">
				<tr>
					<td>
				<h2 align="center"><span class="green">Installation Complete</span></h2>
				<h5>AKKAR is installed and ready for use.
				<br>Note that <em>install.php</em> and <em>upgrade.php</em> in the AKKAR-directory MUST be deleted before AKKAR can be used.
				<br><br>
				<br>The first thing you want to do after logging in is head on to the Configuration-screen under the Administration section and complete the configuration of AKKAR.
				<br>
				<br>You should also run the Selftest in the Administration-section. This will check any file and directory access-rights AKKAR needs to operate.
				</h5>
					</td>
				</tr>
				<tr>
					<td align="center">
						<button type="button" onClick="javascript:window.location=\'./\';">To AKKAR</button>
					</td>
				</tr>
			</table>
		';
	} else {
		# Configuration-dir wasn't writeable, so we need to tell the user how to manually place the file
		echo '
			<table align="center" width="60%">
				<tr>
					<td>
				<h2 align="center"><span class="green">Installation Complete</span></h2>
				<br><br>
				<h5>AKKAR was unable to create the file containing the basic configuration. This file is required for AKKAR to operate. Click "Download" below to download it, and place it in the <em>conf/</em> subdirectory of the directory where you installed AKKAR - make sure you name the file </em>baseconf.php</em>. Once it\'s done, you can click "Go to AKKAR" below.
				<br>
				<br>Note that <em>install.php</em> and <em>upgrade.php</em> in the AKKAR-directory MUST be deleted before AKKAR can be used.
				<br>
				<br>When the configuration-file is in place, the first thing you want to do after logging in is head on to the Configuration-screen under the Administration section and complete the configuration of AKKAR.
				<br>
				<br>You should also run the Selftest in the Administration-section. This will check any file and directory access-rights AKKAR needs to operate.
				</h5>
					</td>
				</tr>
				<tr>
					<td align="center">
				<button type="button" onClick="javascript:window.location=\'install.php?get_config=yes\';">Download</button>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center">
						<button type="button" onClick="javascript:window.location=\'./\';">Go to AKKAR</button>
					</td>
				</tr>
			</table>
		';
	}
	
	if ($_POST['notify_author']) {
		# This will email the author (yep, c'est moi) to let him know an installation has taken place.
		# No sensitive information is sent (although it would be possible to trace the mail to the originating server, so if you're extra paranoid and think I'm a scary guy, you don't want to do this.

		mail(
			'Roy W. Andersen <ensnared@gmail.com>',
			'[AKKAR Install notification]',
			"This is an automatic email to notify of AKKAR having been installed somewhere by someone.\r\n\r\n-- \r\nAKKAR Install Script",
			'FROM: AKKAR Install Script <ensnared@gmail.com>'
		);
	}
	
	if ($delete_self) {
		# Delete the upgrade and/or install-scripts if Bob's our uncle
		if (is_writeable('install.php')) {
			unlink('install.php');
		}
		if (is_writeable('upgrade.php')) {
			unlink('upgrade.php');
		}
	}
}

?>
