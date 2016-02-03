<?php
/**
 * 问题控制层
 * $Id: QuestionController.php 425 2012-12-14 06:14:59Z chenxiaodong $
 */

class QuestionController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oComplain;
	protected $oQuestion;
	protected $oCategory;
	protected $oQtype;
	protected $oUser;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oComplain = new Kubao_Complain();
		$this->oQuestion = new Kubao_Question();
		$this->oCategory = new Kubao_Category();
		$this->oQtype = new Kubao_Qtype();
		$this->oUser = new Kubao_User();
	}
	/**
	 *追问
	 */
	public function subQuestionAction()
	{
		//基础元素，必须参与验证
		$Question['QuestionId'] = abs(intval($this->request->QuestionId));
		$Question['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$Question['UserName'] = urldecode(trim($this->request->UserName));
		$Question['UserId'] = urldecode(trim($this->request->UserId));
		$Question['QuickAsk'] = urldecode(trim($this->request->QuickAsk));
		$Question['QuestionContent'] = urldecode(trim($this->request->QuestionContent));
		$Question['IP'] = urldecode(trim($this->request->IP));
		$Question['OS'] = urldecode(trim($this->request->OS));
		$Question['Browser'] = urldecode(trim($this->request->Browser));
		$Question['Time'] = abs(intval($this->request->Time));
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($Question,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Question['Time']-time())<=600)
			{
				//问题类型为咨询|建议
				if($Question['QuestionType']=="question")
				{
					//获取用户禁言记录
					$Gag = $this->oUser->getGag($Question['UserName'],'id');
					//如果获取到用户禁言记录
					if($Gag['id'])
					{
						$result = array('return'=>0,'comment'=>"很抱歉，您的帐号已被管理员禁言处理，请您自觉遵守5173言论规则。");	
					}
					else
					{
						//获取问题内容
						$QuestionDetail = $this->oQuestion->getQuestionDetail($Question['QuestionId']);
						//如果获取到问题
						if($QuestionDetail['QuestionId'])
						{
							//如果可以继续追问
							if($QuestionDetail['SubQuestion'] == 1)
							{
								$RecentAsked = $this->oQuestion->getRecentByIP($Question['IP'],$QuestionDetail['QuestionId']);
								//如果单位时间追问数量不限定 或者 追问数量尚未超过限制
								if($Setting['limit_question_num_add'] ==0 || $Setting['limit_question_num_add'] > $RecentAsked)
								{
									//cookie验证
									$CookieAuthor = $this->oUser->authorUserByCookie($Question['QuickAsk'],'zx,jy',$QuestionDetail['QuestionId']);
									//用户名验证
									$UserAuthor = $this->oUser->authorUserByName($Question['UserName'],$QuestionDetail['AuthorName']);
									//如果用户验证痛过
									if($UserAuthor)
									{
										//获取主问记录已继承追问数据
										$QuestionInfo = $this->oQuestion->getQuestion($QuestionDetail['QuestionId'],'author,author_id,qtype,cid,gameid,game_name,areaid,area_name,serverid,server_name,operatorid,operator_name');
										$Comment['OS'] = $Question['OS'];
										$Comment['Browser'] = $Question['Browser'];
										$QuestionInfo = array(
															"cid"=> $QuestionInfo['cid'],
															"qtype"=> $QuestionInfo['qtype'],
															"gameid" => $QuestionInfo['gameid'],
															"game_name" => $QuestionInfo['game_name'],
															"operatorid" => $QuestionInfo['operatorid'],
															"operator_name" => $QuestionInfo['operator_name'],
															"areaid" => $QuestionInfo['areaid'],
															"area_name" => $QuestionInfo['area_name'],
															"serverid" => $QuestionInfo['serverid'],
															"server_name" => $QuestionInfo['server_name'],
															"author"=>$QuestionInfo['author'],
															"author_id"=>$QuestionInfo['author_id']?$QuestionInfo['author_id']:$Question['UserId'],
															"title"=>strip_tags($Question['QuestionContent']),
															"description"=>strip_tags($Question['QuestionContent']),
															"time"=>time(),
															"ip"=>$Question['IP'],
															"pid"=>$QuestionDetail['QuestionId'],
															"comment"=>serialize($Comment));																
										//添加提问记录
										$SubQuestion = $this->oQuestion->addQuestion($QuestionInfo);
										//添加成功
										if($SubQuestion)
										{
											$result = array('return'=>1,'QuestionId'=>$SubQuestion,'comment'=>"提问成功！");
										}
										else
										{
											$result = array('return'=>0,'comment'=>"提问失败！");	
										}

									}
									//如果Cookie验证痛过
									elseif($CookieAuthor)
									{
										//获取主问记录已继承追问数据
										$QuestionInfo = $this->oQuestion->getQuestion($QuestionDetail['QuestionId'],'author,author_id,qtype,cid,gameid,game_name,areaid,area_name,serverid,server_name,operatorid,operator_name');
										$Comment['OS'] = $Question['OS'];
										$Comment['Browser'] = $Question['Browser'];
										$QuestionInfo = array(
															"cid"=> $QuestionInfo['cid'],
															"qtype"=> $QuestionInfo['qtype'],
															"gameid" => $QuestionInfo['gameid'],
															"game_name" => $QuestionInfo['game_name'],
															"operatorid" => $QuestionInfo['operatorid'],
															"operator_name" => $QuestionInfo['operator_name'],
															"areaid" => $QuestionInfo['areaid'],
															"area_name" => $QuestionInfo['area_name'],
															"serverid" => $QuestionInfo['serverid'],
															"server_name" => $QuestionInfo['server_name'],
															"author"=>$QuestionInfo['author'],
															"author_id"=>$QuestionInfo['author_id']?$QuestionInfo['author_id']:$Question['UserId'],
															"description"=>strip_tags($Question['QuestionContent']),
															"time"=>time(),
															"ip"=>$Question['IP'],
															"pid"=>$QuestionDetail['QuestionId'],
															"comment"=>serialize($Comment));
										//添加提问记录
										$SubQuestion = $this->oQuestion->addQuestion($QuestionInfo);
										//添加成功
										if($SubQuestion)
										{
											$result = array('return'=>1,'QuestionId'=>$SubQuestion,'comment'=>"提问成功！");
										}
										else
										{
											$result = array('return'=>0,'comment'=>"提问失败！");
										}
									}
									else
									{
										$result = array('return'=>0,'comment'=>"您不可以对不是自己的问题进行追问");
									}
								}
								else
								{
									$result = array('return'=>0,'comment'=>"对不起，您提问的太频繁了，请稍候再进行提问！");	
								}						
							}
							else
							{
								$result = array('return'=>0,'comment'=>"您的上一次提问还没有被回答，不能继续追问！");
							}
						}
						else
						{
							$result = array('return'=>0,'comment'=>"无此问题！");
						}
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"无此分类");
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
		//如果追问成功
		if($result['return'] == 1)
		{
			//问题类型为咨询|建议
			if($Question['QuestionType']=="question")
			{
				//获取封装完毕的问题详情
				$QuestionDetail = $this->oQuestion->ProcessQuestionDetail($Question);
				//写入缓存
				$oMenCache -> set('QuestionDetail_'.$Question['QuestionType']."_".$Question['QuestionId'],json_encode($QuestionDetail),300);
			}
		}
		echo json_encode($result);
	}
	/**
	 *问题评价
	 */
	public function questionAssessAction()
	{
		//基础元素，必须参与验证
		$Question['QuestionId'] = abs(intval($this->request->QuestionId));
		$Question['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$Question['UserName'] = urldecode(trim($this->request->UserName));
		$Question['QuickAsk'] = urldecode(trim($this->request->QuickAsk));
		$Question['Assess'] = abs(intval($this->request->Assess));
		$Question['Time'] = abs(intval($this->request->Time));
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($Question,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Question['Time']-time())<=600)
			{
				//如果评价为有效数值
				if(in_array($Question['Assess'],array(1,2)))
				{

					//问题类型为咨询|建议
					if($Question['QuestionType']=="question")
					{						
						//获取问题内容
						$QuestionInfo = $this->oQuestion->getQuestion($Question['QuestionId'],"id,author,is_pj,comment");
						//如果获取到问题
						if($QuestionInfo['id'])
						{
							//解包备注信息
							$Comment = unserialize($QuestionInfo['comment']);
							//如果不限制评价次数 或者，评价次数限制大于当前评价次数
							if($Setting['limit_assess_num']==0 || $Setting['limit_assess_num']>$Comment['assess_num'])
							{
								//如果问题未评价 或者 评价为不满意
								if(($QuestionInfo['is_pj'] == 0) || ($QuestionInfo['is_pj'] == 2))
								{
									//cookie验证
									$CookieAuthor = $this->oUser->authorUserByCookie($Question['QuickAsk'],'zx,jy',$Question['QuestionId']);
									//用户名验证
									$UserAuthor = $this->oUser->authorUserByName($Question['UserName'],$QuestionInfo['author']);
									//如果用户验证痛过
									if($UserAuthor)
									{
										$AssessResult = $this->oQuestion->AssessQuestion($Question['QuestionId'],$Question['Assess']);
									}
									//如果cookie验证痛过
									if($CookieAuthor)
									{
										$AssessResult = $this->oQuestion->AssessQuestion($Question['QuestionId'],$Question['Assess']);
									}
									//如果评价成功
									if($AssessResult)
									{
										//如果不限制评价次数 或者，评价次数限制大于当前评价次数
										if($Setting['limit_assess_num']==0 || $Setting['limit_assess_num']>($Comment['assess_num']+1))
										{
											//如果评价为不满意
											if($Question['Assess']==2)
											{
												//可以继续评价
												$Assess = 1;
											}
											else
											{
												//不可继续评价
												$Assess = 0;
											}
										}
										else
										{
											//不可继续评价
											$Assess = 0;
										}
										$result = array('return'=>1,'Assess'=>$Assess,'comment'=>"评价成功");
										if($Question['Assess']==1)
										{
											$LogText = "用户评价：满意";
										}
										elseif($Question['Assess']==2)
										{
											$LogText = "用户评价：不满意";
										}
										$this->oQuestion->addSystemLog($Question['QuestionId'],$QuestionInfo['author'],$QuestionInfo['author'],19,$LogText);
									}
									else
									{
										$result = array('return'=>0,'comment'=>"评价失败");	
									}
								}
								else
								{
									$result = array('return'=>1,'Assess'=>0,'comment'=>"评价成功");
								}
							}
							else
							{
								$result = array('return'=>1,'Assess'=>0,'comment'=>"评价成功");	
							}						
						}
						else
						{
							$result = array('return'=>0,'comment'=>"无此问题");
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"无此分类");	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"评价选项出错");	
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
		//如果评价成功
		if($result['return'] == 1)
		{
			//问题类型为咨询|建议
			if($Question['QuestionType']=="question")
			{
				//获取封装完毕的问题详情
				$QuestionDetail = $this->oQuestion->ProcessQuestionDetail($Question);
				//写入缓存
				$oMenCache -> set('QuestionDetail_'.$Question['QuestionType']."_".$Question['QuestionId'],json_encode($QuestionDetail),300);				
			}			
		}
		echo json_encode($result);
	}
	/**
	 *问题评价
	 */
	public function questionRevokeAction()
	{
		//基础元素，必须参与验证
		$Question['QuestionId'] = abs(intval($this->request->QuestionId));
		$Question['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$Question['UserName'] = urldecode(trim($this->request->UserName));
		$Question['QuickAsk'] = urldecode(trim($this->request->QuickAsk));
		$Question['RevokeReason'] = urldecode(trim($this->request->RevokeReason));
		$Question['IP'] = urldecode(trim($this->request->IP));
		$Question['Time'] = abs(intval($this->request->Time));
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($Question,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Question['Time']-time())<=600)
			{
				//问题类型为咨询|建议
				if($Question['QuestionType']=="complain")
				{
					//如果撤销需要理由
					if($Setting['complainReasonSwitch']==1)
					{
						//如果未输入理由
						if($Question['RevokeReason'] == "")
						{
							$RevokeReason = $this->oComplain->getRevokeReason();
							$result = array('return'=>2,'RevokeReason'=>$RevokeReason,'comment'=>'请选择一个撤销理由');
							//不可以继续处理，需要返回
							$process = 0;
						}
						else
						{
							//可以继续处理
							$process = 1;
						}
					}
					else
					{
						//可以继续处理
						$process = 1;
						//初始化撤销理由
						$Question['RevokeReason'] = "无理由撤销";
					}
					//如果可以继续处理
					if($process == 1)
					{
						//获取问题内容
						$QuestionInfo = $this->oComplain->getComplain($Question['QuestionId'],'id,status,sync,author');
						//如果获取到问题
						if($QuestionInfo['id'])
						{						
							//如果投诉状态为 已撤销
							if($QuestionInfo['status'] == 2)
							{
								$result = array('return'=>1,'comment'=>"撤销成功");	
							}
							else
							{
								//如果投诉单已经同步到投诉
								if($QuestionInfo['sync'] == 1)
								{
									//如果问题状态未初始创建 或者 开关允许任何状态撤销
									if($QuestionInfo['status'] == 0 || $Setting['complainSwitch'])
									{
										//理由内容过滤
										$replace = array('@'=>'','/'=>'','\\'=>'','|'=>'','、'=>'');
										$Question['RevokeReason'] = strtr($Question['RevokeReason'],$replace);
										//cookie验证
										$CookieAuthor = $this->oUser->authorUserByCookie($Question['QuickAsk'],'ts',$Question['QuestionId']);
										//用户名验证
										$UserAuthor = $this->oUser->authorUserByName($Question['UserName'],$QuestionInfo['author']);	
										//如果用户验证通过 或者 cookie验证痛过
										if($UserAuthor || $CookieAuthor)
										{
											//撤销操作
											$RevokeResult = $this->oComplain->RevokeQuestion($Question);
											//如果撤销成功
											if($RevokeResult)
											{
												$result = array('return'=>1,'comment'=>"撤销成功！");		
											}
											else
											{
												$result = array('return'=>0,'comment'=>"撤销失败！");	
											}
											
										}
										else
										{
											$result = array('return'=>0,'comment'=>"不能对不是自己的单据进行撤销");	
										}
									}
									else
									{
										$result = array('return'=>0,'comment'=>"当前状态无法撤销！");	
									}
								}
								//尚未同步到投诉，无法撤销
								else
								{
									$result = array('return'=>0,'comment'=>"系统忙，请稍后再试！");	
								}
							}
						}
						else
						{
							$result = array('return'=>0,'comment'=>"无此问题");							
						}					
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"无此分类");	
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
		//如果评价成功
		if($result['return'] == 1)
		{
			//问题类型为投诉
			if($Question['QuestionType']=="complain")
			{
				//获取封装完毕的问题详情
				$QuestionDetail = $this->oComplain->ProcessComplainDetail($Question);
				//写入缓存
				$oMenCache -> set('QuestionDetail_'.$Question['QuestionType']."_".$Question['QuestionId'],json_encode($QuestionDetail),300);
			}
		}
		echo json_encode($result);
	}
	/**
	 *问题浏览量更新
	 */
	public function questionViewAction()
	{
		//基础元素，必须参与验证
		$Question['QuestionId'] = abs(intval($this->request->QuestionId));
		$Question['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$Question['QuestionView'] = abs(intval($this->request->QuestionView))?abs(intval($this->request->QuestionView)):1;
		$Question['Time'] = abs(intval($this->request->Time));
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($Question,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Question['Time']-time())<=600)
			{
				//问题类型为咨询|建议
				if($Question['QuestionType']=="complain")
				{
					$this->oComplain->updateComplain($Question['QuestionId'],array('view'=>"_view+".$Question['QuestionView']));
					$result = array('return'=>1,'comment'=>"更新成功");	
				}
				elseif($Question['QuestionType']=="question")
				{
					$this->oQuestion->updateQuestion($Question['QuestionId'],array('views'=>"_views+".$Question['QuestionView']));
					$result = array('return'=>1,'comment'=>"更新成功");
				}
				else
				{
					$result = array('return'=>0,'comment'=>"无此分类");
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
		//如果更新评价成功
		if($result['return'] == 1)
		{
			$oMenCache = new Base_Cache_Memcache("Complaint");
			//获取封装完毕的问题详情
			$QuestionDetail = $this->oQuestion->ProcessQuestionDetail($Question);
			//写入缓存
			$oMenCache -> set('QuestionDetail_'.$Question['QuestionType']."_".$Question['QuestionId'],json_encode($QuestionDetail),300);
		}	
		echo json_encode($result);
	}
	/**
	 *重建问题缓存
	 */
	public function rebuildQuestionDetailAction()
	{
		//基础元素，必须参与验证
		$Question['QuestionId'] = abs(intval($this->request->QuestionId));
		$Question['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$Question['Time'] = abs(intval($this->request->Time));
		$oMenCache = new Base_Cache_Memcache("Complaint");

		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($Question,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($Question['Time']-time())<=600)
			{
				//问题类型为咨询|建议
				if($Question['QuestionType']=="question")
				{
					//获取封装完毕的问题详情
					$QuestionDetail = $this->oQuestion->ProcessQuestionDetail($Question);
				}
				elseif($Question['QuestionType']=="complain")
				{
					//获取封装完毕的问题详情
					$QuestionDetail = $this->oComplain->ProcessComplainDetail($Question);
				}
				if($QuestionDetail['QuestionId'])
				{
					$oMenCache -> set('QuestionDetail_'.$Question['QuestionType']."_".$Question['QuestionId'],json_encode($QuestionDetail),300);
					$result = array('return'=>1);	
				}
				else
				{
					$result = array('return'=>0,'comment'=>"无此问题");						
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
		echo json_encode($result);
	}
	/**
	 *追问
	 */
	public function rebuildQuestionDetailTestAction()
	{
		//基础元素，必须参与验证
		$Question['QuestionId'] = abs(intval($this->request->QuestionId));
		$Question['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$Question['Time'] = abs(intval($this->request->Time));
		$oMenCache = new Base_Cache_Memcache("Complaint");

		print_R($Question);
		$M = $oMenCache -> get('QuestionDetail_'.$Question['QuestionType']."_".$Question['QuestionId']);
		$QuestionDetail = json_decode($M,true);
		echo "QuestionDetail:"."<br>";
		print_R($QuestionDetail);
	}


	
}
