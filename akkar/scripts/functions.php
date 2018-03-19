<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                               functions.php                             #
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

if (!defined('IN_AKKAR')) {
	exit('Access violation.');
}

# Format library string.
define('PHP_SHLIB_PREFIX', PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '');
define('SHLIB_FMT', PHP_SHLIB_PREFIX.'%s.'.PHP_SHLIB_SUFFIX);

# Check which GD version is used, and try to load if not.
if (extension_loaded('gd')) {
	if (function_exists('imagecreatetruecolor')) {
		$check = false;
		$check = @imagecreatetruecolor(1, 1);
		if ($check) {
			$gdver = 2;
			imagedestroy($check);
		} else {
			$gdver = 1;
		}
	} else {
		$gdver = 1;
	}
} else {
	if (ini_set('extension', 'gd2')) {
		$gdver = 2;
	} elseif (ini_set('extension', 'gd')) {
		$gdver = 1;
	} else {
		$gdver = 0;
	}
}

# Check if AKKAR is running on a Windows server
function is_windows() {
	return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

# Used for checking if a user has a valid session
function is_logged_in() {
	if ($_SESSION['is_logged_in']) {
		return true;
	}
	return false;
}

# Check if user is an administrator
function is_admin() {
	return $_SESSION['level'] >= 20;
}

# Check if user is a coordinator
function is_koordinator() {
	return $_SESSION['level'] >= 10;
}

# Check if user has the proper rights to edit another user
function is_modifiable($person_id) {
	if ($_SESSION['person_id'] == $person_id) {
		return true;
	} elseif (is_admin()) {
		return true;
	}
	$bruker = get_bruker($person_id);
	return $bruker['level'] < $_SESSION['level'];
}

# Function to create proper filenames from weird filenames
function mkfilename($string) {
	#	$filename = ereg_replace("[��]", "ae", ereg_replace("[��]", "oe", ereg_replace("[��]", "aa", str_replace(" ", "_", str_replace("'", "`", str_replace('"', '`', str_replace("&", "", str_replace("#", "", $string))))))));
	#	$filename = ereg_replace("[������]", "x", str_replace(" ", "_", str_replace("'", "`", str_replace('"', '`', str_replace("&", "", str_replace("#", "", $string))))));
	//$filename = str_replace("'", "`", str_replace('"', '`', str_replace('&', '', str_replace('#', '', $string))));
	$replace = array(
		"'" => "`",
		'"' => '`',
		'&' => '',
		'#' => '',
		'\\' => '',
		'/' => '',
		':' => '',
		'*' => '',
		'?' => '',
		'<' => '',
		'>' => '',
		'|' => ''
	);
	$filename = strtr($string, $replace);
	return $filename;
}

# Functions to create the links for increasing and decreasing input-field size
function inputsize_more($field, $num) {
	global $styleimages;
	$arrow = $styleimages['arrowdown'] ? '<img src="'.$styleimages['arrowdown'].'" name="arrowdown'.$num.'" height="15" width="15" alt="" onMouseover="lightup(\'arrowdown'.$num.'\')" onMouseout="turnoff(\'arrowdown'.$num.'\')">' : '[+]';
	return '<a href="javascript:document.getElementById(\''.$field.'\').setAttribute(\'rows\',document.getElementById(\''.$field.'\').rows+6);">'.$arrow.'</a>';
}

function inputsize_less($field, $num) {
	global $styleimages;
	$arrow = $styleimages['arrowup'] ? '<img src="'.$styleimages['arrowup'].'" name="arrowup'.$num.'" height="15" width="15" alt="" onMouseover="lightup(\'arrowup'.$num.'\')" onMouseout="turnoff(\'arrowup'.$num.'\')">' : '[-]';
	return '<a href="javascript:document.getElementById(\''.$field.'\').setAttribute(\'rows\',document.getElementById(\''.$field.'\').rows-6);">'.$arrow.'</a>';
}

# Function to create a generic popup using the overlib library
function popup_sheet($html, $title) {
	global $LANG, $styleimages;
	$search =  array("\r", "\n",     '\\', "'",  '"');
	$replace = array('', '', '', "\'", "\'");
	$onclick = 'javascript:return overlib(\''.str_replace($search, $replace, $html).'<br />\', WIDTH, 400, CAPTION,\''.str_replace($search, $replace, $title).'\');';
	$component = $styleimages['info'] ? '<img src="'.$styleimages['info'].'" alt="'.$LANG['MISC']['info'].'" onClick="'.$onclick.'">' : '<button type="button" onclick="'.$onclick.'">i</button>';
	return '&nbsp;'.$component.'&nbsp;';
}

# Function to create an Info-icon with a popup using the overlib library
function info_icon($title, $text) {
	global $LANG, $styleimages;
	$search =  array("\r", "\n",     '\\', "'",  '"');
	$replace = array('',   "<br />", '',   "\'", "\'");
	$width = strlen($text)*6;
	if ($width < 250) { $width = 250; } elseif ($width > 350) { $width = 350; }
	$onclick = 'javascript:return overlib(\''.str_replace($search, $replace, $text).'\', WIDTH, '.$width.', CAPTION,\''.str_replace($search, $replace, $title).'\');';
	$component = $styleimages['info'] ? '<img src="'.$styleimages['info'].'" alt="'.$LANG['MISC']['info'].'" onClick="'.$onclick.'">' : '<button type="button" onclick="'.$onclick.'">i</button>';
	return '&nbsp;'.$component.'&nbsp;';
}

# Function to create a Note-icon with a small popup using the overlib library
function small_note_icon($notat) {
	global $LANG, $styleimages;
	$width = strlen($notat['tekst'])*6;
	if ($width < 250) { $width = 250; } elseif ($width > 350) { $width = 350; }
	$person = get_person($notat['person_id']);
	$onclick = 'javascript:return overlib(\''.kal_convert($notat['tekst']).'<br><br>\', WIDTH, '.$width.', CAPTION,\''.$LANG['MISC']['note_by'].' '.$person['fornavn'].' '.$person['etternavn'].'\');';
	$link = $styleimages['note'] ? '<img src="'.$styleimages['note'].'" style="float: left;" onClick="'.$onclick.'">' : '<button type="button" onclick="'.$onclick.'">!</button>';
	return $link;
}

# Function to create a Note-icon with a normal popup using the overlib library
function note_icon($notat) {
	global $LANG, $month, $year, $styleimages;
	$width = strlen($notat['tekst'])*6;
	if ($width < 250) { $width = 250; } elseif ($width > 350) { $width = 350; }
	$person = get_person($notat['person_id']);
	$text = kal_convert($notat['tekst'])."<br><br><div align=\'center\'><button type=\'button\' onClick=javascript:window.location=\'./kalender.php?month=$month&amp;year=$year&amp;slett_notat=".$notat['notat_id'].";\'>".$LANG['MISC']['delete']."</button> <button type=\'button\' onClick=javascript:window.location=\'./editnotat.php?month=$month&amp;year=$year&amp;edit_notat=".$notat['notat_id']."\'>".$LANG['MISC']['edit']."</button></div>";
	$link = '<img src="'.$styleimages['note'].'" style="float: left;" onClick="javascript:return overlib(\''.$text.'\',WIDTH, '.$width.', CAPTION, \''.$LANG['MISC']['note_by'].' '.$person['fornavn'].' '.$person['etternavn'].'\');">'.substr(purge_smileys($notat['tekst']), 0, 15);
	if (strlen($notat['tekst']) > 15) {
		$link .= "...";
	}
	return $link;
}

# Function to create an task-defined Deadline-icon with a popup using the overlib library
function taskdeadline_icon($deadline) {
	global $LANG, $styleimages;
	$width = strlen($deadline['oppgavetekst'])*6;
	if ($width < 250) { $width = 250; } elseif ($width > 350) { $width = 350; }
	$link = "<img src=\"".$styleimages['deadline']."\" style=\"float: left\" onClick=\"javascript:return overlib('".kal_convert($deadline['oppgavetekst'])."<br><br><div align=\'center\'><button type=\'button\' onClick=\javascript:window.location=\'./editoppgave.php?vis=$vis&amp;action=rapport&amp;oppgave_id=$deadline[oppgave_id]\';>".$LANG['MISC']['complete']."</button></div>', WIDTH, $width, CAPTION,'".$LANG['MISC']['deadline'].": ".$LANG['MISC']['task']."')\">";
	return $link;
}

# Function to create an game-defined Deadline-icon with a popup using the overlib library
function gamedeadline_icon($deadline) {
	global $LANG, $styleimages;
	$width = strlen($deadline['tekst'])*6;
	$spillinfo = get_spillinfo($deadline['spill_id']);
	if ($width < 250) { $width = 250; } elseif ($width > 350) { $width = 350; }
	$link = "<img src=\"".$styleimages['deadline']."\" style=\"float: left\" onClick=\"javascript:return overlib('".kal_convert($deadline['tekst'])."<br><br>',WIDTH, $width, CAPTION,'".$LANG['MISC']['deadline'].": ".$spillinfo['navn']." (".$LANG['MISC']['game'].")')\">";
	return $link;
}

# Function to create a Help-icon with a popup using the overlib library
function hjelp_icon($title, $text, $parms = "") {
	global $LANG, $styleimages;
	$width = strlen($text)*6;
	if ($width < 250) { $width = 250; } elseif ($width > 350) { $width = 350; }
	$search =  array("\r", "\n",     '\\', "'",  '"');
	$replace = array("",   "<br />", '',   "\'", "\'");
	return "&nbsp;<img src=\"".$styleimages['help']."\" alt=\"".$LANG['MISC']['info']."\" onClick=\"javascript:return overlib('".str_replace($search, $replace, $text)."',WIDTH, $width, CAPTION,'".str_replace($search, $replace, $title)."');\"></a>&nbsp;";
}

# Get the array containing the images defined by the style used
function get_style_images() {
	global $config;
	$styleimages = array(
		'arrowdown'=>'',
		'arrowup'=>'',
		'deadline'=>'',
		'dot'=>'',
		'dot_print'=>'',
		'harrowdown'=>'',
		'harrowup'=>'',
		'help'=>'',
		'icon_audio'=>'',
		'icon_binary'=>'',
		'icon_cdup'=>'',
		'icon_dir'=>'',
		'icon_image'=>'',
		'icon_text'=>'',
		'icon_video'=>'',
		'icon_zip'=>'',
		'info'=>'',
		'logo'=>'',
		'logo_bw'=>'',
		'no_mugshot_m'=>'',
		'no_mugshot_f'=>'',
		'no_mugshot_m_print'=>'',
		'no_mugshot_f_print'=>'',
		'nodot'=>'',
		'nodot_print'=>'',
		'note'=>'',
		'symb_female'=>'',
		'symb_male'=>'',
		'symb_unknown_gender'=>'',
		'tinyarrow_down'=>'',
		'tinyarrow_up'=>''
		);
	foreach ($styleimages as $key=>$value) {
		if (is_file('styles/'.$config['style'].'/'.$key.'.png')) {
			$styleimages[$key] = 'styles/'.$config['style'].'/'.$key.'.png';
		} elseif (is_file('styles/'.$config['style'].'/'.$key.'.jpg')) {
			$styleimages[$key] = 'styles/'.$config['style'].'/'.$key.'.jpg';
		} elseif (is_file('styles/'.$config['style'].'/'.$key.'.gif')) {
			$styleimages[$key] = 'styles/'.$config['style'].'/'.$key.'.gif';
		} elseif (is_file('styles/default/'.$key.'.png')) {
			$styleimages[$key] = 'styles/default/'.$key.'.png';
		} elseif (is_file('styles/default/'.$key.'.jpg')) {
			$styleimages[$key] = 'styles/default/'.$key.'.jpg';
		} elseif (is_file('styles/default/'.$key.'.gif')) {
			$styleimages[$key] = 'styles/default/'.$key.'.gif';
		} else {
			$styleimages[$key] = false;
		}
	}
	return $styleimages;
}

# Function to create navigation-buttons. This will return a linked image if it exists,
# a HTML button if it doesn't. A non-breaking space is returned if we're drawing a printer-friendly page.
# Images are located in styles/<stylename>/<language>/ and can be either png, jpg or gif
function button($title, $destination, $event = 0, $eventaction = 0) {
	global $config, $LANG;
	if ($_GET['utskrift']) {
		return '&nbsp;';
	}
	if (is_file('styles/'.$config['style'].'/'.$config['lang'].'/'.$title.'.png')) {
		$button = 'styles/'.$config['style'].'/'.$config['lang'].'/'.$title.'.png';
	} elseif (is_file('styles/'.$config['style'].'/'.$config['lang'].'/'.$title.'.jpg')) {
		$button = 'styles/'.$config['style'].'/'.$config['lang'].'/'.$title.'.jpg';
	} elseif (is_file('styles/'.$config['style'].'/'.$config['lang'].'/'.$title.'.gif')) {
		$button = 'styles/'.$config['style'].'/'.$config['lang'].'/'.$title.'.gif';
	} elseif (is_file('styles/default/'.$config['lang'].'/'.$key.'.png')) {
		$button = 'styles/default/'.$config['lang'].'/'.$key.'.png';
	} elseif (is_file('styles/default/'.$config['lang'].'/'.$key.'.jpg')) {
		$button = 'styles/default/'.$config['lang'].'/'.$key.'.jpg';
	} else {
		$button = 'styles/default/'.$config['lang'].'/'.$key.'.gif';
	}

	if (is_file($button)) {
		$html = '<a href="'.$destination.'"';
		if ($event) {
			$html .= ' '.$event.'="'.$eventaction.'">';
		} else {
			$html .= '>';
		}
		$html .= '<img src="'.$button.'" alt="'.$LANG['MISC'][$title].'" style="border: none; padding: 0; margin: 0;" /></a>';
	} else {
		if ($event) {
			$eventstr = $event.'="'.$eventaction.'"';
		} else {
			$eventstr = 'onClick="javascript:window.location=\''.$destination.'\';"';
		}
		$html = '<button type="button" class="button" '.$eventstr.'>'.$LANG['MISC'][$title].'</button>';
		
	}
	return $html;
}

# Function to get the mugshot for a given person
function mugshot($person, $print = 0) {
	global $styleimages;
	if (!$print) {
		if ((!$person['bilde']) || !is_file('images/personer/'.$person['bilde'])) {
			if ($person['kjonn'] == 'hun') {
				return $styleimages['no_mugshot_f'];
			} else {
				return $styleimages['no_mugshot_m'];
			}
		} else {
			return 'images/personer/'.$person['bilde'];
		}
	} else {
		if ((!$person['bilde']) || !is_file('images/personer/'.$person['bilde'])) {
			if ($person['kjonn'] == 'hun') {
				return $styleimages['no_mugshot_f_print'];
			} else {
				return $styleimages['no_mugshot_m_print'];
			}
		} else {
			return 'images/personer/'.$person['bilde'];
		}
	}
}

# Function to purge smiley-strings from a text
function purge_smileys($string) {
	$smileys = get_smileys_replace();
	$string = str_replace($smileys['text'], '', $string);
	return $string;
}

# Function to replace smiley-text with graphics
function get_smileys_replace() {
	$smileys['text'] = array(
		':robot:',
		':joystick:',
		':fencers:',
		':wizard:',
		':wrn:',
		':wrn-wrn:',
		':shock:',
		':roll:',
		':rolleyes:',
		':idea:',
		':arrow:',
		':banghead:',
		':?:',
		':!:',
		':oops:',
		':blush:',
		':cool:',
		':evil:',
		':angry:',
		':nono:',
		':fy:',
		':deal:',
		':tie:',
		':knight:',
		':zzz:',
		':sove:',
		':spy:',
		':puke:',
		':tzzt:',
		':stek:',
		':hammer:',
		':cyber:',
		':knivhode:',
		':twisted:',
		':cry:',
		':lol:',
		':wink:',
		':angry:',
		'>:(',
		'>:-(',
		':x',
		':-x',
		':)',
		':-)',
		':(',
		':-(',
		';)',
		';-)',
		':P',
		':-P',
		'8)',
		'8-)',
		':D',
		':-D',
		':o',
		':-o',
		':?',
		':-?'
	);
	$smileys['images'] = array(
		'<img src=\'./images/smileys/robot.gif\' alt=\'robot.gif\'>',
		'<img src=\'./images/smileys/joystick.gif\' alt=\'joystick.gif\'>',
		'<img src=\'./images/smileys/fencers.gif\' alt=\'fencers.gif\'>',
		'<img src=\'./images/smileys/wizard.gif\' alt=\'wizard.gif\'>',
		'<img src=\'./images/smileys/wrn.gif\' alt=\'wrn.gif\'>',
		'<img src=\'./images/smileys/wrn-wrn.gif\' alt=\'wrn-wrn.gif\'>',
		'<img src=\'./images/smileys/eek.gif\' alt=\'eek.gif\'>',
		'<img src=\'./images/smileys/rolleyes.gif\' alt=\'rolleyes.gif\'>',
		'<img src=\'./images/smileys/rolleyes.gif\' alt=\'rolleyes.gif\'>',
		'<img src=\'./images/smileys/idea.gif\' alt=\'idea.gif\'>',
		'<img src=\'./images/smileys/arrow.gif\' alt=\'arrow.gif\'>',
		'<img src=\'./images/smileys/banghead.gif\' alt=\'banghead.gif\'>',
		'<img src=\'./images/smileys/question.gif\' alt=\'question.gif\'>',
		'<img src=\'./images/smileys/exclaim.gif\' alt=\'exclaim.gif\'>',
		'<img src=\'./images/smileys/redface.gif\' alt=\'redface.gif\'>',
		'<img src=\'./images/smileys/redface.gif\' alt=\'redface.gif\'>',
		'<img src=\'./images/smileys/cool.gif\' alt=\'cool.gif\'>',
		'<img src=\'./images/smileys/evil.gif\' alt=\'evil.gif\'>',
		'<img src=\'./images/smileys/angry.gif\' alt=\'angry.gif\'>',
		'<img src=\'./images/smileys/nono.gif\' alt=\'nono.gif\'>',
		'<img src=\'./images/smileys/nono.gif\' alt=\'nono.gif\'>',
		'<img src=\'./images/smileys/deal.gif\' alt=\'deal.gif\'>',
		'<img src=\'./images/smileys/tie.gif\' alt=\'tie.gif\'>',
		'<img src=\'./images/smileys/knight.gif\' alt=\'knight.gif\'>',
		'<img src=\'./images/smileys/sove.gif\' alt=\'sove.gif\'>',
		'<img src=\'./images/smileys/sove.gif\' alt=\'sove.gif\'>',
		'<img src=\'./images/smileys/spy.gif\' alt=\'spy.gif\'>',
		'<img src=\'./images/smileys/spy.gif\' alt=\'spy.gif\'>',
		'<img src=\'./images/smileys/tzzt.gif\' alt=\'tzzt.gif\'>',
		'<img src=\'./images/smileys/stek.gif\' alt=\'stek.gif\'>',
		'<img src=\'./images/smileys/hammer.gif\' alt=\'hammer.gif\'>',
		'<img src=\'./images/smileys/cyber.gif\' alt=\'cyber.gif\'>',
		'<img src=\'./images/smileys/knivhode.gif\' alt=\'knivhode.gif\'>',
		'<img src=\'./images/smileys/twisted.gif\' alt=\'twisted.gif\'>',
		'<img src=\'./images/smileys/cry.gif\' alt=\'cry.gif\'>',
		'<img src=\'./images/smileys/lol.gif\' alt=\'lol.gif\'>',
		'<img src=\'./images/smileys/wink.gif\' alt=\'wink.gif\'>',
		'<img src=\'./images/smileys/angry.gif\' alt=\'angry.gif\'>',
		'<img src=\'./images/smileys/angry.gif\' alt=\'angry.gif\'>',
		'<img src=\'./images/smileys/angry.gif\' alt=\'angry.gif\'>',
		'<img src=\'./images/smileys/mad.gif\' alt=\',mad.gif\'>',
		'<img src=\'./images/smileys/mad.gif\' alt=\'mad.gif\'>',
		'<img src=\'./images/smileys/smile.gif\' alt=\'smile.gif\'>',
		'<img src=\'./images/smileys/smile.gif\' alt=\'smile.gif\'>',
		'<img src=\'./images/smileys/sad.gif\' alt=\'sad.gif\'>',
		'<img src=\'./images/smileys/sad.gif\' alt=\'sad.gif\'>',
		'<img src=\'./images/smileys/wink.gif\' alt=\'wink.gif\'>',
		'<img src=\'./images/smileys/wink.gif\' alt=\'wink.gif\'>',
		'<img src=\'./images/smileys/razz.gif\' alt=\'razz.gif\'>',
		'<img src=\'./images/smileys/razz.gif\' alt=\'razz.gif\'>',
		'<img src=\'./images/smileys/cool.gif\' alt=\'cool.gif\'>',
		'<img src=\'./images/smileys/cool.gif\' alt=\'cool.gif\'>',
		'<img src=\'./images/smileys/mrgreen.gif\' alt=\'mrgreen.gif\'>',
		'<img src=\'./images/smileys/mrgreen.gif\' alt=\'mrgreen.gif\'>',
		'<img src=\'./images/smileys/surprised.gif\' alt=\'surprised.gif\'>',
		'<img src=\'./images/smileys/surprised.gif\' alt=\'surprised.gif\'>',
		'<img src=\'./images/smileys/confused.gif\' alt=\'wrn.confused\'>',
		'<img src=\'./images/smileys/confused.gif\' alt=\'confused.gif\'>'
	);
	return $smileys;
}

# Function to convert text in calendar events into a readable form
function kal_convert($string) {
	$smileys = get_smileys_replace();
	$string = str_replace($smileys['text'], $smileys['images'], $string);
	$string = str_replace("\'", "'", $string);
	$string = str_replace("'", "\'", $string);
	$string = str_replace('"', "\'", $string);
	$string = str_replace("\r\n", '<br>', $string);
	return $string;
}

# Function to convert most text with smileys
function customtagged_text($string) {
	$smileys = get_smileys_replace();
	$string = str_replace($smileys['text'], $smileys['images'], $string);
	$string = str_replace("\'", '"', $string);
	return $string;
}

# Function to determine browsertype, used to choose the proper stylesheets
function browsertype() {
	if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
		return 'ie';
	}
	if (strstr($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
		return 'opera';
	}
	return 'mozilla';
}

# Function to get a list of available styles
function get_styles() {
	if ($handle = opendir('styles/')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && $file != '.svn' && is_dir('styles/'.$file)) {
				$styles[] = $file;
			}
		}
	closedir($handle);
	}
	sort($styles);
	return $styles;
}

