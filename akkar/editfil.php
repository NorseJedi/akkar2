<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                                editfil.php                              #
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

include('header.php');

echo '
	<h2 align="center">'.$LANG['MISC']['edit_file'].'</h2>
	<br>
';

$fil = get_fil($_GET['fil_id']);
echo '
	<form name="movefileform" action="./visfil.php" method="post">
	<input type="hidden" name="edited" value="'.$fil['fil_id'].'">
	<table align="center" class="bordered" cellspacing="0">
		<tr>
			<td class="highlight">'.$LANG['MISC']['filename'].'</td>
			<td><input type="text" size="35" name="navn" value="'.$fil['navn'].'"></td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['dir'].'</td>
			<td>
				<select name="dir">
					<option value="/"'; if ($fil['dir'] == '/') { echo ' selected'; } echo '>/</option>
				';
				if ($dirs = get_fs_dirtree('/')) {
					foreach ($dirs as $dir) {
						echo '<option value="'.$dir.'/"'; if ($fil['dir'] == $dir.'/') { echo ' selected'; } echo '>'.$dir.'</option>';
					}
				}
				echo '
				</select>
			</td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['filetype'].'</td>
			<td><input type="text" size="35" name="type" value="'.$fil['type'].'"></td>
		</tr>
		<tr>
			<td class="highlight">'.$LANG['MISC']['description'].'</td>
			<td><input type="text" size="35" name="beskrivelse" value="'.$fil['beskrivelse'].'"></td>
		</tr>
	</table>
	<table align="center" cellspacing="0">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="nospace"><button type="button" onClick="javascript:history.back();">'.$LANG['MISC']['back'].'</button></td>
			<td class="nospace"><button type="reset">'.$LANG['MISC']['reset'].'</button></td>
			<td class="nospace"><button type="submit">'.$LANG['MISC']['save'].'</button></td>
		</tr>
	</table>
	</form>
';
?>