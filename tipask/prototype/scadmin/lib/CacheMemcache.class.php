<?php
/**
 * Memcache缓存驱动
 */
class CacheMemcache{
    var $handler;
    var $options;
    var $onlineConfig;
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($options=array()) {
        if ( !extension_loaded('memcache') ) {
            throw new Exception('没有加载memcache扩展！');
        }       
        $options['prefix'] = isset($options['prefix'])?$options['prefix']:'Complaint';
        $options['expire'] = isset($options['expire'])?$options['expire']:0;//不过期	
        $this->options = $options;
        $this->onlineConfig = require TIPASK_ROOT.'/onlineConfig.php'; // 获取配置文件
        
        $this->handler = new Memcache;
        if( is_array( $this->onlineConfig['MEMECACHE_SERVER']) ) {
        	foreach($this->onlineConfig['MEMECACHE_SERVER'] as $v) {
        		$this->handler->addServer("$v", $this->onlineConfig['MEMECACHE_PORT']);
        	}
        } else {
        	$this->handler->addServer($this->onlineConfig['MEMECACHE_SERVER'], $this->onlineConfig['MEMECACHE_PORT']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return $this->handler->get($this->options['prefix'].$name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null) {
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        if($this->handler->set($name, $value, 0, $expire)) {         
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name, $ttl = false) {
        $name   =   $this->options['prefix'].$name;
        return $ttl === false ?
            $this->handler->delete($name) :
            $this->handler->delete($name, $ttl);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flush();
    }
    
    /**
     * 缓存配置文件
     * @access public
     * @return boolen
     */
    function load($cachename, $id='id', $orderby='') {
    	$arraydata = $this->get($cachename);
    	if (!$arraydata) {
    		$sql = 'SELECT * FROM ' . DB_TABLEPRE . $cachename;
    		$orderby && $sql.=" ORDER BY $orderby ASC";
    		$query = $this->options['db']->query($sql);
    		while ($item = $this->options['db']->fetch_array($query)) {
    			if (isset($item['k'])) {
    				$arraydata[$item['k']] = $item['v'];
    			} else {
    				$arraydata[$item[$id]] = $item;
    			}
    		}
    		$this->set($cachename, $arraydata);
    	}
    	return $arraydata;
    }
    function reload($cachename, $id='id', $orderby='') {
    		$sql = 'SELECT * FROM ' . DB_TABLEPRE . $cachename;
    		$orderby && $sql.=" ORDER BY $orderby ASC";
    		$query = $this->options['db']->query($sql);
    		while ($item = $this->options['db']->fetch_array($query)) {
    			if (isset($item['k'])) {
    				$arraydata[$item['k']] = $item['v'];
    			} else {
    				$arraydata[$item[$id]] = $item;
    			}
    		}
    		$this->set($cachename, $arraydata);
    	return $arraydata;
    }
}