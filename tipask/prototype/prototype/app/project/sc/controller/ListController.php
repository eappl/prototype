<?php
/**
 * 用户控制层
 * $Id: BroadCastController.php 425 2012-12-14 06:14:59Z chenxiaodong $
 */

class ListController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oComplain;
	protected $oQuestion;
	protected $oCategory;
	protected $oQtype;
	protected $oOperator;
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
		$this->oOperator = new Kubao_Operator();
		$this->oUser = new Kubao_User();
	}
	/**
	 *获取日期段的问题数量汇总列表
	 */
	public function questionNumListAction()
	{
		//基础元素，必须参与验证
		$List['Time'] = abs(intval($this->request->Time));
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($List,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($List['Time']-time())<=600)
			{
				//查询当前正在生效的公告列表
				$oMenCache = new Base_Cache_Memcache("Complaint");
				$time = time();
				$AskSuggestStart = date("Y-m-01",strtotime("-1 month",$time));
				$ComplainStart = date("Y-m-01",strtotime("-1 month",$time));
				$End = date("Y-m-d",$time);
				$M = $oMenCache -> get('QuestionNumList_'.$AskSuggestStart.'_'.$ComplainStart.'_'.$End.'_'.$QtypeId);
				if($M)
				{
					$QuestionNumList = json_decode($M,true);
				}
				else
				{					
					$List['EndDate'] = $End;
					$List['StartDate'] = $AskSuggestStart;
					$List['QtypeId'] = 0;
					$List['QuestionType'] = "ask,suggest,complain";
					$QuestionNumList = $this->oQtype->getQuestionNumList($List);	
					$oMenCache -> set('QuestionNumList_'.$AskSuggestStart.'_'.$ComplainStart.'_'.$End.'_'.$QtypeId,json_encode($QuestionNumList),10);
				}
				$result = array('return'=>1,'QuestionNumList'=>$QuestionNumList);	
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
	}	/**
	 *获取日期段的问题数量汇总列表
	 */
	public function questionListAction()
	{
		$QuestionTypeList = $this->config->QuestionTypeList;
		$QuestionStatusList = $this->config->QuestionStatusList;
		//基础元素，必须参与验证
		$List['Time'] = abs(intval($this->request->Time));
		$List['QuestionType'] = trim($this->request->QuestionType);
		$List['QuestionStatus'] = abs(intval($this->request->QuestionStatus));
		$List['QtypeId'] = abs(intval($this->request->QtypeId));
		$List['Page'] = abs(intval($this->request->Page));
		$List['PageSize'] = abs(intval($this->request->PageSize));
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($List,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($List['Time']-time())<=600)
			{
				if(isset($QuestionTypeList[$List['QuestionType']]))
				{
					if($List['QtypeId']>=0)
					{
						$QtypeList = array();
						$QtypeInfo = $this->oQtype->getQtypeById($List['QtypeId']);
						if(($QtypeInfo['id'] && $QtypeInfo['visiable']) || $List['QtypeId']==0)
						{
							$time = time();
							$List['EndDate'] = date("Y-m-d",$time);
							if($List['QuestionType']=="complain")
							{
								$List['StartDate'] = date("Y-m-01",strtotime("-1 month",$time));
							}
							else
							{
								$List['StartDate'] = date("Y-m-01",strtotime("-1 month",$time));	
							}
							//查询当前正在生效的公告列表
							$oMenCache = new Base_Cache_Memcache("Complaint");
							$M = $oMenCache -> get('NewQuestionNumList_'.$List['StartDate'].'_'.$List['EndDate'].'_'.$List['QuestionType'].'_'.$List['QtypeId'].'_'.$List['QuestionStatus'].'_'.$List['Page'].'_'.$List['PageSize']);
							if($M)
							{
								$QuestionList = json_decode($M,true);
							}
							else
							{
								switch($List['QuestionType'])
								{
									//咨询
									case "ask":
										$List['Parent'] = 0;
										$List['Revocation'] = 0;
										$List['hidden'] = 1;
										$QuestionList = $this->oQuestion->getQuestionList($List,"id,description,time,atime,status,qtype");
										foreach($QuestionList['QuestionList'] as $key => $QuestionInfo)
										{
											//生成问题链接
											$QuestionList['QuestionList'][$key]['QuestionUrl'] = $this->oQuestion->getQuestionLink($QuestionInfo['id'],"question");
											//生成问题状态
											
											if($QuestionInfo['status']==1)
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 1;	
											}
											else
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 2;	
											}
										}
										break;
									//建议
									case "suggest":
										$List['Parent'] = 0;
										$List['Revocation'] = 0;
										$List['hidden'] = 1;
										$QuestionList = $this->oQuestion->getQuestionList($List,"id,description,time,atime,status,qtype");
										foreach($QuestionList['QuestionList'] as $key => $QuestionInfo)
										{
											//生成问题链接
											$QuestionList['QuestionList'][$key]['QuestionUrl'] = $this->oQuestion->getQuestionLink($QuestionInfo['id'],"question");
											//生成问题状态
											if($QuestionInfo['status']==1)
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 1;	
											}
											else
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 2;	
											}
										}
										break;
									//投诉
									case "complain":
										$List['Public'] = 0;
										$QuestionList = $this->oComplain->getComplainList($List,"id,description,time,atime,status,qtype");
										foreach($QuestionList['QuestionList'] as $key => $QuestionInfo)
										{
											//生成问题链接
											$QuestionList['QuestionList'][$key]['QuestionUrl'] = $this->oComplain->getQuestionLink($QuestionInfo['id'],"complain");
											//生成问题状态
											if(in_array($QuestionInfo['status'],array(0,4)))
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 1;	
											}
											elseif(in_array($QuestionInfo['status'],array(1,3)))
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 2;	
											}
											if($QuestionInfo['status']==2)
											{
												$QuestionList['QuestionList'][$key]['QuestionStatus'] = 3;	
											}
										}
										break;
								}
								$QtypeList = array();
								foreach($QuestionList['QuestionList'] as $key => $QuestionInfo)
								{
									//获取问题分类
									$CategoryInfo = $this->oCategory->getCategoryByQuestionType($List['QuestionType'],'name');
									//获取问题主分类
									$QuestionList['QuestionList'][$key]['QuestionType'] = $CategoryInfo['name'];
									if(!isset($QtypeList[$QuestionInfo['qtype']]))
									{
										$QtypeList[$QuestionInfo['qtype']] = $this->oQtype->getQtypeById($QuestionInfo['qtype']);
									}									
									$QuestionList['QuestionList'][$key]['Qtype'] = $QtypeList[$QuestionInfo['qtype']]['name'];
									//格式化提问时间
									$QuestionList['QuestionList'][$key]['AddTime'] = date("Y-m-d H:i",$QuestionInfo['time']);
									//格式化回答时间
									$QuestionList['QuestionList'][$key]['AnswerTimeLag'] = ($QuestionInfo['QuestionStatus']>=2 && $QuestionInfo['atime'])?Base_Common::timeLagToText($QuestionInfo['time'],$QuestionInfo['atime']):"-";
									//获取问题状态名称
									$QuestionList['QuestionList'][$key]['QuestionStatusName'] = $QuestionStatusList[$QuestionInfo['QuestionStatus']];
									//格式化问题内容
									$QuestionList['QuestionList'][$key]['Content'] = Base_Common::cutstr($QuestionInfo['description'],14);
									unset($QuestionInfo['description']);
								}
								$oMenCache -> set('NewQuestionNumList_'.$List['StartDate'].'_'.$List['EndDate'].'_'.$List['QuestionType'].'_'.$List['QtypeId'].'_'.$List['QuestionStatus'].'_'.$List['Page'].'_'.$List['PageSize'],json_encode($QuestionList),10);
							}
							$result = array('return'=>1,'QuestionList'=>$QuestionList);											
						}
						else
						{
							$result = array('return'=>0,'comment'=>"无此问题主分类");	
						}
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"无此问题分类");	
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
	 *获取问题详情
	 */
	public function questionDetailAction()
	{
		//基础元素，必须参与验证
		$List['QuestionId'] = abs(intval($this->request->QuestionId));
		$List['QuestionType'] = urldecode(trim($this->request->QuestionType));
		$List['UserName'] = urldecode(trim($this->request->UserName));
		$List['QuickAsk'] = urldecode(trim($this->request->QuickAsk));
		$List['Time'] = abs(intval($this->request->Time));
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($List,$p_sign);
		//不参与验证的元素
		
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($List['Time']-time()) <=600)
			{
				//问题类型为咨询|建议
				if($List['QuestionType']=="question")
				{					
					//获取缓存
					$M = $oMenCache -> get('QuestionDetail_'.$List['QuestionType']."_".$List['QuestionId']);
					if($M)
					{
						$QuestionDetail = json_decode($M,true);
						//如果缓存内的数据不正确
						if(!$QuestionDetail['QuestionId'])
						{
							//获取封装完毕的问题详情
							$QuestionDetail = $this->oQuestion->ProcessQuestionDetail($List);						
						}
					}
					else
					{					
						//获取封装完毕的问题详情
						$QuestionDetail = $this->oQuestion->ProcessQuestionDetail($List);
					}
					if($QuestionDetail['QuestionId'])
					{
						//写入缓存
						$oMenCache -> set('QuestionDetail_'.$List['QuestionType']."_".$List['QuestionId'],json_encode($QuestionDetail),300);
						//如果已经被转换分类
						if($QuestionDetail['Transformed'] == 1)
						{
							//获取转换后的分类信息
							$CategoryInfo = $this->oCategory->getCategoryByQuestionType($QuestionDetail['QuestionType']);
							//提示跳转
							$result = array('return'=>2,'action'=>$this->oQuestion->getQuestionLink($QuestionDetail['QuestionId'],$QuestionDetail['QuestionType']),'comment'=>"此问题已经被转换为".$CategoryInfo['name']);							
						}
						else
						{
							//cookie验证
							$CookieAuthor = $this->oUser->authorUserByCookie($List['QuickAsk'],'zx,jy',$QuestionDetail['QuestionId']);
							//用户名验证
							$UserAuthor = $this->oUser->authorUserByName($List['UserName'],$QuestionDetail['AuthorName']);
							//用户名验证不通过 并且 COOKIE验证不通过
							if(!$UserAuthor)
							{
								if(!$CookieAuthor)
								{
									$QuestionDetail['AuthorName'] = Base_Common::cutstr($QuestionDetail['AuthorName'],2,'**');		
								}
							}
							//如果问题不是隐私状态
							if($QuestionDetail['Hidden']==1)
							{							
								//登陆用户 并且 账号不一致
								if(!$UserAuthor)
								{
									//不显示附件信息
									$QuestionDetail['QuestionAttatch'] = "";
									//如果浏览器记录中无该问题提问记录
									if(!$CookieAuthor)
									{
										//取消追问资格
										$QuestionDetail['SubQuestion'] = 0;
										//取消评价资格
										$QuestionDetail['Assess'] = 0;
										//不显示评价
										unset($QuestionDetail['AssessStatus']);
									}
								}
								$result = array('return'=>1,'QuestionDetail'=>$QuestionDetail);
								//缓存中清除新处理的问题标记
								if($CookieAuthor || $UserAuthor)
								{									
									//缓存中清除新处理的问题标记
									$oMenCache -> remove('fw'.$List['QuestionId']);
								}
							}
							//问题为隐私
							else
							{
								//登陆用户 并且 账号不一致
								if(!$UserAuthor)
								{
									//不显示附件信息
									$QuestionDetail['QuestionAttatch'] = "";
									//如果浏览器记录中无该问题提问记录
									if(!$CookieAuthor)
									{
										//取消追问资格
										$QuestionDetail['SubQuestion'] = 0;
										//取消评价资格
										$QuestionDetail['Assess'] = 0;
										//不显示评价
										unset($QuestionDetail['AssessStatus']);
									}
								}
								$result = array('return'=>1,'QuestionDetail'=>$QuestionDetail);	
								//如果cookie验证通过 或者 用户验证通过
								if($CookieAuthor || $UserAuthor)
								{									
									//缓存中清除新处理的问题标记
									$oMenCache -> remove('fw'.$List['QuestionId']);
								}
								//引导用户登录
								else
								{
									$result = array('return'=>2,'action'=>'login','comment'=>"此问题的状态为：用户自己可见，请登录");
								}
							}	
						}	
					}
					else
					{
						$result = array('return'=>0,'comment'=>"无此问题");	
					}	
				}			
				//问题类型为投诉
				elseif($List['QuestionType']=="complain")
				{
					//获取缓存
					$M = $oMenCache -> get('QuestionDetail_'.$List['QuestionType']."_".$List['QuestionId']);
					if($M)
					{
						$QuestionDetail = json_decode($M,true);						
						//如果缓存内的数据不正确
						if(!$QuestionDetail['QuestionId'])
						{
							//获取封装完毕的问题详情
							$QuestionDetail = $this->oComplain->ProcessComplainDetail($List);						
						}
					}
					else
					{					
						//获取封装完毕的问题详情
						$QuestionDetail = $this->oComplain->ProcessComplainDetail($List);
					}
					if($QuestionDetail['QuestionId'])
					{
						//写入缓存
						$oMenCache -> set('QuestionDetail_'.$List['QuestionType']."_".$List['QuestionId'],json_encode($QuestionDetail),300);
						//如果已经被转换分类
						if($QuestionDetail['Transformed'] == 1)
						{
							//如果问题被转换为咨询/建议
							if(in_array($QuestionDetail['QuestionType'],array('ask','suggest')))
							{
								//获取转换后的分类信息
								$CategoryInfo = $this->oCategory->getCategoryByQuestionType($QuestionDetail['QuestionType']);
								$result = array('return'=>2,'action'=>$this->oQuestion->getQuestionLink($QuestionDetail['QuestionId'],"question"),'comment'=>"此问题已经被转换为".$CategoryInfo['name']);
							}
							else
							{
								$result = array('return'=>0,'comment'=>"无此问题");	
							}					
						}
						else
						{
							//cookie验证
							$CookieAuthor = $this->oUser->authorUserByCookie($List['QuickAsk'],'ts',$QuestionDetail['QuestionId']);
							//用户名验证
							$UserAuthor = $this->oUser->authorUserByName($List['UserName'],$QuestionDetail['AuthorName']);
							//如果问题被设置为隐藏
							if($QuestionDetail['Hidden']==1)
							{							
								$result = array('return'=>0,'private'=>1,'comment'=>"此问题的状态为：隐藏");
							}								
							else
							{
								//用户名验证不通过 并且 COOKIE验证不通过
								if(!$UserAuthor)
								{
									if(!$CookieAuthor)
									{
										$QuestionDetail['AuthorName'] = Base_Common::cutstr($QuestionDetail['AuthorName'],2,'**');		
									}
								}
								//如果问题被设置为公开 或 cookie验证通过 或 用户名验证通过
								if($QuestionDetail['Hidden']==0 || $CookieAuthor || $UserAuthor)
								{
									//登陆用户 并且 账号不一致 并且 浏览器记录中无该问题提问记录
									if(!$UserAuthor && !$CookieAuthor)
									{
										//取消撤销资格
										$QuestionDetail['Revoke'] = 0;
										//不显示附件信息
										$QuestionDetail['QuestionAttatch'] = "";
										//不显示评价
										unset($QuestionDetail['AssessStatus']);
									}
									$result = array('return'=>1,'QuestionDetail'=>$QuestionDetail);	
									//缓存中清除新处理的问题标记
									$oMenCache -> remove('ts'.$List['QuestionId']);											
								}
								else
								{
									$result = array('return'=>2,'action'=>'login','comment'=>"此问题的状态为：用户自己可见，请登录");
								}									
							}					
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
				$result = array('return'=>0,'comment'=>"时间有误");	
			}
		}
		else
		{
			$result = array('return'=>0,'comment'=>"验证失败,请检查URL");	
		}
		echo json_encode($result);
	}	
}