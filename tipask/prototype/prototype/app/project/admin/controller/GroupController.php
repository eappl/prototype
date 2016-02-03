<?php
/**
 * 用户组管理
 * @category   UserController
 * @Description
 * @author   陈晓东
 * @version
 * $Id: GroupController.php 15233 2014-08-04 06:46:08Z 334746 $
 */

class GroupController extends AbstractController
{
	protected $sign = '?ctl=group';

	/**
	 * 管理员组管理首页
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "管理员组管理首页\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);

		$Widget_Group=new Widget_Group();
		$group = $Widget_Group->getAll();

		include $this->tpl('group_index');
	}

	/**
	 *
	 * 添加管理员组表单页面
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "添加管理员组表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		include $this->tpl('group_add');
	}

	/**
	 *
	 * 添加管理员组执行页面
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "添加管理员组执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);

		$name = $this->request->name;

		$bind['name'] = $name;

		if (empty($name)) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		$Widget_Group=new Widget_Group();
		$res = $Widget_Group->insert($bind);
		if (!$res)
		{
			$response = array('errno' => 9);
			echo json_encode($response);
			return false;
		}

		$response = array('errno' => 0);
		echo  json_encode($response);
		return true;
	}

	/**
	 *
	 * 修改管理员组表单页面
	 */
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改管理员组表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$group_id = $this->request->group_id;

		$Widget_Group=new Widget_Group();
		$group = $Widget_Group->get($group_id);

		include $this->tpl('group_modify');
	}

	/**
	 *
	 * 修改管理员组执行页面
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改管理员组执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);

		$group_id = $this->request->group_id;
		$name = $this->request->name;
		$comment = $this->request->comment;

		$bind['name']=$name;

		if (empty($name)) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		$Widget_Group=new Widget_Group();
		$res = $Widget_Group->update($group_id,$bind);

		if (!$res)
		{
			$response = array('errno' => 9);
			echo json_encode($response);
			return false;
		}

		$response = array('errno' => 0);
		echo json_encode($response);
		return true;
	}

	/**
	 *
	 * 删除管理员执行页面
	 */
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "删除管理员执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$group_id = intval($this->request->group_id);

		$Widget_Group=new Widget_Group();
		$res = $Widget_Group->delete($group_id);
		if ($res)
		{
			$Widget_Menu_Permission = new Widget_Menu_Permission();
			$Widget_Menu_Permission->deleteByGroup($group_id);
		}
		$this->response->goBack();
	}

}
