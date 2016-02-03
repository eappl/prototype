<?php
!defined('IN_TIPASK') && exit('Access Denied');
class testmodel extends base{

    var $db;
    var $base;
    var $table = "self_test";

    function testmodel(&$base) 
    {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }
    
    function db_self_test()
    {
        $rand = mt_rand(1,10);
        $text = "数据库连接随机读写自检：<br>";
        $text.= "生成随机数：".$rand."<br>";
        $sql = "insert into self_test (value) values (".$rand.")";
        $add = $this->db->query($sql);
        $text.= "写入数据成功？".($this->db->insert_id()>0?"是":"否")."<br>";
        $sql = "select value from self_test where id = ".$this->db->insert_id();
        $value = $this->db->result_first($sql);
        $text.= "数据取回内容为：".$value."<br>";
        $text.= "验证结果为：".($value==$rand?"是":"否");
        return $text;
    }
    function pdo_self_test()
    {
        $rand = mt_rand(1,10);
        $text = "数据库PDO随机读写自检：<br>";
        $text.= "生成随机数：".$rand."<br>";
        $table_name = $this->base->getDbTable($this->table);

        $add = $this->pdo->insert($table_name,array('value'=>$rand));
        $text.= "写入数据成功？".($add>0?"是":"否")."<br>";
        $sql = "select value from ".$table_name." where `id` =".$add;
        $value = $this->pdo->getOne($sql);
        $text.= "数据取回内容为：".$value."<br>";
        $text.= "验证结果为：".($value==$rand?"是":"否");
        return $text;
    }
    
    function cache_self_test()
    {
        $rand = mt_rand(1,10);
        $text = "缓存随机读写自检：<br>";
        $text.= "生成随机数：".$rand."<br>";
        $set = $this->cache->set("self_test",$rand,600);
        $text.= "写入数据成功？".($set>0?"是":"否")."<br>";
        $value = $this->cache->get("self_test");
        $text.= "数据取回内容为：".$value."<br>";
        $text.= "验证结果为：".($value==$rand?"是":"否");
        return $text;
    }  
}
?>
