<?php
/**
 * 菜单管理
 * @author 陈晓东
 * $Id: MenuController.php 15240 2014-08-04 09:48:26Z 334746 $
 */

class MenuController extends AbstractController
{
	protected $sign = '?ctl=menu';

	/**
	 * 
	 * 菜单管理主页面
	 * @author 张骥
	 */
	public function indexAction()
	{
		/**
		 * 记录日志
		 */
		$log = "菜单管理主页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		//检查权限
		$CheckPermission = $this->manager->checkMenuPermission(0);
        
		$menuArr = $this->getChildMenu(0);

		include $this->tpl();
	}
    
    /**
	 * 
	 * 递归获取菜单
	 * @author 张骥
	 */
    public function getChildMenu($parentId)
    {
        $Menu = new Widget_Menu();
        
        $ChildMenu = $Menu->getChildMenu($parentId);
        
        if(count($ChildMenu)){
            foreach($ChildMenu as $key=>$val){
                $rescurTree = $this->getChildMenu($val['menu_id']);
                if(count($rescurTree)){
                    $ChildMenu[$key]['tree'] = $rescurTree;
					$ChildMenu[$key]['Child'] = 1;
					$ChildMenu[$key]['permission_list'] = "双击打开下级菜单";
                }
				else
				{
					$ChildMenu[$key]['Child'] = 0;
				}
            }            
        }
        
        return $ChildMenu;
    }

	/**
	 * 
	 * 添加菜单表单页面
	 * @author 张骥
	 */
	public function addAction()
	{
		/**
		 * 记录日志
		 */
		$log = "添加菜单表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);

		$Menu = new Widget_Menu();
		$root = $Menu->getRootAll();

		include $this->tpl("Menu_add");
	}

	/**
	 * 
	 * 添加菜单执行页面
	 * @author 张骥
	 */
	public function insertAction()
	{
		/**
		 * 记录日志
		 */
		$log = "添加菜单执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_INSERT);
        
		$name = $this->request->name;
		$link = $this->request->link;
		$sign = $this->request->sign;
		$sort = $this->request->sort;
        
        foreach($_REQUEST as $key=>$val){
            if(strstr($key,"parent") && $val!=0){
                $k = explode("_",$key);
                $parents[$k[1]] = $val; 
            }
        }
        if(count($parents)){
            ksort($parents);        
            $parent = $parents[count($parents)];
        }else{
            $parent = 0;
        }
        
		$Menu = new Widget_Menu();

		//检查菜单名
		if (empty($name)) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}
		//检查菜单名是否已经存在
		if ($Menu->getByName($name)) {
			$response = array('errno' => 2);
			echo json_encode($response);
			return false;
		}

		$bind['name'] = $name;
		$bind['link'] = $link;
		$bind['sign'] = $sign;
		$bind['parent'] = $parent;
		$bind['sort'] = $sort;
		$menu_id = $Menu->insert($bind);
		if(!$menu_id)
		{
			$response = array('errno' => 9);
			echo json_encode($response);
			return false;
		}

		//更新权限
		$MenuPermission = new Widget_Menu_Permission();
		$bind = array();
		$bind['menu_id'] = $menu_id;
		$bind['group_id'] = $this->manager->menu_group_id;
		$bind['purview'] = bindec(1111);
		$res=$MenuPermission->insert($bind);
		if(!$res)
			$response = array('errno' => 4);

