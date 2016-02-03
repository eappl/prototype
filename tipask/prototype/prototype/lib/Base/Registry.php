<?php
/**
 * 全局变量注册
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Registry.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Registry
{
	protected static $store = array();

	public static function get($key)
	{
		return array_key_exists($key, self::$store) ? self::$store[$key] : null;
	}

	public static function set($key, $value)
	{
		self::$store[$key] = $value;
	}

	public static function remove($key)
	{
	    unset(self::$store[$key]);
	}

}
