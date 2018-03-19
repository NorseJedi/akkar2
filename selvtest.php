<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               selvtest.php                              #
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

include('header.php');

if (!is_dir($config['filsystembane'])) {
	$errors[] = $LANG['ERROR']['noexist_filesystem_path'];
}
if (!is_writeable ($config['filsystembane'])) {
	$errors[] = $LANG['ERROR']['nowrite_filesystem'];
}
if (!is_writeable ('images/personer')) {
	$errors[] = $LANG['ERROR']['nowrite_imagedir'];
}
if (!is_writeable ('tmp/')) {
	$errors[] = $LANG['ERROR']['nowrite_tmp'];
}

if ((ini_get('register_globals') == 1) || ((strtolower(ini_get('register_globals')) == 'on'))){
	$errors[] = $LANG['ERROR']['register_globals_enabled'];
}

if (is_dir ('includes/')) {
	$errors[] = str_replace('%s', '<em>includes/</em>', $LANG['ERROR']['obsolete_dir']);
}

if (is_file('templates/print_rolle.php')) {
	$errors[] = str_replace('%s', '<em>templates/print_rolle.php</em>', $LANG['ERROR']['obsolete_file']);
}

if (is_file('templates/print_person.php')) {
	$errors[] = str_replace('%s', '<em>templates/print_person.php</em>', $LANG['ERROR']['obsolete_file']);
}


if (!extension_loaded('iconv')) {
	$errors[] = str_replace('%s', '<em><a href="http://php.net/iconv" target="_blank">iconv</a></em>', $LANG['ERROR']['missing_extension_noncrit']);
}

if (!extension_loaded('exif')) {
	$errors[] = str_replace('%s', '<em><a href="http://php.net/exif" target="_blank">exif</a></em>', $LANG['ERROR']['missing_extension_noncrit']);
}

if (!extension_loaded('mbstring') && is_windows()) {
	$errors[] = str_replace('%s', '<em><a href="http://php.net/mbstring" target="_blank">mbstring</a></em>', $LANG['ERROR']['missing_extension_noncrit']);
}

if (!extension_loaded('calendar')) {
	$errors[] = str_replace('%s', '<em><a href="http://php.net/calendar" target="_blank">calendar</a></em>', $LANG['ERROR']['missing_extension_noncrit']);
}

if (!extension_loaded('gd')) {
	$errors[] = str_replace('%s', '<em><a href="http://php.net/gd" target="_blank">GD</a></em>', $LANG['ERROR']['missing_extension']);
}

if (!extension_loaded('zlib')) {
	$errors[] = str_replace('%s', '<em><a href="http://php.net/zlib" target="_blank">zlib</a></em>', $LANG['ERROR']['missing_extension']);
}

