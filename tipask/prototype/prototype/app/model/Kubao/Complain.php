<?php
/**
 * 投诉mod层
 * $Id: BroadCastController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Kubao_Complain extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_complain';
	protected $table_answer = 'ask_complain_answer';
	protected $table_revoke_reason = 'ask_complain_revoke_reason';
	protected $table_revoke_queue = 'ask_complain_revoke_queue';
	
	//根据ID获取问题内容
	public function getComplain($QuestionId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $QuestionId);		
	}
	//根据ID获取问题回答
	public function getAnswer($QuestionId,$fields = '*')
	{
		$table_to_process = Base_Widget::getDbTable($this->table_answer);
		return $this->db->selectRow($table_to_process, $fields, '`qid` = ?', $QuestionId);		
	}
	//根据ID更新问题内容
	public function updateComplain($QuestionId, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`id` = ?', $QuestionId);
	}
	//添加撤销重试队列
	public function addRevokeQueue($QueueInfo)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_revoke_queue);
		return $this->db->insert($table_to_process,$QueueInfo);
	}
	//根据问题ID拼接出URL
	public function getQuestionLink($QuestionId,$QuestionType)
	{	
		if($QuestionType == "my")
		{
			$QuestionUrl = $this->config->ScUrl."/?question/complain_detail/".$Id;	
		}
		else
		{
			$QuestionUrl = $this->config->ScUrl."/detail.aspx?QuestionId=".$QuestionId."&QuestionType=".$QuestionType;	
		}
		return $QuestionUrl;
	}
	//获取投诉撤销理由
	public function getRevokeReason()
	{
		
		$table_to_process = Base_Widget::getDbTable($this->table_revoke_reason);
		return $this->db->select($table_to_process,'*');		
	}
	//获取投诉服务记录数量
	public function getComplainServiceList($ConditionList)
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);		
		//查询列
		$select_fields = array('QuestionId'=>'id','time','status');
		//初始化查询条件
		$whereQtype = " qtype > 0 ";
		$whereUser = $ConditionList['UserName']?" author ='".$ConditionList['UserName']."' ":"";
		$t = explode(",",$ConditionList['Public']);
		if(count($t)>=2)
		{
			$wherePublic =' public in ('.implode(',',$t).')';
		}
		else
		{
			$wherePublic = $ConditionList['Public']>=0?" public = ".$ConditionList['Public']." ":"";	
		}		
		$whereId = $ConditionList['IdList']?" id in (".$ConditionList['IdList'].") ":"";
		$whereCondition = array($whereUser,$wherePublic,$whereId,$whereQtype);				
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		
		//计算记录数量
		$ListSql = "SELECT $fields FROM $table_to_process where 1 ".$where;
		$ComplainList = $this->db->getAll($ListSql);
		return $ComplainList;
	}
	//获取日期和问题主分类获取问题数量汇总列表
	public function getComplainList($ConditionList,$fields = "*",$order = "desc")
	{			
		$table_to_process = Base_Widget::getDbTable($this->table);		
		//查询列
		$select_fields = array($fields);
		//初始化查询条件
		$whereStartTime = $ConditionList['StartDate']?" time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereEndTime = $ConditionList['EndDate']?" time <= ".(strtotime($ConditionList['EndDate'])+86400)." ":"";	
		$whereQtype = $ConditionList['QtypeId']?" qtype = ".$ConditionList['QtypeId']." ":"";
		$whereStartTime = $ConditionList['StartTime']?" time >= ".$ConditionList['StartTime']." ":$whereStartTime;
		$whereEndTime = $ConditionList['EndTime']?" time <= ".$ConditionList['EndTime']." ":$whereEndTime;
		$t = explode(",",$ConditionList['Public']);
		if(count($t)>=2)
		{
			$wherePublic =' AND public in ('.implode(',',$t).')';
		}
		else
		{
			$wherePublic = $ConditionList['Public']>=0?" public = ".$ConditionList['Public']." ":"";	
		}
		switch ($ConditionList['QuestionStatus'])
		{
			case 0:
				$whereQuestionStatus = "";
				break;
			case 1:
				$whereQuestionStatus = " status in (0,4)";
				break;
			case 2:
				$whereQuestionStatus = " status in (1,3)";
				break;
			case 3:
				$whereQuestionStatus = " status = 2";
				break;			
		}
		$whereCondition = array($whereStartTime,$whereEndTime,$whereQtype,$wherePublic,$whereQuestionStatus);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//计算记录数量
		$CountSql = "SELECT count(1) as QuestionNum FROM $table_to_process where 1 ".$where;
		$QuestionNum = $this->db->getOne($CountSql);
		//如果记录数量大于页码数量
		if($QuestionNum >= ($ConditionList['Page']-1)*$ConditionList['PageSize'])
		{
			$Limit = " limit ".($ConditionList['Page']-1)*$ConditionList['PageSize'].",".$ConditionList['PageSize'];
			$sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups." order by time ".$order.$Limit;
			$data = $this->db->getAll($sql);
			$ReturnArr = array("QuestionNum"=>$QuestionNum,"QuestionList"=>$data);
			$ReturnArr['QuestionList'] = $data;
		}
		else
		{
			$ReturnArr = array("QuestionNum"=>0,"QuestionList"=>array());
		}
		return $ReturnArr;
	}
	public function getQuestionDetail($QuestionId)
	{
		//获取问题的ID和父ID
		$QuestionInfo = $this->getComplain($QuestionId,'id,author,time,qtype,view,status,description,resolve,photo,call_time,call_type,loginId,receive_time,comment,public,assess,sync,rtime');
		//问题获取到
		$CallTypeList = $this->config->CallTypeList;
		//格式化问题内容
		if($QuestionInfo['id'])
		{
			//解包压缩数组
			$Comment = unserialize($QuestionInfo['comment']);
			//如果问题分类已经被转换
			if($Comment['convert']['to_id']>0)
			{
				$QuestionDetail = array('QuestionId'=>$Comment['convert']['to_id'],'QuestionType'=>$Comment['convert']['to_type'],'Transformed'=>1);
			}
			else
			{
				$QuestionDetail = array('QuestionId'=>intval($QuestionInfo['id']),'QuestionContent'=>$QuestionInfo['description'],'QuestionResolve'=>$QuestionInfo['resolve'],'AuthorName'=>$QuestionInfo['author'],'QuestionTime'=>date("Y-m-d H:i:s",$QuestionInfo['time']),
				'QuestionAttatch'=>$QuestionInfo['photo'],'Views'=>$QuestionInfo['view'],'QtypeId'=>$QuestionInfo['qtype'],'CallType'=>$QuestionInfo['call_time']>0?$CallTypeList[$QuestionInfo['call_type']]:"",
				'CallTime'=>$QuestionInfo['call_time']>0?date("Y-m-d H:i:s",$QuestionInfo['call_time']):0,'QuestionStauts'=>$this->processStatus($QuestionInfo['status']),'QuestionStatus'=>$this->processStatus($QuestionInfo['status']),
				'AcceptTime'=>date("Y-m-d H:i:s",$QuestionInfo['receive_time']),'Hidden'=>$QuestionInfo['public'],'AcceptOperatorName'=>$QuestionInfo['loginId'],'AssessStatus'=>$QuestionInfo['assess'],"Sync"=>$QuestionInfo['sync'],"RevokeTime"=>date("Y-m-d H:i:s",$QuestionInfo['rtime']));
				//获取回答内容
				$AnswerInfo = $this->getAnswer($QuestionDetail['QuestionId']);
				//如果获取到回答
				if($AnswerInfo['id'])
				{
					$QuestionDetail['Answer'] = array('OperatorName'=>$AnswerInfo['contact'],'AnswerTime'=>date("Y-m-d H:i:s",$AnswerInfo['time']),'AnswerContent'=>$AnswerInfo['content']);
					$QuestionDetail['AnswerLag'] = Base_Common::timeLagToText($QuestionInfo['time'],$AnswerInfo['time']);
				}				
			}
		}
		else
		{
			return false;
		}
		return $QuestionDetail;

	}
	//格式化问题状态
	public function processStatus($QuestionStatus)
	{
		//问题创建&分单
		if($QuestionStatus == 0)
		{
			$Status = 1;
		}
		//问题已分单
		elseif($QuestionStatus == 4)
		{
			$Status = 2;
		}
		//问题已回答
		elseif($QuestionStatus == 1)
		{
			$Status = 3;
		}
		//问题已评价
		elseif($QuestionStatus == 3)
		{
			$Status = 4;
		}
		//问题已撤销
		elseif($QuestionStatus == 2)
		{
			$Status = 5;
		}
		else
		{
			$Status = false;
		}
		return $Status;
	}
	//撤销评价
	public function RevokeQuestion($Question,$Update = 1)
	{
		//获取问题内容
		$QuestionInfo = $this->GetComplain($Question['QuestionId'],"id,description,status,sync,comment,author");
		//如果获取到问题
		if($QuestionInfo['id'])
		{
			$oMenCache = new Base_Cache_Memcache("Complaint");
			$Setting = $oMenCache -> get('setting');			
			//如果投诉单已经同步到投诉
			if($QuestionInfo['sync'] == 1)
			{
				//如果问题状态未初始创建 或者 开关允许任何状态撤销
				if($QuestionInfo['status'] == 0 || $Setting['complainSwitch'])
				{
					//解包备注字段
					$Comment = unserialize($QuestionInfo['comment']);
					//备注内容加入撤销信息
					$time = time();
					$Comment['revoke'] = array('rtime'=>$time,'revokeReason'=>$Question['RevokeReason'],'ip'=>$Question['IP']);
					//更新投诉记录的状态
					$UpdateArr = array('status'=>2,'rtime'=>$time,'comment'=>serialize($Comment));
					//如果需要更新
					if($Update)
					{
						$updateComplain = $this->updateComplain($QuestionInfo['id'],$UpdateArr);
					}
					else
					//不需更新，跳过
					{
						$updateComplain = 1;	
					}

					//如果更新成功
					if($updateComplain)
					{
						//从搜索引擎删除数据
						base_common::delete_search('c_'.$QuestionInfo['id']);
						
						//推送信息到投诉
						$CommonConfig = require(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/CommonConfig/commonConfig.php");
						$data = "scid=".$QuestionInfo['id']."&uid=".urlencode($QuestionInfo['author'])."&ip=".$Question['IP']."&revokeTime=".$time."&revokeReason=".urlencode($Question['RevokeReason'])."&sign=".$CommonConfig['COMPLAIN_SIGN'];
						$Revoke= base_common::do_post($this->config->ComplainRevokeUrl,$data);
						$RevokeArr = json_decode($Revoke,true);
						//如果推送到投诉失败
						if($RevokeArr['return']!=1)
						{
							//记入重试队列
							$RevokeQueue = array('scid'=>$QuestionInfo['id'],'uid'=>$Question['UserName'],'ip'=>$Question['IP'],'revokeTime'=>$time,'revokeReason'=>$Question['RevokeReason']);
							$this->addRevokeQueue($RevokeQueue);
						}
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}			
		}
		else
		{
			return false;
		}
	}
	public function ProcessComplainDetail($Complain)
	{
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		
		$oCategory = new Kubao_Category();
		$oOperator = new Kubao_Operator();
		$oQtype = new Kubao_Qtype();
	
		$QuestionDetail = $this->GetQuestionDetail($Complain['QuestionId']);
		//获取到问题详情
		if($QuestionDetail['QuestionId'] > 0)
		{
			//如果问题尚未被转换分类
			if($QuestionDetail['Transformed'] != 1)
			{
				//如果问题被设置为隐藏
				if($QuestionDetail['Hidden']==1)
				{
					return $QuestionDetail;
				}
				else
				{
					$QuestionType = "complain";
					//获取问题分类内容
					$CategoryInfo = $oCategory->getCategoryByQuestionType($QuestionType);
					$QuestionDetail['CategoryName'] = $CategoryInfo['id']?$CategoryInfo['name']:"未设置分类";
					$QuestionDetail['QuestionType'] = ucfirst($QuestionType);
					$QuestionDetail['PageTitle'] = $QuestionDetail['CategoryName']."详情";
					//获取问题主分类内容
					$QtypeInfo = $oQtype->getQtypeById($QuestionDetail['QtypeId'],'id,name');						
					$QuestionDetail['QtypeName'] = $QtypeInfo['id']?$QtypeInfo['name']:"未设置分类";
					//如果回答中包含客服账号
					if($QuestionDetail['Answer']['OperatorName'])
					{
						$List = 'id,photo,login_name,cno,QQ,mobile,weixin,weixinPicUrl,tel,name';
						$M = $oMenCache -> get('OperatorInfo_'.$QuestionDetail['Answer']['OperatorName']."_".md5($List));
						if($M)
						{
							$OperatorInfo = json_decode($M,true);
							//如果获取到的客服信息不合法
							if(!$OperatorInfo['login_name'])
							{
								//从主站获取客服信息
								$OperatorInfo = $oOperator->getOperatorFromVadmin($QuestionDetail['Answer']['OperatorName'],$List);
								$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
							}
						}
						else
						{					
							//从主站获取客服信息
							$OperatorInfo = $oOperator->getOperatorFromVadmin($QuestionDetail['Answer']['OperatorName'],$List);
							//如果没有从主站获取到客服信息
							if($OperatorInfo['login_name'])
							{
								//格式化显示信息
								$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
								$oMenCache -> set('OperatorInfo_'.$QuestionDetail['Answer']['OperatorName']."_".md5($List),json_encode($OperatorInfo),60);
							}
						}
						//如果获取到的客服信息合法
						if($OperatorInfo['OperatorName'])
						{
							$oMenCache -> set('OperatorInfo_'.$QuestionDetail['Answer']['OperatorName']."_".md5($List),json_encode($OperatorInfo),60);
							$QuestionDetail['Answer']['OperatorInfo'] = $OperatorInfo;
						}
						else
						{
							unset($QuestionDetail['Answer']['OperatorInfo']);
						}
					}
					//如果包含接单客服账号
					if($QuestionDetail['AcceptOperatorName'])
					{
						$List = 'id,photo,login_name,cno,QQ,mobile,weixin,weixinPicUrl,tel,name';
						//$M = $oMenCache -> get('OperatorInfo_'.$QuestionDetail['AcceptOperatorName']."_".md5($List));
						if($M)
						{
							$OperatorInfo = json_decode($M,true);
							//如果获取到的客服信息不合法
							if(!$OperatorInfo['login_name'])
							{
								//从主站获取客服信息
								$OperatorInfo = $oOperator->getOperatorFromVadmin($QuestionDetail['AcceptOperatorName'],$List);
								$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
							}
						}
						else
						{					
							//从主站获取客服信息
							$OperatorInfo = $oOperator->getOperatorFromVadmin($QuestionDetail['AcceptOperatorName'],$List);
							//如果没有从本地获取到客服信息
							if($OperatorInfo['login_name'])
							{
								//格式化显示信息
								$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
							}
						}
						//如果获取到的客服信息合法
						if($OperatorInfo['OperatorName'])
						{
							$oMenCache -> set('OperatorInfo_'.$QuestionDetail['AcceptOperatorName']."_".md5($List),json_encode($OperatorInfo),60);
							$QuestionDetail['AcceptOperatorInfo'] = $OperatorInfo;
						}
						else
						{
							unset($QuestionDetail['AcceptOperatorInfo']);
						}
					}
					//如果问题状态为 已同步至投诉 并且 状态不是已撤销 并且 问题状态未初始创建 或者 开关允许任何状态撤销
					if(($QuestionDetail['Sync'] == 1) && ($QuestionDetail['QuestionStauts'] != 5) && ($QuestionDetail['QuestionStatus'] != 5) && ($QuestionDetail['QuestionStauts'] == 0 || $QuestionDetail['QuestionStatus'] == 0 || $Setting['complainSwitch']))
					{
						//允许问题撤销
						$QuestionDetail['Revoke'] = 1;
					}
					unset($QuestionDetail['CatagoryId'],$QuestionDetail['QtypeId']);
					return $QuestionDetail;							
				}						
			}
			else
			{
				//如果问题被转换为咨询/建议
				if(in_array($QuestionDetail['QuestionType'],array('ask','suggest')))
				{
					return $QuestionDetail;
				}
				else
				{
					return false;
				}
			}
		}
		//未获取到问题详情
		else
		{
			return false;
		}
	}
	//向投诉系统检查撤销状态
	public function checkRevokeFromComplain($QuestionId)
	{
		$url = "http://complain.5173.com/sc/AsycCancelStatus.ashx";
		$url.= "?scid=".intval($QuestionId);
		$return = file_get_contents($url);
		$return = json_decode($return,true);
		return $return;
	}
}
