<?php

!defined('IN_TIPASK') && exit('Access Denied');

class qcontentmodel extends base{

    var $db;
    var $base;
    var $cache;

    function qcontentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
    //获取所有用户自选类型
    function GetAllQcontent($allQuestion=1,$p=1)
    {           	
    	$wherep = ($p>0)?" and pid = 0 ":" ";
    	$sql = "SELECT * FROM ".DB_TABLEPRE."qcontent WHERE 1 $is_visiable $wherep ORDER BY displayOrder";
    	$qcontent = $this->db->fetch_all($sql,'id');
    	if($allQuestion == 1)
    	{
    		$qcontent[0] = array('id' => 0,'name' => '全部类型', 'pid' => 0); 
    	 } 
    	return $qcontent;
    }	
	function updateQcontent($id,$qcontentInfo)
	{
		foreach($qcontentInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."qcontent set ".implode($txt,",")." where id = ".intval($id);
		return $this->db->query($sql);
	}
	function insertQcontent($qcontentInfo)
	{
		foreach($qcontentInfo as $key => $value)
		{
			$array_key[$key] = $key;
			$array_value[$key] = "'".$value."'";			
		}
		$sql = "insert into " .DB_TABLEPRE."qcontent (".implode($array_key,",").") values (".implode($array_value,",").")";
		return $this->db->query($sql);
	}
    //获取所有用户自选类型
    function getQcontent($id,$fields = "*")
    {           	
    	$qcontent_list = array();
    	$sql = "SELECT ".$fields." FROM ".DB_TABLEPRE."qcontent WHERE id = $id";
		$query = $this->db->query($sql);
    	$data = $this->db->fetch_array($query);
    	return $data;
    }
    function GetSubList($id)
    {
    	$sql = "SELECT * FROM ".DB_TABLEPRE."qcontent WHERE pid = ".$id;
    	$data = $this->db->fetch_all($sql);
    	return $data;
    }
	function deleteQcontent($id)
	{
		$sql = "delete from  ".DB_TABLEPRE."qcontent where id = ".intval($id);
		return $this->db->query($sql);
	}
	function Replace($QContentA,$QContentB)
	{
		$this->db->begin();
		$TT = $QContentA['displayOrder'];
		$QContentA['displayOrder'] = $QContentB['displayOrder'];
		$QContentB['displayOrder'] = $TT;
		$UpdateA = $this->updateQcontent($QContentA['id'],array('displayOrder'=>$QContentA['displayOrder']));
		$UpdateB = $this->updateQcontent($QContentB['id'],array('displayOrder'=>$QContentB['displayOrder']));
		if($UpdateA && $UpdateB)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollback();
			return false;
		}

	}
}
?>
