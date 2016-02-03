<?php
/**
 * 通用登录控制层
 * $Id: LoginController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class LoginController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oLogin;
	protected $oPartnerApp;
	protected $oServer;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
		$this->oLogin = new Lm_Login();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oServer = new Config_Server();
	}
	/**
	 *用户ID方式登录
	 */
	public function loginByIdAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['UserPassWord'] = $this->request->UserPassWord;
		$User['LoginTime'] = abs(intval($this->request->LoginTime));
		$User['ServerId'] = (($this->request->ServerId));
		$User['UserLoginIP'] = trim($this->request->UserLoginIP);
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		$User['UserLoginIP'] = Base_Common::ip2long($User['UserLoginIP']);
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				if($User['ServerId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['LoginTime']-time())<=600)
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserById($User['UserId']);
						$result = $this->oLogin->UserLogin($User,$UserInfo);
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"请选择服务器");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入用户ID");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);

			if($result['return']==1)
			{
				$r = $r."|".$result['LoginId']."|".$result['adult'];
			}
			echo $r;
		}
	}
	/**
	 *用户名方式登录
	 */
	public function loginByNameAction()
	{
		//基础元素，必须参与验证
		$User['UserName'] = $this->request->UserName;
		$User['UserPassWord'] = $this->request->UserPassWord;
		$User['LoginTime'] = abs(intval($this->request->LoginTime));
		$User['ServerId'] = abs(intval($this->request->ServerId));
		$User['UserLoginIP'] = trim($this->request->UserLoginIP);
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		$User['UserLoginIP'] = Base_Common::ip2long($User['UserLoginIP']);

		//不参与验证的元素
		$start = microtime(true);
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserName'])
			{
				if($User['ServerId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['LoginTime']-time())<=600)
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserByName($User['UserName']);
						unset($User['UserName']);
						$User['UserId'] = $UserInfo['UserId'];
						$result = $this->oLogin->UserLogin($User,$UserInfo);
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"请选择服务器");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入用户名");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$end = microtime(true);
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);
			if($result['return']==1)
			{
				$r = $r."|".$result['LoginId']."|".$result['UserId']."|".$result['adult'];
			}
			echo $r;
		}
	}
	/**
	 *登出
	 */
	public function logoutAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['LoginId'] = ($this->request->LoginId);
		$User['LogoutTime'] = abs(intval($this->request->LogoutTime));
		$User['ServerId'] = abs(intval($this->request->ServerId));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';

		$start = microtime(true);		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				if($User['ServerId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['LogoutTime']-time())<=600)
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserById($User['UserId']);
						$result = $this->oLogin->UserLogout($User,$UserInfo);
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"请选择服务器");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入用户ID");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$end = microtime(true);
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);
			echo $r;
		}
	}
	/**
	 *用户ID方式登录
	 */
	public function getLastLoginAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ServerId'] = (($this->request->ServerId));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询用户
					$LoginLog = $this->oLogin->getLoginDetail(0,0,$User['UserId'],$User['ServerId'],0,1,1);
					$result = array('return'=>1,'LoginLog'=>$LoginLog['LoginDetail']);	
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入用户ID");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
}
