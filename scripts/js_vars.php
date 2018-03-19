<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                js_vars.php                              #
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

# The only purpose of this file is to set certain variables generated serverside, which are also needed clientside
if (is_logged_in()) {
	echo '
	<script language="JavaScript" type="text/javascript">
		var ckprefix = \''.$config['ckprefix'].'\';
		var msg_confirm_delete = \''.$LANG['JSBOX']['confirm_delete'].'\';
		var msg_confirm_delete_dir = \''.$LANG['JSBOX']['confirm_delete_dir'].'\';
		var msg_confirm_delete_game = \''.$LANG['JSBOX']['confirm_delete_game'].'\';
		var msg_confirm_override_lock = \''.$LANG['JSBOX']['confirm_override_lock'].'\';
		var img_arrowup = \''.$styleimages['arrowup'].'\';
		var img_arrowdown = \''.$styleimages['arrowdown'].'\';
		var img_harrowup = \''.$styleimages['harrowup'].'\';
		var img_harrowdown = \''.$styleimages['harrowdown'].'\';
		var browsertype = \''.browsertype().'\';
	';
	if ($_SESSION['showhide']) {
		foreach ($_SESSION['showhide'] as $navbar=>$status) {
			echo '
			var '.$navbar.' = \''.$status.'\';';
		}
	}
	echo '
		
		</script>
	';

} else {
	echo '
	<script language="JavaScript" type="text/javascript">
		var img_arrowup = \''.$styleimages['arrowup'].'\';
		var img_arrowdown = \''.$styleimages['arrowdown'].'\';
		var img_harrowup = \''.$styleimages['harrowup'].'\';
		var img_harrowdown = \''.$styleimages['harrowdown'].'\';
		var browsertype = \''.browsertype().'\';
	</script>
	';
}
?>