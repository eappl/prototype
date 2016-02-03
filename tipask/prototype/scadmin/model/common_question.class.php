<?php

!defined('IN_TIPASK') && exit('Access Denied');

class common_questionmodel {

    var $db;
    var $base;

    function common_questionmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

 /* 获取分类信息 */

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "common_question WHERE id='$id'");
    }
    
    function set($id,$number=0,$url='',$title=''){
    	$this->db->query("UPDATE " . DB_TABLEPRE . "common_question SET number='$number',url='$url',title='$title',display=0 WHERE id=$id");
    }
 		/**
          *
          * @param  bool $tag 判断是否更新到首页 默认不更新
          * @return unknown
          */
    
        function get_common_list($tag=false) {
        	$where = $tag ? 'WHERE display=1' : '';
            $commonlist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "common_question $where ORDER BY number ASC,id DESC","id");
            return $commonlist;
        }
        
        function save_common($number=0,$url='',$title=''){
        	$this->db->query('INSERT INTO `' . DB_TABLEPRE . "common_question`(`number`,`url`,`title`) values (" . $number . ",'$url','$title')");
        }
        
        function remove($id) {
        	$this->db->query("DELETE FROM " . DB_TABLEPRE . "common_question WHERE id='$id'");
        }
        
        function updateToHome(){
        	$this->db->query("UPDATE " . DB_TABLEPRE . "common_question SET display = 1");
        }
}

?>
