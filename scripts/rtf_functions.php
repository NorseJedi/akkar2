<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             rtf_functions.php                           #
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
if (!defined('IN_AKKAR')) {
	exit('Access violation.');
}

function rtf_get_bildedata($image, $x, $y) {
	resizeimg($image, 'tmp/'.basename($image), 'image/jpeg', $x,$y, 0);
	$imgfile = fopen('tmp/'.basename($image), r);
	$bilde = fread($imgfile, filesize("tmp/".basename($image)));
	fclose ($imgfile);
	unlink('tmp/'.basename($image));
	return chunk_split(bin2hex($bilde).'}');
}

function rtf_get_rolle($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages;
	$rolle = get_rolle($rolle_id, $spill_id);
	$spiller = get_person($rolle['spiller_id']);
	$arrangor = get_arrangor($rolle['arrangor_id']);

	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['rollemal'];
	$malinfo = get_maldata($mal_id);

	$spiller['bilde'] = mugshot($spiller, 1);
	switch (get_mime_type($spiller['bilde'])) {
		case 'image/png';
		$blip = '\pngblip';
		break;
		case 'image/jpeg';
		case 'image/jpeg';
		case 'image/jpeg';
		$blip = '\jpegblip';
		break;
	}

	$bilde = rtf_get_bildedata($spiller['bilde'], 120, 150);

	$rtf_title = $rolle['navn'];
	$rtf_subject = 'Rolle til '.$spillinfo['navn'];
	$rtf_author = $arrangor['fornavn'].' '.$arrangor['etternavn'];
	$rtf_comment = 'Rolle til '.$spillinfo['navn'].' for '.$spiller['fornavn'].' '.$spiller['etternavn'];
	$rtf_operator = $_SESSION['navn'];

	$filename = 'tmp/'.mkfilename($rolle['navn']).'.rtf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$fp = fopen($filename, w);

	$rtf .= '{\rtf1\ansi\ansicpg1252{\fonttbl{\f0\fswiss\fcharset0\fprq2{\*\panose 02020603050405020304}Arial;}{\f1\froman\fcharset0\fprq2{\*\panose 02020603050405020304}Times New Roman;}}{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue128;\red0\green128\blue128;\red0\green128\blue0;\red128\green0\blue128;\red128\green0\blue0;\red128\green128\blue0;\red128\green128\blue128;\red192\green192\blue192;}';
	$rtf .= '{\*\generator AKKAR-'.$config['version'].';}{\info{\title '.$rtf_title.'}{\subject '.$rtf_subject.'}{\author '.$rtf_author.'}{\company '.$config['arrgruppenavn'].'}{\operator '.$rtf_operator.'}{\creatim\yr'.date("Y", time()).'\mo'.date("n", time()).'\dy'.date("j", time()).'\hr'.date("G", time()).'\min'.date("i", time()).'}{\doccomm '.$rtf_comment.'}}{';
	$rtf .= '{\header\pard\brdrb\brdrs\brdrw10\brsp100\plain \s15\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs24\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_title.'\tab '.$spillinfo['navn'].'\tab '.$spiller['fornavn'].' '.$spiller['etternavn'].'\par }}{\footer\pard\brdrt\brdrs\brdrw10\brsp100\plain \s15\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs24\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_author.'\tab '.$LANG['MISC']['page'].' }{\field{\*\fldinst {\insrsid1910154 PAGE }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154  '.$LANG['MISC']['of'].' }{\field{\*\fldinst {\insrsid1910154  NUMPAGES }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154 \tab '.$config['arrgruppenavn'].'}}';
	$rtf .= '\par \phmrg\posxr\posyil\posy0\dxfrtext0 {\pict'.$blip.'\picscalex100\picscaley100\bliptag132000428 '.$bilde;
	$rtf .= '\par \pard ';
	$rtf .= '\b1 '.strip_tags($LANG['MISC']['player']).': \b0 '.$spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'>\par';
	$rtf .= '\b1 '.strip_tags($LANG['MISC']['organizer']).': \b0 '.$arrangor['fornavn'].' '.$arrangor['etternavn'].' <'.$arrangor['email'].'>\par';

	foreach ($rolle as $fieldname => $value) {
		$value = stripslashes($value);
		if (is_int(strpos($fieldname, 'field'))) {
			$fieldinfo = get_malentry($fieldname, $mal_id);
			if (!$fieldinfo['intern']) {
				$extras = explode(';',$fieldinfo['extra']);
				switch ($fieldinfo['type']) {
					case 'inline';
					$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.stripslashes($value).'\par';
					break;
					case 'inlinebox';
					$rtf .= '\par \b1 '.$fieldinfo['fieldtitle'].': \b0 \par '.stripslashes($value).'\par';
					break;
					case 'box';
					$rtf .= '\par \b1 '.$fieldinfo['fieldtitle'].': \b0 \par '.str_replace("<br />", "\par ", nl2br(stripslashes($value))).'\par';
					break;
					case 'listsingle';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						if (strtolower($value) == strtolower($extras[$i])) {
							$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.ucwords(stripslashes($extras[$i])).'\par';
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
					$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.$value.'\par';
					break;
					case 'radio';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						if (strtolower($value) == strtolower($extras[$i])) {
							$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.ucwords(stripslashes($extras[$i])).'\par';
						}
					}
					break;
					case 'check';
					if ($value != 0) {
						$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.$extras[0].'\par';
					} else {
						$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.$extras[1].'\par';
					}
					break;
					case 'calc';
					$calc = get_calc_formula($rolle[$malinfo[$extras[0]]['fieldname']], $extras[1]);
					@eval("\$calcresult = ".$calc.";");
					$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 '.$calcresult.'\par';
					break;
					case 'dots';
					$rtf .= '\b1 '.$fieldinfo['fieldtitle'].': \b0 \fs52\dn10';
					for ($i = 1; $i <= $value; $i++) {
						$rtf .= '\bullet ';
					}
					$rtf .= '\fs24\dn0\par';
					break;
					case 'header';
					$rtf .= '\par\fs28 \b1 '.$fieldinfo['fieldtitle'].'\b0\fs24\par';
					break;
					case 'separator';
					$rtf .= '\par\brdrt\brdrs\brdrw10\brsp100\par\pard';
					break;
				}
			}
		} else {
			switch($fieldname) {
				case 'oppdatert';
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
				default;
				$rtf .= '\b1 '.strip_tags($LANG['DBFIELD'][$fieldname]).': \b0 '.str_replace('<br />', "\r\n", nl2br(stripslashes($value))).'\par';
			}
		}
	}
	$rtf .= '\par ';
	if ($grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id'])) {
		foreach ($grupper as $gruppe) {
			if ($gruppe['medlemsinfo']) {
				$rtf .= '\b1 '.str_replace("<groupname>", $gruppe['navn'], strip_tags($LANG['MISC']['info_from_group_membership'])).': \b0 \par '.stripslashes($gruppe['medlemsinfo']).'\par';
				$rtf .= '\par ';
			}
		}
	}
	$rtf .= '}}';
	fwrite($fp, $rtf);
	fclose($fp);
	return $filename;
}



