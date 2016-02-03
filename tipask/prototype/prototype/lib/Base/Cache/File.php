<?php
/**
 * 文件缓存
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: File.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Cache_File implements Base_Cache_Interface
{
    protected $cacheDir;

    public function __construct()
    {
        $this->cacheDir = Base_Common::$config['var_dir'] . 'cache/';
    }

    protected function getFileName($key)
    {
        return $this->cacheDir . md5($key) . '.php';
    }

    public function get($key)
    {
		$file = $this->getFileName($key);

		if (is_file($file)) {
			$data = unserialize(file_get_contents($file));

			$forceRefresh = $this->cacheDir . 'forceRefresh';
			$time = intval(@filemtime($forceRefresh));

			if (($data['expire'] > time()) && intval(filemtime($file)) > $time) {
				return $data['cache'];
			} else {
				@unlink($file);
			}
		}

		return false;
    }

	public function set($key, $value, $expire = 900)
	{
		$file = $this->getFileName($key);
		$expire = $expire + time();
		$data = array('expire' => $expire, 'cache' => $value);
		return file_put_contents($file, serialize($data));
	}

	public function remove($key)
	{
		$file = $this->getFileName($key);
		return @unlink($file);
	}

}
