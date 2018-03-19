<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            visrolleforslag.php                          #
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
	oppdater_rolleforslag();
	$_SESSION['message'] = $LANG['MESSAGE']['character_suggestion_updated'];
	header('Location: ./visrolleforslag.php?rolle_id='.$_POST['rolle_id'].'&spill_id='.$_POST['spill_id']);
	exit();
} elseif ($_POST['ny']) {
	$rolle_id = opprett_rolleforslag();
	$_SESSION['message'] = $LANG['MESSAGE']['character_suggestion_created'];
	header('Location: ./visrolleforslag.php?rolle_id='.$rolle_id.'&spill_id='.$_POST['spill_id']);
	exit();
}
$rolle_id = $_REQUEST['rolle_id'];

if (!$spill_id && !$rolle_id) {
	exit($LANG['ERROR']['no_char_or_game_selected']);
}
include('header.php');

$rolle = get_rolleforslag($rolle_id, $spill_id);
$spillinfo = get_spillinfo($spill_id);
$mal_id = $spillinfo['rollemal'];
$malinfo = get_maldata($mal_id);
$buttons = '
<table align="center">
	<tr>
		<td class="nospace"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
		<td class="nospace"><button type="button" onClick="javascript:window.location=\'./rolleforslag.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['character_suggestions'].'</button></td>
		<td class="nospace"><button type="button" onClick="javascript:return confirmDelete(\''.addslashes($rolle['navn']).'\', \'./rolleforslag.php?spill_id='.$spill_id.'&amp;slett_rolle='.$rolle_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
';
echo '
	<h2 align="center">'.$LANG['MISC']['character_suggestion'].'</h2>
	<h3 align="center">'.$rolle['navn'].'</h3>
	<div align="center" class="small">'.$LANG['MISC']['updated'].': '.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $rolle['oppdatert'])).'</div>
';
if ($rolle['godkjent']) {
	$godkjent = unserialize($rolle['godkjent']);
	$godkjent_av = get_person($godkjent[1]);
	$godkjent_tid = ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $godkjent[0]));
	echo '
		<h4 align="center" class="green">'.$LANG['MESSAGE']['character_is_approved'].'</h4>
		<h6 align="center">'.$LANG['MISC']['approved_by'].' <a href="./visperson.php?person_id='.$godkjent_av['person_id'].'">'.$godkjent_av['fornavn'].' '.$godkjent_av['etternavn'].'</a> '.$godkjent_tid.'</h6>
		<br>
		<div align="center"><button type="button" onClick="javascript:window.location=\'./visrolle.php?rolle_id='.$godkjent[2].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['charactersheet'].'</button></div>
	';
	$buttons .= '
		<td class="nospace"><button type="button" onClick="javascript:confirmAction(\''.$LANG['JSBOX']['edit_approved_character'].'\', \'./editrolleforslag.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\');">'.$LANG['MISC']['edit'].'</button></td>
		<td class="nospace"><button type="button" onClick="javascript:confirmAction(\''.$LANG['JSBOX']['character_already_approved'].'\', \'./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;overfor_forslag=yes\');">'.$LANG['MISC']['approve'].'</button></td>
	';
} else {
	$buttons .= '
		<td class="nospace"><button type="button" onClick="javascript:window.location=\'./editrolleforslag.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
		<td class="nospace"><button type="button" onClick="javascript:window.location=\'./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;overfor_forslag=yes\';">'.$LANG['MISC']['approve'].'</button></td>
	';
}
$buttons .= '
	</tr>
</table>
';

echo '
	<br>
	'.$buttons.'
	<br>
	<table border="0" align="center" width="60%">
';
foreach ($rolle as $fieldname => $value) {
	if (is_int(strpos($fieldname, 'field'))) {
		$fieldinfo = get_malentry($fieldname, $mal_id);
		$extras = explode(';',$fieldinfo['extra']);
		switch ($fieldinfo['type']) {
			case 'inline':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			case 'inlinebox':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			case 'box':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
					</tr>
					<tr>
						<td colspan="2">'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			case 'listsingle':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					if (strtolower($value) == strtolower($extras[$i])) { 
						echo ucwords(stripslashes($extras[$i])); 
					}
				}
				echo '</td>
					</tr>
				';
				break;
			case 'listmulti':
				$values = unserialize($value);
				unset($value);
				if (!is_array($values)) {
					$value = $LANG['MISC']['none'];
				} else {
					foreach ($values as $thisval) {
						$value .= stripslashes($thisval).', ';
					}
					$value = substr($value, 0, -2);
				}
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>'.$value.'</td>
					</tr>
				';
				break;
			case 'radio':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					if (strtolower($value) == strtolower($extras[$i])) { 
						echo ucwords(stripslashes($extras[$i])); 
					}
				}
				echo '</td>
					</tr>
				';
				break;
			case 'check':
				echo '
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td nowrap>'; if ($value != 0) { echo $extras[0]; } else { echo $extras[1]; } echo '</td>
					</tr>
				';
				break;
			case 'calc':
				$calc = get_calc_formula($rolle[$malinfo[$extras[0]]['fieldname']], $extras[1]);
				@eval('\$calcresult = '.$calc.';');
				echo '
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td nowrap>'.$calcresult.'</td>
					</tr>
				';
				break;
			case 'dots':
			    echo '
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td nowrap><span>
				';
				for ($i = 1; $i <= $value; $i++) {
				    echo '<img src="'.$styleimages['dot'].'">&nbsp;';
				}
				for ($i = $value; $i < $extras[0]; $i++) {
				    echo '<img src="'.$styleimages['nodot'].'">&nbsp;';
				}
				echo '
						</span></td>
					</tr>
				';
			    break;
			case 'header':
			        echo '
			                <tr>
			                        <td nowrap colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
					</tr>
				';
				break;
			case 'separator':
			        echo '
			                <tr>
			                        <td nowrap colspan="2"><hr size="2"></td>
					</tr>
				';
				break;
		}
	} else {
		switch($fieldname) {
			case 'oppdatert':
			case 'rolle_id':
			case 'locked':
			case 'bilde':
			case 'godkjent':
			case 'spill_id':
				break;
			case 'spiller':
				if ($value > 0) {
					$spiller = get_person($value);
					$spillernavn = '<a href=\'./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$spill_id.'\'>'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a>';
				} else {
					$spillernavn = $value;
				}
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['player'].'</strong></td>
						<td nowrap>'.$spillernavn.'</td>';
				break;
			case 'arrangor_id':
				$person = get_person($value);
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
						<td nowrap><a href="visperson.php?person_id='.$person['person_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a></td>';
				break;
			case 'intern_info':
			case 'beskrivelse1':
			case 'beskrivelse2':
			case 'beskrivelse3':
			case 'beskrivelse_gruppe':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
					</tr>
					<tr>
						<td colspan="2">'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			default:
				echo '
					<tr>
						<td><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
						<td>'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
		}
	}
}


echo '
	<tr>
		<td colspan="2" class="bt">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			'.$buttons.'
		</td>
	</tr>
</table>

';

include('footer.php');
?>
