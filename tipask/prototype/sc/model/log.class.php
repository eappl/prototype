<?php

!defined('IN_TIPASK') && exit('Access Denied');

class logmodel {

    var $db;
    var $base;

    function logmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    //获取相关问题的日志记录信息
    function Get_List($qid){
    	$log = array();
    	$list = $this->db->fetch_all("SELECT * FROM ".DB_TABLEPRE."log WHERE qid=".intval($qid));
    	if(!empty($list)){
    		foreach($list as $k => $v)
    			$list[$k]['time'] = $v['time'] == 0?'':date('Y-m-d H:i:s',$v['time']);
    	}
    	return $list;
    }

}

?>