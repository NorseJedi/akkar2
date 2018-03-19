<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               kalender.php                              #
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

if ($_POST['nytt_notat']) {
	if (opprett_kalnotat()) {
		$_SESSION['message'] = $LANG['MESSAGE']['note_created'];
	} else {
		$_SESSION['message'] = '<span class="red">'.$LANG['MESSAGE']['note_create_error'].'</span>';
	}
	header('Location: ./kalender.php?month='.$_POST['notat_mnd'].'&year='.$_POST['notat_aar']);
	exit();
} elseif ($_POST['edited_notat']) {
	if (oppdater_kalnotat()) {
		$_SESSION['message'] = $LANG['MESSAGE']['note_updated'];
		header('Location: ./kalender.php?month='.$_POST['notat_mnd'].'&year='.$_POST['notat_aar']);
	} else {
		$_SESSION['message'] = '<span class="red">'.$LANG['MESSAGE']['note_update_error'].'</span>';
		header('Location: ./editnotat.php?edit_notat='.$_POST['edited_notat'].'&month='.$_POST['notat_mnd'].'&year='.$_POST['notat_aar']);
	}
	exit();
} elseif ($_GET['slett_notat']) {
	slett_kalnotat($_GET['slett_notat']);
	$_SESSION['message'] = $LANG['MESSAGE']['note_deleted'];
	header('Location: ./kalender.php?month='.$_GET['month'].'&year='.$_GET['year']);
	exit();
}	


include('header.php');

if (!$_REQUEST['month']) {
	$month = abs(strftime('%m', time()));
} else {
	$month = $_REQUEST['month'];
}
if (!$_REQUEST['year']) {
	$year =  strftime('%Y', time());
} else {
	$year = $_REQUEST['year'];
}
$dager = array('Monday'=>$LANG['MISC']['monday'], 'Tuesday'=>$LANG['MISC']['tuesday'], 'Wednesday'=>$LANG['MISC']['wednesday'], 'Thursday'=>$LANG['MISC']['thursday'], 'Friday'=>$LANG['MISC']['friday'], 'Saturday'=>$LANG['MISC']['saturday'], 'Sunday'=>$LANG['MISC']['sunday']);
if (($_SESSION['kalendervis']['merkedager']) && is_file('lang/'.$config['lang'].'_calext.php')) {
	include_once('lang/'.$config['lang'].'_calext.php');
/*	# Possible code for parsed fileformat for later use.
	$merkedager_entries = file('lang/'.$config['lang'].'_calext2.def');
	foreach ($merkedager_entries as $line) {
		$data = explode(';', $line);
		if (strtolower(trim($data[0])) == 'merkedag') {
			array_shift($data);
			if (count($data) > 3) {
				for ($i = 4; $i < count($data); $i++) {
					$data[3] = $data[3].';'.$data[$i];
				}
			}
			foreach ($data as $key=>$value) {
				$data[$key] = trim($value);
			}
			$merkedager[gregoriantojd($data[0],$data[1],$year)] = '<a onClick="javascript:return overlib('$data[3]', CAPTION, '$data[2]');">$data[2]</a>';
		}
	}*/
}

