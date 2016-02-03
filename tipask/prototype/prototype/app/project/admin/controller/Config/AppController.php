<?php
/**
 * 游戏管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: AppController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_AppController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/app';
	/**
	 * App对象
	 * @var object
	 */
	protected $oApp;
	protected $oClass;
	protected $oPartner;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oApp = new Config_App();
		$this->oClass = new Config_Class();
		$this->oPartner	= new Config_Partner();
	}

	/**
	 * 浏览游戏列表
	 * @return unknown_type
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		/**
		 * 游戏类型
		 * @var string
		 */
		$appType = intval($this->request->appType);

		$oAppArr = array();
		$oGameClaArr = $this->oClass->getAll();
		if($appType)
			$oAppArr = $this->oApp->getByClass($appType);
		else
			$oAppArr = $this->oApp->getAll();
		if(is_array($oAppArr))
		{
			foreach($oAppArr as $keyApp => $valApp)
			{
				$oAppArr[$keyApp]['class_name'] = $valApp['ClassId']?$oGameClaArr[$valApp['ClassId']]['name']:"未分类";
				$oAppArr[$keyApp]['comment'] = json_decode($valApp['comment'],true);
			}
		}
		include $this->tpl();
	}

	/**
	 * 添加游戏表单
	 * @return unknown_type
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$oGameClaArr = $this->oClass->getAll();
		include $this->tpl();
	}

	/**
	 * 游戏数据添加入库
	 * @return unknown_type
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$params = $this->request->from('AppId', 'app_sign', 'app_desc', 'ClassId', 'name', 'site_url', 'exchange_rate','is_show','create_loginid');
		if(!intval($params['AppId']))
		{
			exit(json_encode(array('errno' => 1)));
		}

		if(empty($params['name']))
		{
			exit(json_encode(array('errno' => 2)));
		}

		if(empty($params['app_sign']))
		{
			exit(json_encode(array('errno' => 3)));
		}

		$bind = array();
		$bind['AppId']       = intval($params['AppId']);
		$bind['app_sign']     = preg_replace('/[^a-z0-9_]/i', '', $params['app_sign']);
		$bind['ClassId']     = trim($params['ClassId']);
		$bind['app_desc']     = trim($params['app_desc']);
		$bind['name']         = trim($params['name']);
		$bind['site_url']     = trim($params['site_url']);
		$bind['exchange_rate']= trim($params['exchange_rate']);
		$bind['is_show']    = trim($params['is_show']);

		$bind['comment']      = json_encode($this->request->comment);
		$res = $this->oApp->insert($bind);

		if ($res) {
			$response = array('errno' => 0);
			$this->oApp->reBuildAppConfig();
			echo json_encode($response);
		} else {
			$response = array('errno' => 9);
			echo json_encode($response);
		}
	}

	/**
	 * 删除数据
	 * @return unknown_type
	 */
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$AppId = $this->request->AppId;
		if(!intVal($AppId))
		{
			$this->response->goBack();
		}
		$this->oApp->delete($AppId);
		$this->oApp->reBuildAppConfig();
		$this->response->goBack();
	}

	/**
	 * 更新数据模板
	 * @return unknown_type
	 */
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$AppId = intval($this->request->AppId);
		if(empty($AppId))
		{
			$this->response->goBack();
		}
		$oAppArr = $this->oApp->getRow($AppId);
		$oAppArr['comment'] = json_decode($oAppArr['comment'],true);
		$oGameClaArr = $this->oClass->getAll();
		include $this->tpl();
	}

	/**
	 * 更新数据
	 * @return unknown_type
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$params = $this->request->from('AppId', 'app_sign', 'app_desc', 'ClassId', 'name', 'site_url', 'exchange_rate','is_show');
		if(!intval($params['AppId']))
		{
			exit(json_encode(array('errno' => 1)));
		}

		if(empty($params['name']))
		{
			exit(json_encode(array('errno' => 2)));
		}

		if(empty($params['app_sign']))
		{
			exit(json_encode(array('errno' => 3)));
		}

		$AppId = intval($params['AppId']);
		unset($params['AppId']);

		$bind = array();
		$bind['app_sign']     = preg_replace('/[^a-z0-9_]/i', '', $params['app_sign']);
		$bind['ClassId']     = trim($params['ClassId']);
		$bind['app_desc']     = trim($params['app_desc']);
		$bind['name']         = trim($params['name']);
		$bind['site_url']     = trim($params['site_url']);
		$bind['exchange_rate']= trim($params['exchange_rate']);
		$bind['is_show']    = trim($params['is_show']);
		$bind['comment']         = json_encode($this->request->comment);

		$res = $this->oApp->update($AppId, $bind);

		if ($res) {
			$response = array('errno' => 0);
			$this->oApp->reBuildAppConfig();
			echo json_encode($response);
			return true;
		} else {
			$response = array('errno' => 9);
			echo json_encode($response);
			return false;
		}
	}
	public function getAppCoinAction()
	{
		$AppId = intval($this->request->AppId);
		$AppInfo = $this->oApp->getRow($AppId);
		$Comment = json_decode($AppInfo['comment'],true);
		echo "<option value=$AppId>".$Comment['coin_name']."</option>";		
	}
}
