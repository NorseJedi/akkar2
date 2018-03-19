<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             pdf_functions.php                           #
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
define('FPDF_FONTPATH', 'scripts/font/');

require_once('fpdf.lib.php');
require_once('akkar_fpdf.lib.php');

function pdf_get_rolle($rolle_id, $spill_id, $internal_print = 0) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$arrangor = get_person($rolle['arrangor_id']);
	$filename = 'tmp/'.mkfilename($rolle['navn'].'-'.$spillnavn).'.pdf';
	if (is_file($filename)) {
		unlink($filename);
	}

	$pdf = new PDF('P', 'mm', $config['paperformat']);
	$pdf->internal_print = $internal_print;

	$pdf->SetAutoPageBreak(true, 15);
	$pdf->SetCompression(true);
	$pdf->SetCreator($_SESSION['navn']);
	$pdf->SetAuthor($arrangor['fornavn'].' '.$arrangor['etternavn']);
	$pdf->SetSubject($LANG['MISC']['character'].' - '.$rolle['navn'].' ('.$spillnavn.')');
	$pdf->SetTitle($LANG['MISC']['character'].' - '.$rolle['navn'].' ('.$spillnavn.')');
	$pdf->SetDisplayMode('real');
	$pdf->AliasNbPages();
	$spiller = get_person($rolle['spiller_id']);
	pdf_write_rolle($pdf, $rolle, $spiller, $internal_print);
	$pdf->Output($filename, 'F');
	return $filename;
}

function pdf_get_roller($spill_id, $print, $exclude_deactivated, $exclude_arrangorer, $exclude_unassigned, $exclude_unpaid, $mailpref, $internal_print, $full_rolle) {
	global $config, $LANG;

	$pdf = new PDF('P', 'mm', $config['paperformat']);
	$pdf->internal_print = $internal_print;

	$pdf->SetAutoPageBreak(true, 15);
	$pdf->SetCompression(true);
	$pdf->SetCreator($_SESSION['navn']);
	$pdf->SetAuthor($_SESSION['navn']);
	if ($exclude_deactivated) {
		$get_status = 'aktive';
	} else {
		$get_status = 'alle';
	}
	if ($print == 'spillroller') {
		$roller = get_roller($spill_id, $get_status);
		$spillinfo = get_spillinfo($spill_id);
		$filename = 'tmp/'.mkfilename($LANG['MISC']['characters'].'-'.$spillinfo['navn']).'.pdf';
		$pdf->SetSubject($LANG['MISC']['characters'].' - '.$spillinfo['navn']);
		$pdf->SetTitle($LANG['MISC']['characters'].' - '.$spillinfo['navn']);
	} else {
		$roller = get_roller(0, $get_status);
		$filename = 'tmp/'.mkfilename($LANG['MISC']['characters'].'-'.$LANG['MISC']['all']).'.pdf';
		$pdf->SetSubject($LANG['MISC']['characters'].' - '.$LANG['MISC']['all']);
		$pdf->SetTitle($LANG['MISC']['characters'].' - '.$LANG['MISC']['all']);
	}
	if (is_file($filename)) {
		unlink($filename);
	}

	$pdf->SetDisplayMode('real');
	$pdf->AliasNbPages();
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
			if (pdf_write_rolle($pdf, $rolle, $spiller, $internal_print)) {
				if ($full_rolle) {
					pdf_write_grupper($pdf, $rolle);
					pdf_write_kjentfolk($pdf, $rolle);
					pdf_write_kjentgrupper($pdf, $rolle);
				}
			}
		}
		unset($rolle, $print_this, $spillinfo, $spiller, $paamelding);
	}
	$pdf->Output($filename, 'F');
	return $filename;
}