function rtf_get_kjentfolk($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spiller = get_person($rolle['spiller_id']);

	$rtf_title = strip_tags($LANG['MISC']['acquaintances']).' - '.$rolle['navn'];
	$rtf_subject = strip_tags($LANG['MISC']['acquaintances']).' - '.$spillinfo['navn'];
	$rtf_author = $arrangor['fornavn']." ".$arrangor['etternavn'];
	$rtf_comment = strip_tags($LANG['MISC']['acquaintances']).' - '.$rolle['navn'].' ('.$spillinfo['navn'].')';
	$rtf_operator = $_SESSION['navn'];

	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['acquaintances']).'-'.$rolle['navn']).'.rtf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$fp = fopen($filename, w);

	$rtf .= '{\rtf1\ansi\ansicpg1252\uc1\deff0\deflang1044\deflangfe1044{\fonttbl{\f0\fswiss\fcharset0\fprq2{\*\panose 02020603050405020304}Arial;}{\f1\froman\fcharset0\fprq2{\*\panose 02020603050405020304}Times New Roman;}}{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue128;\red0\green128\blue128;\red0\green128\blue0;\red128\green0\blue128;\red128\green0\blue0;\red128\green128\blue0;\red128\green128\blue128;\red192\green192\blue192;}';
	$rtf .= '{\*\generator AKKAR-'.$config['version'].';}{\info{\title '.$rtf_title.'}{\subject '.$rtf_subject.'}{\author '.$rtf_author.'}{\company '.$config['arrgruppenavn'].'}{\operator '.$rtf_operator.'}{\creatim\yr'.date('Y', time()).'\mo'.date('n', time()).'\dy'.date("j", time()).'\hr'.date('G', time()).'\min'.date('i', time()).'}{\doccomm '.$rtf_comment.'}}{';
	$rtf .= '{\header \pard\plain \s15\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs20\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_title.'\tab '.$spillinfo['navn'].'\tab '.$spiller['fornavn'].' '.$spiller['etternavn'].'\par }}{\footer \pard\plain \s16\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs20\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_author.'\tab Side }{\field{\*\fldinst {\insrsid1910154 PAGE }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154  av }{\field{\*\fldinst {\insrsid1910154  NUMPAGES }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154 \tab '.$config['arrgruppenavn'].'\par }}';
	$rtf .= '\par \fs36 \qc \b1 '.$rtf_title.' \par \fs24';

	$kjentfolk = get_rolle_kjentfolk($rolle['rolle_id'], $rolle['spill_id']);
	if (!$kjentfolk) {
		$rtf .= '\par \par \pard \fs16 \b1 '.strip_tags($LANG['MISC']['no_acquaintances']).' \par \fs12 ';
	} else {
		foreach($kjentfolk as $kjentdata) {
			$kjentspiller = get_person($kjentdata['spiller_id']);
			if (!$kjentspiller) {
				$spillernavn = strip_tags($LANG['MISC']['no_player']);
			} else {
				if (!$kjentspiller['email']) {
					$kjentspiller['email'] = strip_tags($LANG['MISC']['no_email']);
				}
				$spillernavn = $kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].' <'.$kjentspiller['email'].'>';
			}
			$kjentspiller['bilde'] = mugshot($kjentspiller, 1);

			$beskrivelse = $kjentdata[beskrivelse . $kjentdata['level']];
			$bilde = rtf_get_bildedata($kjentspiller['bilde'], 90, 112);

			$rtf .= '\par \phmrg\posxl\posyil\dxfrtext100 {\pict\jpegblip\picscalex100\picscaley100\bliptag132000428 '.$bilde;
			$rtf .= '\par \pard ';
			$rtf .= '\b1 '.$kjentdata['navn'].' \b0 ('.$spillernavn.') \par ';
			$rtf .= '\i1 '.$kjentdata['kjentgrunn'].' \i0 \par ';
			$rtf .= '\par '.$beskrivelse.' \par \par \par \par \par ';
		}
	}
	$rtf .= '}}';
	fwrite($fp, $rtf);
	fclose($fp);
	return $filename;
}

