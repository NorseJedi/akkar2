<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               betaling.php                              #
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

if ($_POST['oppdater_betaling']) {
	oppdater_betaling();
	$_SESSION['message'] = $LANG['MESSAGE']['payments_updated'];
	header('Location: ./betaling.php?spill_id='.$spill_id);
	exit();
}

include('header.php');
echo '<h2 align="center">'.$LANG['MISC']['payments'].'</h2>';
$paameldinger = get_paameldte($spill_id);
$ubet_mail_liste = $config['arrgruppemail'].',';
$bet_mail_liste = $config['arrgruppemail'].',';
$mail_liste = $config['arrgruppemail'].',';
$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
if ($paameldinger) {
foreach ($paameldinger as $paamelding) {
	if (!$paamelding['betalt']) {
		$ubetalte[] = $paamelding;
	} else {
		$betalte[] = $paamelding;
	}
}
$numubetalte = count($ubetalte);
$numbetalte = count($betalte);
$numpaameldinger = count($paameldinger);
$buttons = '
<table align="center">
	<tr>
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
	</tr>
</table>
';
}

if (!$paameldinger) {
	echo '
		<h4 align="center">'.$LANG['MISC']['no_registrations'].'</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>
	';
} else {
	$fields = array(
		'etternavn',
		'fornavn',
		'fodt',
		'alder',
		'adresse',
		'postnr',
		'poststed',
		'telefon',
		'mobil',
		'email',
		'paameldt',
		'betalt'
	);
	echo '
		<h4 align="center">
		'.$numpaameldinger.' '.$LANG['MISC']['registration_s'].'<br>
		'.$numbetalte.' '.$LANG['MISC']['paid_s'].'<br>
		'.$numubetalte.' '.$LANG['MISC']['unpaid_s'].'<br>
		</h4>
		<table align="center">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center"><button onClick="javascript:window.location=\'./sendmail.php?sendto=ubetalte&spill_id='.$spill_id.'\';">'.$LANG['MISC']['mail_unpaid'].'</button></td>
				<td align="center"><button onClick="javascript:window.location=\'./sendmail.php?sendto=betalte&spill_id='.$spill_id.'\';">'.$LANG['MISC']['mail_paid'].'</button></td>
				<td align="center"><button onClick="javascript:window.location=\'./sendmail.php?sendto=paameldte&spill_id='.$spill_id.'\';">'.$LANG['MISC']['mail_all'].'</button></td>
			</tr>
		</table>
		<br>
	';
	if ((!$_REQUEST['utskrift']) && ($spill_id != 0)) {
		echo '
		<form method="post" action="betaling.php" name="nyvisningform">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="nybetalingvis" value="yes">
		<input type="hidden" name="betalingorder" value="'.$betalingorder.'">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		';
		foreach ($fields as $fieldname) {
			switch ($fieldname) {
				case 'type':
				case 'person_id':
				case 'betalt':
				case 'spill_id':
					break;
				case 'rolle_id':
					echo '
						<td align="left" nowrap><input type="checkbox" name="betalingvis['.$fieldname.']"'; if ($_SESSION['betalingvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['character'].'</td>
					';
					check_for_new_row($j, 5);
					break;
				case 'fodt':
					echo '
						<td align="left" nowrap><input type="checkbox" name="betalingvis['.$fieldname.']"'; if ($_SESSION['betalingvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['MISC']['birthdate'].'</td>
					';
					check_for_new_row($j, 5);
					break;
				default:
					echo '
						<td align="left" nowrap><input type="checkbox" name="betalingvis['.$fieldname.']"'; if ($_SESSION['betalingvis'][$fieldname]) { echo ' checked'; } echo '>'.$LANG['DBFIELD'][$fieldname].'</td>
					';
					check_for_new_row($j, 5);
			}
		}
		echo '
			<td align="left" nowrap><input type="checkbox" name="betalingvis[rolle]"'; if ($_SESSION['betalingvis']['rolle']) { echo ' checked'; } echo '>'.$LANG['MISC']['character'].'</td>
		';
		check_for_new_row($j, 5);
		echo '
		</tr>
		<tr><td colspan="6" align="center">
			<button onClick="javascript:document.nyvisningform.submit();">'.$LANG['MISC']['change_view'].'</button>
		</td>
		</tr>
		</table>
		</form>
		';
	}
	echo '
		<form name="betalingsform" action="betaling.php" method="post">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="oppdater_betaling" value="yes">
		'.$buttons.'
		<table border="0" cellpadding="3" cellspacing="0" align="center" width="90%">
			<tr class="noprint">
				<td colspan="'.(count($_SESSION['betalingvis'])+1).'" class="nospace"><input type="checkbox" tabindex="'.$tabindex++.'" onClick="javascript:showhide(\'filters\');" id="filterbox"> <strong>'.$LANG['MISC']['show_filters'].'</strong></td>
			</tr>
			<tr valign="top" class="highlight">
		';
		foreach ($fields as $fieldname) {
			if ($_SESSION['betalingvis'][$fieldname]) {
				$sorting = get_sorting('./betaling.php?spill_id='.$spill_id, $fieldname, 'betalingorder');
				switch ($fieldname) {
					case 'betalt':
						break;
					case 'fodt':
						echo '<td nowrap="nowrap">'.$LANG['MISC']['birthdate'].' '.$sorting.'</td>';
						break;
					case 'paameldt':
						echo '<td nowrap="nowrap">'.$LANG['MISC']['registered'].' '.$sorting.'</td>';
						break;
					default:
						echo '<td nowrap="nowrap">'.$LANG['DBFIELD'][$fieldname].' '.$sorting.'</td>';
				}
			}
		}
		if ($_SESSION['betalingvis']['rolle']) {
			echo '<td nowrap>'.$LANG['MISC']['character_s'].'</td>';
		}
		$sorting = get_sorting('./betaling.php?spill_id='.$spill_id, 'betalt', 'betalingorder');
		echo '
			<td nowrap align="center" width="1%">'.$LANG['DBFIELD']['betalt'].' '.$sorting.'</td>
			</tr>
			<tbody id="filters">
			<tr class="highlight">
		';
		foreach ($fields as $fieldname) {
			if ($_SESSION['betalingvis'][$fieldname]) {
				echo '
					<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['DBFIELD'][$fieldname]).'" type="text" id="'.$fieldname.'_filter" title="'.$fieldname.'_filter" onkeyup="javascript:filter_list(this.value, \''.$fieldname.'\');"></td>
				';
			}
		}
		if ($_SESSION['betalingvis']['rolle']) {
				echo '
					<td><input class="filterbox" tabindex="'.$tabindex++.'" size="'.strlen($LANG['MISC']['character']).'" type="text" id="rolle_filter" title="rolle_filter" onkeyup="javascript:filter_list(this.value, \'rolle\');"></td>
				';
		}
		echo '
			<td colspan="2">&nbsp;</td>
			</tr>
			</tbody>
	';
	foreach ($paameldinger as $paamelding) {
		echo '<tr>';
		# Traverse the new array and show the selected values
		foreach ($paamelding as $fieldname => $value) {
			if ($_SESSION['betalingvis'][$fieldname]) {
				switch ($fieldname) {
					case 'fornavn':
					case 'etternavn':
						echo '
							<td id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$value.'" nowrap><a href="vispaamelding.php?person_id='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'">'.$value.'</a></td>
						';
						break;
					case 'paameldt':
						echo '
							<td id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.ucfirst(strftime($config['medium_dateformat'], $value)).'" nowrap>'.ucfirst(strftime($config['medium_dateformat'], $value)).'</td>
						';
						break;
					case 'fodt':
						$dato = explode('-', '$value');
						$fodt = abs($dato[2]).'. '.substr($mndliste[abs($dato[1])], 0, 3).' '.$dato[0];
						echo '
							<td id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$fodt.'" nowrap="nowrap">'.$fodt.'/td>
						';
						break;
					case 'alder':
						$fodt = explode('-', $paamelding['fodt']);
						$then = $fodt[0].$fodt[1].$fodt[2];
						$now = date('Ymd');
						$tmpalder = $now-$then;
						if (strlen($tmpalder) == 4) {
							$value = '0 '.$LANG['MISC']['years_old'];
				 		} elseif (strlen($tmpalder) == 5) {
							$value = substr($tmpalder,0,1).' '.$LANG['MISC']['years_old'];
						} else {
							$value = substr($tmpalder,0,2).' '.$LANG['MISC']['years_old'];
						}
						unset($dato,$fodt,$then,$now,$tmpalder);
						echo '
							<td id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$value.'" nowrap="nowrap">'.$value.'</td>
						';
						break;
					case 'mobil':
					case 'telefon':
						if ($value) {
							echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$value.'" nowrap="nowrap">'.$value.'</td>';
						} else {
							echo '<td id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>';
						}
						break;
					case 'betalt':
						break;
					default:
						echo '<td width="1%" id="'.$fieldname.'_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$value.'" nowrap="nowrap">'.$value.'</td>';
				}
			}
		}
		if ($_SESSION['betalingvis']['rolle']) {
			$roller = get_spiller_roller($paamelding['person_id'], $paamelding['spill_id']);
			if (!$roller) {
				echo '<td id="rolle_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$LANG['MISC']['none'].'" nowrap>'.$LANG['MISC']['none'].'</td>'; 
			} else {
				if (count($roller) > 1) {
					$roller_num = '('.count($roller).') ';	
				}
				foreach ($roller as $rolle) {
					$roller_value .= '<a href="visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$spill_id.'">'.$rolle['navn'].'</a>, ';
					$roller_plain .= $rolle['navn'].', ';
				}
				echo '<td id="rolle_p'.$paamelding['person_id'].'_s'.$paamelding['spill_id'].'" title="'.$roller_num.substr(trim($roller_plain), 0, -1).'">'.$roller_num.substr(trim($roller_value), 0, -1).'</td>';
				unset($roller, $rolle, $roller_value, $roller_plain, $roller_num);
			}
		}
		echo '
			<td align="center" nowrap="nowrap"><input class="nospace" type="checkbox" name="'.$paamelding['person_id'].'"'; if ($paamelding['betalt'] == 1) { echo ' checked'; } echo '></td>

		</tr>';
	}
	echo '
		</table>
		'.$buttons.'
		</form>
	';
}

include('footer.php');

?>
