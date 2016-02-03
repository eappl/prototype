<?php
header("Content-type: text/html; charset=gb2312"); 
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
// 普通的 http 通知方式
error_reporting(0);
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
require_once TIPASK_ROOT.'/config.php';
require_once TIPASK_ROOT.'/lib/db.class.php';
require_once TIPASK_ROOT.'/lib/CacheMemcache.class.php';
$memcache = new CacheMemcache();
$db=new db(DB_HOST, DB_USER, DB_PW, DB_NAME , DB_CHARSET , DB_CONNECT);
$count = intval($_GET['count'])>0?intval($_GET['count']):10;
$ac = trim($_GET['ac'])?trim($_GET['ac']):"online_operator_list";
if($ac=="online_operator_list")
{
    $flush = intval($_GET['flush'])?intval($_GET['flush']):0;
    online_operator_list($memcache,$db,$count,$flush);    
}
elseif($ac=="operator_info")
{
    operator_info($memcache,$db,intval($_GET['id']));    
}

function online_operator_list($memcache,$db,$count,$flush)
{
    if($flush==0)
    {
        $cache_data = $memcache->get('new_online_operator_'.$count);
        if(false !== $cache_data) exit($cache_data);
    }
    $sql = "SELECT id,isonjob,isbusy,slogan,cno as login_name,name as nick_name,login_name as loginid,QQ,qq_url,weixin,mobile,tel,slogan,photo as icon,login_name_officer as superior_loginid
    ,name_officer as nick_name_officer,cno_officer as superior_name,photo_officer as superior_icon,QQ_officer as superior_qq,weixin_officer as superior_weixin
    ,mobile_officer as superior_mobile,tel_officer as superior_tel,qq_url_officer as superior_qqidkey FROM ".DB_TABLEPRE."operator WHERE isonjob=1 AND isbusy=0 ORDER BY RAND() DESC LIMIT $count";
    $sql = "SELECT id,isonjob,isbusy,slogan,cno as login_name,name as nick_name,login_name as loginid,QQ,qq_url,weixin,mobile,tel,slogan,photo as icon FROM ".DB_TABLEPRE."operator WHERE isonjob=1 AND isbusy=0 ORDER BY RAND() DESC LIMIT $count";
    $operator = $db->query($sql);
    while($row = $db->fetch_array($operator))
    {
        $t = explode("IDKEY=",$row['qq_url']);
        $row['qq_url'] = $t[1];
        foreach($row as $key => $value)
        {
            $temp[$key] = "$key:\"".$value."\"";    
        }
        $operator_list[$row['id']] = "{".implode(",",$temp)."}";
        //$operator_list[$row['id']] = "{id:\"".$row['id']."\",login_name:\"".cutstr($row['cno'],12)."\",icon:\"".$row['photo']."\",QQ:\"".cutstr($row['QQ'],12)."\",weixin:\"".cutstr($row['weixin'],12)."\",mobile:\"".cutstr($row['mobile'],15)."\",tel:\"".cutstr($row['tel'],15)."\",slogan:\"".cutstr($row['slogan'],12)."\",qqidkey:\"".$row['qq_url']."\",superior_name:\"".cutstr($row['cno_officer'],12)."\",superior_icon:\"".$row['photo_officer']."\",superior_qq:\"".cutstr($row['QQ_officer'],12)."\",superior_weixin:\"".cutstr($row['weixin_officer'],12)."\",superior_mobile:\"".cutstr($row['mobile_officer'],15)."\",superior_tel:\"".cutstr($row['tel_officer'],15)."\",superior_qqidkey:\"".$row['qq_url_officer']."\"}";

    }
    $str = "operator({operator:[".implode(",",$operator_list)."]})";
    $str = iconv("UTF-8", "gb2312//IGNORE", $str);
    $memcache->set('online_operator_'.$count,$str,3);//缓存3秒
    exit($str);
}
function operator_info($memcache,$db,$id)
{
    $cache_data = $memcache->get('operator_'.$id);
    if(false !== $cache_data) exit($cache_data);
    $sql = "SELECT id,isonjob,isbusy,slogan,cno as login_name,name as nick_name,login_name as loginid,QQ,qq_url,weixin,mobile,tel,slogan,photo as icon,login_name_officer as superior_loginid
    ,name_officer as nick_name_officer,cno_officer as superior_name,photo_officer as superior_icon,QQ_officer as superior_qq,weixin_officer as superior_weixin
    ,mobile_officer as superior_mobile,tel_officer as superior_tel,qq_url_officer as superior_qqidkey FROM ".DB_TABLEPRE."operator WHERE id = ".$id." limit 1";
    $operator = $db->query($sql);
    while($row = $db->fetch_array($operator))
    {
        $t = explode("IDKEY=",$row['qq_url']);
        $row['qq_url'] = $t[1];
        $t2 = explode("IDKEY=",$row['qq_url_officer']);
        $row['qq_url_officer'] = $t[1];
        foreach($row as $key => $value)
        {
            $temp[$key] = "$key:\"".$value."\"";    
        }
        $operator_info = "{".implode(",",$temp)."}";
        //$operator_info = "{id:\"".$row['id']."\",login_name_sc:\"".cutstr($row['login_name'],12)."\",login_name_vadmin:\"".cutstr($row['Vadmin'],12)."\",icon:\"".$row['photo']."\",QQ:\"".cutstr($row['QQ'],12)."\",weixin:\"".cutstr($row['weixin'],12)."\",mobile:\"".cutstr($row['mobile'],15)."\",tel:\"".cutstr($row['tel'],15)."\",slogan:\"".cutstr($row['slogan'],12)."\",isonjob:\"".intval($row['isonjob'])."\",isbusy:\"".intval($row['isbusy'])."\",qqidkey:\"".$row['qq_url']."\",superior_name:\"".cutstr($row['name_officer'],12)."\",superior_icon:\"".$row['photo_officer']."\",superior_qq:\"".cutstr($row['QQ_officer'],12)."\",superior_weixin:\"".cutstr($row['weixin_officer'],12)."\",superior_mobile:\"".cutstr($row['mobile_officer'],15)."\",superior_tel:\"".cutstr($row['tel_officer'],15)."\",superior_qqidkey:\"".$row['qq_url_officer']."\"}";
    }
    $str = "operator({operator:[".$operator_info."]})";
    $str = iconv("UTF-8", "gb2312//IGNORE", $str);
    $memcache->set('operator_'.$row['id'],$str,60);//缓存60秒
    exit($str);
}
function cutstr($str,$len,$replace = '...')
{
    $ascLen=strlen($str);

    $i = 0;
    for($i;$i<$ascLen;$i++){

    $c=ord(substr($str,0,1));

    if(ord(substr($str,0,1)) >252){$p = 5;}elseif($c > 248){$p = 4;}elseif($c > 240){$p = 3;}elseif($c > 224){$p = 2;}elseif($c > 192){$p = 1;}else{$p = 0;}

    $truekey=substr($str,0,$p+1);

    if($truekey===false){break;}       

    $splikey[]=$truekey;

    $str=substr($str,$p+1);

    }
    $strlen = count($splikey);

    if($strlen<=$len)
    {
        return (implode("",$splikey));
    }
    else
    {
        for($i=0;$i<$len;$i++)
        {
            $t[$i] = array_shift($splikey);    
        }
        return implode("",$t).$replace;  
    }
    
    for($i = 0;$i<$len;$i++)
    {
        $str.=  $splikey[$i];    
    }
    return $str;
}
