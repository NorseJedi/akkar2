<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                upgrade.php                              #
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
define("IN_RASLAV", true); // Legacy definition in case any cavemen out there are still trying to upgrade from the ancient RAsLAV.

# Set the error-reporting to ignore notices. This is default on *nix installations of PHP, but apparently not in Windows...
error_reporting(E_ALL ^ E_NOTICE);

if (!file_exists('conf/baseconf.php')) {
	# No existing configfile found, so this isn't an upgrade. Let's try an install instead.
	header('Location: install.php');
	exit();
}

session_start();

# Provide the config file if AKKAR was unable to create it and have the user download it
if ($_REQUEST['get_config']) {
	header('Content-type: text/plain');
	header('Content-disposition: attachment; filename=baseconf.php');
	echo $_SESSION['newconf'];
	exit();
}

$ds = DIRECTORY_SEPARATOR;
$ps = PATH_SEPARATOR;
ini_set("include_path", ".".$ps.".".$ds."conf".$ds."".$ps.".".$ds."templates".$ds."".$ps.".".$ds."scripts".$ds."".$ps.".".$ds."hjelp".$ds."".$ps . ini_get("include_path"));
include_once('baseconf.php');
@$connection = mysql_pconnect($sqlserver, $sqluser, $sqlpasswd) or exit(mysql_error());
mysql_select_db($sqlbase);
include_once('functions.php');
include_once('mysql_functions.php');
$config = get_configuration();

# Version we're upgrading to
$akkar_version = '2.4.6';

# Find out if current version is 2.0 - this should only be the case for cavemen and such. Providing backwards compatibility can suck :P
if (@mysql_num_rows(@mysql_query("SELECT * FROM `".$table_prefix."config` WHERE name LIKE '%raslav_admin%'", $connection)) > 0) {
	$config['version'] = '2.0';
}

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
	<title>AKKAR Upgrade</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

';
if ((browsertype() == 'ie') && (is_file('styles/'.$config['style'].'/iestyle.css'))) {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/iestyle.css" type="text/css">';
} elseif ((browsertype() == 'opera') && (is_file('styles/'.$config['style'].'/operastyle.css'))) {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/operastyle.css" type="text/css">';
} else {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/style.css" type="text/css">';
}

if (is_file('styles/'.$config['style'].'/logo.png')) {
	$logo = 'styles/'.$config['style'].'/logo.png';
} elseif (is_file('styles/'.$config['style'].'/logo.jpg')) {
	$logo = 'styles/'.$config['style'].'/logo.jpg';
} elseif (is_file('styles/'.$config['style'].'/logo.gif')) {
	$logo = 'styles/'.$config['style'].'/logo.gif';
} else {
	$logo = 'styles/default/logo.png';
}
echo '
<link rel="StyleSheet" href="styles/'.$config['style'].'/common.css" type="text/css">
<link rel="icon" href="/images/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
	</head>
<body>
<table border="0" align="center">
	<tr>
		<td class="banner">
			<img src="'.$logo.'" alt="AKKAR">
		</td>
	</tr>
	<tr>
		<td class="maincol" style="padding-bottom: 20em;">
		<h1 style="padding-bottom: 1em;" align="center">'.$config['arrgruppenavn'].'</h1>
';

if ($config['version'] == $akkar_version) {
	echo '
		<div align="center">
		<h3>You have to delete <em>install.php</em> and <em>upgrade.php</em> from the AKKAR directory before the system will operate.</h3>
		<br><br><button type="button" onClick="javascript:window.location=\'./\';">OK</button>
		</div>
	';
	exit();
} else {
	echo '
		<h2 align="center">AKKAR Upgrade</h2>
		<h3 align="center">'.$config['version'].' -&gt; '.$akkar_version.'</h3>
';
}

