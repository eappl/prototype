<?php
/**
 * 通用登录控制层
 * $Id: ResearchController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class ResearchController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oResearch;
	protected $oQuestion;
	protected $oUser;


	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();

		$this->oUser = new Lm_User();
		$this->oResearch = new Config_Research();
		$this->oQuestion = new Config_Research_Question();
	}

	/**
	 *获取调研的内容
	 */
	public function getResearchAction()
	{
		//基础元素，必须参与验证
		$User['ResearchId'] = abs(intval($this->request->ResearchId));
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
			if($User['ResearchId'])
			{
				//验证时间戳，时差超过600秒即认为非法
				if(abs($User['Time']-time())<=600)
				{
		 			//查询调研信息
					$ResearchInfo = $this->oResearch->getRow($User['ResearchId']);
					if($ResearchInfo['ResearchId'])
					{
						$QuestionInfo = $this->oQuestion->getAll($User['ResearchId']);					
			 			$result = array('return'=>1,'ResearchInfo'=>$ResearchInfo,'QuestionInfo'=>$QuestionInfo);							 	
					}
					else
					{
			 			$result = array('return'=>2,'comment'=>"无此调研");						 	
					}
				}
				else
				{
					$result = array('return'=>0,'comment'=>"时间有误");	
				}
			}
			else
			{
				$result = array('return'=>2,'comment'=>"请输入调研ID");	
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
	 *获取调研的内容
	 */
	public function answerResearchAction()
	{
		//基础元素，必须参与验证
		$User['UserId'] = abs(intval($this->request->UserId));
		$User['ResearchId'] = abs(intval($this->request->ResearchId));
		$User['AnswerTime'] = abs(intval($this->request->AnswerTime));
		$User['Answer'] = urldecode($this->request->Answer);		
		$User['ReturnType'] = $this->request->ReturnType?$this->request->ReturnType:2;
        
		//URL验证码
		$sign = $this->request->sign;
		//私钥，以后要移开到数据库存储
		$P_Sign = 'lm';
		
		$sign_to_check = base_common::check_sign($User,$P_Sign);
		//不参与验证的元素
        
		//验证URL是否来自可信的发信方
		if($sign_to_check==$sign)
		{
			if($User['Answer'])
			{
				//验证用户名有效性
				if($User['ResearchId'])
				{
					//验证时间戳，时差超过600秒即认为非法
					if(abs($User['AnswerTime']-time())<=600)
					{
			 			//查询调研信息
						$ResearchInfo = $this->oResearch->getRow($User['ResearchId']);
						if($ResearchInfo['ResearchId'])
						{
				 			//查询用户
							$UserInfo = $this->oUser->GetUserById($User['UserId']);
							if($UserInfo['UserId'])
							{
								$Answer = json_decode($User['Answer'],true);
								$QuestionInfo = $this->oQuestion->getAll($User['ResearchId']);					
								if(isset($Answer))
								{
									foreach($Answer[$User['ResearchId']] as $QuestionId => $AnswerList)
									{
										if(isset($QuestionInfo[$QuestionId]))
										{
											$AnswerArr = array('UserId'=>$User['UserId'],'QuestionId'=>$QuestionId,'AnswerTime'=>$User['AnswerTime'],
											'Answer'=>implode('|',$AnswerList));
											$log = $this->oQuestion->InsertAnswerLog($AnswerArr,$User['ResearchId']);
										}	
									}	
								}
							}
							else
							{
					 			$result = array('return'=>2,'comment'=>"无此用户");						 	
							}							 	
						}
						else
						{
				 			$result = array('return'=>2,'comment'=>"无此调研");						 	
						}
					}
					else
					{
						$result = array('return'=>0,'comment'=>"时间有误");	
					}
				}
				else
				{
					$result = array('return'=>2,'comment'=>"请输入调研ID");	
				}
			}
			else
			{
				$result = array('return'=>0,'comment'=>"请输入回答内容");	
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
