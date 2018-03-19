<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              printfooter.php                            #
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
		</td>
	</tr>
	<tr>
		<td class="bottom">
		<hr width="100%" size="1">
		Powered by AKKAR-'.$config['version'].'</td>
	</tr>
</table>
</div>
<script language="JavaScript" type="text/javascript">
	initial_showhide_filters();
	initial_navvis();
</script>
</body>
</html>
';
?>
