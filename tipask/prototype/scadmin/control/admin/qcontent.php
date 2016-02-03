<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_qcontentcontrol extends base {

    function admin_qcontentcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("qcontent");
		$this->load("menu");
    }

    function ondefault($message='')
	{
       $this->onqcontent();
    }
	/* 
		主分类显示页面
		进入主分类页面权限：intoqcontent
	*/
    function onqcontent($msg='', $ty='') 
	{
		$hasIntoQcontentPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoQcontent");
		// 是否有进入操作员管理页面权限
		if ( $hasIntoQcontentPrivilege['return'] )
		{
			if(isset($this->get[2]))
			{
				$Qcontent_info = $_ENV['qcontent']->getQcontent(intval($this->get[2]));
			}			
			$Qcontent_list = $_ENV['qcontent']->GetAllQcontent(0,0);
			foreach($Qcontent_list as $QcontentId=>$QcontentInfo)
			{
				if($QcontentInfo['pid']>0)
				{
					$Qcontent_list[$QcontentId]['parentName']=$Qcontent_list[$QcontentInfo['pid']]['content'];
				}
				else
				{
					$Qcontent_list[$QcontentId]['parentName']="无分类";
				}
			}
			$msg && $message = $msg;
			$ty && $type = $ty;
			include template('qcontent','admin');
		}
		else 
		{
			$hasIntoQcontentPrivilege['url'] = "?admin_main";
			__msg($hasIntoQcontentPrivilege);
		}
        
    }
    
    /* 
	操作员添加
	添加操作员权限：operatorAdd
	*/
    function onQcontent_add()
	{
		$backReturn = array();
		// 是否有主分类修改/添加权限updateQcontent
		$hasAddQcontentPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "updateQcontent");
		if ( $hasAddQcontentPrivilege['return'] ) 
		{
			if(isset($this->post['submit_add']))
			{
				$id = !empty($this->post['id'])?$this->post['id']:0;
				if($id)
				{
					$QcontentInfo = $_ENV['qcontent']->GetQcontent($id);
					$QcontentInfo['content'] = trim($this->post['content']);
					$Qcontent_list = $_ENV['qcontent']->GetAllQcontent(0,0);
					foreach($Qcontent_list as $QcontentId=>$Qcontent_Info)
					{
						if($QcontentInfo['content'] == $Qcontent_Info['content'] && $id != $Qcontent_Info['id'])
						{
							$exist = 1;
							break;
						}
					}
					if($exist==1)
					{
						$this->onQcontent("已有重名的分类，更新失败！");	
					}
					else
					{
						$QcontentInfo['pid'] = intval($this->post['pid']);
						$QcontentInfo['displayOrder'] = intval($this->post['displayOrder']);
						unset($QcontentInfo['id']);
						$update = $_ENV['qcontent']->updateQcontent($id,$QcontentInfo);
						$this->onQcontent("快速回复修改成功！");					
					}

				}
				else
				{
					$QcontentInfo['content'] = trim($this->post['content']);
					$Qcontent_list = $_ENV['qcontent']->GetAllQcontent(0,0);
					foreach($Qcontent_list as $QcontentId=>$Qcontent_Info)
					{
						if($QcontentInfo['content'] == $Qcontent_Info['content'])
						{
							$exist = 1;
							break;
						}					
					}
					if($exist==1)
					{
						$this->onQcontent("已有重名的分类，添加失败！");	
					}
					else
					{
						$QcontentInfo['pid'] = intval($this->post['pid']);
						//$QcontentInfo['displayOrder'] = intval($this->post['displayOrder']);
						$QcontentInfo['displayOrder'] = count($Qcontent_list)+1;

						$insert = $_ENV['qcontent']->insertQcontent($QcontentInfo);
						$this->onQcontent("快速回复添加成功！");
					}

				}
			}
		}
		else
		{
			$hasAddQcontentPrivilege['url'] = "?admin_qcontent/qcontent";
			__msg($hasAddQcontentPrivilege);
		}
    }
    /* 
	快速回复删除
	删除快速回复：deleteQcontent
	*/
    function onQcontent_del()
	{
		$backReturn = array();
		// 是否有快速回复删除权限deleteQcontent
		$hasDelQcontentPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "deleteQcontent");
		if ( $hasDelQcontentPrivilege['return'] ) 
		{
			$id = !empty($this->get[2])?$this->get[2]:0;
			if($id)
			{
				$SubList = $_ENV['qcontent']->GetSubList($id);
				if(count($SubList)==0)
				{
					$delete = $_ENV['qcontent']->deleteQcontent($id);
					if($delete)
					{
						$this->onQcontent("快速回复删除成功！");
					}
					else
					{
						$this->onQcontent("快速回复删除失败！");
					}
				}
				else
				{
					$this->onQcontent("此分类下有数据，请移除后再删除！");	
				}
			}
		}
		else
		{
			$hasDelQcontentPrivilege['url'] = "?admin_qcontent/qcontent";
			__msg($hasDelQcontentPrivilege);
		}
    }
    function onReplace()
	{
		$id = $this->post['id'];
		$act = $this->post['act'];
		if($id)
		{
			$Qcontent_info = $_ENV['qcontent']->getQcontent(intval($id));
			$Qcontent_list = $_ENV['qcontent']->GetAllQcontent(0,0);
			$i = 0;
			if($Qcontent_info['pid']==0)
			{
				foreach($Qcontent_list as $Key => $value)
				{
					if($value['pid'] == 0)
					{
						$List[$i] = $value;
						if($value['id'] == $Qcontent_info['id'])
						{
							$T = $i;
						}
						$i++;
					}
				}
			}
			else
			{
				foreach($Qcontent_list as $Key => $value)
				{
					if($value['pid'] == $Qcontent_info['pid'])
					{
						$List[$i] = $value;
						if($value['id'] == $Qcontent_info['id'])
						{
							$T = $i;
						}
						$i++;
					}
				}
			}
			if($act == "up")
			{
				if($T==0)
				{
					$returnArr = array('return'=>1,'comment'=>"上移成功");
				}
				else
				{
					$Replace = $_ENV['qcontent']->Replace($List[$T],$List[$T-1]);
					if($Replace)
					{
						$returnArr = array('return'=>1,'comment'=>"上移成功");	
					}
					else
					{
						$returnArr = array('return'=>0,'comment'=>"上移失败");	
					}
				}
			}
			elseif($act == "down")
			{
				if($T==count($List)-1)
				{
					$returnArr = array('return'=>1,'comment'=>"下移成功");	
				}
				else
				{
					$Replace = $_ENV['qcontent']->Replace($List[$T],$List[$T+1]);
					if($Replace)
					{
						$returnArr = array('return'=>1,'comment'=>"下移成功");	
					}
					else
					{
						$returnArr = array('return'=>0,'comment'=>"下移失败");	
					}
				}
			}
		}
		else
		{
			$returnArr = array('return'=>0,'comment'=>"移动失败");	
		}
		exit(json_encode($returnArr));
    }
}
?>
