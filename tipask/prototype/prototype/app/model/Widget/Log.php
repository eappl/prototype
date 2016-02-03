<?php
/**
 * 日志
 * @author Chen <cxd032404@hotmail.com>
 * $Id: Log.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Widget_Log extends Base_Widget
{

	protected static $instance = null;

	public static function getInstance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * 析构时调用
	 */
	public function log()
	{
		$log = $this->row;
	}

	public function __destruct()
	{
		$this->log();
	}

	public function push($key, $value)
	{
		$this->row[$key] = $value;

		return $this;
	}

}