function pdf_get_kjentfolk($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);
	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['acquaintances']).'-'.$rolle['navn'].'-'.$spillnavn).'.pdf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$pdf = new PDF('P', 'mm', $config['paperformat']);
	$pdf->SetAutoPageBreak(true, 15);
	$pdf->SetCompression(true);
	$pdf->SetCreator($_SESSION['navn']);
	$pdf->SetAuthor($arrangor['fornavn'].' '.$arrangor['etternavn']);
	$pdf->SetSubject(strip_tags($LANG['MISC']['acquaintances']).' - '.$rolle['navn'].' ('.$spillinfo['navn'].')');
	$pdf->SetTitle(strip_tags($LANG['MISC']['acquaintances']).' - '.$rolle['navn']);
	$pdf->SetDisplayMode('real');
	$pdf->AliasNbPages();
	if (pdf_write_kjentfolk($pdf, $rolle)) {
		$pdf->Output($filename, 'F');
		return $filename;
	}
	return false;
}

function pdf_get_grupper($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);
	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['group_members']).'-'.$rolle['navn'].'-'.$spillnavn).'.pdf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$pdf = new PDF('P', 'mm', $config['paperformat']);
	$pdf->SetAutoPageBreak(true, 15);
	$pdf->SetCompression(true);
	$pdf->SetCreator($_SESSION['navn']);
	$pdf->SetAuthor($arrangor['fornavn'].' '.$arrangor['etternavn']);
	$pdf->SetTitle(strip_tags($LANG['MISC']['group_members']).' - '.$rolle['navn']);
	$pdf->SetSubject(strip_tags($LANG['MISC']['group_members']).' - '.$rolle['navn']);
	$pdf->SetDisplayMode('real');
	$pdf->AliasNbPages();
	if (pdf_write_grupper($pdf, $rolle)) {
		$pdf->Output($filename, 'F');
		return $filename;
	}
	return false;
}

function pdf_get_kjentgrupper($rolle_id, $spill_id) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin;
	$rolle = get_rolle($rolle_id, $spill_id);
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($spill_id);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);
	$filename = 'tmp/'.mkfilename(strip_tags($LANG['MISC']['acquainted_groups']).'-'.$rolle['navn'].'-'.$spillnavn).'.pdf';
	if (is_file($filename)) {
		unlink($filename);
	}
	$pdf = new PDF('P', 'mm', $config['paperformat']);
	$pdf->SetAutoPageBreak(true, 15);
	$pdf->SetCompression(true);
	$pdf->SetCreator($_SESSION['navn']);
	$pdf->SetAuthor($arrangor['fornavn'].' '.$arrangor['etternavn']);
	$pdf->SetTitle(strip_tags($LANG['MISC']['acquainted_groups']).' - '.$rolle['navn']);
	$pdf->SetSubject(strip_tags($LANG['MISC']['acquainted_groups']).' - '.$rolle['navn']);
	$pdf->SetDisplayMode('real');
	$pdf->AliasNbPages();
	if (pdf_write_kjentgrupper($pdf, $rolle)) {
		$pdf->Output($filename, 'F');
		return $filename;
	}
	return false;
}

function pdf_get_labels($spill_id, $print, $labeltype, $exclude_unpaid, $mailpref) {
	global $LANG, $config;
	switch ($print) {
		case 'kontakter':
		$filename = 'tmp/'.mkfilename($LANG['MISC']['address_labels'].'-'.$LANG['MISC']['contacts']).'.pdf';
		$kontakter = get_kontakter();
		foreach ($kontakter as $id=>$kontakt) {
			$personer[$id] = $kontakt;
			$personer[$id]['person_id'] = $kontakt['kontakt_id'];
			if ($kontakt['kontaktperson']) {
				$personer[$id]['fornavn'] = $kontakt['kontaktperson'].', '.$kontakt['navn'];
			} else {
				$personer[$id]['fornavn'] = $kontakt['navn'];
			}
			$personer[$id]['mailpref'] = 'post';
			$personer[$id]['betalt'] = 1;
		}
		break;
		case 'paameldte':
		$spillinfo = get_spillinfo($spill_id);
		$filename = 'tmp/'.mkfilename($LANG['MISC']['address_labels'].'-'.$spillinfo['navn'].' '.$LANG['MISC']['players']).'.pdf';
		$personer = get_paameldte($spill_id);
		break;
		case 'spillere':
		$filename = 'tmp/'.mkfilename($LANG['MISC']['address_labels'].'-'.$LANG['MISC']['players']).'.pdf';
		$personer = get_spillere();
		foreach ($personer as $id=>$person) {
			$personer[$id]['betalt'] = 1;
		}
		break;
		case 'arrangorer':
		$filename = 'tmp/'.mkfilename($LANG['MISC']['address_labels'].'-'.$LANG['MISC']['organizers']).'.pdf';
		$personer = get_arrangorer();
		foreach ($personer as $id=>$person) {
			$personer[$id]['betalt'] = 1;
		}
		break;
	}
	$pdf = new PDF_Label($labeltype, 'mm');
	$pdf->Open();
	$pdf->SetDisplayMode('real');
	#	$pdf->AddPage();
	foreach ($personer as $person) {
		$print_this = true;

		if (($mailpref) && ($person['mailpref'] != 'post')) {
			$print_this = false;
		}

		if (($exclude_unpaid) && (!$person['betalt'])) {
			$print_this = false;
		}

		if ($print_this) {
			$pdf->Add_PDF_Label($person['fornavn'].' '.$person['etternavn']."\r\n".$person['adresse']."\r\n".$person['postnr'].' '.$person['poststed']);
		}
	}
	$pdf->Output($filename, 'F');
	return $filename;
}

