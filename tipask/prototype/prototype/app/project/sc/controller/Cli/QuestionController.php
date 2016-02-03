<?php
class Cli_QuestionController extends Base_Controller_Action{
    
	public function init()
	{
		parent::init();
		$this->oQuestion = new Kubao_Question();
		$this->oCommon = new Kubao_Common();
		$this->oOperator = new Kubao_Operator();
	}
    public function applyAction()
	{
		$this->QuestionApply();
		$this->QuestionApply_Add();
	}
    public function applyTimeoutAction()
	{
		$this->QuestionApply_Timeout();
		$this->QuestionApply_Timeout_Add();
	}
	//分单循环
    public function QuestionApply()
    {
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",2,"系统首问自动分单开始");//系统操作日志
		sleep(1);
		//获取可接单的客服列表
        $AcceptableOperatorList = $this->oOperator->getAcceptableOperator(0);
		//根据客服接单情况，获取指定数量的单子
		$unAppliedQuestionList = $this->oQuestion->getUnAppliedQuestionList($AcceptableOperatorList['totalAcceptable'],0);
		//如果有取到单子
		$success = 0;
		if(is_array($unAppliedQuestionList['QuestionList']))
    	{               
			//依次尝试分单
        	foreach($unAppliedQuestionList['QuestionList'] as $key => $QuestionInfo)
        	{
				//$AcceptableOperator = $this->oOperator->getMaxAcceptableOperator($AcceptableOperatorList,-1);
				$AcceptableOperator = $this->oOperator->getMaxAcceptableOperator($AcceptableOperatorList,$QuestionInfo['cid1']);
				if($AcceptableOperator['operator']=='')
				{
					$flag = 0;
					$AcceptableOperator = $this->oOperator->getMaxAcceptableOperator($AcceptableOperatorList);	
				}
				else
				{
					$flag = 1;
				}
				if($AcceptableOperator['operator'] != "")
				{
					//分单结果
					$Apply = $this->oQuestion->ApplyToOperator($QuestionInfo['id'],$AcceptableOperator['operator']);
					$success ++;
					echo "apply:".intval($Apply)."\n";
					//不论是否成功，本轮次不再对此单进行分单
					unset($unAppliedQuestionList['QuestionList'][$key]);
					if($Apply)
					{
						//写日志
						if($flag==1)
						{
							$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$AcceptableOperator['operator'],2,'系统依据分类规则分单给了'.$AcceptableOperator['operator']);//系统操作日志
						}
						else
						{
							$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$AcceptableOperator['operator'],2,'系统分单给了'.$AcceptableOperator['operator']);//系统操作日志
						}
						//更新目前的数量
						$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['handling']++;
						$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last']--;
						$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last_receive'] = time();
						//$AcceptableOperatorList['last_accept'] = $AcceptableOperatorList['operator'][$AcceptableOperator['operator']];
						$AcceptableOperatorList['last_accept']['operator'] = $AcceptableOperator['operator'];
					}
				}
        	}
        }
        else
        {
			//没有取到列表
			echo "no job\n";
        }
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",2,"本次共分单".$success."条");//系统操作日志
    }
    //追问分单循环
    function QuestionApply_Add()
    {
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",2,"系统追问自动分单开始");//系统操作日志
		sleep(1);
		//用以累加成功分单的数量，这里获取的单量可能大于最大可接单量
   		$success = 0;
        //获取可接单的客服列表
        $AcceptableOperatorList = $this->oOperator->getAcceptableOperator(1);
		//根据客服接单情况，获取指定数量的单子
    	$unAppliedQuestionList = $this->oQuestion->getUnAppliedQuestionList(1000,1);
		//如果有取到单子
    	if($AcceptableOperatorList['totalAcceptable']>0)
		{
			if(is_array($unAppliedQuestionList))
			{               
				//依次尝试分单
				foreach($unAppliedQuestionList['QuestionList'] as $key => $QuestionInfo)
				{
					echo $QuestionInfo['id']."-".$QuestionInfo['pid']."\n";
					//获取父级问题
					$ParentQuestionInfo = $this->oQuestion->getQuestion($QuestionInfo['pid'],'id,js_kf');
					//问题存在
					if($ParentQuestionInfo['id'])
					{
						//如果该客服当前可接单则分配给本人
						if($AcceptableOperatorList['operator'][$ParentQuestionInfo['js_kf']]['last']>0)
						{
							$AcceptableOperator = array('operator'=>$ParentQuestionInfo['js_kf']);
						}
						else//否则分配给其他人
						{
							$AcceptableOperator = $this->oOperator->getMaxAcceptableOperator($AcceptableOperatorList);
						}
						if($AcceptableOperator['operator'] != "")
						{
							//分单结果
							$Apply = $this->oQuestion->ApplyToOperator($QuestionInfo['id'],$AcceptableOperator['operator']);
							echo "apply:".intval($Apply)."\n";
							//不论是否成功，本轮次不再对此单进行分单
							unset($unAppliedQuestionList['QuestionList'][$key]);
							if($Apply)
							{
								//写日志
								$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$AcceptableOperator['operator'],2,'系统分单给了'.$AcceptableOperator['operator']);//系统操作日志
								//更新目前的数量
								$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['handling']++;
								$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last']--;
								$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last_receive'] = time();
								//如果成功的单量超过最大单量，则跳出循环提前结束
								$success++;
								if($success >= $AcceptableOperatorList['totalAcceptable'])
								{
									break;
								}
							}
						}
					}
				}
			}
			else
			{
				//没有取到列表
				echo "no job\n";
			}
		}
		else
		{
			//无人能接单
			echo "no one free";
			return;
		}
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",2,"本次共分单".$success."条");//系统操作日志
    }
    //超时首问分单循环
    function QuestionApply_Timeout()
    {
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",2,"系统首问超时自动分单开始");//系统操作日志
		sleep(1);
		$AcceptableOperatorList = $this->oOperator->getAcceptableOperator(0);
		$PostList = $this->oOperator->getAllPost("id,timeout");
		foreach($PostList as $key => $PostInfo)
		{
			if($PostInfo['timeout']>0)
			{
				//$PostInfo['timeout'] = 1;
				$OperatorList = $this->oOperator->getOperatorByPost($PostInfo['id'],$fields = 'login_name');
				if(count($OperatorList)>0)
				{
					//获取超时的首问列表
					$timeoutQuestionList = $this->oQuestion->getAppliedUnAnsweredQuestionList(0,$OperatorList,$Setting['helpReApply'],$PostInfo['timeout'],0);
					//如果有取到单子
					if(is_array($timeoutQuestionList['QuestionList']))
					{
						//依次尝试分单
						foreach($timeoutQuestionList['QuestionList'] as $key2 => $QuestionInfo)
						{
							//取消分单
							$Cancel = $this->oQuestion->ApplyCancel($QuestionInfo['id']);
							echo "Cancel:".intval($Cancel)."\n";
							if($Cancel)
							{
								//写日志
								$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$QuestionInfo['js_kf'],11,'系统取消了分单给'.$QuestionInfo['js_kf'].'的咨询问题，撤单原因：'.$PostInfo['timeout'].'秒超时');//系统操作日志
								//写日志
								//更新目前的数量
								$AcceptableOperatorList['operator'][$QuestionInfo['js_kf']]['handling']--;
								$AcceptableOperatorList['operator'][$QuestionInfo['js_kf']]['last']++;
								//生成一个可接单客服列表副本
								$tmp =  $AcceptableOperatorList;								
								//不分给当前接这个单的客服
								unset($tmp['operator'][$QuestionInfo['js_kf']]);
								//在余下的客服中选取一个
								$AcceptableOperator = $this->oOperator->getMaxAcceptableOperator($tmp);
								if($AcceptableOperator['operator'] != "")
								{
									//分单结果
									$Apply = $this->oQuestion->ApplyToOperator($QuestionInfo['id'],$AcceptableOperator['operator']);
									echo "Apply:".intval($Apply)."\n";
									//不论是否成功，本轮次不再对此单进行分单
									unset($unAppliedQuestionList['QuestionList'][$key]);
									if($Apply)
									{
										//写日志
										$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$AcceptableOperator['operator'],2,'系统分单给了'.$AcceptableOperator['operator']);//系统操作日志
										//更新目前的数量
										$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['handling']++;
										$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last']--;
										$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last_receive'] = time();
										//$AcceptableOperatorList['last_accept'] = $AcceptableOperatorList['operator'][$AcceptableOperator['operator']];
										$AcceptableOperatorList['last_accept']['operator'] = $AcceptableOperator['operator'];
									}
								}
							}
						}
					}
					
				}
			}
		}
    }
    //超时追问分单循环
    function QuestionApply_Timeout_Add()
    {
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		sleep(1);
		$this->oCommon->addSystemLog(0,"system","system",2,"系统追问超时自动分单开始");//系统操作日志
		sleep(1);
		$AcceptableOperatorList = $this->oOperator->getAcceptableOperator(1);
		$PostList = $this->oOperator->getAllPost("id,timeout");
		foreach($PostList as $key => $PostInfo)
		{
			if($PostInfo['timeout']>0)
			{
				$OperatorList = $this->oOperator->getOperatorByPost($PostInfo['id'],$fields = 'login_name');
				if(count($OperatorList)>0)
				{
					//获取超时的首问列表
					$timeoutQuestionList = $this->oQuestion-> getAppliedUnAnsweredQuestionList(0,$OperatorList,$Setting['helpReApply'],$PostInfo['timeout'],1);
					//如果有取到单子
					if(is_array($timeoutQuestionList['QuestionList']))
					{
						//依次尝试分单
						foreach($timeoutQuestionList['QuestionList'] as $key2 => $QuestionInfo)
						{
							//取消分单
							$Cancel = $this->oQuestion->ApplyCancel($QuestionInfo['id']);
							echo "Cancel:".intval($Cancel)."\n";
							if($Cancel)
							{
								//写日志
								$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$QuestionInfo['js_kf'],11,'系统取消了分单给'.$QuestionInfo['js_kf'].'的咨询问题，撤单原因：'.$PostInfo['timeout'].'秒超时');//系统操作日志
								//更新目前的数量
								$AcceptableOperatorList['operator'][$QuestionInfo['js_kf']]['handling']--;
								$AcceptableOperatorList['operator'][$QuestionInfo['js_kf']]['last']++;
								//生成一个可接单客服列表副本
								$tmp =  $AcceptableOperatorList;
								//不分给当前接这个单的客服
								unset($tmp['operator'][$QuestionInfo['js_kf']]);
								//在余下的客服中选取一个
								$AcceptableOperator = $this->oOperator->getMaxAcceptableOperator($tmp);
								if($AcceptableOperator['operator'] != "")
								{
									//分单结果
									$Apply = $this->oQuestion->ApplyToOperator($QuestionInfo['id'],$AcceptableOperator['operator']);
									echo "Apply:".intval($Apply)."\n";
									//不论是否成功，本轮次不再对此单进行分单
									unset($unAppliedQuestionList['QuestionList'][$key]);
									if($Apply)
									{
										//写日志
										$this->oCommon->addSystemLog($QuestionInfo['id'],$QuestionInfo['author'],$AcceptableOperator['operator'],2,'系统分单给了'.$AcceptableOperator['operator']);//系统操作日志
										//更新目前的数量
										$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['handling']++;
										$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last']--;
										$AcceptableOperatorList['operator'][$AcceptableOperator['operator']]['last_receive'] = time();
										//$AcceptableOperatorList['last_accept'] = $AcceptableOperatorList['operator'][$AcceptableOperator['operator']];
										$AcceptableOperatorList['last_accept']['operator'] = $AcceptableOperator['operator'];
									}
								}
							}
						}
					}
				}
			}
		}
    }
}