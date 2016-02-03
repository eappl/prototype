<?php

!defined('IN_TIPASK') && exit('Access Denied');

class usermodel extends base{

    var $db;
    var $base;
	var $table_gag = "ask_gag";
    function usermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
        $this->pdo = $this->base->init_pdo($this->table_gag);

    }
    
    function getGag($user_name) 
	{
    	$table_name = $this->base->getDbTable($this->table_gag);
    	$sql = "select * from $table_name where `login_name` = '".$user_name."'";
		$GagLog = $this->pdo->getAll($sql);
		return $GagLog;
    }
}

?>
