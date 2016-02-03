<?php
/**
 * 游戏管理
 * @author chen<cxd032404@hotmail.com>
 * $Id: SecurityAnswerController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class Config_SecurityAnswerController extends AbstractController
{
	/**
	 * 权限限制
	 * @var string
	 */
	protected $sign = '?ctl=config/security.answer';
	/**
	 * App对象
	 * @var object
	 */
	protected $oSecurityAnswer;

	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oSecurityAnswer = new Config_SecurityAnswer();
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
		$log = "密保问题管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		

		$QuestionArr = $this->oSecurityAnswer->getAll();
		include $this->tpl();
	}

	/**
	 * 添加密保问题表单
	 * @return unknown_type
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "密保问题添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
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
		$log = "密保问题添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$params = $this->request->from('Question');
		if(empty($params['Question']))
		{
			exit(json_encode(array('errno' => 2)));
		}

		$bind = array();
		$bind['Question']       = $params['Question'];

		$res = $this->oSecurityAnswer->insert($bind);
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
		$log = "密保问题删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$QuestionId = $this->request->QuestionId;
		if(!intVal($QuestionId))
		{
			$this->response->goBack();
		}
		$this->oSecurityAnswer->delete($QuestionId);
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
		$log = "密保修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$QuestionId = intval($this->request->QuestionId);
		if(empty($QuestionId))
		{
			$this->response->goBack();
		}
		$QuestionInfo = $this->oSecurityAnswer->getRow($QuestionId);
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
		$log = "密保问题修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$params = $this->request->from('QuestionId', 'Question');

		if(!intval($params['QuestionId']))
		{
			exit(json_encode(array('errno' => 1)));
		}

		if(empty($params['Question']))
		{
			exit(json_encode(array('errno' => 2)));
		}

		$QuestionId = intval($params['QuestionId']);
		unset($params['QuestionId']);

		$bind = array();
		$bind['Question']        = trim($params['Question']);

		$res = $this->oSecurityAnswer->update($QuestionId, $bind);

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
