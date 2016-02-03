<?php
/**
 * 通用登录控制层
 * $Id: LotoController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class LotoController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oLoto;
	protected $oPrize;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
		$this->oLoto = new Loto_Loto();
		$this->oPrize = new Loto_Prize();
	}

	/**
	 *用户ID方式参与抽奖
	 */
	public function lotoAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['LotoTime'] = abs(intval($this->request->LotoTime));
		$User['LotoId'] = abs(intval($this->request->LotoId));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'loto';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		
        
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				if($User['LotoId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['LotoTime']-time())<=600)
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserById($User['UserId']);
						if($UserInfo['UserId'])
						{
							$LotoInfo = $this->oLoto->getRow($User['LotoId']);					
							if($LotoInfo['LotoId'])
							{
								if(($User['LotoTime']>=$LotoInfo['StartTime'])&&($User['LotoTime']<=$LotoInfo['EndTime']))
								{
									$UserLotoLog = $this->oPrize->getUserLotoLog($User['UserId'],$User['LotoId']);
									if(count($UserLotoLog)<$LotoInfo['UserLotoLimit'])
									{
										$currentRate = $this->oPrize->getCurrentRate($LotoInfo['LotoId'],$User['LotoTime']);
										$PrizeList = $this->oPrize->getAll($LotoInfo['LotoId']);
										
										if(is_array($currentRate['PrizeDetailList']))
										{
											ksort($currentRate['PrizeDetailList']);
											$Start = 1;
											foreach($currentRate['PrizeDetailList'] as $PrizeDetailId => $PrizeData)
											{
												if(isset($PrizeList[$LotoInfo['LotoId']][$PrizeData['LotoPrizeId']]))
												{													
													if($PrizeData['LotoPrizeCountUsed']<$PrizeData['LotoPrizeCount'])
													{
														$j = $Start;

														for($i = $j;$i<$currentRate['PrizeDetailList'][$PrizeDetailId]['PrizeRate']+$j;$i++)
														{
															if($Start>10000)
															{
																break;
															}
															$Rate[$Start]['LotoPrizeId'] = $PrizeData['LotoPrizeId'];
															$Rate[$Start]['LotoPrizeDetailId'] = $PrizeDetailId;
															$Start++;	
														}	
													}
												}
											}
											$Loto = mt_rand(1,10000);

											$Prize = ($Rate[$Loto]);
											$LogArr = array('LotoId'=>$LotoInfo['LotoId'],'UserId'=>$User['UserId'],'LotoTime'=>$User['LotoTime'],'LotoPrizeId'=>intval($Prize['LotoPrizeId']),'LotoPrizeDetailId'=>intval($Prize['LotoPrizeDetailId']));
											$log = $this->oPrize->insertLotoLog($LogArr);
											if($log)
											{
												$result = array('return'=>1,'Prize'=>$Prize,'LotoLogId'=>$log,'comment'=>isset($Prize['LotoPrizeId'])?"":"未中奖");
											}
											else
											{
												$result = array('return'=>2,'comment'=>"抽奖失败");
											}

										}
									}
									else
									{
										$result = array('return'=>2,'comment'=>"您的抽奖次数已经达到上限");
									}
								}
								else
								{
									$result = array('return'=>2,'comment'=>"该次抽奖尚未开始或已经结束");
								}
							}
							else
							{
					 			$result = array('return'=>2,'comment'=>"无此抽奖批次");							 	
							}
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
					$result = array('return'=>2,'comment'=>"请选择抽奖批次");	
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
				$r = $r."|".$result['LotoId']."|".$result['adult'];
			}
			echo $r;
		}
	}
	/**
	 *用户ID方式参与抽奖
	 */
	public function getLotoLogAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['LotoTime'] = abs(intval($this->request->LotoTime));
		$User['LotoId'] = abs(intval($this->request->LotoId));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'loto';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				if($User['LotoId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['LotoTime']-time())<=600)
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserById($User['UserId']);
						if($UserInfo['UserId'])
						{
							$LotoInfo = $this->oLoto->getRow($User['LotoId']);					
							if($LotoInfo['LotoId'])
							{
								if(($User['LotoTime']>=$LotoInfo['StartTime'])&&($User['LotoTime']<=$LotoInfo['EndTime']))
								{
									$UserLotoLog = $this->oPrize->getUserLotoLog($User['UserId'],$User['LotoId']);
									if(count($UserLotoLog)<$LotoInfo['UserLotoLimit'])
									{										
										$result = array('return'=>1);										
									}
									else
									{
										$result = array('return'=>2,'comment'=>"您的抽奖次数已经达到上限");
									}
								}
								else
								{
									$result = array('return'=>2,'comment'=>"该次抽奖尚未开始或已经结束");
								}
							}
							else
							{
					 			$result = array('return'=>2,'comment'=>"无此抽奖批次");							 	
							}
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
					$result = array('return'=>2,'comment'=>"请选择抽奖批次");	
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
				$r = $r."|".$result['LotoId']."|".$result['adult'];
			}
			echo $r;
		}
	}
	/**
	 *用户ID方式参与抽奖
	 */
	public function getLotoInfoAction()
	{
		//基础元素，必须参与验证
		$User['Time'] = abs(intval($this->request->Time));
		$User['LotoId'] = abs(intval($this->request->LotoId));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'loto';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
				if($User['LotoId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['Time']-time())<=600)
					{
						$LotoInfo = $this->oLoto->getRow($User['LotoId']);					
						if($LotoInfo['LotoId'])
						{
							$PrizeList = $this->oPrize->getAll($LotoInfo['LotoId']);	
							$result = array('return'=>1,'PrizeList'=>$PrizeList,'LotoInfo'=>$LotoInfo);
						}
						else
						{
				 			$result = array('return'=>2,'comment'=>"无此抽奖批次");							 	
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"请选择抽奖批次");	
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
				$r = $r."|".$result['LotoId']."|".$result['adult'];
			}
			echo $r;
		}
	}
}