function pdf_get_envelopes($spill_id, $print, $envelopetype, $exclude_unpaid, $mailpref) {
	global $LANG, $config;
	switch ($print) {
		case 'kontakter':
		$filename = 'tmp/'.mkfilename($LANG['MISC']['envelopes'].'-'.$LANG['MISC']['contacts']).'.pdf';
		$personer = get_kontakter();
		foreach ($personer as $id=>$person) {
			$personer[$id]['mailpref'] = 'post';
			$personer[$id]['betalt'] = 1;
		}
		break;
		case 'paameldte':
		$spillinfo = get_spillinfo($spill_id);
		$filename = 'tmp/'.mkfilename($LANG['MISC']['envelopes'].'-'.$spillinfo['navn'].' '.$LANG['MISC']['players']).'.pdf';
		$personer = get_paameldte($spill_id);
		break;
		case 'spillere':
		$filename = 'tmp/'.mkfilename($LANG['MISC']['envelopes'].'-'.$LANG['MISC']['players']).'.pdf';
		$personer = get_spillere();
		foreach ($personer as $id=>$person) {
			$personer[$id]['betalt'] = 1;
		}
		break;
		case 'arrangorer':
		$filename = 'tmp/'.mkfilename($LANG['MISC']['envelopes'].'-'.$LANG['MISC']['organizers']).'.pdf';
		$personer = get_arrangorer();
		foreach ($personer as $id=>$person) {
			$personer[$id]['betalt'] = 1;
		}
		break;
	}
	$pdf = new FPDF('L', 'mm', $envelopetype);
	$pdf->Open();
	$pdf->SetDisplayMode('real');
	$pdf->SetFont('Arial','',20);
	$pdf->SetLeftMargin(20);
	foreach ($personer as $person) {
		$print_this = true;

		if (($mailpref) && ($person['mailpref'] != 'post')) {
			$print_this = false;
		}

		if (($exclude_unpaid) && (!$person['betalt'])) {
			$print_this = false;
		}
		if ($print_this) {
			if ($print == 'kontakter') {
				$pdf->AddPage();
				$pdf->Ln(40);
				$pdf->Write(15, $person['navn']);
				if ($person['kontaktperson']) {
					$pdf->Ln(10);
					$pdf->Write(15, 'att: '.$person['kontaktperson']);
				}
				$pdf->Ln(10);
				$pdf->Write(15, $person['adresse']);
				$pdf->Ln(10);
				$pdf->Write(15, $person['postnr'].' '.$person['poststed']);
			} else {
				$pdf->AddPage();
				$pdf->Ln(50);
				$pdf->Write(15, $person['fornavn'].' '.$person['etternavn']);
				$pdf->Ln(10);
				$pdf->Write(15, $person['adresse']);
				$pdf->Ln(10);
				$pdf->Write(15, $person['postnr'].' '.$person['poststed']);
			}
		}
	}
	$pdf->Output($filename, 'F');
	return $filename;
}

