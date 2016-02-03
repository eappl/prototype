<?php
/**
 * 通用订单控制层
 * $Id: PayController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class PayController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oOrder;
	protected $oPay;
	protected $oExchange;
	protected $oApp;
	protected $oPartner;
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
		$this->oPay = new Lm_Pay();
		$this->oExchange = new Lm_Exchange();
		$this->oApp = new Config_App();
		$this->oPartner = new Config_Partner();
		$this->oPartnerApp = new Config_Partner_App();
		$this->oServer = new Config_Server();
		$this->oPassage = new Config_Passage();
	}

	 /*生成订单
	 */
	public function createPayAction()
	{
		//基础元素，必须参与验证
		$Pay['OrderId'] = $this->request->OrderId;
		$Pay['PassageId'] = abs(intval($this->request->PassageId));
		$Pay['SubPassageId'] = ($this->request->SubPassageId)?($this->request->SubPassageId):"";	
		$Pay['PayTime'] = abs(intval($this->request->PayTime))?abs(intval($this->request->PayTime)):time();
		$Pay['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$Pay['PayIP'] = $this->request->PayIP?$this->request->PayIP:"127.0.0.1";

		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = Base_common::check_sign($Pay,$p_sign);
		//不参与验证的元素
  		$Pay['PayIP'] = Base_Common::ip2long($Pay['PayIP']);
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{			
			//检查订单号
			if($Pay['OrderId'])
			{
				if(abs($Pay['PayTime']-time())<=600)
				{
		 			//查询订单
		 			$OrderInfo = $this->oOrder->getRow($Pay['OrderId']);
					//如果订单未作废
					if($OrderInfo['OrderId'])
					{
						if(($OrderInfo['OrderStatus']==0))
						{
							//如果指定支付方
							if($OrderInfo['PayUserId'])
							{
								//查询用户
								$PayUserInfo = $this->oUser->GetUserById($OrderInfo['PayUserId']);
							}
							//如果不指定支付方或者支付方确定存在
							if(($PayUserInfo['UserId'])||($Order['PayUserId']==0))
							{
								//检查收款方用户
								$AcceptUserInfo = $this->oUser->GetUserById($OrderInfo['AcceptUserId']);
								if($AcceptUserInfo['UserId'])
								{
									if($Pay['PassageId']!=$OrderInfo['PassageId'])
									{
										$PassageInfo = $this->oPassage->getRow($Pay['PassageId']);
									}
									else
									{
										$PassageInfo = $this->oPassage->getRow($OrderInfo['PassageId']);
									}
									//获取支付方式信息
									if($PassageInfo['passage_id'])
									{
										//检查服务器配置
										$ServerInfo = $this->oServer->getRow($OrderInfo['ServerId']);								
										if($ServerInfo['ServerId'])
										{
											if($ServerInfo['PartnerId']&&$ServerInfo['AppId'])
											{
												$bind = array($ServerInfo['PartnerId'],$ServerInfo['AppId']);
												//验证游戏－运营商信息
												$PartnerAppInfo = $this->oPartnerApp->getRow($bind);
												if($PartnerAppInfo['AppId']&&$PartnerAppInfo['PartnerId'])
												{
													//验证游戏信息
													$AppInfo = $this->oApp->getRow($ServerInfo['AppId']);
													if($AppInfo['AppId'])
													{
														//检测运营商信息
														$PartnerInfo = $this->oPartner->getRow($ServerInfo['PartnerId']);
														if($PartnerInfo['PartnerId'])
														{
															//如果关联支付订单存在
															if($OrderInfo['PayId'])
															{
											 		 			$result = array('return'=>0,'comment'=>"该订单已经支付完毕");
								 		 					}
										 		 			else
										 		 			{
																$OrderUpdateArr = array('PayIp'=>$Pay['PayIP'],'PassageId'=>$Pay['PassageId'],'PayTime'=>$Pay['PayTime']);
																$OrderUpdate = $this->oOrder->updateOrder($Pay['OrderId'],$OrderInfo['AcceptUserId'],$OrderUpdateArr);
								 		 						$PassageClassName = "Lm_Pay_Passage_".$PassageInfo['passage'];
																$oPayPassage = new $PassageClassName;
																$PayUrl = $oPayPassage->createPay($AppInfo,$PartnerInfo,$ServerInfo,$PassageInfo,$OrderInfo,$Pay);	
										 		 				$result = array('return'=>1,'PayUrl'=>$PayUrl,'StageUrl'=>($PassageInfo['StageUrl']),'comment'=>"该订单可以继续支付");		 		 					 	
										 		 			}																	
														}
														else
														{
							 		 				 		$result = array('return'=>0,'comment'=>"所选择的游戏不存在");							 					 									 									 															 																 	
														}
														
													}
													else
													{
						 		 				 		$result = array('return'=>0,'comment'=>"所选择的游戏不存在");							 					 									 									 															 	
													}
													
												}	
												else
												{
						 		 				 	$result = array('return'=>0,'comment'=>"所选择的游戏－运营商不存在");							 					 									 									 	
												}	
											}
											else
											{
							 		 			$result = array('return'=>0,'comment'=>"所选择的服务器配置不完整");							 					 									 									 												 													 	
											}
										}
										else
										{
						 		 			$result = array('return'=>0,'comment'=>"所选择的服务器不存在");							 					 									 									 												 	
										}																				
									}
									else
									{
			 		 				 	$result = array('return'=>0,'comment'=>"支付方式不存在");							 					 									 	
									}																			
								}
								else
								{
		 		 				 	$result = array('return'=>0,'comment'=>"收款方用户不存在");							 					 	
								 	
								}								
							}
							else
							{
	 		 				 	$result = array('return'=>0,'comment'=>"支付方用户不存在");							 					 	
							}								
						}
						else
						{
		 				 	if($OrderInfo['OrderStatus']==-1)
		 				 	{
		 				 		$result = array('return'=>0,'comment'=>"该订单已经作废");
		 					}
		 					elseif($OrderInfo['OrderStatus']>1)
		 					{
		 				 		$result = array('return'=>0,'comment'=>"该订单已经支付完毕");	 						
		 					} 											 					 	
						}			
					}
					else
					{
	 				 	$result = array('return'=>0,'comment'=>"该订单不存在");							 	
					}
				}
				else
				{
 				 	$result = array('return'=>0,'comment'=>"时间错误");				 	
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
		$Pay['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($Pay['ReturnType']==1)
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
		}
	}
	/**
	 *支付成功回调
	 */	
	public function payedAction()
	{
		$OrderId = $this->request->out_trade_no;
		$OrderInfo = $this->oOrder->getRow($OrderId);
		if($OrderInfo['OrderId'])
		{
			if($OrderInfo['OrderStatus']<1)
			{
				if($OrderInfo['PassageId'])
				{
					$PassageInfo = $this->oPassage->getRow($OrderInfo['PassageId']);
		 			$PassageClassName = "Lm_Pay_Passage_".$PassageInfo['passage'];
					$oPayPassage = new $PassageClassName;
					$Pay = $oPayPassage->endPay($PassageInfo,$OrderInfo);
					if($Pay!=false)
					{
						$PayLog = $this->oPay->createPay($Pay);
						if($PayLog)
						{
						 	$result = array('return'=>1,'OrderId'=>$OrderId,'comment'=>"支付完毕");
						 	$this->oExchange->createExchangeQueueByOrder($OrderInfo);				 		
						}
						else
						{
						 	$result = array('return'=>0,'comment'=>"支付失败");				 		
						}
					}
					else 
					{
						 $result = array('return'=>0,'comment'=>"订单信息不符");				 						 	
					}
				}
			}
			else
			{
				$result = array('return'=>1,'comment'=>"已经支付完毕，不需重复请求");				 					 	
			}	
		}
		echo json_encode($result);

	}
	/**
	 *支付成功回调查询
	 */	
	public function payStatusCheckAction()
	{
		$OrderId = $this->request->OrderId;
		$OrderInfo = $this->oOrder->getRow($OrderId);
		if($OrderInfo['OrderId'])
		{
			if($OrderInfo['OrderStatus']>=0)
			{
				if($OrderInfo['PassageId'])
				{
					$PassageInfo = $this->oPassage->getRow($OrderInfo['PassageId']);
		 			$PassageClassName = "Lm_Pay_Passage_".$PassageInfo['passage'];
					$oPayPassage = new $PassageClassName;
					$Pay = $oPayPassage->checkPay($PassageInfo,$OrderInfo);
					if($Pay!=false)
					{
						$PayLog = $this->oPay->createPay($Pay);
						if($PayLog)
						{
						 	$result = array('return'=>1,'OrderId'=>$OrderId,'comment'=>"支付完毕");				 		
						}
						else
						{
						 	$result = array('return'=>0,'comment'=>"支付失败");				 		
						}
					}
					else 
					{
						 	$result = array('return'=>0,'comment'=>"订单信息不符");				 						 	
					}
				}
			}
			else
			{
				$result = array('return'=>1,'comment'=>"已经支付完毕，不需重复请求");				 					 	
			}	
		}
		echo json_encode($result);

	}
}
