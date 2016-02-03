<?php
/**
 * 管理员
 * @category   ManagerController
 * @Description
 * @author     陈晓东
 * $Id: ManagerController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class ManagerController extends AbstractController
{
	/**
	 * 权限特征
	 */
	protected $sign='?ctl=manager';

	/**
	 * 管理员管理首页
	 * @author 陈晓东
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "管理员管理首页\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			$username=$this->request->username;
			$menu_group_id = $this->request->menu_group_id;
			$data_group_id = $this->request->data_group_id;
			$is_partner = $this->request->is_partner;
			$bind = array(
				'username' => $username,
				'menu_group_id' => $menu_group_id,
				'data_group_id' => $data_group_id,
				'is_partner'    => $is_partner
			);
			$Widget_Group = new Widget_Group();
			$menuGroup = $Widget_Group->getClass('1');
			$dataGroup = $Widget_Group->getClass('2');

			$Widget_Manager=new Widget_Manager();
			$manager = $Widget_Manager->getLikeName($bind);
			if(!empty($manager))
			{
				foreach($manager as $k => $v)
				{
					if($v['name']==$this->manager->name)
					{
						$manager[$k]['delete'] = 0;
					}
					else
					{
						$manager[$k]['delete'] = 1;
					}
					$manager[$k]['menu_group_name'] = "暂无分组";
					$manager[$k]['data_group_name'] = "";

					$manager[$k]['last_login']=date('Y-m-d H:i:s',$v['last_login']);
					$manager[$k]['last_active']=date('Y-m-d H:i:s',$v['last_login']);
					$manager[$k]['reg_time']=date('Y-m-d H:i:s',$v['reg_time']);
					foreach ($menuGroup as $row)
					{
						if ($v['menu_group_id'] == $row['group_id'])
						{
							$manager[$k]['menu_group_name'] = $row['name'];
						}
					}
					
					if (!empty($v['data_groups']))
					{
						$datas = explode(',', $v['data_groups']);
						sort($datas);
						$data_group_name = array();
						foreach ($datas as $ks => $vs)
						{
							foreach ($dataGroup as $key => $value) {
								if ($vs == $value['group_id']) {
									$data_group_name[] = $value['name'];
								}
							}
						}
						$manager[$k]['data_group_name'] = implode(',', $data_group_name);
					}
				}
			}
			include $this->tpl('manager_index');
		}
		else
		{
			$home = "?ctl=home";
			include $this->tpl('403');
		}
	}

	/**
	 * 插入/新增一个管理员表单页面
	 * @author 陈晓东
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "插入/新增一个管理员表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$PermissionCheck = $this->manager->checkMenuPermission("AddManager");
		if($PermissionCheck['return'])
		{
			$Widget_Group = new Widget_Group();
			$menuGroup = $Widget_Group->getClass('1');
			$dataGroup = $Widget_Group->getClass('2');

			$pass = Base_String::random(8);
			include $this->tpl('manager_add');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}

	}

	/**
	 * 插入/新增一个管理员执行页面
	 * @author 陈晓东
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "插入/新增一个管理员执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$PermissionCheck = $this->manager->checkMenuPermission("AddManager");
		if($PermissionCheck['return'])
		{
			$name = trim($this->request->name);
			$password = trim($this->request->password);
			$confirm = trim($this->request->confirm);
			$menu_group_id = $this->request->menu_group_id;//用户组
			$data_groups = $this->request->data_groups ?  implode(',',$this->request->data_groups) : 0;
			$is_partner = $this->request->is_partner;
			$nowtime = date("Y:m:d H:i:s");
			$bind['name'] = $name;
			$bind['password'] = md5($password);
			$bind['menu_group_id'] = $menu_group_id;
			$bind['data_groups'] = $data_groups;
			$bind['is_partner']  = $is_partner;
			$bind['last_login'] = $nowtime;
			$bind['last_active'] = $nowtime;
			$bind['reg_date'] = $nowtime;
			$bind['reg_ip'] = $this->request->getIp();
			$bind['reset_password']=1;

			//验证
			if (empty($name)) 
			{
				$response = array('errno' => 4,'message' => "用户名不能为空，请修正后再次提交");
				echo json_encode($response);
				return false;
			}
			if ($password != $confirm) 
			{
				$response = array('errno' => 1,'message' => "密码两次输入不一致，请确认后再次提交");
				echo json_encode($response);
				return false;
			}
			if (strlen($password) < 6 || strlen($password) > 18) 
			{
				$response = array('errno' => 3,'message' => "密码不能少于6位或大于18位，请修正后再次提交");
				echo json_encode($response);
				return false;
			}
			$Widget_Manager=new Widget_Manager();
			if ($Widget_Manager->nameExists($name)) 
			{
				$response = array('errno' => 2,'message' => "用户名已存在，请修正后再次提交");
				echo json_encode($response);
				return false;
			}
			//插入
			$insert = $Widget_Manager->insert($bind);
			if (!$insert)
			{
				$response = array('errno' => 9,'message' => "添加用户失败，请修正后再次提交");
			}
			else
			{
				$response = array('errno' => 0,'message' => "添加管理员成功",'goto' => $this->sign);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	/**
	 * 修改管理员表单页面
	 * @author 陈晓东
	 */
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改管理员表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);

		$PermissionCheck = $this->manager->checkMenuPermission("UpdateManager");
		if($PermissionCheck['return'])
		{
			$id = $this->request->id;
			$Widget_Group = new Widget_Group();
			$menuGroup = $Widget_Group->getClass('1');
			$dataGroup = $Widget_Group->getClass('2');

			$Widget_Manager=new Widget_Manager();
			$admin = $Widget_Manager->get($id);
			$admin['data_groups'] = explode(',',$admin['data_groups']);
			include $this->tpl('manager_modify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	/**
	 * 修改管理员执行页面
	 * @author 陈晓东
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改管理员执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$PermissionCheck = $this->manager->checkMenuPermission("UpdateManager");
		if($PermissionCheck['return'])
		{
			$id = trim($this->request->id);//用户ID
			$oldpasswd = trim($this->request->oldpasswd);//原密码
			$newpasswd = trim($this->request->newpasswd);//新密码
			$confirm = trim($this->request->confirm);//确认密码
			$menu_group_id = $this->request->menu_group_id;//用户组
			$data_groups = $this->request->data_groups ?  implode(',',$this->request->data_groups) : 0;
			$is_partner = $this->request->is_partner;
			$bind = array('menu_group_id' => $menu_group_id, 'data_groups' => $data_groups,'is_partner' => $is_partner);

			//取得用户现有信息
			$Widget_Manager=new Widget_Manager();
			$password =$Widget_Manager->getOne($id, 'password');

			//如果要改密码
			if($newpasswd != '') 
			{
				if($password != md5($oldpasswd)) 
				{
					$response = array('errno' => 1,'message' => "原密码不正确，请确认后再次提交");
					echo json_encode($response);
					return false;
				}
				if ($newpasswd != $confirm) 
				{
					$response = array('errno' => 2,'message' => "密码两次输入不一致，请确认后再次提交");
					echo json_encode($response);
					return false;
				}
				if (strlen($newpasswd) < 6 || strlen($newpasswd) > 18) 
				{
					$response = array('errno' => 3,'message' => "密码不能少于6位或大于18位，请修正后再次提交");
					echo json_encode($response);
					return false;
				}
				$bind['password'] = md5($newpasswd);
			}
			//更新信息
			$update = $this->manager->update($id,$bind);
			if ($update) 
			{
				$response = array('errno' => 0,'message' => "修改管理员成功",'goto' => $this->sign);
			} 
			else 
			{
				$response = array('errno' => 9,'message' => "修改用户失败，请修正后再次提交");
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	/**
	 * 删除管理员执行页面
	 * @author 陈晓东
	 */
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "删除管理员执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$PermissionCheck = $this->manager->checkMenuPermission("DeleteManager");
		if($PermissionCheck['return'])
		{
			$id = intval($this->request->id);

			$Widget_Manager=new Widget_Manager();
			$Widget_Manager->delete($id);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}

	/**
	 * 修改密码表单页面
	 * @author 陈晓东
	 */
	public function repwdAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改密码表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$id = $this->request->id;
		$Widget_Manager=new Widget_Manager();
		$admin = $Widget_Manager->get($id);
		include $this->tpl('manager_repwd');
	}

	/**
	 * 修改密码执行页面
	 * @author 陈晓东
	 */
	public function repwdUpdateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改密码执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		
		$id = trim($this->request->id);//用户ID
		$oldpasswd = trim($this->request->oldpasswd);//原密码
		$newpasswd = trim($this->request->newpasswd);//新密码
		$confirm = trim($this->request->confirm);//确认密码

		$Widget_Manager=new Widget_Manager();
		$password = $Widget_Manager->getOne($id, 'password');

		if($password != md5($oldpasswd)) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		if ($newpasswd != $confirm) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}

		if (strlen($newpasswd) < 6 || strlen($newpasswd) > 18) {
			$response = array('errno' => 3);
			echo json_encode($response);
			return false;
		}

		$admin=$this->manager->get();
		if($id!=$admin['id']){
			$response = array('errno' => 4);
			echo json_encode($response);
			return false;
		}

		$bind['password'] = md5($newpasswd);


		$res = $this->manager->update($id,$bind);

		if ($res) {
			$response = array('errno' => 0);
		} else {
			$response = array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

	/**
	 * 密码重置表单页面
	 * @author 陈晓东
	 */
	public function pwdresetAction()
	{
		/**
		 * 记录日志
		 */
		$log = "密码重置表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$id = $this->request->id;
		$admin = $this->manager->get($id);
		include $this->tpl('manager_pwdreset');
	}

	/**
	 * 密码重置执行页面
	 * @author 陈晓东
	 */
	public function pwdresetUpdateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "密码重置执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$id = trim($this->request->id);//用户ID
		$newpasswd = trim($this->request->newpasswd);//新密码
		$confirm = trim($this->request->confirm);//确认密码
		$group_id = $this->request->group_id;//用户组

		$password = $this->manager->getOne($id, 'password');

		if($newpasswd != '') {
			if ($newpasswd != $confirm) {
				$response = array('errno' => 2);
				echo json_encode($response);
				return false;
			}

			if (strlen($newpasswd) < 6 || strlen($newpasswd) > 18) {
				$response = array('errno' => 3);
				echo json_encode($response);
				return false;
			}

			$bind['password'] = md5($newpasswd);
			$bind['reset_password']=1;
		}


		$res = $this->manager->update($id,$bind);

		if ($res) {
			$response = array('errno' => 0);
		} else {
			$response = array('errno' => 9);
		}
		echo json_encode($response);
		return true;
	}

}
