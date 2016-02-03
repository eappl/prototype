<?php
/**
 * 通用用户角色信息控制层
 * @author chen<cxd032404@hotmail.com>
 * $Id: CharacterController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class CharacterController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oActive;
	protected $oPartnerApp;
	protected $oServer;
	protected $oCharacter;
 

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
		$this->oCharacter = new Lm_Character();
	}

	/**
	 *角色信息生成
	 */
	public function createCharacterAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['CharacterName'] = $this->request->CharacterName;
		$User['CharacterCreateTime'] = abs(intval($this->request->CharacterCreateTime));
		$User['ServerId'] = $this->request->ServerId;
		$User['CharacterLevel'] = abs(intval($this->request->CharacterLevel));
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
			if($User['UserId'])
			{
				//验证用户名有效性
				if((strlen($User['CharacterName'])>=6)&&(strlen($User['CharacterName'])<=20))
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['CharacterCreateTime']-time())<=600)
					{
						if($User['ServerId'])
						{
				 			//查询用户
							$UserInfo = $this->oUser->GetUserById($User['UserId']);
							if($UserInfo['UserId'])
							{
				 				unset($User['ReturnType']);
								//判断用户所选服务器大区是否存在
								$ServerInfo = $this->oServer->getRow($User['ServerId']);
								if($ServerInfo['ServerId'])
								{
				 					//判断服务器信息附带的游戏－运营商信息是否合法
//									$bind = array($ServerInfo['PartnerId'],$ServerInfo['AppId']);										
//									$PartnerInfo = $this->oPartnerApp->getRow($bind);	
//						 			if($PartnerInfo['AppId']&&$PartnerInfo['PartnerId'])
//						 			{
							 			$User['AppId'] = $PartnerInfo['AppId'];
							 			$User['PartnerId'] = $PartnerInfo['PartnerId'];
										$CharacterInfo = $this->oCharacter->getCharacterInfoByUser($User['UserId'],$User['ServerId']);
							 			if(count($CharacterInfo)==0)
							 			{
								 			$AddLog = $this->oCharacter->CreateCharacter($User);
								 			if($AddLog)
								 			{
								 				$result = array('return'=>1,'comment'=>"角色创建成功");
								 			}
								 			else
								 			{
								 			 	$result = array('return'=>0,'comment'=>"角色创建失败");
								 			}
							 			}
							 			else
							 			{
							 			 	$result = array('return'=>0,'comment'=>"您已经在此服务器有角色，无法重复创建");
							 			}
//									}
//									else
//									{
//								 			$result = array('return'=>2,'comment'=>"服务器配置信息错误");										 	
//									}									 									
								}
								else
								{
						 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
								}

							}
							else
							{
					 			$result = array('return'=>2,'comment'=>"无此用户");							 	
							}
						}
						else
						{
							$result = array('return'=>2,'comment'=>"请选择服务器");	
						}
					}
					else
					{
						$result = array('return'=>2,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入合法的用户名");	
				}
			}
			else
			{
			 	$result = array('return'=>0,'comment'=>"请选择用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
	/**
	 *根据用户ID获取角色列表
	 */
	public function getUserCharacterListAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['AppId'] = abs(intval($this->request->AppId));
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['ServerId'] = $this->request->ServerId;
		$User['Time'] = abs(intval($this->request->Time));
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
					$UserInfo = $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{
						$CharacterList = $this->oCharacter->getUserCharacterList($User['UserId'],$User['AppId'],$User['PartnerId'],$User['ServerId']);
			 			if(count($CharacterList)>0)
			 			{
				 			$result = array('return'=>1,'CharacterList'=>$CharacterList);
			 			}
			 			else
			 			{
			 			 	$result = array('return'=>0,'comment'=>"尚未创建角色");
			 			}
					}
					else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");
					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请选择用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
	/**
	 *根据角色名获取角色列表
	 */
	public function getCharacterListAction()
	{
		//基础元素，必须参与验证		
		$User['CharacterName'] = $this->request->CharacterName;
		$User['AppId'] = abs(intval($this->request->AppId));
		$User['PartnetId'] = abs(intval($this->request->PartnerId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['PageSize'] = abs(intval($this->request->PageSize));
		$User['Page'] = abs(intval($this->request->Page));		
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
			if($User['CharacterName'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询用户
					$CharacterList =  $this->oCharacter->getCharacterInfoByCharacter($User['CharacterName'],$User['ServerId']);
					if(is_array($CharacterList))
					{			 			
				 		foreach($CharacterList as $key => $value)
				 		{
				 			if(!isset($ServerInfo[$value['ServerId']]))
				 			{
				 				$ServerInfo[$value['ServerId']] = $this->oServer->getRow($value['ServerId']);	
				 			}
				 			$CharacterList[$key]['ServerName'] = $ServerInfo[$value['ServerId']]['name'];	
				 		}
				 		$result = array('return'=>1,'CharacterList'=>$CharacterList);
					}
					else
					{
			 			$result = array('return'=>2,'comment'=>"无此角色");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入角色名");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *获取角色PVP记录表
	 */
	public function getPvpListAction()
	{
		//基础元素，必须参与验证		
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ServerId'] = $this->request->ServerId;
		$User['HeroId'] = (intval($this->request->HeroId))?(intval($this->request->HeroId)):-1;
		$User['SlkId'] = abs(intval($this->request->SlkId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['PageSize'] = abs(intval($this->request->PageSize));
		$User['Page'] = abs(intval($this->request->Page));		
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
					$UserInfo =  $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{			 			
						//判断用户所选服务器大区是否存在
						$ServerInfo = $this->oServer->getRow($User['ServerId']);
						if($ServerInfo['ServerId'])
						{
							$oTask = new Lm_Task();
							$PvpDetailList = $oTask->getPvpDetail("2012-09-01 00:00:00",date("Y-m-d 23:59:59",time()),$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);
				 			if(count($PvpDetailList['PvpDetail'])>0)
				 			{
						 		$oProduct = new Config_Product_Product();
						 		$oHeroData = new Config_Hero();
						 		$oInstmap = new Config_Instmap();
						 		$HeroArr = $oHeroData->getAll($ServerInfo['AppId']);
						 		$ItemArr = $oProduct->getAll($ServerInfo['AppId'],0);
						 		$InstMapArr = $oInstmap->getAll($ServerInfo['AppId']);
						 		foreach($PvpDetailList['PvpDetail'] as $key => $value)
						 		{
						 			if(!isset($ServerInfo[$value['ServerId']]))
						 			{
						 				$ServerInfo[$value['ServerId']] = $this->oServer->getRow($value['ServerId']);	
						 			}
						 			$EquipList = json_decode($value['EquipList'],true);
						 			foreach($EquipList as $equip_key => $ItemId)
						 			{
						 				$EquipNameList[$equip_key] = $ItemArr[$ServerInfo['AppId']][$ItemId]['name'];	
						 			}
						 			$PvpDetailList['PvpDetail'][$key]['Comment'] = json_decode($value['Comment'],true);
						 			$PvpDetailList['PvpDetail'][$key]['EquipList'] = $EquipList;
						 			$PvpDetailList['PvpDetail'][$key]['EquipNameList'] = $EquipNameList;		 			
						 			$PvpDetailList['PvpDetail'][$key]['ServerName'] = $ServerInfo[$value['ServerId']]['name'];
						 			$PvpDetailList['PvpDetail'][$key]['HeroName'] = $HeroArr[$value['AppId']][$value['HeroId']]['name'];	
						 			$PvpDetailList['PvpDetail'][$key]['PvpName'] = $InstMapArr[$value['AppId']][$value['SlkId']]['name'];		
							 		$PvpDetailList['PvpDetail'][$key]['PvpTotalInfo'] = $oTask->getPvpTotalLog($value['PvpEnterTime'],$value['EctypeId']);

						 		}
					 			$result = array('return'=>1,'PvpDetailList'=>$PvpDetailList);
				 			}
				 			else
				 			{
				 			 	$result = array('return'=>0,'comment'=>"尚未创建角色");
				 			}
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
						}				 			
			 		}					
			 		else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *根据获取角色最近场次PVP英雄使用情况表
	 */
	public function getPvpRecentHeroAction()
	{
		//基础元素，必须参与验证		
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ServerId'] = $this->request->ServerId;
		$User['PvpCount'] = (intval($this->request->PvpCount))?(intval($this->request->PvpCount)):1;
		$User['Time'] = abs(intval($this->request->Time));
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
					$UserInfo =  $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{			 			
						//判断用户所选服务器大区是否存在
						$ServerInfo = $this->oServer->getRow($User['ServerId']);
						if($ServerInfo['ServerId'])
						{
							$oTask = new Lm_Task();
							$PvpDetailList = $oTask->getPvpDetail("2012-09-01 00:00:00",date("Y-m-d 23:59:59",time()),$UserInfo['UserId'],0,-1,$User['ServerId'],0,0,0,$User['PvpCount']);
				 			if(count($PvpDetailList['PvpDetail'])>0)
				 			{
						 		$oHeroData = new Config_Hero();
						 		$HeroArr = $oHeroData->getAll($ServerInfo['AppId']);
						 		foreach($PvpDetailList['PvpDetail'] as $key => $value)
						 		{
									$HeroList[$value['HeroId']]['HeroCount'] ++;
									$HeroList[$value['HeroId']]['LastUse']  = max($HeroList[$value['HeroId']]['LastUse'],$value['PvpEnterTime']);		
		
						 		}
						 		foreach($HeroList as $Hero => $HeroInfo)
						 		{
						 			$HeroUseList[$HeroInfo['HeroCount']][$HeroInfo['LastUse']] = $Hero;	
						 		}
						 		krsort($HeroUseList);
						 		$rank = 0;
					 			foreach($HeroUseList as $c => $c_data)
					 			{
					 				krsort($c_data);
					 				foreach($c_data as $t => $t_data)
					 				{
					 					$rank++;
					 					$returnArr[$rank]['HeroId'] = $t_data;
					 					$returnArr[$rank]['HeroName'] = $HeroArr[$ServerInfo['AppId']][$t_data]['name'];
					 					$returnArr[$rank]['UserCount'] = $c;
					 				}	
					 			}						 		
					 			$result = array('return'=>1,'RecentHero'=>$returnArr);
				 			}
				 			else
				 			{
				 			 	$result = array('return'=>0,'comment'=>"无PVP记录");
				 			}
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
						}				 			
			 		}					
			 		else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *获取PVP场次详情表
	 */
	public function getPvpDetailAction()
	{
		//基础元素，必须参与验证		
		$User['ServerId'] = $this->request->ServerId;
		$User['EctypeId'] = abs(intval($this->request->EctypeId));
		$User['PvpEnterTime'] = abs(intval($this->request->PvpEnterTime));
		$User['Time'] = abs(intval($this->request->Time));
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
			if($User['EctypeId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
					if($User['PvpEnterTime'])
					{			 			
						//判断用户所选服务器大区是否存在
						$ServerInfo = $this->oServer->getRow($User['ServerId']);
						if($ServerInfo['ServerId'])
						{
							$oTask = new Lm_Task();
							$StartTime = date("Y-m-d H:i:s",$User['PvpEnterTime']);
							$EndTime = date("Y-m-d H:i:s",$User['PvpEnterTime']);
							$PvpDetailList = $oTask->getPvpDetail($StartTime,$EndTime,0,0,-1,$User['ServerId'],$User['EctypeId'],0,0,100);
				 			if(count($PvpDetailList['PvpDetail'])>0)
				 			{
						 		$oProduct = new Config_Product_Product();
						 		$oHeroData = new Config_Hero();
						 		$oInstmap = new Config_Instmap();
						 		$HeroArr = $oHeroData->getAll($ServerInfo['AppId']);
						 		$ItemArr = $oProduct->getAll($ServerInfo['AppId'],0);
						 		$InstMapArr = $oInstmap->getAll($ServerInfo['AppId']);
						 		foreach($PvpDetailList['PvpDetail'] as $key => $value)
						 		{
						 			if(!isset($ServerInfo[$value['ServerId']]))
						 			{
						 				$ServerInfo[$value['ServerId']] = $this->oServer->getRow($value['ServerId']);	
						 			}
						 			$EquipList = json_decode($value['EquipList'],true);
						 			foreach($EquipList as $equip_key => $ItemId)
						 			{
						 				$EquipNameList[$equip_key] = $ItemArr[$ServerInfo['AppId']][$ItemId]['name'];	
						 			}
						 			$PvpDetailList['PvpDetail'][$key]['Comment'] = json_decode($value['Comment'],true);
						 			$PvpDetailList['PvpDetail'][$key]['EquipList'] = $EquipList;
						 			$PvpDetailList['PvpDetail'][$key]['EquipNameList'] = $EquipNameList;		 			
						 			$PvpDetailList['PvpDetail'][$key]['ServerName'] = $ServerInfo[$value['ServerId']]['name'];
						 			$PvpDetailList['PvpDetail'][$key]['HeroName'] = $HeroArr[$value['AppId']][$value['HeroId']]['name'];	
						 			$PvpDetailList['PvpDetail'][$key]['PvpName'] = $InstMapArr[$value['AppId']][$value['SlkId']]['name'];		
									$CharacterList = $this->oCharacter->getUserCharacterList($value['UserId'],$value['AppId'],$value['PartnerId'],$value['ServerId']);
						 			$PvpDetailList['PvpDetail'][$key]['CharacterName'] = $CharacterList['0']['CharacterName'];		
						 		}
						 		$PvpTotalInfo = $oTask->getPvpTotalLog($User['PvpEnterTime'],$User['EctypeId']);
					 			$result = array('return'=>1,'PvpDetailList'=>$PvpDetailList,'PvpTotalInfo'=>$PvpTotalInfo);
				 			}
				 			else
				 			{
				 			 	$result = array('return'=>0,'comment'=>"无此记录");
				 			}
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
						}				 			
			 		}					
			 		else
					{
			 			$result = array('return'=>2,'comment'=>"请指定PVP开始时间");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用PVP生命周期");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *获取PVP记录汇总
	 */
	public function getPvpSummaryAction()
	{
		//基础元素，必须参与验证		
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ServerId'] = $this->request->ServerId;
		$User['HeroId'] = (intval($this->request->HeroId))?(intval($this->request->HeroId)):-1;
		$User['SlkId'] = abs(intval($this->request->SlkId));
		$User['Time'] = abs(intval($this->request->Time));
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
					$UserInfo =  $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{			 			
						//判断用户所选服务器大区是否存在
						$ServerInfo = $this->oServer->getRow($User['ServerId']);
						if($ServerInfo['ServerId'])
						{
							$oTask = new Lm_Task();
							$PvpSummary = $oTask->getPvpSummary(0,0,$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);																	
				 			if(count($PvpSummary['Won'])>0)
				 			{
								$PvpDetailList = $oTask->getPvpDetail(0,0,$User['UserId'],0,-1,$User['ServerId'],0,0,0,0);
								foreach($PvpDetailList['PvpDetail'] as $key => $value)
								{
									$Comment = json_decode($value['Comment'],true);
									$PvpSummary['AssistKing']+=$Comment['AssistKing'];
									$PvpSummary['DestroyKing']+=$Comment['DestroyKing'];
									$PvpSummary['Mvp']+=$Comment['Mvp'];
									$PvpSummary['KillKing']+=$Comment['KillKing'];
									$PvpSummary['Killing']['2']+=$Comment['Double'];
									$PvpSummary['Killing']['3']+=$Comment['Triple'];
									$PvpSummary['Killing']['4']+=$Comment['Four'];
									$PvpSummary['Killing']['5']+=$Comment['Five'];
									$PvpSummary['MaxKilling'] = max($PvpSummary['MaxKilling'],$value['KillNum']);
									$Log[count($Log)+1] = array("Time"=>$value['PvpEnterTime'],'Won' =>$value['Won']);				
								}
								foreach($Log as $k => $v)
								{
									if((!$Log[$k-1]['Won'])&&($v['Won']))
									{
										for($i=$k;$i<=count($Log);$i++)
										{
											if($Log[$i]['Won'])
											{
												$t[$v['Time']][] = $Log[$i]['Time'];
												$PvpSummary['ContinuousWon'] = max($PvpSummary['ContinuousWon'],count($t[$v['Time']]));	
											}
											else
											{
												break; 	
											}	
										}
									}	
								}
								$PvpSummary['LastUpdateTime'] = $Log['1']['Time'];
					 			$result = array('return'=>1,'PvpSummary'=>$PvpSummary);
				 			}
				 			else
				 			{
				 			 	$result = array('return'=>0,'comment'=>"无PVP记录");
				 			}
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
						}				 			
			 		}					
			 		else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *获取PVE塔副本记录汇总
	 */
	public function getPveTowerSummaryAction()
	{
		//基础元素，必须参与验证		
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ServerId'] = $this->request->ServerId;
		$User['HeroId'] = (intval($this->request->HeroId))?(intval($this->request->HeroId)):-1;
		$User['Time'] = abs(intval($this->request->Time));
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
					$UserInfo =  $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{			 			
						//判断用户所选服务器大区是否存在
						$ServerInfo = $this->oServer->getRow($User['ServerId']);
						if($ServerInfo['ServerId'])
						{
							$oTask = new Lm_Task();
							$FirstKillSummary = $oTask->getPveTowerFirstKill(0,0,$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);
							$FastestKillSummary = $oTask->getPveTowerFastestKill(0,0,$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);
							$TotalKillSummary = $oTask->getPveTowerTotalKill(0,0,$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);
				 			
					 		$result = array('return'=>1,'FirstKillSummary'=>$FirstKillSummary,'FastestKillSummary'=>$FastestKillSummary,'TotalKillSummary'=>$TotalKillSummary);
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
						}				 			
			 		}					
			 		else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *获取PVE塔副本记录汇总
	 */
	public function getPveTowerThisWeekAction()
	{
		//基础元素，必须参与验证		
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ServerId'] = $this->request->ServerId;
		$User['HeroId'] = (intval($this->request->HeroId))?(intval($this->request->HeroId)):-1;
		$User['Time'] = abs(intval($this->request->Time));
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
					$UserInfo =  $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{			 			
						//判断用户所选服务器大区是否存在
						$ServerInfo = $this->oServer->getRow($User['ServerId']);
						if($ServerInfo['ServerId'])
						{
							$oTask = new Lm_Task();
							$t = time();
							while(date("H:i:s",$t)!="06:00:00")
							{
								$t--;
							}
							while(date("w",$t)!="5")
							{
								$t = $t - 86400;
							}
							$StartTime = date("Y-m-d H:i:s",$t);
							$EndTime = date("Y-m-d H:i:s",$t + 86400 * 7);
							$FirstKillSummary = $oTask->getPveTowerFirstKill($StartTime,$EndTime,$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);
							$FastestKillSummary = $oTask->getPveTowerFastestKill($StartTime,$EndTime,$UserInfo['UserId'],$User['SlkId'],$User['HeroId'],$User['ServerId'],0,0,($User['Page']-1)*$User['PageSize'],$User['PageSize']);				 			
					 		$result = array('return'=>1,'FirstKill'=>$FirstKillSummary,'FastestKillSummary'=>$FastestKillSummary);
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
						}				 			
			 		}					
			 		else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请指定用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *使用礼包码
	 */
	public function useProductPackCodeAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ProductPackCode'] = $this->request->ProductPackCode;
		$User['Time'] = abs(intval($this->request->Time));
		$User['ServerId'] = $this->request->ServerId;
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
			if($User['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
					if($User['ServerId'])
					{
			 			//查询用户
						$UserInfo = $this->oUser->GetUserById($User['UserId']);
						if($UserInfo['UserId'])
						{
			 				unset($User['ReturnType']);
							//判断用户所选服务器大区是否存在
							$ServerInfo = $this->oServer->getRow($User['ServerId']);
							if($ServerInfo['ServerId'])
							{
								$oProductPack = new Config_Product_Pack();
								$ProductPackCode = $oProductPack->getUserProductPackCode($User['ProductPackCode'],$User['UserId']);
								if(!$ProductPackCode['ProductPackCode'])
								{
									$ProductPackCode = $oProductPack->getProductPackCode($User['ProductPackCode']);	
								}	
								if($ProductPackCode['ProductPackCode'])
								{
									if(($ProductPackCode['AppId'] = $ServerInfo['AppId'])&&($ProductPackCode['PartnerId'] = $ServerInfo['PartnerId']))
									{
										if(!$ProductPackCode['UsedUser'])
										{											
											$GenInfo = $oProductPack->GetGenPackCodeLogById($ProductPackCode['GenId']);
											if($GenInfo['GenId'])
											{
												if($User['Time']<=$GenInfo['EndTime'])
												{
													if($GenInfo['needBind']==2)
													{
														$ProductPack = $oProductPack->getRow($ProductPackCode['ProductPackId']);
														if($ProductPack['ProductPackId'])
														{
															if($ProductPack['UseCountLimit'])
															{
																$GenLog = $oProductPack->getGenLog(0,0,$ProductPackCode['ProductPackId'],2,0,0,0);
																$UseLog = $oProductPack->getUserPackUserLog(0,0,$UserInfo['UserId'],$ProductPackCode['ProductPackId'],Base_Common::getArrList($GenLog['GenLog']),0);
																$Count = count($UseLog);
																//获取该用户该礼包使用次数	
																if($Count>=$ProductPack['UseCountLimit'])
																{
																	$result = array('return'=>0,'comment'=>"您的该礼包使用次数已经超过上限");							 									 											 		
																}
																else 
																{
																 	$Use = $oProductPack->usePackCode($ProductPackCode['ProductPackCode'],$ServerInfo['ServerId'],$UserInfo['UserId'],$User['Time']);
																	if($Use)
																	{
																		$result = array('return'=>1,'comment'=>"礼包已经在发送中，请稍后登陆游戏查收");	
																	}
																	else
																	{
																	 	$result = array('return'=>2,'comment'=>"礼包发送失败，请稍后重试");	
																	}						 									 											 		
																}
															}
															else
															{
															 	$Use = $oProductPack->usePackCode($ProductPackCode['ProductPackCode'],$ServerInfo['ServerId'],$UserInfo['UserId'],$User['Time']);
																if($Use)
																{
																	$result = array('return'=>1,'comment'=>"礼包已经在发送中，请稍后登陆游戏查收");	
																}
																else
																{
																 	$result = array('return'=>2,'comment'=>"礼包发送失败，请稍后重试");	
																}																
															}															
														}
														else
														{
															$result = array('return'=>0,'comment'=>"无此礼包");							 									 											 																 	
														}

													}
													else
													{
														if($ProductPackCode['AsignUser']==$UserInfo['UserId'])
														{
														 	$Use = $oProductPack->usePackCode($ProductPackCode['ProductPackCode'],$ServerInfo['ServerId'],$UserInfo['UserId'],$User['Time']);
															if($Use)
															{
																$result = array('return'=>1,'comment'=>"礼包已经在发送中，请稍后登陆游戏查收");	
															}
															else
															{
															 	$result = array('return'=>2,'comment'=>"礼包发送失败，请稍后重试");	
															}															
														}
														else 
														{
															$result = array('return'=>0,'comment'=>"该礼包的指定使用用户不是您");	 	
														}
													}
												}
												else
												{
												 	$result = array('return'=>0,'comment'=>"礼包已经过期");							 									 											 		
												}
											}
											else
											{
												$result = array('return'=>0,'comment'=>"无此批次");							 									 											 		
											}
										}
										else
										{
										 	if($ProductPackCode['UsedUser']==$UserInfo['UserId'])
										 	{
										 		if($ProductPackCode['CodeStatus']==1)
										 		{
											 	 	$ProductPack = $oProductPack->getRow($ProductPackCode['ProductPackId']);
											 	 	if((time()-$ProductPack['UseTimeLag'])>=$ProductPackCode['UsedTime'])
											 	 	{
												 	 	$errorList = $this->oCharacter->getSendErrorList($UserInfo['UserId'],$ProductPackCode['ProductPackCode']);
											 	 		$addLog = 0;
												 	 	$oProduct = new Config_Product_Product;
												 	 	foreach($errorList as $key => $errorLog)
												 	 	{
												 	 		$insert = $oProduct->insertIntoProductSendList($ProductPackCode['ProductPackCode'],'ProductPack',$errorLog['ProductId'],$errorLog['ProductType'],$errorLog['ProductCount'],$errorLog['UserId'],$errorLog['ServerId'],time());
												 	 		if($insert)
												 	 		{
												 	 			$addLog++;	
												 	 		}	
												 	 	}
												 	 	if($addLog)
												 	 	{
												 	 		$oProductPack->updatePackCode($ProductPackCode['ProductPackCode'],array('UsedTime'=>time()));
												 	 		$result = array('return'=>1,'comment'=>$addLog."个道具重新加入发放队列");	
												 		}
												 		else
												 		{
												 	 		$result = array('return'=>1,'comment'=>"礼包码已于 ".date("Y-m-d H:i:s",$ProductPackCode['UsedTime'])." 被您使用");	
												 		}											 	 		
											 	 	}
											 	 	else
											 	 	{
														$result = array('return'=>0,'comment'=>"您的操作过于频繁，".$ProductPack['UseTimeLag']."秒内只允许操作一次，请稍后再试");											 	 		 	
											 	 	}
						 									 											 		
										 		}
										 	}
										 	else
										 	{
										 	 	$result = array('return'=>0,'comment'=>"礼包码已于 ".date("Y-m-d H:i:s",$ProductPackCode['UsedTime'])." 被其他用户使用");							 									 	
										 	}
										}
									}
									else
									{
									 	$result = array('return'=>0,'comment'=>"您所输入的礼包码不允许在此服务器使用");							 									 	
									}
								}
								else
								{
						 			$result = array('return'=>0,'comment'=>"无此礼包码");							 									 	
								}									 									
							}
							else
							{
					 			$result = array('return'=>0,'comment'=>"您所选择的服务器不存在");							 	
							}

						}
						else
						{
				 			$result = array('return'=>2,'comment'=>"无此用户");							 	
						}
					}
					else
					{
						$result = array('return'=>2,'comment'=>"请选择服务器");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
			 	$result = array('return'=>0,'comment'=>"请选择用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *礼包码详情
	 */
	public function getProductPackCodeDetailAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ProductPackCode'] = $this->request->ProductPackCode;
		$User['Time'] = abs(intval($this->request->Time));
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
			if($User['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询用户
					$UserInfo = $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{
		 				unset($User['ReturnType']);
						$oProductPack = new Config_Product_Pack();
						$ProductPackCode = $this->oProductPack->getUserProductPackCode($User['ProductPackCode'],$User['UserId']);
						if(!$ProductPackCode['ProductPackCode'])
						{
							$ProductPackCode = $oProductPack->getProductPackCode($User['ProductPackCode']);	
						}						
						if($ProductPackCode['ProductPackCode'])
						{
							if(($ProductPackCode['AsignUser']!=$User['UserId'])&&($ProductPackCode['AsignUser']>0))
							{
				 				$result = array('return'=>2,'comment'=>"您不是此礼包的指定使用人");							 									 									
							}
							else
							{
								if(($ProductPackCode['UsedUser']!=$User['UserId'])&&($ProductPackCode['UsedUser']>0))
								{
					 				$result = array('return'=>2,'comment'=>"您不是此礼包的使用人");							 									 									
								}
								else
								{
									$oSkin = new Config_Skin();
									$oHero = new Config_Hero();
									$oMoney = new Config_Money();
									$oApp = new Config_App();
									$ProductPack = $oProductPack->getRow($ProductPackCode['ProductPackId']);
									$Comment = json_decode($ProductPack['Comment'],true);
									if(is_array($Comment))
									{
										unset($ProductList);
										foreach($Comment as $Type => $TypeInfo)
										{
											foreach($TypeInfo as $ProductId => $Count)
											{
												if(!isset($ProductInfo[$Type][$ProductPack['AppId']][$ProductId]))
												{
													if($Type=="hero")
													{
														$ProductInfo[$Type][$ProductPack['AppId']][$ProductId] = $oHero->getRow($ProductId,$ProductPack['AppId'],'*');
													}
													elseif($Type=="skin")
													{
														$ProductInfo[$Type][$ProductPack['AppId']][$ProductId] = $oSkin->getRow($ProductId,$ProductPack['AppId'],'*');
													}
													elseif($Type=="product")
													{
														$ProductInfo[$Type][$ProductPack['AppId']][$ProductId] = $oProduct->getRow($ProductId,$ProductPack['AppId'],'*');
													}
													elseif($Type=="money")
													{
														$ProductInfo[$Type][$ProductPack['AppId']][$ProductId] = $oMoney->getRow($ProductId,$ProductPack['AppId'],'*');
													}
													elseif($Type=="appcoin")
													{
														$AppInfo = $oApp->getRow($ProductPack['AppId']);
														$comment = json_decode($AppInfo['comment'],true);
														$ProductInfo[$Type][$ProductPack['AppId']][$ProductId]['name'] = $comment['coin_name'];
													}
												}
												$ProductList[$Type]['detail'][$ProductId] = $ProductInfo[$Type][$ProductPack['AppId']][$ProductId]['name']."*".$Count."个";
											}
											$TypeList[$Type] = implode(",",$ProductList[$Type]['detail']);							
										}
				
										$ProductPack['ProductListText'] = implode(",",$TypeList);		
									}
									else
									{
										$ProductPack['ProductListText'] = "无道具";		
									}
									$result = array('return'=>1,'ProductPack'=>$ProductPack,'ProductPackCode'=>$ProductPackCode);								 	
								}							 	
							}								
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"无此礼包码");							 									 	
						}									 									
					}
					else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");							 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
			 	$result = array('return'=>0,'comment'=>"请选择用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *用户礼包码列表
	 */
	public function getUserProductPackCodeAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['Used'] = abs(intval($this->request->Used));
		$User['ProductPackId'] = abs(intval($this->request->ProductPackId));
		$User['GenId'] = abs(intval($this->request->GenId));
		$User['PageSize'] = abs(intval($this->request->PageSize));
		$User['Page'] = abs(intval($this->request->Page));	
		$User['Time'] = abs(intval($this->request->Time));
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
			if($User['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询用户
					$UserInfo = $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{
		 				unset($User['ReturnType']);
						$oProductPack = new Config_Product_Pack();
						$ProductPackCodeList = $oProductPack->getUserProductPackCodeList($User['UserId'],$User['Used'],$User['ProductPackId'],$User['GenId'],($User['Page']-1)*$User['PageSize'],$User['PageSize']);
						if(is_array($ProductPackCodeList['UserProductPackCodeList']))
						{
							foreach($ProductPackCodeList['UserProductPackCodeList'] as $Code => $CodeData)
							{
								if(!isset($ProductPackList[$CodeData['ProductPackId']]))
								{
									$ProductPackList[$CodeData['ProductPackId']] = $oProductPack->getRow($CodeData['ProductPackId']);
								}
								if(!isset($GenLogList[$CodeData['GenId']]))
								{
									$GenLogList[$CodeData['GenId']] = $oProductPack->GetGenPackCodeLogById($CodeData['GenId']);
								}		
							}	
						}
						if(is_array($ProductPackList))
						{
							$oSkin = new Config_Skin();
							$oHero = new Config_Hero();
							$oMoney = new Config_Money();
							$oProduct = new Config_Product_Product();
							$oApp = new Config_App();
							foreach($ProductPackList as $ProductPack => $ProductPackInfo)
							{
								unset($Comment);
								$Comment = json_decode($ProductPackInfo['Comment'],true);
								if(!isset($ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]))
								{
									if(is_array($Comment))
									{
										foreach($Comment as $Type => $TypeInfo)
										{
											foreach($TypeInfo as $ProductId => $Count)
											{
												if(!isset($ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]))
												{
													if($Type=="hero")
													{
														$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $oHero->getRow($ProductId,$ProductPackInfo['AppId'],'*');
													}
													elseif($Type=="skin")
													{
														$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $oSkin->getRow($ProductId,$ProductPackInfo['AppId'],'*');
													}
													elseif($Type=="product")
													{
														$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $oProduct->getRow($ProductId,$ProductPackInfo['AppId'],'*');
													}
													elseif($Type=="money")
													{
														$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId] = $oMoney->getRow($ProductId,$ProductPackInfo['AppId'],'*');
													}
													elseif($Type=="appcoin")
													{
														$AppInfo = $oApp->getRow($ProductPackInfo['AppId']);
														$comment = json_decode($AppInfo['comment'],true);
														$ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]['name'] = $comment['coin_name'];
													}
												}
												$ProductList[$Type]['detail'][$ProductId] = $ProductInfo[$Type][$ProductPackInfo['AppId']][$ProductId]['name']."*".$Count."个";
											}
											$TypeList[$Type] = implode(",",$ProductList[$Type]['detail']);							
										}
										$ProductPackList[$ProductPack]['ProductListText'] = implode(",",$TypeList);																						
									}
									else
									{
										$ProductPackList[$ProductPack]['ProductListText'] = "无道具";		
									}
								}									
							}	
						}
						$result = array('return'=>1,'ProductPackCodeList'=>$ProductPackCodeList,'ProductPackList'=>$ProductPackList,'GenLogList'=>$GenLogList);								 									 									
					}
					else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");							 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
			 	$result = array('return'=>0,'comment'=>"请选择用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
	/**
	 *设定用户角色为默认角色
	 */
	public function setUserCharacterDefaultAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['AppId'] = abs(intval($this->request->AppId));
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['ServerId'] = $this->request->ServerId;
		$User['Time'] = abs(intval($this->request->Time));
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
					$UserInfo = $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])
					{
						$CharacterList = $this->oCharacter->getUserCharacterList($User['UserId'],$User['AppId'],$User['PartnerId']);
			 			if(count($CharacterList)>0)
			 			{
				 			if(isset($CharacterList[$User['ServerId']]))
				 			{
					 			foreach($CharacterList as $Server => $CharacterInfo)
					 			{
				 					$Comment = json_decode($CharacterInfo['Comment'],true);
					 				if($CharacterInfo['ServerId']==$User['ServerId'])
					 				{
					 					$Comment['default'] = 1;
					 				}
					 				else
					 				{
					 					$Comment['default'] = 0;					 				 	
					 				}
					 				$bind = array('Comment'=>json_encode($Comment));
					 				$update = $this->oCharacter->updateCharacterInfo($User['UserId'],$Server,$bind);
					 			}
				 			 	$result = array('return'=>1,'comment'=>"更新完毕");	
				 			}
				 			else
				 			{
				 			 	$result = array('return'=>0,'comment'=>"无此角色");
				 			}
			 			}
			 			else
			 			{
			 			 	$result = array('return'=>0,'comment'=>"无角色");
			 			}
					}
					else
					{
			 			$result = array('return'=>2,'comment'=>"无此用户");
					 	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请选择用户");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		if($User['ReturnType']==1)
		{
			echo json_encode($result);
		}
	}
}
