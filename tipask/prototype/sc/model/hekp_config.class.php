<?php

!defined('IN_TIPASK') && exit('Access Denied');

class hekp_configmodel {

    var $db;
    var $base;

    function hekp_configmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    /**
     * 更新传过来的协助处理时间
     * @param unknown_type $data
     */
	function hekpUpdate($data){
		foreach($data as $key=>$value){
			$k = substr($key,1);
			$v = intval($value);
			$this->db->query('REPLACE INTO ' . DB_TABLEPRE . "hekp_config (cid,time) VALUES('$k','$v')");
		}
	}
	/*获取所有数据*/
	function getAll(){
		return  $this->db->fetch_all('SELECT * FROM ' . DB_TABLEPRE . 'hekp_config','cid');
	}
}
?>
