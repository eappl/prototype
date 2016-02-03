<?php
!defined('IN_TIPASK') && exit('Access Denied');
class complainmodel {

    var $db;
    var $base;
    var $cache;

    function complainmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
	//获取投诉列表
	function getComplainList($ConditionList,$page,$pagesize)
	{
		$whereComplainStartTime = $ConditionList['ComplainStartDate']?" time >= ".strtotime($ConditionList['ComplainStartDate'])." ":"";
		$whereComplainEndTime = $ConditionList['ComplainEndDate']?" time < ".(strtotime($ConditionList['ComplainEndDate'])+86400)." ":"";
		$whereAnswerStartTime = $ConditionList['AnswerStartDate']?" atime >= ".strtotime($ConditionList['AnswerStartDate'])." ":"";
		$whereAnswerEndTime = $ConditionList['AnswerEndDate']?" atime < ".(strtotime($ConditionList['AnswerEndDate'])+86400)." ":"";
		$whereAuthor = $ConditionList['author']!=""?" author = '".$ConditionList['author']."' ":"";
		$whereAuthorId = $ConditionList['author_id']!=""?" author_id = '".$ConditionList['author_id']."' ":"";
		$whereOperator = $ConditionList['operator_loginId']!=""?" loginId = '".$ConditionList['operator_loginId']."' ":"";
		$whereStatus = $ConditionList['status']!=-1?($ConditionList['status']!=-2?" status = ".$ConditionList['status']." ":" status !=2"):"";
		$whereId = $ConditionList['complainId']!=0?" id = ".$ConditionList['complainId']." ":"";
		$whereSid = $ConditionList['sid']!=-1?" sid = ".$ConditionList['sid']." ":"";
		$whereJid = $ConditionList['jid']!=0?" jid = ".$ConditionList['jid']." ":"";
		$whereAssess = $ConditionList['Assess']!=-1?" assess = ".$ConditionList['Assess']." ":"";
		$whereReason = $ConditionList['reason']!=""?" comment like '%".$ConditionList['reason']."%' ":"";
		if($ConditionList['transformed']==2)
		{
			//查询转出的投诉
			$whereTransformed = "LOCATE('to_id', comment)!=0 ";
		}
		elseif($ConditionList['transformed']==3)
		{
			//查询转入的投诉
			$whereTransformed = "LOCATE('from_id', comment)!=0 ";
		}
		else
		{
			//不查询已经转出的投诉
			$whereTransformed = "LOCATE('to_id', comment)=0 ";
		}
		if(in_array($ConditionList['status'],array(0,4)))
		{
			unset($whereAnswerStartTime,$whereAnswerEndTime);
			//$whereAnswerStartTime = " time = atime ";			
		}
		if(in_array($ConditionList['status'],array(2,-1)))
		{
			unset($whereAnswerStartTime,$whereAnswerEndTime);
		}

		$whereCondition = array($whereComplainStartTime,$whereComplainEndTime,$whereAnswerStartTime,$whereAnswerEndTime,$whereStatus,$whereSid,$whereJid,$whereAssess,$whereAuthor,$whereAuthorId,$whereOperator,$whereReason,$whereTransformed);
		if($whereId!="")
		{
			$whereCondition = array($whereId);
		}
		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}				
		$count_sql = "select count(*) from " . DB_TABLEPRE . "complain where 1 ".$where;
		$ComplainCount = $this->db->result_first($count_sql);
		if($ComplainCount>0)
		{
			$sql = "select * from " . DB_TABLEPRE . "complain where 1 ".$where." order by id desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("ComplainCount"=>$ComplainCount,"ComplainList"=>$rs);
		}
		else
		{
			$returnArr = array("ComplainCount"=>0,"ComplainList"=>array());
		}
		return $returnArr;
	}
	function getComplainData($ConditionList)
	{
		$date = $ConditionList['ComplainStartDate'];
		while(strtotime($date)<=strtotime($ConditionList['ComplainEndDate']))
		{
			$returnArr['date'][$date] = array("complainCount"=>0,"assess"=>array());
			$date = date("Y-m-d",strtotime($date)+86400);
		}
		$whereComplainStartTime = $ConditionList['ComplainStartDate']?" time >= ".strtotime($ConditionList['ComplainStartDate'])." ":"";
		$whereComplainEndTime = $ConditionList['ComplainEndDate']?" time < ".(strtotime($ConditionList['ComplainEndDate'])+86400)." ":"";
		$whereAnswerStartTime = $ConditionList['AnswerStartDate']?" atime >= ".strtotime($ConditionList['AnswerStartDate'])." ":"";
		$whereAnswerEndTime = $ConditionList['AnswerEndDate']?" atime < ".(strtotime($ConditionList['AnswerEndDate'])+86400)." ":"";
		$whereAuthor = $ConditionList['author']!=""?" author = '".$ConditionList['author']."' ":"";
		$whereAuthorId = $ConditionList['author_id']!=""?" author_id = '".$ConditionList['author_id']."' ":"";
		$whereOperator = $ConditionList['operator_loginId']!=""?" loginId = '".$ConditionList['operator_loginId']."' ":"";
		$whereStatus = $ConditionList['status']!=-1?($ConditionList['status']!=-2?" status = ".$ConditionList['status']." ":" status !=2"):"";

		$whereSid = $ConditionList['sid']!=-1?" sid = ".$ConditionList['sid']." ":"";
		$whereJid = $ConditionList['jid']!=0?" jid = ".$ConditionList['jid']." ":"";
		$whereAssess = $ConditionList['Assess']!=-1?" assess = ".$ConditionList['Assess']." ":"";
		if($ConditionList['transformed']==2)
		{
			//查询转出的投诉
			$whereTransformed = "LOCATE('to_id', comment)!=0 ";
		}
		elseif($ConditionList['transformed']==3)
		{
			//查询转入的投诉
			$whereTransformed = "LOCATE('from_id', comment)!=0 ";
		}
		else
		{
			//不查询已经转出的投诉
			$whereTransformed = "LOCATE('to_id', comment)=0 ";
		}
		if(in_array($ConditionList['status'],array(0,4)))
		{
			unset($whereAnswerStartTime,$whereAnswerEndTime);
		}
		if(in_array($ConditionList['status'],array(2,-1)))
		{
			unset($whereAnswerStartTime,$whereAnswerEndTime);
		}

		$whereCondition = array($whereComplainStartTime,$whereComplainEndTime,$whereAnswerStartTime,$whereAnswerEndTime,$whereStatus,$whereSid,$whereJid,$whereAssess,$whereAuthor,$whereAuthorId,$whereOperator,$whereTransformed);
		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}				
		$data_sql = "select count(*) as complainCount,from_unixtime(time,'%Y-%m-%d') as date,jid,sid,assess from " . DB_TABLEPRE . "complain where 1  ".$where." group by date,sid,jid,assess order by date";
		$ComplainData = $this->db->fetch_all($data_sql);
		foreach($ComplainData as $key => $value)
		{
			$returnArr['date'][$value['date']]['assess'][$value['assess']]['complainCount'] += $value['complainCount'];
			$returnArr['date'][$value['date']]['complainCount'] += $value['complainCount'];
			$returnArr['jid'][$value['jid']]['complainCount'] += $value['complainCount'];
			$returnArr['totalData']['assess'][$value['assess']]['complainCount'] += $value['complainCount'];
			$returnArr['totalData']['complainCount'] += $value['complainCount']; 			
		}
		return $returnArr;
	}
	function getRevokeComplainData($ConditionList)
	{
		$n=1;
		$page=1;
		$pagesize = 1000;
		$RevokeReason = $this->GetRevokeReason();
		$returnArr = array('RevokeReasonList'=>$RevokeReason,'RevokeIPList'=>array(),'sList'=>array(),'jList'=>array());
		
		$returnArr['RevokeReasonList']['other'] = array('content'=>"其他理由");
		$returnArr['RevokeReasonList']['none'] = array('content'=>"无理由");
		
		while($n>0)
		{
			$ComplainList = $this->getComplainList($ConditionList,$page,$pagesize);
			foreach($ComplainList['ComplainList'] as $key => $value)
			{
				$Comment = unserialize($value['comment']);
				if(isset($Comment['revoke']['revokeReason']))
				{
					if($Comment['revoke']['revokeReason']!="")
					{
						$returnArr['RevokeReasonList']['other']['revokeCount']++;
						foreach($returnArr['RevokeReasonList'] as $k => $v)
						{
							if($Comment['revoke']['revokeReason']==$v['content'])
							{
								$returnArr['RevokeReasonList'][$k]['revokeCount']++;
								$returnArr['RevokeReasonList']['other']['revokeCount']--;
							}
						}												
					}
					else
					{
						$returnArr['RevokeReasonList']['none']['revokeCount']++;
					}
				}
				else
				{
					$returnArr['RevokeReasonList']['none']['revokeCount']++;
				}

				if($Comment['revoke']['ip']!="")
				{
					$returnArr['RevokeIPList'][$Comment['revoke']['ip']]['revokeCount']++;				
				}
				else
				{
					$returnArr['RevokeIPList']["none"]['revokeCount']++;
				}
				$returnArr['sList'][$value['sid']]['revokeCount']++;
				$returnArr['jList'][$value['jid']]['revokeCount']++;
				$returnArr['totalData']['complainCount']++;
			}
			$n=count($ComplainList['ComplainList']);
			$page++;
		}
		ksort($returnArr['RevokeReasonList']);
		ksort($returnArr['RevokeIPList']);
		ksort($returnArr['sList']);
		ksort($returnArr['jList']);
		
		return $returnArr;
	}

     //根据id获取投诉信息
     function get_ComplainInfo($id,$public=1){
     	$where = $public == 1 ? "public=0 and":' ';
     	$sql = "SELECT astime,atime,status,countdown_time,receive_time,id,sid,sname,jname,order_id,good_id,
     			author,author_id,time,rtime,title,description,photo,contact,real_name,atime,assess,view,
     			resolve_photo,resolve,qtype,jid,loginId,comment,ip,public,sync
     	 		 FROM ".DB_TABLEPRE."complain where $where id=$id ";
     	return $this->db->fetch_first($sql);
     }
     //根据id获取投诉回答信息
     function get_ComplainSyncInfo($id){
	   	 $sql   = "SELECT * FROM ".DB_TABLEPRE."sync where scid=$id";
	     return $this->db->fetch_first($sql);
     }
     //根据id获取投诉回答信息
     function get_ComplainAnInfo($id){
	   	 $sql   = "SELECT id,qid,csn,content,time,contact FROM ".DB_TABLEPRE."complain_answer where qid=$id";
	     return $this->db->fetch_first($sql);
     }

    function insertComplain($complainInfo)
    {
    	foreach($complainInfo as $key => $value)
    	{
    		$array_key[$key] = $key;
    		$array_value[$key] = "'".$value."'";
    	}
    	$sql = "insert into " .DB_TABLEPRE."complain (".implode($array_key,",").") values (".implode($array_value,",").")";
    	return $this->db->query($sql);
    } 
    function updateComplain($id,$complainInfo)
    {
    	foreach($complainInfo as $key => $value)
    	{
    		$txt[$key] = "`".$key."`='".$value."'";
    	}
    	$sql = "update ".DB_TABLEPRE."complain set ".implode($txt,",")." where id = ".intval($id);
		return $this->db->query($sql);
    }
    function getJList($sid)
    {
        if($sid<0)
		{
			$sid = 0;
		}
			$url = "http://complain.5173esb.com/sc/GetStatusModes.aspx?sid=".$sid;
			$return = file_get_contents($url);
			$return = urldecode($return);
			$return = substr($return,1);
			$return = substr($return,0,strlen($return)-1);
			$t = explode("}",$return);
			foreach($t as $key => $value)
			{
				$value.="}";
				$a = (json_decode($value,true));
				if(is_array($a))
				{
					$returnArr[$a['id']] = $a['name'];
				}
			}

		$returnArr[0] = "全部";
		ksort($returnArr);
		return $returnArr;
	}
    //获取投诉撤销理由
    function GetRevokeReason()
    {
    	$sql = "select * from ".DB_TABLEPRE."complain_revoke_reason";
    	$RevokeReason = $this->db->fetch_all($sql);
    	return $RevokeReason;
    }
}

?>
