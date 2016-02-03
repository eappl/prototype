<?php
/**
 * 通用用户信息控制层
 * @author chen<cxd032404@hotmail.com>
 * $Id: UserController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class UserController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
 protected $oActive;
 protected $oPartner;
 	protected $oSecurityAnswer;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oUser = new Lm_User();
    	$this->oActive = new Lm_Active();
		$this->oPartner = new Config_Partner();
		$this->oSecurityAnswer = new Config_SecurityAnswer();
	}

	/**
	 *账号生成
	 */
	public function createUserAction()
	{
		//基础元素，必须参与验证
		$User['UserName'] = $this->request->UserName;
		//$User['UserMail'] = $this->request->UserMail;
		$User['UserRegTime'] = abs(intval($this->request->UserRegTime));
		$User['UserPassWord'] = $this->request->UserPassWord;
		$User['UserPassWordR'] = $this->request->UserPassWordR;
		$User['UserSourceId'] = abs(intval($this->request->UserSourceId));
		$User['UserSourceDetail'] = abs(intval($this->request->UserSourceDetail));
		$User['UserSourceProjectId'] = abs(intval($this->request->UserSourceProjectId));
		$User['UserSourceActionId'] = abs(intval($this->request->UserSourceActionId));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
	  	$User['UserRegIP'] = $this->request->UserRegIP?$this->request->UserRegIP:"127.0.0.1";
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$start_time = microtime(true);
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'limaogame';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
	  $User['UserRegIP'] = Base_Common::ip2long($User['UserRegIP']);

		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if((strlen($User['UserName'])>=6)&&(strlen($User['UserName'])<=20)&&(!preg_match_all("/[\xe0-\xef][\x80-\xbf]{2}/",$User['UserName'], $mat)))
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['UserRegTime']-time())<=600)
				{
//					if(!preg_match_all("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)|(^\-)|(\-\.)|(\.\-)/",$User['UserMail'],$mat)&&preg_match_all("/^.+\@[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}$/",$User['UserMail'],$mat))
//					{
						//验证密码长度不小于6
						if((strlen($User['UserPassWord'])>=6)&&(strlen($User['UserPassWord'])<=20))
						{
							//验证两次输入密码是否相同
							if($User['UserPassWordR']==$User['UserPassWord'])
							{
					 			unset($User['ReturnType']);
					 			//新建用户
								$PartnerInfo = $this->oPartner->getRow($User['PartnerId']);
								if($PartnerInfo['PartnerId'])
								{
					 				//获取邮箱信息
//									$UserMail = $this->oUser->getUserMail($User['UserMail']);
//									//检查邮箱是否被占用
//									if(!$UserMail['UserId'])
//									{
						 				//删除重复密码
						 				unset($User['UserPassWordR']);
							 			$CreateResult = $this->oUser->InsertUser($User);
							 			//返回用户ID,不包含（23000）
							 			if($CreateResult)
							 			{
											sleep(2);
											$checkResult = $this->oUser->GetUserByName($User['UserName']);
					 						if(count($checkResult)>1)
											{
								 				$User['UserId'] = $checkResult['UserId'];
								 				$result = array('return'=>1,'UserId' => $checkResult['UserId'],'comment'=>$User['UserName']."注册成功");
								 			}
								 			else
								 			{
								 				$result = array('return'=>2,'comment'=>"失败");
								 			}
							 			}
							 			else 
							 			{
				 				 			$result = array('return'=>2,'comment'=>"用户已存在");
							 			}
//							 		}
//						 			else 
//						 			{
//			 				 			$result = array('return'=>2,'comment'=>"邮箱已被其他用户使用");
//						 			}
					 			}
					 			else
					 			{
					 			 	$result = array('return'=>2,'comment'=>"用户所属平台数据不存在");
					 			}
							}
							else
							{
					 			$result = array('return'=>0,'comment'=>"两次输入的密码不相符");	
							}	
						}
						else 
						{
							$result = array('return'=>0,'comment'=>"请重新输入密码");	
						}
//					}
//					else 
//					{
//						$result = array('return'=>0,'comment'=>"请输入合法的邮箱");	
//					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"注册时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入合法的用户名");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$end_time = microtime(true);
		if($User['ReturnType']==1)
		{
			$result['start_time'] = $start_time;
			$result['end_time'] = $end_time;
			$result['time'] = $end_time-$start_time;
			echo json_encode($result);
		}
		else
		{
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			if($result['return']==1)
			{
				$r = $r."|".$result['UserId'];
			}
			echo $r;
		}
	}
	/**
	 *检测账号是否被使用
	 */
	public function checkUserExistAction()
	{
		//基础元素，必须参与验证
		$User['UserName'] = $this->request->UserName;
		$User['Time'] = abs(intval($this->request->Time));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if((strlen($User['UserName'])>=6)&&(strlen($User['UserName'])<=20))
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询用户
					$UserInfo = $this->oUser->GetUserByName($User['UserName']);
		 			//返回用户ID
		 			if($UserInfo['UserId'])
		 			{
			 			$result = array('return'=>1,'UserId'=>$UserInfo['UserId'],'comment'=>$User['UserName']."已经存在");
		 			}
		 			else 
		 			{
			 			$result = array('return'=>0,'comment'=>$User['UserName']."可以正常注册");
		 			}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入合法的用户名");	
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
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
	/**
	 *检测邮箱是否被使用
	 */
	public function checkMailExistAction()
	{
		//基础元素，必须参与验证
		$User['UserMail'] = $this->request->UserMail;
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;

		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效
			if(!preg_match_all("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)|(^\-)|(\-\.)|(\.\-)/",$User['UserMail'],$mat)&&preg_match_all("/^.+\@[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}$/",$User['UserMail'],$mat))
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询用户
					$MailInfo = $this->oUser->GetUserMail($User['UserMail']);
		 			//返回用户ID
		 			if($MailInfo['UserId'])
		 			{
			 			$result = array('return'=>1,'UserId'=>$UserInfo['UserId'],'comment'=>$User['UserMail']."已经被使用");
		 			}
		 			else 
		 			{
			 			$result = array('return'=>0,'comment'=>$User['UserMail']."可以正常使用");
		 			}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入合法的邮箱");	
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
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
	/**
	 *指定大区激活账号
	 */
	public function userActiveAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['AppId'] = abs(intval($this->request->AppId));
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['ActiveTime'] = abs(intval($this->request->ActiveTime));
    	$User['ActiveCode'] = $this->request->ActiveCode;
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				//检查激活码是否为空
				if($User['ActiveCode'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['ActiveTime']-time())<=600)
					{
						//获取所输入的激活码的信息
						$ActiveCodeInfo = $this->oActive->GetActiveCodeInfo($User['ActiveCode']);
			            if($ActiveCodeInfo['ActiveCode'])
			            {
							if(!$ActiveCodeInfo['ActiveUser'])
							{
								if(($ActiveCodeInfo['AppId']==$User['AppId'])&&($ActiveCodeInfo['PartnerId']==$User['PartnerId']))
								{
                 					$UserInfo = $this->oUser->GetUserById($User['UserId']);
									if($UserInfo['UserId'])		
									{
										//查询用户激活记录
										$checkResult = $this->oUser->GetUserActive($User['UserId'],$User['AppId'],$User['PartnerId']);
										//返回用户激活记录
										if(count($checkResult))
										{
											$result = array('return'=>2,'comment'=>"已经激活");
										}
										else 
										{
											unset($User['ReturnType']);
											$activeResult = $this->oUser->InsertUserActive($User);
											//检查更新结果
											if(intval($activeResult)==1)
											{
												$result = array('return'=>1,'comment'=>"激活成功");
											}
											else
											{
											$result = array('return'=>0,'comment'=>"激活失败");
											}
										}
									}
									else
									{
										$result = array('return'=>0,'comment'=>"无此用户");
									}                                    
								}
								else
								{
									$result = array('return'=>2,'comment'=>"您所选择的游戏－平台不符"); 
								}
							}
							else
							{
								$result = array('return'=>2,'comment'=>"该激活码已经被使用"); 
							}
				        }
				        else
				        {
				           $result = array('return'=>2,'comment'=>"激活码不存在"); 
				        }
					}
	  				else
	  				{
							$result = array('return'=>0,'comment'=>"时间有误");	
	  				}	   
				}
		        else
		        {
		           $result = array('return'=>2,'comment'=>"请输入激活码"); 
		        }
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入用户Id");	
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
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
	/**
	 *检测账号在指定大区是否被激活
	 */
	public function checkUserActiveAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['AppId'] = abs(intval($this->request->AppId));
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
					$UserInfo = $this->oUser->GetUserById($User['UserId']);
					if($UserInfo['UserId'])	
		 			{
			 			//查询用户激活记录
						$checkResult = $this->oUser->GetUserActive($User['UserId'],$User['AppId'],$User['PartnerId']);
			 			//返回用户激活记录
			 			if(count($checkResult))
			 			{
				 			$result = array('return'=>2,'comment'=>"已经激活");
			 			}
			 			else 
			 			{
				 			$result = array('return'=>1,'comment'=>"可以继续激活");
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
				$result = array('return'=>2,'comment'=>"请输入用户Id");	
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
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
	/**
	 *输入用户身份证号码
	 */
	public function insertUserIdCardAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
    	$User['UserRealName'] = $this->request->UserRealName;
		$User['IdCard'] = $this->request->IdCard;
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
  
    	if($sign_to_check==$sign)
		{
     	 //验证用户名有效性
			if($User['UserId'])
			{
				if($User['UserRealName'])
				{
					if($User['IdCard'])
					{
						//验证时间戳，时差超过600秒即认为非法
						if(abs($User['Time']-time())<=600)
						{
				 			//查询用户
							$UserInfo = $this->oUser->GetUserById($User['UserId']);
							if($UserInfo['UserId'])
							{
								//获取用户身份证信息
								$UserCommunication = $this->oUser->GetUserCommunication($User['UserId']);
								//如果存在则不可修改
								if($UserCommunication['UserIdCard'])
								{
										$result = array('return'=>0,'comment'=>"身份证无需更改");	
								}
								else
							 	{
									//检查身份证是否合法
									$checkResult = $this->oUser->idcard_verify($User['IdCard']);
									if($checkResult['return']==1)
									{
										//拆解出生日
										$birthday_text = substr($User['IdCard'],6,8);
										//生日格式化
										$Birthday = date("Y-m-d",strtotime($birthday_text));
										//拆解出性别标志位
										$sex = substr($User['IdCard'],strlen($User['IdCard'])-2,1);
										//性别格式化
										$User['UserSex'] = $sex%2?1:2;
										//更新用户数据
										$UserCommunicationArr = array('UserRealName'=>$User['UserRealName'],'UserIdCard'=>$User['IdCard'],'UserBirthDay'=>$Birthday,'UserSex'=>$User['UserSex']);
										$update = $this->oUser->updateUserCommunication($User['UserId'],$UserCommunicationArr);
										if(intval($update)==1)
										{
											$result = array('return'=>1,'comment'=>$User['UserId']."更新成功");	
										}
										else
										{
											$result = array('return'=>0,'comment'=>"更新失败");	
										}
									}
									else
									{
									$result = array('return'=>0,'comment'=>"您所输入的身份证号码有误");	
									}												 	
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
					 $result = array('return'=>0,'comment'=>"请输入身份证号码");	 
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入真实姓名");	
				}    
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入用户Id");	
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
 	/**
	 *更新用户邮箱
	 */
	public function updateUserMailAction()
	{
		//基础元素，必须参与验证
		$User['UserIdCard'] = $this->request->UserIdCard;
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['UserMail'] = $this->request->UserMail;
		$User['Time'] = abs(intval($this->request->Time));
		$User['UserIdCard'] = $this->request->UserIdCard?$this->request->UserIdCard:0;
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
    	if($sign_to_check==$sign)
		{
			if($User['UserId'])
			{
			  if($User['UserMail'])
			  {
			    //验证时间戳，时差超过600秒即认为非法
					if(abs($User['Time']-time())<=600)
					{
						//获取邮箱信息
						$UserMail = $this->oUser->getUserMail($User['UserMail']);
						//检查邮箱是否被占用
						if($UserMail['UserId'])
						{
							//检查邮箱是否被用户自己占用
							if($UserMail['UserId'] == $User['UserId'])
							{
								$result = array('return'=>0,'comment'=>"您已使用这个邮箱");	
							}
							else
							{
								$result = array('return'=>0,'comment'=>"这个邮箱已经被其他用户占用");									 	
							}
						}
						else
						{
						 	//获取用户的联系信息
						 	$UserCommunication = $this->oUser->GetUserCommunication($User['UserId']);
						 	//判断用户是否存在
						 	if($UserCommunication['UserId'])
							{
								//判断用户是否已经有填写邮箱
								if($UserCommunication['UserMail'])
								{
									//获取邮箱信息
				              	$CurrentUserMail = $this->oUser->getUserMail($UserCommunication['UserMail']);
				              	//检查用户邮箱信息是否填写完整
									if($CurrentUserMail['UserId'])
									{
										//检查用户邮箱是否已经验证
										if($CurrentUserMail['UserMailAuth'])
										{
											//检查用户是否有身份证
											if($UserCommunication['UserIdCard'])
											{
												//存在身份证
												$result = array('return'=>0,'comment'=>"请校验身份证号码");
												if($User['UserIdCard']==$UserCommunication['UserIdCard'])
												{
													$MailUpdate = $this->oUser->updateUserMail($User['UserId'],$User['UserMail'],$CurrentUserMail['UserMail']);
													//如果更新成功
													if(intval($MailUpdate)==1)
													{
														$result = array('return'=>1,'comment'=>"更新成功");
													}
													else
													{
														$result = array('return'=>0,'comment'=>"更新失败");
													}
												}
												else
												{
												 	$result = array('return'=>0,'comment'=>"您填写的身份证号码与原有不符");
												}
											}
											else
											{
											 	//不存在身份证
											 	$result = array('return'=>0,'comment'=>"请填写身份证号码");
											}
										}
										else
										{
											//用户邮箱数组
											$bind = array('UserId' => $User['UserId'],'UserMail'=>$User['UserMail']);
											//更新联系信息(用户，新邮箱，旧邮箱)
											$MailUpdate = $this->oUser->updateUserMail($User['UserId'],$User['UserMail'],$CurrentUserMail['UserMail']);
											//如果更新成功
											if(intval($MailUpdate)==1)
											{
												$result = array('return'=>1,'comment'=>"更新成功");
											}
											else
											{
												$result = array('return'=>0,'comment'=>"更新失败");
											}
										}
									}
									else
									{
										$result = array('return'=>0,'comment'=>"用户邮箱数据错误");
									}
								}
								else
								{
									$MailUpdate = $this->oUser->updateUserMail($User['UserId'],$User['UserMail']);
									if(intval($MailUpdate)==1)
									{
										$result = array('return'=>1,'comment'=>"更新成功");
									}
									else
									{
										$result = array('return'=>0,'comment'=>"更新失败");
									}
								}
							}
						 	else
						 	{
						 	 	$result = array('return'=>0,'comment'=>"无此用户");		
						 	}							 	
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}                    
			  }
			  else
			  {
			     $result = array('return'=>0,'comment'=>"请输入邮箱");	 
			  }
			}
			else
			{
			$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
			echo $r;
		}
	}
 	/**
	 *获取用户邮箱信息
	 */
	public function getUserMailAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
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
						//获取邮箱信息
						$UserMail = $this->oUser->GetUserCommunication($User['UserId'],"UserMail");
						//检查邮箱是否被占用
						if($UserMail['UserMail'])
						{
							//获取用户邮箱的关联信息
							$UserMailInfo = $this->oUser->getUserMail($UserMail['UserMail']);
							if($UserMailInfo['UserId'])
							{
								//返回用户邮箱信息
								$result = array('return'=>1,'UserMail'=>$UserMailInfo,'comment'=>"");
							}
							else
							{
								$result = array('return'=>2,'comment'=>"数据错误");									 	
							}
						}
						else
						{
							$result = array('return'=>0,'comment'=>"您尚未填写邮箱");
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
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
 	/**
	 *获取用户联系信息
	 */
	public function getUserCommunicationAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
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
						//获取用户联系信息
						$UserCommunicationInfo = $this->oUser->GetUserCommunication($User['UserId']);
						//格式化用户信息
						$UserCommunicationInfo = array('UserId' => $User['UserId'],
						'UserMail'=>$UserCommunicationInfo['UserMail']?$UserCommunicationInfo['UserMail']:"",
						'UserCountry'=>abs(intval($UserCommunicationInfo['UserCountry']))?abs(intval($UserCommunicationInfo['UserCountry'])):86,
						'UserCity'=>abs(intval($UserCommunicationInfo['UserCity']))?abs(intval($UserCommunicationInfo['UserCity'])):21,
						'UserZipCode'=>$UserCommunicationInfo['UserZipCode']?$UserCommunicationInfo['UserZipCode']:"",
						'UserAddress'=>$UserCommunicationInfo['UserAddress']?$UserCommunicationInfo['UserAddress']:"",
						'UserMobile'=>$UserCommunicationInfo['UserMobile']?$UserCommunicationInfo['UserMobile']:"",
						'UserTel'=>$UserCommunicationInfo['UserTel']?$UserCommunicationInfo['UserTel']:"",
						'UserIdCard'=>$UserCommunicationInfo['UserIdCard']?$UserCommunicationInfo['UserIdCard']:"",
						'UserSex'=>intval($UserCommunicationInfo['UserSex'])?intval($UserCommunicationInfo['UserSex']):0,
						'UserRealName'=>$UserCommunicationInfo['UserRealName']?$UserCommunicationInfo['UserRealName']:"",
						'UserBirthDay'=>$UserCommunicationInfo['UserBirthDay']?$UserCommunicationInfo['UserBirthDay']:'1970-01-01',
						'UserQQ'=>$UserCommunicationInfo['UserQQ']?$UserCommunicationInfo['UserQQ']:"",
						'UserMsn'=>$UserCommunicationInfo['UserMsn']?$UserCommunicationInfo['UserMsn']:"",
						'UserWeibo'=>$UserCommunicationInfo['UserWeibo']?$UserCommunicationInfo['UserWeibo']:"",
						'UserWeixin'=>$UserCommunicationInfo['UserWeixin']?$UserCommunicationInfo['UserWeixin']:"",
						'UserNickName'=>$UserCommunicationInfo['UserNickName']?$UserCommunicationInfo['UserNickName']:"",	
						'adult'=>$UserCommunicationInfo['UserBirthDay']?(Base_Common::checkAdult($UserCommunicationInfo['UserBirthDay'])):(Base_Common::checkAdult('1970-01-01')),						 	
					 	
						);												
						$result = array('return'=>1,'UserCommunicationInfo'=>$UserCommunicationInfo,'comment'=>"");
					}
					else
					{
						//用户不存在
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
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
 	/**
	 *获取用户基础信息
	 */
	public function getUserBaseInfoAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
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
						//返回用户邮箱信息
							$UserInfo['UserRegIP'] = long2ip($UserInfo['UserRegIP']);


						unset($UserInfo['UserPassWord'],$UserInfo['UserRegFrom'],$UserInfo['UserRegDetail'],$UserInfo['UserPosionFix']);
						$result = array('return'=>1,'UserInfo'=>$UserInfo,'comment'=>"");
					}
					else
					{
						//用户不存在
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
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
	/**
	 *更新密码
	 */
	public function updateUserPasswordAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['UserPassWordOld'] = $this->request->UserPassWordOld;
		$User['UserPassWord'] = $this->request->UserPassWord;
		$User['UserPassWordR'] = $this->request->UserPassWordR;
		$User['Time'] = abs(intval($this->request->Time));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		$start_time = microtime(true);
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
	  $User['UserRegIP'] = Base_Common::ip2long($_SERVER["REMOTE_ADDR"]);

		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证用户名有效性
			if($User['UserId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
					//验证密码长度不小于6
					if(strlen($User['UserPassWord'])>=6)
					{
						//验证两次输入密码是否相同
						if($User['UserPassWordR']==$User['UserPassWord'])
						{
				 			//验证原密码输入
				 			if($User['UserPassWordOld'])
				 			{
				 				//验证原密码是否与新密码相同
				 				if($User['PassWordOld']!=md5($User['UserPassWord']))
								{
					 				//获取用户信息
					 				$UserInfo = $this->oUser->GetUserById($User['UserId']);
					 				if($UserInfo['UserId'])
					 				{
					 					if($UserInfo['UserPassWord']==$User['UserPassWordOld'])
					 					{
											$PartnerInfo = $this->oPartner->getRow($User['PartnerId']);
											if($PartnerInfo['PartnerId'])
											{
						 						$update = $this->oUser->updateUser($User['UserId'],array('UserPassWord'=>md5($User['UserPassWord'])));
												if(intval($update)==1)
												{
													$result = array('return'=>1,'comment'=>"密码更新成功");	
												}
												else
												{
													$result = array('return'=>0,'comment'=>"更新失败");											 	
												}
											}
								 			else
								 			{
								 			 	$result = array('return'=>2,'comment'=>"用户所属平台数据不存在");
								 			}
					 					}
					 					else
					 					{
			 			 					$result = array('return'=>0,'comment'=>"原密码输入错误");				 				 					 			 					 					 	
					 					}
					 				}
					 				else
					 				{
			 				 			$result = array('return'=>0,'comment'=>"用户不存在");				 				 	
					 				}
					 			}
					 			else
					 			{
			 				 		$result = array('return'=>0,'comment'=>"新密码不能与原密码相同");				 				 						 			 	
					 			}				 					
				 			}
				 			else
				 			{
		 			 			$result = array('return'=>0,'comment'=>"请输入原密码");				 				 					 			 	
				 			}
						}
						else
						{
				 			$result = array('return'=>0,'comment'=>"两次输入的密码不相符");	
						}	
					}
					else 
					{
						$result = array('return'=>0,'comment'=>"密码长度过短");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
			if($result['return']==1)
			{
				$r = $r."|".$result['UserId'];
			}
			echo $r;
		}
	}
	/**
	 *输入用户密保问题
	 */
	public function insertUserSecurityAnswerAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
    $User['QuestionId'] = abs(intval($this->request->QuestionId));
		$User['Answer'] = $this->request->Answer;
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
  
    if($sign_to_check==$sign)
		{
     	 //验证用户名有效性
			if($User['UserId'])
			{
				if($User['QuestionId'])
				{
					if($User['Answer'])
					{
						//验证时间戳，时差超过600秒即认为非法
						if(abs($User['Time']-time())<=600)
						{
				 			//查询用户
							$UserInfo = $this->oUser->GetUserById($User['UserId']);
							if($UserInfo['UserId'])
							{
								//获取问题信息
								$QuestionInfo = $this->oSecurityAnswer->getRow($User['QuestionId']);
								if(!$QuestionInfo['QuestionId'])
								{
									$result = array('return'=>0,'comment'=>"无此问题");	
								}
								else
							 	{
									//检查用户是否已经输入密保问题
									$UserAnswer = $this->oUser->getUserQuestionAnswer($User['UserId'],$User['QuestionId']);
									if(!$UserAnswer['UserId'])
									{
										$bind = array('UserId'=>$User['UserId'],'QuestionId'=>$User['QuestionId'],'Answer'=>$User['Answer']);
										$Answer = $this->oUser->InsertAnswer($bind);
										if(intval($Answer)==1)		              
										{
											$result = array('return'=>1,'comment'=>"答案更新成功");	
										}
										else
										{
											$result = array('return'=>0,'comment'=>"更新失败");	
										}			              		
									}
									else
									{
										$result = array('return'=>0,'comment'=>"您已经输入密保问题，不能更改");			               	
									}									 	
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
						$result = array('return'=>0,'comment'=>"请输入回答内容");	 
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入问题编号");	
				}    
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入用户Id");	
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
	/**
	 *获取用户是否已经存密保问题
	 */
	public function getUserAnswerAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//print_R($User);
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
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
              //检查用户是否已经输入密保问题
            $UserAnswer = $this->oUser->getUserAnswer($User['UserId']);
						foreach($UserAnswer as $key => $value)
						{
							$QuestionInfo = $this->oSecurityAnswer->getRow($value['QuestionId']);
							$UserAnswer[$key]['Question'] = $QuestionInfo['Question'];
						}
						$result = array('return'=>1,'Answer'=>$UserAnswer);			               	              								 							
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
				$result = array('return'=>0,'comment'=>"请输入用户Id");	
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
 	/**
	 *获取用户联系信息
	 */
	public function updateUserCommunicationAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['UserNickName'] = $this->request->UserNickName;
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
		$User['UserCountry'] = $this->request->UserCountry;
		$User['UserCity'] = $this->request->UserCity;
		$User['UserZipCode'] = $this->request->UserZipCode;
		$User['UserAddress'] = $this->request->UserAddress;
		$User['UserMobile'] = $this->request->UserMobile;
		$User['UserTel'] = $this->request->UserTel;
		$User['UserSex'] = intval($this->request->UserSex)?intval($this->request->UserSex):0;
		$User['UserBirthDay'] = $this->request->UserBirthDay?$this->request->UserBirthDay:'1970-01-01';
		$User['UserQQ'] = $this->request->UserQQ;
		$User['UserMsn'] = $this->request->UserMsn;
		$User['UserWeibo'] = $this->request->UserWeibo;
		$User['UserWeixin'] = $this->request->UserWeixin;
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
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
						//获取用户联系信息
						$UserCommunicationInfo = $this->oUser->GetUserCommunication($User['UserId']);
						//如果用户已经填写身份证，则连带的年龄和性别以及真实姓名不可更改
						if($UserCommunicationInfo['UserIdCard'])
						{
							unset($User['UserSex'],$User['UserBirthDay']);
						}
						$UserId = $User['UserId'];
						unset($User['Time'],$User['UserId'],$User['PartnerId'],$User['ReturnType']);
						$update = $this->oUser->updateUserCommunication($UserId,$User);
						if(intval($update)==1)
						{
							$result = array('return'=>1,'comment'=>"更新成功");							
						}
						else
						{
							$result = array('return'=>2,'comment'=>"更新失败");						 	
						}																		
					}
					else
					{
						//用户不存在
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
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
			$r = $result['return']."|".iconv('UTF-8','GBK',$result['comment']);;
			echo $r;
		}
	}
 	/**
	 *更新用户邮箱
	 */
	public function authUserMailAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['UserMail'] = $this->request->UserMail;
		$User['EndTime'] = abs(intval($this->request->EndTime))?abs(intval($this->request->EndTime)):time()+86400;

		
		//URL验证码
		$sign = $this->request->sign;
		//不参与验证

		//私钥，以后要移开到数据库存储
		$p_sign = 'authmail';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
    	if($sign_to_check==$sign)
		{
			if($User['UserId'])
			{
			  if($User['UserMail'])
			  {
					//查询用户
					$UserCommunicationInfo = $this->oUser->GetUserCommunication($User['UserId']);
					if($UserCommunicationInfo['UserId'])
					{
						//检查用户联系信息的邮箱与传来的是否符合
						if($UserCommunicationInfo['UserMail']==$User['UserMail'])
						{
							//获取邮箱信息
							$UserMail = $this->oUser->getUserMail($User['UserMail']);
							//检查邮箱是否被占用
							if($UserMail['UserId'])
							{
								//检查邮箱是否被用户自己占用
								if($UserMail['UserId'] == $User['UserId'])
								{
									if($UserMail['UserMailAuth']==0)
									{
										//可以更新
										$result = array('return'=>0,'comment'=>"您已使用这个邮箱");	
										if(time()>=$UserMail['UserMailAuthApplyTime']&&time<=$User['EndTime'])
										{
											$Auth = $this->oUser->authUserMail($User['UserId'],$User['UserMail']);
											if(intval($Auth)==1)
											{
												$result = array('return'=>1,'comment'=>"验证成功");	
											}	
											else
											{
												$result = array('return'=>0,'comment'=>"验证失败");												 	
											}																					
										}
										else
										{
											$result = array('return'=>0,'comment'=>"您的邮箱验证已经超时，请重新发送");																									
										}
									}
									else
									{
										$result = array('return'=>0,'comment'=>"您已经验证邮箱，无需重复验证");																									
									}
								}
								else
								{
									//这个邮箱不属于您
									$result = array('return'=>0,'comment'=>"这个邮箱已经被其他用户占用");									 	
								}
							}
							else
							{
								$result = array('return'=>0,'comment'=>"用户邮箱数据错误");
							}									
						}
						else
						{
							$result = array('return'=>0,'comment'=>"您填写的邮箱与此邮箱不符");							 	
						}
					 														
					}
				 	else
				 	{
				 	 	$result = array('return'=>0,'comment'=>"无此用户");		
				 	}                  
			  }
			  else
			  {
			     $result = array('return'=>0,'comment'=>"请输入邮箱");	 
			  }
			}
			else
			{
			$result = array('return'=>0,'comment'=>"请输入用户ID");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
   		echo json_encode($result);
	}
 	/**
	 *更新用户邮箱
	 */
	public function resetUserMailAction()
	{
		$resetLag = 60;
		//基础元素，必须参与验证
		$User['UserId'] = $this->request->UserId;
		$User['PartnerId'] = abs(intval($this->request->PartnerId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//验证URL是否来自可信的发信方
    	if($sign_to_check==$sign)
		{
			if($User['UserId'])
			{
		    //验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
				 	//获取用户的联系信息
				 	$UserCommunication = $this->oUser->GetUserCommunication($User['UserId']);
				 	//判断用户是否存在
				 	if($UserCommunication['UserId'])
					{
						//判断用户是否已经有填写邮箱
						if($UserCommunication['UserMail'])
						{
							//获取邮箱信息
			            	$CurrentUserMail = $this->oUser->getUserMail($UserCommunication['UserMail']);
			            	//检查用户邮箱信息是否填写完整
							if($CurrentUserMail['UserId'])
							{
								//检查用户邮箱是否已经验证
								if($CurrentUserMail['UserMailAuth'])
								{
									 $result = array('return'=>0,'comment'=>"您已验证邮箱，无需重复验证");
								}
								else
								{
							    //验证时间戳，时差小于resetLag即认为频率过高
									if(abs($CurrentUserMail['UserMailAuthApplyTime']-time())>=$resetLag)
									{
										//重置用户邮箱状态
										$MailReset = $this->oUser->resetUserMail($User['UserId']);
										//如果更新成功
										if(intval($MailReset)==1)
										{
											$result = array('return'=>1,'comment'=>"重发成功");
										}
										else
										{
											$result = array('return'=>0,'comment'=>"重发失败");
										}
									}
									else
									{
										$result = array('return'=>0,'comment'=>"上一次申请发送至今时间未超过". $resetLag."秒，请稍后重试");
									}								
								}
							}
							else
							{
								$result = array('return'=>0,'comment'=>"用户邮箱数据错误");
							}
						}
						else
						{
							$result = array('return'=>0,'comment'=>"您尚未填写邮箱，无法重发");
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
			$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
	/**
	 *更新密码
	 */
	public function createResetUserPasswordAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['Time'] = abs(intval($this->request->Time));
		$User['ResetType'] = abs(intval($this->request->ResetType));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
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
					//校验重置方式
					if(in_array($User['ResetType'],array(1,2)))
					{
						//查询用户
						$UserInfo = $this->oUser->GetUserById($User['UserId']);
						if($UserInfo['UserId'])
						{
							$LastTime = $this->oUser->getLastResetPasswordTime($UserInfo['UserId']);
							$Lag = 60;
							if((time()-intval($LastTime))>=$Lag)
							{
								if($UserInfo['PartnerId']==$User['PartnerId'])
								{
									$PartnerInfo = $this->oPartner->getRow($UserInfo['PartnerId']);
									if($PartnerInfo['PartnerId'])
									{
										if($User['ResetType']==2)
										{
											$ResetArr = array('UserId'=>$UserInfo['UserId'],'PartnerId'=>$UserInfo['PartnerId'],'ResetType'=>$User['ResetType'],'AsignResetTime'=>time(),'ResetStatus'=>0);
											$createReset = $this->oUser->createResetPassword($ResetArr);
										}
										elseif($User['ResetType']==1)
										{
											$UserCommunicationInfo = $this->oUser->GetUserCommunication($UserInfo['UserId']);
											if($UserCommunicationInfo['UserMail'])
											{
												$UserMail = $this->oUser->getUserMail($UserCommunicationInfo['UserMail']);
												if($UserMail['UserMailAuth']==1)
												{
													$createReset = $this->oUser->sendUserResetPasswordMail($User['UserId'],$UserMail['UserMail']);	
												}
												else
												{
												 	$result = array('return'=>0,'comment'=>"您的邮箱尚未验证");						 	
												}
											}
											else
											{
												$result = array('return'=>0,'comment'=>"您尚未填写邮箱");						 	
											}											
										}									
										if($createReset)
										{
											$result = array('return'=>1,'ResetId'=>$createReset,'ResetType'=>$User['ResetType'],'comment'=>"申请重置成功，请进入下一页面进行密码重置");						 	
										}
										else
										{
											$result = array('return'=>0,'comment'=>"申请重置失败，请重新申请");						 	
										}
									}
									else
						 			{
						 			 	$result = array('return'=>2,'comment'=>"用户所属平台数据不存在");
						 			}
								}
								else
								{
								 	$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");						 	
								}																
							}
							else
							{
								$result = array('return'=>0,'comment'=>"您在 ".$Lag." 秒内已经申请过重置，请稍后再试");						 	
							}
						}
						else
						{
						 	$result = array('return'=>0,'comment'=>"无此用户");	
						}
					}
					else
					{
					 	$result = array('return'=>0,'comment'=>"请指定合法的重置方式");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
	 *通过密保问题更新密码
	 */
	public function resetUserPasswordBySecurityAnswerAction()
	{
		//基础元素，必须参与验证
		$User['ResetId'] = abs(intval($this->request->ResetId));
		$User['UserId'] = abs(intval($this->request->UserId));
    $User['QuestionId'] = ($this->request->QuestionId);
		$User['Answer'] = urldecode($this->request->Answer);
		$User['UserPassWord'] = $this->request->UserPassWord;
		$User['UserPassWordR'] = $this->request->UserPassWordR;
		$User['Time'] = abs(intval($this->request->Time));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
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
//			//验证用户名有效性
//			if($User['UserId'])
//			{
//				//验证时间戳，时差超过600秒即认为非法
//				if(abs($User['Time']-time())<=600)
//				{
//					if($User['ResetId'])
//					{
//						if($User['Answer'])
//						{
//							if($User['QuestionId'])
//							{
//				 				//获取用户信息
//				 				$UserInfo = $this->oUser->GetUserById($User['UserId']);
//				 				if($UserInfo['UserId'])
//				 				{
//									if($UserInfo['PartnerId']==$User['PartnerId'])
//				 					{
//										$PartnerInfo = $this->oPartner->getRow($UserInfo['PartnerId']);
//										if($PartnerInfo['PartnerId'])
//										{
//											if($UserInfo['PartnerId']==$User['PartnerId'])
//											{									
//								        $QuestionInfo = $this->oSecurityAnswer->getRow($User['QuestionId']);
//								        if($QuestionInfo['QuestionId'])
//								        {
//									  			$ResetInfo = $this->oUser->getResetPassword($User['ResetId'],$User['UserId']);
//									        if($ResetInfo['ResetType']==2)
//									        {
//										        if($ResetInfo['ResetStatus']==0)
//										        {
//											        //检查用户是否已经输入密保问题
//											        $UserAnswer = $this->oUser->getUserQuestionAnswer($User['UserId'],$User['QuestionId']);
//															$Answer = $UserAnswer['Answer'];															
//															if((md5($Answer)==$User['Answer'])&&($UserAnswer['QuestionId']))
//															{
//																//验证密码长度不小于6
//																if(strlen($User['UserPassWord'])>=6)
//																{
//																	//验证两次输入密码是否相同
//																	if($User['UserPassWordR']==$User['UserPassWord'])
//																	{
//																		$reset = $this->oUser->ResetUserPassword($ResetInfo,1,$User['UserPassWord'],array('QuestionId'=>$User['QuestionId']));
//																		$result = array('return'=>1,'comment'=>"回答正确，您的密码已经重置");
//																	}
//																	else
//																	{
//															 			$result = array('return'=>0,'comment'=>"两次输入的密码不相符");	
//																	}	
//																}
//																else 
//																{
//																	$result = array('return'=>0,'comment'=>"密码长度过短");	
//																}
//															}
//															else
//															{
//															 	$reset = $this->oUser->ResetUserPassword($ResetInfo,2,0,array('QuestionId'=>$User['QuestionId']));
//															 	$result = array('return'=>0,'comment'=>"回答错误");
//															}
//														}
//														else
//														{
//															$result = array('return'=>0,'comment'=>"该次更新已经完成或失败");
//														}
//													}
//													else
//													{
//													 	$result = array('return'=>0,'comment'=>"您所选的验证方式不符");
//													}														
//												}
//												else
//												{
//												 	$result = array('return'=>0,'comment'=>"无此问题");
//												}
//											}
//											else
//											{
//						 						$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");						 	
//											}
//										}
//							 			else
//							 			{
//							 			 	$result = array('return'=>0,'comment'=>"用户所属平台数据不存在");
//							 			}
//				 					}
//				 					else
//				 					{
//		 			 					$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");				 				 					 			 					 					 	
//				 					}
//				 				}
//				 				else
//				 				{
//		 				 			$result = array('return'=>0,'comment'=>"用户不存在");				 				 	
//				 				}		
//							}
//							else
//							{
//							 	$result = array('return'=>0,'comment'=>"请选择问题");	
//							}
//						}
//						else
//						{
//						 	$result = array('return'=>0,'comment'=>"请输入答案");	
//						}
//					}
//					else 
//					{
//					 	$result = array('return'=>0,'comment'=>"请指定申请重置ID");	
//					}
//				}
//				else
//				{
//					$result = array('return'=>0,'comment'=>"时间有误");	
//				}
//			}
//			else
//			{
//				$result = array('return'=>0,'comment'=>"请输入用户ID");	
//			}
			$result = array('return'=>0,'comment'=>"密保找回密码功能维护中，请使用邮箱找回！");
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
			if($result['return']==1)
			{
				$r = $r."|".$result['UserId'];
			}
			echo $r;
		}
	}
	/**
	 *通过邮件连接更新密码
	 */
	public function resetUserPasswordByMailAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ResetId'] = abs(intval($this->request->ResetId));
		$User['UserMail'] = $this->request->UserMail;
		$User['UserPassWord'] = $this->request->UserPassWord;
		$User['UserPassWordR'] = $this->request->UserPassWordR;
		$User['EndTime'] = abs(intval($this->request->EndTime));
		$User['Time'] = abs(intval($this->request->Time));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
    $User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = 'lm';
		$sign_to_check = base_common::check_sign($User,"resetpassword");
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
					if($User['ResetId'])
					{
		 				//获取用户信息
		 				$UserInfo = $this->oUser->GetUserById($User['UserId']);
		 				if($UserInfo['UserId'])
		 				{
							if($UserInfo['PartnerId']==$User['PartnerId'])
		 					{
								$PartnerInfo = $this->oPartner->getRow($UserInfo['PartnerId']);
								if($PartnerInfo['PartnerId'])
								{
									if($UserInfo['PartnerId']==$User['PartnerId'])
									{									
						  				$ResetInfo = $this->oUser->getResetPassword($User['ResetId'],$User['UserId']);
								        if($ResetInfo['ResetType']==1)
								        {
									        if($ResetInfo['ResetStatus']==0)
									        {
												if((time()>=$ResetInfo['AsignResetTime'])&&(time()<=$User['EndTime']))
												{
													//验证密码长度不小于6
													if(strlen($User['UserPassWord'])>=6)
													{
														//验证两次输入密码是否相同
														if($User['UserPassWordR']==$User['UserPassWord'])
														{
															$reset = $this->oUser->ResetUserPassword($ResetInfo,1,$User['UserPassWord'],array('UserMail'=>$User['UserMail']));
															if($reset)
															{
																$result = array('return'=>1,'comment'=>"您的密码已经重置");
															}
															else
															{
															 	$result = array('return'=>0,'comment'=>"重置失败");	
															}
														}
														else
														{
												 			$result = array('return'=>0,'comment'=>"两次输入的密码不相符");	
														}	
													}
													else 
													{
														$result = array('return'=>0,'comment'=>"密码长度过短");	
													}
												}
												else 
												{
													$result = array('return'=>0,'comment'=>"该连接已经超时");	
												}											
											}
											else
											{
												$result = array('return'=>0,'comment'=>"该次更新已经完成或失败");
											}
										}
										else
										{
										 	$result = array('return'=>0,'comment'=>"您所选的验证方式不符");
										}														
									}
									else
									{
				 						$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");						 	
									}
								}
					 			else
					 			{
					 			 	$result = array('return'=>0,'comment'=>"用户所属平台数据不存在");
					 			}
		 					}
		 					else
		 					{
			 					$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");				 				 					 			 					 					 	
		 					}
		 				}
		 				else
		 				{
				 			$result = array('return'=>0,'comment'=>"用户不存在");				 				 	
		 				}		
					}
					else 
					{
					 	$result = array('return'=>0,'comment'=>"请指定申请重置ID");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
			}
		}
		else
		{
			$result = array('return'=>2,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);

	}
	/**
	 *密保问题答案
	 */
	public function authUserSecurityAnswerAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
    $User['QuestionId'] = ($this->request->QuestionId);
		$User['Answer'] = urldecode($this->request->Answer);
		$User['Time'] = abs(intval($this->request->Time));
		$User['PartnerId'] = abs(intval($this->request->PartnerId))?abs(intval($this->request->PartnerId)):1;
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
					if($User['Answer'])
					{
						if($User['QuestionId'])
						{
			 				//获取用户信息
			 				$UserInfo = $this->oUser->GetUserById($User['UserId']);
			 				if($UserInfo['UserId'])
			 				{
								if($UserInfo['PartnerId']==$User['PartnerId'])
			 					{
									$PartnerInfo = $this->oPartner->getRow($UserInfo['PartnerId']);
									if($PartnerInfo['PartnerId'])
									{
										if($UserInfo['PartnerId']==$User['PartnerId'])
										{									
							        $QuestionInfo = $this->oSecurityAnswer->getRow($User['QuestionId']);
							        if($QuestionInfo['QuestionId'])
							        {
								        //检查用户是否已经输入密保问题
								        $UserAnswer = $this->oUser->getUserQuestionAnswer($User['UserId'],$User['QuestionId']);
												$Answer = $UserAnswer['Answer'];													
												if(md5($Answer)==$User['Answer'])
												{
													$result = array('return'=>1,'comment'=>"回答正确");
												}
												else
												{
												 	$result = array('return'=>0,'comment'=>"回答错误");
												}														
											}
											else
											{
											 	$result = array('return'=>0,'comment'=>"无此问题");
											}
										}
										else
										{
					 						$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");						 	
										}
									}
						 			else
						 			{
						 			 	$result = array('return'=>0,'comment'=>"用户所属平台数据不存在");
						 			}
			 					}
			 					else
			 					{
	 			 					$result = array('return'=>0,'comment'=>"您所选择的用户不属于该平台");				 				 					 			 					 					 	
			 					}
			 				}
			 				else
			 				{
	 				 			$result = array('return'=>0,'comment'=>"用户不存在");				 				 	
			 				}		
						}
						else
						{
						 	$result = array('return'=>0,'comment'=>"请选择问题");	
						}
					}
					else
					{
					 	$result = array('return'=>0,'comment'=>"请输入答案");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入用户ID");	
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
			if($result['return']==1)
			{
				$r = $r."|".$result['UserId'];
			}
			echo $r;
		}
	}
}
