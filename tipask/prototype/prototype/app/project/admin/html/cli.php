<?php
/**
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: cli.php 15195 2014-07-23 07:18:26Z 334746 $
 *
 * cli模式运行说明
 * Windows平台:
 * D:\wamp\bin\php\php5.2.6\php.exe d:\wamp\www\wee/trunk/cli.php "ctl=test&ac=go"
 *
 * Linux平台:
 * /usr/local/php/bin/php /www/wee/cli.php "ctl=test&ac=go"
 */


/**
 * 仅cli模式下运行
 */
if (substr(PHP_SAPI, 0, 3) != 'cli') {
	echo "This script must run under cli mode.\n";
	exit;
}

set_time_limit(0);

include dirname(__FILE__) . '/init.php';

$request = new Base_Controller_Request_Cli();
$request->setParam('mod', 'cli');
Base_Controller_Front::getInstance()->setRequest($request)->dispatch();
