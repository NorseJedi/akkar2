<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             konfigurasjon.php                           #
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

if ($_POST['nyconfig']) {
	save_config();
	$_SESSION['message'] = $LANG['MISC']['configuration'].' '.$LANG['MISC']['saved'];
	header('Location: ./konfigurasjon.php');
	exit();
}

include('header.php');

echo '<h2 align="center">AKKAR '.$LANG['MISC']['configuration'].'</h2>
	<form method="post" action="konfigurasjon.php" name="configform">
	<table align="center">
		<tr>
			<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
		</tr>
	</table>
	<input type="hidden" name="nyconfig" value="yes">
	<table border="0" cellpadding="3" cellspacing="0" align="center">
';

$languages = get_languages();
$styles = get_styles();
$types = array('inline','inlinebox','box','listsingle','listmulti','radio','check','calc','dots','header','separator');
$fields = get_fields($table_prefix.'personer');
$fields[] = 'annet';
$contactfields = get_fields($table_prefix.'kontakter');
$images = get_images();
$dirs = get_dirs();

echo '
	<tr>
		<td colspan="2"><h4 class="table">'.$LANG['CONFTITLE']['motd'].'</h4></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['motd'],$LANG['CONFEXPLAIN']['motd']).'</td>
	</tr>
	<tr>
		<td colspan="3" align="center" valign="top">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
				<td colspan="2" align="left" valign="top" align="center"><textarea cols="75" rows="'.get_numrows($config['motd'], 3).'" id="motd" name="motd">'.$config['motd'].'</textarea></td>
				</tr>
				<tr>
				<td align="left">
				'.inputsize_less('motd', 1).'
				</td>
				<td align="right" colspan="2">
				'.inputsize_more('motd', 1).'
				</td>
				</tr>		
			</table>
		</td>
	</tr>

	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['arrgruppenavn'].'</td>
		<td align="right" valign="top"><input type="text" size="25" value="'.$config['arrgruppenavn'].'" name="arrgruppenavn"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['arrgruppenavn'],$LANG['CONFEXPLAIN']['arrgruppenavn']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['arrgruppemail'].'</td>
		<td align="right" valign="top"><input type="text" size="25" value="'.$config['arrgruppemail'].'" name="arrgruppemail"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['arrgruppemail'],$LANG['CONFEXPLAIN']['arrgruppemail']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['arrgruppeurl'].'</td>
		<td align="right" valign="top"><input type="text" size="30" value="'.$config['arrgruppeurl'].'" name="arrgruppeurl"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['arrgruppeurl'],$LANG['CONFEXPLAIN']['arrgruppeurl']).'</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['publicforum'].'</td>
		<td align="right" valign="top"><input type="text" size="30" value="'.$config['publicforum'].'" name="publicforum"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['publicforum'],$LANG['CONFEXPLAIN']['publicforum']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['secretforum'].'</td>
		<td align="right" valign="top"><input type="text" size="30" value="'.$config['secretforum'].'" name="secretforum"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['secretforum'],$LANG['CONFEXPLAIN']['secretforum']).'</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['akkar_admin_navn'].'</td>
		<td align="right" valign="top"><input type="text" size="25" value="'.$config['akkar_admin_navn'].'" name="akkar_admin_navn"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['akkar_admin_navn'],$LANG['CONFEXPLAIN']['akkar_admin_navn']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['akkar_admin_email'].'</td>
		<td align="right" valign="top"><input type="text" size="25" value="'.$config['akkar_admin_email'].'" name="akkar_admin_email"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['akkar_admin_email'],$LANG['CONFEXPLAIN']['akkar_admin_email']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['lang'].'</td>
		<td align="right" valign="top">
			<select name="lang">
		';
		foreach ($languages as $key=>$value) {
			echo '<option value="'.$key.'"'; if ($config['lang'] == $key) { echo ' selected'; } echo '>'.$value.'</option>';
		}
		echo '
			</select>
		</td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['lang'],$LANG['CONFEXPLAIN']['lang']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['style'].'</td>
		<td align="right" valign="top"><select name="style">
		';
		foreach ($styles as $style) {
			echo '<option value="'.$style.'"'; if ($config['style'] == $style) { echo ' selected'; } echo '>'.$style.'</option>';
		}
		echo '
			</select></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['style'],$LANG['CONFEXPLAIN']['style']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['filsystembane'].'</td>
		<td align="right" valign="top"><select name="filsystembane">
		';
		foreach ($dirs as $dir) {
			echo '<option value="'.$dir.'"'; if ($config['filsystembane'] == $dir) { echo ' selected'; } echo '>'.$dir.'</option>';
		}
		echo '
			</select></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['filsystembane'],$LANG['CONFEXPLAIN']['filsystembane']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['max_tmp_age'].'</td>
		<td align="right" valign="top"><input type="text" size="6" value="'.$config['max_tmp_age'].'" name="max_tmp_age"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['max_tmp_age'],$LANG['CONFEXPLAIN']['max_tmp_age']).'</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['use_overlib_fade'].'</td>
		<td align="right" valign="top"><input type="checkbox" value="1" name="use_overlib_fade"'; if ($config['use_overlib_fade']) { echo ' checked'; } echo '></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['use_overlib_fade'],$LANG['CONFEXPLAIN']['use_overlib_fade']).'</td>
	</tr>

	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['long_dateformat'].'</td>
		<td align="right" valign="top"><input type="text" size="20" value="'.$config['long_dateformat'].'" name="long_dateformat"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['long_dateformat'],$LANG['CONFEXPLAIN']['long_dateformat']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['medium_dateformat'].'</td>
		<td align="right" valign="top"><input type="text" size="20" value="'.$config['medium_dateformat'].'" name="medium_dateformat"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['medium_dateformat'],$LANG['CONFEXPLAIN']['medium_dateformat']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['short_dateformat'].'</td>
		<td align="right" valign="top"><input type="text" size="20" value="'.$config['short_dateformat'].'" name="short_dateformat"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['short_dateformat'],$LANG['CONFEXPLAIN']['short_dateformat']).'</td>
	</tr>

	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['use_autoregion'].'</td>
		<td align="right" valign="top"><input type="checkbox" value="1" name="use_autoregion"'; if ($config['use_autoregion']) { echo ' checked'; } echo '></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['use_autoregion'],$LANG['CONFEXPLAIN']['use_autoregion']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['primary_exportformat'].'</td>
		<td align="right" valign="top"><select name="primary_exportformat">
			<option value="pdf"'; if ($config['primary_exportformat'] == 'pdf') { echo ' selected'; } echo '>PDF</option>
			<option value="rtf"'; if ($config['primary_exportformat'] == 'rtf') { echo ' selected'; } echo '>RTF</option>
			</select>
		</td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['primary_exportformat'],$LANG['CONFEXPLAIN']['primary_exportformat']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['allow_exportformat_override'].'</td>
		<td align="right" valign="top"><input type="checkbox" value="1" name="allow_exportformat_override"'; if ($config['allow_exportformat_override']) { echo ' checked'; } echo '></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['allow_exportformat_override'],$LANG['CONFEXPLAIN']['allow_exportformat_override']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['paperformat'].'</td>
		<td align="right" valign="top"><select name="paperformat">
			<option value="A4"'; if ($config['paperformat'] == 'A4') { echo ' selected'; } echo '>A4</option>
			<option value="A5"'; if ($config['paperformat'] == 'A5') { echo ' selected'; } echo '>A5</option>
			<option value="Letter"'; if ($config['paperformat'] == 'Letter') { echo ' selected'; } echo '>Letter</option>
			<option value="Legal"'; if ($config['paperformat'] == 'Legal') { echo ' selected'; } echo '>Legal</option>
		</td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['paperformat'],$LANG['CONFEXPLAIN']['paperformat']).'</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['max_login_attempts'].'</td>
		<td align="right" valign="top"><input type="text" size="1" value="'.$config['max_login_attempts'].'" name="max_login_attempts"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['max_login_attempts'],$LANG['CONFEXPLAIN']['max_login_attempts']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['send_security_warning'].'</td>
		<td align="right" valign="top"><input type="checkbox" value="1" name="send_security_warning"'; if ($config['send_security_warning']) { echo ' checked'; } echo '></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['send_security_warning'],$LANG['CONFEXPLAIN']['send_security_warning']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['autologout'].'</td>
		<td align="right" valign="top"><input type="text" size="3" value="'.$config['autologout'].'" name="autologout"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['autologout'],$LANG['CONFEXPLAIN']['autologout']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['ckprefix'].'</td>
		<td align="right" valign="top"><input type="text" size="5" value="'.$config['ckprefix'].'" name="ckprefix"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['ckprefix'],$LANG['CONFEXPLAIN']['ckprefix']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['ckexpire'].'</td>
		<td align="right" valign="top"><input type="text" size="6" value="'.$config['ckexpire'].'" name="ckexpire"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['ckexpire'],$LANG['CONFEXPLAIN']['ckexpire']).'</td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['ckdir'].'</td>
		<td align="right" valign="top"><input type="text" size="3" value="'.$config['ckdir'].'" name="ckdir"></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['ckdir'],$LANG['CONFEXPLAIN']['ckdir']).'</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td colspan="2"><h4 class="table">'.$LANG['CONFTITLE']['defaultrollemailtekst'].'</h4></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['defaultrollemailtekst'],$LANG['CONFEXPLAIN']['defaultrollemailtekst']).'</td>
	</tr>
	<tr>
		<td colspan="3" align="left" valign="top"></td>
	</tr>
	<tr>
		<td colspan="3" align="left" valign="top"><textarea cols="75" rows="'.get_numrows($config['defaultrollemailtekst'], 3).'" id="defaultrollemailtekst" name="defaultrollemailtekst">'.$config['defaultrollemailtekst'].'</textarea></td>
	</tr>
	<tr>
		<td align="left">
			'.inputsize_less('defaultrollemailtekst', 2).'
		</td>
		<td align="right" colspan="2">
			'.inputsize_more('defaultrollemailtekst', 2).'
		</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td colspan="2"><h4 class="table">'.$LANG['CONFTITLE']['defaultrollekonseptmailtekst'].'</h4></td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['defaultrollekonseptmailtekst'],$LANG['CONFEXPLAIN']['defaultrollekonseptmailtekst']).'</td>
	</tr>
	<tr>
		<td colspan="3" align="left" valign="top"></td>
	</tr>
	<tr>
		<td colspan="3" align="left" valign="top"><textarea cols="75" rows="'.get_numrows($config['defaultrollekonseptmailtekst'], 3).'" id="defaultrollekonseptmailtekst" name="defaultrollekonseptmailtekst">'.$config['defaultrollekonseptmailtekst'].'</textarea></td>
	</tr>
	<tr>
		<td align="left">
			'.inputsize_less('defaultrollekonseptmailtekst', 3).'
		</td>
		<td align="right" colspan="2">
			'.inputsize_more('defaultrollekonseptmailtekst', 3).'
		</td>
	</tr>
	<tr>
		<td colspan="3"><hr width="100%"></td>
	</tr>
	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['types_not_in_lists'].'<br><br></td>
		<td align="right" valign="top"><select name="types_not_in_lists[]" multiple>
		';
		foreach ($types as $type) {
			echo '
				<option value="'.$type.'"'; if (in_array($type, $config['types_not_in_lists'])) { echo ' selected'; } echo '>'.$type.'</option>
			';
		}
		echo '</select>
		</td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['types_not_in_lists'],$LANG['CONFEXPLAIN']['types_not_in_lists']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['fields_not_in_person_lists'].'<br><br></td>
		<td align="right" valign="top"><select name="fields_not_in_person_lists[]" multiple>
		';
		foreach ($fields as $field) {
			echo '
				<option value="'.$field.'"'; if (in_array($field, $config['fields_not_in_person_lists'])) { echo ' selected'; } echo '>'; echo ($LANG['DBFIELD'][$field]) ? $LANG['DBFIELD'][$field] : $field; echo '</option>
			';
		}
		echo '</select>
		</td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['fields_not_in_person_lists'],$LANG['CONFEXPLAIN']['fields_not_in_person_lists']).'</td>
	</tr>

	<tr>
		<td align="left" valign="top"><h4 class="table">'.$LANG['CONFTITLE']['fields_not_in_contacts_list'].'<br><br></td>
		<td align="right" valign="top"><select name="fields_not_in_contacts_list[]" multiple>
		';
		foreach ($contactfields as $field) {
			echo '
				<option value="'.$field.'"'; if (in_array($field, $config['fields_not_in_contacts_list'])) { echo ' selected'; } echo '>'; echo ($LANG['DBFIELD'][$field]) ? $LANG['DBFIELD'][$field] : $field; echo '</option>
			';
		}
		echo '</select>
		</td>
		<td>'.hjelp_icon($LANG['CONFTITLE']['fields_not_in_contacts_list'],$LANG['CONFEXPLAIN']['fields_not_in_contacts_list']).'</td>
	</tr>

	</table>
	<table align="center">
		<tr>
			<td><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td><button type="submit">'.$LANG['MISC']['save'].'</button></td>
		</tr>
	</table>
	</form>
';

include('footer.php');
?>