# Function to get a list of available languages
function get_languages() {
	$language = array();
	if ($handle = opendir('lang/')) {
		while (false !== ($file = readdir($handle))) {
			if (!is_dir('lang/'.$file) && (substr($file, -4, 4) == '.php') && (substr($file, -11, 11) != '_calext.php')) {
				include('lang/'.$file);
			}
		}
	closedir($handle);
	}
	return $language;
}

# Function to get a list of available images (deprecated, mostly replaced by the styles)
function get_images($dir = 'images/') {
	$internals = array(
		'favicon.png',
		'akkar-powered.gif',
		'vcss.gif',
		'valid-html401.gif'
	);
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && !is_dir($dir.$file) && exif_imagetype($dir.$file) && (!in_array($file, $internals))) {
				$images[] = $file;
			}
		}
	closedir($handle);
	}
	return $images;
}

# Function to get a list of available directories
function get_dirs() {
	$internals = array(
		'images',
		'help',
		'scripts',
		'templates',
		'conf',
		'lang',
		'styles',
		'tmp',
		'.svn'
	);
	if ($handle = opendir('./')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir($file) && (!in_array($file, $internals))) {
				$dirs[] = $file;
			}
		}
	closedir($handle);
	}
	return $dirs;
}

# Function to save an uploaded file
function lagre_fil($navn, $stored_name, $dir) {
	if (is_uploaded_file($_FILES[$navn]['tmp_name'])) {
    		move_uploaded_file($_FILES[$navn]['tmp_name'], $dir.$stored_name);
	}
	return file_exists($dir.$stored_name);
}

