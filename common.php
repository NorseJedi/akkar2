<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                common.php                               #
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

if (!defined('IN_AKKAR')) {
	exit('Access violation');
}

# Turn off output-compression since we use output buffering instead - having both will create double compression, which is useless and makes for funky results...
ini_set('zlib.output_compression', false);

# Set the error-reporting to ignore notices. This is default on *nix installations of PHP, but apparently not in Windows...
error_reporting(E_ALL ^ E_NOTICE);

# Uncomment the next line and set a name if you want the sessiondata to be used on multiple sites on the server (for example to require users to be logged in to AKKAR to access other private areas of a website on the server
#session_name('AKKAR');

# Start the session
session_start();

# Set the time the page creation started - used to show the total creation time at the bottom
if (!$_SESSION['page_timestart'] && !$_REQUEST['infowindow']) {
	$_SESSION['page_timestart'] = microtime();
}

# Activate output compression unless otherwise requested, like when downloading a file since all such downloads are read by PHP and not sent directly to the client
if (!$_REQUEST['nozip']) {
	@ob_start(ob_gzhandler);
}


# Build and set the include-path - dir- and path-separators are different in Win and *nix.
$ds = DIRECTORY_SEPARATOR;
$ps = PATH_SEPARATOR;
ini_set('include_path', '.'.$ps.'.'.$ds.'conf'.$ds.''.$ps.'.'.$ds.'templates'.$ds.''.$ps.'.'.$ds.'scripts'.$ds.''.$ps.'.'.$ds.'hjelp'.$ds.''.$ps . ini_get('include_path'));

# Deactivate browser caching of the content
header('Cache-Control: no-cache, max-age=0, must-revalidate');

# Check if this is a new install or an upgrade
if (file_exists('upgrade.php') || file_exists('install.php')) {
	$_SESSION = array();
	if (file_exists('conf/baseconf.php')) {
		# We have a config-file, so we're gonna upgrade
		header('Location: upgrade.php');
		ob_end_flush();
		exit();
	} else {
		# No config-file. Let's install!
		header('Location: install.php');
		ob_end_flush();
		exit();
	}
# Check if this is a fresh tree checked out from SVN and provide installation instructions.
} elseif ((!file_exists('conf/baseconf.php')) && ((file_exists('dist_files/install.php') || file_exists('dist_files/upgrade.php')))) {
	echo '
		<p>You seem to be trying to install or upgrade a version of AKKAR checked out from the subversion repository. In order to proceed, you need to do one of the following:</p>
		<ul>
			<li><b>If you\'re doing a fresh install:</b> Move the file <em>install.php</em> located in the <em>dist_files</em> directory into the main AKKAR directory.</li>
			<li><b>If you\'re doing an upgrade:</b> Move the file <em>upgrade.php</em> located in the <em>dist_files</em> directory into the main AKKAR directory.</li>
		</ul>
		<p>Once this is done, delete the dist_files directory, or atleast delete the two files listed above from it. You can then refresh this page to continue the installation or upgrade.</p> 
	';
	ob_end_flush();
	exit();
}

# Emulate register_globals off in case it's on (it should be off in php.ini but we'll do our best anyway).
if ((ini_get('register_globals') == 1) || ((strtolower(ini_get('register_globals')) == 'on'))){
	$superglobals = array($_SERVER, $_ENV, $_FILES, $_COOKIE, $_POST, $_GET);
	if (isset($_SESSION)) {
		array_unshift($superglobals, $_SESSION);
	}
	foreach ($superglobals as $superglobal) {
		foreach ($superglobal as $global => $value) {
			unset($GLOBALS[$global]);
		}
	}
	ini_set('register_globals', false);
}

