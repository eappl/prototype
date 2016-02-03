<?php
/**
 * HTTP response
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Http.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Controller_Response_Http extends Base_Controller_Response_Abstract
{
	/**
	 * 字符集
	 * @var string
	 */
	protected $charset;

	/**
	 * 默认字符集
	 */
	const CHARSET = 'UTF-8';

	protected static $instance = NULL;

	/**
	 * http code description
	 * @var array
	 */
	protected static $httpCode = array(
		100 => 'Continue',
		101	=> 'Switching Protocols',
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',
		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		303	=> 'See Other',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',
		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		402	=> 'Payment Required',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',
		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);

	/**
	 * 唯一实例
	 * @return Base_Response
	 */
	public static function getInstance()
	{
		if (NULL === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * 设置默认字符集
	 * @param string $charset
	 * @return void
	 */
	public function setCharset($charset = NULL)
	{
		$this->charset = empty($charset) ? self::CHARSET : $charset;
	}

	/**
	 * 获取字符集
	 * @return string
	 */
	public function getCharset()
	{
		if (empty($this->charset)) {
			$this->setCharset();
		}

		return $this->charset;
	}

	/**
	 * 声明类型和字符集
	 * @param string $contentType
	 * @return void
	 */
	public function setContentType($contentType = 'text/html')
	{
		header('Content-Type: ' . $contentType . '; charset=' . $this->getCharset(), true);
	}

	/**
	 * 设置HTTP头
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function setHeader($name, $value)
	{
		header($name . ':' . $value, true);
	}

	/**
	 * 设置HTTP状态
	 * @param integer $code
	 * @return void
	 */
	public static function setStatus($code)
	{
		if (isset(self::$httpCode[$code])) {
			header('HTTP/1.1 ' . $code . ' ' . self::$httpCode[$code], true, $code);
		}
	}

	/**
	 * 301 302 重定向
	 * @param string $location
	 * @param boolean $isPermanently 是否永久转向
	 * @return void
	 */
	public function redirect($location, $isPermanently = false)
	{
		if ($isPermanently) {
			self::setStatus(301);
			header('Location: ' . $location);
			echo '<html><head>
<title>301 Moved Permanently</title>
</head><body>
<h1>Moved Permanently</h1>
<p>The document has moved <a href="' . $location . '">here</a>.</p>
</body></html>';
			exit;
		} else {
			self::setStatus(302);
			header('Location: ' . $location);
			echo '<html><head>
<title>302 Moved Temporarily</title>
</head><body>
<h1>Moved Temporarily</h1>
<p>The document has moved <a href="' . $location . '">here</a>.</p>
</body></html>';
			exit;
		}
	}

	/**
	 * 返回来路
	 * @param string $anchor
	 * @param string $default
	 * @return void
	 */
	public function goBack($anchor = NULL, $default = NULL)
	{
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if (!empty($referer)) {
			if (!empty($anchor)) {
				$parts = parse_url($referer);
				if (isset($parts['fragment'])) {
					$referer = substr($referer, 0, strlen($referer) - strlen($parts['fragment']) - 1);
				}
			}
			$this->redirect($referer . (empty($anchor) ? NULL : '#' . $anchor), false);
		} else if (!empty($default)) {
			$this->redirect($default);
		}
	}

}