function pdf_write_kjentfolk(&$pdf, $rolle) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin, $pdf_dimvars;
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);

	$pdf_dimvars = pdf_dimvars($config['paperformat']);
	$pdf_dimvars['imgdim'] = ($pdf_dimvars['imgdim'] * 0.66);

	$pdf_header_left = $rolle['navn'];
	$pdf_header_center = $spillnavn;
	$pdf_header_right = $spiller['fornavn'].' '.$spiller['etternavn'];
	$pdf_left_margin = $pdf_dimvars['lmargin'];
	$kjentfolk = get_rolle_kjentfolk($rolle['rolle_id'], $rolle['spill_id']);


	if ($kjentfolk) {
		$pdf->StartPageGroup();
		$pdf->AddPage();
		$pdf->SetFont('Times','B',16);
		$pdf->SetLeftMargin(10);
		$pdf->Ln(1);
		$pdf->Cell(0,0,$LANG['MISC']['acquaintances'],0,1,'C');
		$pdf->Ln(6);
		$pdf->SetFont('Times','B',12);
		$pdf->Cell(0,0,count($kjentfolk).' '.$LANG['MISC']['acquaintance_s'],0,1,'C');
		$pdf->SetLeftMargin($pdf_left_margin);
		$pdf->Ln(10);
		foreach($kjentfolk as $kjentdata) {
			$kjentspiller = get_person($kjentdata['spiller_id']);
			if (!$kjentspiller) {
				$spillernavn = strip_tags($LANG['MISC']['none']);
			} else {
				if (!$kjentspiller['email']) {
					$kjentspiller['email'] = strip_tags($LANG['MISC']['no_email']);
				}
				$spillernavn = $kjentspiller['fornavn'].' '.$kjentspiller['etternavn'].' <'.$kjentspiller['email'].'>';
			}
			$mugshot = mugshot($kjentspiller, 1);
			$beskrivelse = $kjentdata['beskrivelse'.$kjentdata['level']];
			if ($pdf->GetY() > $pdf_dimvars['pagebreak']) {
				$pdf->SetX(0);
				$pdf->SetY(0);
				$pdf->AddPage();
			} else {
				while ($pdf->GetY() < $oldY + ($pdf_dimvars['imgdim'] * 1.3)) {
					$pdf->Ln(2);
				}
			}
			$pdf->Image($mugshot, 10, $pdf->GetY()-2, $pdf_dimvars['imgdim']);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(0,0, $kjentdata['navn'],0,0,'L');
			$pdf->SetFont('Arial','',10);
			$spillerstr = '('.$LANG['MISC']['player'].': '.$spillernavn.')';
			if ($config['paperformat'] == 'A5') {
				$pdf->Ln(5);
				$pdf->Cell(0,0, $spillerstr,0,1,'L');
			} else {
				if($pdf->GetStringWidth($kjentdata['navn'].$spillerstr) > $pdf->GetPageWidth() - $pdf_dimvars['imgdim']+20)
					$pdf->Ln(5);
				$pdf->Cell(0,0, $spillerstr,0,1,'R');
			}
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(0,0,'- '.$kjentdata['kjentgrunn'],0,1,'L');
			$pdf->Ln(5);
			$pdf->SetFont('Arial','',10);
			$pdf->Write(5,$beskrivelse);
			$oldY = $pdf->GetY();
		}
		return true;
	}
	return false;
}

