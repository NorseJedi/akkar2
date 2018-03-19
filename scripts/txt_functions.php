<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             txt_functions.php                           #
#                            -------------------                          #
#                                                                         #
#   copyright (C) 2004-2006 Roy W. Andersen                               #
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
if (!defined('IN_AKKAR')) {
	exit('Access violation.');
}

$textformat['separator1'] = '                              **********                              ';
$textformat['separator2'] = '----------------------------------------------------------------------';

function txt_get_rolle($rolle_id, $spill_id, $internal_print = 0) {
	global $config, $LANG, $textformat;
	$rolle = get_rolle($rolle_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$arrangor = get_person($rolle['arrangor_id']);
	$filename = 'tmp/'.mkfilename($rolle['navn'].'-'.$spillnavn).'.txt';
	if (is_file($filename)) {
		unlink($filename);
	}
	$spiller = get_person($rolle['spiller_id']);
	$txt = '';
	txt_write_rolle($txt, $rolle, $spiller, $print, $internal_print);
	$fp = fopen($filename, w);
	fputs($fp, trim($txt));
	fclose($fp);
	return $filename;
}

function txt_get_roller($spill_id, $print, $exclude_deactivated, $exclude_arrangorer, $exclude_unassigned, $exclude_unpaid, $mailpref, $internal_print, $full_rolle) {
	global $config, $LANG, $textformat;

	if ($exclude_deactivated) {
		$get_status = 'aktive';
	} else {
		$get_status = 'alle';
	}
	if ($print == 'spillroller') {
		$roller = get_roller($spill_id, $get_status);
		$spillinfo = get_spillinfo($spill_id);
		$filename = 'tmp/'.mkfilename($LANG['MISC']['characters'].'-'.$spillinfo['navn']).'.txt';
		$txt = '';
	} else {
		$roller = get_roller(0, $get_status);
		$filename = 'tmp/'.mkfilename($LANG['MISC']['characters'].'-'.$LANG['MISC']['all']).'.txt';
		$txt = strtoupper($LANG['MISC']['characters'].' - '.$LANG['MISC']['all'])."\r\n".$textformat['separator1']."\r\n";
	}
	if (is_file($filename)) {
		unlink($filename);
	}
	foreach ($roller as $rolle) {

		$spillinfo = get_spillinfo($rolle['spill_id']);
		$spiller = get_person($rolle['spiller_id']);
		if ($spiller['type'] != 'spiller') {
			$paamelding['betalt'] = 1;
		} else {
			$paamelding = get_paamelding($spiller['person_id'], $rolle['spill_id']);
		}

		$print_this = true;

		if (($exclude_arrangorer) && ($spiller['type'] != 'spiller')) {
			$print_this = false;
		}

		if (($exclude_unassigned) && (!$spiller)) {
			$print_this = false;
		}

		if (($mailpref) && ($spiller['mailpref'] != 'post')) {
			$print_this = false;
		}

		if (($exclude_unpaid) && (!$paamelding['betalt'])) {
			$print_this = false;
		}

		if ($print_this) {
			if (txt_write_rolle($txt, $rolle, $spiller, $print, $internal_print)) {
				if ($full_rolle) {
					txt_write_grupper($txt, $rolle);
					txt_write_kjentfolk($txt, $rolle);
					txt_write_kjentgrupper($txt, $rolle);
				}
			}
			$txt .= "\r\n".$textformat['separator1']."\r\n".$textformat['separator1']."\r\n".$textformat['separator1']."\r\n";
		}
		unset($rolle, $print_this, $spillinfo, $spiller, $paamelding);
	}
	$fp = fopen($filename, w);
	fputs($fp, $txt);
	fclose($fp);
	return $filename;
}

function txt_get_kjentfolk($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages, $txt_header_left, $txt_header_right, $txt_header_center, $txt_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);
	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['acquaintances']).'-'.$rolle['navn'].'-'.$spillnavn).'.txt';
	if (is_file($filename)) {
		unlink($filename);
	}
	$txt = '';
	if (txt_write_kjentfolk($txt, $rolle)) {
		$fp = fopen($filename, w);
		fputs($fp, trim($txt));
		fclose($fp);
		return $filename;
	}
	return false;
}

