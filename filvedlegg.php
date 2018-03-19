<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              filvedlegg.php                             #
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

$vedlagt = $_REQUEST['vedlagt'];

if ($_FILES) {
	if ($fil_id = opprett_fil()) {
		opprett_vedlegg($fil_id, $_POST['vedlegg_id'], $_POST['spill_id'], $_POST['vedlagt']);
		switch($_POST['vedlagt']) {
			case 'rolle':
				$_SESSION['message'] .= $LANG['MESSAGE']['file_uploaded_attached_character'];
				header('Location: ./filvedlegg.php?rolle_id='.$_POST['vedlegg_id'].'&spill_id='.$_POST['spill_id'].'&vedlagt=rolle');
				break;
			case 'gruppe':
				$_SESSION['message'] .= $LANG['MESSAGE']['file_uploaded_attached_group'];
				header('Location: ./filvedlegg.php?gruppe_id='.$_POST['vedlegg_id'].'&spill_id='.$_POST['spill_id'].'&vedlagt=gruppe');
				break;
			case 'spill':
				$_SESSION['message'] .= $LANG['MESSAGE']['file_uploaded_attached_game'];
				header('Location: ./filvedlegg.php?spill_id='.$_POST['spill_id'].'&vedlagt=spill');
				break;
			case 'rollekonsept':
				$_SESSION['message'] .= $LANG['MESSAGE']['file_uploaded_attached_charconcept'];
				header('Location: ./filvedlegg.php?spill_id='.$_POST['spill_id'].'&vedlagt=rollekonsept');
				break;
			default:
				exit('Access violation.');
		}
	} else {
		$_SESSION['message'] .= $LANG['MESSAGE']['file_upload_error'];
		switch($_POST['vedlagt']) {
			case 'rolle':
				header('Location: ./filvedlegg.php?rolle_id='.$_POST['vedlegg_id'].'&spill_id='.$_POST['spill_id'].'&vedlagt=rolle');
				break;
			case 'gruppe':
				header('Location: ./filvedlegg.php?gruppe_id='.$_POST['vedlegg_id'].'&spill_id='.$_POST['spill_id'].'&vedlagt=gruppe');
				break;
			case 'spill':
				header('Location: ./filvedlegg.php?spill_id='.$_POST['spill_id'].'&vedlagt=spill');
				break;
			case 'rollekonsept':
				header('Location: ./filvedlegg.php?spill_id='.$_POST['spill_id'].'&vedlagt=rollekonsept');
				break;
			default:
				exit('Access violation.');
		}
	}
	exit();
}

if ($_GET['slett_rollevedlegg']) {
	slett_vedlegg($_GET['slett_rollevedlegg'], $_GET['rolle_id'], $_GET['spill_id'], 'rolle');
	$_SESSION['message'] = $LANG['MESSAGE']['attachment_removed_character'];
	header('Location: ./filvedlegg.php?rolle_id='.$_GET['rolle_id'].'&spill_id='.$_GET['spill_id'].'&vedlagt=rolle');
	exit();
} elseif ($_GET['slett_gruppevedlegg']) {
	slett_vedlegg($_GET['slett_gruppevedlegg'], $_GET['gruppe_id'], $_GET['spill_id'], 'gruppe');
	$_SESSION['message'] = $LANG['MESSAGE']['attachment_removed_group'];
	header('Location: ./filvedlegg.php?gruppe_id='.$_GET['gruppe_id'].'&spill_id='.$_GET['spill_id'].'&vedlagt=gruppe');
	exit();
} elseif ($_GET['slett_spillvedlegg']) {
	slett_vedlegg($_GET['slett_spillvedlegg'], 0, $_GET['spill_id'], 'spill');
	$_SESSION['message'] = $LANG['MESSAGE']['attachment_removed_game'];
	header('Location: ./filvedlegg.php?spill_id='.$_GET['spill_id'].'&vedlagt=spill');
	exit();
} elseif ($_GET['slett_rollekonseptvedlegg']) {
	slett_vedlegg($_GET['slett_rollekonseptvedlegg'], 0, $_GET['spill_id'], 'rollekonsept');
	$_SESSION['message'] = $LANG['MESSAGE']['attachment_removed_charconcept'];
	header('Location: ./filvedlegg.php?spill_id='.$_GET['spill_id'].'&vedlagt=rollekonsept');
	exit();
} elseif ($_POST['nytt_vedlegg']) {
	opprett_vedlegg($_POST['nytt_vedlegg'], $_POST['vedlegg_id'], $_POST['spill_id'], $_POST['vedlagt']);
	switch($_POST['vedlagt']) {
		case 'rolle':
			$_SESSION['message'] = $LANG['MESSAGE']['file_attached_character'];
			header('Location: ./filvedlegg.php?rolle_id='.$_POST[vedlegg_id].'&spill_id='.$_POST['spill_id'].'&vedlagt=rolle');
			break;
		case 'gruppe':
			$_SESSION['message'] = $LANG['MESSAGE']['file_attached_group'];
			header('Location: ./filvedlegg.php?gruppe_id='.$_POST[vedlegg_id].'&spill_id='.$_POST['spill_id'].'&vedlagt=gruppe');
			break;
		case 'spill':
			$_SESSION['message'] = $LANG['MESSAGE']['file_attached_game'];
			header('Location: ./filvedlegg.php?spill_id='.$_POST['spill_id'].'&vedlagt=spill');
			break;
		case 'rollekonsept':
			$_SESSION['message'] = $LANG['MESSAGE']['file_attached_charconcept'];
			header('Location: ./filvedlegg.php?spill_id='.$_POST['spill_id'].'&vedlagt=rollekonsept');
			break;
		default:
			exit('Access violation.');
	}
	exit();
}

