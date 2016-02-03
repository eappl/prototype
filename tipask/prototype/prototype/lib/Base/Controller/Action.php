<?php
/**
 * 动作控制
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Action.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Controller_Action
{

	protected $request = null;

	protected $response = null;

	protected $delimiters;

	protected $invokeArgs = array();

    protected $tpl = '';

	public function __construct($request, $response, array $invokeArgs = array())
	{
		$this->setRequest($request)
			->setResponse($response)
			->setInvokeArgs($invokeArgs);
		$this->init();

		$this->response->setContentType();
	}

	public function init()
	{}

	public function setRequest(Base_Controller_Request_Abstract $request)
	{
		$this->request = $request;

		return $this;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function setResponse(Base_Controller_Response_Abstract $response)
	{
		$this->response = $response;

		return $this;
	}

	public function getResponse()
	{
		return $this->response();
	}

	public function setInvokeArgs(array $args = array())
	{
		$this->invokeArgs = $args;
		return $this;
	}

	public function getInvokeArgs()
	{
		return $this->invokeArgs;
	}

	public function getInvokeArg($key)
	{
		return isset($this->invokeArg[$key]) ? $this->invokeArg[$key] : null;
	}

	public function __call($methodName, $args)
	{
		if ('Action' == substr($methodName, -6)) {
			$action = substr($methodName, 0, strlen($methodName) - 6);
			throw new Base_Exception(sprintf('Action "%s" does not exist and was not trapped in __call()', $action), 404);
		}
		throw new Base_Exception(sprintf('Method "%s" does not exist and was not trapped in __call()', $methodName), 500);
	}

	/**
	 * 动作分发
	 * @param string $action
	 */
	public function dispatch($action)
	{
		$classMethods = get_class_methods($this);

		if (in_array($action, $classMethods)) {
            $this->tpl = substr(get_class($this), 0, -10) . '_' . substr($action, 0, -6);
			$this->$action();
		} else {
			$this->__call($action, array());
		}
	}

	/**
	 * 载入模板
	 * @param string $tpl
	 */
	public function tpl($tpl = null)
	{
        if (null === $tpl) {
            $tpl = $this->tpl;
        }

	    return Base_Template::factory($tpl)->get();
	}

	public function run($request = null, $response = null)
	{
		if (null === $request) {
			$request = $this->getRequest();
		} else {
			$this->setRequest($request);
		}

		if (null === $response) {
			$response = $this->getResponse();
		} else {
			$this->setResponse($response);
		}

		$action = $request->getActionName();
		if (empty($action)) {
			$action = 'index';
		}

		$action = $action . 'Action';
		$this->dispatch($action);
	}

}