# Function to get a human readable size-display of filesizes (instead of showing everything in bytes)
function get_human_readable_size($size) {
	if ($size >= 1073741824) {
        return number_format(($size / 1073741824),2) . 'GB';
	} elseif ($size >= 1048576) {
		return number_format(($size / 1048576),2) . 'MB';
	} elseif ($size >= 1024) {
		return number_format(($size / 1024),2) . 'kB';
	}
	return $size . ' bytes';
}

# Function to check the amount of space in use in a given filesystem directory, and total for the filesystem
function used_space($location, $recursive = 0) {
	if (!$location or !is_dir($location)) {
		return false;
	}
	$total = 0;
	$all = opendir($location);
	while ($file = readdir($all)) {
		if (($file != 'index.php') && ($file != '.htaccess')) {
			if ($recursive) {
				if (is_dir($location.'/'.$file) and $file <> ".." and $file <> ".") {
					$total += used_space($location.'/'.$file, 1);
					unset($file);
				}
				elseif (!is_dir($location.'/'.$file)) {
					$stats = stat($location.'/'.$file);
					$total += $stats['size'];
					unset($file);
				}
			} else {
				if (!is_dir($location.'/'.$file)) {
					$stats = stat($location.'/'.$file);
					$total += $stats['size'];
					unset($file);
				}
			}
		}
	}
	closedir($all);
	unset($all);
	return $total;
}

# Function to create a filesystem directory
function save_dir($dirname, $parent) {
	global $config;
	$dirname = trim($dirname);
	$result = mkdir($config['filsystembane'].$parent.$dirname);
	if ($result) {
		$fp1 = fopen($config['filsystembane'].$parent.$dirname.'/.htaccess', w);
		fwrite($fp1, 'order deny,allow\r\ndeny from all');
		fclose($fp1);
		$fp2 = fopen($config['filsystembane'].$parent.$dirname.'/index.php', w);
		fwrite($fp2, "<?php\r\n\r\nheader('Location: ../');\r\n\r\n?>");
		fclose($fp2);
		return true;
	}
	return false;
}

# Function to get a list of filesystem directories in the current directory
function get_fs_dirs($dir) {
	global $config;
	$path = $config['filsystembane'].$dir;
	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if (($file != '.') && ($file != '..') && ($file != '.svn') && (is_dir($path.'/'.$file))) {
				$dirs[$file] = get_fileinfo($file, $dir);
				$dirs[$file]['navn'] = basename($file);
			}
		}
	closedir($handle);
	}
	return $dirs;
}

# Function to get a list of files in the current directory
function get_fs_files($dir) {
	global $config;
	$path = $config['filsystembane'].$dir;
	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if (($file != '.htaccess') && ($file != 'index.php') && (is_file($path.'/'.$file))) {
				$files[$file] = get_fileinfo($file, $dir);
				$files[$file]['navn'] = $file;
				$files[$file]['oppdatert'] = ucfirst(strftime($config['short_dateformat'], filemtime($path.'/'.$file)));
			}
		}
	closedir($handle);
	}
	if (is_array($files)) {
		ksort($files);
	}
	return $files;
}

# Build the links in the path in the filesystem
function build_pathlink($path) {
	$dirs = explode('/', $path);
	array_shift($dirs);
	array_pop($dirs);
	if (count($dirs) > 0) {
		foreach ($dirs as $dir) {
			$fullpath .= $dir.'/';
			$cd[] = '<a href="./filsystem.php?cd=/'.$fullpath.'" class="small">'.$dir.'</a>';
		}
		foreach ($cd as $dirlink) {
			$pathlink .= $dirlink.'&nbsp;/&nbsp;';
		}
	}
	return '<a href="./filsystem.php?cd=/" class="small">(root)</a>&nbsp;/&nbsp;'.$pathlink;
}

# Get the mime-type of a file based on the filename
function get_mime_type($filename) {
	include_once('mimetypes.php');
	$elements = explode('.', $filename);
	end($elements);
	$extension = strtolower(current($elements));
	if ($mimetypes[$extension]) {
		return $mimetypes[$extension];
	}
	return 'application/octet-stream';
}

# Get the entire filesystem tree
function get_fs_dirtree($dir) {
	global $config;
	$basedir = $config['filsystembane'].$dir;
	if ($handle = opendir($basedir)) {
		while (false !== ($file = readdir($handle))) {
			if (($file != '.') && ($file != '..') && ($file != '.svn') && (is_dir($basedir.'/'.$file))) {
				$dirs[] = '/'.$file;
				if ($subdirs = get_fs_dirtree($dir.'/'.$file)) {
					foreach ($subdirs as $subdir) {
						$dirs[] = '/'.$file.$subdir;
					}
				}
			}
		}
	closedir($handle);
	}
	if (is_array($dirs)) {
		return $dirs;
	}
	return false;
}


# Clean up the temporary directory
function clean_tmp_dir() {
	global $config;
	$oldtime = strtotime($config['max_tmp_age']) - time();
	if ($handle = opendir('tmp/')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && $file != 'index.php' && $file != '.htaccess' && !is_dir('tmp/'.$file)) {
				if ((time() - filemtime('tmp/'.$file)) > $oldtime) {
					unlink('tmp/'.$file);
				}
			}
		}
	}
	closedir($handle);
	return true;
}

# Function to increase execution-time and memory-size, used for some longwinded operations (like when sending all characters for a game by email)
function this_might_take_a_while() {
	ini_set('memory_limit', '64M');
	ini_set('max_execution_time', 900);
}


# Get the number of rows in a textstring (used for determining initial textfield size in forms)
function get_numrows($string, $min = 20) {
	$rows = substr_count(wordwrap($string), "\n");
	if ($rows < $min) {
		return $min;
	} else {
		return $rows+2;
	}
}

# Determine the size of an input-field based on length of content
function get_fieldsize($string, $min = 20) {
	$size = (strlen(nl2br($string))) + 1;
	if ($size < $min) {
		return $min;
	} else {
		return $size;
	}
}

