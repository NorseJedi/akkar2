<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             mkkombiskjema.php                           #
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

$dir = dirname($_SERVER['REQUEST_URI']);
if ($dir == '/') {
	$dir = '';
}

if ($_SERVER['SSL_SESSION_ID']) {
	$target_prot = 'https';
} else {
	$target_prot = 'http';
}
if ($_SERVER['SERVER_PORT']>80){
	$target_url = $target_prot.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$dir;
} else {
	$target_url = $target_prot.'://'.$_SERVER['SERVER_NAME'].$dir;
}

$target_file = '/send_kombi.php';

for ($i = 1; $i <= 31; $i++) {
	$dager[$i] = $i;
}
$mnder = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
for ($i = date('Y'); $i >= date('Y')-99; $i--) {
	$aarliste[$i] = $i;
}

$personfelt = get_fields($table_prefix.'personer');
$paameldingsfelt = get_fields($table_prefix.'paameldinger');
$spillinfo = get_spillinfo($_GET['spill_id']);
$mal_id = $spillinfo['paameldingsmal'];
if ($mal = get_paameldingsmal($_GET['spill_id'])) {
	foreach($mal as $malfield){
		$paamelding[$malfield['fieldname']] = $mal[$malfield['fieldname']]['fieldtitle'];
	}
} else {
	$paamelding = array();
}

function s_td($extra = "") {
	$style_td = "vertical-align:top; padding-left:7px; padding-right:7px;";
	return 'style="'.$style_td.$extra.'"';
}

