<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            mail_functions.php                           #
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

# Get the MIME class used for sending MIME-compliant emails
#require_once("MIME.class.php");
require_once('email_message.php');

# The function doing the actual sending of mail.
# $from: 		A string with the sender name <email>
# $to: 			A string with the recipient name <email>
# $subject: 	The mail subject
# $body: 		The message body
# $attachments:	2-dimensional array, second dimension is associative:
#				file = path and name of the file
#				name = the name of the file as set in the MIME header
#				type = the MIME type of the file
/*function send_mail($from, $to, $subject, $body, $attachments = 0) {
	# Look for broken signature-separator and fix it
	if (strpos($body, "--\r\n") && !strpos($body, "-- \r\n")) {
		$body = substr_replace($body, "-- \r\n", lastIndexOf($body, "--\r\n"), 4);
	}
	$mime = new MIME_mail($from, $to, $subject, $body);
	if ($attachments) {
		foreach ($attachments as $attachment) {
			$mime->fattach($attachment['file'], $attachment['name'], $attachment['type']);
		}
	}
	if ($mime->send_mail()) {
		return true;
	}
	return false;
}
*/
function send_mail($from, $to, $replyto, $subject, $body, $attachments = 0) {
	# Look for broken signature-separator and fix it
	if (strpos($body, "--\r\n") && !strpos($body, "-- \r\n")) {
		$body = substr_replace($body, "-- \r\n", lastIndexOf($body, "--\r\n"), 4);
	}
	$mime = new email_message_class;
	$mime->SetHeader('From', $from);
	$mime->SetHeader('Subject', $subject);
	$mime->SetHeader('To', $to);
	if ($replyto != false) {
		$mime->SetHeader('Reply-To', $replyto);
	}
	$mime->AddPlainTextPart(str_replace("\r\n", "\n", $body));
	if ($attachments) {
		foreach ($attachments as $attachment) {
			$thisfile = array('FileName'=>$attachment['file'],'Content-Type'=>$attachment['type'],'Disposition'=>'attachment');
			$mime->AddFilePart($thisfile);
			unset($thisfile);
		}
	}
	$iteration = 0;
	$error = "A random string";
	while($error != '' && $iteration++ < 5) {
		$error = $mime->Send();
	}
	return empty($error);
}

