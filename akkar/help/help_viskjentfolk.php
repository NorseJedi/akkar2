<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                           help_viskjentfolk.php                         #
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
if (!defined("IN_AKKAR")) {
	exit("Access violation.");
}
echo "
<br>
<h2 align=\"center\">".$LANG['MISC']['help']."</h2>
<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['acquaintances']."</h3>
";
switch ($hjelp) {
	case "grupper";
		echo "<p>".$LANG['HELP']['acquaintances_groups']."</p>";
		break;
	case "kjentgrupper";
		echo "
			<p>".$LANG['HELP']['acquainted_groups']."</p>
		";
		break;
	case "grupper_liste";
		echo "
			<p>".$LANG['HELP']['acquainted_groups_list']."</p>
		";
		break;
	case "kjentroller";
		echo "
			<p>".$LANG['HELP']['acquaintances_characters']."</p>";
		break;
	case "roller_liste";
		echo"
			<p>".$LANG['HELP']['acquaintances_characters_list']."</p>";
		break;
	case "folkkjent";
		echo "
			<p>".$LANG['HELP']['acquaintances_characters_reverse']."</p>";
}
?>
