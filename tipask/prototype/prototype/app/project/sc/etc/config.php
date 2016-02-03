<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: config.php 15497 2014-12-18 09:13:55Z 334746 $
 */

$config = array();
$config['js'] = '/js/';
$config['style'] = '/style/';
$config['companyName'] = "5173";
$config['QuestionTypeList'] = array(
		'ask'=>'咨询',
		'suggest'=>'建议',
		'complain'=>'投诉',
	);
$config['QuestionStatusList'] = array(
		'0'=>'全部',
		'1'=>'解决中',
		'2'=>'已解决',
		'3'=>'已撤销',
	);
$config['ScUrl'] = "http://sc.5173.com";
$config['BroadCastZoneList'] = array(
		'0'=>'全部',
		'1'=>'首页',
		'2'=>'提问页',
	);
$config['CallTypeList'] = array(
		'1'=>'电话',
		'2'=>'短信',
	);
$config['DefaultOperatorPic'] = "http://img01.5173cdn.com/zixun_center/build/1.00/images/default_kf.png";
$config['ComplainRevokeUrl'] = "http://complain.5173esb.com/Sc/PostCancel.aspx";
$config['UnLoggedUserName']	 = "游客";
$CommonConfig = require(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))."/CommonConfig/commonConfig.php");


$config['ScUrl'] = $CommonConfig['ScUrl'];
$config['ScadminUrl'] = $CommonConfig['ScadminUrl'];
$config['sys_log_arr'] = $CommonConfig['sys_log_arr'];
return $config;
