<?php
//error_reporting(0);
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
date_default_timezone_set('Etc/GMT-8');
defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

	
@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
$FastDFSClient = new FastDFSClient();
$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', time()) . '/';// 设置附件上传目录
$FastDFSInfo = $FastDFSClient->upload("sk");

if($FastDFSInfo != -1)
{
	$arr=array("state"=>1,"small_pic"=>$FastDFSInfo,"big_pic"=>$FastDFSInfo);
}
else
{
	$arr=array("state"=>0);
}

echo json_encode($arr);