<?php
/**
 * 通用订单控制层
 * $Id: Ka91Controller.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Callback_Ka91Controller extends AbstractController
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
		$this->oPassage = new Config_Passage();
		$this->oApp = new Config_App();
		$this->oServer = new Config_Server();
		$this->oPartnerApp = new Config_Partner_App();
	}


//payment.test.limaogame.com/?ctl=callback/ka91&orderid=201304221456321010012435&chargemoney=0.05&systemno=871312448438&channelid=1&status=1&ext1=lm&ext2=limaogame&validate=4f761a7e918dc9dc
	/**
	 *支付成功回调
	 */	
	public function indexAction()
	{
		$RequestArr = array('orderid'=>$this->request->orderid,
		'channelid'=>intval($this->request->channelid),
		'systemno'=>$this->request->systemno,
		'chargemoney'=>$this->request->chargemoney,
		'status'=>intval($this->request->status),
		'ext1'=>$this->request->ext1,
		'ext2'=>$this->request->ext2,
		'validate'=>$this->request->validate);
		$OrderId = $this->request->orderid;
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
					$Pay = $oPayPassage->endPay($PassageInfo,$OrderInfo,$RequestArr);
					if($Pay!=false)
					{
						$PayLog = $this->oPay->createPay($Pay);
						if($PayLog)
						{
						 	//回调成功
						 	echo "1";
						 	$this->oExchange->createExchangeQueueByOrder($OrderInfo);				 		
						}
						else
						{
						 	//回调失败
						 	echo "0";
						}
					}
					else 
					{
						 //参数错误
						 echo "2";				 						 	
					}
				}
			}
			else
			{
					//已经执行过，返回成功
					echo "1";
			}	
		}
		else
		{
				//订单号不存在
				echo "3";
		}
	}

	public function createKa91OrderAction()
	{
		//基础元素，必须参与验证
		$Order['UserName'] = $this->request->UserName;
		$Order['OrderTime'] = abs(intval($this->request->OrderTime));
		$Order['ServerId'] = abs(intval($this->request->ServerId))?abs(intval($this->request->ServerId)):101001001;
		$Order['SubPassageId'] = ($this->request->SubPassageId)?($this->request->SubPassageId):"";
		$Order['Coin'] = abs(intval($this->request->Coin))?abs(intval($this->request->Coin)):0;
		$Order['OrderIP'] = $this->request->OrderIP?$this->request->OrderIP:"127.0.0.1";
		$Order['PayIP'] = $this->request->PayIP?$this->request->PayIP:"127.0.0.1";
		$Order['PayTime'] = abs(intval($this->request->PayTime));
		$Order['StageOrder'] = $this->request->StageOrder;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = Base_common::check_sign($Order,$p_sign);
		//不参与验证的元素
  	$Order['OrderIP'] = Base_Common::ip2long($Order['OrderIP']);
  	$Order['PayIP'] = Base_Common::ip2long($Order['PayIP']);
		
		if(in_array($_SERVER["REMOTE_ADDR"],array('61.145.117.183','61.145.117.184','219.136.252.38','121.9.211.6')))
		{	
			//验证URL是否来自可信的发信方
			if($sign_to_check==$sign)
			{
				if($Order['ServerId'])
				{
					//验证用户名有效性
					if($Order['UserName'])
					{
							//验证时间戳，时差超过600秒即认为非法
							if(abs($Order['PayTime']-time())<=600)
							{
					 			//查询用户
								$UserInfo = $this->oUser->GetUserByName($Order['UserName']);
								if($UserInfo['UserId'])
								{
									$Order['PayUserId'] = $UserInfo['UserId'];
									$Order['AcceptUserId'] = $UserInfo['UserId'];
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
														$PassageInfo = $this->oPassage->getByPassage("Ka91");
														if($PassageInfo['passage_id'])
														{
															$checkStageOrder = $this->oPay->getKa91StageOrder($Order['StageOrder']);
															if($checkStageOrder['StageOrder'])
															{
																 	$result = array('return'=>1,'OrderId'=>$checkStageOrder['OrderId'],'comment'=>"已经执行过，无需重复执行");							 											
															}
															else
															{
																$Order['PassageId'] = $PassageInfo['passage_id'];
																$Order['AppId'] = $ServerInfo['AppId'];
																$Order['PartnerId'] = $ServerInfo['PartnerId'];
																$Order['Amount'] = $PassageInfo['finance_rate']*$Order['Coin'];
																$Order['Credit'] = $PassageInfo['finance_rate']*$Order['Coin'];
																$Order['ExchangeRate'] = $AppInfo['exchange_rate'];
																$Order['AppCoin'] = $AppInfo['exchange_rate']*$Order['Coin'];
																$Order['OrderStatus'] = 1;
																$Order['UserSourceId'] = $UserInfo['UserSourceId'];
																$Order['UserSourceDetail'] = $UserInfo['UserSourceDetail'];
																$Order['UserSourceProjectId'] = $UserInfo['UserSourceProjectId'];
																$Order['UserSourceActionId'] = $UserInfo['UserSourceActionId'];
																$Order['UserRegTime'] = $UserInfo['UserRegTime'];
																
																$Pay['PayUserId'] = $Order['PayUserId'];
																$Pay['AcceptUserId'] = $Order['AcceptUserId'];
																$Pay['PassageId'] = $Order['PassageId'];
																$Pay['SubPassageId'] = $Order['SubPassageId'];
																$Pay['PayIP'] = $Order['PayIP'];
																$Pay['AppId'] = $Order['AppId'];
																$Pay['PartnerId'] = $Order['PartnerId'];
																$Pay['PayTime'] = $Order['PayTime'];
																$Pay['PayedTime'] = $Order['PayTime'];
																$Pay['Coin'] = $Order['Coin'];
																$Pay['Amount'] = $Order['Amount'];
																$Pay['Credit'] = $Order['Credit'];
																$Pay['StageOrder'] = $Order['StageOrder'];
																$Pay['UserSourceId'] = $Order['UserSourceId'];
																$Pay['UserSourceDetail'] = $Order['UserSourceDetail'];
																$Pay['UserSourceProjectId'] = $Order['UserSourceProjectId'];
																$Pay['UserSourceActionId'] = $Order['UserSourceActionId'];
																$Pay['UserRegTime'] = $Order['UserRegTime'];
																
																unset($Order['StageOrder'],$Order['UserName']);														
																$Ka91Pay = $this->oPay->createKa91Pay($Order,$Pay);
																if(intval($Ka91Pay))
																{
																 	$result = array('return'=>1,'OrderId'=>$Ka91Pay,'comment'=>"充值成功");
																 	$this->oExchange->createExchangeQueueByOrder(array('OrderId'=>$Ka91Pay));						 											
																}
																else
																{
																 	$result = array('return'=>2,'comment'=>"充值失败");							 										 	
																}
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
						 			$result = array('return'=>2,'comment'=>"用户不存在");						 	
								}
							}
							else
							{
								$result = array('return'=>0,'comment'=>"时间有误");	
							}
					}				
					else
					{
						$result = array('return'=>2,'comment'=>"请输入接收方用户账号");	
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
		}
		else
		{
				$result = array('return'=>0,'comment'=>"您的IP不在可允许的列表之内");			 	
		}		
		echo json_encode($result);	
	}
	public function checkKa91OrderAction()
	{
		//基础元素，必须参与验证
		$Order['Time'] = abs(intval($this->request->Time));
		$Order['StageOrder'] = $this->request->StageOrder;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = Base_common::check_sign($Order,$p_sign);
		//不参与验证的元素		
		if(in_array($_SERVER["REMOTE_ADDR"],array('61.145.117.183','61.145.117.184','219.136.252.38','121.9.211.6','58.247.169.182')))
		{		
			//验证URL是否来自可信的发信方
			if($sign_to_check==$sign)
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($Order['Time']-time())<=600)
				{				
						$checkStageOrder = $this->oPay->getKa91StageOrder($Order['StageOrder']);
						if($checkStageOrder['StageOrder'])
						{
							 	$result = array('return'=>1,'OrderId'=>$checkStageOrder['OrderId'],'comment'=>"订单执行成功");							 											
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
				$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
			}
		}
		else
		{
				$result = array('return'=>0,'comment'=>"您的IP不在可允许的列表之内");			 	
		}
		echo json_encode($result);	
	}
}
