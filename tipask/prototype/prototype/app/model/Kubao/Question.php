<?php
/**
 * 咨询/建议mod层
 * $Id: BroadCastController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Kubao_Question extends Base_Widget
{
	//声明所用到的表
	protected $table = 'ask_question';
	protected $table_answer = 'ask_answer';
	protected $table_question_num = 'ask_question_num';
	protected $table_history_map = 'ask_histroy_map';
	protected $table_log = 'ask_log';
	
	//根据问题ID拼接出URL
	public function getQuestionLink($QuestionId,$QuestionType)
	{	
		$QuestionUrl = $this->config->ScUrl."/detail.aspx?QuestionId=".$QuestionId."&QuestionType=".$QuestionType;
		return $QuestionUrl;
	}	
	//根据ID获取问题内容
	public function getQuestion($QuestionId,$fields = '*',$Year = 0)
	{
		if($Year == 0)
		{
			$table_to_process = Base_Widget::getDbTable($this->table);
			return $this->db->selectRow($table_to_process, $fields, '`id` = ?', $QuestionId);
		}
		else
		{
			return $this->getQuestion_History($QuestionId,$fields,$Year);
		}
	}
	//根据ID获取历史问题内容
	public function getQuestion_History($QuestionId,$fields = '*',$Year)
	{
		$table_to_process = Base_Widget::getDbTable($this->table."_h_".$Year);
		$this->db_h = Base_Db_Hash::getInstance()->prepare($table_to_process);
		return $this->db_h->selectRow($table_to_process, $fields, '`id` = ?', $QuestionId);		
	}
	//根据ID更新问题内容
	public function updateQuestion($QuestionId, array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`id` = ?', $QuestionId);
	}
	//添加新提问
	public function addQuestion($QuestionInfo)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process,$QuestionInfo);
	}
	//根据ID获取问题回答
	public function getAnswer($QuestionId,$fields = '*',$Year = 0)
	{
		if($Year == 0)
		{
			$table_to_process = Base_Widget::getDbTable($this->table_answer);
			return $this->db->selectRow($table_to_process, $fields, '`qid` = ?', $QuestionId);	
		}
		else
		{
			return $this->getAnswer_History($QuestionId,$fields,$Year);
		}	
	}
	//根据ID获取历史问题回答
	public function getAnswer_History($QuestionId,$fields = '*',$Year)
	{
		$table_to_process = Base_Widget::getDbTable($this->table_answer."_h_".$Year);
		$this->db_h = Base_Db_Hash::getInstance()->prepare($table_to_process);
		return $this->db_h->selectRow($table_to_process, $fields, '`qid` = ?', $QuestionId);		
	}
	//获取咨询/建议的服务记录数量
	public function getServiceQuestionList($ConditionList)
	{	
		$table_to_process = Base_Widget::getDbTable($this->table);
		$oCategory = new Kubao_Category();
		$CategoryInfo = $oCategory->getCategoryByQuestionType($ConditionList['QuestionType'],'id');
		if($CategoryInfo['id'])
		{
			//查询列
			$select_fields = array('QuestionId'=>'id','time','status');
			//初始化查询条件
			$whereUser = $ConditionList['UserName']?" author ='".$ConditionList['UserName']."' ":"";
			$whereParent = $ConditionList['Parent']>=0?" pid =".$ConditionList['Parent']." ":"";
			$whereRevocation = $ConditionList['Revocation']>=0?" revocation = ".$ConditionList['Revocation']." ":"";
			$whereId = $ConditionList['IdList']?" id in (".$ConditionList['IdList'].") ":"";
			$whereCid = $CategoryInfo['id']?" cid = ".$CategoryInfo['id']." ":"";
			$whereCondition = array($whereUser,$whereCid,$whereParent,$whereRevocation,$whereId);
			
			//生成查询列
			$fields = Base_common::getSqlFields($select_fields);
			//生成条件列
			$where = Base_common::getSqlWhere($whereCondition);
			
			//计算记录数量
			$ListSql = "SELECT $fields FROM $table_to_process where 1 ".$where;
			$QuestionList = $this->db->getAll($ListSql);
		}
		else
		{
			$QuestionList = array();
		}
		return $QuestionList;
	}
	//获取日期和问题主分类获取问题数量汇总列表
	public function getQuestionList($ConditionList,$fields = "*",$order = "desc")
	{			
		//如果是历史数据
		if($ConditionList['History']>0)
		{
			//生成历史表名
			$table_to_process = Base_Widget::getDbTable($this->table."_h_".$ConditionList['History']);
			//重新建立与历史数据库的链接
			$this->db_h = Base_Db_Hash::getInstance()->prepare($table_to_process);
			//将当前应用的数据库链接置为历史库
			$db = $this->db_h;
		}
		else
		{
			$table_to_process = Base_Widget::getDbTable($this->table);
			//将当前应用的数据库链接置为线上库
			$db = $this->db;
		}
		//查询列
		$select_fields = array($fields);
		//初始化查询条件
		$whereStartTime = $ConditionList['StartDate']?" time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereEndTimeTime = $ConditionList['EndDate']?" time <= ".(strtotime($ConditionList['EndDate'])+86400)." ":"";	
		$whereQtype = $ConditionList['QtypeId']?" qtype = ".$ConditionList['QtypeId']." ":"";
		$whereParent = $ConditionList['Parent']>=0?" pid =".$ConditionList['Parent']." ":" pid > 0";
		$whereRevocation = $ConditionList['Revocation']>=0?" revocation = ".$ConditionList['Revocation']." ":"";
		$whereHelp = $ConditionList['Help']>=0?" help_status = ".$ConditionList['Help']." ":"";
		$WhereAccepted = $ConditionList['Accepted']>=0?" is_hawb = ".$ConditionList['Accepted']." ":"";
		if(count($ConditionList['AcceptedOperatorList'])>=1)
		{
			$t = array();
			foreach($ConditionList['AcceptedOperatorList'] as $key => $OperatorInfo)
			{
				$t[] = "'".$OperatorInfo['login_name']."'";
			}
			$text = implode(",",$t);
			$whereAcceptedOperatorList = strlen($text)>0? " js_kf in (".$text.") ":"";
		}
		$oCategory = new Kubao_Category();
		if($ConditionList['QuestionType'])
		{
			$t = explode(",",$ConditionList['QuestionType']);
			foreach($t as $k => $v)
			{
				if($v == '0')
				{
					$t2[$v] = 0;
				}
				else
				{
					$CategoryInfo = $oCategory->getCategoryByQuestionType($v,'id');
					if($CategoryInfo['id']>0)
					{
						$t2[$v] = $CategoryInfo['id'];
					}
				}
			}
			$whereCid = $CategoryInfo['id']?" cid in (".implode(",",$t2).") ":"";
		}
		$whereHidden = $ConditionList['hidden']?" hidden = ".$ConditionList['hidden']." ":"";
		switch ($ConditionList['QuestionStatus'])
		{
			case 0:
				$whereQuestionStatus = "";
				break;
			case 1:
				$whereQuestionStatus = " status = '1'";
				break;
			case 2:
				$whereQuestionStatus = " status != '1'";
				break;
		}
		$whereCondition = array($whereStartTime,$whereEndTimeTime,$whereQtype,$whereCid,$whereParent,$whereRevocation,$whereQuestionStatus,$whereHidden,$whereHelp,$WhereAccepted,$whereAcceptedOperatorList);
		//生成查询列
		$fields = Base_common::getSqlFields($select_fields);
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		//计算记录数量
		$CountSql = "SELECT count(1) as QuestionNum FROM $table_to_process where 1 ".$where;
		$QuestionNum = $db->getOne($CountSql);
		if($ConditionList['PageSize']>0)
		{
			//如果记录数量大于页码数量
			if($QuestionNum >= ($ConditionList['Page']-1)*$ConditionList['PageSize'])
			{
				$Limit = " limit ".($ConditionList['Page']-1)*$ConditionList['PageSize'].",".$ConditionList['PageSize'];
				$sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups." order by time ".$order." ".$Limit;
				$data = $db->getAll($sql);
				$ReturnArr = array("QuestionNum"=>$QuestionNum,"QuestionList"=>$data);
				$ReturnArr['QuestionList'] = $data;
			}
			else
			{
				$ReturnArr = array("QuestionNum"=>0,"QuestionList"=>array());
			}			
		}
		else
		{
			$sql = "SELECT $fields FROM $table_to_process where 1 ".$where.$groups." order by time ".$order;
			$data = $db->getAll($sql);
			$ReturnArr = array("QuestionNum"=>$QuestionNum,"QuestionList"=>$data);
			$ReturnArr['QuestionList'] = $data;
		}
		return $ReturnArr;
	}
	public function getQuestionDetail($QuestionId,$History = 0)
	{
		//获取问题的ID和父ID
		$QuestionInfo = $this->getQuestion($QuestionId,'id,pid',$History);
		//问题获取到
		if($QuestionInfo['id'])
		{
			//如果问题父ID大于0，为追问
			if($QuestionInfo['pid'])
			{
				$ParentID = $QuestionInfo['pid'];
			}
			//主文
			else
			{
				$ParentID = $QuestionInfo['id'];
			}
			//获取主问内容
			$ParentInfo = $this->getQuestion($ParentID,'id,author,time,cid,cid1,qtype,attach,views,status,description,is_pj,comment,hidden',$History);
			//解包压缩数组
			$Comment = unserialize($ParentInfo['comment']);
			//如果问题分类已经被转换
			if($Comment['convert']['to_id']>0)
			{
				$QuestionDetail = array('QuestionId'=>$Comment['convert']['to_id'],'QuestionType'=>$Comment['convert']['to_type'],'Transformed'=>1);
			}
			else
			{
				//格式化问题内容
				$QuestionDetail = array('QuestionId'=>intval($ParentInfo['id']),'QuestionContent'=>$ParentInfo['description'],'AuthorName'=>$ParentInfo['author'],'QuestionTime'=>date("Y-m-d H:i:s",$ParentInfo['time']),'QuestionAttatch'=>$ParentInfo['attach'],'Views'=>$ParentInfo['views'],'CatagoryId'=>$ParentInfo['cid'],'QtypeId'=>$ParentInfo['qtype'],'QuestionStauts'=>$this->processStatus($ParentInfo['status']),'QuestionStatus'=>$this->processStatus($ParentInfo['status']),'SubQuestion'=>0,'AssessStatus'=>$ParentInfo['is_pj'],'AssessCount'=>intval($Comment['assess_num']),'Hidden'=>$ParentInfo['hidden'],'Assess'=>$ParentInfo['is_pj']==1?0:1,//如果未评价或评价为不满意，则默认可以继续评价
				'SubQuestionList'=>array());
				//获取回答内容
				$AnswerInfo = $this->getAnswer($QuestionDetail['QuestionId'],"*",$History);
				//如果获取到回答
				if($AnswerInfo['id'])
				{
					//可以继续追问
					$QuestionDetail['SubQuestion'] = 1;
					//可以评价
					$QuestionDetail['Assess'] = $QuestionDetail['Assess']==0?0:1;
					//格式化回答内容
					$QuestionDetail['Answer'] = array('OperatorName'=>$AnswerInfo['author'],'AnswerTime'=>date("Y-m-d H:i:s",$AnswerInfo['time']),'AnswerContent'=>$AnswerInfo['content']);
					$QuestionDetail['AnswerLag'] = Base_Common::timeLagToText($ParentInfo['time'],$AnswerInfo['time']);
				}
				
				//获取追问信息
				$SubQuestionList = $this->getQuestionList(array('Accepted'=>-1,'Parent'=>$ParentID,'Revocation'=>-1,'Help'=>-1,'History'=>$History,'QuestionType'=>"ask,suggest"),'id,time,cid,cid1,qtype,attach,views,status,description','asc');
				$QuestionDetail['SubQuestionList'] = array();
				if(count($SubQuestionList['QuestionNum'])>0)
				{
					foreach($SubQuestionList['QuestionList'] as $key => $SubQuestion)
					{
						//格式化追问内容
						$QuestionDetail['SubQuestionList'][$key] = array('QuestionId'=>intval($SubQuestion['id']),'QuestionContent'=>$SubQuestion['description'],'QuestionTime'=>date("Y-m-d H:i:s",$SubQuestion['time']),'QuestionStauts'=>$this->processStatus($SubQuestion['status']),'QuestionStatus'=>$this->processStatus($SubQuestion['status']));
						//获取追问回答内容
						$AnswerInfo = $this->getAnswer($SubQuestion['id'],"*",$History);
						//如果获取到回答
						if($AnswerInfo['id'])
						{
							//可以继续追问
							$QuestionDetail['SubQuestion'] = 1;
							//格式化追问回答内容
							$QuestionDetail['SubQuestionList'][$key]['Answer'] = array('OperatorName'=>$AnswerInfo['author'],'AnswerTime'=>date("Y-m-d H:i:s",$AnswerInfo['time']),'AnswerContent'=>$AnswerInfo['content']);
							$QuestionDetail['SubQuestionList'][$key]['AnswerLag'] = Base_Common::timeLagToText($SubQuestion['time'],$AnswerInfo['time']);
						}
						else
						{
							//不可继续追问
							$QuestionDetail['SubQuestion'] = 0;
						}
					}
				}
			}
			//如果是历史数据
			if($History > 0)
			{
				//不可继续追问
				$QuestionDetail['SubQuestion'] = 0;
				//不可评价
				$QuestionDetail['Assess'] = 0;				
			}
			return $QuestionDetail;
		}
		else
		{
			return false;
		}		
	}
	//格式化问题状态
	public function processStatus($QuestionStatus)
	{		
		//问题创建&分单
		if($QuestionStatus == 1)
		{
			$Status = 1;
		}
		//问题已回答
		elseif($QuestionStatus == 2)
		{
			$Status = 3;
		}
		//问题已评价
		elseif($QuestionStatus == 3)
		{
			$Status = 4;
		}
		else
		{
			$Status = false;
		}
		return $Status;
	}
	//评价问题
	public function AssessQuestion($QuestionId,$Assess)
	{
		//获取问题内容
		$QuestionInfo = $this->GetQuestion($QuestionId,"id,is_pj,comment");
		//如果问题获取到
		if($QuestionInfo['id'])
		{			
			//解包压缩数组
			$Comment = unserialize($QuestionInfo['comment']);
			//评价次数累加
			$Comment['assess_num'] ++;
			$AssessArr = array('is_pj'=>$Assess,'status'=>3,'astime'=>time(),'comment'=>serialize($Comment));
			//更新问题内容
			$AssessResult = $this->updateQuestion($QuestionInfo['id'],$AssessArr);
			return $AssessResult;			
		}
		else
		{
			return false;
		}
		return $Status;
	}
	//根据ID获取问题内容
	public function getRecentByIP($IP,$IsParent)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		//计算起始时间
		$EndTime = time();
		$StartTime = $EndTime - 3600;
		$StartTime = 0;		
		return $this->db->selectOne($table_to_process,"count(1) as QuestionNum", '`time` >= ? and `ip`=? and `pid` = ?', array($StartTime,$IP,$IsParent));
	}
    //根据问题ID获取所在年份
    function GetHistoryMap($QuestionId,$QuestionType,$fields = "*")
    {
		$table_name = Base_Widget::getDbTable($this->table_history_map);
		$Mapping = $this->db->selectRow($table_name, $fields, '`question_type` = ? and `min` <= ? and `max` = 0', array($QuestionType,$QuestionId));
		if($Mapping['min']>0)
		{
			return $Mapping;
		}
		$Mapping = $this->db->selectRow($table_name, $fields, '`question_type` = ? and `min` <= ? and `max` >= ?', array($QuestionType,$QuestionId,$QuestionId));
    	return $Mapping;
    }
	function ProcessQuestionDetail($Question)
	{
		$oMenCache = new Base_Cache_Memcache("Complaint");
		$Setting = $oMenCache -> get('setting');
		
		$oCategory = new Kubao_Category();
		$oOperator = new Kubao_Operator();
		$oQtype = new Kubao_Qtype();

		//根据问题ID判断问题是否属于历史数据库
		$HistoryMapping = $this->GetHistoryMap($Question['QuestionId'],'ask');
		$QuestionDetail = $this->GetQuestionDetail($Question['QuestionId'],intval($HistoryMapping['year']));
		//获取到问题详情
		if($QuestionDetail['QuestionId'] > 0)
		{
			//如果问题尚未被转换分类
			if($QuestionDetail['Transformed'] != 1)
			{
				//获取问题分类内容
				$CategoryInfo = $oCategory->getCategory($QuestionDetail['CatagoryId'],'id,name,question_type');
				$QuestionDetail['CategoryName'] = $CategoryInfo['id']?$CategoryInfo['name']:"未设置分类";
				$QuestionDetail['QuestionType'] = ucfirst($CategoryInfo['question_type']);
				$QuestionDetail['PageTitle'] = $QuestionDetail['CategoryName']."详情";
				//获取问题主分类内容
				$QtypeInfo = $oQtype->getQtypeById($QuestionDetail['QtypeId'],'id,name');						
				$QuestionDetail['QtypeName'] = $QtypeInfo['id']?$QtypeInfo['name']:"未设置分类";
				//如果回答中包含客服账号
				if($QuestionDetail['Answer']['OperatorName'])
				{
					$List = 'id,photo,login_name,cno,QQ,tel,mobile,weixin,weixinPicUrl,xnGroupId,name';
					$M = $oMenCache -> get('OperatorInfo_'.$QuestionDetail['Answer']['OperatorName']."_".md5($List));
					if($M)
					{
						$OperatorInfo = json_decode($M,true);
						//如果获取到的客服信息合法
						if(!$OperatorInfo['OperatorId'])
						{
							//获取相关客服信息
							$OperatorInfo = $oOperator->getOperatorByName($QuestionDetail['Answer']['OperatorName'],$List);
							//格式化显示信息
							$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
						}
					}
					else
					{											
						//获取相关客服信息
						$OperatorInfo = $oOperator->getOperatorByName($QuestionDetail['Answer']['OperatorName'],$List);
						//格式化显示信息
						$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
					}
					//如果获取到的客服信息合法
					if($OperatorInfo['OperatorId'])
					{
						$oMenCache -> set('OperatorInfo_'.$QuestionDetail['Answer']['OperatorName']."_".md5($List),json_encode($OperatorInfo),60);
						$QuestionDetail['Answer']['OperatorInfo'] = $OperatorInfo;
					}
					else
					{
						unset($QuestionDetail['Answer']['OperatorInfo']);
					}
				}
				//处理追问内数据
				foreach($QuestionDetail['SubQuestionList'] as $key => $SubQuestion)
				{
					//如果回答中包含客服账号
					if($SubQuestion['Answer']['OperatorName'])
					{
						$List = 'id,photo,login_name,cno,QQ,tel,mobile,weixin,weixinPicUrl,xnGroupId,name';
						$M = $oMenCache -> get('OperatorInfo_'.$SubQuestion['Answer']['OperatorName']."_".md5($List));
						if($M)
						{
							$OperatorInfo = json_decode($M,true);
							//如果获取到的客服信息不合法
							if(!$OperatorInfo['OperatorId'])
							{
								//获取相关客服信息
								$OperatorInfo = $oOperator->getOperatorByName($SubQuestion['Answer']['OperatorName'],$List);
								//格式化显示信息
								$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
							}
						}
						else
						{					
							//获取相关客服信息
							$OperatorInfo = $oOperator->getOperatorByName($SubQuestion['Answer']['OperatorName'],$List);
							//格式化显示信息
							$OperatorInfo = $oOperator->processOperatorInfo($OperatorInfo);
						}
						//如果获取到的客服信息不合法
						if($OperatorInfo['OperatorId'])
						{
							$oMenCache -> set('OperatorInfo_'.$SubQuestion['Answer']['OperatorName']."_".md5($List),json_encode($OperatorInfo),60);
							$QuestionDetail['SubQuestionList'][$key]['OperatorInfo'] = $OperatorInfo;
						}
						else
						{
							unset($QuestionDetail['SubQuestionList'][$key]['OperatorInfo']);
						}
					}							
				}
				//如果评价次数为正数且达到上限 
				if($Setting['limit_assess_num']<=$QuestionDetail['AssessCount'] && $Setting['limit_assess_num'] >=0)
				{
					//取消评价资格
					$QuestionDetail['Assess'] = 0;
				}
				//删除不必要的字段
				unset($QuestionDetail['CatagoryId'],$QuestionDetail['QtypeId']);
				return $QuestionDetail;											
			}
			else
			{
				//如果问题被转换为投诉
				if($QuestionDetail['QuestionType'] == "complain")
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
    //获取指定数量的未分配的提问
    //$num：问题数量
    //$add=0为获取首问接单数据，否则为追问接单数据
    function getUnAppliedQuestionList($Num,$Add = 0)
    {
		//获取咨询、建议的分类ID
		$oCategory = new Kubao_Category();
		$Category_Ask = $oCategory->getCategoryByQuestionType('ask');
		$Category_Suggest = $oCategory->getCategoryByQuestionType('suggest');
		$ConditionList = array("QuestionType"=>'0,ask,suggest',"QuestionStatus"=>1,"Accepted"=>0,"Revocation"=>0,"Help"=>0,"Parent"=>-1,"PageSize"=>$Num,"Page"=>1,"Parent"=>$Add==0?0:-1);
		$QuestionList = $this->getQuestionList($ConditionList,$fields = "id,pid,cid,cid1,author",$order = "asc");
		return $QuestionList;
    }
    //获取指定数量的未已分配未回答的提问
    //$num：问题数量
    //$add=0为获取首问接单数据，否则为追问接单数据
	//$OperatorList为接单客服的名单
	//$HelpReApply为协助处理的单据是否重新分单的开关，1为是，0为否
    function getAppliedUnAnsweredQuestionList($Num,$OperatorList,$HelpReApply,$TimeLag,$Add=0)
    {
		//获取咨询、建议的分类ID
		$oCategory = new Kubao_Category();
		$Category_Ask = $oCategory->getCategoryByQuestionType('ask');
		$Category_Suggest = $oCategory->getCategoryByQuestionType('suggest');
		$ConditionList = array("QuestionType"=>'0,ask,suggest',"QuestionStatus"=>1,"Accepted"=>1,"Revocation"=>0,"Help"=>0,"Parent"=>-1,"PageSize"=>$Num,"Page"=>1,"Parent"=>$Add==0?0:-1,"AcceptedOperatorList"=>$OperatorList);
		$QuestionList = $this->getQuestionList($ConditionList,$fields = "id,pid,cid,cid,author,js_kf,receive_time,comment",$order = "asc");
		foreach($QuestionList['QuestionList'] as $key => $QuestionInfo)
		{
			//如果 当前时间 与 接单时间 的时间差小于要求的时间差
			if($TimeLag >= time() - $QuestionInfo['receive_time'])
			{
				//将未超时的数据从队列中移除
				unset($QuestionList['QuestionList'][$key]);
				//总量减少
				$QuestionList['QuestionNum']--;
			}
			else
			{
				//协助处理的单据不进行重新分单
				if($HelpReApply ==0)
				{
					//解包压缩数组
					$Comment = unserialize($QuestionInfo['comment']);
					//如果当前问题最后一次的转单时间 比 接单时间要大
					if($Comment['transfer'][count($Comment['transfer'])-1]['transfer_time'] >= $QuestionInfo['receive_time'])
					{
						//将未超时的数据从队列中移除
						unset($QuestionList['QuestionList'][$key]);
						//总量减少
						$QuestionList['QuestionNum']--;
					}
				}
			}
		}
		return $QuestionList;
    }
    //将一个未分配的问题分配给指定客服
    //$qid：问题ID
    //$operator：客服账号
    function ApplyToOperator($QuestionId,$OperatorName,$force = false)
    {
		//获取咨询、建议的分类ID
		$oCategory = new Kubao_Category();
		$oOperator = new Kubao_Operator();
		$Category_Ask = $oCategory->getCategoryByQuestionType('ask');
		$Category_Suggest = $oCategory->getCategoryByQuestionType('suggest');

		//获取问题
        $QuestionInfo = $this->getQuestion($QuestionId);
		//问题存在
        if($QuestionInfo['id'])
        {
            //问题 尚未分单 且 尚未被撤销 且 接手客服为空 且 为非协助状态 且 问题分类在指定列表中
			if($QuestionInfo['is_hawb'] == 0 && $QuestionInfo['revocation'] == 0 && $QuestionInfo['js_kf'] == "" &&  $QuestionInfo['help_status'] == 0 && in_array($QuestionInfo['cid'],array(0,$Category_Ask['id'],$Category_Suggest['id'])))
			{
	            $this->db->begin();
				$updateArr = array("is_hawb"=>1,"js_kf"=>$OperatorName,"receive_time"=>time());
				$UpdateQuestion = $this->updateQuestion($QuestionInfo['id'],$updateArr);
				//如果更新成功
				if($UpdateQuestion)
				{
					$OperatorInfo = $oOperator->getOperatorByName($OperatorName,"login_name,pid,ishandle,isonjob,isbusy");
					//如果客服存在 且 （是强制分单 或 客服非忙碌） 且 客服可以接单 且 客服在班
					if(($OperatorInfo['login_name']!='') && (($force == true) || ($OperatorInfo['isbusy'] == 0)) && ($OperatorInfo['ishandle'] == 1) && ($OperatorInfo['isonjob'] == 1))
					{
						//如果强制分单,则将当前单量置为负值
						if($force == true)
						{
							$OperatorAccepted['num'] = -1;
							$OperatorAccepted['num_add'] = -1;
							
						}
						else
						{
							//检查客服已分配单量
							$OperatorAccepted = $oOperator->getOperatorAccecpted($OperatorName,"num,num_add");
						}
						//获取分单数量限制
						$PostLimit = $oOperator->getPost($OperatorInfo['pid'],"question_limit,question_limit_add");
						//首问
						if($QuestionInfo['pid']==0)
						{
							//首问单量小于首问最大单量
							if(intval($OperatorAccepted['num'])<$PostLimit['question_limit'])
							{
								//更新首问数量
								$UpdateAcceptedNum = $oOperator->UpdateOperatorAccecpted($OperatorName,1,0);
							}
							else
							{
								//单量不足，回滚
								$this->db->rollback();
								return false;
							}
						}
						//追问
						else
						{
							//追问单量小于追问最大单量
							if(intval($OperatorAccepted['num_add'])<$PostLimit['question_limit_add'])
							{
								//更新追问数量
								$UpdateAcceptedNum = $oOperator->UpdateOperatorAccecpted($OperatorName,1,1);
							}
							else
							{
								//单量不足，回滚
								$this->db->rollback();
								return false;
							}
						}
						if($UpdateAcceptedNum)
						{
							//更新成功，提交
							$this->db->commit();
							return true;
						}
						else
						{
							// 更新失败，回滚
							$this->db->rollback();
							return false;
						}
					}
					else
					{
						//客服不在班或不存在
						$this->db->rollback();
						return false;
					}
				}
				else
				{
					//更新失败
					$this->db->rollback();
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
			//无此问题
			return false;
		}
	}
    function ApplyCancel($QuestionId)
    {
		$oOperator = new Kubao_Operator();
		//事务开启
        $this->db->begin();
		//获取问题
        $QuestionInfo = $this->getQuestion($QuestionId,"id,status,js_kf,pid");
		//问题存在
        if($QuestionInfo['id'])
        {
            //如果已经被接手
            if($QuestionInfo['js_kf']!='')
            {
                //如果尚未被回答或尚未完结
                if($QuestionInfo['status']<2)
                {
					//标识未被分单
					$updateArr = array("is_hawb"=>0,"js_kf"=>"","receive_time"=>0);
					$UpdateQuestion = $this->updateQuestion($QuestionInfo['id'],$updateArr);
                    //减去单量
                    if($QuestionInfo['pid']==0)
                    {
						//更新首问数量
						$UpdateAcceptedNum = $oOperator->UpdateOperatorAccecpted($QuestionInfo['js_kf'],-1,0);
                    }
                    else
                    {
						//更新追问数量
						$UpdateAcceptedNum = $oOperator->UpdateOperatorAccecpted($QuestionInfo['js_kf'],-1,1);
                    }
					if($UpdateQuestion && $UpdateAcceptedNum)
                    {
                        //事务成功，提交
                        $this->db->commit();
                        return true;
                    }
                    else
                    {
                        //事务失败，回滚
                        $this->db->rollback();
                        return false;
                    }
                }
                else
                {
                    //问题已被回答，回滚
                    $this->db->rollback();
                    return false;
                }
            }
            else
            {
                //问题无接手，回滚
                $this->db->rollback();
                return false;
            }
        }
        else
        {
            //问题未找到，回滚
            return false;
            $this->db->rollback();
        }

    }
}
