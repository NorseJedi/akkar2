<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                          help_konfigurasjon.php                         #
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
<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['configuration']."</h3>
<p>".str_replace("<helpicon>", "<img src=\"./images/hjelp.gif\">", $LANG['HELP']['configuration'])."</p>
";
