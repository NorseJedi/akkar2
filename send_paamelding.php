<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            send_paamelding.php                          #
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
ini_set('track_errors', 'on');
$spill_id = $_POST['paamelding']['spill_id'];
include('common.php');

$spillinfo = get_spillinfo($spill_id);
if(!$spillinfo) {
        $message = '<h4>'.$LANG['MESSAGE']['registration_invalid'].' ';
	$message .= $LANG['MESSAGE']['contact_admin'].'</h4>';
        $skip_mail = true;
} else {
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
					$fields .= ''.$key.', ';
					$values .= '\''.$value.'\', ';
			}
		}
		$fields .= 'type,';
		$values .= '\'spiller\',';
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
	$paamelding = db_select('paameldinger', 'person_id=\''.$person_id.'\' && spill_id=\''.$_POST['paamelding']['spill_id'].'\'');
	if ($paamelding) {
		$message = '<h4>'.$LANG['MESSAGE']['registration_exists'].'</h4>';
		$skip_mail = true;
	} else {
		$fields = 'person_id, ';
		$values = '\''.$person_id.'\', ';
		if ($mal = get_paameldingsmal($_POST['paamelding']['spill_id'])) {
			foreach ($mal as $entry) {
				$extras = explode(';', $entry['extra']);
				switch ($entry['type']) {
					case 'check':
						if (!$_POST['paamelding'][$entry['fieldname']]) {
							$fields .= $entry['fieldname'].', ';
							$values .= '\'0\', ';
							$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td>'.nl2br(wordwrap($extras[1])).'</td></tr>';
							$mal_mailtext[] = $entry['fieldtitle'].': '.$extras[1]."\r\n\r\n";
						} else {
							$fields .= $entry['fieldname'].', ';
							$values .= '\'1\', ';
							$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td>'.nl2br(wordwrap($extras[0])).'</td></tr>';
							$mal_mailtext[] = $entry['fieldtitle'].': '.$extras[0]."\r\n\r\n";
						}
						break;
					case 'listmulti':
						$fields .= $entry['fieldname'].', ';
						$values .= '\''.sql_serialize($_POST[$entry['fieldname']]).'\', ';
						foreach ($_POST[$entry['fieldname']] as $tempval) {
							$tempreturn .= $tempval.', ';
						}
						$result_html[] = '<tr><td><strong>'.$entry['fieldtitle'].':</strong></td><td>'.nl2br(wordwrap(substr(trim($tempreturn), 0, -1))).'</td></tr>';
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
}

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
			<br><br><br><br><br><br><br><br><br><br><br><br><br><br>
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
$mail_errors = array();
function try_mail($to, $subject, $body, $extra) {
	global $php_errormsg, $mail_errors;
	if(!@mail($to, $subject, $body, $extra)) {
		$mail_errors[] = $php_errormsg;
	}
}
if ($_POST['person']['email'] && !$skip_mail) {
	try_mail($_POST['person']['fornavn'].' '.$_POST['person']['etternavn'].' <'.$_POST['person']['email'].'>', $spillnavn.' '.$LANG['MISC']['registration'], "\r\n".$LANG['MESSAGE']['registration_received_email']."\r\n\r\n".$LANG['MESSAGE']['you_sent_this_info']."\r\n\r\n".$mailtext."\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail']."\r\n".$config['arrgruppeurl']."\r\n", 'FROM: '.$config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>');
	try_mail($config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>', $spillnavn.' '.$LANG['MISC']['registration'], "\r\n".$LANG['MESSAGE']['registration_received_email']."\r\n\r\n".$LANG['MESSAGE']['you_sent_this_info']."\r\n\r\n".$mailtext."\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail']."\r\n".$config['arrgruppeurl']."\r\n", 'FROM: '.$_POST['person']['fornavn'].' '.$_POST['person']['etternavn'].' <'.$_POST['person']['email'].'>');
} elseif (!$skip_mail) {
	try_mail($config['arrgruppenavn'].' <'.$config['arrgruppemail'].'>', $spillnavn.' '.$LANG['MISC']['registration'], "\r\n".$LANG['MESSAGE']['registration_received_email']."\r\n\r\n".$LANG['MESSAGE']['you_sent_this_info']."\r\n\r\n".$mailtext."\r\n\r\n-- \r\n".$config['arrgruppenavn'].' <'.$config['arrgruppemail']."\r\n".$config['arrgruppeurl']."\r\n", 'FROM: '.$_POST['person']['fornavn'].' '.$_POST['person']['etternavn'].' <'.$config['arrgruppemail'].'>');
}
if(!empty($mail_errors)) {
	@mail($config['arrgruppemail'], "AKKAR ERROR", $implode("\r\n", $mail_errors));
}
?>
