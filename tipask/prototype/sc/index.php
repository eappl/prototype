<?php
/*the tipask entrance */
error_reporting(0);
date_default_timezone_set('Etc/GMT-8');
session_start();
header("Cache-control: private");
ini_set("magic_quotes_runtime",0);
$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(__FILE__));
define('SITE_URL','http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,-9) );
include TIPASK_ROOT.'/model/tipask.class.php';
$tipask = new tipask();
$tipask->run();
?>