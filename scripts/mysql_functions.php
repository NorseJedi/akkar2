<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            mysql_functions.php                          #
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

# Establish the MySQL connection and select our database
$connection = mysql_pconnect($sqlserver, $sqluser, $sqlpasswd) or exit(mysql_error());
mysql_select_db($sqlbase);

##################################
# Funksjoner for rollebehandling #
##################################

function get_rolle($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['rollemal'];
	if ($maldata = get_maldata($mal_id)) {
		foreach ($maldata as $value) {
			$malfields .= $value['fieldname'].",";
		}
	}
	$fields = "rolle_id,arrangor_id,spill_id,spiller_id,navn,".$malfields."intern_info,beskrivelse1,beskrivelse2,beskrivelse3,beskrivelse_gruppe,bilde,oppdatert,status,status_id,status_tekst,locked";
	$rolle = mysql_fetch_assoc(mysql_query("SELECT $fields FROM `".$table_prefix."roller` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection));
	if ($rolle) {
		return $rolle;
	}
	return false;
}

function get_dummy_rolle($spill_id) {
	$rolle_first['arrangor_id'] = "";
	$rolle_first['spiller_id'] = "";
	$rolle_first['spill_id'] = $spill_id;
	$rolle_first['navn'] = "";
	$rolle_last['intern_info'] = "";
	$rolle_last['beskrivelse1'] = "";
	$rolle_last['beskrivelse2'] = "";
	$rolle_last['beskrivelse3'] = "";
	$rolle_last['beskrivelse_gruppe'] = "";
	if ($mal = get_rollemal($spill_id)) {
		foreach ($mal as $fieldname=>$fieldinfo) {
			$field[$fieldname] = "";
		}
		return array_merge($rolle_first, $field, $rolle_last);
	} else {
		return array_merge($rolle_first, $rolle_last);
	}
}

function get_roller($spill_id, $type = "aktive") {
	global $connection, $table_prefix;
	if ($_SESSION['rolleorder']) {
		$order = $_SESSION['rolleorder'];
	} else {
		$order = "navn";
	}
	if ($spill_id == "0") {
		$allespill = get_spill();
		foreach ($allespill as $spill) {
			if ($maldata = get_maldata($spill['rollemal'])) {
				foreach ($maldata as $value) {
					$malfields .= $value['fieldname'].",";
				}
			}
			$fields = "rolle_id,arrangor_id,spill_id,spiller_id,navn,".$malfields."intern_info,beskrivelse1,beskrivelse2,beskrivelse3,beskrivelse_gruppe,bilde,oppdatert,status,status_id,status_tekst,locked";
			if ($type == "alle") {
				$sql = "SELECT ".$fields." FROM `".$table_prefix."roller` WHERE spill_id=".intval($spill['spill_id'])." ORDER BY ".$order;
			} elseif ($type == "inaktive") {
				$sql = "SELECT ".$fields." FROM `".$table_prefix."roller` WHERE status!=0 && spill_id=".intval($spill['spill_id'])." ORDER BY ".$order;
			} else {
				$sql = "SELECT ".$fields." FROM `".$table_prefix."roller` WHERE status=0  && spill_id=".intval($spill['spill_id'])." ORDER BY ".$order;
			}
			$sqlroller = mysql_query($sql, $connection);
			//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
			//	$roller[] = mysql_fetch_assoc($sqlroller);
			while ($tuple = mysql_fetch_assoc($sqlroller)) {
				$roller[] = $tuple;
			}
			unset($fields, $malfields, $maldata);
		}
	} else {
		$spillinfo = get_spillinfo($spill_id);
		$mal_id = $spillinfo['rollemal'];
		if ($maldata = get_maldata($mal_id)) {
			foreach ($maldata as $value) {
				$malfields .= $value['fieldname'].",";
			}
		}
		$fields = "rolle_id,arrangor_id,spill_id,spiller_id,navn,".$malfields."intern_info,beskrivelse1,beskrivelse2,beskrivelse3,beskrivelse_gruppe,bilde,oppdatert,status,status_id,status_tekst,locked";
		if ($type == "alle") {
			$sql = "SELECT $fields FROM `".$table_prefix."roller` WHERE spill_id=".intval($spill_id)." ORDER BY ".$order;
		} elseif ($type == "inaktive") {
			$sql = "SELECT $fields FROM `".$table_prefix."roller` WHERE spill_id=".intval($spill_id)." && status!='0' ORDER BY ".$order;
		} else {
			$sql = "SELECT $fields FROM `".$table_prefix."roller` WHERE spill_id=".intval($spill_id)." && status='0' ORDER BY ".$order;
		}
		if ($sqlroller = mysql_query($sql, $connection)) {
			//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
			//	$roller[] = mysql_fetch_assoc($sqlroller);
			while ($tuple = mysql_fetch_assoc($sqlroller)) {
				$roller[] = $tuple;
			}
		} else {
			return false;
		}
	}
	if ($roller) {
		if (strpos(strtolower($order), ' desc') !== false) {
			$order = explode(' ', $order);
			$roller = array_msort($roller, $order[0], 'desc');
		} else {
			$roller = array_msort($roller, $order);
		}
		return $roller;
	}
	return false;
}

function get_mine_roller($spill_id) {
	global $connection, $table_prefix;
	if ($_SESSION['rolleorder']) {
		$order = $_SESSION['rolleorder'];
	} else {
		$order = "navn";
	}
	if ($spill_id == 0) {
		$sql = "SELECT * FROM `".$table_prefix."roller` WHERE arrangor_id=".intval($_SESSION['person_id'])." WHERE status='0' ORDER BY ".$order;
	} else {
		$sql = "SELECT * FROM `".$table_prefix."roller` WHERE arrangor_id=".intval($_SESSION['person_id'])." && spill_id=".intval($spill_id)." && status='0' ORDER BY ".$order;
	}
	$sqlroller = mysql_query($sql, $connection);
	$roller = array();
	//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
	//	$rolle = mysql_fetch_assoc($sqlroller);
	while ($rolle = mysql_fetch_assoc($sqlroller)) {
		if (is_aktiv_spill($rolle['spill_id'])) {
			$roller[$rolle['spill_id']][] = $rolle;
		}
	}
	if ($roller) {
		krsort($roller);
		return $roller;
	}
	return false;
}

function get_spiller_roller($person_id, $spill_id) {
	global $connection, $table_prefix;
	if ($_SESSION['rolleorder']) {
		$order = $_SESSION['rolleorder'];
	} else {
		$order = "navn";
	}
	if ($spill_id == 0) {
		$sql = "SELECT * FROM `".$table_prefix."roller` WHERE spiller_id=".intval($person_id)." && status='0' ORDER BY ".$order;
	} else {
		$sql = "SELECT * FROM `".$table_prefix."roller` WHERE spiller_id=".intval($person_id)." && spill_id=".intval($spill_id)." && status='0' ORDER BY ".$order;
	}
	$sqlroller = mysql_query($sql, $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
	//	$roller[] = mysql_fetch_assoc($sqlroller);
	while ($tuple = mysql_fetch_assoc($sqlroller)) {
		$roller[] = $tuple;
	}
	if ($roller) {
		return $roller;
	}
	return false;
}

function oppdater_rolle() {
	global $connection, $table_prefix, $config;
	$mal = get_spillmal($_POST['spill_id'], "rollemal");
	if ($maldata = get_maldata($mal['mal_id'])) {
		foreach ($maldata as $entry) {
			switch ($entry['type']) {
				case "check":
				if (!$_POST['rolle'][$entry['fieldname']]) {
					$sql .= "$entry[fieldname]='0',";
				}
				break;
				case "listmulti":
				$sql .= $entry['fieldname']."='".sql_serialize($_POST[$entry['fieldname']])."',";
				break;
			}
		}
	}
	foreach ($_POST['rolle'] as $key=>$value) {
		$sql .= "$key='$value',";
	}
	$sql .= "oppdatert=".time().",";
	mysql_query("UPDATE `".$table_prefix."roller` SET ".substr($sql, 0, -1)." WHERE rolle_id=".intval($_POST['rolle_id'])." && spill_id=".intval($_POST['spill_id']), $connection) or die(mysql_eror());
	unlock_rolle($_POST['rolle_id'], $_POST['spill_id']);
}

function lock_rolle($rolle_id, $spill_id) {
	global $connection, $table_prefix, $config;
	$locked = serialize(array(time(), $_SESSION['person_id']));
	mysql_query("UPDATE `".$table_prefix."roller` SET locked='".$locked."' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function unlock_rolle($rolle_id, $spill_id) {
	global $connection, $table_prefix, $config;
	mysql_query("UPDATE `".$table_prefix."roller` SET locked='0' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function check_lock_rolle($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$lockinfo = mysql_fetch_assoc(mysql_query("SELECT locked FROM `".$table_prefix."roller` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection));
	if ($lockinfo['locked'] != "0") {
		return unserialize($lockinfo['locked']);
	}
	return false;
}

function unlock_roller_all($person_id) {
	global $connection, $table_prefix, $config;
	/*
	$locked_roller = mysql_query("SELECT rolle_id, spill_id, locked FROM `".$table_prefix."roller` WHERE locked != '0'", $connection);
	for ($i = 0; $i < mysql_num_rows($locked_roller); $i++) {
		$rolle = mysql_fetch_assoc($locked_roller);
		$locked = unserialize($rolle['locked']);
		if ($locked[1] == $person_id) {
			mysql_query("UPDATE `".$table_prefix."roller` SET locked='0' WHERE rolle_id=".intval($rolle['rolle_id'])." && spill_id=".intval($rolle['spill_id']), $connection);
		}
	}
	*/
	mysql_query("UPDATE `".$table_prefix."roller` SET locked='0'", $connection);
}

function oppdater_rollefordeling($type) {
	global $connection, $table_prefix, $config;
	if ($type == "alle") {
		$roller = get_roller($_POST['spill_id']);
		foreach ($roller as $rolle) {
			if ($_POST[$rolle['rolle_id']]['spiller']) {
				$rolle['spiller_id'] = $_POST[$rolle['rolle_id']]['spiller'];
			} else {
				$rolle['spiller_id'] = 0;
			}
			if ($_POST[$rolle['rolle_id']]['arrangor']) {
				$rolle['arrangor_id'] = $_POST[$rolle['rolle_id']]['arrangor'];
			} else {
				$rolle['arrangor_id'] = 0;
			}
			mysql_query("UPDATE `".$table_prefix."roller` SET arrangor_id=".intval($rolle['arrangor_id']).",spiller_id=".intval($rolle['spiller_id'])." WHERE rolle_id=".intval($rolle['rolle_id'])." && spill_id=".intval($_POST['spill_id']), $connection) or exit(mysql_error());
		}
	} else {
		foreach ($_POST as $key=>$value) {
			switch($key) {
				case "spill_id":
				case "ny_fordeling":
				case "vis":
				break;
				default:
				if ($value) {
					mysql_query("UPDATE `".$table_prefix."roller` SET spiller_id=".intval($key)." WHERE rolle_id=".intval($value)." && spill_id=".intval($_POST['spill_id']), $connection);
				}
			}
		}
	}
}

function hent_roller() {
	global $connection, $table_prefix;
	foreach ($_POST as $key=>$value) {
		switch ($key) {
			case 'spill_id':
			case 'hent_roller':
			break;
			default:
			$key = explode('_', $key);
			$rolle = get_rolle($key[0], $key[1]);
			foreach ($rolle as $key2=>$value2) {
				switch ($key2) {
					case 'spill_id':
						$fields .= $key2.',';
						$values .= intval($_POST['spill_id']).',';
						break;
					case 'spiller_id':
						if ((get_paamelding($value2, $_POST['spill_id'])) || (get_arrangor($value2))) {
							$fields .= 'spiller_id,';
							$values .= $value2.',';
						} else {
							$fields .= 'spiller_id,';
							$values .= '0,';
						}
						break;
					case 'oppdatert':
						$fields .= 'oppdatert,';
						$values .= time().',';
						break;
					default:
						$fields .= $key2.',';
						$values .= '\''.addslashes($value2).'\',';
				}
			}
			mysql_query("INSERT INTO `".$table_prefix."roller` (".substr(trim($fields), 0, -1).") VALUES (".substr(trim($values), 0, -1).")", $connection) or exit(mysql_error());
			unset($rolle,$fields,$values);
		}
	}
}

function opprett_rolle() {
	global $connection, $table_prefix;
	$mal = get_spillmal($_POST['spill_id'], "rollemal");
	$fields = "spill_id,";
	$values = intval($_POST['spill_id']).",";
/*	if ($maldata = get_maldata($mal['mal_id'])) {
		foreach ($maldata as $entry) {
			switch ($entry['type']) {
				case "check":
				if (!$_POST['paamelding'][$entry['fieldname']]) {
					$fields .= "$entry[fieldname],";
					$values .= "'0',";
				}
				break;
				case "listmulti":
				$fields .= $entry['fieldname'].",";
				$values .= "'".sql_serialize($_POST[$entry['fieldname']])."',";
				break;
			}
		}
	}
*/
	foreach ($_POST['rolle'] as $key=>$value) {
		$fields .= "$key,";
		if (is_array($value)) {
			$values .= "'".sql_serialize($value)."',";
		} else {
			$values .= "'$value',";
		}
	}
	$fields .= "oppdatert,status,";
	$values .= time().",0,";
	mysql_query("INSERT INTO `".$table_prefix."roller` (".substr($fields, 0, -1).") VALUES (".substr($values, 0, -1).")", $connection) or exit(mysql_error());
	$rolle_id = mysql_insert_id();
	$spillinfo = get_spillinfo($_POST['spill_id']);
	if ($spillinfo['rollekonsept'] && $_POST['konsept_id']) {
		mysql_query("UPDATE `".$table_prefix."rollekonsept` SET rolle_id=".intval($rolle_id)." WHERE konsept_id=".intval($_POST['konsept_id'])." && spill_id=".intval($_POST['spill_id']), $connection);
	}
	return $rolle_id;
}

function slett_rolle() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."roller` WHERE rolle_id=".intval($_GET['slett_rolle'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."plott_medlemmer` WHERE medlem_id=".intval($_GET['slett_rolle'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."gruppe_roller` WHERE rolle_id=".intval($_GET['slett_rolle'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."filvedlegg` WHERE vedlagt='rolle' && vedlegg_id=".intval($_GET['slett_rolle'])." && spill_id=".intval($_GET['spill_id']), $connection);
}

function get_opd_roller($time, $spill_id) {
	$roller = get_roller($spill_id);
	if ($roller) {
		foreach ($roller as $rolle) {
			if ($rolle['oppdatert'] > $time) {
				$nyeroller[] = $rolle;
			}
		}
		if ($nyeroller) {
			return $nyeroller;
		}
	}
	return false;
}

function deaktiviser_rolle($rolle_id, $spill_id, $tekst) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."roller` SET status=".time().", status_id=".intval($_SESSION['person_id']).", status_tekst='".$tekst."' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function reaktiviser_rolle($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."roller` SET status=0, status_tekst='' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function oppdater_rollestatus($rolle_id, $spill_id, $tekst) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."roller` SET status_id=".intval($_SESSION['person_id']).", status_tekst='".$tekst."' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function get_rolle_spill($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$spilldata = mysql_query("SELECT spill_id FROM ".$table_prefix."roller WHERE rolle_id=".intval($rolle_id)." && spill_id != ".intval($spill_id), $connection);
	//for ($i = 0; $i < mysql_num_rows($spilldata); $i++) {
	//	$andrespill_id = mysql_fetch_assoc($spilldata);
	while ($andrespill_id = mysql_fetch_assoc($spilldata)) {
		$andrespill_info = get_spillinfo($andrespill_id['spill_id']);
		$andre_spill[$andrespill_id['spill_id']] = $andrespill_info['navn'];
		unset($spill_id, $andrespill_info);
	}
	return $andre_spill;
}

#############################################
# Funksjoner for behandling av rollekonsept #
#############################################

function get_roller_konsept($spill_id) {
	global $connection, $table_prefix;
	if ($_SESSION['rollekonseptorder']) {
		$order = $_SESSION['rollekonseptorder'];
	} else {
		$order = 'tittel';
	}
	$konsept = array();
	$sqlkonsept = mysql_query("SELECT * FROM `".$table_prefix."rollekonsept` WHERE spill_id=".intval($spill_id)." ORDER BY ".$order."", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlkonsept); $i++) {
	//	$konsept[] = mysql_fetch_assoc($sqlkonsept);
	while ($tuple = mysql_fetch_assoc($sqlkonsept)) {
		$konsept[] = $tuple;
	}
	if ($konsept) {
		return $konsept;
	}
	return false;
}

function get_rollekonsept($konsept_id, $spill_id) {
	global $connection, $table_prefix;
	if ($konsept = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."rollekonsept` WHERE konsept_id='".intval($konsept_id)."' && spill_id='".intval($spill_id)."'", $connection))) {
		return $konsept;
	}
	return false;
}

function get_konsept_rolle($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	if ($konsept = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."rollekonsept` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection))) {
		return $konsept;
	}
	return false;
}

function opprett_rollekonsept() {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."rollekonsept` VALUES (NULL,".intval($_POST['spill_id']).",".intval($_POST['rolle_id']).", ".intval($_POST['arrangor_id']).",".intval($_POST['spiller_id']).",'".$_POST['tittel']."','".$_POST['konsept']."',".time().")", $connection);
	return mysql_insert_id();
}

function oppdater_rollekonsept() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."rollekonsept` SET rolle_id=".intval($_POST['rolle_id']).", arrangor_id=".intval($_POST['arrangor_id']).", spiller_id=".intval($_POST['spiller_id']).", tittel='".$_POST['tittel']."', konsept='".$_POST['konsept']."', oppdatert=".time()." WHERE konsept_id=".intval($_POST['konsept_id'])." && spill_id=".intval($_POST['spill_id']), $connection);
}

function slett_rollekonsept() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."rollekonsept` WHERE konsept_id=".intval($_GET['slett_rollekonsept'])." && spill_id=".intval($_GET['spill_id']), $connection);
}

