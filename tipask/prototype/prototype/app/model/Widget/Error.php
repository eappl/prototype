<?php
/**
 * 支付错误提示信息
 * @author chen<cxd032404@hotmail.com>
 * $Id: Error.php 15195 2014-07-23 07:18:26Z 334746 $
 *
 */
class Widget_Error
{
	/**
	 * 错误提示信息
	 * @param $errorTile 错误标题
	 * @param $errorDome 错误信息
	 * @param $returnUrl 跳转网址
	 * @param $link 返回说明
	 * @param $errorNum 错误编号 0成功、1错误、2等待
	 * @param $delay 跳转等待时间
	 */
	public static function PayError( $errorDome = "正在执行操作", $errorTile = "提示信息", $returnUrl = "", $link = "请点击这里返回", $errorNum = 1, $delay = 3)
	{
		include Base_Template::factory("Pay_Error_Error")->get();
		exit;
	}
}