<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            sendrollekonsept.php                         #
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
	$replyto = $config['arrgruppemail'];
	if (!$_GET['konsept_id']) {
		echo '
		<script language="JavaScript" type="text/javascript">
			function selectall(){
				for (var i=0;i<document.sendrollekonseptform.elements.length;i++){
					var e = document.sendrollekonseptform.elements[i];
					if ((e != document.sendrollekonseptform.sendpdf) && (e != document.sendrollekonseptform.use_replyto)) {
						e.checked = true;
					}
				}
			}
			function deselectall(){
				for (var i=0;i<document.sendrollekonseptform.elements.length;i++){
					var e = document.sendrollekonseptform.elements[i];
					if ((e != document.sendrollekonseptform.sendpdf) && (e != document.sendrollekonseptform.use_replyto)) {
						e.checked = false;
					}
				}
			}
			function check_replyto() {
				if (document.sendrollekonseptform.use_replyto.checked == true) {
					document.sendrollekonseptform.replyto.disabled = false;
				} else {
					document.sendrollekonseptform.replyto.disabled = true;
				}
			}
		</script>
		<h2 align="center">'.$LANG['MISC']['send_character_concepts'].'</h2>
		<br>
		';
		$allerollekonsept = get_roller_konsept($spill_id);
		if(!$allerollekonsept) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_character_concepts'].'</h4><br>
		   		<br>
			';
		} else {
			foreach ($allerollekonsept as $tmprollekonsept) {
				if ($tmprollekonsept['spiller_id']) {
					$rollekonsept[] = $tmprollekonsept;
				}
			}
			$numrollekonsept = count($rollekonsept);
			echo '
				<h4 align="center">'.$numrollekonsept.' '.$LANG['MISC']['concept_s'].'</h4><br>
				<h5 align="center">'.$LANG['MESSAGE']['only_assigned_concepts'].'</h5>
		   		<br>
			';
			echo '
				<form name="sendrollekonseptform" action="./sendrollekonsept.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
				<input type="hidden" name="spill_id" value="'.$spill_id.'">
				<input type="hidden" name="send_mail" value="yes">
				<table border="0" cellpadding="5" cellspacing="0" align="center">
					<tr valign="top" class="highlight">
			';
			$sorting = get_sorting('./sendrollekonsept.php', 'tittel', 'rollekonseptorder');
			echo '
						<td align="left">'.$LANG['MISC']['concept'].' '.$sorting.'</td>
						<td align="left">'.$LANG['MISC']['player'].'</td>
						<td align="left">'.$LANG['MISC']['paid'].'</td>
						<td align="center"><a title="'.$LANG['MISC']['all'].'" href="javascript:selectall();"><strong>+</strong></a> <a title="'.$LANG['MISC']['none'].'" href="javascript:deselectall();"><strong>-</strong></a></td>
					</tr>
			';
			foreach ($rollekonsept as $konsept) {
				$arrangor = get_arrangor($konsept['arrangor_id']);
				$spiller = get_person($konsept['spiller_id']);
				if ($spiller) {
					$paamelding = get_paamelding($konsept['spiller_id'], $konsept['spill_id']);
					if ($paamelding[betalt] == '1') {
						$betalt = '<span class="green">'.$LANG['MISC']['yes'].'</span>';
					} else {
						$betalt = '<span class="red">'.$LANG['MISC']['no'].'</span>';
					}
			        echo '
						<tr>
							<td align="left" nowrap><a href="./visrollekonsept.php?konsept_id='.$konsept['konsept_id'].'&amp;spill_id='.$konsept['spill_id'].'">'.$konsept['tittel'].'</a></td>
							<td align="left" nowrap><a href="./vispaamelding.php?person_id='.$spiller['person_id'].'&amp;spill_id='.$konsept['spill_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a> ('.$spiller['email'].')</td>
							<td align="left" nowrap>'.$betalt.'</td>
					';
					if ($spiller['email']) {
						echo '
							<td align="center"><input type="checkbox" name="mailto[]" value="'.$konsept['konsept_id'].'" checked></td>
						';
					} else {
						echo '
							<td align="center">&nbsp;</td>
						';
					}
					echo '
					</tr>
   					';
				}
			}
			echo '
				</table>
			';
		}
	} else {
		$rollekonsept = get_rollekonsept($_GET['konsept_id'], $spill_id);
		echo '
			<h2 align="center">'.$LANG['MISC']['send_character_concepts'].'</h2>
			<h3 align="center">'.$rollekonsept['tittel'].'</h3>
			<form name="sendrollerform" action="./sendrollekonsept.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
			<input type="hidden" name="spill_id" value="'.$spill_id.'">
			<input type="hidden" name="konsept_id" value="'.$rollekonsept['konsept_id'].'">
			<input type="hidden" name="send_mail" value="yes">
		';
	}
	echo '

		<table border="0" align="center" width="400" cellspacing="0" style="margin-top: 2em; margin-bottom: 2em;" class="bordered">
			<tr>
				<td colspan="2" class="highlight">'.$LANG['MISC']['from'].': '.$config['arrgruppenavn'].' / '.$_SESSION['navn'].' &lt;'.$_SESSION['email'].'&gt;</td>
			</tr>
			<tr>
				<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['subject'].': '.strip_tags($LANG['MISC']['character_concept']).' ('.$spillnavn.')</td>
			</tr>
			<tr>
			<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['use_replyto'].': <input type="checkbox" id="use_replyto" name="use_replyto"'; if ($_GET['use_replyto'] == 'true') { echo ' checked'; } echo ' onchange="javascript:check_replyto();" /></td>
			</tr>
			<tr>
			<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['replyto_address'].': <input type="text" id="replyto" name="replyto" size="'.strlen($replyto).'" value="'.$replyto.'"'; if ($_GET['use_replyto'] != 'true') { echo ' disabled'; } echo '></td>
			</tr>
			<tr>
	            <td align="center" class="highlight" nowrap="nowrap" colspan="2"><h3>'.$LANG['MISC']['messagetext'].'</h3></td>
			</tr>
			<tr>
	            <td align="right" class="highlight" nowrap="nowrap" colspan="2"><textarea name="mailtekst" id="mailtekst" rows="'.get_numrows($config['defaultrollekonseptmailtekst']).'" cols="74">'.parse_custom_tags($config['defaultrollekonseptmailtekst']).'</textarea></td>
	        </tr>
			<tr>
				<td class="highlight" nowrap="nowrap" align="left">
				'.inputsize_less('mailtekst', 1).'
				</td>
				<td class="highlight" nowrap="nowrap" align="right">
				'.inputsize_more('mailtekst', 1).'
				</td>
			</tr>
		</table>
		<table align="center">
			<tr>
				<td align="right"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				<td align="right"><button type="submit" onClick="javascript:return window.confirm(\''.$LANG['JSBOX']['confirm_send_concepts'].'\');">'.$LANG['MISC']['send'].'</button></td>
			</tr>
		</table>
		</form>
		';
} else {
	include('mail_functions.php');
	include('header.php');
	if (!$_POST['mailto'] && !$_POST['konsept_id']) {
		echo '
			<h2 align="center">'.$LANG['MISC']['send_character_concepts'].'</h2>
			<br><br>
			<h3 align="center">'.$LANG['MISC']['no_concepts_selected'].'</h4>
			<br><br>
			<p align="center">'.$LANG['MISC']['no_mails_sent'].'
			<br><br><br>
			<div align="center">
			<button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button>
			</div>
		';
	} else {
		if (!$_POST['konsept_id']) {
			foreach ($_POST['mailto'] as $konsept_id) {
				$send_til[] = $konsept_id;
			}
			echo '
				<h2 align="center">'.$LANG['MISC']['send_character_concepts'].'</h2>
				<br><br>
				<h3 align="center">'.$LANG['MISC']['concepts_sent'].'</h3>
				<br><br>
				<h4 align="center">'.$LANG['MISC']['these_concepts_sent'].':</h4>
				<br>
			';
		} else {
			$send_til[] = $_POST['konsept_id'];
			echo '
				<h3 align="center">'.$LANG['MISC']['concepts_sent'].'</h3>
				<br><br>
			';
		}
		this_might_take_a_while();
		if ($_POST['use_replyto']) {
			$replyto = $_POST['replyto'];
		} else {
			$replyto = false;
		}
		foreach($send_til as $konsept_id) {
			$_SESSION['vedlegg'][$konsept_id][$spill_id] = array();
			send_rollekonsept_mail($konsept_id, $spill_id, $replyto);
			
			# Sleep for half a second to prevent mailserver spamming
			sleep(0.5);
			
			$rollekonsept = get_rollekonsept($konsept_id, $spill_id);
			$spiller = get_person($rollekonsept['spiller_id']);
			$arrangor = get_arrangor($rollekonsept['arrangor_id']);
			$to = $spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'>';
			$from = $config['arrgruppenavn'].' / '.$arrangor['fornavn'].' '.$arrangor['etternavn'].' <'.$arrangor['email'].'>';
			echo '
				<h4 align="left">'.$rollekonsept['tittel'].' '.$LANG['MISC']['sent_to'].' '.htmlentities($to);
			if (count($_SESSION['vedlegg'][$konsept_id][$spill_id]) > 0) {
				echo ' '.$LANG['MISC']['with_attachment_s'].':</h4>
					<ul style="margin-top: 0;">
				';
				foreach($_SESSION['vedlegg'][$konsept_id][$spill_id] as $filnavn) {
					echo '
						<li>'.$filnavn.'</li>
					';
				}
				echo '
					</ul>
				';
			} else {
				echo ' '.$LANG['MISC']['without_attachments'].'.</h4>';
			}
			if (!$mailbody) {
				$mailbody = $LANG['MISC']['these_concepts_sent'];
			}
			if (count($_SESSION['vedlegg'][$konsept_id][$spill_id]) > '0') {
				$mailbody .= "\r\n\r\n".$rollekonsept['tittel'].' '.$LANG['MISC']['sent_to'].' '.$spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'> '.$LANG['MISC']['with_attachment_s'].':';
				foreach($_SESSION['vedlegg'][$konsept_id][$spill_id] as $filnavn) {
					$mailbody .= "\r\n- ".$filnavn;
				}
			} else {
				$mailbody .= "\r\n\r\n".$rollekonsept['tittel'].' '.$LANG['MISC']['sent_to'].' '.$spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'> '.$LANG['MISC']['without_attachments'];
			}
		}
		ini_restore('max_execution_time');
		$mailbody .= "\r\n\r\n\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail'].">\r\n".$config['arrgruppeurl']."\r\n";
		if ($config['arrgruppemail']) {
			mail($config['arrgruppemail'], $LANG['MISC']['send_character_concepts'].' ('.$spillnavn.')', ltrim($mailbody), 'From: '.$config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>');
		}
		echo '
			<table align="center">
				<tr>
					<td align="right">&nbsp;</td>
				</tr>
				<tr>
		';
		if (!$_POST['konsept_id']) {
			echo '
					<td align="right"><button type="button" onClick="javascript:window.location=\'./rollekonsept.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['character_concepts'].'</button></td>
			';
		} else {
			echo '
					<td align="right"><button type="button" onClick="javascript:window.location=\'./visrolle.php?konsept_id='.$rollekonsept['konsept_id'].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['character_concepts'].'</button></td>
			';
		}
		echo '
				</tr>
			</table>
		';
	}
}
include('footer.php');
?>