<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_questioncontrol extends base {
    function admin_questioncontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("job");
        $this->load("category");
        $this->load("question");
        $this->load("quick_cat");
        $this->load("quick_content");
        $this->load("operator");
        $this->load("help");
        $this->load("tag");
        $this->load("department");
        $this->load("answer");
		$this->load("menu"); 
		$this->load("qtype");
		$this->load("complain");
		$this->load("help");
    }

    function ondefault($message='') {
        $this->onview();
    }
    /* 
		intoView:进入查看全部问题页面
		viewExport:导出全部问题数据
		viewHandleQuestion:客服主动接手问题权限  
		查看全部问题（已修改） 
	*/
    function onview($msg='', $ty='')
	{
		$hasIntoViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoView");
		$hasAcceptQuestionPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "viewHandleQuestion");
		$hasIntoViewPrivilege['url'] = "?admin_main";
		!$hasIntoViewPrivilege['return']  && __msg( $hasIntoViewPrivilege );
		
		$ask_login_name = $this->ask_login_name;

    	$ask_start_time_search = isset($this->post['ask_start_time']) && false != strtotime($this->post['ask_start_time'])?
    	 	strtotime($this->post['ask_start_time']):(isset($this->get[2])?$this->get[2]:$_ENV['question']->_getSETime(1));   	
		
    	$ask_end_time_search = isset($this->post['ask_end_time']) && false != strtotime($this->post['ask_end_time'])?
    		strtotime('+1 day',strtotime($this->post['ask_end_time']))-1:(isset($this->get[3])?$this->get[3]:$_ENV['question']->_getSETime(2));
    	
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
    	
    	//$category_search = isset($this->post['category']) && $this->post['category']!=-1?
    	//	intval($this->post['category']):(isset($this->get[19])?intval($this->get[19]):-1);
    	
    	$order_search = isset($this->post['order'])?intval($this->post['order']):(isset($this->get[20])?intval($this->get[20]):0);
    	
    	$help_search = isset($this->post['help_status']) && $this->post['help_status']!=-1?
    		intval($this->post['help_status']):(isset($this->get[21])?intval($this->get[21]):-1);
    	
    	$all_kf = isset($this->post['all_kf'])?$this->post['all_kf']:(isset($this->get[22])?trim($this->get[22]):0);
    	
    	$r_site = isset($this->post['r_site']) && $this->post['r_site']!=-1?
    	    intval($this->post['r_site']):(isset($this->get[23])?intval($this->get[23]):-1);
    	
    	$cid = isset($this->post['cid']) && $this->post['cid']!=-1?
    		intval($this->post['cid']):(isset($this->get[24])?intval($this->get[24]):-1);
    	
    	$cid1 = isset($this->post['cid1']) && $this->post['cid1']!=-1?
    		intval($this->post['cid1']):(isset($this->get[25])?intval($this->get[25]):-1);
    	 
    	$cid2 = isset($this->post['cid2']) && $this->post['cid2']!=-1?
    		intval($this->post['cid2']):(isset($this->get[26])?intval($this->get[26]):-1);
    	 
    	$cid3 = isset($this->post['cid3']) && $this->post['cid3']!=-1?
    		intval($this->post['cid3']):(isset($this->get[27])?intval($this->get[27]):-1);
    	
    	$cid4 = isset($this->post['cid4']) && $this->post['cid4']!=-1?
    		intval($this->post['cid4']):(isset($this->get[28])?intval($this->get[28]):-1);
		$Comm_status_List = $this->ask_config->getCommStatus();
    	$Comm_status = isset($this->post['Comm_status']) && $this->post['Comm_status']!=-1?
    		intval($this->post['Comm_status']):(isset($this->get[29])?intval($this->get[29]):-1);
    	$game_search = isset($this->post['gameid']) && $this->post['gameid']!=0?
    		trim($this->post['gameid']):(isset($this->get[30])?trim($this->get[30]):"0");
    	$mobile_search = isset($this->post['mobile']) && $this->post['mobile']!=''?
    		trim($this->post['mobile']):(isset($this->get[31])?trim($this->get[31]):'');
		$include_answer = isset($this->post['include_answer']) && $this->post['include_answer']!=0?
    		intval($this->post['include_answer']):(isset($this->get[32])?intval($this->get[32]):'');
		$where = $_ENV['question']->Get_Where($ask_start_time_search,$ask_end_time_search,$wait_start_time_search,$wait_end_time_search,$answer_start_time_search
    	,$answer_end_time_search,$question_start_time_search,$question_end_time_search,$revocation_search,$que_status_search,$question_search,$assess_search,
    	$qid_search,$operator_search,$user_name_search,$question_title_search,$display_method,"",$order_search,$help_search,$all_kf,$r_site,$cid,$cid1,$cid2,$cid3,$cid4,$Comm_status,$game_search,$mobile_search);
        @$page = max(1, intval($this->get[33]));   
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;  
        $rownum = $_ENV['question']->Get_Num($where,$all_kf); 
		$question_list = $_ENV['question']->Get_All_Question($where,true,$all_kf,$startindex, $pagesize);
		foreach($question_list as $key => $value)
		{
		    $question_list[$key]['description'] = cutstr($value['description'],30);    
		}
		$all_game = $this->get_all_game();
		$game_arr = array();
		foreach($all_game as $k => $v){
			   $game_info['Name'] = $v;
			   $game_info['Id'] = $k;			   
			   $game_arr[$v] = $game_info;
		   }
		ksort($game_arr);
        $departstr = page($rownum, $pagesize, $page, "admin_question/view/$ask_start_time_search/$ask_end_time_search/$wait_start_time_search/$wait_end_time_search" .
        		"/$answer_start_time_search/$answer_end_time_search/$question_start_time_search/$question_end_time_search/$revocation_search/$que_status_search/$question_search" .
        		"/$assess_search/$qid_search/$operator_search/$user_name_search/$question_title_search/$display_method/" .
        		"/$order_search/$help_search/$all_kf/$r_site/$cid/$cid1/$cid2/$cid3/$cid4/$Comm_status/$game_search/$mobile_search/$include_answer");
        $_all = array();
        $_all['ask_start_time_search']=$ask_start_time_search;
        $_all['ask_end_time_search']=$ask_end_time_search;
        $_all['wait_start_time_search']=$wait_start_time_search;
        $_all['wait_end_time_search']=$wait_end_time_search;
        $_all['answer_start_time_search']=$answer_start_time_search;
        $_all['answer_end_time_search']=$answer_end_time_search;
        $_all['question_start_time_search']=$question_start_time_search;
        $_all['question_end_time_search']=$question_end_time_search;
        $_all['revocation_search']=$revocation_search;
        $_all['que_status_search']=$que_status_search;
        $_all['question_search']=$question_search;
        $_all['assess_search']=$assess_search;
        $_all['qid_search']=$qid_search;
        $_all['operator_search']=$operator_search;
        $_all['user_name_search']=$user_name_search;
        $_all['question_title_search']=$question_title_search;
        $_all['display_method']=$display_method;
        //$_all['category_search']=$category_search;
        $_all['order_search']=$order_search;
        $_all['help_search']=$help_search;
        $_all['all_kf_search']=$all_kf;
        $_all['r_site_search']=$r_site;
        $_all['cid']=$cid;
        $_all['cid1']=$cid1;
        $_all['cid2']=$cid2;
        $_all['cid3']=$cid3;
        $_all['cid4']=$cid4;
        $_all['num']=$rownum;
		$_all['Comm_status']=$Comm_status;
		$_all['game_search']=$game_search;
		$_all['mobile_search']=$mobile_search;
        $_all['include_answer']=$include_answer;
		$_SESSION['all_session'] = $_all;
    	$question_status = $this->ask_config->getQuestion();
    	$que_status = $this->ask_config->getQueStatus();
    	$revocation_status = $this->ask_config->getRevocation();
    	$assess_status = $this->ask_config->getAssess();
    	$help_status = $this->ask_config->getHelpStatus();
    	//$category_option = $_ENV['category']->get_categrory_tree($category_search);
    	
    	$op_cid = $_ENV['question']->_getCid(0,$cid);
    	$op_cid1 = $_ENV['question']->_getCid($cid,$cid1);
    	$op_cid2 = $_ENV['question']->_getCid($cid1,$cid2);
    	$op_cid3 = $_ENV['question']->_getCid($cid2,$cid3);
    	$op_cid4 = $_ENV['question']->_getCid($cid3,$cid4);
    	$msg && $message = $msg;
    	$ty && $type = $ty;
        include template('queview','admin');
        
    }
    /* 
		导出全部问题（已修改）
		viewExport:导出全部问题数据权限
	*/
    function onview_export()
	{
    	
		$hasViewExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "viewExport");
		if( $hasViewExportPrivilege['return'] )
		{
			if($_SESSION['all_session']['num'] >= 10000)
			{
				$this->onview('数据量太大，请重新筛选条件后进行导出！',"errormsg");
				exit;
    	    }
			require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("问题ID","5173帐号","问题描述","问题分类","提问时间","回答客服","接手时间","回答时间","回复时长","处理状态","协助处理","评价状态","浏览量","问题的状态","来源站点","游戏名称","联系状态","浏览器","操作系统","回答内容");
			array_push($export,$export_header);
			$cat = $_ENV['category']->getNameById();
			$_all = $_SESSION['all_session'];
			$where = $_ENV['question']->Get_Where($_all['ask_start_time_search'],$_all['ask_end_time_search'],$_all['wait_start_time_search'],
			$_all['wait_end_time_search'],$_all['answer_start_time_search'],$_all['answer_end_time_search'],$_all['question_start_time_search'],
			$_all['question_end_time_search'],$_all['revocation_search'],$_all['que_status_search'],$_all['question_search'],$_all['assess_search'],
			$_all['qid_search'],$_all['operator_search'],$_all['user_name_search'],$_all['question_title_search'],
			$_all['display_method'],$_all['category_search'],$_all['order_search'],$_all['help_search'],$_all['all_kf_search'],$_all['r_site_search']
					,$_all['cid'],$_all['cid1'],$_all['cid2'],$_all['cid3'],$_all['cid4'],$_all['Comm_status'],$_all['game_search'],$_all['mobile_search']);
			$export_arr = $_ENV['question']->Get_All_Question($where,false,$_all['all_kf_search']);	
			$Comm_status_List = $this->ask_config->getCommStatus();
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
				$val['comment'] = unserialize($val['comment']);
				
				if($_all['include_answer'])
				{
					$answer = $_ENV['answer']->get($val['id']);
						$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
						//$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
						
						$answer['content'] = strip_tags($answer['content']);
				}
				else
				{
					$answer['content'] = "";
				}
				$export[]=array($val['id'],$val['author'],$val['description'],$q_cat,date("Y-m-d H:i:s",$val['time']),
						$val['Aauthor'],$val['receive_time'], $val['Atime'],$replay_range,$mange_status,$help_status,
						$asses,$val['views'],$q_status,$r_site,$val['game_name'],$Comm_status_List[$val['Comm_status']],$val['comment']['Browser'],$val['comment']['OS'],$answer['content']);
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('question'.date('Ymd'));
		}
		else
		{
				$hasViewExportPrivilege['url'] = "?admin_question/view";
				__msg($hasViewExportPrivilege);
		}
    	
    }
    
    //客服主动接手问题权限  viewHandleQuestion
    function onview_handle_question()
    {
		$backReturn = array();
		$hasViewHandleQuestionPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "viewHandleQuestion");
		if( $hasViewHandleQuestionPrivilege['return'] )
		{
			$operator = $this->ask_login_name;
			$qid = $this->post['qid'];
			
			$q_info = $_ENV['question']->Get($qid);
			if($q_info['id']!=$qid)
			{
				//无此问题
				$backReturn = array('type'=>'1','comment'=>"无此问题");
			}
			else
			{
				$apply = $_ENV['question']->ApplyToOperator($qid,$operator,1);
				if($apply>0)
				{
					//接单成功
					$this->sys_admin_log($qid,$operator,$operator.'主动接取了咨询单'.$qid,3);//系统操作日志
					$backReturn = array('type'=>'2','comment'=>"接单成功");
				}
				else
				{
					//接单失败
					$backReturn = array('type'=>'3','comment'=>"接单失败");   
				}    
			}
		}
		else
		{
			$backReturn = array('type'=>'4','comment'=>$hasViewHandleQuestionPrivilege['comment']); 
		}
		
		echo(json_encode($backReturn));
    }
    //客服撤销接手问题权限  viewCancelHandleQuestion
    function onview_handle_question_cancel()
    {
		$backReturn = array();
		$hasViewCancelHandleQuestionPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "viewCancelHandleQuestion");
		if ( $hasViewCancelHandleQuestionPrivilege['return'] )
		{
			$qid = $this->post['qid'];
			$q_info = $_ENV['question']->Get($qid);
			if($q_info['id']!=$qid)
			{
				//无此问题
				$backReturn = array('type'=>'1','comment'=>"无此问题");
			}
			else
			{
				$apply = $_ENV['question']->ApplyCancel($qid);
				if($apply>0)
				{
					//接单成功
					$this->sys_admin_log($qid,$this->ask_login_name,$this->ask_login_name.'取消了咨询单'.$qid."的分配",11);//系统操作日志
					$backReturn = array('type'=>'2','comment'=>"撤单成功");
				}
				else
				{
					//撤单失败
					$backReturn = array('type'=>'3','comment'=>"撤单失败");   
				}    
			}
		}
		else
		{
			$backReturn = array('type'=>'4','comment'=>$hasViewCancelHandleQuestionPrivilege['comment']); 
		}
		echo(json_encode($backReturn));
    }
    //标记问题
    function onajaxmark(){
    	
    	if(isset($this->post['question_id']) && isset($this->post['type'])){
    	 	  if($this->post['type'] == 1){   	 	  	    	 	  	   
    	 	  	   $this->db->query("UPDATE ".DB_TABLEPRE."question SET mark='1' WHERE id='".$this->post['question_id']."'");
    	 	  	   exit('1');
    	 	  }elseif($this->post['type'] == 2){
    	 	  	   $this->db->query("UPDATE ".DB_TABLEPRE."question SET mark='0' WHERE id='".$this->post['question_id']."'");
    	 	  	   exit('2');
    	 	  }
    	 }else{
    	 	exit('0');
    	 }
    }
	// intoHandle:进入接手处理问题页面权限
    function onhandle() 
	{
    	
		$hasIntoHandlePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoHandle");
		$hasIntoHandlePrivilege['url'] = "?admin_main";
		!$hasIntoHandlePrivilege['return']  && __msg($hasIntoHandlePrivilege);
		
		setcookie('t_answer_template','',time()-3600);
    	if(isset($this->ask_login_name)){
    		isset($this->get[2]) && $hide_wait = true;
    		$o_list = $_ENV['operator']->getUser($this->ask_login_name);                   	 		
    	}    	
       include template('handle','admin');

    }
    
    function onajaxhandle_top() {
        if(isset($this->ask_login_name)){       	
        	$month = $_ENV['question']->getMonthQuestion();
        	if($month){
        		if(array_key_exists($this->ask_login_name,$month)){
        			$href = '<a href=index.php?admin_question/my/////'.$_ENV['question']->_getTime(1).'/'.time().'///-1/-1/-1/-1//'.$this->ask_login_name.'///-1///1/-1/0" target="main">查看</a>';       					
        			$str = '您本月已经处理<span style="color:blue;">'.$month[$this->ask_login_name]['num'].'</span>个问题（'.$href.'），排名第<span style="color:red;">'.$month[$this->ask_login_name]['sorce'].'</span>位，继续努力！';
        			exit($str);
        		}else{
        			exit('您本月还没有处理过问题，继续努力！');
        		}
        	}else{
        		exit('本月还没有客服处理过问题！');
        	}
        }  
    }
      
    function onhandle_quick() {
       
       $quick_list = $_ENV['quick_cat']->get_categories_list();
       $this->load("qcontent");
	   $Qcontent_list = $_ENV['qcontent']->GetAllQcontent(0,0);
	   include template('quick','admin');
    }
    // 添加快捷回复分类页面
    function onhandle_quick_setting($msg='', $ty='')
	{
       $cat_list = $_ENV['quick_cat']->get_list();	
       $msg && $message = $msg;
       $ty && $type = $ty;
       include template('quick_setting','admin');

    }
    /* 
		添加快捷回复分类
		handleQuickAdd:添加快捷回复分类权限（和重命名快捷回复分类是一个权限）
	*/
    function onhandle_quick_add()
	{
    	
		$hasHandleQuickAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleQuickAdd");
		if( $hasHandleQuickAddPrivilege['return'] )
		{
			if(isset($this->post['id']))
			{
				isset($this->post['title']) && $_ENV['quick_cat']->add(trim($this->post['title']),$this->post['id']);
				$this->onhandle_quick_setting("快捷回复修改成功！"); 
			}
			else
			{
				isset($this->post['title']) && $_ENV['quick_cat']->add(trim($this->post['title']));
				$this->onhandle_quick_setting("快捷回复添加成功！");     	
			}	
		}
		else
		{
			if( isset($this->post['id']) )
			{
				__msg(array('comment'=>"你没有重命名快捷回复分类权限！" , 'url'=>"?admin_question/handle_quick_setting"));
			}
			else
			{
				__msg(array('comment'=>"你没有添加快捷回复分类权限！" , 'url'=>"?admin_question/handle_quick_setting"));
			}
		}
                      
    }
    // handleQuickRemove:删除快捷回复分类
    function onhandle_quick_remove()
	{
    	
		$hasHandleQuickRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleQuickRemove");
		if( $hasHandleQuickRemovePrivilege['return'] )
		{
		   if(isset($this->get[2]))
			{
				$_ENV['quick_cat']->remove($this->get[2]);
				$this->onhandle_quick_setting("快捷回复删除成功！");
			}	 
		}
		else
		{
			 $hasHandleQuickRemovePrivilege['url'] = "?admin_question/handle_quick_setting";
			 __msg($hasHandleQuickRemovePrivilege);
		}
			                
    }
    
    function onhandle_quick_edit($msg='', $cid='')
	{
    	    	
    	$quick_cid = isset($this->get[2])?$this->get[2]:$cid;
    	$content_list = $_ENV['quick_content']->get_list($quick_cid);
    	foreach($content_list as $key => &$val){
    		
    		$content_list[$key]['content'] = str_replace(" ", "&nbsp;", str_replace("\n", "<br>", htmlspecialchars($val['content'])));
    	}	
        $msg && $message = $msg;
        include template('quick_content','admin');             
    }
    
	//handleQuickEditAdd:添加快捷回复内容权限 (和编辑快捷回复内容是一个权限)
    function onhandle_quick_edit_add() 
	{
    	
		$hasHandleQuickEditAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleQuickEditAdd");
		if( $hasHandleQuickEditAddPrivilege['return'] )
		{
			if(isset($this->post['id']))
			{
				$_ENV['quick_content']->add($this->post['content'],0,$this->post['id']);
				$this->onhandle_quick_edit("快捷回复内容修改成功！",$this->post['cid']); 
			}
			else
			{
				$_ENV['quick_content']->add($this->post['content'],$this->post['cid']);
				$this->onhandle_quick_edit("快捷回复内容添加成功！",$this->post['cid']);     	
			}       
		}
		else
		{
			if( isset($this->post['id']) )
			{
				__msg(array('comment'=>"你没有编辑快捷回复内容权限！" , 'url'=>"?admin_question/handle_quick_setting"));
			}
			else
			{
				__msg(array('comment'=>"你没有添加快捷回复内容权限！" , 'url'=>"?admin_question/handle_quick_setting"));
			}
		}
    }
    //handleQuickEditRemove:删除快捷回复权限
    function onhandle_quick_edit_remove() 
	{
    	
		$hasHandleQuickEditRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleQuickEditRemove");
		if( $hasHandleQuickEditRemovePrivilege['return'] )
		{
			if(isset($this->get[2]))
			{
				$_ENV['quick_content']->remove($this->get[2]);
				unset($this->get[2]);
				$this->onhandle_quick_edit("快捷回复内容删除成功！",$this->get[3]);
			} 
		}
		else
		{
			$hasHandleQuickEditRemovePrivilege['url'] = "?admin_question/handle_quick_setting";
			__msg($hasHandleQuickEditRemovePrivilege);
		}            
    }
    
    function onhandle_wait() {
       setcookie('view_question_id','',time()-3600);
       include template('wait','admin');

    }
    
    //即时刷新等待处理的问题（已修改）
    function onajaxhandle_wait_question() {
       isset($this->ask_login_name)	&& $question_list = $_ENV['question']->Get_Handle_List($this->ask_login_name);
       !empty($question_list) && $question_num = count($question_list);
       $question_num = isset($question_num)?$question_num:0;
       $html = '<table style="width:100%;border-collapse:collapse;border-spacing:0px;margin:0px;">
        <tr><td colspan="4" style="color:#555;">等待处理的问题&nbsp;<span style="color:red;">'.$question_num.'</span></td></tr><tr><td><div id="accordion">';
       foreach($question_list as $key => $question){
       	   $display_help='';
	       $question['help_status']==1 && $display_help="<font color='red'>协</font>";
     	   $html.='<span style="display:block;margin-top:10px;">'.$display_help.($key+1).'、<a href="javascript:;" onclick="on_show_question('.$question['id'].')">'.cutstr($question['description'],21).'</a></span>';
       }
       $html.='</div></td></tr></table>';         
       exit($html);
    }
    
    //显示问题树（已修改）
    function onhandle_answer_content() 
	{
		$t_answer_template = isset($_COOKIE['t_answer_template'])?$_COOKIE['t_answer_template']:'';
		$question_id = $this->get[2]?$this->get[2]:(isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:''));
		if($question_id)
		{
			$q_tree = $_ENV['question']->get_question_tree($question_id);
			$cat = $_ENV['category']->getNameById();
			if(!empty($q_tree))
			{
				$where = " WHERE q.id IN (".implode(",",$q_tree).") ORDER BY q.time,a.time" ;     	   	   
				$q_list = $_ENV['question']->Get_Question_List($where);
			}
			$q_info = $_ENV['question']->Get($q_tree[$question_id]);
			$categoryInfo = $_ENV['category']->get(intval($q_list[$question_id]['cid']));
			$MobileView = 0;
			if($q_list[$question_id]['js_kf']==$this->ask_login_name)
			{
				$MobileView = 1;
			}
			else
			{
				$MobileViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, 'admin_question/view', "MobileView");
				if($MobileViewPrivilege['return'] == 1)
				{
					$MobileView = 1;
				}
			}
			$comment = unserialize($question_info['comment']);
			if(	isset($comment['convert']['from_id']) )
			{
				$qid = intval($comment['convert']['from_id']);
			}
			else if(isset($comment['convert']['to_id']))
			{
				$qid = intval($comment['convert']['to_id']);
			}
			else
			{
				$qid = intval($question_id);
			}
			$transformLog = $_ENV['question']->getTransformLogByQuestion( $qid, $categoryInfo['question_type'] );
			$assess_status = $this->ask_config->getAssess();
			$question_type = $this->ask_config->getQuestionType();
		}       
		include template('content','admin');
    }
    
    //提交回答内容（已修改）
    function onhandle_answer_submit() 
    {      	
	   $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
       if($question_id)
       {
			$query = $_ENV['question']->Get($question_id); 
			$this->post['content'] = htmlspecialchars_decode(strip_tags($this->post['content'],'<br><p><img><a><strong><em><span>'));
			if(!empty($_COOKIE['modify_question_id']))
	       	{
				// handleQuestionMofify:修改问题权限
				$hasHandleQuestionMofifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleQuestionMofify");
				//获取回答内容
				$answer_id = $_COOKIE['modify_question_id'];
				$answer_sql = "select qid,id,comment,content,time from ".DB_TABLEPRE."answer where id=$answer_id LIMIT 1";
				$answer_detail = $this->db->fetch_first($answer_sql);
				$Comment = unserialize($answer_detail['comment']);
				//如果答案关联的问题和入口问题不是同一个
				if($answer_detail['qid'] != $query['id'])
				{
					//答案关联问题
					$q = $_ENV['question']->Get($answer_detail['qid']);
					//答案关联的问题是入口问题的追问
					if($q['pid'] == $query['id'])
					{
						$qid = $q['id'];
						//修改人为接手客服或者有权限的人
						if(($this->ask_login_name == $q['js_kf']) ||  ($hasHandleQuestionMofifyPrivilege['return'] ))
						{
							$this->db->begin();
							$Comment['answer_update_log'][count($Comment['answer_update_log'])] = array('update_operator'=>$this->ask_login_name,'time'=>$answer_detail['time'],/*'content'=>$answer_detail['content'],*/'update_time'=>time());
							$Comment = serialize($Comment);
							$update_answer = "update ".DB_TABLEPRE."answer SET qid=".$qid.",content='".$this->post['content']."',comment='".$Comment."' where id = ".$answer_detail['id'];
							$this->db->query($update_answer);
							$update_answer_num = $this->db->affected_rows();
							if($q['status']==1)
							{
								$sql = "UPDATE ".DB_TABLEPRE."question SET status='2' WHERE id='".$qid."' and status = '1'";
								$this->db->query($sql); //更新问题的状态，为等待评价
								$updateQuestionNum = $this->db->affected_rows(); 
							}
							else
							{
								$updateQuestionNum = 1; 
							}

							if($update_answer_num>0 && $updateQuestionNum>0)
							{
								$this->db->commit();
								setcookie('modify_question_id','',time()-3600);
								$this->sys_admin_log($question_id,$this->ask_login_name,"原回复:".$answer_detail['content']."改为 新回复:".$this->post['content'],8);//系统操作日志
								$_ENV['question']->rebuildQuestionDetail($question_id,"question");
								exit('6');
							}
							else
							{
								$this->db->rollBack();
								exit('11');									
							}
						}
						else
						{
							setcookie('modify_question_id','',time()-3600);
							exit('3'); // 没有修改客服回答的权限
						} 
					}
					//入口问题是答案关联问题的追问
					elseif($query['pid'] == $q['id'])
					{
						$qid = $query['pid'];
						//修改人为接手客服或者有权限的人
						if(($this->ask_login_name == $query['js_kf']) ||  ($hasHandleQuestionMofifyPrivilege['return'] ))
						{
							$this->db->begin();
							$Comment['answer_update_log'][count($Comment['answer_update_log'])] = array('update_operator'=>$this->ask_login_name,'time'=>$answer_detail['time'],/*'content'=>$answer_detail['content'],*/'update_time'=>time());
							$Comment = serialize($Comment);
							$update_answer = "update ".DB_TABLEPRE."answer SET qid=".$qid.",content='".$this->post['content']."',comment='".$Comment."' where id = ".$answer_detail['id'];
							$this->db->query($update_answer);
							$update_answer_num = $this->db->affected_rows();
							if($query['status']==1)
							{
								$sql = "UPDATE ".DB_TABLEPRE."question SET status='2' WHERE id='".$qid."' and status = '1'";
								$this->db->query($sql); //更新问题的状态，为等待评价
								$updateQuestionNum = $this->db->affected_rows(); 
							}
							else
							{
								$updateQuestionNum = 1; 
							}
							if($update_answer_num>0 && $updateQuestionNum>0)
							{
								$this->db->commit();
								setcookie('modify_question_id','',time()-3600);
								$this->sys_admin_log($question_id,$this->ask_login_name,"原回复:".$answer_detail['content']."改为 新回复:".$this->post['content'],8);//系统操作日志
								$_ENV['question']->rebuildQuestionDetail($question_id,"question");
								exit('6');
							}
							else
							{
								$this->db->rollBack();
								exit('11');									
							}
						}
						else
						{
							setcookie('modify_question_id','',time()-3600);
							exit('3'); // 没有修改客服回答的权限
						} 
					}
					//入口问题和答案关联问题同属一个问题的追问
					elseif($query['pid'] == $q['pid'])
					{
						$qid = $q['id'];
						//修改人为接手客服或者有权限的人
						if(($this->ask_login_name == $query['js_kf']) ||  ($hasHandleQuestionMofifyPrivilege['return'] ))
						{
							$this->db->begin();
							$Comment['answer_update_log'][count($Comment['answer_update_log'])] = array('update_operator'=>$this->ask_login_name,'time'=>$answer_detail['time'],/*'content'=>$answer_detail['content'],*/'update_time'=>time());
							$Comment = serialize($Comment);
							$update_answer = "update ".DB_TABLEPRE."answer SET qid=".$qid.",content='".$this->post['content']."',comment='".$Comment."' where id = ".$answer_detail['id'];
							$this->db->query($update_answer);
							$update_answer_num = $this->db->affected_rows();
							if($q['status']==1)
							{
								$sql = "UPDATE ".DB_TABLEPRE."question SET status='2' WHERE id='".$qid."' and status = '1'";
								$this->db->query($sql); //更新问题的状态，为等待评价
								$updateQuestionNum = $this->db->affected_rows(); 
							}
							else
							{
								$updateQuestionNum = 1; 
							}
							if($update_answer_num>0 && $updateQuestionNum>0)
							{
								$this->db->commit();
								setcookie('modify_question_id','',time()-3600);
								$this->sys_admin_log($question_id,$this->ask_login_name,"原回复:".$answer_detail['content']."改为 新回复:".$this->post['content'],8);//系统操作日志
								$_ENV['question']->rebuildQuestionDetail($question_id,"question");
								exit('6');
							}
							else
							{
								$this->db->rollBack();
								exit('11');									
							}
						}
						else
						{
							setcookie('modify_question_id','',time()-3600);
							exit('3'); // 没有修改客服回答的权限
						} 
					}
					else
					{
						setcookie('modify_question_id','',time()-3600);
						exit('11');			
					}					
				}
				//同一问题的答案与问题关联
				else
				{
					//修改人为接手客服或者有权限的人
					if(($this->ask_login_name == $query['js_kf']) ||  ($hasHandleQuestionMofifyPrivilege['return'] ))
					{
						$this->db->begin();
						$Comment['answer_update_log'][count($Comment['answer_update_log'])] = array('update_operator'=>$this->ask_login_name,'time'=>$answer_detail['time'],/*'content'=>$answer_detail['content'],*/'update_time'=>time());
						$Comment = serialize($Comment);
						$update_answer = "update ".DB_TABLEPRE."answer SET qid=".$query['id'].",content='".$this->post['content']."',comment='".$Comment."' where id = ".$answer_detail['id'];
						$this->db->query($update_answer);
						$update_answer_num = $this->db->affected_rows();
						if($query['status']==1)
						{
							$sql = "UPDATE ".DB_TABLEPRE."question SET status='2'  WHERE id='".$question_id."' and status = '1'";
							$this->db->query($sql); //更新问题的状态，为等待评价
							$updateQuestionNum = $this->db->affected_rows(); 
						}
						else
						{
							$updateQuestionNum = 1; 
						}

						if($update_answer_num>0 && $updateQuestionNum>0)
						{
							$this->db->commit();
							setcookie('modify_question_id','',time()-3600);
							$this->sys_admin_log($question_id,$this->ask_login_name,"原回复:".$answer_detail['content']."改为 新回复:".$this->post['content'],8);//系统操作日志
							$_ENV['question']->rebuildQuestionDetail($question_id,"question");
							exit('6');
						}
						else
						{
							$this->db->rollBack();
							exit('11');									
						}
					}
					else
					{
						setcookie('modify_question_id','',time()-3600);
						exit('3'); // 没有修改客服回答的权限
					} 
				}
				

	       	}
       	    if(!empty($query))
       	    {	    	
				$query['revocation'] == 1 && exit('4'); //看是否被撤销
       	    	if($query['status'] == 1 && $query['cid1'] == 0) exit('5'); //看是否选择了分类 
       	    	$check = $_ENV['category']->check_cid($query['cid'],$query['cid1'],$query['cid2'],$query['cid3'],$query['cid4']);
				if($check != true)
				{
					exit('5');	
				}
				//if($query['status'] == 1 && $query['tag'] == '') exit('7'); //看是否选择了标签
       	    	if($query['is_hawb'] == 0) exit('9'); //看此问题是否已分单
				if($query['Comm_status'] == 0) exit('13'); //看此问题是否已分单
       	    	
       	    	// 判断是否为协助处理单
       	    	if($query['help_status'] == 1) 
       	    	{ 
       	    		$help_detail = $this->db->fetch_first("select id,qid,aid from ".DB_TABLEPRE."help where qid=$question_id and aid='$this->ask_login_name' and status=0 ORDER BY start DESC LIMIT 1");
					$answer_detail = $this->db->fetch_first("select id from ".DB_TABLEPRE."answer where qid=$question_id and author='$this->ask_login_name' ORDER BY id DESC LIMIT 1");
       	    		if(empty($help_detail))
       	    		{
       	    			exit('10'); // 无该协助单或该协助单不是你的
       	    		}
       	    		//更新开始,添加事务
       	    		$this->db->begin();
       	    		
       	    		$this->db->query("update ".DB_TABLEPRE."help set status=1,back_content='已回复',back_time='".time()."' where id={$help_detail['id']}"); //更新该协助处理状态
       	    		$updateHelpNum = $this->db->affected_rows();
       	    		
       	    		if($answer_detail['id'])
					{
						$this->db->query("update ".DB_TABLEPRE."answer SET content='{$this->post['content']}',time='".time()."' where id={$help_detail['id']} and qid={$help_detail['qid']} and author='{$help_detail['aid']}'"); //覆盖原有问题
						$insertId = $this->db->affected_rows();				
					}
					else
					{
						$this->db->query("INSERT INTO ".DB_TABLEPRE."answer SET qid={$help_detail['qid']},author='{$help_detail['aid']}',
								content='{$this->post['content']}',time='".time()."'"); //回答问题
						$insertId = $this->db->insert_id();					
					}

       	    		
       	    		$atime = $query['atime'] == 0 ? ",atime='".time()."' " : '';
       	    		$this->db->query("UPDATE ".DB_TABLEPRE."question SET status='2' $atime WHERE id=$question_id");//更新问题的状态，为等待评价
       	    		$updateQuestionNum = $this->db->affected_rows();
       	    		
       	    		// 更新结束，需要补充事务
       	    		if($query['author'] != '游客')
       	    	    {
       	    			send_message($question_id,$query['author_id'],$query['author'],$query['title']);//发送站内信
       	    			if($query['pid'] == 0) 
       	    			{
       	    				$cid = $_ENV['question']->getType(2); //建议分类id
       	    				if($query['cid'] == $cid ) { $this->send_SMS($query['author_id']); } //建议类问题发送短信通知
       	    			}
       	    		}
       	    		
					// 问题先回复再协助处理，回答改协助处理问题时 更新问题表时影响行数为0
       	    		if ($updateHelpNum > 0 && $insertId > 0 && $updateQuestionNum >= 0) 
       	    		{
       	    			$this->db->commit();
       	    			$_ENV['question']->rebuildQuestionDetail($question_id,"question");
						exit('1');
       	    		}
       	    		else
       	    		{
       	    			$this->db->rollBack();
       	    			exit('11');
       	    		}
       	    		// 事务处理结束
       	    	}
       	    	
       	    	if($query['status'] == 1 && $query['js_kf'] != '' && $query['js_kf'] != $this->ask_login_name) exit('8');//第一次回答必须是接手此问题的客服
       	    	
				if($query['js_kf'] != '')
       	    	{//接手客服不为空，新版
					$time = time();
       	    		//接手此问题客服就是登陆客服
       	    		if($query['js_kf'] == $this->ask_login_name)
       	    		{
       	    			//此问题还没有被回答
       	    			if($query['status'] == 1)
       	    			{
							// 更新操作,增加事务
       	    				$this->db->begin();
       	    				
       	    				$this->db->query("INSERT INTO ".DB_TABLEPRE."answer SET qid=".$query['id'].",author='".$this->ask_login_name."',
       	    									content='".$this->post['content']."',time='".$time."'"); //回答问题
       	    				$insertAnswerId = $this->db->insert_id();
       	    				
       	    				$this->db->query("UPDATE ".DB_TABLEPRE."question SET status='2',atime='".$time."' WHERE id='".$question_id."'"); //更新问题的状态，为等待评价
       	    				$updateQuestionNum = $this->db->affected_rows(); 
       	    				
       	    				$updateAuthor_numNum = 0;
       	    				//如果已经协助处理过了不减单量
       	    				if($query['help_status'] == 0)
       	    				{ 
       	    					if($query['pid']==0)
       	    					{
       	    					    //首问
									$this->db->query ( "UPDATE ".DB_TABLEPRE."author_num SET num = num-1 WHERE author='".$this->ask_login_name."'" ); //更新客服的接单量
       	    				    }
       	    					else
       	    					{
       	    					    //追问
       	    					    $this->db->query ( "UPDATE ".DB_TABLEPRE."author_num SET num_add = num_add-1 WHERE author='".$this->ask_login_name."'" ); //更新客服的接单量
       	    				    }
       	    				    
       	    					$updateAuthor_numNum = $this->db->affected_rows();
       	    				}
       	    				else
       	    				{
       	    					//否则就跳过判断 
       	    					$updateAuthor_numNum = 1;
       	    				}
       	    				if($insertAnswerId > 0 && $updateQuestionNum > 0 && $updateAuthor_numNum > 0)
       	    				{
       	    					$this->db->commit();
       	    					$_ENV['question']->rebuildQuestionDetail($question_id,"question");
								$this->cache->set('fw'.$question_id,time(),604800); // 分类id写入memcache
 								$this->sys_admin_log($question_id,$this->ask_login_name,$this->post['content'],4);
      	    					
       	    					//更新Solr服务器
       	    					if($query['pid'] == 0)
       	    					{
       	    						$searchStart = microtime(true);
       	    						$q_info = $_ENV['question']->Get_Search_Data($query['id']);
       	    						$q_info['title'] = $q_info['description'];
       	    						if(!empty($q_info))
       	    						{
       	    							if($q_info['hidden']==1)
										{
											unset($q_info['hidden']);
											try 
											{
												$this->set_search($q_info);
											}
											catch(Exception $e) 
											{
												send_AIC('http://scadmin.5173.com/index.php?admin_question/ajaxhandle_answer_submit.html','搜索服务器异常',1,'搜索接口');
											}										
										}
										else
										{
											$this->delete_search($q_info['id']);
										}
       	    						}
       	    					}
       	    					exit('1'); // 回答成功
       	    				}
       	    				else 
       	    				{
       	    					$this->db->rollBack();
       	    					exit('11'); // 回答失败
       	    				}
       	    				//更新结束，事务处理结束       	    				
       	    			}
       	    			else
       	    			{
							$answer_sql = "select id,Comment,content,time from ".DB_TABLEPRE."answer where qid=$question_id and author='$this->ask_login_name' ORDER BY id DESC LIMIT 1";
							$answer_detail = $this->db->fetch_first($answer_sql);
							if(!$answer_detail['id'])
       	    				{
       	    					exit('2');//如您要修改自己的问题，请单击上面的修改按钮！
       	    				}
       	    				else
       	    				{
								$this->db->begin();
								$Comment = unserialize($answer_detail['Comment']);
								$Comment['answer_update_log'][count($Comment['answer_update_log'])] = array('update_operator'=>$this->ask_login_name,'time'=>$answer_detail['time'],/*'content'=>$answer_detail['content'],*/'update_time'=>time());
								$Comment = serialize($Comment);
								$update_answer = "update ".DB_TABLEPRE."answer SET qid=".$query['id'].",author='".$this->ask_login_name."',content='".$this->post['content']."',Comment='".$Comment."' where id = ".$answer_detail['id'];
								$this->db->query($update_answer);
								$update_answer_num = $this->db->affected_rows();
								if($query['status']==1)
								{
									$sql = "UPDATE ".DB_TABLEPRE."question SET status='2'  WHERE id='".$question_id."' and status = '1'";
									$this->db->query($sql); //更新问题的状态，为等待评价
									$updateQuestionNum = $this->db->affected_rows(); 
								}
								else
								{
									$updateQuestionNum = 1; 
								}
								if($update_answer_num>0 && $updateQuestionNum>0)
								{
									$this->db->commit();
									$_ENV['question']->rebuildQuestionDetail($question_id,"question");
									setcookie('modify_question_id','',time()-3600);
									$this->sys_admin_log($question_id,$this->ask_login_name,"原回复:".$answer_detail['content']."改为 新回复:".$this->post['content'],8);
									exit('6');
								}
								else
								{
									$this->db->rollBack();
									exit('11');									
								}
       	    				}        	    					
       	    			}
       	    		}
       	    		else
       	    		{
						$answer_sql = "select id,content,time from ".DB_TABLEPRE."answer where qid=$question_id and author='$this->ask_login_name' ORDER BY id DESC LIMIT 1";
						$answer_detail = $this->db->fetch_first($answer_sql);
						if(!$answer_detail['id'])
						{
							exit('2');//如您要修改自己的问题，请单击上面的修改按钮！
						}
						else
						{
							$this->db->begin();
							$Comment = unserialize($answer_detail['Comment']);
							$Comment['answer_update_log'][count($Comment['answer_update_log'])] = array('update_operator'=>$this->ask_login_name,'time'=>$answer_detail['time'],/*'content'=>$answer_detail['content'],*/'update_time'=>time());
							$Comment = serialize($Comment);
							$update_answer = "update ".DB_TABLEPRE."answer SET qid=".$query['id'].",author='".$this->ask_login_name."',content='".$this->post['content']."',Comment='".$Comment."' where id = ".$answer_detail['id'];
							$this->db->query($update_answer);
							$update_answer_num = $this->db->affected_rows();
							if($query['status']==1)
							{
								$sql = "UPDATE ".DB_TABLEPRE."question SET status='2'  WHERE id='".$question_id."' and status = '1'";
								$this->db->query($sql); //更新问题的状态，为等待评价
								$updateQuestionNum = $this->db->affected_rows(); 
							}
							else
							{
								$updateQuestionNum = 1; 
							}
							if($update_answer_num>0 && $updateQuestionNum>0)
							{
								$this->db->commit();
								$_ENV['question']->rebuildQuestionDetail($question_id,"question");
								setcookie('modify_question_id','',time()-3600);
								$this->sys_admin_log($question_id,$this->ask_login_name,"原回复:".$answer_detail['content']."改为 新回复:".$this->post['content'],8);
								exit('6');
							}
							else
							{
								$this->db->rollBack();
								exit('11');									
							}
						} 
       	    		}
       	    	}
       	    	
       	    	//首次回复操作记录
       	    	if($query['game_name'] != '') $message .= '选择游戏:'.$query['game_name'];
       	    	$message .= '回复内容:'.$this->post['content'];
       	    	$this->sys_admin_log($question_id,$this->ask_login_name,$message,4);//系统操作日志
       	    	
       	    	if($query['author'] != '游客')
       	    	{
       	    		send_message($question_id,$query['author_id'],$query['author'],$query['title']);//发送站内信     	    		
       	    		if($query['pid'] == 0){
       	    			$jyCid = $_ENV['question']->getType(2); // 获取问题分类id
       	    			if($query['cid'] == $jyCid ){
       	    				$this->send_SMS($query['author_id']); //建议类问题发送短信通知
       	    			}    	    			
       	    		}
       	    	} 
       	    	 
       	    }
       	    else
       	    {
       	    	exit('11');
       	    }
       	}
       	else
       	{
       		exit('12');
       	}
    }
    
    function onhandle_answer() 
	{       
       $question_id = $this->get[2]?$this->get[2]:(isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:''));
	   $ComplainType = $_ENV['question']->getType(3);
	   if($question_id){
       	   $question_info = $_ENV['question']->Get($question_id);
		   $q_status = $question_info['pid'] == 0 ? $question_info : ($_ENV['question']->Get($question_info['pid']));
       }
       include template('answer','admin');
    }

    //在协助之前是否已经选择了分类
    function onajaxhandle_answer_cat() {
    	$question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
    	if($question_id){
    		$_ENV['question']->is_cat($question_id);
    	}
    }
    // 选择问题分类
    function onhandle_answer_sort() 
	{   
		$hashandleAnswerSortPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerSort");
		!$hashandleAnswerSortPrivilege['return'] && exit('3');
		$TransformReason = trim($this->post['transform_reason']);
    	$question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
		$CidArr = array('cid' => intval($this->post['cid']),'cid1' => intval($this->post['cid1']),'cid2' => intval($this->post['cid2']),'cid3' => intval($this->post['cid3']),
		'cid4' => intval($this->post['cid4']));
		$modify = $_ENV['question'] -> modifyQuestionCid($question_id,$CidArr,$this->ask_login_name,$this->setting["askSuggestTransComplain"],$TransformReason);
		//问题分类改为投诉
		if($modify['to_type'] == "complain")
		{
			$QuestionInfo = $_ENV['question']->Get($question_id);
			// 更新成功 
			if($modify['result'] == 1)
			{
				//转换到投诉
				$convert = $_ENV['question']->convertQuestionToComplain($question_id,$modify['from_type'],$modify['to_type'],$this->ask_login_name,$TransformReason);
				if($convert>0)
				{
					$message = $this->ask_login_name."将问题".$question_id."改为投诉,关联投诉单号：".$convert."理由为：".$TransformReason;
					$log = $this->sys_admin_log($question_id,$this->ask_login_name,$message,5);//系统操作日志
					$logInfo = array('AuthorName'=>$QuestionInfo['author'],'acceptTime'=>time(),'applyTime'=>time(),"from_type"=>$modify['from_type'],"to_type"=>$modify['to_type'],"ApplyOperator"=>$this->ask_login_name,"transform_status"=>1,"comment" =>serialize(array('TransformReason'=>$TransformReason,"CidArr"=>$CidArr)),"to_id"=>$convert,"from_id"=>$question_id,'AcceptOperator'=>"system");
					$_ENV['question']->insertTransformLog($logInfo);
					
					$complainInfo = $_ENV['complain']->get_ComplainInfo($convert);
					// 增加新的纪录到 搜索服务器
					if ( isset ( $complainInfo['id'] ) ) 
					{
						$q_search['id'] = 'c_'.$complainInfo['id'];
						$q_search['title'] = $complainInfo['description'];
						$q_search['description'] = $complainInfo['description'];
						$q_search['tag'] = json_encode(array(),true);
						$q_search['question_type'] = 'complain';
						$q_search['time'] = $complainInfo['time'];
						$q_search['atime'] = 0;
						// 从搜索服务器上删除咨询或建议问题
						$this->delete_search($question_id);
						if($complainInfo['public']==0)
						{
							try
							{
								$this->set_search($q_search);																				
							}
							catch(Exception $e)
							{
								send_AIC('http://scadmin.5173.com/control/admin/question/convertQuestionToComplain.html','咨询建议转投诉,添加到搜索服务器异常',1,'搜索接口');
							} 								
						}
						else
						{
							$this->delete_search('c_'.$complainInfo['id']);
						}
					}
					$_ENV['question']->rebuildQuestionDetail($question_id,"question");
					$_ENV['question']->rebuildQuestionDetail($complainInfo['id'],"complain");
					exit("4"); // 转单成功
						
				}
				else
				{
					exit("5"); // 转单失败
				}			
			}
			elseif($modify['result'] == 6)
			{
				$InProgess = $_ENV['question']->getTransformLogInProgess( $question_id );
				if(count($InProgess)>0)
				{
					exit("9");
				}

				$message = $this->ask_login_name."申请将问题".$question_id."改为投诉,理由为：".$TransformReason;
				$log = $this->sys_admin_log($question_id,$this->ask_login_name,$message,5);//系统操作日志
				$logInfo = array('AuthorName'=>$QuestionInfo['author'],'applyTime'=>time(),"from_type"=>$modify['from_type'],"to_type"=>$modify['to_type'],"ApplyOperator"=>$this->ask_login_name,"transform_status"=>0,"comment" =>serialize(array('TransformReason'=>$TransformReason,"CidArr"=>$CidArr)),"to_id"=>$convert,"from_id"=>$question_id);
				$_ENV['question']->insertTransformLog($logInfo);
				exit("6"); // 待审核
			}
			else
			{
				echo ($modify['result']); //转换成功
			}
		}
		else
		{
			echo ($modify['result']); //返回
		}
    }
    
    //取选择分类下的子类
    function onajaxhandle_answer_sort_cid() {
	   isset($this->post['cid']) && $_ENV['question']->getCid($this->post['cid']);  
    }
    
    //选择游戏 handleAnswerGame:选择游戏权限
    function onhandle_answer_game()
	{       
	   $hasHandleAnswerGamePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerGame");
	   !$hasHandleAnswerGamePrivilege['return']  && exit('3');
		
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');	 
       if($question_id)
	   {
			$question_info = $_ENV['question']->Get($question_id);
			$game_name = trim($this->post['game_name']);
			$t = explode("-",$game_name);
			if(count($t)>1)
			{
				$game_name = $t[1];
			}
			else
			{
				$game_name = $game_name;
			}
       	    $questionInfo = array( 'gameid' => $this->post['game_id'],
      	    'game_name' => $game_name);
       	    $pid = $question_info['pid']>0?$question_info['pid']:$question_info['id'];
       	    $update = $_ENV['question']->updateQuestionGame( $pid, $questionInfo ); // 更新问题游戏
       	    if($update)
			{			
       	    	$this->sys_admin_log($pid,$this->ask_login_name,$question_info['game_name'].'及其追问【修改为】'.$game_name,6);//系统操作日志
       	    }   	         	
   	    	exit('1');       	    	
       }
	   else{
       	    exit('0');
       }
    }
    
    //游戏列表
    function onajaxhandle_answer_game_list() {   
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');	 
       if($question_id){
       	   $q_info = $_ENV['question']->Get($question_id);
       	   if(!empty($q_info['r_site']) && $q_info['gameid'] != '') exit('0');//发布来源站点已有游戏，不能修改
       }
   	   $select_game = ($q_info['gameid']=='0' || trim($q_info['gameid'])=='')?'':"game:{id:'".$q_info['gameid']."',name:'".$q_info['game_name']."'},";
	   $select_operator = ($q_info['operatorid']=='0' || trim($q_info['operatorid'])=='')?'':"operator:{id:'".$q_info['operatorid']."',name:'".$q_info['operator_name']."'},";
	   $select_area = ($q_info['areaid']=='0' || trim($q_info['areaid'])=='')?'':"area:{id:'".$q_info['areaid']."',name:'".$q_info['area_name']."'},";
	   $select_server = ($q_info['serverid']=='0' || trim($q_info['serverid'])=='')?'':"server:{id:'".$q_info['serverid']."',name:'".$q_info['server_name']."'},";
	   include template('game_update','admin');	
    }
    
    //选择标签 handleAnswerTag:选择标签权限
    function onhandle_answer_tag()
	{
	  
	   $hasHandleAnswerTagPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerTag");
	   !$hasHandleAnswerTagPrivilege['return']  && exit('3'); // 没有选择标签权限
		
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');	 
       if($question_id)
	   {
       	    $log_flag = false;
       	    $q_info = $_ENV['question']->Get($question_id);
       	    if($q_info['tag'] != '')
			{
       	    	$log_flag = true;
       	    } 
       	    $tag_arr_old = json_decode($q_info['tag'],true); 
       	    $tag_arr = explode("|",$this->post['tag_str']);
       	    $questionInfo = array('tag'=>json_encode($tag_arr));
       	    $_ENV['question']->updateQuestion( $question_id, $questionInfo );
       	        	
   	    	foreach($tag_arr_old as $tag){
   	    		if(!in_array($tag,$tag_arr)){
   	    			$this->db->query("UPDATE ".DB_TABLEPRE."tag SET questions=questions-1 WHERE id='$tag'");   	    			
   	    		}
   	    	}
   	    	
   	    	foreach($tag_arr as $tag){
   	    		if(!in_array($tag,$tag_arr_old)){
   	    			$this->db->query("UPDATE ".DB_TABLEPRE."tag SET questions=questions+1 WHERE id='$tag'");   	    			
   	    		}
   	    	}
   	    	
   	    	//更新Solr服务器
   	    	if($q_info['pid'] == 0)
			{    		
   	    		$q_info = $_ENV['question']->Get_Search_Data($q_info['id']);
   	    		if(!empty($q_info))
				{
   	    			if($q_info['hidden']==1)
					{
						unset($q_info['hidden']);
						try
						{
							$this->set_search($q_info);
						}
						catch(Exception $e)
						{ 	    				
							send_AIC('http://scadmin.5173.com/index.php?admin_question/handle_answer_tag.html','搜索服务器异常',1,'搜索接口');
						}						
					}
					else
					{
						$this->delete_search($q_info['id']);
					}
   	    		}
   	    	}
   	    	if($log_flag)
			{
   	    		$message .= '';
   	    		$tag_new = $_ENV['tag']->getNameById();
   	    		if(!empty($tag_arr_old))
				{
   	    			foreach($tag_arr_old as $k => $v)
					{
   	    				if(array_key_exists($v, $tag_new))
						{
   	    					$message .= '['.$tag_new[$v].']';
   	    				}  	    				
   	    			}
   	    		}
   	    		$message .= '【修改为】';
   	    		if(!empty($tag_arr))
				{
   	    			foreach($tag_arr as $k => $v)
					{
   	    				if(array_key_exists($v, $tag_new))
						{
   	    					$message .= '['.$tag_new[$v].']';
   	    				}
   	    			}
   	    		}  	    		  	    		
   	    		$this->sys_admin_log($question_id,$this->ask_login_name,$message,7);//系统操作日志
   	    	} 	    	
   	    	exit('1');       	    	
       }
	   else
	   {
       	    exit('0');
       }
    }
    
    //选择标签列表显示
    function onajaxhandle_answer_tag_list()
	{
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');	
       if($question_id)
	   {
       	   $tag_list = $_ENV['tag']->get_categories_list($question_id);
       }      
       exit($tag_list);
    }

	    //联系状态显示
    function onajaxhandle_comm_status()
	{
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');	
       if($question_id)
	   {
       	   $questionInfo = $_ENV['question']->get($question_id);
		   $Comm_status = $questionInfo['Comm_status'];
			$Comm_status_List = $this->ask_config->getCommStatus();		  
		  $return_arr = "<select name = 'comm_status' id = 'comm_status'>";
		  foreach($Comm_status_List as $key => $value)
		  {
			if($key >= 0)
			{
				$return_arr.= "<option value = $key ".($Comm_status==$key?"selected=1":"").">$value</option>";
			}			
		  }
		  $return_arr.="</select>";
       }      
       exit($return_arr);
    }
	    //联系状态更新
    function onhandle_comm_status()
	{
	   $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');	      
	   if($question_id)
	   {
			$hasHandleCommStatusPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleCommStatus");
			!$hasHandleCommStatusPrivilege['return'] && exit('-1');
			$update = $_ENV['question']->updateQuestion($question_id,array('Comm_status'=>intval($this->post['comm_status'])));
			exit($update);
       }      
    }

	//订单号更新页面
    function onajaxhandle_order_update()
	{
       $question_id = $this->post['qid'];
	   if($question_id)
	   {
			$questionInfo = $_ENV['question']->get($question_id);
			if($questionInfo['author_id']=='')
			{
				$UserInfo = $_ENV['question']->getUserInfo($questionInfo['author']);
				$questionInfo['author_id'] = $UserInfo['UserId'];
			}
			if($questionInfo['author_id']=='')
			{
				$questionInfo['author_id'] = '用户不存在';
			}
			$QtypeInfo = $_ENV['qtype']->GetQType($questionInfo['qtype']);
			$TradingComment = unserialize($QtypeInfo['trading']);
			$Comment = unserialize($questionInfo['comment']);
			if(strlen($TradingComment['sellerOrderUrl'])>0)
			{
				$OrderTypeList[2] = "卖家订单";
			}
			if(strlen($TradingComment['buyerOrderUrl'])>0)
			{
				$OrderTypeList[1] = "买家订单";
			}
			if(strlen($TradingComment['sellingOrderUrl'])>0)
			{
				$OrderTypeList[3] = "卖出中订单";
			}
			ksort($OrderTypeList);
			include template('order_update','admin');
       }      
       exit($return_arr);
    }
    function onajax_order_update()
	{
       $question_id = $this->post['qid'];
	   $order_id = trim($this->post['order_id']);
	   if($question_id)
	   {
			$questionInfo = $_ENV['question']->get($question_id);
			$Comment = unserialize($questionInfo['comment']);
			
			if($order_id!='-1' && $order_id!='0')
			{
				$t = explode('|',$order_id);
				$order_id = $t['0'];
				$game_id = $t['1'];
				$operator_id = $t['2'];
				$area_id = $t['3'];
				$server_id = $t['4'];				
			}
			if($Comment['order_id']==$order_id)
			{
				$return_arr = 1;
			}
			else
			{
				$GameName = $_ENV['question']->getFCDInfo($game_id,0);
				$AreaName = $_ENV['question']->getFCDInfo($area_id,1);
				$ServerName = $_ENV['question']->getFCDInfo($server_id,2);
				$OperatorName = $_ENV['question']->getFCDInfo($operator_id,7);
				$Comment['order_id'] = $order_id;
				$updateArr = array('gameid'=>$game_id,'game_name'=>$GameName,'operatorid'=>$operator_id,'operator_name'=>$OperatorName,'areaid'=>$area_id,'area_name'=>$AreaName,'serverid'=>$server_id,'server_name'=>$ServerName,'comment'=>serialize($Comment));
				$update = $_ENV['question']->updateQuestion($question_id,$updateArr);
				if($update)
				{
					$return_arr = 1;
					$_ENV['question']->rebuildQuestionDetail($question_id,"question");
				}
				else
				{
					$return_arr = 0;
				}
			}
       }
       echo $return_arr;
    }
    function onajax_game_update()
	{
       $question_id = $this->post['qid'];
	   if($question_id)
	   {
			$gameid = trim($this->post['hide_game']);
			$operatorid = trim($this->post['hide_operator']);
			$areaid = trim($this->post['hide_area']);
			$serverid = trim($this->post['hide_server']);
			$questionInfo = $_ENV['question']->get($question_id);
			$updateArr = array('gameid'=>$gameid,'game_name'=>($gameid!='0' && $gameid!='')?trim($this->post['gs_game']):'',
								'operatorid'=>$operatorid,'operator_name'=>($operatorid!='0' && $operatorid!='')?trim($this->post['gs_operator']):'',
								'areaid'=>$areaid,'area_name'=>($areaid!='0' && $areaid!='')?trim($this->post['gs_area']):'',
								'serverid'=>$serverid,'server_name'=>($serverid!='0' && $serverid!='')?trim($this->post['gs_server']):'',
								);
			
			$update = $_ENV['question']->updateQuestion($question_id,$updateArr);
			if($update)
			{
				$return_arr = 1;
			}
			else
			{
				$return_arr = 0;
			}			
       }
       echo $return_arr;
    }

	function onajaxordersadmin()
	{		
		$page = 1;
		$qtypeInfo = $_ENV['qtype']->GetQType(intval($this->post['qtype']));
		$qtypeInfo['trading'] = unserialize($qtypeInfo['trading']);
		$Order = array();
		$checked = in_array(trim($this->post['old_order']),array(0,-1))?' selected = "selected" ':'';

		$Order[0] = '<option value = -1 '.$checked.'>'.无订单."</option>";

		ksort($Order);
		if($this->post['type'] == 1 || $this->post['type'] == 2)
		{
			//我购买的商品
			if($this->post['type'] == 1)
			{
				$url = $qtypeInfo['trading']['buyerOrderUrl'];				
			}
			else
			{
				$url = $qtypeInfo['trading']['sellerOrderUrl'];
			}
			$url .= "&uid=".$this->post['author']."&ps=20&p=".$page;
			$this->post['start_date'] != '' && $url .= '&mindate='.$this->post['start_date'];
			$this->post['end_date'] != '' && $url .= '&maxdate='.$this->post['end_date'];
			$url .= '&ts='.$qtypeInfo['trading']['ServiceType'];
			$this->post['dd'] != '' && $url .= '&oc='.$this->post['dd'];
			
			$rs = get_url_contents($url);
			echo $url."<br>";
			$result = json_decode($rs,true);
			 if(!empty($result['OrderList']))
			 {
				foreach ($result['OrderList'] as $OrderList)
				{
					$checked = trim($OrderList['Id'])==trim($this->post['old_order'])?' selected = "selected" ':'';
					$Order[$OrderList['Id']] = '<option value = '.$OrderList['Id'].'|'.$OrderList['GameId'].'|'.$OrderList['OperatorId'].'|'.$OrderList['AreaId'].'|'.$OrderList['ServerId'].' '.$checked.'>'.$OrderList['Id'].'/'.$OrderList['GameName'].'/'.$OrderList['AreaName'].'/'.$OrderList['ServerName']."</option>";
				}
			 }
		}
		elseif($this->post['type'] == 3)
		{//我的发布单
			$url = $qtypeInfo['trading']['sellingOrderUrl'];
			$url .= "?uid=".$this->post['author']."&ps=5&p=".$page;
			$url .= '&ts='.$qtypeInfo['trading']['ServiceType'];
			$this->post['start_date'] != '' && $url .= '&mindate='.$this->post['start_date'];
			$this->post['end_date'] != '' && $url .= '&maxdate='.$this->post['end_date'];
			$rs = get_url_contents($url);
			$result = json_decode($rs,true);
			if(!empty($result['BizofferList']))
			{
				foreach ($result['BizofferList'] as $OrderList)
				{
					$checked = trim($OrderList['Id'])==trim($this->post['old_order'])?' selected = "selected" ':'';
					$Order[$OrderList['Id']] = '<option value = '.$OrderList['Id'].'|'.$OrderList['GameId'].'|'.$OrderList['OperatorId'].'|'.$OrderList['AreaId'].'|'.$OrderList['ServerId'].' '.$checked.'>'.$OrderList['Id'].'/'.$OrderList['GameName'].'/'.$OrderList['AreaName'].'/'.$OrderList['ServerName']."</option>";
				}
			}
		}


		$str = implode("",$Order);
		echo $str;
	}
 
    //协助处理 handleAnswerAid:协助处理权限
    function onhandle_answer_aid() 
    {  	
		$hasHandleAnswerAidPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerAid");
		if($hasHandleAnswerAidPrivilege['return'] != 1)
		{
			exit('7');
		}
	   
	    $did    = isset($this->post['did']) ? intval($this->post['did']) : 0;
	    $aid_id = isset($this->post['aid_id']) ? intval($this->post['aid_id']) : 0;
		$aid_content = isset($this->post['aid_content']) ? trim($this->post['aid_content']) : "";
		if($did ==0 || $aid_id ==0) exit('0');	//部门与协助人没获取到
        $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
		$q_info = $_ENV['question']->Get($question_id);
		if(empty($q_info)) 
        {
        	exit('5');	//问题没找到
        }
        if($q_info['revocation'] == 1)
        {
        	exit('4'); // 问题已撤销
        }
        if($q_info['is_hawb'] == 0)
        {
        	exit('3');	// 问题没分单
        }
        if($q_info['js_kf'] == $aid)
        {
        	exit('9'); // 不能协助给接手客服只能撤销
        }
		if($q_info['status'] != 1)
		{
			exit('10'); // 未回答的问题可以协助
		}
		$operatorInfo = $_ENV['operator']->get($aid_id);
		if(($operatorInfo['ishandle']==1) && ($operatorInfo['isonjob']==1) && ($operatorInfo['ishelp']==1))
		{
			$applyForAid = $_ENV['question']->applyForAid($question_id,$aid_id);
			if($applyForAid)
			{
				$time = time();
				$Comment = unserialize($q_info['comment']);
				$Comment['transfer'][count($Comment['transfer'])+1] = array('transfer_time'=>$time,'from_operator'=>$this->ask_login_name,'to_operator'=>$operatorInfo['login_name'],'transfer_reason'=>$aid_content);
				$_ENV['question']->updateQuestion($question_id,array('comment'=>serialize($Comment)));
				$AidInfo = array('transfer_time'=>$time,'from_operator'=>$this->ask_login_name,'to_operator'=>$operatorInfo['login_name'],'qid'=>$question_id,'transfer_reason'=>$aid_content,'cid'=>$q_info['cid'],'cid1'=>$q_info['cid1'],'cid2'=>$q_info['cid2'],'cid3'=>$q_info['cid3'],'cid4'=>$q_info['cid4']);
				$_ENV['question']->insertAidLog($AidInfo);
				//写日志
				$this->sys_admin_log($question_id,$this->ask_login_name,'客服'.$this->ask_login_name.'将问题'.$question_id.'转单给了客服'.$operatorInfo['login_name'].",理由:".$aid_content,14);//系统操作日志
				exit($applyForAid);
			}
			else
			{
				exit('6');
			}
			
		}
		else
		{
			exit('11'); // 被协助人不在班或者不可接单
		}
    }
    //获取协助处理部门用户列表
    function onajaxhandle_get_did() {
    	if(isset($this->post['did'])){
    		$did_user = $_ENV['operator']->get_aid_by_did($this->post['did'],$this->ask_login_name);
    		$did_user_str ='';
    		if(!empty($did_user)){
    			foreach($did_user as $k=>$v){
    				$did_user_str .='<option value="'.$k.'">'.$v.'</option>';
    			}
    		}else{$did_user_str .='<option value="0">无</option>';}
    		exit($did_user_str);
    	}else{
    		exit('0');
    	}
    		
    }
    //协助处理部门列表显示
    function onajaxhandle_answer_help() {
       $help_dep = $_ENV['department']->get_categrory_tree(); 
       exit($help_dep);

    }
      
    //撤销问题 handleAnswerRevocation:撤销问题权限
    function onhandle_answer_revocation() 
    {
	   $hasHandleAnswerRevocationPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerRevocation");
	   !$hasHandleAnswerRevocationPrivilege['return']  && exit('3');
	   
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');    
          
       if($question_id && $this->ask_login_name)
       {
       	   $q_info = $_ENV['question']->Get($question_id);
       	   if(!empty($q_info))
       	   {
       	   	   if($q_info['revocation']  == 1) exit('4'); // 问题已撤销
       	   	   if($q_info['help_status'] == 1) exit('2'); // 协助处理状态的问题不能被撤销
       	   	   
       	   	   // 增加事务
       	   	   $this->db->begin();
       	   	   
       	   	   $questionInfo = array('revocation'=>1,
       	   	   'rev_man'=>$this->ask_login_name,
       	   	   'revocation_time'=>time());
       	   	   
       	   	   $_ENV['question']->updateQuestion( $question_id, $questionInfo );
			   $updateQuestionNum = $this->db->affected_rows();
			   
       	   	   //1.3处理
       	   	   $updateAuthor_numNum = 0;
       	   	   if($q_info['js_kf'] != '' && $q_info['status'] == 1)
       	   	   {
       	   	   	    if($q_info['pid']==0)
       	   	   	    {
       	   	   	        $this->db->query ( "UPDATE ask_author_num SET num=num-1 WHERE author='".$q_info['js_kf']."'" );//更新客服的接单量
       	   	   	    }
       	   	   	    else
       	   	   	    {
                        $this->db->query ( "UPDATE ask_author_num SET num_add=num_add-1 WHERE author='".$q_info['js_kf']."'" );//更新客服的接单量
                    }
       	   	   		$updateAuthor_numNum = $this->db->affected_rows();
       	   	   }
       	   	   else
       	   	   {
       	   	    	$updateAuthor_numNum = 1 ;
       	   	   }
       	   	   
       	   	   if($updateQuestionNum > 0 && $updateAuthor_numNum > 0)
       	   	   {
       	   	   		$this->db->commit();
       	   	   		$this->sys_admin_log($question_id,$this->ask_login_name,$this->ask_login_name.'撤销了问题',10);//系统操作日志
       	   	   		//删除Solr服务器上的对应的问题id
       	   	   		try{
       	   	   			$this->delete_search($question_id);
       	   	   		}catch(Exception $e){
       	   	   			exit('1');//Solr服务器没有更新成功
       	   	   		}
       	   	   		exit('1');
       	   	   }
       	   	   else 
       	   	   {
       	   	   	  $this->db->rollBack();
       	   	   	  exit('5');
       	   	   }
       	   }
       	   else 
       	   {
       	   	exit('0');
       	   }    	   
       }
       else
       {
       	   exit('0');
       }
    }
    
    //开启问题 handleAnswerUse:开启问题权限
    function onhandle_answer_use() 
    {
	   $hasHandleAnswerUsePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerUse");
	   !$hasHandleAnswerUsePrivilege['return']  && exit('3');
	   
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
       if($question_id && $this->ask_login_name)
       {
       	   $q_info = $_ENV['question']->Get($question_id);
       	   if(!empty($q_info))
       	   {   
				if($q_info['revocation'] == 0) exit('2');
				// 新增加事务处理
				$this->db->begin();
					   
				$questionInfo = array('revocation'=>0,
					'start_man'=>$this->ask_login_name,
					'start_time'=>time());

				$_ENV['question']->updateQuestion( $question_id, $questionInfo );
				$updateQuestionNum = $this->db->affected_rows();
			   
				$updateAuthor_numNum = 0;
				//1.3处理
				if($q_info['js_kf'] != '')
				{
				   if($q_info['status'] == 1)
				   {
					   if($q_info['pid']==0)
					   {
						   $this->db->query ( "UPDATE ask_author_num SET num=num+1 WHERE author='".$q_info['js_kf']."'" );//更新客服的接单量
					   }
					   else 
					   {
						   $this->db->query ( "UPDATE ask_author_num SET num_add=num_add+1 WHERE author='".$q_info['js_kf']."'" );//更新客服的接单量 
					   }
					   $updateAuthor_numNum = $this->db->affected_rows();
				   }
				   else 
				   {
						$updateAuthor_numNum = 1;
				   }
				}
				else
				{
				 $updateAuthor_numNum = 1;
				}
	       	   
				if($updateQuestionNum > 0 && $updateAuthor_numNum > 0)
				{
					$this->db->commit();
					$this->sys_admin_log($question_id,$this->ask_login_name,$this->ask_login_name.'开启了问题',10); //系统操作日志
					//更新Solr服务器
					if($q_info['pid'] == 0)
					{
						$q_search = $_ENV['question']->Get_Search_Data($question_id);
						if(!empty($q_search))
						{
							if($q_search['hidden']==1)
							{
								unset($q_search['hidden']);
								try
								{
									$this->set_search($q_search);
								}
								catch(Exception $e) 
								{
									send_AIC('http://scadmin.5173.com/index.php?admin_question/handle_answer_use.html','搜索服务器异常',1,'搜索接口');
								}						
							}
							else
							{
								$this->delete_search($q_search['id']);
							}
						}
					}
					exit('1');
				}
				else
				{
					$this->db->rollBack();
					exit('0');
				}
       	   }
       	   else 
       	   {
       	   	 exit('0');	
       	   }
       }
       else
       {
       	   exit('0');
       }
    }
    
    //显示完整记录 handleAnswerAll:显示完整记录权限
    function onhandle_answer_all() 
	{
	   $hasHandleAnswerAllPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerAll");
	   $hasHandleAnswerAllPrivilege['url'] = "?admin_question/handle";
	   !$hasHandleAnswerAllPrivilege['return']  && __msg($hasHandleAnswerAllPrivilege);
	   
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
	   if($question_id){
       	   $this->load("log");//载入日志模型
       	   $log_list = $_ENV['log']->Get_List($question_id);
       }
       include template('record','admin');
    }
    
    //历史提问 handleAnswerHistory:历史提问权限
    function onhandle_answer_history()
	{
		$hasHandleAnswerHistoryPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerHistory");
		$hasHandleAnswerHistoryPrivilege['url'] = "?admin_question/handle";
	   !$hasHandleAnswerHistoryPrivilege['return']  && __msg($hasHandleAnswerHistoryPrivilege);
	   
       $question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');
       if($question_id){
       	    $cat = $_ENV['category']->getNameById();	
       	    $q_arr = $_ENV['question']->Get($question_id);
       	    if(!empty($q_arr)){
       	    	$q_list = $_ENV['question']->Get_History($q_arr['author']);   	    	
       	    }
       } 
       include template('history','admin');

    }
    
    //协助处理记录 handleAnswerAidRecord:协助处理记录权限
    function onhandle_answer_aid_record()
	{
		$hasHandleAnswerAidRecordPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAnswerAidRecord");
		$hasHandleAnswerAidRecordPrivilege['url'] = "?admin_question/handle";
	   !$hasHandleAnswerAidRecordPrivilege['return']  && __msg($hasHandleAnswerAidRecordPrivilege);
	   
       if(isset($this->ask_login_name)){
			$question_id = isset($_COOKIE['view_question_id'])?$_COOKIE['view_question_id']:(isset($_COOKIE['ask_question_id'])?$_COOKIE['ask_question_id']:'');

			$where = " WHERE h.applicant='".$this->ask_login_name."'";
			$questionInfo = $_ENV['question']->Get($question_id);
			if($questionInfo['author']!="游客" && $questionInfo['author']!="")
			{$where.= " and q.author = '".$questionInfo['author']."'";} 
			$h_list = $_ENV['help']->getAidRecord($where);
       } 
       include template('aidrecord','admin');

    }
	/* 
		我的问题
		intoMy:进入查看我的问题页面
		myExport:导出我的问题数据权限
	*/
    function onmy($msg='', $ty='')
	{
    	$hasIntoMyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoMy");
		$hasIntoMyPrivilege['url'] = "?admin_main";
	   !$hasIntoMyPrivilege['return']  && __msg($hasIntoMyPrivilege);
	   
    	$ask_start_time_search = isset($this->post['ask_start_time']) && false != strtotime($this->post['ask_start_time'])?
    		strtotime($this->post['ask_start_time']):(isset($this->get[2])?$this->get[2]:$_ENV['question']->_getSETime(1));
    	
    	$ask_end_time_search = isset($this->post['ask_end_time']) && false != strtotime($this->post['ask_end_time'])?
    		strtotime('+1 day',strtotime($this->post['ask_end_time']))-1:(isset($this->get[3])?$this->get[3]:$_ENV['question']->_getSETime(2));
    	
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
    	
    	$qid_search = isset($this->post['qid']) && $this->post['qid']!=''?
    		intval($this->post['qid']):(isset($this->get[14])?$this->get[14]:'');
    	
    	$operator_search = isset($this->post['operator']) && $this->post['operator']!=''?$this->post['operator']:(isset($this->get[15])?
    		urldecode($this->get[15]):(isset($this->ask_login_name)?$this->ask_login_name:''));
    	
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
    	
    	$r_site = isset($this->post['r_site']) && $this->post['r_site']!=-1?
    	    intval($this->post['r_site']):(isset($this->get[23])?intval($this->get[23]):-1);
    	
    	$where = $_ENV['question']->Get_Where($ask_start_time_search,$ask_end_time_search,$wait_start_time_search,$wait_end_time_search,$answer_start_time_search
    	,$answer_end_time_search,$question_start_time_search,$question_end_time_search,$revocation_search,$que_status_search,$question_search,$assess_search,
    	$qid_search,$operator_search,$user_name_search,$question_title_search,$display_method,$category_search,$order_search,$help_search,$all_kf,$r_site);  

        @$page      = max(1, intval($this->get[24]));   	
        $pagesize   = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;  
        $rownum     = $_ENV['question']->Get_Num($where,$all_kf); 
        $question_list = $_ENV['question']->Get_All_Question($where,true,$all_kf,$startindex, $pagesize);
		foreach($question_list as $key => $value)
		{
		    $question_list[$key]['description'] = cutstr($value['description'],30);    
		}
        $departstr = page($rownum, $pagesize, $page, "admin_question/my/$ask_start_time_search/$ask_end_time_search/$wait_start_time_search/$wait_end_time_search" .
        		"/$answer_start_time_search/$answer_end_time_search/$question_start_time_search/$question_end_time_search/$revocation_search/$que_status_search/$question_search" .
        		"/$assess_search/$qid_search/$operator_search/$user_name_search/$question_title_search/$display_method/$category_search" .
        		"/$order_search/$help_search/$all_kf/$r_site");
        $_my = array();
        $_my['ask_start_time_search']=$ask_start_time_search;
        $_my['ask_end_time_search']=$ask_end_time_search;
        $_my['wait_start_time_search']=$wait_start_time_search;
        $_my['wait_end_time_search']=$wait_end_time_search;
        $_my['answer_start_time_search']=$answer_start_time_search;
        $_my['answer_end_time_search']=$answer_end_time_search;
        $_my['question_start_time_search']=$question_start_time_search;
        $_my['question_end_time_search']=$question_end_time_search;
        $_my['revocation_search']=$revocation_search;
        $_my['que_status_search']=$que_status_search;
        $_my['question_search']=$question_search;
        $_my['assess_search']=$assess_search;
        $_my['qid_search']=$qid_search;
        $_my['operator_search']=$operator_search;
        $_my['user_name_search']=$user_name_search;
        $_my['question_title_search']=$question_title_search;
        $_my['display_method']=$display_method;
        $_my['category_search']=$category_search;
        $_my['order_search']=$order_search;
        $_my['help_search']=$help_search;
        $_my['all_kf_search']=$all_kf;
        $_my['r_site_search']=$r_site;
        $_my['num']=$rownum;
        $_SESSION['my_session'] = $_my;
    	$question_status = $this->ask_config->getQuestion();
    	$que_status = $this->ask_config->getQueStatus();
    	$revocation_status = $this->ask_config->getRevocation();
    	$assess_status = $this->ask_config->getAssess();
    	$category_option = $_ENV['category']->get_categrory_tree($category_search);
    	$help_status = $this->ask_config->getHelpStatus();
    	
    	$msg && $message = $msg;
    	$ty && $type = $ty;
    	
        include template('quemy','admin');

    }
    // myExport:导出我的问题数据权限
    function onmy_export()
	{
	   $hasMyExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myExport");
	   if( $hasMyExportPrivilege['return'])
	   {
			if($_SESSION['my_session']['num'] >= 10000){
				$this->onmy('数据量太大，请重新筛选条件后进行导出！',"errormsg");
				exit;
			}
			require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("问题ID","5173帐号","问题描述","问题分类","提问时间","回答客服","接手时间","回答时间","回复时长","处理状态","协助处理","评价状态","浏览量","问题的状态","来源站点","游戏名称","回复内容");
			array_push($export,$export_header);
			$cat = $_ENV['category']->getNameById();
			$_my = $_SESSION['my_session'];
			$where = $_ENV['question']->Get_Where($_my['ask_start_time_search'],$_my['ask_end_time_search'],$_my['wait_start_time_search'],
			$_my['wait_end_time_search'],$_my['answer_start_time_search'],$_my['answer_end_time_search'],$_my['question_start_time_search'],
			$_my['question_end_time_search'],$_my['revocation_search'],$_my['que_status_search'],$_my['question_search'],$_my['assess_search'],
			$_my['qid_search'],$_my['operator_search'],$_my['user_name_search'],$_my['question_title_search'],
			$_my['display_method'],$_my['category_search'],$_my['order_search'],$_my['help_search'],$_my['all_kf_search'],$_my['r_site_search']);
			$export_arr = $_ENV['question']->Get_All_Question($where,false,$_my['all_kf_search']);	
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
				$val['cid']  && $q_cat.='-'.$cat[$val['cid']].'-';
				$val['cid1'] && $q_cat.='-'.$cat[$val['cid1']].'-';
				$val['cid2'] && $q_cat.='-'.$cat[$val['cid2']].'-';
				$val['cid3'] && $q_cat.='-'.$cat[$val['cid3']].'-';
				$val['cid4'] && $q_cat.='-'.$cat[$val['cid4']].'-';
				$replay_range = getHour($val['Atime']-$val['receive_time']);
				$val['receive_time'] = empty($val['receive_time']) ? '' : date("Y-m-d H:i:s",$val['receive_time']);
				$val['Atime'] = empty( $val['Atime']) ? '' : date("Y-m-d H:i:s", $val['Atime']);
								$answer = $_ENV['answer']->get($val['id']);
						$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
						//$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
						
						$answer['content'] = strip_tags($answer['content']);	
				$export[]=array($val['id'],$val['author'],$val['description'],$q_cat,date("Y-m-d H:i:s",$val['time']),
				$val['Aauthor'],$val['receive_time'],$val['Atime'],$replay_range,$mange_status,$help_status,$asses,
						$val['views'],$q_status,$r_site,$val['game_name'],$answer['content']);
			}  	

			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('question'.date('Ymd'));
		}
		else
		{	
			$hasMyExportPrivilege['url'] = "?admin_question/my";
			__msg($hasMyExportPrivilege);
		}
    }
    /* 
		查看协助处理
		intoHelp:进入查看协助处理页面权限
		helpExport:导出协助处理数据权限
		helpSee：查看协助处理权限
		helpRevocation:撤销协助处理
	*/
    function onhelp($msg='', $ty='') 
	{
		$hasIntoHelpPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoHelp");
		$hasIntoHelpPrivilege['url'] = "?admin_main";
	   !$hasIntoHelpPrivilege['return']  && __msg($hasIntoHelpPrivilege);
	   
    	$start = isset($this->post['start_time']) && false != strtotime($this->post['start_time'])?strtotime($this->post['start_time']):
    		     (isset($this->get[2]) ? $this->get[2]:$_ENV['question']->_getSETime(1));
    	$end_time = isset($this->post['end_time']) && $this->post['end_time']!='' ? strtotime('+1 day',strtotime($this->post['end_time']))-1: 
    		       (isset($this->get[3]) ? $this->get[3] : $_ENV['question']->_getSETime(2));
    	$status = isset($this->post['status']) && $this->post['status'] != -1 ? intval($this->post['status']):
    		     (isset($this->get[4]) && $this->get[4] != -1 ? intval($this->get[4]) : -1);
    	$qid = isset($this->post['qid']) && $this->post['qid']!='' ? intval($this->post['qid']):
    		  (isset($this->get[5]) && $this->get[5] != '' ? intval($this->get[5]):-1);
    	$department = isset($this->post['department']) && $this->post['department'] != -1 ? intval($this->post['department']):
    		         (isset($this->get[6]) && $this->get[6] != -1 ? intval($this->get[6]):-1);
    	$aid_id = isset($this->post['aid']) && $this->post['aid']!=-1 ? intval($this->post['aid']):
    		     (isset($this->get[7]) && $this->get[7] != -1 ? intval($this->get[7]):-1);
    	$overdue = isset($this->post['overdue']) && $this->post['overdue']!=-1 ? intval($this->post['overdue']):
    		      (isset($this->get[8]) && $this->get[8] != -1 ? intval($this->get[8]) : -1);
    	$applicant = isset($this->post['applicant'])? $this->post['applicant']:(isset($this->get[9]) ? trim($this->get[9]):'');
    	
    	$where_search = $_ENV['help']->getWhere($start,$end_time,$status,$qid,$department,$aid_id,$overdue,$applicant);
    	
    	@$page      = max(1, intval($this->get[10]));
    	$pagesize   = $this->setting['list_default'];
    	$startindex = ($page - 1) * $pagesize;
    	$rownum   = $_ENV['help']->getNum($where_search);
    	$helplist = $_ENV['help']->getList($startindex, $pagesize,$where_search);
    	$helpstr  = page($rownum, $pagesize, $page, "admin_question/help/$start/$end_time/$status/$qid/$department/$aid_id/$overdue/$applicant");
    	
    	$_help = array();
    	$_help['start'] 	= $start;
    	$_help['end_time']  = $end_time;
    	$_help['status']  	= $status;
    	$_help['qid'] 	    = $qid;
    	$_help['department'] = $department;
    	$_help['aid_id']     = $aid_id;
    	$_help['overdue'] 	 = $overdue;
    	$_SESSION['help_session'] = $_help;
    	$department_select = $_ENV['department']->get_categrory_tree($department);
    	$help_aid =  $_ENV['operator']->get_help_aid();
    	 
    	$isOverdue = $this->ask_config->getOverdue();
    	$helpStatus = $this->ask_config->helpStatus();
    	$msg && $message = $msg;
    	$ty && $type = $ty;
    	include template('quehelp','admin');
    }
    // 导出协助处理 helpExport:导出协助处理数据权限
    function onhelp_export()
	{
	   $hasHelpExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpExport");
	   if( $hasHelpExportPrivilege['return'])
	   {
			require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();		 	
			$export_header = array("问题ID","申请人","协助人","状态 ","协助时间","协助完成时间","逾期");
			array_push($export,$export_header);
			$_help = $_SESSION['help_session'];
			$where_search = $_ENV['help']->getWhere($_help['start'] ,$_help['end_time'],$_help['status'],$_help['qid'],$_help['department'],$_help['aid_id'],$_help['overdue']);
			
			$export_arr = $_ENV['help']->getList('', '',$where_search,false);
			foreach($export_arr as $val){
				if($val['status']==1){
					$status = "已回复";
				}else{
					$status = "授理中";
				}
			
				if($val['overdue']==1){
					$overdue = "已逾期";
				}else{
					$overdue = "未逾期";
				}
				$export[]=array($val['qid'],$val['applicant'],$val['aid'],$status,$val['start'],$val['back_time'],$overdue);
			}
			
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('help'.date('Ymd'));
	   }
	   else
	   {
			$hasHelpExportPrivilege['url'] = "?admin_question/help";
			__msg($hasHelpExportPrivilege);
	   }
			
    }
	//helpSee：查看协助处理权限
	function onhelp_see()
	{
		$hasHelpSee = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpSee");
		echo (json_encode($hasHelpSee));
	}
	// 撤销协助处理单  helpRevocation:撤销协助处理
	function onhelp_revoke()
	{
		$hid = isset($this->post['hid']) ? intval($this->post['hid']) : '';
		$qid = isset($this->post['qid']) ? intval($this->post['qid']) : '';
		if($hid =='' || $qid=='') exit('0');
	
		$QuestionInfo = $_ENV['question']->Get($qid);
		$helpData = $this->db->fetch_first("SELECT aid,status FROM ".DB_TABLEPRE."help WHERE id=$hid");
		
		$helpData['status'] > 0 &&  exit('4'); // 协助单已经回复或撤销
		if($helpData['aid'] != $this->ask_login_name)
		{
			$hashelpRevocationPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "helpRevocation");
			if(!$hashelpRevocationPrivilege['return'])
			{
			    exit('3');
			}
		}			
		//新增加事务处理
		$this->db->begin();
		$questionInfo = array( 'help_status'=>0, 'display_h'=>0);
		$_ENV['question']->updateQuestion( $qid, $questionInfo);
		$updateQuestion = $this->db->affected_rows();
		
		$this->db->query("update ".DB_TABLEPRE."help set status=2,back_content='已撤销',back_time='' where id=$hid"); //更新该协助处理状态
        $updateHelp = $this->db->affected_rows();
        
        if($QuestionInfo['status'] == 1)
        {
        	if($QuestionInfo['pid']==0)
        	{
        		$reduce_num_sql =  "UPDATE ".DB_TABLEPRE."author_num SET num = num + 1 WHERE author ='".$QuestionInfo['js_kf']."' limit 1" ;
        	}
        	else
        	{
        		$reduce_num_sql =  "UPDATE ".DB_TABLEPRE."author_num SET num_add = num_add + 1 WHERE author ='".$QuestionInfo['js_kf']."' limit 1" ;
        	}
        	$this->db->query($reduce_num_sql);
        	$updateNum = $this->db->affected_rows();
        }
        else
        {
        	$updateNum = 1;
        }
		if(($updateQuestion>=0)&&($updateHelp>0)&&($updateNum>0))
		{
		    $this->db->commit();
       		exit('1');		
		}
		else 
		{
            $this->db->rollback();
            exit('0');	
        }
	}
    /* 
		我的协助处理 
		intoMyHelp:进入我的协助处理页面
		myHelpRevocation:撤销我的协助处理
		myhelpSee:查看我的协助处理
	*/
    function onmyhelp($msg='', $ty='') 
	{
		$hasIntoMyHelpPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoMyHelp");
		$hasIntoMyHelpPrivilege['url'] = "?admin_main";
	   !$hasIntoMyHelpPrivilege['return']  && __msg($hasIntoMyHelpPrivilege);
	   
    	$login_name = isset($this->ask_login_name)?trim($this->ask_login_name):exit('<h2>非法登录<h2>');
    	$start = isset($this->post['start_time']) && false != strtotime($this->post['start_time']) ? strtotime($this->post['start_time']): 
    		    (isset($this->get[2]) ? $this->get[2]: $_ENV['question']->_getSETime(1));
    	$end_time = isset($this->post['end_time']) && false != strtotime($this->post['end_time']) ? strtotime('+1 day',strtotime($this->post['end_time']))-1:
    	           (isset($this->get[3])?$this->get[3]:$_ENV['question']->_getSETime(2));
    	$status = isset($this->post['status']) ? intval($this->post['status']):(isset($this->get[4]) ? intval($this->get[4]):0);
    	$qid = isset($this->post['qid']) && $this->post['qid'] ? intval($this->post['qid']):
    		  (isset($this->get[5])&& $this->get[5]!='' ? intval($this->get[5]):'');
    	$overdue = isset($this->post['overdue']) ? intval($this->post['overdue']):(isset($this->get[6])?intval($this->get[6]):-1);

    	$where_search = $_ENV['help']->get_hwhere($start,$end_time,$status,$qid,$overdue,$login_name);
    	@$page        = max(1, intval($this->get[8]));
    	$pagesize     = $this->setting['list_default'];
    	$startindex   = ($page - 1) * $pagesize;
    	$rownum   = $_ENV['help']->get_hnum($where_search);
    	$helplist = $_ENV['help']->get_hlist($startindex, $pagesize,$where_search);
    	$helpstr  = page($rownum, $pagesize, $page, "admin_question/myhelp/$start/$end_time/$status/$qid/$overdue/$login_name");
    	
    	$isOverdue = $this->ask_config->getOverdue();
    	$helpStatus = $this->ask_config->helpStatus();
    	$is_help_replay = $this->setting['help_reply']; // 是否显示回复超链
    	$msg && $message = $msg;
    	$ty && $type = $ty;
    	include template('quemyhelp','admin');
    }
	
	  // 撤销我的协助处理单  myHelpRevocation:撤销我的协助处理
    function onmyhelp_revoke()
	{
	    $hid = isset($this->post['hid']) ? intval($this->post['hid']) : '';
		$qid = isset($this->post['qid']) ? intval($this->post['qid']) : '';
		if($hid =='' || $qid=='') exit('0');
	
		$QuestionInfo = $_ENV['question']->Get($qid);
		$helpData = $this->db->fetch_first("SELECT aid,status FROM ".DB_TABLEPRE."help WHERE id=$hid");
		
		if($helpData['status'] > 0 && $helpData['aid'] != $this->ask_login_name)
		{
			exit('4');// 协助单已经回复或撤销,或者不是本人协助单
		}
		$hashelpRevocationPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myHelpRevocation");
		if(!$hashelpRevocationPrivilege['return'])
		{
		    exit('3');
		}
		//新增加事务处理
		$this->db->begin();
		$questionInfo = array( 'help_status'=>0, 'display_h'=>0);
		$_ENV['question']->updateQuestion( $qid, $questionInfo);
		
		$updateQuestion = $this->db->affected_rows();
		$this->db->query("update ".DB_TABLEPRE."help set status=2,back_content='已撤销',back_time='' where id=$hid"); //更新该协助处理状态
        $updateHelp = $this->db->affected_rows();
        if($QuestionInfo['status'] == 1)
        {
        	if($QuestionInfo['pid']==0)
        	{
        		$reduce_num_sql =  "UPDATE ".DB_TABLEPRE."author_num SET num = num + 1 WHERE author ='".$QuestionInfo['js_kf']."' limit 1" ;
        	}
        	else
        	{
        		$reduce_num_sql =  "UPDATE ".DB_TABLEPRE."author_num SET num_add = num_add + 1 WHERE author ='".$QuestionInfo['js_kf']."' limit 1" ;
        	}
        	$this->db->query($reduce_num_sql);
        	$updateNum = $this->db->affected_rows();
        }
        else
        {
        	$updateNum = 1;
        }
        
		if(($updateQuestion>=0)&&($updateHelp>0)&&($updateNum>0))
		{
		    $this->db->commit();
       		exit('1');		
		}
		else 
		{
            $this->db->rollback();
            exit('0');	
        }
    }
	// 查看我的协助处理
    function onmyhelp_see()
	{    
		isset($this->get[2]) && $id = intval($this->get[2]);
		isset($this->get[3]) && $overdueTime = intval($this->get[3]);
		isset($this->get[4]) && $qid = intval($this->get[4]);
		$desc = $this->db->result_first("select description from " . DB_TABLEPRE . "question where id=$qid");
		if(empty($desc)){
			$desc='';
		}
		$myhelplist= $_ENV['help']->getHelpTime($id);
		include template('myhelp_feedback','admin');

    }
	// 回复我的协助处理权限：myhelpAnswer(2013年11月28日11:38:18 后期补上)
	function onmyhelp_answer()
	{
		$hasMyHelpAnswer = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myhelpAnswer");
		echo (json_encode($hasMyHelpAnswer));
	}
		
	
	// myhelpSee:查看我的协助处理权限
	function onmyhelp_see_privilege()
	{
		$hasMyhelpSee = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myhelpSee");
		echo (json_encode($hasMyhelpSee));
	}
    /* 
		我发起的协助 
		intoMyInitiateHelp:进入我发起的协助处理页面
		myInitiateHelpSee:查看我发起的协助处理
	*/
    function onmyInitiateHelp($msg='', $ty='')
	{
	
		$hasIntoMyInitiateHelpPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoMyInitiateHelp");
		$hasIntoMyInitiateHelpPrivilege['url'] = "?admin_main";
		!$hasIntoMyInitiateHelpPrivilege['return'] && __msg($hasIntoMyInitiateHelpPrivilege);
		
    	$login_name = isset($this->ask_login_name)?trim($this->ask_login_name):exit('<h2>非法登录<h2>');
    	$start = isset($this->post['start_time']) && false != strtotime($this->post['start_time']) ? strtotime($this->post['start_time']):
    			(isset($this->get[2]) ? $this->get[2]: $_ENV['question']->_getSETime(1));
    	$end_time = isset($this->post['end_time']) && false != strtotime($this->post['end_time']) ? strtotime('+1 day',strtotime($this->post['end_time']))-1:
    			   (isset($this->get[3])?$this->get[3]:$_ENV['question']->_getSETime(2));
    	$status = isset($this->post['status']) ? intval($this->post['status']):(isset($this->get[4]) ? intval($this->get[4]):-1);
    	$qid = isset($this->post['qid']) && $this->post['qid'] ? intval($this->post['qid']):
    	(isset($this->get[5])&& $this->get[5]!='' ? intval($this->get[5]):'');
    	$overdue = isset($this->post['overdue']) ? intval($this->post['overdue']):(isset($this->get[6])?intval($this->get[6]):-1);
    	
    	$where_search = $_ENV['help']->get_hwhere($start,$end_time,$status,$qid,$overdue,$login_name,$type=2);
    	@$page = max(1, intval($this->get[8]));
    	$pagesize = $this->setting['list_default'];
    	$startindex = ($page - 1) * $pagesize;
    	$rownum = $_ENV['help']->get_hnum($where_search);
    	$helplist= $_ENV['help']->get_hlist($startindex, $pagesize,$where_search);
    	$helpstr = page($rownum, $pagesize, $page, "admin_question/myInitiateHelp/$start/$end_time/$status/$qid/$overdue/$login_name");
    	 
    	$isOverdue  = $this->ask_config->getOverdue();
    	$helpStatus = $this->ask_config->helpStatus();
    	
    	$msg && $message = $msg;
    	$ty && $type = $ty;
    	include template('my_initiate_help','admin');
    }
	 // myInitiateHelpSee:查看我发起的协助处理
	function onmyInitiateHelp_see()
	{
		$hasMyInitiateHelpSee = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "myInitiateHelpSee");
		echo (json_encode($hasMyInitiateHelpSee));
	}
	
    // 我的协助处理反馈内容,现在没有，后期可能会加上改功能 
    function onmyhelp_content(){
    	$id = isset($this->post['id']) ? intval($this->post['id']) : 0;
    	$qid = isset($this->post['qid']) ? intval($this->post['qid']) : 0;
    	$back_content = isset($this->post['back_content']) ? htmlspecialchars($this->post['back_content']):'';
    	if($id == 0 || $back_content == '' || $id== 0){
    		echo '<script>alert("非法操作");window.history.back()</script>';
    	}else{
    		$time = time();
    		$_ENV['help']->update_helpcontent($id,$back_content,$time);
    		// 反馈之后重新分单
    		$questionInfo = array('status'=>1, 'is_hawb'=>0, 'help_status'=>1, 'display_h'=>0);
    		$_ENV['question']->updateQuestion( $qid, $questionInfo );
    		echo '<script>alert("提交成功");window.history.back();window.close();</script>';
    	}
    }
    // 查看协助处理跑秒
    function onhelp_time()
	{
    	isset($this->get[2]) && $id = intval($this->get[2]);
    	isset($this->get[3]) && $overdueTime = intval($this->get[3]);
    	$helplist= $_ENV['help']->getHelpTime($id);
    	include template('help_time','admin');
    }

    // 编辑问题标题和描述 handleQuestionEdit:问题编辑权限
    function onhandle_question_edit()
	{
		$hashandleQuestionEditPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleQuestionEdit");
	   !$hashandleQuestionEditPrivilege['return']  && exit('2');
	   
    	$q_description = isset($this->post['q_description']) ? htmlspecialchars($this->post['q_description']):'';
    	$q_id = isset($this->post['q_id']) ? intval($this->post['q_id']):0;	
    	if($q_id !=0 )
		{
    		$q_arr = $_ENV['question']->Get($q_id);
    		if($q_arr['id'])
			{
				$questionInfo = array('title'=>$q_title, 'description'=>$q_description);
				$update = $_ENV['question']->updateQuestion( $q_id, $questionInfo );				
				if($update)
				{
					//更新Solr服务器
					$q_info = $_ENV['question']->Get_Search_Data($q_id);
					if(!empty($q_info))
					{			
						if($q_info['hidden']==1)
						{
							unset($q_info['hidden']);
							try
							{
								$this->set_search($q_info);
							}
							catch(Exception $e) 
							{
								send_AIC('http://scadmin.5173.com/index.php?admin_question/handle_question_edit.html','搜索服务器异常',1,'搜索接口');
							}							
						}
						else
						{
							$this->delete_search($q_info['id']);
						}
					}
					$str = '';
					if($q_description != ''){
						$str .= $this->ask_login_name.'编辑了描述，由【'.$q_arr['description'].'】修改为【'.$q_description.'】;';
					}
					
					$this->sys_admin_log($q_id,$this->ask_login_name,$str,9);//系统操作日志
					exit('1');					
				}
				else
				{
					exit('0');	
				}		
			}
			else
			{
				exit('0');	
			}
    	}
		else 
		{
    		exit('0');
    	}
    }
    
    // 更新问题的处理状态，同时把该问题的子问题评价状态跟改为0 
	// handleHasManage: 已处理权限
	// handleNoManage : 未处理权限
	
    function onhandle_has_manage()
	{
    	isset($this->post['qid']) && $qid = $this->post['qid'];
    	isset($this->post['type']) && $q_handle_status = intval($this->post['type']);
		
		// 未处理操作
		if($q_handle_status == 0)
		{
			$hasHandleNoManagePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleNoManage");
			!$hasHandleNoManagePrivilege['return'] && exit('3');
			$this->db->query("update ". DB_TABLEPRE . "question set q_handle_status=0,is_pj=0 where id=$qid or pid=$qid");
			exit('1'); // 执行成功
		}
		else // 已处理操作
		{
			$hasHandleHasManagePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleHasManage");
			!$hasHandleHasManagePrivilege['return'] && exit('3');			
			$questionInfo = array('q_handle_status'=>1);
			$_ENV['question']->updateQuestion( $qid, $questionInfo );
			exit('1'); // 执行成功
		}
		
    }
    function onajaxismanage(){
    	isset($this->post['id']) && $id = $this->post['id'];
    	$status = $this->db->result_first("select status from ". DB_TABLEPRE . "help where id=$id");
    	if(empty($status)){
    		exit('0');
    	}else{
    		exit('1');
    	}
    }
  
   // 子问题修改 handleZwQuestionEdit:修改追问权限
    function onhandle_zwquestion_edit()
	{
		$hasHandleAnsweAttachDeletePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAttachDelete");
	   !$hasHandleAnsweAttachDeletePrivilege['return']  && exit('3');
	   
    	$zq_title = isset($this->post['zq_title']) ? htmlspecialchars($this->post['zq_title']):'';
    	$zw_id = isset($this->post['zw_id']) ? intval($this->post['zw_id']):0;
    	if($zw_id !=0 )
		{
    		$q_arr = $_ENV['question']->Get($zw_id);
    		if($q_arr['id'])
			{
				$questionInfo = array('description'=>$zq_title);
				$update = $_ENV['question']->updateQuestion( $zw_id, $questionInfo );
				if($update)
				{
					//更新Solr服务器
					$q_info = $_ENV['question']->Get_Search_Data($zw_id);
					if(!empty($q_info))
					{
						if($q_info['hidden']==1)
						{
							unset($q_info['hidden']);
							try
							{
								$this->set_search($q_info);
							}
							catch(Exception $e) 
							{
								send_AIC('http://scadmin.5173.com/index.php?admin_question/handle_zwquestion_edit.html','搜索服务器异常',1,'搜索接口');
							}							
						}
						else
						{
							$this->delete_search($q_info['id']);	
						}
					}
					$str = '';  		
					if($zq_title != ''){
						$str .= $this->ask_login_name.'编辑了子问题标题，由【'.$q_arr['description'].'】修改为【'.$zq_title.'】;';
					}					
					$this->sys_admin_log($zw_id,$this->ask_login_name,$str,9);//系统操作日志
					exit('1');					
				}			
			}
			else
			{
				exit('0');	
			}			
    	}
		else 
		{
			exit('0');
		}
    }
    // 删除附件 handleAttachDelete:删除附件权限
    function onhandle_attach_dt()
	{
		$hasHandleAnsweAttachDeletePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleAttachDelete");
	   !$hasHandleAnsweAttachDeletePrivilege['return']  && exit('3');
	   
    	$id = isset($this->post['id']) ? intval($this->post['id']) : 0;
    	$attach = isset($this->post['attach']) ? trim($this->post['attach']) : '';
    	if($id !=0 && attach !='')
    	{
    		$questionInfo = array('attach'=>'');
    		$_ENV['question']->updateQuestion( $id, $questionInfo );
    		require_once TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php'; 				
    		$FastDFSClient = new FastDFSClient();
    		$FastDFSClient->delete('sk',$attach);
    		$this->sys_admin_log($id,$this->ask_login_name,$this->ask_login_name.'删除了问题附件',12);//系统操作日志
    		exit('1');
        } 
        else 
        {
        	exit('0');
        }
    }
    // 放入垃圾箱 权限 handleDustbin
    function onhandle_dustbin()
    {      
    	$hasHandleDustbin = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "handleDustbin");
    	!$hasHandleDustbin['return'] && exit('3');
    	
    	$id = intval($this->get[3]);
    	$reasonId = intval($this->get[2]);
    	 
    	$data = $this->db->fetch_first("SELECT id,comment from ask_question WHERE id=$id");
    	
    	if(!empty($data))
    	{
    		$cid = $_ENV['question']->getType(4);
    		if($cid > 0)
    		{
    			$comment = unserialize($data['comment']);
    			$reason = array('重复提问','用户要求删除','数据测试','欺诈广告','恶意信息');
    			$comment['reason'] = $reason[$reasonId];
    			
    			$questionInfo = array('cid'=>$cid,
    			'cid1'=>0,
    			'cid2'=>0,
    			'cid3'=>0,
    			'cid4'=>0,
    			'comment'=>serialize($comment));
    			$_ENV['question']->updateQuestion( $id, $questionInfo );
    			
    			$this->sys_admin_log($id,$this->ask_login_name,$this->ask_login_name.'把问题'.$id.'放入垃圾箱',13);//系统操作日志
    			exit('1'); // 成功
    		}
    		else
    		{
    			exit('2'); // 没有垃圾箱这个分类
    		}
    	}
    	else
    	{
    		exit('4'); // id不存在
    	}
    }
}
?>

