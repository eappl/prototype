<?php 
include dirname(dirname(__FILE__))."/CommonConfig/databaseConfig.php";
$CommonConfig = require(dirname(dirname(__FILE__))."/CommonConfig/commonConfig.php");
define('DB_HOST', HOST_M5);
define('DB_PW', PASSWORD_M5);
define('DB_USER', USER_M5);
define('DB_NAME', 'tipask');
define('DB_CHARSET', 'utf8');
define('DB_TABLEPRE', 'ask_');
define('DB_CONNECT', 0);
define('TIPASK_CHARSET', 'UTF-8');
define('TIPASK_VERSION', '2.0');
define('TIPASK_RELEASE', '20120702');
define('SITE_URL', $CommonConfig['ScadminUrl']); 
//历史库数据库配置
define('DB_HOST_H', '192.168.160.130');
define('DB_USER_H', 'ask_user');
define('DB_PW_H', 'sY9GgzAQy6bwyAEq?%w7');
define('DB_NAME_H', 'TipaskHistory');
define('DB_CHARSET_H', 'utf8');
define('DB_CONNECT_H', 0);
