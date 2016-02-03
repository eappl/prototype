<?php

!defined('IN_TIPASK') && exit('Access Denied');

class settingmodel {

    var $db;
    var $base;

    function settingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }


    function update($setting) {
        foreach($setting as $key=>$value) {
            $this->db->query("REPLACE INTO ".DB_TABLEPRE."setting (k,v) VALUES ('$key','$value')");
        }
        $this->base->cache->rm('setting');
    }
    
    function getHotQuestion(){
    	$rt = array();
    	$hotQue = $this->db->result_first("SELECT v FROM ".DB_TABLEPRE."setting WHERE k='hotQue'");   	
    	if($hotQue != '')
    	{
    		$hotQue = explode(',',$hotQue);
    		$hotQue = array_unique($hotQue);
    		foreach ($hotQue as $k => &$v){
    			if($v ==''){
    				unset($hotQue[$k]);
    			}
    		}
    		$id = implode(',',$hotQue);
    		$where = " AND q.id IN (".$id.") ";
    		$sql = 'SELECT
				  id,cid,title,time,views,status,q_handle_status,atime,qtype
				FROM ' . DB_TABLEPRE . 'question AS q
				WHERE pid = 0
				    AND revocation = 0 '.$where.
    			'GROUP BY id ORDER BY time DESC';
    		
    		$rt = $this->db->fetch_all($sql);
    		$zx = $this->getType(1);
    		$jy = $this->getType(2);
    		$ts = $this->getType(3);
    		foreach($rt as $k=>$v){
    			if($rt[$k]['cid'] == $zx){
    				$rt[$k]['type'] = 'zx_ico';
    			}elseif($rt[$k]['cid'] == $ts){
    				$rt[$k]['type'] = 'ts_ico';
    			}elseif($rt[$k]['cid'] == $jy){
    				$rt[$k]['type'] = 'jy_ico';
    			}
    			$rt[$k]['time']  = $this->base->timeToText($v['time']);
    			$rt[$k]['atime'] = !empty($v['atime']) ? $this->base->timeLagToText($v['time'],$v['atime']) : '';
    			$rt[$k]['views'] = intval($v['views']);
    		}
    	}	
    	return $rt;   	
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