function rtf_get_grupper($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spiller = get_person($rolle['spiller_id']);

	$rtf_title = strip_tags($LANG['MISC']['group_members']).' - '.$rolle['navn'];
	$rtf_subject = strip_tags($LANG['MISC']['group_members']).' - '.$rolle['navn'];
	$rtf_author = $arrangor['fornavn']." ".$arrangor['etternavn'];
	$rtf_comment = strip_tags($LANG['MISC']['group_members']).' - '.$rolle['navn'].' ('.$spillinfo['navn'].')';
	$rtf_operator = $_SESSION['navn'];

	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['group_members']).'-'.$rolle['navn']).'.rtf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$fp = fopen($filename, w);

	$rtf .= '{\rtf1\ansi\ansicpg1252\uc1\deff0\deflang1044\deflangfe1044{\fonttbl{\f0\fswiss\fcharset0\fprq2{\*\panose 02020603050405020304}Arial;}{\f1\froman\fcharset0\fprq2{\*\panose 02020603050405020304}Times New Roman;}}{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue128;\red0\green128\blue128;\red0\green128\blue0;\red128\green0\blue128;\red128\green0\blue0;\red128\green128\blue0;\red128\green128\blue128;\red192\green192\blue192;}';
	$rtf .= '{\*\generator AKKAR-'.$config['version'].';}{\info{\title '.$rtf_title.'}{\subject '.$rtf_subject.'}{\author '.$rtf_author.'}{\company '.$config['arrgruppenavn'].'}{\operator '.$rtf_operator.'}{\creatim\yr'.date("Y", time()).'\mo'.date("n", time()).'\dy'.date("j", time()).'\hr'.date("G", time()).'\min'.date("i", time()).'}{\doccomm '.$rtf_comment.'}}{';
	$rtf .= '{\header \pard\plain \s15\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs20\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_title.'\tab '.$spillinfo['navn'].'\tab '.$spiller['fornavn'].' '.$spiller['etternavn'].'\par }}{\footer \pard\plain \s16\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs20\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_author.'\tab Side }{\field{\*\fldinst {\insrsid1910154 PAGE }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154  av }{\field{\*\fldinst {\insrsid1910154  NUMPAGES }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154 \tab '.$config['arrgruppenavn'].'\par }}';
	$rtf .= '\par \fs36 \qc \b1 '.$rtf_title.' \par \fs24';

	$grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id']);


	if (!$grupper) {
		$rtf .= '\par \par \pard \fs30 \b1 '.strip_tags($LANG['MISC']['no_groups']).' \par \fs24 ';
	} else {
		foreach($grupper as $gruppe) {
			$rtf .= '\par \par \pard \fs28 \qc \b1 '.strip_tags($LANG['MISC']['members_of']).' '.$gruppe['navn'].' \par \fs24 ';

			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				$rtf .= '\par \par \pard \fs16 \b1 '.strip_tags($LANG['MISC']['empty_group']).' \par \fs24 ';
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
							$kjentspillernavn = strip_tags($LANG['MISC']['no_player']);
						}
						$kjentspiller['bilde'] = mugshot($kjentspiller, 1);
						$bilde = rtf_get_bildedata($kjentspiller['bilde'], 90, 112);

						$rtf .= '\par \phmrg\posxl\posyil\dxfrtext100 {\pict\jpegblip\picscalex75\picscaley75\bliptag132000428 '.$bilde;
						$rtf .= '\par \pard ';
						$rtf .= '\b1 '.$medlem['navn'].' \b0 ('.$kjentspillernavn.') \par ';
						$rtf .= $medlem['beskrivelse_gruppe'].' \par \par \par \par \par ';
						unset($kjentrolle, $kjentspiller, $kjentspillernavn);
					}
				}
			}
		}
	}
	$rtf .= '}}';
	fwrite($fp, $rtf);
	fclose($fp);
	return $filename;
}