function pdf_write_kjentgrupper(&$pdf, $rolle) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin, $pdf_dimvars;
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);

	$pdf_dimvars = pdf_dimvars($config['paperformat']);
	$pdf_dimvars['imgdim'] = ($pdf_dimvars['imgdim'] * 0.66);

	$pdf_header_left = $rolle['navn'];
	$pdf_header_center = $spillnavn;
	$pdf_header_right =	$spiller['fornavn'].' '.$spiller['etternavn'];
	$pdf_left_margin = $pdf_dimvars['lmargin'];


	$grupper = get_kjentgrupper($rolle['rolle_id'], $rolle['spill_id']);
	if ($grupper) {
		$pdf->StartPageGroup();
		$pdf->AddPage();
		$pdf->SetLeftMargin(10);
		$pdf->Ln(1);
		$pdf->SetFont('Times','B',16);
		$pdf->Cell(0,0,strip_tags($LANG['MISC']['acquainted_groups']),0,1,'C');
		$pdf->Ln(6);
		$i = 1;
		foreach($grupper as $gruppe) {
			if ($i != 1) {
				unset($oldY);
				$pdf->SetX(0);
				$pdf->SetY(0);
				$pdf->AddPage();
			}
			$i++;
			$pdf->SetLeftMargin(10);
			$pdf->Ln(1);
			$pdf->SetFont('Times','B',14);
			$pdf->Cell(0,0,$gruppe['navn'],0,1,'C');
			$pdf->Ln(5);
			$pdf->SetFont('Times','B',12);
			$pdf->Cell(0,0,'('.strip_tags($gruppe['kjentgrunn']).')',0,1,'C');
			$pdf->SetLeftMargin($pdf_left_margin);
			$pdf->Ln(20);
			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				$pdf->Cell(0,0,strip_tags($LANG['MISC']['empty_group']),0,1,'C');
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
						$mugshot = mugshot($kjentspiller, 1);
						if ($pdf->GetY() > $pdf_dimvars['pagebreak']) {
							$pdf->SetX(0);
							$pdf->SetY(0);
							$pdf->AddPage();
						} else {
							while ($pdf->GetY() < $oldY + ($pdf_dimvars['imgdim']*1.3)) {
								$pdf->Ln(2);
							}
						}
						$pdf->Image($mugshot, 10, $pdf->GetY()-2, $pdf_dimvars['imgdim']);
						$pdf->SetFont('Arial','B',10);
						$pdf->Cell(0,0, $medlem['navn'],0,0,'L');
						$pdf->SetFont('Arial','',10);
						$kjentspillerstr = '('.$LANG['MISC']['player'].': '.$kjentspillernavn.')';
						if ($config['paperformat'] == 'A5') {
							$pdf->Ln(5);
							$pdf->Cell(0,0, $kjentspillerstr,0,1,'L');
						} else {
							if($pdf->GetStringWidth($medlem['navn'].$kjentspillerstr) > $pdf->GetPageWidth() - $pdf_dimvars['imgdim']+20)
								$pdf->Ln(5);
							$pdf->Cell(0,0, $kjentspillerstr,0,1,'R');
						}
						$pdf->Ln(5);
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags($medlem['beskrivelse_gruppe']));
						$oldY = $pdf->GetY();
						unset($kjentrolle, $kjentspiller, $kjentspillernavn, $kjentspillerstr);
					}
				}
			}
		}
		return true;
	}
	return false;
}