$hjelpemne = $vedlagt;
include('header.php');

switch ($vedlagt) {
	case 'rolle':
		$rolle = get_rolle($_GET['rolle_id'], $_GET['spill_id']);
		echo '
			<h2 align="center">'.$LANG['MISC']['character_attachments'].'</h2>
			<h3 align="center">'.$rolle['navn'].'</h3>
			<br>
			<table align="center">
				<tr>
					<td><button onClick="javascript:window.location=\'./visrolle.php?rolle_id='.$_GET['rolle_id'].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['charactersheet'].'</button></td>
				</tr>
			</table>
			<br>
		';
		if (!$vedlegg = get_vedleggsliste($rolle['rolle_id'], $rolle['spill_id'], $vedlagt)) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_attachments'].'</h4>
				<br>
			';
		} else {
			echo '
				<table border="0" cellpadding="3" cellspacing="0" align="center">
					<tr class="highlight">
						<td>'.$LANG['MISC']['filename'].'</td>
						<td>'.$LANG['MISC']['filesize'].'</td>
						<td>'.$LANG['MISC']['description'].'</td>
						<td>'.$LANG['MISC']['updated'].'</td>
						<td colspan="2">&nbsp;</td>
					</tr>
			';
			foreach ($vedlegg as $fil) {
				if (!is_file($config['filsystembane'].$fil['dir'].$fil['navn'])) {
					$fil_navn = '<span class="red">'.$fil['dir'].$fil['navn'].'</span>';
					$fil[size] = 0;
				} else {
					$fil[size] = filesize($config['filsystembane'].$fil['dir'].$fil['navn']);
					$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'">'.$fil['navn'].'</a>';
				}
				$fil_size = get_human_readable_size($fil['size']);
				echo '
					<tr>
						<td>'.$fil['dir'].$fil_navn.'</td>
						<td>'.$fil_size.'</td>
						<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
						<td>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button></td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./filvedlegg.php?rolle_id='.$rolle['rolle_id'].'&spill_id='.$spill_id.'&amp;slett_rollevedlegg='.$fil['fil_id'].'\';">'.$LANG['MISC']['remove'].'</button></td>
					</tr>
				';
			}
			echo '
				</table>
			';
		}
		break;
	case 'gruppe':
		$gruppe = get_gruppe($_GET['gruppe_id'], $_GET['spill_id']);
		echo '
			<h2 align="center">'.$LANG['MISC']['group_attachments'].'</h2>
			<h3 align="center">'.$gruppe['navn'].'</h3>
			<br>
			<table border="0" cellpadding="3" cellspacing="0" align="center">
				<tr>
					<td class="nospace"><button type="button" onClick="javascript:window.location=\'./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'\'">'.$LANG['MISC']['groupsheet'].'</button></td>
				</tr>
			</table>
			<br>
		';
		if (!$vedlegg = get_vedleggsliste($gruppe['gruppe_id'], $gruppe['spill_id'], $vedlagt)) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_attachments'].'</h4>
				<br>
			';
		} else {
			echo '
				<table align="center" cellspacing="0">
					<tr class="highlight">
						<td>'.$LANG['MISC']['filename'].'</td>
						<td>'.$LANG['MISC']['filesize'].'</td>
						<td>'.$LANG['MISC']['description'].'</td>
						<td>'.$LANG['MISC']['updated'].'</td>
						<td colspan="2">&nbsp;</td>
					</tr>
			';
			foreach ($vedlegg as $fil) {
				if (!is_file($config['filsystembane'].$fil['dir'].$fil['navn'])) {
					$fil_navn = '<span class="red">'.$fil['navn'].'</span>';
					$fil[size] = 0;
				} else {
					$fil[size] = filesize($config['filsystembane'].$fil['dir'].$fil['navn']);
					$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'">'.$fil['navn'].'</a>';
				}
				$fil_size = get_human_readable_size($fil['size']);
				echo '
					<tr>
						<td>'.$fil['dir'].$fil_navn.'</td>
						<td>'.$fil_size.'</td>
						<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
						<td>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button></td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./filvedlegg.php?gruppe_id='.$gruppe['gruppe_id'].'&spill_id='.$spill_id.'&amp;slett_gruppevedlegg='.$fil['fil_id'].'\';">'.$LANG['MISC']['remove'].'</button></td>
					</tr>
				';
			}
			echo '
				</table>
			';
		}
		break;
	case 'spill':
		$spill = get_spillinfo($_GET['spill_id']);
		echo '
			<h2 align="center">'.$LANG['MISC']['game_attachments'].'</h2>
			<h3 align="center">'.$spill['navn'].'</h3>
			<br>
		';
		if (!$vedlegg = get_vedleggsliste(0, $spill['spill_id'], $vedlagt)) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_attachments'].'</h4>
				<br>
			';
		} else {
			echo '
				<table border="0" cellpadding="3" cellspacing="0" align="center">
					<tr class="highlight">
						<td>'.$LANG['MISC']['filename'].'</td>
						<td>'.$LANG['MISC']['filesize'].'</td>
						<td>'.$LANG['MISC']['description'].'</td>
						<td>'.$LANG['MISC']['updated'].'</td>
						<td colspan="2">&nbsp;</td>
					</tr>
			';
			foreach ($vedlegg as $fil) {
				if (!is_file($config['filsystembane'].$fil['dir'].$fil['navn'])) {
					$fil_navn = '<span class="red">'.$fil['navn'].'</span>';
					$fil[size] = 0;
				} else {
					$fil[size] = filesize($config['filsystembane'].$fil['dir'].$fil['navn']);
					$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'">'.$fil['navn'].'</a>';
				}
				$fil_size = get_human_readable_size($fil['size']);
				echo '
					<tr>
						<td>'.$fil['dir'].$fil_navn.'</td>
						<td>'.$fil_size.'</td>
						<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
						<td>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button></td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./filvedlegg.php?spill_id='.$spill_id.'&amp;slett_spillvedlegg='.$fil['fil_id'].'\';">'.$LANG['MISC']['remove'].'</button></td>
					</tr>
				';
			}
			echo '
				</table>
			';
		}
		break;
	case 'rollekonsept':
		$spill = get_spillinfo($_GET['spill_id']);
		echo '
			<h2 align="center">'.$LANG['MISC']['charconcept_attachments'].'</h2>
			<h3 align="center">'.$spill['navn'].'</h3>
			<br>
		';
		if (!$vedlegg = get_vedleggsliste(0, $spill['spill_id'], $vedlagt)) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_attachments'].'</h4>
				<br>
			';
		} else {
			echo '
				<table border="0" cellpadding="3" cellspacing="0" align="center">
					<tr class="highlight">
						<td>'.$LANG['MISC']['filename'].'</td>
						<td>'.$LANG['MISC']['filesize'].'</td>
						<td>'.$LANG['MISC']['description'].'</td>
						<td>'.$LANG['MISC']['updated'].'</td>
						<td colspan="2">&nbsp;</td>
					</tr>
			';
			foreach ($vedlegg as $fil) {
				if (!is_file($config['filsystembane'].$fil['dir'].$fil['navn'])) {
					$fil_navn = '<span class="red">'.$fil['navn'].'</span>';
					$fil[size] = 0;
				} else {
					$fil[size] = filesize($config['filsystembane'].''.$fil['dir'].''.$fil['navn']);
					$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'&amp;spill_id='.$spill_id.'">'.$fil['navn'].'</a>';
				}
				$fil_size = get_human_readable_size($fil['size']);
				echo '
					<tr>
						<td>'.$fil_navn.'</td>
						<td>'.$fil_size.'</td>
						<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
						<td>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button></td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./filvedlegg.php?spill_id='.$spill_id.'&amp;slett_rollekonseptvedlegg='.$fil['fil_id'].'\';">'.$LANG['MISC']['remove'].'</button></td>
					</tr>
				';
			}
			echo '
				</table>
			';
		}
		break;
}

