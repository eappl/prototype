<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_configcontrol extends base {

    function admin_configcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("setting");
        $this->load("category");
        $this->load("tag");
        $this->load("gag");
        $this->load("common_question");
        $this->load("banner");
        $this->load("answer");
        $this->load("operator");
        $this->load("worktime");
        $this->load("question");
        $this->load("qtype");  
		$this->load("menu"); 
    }

    function ondefault() {
        $this->onconsult();
    }
	/*    
	intoConsult:进入咨询配置页面
	updateConsult:更新咨询配置权限
	咨询配置
   	 更新咨询配置权限：updateConsult 
	*/
    function onconsult() 
	{
		$setting = $this->setting = $this->cache->reload('setting');
		$hasIntoConsultPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoConsult");
		$hasUpdateConsultPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "updateConsult");
		if ( $hasIntoConsultPrivilege['return'] )
		{
			$set_array = array();
			if(isset($this->post['submit']))
			{
				if(!empty($this->post['limit_question_num']))
					isset($this->post['number']) && $set_array['limit_question_num'] = intval($this->post['number']);
				else
					$set_array['limit_question_num'] = 0;
					
				if(!empty($this->post['limit_question_num_add']))
					isset($this->post['number_add']) && $set_array['limit_question_num_add'] = intval($this->post['number_add']);
				else
					$set_array['limit_question_num_add'] = 0;
					
				if(!empty($this->post['limit_assess_num']))
					isset($this->post['assess_num']) && $set_array['limit_assess_num'] = intval($this->post['assess_num']);
				else
					$set_array['limit_assess_num'] = 0;
					 
					
				isset($this->post['answer_time']) &&  $set_array['answer_time'] = intval($this->post['answer_time']); 
				isset($this->post['answer_template']) &&  $set_array['answer_template'] = str_replace(" ", "&nbsp;", htmlspecialchars($this->post['answer_template']));
				
				
				if(!empty($this->post['help_reply']))
					$set_array['help_reply'] = intval($this->post['help_reply']);
				else
					$set_array['help_reply'] = 0;  	

				$set_array['ts_warn_num'] =  intval($this->post['ts_warn_num']);
				$set_array['ts_warn_time'] =  intval($this->post['ts_warn_time']);
				$set_array['ts_warn_maxNum'] =  intval($this->post['ts_warn_maxNum']);
				
				$set_array['askSuggestTransComplain'] =  intval($this->post['askSuggestTransComplain']);
				$set_array['complainTransAskSuggest'] =  1;//intval($this->post['complainTransAskSuggest']);
				
				$set_array['complainSwitch'] =  intval($this->post['complainSwitch']);
				
				$set_array['complainReasonSwitch'] =  intval($this->post['complainReasonSwitch']);
				$set_array['helpReApply'] =  intval($this->post['helpReApply']);
				$set_array['telDisplay'] =  intval($this->post['telDisplay']);
				$set_array['qqDisplay'] =  intval($this->post['qqDisplay']);
				$set_array['xnDisplay'] =  intval($this->post['xnDisplay']);
				$set_array['xn_siteid'] =  ($this->post['xn_siteid']);
				$set_array['xn_sellerid'] =  ($this->post['xn_sellerid']);
				$set_array['xn_default_settingid'] =  ($this->post['xn_default_settingid']);
				$set_array['IpBlackList'] =  trim($this->post['IpBlackList']);
				$set_array['selfServiceFirst'] =  intval($this->post['selfServiceFirst']);
				$set_array['assessOverTimeLimit'] =  intval($this->post['assessOverTimeLimit']);
				// 是否有更新咨询配置权限
				if( $hasUpdateConsultPrivilege['return'])
				{
					$_ENV['setting']->update($set_array); 
					if($set_array['IpBlackList'] != $setting['IpBlackList'])
					{
						$ip = array('add'=>array(),'del'=>array(),'update'=>0);
						$new_list = explode('|',$set_array['IpBlackList']);
						$old_list = explode('|',$setting['IpBlackList']);
						foreach($new_list as $key => $value)
						{
							if(!in_array($value,$old_list))
							{
								$ip['add'][] = $value;
								$ip['update'] ++;
							}
						}
						foreach($old_list as $key => $value)
						{
							if(!in_array($value,$new_list))
							{
								$ip['del'][] = $value;
								$ip['update'] ++;
							}
						}
						if($ip['update']>0)
						{
							$text = "添加IP:".implode("|",$ip['add']).",删除IP:".implode("|",$ip['del']);						
							$this->sys_admin_log(0,$this->ask_login_name,$text,16);//系统操作日志
						}
					}
					$setting = $this->setting = $this->cache->reload('setting');
					$backReturn['url'] = "?admin_config/consult";
					$backReturn['comment'] = '数据更新成功';
					__msg($backReturn);
				}
				else
				{
					$hasUpdateConsultPrivilege['url'] = "?admin_config/consult";
					__msg($hasUpdateConsultPrivilege);
				}
				         
			}
				
			include template('consult','admin');
		}
		else
		{
			$hasIntoConsultPrivilege['url'] = "?admin_main";
			__msg($hasIntoConsultPrivilege);
		}
    }
	/* 
	intoSort:进入分类维护页面
	sortAdd:添加分类
	sortModify:修改分类
	sortRemove:删除分类
	分类维护 
	*/
    function onsort($msg='', $ty='',$_cid='') 
	{
	   $hasIntoSortPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoSort");
		if ( $hasIntoSortPrivilege['return'] )
		{
		   
		   $cid_zx = $_ENV['question']->getType(1); // 咨询id
		   $cid_zx = !empty($cid_zx)?intval($cid_zx):-1;
		   $cid    = isset($_cid) && $_cid != '' ? $_cid : (isset($this->get[2])?intval($this->get[2]):$cid_zx);
		   $op_cid = $_ENV['question']->_getCid(0,$cid);
		   $categorylist = $_ENV['category']->get_categories_list($cid);    
           $question_type_list = $this->ask_config->getQuestionType();
           $qtypelist = $_ENV['qtype']->GetAllQType(0,"",0);

           foreach($categorylist as $key => $value)
           {
                $categorylist[$key]['comment'] = isset($question_type_list[$value['question_type']])?"对应问题分类[".$question_type_list[$value['question_type']]."]":"无对应问题分类";
                foreach($value['child'] as $key_child => $value_child)
                {
	                $categorylist[$key]['child'][$key_child]['comment'] = isset($qtypelist[$value_child['qtype']]['name'])?"对应主分类[".$qtypelist[$value_child['qtype']]['name']."]":"无对应主分类"; 
                }            
        
           }
           		   
		   $msg && $message = $msg;
		   $ty && $type = $ty;
		   include template('consort','admin'); 
		}
		else
		{
			$hasIntoSortPrivilege['url'] =  "?admin_main";
			__msg($hasIntoSortPrivilege);
		}
    }
	// 添加分类
	// 添加分类权限：sortAdd
     function onsort_add()
	 {
	    $hasSortAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "sortAdd");
		if ( $hasSortAddPrivilege['return'] )
		{
			 $name = isset($this->post['name']) ? trim($this->post['name']) : '';
			 $question_type = isset($this->post['question_type']) ? trim($this->post['question_type']) : '';
			 $pcid = intval($this->post['pcid']);
			 $qtype = intval($this->post['qtype']) ? intval($this->post['qtype']) : 0;
			 if(isset($this->post['cid']))
			 {       	
				if ($name == '') 
				{
					$this->onsort("分类名称不能为空","errormsg",$pcid);
				} 
				else 
				{
					$_ENV['category']->add($name,$qtype,$question_type,$this->post['cid']);
					$this->onsort("子分类添加成功",'',$pcid);
				}
			 } 
			 else 
			 {        	
				if ($name == '')
				{
					$this->onsort("分类名称不能为空","errormsg",$pcid);
				}
				else
				{
					$_ENV['category']->add($name,$qtype,$question_type,$this->post['cid']); 
					$this->onsort("一级分类添加成功",'',$pcid);
				}      	
			 } 
		}
		else
		{
			$hasSortAddPrivilege['url']  = "?admin_config/sort";
			__msg($hasSortAddPrivilege);
		}
     }

	 // 修改分类
	 // 修改分类权限：sortModify
    function onsort_modify() 
	{
	    $hasSortModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "sortModify");
		if ( $hasSortModifyPrivilege['return'] )
		{
			 if (isset($this->post['cid']))
			 {        	
				 $pcid = intval($this->post['pcid']);
				 $name = isset($this->post['name']) ? trim($this->post['name']) : '';
    			 $question_type = isset($this->post['question_type']) ? trim($this->post['question_type']) : '';
				 $qtype = intval($this->post['qtype']) ? intval($this->post['qtype']) : 0;
				 if ($name == '')
				 {
					 $this->onsort("分类名称不能为空","errormsg",$pcid);
				 } else 
				 {
					 $_ENV['category']->set($this->post['cid'],$name,$qtype,$question_type);
					 $this->onsort("修改分类成功",'',$pcid);
				 }
			 }
		}
		else
		{
			$hasSortModifyPrivilege['url'] = "?admin_config/sort";
			__msg($hasSortModifyPrivilege);
		}
    }
    // 分类删除
    // 分类删除权限：sortRemove
	
    function onsort_remove()
	{
	    $hasSortRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "sortRemove");
		if ( $hasSortRemovePrivilege['return'] )
		{
			 $sortId = isset($this->post['cid']) ? intval($this->post['cid']) : 0;
			 if ( $sortId)
			 {            
				 $_ENV['category']->remove($this->post['cid']);                           
			 }
		}
		else
		{
			exit('3'); // 没有删除分类权限
		}
    }
	

	/* 
		首页栏目维护
		intoPart:进入首页栏目维护页面
		partBannerAdd:添加banner
		partBannerRemove:删除banner
		partBannerModify:修改banner
		partBannerDisplay:更新banner到首页
		partCommonDisplay:更新常见问题到首页
		partHotQuestionAdd:热门问题配置
	*/
    function onpart($msg='', $ty='',$id='')
	{
       
	    $hasIntoPartPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoPart");
		if($hasIntoPartPrivilege['return'])
		{
		   if($id == '')
		   {
			$hotQuestion = $this->db->result_first("SELECT v FROM ".DB_TABLEPRE."setting WHERE k='hotQue'");
			$hotQuestion = explode(',',$hotQuestion);
		   }
		   else
		   {
			 $hotQuestion = $id;
		   }
		 
		   $common_list = $_ENV['common_question']->get_common_list(); 
		   $banner_list = $_ENV['banner']->get_banner_list(); 
		   $msg && $message = $msg;
		   $ty && $type = $ty;
		   include template('part','admin');
		}
		else
		{
			$hasIntoPartPrivilege['url'] = "?admin_main";
			__msg($hasIntoPartPrivilege);
		}
    }
    /* 
	添加banner
	添加banner权限：partBannerAdd
	*/
    function onpart_banner_add() 
	{
    	
	    $hasPartBannerAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partBannerAdd");
		if ( $hasPartBannerAddPrivilege['return'] )
		{
			$msg = '';
			$pid  = isset($this->post['pid'])  ? intval($this->post['pid']) : 0;
			$title = isset($this->post['title']) ? trim($this->post['title'])  : '';
			$url = isset($this->post['url']) ? trim($this->post['url'])  : '';
			$number = isset($this->post['number'])  ? intval($this->post['number']) : 0;
			
			$title == '' && $msg .= 'banner标题不能为空 、';
			$url   == '' && $msg .= 'banner链接地址不能为空';
			if($msg == '')
			{
				$_ENV['banner']->add($title,$url,$pid,$number);
				$this->onpart("添加成功");
			}
			else
			{
				$this->onpart($msg,'errormsg');
			}
		}
		else
		{
			$hasPartBannerAddPrivilege['url'] = "?admin_config/part";
			__msg($hasPartBannerAddPrivilege);
		}
    	
    }
    /*判断banner下子问题数是否大于4*/
    function onbanner_pid(){
    	if(isset($this->post['bannerid'])){
    		$_ENV['banner']->get_banner_pid($this->post['bannerid']);
    	}
    }
    /*判读banner是否大于4*/
    function onbanner_bannerid(){
    		$_ENV['banner']->get_all_banner();
    }
    /*判断是否有banner子问题*/
    function onajaxbanner_pid() {
    	if(isset($this->post['bannerid'])){
    		$_ENV['banner']->get_bannerid($this->post['bannerid']);
    	}
    }
    /* 
		删除banner问题
		删除banner权限：partBannerRemove
	*/
    function onpart_banner_remove() 
	{
    	
	    $hasPartBannerRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partBannerRemove");
		if ( $hasPartBannerRemovePrivilege['return'] )
		{
			if(isset($this->post['bannerid']) && $this->post['bannerid'] !='')
			{
				$_ENV['banner']->remove($this->post['bannerid']);
				$this->onpart("问题删除成功");
			}
			else
			{
				$this->onpart("问题删除失败","errormsg");
			}
		}
		else
		{
			$hasPartBannerRemovePrivilege['url'] = "?admin_config/part";
			__msg($hasPartBannerRemovePrivilege);
		}
    }
    /* 
	编辑banner问题
	编辑banner权限：partBannerModify
	*/
    function onpart_banner_modify() 
	{
    	
	    $hasPartBannerModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partBannerModify");
		if ( $hasPartBannerModifyPrivilege['return'] )
		{
			$id    = isset($this->post['bannerid'])  ? intval($this->post['bannerid']) : 0;
			$title = isset($this->post['title']) ? trim($this->post['title'])  : '';
			$url   = isset($this->post['url']) ? trim($this->post['url'])  : '';
			$number   = isset($this->post['number']) ? trim($this->post['number'])  : 0;
			if($id == 0 || $title == '' || $url == '')
			{
				$this->onpart("修改失败,标题或url不能为空","errormsg");
			}
			else
			{
				$_ENV['banner']->set($id,$url,$title,$number);
				$this->onpart("修改成功");
			}
		}
		else
		{
			$hasPartBannerModifyPrivilege['url'] = "?admin_config/part";
			__msg($hasPartBannerModifyPrivilege);
		}
    }
    //更新banner到首页 partBannerDisplay:更新banner到首页权限
    function onpart_banner_display() 
	{
    	
		$hasPartBannerDisplayPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partBannerDisplay");
		if ( $hasPartBannerDisplayPrivilege['return'] )
		{
			$_ENV['banner']->updateToHome();
			$this->onpart("更新到首页成功！");
		}
		else
		{
			$hasPartBannerDisplayPrivilege['url'] = "?admin_config/part";
			__msg($hasPartBannerDisplayPrivilege);
		}	

    }
    //增加常见问题权限：partCommonAdd
    function onpart_common_add()
	{
    	
		$hasPartCommonAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partCommonAdd");
		if ( $hasPartCommonAddPrivilege['return'] )
		{
			if(isset($this->post['common_add']))
			{
				if($this->post['id'] > 0)
				{
					$_ENV['common_question']->set($this->post['id'],$this->post['common_number'][0],$this->post['common_url'][0],$this->post['common_title'][0]); 
					$this->onpart("数据修改成功！");
				}
				else
				{
				   $common_number_arr = $this->post['common_number'];
				   $common_url_arr = $this->post['common_url'];
				   $common_title_arr = $this->post['common_title'];
				   $common_count = count($common_number_arr);
				   if($common_count > 0)
				   {
						for($i=0;$i<$common_count;$i++)
						{
							$_ENV['common_question']->save_common($common_number_arr[$i],$common_url_arr[$i],$common_title_arr[$i]); 
						}
				   }
				   $this->onpart("数据保存成功！");
				}        
		   }
		 }
		else
		{
			$hasPartCommonAddPrivilege['url'] = "?admin_config/part";
			__msg($hasPartCommonAddPrivilege);
		}
    }

    //编辑常见问题权限：partCommonModify
    function onpart_common_modify()
	{     	
       	
	   $hasPartCommonModifyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partCommonModify");
		if ( $hasPartCommonModifyPrivilege['return'] )
		{
		   isset($this->get[2]) && $add_flag = false;
		   isset($this->get[2]) && $common_info = $_ENV['common_question']->get($this->get[2]);
		   $common_list = $_ENV['common_question']->get_common_list(); 
		   include template('part','admin');
		}
		else
		{
			$hasPartCommonModifyPrivilege['url'] = "?admin_config/part";
			__msg($hasPartCommonModifyPrivilege);
		}

    }

    //删除常见问题权限： partCommonRemove
    function onpart_common_remove()
	{
        
	    $hasPartCommonRemovePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partCommonRemove");
		if ( $hasPartCommonRemovePrivilege['return'] )
		{
		   isset($this->get[2]) &&  $_ENV['common_question']->remove($this->get[2]);
		   $this->onpart("数据删除成功！");
		}
		else
		{
			$hasPartCommonRemovePrivilege['url'] = "?admin_config/part";
			__msg($hasPartCommonRemovePrivilege);
		}
    }

    //常见问题更新到首页 partCommonDisplay:更新常见问题到首页权限
    function onpart_common_display()
	{
    	
		$hasPartCommonDisplayPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partCommonDisplay");
		if ( $hasPartCommonDisplayPrivilege['return'] )
		{
			$_ENV['common_question']->updateToHome();
			$this->onpart("更新常见问题到首页成功！");
		}
		else
		{
			$hasPartCommonDisplayPrivilege['url'] = "?admin_config/part";
			__msg($hasPartCommonDisplayPrivilege);
		}
    }
	 /* 
	热门问题 
	partHotQuestionAdd:热门问题配置
	*/
    function onpart_hot_questionAdd()
	{
	   $hasPartHotQuestionAddPrivilege  = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "partHotQuestionAdd");
	   if( $hasPartHotQuestionAddPrivilege['return'] )
	   {
		   $hotQue = array();
		   $hotQue['0'] = isset($this->post['one']) && $this->post['one'] !='' ? intval($this->post['one']) : '';
		   $hotQue['1'] = isset($this->post['two']) && $this->post['two'] !='' ? intval($this->post['two']) :'';
		   $hotQue['2'] = isset($this->post['three']) && $this->post['three'] !='' ? intval($this->post['three']) : '';
		   $hotQue['3'] = isset($this->post['four']) && $this->post['four'] !='' ? intval($this->post['four']) : '';
		   $hotQue['4'] = isset($this->post['five']) && $this->post['five'] !='' ? intval($this->post['five']) : '';
		   
		   $hotQue['5'] = isset($this->post['six']) && $this->post['six'] !='' ?intval($this->post['six']) :'';
		   $hotQue['6'] = isset($this->post['seven']) && $this->post['seven'] !='' ? intval($this->post['seven']) : '';
		   $hotQue['7'] = isset($this->post['eight']) && $this->post['eight'] !='' ? intval($this->post['eight']) : '';
		   $hotQue['8'] = isset($this->post['nine']) && $this->post['nine'] !='' ? intval($this->post['nine']) : '';
		   $hotQue['9'] = isset($this->post['ten']) && $this->post['ten'] !='' ?  intval($this->post['ten']) : '';
		   
		   $hotQue['10'] = isset($this->post['eleven']) && $this->post['eleven'] !='' ?  intval($this->post['eleven']) : '';
		   $hotQue['11'] = isset($this->post['twelve']) && $this->post['twelve'] !='' ?  intval($this->post['twelve']) : '';
		   $hotQue['12'] = isset($this->post['thirteen']) && $this->post['thirteen'] !='' ? intval($this->post['thirteen']) : '';
		   $hotQue['13'] = isset($this->post['fourteen']) && $this->post['fourteen'] !='' ? intval($this->post['fourteen']) : '';
		   $hotQue['14'] = isset($this->post['fifteen']) && $this->post['fifteen'] !='' ? intval($this->post['fifteen']) : '';
		   
		   $hotQue['15'] = isset($this->post['sixteen']) && $this->post['sixteen'] !='' ? intval($this->post['sixteen']) : '';
		   $hotQue['16'] = isset($this->post['seventeen']) && $this->post['seventeen'] !='' ? intval($this->post['seventeen']) : '';
		   $hotQue['17'] = isset($this->post['eighteen']) && $this->post['eighteen'] !='' ? intval($this->post['eighteen']) : '';
		   $hotQue['18'] = isset($this->post['nineteen']) && $this->post['nineteen'] !='' ? intval($this->post['nineteen']) : '';
		   $hotQue['19'] = isset($this->post['twenty']) && $this->post['twenty'] !='' ? intval($this->post['twenty']) : '';	   
		   $hotQue_str = implode(',',$hotQue);
		   $hotQue1 = $hotQue;
		   foreach ($hotQue1 as $k => &$v){
			 if($v ==''){
				unset($hotQue1[$k]);
			 }
		   }
		   if(empty($hotQue1)){
			   $this->onpart('请输入问题ID!','errormsg');
			   exit;
		   } 	   
		   $hotQue2 = array_unique($hotQue1);
		   if(count($hotQue1) != count($hotQue2)){
			   $this->onpart('您输入的ID存在重复，请核对！','errormsg',$hotQue);
			   exit;
		   } 
		   $id = implode(',',$hotQue2); 
		   $all_id = $this->db->fetch_all("SELECT id FROM ".DB_TABLEPRE."question WHERE id in($id)");
		   if(count($all_id) != count($hotQue2)){
			 $this->onpart('输入的ID有的不存在，请核对！','errormsg',$hotQue);
		   }else{
			$this->db->query("REPLACE INTO ".DB_TABLEPRE."setting (k,v) VALUES ('hotQue','$hotQue_str')");
			 $this->onpart('热点问题添加成功！','');
		   }
	   }
	   else
	   {
			$hasPartHotQuestionAddPrivilege['url'] = "?admin_config/part";
			__msg($hasPartHotQuestionAddPrivilege);
	   }
    }
    /* 
		intoOrders:进入接单管理页面
		ordersSetIsBusy:接单客服理忙碌空闲设置
		ordersReset:重置接单量
	*/
    function onorders($msg='', $ty='')
	{
	    $hasIntoOrdersPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoOrders");
		if( $hasIntoOrdersPrivilege['return'] )
		{
			$login_name_search = isset($this->post['user_name']) && $this->post['user_name']!=''?trim($this->post['user_name']):(isset($this->get[2])?urldecode($this->get[2]):'');
			$busy_search = isset($this->post['busy']) && $this->post['busy']!=-1?intval($this->post['busy']):((isset($this->get[3]) && $this->get[3] != -1)?intval($this->get[3]):-1);
			$bill_search = isset($this->post['bill']) && $this->post['bill']!=1?intval($this->post['bill']):((isset($this->get[4]) && $this->get[4] != 1)?intval($this->get[4]):1);
			$having_flag = $bill_search == 0?true:false;
			$where_search = $_ENV['answer']->getWhere($login_name_search, $busy_search,$bill_search);
			@$page = max(1, intval($this->get[5]));
			$pagesize = $this->setting['list_default'];
			$startindex = ($page - 1) * $pagesize;
			$rownum = $_ENV['answer']->getNum($where_search);
			$answer_list = $_ENV['answer']->getList($startindex, $pagesize,$where_search);
			$departstr = page($rownum, $pagesize, $page, "admin_config/orders/$login_name_search/$busy_search/$bill_search");
			
			$msg && $message = $msg;
			include template('orders','admin');
		}
		else
		{
			$hasIntoOrdersPrivilege['url'] = "?admin_main";
			__msg($hasIntoOrdersPrivilege);
		}
    }
   //ordersSetIsBusy:接单客服理忙碌空闲设置权限
   function onorders_setIsBusy()
	{
		$hasordersSetIsBusyPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "ordersSetIsBusy");
		!$hasordersSetIsBusyPrivilege['return'] && exit('3');
		
    	$login_name = isset($this->post['author_name'])?trim($this->post['author_name']):'';
    	$isbusy = isset($this->post['isbusy'])?intval($this->post['isbusy']):-1;
    	
    	//工时利用统计逻辑处理
    	if($login_name != ''){
    		$info = $_ENV['operator']->getUser($login_name);//获取用户名信息
    		if(!empty($info)){
    			if($info['ishandle'] == 1){//只相对处理人员进行处理  				
    				if($info['isonjob'] == 1){//在班处理
    				    if($info['isbusy'] == 0){//数据库里读为空闲状态
    						if($isbusy == 1){
    							$busy_start = time();//记录点忙碌状态为是的时间
    							$istoday = $_ENV['worktime']->isToday($login_name,date('Y-m-d'));
    							if(false === $istoday){  								
    								$lasttoday = $_ENV['worktime']->lastToday($login_name);
    								if(false !== $lasttoday){
    									$last_job_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['onjob_start'];//统计前一天在班的在班时间
    									$job_time = $busy_start - strtotime(date('Y-m-d'));//统计当天在班的在班时间
    									$this->db->query("UPDATE ".DB_TABLEPRE."worktime SET onjob_time=onjob_time+".$last_job_time.",onjob_start='".strtotime(date('Y-m-d'))."' WHERE id=".$lasttoday['id']);//更新前一天在班的忙碌时间以及在班时间
    									$this->db->query("INSERT INTO ".DB_TABLEPRE."worktime SET login_name='".$login_name."',login_time='".date('Y-m-d')."',onjob_time=onjob_time+".$job_time.",onjob_start='".$busy_start."',busy_start='".$busy_start."'");//插入当天在班时间，并统计当天的忙碌时间以及在班时间
    								}   										
    							}else{
    								$this->db->query("UPDATE ".DB_TABLEPRE."worktime SET busy_start='".$busy_start."' WHERE id=".$istoday['id']);
    							}
    						}
    					}else{//数据库里读为忙碌状态
    						if($isbusy == 0){
    							$busy_end = time();//记录点忙碌状态为否的时间
    							$istoday = $_ENV['worktime']->isToday($login_name,date('Y-m-d'));
    							if(false === $istoday){//点忙碌与非忙碌不是同一天
    								$lasttoday = $_ENV['worktime']->lastToday($login_name);
    								if(false !== $lasttoday){
    									$last_busy_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['busy_start'];//统计前一天在班的忙碌时间
    									$last_job_time = strtotime($lasttoday['login_time']) + 24*3600 - $lasttoday['onjob_start'];//统计前一天在班的在班时间
    									$busy_time = $busy_end - strtotime(date('Y-m-d'));//统计当天在班的忙碌时间
    									$this->db->query("UPDATE ".DB_TABLEPRE."worktime SET busy_time=busy_time+".$last_busy_time.",onjob_time=onjob_time+".$last_job_time.",onjob_start='".strtotime(date('Y-m-d'))."',busy_start='".strtotime(date('Y-m-d'))."' WHERE id=".$lasttoday['id']);//更新前一天在班的忙碌时间
    									$this->db->query("INSERT INTO ".DB_TABLEPRE."worktime SET login_name='".$login_name."',login_time='".date('Y-m-d')."',onjob_time=onjob_time+".$busy_time.",busy_time=busy_time+".$busy_time.",onjob_start='".$busy_end."',busy_start='".$busy_end."'");//插入当天在班时间，并统计当天的忙碌时间
    								}
    							}else{//同一天处理
    								$busy_time = $busy_end - $istoday['busy_start'];
    								$job_time = $busy_end - $istoday['onjob_start'];
    								$this->db->query("UPDATE ".DB_TABLEPRE."worktime SET busy_time=busy_time+".$busy_time.",onjob_time=onjob_time+".$job_time.",onjob_start='".$busy_end."',busy_start='".$busy_end."' WHERE id=".$istoday['id']);
    							}
    						}
    					}
    				}
    			}
    		}
    	}   	
    	
    	if($login_name == '' || $isbusy == -1){
    		exit('0');
    	}else{
    		$data = $_ENV['operator']->set($login_name,$isbusy);
    		$data == 1 ? exit('1') : exit('2');
    	}
    }
	 // 重置单量 ordersReset:重置接单量权限
    function onorders_resetorder()
    {
		$hasOrdersResetPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "ordersReset");
		!$hasOrdersResetPrivilege['return'] && exit('3');
		
    	$name = isset($this->post['name']) ? trim($this->post['name']) : '';
    	if(!empty($name))
    	 {   
			$this->db->query("UPDATE ". DB_TABLEPRE . "author_num set num=0,num_add=0 where author='$name'");
			$this->db->query("UPDATE ".DB_TABLEPRE."question SET is_hawb=0,js_kf='',receive_time='' WHERE js_kf='$name' AND help_status=0 AND status=1 AND revocation=0 AND is_hawb=1");	
			$this->db->commit();
			exit('1');
    	}	
    	else
    	{
    		exit('0');
    	}
    }
    
	/* 
		intoGag:进入用户禁言管理页面
		gagAdd:添加禁言用户
		gagSearch:查询禁言用户
		gagRemove:删除禁言

	*/
    function ongag($msg='', $ty='')
	{
	    $hasIntoGagPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoGag");
		if( $hasIntoGagPrivilege['return'] )
		{
		   @$page = max(1, intval($this->get[3]));   	
		   $pagesize = 30;
		   $startindex = ($page - 1) * $pagesize;  
		   $rownum = $_ENV['gag']->getNum(); 
		   $gag_list = $_ENV['gag']->getList($startindex, $pagesize);
		   $departstr = page($rownum, $pagesize, $page, "admin_config/gag/$login_name_search");
		   
		   $msg && $message = $msg;
		   $ty && $type = $ty;
			
		   include template('gag','admin');
		}
		else
		{
			$hasIntoGagPrivilege['url'] = "?admin_main";
			__msg($hasIntoGagPrivilege);
		}
    }
    
    //禁言增加 gagAdd:添加禁言用户权限
    function ongag_add() 
	{
	    $hasGagAddPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "gagAdd");;
		if ( $hasGagAddPrivilege['return'] )
		{
		   if(isset($this->post['submit_add']))
		   {
				if(isset($this->post['login_name']))
				{
					$has_login_name = $this->db->fetch_total('gag',"login_name ='".$this->post['login_name']."'");
					if($has_login_name > 0)
					{
						$this->ongag("该用户已被禁言！","errormsg");
					}
					else
					{
						$time = time();
						$operator = $this->ask_login_name;
						$_ENV['gag']->add($this->post['login_name'],$operator,$time);
						$this->ongag("禁言成功！");
					}     	    	
				}
		   }    
		}
		else
		{
			$hasGagAddPrivilege['url'] = "?admin_config/gag";
			__msg($hasGagAddPrivilege);
		}
    }
    
    //禁言查询 gagSearch:查询禁言用户权限
    function ongag_search() 
	{
	    $hasGagSearchPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "gagSearch");
		if ( $hasGagSearchPrivilege['return'] )
		{
		   $login_name_search = $this->post['login_name']!=''?trim($this->post['login_name']):(isset($this->get[2])?urldecode($this->get[2]):'');
		   $where = $_ENV['gag']->getWhere($login_name_search);
		   @$page = max(1, intval($this->get[3]));   	
		   $pagesize = 30;
		   $startindex = ($page - 1) * $pagesize;  
		   $rownum = $_ENV['gag']->getNum($where); 
		   $gag_list = $_ENV['gag']->getList($startindex, $pagesize,$where);
		   $departstr = page($rownum, $pagesize, $page, "admin_config/gag_search/$login_name_search");
		   include template('gag','admin');
		}
		else
		{
			$hasGagSearchPrivilege['url'] = "?admin_config/gag";
			__msg($hasGagSearchPrivilege);
		}

    }
    
    //禁言删除 gagRemove:删除禁言权限
    function ongag_remove() 
	{
		$hasGagRemovPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "gagRemove");
		if ( $hasGagRemovPrivilege['return'] )
		{
		   isset($this->get[2]) && $_ENV['gag']->remove($this->get[2]);
		   $this->ongag("开通成功！");
		}
		else
		{
			$hasGagRemovPrivilege['url'] = "?admin_config/gag";
			__msg($hasGagRemovPrivilege);
		}

    }
    
    /* 
		工时统计利用率
		intoWorkTime:进入工时利用率页面
		worktimeExport:导出工时利用率数据
	*/
    function onworktime()
	{
		$hasIntoWorkTimePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoWorkTime");
		$hasIntoWorkTimePrivilege['url'] = "?admin_main";
	    !$hasIntoWorkTimePrivilege['return'] && __msg($hasIntoWorkTimePrivilege);
		
    	$start_time = isset($this->post['start_time'])?strtotime($this->post['start_time']):$_ENV['question']->_getTime(1);
    	$end_time = isset($this->post['end_time'])?strtotime("+1 day",strtotime($this->post['end_time']))-1:$_ENV['question']->_getTime(2);
    	$user_name = isset($this->post['user_name'])?$this->post['user_name']:'';
    	
    	$where = $_ENV['worktime']->get_where($start_time,$end_time,$user_name);
    	$work_list = $_ENV['worktime']->get_list($where);
    	$answer_list = $_ENV['answer']->get_count_by_author($start_time,$end_time,$user_name);
    	$time_data = array();  	
    	if(!empty($work_list)){
    		$all_total_line = 0;//统计全部在线时间
    		$all_total_busy = 0;//统计全部忙碌时间
    		$all_total_job = 0;//统计全部在班时间
    		$all_total_num = 0;//统计全部处理问题总数
    		$all_total_score = '';//统计全部工时利用率
    		foreach($work_list as $key => $work){
    			$line_time = $work['total_job'] - $work['total_busy'];
    			$time_data[$key]['login_name']=$work['login_name'];
    			$time_data[$key]['total_line']=$_ENV['worktime']->getHour($line_time);
    			$time_data[$key]['total_busy']=$_ENV['worktime']->getHour($work['total_busy']);
    			$time_data[$key]['total_job']=$_ENV['worktime']->getHour($work['total_job']);
    			if(array_key_exists($work['login_name'],$answer_list)){
    				$time_data[$key]['num']=$answer_list[$work['login_name']];
    			}else{
    				$time_data[$key]['num']=0;
    			}
    			if($work['total_job'] != 0){
    				$time_data[$key]['score'] = vsprintf("%01.2f", ($line_time/$work['total_job'])*100).'%';
    			}else{
    				$time_data[$key]['score'] = '';
    			}
    			
    			$all_total_line+=$line_time;
    			$all_total_busy+=$work['total_busy'];
    			$all_total_job+=$work['total_job'];
    			$all_total_num+=$time_data[$key]['num'];
    		}
    		if($all_total_job != 0){
    			$all_total_score = vsprintf("%01.2f", ($all_total_line/$all_total_job)*100).'%';
    		}
    		$all_total_line = $_ENV['worktime']->getHour($all_total_line);
    		$all_total_busy = $_ENV['worktime']->getHour($all_total_busy);
    		$all_total_job = $_ENV['worktime']->getHour($all_total_job);		
    	}
    	//准备导出数据
    	if(!empty($time_data)){
    		$export_time = array();
    		$export_time['data'] = $time_data;
    		$export_time['all_total_line'] = $all_total_line;
    		$export_time['all_total_busy'] = $all_total_busy;
    		$export_time['all_total_job'] = $all_total_job;
    		$export_time['all_total_num'] = $all_total_num;
    		$export_time['all_total_score'] = $all_total_score;
    		$_SESSION['export_time'] = $export_time;
    	}
    	include template('worktime','admin');
    }
    
    //工时统计利用率导出 worktimeExport:导出工时利用率数据
    function onworktime_export()
	{
		$hasWorktimeExportPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "worktimeExport");
		if( $hasWorktimeExportPrivilege['return'] )
		{
			require TIPASK_ROOT . '/lib/php_excel.class.php';
			$export = array();
			$export_header = array("用户名","在线时间","忙碌时间","在班时间","处理量","工时利用率");
			array_push($export,$export_header);
			$time = $_SESSION['export_time'];
			if(!empty($time)){
				foreach($time['data'] as $data){
					$export[]=array($data['login_name'],$data['total_line'],$data['total_busy'],$data['total_job'],$data['num'],$data['score']);
				}
				$export[]=array('合计',$time['all_total_line'],$time['all_total_busy'],$time['all_total_job'],$time['all_total_num'],$time['all_total_score']);
			}
			$xls = new Excel_XML('UTF-8', false, 'My Sheet');
			$xls->addArray($export);
			$xls->generateXML('export_time'.date('Ymd'));
			
		}
		else
		{
			$hasWorktimeExportPrivilege['url'] = "?admin_config/worktime";
			__msg($hasWorktimeExportPrivilege);
		}
    }
   
   
    /* 
		短信配置管理
		intoMessage:进入短信配置管理页面
		message:短信配置权限
	*/
    function onmessage()
	{
		$hasIntoMessagePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoMessage");
		$hasUpdateMessagePrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "message");
		if( $hasIntoMessagePrivilege['return'] )
		{
			$set_array=array();
			if(isset($this->post['submit']))
			{
				$msg_switch = $this->post['msg_switch'];
				$set_array['msg_switch_off'] = intval($msg_switch);
				$msg =  $this->post['msg_content'];
				!empty($msg) ? $set_array['msg_content'] = $msg : $message='短信内容不能为空';
				if ($message =='') 
				{ 
					// 是否有短信配置权限
					if( $hasUpdateMessagePrivilege['return'] )
					{
						$_ENV['setting']->update($set_array);
						$message = '数据更新成功';
					}
					else
					{
						$hasUpdateMessagePrivilege['url'] = "?admin_config/message";
						__msg($hasUpdateMessagePrivilege);
					}
					
				} 
				else 
				{
					$message .= '，数据更新失败';
					$type ='errormsg';
				}
			}
			$setting = $this->setting = $this->cache->load('setting');
			include template('message','admin');
		}	
		else
		{
			$hasIntoMessagePrivilege['url'] = "?admin_main";
			__msg($hasIntoMessagePrivilege);
		}
    }
    function ongetCheckedQtype()
    {
    	$id = isset($this->post['id']) ? intval($this->post['id']) : 0;
		// 获取顶层全部目录
        $qtypelist = $_ENV['qtype']->GetAllQType(1,"",0);
    	$qtypeOption  = '';
    	if($id)
    	{
		   // 根据分类id获取单个分类信息
    		$categoryInfo = $_ENV['category']->get($id);
        	if($categoryInfo['id']&&$categoryInfo['grade']==2)
        	{
        		if (!empty($qtypelist))
        		{
        			foreach ( $qtypelist as $v)
        			{
    				    // 判断是否该菜单是这个id的父级菜单，是选中
        				if($categoryInfo['qtype'] == $v['id'])
        				{
        					$qtypeOption .= "<option value='{$v['id']}' selected>{$v['name']}</option>";
        				}
        				else
        				{
        					$qtypeOption .= "<option value='{$v['id']}'>{$v['name']}</option>";
        				}
        				
        			}
        		}
        	}
    	}
    	else
    	{
			foreach ( $qtypelist as $v)
			{
				{
					$qtypeOption .= "<option value='{$v['id']}'>{$v['name']}</option>";
				}
				
			}            
        }    	
    	echo $qtypeOption;
    }
	
    function ongetCheckedQuestionType()
    {
    	$id = isset($this->post['id']) ? intval($this->post['id']) : 0;
		// 获取顶层全部目录
        $questiontypelist = $this->ask_config->getQuestionType();
    	$qtypeOption  = '';
    	if($id)
    	{
		   // 根据分类id获取单个分类信息
    		$categoryInfo = $_ENV['category']->get($id);
        	if($categoryInfo['id']&&$categoryInfo['grade']==1)
        	{
        		if (!empty($questiontypelist))
        		{
        			foreach ( $questiontypelist as $key => $value)
        			{
    				    // 判断是否该菜单是这个id的父级菜单，是选中
        				if($categoryInfo['question_type'] == $key)
        				{
        					$qtypeOption .= "<option value='$key' selected>{$value}</option>";
        				}
        				else
        				{
        					$qtypeOption .= "<option value='$key'>{$value}</option>";
        				}
        				
        			}
        		}
        	}
    	}
    	else
    	{
			foreach ( $questiontypelist as $key => $value)
			{				
				$qtypeOption .= "<option value='$key'>{$value}</option>";								
			}            
        }    	
    	echo $qtypeOption;
    }	
}
?>
