<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: config.php 15195 2014-07-23 07:18:26Z 334746 $
 */

$config = array();
$config['js'] = '/js/';
$config['style'] = '/style/';
$config['companyName'] = "5173";
$config['kindDefault'] = array(
		'1'=>'网上支付',
		'2'=>'卡类',
		'3'=>'手机/固话类',
		'4'=>'骏网一卡通',
		'5'=>'其他',
	); 
if(strstr($_SERVER['SERVER_NAME'],"limaogame.com"))
{
	$config['passporturl'] = 'http://passport.limaogame.com/';
		
}
else {
	$config['passporturl'] = 'http://my.test.com/';
 	
}
$config['OrderStatus'] = array('0'=>'已下单','1'=>'已支付','2'=>'已兑换','3'=>'已失败','4'=>'已扣款','-1'=>'已取消','5'=>'全部');
$config['ExchangeStatusArr'] = array('0'=>'新建','1'=>"已通知游戏服务器",'2'=>"已成功",'3'=>"已失败");
$config['ExchangeTypeArr'] = array('1'=>'支付自动兑换','2'=>"管理员发放",'3'=>"用户自主兑换",'4'=>'产品包兑换');
$config['ProductSendTypeArr'] = array('0'=>'全部','ProductPack'=>'礼包发放');
$config['ProductTypeArr'] = array('0'=>'全部','skin'=>'皮肤','hero'=>'英雄','product'=>'道具','appcoin'=>'游戏币','money'=>'货币');
  
return $config;