if ($filer = get_filer()) {
	echo '
		<script language="javascript" type="text/javascript">
			function check_disable() {
				if ((document.nyfilform.nyfil.value != \'\') || (document.nyfilform.dir.value != \'/\')) {
					document.nyvedleggform.nytt_vedlegg.value = \'\';
					document.nyvedleggform.nytt_vedlegg.disabled = true;
					document.nyvedleggform.submit.disabled = true;
				} else {
					document.nyvedleggform.nytt_vedlegg.disabled = false;
					document.nyvedleggform.submit.disabled = false;
				}
				if (document.nyvedleggform.nytt_vedlegg.value != \'\') {
					document.nyfilform.dir.disabled = true;
					document.nyfilform.nyfil.disabled = true;
					document.nyfilform.beskrivelse.disabled = true;
					document.nyfilform.submit.disabled = true;
				} else {
					document.nyfilform.dir.disabled = false;
					document.nyfilform.nyfil.disabled = false;
					document.nyfilform.beskrivelse.disabled = false;
					document.nyfilform.submit.disabled = false;
				}
			}
		</script>
		<br><br>
		<h4 align="center">'.$LANG['MISC']['assign_new_attachment'].'</h4>
		<form name="nyvedleggform" action="filvedlegg.php" method="post">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
	';
	switch ($vedlagt) {
		case 'rolle':
			$vedlegg_id = $rolle['rolle_id'];
			break;
		case 'gruppe':
			$vedlegg_id = $gruppe['gruppe_id'];
			echo '';
			break;
		case 'spill':
		case 'rollekonsept':
			$vedlegg_id = 0;
			break;
	}
	echo '
		<input type="hidden" name="vedlegg_id" value="'.$vedlegg_id.'">
		<input type="hidden" name="vedlagt" value="'.$vedlagt.'">
		<table align="center" cellspacing="0" border="0">
			<tr class="highlight">
				<td>'.$LANG['MISC']['file'].'</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><select name="nytt_vedlegg" onChange="javascript:check_disable();">
					<option value="" class="selectname">- '.$LANG['MISC']['select'].' - </option>
	';
	foreach ($filer as $fil) {
		if (!is_vedlegg($fil['fil_id'], $vedlegg_id, $spill_id, $vedlagt)) {
			echo '<option value="'.$fil['fil_id'].'">'.$fil['dir'].$fil['navn'].'</option>';
		}
	}
	echo '
					</select>
				</td>
				<td align="right"><button type="submit" name="submit">'.$LANG['MISC']['add'].'</button></td>
			</tr>
		</table>
		</form>
	';
}
echo '
	<script language="JavaScript" type="text/javascript">
		function validate_nyfilform() {
			if (document.nyfilform.beskrivelse.value == \'\') {
				alert(\''.$LANG['JSBOX']['no_description'].'\');
				document.nyfilform.beskrivelse.focus();
				return false;
			}
			return true;
		}
	</script>

	<h4 align="center">'.$LANG['MISC']['upload_assign_new_attachment'].'</h4>
	<form name="nyfilform" action="filvedlegg.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<input type="hidden" name="vedlegg_id" value="'.$vedlegg_id.'">
	<input type="hidden" name="vedlagt" value="'.$vedlagt.'">
	<table align="center" cellspacing="0" border="0">
		<tr class="highlight">
			<td>'.$LANG['MISC']['dir'].'</td>
			<td>'.$LANG['MISC']['file'].'</td>
			<td>'.$LANG['MISC']['description'].'</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="vertical-align: middle">
				<select name="dir" onChange="javascript:check_disable();">
					<option value="/">/</option>
				';
				if ($dirs = get_fs_dirtree('/')) {
					foreach ($dirs as $dir) {
						echo '<option value="'.$dir.'/">'.$dir.'</option>';
					}
				}
				echo '
				</select>
			</td>
			<td style="vertical-align: middle"><input type="file" name="nyfil" onChange="javascript:check_disable();"></td>
			<td><input type="text" size="60" name="beskrivelse"></td>
			<td align="right"><button type="submit" onClick="javascript:return validate_nyfilform();" name="submit">'.$LANG['MISC']['upload'].'</button></td>
		</tr>
	</table>
	</form>
';

if ($vedlagt != 'rollekonsept' && $vedlagt != 'spill') {
echo '
	<h3 align="center" class="bt">'.$LANG['MISC']['other_attachments'].'</h3>
	<table align="center" cellspacing="0">
';

switch ($vedlagt) {
	case 'spill':
		break;
	case 'rolle':
		if ($grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id'])) {
			echo '
					<tr>
						<td colspan="7"><h4 align="center" style="margin-top: 1em;">'.$LANG['MISC']['attachments_from_groups'].'</h4></td>
					</tr>
					<tr class="highlight">
						<td>'.$LANG['MISC']['filename'].'</td>
						<td>'.$LANG['MISC']['filesize'].'</td>
						<td>'.$LANG['MISC']['description'].'</td>
						<td>'.$LANG['MISC']['updated'].'</td>
						<td>'.$LANG['MISC']['group'].'</td>
						<td colspan="2">&nbsp;</td>
					</tr>
			';
			foreach ($grupper as $gruppe) {
				if ($gruppevedlegg = get_vedleggsliste($gruppe['gruppe_id'], $gruppe['spill_id'], 'gruppe')) {
					foreach ($gruppevedlegg as $fil) {
						if (!is_file($config['filsystembane'].$fil['dir'].$fil['navn'])) {
							$fil_navn = '<span class="red">'.$fil['navn'].'</span>';
							$fil[size] = 0;
						} else {
							$fil[size] = filesize($config['filsystembane'].$fil['dir'].$fil['navn']);
							$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'&amp;spill_id='.$spill_id.'">'.$fil['navn'].'</a>';
						}
						$fil_size = get_human_readable_size($fil['size']);
						echo '
							<tr>
								<td>'.$fil['dir'].$fil_navn.'</td>
								<td>'.$fil_size.'</td>
								<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
								<td>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
								<td><a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a></td>
								<td class="nospace"><button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button></td>
							</tr>
						';
						unset($fil, $fil_navn);
					}
				}
			}
		}
		$gruppe['spill_id'] = $rolle['spill_id'];
	case 'gruppe':
		if ($spillvedlegg = get_vedleggsliste(0, $gruppe['spill_id'], 'spill')) {
			echo '
					<tr>
						<td colspan="7"><h4 align="center" style="margin-top: 1em;">'.$LANG['MISC']['attachments_from_game'].'</h4></td>
					</tr>
					<tr class="highlight">
						<td>'.$LANG['MISC']['filename'].'</td>
						<td>'.$LANG['MISC']['filesize'].'</td>
						<td>'.$LANG['MISC']['description'].'</td>
						<td>'.$LANG['MISC']['updated'].'</td>
						<td colspan="3">&nbsp;</td>
					</tr>
			';
			foreach ($spillvedlegg as $fil) {
				if (!is_file($config[filsystembane].''.$fil['dir'].''.$fil[navn])) {
					$fil_navn = '<span class="red">'.$fil['navn'].'</span>';
					$fil[size] = 0;
				} else {
					$fil[size] = filesize($config['filsystembane'].$fil['dir'].$fil['navn']);
					$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'&amp;spill_id='.$spill_id.'">'.$fil['navn'].'</a>';
				}
				$fil_size = get_human_readable_size($fil['size']);
				echo '
					<tr>
						<td>'.$fil['dir'].$fil_navn.'</td>
						<td>'.$fil_size.'</td>
						<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
						<td>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
						<td>&nbsp;</td>
						<td class="nospace"><button type="button" onClick="javascript:window.location=\'./download.php?fil_id='.$fil['fil_id'].'\';">'.$LANG['MISC']['download'].'</button></td>
					</tr>
				';
			}
	}
}
}
echo '
	</table>
';

include('footer.php');

?>