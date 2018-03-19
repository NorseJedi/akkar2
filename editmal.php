<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                editmal.php                              #
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

if ($_GET['nyttfelt'] || $_GET['editfelt']) {
	$hjelpemne = 'ok';
}
include('header.php');

echo '
	<script language="JavaScript" type="text/javascript">
		function showhide_fieldoptions() {
			hide(document.getElementById(\'default_width\'));
			hide(document.getElementById(\'default_heigth\'));
			hide(document.getElementById(\'choices\'));
			hide(document.getElementById(\'on_value\'));
			hide(document.getElementById(\'off_value\'));
			hide(document.getElementById(\'sourcefield\'));
			hide(document.getElementById(\'calculation\'));
			hide(document.getElementById(\'max_dots\'));
			hide(document.getElementById(\'helptext\'));
			document.nyttfeltform.default_width.disabled = true;
			document.nyttfeltform.default_heigth.disabled = true;
			document.nyttfeltform.choices.disabled = true;
			document.nyttfeltform.on_value.disabled = true;
			document.nyttfeltform.off_value.disabled = true;
			document.nyttfeltform.sourcefield.disabled = true;
			document.nyttfeltform.calculation.disabled = true;
			document.nyttfeltform.max_dots.disabled = true;
			document.nyttfeltform.helptext.disabled = true;
			document.nyttfeltform.mand.disabled = true;
			document.nyttfeltform.fieldtitle.disabled = false;
			switch (document.nyttfeltform.type.value) {
				case \'inline\':
					show(document.getElementById(\'default_width\'), tr_showproperty);
					show(document.getElementById(\'helptext\'), tr_showproperty);
					document.nyttfeltform.default_width.disabled = false;
					document.nyttfeltform.helptext.disabled = false;
					document.nyttfeltform.mand.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'inlinebox\':
					show(document.getElementById(\'default_width\'), tr_showproperty);
					show(document.getElementById(\'default_heigth\'), tr_showproperty);
					show(document.getElementById(\'helptext\'), tr_showproperty);
					document.nyttfeltform.default_width.disabled = false;
					document.nyttfeltform.default_heigth.disabled = false;
					document.nyttfeltform.helptext.disabled = false;
					document.nyttfeltform.mand.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'box\':
					show(document.getElementById(\'default_heigth\'), tr_showproperty);
					show(document.getElementById(\'helptext\'), tr_showproperty);
					document.nyttfeltform.default_heigth.disabled = false;
					document.nyttfeltform.helptext.disabled = false;
					document.nyttfeltform.mand.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'listmulti\':
				case \'listsingle\':
				case \'radio\':
					show(document.getElementById(\'choices\'), tr_showproperty);
					show(document.getElementById(\'helptext\'), tr_showproperty);
					document.nyttfeltform.choices.disabled = false;
					document.nyttfeltform.helptext.disabled = false;
					document.nyttfeltform.mand.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'check\':
					show(document.getElementById(\'on_value\'), tr_showproperty);
					show(document.getElementById(\'off_value\'), tr_showproperty);
					show(document.getElementById(\'helptext\'), tr_showproperty);
					document.nyttfeltform.on_value.disabled = false;
					document.nyttfeltform.off_value.disabled = false;
					document.nyttfeltform.helptext.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'calc\':
					show(document.getElementById(\'sourcefield\'), tr_showproperty);
					show(document.getElementById(\'calculation\'), tr_showproperty);
					document.nyttfeltform.sourcefield.disabled = false;
					document.nyttfeltform.calculation.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'dots\':
					show(document.getElementById(\'max_dots\'), tr_showproperty);
					show(document.getElementById(\'helptext\'), tr_showproperty);
					document.nyttfeltform.max_dots.disabled = false;
					document.nyttfeltform.helptext.disabled = false;
					document.nyttfeltform.mand.disabled = false;
					document.nyttfeltform.fieldtitle.disabled = false;
					break;
				case \'separator\':
					document.nyttfeltform.fieldtitle.disabled = true;
				case \'header\':
					break;
			}
			
		}
		function reset_form() {
			document.nyttfeltform.reset();
			showhide_fieldoptions();
		}
	</script>
';

if ($_GET['nyttfelt'] || $_GET['editfelt']) {
	if ($_GET['editfelt']) {
		$malinfo = get_malentry($_GET['editfelt'], $_GET['mal_id']);
		if (!$_GET['type']) {
			$_GET['type'] = $malinfo['type'];
		}
		if (!$_GET['fieldtitle']) {
			$_GET['fieldtitle'] = $malinfo['fieldtitle'];
		}
		$editedornytt = 'editfelt=$_GET[editfelt]';
		switch ($malinfo['type']) {
			case 'inline':
				$default_width = $malinfo['extra'];
				break;
			case 'inlinebox':
				$extras = explode(';', $malinfo['extra']);
				$default_heigth = $extras[0];
				$default_width = $extras[1];
				break;
			case 'box':
				$default_heigth = $malinfo['extra'];
				break;
			case 'radio':
			case 'listsingle':
			case 'listmulti':
				$extras = explode(';', $malinfo['extra']);
				foreach ($extras as $key=>$value) {
				 	if ($key != 0) {
						$extra .= $value.',';
					}
				}
				$choices = substr(trim($extra), 0, -1);
				break;
			case 'check':
				$extras = explode(';', $malinfo['extra']);
				$on_value = $extras[0];
				$off_value = $extras[1];
				break;
			case 'calc':
				$extra = explode(';', $malinfo['extra']);
				$sourcefield = $extra[0];
				$calculation = htmlentities($extra[1]);
				break;
			case 'dots':
				$max_dots = $malinfo['extra'];
				break;
		}
		$helptext = $malinfo['hjelp'];
	} else {
		$editedornytt = 'nyttfelt=yes';
	}
	$mal = get_malinfo($_GET['mal_id']);
	$rawfields = get_maldata($_GET['mal_id']);
	if (is_array($rawfields)) {
		foreach ($rawfields as $entry) {
			if (($entry['type'] != 'separator') && ($entry['type'] != 'header')) {
				$malfields[$entry['fieldname']] = $entry['fieldtitle'];
			}
		}
	}

	echo '
		<h3 align="center">'.$LANG['MISC']['edit_template'].'<br>'.ucwords($mal['navn']).' ('.$LANG['DBFIELD'][$mal['type']].')<br>';
		if ($_GET['nyttfelt']) {
			echo $LANG['MISC']['new_field'];
		} else {
			echo $LANG['MISC']['edit_field'];
		}
		echo '</h3>
		<br>
		<form name="nyttfeltform" action="vismal.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="mal_id" value="'.$_GET['mal_id'].'">
	';
	if ($_GET['editfelt']) {
		echo '<input type="hidden" name="editedfelt" value="'.$_GET['editfelt'].'">';
	} else {
		echo '<input type="hidden" name="nyttfelt" value="yes">';
	}
	echo '
		<table align="center" cellpadding="3" cellspacing="0">
			<tr>
				<td><strong>'.$LANG['MISC']['type'].'</strong></td>
				<td>
					<select name="type" onChange="javascript:showhide_fieldoptions();">
					<option class="selectname" value=""'; if ($_GET['nyttfelt']) { echo ' selected'; } echo '>'.$LANG['MISC']['select'].'</option>
					<option value="inline"'; if ($_GET['type'] == 'inline') { echo ' selected'; } echo '>inline</option>
					<option value="inlinebox"'; if ($_GET['type'] == 'inlinebox') { echo ' selected'; } echo '>inlinebox</option>
					<option value="box"'; if ($_GET['type'] == 'box') { echo ' selected'; } echo '>box</option>
					<option value="listsingle"'; if ($_GET['type'] == 'listsingle') { echo ' selected'; } echo '>listsingle</option>
					<option value="listmulti"'; if ($_GET['type'] == 'listmulti') { echo ' selected'; } echo '>listmulti</option>
					<option value="radio"'; if ($_GET['type'] == 'radio') { echo ' selected'; } echo '>radio</option>
					<option value="check"'; if ($_GET['type'] == 'check') { echo ' selected'; } echo '>check</option>
					<option value="calc"'; if ($_GET['type'] == 'calc') { echo ' selected'; } echo '>calc</option>
					<option value="dots"'; if ($_GET['type'] == 'dots') { echo ' selected'; } echo '>dots</option>
					<option value="header"'; if ($_GET['type'] == 'header') { echo ' selected'; } echo '>header</option>
					<option value="separator"'; if ($_GET['type'] == 'separator') { echo ' selected'; } echo '>separator</option>
					</select>
				</td>
				<td>'.hjelp_icon($LANG['MISC']['type'], $LANG['HELPTIP']['fieldtype'],'this.T_LEFT=true;this.T_OFFSETY=-200;').'</td>
			</tr>

			<tr>
				<td><strong>'.$LANG['MISC']['name'].'</strong></td>
				<td><input type="text" name="fieldtitle" value="'.$_GET['fieldtitle'].'"></td>
				<td>'.hjelp_icon($LANG['MISC']['name'], $LANG['HELPTIP']['fieldname']).'</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['mandatory'].'</strong></td>
				<td><input type="checkbox" name="mand" value="1"'; if ($malinfo['mand']) { echo ' checked'; } echo '></td>
				<td>'.hjelp_icon($LANG['MISC']['mandatory'], $LANG['HELPTIP']['mandatory_field']).'</td>
			</tr>

			<tr>
				<td><strong>'.$LANG['MISC']['internal'].'</strong></td>
				<td><input type="checkbox" name="intern" value="1"'; if ($malinfo['intern']) { echo ' checked'; } echo '></td>
				<td>'.hjelp_icon($LANG['MISC']['internal'], $LANG['HELPTIP']['internal_field']).'</td>
			</tr>

			<tr id="default_width">
				<td><strong>'.$LANG['MISC']['default_width'].'</strong></td>
				<td><input type="text" name="default_width" size="2" value="'.$default_width.'"></td>
				<td>'.hjelp_icon($LANG['MISC']['default_width'], $LANG['HELPTIP']['default_width']).'</td>
			</tr>
			<tr id="default_heigth">
				<td><strong>'.$LANG['MISC']['default_height'].'</strong></td>
				<td><input type="text" name="default_heigth" size="2" value="'.$default_heigth.'"></td>
				<td>'.hjelp_icon($LANG['MISC']['default_heigth'], $LANG['HELPTIP']['default_heigth']).'</td>
			</tr>
			<tr id="choices">
				<td><strong>'.$LANG['MISC']['choices'].'</strong></td>
				<td><input type="text" name="choices" size="30" value="'.$choices.'"></td>
				<td>'.hjelp_icon($LANG['MISC']['choices'], $LANG['HELPTIP']['field_choices']).'</td>
			</tr>
			<tr id="on_value">
				<td><strong>'.$LANG['MISC']['on_value'].'</strong></td>
				<td><input type="text" name="on_value" value="'.$on_value.'" size="15"></td>
				<td>'.hjelp_icon($LANG['MISC']['on_value'], $LANG['HELPTIP']['on_value']).'</td>
			</tr>
			<tr id="off_value">
				<td><strong>'.$LANG['MISC']['off_value'].'</strong></td>
				<td><input type="text" name="off_value" value="'.$off_value.'" size="15"></td>
				<td>'.hjelp_icon($LANG['MISC']['off_value'], $LANG['HELPTIP']['off_value']).'</td>
			</tr>
			<tr id="sourcefield">
				<td><strong>'.$LANG['MISC']['sourcefield'].'</strong></td>
				<td><select name="sourcefield">
					<option class="selectname" value="">'.$LANG['MISC']['select'].'</option>
				';
				foreach ($malfields as $key=>$value) {
					echo '
						<option value="'.$key.'"'; if ($sourcefield == trim($key)) { echo ' selected'; } echo '>'.$value.'</option>
					';
				}
				echo '
				</select>
				<td>'.hjelp_icon($LANG['MISC']['sourcefield'], $LANG['HELPTIP']['sourcefield']).'</td>
				</td>
			</tr>
			<tr id="calculation">
				<td><strong>'.$LANG['MISC']['calculation'].'</strong><br><span class="small">'.$LANG['MESSAGE']['template_x_field'].'</span></td>
				<td><input type="text" name="calculation" value="'.$calculation.'" size="30"></td>
				<td>'.hjelp_icon($LANG['MISC']['calculation'], $LANG['HELPTIP']['calculation'],'this.T_LEFT=true;this.T_OFFSETY=-300;').'</td>
			</tr>
			<tr id="max_dots">
				<td><strong>'.$LANG['MISC']['max_dots_value'].'</strong></td>
				<td><input type="text" name="max_dots" size="2" value="'.$max_dots.'"></td>
				<td>'.hjelp_icon($LANG['MISC']['max_dots_value'], $LANG['HELPTIP']['max_dots_value']).'</td>
			</tr>

			<tr id="helptext">
				<td><strong>'.$LANG['MISC']['helptext'].'</strong></td>
				<td><textarea cols="30" rows="2" maxlength="255" name="helptext">'.$helptext.'</textarea></td>
				<td>'.hjelp_icon($LANG['MISC']['helptext'], $LANG['HELPTIP']['helptext']).'</td>
			</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr
		<tr>
			<td colspan="2" align="center">
				<button type="button" onClick="javascript:window.location=\'./vismal.php?mal_id='.$_GET['mal_id'].'\';">'.$LANG['MISC']['template_info'].'</button>
				<button type="button" onClick="javascript:reset_form();">'.$LANG['MISC']['reset'].'</button>
				<button type="submit">'.$LANG['MISC']['save'].'</button>
			</td>
		</tr>
		</table>
		</form>
	';
} else {
	$mal = get_malinfo($_GET['mal_id']);

	echo '
		<h3 align="center">'.$LANG['MISC']['edit_template'].'<br>'.ucwords($mal['navn']).' ('.ucwords($mal['type']).')</h3>
		<br>
		<form name="nyttfeltform" action="vismal.php" method="post" onSubmit="javascript:convert_funky_letters(this);">
		<input type="hidden" name="editedmal" value="'.$mal['mal_id'].'">
		<table align="center">
			<tr>
				<td><strong>'.$LANG['MISC']['name'].'</strong></td>
				<td><input type="text" name="navn" value="'.$mal['navn'].'"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<button type="button" onClick="javascript:window.location=\'./vismal.php?mal_id='.$_GET['mal_id'].'\';">'.$LANG['MISC']['back'].'</button>
					<button type="reset">'.$LANG['MISC']['reset'].'</button>
					<button type="submit">'.$LANG['MISC']['save'].'</button>
				</td>
			</tr>
		</table>
		</form>
	';



}

echo '
<script language="JavaScript" type="text/javascript">
	showhide_fieldoptions();
</script>
';

include('footer.php');
?>
