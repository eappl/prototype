<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_historycontrol extends base {
	function admin_historycontrol(& $get,& $post) {
		$this->base( & $get,& $post);
		$this->db_h = new db(DB_HOST_H, DB_USER_H, DB_PW_H, DB_NAME_H, DB_CHARSET_H, DB_CONNECT_H);
		$this->load("question_h");
		$this->load("category");
		$this->load("menu"); 
	}

	function ondefault($message='') {
		$this->onviewHistory();
	}
	
	/* 
	intoViewHistory:进入查看历史问题页面
	viewHistoryExport:导出历史问题数据
	历史数据查询 */
	function onviewHistory($msg='', $ty='')
	{
						
		$hasintoViewHistoryPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoViewHistory");
		$hasintoViewHistoryPrivilege['url'] = "?admin_main";
		!$hasintoViewHistoryPrivilege['return']  && __msg($hasintoViewHistoryPrivilege);

		$ask_start_time_search = isset($this->post['ask_start_time']) && false != strtotime($this->post['ask_start_time'])?
    	 	strtotime($this->post['ask_start_time']):(isset($this->get[2])?$this->get[2]:'');
    	
    	$ask_end_time_search = isset($this->post['ask_end_time']) && false != strtotime($this->post['ask_end_time'])?
    		strtotime('+1 day',strtotime($this->post['ask_end_time']))-1:(isset($this->get[3])?$this->get[3]:'');
    	
    	$wait_start_time_search = isset($this->post['wait_start_time']) && $this->post['wait_start_time']!=''?
    	    intval($this->post['wait_start_time']):(isset($this->get[4])?$this->get[4]:'');
    	
    	$wait_end_time_search = isset($this->post['wait_end_time']) && $this->post['wait_end_time']!=''?
    		intval($this->post['wait_end_time']):(isset($this->get[5])?$this->get[5]:'');
    	
    	$answer_start_time_search = isset($this->post['answer_start_time']) && false != strtotime($this->post['answer_start_time'])?
       	    strtotime($this->post['answer_start_time']):(isset($this->get[6])?$this->get[6]:'');
    	
    	$answer_end_time_search = isset($this->post['answer_end_time']) && false != strtotime($this->post['answer_end_time'])?
    		strtotime('+1 day',strtotime($this->post['answer_end_time']))-1:(isset($this->get[7])?$this->get[7]:'');
    	
    	$question_start_time_search = isset($this->post['question_start_time']) && $this->post['question_start_time']!=''?
    		intval($this->post['question_start_time']):(isset($this->get[8])?$this->get[8]:'');
    	
    	$question_end_time_search = isset($this->post['question_end_time']) && $this->post['question_end_time']!=''?
    		intval($this->post['question_end_time']):(isset($this->get[9])?$this->get[9]:'');
    	
    	$revocation_search = isset($this->post['revocation']) && $this->post['revocation']!=-1?
    		intval($this->post['revocation']):(isset($this->get[10])?intval($this->get[10]):-1);
    	
    	$que_status_search = isset($this->post['que_status']) && $this->post['que_status']!=-1?
    		intval($this->post['que_status']):(isset($this->get[11])?intval($this->get[11]):-1);
    	
    	$question_search = isset($this->post['question']) && $this->post['question']!=-1?
    		intval($this->post['question']):(isset($this->get[12])?intval($this->get[12]):-1);
    	
    	$assess_search = isset($this->post['assess']) && $this->post['assess']!=-1?
    		intval($this->post['assess']):(isset($this->get[13])?intval($this->get[13]):-1);
    	
    	$qid_search = isset($this->post['qid']) && $this->post['qid']!=''?intval($this->post['qid']):(isset($this->get[14])?
    		$this->get[14]:'');
    	
    	$operator_search = isset($this->post['operator']) && $this->post['operator']!=''?
    		$this->post['operator']:(isset($this->get[15])?urldecode($this->get[15]):'');
    	
    	$user_name_search = isset($this->post['user_name']) && $this->post['user_name']!=''?
    		$this->post['user_name']:(isset($this->get[16])?urldecode($this->get[16]):'');
    	
    	$question_title_search = isset($this->post['question_title']) && $this->post['question_title']!=''?
    		$this->post['question_title']:(isset($this->get[17])?urldecode($this->get[17]):'');
    	 	
    	$display_method = isset($this->get[18])?intval($this->get[18]):1;
    	
    	$category_search = isset($this->post['category']) && $this->post['category']!=-1?
    		intval($this->post['category']):(isset($this->get[19])?intval($this->get[19]):-1);
    	
    	$order_search = isset($this->post['order'])?intval($this->post['order']):(isset($this->get[20])?intval($this->get[20]):0);
    	
    	$help_search=isset($this->post['help_status']) && $this->post['help_status']!=-1?
    		intval($this->post['help_status']):(isset($this->get[21])?intval($this->get[21]):-1);
    	
    	$all_kf = isset($this->post['all_kf'])?$this->post['all_kf']:(isset($this->get[22])?trim($this->get[22]):0);
				
		$history_year = isset($this->post['history_year'])?$this->post['history_year']:(isset($this->get[23])?trim($this->get[23]):date('Y'));
		
		$r_site = isset($this->post['r_site']) && $this->post['r_site']!=-1?
		    intval($this->post['r_site']):(isset($this->get[24])?intval($this->get[24]):-1);

		if(isset($this->post['history_year']) || isset($this->get[22])){//默认不搜索
			$where = $_ENV['question_h']->Get_Where_H($ask_start_time_search,$ask_end_time_search,$wait_start_time_search,$wait_end_time_search,$answer_start_time_search
					,$answer_end_time_search,$question_start_time_search,$question_end_time_search,$revocation_search,$que_status_search,$question_search,$assess_search,
					$qid_search,$operator_search,$user_name_search,$question_title_search,$display_method,$category_search,$order_search,$help_search,$all_kf,$r_site);
			 
			@$page = max(1, intval($this->get[25]));
			$pagesize = $this->setting['list_default'];
			$startindex = ($page - 1) * $pagesize;
			$rownum = $_ENV['question_h']->Get_Num_H($where,$all_kf,$history_year);
			if($rownum == -1){
				$msg && $message = $msg;
				$ty && $type = $ty;
				if($message == ''){
					$this->onviewHistory('不存在此历史问题表，请创建此历史表！',"errormsg");
					exit;
				}							
			}
			$question_list = $_ENV['question_h']->Get_All_Question_H($where,true,$all_kf,$history_year,$startindex, $pagesize);
			$departstr = page($rownum, $pagesize, $page, "admin_history/onviewHistory/$ask_start_time_search/$ask_end_time_search/$wait_start_time_search/$wait_end_time_search" .
					"/$answer_start_time_search/$answer_end_time_search/$question_start_time_search/$question_end_time_search/$revocation_search/$que_status_search/$question_search" .
					"/$assess_search/$qid_search/$operator_search/$user_name_search/$question_title_search/$display_method/$category_search" .
					"/$order_search/$help_search/$all_kf/$history_year/$r_site");
			$_his = array();
			$_his['ask_start_time_search']=$ask_start_time_search;
			$_his['ask_end_time_search']=$ask_end_time_search;
			$_his['wait_start_time_search']=$wait_start_time_search;
			$_his['wait_end_time_search']=$wait_end_time_search;
			$_his['answer_start_time_search']=$answer_start_time_search;
			$_his['answer_end_time_search']=$answer_end_time_search;
			$_his['question_start_time_search']=$question_start_time_search;
			$_his['question_end_time_search']=$question_end_time_search;
			$_his['revocation_search']=$revocation_search;
			$_his['que_status_search']=$que_status_search;
			$_his['question_search']=$question_search;
			$_his['assess_search']=$assess_search;
			$_his['qid_search']=$qid_search;
			$_his['operator_search']=$operator_search;
			$_his['user_name_search']=$user_name_search;
			$_his['question_title_search']=$question_title_search;
			$_his['display_method']=$display_method;
			$_his['category_search']=$category_search;
			$_his['order_search']=$order_search;
			$_his['help_search']=$help_search;
			$_his['all_kf_search']=$all_kf;
			$_his['history_year']=$history_year;
			$_his['r_site_search']=$r_site;
			$_his['num']=$rownum;
			$_SESSION['his_session'] = $_his;
		}
		$question_status = $this->ask_config->getQuestion();
		$que_status = $this->ask_config->getQueStatus();
		$revocation_status = $this->ask_config->getRevocation();
		$assess_status = $this->ask_config->getAssess();
		$help_status = $this->ask_config->getHelpStatus();
		$category_option = $_ENV['category']->get_categrory_tree($category_search);
		
		$msg && $message = $msg;
		$ty && $type = $ty;
		
		include template('his_view','admin');
	}
	
	//导出历史问题
	function onviewHistory_export()
	{
		
		$hasviewHistoryExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "viewHistoryExport");
		$hasviewHistoryExportPrivilege['url'] = "?admin_history/viewHistory";
		!$hasviewHistoryExportPrivilege['return']  && __msg($hasviewHistoryExportPrivilege);
		
		if($_SESSION['his_session']['num'] >= 10000){
			$this->onviewHistory('数据量太大，请重新筛选条件后进行导出！',"errormsg");
			exit;
		}
		require TIPASK_ROOT . '/lib/php_excel.class.php';
		$export = array();
		$export_header = array("问题ID","5173帐号","问题标题","问题描述","问题分类","提问时间","回答客服","接手时间","回答时间","回复时长","处理状态","协助处理","评价状态","浏览量","问题的状态","来源站点","游戏名称");
		array_push($export,$export_header);
		$cat = $_ENV['category']->getNameById();
		$_his = $_SESSION['his_session'];
		$where = $_ENV['question_h']->Get_Where_H($_his['ask_start_time_search'],$_his['ask_end_time_search'],$_his['wait_start_time_search'],
				$_his['wait_end_time_search'],$_his['answer_start_time_search'],$_his['answer_end_time_search'],$_his['question_start_time_search'],
				$_his['question_end_time_search'],$_his['revocation_search'],$_his['que_status_search'],$_his['question_search'],$_his['assess_search'],
				$_his['qid_search'],$_his['operator_search'],$_his['user_name_search'],$_his['question_title_search'],
				$_his['display_method'],$_his['category_search'],$_his['order_search'],$_his['help_search'],$_his['all_kf_search'],$_his['r_site_search']);
		$export_arr = $_ENV['question_h']->Get_All_Question_H($where,false,$_his['all_kf_search'],$_his['history_year']);	
		foreach($export_arr as $val){
			if($val['is_pj']==0){
				$asses = "未评价";
			}elseif($val['is_pj']==1){
				$asses = "满意";
			}else{
				$asses = "不满意";
			}
	
			if($val['status']==1){
				$q_status = "等待处理";
			}elseif($val['status']==2){
				$q_status = "等待评价";
			}else{
				$q_status = "已结束";
			}
			if($val[q_handle_status]==0){
				$mange_status = '未处理';
			}else{
				$mange_status = '已处理';
			}
			if($val[help_status]==0){
				$help_status = '未协助';
			}else{
				$help_status = '协助处理';
			}
			if($val['r_site']==1){
				$r_site = '寄售';
			}elseif($val['r_site']==2){
				$r_site = '担保';
			}elseif($val['r_site']==3){
				$r_site = '账号';
			}else{
				$r_site = '';
			}
			$q_cat = '';
			$val['cid'] && $q_cat.='-'.$cat[$val['cid']].'-';
			$val['cid1'] && $q_cat.='-'.$cat[$val['cid1']].'-';
			$val['cid2'] && $q_cat.='-'.$cat[$val['cid2']].'-';
			$val['cid3'] && $q_cat.='-'.$cat[$val['cid3']].'-';
			$val['cid4'] && $q_cat.='-'.$cat[$val['cid4']].'-';
			$replay_range = getHour($val['Atime']-$val['receive_time']);
			$val['receive_time'] = empty($val['receive_time']) ? '' : date("Y-m-d H:i:s",$val['receive_time']);
			$val['Atime'] = empty( $val['Atime']) ? '' : date("Y-m-d H:i:s", $val['Atime']);
			$export[]=array($val['id'],$val['author'],$val['title'],$val['description'],$q_cat,date("Y-m-d H:i:s",$val['time']),
					$val['Aauthor'],$val['receive_time'], $val['Atime'],$replay_range,$mange_status,$help_status,
					$asses,$val['views'],$q_status,$r_site,$val['game_name']);
		}
	
		$xls = new Excel_XML('UTF-8', false, 'My Sheet');
		$xls->addArray($export);
		$xls->generateXML('question_h'.date('Ymd'));
	}
	
	//根据问题ID查看历史问题页面
	function onview_his_by_id(){
		if(isset($this->get[2])){
			$question_id = $this->get[2];
			$cat = $_ENV['category']->getNameById();
			if($question_id){
				$q_tree = $_ENV['question_h']->get_question_tree_H($question_id,$_SESSION['his_session']['history_year']);
				if(!empty($q_tree)){
					$where = " WHERE q.id IN (".implode(",",$q_tree).") ORDER BY q.time,a.time" ;
					$q_list = $_ENV['question_h']->Get_Question_List_H($where,$_SESSION['his_session']['history_year']);
				}
			}
			$assess_status = $this->ask_config->getAssess();
		}
		include template('view_his_by_id','admin');
	}

}
?>