function get_opd_rollekonsept($time, $spill_id) {
	$rollekonsept = get_roller_konsept($spill_id);
	if ($rollekonsept) {
		foreach ($rollekonsept as $konsept) {
			if ($konsept['oppdatert'] > $time) {
				$nyekonsept[] = $konsept;
			}
		}
		if ($nyekonsept) {
			return $nyekonsept;
		}
	}
	return false;
}

#############################################
# Funksjoner for behandling av rolleforslag #
#############################################

function get_rolleforslag($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['rollemal'];
	if ($maldata = get_maldata($mal_id)) {
		foreach ($maldata as $value) {
			$malfields .= $value['fieldname'].",";
		}
	}
	$fields = "rolle_id,arrangor_id,spill_id,spiller,navn,".$malfields."intern_info,beskrivelse1,beskrivelse2,beskrivelse3,beskrivelse_gruppe,oppdatert,locked,godkjent";
	$rolle = mysql_fetch_assoc(mysql_query("SELECT $fields FROM `".$table_prefix."rolleforslag` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection));
	if ($rolle) {
		return $rolle;
	}
	return false;
}

function get_dummy_rolleforslag($spill_id) {
	$rolle_first['arrangor_id'] = "";
	$rolle_first['spiller'] = "";
	$rolle_first['navn'] = "";
	$rolle_last['intern_info'] = "";
	$rolle_last['beskrivelse1'] = "";
	$rolle_last['beskrivelse2'] = "";
	$rolle_last['beskrivelse3'] = "";
	$rolle_last['beskrivelse_gruppe'] = "";
	if ($mal = get_rollemal($spill_id)) {
		foreach ($mal as $fieldname=>$fieldinfo) {
			$field[$fieldname] = "";
		}
		return array_merge($rolle_first, $field, $rolle_last);
	}
	return array_merge($rolle_first, $rolle_last);
}

function get_roller_forslag($spill_id) {
	global $connection, $table_prefix;
	if ($_SESSION['rolleorder']) {
		$order = $_SESSION['rolleorder'];
	} else {
		$order = "navn";
	}
	if ($spill_id == "0") {
		$allespill = get_spill();
		foreach ($allespill as $spill) {
			if ($maldata = get_rollemal($spill['spill_id'])) {
				foreach ($maldata as $value) {
					$malfields .= $value['fieldname'].",";
				}
			}
			$sql = "SELECT rolle_id,arrangor_id,spill_id,spiller,navn,".$malfields."intern_info,beskrivelse1,beskrivelse2,beskrivelse3,beskrivelse_gruppe,oppdatert,locked,godkjent FROM `".$table_prefix."rolleforslag` WHERE spill_id=".intval($spill['spill_id'])." ORDER BY ".$order;
			$sqlroller = mysql_query($sql, $connection);
			//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
			//	$roller[] = mysql_fetch_assoc($sqlroller);
			while ($tuple = mysql_fetch_assoc($sqlroller)) {
				$roller[] = $tuple;
			}
			unset($sql, $malfields, $maldata);
		}
	} else {
		if ($maldata = get_rollemal($spill_id)) {
			foreach ($maldata as $value) {
				$malfields .= $value['fieldname'].",";
			}
		}
		$sql = "SELECT rolle_id,arrangor_id,spill_id,spiller,navn,".$malfields."intern_info,beskrivelse1,beskrivelse2,beskrivelse3,beskrivelse_gruppe,oppdatert,locked,godkjent FROM `".$table_prefix."rolleforslag` WHERE spill_id=".intval($spill_id)." ORDER BY ".$order;
		if ($sqlroller = mysql_query($sql, $connection)) {
			//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
			//	$roller[] = mysql_fetch_assoc($sqlroller);
			while ($tuple = mysql_fetch_assoc($sqlroller)) {
				$roller[] = $tuple;
			}
		}
	}
	if ($roller) {
		return $roller;
	}
	return false;
}

function get_spiller_rolleforslag($person_id, $spill_id) {
	global $connection, $table_prefix;
	if ($_SESSION['rolleorder']) {
		$order = $_SESSION['rolleorder'];
	} else {
		$order = "navn";
	}
	if ($spill_id == 0) {
		$sql = "SELECT * FROM `".$table_prefix."roller` WHERE spiller_id=".intval($person_id)." ORDER BY ".$order;
	} else {
		$sql = "SELECT * FROM `".$table_prefix."roller` WHERE spiller_id=".intval($person_id)." && spill_id=".intval($spill_id)." ORDER BY ".$order;
	}
	$sqlroller = mysql_query($sql, $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlroller); $i++) {
	//	$roller[] = mysql_fetch_assoc($sqlroller);
	while ($tuple = mysql_fetch_assoc($sqlroller)) {
		$roller[] = $tuple;
	}
	if ($roller) {
		return $roller;
	}
	return false;
}

function oppdater_rolleforslag() {
	global $connection, $table_prefix;
	$mal = get_spillmal($_POST['spill_id'], "rollemal");
	$maldata = get_maldata($mal['mal_id']);
	foreach ($maldata as $entry) {
		switch ($entry['type']) {
			case "check":
			if (!$_POST['rolle'][$entry['fieldname']]) {
				$sql .= $entry['fieldname']."='0',";
			}
			break;
			case "listmulti":
			$sql .= $entry['fieldname']."='".sql_serialize($_POST[$entry['fieldname']])."',";
			break;
		}
	}
	foreach ($_POST['rolle'] as $key=>$value) {
		$sql .= "$key='".$value."',";
	}
	$sql .= "oppdatert=".time().",";
	mysql_query("UPDATE `".$table_prefix."rolleforslag` SET ".substr($sql, 0, -1)." WHERE rolle_id=".intval($_POST['rolle_id'])." && spill_id=".intval($_POST['spill_id']), $connection);
	unlock_rolle($_POST['rolle_id'], $_POST['spill_id']);
}

function lock_rolleforslag($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$locked = serialize(array(time(), $_SESSION['person_id']));
	mysql_query("UPDATE `".$table_prefix."roller` SET locked='$locked' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function unlock_rolleforslag($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."roller` SET locked='0' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection);
}

function check_lock_rolleforslag($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$lockinfo = mysql_fetch_assoc(mysql_query("SELECT locked FROM `".$table_prefix."roller` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection));
	if ($lockinfo['locked'] != "0") {
		return unserialize($lockinfo['locked']);
	}
	return false;
}

function overfor_rolleforslag($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$rolle = get_rolleforslag($rolle_id, $spill_id);
	foreach ($rolle as $key=>$value) {
		switch ($key) {
			case "rolle_id":
			case "godkjent":
				break;
			case "spiller":
				if ($value > 0) {
					$fields .= "spiller_id, ";
					$values .= intval($value).", ";
				}
				break;
			default:
				$fields .= "$key, ";
				$values .= "'".addslashes($value)."', ";
		}
	}
	mysql_query("INSERT INTO `".$table_prefix."roller` (".substr(trim($fields), 0, -1).") VALUES (".substr(trim($values), 0, -1).")", $connection) or exit(mysql_error());
	$ny_rolle_id = mysql_insert_id();
	$godkjent = serialize(array(time(), $_SESSION['person_id'], $ny_rolle_id));
	mysql_query("UPDATE `".$table_prefix."rolleforslag` SET godkjent='".$godkjent."' WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id), $connection) or exit(mysql_error());
	return $ny_rolle_id;
}

function opprett_rolleforslag() {
	global $connection, $table_prefix;
	$mal = get_spillmal($_POST['spill_id'], "rollemal");
	$fields = "spill_id,";
	$values = intval($_POST['spill_id']).",";
/*	if ($maldata = get_maldata($mal['mal_id'])) {
		foreach ($maldata as $entry) {
			switch ($entry['type']) {
				case "check":
				if (!$_POST['rolle'][$entry['fieldname']]) {
					$fields .= "$entry[fieldname],";
					$values .= "'0',";
				}
				break;
			case "listmulti":
				$fields .= $entry['fieldname'].",";
				if ($_POST[$entry['fieldname']]) {
					$values .= "'".sql_serialize($_POST[$entry['fieldname']])."',";
				} else {
					$values .= "'".sql_serialize($_POST['rolle_'.$entry['fieldname']])."',";
				}
				break;
			}
		}
	}
*/
	foreach ($_POST['rolle'] as $key=>$value) {
		$fields .= "$key,";
		if (is_array($value)) {
			$values .= "'".sql_serialize($value)."',";
		} else {
			$values .= "'".$value."',";
		}
	}
	$fields .= "oppdatert,";
	$values .= time().",";
	mysql_query("INSERT INTO `".$table_prefix."rolleforslag` (".substr($fields, 0, -1).") VALUES (".substr($values, 0, -1).")", $connection) or exit(mysql_error());
	return mysql_insert_id();
}

function slett_rolleforslag() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."rolleforslag` WHERE rolle_id=".intval($_GET['slett_rolle'])." && spill_id=".intval($_GET['spill_id']), $connection);
}

function get_opd_rolleforslag($time, $spill_id) {
	$roller = get_roller_forslag($spill_id);
	if ($roller) {
		foreach ($roller as $rolle) {
			if ($rolle['oppdatert'] > $time) {
				$nyeroller[] = $rolle;
			}
		}
		if ($nyeroller) {
			return $nyeroller;
		}
	}
	return false;
}

###################################
# Funksjoner for kjentfolk-system #
###################################

function get_rolle_kjentfolk($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$sqlkjentfolk = mysql_query("SELECT * FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id)." && type='rolle'", $connection) or exit(mysql_error());
	//for ($i = 0; $i < mysql_num_rows($sqlkjentfolk); $i++) {
	//	$data = mysql_fetch_assoc($sqlkjentfolk);
	while ($data = mysql_fetch_assoc($sqlkjentfolk)) {
		if ($data) {
			$rolle = get_rolle($data['kjent_rolle_id'], $data['spill_id']);
			$kjentfolk[$data['kjent_rolle_id']] = $data;
			$kjentfolk[$data['kjent_rolle_id']]['navn'] = $rolle['navn'];
			$kjentfolk[$data['kjent_rolle_id']]['spiller_id'] = $rolle['spiller_id'];
			$kjentfolk[$data['kjent_rolle_id']]['beskrivelse1'] = $rolle['beskrivelse1'];
			$kjentfolk[$data['kjent_rolle_id']]['beskrivelse2'] = $rolle['beskrivelse2'];
			$kjentfolk[$data['kjent_rolle_id']]['beskrivelse3'] = $rolle['beskrivelse3'];
			$kjentfolk[$data['kjent_rolle_id']]['beskrivelse_gruppe'] = $rolle['beskrivelse_gruppe'];
		}
	}
	if ($kjentfolk) {
		return $kjentfolk;
	}
	return false;
}

function get_kjentfolk_data($rolle_id, $kjent_rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$kjentdata = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($rolle_id)." && kjent_rolle_id=".intval($kjent_rolle_id)." && spill_id=".intval($spill_id)." && type='rolle'", $connection));
	if (is_array($kjentdata)) {
		return $kjentdata;
	}
	return false;
}

function opprett_kjentfolk($rolle_id, $ny_kjentfolk, $spill_id, $level, $kjentgrunn) {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."kjentfolk` VALUES (".intval($rolle_id).",".intval($ny_kjentfolk).",".intval($spill_id).",".intval($level).",'".$kjentgrunn."', 'rolle')", $connection) or exit(mysql_error());
}

function slett_kjentfolk() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($_GET['rolle_id'])." && spill_id=".intval($_GET['spill_id'])." && kjent_rolle_id=".intval($_GET['slett_kjentfolk'])." && type='rolle'", $connection) or exit(mysql_error());
}

function oppdater_kjentfolk() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."kjentfolk` SET level=".intval($_POST['level']).", kjentgrunn='".$_POST['kjentgrunn']."' WHERE rolle_id=".intval($_POST['rolle_id'])." && kjent_rolle_id=".intval($_POST['oppdater_kjentfolk'])." && spill_id=".intval($_POST['spill_id'])." && type='rolle'", $connection) or exit(mysql_error());

}

function importer_kjentfolk($import_id, $rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$kjentfolk = get_rolle_kjentfolk($rolle_id, $spill_id); 
	$import_kjentfolk = get_rolle_kjentfolk($rolle_id, $import_id);
	if (is_array($import_kjentfolk)) {
		$imported = 0;
		foreach ($import_kjentfolk as $import_data) {
			if (!is_array($kjentfolk[$import_data['kjent_rolle_id']])) {
				$kjentrolle = get_rolle($import_data['kjent_rolle_id'], $spill_id);
				if (is_array($kjentrolle)) {
					opprett_kjentfolk($rolle_id, $import_data['kjent_rolle_id'], $spill_id, $import_data['level'], $import_data['kjentgrunn']);
					$imported++;
				}
			}
		}
		if ($imported == 0) {
			return false;
		}
		return true;
	}
	return false;
}

function opprett_kjentgruppe() {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."kjentfolk` VALUES (".intval($_POST['rolle_id']).",".intval($_POST['ny_kjentgruppe']).",".intval($_POST['spill_id']).",0,'".$_POST['kjentgrunn']."', 'gruppe')", $connection) or exit(mysql_error());
}

function slett_kjentgruppe() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($_GET['rolle_id'])." && spill_id=".intval($_GET['spill_id'])." && kjent_rolle_id=".intval($_GET['slett_kjentgruppe'])." && type='gruppe'", $connection) or exit(mysql_error());
}

function oppdater_kjentfolk_liste() {
	global $connection, $table_prefix;
	$roller = get_roller($_POST['spill_id']);
	$kjentfolk = get_rolle_kjentfolk($_POST['rolle_id'], $_POST['spill_id']);
	foreach ($kjentfolk as $kjentinfo) {
		if (!$_POST[$kjentinfo['kjent_rolle_id']]) {
			mysql_query("DELETE FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($_POST['rolle_id'])." && kjent_rolle_id=".intval($kjentinfo['kjent_rolle_id'])." && spill_id=".intval($_POST['spill_id'])." && type='rolle'", $connection) or exit(mysql_error());
		}
	}
	foreach ($_POST as $key=>$value) {
		if ($key != "rolle_id" && $key != "spill_id" && $key != "ny_kjentfolk_liste") {
			if ($kjentfolk[$key]) {
				mysql_query("UPDATE `".$table_prefix."kjentfolk` SET level=".intval($_POST[$key]['level']).", kjentgrunn='".$_POST[$key]['kjentgrunn']."' WHERE rolle_id=".intval($_POST['rolle_id'])." && kjent_rolle_id=".intval($key)." && spill_id=".intval($_POST['spill_id'])." && type='rolle'", $connection) or exit(mysql_error());
			} else {
				mysql_query("INSERT INTO `".$table_prefix."kjentfolk` VALUES (".intval($_POST['rolle_id']).", ".intval($key).", ".intval($_POST['spill_id']).", ".intval($_POST[$key]['level']).",'".$_POST[$key]['kjentgrunn']."', 'rolle')", $connection) or exit(mysql_error());
			}
		}
	}
}

function oppdater_folkkjent_liste() {
	global $connection, $table_prefix;
	$roller = get_roller($_POST['spill_id']);
	foreach ($roller as $rolle) {
		if (!$_POST[$rolle['rolle_id']]) {
			mysql_query("DELETE FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($rolle['rolle_id'])." && kjent_rolle_id=".intval($_POST['kjent_rolle_id'])." && spill_id=".intval($_POST['spill_id'])." && type='rolle'", $connection) or exit(mysql_error());
		}
	}
	foreach ($_POST as $key=>$value) {
		if ($key != "kjent_rolle_id" && $key != "spill_id" && $key != "ny_folkkjent_liste") {
			if (get_kjentfolk_data($key, $_POST['kjent_rolle_id'], $_POST[spill_id])) {
				mysql_query("UPDATE `".$table_prefix."kjentfolk` SET level=".intval($_POST[$key]['level']).", kjentgrunn='".$_POST[$key]['kjentgrunn']."' WHERE rolle_id=".intval($key)." && kjent_rolle_id=".intval($_POST['kjent_rolle_id'])." && spill_id=".intval($_POST['spill_id'])." && type='rolle'", $connection) or exit(mysql_error());
			} else {
				mysql_query("INSERT INTO `".$table_prefix."kjentfolk` VALUES (".intval($key).", ".intval($_POST['kjent_rolle_id']).", ".intval($_POST['spill_id']).", ".intval($_POST[$key]['level']).",'".$_POST[$key]['kjentgrunn']."', 'rolle')", $connection) or exit(mysql_error());
			}
		}
	}
}

function get_kjentgruppe_data($rolle_id, $gruppe_id, $spill_id) {
	global $connection, $table_prefix;
	$kjentdata = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($rolle_id)." && kjent_rolle_id=".intval($gruppe_id)." && spill_id=".intval($spill_id)." && type='gruppe'", $connection));
	if (is_array($kjentdata)) {
		return $kjentdata;
	}
	return false;
}