function pdf_write_grupper(&$pdf, $rolle) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin, $pdf_dimvars;
	$arrangor = get_person($rolle['arrangor_id']);
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$spiller = get_person($rolle['spiller_id']);

	$pdf_dimvars = pdf_dimvars($config['paperformat']);
	$pdf_dimvars['imgdim'] = ($pdf_dimvars['imgdim'] * 0.66);

	$pdf_header_left = $rolle['navn'];
	$pdf_header_center = $spillnavn;
	$pdf_header_right =	$spiller['fornavn'].' '.$spiller['etternavn'];
	$pdf_left_margin = $pdf_dimvars['lmargin'];

	$grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id']);

	if ($grupper) {
		$pdf->StartPageGroup();
		$pdf->AddPage();
		$pdf->SetLeftMargin(10);
		$pdf->Ln(1);
		$pdf->SetFont('Times','B',16);
		$pdf->Cell(0,0,strip_tags($LANG['MISC']['groups']),0,1,'C');
		$pdf->Ln(6);
		$i = 1;
		foreach($grupper as $gruppe) {
			if ($i != 1) {
				unset($oldY);
				$pdf->SetX(0);
				$pdf->SetY(0);
				$pdf->AddPage();
			}
			$i++;
			$pdf->SetLeftMargin(10);
			$pdf->Ln(1);
			$pdf->SetFont('Times','B',14);
			$pdf->Cell(0,0,strip_tags($gruppe['navn']),0,1,'C');
			if ($gruppe['medlemsinfo']) {
				$pdf->Ln(5);
				$pdf->SetFont('Arial','B',11);
				$pdf->Write(5, $LANG['MESSAGE']['groupmember_info'].':');
				$pdf->Ln(5);
				$pdf->SetFont('Arial','',10);
				$pdf->Write(5, strip_tags($gruppe['medlemsinfo']));
				$pdf->Ln(10);
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(0,0,$LANG['MISC']['group_members'].':',0,1,'C');
				$pdf->Ln(5);
			}
			$pdf->SetLeftMargin($pdf_left_margin);
			$pdf->Ln(5);
			$medlemmer = get_gruppe_roller($gruppe['gruppe_id'], $gruppe['spill_id']);
			if (!$medlemmer) {
				$pdf->Cell(0,0,strip_tags($LANG['MISC']['empty_group']),0,1,'C');
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
						$mugshot = mugshot($kjentspiller, 1);
						if ($pdf->GetY() > $pdf_dimvars['pagebreak']) {
							$pdf->SetX(0);
							$pdf->SetY(0);
							$pdf->AddPage();
						} else {
							while ($pdf->GetY() < $oldY + ($pdf_dimvars['imgdim'] * 1.3)) {
								$pdf->Ln(2);
							}
						}
						$pdf->Image($mugshot, 10, $pdf->GetY()-2, $pdf_dimvars['imgdim']);
						$pdf->SetFont('Arial','B',10);
						$pdf->Cell(0,0, $medlem['navn'],0,0,'L');
						$pdf->SetFont('Arial','',10);
						$kjentspillerstr = '('.$LANG['MISC']['player'].': '.$kjentspillernavn.')';
						if ($config['paperformat'] == 'A5') {
							$pdf->Ln(5);
							$pdf->Cell(0,0, $kjentspillerstr,0,1,'L');
						} else {
							if($pdf->GetStringWidth($medlem['navn'].$kjentspillerstr) > $pdf->GetPageWidth() - $pdf_dimvars['imgdim']+20)
								$pdf->Ln(5);
							$pdf->Cell(0,0, $kjentspillerstr,0,1,'R');
						}
						$pdf->Ln(5);
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags($medlem['beskrivelse_gruppe']));
						$oldY = $pdf->GetY();
						unset($kjentrolle, $kjentspiller, $kjentspillernavn, $kjentspillerstr);
					}
				}
			}
		}
		return true;
	}
	return false;
}

