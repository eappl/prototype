<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_quickcontrol extends base {

    function admin_quickcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("quick");
		$this->load("menu");
    }

    function ondefault($message='')
	{
       $this->onquick();
    }
	
	/* 
		主分类显示页面
		进入主分类页面权限：intoQtype
	*/
    function onquick($msg='', $ty='') 
	{
		$hasIntoQuickLinkPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoQuick");
		// 是否有进入操作员管理页面权限
		if ( $hasIntoQuickLinkPrivilege['return'] )
		{
			if(isset($this->get[2]))
			{
				$quicklink_info = $_ENV['quick']->GetQuicklink(intval($this->get[2]));	
			}
			$quicklink_list = $_ENV['quick']->GetAllQuicklink(0);
			foreach($quicklink_list as $qucklinkId => $qucklinkInfo)
			{
				if($qucklinkInfo['Parent']>0)
				{
					$quicklink_list[$qucklinkId]['parentName']=$quicklink_list[$qucklinkInfo['Parent']]['LinkName'];
				}
				else
				{
					$quicklink_list[$qucklinkId]['parentName']="无分类";
				}
			}
			$msg && $message = $msg;
			$ty && $type = $ty;
			include template('quicklink','admin');
		}
		else 
		{
			$hasIntoQuickLinkPrivilege['url'] = "?admin_main";
			__msg($hasIntoQuickLinkPrivilege);
		}        
    }
    
    /* 
	操作员添加
	添加操作员权限：operatorAdd
	*/
    function onquick_add()
	{
		$backReturn = array();
		// 是否有主分类修改/添加权限updateQtype
		$hasAddQuickPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "updateQuick");
		if ( $hasAddQuickPrivilege['return'] ) 
		{
			if(isset($this->post['submit_add']))
			{
				$id = !empty($this->post['Id'])?$this->post['Id']:0;
				if($id)
				{
					$quicklinkInfo = $_ENV['quick']->GetQuicklink($id);
					$quicklinkInfo['LinkName'] = trim($this->post['LinkName']);
					$quicklinkInfo['Parent'] = intval($this->post['Parent']);
					$quicklinkInfo['LinkUrl'] = trim($this->post['LinkUrl']);
					$quicklinkInfo['LinkType'] = trim($this->post['LinkType']);
					if(!empty($_FILES['LinkIcon']['name']))
					{
						@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
						$FastDFSClient = new FastDFSClient();
						$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
						$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
						$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
						$FastDFSInfo = $FastDFSClient->upload("sk");
						$quicklinkInfo['LinkIcon'] = $FastDFSInfo != -1?$FastDFSInfo:'';
					}
					
					unset($quicklinkInfo['Id']);
					$update = $_ENV['quick']->updateQuicklink($id,$quicklinkInfo);					
					$this->onquick("快捷链接修改成功！");
				}
				else
				{
					$quicklinkInfo['LinkName'] = trim($this->post['LinkName']);
					$quicklinkInfo['Parent'] = intval($this->post['Parent']);
					$quicklinkInfo['LinkUrl'] = trim($this->post['LinkUrl']);
					$quicklinkInfo['LinkType'] = trim($this->post['LinkType']);
					if(!empty($_FILES['LinkIcon']['name']))
					{
						@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
						$FastDFSClient = new FastDFSClient();
						$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
						$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
						$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
						$FastDFSInfo = $FastDFSClient->upload("sk");
						$quicklinkInfo['LinkIcon'] = $FastDFSInfo != -1?$FastDFSInfo:'';
					}				
					$update = $_ENV['quick']->insertQuicklink($quicklinkInfo);					
					$this->onquick("快捷链接添加成功！");
				}														
			}  	        
		}
		else
		{
			$hasAddQuickPrivilege['url'] = "?admin_quick/quick";
			__msg($hasAddQuickPrivilege);
		
		}    	
    }
	// 删除菜单
	// 权限：quickRemove
    function onquick_remove() 
    {
	   $returnBack = array();
	   $hasQuickRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "removeQuick");
	   if( $hasQuickRemovePrivilege['return'] )
	   {
			$id = isset($this->post['id']) ? intval($this->post['id']) : 0;
			$quicklinkInfo = $_ENV['quick']->GetQuicklink($id);  	
			if ($quicklinkInfo['Id'])
			{
				//根据菜单id获取下层目录
				$childLink = $_ENV['quick']->getSubLink($quicklinkInfo['Id']);
				if (count($childLink)>0)
				{
					$returnBack = array('return'=>1,'comment'=>"该快捷链接下面有子链接，请先删除子链接");
				}
				else 
				{
					if ($_ENV['quick']->deleteQuicklink($quicklinkInfo['Id']))
					{
						$returnBack = array('return'=>1,'comment'=>"删除快速链接成功");
					}
					else
					{
						$returnBack = array('return'=>0,'comment'=>"删除快速链接成功");
					}
				}
			}
			else 
			{
				$returnBack = array('return'=>0,'comment'=>"快速链接不存在");
			}
		}
		else
		{
			$returnBack = array('return'=>0,'comment'=>$hasQuickRemovePrivilege['comment']);
		}
    	// 统一输出返回结果
		exit(json_encode($returnBack));
    }

}
?>
