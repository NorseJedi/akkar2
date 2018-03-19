<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               download.php                              #
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

# All of these read the files that are to be downloaded rather than linking to the actual files, so it might take a while depending on many factors.
this_might_take_a_while();


if ($_REQUEST['pdf']) {
	# We're trying to download a PDF export of some kind.
	require('pdf_functions.php');
	$pdf = $_REQUEST['pdf'];
	unset ($filnavn);
	switch ($pdf) {
		case 'rolle':
			# It's a character - let's generate it and get the filename.
			$filnavn = pdf_get_rolle($_GET['rolle_id'], $spill_id);
			break;
		case 'kjentroller':
		case 'roller_liste':
			# It's an export of acquaintances
			$filnavn = pdf_get_kjentfolk($_GET['rolle_id'], $spill_id);
			break;
		case 'grupper':
			# It's an export of a characters group memberships
			$filnavn = pdf_get_grupper($_GET['rolle_id'], $spill_id);
			break;
		case 'kjentgrupper':
		case 'grupper_liste':
			# It's an export of a characters group acquaintances
			$filnavn = pdf_get_kjentgrupper($_GET['rolle_id'], $spill_id);
			break;
		case 'roller':
			# It's an export of several characters, lots of possible options here.
			$print = $_POST['rolle_print'];
			$exclude_unpaid = $_POST['rolle_exclude_unpaid'] ? 1 : 0;
			$mailpref = $_POST['rolle_mailpref'] ? 1 : 0;
			$internal_print = $_POST['internal_print'] ? 1 : 0;
			$rolle_exclude_arrangorer = $_POST['rolle_exclude_arrangorer'] ? 1 : 0;
			$rolle_exclude_unassigned = $_POST['rolle_exclude_unassigned'] ? 1 : 0;
			$rolle_exclude_deactivated = $_POST['rolle_exclude_deactivated'] ? 1 : 0;
			$full_rolle = $_POST['full_rolle'] ? 1 : 0;
			$filnavn = pdf_get_roller($spill_id, $print, $rolle_exclude_deactivated, $rolle_exclude_arrangorer, $rolle_exclude_unassigned, $exclude_unpaid, $mailpref, $internal_print, $full_rolle);
			break;
		case 'labels':
			# We're generating Avery address-labels. Figure out what exactly we want labels of, and make them.
			$print = $_POST['spiller_print'];
			$labeltype = $_POST['labeltype'];
			$exclude_unpaid = $_POST['spiller_exclude_unpaid'] ? 1 : 0;
			$mailpref = $_POST['spiller_mailpref'] ? 1 : 0;
			$filnavn = pdf_get_labels($spill_id, $print, $labeltype, $exclude_unpaid, $mailpref);
			break;
		case 'envelopes':
			# We're generating envelope printouts. Same as with Avery labels - figure out, then do.
			$print = $_POST['spiller_print'];
			$envelopetype = $_POST['envelopetype'];
			$exclude_unpaid = $_POST['spiller_exclude_unpaid'] ? 1 : 0;
			$mailpref = $_POST['spiller_mailpref'] ? 1 : 0;
			$filnavn = pdf_get_envelopes($spill_id, $print, $envelopetype, $exclude_unpaid, $mailpref);
			break;
		default:
			# Hah! Your vicious plot to take over the world has failed! Or you encountered a bug... probably a faulty link somewhere.
			exit('Access violation');
			break;
	}
	# Send the appropriate headers for file downloads to the user, and give the contents of the file.
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer');
	header('Content-Type: application/pdf');
	header('Content-Length: '.filesize($filnavn));
	header('Content-Disposition: attachment; filename='.basename($filnavn));
	readfile($filnavn);
	unlink($filnavn);
	exit();
} elseif ($_REQUEST['rtf']) {
	# RTF document requested... didn't think anyone ever used this stuff anymore.
	require('rtf_functions.php');
	$rtf = $_REQUEST['rtf'];
	unset ($filnavn);
	switch ($rtf) {
		case 'rolle':
			# Single character...
			$filnavn = rtf_get_rolle($_GET['rolle_id'], $spill_id);
			break;
		case 'kjentroller':
		case 'roller_liste':
			# Acquaintances for a character
			$filnavn = rtf_get_kjentfolk($_GET['rolle_id'], $spill_id);
			break;
		case 'grupper':
			# Group memberships for a character
			$filnavn = rtf_get_grupper($_GET['rolle_id'], $spill_id);
			break;
		case 'kjentgrupper':
		case 'grupper_liste':
			# Acquainted groups for a character
			$filnavn = rtf_get_kjentgrupper($_GET['rolle_id'], $spill_id);
			break;
		default:
			# Nope - nothing actually requested. Funny...
			exit('Access violation');
			break;
	}
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer'); 
	header('Content-Type: application/rtf');
	header('Content-Length: '.filesize($filnavn)); 
	header('Content-Disposition: attachment; filename='.basename($filnavn));
	readfile($filnavn);
	unlink($filnavn);
	exit();
} elseif ($_REQUEST['txt']) {
	# Plaintext exports. Same stuff as the PDF exports, only different functions (obviously)
	require('txt_functions.php');
	$txt = $_REQUEST['txt'];
	unset ($filnavn);
	switch ($txt) {
		case 'rolle':
			$filnavn = txt_get_rolle($_GET['rolle_id'], $spill_id);
			break;
		case 'kjentroller':
		case 'roller_liste':
			$filnavn = txt_get_kjentfolk($_GET['rolle_id'], $spill_id);
			break;
		case 'grupper':
			$filnavn = txt_get_grupper($_GET['rolle_id'], $spill_id);
			break;
		case 'kjentgrupper':
		case 'grupper_liste':
			$filnavn = txt_get_kjentgrupper($_GET['rolle_id'], $spill_id);
			break;
		case 'roller':
			$print = $_POST['rolle_print'];
			$exclude_unpaid = $_POST['rolle_exclude_unpaid'] ? 1 : 0;
			$mailpref = $_POST['rolle_mailpref'] ? 1 : 0;
			$internal_print = $_POST['internal_print'] ? 1 : 0;
			$rolle_exclude_arrangorer = $_POST['rolle_exclude_arrangorer'] ? 1 : 0;
			$rolle_exclude_unassigned = $_POST['rolle_exclude_unassigned'] ? 1 : 0;
			$rolle_exclude_deactivated = $_POST['rolle_exclude_deactivated'] ? 1 : 0;
			$full_rolle = $_POST['full_rolle'] ? 1 : 0;
			$filnavn = txt_get_roller($spill_id, $print, $rolle_exclude_deactivated, $rolle_exclude_arrangorer, $rolle_exclude_unassigned, $exclude_unpaid, $mailpref, $internal_print, $full_rolle);
			break;
		default:
			exit('Access violation');
			break;
	}
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer'); 
	header('Content-Type: text/plain; charset=iso-8859-1');
	header('Content-Length: '.filesize($filnavn)); 
	header('Content-Disposition: attachment; filename='.basename($filnavn));
	readfile($filnavn);
	unlink($filnavn);
	exit();
} else {
	# Downloading a file from the filesystem. Get the file info, send the headers and pass the file contents through.
	$fil = get_fil($_GET['fil_id']);
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer'); 
	header('Content-Type: '.$fil['type']);
	header('Content-Length: ' . filesize($config['filsystembane'].''.$fil['dir'].''.$fil['navn'])); 
	header('Content-Disposition: attachment; filename='.$fil['navn']);
	readfile($config['filsystembane'].''.$fil['dir'].''.$fil['navn']);
	exit();
}
?>