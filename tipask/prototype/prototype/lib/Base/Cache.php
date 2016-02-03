<?php
/**
 * 缓存处理
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Cache.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Cache
{
    /**
     * 缓存类型
     * @var array
     */
	protected static $types = array('File', 'File_Hash', 'Memcache', 'Memcachedb','Redis');

	/**
	 * 工厂方法
	 * @param unknown_type $type
	 * @return object
	 */
	public function factory($type = 'File')
	{
		$type = in_array($type, self::$types) ? $type : 'File';
		$drvName = 'Base_Cache_' . $type;
		echo $drvName."<br>";
		return new $drvName();
	}

}
