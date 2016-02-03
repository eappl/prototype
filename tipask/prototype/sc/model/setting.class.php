<?php

!defined('IN_TIPASK') && exit('Access Denied');

class settingmodel extends base{

    var $db;
    var $base;
	var $table_setting = 'ask_setting';
	
    function settingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
        $this->pdo = $this->base->init_pdo($this->table_setting);
        
    }
    // 插入一条新数据
    function replace($dataArr)
    {
    	$table_name = $this->getDbTable($this->table_setting);
    	$result = $this->pdo->replace($table_name,$dataArr);
    	return $result;
    }
    
    function update($setting) {
        foreach($setting as $key=>$value) {
            $this->db->query("REPLACE INTO ".DB_TABLEPRE."setting (k,v) VALUES ('$key','$value')");
        }
        $this->base->cache->rm('setting');
    }
    
    //获取咨询或投诉建议类型
    function getType($type=''){
    	$cid = '';
    	if($type == 1) {
    
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE name='咨询'";
    		$cache_key = md5($sql);
    		$cache_data = $this->cache->get($cache_key);
    		if(false !== $cache_data) return $cache_data;
    
    		$cid = $this->db->result_first($sql);
    		if(!empty($cid)) {
    			$this->cache->set($cache_key,$cid,2592000);
    		}
    	}elseif($type == 2) {
    
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE name='建议与意见'";
    		$cache_key = md5($sql);
    		$cache_data = $this->cache->get($cache_key);
    		if(false !== $cache_data) return $cache_data;
    
    		$cid = $this->db->result_first($sql);
    		if(!empty($cid)) {
    			$this->cache->set($cache_key,$cid,2592000);
    		}
    	}elseif($type==3) {
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE name='投诉'";
    		$cache_key = md5($sql);
    		$cache_data = $this->cache->get($cache_key);
    		if(false !== $cache_data) return $cache_data;
    
    		$cid = $this->db->result_first($sql);
    		if(!empty($cid)) {
    			$this->cache->set($cache_key,$cid,2592000);
    		}
    	}
    	return $cid;
    }
}

?>