function rtf_get_kjentgrupper($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spiller = get_person($rolle['spiller_id']);

	$rtf_title = strip_tags($LANG['MISC']['acquainted_groups']).' - '.$rolle['navn'];
	$rtf_subject = strip_tags($LANG['MISC']['acquainted_groups']).' - '.$rolle['navn'];
	$rtf_author = $arrangor['fornavn']." ".$arrangor['etternavn'];
	$rtf_comment = strip_tags($LANG['MISC']['acquainted_groups']).' - '.$rolle['navn'].' ('.$spillinfo['navn'].')';
	$rtf_operator = $_SESSION['navn'];

	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['acquainted_groups']).'-'.$rolle['navn']).'.rtf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$fp = fopen($filename, w);

	$rtf .= '{\rtf1\ansi\ansicpg1252\uc1\deff0\deflang1044\deflangfe1044{\fonttbl{\f0\fswiss\fcharset0\fprq2{\*\panose 02020603050405020304}Arial;}{\f1\froman\fcharset0\fprq2{\*\panose 02020603050405020304}Times New Roman;}}{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue128;\red0\green128\blue128;\red0\green128\blue0;\red128\green0\blue128;\red128\green0\blue0;\red128\green128\blue0;\red128\green128\blue128;\red192\green192\blue192;}';
	$rtf .= '{\*\generator AKKAR-'.$config['version'].';}{\info{\title '.$rtf_title.'}{\subject '.$rtf_subject.'}{\author '.$rtf_author.'}{\company '.$config['arrgruppenavn'].'}{\operator '.$rtf_operator.'}{\creatim\yr'.date("Y", time()).'\mo'.date("n", time()).'\dy'.date("j", time()).'\hr'.date("G", time()).'\min'.date("i", time()).'}{\doccomm '.$rtf_comment.'}}{';
	$rtf .= '{\header \pard\plain \s15\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs20\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_title.'\tab '.$spillinfo['navn'].'\tab '.$spiller['fornavn'].' '.$spiller['etternavn'].'\par }}{\footer \pard\plain \s16\ql \li0\ri0\widctlpar\tqc\tx4536\tqr\tx9072\aspalpha\aspnum\faauto\adjustright\rin0\lin0\itap0 \fs20\lang1044\langfe1044\cgrid\langnp1044\langfenp1044 {\insrsid1910154 '.$rtf_author.'\tab Side }{\field{\*\fldinst {\insrsid1910154 PAGE }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154  av }{\field{\*\fldinst {\insrsid1910154  NUMPAGES }}{\fldrslt {\lang1024\langfe1024\noproof\insrsid1910154 1}}}{\insrsid1910154 \tab '.$config['arrgruppenavn'].'\par }}';
	$rtf .= '\par \fs36 \qc \b1 '.$rtf_title.' \par \fs24';

	$grupper = get_kjentgrupper($rolle['rolle_id'], $rolle['spill_id']);


	if (!$grupper) {
		$rtf .= '\par \par \pard \fs30 \b1 '.$LANG['none'].' \par \fs24 ';
	} else {
		foreach($grupper as $gruppe) {
			$rtf .= '\par \par \pard \fs28 \qc \b1 '.strip_tags($LANG['MISC']['members_of']).' '.$gruppe['navn'].' \par \fs24 ';
			$rtf .= '\pard \fs24 \qc \b1 ('.strip_tags($gruppe['kjentgrunn']).') \par \fs24 ';
			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				$rtf .= '\par \par \pard \fs16 \b1 '.strip_tags($LANG['MISC']['empty_group']).' \par \fs24 ';
			} else {
				foreach ($medlemmer as $medlem) {
					if ($medlem['rolle_id'] != $rolle['rolle_id']) {
						$kjentrolle = get_rolle($medlem['rolle_id'], $gruppe['spill_id']);
						$kjentspiller = get_person($kjentrolle['spiller_id']);
						if ($kjentspiller) {
							if (!$kjentspiller['email']) {
								$kjentspiller['email'] = $LANG['none'];
							}
							$kjentspillernavn = $kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].' <'.$kjentspiller['email'].'>';
						} else {
							$kjentspillernavn = $LANG['none'];
						}
						$kjentspiller['bilde'] = mugshot($kjentspiller, 1);
						$bilde = rtf_get_bildedata($kjentspiller['bilde'], 90, 112);
						$rtf .= '\par \phmrg\posxl\posyil\dxfrtext100 {\pict\jpegblip\picscalex75\picscaley75\bliptag132000428 '.$bilde;
						$rtf .= '\par \pard ';
						$rtf .= '\b1 '.$medlem['navn'].' \b0 ('.$kjentspillernavn.') \par ';
						$rtf .= $medlem['beskrivelse_gruppe'].' \par \par \par \par \par ';
						unset($kjentrolle, $kjentspiller, $kjentspillernavn);
					}
				}
			}
		}
	}
	$rtf .= '}}';
	fwrite($fp, $rtf);
	fclose($fp);
	return $filename;
}

?>