function oppdater_kjentgrupper_liste() {
	global $connection, $table_prefix;
	$grupper = get_grupper($_POST[spill_id]);
	$kjentgrupper = get_kjentgrupper($_POST['rolle_id'], $_POST['spill_id']);
	foreach ($kjentgrupper as $kjentinfo) {
		if (!$_POST[$kjentinfo['kjent_rolle_id']]) {
			mysql_query("DELETE FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($_POST['rolle_id'])." && kjent_rolle_id=".intval($kjentinfo['kjent_rolle_id'])." && spill_id=".intval($_POST['spill_id'])." && type='gruppe'", $connection) or exit(mysql_error());
		}
	}
	foreach ($_POST as $key=>$value) {
		if ($key != "rolle_id" && $key != "spill_id" && $key != "ny_kjentgrupper_liste") {
			if ($kjentgrupper[$key]) {
				mysql_query("UPDATE `".$table_prefix."kjentfolk` SET kjentgrunn='".$_POST[$key]['kjentgrunn']."' WHERE rolle_id=".intval($_POST['rolle_id'])." && kjent_rolle_id=".intval($key)." && spill_id=".intval($_POST['spill_id'])." && type='gruppe'", $connection) or exit(mysql_error());
			} else {
				mysql_query("INSERT INTO `".$table_prefix."kjentfolk` VALUES (".intval($_POST['rolle_id']).", ".intval($key).", ".intval($_POST['spill_id']).", 0,'".$_POST[$key]['kjentgrunn']."', 'gruppe')", $connection) or exit(mysql_error());
			}
		}
	}
}

function get_kjentgrupper($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$sqlgrupper = mysql_query("SELECT * FROM `".$table_prefix."kjentfolk` WHERE rolle_id=".intval($rolle_id)." && spill_id=".intval($spill_id)." && type='gruppe'", $connection) or exit(mysql_error());
	//for ($i = 0; $i < mysql_num_rows($sqlgrupper); $i++) {
	//	$data = mysql_fetch_assoc($sqlgrupper);
	while ($data = mysql_fetch_assoc($sqlgrupper)) {
		if ($data) {
			$gruppe = get_gruppe($data['kjent_rolle_id'], $data['spill_id']);
			$kjentgrupper[$data['kjent_rolle_id']] = $data;
			$kjentgrupper[$data['kjent_rolle_id']]['gruppe_id'] = $gruppe['gruppe_id'];
			$kjentgrupper[$data['kjent_rolle_id']]['navn'] = $gruppe['navn'];
			$kjentgrupper[$data['kjent_rolle_id']]['beskrivelse'] = $gruppe['beskrivelse'];
		}
	}
	if ($kjentgrupper) {
		return $kjentgrupper;
	}
	return false;
}

####################################
# Funksjoner for person-behandling #
####################################


function get_arrangorer() {
	global $connection, $table_prefix;
	if ($_SESSION['arrangororder']) {
		switch($_SESSION['arrangororder']) {
			case "alder":
			$order = "fodt desc";
			break;
			case "alder desc":
			$order = "fodt";
			break;
			default:
			$order = $_SESSION['arrangororder'];
		}
	} else {
		$order = "etternavn";
	}
	$sqlpersoner = mysql_query("SELECT `".$table_prefix."personer`.person_id,type,etternavn,fornavn,fodt,alder,kjonn,adresse,postnr,poststed,telefon,mobil,email,mailpref,hensyn,intern_info,bilde,bruker_id,brukernavn,passord,secret,level,nowlog,lastlog,locked FROM `".$table_prefix."personer` LEFT JOIN `".$table_prefix."brukere` ON `".$table_prefix."brukere`.person_id=`".$table_prefix."personer`.person_id WHERE type!='spiller' ORDER BY $order", $connection);
	$arrangorer = array();
	//for ($i = 0; $i < mysql_num_rows($sqlpersoner); $i++) {
	//	$arrangorer[] = mysql_fetch_assoc($sqlpersoner);
	while ($tuple = mysql_fetch_assoc($sqlpersoner)) {
		$arrangorer[] = $tuple;
	}
	return $arrangorer;
}

function get_arrangor($person_id) {
	global $connection, $table_prefix;
	$arrangor = mysql_fetch_assoc(mysql_query("SELECT `".$table_prefix."personer`.person_id,type,etternavn,fornavn,fodt,alder,kjonn,adresse,postnr,poststed,telefon,mobil,email,mailpref,hensyn,intern_info,bilde,bruker_id,brukernavn,passord,secret,level,nowlog,lastlog,locked FROM `".$table_prefix."personer` LEFT JOIN `".$table_prefix."brukere` ON `".$table_prefix."brukere`.person_id=`".$table_prefix."personer`.person_id WHERE `".$table_prefix."personer`.person_id=".intval($person_id)." && type='arrangor'", $connection));
	if (count($arrangor) != 1) {
		return $arrangor;
	}
	return false;
}


function get_spillere() {
	global $connection, $table_prefix;
	if ($_SESSION['personorder']) {
		switch($_SESSION['personorder']) {
			case "alder":
			$order = "fodt desc";
			break;
			case "alder desc":
			$order = "fodt";
			break;
			default:
			$order = $_SESSION['personorder'];
		}
	} else {
		$order = "etternavn";
	}
	$sqlpersoner = mysql_query("SELECT * FROM `".$table_prefix."personer` WHERE type='spiller' ORDER BY $order", $connection);
	$spillere = array();
	//for ($i = 0; $i < mysql_num_rows($sqlpersoner); $i++) {
	//	$spiller = mysql_fetch_assoc($sqlpersoner);
	while($spiller = mysql_fetch_assoc($sqlpersoner)) {
		$spillere[$spiller['person_id']] = $spiller;
	}
	return $spillere;
}

function get_personer() {
	global $connection, $table_prefix;
	if ($_SESSION['personorder']) {
		switch($_SESSION['personorder']) {
			case "alder":
			$order = "fodt desc";
			break;
			case "alder desc":
			$order = "fodt";
			break;
			default:
			$order = $_SESSION['personorder'];
		}
	} else {
		$order = "etternavn";
	}
	$sqlpersoner = mysql_query("SELECT * FROM `".$table_prefix."personer` ORDER BY $order", $connection);
	$personer = array();
	//for ($i = 0; $i < mysql_num_rows($sqlpersoner); $i++) {
	//	$person = mysql_fetch_assoc($sqlpersoner);
	while ($person = mysql_fetch_assoc($sqlpersoner)) {
		$personer[$person['person_id']] = $person;
	}
	return $personer;
}

function oppdater_personinfo() {
	global $connection, $table_prefix, $config, $LANG;
	if (!$_POST['person']['poststed'] && $config['use_autoregion']) {
		if (!$_POST['person']['poststed'] = get_poststed($_POST['person']['postnr'])) {
			$_POST['person']['poststed'] = $LANG['MISC']['unknown'];
		}
	}
	foreach ($_POST['person'] as $key=>$value) {
		switch ($key) {
			case "person_id":
			case "spill_id":
			case "whereiwas":
			case "type":
			case "edited":
			case "bilde":
			case "slettbilde":
			case "dag":
			case "mnd":
			case "aar":
			break;
			default:
			$sql .= "$key='$value',";
		}
	}
	if (!intval($_POST['person']['aar'])) {
		$_POST['person']['aar'] = "2000";
	}
	if (checkdate($_POST['person']['mnd'], $_POST['person']['dag'], $_POST['person']['aar'])) {
		$sql .= "fodt='".$_POST['person']['aar']."-".$_POST['person']['mnd']."-".$_POST['person']['dag']."',";
	} else {
		$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['invalid_date']."</span><br><br>";
		$sql .= "fodt='0000-00-00',";
	}
	mysql_query("UPDATE `".$table_prefix."personer` SET ".substr($sql, 0, -1)." WHERE person_id=".intval($_POST['person_id']), $connection) or exit(mysql_error());
}

function opprett_person() {
	global $connection, $table_prefix, $config, $LANG;
	if (!$_POST['person']['poststed'] && $config['use_autoregion']) {
		if (!$_POST['person']['poststed'] = get_poststed($_POST['person']['postnr'])) {
			$_POST['person']['poststed'] = $LANG['MISC']['unknown'];
		}
	}
	foreach ($_POST['person'] as $key=>$value) {
		switch ($key) {
			case "bilde":
			case "type":
			case "slettbilde":
			case "dag":
			case "mnd":
			case "aar":
			break;
			default:
			$fields .= "$key,";
			$values .= "'$value',";
		}
	}

	if (!intval($_POST['person']['aar'])) {
		$_POST['person']['aar'] = "2000";
	}
	if (checkdate($_POST['person']['mnd'], $_POST['person']['dag'], $_POST['person']['aar'])) {
		$fields .= "fodt,";
		$values .= "'".$_POST['person']['aar']."-".$_POST['person']['mnd']."-".$_POST['person']['dag']."',";
	} else {
		$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['invalid_date']."</span><br><br>";
		$fields .= "fodt,";
		$values .= "'0000-00-00',";
	}
	if (!$_POST['person']['type']) {
		$fields .= "type,";
		$values .= "'spiller',";
	} else {
		$fields .= "type,";
		$values .= "'".$_POST['person']['type']."',";
	}
	mysql_query("INSERT INTO `".$table_prefix."personer` (".substr($fields, 0, -1).") VALUES (".substr($values, 0, -1).")", $connection) or exit(mysql_error());
	$person_id = mysql_insert_id();
	if (is_file($_FILES['bilde']['tmp_name'])) {
		$bildenavn = mkfilename($person['fornavn']." ".$person['etternavn']." ".time()).".jpg";
		$bildepath = "images/personer/".$bildenavn;
		$tmpbilde = getcwd()."/tmp/".$bildenavn;
		move_uploaded_file($_FILES['bilde']['tmp_name'],$tmpbilde);
		if (resizeimg($tmpbilde, $bildepath, $_FILES['bilde']['type'], "120", "150")) {
			add_mugshot($person_id, $bildenavn);
			update_personmug($person_id, $bildenavn);
		} else {
			$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['image_convert_error']."</span><br>";
		}
	}
	return $person_id;
}

function get_person($person_id) {
	global $connection, $table_prefix;
	$person = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."personer` WHERE person_id=".intval($person_id), $connection));
	return $person;
}

function spiller_til_arrangor() {
	global $connection, $table_prefix, $config;
	if (is_koordinator()) {
		mysql_query("UPDATE `".$table_prefix."personer` SET type='arrangor' WHERE person_id=".intval($_GET['tilarrangor']), $connection);
		if ($config['calamar_installed'] == 1) {
			$_POST['nylevel'] = 5;
			$_POST['person_id'] = $_GET['tilarrangor'];
			nytt_level();
		}
	}
}

function arrangor_til_spiller() {
	global $connection, $table_prefix, $LANG, $config;
	if (is_koordinator()) {
		$roller = get_spiller_roller($_GET['tilspiller'], 0);
		if ($roller) {
			foreach ($roller as $rolle) {
				if (!get_paamelding($rolle['spiller_id'], $rolle['spill_id'])) {
					mysql_query("INSERT INTO `".$table_prefix."paameldinger` (person_id, spill_id, betalt, annet) VALUES (".intval($rolle['spiller_id']).", ".intval($rolle['spill_id']).", 1, '".$LANG['MESSAGE']['player_was_organizer']."')", $connection);
				}
			}
		}
		mysql_query("UPDATE `".$table_prefix."personer` SET type='spiller' WHERE person_id=".intval($_GET['tilspiller']), $connection);
		if ($config['calamar_installed'] == 1) {
			$_POST['nylevel'] = 1;
			$_POST['person_id'] = $_GET['tilspiller'];
			nytt_level();
		} else {
			slett_bruker($_GET['tilspiller']);
		}
	}
}

function slett_spiller() {
	global $connection, $table_prefix;
	if (is_koordinator()) {
		mysql_query("DELETE FROM `".$table_prefix."personer` WHERE person_id=".intval($_GET['slett_spiller']), $connection);
		mysql_query("DELETE FROM `".$table_prefix."paameldinger` WHERE person_id=".intval($_GET['slett_spiller']), $connection);
		mysql_query("DELETE FROM `".$table_prefix."brukere` WHERE person_id=".intval($_GET['slett_spiller']), $connection);
		mysql_query("UPDATE `".$table_prefix."roller` SET spiller_id=0 WHERE spiller_id=".intval($_GET['slett_spiller']), $connection);
	}
}

function slett_arrangor() {
	global $connection, $table_prefix;
	if (is_koordinator() && is_modifiable($_POST['slett_arrangor'])) {
		mysql_query("DELETE FROM `".$table_prefix."personer` WHERE person_id=".intval($_GET['slett_arrangor']), $connection);
		mysql_query("DELETE FROM `".$table_prefix."paameldinger` WHERE person_id=".intval($_GET['slett_arrangor']), $connection);
		mysql_query("DELETE FROM `".$table_prefix."brukere` WHERE person_id=".intval($_GET['slett_arrangor']), $connection);
		mysql_query("UPDATE `".$table_prefix."roller` SET spiller_id=0 WHERE spiller_id=".intval($_GET['slett_arrangor']), $connection);
	}
}

function update_personmug($person_id, $image) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."personer` SET bilde='".$image."' WHERE person_id=".intval($person_id), $connection);
	return true;
}

function get_mugshots($person_id, $type = 'person') {
	global $connection, $table_prefix;
	if ($result = mysql_query("SELECT * FROM `".$table_prefix."mugshots` WHERE person_id=".intval($person_id)." && type='".$type."'", $connection)) {
		//for ($i = 0; $i < mysql_num_rows($result); $i++) {
		//	$mugshot = mysql_fetch_assoc($result);
		while ($mugshot = mysql_fetch_assoc($result)) {
			$mugshots[] = $mugshot['image'];
		}
		if (is_array($mugshots)) {
			return $mugshots;
		}
	}
	return false;
}

function add_mugshot($person_id, $image, $type = 'person') {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."mugshots` VALUES (".intval($person_id).", '".$image."', '".$type."')", $connection);
	if (mysql_affected_rows > 0) {
		return true;
	}
	return false;
}

function delete_mugshot($image) {
	global $connection, $table_prefix;
	if (@unlink("images/personer/".$image)) {
		mysql_query("DELETE FROM `".$table_prefix."mugshots` WHERE image='".$image."'", $connection);
		if (mysql_affected_rows($connection) > 0) {
			return true;
		}
	}
	return false;
}

function get_all_mugshots() {
	global $connection, $table_prefix;
	if ($result = mysql_query("SELECT * FROM `".$table_prefix."mugshots`", $connection)) {
		//for ($i = 0; $i < mysql_num_rows($result); $i++) {
		//	$mugshot = mysql_fetch_assoc($result);
		while ($mugshot = mysql_fetch_assoc($result)) {
			$mugshots[] = $mugshot['image'];
		}
		if (is_array($mugshots)) {
			return array_unique($mugshots);
		}
	}
	return false;
}

function is_mugshot($image) {
	global $connection, $table_prefix;
	if (mysql_num_rows(mysql_query("SELECT * FROM `".$table_prefix."mugshots` WHERE image='".$image."'", $connection)) > 0) {
		return true;
	}
	return false;
}

function remove_mugshot($person_id, $image, $type = 'person') {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."mugshots` WHERE image='".$image."' && person_id=".intval($person_id)." && type='".$type."'", $connection);
	if (mysql_affected_rows($connection) > 0) {
		return true;
	}
	return false;
}

#####################################
# Funksjoner for kontakt-behandling #
#####################################

function get_kontakter() {
	global $connection, $table_prefix;
	if ($_SESSION['kontaktorder']) {
		$order = $_SESSION['kontaktorder'];
	} else {
		$order = "navn";
	}
	$sqlkontakter = mysql_query("SELECT * FROM `".$table_prefix."kontakter` ORDER BY $order", $connection);
	$kontakter = array();
	//for ($i = 0; $i < mysql_num_rows($sqlkontakter); $i++) {
	//	$kontakt = mysql_fetch_assoc($sqlkontakter);
	while ($kontakt = mysql_fetch_assoc($sqlkontakter)) {
		$kontakter[$kontakt['kontakt_id']] = $kontakt;
	}
	return $kontakter;
}

function oppdater_kontaktinfo() {
	global $connection, $table_prefix, $config, $LANG;
	if (!$_POST['kontakt']['poststed'] && $config['use_autoregion']) {
		if (!$_POST['kontakt']['poststed'] = get_poststed($_POST['kontakt']['postnr'])) {
			$_POST['kontakt']['poststed'] = $LANG['MISC']['unknown'];
		}
	}
	foreach ($_POST['kontakt'] as $key=>$value) {
		switch ($key) {
			case "kontakt_id":
			case "spill_id":
			case "whereiwas":
			case "type":
			case "edited":
			case "bilde":
			case "slettbilde":
			break;
			default:
			$sql .= "$key='$value',";
		}
	}
	if ($_POST['slettbilde']) {
		$img = mysql_fetch_assoc(mysql_query("SELECT bilde FROM `".$table_prefix."kontakter` WHERE kontakt_id=".intval($_POST['kontakt_id']), $connection));
		$imgfile = basename($img['bilde']);
		delete_mugshot($imgfile); 
		$sql .= "bilde='',";
	} elseif (is_file($_FILES['bilde']['tmp_name'])) {
		$oldimg = mysql_fetch_assoc(mysql_query("SELECT bilde FROM `".$table_prefix."kontakter` WHERE kontakt_id=".intval($_POST['kontakt_id']), $connection));
		$oldimgfile = basename($oldimg['bilde']);
		$bildenavn = mkfilename($_POST['kontakt']['navn']." ".time()).".jpg";
		$bildepath = "images/personer/$bildenavn";
		$tmpbilde = getcwd()."/tmp/".$bildenavn;
		move_uploaded_file($_FILES['bilde']['tmp_name'], $tmpbilde);
		if (resizeimg($tmpbilde, $bildepath, $_FILES['bilde']['type'], "120", "150")) {
			if ($oldimgfile) {
				delete_mugshot($oldimgfile);
			}
			$sql .= "bilde='".$bildenavn."',";
			add_mugshot($_POST['kontakt_id'], $bildenavn, 'kontakt');
		} else {
			$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['image_convert_error']."</span><br>";
		}
	}
	mysql_query("UPDATE `".$table_prefix."kontakter` SET ".substr($sql, 0, -1)." WHERE kontakt_id=".intval($_POST['kontakt_id']), $connection) or exit(mysql_error());
}