# Build the code for displaying the sheet containing personalia for a player or organizer
function person_sheet($person_id, $js = 0) {
	global $LANG, $styleimages, $config, $spill_id;
	$person = get_person($person_id);
	$person['bilde'] = mugshot($person);
	
	$dato = explode('-', $person['fodt']);
	$mndliste = array(1=>$LANG['MISC']['january'], 2=>$LANG['MISC']['february'], 3=>$LANG['MISC']['march'], 4=>$LANG['MISC']['april'], 5=>$LANG['MISC']['may'], 6=>$LANG['MISC']['june'], 7=>$LANG['MISC']['july'], 8=>$LANG['MISC']['august'], 9=>$LANG['MISC']['september'], 10=>$LANG['MISC']['october'], 11=>$LANG['MISC']['november'], 12=>$LANG['MISC']['december']);
	$fodt = abs($dato[2]).'. '.$mndliste[abs($dato[1])].' '.$dato['0'];
	$buttons = '
	<table align="center">
			<tr>
				<td align="right"><button onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
	';
	if ($person['type'] == 'arrangor') {
		$buttons .= '<td align="right"><button onClick="javascript:window.location=\'arrangorer.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['organizers'].'</button></td>';
		if (is_koordinator() && !is_last_admin($person['person_id'])) {
			$buttons .= '<td align="center"><button onClick="javascript:window.location=\'./visperson.php?tilspiller='.$person['person_id'].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['convert_to_player'].'</button></td>';
		}
	} else {
		if (basename($_SERVER['PHP_SELF']) == 'vispaamelding.php') {
			$buttons .= '<td align="right"><button onClick="javascript:window.location=\'./paameldinger.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['registrations'].'</button></td>';
		} else {
			$buttons .= '<td align="right"><button onClick="javascript:window.location=\'./spillere.php\';">'.$LANG['MISC']['players'].'</button></td>';
		}
		if (is_koordinator()) {
			$buttons .= '
				<td align="center"><button onClick="javascript:window.location=\'./visperson.php?tilarrangor='.$person['person_id'].'&amp;spill_id='.$spill_id.'\';">'.$LANG['MISC']['convert_to_organizer'].'</button></td>
				<td align="center"><button onClick="javascript:confirmDelete(\''.$person['fornavn'].' '.$person['etternavn'].'\', \'./spillere.php?slett_spiller='.$person['person_id'].'&amp;spill_id='.$spill_id.'\');">'.$LANG['MISC']['delete'].'</button></td>
			';
		}
	}
	if (basename($_SERVER['PHP_SELF']) == 'vispaamelding.php') {
		$buttons .= '<td align="right"><button onClick="javascript:window.location=\'./visperson.php?person_id='.$person['person_id'].'\';">'.$LANG['MISC']['personsheet'].'</button></td>';
	}
	
	
	$buttons .='
				<td align="left"><button onClick="javascript:window.location=\'./editperson.php?person_id='.$person['person_id'].'&amp;spill_id='.$spill_id.'&amp;whereiwas='.basename($_SERVER['PHP_SELF']).'\';">'.$LANG['MISC']['edit'].'</button></td>
			</tr>
		</table>
	';
	$html = '
		<h2 align="center">'.$LANG['MISC']['personsheet'].'</h2>
		<h3 align="center">'.$person['fornavn'].' '.$person['etternavn'].'
		<br>('.$LANG['DBFIELD'][$person['type']].')</h3>
		<br>
		<table border="0" align="center" width="50%">
			<tr>
				<td rowspan="8" align="center"><img class="foto" src="'.$person['bilde'].'" height="150" width="120" alt="'.$person['fornavn'].' '.$person['etternavn'].'">
		';
		if ($js == 0) {
			$html .= '<br><button type="button" onClick="javascript:openInfowindow(\'./mugshots.php?person_id='.$person['person_id'].'\');">'.$LANG['MISC']['mugshots'].'</button>';
		} else {
			$html .= '<br /><br />';
		}
		$html .= '
				</td>
				<td><strong>'.$LANG['MISC']['birthdate'].'</strong></td>
				<td nowrap="nowrap">'.$fodt.'</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['gender'].'</strong></td>
				<td nowrap="nowrap">
		';
		if ($person['kjonn'] == 'han') {
				$fm = 'male';
		} elseif ($person['kjonn'] == 'hun') {
				$fm = 'female';
		} else {
				$fm = 'unknown_gender';
		}
		$html .= $LANG['DBFIELD'][$person['kjonn']].' <img src="'.$styleimages['symb_'.$fm].'"></td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['address'].'</strong></td>
				<td nowrap="nowrap">'.$person['adresse'].'</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['zipcode'].'/'.$LANG['MISC']['region'].'</strong></td>
				<td nowrap="nowrap">'.$person['postnr'].' '.$person['poststed'].'</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['telephone'].'</strong></td>
				<td nowrap="nowrap">'; if (!$person['telefon']) { $html .= $LANG['MISC']['none']; } else { $html .= $person['telefon']; } $html .= '</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['cellphone'].'</strong></td>
				<td nowrap="nowrap">'; if (!$person['mobil']) { $html .= $LANG['MISC']['none']; } else { $html .= $person['mobil']; } $html .= '</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['email'].'</strong></td>
				<td nowrap="nowrap">'; if ($person['email'] == '') { $html .= $LANG['MISC']['none']; } else { $html .= '<a href="mailto:'.$person['email'].'">'.$person['email'].'</a>'; } $html .= '</td>
			</tr>
			<tr>
				<td><strong>'.$LANG['MISC']['mail_preference'].'</strong></td>
				<td>'.$LANG['DBFIELD'][$person['mailpref']].'</td>
			</tr>
			<tr>
				<td colspan="3"><strong>'.$LANG['MISC']['special_considerations'].'</strong></td>
			</tr>
			<tr>
				<td colspan="3">'; if ($person['hensyn'] == '') { $html .= $LANG['MISC']['none']; } else { $html .= nl2br(stripslashes($person['hensyn'])); } $html .= '</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3"><strong>'.$LANG['MISC']['internal_notes'].'</strong></td>
			</tr>
			<tr>
				<td colspan="3">'; if ($person['intern_info'] == '') { $html .= $LANG['MISC']['none']; } else { $html .= nl2br(stripslashes($person['intern_info'])); } $html .= '</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
		</table>
	';
	if ($js == 0) {
		$html .= $buttons;
	}
	return $html;
}

# Build the code for displaying the registration-sheet for a player in a game
function registration_sheet($person_id, $spill_id, $js = 0) {
	global $LANG, $config, $styleimages;
	$person = get_person($person_id);
	$paamelding = get_paamelding($person_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['paameldingsmal'];
	$mal = get_paameldingsmal($spill_id);
	$buttons = '
		<table align="center">
			<tr>
		';
	if (is_koordinator()) {
		$buttons .= '
				<td><button onClick="javascript:confirmDelete(\''.strtolower($LANG['MISC']['this_registration']).'\', \'./paameldinger.php?slett_paamelding='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'\');">'.$LANG['MISC']['delete'].'</button></td>
		';
	}
	$buttons .= '
				<td><button onClick="javascript:window.location=\'./editpaamelding.php?person_id='.$paamelding['person_id'].'&amp;spill_id='.$paamelding['spill_id'].'\';">'.$LANG['MISC']['edit'].'</button></td>
			</tr>
		</table>
	';
	$html = '
		<br><br>
		<h2 align="center">'.$LANG['MISC']['registrationsheet'].'</h2>
		<br>
		<table border="0" align="center" width="50%">
	';
	if ($person['type'] == 'arrangor') {
		$html .= '
		</table>
			<h4 align="center">'.$LANG['MESSAGE']['organizer_no_registrations'].'</h4>
		';
	} elseif (!$paamelding) {
		$html .= '
			<tr>
				<td align="center"><h4>'.$LANG['MESSAGE']['no_registration'].'</h4></td>
			</tr>
		</table>
		';
	} else {
		foreach ($paamelding as $fieldname => $value) {
			if (is_int(strpos($fieldname, 'field'))) {
				if (!$value) {
					$value = $LANG['MISC']['none'];
				}
				$fieldinfo = get_malentry($fieldname, $mal_id);
				$extras = explode(';',$fieldinfo['extra']);
				switch ($fieldinfo['type']) {
					case 'inline':
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td align="right" nowrap>'.nl2br(stripslashes($value)).'</td>
							</tr>
						';
						break;
					case 'inlinebox':
						$html .= '
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td>'.nl2br(stripslashes($value)).'</td>
							</tr>
						';
							break;
					case 'box':
						$html .= '
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2" align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
							</tr>
							<tr>
								<td colspan="2">'.nl2br(stripslashes($value)).'</td>
							</tr>
						';
						break;
					case 'listsingle':
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td align="right" nowrap>
						';
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							if (strtolower($value) == strtolower($extras[$i])) { 
								$html .= ucwords(stripslashes($extras[$i])); 
							}
						}
						$html .= '</td>
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
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td align="right" nowrap>'.stripslashes($value).'</td>
							</tr>
						';
						break;
					case 'radio':
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td align="right" nowrap>';
						for ($i = 1; $i < (int)$extras[0]+1; $i++) {
							if (strtolower($value) == strtolower($extras[$i])) { 
								$html .= ucwords(stripslashes($extras[$i])); 
							}
						}
						$html .= '</td>
							</tr>
						';
						break;
					case 'check':
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td align="right" nowrap>'; if ($value != 0) { $html .= $extras[0]; } else { $html .= $extras[1]; } $html .= '</td>
							</tr>
						';
						break;
					case 'calc':
						$calc = get_calc_formula($paamelding[$mal[$extras[0]]['fieldname']], $extras[1]);
						@eval('\$calcresult = '.$calc.';');
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td align="right" nowrap>'.$calcresult.'</td>
							</tr>
						';
						break;
					case 'dots':
						$html .= '
							<tr>
								<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong></td>
								<td nowrap><span>
						';
						for ($i = 1; $i <= $value; $i++) {
							$html .= '<img src="'.$styleimages['dot'].'">&nbsp;';
						}
						for ($i = $value; $i < $extras[0]; $i++) {
							$html .= '<img src="'.$styleimages['nodot'].'">&nbsp;';
						}
						$html .= '
								</span></td>
							</tr>
						';
						break;
					case 'header':
							$html .= '
									<tr>
											<td nowrap colspan="2"><h4>'.$fieldinfo['fieldtitle'].'</h4></td>
							</tr>
						';
						break;
					case 'separator':
							$html .= '
									<tr>
											<td nowrap colspan="2"><hr size="2"></td>
							</tr>
						';
						break;
				}
			} else {
				switch ($fieldname) {
					case 'spill_id':
						break;
					case 'betalt':
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$LANG['DBFIELD'][$fieldname].'</strong</td>
								<td align="right" nowrap>'; if ($value == 1) { $html .= $LANG['MISC']['yes']; } else { $html .= $LANG['MISC']['no']; } $html .= '</td>
							</tr>
						';
						break;
					case 'annet':
					case 'rolle_id':
						break;
					case 'paameldt':
						$html .= '
							<tr>
								<td align="left" nowrap><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
								<td align="right" nowrap>'.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $value)).'</td>
							</tr>
						';
						break;
				}
			}
		}
		$roller = get_spiller_roller($paamelding['person_id'], $paamelding['spill_id']);
		$html .= '
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="left" nowrap><strong>'.$LANG['MISC']['assigned_character'].'</strong></td>
				<td align="right" nowrap>
		';
		if (!$roller) {
			$html .= $LANG['MISC']['none']; 
		} else {
			foreach ($roller as $rolle) {
				$html .= '<a href="./visrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$spill_id.'">'.$rolle['navn'].'</a><br>';
			}
		}
		$html .= '
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2"><strong>'.$LANG['MISC']['generic_info'].'</strong></td>
			</tr>
			<tr>
				<td colspan="2">'; if (!$paamelding['annet']) { $html .= $LANG['MISC']['none']; } else { $html .= nl2br($paamelding['annet']); } $html .= '</td>
			</tr>
		</table>
		';
		if ($js == 0) {
			$html .= $buttons;
		}
	}
	return $html;
}

