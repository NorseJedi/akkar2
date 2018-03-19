<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              utskrifter.php                             #
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

include('header.php');

echo '
<script language="JavaScript" type="text/javascript">
	function addremove_extraoptions() {
		hide(document.getElementById(\'rolle_options\'));
		hide(document.getElementById(\'spiller_options\'));
		hide(document.getElementById(\'envelope_options\'));
		hide(document.getElementById(\'label_options\'));
	';
	if ($spill_id) {
		echo '
		document.printoutform.spiller_print[0].disabled = true;
		document.printoutform.spiller_print[1].disabled = true;
		document.printoutform.spiller_exclude_unpaid.disabled  = true;
		document.printoutform.rolle_print[0].disabled = true;
		document.printoutform.rolle_print[1].disabled = true;
		document.printoutform.rolle_exclude_unpaid.disabled  = true;
		';
	}
	echo '
		document.printoutform.labeltype.disabled = true;
		document.printoutform.envelopetype.disabled = true;
		document.printoutform.spiller_mailpref.disabled  = true;
		document.printoutform.rolle_mailpref.disabled  = true;
		document.printoutform.full_rolle.disabled  = true;
		document.printoutform.internal_print.disabled = true;
		if (document.printoutform.format.value == \'text\') {
			document.printoutform.txt.disabled = false;
			document.printoutform.txt.value = \'roller\';
	';
	if ($spill_id) {
		echo '
			document.printoutform.rolle_print[0].disabled = false;
			document.printoutform.rolle_print[1].disabled = false;
			document.printoutform.rolle_exclude_unpaid.disabled  = false;
		';
	}
	echo '
			show(document.getElementById(\'rolle_options\'), tbody_showproperty);
			document.printoutform.rolle_mailpref.disabled  = false;
			document.printoutform.full_rolle.disabled  = false;
			document.printoutform.internal_print.disabled = false;
			document.printoutform.pdf.value = \'roller\';
			document.printoutform.pdf.disabled = true;
			show(document.getElementById(\'rolle_options\'), tbody_showproperty);
		} else {
			if (document.printoutform.format.value == \'pdf\') {
				document.printoutform.pdf.disabled = false;
				document.printoutform.txt.value = \'\';
				document.printoutform.txt.disabled = true;
				switch (document.printoutform.pdf.value) {
					case \'roller\':
	';
	if ($spill_id) {
		echo '
						document.printoutform.rolle_print[0].disabled = false;
						document.printoutform.rolle_print[1].disabled = false;
						document.printoutform.rolle_exclude_unpaid.disabled  = false;
		';
	}
	echo '
						show(document.getElementById(\'rolle_options\'), tbody_showproperty);
						document.printoutform.rolle_mailpref.disabled  = false;
						document.printoutform.full_rolle.disabled  = false;
						document.printoutform.internal_print.disabled = false;
						document.printoutform.format.disabled = false;
						break;
					case \'labels\':
						show(document.getElementById(\'spiller_options\'), tbody_showproperty);
						show(document.getElementById(\'label_options\'), tbody_showproperty);
						document.printoutform.labeltype.disabled = false;
						document.printoutform.spiller_mailpref.disabled  = false;
	';
	if ($spill_id) {
		echo '
						document.printoutform.spiller_print[0].disabled = false;
						document.printoutform.spiller_print[1].disabled = false;
						document.printoutform.spiller_exclude_unpaid.disabled  = false;
		';
	}
	echo '
						document.printoutform.format.value = \'pdf\';
						document.printoutform.format.disabled = true;
						break;
					case \'envelopes\':
						show(document.getElementById(\'spiller_options\'), tbody_showproperty);
						show(document.getElementById(\'envelope_options\'), tbody_showproperty);
						document.printoutform.envelopetype.disabled = false;
						document.printoutform.spiller_mailpref.disabled  = false;
	';
	if ($spill_id) {
		echo '
						document.printoutform.spiller_print[0].disabled = false;
						document.printoutform.spiller_print[1].disabled = false;
						document.printoutform.spiller_exclude_unpaid.disabled  = false;
		';
	}
	echo '
						document.printoutform.format.value = \'pdf\';
						document.printoutform.format.disabled = true;
						break;
				}
			} else {
				document.printoutform.pdf.value = \'\';
				document.printoutform.pdf.disabled = true;
			}
		} 
		check_disabled();
	}
	function validate_printoutform() {
		if ((document.printoutform.pdf.value == \'roller\') && (document.printoutform.rolle_print.value == \'\')) {
			alert(\''.$LANG['JSBOX']['select_what_to_print'].'\');
			return false;
		} else {
			if (document.printoutform.spiller_print.value == \'\') {
				alert(\''.$LANG['JSBOX']['select_what_to_print'].'\');
				return false;
			}
		}
		return true;
	}
	function check_disabled() {
		if (document.getElementById(\'print_kontakter\').checked == true) {
			document.printoutform.spiller_mailpref.disabled = true;
	';
	if ($spill_id) {
		echo '
			document.printoutform.spiller_exclude_unpaid.disabled = true;
		';
	}
	echo '
		}
		if ((document.getElementById(\'print_spillere\').checked == true) || (document.getElementById(\'print_arrangorer\').checked == true)) {
	';
	if ($spill_id) {
		echo '
			document.printoutform.spiller_exclude_unpaid.disabled = true;
		';
	}
	echo '
			document.printoutform.spiller_mailpref.disabled = false;
		}
	';
	if ($spill_id) {
		echo '
		if (document.getElementById(\'print_paameldte\').checked == true) {
			document.printoutform.spiller_mailpref.disabled = false;
			document.printoutform.spiller_exclude_unpaid.disabled = false;
		}
		';
	}
	if ($spill_id) {
		echo '
		if (document.getElementById(\'print_spillroller\').checked == true) {
			document.printoutform.rolle_exclude_unpaid.disabled = false;
		} else {
			document.printoutform.rolle_exclude_unpaid.disabled = true;
		}
		';
	}
	echo '
	}
	
</script>

	<h2 align="center">'.$LANG['MISC']['printouts'].'</h2>
	<br><br>
	<form name="printoutform" action="./download.php" method="post">
	<input type="hidden" name="spill_id" value="'.$spill_id.'">
	<input type="hidden" name="txt" value="roller">
	<table cellspacing="0" cellpadding="3" border="0" align="center">
	<tr>
		<td align="center">
			<table class="nospace" border="0">
				<tr>
					<td>
						<h4 class="table">'.$LANG['MISC']['format'].'</h4>
					</td>
					<td align="left">
						<select name="format" onChange="javascript:addremove_extraoptions();">
							<option value="" class="selectname" selected="selected">'.$LANG['MISC']['select'].'</option>
							<option value="pdf">'.$LANG['MISC']['pdf'].'</option>
							<option value="text">'.$LANG['MISC']['plaintext'].'</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<h4 class="table">'.$LANG['MISC']['print'].'</h4>
					</td>
					<td align="center">
						<select name="pdf" onChange="javascript:addremove_extraoptions();">
							<option value="" class="selectname">'.$LANG['MISC']['select'].'</option>
							<option value="roller"'; if ($_GET['print'] == 'roller') { echo ' selected'; } echo '>'.$LANG['MISC']['characters'].'</option>
							<option value="labels">'.$LANG['MISC']['address_labels'].'</option>
							<option value="envelopes">'.$LANG['MISC']['envelopes'].'</option>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>

	<tbody id="label_options">
	<tr>
		<td align="left"><select name="labeltype">
				<option value="L7160"'; if (($config['paperformat'] == 'A4') || ($config['paperformat'] == 'A5')) { echo ' selected'; } echo '>L7160 (A4)</option>
				<option value="L7163">L7163 (A4)</option>
				<option value="C2160">C2160 (A4)</option>
				<option value="5160"'; if (($config['paperformat'] == 'Letter') || ($config['paperformat'] == 'Legal')) { echo ' selected'; } echo '>5160 (letter)</option>
				<option value="5161">5161 (letter)</option>
				<option value="5162">5162 (letter)</option>
				<option value="5163">5163 (letter)</option>
				<option value="5164">5164 (letter)</option>
				<option value="8600">8600 (letter)</option>
			</select> '.$LANG['MISC']['label_type'].'
		</td>
	</tr>
	</tbody>
	
	<tbody id="envelope_options">
	<tr>
		<td align="left"><select name="envelopetype">
			<option value="A4"'; if ($config['paperformat'] == 'A4') { echo ' selected'; } echo '>C4 (A4)</option>
			<option value="A5"'; if ($config['paperformat'] == 'A5') { echo ' selected'; } echo '>C5 (A5)</option>
			<option value="Letter"'; if ($config['paperformat'] == 'Letter') { echo ' selected'; } echo '>Letter</option>
			<option value="Legal"'; if ($config['paperformat'] == 'Legal') { echo ' selected'; } echo '>Legal</option>
			</select> '.$LANG['MISC']['envelope_format'].'
		</td>
	</tr>
	</tbody>

	<tbody id="spiller_options">
	<tr>
		<td align="left"><input type="radio" onClick="javascript:check_disabled();" id="print_kontakter" name="spiller_print" value="kontakter"'; if ($_GET['print'] == 'kontakter') { echo ' checked'; } echo '> '.$LANG['MISC']['contacts'].'</td>
	</tr>
	<tr>
		<td align="left"><input type="radio" onClick="javascript:check_disabled();" id="print_spillere" name="spiller_print" value="spillere"'; if (($_GET['print'] == 'spillere') || ($_GET['print'] == 'roller')) { echo ' checked'; } echo '> '.$LANG['MISC']['players'].'</td>
	</tr>
	<tr>
		<td align="left"><input type="radio" onClick="javascript:check_disabled();" id="print_arrangorer" name="spiller_print" value="arrangorer"'; if ($_GET['print'] == 'arrangorer') { echo ' checked'; } echo '> '.$LANG['MISC']['organizers'].'</td>
	</tr>
';
if ($spill_id) {
	echo '
	<tr>
		<td align="left"><input type="radio" onClick="javascript:check_disabled();" id="print_paameldte" name="spiller_print" value="paameldte"'; if (($_GET['print'] == 'paameldte') || ($_GET['print'] == 'roller')) { echo ' checked'; } echo '> '.$LANG['MISC']['all_registered_players'].' ('.$spillnavn.')</td>
	</tr>
	<tr>
		<td align="left"><input type="checkbox" name="spiller_exclude_unpaid"> '.$LANG['MISC']['exclude_unpaid_players'].'</td>
	</tr>
	';
}
echo '
	<tr>
		<td align="left"><input type="checkbox" name="spiller_mailpref" checked> '.$LANG['MISC']['only_mailpref_post_players'].'</td>
	</tr>
	</tbody>
	
	<tbody id="rolle_options">

';
if ($spill_id) {
	echo '
	<tr>
		<td align="left"><input type="radio" onClick="javascript:check_disabled();" id="print_roller" name="rolle_print" value="all" checked> '.$LANG['MISC']['all'].'</td>
	</tr>
	<tr>
		<td align="left"><input type="radio" onClick="javascript:check_disabled();" id="print_spillroller" name="rolle_print" value="spillroller" checked> '.$LANG['MISC']['all_game_characters'].'</td>
	</tr>
	<tr>
		<td align="left">
			<input type="checkbox" name="rolle_exclude_unpaid"> '.$LANG['MISC']['exclude_unpaid_characters'].'</td>
		</td>
	</tr>
	';
} else {
	echo '
	<tr>
		<td align="left"><input type="hidden" name="rolle_print" value="all"></td>
	</tr>
	';
}
echo '
	<tr>
		<td align="left"><input type="checkbox" name="rolle_mailpref"> '.$LANG['MISC']['only_mailpref_post_characters'].'</td>
	</tr>
	<tr>
		<td align="left"><input type="checkbox" name="rolle_exclude_arrangorer"> '.$LANG['MISC']['exclude_organizer_characters'].'</td>
	</tr>
	<tr>
		<td align="left"><input type="checkbox" name="rolle_exclude_unassigned"> '.$LANG['MISC']['exclude_unassigned_characters'].'</td>
	</tr>
	<tr>
		<td align="left"><input type="checkbox" name="rolle_exclude_deactivated" checked> '.$LANG['MISC']['exclude_deactivated_characters'].'</td>
	</tr>
	<tr>
		<td align="left">
			<input type="checkbox" name="full_rolle"> '.$LANG['MISC']['full_character_printout'].'</td>
		</td>
	</tr>
	<tr>
		<td align="left">
			<input type="checkbox" name="internal_print"> '.$LANG['MISC']['include_character_internals'].' ('.$LANG['MISC']['internal_document'].')</td>
		</td>
	</tr>
	</tbody>
	
</table>
<table align="center" style="margin-top: 3em;">
	<tr>
		<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
		<td><button type="submit" onClick="return validate_printoutform();">'.$LANG['MISC']['print'].'</button></td>
	</tr>
</table>
</form>
<script language="JavaScript" type="text/javascript">
	addremove_extraoptions();
</script>
';

include('footer.php');
?>