		$response = array('errno' => 0);
		echo json_encode($response);
	}

	/**
	 * 
	 * 修改菜单表单页面
	 * @author 张骥
	 */
	public function modifyAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改菜单表单页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_UPDATE);
		$this->manager->checkMenuPermission($this->sign, 'update');

		$menu_id = $this->request->menu_id;
		$Menu = new Widget_Menu();
        
        $menu = $Menu->get($menu_id);
        $root = $Menu->getRootAll();
        
        $getParentMenu = $this->getParentMenu($menu_id);
        $MenuCount = count($getParentMenu);
        foreach($getParentMenu as $key=>$val){
            if($val['parent'] == 0){
                $menu_id = $val['menu_id'];
            }
        }
        
		include $this->tpl("Menu_modify");
	}
    
    /**
	 * 
	 * 递归获取父级菜单
	 * @author 张骥
	 */
    public function getParentMenu($menu_id)
    {
        $Menu = new Widget_Menu();
        
        $ParentMenu = $Menu->getParentMenu($menu_id);
        
        foreach($ParentMenu as $key=>$val){
            if($val['parent']>0){
                $Parent = $this->getParentMenu($val['parent']);
                foreach($Parent as $k=>$v){
                    $ParentMenu[] = $v;
                }
            }
        }
                
        return $ParentMenu;
    }    

	/**
	 * 
	 * 修改菜单执行页面
	 * @author 张骥
	 */
	public function updateAction()
	{
		/**
		 * 记录日志
		 */
		$log = "修改菜单执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$PermissionCheck = $this->manager->checkMenuPermission("UpdateMenu");
		$menu_id = $this->request->menu_id;
		$name = $this->request->name;
		$link = $this->request->link;
		$sign = $this->request->sign;
		$sort = $this->request->sort;
		$permission_list = $this->request->permission_list;

        foreach($_REQUEST as $key=>$val){
            if(strstr($key,"parent") && $val!=0 && $val != $menu_id){
                $k = explode("_",$key);
                $parents[$k[1]] = $val; 
            }
        }
        if(count($parents)){
            ksort($parents);        
            $parent = $parents[count($parents)];
        }else{
            $parent = 0;
        }

		$Menu = new Widget_Menu();

		//检查菜单名
		if (empty($name)) {
			$response = array('errno' => 1);
			echo json_encode($response);
			return false;
		}

		//更新菜单
		$bind['name'] = $name;
		$bind['link'] = $link;
		$bind['sign'] = $sign;
		$bind['parent'] = $parent;
		$bind['sort'] = $sort;
		$bind['permission_list'] = trim($permission_list);

		$res = $Menu->update($menu_id,$bind);
		if (!$res) {
			$response = array('errno' => 9);
			echo json_encode($response);
			return false;
		}

		$response = array('errno' => 0);
		echo json_encode($response);
	}

	/**
	 * 
	 * 对菜单排序执行页面
	 * @author 张骥
	 */
	public function sortAction()
	{
		/**
		 * 记录日志
		 */
		$log = "对菜单排序执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_SELECT);

		$Menu = new Widget_Menu();

		$sort = $this->request->sort;

		foreach ($sort as $menu_id => $order) {
			$bind = array('sort' => $order);			
			$Menu->update($menu_id, $bind);
		}

		$this->response->goBack();
	}

	/**
	 * 
	 * 
	 * 删除菜单执行页面
	 * @author 张骥
	 */
	public function deleteAction()
	{
		/**
		 * 记录日志
		 */
		$log = "删除菜单执行页面\n\nServerIp:\n" . $this->request->getServer('SERVER_ADDR') . "\n\nGET:\n" . var_export($_GET, true) . "\n\nPOST:\n" . var_export($_POST, true);
		$this->oLogManager->push('log', $log);
				
		$this->manager->checkMenuPermission($this->sign, Widget_Manager::MENU_PURVIEW_DELETE);

		$menu_id = intval($this->request->menu_id);

		$Menu = new Widget_Menu();
		$res = $Menu->delete($menu_id);
		if ($res)
		{
			$Widget_Menu_Permission = new Widget_Menu_Permission();
			$Widget_Menu_Permission->deleteByMenu($menu_id);
		}
		$this->response->goBack();
	}
    
    /**
	 * 
	 * 获取子级菜单
	 * @author 张骥
	 */
	public function getChildMenuAction()
	{
		$partnerId = $this->request->partnerId;
		$menu_id = $this->request->menu;
        $level = $this->request->level;
        $is_table = $this->request->is_table;
		$Menu = new Widget_Menu();
		$childmenu = $Menu->getChildMenu($partnerId);
		$menuCount = count($childmenu);
		
        if($is_table){
            $pfix = "";
                    
            for($i=1;$i<=$level;$i++){
                for($j=1;$j<=$level;$j++){
                    $pfix .= "&nbsp;&nbsp;";
                }
            }
            
            $pfix .= "┠&nbsp;&nbsp;";
            
            $return = array("count"=>$menuCount,"tr"=>'<tr class="'.$partnerId.'_'.$level.'" level="'.$level.'"><td colspan="6" style="padding:0px;border-top:none;border-bottom:none;"><table border="0" cellpadding="0" cellspacing="0">');
            
            if($menuCount){
                foreach($childmenu as $key=>$arr){
                    
                    $Child = $Menu->getChildMenu($arr['menu_id']);
					if(count($Child)>0)
					{
						$arr['permission_list'] = "双击打开下级菜单";
						$arr['update_permission'] = '';
					}
					else
					{
						$arr['update_permission'] = '| <a href="?ctl=menu/permission&ac=modify.by.menu&menu_id='.$arr['menu_id'].'">权限</a>';						
					}
					$return['tr'] .= '<tr class="hover" id="'.$arr['menu_id'].'" level="'.$level.'" ondblclick="getChildMenu(this.id,'.($level+1).')" style="cursor: pointer;">';
                    $return['tr'] .= '<td width="100"><input type="text" name="sort['.$arr['menu_id'].']" value="'.$arr['sort'].'" size="3"/></td>
                                      <td width="100">'.$arr['menu_id'].'</td>
                                      <td style="text-align:left" width="500">'.$pfix.$arr['name'].'</td>
                                      <td width="400">'.$arr['link'].'</td>
                                      <td width="400">'.$arr['permission_list'].'</td>
                                      <td width="200">
                                        <a href="javascript:;" onclick="divBox.showBox(\'?ctl=menu&ac=modify&menu_id='.$arr['menu_id'].'\', {title:\'修改菜单\',height:450,width:620});">修改</a>
                                      | <a href="javascript:;" onclick="divBox.confirmBox({content:\'是否确认删除 '.$arr['name'].' ?\',ok:function(){location.href=\'?ctl=menu&ac=delete&menu_id='.$arr['menu_id'].'\';}});">删除</a>'.
                                      $arr['update_permission'].'
                                      </td>
                                      </tr>';
                }
            }
            
            $return['tr'] .= '</table></td></tr>';
        }else{
			$menu = $Menu->get($menu_id);
			$return = array("count"=>$menuCount,"select"=>'<select id="parent_'.$level.'" level="'.$level.'" onchange="getChildMenu(this.id,'.($level+1).');" name="parent_'.$level.'">');
            if($menu['parent'] == $partnerId)
			{
				$return['select'].= '<option value="0" selected="selected">无</option>';				
				
				if($menuCount)
				{
					foreach($childmenu as $key=>$arr)
					{				
						if($arr['menu_id']!=$menu_id)
						{
							$return['select'] .= '<option value="'.$arr['menu_id'].'">'.$arr['name'].'</option>';	
						}						
					}
				}
			}
			else
			{
				$return['select'].= '<option value="0">无</option>';
				if($menuCount)
				{
					foreach($childmenu as $key=>$arr)
					{				
						if($arr['menu_id']!=$menu_id)
						{
							if($arr['menu_id']==$menu['parent'])
							{
								$return['select'] .= '<option value="'.$arr['menu_id'].'" selected>'.$arr['name'].'</option>';
							}
							else
							{
								$return['select'] .= '<option value="'.$arr['menu_id'].'">'.$arr['name'].'</option>';
							}
						}
						
					}
				}
			}            
            $return['select'] .= '</select>';            
            $return['myid'] = "parent_$level";
        }
        echo json_encode($return);        
	}
    
    /**
	 * 
	 * 获取子级菜单
	 * @author 张骥
	 */
    public function getLeftMenuAction()
    {
		$menu_id = $this->request->menu_id;
        $nextMenu = $this->request->nextMenu;
        
		//$childmenu = $this->getChildMenu($menu_id);
		$oMenuPurview = new Widget_Menu_Permission();
		$childmenu = $oMenuPurview->getTopPermissionByGroup($this->manager->menu_group_id,$menu_id);
		foreach($childmenu as $m => $m_info)
		{
			$childmenu[$m]['tree'] = $oMenuPurview->getTopPermissionByGroup($this->manager->menu_group_id,$m);
		}
        $return = array('div'=>'');
        
        $is_select = 1;
        foreach($childmenu as $k=>$v)
		{                      
			if(isset($v['tree']))
			{
				$return2['div'] = "";
				
				if($is_select)
				{
					$return2['div'] .= '<div class="accordion-group">
										<div class="accordion-heading sdb_h_active">
											<a href="#menu_id_'.$v['menu_id'].'" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle">
												<i class="icon-th"></i> '.$v['name'].'
											</a>
										</div>';
										
					 $return2['div'] .= '<div id="menu_id_'.$v['menu_id'].'" class="accordion-body in collapse" style="height: auto;">
							  <div class="accordion-inner">
								 <ul class="nav nav-list">';
					
					$is_select = 0;
				}
				else
				{					
					$return2['div'] .= '<div class="accordion-group">
										<div class="accordion-heading">
											<a href="#menu_id_'.$v['menu_id'].'" data-parent="#side_accordion" data-toggle="collapse" class="accordion-toggle collapsed">
												<i class="icon-th"></i> '.$v['name'].'
											</a>
										</div>';
										
					 $return2['div'] .= '<div id="menu_id_'.$v['menu_id'].'" class="accordion-body collapse" style="height: 0px;">
							  <div class="accordion-inner">
								 <ul class="nav nav-list">';
				}				
				$count = 0;              
				foreach($v['tree'] as $k2=>$v2)
				{
					$count++;
					$return2['div'] .= '<li id="nav_'.$v2['menu_id'].'" onclick="getRightHtml(\''.$v2['link'].'\',\'nav_'.$v2['menu_id'].'\',\'li\')"><a href="javascript:;">'.$v2['name'].'</a></li>';					
				}				
				$return2['div'] .= '</ul>
								</div>
						   </div>';
						   
				if($count > 0)
				{
					$return['div'] .= $return2['div'];
				}
				else
				{
					$return['div'] .= '<div class="accordion-group">
										<div class="accordion-heading" id="nav_'.$v['menu_id'].'">
											<a onclick="getRightHtml(\''.$v['link'].'\',\'nav_'.$v['menu_id'].'\',\'div\')" href="javascript:;" class="accordion-toggle">
												<i class="icon-th"></i> '.$v['name'].'
											</a>
										</div>';
				}
			}
			else
			{
				$return['div'] .= '<div class="accordion-group">
									<div class="accordion-heading" id="nav_'.$v['menu_id'].'">
										<a onclick="getRightHtml(\''.$v['link'].'\',\'nav_'.$v['menu_id'].'\',\'div\')" href="javascript:;" class="accordion-toggle">
											<i class="icon-th"></i> '.$v['name'].'
										</a>
									</div>';
			}			
			$return['div'] .= '</div>';           
        }        
        echo json_encode($return);
    }    
}
