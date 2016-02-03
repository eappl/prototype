<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_systemcontrol extends base {

    function admin_systemcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("operator");
        $this->load("department");
        $this->load("job");
        $this->load("post");
        $this->load("hekp_config");
        $this->load("category");
        $this->load("worktime");
		$this->load("menu");
    }

    function ondefault($message='')
	{
       $this->onoperator();
    }
	
	/* 
		操作员显示页面
		进入操作员页面权限：intoOperator
	*/
    function onoperator($msg='', $ty='') 
	{
		$hasIntoOperatorPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoOperator");
		// 是否有进入操作员管理页面权限
		if ( $hasIntoOperatorPrivilege['return'] )
		{
			isset($this->get[2]) && $operator_info = $_ENV['operator']->get(intval($this->get[2]));	    	
			$department_option = isset($this->get[2])?$_ENV['department']->get_categrory_tree($operator_info['did']):$_ENV['department']->get_categrory_tree();  	
			$department_option1 = $_ENV['department']->get_categrory_tree();  	
			$job_option = $_ENV['job']->getOptions();
			$post_option = $_ENV['post']->getOptions();  	
			@$page = max(1, intval($this->get[3]));    	
			$pagesize = $this->setting['list_default'];
			$startindex = ($page - 1) * $pagesize;
			$rownum = $this->db->fetch_total("operator");
			$operator_list = $_ENV['operator']->getList($startindex, $pagesize);
			$departstr = page($rownum, $pagesize, $page, "admin_system/operator/");
			
			$msg && $message = $msg;
			$ty && $type = $ty;
			include template('operator','admin');
		}
		else 
		{
			$hasIntoOperatorPrivilege['url'] = "?admin_main";
			__msg($hasIntoOperatorPrivilege);
		}
        
    }
    /* 
	操作员查询
	操作员查询权限 operatorSearch
	*/
    function onoperator_search() 
	{
		$hasSearchOperatorPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "operatorSearch");
		if ( $hasSearchOperatorPrivilege['return'] )
		{
			$login_name_search = isset($this->post['login_name']) && $this->post['login_name']!=''?trim($this->post['login_name']):(isset($this->get[2])?urldecode($this->get[2]):'');
			$name_search = isset($this->post['name']) && $this->post['name']!=''?trim($this->post['name']):(isset($this->get[3])?urldecode($this->get[3]):'');
			$cno_search = isset($this->post['cno']) && $this->post['cno']!=''?trim($this->post['cno']):(isset($this->get[4])?urldecode($this->get[4]):'');
			$department_search = isset($this->post['department']) && $this->post['department']!=-1?intval($this->post['department']):(isset($this->get[5])?intval($this->get[5]):0);
			$post_search = isset($this->post['post']) && $this->post['post']!=-1?intval($this->post['post']):(isset($this->get[6])?intval($this->get[6]):0);
			$job_search = isset($this->post['job']) && $this->post['job']!=-1?intval($this->post['job']):(isset($this->get[7])?intval($this->get[7]):0);

		   
			$where_search1 = $_ENV['operator']->getWhere($login_name_search, $name_search,$cno_search,$department_search,$post_search,$job_search,true);
			$where_search2 = $_ENV['operator']->getWhere($login_name_search, $name_search,$cno_search,$department_search,$post_search,$job_search,false);
				
			$job_option = $_ENV['job']->getOptions();
			$post_option = $_ENV['post']->getOptions();
			 
			@$page = max(1, intval($this->get[8]));   	
			$pagesize = $this->setting['list_default'];
			$startindex = ($page - 1) * $pagesize;  
			
			$rownum = $_ENV['operator']->getNum($where_search2); 
	
			$operator_list = $_ENV['operator']->getList($startindex, $pagesize,$where_search1);
			$department_option1 = $_ENV['department']->get_categrory_tree($department_search);
			
			$departstr = page($rownum, $pagesize, $page, "admin_system/operator_search/$login_name_search/$name_search/$cno_search/$department_search/$post_search/$job_search");
			include template('operator','admin');
		}
		else
		{
			$hasSearchOperatorPrivilege['url'] = "?admin_system/operator";
			__msg($hasSearchOperatorPrivilege);
		}
        
    }
    
    /*
	操作员删除
	删除操作员权限：operatorRemove
	*/
    function onoperator_remove() 
	{
		$hasRemoveOperatorPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "operatorRemove");
		if ( $hasRemoveOperatorPrivilege['return'] )
		{
			isset($this->get[2]) && !empty($this->get[2]) && $operator_arr = $_ENV['operator']->get($this->get[2]);
			if(isset($operator_arr) && !empty($operator_arr)){  		
				$this->db->query("DELETE FROM ".DB_TABLEPRE."operator WHERE id=".$this->get[2]);
				$this->db->query("UPDATE ".DB_TABLEPRE."answer SET is_delete=1 WHERE author= '".$operator_arr['login_name']."'");
				$this->db->query("UPDATE ".DB_TABLEPRE."department SET num=num-1 WHERE id=".$operator_arr['did']);
				$this->db->query("UPDATE ".DB_TABLEPRE."post SET num=num-1 WHERE id=".$operator_arr['pid']); 
				$this->db->query("UPDATE ".DB_TABLEPRE."job SET num=num-1 WHERE id=".$operator_arr['jid']);
			}
			$department_option = $_ENV['department']->get_categrory_tree();
			$department_option1 = $department_option;  	
			$job_option = $_ENV['job']->getOptions();
			$post_option = $_ENV['post']->getOptions();  	
			@$page = max(1, intval($this->get[3]));    	
			$pagesize = $this->setting['list_default'];
			$startindex = ($page - 1) * $pagesize;
			$rownum = $this->db->fetch_total("operator");
			$operator_list = $_ENV['operator']->getList($startindex, $pagesize);
			$departstr = page($rownum, $pagesize, $page, "admin_system/operator_remove/");
			
			$message = "操作员删除成功";  		
			include template('operator','admin');     
		}
		else
		{
			$hasRemoveOperatorPrivilege['url'] ="?admin_system/operator";
			__msg($hasRemoveOperatorPrivilege);
		
		}
    	
    }
    
    /* 
	操作员添加
	添加操作员权限：operatorAdd
	*/
    function onoperator_add()
	{
		$backReturn = array();
		// 是否有操作员删除权限operatorRemove
		$hasAddOperatorPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "operatorAdd");
		
		if ( $hasAddOperatorPrivilege['return'] ) 
		{
				if(isset($this->post['submit_add']))
				{
				$id = !empty($this->post['id'])?$this->post['id']:0;
				if($id)
				{
					$operator_atr = $_ENV['operator']->get($id);
				}
				$login_name = isset($this->post['login_name'])?trim($this->post['login_name']):'';
				//$name = isset($this->post['name'])?trim($this->post['name']):'';
				//$cno = isset($this->post['cno'])?trim($this->post['cno']):'';
				$department = $this->post['department'] != -1?intval($this->post['department']):0;
				$post = $this->post['post'] != -1?intval($this->post['post']):0;
				$job = $this->post['job'] != -1?intval($this->post['job']):0;
				$Vadmin = $this->post['Vadmin'] != ""?($this->post['Vadmin']):"";
				$_ENV['operator']->add($login_name,$department,$post,$job,$Vadmin,$id);
										
			}
			if($id)
			{
				$operator_atr1 = $_ENV['operator']->get($id);
				if(!empty($operator_atr) && !empty($operator_atr1))
				{
					if($operator_atr['did'] != $operator_atr1['did'])
					{
						if($operator_atr['did'] == 0)
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."department SET num=num+1 WHERE id=".$operator_atr1['did']);
						}
						elseif($operator_atr1['did'] == 0)
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."department SET num=num-1 WHERE id=".$operator_atr['did']);
						}
						else
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."department SET num=num-1 WHERE id=".$operator_atr['did']);
							$this->db->query("UPDATE ".DB_TABLEPRE."department SET num=num+1 WHERE id=".$operator_atr1['did']);
						}
					}
					
					if($operator_atr['pid'] != $operator_atr1['pid'])
					{
						if($operator_atr['pid'] == 0)
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."post SET num=num+1 WHERE id=".$operator_atr1['pid']);
						}
						elseif($operator_atr1['pid'] == 0){
							$this->db->query("UPDATE ".DB_TABLEPRE."post SET num=num-1 WHERE id=".$operator_atr['pid']);
						}
						else
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."post SET num=num-1 WHERE id=".$operator_atr['pid']);
							$this->db->query("UPDATE ".DB_TABLEPRE."post SET num=num+1 WHERE id=".$operator_atr1['pid']);
						}
					}
					
					if($operator_atr['jid'] != $operator_atr1['jid'])
					{
						if($operator_atr['jid'] == 0)
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."job SET num=num+1 WHERE id=".$operator_atr1['jid']);
						}
						elseif($operator_atr1['jid'] == 0)
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."job SET num=num-1 WHERE id=".$operator_atr['jid']);
						}
						else
						{
							$this->db->query("UPDATE ".DB_TABLEPRE."job SET num=num-1 WHERE id=".$operator_atr['jid']);
							$this->db->query("UPDATE ".DB_TABLEPRE."job SET num=num+1 WHERE id=".$operator_atr1['jid']);
						}
					}  			
					$this->onoperator("操作员修改成功！");
				}
			}
			else
			{
				$this->post['department'] != -1 && $this->db->query("UPDATE ".DB_TABLEPRE."department SET num=num+1 WHERE id=".$this->post['department']);
				$this->post['post'] != -1 && $this->db->query("UPDATE ".DB_TABLEPRE."post SET num=num+1 WHERE id=".$this->post['post']);
				$this->post['job'] != -1 && $this->db->query("UPDATE ".DB_TABLEPRE."job SET num=num+1 WHERE id=".$this->post['job']);
				$this->onoperator("操作员添加成功！"); 
			}   	        
		}
		else
		{
			$hasAddOperatorPrivilege['url'] = "?admin_system/operator";
			__msg($hasAddOperatorPrivilege);
		
		}
		
    	
    }
      
	/* 
		intoDepartment:进入部门设置页面|
		departmentModify:修改部门|
		departmentAdd:添加部门|
		departmentRemove:删除部门|
		departmentUpdatePrivilege:部门权限配置
		部门显示页面
		进入部门设置页面权限：intoDepartment
	*/
    function ondepartment($msg='', $ty='')
	{
		$hasIntoDepartmentPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoDepartment");
		
		if ($hasIntoDepartmentPrivilege['return'])
		{
			$departmentlist = $_ENV['department']->get_department_list();
			$msg && $message = $msg;
			$ty && $type = $ty;
			include template('department','admin');
		}
		else
		{
			$hasIntoDepartmentPrivilege['url'] = "?admin_main";
			__msg($hasIntoDepartmentPrivilege);
		
		}
    	
    }

	/* 
		增加部门
		增加部门权限：departmentAdd
	*/ 
    function ondepartment_add()
	{
		$hasDepartmentAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "departmentAdd");
		
		if ( $hasDepartmentAddPrivilege['return'] )
		{
			$pid   = isset($this->post['did'])  ? intval($this->post['did']) : 0;
			$grade = isset($this->post['grade']) ? intval($this->post['grade'])+1 : 1;
			$name  = isset($this->post['name']) ? trim($this->post['name'])  : '';
			if ($name == '')
			{
				$this->ondepartment("部门名不能为空","errormsg");
			} 
			else 
			{
				$_ENV['department']->add($name,$pid,$grade);
				$msg = $grade."级部门添加成功";
				$this->ondepartment($msg);
			}
		}
		else
		{
			$hasDepartmentAddPrivilege['url'] = "?admin_system/department";
			__msg($hasDepartmentAddPrivilege);
		
		}
    	
    }
    
    /*
		修改部门
		修改部门权限：departmentModify
	*/
    function ondepartment_modify() 
	{
		$hasDepartmentModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "departmentModify");
		if ( $hasDepartmentModifyPrivilege['return'] )
		{
			if(isset($this->post['did']))
			{
				$name  = isset($this->post['name']) ? trim($this->post['name'])  : '';
				if ($name == '')
				{
					$this->ondepartment("部门名不能为空","errormsg");
				}
				else
				{
					$_ENV['department']->set($this->post['did'],$name);
					$this->ondepartment("修改部门成功");
				}    		
			}
		}
		else
		{
		
			$hasDepartmentModifyPrivilege['url'] = "?admin_system/department";
			__msg($hasDepartmentModifyPrivilege);
		}
    	
    }
     
   		/*判断是否有子部门*/ 
    function onajax_department_did()
	{
    	
    	if(isset($this->post['did'])){
    		
    		 $_ENV['department']->get_did($this->post['did']);
    	}
    }
	/* 
		删除部门 
		删除部门权限：departmentRemove
	*/
    function ondepartment_remove()
	{
    	
		$hasDepartmentRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "departmentRemove");
		
		if ( $hasDepartmentRemovePrivilege['return'] )
		{
			$id = intval($this->post['did']);
			if(!empty($id)) 
			{
				$_ENV['department']->remove($id);
				$this->ondepartment("部门删除成功");
			} 
			else
			{
				$this->ondepartment("部门删除失败","errormsg");
			}
		}
		else
		{
			$hasDepartmentRemovePrivilege['url'] = "?admin_system/department";
			__msg($hasDepartmentRemovePrivilege);
		
		}
    	
    }
	
	// 配置部门
	function ondepartment_config($msg='', $ty='')
	{
		$msg && $message = $msg;
		$ty && $type = $ty;
		
		$departmentId = intval($this->get[2]);
		// 根据部门id获取该记录信息
		$deparmenInfo = $_ENV['department'] -> getDeparmenInfo($departmentId);
	
		//判断该部门是否存在
		if($deparmenInfo['id'])
		{
			$departmentName = $deparmenInfo['name'];
			// 根据部门id获取部门的权限
			$permission = $_ENV['menu'] -> getPermissionByDepartment($departmentId);
			if(empty($permission))
			{
				$this->ondepartment("部门不存在",'errormsg');
				header("Location:?admin_system/department");
			}

		}
		else
		{
			$this->ondepartment("部门不存在",'errormsg');
			header("Location:?admin_system/department");
		}
		include template('departmentPriConfig','admin');
	}
	
	// 从部门设置更新部门权限:departmentUpdatePrivilege
	function ondepartment_updatePrivilege()
	{
		$hasDepartmentUpdatePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "departmentUpdatePrivilege");
		
		if ( $hasDepartmentUpdatePrivilege['return'] )
		{
			// 获取部门id
			$departmentId = isset($this->post['departmentId']) ? intval($this->post['departmentId']) : 0;
			$PermissionDetailList = isset($this->post['PermissionDetailList']) ? $this->post['PermissionDetailList'] :false;
			$returnBack = array();
			
			$departmentInfo = $_ENV['department'] -> getDeparmenInfo($departmentId);
			
			if ($departmentInfo['id'])
			{
				$departmentName = $departmentInfo['name'];
				$result = $_ENV['menu'] -> updatePermissionByDepartment($departmentId,$PermissionDetailList);
				if($result)
				{
					$returnBack = array('comment'=>"修改成功",'type'=>'correctmsg');
				}
				else 
				{
					$returnBack = array('comment'=>"修改出错，请重新修改！",'type'=>'errormsg');
				}
			}
			else
			{
				$returnBack = array('comment'=>"没找到改部门",'type'=>'errormsg'); 
			} 

			$this->ondepartment($returnBack['comment'],$returnBack['type']);	
		}
		else
		{
			$hasDepartmentUpdatePrivilege['url'] = "?admin_system/department";
			__msg($hasDepartmentUpdatePrivilege);
		}
	}
	
    /* 
		intoPost:进入职位设置页面
		postAdd:添加职位
		postModify:修改职位
		postRemove:删除职位
		显示职位页面
	*/
    function onpost($msg='', $ty='')
	{
	   $hasIntoPostPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoPost");
		
	   if ( $hasIntoPostPrivilege['return'] )
	   {
		   $postlist = $_ENV['post']->get();
		   $msg && $message = $msg;
		   $ty && $type = $ty; 
		   include template('post', 'admin');
	   }
	   else
	   {
			$hasIntoPostPrivilege['url'] = "?admin_main";
			__msg($hasIntoPostPrivilege);
	   
	   }

    }
    /* 
		添加职位
		添加职位权限：postAdd
	*/
    function onpost_add() 
    {
	   $hasPostAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "postAdd");
		if ( $hasPostAddPrivilege['return'] )
		{
			$PostInfo['name']  = isset($this->post['name']) ? trim($this->post['name'])  : '';
			$PostInfo['question_limit']  = intval($this->post['question_limit']) ? intval($this->post['question_limit'])  : 0;
			$PostInfo['question_limit_add']  = intval($this->post['question_limit_add']) ? intval($this->post['question_limit_add'])  : 0;
			$PostInfo['timeout']  = intval($this->post['timeout']) ? intval($this->post['timeout'])  : 0;
			if ($PostInfo['name'] == '')
			{
				$this->onpost("职位名称不能为空","errormsg");
			}
			elseif($PostInfo['question_limit'] < 0)
			{
				$this->onpost("咨询分单上限不能小于0","errormsg");
			}
			elseif($PostInfo['question_limit_add'] < 0)
			{
				$this->onpost("咨询追问分单上限不能小于0","errormsg");
			}
			elseif($PostInfo['timeout'] < 0)
			{
				$this->onpost("超时时间不能小于0","errormsg");
			}			
			else 
			{                                                      
				$insert = $_ENV['post']->insertPost($PostInfo);
				if($insert)
				{
					$this->onpost("职位添加成功");
				}
				else
				{
					$this->onpost("职位添加失败");
				}
			}       
		}
		else
		{
			$hasPostAddPrivilege['url'] = "?admin_system/post";
			__msg($hasPostAddPrivilege);
		}
    	
    }
    /* 
		修改职位
		修改职位权限：postModify
	*/
    function onpost_modify() 
	{
	   $backReturn = array();
	   $hasPostModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "postModify");
		if ( $hasPostModifyPrivilege['return'] )
		{
			$id = intval($this->post['pid']);
			if($id>0)
			{
				$PostInfo['name']  = isset($this->post['name']) ? trim($this->post['name'])  : '';
				$PostInfo['question_limit']  = intval($this->post['question_limit']) ? intval($this->post['question_limit'])  : 0;
				$PostInfo['question_limit_add']  = intval($this->post['question_limit_add']) ? intval($this->post['question_limit_add'])  : 0;
				$PostInfo['timeout']  = intval($this->post['timeout']) ? intval($this->post['timeout'])  : 0;
				if ($PostInfo['name'] == '')
				{
					$this->onpost("职位名称不能为空","errormsg");
				}
				elseif($PostInfo['question_limit'] < 0)
				{
					$this->onpost("咨询分单上限不能小于0","errormsg");
				}
				elseif($PostInfo['question_limit_add'] < 0)
				{
					$this->onpost("咨询追问分单上限不能小于0","errormsg");
				}
				elseif($PostInfo['timeout'] < 0)
				{
					$this->onpost("超时时间不能小于0","errormsg");
				}
				else 
				{                                                      
					$update = $_ENV['post']->updatePost($id,$PostInfo);
					if($update)
					{
						$this->onpost("职位更新成功");
					}
					else
					{
						$this->onpost("职位更新失败");
					}
				} 
			}
			else
			{
				$this->onpost("职位ID必须大于0");
			} 
		}		
		else
		{
			$hasPostModifyPrivilege['url'] = "?admin_system/post";
			__msg($hasPostModifyPrivilege);
		}
    }
    /* 
		删除职位
		删除职位权限：postRemove
	*/
    function onpost_remove() 
	{
	   $hasPostRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "postRemove");
		if ( $hasPostRemovePrivilege['return'] )
		{
			if (isset($this->post['pid']))
			{
				$_ENV['post']->remove($this->post['pid']);
			}
			$this->onpost("职位删除成功");
		}
		else
		{
			$hasPostRemovePrivilege['url'] = "?admin_system/post";
			__msg($hasPostRemovePrivilege);
		}
    }
    
	/* 
		intoJob:进入岗位设置页面
		jobAdd:添加岗位
		jobModify:修改岗位
		jobRemove:删除岗位
		显示岗位方法
		进入岗位设置页面权限：intoJob
		
	*/
    function onjob($msg='', $ty='')
	{
	   $hasIntoJobPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoJob");
		if ( $hasIntoJobPrivilege['return'] )
		{
			$joblist = $_ENV['job']->get();
			$msg && $message = $msg;
			$ty && $type = $ty;
			include template('job', 'admin');
		}
		else
		{
			$hasIntoJobPrivilege['url'] = "?admin_main";
			__msg($hasIntoJobPrivilege);
		
		}
    }
    /**
     *  添加一个岗位
	 *  添加岗位权限：jobAdd
     */
    function onjob_add()
	{
	    $hasJobAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "jobAdd");
		if ( $hasJobAddPrivilege['return'] )
		{
			$name  = isset($this->post['name']) ? trim($this->post['name'])  : '';
			if ($name == '')
			{
				$this->onjob("岗位名称不能为空","errormsg");
			}
			else
			{
				$_ENV['job']->add($name);
				$this->onjob("岗位添加成功");
			}
		}
		else
		{
			$hasJobAddPrivilege['url'] = "?admin_system/job";
			__msg($hasJobAddPrivilege);
		
		}
    }
    /**
     *  修改岗位
	 *  修改岗位权限：jobModify
     */
    function onjob_modify()
	{
		$backReturn = array();
	    $hasJobModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "jobModify");
		if ( $hasJobModifyPrivilege['return'] )
		{
			if(isset($this->post['pid']))
			{
				$name  = isset($this->post['name']) ? trim($this->post['name'])  : '';
				if ($name == '')
				{
					$this->onjob("岗位名称不能为空","errormsg");
				}
				else
				{
					$_ENV['job']->set($this->post['pid'],$name);
					$this->onjob("岗位添加成功");
				}
			}
		}
		else
		{
			$hasJobModifyPrivilege['url'] = "?admin_system/job";
			__msg($hasJobModifyPrivilege);
		}
    }
    /**
     * 删除岗位
	 * 删除岗位权限：jobRemove
     */
    function onjob_remove() 
	{
	    $hasJobRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "jobRemove");
		if ( $hasJobRemovePrivilege['return'] )
		{
		   $jobId = isset($this->post['pid']) ? intval($this->post['pid']) : 0;
		   if ($jobId)
		   {
				$_ENV['job']->remove($this->post['pid']);
				$this->onjob("岗位删除成功");
		   }
		   else
		   {
				$this->onjob("岗位不存在");
		   }
		}
		else
		{
			$hasJobRemovePrivilege['url'] = "?admin_system/job";
			__msg($hasJobRemovePrivilege);
		
		}
    	
    }
  
    /**
		intoHawb:进入分单管理页面
		hawbConfig:分单管理配置
		
     * 分单管理显示页面
     *   $msg 页面显示的消息
     *   $ty 消息样式
     *   $goto 分单管理协助时间管理配置跳转
	 进入分单管理页面权限：intoHawb
     */
    function onhawb($msg='', $ty='', $goto)
	{
	   $hasIntoHawbPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoHawb");
	   if( $hasIntoHawbPrivilege['return'] )
	   {
		   (isset($this->get[8])) && $submit_flag = true;
		   if(isset($this->post['submit_search']) || isset($submit_flag) || $goto)
		   {
				$login_name_search = isset($this->post['user_name']) && $this->post['user_name']!=''?trim($this->post['user_name']):(isset($this->get[2])?urldecode($this->get[2]):'');
				$job_search = isset($this->post['job']) && $this->post['job']!=-1?intval($this->post['job']):((isset($this->get[3]) && $this->get[3] != -1)?intval($this->get[3]):-1);
				$busy_search = isset($this->post['busy']) && $this->post['busy']!=-1?intval($this->post['busy']):((isset($this->get[4]) && $this->get[4] != -1)?intval($this->get[4]):-1);
				$handle_search = isset($this->post['handle']) && $this->post['handle']!=-1?intval($this->post['handle']):((isset($this->get[5]) && $this->get[5] != -1)?intval($this->get[5]):-1);
				$hawb_search = isset($this->post['hawb']) && $this->post['hawb']!=-1?intval($this->post['hawb']):((isset($this->get[6]) && $this->get[6] != -1)?intval($this->get[6]):-1);
				$isonjob_search = isset($this->post['is_onjob']) && $this->post['is_onjob']!=-1?intval($this->post['is_onjob']):((isset($this->get[7]) && $this->get[7] != -1)?intval($this->get[7]):-1);
				$where_search1 = $_ENV['operator']->getHawbWhere($login_name_search, $job_search,$busy_search,$handle_search,$hawb_search,$isonjob_search,true);
				$where_search2 = $_ENV['operator']->getHawbWhere($login_name_search, $job_search,$busy_search,$handle_search,$hawb_search,$isonjob_search,false); 
					 
				@$page = max(1, intval($this->get[8]));   	
				$pagesize = $this->setting['list_default'];
				$startindex = ($page - 1) * $pagesize;  
				$rownum = $_ENV['operator']->getNum($where_search2); 
				$operator_list = $_ENV['operator']->getList($startindex, $pagesize,$where_search1);
				foreach($operator_list as $key => $value)
				{
					if($hawb_search!=-1)
					{
						$t_list = explode(',',$value['type']);
						if(!in_array($hawb_search,$t_list))
						{
							unset($operator_list[$key]);
							continue;
						}
					}

					$name_list = array();
					$type_list = explode(',',$value['type']);
					foreach($type_list as $k => $cid)
					{
						$cid_info = $_ENV['category']->get($cid);
						if($cid_info['id'])
						{
							$cid_list[$cid_info['id']] = $cid_info;
						}
						$name_list[$cid_info['id']] = $cid_list[$cid_info['id']]['name'];
					}
					$t = explode(',',$value['detail_type']);
					if(in_array(-1,$t))
					{
						$name_list[-1] = "其他";	
					}
					$operator_list[$key]['type_name'] = implode('|',$name_list);
				}
					
				
				$departstr = page($rownum, $pagesize, $page, "admin_system/hawb/$login_name_search/$job_search/$busy_search/$handle_search/$hawb_search/$isonjob_search");
		   }   
		   $ty && $type = $ty;
		   $msg && $message = $msg;
		   
		   $busy_option = $this->ask_config->getBusy();
		   $handle_option = $this->ask_config->getHandle();
			$ask_type = $_ENV['category']->getTypeDB(1);
			$suggest_type = $_ENV['category']->getTypeDB(2);
			
			$hawb_option = array($ask_type=>'咨询',$suggest_type=>'建议');
		   //$hawb_option = $this->ask_config->getHawb();	
		   $job_option = $_ENV['job']->getOptions();
		   $is_onjob = $this->ask_config->isonjob();
		   $check_category = $_ENV['category']->check_category_list();
		   include template('hawb','admin');
			
	     }
		 else
		 {
			$hasIntoHawbPrivilege['url'] = "?admin_main";
			__msg($hasIntoHawbPrivilege);
		 
		 }
       

    }
    
    //分单管理配置
	// 分单管理配置权限：hawbConfig
    function onhawb_config() 
	{
	$hasHawbConfigPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "hawbConfig");
	if( $hasHawbConfigPrivilege['return'] )
	{
		if(isset($this->post['id']))
		{     	   
			if(isset($this->post['checks']) && $this->post['checks'] != null)
			{
				$detail_type = implode(',',$this->post['checks']);
			}
			$isbusy = isset($this->post['isbusy'])?$this->post['isbusy']:0;
			$ishandle = isset($this->post['ishandle'])?$this->post['ishandle']:0;
			//$istype = isset($this->post['type'])?$this->post['type']:0;
			$isonjob = isset($this->post['isonjob'])?$this->post['isonjob']:0;
			$user = isset($this->post['user'])?$this->post['user']:'';
					 
			$operator_info = $_ENV['operator']->get($this->post['id']);//查询当前配置的人的在线状态
			if(!empty($operator_info))
			{
				if($operator_info['ishandle'] == 1)
				{//只相对处理人员进行处理
					if($operator_info['isonjob'] == 0)
					{//不在班时处理
						if($isonjob == 1)
						{
							$onjob_start = time();//记录点在班状态为是的时间
							if($isbusy == 1)
							{
								$busy_start = $onjob_start;//记录点忙碌状态为是的时间
							}
							$istoday = $_ENV['worktime']->isToday($user,date('Y-m-d'));
							if(false === $istoday)
							{
								$insertArr = array('login_name'=>$user,'login_time'=>date('Y-m-d'),'busy_start'=>$onjob_start,'onjob_start'=>$onjob_start);
								$insert = $_ENV['worktime']->insertWorkTime($insertArr);
							}
							else
							{
								if(isset($busy_start))
								{
									$updateArr = array('busy_start'=>$busy_start,'onjob_start'=>$onjob_start);
									$update = $_ENV['worktime']->updateWorkTime($istoday['id'],$updateArr);
								}
								else
								{
									$updateArr = array('onjob_start'=>$onjob_start);
									$update = $_ENV['worktime']->updateWorkTime($istoday['id'],$updateArr);
								}
							}
						}
					}
					else
					{//在班处理
						if($isonjob == 0)
						{
							$onjob_end = time();//记录点在班状态为否的时间
							$istoday = $_ENV['worktime']->isToday($user,date('Y-m-d'));
							if(false === $istoday)
							{//点在班与不在班不是同一天
								$lasttoday = $_ENV['worktime']->lastToday($user);
								if(false !== $lasttoday)
								{
									$last_job_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['onjob_start'];//统计前一天在班的在班时间
									$job_time = $onjob_end - strtotime(date('Y-m-d'));//统计当天在班的在班时间
									$last_busy_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['busy_start'];//统计前一天在班的忙碌时间
									$busy_time = $onjob_end - strtotime(date('Y-m-d'));//统计当天在班的忙碌时间
									if($operator_info['isbusy'] == 1)
									{//在班选否时是否是忙碌状态，是的话，则统计忙碌时间
										$updateArr = array('busy_time'=>$lasttoday['busy_time']+$last_busy_time,'onjob_time'=>$lasttoday['onjob_time']+$last_job_time,'onjob_start'=>strtotime(date('Y-m-d')),'busy_start'=>strtotime(date('Y-m-d')));
										$update = $_ENV['worktime']->updateWorkTime($lasttoday['id'],$updateArr);//更新前一天在班的忙碌时间以及在班时间
										$insertArr = array('login_name'=>$user,'login_time'=>date('Y-m-d'),'busy_time'=>$lasttoday['busy_time']+$busy_time,'onjob_time'=>$lasttoday['onjob_time']+$job_time,'onjob_start'=>$onjob_end,'busy_start'=>$onjob_end);
										$insert = $_ENV['worktime']->insertWorkTime($insertArr);
									}
									else
									{
										$updateArr = array('onjob_time'=>$lasttoday['onjob_time']+$last_job_time,'onjob_start'=>$onjob_end);
										$update = $_ENV['worktime']->updateWorkTime($lasttoday['id'],$updateArr);//更新前一天在班的忙碌时间以及在班时间
										$insertArr = array('login_name'=>$user,'login_name'=>date('Y-m-d'),'onjob_time'=>$job_time,'onjob_start'=>$onjob_end,'busy_start'=>$onjob_end);
										$insert = $_ENV['worktime']->insertWorkTime($insertArr);//插入当天在班时间，并统计当天的忙碌时间以及在班时间
									}
								}
							}
							else
							{//同一天处理
								$job_time = $onjob_end - $istoday['onjob_start'];
								if($operator_info['isbusy'] == 1)
								{//在班选否时是否是忙碌状态，是的话，则统计忙碌时间
									$busy_time = $onjob_end - $istoday['busy_start'];
								}
								if(isset($busy_time))
								{
									$updateArr = array('busy_time'=>$istoday['busy_time']+$busy_time,'onjob_time'=>$istoday['onjob_time']+$job_time,'onjob_start'=>$onjob_end,'busy_start'=>$onjob_end);
									$update = $_ENV['worktime']->updateWorkTime($istoday['id'],$updateArr);
								}
								else
								{
									$updateArr = array('onjob_time'=>$istoday['onjob_time']+$job_time,'onjob_start'=>$onjob_end);
									$update = $_ENV['worktime']->updateWorkTime($istoday['id'],$updateArr);
								}
							}
						}
						else
						{//选择为在班时处理
							if($operator_info['isbusy'] == 0)
							{//数据库里读为空闲状态
								if($isbusy == 1)
								{
									$_ENV['worktime']->worktimeModify($user,1,1);
								}
							}
							else
							{//数据库里读为忙碌状态
								if($isbusy == 0)
								{
									$_ENV['worktime']->worktimeModify($user,1,0);
								}
							}
						}
					}
				} 
			}
			$type_list = explode(',',$detail_type);
			foreach($type_list as $key => $value)
			{
				$type_detail = $_ENV['category']->get($value);
				if($type_detail['id'])
				{
					$question_type_list[$type_detail['pid']] = $type_detail['pid'];
				}
			}
			$istype = implode(',',$question_type_list);
			$_ENV['operator']->hawbUpdate($this->post['id'],$isbusy,$ishandle,$istype,$detail_type,$isonjob);
			$this->onhawb("配置成功！",'correctmsg',true);
		}
		else
		{
			$this->onhawb("非法操作!","errormsg");
		} 

	}
	else
	{
		$hasHawbConfigPrivilege['url'] = "?admin_system/hawb";
		__msg($hasHawbConfigPrivilege);
	}
    }
	/* 
		intoHelp:进入协助处理管理页面
		helpConfig:接单类型配置
		helpManageTime:协助处理时间配置
		协助处理管理页面权限：intoHelp
	*/
    function onhelp($msg='', $ty='')
	{
	   $hasIntoHelpPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoHelp");
	   if( $hasIntoHelpPrivilege['return'] )
	   {
			$login_name_search = isset($this->post['user_name']) && $this->post['user_name']!=''?trim($this->post['user_name']):(isset($this->get[2])?urldecode($this->get[2]):'');
			$job_search = isset($this->post['job']) && $this->post['job']!=-1?intval($this->post['job']):((isset($this->get[3]) && $this->get[3] != -1)?intval($this->get[3]):-1);
			$busy_search = isset($this->post['busy']) && $this->post['busy']!=-1?intval($this->post['busy']):((isset($this->get[4]) && $this->get[4] != -1)?intval($this->get[4]):-1);
			$handle_search = isset($this->post['handle']) && $this->post['handle']!=-1?intval($this->post['handle']):((isset($this->get[5]) && $this->get[5] != -1)?intval($this->get[5]):-1);
			$hawb_search = isset($this->post['hawb']) && $this->post['hawb']!=-1?intval($this->post['hawb']):((isset($this->get[6]) && $this->get[6] != -1)?intval($this->get[6]):-1);
			 
			$where_search1 = $_ENV['operator']->getHawbWhere($login_name_search, $job_search,$busy_search,$handle_search,$hawb_search,true);
			$where_search2 = $_ENV['operator']->getHawbWhere($login_name_search, $job_search,$busy_search,$handle_search,$hawb_search,false);

			@$page = max(1, intval($this->get[7]));
			$pagesize = $this->setting['list_default'];
			$startindex = ($page - 1) * $pagesize;
			$rownum = $_ENV['operator']->getNum($where_search2);
			$operator_list = $_ENV['operator']->getList($startindex, $pagesize,$where_search1);
			$departstr = page($rownum, $pagesize, $page, "admin_system/help/$login_name_search/$job_search/$busy_search/$handle_search/$hawb_search");
			
			$ty && $type = $ty;
			$msg && $message = $msg;
			 
			$busy_option = $this->ask_config->getBusy();
			$handle_option = $this->ask_config->getHandle();
			$hawb_option = $this->ask_config->getHawb();
			
			$job_option = $_ENV['job']->getOptions();
			$hekp_config = $_ENV['hekp_config']->getAll();
			$input_category = $_ENV['category']->input_category_list($hekp_config);
			include template('syshelp','admin');
		}
		else
		{
			$hasIntoHelpPrivilege['url'] = "?admin_main";
			__msg($hasIntoHelpPrivilege);
		}
    }
    //协助处理管理->接单类型配置
	// 配置接单类型权限：helpConfig
    function onhelp_config() 
	{
	   $hasHelpConfigPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpConfig");
	   if( $hasHelpConfigPrivilege['return'] )
	   {
			if(isset($this->post['id']))
			{
				if(isset($this->post['checks']) && $this->post['checks'] != null)
				{
					$detail_type = implode(',',$this->post['checks']);
				}
				$ishelp = isset($this->post['ishelp'])?$this->post['ishelp']:0;
				$istype = isset($this->post['type'])?$this->post['type']:0;
				$_ENV['operator']->helpUpdate($this->post['id'],$ishelp,$istype,$detail_type);
				$this->onhelp("配置成功！");
			}
			else
			{
				$this->onhelp("非法操作","errormsg");
			}
	   }
	   else
	   {
			$hasHelpConfigPrivilege['url'] = "?admin_system/help";
			__msg($hasHelpConfigPrivilege);
	   }
    	
    }
    // 协助处理时间配置
	// 协助处理时间配置权限：helpManageTime
    function onhelp_manage_time()
	{
	   $hasHelpManageTimePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpManageTime");
	   if( $hasHelpManageTimePrivilege['return'] )
	   {
			if($this->post != null)
			{
				$data = $this->post;
				array_shift($data);
				$_ENV['hekp_config']->hekpUpdate($data);
				$this->onhelp('配置成功');
			}
			else
			{
				$this->onhelp('配置出错，没有参数','errormsg');
			}
		}
		else
		{
			$hasHelpManageTimePrivilege['url'] = "?admin_system/help";
			__msg($hasHelpManageTimePrivilege);
		}
    }
	
    // 根据用户id 查找用户接单详细类型
    function onajax_help_detail_type(){
    	if(isset($this->post['id'])){
    		 $detail_type = $_ENV['operator']->get_detail_type($this->post['id']);
    		 $check_category = $_ENV['category']->check_category_list($detail_type);
    		 echo $check_category;
   		 }
    }

}
?>
