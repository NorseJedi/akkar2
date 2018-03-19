<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                mugshots.php                             #
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
$_REQUEST['infowindow'] = true;

include('common.php');
$ds = DIRECTORY_SEPARATOR;

if ($_POST) {
	if ($_POST['delete']) {
		# Take care of images marked for deletion
		foreach ($_POST['delete'] as $delimage=>$a) {
			delete_mugshot($delimage);
		}
	}
	if ($_POST['delghost']) {
		# Take care of unassigned images marked for deletion
		foreach ($_POST['delghost'] as $ghostimage=>$a) {
			unlink('images/personer/'.$ghostimage);
		}
	}
	if ($_POST['remove']) {
		# Take care of images marked for removal (only DB-removal from the person, the file remains intact)
		foreach ($_POST['remove'] as $remimage=>$a) {
			remove_mugshot($_POST['person_id'], $remimage);
		}
	}
	$person = get_person($_POST['person_id']);
	//if (is_file($_FILES['newmug']['tmp_name'])) {
	if (file_exists($_FILES['newmug']['tmp_name'])) {
		# Bag and tag the uploaded image
		$bildenavn = mkfilename($person['fornavn'].' '.$person['etternavn'].' '.time().'.jpg');
		$bildepath = getcwd().$ds.'images'.$ds.'personer'.$ds.$bildenavn;
		$tmpbilde = getcwd().$ds.'tmp'.$ds.$bildenavn;
		move_uploaded_file($_FILES['newmug']['tmp_name'],$tmpbilde);
		if (resizeimg($tmpbilde, $bildepath, $_FILES['newmug']['type'], '120', '150')) {
			print($tmpbilde."<br>".$bildepath);
			add_mugshot($person['person_id'], $bildenavn);
			update_personmug($person['person_id'], $bildenavn);
			$refresh = true;
		}
	} elseif ($_POST['mugshot'] != $person['bilde']) {
		if (is_int(strpos($_POST['mugshot'], $styleimages['no_mugshot_f'])) || is_int(strpos($_POST['mugshot'], $styleimages['no_mugshot_m']))) {
			# Set the image to NULL so the generic one will be used
			update_personmug($_POST['person_id'], '');
		} else {
			# Update the db with the new selected image
			update_personmug($_POST['person_id'], $_POST['mugshot']);
			if (!is_mugshot($_POST['mugshot'])) {
				# Rename the image if it's not assigned to anyone else
				$bildenavn = mkfilename($person['fornavn'].' '.$person['etternavn'].' '.time().'.jpg');
				if (rename('images'.$ds.'personer'.$ds.$_POST['mugshot'], 'images'.$ds.'personer'.$ds.$bildenavn)) {
					add_mugshot($_POST['person_id'], $bildenavn);
					update_personmug($_POST['person_id'], $bildenavn);
				} else {
					add_mugshot($_POST['person_id'], $_POST['mugshot']);
				}
			}
		}
		$refresh = true;
	} else {
		print_a($_FILES);
	}
	if ($refresh) {
		# Refresh the parent window and this window.
		echo '
			<script language="JavaScript" type="text/javascript">
				window.opener.location.reload();
				window.location="./mugshots.php?person_id='.$person['person_id'].'";
			</script>
		';
	} else {
		# Refresh this window
		header('Location: ./mugshots.php?person_id='.$person['person_id']);
	}
	exit();
} else {
	$person = get_person($_GET['person_id']);
	$i = 0;
	
	# Get the images and remove any entries pointing to non-existing images
	if ($images = get_mugshots($person['person_id'])) {
		foreach ($images as $image) {
			if (!is_file('images/personer/'.$image)) {
				remove_mugshot($person['person_id'], $image);
				$refresh = 1;
			}
		}
		if ($refresh) {
			header('Location: ./mugshots.php?person_id='.$person['person_id']);
			exit();
		}
	}
	
	# Add the generic mugshot to the list
	if ($person['kjonn'] == 'hun') {
		$images[] = '../../'.$styleimages['no_mugshot_f'];
	} else {
		$images[] = '../../'.$styleimages['no_mugshot_m'];
	}
	# Get all available mugshots for comparison
	if (!$allmugs = get_all_mugshots()) {
		$allmugs = array();
	}
	# Open the directory and make a list of images not assigned to any persons
	$handle = opendir('images/personer');
	while (false !== ($file = readdir($handle))) {
		if  ((is_file('images/personer/'.$file)) && (!is_int(strpos(strtolower($file), 'ikke')) && !is_int(strpos(strtolower($file), 'bilde.'))) && (exif_imagetype('images/personer/'.$file) == IMAGETYPE_JPEG)  && (!is_mugshot($file, $allmugs))) {
			$ghostimages[] = $file;
		}
	}
	closedir($handle);

	include('header.php');
	echo '
		<h2 align="center">'.$LANG['MISC']['mugshots'].'</h2>
		<br>
		<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'</h3>
		<br>
		<script language="JavaScript" type="text/javascript">
			function checkdisable(eName) {
				inputs = document.getElementsByTagName(\'input\');
				for (i = 0; i < inputs.length; i++) {
					if (inputs[i].id.indexOf(\'delete_\') > -1) {
						var imgName = str_replace(\'delete_\', \'\', inputs[i].id);
						if (document.getElementById(\'select_\' + imgName).checked == true) {
							inputs[i].disabled = true;
							inputs[i].checked = false;
							document.getElementById(\'remove_\' + imgName).disabled = true;
							document.getElementById(\'remove_\' + imgName).checked = false;
						} else {
							inputs[i].disabled = false;
							document.getElementById(\'remove_\' + imgName).disabled = false;
						}
					}
					if (inputs[i].id.indexOf(\'delghost_\') > -1) {
						var imgName2 = str_replace(\'delghost_\', \'\', inputs[i].id);
						if (document.getElementById(\'select_\' + imgName2).checked == true) {
							inputs[i].disabled = true;
							inputs[i].checked = false;
						} else {
							inputs[i].disabled = false;
						}
					}

				}
			
			}
		</script>
		<form name="imageform" action="./mugshots.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="person_id" value="'.$person['person_id'].'">
		<table align="center" cellpadding="0" cellspacing="0" border="0">
			<tr>
	';

	if (($images) || ($ghostimages)) {
		if ($images) {
			# List the images belonging to the person
			foreach ($images as $image) {
				$i++;
				echo '
						<td align="center" style="padding-bottom: 1em;"'; if (count($images) == 1) { echo ' colspan="3"'; } echo '><img class="foto" src="images/personer/'.$image.'" title="'.$image.'" alt="'.$image.'">
							<br><input onClick="javascript:checkdisable(\''.$image.'\');" type="radio" name="mugshot" id="select_'.$image.'" value="'.$image.'"'; if (strtolower($person['bilde']) == strtolower($image)) { echo ' checked'; } if ((is_int(strpos($image, $styleimages['no_mugshot_f'])) || is_int(strpos($image, $styleimages['no_mugshot_m']))) && !$person['bilde']) { echo ' checked'; } echo '>'.$LANG['MISC']['select'].'
				';
				if (!is_int(strpos($image, $styleimages['no_mugshot_f'])) && !is_int(strpos($image, $styleimages['no_mugshot_m']))) {
					echo '
							<br><input type="checkbox" id="delete_'.$image.'" name="delete['.$image.']"'; if (strtolower($person['bilde']) == strtolower($image)) { echo ' disabled'; } echo '><span class="small">'.$LANG['MISC']['delete'].'</span>
							<br><input type="checkbox" id="remove_'.$image.'" name="remove['.$image.']"'; if (strtolower($person['bilde']) == strtolower($image)) { echo ' disabled'; } echo '><span class="small">'.$LANG['MISC']['remove'].'</span>
					';
				}
				echo '
						</td>
				';
				if (is_int($i / 3)) {
					echo '
						</tr>
						<tr>
					';
				}
			}
		}
		if ($ghostimages) {
			# List any nonassigned images
			$j = 0;
			echo '
				</tr>
				<tr>
				<td align="center" colspan="3" style="padding-bottom: 1em;padding-top: 1em;"><h4>'.$LANG['MISC']['unassigned_mugshots'].'</td>
				</tr>
				<tr>
			';
			foreach ($ghostimages as $image) {
				$j++;
				echo '
						<td align="center" style="padding-bottom: 1em;"'; if (count($ghostimages) == 1) { echo ' colspan="3"'; } echo '><img class="foto" src="images/personer/'.$image.'" title="'.$image.'" alt="'.$image.'">
							<br><input onClick="javascript:checkdisable(\''.$image.'\');" type="radio" name="mugshot" id="select_'.$image.'" value="'.$image.'">'.$LANG['MISC']['select'].'
							<br><input type="checkbox" id="delghost_'.$image.'" name="delghost['.$image.']"><span class="small">'.$LANG['MISC']['delete'].'</span>
						</td>
				';
				if (is_int($j / 3)) {
					echo '
						</tr>
						<tr>
					';
				}
			}
		}

	} else {
		echo '
			<td align="center" style="padding-bottom: 3em;"><h4>'.$LANG['MISC']['no_mugshots'].'</h4></td>
		';
	}
	echo '
			</tr>
		</table>
		<table align="center">
			<tr>
				<td><h4 class="table">'.$LANG['MISC']['upload'].':</h4></td>
				<td><input type="file" accept="image/jpeg" name="newmug" size="25"></td>
			</tr>
		</table>

		<table align="center" style="margin-top: 2em;">
			<tr>
				<td><button class="visible" type="reset">'.$LANG['MISC']['reset'].'</button></td>
				<td><button class="visible" type="submit">'.$LANG['MISC']['save'].'</button></td>
			</tr>
		</table>
		</form>
	';
}
include('footer.php');
?>
