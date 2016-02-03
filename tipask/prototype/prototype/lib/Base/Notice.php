<?php
/**
 * 信息提示
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Notice.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Notice
{
	protected $key = '__notice__';

	public static $instance = null;

	public $type = 'notice';

	public $message = '';

	public static function getInstance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct()
	{
		$notice = json_decode(Base_Cookie::get($this->key), true);
        Base_Cookie::remove($this->key);

        $this->type = $notice['type'];
        $this->message = $notice['message'];

        return $this;
	}

	public function setKey($key)
	{
		$this->key = $key;

		return $this;
	}

    /**
     * 设置提示
     * @param string $message
     * @param string $type notice|warning|error
     */
    public function set($message, $type = 'notice')
    {
    	$value = json_encode(array('type' => $type, 'message' => $message));
    	Base_Cookie::set($this->key, $value);

    	return $this;
    }

    public function type()
    {
		echo $this->type;
    }

    public function message()
    {
    	echo $this->message;
    }

}
