<?php
/**
 * Cookie操作
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Cookie.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Cookie
{
	/**
	 * 获取cookie
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
	}

	/**
	 * 设置cookie
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expire
	 * @param string $url
	 * @return void
	 */
	public static function set($key, $value, $expire = 0, $url = null)
	{
		$path = '/';
		if (!empty($url)) {
			$parsed = parse_url($url);
			$path = empty($parsed['path']) ? '/' : Base_Common::url(null, $parsed['path']);
		}

		if (is_array($value)) {
			foreach ($value as $name => $val) {
				setcookie("{$key}[{$name}]", $val, $expire, $path);
			}
		} else {
			setcookie($key, $value, $expire, $path);
		}
	}

	/**
	 * 移除cookie
	 * @param string $key
	 * @param string $url
	 * @return void
	 */
	public static function remove($key, $url = null)
	{
		if (!isset($_COOKIE[$key])) {
			return;
		}

		$path = '/';
		if (!empty($url)) {
			$parsed = parse_url($url);
			$path = empty($parsed['path']) ? '/' : Base_Common::url(null, $parsed['path']);
		}

		if (is_array($_COOKIE[$key])) {
			foreach($_COOKIE[$key] as $name => $val) {
				setcookie("{$key}[{$name}]", '', time() - 2592000, $path);
			}
		} else {
			setcookie($key, '', time() - 2592000, $path);
		}
	}

}
