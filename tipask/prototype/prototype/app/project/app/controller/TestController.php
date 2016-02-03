<?php
/**
 * 通用用户角色信息控制层
 * @author chen<cxd032404@hotmail.com>
 * $Id: TestController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class TestController extends AbstractController
{
	/**
	 *对象声明
	 */
	protected $oUser;
	protected $oActive;
	protected $oPartnerApp;
	protected $oServer;
	protected $oCharacter;
 

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 *角色信息生成
	 */
	public function indexAction()
	{
		echo "<pre>";
		var_dump($_GET);
		echo 223;
		$oApp = new Lm_DB1_DB1();
		$oApp->test($this->request->word);
		
		
		$oMenCache = new Base_Cache_Memcache("server");
//		$oMenCache -> set('a',1,100);
//		$oMenCache -> set('b',2,100);
		print_r($oMenCache->get(array('a','b')));
	}

}
