<?php
!defined('IN_TIPASK') && exit('Access Denied');
class menumodel {

    var $db;
    var $base;

    function menumodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    //添加一个菜单页面
    //$name：菜单名称
    //$link：页面地址，只截取主URL后面的
    //$parent：上级页面ID
    //$sort：排序权重，数值越大越靠后
    //$permission_list：该页面提供的权限列表 格式 a:b|c:d|e:f
    function add($name,$link,$parent,$sort,$permission_list)
    {
        $add = "INSERT INTO `" . DB_TABLEPRE . "menu`(`name` ,`link` , `parent` , `sort`,`permission_list`) VALUES ";      
        $add .= "('$name','$link',$parent,$sort,'$permission_list')";
        return $this->db->query($add);
    }
    //修改一个菜单页面
    //$menu_id：菜单ID
    //$name：菜单名称
    //$link：页面地址，只截取主URL后面的
    //$parent：上级页面ID
    //$sort：排序权重，数值越大越靠后
    //$permission_list：该页面提供的权限列表 格式 a:b|c:d|e:f
    function update($menu_id,$name,$link,$parent,$sort,$permission_list)
    {        
        $this->db->begin();
        $update = 'UPDATE `' .  DB_TABLEPRE . "menu` SET `name` = '" . $name . "',`link` = '" . $link . "',`parent` = " . $parent .  ",`sort` = " . $sort .  ",`permission_list` = '" . $permission_list .  "' WHERE `menu_id` = " . intval($menu_id);      
        $update_menu = $this->db->query($update);
		$update_rows = $this -> db -> affected_rows();
		//拆解权限列表信息
		$tmp_1 = explode("|",$permission_list);
		if(is_array($tmp_1))
		{
			foreach($tmp_1 as $key => $value)
			{										
				$tmp_2 = explode(":",$value);
				if($tmp_2[0]!='')
				{
				    $permission_detail[$tmp_2[0]] = "'".$tmp_2[0]."'";
			    }	
			}
			//更新后权限列表是否还有存留
			if(is_array($permission_detail))
			{
			    //删除多余的部分
			    $p_list = implode(",",$permission_detail);
	        	$delete = 'delete from  `' .  DB_TABLEPRE . "menu_permission` WHERE `menu_id`= " . intval($menu_id) . " and permission not in ( ". $p_list  . " ) ";
			}
		} 
		else
		{
			//删除全部
			$delete = 'delete from  `' .  DB_TABLEPRE . "menu_permission` WHERE `menu_id`= " . intval($menu_id) ;
		}
		$delete =  $this->db->query($delete);
		$delete_rows = $this->db->affected_rows();
		
		if($update_rows >= 0)
		{
			$this->db->commit();
			return true; 
		}
		else
		{
			$this->db->rollback();
			return false;    
		} 	
		       
    }
    //删除一个菜单页面
    //$menu_id：菜单ID
    function delete($menu_id)
    {                
        //获取目录信息
        $Menu = $this -> getByMenu(intval($menu_id));
        //目录是否存在
        if($Menu['menu_id'])
        {
	        //是否为顶层目录
	        if($Menu['parent']>0)
	        {
	        	//获取其下子目录
	        	$SubMenu = $this -> getSubMenu($Menu['menu_id']);
	        	//如果其下子目录存在则不可删除
	        	if(count($SubMenu))
	        	{
	        		return false;	
	        	}
	        	else
		        {
		        	//事务开始
		        	$this->db->begin();
		        	//删除目录
		        	$delete = 'delete from  `' .  DB_TABLEPRE . "menu` WHERE `menu_id`= " . intval($menu_id);
		        	$this->db->query($delete);
		        	$delete_menu = $this->db->affected_rows();
		        	//删除权限
	        	    $delete = 'delete from  `' .  DB_TABLEPRE . "menu_permission` WHERE `menu_id`= " . intval($menu_id);
	        	    $this->db->query($delete);
	        	    $delete_permission = $this->db->affected_rows();					
	        	    if($delete_menu)
                    {
                        $this->db->commit();
                        return true; 
                    }
                    else
                    {
                        $this->db->rollback();
                        return false;    
                    } 	 
		        }
	        }
	        else
	        {	        	
	        	$delete = 'delete from  `' .  DB_TABLEPRE . "menu` WHERE `menu_id`= " . intval($menu_id);	        	
	        	return $this->db->query($delete); 
	        }
    	}
    	else
    	{
    		return false;	
    	}
    }
    //根据页面ID获取单个页面信息
    //$menu_id:页面ID
    function getByMenu($menu_id)
    {
        if($menu_id)
        {
	        $sql = "select menu_id,name,link,permission_list,sort,parent from `" . DB_TABLEPRE . "menu` where menu_id = $menu_id limit 1";      
	        $query = $this->db->query($sql);
			while($data = $this->db->fetch_array($query)) 
			{
				$Menu = $data;
			}
			//拆解权限列表信息
			$tmp_1 = explode("|",$Menu['permission_list']);
			if(is_array($tmp_1))
			{
				foreach($tmp_1 as $key => $value)
				{										
					if(!isset($Menu['permission_detail']))
					{
						$Menu['permission_detail'] = array();	
					}
					$tmp_2 = explode(":",$value);
					$Menu['permission_detail'][$tmp_2[0]] = array('name'=>$tmp_2[1]);	
				}	
			}
			return $Menu;			
	    }
	    else
	    {
	    	return false;	
	    }
    }
    //根据路径获取单个页面信息
    //$link:页面路径（只截取主域名之后的部分）
    function getByLink($link)
    {
        if($link)
        {
	        $sql = "select menu_id,name,link,permission_list,sort,parent from `" . DB_TABLEPRE . "menu` where link = '".trim($link)."' limit 1";      
	        $query = $this->db->query($sql);
			while($data = $this->db->fetch_array($query)) 
			{
				$Menu = $data;
			}
			//拆解权限列表信息
			$tmp_1 = explode("|",$Menu['permission_list']);
			if(is_array($tmp_1))
			{
				foreach($tmp_1 as $key => $value)
				{										
					if(!isset($Menu['permission_detail']))
					{
						$Menu['permission_detail'] = array();	
					}
					$tmp_2 = explode(":",$value);
					$Menu['permission_detail'][$tmp_2[0]] = array('name'=>$tmp_2[1]);	
				}	
			}
			return $Menu;			
	    }
	    else
	    {
	    	return false;	
	    }
    }
    //获取完整目录树
    function getAllMenuTree()
    {
        $sql = "select menu_id,name,link,permission_list,sort,parent from `" . DB_TABLEPRE . "menu` where 1 order by parent";      
        $query = $this->db->query($sql);
		
		while($data = $this->db->fetch_array($query)) 
		{
			//顶层目录
			if($data['parent']==0)
			{
				$MenuTree[$data['menu_id']] = $data;
			}
			//下层目录
			else
			{
				$tmp_1 = explode("|",$data['permission_list']);
				if(is_array($tmp_1))
				{
					foreach($tmp_1 as $key => $value)
					{										
						//拆解权限列表信息
						if(!isset($data['permission_detail']))
						{
							$data['permission_detail'] = array();	
						}
						$tmp_2 = explode(":",$value);
						$data['permission_detail'][$tmp_2[0]] = array('name' => $tmp_2[1]);	
					}	
				}
				$MenuTree[$data['parent']]['sub_menu'][$data['menu_id']] = $data;	
			}
		}
		return $MenuTree;			
    }    
    //获取顶层全部目录
    function getMainMenu()
    {
		$sql = "select menu_id,name from `" . DB_TABLEPRE . "menu` where `parent` = 0 order by sort,menu_id";
		$query = $this->db->query($sql);
		while($data = $this->db->fetch_array($query)) 
		{
			if(!isset($MainMenu))
			{
				$MainMenu = array();
			}
			$MainMenu[$data['menu_id']] = $data;
		}
		return $MainMenu;								
    }
    //获取指定顶层目录的下层目录
    //$parent：上册目录的目录ID
    function getSubMenu($parent)
    {
		$sql = "select menu_id,name,link from `" . DB_TABLEPRE . "menu` where `parent` = ".intval($parent)." order by sort,menu_id";
		$query = $this->db->query($sql);
		while($data = $this->db->fetch_array($query)) 
		{
			if(!isset($MainMenu))
			{
				$MainMenu = array();
			}
			$MainMenu[$data['menu_id']] = $data;
		}
		return $MainMenu;								
    }
    //获取拥有权限的顶层目录
    //$department_id:部门ID
    function getPermittedMainMenu($operator)
    {
    	if(strlen($operator)>0)
    	{
        	//获取管理员信息，取出部门信息
			$sql = "select did from `" . DB_TABLEPRE . "operator` where login_name = '".trim($operator)."' limit 1";
			$department_id = $this->db->result_first($sql);
    		//如果查到部门ID是有效信息
    		if($department_id>=0)
    		{
        		//获取该部门拥有进入权限的下层菜单列表
        		$sql = "select distinct(menu_id) as sub_id from `" . DB_TABLEPRE . "menu_permission` where `department_id` = $department_id";
        		$query = $this->db->query($sql);
    			while($data = $this->db->fetch_array($query)) 
    			{
    				if(!isset($sub_id))
    				{
    					$sub_id = array();
    				}
    				$sub_id[] = $data['sub_id'];
    			}
    			$sub_id_list = implode(",",$sub_id);
    			if(strlen($sub_id_list)>0)
    			{
    				//根据下层菜单列表获取上层菜单列表
    				$sql = "select m.menu_id,m.name from `" . DB_TABLEPRE . "menu` as m,(select distinct(parent) as parent from `" . DB_TABLEPRE . "menu` where menu_id in ($sub_id_list)) as parent_list where m.menu_id = parent_list.parent order by m.sort,m.menu_id";
        			$query = $this->db->query($sql);
    				while($data = $this->db->fetch_array($query)) 
    				{
    					if(!isset($MainMenu))
    					{
    						$MainMenu = array();
    					}
    					$MainMenu[$data['menu_id']] = $data;
    				}
    				return $MainMenu;						
    			}
    			else
    			{
    				return false;
    			}    		    
    		}
    		else
    		{
                return false;    
            }								
    	}
    	else
    	{
    		return false;	
    	}	
    }
    //获取拥有权限的指定顶层目录下的子目录
    //$department_id:部门ID
    //$parent:顶层目录ID
    function getPermittedSubMenu($operator,$parent)
    {
		if(strlen($operator)>0&&$parent)
		{
        	//获取管理员信息，取出部门信息
			$sql = "select did from `" . DB_TABLEPRE . "operator` where login_name = '".trim($operator)."' limit 1";
			$department_id = $this->db->result_first($sql);
    		//如果查到部门ID是有效信息
    		if($department_id>=0)
			{
    			//获取该部门拥有进入权限的下层菜单列表
    			$sql = "select distinct(menu_id) as sub_id from `" . DB_TABLEPRE . "menu_permission` where `department_id` = $department_id";
    			$query = $this->db->query($sql);
    			while($data = $this->db->fetch_array($query)) 
    			{
    				if(!isset($sub_id))
    				{
    					$sub_id = array();
    				}
    				$sub_id[] = $data['sub_id'];
    			}
    			$sub_id_list = implode(",",$sub_id);
    			if(strlen($sub_id_list)>0)
    			{
    				//根据顶层菜单和所有下层菜单列表筛选出下层菜单列表
    				$sql = "select menu_id,name,link from `" . DB_TABLEPRE . "menu` where menu_id in ($sub_id_list) and parent = $parent order by sort,menu_id";
        			$query = $this->db->query($sql);
    				while($data = $this->db->fetch_array($query)) 
    				{
    					if(!isset($SubMenu))
    					{
    						$SubMenu = array();
    					}
    					$SubMenu[$data['menu_id']] = $data;
    				}
    				return $SubMenu;						
    			}
    			else
    			{
    				return false;
    			}
    		}
	        else
			{
				return false;
			}	
		}
		else
		{
			return false;	
		}   	
    }
    //获取某菜单下各部门的权限
    //$menu_id：菜单ID
    function getPermissionByMenu($menu_id)
    {
        //获取目录信息
        $Menu = $this -> getByMenu(intval($menu_id));
        //目录是否存在
        if($Menu['menu_id'])
        {
        	//只有下层菜单才有权限列表
        	if($Menu['parent'])
        	{
				//根据顶层菜单和所有下层菜单列表筛选出下层菜单列表
				$sql = "select permission,department_id from `" . DB_TABLEPRE . "menu_permission` where menu_id = ".intval($menu_id);
    		    $query = $this->db->query($sql);
				while($data = $this->db->fetch_array($query)) 
				{
					if(isset($Menu['permission_detail'][$data['permission']]))
					{
						$Menu['permission_detail'][$data['permission']]['department'][$data['department_id']] = $data['department_id'];
					}
				}
				return $Menu;
        	}
        	else
        	{
        		return false;	
        	}
        }
        else
        {
        	return false;	
        }    	
    	
    }
    //获取某部门的权限
    //$department_id：部门ID
    function getPermissionByDepartment($department_id)
    {
        //获取目录树
        $MenuTree = $this->getAllMenuTree();
		//根据部门获得菜单权限列表
		$sql = "select permission,menu_id from `" . DB_TABLEPRE . "menu_permission` where department_id = ".intval($department_id);
		$query = $this->db->query($sql);
		while($data = $this->db->fetch_array($query)) 
		{
			if(!isset($MenuList[$data['menu_id']]))
			{
				$MenuList[$data['menu_id']] = $this->getByMenu($data['menu_id']);			
			}
			if(isset($MenuTree[$MenuList[$data['menu_id']]['parent']]['sub_menu'][$data['menu_id']]['permission_detail'][$data['permission']]))
			{
				$MenuTree[$MenuList[$data['menu_id']]['parent']]['sub_menu'][$data['menu_id']]['permission_detail'][$data['permission']]['selected'] = 1;
			}	

		}
		return $MenuTree;   	    	
    }
    //根据菜单单页面更新各权限组的权限
    //$menu_id：菜单ID
    //$PermissionDetailList：权限详细列表 格式 array($department_id_1=>array($pemission1=>1,$permission2=>1),$department_id_2=>array($permission3=>1,$permission4=>1),……)
    function updatePermissionByMenu($menu_id,$PermissionDetailList)
    {        
    	//事务开始
    	$this->db->begin();
    	//清空原有权限
    	$delete = 'delete from  `' .  DB_TABLEPRE . "menu_permission` WHERE `menu_id`= " . intval($menu_id);
    	
        $this->db->query($delete);
        //获取目录信息
        $Menu = $this -> getByMenu(intval($menu_id));
        //目录是否存在
        if($Menu['menu_id'])
        {
        	//只有下层菜单才有权限列表
        	if($Menu['parent'])
        	{
                foreach($PermissionDetailList as $department_id => $permission_list)
                {
                    foreach($permission_list as $permission=>$kpermission)
                    {
                        //如果该权限有配置则尝试添加
                        if(isset($Menu['permission_detail'][$permission]))
                        {
                            //逐条添加，如果失败则回滚
                            $add = "INSERT INTO `" . DB_TABLEPRE . "menu_permission`(`menu_id` ,`department_id` ,`permission`) VALUES ";      
                            $add .= "(".intval($menu_id).",".intval($department_id).",'".trim($permission)."')";
                        
                            $this->db->query($add);
                            if($this->db->affected_rows()<=0)
                            {
                                $this->db->rollback();
                                return false;    
                            }                            
                        }
                        else
                        {
                            //跳过    
                        }
                    }    
                }
                
                //如果全部成功（或无失败则提交）
                $this->db->commit();
                return true;
        	}
        	else
        	{
        		return false;	
        	}
        }
        else
        {
        	return false;	
        }    
    }
    //根据菜单权限组更新各菜单的权限
    //$department_id：菜单ID
    //$PermissionDetailList：权限详细列表 格式 array($menu_id_1=>array($pemission1=>1,$permission2=>1),$menu_id_2=>array($permission3=>1,$permission4=>1),……)
    function updatePermissionByDepartment($department_id,$PermissionDetailList)
    {        
    	$this->db->begin();
    	//清空原有权限
    	$delete = 'delete from  `' .  DB_TABLEPRE . "menu_permission` WHERE `department_id`= " . intval($department_id) ;
        $this->db->query($delete);
		
        foreach($PermissionDetailList as $menu_id => $permission_list)
        {
            //获取目录信息
            $Menu = $this -> getByMenu(intval($menu_id));
            //目录是否存在
            if($Menu['menu_id'])
            {
            	//只有下层菜单才有权限列表
            	if($Menu['parent'])
            	{              
                    foreach($permission_list as $permission => $kpermission)
                    {
                        //如果该权限有配置则尝试添加
                        if(isset($Menu['permission_detail'][$permission]))
                        {
                            //逐条添加，如果失败则回滚
                            $add = "INSERT INTO `" . DB_TABLEPRE . "menu_permission`(`menu_id` ,`department_id` ,`permission`) VALUES ";      
                            $add .= "(".intval($menu_id).",".intval($department_id).",'".trim($permission)."')";
                            $this->db->query($add);
                            if($this->db->affected_rows()<=0)
                            {
                                $this->db->rollback();
                                return false;    
                            }                            
                        }
                        else
                        {
                            //跳过    
                        }
                	}
                }
                else
                {
                	//跳过
                }     
            }
            else
            {
                //跳过    
            }    
        }
        //如果全部成功（或无失败则提交）
        $this->db->commit();
        return true;   
    }
    //检查权限
    //$department_id：权限组ID
    //$link：菜单路径
    //$permission：权限
    function checkPermission($operator,$link,$permission)
    {		
		// 对$link进行匹配 结果像：admin_question/view
		if ( preg_match("/(.+?\/[a-zA-Z]+)(_|\/)/",$link,$matches) )
		{
			$link = $matches[1];
		}
				
        //获取目录信息
        $Menu = $this -> getByLink(trim($link));
        //目录是否存在
        if($Menu['menu_id'])
        {
        	//只有下层菜单才有权限列表
        	if($Menu['parent'])
        	{
				//判断权限是否为空
				if(strlen($permission)>=1)
				{
    				//如果该权限有配置
    				if(isset($Menu['permission_detail'][$permission]))
    				{    				
        				//获取管理员信息，取出部门信息
        				$sql = "select did from `" . DB_TABLEPRE . "operator` where login_name = '".trim($operator)."' limit 1";
        				$department_id = $this->db->result_first($sql);
        				//根据顶层菜单和所有下层菜单列表筛选出下层菜单列表，适用于实际操作权限
        				$sql = "select menu_id from `" . DB_TABLEPRE . "menu_permission` where department_id = ".intval($department_id)." and menu_id = ".intval($Menu['menu_id']) ." and permission = '".$permission."' limit 1";
            		    $query = $this->db->query($sql);
            		    $num = $this->db->num_rows($query);
            		    if($num)
            		    {
            		        return array("return"=>1);    
            		    }
            		    else
            		    {
                            return array("return"=>0,"comment"=>"对不起，您没有执行 ".$Menu['permission_detail'][$permission]['name']." 的权限");    
                        }
    				}
    				else
    				{
                        return array("return" => 0,'comment'=>'无此权限');    
                    }				    
				}
				else
				{
    				//获取管理员信息，取出部门信息
    				$sql = "select did from `" . DB_TABLEPRE . "operator` where login_name = '".trim($operator)."' limit 1";
    				$department_id = $this->db->result_first($sql);
    				//权限为空，取出任意一条既可，适用于进入页面的权限
    				$sql = "select menu_id from `" . DB_TABLEPRE . "menu_permission` where department_id = ".intval($department_id)." and menu_id = ".intval($Menu['menu_id']) ." limit 1";
        		    $query = $this->db->query($sql);
        		    $num = $this->db->num_rows($query);
        		    if($num)
        		    {
        		        return array("return"=>1);    
        		    }
        		    else
        		    {
                        return array("return"=>0,"comment"=>"对不起，您没有进入页面 ".$Menu['name']." 的权限");    
                    }                     
                }
        	}
        	else
        	{
        		return array("return"=>0,"comment"=>"当前目录为父级目录，不包含权限");	
        	}
        }
        else
        {
        	return array("return"=>0,"comment"=>"无此页面");	;	
        }  
    }  
}
?>
