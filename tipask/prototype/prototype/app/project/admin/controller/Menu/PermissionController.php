<?php
/**
 * 菜单权限管理
 * @author 陈晓东
 * $Id: PermissionController.php 15235 2014-08-04 06:54:44Z 334746 $
 */

class Menu_PermissionController extends AbstractController
{
	protected $sign = '?ctl=menu/permission';

	/**
	 *
	 * 修改一个菜单对应的管理员组的权限表单页面
	 */
	public function modifyByMenuAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改一个菜单对应的管理员组的权限表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);

		$menu_id = intval($this->request->menu_id);

		$Menu = new Widget_Menu();
		$menu = $Menu->get($menu_id);
		$Widget_Group = new Widget_Group();
		$group = $Widget_Group->getClass('1','group_id,name');
		$Widget_Menu_Permission = new Widget_Menu_Permission();
		$permission_list = array();
		$M = explode('|',$menu['permission_list']);
		if(is_array($M))
		{
			foreach($M as $key => $value)
			{
				$P = explode(':',$value);
				if(is_array($P))
				{
					$permission_list[$P[0]] = $P[1];
				}
			}
		}
		$permission_by_menu = $Widget_Menu_Permission->getPermissionByMenu($menu_id);
		$groupPermission = array();

		foreach($group as $key => $group_info)
		{
			foreach($permission_list as $pn => $p)
			{
				$group[$key]['permission_list'][$p] = 0;
			}
			//ksort($group[$key]['permission_list']);
		}
		foreach ($permission_by_menu as $row)
		{
			if(isset($group[$row['group_id']]['permission_list'][$row['permission']]))
			{
				$group[$row['group_id']]['permission_list'][$row['permission']] = 1;
			}
			//ksort($group[$row['group_id']]['permission_list']);
		}
		include $this->tpl('Menu_purview_modifybymenu');
	}

	/**
	 *
	 * 修改一个管理员组对应的菜单权限表单页面
	 */
	public function modifyByGroupAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改一个管理员组对应的菜单权限表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$group_id = intval($this->request->group_id);

		$Widget_Group = new Widget_Group();
		$group = $Widget_Group->get($group_id);

		$Widget_Menu = new Widget_Menu();
		$menus = $this->getChildMenu(0);		
		$menu = $this->MenuLink($menus);
		$Widget_Menu_Permission = new Widget_Menu_Permission();
		$permission_detail = $Widget_Menu_Permission->getPermissionByGroup($group_id);
		foreach ($permission_detail as $row) 
		{
			if(isset($menu[$row['menu_id']]['permission_detail'][$row['permission']]))
			{
				$menu[$row['menu_id']]['permission_detail'][$row['permission']]['selected'] = 1;
			}
		}
		include $this->tpl('Menu_purview_modifybygroup');
	}
    
    /**
	 * 
	 * 递归获取菜单
	 * @author 张骥
	 */
    public function getChildMenu($parentId)
    {
        $Menu = new Widget_Menu();
        
        $ChildMenu = $Menu->getPermissionChildMenu($parentId);
		if(count($ChildMenu['menu_list']))
		{
            foreach($ChildMenu['menu_list'] as $key=>$val)
			{
                $rescurTree = $this->getChildMenu($val['menu_id']);
                if(count($rescurTree))
				{
                    $ChildMenu['menu_list'][$key]['tree'] = $rescurTree;
                }
            }            
        }
		else
		{
			$menu_info = $Menu->get($parentId);
			$permission_list = array();
			$M = explode('|',$menu_info['permission_list']);
			if(is_array($M))
			{
				foreach($M as $key => $value)
				{
					$P = explode(':',$value);
					if(is_array($P))
					{
						$permission_list['permission_list'][$P[1]] = array('permission_name'=>$P[0],'selected'=>0);
					}
				}
			}
			ksort($permission_list);
			$ChildMenu = $permission_list;		
		}
        return $ChildMenu;
    }
    
    /**
	 * 
	 * 递归拼接
	 * @author 张骥
	 */
    public function MenuLink($menu,$level = 0)
    {
        $prefix = "";
        for($i=1;$i<=$level;$i++){
            $prefix .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        
        if($level > 0){
            $prefix .= "┠&nbsp;&nbsp;";
        }
                
        $returnMenu = array();
        
        if(isset($menu['menu_list']))
		{
			foreach($menu['menu_list'] as $k=>$v)
			{
				$v['prefix'] = $prefix;
				$v['level'] = $level+1;
				if(isset($v["tree"]))
				{	
					$tree = $v["tree"];                

					if(isset($v["tree"]["permission_list"]))
					{
						$v['permission_detail'] = $v["tree"]["permission_list"];
					}
					unset($v["tree"]);					
					$returnMenu[$k] = $v;
					
					$returnTree = $this->MenuLink($tree,($level+1));
					foreach($returnTree as $val)
					{
						$returnMenu[$val['menu_id']] = $val;
					}										
				}
				else
				{
					$returnMenu[$k] = $v;
				}               
			}		
		}
        return $returnMenu;
    }    

	/**
	 *
	 * 修改一个菜单对应的管理员组的权限执行页面
	 */
	public function updateByMenuAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改一个菜单对应的管理员组的权限执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$menu_id = $this->request->menu_id;
		$purview = $this->request->purview;
		$permission = $this->request->permission;
		$permission = is_array($permission)?$permission:array();
		$Widget_Menu_Permission = new Widget_Menu_Permission();
		$Widget_Menu = new Widget_Menu;

		$Widget_Menu_Permission -> updatePermissionByMenu($menu_id,$permission);
		$this->response->goBack();
	}

	/**
	 *
	 * 修改一个管理员组对应的菜单权限执行页面
	 */
	public function updateByGroupAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改一个管理员组对应的菜单权限执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$group_id = $this->request->group_id;
		$purview = $this->request->purview;
		$permission = $this->request->permission;

		$Widget_Menu_Permission = new Widget_Menu_Permission();
		$Widget_Menu = new Widget_Menu;

		$Widget_Menu_Permission -> updatePermissionByGroup($group_id,$permission);
		$this->response->goBack();
	}

}
