<?php
/**
 * request
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Abstract.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Controller_Request_Abstract
{
	protected $params = array();
	protected static $instance = null;
	protected $server = array();
	protected $ip = null;
	protected $agent = null;
	protected $referer = null;

	protected $controller;
	protected $controllerKey = 'ctl';

	protected $action;
	protected $actionKey = 'ac';

	protected $module;
	protected $moduleKey = 'mod';

	public function setControllerName($value)
	{
		$this->controller = (string) $value;

		return $this;
	}

	public function getControllerName()
	{
		//if (null === $this->controller) {
			$this->controller = $this->get($this->getControllerKey());
		//}

		return $this->controller;
	}

	public function setControllerKey($key)
	{
		$this->controllerKey = (string) $key;

		return $this;
	}

	public function getControllerKey()
	{
		return $this->controllerKey;
	}

	public function setActionName($value)
	{
		$this->action = (string) $value;

		return $this;
	}

	public function getActionName()
	{
		//if (null === $this->action) {
			$this->action = $this->get($this->getActionKey());
		//}

		return $this->action;
	}

	public function setActionKey($key)
	{
		$this->actionKey = (string) $key;

		return $this;
	}

	public function getActionKey()
	{
		return $this->actionKey;
	}

	public function setModuleName($module)
	{
		$this->module = (string) $module;
		return $this;
	}

	public function getModuleName()
	{
		if (null === $this->module) {
			$this->module = $this->get($this->getModuleKey());
		}

		return $this->module;
	}

	public function setModuleKey($key)
	{
		$this->moduleKey = (string) $key;
		return $this;
	}

	public function getModuleKey()
	{
		return $this->moduleKey;
	}

	public static function getInstance()
	{
		if (NULL === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __get($key)
	{
		switch (true) {
			case isset($this->params[$key]):
				return $this->params[$key];
			case isset($_GET[$key]):
				return $_GET[$key];
			case isset($_POST[$key]):
				return $_POST[$key];
			case isset($_COOKIE[$key]):
				return $_COOKIE[$key];
			default:
				return null;
		}
	}

	public function __isset($key)
	{
		switch (true) {
			case isset($this->params[$key]):
				return true;
			case isset($_GET[$key]):
				return true;
			case isset($_POST[$key]):
				return true;
			case isset($_COOKIE[$key]):
				return true;
			default:
				return false;
		}
	}

	public function get($key)
	{
		return $this->__get($key);
	}

	/**
	 * 同时获取多个参数到数组
	 * @param mixed $params
	 * @return array
	 */
	public function from($params)
	{
		$args = is_array($params) ? $params : func_get_args();

		$result = array();
		foreach ($args as $arg) {
			$result[$arg] = $this->__get($arg);
		}

		return $result;
	}

	public function getQuery($key = null)
	{
		if (null === $key) {
			return $_GET;
		}

		return (array_key_exists($key, $_GET)) ? $_GET[$key] : null;
	}

	public function getPost($key = null)
	{
		if (null === $key) {
			return $_POST;
		}

		return (array_key_exists($key, $_POST)) ? $_GET[$_POST] : null;
	}

	public function getCookie($key = null)
	{
		if (null === $key) {
			return $_COOKIE;
		}

		return (array_key_exists($key, $_COOKIE)) ? $_GET[$_COOKIE] : null;
	}

	/**
	 * 从params中获取指定参数
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	/**
	 * 设置一个参数
	 * @param string $key
	 * @param mixed $value
	 * @return Base_Controller_Request_Abstract
	 */
	public function setParam($key, $value)
	{
		$key = (string) $key;

		$this->params[$key] = $value;

		return $this;
	}

	/**
	 * 删除参数
	 * @param string $key
	 * @return Base_Controller_Request_Abstract
	 */
	public function unsetParam($key)
	{
		if (array_key_exists($key, $this->params)) {
			unset($this->params[$key]);
		}

		return $this;
	}

	/**
	 * 参数是否存在
	 * @param string $key
	 * @return boolean
	 */
	public function issetParam($key)
	{
		return isset($this->params[$key]);;
	}

	/**
	 * 设置多个参数
	 * @param mixed $params
	 * @return Base_Controller_Request_Abstract
	 */
	public function setParams($params)
	{
		if (!is_array($params)) {
			parse_str($params, $out);
			$params = $out;
		}

		$this->params = array_merge($this->params, $params);

		return $this;
	}

	/**
	 * 清除所有参数
	 * @return Base_Controller_Request_Abstract
	 */
	public function clearParams()
	{
		$this->params = array();

		return $this;
	}

	/**
	 * 设置服务器参数
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setServer($key, $value = NULL)
	{
		if (NULL === $value) {
			if (isset($_SERVER[$key])) {
				$value = $_SERVER[$key];
			} elseif (isset($_ENV[$key])) {
				$value = $_ENV[$key];
			}
		}

		$this->server[$key] = $value;
	}

	/**
	 * 获取服务器参数
	 * @param string $key
	 * @return mixed
	 */
	public function getServer($key)
	{
		if (!isset($this->server[$key])) {
			$this->setServer($key);
		}

		return $this->server[$key];
	}

	/**
	 * 设置ip
	 * @param string $ip
	 * @return void
	 */
	public function setIp($ip = NULL)
	{
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}

		preg_match("/[\d\.]{7,15}/", $onlineip, $matches);
		$this->ip = isset($matches[0]) ? $matches[0] : 'unknown';
	}

	/**
	 * 获取客户端ip
	 * @return string
	 */
	public function getIp()
	{
		if (NULL === $this->ip) {
			$this->setIp();
		}

		return $this->ip;
	}

	/**
	 * 设置客户端Agent
	 * @param string $agent
	 * @return void
	 */
	public function setAgent($agent = NULL)
	{
		$this->agent = (NULL === $agent) ? $this->getServer('HTTP_USER_AGENT') : $agent;
	}

	/**
	 * 获取客户端Agent
	 * @return string
	 */
	public function getAgent()
	{
		if (NULL === $this->agent) {
			$this->setAgent();
		}

		return $this->agent;
	}

	/**
	 * 设置引用页
	 * @param string $referer
	 * @return void
	 */
	public function setReferer($referer = NULL)
	{
		$this->referer = (NULL === $referer) ? $this->getServer('HTTP_REFERER') : $referer;
	}

	/**
	 * 获取引用页
	 * @return string
	 */
	public function getReferer()
	{
		if(NULL === $this->referer) {
			$this->referer = $this->setReferer();
		}
		return $this->referer;
	}

	/**
	 * 判断方法是否为GET
	 * @return boolean
	 */
	public function isGet()
	{
		return 'GET' == $this->getServer('REQUEST_METHOD');
	}

	/**
	 * 判断方法是否为POST
	 * @return boolean
	 */
	public function isPost()
	{
		return 'POST' == $this->getServer('REQUEST_METHOD');
	}

	public function isPut()
	{
		return 'PUT' == $this->getServer('REQUEST_METHOD');
	}

	public function isHttps()
	{
		return 'on' == $this->getServer('HTTPS');
	}

	/**
	 * 请求是否为ajax
	 * @return boolean
	 */
	public function isAjax()
	{
		return 'XMLHttpRequest' == $this->getServer('HTTP_X_REQUESTED_WITH');
	}

	/**
	 * 请求是否为flash
	 * @return boolean
	 */
	public function isFlash()
	{
		return 'Shockwave Flash' == $this->getServer('USER_AGENT');
	}

}
