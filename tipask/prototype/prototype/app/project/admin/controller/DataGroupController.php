<?php
/**
 * 数据组管理
 * @author <cxd032404@hotmail.com>
 * $Id: DataGroupController.php 15233 2014-08-04 06:46:08Z 334746 $
 */
class DataGroupController extends AbstractController
{
	protected $sign = "?ctl=data.group";
	
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "数据用户组管理\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);
		
		$oGroup = new Widget_Group();
		$groupArr = $oGroup->getClass('2');
		include $this->tpl();
	}
	
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "数据用户组添加\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		include $this->tpl();
	}
	
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "数据用户组添加入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
		
		$data = $this->request->from('name', 'ClassId');
		$oGroup = new Widget_Group();
		if(empty($data['name']))
		{
			echo json_encode(array('errno' => 1));
			return false;
		}
		$res = $oGroup->insert($data);
		if (!$res)
		{
			echo json_encode(array('errno' => 9));
			return false;
		}

		echo  json_encode(array('errno' => 0));
		return true;
		
	}
	
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "数据用户组修改\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$group_id = $this->request->group_id;
		$oGroup = new Widget_Group();
		$group = $oGroup->get($group_id);
		
		include $this->tpl();
	}
	
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "数据用户组修改入库\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		
		$data = $this->request->from('name', 'ClassId');
		$group_id = $this->request->group_id;
		if(!intVal($group_id))
		{
			echo json_encode(array('errno' => 1));
			return false;
		}
		
		if(empty($data['name']))
		{
			echo json_encode(array('errno' => 2));
			return false;
		}
		
		$oGroup = new Widget_Group();
		$res = $oGroup->update($group_id, $data);
		if (!$res)
		{
			echo json_encode(array('errno' => 9));
			return false;
		}

		echo  json_encode(array('errno' => 0));
		return true;
	}
	
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "数据用户组删除\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);
		
		$group_id = intval($this->request->group_id);
		$oGroup = new Widget_Group();
		$res = $oGroup->delete($group_id);
		if ($res)
		{
			$Widget_Menu_Permission = new Widget_Menu_Permission();
			$Widget_Menu_Permission->deleteByGroup($group_id);
		}
		$this->response->goBack();
	}
}





