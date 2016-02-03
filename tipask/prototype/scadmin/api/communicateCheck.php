<?php
header("Content-type: text/html; charset=utf8"); 
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
// 普通的 http 通知方式
error_reporting(0);
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
require_once TIPASK_ROOT.'/lib/global.func.php';

$check_type = trim($_GET['type']);
$check_type = in_array($check_type,array('mobile','weixin','tel','qq'))?$check_type:'mobile';
$value = trim($_GET['value']);
if($check_type=='mobile')
{
    if($value!="")
    {
        $result = checkmobile($value);
        if($result)
        {
            $return = array('return'=>1);    
        }
        else
        {
            $return = array('return'=>0,'comment'=>'请输入正确格式的手机号');    
        }
    }
    else
    {
        $return = array('return'=>0,'comment'=>'请输入正确格式的手机号');    
    }  
}
elseif($check_type=='qq')
{
    if($value!="")
    {
        $result = isQQ($value);
        if($result)
        {
            $return = array('return'=>1);    
        }
        else
        {
            $return = array('return'=>0,'comment'=>'请输入正确格式的QQ号');    
        }  
    }
    else
    {
        $return = array('return'=>0,'comment'=>'请输入正确格式的QQ号');    
    } 
}
elseif($check_type=='weixin')
{
    if($value!="")
    {
        if(strlen($value) > 20 || strlen($value) < 4)    
        {
            $return = array('return'=>0,'comment'=>'请输入正确格式的微信号');    

        }
        else
        {
            $return = array('return'=>1);    
        }  
    }
    else
    {
        $return = array('return'=>0,'comment'=>'请输入正确格式的微信号');    
    } 
}
echo json_encode($return);