# Build the code for displaying the sheet containing a character
function character_sheet($rolle_id, $spill_id) {
	global $LANG, $config, $styleimages;
	$rolle = get_rolle($rolle_id, $spill_id);
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['rollemal'];
	$malinfo = get_maldata($mal_id);
	$mal = get_rollemal($spill_id);
	if (basename($_SERVER['PHP_SELF']) == 'visrolle.php') {
	$buttons = '
	<table align="center">
		<tr>
			<td align="center">
			<button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button>
			<button type="button" onClick="javascript:window.location=\'./roller.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['characters'].'</button>
			<button type="button" onClick="javascript:return confirmDelete(\''.$rolle['navn'].'\', \'./roller.php?spill_id='.$spill_id.'&amp;slett_rolle='.$rolle_id.'\');">'.$LANG['MISC']['delete'].'</button>
	';
	if (!$rolle['status']) {
		$buttons .= '
			<button type="button" onClick="javascript:window.location=\'./editrolle.php?spill_id='.$spill_id.'&amp;deaktiviser_rolle='.$rolle_id.'\';">'.$LANG['MISC']['deactivate'].'</button>
		';
	} else {
		$buttons .= '
			<button type="button" onClick="javascript:window.location=\'./visrolle.php?reaktiviser_rolle='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['reactivate'].'</button>
		';
	}
	if (!$rolle['status']) {
		$buttons .= '
			<button type="button" onClick="javascript:window.location=\'./editrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['edit'].'</button>
		';
	}
	if (!$rolle['status']) {
		$buttons .= '
			<button type="button" onClick="javascript:window.location=\'./sendroller.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['send_character'].'</button>
		';
	}
	$buttons .= '
			</td>
		</tr>
		<tr>
			<td align="center">
			<button type="button" onClick="javascript:window.location=\'./viskjentfolk.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['acquaintances'].'</button>
			<button type="button" onClick="javascript:window.location=\'./filvedlegg.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;vedlagt=rolle\';">'.$LANG['MISC']['character_attachments'].'</button>
			<button type="button" onClick="javascript:window.location=\'./download.php?rtf=rolle&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['download'].' (.rtf)</button>
			<button type="button" onClick="javascript:window.location=\'./download.php?pdf=rolle&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['download'].' (.pdf)</button>
			<button type="button" onClick="javascript:window.location=\'./download.php?txt=rolle&amp;rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'\';">'.$LANG['MISC']['download'].' (.txt)</button>
			</td>
		</tr>
	</table>
	';
	}
	$html .= '
		<h2 align="center">'.$LANG['MISC']['charactersheet'].'</h2>
		<h3 align="center">'.stripslashes($rolle['navn']).'</h3>
	';
	if (!$rolle['status']) {
		$html .= '
			<div align="center" class="small">'.$LANG['MISC']['updated'].': '.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $rolle['oppdatert'])).'</div>
		';
	} else {
		$deaktivisert_av = get_person($rolle['status_id']);
		$html .= '
			<h4 align="center">'.$LANG['MISC']['inactive_character'].'</h4>
			<div align="center" class="small">'.$LANG['MISC']['deactivated'].' '.ucfirst(strftime($config['long_dateformat'].' (%H:%M)', $rolle['status'])).' '.$LANG['MISC']['by'].' <a href="./visperson.php?person_id='.$deaktivisert_av['person_id'].'">'.$deaktivisert_av['fornavn'].' '.$deaktivisert_av['etternavn'].'</a></div>
			<br>
			<table align="center" width="50%" class="bordered" cellspacing="0" cellpadding="3">
				<tr>
					<td class="highlight">'.$LANG['MISC']['deactivate_cause'].'</td>
					<td class="highlight" align="right"><button type="button" onClick="javascript:window.location=\'./editrolle.php?rolle_id='.$rolle['rolle_id'].'&amp;spill_id='.$rolle['spill_id'].'&amp;edit_status=yes\';">'.$LANG['MISC']['edit'].'</button></td>
				</tr>
				<tr>
					<td colspan="2">'.nl2br(stripslashes($rolle['status_tekst'])).'</td>
				</tr>
			</table>
		';
	}
	if ($js == 0) {
		$html .= '<br>'.$buttons.'<br>';
	}
	$html .= '
		<table border="0" align="center" width="70%">
	';
	if (basename($_SERVER['PHP_SELF']) == 'visrolle.php') {
		$html .= '<tr>
			<td width="50%">
				<table border="0">
		';
	}
	foreach ($rolle as $fieldname => $value) {
		if (strpos($fieldname, 'field') !== false) {
			$fieldinfo = $mal[$fieldname];
			$extras = explode(';',$fieldinfo['extra']);
			switch ($fieldinfo['type']) {
				case 'inline':
					$html .= '
						<tr>
						<td><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td>'.nl2br(stripslashes($value)).'</td>
						</tr>
					';
					break;
				case 'inlinebox':
					$html .= '
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td>'.nl2br(stripslashes($value)).'</td>
						</tr>
					';
					break;
				case 'box':
					$we_have_a_box = true;
					if (basename($_SERVER['PHP_SELF']) == 'visrolle.php') {
						if (!$done_split) {
							$html .= '
								</table>
							</td>
							<td width="50%">
								<table width="100%">
									<tr>
										<td width="50%" class="bordered">
										'.plottinfo_rolle_sheet($rolle['rolle_id'], $rolle['spill_id']).'
										</td>
										<td width="50%" class="bordered">
										'.gruppeinfo_rolle_sheet($rolle['rolle_id'], $rolle['spill_id']).'
										</td>
									</tr>
								</table>
							</td>
						</tr>
							';
						$done_split = 'yes';
						}
					}
					$html .= '
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2"><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
						</tr>
						<tr>
							<td colspan="2">'.nl2br(stripslashes($value)).'</td>
						</tr>
					';
					break;
				case 'listsingle':
					$html .= '
						<tr>
							<td><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td>';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						if (strtolower($value) == strtolower($extras[$i])) { 
							$html .= $extras[$i]; 
						}
					}
					$html .= '</td>
						</tr>
					';
					break;
				case 'listmulti':
					$values = unserialize(stripslashes($value));
					if (!is_array($values)) {
						$show = $LANG['MISC']['none'];
					} else {
						foreach ($values as $thisval) {
							$show .= $thisval.', ';
						}
						$show = substr(trim($show), 0, -1);
					}
					$html .= '
						<tr>
							<td><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td>'.$show.'</td>
						</tr>
					';
					break;
				case 'radio':
					$html .= '
						<tr>
							<td><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td>';
					for ($i = 1; $i < (int)$extras[0]+1; $i++) {
						if (strtolower($value) == strtolower($extras[$i])) { 
							$html .= ucwords(stripslashes($extras[$i])); 
						}
					}
					$html .= '</td>
						</tr>
					';
					break;
				case 'check':
					$html .= '
						<tr>
							<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td nowrap>'; if ($value != 0) { $html .= $extras[0]; } else { $html .= $extras[1]; } $html .= '</td>
						</tr>
					';
					break;
				case 'calc':
					$calc = get_calc_formula($rolle[$malinfo[$extras[0]]['fieldname']], $extras[1]);
					@eval('\$calcresult = '.$calc.';');
					$html .= '
						<tr>
							<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td nowrap>'.$calcresult.'</td>
						</tr>
					';
					break;
				case 'dots':
					$html .= '
						<tr>
							<td nowrap><strong>'.$fieldinfo['fieldtitle'].'</strong> '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</td>
							<td nowrap>
					';
					for ($i = 1; $i <= $value; $i++) {
						$html .= '<img src="'.$styleimages['dot'].'">&nbsp;';
					}
					for ($i = $value; $i < $extras[0]; $i++) {
						$html .= '<img src="'.$styleimages['nodot'].'">&nbsp;';
					}
					$html .= '</td>
						</tr>
					';
					break;
				case 'header':
						$html .= '
							<tr>
								<td nowrap colspan="2"><h4>'.$fieldinfo['fieldtitle'].' '; if ($fieldinfo['intern']) { $html .= '(<span class="red">'.$LANG['MISC']['internal'].'</span>)'; } $html .= '</h4></td>
							</tr>
					';
					break;
				case 'separator':
						$html .= '
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
				case 'spill_id':
				case 'status':
				case 'status_id':
				case 'status_tekst':
					break;
				case 'spiller_id':
					$html .= '
						<tr>
							<td><strong>'.$LANG['MISC']['player'].'</strong></td>
					';
					if (!$value) {
						$html .= '
							<td nowrap>'.$LANG['MISC']['none'].'</td>
						';
					} else {
						$person = get_person($value);
						$html .= '
								<td nowrap><a href="./vispaamelding.php?person_id='.$person['person_id'].'&amp;spill_id='.$rolle['spill_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a>
							'.popup_sheet(person_sheet($person['person_id'], 1).registration_sheet($person['person_id'], $spill_id, 1), $person['fornavn'].' '.$person['etternavn']).'</td>
						';
					}
					$html .= '
						</tr>
					';
					break;
				case 'arrangor_id':
					$html .= '
						<tr>
							<td><strong>'.$LANG['MISC']['organizer'].'</strong></td>
					';
					if (!$value) {
						$html .= '
							<td nowrap>'.$LANG['MISC']['none'].'</td>
						';
					} else {
						$person = get_person($value);
						$html .= '
								<td nowrap><a href="./visperson.php?person_id='.$person['person_id'].'">'.$person['fornavn'].' '.$person['etternavn'].'</a>
								'.popup_sheet(person_sheet($person['person_id'], 1), $person['fornavn'].' '.$person['etternavn']).'</td>
						';
					}
					$html .= '
						</tr>
					';
					break;
				case 'intern_info':
					if (basename($_SERVER['PHP_SELF']) == 'visrolle.php') {
						if (!$done_split) {
							$html .= '
								</table>
							</td>
							<td width="50%">
								<table width="100%">
									<tr>
										<td width="50%" class="bordered">
										'.plottinfo_rolle_sheet($rolle['rolle_id'], $rolle['spill_id']).'
										</td>
										<td width="50%" class="bordered">
										'.gruppeinfo_rolle_sheet($rolle['rolle_id'], $rolle['spill_id']).'
										</td>
									</tr>
								</table>
							</td>
						</tr>
							';
						$done_split = 'yes';
						}
					}
				case 'beskrivelse1':
				case 'beskrivelse2':
				case 'beskrivelse3':
				case 'beskrivelse_gruppe':
					$html .= '
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
					$html .= '
						<tr>
							<td><strong>'.$LANG['DBFIELD'][$fieldname].'</strong></td>
							<td>'.nl2br(stripslashes($value)).'</td>
						</tr>
					';
			}
		}
	}
	
	$html .= '
		<tr>
			<td colspan="2" class="bt">&nbsp;</td>
		</tr>
	';
	
	if ($grupper = get_rolle_grupper($rolle['rolle_id'], $rolle['spill_id'])) {
		foreach ($grupper as $gruppe) {
			if ($gruppe['medlemsinfo']) {
			$html .= '
				<tr>
					<td colspan="2"><strong>'.str_replace('<groupname>', '<a href="./visgruppe.php?gruppe_id='.$gruppe['gruppe_id'].'&amp;spill_id='.$gruppe['spill_id'].'">'.$gruppe['navn'].'</a>', $LANG['MISC']['info_from_group_membership']).'</strong></td>
				</tr>
				<tr>
					<td colspan="2">'.nl2br($gruppe['medlemsinfo']).'</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			';
			}
		}
	}
	if ($js == 0) {
	$html .= '
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					'.$buttons.'
				</td>
			</tr>
	</table>
	';
	}
	return $html;
}

# Print a form-list based on a supplied array and default value
function print_liste($liste, $valg) {
	$string = '';
	foreach ($liste as $key=>$value) {
		$string .= '<option value="'.$key.'"'; if ($key == $valg) { $string .= ' selected'; } $string .= '>'.$value.'</option>'."\r\n";
	}
	return $string;
}

# Build the code for displaying the plot-related info for a character
function plottinfo_rolle_sheet($rolle_id, $spill_id) {
	global $LANG;
	$html .= '
		<table width="100%">
			<tr>
				<td align="center"><h4 class="table">'.$LANG['MISC']['plot_relations'].'</h4></td>
			</tr>
	';
	if (!$rolleplott = get_rolle_plott($rolle_id, $spill_id)) {
		$html .= '
			<tr>
				<td>'.$LANG['MISC']['no_plots'].'</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center">
					<button onClick="javascript:window.location=\'./plott.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['plots'].'</button>
				</td>
			</tr>
		';
	} else {
		foreach ($rolleplott as $plott) {
			if (!$plott['tilknytning']) {
				$plott['tilknytning'] = '('.$LANG['MISC']['information_missing'].')';
			}
			$html .= '
				<tr class="highlight">
					<td>'.$plott['navn'].'</td>
				</tr>
				<tr>
					<td>'.nl2br($plott['tilknytning']).'</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center" nowrap>
						<button onClick="javascript:window.location=\'./visplott.php?plott_id='.$plott['plott_id'].'&amp;spill_id='.$plott['spill_id'].'\';">'.$LANG['MISC']['plotsheet'].'</button>
					</td>
				</tr>
			';
		}
	}
	$html .= '
		</table>
	';
	return $html;
}

