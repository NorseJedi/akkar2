<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                           send_rolleforslag.php                         #
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
$rolle_id = opprett_rolleforslag();
$rolle = get_rolleforslag($rolle_id, $spill_id);
$spillinfo = get_spillinfo($spill_id);
$mal_id = $spillinfo['rollemal'];
$malinfo = get_maldata($mal_id);
$buttons = '
<table align="center">
	<tr>
		<td class="nospace"><button type="button" onClick="javascript:window.location=\''.$config['arrgruppeurl'].'\';">'.$LANG['MISC']['ok'].'</button></td>
	</tr>
</table>
';

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
	<title>'.$config['arrgruppenavn'].' AKKAR-'.$config['version'].'</title>
';

if ((browsertype() == 'ie') && (is_file('styles/'.$config['style'].'/iestyle.css')))
{
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/iestyle.css" type="text/css">';
}
elseif ((browsertype() == 'opera') && (is_file('styles/'.$config['style'].'/operastyle.css')))
{
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/operastyle.css" type="text/css">';
}
else
{
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/style.css" type="text/css">';
}
echo '
	<link rel="StyleSheet" href="styles/'.$config['style'].'/common.css" type="text/css">
	<link rel="icon" href="/images/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<table class="main" border="0" align="center">
	<tr>
		<td class="banner">
			<img src="'.$styleimages['logo'].'" alt="'.$config['arrgruppenavn'].'">
		</td>
	</tr>
	<tr>
		<td class="maincol" style="margin-left: 25px; margin-right: 25px">
		<h1 align="center">'.$spillnavn.'<br>'.$LANG['MISC']['character_suggestion'].'</h1>

		<h4 align="center">'.$LANG['MESSAGE']['character_suggestion_received'].'</h4>
		'.$buttons.'
		<br>
		<table border="0" align="center" width="60%">
';
foreach ($rolle as $fieldname => $value) {
	$value = nl2br(stripslashes($value));
	if (is_int(strpos($fieldname, 'field'))) {
		$fieldinfo = get_malentry($fieldname, $mal_id);
		$extras = explode(';',$fieldinfo['extra']);
		switch ($fieldinfo['type']) {
			case 'inline':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			case 'inlinebox':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			case 'box':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
					</tr>
					<tr>
						<td colspan="2">'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			case 'listsingle':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					if (strtolower($value) == strtolower($extras[$i])) { 
						echo ucwords(stripslashes($extras[$i])); 
					}
				}
				echo '</td>
					</tr>
				';
				break;
			case 'listmulti':
				$values = unserialize($value);
				unset($value);
				if (!is_array($values)) {
					$value = $LANG['MISC']['none'];
				} else {
					foreach ($values as $thisval) {
						$value .= stripslashes($thisval).', ';
					}
					$value = substr($value, 0, -2);
				}
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>'.$value.'</td>
					</tr>
				';
				break;
			case 'radio':
				echo '
					<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td>';
				for ($i = 1; $i < (int)$extras[0]+1; $i++) {
					if (strtolower($value) == strtolower($extras[$i])) { 
						echo ucwords(stripslashes($extras[$i])); 
					}
				}
				echo '</td>
					</tr>
				';
				break;
			case 'check':
				echo '
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td nowrap>'; if ($value != 0) { echo $extras[0]; } else { echo $extras[1]; } echo '</td>
					</tr>
				';
				break;
			case 'calc':
				$calc = get_calc_formula($rolle[$malinfo[$extras[0]][fieldname]], $extras[1]);
				@eval('\$calcresult = '.$calc.';');
				echo '
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td nowrap>'.$calcresult.'</td>
					</tr>
				';
				break;
			case 'dots':
			    echo '
					<tr>
						<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
						<td nowrap><span>
				';
				for ($i = 1; $i <= $value; $i++) {
				    echo '<img src="styles/'.$config['style'].'/dot.png">&nbsp;';
				}
				for ($i = $value; $i < $extras[0]; $i++) {
				    echo '<img src="styles/'.$config['style'].'/nodot.png">&nbsp;';
				}
				echo '
						</span></td>
					</tr>
				';
			    break;
			case 'header':
			        echo '
			                <tr>
			                        <td nowrap colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
					</tr>
				';
				break;
			case 'separator':
			        echo '
			                <tr>
			                        <td nowrap colspan="2"><hr size="2"></td>
					</tr>
				';
				break;
		}
	} else {
		switch($fieldname) {
			case 'oppdatert':
			case 'rolle_id':
			case 'locked':
			case 'bilde':
			case 'intern_info':
			case 'spill_id':
			case 'godkjent':
				break;
			case 'arrangor_id':
				$person = get_person($value);
				echo '
					<tr>
						<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
						<td nowrap>'.$person['fornavn'].' '.$person['etternavn'].'</td>';
				break;
			case 'beskrivelse1':
			case 'beskrivelse2':
			case 'beskrivelse3':
			case 'beskrivelse_gruppe':
				echo '
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
					</tr>
					<tr>
						<td colspan="2">'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
				break;
			default:
				echo '
					<tr>
						<td><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
						<td>'.nl2br(stripslashes($value)).'</td>
					</tr>
				';
		}
	}
}


echo '
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			'.$buttons.'
		</td>
	</tr>
</table>

		</td>
	</tr>
	<tr>
		<td class="bottom">

<table class="bottom" width="100%">
  <tr>
    <td class="bottom" width="33%" align="left">
		<a href="http://akkar.sourceforge.net/" target="_blank"><img src="images/akkar-powered.png"  alt="Powered by AKKAR" height="32" width="90"></a>
    </td>
    <td class="bottom" width="34%" align="center">
      <span class="tiny">Powered by <a href="http://akkar.sourceforge.net/" target="_blank">AKKAR-'.$config['version'].'</a></span>
    </td>
    <td class="bottom" width="33%" align="right">
       &nbsp;
    </td>
  </tr>
</table>
		</td>
	</tr>
</table>
</body>
</html>
';
?>