if ($_SESSION['kalendervis']['spillerbursdager']) {
	$spillere = get_spillere();
	foreach ($spillere as $person) {
		$dato = explode('-', $person['fodt']);
		$bursdager[gregoriantojd($dato[1],$dato[2],$year)][] = '<a href="./visperson.php?person_id='.$person['person_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a> ('.($year - $dato[0]).')';
	}
	unset($spillere,$person,$dato);
}

if ($_SESSION['kalendervis']['arrangorbursdager']) {
	$arrangorer = get_arrangorer();
	foreach ($arrangorer as $person) {
		$dato = explode('-', $person['fodt']);
		$bursdager[gregoriantojd($dato[1],$dato[2],$year)][] = '<a href="./visperson.php?person_id='.$person['person_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a> ('.($year - $dato[0]).')';
	}
	unset($arrangorer,$person,$dato);
}


if ($_SESSION['kalendervis']['spilldager']) {
	$spill = get_spill();
	foreach ($spill as $spillinfo) {
		$start = gregoriantojd(strftime('%m', $spillinfo['start']), strftime('%d', $spillinfo['start']), strftime('%Y', $spillinfo['start']));
		$slutt = gregoriantojd(strftime('%m', $spillinfo['slutt']), strftime('%d', $spillinfo['slutt']), strftime('%Y', $spillinfo['slutt']));
		for ($i = $start; $i <= $slutt; $i++) {
			if ($start == $slutt) {
				$datomerknader[$i][] = '<strong>'.$LANG['MISC']['game'].':</strong> <a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a>';
			} elseif ($i == $start) {
				$datomerknader[$i][] = '<strong>'.$LANG['MISC']['game_start'].':</strong> <a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a>';
			} elseif ($i == $slutt) {
				$datomerknader[$i][] = '<strong>'.$LANG['MISC']['game_end'].':</strong> <a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a>';
			} else {
				$datomerknader[$i][] = '<a href="./visspill.php?spill_id='.$spillinfo['spill_id'].'">'.$spillinfo['navn'].'</a>';
			}
		}
	}
	unset($personer, $spill, $start, $slutt);
}

if ($deadlines = get_deadlines(0)) {
	foreach ($deadlines as $deadline) {
		$jddato = unixtojd($deadline['deadline']);
		$datomerknader[$jddato][] = gamedeadline_icon($deadline).'&nbsp;'.$LANG['MISC']['game'].'<br>';
	}
	unset($deadlines);
}
if ($deadlines = get_mine_oppgaver()) {
	foreach ($deadlines as $deadline) {
		$jddato = unixtojd($deadline['deadline']);
		$datomerknader[$jddato][] = taskdeadline_icon($deadline).'&nbsp;'.$LANG['MISC']['task'].'<br>';
	}
	unset($deadlines);
}

if ($_SESSION['kalendervis']['helligdager']) {
	if (is_file('lang/'.$config['lang'].'_calext.php')) {
		include_once('lang/'.$config['lang'].'_calext.php');
		if (!$_SESSION['kalendervis']['merkedager']) {
			unset($merkedager);
		}
	} else {
		$helligdager = array();
	}
	$helligdager[gregoriantojd(1,1,$year)] = $LANG['MISC']['new_years_day'];
	$helligdager[gregoriantojd(12,24,$year)] = $LANG['MISC']['christmas_eve'];
	$helligdager[gregoriantojd(12,25,$year)] = $LANG['MISC']['christmas_day'];
	$helligdager[gregoriantojd(12,26,$year)] = $LANG['MISC']['boxing_day'];
	$helligdager[gregoriantojd(12,31,$year)] = $LANG['MISC']['new_years_eve'];
	if ($year < 2038 && $year > 1969) {
		$helligdager[unixtojd(easter_date($year))+50] = $LANG['MISC']['whit_monday'];
		$helligdager[unixtojd(easter_date($year))+49] = $LANG['MISC']['pentecost'];
		$helligdager[unixtojd(easter_date($year))+39] = $LANG['MISC']['ascension_day'];
		$helligdager[unixtojd(easter_date($year))+1] = $LANG['MISC']['easter_monday'];
		$helligdager[unixtojd(easter_date($year))] = $LANG['MISC']['easter_day'];
		$helligdager[unixtojd(easter_date($year))-2] = $LANG['MISC']['good_friday'];
		$helligdager[unixtojd(easter_date($year))-3] = $LANG['MISC']['maundy_thursday'];
		$helligdager[unixtojd(easter_date($year))-7] = $LANG['MISC']['palm_sunday'];
	}
}


$days = cal_days_in_month(0, $month, $year);
$weeknum = 1;
$today = unixtojd(time());

$firstday = gregoriantojd($month, 1, $year);
$lastday =  gregoriantojd($month, $days, $year);

$notater = get_kalnotater($firstday, $lastday);

for ($day = 1; $day <= $days; $day ++) {
	
	$daystamp = gregoriantojd($month, $day, $year);
	if ($daystamp == $today) {
		$jdtoday = $daystamp;
		$jdthisweek = $weeknum;
	}
	$week[$weeknum][$day] = jddayofweek($daystamp, 1);
	if (jddayofweek($daystamp, 0) == 0) {
		$weeknum++;
	}
}




$tmpstamp = strtotime('2000-'.$month.'-1');

if ($month == 1) {
	$prevmonth = 12;
	$prevyear = $year-1;
	$nextmonth = $month+1;
	$nextyear = $year;
} elseif ($month == 12) {
	$prevmonth = $month-1;
	$prevyear = $year;
	$nextmonth = 1;
	$nextyear = $year+1;
} else {
	$prevmonth = $month-1;
	$prevyear = $year;
	$nextmonth = $month+1;
	$nextyear = $year;
}

$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = 2037; $i >= 1970; $i--) {
	$aarliste[$i] = $i;
}

echo '
	<h2 align="center">'.$LANG['MISC']['calendar'].'</h2>
	<h3 align="center">'.ucfirst(strftime('%B', $tmpstamp)).' '.$year.'</h3>
	<br>
	<table align="center">
		<tr>
			<td><button type="button" onClick="javascript:window.location=\'./kalender.php?month='.$prevmonth.'&amp;year='.$prevyear.'\';">'.$LANG['MISC']['previous'].'</button></td>
			<td><button type="button" onClick="javascript:window.location=\'./kalender.php\';">'.$LANG['MISC']['now'].'</button></td>
			<td><button type="button" onClick="javascript:window.location=\'./kalender.php?month='.$nextmonth.'&amp;year='.$nextyear.'\';">'.$LANG['MISC']['next'].'</button></td>
		</tr>
	</table>
	<form name="viskalenderform" action="./kalender.php" method="get">
	<table align="center">
		<tr>
			<td class="nospace"><select name="month">'.print_liste($mndliste, $month).'</select></td>
			<td class="nospace"><select name="year">'.print_liste($aarliste, $year).'</select></td>
			<td class="nospace"><button type="submit">'.$LANG['MISC']['go_to'].'</button></td>
		</tr>
	</table>
	</form>
';


if (!$_REQUEST[utskrift]) {
	echo '
		<form method="post" action="kalender.php" name="nyvisningform">
		<input type="hidden" name="nykalendervis" value="yes">
		<input type="hidden" name="whereiwas" value="'.$whereiam.'">
		<table border="0" cellspacing="0" cellpadding="0" align="center" class="tiny">
		<tr>
			<td colspan="7" align="center"><strong>'.$LANG['MISC']['show'].'...</strong></td>
		</tr>
		<tr>
		<td align="left"><input type="checkbox" name="kalendervis[arrangorbursdager]"'; if ($_SESSION['kalendervis']['arrangorbursdager']) { echo ' checked'; } echo '>'.$LANG['MISC']['birthdays'].' ('.$LANG['MISC']['organizers'].')</td>
		<td align="left"><input type="checkbox" name="kalendervis[helligdager]"'; if ($_SESSION['kalendervis']['helligdager']) { echo ' checked'; } echo '>'.$LANG['MISC']['holidays'].'</td>
		</tr>
		<tr>
		<td align="left"><input type="checkbox" name="kalendervis[spillerbursdager]"'; if ($_SESSION['kalendervis']['spillerbursdager']) { echo ' checked'; } echo '>'.$LANG['MISC']['birthdays'].' ('.$LANG['MISC']['players'].')</td>
		<td align="left"><input type="checkbox" name="kalendervis[merkedager]"'; if ($_SESSION['kalendervis']['merkedager']) { echo ' checked'; } echo '>'.$LANG['MISC']['traditional_days'].'</td>
		</tr>
		<tr>
		<td align="left"><input type="checkbox" name="kalendervis[spilldager]"'; if ($_SESSION['kalendervis']['spilldager']) { echo ' checked'; } echo '>'.$LANG['MISC']['gamedays'].'</td>
		<td>&nbsp;</td>
		<tr><td colspan="4" align="center">
			<button type="submit">'.$LANG['MISC']['change_view'].'</button>
		</td>
		</tr>
		</table>
		</form>
	';
}

echo '
	<table border="0" cellspacing="0" cellpadding="0" align="center" class="bordered">
';

foreach ($week as $weeknum=>$weekdays) {
	if ($weeknum == $jdthisweek) {
		$weekstyle = 'id="kaldenneuke"';
	}
	$yearweek = abs(strftime('%W', jdtounix(gregoriantojd($month, key($weekdays), $year))));
	echo '
		<tr>
			<td class="highlight" align="center" '.$weekstyle.'>'.$LANG['MISC']['week'].' '.$yearweek.'</td>
	';
	if ($weeknum == 1) {
		for ($i = 7; $i > count($weekdays); $i--) {
			if ($i == 7) {
				echo '
				<td class="bl">&nbsp;</td>
				';
			} else {
				echo '
				<td>&nbsp;</td>
				';
			}
		}
	}
	foreach ($weekdays as $daynum=>$dayname) {
		$daystamp = gregoriantojd($month, $daynum, $year);
		if ($merkedager[$daystamp]) {
			$daystyle = 'class="kalmerkedag"';
		} elseif ($helligdager[$daystamp]) {
			$daystyle = 'class="helligdag"';
		}
		echo '
			<td class="bl" style="padding: 0; margin: 0;"'; if ($daystamp == $jdtoday && $weeknum == $jdthisweek) { echo ' id="kalidag"'; } echo '>
				<table'; if ($weeknum != 1) { echo ' class="bt"'; } echo ' border="0" width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td class="highlight" nowrap="nowrap">'; if ($dager[$dayname] == $LANG['MISC']['sunday'] || $helligdager[$daystamp]) { echo ' <div class="kalhelligdag">'; } echo $daynum.' '.$dager[$dayname].'</td>
					</tr>
					<tr>
						<td class="kaldagtittel">';
						if ($helligdager[$daystamp] && $merkedager[$daystamp]) {
							echo $helligdager[$daystamp].'<br>'.$merkedager[$daystamp].'<br>';
						} elseif ($helligdager[$daystamp]) {
							echo $helligdager[$daystamp].'<br>';
						} elseif ($merkedager[$daystamp]) {
							echo $merkedager[$daystamp].'<br>';
						}
						if ($month == 2 && $weeknum == 2 && $dayname == 'Sunday') {
							echo '<a onClick="javascript:return overlib(\''.$LANG['CALNOTE']['mothers_day'].'\',CAPTION,\''.$LANG['MISC']['mothers_day'].'\');">'.$LANG['MISC']['mothers_day'].'</a><br>';
						}
						if ($month == 11 && $weeknum == 2 && $dayname == 'Sunday') {
							echo '<a onClick="javascript:return overlib(\''.$LANG['CALNOTE']['fathers_day'].'\',CAPTION,\''.$LANG['MISC']['fathers_day'].'\');">'.$LANG['MISC']['fathers_day'].'</a><br>';
						}
						if ($bursdager[$daystamp]) {
							foreach ($bursdager[$daystamp] as $bursdag) {
								echo $bursdag.'<br>';
							}
						} else {
							echo '&nbsp;';
						}
						echo '
						</td>
					</tr>
					<tr>
						<td style="height: 4em;" '.$infostyle.'>
					';
						if ($datomerknader[$daystamp]) {
							foreach ($datomerknader[$daystamp] as $datomerknad) {
								echo '<p>'.$datomerknad.'</p>';
							}
						}
						if ($notater[$daystamp]) {
							foreach ($notater[$daystamp] as $notat) {
								echo '<p>'.note_icon($notat).'</p>';
							}
						}
					echo '
						</td>
					</tr>
				</table>
			</td>
		';
		unset($daystyle,$infostyle);
	}
	if ($weeknum != 1) {
		for ($i = 7; $i > count($weekdays); $i--) {
			if ($i == 7) {
				echo '
				<td style="padding:0;margin:0;" class="bl"><div class="bt">&nbsp;</div></td>
				';
			} else {
				echo '
				<td class="bt">&nbsp;</td>
				';
			}
		}
	}
	echo '
		</tr>
	';
	unset($weekstyle);
}


echo '
	</table>
';

for ($i = 1; $i <= 31; $i++) {
	$dagliste[$i] = $i;
}

echo '
	<script language="JavaScript" type="text/javascript">
		function validate_notat() {
			if (!validDate(document.editnotat.notat_dag.value, document.editnotat.notat_mnd.value, document.editnotat.notat_aar.value)) {
				window.alert(\''.$LANG['JSBOX']['invalid_date'].'\');
				return false;
			}
			if (document.editnotat.tekst.value == \'\') {
				window.alert(\''.$LANG['JSBOX']['note_text'].'\');
				document.editnotat.tekst.focus();
				return false;
			}
			return true;
		}
	</script>
	<form name="editnotat" class="noprint" action="./kalender.php" method="post">
	<input type="hidden" name="nytt_notat" value="yes">
	<br>
	<table align="center" class="bordered" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="3" class="highlight" align="center"><h3>'.$LANG['MISC']['new_note'].'</h3><br></td>
		</tr>
		<tr>
			<td colspan="3" class="highlight" >'.$LANG['MISC']['date'].': <select name="notat_dag"><option value="" class="selectname">'.$LANG['MISC']['day'].'</option>'.print_liste($dagliste, strftime('%d', time())).'</select> <select name="notat_mnd"><option value="" class="selectname">'.$LANG['MISC']['month'].'</option>'.print_liste($mndliste, $month).'</select> <select name="notat_aar"><option value="" class="selectname">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, $year).'</select></td>
		</tr>
		<tr>
			<td colspan="3" class="highlight" ><textarea id="tekst" name="tekst" cols="50" rows="3"></textarea></td>
		</tr>
		<tr>
			<td align="left" class="highlight" >
			'.inputsize_less('tekst', 1).'
			</td>
			<td align="center" class="highlight" >
				<table cellspacing="0" cellpadding="0" align="center">
					<tr>
				<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
				<td><button type="submit" onClick="javascript:return validate_notat();">'.$LANG['MISC']['save'].'</button></td>
					</tr>
				</table>
			</td>
			<td align="right" class="highlight">
			'.inputsize_more('tekst', 1).'
			</td>
		</tr>
		<tr class="highlight">
			<td colspan="3">&nbsp;</td>
		</tr>
	</table>

	<table align="center" cellspacing="0" cellpadding="0">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
		</tr>
	</table>
	</form>
';
include('footer.php');
?>