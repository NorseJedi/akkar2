<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                vismal.php                               #
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

if ($_GET['slettfelt']) {
	slett_malfelt();
	$_SESSION['message'] = $LANG['MESSAGE']['field_deleted'];
	header('Location: ./vismal.php?mal_id='.$_GET['mal_id']);
	exit();
} elseif ($_POST['nyttfelt']) {
	opprett_malfelt($_POST['mal_id']);
	$_SESSION['message'] = $LANG['MESSAGE']['field_created'];
	header('Location: ./vismal.php?mal_id='.$_POST['mal_id']);
	exit();
} elseif ($_POST['nymal']) {
	$mal_id = opprett_mal();
	$_SESSION['message'] = $LANG['MESSAGE']['template_created'];
	header('Location: ./vismal.php?mal_id='.$mal_id);
	exit();
} elseif ($_POST['editedfelt']) {
	oppdater_malfelt();
	$_SESSION['message'] = $LANG['MESSAGE']['field_updated'];
	header('Location: ./vismal.php?mal_id='.$_POST['mal_id']);
	exit();
} elseif ($_POST['editedmal']) {
	oppdater_mal();
	$_SESSION['message'] = $LANG['MESSAGE']['template_updated'];
	header('Location: ./vismal.php?mal_id='.$_POST['editedmal']);
	exit();
} elseif ($_GET['pri_opp']) {
	if (mal_pri_opp($_GET['pri_opp'], $_GET['mal_id'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['field_order_updated'];
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['field_is_at_bottom'];
	}
	header('Location: ./vismal.php?mal_id='.$_GET['mal_id']);
	exit();
} elseif ($_GET['pri_ned']) {
	if (mal_pri_ned($_GET['pri_ned'], $_GET['mal_id'])) {
		$_SESSION['message'] = $LANG['MESSAGE']['field_order_updated'];
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['field_is_at_top'];
	}
	header('Location: ./vismal.php?mal_id='.$_GET['mal_id']);
	exit();
}
include('header.php');

$mal = get_malinfo($_GET['mal_id']);
$buttons = '
	<table align="center">
		<tr>
			<td><button onClick="javascript:window.location=\'./maler.php\';">'.$LANG['MISC']['templates'].'</button></td>
			<td><button onClick="javascript:return confirmDelete(\''.addslashes($mal['navn']).'\', \'./maler.php?slettmal='.$mal['mal_id'].'\');">'.$LANG['MISC']['delete_template'].'</button></td>
			<td><button onClick="javascript:window.location=\'./editmal.php?mal_id='.$mal['mal_id'].'&amp;nyttfelt=yes\';">'.$LANG['MISC']['create_field'].'</button></td>
		</tr>
	</table>
';
echo '
	<h2 align="center">'.$LANG['MISC']['template_info'].'</h2>
	<h3 align="center">'.ucwords($mal['navn']).' ('.$LANG['DBFIELD'][$mal['type']].')</h3>
	<br>
	<div align="center"><button type="button" onClick="javascript:window.location=\'./editmal.php?mal_id='.$mal['mal_id'].'\';">'.$LANG['MISC']['rename'].'</button></div>
	<br>
	'.$buttons;

$maldata = get_maldata($mal['mal_id']);
if (!$maldata) {
	echo '<h4 align="center">'.$LANG['MISC']['empty_template'].'</h4>';
} else {
echo '
	<table border="0" cellpadding="3" cellspacing="0" align="center">
		<tr class="highlight">
			<td>'.$LANG['MISC']['title'].'</td>
			<td>'.$LANG['MISC']['internal'].'</td>
			<td>'.$LANG['MISC']['mandatory'].'</td>
			<td>'.$LANG['MISC']['type'].'</td>
			<td>'.$LANG['MISC']['help'].'</td>
			<td>'.$LANG['MISC']['info'].'</td>
			<td align="center">&nbsp;</td>
		</tr>
	';
foreach ($maldata as $malinfo) {
	if (($malinfo['type'] == 'header') || ($malinfo['type'] == 'separator') || ($malinfo['type'] == 'check') || ($malinfo['type'] == 'calc')) {
		$mand = 'N/A';
	} elseif ($malinfo['mand']) {
		$mand = $LANG['MISC']['yes'];
	} else {
		$mand = $LANG['MISC']['no'];
	}
	if ($malinfo['intern']) {
		$intern = $LANG['MISC']['yes'];
	} else {
		$intern = $LANG['MISC']['no'];
	}
	if ($malinfo['type'] == 'separator') {
		$malinfo['fieldtitle'] = '------';
	}
	$extras = explode(';', $malinfo['extra']);
	echo '
		<tr>
			<td><strong>'.$malinfo['fieldtitle'].'</strong></td>
			<td align="center">'.$intern.'</td>
			<td align="center">'.$mand.'</td>
			<td>'.$malinfo['type'].'</td>
			<td align="center">'; if ($malinfo['hjelp']) { echo hjelp_icon($malinfo['fieldtitle'], $malinfo['hjelp']); } else { echo 'N/A'; } echo '</td>
			<td nowrap>
	';
		switch ($malinfo['type']) {
			case 'inline':
				echo $LANG['MISC']['width'].': '.$extras[0];
				break;
			case 'inlinebox':
				echo $LANG['MISC']['height'].': '.$extras[0].'<br>'.$LANG['MISC']['width'].': '.$extras[1];
				break;
			case 'box':
				echo $LANG['MISC']['height'].': '.$extras[0];
				break;
			case 'listsingle':
			case 'listmulti':
			case 'radio':
				for ($i = 1; $i < count($extras); $i++) {
					$extra .= $LANG['MISC']['option'].' #'.$i.': '.$extras[$i].'<br>';
				}
				echo $LANG['MISC']['number_of_options'].': '.$extras[0].'<br>'.substr(trim($extra), 0, -1);
				unset($extra);
				break;
			case 'check':
				echo $extras[0].' '.$LANG['MISC']['or'].' '.$extras[1];
				break;
			case 'calc':
				$calcfield = get_maldata($_GET['mal_id']);
				$calc = str_replace('X', '\''.$calcfield[$extras[0]]['fieldtitle'].'\'', $extras[1]);
				@eval("\$calcresult = ".$calc.";");
				if (!$calcresult) {
					echo '<span title="'.$LANG['ERROR']['template_formula_error'].'" class="red">'.$calc.'</span>';
				} else {
					echo $calc;
				}
				break;
			case 'dots':
			    echo $LANG['MISC']['max_dots_value'].': '.$extras[0];
			    break;
		}
		$arrownum++;
		echo '
			</td>
			<td>
				<table cellspacing="0" cellspacing="0">
					<tr>
						<td class="nospace"><a href="./vismal.php?mal_id='.$malinfo['mal_id'].'&amp;pri_ned='.$malinfo['fieldname'].'" onMouseover="javascript:lightup(\'arrowup'.$arrownum.'\')" onMouseout="javascript:turnoff(\'arrowup'.$arrownum.'\')"><img name="arrowup'.$arrownum.'" title="'.$LANG['MISC']['move_up'].'" src="'.$styleimages['arrowup'].'" height="15" width="15" alt=""></a></td>
						<td class="nospace"><a href="./vismal.php?mal_id='.$malinfo['mal_id'].'&amp;pri_opp='.$malinfo['fieldname'].'" onMouseover="javascript:lightup(\'arrowdown'.$arrownum.'\')" onMouseout="javascript:turnoff(\'arrowdown'.$arrownum.'\')"><img name="arrowdown'.$arrownum.'" title="'.$LANG['MISC']['move_down'].'" src="'.$styleimages['arrowdown'].'" height="15" width="15" alt=""></a></td>
						<td class="nospace"><button onClick="javascript:window.location=\'./editmal.php?mal_id='.$mal['mal_id'].'&amp;editfelt='.$malinfo['fieldname'].'\';">'.$LANG['MISC']['edit'].'</button></td>
						<td class="nospace"><button onClick="javascript:return confirmDelete(\''.addslashes($malinfo['fieldtitle']).'\', \'./vismal.php?mal_id='.$mal['mal_id'].'&amp;slettfelt='.$malinfo['fieldname'].'\');">'.$LANG['MISC']['delete'].'</button></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="7" class="bb"></td>
		</tr>
	';
}
echo '
	</table>
	'.$buttons;
}
include('footer.php');
?>
