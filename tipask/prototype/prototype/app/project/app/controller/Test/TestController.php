<?php
/**
 * 通用登录控制层
 * @author chen<cxd032404@hotmail.com>
 * $Id: TestController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Test_TestController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
	}

	/**
	 *账号生成
	 */
	public function indexAction()
	{
		$this->oUser->testDB();
		}
//	public function createUserAction()
//	{
//		//基础元素，必须参与验证
//		$User['UserName'] = $this->request->UserName;
//		$User['UserRegTime'] = abs(intval($this->request->UserRegTime));
//		$User['UserPassWord'] = $this->request->UserPassWord;
//		$User['UserPassWordR'] = $this->request->UserPassWordR;
//		$User['UserRegFrom'] = abs(intval($this->request->UserRegFrom));
//		$User['UserRegDetail'] = abs(intval($this->request->UserRegDetail));
//		//URL验证码
//		$sign = $this->request->sign;
//		//私钥，以后要移开到数据库存储
//		$p_sign = 'lm';
//		$sign_to_check = Base_common::check_sign($User,$p_sign);
//		//不参与验证的元素
//		$User['UserRegIP'] = $_SERVER["REMOTE_ADDR"];
//
//		//验证URL是否来自可信的发信方
//		if($sign_to_check==$sign)
//		{
//			//验证用户名有效性
//			if($User['UserName'])
//			{
//				//验证时间戳，时差超过600秒即认为非法
//				if(abs($User['UserRegTime']-time())<=600)
//				{
//					//验证密码长度不小于6
//					if(strlen($User['UserPassWord'])>=6)
//					{
//						//验证两次输入密码是否相同
//						if($User['UserPassWordR']==$User['UserPassWord'])
//						{
//				 			//新建用户
//				 			$CreateResult = $this->oUser->InsertUser($User);
//				 			//返回用户ID,不包含（23000）
//				 			if($CreateResult&&($CreateResult!=23000))
//				 			{
//								$checkResult = $this->oUser->GetUserByName($User['UserName']);
//								if(count($checkResult))
//								{
//					 				$result = array('return'=>1,'UserId' => $checkResult['UserId'].sprintf("%03d",$checkResult['UserPosionFix']),'comment'=>$User['UserName']."注册成功");
//					 			}
//					 			else
//					 			{
//					 				$result = array('return'=>0,'comment'=>"注册失败");
//					 			}
//				 			}
//				 			else 
//				 			{
//	 				 			$result = array('return'=>2,'comment'=>"用户已存在");
//				 			}
//						}
//						else
//						{
//				 			$result = array('return'=>2,'comment'=>"两次输入的密码不相符");	
//						}	
//					}
//					else 
//					{
//						$result = array('return'=>2,'comment'=>"请重新输入密码");	
//					}
//				}
//				else
//				{
//					$result = array('return'=>0,'comment'=>"时间有误");	
//				}
//			}
//			else
//			{
//				$result = array('return'=>2,'comment'=>"请输入用户名");	
//			}
//		}
//		else
//		{
//			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
//		}
//		echo ($result['comment']);
//	}
//	public function checkUserExistAction()
//	{
//		//基础元素，必须参与验证
//		$User['UserName'] = $this->request->UserName;
//		$User['Time'] = abs(intval($this->request->Time));
//		//URL验证码
//		$sign = $this->request->sign;
//		//私钥，以后要移开到数据库存储
//		$p_sign = 'lm';
//		$sign_to_check = Base_common::check_sign($User,$p_sign);
//		//验证URL是否来自可信的发信方
//		if($sign_to_check==$sign)
//		{
//			//验证用户名有效性
//			if($User['UserName'])
//			{
//				//验证时间戳，时差超过600秒即认为非法
//				if(abs($User['Time']-time())<=600)
//				{
//		 			//查询用户
//					$checkResult = $this->oUser->GetUserByName($User['UserName']);
//		 			//返回用户ID
//		 			if(count($checkResult))
//		 			{
//			 			$result = array('return'=>0,'comment'=>$User['UserName']."已经存在");
//		 			}
//		 			else 
//		 			{
//			 			$result = array('return'=>1,'comment'=>$User['UserName']."可以正常注册");
//		 			}
//				}
//				else
//				{
//					$result = array('return'=>0,'comment'=>"时间有误");	
//				}
//			}
//			else
//			{
//				$result = array('return'=>2,'comment'=>"请输入用户名");	
//			}
//		}
//		else
//		{
//			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
//		}
//		//echo ($result['comment']);
//		echo json_encode($result);
//	}
//	public function checkUserActiveAction()
//	{
//		//基础元素，必须参与验证
//		$User['UserId'] = $this->request->UserId;
//		$User['AppId'] = abs(intval($this->request->AppId));
//		$User['PartnerId'] = abs(intval($this->request->PartnerId));
//		$User['Time'] = abs(intval($this->request->Time));			
//		//URL验证码
//		$sign = $this->request->sign;
//		//私钥，以后要移开到数据库存储
//		$p_sign = 'lm';
//		$sign_to_check = Base_common::check_sign($User,$p_sign);
//		//验证URL是否来自可信的发信方
//		if($sign_to_check==$sign)
//		{
//			//验证用户名有效性
//			if($User['UserId'])
//			{
//				//验证时间戳，时差超过600秒即认为非法
//				if(abs($User['Time']-time())<=600)
//				{
//		 			//查询用户激活记录
//					$checkResult = $this->oUser->GetUserActiveById($User['UserId'],$User['AppId'],$User['PartnerId']);
//		 			//返回用户激活记录
//		 			if(count($checkResult))
//		 			{
//			 			$result = array('return'=>1,'comment'=>"已经激活");
//		 			}
//		 			else 
//		 			{
//			 			$result = array('return'=>2,'comment'=>"可以继续激活");
//		 			}
//				}
//				else
//				{
//					$result = array('return'=>0,'comment'=>"时间有误");	
//				}
//			}
//			else
//			{
//				$result = array('return'=>2,'comment'=>"请输入用户Id");	
//			}
//		}
//		else
//		{
//			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
//		}
//		echo ($result['comment']);
//	}

}