function txt_get_grupper($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages, $txt_header_left, $txt_header_right, $txt_header_center, $txt_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);
	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['group_members']).'-'.$rolle['navn'].'-'.$spillnavn).'.txt';
	if (is_file($filename)) {
		unlink($filename);
	}
	$txt = '';
	if (txt_write_grupper($txt, $rolle)) {
		$fp = fopen($filename, w);
		fputs($fp, trim($txt));
		fclose($fp);
		return $filename;
	}
	return false;
}

function txt_get_kjentgrupper($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages, $txt_header_left, $txt_header_right, $txt_header_center, $txt_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);
	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['acquainted_groups']).'-'.$rolle['navn'].'-'.$spillnavn).'.txt';
	if (is_file($filename)) {
		unlink($filename);
	}
	$txt = '';
	if (txt_write_kjentgrupper($txt, $rolle)) {
		$fp = fopen($filename, w);
		fputs($fp, trim($txt));
		fclose($fp);
		return $filename;
	}
	return false;
}

function txt_write_kjentfolk(&$txt, $rolle) {
	global $config, $LANG, $textformat;
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);

	$kjentfolk = get_rolle_kjentfolk($rolle['rolle_id'], $rolle['spill_id']);

	if ($kjentfolk) {
		$txt .= $rolle['navn'].' ('.$spillnavn.")\r\n";
		$txt .= strtoupper($LANG['MISC']['acquaintances']).' ('.count($kjentfolk).")\r\n\r\n";
		foreach($kjentfolk as $kjentdata) {
			$txt .= "\r\n".$textformat['separator2']."\r\n";
			$kjentspiller = get_person($kjentdata['spiller_id']);
			if (!$kjentspiller) {
				$spillernavn = strip_tags($LANG['MISC']['none']);
			} else {
				if (!$kjentspiller['email']) {
					$kjentspiller['email'] = strip_tags($LANG['MISC']['no_email']);
				}
				$spillernavn = $kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].' <'.$kjentspiller['email'].'>';
			}
			$beskrivelse = $kjentdata['beskrivelse'.$kjentdata['level']];
			$txt .= $kjentdata['navn'].' ('.$LANG['MISC']['player'].': '.$spillernavn.")\r\n";
			$txt .= '- '.$kjentdata['kjentgrunn']."\r\n";
			$txt .= "\r\n".$beskrivelse."\r\n";
		}
		$txt .= "\r\n".$textformat['separator2']."\r\n";
		$txt = trim($txt);
		return true;
	}
	return false;
}

function txt_write_kjentgrupper(&$txt, $rolle) {
	global $config, $LANG, $textformat;
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);

	$grupper = get_kjentgrupper($rolle['rolle_id'], $rolle['spill_id']);
	if ($grupper) {
		$txt .= $rolle['navn'].' ('.$spillnavn.")\r\n";
		$txt .= strtoupper($LANG['MISC']['acquainted_groups']).' ('.count($grupper).")\r\n\r\n";
		foreach($grupper as $gruppe) {
			$txt .= "\r\n".$textformat['separator1']."\r\n";
			$txt .= strtoupper($gruppe['navn'])."\r\n";
			$txt .= '- '.$gruppe['kjentgrunn']."\r\n\r\n";
			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				$txt .= $LANG['MISC']['empty_group']."\r\n";
			} else {
				foreach ($medlemmer as $medlem) {
					if ($medlem['rolle_id'] != $rolle['rolle_id']) {
						$kjentrolle = get_rolle($medlem['rolle_id'], $gruppe['spill_id']);
						$kjentspiller = get_person($kjentrolle['spiller_id']);
						if ($kjentspiller) {
							if (!$kjentspiller['email']) {
								$kjentspiller['email'] = strip_tags($LANG['MISC']['no_email']);
							}
							$kjentspillernavn = $kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].' <'.$kjentspiller['email'].'>';
						} else {
							$kjentspillernavn = strip_tags($LANG['MISC']['none']);
						}
						$txt .= $medlem['navn'].' ('.$LANG['MISC']['player'].': '.$kjentspillernavn.")\r\n";
						$txt .= $medlem['beskrivelse_gruppe']."\r\n\r\n";
						unset($kjentrolle, $kjentspiller, $kjentspillernavn);
					}
				}
			}
		}
		$txt .= "\r\n".$textformat['separator1']."\r\n";
		$txt = trim($txt);
		return true;
	}
	return false;
}

