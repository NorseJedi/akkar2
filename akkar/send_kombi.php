<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               send_kombi.php                            #
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
$spill_id = $_POST['paamelding']['spill_id'];
$_POST['spill_id'] = $spill_id; // hack
include('common.php');

$spillinfo = get_spillinfo($spill_id);
$spillnavn = $spillinfo['navn'];
$fodt = $_POST['person']['aar'].'-'.$_POST['person']['mnd'].'-'.$_POST['person']['dag'];
$person = db_select('personer', 'fodt=\''.$fodt.'\' && kjonn=\''.$_POST['person']['kjonn'].'\' && fornavn=\''.$_POST['person']['fornavn'].'\' && etternavn=\''.$_POST['person']['etternavn'].'\'');
if (!$person) {
	$person = db_select('personer', 'fodt=\''.$fodt.'\' && fornavn=\''.$_POST['person']['fornavn'].'\' && etternavn=\''.$_POST['person']['etternavn'].'\'');
}
if (file_exists($_FILES['passfoto']['tmp_name'])) {
	$bildenavn = mkfilename($_POST['person']['fornavn'].' '.$_POST['person']['etternavn'].' '.time().'.jpg');
	$bildepath = 'images/personer/'.$bildenavn;
	$tmpbilde = getcwd().'/'.'tmp/'.$bildenavn;
	move_uploaded_file($_FILES['passfoto']['tmp_name'],$tmpbilde);
	resizeimg($tmpbilde, $bildepath, $_FILES['passfoto']['type'], '120', '150');
	$fields .= 'bilde, ';
	$values .= '\''.$bildenavn.'\', ';
}
if (!$person) {
	foreach ($_POST['person'] as $key=>$value) {
		switch ($key) {
			case 'dag':
			case 'mnd':
				break;
			case 'aar':
				$fields .= 'fodt,';
				$values .= '\''.$fodt.'\',';
				break;
			default:
				$fields .= $key.', ';
				$values .= '\''.$value.'\', ';
		}
	}
	$fields .= 'type,';
	$values .= '\'spiller\', ';
	$person_id = db_insert('personer', substr(trim($fields), 0, -1), substr(trim($values), 0, -1));
	add_mugshot($person_id, $bildenavn);
} else {
	$person_id = $person['person_id'];
	$parms = 'telefon=\''.$_POST['person']['telefon'].'\', mobil=\''.$_POST['person']['mobil'].'\', adresse=\''.$_POST['person']['adresse'].'\', postnr=\''.$_POST['person']['postnr'].'\', poststed=\''.$_POST['person']['poststed'].'\', email=\''.$_POST['person']['email'].'\', mailpref=\''.$_POST['person']['mailpref'].'\', hensyn=\''.addslashes($person['hensyn'])."\r\n\r\n".$_POST['person']['hensyn'].'\'';
	if ($bildenavn) {
		$parms .= ', bilde=\''.$bildenavn.'\'';
	}
	db_update('personer', trim($parms), 'person_id=\''.$person_id.'\' && type=\'spiller\'');
	add_mugshot($person_id, $bildenavn);
}
unset($fields, $values, $parms);
$paamelding = db_select('paameldinger', 'person_id=\''.$person_id.'\' && spill_id=\''.$spill_id.'\'');
if ($paamelding) {
	$message = '<h4>'.$LANG['MESSAGE']['registration_exists'].'</h4>';
	$skip_mail = true;
} else {
	$fields = 'person_id, ';
	$values = '\''.$person_id.'\', ';
	if ($mal = get_paameldingsmal($spill_id)) {
		foreach ($mal as $entry) {
			$extras = explode(';', $entry['extra']);
			switch ($entry['type']) {
				case 'check':
					if (!$_POST['paamelding'][$entry['fieldname']]) {
						$fields .= $entry['fieldname'].', ';
						$values .= '\'0\', ';
						$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td align="right">'.nl2br(wordwrap($extras[1])).'</td></tr>';
						$mal_mailtext[] = $entry['fieldtitle'].': '.$extras[1]."\r\n\r\n";
					} else {
						$fields .= $entry['fieldname'].', ';
						$values .= '\'1\', ';
						$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td align="right">'.nl2br(wordwrap($extras[0])).'</td></tr>';
						$mal_mailtext[] = $entry['fieldtitle'].': '.$extras[0]."\r\n\r\n";
					}
					break;
				case 'listmulti':
					$fields .= $entry['fieldname'].', ';
					$values .= '\''.sql_serialize($_POST['paamelding_'.$entry['fieldname']]).'\', ';
					foreach ($_POST['paamelding_'.$entry['fieldname']] as $tempval) {
						$tempreturn .= $tempval.', ';
					}
					$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td align="right">'.nl2br(wordwrap(substr(trim($tempreturn), 0, -1))).'</td></tr>';
					$mal_mailtext[] = $entry['fieldtitle'].': '.substr(trim($tempreturn), 0, -1)."\r\n\r\n";
					unset($tempval, $tempreturn);
					break;
				case 'box':
				case 'inlinebox':
					$fields .= $entry['fieldname'].', ';
					$values .= '\''.$_POST['paamelding'][$entry['fieldname']].'\', ';
					$result_html[] = '<tr><td colspan="2"><strong>'.$entry['fieldtitle'].':</strong></td></tr><tr><td colspan="2">'.nl2br(wordwrap($_POST['paamelding'][$entry['fieldname']])).'</td></tr>';
					$mal_mailtext[] = $entry['fieldtitle'].":\r\n".$_POST['paamelding'][$entry['fieldname']]."\r\n\r\n";
					break;
				case 'listsingle':
				case 'radio':
					$fields .= $entry['fieldname'].', ';
					$values .= '\''.$_POST['paamelding'][$entry['fieldname']].'\', ';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						if (strtolower($_POST['paamelding'][$entry['fieldname']]) == strtolower($extras[$i])) { 
							$tempval = $extras[$i]; 
						}
					}
					$result_html[] = '<tr><td align="left" nowrap><strong>'.$entry['fieldtitle'].'</strong></td><td align="right" nowrap>'.$tempval.'</td></tr>';
					$mal_mailtext[] = $entry['fieldtitle'].': '.$tempval."\r\n\r\n";
					unset($tempval);
					break;
				case 'calc':
					break;
				case 'dots':
					$fields .= $entry['fieldname'].', ';
					$values .= '\''.$_POST['paamelding'][$entry['fieldname']].'\', ';
					for ($i = 1; $i <= $_POST['paamelding'][$entry['fieldname']]; $i++) {
					    $tempval .= '<img src="styles/'.$config['style'].'/dot.png">&nbsp;';
					}
					for ($i = $value; $i < $extras[0]; $i++) {
					    $tempval .= '<img src="styles/'.$config['style'].'/nodot.png">&nbsp;';
					}
					$result_html[] = '<tr><td nowrap><strong>'.$entry['fieldtitle'].'</strong></td><td nowrap><span>'.$tempval.'</span></td></tr>';
					$mal_mailtext[] = $entry['fieldtitle'].': '.$_POST['paamelding'][$entry['fieldname']]."\r\n\r\n";
					unset($tempval);
				    break;
				case 'header':
			        $result_html[] = '<tr><td nowrap colspan="2"><h4>'.$entry['fieldtitle'].'</h4></td></tr>';
					$mal_mailtext[] = strtoupper($entry['fieldtitle'])."\r\n\r\n";
					break;
				case 'separator':
			        $result_html[] = '<tr><td nowrap colspan="2"><hr size="2"></td></tr>';
					$mal_mailtext[] = '--------------------------------------'."\r\n";
					break;
				default:
					$fields .= $entry['fieldname'].', ';
					$values .= '\''.$_POST['paamelding'][$entry['fieldname']].'\', ';
					$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td>'.nl2br(wordwrap($_POST['paamelding'][$entry['fieldname']])).'</td></tr>';
					$mal_mailtext[] = $entry['fieldtitle'].': '.$_POST['paamelding'][$entry['fieldname']]."\r\n\r\n";
					break;
			}
		}
	}
	$result_html[] = '<tr><td colspan="2"><strong>'.$LANG['DBFIELD']['annet'].':</strong></td></tr><tr><td colspan="2">'.nl2br(wordwrap($_POST['paamelding']['annet'])).'</td></tr>';
	$mal_mailtext[] = $LANG['DBFIELD']['annet'].":\r\n".$_POST['paamelding']['annet']."\r\n\r\n";
	foreach ($_POST['paamelding'] as $key=>$value) {
		if (!$mal[$key]) {
			if (is_array($value)) {
				$fields .= $key.', ';
				$values .= '\''.sql_serialize($value).'\',';
			} else {
				$fields .= $key.', ';
				$values .= '\''.$value.'\', ';
			}
		}
	}
	$paameldt = time();
	$fields .= 'paameldt, ';
	$values .= '\''.$paameldt.'\', ';
	$result = db_insert('paameldinger', substr(trim($fields), 0, -1), substr(trim($values), 0, -1));
	if (!$result) {
		$skip_mail = true;
		$message = $LANG['ERROR']['registration_error'].'<br><br>'.$LANG['MISC']['errormessage'].': INSERT REGISTRATION FAILED<br><br>'.$LANG['MESSAGE']['contact_admin'].'</h4>';
	}
}
$_POST['rolle']['spiller'] = $person_id;
$rolle_id = opprett_rolleforslag();
$rolle = get_rolleforslag($rolle_id, $spill_id);
$spillinfo = get_spillinfo($spill_id);
$mal_id = $spillinfo['rollemal'];
$malinfo = get_maldata($mal_id);

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
	<title>'.$config['arrgruppenavn'].' - AKKAR</title>
