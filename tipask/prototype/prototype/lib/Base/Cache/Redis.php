<?php
/**
 * memcache
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Redis.php 15195 2014-07-23 07:18:26Z 334746 $
 */


//@todo:
class Base_Cache_Redis implements Base_Cache_Interface
{	
	  var $handler;
    var $options;
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($server) {
        if ( !extension_loaded('memcache') ) {
            throw new Exception('没有加载memcache扩展！');
        }           
        $this->handler = new Redis;

        $this->handler->connect('127.0.0.1',6379);
    }
    public function test()
    {
	    //这里开始使用redis的功能，就是设置一下
			$this->handler->set('name1', 'www.51projob.com');
			$this->handler->set('name2', 'www.crazyant.com');
			
			echo "通过get方法获取到键的值：<br>"
				.$this->handler->get('name1')."<br>"
				.$this->handler->get('name2');	
    }
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return $this->handler->get($name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value,  $expire = 1) {
        if(is_null($expire)) {
        }
        if($this->handler->set($name, $value, MEMCACHE_COMPRESSED, $expire)) {         
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
    public function remove($name, $ttl = false) {
        $name   =   $name;
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


}
