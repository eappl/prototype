<?php
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
date_default_timezone_set('Etc/GMT-8');

// 普通的 http 通知方式
error_reporting(0);
require_once TIPASK_ROOT.'/config.php';
require TIPASK_ROOT.'/lib/pdo/Mysql/pdo.class.php';
require TIPASK_ROOT.'/lib/pdo/Mysql/hash.class.php';
require_once TIPASK_ROOT.'/lib/global.func.php';
require_once TIPASK_ROOT.'/lib/db.class.php';
require_once TIPASK_ROOT.'/lib/config.class.php';
require_once TIPASK_ROOT.'/lib/CacheMemcache.class.php';
require TIPASK_ROOT.'/model/base.class.php';

$jsoncallback = $_GET['jsoncallback'];	
!isset($_GET['userInfo']) && exit($jsoncallback."([{msg:\"usrInfo 不能为空\",return:0}]");
!isset($_GET['scid']) && exit($jsoncallback."([{msg:\"scid 不能为空\",return:0}]"); 
!isset($_GET['time']) && exit($jsoncallback."([{msg:\"time 不能为空\",return:0}]"); 
!isset($_GET['userInfo']) && exit($jsoncallback."([{msg:\"userInfo 不能为空\",return:0}]");

$scid =  intval($_GET['scid']); // 绑定客服id
$time = intval($_GET['time']); // 操作时间10位整形
if(abs($time-time()) >=600)
{
	$time = time();
}

$author = trim(urldecode($_GET['userInfo'])); // 登陆用户名
if($scid<=0)
{
	exit($jsoncallback."([{msg:\"scid 数值不对\",return:0}])");
}

$base = new base();
$base->load('bind_log');

$bindLogArr = array('author'=>$author, 'scid'=>$scid, 'bind_time'=>$time);
$result = $_ENV['bind_log']->bindUnbindOperator($bindLogArr); // 绑定解绑操作

if($result)
{
	//exit($jsoncallback."([{msg:\"success\",return:1}])");
}
else
{
	//exit($jsoncallback."([{msg:\"failure rollback\",return:0}])");
}

