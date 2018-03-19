<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                header.php                               #
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

if ($_REQUEST['utskrift']) {
	include('printheader.php');
} elseif ($_REQUEST['infowindow']) {
	include('infoheader.php');
} else {
	if (is_file('styles/'.$config['style'].'/mainheader.php')) {
		include('styles/'.$config['style'].'/mainheader.php');
	} else {
		include('mainheader.php');
	}
}
?>