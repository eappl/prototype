<?php
/**
 * Widget基类
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Widget.php 15195 2014-07-23 07:18:26Z 334746 $
 */


abstract class Base_Widget
{
	protected $stack = array();
	protected $row = array();
	protected $length = 0;

	protected $config;

	protected $table = '';
	protected $db = null;


	/**
	 * 错误代码
	 * @var integer
	 */
	protected $errno = 0;

	/**
	 * 错误信息
	 * @var string
	 */
	protected $error = '';

	public function __construct()
	{
		if (is_string($this->table) && $this->table != '') {
			$this->db = Base_Db_Hash::getInstance()->prepare($this->table);
		}

		$config = (@include Base_Common::$config['config_file']);
		$this->config = Base_Config::factory($config);
		$this->auto();
	}

	public function getDbTable($table = null,$db = null, $key = null)
	{
		if ($table === null) {
			$table = $this->table;
		}
		return Base_Db_Hash::getInstance()->getHashTable($table, $db, $key);
	}

	public function getDbInstance($table)
	{
		return Base_Db_Hash::getInstance()->prepare($table);
	}

	public function auto()
	{}

	public function to(& $variable)
	{
		return $variable = $this;
	}

	public function push(array $value)
	{
		$this->row = $value;

		$this->length++;
		$this->stack[] = $value;

		return $value;
	}

	public function length()
	{
		echo $this->length;
	}

	public function getErrno()
	{
		return $this->errno;
	}

	public function getError()
	{
		return $this->error;
	}

	public function __call($key, $args)
	{
		echo $this->{$key};
	}

	public function __get($key)
	{
		return isset($this->row[$key])
			? $this->row[$key]
			: (method_exists($this, $method = '___' . $key)
				? ($this->row[$key] = $this->{$method}())
				: null);
	}

	public function __set($key, $value)
	{
		$this->row[$key] = $value;
	}

	public function __isset($key)
	{
		return isset($this->row[$key]);
	}

}
