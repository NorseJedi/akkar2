<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                             help_kalender.php                           #
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
?>
<script language="javascript" type="text/javascript">
<!--
function emoticon(text) {
	text = ' ' + text + ' ';
	if (opener.document.editnotat.tekst.createTextRange && opener.document.editnotat.tekst.caretPos) {
		var caretPos = opener.document.editnotat.tekst.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		opener.document.editnotat.tekst.focus();
	} else {
	opener.document.editnotat.tekst.value  += text;
	opener.document.editnotat.tekst.focus();
	}
}
//-->
</script>
<?php
echo "
<br>
<h2 align=\"center\">".$LANG['MISC']['help']."</h2>
<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['calendar']."</h3>
<p>".$LANG['HELP']['calendar']."</p>
<table>
";
$smileys = get_smileys_replace();
for ($i = 0; $i < count($smileys['text']); $i++) {
	echo "
	<tr><td>".$smileys['text'][$i]."</td><td><a href=\"javascript:emoticon('".$smileys['text'][$i]."');\">".str_replace("\'", '"', $smileys['images'][$i])."</a></td></tr>
	";
}
echo "
</table>
";
?>
