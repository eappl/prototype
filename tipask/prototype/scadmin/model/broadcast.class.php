<?php

!defined('IN_TIPASK') && exit('Access Denied');

class broadcastmodel extends base{

    var $db;
    var $base;
    var $cache;

    function broadcastmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
    //获取公告列表
    function getBroadCastList($ConditionList,$page,$pagesize)
    {           	
		$whereStartTime = $ConditionList['StartTime']?" AddTime >= ".strtotime($ConditionList['StartTime'])." ":"";
		$whereEndTime = $ConditionList['EndTime']?" AddTime < ".(strtotime($ConditionList['EndTime'])+86400)." ":"";
		$whereZone = $ConditionList['BroadCastZone']>=0?" BroadCastZone =".$ConditionList['BroadCastZone']." ":"";
		$time = time();
		if($ConditionList['BroadCastStatus']==0)
		{
			$whereStatus = "";
		}
		elseif($ConditionList['BroadCastStatus']==1)
		{			
			$whereStatus = " StartTime <= $time and EndTime >= $time ";
		}
		elseif($ConditionList['BroadCastStatus']==2)
		{
			$whereStatus = " EndTime < $time ";	
		}
		elseif($ConditionList['BroadCastStatus']==3)
		{
			$whereStatus = " BroadCastStatus = 3";
		}
		elseif($ConditionList['BroadCastStatus']==4)
		{
			$whereStatus = " StartTime > $time ";	
		}
		$whereCondition = array($whereStartTime,$whereEndTime,$whereZone,$whereStatus);
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
		$count_sql = "select count(*) from broadcast where 1 ".$where;
		$BroadCastCount = $this->db->result_first($count_sql);
		if($BroadCastCount>0)
		{
			$sql = "select * from broadcast where 1 ".$where." order by id desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("BroadCastCount"=>$BroadCastCount,"BroadCastList"=>$rs);
		}
		else
		{
			$returnArr = array("BroadCastCount"=>0,"BroadCastList"=>array());
		}
		return $returnArr;
    }	
	function updateBroadCast($id,$BroadCastInfo)
	{
		foreach($BroadCastInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update broadcast set ".implode($txt,",")." where Id = ".intval($id);
		return $this->db->query($sql);
	}
	function insertBroadCast($BroadCastInfo)
	{
		foreach($BroadCastInfo as $key => $value)
		{
			$array_key[$key] = $key;
			$array_value[$key] = "'".$value."'";			
		}
		$sql = "insert into broadcast (".implode($array_key,",").") values (".implode($array_value,",").")";
		return $this->db->query($sql);
	}
	function deleteBroadCast($id)
	{
		$sql = "delete from BroadCast where Id = ".intval($id);
		return $this->db->query($sql);
	}   
    //获取所有用户自选类型
    function GetBroadCast($id,$fields = "*")
    {           	
    	$sql = "SELECT $fields from broadcast WHERE Id = $id";
    	$query = $this->db->query($sql);
    	$data = $this->db->fetch_array($query);   	
    	return $data;
    }
}
?>
