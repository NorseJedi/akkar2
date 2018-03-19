<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               sendmail.php                              #
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

if (!$_POST['send_mail']) {
	include('header.php');
	if ($_GET['new_attachment']) {
		$_SESSION['mailvedlegg'][$_GET['new_attachment']] = true;
	} elseif ($_GET['remove_attachment']) {
		unset($_SESSION['mailvedlegg'][$_GET['remove_attachment']]);
	}
	switch ($_GET['sendto']) {
		case 'arrangorer';
			$personer = get_arrangorer();
			break;
		case 'spillere';
			$personer = get_spillere();
			break;
		case 'kontakter';
			$kontakter = get_kontakter();
			foreach ($kontakter as $id=>$kontakt) {
				$personer[$id]['person_id'] = $kontakt['kontakt_id'];
				if ($kontakt['kontaktperson']) {
					$personer[$id]['navn'] = $kontakt['kontaktperson'].', '.$kontakt['navn'];
				} else {
					$personer[$id]['navn'] = $kontakt['navn'];
				}
				$personer[$id]['email'] = $kontakt['email'];
				$personer[$id]['mailpref'] = 'Ingen';
			}
			break;
		case 'betalte';
			$paameldte = get_paameldte($_GET['spill_id']);
			foreach ($paameldte as $id=>$paameldt) {
				if ($paameldt['betalt']) {
					$personer[$id] = $paameldt;
				}
			}
			break;
		case 'ubetalte';
			$paameldte = get_paameldte($_GET['spill_id']);
			foreach ($paameldte as $id=>$paameldt) {
				if (!$paameldt['betalt']) {
					$personer[$id] = $paameldt;
				}
			}
			break;
		case 'all';
			$personer = get_personer();
			break;
		default:
			$personer = get_paameldte($_GET['spill_id']);
			$subject = '['.$spillnavn.']';
	}
	if ($_GET['replyto']) {
		$replyto = $_GET['replyto'];
	} else {
		$replyto = $config['arrgruppemail'];
	}
	if ($_GET['subject']) {
		$subject = $_GET['subject'];
	}
	if ($_GET['mailtekst']) {
		$mailtekst = $_GET['mailtekst'];
	} else {
		$mailtekst = "\r\n\r\n\r\n\r\n\r\n\r\n-- \r\n".$_SESSION['navn'].' <'.$_SESSION['email'].">\r\n".$config['arrgruppenavn'].' <'.$config['arrgruppeurl'].'>';
	}
	$filer = get_filer();
	echo '
		<script language="JavaScript" type="text/javascript">
			function selectall(){
				for (var i=0;i<document.sendmailform.elements.length;i++){
					var e = document.sendmailform.elements[i];
					if ((e != document.sendmailform.sendpdf) && (e != document.sendmailform.use_replyto)) {
						e.checked = true;
					}
				}
			}
			function deselectall(){
				for (var i=0;i<document.sendmailform.elements.length;i++){
					var e = document.sendmailform.elements[i];
					if ((e != document.sendmailform.sendpdf) && (e != document.sendmailform.use_replyto)) {
			    		e.checked = false;
					}
				}
			}
			function add_attachment() {
				var attachment = document.getElementById(\'attach_file\').value;
				mailto = new Array();
				for (i = 0; i < document.sendmailform.elements.length; i++){
					if (document.sendmailform.elements[i].checked == true) {
			    		mailto[i] = document.sendmailform.elements[i].title;
					}
				}
				if (document.getElementById(\'use_replyto\').checked == true) {
					use_replyto = true;
				} else {
					use_replyto = false;
				}
				window.location=\'./sendmail.php?sendto='.$_GET['sendto'].'&spill_id='.$_GET['spill_id'].'&new_attachment=\' + attachment + \'&use_replyto=\' + use_replyto + \'&replyto=\' + document.getElementById(\'replyto\').value + \'&mailto=\' + mailto + \'&subject=\' + document.getElementById(\'subject\').value + \'&mailtekst=\' + escape(document.getElementById(\'mailtekst\').value) + \'#attachments\';
			}
			function remove_attachment(file_id) {
				mailto = new Array();
				for (i = 0; i < document.sendmailform.elements.length; i++){
					if (document.sendmailform.elements[i].checked == true) {
			    		mailto[i] = document.sendmailform.elements[i].title;
					}
				}
				if (document.getElementById(\'use_replyto\').checked == true) {
					use_replyto = true;
				} else {
					use_replyto = false;
				}
				window.location=\'./sendmail.php?sendto='.$_GET['sendto'].'&spill_id='.$_GET['spill_id'].'&remove_attachment=\' + file_id + \'&use_replyto=\' + use_replyto + \'&replyto=\' + document.getElementById(\'replyto\').value + \'&mailto=\' + mailto + \'&subject=\' + document.getElementById(\'subject\').value + \'&mailtekst=\' + escape(document.getElementById(\'mailtekst\').value) + \'#attachments\';
			}
			function set_body_insertpoint() {
				if (browsertype == \'ie\') {
					var text = document.sendmailform.mailtekst.createTextRange();
					text.moveStart(\'character\', 0);
					text.moveEnd(\'character\', -document.sendmailform.mailtekst.value.length + 0);
					text.select();
					document.sendmailform.mailtekst.blur();
				} else {
					document.sendmailform.mailtekst.setSelectionRange(0, 0);
				}
			}
			function check_replyto() {
				if (document.sendmailform.use_replyto.checked == true) {
					document.sendmailform.replyto.disabled = false;
				} else {
					document.sendmailform.replyto.disabled = true;
				}
			}
		</script>
		<h2 align="center">'.$LANG['MISC']['send_mail'].'</h2>
		<br>
	';
	if (!$personer) {
		echo '
			<h4 align="center">'.$LANG['MISC']['no_recipients'].'</h4><br>
	   		<br>
		';
	} else {
		echo '
			<form name="sendmailform" action="./sendmail.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
			<input type="hidden" name="spill_id" value="'.$spill_id.'">
			<input type="hidden" name="send_mail" value="yes">
			<input type="hidden" name="sendto" value="'.$_GET['sendto'].'">
			<table border="0" cellpadding="5" cellspacing="0" align="center">
				<tr valign="top" class="highlight">
					<td align="left">'.$LANG['MISC']['name'].'</td>
					<td align="center">'.$LANG['MISC']['mail_preference'].'</td>
					<td align="center">'.$LANG['MISC']['email'].'</td>
					<td align="center"><a title="'.$LAMG['MISC']['all'].'" href="javascript:selectall();"><strong>+</strong></a> <a title="'.$LAMG['MISC']['none'].'" href="javascript:deselectall();"><strong>-</strong></a></td>
				</tr>
		';
		if ($_GET['mailto']) {
			$mailto = explode(',', $_GET['mailto']);
		}
		foreach ($personer as $person) {
			if ($_GET['sendto'] == 'kontakter') {
		        echo '
					<tr>
						<td align="left" nowrap><a href="./viskontakt.php?kontakt_id='.$person['person_id'].'">'.$person['navn'].'</a></td>
						<td align="center">'.$LANG['MISC']['none'].'</td>
				';
			} else {
		        echo '
					<tr>
						<td align="left" nowrap><a href="./visperson.php?person_id='.$person['person_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a></td>
						<td align="center">'.$LANG['DBFIELD'][$person['mailpref']].'</td>
				';
			}
			if ($person['email']) {
				echo '
					<td align="center"><a href="mailto:'.$person['email'].'">'.$person['email'].'</a></td>
					<td align="center"><input type="checkbox" title="'.$person['person_id'].'" name="mailto[]" value="'.$person['person_id'].'"'; if ((!$mailto) || (in_array($person['person_id'], $mailto))) { echo ' checked'; } echo '></td>
				';
			} else {
				echo '
					<td align="center">'.$LANG['MISC']['none'].'</td>
					<td align="center">&nbsp;</td>
				';
			}
			echo '
				</tr>
  				';
		}
		echo '
			</table>
		';
	}
	echo '
		<table border="0" cellspacing="0" align="center" width="400" style="margin-top: 2em; margin-bottom: 2em;" class="bordered">
			<tr>
				<td colspan="2" class="highlight">'.$LANG['MISC']['from'].': '.$_SESSION['navn'].' ['.$config['arrgruppenavn'].'] &lt;'.$_SESSION['email'].'&gt;</td>
			</tr>
			<tr>
				<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['subject'].': <input type="text" id="subject" name="subject" size="50" value="'.$subject.'"></td>
			</tr>
			<tr>
			<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['use_replyto'].': <input type="checkbox" id="use_replyto" name="use_replyto"'; if ($_GET['use_replyto'] == 'true') { echo ' checked'; } echo ' onchange="javascript:check_replyto();" /></td>
			</tr>
			<tr>
			<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['replyto_address'].': <input type="text" id="replyto" name="replyto" size="'.strlen($replyto).'" value="'.$replyto.'"'; if ($_GET['use_replyto'] != 'true') { echo ' disabled'; } echo '></td>
			</tr>
			<tr>
	            <td class="highlight" align="right" nowrap colspan="2"><textarea name="mailtekst" id="mailtekst" rows="'.get_numrows($mailtekst, 5).'" cols="74">'.$mailtekst.'</textarea></td>
	        </tr>
			<tr>
				<td class="highlight" align="left">
				'.inputsize_less('mailtekst', 1).'
				</td>
				<td class="highlight" align="right">
				'.inputsize_more('mailtekst', 1).'
				</td>
			</tr>
		</table>
		<h3 align="center"><a name="attachments"></a>'.$LANG['MISC']['attachments'].'</h3>
	';
	if (!$_SESSION['mailvedlegg']) {
		echo '
			<h4 align="center">'.$LANG['MISC']['no_attachments'].'</h4>
		';
	} else {
		echo '
			<table align="center" cellspacing="0" width="50%">
			<tr class="highlight">
				<td>'.$LANG['MISC']['filename'].'</td>
				<td>'.$LANG['MISC']['filesize'].'</td>
				<td>'.$LANG['MISC']['description'].'</td>
				<td>'.$LANG['MISC']['updated'].'</td>
				<td>&nbsp;</td>
			</tr>
		';
		foreach ($_SESSION['mailvedlegg'] as $key=>$value) {
			$fil = get_fil($key);
			print_a($fileinfo);
			if (!is_file($config[filsystembane].$fil['dir'].$fil['navn'])) {
				$fil_navn = '<span class="red">'.$fil['navn'].'</span>';
				$fil['size'] = 0;
			} else {
				$fil['size'] = filesize($config['filsystembane'].''.$fil['dir'].''.$fil['navn']);
				$fil_navn = '<a href="./visfil.php?fil_id='.$fil['fil_id'].'&amp;spill_id='.$spill_id.'">'.$fil['navn'].'</a>';
			}
			$fil_size = get_human_readable_size($fil['size']);
			echo '
				<tr>
					<td nowrap>'.$fil['dir'].$fil_navn.'</td>
					<td nowrap>'.$fil_size.'</td>
					<td title="'.$fil['beskrivelse'].'">'.broken_text($fil['beskrivelse'], 65).'</td>
					<td nowrap>'.ucfirst(strftime($config['medium_dateformat'], $fil['oppdatert'])).'</td>
					<td><button type="button" onClick="javascript:remove_attachment(\''.$fil['fil_id'].'\');">'.$LANG['MISC']['remove'].'</button></td>
				</tr>
			';
		}
		echo '
			</table>
		';
	}
	echo '
		<h4 align="center">'.$LANG['MISC']['assign_new_attachment'].'</h4>
		<table align="center" cellspacing="0" border="0">
			<tr class="highlight">
				<td>'.$LANG['MISC']['file'].'</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><select id="attach_file">
					<option value="" class="selectname">- '.$LANG['MISC']['select'].' - </option>
	';
	foreach ($filer as $fil) {
		if (!$_SESSION['mailvedlegg'][$fil['fil_id']]) {
			echo '<option value="'.$fil['fil_id'].'">'.$fil['dir'].$fil['navn'].'</option>';
		}
	}
	echo '
					</select>
				</td>
				<td style="vertical-align: middle"><button type="button" onClick="javascript:add_attachment();">'.$LANG['MISC']['add'].'</button></td>
			</tr>
		</table>
		<table align="center" border="0" style="margin-top: 2em;">
			<tr>
				<td align="right" width="50%"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				<td align="left" width="50%"><button type="submit" onClick="return window.confirm(\''.$LANG['MISC']['send_mail'].': '.$LANG['JSBOX']['general_confirm'].'\');">'.$LANG['MISC']['send'].'</button></td>
			</tr>
		</table>
		</form>
		<script language="JavaScript" type="text/javascript">
			set_body_insertpoint();
		</script>
	';
} else {
	include('mail_functions.php');
	include('header.php');
	if (!$_POST['mailto']) {
		echo '
			<h2 align="center">'.$LANG['MISC']['send_mail'].'</h2>
			<br><br>
			<h3 align="center">'.$LANG['MISC']['no_recipients_selected'].'</h4>
			<br><br>
			<p align="center">'.$LANG['MISC']['no_mails_sent'].'
			<br><br><br>
			<div align="center">
			<button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button>
			</div>
		';
	} else {
		switch ($_POST['sendto']) {
			case 'arrangorer';
				$blink = 'arrangorer.php';
				$btext = $LANG['MISC']['organizers'];
				break;
			case 'spillere';
				$blink = 'spillere.php';
				$btext = $LANG['MISC']['players'];
				break;
			case 'paameldte';
				$blink = 'paameldinger.php?spill_id='.$spill_id;
				$btext = $LANG['MISC']['registrations'];
				break;
			case 'kontakter';
				$blink = 'kontakter.php';
				$btext = $LANG['MISC']['contacts'];
				break;
		}
		echo '
			<br><br>
			<h3 align="center">'.$LANG['MISC']['mail_sent'].'</h3>
			<br><br><br>
			<div align="center">
			<button type="button" onClick="window.location=\'./'.$blink.'\';">'.$btext.'</button>
			</div>
		';
		this_might_take_a_while();
		$from = $_SESSION['navn'].' <'.$_SESSION['email'].'>';
		if ($_POST['use_replyto']) {
			$replyto = $_POST['replyto'];
		} else {
			$replyto = false;
		}
		send_mass_mail($_POST['mailto'], $_POST['sendto'], $replyto);
	}
}
include('footer.php');
?>