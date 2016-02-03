<?php
/**
 * 用户控制层
 * $Id: BroadCastController.php 425 2012-12-14 06:14:59Z chenxiaodong $
 */

class UserController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oComplain;
	protected $oQuestion;
	protected $oCategory;
	protected $oQtype;

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
	}
	/**
	 *获取当前登录用户的服务记录数量
	 */
	public function serviceLogLoggedAction()
	{
		//基础元素，必须参与验证
		$User['UserName'] = urldecode(trim($this->request->UserName));
		$User['Time'] = abs(intval($this->request->Time));
		$User['NewCount'] = abs(intval($this->request->NewCount))?abs(intval($this->request->NewCount)):3;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$QuestionStatusList = $this->config->QuestionStatusList;
		$QuestionTypeList = $this->config->QuestionTypeList;
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($User['Time']-time())<=600)
			{
				if($User['UserName'])
				{
					//初始化结果数组
					$QuestionTypeArr = array('ask','suggest','complain');
					foreach($QuestionTypeArr as $key => $value)
					{
						$CategoryInfo = $this->oCategory->getCategoryByQuestionType($value,'name');
						$ServiceNum[$value] = array('Num'=>0,'Content'=>"我的".$CategoryInfo['name']."记录");
					}
					$NewLog = array();
					//获取咨询数量
					$ServiceNum['ask']['List'] = $this->oQuestion->getServiceQuestionList(array('UserName'=>$User['UserName'],'QuestionType'=>'ask','Parent'=>0,'Revocation'=>0));
					$ServiceNum['ask']['Num'] = count($ServiceNum['ask']['List']);
					//从缓存服务器上获取是否有新未读数据
					$i = 1;
					foreach($ServiceNum['ask']['List'] as $key => $QuestionInfo)
					{
						if($i<=$User['NewCount'])
						{
							$M = $oMenCache->get('fw'.$QuestionInfo['QuestionId']);
							if($M)
							{
								$i++;
								$ServiceNum['ask']['New'] = 1;
							}
						}
						if($QuestionInfo['status']==1)
						{
							$NewLog[$QuestionInfo['time']][] = array('QuestionId'=>$QuestionInfo['QuestionId'],'QuestionType'=>'ask');
						}
					}
					unset($ServiceNum['ask']['List']);
					//获取建议数量
					$ServiceNum['suggest']['List'] = $this->oQuestion->getServiceQuestionList(array('UserName'=>$User['UserName'],'QuestionType'=>'suggest','Parent'=>0,'Revocation'=>0));
					$ServiceNum['suggest']['Num'] = count($ServiceNum['suggest']['List']);
					//从缓存服务器上获取是否有新未读数据
					$i = 1;
					foreach($ServiceNum['suggest']['List'] as $key => $QuestionInfo)
					{
						if($i<=$User['NewCount'])
						{
							$M = $oMenCache->get('fw'.$QuestionInfo['QuestionId']);
							if($M)
							{
								$i++;
								$ServiceNum['suggest']['New'] = 1;
							}
						}
						if($QuestionInfo['status']==1)
						{
							$NewLog[$QuestionInfo['time']][] = array('QuestionId'=>$QuestionInfo['QuestionId'],'QuestionType'=>'suggest');
						}
					}
					unset($ServiceNum['suggest']['List']);
					//获取投诉数量
					$ServiceNum['complain']['List'] = $this->oComplain->getComplainServiceList(array('UserName'=>$User['UserName'],'Public'=>'0,2'));
					$ServiceNum['complain']['Num'] = count($ServiceNum['complain']['List']);
					//从缓存服务器上获取是否有新未读数据
					$i = 1;
					foreach($ServiceNum['complain']['List'] as $key => $QuestionInfo)
					{
						if($i<=$User['NewCount'])
						{
							$M = $oMenCache->get('ts'.$QuestionInfo['QuestionId']);
							if($M)
							{
								$i++;
								$ServiceNum['complain']['New'] = 1;
							}
						}
						if(in_array($QuestionInfo['status'],array(0,4)))
						{
							$NewLog[$QuestionInfo['time']][] = array('QuestionId'=>$QuestionInfo['QuestionId'],'QuestionType'=>'complain');
						}
					}
					unset($ServiceNum['complain']['List']);
					krsort($NewLog);
					$i = 0;
					$NewServiceList = array();
					foreach($NewLog as $Time => $TimeList)
					{
						foreach($TimeList as $key => $Question)
						{
							if($i<$User['NewCount'])
							{
								switch($Question['QuestionType'])
								{
									case "ask":
										$QuestionInfo = $this->oQuestion->getQuestion($Question['QuestionId'],"id,description,time,atime,status,qtype");
										//生成问题链接
										$QuestionInfo['QuestionUrl'] = $this->oQuestion->getQuestionLink($QuestionInfo['id'],"question");
										//复制问题分类
										$QuestionInfo['QuestionType'] = $Question['QuestionType'];
										//生成问题状态
										if($QuestionInfo['status']==1)
										{
											$QuestionInfo['QuestionStatus'] = 1;	
										}
										else
										{
											$QuestionInfo['QuestionStatus'] = 2;	
										}
										$NewServiceList[] = $QuestionInfo;
										break;
									case "suggest":
										$QuestionInfo = $this->oQuestion->getQuestion($Question['QuestionId'],"id,description,time,atime,status,qtype");
										//生成问题链接
										$QuestionInfo['QuestionUrl'] = $this->oQuestion->getQuestionLink($QuestionInfo['id'],"question");
										//复制问题分类
										$QuestionInfo['QuestionType'] = $Question['QuestionType'];
										//生成问题状态
										if($QuestionInfo['status']==1)
										{
											$QuestionInfo['QuestionStatus'] = 1;	
										}
										else
										{
											$QuestionInfo['QuestionStatus'] = 2;	
										}
										$NewServiceList[] = $QuestionInfo;
										break;
									case "complain":
										$QuestionInfo = $this->oComplain->getComplain($Question['QuestionId'],"id,description,time,atime,status,qtype");
										//生成问题链接
										$QuestionInfo['QuestionUrl'] = $this->oComplain->getQuestionLink($QuestionInfo['id'],"complain");
										//复制问题分类
										$QuestionInfo['QuestionType'] = $Question['QuestionType'];
										//生成问题状态
										if(in_array($QuestionInfo['status'],array(0,4)))
										{
											$QuestionInfo['QuestionStatus'] = 1;	
										}
										elseif(in_array($QuestionInfo['status'],array(1,3)))
										{
											$QuestionInfo['QuestionStatus'] = 2;	
										}
										if($QuestionInfo['status']==2)
										{
											$QuestionInfo['QuestionStatus'] = 3;	
										}
										$NewServiceList[] = $QuestionInfo;
										break;
								}
							}
							$i++;
						}
					}
					foreach($NewServiceList as $key => $QuestionInfo)
					{
						if(!isset($QtypeList[$QuestionInfo['qtype']]))
						{
							$QtypeList[$QuestionInfo['qtype']] = $this->oQtype->getQtypeById($QuestionInfo['qtype']);
						}									
						$NewServiceList[$key]['Qtype'] = $QtypeList[$QuestionInfo['qtype']]['name'];
						//格式化提问时间
						$NewServiceList[$key]['AddTime'] = date("Y-m-d H:i",$QuestionInfo['time']);
						//格式化回答时间
						$NewServiceList[$key]['AnswerTimeLag'] = ($QuestionInfo['QuestionStatus']>=2 && $QuestionInfo['atime'])?Base_Common::timeLagToText($QuestionInfo['time'],$QuestionInfo['atime']):"-";
						//获取问题状态名称
						$NewServiceList[$key]['QuestionStatusName'] = $QuestionStatusList[$QuestionInfo['QuestionStatus']];
						//格式化问题内容
						$NewServiceList[$key]['Content'] = Base_Common::cutstr($QuestionInfo['description'],14);
						//获取问题分类
						$CategoryInfo = $QuestionTypeList[$QuestionInfo['QuestionType']];
						//获取问题主分类
						$NewServiceList[$key]['QuestionType'] = $CategoryInfo;
						unset($NewServiceList[$key]['description']);
					}
					//服务记录详情页面信息
					$ServiceLogDetail = array('Url'=>'http://sc.5173.com/index.php?question/my_ask.html','Content'=>'查看全部服务记录');
					//入口信息
					$ServiceEntrance = array('Url'=>'http://sc.5173.com/index.php?question/ask_skip.html','Content'=>'我要提问');
					$result = array('return'=>1,'ServiceNum'=>$ServiceNum,'ServiceLogDetail'=>$ServiceLogDetail,'ServiceEntrance'=>$ServiceEntrance,'NewServiceList'=>$NewServiceList);
				}
				else
				{
					$result = array('return'=>0,'comment'=>"请输入用户名");	
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
	 *根据cookie信息获取未登录用户的服务记录数量
	 */
	public function serviceLogUnloggedAction()
	{
		//基础元素，必须参与验证
		$User['QuickAsk'] = urldecode(trim($this->request->QuickAsk));
		$User['Time'] = abs(intval($this->request->Time));
		$User['NewCount'] = abs(intval($this->request->NewCount))?abs(intval($this->request->NewCount)):3;
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$p_sign = '5173';
		
		$sign_to_check = base_common::check_sign($User,$p_sign);
		//不参与验证的元素
		
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$QuestionStatusList = $this->config->QuestionStatusList;
		$QuestionTypeList = $this->config->QuestionTypeList;
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			//验证时间戳，时差超过600秒即认为非法
			if(abs($User['Time']-time())<=600)
			{
				//初始化结果数组
				$QuestionTypeArr = array('ask','suggest','complain');
				foreach($QuestionTypeArr as $key => $value)
				{
					$CategoryInfo = $this->oCategory->getCategoryByQuestionType($value,'name');
					$ServiceNum[$value] = array('Num'=>0,'Content'=>"我的".$CategoryInfo['name']."记录");
				}
				$NewLog = array();
				$ask_type = unserialize(stripslashes($User['QuickAsk']));
				$IdArr = array($ask_type['zx'],$ask_type['jy']);
				foreach($IdArr as $key => $value)
				{
					if(trim($value)=="")
					{
						unset($IdArr[$key]);
					}
				}
				if(count($IdArr))
				{
					//获取咨询数量
					$ServiceNum['ask']['List'] = $this->oQuestion->getServiceQuestionList(array('IdList'=>implode(",",$IdArr),'QuestionType'=>'ask','Parent'=>0,'Revocation'=>0));
					$ServiceNum['ask']['Num'] = count($ServiceNum['ask']['List']);
					//从缓存服务器上获取是否有新未读数据
					foreach($ServiceNum['ask']['List'] as $key => $QuestionInfo)
					{
						$M = $oMenCache -> get('fw'.$QuestionInfo['QuestionId']);
						if($M)
						{
							$ServiceNum['ask']['New'] = 1;
						}
						//echo $QuestionInfo['QuestionId']."-".$QuestionInfo['status']."<br>";
						if($QuestionInfo['status']==1)
						{
							$NewLog[$QuestionInfo['time']][] = array('QuestionId'=>$QuestionInfo['QuestionId'],'QuestionType'=>'ask');
						}
					}
					unset($ServiceNum['ask']['List']);
					//获取建议数量
					$ServiceNum['suggest']['List'] = $this->oQuestion->getServiceQuestionList(array('IdList'=>implode(",",$IdArr),'QuestionType'=>'suggest','Parent'=>0,'Revocation'=>0));
					$ServiceNum['suggest']['Num'] = count($ServiceNum['suggest']['List']);
					//从缓存服务器上获取是否有新未读数据
					foreach($ServiceNum['suggest']['List'] as $key => $QuestionInfo)
					{
						$M = $oMenCache -> get('fw'.$QuestionInfo['QuestionId']);
						if($M)
						{
							$ServiceNum['suggest']['New'] = 1;
						}
						if($QuestionInfo['status']==1)
						{
							$NewLog[$QuestionInfo['time']][] = array('QuestionId'=>$QuestionInfo['QuestionId'],'QuestionType'=>'suggest');
						}
					}
					unset($ServiceNum['suggest']['List']);					
				}
				else
				{
					$ServiceNum['ask']['Num'] = 0;
					$ServiceNum['suggest']['Num'] = 0;					
				}
				$IdList = $ask_type['ts'];
				if(count(explode(',',$IdList))>=1 && $IdList!= "")
				{
					//获取投诉数量
					$ServiceNum['complain']['List'] = $this->oComplain->getComplainServiceList(array('IdList'=>$IdList,'Public'=>'0,2'));
					$ServiceNum['complain']['Num'] = count($ServiceNum['complain']['List']);					
					//从缓存服务器上获取是否有新未读数据
					foreach($ServiceNum['complain']['List'] as $key => $QuestionInfo)
					{
						$M = $oMenCache -> get('ts'.$QuestionInfo['QuestionId']);
						if($M)
						{
							$ServiceNum['complain']['New'] = 1;
						}
						if(in_array($QuestionInfo['status'],array(0,4)))
						{
							$NewLog[$QuestionInfo['time']][] = array('QuestionId'=>$QuestionInfo['QuestionId'],'QuestionType'=>'complain');
						}
					}
					unset($ServiceNum['complain']['List']);
				}
				else
				{
					$ServiceNum['complain']['Num'] = 0;
				}				
				krsort($NewLog);
				$i = 0;
				$NewServiceList = array();
				foreach($NewLog as $Time => $TimeList)
				{
					foreach($TimeList as $key => $Question)
					{
						if($i<$User['NewCount'])
						{
							switch($Question['QuestionType'])
							{
								case "ask":
									$QuestionInfo = $this->oQuestion->getQuestion($Question['QuestionId'],"id,description,time,atime,status,qtype");
									//生成问题链接
									$QuestionInfo['QuestionUrl'] = $this->oQuestion->getQuestionLink($QuestionInfo['id'],"question");
									//复制问题分类
									$QuestionInfo['QuestionType'] = $Question['QuestionType'];
									//生成问题状态
									if($QuestionInfo['status']==1)
									{
										$QuestionInfo['QuestionStatus'] = 1;	
									}
									else
									{
										$QuestionInfo['QuestionStatus'] = 2;	
									}
									$NewServiceList[] = $QuestionInfo;
									break;
								case "suggest":
									$QuestionInfo = $this->oQuestion->getQuestion($Question['QuestionId'],"id,description,time,atime,status,qtype");
									//生成问题链接
									$QuestionInfo['QuestionUrl'] = $this->oQuestion->getQuestionLink($QuestionInfo['id'],"question");
									//复制问题分类
									$QuestionInfo['QuestionType'] = $Question['QuestionType'];
									//生成问题状态
									if($QuestionInfo['status']==1)
									{
										$QuestionInfo['QuestionStatus'] = 1;	
									}
									else
									{
										$QuestionInfo['QuestionStatus'] = 2;	
									}
									$NewServiceList[] = $QuestionInfo;
									break;
								case "complain":
									$QuestionInfo = $this->oComplain->getComplain($Question['QuestionId'],"id,description,time,atime,status,qtype");
									//生成问题链接
									$QuestionInfo['QuestionUrl'] = $this->oComplain->getQuestionLink($QuestionInfo['id'],"complain");
									//复制问题分类
									$QuestionInfo['QuestionType'] = $Question['QuestionType'];
									//生成问题状态
									if(in_array($QuestionInfo['status'],array(0,4)))
									{
										$QuestionInfo['QuestionStatus'] = 1;	
									}
									elseif(in_array($QuestionInfo['status'],array(1,3)))
									{
										$QuestionInfo['QuestionStatus'] = 2;	
									}
									if($QuestionInfo['status']==2)
									{
										$QuestionInfo['QuestionStatus'] = 3;	
									}
									$NewServiceList[] = $QuestionInfo;
									break;
							}
						}
						$i++;
					}
				}				
				foreach($NewServiceList as $key => $QuestionInfo)
				{
					if(!isset($QtypeList[$QuestionInfo['qtype']]))
					{
						$QtypeList[$QuestionInfo['qtype']] = $this->oQtype->getQtypeById($QuestionInfo['qtype']);
					}									
					$NewServiceList[$key]['Qtype'] = $QtypeList[$QuestionInfo['qtype']]['name'];
					//格式化提问时间
					$NewServiceList[$key]['AddTime'] = date("Y-m-d H:i",$QuestionInfo['time']);
					//格式化回答时间
					$NewServiceList[$key]['AnswerTimeLag'] = ($QuestionInfo['QuestionStatus']>=2 && $QuestionInfo['atime'])?Base_Common::timeLagToText($QuestionInfo['time'],$QuestionInfo['atime']):"-";
					//获取问题状态名称
					$NewServiceList[$key]['QuestionStatusName'] = $QuestionStatusList[$QuestionInfo['QuestionStatus']];
					//格式化问题内容
					$NewServiceList[$key]['Content'] = Base_Common::cutstr($QuestionInfo['description'],14);
					//获取问题分类
					$CategoryInfo = $QuestionTypeList[$QuestionInfo['QuestionType']];
					//获取问题主分类
					$NewServiceList[$key]['QuestionType'] = $CategoryInfo;
					unset($NewServiceList[$key]['description']);
				}
				//服务记录详情页面信息
				$ServiceLogDetail = array('Url'=>'http://sc.5173.com/index.php?question/my_ask.html','Content'=>'查看全部服务记录');
				//入口信息
				$ServiceEntrance = array('Url'=>'http://sc.5173.com/index.php?question/ask_skip.html','Content'=>'我要提问');
				$result = array('return'=>1,'ServiceNum'=>$ServiceNum,'ServiceLogDetail'=>$ServiceLogDetail,'ServiceEntrance'=>$ServiceEntrance,'NewServiceList'=>$NewServiceList);	
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
