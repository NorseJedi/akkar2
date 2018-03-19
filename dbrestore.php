<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               dbrestore.php                             #
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

if (!is_admin()) {
	# User is not an administrator - go away.
	header('Location: '.$whereiwas);
	exit();
}

if ($_FILES) {
	# Files have been uploaded. That means we're supposed to restore something.
	if ($_FILES['restore_fil']['error'] != 0) {
		# Shoot... something error happened. Let's try and figure out what exactly failed and tell the user about it.
		$_SESSION['message'] = '<span class="red">'.$LANG['ERROR']['restore_failed'].'</span><br>'.$LANG['MISC']['cause'].': ';
		switch ($_FILES['restore_fil']['error']) {
			case 1:
				# The file was too big for PHP's max setting
				$_SESSION['message'] .= $LANG['ERROR']['upload_max_filesize'];
				break;
			case 2:
				$_SESSION['message'] .= "UNKNOWN";
				# Err... this is probably a bug...
				break;
			case 3:
				# For some reason the upload was interrupted, so we didn't get the entire file. We can't do a partial restore, so let's not mess things up by trying.
				$_SESSION['message'] .= $LANG['ERROR']['partial_file_upload'];
				break;
			case 4:
				# User didn't upload any file. That's also a bit hard to perform a restore from, so let's not get inventive.
				$_SESSION['message'] .= $LANG['ERROR']['no_file_uploaded'];
				break;
			default:
				# Yep. Something went wrong. Can't tell you what though, but it obviously didn't work.
				$_SESSION['message'] .= $LANG['MISC']['unknown'];
		}
	} else {
		if (is_admin()) {
			# Only admins are allowed to do this stuff for obvious reasons.
			this_might_take_a_while();
			if ($_POST['full_restore_file'] == 'yes') {
				# We're doing a full restore - db and files
				if (do_fullrestore()) {
					# The do_fullrestore() returns true or false, and if it's true it means we're a-ok. Let's tell the user.
					$_SESSION['message'] = substr_replace($_FILES['restore_fil']['name'], '-<br/>', strpos($_FILES['restore_fil']['name'], '-'), 1).' '.$LANG['MISC']['uploaded'].'<br>'.$LANG['MISC']['restore_complete'];
					header('Location: ./dbrestore.php?restore=ok');
				} else {
					# The do_fullrestore() function returned false, so something went wrong. Damn...
					header('Location: ./dbrestore.php?restore=fail');
				}
			} else {
				if (do_dbrestore(0)) {
					# do_dbrestore() also returns true or false - here it said "yay" so we let the user know.
					$_SESSION['message'] = substr_replace($_FILES['restore_fil']['name'], '-<br/>', strpos($_FILES['restore_fil']['name'], '-'), 1).' '.$LANG['MISC']['uploaded'].'<br>'.$LANG['MISC']['restore_complete'];
					header('Location: ./dbrestore.php?restore=ok');
				} else {
					# ...and here it said "nay", which kinda sucks...
					header('Location: ./dbrestore.php?restore=fail');
				}
			}
			# All done, byebye.
			exit();
		}
	}
}
# No files have been attempted uploaded, so either we've just performed a restore, or we're about to perform one.
include('header.php');
echo '
	<h2 align="center">'.$LANG['MISC']['restore'].'</h2>
	<br>
';
if ($_REQUEST['restore'] == 'ok') {
	# We've performed a restore, and it succeeded. Hooray!
	echo '
		<h4 align="center">'.$LANG['MISC']['restore_complete'].'</h4>
	';
} elseif ($_REQUEST['restore'] == 'fail') {
	# We tried to perform a restore, but something went wrong...
	echo '
		<h4 align="center" class="red">'.$LANG['ERROR']['restore_failed'].'</h4>
	';
} else {
	# We've not tried anything yet, so let's find out what we're supposed to be trying. Make a form.
	echo '
		<script language="JavaScript" type="text/javascript">
			function check_restoreform() {
				if (document.restoreform.restore_fil.value.indexOf(\'.zip\') > -1) {
					document.restoreform.full_restore_file.value = \'yes\';
					document.restoreform.do_full_restore.checked = true;
				}
			}
		</script>
		<form name="restoreform" action="./dbrestore.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="full_restore_file" value="no">
		<table align="center">
			<tr>
				<td class="highlight" colspan="3">'.$LANG['MISC']['preferences'].'</td>
			</tr>
			<tr>
				<td colspan="2"><input type="file" name="restore_fil" onChange="javascript:check_restoreform();"></td>
				<td>'.hjelp_icon($LANG['MISC']['restore_file'],$LANG['HELPTIP']['restore_file']).'</td>
			</tr>
			<tr>
				<td>'.$LANG['MISC']['do_full_restore'].'</td>
				<td><input type="checkbox" name="do_full_restore"></td>
				<td>'.hjelp_icon($LANG['MISC']['do_full_restore'],$LANG['HELPTIP']['do_full_restore']).'</td>
			</tr>
			<tr>
				<td>'.$LANG['MISC']['restore_overwrite'].'</td>
				<td><input type="checkbox" name="restore_overwrite"></td>
				<td>'.hjelp_icon($LANG['MISC']['restore_overwrite'],$LANG['HELPTIP']['restore_overwrite']).'</td>
			</tr>
			<tr>
				<td align="center" colspan="3"><button type="submit" onClick="javascript:return confirmAction(\''.$LANG['JSBOX']['restore'].'\');">'.$LANG['MISC']['execute'].'</button></td>
			</tr>
		</table>
		</form>
	';
}
	
include('footer.php');
?>