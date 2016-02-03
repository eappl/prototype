<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_menucontrol extends base 
{

    function admin_menucontrol(& $get,& $post) 
    {
        $this->base( & $get,& $post);
        $this->load('menu');
        $this->load('department'); 
    }

    function ondefault() 
    {
        $this->onmenu();
    }

	 /* 
		intoMenu:进入菜单管理页面
		menuAdd:增加菜单
		menuRemove:删除菜单
		menuModify:修改菜单
		menuUpdatePrivilege:配置菜单权限
		$msg 页面显示消息
		$ty  消息类型(值：errormsg错误，correctmsg正确)
	 */
    function onmenu($msg='',$ty='')
    {
	   $hasIntoMenuPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoMenu");
	   if( $hasIntoMenuPrivilege['return'] )
	   {
		
			$msg && $message = $msg;
			$ty  && $type = $ty;
			
			// 获取顶层所有菜单目录
			$parentMenu  = $_ENV['menu']->getMainMenu();
			// 获取所有菜单目录
			$MenuTree    = $_ENV['menu']->getAllMenuTree();
			// 顶层菜单是否为空
			if (!empty($parentMenu)) 
			{
				// 拼接顶层菜单下拉列表
				$menuOption = '';
				foreach ( $parentMenu as $v) 
				{
					$menuOption .= "<option value='{$v['menu_id']}'>{$v['name']}</option>";
				}
			}
			include template('menu','admin');
		}
		else
		{
			$hasIntoMenuPrivilege['url'] = "?admin_main";
			__msg($hasIntoMenuPrivilege);
		
		}
    }
    
	/* 
		添加菜单
		添加菜单权限：menuAdd 
	*/
    function onmenu_add( ) 
    {
	   $hasMenuAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "menuAdd");
	   if( $hasMenuAddPrivilege['return'] )
	   {
		
			$message = '';
			// 菜单名
			$name   = isset($this->post['name']) && !empty($this->post['name']) ? trim($this->post['name']) : $message .='菜单名称必须填写';
			// 菜单父id
			$parent = isset($this->post['parent']) && $this->post['parent'] != 0 ? intval($this->post['parent']) : 0;
			$sort   = isset($this->post['sort']) && !empty($this->post['sort']) ? intval($this->post['sort']) : 0;
		  
			//$returnData = array('type'=>'','comment'=>''); 
			// 存在父菜单id时，权限列表和页面地址必须填写
			if( $parent > 0)
			{
				$this->post['link'] == '' ? $message .= '页面地址必须填写' : $link = trim($this->post['link']);
				
				// 验证权限列表值
				if( $this->post['permission_list'] == "")
				{
					$message .= "权限列表必须填写";
				}
				else
				{
				   $permission_list = trim($this->post['permission_list']);
				   $num = preg_match("/^([a-zA-Z]*:[^|^:]+)(\|[a-zA-Z]*:[^|^:]+)*$/",$permission_list);
				   if(!$num)
				   {
						$message .= "权限列表格式不正确";
						$this->onmenu('权限列表格式不正确', 'errormsg');
						exit;
				   }
				}
			}
			else
			{
				$link = '';
				$permission_list = '';
			}
			
			if ($message)
			{
				$this->onmenu($message, 'errormsg');
			}
			else
			{
				if($this->db->begin())
				{
					$result = $_ENV['menu'] -> add($name,$link,$parent,$sort,$permission_list);
					if($result)
					{
						$this->db->commit();
						$message = '菜单添加成功';
						$this->onmenu($message, '');
					}
					else
					{
						$this->db->rollBack();
						$this->onmenu('菜单添加失败', 'errormsg');
					}
				}
				else
				{
					$this->onmenu('添加菜单出错', 'errormsg');
				}
				
			}
		}
		else
		{
			$hasMenuAddPrivilege['url'] = "?admin_menu/menu";
			__msg($hasMenuAddPrivilege);
		}
		
    }
    
	// 删除菜单
	// 权限：menuRemove
    function onmenu_remove() 
    {
	   $returnBack = array();
	   $hasMenuRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "menuRemove");
	   if( $hasMenuRemovePrivilege['return'] )
	   {
			$menu_id = isset($this->post['menu_id']) ? intval($this->post['menu_id']) : 0;
			$MenuInfo = $_ENV['menu']->getByMenu($menu_id);    	
			
			if ($MenuInfo['menu_id'])
			{
				//根据菜单id获取下层目录
				$childMenu = $_ENV['menu']->getSubMenu($MenuInfo['menu_id']);
				if (count($childMenu)>0)
				{
					$returnBack = array('return'=>1,'comment'=>"该菜单下面有子菜单，请先删除子菜单");
				}
				else 
				{
					if ($_ENV['menu']->delete($menu_id))
					{
						$returnBack = array('return'=>1,'comment'=>"删除菜单成功");
					}
					else
					{
						$returnBack = array('return'=>0,'comment'=>"删除菜单失败");
					}
				}
			}
			else 
			{
				$returnBack = array('return'=>0,'comment'=>"菜单不存在");
			}
		}
		else
		{
			$returnBack = array('return'=>0,'comment'=>"你没有 删除菜单 权限！");
		}
    	// 统一输出返回结果
		exit(json_encode($returnBack));
    }
    // 修改菜单
	// 权限：menuModify
    function onmenu_modify() 
    {
	   $hasMenuModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "menuModify");
	   if( $hasMenuModifyPrivilege['return'] )
	   {
			$message = '';
			$menu_id = isset($this->post['menu_id']) ? intval($this->post['menu_id']) : '菜单ID为空，';
			$name    = isset($this->post['name']) && !empty($this->post['name']) ? trim($this->post['name']) : $message .= '菜单名称不能为空，';
			$sort    = isset($this->post['sort']) && !empty($this->post['sort']) ? intval($this->post['sort']) : 0;
			$parent  = isset($this->post['parent']) && $this->post['parent'] != 0 ? intval($this->post['parent']) : 0;
			
			if( $parent > 0)
			{
				$this->post['link'] == '' ? $message .= '页面地址必须填写' : $link = trim($this->post['link']);
				if( $this->post['permission_list'] == "")
				{
					$message .= "权限列表必须填写";
				}
				else
				{
				   $permission_list = trim($this->post['permission_list']);
				   $num = preg_match("/^([a-zA-Z]*:[^|^:]+)(\|[a-zA-Z]*:[^|^:]+)*$/",$permission_list);
				   if(!$num)
				   {
						$this->onmenu('权限列表格式不正确', 'errormsg');
						exit;
				   }
				}
			}
			else
			{
				$link = '';
				$permission_list = '';
			}
			
			if ($message)
			{
				$this->onmenu($message, 'errormsg');
			}
			else
			{
				$result = $_ENV['menu'] -> update($menu_id,$name,$link,$parent,$sort,$permission_list);
				if($result)
				{
					$this->onmenu('菜单修改成功', '');
				}
				else
				{
					$this->onmenu('菜单修改失败', 'errormsg');
				}
			}
		}
		else
		{
			$hasMenuModifyPrivilege['url'] = "?admin_menu/menu";
			__msg($hasMenuModifyPrivilege);
		}
    }

    // 获取选中的父级菜单列表
    function ongetCheckedMenu()
    {
    	$menu_id = isset($this->post['menu_id']) ? intval($this->post['menu_id']) : 0;
		// 获取顶层全部目录
    	$parentMenu  = $_ENV['menu']->getMainMenu();
    	$menuOption  = '';
    	
    	if($menu_id)
    	{
		   // 根据菜单id获取单个菜单信息
    		$menuInfo = $_ENV['menu']->getByMenu($menu_id);
    	}
    	
    	if($menuInfo['menu_id'])
    	{
    		if (!empty($parentMenu))
    		{
    			foreach ( $parentMenu as $v)
    			{
				    // 判断是否该菜单是这个id的父级菜单，是选中
    				if($menuInfo['parent'] == $v['menu_id'])
    				{
    					$menuOption .= "<option value='{$v['menu_id']}' selected>{$v['name']}</option>";
    				}
    				else
    				{
    					$menuOption .= "<option value='{$v['menu_id']}'>{$v['name']}</option>";
    				}
    				
    			}
    		}
    	}
    	echo $menuOption;
    }
    // 菜单 - 配置部门权限
    function onprivilegeConfig()
    {
    	$menu_id  = intval($this->get[2]);
    	$menuInfo = $_ENV['menu']->getByMenu($menu_id);
    	$type     = isset($this->get[4]) ? trim($this->get[4]) : '';
    	
    	if (isset($this->get[3]))
    	{
    		$message = trim($this->get[3]);
    	}
    	
    	if ( $menuInfo['menu_id'] )
    	{
    		$menuId   = $menu_id;
    		$menuName = $menuInfo['name'];
    	}
    	else 
    	{
    		$this->onmenu('没有该菜单', 'errormsg');
			header("Location:?admin_menu/menu");
    	}
    		
    	$menuPrivilege = array();
    	$parentDepartment = $_ENV['department']->getParentDepartment(); // 获取父部门
    	$permissionMenu   = $_ENV['menu']->getPermissionByMenu($menu_id); //获取菜单下各部门的权限
    	
    	if ($parentDepartment)
    	{
    		if ($permissionMenu && $permissionMenu['permission_detail'])
    		{
    			foreach ($permissionMenu['permission_detail'] as $k=>$v)
    			{
    				$menuPrivilege[$k] = array($v['name']);
    				foreach ($parentDepartment as $v1)
    				{
    					$strChecked = '';
    					$departmentList = isset($v['department']) ? $v['department'] : array();
    					if(in_array($v1['id'],$departmentList))
    					{
    						$strChecked = '<input type="checkbox" value="1" name="PermissionDetailList['.$v1['id'].']['.$k.']" checked="checked">';
    					}
    					else
    					{
    						$strChecked = '<input type="checkbox" value="1" name="PermissionDetailList['.$v1['id'].']['.$k.']">';
    					}
    					array_push($menuPrivilege[$k],$strChecked);
    				}
    			}
    			include template('privilege_department','admin');
    		}
    		else
    		{
    			$this->onmenu('该菜单下没有权限列表', 'errormsg');
    		}
    	}
    	else
    	{
    		$this->onmenu('没有配置部门，请先配置部门。', 'errormsg');
    	}
    		
    }
    // 菜单 - 更新部门权限
	// 配置菜单权限：menuUpdatePrivilege
    function onmenu_updatePrivilegeConfig()
    {
	   $hasMenuUpdatePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "menuUpdatePrivilege");
	   if( $hasMenuUpdatePrivilege['return'] )
	   {
			$menu_id = isset($this->post['menu_id']) ? intval($this->post['menu_id']) : 0;
			$PermissionDetailList = isset($this->post['PermissionDetailList']) ? $this->post['PermissionDetailList'] :false;
			if ($menu_id)
			{
				$result = $_ENV['menu'] -> updatePermissionByMenu($menu_id,$PermissionDetailList);
				$reurnBack = array();
				if($result)
				{
					$reurnBack = array('comment'=>"修改成功", 'type'=>"correctmsg");
				}
				else 
				{
					$reurnBack = array('comment'=>"修改出错，请重新修改！", 'type'=>"errormsg");
				}
			}
			else
			{
				$reurnBack = array('comment'=>"该菜单不存在", 'type'=>"errormsg");
			} 
			
			$this->onmenu($reurnBack['comment'] , $reurnBack['type']);
		}
		else
		{
			$hasMenuUpdatePrivilege['url'] = "?admin_menu/menu";
			__msg($hasMenuUpdatePrivilege);
		}
	}
}

?>
