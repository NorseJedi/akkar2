<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                no_help.php                              #
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
<h2 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['no_help']."</h2>
<h4>".$LANG['HELP']['no_help']."</h4>
<br>
<p>".str_replace("<helpicon>", "<img src=\"".$styleimages['help']."\">", $LANG['HELP']['why_no_help'])."</p>";
?>
