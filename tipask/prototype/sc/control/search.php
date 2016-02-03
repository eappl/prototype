<?php
define('IN_TIPASK', TRUE);
define('TIPASK_ROOT', dirname(dirname(__FILE__)));
ini_set('include_path', dirname(dirname(__FILE__)));

require 'config.php';
require 'lib/db.class.php';
require 'lib/config.class.php';
require 'model/base.class.php';

//删除搜索的计划任务
class search extends base {

    function search() {
        $this->init_db();
    }

    function drop_search() {
    	set_time_limit(0);
    	//判断数据库中此表是否存在
    	$temp_question = 'temp_question';   			
    	$query=$this->db->query("SHOW TABLES LIKE '".$temp_question."'");
    	$rows = $this->db->num_rows($query);
    	if($rows == 1){//存在此表
    		$query = $this->db->query("SELECT id FROM ".$temp_question);
    		while($row = $this->db->fetch_array($query)){
    			//删除Solr服务器上的对应的问题id
    			try{
    				$this->delete_search($row['id']);
    			}catch(Exception $e){}    					
    		}
    				
    	}
    }			    			  	    
}

$crontab = new search();
$crontab->drop_search();
?>