<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: database.php 1362 2010-01-17 11:00:03Z 闄堟檽涓?$
 */

include dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))."/CommonConfig/databaseConfig.php";

$db = array();
$db['isPersistent'] = 0;

$db['lm_config_global'][0] = array(
	'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'lm_config_global',
);
$db['lm_config_global'][1] = array(
	'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'lm_config_global',
);
$db['lm_config_game'][0] = array(
	'host' => HOST_M4,
	'user' => USER_M4,
	'password' => PASSWORD_M4,
	'port' => PORT_M4,
	'database' => 'lm_config_game',
);
$db['lm_config_game'][1] = array(
	'host' => HOST_S4,
	'user' => USER_S4,
	'password' => PASSWORD_S4,
	'port' => PORT_S4,
	'database' => 'lm_config_game',
);
return $db;
?>
