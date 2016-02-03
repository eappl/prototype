<?php
/**
 * @author Chen <cxd032404@hotmail.com>
 * $Id: IndexController.php 15233 2014-08-04 06:46:08Z 334746 $
 */

class IndexController extends AbstractController
{

	/**
	 * 框架页
	 */
    public function indexAction()
    {
    	$oMenu = new Widget_Menu();
    	$oMenuPurview = new Widget_Menu_Permission();
		$allowedMenuArr = $oMenuPurview->getTopPermissionByGroup($this->manager->menu_group_id,0);
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
                }
            }            
        }
        
        return $ChildMenu;
    }

    public function homeAction()
    {
        include $this->tpl();
    }
}