function opprett_kontakt() {
	global $connection, $table_prefix, $config, $LANG;
	if (!$_POST['kontakt']['poststed'] && $config['use_autoregion']) {
		if (!$_POST['kontakt']['poststed'] = get_poststed($_POST['kontakt']['postnr'])) {
			$_POST['kontakt']['poststed'] = $LANG['MISC']['unknown'];
		}
	}
	foreach ($_POST['kontakt'] as $key=>$value) {
		switch ($key) {
			case "bilde":
			case "slettbilde":
			break;
			default:
			$fields .= "$key,";
			$values .= "'$value',";
		}
	}

	if (is_file($_FILES['bilde']['tmp_name'])) {
		$bildenavn = mkfilename($_POST['kontakt']['navn']." ".time()).".jpg";
		$bildepath = "images/personer/$bildenavn";
		$tmpbilde = getcwd()."/tmp/".$bildenavn;
		move_uploaded_file($_FILES['bilde']['tmp_name'], $tmpbilde);
		if (resizeimg($tmpbilde, $bildepath, $_FILES['bilde']['type'], "120", "150")) {
			$fields .= "bilde,";
			$values .= "'".$bildenavn."',";
		}
	}
	mysql_query("INSERT INTO `".$table_prefix."kontakter` (".substr($fields, 0, -1).") VALUES (".substr($values, 0, -1).")", $connection) or exit(mysql_error());
	$kontakt_id = mysql_insert_id();
	return $kontakt_id;
}

function get_kontakt($kontakt_id) {
	global $connection, $table_prefix;
	$kontakt = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."kontakter` WHERE kontakt_id=".intval($kontakt_id), $connection));
	return $kontakt;
}

function slett_kontakt() {
	global $connection, $table_prefix;
	$kontakt = get_kontakt($_GET['slett_kontakt']);
	mysql_query("DELETE FROM `".$table_prefix."kontakter` WHERE kontakt_id=".intval($_GET['slett_kontakt']), $connection);
	if (!is_mugshot($kontakt['bilde'])) {
		delete_mugshot($kontakt['bilde']);
	}
	
}


###################################
# Funksjoner for brukerbehandling #
###################################


function get_bruker($person_id) {
	global $connection, $table_prefix;
	$bruker = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."brukere` WHERE person_id=".intval($person_id), $connection));
	if ($bruker) {
		return $bruker;
	}
	return false;
}

function get_brukere() {
	global $connection, $table_prefix;
	$sqlbrukere = mysql_query("SELECT * FROM `".$table_prefix."brukere`", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlbrukere); $i++) {
	//	$data = mysql_fetch_assoc($sqlbrukere);
	while ($data = mysql_fetch_assoc($sqlbrukere)) {
		$brukere[$data['bruker_id']] = $data;
	}
	if ($brukere) {
		return $brukere;
	}
	return false;
}

function slett_bruker($person_id) {
	global $connection, $table_prefix;
	if (is_koordinator()) {
		mysql_query("DELETE FROM `".$table_prefix."brukere` WHERE person_id=".intval($person_id), $connection);
	}
}

