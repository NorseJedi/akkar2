<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              sendroller.php                             #
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
	$check_replyto = '
			function check_replyto() {
				if (document.sendrollerform.use_replyto.checked == true) {
					document.sendrollerform.replyto.disabled = false;
				} else {
					document.sendrollerform.replyto.disabled = true;
				}
			}';
	if (!$_GET['rolle_id']) {
		echo '
		<script language="JavaScript" type="text/javascript">
			function selectall(){
				for (var i=0;i<document.sendrollerform.elements.length;i++){
					var e = document.sendrollerform.elements[i];
					if ((e != document.sendrollerform.sendpdf) && (e != document.sendrollerform.use_replyto)) {
						e.checked = true;
					}
				}
			}
			function deselectall(){
				for (var i=0;i<document.sendrollerform.elements.length;i++){
					var e = document.sendrollerform.elements[i];
					if ((e != document.sendrollerform.sendpdf) && (e != document.sendrollerform.use_replyto)) {
						e.checked = false;
					}
				}
			}'.$check_replyto.'	
		</script>
		<h2 align="center">'.$LANG['MISC']['send_characters'].'</h2>
		<br>
		';
		$alleroller = get_roller($spill_id);
		if(!$alleroller) {
			echo '
				<h4 align="center">'.$LANG['MISC']['no_characters'].'</h4><br>
		   		<br>
			';
		} else {
			foreach ($alleroller as $rolle) {
				if ($rolle['spiller_id']) {
					$roller[] = $rolle;
				}
			}
			$numroller = count($roller);
			echo '
				<h4 align="center">'.$numroller.' '.$LANG['MISC']['character_s'].'</h4><br>
				<h5 align="center">'.$LANG['MESSAGE']['only_assigned_characters'].'</h5>
		   		<br>
			';
			echo '
				<form name="sendrollerform" action="./sendroller.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
				<input type="hidden" name="spill_id" value="'.$spill_id.'">
				<input type="hidden" name="send_mail" value="yes">
				<table border="0" cellpadding="5" cellspacing="0" align="center">
					<tr valign="top" class="highlight">
			';
			$sorting = get_sorting('./sendroller.php', 'navn', 'rolleorder');
			echo '
						<td align="left">'.$LANG['MISC']['character'].' '.$sorting.'</td>
						<td align="left">'.$LANG['MISC']['player'].'</td>
						<td align="center">'.$LANG['MISC']['paid'].'</td>
						<td align="center">'.$LANG['MISC']['mail_preference'].'</td>
						<td align="center"><a title="'.$LANG['MISC']['all'].'" href="javascript:selectall();"><strong>+</strong></a> <a title="'.$LANG['MISC']['none'].'" href="javascript:deselectall();"><strong>-</strong></a></td>
					</tr>
			';
			foreach ($roller as $rolle) {
				$arrangor = get_arrangor($rolle['arrangor_id']);
				$spiller = get_person($rolle['spiller_id']);
				if ($spiller) {
					$paamelding = get_paamelding($rolle['spiller_id'], $rolle['spill_id']);
					if ($paamelding['betalt'] == '1') {
						$betalt = '<span class="green">'.$LANG['MISC']['yes'].'</span>';
					} else {
						$betalt = '<span class="red">'.$LANG['MISC']['no'].'</span>';
					}
			        echo '
						<tr>
							<td align="left" nowrap><a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a></td>
							<td align="left" nowrap><a href="./vispaamelding.php?person_id='.$spiller['person_id'].'">'.$spiller['fornavn'].' '.$spiller['etternavn'].'</a> ('.$spiller['email'].')</td>
							<td align="center" nowrap>'.$betalt.'</td>
							<td align="center" nowrap>'.$LANG['DBFIELD'][$spiller['mailpref']].'</td>
					';
					if ($spiller['email']) {
						echo '
							<td align="center"><input type="checkbox" name="mailto[]" value="'.$rolle['rolle_id'].'"'; if ($spiller['mailpref'] == 'email') { echo ' checked'; } echo '></td>
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
		$rolle = get_rolle($_GET['rolle_id'], $spill_id);
		echo '
		<script language="JavaScript" type="text/javascript">'.$check_replyto.'
		</script>
		<h2 align="center">'.$LANG['MISC']['send_characters'].'</h2>
		<h3 align="center">'.$rolle['navn'].'</h3>
		<form name="sendrollerform" action="./sendroller.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="spill_id" value="'.$spill_id.'">
		<input type="hidden" name="rolle_id" value="'.$rolle['rolle_id'].'">
		<input type="hidden" name="send_mail" value="yes">
		';
	}
	echo '

		<table border="0" align="center" width="400" cellspacing="0" style="margin-top: 2em; margin-bottom: 2em;" class="bordered">
			<tr>
				<td colspan="2" class="highlight">'.$LANG['MISC']['from'].': '.$config['arrgruppenavn'].' / '.$_SESSION['navn'].' &lt;'.$_SESSION['email'].'&gt;</td>
			</tr>
			<tr>
				<td colspan="2" class="highlight" nowrap="nowrap">'.$LANG['MISC']['subject'].': '.strip_tags($LANG['MISC']['character']).' ('.$spillnavn.')</td>
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
	            <td align="right" class="highlight" nowrap="nowrap" colspan="2"><textarea name="mailtekst" id="mailtekst" rows="'.get_numrows($config['defaultrollemailtekst']).'" cols="74">'.parse_custom_tags($config['defaultrollemailtekst']).'</textarea></td>
	        </tr>
			<tr>
				<td class="highlight" nowrap="nowrap" align="left">
				'.inputsize_less('mailtekst', 1).'
				</td>
				<td class="highlight" nowrap="nowrap" align="right">
				'.inputsize_more('mailtekst', 1).'
				</td>
			</tr>
		';
		if ($config['allow_exportformat_override']) {
			echo '
				<tr>
					<td class="highlight" nowrap="nowrap" align="right" width="50%"><h4 class="table">'.$LANG['MISC']['document_format'].'</h4></td>
					<td class="highlight" nowrap="nowrap" align="left" width="50%"><select name="document_format">
						<option value="pdf"'; if ($config['primary_exportformat'] == 'pdf') { echo ' selected'; } echo '>PDF</option>
						<option value="rtf"'; if ($config['primary_exportformat'] == 'rtf') { echo ' selected'; } echo '>RTF</option>
					</select>
					</td>
				</tr>
			';
		}
		echo '
			<tr>
				<td colspan="2" class="highlight">&nbsp;</td>
			</tr>
		</table>
		<table align="center" border="0" cellspacing="0">
			<tr>
				<td align="right" width="50%"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				<td align="left" width="50%"><button type="submit" onClick="javascript:return window.confirm(\''.$LANG['JSBOX']['confirm_send_characters'].'\');">'.$LANG['MISC']['send'].'</button></td>
			</tr>
		</table>
		</form>
		';
} else {
	include('mail_functions.php');
	include('header.php');
	if (!$_POST['mailto'] && !$_POST['rolle_id']) {
		echo '
			<h2 align="center">'.$LANG['MISC']['send_characters'].'</h2>
			<br><br>
			<h3 align="center">'.$LANG['MISC']['no_characters_selected'].'</h4>
			<br><br>
			<p align="center">'.$LANG['MISC']['no_mails_sent'].'
			<br><br><br>
			<div align="center">
			<button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button>
			</div>
		';
	} else {
		if (!$_POST['rolle_id']) {
			foreach ($_POST['mailto'] as $rolle_id) {
				$send_til[] = $rolle_id;
			}
			echo '
				<h3 align="center">'.$LANG['MISC']['characters_sent'].'</h3>
				<br><br>
				<h4 align="center">'.$LANG['MISC']['these_characters_sent'].':</h4>
				<br>
			';
		} else {
			$send_til[] = $_POST['rolle_id'];
			echo '
				<h3 align="center">'.$LANG['MISC']['character_sent'].'</h3>
				<br><br>
			';
		}
		this_might_take_a_while();
		if ($_POST['use_replyto']) {
			$replyto = $_POST['replyto'];
		} else {
			$replyto = false;
		}
		foreach($send_til as $rolle_id) {
			$_SESSION['vedlegg'][$rolle_id][$spill_id] = array();
			send_rolle_mail($rolle_id, $spill_id, $replyto);
			
			# Sleep for half a second to prevent mailserver spamming
			sleep(0.5);
			
			$rolle = get_rolle($rolle_id, $spill_id);
			$spiller = get_person($rolle[spiller_id]);
			$arrangor = get_arrangor($rolle[arrangor_id]);
			$to = $spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'>';
			$from = $config['arrgruppenavn'].' / '.$arrangor['fornavn'].' '.$arrangor['etternavn'].' <'.$arrangor['email'].'>';
			echo '
				<h4 align="left"><a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$rolle['navn'].'</a> '.$LANG['MISC']['sent_to'].' '.htmlentities($to);
			if (count($_SESSION['vedlegg'][$rolle_id][$spill_id]) > 0) {
				echo ' '.$LANG['MISC']['with_attachment_s'].':</h4>
					<ul style="margin-top: 0;">
				';
				foreach($_SESSION['vedlegg'][$rolle_id][$spill_id] as $filnavn) {
					echo '
						<li>'.$filnavn.'</li>
					';
				}
				echo '
					</ul>
				';
			} else {
				echo ' '.$LANG['MISC']['without_attachments'].'</h4>';
			}
			if (!$mailbody) {
				$mailbody = $LANG['MISC']['these_characters_sent'];
			}
			if (count($_SESSION['vedlegg'][$rolle_id][$spill_id]) > '0') {
				$mailbody .= "\r\n\r\n".$rolle['navn'].' '.$LANG['MISC']['sent_to'].' '.$spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'> '.$LANG['MISC']['with_attachment_s'].':';
				foreach($_SESSION['vedlegg'][$rolle_id][$spill_id] as $filnavn) {
					$mailbody .= "\r\n- ".$filnavn;
				}
			} else {
				$mailbody .= "\r\n\r\n".$rolle['navn'].' '.$LANG['MISC']['sent_to'].' '.$spiller['fornavn'].' '.$spiller['etternavn'].' <'.$spiller['email'].'> '.$LANG['MISC']['without_attachments'].'.';
			}
		}
		ini_restore('max_execution_time');
		$mailbody .= "\r\n\r\n\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail'].">\r\n".$config['arrgruppeurl']."\r\n";
		if ($config['arrgruppemail']) {
			if (!$_POST['rolle_id']) {
				mail($config['arrgruppemail'], $LANG['MISC']['characters_sent'].' ('.$spillnavn.')', ltrim($mailbody),'From: '.$config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>');
			} else {
				mail($config['arrgruppemail'], $LANG['MISC']['character_sent'].' ('.$spillnavn.')', ltrim($mailbody),'From: '.$config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>');
			}
		}
		echo '
			<table align="center">
				<tr>
					<td align="right">&nbsp;</td>
				</tr>
				<tr>
		';
		if (!$_POST['rolle_id']) {
			echo '
					<td align="right"><button type="button" onClick="javascript:window.location=\'./roller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['characters'].'</button></td>
			';
		} else {
			echo '
					<td align="right"><button type="button" onClick="javascript:window.location=\'./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['charactersheet'].'</button></td>
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