function pdf_write_rolle(&$pdf, $rolle, $spiller, $internal_print = 0) {
	global $config, $LANG, $styleimages, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin, $pdf_dimvars;
	$spillinfo = get_spillinfo($rolle['spill_id']);
	$spillnavn = $spillinfo['navn'];
	$mal_id = $spillinfo['rollemal'];
	$malinfo = get_maldata($mal_id);
	$mal = get_rollemal($rolle['spill_id']);
	$pdf_left_margin = 10;

	$pdf_dimvars = pdf_dimvars($config['paperformat']);

	$arrangor = get_person($rolle['arrangor_id']);
	$mugshot = mugshot($spiller, 1);
	$pdf_header_center = $spillnavn;
	$pdf_header_left =	$rolle['navn'];
	$pdf_header_right = $spiller['fornavn'].' '.$spiller['etternavn'];
	$pdf->StartPageGroup();
	$pdf->AddPage();
	$pdf->SetLeftMargin(10);
	$pdf->SetTopMargin(15);
	$pdf->SetRightMargin($pdf_dimvars['imgdim'] + 20);
	$sep_length = $pdf_dimvars['sep_len'] - ($pdf_dimvars['imgdim'] * 1.2);
	$pdf->Image($mugshot,$pdf_dimvars['imgx'],$pdf_dimvars['imgy'],$pdf_dimvars['imgdim']);
	$page_one = true;

	foreach ($rolle as $fieldname => $value) {
		if (($pdf->GetY() > ($pdf_dimvars['imgdim'] * 1.7)) && ($page_one == true)) {
			$pdf->SetRightMargin(10);
			$sep_length = $pdf_dimvars['sep_len'];
			$page_one = false;
		}
		if (strpos($fieldname, 'field') !== false) {
			$fieldinfo = $mal[$fieldname];
			if ((!$fieldinfo['intern']) || ($internal_print)) {
				$extras = explode(';',$fieldinfo['extra']);
				switch ($fieldinfo['type']) {
					case 'inline':
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].': ');
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags(stripslashes($value)));
						$pdf->Ln(5);
						break;
					case 'inlinebox':
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle']);
						$pdf->Ln(5);
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags(stripslashes($value)));
						$pdf->Ln(5);
						break;
					case 'box':
						if ($page_one == true) {
							$pdf->SetY($pdf_dimvars['imgdim'] * 1.7);
							$pdf->SetRightMargin(10);
							$sep_length = $pdf_dimvars['sep_len'];
							$page_one = false;
						}
						$pdf->Ln(10);
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle']);
						$pdf->Ln(5);
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags(stripslashes($value)));
						$pdf->Ln(5);
						break;
					case 'listsingle':
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].': ');
						$pdf->SetFont('Arial','',10);
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							if (strtolower($value) == strtolower($extras[$i])) {
								$pdf->Write(5,strip_tags(stripslashes($value)));
							}
						}
						$pdf->Ln(5);
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
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].': ');
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags(stripslashes($value)));
						$pdf->Ln(5);
						break;
					case 'radio':
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].': ');
						$pdf->SetFont('Arial','',10);
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							if (strtolower($value) == strtolower($extras[$i])) {
								$pdf->Write(5,strip_tags(stripslashes($extras[$i])));
							}
						}
						$pdf->Ln(5);
						break;
					case 'check':
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].': ');
						$pdf->SetFont('Arial','',10);
						if ($value != 0) {
							$pdf->Write(5,strip_tags(stripslashes($extras[0])));
						} else {
							$pdf->Write(5,strip_tags(stripslashes($extras[1])));
						}
						$pdf->Ln(5);
						break;
					case 'calc':
						$calc = get_calc_formula($rolle[$malinfo[$extras[0]]['fieldname']], $extras[1]);
						@eval('\$calcresult = '.$calc.';');
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].': ');
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags(stripslashes($calcresult)));
						$pdf->Ln(5);
						break;
					case 'dots':
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$fieldinfo['fieldtitle'].':   ');
						$pdf->SetFont('Arial','',10);
						for ($i = 1; $i <= $value; $i ++) {
							$pdf->Image($styleimages['dot_print'], $pdf->GetX(), $pdf->GetY()+1, 3, 3);
							$pdf->Write(5, '    ');
						}
						for ($i = $value; $i < $extras[0]; $i ++) {
							$pdf->Image($styleimages['nodot_print'], $pdf->GetX(), $pdf->GetY()+1, 3, 3);
							$pdf->Write(5, '    ');
						}
						$pdf->Ln(5);
						break;
					case 'header':
						$pdf->SetFont('Arial','B',12);
						$pdf->Write(5,$fieldinfo['fieldtitle']);
						$pdf->Ln(5);
						break;
					case 'separator':
						$pdf->SetFont('Arial','',10);
						$pdf->Ln(2);
						$pdf->Rect($pdf->GetX(), $pdf->GetY(), $sep_length, 0.1);
						$pdf->Ln(5);
						break;
				}
			}
		} else {
			switch($fieldname) {
				case 'oppdatert':
				case 'rolle_id':
				case 'locked':
				case 'bilde':
				case 'spill_id':
				case 'status':
				case 'status_id':
				case 'status_tekst':
					break;
				case 'spiller_id':
					if (!$spiller) {
						$spillernavn = $LANG['MISC']['none'];
					} elseif (!$spiller['email']) {
						$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'];
					} else {
						$spillernavn = $spiller['fornavn'].' '.$spiller['etternavn'].' ('.$spiller['email'].')';
					}
					$pdf->SetFont('Arial','B',10);
					$pdf->Write(5,$LANG['MISC']['player'].': ');
					$pdf->SetFont('Arial','',10);
					$pdf->Write(5,strip_tags(stripslashes($spillernavn)));
					$pdf->Ln(5);
					break;
				case 'arrangor_id':
					if (!$arrangor) {
						$arrangornavn = $LANG['MISC']['none'];
					} elseif (!$arrangor['email']) {
						$arrangornavn = $arrangor['fornavn'].' '.$arrangor['etternavn'];
					} else {
						$arrangornavn = $arrangor['fornavn'].' '.$arrangor['etternavn'].' ('.$arrangor['email'].')';
					}
					$pdf->SetFont('Arial','B',10);
					$pdf->Write(5,$LANG['MISC']['organizer'].': ');
					$pdf->SetFont('Arial','',10);
					$pdf->Write(5,strip_tags(stripslashes($arrangornavn)));
					$pdf->Ln(5);
					break;
				case 'intern_info':
				case 'beskrivelse1':
				case 'beskrivelse2':
				case 'beskrivelse3':
				case 'beskrivelse_gruppe':
					if ($_POST['internal_print']) {
						$pdf->Ln(10);
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,$LANG['DBFIELD'][$fieldname]);
						$pdf->Ln(5);
						$pdf->SetFont('Arial','',10);
						$pdf->Write(5,strip_tags(stripslashes($value)));
						$pdf->Ln(5);
					}
					break;
				case 'navn':
					$pdf->SetFont('Arial','B',10);
					$pdf->Write(5,$LANG['DBFIELD'][$fieldname].': ');
					$pdf->SetFont('Arial','',10);
					$pdf->Write(5,strip_tags(stripslashes($value)));
					$pdf->Ln(5);
					if ($rolle['status']) {
						$pdf->SetFont('Arial','B',10);
						$pdf->Write(5,strip_tags($LANG['MISC']['status']).': ');
						$pdf->SetTextColor(255,0,0);
						$pdf->Write(5,strip_tags($LANG['MISC']['inactive']));
						$pdf->Ln(5);
						$pdf->Write(5,strip_tags($rolle['status_tekst']));
						$pdf->SetTextColor(0,0,0);
						$pdf->Ln(10);
					}
					break;
				default:
					$pdf->SetFont('Arial','B',10);
					$pdf->Write(5,$LANG['DBFIELD'][$fieldname].': ');
					$pdf->SetFont('Arial','',10);
					$pdf->Write(5,strip_tags(stripslashes($value)));
					$pdf->Ln(5);
			}
		}
	}
	return true;
}

