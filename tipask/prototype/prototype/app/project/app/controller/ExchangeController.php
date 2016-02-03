<?php
/**
 * 通用订单控制层
 * $Id: ExchangeController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class ExchangeController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oExchange;
	protected $oOrder;
	protected $oApp;
	protected $oPartnerApp;
	protected $oServerApp;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
		$this->oExchange = new Lm_Exchange();
		$this->oOrder = new Lm_Order();
		$this->oApp = new Config_App();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oServer = new Config_Server();
	}

	/**
	 *获取用户订单信息
	 */
	public function getExchangeAction()
	{
		//基础元素，必须参与验证
		$Exchange['ExchangeId'] = ($this->request->ExchangeId);
		$Exchange['Time'] = abs(intval($this->request->Time));
		$Exchange['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;	

		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Exchange,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
				if($Exchange['ExchangeId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($Exchange['Time']-time())<=600)
					{
			 			$ExchangeInfo = $this->oExchange->getExchangeInfo($Exchange['ExchangeId']);
			 			//查询订单
						if($ExchangeInfo['ExchangeId'])
						{
				 			$result = array('return'=>1,'ExchangeInfo'=>$ExchangeInfo,'comment'=>"");				 	
						}
						else
						{
				 			$result = array('return'=>2,'comment'=>"无此订单");				 	
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入兑换订单号");					
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
//			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);
//			if($result['return']==1)
//			{
//				$r = $r."|".$result['LoginId']."|".$result['adult'];
//			}
//			echo $r;
		}
	}
	/**
	 *生成兑换订单
	 */
	public function createExchangeAction()
	{
		//基础元素，必须参与验证
		$Exchange['UserId'] = abs(intval($this->request->UserId));
		$Exchange['ExchangeTime'] = abs(intval($this->request->ExchangeTime));
		$Exchange['ServerId'] = abs(intval($this->request->ServerId))?abs(intval($this->request->ServerId)):101001001;
		$Exchange['Coin'] = abs(intval($this->request->Coin))?abs(intval($this->request->Coin)):0;
		$Exchange['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;	
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		//print_R($Exchange);
		$sign_to_check = Base_common::check_sign($Exchange,$p_sign);
		//不参与验证的元素

		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			if($Exchange['ServerId'])
			{
				//验证用户名有效性
				if($Exchange['UserId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($Exchange['ExchangeTime']-time())<=600)
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserById($Exchange['UserId']);
						if($UserInfo['UserId'])
						{
							if($UserInfo['UserCoin']>=$Exchange['Coin'])
							{
								//获取服务器信息
								$ServerInfo = $this->oServer->getRow($Exchange['ServerId']);
								if($ServerInfo['ServerId'])
								{
									if($ServerInfo['AppId']&&$ServerInfo['PartnerId'])
									{
										$bind = array($ServerInfo['PartnerId'],$ServerInfo['AppId']);
										//验证游戏－平台信息
										$PartnerInfo = $this->oPartnerApp->getRow($bind);
										if($PartnerInfo['AppId']&&$PartnerInfo['PartnerId'])
										{
											$AppInfo = $this->oApp->getRow($PartnerInfo['AppId']);
											if($AppInfo['AppId'])
											{
												$createExchange = $this->oExchange->createExchangeQueueByUser($UserInfo['UserId'],$ServerInfo['ServerId'],$Exchange['Coin']);
												if(intval($createExchange))
												{
												 	$result = array('return'=>1,'ExchangeId'=>$createExchange,'comment'=>"下单成功");							 											
												}
												else
												{
												 	$result = array('return'=>2,'comment'=>"下单失败");							 										 	
												}
											}
											else
											{
												 	$result = array('return'=>0,'comment'=>"您所选择的游戏不存在");							 							 	
											}
										}
										else
										{
										 	$result = array('return'=>0,'comment'=>"您所选择的游戏－平台不存在");						 							 	
										}
									}
									else
									{
									 	$result = array('return'=>0,'comment'=>"您所选择的服务器配置不完整");						 							 										 											 	
									}
								}
								else
								{
									 	$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");						 							 										 	
								}
							}
							else
							{
							 	$result = array('return'=>0,'comment'=>"余额不足");
							}
						}
						else
						{
						 	$result = array('return'=>0,'comment'=>"用户不存在");
						}
						
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}				
				else
				{
					$result = array('return'=>2,'comment'=>"请输入接收方用户ID");	
				}
			}
			else
			{
 				$result = array('return'=>0,'comment'=>"请输入服务器");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		$Exchange['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($Exchange['ReturnType']==1)
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
	 *获取用户订单信息
	 */
	public function getUserExchangeListAction()
	{
		//基础元素，必须参与验证
		$Exchange['UserId'] = ($this->request->UserId);
		$Exchange['AppId'] = abs(intval($this->request->AppId));
		$Exchange['PartnerId'] = abs(intval($this->request->PartnerId));
		$Exchange['ServerId'] = abs(intval($this->request->ServerId));
		$Exchange['StartDate'] = ($this->request->StartDate)?($this->request->StartDate):date("Y-m-01",time());
		$Exchange['EndDate'] = ($this->request->EndDate)?($this->request->EndDate):date("Y-m-d",time());
		$Exchange['PageSize'] = abs(intval($this->request->PageSize));
		$Exchange['Page'] = abs(intval($this->request->Page));
		$Exchange['Time'] = abs(intval($this->request->Time));
		$Exchange['ExchangeStatus'] = (intval($this->request->ExchangeStatus));
		$Exchange['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;	

		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Exchange,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			if($Exchange['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($Exchange['Time']-time())<=600)
				{
					$UserInfo = $this->oUser->GetUserById($Exchange['UserId']);
					if($UserInfo['UserId'])
					{			 				
		 				$ExchangeListInfo = $this->oExchange->getUserExchangeList($Exchange['UserId'],$Exchange['AppId'],$Exchange['PartnerId'],$Exchange['ServerId'],$Exchange['StartDate'],$Exchange['EndDate'],$Exchange['PageSize'],($Exchange['Page']-1)*$Exchange['PageSize'],$Exchange['ExchangeStatus']);
		 			 	$result = array('return'=>1,'ExchangeList'=>$ExchangeListInfo);
		 			}
		 			else
		 			{
		 			 	$result = array('return'=>2,'comment'=>"无此用户");
		 			}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用户");					
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

		}
	}
	/**
	 *根据用户订单号创建兑换订单
	 */
	public function createExchangeByOrderAction()
	{
		//基础元素，必须参与验证
		$Order['OrderId'] = ($this->request->OrderId);
		$Order['Time'] = abs(intval($this->request->Time));
		$Order['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;			
		//URL验证码

		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';		
		$sign_to_check = Base_common::check_sign($Order,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
				if($Order['OrderId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($Order['Time']-time())<=600)
					{
			 			$OrderInfo = $this->oOrder->getRow($Order['OrderId']);
			 			//查询订单
						if($OrderInfo['OrderId'])
						{
							$create = $this->oExchange->createExchangeQueueByOrder($OrderInfo);
							if($create)
							{
				 				$result = array('return'=>1,'ExchangeId'=>$create,'comment'=>"创建兑换成功");
				 			}
				 			else
				 			{
				 				$result = array('return'=>2,'comment'=>"创建兑换失败");
				 			}				 	
						}
						else
						{
				 			$result = array('return'=>2,'comment'=>"无此订单");				 	
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入订单号");					
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
//			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);
//			if($result['return']==1)
//			{
//				$r = $r."|".$result['LoginId']."|".$result['adult'];
//			}
//			echo $r;
		}
	}

}
