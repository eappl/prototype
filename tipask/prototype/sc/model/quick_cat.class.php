<?php

!defined('IN_TIPASK') && exit('Access Denied');

class quick_catmodel {

    var $db;
    var $base;

    function quick_catmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    function get_list(){
    	if(isset($this->base->ask_login_name)){
    		return $this->db->fetch_all("SELECT id,title,rank,login_name FROM " . DB_TABLEPRE . "quick_cat WHERE login_name='".$this->base->ask_login_name."' ORDER BY rank ");
    	}   	
    }

    function add($title,$id=0){
    	if(isset($this->base->ask_login_name)){
    		if($id)
    	       $this->db->query("UPDATE " . DB_TABLEPRE . "quick_cat SET title='$title' WHERE id = '$id'");
	    	else 
	    	   $this->db->query("INSERT INTO " . DB_TABLEPRE . "quick_cat SET title='$title',login_name='".$this->base->ask_login_name."'");
    	}  	
    }
    
    function remove($id){
    	 $this->db->query("DELETE FROM " . DB_TABLEPRE . "quick_cat WHERE id = '$id'");
    	 $this->db->query("DELETE FROM " . DB_TABLEPRE . "quick_content WHERE cid = '$id'");   	
    }
    
	function get_categories_list(){
		 if(isset($this->base->ask_login_name)){
		 	$cat_arr = array();
		     $sql = "SELECT id,title,rank FROM " . DB_TABLEPRE ."quick_cat WHERE login_name='".$this->base->ask_login_name."' ORDER BY rank";
		     $res = $this->db->fetch_all($sql,'id');
		     foreach ($res as $row){   
		        $cat_arr[$row['id']]['id']   = $row['id'];
		        $cat_arr[$row['id']]['title'] = $row['title'];      
		        $cat_arr[$row['id']]['child'] = $this->get_child_list($row['id']);          
		     }
	
		     return $cat_arr;
		 }	 
	}
	
	function get_child_list($tree_id = 0){
	    $three_arr = array();
        $child_sql = "SELECT id,content,cid,rank FROM " . DB_TABLEPRE ."quick_content WHERE cid = '$tree_id' ORDER BY rank";
        $res = $this->db->fetch_all($child_sql,'id');      
        foreach ($res as $row){          
            $three_arr[$row['id']]['id']   = $row['id'];
            $three_arr[$row['id']]['alt']   = str_replace(" ", "&nbsp;", htmlspecialchars($row['content']));
            $three_arr[$row['id']]['content'] = cutstr($row['content'],20); 
        }
        
	    return $three_arr;
	}
	   
   
}

?>