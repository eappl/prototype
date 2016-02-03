<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_countcontrol extends base {

    function admin_countcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("operator");
		$this->load("department");
        $this->load("help");
        $this->load("category");
        $this->load("question");
		$this->load("menu"); 
    }

    function ondefault($message='') {
        $this->onsort();
    }
    
    /* 
	intoGameGroup:进入游戏数据统计页面
	exportGameGroup:导出游戏数据
	游戏数据统计

	*/
    function onGameGroup() 
	{
		$hasIntoGameGroupPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoGameGroup");
		if ( $hasIntoGameGroupPrivilege['return'] )
		{
			set_time_limit(0);
			$all_game=$this->get_all_game();
			foreach($all_game as $k => $v){
				$op_game.='<option value="'.$k.'" id="'.$k.'">'.$v.'</option>';
			}
			$start_time = isset($this->post['start_time'])?strtotime($this->post['start_time']):
			(isset($this->get[2])?$this->get[2]:$_ENV['question']->_getTime(1));
			$end_time = isset($this->post['end_time'])?strtotime("+1 day",strtotime($this->post['end_time']))-1:
			(isset($this->get[3])?$this->get[3]:$_ENV['question']->_getTime(2));
			
			$selGame = isset($this->post['selGame'])?$this->post['selGame']:(isset($this->get[4])?$this->get[4]:-1);
			$join = isset($this->post['join'])?$this->post['join']:(isset($this->get[5])?$this->get[5]:0);
			$order = isset($this->get['6'])?$this->get['6']:-1;  
			if(isset($this->post['submit_search']) || $order !=-1){
				$c_data = $_ENV['question']->GetGameGroup($start_time,$end_time,$selGame,$join,$order);
				$_SESSION['count_GameGroup'] = $c_data;
			}
			include template('gameGroup','admin');
		}
		else
		{
			$hasIntoGameGroupPrivilege['url'] = "?admin_main";
			__msg($hasIntoGameGroupPrivilege);
		
		}
    }
    /* 	游戏数据统计导出 
		exportGameGroup:导出游戏数据权限
	*/
    function onGameGroup_export()
	{
		$hasExportGameGroupPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "exportGameGroup");
		if ( $hasExportGameGroupPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("排名","游戏名称","咨询量","处理量","满意","不满意","未评价");
			array_push($export,$export_header);
			$c_data = $_SESSION['count_GameGroup'];
			$i = 1;
			foreach($c_data as $data){
				$export[] = array($i,$data['game_name'],$data['zxj'],$data['clj'],$data['myj'],$data['bmyj'],$data['wpjj']);
				$i++;
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('count_GameGroup'.date('Ymd'));
		}
		else
		{
			$hasExportGameGroupPrivilege['url'] = "?admin_count/GameGroup";
			__msg($hasExportGameGroupPrivilege);
		
		}
    }
    
    /* 
	intoSort:进入分类数据统计页面
	sortExport:导出分类数据
	分类数据统计 
	*/
    function onsort()
	{    	
		$hasIntoSortPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoSort");
		if ( $hasIntoSortPrivilege['return'] )
		{
			set_time_limit(0);
			$cid_zx = $_ENV['question']->getType(1); //咨询分类id
			$cid_zx = !empty($cid_zx)?intval($cid_zx):-1;
			$start_time = isset($this->post['start_time'])?strtotime($this->post['start_time']):
						   (isset($this->get[2])?$this->get[2]:$_ENV['question']->_getTime(1));
			$end_time = isset($this->post['end_time'])?strtotime("+1 day",strtotime($this->post['end_time']))-1:
						   (isset($this->get[3])?$this->get[3]:$_ENV['question']->_getTime(2));
			$cid = isset($this->post['cid'])?$this->post['cid']:(isset($this->get[4])?$this->get[4]:$cid_zx);
			$cid1 = isset($this->post['cid1'])?$this->post['cid1']:(isset($this->get[5])?$this->get[5]:-1);
			$cid2 = isset($this->post['cid2'])?$this->post['cid2']:(isset($this->get[6])?$this->get[6]:-1);
			$cid3 = isset($this->post['cid3'])?$this->post['cid3']:(isset($this->get[7])?$this->get[7]:-1);
			$cid4 = isset($this->post['cid4'])?$this->post['cid4']:(isset($this->get[8])?$this->get[8]:-1);
			$join = isset($this->post['join'])?$this->post['join']:(isset($this->get[9])?$this->get[9]:0);
			$order = isset($this->get['10'])?$this->get['10']:-1;
			$game_search = isset($this->post['gameid']) && $this->post['gameid']!="0"?
    		trim($this->post['gameid']):(isset($this->get[11])?trim($this->get[11]):"0");
			$all_game = $this->get_all_game();
			if($game_search==0)
			{
				$game_name = "全部";
			}
			elseif($game_search==-1)
			{
				$game_name = "未选择游戏";
			}
			else
			{
				$game_name = substr($all_game[$game_search],2);
			}
			$game_arr = array();
			foreach($all_game as $k => $v){
				   $game_info['Name'] = $v;
				   $game_info['Id'] = $k;			   
				   $game_arr[$v] = $game_info;
			   }
			ksort($game_arr);
			$op_cid = $_ENV['question']->_getCid(0,$cid);  	
			$op_cid1 = $_ENV['question']->_getCid($cid,$cid1);
			$op_cid2 = $_ENV['question']->_getCid($cid1,$cid2);
			$op_cid3 = $_ENV['question']->_getCid($cid2,$cid3);
			$op_cid4 = $_ENV['question']->_getCid($cid3,$cid4);
			if(isset($this->post['submit_search']) || $order !=-1){
				$c_data = $_ENV['question']->GetCategoryCount($start_time,$end_time,$cid,$cid1,$cid2,$cid3,$cid4,$join,$order,$game_search);  	
				foreach($c_data as $key => $value)
				{
					$c_data[$key]['game_name'] = $game_name;
				}
				$_SESSION['count_sort'] = $c_data;
			}
			include template('cousort','admin');      
		}
		else
		{
			$hasIntoSortPrivilege['url'] = "?admin_main";
			__msg($hasIntoSortPrivilege);
		
		}
    }
    
    /*	 分类数据统计导出
		sortExport:导出分类数据权限
	*/
    function onsort_export()
	{
    	
		$hasSortExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "sortExport");
		if ( $hasSortExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("排名","问题分类","游戏名称","咨询量","处理量","满意","不满意","未评价");
			array_push($export,$export_header);
			$c_data = $_SESSION['count_sort'];
			$i = 1;
			foreach($c_data as $data){
				$export[] = array($i,$data['name'],$data['game_name'],$data['i'],$data['j'],$data['k'],$data['m'],$data['n']);
				$i++;
			}  	  	
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('count_sort'.date('Ymd'));
		}
		else
		{
			$hasSortExportPrivilege['url'] = "?admin_count/sort";
			__msg($hasSortExportPrivilege);
		
		}
    }
    
    /* 
	intoDate:进入日期数据统计页面
	dateExport:导出日期数据
	日期数据统计 
	*/
    function ondate()
	{
    	
		$hasIntoDatePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoDate");
		if ( $hasIntoDatePrivilege['return'] )
		{
			$start_time = isset($this->post['start_time'])?strtotime($this->post['start_time']):
			(isset($this->get[2])?$this->get[2]:$_ENV['question']->_getTime(1));
			$end_time = isset($this->post['end_time'])?strtotime("+1 day",strtotime($this->post['end_time']))-1:
			(isset($this->get[3])?$this->get[3]:$_ENV['question']->_getTime(2));
			$cid = isset($this->post['cid'])?$this->post['cid']:(isset($this->get[4])?$this->get[4]:-1);
			$cid1 = isset($this->post['cid1'])?$this->post['cid1']:(isset($this->get[5])?$this->get[5]:-1);
			$cid2 = isset($this->post['cid2'])?$this->post['cid2']:(isset($this->get[6])?$this->get[6]:-1);
			$cid3 = isset($this->post['cid3'])?$this->post['cid3']:(isset($this->get[7])?$this->get[7]:-1);
			$cid4 = isset($this->post['cid4'])?$this->post['cid4']:(isset($this->get[8])?$this->get[8]:-1);
			$join = isset($this->post['join'])?$this->post['join']:(isset($this->get[9])?$this->get[9]:0);
			$order = isset($this->get['10'])?$this->get['10']:-1;
			$operator   = isset($this->post['operator'])?trim($this->post['operator']):(isset($this->get[11])?$this->get[11]:'');
			$operatorList = $_ENV['department']->getOnlineWebOperator($operator);
			$op_cid = $_ENV['question']->_getCid(0,$cid);
			$op_cid1 = $_ENV['question']->_getCid($cid,$cid1);
			$op_cid2 = $_ENV['question']->_getCid($cid1,$cid2);
			$op_cid3 = $_ENV['question']->_getCid($cid2,$cid3);
			$op_cid4 = $_ENV['question']->_getCid($cid3,$cid4);
			if(isset($this->post['submit_search'])|| $order !=-1){
				$d_data = $_ENV['question']->GetDateCount($start_time,$end_time,$cid,$cid1,$cid2,$cid3,$cid4,$join,$order,$operatorList,$this->setting['assessOverTimeLimit']);
				$_SESSION['count_date'] = $d_data;
			}
			include template('date','admin');
		}
		else
		{
			$hasIntoDatePrivilege['url'] = "?admin_main";
			__msg($hasIntoDatePrivilege);
		
		}

    }
    
    /* 日期数据统计导出 
	   dateExport:导出日期数据权限
	*/
    function ondate_export()
	{
    	
		$hasDateExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "dateExport");
		if ( $hasDateExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("排名","日期","咨询量","回复量","处理量","处理率","满意","逾期满意","满意率","不满意","逾期不满意","不满意率","未评价","评价率");
			array_push($export,$export_header);
			$d_data = $_SESSION['count_date'];
			$i = 1;
			foreach($d_data['detail'] as $data){
				$export[] = array($i,$data['date'],$data['zxj'],$data['hfj'],$data['clj'],$data['cl_rate'],$data['myj'],$data['yqmyj'],$data['my_rate'],$data['bmyj'],$data['yqbmyj'],$data['bmy_rate'],$data['wpjj'],$data['pj_rate']);
				$i++;
			}
			$export[] = array('总计：','',$d_data['total']['zxj'],$d_data['total']['hfj'],$d_data['total']['clj'],$d_data['total']['cl_rate'],$d_data['total']['myj'],$d_data['total']['yqmyj'],$d_data['total']['my_rate'],$d_data['total']['bmyj'],$d_data['total']['yqbmyj'],$d_data['total']['bmy_rate'],$d_data['total']['wpjj'],$d_data['total']['pj_rate']);
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('count_date'.date('Ymd'));
		}
		else
		{
			$hasDateExportPrivilege['url'] = "?admin_count/date";
			__msg($hasDateExportPrivilege);
		
		}
    }
    
    /* 
	intoKeyWord:进入客服数据统计页面
	keywordExport:导出客服统计数据
	客服数据统计 
	
	*/
    function onkeyword() 
	{
    	
		$backReturn = array();
		$hasIntoKeyWordPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoKeyWord");
		if ( $hasIntoKeyWordPrivilege['return'] )
		{
			$start_time = isset($this->post['start_time'])?strtotime($this->post['start_time']):
			(isset($this->get[2])?$this->get[2]:$_ENV['question']->_getTime(1));
			$end_time = isset($this->post['end_time'])?strtotime("+1 day",strtotime($this->post['end_time']))-1:
			(isset($this->get[3])?$this->get[3]:$_ENV['question']->_getTime(2));
			
			$cid = isset($this->post['cid'])?$this->post['cid']:(isset($this->get[4])?$this->get[4]:-1);
			$cid1 = isset($this->post['cid1'])?$this->post['cid1']:(isset($this->get[5])?$this->get[5]:-1);
			$cid2 = isset($this->post['cid2'])?$this->post['cid2']:(isset($this->get[6])?$this->get[6]:-1);
			$cid3 = isset($this->post['cid3'])?$this->post['cid3']:(isset($this->get[7])?$this->get[7]:-1);
			$cid4 = isset($this->post['cid4'])?$this->post['cid4']:(isset($this->get[8])?$this->get[8]:-1);
			$operator = isset($this->post['operator'])?$this->post['operator']:(isset($this->get[9])?urldecode($this->get[9]):'');
			$order = isset($this->get['10'])?$this->get['10']:-1;
			$join = isset($this->post['join'])?$this->post['join']:(isset($this->get[11])?$this->get[11]:0);
			
			$op_cid = $_ENV['question']->_getCid(0,$cid);
			$op_cid1 = $_ENV['question']->_getCid($cid,$cid1);
			$op_cid2 = $_ENV['question']->_getCid($cid1,$cid2);
			$op_cid3 = $_ENV['question']->_getCid($cid2,$cid3);
			$op_cid4 = $_ENV['question']->_getCid($cid3,$cid4);
			$operatorList = $_ENV['department']->getOnlineWebOperator($operator);
			if(isset($this->post['submit_search'])|| $order !=-1){
				$k_data = $_ENV['question']->GetKeywordCount($start_time,$end_time,$cid,$cid1,$cid2,$cid3,$cid4,$operatorList,$order,$join,$this->setting['assessOverTimeLimit']);
				$_SESSION['count_keyword'] = $k_data;
			}
			include template('keyword','admin');
		}
		else
		{
			$hasIntoKeyWordPrivilege['url'] = "?admin_main";
			__msg($hasIntoKeyWordPrivilege);
		
		}

    }
    
    /* 客服数据统计导出
	   keywordExport:导出客服统计数据权限
	*/
    function onkeyword_export()
	{
    	
		$hasKeywordExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "keywordExport");
		if ( $hasKeywordExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("排名","操作员","进单量","回复量","处理量","处理率","协助量","满意","逾期满意","满意率","不满意","逾期不满意","不满意率","未评价","评价率");
			array_push($export,$export_header);
			$k_data = $_SESSION['count_keyword'];
			$i = 1;
			foreach($k_data['detail'] as $data){
				$export[] = array($i,$data['js_kf'],$data['zxj'],$data['hfj'],$data['clj'],$data['cl_rate'],$data['AidCount'],$data['myj'],$data['yqmyj'],$data['my_rate'],$data['bmyj'],$data['yqbmyj'],$data['bmy_rate'],$data['wpjj'],$data['pj_rate']);
				$i++;
			}
			$export[] = array('总计：','',$k_data['total']['zxj'],$k_data['total']['hfj'],$k_data['total']['clj'],$k_data['total']['cl_rate'],$k_data['total']['AidCount'],$k_data['total']['myj'],$k_data['total']['yqmyj'],$k_data['total']['my_rate'],$k_data['total']['bmyj'],$k_data['total']['yqbmyj'],$k_data['total']['bmy_rate'],$k_data['total']['wpjj'],$k_data['total']['pj_rate']);
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('count_keyword'.date('Ymd'));
		}
		else
		{
			$hasKeywordExportPrivilege['url'] = "?admin_count/keyword";
			__msg($hasKeywordExportPrivilege);
		
		}
    }
    
    /* 回复时长统计 
		intoSorce:进入回复时长统计页面
		sorceExport:导出回复时长数据
	*/
    function onsorce()
	{
    	
		$hasIntoSorcePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoSorce");
		if ( $hasIntoSorcePrivilege['return'] )
		{
			$start_time = isset($this->post['start_time']) && $this->post['start_time']!=''?strtotime($this->post['start_time']):$_ENV['question']->_getTime(1);
			$end_time = isset($this->post['end_time']) && $this->post['end_time']!=''?strtotime("+1 day",strtotime($this->post['end_time']))-1:$_ENV['question']->_getTime(2);
			$operator = isset($this->post['operator']) ? trim($this->post['operator']) : '';
			// 获取在线部所有人
			$cid  = isset($this->post['cid'])?$this->post['cid']:-1;
			$cid1 = isset($this->post['cid1'])?$this->post['cid1']:-1;
			$cid2 = isset($this->post['cid2'])?$this->post['cid2']:-1;
			$cid3 = isset($this->post['cid3'])?$this->post['cid3']:-1;
			$cid4 = isset($this->post['cid4'])?$this->post['cid4']:-1;
			$op_cid  = $_ENV['question']->_getCid(0,$cid);
			$op_cid1 = $_ENV['question']->_getCid($cid,$cid1);
			$op_cid2 = $_ENV['question']->_getCid($cid1,$cid2);
			$op_cid3 = $_ENV['question']->_getCid($cid2,$cid3);
			$op_cid4 = $_ENV['question']->_getCid($cid3,$cid4);
			$operatorList = $_ENV['department']->getOnlineWebOperator($operator);
			if(isset($this->post['submit_search'])){ 
				$sorce_data = $_ENV['help']->get_sorceList($start_time,$end_time,$operatorList,$cid,$cid1,$cid2,$cid3,$cid4);
				$_SESSION['sorce_data'] = $sorce_data;
			}
		   include template('sorce','admin');
		}
		else
		{
			$hasIntoSorcePrivilege['url'] = "?admin_main";
			__msg($hasIntoSorcePrivilege);
		
		}
    }
    /* 导出回复时长
		sorceExport:导出回复时长数据权限
	*/
    function onsorce_export()
	{
    	
		$hasSorceExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "sorceExport");
		if ( $hasSorceExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("操作员","回复量","平均回复时长","0-60秒数量","比率","61-120秒数量","比率","121-180秒数量","比率","181-240秒数量",
					"比率","241-300秒数量","比率","301-600秒数量","比率","600秒以上数量","比率",);
			array_push($export,$export_header);
			$export_arr = $_SESSION['sorce_data'];
			foreach($export_arr as $val){
				$export[]=array($val['author'],$val['reply_count'],$val['avg_time'],$val['one'],$val['one_rate'],$val['two'],
					 $val['two_rate'],$val['three'],$val['three_rate'],$val['four'],$val['four_rate'],$val['five'],
					 $val['five_rate'],$val['six'],$val['six_rate'],$val['seven'],$val['seven_rate']);
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('sorce_data'.date('Ymd'));
		}
		else
		{
			$hasSorceExportPrivilege['url'] = "?admin_count/sorce";
			__msg($hasSorceExportPrivilege);
		
		}
    }
    
    /*
	intoResponse:进入响应时长统计页面
	responseExport:导出响应时长数据
	响应时长统计 
	*/
    function onresponse() 
	{
    	
		$hasIntoResponsePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoResponse");
		if ( $hasIntoResponsePrivilege['return'] )
		{
			$start_time = isset($this->post['start_time'])&&$this->post['start_time'] != ''?strtotime($this->post['start_time']):$_ENV['question']->_getTime(1);
			$end_time = isset($this->post['end_time'])&&$this->post['end_time'] != '' ?strtotime("+1 day",strtotime($this->post['end_time']))-1:$_ENV['question']->_getTime(2);
			$operator = isset($this->post['operator']) ? trim($this->post['operator']) : '';
			$cid  = isset($this->post['cid'])?$this->post['cid']:-1;
			$cid1 = isset($this->post['cid1'])?$this->post['cid1']:-1;
			$cid2 = isset($this->post['cid2'])?$this->post['cid2']:-1;
			$cid3 = isset($this->post['cid3'])?$this->post['cid3']:-1;
			$cid4 = isset($this->post['cid4'])?$this->post['cid4']:-1;
			$op_cid = $_ENV['question']->_getCid(0,$cid);
			$op_cid1 = $_ENV['question']->_getCid($cid,$cid1);
			$op_cid2 = $_ENV['question']->_getCid($cid1,$cid2);
			$op_cid3 = $_ENV['question']->_getCid($cid2,$cid3);
			$op_cid4 = $_ENV['question']->_getCid($cid3,$cid4);
			$operatorList = $_ENV['department']->getOnlineWebOperator($operator);
			if(isset($this->post['submit_search'])){
				$response_data = $_ENV['help']->get_rs_list($start_time,$end_time,$operatorList,$cid,$cid1,$cid2,$cid3,$cid4);
				$_SESSION['response_data'] = $response_data;
			}
			include template('response','admin');
		}
		else
		{
			$hasIntoResponsePrivilege['url'] = "?admin_main";
			__msg($hasIntoResponsePrivilege);
		
		}
    }
    /* 导出响应时长统计 
		responseExport:导出响应时长数据权限
	*/
    function onresponse_export() 
	{
    	
		$hasResponseExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "responseExport");
		if ( $hasResponseExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("操作员","回复量","平均响应时长","0-60秒数量","比率","61-120秒数量","比率","121-180秒数量","比率","181-240秒数量",
			"比率","241-300秒数量","比率","301-600秒数量","比率","600秒以上数量","比率",);
			array_push($export,$export_header);
			$export_arr = $_SESSION['response_data'];
			foreach($export_arr as $val){
				$export[]=array($val['author'],$val['reply_count'],$val['avg_time'],$val['one'],$val['one_rate'],$val['two'],
				$val['two_rate'],$val['three'],$val['three_rate'],$val['four'],$val['four_rate'],$val['five'],
				$val['five_rate'],$val['six'],$val['six_rate'],$val['seven'],$val['seven_rate']);
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('response_data'.date('Ymd'));
		}
		else
		{
			$hasResponseExportPrivilege['url'] = "?admin_count/response";
			__msg($hasResponseExportPrivilege);
		
		}
    }
    /* 协助数据统计 
		intoHelp:进入协助数据统计页面
		helpExport:导出协助数据
	*/
    function onhelp()
	{
    	
		$hasIntoHelpPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoHelp");
		if ( $hasIntoHelpPrivilege['return'] )
		{
			$start      = isset($this->post['start_time']) && $this->post['start_time']!='' ? strtotime(($this->post['start_time'])) :
			(isset($this->get[2])?intval($this->get[2]) : $_ENV['question']->_getTime(1));
			$end_time   = isset($this->post['end_time']) && $this->post['end_time']!='' ? strtotime("+1 day",strtotime($this->post['end_time']))-1:
			(isset($this->get[3])?intval($this->get[3]): $_ENV['question']->_getTime(2));
			$operator   = isset($this->post['operator'])?trim($this->post['operator']):(isset($this->get[4])?$this->get[4]:'');
			$did        =  isset($this->post['department'])?intval($this->post['department']):(isset($this->get[5])?$this->get[5]:-1);
			$dpt_select =  $_ENV['department']->get_categrory_tree($did,"where name !='在线服务部'");
			$orderby    = isset($this->get[6])?intval($this->get[6]):-1;
			$count = 1;
			if(isset($this->post['submit_search']) || $orderby !=-1){
				$where = $_ENV['help']->get_kwhere($start,$end_time,$operator,$did,$orderby,$web_did);
				$keyword_list = $_ENV['help']->get_klist($where);
				$_SESSION['keyword_list']  = $keyword_list;
			}
			include template('couhelp','admin');
		}
		else
		{
			$hasIntoHelpPrivilege['url'] = "?admin_main";
			__msg($hasIntoHelpPrivilege);
		
		}
    
    }
    /*
		导出协助数据统计 
		helpExport:导出协助数据
	*/
    function onhelp_export()
	{
    	
		$hasHelpExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpExport");
		if ( $hasHelpExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("排名","操作员","接手协助量","回复量","处理量","处理率","满意","满意率","不满意","不满意率","未评价","评价率");
			array_push($export,$export_header);
			$export_arr = $_SESSION['keyword_list'];
			$count = 0;
			foreach($export_arr as $val){
				$count++;
				$export[]=array($count,$val['aid'],$val['jx_count'],$val['replay'],$val['handle'],$val['handle_rate'],$val['pj_my'],
				$val['satify_rate'],$val['pj_bmy'],$val['nsatify_rate'],$val['wpj'],$val['assess_rate']);
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('help_data'.date('Ymd'));
		}
		else
		{
			$hasHelpExportPrivilege['url'] = "?admin_count/help";
			__msg($hasHelpExportPrivilege);
		
		}
    }
    /*
	intoHelpTime:进入协助响应时长统计页面
	helpTimeExport:导出协助响应时长
	协助响应时长统计 
	*/
    function onhelpTime() 
	{
    	
		$hasIntoHelpTimePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoHelpTime");
		if ( $hasIntoHelpTimePrivilege['return'] )
		{
			$start = isset($this->post['start_time']) && $this->post['start_time']!='' ? strtotime($this->post['start_time']) : $_ENV['question']->_getTime(1);
			$end_time = isset($this->post['end_time']) && $this->post['end_time']!=''?strtotime("+1 day",strtotime($this->post['end_time']))-1:$_ENV['question']->_getTime(2);
			$operator = isset($this->post['operator'])?trim($this->post['operator']):'';
			$did = isset($this->post['department'])?intval($this->post['department']):-1;
			$dpt_select =  $_ENV['department']->get_categrory_tree($did,"where name !='在线服务部'");
			if(isset($this->post['submit_search'])){
				// 获取在线服务部及其子id
				$web_did = $_ENV['department']->getOnlineWebId();
				$where = $_ENV['help']->get_swhere($start,$end_time,$operator,$did,$web_did);
				$sorce_list = $_ENV['help']->get_slist($where);
				$_SESSION['sorce_list'] = $sorce_list;
			}
			include template('couhelptime','admin');
		}
		else
		{
			$hasIntoHelpTimePrivilege['url'] = "?admin_main";
			__msg($hasIntoHelpTimePrivilege);
		
		}
    }
    /* 导出协助相应时长统计
		helpTimeExport:导出协助响应时长权限
	*/
    function onhelpTime_export()
	{
    	
		$hashelpTimeExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpTimeExport");
		if ( $hashelpTimeExportPrivilege['return'] )
		{
			@require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("操作员","回复量","平均响应时长","0-10分钟数量","比率","11-20分钟数量","比率","21-30分钟数量","比率","30分钟以上数量","比率");
			array_push($export,$export_header);
			$export_arr = $_SESSION['sorce_list'];
			foreach($export_arr as $val){
				$export[]=array($val['aid'],$val['reply'],$val['avg_time'],$val['ten_reply'],$val['ten_rate'],$val['twenty_reply'],$val['twenty_rate'],
				$val['thirty_reply'],$val['thirty_rate'],$val['more_reply'],$val['more_rate']);
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('ht_data'.date('Ymd'));
		}
		else
		{
			$hashelpTimeExportPrivilege['url'] = "?admin_count/helpTime";
			__msg($hashelpTimeExportPrivilege);
		
		}
    }
    function onajaxGetNextCid(){
    	$category_str = '';
    	$cid = $this->post['cid'];
    	$categorylist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "category WHERE pid='$cid'","id");
    	foreach($categorylist as $val){
    		$category_str.='<option value="'.$val['id'].'">'.$val['name'].'</option>';
    	}
    	exit($category_str);
    }
}
?>
