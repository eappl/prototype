<?php
/**
 * 通用订单控制层
 * $Id: OrderController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class OrderController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oOrder;
	protected $oApp;
	protected $oPartnerApp;
	protected $oServer;
	protected $oPassage;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
		$this->oOrder = new Lm_Order();
		$this->oApp = new Config_App();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oServer = new Config_Server();
		$this->oPassage = new Config_Passage();
	}

	/**
	 *生成订单
	 */
	public function createOrderAction()
	{
		//基础元素，必须参与验证
		$Order['PayUserId'] = abs(intval($this->request->PayUserId));
		$Order['AcceptUserId'] = abs(intval($this->request->AcceptUserId));
		$Order['OrderTime'] = abs(intval($this->request->OrderTime));
		$Order['ServerId'] = abs(intval($this->request->ServerId))?abs(intval($this->request->ServerId)):101001001;
		$Order['PassageId'] = abs(intval($this->request->PassageId))?abs(intval($this->request->PassageId)):0;
		$Order['SubPassageId'] = ($this->request->SubPassageId)?($this->request->SubPassageId):"";
		$Order['Coin'] = abs(intval($this->request->Coin))?abs(intval($this->request->Coin)):0;
		$Order['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;	
		$Order['OrderIP'] = $this->request->OrderIP?$this->request->OrderIP:"127.0.0.1";
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		//print_R($Order);
		$sign_to_check = Base_common::check_sign($Order,$p_sign);
		//不参与验证的元素
  	$Order['OrderIP'] = Base_Common::ip2long($Order['OrderIP']);

		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			if($Order['ServerId'])
			{
				//验证用户名有效性
				if($Order['AcceptUserId'])
				{
					if($Order['PassageId'])
					{
						//验证时间戳，时差超过600秒即认为非法
						if(abs($Order['OrderTime']-time())<=600)
						{
				 			//查询用户
							$AcceptUserInfo = $this->oUser->GetUserById($Order['AcceptUserId']);
							if($AcceptUserInfo['UserId'])
							{
								//如果指定支付方
								if($Order['PayUserId'])
								{
									//查询用户
									$PayUserInfo = $this->oUser->GetUserById($Order['PayUserId']);
								}
								//如果不指定支付方或者支付方确定存在
								if(($PayUserInfo['UserId'])||($Order['PayUserId']==0))
								{
									//获取服务器信息
									$ServerInfo = $this->oServer->getRow($Order['ServerId']);
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
													//获取支付方式信息
													$PassageInfo = $this->oPassage->getRow($Order['PassageId']);
													if($PassageInfo['passage_id'])
													{
														$Order['AppId'] = $ServerInfo['AppId'];
														$Order['PartnerId'] = $ServerInfo['PartnerId'];
														$Order['Amount'] = $PassageInfo['finance_rate']*$Order['Coin'];
														$Order['Credit'] = $PassageInfo['finance_rate']*$Order['Coin'];
														$Order['ExchangeRate'] = $AppInfo['exchange_rate'];
														$Order['AppCoin'] = $AppInfo['exchange_rate']*$Order['Coin'];
														$Order['OrderStatus'] = 0;
														$Order['UserSourceId'] = $AcceptUserInfo['UserSourceId'];
														$Order['UserSourceDetail'] = $AcceptUserInfo['UserSourceDetail'];
														$Order['UserSourceProjectId'] = $AcceptUserInfo['UserSourceProjectId'];
														$Order['UserSourceActionId'] = $AcceptUserInfo['UserSourceActionId'];
														$Order['UserRegTime'] = $AcceptUserInfo['UserRegTime'];

														unset($Order['ReturnType']);
														$createOrder = $this->oOrder->createOrder($Order);
														if(intval($createOrder))
														{
														 	$result = array('return'=>1,'OrderId'=>$createOrder,'comment'=>"下单成功");							 											
														}
														else
														{
														 	$result = array('return'=>2,'comment'=>"下单失败");							 										 	
														}
													}
													else
													{
													 	$result = array('return'=>0,'comment'=>"您所选择的支付方式不存在");							 	
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
								 	$result = array('return'=>0,'comment'=>"支付方用户不存在");
								}
							}
							else
							{
					 			$result = array('return'=>2,'comment'=>"接收方用户不存在");						 	
							}
						}
						else
						{
							$result = array('return'=>0,'comment'=>"时间有误");	
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"请选择支付方式");					
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
		$Order['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($Order['ReturnType']==1)
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
	public function getOrderAction()
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
							
							if($OrderInfo['PayUserId'])
							{
								$PayUserInfo = $this->oUser->GetUserById($OrderInfo['PayUserId']);
								
								$OrderInfo['PayUserName'] = $PayUserInfo['UserName'];
							}
							$AcceptUserInfo = $this->oUser->GetUserById($OrderInfo['AcceptUserId']);							
							$OrderInfo['AcceptUserName'] = $AcceptUserInfo['UserName'];
				 			$result = array('return'=>1,'OrderInfo'=>$OrderInfo,'comment'=>"");				 	
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
	/**
	 *获取用户订单信息
	 */
	public function getUserOrderListAction()
	{
		//基础元素，必须参与验证
		$Order['UserId'] = ($this->request->UserId);
		$Order['AppId'] = abs(intval($this->request->AppId));
		$Order['PartnerId'] = abs(intval($this->request->ParnterId));
		$Order['ServerId'] = abs(intval($this->request->ServerId));
		$Order['StartDate'] = ($this->request->StartDate)?($this->request->StartDate):date("Y-m-01",time());
		$Order['EndDate'] = ($this->request->EndDate)?($this->request->EndDate):date("Y-m-d",time());
		$Order['PageSize'] = abs(intval($this->request->PageSize));
		$Order['Page'] = abs(intval($this->request->Page));
		$Order['Time'] = abs(intval($this->request->Time));
		$Order['OrderStatus'] = (intval($this->request->OrderStatus));
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
				if($Order['UserId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($Order['Time']-time())<=600)
					{
						$UserInfo = $this->oUser->GetUserById($Order['UserId']);
						if($UserInfo['UserId'])
						{
			 				
			 				$OrderListInfo = $this->oOrder->getUserOrderList($Order['UserId'],$Order['AppId'],$Order['PartnerId'],$Order['ServerId'],$Order['StartDate'],$Order['EndDate'],$Order['PageSize'],($Order['Page']-1)*$Order['PageSize'],$Order['OrderStatus']);
			 			 	$result = array('return'=>1,'OrderList'=>$OrderListInfo);
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

}