$filename = 'tmp/comboform.html';
$fp = fopen($filename, w);
fwrite($fp, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>'.$spillnavn.' '.$LANG['MISC']['registration'].'</title>
<!-- THESE TWO JAVASCRIPT FUNCTIONS ARE USED TO REMOVE FUNKY LETTERS FROM TEXT PASTED FROM MICROSOFT WORD AND SHOULD BE LEFT ALONE -->
<script language="javascript" type="text/javascript">
<!--
function convert_funky_letters(form) {
	for(i = 0; i < form.length;i++){
		el=form[i];
		if ((el.type == \'text\') || (el.type == \'textarea\')) {
			el.value = str_replace(\''.chr(150).'\', \'-\', str_replace(\''.chr(148).'\', \'"\', str_replace(\''.chr(133).'\',\'...\', str_replace(\''.chr(146).'\', "\'", str_replace(\''.chr(153).'\', \'(TM)\', str_replace(\''.chr(169).'\', \'(C)\', str_replace(\''.chr(174).'\', \'(R)\', el.value)))))));
		}
	}
	return true;
}

function str_replace(match, replacement, string) {
	var result = \'\';
	rexp = new RegExp(match, \'gi\');
	result = string.replace(rexp, replacement);
	return result;
}

function validate_person() {
	if (document.getElementById(\'fornavn\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['firstname'].'\');
		document.getElementById(\'fornavn\').focus();
		return false;
	}
	if (document.getElementById(\'etternavn\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['surname'].'\');
		document.getElementById(\'etternavn\').focus();
		return false;
	}
	if (document.getElementById(\'dag\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['birthdate'].'\');
		document.getElementById(\'dag\').focus();
		return false;
	}
	if (document.getElementById(\'mnd\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['birthdate'].'\');
		document.getElementById(\'mnd\').focus();
		return false;
	}
	if (document.getElementById(\'aar\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['birthdate'].'\');
		document.getElementById(\'aar\').focus();
		return false;
	}
	if (document.getElementById(\'kjonn\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['gender'].'\');
		document.getElementById(\'kjonn\').focus();
		return false;
	}
	if (document.getElementById(\'mailpref\').value == \'\') {
		window.alert(\''.$LANG['JSBOX']['mail_preference'].'\');
		document.getElementById(\'mailpref\').focus();
		return false;
	}
	if (document.getElementById(\'email\').value == \'\' && document.getElementById(\'mailpref\').value == \'email\') {
		window.alert(\''.$LANG['JSBOX']['email_pref_noaddress'].'\');
		document.getElementById(\'email\').focus();
		return false;
	}
	if (document.getElementById(\'mailpref\').value == \'post\') {
		if (document.getElementById(\'adresse\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['snailmail_pref_noaddress'].' ('.$LANG['MISC']['address'].','.$LANG['MISC']['zipcode'].','.$LANG['MISC']['region'].')\');
			document.getElementById(\'adresse\').focus();
			return false;
		}
		if (document.getElementById(\'postnr\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['snailmail_pref_noaddress'].' ('.$LANG['MISC']['address'].','.$LANG['MISC']['zipcode'].','.$LANG['MISC']['region'].')\');
			document.getElementById(\'postnr\').focus();
			return false;
		}
		if (document.getElementById(\'poststed\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['snailmail_pref_noaddress'].' ('.$LANG['MISC']['address'].','.$LANG['MISC']['zipcode'].','.$LANG['MISC']['region'].')\');
			document.getElementById(\'poststed\').focus();
			return false;
		}
	}
	return true;
}
-->
</script>
<!-- STYLES CAN (AND SHOULD) BE EDITED TO MATCH YOUR WEBSITE -->

<style title="StyleSheet" type="text/css">
body {
	color: #000000;
	font-family : Verdana, Arial, Helvetica, sans-serif;
	font-size: 8pt;
	background: #9999aa;
	padding-top: 5px;
	padding-left: 15px;
	padding-right: 15px;
	padding-bottom: 15px;
	margin-bottom: 10px;
	margin-left: 10px;
	margin-right: 10px;
	margin-top: 0px;
}
a {
	color: #444499;
	font-weight: bold;
	background-color: transparent;
}
a:link, a:visited { 
	text-decoration: none;
}
a:hover, a:active {
	text-decoration: none;
	color: #222255;
}
h6 {
	color: #222255;
	margin: 0;
	font-size: 8pt;
}

h5 {
	color: #222255;
	margin: 0;
	font-weight: bold;
	font-size: 9pt;
}

h4 {
	color: #222255;
	margin-bottom: 0;
	margin-top: 1em;
	font-size: 10pt;
}

h3 {
	color: #222255;
	margin: 0;
	font-size: 12pt;
}

h2 {
	color: #222255;
	margin: 0;
	font-size: 14pt;
}

h1 {
	color: #222255;
	margin: 0;
	font-size: 18pt;
}

p {
	margin-top: 0;
	margin-bottom: 1em;
}

select {
	font-size: 8pt;
}

input {
	font-size: 8pt;
}

textarea {
	font-size: 8pt;
}

img.foto {
	border: ridge 2px #444499;
}

button {
	font-size: 7pt;
	color: #ffffff;
	background-color: #444499;
	border: outset 1px #ffffff;
	padding-left: 0.3em;
	padding-right: 0.3em;
	font-weight: bold;
}
</style>
<meta name="GENERATOR" content="AKKAR-'.$config['version'].'">
</head>
<body onLoad="javascript:document.getElementById(\'fornavn\').focus();">
<h1 style="margin-top: 2em;margin-bottom: 1em;" align="center">'.$spillnavn.'</h1>
<h2 align="center">'.$LANG['MISC']['registration'].'</h2>
<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
<!-- DO NOT EDIT ANYTHING BELOW -->
<form name="paameldingskjema" action="'.$target_url.$target_file.'" method="post" enctype="multipart/form-data">
<input type="hidden" name="paamelding[spill_id]" value="'.$spill_id.'">
<table border="0" align="center">
	<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['firstname'].'</strong> <span style="font-size:smaller">('.$LANG['MISC']['and_middlename'].')</span></td>
			<td '.s_td().'><input type="text" tabindex="1" size="20" id="fornavn" name="person[fornavn]" value=""> '.$mand_mark.'</td>
		<td '.s_td().' rowspan="9" align="center"><img class="foto" src="'.$target_url.'/'.$styleimages['no_mugshot_m'].'" height="150" width="120" alt="">
		<br><input type="file" tabindex="12" name="passfoto" size="15">
		<br><span style="font-size:smaller">'.nl2br(wordwrap($LANG['MESSAGE']['mugshot_dim'], 30)).'</span>
			</td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['surname'].'</strong></td>
			<td '.s_td().'><input type="text" tabindex="2" size="20" id="etternavn" name="person[etternavn]" value=""> '.$mand_mark.'</td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['birthdate'].'</strong></td>
			<td '.s_td().'><select id="dag" tabindex="3" name="person[dag]"><option value="" style="margin-bottom:0.5em; font-style:italic">'.$LANG['MISC']['day'].'</option>'.print_liste($dager, $dag).'</select> <select id="mnd" tabindex="4" name="person[mnd]"><option value="" style="margin-bottom:0.5em; font-style:italic">'.$LANG['MISC']['month'].'</option>'.print_liste($mnder, $mnd).'</select> <select id="aar" tabindex="5" name="person[aar]"><option value="" style="margin-bottom:0.5em; font-style:italic">'.$LANG['MISC']['year'].'</option>'.print_liste($aarliste, $aar).'</select> '.$mand_mark.'</td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['gender'].'</strong></td>
			<td '.s_td('white-space:nowrap').'>
			<select id="kjonn" tabindex="6" name="person[kjonn]">
				<option value="" style="margin-bottom:0.5em; font-style:italic">'.$LANG['MISC']['select'].'</option>
				<option value="han">'.$LANG['MISC']['male'].'</option>
				<option value="hun">'.$LANG['MISC']['female'].'</option>
			</select> '.$mand_mark.'
			</td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['address'].'</strong></td>
			<td '.s_td().'><input type="text" tabindex="7" size="20" id="adresse" name="person[adresse]" value=""></td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['zipcode'].'/'.$LANG['MISC']['region'].'</strong></td>
			<td '.s_td().'><input type="text" tabindex="8" size="4" maxlength="255" id="postnr" name="person[postnr]" value=""> <input type="text" tabindex="9" size="15" maxlength="255" id="poststed" name="person[poststed]" value=""></td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['telephone'].'</strong></td>
			<td '.s_td().'><input type="text" tabindex="10" size="10" maxlength="255" id="telefon" name="person[telefon]" value=""></td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['cellphone'].'</strong></td>
			<td '.s_td().'><input type="text" tabindex="11" size="10" maxlength="255" id="mobil" name="person[mobil]" value=""></td>

		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['email'].'</strong></td>
			<td '.s_td().'><input type="text" tabindex="12" size="20" maxlength="255" id="email" name="person[email]" value=""></td>
		</tr>
		<tr>
			<td '.s_td().'><strong>'.$LANG['MISC']['mail_preference'].'</strong></td>
			<td '.s_td().'><select id="mailpref" tabindex="13" name="person[mailpref]">
				<option style="margin-bottom:0.5em; font-style:italic" value="">- '.$LANG['MISC']['select'].' -</option>
				<option value="email">'.$LANG['MISC']['email'].'</option>
				<option value="post">'.$LANG['MISC']['snailmail'].'</option>
				</select>
			</td>
		</tr>
	</table>
	<br>
	<table border="0" align="center">
		<tr>
			<td '.s_td().' colspan="3"><strong>'.$LANG['MISC']['special_considerations'].'</strong></td>
		</tr>
		<tr>
			<td '.s_td().' colspan="3">
			<textarea tabindex="14" id="hensyn" name="person[hensyn]" rows="5" cols="75"></textarea>
			</td>
		</tr>
		<tr>
			<td '.s_td('text-align:left').'>
				<a href="javascript:document.getElementById(\'hensyn\').setAttribute(\'rows\',document.getElementById(\'hensyn\').rows-6);"><span style="font-size:smaller">[-]</span></a>
			</td>
			<td>&nbsp;</td>
			<td '.s_td('text-align:right').'>
				<a href="javascript:document.getElementById(\'hensyn\').setAttribute(\'rows\',document.getElementById(\'hensyn\').rows+6);"><span style="font-size:smaller">[+]</span></a>
			</td>
		</tr>
		<tr>
			<td '.s_td().' colspan="3">&nbsp;</td>
		</tr>
	</table>
	<table border="0" align="center">
');

$validatefunc = '
	<script language="JavaScript" type="text/javascript">
	function check_dots(fieldname, num, max) {
		for (i = 1; i <= max; i ++) {
		    if (i <= num) {
		    	document.getElementById(\'mal_\' + fieldname + \'_\' + i).checked = true;
			} else {
		    	document.getElementById(\'mal_\' + fieldname + \'_\' + i).checked = false;
		    }
		}
	}
	function validate_paamelding() {
		if (validate_person() == false) {
			return false;
		}
';

$tabindex = 14;
foreach ($paamelding as $fieldname => $value) {
	$tabindex++;
	$value = nl2br(stripslashes($value));
	if (is_int(strpos($fieldname, 'field'))) {
		if (!$value) {
			$value = '';
		}
		$fieldinfo = $mal[$fieldname];
		if (!$fieldinfo['intern']) {
			$extras = explode(';',$fieldinfo[extra]);
			if ($fieldinfo['mand'] == 1) {
				if ($fieldinfo['type'] == 'radio') {
					$validatefunc .= 'if (';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						$validatefunc .= $ifand.'(document.getElementById(\'paameldingmal_'.$fieldname.'_'.$extras[$i].'\').checked == false)';
						$ifand = ' && ';
					}
					$validatefunc .= ') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'paameldingmal_'.$fieldname.'_'.$extras[1].'\').focus();
						return false;
					}
					';
					unset($ifand);
				} elseif ($fieldinfo['type'] == 'dots') {
					$validatefunc .= 'if (';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						$validatefunc .= $ifand.'(document.getElementById(\'paameldingmal_'.$fieldname.'_'.$i.'\').checked == false)';
						$ifand = ' && ';
					}
					$validatefunc .= ') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'paameldingmal_'.$fieldname.'_1\').focus();
						return false;
					}
					';
					unset($ifand);
				} else {
					$validatefunc .= '
					if (document.getElementById(\'mal_'.$fieldname.'\').value == \'\') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'paameldingmal_'.$fieldname.'\').focus();
						return false;
					}
					';
				}
			}
			switch ($fieldinfo['type']) {
				case 'inline':
				fwrite($fp, '
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td('text-align:right; white-space:nowrap').'><input type="text" tabindex="'.$tabindex.'" id="paameldingmal_'.$fieldname.'" name="paamelding['.$fieldname.']" value="" size="'.$extras[0].'"></td>
							<td>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'inlinebox':
					fwrite($fp, '
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td '.s_td('white-space:nowrap').'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td('text-align:right').'><textarea id="paameldingmal_'.$fieldname.'" name="paamelding['.$fieldname.']" tabindex="'.$tabindex.'" cols="'.$extras[1].'" rows="'.$extras[0].'"></textarea></td>
							<td>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'box':
					fwrite($fp, '
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').' colspan="2"><strong>'.$fieldinfo['fieldtitle'].'</strong>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } fwrite($fp, '</td>
						</tr>
						<tr>
							<td '.s_td().' colspan="2" align="center"><textarea id="paameldingmal_'.$fieldname.'" name="paamelding['.$fieldname.']" tabindex="'.$tabindex.'" cols="75" rows="'.$extras[0].'"></textarea></td>
						</tr>
						<tr>
							<td '.s_td('text-align:left').'>
								<a href="javascript:document.getElementById(\'paameldingmal_'.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\'paameldingmal_'.$fieldname.'\').rows-6);"><span style="font-size:smaller">[-]</span></a>
							</td>
							<td '.s_td('text-align:right').'>
								<a href="javascript:document.getElementById(\'paameldingmal_'.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\'paameldingmal_'.$fieldname.'\').rows+6);"><span style="font-size:smaller">[+]</span></a>
							</td>
						</tr>
					');
					break;
				case 'listsingle':
					fwrite($fp, '
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td('text-align:right; white-space:nowrap').'><select tabindex="'.$tabindex.'" id="paameldingmal_'.$fieldname.''.$fieldname.'" name="paamelding['.$fieldname.']">
								<option value="" style="margin-bottom:0.5em; font-style:italic">- '.$LANG['MISC']['select'].' -</option>');
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						fwrite($fp, '<option value="'.$extras[$i].'">'.$extras[$i].'</option>');
					}
					fwrite($fp, '</select></td>
							<td>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'listmulti':
					$values = unserialize($value);
					unset($value);
					if (!is_array($values)) {
						$value = $LANG['MISC']['none'];
					}
					fwrite($fp, '
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td('text-align:right; white-space:nowrap').'><select tabindex="'.$tabindex.'" id="paameldingmal_'.$fieldname.'" name="paamelding_'.$fieldname.'[]" multiple>');
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						fwrite($fp, '<option value="'.$extras[$i].'">'.$extras[$i].'</option>');
					}
					fwrite($fp, '</select></td>
							<td>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'radio':
					fwrite($fp, '
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td('text-align:right; white-space:nowrap').'>');
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						fwrite($fp, '<input type="radio" tabindex="'.$tabindex.'" id="paameldingmal_'.$fieldname.'_'.$extras[$i].'" name="paamelding['.$fieldname.']" value="'.$extras[$i].'">'.ucwords(stripslashes($extras[$i])));
					}
					fwrite($fp, '</td>
							<td>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'check':
					fwrite($fp, '
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td('text-align:right; white-space:nowrap').'><input value="1" tabindex="'.$tabindex.'" id="paameldingmal_'.$fieldname.'" name="paamelding['.$fieldname.']" type="checkbox"></td>
						</tr>
					');
					break;
				case 'calc':
					break;
				case 'dots':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'>
					');
					for ($i = 1; $i <= $extras[0]; $i++) {
						fwrite($fp, '<input id="paameldingmal_'.$fieldname.'_'.$i.'" type="radio" name="paamelding['.$fieldname.']_'.$i.'" value="'.$i.'" onClick="javascript:check_dots(\''.$fieldname.'\', '.$i.', '.$extras[0].')">');
					}
					if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'header':
						fwrite($fp, '
								<tr>
										<td '.s_td().' colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
						</tr>
					');
					break;
				case 'separator':
						fwrite($fp, '
								<tr>
										<td colspan="2"><hr size="2"></td>
						</tr>
					');
					break;
			}
		}
	}
}
$validatefunc .= '
		if (validate_rolle() == false) {
			return false;
		}
		return true;
	}
';
$tabindex++;
fwrite($fp, '
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td '.s_td().' colspan="2"><strong>'.$LANG['MISC']['generic_info'].'</strong></td>
		</tr>
		<tr>
			<td '.s_td().' colspan="2"><textarea id="annet" name="paamelding[annet]" tabindex="'.$tabindex.'" rows="5" cols="75"></textarea></td>
		</tr>
		<tr>
			<td '.s_td('text-align:left').'>
				<a href="javascript:document.getElementById(\'annet\').setAttribute(\'rows\',document.getElementById(\'annet\').rows-6);"><span style="font-size:smaller">[-]</span></a>
			</td>
			<td '.s_td('text-align:right').'>
				<a href="javascript:document.getElementById(\'annet\').setAttribute(\'rows\',document.getElementById(\'annet\').rows+6);"><span style="font-size:smaller">[+]</span></a>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</table>
	<h2 align="center" style="margin-bottom: 1em;">'.$LANG['MISC']['character_suggestion'].'</h2>
	<table align="center" border="0">
');


$validatefunc .= '
	function check_rolledots(fieldname, num, max) {
		for (i = 1; i <= max; i ++) {
		    if (i <= num) {
		    	document.getElementById(\'rollemal_\' + fieldname + \'_\' + i).checked = true;
			} else {
		    	document.getElementById(\'rollemal_\' + fieldname + \'_\' + i).checked = false;
		    }
		}
	}
	function validate_rolle() {
		if (document.getElementById(\'navn\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['character_name'].'\');
			document.getElementById(\'navn\').focus();
			return false;
		}
';
# Generate an array as if an empty character was pulled from the db using the appropriate template

$rolle_first['arrangor_id'] = '';
$rolle_first['spiller'] = '';
$rolle_first['navn'] = '';
$rolle_last['beskrivelse1'] = '';
$rolle_last['beskrivelse2'] = '';
$rolle_last['beskrivelse3'] = '';
$rolle_last['beskrivelse_gruppe'] = '';
if ($mal = get_rollemal($spill_id)) {
	foreach ($mal as $malfield) {
		$fields[$malfield['fieldname']] = $mal[$malfield['fieldname']]['fieldtitle'];
	}
	$rolle = array_merge($rolle_first, $fields, $rolle_last);
} else {
	$rolle = array_merge($rolle_first, $rolle_last);
}
$arrangorer = get_arrangorer();

foreach ($rolle as $fieldname => $value) {
	$tabindex++;
	$value = stripslashes($value);
	if (strstr($fieldname, 'field')) {
		$value = stripslashes($value);
		$fieldinfo = $mal[$fieldname];
		if (!$fieldinfo['intern']) {
			$extras = explode(';',$fieldinfo['extra']);
			if ($fieldinfo['mand'] == 1) {
				if ($fieldinfo['type'] == 'radio') {
					$validatefunc .= 'if (';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						$validatefunc .= $ifand.'(document.getElementById(\'rollemal_'.$fieldname.'_'.$extras[$i].'\').checked == false)';
						$ifand = ' && ';
					}
					$validatefunc .= ') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'rollemal_'.$fieldname.'_'.$extras[1].'\').focus();
						return false;
					}
					';
					unset($ifand);
				} elseif ($fieldinfo['type'] == 'dots') {
					$validatefunc .= '\tif (';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						$validatefunc .= $ifand.'(document.getElementById(\'rollemal_'.$fieldname.'_'.$i.'\').checked == false)';
						$ifand = ' && ';
					}
					$validatefunc .= ') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'rollemal_'.$fieldname.'_1\').focus();
						return false;
					}
					';
					unset($ifand);
				} else {
					$validatefunc .= '
					if (document.getElementById(\'rollemal_'.$fieldname.'\').value == \'\') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'rollemal_'.$fieldname.'\').focus();
						return false;
					}
					';
				}
			}
			switch ($fieldinfo['type']) {
				case 'inline':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><input tabindex="'.$tabindex++.'" type="text" size="'.$extras[0].'" id="rollemal_'.$fieldname.'" name="rolle['.$fieldname.']"/>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'inlinebox':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'>
								<table cellpadding="0" cellspacing="0">
									<tr>
									<td><textarea tabindex="'.$tabindex++.'" cols="'.$extras[1].'" rows="'.$extras[0].'" id="rollemal_'.$fieldname.'" name="rolle['.$fieldname.']"></textarea></td>
									<td>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
									</tr>
								</table>
							</td>
						</tr>
					');
					break;
				case 'box':
					$j++;
					fwrite($fp, '
						<tr>
							<td '.s_td('text-align:left; white-space:nowrap').' colspan="2"><strong>'.$fieldinfo['fieldtitle'].'</strong>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } fwrite($fp, '</td>
						</tr>
						<tr>
							<td '.s_td().' colspan="2"><textarea tabindex="'.$tabindex++.'" cols="75" rows="'.$extras[0].'" id="rollemal_'.$fieldname.'" name="rolle['.$fieldname.']"></textarea></td>
						</tr>
						<tr>
						<td '.s_td('text-align:left').'>
						<a href="javascript:document.getElementById(\'rollemal_'.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\'rollemal_'.$fieldname.'\').rows-6);"><span style="font-size:smaller">[-]</span></a>
						</td>
						<td '.s_td('text-align:right').'>
						<a href="javascript:document.getElementById(\'rollemal_'.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\'rollemal_'.$fieldname.'\').rows+6);"><span style="font-size:smaller">[+]</span></a>
						</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
					');
					break;
				case 'listsingle':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><select tabindex="'.$tabindex++.'" id="rollemal_'.$fieldname.'" name="rolle['.$fieldname.']">
								<option value="" style="margin-bottom: 1em; font-style: italic;">- '.$LANG['MISC']['select'].' -</option>');
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							fwrite($fp, '<option value="'.$extras[$i].'">'.$extras[$i].'</option>');
						}
					fwrite($fp, '</select>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '
						</td>
						</tr>
					');
					break;
				case 'listmulti':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><select tabindex="'.$tabindex++.'" id="rollemal_'.$fieldname.'" name="rolle_'.$fieldname.'[]" size="'.(count($extras)-1).'" multiple>');
						for ($i = 1; $i < $extras[0]+1; $i++) {
							fwrite($fp, '<option value="'.$extras[$i].'">'.$extras[$i].'</option>');
						}
					fwrite($fp, '</select>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '
						</td>
						</tr>
					');
					break;
				case 'radio':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'>
					');
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						fwrite($fp, '<input tabindex="'.$tabindex++.'" type="radio" id="rollemal_'.$fieldname.'_'.$extras[$i].'" name="rolle['.$fieldname.']" value="'.$extras[$i].'"/>'.$extras[$i].' ');
					}
					if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } else { fwrite($fp, '&nbsp;'); }
					fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'check':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><input tabindex="'.$tabindex++.'" value="1" id="rollemal_'.$fieldname.'" name="rolle['.$fieldname.']" type="checkbox"/></td>
						</tr>
					');
					break;
				case 'dots':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'>
					');
					for ($i = 1; $i <= $extras[0]; $i++) {
						fwrite($fp, '<input id="rollemal_'.$fieldname.'_'.$i.'" type="radio" name="rolle['.$fieldname.']_'.$i.'" value="'.$i.'" onclick="javascript:check_rolledots(\''.$fieldname.'\', '.$i.', '.$extras[0].')"/>');
					}
					if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } else { fwrite($fp, '&nbsp;'); }
					fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'header':
					fwrite($fp, '
								<tr>
										<td '.s_td().' colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
						</tr>
					');
					break;
				case 'separator':
						fwrite($fp, '
								<tr>
										<td colspan="2"><hr size="2"></td>
						</tr>
					');
					break;
			}
		}
	} else {
		switch($fieldname) {
			case 'oppdatert':
			case 'rolle_id':
			case 'locked':
			case 'bilde':
			case 'intern_info':
			case 'spill_id':
			case 'spiller':
				break;
			case 'arrangor_id':
				fwrite($fp,	'
					<tr>
						<td '.s_td().'><strong>'.$LANG['MISC']['organizer'].'</strong></td>
						<td '.s_td().'><select tabindex="'.$tabindex++.'" id="arrangor" name="rolle[arrangor_id]">
							<option value="" style="margin-bottom:0.5em; font-style:italic">- '.$LANG['MISC']['select'].' -</option>
				');
				foreach ($arrangorer as $arrangor) {
					fwrite($fp, '<option value="'.$arrangor['person_id'].'">'.$arrangor['fornavn'].' '.$arrangor['etternavn'].'</option>');
				}
				fwrite($fp, '
							</select>
						</td>
					</tr>
				');
				break;

			case 'beskrivelse1':
			case 'beskrivelse2':
			case 'beskrivelse3':
			case 'beskrivelse_gruppe':
				$j++;
				fwrite($fp, '
					<tr>
						<td '.s_td().' colspan="2"><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
					</tr>
					<tr>
						<td '.s_td().' colspan="2"><textarea tabindex="'.$tabindex++.'" cols="75" rows="5" id="'.$fieldname.'" name="rolle['.$fieldname.']"></textarea></td>
					</tr>
					<tr>
					<td '.s_td('text-align:left').'>
					<a href="javascript:document.getElementById(\''.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\''.$fieldname.'\').rows-6);"><span style="font-size:smaller">[-]</span></a>
					</td>
					<td '.s_td('text-align:right').'>
					<a href="javascript:document.getElementById(\''.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\''.$fieldname.'\').rows+6);"><span style="font-size:smaller">[+]</span></a>
					</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
				');
				break;
			case 'navn':
				fwrite($fp, '
					<tr>
						<td '.s_td().'><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
						<td '.s_td().'><input tabindex="'.$tabindex++.'" type="text" id="'.$fieldname.'" name="rolle['.$fieldname.']"/> '.$mand_mark.'
					</tr>
				');
				break;
			default:
				fwrite($fp, '
					<tr>
						<td '.s_td().'><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
						<td '.s_td().'><input tabindex="'.$tabindex++.'" type="text" id="'.$fieldname.'" name="rolle['.$fieldname.']"/></td>
					</tr>
				');
		}
	}
}
$validatefunc .= '
		return true;
	}
	</script>
';





fwrite($fp, $validatefunc.'
	<table align="center">
		<tr>
			<td '.s_td('text-align:right').'><button tabindex="'.($tabindex+2).'" type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td '.s_td('text-align:left').'><button tabindex="'.($tabindex+1).'" type="submit" onClick="javascript:return validate_paamelding();">'.$LANG['MISC']['submit'].'</button></td>
		</tr>
	</table>
	</form>
</body>
</html>');

fclose($fp);
$filename = gzcompressfile($filename);
header('Content-Disposition: attachment; filename='.basename($filename));
header('Content-Type: application/x-gzip');
readfile($filename);
exit();
?>
