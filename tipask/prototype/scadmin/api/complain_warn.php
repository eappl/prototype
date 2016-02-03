<?php
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
date_default_timezone_set('Etc/GMT-8');
// 普通的 http 通知方式
error_reporting(0);
require_once TIPASK_ROOT.'/config.php';
require_once TIPASK_ROOT.'/lib/db.class.php';
require_once TIPASK_ROOT.'/lib/global.func.php';
require_once TIPASK_ROOT.'/lib/CacheMemcache.class.php';

$db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME , DB_CHARSET , DB_CONNECT);
$memcache = new CacheMemcache();
$setting  =  $memcache->load('setting');

$ts_warn_maxNum = isset($setting['ts_warn_maxNum']) ? $setting['ts_warn_maxNum'] : 3; // 投诉同步失败报警最大数量,默认3
$ts_warn_num    = isset($setting['ts_warn_num']) ? $setting['ts_warn_num'] : 0;  // 投诉同步失败报警阈值:
$ts_warn_time   = isset($setting['ts_warn_time']) ? $setting['ts_warn_time'] : 0; // 投诉同步失败报警时间间隔 ,不填就是 所有

if( $ts_warn_time != 0 )
{
	
	$timeRang = time()-$ts_warn_time;
	$where .= " AND time > $timeRang";
}

$sql = "SELECT count(id) FROM ask_complain WHERE sync <= -$ts_warn_num $where";
$num = $db->result_first($sql);
echo $sql. '<br>ts_warn_maxNum = '.$setting['ts_warn_maxNum'].'<br>'.
	  'ts_warn_time = '.$setting['ts_warn_time'].'<br>'.
	  'ts_warn_num = '.$setting['ts_warn_num'].'<br>';
if( !empty( $num ) )
{
	
	if( $num >= $ts_warn_maxNum )
	{
		if( $ts_warn_time != 0 )
	    {
			$warm_message =  "$ts_warn_time 秒内 投诉失败数量：$num 报警";
			send_AIC('http://scadmin.5173.com/api/sync_data.php',$warm_message,1,'同步进程报警');
		}
		else
		{
			$warm_message = "投诉失败数量：$num 报警";
		}
	}
	else
    {
		$warm_message =  'ok';
	} 
} 
else
{
	$warm_message =  'ok';
}

echo $warm_message;