# Build the code for displaying a smaller sheet of plot-info for a character
function print_plottinfo_rolle_small($rolle_id, $spill_id) {
	global $LANG;
	$rolle = get_rolle($rolle_id, $spill_id);
	echo '
	<h2 align="center">'.$LANG['MISC']['plot_relations'].'</h2>
	<h3 align="center">'.$rolle['navn'].'</h3>
	<br>
	';
	if (!$rolleplott = get_rolle_plott($rolle_id, $spill_id)) {
		echo '
			<h4 align="center">'.$LANG['MISC']['no_plots'].'</h4>
		';
	} else {
		echo '
			<table align="center">
		';
		foreach ($rolleplott as $plott) {
			if (!$plott['tilknytning']) {
				$plott['tilknytning'] = $LANG['MISC']['information_missing'];
			}
			echo '
				<tr class="highlight">
					<td><span onClick="javascript:return overlib(\''.$plott['beskrivelse'].'\', CAPTION, \''.$plott['navn'].'\');">'.$plott['navn'].'</span></td>
				</tr>
				<tr>
					<td>'.nl2br($plott['tilknytning']).'</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			';
		}
		echo '
			</table>
		';
	}
}

# Build the code for displaying the group-related info for a character
function gruppeinfo_rolle_sheet($rolle_id, $spill_id) {
	global $LANG;
	$html .= '
		<table width="100%">
			<tr>
				<td align="center"><h4 class="table">'.$LANG['MISC']['group_memberships'].'</h4></td>
			</tr>
	';
	if (!$rolle_grupper = get_rolle_grupper($rolle_id, $spill_id)) {
		$html .= '
			<tr>
				<td>'.$LANG['MISC']['no_groups'].'</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center">
					<button onClick="javascript:window.location=\'./grupper.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['groups'].'</button>
				</td>
			</tr>
		';
	} else {
		foreach ($rolle_grupper as $rolle_gruppe) {
			$medlemmer = get_gruppe_roller($rolle_gruppe['gruppe_id'], $rolle_gruppe['spill_id']);
			$html .= '
				<tr class="highlight">
					<td nowrap>'.$rolle_gruppe['navn'].'</td>
				</tr>
				<tr>
					<td nowrap>
			';
			foreach ($medlemmer as $medlem) {
				$html .= '<a href="./visrolle.php?rolle_id='.$medlem['rolle_id'].'&amp;spill_id='.$medlem['spill_id'].'">'.$medlem['navn'].'</a><br>';
			}
			$html .= '
					</td>
				</tr>
			';

			if ($gruppe_plott = get_gruppe_plott($rolle_gruppe['gruppe_id'], $rolle_gruppe['spill_id'])) {
				$html .= '
					<tr>
						<td class="nospace"><br><h5>'.$LANG['MISC']['plot_relations'].':</h5></td>
					</tr>
				';
				foreach ($gruppe_plott as $plottinfo) {
					$html .= '
						<tr>
							<td><a href="./visplott.php?plott_id='.$plottinfo['plott_id'].'&amp;spill_id='.$plottinfo['spill_id'].'">'.$plottinfo['navn'].'</a></td>
						</tr>
					';
				}
			}
			$html .= '
				<tr>
					<td align="center" nowrap>
						<button onClick="javascript:window.location=\'./visgruppe.php?gruppe_id='.$rolle_gruppe['gruppe_id'].'&amp;spill_id='.$rolle_gruppe['spill_id'].'\';">'.$LANG['MISC']['groupsheet'].'</button>
					</td>
				</tr>
			';

		}
	}
	$html .= '
		</table>
	';
	return $html;
}

# Build the code for displaying a smaller sheet of group-info for a character
function print_gruppeinfo_small($rolle_id, $spill_id) {
	global $LANG;
	$rolle = get_rolle($rolle_id, $spill_id);
	echo '
		<h2 align="center">'.$LANG['MISC']['group_memberships'].'</h4>
		<h3 align="center">'.$rolle['navn'].'</h3>
		<br>
	';
	if (!$rolle_grupper = get_rolle_grupper($rolle_id, $spill_id)) {
		echo '
			<h4 align="center">'.$LANG['MISC']['no_group_memberships'].'</h4>
		';
	} else {
		echo '
			<table align="center" width="100%">
		';
		foreach ($rolle_grupper as $rolle_gruppe) {
			$medlemmer = get_gruppe_roller($rolle_gruppe['gruppe_id'], $rolle_gruppe['spill_id']);
			echo '
				<tr class="highlight">
					<td nowrap>'.$LANG['MISC']['group'].': '.$rolle_gruppe['navn'].'</td>
				</tr>
				<tr>
					<td nowrap><strong>'.$LANG['MISC']['group_members'].':</strong>
					<ul style="margin:0;">
			';
			foreach ($medlemmer as $medlem) {
				echo '<li><span onClick="return overlib(\''.print_rolle_small($medlem['rolle_id'], $spill_id).'\', CAPTION, \''.$medlem['navn'].'\');">'.$medlem['navn'].'</span></li>';
			}
			echo '
					</ul>
					</td>
				</tr>
			';

			if ($gruppe_plott = get_gruppe_plott($rolle_gruppe['gruppe_id'], $rolle_gruppe['spill_id'])) {
				echo '
					<tr>
						<td class="nospace"><strong>'.$LANG['MISC']['plot_relations'].':</strong></td>
					</tr>
					<tr>
						<td>
						<ul style="margin:0;">
				';
				foreach ($gruppe_plott as $plottinfo) {
					echo '
						<li><span onClick="return overlib(\''.$plottinfo['tilknytning'].'\', CAPTION, \''.$LANG['MISC']['relation'].' ('.$plottinfo['navn'].')\');">'.$plottinfo['navn'].'</span></li>
					';
				}
			}
			echo '
						</td>
					</tr>
			';

		}
		echo '
			</table>
		';
	}
}

