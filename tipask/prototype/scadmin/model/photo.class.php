<?php
/**
 * 生成后台个人设置setting 中的图片
 */
!defined('IN_TIPASK') && exit('Access Denied');

class photomodel {

    var $db;
    var $base;

    function photomodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    function showphoto(&$data){
    	header('Content-type: image/gif');
    	echo $data;
    }
}
?>
