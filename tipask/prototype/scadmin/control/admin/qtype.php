<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_qtypecontrol extends base {

    function admin_qtypecontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("qtype");
		$this->load("menu");
    }

    function ondefault($message='')
	{
       $this->onqtype();
    }
	
	/* 
		主分类显示页面
		进入主分类页面权限：intoQtype
	*/
    function onqtype($msg='', $ty='') 
	{
		$hasIntoQtypePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoQtype");
		// 是否有进入操作员管理页面权限
		if ( $hasIntoQtypePrivilege['return'] )
		{
			if(isset($this->get[2]))
			{
				$qtype_info = $_ENV['qtype']->GetQType(intval($this->get[2]));	
				$qtype_info['faq'] = unserialize($qtype_info['faq']);
				$qtype_info['complain'] = unserialize($qtype_info['complain']);
				$qtype_info['trading'] = unserialize($qtype_info['trading']);
			}			
			$qtype_list = $_ENV['qtype']->GetAllQType(0,'',0);
			foreach($qtype_list as $qtypeId=>$qtypeInfo)
			{
				if($qtypeInfo['pid']>0)
				{
					$qtype_list[$qtypeId]['parentName']=$qtype_list[$qtypeInfo['pid']]['name'];
				}
				else
				{
					$qtype_list[$qtypeId]['parentName']="无分类";
				}
				$qtype_list[$qtypeId]['complain'] = unserialize($qtypeInfo['complain']);
				$qtype_list[$qtypeId]['faq'] = unserialize($qtypeInfo['faq']);
				$qtype_list[$qtypeId]['trading'] = unserialize($qtypeInfo['trading']);
			}
			$msg && $message = $msg;
			$ty && $type = $ty;
			include template('qtype','admin');
		}
		else 
		{
			$hasIntoQtypePrivilege['url'] = "?admin_main";
			__msg($hasIntoQtypePrivilege);
		}
        
    }
    
    /* 
	操作员添加
	添加操作员权限：operatorAdd
	*/
    function onqtype_add()
	{
		$backReturn = array();
		// 是否有主分类修改/添加权限updateQtype
		$hasAddQtypePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "updateQtype");
		if ( $hasAddQtypePrivilege['return'] ) 
		{
			if(isset($this->post['submit_add']))
			{
				$id = !empty($this->post['id'])?$this->post['id']:0;
				if($id)
				{
					$qtypeInfo = $_ENV['qtype']->GetQType($id);
					$qtypeInfo['name'] = trim($this->post['name']);
					$qtypeInfo['complain_type_id'] = intval($this->post['complain_type_id']);
					$qtypeInfo['complain'] = unserialize($qtypeInfo['complain']);
					$qtypeInfo['complain']['manager_name'] = trim($this->post['complain']['manager_name']);
					//$qtypeInfo['complain']['icon'] = trim($this->post['complain']['icon']);
					$qtypeInfo['complain']['visiable'] = trim($this->post['complain']['visiable']);
					//$qtypeInfo['complain'] = serialize($qtypeInfo['complain']);
					$qtypeInfo['visiable'] = trim($this->post['visiable']);
					$qtypeInfo['pid'] = intval($this->post['pid']);
					$qtypeInfo['displayOrder'] = intval($this->post['displayOrder']);
					$qtypeInfo['faq'] = unserialize($qtypeInfo['faq']);
					$qtypeInfo['faq']['visiable']=intval($this->post['faq']['visiable']);
					$qtypeInfo['faq'] = serialize($qtypeInfo['faq']);
					$qtypeInfo['trading'] = unserialize($qtypeInfo['trading']);
					$qtypeInfo['trading']['ServiceType']=intval($this->post['trading']['ServiceType']);
					$qtypeInfo['trading']['sellingOrderUrl']=trim($this->post['trading']['sellingOrderUrl']);
					$qtypeInfo['trading']['buyerOrderUrl']=trim($this->post['trading']['buyerOrderUrl']);
					$qtypeInfo['trading']['sellerOrderUrl']=trim($this->post['trading']['sellerOrderUrl']);
					$qtypeInfo['trading']['checkOrderUrl']=trim($this->post['trading']['checkOrderUrl']);
					$qtypeInfo['trading']['directOrderUrl']=trim($this->post['trading']['directOrderUrl']);
					$qtypeInfo['trading']['directPostOrderUrl']=trim($this->post['trading']['directPostOrderUrl']);
					$qtypeInfo['trading'] = serialize($qtypeInfo['trading']);
					if(!empty($_FILES['managerphoto']['name']))
					{
						@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
						$FastDFSClient = new FastDFSClient();
						$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
						$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
						$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
						$FastDFSInfo = $FastDFSClient->upload("sk");
						$qtypeInfo['complain']['icon'] = $FastDFSInfo != -1?$FastDFSInfo:'';
					}
					
					$qtypeInfo['complain'] = serialize($qtypeInfo['complain']);
					unset($qtypeInfo['id']);
					$update = $_ENV['qtype']->updateQtype($id,$qtypeInfo);
					
					$qtypeList = $_ENV['qtype']->GetAllQType(1,"",0);
					$this->cache->set('qtype_list',json_encode($qtypeList),30*60);//缓存60秒
					
					$this->onqtype("主分类修改成功！");
				}
				else
				{
					$qtypeInfo['name'] = trim($this->post['name']);
					$qtypeInfo['complain_type_id'] = intval($this->post['complain_type_id']);
					$qtypeInfo['complain']['manager_name'] = trim($this->post['complain']['manager_name']);
					//$qtypeInfo['complain']['icon'] = trim($this->post['complain']['icon']);
					$qtypeInfo['complain']['visiable'] = trim($this->post['complain']['visiable']);
					//$qtypeInfo['complain'] = serialize($qtypeInfo['complain']);
					$qtypeInfo['visiable'] = trim($this->post['visiable']);
					$qtypeInfo['pid'] = intval($this->post['pid']);
					$qtypeInfo['displayOrder'] = intval($this->post['displayOrder']);
					$qtypeInfo['faq']['visiable']=intval($this->post['faq']['visiable']);
					$qtypeInfo['faq'] = serialize($qtypeInfo['faq']);
					$qtypeInfo['trading']['ServiceType']=intval($this->post['trading']['ServiceType']);
					$qtypeInfo['trading']['sellingOrderUrl']=trim($this->post['trading']['sellingOrderUrl']);
					$qtypeInfo['trading']['buyerOrderUrl']=trim($this->post['trading']['buyerOrderUrl']);
					$qtypeInfo['trading']['sellerOrderUrl']=trim($this->post['trading']['sellerOrderUrl']);
					$qtypeInfo['trading']['checkOrderUrl']=trim($this->post['trading']['checkOrderUrl']);
					$qtypeInfo['trading']['directOrderUrl']=trim($this->post['trading']['directOrderUrl']);
					$qtypeInfo['trading']['directPostOrderUrl']=trim($this->post['trading']['directPostOrderUrl']);
					$qtypeInfo['trading'] = serialize($qtypeInfo['trading']);
					if(!empty($_FILES['managerphoto']['name']))
					{
						@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
						$FastDFSClient = new FastDFSClient();
						$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
						$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
						$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
						$FastDFSInfo = $FastDFSClient->upload("sk");
					}
					$qtypeInfo['complain']['icon'] = $FastDFSInfo != -1?$FastDFSInfo:'';
					$qtypeInfo['complain'] = serialize($qtypeInfo['complain']);
					$insert = $_ENV['qtype']->insertQtype($qtypeInfo);					
					
					$qtypeList = $_ENV['qtype']->GetAllQType(1,"",0);
					$this->cache->set('qtype_list',json_encode($qtypeList),30*60);//缓存60秒
					
					$this->onqtype("主分类修改成功！");
				}														
			}  	        
		}
		else
		{
			$hasAddQtypePrivilege['url'] = "?admin_qtype/qtype";
			__msg($hasAddQtypePrivilege);
		
		}
		
    	
    }
}
?>
