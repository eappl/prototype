<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: database.php 1362 2010-01-17 11:00:03Z 闄堟檽涓?$
 */

include dirname(dirname(dirname(dirname(__FILE__))))."/CommonConfig/databaseConfig.php";

$db = array();
$db['isPersistent'] = 0;

$db['prototype_global'][0] = array(
	'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'prototype_global',
);
$db['prototype_global'][1] = array(
	'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'prototype_global',
);
$db['prototype_game'][0] = array(
	'host' => HOST_M4,
	'user' => USER_M4,
	'password' => PASSWORD_M4,
	'port' => PORT_M4,
	'database' => 'prototype_game',
);
$db['prototype_game'][1] = array(
	'host' => HOST_S4,
	'user' => USER_S4,
	'password' => PASSWORD_S4,
	'port' => PORT_S4,
	'database' => 'prototype_game',
);
$db['tipask'][0] = array(
    'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'tipask');
return $db;
?>
