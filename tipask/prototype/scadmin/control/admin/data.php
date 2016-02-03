<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_datacontrol extends base {
    function admin_datacontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("category");
        $this->load("question");
		$this->load("complain");
        $this->load("operator");
		$this->load("menu"); 
		$this->load("qtype");
		$this->load("department");
		$this->load("view");
    }

    function ondefault($message='') {
        $this->oncomplainView();
    }
    /* 
		intoResponseDayData:进入客服响应数据
	*/
    function onresponseDayData($msg='', $ty='')
	{
		//只查询转为投诉的问题
		$action = "index.php?admin_data/responseDayData";
		$hasIntoResponseDayDataPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoResponseDayData");
		$hasIntoResponseDayDataPrivilege['url'] = "?admin_main";
		!$hasIntoResponseDayDataPrivilege['return']  && __msg( $hasIntoResponseDayDataPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-86400));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()-86400)); 
		$DepartmentList = $_ENV['department']->get_list();
		$ConditionList['DepartmentId'] = isset($this->post['DepartmentId'])?intval($this->post['DepartmentId']):(isset($this->get[4])?intval($this->get[4]):0);
		$QtypeList = $_ENV['qtype']->GetAllQType(0,'',0);
		$ConditionList['QtypeId'] = isset($this->post['QtypeId'])?intval($this->post['QtypeId']):(isset($this->get[5])?intval($this->get[5]):0);
		$QtypeFlag = 0;
		if($ConditionList['QtypeId']>0)
		{
			foreach($QtypeList as $key => $QtypeInfo)
			{
				if($QtypeInfo['id'] == $ConditionList['QtypeId'])
				{
					$QtypeFlag = 1;
					break;
				}
			}
			if($QtypeFlag == 0)
			{
				$ConditionList['QtypeId'] = 0;	
			}
		}
		$export = trim($this->get[6])=="export"?1:0;
		if(!$export)
		{
			$ResponseDayArr = $_ENV['question']->getResponseDay($ConditionList);
			
			$OnlineOperatorCount = $_ENV['operator']->getOnlineOperatorCount($ConditionList);

			include( TIPASK_ROOT . '/lib/fusion/Includes/FusionCharts_Gen.php');

			# Create Multiseries ColumnD chart object using FusionCharts PHP Class
			$FC = new FusionCharts("MSColumn2DLineDY",'1200','400');

			# Set the relative path of the swf file
			$FC->setSWFPath( '../Charts/');

			# Store chart attributes in a variable
			$strParam="caption='客服响应数据';xAxisName='时间段';baseFontSize=12;decimalPrecision=0;showValues=0;formatNumberScale=0;labelStep=1;numvdivlines=$divideV;rotateNames=0;yAxisMinValue=0;yAxisMaxValue=10;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';pYAxisName=客服;sYAxisName=客服响应数据;hoverCapSepChar=，";

			# Set chart attributes

			$FC->setChartParams($strParam);
			foreach($ResponseDayArr as $Hour => $data)
			{
				$FC->addCategory($Hour.":00");				
			}
			$FC->addDataset("进单量");
			foreach($ResponseDayArr as $Hour => $data)
			{
				$FC->addChartData($data['ReceiveCount']);
			}
			$FC->addDataset("回答量");
			foreach($ResponseDayArr as $Hour => $data)
			{
				$FC->addChartData($data['AnsweredCount']);
			}
			$FC->addDataset("在班客服");
			foreach($ResponseDayArr as $Hour => $data)
			{
				if(isset($OnlineOperatorCount[$Hour]))
				{
					$FC->addChartData($OnlineOperatorCount[$Hour]);
				}
				else
				{
					$FC->addChartData(0);
				}
			}
			$FC->addDataset("平均响应时间","parentYAxis=S");
			foreach($ResponseDayArr as $Hour => $data)
			{
				$FC->addChartData($data['AverageResponseTime']);
			}
			$downloadstr = page_url("<下载EXCEL表格>", "admin_data/responseDayData/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".$ConditionList['DepartmentId']."/".$ConditionList['QtypeId']."/export");
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
			$FileName=$ConditionList['Date'] .'客服响应数据';
			$oExcel->download($FileName)->addSheet('客服响应数据');
			//标题栏
			$title = array("时间段","进单量","回复量","在班客服","平均响应时间");
			$oExcel->addRows(array($title));
			$ResponseDayArr = $_ENV['question']->getResponseDay($ConditionList);
			$OnlineOperatorCount = $_ENV['operator']->getOnlineOperatorCount($ConditionList);
			foreach($ResponseDayArr as $Hour => $data)
			{
				$excelArr = array("Hour"=>$Hour.":00",
				"ReceiveCount"=>$data['ReceiveCount'],
				"AnsweredCount"=>$data['AnsweredCount'],
				"OnlineOperator"=>isset($OnlineOperatorCount[$Hour])?$OnlineOperatorCount[$Hour]:0,
				"AverageResponseTime"=>$data['AverageResponseTime'],
				);
				$oExcel->addRows(array($excelArr));
			}
			$oExcel->closeSheet()->close();	
		}
		include template('responseDay','admin');        
    }
    /* 
		intoResponseDayData:进入客服响应数据
	*/
    function onresponseDayDataStacked($msg='', $ty='')
	{
		//只查询转为投诉的问题
		$action = "index.php?admin_data/responseDayDataStacked";
		$hasIntoResponseDayStackedDataPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoResponseDayStackedData");
		$hasIntoResponseDayStackedDataPrivilege['url'] = "?admin_main";
		!$hasIntoResponseDayStackedDataPrivilege['return']  && __msg( $hasIntoResponseDayStackedDataPrivilege );
		
    	
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-86400));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time()-86400)); 
		$DepartmentList = $_ENV['department']->get_list();
		$ConditionList['DepartmentId'] = isset($this->post['DepartmentId'])?intval($this->post['DepartmentId']):(isset($this->get[4])?intval($this->get[4]):0);
		$QtypeList = $_ENV['qtype']->GetAllQType(0,'',0);
		$ConditionList['QtypeId'] =  0;
		$export = trim($this->get[6])=="export"?1:0;
		if(!$export)
		{
			$ResponseDayArr = $_ENV['question']->getResponseDay($ConditionList);
			foreach($QtypeList as $Key => $QtypeInfo)
			{
				foreach($ResponseDayArr as $Hour => $data)
				{
					if(!isset($data['QtypeDetail'][$QtypeInfo['id']]))
					{
						$ResponseDayArr[$Hour]['QtypeDetail'][$QtypeInfo['id']]['ReceiveCount'] = 0;	
					}
					ksort($ResponseDayArr[$Hour]['QtypeDetail']);
				}
			}
			include( TIPASK_ROOT . '/lib/fusion/Includes/FusionCharts_Gen.php');

			# Create Multiseries ColumnD chart object using FusionCharts PHP Class
			$FC = new FusionCharts("StackedColumn2D",'1200','400');

			# Set the relative path of the swf file
			$FC->setSWFPath( '../Charts/');

			# Store chart attributes in a variable
			$strParam="caption='客服响应数据';xAxisName='时间段';baseFontSize=12;decimalPrecision=0;showValues=0;formatNumberScale=0;labelStep=1;numvdivlines=$divideV;rotateNames=0;yAxisMinValue=0;yAxisMaxValue=10;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';pYAxisName=客服;sYAxisName=客服响应数据;hoverCapSepChar=，";

			# Set chart attributes
			$FC->setChartParams($strParam);
			foreach($ResponseDayArr as $Hour => $data)
			{
				$FC->addCategory($Hour.":00");
			}
			foreach($QtypeList as $Key => $QtypeInfo)
			{
				$FC->addDataset($QtypeInfo['name']);
				foreach($ResponseDayArr as $Hour => $data)
				{
					$FC->addChartData($data['QtypeDetail'][$QtypeInfo['id']]['ReceiveCount']);
				}
			}
			$downloadstr = page_url("<下载EXCEL表格>", "admin_data/responseDayDataStacked/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".$ConditionList['DepartmentId']."/".$ConditionList['QtypeId']."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{
			set_time_limit(0);
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName=$ConditionList['Date'] .'客服响应数据';
			$oExcel->download($FileName)->addSheet('客服响应数据');
			//标题栏
			$title = array("时间段");
			ksort($QtypeList);
			foreach($QtypeList as $Key => $QtypeInfo)
			{
				$title[] = $QtypeInfo['name'];
			}
			$oExcel->addRows(array($title));			
			
			$ResponseDayArr = $_ENV['question']->getResponseDay($ConditionList);			
			foreach($ResponseDayArr as $Hour => $HourInfo)
			{
				$excelArr = array("Hour"=>$Hour.":00");
				ksort($HourInfo['QtypeDetail']);
				foreach($QtypeList as $Key => $QtypeInfo)
				{
					$excelArr[$Key] = intval($HourInfo['QtypeDetail'][$Key]['ReceiveCount']);
				}
				$oExcel->addRows(array($excelArr));
			}
			$oExcel->closeSheet()->close();	
		}
		include template('responseDayStacked','admin');        
    }
    /* 
		intoResponseDayData:进入客服响应数据
	*/
    function onpageViewDetail($msg='', $ty='')
	{
		//只查询转为投诉的问题
		$action = "index.php?admin_data/PageViewDetail";
		$hasIntoViewDetailPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoPageViewDetail");
		$hasIntoResponseDayDataPrivilege['url'] = "?admin_main";
		!$hasIntoViewDetailPrivilege['return']  && __msg( $hasIntoViewDetailPrivilege );
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()));
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
		$ConditionList['PageId'] = isset($this->post['PageId'])?intval($this->post['PageId']):(isset($this->get[4])?intval($this->get[4]):0);
		$ConditionList['EndDate'] = min($ConditionList['EndDate'],date("Y-m-t",strtotime($ConditionList['StartDate'])),date("Y-m-d",time()));
		$PageList = $_ENV['view']->GetAllPage();
		$export = trim($this->get[6])=="export"?1:0;
		@$page = max(1, intval($this->get[5]));
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 10;
			$PageViewDetail = $_ENV['view']->getPageViewDetail($ConditionList,$page,$pagesize);
			foreach($PageViewDetail['PageViewList'] as $key => $value)
			{
				$PageViewDetail['PageViewList'][$key]['PageName'] = isset($PageList[$value['PageId']])?$PageList[$value['PageId']]['PageName']:'未知页面';
			}

			$downloadstr = page_url("<下载EXCEL表格>", "admin_data/PageViewDetail/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".$ConditionList['PageId']."/".$page."/export");
			$departstr = page($PageViewDetail['PageViewCount'], $pagesize, $page, "admin_data/PageViewDetail/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".$ConditionList['PageId']);
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
			$FileName='页面浏览记录';
			$oExcel->download($FileName)->addSheet('页面浏览记录');
			//标题栏
			$title = array("页面","浏览IP","浏览时间");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$PageViewDetail = $_ENV['view']->getPageViewDetail($ConditionList,$page,$pagesize);
				foreach($PageViewDetail['PageViewList'] as $key => $value)
				{
					$excelArr = array("PageName"=>isset($PageList[$value['PageId']])?$PageList[$value['PageId']]['PageName']:'未知页面',
					"ViewIP"=>long2ip($value['ViewIP']),
					"ViewTime"=>date('Y-m-d H:i:s',$value['Time'])
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($PageViewDetail['PageViewList']);
			}			 
			$oExcel->closeSheet()->close();	
		}
		include template('PageViewDetail','admin');        
    }
}
?>
