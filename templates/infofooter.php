<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              infofooter.php                             #
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
echo '
		<div align="center" style="margin-top: 40px;">
			<button class="visible" type="button" onClick="javascript:window.close();">'.$LANG['MISC']['close_window'].'</button>
		</div>
		</td>
	</tr>
	<tr>
		<td class="bottom" align="center">
			<span class="tiny">Powered by <a href="" onClick="javascript:window.opener.location=\'http://akkar.sourceforge.net/\'; return false;" target="_blank">AKKAR-'.$config['version'].'</a></span>
		</td>
	</tr>
</table>
</div>
</body>
</html>
';
?>