function pdf_dimvars($format) {
	global $LANG;
	switch ($format) {
		case 'A4':
		$return = array(
		'imgdim'=>50,
		'imgx'=>150,
		'imgy'=>25,
		'sep_len'=>190,
		'pagebreak'=>200,
		'wmarkx'=>50,
		'wmarky'=>190,
		'lmargin'=>44
		);
		break;
		case 'A5':
		$return = array(
		'imgdim'=>35,
		'imgx'=>102,
		'imgy'=>25,
		'sep_len'=>128,
		'pagebreak'=>110,
		'wmarkx'=>25,
		'wmarky'=>150,
		'lmargin'=>34
		);
		break;
		case 'Letter':
		$return = array(
		'imgdim'=>50,
		'imgx'=>155,
		'imgy'=>25,
		'sep_len'=>195,
		'pagebreak'=>185,
		'wmarkx'=>50,
		'wmarky'=>190,
		'lmargin'=>44
		);
		break;
		case 'Legal':
		$return = array(
		'imgdim'=>50,
		'imgx'=>155,
		'imgy'=>25,
		'sep_len'=>195,
		'pagebreak'=>210,
		'wmarkx'=>50,
		'wmarky'=>190,
		'lmargin'=>44
		);
		break;
	}
	return $return;

}

?>
