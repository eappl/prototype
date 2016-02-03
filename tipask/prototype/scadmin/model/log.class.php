<?php

!defined('IN_TIPASK') && exit('Access Denied');

class logmodel {

    var $db;
    var $base;
	var $table_log = 'ask_log';

    function logmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    //获取相关问题的日志记录信息
    function Get_List($qid)
	{
    	$log = array();
		$questionInfo = $_ENV['question']->get($qid);
		$startDate = date("Y-m-d",$questionInfo['time']);
		$endDate = date("Y-m-d",time());
		$LogList = array();
		while($startDate <= $endDate)
		{
			$table_name = $this->base->getDbTable($this->table_log)."_".date("Ym",strtotime($startDate));
			$list = $this->db->fetch_all("SELECT * FROM ".$table_name." WHERE qid=".intval($qid)." order by time,id desc");
			if(!empty($list))
			{
				foreach($list as $k => $v)
				{
					$v['time'] = $v['time'] == 0?'':date('Y-m-d H:i:s',$v['time']);
					$Loglist[$v['id']] = $v;
				}
			}
			$startDate = date("Y-m-d",strtotime("+1 month",strtotime($startDate)));
		}

    	return $Loglist;
    }
	function getLogList($ConditionList,$page,$pagesize)
	{
		$whereStartTime = $ConditionList['StartDate']?" time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereOperator = $ConditionList['scopid']!=""?" scopid = '".$ConditionList['scopid']."' ":"";
		$whereAuthor = $ConditionList['AuthorName']!=""?" AuthorName = '".$ConditionList['AuthorName']."' ":"";
		if($ConditionList['operator']=='0')
		{
			$whereOperator = "";
		}
		elseif($ConditionList['operator']=='-1')
		{
			$operator_list = $_ENV['operator']->getList(0,0);
			foreach($operator_list as $key => $value)
			{
				$t[$key] = "'".$value['login_name']."'";
			}
			$whereOperator = " user not in (".implode($t,",").") ";
		}
		elseif($ConditionList['operator']=='-2')
		{
			$whereOperator = " user = '游客'";
		}
		else
		{
			$whereOperator = " user = '".$ConditionList['operator']."'";
		}
		
		$whereLogType = $ConditionList['log_type']!='0'?" message like '".$ConditionList['log_type']."%' ":"";
		$whereQuestion = $ConditionList['QuestionId']!='0'?" qid = ".$ConditionList['QuestionId']." ":"";
		$whereEndTime = $ConditionList['EndDate']?" time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";
		$table_name = $this->base->getDbTable($this->table_log);
		$Suffix = '_'.date("Ym",strtotime($ConditionList['StartDate']));	
		$table_name.=$Suffix;
		$whereCondition = array($whereStartTime,$whereEndTime,$whereLogType,$whereOperator,$whereQuestion,$whereAuthor);
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
		$count_sql = "select count(1) as log_count from $table_name where 1 ".$where;
		$LogCount = $this->db->fetch_first($count_sql);
		if($LogCount['log_count']>0)
		{
			$sql = "select * from $table_name where 1  ".$where." order by time desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("LogCount"=>$LogCount['log_count'],"LogList"=>$rs);
		}
		else
		{
			$returnArr = array("LogCount"=>0,"LogList"=>array());
		}
		return $returnArr;
	}

}

?>