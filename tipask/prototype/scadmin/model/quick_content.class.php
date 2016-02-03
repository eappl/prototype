<?php

!defined('IN_TIPASK') && exit('Access Denied');

class quick_contentmodel {

    var $db;
    var $base;

    function quick_contentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    function get_list($cid){
    	return $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "quick_content WHERE cid = '$cid' ORDER BY rank");
    }

    function add($content,$cid=0,$id=0){
    	if($id)
    	   $this->db->query("UPDATE " . DB_TABLEPRE . "quick_content SET content='$content' WHERE id = '$id'");
    	else 
    	   $this->db->query("INSERT INTO " . DB_TABLEPRE . "quick_content SET content='$content',cid='$cid'");
    }
    
    function remove($id=0){
    	 $id && $this->db->query("DELETE FROM " . DB_TABLEPRE . "quick_content WHERE id = '$id'");  		
    }
   
}

?>