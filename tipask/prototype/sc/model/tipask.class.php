<?php

!defined('IN_TIPASK') && exit('Access Denied');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
require TIPASK_ROOT . '/lib/config.class.php';
require TIPASK_ROOT . '/lib/db.class.php';
require TIPASK_ROOT . '/lib/pdo/Mysql/pdo.class.php';
require TIPASK_ROOT . '/lib/pdo/Mysql/hash.class.php';
require TIPASK_ROOT . '/lib/global.func.php';
require TIPASK_ROOT . '/model/base.class.php';
require TIPASK_ROOT . '/lib/CacheMemcache.class.php';
//require TIPASK_ROOT . '/lib/CacheRedis.class.php';


class tipask {

    var $get = array();
    var $post = array();
    var $vars = array();

    function tipask() {
        $this->init_request();
        $this->load_control();
    }

    function init_request() {      
        require TIPASK_ROOT . '/config.php';
        header('Content-type: text/html; charset=' . TIPASK_CHARSET); //给浏览器识别，sbie6
        $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $pos = strpos($querystring, '.');
        if ($pos !== false) {
            $querystring = substr($querystring, 0, $pos);
        }       

        $andpos = strpos($querystring, "&");
        $andpos && $querystring = substr($querystring, 0, $andpos);
        $this->get = explode('/', $querystring);
        if (empty($this->get[0])) {
            $this->get[0] = 'index';
        }
        if (empty($this->get[1])) {
            $this->get[1] = 'default';
        }
        if (count($this->get) < 2) {
            exit(' Access Denied !');
        }
        unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
        $this->get = taddslashes($this->get, 1);
        $this->post = taddslashes(array_merge($_GET, $_POST));
        unset($_POST);
    }

    function load_control() {
        $controlfile = TIPASK_ROOT . '/control/' . $this->get[0] . '.php';
        $isadmin = ('admin' == substr($this->get[0], 0, 5));
        $isadmin && $controlfile = TIPASK_ROOT . '/control/admin/' . substr($this->get[0], 6) . '.php';
        if (false === @include($controlfile)) {
            $this->notfound('您访问的页面不存在！');
        }
    }

    function run() { 
    	$this->domain_deployment();   	
        $controlname = $this->get[0] . 'control';
        $control = new $controlname($this->get, $this->post);
        $method = 'on' . $this->get[1];
        if (method_exists($control, $method)) {           
            $control->$method();
        } else {
            $this->notfound('method "' . $method . '" not found!');
        }
    }

    function notfound($error) {
        @header('HTTP/1.0 404 Not Found');
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
    }
    
    function domain_deployment(){
    	$isadmin = ('admin' == substr($this->get[0], 0, 5));
    	if($isadmin){
    		if(config::ADMIN_DOMAIN != getServerName()){
    			$this->notfound('您所访问的页面不存在！');
    		}
    	}else{
    		if(config::FRONT_DOMAIN != getServerName()){
    			$curPageURL = curPageURL();
    			strpos($curPageURL, 'http://') !== false && $curPageURL = str_replace('http://','',$curPageURL);
    			strpos($curPageURL, '/') !== false && $curPageURL = str_replace('/','',$curPageURL);
    			if($curPageURL == config::ADMIN_DOMAIN){
    				header("Location: ".url('admin_main/',true));
    				exit;
    			}else{
    				if($this->get[0] != 'attach'){
    					$this->notfound('您所访问的页面不存在！');
    				}
    			}
    			 
    		}
    	}
    }

}

?>