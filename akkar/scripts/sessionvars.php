<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                              sessionvars.php                            #
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

if (!$_SESSION['rollevis']) {
	$_SESSION['rollevis']['navn'] = 1;
	$_SESSION['rollevis']['spiller_id'] = 1;
	$_SESSION['rollevis']['arrangor_id'] = 1;
}

if ($_POST['nyrollevis']) {
	unset($_SESSION['rollevis']);
	foreach ($_POST['rollevis'] as $key => $value) {
		$_SESSION['rollevis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}
if ($_REQUEST['spill_id'] == 0) {
	$_SESSION['rollevis']['navn'] = 1;
	$_SESSION['rollevis']['spiller_id'] = 1;
	$_SESSION['rollevis']['arrangor_id'] = 1;
}
if (!$_SESSION['personvis']) {
	$_SESSION['personvis']['etternavn'] = 1;
	$_SESSION['personvis']['fornavn'] = 1;
	$_SESSION['personvis']['email'] = 1;
}

if ($_POST['nypersonvis']) {
	unset($_SESSION['personvis']);
	foreach ($_POST['personvis'] as $key => $value) {
		$_SESSION['personvis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['arrangorvis']) {
	$_SESSION['arrangorvis']['etternavn'] = 1;
	$_SESSION['arrangorvis']['fornavn'] = 1;
	$_SESSION['arrangorvis']['email'] = 1;
}

if ($_POST['nyarrangorvis']) {
	unset($_SESSION['arrangorvis']);
	foreach ($_POST['arrangorvis'] as $key => $value) {
		$_SESSION['arrangorvis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['kontaktvis']) {
	$_SESSION['kontaktvis']['navn'] = 1;
	$_SESSION['kontaktvis']['kontaktperson'] = 1;
	$_SESSION['kontaktvis']['telefon'] = 1;
	$_SESSION['kontaktvis']['email'] = 1;
}

if ($_POST['kontaktvis']) {
	unset($_SESSION['kontaktvis']);
	foreach ($_POST['kontaktvis'] as $key => $value) {
		$_SESSION['kontaktvis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['kalendervis']) {
	$_SESSION['kalendervis']['arrangorbursdager'] = 1;
	$_SESSION['kalendervis']['spillerbursdager'] = 1;
	$_SESSION['kalendervis']['spilldager'] = 1;
	if (is_file('lang/'.$config['lang'].'_calext.php')) {
		$_SESSION['kalendervis']['merkedager'] = 1;
	}
	$_SESSION['kalendervis']['helligdager'] = 1;
}

if ($_POST['nykalendervis']) {
	unset($_SESSION['kalendervis']);
	$_SESSION['kalendervis']['dummy'] = 1;
	foreach ($_POST['kalendervis'] as $key => $value) {
		$_SESSION['kalendervis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['historikkvis']) {
	$_SESSION['historikkvis']['etternavn'] = 1;
	$_SESSION['historikkvis']['fornavn'] = 1;
	$_SESSION['historikkvis']['spill'] = 1;
	$_SESSION['historikkvis']['roller'] = 1;
}

if ($_POST['nyhistorikkvis']) {
	unset($_SESSION['historikkvis']);
	foreach ($_POST['historikkvis'] as $key => $value) {
		$_SESSION['historikkvis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['betalingvis']) {
	$_SESSION['betalingvis']['etternavn'] = 1;
	$_SESSION['betalingvis']['fornavn'] = 1;
	$_SESSION['betalingvis']['email'] = 1;
	$_SESSION['betalingvis']['paameldt'] = 1;
}

if ($_POST['nybetalingvis']) {
	unset($_SESSION['betalingvis']);
	foreach ($_POST['betalingvis'] as $key => $value) {
		$_SESSION['betalingvis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['betalingorder']) {
	$_SESSION['betalingorder'] = 'etternavn';
} elseif ($_GET['betalingorder']) {
	$_SESSION['betalingorder'] = $_GET['betalingorder'];
}

if (!$_SESSION['personorder']) {
	$_SESSION['personorder'] = 'etternavn';
} elseif ($_GET['personorder']) {
	$_SESSION['personorder'] = $_GET['personorder'];
}

if (!$_SESSION['kontaktorder']) {
	$_SESSION['kontaktorder'] = 'navn';
} elseif ($_GET['kontaktorder']) {
	$_SESSION['kontaktorder'] = $_GET['kontaktorder'];
}

if (!$_SESSION['spillorder']) {
	$_SESSION['spillorder'] = 'start DESC';
} elseif ($_GET['spillorder']) {
	$_SESSION['spillorder'] = $_GET['spillorder'];
}

if (!$_SESSION['arrangororder']) {
	$_SESSION['arrangororder'] = 'etternavn';
} elseif ($_GET['arrangororder']) {
	$_SESSION['arrangororder'] = $_GET['arrangororder'];
}

if (!$_SESSION['malorder']) {
	$_SESSION['malorder'] = 'navn';
} elseif ($_GET['malorder']) {
	$_SESSION['malorder'] = $_GET['malorder'];
}

if (!$_SESSION['rolleorder']) {
	$_SESSION['rolleorder'] = 'navn';
} elseif ($_GET['rolleorder']) {
	$_SESSION['rolleorder'] = $_GET['rolleorder'];
}

if (!$_SESSION['rollekonseptorder']) {
	$_SESSION['rollekonseptorder'] = 'tittel';
} elseif ($_GET['rollekonseptorder']) {
	$_SESSION['rollekonseptorder'] = $_GET['rollekonseptorder'];
}

if (!$_SESSION['gruppeorder']) {
	$_SESSION['gruppeorder'] = 'navn';
} elseif ($_GET['gruppeorder']) {
	$_SESSION['gruppeorder'] = $_GET['rolleorder'];
}

if (!$_SESSION['paameldingvis']) {
	$_SESSION['paameldingvis']['etternavn'] = 1;
	$_SESSION['paameldingvis']['fornavn'] = 1;
	$_SESSION['paameldingvis']['email'] = 1;
}

if ($_POST['nypaameldingvis']) {
	unset($_SESSION['paameldingvis']);
	foreach ($_POST['paameldingvis'] as $key => $value) {
		$_SESSION['paameldingvis'][$key] = $value;
	}
	header('Location: '.$_POST['whereiwas']);
	exit();
}

if (!$_SESSION['paameldingorder']) {
	$_SESSION['paameldingorder'] = 'etternavn';
} elseif ($_GET['paameldingorder']) {
	$_SESSION['paameldingorder'] = $_GET['paameldingorder'];
}

if (!$_SESSION['cwd']) {
	$_SESSION['cwd'] = '/';
}
?>