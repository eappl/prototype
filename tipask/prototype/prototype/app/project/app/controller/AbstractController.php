<?php
/**
 * 支付中心基类
 * @author Chen <cxd032404@hotmail.com>
 * $Id: AbstractController.php 15195 2014-07-23 07:18:26Z 334746 $
 */


abstract class AbstractController extends Base_Controller_Action
{

	/**
	 * 配置
	 * @var Base_Config
	 */
	protected $config;

	/**
	 * 用户
	 * @var Widget_User
	 */
	protected $user;

	/**
	 * 是否需要登录后访问
	 * @var boolean
	 */
	protected $needLogin = false;

	/**
	 * 初始化配置,用户,检查是否需要登录才能访问
	 * @see Controller/Base_Controller_Action::init()
	 */
	public function init()
	{
		parent::init();

//		$config = (@include Base_Common::$config['config_file']);
//		$appConfig = (@include dirname(dirname(__FILE__)) . '/etc/config.php');
//		is_array($appConfig) && $config = $config + $appConfig;
//		$this->config = Base_Config::factory($config);
//
//		$this->user = new Widget_User();
//
//	    if ($this->needLogin && !$this->user->isLogin()) {
//			$this->response->redirect($this->user->getLoginUrl());
//        }
	}

}
