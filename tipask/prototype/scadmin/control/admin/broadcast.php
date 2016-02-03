<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_broadcastcontrol extends base {

    function admin_broadcastcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("broadcast");
		$this->load("menu");
    }

    function ondefault($message='')
	{
       $this->onbroadcast();
    }
	
	/* 
		主分类显示页面
		进入主分类页面权限：intoQtype
	*/
    function onbroadcast($msg='', $ty='') 
	{
		$action = "?admin_broadcast/broadcast";
		$hasIntoBroadcastPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoBroadCast");
		// 是否有进入操作员管理页面权限
		if ( $hasIntoBroadcastPrivilege['return'] )
		{
			$ConditionList['StartTime'] = isset($this->post['StartTime'])?$this->post['StartTime']:(isset($this->get[2])?$this->get[2]:date("Y-m-01",time()));   	
			$ConditionList['EndTime'] = isset($this->post['EndTime'])?$this->post['EndTime']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()+86400)); 
			
			$BreadCastStatusList = $this->ask_config->getBroadCastStatus();
			$BroadCastZoneList = $this->ask_config->getBroadCastZone();
			$ConditionList['BroadCastZone'] = isset($this->post['BroadCastZone'])?intval($this->post['BroadCastZone']):(isset($this->get[4])?intval($this->get[4]):-1);
			$ConditionList['BroadCastStatus'] = isset($this->post['BroadCastStatus'])?intval($this->post['BroadCastStatus']):(isset($this->get[5])?intval($this->get[5]):0);
			
			@$page = max(1, intval($this->get[6]));
			$export = trim($this->get[7])=="export"?1:0;
			$setting = $this->setting;

			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$BroadCastList = $_ENV['broadcast']->getBroadCastList($ConditionList,$page,$pagesize);
			foreach($BroadCastList['BroadCastList'] as $key => $value)
			{
				$BroadCastList['BroadCastList'][$key]['BroadCastZone'] = $BroadCastZoneList[$value['BroadCastZone']];
				$time = time();
				if($value['BroadCastStatus'] != 3)
				{				
					if(($value['StartTime'] <= $time) && ($value['EndTime'] >= $time))
					{
						$BroadCastList['BroadCastList'][$key]['BroadCastStatus'] = 1;
					}
					elseif($value['EndTime'] < $time)
					{
						$BroadCastList['BroadCastList'][$key]['BroadCastStatus'] = 2;	
					}
					elseif($value['StartTime'] > $time)
					{
						$BroadCastList['BroadCastList'][$key]['BroadCastStatus'] = 4;	
					}
				}
				$BroadCastList['BroadCastList'][$key]['BroadCastStatus'] = $BreadCastStatusList[$BroadCastList['BroadCastList'][$key]['BroadCastStatus']];
			}
			$departstr = page($complain_list['ComplainCount'], $pagesize, $page, "admin_broadcast/broadcast/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['BroadCastZone']."/".$ConditionList['BroadCastStatus']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_broadcast/broadcast/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['BroadCastZone']."/".$ConditionList['BroadCastStatus']."/".$page."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
			
			include template('broadcast','admin');
		}
		else 
		{
			$hasIntoBroadcastPrivilege['url'] = "?admin_main";
			__msg($hasIntoBroadcastPrivilege);
		}        
    }
    
    /* 
	操作员添加
	添加操作员权限：operatorAdd
	*/
    function onbroadcast_add()
	{
		$backReturn = array();
		// 是否有主分类修改/添加权限updateQtype
		$hasAddbroadcastPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "addBroadCast");
		if ( $hasAddbroadcastPrivilege['return'] ) 
		{
			$BroadCastZoneList = $this->ask_config->getBroadCastZone();
			if($this->post['operation']=="add")
			{
				$DataArr['StartTime'] = strtotime(trim($this->post['StartTime']));
				$DataArr['EndTime'] = strtotime(trim($this->post['EndTime']));
				if($DataArr['EndTime']<=time())
				{
					echo 2;
					return;
				}
				$DataArr['Content'] = cutstr(trim($this->post['Content']),50);
				if($DataArr['Content']=="")
				{
					echo 3;
					return;
				}
				$DataArr['BroadCastZone'] = intval($this->post['BroadCastZone'])?intval($this->post['BroadCastZone']):0;
				$DataArr['operator'] = $this->ask_login_name;
				$DataArr['AddTime'] = time();
				if($this->post['BroadCastAvailable']==1)
				{
					$DataArr['BroadCastStatus'] = 3;
				}
				else
				{
					$DataArr['BroadCastStatus'] = 1;	
				}
				$add = $_ENV['broadcast']->insertBroadCast($DataArr);				
				if($add)
				{
					echo 1;
					$this->sys_admin_log(0,$DataArr['operator'],$DataArr['operator'].'添加公告:'.$DataArr['Content'].",自:".$this->post['StartTime']."至".$this->post['EndTime'].",作用范围:".$BroadCastZoneList[$DataArr['BroadCastZone']],17);//系统操作日志
					return;
				}
				else
				{
					echo 0;
					return;
				}
			}
			else
			{
				include template('broadcast_add','admin');
			}																	  	        
		}
		else
		{
			$hasAddbroadcastPrivilege['url'] = "?admin_broadcast/broadcast";
			__msg($hasAddbroadcastPrivilege);
		
		}    	
    }
	function onbroadcast_update()
	{
		$backReturn = array();
		// 是否公告修改权限updateBroadCast
		$hasUpdatebroadcastPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "updateBroadCast");
		if ( $hasUpdatebroadcastPrivilege['return'] ) 
		{
			$Id = !empty($this->post['Id'])?intval($this->post['Id']):0;
			$broadcastInfo = $_ENV['broadcast']->GetBroadCast($Id);
			$BroadCastZoneList = $this->ask_config->getBroadCastZone();
			if($this->post['operation']=="update")
			{
				$DataArr['StartTime'] = strtotime(trim($this->post['StartTime']));
				$DataArr['EndTime'] = strtotime(trim($this->post['EndTime']));
				if($DataArr['EndTime']<=time())
				{
					echo 2;
					return;
				}
				$DataArr['Content'] = cutstr(trim($this->post['Content']),50);
				if($DataArr['Content']=="")
				{
					echo 3;
					return;
				}
				$DataArr['BroadCastZone'] = intval($this->post['BroadCastZone'])?intval($this->post['BroadCastZone']):0;
				if($this->post['BroadCastAvailable']==1)
				{
					$DataArr['BroadCastStatus'] = 3;
				}
				else
				{
					$DataArr['BroadCastStatus'] = 1;	
				}
				$update = $_ENV['broadcast']->updateBroadCast($Id,$DataArr);				
				if($update)
				{
					echo 1;
					if($broadcastInfo['StartTime']!=$DataArr['StartTime'])
					{
						$updateArr['StartTime'] = "开始时间由".date("Y-m-d H:i:s",$broadcastInfo['StartTime'])."改为".date("Y-m-d H:i:s",$DataArr['StartTime']);
					}
					if($broadcastInfo['EndTime']!=$DataArr['EndTime'])
					{
						$updateArr['EndTime'] = "结束时间时间由".date("Y-m-d H:i:s",$broadcastInfo['EndTime'])."改为".date("Y-m-d H:i:s",$DataArr['EndTime']);
					}
					if($broadcastInfo['Content']!=$DataArr['Content'])
					{
						$updateArr['Content'] = "公告内容由".$broadcastInfo['Content']."改为".$DataArr['Content'];
					}
					if($broadcastInfo['BroadCastZone']!=$DataArr['BroadCastZone'])
					{
						$updateArr['BroadCastZone'] = "作用区域由".$BroadCastZoneList[$broadcastInfo['BroadCastZone']]."改为".$BroadCastZoneList[$DataArr['BroadCastZone']];
					}
					$this->sys_admin_log(0,$this->ask_login_name,$this->ask_login_name.'修改公告:'.implode(',',$updateArr),17);//系统操作日志
					return;
				}
				else
				{
					echo 0;
					return;
				}
			}
			else
			{
				include template('broadcast_update','admin');
			}	        
		}
		else
		{
			$hasUpdatebroadcastPrivilege['url'] = "?admin_broadcast/broadcast";
			__msg($hasUpdatebroadcastPrivilege);
		
		}    	
    }
	// 删除菜单
	// 权限：broadcastRemove
    function onbroadcast_remove() 
    {
	   $returnBack = array();
	   $hasbroadcastRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "removebroadcast");
	   if( $hasbroadcastRemovePrivilege['return'] )
	   {
			$id = isset($this->post['id']) ? intval($this->post['id']) : 0;
			$broadcastlinkInfo = $_ENV['broadcast']->Getbroadcastlink($id);  	
			if ($broadcastlinkInfo['Id'])
			{
				//根据菜单id获取下层目录
				$childLink = $_ENV['broadcast']->getSubLink($broadcastlinkInfo['Id']);
				if (count($childLink)>0)
				{
					$returnBack = array('return'=>1,'comment'=>"该快捷链接下面有子链接，请先删除子链接");
				}
				else 
				{
					if ($_ENV['broadcast']->deletebroadcastlink($broadcastlinkInfo['Id']))
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
			$returnBack = array('return'=>0,'comment'=>$hasbroadcastRemovePrivilege['comment']);
		}
    	// 统一输出返回结果
		exit(json_encode($returnBack));
    }

}
?>
