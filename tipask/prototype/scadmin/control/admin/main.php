<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_maincontrol extends base {

    function admin_maincontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load('setting');
		$this->load('menu');
		$this->load('operator');
		
    }

    function ondefault()
	{
	
	   // 获取该登陆客服有全新的所有顶级菜单列表
	   $menuList = $_ENV['menu'] -> getPermittedMainMenu($this->ask_login_name);
	   $nowTime  = date("Y-m-d H:i:s");
	   $ask_login_name = $this->ask_login_name;
	   if( !empty($menuList) )
	   {	
	        // copy一份顶层菜单列表
			$copyMenuList = $menuList;
	        // 顶层菜单的第一个菜单id值
			$firstMenu = array_shift($copyMenuList);
			// 获取第一个目录列表，默认显示
			$subMenu  = $_ENV['menu'] -> getPermittedSubMenu($this->ask_login_name ,$firstMenu['menu_id']);
			$i = 0;
			// 获取第一个目录名称，默认显示
			$menuName = $firstMenu['name'];
			$operator_info = $_ENV['operator']->getByColumn('login_name',$this->ask_login_name);
			include template('index','admin');
	   }
	   else
	   {
			echo " <h1 style='color:red'>你没有权限进入任何目录</h1>";
	   }
    }
    function onstat() 
	{
  
    	$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
        $serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
        $fileupload = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '<font color="red">否</font>';       
        $dbversion = $this->db->version();
        $magic_quote_gpc = get_magic_quotes_gpc() ? 'On' : 'Off';
        $allow_url_fopen = ini_get('allow_url_fopen') ? 'On' : 'Off';
		
		
        include template('stat','admin');
    }

    function ontest() {

    }


    function _sizecount($filesize) {
        if($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' Bytes';
        }
        return $filesize;
    }
	// 根据菜单id获取下级目录
	function ongetSubMenu()
	{
		// 获取post过来菜单id
		$menu_id = isset($this->post['menu_id']) ? intval($this->post['menu_id']) : 0;
		$retunData = array();
		if ( $menu_id )
		{
			// 获取该客服的指定顶层目录的下层目录
			$subMenu = $_ENV['menu'] -> getPermittedSubMenu($this->ask_login_name ,$menu_id);
			if( !empty($subMenu) )
			{
				$subMenuList = "";
			    foreach ( $subMenu as $menu)
				{
					$subMenuList .=  "<li><a href='?{$menu['link']}' target='main'>{$menu['name']}</a> </li>";
				}
				$retunData =  array('return'=>1 ,'data'=>$subMenuList);
			}
			else
			{
				$retunData =  array('return'=>0,'commnet'=>"没有下级菜单");
			}
		}
		else
		{
			$retunData =  array('return'=>0,'commnet'=>"没有下级菜单");
		}
		exit(json_encode($retunData));
	}

}
?>