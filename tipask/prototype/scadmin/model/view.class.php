<?php

!defined('IN_TIPASK') && exit('Access Denied');

class viewmodel extends base{

    var $db;
    var $base;
    var $cache;

    function viewmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    //获取所有用户自选类型
    function GetAllPage()
    {           	
    	$sql = "SELECT * FROM page_view_config WHERE 1 ORDER BY PageId";
		$qtype = $this->db->fetch_all($sql,'PageId');
    	ksort($qtype);
    	return $qtype;
    }
	//获取投诉列表
	function getPageViewDetail($ConditionList,$page,$pagesize)
	{
		$whereStartTime = $ConditionList['StartDate']?" Time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereEndTime = $ConditionList['EndDate']?" Time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";

		$wherePage = $ConditionList['PageId']!=0?" PageId = ".$ConditionList['PageId']." ":"";
		$whereIP = $ConditionList['IP']!=0?" ViewIP = ".$ConditionList['IP']." ":"";

		$whereCondition = array($whereStartTime,$whereEndTime,$wherePage,$whereIP);
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
		$TableToProcess = "page_view_log_".date("Ym",strtotime($ConditionList['StartDate']));
		$count_sql = "select count(*) from $TableToProcess where 1 ".$where;
		$PageViewCount = $this->db->result_first($count_sql);
		if($PageViewCount>0)
		{
			$sql = "select * from $TableToProcess where 1 ".$where." order by id desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("PageViewCount"=>$PageViewCount,"PageViewList"=>$rs);
		}
		else
		{
			$returnArr = array("PageViewCount"=>0,"PageViewList"=>array());
		}
		return $returnArr;
	}
}
?>