function txt_write_grupper(&$txt, $rolle) {
	global $config, $LANG, $textformat;
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);

	$grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id']);

	if ($grupper) {
		$txt .= $rolle['navn'].' ('.$spillnavn.")\r\n";
		$txt .= strtoupper($LANG['MISC']['groups']).' ('.count($grupper).")\r\n\r\n";
		foreach($grupper as $gruppe) {
			$txt .= "\r\n".$textformat['separator1']."\r\n";
			$txt .= '*** '.strtoupper($gruppe['navn'])." ***\r\n\r\n";
			$txt .= $LANG['MESSAGE']['groupmember_info'].":\r\n".$gruppe['medlemsinfo']."\r\n\r\n";
			$txt .= strtoupper($LANG['MISC']['group_members'])."\r\n";

			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				$txt .= $LANG['MISC']['empty_group']."\r\n";
			} else {
				foreach ($medlemmer as $medlem) {
					if ($medlem['rolle_id'] != $rolle['rolle_id']) {
						$kjentrolle = get_rolle($medlem['rolle_id'], $gruppe['spill_id']);
						$kjentspiller = get_person($kjentrolle['spiller_id']);
						if ($kjentspiller) {
							if (!$kjentspiller['email']) {
								$kjentspiller['email'] = strip_tags($LANG['MISC']['no_email']);
							}
							$kjentspillernavn = $kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].' <'.$kjentspiller['email'].'>';
						} else {
							$kjentspillernavn = strip_tags($LANG['MISC']['none']);
						}
						$txt .= $medlem['navn'].' ('.$LANG['MISC']['player'].': '.$kjentspillernavn.")\r\n";
						$txt .= $medlem['beskrivelse_gruppe']."\r\n\r\n";
						unset($kjentrolle, $kjentspiller, $kjentspillernavn);
					}
				}
			}
		}
		$txt .= "\r\n".$textformat['separator1']."\r\n";
		$txt = trim($txt);
		return true;
	}
	return false;
}

