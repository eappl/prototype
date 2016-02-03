<?php
/**
 * 配置类
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Config.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Config implements Iterator
{
	/**
	 * 配置数据
	 * @var array
	 */
    protected $currentConfig = array();

    private function __construct($config = array())
    {
        $this->setDefault($config);
    }

    /**
     * 工厂方法
     * @param array|string $config
     */
    public static function factory($config = array())
    {
        return new self($config);
    }

    /**
     * 设置配置数据
     * @param $config
     * @param $replace
     * @return void
     */
    public function setDefault($config, $replace = false)
    {
        if (is_string($config)) {
            parse_str($config, $params);
        } else if (is_array($config)) {
            $params = $config;
        } else {
            throw new Base_Exception('$config must be a string or an array');
        }

        foreach ($params as $key => $val) {
            if ($replace ||!array_key_exists($key, $this->currentConfig)) {
                $this->currentConfig[$key] = $val;
            }
        }
    }

    public function rewind()
    {
        reset($this->currentConfig);
    }

    public function current()
    {
        return current($this->currentConfig);
    }

    public function next()
    {
        next($this->currentConfig);
    }

    public function key()
    {
        return key($this->currentConfig);
    }

    public function valid()
    {
        return false !== $this->current();
    }

    public function __get($key)
    {
        return isset($this->currentConfig[$key]) ? $this->currentConfig[$key] : null;
    }

    public function __set($key, $val)
    {
        $this->currentConfig[$key] = $val;
    }

    public function __call($key, $args)
    {
        echo isset($this->currentConfig[$key]) ? $this->currentConfig[$key] : '';
    }

    public function __isset($key)
    {
        return isset($this->currentConfig[$key]);
    }

    public function __toString()
    {
        return json_encode($this->currentConfig);
    }

}
