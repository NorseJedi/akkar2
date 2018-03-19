<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                           editrolleforslag.php                          #
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
$spill_id = $_REQUEST['spill_id'];
$rolle_id = $_REQUEST['rolle_id'];

if ($_GET['override_lock']) {
	unlock_rolleforslag($rolle_id, $spill_id);
	$_SESSION['message'] = $LANG['MESSAGE']['characterlock_override'];
	header('Location: ./editrolleforslag.php?rolle_id='.$rolle_id.'&spill_id='.$spill_id);
	exit();
} elseif ($locked = check_lock_rolleforslag($rolle_id, $spill_id)) {
	include('header.php');
	$arrangor = get_person($locked[1]);
	echo '
		<div align="center">
		<h3>'.$LANG['MESSAGE']['character_locked_by'].' '.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</h3>
		<h4>'.$LANG['MESSAGE']['lock_created_at'].' '.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $locked[0])).'</h4>
		<br>
		<button onClick="javascript:return confirmOverride(\'./editrolleforslag.php?rolle_id='.$rolle_id.'&amp;spill_id='.$spill_id.'&amp;override_lock=yes\');">'.$LANG['MISC']['override'].'</button>
		</div>
		';
	exits();
}

include('header.php');
echo '
	<script language="JavaScript" type="text/javascript">
	function check_dots(fieldname, num, max) {
		for (i = 1; i <= max; i ++) {
		    if (i <= num) {
		    	document.getElementById(\'mal_\' + fieldname + \'_\' + i).checked = true;
			} else {
		    	document.getElementById(\'mal_\' + fieldname + \'_\' + i).checked = false;
		    }
		}
	}
	function check_spillersrc() {
		if (document.getElementById(\'spiller_id\').value != \'\') {
			document.getElementById(\'spiller_tekst\').disabled = true;
		} else {
			document.getElementById(\'spiller_tekst\').disabled = false;
		}
		if (document.getElementById(\'spiller_tekst\').value != \'\') {
			document.getElementById(\'spiller_id\').disabled = true;
		} else {
			document.getElementById(\'spiller_id\').disabled = false;
		}
	}
	</script>
	<form name="editrolle" action="visrolleforslag.php" method="post" enctype="multipart/form-data" onSubmit="javascript:convert_funky_letters(this);">
';
if ($_GET['nyrolle']) {
	if (!$spill_id) {
		$spill_liste = get_spill();
		echo '
			</form>
			<form name="velgspillform" action="editrolleforslag.php" method="get">
			<input type="hidden" name="nyrolle" value="yes">
			<table align="center">
				<tr class="highlight">
					<td colspan="2">'.$LANG['MISC']['select_game'].'</td>
				</tr>
				<tr>
					<td><select name="spill_id">
						<option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>
		';
		foreach ($spill_liste as $spill) {
			echo '<option value="$spill[spill_id]">'.$spill['navn'].'</option>';
		}
		echo '
						</select>
					</td>
					<td><button type="submit">'.$LANG['MISC']['continue'].'</button></td>
				</tr>
			</table>
			</form>
		';
		exits();
	}
	$fields = get_fields($table_prefix.'rolleforslag');
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['rollemal'];
	$mal = get_rollemal($spill_id);
	$rolle = get_dummy_rolleforslag($spill_id);
	echo '
	<input type="hidden" name="ny" value="yes">
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<h2 align="center">'.$LANG['MISC']['new_character_suggestion'].'</h2>
	';
} else {
	lock_rolleforslag($rolle_id, $spill_id);
	$rolle = get_rolleforslag($rolle_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$mal = get_rollemal($spill_id);
	echo '
		<input type="hidden" name="edited" value="yes">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="rolle_id" value="'.$rolle_id.'">
		<h2 align="center">'.$LANG['MISC']['edit_character_suggestion'].'</h2>
		<h3 align="center">'.$rolle['navn'].'</h3>
	';
}

$buttons = '
<table align="center">
	<tr>
';
if (!$_GET['nyrolle']) {
	$buttons .= '
		<td><button type="button" onClick="javascript:window.location=\'./visrolleforslag.php?rolle_id='.$rolle_id.'&amp;spill_id='.$spill_id.'&amp;unlock=yes\';">'.$LANG['MISC']['charactersheet'].'</button></td>
	';
}
$buttons .= '
		<td><button type="reset" onClick="javascript:return window.confirm(\''.$LANG['JSBOX']['confirm_reset'].'\');">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
';

$arrownum = 0;
echo '
	'.$buttons.'
	<br>
	<table border="0" align="center" width="0">
';
foreach ($rolle as $fieldname => $value) {
	$value = stripslashes($value);
	if (strstr($fieldname, 'field')) {
		$value = stripslashes($value);
		$fieldinfo = $mal[$fieldname];
		$extras = explode(';',$fieldinfo['extra']);
		if ($fieldinfo['mand'] == 1) {
			if ($fieldinfo['type'] == 'radio') {
				$validatefunc .= 'if (';
				for ($i = 1; $i < $extras[0]+1; $i++) {
					$validatefunc .= $ifand.'(document.getElementById(\'mal_'.$fieldname.'_'.$extras[$i].'\').checked == false)';
					$ifand = ' && ';
				}
				$validatefunc .= ') {
					window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
					document.getElementById(\'mal_'.$fieldname.'_'.$extras[1].'\').focus();
					return false;
				}
				';
				unset($ifand);
			} elseif ($fieldinfo['type'] == 'dots') {
				$validatefunc .= 'if (';
				for ($i = 1; $i < $extras[0]+1; $i++) {
					$validatefunc .= $ifand.'(document.getElementById(\'mal_'.$fieldname.'_'.$i.'\').checked == false)';
					$ifand = ' && ';
				}
				$validatefunc .= ') {
					window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
					document.getElementById(\'mal_'.$fieldname.'_1\').focus();
					return false;
				}
				';
				unset($ifand);
			} else {
				$validatefunc .= '
				if (document.getElementById(\'mal_'.$fieldname.'\').value == \'\') {
					window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
					document.getElementById(\'mal_'.$fieldname.'\').focus();
					return false;
				}
				';
			}
		}
		switch ($fieldinfo['type']) {
			case 'inline':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><input type="text" size="'.$extras[0].'" name="rolle['.$fieldname.']" value="'.htmlspecialchars($value).'"></td>
					</tr>
				';
				break;
			case 'inlinebox':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><textarea cols="'.$extras[1].'" rows="'.get_numrows($value, $extras[0]).'" name="rolle['.$fieldname.']">'.$value.'</textarea></td>
					</tr>
				';
				break;
			case 'box':
				$arrownum++;
				echo '
					<tr>
						<td colspan="2">
							<table width="100%" border="0">
								<tr>
									<td colspan="2">
										<strong>'.$fieldinfo['fieldtitle'].'</strong>
									</td>
									</tr>
									<tr>
										<td colspan="2"><textarea cols="75" rows="'.get_numrows($value, $extras[0]).'" id="'.$fieldname.'" name="rolle['.$fieldname.']">'.$value.'</textarea></td>
								</tr>
								<tr>
									<td align="left">
									'.inputsize_less($fieldname, $arrownum).'
									</td>
									<td align="right">
									'.inputsize_more($fieldname, $arrownum).'
									</td>
									</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				';
				break;
			case 'listsingle':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><select name="rolle['.$fieldname.']">
							<option value="" style="margin-bottom: 1em; font-style: italic;">- '.$LANG['MISC']['select'].' -</option>';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						echo '<option value="'.$extras[$i].'"'; if (strtolower($value) == strtolower($extras[$i])) { echo ' selected'; } echo '>'.$extras[$i].'</option>';
					}
				echo '</select>
					</td>
					</tr>
				';
				break;
			case 'listmulti':
				if ($value) {
					$thisval = unserialize($value);
					if (!is_array($thisval)) {
						$thisval = array();
					}
				} else {
					$thisval = array();
				}
				echo '
					<tr>
						<td valign="top"><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><select name="'.$fieldname.'[]" size="'.(count($extras)-1).'" multiple>';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						echo '<option value="'.$extras[$i].'"'; if (in_array($extras[$i], $thisval)) { echo ' selected'; } echo '>'.$extras[$i].'</option>';
					}
				echo '</select>
					</td>
					</tr>
				';
				break;
			case 'radio':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>
				';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					echo '<input type="radio" name="rolle['.$fieldname.']" value="'.$extras[$i].'"'; if (strtolower($value) == strtolower($extras[$i])) { echo ' checked'; } echo '>'.$extras[$i].' ';
				}
				echo '
						</td>
					</tr>
				';
				break;
			case 'check':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><input value="1" name="rolle['.$fieldname.']" type="checkbox"'; if ($value != 0) { echo ' checked'; } echo '></td>
					</tr>
				';
				break;
			case 'calc':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td><em>'.$LANG['MISC']['auto_generated'].'</em></td>
					</tr>
				';
				break;
			case 'dots':
			    echo '
			        <tr>
			            <td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
			            <td>
				';
				for ($i = 1; $i <= $extras[0]; $i++) {
				    echo '<input id="mal_'.$fieldname.'_'.$i.'" type="radio" name="rolle['.$fieldname.']_'.$i.'" value="'.$i.'" onClick="javascript:check_dots(\''.$fieldname.'\', '.$i.', '.$extras[0].')"'; if ($i <= $value) { echo ' checked'; } echo '>';
				}
				echo '</td>
					</tr>
				';
				break;
			case 'header':
			        echo '
			                <tr>
			                        <td colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
					</tr>
				';
				break;
			case 'separator':
			        echo '
			                <tr>
			                        <td colspan="2"><hr size="2"></td>
					</tr>
				';
				break;
		}
	} else {
		switch($fieldname) {
			case 'oppdatert':
			case 'rolle_id':
			case 'locked':
			case 'spill_id':
			case 'godkjent':
				break;
			case 'spiller':
				if ($paameldte = get_paameldte_og_arrangorer($spill_id)) {
					foreach ($paameldte as $paameldt) {
						$paameldingsliste[$paameldt['person_id']] = $paameldt['fornavn'].' '.$paameldt['etternavn'];
					}
				} else {
					$paameldingsliste[] = '';
				}
				unset($paameldte);
				if ($value > 0) {
					$spillernavn = '';
				} else {
					$spillernavn = $value;
				}
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['player'].'</strong></td>
						<td><input type="text" value="'.$spillernavn.'" onkeyup="javascript:check_spillersrc();" id="spiller_tekst" name="rolle['.$fieldname.']" size="20"> <select id="spiller_id" onchange="javascript:check_spillersrc();" name="rolle['.$fieldname.']"><option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>'.print_liste($paameldingsliste, $rolle['spiller']).'</select>
						</td>
				';
				break;
			case 'arrangor_id':
				$arrangorer = get_arrangorer();
				foreach ($arrangorer as $arrangor) {
					$arrangorliste[$arrangor['person_id']] = $arrangor['fornavn'].' '.$arrangor['etternavn'];
				}
				unset($arrangorer);
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
						<td><select name="rolle['.$fieldname.']"><option value="" class="selectname">- '.$LANG['MISC']['select'].' -</option>'.print_liste($arrangorliste, $rolle['arrangor_id']).'</select>
						</td>
				';
				break;
			case 'intern_info':
			case 'beskrivelse1':
			case 'beskrivelse2':
			case 'beskrivelse3':
			case 'beskrivelse_gruppe':
				$arrownum++;
				echo '
					<tr>
						<td colspan="2">
							<table width="100%" border="0">
								<tr>
									<td colspan="2">
										<strong>'.$LANG['DBFIELD'][$fieldname].'</strong>
									</td>
								</tr>
								<tr>
									<td colspan="2"><textarea cols="75" rows="'.get_numrows($value, 5).'" id="'.$fieldname.'" name="rolle['.$fieldname.']">'.$value.'</textarea></td>
								</tr>
								<tr>
									<td align="left">
									'.inputsize_less($fieldname, $arrownum).'
									</td>
									<td align="right">
									'.inputsize_more($fieldname, $arrownum).'
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				';
				break;
			default:
				echo '
					<tr>
						<td><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
						<td><input type="text" name="rolle['.$fieldname.']" value="'.htmlspecialchars($value).'"></td>
					</tr>
				';
		}
	}
}
echo '
		<tr>
			<td colspan="2" align="center">
			'.$buttons.'
			</td>
		</tr>
</table>
</form>
<script language="JavaScript" type="text/javascript">
	check_spillersrc()
</script>
';

include('footer.php');
?>
