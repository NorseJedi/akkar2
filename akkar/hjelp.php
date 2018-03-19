<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                 hjelp.php                               #
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

$_REQUEST['infowindow'] = true;

$hjelp = $_GET['hjelp'];

include('header.php');
if (!is_file('./'.$whereiwas)) {
	exit('Access violation.');
} elseif (is_file('help/help_'.$whereiwas)) {
	include('help/help_'.$whereiwas);
} else {
	include('help/no_help.php');
}

include('footer.php');
?>