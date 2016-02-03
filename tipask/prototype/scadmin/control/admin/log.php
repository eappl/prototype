<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_logcontrol extends base {
    function admin_logcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("operator");
		$this->load("menu");
		$this->load("log"); 		
    }

    function ondefault($message='') {
        $this->onlogView();
    }
    function onlogView($msg='', $ty='')
	{
		$action = "index.php?admin_log/logView";
		$hasLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "logView");
		$hasLogViewPrivilege['url'] = "?admin_main";
		$hasLogViewPrivilege['return'] = true;
		!$hasLogViewPrivilege['return']  && __msg( $hasLogViewPrivilege );
		
		$LogTypeList = $this->ask_config->getLogType(); 
		$operator_list = $_ENV['operator']->getList(0,0);		

		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()));
		$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-d",time()),date("Y-m-t",strtotime($ConditionList['StartDate'])));    	
		$ConditionList['operatorId'] = isset($this->post['operatorId'])?intval(urldecode($this->post['operatorId'])):(isset($this->get[4])?intval(urldecode($this->get[4])):0);
		$ConditionList['operator'] = !in_array($ConditionList['operatorId'],array(-2,-1,0))?$operator_list[$ConditionList['operatorId']]['login_name']:$ConditionList['operatorId'];
		$ConditionList['QuestionId'] = isset($this->post['QuestionId'])?intval($this->post['QuestionId']):(isset($this->get[5])?intval($this->get[5]):0);
		$ConditionList['log_type_id'] = isset($this->post['log_type_id'])?intval(urldecode($this->post['log_type_id'])):(isset($this->get[6])?intval(urldecode($this->get[6])):0);
		$ConditionList['AuthorName'] = isset($this->post['AuthorName'])?trim(urldecode($this->post['AuthorName'])):(isset($this->get[7])?trim(urldecode($this->get[7])):'');

		$ConditionList['log_type'] = $ConditionList['log_type_id']?$LogTypeList[$ConditionList['log_type_id']]:$ConditionList['log_type_id'];
		@$page = max(1, intval($this->get[8]));
		$export = trim($this->get[9])=="export"?1:0;
		$setting = $this->setting;
		
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$log_list = $_ENV['log']->getLogList($ConditionList,$page,$pagesize);

			$departstr = page($log_list['LogCount'], $pagesize, $page, "admin_log/logView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['operatorId'])."/".$ConditionList['QuestionId']."/".$ConditionList['log_type_id']."/".urlencode($ConditionList['AuthorName']));
			$downloadstr = page_url("<下载EXCEL表格>", "admin_log/logView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['operatorId'])."/".$ConditionList['QuestionId']."/".$ConditionList['log_type_id']."/".urlencode($ConditionList['AuthorName'])."/".$page."/export");
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
			$FileName='操作日志表';
			$oExcel->download($FileName)->addSheet('日志表');
			//标题栏
			$title = array("日志ID","对应问题ID","用户名","操作人","操作时间","日志内容");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$log_list = $_ENV['log']->getLogList($ConditionList,$page,$pagesize);
				foreach($log_list['LogList'] as $key => $value)
				{	
				$value['message'] = htmlspecialchars_decode($value['message']);
				$log_list['LogList'][$key]['message'] = preg_replace("/<(.*?)>/","",$value['message']); 
					$excelArr = array("id"=>$log_list['LogList'][$key]['id'],
					"qid"=>$log_list['LogList'][$key]['qid'],
					"AuthorName"=>$log_list['LogList'][$key]['AuthorName'],
					"user"=>$log_list['LogList'][$key]['user'],
					"time"=>date("Y-m-d H:i:s",$log_list['LogList'][$key]['time']),
					"message"=>$log_list['LogList'][$key]['message'],
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($order_list['OrderList']);
			} 
			$oExcel->closeSheet()->close();	
		}		
		include template('logview','admin');        
    }
}
?>
