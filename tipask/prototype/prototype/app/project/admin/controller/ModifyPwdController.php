<?php
/**
 * 管理员
 * @category   ManagerController
 * @Description
 * @author     陈晓东
 * $Id: ModifyPwdController.php 15195 2014-07-23 07:18:26Z 334746 $
 */

class ModifyPwdController extends AbstractController
{
	/**
	 * 权限特征
	 */
	protected $sign='?ctl=';

	/**
	 * 强制修改密码表单页面
	 * @author 陈晓东
	 */
	public function compelRepwdAction()
	{
		/**
		 * 记录日志
		 */
		$log = "强制修改密码表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$id = $this->request->id;
		$admin = $this->manager->getRow($id);
		include $this->tpl('manager_compelrepwd');
	}

	/**
	 * 强制修改密码执行页面
	 * @author 陈晓东
	 */
	public function pwdcompelUpdateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "强制修改密码执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		
		$id = trim($this->request->id);//用户ID
		$newpasswd = trim($this->request->newpasswd);//新密码
		$confirm = trim($this->request->confirm);//确认密码
		$group_id = $this->request->group_id;//用户组

		$password = $this->manager->getOne($id, 'password');

		if($newpasswd != '')
		{
			if ($newpasswd != $confirm)
			{
				$response = array('errno' => 2);
				echo json_encode($response);
				return false;
			}

			if (strlen($newpasswd) < 6 || strlen($newpasswd) > 18)
			{
				$response = array('errno' => 3);
				echo json_encode($response);
				return false;
			}

			//密码强度检测
			if (strlen($newpasswd)<7)
			{
				$response = array('errno' => 5);
				echo json_encode($response);
				return false;
			}

			$bind['password'] = md5($newpasswd);
			$bind['reset_password']=0;
		}
		$res = $this->manager->update($id,$bind);
		if ($res)
		{
			$response = array('errno' => 0);

            $cookieManager = Base_String::encode($this->manager->id.' '.$this->manager->group_id.' '.$this->manager->name.' '.'0');
            Base_Cookie::set('__Base_Manager', $cookieManager, 0);
		}
		else
		{
			$response = array('errno' => 9);
		}

		echo json_encode($response);
		return true;
	}
}