function opprett_bruker() {
	global $connection, $table_prefix;
	if (is_koordinator()) {
		if (!$_POST['nybrukernavn'] || !$_POST['nypassord']) {
			return false;
		}
		if ($_POST['nypassord'] != $_POST['nyconfirm']) {
			return false;
		}
		$passcrypt = md5($_POST['nypassord']);
		$secret = md5(microtime().$_POST['nybrukernavn'].$_POST['nypassord'].uniqid(""));
		mysql_query("INSERT INTO `".$table_prefix."brukere` VALUES (NULL, ".intval($_POST['person_id']).", '".$_POST['nybrukernavn']."', '".$passcrypt."', '".$secret."', ".intval($_POST['nylevel']).", 0, 0, '', 0)", $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function nytt_brukernavn() {
	global $connection, $table_prefix;
	if (is_modifiable($_POST['person_id'])) {
		if (!$_POST['nybrukernavn']) {
			return false;
		}
		mysql_query("UPDATE `".$table_prefix."brukere` SET brukernavn='".$_POST['nybrukernavn']."' WHERE person_id=".intval($_POST['person_id']), $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function nytt_level() {
	global $connection, $table_prefix;
	if (is_koordinator() && is_modifiable()) {
		if (!$_POST['nylevel']) {
			return false;
		}
		mysql_query("UPDATE `".$table_prefix."brukere` SET level=".intval($_POST['nylevel'])." WHERE person_id=".intval($_POST['person_id']), $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function nytt_passord() {
	global $connection, $table_prefix;
	if (is_modifiable()) {
		if (!$_POST['nypassord']) {
			return false;
		}
		if ($_POST['nypassord'] != $_POST['nyconfirm']) {
			return false;
		}
		$passcrypt = md5($_POST['nypassord']);
		mysql_query("UPDATE `".$table_prefix."brukere` SET passord='".$passcrypt."' WHERE person_id=".intval($_POST['person_id']), $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function unlock_bruker() {
	global $connection, $table_prefix;
	if (is_admin()) {
		mysql_query("UPDATE `".$table_prefix."brukere` SET locked=0 WHERE person_id=".intval($_GET['unlock_bruker']), $connection);
	}
}

function lock_bruker() {
	global $connection, $table_prefix;
	if (is_admin() && !is_last_admin($_GET['lock_bruker'])) {
		mysql_query("UPDATE `".$table_prefix."brukere` SET locked=1 WHERE person_id=".intval($_GET['lock_bruker']), $connection);
	}
}

function is_last_admin($person_id) {
	global $connection, $table_prefix;
	static $admins = array();
	if (empty($admins)) {
		$sql = mysql_query("SELECT person_id FROM `".$table_prefix."brukere` WHERE level=20 && locked!=1", $connection);
		while(list($tmp) = mysql_fetch_row($sql)) {
			$admins[] = $tmp;
		}
	}
	if (empty($admins) || count($admins) > 1) {
		// Either invalid condition or more than one admin, so not the last.
		return false;
	}
	return ($admins[0] == $person_id);
}

function ensure_admin() {
	global $connection, $table_prefix;
	list($numadmins) = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM `".$table_prefix."brukere` where level=20 && locked!=1", $connection));
	if (!$numadmins) {
		create_admins();
	}
}

// Emergency measure; will turn all users into admins
function create_admins() {
	global $connection, $table_prefix, $LANG;
	mysql_query("UPDATE `".$table_prefix."brukere` SET level=20 WHERE locked!=1", $connection);
	$_SESSION['message'] = $LANG['MESSAGE']['no_admins_all_upgraded'];
}

###############################
# Funksjoner for mal-systemet #
###############################


function get_malentry($fieldname, $mal_id) {
	global $connection, $table_prefix;
	if ($fieldinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."tabellmaler_data` WHERE fieldname='".$fieldname."' && mal_id=".intval($mal_id), $connection))) {
		return $fieldinfo;
	}
	return false;
}

function get_fields($table) {
	global $connection, $sqlbase;
	$sqlfields = mysql_list_fields($sqlbase, $table, $connection);
	$numfields = mysql_num_fields($sqlfields);
	for ($i = 0; $i < $numfields; $i++) {
		$fields[$i] = mysql_field_name($sqlfields, $i);
	}
	return $fields;
}

function get_malinfo($mal_id) {
	global $connection, $table_prefix;
	$mal = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."tabellmaler` WHERE mal_id=".intval($mal_id), $connection));
	return $mal;
}

function get_maldata($mal_id) {
	global $connection, $table_prefix;
	$maldata = array();
	$sqlmal = mysql_query("SELECT * FROM `".$table_prefix."tabellmaler_data` WHERE mal_id=".intval($mal_id)." ORDER BY pri", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlmal); $i++) {
	//	$data = mysql_fetch_assoc($sqlmal);
	while ($data = mysql_fetch_assoc($sqlmal)) {
		$maldata[$data['fieldname']] = $data;
	}
	return $maldata;
}

function get_paameldingsmal($spill_id) {
	return get_mal($spill_id, "paameldingsmal");
}

function get_rollemal($spill_id) {
	return get_mal($spill_id, "rollemal");
}

function get_mal($spill_id, $type) {
	global $connection, $table_prefix;
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo[$type];
	return get_maldata($mal_id);
}

function get_spillmal($spill_id, $maltype) {
	global $connection, $table_prefix;
	$spillinfo = get_spillinfo($spill_id);
	$mal = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."tabellmaler` WHERE mal_id=".intval($spillinfo[$maltype]), $connection));
	return $mal;
}

function get_maler($type = 0) {
	global $connection, $table_prefix;
	if (!$_SESSION['malorder']) {
		$order = "navn";
	} else {
		$order = $_SESSION['malorder'];
	}
	if ($type == 0) {
		$sqlmaler = mysql_query("SELECT * FROM `".$table_prefix."tabellmaler` ORDER BY ".$order, $connection);
	} else {
		$sqlmaler = mysql_query("SELECT * FROM `".$table_prefix."tabellmaler` WHERE type='".$type."' ORDER BY ".$order, $connection);
	}
	$maler = array();
	//for ($i = 0; $i < mysql_num_rows($sqlmaler); $i++) {
	//	$mal = mysql_fetch_assoc($sqlmaler);
	while ($mal = mysql_fetch_assoc($sqlmaler)) {
		foreach ($mal as $key=>$value) {
			$maler[$mal['mal_id']][$key] = $value;
		}
	}
	return $maler;
}

function opprett_mal() {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."tabellmaler` (navn, type) VALUES ('".$_POST['navn']."', '".$_POST['type']."')", $connection) or exit(mysql_error());
	$mal_id = mysql_insert_id();
	return $mal_id;
}

function oppdater_mal() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."tabellmaler` SET navn='".$_POST['navn']."' WHERE mal_id=".intval($_POST['editedmal']), $connection) or exit(mysql_error());
}

function slett_malfelt() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."tabellmaler_data` WHERE mal_id=".intval($_GET['mal_id'])." && fieldname='".$_GET['slettfelt']."'", $connection);
	mal_rehash_pri($_GET['mal_id']);
}

function opprett_malfelt($mal_id) {
	global $connection, $table_prefix;
	$mal = get_malinfo($mal_id);
	if (!$_POST['mand']) {
		$mand = "0";
	} else {
		$mand = "1";
	}
	if (!$_POST['intern']) {
		$intern = "0";
	} else {
		$intern = "1";
	}

	switch ($mal['type']) {
		case "paamelding":
		$table = $table_prefix."paameldinger";
		$allfields = get_fields($table);
		break;
		case "rolle":
		$table = $table_prefix."roller";
		$allfields = get_fields($table);
		break;
	}
	foreach ($allfields as $value) {
		if (strstr($value, "field")) {
			$fields[] = $value;
		}
	}
	if (!$fields) {
		$lastfield = "field0";
	}
	foreach ($fields as $key => $fieldname) {
		if (!$ourfield) {
			if (count(get_malentry($fieldname, $mal_id)) == 1) {
				$ourfield = $fieldname;
			}
			$lastfield = $fieldname;
		}
	}
	if (!$ourfield) {
		if ($lastfield == "field0") {
			$ourfield = "field1";
			if ($table == $table_prefix."roller") {
				$lastfield = "navn";
			} else {
				$lastfield = "betalt";
			}
		} else {
			$lastnum = substr($lastfield, 5)+1;
			$ourfield = "field$lastnum";
		}
		mysql_query("ALTER TABLE $table ADD COLUMN $ourfield longtext AFTER $lastfield", $connection) or exit(mysql_error());
		if ($table == $table_prefix."roller") {
			mysql_query("ALTER TABLE `".$table_prefix."rolleforslag` ADD COLUMN $ourfield longtext AFTER $lastfield", $connection) or exit(mysql_error());
		}
	}
	switch($_POST['type']) {
		case "inline":
		$extras = $_POST['default_width'];
		break;
		case "inlinebox":
		$extras = $_POST['default_heigth'].";".$_POST['default_width'];
		break;
		case "box":
		$extras = $_POST['default_heigth'];
		break;
		case "listsingle":
		case "listmulti":
		case "radio":
		$newextra = explode(",", $_POST['choices']);
		for ($i = 0; $i < count($newextra); $i++) {
			$extras .= trim($newextra[$i]).";";
		}
		$extras = "$i;".substr($extras, 0, -1);
		break;
		case "check":
		$extras = $_POST['on_value'].";".$_POST['off_value'];
		break;
		case "calc":
		$extras = $_POST['sourcefield'].";".$_POST['calculation'];
		break;
		case "dots":
		$extras = $_POST['max_dots'];
		break;
		case "separator":
		$_POST['fieldtitle'] = "------";
		case "header":
		$extras = "";
		break;
	}
	$lastprifield = mysql_fetch_assoc(mysql_query("SELECT pri FROM `".$table_prefix."tabellmaler_data` WHERE mal_id=".intval($mal_id)." ORDER BY pri DESC LIMIT 1", $connection));
	$pri = $lastprifield['pri'] + 1;
	mysql_query("INSERT INTO `".$table_prefix."tabellmaler_data` VALUES (".intval($mal_id).", '".$_POST['type']."', '".$ourfield."', '".$_POST['fieldtitle']."', '".$extras."', '".$_POST['helptext']."', ".intval($pri).", ".intval($mand).", ".intval($intern).")", $connection) or exit(mysql_error());
}

function oppdater_malfelt() {
	global $connection, $table_prefix;
	if (!$_POST['mand']) {
		$mand = "0";
	} else {
		$mand = "1";
	}
	if (!$_POST['intern']) {
		$intern = "0";
	} else {
		$intern = "1";
	}
	switch($_POST['type']) {
		case "inline":
		$extras = $_POST['default_width'];
		break;
		case "inlinebox":
		$extras = $_POST['default_heigth'].";".$_POST['default_width'];
		break;
		case "box":
		$extras = $_POST['default_heigth'];
		break;
		case "listsingle":
		case "listmulti":
		case "radio":
		$newextra = explode(",", $_POST['choices']);
		for ($i = 0; $i < count($newextra); $i++) {
			$extras .= trim($newextra[$i]).";";
		}
		$extras = "$i;".substr($extras, 0, -1);
		break;
		case "check":
		$extras = $_POST['on_value'].";".$_POST['off_value'];
		break;
		case "calc":
		$extras = $_POST['sourcefield'].";".$_POST['calculation'];
		break;
		case "dots":
		$extras = $_POST['max_dots'];
		break;
		case "separator":
		$_POST['fieldtitle'] = "------";
		case "header":
		$extras = "";
		break;
	}
	mysql_query("UPDATE `".$table_prefix."tabellmaler_data` SET type='".$_POST['type']."', fieldtitle='".$_POST['fieldtitle']."', extra='$extras', hjelp='".$_POST['helptext']."', mand=".intval($mand).", intern=".intval($intern)." WHERE mal_id=".intval($_POST['mal_id'])." && fieldname='".$_POST['editedfelt']."'", $connection) or exit(mysql_error());
}

function mal_pri_ned($fieldname, $mal_id) {
	global $connection, $table_prefix;
	$pri_ned = get_malentry($fieldname, $mal_id);
	$pri_opp = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."tabellmaler_data` WHERE mal_id='$mal_id' && pri='".($pri_ned['pri'] - 1)."'", $connection));
	if ($pri_opp) {
		mysql_query("UPDATE `".$table_prefix."tabellmaler_data` SET pri=".($pri_ned['pri'] - 1)." WHERE fieldname='".$pri_ned['fieldname']."' && mal_id=".intval($mal_id), $connection) or exit(mysql_error());
		mysql_query("UPDATE `".$table_prefix."tabellmaler_data` SET pri=".intval($pri_ned['pri'])." WHERE fieldname='".$pri_opp['fieldname']."' && mal_id=".intval($mal_id), $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function mal_pri_opp($fieldname, $mal_id) {
	global $connection, $table_prefix;
	$pri_opp = get_malentry($fieldname, $mal_id);
	$pri_ned = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."tabellmaler_data` WHERE mal_id=".intval($mal_id)." && pri=".($pri_opp['pri'] + 1), $connection));
	if ($pri_ned) {
		mysql_query("UPDATE `".$table_prefix."tabellmaler_data` SET pri=".($pri_opp['pri'] + 1)." WHERE fieldname='".$pri_opp['fieldname']."' && mal_id=".intval($mal_id), $connection) or exit(mysql_error());
		mysql_query("UPDATE `".$table_prefix."tabellmaler_data` SET pri=".intval($pri_opp['pri'])." WHERE fieldname='".$pri_ned['fieldname']."' && mal_id=".intval($mal_id), $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function mal_rehash_pri($mal_id) {
	global $connection, $table_prefix;
	$sql = mysql_query("SELECT fieldname FROM `".$table_prefix."tabellmaler_data` WHERE mal_id=".$mal_id." ORDER BY pri", $connection);
	while (list($tmp) = mysql_fetch_row($sql)) {
		$fields[] = $tmp;
	}
	$pri = 0;
	foreach ($fields as $field) {
		mysql_query("UPDATE `".$table_prefix."tabellmaler_data` SET pri=".++$pri." WHERE mal_id=".$mal_id." && fieldname='".$field."'", $connection);
	}
}

function slett_mal() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."tabellmaler_data` WHERE mal_id=".intval($_GET['slettmal']), $connection) or exit(mysql_error());
	mysql_query("DELETE FROM `".$table_prefix."tabellmaler` WHERE mal_id=".intval($_GET['slettmal']), $connection) or exit(mysql_error());
	mysql_query("UPDATE `".$table_prefix."spill` SET rollemal=0 WHERE rollemal=".intval($_GET['slettmal']), $connection) or exit(mysql_error());
	mysql_query("UPDATE `".$table_prefix."spill` SET paameldingsmal=0 WHERE paameldingsmal=".intval($_GET['slettmal']), $connection) or exit(mysql_error());
}

function get_malspill($mal_id) {
	global $connection, $table_prefix;
	$spill = mysql_query("SELECT * FROM `".$table_prefix."spill` WHERE rollemal=".intval($mal_id)." || paameldingsmal=".intval($mal_id), $connection);
	//for ($i = 0; $i < mysql_num_rows($spill); $i++) {
	//	$spillinfo[] = mysql_fetch_assoc($spill);
	while ($tuple = mysql_fetch_assoc($spill)) {
		$spillinfo[] = $tuple;
	}
	if ($spillinfo) {
		return $spillinfo;
	}
	return false;
}

############################################
# Funksjoner for behandling av p?meldinger #
############################################

function get_paamelding($person_id, $spill_id) {
	global $connection, $table_prefix;
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['paameldingsmal'];
	if ($maldata = get_paameldingsmal($spill_id)) {
		foreach ($maldata as $value) {
			$malfields .= $value['fieldname'].",";
		}
	}
	$fields = "person_id,spill_id,paameldt,betalt,".$malfields."annet";
	$spiller = get_person($person_id);
	$paamelding = mysql_fetch_assoc(mysql_query("SELECT $fields FROM `".$table_prefix."paameldinger` WHERE spill_id=".intval($spill_id)." && person_id=".intval($person_id), $connection));
	if ($paamelding) {
		return array_merge($spiller, $paamelding);
	}
	return false;
}

function slett_paamelding() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."paameldinger` WHERE person_id=".intval($_GET['slett_paamelding'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("UPDATE `".$table_prefix."roller` SET spiller_id=0 WHERE spiller_id=".intval($_GET['slett_paamelding'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("UPDATE `".$table_prefix."rollekonsept` SET spiller_id=0 WHERE spiller_id=".intval($_GET['slett_paamelding'])." && spill_id=".intval($_GET['spill_id']), $connection);
}

function oppdater_paamelding() {
	global $connection, $table_prefix;
	$mal = get_spillmal($_POST['spill_id'], "paameldingsmal");
	$maldata = get_maldata($mal['mal_id']);
	foreach ($maldata as $entry) {
		switch ($entry['type']) {
			case "check":
			if (!$_POST['paamelding'][$entry['fieldname']]) {
				$sql .= $entry['fieldname']."='0',";
			}
			break;
			case "listmulti":
			$sql .= $entry['fieldname']."='".sql_serialize($_POST[$entry['fieldname']])."',";
			break;
		}
	}
	foreach ($_POST['paamelding'] as $key=>$value) {
		if (is_array($value)) {
			$sql .= $key."='".sql_serialize($value)."',";
		} else {
			switch($key) {
				case "betalt":
				case "dag":
				case "mnd":
				case "aar":
				case "time":
				case "min":
				break;
				default:
				$sql .= $key."='".$value."',";
			}
		}
	}
	if (!$_POST['paamelding']['betalt']) {
		$sql .= "betalt=0,";
	} else {
		$sql .= "betalt=1,";
	}
	$paameldt = strtotime($_POST['paamelding']['aar']."-".$_POST['paamelding']['mnd']."-".$_POST['paamelding']['dag']." ".$_POST['paamelding']['time'].":".$_POST['paamelding']['min']);
	$sql .= "paameldt=".intval($paameldt).",";
	mysql_query("UPDATE `".$table_prefix."paameldinger` SET ".substr($sql, 0, -1)." WHERE person_id=".intval($_POST['person_id'])." && spill_id=".intval($_POST['spill_id']), $connection);
}

function opprett_paamelding() {
	global $connection, $table_prefix, $person_id;
	if ($_POST['ny_person']) {
		$person_id = opprett_person();
	} else {
		$person_id = $_POST['person_id'];
	}
	$fields .= 'person_id,spill_id,';
	$values .= intval($person_id).",".intval($_POST['spill_id']).",";
	$mal = get_spillmal($_POST['spill_id'], 'paameldingsmal');
	$maldata = get_maldata($mal['mal_id']);
	foreach ($maldata as $entry) {
		switch ($entry['type']) {
			case 'check':
			if (!$_POST['paamelding'][$entry['fieldname']]) {
				$fields .= $entry['fieldname'].",";
				$values .= "'0',";
			}
			break;
			case 'listmulti':
			$fields .= $entry['fieldname'].",";
			$values .= "'".sql_serialize($_POST[$entry['fieldname']])."',";
			break;
		}
	}
	foreach ($_POST['paamelding'] as $key=>$value) {
		if ($key != 'ny_paamelding') {
			if (is_array($value)) {
				$fields .= $key.',';
				$values .= "'".sql_serialize($value)."',";
			} else {
				switch($key) {
					case 'betalt':
					case 'dag':
					case 'mnd':
					case 'aar':
					case 'time':
					case 'min':
					break;
					default:
					$fields .= $key.',';
					$values .= "'".$value."',";
				}
			}
		}
	}
	if (!$_POST['paamelding']['betalt']) {
		$betalt = 0;
	} else {
		$betalt = 1;
	}
	if (!$_POST['paamelding']['dag']) {
		$paameldt = time();
	} else {
		$paameldt = strtotime($_POST['paamelding']['aar']."-".$_POST['paamelding']['mnd']."-".$_POST['paamelding']['dag']." ".$_POST['paamelding']['time'].":".$_POST['paamelding']['min']);
	}
	$fields .= 'paameldt,betalt,';
	$values .= intval($paameldt).",".intval($betalt).",";
	mysql_query("INSERT INTO `".$table_prefix."paameldinger` (".substr($fields, 0, -1).") VALUES (".substr($values, 0, -1).")", $connection) or exit(mysql_error());
}

function get_paameldinger($spill_id) {
	global $connection, $table_prefix;
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['paameldingsmal'];
	if ($maldata = get_paameldingsmal($spill_id)) {
		foreach ($maldata as $value) {
			$malfields .= $value['fieldname'].",";
		}
	}
	$fields = 'person_id,spill_id,paameldt,betalt,'.$malfields.'annet';
	$sqlpersoner = mysql_query("SELECT ".$fields." FROM `".$table_prefix."paameldinger` WHERE spill_id=".intval($spill_id), $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlpersoner); $i++) {
	//	$personer[] = mysql_fetch_assoc($sqlpersoner);
	while ($tuple = mysql_fetch_assoc($sqlpersoner)) {
		$personer[] = $tuple;
	}
	if ($personer) {
		return $personer;
	}
	return false;
}

function get_paameldte($spill_id) {
	global $connection, $table_prefix;
	switch (basename($_SERVER['PHP_SELF'])) {
		case "paameldinger.php":
		if ($_SESSION['paameldingorder']) {
			switch($_SESSION['paameldingorder']) {
				case "alder":
				$order = "fodt desc";
				break;
				case "alder desc":
				$order = "fodt";
				break;
				default:
				$order = $_SESSION['paameldingorder'];
			}
		} else {
			$order = "etternavn";
		}
		break;
		case "betaling.php":
		if ($_SESSION['betalingorder']) {
			switch($_SESSION['betalingorder']) {
				case "alder":
				$order = "fodt desc";
				break;
				case "alder desc":
				$order = "fodt";
				break;
				default:
				$order = $_SESSION['betalingorder'];
			}
		} else {
			$order = "etternavn";
		}
		break;
		default:
		$order = "etternavn";
	}
	$spillinfo = get_spillinfo($spill_id);
	$mal_id = $spillinfo['paameldingsmal'];
	if ($maldata = get_paameldingsmal($spill_id)) {
		foreach ($maldata as $value) {
			$malfields .= $value['fieldname'].",";
		}
	}
	$fields = $table_prefix.'personer.person_id, type, etternavn, fornavn, fodt, alder, kjonn, adresse, postnr, poststed, telefon, mobil, email, mailpref, hensyn, intern_info, bilde, spill_id, paameldt, betalt, '.$malfields.' annet';
	$sqlpaameldte = mysql_query("SELECT ".$fields." FROM `".$table_prefix."personer`,`".$table_prefix."paameldinger` WHERE `".$table_prefix."paameldinger`.person_id=`".$table_prefix."personer`.person_id && `".$table_prefix."paameldinger`.spill_id=".intval($spill_id)." ORDER BY ".$order, $connection) or exit(mysql_error());
	//for ($i = 0; $i < mysql_num_rows($sqlpaameldte); $i++) {
	//	$paameldte[] = mysql_fetch_assoc($sqlpaameldte);
	while ($tuple = mysql_fetch_assoc($sqlpaameldte)) {
		$paameldte[] = $tuple;
	}
	if ($paameldte) {
		return $paameldte;
	}
	return false;
}

function get_paameldte_og_arrangorer($spill_id) {
	global $connection, $table_prefix;
	$paameldte = mysql_query("SELECT `".$table_prefix."personer`.person_id,fornavn,etternavn FROM `".$table_prefix."personer`,`".$table_prefix."paameldinger` WHERE `".$table_prefix."paameldinger`.person_id=`".$table_prefix."personer`.person_id && `".$table_prefix."paameldinger`.spill_id=".intval($spill_id)." ORDER BY etternavn", $connection) or exit(mysql_error());
	$arrangorer = mysql_query("SELECT person_id,fornavn,etternavn FROM `".$table_prefix."personer` WHERE type='arrangor' ORDER BY etternavn", $connection) or exit(mysql_error());
	/*
	for ($i = 0; $i < mysql_num_rows($paameldte); $i++) {
		$personer[] = mysql_fetch_assoc($paameldte);
	}
	for ($i = 0; $i < mysql_num_rows($arrangorer); $i++) {
		$personer[] = mysql_fetch_assoc($arrangorer);
	}
	*/
	while($tuple = mysql_fetch_assoc($paameldte)) {
		$personer[] = $tuple;
	}
	while($tuple = mysql_fetch_assoc($arrangorer)) {
		$personer[] = $tuple;
	}
	if ($personer) {
		ksort($personer);
		return $personer;
	}
	return false;
}

function oppdater_betaling() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."paameldinger` SET betalt=0 WHERE spill_id=".intval($_POST['spill_id']), $connection);
	foreach ($_POST as $key=>$value) {
		switch ($key) {
			case "spill_id":
			case "oppdater_betaling":
			break;
			default:
			mysql_query("UPDATE `".$table_prefix."paameldinger` SET betalt=1 WHERE person_id=".intval($key)." && spill_id=".intval($_POST['spill_id']), $connection);
		}
	}
}

function get_opd_paameldinger($time, $spill_id) {
	$paameldinger = get_paameldte($spill_id);
	if ($paameldinger) {
		foreach ($paameldinger as $paamelding) {
			if ($paamelding['paameldt'] > $time) {
				$nyepaameldinger[] = $paamelding;
			}
		}
		if ($nyepaameldinger) {
			return $nyepaameldinger;
		}
	}
	return false;
}


###################################
# Funskjoner for gruppebehandling #
###################################

function get_gruppe($gruppe_id, $spill_id) {
	global $connection, $table_prefix;
	$gruppe = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."grupper` WHERE gruppe_id=".intval($gruppe_id)." && spill_id=".intval($spill_id), $connection));
	return $gruppe;
}

function get_grupper($spill_id) {
	global $connection, $table_prefix;
	if ($_SESSION['gruppeorder']) {
		$order = $_SESSION['gruppeorder'];
	} else {
		$order = "navn";
	}
	$sqlgrupper = mysql_query("SELECT * FROM `".$table_prefix."grupper` WHERE spill_id=".intval($spill_id)." ORDER BY ".$order, $connection);
	$grupper = array();
	//for ($i = 0; $i < mysql_num_rows($sqlgrupper); $i++) {
	//	$gruppe = mysql_fetch_assoc($sqlgrupper);
	while ($gruppe = mysql_fetch_assoc($sqlgrupper)) {
		foreach ($gruppe as $key=>$value) {
			$grupper[$gruppe['gruppe_id']][$key] = $value;
		}
	}
	if ($grupper) {
		return $grupper;
	}
	return false;
}

function get_gruppe_roller($gruppe_id, $spill_id) {
	global $connection, $table_prefix;
	$sqlmedlemmer = mysql_query("SELECT `".$table_prefix."roller`.rolle_id,spiller_id,`".$table_prefix."roller`.spill_id,navn,beskrivelse_gruppe FROM `".$table_prefix."roller`,`".$table_prefix."gruppe_roller` WHERE `".$table_prefix."roller`.rolle_id=`".$table_prefix."gruppe_roller`.rolle_id && `".$table_prefix."roller`.spill_id=`".$table_prefix."gruppe_roller`.spill_id && `".$table_prefix."gruppe_roller`.spill_id=".intval($spill_id)." && gruppe_id=".intval($gruppe_id), $connection) or exit(mysql_error());
	//for ($i = 0; $i < mysql_num_rows($sqlmedlemmer); $i++) {
	//	$data = mysql_fetch_assoc($sqlmedlemmer);
	while ($data = mysql_fetch_assoc($sqlmedlemmer)) {
		$medlemmer[$data['rolle_id']] = $data;
	}
	return $medlemmer;
}

function get_rolle_grupper($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$sqlgrupper = mysql_query("SELECT * FROM `".$table_prefix."grupper`,`".$table_prefix."gruppe_roller` WHERE `".$table_prefix."grupper`.gruppe_id=`".$table_prefix."gruppe_roller`.gruppe_id && `".$table_prefix."gruppe_roller`.spill_id=".intval($spill_id)." && rolle_id=".intval($rolle_id), $connection) or exit(mysql_error());
	//for ($i = 0; $i < mysql_num_rows($sqlgrupper); $i++) {
	//	$data = mysql_fetch_assoc($sqlgrupper);
	while ($data = mysql_fetch_assoc($sqlgrupper)) {
		$grupper[$data['gruppe_id']] = $data;
	}
	if ($grupper) {
		return $grupper;
	}
	return false;
}

function rolle_er_medlem($rolle_id, $gruppe_id, $spill_id) {
	global $connection, $table_prefix;
	$check = mysql_query("SELECT * FROM `".$table_prefix."gruppe_roller` WHERE rolle_id=".intval($rolle_id)." && gruppe_id=".intval($gruppe_id)." && spill_id=".intval($spill_id), $connection);
	if (mysql_num_rows($check) > 0) {
		return true;
	}
	return false;
}

function fjern_gruppe_medlem() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."gruppe_roller` WHERE rolle_id=".intval($_GET['fjern_medlem'])." && spill_id=".intval($_GET['spill_id'])." && gruppe_id=".intval($_GET['gruppe_id']), $connection);
}

function ny_gruppe_medlem() {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."gruppe_roller` (gruppe_id, spill_id, rolle_id) VALUES (".intval($_POST['gruppe_id']).", ".intval($_POST['spill_id']).", ".intval($_POST['ny_medlem']).")", $connection) or exit(mysql_error());
}

function oppdater_gruppeinfo() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."grupper` SET navn='".$_POST['navn']."', beskrivelse='".$_POST['beskrivelse']."', medlemsinfo='".$_POST['medlemsinfo']."' WHERE gruppe_id=".intval($_POST['edited'])." && spill_id=".intval($_POST['spill_id']), $connection) or exit(mysql_error());
}

function opprett_gruppe() {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."grupper` (spill_id, navn, beskrivelse, medlemsinfo) VALUES (".intval($_POST['spill_id']).",'".$_POST['navn']."','".$_POST['beskrivelse']."','".$_POST['medlemsinfo']."')", $connection);
	return mysql_insert_id();
}

function slett_gruppe() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."grupper` WHERE gruppe_id=".intval($_GET['slett_gruppe'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("UPDATE `".$table_prefix."roller` SET gruppe_id=0 WHERE gruppe_id=".intval($_GET['slett_gruppe'])." && spill_id=".intval($_GET['spill_id']), $connection);
}

##################################
# Funskjoner for plottbehandling #
##################################

function get_spillplott ($spill_id) {
	global $connection, $table_prefix;
	$sqlplott = mysql_query("SELECT * FROM `".$table_prefix."plott` WHERE spill_id=".intval($spill_id), $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlplott); $i++) {
	//	$plott[] = mysql_fetch_assoc($sqlplott);
	while ($tuple = mysql_fetch_assoc($sqlplott)) {
		$plott[] = $tuple;
	}
	return $plott;
}

function get_plott($plott_id, $spill_id) {
	global $connection, $table_prefix;
	$plott = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."plott` WHERE plott_id=".intval($plott_id)." && spill_id=".intval($spill_id), $connection));
	if (count($plott) != 1) {
		return $plott;
	}
	return false;
}

function get_plott_roller($plott_id, $spill_id) {
	global $connection, $table_prefix;
	$medlemmer = mysql_query("SELECT * FROM `".$table_prefix."plott_medlemmer` WHERE plott_id=".intval($plott_id)." && spill_id=".intval($spill_id)." && type='rolle'", $connection);
	//for ($i = 0; $i < mysql_num_rows($medlemmer); $i++) {
	//	$medlem = mysql_fetch_assoc($medlemmer);
	while($medlem = mysql_fetch_assoc($medlemmer)) {
		$medlemsrolle = get_rolle($medlem['medlem_id'], $medlem['spill_id']);
		$medlemsrolle['tilknytning'] = $medlem['tilknytning'];
		$plottroller[] = $medlemsrolle;
	}
	if (count($plottroller) >= 1) {
		return $plottroller;
	}
	return false;
}