';
if ((browsertype() == 'ie') && (is_file('styles/'.$config['style'].'/iestyle.css'))) {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/iestyle.css" type="text/css">';
} elseif ((browsertype() == 'opera') && (is_file('styles/'.$config['style'].'/operastyle.css'))) {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/operastyle.css" type="text/css">';
} else {
	echo '<link rel="StyleSheet" href="styles/'.$config['style'].'/style.css" type="text/css">';
}
echo '
	<link rel="StyleSheet" href="styles/'.$config['style'].'/common.css" type="text/css">
	<link rel="icon" href="/images/favicon.png" type="image/png">
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<div align="center">
<table class="main" border="0" style="width:75%;">
	<tr>
		<td class="banner">
			<img src="'.$styleimages['logo'].'" alt="'.$config['arrgruppenavn'].'">
		</td>
	</tr>
	<tr>
		<td class="maincol">
		<h1 align="center">'.$spillnavn.'<br>'.$LANG['MISC']['registration'].'</h1>
';
		if (!$message) {
			echo '
			<br><br>
			<table width="75%" align="center">
				<tr>
					<td>'.$LANG['MESSAGE']['registration_received'].'<br><br>'.$LANG['MESSAGE']['registration_additonal_info'].'</td>
				</tr>
			</table>
			<h4 align="center">'.$LANG['MESSAGE']['you_sent_this_info'].'</h4>
			<table align="center">
			';
			foreach ($_POST['person'] as $key=>$value) {
				switch ($key) {
					case 'hensyn':
					echo '<tr><td colspan="2"><strong>'.$LANG['DBFIELD'][$key].':</strong></td></tr><tr><td colspan="2">'.nl2br(wordwrap(stripslashes($value))).'</td></tr>';
					$mailtext .= $LANG['DBFIELD'][$key].': '.$value."\r\n\r\n";
					break;
				case 'dag':
					$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
					$fodt = $_POST['person']['dag'].'. '.substr($mndliste[abs($_POST['person']['mnd'])], 0, 3).' '.$_POST['person']['aar'];
					echo '<tr><td><strong>'.$LANG['MISC']['birthdate'].':</strong></td><td colspan="2">'.$fodt.'</td></tr>';
					$mailtext .= $LANG['MISC']['birthdate'].': '.$fodt."\r\n\r\n";
				case 'mnd':
				case 'aar':
					break;
				case 'kjonn':
					echo '<tr><td><strong>'.$LANG['DBFIELD'][$key].':</strong></td><td>'.$LANG['DBFIELD'][$value].'</td></tr>';
					$mailtext .= $LANG['DBFIELD'][$key].': '.$LANG['DBFIELD'][$value]."\r\n\r\n";
					break;
				case 'mailpref':
					echo '<tr><td><strong>'.$LANG['DBFIELD'][$key].':</strong></td><td>'.$LANG['DBFIELD'][$value].'</td></tr>';
					$mailtext .= $LANG['DBFIELD'][$key].': '.$LANG['DBFIELD'][$value]."\r\n\r\n";
					break;
				default:
					echo '<tr><td><strong>'.$LANG['DBFIELD'][$key].':</strong></td><td>'.nl2br(wordwrap(stripslashes($value))).'</td></tr>';
					$mailtext .= $LANG['DBFIELD'][$key].': '.$value."\r\n\r\n";
				}
			}
			echo '<tr><td>&nbsp;</td></tr>';
			foreach ($result_html as $row) {
				echo $row;
			}
			$mailtext .= "\r\n";
			foreach ($mal_mailtext as $row) {
				$mailtext .= $row;
			}
			echo '
				</table>
					<h1 align="center">'.$LANG['MISC']['character_suggestion'].'</h1>

					<h4 align="center">'.$LANG['MESSAGE']['character_suggestion_received'].'</h4>
					<br>
					<table border="0" align="center" width="60%">
			';
			$mailtext .= "\r\n\r\n".$LANG['MISC']['character_suggestion']."\r\n\r\n";
			foreach ($rolle as $fieldname => $value) {
				$value = nl2br(stripslashes($value));
				if (is_int(strpos($fieldname, 'field'))) {
					$fieldinfo = get_malentry($fieldname, $mal_id);
					$extras = explode(';',$fieldinfo['extra']);
					switch ($fieldinfo['type']) {
						case 'inline':
							$mailtext .= $fieldinfo['fieldtitle'].': '.stripslashes($value)."\r\n\r\n";
							echo '
								<tr>
									<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
									<td>'.nl2br(stripslashes($value)).'</td>
								</tr>
							';
							break;
						case 'inlinebox':
							$mailtext .= $fieldinfo['fieldtitle'].":\r\n".stripslashes($value)."\r\n\r\n";
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
							$mailtext .= $fieldinfo['fieldtitle'].":\r\n".stripslashes($value)."\r\n\r\n";
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
									<td align="right">';
							for ($i = 1; $i < (int)$extras[0]+1; $i++) {
								if (strtolower($value) == strtolower($extras[$i])) { 
									$mailtext .= $fieldinfo['fieldtitle'].': '.stripslashes($extras[$i])."\r\n\r\n";
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
							$mailtext .= $fieldinfo['fieldtitle'].': '.stripslashes($value)."\r\n\r\n";
							echo '
								<tr>
									<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
									<td align="right">'.$value.'</td>
								</tr>
							';
							break;
						case 'radio':
							echo '
								<tr>
									<td><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
									<td align="right">';
							for ($i = 1; $i < (int)$extras[0]+1; $i++) {
								if (strtolower($value) == strtolower($extras[$i])) { 
									$mailtext .= $fieldinfo['fieldtitle'].': '.stripslashes($extras[$i])."\r\n\r\n";
									echo ucwords(stripslashes($extras[$i])); 
								}
							}
							echo '</td>
								</tr>
							';
							break;
						case 'check':
							$mailtext .= $fieldinfo['fieldtitle'].': ';
							echo '
								<tr>
									<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
									<td align="right" nowrap>'; if ($value != 0) { echo $extras[0]; $mailtext .= $extras[0]; } else { echo $extras[1]; $mailtext .= $extras[1]; } echo '</td>
								</tr>
							';
							$mailtext .= "\r\n\r\n";
							break;
						case 'calc':
							break;
						case 'dots':
							$mailtext .= $fieldinfo['fieldtitle'].': '.$value."\r\n\r\n";
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
								$mailtext .= strtoupper($fieldinfo['fieldtitle'])."\r\n\r\n";
								echo '
										<tr>
												<td nowrap colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
								</tr>
							';
							break;
						case 'separator':
								$mailtext .= '----------------------------------------'."\r\n\r\n";
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
						case 'spiller':
						case 'godkjent':
							break;
						case 'arrangor_id':
							$person = get_person($value);
							echo '
								<tr>
									<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
									<td nowrap>'.$person['fornavn'].' '.$person['etternavn'].'</td>';
							$mailtext .= $LANG['MISC']['organizer'].': '.$person['fornavn'].' '.$person['etternavn']."\r\n\r\n";
							break;
						case 'beskrivelse1':
						case 'beskrivelse2':
						case 'beskrivelse3':
						case 'beskrivelse_gruppe':
							$mailtext .= $LANG['DBFIELD'][$fieldname].":\r\n".stripslashes($value)."\r\n\r\n";
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
							$mailtext .= $LANG['DBFIELD'][$fieldname].': '.stripslashes($value)."\r\n\r\n";
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
					<td colspan="2" class="bt">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			</table>
			'.$buttons.'
			';
			if ($config['arrgruppeurl']) {
				echo '
					<div align="center">
					<button type="button" onClick="javascript:window.location=\''.$config['arrgruppeurl'].'\';">'.$LANG['MISC']['ok'].'</button>
					</div>
				';
			}
		} else {
			echo '<br><br>
			<table width="75%" align="center">
				<tr>
					<td>'.$message.'</td>
				</tr>
			</table>
			<br><br><br>
			<table align="center">
				<tr>
					<td><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
				</tr>
			</table>
			<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
			';
		}
echo '
		</td>
	</tr>
	<tr>
		<td class="bottom">

<table class="bottom" width="100%">
  <tr>
    <td class="bottom" width="33%" align="left">
      <a href="http://akkar.sourceforge.net/"><img src="images/akkar-powered.png"  alt="Powered by AKKAR" height="32" width="90"></a>
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
</div>
</body>
</html>
';
if ($_POST['person']['email'] && !$skip_mail) {
	mail($_POST['person']['fornavn'].' '.$_POST['person']['etternavn'].' <'.$_POST['person']['email'].'>', $spillnavn.' '.$LANG['MISC']['registration'], "\r\n".$LANG['MESSAGE']['registration_received_email']."\r\n\r\n".$LANG['MESSAGE']['you_sent_this_info']."\r\n\r\n".$mailtext."\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail']."\r\n".$config['arrgruppeurl']."\r\n", 'FROM: '.$config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>');
	mail($config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>', $spillnavn.' '.$LANG['MISC']['registration'], "\r\n".$LANG['MESSAGE']['registration_received_email']."\r\n\r\n".$LANG['MESSAGE']['you_sent_this_info']."\r\n\r\n".$mailtext."\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail']."\r\n".$config['arrgruppeurl']."\r\n", 'FROM: '.$_POST['person']['fornavn'].' '.$_POST['person']['etternavn'].' <'.$_POST['person']['email'].'>');
} elseif (!$skip_mail) {
	mail($config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>', $spillnavn.' '.$LANG['MISC']['registration'], "\r\n".$LANG['MESSAGE']['registration_received_email']."\r\n\r\n".$LANG['MESSAGE']['you_sent_this_info']."\r\n\r\n".$mailtext."\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail']."\r\n".$config['arrgruppeurl']."\r\n", 'FROM: '.$config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>');
}
?>