# Build the code for displaying a smaller charactersheet
function print_rolle_small($rolle_id, $spill_id) {
	global $LANG;
	$rolle = get_rolle($rolle_id, $spill_id);
	$spiller = get_person($rolle['spiller_id']);
	$rolleboks = str_replace("\r\n", '', '
		<table>
			<tr>
				<td>'.$LANG['MISC']['player'].': '.$spiller['fornavn'].' '.$spiller['etternavn'].'</td>
			</tr>
		</table>
	');
	return $rolleboks;
}

# Build the code for displaying the plot-info for a group
function print_plottinfo_gruppe($gruppe_id, $spill_id) {
	global $LANG;
	echo '
		<table width="100%">
			<tr>
				<td align="center"><h4 class="table">'.$LANG['MISC']['plot_relations'].'</h4></td>
			</tr>
	';
	if (!$gruppeplott = get_gruppe_plott($gruppe_id, $spill_id)) {
		echo '
			<tr>
				<td>'.$LANG['MISC']['no_plots'].'</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center">
					<button onClick="javascript:window.location=\'./plott.php?spill_id='.$spill_id.'\';">'.$LANG['MISC']['plots'].'</button>
				</td>
			</tr>
		';
	} else {
		foreach ($gruppeplott as $plott) {
			if (!$plott['tilknytning']) {
				$plott['tilknytning'] = $LANG['MISC']['information_missing'];
			}
			echo '
				<tr class="highlight">
					<td>'.$plott['navn'].'</td>
				</tr>
				<tr>
					<td>'.nl2br($plott['tilknytning']).'</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center" nowrap>
						<button onClick="javascript:window.location=\'./visplott.php?plott_id='.$plott['plott_id'].'&amp;spill_id='.$plott['spill_id'].'\';">'.$LANG['MISC']['plotsheet'].'</button>
					</td>
				</tr>
			';
		}
	}
	echo '
		</table>
	';
}

# Return a portion of a string, used to crop information in lists
function broken_text($tekst, $max) {
	if (strlen($tekst) > $max) {
		return substr($tekst, 0, $max)."...";
	}
	return $tekst;
}

# Send an email to the admin in the event of a potential security-breach (like a user failing a login 3 times) 
function security_warning($message) {
	global $config;
	if ($config['send_security_warning']) {
		mail($config['akkar_admin_navn'].' <'.$config['akkar_admin_email'].'>', 'AKKAR '.$LANG['MISC']['security_message'], $message);
	}
}

# Resize an image
function resizeimg($srcbilde,$destbilde,$dummy_image_type,$new_w,$new_h, $delete_old = 1) {
	global $gdver;
	if (!$gdver) {
		return false;
	}
	$image_type = exif_imagetype($srcbilde);
	switch($image_type) {
		case IMAGETYPE_GIF:
			if (imagetypes() & IMG_GIF) {
				$src_img = imagecreatefromgif($srcbilde);
			} else {
				return false;
			}
			break;
		case IMAGETYPE_JPEG:
			if (imagetypes() & IMG_JPG) {
				$src_img = imagecreatefromjpeg($srcbilde);
			} else {
				return false;
			}
			break;
		case IMAGETYPE_BMP:
			$src_img = imagecreatefrombmp($srcbilde);
			break;
		case IMAGETYPE_PNG:
			if (imagetypes() & IMG_PNG) {
				$src_img = imagecreatefrompng($srcbilde);
			} else {
				return false;
			}
			break;
		default:
			return false;
	}
	$sx = imagesx($src_img);
	$sy = imagesy($src_img);
	$ratio = min($sx / $new_w, $sy / $new_h);
	$newsx = $new_w * $ratio;
	$newsy = $new_h * $ratio;
	$x = $sx / 2 - $newsx / 2;
	$y = $sy / 2 - $newsy / 2;
	if($gdver >= 2) {
		$dst_img = imagecreatetruecolor($new_w,$new_h);
        	imagecopyresampled($dst_img, $src_img, 0, 0, $x, $y,$new_w, $new_h, $newsx, $newsy);
	} elseif($gdver == 1) {
		$dst_img = imagecreate($new_w,$new_h);
        	imagecopyresized($dst_img, $src_img, 0, 0, $x, $y,$new_w, $new_h, $newsx, $newsy);
	}
	imagejpeg($dst_img, $destbilde);
	if ($delete_old != 0) {
		unlink($srcbilde);
	}
	return true;
}

# Function for setting up links for list-sorting
# $url = The page to open when link is clicked (normally the same as the current page)
# $order = Field to sort by (navn, fornavn, alder, etc...) [fields are named in norwegian]
# $type = "rolleorder", "personorder" or "gruppeorder" (character, person or group - non-translatable argument)
function get_sorting($srcurl, $order, $type) {
	global $config, $styleimages;
	if (!$_GET['utskrift']) {
		$bookmark = strstr($srcurl, '#');
		if (strpos($srcurl, '?')) {
			$urlparms = '&amp;'.substr(strstr($srcurl, '?'), 1);
			$url = explode('?', $srcurl);
		} elseif (strpos($srcurl, '#')) {
			$url = explode('#', $srcurl);
		} else {
			$url[0] = $srcurl;
		}
		if ((strtolower(substr(trim(urldecode($_SESSION[$type])), -5)) == ' desc') && ((urldecode($_SESSION[$type]) == urldecode($order)) || (urldecode($_SESSION[$type]) == urldecode($order.' desc')))) {
			$sorting = '<a href="'.$url[0].'?'.$type.'='.$order.$urlparms.$bookmark.'"><img src="'.$styleimages['tinyarrow_down'].'"></a>';
		} elseif (urldecode($_SESSION[$type]) == urldecode($order)) {
			$sorting = '<a href="'.$url[0].'?'.$type.'='.$order.' desc'.$urlparms.$bookmark.'"><img src="'.$styleimages['tinyarrow_up'].'"></a>';
		} else {
			$sorting = '<a href="'.$url[0].'?'.$type.'='.$order.$urlparms.$bookmark.'"><img src="'.$styleimages['tinyarrow_down'].'"></a><a href="'.$url[0].'?'.$type.'='.$order.' desc'.$urlparms.$bookmark.'"><img src="'.$styleimages['tinyarrow_up'].'"></a>';
		}
		return $sorting;
	}
	return false;
}

# Exit the script. Include the footer first to make it look proper
function exits() {
	global $LANG, $config;
	include('footer.php');
	exit();
}

# Check if it's time for a new table row in a list
function check_for_new_row(&$count, $break) {
	if (!$count) {
		$count = 0;
	}
	$count++;
	if ($count == $break) {
		echo '
			</tr>
			<tr>
		';
		$count = 0;
	}
}

# Get the actual formula from a template CALC-field
function get_calc_formula($target, $formula) {
	$result = str_replace('X', '"'.$target.'"', $formula);
	return $result;
}

# Debug-function
function print_a($data) {
	echo '<div align="left"><pre>';
	print_r($data);
	echo '</pre></div>';
}

# Another debug-function
function print_x($data) {
	print_a($data);
	exit();
}

# Function to replace weird characters inserted by Microsoft Word's "Auto Correct" feature with normal characters
function convert_funky_letters($text) {
	$result = str_replace(chr(150), '-', 
		str_replace(chr(148), '"', 
			str_replace(chr(146), "'", 
				str_replace(chr(133), '...', 
					str_replace('"', "'", $text))
			)
		)
	);
	return $result;
}

# Serialize strings coming from or going to an SQL database
function sql_serialize($value) {
	foreach ($value as $skey=>$svalue) {
		if (is_array($svalue)) {
			foreach ($svalue as $nkey=>$nvalue) {
				$svalue[$nkey] = stripslashes($nvalue);
			}
		} else {
			$value[$skey] = stripslashes($svalue);
			
		}
	}
	return addslashes(serialize($value));
}

# Parse the tags used in email templates
function parse_custom_tags($text) {
	global $config, $spillnavn;
	$text = str_replace('[game]', $spillnavn,
				str_replace('[org_group]', $config['arrgruppenavn'],
					str_replace('[email]', $config['arrgruppemail'],
						str_replace('[url]', $config['arrgruppeurl'], $text)
					)
				)
			);
	return $text;
}

# Workaround if the iconv-extension is missing.
if (!extension_loaded('iconv')) {
	require_once('scripts/ConvertCharset.class.php');
	function iconv($fromcharset, $tocharset, $string) {
		$charconvert = new ConvertCharset();
		$string = $charconvert->Convert($string, $fromcharset, $tocharset);
		return $string;
	}

}

# Callback to the backup-function to convert the filenames to CP850 (or it'll get corrupted within the zip-file)
function add_zipfile_callback($p_event, &$p_header) {
	$p_header['stored_filename'] = iconv('ISO-8859-1', 'CP850', $p_header['stored_filename']);
	return 1;
}

# Callback to the restore-function to convert the filenames back from CP850
function restore_filecheck_callback($p_event, &$p_header) {
	if ($_POST['restore_overwrite'] && is_file($p_header['filename'])) {
		unlink($p_header['filename']);
	}
	$p_header['filename'] = iconv('CP850', 'ISO-8859-1', $p_header['filename']);
	return 1;
}

# Do a complete backup
function do_fullbackup() {
	global $config, $ds;
	require_once('pclzip.lib.php');
	$backupfile = 'tmp'.$ds.'akkar_fullbackup-'.strftime('%d%b%Y-%H%M', time()).'.zip';
	$zipfile = new PclZip($backupfile);
	$sqlfile = do_dbbackup();
	$zipfile->create($sqlfile.',images'.$ds.'personer,'.$config['filsystembane'], PCLZIP_CB_PRE_ADD, 'add_zipfile_callback', PCLZIP_OPT_REMOVE_PATH, 'tmp');
	unlink($sqlfile);
	return $backupfile;
}

# Do a complete restore from uploaded backup-file
function do_fullrestore() {
	global $config, $LANG, $ds;
	require_once('pclzip.lib.php');
	$restorefile = '.'.$ds.'tmp'.$ds.$_FILES['restore_fil']['name'];
	move_uploaded_file($_FILES['restore_fil']['tmp_name'], $restorefile);
	$zipfile = new PclZip($restorefile);
	$sqlfile = $zipfile->extractByIndex(0, PCLZIP_OPT_PATH, 'tmp'.$ds);
	if (strtolower(substr($sqlfile[0]['filename'], -4)) != '.sql') {
		$_SESSION['message'] = $LANG['ERROR']['invalid_restore_file'];
		unlink($restorefile);
		return false;
	}
	do_dbrestore($sqlfile[0]['filename']);
	if ($_POST['do_full_restore']) {
		$files = $zipfile->listContent();
		foreach ($files as $file) {
			if ($file['index'] != 0) {
				print_a($file);
				$zipfile->extractByIndex($file['index'], PCLZIP_CB_PRE_EXTRACT, 'restore_filecheck_callback');
			}
		}
	}
	unlink($restorefile);
	return true;
}


# Function to gzip a file
function gzcompressfile ($source,$level = 9){
	$dest = $source.'.gz';
	$mode = 'wb'.$level;
	$error = false;
	if($fp_out = gzopen($dest,$mode)){
		if($fp_in = fopen($source,'rb')) {
		while(!feof($fp_in))
			gzputs($fp_out,fread($fp_in,1024*512));
			fclose($fp_in);
		}
		else $error=true;
		gzclose($fp_out);
	} else {
		$error = true;
	}
	if (!$error) {
		return $dest;
	}
	return false;
}

# Increase failed login amount by one and let the user try again (unless he/she has failed once too many, in which case this function never gets called)
function login_failed() {
	$_SESSION['failed_attempt']++;
	$_SESSION['failed_usernames'] .= $_POST['brukernavn'].', ';
	header('Location: login.php');
	exit();
}

# Email-address validation, taken from hololib (ftp://ftp.holotech.net/hololib.zip)
 /*
  * Validate an email address
  *   $Addr    = The address to check
  *   $Fail    = The level at which the validation failed
  *   $Level   = The level of checking to perform
  *   $Timeout = Optional timeout for mail server response

	This function has five levels of checking; each level above 1 includes all
	the checks of the prior level(s). Level 1 checks that: a) There is an @
	sign with something on the left and something on the right; b) to the right
	of the @ there is at least one dot, with something to the left and to the
	right; c) The string to the right of the last dot is two or three charac-
	ters long, or one of the new, longer TLDs.
	    
	Level 2 checks to see if the string after the dot is a valid TLD (i.e. 
	com, net, org, edu, gov, mil, int, arpa, aero, coop, info, museum, name or
	a two-letter country code). If the TLD is a country code, it checks that
	the next level domain is valid (com, net, org, edu, gov, mil, co, ne, or,
	ed, go, mi -- for example, mydomain.com.au).
	
	Level 3 checks to see if there is an MX record for the domain. Level 4
	attempts to connect to port 25 on an MX host, and Level 5 checks if
	there's an answer. 
*/
function ValEmail($Addr, &$Fail, $Level = 2, $Timeout = 3) {
    // Valid Top-Level Domains
    $gTLDs = "com:net:org:edu:gov:mil:int:arpa:aero:biz:coop:info:museum:name:";
    $CCs   = 
      "ad:ae:af:ag:ai:al:am:an:ao:aq:ar:as:at:au:aw:az:ba:bb:bd:be:bf:bg:bh:".
      "bi:bj:bm:bn:bo:br:bs:bt:bv:bw:by:bz:ca:cc:cf:cd:cg:ch:ci:ck:cl:cm:cn:".
      "co:cr:cs:cu:cv:cx:cy:cz:de:dj:dk:dm:do:dz:ec:ee:eg:eh:er:es:et:fi:fj:".
      "fk:fm:fo:fr:fx:ga:gb:gd:ge:gf:gh:gi:gl:gm:gn:gp:gq:gr:gs:gt:gu:gw:gy:".
      "hk:hm:hn:hr:ht:hu:id:ie:il:in:io:iq:ir:is:it:jm:jo:jp:ke:kg:kh:ki:km:".
      "kn:kp:kr:kw:ky:kz:la:lb:lc:li:lk:lr:ls:lt:lu:lv:ly:ma:mc:md:mg:mh:mk:".
      "ml:mm:mn:mo:mp:mq:mr:ms:mt:mu:mv:mw:mx:my:mz:na:nc:ne:nf:ng:ni:nl:no:".
      "np:nr:nt:nu:nz:om:pa:pe:pf:pg:ph:pk:pl:pm:pn:pr:pt:pw:py:qa:re:ro:ru:".
      "rw:sa:sb:sc:sd:se:sg:sh:si:sj:sk:sl:sm:sn:so:sr:st:su:sv:sy:sz:tc:td:".
      "tf:tg:th:tj:tk:tm:tn:to:tp:tr:tt:tv:tw:tz:ua:ug:uk:um:us:uy:uz:va:vc:".
      "ve:vg:vi:vn:vu:wf:ws:ye:yt:yu:za:zm:zr:zw:";

	$cTLDs = "com:net:org:edu:gov:mil:co:ne:or:ed:go:mi:aero:biz:coop:info:museum:name:";

	$Fail = 0;
	$Addr = trim(strtolower($Addr));

	if (ereg(" ", $Addr)) $Fail = 1;

	$UD = explode("@", $Addr);
	if (sizeof($UD) != 2 || !$UD[0]) $Fail = 1;

	$Levels = explode(".", $UD[1]); $sLevels = sizeof($Levels);
	if (!$Levels[0] || !$Levels[1]) $Fail = 1;

	$tld = $Levels[$sLevels-1];
	$tld = ereg_replace("[>)}]$|]$", "", $tld);
	if (strlen($tld) < 2
	|| (strlen($tld) > 3 && !ereg(":$tld:", ":arpa:aero:coop:info:museum:name:"))) $Fail = 1;

	$Level--;

	if ($Level && !$Fail) {
		$Level--;
		if (!ereg($tld.":", $gTLDs) && !ereg($tld.":", $CCs)) $Fail = 2;
	}

	if ($Level && !$Fail) {
		$cd = $sLevels - 2; $domain = $Levels[$cd].".".$tld;
		if (ereg($Levels[$cd].":", $cTLDs)) { $cd--; $domain = $Levels[$cd].".".$domain; }
	}

	if ($Level && !$Fail) {
		$Level--;
		if (!getmxrr($domain, $mxhosts, $weight)) $Fail = 3;
	}

	if ($Level && !$Fail) {
		$Level--;
		while (!$sh && (list($nul, $mxhost) = each($mxhosts))) {
				$sh = fsockopen($mxhost, 25);
		}
		if (!$sh) {
			$Fail=4;
		}
	}

	if ($Level && !$Fail) {
		$Level--;
		$out = "";
		socket_set_blocking($sh, false);
		$WaitTil = time() + $Timeout;
		while ($WaitTil > time() && !$out) $out = fgets($sh, 256);
		if (!ereg("^220", $out)) $Fail = 5;
	}

	if ($sh) fclose($sh);

	if ($Fail) return false; else return true;
}

# Convert BMP images to something GDlib can understand
function ConvertBMP2GD($src, $dest = false) {
	if(!($src_f = fopen($src, "rb"))) {
		return false;
	}
	if(!($dest_f = fopen($dest, "wb"))) {
		return false;
	}
	$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f, 14));
	$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($src_f, 40));

	extract($info);
	extract($header);

	if($type != 0x4D42) {  // signature "BM"
		return false;
	}

	$palette_size = $offset - 54;
	$ncolor = $palette_size / 4;
	$gd_header = "";
	// true-color vs. palette
	$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF"; 
	$gd_header .= pack("n2", $width, $height);
	$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
	if($palette_size) {
		$gd_header .= pack("n", $ncolor);
	}
	// no transparency
	$gd_header .= "\xFF\xFF\xFF\xFF";     

	fwrite($dest_f, $gd_header);

	if($palette_size) {
		$palette = fread($src_f, $palette_size);
		$gd_palette = "";
		$j = 0;
		while($j < $palette_size) {
			$b = $palette{$j++};
			$g = $palette{$j++};
			$r = $palette{$j++};
			$a = $palette{$j++};
			$gd_palette .= "$r$g$b$a";
		}
		$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
		fwrite($dest_f, $gd_palette);
	}

	$scan_line_size = (($bits * $width) + 7) >> 3;
	$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;

	for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
		// BMP stores scan lines starting from bottom
		fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
		$scan_line = fread($src_f, $scan_line_size);
		if ($bits == 24) {
			$gd_scan_line = "";
			$j = 0;
			while($j < $scan_line_size) {
				$b = $scan_line{$j++};
				$g = $scan_line{$j++};
				$r = $scan_line{$j++};
				$gd_scan_line .= "\x00$r$g$b";
			}
		} elseif ($bits == 8) {
			$gd_scan_line = $scan_line;
		} elseif ($bits == 4) {
			$gd_scan_line = "";
			$j = 0;
			while($j < $scan_line_size) {
				$byte = ord($scan_line{$j++});
				$p1 = chr($byte >> 4);
				$p2 = chr($byte & 0x0F);
				$gd_scan_line .= "$p1$p2";
			}
			$gd_scan_line = substr($gd_scan_line, 0, $width);
		} elseif ($bits == 1) {
			$gd_scan_line = "";
			$j = 0;
			while($j < $scan_line_size) {
				$byte = ord($scan_line{$j++});
				$p1 = chr((int) (($byte & 0x80) != 0));
				$p2 = chr((int) (($byte & 0x40) != 0));
				$p3 = chr((int) (($byte & 0x20) != 0));
				$p4 = chr((int) (($byte & 0x10) != 0));
				$p5 = chr((int) (($byte & 0x08) != 0));
				$p6 = chr((int) (($byte & 0x04) != 0));
				$p7 = chr((int) (($byte & 0x02) != 0));
				$p8 = chr((int) (($byte & 0x01) != 0));
				$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
			}
			$gd_scan_line = substr($gd_scan_line, 0, $width);
		}

		fwrite($dest_f, $gd_scan_line);
	}
	fclose($src_f);
	fclose($dest_f);
	return true;
}