function get_plott_rolle($rolle_id, $plott_id, $spill_id) {
	global $connection, $table_prefix;
	$plottrolle = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."plott_medlemmer` WHERE plott_id=".intval($plott_id)." && medlem_id=".intval($rolle_id)." && spill_id=".intval($spill_id)." && type='rolle'", $connection));
	if (count($plottrolle) != 1) {
		$rolleinfo = get_rolle($rolle_id, $spill_id);
		$rolleinfo['tilknytning'] = $plottrolle['tilknytning'];
		return $rolleinfo;
	}
	return false;
}

function get_rolle_plott($rolle_id, $spill_id) {
	global $connection, $table_prefix;
	$sqlplott = mysql_query("SELECT * FROM `".$table_prefix."plott_medlemmer` WHERE medlem_id=".intval($rolle_id)." && spill_id=".intval($spill_id)." && type='rolle'", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlplott); $i++) {
	//	$plott = mysql_fetch_assoc($sqlplott);
	while ($plott = mysql_fetch_assoc($sqlplott)) {
		$plottinfo = get_plott($plott['plott_id'], $plott['spill_id']);
		$rolleplott[] = array_merge($plott, $plottinfo);
	}
	if (count($rolleplott) != 0) {
		return $rolleplott;
	}
	return false;
}

function get_plott_grupper($plott_id, $spill_id) {
	global $connection, $table_prefix;
	$medlemmer = mysql_query("SELECT * FROM `".$table_prefix."plott_medlemmer` WHERE plott_id=".intval($plott_id)." && spill_id=".intval($spill_id)." && type='gruppe'", $connection);
	//for ($i = 0; $i < mysql_num_rows($medlemmer); $i++) {
	//	$medlem = mysql_fetch_assoc($medlemmer);
	while ($medlem = mysql_fetch_assoc($medlemmer)) {
		$medlemsgruppe = get_gruppe($medlem['medlem_id'], $medlem['spill_id']);
		$medlemsgruppe['tilknytning'] = $medlem['tilknytning'];
		$plottgrupper[] = $medlemsgruppe;
	}
	if ($plottgrupper) {
		return $plottgrupper;
	}
	return false;
}

function get_plott_gruppe($gruppe_id, $plott_id, $spill_id) {
	global $connection, $table_prefix;
	$plottgruppe = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."plott_medlemmer` WHERE plott_id=".intval($plott_id)." && medlem_id=".intval($gruppe_id)." && spill_id=".intval($spill_id)." && type='gruppe'", $connection));
	if ($plottgruppe) {
		$gruppeinfo = get_gruppe($gruppe_id, $spill_id);
		$gruppeinfo['tilknytning'] = $plottgruppe['tilknytning'];
		return $gruppeinfo;
	}
	return false;
}

function get_gruppe_plott($gruppe_id, $spill_id) {
	global $connection, $table_prefix;
	$sqlplott = mysql_query("SELECT * FROM `".$table_prefix."plott_medlemmer` WHERE medlem_id=".intval($gruppe_id)." && spill_id=".intval($spill_id)." && type='gruppe'", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlplott); $i++) {
	//	$plott = mysql_fetch_assoc($sqlplott);
	while ($plott = mysql_fetch_assoc($sqlplott)) {
		$plottinfo = get_plott($plott['plott_id'], $plott['spill_id']);
		$gruppeplott[] = array_merge($plott, $plottinfo);
	}
	if ($gruppeplott) {
		return $gruppeplott;
	}
	return false;
}

function ny_plott_medlem($medlem_id, $type) {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."plott_medlemmer` VALUES (".intval($_POST['plott_id']).",".intval($_POST['spill_id']).",".intval($medlem_id).",'".$type."', '".$_POST['tilknytning']."', ".time().")", $connection);
}

function fjern_plott_medlem($type) {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."plott_medlemmer` WHERE medlem_id=".intval($_GET['fjern_medlem'])." && plott_id=".intval($_GET['plott_id'])." && type='".$type."' && spill_id=".intval($_GET['spill_id']), $connection);
}

function oppdater_plott_medlem($type) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."plott_medlemmer` SET tilknytning='".$_POST['tilknytning']."', oppdatert=".time()." WHERE plott_id=".intval($_POST['plott_id'])." && spill_id=".intval($_POST['spill_id'])." && type='".$type."' && medlem_id=".intval($_POST['redigert_medlem']), $connection);
}

function oppdater_plott() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."plott` SET navn='".$_POST['navn']."', beskrivelse='".$_POST['beskrivelse']."', oppdatert=".time()." WHERE plott_id=".intval($_POST['edited'])." && spill_id=".intval($_POST['spill_id']), $connection) or exit(mysql_error());
}

function opprett_plott() {
	global $connection, $table_prefix;
	mysql_query("INSERT INTO `".$table_prefix."plott` (spill_id, navn, beskrivelse, oppdatert) VALUES (".intval($_POST['spill_id']).",'".$_POST['navn']."','".$_POST['beskrivelse']."', ".time().")", $connection);
	return mysql_insert_id();
}

function slett_plott() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."plott` WHERE plott_id=".intval($_GET['slett_plott'])." && spill_id=".intval($_GET['spill_id']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."plott_roller` WHERE plott_id=".intval($_GET['slett_plott'])." && spill_id=".intval($_GET['spill_id']), $connection);
}

function get_opd_plott($time, $spill_id) {
	$plottinfo = get_spillplott($spill_id);
	if (!$plottinfo) {
		return false;
	}
	foreach ($plottinfo as $info) {
		if ($info['oppdatert'] > $time) {
			$plott[] = $info;
		}
	}
	if ($plott) {
		return $plott;
	}
	return false;
}

##################################
# Funksjoner for spillbehandling #
##################################

function get_spillhistorikk($person_id) {
	global $connection, $table_prefix;
	$person = get_person($person_id);
	if ($person['type'] == "arrangor") {
		$data = get_spiller_roller($person_id, 0);
		if ($data) {
			foreach ($data as $entry) {
				$spill[] = get_spillinfo($entry['spill_id']);
			}
		}
	} else {
		$data = mysql_query("SELECT * FROM `".$table_prefix."paameldinger` WHERE person_id=".intval($person_id), $connection) or exit(mysql_error());
		//for ($i = 0; $i < mysql_num_rows($data); $i++) {
		//	$data2 = mysql_fetch_assoc($data);
		while ($data2 = mysql_fetch_assoc($data)) {
			$spill[] = get_spillinfo($data2['spill_id']);
		}
	}
	if ($spill) {
		return $spill;
	}
	return false;
}

function get_spillinfo($spill_id) {
	global $connection, $table_prefix;
	$spillinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."spill` WHERE spill_id=".intval($spill_id), $connection));
	return $spillinfo;
}

function get_spill($thisorder = 0) {
	global $connection, $table_prefix;
	if ($thisorder) {
		$order = $thisorder;
	} else {
		if ($_SESSION['spillorder']) {
			$order = $_SESSION['spillorder'];
		} else {
			$order = "start DESC";
		}
	}
	$sqlspill = mysql_query("SELECT * FROM `".$table_prefix."spill` ORDER BY ".$order, $connection);
	$spill = array();
	//for ($i = 0; $i < mysql_num_rows($sqlspill); $i++) {
	//	$spillinfo = mysql_fetch_assoc($sqlspill);
	while ($spillinfo = mysql_fetch_assoc($sqlspill)) {
		foreach ($spillinfo as $key=>$value) {
			$spill[$spillinfo['spill_id']][$key] = $value;
		}
	}
	return $spill;
}

function oppdater_spillstatus() {
	global $connection, $table_prefix;
	if ($_GET['deaktiviser']) {
		mysql_query("UPDATE `".$table_prefix."spill` SET status='Inaktiv' WHERE spill_id=".intval($_GET['deaktiviser']), $connection);
	} elseif ($_GET['aktiviser']) {
		mysql_query("UPDATE `".$table_prefix."spill` SET status='Aktiv' WHERE spill_id=".intval($_GET['aktiviser']), $connection);
	}
}

function oppdater_spillinfo() {
	global $connection, $table_prefix;
	$spill_id = $_POST['spill_id'];
	if (!$_POST['rollekonsept']) {
		$_POST['rollekonsept'] = "0";
	}
	$start = strtotime($_POST['start_year']."-".$_POST['start_month']."-".$_POST['start_day']);
	$slutt = strtotime($_POST['slutt_year']."-".$_POST['slutt_month']."-".$_POST['slutt_day']);
	mysql_query("UPDATE `".$table_prefix."spill` SET navn='".$_POST['navn']."', start=".$start.", slutt=".$slutt.", rollemal=".intval($_POST['rollemal']).", paameldingsmal=".intval($_POST['paameldingsmal']).", rollekonsept=".intval($_POST['rollekonsept']).", status='".$_POST['status']."' WHERE spill_id=".intval($_POST['spill_id']), $connection) or exit(mysql_error());
}

function nytt_spill() {
	global $connection, $table_prefix;
	$start = strtotime($_POST['start_year']."-".$_POST['start_month']."-".$_POST['start_day']);
	$slutt = strtotime($_POST['slutt_year']."-".$_POST['slutt_month']."-".$_POST['slutt_day']);
	mysql_query("INSERT INTO `".$table_prefix."spill` (navn, start, slutt, rollemal, paameldingsmal, status) VALUES ('".$_POST['navn']."', ".$start.", ".$slutt.", ".intval($_POST['rollemal']).", ".intval($_POST['paameldingsmal']).", '".$_POST['status']."')", $connection) or exit(mysql_error());
	return mysql_insert_id();
}

function slett_spill() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."paameldinger` WHERE spill_id=".intval($_GET['slett_spill']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."roller` WHERE spill_id=".intval($_GET['slett_spill']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."spill` WHERE spill_id=".intval($_GET['slett_spill']), $connection);
	mysql_query("DELETE FROM `".$table_prefix."grupper` WHERE spill_id=".intval($_GET['slett_spill']), $connection);
}

function get_aktive_spill($order = 0) {
	$spill = get_spill($order);
	foreach ($spill as $info) {
		if (strtolower($info['status']) == 'aktiv') {
			$aktive_spill[] = $info;
		}
	}
	if ($aktive_spill) {
		return $aktive_spill;
	}
	return false;
}

function is_aktiv_spill($spill_id) {
	$spillinfo = get_spillinfo($spill_id);
	if (strtolower($spillinfo['status']) == 'aktiv') {
		return true;
	}
	return false;
}

function get_deadlines($spill_id) {
	global $connection, $table_prefix;
	if ($spill_id == 0) {
		$sqldeadlines = mysql_query("SELECT * FROM `".$table_prefix."deadlines` ORDER BY deadline", $connection);
	} else {
		$sqldeadlines = mysql_query("SELECT * FROM `".$table_prefix."deadlines` WHERE spill_id=".intval($spill_id)." ORDER BY deadline", $connection);
	}
	//for ($i = 0; $i < mysql_num_rows($sqldeadlines); $i++) {
	//	$deadlines[] = mysql_fetch_assoc($sqldeadlines);
	while ($tuple = mysql_fetch_assoc($sqldeadlines)) {
		$deadlines[] = $tuple;
	}
	if ($deadlines) {
		return $deadlines;
	}
	return false;
}

function get_spilldeadlines_for_dag($timestamp) {
	global $connection, $table_prefix;
	if ($alledeadlines = get_deadlines(0)) {
		foreach ($alledeadlines as $deadline) {
			if (strftime('%Y-%m-%d', $deadline['deadline']) == strftime('%Y-%m-%d', $timestamp)) {
				$deadlines[] = $deadline;
			}
		}
		if ($deadlines) {
			return $deadlines;
		}
	}
	return false;
}

function get_deadline($deadline_id, $spill_id) {
	global $connection, $table_prefix;
	$deadline = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."deadlines` WHERE deadline_id=".intval($deadline_id)." && spill_id=".intval($spill_id), $connection));
	if ($deadline) {
		return $deadline;
	}
	return false;
}

function opprett_deadline($spill_id) {
	global $connection, $table_prefix;
	if (checkdate($_POST['mnd'], $_POST['dag'], $_POST['aar'])) {
		$deadline = strtotime($_POST['aar']."-".$_POST['mnd']."-".$_POST['dag']);
	} else {
		$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['invalid_date']."</span><br><br>";
		$deadline = time();
	}
	mysql_query("INSERT INTO `".$table_prefix."deadlines` VALUES (NULL,".intval($spill_id).",'".$_POST['tekst']."', ".$deadline.")", $connection) or exit(mysql_error());
	return true;
}

function oppdater_deadline($deadline_id, $spill_id) {
	global $connection, $table_prefix;
	if (checkdate($_POST['mnd'], $_POST['dag'], $_POST['aar'])) {
		$deadline = strtotime($_POST['aar']."-".$_POST['mnd']."-".$_POST['dag']);
	} else {
		$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['invalid_date']."</span><br><br>";
		$deadline = time();
	}
	mysql_query("UPDATE `".$table_prefix."deadlines` SET tekst='".$_POST['tekst']."', deadline='".$deadline."' WHERE deadline_id='".$deadline_id."' && spill_id='".$spill_id."'", $connection);
	return true;
}

function slett_deadline($deadline_id, $spill_id) {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."deadlines` WHERE deadline_id=".intval($deadline_id)." && spill_id=".intval($spill_id), $connection);
	return true;
}

##############################
# Funksjoner for filsystemet #
##############################

function get_filer($dirs = 0) {
	global $connection, $table_prefix;
	if (!$_SESSION['filorder']) {
		$order = "navn";
	} else {
		$order = $_SESSION['filorder'];
	}
	if (!$dirs) {
		$sqlfiler = mysql_query("SELECT * FROM `".$table_prefix."filsystem` WHERE type != 'dir' ORDER BY ".$order);
	} else {
		$sqlfiler = mysql_query("SELECT * FROM `".$table_prefix."filsystem` ORDER BY ".$order);
	}
	//for ($i = 0; $i < mysql_num_rows($sqlfiler); $i++) {
	//	$data = mysql_fetch_assoc($sqlfiler);
	while ($data = mysql_fetch_assoc($sqlfiler)) {
		$filer[$data['fil_id']] = $data;
	}
	if ($filer) {
		return $filer;
	}
	return false;
}

function get_fileinfo($file, $dir) {
	global $connection, $table_prefix;
	$fileinfo = mysql_fetch_array(mysql_query("SELECT * FROM `".$table_prefix."filsystem` WHERE navn='".$file."' && dir='".$dir."'", $connection));
	if (!$fileinfo) {
		return false;
	} else {
		return $fileinfo;
	}
}

function opprett_fil($dir, $beskrivelse) {
	global $connection, $table_prefix, $config, $LANG;
	$dir_t = getcwd().DIRECTORY_SEPARATOR.$config['filsystembane'].strtr($dir, '/', DIRECTORY_SEPARATOR);

	if (file_exists($dir_t.$_FILES['nyfil']['name'])) {
		$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['filename_exists']."</span><br><br>";
		return false;
	}
	$stored_name = mkfilename($_FILES['nyfil']['name']);
	$result = lagre_fil("nyfil", $stored_name, $dir_t);
	if ($result) {
		$filetype = get_mime_type($_FILES['nyfil']['name']);
		mysql_query("INSERT INTO `".$table_prefix."filsystem` VALUES (NULL, '".$stored_name."', '".$dir."', '".$filetype."', '".$beskrivelse."', '".time()."')", $connection);
		return mysql_insert_id();
	}
	return false;
}

function create_dir() {
	global $connection, $table_prefix, $config, $LANG;
	$stored_name = mkfilename($_POST['newdir']);
	if (is_dir($config['filsystembane'].$_SESSION['cwd'].$stored_name)) {
		$_SESSION['message'] = "<span class=\"red\">".$LANG['MESSAGE']['filename_exists']."</span><br><br>";
		return false;
	}
	$result = save_dir($stored_name, $_SESSION['cwd']);
	if ($result) {
		mysql_query("INSERT INTO `".$table_prefix."filsystem` VALUES (NULL, '".$stored_name."', '".$_SESSION['cwd']."', 'dir', '".$_POST['beskrivelse']."', '".time()."')", $connection);
		return mysql_insert_id();
	}
	return false;
}

function slett_fil($fil, $dir) {
	global $connection, $table_prefix, $config;
	if (@unlink($config['filsystembane'].$dir.$fil)) {
		$filinfo = get_fileinfo($fil, $dir);
		mysql_query("DELETE FROM `".$table_prefix."filsystem` WHERE navn='".$fil."' && dir='".$dir."' && type != 'dir'", $connection);
		mysql_query("DELETE FROM `".$table_prefix."filvedlegg` WHERE fil_id=".intval($filinfo['fil_id']), $connection);
		return true;
	}
	return false;
}

function slett_dir($dir, $parent) {
	global $connection, $table_prefix, $config;
	if ($subdirs = get_fs_dirs($parent."".$dir)) {
		foreach ($subdirs as $subdir) {
			slett_dir($subdir['navn'], $parent."".$dir."/");
		}
	}
	if ($files = get_fs_files($parent."".$dir)) {
		foreach ($files as $fil) {
			slett_fil($fil['navn'], $parent."".$dir."/");
		}
	}
	if (is_file($config['filsystembane'].$parent.$dir."/.htaccess")) {
		@unlink($config['filsystembane'].$parent.$dir."/.htaccess");
	}
	if (is_file($config['filsystembane'].$parent.$dir."/index.php")) {
		@unlink($config['filsystembane'].$parent.$dir."/index.php");
	}
	if (@rmdir($config['filsystembane'].$parent.$dir)) {
		mysql_query("DELETE FROM `".$table_prefix."filsystem` WHERE navn='".$dir."' && dir='".$parent."' && type = 'dir'", $connection);
		return true;
	}
	return false;
}