function txt_write_rolle(&$txt, $rolle, $spiller, $print, $internal_print = 0) {
	global $config, $LANG, $textformat;
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$mal_id = $spillinfo['rollemal'];
	$malinfo = get_maldata($mal_id);
	$mal = get_rollemal($rolle['spill_id']);
	$arrangor = get_person($rolle['arrangor_id']);
	$txt .= strtoupper($LANG['MISC']['name']).': '.$rolle['navn']."\r\n";
	if ($print != 'spillroller') {
		$txt .= strtoupper($LANG['MISC']['game']).': '.$spillnavn."\r\n";
	}
	$txt .= strtoupper(strip_tags($LANG['MISC']['player'])).': '.$spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].">\r\n";
	$txt .= strtoupper(strip_tags($LANG['MISC']['organizer'])).': '.$arrangor['fornavn'].' '.$arrangor['etternavn'].' <'.$arrangor['email'].">\r\n";

	foreach ($rolle as $fieldname => $value) {
		$value = stripslashes($value);
		if (is_int(strpos($fieldname, 'field'))) {
			$fieldinfo = get_malentry($fieldname, $mal_id);
			if (!$fieldinfo['intern']) {
				$extras = explode(';',$fieldinfo['extra']);
				$fieldinfo['fieldtitle'] = strtoupper($fieldinfo['fieldtitle']);
				switch ($fieldinfo['type']) {
					case 'inline';
						$txt .= $fieldinfo['fieldtitle'].': '.stripslashes($value)."\r\n";
						break;
					case 'inlinebox';
						$txt .= "\r\n".$fieldinfo['fieldtitle'].":\r\n".stripslashes($value)."\r\n";
						break;
					case 'box';
						$txt .= "\r\n\r\n*** ".$fieldinfo['fieldtitle']." ***\r\n".stripslashes($value)."\r\n";
						break;
					case 'listsingle';
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							if (strtolower($value) == strtolower($extras[$i])) {
								$txt .= $fieldinfo['fieldtitle'].': '.ucwords(stripslashes($extras[$i]))."\r\n";
							}
						}
						break;
					case 'listmulti';
						$values = unserialize($value);
						unset($value);
						if (!is_array($values)) {
							$value = strip_tags($LANG['MISC']['none']);
						} else {
							foreach ($values as $thisval) {
								$value .= stripslashes($thisval).", ";
							}
							$value = substr($value, 0, -2);
						}
						$txt .= $fieldinfo['fieldtitle'].': '.$value."\r\n";
						break;
					case 'radio';
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							if (strtolower($value) == strtolower($extras[$i])) {
								$txt .= $fieldinfo['fieldtitle'].': '.ucwords(stripslashes($extras[$i]))."\r\n";
							}
						}
						break;
					case 'check';
						if ($value != 0) {
							$txt .= $fieldinfo['fieldtitle'].': '.$extras[0]."\r\n";
						} else {
							$txt .= $fieldinfo['fieldtitle'].': '.$extras[1]."\r\n";
						}
						break;
					case 'calc';
						$calc = get_calc_formula($rolle[$malinfo[$extras[0]]['fieldname']], $extras[1]);
						@eval('$calcresult = '.$calc.';');
						$txt .= $fieldinfo['fieldtitle'].': '.$calcresult."\r\n";
						break;
					case 'dots';
						$txt .= $fieldinfo['fieldtitle'].': ';
						for ($i = 1; $i <= $value; $i++) {
							$txt .= 'Ø ';
						}
						for ($i = $value; $i < $extras[0]; $i ++) {
							$txt .= 'O ';
						}
						$txt .= ' ('.$value.'/'.$extras[0].")\r\n";
						break;
					case 'header';
						$txt .= "\r\n\r\n*** ".$fieldinfo['fieldtitle']." ***\r\n";
						break;
					case 'separator';
						$txt .= "\r\n".$textformat['separator2']."\r\n";
						break;
				}
			}
		} else {
			switch($fieldname) {
				case 'oppdatert';
				case 'navn';
				case 'rolle_id';
				case 'locked';
				case 'bilde';
				case 'spill_id';
				case 'spiller_id';
				case 'arrangor_id';
				case 'intern_info';
				case 'beskrivelse1';
				case 'beskrivelse2';
				case 'beskrivelse3';
				case 'beskrivelse_gruppe';
				case 'status';
				case 'status_id';
				case 'status_tekst';
				break;
				default:
				$txt .= strip_tags($LANG['DBFIELD'][$fieldname]).': '.stripslashes($value)."\r\n";
			}
		}
	}
	$txt .= "\r\n";
	if ($grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id'])) {
		foreach ($grupper as $gruppe) {
			if ($gruppe['medlemsinfo']) {
				$txt .= "\r\n\r\n*** ".str_replace("<groupname>", '"'.$gruppe['navn'].'"', $LANG['MISC']['info_from_group_membership'])." ***\r\n".stripslashes($gruppe['medlemsinfo'])."\r\n";
				$txt .= "\r\n";
			}
		}
	}
	return true;
}

?>
