<?php
/**
 * 通用登录控制层
 * $Id: ActionController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class ActionController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oPrize;
	protected $oLoto;
    protected $oActive;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
		$this->oPrize = new Loto_Prize();
		$this->oLoto = new Loto_Loto();
        $this->oActive = new Lm_Active();
	}

	/**
	 *用户ID方式登录
	 */
	public function getPrizeAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['GetPrizeTime'] = abs(intval($this->request->GetPrizeTime));
		$User['LotoLogId'] = abs(intval($this->request->LotoLogId));
		$User['LotoId'] = abs(intval($this->request->LotoId));
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
				if($User['LotoId'])
				{
					if($User['LotoLogId'])
					{
						//验证时间戳，时差超过600秒即认为非法
						if(abs($User['GetPrizeTime']-time())<=600)
						{
				 			//查询用户
							$UserInfo = $this->oUser->GetUserById($User['UserId']);
							if($UserInfo['UserId'])
							{
								$LotoInfo = $this->oLoto->getRow($User['LotoId']);					
								if($LotoInfo['LotoId'])
								{
									$UserLotoLog = $this->oPrize->getUserLotoLog($User['UserId'],$User['LotoId']);
									if(isset($UserLotoLog[$User['LotoLogId']]))
									{
										if(!$UserLotoLog[$User['LotoLogId']]['PrizeGetTime'])
										{
											if($User['LotoId']==6)
											{
												$result = $this->actionOne($UserLotoLog[$User['LotoLogId']]);
											}
										}
										else
										{
 							 				$result = array('return'=>0,'comment'=>"您已领过奖品，无法重复领奖");							 										 	
										}
									}
									else
									{
						 				$result = array('return'=>0,'comment'=>"抽奖记录ID错误");							 										 	
									}								
								}
								else
								{
						 			$result = array('return'=>0,'comment'=>"无此抽奖批次");							 	
								}
							}
							else
							{
					 			$result = array('return'=>0,'comment'=>"无此用户");
							 	
							}
						}
						else
						{
							$result = array('return'=>0,'comment'=>"时间有误");	
						}
					}
					else
					{
						$result = array('return'=>2,'comment'=>"请输入抽奖记录ID");	
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
				$r = $r."|".$result['LoginId']."|".$result['adult'];
			}
			echo $r;
		}
	}
	/**
	 *用户名方式登录
	 */
	public function actionOne($UserLotoLog)
	{
		if($UserLotoLog['LotoPrizeId']==5)
		{
			$AppId = 101;
			$PartnerId = 1;
			//查询用户激活记录
			$checkResult = $this->oUser->GetUserActive($UserLotoLog['UserId'],$AppId,$PartnerId);
			//返回用户激活记录
			if(count($checkResult))
			{
				$result = array('return'=>2,'comment'=>"已经激活");
			}
			else 
			{
				$oActive = new Lm_Active();
				$ActiveCode = $oActive->getUnUsedActiveCode($AppId,$PartnerId,0,1);
				$time = time();
				$User = array('AppId'=>$AppId,'PartnerId'=>$PartnerId,'UserId'=>$UserLotoLog['UserId'],'ActiveTime'=>$time,'ActiveCode'=>$ActiveCode['0']['ActiveCode']);
				$activeResult = $this->oUser->InsertUserActive($User);
				//检查更新结果
				if(intval($activeResult)==1)
				{
					$this->oPrize->updateLotoLog(1,$UserLotoLog['LotoLogId'],array('PrizeGetTime'=>$time,'Comment'=>json_encode(array('PrizeType'=>'autoActive'))));
					$result = array('return'=>1,'comment'=>"自动激活成功");
				}
				else
				{
					$result = array('return'=>0,'comment'=>"激活失败");
				}
			}
			return $result;
		}

	}
	public function testMailAction()
	{
		$this->oUser->sendAuthMail(1000,"cxd032404@hotmail.com",time());	
	}
	/*
	*获取用户战斗力和实力等级的排名 
	*@author selena 2013/3/12
	*/
	public function getRankAction()
	{
		//基础元素，必须参与验证
		
		$Rank['Time'] = abs(intval($this->request->Time));
		$Rank['RankType'] = $this->request->RankType?$this->request->RankType:1;	
		$Rank['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$Rank['Page'] = $this->request->Page?$this->request->Page:1;
		$Rank['PageSize'] = $this->request->PageSize?$this->request->PageSize:10;
		$Rank['ServerId'] = $this->request->ServerId;

		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($Rank,$p_sign);
		//不参与验证的元素
		$resultArr = array();
		$resultTop3 = array();
		$beginIndex = ($Rank['Page']-1)*$Rank['PageSize']+1;
	 	$max = $Rank['PageSize']+$beginIndex;
	 
	 	$count = 0;
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Rank['Time']-time())<=600)
			{
	 			if($Rank['ServerId'])
	 			{
		 			$resultArr = array();
		 			$resultTop3 = array();
		 			if($Rank['RankType']==1)//1表示获取战斗力
		 			{	 				
		 				$FightRankList = (@include(__APP_ROOT_DIR__."/etc/FightRank.php"));
		 				if(isset($FightRankList[$Rank['ServerId']]))
		 				{
			 				for($i=1;$i<4;$i++)
			 				{
			 					$resultTop3[$i] = $FightRankList[$Rank['ServerId']][$i];	
			 				}
			 				$count = count($FightRankList[$Rank['ServerId']]);
			 				for($i=$beginIndex;$i<$max;$i++)
			 				{
			 					if($FightRankList[$Rank['ServerId']][$i])
			 					{
			 						$resultArr[$i] = $FightRankList[$Rank['ServerId']][$i];
			 					}	 				
			 				}		 						
		 				}	 				
		 			}
		 			else if($Rank['RankType']==2)//2表示获取实力
		 			{
		 				$CapacityRankList = (@include(__APP_ROOT_DIR__."/etc/CapacityRank.php"));
		 				if(isset($CapacityRankList[$Rank['ServerId']]))
		 				{
			 				for($i=1; $i<4;$i++)
			 				{
			 					$resultTop3[$i] = $CapacityRankList[$Rank['ServerId']][$i];	
			 				} 	
			 				$count = count($CapacityRankList[$Rank['ServerId']]);
			 				
			 				for($i=$beginIndex;$i<$max;$i++)
			 				{
			 					if($CapacityRankList[$Rank['ServerId']][$i])
			 					{
			 						$resultArr[$i] = $CapacityRankList[$Rank['ServerId']][$i];
			 						
			 					}
			 				}	 						
		 				}	 			 				
		 			}
		 			else if($Rank['RankType']==3)//3表示PVP战绩排名
		 			{
		 				$PvpRankList = (@include(__APP_ROOT_DIR__."/etc/PvpRank.php"));
		 				if(isset($PvpRankList[$Rank['ServerId']]))
		 				{
			 				for($i=1; $i<4;$i++)
			 				{
			 					$resultTop3[$i] = $PvpRankList[$Rank['ServerId']][$i];	
			 				} 	
			 				$count = count($PvpRankList[$Rank['ServerId']]);
			 				
			 				for($i=$beginIndex;$i<$max;$i++)
			 				{
			 					if($PvpRankList[$Rank['ServerId']][$i])
			 					{
			 						$resultArr[$i] = $PvpRankList[$Rank['ServerId']][$i];
			 						
			 					}
			 				}	 						
		 				}	 			 				
		 			}
		 			else if($Rank['RankType']==4)//4表示PK战绩排名
		 			{
		 				$PKPointRankList = (@include(__APP_ROOT_DIR__."/etc/PKPoint.php"));
		 				if(isset($PKPointRankList[$Rank['ServerId']]))
		 				{
			 				for($i=1; $i<4;$i++)
			 				{
			 					$resultTop3[$i] = $PKPointRankList[$Rank['ServerId']][$i];	
			 				} 	
			 				$count = count($PKPointRankList[$Rank['ServerId']]);
			 				
			 				for($i=$beginIndex;$i<$max;$i++)
			 				{
			 					if($PKPointRankList[$Rank['ServerId']][$i])
			 					{
			 						$resultArr[$i] = $PKPointRankList[$Rank['ServerId']][$i];
			 						
			 					}
			 				}	 						
		 				}	 			 				
		 			}
		 			$result = array('return'=>1,'resultArr'=>$resultArr,'count'=>$count,'resultTop3'=>$resultTop3);		 				
	 			}
	 			else
	 			{
					$result = array('return'=>0,'comment'=>"请指定服务器");		 			 	
	 			}

			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}

		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);
		
			
	}
    
    //激活执行
    public function activateExecuteAction()
    {        
        //初始化页面选项  
        $bind['Time'] = abs(intval($this->request->Time)); 
        $bind['UserName'] = $this->request->UserName?$this->request->UserName:"";
        $bind['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:1;
        
        //URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($bind,$p_sign);
        
        $UserName = $bind['UserName'];
        
        $AppId = $this->request->AppId?($this->request->AppId):101;
		$PartnerId = $this->request->PartnerId?($this->request->PartnerId):1;
        
        //验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($bind['Time']-time())<=600)
			{
                $UserArr = $this->oUser->GetUserByName($UserName);
                if(is_array($UserArr) && !empty($UserArr) && $UserArr['UserId'] != ""){
            		//查询用户激活记录
            		$checkResult = $this->oUser->GetUserActive($UserArr['UserId'],$AppId,$PartnerId);
            		//返回用户激活记录
            		if(count($checkResult))
            		{
            			$result = array('return'=>2,'comment'=>"已经激活");
            		}
            		else 
            		{			
            			$ActiveCode = $this->oActive->getUnUsedActiveCode($AppId,$PartnerId,0,1);
            			$time = time();
            			$User = array('AppId'=>$AppId,'PartnerId'=>$PartnerId,'UserId'=>$UserArr['UserId'],'ActiveTime'=>$time,'ActiveCode'=>$ActiveCode['0']['ActiveCode']);
            			
                        $activeResult = $this->oUser->InsertUserActive($User);
            			//检查更新结果
            			if(intval($activeResult)==1)
            			{
            				$result = array('return'=>1,'comment'=>"自动激活成功");
            			}
            			else
            			{
            				$result = array('return'=>0,'comment'=>"激活失败");
            			}
            		}
                    
                    $this->writeTxt('/www/web_logs/web_usercenter_log/weixin/',"weixinAuto1.log",$UserArr['UserId'].":".$UserName.":".iconv("utf-8","gbk",$result['comment'])."\n");    		
                }else{
                    $result = array('return'=>3,'comment'=>"用户名不存在");
                }
            }else{
                $result = array('return'=>0,'comment'=>"时间有误");	
            }
        }else{
            $result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
        }
        
        echo  json_encode($result);   
    }
    
    //写日志
    private function writeTxt($logpath,$filename,$content)
	{
		$filename = $logpath.$filename;
		$fp = fopen($filename,'a+');
		fwrite($fp,$content);
		fclose($fp);
	}
	public function getVideoListAction()
	{
		//基础元素，必须参与验证
		
		$Video['Time'] = abs(intval($this->request->Time));
		$Video['VideoTypeId'] = abs(intval($this->request->VideoTypeId))?abs(intval($this->request->VideoTypeId)):0;	
		$Video['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$Video['Page'] = $this->request->Page?$this->request->Page:1;
		$Video['PageSize'] = $this->request->PageSize?$this->request->PageSize:10;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($Video,$p_sign);
		//不参与验证的元素
	 
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Video['Time']-time())<=600)
			{
	 			$oVideo = new Config_Video_Video();
	 			$VideoList = $oVideo->getAll($Video['VideoTypeId']);

				$Start = ($Video['Page']-1)*$Video['PageSize'];
				$End = $Start + $Video['PageSize'];
				$i = 0;
				foreach($VideoList as $key => $value)
				{
					if(($i<$Start)||($i>=$End))
					{
						unset($VideoList[$key]);	
					}
					$i++;	
				}
				$result = array('return'=>1,'VideoList'=>$VideoList);		 			 		 			
			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);
		
			
	}
	public function getVideoTypeListAction()
	{
		//基础元素，必须参与验证
		
		$Video['Time'] = abs(intval($this->request->Time));
		$Video['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		
		$sign_to_check = base_common::check_sign($Video,$p_sign);
		//不参与验证的元素
	 
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Video['Time']-time())<=600)
			{
	 			$oVideoType = new Config_Video_Type();
	 			$VideoTypeList = $oVideoType->getAll();
				$result = array('return'=>1,'VideoTypeList'=>$VideoTypeList);		 			 		 			
			}
			else
			{
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);
		
			
	}
}
