<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            help_filvedlegg.php                          #
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
";
if ($hjelp == "spill") {
	echo "<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['game_attachments']."</h3>
		<p>".$LANG['HELP']['game_attachments']."
	";
} elseif ($hjelp == "gruppe") {
	echo "
		<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['group_attachments']."</h3>
		<p>".$LANG['HELP']['group_attachments']."</p>
	";
} elseif ($hjelp == "rollekonsept") {
	echo "
		<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['charconcept_attachments']."</h3>
		<p>".$LANG['HELP']['charconcept_attachments']."</p>
	";
} else {
	echo "
		<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['character_attachments']."</h3>
		<p>".$LANG['HELP']['character_attachments']."</p>
	";
}
echo "
<h4>".$LANG['HELP']['all_attachments']."</h4>
";
?>