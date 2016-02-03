<?php

!defined('IN_TIPASK') && exit('Access Denied');

class postmodel {

    var $db;
    var $base;

    function postmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    function get(){
    	$postlist = $this->db->fetch_all("SELECT * FROM `" . DB_TABLEPRE . "post` WHERE 1=1 ");
    	return $postlist;
    }
 	function updatePost($id,$postInfo)
	{
		foreach($postInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."post set ".implode($txt,",")." where id = ".intval($id);
		return $this->db->query($sql);
	}
	function insertPost($postInfo)
	{
		foreach($postInfo as $key => $value)
		{
			$array_key[$key] = $key;
			$array_value[$key] = "'".$value."'";			
		}
		$sql = "insert into " .DB_TABLEPRE."post (".implode($array_key,",").") values (".implode($array_value,",").")";
		return $this->db->query($sql);
	}       
    function remove($pid){
    	$this->db->query("DELETE FROM `" . DB_TABLEPRE . "post` WHERE `id` = $pid");
    }
    
    function getOptions(){
    	$options_list = array();
    	$postlist = $this->db->fetch_all("SELECT * FROM `" . DB_TABLEPRE . "post` WHERE 1=1 ");
		foreach($postlist as $key => $val){
			$options_list[$val['id']] = $val['name'];
		}
    	return $options_list;
    }

}

?>