# Send the character email to the player
# $rolle_id:	The ID of the character we're sending
# $spill_id:	The ID of the game to which the character belongs
function send_rolle_mail($rolle_id, $spill_id, $replyto = false) {
	global $config, $LANG;
	require_once('rtf_functions.php');
	require_once('pdf_functions.php');
	require_once('pclzip.lib.php');
	this_might_take_a_while();
	$rolle = get_rolle($rolle_id, $spill_id);
	$spiller = get_person($rolle['spiller_id']);
	$arrangor = get_arrangor($rolle['arrangor_id']);
	$grupper = get_rolle_grupper($rolle_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$to = $spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'>';
#	$to = 'roy@localhost';
	$from = $config['arrgruppenavn'].' / '.$arrangor['fornavn'].' '.$arrangor['etternavn'].' <'.$arrangor['email'].'>';
	$subject = strip_tags($LANG['MISC']['character']).' ('.$spillinfo['navn'].')';
	$body = $_POST['mailtekst'];

	if ($_POST['document_format']) {
		$format = $_POST['document_format'];
	} else {
		$format = $config['primary_exportformat'];
	}
	# Generate the document with the character and add it to the list of files to attach
	if ($format == "rtf") {
		$rollefil = rtf_get_rolle($rolle_id, $spill_id);
	} else {
		$rollefil = pdf_get_rolle($rolle_id, $spill_id);
	}
	$vedleggsfiler[] = $rollefil;

	# Add the list of acquaintances
	if (get_rolle_kjentfolk($rolle_id, $spill_id)) {
		if ($format == "rtf") {
			$kjentfolkfil = rtf_get_kjentfolk($rolle_id, $spill_id);
		} else {
			$kjentfolkfil = pdf_get_kjentfolk($rolle_id, $spill_id);
		}
		$vedleggsfiler[] = $kjentfolkfil;
		$_SESSION['vedlegg'][$rolle_id][$spill_id][] = basename($kjentfolkfil);
	}

	# Add descriptions of fellow groupmembers
	if ($grupper) {
		if ($format == "rtf") {
			$gruppefil = rtf_get_grupper($rolle_id, $spill_id);
		} else {
			$gruppefil = pdf_get_grupper($rolle_id, $spill_id);
		}
		$vedleggsfiler[] = $gruppefil;
		$_SESSION['vedlegg'][$rolle_id][$spill_id][] = basename($gruppefil);

		# Add attachments from group memberships
		foreach ($grupper as $gruppe) {
			if ($gruppevedlegg = get_vedleggsliste($gruppe['gruppe_id'], $spill_id, "gruppe")) {
				foreach ($gruppevedlegg as $filinfo) {
					$file = $config['filsystembane'].$filinfo['dir'].$filinfo['navn'];
					if (!in_array($file, $vedleggsfiler)) {
						$vedleggsfiler[] = $file;
						$_SESSION['vedlegg'][$rolle_id][$spill_id][] = $filinfo['dir'].$filinfo['navn'];
					}
					unset($file);
				}
			}
		}
	}

	# Add the acquainted groups document
	if (get_kjentgrupper($rolle_id, $spill_id)) {
		if ($format == "rtf") {
			$kjentgrupperfil = rtf_get_kjentgrupper($rolle_id, $spill_id);
		} else {
			$kjentgrupperfil = pdf_get_kjentgrupper($rolle_id, $spill_id);
		}
		$vedleggsfiler[] = $kjentgrupperfil;
		$_SESSION['vedlegg'][$rolle_id][$spill_id][] = basename($kjentgrupperfil);
	}

	# Add the character-specific attachments
	if ($rollevedlegg = get_vedleggsliste($rolle_id, $spill_id, "rolle")) {
		foreach ($rollevedlegg as $filinfo) {
			$file = $config['filsystembane'].$filinfo['dir'].$filinfo['navn'];
			if (!in_array($file, $vedleggsfiler)) {
				$vedleggsfiler[] = $file;
				$_SESSION['vedlegg'][$rolle_id][$spill_id][] = $filinfo['dir'].$filinfo['navn'];
			}
			unset($file);
		}
	}

	# Add the gamewide attachments
	if ($spillvedlegg = get_vedleggsliste(0, $spill_id, "spill")) {
		foreach ($spillvedlegg as $filinfo) {
			$file = $config['filsystembane'].$filinfo['dir'].$filinfo['navn'];
			if (!in_array($file, $vedleggsfiler)) {
				$vedleggsfiler[] = $file;
				$_SESSION['vedlegg'][$rolle_id][$spill_id][] = $filinfo['dir'].$filinfo['navn'];
			}
			unset($file);
		}
	}

	# Create the zipfile and put all documents and attachments in it
	$attachment = './tmp/'.mkfilename($rolle['navn']).'.zip';
	$zipfil = new PclZip($attachment);
	$zipfil->create($vedleggsfiler, PCLZIP_CB_PRE_ADD, 'add_zipfile_callback', PCLZIP_OPT_REMOVE_ALL_PATH);

	# Make the array for the send-function
	$attachmentfile = array(array(
	'file'=>$attachment,
	'name'=>$rolle['navn'],
	'type'=>'application/zip'
	));

	# And off we go
	$return = send_mail($from, $to, $replyto, $subject, $body, $attachmentfile);
	if ($return) {
		# Clean up the mess we've made
		if ($kjentfolkfil) {
			unlink($kjentfolkfil);
		}
		if ($kjentgrupperfil) {
			unlink($kjentgrupperfil);
		}
		if ($gruppefil) {
			unlink($gruppefil);
		}
		unlink($rollefil);
		unlink($attachment);
		return true;
	}
	return false;
}

# Send the character concept email to the player
# $konsept_id:	The ID of the character concept we're sending
# $spill_id:	The ID of the game to which the character concept belongs
function send_rollekonsept_mail($konsept_id, $spill_id, $replyto = false) {
	global $config, $spillnavn, $LANG;
	require_once('pclzip.lib.php');
	this_might_take_a_while();

	# Get the information needed and construct the To: and From: fields
	$rollekonsept = get_rollekonsept($konsept_id, $spill_id);
	$spiller = get_person($rollekonsept['spiller_id']);
	$arrangor = get_arrangor($rollekonsept['arrangor_id']);
	$to = $spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'>';
	$from = $config['arrgruppenavn'].' / '.$arrangor['fornavn'].' '.$arrangor['etternavn'].' <'.$arrangor['email'].'>';
	$subject = strip_tags($LANG['MISC']['character_concept']).' ('.$spillnavn.')';

	# Check for existence of fields in the template and replace it with the information if it's found
	if (strpos($_POST['mailtekst'], '[title]') && strpos($_POST['mailtekst'], '[description]')) {
		$body = str_replace('[title]', $rollekonsept['tittel'], str_replace('[description]', $rollekonsept['konsept'], $_POST['mailtekst']));
	} elseif (strpos($_POST['mailtekst'], '[tittel]') && strpos($_POST['mailtekst'], '[beskrivelse]')) {
		# Need this for compatibility with RAsLAV 2.0 templates, in case people don't read the update-docs
		$body = str_replace('[tittel]', $rollekonsept['tittel'], str_replace('[beskrivelse]', $rollekonsept['konsept'], $_POST['mailtekst']));
	} else {
		# Default is adding the information at the end of the mail, before the signature separator
		$body = '';
		# We check for a valid separator first
		if (strpos($_POST['mailtekst'], "-- \r\n")) {
			$bodydata = explode("-- \r\n", $_POST['mailtekst']);
			if (count($bodydata) > 2) {
				# You never know if someone put a separator into the main body as well for some reason
				for ($i = 0; $i < count($bodydata)-2; $i++) {
					$body .= $bodydata[$i]."-- \r\n";
				}
				$body .= $bodydata[$i];
				end($bodydata);
				$body .= "\r\n".strtoupper(strip_tags($LANG['MISC']['character_concept'])).":\r\n".$LANG['MISC']['title'].": ".$rollekonsept['tittel']."\r\n".$LANG['MISC']['description'].": ".$rollekonsept['konsept']."\r\n\r\n-- \r\n".current($bodydata);
			} else {
				$body = $bodydata[0]."\r\n".strtoupper(strip_tags($LANG['MISC']['character_concept'])).":\r\n".$LANG['MISC']['title'].": ".$rollekonsept['tittel']."\r\n".$LANG['MISC']['description'].": ".$rollekonsept['konsept']."\r\n\r\n-- \r\n".$bodydata[1];
			}
			# ...then check for an invalid separator (can't expect everyone to get that right)
		} elseif (strpos($_POST['mailtekst'], "--\r\n")) {
			$bodydata = explode("--\r\n", $_POST['mailtekst']);
			if (count($bodydata) > 2) {
				# You never know if someone put a separator into the main body as well for some reason
				for ($i = 0; $i < count($bodydata)-2; $i++) {
					$body .= $bodydata[$i]."--\r\n";
				}
				$body .= $bodydata[$i];
				end($bodydata);
				$body .= "\r\n".strtoupper(strip_tags($LANG['MISC']['character_concept'])).":\r\n".$LANG['MISC']['title'].": ".$rollekonsept['tittel']."\r\n".$LANG['MISC']['description'].": ".$rollekonsept['konsept']."\r\n\r\n-- \r\n".current($bodydata);
			}
			# No separator found, so we just add the info at the bottom
		} else {
			$body = $_POST['mailtekst']."\r\n\r\n".$LANG['MISC']['title'].": ".$rollekonsept['tittel']."\r\n".$LANG['MISC']['description'].": ".$rollekonsept['konsept']."\r\n\r\n";
		}
	}
	# Add any attachments defined for the concepts
	if ($konseptvedlegg = get_vedleggsliste(0, $spill_id, 'rollekonsept')) {
		$vedleggsfiler = array();
		foreach ($konseptvedlegg as $filinfo) {
			$file = $config['filsystembane'].$filinfo['dir'].$filinfo['navn'];
			if (!in_array($file, $vedleggsfiler)) {
				$vedleggsfiler[] = $file;
				$_SESSION['vedlegg'][$konsept_id][$spill_id][] = $filinfo['dir'].$filinfo['navn'];
			}
			unset($file);
		}
	}

	# If we have any attachments, create a zipfile and add the attachments to it
	if ($vedleggsfiler) {
		$attachment = './tmp/'.mkfilename(strip_tags($LANG['MISC']['character_concept']).' '.strip_tags($LANG['MISC']['attachment'])).'.zip';
		$zipfil = new PclZip($attachment);
		$zipfil->create($vedleggsfiler, PCLZIP_CB_PRE_ADD, 'add_zipfile_callback', PCLZIP_OPT_REMOVE_ALL_PATH);

		# Make the array for the send-function
		$attachmentfile = array(array(
		'file'=>$attachment,
		'name'=>$rolle['navn'],
		'type'=>'application/zip'
		));
	}

	# And off we go
	$return = send_mail($from, $to, $replyto, $subject, $body, $attachmentfile);

	# Clean up the mess
	if ($return) {
		if ($attachment) {
			unlink($attachment);
		}
		return true;
	}
	return false;
}

# Send emails to a list of recipients from the DB
# $mailto: 		An array of ID's for the recipients
# $type: 		'kontakter' or 'personer' (contacts or persons - people are either players or organizers, contacts are neither)
function send_mass_mail($mailto, $type = 'personer', $replyto = false) {
	global $config, $spillnavn, $LANG;
	this_might_take_a_while();
	$from = $config['arrgruppenavn'].' / '.$_SESSION['navn'].' <'.$_SESSION['email'].'>';
	$subject = $_POST['subject'];
	$mailtekst = $_POST['mailtekst'];

	# Generate the attachments-list
	if ($_SESSION['mailvedlegg']) {
		foreach ($_SESSION['mailvedlegg'] as $key=>$value) {
			$fil = get_fil($key);
			$attachments[] = array(
			'file'=>$config['filsystembane'].$fil['dir'].$fil['navn'],
			'name'=>$fil['navn'],
			'type'=>$fil['type']
			);
		}
	}
	unset($_SESSION['mailvedlegg']);

	# Traverse the recipients and send one mail per
	$cc = '';
	foreach ($mailto as $person_id) {
		if ($type == 'kontakter') {
			$person = get_kontakt($person_id);
			if ($person['kontaktperson']) {
				$to = $person['navn'].' / '.$person['kontaktperson'].' <'.$person['email'].'>';
			} else {
				$to = $person['navn'].' <'.$person['email'].'>';
			}
		} else {
			$person = get_person($person_id);
			$to = $person['fornavn'].' '.$person['etternavn'].' <'.$person['email'].'>';
			$cc .= $to.', ';
		}
		send_mail($from, $to, false, $subject, $mailtekst, $attachments);
		
		# Sleep for half a second to prevent mailserver spamming
		sleep(0.5);
		
		unset($to,$person);
	}

	# Also send a copy to the LARP group address if it's set.
	if ($config['arrgruppemail']) {
		send_mail($from, $config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>', $replyto, $subject, $mailtekst, $attachments);
	}
	return true;
}

?>
