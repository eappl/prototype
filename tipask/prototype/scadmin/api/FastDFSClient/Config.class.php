<?php

final class DFSConfig {   
    private $data = array(); 

    function __construct(){
    	$config_file = "fdfs_config.php";
    	$cfg = require($config_file); 
        $this->data = array_merge($this->data, $cfg);  
    }
                 
    public function get($key) {   
        return (isset($this->data[$key]) ? $this->data[$key] : NULL);   
    }      
                     
    public function set($key, $value) {   
        $this->data[$key] = $value;   
    }   
                 
    public function has($key) {   
        return isset($this->data[$key]);   
    }  
}



?>