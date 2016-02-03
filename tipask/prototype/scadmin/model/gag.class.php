<?php

!defined('IN_TIPASK') && exit('Access Denied');

class gagmodel {

    var $db;
    var $base;

    function gagmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    function getList($start=0, $limit=20,$where=''){
    	$sql = "select * from ". DB_TABLEPRE . "gag $where";
    	$sql .=" ORDER BY time DESC";         
    	$sql .=" LIMIT $start,$limit";
    	               
    	$gaglist = $this->db->fetch_all($sql,"id");
        return $gaglist;
    }  
    
    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "gag WHERE id='$id'");
    }
    
    function getNum($where=''){
    	return $this->db->result_first("SELECT COUNT(*) num FROM ".DB_TABLEPRE."gag $where");
    }
    
    function add($login_name,$operator,$time){
    	
    	$this->db->query("INSERT INTO " . DB_TABLEPRE . "gag SET login_name='$login_name',operator='$operator',time='$time'");    
    }
    
    function remove($gid){  	
    	$this->db->query("DELETE FROM `" . DB_TABLEPRE . "gag` WHERE `id` = $gid");
    }
    
    function getWhere($login_name_search=''){
    	$where = " where 1 ";   	
    	$login_name_search != '' && $where.=" and login_name like '%$login_name_search%'";           	
    	return $where;       
    } 

}

?>
