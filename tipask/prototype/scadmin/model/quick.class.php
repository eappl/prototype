<?php

!defined('IN_TIPASK') && exit('Access Denied');

class quickmodel extends base{

    var $db;
    var $base;
    var $cache;

    function quickmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
    //获取所有用户自选类型
    function GetAllQuicklink($p=1)
    {           	
    	$wherep = ($p>0)?" and Parent = 0 ":" ";
    	$sql = "SELECT * FROM quicklink WHERE 1 $wherep ORDER BY Id";
    	$quicklink = $this->db->fetch_all($sql,'Id');
		ksort($quicklink);
    	return $quicklink;
    }	
	function updateQuicklink($id,$quicklinkInfo)
	{
		foreach($quicklinkInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update quicklink set ".implode($txt,",")." where Id = ".intval($id);
		return $this->db->query($sql);
	}
	function insertQuicklink($quicklinkInfo)
	{
		foreach($quicklinkInfo as $key => $value)
		{
			$array_key[$key] = $key;
			$array_value[$key] = "'".$value."'";			
		}
		$sql = "insert into quicklink (".implode($array_key,",").") values (".implode($array_value,",").")";
		return $this->db->query($sql);
	}
	function deleteQuicklink($id)
	{
		$sql = "delete from quicklink where Id = ".intval($id);
		return $this->db->query($sql);
	}
	
    
    //获取所有用户自选类型
    function GetQuicklink($id,$fields = "*")
    {           	
    	$qtype_list = array();
    	$sql = "SELECT ".$fields." FROM quicklink WHERE Id = $id";
    	$query = $this->db->query($sql);
    	$data = $this->db->fetch_array($query);
    	
    	return $data;
    }
    //获取指定顶层目录的下层目录
    //$parent：上册目录的目录ID
    function getSubLink($parent)
    {
		$sql = "select * from quicklink where `Parent` = ".intval($parent)." order by Id,LinkName";
		$query = $this->db->query($sql);
		while($data = $this->db->fetch_array($query)) 
		{
			if(!isset($QuickLink))
			{
				$QuickLink = array();
			}
			$QuickLink[$data['Id']] = $data;
		}
		return $QuickLink;								
    }
}
?>
