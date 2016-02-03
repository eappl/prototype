<?php
/**
 * 前端控制器
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Front.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Controller_Front
{

	protected $defaultController = 'index';

	protected $defaultAction = 'index';

	protected $defaultModule = 'default';

	protected $currentModule;

	protected static $instance = null;

	protected $request = null;

	protected $response = null;

	protected $pathDelimiter = '/';

	protected $wordDelimiter = array('.');

	public function getInstance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function run()
	{
	  self::getInstance()->dispatch();
	}

	public function dispatch(Base_Controller_Request_Abstract $request = null, Base_Controller_Response_Abstract $response = null)
	{
		if (null === $request && null === ($request = $this->getRequest())) {
			$request = new Base_Controller_Request_Http();
			$this->setRequest($request);
		} else if ($request !== null) {
			$this->setRequest($request);
		}

		if (null === $response && null === ($response = $this->getResponse())) {
			$response = new Base_Controller_Response_Http();
			$this->setResponse($response);
		} else if (null !== $response) {
			$this->setResponse($response);
		}

		$className = $this->getControllerClass($request);

		$className = $this->loadClass($className);

		$controller = new $className($request, $this->getResponse());

		if (!$controller instanceof Base_Controller_Action) {
			throw new Base_Exception("Controller $className is not an instance of Base_Controller_Action");
		}

		$action = $this->getActionMethod($request);

		try {
			$controller->dispatch($action);
		} catch (Exception $e) {
			throw $e;
		}

		$controller = null;
	}

	public function setPathDelimiter($spec)
	{
		$spec = (string) $spec;
		$this->pathDelimiter($spec);
		return $this;
	}

	public function getPathDelimiter()
	{
		return $this->pathDelimiter;
	}

	public function setWordDelimiter($spec)
	{
		$this->wordDelimiter($spec);
		return $this;
	}

	public function getWordDelimiter()
	{
		return $this->wordDelimiter;
	}

	public function getControllerClass(Base_Controller_Request_Abstract $request)
	{
		$controllerName = $request->getControllerName();

		if (empty($controllerName)) {
			$controllerName = $this->getDefaultControllerName();
			$request->setControllerName($controllerName);
		}

		$className = $this->formatControllerName($controllerName);

		$module = $request->getModuleName();
		if (!empty($module)) {
			$this->currentModule = $module;
		} else {
			$request->setModuleName($this->defaultModule);
			$this->currentModule = $this->defaultModule;
		}

		return $className;
	}

	public function getActionMethod(Base_Controller_Request_Abstract $request)
	{
		$action = $request->getActionName();

		if (empty($action)) {
			$action = $this->getDefaultAction();
			$request->setActionName($action);
		}

		return $this->formatActionName($action);
	}

	public function formatName($name, $isAction = false)
	{
		if (!$isAction) {
			$segments = explode($this->getPathDelimiter(), $name);
		} else {
			$segments = (array) $name;
		}

		foreach ($segments as $key => $segment) {
			$segment = str_replace($this->getWordDelimiter(), ' ', strtolower($segment));
			$segment = preg_replace('/[^a-z0-9 ]/', '', $segment);
			$segments[$key] = str_replace(' ', '', ucwords($segment));
		}

		return implode('_', $segments);
	}

	public function formatControllerName($controllerName)
	{
		return ucfirst($this->formatName($controllerName)) . 'Controller';
	}

	public function formatActionName($action)
	{
		$action = $this->formatName($action, true);
		return strtolower(substr($action, 0, 1)) . substr($action, 1) . 'Action';
	}

	public function setRequest($request)
	{
		if (is_string($request)) {
			$request = new $request();
		}

		if (!$request instanceof Base_Controller_Request_Abstract) {
			throw new Base_Exception('Invalid request class', 500);
		}

		$this->request = $request;

		return $this;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function setResponse($response)
	{
		if (is_string($response)) {
			$response = new $response();
		}

		if (!$response instanceof Base_Controller_Response_Abstract) {
			throw new Base_Exception('Invalid response class', 500);
		}

		$this->response = $response;

		return $this;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function setDefaultControllerName($controller)
	{
		$this->defaultController = (string) $controller;

		return $this;
	}

	public function getDefaultControllerName()
	{
		return $this->defaultController;
	}

	public function setDefaultAction($action)
	{
		$this->defaultAction = (string) $action;

		return $this;
	}

	public function getDefaultAction()
	{
		return $this->defaultAction;
	}

	public function setDefaultModule($module)
	{
		$this->defaultModule = (string) $module;
		return $this;
	}

	public function getDefaultModule()
	{
		return $this->defaultModule;
	}

	public function loadClass($className)
	{
		$finalClass = $className;

		//暂停module功能
		if ($this->defaultModule != $this->currentModule) {
			$finalClass = $this->formatClassName($this->currentModule, $className);
		}

		if (class_exists($finalClass)) {
			return $finalClass;
		} else {
			throw new Base_Exception('Invalid controller class "' . $finalClass . '"');
		}
	}

    public function formatClassName($moduleName, $className)
    {
        return $this->formatModuleName($moduleName) . '_' . $className;
    }

    public function formatModuleName($module)
    {
        if ($this->defaultModule == $module) {
            return $module;
        }

        return ucfirst($this->formatName($module));
    }

}
