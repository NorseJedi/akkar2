<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            help_editoppgave.php                         #
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
	if (opener.document.editoppgaveform.resultat) {
		if (opener.document.editoppgaveform.resultat.createTextRange && opener.document.editoppgaveform.resultat.caretPos) {
			var caretPos = opener.document.editoppgaveform.resultat.caretPos;
			caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
			opener.document.editoppgaveform.resultat.focus();
		} else {
			opener.document.editoppgaveform.resultat.value  += text;
			opener.document.editoppgaveform.resultat.focus();
		}
	} else {
		if (opener.document.editoppgaveform.oppgavetekst) {
			if (opener.document.editoppgaveform.oppgavetekst.createTextRange && opener.document.editoppgaveform.oppgavetekst.caretPos) {
				var caretPos = opener.document.editoppgaveform.oppgavetekst.caretPos;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
				opener.document.editoppgaveform.oppgavetekst.focus();
			} else {
				opener.document.editoppgaveform.oppgavetekst.value  += text;
				opener.document.editoppgaveform.oppgavetekst.focus();
			}
		}
	}
}
//-->
</script>
<?php
echo "
<br>
<h2 align=\"center\">".$LANG['MISC']['help']."</h2>
<h3 style=\"margin-bottom: 1em;\" align=\"center\">".$LANG['MISC']['create_task']."<br>".$LANG['MISC']['edit_task']."<br>".$LANG['MISC']['complete_task']."</h3>
<p>".$LANG['HELP']['emoticons']."</p>
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
