<?php
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              animbuttons.js                             #
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

# We create enough elements to handle 30 <textarea> input-fields.
$js_numfields = 30;


echo "
if (document.images) {
";
for ($i = 0; $i < $js_numfields; $i++) {
	echo '
	arrowup'.$i.'on=new Image(15,15);
	arrowup'.$i.'on.src=img_harrowup;
	arrowdown'.$i.'on=new Image(15,15);
	arrowdown'.$i.'on.src=img_harrowdown;
	arrowup'.$i.'off=new Image(15,15);
	arrowup'.$i.'off.src=img_arrowup;
	arrowdown'.$i.'off=new Image(15,15);
	arrowdown'.$i.'off.src=img_arrowdown;
	';
}
unset($js_numfields);
echo '
}

function lightup(imgName) {
	if (document.images) {
		imgOn=eval(imgName + "on.src");
		document[imgName].src=imgOn;
	}
}

function turnoff(imgName) {
	if (document.images) {
		imgOff=eval(imgName + "off.src");
		document[imgName].src=imgOff;
	}
}

';
?>