<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_bindingcontrol extends base {
    function admin_bindingcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("category");
        $this->load("operator");
		$this->load("menu"); 
		$this->load("qtype");
		$this->load("complain");
		$this->load("bind_log");  		
    }

    function ondefault($message='') {
        $this->onorderLogView();
    }
    function onorderLogView($msg='', $ty='')
	{
		$action = "index.php?admin_binding/orderLogView";
		$hasOrderLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "orderLogView");
		$hasOrderLogViewPrivilege['url'] = "?admin_main";
		$hasOrderLogViewPrivilege['return'] = true;
		!$hasOrderLogViewPrivilege['return']  && __msg( $hasOrderLogViewPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-86400));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()-86400));
    	
		$ConditionList['author'] = isset($this->post['author'])?trim(urldecode($this->post['author'])):(isset($this->get[4])?trim(urldecode($this->get[4])):"");
		if(trim($ConditionList['author'])=="")
		{
			$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-d",time()-86400),date("Y-m-t",strtotime($ConditionList['StartDate'])));
		}
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim(urldecode($this->post['operator_loginId'])):(isset($this->get[5])?trim(urldecode($this->get[5])):"");								
		if($ConditionList['operator_loginId']!="")
		{
			$OperatorInfo = $_ENV['operator']->getByColumn("login_name",urldecode($ConditionList['operator_loginId']));
			if($OperatorInfo['id'])
			{
				$ConditionList['scopid'] = $OperatorInfo['id']; 
			}
			else
			{
				$ConditionList['operator_loginId'] = "";
			}
		}
		$ConditionList['bid'] = isset($this->post['bid'])?intval($this->post['bid']):(isset($this->get[6])?intval($this->get[6]):0);								
		$BindTypeList = $this->ask_config->getBindType();
		$ConditionList['ServiceType'] = isset($this->post['ServiceType'])?intval($this->post['ServiceType']):(isset($this->get[7])?intval($this->get[7]):0);
		$qtypeList = $_ENV['qtype']->GetAllQType(0,"",0,"");
		$tradingList = array(0=>"全部");
		foreach($qtypeList as $key => $value)
		{
			$trading = unserialize($value['trading']);
			if($trading['ServiceType']>0)
			{
				$tradingList[$trading['ServiceType']] = $value['name'];
			}
		}
		
		@$page = max(1, intval($this->get[8]));
		$export = trim($this->get[9])=="export"?1:0;
		$setting = $this->setting;
		
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$order_list = $_ENV['bind_log']->getOrderList($ConditionList,$page,$pagesize);
 			foreach($order_list['OrderList'] as $key => $value)
			{
				if(!isset($OperatorList[$value['scopid']]))
				{
					$OperatorList[$value['scopid']] = $_ENV['operator']->get($value['scopid']);
				}
				$order_list['OrderList'][$key]['login_name'] = $OperatorList[$value['scopid']]['login_name'];
				if($value['bind_type']==1)
				{
					$order_list['OrderList'][$key]['author'] = $value['author_buyer'];
				}
				elseif($value['bind_type']==2)
				{
					$order_list['OrderList'][$key]['author'] = $value['author_seller'];
				}
				if($value['bind_time']>0)
				{
					$order_list['OrderList'][$key]['bind_time'] = date("Y-m-d H:i:s",$value['bind_time']);
					$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"尚未解绑";
				}
				else
				{
					$order_list['OrderList'][$key]['bind_time'] = "未知";
					$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"未知";				
				}
				$order_list['OrderList'][$key]['tradingType'] = isset($tradingList[$value['order_type']])?$tradingList[$value['order_type']]:"未知类型";
				$order_list['OrderList'][$key]['bindType'] = isset($BindTypeList[$value['bind_type']])?$BindTypeList[$value['bind_type']]:"未知类型";
			}
			$departstr = page($order_list['OrderCount'], $pagesize, $page, "admin_binding/orderLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$ConditionList['ServiceType']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_binding/orderLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$ConditionList['ServiceType']."/".$page."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{
			set_time_limit(0);
			$page = 1;
			$pagesize = 1000;
			$num = 1;
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='专属客服佣金表';
			$oExcel->download($FileName)->addSheet('佣金表');
			//标题栏
			$title = array("客服账号","被绑定用户帐号","绑定时间","解绑时间","交易类型","身份类型","交易单号","订单完成时间","订单金额","交易佣金");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$order_list = $_ENV['bind_log']->getOrderList($ConditionList,$page,$pagesize);
				foreach($order_list['OrderList'] as $key => $value)
				{
					if(!isset($OperatorList[$value['scopid']]))
					{
						$OperatorList[$value['scopid']] = $_ENV['operator']->get($value['scopid']);
					}
					$order_list['OrderList'][$key]['login_name'] = $OperatorList[$value['scopid']]['login_name'];
					if($value['bind_type']==1)
					{
						$order_list['OrderList'][$key]['author'] = $value['author_buyer'];
					}
					elseif($value['bind_type']==2)
					{
						$order_list['OrderList'][$key]['author'] = $value['author_seller'];
					}
					if($value['bind_time']>0)
					{
						$order_list['OrderList'][$key]['bind_time'] = date("Y-m-d H:i:s",$value['bind_time']);
						$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"尚未解绑";
					}
					else
					{
						$order_list['OrderList'][$key]['bind_time'] = "未知";
						$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"未知";				
					}
					$order_list['OrderList'][$key]['tradingType'] = isset($tradingList[$value['order_type']])?$tradingList[$value['order_type']]:"未知类型";
					$order_list['OrderList'][$key]['bindType'] = isset($BindTypeList[$value['bind_type']])?$BindTypeList[$value['bind_type']]:"未知类型";	
					
					$excelArr = array("login_name"=>$order_list['OrderList'][$key]['login_name'],
					"author"=>$order_list['OrderList'][$key]['author'],
					"bind_time"=>$order_list['OrderList'][$key]['bind_time'],
					"unbind_time"=>$order_list['OrderList'][$key]['unbind_time'],
					"tradingType"=>$order_list['OrderList'][$key]['tradingType'],
					"bindType"=>$order_list['OrderList'][$key]['bindType'],
					"order_id"=>$order_list['OrderList'][$key]['order_id'],
					"deal_time"=>date("Y-m-d H:i:s",$order_list['OrderList'][$key]['deal_time']),
					"amount"=>$order_list['OrderList'][$key]['amount'],
					"commission"=>$order_list['OrderList'][$key]['commission'],				
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($order_list['OrderList']);
			}			 
			$oExcel->closeSheet()->close();	
		}		
		include template('orderLogview','admin');        
    }
    function onmyOrderLogView($msg='', $ty='')
	{
		$action = "index.php?admin_binding/myOrderLogView";
		$hasOrderLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myOrderLogView");
		$hasOrderLogViewPrivilege['url'] = "?admin_main";
		!$hasOrderLogViewPrivilege['return']  && __msg( $hasOrderLogViewPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-86400));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()-86400));
    			
		$ConditionList['author'] = isset($this->post['author'])?trim(urldecode($this->post['author'])):(isset($this->get[4])?trim(urldecode($this->get[4])):"");
		if(trim($ConditionList['author'])=="")
		{
			$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-d",time()-86400),date("Y-m-t",strtotime($ConditionList['StartDate'])));
		}
		$OperatorInfo = $_ENV['operator']->getByColumn("login_name",urldecode($this->ask_login_name));
		//$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim(urldecode($this->post['operator_loginId'])):(isset($this->get[5])?trim(urldecode($this->get[5])):"");								
		if($OperatorInfo['id']>0)
		{
			$ConditionList['scopid'] = $OperatorInfo['id']; 
		}
		$ConditionList['bid'] = isset($this->post['bid'])?intval($this->post['bid']):(isset($this->get[6])?intval($this->get[6]):0);								
		$BindTypeList = $this->ask_config->getBindType();
		$ConditionList['ServiceType'] = isset($this->post['ServiceType'])?intval($this->post['ServiceType']):(isset($this->get[7])?intval($this->get[7]):0);
		$qtypeList = $_ENV['qtype']->GetAllQType(0,"",0,"");
		$tradingList = array(0=>"全部");
		foreach($qtypeList as $key => $value)
		{
			$trading = unserialize($value['trading']);
			if($trading['ServiceType']>0)
			{
				$tradingList[$trading['ServiceType']] = $value['name'];
			}
		}
		
		@$page = max(1, intval($this->get[8]));
		$export = trim($this->get[9])=="export"?1:0;
		$setting = $this->setting;
		
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$order_list = $_ENV['bind_log']->getOrderList($ConditionList,$page,$pagesize);
 			foreach($order_list['OrderList'] as $key => $value)
			{
				if(!isset($OperatorList[$value['scopid']]))
				{
					$OperatorList[$value['scopid']] = $_ENV['operator']->get($value['scopid']);
				}
				$order_list['OrderList'][$key]['login_name'] = $OperatorList[$value['scopid']]['login_name'];
				if($value['bind_type']==1)
				{
					$order_list['OrderList'][$key]['author'] = $value['author_buyer'];
				}
				elseif($value['bind_type']==2)
				{
					$order_list['OrderList'][$key]['author'] = $value['author_seller'];
				}
				if($value['bind_time']>0)
				{
					$order_list['OrderList'][$key]['bind_time'] = date("Y-m-d H:i:s",$value['bind_time']);
					$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"尚未解绑";
				}
				else
				{
					$order_list['OrderList'][$key]['bind_time'] = "未知";
					$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"未知";				
				}
				$order_list['OrderList'][$key]['tradingType'] = isset($tradingList[$value['order_type']])?$tradingList[$value['order_type']]:"未知类型";
				$order_list['OrderList'][$key]['bindType'] = isset($BindTypeList[$value['bind_type']])?$BindTypeList[$value['bind_type']]:"未知类型";
			}
			$departstr = page($order_list['OrderCount'], $pagesize, $page, "admin_binding/myOrderLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$ConditionList['ServiceType']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_binding/myOrderLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$ConditionList['ServiceType']."/".$page."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{
			set_time_limit(0);
			$page = 1;
			$pagesize = 1000;
			$num = 1;
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='我的专属客服佣金表';
			$oExcel->download($FileName)->addSheet('佣金表');
			//标题栏
			$title = array("客服账号","被绑定用户帐号","绑定时间","解绑时间","交易类型","身份类型","交易单号","订单完成时间","订单金额","交易佣金");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$order_list = $_ENV['bind_log']->getOrderList($ConditionList,$page,$pagesize);
				foreach($order_list['OrderList'] as $key => $value)
				{
					if(!isset($OperatorList[$value['scopid']]))
					{
						$OperatorList[$value['scopid']] = $_ENV['operator']->get($value['scopid']);
					}
					$order_list['OrderList'][$key]['login_name'] = $OperatorList[$value['scopid']]['login_name'];
					if($value['bind_type']==1)
					{
						$order_list['OrderList'][$key]['author'] = $value['author_buyer'];
					}
					elseif($value['bind_type']==2)
					{
						$order_list['OrderList'][$key]['author'] = $value['author_seller'];
					}
					if($value['bind_time']>0)
					{
						$order_list['OrderList'][$key]['bind_time'] = date("Y-m-d H:i:s",$value['bind_time']);
						$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"尚未解绑";
					}
					else
					{
						$order_list['OrderList'][$key]['bind_time'] = "未知";
						$order_list['OrderList'][$key]['unbind_time'] = $value['unbind_time']?date("Y-m-d H:i:s",$value['unbind_time']):"未知";				
					}
					$order_list['OrderList'][$key]['tradingType'] = isset($tradingList[$value['order_type']])?$tradingList[$value['order_type']]:"未知类型";
					$order_list['OrderList'][$key]['bindType'] = isset($BindTypeList[$value['bind_type']])?$BindTypeList[$value['bind_type']]:"未知类型";	
					
					$excelArr = array("login_name"=>$order_list['OrderList'][$key]['login_name'],
					"author"=>$order_list['OrderList'][$key]['author'],
					"bind_time"=>$order_list['OrderList'][$key]['bind_time'],
					"unbind_time"=>$order_list['OrderList'][$key]['unbind_time'],
					"tradingType"=>$order_list['OrderList'][$key]['tradingType'],
					"bindType"=>$order_list['OrderList'][$key]['bindType'],
					"order_id"=>$order_list['OrderList'][$key]['order_id'],
					"deal_time"=>date("Y-m-d H:i:s",$order_list['OrderList'][$key]['deal_time']),
					"amount"=>$order_list['OrderList'][$key]['amount'],
					"commission"=>$order_list['OrderList'][$key]['commission'],				
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($order_list['OrderList']);
			}			 
			$oExcel->closeSheet()->close();	
		}		
		include template('myOrderLogview','admin');        
    }
    function ontotalOrderLogView($msg='', $ty='')
	{
		$action = "index.php?admin_binding/totalOrderLogView";
		$hasOrderLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "totalOrderLogView");
		$hasOrderLogViewPrivilege['url'] = "?admin_main";
		$hasOrderLogViewPrivilege['return'] = true;
		!$hasOrderLogViewPrivilege['return']  && __msg( $hasOrderLogViewPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-86400));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()-86400));
    			
		$ConditionList['author'] = isset($this->post['author'])?trim(urldecode($this->post['author'])):(isset($this->get[4])?trim(urldecode($this->get[4])):"");
		if(trim($ConditionList['author'])=="")
		{
			$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-d",time()-86400),date("Y-m-t",strtotime($ConditionList['StartDate'])));
		}
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim(urldecode($this->post['operator_loginId'])):(isset($this->get[5])?trim(urldecode($this->get[5])):"");								
		if($ConditionList['operator_loginId']!="")
		{
			$OperatorInfo = $_ENV['operator']->getByColumn("login_name",urldecode($ConditionList['operator_loginId']));
			if($OperatorInfo['id'])
			{
				$ConditionList['scopid'] = $OperatorInfo['id']; 
			}
			else
			{
				$ConditionList['operator_loginId'] = "";
			}
		}
		$ConditionList['bid'] = isset($this->post['bid'])?intval($this->post['bid']):(isset($this->get[6])?intval($this->get[6]):0);								
		$BindTypeList = $this->ask_config->getBindType();
		$ConditionList['ServiceType'] = isset($this->post['ServiceType'])?intval($this->post['ServiceType']):(isset($this->get[7])?intval($this->get[7]):0);
		$qtypeList = $_ENV['qtype']->GetAllQType(0,"",0,"");
		$tradingList = array(0=>"全部");
		foreach($qtypeList as $key => $value)
		{
			$trading = unserialize($value['trading']);
			if($trading['ServiceType']>0)
			{
				$tradingList[$trading['ServiceType']] = $value['name'];
			}
		}
		$export = trim($this->get[8])=="export"?1:0;
		$setting = $this->setting;
		
		if(!$export)
		{
 			$OrderStatus = $_ENV['bind_log']->getOrderStatus($ConditionList);
			foreach($OrderStatus['OrderStatus'] as $key => $value)
			{
				if(!isset($OperatorList[$value['scopid']]))
				{
					$OperatorList[$value['scopid']] = $_ENV['operator']->get($value['scopid']);
				}
				$OrderStatus['OrderStatus'][$key]['login_name'] = $OperatorList[$value['scopid']]['login_name'];
				$OrderStatus['total']['order_count'] += $value['order_count'];
				$OrderStatus['total']['total_amount'] += $value['total_amount'];
				$OrderStatus['total']['total_commission'] += $value['total_commission'];
				
			}
			$downloadstr = page_url("<下载EXCEL表格>", "admin_binding/totalOrderLogview/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$ConditionList['ServiceType']."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{			
			set_time_limit(0);
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='专属客服佣金汇总表';
			$oExcel->download($FileName)->addSheet('汇总表');
			//标题栏
			$title = array("客服账号","订单数量","订单总金额","佣金总金额");
			$oExcel->addRows(array($title));
			
 			$OrderStatus = $_ENV['bind_log']->getOrderStatus($ConditionList);
			foreach($OrderStatus['OrderStatus'] as $key => $value)
			{
				if(!isset($OperatorList[$value['scopid']]))
				{
					$OperatorList[$value['scopid']] = $_ENV['operator']->get($value['scopid']);
				}
				$OrderStatus['OrderStatus'][$key]['login_name'] = $OperatorList[$value['scopid']]['login_name'];
				$OrderStatus['total']['order_count'] += $value['order_count'];
				$OrderStatus['total']['total_amount'] += $value['total_amount'];
				$OrderStatus['total']['total_commission'] += $value['total_commission'];

				
				$excelArr = array("login_name"=>$OrderStatus['OrderStatus'][$key]['login_name'],
				"author"=>$OrderStatus['OrderStatus'][$key]['order_count'],
				"total_amount"=>$OrderStatus['OrderStatus'][$key]['total_amount'],
				"total_commission"=>$OrderStatus['OrderStatus'][$key]['total_commission'],
				);
				$oExcel->addRows(array($excelArr));
			}
			$excelArr = array("login_name"=>"总计",
			"author"=>$OrderStatus['total']['order_count'],
			"total_amount"=>$OrderStatus['total']['total_amount'],
			"total_commission"=>$OrderStatus['total']['total_commission'],
			);
			$oExcel->addRows(array($excelArr));
			$oExcel->closeSheet()->close();				 
		}		
		include template('totalOrderLogview','admin');        
    }
    function onmyBindLogView($msg='', $ty='')
	{
		$action = "index.php?admin_binding/myBindLogView";
		$hasOrderLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myBindLogView");
		$hasOrderLogViewPrivilege['url'] = "?admin_main";
		!$hasOrderLogViewPrivilege['return']  && __msg( $hasOrderLogViewPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()));
    			
		$ConditionList['author'] = isset($this->post['author'])?trim(urldecode($this->post['author'])):(isset($this->get[4])?trim(urldecode($this->get[4])):"");
		$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-d",time()),date("Y-m-t",strtotime($ConditionList['StartDate'])));
		$OperatorInfo = $_ENV['operator']->getByColumn("login_name",urldecode($this->ask_login_name));
		//$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim(urlencode($this->post['operator_loginId'])):(isset($this->get[5])?trim(urlencode($this->get[5])):"");								
		if($OperatorInfo['id']>0)
		{
			$ConditionList['scopid'] = $OperatorInfo['id']; 
		}
		$ConditionList['bid'] = isset($this->post['bid'])?trim($this->post['bid']):(isset($this->get[6])?trim($this->get[6]):'all');								
		$BindTypeList = $this->ask_config->getBindOperateType();

		
		@$page = max(1, intval($this->get[7]));
		$export = trim($this->get[8])=="export"?1:0;
		$setting = $this->setting;
		
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$bind_log_list = $_ENV['bind_log']->getBindLogList($ConditionList,$page,$pagesize);
			foreach($bind_log_list['BindLogList'] as $key => $value)
			{
				if(!isset($OperatorList[$value['scopid']]))
				{
					$OperatorList[$value['scid']] = $_ENV['operator']->get($value['scid']);
				}
				$bind_log_list['BindLogList'][$key]['login_name'] = $OperatorList[$value['scid']]['login_name'];
				$bind_log_list['BindLogList'][$key]['bind_type'] = $BindTypeList[$value['bind_type']];
			}
			$departstr = page($bind_log_list['BindLogCount'], $pagesize, $page, "admin_binding/myBindLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_binding/myBindLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$page."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{
			set_time_limit(0);
			$page = 1;
			$pagesize = 1000;
			$num = 1;
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='我的专属客服绑定记录表';
			$oExcel->download($FileName)->addSheet('绑定记录表');
			//标题栏
			$title = array("客服账号","被绑定用户帐号","操作类型","操作时间");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$bind_log_list = $_ENV['bind_log']->getBindLogList($ConditionList,$page,$pagesize);
				foreach($bind_log_list['BindLogList'] as $key => $value)
				{
					if(!isset($OperatorList[$value['scopid']]))
					{
						$OperatorList[$value['scid']] = $_ENV['operator']->get($value['scid']);
					}
					$bind_log_list['BindLogList'][$key]['login_name'] = $OperatorList[$value['scid']]['login_name'];
					$bind_log_list['BindLogList'][$key]['bind_type'] = $BindTypeList[$value['bind_type']];
					
					$excelArr = array("login_name"=>$bind_log_list['BindLogList'][$key]['login_name'],
					"author"=>$bind_log_list['BindLogList'][$key]['author'],
					"bind_type"=>$bind_log_list['BindLogList'][$key]['bind_type'],
					"time"=>date("Y-m-d H:i:s",$bind_log_list['BindLogList'][$key]['time'])			
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($bind_log_list['BindLogList']);
			}			 
			$oExcel->closeSheet()->close();	
		}		
		include template('myBindLogview','admin');        
    }
    function onbindLogView($msg='', $ty='')
	{
		$action = "index.php?admin_binding/bindLogView";
		$hasOrderLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "bindLogView");
		$hasOrderLogViewPrivilege['url'] = "?admin_main";
		!$hasOrderLogViewPrivilege['return']  && __msg( $hasOrderLogViewPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()));
    			
		$ConditionList['author'] = isset($this->post['author'])?trim(urldecode($this->post['author'])):(isset($this->get[4])?trim(urldecode($this->get[4])):"");
		$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-d",time()),date("Y-m-t",strtotime($ConditionList['StartDate'])));
		$OperatorInfo = $_ENV['operator']->getByColumn("login_name",urldecode($this->ask_login_name));
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim(urldecode($this->post['operator_loginId'])):(isset($this->get[5])?trim(urldecode($this->get[5])):"");								
		if($ConditionList['operator_loginId']!="")
		{
			$OperatorInfo = $_ENV['operator']->getByColumn("login_name",urldecode($ConditionList['operator_loginId']));
			if($OperatorInfo['id'])
			{
				$ConditionList['scopid'] = $OperatorInfo['id']; 
			}
			else
			{
				$ConditionList['operator_loginId'] = "";
			}
		}
		$ConditionList['bid'] = isset($this->post['bid'])?trim($this->post['bid']):(isset($this->get[6])?trim($this->get[6]):'all');								
		$BindTypeList = $this->ask_config->getBindOperateType();

		
		@$page = max(1, intval($this->get[7]));
		$export = trim($this->get[8])=="export"?1:0;
		$setting = $this->setting;
		
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$bind_log_list = $_ENV['bind_log']->getBindLogList($ConditionList,$page,$pagesize);
			foreach($bind_log_list['BindLogList'] as $key => $value)
			{
				if(!isset($OperatorList[$value['scopid']]))
				{
					$OperatorList[$value['scid']] = $_ENV['operator']->get($value['scid']);
				}
				$bind_log_list['BindLogList'][$key]['login_name'] = $OperatorList[$value['scid']]['login_name'];
				$bind_log_list['BindLogList'][$key]['bind_type'] = $BindTypeList[$value['bind_type']];
			}
			$departstr = page($bind_log_list['BindLogCount'], $pagesize, $page, "admin_binding/bindLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_binding/bindLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['operator_loginId'])."/".$ConditionList['bid']."/".$page."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{
			set_time_limit(0);
			$page = 1;
			$pagesize = 1000;
			$num = 1;
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='专属客服绑定记录表';
			$oExcel->download($FileName)->addSheet('绑定记录表');
			//标题栏
			$title = array("客服账号","被绑定用户帐号","操作类型","操作时间");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$bind_log_list = $_ENV['bind_log']->getBindLogList($ConditionList,$page,$pagesize);
				foreach($bind_log_list['BindLogList'] as $key => $value)
				{
					if(!isset($OperatorList[$value['scopid']]))
					{
						$OperatorList[$value['scid']] = $_ENV['operator']->get($value['scid']);
					}
					$bind_log_list['BindLogList'][$key]['login_name'] = $OperatorList[$value['scid']]['login_name'];
					$bind_log_list['BindLogList'][$key]['bind_type'] = $BindTypeList[$value['bind_type']];
					
					$excelArr = array("login_name"=>$bind_log_list['BindLogList'][$key]['login_name'],
					"author"=>$bind_log_list['BindLogList'][$key]['author'],
					"bind_type"=>$bind_log_list['BindLogList'][$key]['bind_type'],
					"time"=>date("Y-m-d H:i:s",$bind_log_list['BindLogList'][$key]['time'])			
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($bind_log_list['BindLogList']);
			}			 
			$oExcel->closeSheet()->close();	
		}		
		include template('bindLogview','admin');        
    }	
}
?>
