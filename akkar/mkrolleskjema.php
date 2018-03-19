<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             mkrolleskjema.php                           #
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

$target_file = '/send_rolleforslag.php';

$spillinfo = get_spillinfo($spill_id); // Hente informasjon for spillet (vi trenger bare mal_id for rollemalen
$mal_id = $spillinfo['rollemal']; // Sette mal_id
$arrangorer = get_arrangorer();

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

// Style definitions.
function s_td($extra = "") {
	$style_td = 'vertical-align:top; padding-left:7px; padding-right:7px;';
	return 'style="'.$style_td.$extra.'"';
}

$filename = 'tmp/characterform.html';
$fp = fopen($filename, 'w');
fwrite($fp, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"/>
<title>'.$LANG['MISC']['character_suggestion'].' - '.$spillnavn.'</title>
<!-- THESE TWO JAVASCRIPT FUNCTIONS ARE USED TO REMOVE FUNKY LETTERS FROM TEXT PASTED FROM MICROSOFT WORD AND SHOULD BE LEFT ALONE -->
<script language="JavaScript" type="text/javascript">
function convert_funky_letters(form) {
	for(i = 0; i < form.length;i++){
		el=form[i];
		if ((el.type == \'text\') || (el.type == \'textarea\')) {
			el.value = str_replace(\''.chr(150).'\', \'-\', str_replace(\''.chr(148).'\', \'"\', str_replace(\''.chr(133).'\',\'...\', str_replace(\''.chr(146).'\', "\'", str_replace(\''.chr(153).'\', \'(TM)\', str_replace(\''.chr(169).'\', \'(C)\', str_replace(\''.chr(174).'\', \'(R)\', el.value)))))));
		}
	}
	return false;
}

function str_replace(match, replacement, string) {
	var result = \'\';
	rexp = new RegExp(match, \'gi\');
	result = string.replace(rexp, replacement);
	return result;
}
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
	font-size: 16pt;
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
<meta name="GENERATOR" content="AKKAR-'.$config['version'].'"/>
</head>
<body>
<h1 align="center">'.$spillnavn.'</h1><h2 align="center" style="margin-bottom: 1em;">'.$LANG['MISC']['character_suggestion'].'</h2>
<h4 align="center">'.str_replace('<mand_mark>', $mand_mark, $LANG['MESSAGE']['mandatory_field']).'</h4>
<!-- DO NOT EDIT ANYTHING BELOW -->
<form name="rolleskjema" action="'.$target_url.$target_file.'" method="post" enctype="multipart/form-data" onsubmit="javascript:convert_funky_letters(this);">
<input type="hidden" name="spill_id" value="'.$spill_id.'"/>
<table align="center" border="0">
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
	function validate_rolle() {
		if (document.getElementById(\'spiller\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['player_name'].'\');
			document.getElementById(\'spiller\').focus();
			return false;
		}
		if (document.getElementById(\'navn\').value == \'\') {
			window.alert(\''.$LANG['JSBOX']['character_name'].'\');
			document.getElementById(\'navn\').focus();
			return false;
		}
';

$tabindex = 1;
foreach ($rolle as $fieldname => $value) {
	$tabindex++;
	$value = stripslashes($value);
	if (strstr($fieldname, 'field')) {
		$value = stripslashes($value);
		$fieldinfo = $mal[$fieldname];
		$extras = explode(';',$fieldinfo['extra']);
		if (!$fieldinfo['intern']) {
			if ($fieldinfo['mand'] == 1) {
				if ($fieldinfo['type'] == 'radio') {
					$validatefunc .= 'if (';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						$validatefunc .= $ifand.'(document.getElementById(\'mal_'.$fieldname.'_'.$extras[$i].'\').checked == false)';
						$ifand = ' && ';
					}
					$validatefunc .= ') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'mal_'.$fieldname.'_'.$extras[1].'\').focus();
						return false;
					}
					';
					unset($ifand);
				} elseif ($fieldinfo['type'] == 'dots') {
					$validatefunc .= '\tif (';
					for ($i = 1; $i < $extras[0]+1; $i++) {
						$validatefunc .= $ifand.'(document.getElementById(\'mal_'.$fieldname.'_'.$i.'\').checked == false)';
						$ifand = ' && ';
					}
					$validatefunc .= ') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'mal_'.$fieldname.'_1\').focus();
						return false;
					}
					';
					unset($ifand);
				} else {
					$validatefunc .= '
					if (document.getElementById(\'mal_'.$fieldname.'\').value == \'\') {
						window.alert(\''.$fieldinfo['fieldtitle'].' '.$LANG['JSBOX']['missing'].'\');
						document.getElementById(\'mal_'.$fieldname.'\').focus();
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
							<td '.s_td().'><input tabindex="'.$tabindex++.'" type="text" size="'.$extras[0].'" id="mal_'.$fieldname.'" name="rolle['.$fieldname.']"/>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '</td>
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
									<td><textarea tabindex="'.$tabindex++.'" cols="'.$extras[1].'" rows="'.$extras[0].'" id="mal_'.$fieldname.'" name="rolle['.$fieldname.']"></textarea></td>
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
							<td '.s_td().' colspan="2" align="left" nowrap="nowrap"><strong>'.$fieldinfo['fieldtitle'].'</strong>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, ' '.$mand_mark); } fwrite($fp, '</td>
						</tr>
						<tr>
							<td '.s_td().' colspan="2"><textarea tabindex="'.$tabindex++.'" cols="75" rows="'.$extras[0].'" id="mal_'.$fieldname.'" name="rolle['.$fieldname.']"></textarea></td>
						</tr>
						<tr>
						<td '.s_td('text-align:left').'>
						<a href="javascript:document.getElementById(\'mal_'.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\'mal_'.$fieldname.'\').rows-6);"><span style="font-size:smaller">[-]</span></a>
						</td>
						<td '.s_td('text-align:right').'>
						<a href="javascript:document.getElementById(\'mal_'.$fieldname.'\').setAttribute(\'rows\',document.getElementById(\'mal_'.$fieldname.'\').rows+6);"><span style="font-size:smaller">[+]</span></a>
						</td>
						</tr>
						<tr>
							<td '.s_td().' colspan="2">&nbsp;</td>
						</tr>
					');
					break;
				case 'listsingle':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><select tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'" name="rolle['.$fieldname.']">
								<option value="" style="margin-bottom: 1em; font-style: italic;">- '.$LANG['MISC']['select'].' -</option>');
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							fwrite($fp, '<option value="'.$extras[$i].'">'.$extras[$i].'</option>');
						}
					fwrite($fp, '</select>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '
						</td>
						</tr>
					');
					break;
				case 'listmulti':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><select tabindex="'.$tabindex++.'" id="mal_'.$fieldname.'" name="'.$fieldname.'[]" size="'.(count($extras)-1).'" multiple>');
						for ($i = 1; $i < $extras[0]+1; $i++) {
							fwrite($fp, '<option value="'.$extras[$i].'">'.$extras[$i].'</option>');
						}
					fwrite($fp, '</select>'); if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); } fwrite($fp, '
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
						fwrite($fp, '<input tabindex="'.$tabindex++.'" type="radio" id="mal_'.$fieldname.'_'.$extras[$i].'" name="rolle['.$fieldname.']" value="'.$extras[$i].'"/>'.$extras[$i].' ');
					}
					if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); }
					fwrite($fp, '</td>
						</tr>
					');
					break;
				case 'check':
					fwrite($fp, '
						<tr>
							<td '.s_td().'><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							<td '.s_td().'><input tabindex="'.$tabindex++.'" value="1" id="mal_'.$fieldname.'" name="rolle['.$fieldname.']" type="checkbox"/></td>
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
						fwrite($fp, '<input id="mal_'.$fieldname.'_'.$i.'" type="radio" name="rolle['.$fieldname.']_'.$i.'" value="'.$i.'" onclick="javascript:check_dots(\''.$fieldname.'\', '.$i.', '.$extras[0].')"/>');
					}
					if ($fieldinfo['mand'] == 1) { fwrite($fp, $mand_mark); } else { fwrite($fp, '&nbsp;'); }
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
#			case 'spiller';

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
						<td '.s_td().' colspan="2">&nbsp;</td>
					</tr>
				');
				break;
			case 'spiller':
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
fwrite($fp, '
	<tr>
		<td colspan="2">&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table align="center">
			<tr>
			<td align="right"><button tabindex="'.($tabindex+2).'" type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td align="left"><button tabindex="'.($tabindex+1).'" type="submit" onclick="javascript:return validate_rolle();">'.$LANG['MISC']['submit'].'</button></td>
			</tr>
			</table>
		</td>
	</tr>
</table>
</form>
'.$validatefunc.'
</body>
</html>');

$filename = gzcompressfile($filename);
header('Content-Disposition: attachment; filename='.basename($filename));
header('Content-type: application/x-gzip');
readfile($filename);
exit();
?>