function move_file($file, $dest) {
	global $connection, $table_prefix, $config;
	$fileinfo = get_fil($file);
	if (@copy($config['filsystembane'].$fileinfo['dir'].$fileinfo['navn'], $config['filsystembane'].$dest.$fileinfo['navn'])) {
		if (@unlink($config['filsystembane'].$fileinfo['dir'].$fileinfo['navn'])) {
			mysql_query("UPDATE `".$table_prefix."filsystem` SET dir='".$dest."', oppdatert='".time()."' WHERE fil_id=".intval($file), $connection);
			return true;
		}
	}
	return false;
}

function rename_dir($dir_id, $newname) {
	global $connection, $table_prefix, $config;
	$dirinfo = get_fil($dir_id);
	$newname = mkfilename($newname);
	if (@rename($config['filsystembane'].$dirinfo['dir'].$dirinfo['navn'], $config['filsystembane'].$dirinfo['dir'].$newname)) {
		mysql_query("UPDATE `".$table_prefix."filsystem` SET navn='".$newname."' WHERE fil_id=".intval($dir_id)." && type='dir'", $connection);
		$dirs = mysql_query("SELECT * FROM `".$table_prefix."filsystem` WHERE dir LIKE '%".$dirinfo['dir'].$dirinfo['navn']."%'", $connection);
		//for ($i = 0; $i < mysql_num_rows($dirs); $i++) {
		//	$dir = mysql_fetch_array($dirs);
		while ($dir = mysql_fetch_array($dirs)) {
			mysql_query("UPDATE `".$table_prefix."filsystem` SET dir='".ereg_replace("^".$dirinfo['dir'].$dirinfo['navn']."/", $dirinfo['dir'].$newname."/", $dir['dir'])."', oppdatert='".time()."' WHERE fil_id=".intval($dir['fil_id']), $connection);
		}
		return true;
	}
	return false;
}

function update_file($fil_id) {
	global $connection, $table_prefix, $config;
	$fileinfo = get_fil($fil_id);
	if ($_POST['navn'] != $fileinfo['navn']) {
		if (!@rename($config['filsystembane'].$fileinfo['dir'].$fileinfo['navn'], $config['filsystembane'].$fileinfo['dir'].mkfilename($_POST['navn']))) {
			return false;
		} else {
			mysql_query("UPDATE `".$table_prefix."filsystem` SET navn='".mkfilename($_POST['navn'])."', oppdatert='".time()."' WHERE fil_id=".intval($fil_id), $connection);
		}
	}
	if ($_POST['dir'] != $fileinfo['dir']) {
		if (!move_file($fil_id, $_POST['dir'])) {
			return false;
		}
	}
	mysql_query("UPDATE `".$table_prefix."filsystem` SET navn='".mkfilename($_POST['navn'])."', type='".$_POST['type']."', beskrivelse='".$_POST['beskrivelse']."', oppdatert='".time()."' WHERE fil_id=".intval($fil_id), $connection);
	return true;
}

function replace_file($fil_id) {
	global $connection, $table_prefix, $config;
	$fileinfo = get_fil($fil_id);
	$filetype = get_mime_type($_FILES['nyfil']['name']);
	$filename = mkfilename($_FILES['nyfil']['name']);
	@unlink($config['filsystembane'].$fileinfo['dir'].$fileinfo['navn']);
	move_uploaded_file($_FILES['nyfil']['tmp_name'], $config['filsystembane'].$fileinfo['dir'].$filename);
	mysql_query("UPDATE `".$table_prefix."filsystem` SET navn='".$filename."', type='".$filetype."', oppdatert='".time()."' WHERE fil_id=".intval($fil_id), $connection);
	return true;
}

function remove_dummy_entries() {
	global $connection, $table_prefix, $config;
	if ($filer = get_filer(1)) {
		foreach ($filer as $fil) {
			if (!is_file($config['filsystembane'].$fil['dir'].$fil['navn']) && !is_dir($config['filsystembane'].$fil['dir'].$fil['navn'])) {
				mysql_query("DELETE FROM `".$table_prefix."filsystem` WHERE fil_id=".intval($fil['fil_id']), $connection);
				mysql_query("DELETE FROM `".$table_prefix."filvedlegg` WHERE fil_id=".intval($fil['fil_id']), $connection);
				$removed[] = $fil;
			}
		}
		if ($removed) {
			return $removed;
		}
	}
	return false;
}

function create_new_entries($dir) {
	global $connection, $table_prefix, $config, $LANG;
	if ($filer = get_fs_files($dir)) {
		foreach ($filer as $fil) {
			if (!$fileinfo = get_fileinfo($fil['navn'], $dir)) {
				$filetype = get_mime_type($fil['navn']);
				mysql_query("INSERT INTO `".$table_prefix."filsystem` VALUES (NULL, '".$fil['navn']."', '".$_SESSION['cwd']."', '".$filetype."', '".$LANG['MISC']['none']."', '".time()."')", $connection);
			}
		}
	}
	if ($dirs = get_fs_dirs($dir)) {
		foreach ($dirs as $newdir) {
			if (!$dirinfo = get_fileinfo($newdir['navn'], $dir)) {
				mysql_query("INSERT INTO `".$table_prefix."filsystem` VALUES (NULL, '".$newdir['navn']."', '".$_SESSION['cwd']."', 'dir', '".$LANG['MISC']['none']."', '".time()."')", $connection);
			}
		}
	}
}

function get_fil($fil_id) {
	global $connection, $table_prefix, $config;
	$fil = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."filsystem` WHERE fil_id=".intval($fil_id), $connection));
	if ($fil && file_exists($config['filsystembane'].$fil['dir'].$fil['navn'])) {
		return $fil;
	}
	return false;
}

function get_opd_filer($time) {
	$filer = get_filer(0);
	if ($filer) {
		foreach ($filer as $fil) {
			if ($fil['oppdatert'] > $time) {
				$nyefiler[] = $fil;
			}
		}
		if ($nyefiler) {
			return $nyefiler;
		}
	}
	return false;
}

#####################################
# Funksjoner for vedleggsbehandling #
#####################################

function get_tillatt_vedlagt($vedlagt) {
	$match = array('rolle', 'gruppe', 'spill', 'rollekonsept');
	return in_array($vedlagt, $match);
}

function get_add_vedlegg_id($vedlagt) {
	$match = array('rolle', 'gruppe');
	return in_array($vedlagt, $match);
}

function get_vedleggsliste($vedlegg_id, $spill_id, $vedlagt) {
	global $connection, $table_prefix;
	if (!get_tillatt_vedlagt($vedlagt)) {
		return false;
	}
	$sqlquery = "SELECT * FROM `".$table_prefix."filvedlegg` WHERE spill_id=".intval($spill_id)." && vedlagt='".$vedlagt."'";
	$sqlquery .= $vedlegg_id == 0 ? "" : " && vedlegg_id=".intval($vedlegg_id);
	$sqlvedlegg = mysql_query($sqlquery, $connection);
	while ($data = mysql_fetch_assoc($sqlvedlegg)) {
		if ($filinfo = get_fil($data['fil_id'])) {
			$vedlegg[] = $filinfo;
		}
	}
	if ($vedlegg) {
		return $vedlegg;
	}
	return false;
}

function slett_vedlegg($fil_id, $vedlegg_id, $spill_id, $vedlagt) {
	global $connection, $table_prefix;
	if (!get_tillatt_vedlagt($vedlagt)) {
		return;
	}
	$sqlquery = "DELETE FROM `".$table_prefix."filvedlegg` WHERE fil_id=".intval($fil_id)." && spill_id=".intval($spill_id)." && vedlagt='".$vedlagt."'";
	if (get_add_vedlegg_id($vedlagt)) {
		$sql_query .= " && vedlegg_id=".intval($vedlegg_id);
	}
	mysql_query($sqlquery, $connection) or exit(mysql_error());
}

function is_vedlegg($fil_id, $vedlegg_id, $spill_id, $vedlagt) {
	global $connection, $table_prefix;
	if (!get_tillatt_vedlagt($vedlagt)) {
		return;
	}
	$sqlquery = "SELECT * FROM `".$table_prefix."filvedlegg` WHERE fil_id=".intval($fil_id)." && spill_id=".intval($spill_id)." && vedlagt='".$vedlagt."'";
	if (get_add_vedlegg_id($vedlagt)) {
		$sqlquery .= " && vedlegg_id=".intval($vedlegg_id);
	}
	$check = mysql_query($sqlquery, $connection);
	if (mysql_num_rows($check) > 0) {
		return true;
	}
	return false;
}

function opprett_vedlegg($fil_id, $vedlegg_id, $spill_id, $vedlagt) {
	global $connection, $table_prefix;
	if (!get_tillatt_vedlagt($vedlagt)) {
		return;
	}
	if (!get_add_vedlegg_id($vedlagt)) {
		$vedlegg_id = 0;
	}
	mysql_query("INSERT INTO `".$table_prefix."filvedlegg` VALUES (".intval($fil_id).", ".intval($vedlegg_id).", ".intval($spill_id).", '".$vedlagt."')", $connection) or exit(mysql_error());
}

function get_fil_vedlagt($fil_id, $vedlagt) {
	global $connection, $table_prefix;
	$sqlvedlagt = mysql_query("SELECT * FROM `".$table_prefix."filvedlegg` WHERE fil_id=".intval($fil_id)." && vedlagt='".$vedlagt."'", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqlvedlagt); $i++) {
	//	$vedlagt_hos[] = mysql_fetch_assoc($sqlvedlagt);
	while ($tuple = mysql_fetch_assoc($sqlvedlagt)) {
		$vedlagt_hos[] = $tuple;
	}
	if ($vedlagt_hos) {
		return $vedlagt_hos;
	}
	return false;
}


###################################
# Funksjoner for oppgave-systemet #
###################################

function get_oppgaver() {
	global $connection, $table_prefix;
	$sqloppgaver = mysql_query("SELECT * FROM `".$table_prefix."oppgaver` WHERE utfort=0 ORDER BY deadline DESC, opprettet DESC", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqloppgaver); $i++) {
	//	$oppgaver[] = mysql_fetch_assoc($sqloppgaver);
	while ($tuple = mysql_fetch_assoc($sqloppgaver)) {
		$oppgaver[] = $tuple;
	}
	if ($oppgaver) {
		return $oppgaver;
	}
	return false;
}


function get_utforte_oppgaver() {
	global $connection, $table_prefix;
	$sqloppgaver = mysql_query("SELECT * FROM `".$table_prefix."oppgaver` WHERE utfort!=0 ORDER BY deadline DESC, utfort DESC, opprettet DESC", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqloppgaver); $i++) {
	//	$oppgaver[] = mysql_fetch_assoc($sqloppgaver);
	while ($tuple = mysql_fetch_assoc($sqloppgaver)) {
		$oppgaver[] = $tuple;
	}
	if ($oppgaver) {
		return $oppgaver;
	}
	return false;
}

function get_mine_oppgaver() {
	global $connection, $table_prefix;
	$sqloppgaver = mysql_query("SELECT * FROM `".$table_prefix."oppgaver` WHERE utfores_av=".intval($_SESSION['person_id'])." && utfort=0 ORDER BY deadline DESC", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqloppgaver); $i++) {
	//	$oppgaver[] = mysql_fetch_assoc($sqloppgaver);
	while ($tuple = mysql_fetch_assoc($sqloppgaver)) {
		$oppgaver[] = $tuple;
	}
	if ($oppgaver) {
		return $oppgaver;
	}
	return false;
}

function get_mine_utforte_oppgaver() {
	global $connection, $table_prefix;
	$sqloppgaver = mysql_query("SELECT * FROM `".$table_prefix."oppgaver` WHERE utfores_av=".intval($_SESSION['person_id'])." && utfort!=0 ORDER BY deadline DESC", $connection);
	//for ($i = 0; $i < mysql_num_rows($sqloppgaver); $i++) {
	//	$oppgaver[] = mysql_fetch_assoc($sqloppgaver);
	while ($tuple = mysql_fetch_assoc($sqloppgaver)) {
		$oppgaver[] = $tuple;
	}
	if ($oppgaver) {
		return $oppgaver;
	}
	return false;
}

function get_oppgave($oppgave_id) {
	global $connection, $table_prefix;
	$oppgave = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."oppgaver` WHERE oppgave_id=".intval($oppgave_id), $connection));
	if ($oppgave) {
		return $oppgave;
	}
	return false;
}

function opprett_oppgave() {
	global $connection, $table_prefix;
	$deadline = strtotime($_POST['deadline_aar']."-".$_POST['deadline_mnd']."-".$_POST['deadline_dag']);
	mysql_query("INSERT INTO `".$table_prefix."oppgaver` VALUES (NULL, ".time().", ".intval($_SESSION['person_id']).", ".$deadline.", '".$_POST['oppgavetekst']."', ".intval($_POST['utfores_av']).", 0, '')", $connection) or exit(mysql_error());
}

function oppdater_oppgave() {
	global $connection, $table_prefix;
	$deadline = strtotime($_POST['deadline_aar']."-".$_POST['deadline_mnd']."-".$_POST['deadline_dag']);
	mysql_query("UPDATE `".$table_prefix."oppgaver` SET deadline=".$deadline.", utfores_av=".intval($_POST['utfores_av']).", oppgavetekst='".$_POST['oppgavetekst']."', resultat='".$_POST['resultat']."' WHERE oppgave_id=".intval($_POST['edited']), $connection) or exit(mysql_error());
}

function slett_oppgave() {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."oppgaver` WHERE oppgave_id=".intval($_GET['slett']), $connection);
}

function tildel_oppgave($oppgave_id, $person_id) {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."oppgaver` SET utfores_av=".intval($person_id)." WHERE oppgave_id=".intval($oppgave_id), $connection);
}

function fullfor_oppgave() {
	global $connection, $table_prefix;
	mysql_query("UPDATE `".$table_prefix."oppgaver` SET utfort=".time().", resultat='".$_POST['resultat']."' WHERE oppgave_id=".intval($_POST['utfort']), $connection);
}

#############################
# Funksjoner for kalenderen #
#############################

function opprett_kalnotat() {
	global $connection, $table_prefix;
	if (checkdate($_POST['notat_mnd'], $_POST['notat_dag'], $_POST['notat_aar'])) {
		$juliandc = unixtojd(strtotime($_POST['notat_aar']."-".$_POST['notat_mnd']."-".$_POST['notat_dag']));
		mysql_query("INSERT INTO `".$table_prefix."kalender` (person_id, juliandc, tekst) VALUES (".intval($_SESSION['person_id']).", '".$juliandc."', '".$_POST['tekst']."')", $connection) or exit(mysql_error());
		return true;
	}
	return false;
}

function oppdater_kalnotat() {
	global $connection, $table_prefix;
	if (checkdate($_POST['notat_mnd'], $_POST['notat_dag'], $_POST['notat_aar'])) {
		$juliandc = unixtojd(strtotime($_POST['notat_aar']."-".$_POST['notat_mnd']."-".$_POST['notat_dag']));
		mysql_query("UPDATE `".$table_prefix."kalender` SET juliandc='".$juliandc."', tekst='".$_POST['tekst']."' WHERE notat_id=".intval($_POST['edited_notat']), $connection);
		return true;
	} else {
		mysql_query("UPDATE `".$table_prefix."kalender` SET tekst='".$_POST['tekst']."' WHERE notat_id=".intval($_POST['edited_notat']), $connection);
	}
	return false;
}

function get_kalnotater($firstday, $lastday = 0) {
	global $connection, $table_prefix;
	if ($lastday == 0) {
		$sqlnotater = mysql_query("SELECT * FROM `".$table_prefix."kalender` WHERE juliandc='".$firstday."'", $connection) or exit(mysql_error());
	} else {
		$sqlnotater = mysql_query("SELECT * FROM `".$table_prefix."kalender` WHERE juliandc>='".$firstday."' && juliandc<='".$lastday."'", $connection) or exit(mysql_error());
	}
	//for ($i = 0; $i < mysql_num_rows($sqlnotater); $i++) {
	//	$data = mysql_fetch_assoc($sqlnotater);
	while ($data = mysql_fetch_assoc($sqlnotater)) {
		if ($lastday == 0) {
			$notater[] = $data;
		} else {
			$notater[$data['juliandc']][] = $data;
		}
	}
	if ($notater) {
		return $notater;
	}
	return false;
}

function get_kalnotat($notat_id) {
	global $connection, $table_prefix;
	$notat = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."kalender` WHERE notat_id=".intval($notat_id), $connection));
	return $notat;
}

function slett_kalnotat($notat_id) {
	global $connection, $table_prefix;
	mysql_query("DELETE FROM `".$table_prefix."kalender` WHERE notat_id=".intval($notat_id), $connection);
	return true;
}

#####################################
# Funksjoner for inn- og ut-logging #
#####################################

