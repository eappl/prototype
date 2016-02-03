<?php
/**
 * 服务器配置获取控制层
 * $Id: ServerController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_ServerController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oServer;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oServer = new Config_Server();

	}

	/**
	 *获取服务器列表登录
	 */
	public function getServerListAction()
	{

		//基础元素，必须参与验证
		$Config['AppId'] = abs(intval($this->request->AppId));
		$Config['PartnerId'] = abs(intval($this->request->PartnerId));
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
				$ServerList = $this->oServer->getByAppPartner($Config['AppId'],$Config['PartnerId'],"*",1);
				if(is_array($ServerList))
				{
					$result = array('return'=>1,'ServerList'=>$ServerList);	
				}
				else
				{
					$result = array('return'=>0,'comment'=>'您所选择的游戏－平台下无服务器');					 	
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
	/**
	 *获取服务器列表登录
	 */
	public function getServerByIpAction()
	{

		//基础元素，必须参与验证
		$Config['ServerIp'] = $this->request->ServerIp;
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
				if($Config['ServerIp'])
				{
					$ServerIp =  Base_Common::ip2long($Config['ServerIp']);
					$ServerInfo = $this->oServer->getByIp($ServerIp);
					if($ServerInfo['ServerId'])
					{
						$result = array('return'=>1,'ServerInfo'=>$ServerInfo,'comment'=>'找到服务器');	
					}
					else
					{
						$result = array('return'=>2,'comment'=>'你所查询的IP不属于任何服务器');						 	
					}
						
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入服务器IP");					 	
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
		if($Config['ReturnType']==1)
		{
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			if($result['return']==1)
			{
				$r = $r."|".$result['ServerInfo']['ServerId']."|".iconv('UTF-8','GBK',$result['ServerInfo']['name']);
			}
			echo $r;
		}
	}
	
}
