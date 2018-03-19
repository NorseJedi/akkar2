<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              visplott.php                               #
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
if ($_POST['edited']) {
	oppdater_plott();
	$_SESSION['message'] = $LANG['MESSAGE']['plot_updated'];
	header('Location: ./visplott.php?plott_id='.$_REQUEST['edited'].'&spill_id='.$_REQUEST['spill_id']);
	exit();
} elseif ($_POST['nytt_plott']) {
	$plott_id = opprett_plott();
	$_SESSION['message'] = $LANG['MESSAGE']['plot_created'];
	header('Location: ./visplott.php?plott_id='.$plott_id.'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_GET['fjern_medlem']) {
	fjern_plott_medlem($_GET['type']);
	if ($_GET['type'] == 'gruppe') {
		$_SESSION['message'] = $LANG['MESSAGE']['group_removed_from_plot'];
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['character_removed_from_plot'];
	}
	header('Location: ./visplott.php?plott_id='.$_GET['plott_id'].'&spill_id='.$_GET['spill_id']);
	exit();
} elseif ($_POST['ny_gruppe'] || $_POST['ny_rolle']) {
	if ($_POST['ny_gruppe']) {
		ny_plott_medlem($_POST['ny_gruppe'], 'gruppe');
		$_SESSION['message'] = $LANG['MESSAGE']['group_added_to_plot'];
	} else {
		ny_plott_medlem($_POST['ny_rolle'], 'rolle');
		$_SESSION['message'] = $LANG['MESSAGE']['character_added_to_plot'];
	}
	header('Location: ./visplott.php?plott_id='.$_POST['plott_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_POST['redigert_medlem']) {
	oppdater_plott_medlem($_POST['type']);
	if ($_POST['type'] == 'gruppe') {
		$_SESSION['message'] = $LANG['MESSAGE']['group_relation_updated'];
	} else {
		$_SESSION['message'] = $LANG['MESSAGE']['character_relation_updated'];
	}
	header('Location: ./visplott.php?plott_id='.$_POST['plott_id'].'&spill_id='.$_POST['spill_id']);
	exit();
}

if (!$_REQUEST['spill_id'] || !$_REQUEST['plott_id']) {
	exit($LANG['ERROR']['no_plot_selected']);
} else {
	$plott_id = $_REQUEST['plott_id'];
}
include('header.php');

$plott = get_plott($plott_id, $spill_id);

echo '
	<h2 align="center">'.$LANG['MISC']['plot'].'</h2>
	<h3 align="center">'.$plott['navn'].'</h3>
	<br>
	<table border="0" align="center">
	<tr>
		<td><strong>'.$LANG['MISC']['description'].'</strong></td>
	</tr>
	<tr>
		<td>'.nl2br($plott['beskrivelse']).'</td>
	</tr>
</table>
<table align="center">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><button onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td><button onClick="javascript:window.location=\'./plott.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['plots'].'</button></td>
		<td><button onClick="javascript:window.location=\'./editplott.php?plott_id='.$plott_id.'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['edit'].'</button></td>
		<td><button class="red" onClick="javascript:return confirmDelete(\''.addslashes($plott['navn']).'\', \'./plott.php?slett_plott='.$plott_id.'&amp;spill_id='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
	</tr>
</table>
';

$plottroller = get_plott_roller($plott_id, $spill_id);
echo '
	<table align="center" cellspacing="0" border="0">
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
';
if (!$plottroller) {
	echo '
		<tr>
			<td colspan="2"><h4 class="table" align="center">'.$LANG['MISC']['no_related_characters'].'</h4></td>
		</tr>
	';
} else {
	echo '
		<tr>
			<td colspan="2"><h4 class="table" align="center">'.$LANG['MISC']['related_characters'].'</h4></td>
		</tr>
	';
	foreach ($plottroller as $rolle) {
		$spiller = get_person($rolle['spiller_id']);
		echo '
		<tr class="highlight">
			<td>
				<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a> (<a href="./vispaamelding.php?person_id='.$rolle['spiller_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>)
			</td>
			<td class="nospace" align="right" nowrap>
				<button onClick="javascript:window.location=\'./editplott.php?plott_id='.$plott_id.'&amp;spill_id='.$spill_id.'&amp;rediger_medlem='.$rolle['rolle_id'].'&amp;type=rolle\';">'.$LANG['MISC']['edit'].'</button>
				<button onClick="javascript:window.location=\'./visplott.php?plott_id='.$plott_id.'&amp;spill_id='.$spill_id.'&amp;fjern_medlem='.$rolle['rolle_id'].'&amp;type=rolle\';">'.$LANG['MISC']['remove'].'</button>
			</td>
		</tr>
		<tr>
			<td>'.nl2br($rolle['tilknytning']).'</td>
		</tr>
		<tr>
			<td class="bb" colspan="2">&nbsp;</td>
		</tr>
		';
	}
}
echo '
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
';
$plottgrupper = get_plott_grupper($plott_id, $spill_id);
if (!$plottgrupper) {
	echo '
		<tr>
			<td colspan="2"><h4 class="table" align="center">'.$LANG['MISC']['no_related_groups'].'</h4></td>
		</tr>
	';
} else {
	echo '
		<tr>
			<td colspan="2"><h4 class="table" align="center">'.$LANG['MISC']['related_groups'].'</h4></td>
		</tr>
	';
	foreach ($plottgrupper as $gruppe) {
		$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
		unset($medlemsliste);
		if (!$medlemmer) {
			$medlemsliste = '<em>'.$LANG['MISC']['empty_group'].'</em>';
		} else {
			foreach ($medlemmer as $medlem) {
				$medlemsliste .= '<a href="./visrolle.php?rolle_id='.$medlem['rolle_id'].'&amp;spill_id='.$medlem['spill_id'].'">'.$medlem['navn'].'</a>, ';
			}
			$medlemsliste = substr(trim($medlemsliste), 0, -1);
		}
		echo '
		<tr class="highlight">
			<td>
				<a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a>
			</td>
			<td class="nospace" nowrap align="right">
				<button onClick="javascript:window.location=\'./editplott.php?plott_id='.$plott_id.'&amp;spill_id='.$spill_id.'&amp;rediger_medlem='.$gruppe['gruppe_id'].'&amp;type=gruppe\';">'.$LANG['MISC']['edit'].'</button>
				<button onClick="javascript:window.location=\'./visplott.php?plott_id='.$plott_id.'&amp;spill_id='.$spill_id.'&amp;fjern_medlem='.$gruppe['gruppe_id'].'&amp;type=gruppe\';">'.$LANG['MISC']['remove'].'</button>
			</td>
		</tr>
		<tr>
			<td colspan="3">('.$medlemsliste.')</td>
		</tr>
		<tr>
			<td colspan="3">'.nl2br($gruppe['tilknytning']).'</td>
		</tr>
		<tr>
			<td class="bb" colspan="2">&nbsp;</td>
		</tr>
		';
	}
}

echo '
</table>
';
echo '
<script language="JavaScript" type="text/javascript">
	function validate(el) {
		if ((el.ny_rolle.value == \'\') && (el.ny_gruppe.value == \'\')) {
			window.alert(\''.$LANG['JSBOX']['select_char_or_group'].'\');
			if (el.ny_rolle.disabled == true) {
				el.ny_gruppe.focus();
			} else {
				el.ny_rolle.focus();
			}
			return false;
		}
		if (el.tilknytning.value == \'\') {
			window.alert(\''.$LANG['JSBOX']['plot_relation'].'\');
			el.tilknytning.focus();
			return false;
		}
		return true;
	}
	function rolle_eller_gruppe(el) {
		if (el.ny_rolle.value != \'\') {
			el.ny_gruppe.value = \'\';
			el.ny_gruppe.disabled = true;
		} else {
			el.ny_gruppe.disabled = false;
		}
		if (el.ny_gruppe.value != \'\') {
			el.ny_rolle.value = \'\';
			el.ny_rolle.disabled = true;
		} else {
			el.ny_rolle.disabled = false;
		}
	}
</script>
<form class="noprint" name="nymedlemform" action="visplott.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
<input type="hidden" name="plott_id" value="'.$plott_id.'">
<input type="hidden" name="spill_id" value="'.$spill_id.'">
<table align="center" style="margin-top: 2em;" border="0" cellspacing="0">
	<tr>
		<td class="highlight">'.$LANG['MISC']['character'].'</td>
		<td class="highlight">&nbsp;</td>
		<td class="highlight">'.$LANG['MISC']['group'].'</td>
	</tr>

	<tr>
		<td>
			<select name="ny_rolle" onChange="javascript:rolle_eller_gruppe(document.nymedlemform);">
			<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
';
$roller = get_roller($spill_id);
foreach ($roller as $rolle) {
	if (!get_plott_rolle($rolle['rolle_id'], $plott_id, $rolle['spill_id'])) {
		$spiller = get_person($rolle['spiller_id']);
		if (!$spiller) {
			$spillernavn = $LANG['MISC']['no_player'];
		} else {
			$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'];
		}
		echo '<option value="'.$rolle['rolle_id'].'">'.$rolle['navn'].' ('.$spillernavn.')</option>';
	}
}
echo '	</select>
		</td>
		<td><h4 class="table">- '.$LANG['MISC']['or'].' -</h4></td>
		<td>
			<select name="ny_gruppe" onChange="javascript:rolle_eller_gruppe(document.nymedlemform);">
			<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
';
$grupper = get_grupper($spill_id);
foreach ($grupper as $gruppe) {
	if (!get_plott_gruppe($gruppe['gruppe_id'], $plott_id, $gruppe['spill_id'])) {
		echo '<option value="'.$gruppe['gruppe_id'].'">'.$gruppe['navn'].'</option>';
	}
}
echo '	</select>
		</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3"><h4 class="table">'.$LANG['MISC']['relation'].':</h4></td>
	</tr>
	<tr>
		<td colspan="3"><textarea rows="3" cols="75" id="tilknytning" name="tilknytning"></textarea></td>
	</tr>
	<tr>
		<td align="left">
			'.inputsize_less('tilknytning', 1).'
		</td>
		<td>&nbsp;</td>
		<td align="right">
			'.inputsize_more('tilknytning', 1).'
		</td>
	</tr>
	<tr>
		<td colspan="3" align="center"><button type="submit" onClick="javascript:return validate(document.nymedlemform);">'.$LANG['MISC']['add'].'</button></td>
	</tr>
</table>
</form>';

include('footer.php');
?>
