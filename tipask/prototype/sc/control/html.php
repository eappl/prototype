<?php

!defined('IN_TIPASK') && exit('Access Denied');

class htmlcontrol extends base {

    function htmlcontrol(& $get, & $post) {
    	
        $this->base(& $get, & $post);
        $this->load("test");
    }
    function onf5()
    {
    	$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
        $serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
        $fileupload = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '<font color="red">否</font>';       
        $dbversion = $this->db->version();
        $magic_quote_gpc = get_magic_quotes_gpc() ? 'On' : 'Off';
        $allow_url_fopen = ini_get('allow_url_fopen') ? 'On' : 'Off';
        $db_self_test = $_ENV['test']->db_self_test();
        $pdo_self_test = $_ENV['test']->pdo_self_test();
        $cache_self_test = $_ENV['test']->cache_self_test();
        include template('f5','default');
        
 
    }  
}

?>