# If we can't find or access the config-file we have to terminate and show the user how to manually write a new one. This shouldn't ever happen, but you just never know...
if (!is_readable('conf/baseconf.php')) {
	$_SESSION = array();
	echo '<div align="center">
		<h3>The main configuration-file <em>conf/baseconf.php</em> is missing or not accessible to AKKAR.<br>The system can not operate without it.</h3>
		<p>To create a new configuration-file, copy the text below and fill in the values. Save the file as <em>baseconf.php</em> in the <em>conf/</em> subdirectory of <strong>AKKAR</strong>.</p>
		<textarea cols="45" rows="12" readonly><?php
if (!defined(\'IN_AKKAR\')) {
	exit(\'Access violation.\');
}

$sqlserver = \'\';
$sqlbase = \'\';
$sqluser = \'\';
$sqlpasswd = \'\';
$table_prefix = \'\';

?></textarea></div>
	';

	ob_end_flush();
	exit();
}

# Turn on magic quotes
ini_set('magic_quotes_gpc', 'On');

# Make sure we produce validating links with &amp; instead of &
ini_set('arg_separator', '&amp;');

# Turn off magic_quotes_runtime
set_magic_quotes_runtime(0);

# Traverse the all the REQUEST variables and terminate everything using slashes if magic_quotes_gpc is set to 'Off'
if (!get_magic_quotes_gpc()) {
	if (is_array($_GET)) {
		while (list($k, $v) = each($_GET)) {
			if (is_array($_GET[$k])) {
				while (list($k2, $v2) = each($_GET[$k])) {
					$_GET[$k][$k2] = addslashes($v2);
				}
				@reset($_GET[$k]);
			} else {
				$_GET[$k] = addslashes($v);
			}
		}
		@reset($_GET);
	}

	if (is_array($_POST)) {
		while (list($k, $v) = each($_POST))	{
			if (is_array($_POST[$k])) {
				while (list($k2, $v2) = each($_POST[$k])) {
					$_POST[$k][$k2] = addslashes($v2);
				}
				@reset($_POST[$k]);
			} else {
				$_POST[$k] = addslashes($v);
			}
		}
		@reset($_POST);
	}

	if (is_array($_COOKIE) ) {
		while (list($k, $v) = each($_COOKIE)) {
			if (is_array($_COOKIE[$k]))	{
				while (list($k2, $v2) = each($_COOKIE[$k])) {
					$_COOKIE[$k][$k2] = addslashes($v2);
				}
				@reset($_COOKIE[$k]);
			} else {
				$_COOKIE[$k] = addslashes($v);
			}
		}
		@reset($_COOKIE);
	}
}

# Load the global functions
include_once('scripts/functions.php');

# Load the session variables used for sorting and listings
include_once('scripts/sessionvars.php');

# Load the basic configuration
include_once('conf/baseconf.php');

# Load the SQL functions
include_once('scripts/mysql_functions.php');


# Traverse any submissions and remove all the breaking characters put there by MS Word using the convert_funky_letters() function defined in functions.php
if (is_array($_GET)) {
	while (list($k, $v) = each($_GET)) {
		if (is_array($_GET[$k])) {
			while (list($k2, $v2) = each($_GET[$k])) {
				$_GET[$k][$k2] = convert_funky_letters($v2);
			}
			@reset($_GET[$k]);
		} else {
			$_GET[$k] = convert_funky_letters($v);
		}
	}
	@reset($_GET);
}

if (is_array($_POST)) {
	while (list($k, $v) = each($_POST))	{
		if (is_array($_POST[$k])) {
			while (list($k2, $v2) = each($_POST[$k])) {
				$_POST[$k][$k2] = convert_funky_letters($v2);
			}
			@reset($_POST[$k]);
		} else {
			$_POST[$k] = convert_funky_letters($v);
		}
	}
	@reset($_POST);
}
if (is_array($_COOKIE) ) {
	while (list($k, $v) = each($_COOKIE)) {
		if (is_array($_COOKIE[$k]))	{
			while (list($k2, $v2) = each($_COOKIE[$k])) {
				$_COOKIE[$k][$k2] = convert_funky_letters($v2);
			}
			@reset($_COOKIE[$k]);
		} else {
			$_COOKIE[$k] = convert_funky_letters($v);
		}
	}
	@reset($_COOKIE);
}


# Get the rest of the configuration and put it into the $config[] array
$config = get_configuration();

# Legacy from older versions where an upgraded-flag was set to show it had been upgraded. Stupid design, obviosuly, which is why we no longer do this ;)
if ($config['upgraded']) {
	mysql_query("DELETE FROM ".$table_prefix."_config WHERE name='upgraded'", $connection);
}

# Sort the configuration array (we don't really need this since the configuration-page isn't generated dynamically anymore, but it doesn't hurt anyway)
ksort($config);

# Get the images used based by the style or the default images.
$styleimages = get_style_images();

if ($_GET['lang'] && is_file('lang/'.$_GET['lang'].'.php')) {
	# Allow for on-the-fly language-setting, handy for checking different languages
	$config['lang'] = $_GET['lang']; 
} elseif (!$config['lang']) {
	# Set language to english if it's not set (like when upgrading from a pre-2.2 release)
	$config['lang'] = 'eng';
}

# Set session lifetime to that defined in the configuration
ini_set('session.gc_maxlifetime', $config['autologout']);

# Load the appropriate language-file
include_once('lang/'.$config['lang'].'.php');
if(is_windows()) {
	$LANG['locale'] = substr($LANG['locale'], 0, 2);
}
setlocale(LC_ALL, $LANG['locale']);

# Ensure that there is at least one operative admin user.
ensure_admin();

# Check wether we came from the character edit page, and if so, unlock the character for editing again.
$ref = explode('?', basename($_SERVER['HTTP_REFERER']));
if (($ref[0] == 'editrolle.php') && (basename($_SERVER['PHP_SELF']) != 'editrolle.php') && (!$_REQUEST['utskrift'])) {
	$ref[1] = 'tmp_'.$ref[1];
	$ref[1] = str_replace('&', '&tmp_', $ref[1]);
	parse_str($ref[1]);
	$locked = check_lock_rolle($tmp_rolle_id, $tmp_spill_id);
	if ($locked[1] == $_SESSION['person_id']) {
		unlock_rolle($tmp_rolle_id, $tmp_spill_id);
		$_SESSION['message'] = $_SESSION['message'].'<br>'.$LANG['MESSAGE']['characterlock_removed'];
	}
} elseif (($ref[0] == 'editrolleforslag.php') && (basename($_SERVER['PHP_SELF']) != 'editrolleforslag.php') && (!$_REQUEST['utskrift'])) {
	$ref[1] = 'tmp_'.$ref[1];
	$ref[1] = str_replace('&', '&tmp_', $ref[1]);
	parse_str($ref[1]);
	$locked = check_lock_rolleforslag($tmp_rolle_id, $tmp_spill_id);
	if ($locked[1] == $_SESSION['person_id']) {
		unlock_rolleforslag($tmp_rolle_id, $tmp_spill_id);
		$_SESSION['message'] = $_SESSION['message'].'<br>'.$LANG['MESSAGE']['characterlock_removed'];
	}
}
unset($ref, $locked, $tmp_rolle_id, $tmp_spill_id);


# Find the name of the current game
if ((!$_REQUEST['spill_id']) && (!$spill_id)) {
# Set the $spill and $spillnavn variables if no current game is selected. This is more of a legacy quirk than anything else.
	$spill_id = 0;
	$spillnavn = '';
	$spill = '';
} else {
# Get the game from the db and set the proper variables
	if (!$spill_id) {
		$spill_id = $_REQUEST['spill_id'];
	}
	$spillnavn = get_spillinfo($spill_id);
	$spillnavn = $spillnavn['navn'];
	$spill = strtolower(strtr($spillnavn, ' ', '_'));
}

# Lock the user if too many failed logins have been attempted.
if (($_SESSION['failed_attempt']) && ($_SESSION['failed_attempt'] >= $config['max_login_attempts'])) {
	$accounts = explode(', ', $_SESSION['failed_usernames']);
	foreach ($accounts as $brukernavn) {
		$brukernavn = trim($brukernavn);

		// If the last admin user is locked, we're in trouble.
		$brukersql = mysql_query("SELECT person_id FROM `".$table_prefix."brukere` WHERE brukernavn='".$brukernavn."'", $connection) or exit(mysql_error());
		$tuple = mysql_fetch_row($brukersql);
		if ($tuple && !is_last_admin($tuple[0])) {
			mysql_query("UPDATE `".$table_prefix."brukere` SET locked=1 WHERE brukernavn='".$brukernavn."'", $connection) or exit(mysql_error());
		}
	}
	# Send an email to the administrator
	security_warning($_SESSION['failed_attempt']." ".$LANG['MESSAGE']['failed_logins_from']." ".$_SERVER['REMOTE_ADDR']."\r\n\r\n".$LANG['MISC']['username'].": ".$_SESSION['failed_usernames']."\r\n\r\n".$LANG['MISC']['password'].": ".$_SESSION['failed_passwords']."\r\n\r\n".$LANG['MESSAGE']['all_matching_accounts_locked']);
	$_SESSION['lockout'] = true;
	unset($_SESSION['failed_attempt']);
	unset($_SESSION['failed_usernames']);
	unset($_SESSION['failed_passwords']);
}

# Clean up the tmp-directory, removing old files and such
clean_tmp_dir();

# Attempt and verify auto-login unless we're in the process of logging in or out
if ($_POST['logout']) {
	# User is logging out
	do_logout();
} elseif (($_SESSION['level'] > 0) && ($_SESSION['level'] < 5)) {
	// User is a player, and is not allowed to log in to AKKAR
	deny_login();
} elseif (is_logged_in()) {
	# User is already logged in
	activity_log();
} elseif ($_COOKIE[$config['ckprefix'].'_data']) {
	# User is returning for auto-login
	do_relogin();
} elseif ($_SESSION['lockout']) {
	# User has just been locked out
	setcookie($config['ckprefix'].'_data', '', 1, $config['ckdir']);
	include('header.php');
 	echo '<h4 align="center">'.$LANG['MESSAGE']['failed_logins'].'<br>'.$LANG['MESSAGE']['contact_admin'].'</h4>';
	include('footer.php');
	exit();
} elseif ($_POST['brukernavn'] && $_POST['passord']) {
	# User is trying to login manually
	if (!$_SESSION['failed_attempt']) {
		# First login-attempt, so we set the session variable
		$_SESSION['failed_attempt'] = 0;
	}
	do_login();
} elseif ($_SESSION['account_locked']) {
	# User account is locked
	setcookie($config['ckprefix'].'_data', '', 1, $config['ckdir']);
	include('header.php');
	echo '<h4 align="center">'.$LANG['MESSAGE']['locked_account'].'<br><a href="mailto:'.$config['akkar_admin_email'].'">'.$LANG['MESSAGE']['contact_admin'].'</a></h4>';
	include('footer.php');
	unset($_SESSION['account_locked']);
	exit();
} elseif (!is_logged_in() && (basename($_SERVER['PHP_SELF']) != 'login.php') && (basename($_SERVER['PHP_SELF']) != 'send_paamelding.php') && (basename($_SERVER['PHP_SELF']) != 'send_rolleforslag.php') && (basename($_SERVER['PHP_SELF']) != 'send_kombi.php')) {
	# User is required to login manually
	header('Location: login.php');
	exit();
}

# Set up variables used for some forwarding and form-submission
if (!$_REQUEST['infowindow']) {
	if (!$_SESSION['whereiam']) {
		$_SESSION['whereiam'] = basename($_SERVER['PHP_SELF']);
	}
	if (!$_SESSION['whereiwas'] || $_SESSION['whereiam'] != basename($_SERVER['PHP_SELF'])) {
		$_SESSION['whereiwas'] = $_SESSION['whereiam'];
	}
	$_SESSION['whereiam'] = basename($_SERVER['PHP_SELF']);
	
	$whereiwas = $_SESSION['whereiwas'];
	$whereiam = basename($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
}

# Define the marking of required form-fields. This will probably become customizeable through the configuration screen at some point. For now we're going with a large-ish asterisk
$mand_mark = '<span style="font-size: 13pt; font-weight: bold;vertical-align: middle;">*</span>';

?>