function do_login() {
	global $connection, $table_prefix, $config;
	$brukersjekk = mysql_query("SELECT * FROM `".$table_prefix."brukere` WHERE brukernavn='".$_POST['brukernavn']."'", $connection) or exit(mysql_error());
	if (mysql_num_rows($brukersjekk) == 0) {
		login_failed();
	} else {
		$brukerinfo = mysql_fetch_assoc($brukersjekk);
		if ($_POST['passord'] != md5($_SESSION['validator'].$brukerinfo['passord'])) {
			login_failed();
		}
		unset($_SESSION['validator']);
		if ($brukerinfo['locked'] != 0) {
			$_SESSION['account_locked'] = true;
		} else {
			if (!$brukerinfo['secret']) {
				$secret = md5(microtime().$_POST['brukernavn'].$_POST['passord'].uniqid(""));
				mysql_query("UPDATE `".$table_prefix."brukere` SET secret='".$secret."' WHERE bruker_id=".intval($brukerinfo['bruker_id']), $connection);
			} else {
				$secret = $brukerinfo['secret'];
			}
			$now = time();
			$expire = $now + $config['autologout'];
			mysql_query("UPDATE `".$table_prefix."brukere` SET nowlog=".$now.",fingerprint='".md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'])."' WHERE bruker_id=".intval($brukerinfo['bruker_id']), $connection);
			$ckexpire = strtotime($config['ckexpire']);
			if ($_POST['husk']) {
				setcookie($config['ckprefix']."_data", $secret, $ckexpire, $config['ckdir']);
			}
			$person = get_person($brukerinfo['person_id']);
			$_SESSION['is_logged_in'] = true;
			$_SESSION['bruker_id'] = $brukerinfo['bruker_id'];
			$_SESSION['person_id'] = $brukerinfo['person_id'];
			$_SESSION['level'] = $brukerinfo['level'];
			$_SESSION['navn'] = $person['fornavn']." ".$person['etternavn'];
			$_SESSION['email'] = $person['email'];
			$_SESSION['lastlog'] = $expire;
			$_SESSION['previouslog'] = $brukerinfo['lastlog'];
			$_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
			$_SESSION['vis_generelt_nav'] = true;;
		}
		header("Location: ./userinfo.php");
		exit();
	}
}

function do_relogin() {
	global $connection, $table_prefix, $config;
	$brukersjekk = mysql_query("SELECT * FROM `".$table_prefix."brukere` WHERE secret='".$_COOKIE[$config['ckprefix']."_data"]."' && fingerprint='".md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'])."'", $connection) or exit(mysql_error());
	if (mysql_num_rows($brukersjekk) == 0) {
		do_logout();
	} else {
		$brukerinfo = mysql_fetch_assoc(mysql_query("SELECT * FROM `".$table_prefix."brukere` WHERE secret='".$_COOKIE[$config['ckprefix']."_data"]."'", $connection));
		$now = time();
		$expire = time() + $config['autologout'];
		$person = get_person($brukerinfo['person_id']);
		$_SESSION['is_logged_in'] = true;
		$_SESSION['bruker_id'] = $brukerinfo['bruker_id'];
		$_SESSION['person_id'] = $brukerinfo['person_id'];
		$_SESSION['level'] = $brukerinfo['level'];
		$_SESSION['navn'] = $person['fornavn']." ".$person['etternavn'];
		$_SESSION['email'] = $person['email'];
		$_SESSION['lastlog'] = $expire;
		if (!$_SESSION['previouslog']) {
			$_SESSION['previouslog'] = $brukerinfo['lastlog'];
		}
		$_SESSION['fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
		activity_log();
	}
}

function activity_log() {
	global $connection, $table_prefix, $config;
	$logtime = time();
	$expire = time() + $config['autologout'];
	$brukerinfo = mysql_fetch_array(mysql_query("SELECT * FROM `".$table_prefix."brukere` WHERE bruker_id=".intval($_SESSION['bruker_id']), $connection));
	if ($brukerinfo['fingerprint'] != md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'])) {
		do_logout();
		exit();
	} else {
		mysql_query("UPDATE `".$table_prefix."brukere` SET lastlog=".intval($expire).", nowlog=".intval($logtime)." WHERE bruker_id=".intval($_SESSION['bruker_id']), $connection);
		$brukere = get_brukere();
		foreach ($brukere as $bruker) {
			if ($bruker['lastlog'] < time()) {
				mysql_query("UPDATE `".$table_prefix."brukere` SET nowlog=0 WHERE bruker_id=".intval($bruker['bruker_id']), $connection);
				unlock_roller_all($bruker['person_id']);
			}
		}
	}
}

function do_logout() {
	global $connection, $table_prefix, $config;
	mysql_query("UPDATE `".$table_prefix."brukere` SET lastlog=nowlog,nowlog=0,fingerprint='' WHERE bruker_id=".intval($_SESSION['bruker_id']), $connection);
	unlock_roller_all($_SESSION['person_id']);
	setcookie($config['ckprefix']."_data", "", 1, $config['ckdir']);
	session_unset();
	$_SESSION = array();
	session_destroy();
	header("Location: ./login.php");
	exit();
}

function deny_login() {
	global $connection, $table_prefix, $config;
	mysql_query("UPDATE `".$table_prefix."brukere` SET nowlog=0,fingerprint='' WHERE bruker_id=".intval($_SESSION['bruker_id']), $connection);
	unlock_roller_all($_SESSION['person_id']);
	setcookie($config['ckprefix']."_data", "", 1, $config['ckdir']);
	session_unset();
	$_SESSION = array();
	$_SESSION['message'] = 'Login denied.';
	header("Location: ./login.php");
	exit();
	
}

function reset_password($user) {
	global $LANG, $connection, $table_prefix, $config;
	$query = "SELECT b.bruker_id, a.email, b.locked FROM `".$table_prefix."personer` a, `".$table_prefix."brukere` b WHERE a.person_id = b.person_id AND (b.brukernavn='".mysql_real_escape_string($user)."' OR a.email='".mysql_real_escape_string($user)."')";
	$brukersjekk = mysql_query($query, $connection) or exit(mysql_error());
	if (mysql_num_rows($brukersjekk) == 0) {
		$_SESSION['resetmsg'] = $LANG['MESSAGE']['passwordreset_failed'];
	} else {
		unset($_SESSION['failed_attempt']);
		$brukerinfo = mysql_fetch_assoc($brukersjekk);
		$newpass = rand_str(8);
		mysql_query("UPDATE `".$table_prefix."brukere` SET passord='".md5($newpass)."',locked=0 WHERE bruker_id=".intval($brukerinfo['bruker_id']), $connection) or exit(mysql_error());
		mail($brukerinfo['email'], 'Akkar password reset', 'Your new password is: '.$newpass, 'From: Akkar/'.$config['arrgruppenavn'].' <'.$config['akkar_admin_email'].'>');
		$_SESSION['resetmsg'] = $LANG['MESSAGE']['passwordreset_complete'];
	}
	header("Location: ./login.php");
	exit();
}


######################
# Diverse funksjoner #
######################

function get_configuration() {
	global $connection, $table_prefix;
	$result = mysql_query("SELECT * FROM `".$table_prefix."config`", $connection);
	//for ($i = 0; $i < mysql_num_rows($result); $i++) {
	//	$thisconf = mysql_fetch_array($result);
	while ($thisconf = mysql_fetch_array($result)) {
		$config[$thisconf['name']] = $thisconf['value'];
	}
	# Unserialize the serialzed config-settings.
	$config['types_not_in_lists'] = unserialize($config['types_not_in_lists']);
	$config['fields_not_in_person_lists'] = unserialize($config['fields_not_in_person_lists']);
	$config['fields_not_in_contacts_list'] = unserialize($config['fields_not_in_contacts_list']);
	return $config;

}

function save_config() {
	global $connection, $table_prefix;
	if (!$_POST['allow_exportformat_override']) {
		$_POST['allow_exportformat_override'] = "0";
	}
	if (!$_POST['use_autoregion']) {
		$_POST['use_autoregion'] = "0";
	}
	if (!$_POST['use_overlib_fade']) {
		$_POST['use_overlib_fade'] = "0";
	}	
	if (!$_POST['send_security_warning']) {
		$_POST['send_security_warning'] = "0";
	}
	foreach ($_POST as $name=>$value) {
		if ($name != "nyconfig") {
			if (is_array($value)) {
				$value = sql_serialize($value);
			}
			mysql_query("UPDATE `".$table_prefix."config` SET value='".$value."' WHERE name='".$name."'", $connection);
		}
	}
}

function logged_on_now() {
	global $connection, $table_prefix;
	$brukere = get_brukere();
	foreach ($brukere as $bruker) {
		if (($bruker['nowlog'] != 0) && ($bruker['bruker_id'] != $_SESSION['bruker_id'])) {
			$person = get_person($bruker['person_id']);
			$bruker['navn'] = $person['fornavn']." ".$person['etternavn'];
			$logged_on[] = $bruker;
		}
	}
	return $logged_on;
}

function db_select($table, $cond) {
	global $connection, $table_prefix;
	$result = mysql_query("SELECT * FROM `".$table_prefix.$table."` WHERE ".$cond, $connection);
	if (mysql_num_rows($result) == 1) {
		return mysql_fetch_assoc($result);
	}
	//for ($i = 0; $i < mysql_num_rows($result); $i++) {
	//	$return[] = mysql_fetch_assoc($result);
	while ($tuple = mysql_fetch_assoc($result)) {
		$return[] = $tuple;
	}
	if (is_array($return)) {
		return $return;
	}
	return false;
}

function db_insert($table, $fields, $values) {
	global $connection, $table_prefix;
	if (mysql_query("INSERT INTO `".$table_prefix.$table."` (".$fields.") VALUES (".$values.")", $connection)) {
		if ($return = mysql_insert_id()) {
			return $return;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

function db_update($table, $parms, $cond) {
	global $connection, $table_prefix;
	if ($result = mysql_query("UPDATE `".$table_prefix."$table` SET $parms WHERE $cond", $connection)) {
		return true;
	}
	return mysql_error();
}

function get_poststed($zipcode) {
	global $connection, $table_prefix, $config;
	$region = mysql_fetch_assoc(mysql_query("SELECT region FROM `".$table_prefix."zipcodemap` WHERE zipcode='".$zipcode."'", $connection));
	if ($region) {
		return $region['region'];
	}
	return false;
}

// TODO: Check for illegal characters... must do further research.
function contains_illegal_chars($string) {
	$illegal = array("\\", "/", ".", chr(0), chr(255));
	foreach($illegal as $ill) {
		if(false !== strpos($string, $ill)) {
			return true;
		}
	}

	return false;
}

function do_dbbackup() {
	global $connection, $table_prefix, $sqlbase, $sqlserver, $config;
	
	$tablenames = get_tablenames();
	if ($_POST['ekstratabeller'] != "") {
		$extras = explode(",", $_POST['ekstratabeller']);
		foreach($extras as $extra) {
			$extra = trim($extra);
			if(!contains_illegal_chars($extra) && !is_numeric($extra))
				$tablenames[] = $extra;
		}
	}
	foreach ($tablenames as $tablename) {
		$sqlfields = mysql_query("DESCRIBE `".$sqlbase."`.".$tablename, $connection);
		//for ($i = 0; $i < mysql_num_rows($sqlfields); $i++) {
		//	$data = mysql_fetch_assoc($sqlfields);
		while ($data = mysql_fetch_assoc($sqlfields)) {
			$tables[$tablename][$data['Field']] = $data;
		}
	}
	$filename = "tmp/akkar_db-".strftime("%d%b%Y-%H%M", time()).".sql";
	if (is_file($filename)) { unlink($filename); }
	$fp = fopen($filename, "w");
	fwrite($fp, "--
-- MySQL database dumpfile
-- Generated by AKKAR-".$config[version]."
-- http://akkar.sourceforge.net/
--
-- Backup executed by ".$_SESSION['navn']."
-- Date: ".strftime($config['long_dateformat']." (%H:%M)", time())."
--
-- Host: ".$sqlserver."    Database: ".$sqlbase."
-- ------------------------------------------------------
-- Server version	".mysql_get_server_info($connection)."

");
	foreach ($tables as $tablename=>$table) {
		fwrite($fp, "\r\n--\r\n-- Table structure for `$tablename`\r\n--\r\n\r\nDROP TABLE IF EXISTS `$tablename`;\r\nCREATE TABLE `$tablename` (");
		foreach ($table as $fieldname=>$info) {
			$field_def = "`".$info['Field']."` ".$info['Type'];
			if ($info['Key']) {
				$keys .= "`".$info['Field']."`,";
				$field_def .= " NOT NULL";
			}
			if ($info['Default'] != "" && $info['Type'] != "timestamp") { // timestamp has its own inherent default
				$field_def .= " DEFAULT '".$info['Default']."'";
			}
			if ($info['Extra']) {
				$field_def .= " ".$info['Extra'];
			}
			fwrite($fp, "\r\n\t$field_def,");
			unset($field_def);
		}
		fwrite($fp, "\r\n\tPRIMARY KEY (".substr($keys, 0, -1).")\r\n);\r\n");
		unset($keys);
		if (!$_POST['struktur']) {
			fwrite($fp, "\r\n--\r\n-- Data for `$tablename`\r\n-- \r\n\r\n");
			unset($keys);
			$data_result = mysql_query("SELECT * FROM `$tablename`", $connection);
			//for ($i = 0; $i < mysql_num_rows($data_result); $i++) {
			//	$data = mysql_fetch_assoc($data_result);
			while ($data = mysql_fetch_assoc($data_result)) {
				foreach ($data as $fieldname=>$value) {
					$coltype = $table[$fieldname]['Type'];
					if(false !== ($pos = strpos($coltype, '('))) {
						$coltype = substr($coltype, 0, $pos);
					} elseif(false !== ($pos = strpos($coltype, ' '))) {
						$coltype = substr($coltype, 0, $pos);
					}
					switch($coltype) {
						case "tinyint" :
						case "smallint" :
						case "mediumint" :
						case "int" :
						case "bigint" :
							$sql .= intval($value).",";
							break;
						case "float" :
						case "double" :
							$sql .= floatval($value).",";
							break;
						case "char" :
						case "varchar" :
						case "binary" :
						case "varbinary" :
						case "date" :
						case "datetime" :
						case "time" :
						case "timestamp" :
						case "year" :
						case "tinytext" :
						case "text" :
						case "mediumtext" :
						case "longtext" :
						case "tinyblob" :
						case "blob" :
						case "mediumblob" :
						case "longblob" :
						case "enum" :
						case "set" :
							$sql .= "'".addslashes($value)."',";
							break;
						default :
							$sql .= "NULL,";
					}
				}
				fwrite($fp, "INSERT INTO `$tablename` VALUES (".str_replace("\r\n", "\\r\\n", substr($sql, 0, -1)).");\r\n");
				unset($sql);
			}
		}
	}
	fclose($fp);
	return $filename;
}

function do_dbrestore($restorefile) {
	global $connection, $LANG;
	if ($restorefile == "0") {
		$restorefile = "./tmp/".$_FILES['restore_fil']['name'];
		move_uploaded_file($_FILES['restore_fil']['tmp_name'], $restorefile);
		if (strtolower(substr($_FILES['restore_fil']['name'], -3, 3)) == ".gz") {
			$gzresdata = gzfile($restorefile);
			foreach ($gzresdata as $gzline) {
				if ((substr($gzline, 0, 2) != "--") && ($gzline != "")) {
					$resdata .= trim($gzline);
				}
			}
		} else {
			$fresdata = file($restorefile);
			foreach ($fresdata as $fline) {
				if ((substr($fline, 0, 2) != "--") && ($fline != "")) {
					$resdata .= trim($fline);
				}
			}
		}
	} else {
		$fresdata = file($restorefile);
		foreach ($fresdata as $fline) {
			if ((substr($fline, 0, 2) != "--") && ($fline != "")) {
				$resdata .= trim($fline);
			}
		}
	}
	unlink($restorefile);
	$sql = split_sql_file($resdata, ";");
	foreach ($sql as $query) {
		mysql_query($query, $connection) or exit(mysql_error());
	}
	return true;
}

function check_tables() {
	global $connection, $sqlbase, $sqlserver, $table_prefix, $LANG;

	$tablenames = get_tablenames();
	$akkartables = array();
	foreach ($tablenames as $tablename) {
		$akkartables[$tablename] = false;
	}
	unset($tablenames, $tablename);

	$sqltables = mysql_query("SHOW TABLES FROM `$sqlbase`", $connection) or exit(mysql_error());
	//for ($i = 0; $i < mysql_num_rows($sqltables); $i++) {
	//	list($table) = mysql_fetch_row($sqltables);
	while(list($table) = mysql_fetch_row($sqltables)) {
		$akkartables[$table] = true;
	}
	foreach ($akkartables as $tablename=>$exists) {
		if (!$exists) {
			$errors[] = $LANG['ERROR']['table_missing'].": $tablename";
		}
	}
	if ($errors) {
		return $errors;
	}
	return false;
}

function get_tablenames() {
	global $table_prefix, $config;

	$tablenames = array(
		$table_prefix."brukere",
		$table_prefix."config",
		$table_prefix."deadlines",
		$table_prefix."filsystem",
		$table_prefix."filvedlegg",
		$table_prefix."gruppe_roller",
		$table_prefix."grupper",
		$table_prefix."kalender",
		$table_prefix."kjentfolk",
		$table_prefix."kontakter",
		$table_prefix."mugshots",
		$table_prefix."oppgaver",
		$table_prefix."paameldinger",
		$table_prefix."personer",
		$table_prefix."plott",
		$table_prefix."plott_medlemmer",
		$table_prefix."rolleforslag",
		$table_prefix."rollekonsept",
		$table_prefix."roller",
		$table_prefix."spill",
		$table_prefix."tabellmaler",
		$table_prefix."tabellmaler_data"
	);
	if ($config['use_autoregion']) {
		$tablenames[] = $table_prefix."zipcodemap";
	}

	return $tablenames;
}

?>
