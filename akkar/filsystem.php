<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               filsystem.php                             #
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

if ($_FILES) {
	if (opprett_fil($_POST['dir'], $_POST['beskrivelse'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['file_uploaded'];
		header('Location: ./filsystem.php?spill_id='.$_POST['spill_id']);
	} else {
		$_SESSION['message'] .= $LANG['MESSAGE']['file_upload_error'];
		header('Location: ./filsystem.php?spill_id='.$_POST['spill_id']);
	}
	exit();
} elseif ($_GET['slett_fil']) {
	if (slett_fil($_GET['slett_fil'], $_SESSION['cwd'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['file_deleted'];
		header('Location: ./filsystem.php?spill_id='.$_GET['spill_id']);
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['file_delete_error'];
		header('Location: ./filsystem.php?spill_id='.$_GET['spill_id']);
	}
	exit();
} elseif ($_GET['slett_dir']) {
	if (slett_dir($_GET['slett_dir'], $_SESSION['cwd'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['dir_deleted'];
		header('Location: ./filsystem.php?spill_id='.$_GET['spill_id']);
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['dir_delete_error'];
		header('Location: ./filsystem.php?spill_id='.$_GET['spill_id']);
	}
	exit();
} elseif ($_POST['newdir']) {
	if (create_dir($_GET['newdir'], $_SESSION['cwd'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['dir_created'];
		header('Location: ./filsystem.php?spill_id='.$spill_id);
	} else {
		$_SESSION['message'] .= $LANG['MESSAGE']['dir_create_error'];
		header('Location: ./filsystem.php?spill_id='.$spill_id);
	}
	exit();
} elseif ($_GET['rename_dir']) {
	if (rename_dir($_GET['rename_dir'], $_GET['newname'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['dir_renamed'];
		header('Location: ./filsystem.php?spill_id='.$spill_id);
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['dir_rename_error'];
		header('Location: ./filsystem.php?spill_id='.$spill_id);
	}
	exit();
} elseif ((!$_SESSION['message']) && ($removed = remove_dummy_entries())) {
	$numremoved = count($removed);
	$_SESSION['message'] = $LANG['MESSAGE']['purged_removed_files'].' ('.$numremoved.'): ';
	foreach ($removed as $fil) {
		$_SESSION['message'] .= $fil['navn'].', ';
	}
	$_SESSION['message'] = substr(trim($_SESSION['message']), 0, -1);
}

if ($_GET['cd']) {
	$_SESSION['cwd'] = $_GET['cd'];
} elseif ($_GET['cdup']) {
	if ($_SESSION['cwd'] != '/') {
		$_SESSION['cwd'] = substr($_SESSION['cwd'], 0, strrpos($_SESSION['cwd'], '/'));
		$_SESSION['cwd'] = substr($_SESSION['cwd'], 0, strrpos($_SESSION['cwd'], '/')+1);
	}
}
create_new_entries($_SESSION['cwd']);
$pathlink = build_pathlink($_SESSION['cwd']);

include('header.php');
$fs_used_space = get_human_readable_size(used_space($config['filsystembane'], 1));
$cwd_used_space = get_human_readable_size(used_space($config['filsystembane'].$_SESSION['cwd'], 0));

$dirs = get_fs_dirs($_SESSION['cwd']);
$filer = get_fs_files($_SESSION['cwd']);
echo '
	<script language="JavaScript" type="text/javascript">
';
if ($filer) {
	echo '
		filer = new Array('.count($filer).');
	';
	foreach ($filer as $fil) {
		echo 'filer[\''.strtolower($fil['navn']).'\']=true;\r\n';
	}
} else {
	echo '
		filer = new Array(0);
	';
}
if ($dirs) {
	echo '
		dirs = new Array('.count($dirs).');
			dirs[\'/\']=true;
	';
	foreach ($dirs as $dir) {
		echo 'dirs[\''.strtolower($dir['navn']).'\']=true;\r\n';
	}
} else {
	echo '
		dirs = new Array(0);
	';
}
echo '
		function validate_newfileform() {
			if (file_exists(document.nyfilform.nyfil.value)) {
				alert(\''.$LANG['JSBOX']['filename_exists'].'\');
				return false;
			}
			if (document.nyfilform.nyfil.value == \'\') {
				alert(\''.$LANG['JSBOX']['no_file'].'\');
				return false;
			}
			if (document.nyfilform.beskrivelse.value == \'\') {
				alert(\''.$LANG['JSBOX']['no_description'].'\');
				document.nyfilform.beskrivelse.focus();
				return false;
			}
			return true;
		}
		function validate_newdirform() {
			if (dirs[document.newdirform.newdir.value.toLowerCase()] == true) {
				alert(\''.$LANG['JSBOX']['dir_exists'].'\');
				return false;
			}
			if (document.newdirform.newdir.value == \'\') {
				alert(\''.$LANG['JSBOX']['directory_name'].'\');
				document.newdirform.newdir.focus();
				return false;
			}
			if (document.newdirform.beskrivelse.value == \'\') {
				alert(\''.$LANG['JSBOX']['no_description'].'\');
				document.newdirform.beskrivelse.focus();
				return false;
			}
			return true;
		}
	</script>
	<h2 align="center">'.$LANG['MISC']['filesystem'].'</h2>
	<p align="center"><em>'.$LANG['MISC']['fs_used_space'].':</em> '.$fs_used_space.'
	<br><em>'.$LANG['MISC']['cwd_used_space'].':</em> '.$cwd_used_space.'</p>
	<p style="margin: 0;"><strong>'.$LANG['MISC']['current_dir'].': '.$pathlink.'</strong></p>
';
echo '
	<table align="center" cellspacing="0" width="100%">
		<tr class="highlight">
		<td width="1">'; if ($_SESSION['cwd'] != '/') { echo '<a href="./filsystem.php?cdup=cdup"><img src="'.$styleimages['icon_cdup'].'"></a>'; } else { echo '&nbsp;'; } echo '</td>
		<td width="1">'.$LANG['MISC']['filename'].'</td>
		<td width="1">'.$LANG['MISC']['filesize'].'</td>
		<td>'.$LANG['MISC']['description'].'</td>
		<td nowrap width="1">'.$LANG['MISC']['updated'].'</td>
		<td colspan="2" width="1">&nbsp;</td>
	</tr>
';
if ((!$filer) && (!$dirs)) {
	echo '
		</table>
		<h4 align="center">'.$LANG['MISC']['no_files'].'</h4>
		<br><br><br>
	';
} else {
	if ($dirs) {
		foreach ($dirs as $dir) {
			echo '
				<tr>
					<td class="nospace" align="right"><a href="./filsystem.php?cd='.$_SESSION['cwd'].''.$dir['navn'].'/"><img src="'.$styleimages['icon_dir'].'"></a></td>
					<td nowrap><a href="./filsystem.php?cd='.$_SESSION['cwd'].''.$dir['navn'].'/" nowrap>'.broken_text($dir['navn'], 25).'</a></td>
					<td nowrap><em>&lt;'.strtolower($LANG['MISC']['dir']).'&gt;</em></td>
					<td>'.$dir['beskrivelse'].'</td>
					<td nowrap>'.strftime($config['short_dateformat'], $dir['oppdatert']).'</td>
					<td class="nospace" align="right">
						<button type="button" onClick="javascript:newname=window.prompt(\''.$LANG['JSBOX']['new_name'].'\'); if (newname) { window.location=\'./filsystem.php?rename_dir='.$dir['fil_id'].'&newname=\' + newname; } else { window.alert(\''.$LANG['JSBOX']['directory_name'].'\'); }">'.$LANG['MISC']['rename'].'</button>
						<button type="button" class="red" onClick="javascript:return confirmDeleteDir(\''.addslashes($dir['navn']).'\', \'./filsystem.php?spill_id='.$spill_id.'&amp;slett_dir='.$dir['navn'].'\');">'.$LANG['MISC']['delete'].'</button>
					</td>
				</tr>
			';
		}
	}
	if ($filer) {
		foreach ($filer as $fil) {
			if (!is_file($config['filsystembane'].''.$_SESSION['cwd'].''.$fil['navn'])) {
				$fil_navn = '<span class="red">'.broken_text($fil['navn'], 25).'</span>';
				$fil['size'] = 0;
			} else {
				$fil['size'] = get_human_readable_size(filesize($config['filsystembane'].''.$_SESSION['cwd'].''.$fil['navn']));
				$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'">'.broken_text($fil['navn'], 25).'</a>';
			}
			$mime_parts = explode('/', $fil['type']);
			switch ($mime_parts[0]) {
				case 'image':
					$icon = $styleimages['icon_image'];
					break;
				case 'audio':
					$icon = $styleimages['icon_audio'];
					break;
				case 'video':
					$icon = $styleimages['icon_video'];
					break;
				case 'text':
					$icon = $styleimages['icon_text'];
					break;
				default:
					unset($icon);
			}
			if (!$icon) {
				switch ($fil['type']) {
					case 'application/x-zip-compressed':
					case 'application/x-gzip':
					case 'application/zip':
					case 'application/x-compressed':
					case 'application/arj':
					case 'application/x-bzip2':
					case 'application/x-bzip':
					case 'application/x-lzh':
					case 'application/x-lzx':
					case 'application/x-rar':
					case 'application/arj':
					case 'application/lha':
						$icon = $styleimages['icon_zip'];
						break;
					case 'application/octet-stream':
						$icon = $styleimages['icon_binary'];
						break;
					default:
						$icon = $styleimages['icon_text'];
				}
			}
			$icon = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'"><img src="'.$icon.'"></a>';
			echo '
				<tr>
					<td class="nospace" align="right">'.$icon.'</td>
					<td title="'.$fil['navn'].'" nowrap>'.$fil_navn.'</td>
					<td nowrap>'.$fil['size'].'</td>
					<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
					<td nowrap>'.$fil['oppdatert'].'</td>
					<td class="nospace" align="right">
						<button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button>
						<button type="button" class="red" onClick="javascript:return confirmDelete(\''.addslashes($fil['navn']).'\', \'./filsystem.php?spill_id='.$spill_id.'&amp;slett_fil='.$fil['navn'].'\');">'.$LANG['MISC']['delete'].'</button>
					</td>
				</tr>
			';
		}
	}
	echo '
		</table>
	';
}
echo '
	<br><br>
	<form name="newdirform" action="filsystem.php" method="post" enctype="multipart/form-data" onSubmit="javascript:return validate_newdirform();">
	<table align="center" cellspacing="0" border="0" width="620">
		<tr class="highlight">
			<td>'.$LANG['MISC']['dir'].'</td>
			<td>'.$LANG['MISC']['description'].'</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="vertical-align: middle"><input type="text" name="newdir" size="20"></td>
			<td><input type="text" name="beskrivelse" size="60"></td>
			<td align="right"><button type="submit">'.$LANG['MISC']['create_dir'].'</button></td>
		</tr>
	</table>
	</form>
	<br><br>
	<form name="nyfilform" action="filsystem.php" method="post" enctype="multipart/form-data" onSubmit="javascript:return validate_newfileform();;">
	<input type="hidden" name="dir" value="'.$_SESSION['cwd'].'" />
	<table align="center" cellspacing="0" border="0" width="620">
		<tr class="highlight">
			<td>'.$LANG['MISC']['file'].'</td>
			<td>'.$LANG['MISC']['description'].'</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="vertical-align: middle"><input type="file" name="nyfil"></td>
			<td><input type="text" size="60" name="beskrivelse"></td>
			<td align="right"><button type="submit">'.$LANG['MISC']['upload'].'</button></td>
		</tr>
	</table>
	</form>
';
include('footer.php');
?>
