<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              mainfooter.php                             #
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
$page_timeend = microtime();
$page_timestart = $_SESSION['page_timestart'];
$page_gentime = number_format(((substr($page_timeend,0,9)) + (substr($page_timeend,-10)) - (substr($page_timestart,0,9)) - (substr($page_timestart,-10))),3);
unset($_SESSION['page_timestart']);
$total_space = get_human_readable_size(used_space(getcwd(), 1));
echo '
			</td>
			<td class="stjerne-hoyre">&nbsp;</td>
		</tr>
		<tr>
			<td class="stjerne-bv">&nbsp;</td>
			<td class="stjerne-bunn">&nbsp;</td>
			<td class="stjerne-bh">&nbsp;</td>
		</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td class="bottom" colspan="2">

<map name="phpmysql">
<area shape="rect" coords="1, 1, 33, 15" href="http://www.php.net/" target="_blank"/>
<area shape="rect" coords="33, 1, 80, 15" href="http://www.mysql.com/" target="_blank"/>
</map>
<table class="bottom" width="100%" border="0">
  <tr>
    <td class="bottom" width="33%" align="left">
		<table class="nospace">
			<tr>
				<td style="padding-right: 2px;" class="nospace"><a href="http://akkar.sourceforge.net/" target="_blank"><img src="images/akkar24.png"  alt="Powered by AKKAR" height="15" width="80"/></a></td>
				<td style="padding-right: 2px;" class="nospace"><img  style="border: 0;" src="images/phpmysql.png"  alt="Powered by PHP and MySQL" height="15" width="80" usemap="phpmysql"/></td>
				<td style="padding-right: 2px;" class="nospace"><a href="http://validator.w3.org/check/referer"><img src="images/html401.png" alt="Valid HTML 4.01!" height="15" width="80"/></a></td>
				<td class="nospace"><a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="images/css.png"  alt="Valid CSS!" height="15" width="80"/></a></td>
			</tr>
		</table>
    </td>
    <td class="bottom" width="34%" align="center">
      <span class="tiny">Powered by <a href="http://akkar.sourceforge.net/" target="_blank">AKKAR-'.$config['version'].'</a></span>
    </td>
    <td class="bottom" width="33%" align="right">
       <span class="tiny">
';
if (is_logged_in()) {
	echo 'AKKAR Total Size: '.$total_space.'<br>';
}
	echo '
	   Execution time: '.$page_gentime.' seconds</span>
    </td>
  </tr>
</table>
		</td>
	</tr>
</table>
';
if (is_logged_in()) {
	# Grab cookies set by the client for hideable elements and transform them into session-variables,
	# then show or hide the elements and delete the cookies
	if ($_COOKIE) {
		foreach ($_COOKIE as $key=>$value) {
			if (is_int(strpos($key, 'nav_'))) {
				$element = substr($key, strpos($key, 'nav_'));
				$_SESSION['showhides'][$element] = $value;
			} elseif (is_int(strpos($key, 'filters'))) {
				$_SESSION['showhides']['filters'] = $value;
			}
		}
	}
	echo '
	<script language="JavaScript" type="text/javascript">
		// initial_showhide_filters();
		// initial_navvis();
	';
		$elements = array('nav_admin', 'nav_general', 'filters');
	if ($websitenav_included) {
		$elements[] = 'nav_website';
	}
	foreach ($elements as $element) {
		if (!$_SESSION['showhides'][$element]) {
			switch ($element) {
				case 'nav_general':
					$_SESSION['showhides'][$element] = 'visible';
					break;
				default:
					$_SESSION['showhides'][$element] = 'hidden';
					break;
			}
		}

		echo '
		if (document.getElementById(\''.$element.'\') != null) {
		';
		if ($_SESSION['showhides'][$element] == 'visible') {
			echo 'show(document.getElementById(\''.$element.'\'), tbody_showproperty);
			';
			if ($element == 'filters') {
				echo 'document.getElementById(\'filterbox\').checked = true;
				';
			}
		} else {
			$_SESSION['showhides'][$element] = 'hidden';
			echo 'hide(document.getElementById(\''.$element.'\'));
			';
		}
		echo '}
		deleteCookie(ckprefix + \'_'.$element.'\');
		';
	}
	echo '
		</script>
	';
}
echo '
</body>
</html>
';
?>
