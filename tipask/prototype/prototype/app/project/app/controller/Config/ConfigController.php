<?php
/**
 * 游戏配置获取控制层
 * $Id: ConfigController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_ConfigController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oApp;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 *获取游戏列表
	 */
	public function rebuildAction()
	{
		//基础元素，必须参与验证
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = trim($this->request->sign);
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Config,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Config['Time']-time())<=600)
			{
				$oApp = new Config_App();
				$oPartner = new Config_Partner();
				$oServer = new Config_Server();
				$oPartnerApp = new Config_Partner_App();
				$oSocketType = new Config_SocketType();
				
				$oApp->reBuildAppConfig();
				$oPartner->reBuildPartnerConfig();
				$oServer->reBuildServerConfig();
				$oPartnerApp->reBuildPartnerAppConfig();
				$oSocketType->reBuildSocketTypeConfig();
			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		if($Config['ReturnType'])
		{
			echo json_encode($result);
		}
	}
	/**
	 * 获取单个游戏信息
	 */
	public function getAppInfoAction()
	{
		//基础元素，必须参与验证
		$Config['Time'] = abs(intval($this->request->Time));
		$Config['AppId'] = abs(intval($this->request->AppId));
		$Config['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = trim($this->request->sign);
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Config,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Config['Time']-time())<=600)
			{
				if($Config['AppId'])
				{
					$AppInfo = $this->oApp->getRow($Config['AppId']);
					if($AppInfo['AppId'])
					{
						$result = array('return'=>1,'AppInfo'=>$AppInfo);	
					}	
					else
					{
	 					$result = array('return'=>0,'comment'=>"无此游戏");					 	
					}				
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请指定游戏");					 	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		if($Config['ReturnType'])
		{
			echo json_encode($result);
		}
	}
	
}
