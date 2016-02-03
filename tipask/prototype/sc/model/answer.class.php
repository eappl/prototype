<?php

!defined('IN_TIPASK') && exit('Access Denied');

class answermodel {

    var $db;
    var $base;
    
    function answermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    /* 根据问题id找出答案*/

    function get($qid) {
		$sql = "SELECT * FROM " . DB_TABLEPRE . "answer WHERE qid=$qid";
        return $this->db->fetch_first($sql);
    }
    
    /**
     * 获取接单查询条件
     * @return string
     */	
    function getWhere($login_name_search='',$busy_search=-1,$bill_search=-1){
    	
    	$where = " where 1 ";
    	$login_name_search != '' && $where.="and an.author='$login_name_search'";
    	$busy_search != -1 && $where.=" and o.isbusy='$busy_search'";
    	if($bill_search == 1){
    		$where.=" and (an.num + an.num_add)!=0 ";
    	}elseif($bill_search == 0){
    		$where.=" and (an.num + an.num_add)=0 ";
    	}
    	return $where;
    }
    /**
     * 获取接单列表
     * @return unknown
     */
    function getList($start=0, $limit=20,$con=''){
    	$sql = 'SELECT an.author,an.num,an.num_add,o.login_name,o.name,o.isbusy FROM ' .
    	 DB_TABLEPRE . 'operator as o LEFT JOIN ' . DB_TABLEPRE . 'author_num AS an ON an.author = o.login_name ' .  $con;
    	$sql .= " LIMIT $start,$limit";
    	$answerlist = $this->db->fetch_all($sql);
    	return $answerlist;
    }
    // 获取节单总数
    function getNum($where){
    	$sql = 'SELECT COUNT(*) FROM ' .
    	 DB_TABLEPRE . 'operator as o LEFT JOIN ' . DB_TABLEPRE . 'author_num AS an ON an.author = o.login_name ' .  $where;
    	return $this->db->result_first($sql);
    }
    
    //根据回答客服统计处理问题数
    function get_count_by_author($start_time='',$end_time='',$user_name=''){
    	$where = ' WHERE is_delete=0 ';
    	if($start_time != ''){
    		$where.= " AND time>='".$start_time."'";
    	}
    	if($end_time != ''){
    		$where.= " AND time<='".$end_time."'";
    	}
    	if($user_name != ''){
    		$where.= " AND author='".$user_name."'";
    	}
    	$arr = array();
    	$list = $this->db->fetch_all("SELECT author,COUNT(*) AS num FROM " . DB_TABLEPRE . "answer $where GROUP BY author");
    	if(!empty($list)){
    		foreach($list as $val){
    			$arr[$val['author']] = $val['num'];
    		}
    	}   	
    	return $arr;
    }
    
}
	
?>