$filelist = array(
	'conf/baseconf.php',
	'help/help_dbbackup.php',
	'help/help_dbrestore.php',
	'help/help_editmal.php',
	'help/help_editoppgave.php',
	'help/help_filvedlegg.php',
	'help/help_kalender.php',
	'help/help_konfigurasjon.php',
	'help/help_maler.php',
	'help/help_oppgaver.php',
	'help/help_roller.php',
	'help/help_selvtest.php',
	'help/help_spill.php',
	'help/help_userinfo.php',
	'help/help_viskjentfolk.php',
	'help/help_vismal.php',
	'help/help_visspill.php',
	'help/no_help.php',
	'scripts/font/courier.php',
	'scripts/font/helveticabi.php',
	'scripts/font/helveticab.php',
	'scripts/font/helveticai.php',
	'scripts/font/helvetica.php',
	'scripts/font/symbol.php',
	'scripts/font/timesbi.php',
	'scripts/font/timesb.php',
	'scripts/font/timesi.php',
	'scripts/font/times.php',
	'scripts/font/zapfdingbats.php',
	'scripts/akkar_fpdf.lib.php',
	'scripts/animbuttons.js.php',
	'scripts/fpdf.lib.php',
	'scripts/functions.js',
	'scripts/functions.php',
	'scripts/js_vars.php',
	'scripts/mail_functions.php',
	'scripts/md5.js',
	'scripts/email_message.php',
	'scripts/mimetypes.php',
	'scripts/mysql_functions.php',
	'scripts/overlib.js',
	'scripts/overlib_draggable.js',
	'scripts/overlib_fade.js',
	'scripts/pclzip.lib.php',
	'scripts/pdf_functions.php',
	'scripts/rtf_functions.php',
	'scripts/sessionvars.php',
	'scripts/txt_functions.php',
	'templates/footer.php',
	'templates/form_edit_person.php',
	'templates/header.php',
	'templates/index.php',
	'templates/infofooter.php',
	'templates/infoheader.php',
	'templates/mainfooter.php',
	'templates/mainheader.php',
	'templates/printfooter.php',
	'templates/print_gruppe.php',
	'templates/printheader.php',
	'templates/print_oppgaver.php',
	'arrangorer.php',
	'betaling.php',
	'common.php',
	'dbbackup.php',
	'dbrestore.php',
	'download.php',
	'editbruker.php',
	'editdeadline.php',
	'editfil.php',
	'editgruppe.php',
	'editkjentfolk.php',
	'editkontakt.php',
	'editmal.php',
	'editnotat.php',
	'editoppgave.php',
	'editperson.php',
	'editplott.php',
	'editpaamelding.php',
	'editrolleforslag.php',
	'editrollekonsept.php',
	'editrolle.php',
	'editspill.php',
	'filsystem.php',
	'filvedlegg.php',
	'grupper.php',
	'hentroller.php',
	'historikk.php',
	'hjelp.php',
	'index.php',
	'kalender.php',
	'konfigurasjon.php',
	'kontakter.php',
	'login.php',
	'maler.php',
	'mkpaameldingskjema.php',
	'mkrolleskjema.php',
	'mugshots.php',
	'nav.php',
	'oppgaver.php',
	'plott.php',
	'paameldinger.php',
	'rollefordeling.php',
	'rolleforslag.php',
	'rollekonsept.php',
	'roller.php',
	'selvtest.php',
	'sendmail.php',
	'send_paamelding.php',
	'send_rolleforslag.php',
	'sendrollekonsept.php',
	'sendroller.php',
	'spillere.php',
	'spill.php',
	'userinfo.php',
	'utskrifter.php',
	'visfil.php',
	'visgruppe.php',
	'viskjentfolk.php',
	'viskontakt.php',
	'vismal.php',
	'visperson.php',
	'visplott.php',
	'vispaamelding.php',
	'visrolleforslag.php',
	'visrollekonsept.php',
	'visrolle.php',
	'visspill.php'
);

foreach ($filelist as $file) {
	if (!is_file($file)) {
		$errors[] = $LANG['ERROR']['file_missing'].': '.$file;
	}
}

if ($table_errors = check_tables()) {
	$errors = array_merge($errors, $table_errors);
}

if (!$errors) {
	echo '
		<div align="center">
		<br><br>
		<h3 align="center"><span class="green">'.$LANG['MISC']['no_errors_found'].'</span></h3>
		<br><br>
		<h4 align="center">'.$LANG['MESSAGE']['should_operate_normally'].'</h4>
		<br><br>
		<br><br>
		<button type="button" onClick="javascript:window.open(\'http://sourceforge.net/tracker/?group_id=126890&amp;atid=707124\');">'.$LANG['MISC']['submit_bug'].'</button>
		</div>
	';
} else {
	echo '
		<br><br>
		<h3 align="center"><span class="red">'.$LANG['MISC']['errors_found'].'</span></h3>
		<br><br>
		<h4>'.$LANG['MESSAGE']['these_errors_found'].':</h4>
		<ul>
	';
	foreach ($errors as $error) {
		echo '
			<li>'.$error.'</li>
		';
	}
	echo '
		</ul>
		<br><br>
		<div align="center">
		<button type="button" onClick="javascript:window.open(\'http://akkar.sourceforge.net/\');">'.$LANG['MISC']['visit_official_site'].'</button>
		</div>
	';
}
$extensions = get_loaded_extensions();
foreach ($extensions as $extension) {
	$loaded_extensions .= $extension.', ';
}
 
$loaded_extensions = substr(trim($loaded_extensions), 0, -1);
echo '<table cellpadding="0" class="bordered" style="margin-top: 2em;background-color: #ffffff; color: #000000;" width="80%" align="center">
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['os_version'].':</strong></td><td>'.php_uname().'<br />('.PHP_OS.')</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['php_version'].':</strong></td><td>PHP '.phpversion().'</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['php_extensions'].':</strong></td><td>'.$loaded_extensions.'</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['sql_server_version'].':</strong></td><td>MySQL '.mysql_get_server_info($connection).'</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['sql_client_version'].':</strong></td><td>MySQL '.mysql_get_client_info().'</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['sql_host_info'].':</strong></td><td>'.mysql_get_host_info($connection).'</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><strong>'.$LANG['MISC']['sql_protocol_version'].':</strong></td><td>'.mysql_get_proto_info($connection).'</td>
	</tr>
</table>';


include('footer.php');
?>