if (!$_GET['do_upgrade']) {
	# First load, so we'll just show a "hi, this will upgrade, click OK" screen before we do anything messy.
	echo '
		<div align="center">
		<h4>It is strongly recomended that you back up your current installation before performing the upgrade.<br/>
		<br/>Click the button to start the upgrade.</h4>
		<br>
		<br>
		<button type="button" onClick="javascript:window.location=\'./upgrade.php?do_upgrade=yes\';">Upgrade</button>
		</td>
	';
} else {
	# Alright, let's do the upgrade
	echo '
			<br>
			<h4>Performing upgrade steps:</h4>
			<br>
	';
	
	# Figure out the version we're upgrading from and perform steps necessary
	switch ($config['version']) {
		case '2.0':
			# Wow, upgrading from RAsLAV 2.0 - lots of stuff needs to be done...
			echo '<p>Adding new tables...';
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
		
			mysql_query("DROP TABLE IF EXISTS `".$table_prefix."mugshots`", $connection) or exit(mysql_error());
			mysql_query("CREATE TABLE `".$table_prefix."mugshots` (
				person_id int(11) unsigned NOT NULL,
				image varchar(255) NOT NULL,
				type varchar(255),
				PRIMARY KEY (person_id, image)
			)", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
		
			
			echo '<p>Populating mugshot-table...';
			$personer = mysql_query("SELECT person_id, fornavn, etternavn, bilde FROM `".$table_prefix."personer` ORDER BY etternavn", $connection) or exit(mysql_error());
			for ($i = 0; $i < mysql_num_rows($personer); $i++) {
				$person = mysql_fetch_assoc($personer);
				if ($person['bilde']) {
					mysql_query("INSERT INTO `".$table_prefix."mugshots` VALUES ('".$person['person_id']."', '".$person['bilde']."', 'person')", $connection) or exit(mysql_error());
				}
				$fornavn = explode(" ", $person['fornavn']);
				$etternavn = explode(" ", $person['etternavn']);
				reset($fornavn);
				end($etternavn);
				$fn1 = strtolower(current($fornavn));
				$en1 = strtolower(current($etternavn));
				$fn2 = str_replace("�", "ae", str_replace("�", "oe", str_replace("�", "aa", $fn1)));
				$en2 = str_replace("�", "ae", str_replace("�", "oe", str_replace("�", "aa", $en1)));
				$handle = opendir(getcwd()."/images/personer");
				while (false !== ($file = readdir($handle))) {
					if ((is_int(strpos(strtolower($file), $fn1)) && is_int(strpos(strtolower($file), $en1))) || (is_int(strpos(strtolower($file), $fn2)) && is_int(strpos(strtolower($file), $en2)))) {
						@mysql_query("INSERT INTO `".$table_prefix."mugshots` VALUES ('".$person['person_id']."', '".$file."', 'person')", $connection) or exit(mysql_error());
					}
				}
				closedir($handle);
			}
			echo ' <span class="green">Done</span>.</p>';
		
		
			echo '<p>Altering existing tables...';
			mysql_query("ALTER TABLE `".$table_prefix."personer` ADD COLUMN kjonn tinytext AFTER alder", $connection) or exit(mysql_error());
			mysql_query("ALTER TABLE `".$table_prefix."filsystem` ADD COLUMN dir tinytext AFTER navn", $connection) or exit(mysql_error());
			mysql_query("ALTER TABLE `".$table_prefix."postnummer` CHANGE postnr zipcode varchar(25) NOT NULL", $connection) or exit(mysql_error());
			mysql_query("ALTER TABLE `".$table_prefix."postnummer` CHANGE sted region varchar(25)", $connection) or exit(mysql_error());
			mysql_query("ALTER TABLE `".$table_prefix."roller` MODIFY status int(10) unsigned NOT NULL DEFAULT '0'", $connection) or exit(mysql_error());
			mysql_query("RENAME TABLE `".$table_prefix."postnummer` TO `".$table_prefix."zipcodemap`", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
			
			echo '<p>Updating filesystem entries...';
			mysql_query("UPDATE `".$table_prefix."filsystem` SET dir='/'", $connection) or exit(mysql_error());
			$files = mysql_query("SELECT * FROM `".$table_prefix."filsystem`", $connection) or exit(mysql_error());
			for ($i = 0; $i < mysql_num_rows($files); $i++) {
				$file = mysql_fetch_array($files);
				$filetype = get_mime_type($file['navn']);
				mysql_query("UPDATE `".$table_prefix."filsystem` SET type='$filetype' WHERE fil_id='".$file['fil_id']."'", $connection) or exit(mysql_error());
			}
			echo ' <span class="green">Done</span>.</p>';
		
			echo '<p>Updating person entries...';
			mysql_query("UPDATE `".$table_prefix."personer` SET mailpref='email' WHERE mailpref='E-Mail'", $connection) or exit(mysql_error());
			mysql_query("UPDATE `".$table_prefix."personer` SET mailpref='post' WHERE mailpref='Post'", $connection) or exit(mysql_error());
			mysql_query("UPDATE `".$table_prefix."personer` SET type='arrangor' WHERE type!='spiller'", $connection) or exit(mysql_error());
			mysql_query("UPDATE `".$table_prefix."tabellmaler` SET type='paamelding' WHERE type!='rolle'", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
			
			echo '<p>Updating configuration entries...';
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('use_autoregion','0')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('motd','')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('paperformat','A4')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('lang','eng')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('primary_exportformat','pdf')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('allow_exportformat_override','0')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('fields_not_in_contacts_list', 'a:4:{i:0;s:10:\"kontakt_id\";i:1;s:11:\"beskrivelse\";i:2;s:7:\"notater\";i:3;s:5:\"bilde\";}')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('long_dateformat', '%A %d. %B %Y')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('medium_dateformat', '%d. %b %y')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('short_dateformat', '%d/%m-%y')", $connection) or exit(mysql_error());
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('send_security_warning','0')", $connection) or exit(mysql_error());
			mysql_query("UPDATE `".$table_prefix."config` SET name='akkar_admin_navn' WHERE name='raslav_admin_navn'", $connection) or exit(mysql_error());
			mysql_query("UPDATE `".$table_prefix."config` SET name='akkar_admin_email' WHERE name='raslav_admin_email'", $connection) or exit(mysql_error());
			mysql_query("DELETE FROM `".$table_prefix."config` WHERE name='banner'", $connection) or exit(mysql_error());
			mysql_query("DELETE FROM `".$table_prefix."config` WHERE name='bwbanner'", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
		
			echo '<p>Updating version-info...';
			@mysql_query("DELETE FROM `".$table_prefix."config` WHERE name='versjon'", $connection) or exit(mysql_error());
			$versioncheck = mysql_query("SELECT * FROM `".$table_prefix."config` WHERE name='version'", $connection) or exit(mysql_error());
			if (mysql_num_rows($versioncheck) > 0) {
				mysql_query("UPDATE `".$table_prefix."config` SET value='".$akkar_version."' WHERE name='version'", $connection) or exit(mysql_error());
			} else {
				mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('version','".$akkar_version."')", $connection) or exit(mysql_error());
			}
			echo ' <span class="green">Done</span>.</p>';
			
			$replace = array("IN_RASLAV" => "IN_AKKAR",
				"Moderen l�k" => "Access violation",
				"RAsLAV" => "AKKAR");
			$newconf = strtr(file_get_contents("conf/baseconf.php"), $replace);
			
			echo '<p>Updating basic config file...';
			if (is_writeable("conf/baseconf.php")) {
				$fp = fopen("conf/baseconf.php", "w");
				fwrite($fp, $newconf);
				fclose($fp);
				$delete_self = true;
				echo ' <span class="green">Done</span>.</p>';
			} else {
				echo ' <span class="red">Failed</span> - manual update required (read below)</p>';
				$_SESSION['newconf'] = $newconf;
				$delete_self = false;
				echo '
					<div align="center" style="padding-top: 3em;">
					<h3>Completed</h3>
					<br><br>
					<p>The basic configuration-file has changed slightly in this version and needs to be updated. AKKAR was unable to do this automatically, so you need to do it manually. The new file can be downloaded by clicking the button below.</p>
					<br><br>
					<button type="button" onClick="javascript:window.location=\'./upgrade.php?get_config=yes\';">Download</button>
					<br><br>
					<p>Download it and place it in the conf/ subdirectory replacing the existing <em>baseconf.php</em> file.</p>
					<br><br>
					<p>If you\'d rather edit the file yourself, just open your existing baseconf.php and replace <em>IN_RASLAV</em> with <em>IN_AKKAR</em> at the top of the file. No other changes are required.
					<br><br>
					<br><br>
					In addition, you MUST delete <em>upgrade.php</em> and <em>install.php</em> from the AKKAR directory before the system will operate. It is also strongly recommended that you delete the <em>includes</em> directory as it is no longer in use. Its contents have been moved to other directories</p>
					</div>
	
					</td>
					<tr>
						<td align="right"><a href="http://akkar.sourceforge.net/" target="_blank"><img src="images/akkar-powered.png" alt="AKKAR Powered"></a></td>
					</tr>
				</table>
				</body>
				</html>
				';
			}
			ob_end_flush();
			exit();
			# We break here since all changes are incorporated in the above operations. The rest is done for upgrades from any 2.2.x version 
			break;
		case '2.2.0':
		case '2.2.1':
			# Upgrading from 2.2.0 or 2.2.1 requires a couple of database updates...
			echo '<p>Correcting possible errors in stored data...';
			mysql_query("UPDATE `".$table_prefix."personer` SET mailpref='email' WHERE mailpref != 'Post'", $connection) or exit(mysql_error());
			mysql_query("UPDATE `".$table_prefix."personer` SET mailpref='post' WHERE mailpref = 'Post'", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
		case '2.2.2':
			# Upgrading from 2.2.0, 2.2.1 or 2.2.2 requires a couple of changes to the database structure...
			echo '<p>Altering tables...';
			mysql_query("ALTER TABLE `".$table_prefix."roller` MODIFY status int(10) unsigned NOT NULL DEFAULT '0'", $connection) or exit(mysql_error());
			mysql_query("ALTER TABLE `".$table_prefix."tabellmaler_data` ADD COLUMN intern int(1) unsigned NOT NULL DEFAULT '0'", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
		case '2.2.3':
		case '2.2.4':
		case '2.2.5':
			# Upgrading from 2.2.0, 2.2.1, 2.2.2, 2.2.3, 2.2.4 or 2.2.5 requires one configuration addition
			echo '<p>Updating configuration entries...';
			mysql_query("INSERT INTO `".$table_prefix."config` VALUES ('use_overlib_fade','1')", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
		case '2.4.0':
		case '2.4.1':
		case '2.4.2':
			# Upgrading from 2.2.0-2.4.2 requires a couple of changes to the database structure...
			echo '<p>Altering tables...';
			mysql_query("ALTER TABLE `".$table_prefix."tabellmaler_data` MODIFY extra longtext", $connection) or exit(mysql_error());
			mysql_query("ALTER TABLE `".$table_prefix."tabellmaler_data` MODIFY hjelp longtext", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
		case '2.4.3':
		case '2.4.4':
		case '2.4.5':
		default:
			echo '<p>Finalising upgrade...';
			# Yep, all upgrades needs a version-number update :)
			mysql_query("UPDATE `".$table_prefix."config` SET value='".$akkar_version."' WHERE name='version'", $connection) or exit(mysql_error());
			echo ' <span class="green">Done</span>.</p>';
	}
	if (!isset($delete_self)) {
		# All done, and nothing has specified we can't delete install.php and upgrade.php
		$delete_self = true;
	}

	# People love to be told everything's a-ok, so let's.
	echo '
		<div align="center" style="padding-top: 3em;">
		<h3>Upgrade complete.</h3>
		<br><br>
		<h5>You MUST delete <em>upgrade.php</em> and <em>install.php</em> from the AKKAR directory before the system will operate. This script may have already done it for you if it has the proper access-rights.
		<br><br><button type="button" onClick="javascript:window.location=\'./\';">To AKKAR</button>
		</div>
		</td>
	</tr>
	<tr>
		<td align="right"><a href="http://akkar.sourceforge.net/" target="_blank"><img src="images/akkar-powered.png" alt="AKKAR Powered"></a></td>
	</tr>
</table>
</body>
</html>
';

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
ob_end_flush();
?>
