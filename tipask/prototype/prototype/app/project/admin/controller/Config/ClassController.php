<?php
/**
 * 游戏类别管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: ClassController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_ClassController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/class';
	/**
	 * App对象
	 * @var object
	 */
	protected $oClass;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oClass = new Config_Class();
	}

	/**
	 * 游戏类别列表
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏类别管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$gameclassArr = $this->oClass->getAll();
		include $this->tpl();
	}

	/**
	 * 载入添加游戏类别模板
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏类别添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		include $this->tpl();
	}

	/**
	 * 将数据添加到数据库
	 * @return boolean
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏类别添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$params = $this->request->from('name','desc');

		if(empty($params['name'])) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}
		if ($this->oClass->nameExists($params['name'])) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}

		$res = $this->oClass->insert($params);

		$response = $res ? array('errno' => 0) : array('errno' => 9);

		echo json_encode($response);
		return true;
	}

	/**
	 * 删除数据
	 */
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏类别删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$gameClassId = $this->request->ClassId;

		$res = $this->oClass->delete($gameClassId);

		$this->response->goBack();
	}

	/**
	 * 修改数据
	 */
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏类别修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$gameClassId = $this->request->ClassId;
		$gameclassArr = $this->oClass->getRow($gameClassId);
		include $this->tpl();
	}

	/**
	 * 更新数据
	 * @return boolean
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "游戏类别修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$params = $this->request->from('name','desc');

		$ClassId = $this->request->ClassId;
		if(empty($params['name'])) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}
		$res = $this->oClass->update($ClassId,$params);
		$response = $res ? array('errno' => 0) : array('errno' => 9);
		echo json_encode($response);
		return true;
	}
}