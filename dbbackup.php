<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              dbbackup.php                               #
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

# Only administrators should be allowed to do anything here.
if (is_admin()) {
	if ($_REQUEST['backup']) {
		# We're instructed to do the backup
		if ($_REQUEST['full_backup']) {
			# Full backup - that's db, files and all except the actual scripts.
			this_might_take_a_while(); // Yep, it might... :)
			$filename = do_fullbackup();
			header('Content-Type: application/x-zip');
			header('Content-Disposition: attachment; filename='.basename($filename));
			readfile($filename);
			unlink($filename);
			exit();
		} else {
			# Just backup the database
			$filename = do_dbbackup();
			if ($_POST['gzip']) {
				$filename = gzcompressfile($filename);
				header('Content-Type: application/x-gzip');
			} else {
				header('Content-Type: text/plain');
			}
			header('Content-Disposition: attachment; filename='.basename($filename));
			readfile($filename);
			unlink($filename);
			exit();
		}
	} else {
		include('header.php');
		# Create the form to provide backup options
		echo '
			<script language="JavaScript" type="text/javascript">
				function check_backupform() {
					if (document.backupform.full_backup.checked == true) {
						document.backupform.gzip.checked = true;
						document.backupform.struktur.checked = false;
						document.backupform.gzip.disabled = true;
						document.backupform.struktur.disabled = true;
					} else {
						document.backupform.gzip.disabled = false;
						document.backupform.struktur.disabled = false;
					}
				}
			</script>
			<h2 align="center">'.$LANG['MISC']['backup'].'</h2>
			<br>
			<form name="backupform" action="./dbbackup.php" method="post">
			<input type="hidden" name="nozip" value="yes">
			<input type="hidden" name="backup" value="yes">
			<table align="center" cellpadding="3" cellspacing="0">
				<tr>
					<td class="highlight" colspan="3">'.$LANG['MISC']['preferences'].'</td>
				</tr>
				<tr>
					<td>'.$LANG['MISC']['compress_backup'].'</td>
					<td align="center"><input type="checkbox" name="gzip" checked disabled></td>
					<td>'.hjelp_icon($LANG['MISC']['compress_backup'],$LANG['HELPTIP']['compress_backup']).'</td>
				</tr>
				<tr>
					<td>'.$LANG['MISC']['dbstructure_only'].'</td>
					<td align="center"><input type="checkbox" name="struktur" disabled></td>
					<td>'.hjelp_icon($LANG['MISC']['dbstructure_only'],$LANG['HELPTIP']['dbstructure_only']).'</td>
				</tr>
				<tr>
					<td>'.$LANG['MISC']['additional_tables'].'</td>
					<td><input type="text" name="ekstratabeller" maxlength="255"></td>
					<td>'.hjelp_icon($LANG['MISC']['additional_tables'],$LANG['HELPTIP']['additional_tables']).'</td>
				</tr>
				<tr>
					<td>'.$LANG['MISC']['full_backup'].'</td>
					<td align="center"><input type="checkbox" name="full_backup" onClick="javascript:check_backupform();" checked></td>
					<td>'.hjelp_icon($LANG['MISC']['full_backup'],$LANG['HELPTIP']['full_backup']).'</td>
				</tr>
				<tr>
					<td align="center" colspan="3"><button type="submit">'.$LANG['MISC']['execute'].'</button></td>
				</tr>
			</table>
			</form>
		';
		include('footer.php');
	}
} else {
	# User is not an administrator - go away.
	header('Location: '.$whereiwas);
	exit();
}

?>
