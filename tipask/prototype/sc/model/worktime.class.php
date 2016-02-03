<?php

!defined('IN_TIPASK') && exit('Access Denied');

class worktimemodel {

    var $db;
    var $base;

    function worktimemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }   
    
    //当天是否在班
    function isToday($user,$date){
    	$info = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "worktime WHERE login_name='".$user."' AND login_time='".$date."'");
    	if(!empty($info)) return $info;
    	return false;	
    	
    }
    
    //查询操作客服的前一天的在班记录，用于统计
    function lastToday($users){
    	$list = $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "worktime WHERE login_name='$users' ORDER BY login_time DESC LIMIT 1");
    	if(!empty($list)) return $list;
    	return false;
    }
    
    function get_list($where=''){	
    	$list = $this->db->fetch_all("SELECT login_name,SUM(busy_time) AS total_busy,SUM(onjob_time) AS total_job FROM " . DB_TABLEPRE . "worktime $where  GROUP BY login_name");
    	if(empty($list)) return false;
    	return $list;
    }
    
    function get_where($start_time='',$end_time='',$user_name=''){
    	$where = ' WHERE 1 ';
    	if($start_time != ''){
    		$where.= " AND login_time>='".date('Y-m-d',$start_time)."'";
    	}
    	if($end_time != ''){
    		$where.= " AND login_time<='".date('Y-m-d',$end_time)."'";
    	}
    	if($user_name != ''){
    		$where.= " AND login_name='".$user_name."'";
    	}
    	return $where;
    }
    
    function getHour($seconds){
    	if(intval($seconds)<=0){
    		return '';
    	}else{
    		$h = floor($seconds/3600);
    		$m = floor($seconds%3600/60);
    		$s = $seconds%3600%60;
    		$m<10 && $m='0'.$m;
    		$s<10 && $s='0'.$s;
    		return $h.': '.$m.': '.$s;
    	}
    
    }

}

?>
