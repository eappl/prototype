<?php
/**
 * 游戏配置获取控制层
 * $Id: AppController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_AppController extends AbstractController
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
		$this->oApp = new Config_App();

	}

	/**
	 *获取游戏列表
	 */
	public function getAppListAction()
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
				$AppList = $this->oApp->getAll("*",1);
				$result = array('return'=>1,'AppList'=>$AppList);	
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
