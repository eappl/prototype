<?php
/**
 * 大区配置获取控制层
 * $Id: PartnerController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_PartnerController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oApp;
	protected $oPartnerApp;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oApp = new Config_App();
		$this->oPartnerApp = new Config_Partner_App();
	}

	/**
	 *用户ID方式登录
	 */
	public function getPartnerAppListAction()
	{
		//基础元素，必须参与验证
		$Config['AppId'] = abs(intval($this->request->AppId));
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
				$AppInfo = $this->oApp->getRow($Config['AppId']);
				if($AppInfo['AppId'])
				{
					$PartnerAppList = $this->oPartnerApp->getByApp($Config['AppId']);
					$result = array('return'=>1,'PartnerAppList'=>$PartnerAppList);	
				}
				else 
				{
					$result = array('return'=>0,'comment'=>"您所选择的游戏不存在");	
				 	
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
		else
		{
//			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
//			if($result['return']==1)
//			{
//				$r = $r."|".$result['LoginId']."|".$result['adult'];
//			}
//			echo $r;
		}
	}
	
}