# Function to create an image from a bmp-file, used to complement the PHP native functions to create from jpeg, gif and png
function imagecreatefrombmp($filename) {
	$tmp_name = tempnam(getcwd().DIRECTORY_SEPARATOR.'tmp', 'GD');
	if(ConvertBMP2GD($filename, $tmp_name)) {
		$img = imagecreatefromgd($tmp_name);
		unlink($tmp_name);
		return $img;
	}
	return false;
}

# Workaround for cases where exif isn't installed.
if(!function_exists('exif_imagetype')) {
	function exif_imagetype($filename) {
		$itype = getimagesize($filename);
		return $itype[2];
	}
}

# Dummy function to prevent an error if GD isn't linked
if (!function_exists('imagetypes')) {
	function imagetypes() {
		return 0;
	}
}

# Workaround for cases where calendar-support isn't installed.
if(!function_exists('unixtojd')) {
	define('EPOCH', 2440588);
	define('SPD', 86400);

	function unixtojd($timestamp = false) {
		if($timestamp === false)
			$timestamp = time();

		// Beware the frumious timezone offset!
		$timestamp = $timestamp + (int)date('Z', $timestamp);

		return EPOCH + floor($timestamp / SPD);
	}

	function gregoriantojd($month, $day, $year) {
		if(!checkdate($month, $day, $year))
			return 0;

		return unixtojd(mktime(0, 0, 0, $month, $day, $year));
	}

	function easter_date ($Year) {
		$G = $Year % 19;
		$C = (int)($Year / 100);
		$H = (int)($C - (int)($C / 4) - (int)((8 * $C + 13) / 25) + 19 * $G + 15) % 30;
		$I = (int)$H - (int)($H / 28) * (1 - (int)($H / 28) * (int)(29 / ($H + 1)) * ((int)(21 - $G) / 11));
		$J = ($Year + (int)($Year / 4) + $I + 2 - $C + (int)($C / 4)) % 7;
		$L = $I - $J;
		$m = 3 + (int)(($L + 40) / 44);
		$d = $L + 28 - 31 * ((int)($m / 4));
		$y = $Year;
		$E = mktime(0, 0, 0, $m, $d, $y);

		return $E;
	}

	function cal_days_in_month($calendar, $month, $year) {
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	function jdtounix($julianday) {
		$timestamp = (($julianday - EPOCH) * SPD);
		$tz = (int)date('Z', $timestamp);
		return $timestamp + $tz;
	}

	function jddayofweek($julianday, $mode) {
		$stamp = jdtounix($julianday);
		$tod = getdate($stamp);

		switch($mode) {
			case 0 :
				return $tod['wday'];
			case 1 :
				return $tod['weekday'];
			case 2 :
				return substr($tod['weekday'], 0, 3);
		}
	}

}


# Workaround for iconv where the function is named libiconv (reported on some *BSD systems)
if (!function_exists('iconv') && function_exists('libiconv')) {
   function iconv($input_encoding, $output_encoding, $string) {
       return libiconv($input_encoding, $output_encoding, $string);
   }
}

# Function taken from phpBB2 (http://www.phpbb.com/). It takes the data 
# from an input SQL dumpfile and returns statements we can use to 
# insert the data.
function split_sql_file($sql, $delimiter)
{
	// Split up our string into "possible" SQL statements.
	$tokens = explode($delimiter, $sql);

	// try to save mem.
	$sql = "";
	$output = array();
	
	// we don't actually care about the matches preg gives us.
	$matches = array();
	
	// this is faster than calling count($oktens) every time thru the loop.
	$token_count = count($tokens);
	for ($i = 0; $i < $token_count; $i++)
	{
		// Don't wanna add an empty string as the last thing in the array.
		if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
		{
			// This is the total number of single quotes in the token.
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes, 
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
			
			$unescaped_quotes = $total_quotes - $escaped_quotes;
			
			// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
			if (($unescaped_quotes % 2) == 0)
			{
				// It's a complete sql statement.
				$output[] = $tokens[$i];
				// save memory.
				$tokens[$i] = "";
			}
			else
			{
				// incomplete sql statement. keep adding tokens until we have a complete one.
				// $temp will hold what we have so far.
				$temp = $tokens[$i] . $delimiter;
				// save memory..
				$tokens[$i] = "";
				
				// Do we have a complete statement yet? 
				$complete_stmt = false;
				
				for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
				{
					// This is the total number of single quotes in the token.
					$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
					// Counts single quotes that are preceded by an odd number of backslashes, 
					// which means they're escaped quotes.
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
			
					$unescaped_quotes = $total_quotes - $escaped_quotes;
					
					if (($unescaped_quotes % 2) == 1)
					{
						// odd number of unescaped quotes. In combination with the previous incomplete
						// statement(s), we now have a complete statement. (2 odds always make an even)
						$output[] = $temp . $tokens[$j];

						// save memory.
						$tokens[$j] = "";
						$temp = "";
						
						// exit the loop.
						$complete_stmt = true;
						// make sure the outer loop continues at the right point.
						$i = $j;
					}
					else
					{
						// even number of unescaped quotes. We still don't have a complete statement. 
						// (1 odd and 1 even always make an odd)
						$temp .= $tokens[$j] . $delimiter;
						// save memory.
						$tokens[$j] = "";
					}
					
				} // for..
			} // else
		}
	}
	return $output;
}

# Function to find the index of a string in a string starting from the end of the string
function lastIndexOf($haystack, $needle) {
	$index = strpos(strrev($haystack), strrev($needle));
	$index = strlen($haystack) - strlen(index) - $index;
	return $index+1;
}

# Function to sort multidimensional arrays by a key in the second dimension
function array_msort ($array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE) {
	if (is_array($array) && count($array) > 0) {
		foreach (array_keys($array) as $key) {
			$temp[$key]=$array[$key][$index];
		}
		if (!$natsort) {
			if ($order == 'asc') {
				asort($temp);
			} else {
				arsort($temp);
			}
		} else {
			if ($case_sensitive) {
				natsort($temp);
			} else {
				natcasesort($temp);
			}
			if ($order != 'asc') {
				$temp = array_reverse($temp, TRUE);
			}
		}
		foreach (array_keys($temp) as $key) {
			if (is_numeric($key)) {
				$sorted[] = $array[$key];
			} else {
				$sorted[$key] = $array[$key];
			}
		}
		return $sorted;
	}
	return $array;
}

function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars{rand(0, $chars_length)};
   
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // Grab a random character from our list
        $r = $chars{rand(0, $chars_length)};
       
        // Make sure the same two characters don't appear next to each other
        if ($r != $string{$i - 1}) $string .=  $r;
    }
   
    // Return the string
    return $string;
}
?>
