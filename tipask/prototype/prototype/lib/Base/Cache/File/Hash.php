<?php
/**
 * hash分布式文件缓存
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Hash.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Cache_File_Hash extends Base_Cache_File
{
    protected function getFileName($key)
    {
        $hash = md5($key);
		$dir = $this->cacheDir . substr($hash, 0, 2);

		if (!is_dir($dir)) {
			mkdir($dir, 0644, true);
		}

        return $dir . '/' . $hash . '.php';
    }

}
