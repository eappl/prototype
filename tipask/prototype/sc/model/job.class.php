<?php

!defined('IN_TIPASK') && exit('Access Denied');

class jobmodel {

    var $db;
    var $base;

    function jobmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get(){
    	$joblist = $this->db->fetch_all("SELECT * FROM `" . DB_TABLEPRE . "job` WHERE 1=1 ");
    	return $joblist;
    }
    
    function set($pid,$name){
    	$this->db->update_field("job","name",$name,"id = ".$pid);
    }
    
    function add($name){
    	$this->db->query("INSERT INTO " . DB_TABLEPRE . "job SET name='$name'");
    }
    
    function remove($pid){
    	$this->db->query("DELETE FROM `" . DB_TABLEPRE . "job` WHERE `id` = $pid");
    }
    
    function getInfo($pid){
    	return $this->db->fetch_first("SELECT * FROM `" . DB_TABLEPRE . "job` WHERE `id` = $pid");
    	
    }
    
    
    function getOptions(){
    	$options_list = array();
    	$joblist = $this->db->fetch_all("SELECT * FROM `" . DB_TABLEPRE . "job` WHERE 1=1 ");
		foreach($joblist as $key => $val){
			$options_list[$val['id']] = $val['name'];
		}
    	return $options_list;
    }
    /**
     * 获取所有岗位生成select下拉菜单
     */
    function get_select_job(){
    	$strlist = '<select name="job"><option value="-1">全部</option>';
    	$joblist = $this->db->fetch_all("SELECT id,name FROM `" . DB_TABLEPRE . "job` WHERE 1=1 ");
    	foreach($joblist as $value){
    		$strlist .= "<option value=\"{$value['id']}\">{$value['name']}</option>";
    	}
    	$strlist .= '</select>';
    			return $strlist;
    }
    
}

?>
