<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: database.php 1362 2010-01-17 11:00:03Z 闄堟檽涓?$
 */

require_once dirname(dirname(dirname(__FILE__)))."/CommonConfig/databaseConfig.php";
$database = array();
$database['isPersistent'] = 0;

$database['tipask'][0] = array(
    'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'tipask');
$database['tipask'][1] = array(
    'host' => HOST_S51,
	'user' => USER_S51,
	'password' => PASSWORD_S51,
	'port' => PORT_S51,
	'database' => 'tipask');
$database['tipask'][2] = array(
    'host' => HOST_S52,
	'user' => USER_S52,
	'password' => PASSWORD_S52,
	'port' => PORT_S52,
	'database' => 'tipask');
$database['binding_service'][0] = array(
    'host' => HOST_M5,
	'user' => USER_M5,
	'password' => PASSWORD_M5,
	'port' => PORT_M5,
	'database' => 'binding_service');
$database['binding_service'][1] = array(
    'host' => HOST_S51,
	'user' => USER_S51,
	'password' => PASSWORD_S51,
	'port' => PORT_S51,
	'database' => 'binding_service');
$database['binding_service'][2] = array(
    'host' => HOST_S52,
	'user' => USER_S52,
	'password' => PASSWORD_S52,
	'port' => PORT_S52,
	'database' => 'binding_service');
$database['tipask_h_2013'][0] = array(
	'host' => HOST_L,
	'user' => USER_L,
	'password' => PASSWORD_L,
	'port' => PORT_L,
	'database' => 'TipaskHistory');
$database['tipask_h_2014'][0] = array(
	'host' => HOST_L,
	'user' => USER_L,
	'password' => PASSWORD_L,
	'port' => PORT_L,
	'database' => 'TipaskHistory');
$database['tipask_h_2015'][0] = array(
	'host' => HOST_L,
	'user' => USER_L,
	'password' => PASSWORD_L,
	'port' => PORT_L,
	'database' => 'TipaskHistory');
return $database;
?>
