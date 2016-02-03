<?php

!defined('IN_TIPASK') && exit('Access Denied');

class postmodel  extends base{

    var $db;
    var $base;
	var $table_post = "ask_post";
	
    function postmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
		$this->pdo = $this->base->init_pdo($this->table_post);
		}
    function get($id) {
    	$table_name = $this->base->getDbTable($this->table_post);
    	$Post = $this->pdo->selectRow($table_name, "*", '`id` = ?', $id);
    	return $Post;
    }    

}

?>
