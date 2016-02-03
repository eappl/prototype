<?php
/**
 * 邮箱后缀管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: MailFixController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_MailFixController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/mail.fix';
	/**
	 * App对象
	 * @var object
	 */
	protected $oMailFix;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oMailFix = new Config_MailFix();
	}

	/**
	 * 浏览邮箱后缀列表
	 * @return unknown_type
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "邮箱后缀管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		

		$MailFixArr = $this->oMailFix->getAll();
		include $this->tpl();
	}

	/**
	 * 添加邮箱后缀表单
	 * @return unknown_type
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "邮箱后缀添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		include $this->tpl();
	}

	/**
	 * 邮箱后缀添加入库
	 * @return unknown_type
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "邮箱后缀添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$params = $this->request->from('SubFix');
		if(empty($params['SubFix']))
		{
			exit(json_encode(array('errno' => 2)));
		}

		$bind = array();
		$bind['SubFix']       = $params['SubFix'];

		$res = $this->oMailFix->insert($bind);
		if ($res) {
			$response = array('errno' => 0);
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
		$log = "邮箱后缀删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$FixId = $this->request->FixId;
		if(!intVal($FixId))
		{
			$this->response->goBack();
		}
		$this->oMailFix->delete($FixId);
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
		$log = "邮箱后缀修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$FixId = intval($this->request->FixId);
		if(empty($FixId))
		{
			$this->response->goBack();
		}
		$MailFixInfo = $this->oMailFix->getRow($FixId);
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
		$log = "邮箱后缀修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$params = $this->request->from('FixId', 'SubFix');

		if(!intval($params['FixId']))
		{
			exit(json_encode(array('errno' => 1)));
		}

		if(empty($params['SubFix']))
		{
			exit(json_encode(array('errno' => 2)));
		}

		$FixId = intval($params['FixId']);
		unset($params['FixId']);

		$bind = array();
		$bind['SubFix']        = trim($params['SubFix']);

		$res = $this->oMailFix->update($FixId, $bind);
		if ($res) {
			$response = array('errno' => 0);
			echo json_encode($response);
			return true;
		} else {
			$response = array('errno' => 9);
			echo json_encode($response);
			return false;
		}
	}
}
