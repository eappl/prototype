<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_complaincontrol extends base {
    function admin_complaincontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("category");
        $this->load("question");
		$this->load("answer");
		$this->load("complain");
        $this->load("operator");
		$this->load("menu"); 
		$this->load("qtype"); 
    }

    function ondefault($message='') {
        $this->oncomplainView();
    }
    /* 
		intoComplainView:进入查看全部投诉页面
		查看全部投诉（已修改） 
	*/
    function oncomplainView($msg='', $ty='')
	{
		//只查询转为投诉的问题
		$ConditionList['transformed']=1;
		$action = "index.php?admin_complain/complainView";
		$hasIntoComplainViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoComplainView");
		$hasIntoComplainViewPrivilege['url'] = "?admin_main";
		!$hasIntoComplainViewPrivilege['return']  && __msg( $hasIntoComplainViewPrivilege );
		
    	
		$ConditionList['ComplainStartDate'] = isset($this->post['ComplainStartDate'])?$this->post['ComplainStartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-7*86400));   	
		$ConditionList['ComplainEndDate'] = isset($this->post['ComplainEndDate'])?$this->post['ComplainEndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
    	
    	// $ConditionList['AnswerStartDate'] = isset($this->post['AnswerStartDate'])?$this->post['AnswerStartDate']:(isset($this->get[4])?$this->get[4]:date("Y-m-d",time()-7*86400)); ;   	
		// $ConditionList['AnswerEndDate'] = isset($this->post['AnswerEndDate'])?$this->post['AnswerEndDate']:(isset($this->get[5])?$this->get[5]:date("Y-m-d",time()));   
    	
    	$ConditionList['AnswerStartDate'] = 0;   	
		$ConditionList['AnswerEndDate'] = 0; 
		
    	$ConditionList['author'] = isset($this->post['author'])?urldecode(trim($this->post['author'])):(isset($this->get[6])?urldecode(trim($this->get[6])):"");
		$ConditionList['author_id'] = isset($this->post['author_id'])?trim($this->post['author_id']):(isset($this->get[7])?trim($this->get[7]):"");
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim($this->post['operator_loginId']):(isset($this->get[8])?trim($this->get[8]):"");		
				
		$ConditionList['jid'] = isset($this->post['jid'])?intval($this->post['jid']):(isset($this->get[10])?intval($this->get[10]):0);
		$ConditionList['complainId'] = isset($this->post['complainId'])?intval($this->post['complainId']):(isset($this->get[9])?intval($this->get[9]):0);
		
		$statusList = $this->ask_config->getComStatus();
		$ConditionList['status'] = isset($this->post['status'])?intval($this->post['status']):(isset($this->get[11])?intval($this->get[11]):-2);
		
		$assessStatusList = $this->ask_config->getComAssessStatus();
		$ConditionList['Assess'] = isset($this->post['Assess'])?intval($this->post['Assess']):(isset($this->get[12])?intval($this->get[12]):-1);

		$SellerTypeList = $this->ask_config->getSellerType();
		$ConditionList['sid'] = isset($this->post['sid'])?intval($this->post['sid']):(isset($this->get[13])?intval($this->get[13]):-1);
		$J = $this->cache->get("Jlist_".$ConditionList['sid']);
		if(false != $J) 
		{
			$Jlist = json_decode($J,true);
		}
		else
		{
			$Jlist = $_ENV['complain']->getJList($ConditionList['sid']);
			$this->cache->set("Jlist_".$ConditionList['sid'],json_encode($Jlist),1800);
		}
		$PublicStatusList = $this->ask_config->getPublicStatus();
		@$page = max(1, intval($this->get[14]));
		$export = trim($this->get[15])=="export"?1:0;
		$setting = $this->setting;
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$complain_list = $_ENV['complain']->getComplainList($ConditionList,$page,$pagesize);
			foreach($complain_list['ComplainList'] as $key => $value)
			{
				$complain_list['ComplainList'][$key]['description'] = cutstr($value['description'],15);
				$S = $SellerTypeList;
				$complain_list['ComplainList'][$key]['sName'] = isset($S[$value['sid']])?$S[$value['sid']]:"尚未处理";
				$J = $this->cache->get("Jlist_0");
				if(false != $J) 
				{
					$J = json_decode($J,true);
				}
				else
				{
					$J = $_ENV['complain']->getJList(0);
					$this->cache->set("Jlist_0",json_encode($J),1800);
				}
				$complain_list['ComplainList'][$key]['jName'] = isset($J[$value['jid']])?$J[$value['jid']]:"其他分类";					
				$complain_list['ComplainList'][$key]['publicStatus'] = $PublicStatusList[$value['public']];
				$complain_list['ComplainList'][$key]['asnum'] = $assessStatusList[$value['assess']].($value['asnum']>0?"/".$value['asnum']."次":"");
				if($value['sync']==0)
				{
					$complain_list['ComplainList'][$key]['syncStatus'] = "未同步"; 				
				}
				elseif($value['sync']==1)
				{
					$complain_list['ComplainList'][$key]['syncStatus'] = "已同步"; 				
				}
				elseif($value['sync']<0)
				{
					$complain_list['ComplainList'][$key]['syncStatus'] = "失败".(-1*$value['sync'])."次/最大".$setting['ts_warn_maxNum']."次"; 				
				}
				$complain_list['ComplainList'][$key]['sync'] = $value['sync'];
				$Comment = unserialize($value['comment']);							
				if(in_array($value['status'],array(1,3)))
				{
					$answer = $_ENV['complain']->get_ComplainAnInfo($complain_list['ComplainList'][$key]['id']);
					$complain_list['ComplainList'][$key]['answer_loginId'] = $answer['contact'];					
				}
				if(in_array($value['status'],array(0,2,4)))
				{
					if($value['status']==2)
					{
						$complain_list['ComplainList'][$key]['title'] = '撤销理由:"'.($Comment['revoke']['revokeReason']?urldecode($Comment['revoke']['revokeReason']):'无').'"，客户端IP:'.($Comment['revoke']['ip']?$Comment['revoke']['ip']:'无');
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "用户于".date("Y-m-d H:i:s",$value['rtime'])."撤销";
					}
					else
					{
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $statusList[$value['status']];					
					}
				}
				else
				{					
					if($answer['time']>0)
					{						
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $this->timeLagToText($value['time'],$answer['time']);
					}
					else
					{
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "尚未回复";
					}					
				}
				$complain_list['ComplainList'][$key]['url'] = $value['public']==1?"已隐藏":"<a href = '".$_ENV['question']->getQuestionLink($value['id'],"complain")."' target='_BLANK'><查看></a>";
				$complain_list['ComplainList'][$key]['SyncInfo'] = $_ENV['complain']->get_ComplainSyncInfo($value['id']);
				$complain_list['ComplainList'][$key]['SyncInfo']['cpid'] = $complain_list['ComplainList'][$key]['SyncInfo']['cpid']?"SC000".$complain_list['ComplainList'][$key]['SyncInfo']['cpid']:"无";
				$BrowerInfo['BrowerInfo']['OS'] = "操作系统：".($Comment['OS']?$Comment['OS']:"未知");
				$BrowerInfo['BrowerInfo']['Browser'] = "浏览器：".($Comment['Browser']?$Comment['Browser']:"未知");
				
				$complain_list['ComplainList'][$key]['BrowerInfo'] = implode("，",$BrowerInfo['BrowerInfo']);
				
			}
			$departstr = page($complain_list['ComplainCount'], $pagesize, $page, "admin_complain/complainView/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['complainId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_complain/complainView/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['complainId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']."/".$page."/export");
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
			$FileName='投诉下载';
			$oExcel->download($FileName)->addSheet('投诉详情列表');
			//标题栏
			$title = array("SC投诉单号","交易/物品订单号","关联投诉单号","问题描述","用户名","身份类型","交易类型","投诉时间","回复时间","浏览量","是否隐藏","接手客服账号","评价","同步状态","回复客服","回复内容","撤销时间","撤销理由","撤销客户端IP","浏览器","操作系统");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$complain_list = $_ENV['complain']->getComplainList($ConditionList,$page,$pagesize);
				foreach($complain_list['ComplainList'] as $key => $value)
				{
					$complain_list['ComplainList'][$key]['author'] = str_replace('<x>','<?>',$value['author']);
					$value['description'] = preg_replace('/[\s"]/','',$value['description']);
					$value['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$value['description']);
					$complain_list['ComplainList'][$key]['description'] = strip_tags($value['description']);
					$complain_list['ComplainList'][$key]['qtypeName'] = isset($qtypeList[$value['qtype']])?$qtypeList[$value['qtype']]['name']:"其他分类";
					$complain_list['ComplainList'][$key]['publicStatus'] = $PublicStatusList[$value['public']];
					$complain_list['ComplainList'][$key]['asnum'] = $assessStatusList[$value['assess']].($value['asnum']>0?"/".$value['asnum']."次":"");				
					if($value['sync']==0)
					{
						$complain_list['ComplainList'][$key]['syncStatus'] = "未同步"; 				
					}
					elseif($value['sync']==1)
					{
						$complain_list['ComplainList'][$key]['syncStatus'] = "已同步"; 				
					}
					elseif($value['sync']<0)
					{
						$complain_list['ComplainList'][$key]['syncStatus'] = "失败".(-1*$value['sync'])."次/最大".$setting['ts_warn_maxNum']."次"; 				
					}
					$Comment = unserialize($value['comment']);	
					if(in_array($value['status'],array(1,3)))
					{
						$answer = $_ENV['complain']->get_ComplainAnInfo($complain_list['ComplainList'][$key]['id']);
						$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
						$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
						$answer['content'] = strip_tags($answer['content']);						
					}
					if(in_array($value['status'],array(0,2,4)))
					{
						if($value['status']==2)
						{
													
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "用户已撤销";//"用户于".date("Y-m-d H:i:s",$value['rtime'])."撤销";
							$complain_list['ComplainList'][$key]['RevokeTime'] = $value['rtime']?date("Y-m-d H:i:s",$value['rtime']):"无";
							$complain_list['ComplainList'][$key]['RevokeReason'] = $Comment['revoke']['revokeReason']?urldecode($Comment['revoke']['revokeReason']):"无";
							$complain_list['ComplainList'][$key]['RevokeIP'] = $Comment['revoke']['ip']?$Comment['revoke']['ip']:"无";
							$answer['content'] = "无";
						}
						else
						{
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $statusList[$value['status']];
						}						
					}
					else
					{						
						if($answer['time']>0)
						{						
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $this->timeLagToText($value['time'],$answer['time']);
						}
						else
						{
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "尚未回复";
						}																		
					}
					$J = $this->cache->get("Jlist_0");
					if(false != $J) 
					{
						$J = json_decode($J,true);
					}
					else
					{
						$J = $_ENV['complain']->getJList(0);
						$this->cache->set("Jlist_0",json_encode($J),1800);
					}
					$complain_list['ComplainList'][$key]['SyncInfo'] = $_ENV['complain']->get_ComplainSyncInfo($value['id']);
					$excelArr = array();
					$S = $SellerTypeList;
					$S['0'] = "尚未处理";
					$excelArr = array("id"=>$complain_list['ComplainList'][$key]['id'],
					"order_id"=>$complain_list['ComplainList'][$key]['order_id']?$complain_list['ComplainList'][$key]['order_id']:$complain_list['ComplainList'][$key]['goods_id'],
					"ComplainId"=>$complain_list['ComplainList'][$key]['SyncInfo']['scid']?"CS000".$complain_list['ComplainList'][$key]['SyncInfo']['cpid']:"",
					"description"=>$complain_list['ComplainList'][$key]['description'],
					"author"=>$complain_list['ComplainList'][$key]['author'],
					"sName"=>isset($S[$value['sid']])?$S[$value['sid']]:"尚未处理",
					"jName"=>isset($J[$value['jid']])?$J[$value['jid']]:"其他分类",
					"time"=>date("Y-m-d H:i:s",$complain_list['ComplainList'][$key]['time']),
					"AnswerTimeLag"=>$complain_list['ComplainList'][$key]['AnswerTimeLag'],
					"view"=>$complain_list['ComplainList'][$key]['view'],
					"publicStatus"=>$complain_list['ComplainList'][$key]['publicStatus'],
					"loginId"=>$complain_list['ComplainList'][$key]['loginId'],
					"asnum"=>$complain_list['ComplainList'][$key]['asnum'],
					"syncStatus"=>$complain_list['ComplainList'][$key]['syncStatus'],
					"answer_loginId"=>trim($answer['contact']),
					"answer"=>trim($answer['content'])==""?"无回复内容":$answer['content'],
					"RevokeTime"=>$complain_list['ComplainList'][$key]['RevokeTime'],
					"RevokeReason"=>$complain_list['ComplainList'][$key]['RevokeReason'],
					"RevokeIP"=>$complain_list['ComplainList'][$key]['RevokeIP'],
					"Browser"=>$Comment['Browser'],
					"OS"=>$Comment['OS']
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($complain_list['ComplainList']);
			}			 
			$oExcel->closeSheet()->close();	

		}
		include template('complainview','admin');        
    }
    /* 
		intoComplainView:进入查看全部投诉页面
		查看全部投诉（已修改） 
	*/
    function onrevokeComplainView($msg='', $ty='')
	{
		//只查询转为投诉的问题
		$ConditionList['transformed']=1;
		$action = "index.php?admin_complain/revokeComplainView";
		$hasIntoComplainViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoRevokeComplainView");
		$hasIntoComplainViewPrivilege['url'] = "?admin_main";
		!$hasIntoComplainViewPrivilege['return']  && __msg( $hasIntoComplainViewPrivilege );
		
    	
		$ConditionList['ComplainStartDate'] = isset($this->post['ComplainStartDate'])?$this->post['ComplainStartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-7*86400));   	
		$ConditionList['ComplainEndDate'] = isset($this->post['ComplainEndDate'])?$this->post['ComplainEndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
    	
    	
    	$ConditionList['AnswerStartDate'] = 0;   	
		$ConditionList['AnswerEndDate'] = 0; 
		
    	$ConditionList['author'] = isset($this->post['author'])?urldecode(trim($this->post['author'])):(isset($this->get[6])?urldecode(trim($this->get[6])):"");
		$ConditionList['author_id'] = isset($this->post['author_id'])?trim($this->post['author_id']):(isset($this->get[7])?trim($this->get[7]):"");
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim($this->post['operator_loginId']):(isset($this->get[8])?trim($this->get[8]):"");		
				
		$ConditionList['jid'] = isset($this->post['jid'])?intval($this->post['jid']):(isset($this->get[10])?intval($this->get[10]):0);
		$ConditionList['complainId'] = isset($this->post['complainId'])?intval($this->post['complainId']):(isset($this->get[9])?intval($this->get[9]):0);
		
		$ConditionList['status'] = 2;
		$ConditionList['Assess'] = -1;
		
		$SellerTypeList = $this->ask_config->getSellerType();
		$ConditionList['sid'] = isset($this->post['sid'])?intval($this->post['sid']):(isset($this->get[13])?intval($this->get[13]):-1);
		$J = $this->cache->get("Jlist_".$ConditionList['sid']);
		if(false != $J) 
		{
			$Jlist = json_decode($J,true);
		}
		else
		{
			$Jlist = $_ENV['complain']->getJList($ConditionList['sid']);
			$this->cache->set("Jlist_".$ConditionList['sid'],json_encode($Jlist),1800);
		}
		$ConditionList['reason'] = isset($this->post['reason'])?urldecode(trim($this->post['reason'])):(isset($this->get[14])?urldecode(trim($this->get[14])):"");
		@$page = max(1, intval($this->get[15]));
		$export = trim($this->get[16])=="export"?1:0;
		$setting = $this->setting;
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$complain_list = $_ENV['complain']->getComplainList($ConditionList,$page,$pagesize);
			foreach($complain_list['ComplainList'] as $key => $value)
			{
				$complain_list['ComplainList'][$key]['description'] = cutstr($value['description'],15);
				$S = $SellerTypeList;
				$complain_list['ComplainList'][$key]['sName'] = isset($S[$value['sid']])?$S[$value['sid']]:"尚未处理";
				$J = $this->cache->get("Jlist_0");
				if(false != $J) 
				{
					$J = json_decode($J,true);
				}
				else
				{
					$J = $_ENV['complain']->getJList(0);
					$this->cache->set("Jlist_0",json_encode($J),1800);
				}
				$complain_list['ComplainList'][$key]['jName'] = isset($J[$value['jid']])?$J[$value['jid']]:"其他分类";					

				$complain_list['ComplainList'][$key]['RevokeTime'] = date("Y-m-d H:i:s",$value['rtime']);

				$complain_list['ComplainList'][$key]['url'] = $value['public']==1?"已隐藏":"<a href = '".$_ENV['question']->getQuestionLink($value['id'],"complain")."' target='_BLANK'><查看></a>";
				$complain_list['ComplainList'][$key]['SyncInfo'] = $_ENV['complain']->get_ComplainSyncInfo($value['id']);
				$complain_list['ComplainList'][$key]['SyncInfo']['cpid'] = $complain_list['ComplainList'][$key]['SyncInfo']['cpid']?"SC000".$complain_list['ComplainList'][$key]['SyncInfo']['cpid']:"无";				
				$Comment = unserialize($value['comment']);
				if(isset($Comment['revoke']['revokeReason']))
				{
					$complain_list['ComplainList'][$key]['RevokeReason'] = $Comment['revoke']['revokeReason'];
				}
				else
				{
					$complain_list['ComplainList'][$key]['RevokeReason'] = "无理由";
				}
				if(isset($Comment['revoke']['ip']))
				{
					$complain_list['ComplainList'][$key]['RevokeIP'] = $Comment['revoke']['ip'];
				}
				else
				{
					$complain_list['ComplainList'][$key]['RevokeIP'] = "无IP";
				}
			}
			$departstr = page($complain_list['ComplainCount'], $pagesize, $page, "admin_complain/revokeComplainView/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['complainId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']."/".$ConditionList['reason']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_complain/revokeComplainView/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['complainId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']."/".$ConditionList['reason']."/".$page."/export");
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
			$FileName='已撤销投诉下载';
			$oExcel->download($FileName)->addSheet('投诉详情列表');
			//标题栏
			$title = array("SC投诉单号","交易/物品订单号","关联投诉单号","问题描述","用户名","身份类型","交易类型","投诉时间","撤销时间","撤销理由","撤销客户端IP","浏览器","操作系统");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$complain_list = $_ENV['complain']->getComplainList($ConditionList,$page,$pagesize);
				foreach($complain_list['ComplainList'] as $key => $value)
				{
					$complain_list['ComplainList'][$key]['author'] = str_replace('<x>','<?>',$value['author']);					
					$value['description'] = preg_replace('/[\s"]/','',$value['description']);
					$value['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$value['description']);
					$complain_list['ComplainList'][$key]['description'] = strip_tags($value['description']);
					$complain_list['ComplainList'][$key]['qtypeName'] = isset($qtypeList[$value['qtype']])?$qtypeList[$value['qtype']]['name']:"其他分类";
					$complain_list['ComplainList'][$key]['RevokeTime'] = date("Y-m-d H:i:s",$value['rtime']);
					
					$J = $this->cache->get("Jlist_0");
					if(false != $J) 
					{
						$J = json_decode($J,true);
					}
					else
					{
						$J = $_ENV['complain']->getJList(0);
						$this->cache->set("Jlist_0",json_encode($J),1800);
					}
					
					$Comment = unserialize($value['comment']);
					if(isset($Comment['revoke']['revokeReason']))
					{
						$complain_list['ComplainList'][$key]['RevokeReason'] = $Comment['revoke']['revokeReason'];
					}
					else
					{
						$complain_list['ComplainList'][$key]['RevokeReason'] = "无理由";
					}
					if(isset($Comment['revoke']['ip']))
					{
						$complain_list['ComplainList'][$key]['RevokeIP'] = $Comment['revoke']['ip'];
					}
					else
					{
						$complain_list['ComplainList'][$key]['RevokeIP'] = "无IP";
					}
					$complain_list['ComplainList'][$key]['SyncInfo'] = $_ENV['complain']->get_ComplainSyncInfo($value['id']);
					$excelArr = array();
					$S = $SellerTypeList;
					$S['0'] = "尚未处理";
					$excelArr = array("id"=>$complain_list['ComplainList'][$key]['id'],
					"order_id"=>$complain_list['ComplainList'][$key]['order_id']?$complain_list['ComplainList'][$key]['order_id']:$complain_list['ComplainList'][$key]['goods_id'],
					"ComplainId"=>$complain_list['ComplainList'][$key]['SyncInfo']['scid']?"CS000".$complain_list['ComplainList'][$key]['SyncInfo']['cpid']:"",
					"description"=>$complain_list['ComplainList'][$key]['description'],
					"author"=>$complain_list['ComplainList'][$key]['author'],
					"sName"=>isset($S[$value['sid']])?$S[$value['sid']]:"尚未处理",
					"jName"=>isset($J[$value['jid']])?$J[$value['jid']]:"其他分类",
					"time"=>date("Y-m-d H:i:s",$complain_list['ComplainList'][$key]['time']),
					"RevokeTime"=>$complain_list['ComplainList'][$key]['RevokeTime'],
					"RevokeReason"=>$complain_list['ComplainList'][$key]['RevokeReason'],
					"RevokeIP"=>$complain_list['ComplainList'][$key]['RevokeIP'],
					"Browser"=>$Comment['Browser'],
					"OS"=>$Comment['OS']
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($complain_list['ComplainList']);
			}			 
			$oExcel->closeSheet()->close();	

		}
		include template('revokecomplainview','admin');        
    }
    /* 
		intoComplainData:进入查看投诉统计页面
		查看全部投诉（已修改） 
	*/
	function oncomplainData()
	{
		//只查询转为投诉的问题
		$ConditionList['transformed']=1;
		$action = "index.php?admin_complain/complainData";
		$hasIntoComplainViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoComplainData");
		$hasIntoComplainViewPrivilege['url'] = "?admin_main";
		!$hasIntoComplainViewPrivilege['return']  && __msg( $hasIntoComplainViewPrivilege );
		
    	
		$ConditionList['ComplainStartDate'] = isset($this->post['ComplainStartDate'])?$this->post['ComplainStartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-7*86400));   	
		$ConditionList['ComplainEndDate'] = isset($this->post['ComplainEndDate'])?$this->post['ComplainEndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
    	
    	$ConditionList['AnswerStartDate'] = 0;   	
		$ConditionList['AnswerEndDate'] = 0;  
    	
    	$ConditionList['author'] = isset($this->post['author'])?urldecode(trim($this->post['author'])):(isset($this->get[6])?urldecode(trim($this->get[6])):"");
		$ConditionList['author_id'] = isset($this->post['author_id'])?trim($this->post['author_id']):(isset($this->get[7])?trim($this->get[7]):"");
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim($this->post['operator_loginId']):(isset($this->get[8])?trim($this->get[8]):"");		
		$ConditionList['jid'] = isset($this->post['jid'])?intval($this->post['jid']):(isset($this->get[9])?intval($this->get[9]):0);
				
		$SellerTypeList = $this->ask_config->getSellerType();
		$ConditionList['sid'] = isset($this->post['sid'])?intval($this->post['sid']):(isset($this->get[12])?intval($this->get[12]):-1);
		$J = $this->cache->get("Jlist_".$ConditionList['sid']);
		if(false !== $J) 
		{
			$Jlist = json_decode($J,true);
		}
		else
		{
			$Jlist = $_ENV['complain']->getJList($ConditionList['sid']);
			$this->cache->set("Jlist_".$ConditionList['sid'],json_encode($Jlist),1800);
		}
		
		
		$statusList = $this->ask_config->getComStatus();
		$ConditionList['status'] = isset($this->post['status'])?intval($this->post['status']):(isset($this->get[10])?intval($this->get[10]):-2);
		
		$assessStatusList = $this->ask_config->getComAssessStatus();
		$ConditionList['Assess'] = isset($this->post['Assess'])?intval($this->post['Assess']):(isset($this->get[11])?intval($this->get[11]):-1);

		$export = trim($this->get[13])=="export"?1:0;
       
		$pagesize = $this->setting['list_default'];
		$pagesize = 20;
		$complainData = $_ENV['complain']->getComplainData($ConditionList);	
		$downloadstr = page_url("<下载EXCEL表格>", "admin_complain/complainData/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']."/export");

		if(!$export)
		{			
			$msg && $message = $msg;
			$ty && $type = $ty;
			# Include FusionCharts PHP Class
			include( TIPASK_ROOT . '/lib/fusion/Includes/FusionCharts_Gen.php');

			# Create Multiseries ColumnD chart object using FusionCharts PHP Class
			$FC = new FusionCharts("MSLine",'100%','400');

			# Set the relative path of the swf file
			$FC->setSWFPath( '../Charts/');

			$Step=3;
			# Store chart attributes in a variable
			$strParam="caption='每日用户投诉统计';xAxisName='日期';baseFontSize=12;numberPrefix=;numberSuffix=次;decimalPrecision=0;showValues=0;formatNumberScale=0;labelStep=".$Step.";numvdivlines=$divideV;rotateNames=1;yAxisMinValue=0;yAxisMaxValue=10;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";

			# Set chart attributes

			$FC->setChartParams($strParam);
			foreach($complainData['date'] as $date => $data)
			{
				$FC->addCategory($date);				
			}
			$FC->addDataset("投诉次数");
			foreach($complainData['date'] as $date => $data)
			{
				// $paramset="link=" . urlencode($FC->getLinkFromPattern($data,'http://www.google.com'));
				// $FC->addChartData($data['complainCount'],$paramset);
				$FC->addChartData($data['complainCount']);
			}
			foreach($assessStatusList as $key => $value)
			{
				if($key>=0)
				{
					$FC->addDataset($value);
					foreach($complainData['date'] as $date => $data)
					{
						// $paramset="link=" . urlencode($FC->getLinkFromPattern($data,'http://www.google.com'));
						// $FC->addChartData($data['complainCount'],$paramset);
						if(!isset($data['assess'][$key]))
						{
							$complainData['date'][$date]['assess'][$key]['complainCount'] = 0;
						}
						ksort($complainData['date'][$date]['assess']);
						$FC->addChartData($complainData['date'][$date]['assess'][$key]['complainCount']);
					}				
				}
			}

			$FC2 = new FusionCharts("Pie2d",'100%','400');
			$FC2->setSWFPath( '../Charts/');
			
			$strParam="caption='交易类型';xAxisName='理由';baseFontSize=12;numberPrefix=;numberSuffix=次;decimalPrecision=0;showValues=1;formatNumberScale=0;rotateNames=0;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
			$FC2->setChartParams($strParam);
			$FC2->addDataset("交易类型");
			
			$jlist = $_ENV['complain']->getJList(0);

			unset($jlist[0]);
			foreach($complainData['jid'] as $j => $data)
			{
				$complainData['jid'][$j]['jName'] = isset($jlist[$j])?$jlist[$j]:"未定义交易类型".$j;
				$complainData['jid'][$j]['rate'] = $complainData['totalData']['complainCount']>0?$data['complainCount']/$complainData['totalData']['complainCount']:0;
				$FC2->addChartData($data['complainCount'],"name=".$complainData['jid'][$j]['jName']);
			}				
			
		} 			
		else
		{
			set_time_limit(0);
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='投诉统计下载';
			{

				$oExcel->download($FileName)->addSheet('交易类型统计');	
				//标题栏
				$title = array("交易类型","投诉次数","问题占比");
				$oExcel->addRows(array($title));
				$jlist = $_ENV['complain']->getJList(0);

				unset($jlist[0]);
				foreach($complainData['jid'] as $j => $data)
				{

					$excelArr = array();
					$excelArr = array("sName"=>isset($jlist[$j])?$jlist[$j]:"未定义交易类型",
					"complainCount"=>$data['complainCount'],
					"rate"=>$complainData['totalData']['complainCount']>0?$data['complainCount']/$complainData['totalData']['complainCount']:0,					
					);
					$oExcel->addRows(array($excelArr));			
				}
				$oExcel->closeSheet();			
			}
			$oExcel->addSheet('投诉统计');
			$title = array("日期");
			foreach($assessStatusList as $key => $value)
			{
				$title[] = $value;
			}
			$oExcel->addRows(array($title));
			foreach($complainData['date'] as $date => $data)
			{
				$excelArr = array();
				$excelArr = array("date"=>$date,
				"complainCount"=>$data['complainCount']
				);
				foreach($assessStatusList as $key => $value)
				{
					if($key>=0)
					{
						$excelArr[$key] = isset($data['assess'][$key])?$data['assess'][$key]['complainCount']:0;
					}
				}
				$oExcel->addRows(array($excelArr));	
				//$paramset="link=" . urlencode($FC->getLinkFromPattern($data,'http://www.google.com'));
				//$FC->addChartData($data['complainCount'],$paramset);
			}
			
			$oExcel->closeSheet()->close();												
		}
		include template('complaindata','admin'); 
	}
    /* 
		intoComplainData:进入查看已撤销投诉统计页面
	*/
	function onrevokeComplainData()
	{
		//只查询转为投诉的问题
		$ConditionList['transformed']=1;
		$action = "index.php?admin_complain/revokeComplainData";
		$hasIntoComplainViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoRevokeComplainData");
		$hasIntoComplainViewPrivilege['url'] = "?admin_main";
		!$hasIntoComplainViewPrivilege['return']  && __msg( $hasIntoComplainViewPrivilege );
		
    	
		$ConditionList['ComplainStartDate'] = isset($this->post['ComplainStartDate'])?$this->post['ComplainStartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-7*86400));   	
		$ConditionList['ComplainEndDate'] = isset($this->post['ComplainEndDate'])?$this->post['ComplainEndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
    	    	
    	$ConditionList['author'] = isset($this->post['author'])?urldecode(trim($this->post['author'])):(isset($this->get[4])?urldecode(trim($this->get[4])):"");
		$ConditionList['author_id'] = isset($this->post['author_id'])?trim($this->post['author_id']):(isset($this->get[5])?trim($this->get[5]):"");
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim($this->post['operator_loginId']):(isset($this->get[6])?trim($this->get[6]):"");		
		$ConditionList['jid'] = isset($this->post['jid'])?intval($this->post['jid']):(isset($this->get[7])?intval($this->get[7]):0);
				
		$SellerTypeList = $this->ask_config->getSellerType();
		$ConditionList['sid'] = isset($this->post['sid'])?intval($this->post['sid']):(isset($this->get[8])?intval($this->get[8]):-1);
		$J = $this->cache->get("Jlist_".$ConditionList['sid']);
		if(false !== $J) 
		{
			$Jlist = json_decode($J,true);
		}
		else
		{
			$Jlist = $_ENV['complain']->getJList($ConditionList['sid']);
			$this->cache->set("Jlist_".$ConditionList['sid'],json_encode($Jlist),1800);
		}
		
		
		$ConditionList['status'] = 2;
		$ConditionList['Assess'] = -1;
		$ConditionList['reason'] = isset($this->post['reason'])?urldecode(trim($this->post['reason'])):(isset($this->get[9])?urldecode(trim($this->get[9])):"");

		$export = trim($this->get[10])=="export"?1:0;
       
		$pagesize = $this->setting['list_default'];
		$pagesize = 20;
		$complainData = $_ENV['complain']->getRevokeComplainData($ConditionList);	
		$downloadstr = page_url("<下载EXCEL表格>", "admin_complain/revokeComplainData/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['jid']."/".$ConditionList['sid']."/".$ConditionList['reason']."/export");

		if(!$export)
		{			
			$msg && $message = $msg;
			$ty && $type = $ty;
			# Include FusionCharts PHP Class
			include( TIPASK_ROOT . '/lib/fusion/Includes/FusionCharts_Gen.php');

			$FC2 = new FusionCharts("Pie2d",'100%','400');
			$FC2->setSWFPath( '../Charts/');
			
			$strParam="caption='撤销理由统计';xAxisName='理由';baseFontSize=12;numberPrefix=;numberSuffix=次;decimalPrecision=0;showValues=1;formatNumberScale=0;rotateNames=0;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
			$FC2->setChartParams($strParam);
			$FC2->addDataset("撤销理由");
			

			foreach($complainData['RevokeReasonList'] as $r => $data)
			{
				$complainData['RevokeReasonList'][$r]['rate'] = $complainData['totalData']['complainCount']>0?$data['revokeCount']/$complainData['totalData']['complainCount']:0;
				$FC2->addChartData($data['revokeCount'],'name="'.($data['content']).'"');
			}
			
			$FC3 = new FusionCharts("Pie2d",'100%','400');
			$FC3->setSWFPath( '../Charts/');
			
			$strParam="caption='交易类型统计';xAxisName='交易类型';baseFontSize=12;numberPrefix=;numberSuffix=次;decimalPrecision=0;showValues=1;formatNumberScale=0;rotateNames=0;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
			$FC3->setChartParams($strParam);
			$FC3->addDataset("交易类型");
			
			$jlist = $_ENV['complain']->getJList(0);
			unset($jlist[0]);
			foreach($complainData['jList'] as $j => $data)
			{
				$complainData['jList'][$j]['jName'] = isset($jlist[$j])?$jlist[$j]:"未定义交易类型".$j;
				$complainData['jList'][$j]['rate'] = $complainData['totalData']['complainCount']>0?$data['revokeCount']/$complainData['totalData']['complainCount']:0;
				$FC3->addChartData($data['revokeCount'],"name=".$complainData['jList'][$j]['jName']);
			}

			$FC4 = new FusionCharts("Pie2d",'100%','400');
			$FC4->setSWFPath( '../Charts/');
			
			$strParam="caption='用户身份类型统计';xAxisName='身份类型';baseFontSize=12;numberPrefix=;numberSuffix=次;decimalPrecision=0;showValues=1;formatNumberScale=0;rotateNames=0;numDivLines=9;showAlternateHGridColor=1;alternateHGridAlpha=5;alternateHGridColor='CC3300';hoverCapSepChar=，";
			$FC4->setChartParams($strParam);
			$FC4->addDataset("身份类型");
			
			$SellerTypeList = $this->ask_config->getSellerType();
			unset($slist[0]);
			foreach($complainData['sList'] as $s => $data)
			{
				$complainData['sList'][$s]['sName'] = isset($SellerTypeList[$s])?$SellerTypeList[$s]:"未定义身份类型".$s;
				$complainData['sList'][$s]['rate'] = $complainData['totalData']['complainCount']>0?$data['revokeCount']/$complainData['totalData']['complainCount']:0;
				$FC4->addChartData($data['revokeCount'],"name=".$complainData['sList'][$s]['sName']);
			}			
			
		} 			
		else
		{
			set_time_limit(0);
			require TIPASK_ROOT . '/lib/Excel.php';
			$oExcel = new Excel();
			$FileName='已撤销投诉统计下载';
			{

				$oExcel->download($FileName);	

				$oExcel->addSheet('撤销理由统计');	
				//标题栏
				$title = array("撤销理由","撤销次数","问题占比");
				$oExcel->addRows(array($title));

				foreach($complainData['RevokeReasonList'] as $r => $data)
				{
					$excelArr = array();
					$excelArr = array("reason"=>$data['content'],
					"revokeCount"=>$data['revokeCount'],
					"rate"=>$complainData['totalData']['complainCount']>0?$data['revokeCount']/$complainData['totalData']['complainCount']:0,					
					);
					$oExcel->addRows(array($excelArr));			
				}
				$oExcel->closeSheet();

				$oExcel->addSheet('交易类型统计');	
				//标题栏
				$title = array("交易类型","撤销次数","问题占比");
				$oExcel->addRows(array($title));
				$jlist = $_ENV['complain']->getJList(0);
				unset($jlist[0]);
				foreach($complainData['jList'] as $j => $data)
				{
					$excelArr = array();
					$excelArr = array("jname"=>isset($jlist[$j])?$jlist[$j]:"未定义交易类型".$j,
					"revokeCount"=>$data['revokeCount'],
					"rate"=>$complainData['totalData']['complainCount']>0?$data['revokeCount']/$complainData['totalData']['complainCount']:0,					
					);
					$oExcel->addRows(array($excelArr));			
				}
				$oExcel->closeSheet();

				$oExcel->addSheet('身份类型统计');	
				//标题栏
				$title = array("身份类型","撤销次数","问题占比");
				$oExcel->addRows(array($title));
				$SellerTypeList = $this->ask_config->getSellerType();
				unset($slist[0]);
				foreach($complainData['sList'] as $s => $data)
				{
					$excelArr = array();
					$excelArr = array("sname"=>isset($SellerTypeList[$s])?$SellerTypeList[$s]:"未定义身份类型".$s,
					"revokeCount"=>$data['revokeCount'],
					"rate"=>$complainData['totalData']['complainCount']>0?$data['revokeCount']/$complainData['totalData']['complainCount']:0,					
					);
					$oExcel->addRows(array($excelArr));			
				}
				$oExcel->closeSheet();					
			}
			$oExcel->close();												
		}
		include template('revokecomplaindata','admin'); 
	}
 /* 
		intoComplainView:进入查看全部投诉页面
		查看全部投诉（已修改） 
	*/
    function ontransformedComplainView($msg='', $ty='')
	{

		$action = "index.php?admin_complain/transformedComplainView";
		$hasIntoComplainViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoTransformedComplainView");
		$hasIntoComplainViewPrivilege['url'] = "?admin_main";
		!$hasIntoComplainViewPrivilege['return']  && __msg( $hasIntoComplainViewPrivilege );
		
    	
		$ConditionList['ComplainStartDate'] = isset($this->post['ComplainStartDate'])?$this->post['ComplainStartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-7*86400));   	
		$ConditionList['ComplainEndDate'] = isset($this->post['ComplainEndDate'])?$this->post['ComplainEndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
    	
    	// $ConditionList['AnswerStartDate'] = isset($this->post['AnswerStartDate'])?$this->post['AnswerStartDate']:(isset($this->get[4])?$this->get[4]:date("Y-m-d",time()-7*86400)); ;   	
		// $ConditionList['AnswerEndDate'] = isset($this->post['AnswerEndDate'])?$this->post['AnswerEndDate']:(isset($this->get[5])?$this->get[5]:date("Y-m-d",time()));   
    	
    	$ConditionList['AnswerStartDate'] = 0;   	
		$ConditionList['AnswerEndDate'] = 0; 
		
    	$ConditionList['author'] = isset($this->post['author'])?urldecode(trim($this->post['author'])):(isset($this->get[6])?urldecode(trim($this->get[6])):"");
		$ConditionList['author_id'] = isset($this->post['author_id'])?trim($this->post['author_id']):(isset($this->get[7])?trim($this->get[7]):"");
		$ConditionList['operator_loginId'] = isset($this->post['operator_loginId'])?trim($this->post['operator_loginId']):(isset($this->get[8])?trim($this->get[8]):"");		
				
		$ConditionList['jid'] = isset($this->post['jid'])?intval($this->post['jid']):(isset($this->get[10])?intval($this->get[10]):0);
		$ConditionList['complainId'] = isset($this->post['complainId'])?intval($this->post['complainId']):(isset($this->get[9])?intval($this->get[9]):0);
		
		$statusList = $this->ask_config->getComStatus();
		$ConditionList['status'] = isset($this->post['status'])?intval($this->post['status']):(isset($this->get[11])?intval($this->get[11]):-2);
		
		$assessStatusList = $this->ask_config->getComAssessStatus();
		$ConditionList['Assess'] = isset($this->post['Assess'])?intval($this->post['Assess']):(isset($this->get[12])?intval($this->get[12]):-1);

		$SellerTypeList = $this->ask_config->getSellerType();
		$ConditionList['sid'] = isset($this->post['sid'])?intval($this->post['sid']):(isset($this->get[13])?intval($this->get[13]):-1);
		$J = $this->cache->get("Jlist_".$ConditionList['sid']);
		if(false != $J) 
		{
			$Jlist = json_decode($J,true);
		}
		else
		{
			$Jlist = $_ENV['complain']->getJList($ConditionList['sid']);
			$this->cache->set("Jlist_".$ConditionList['sid'],json_encode($Jlist),1800);
		}
		$ConditionList['transformed'] = isset($this->post['transformed'])?intval($this->post['transformed']):(isset($this->get[14])?intval($this->get[14]):2);
		$PublicStatusList = $this->ask_config->getPublicStatus();

		@$page = max(1, intval($this->get[15]));
		$export = trim($this->get[16])=="export"?1:0;
		$setting = $this->setting;
		$question_type_list = $this->ask_config->getQuestionType();
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$complain_list = $_ENV['complain']->getComplainList($ConditionList,$page,$pagesize);
			foreach($complain_list['ComplainList'] as $key => $value)
			{
				$complain_list['ComplainList'][$key]['description'] = cutstr($value['description'],15);
				$S = $SellerTypeList;
				$complain_list['ComplainList'][$key]['sName'] = isset($S[$value['sid']])?$S[$value['sid']]:"尚未处理";
				$J = $this->cache->get("Jlist_0");
				if(false != $J) 
				{
					$J = json_decode($J,true);
				}
				else
				{
					$J = $_ENV['complain']->getJList(0);
					$this->cache->set("Jlist_0",json_encode($J),1800);
				}
				$complain_list['ComplainList'][$key]['jName'] = isset($J[$value['jid']])?$J[$value['jid']]:"其他分类";					
				$complain_list['ComplainList'][$key]['publicStatus'] = $PublicStatusList[$value['public']];
				$complain_list['ComplainList'][$key]['asnum'] = $assessStatusList[$value['assess']].($value['asnum']>0?"/".$value['asnum']."次":"");
				if($value['sync']==0)
				{
					$complain_list['ComplainList'][$key]['syncStatus'] = "未同步"; 				
				}
				elseif($value['sync']==1)
				{
					$complain_list['ComplainList'][$key]['syncStatus'] = "已同步"; 				
				}
				elseif($value['sync']<0)
				{
					$complain_list['ComplainList'][$key]['syncStatus'] = "失败".(-1*$value['sync'])."次/最大".$setting['ts_warn_maxNum']."次"; 				
				}
				if(in_array($value['status'],array(1,3)))
				{
					$answer = $_ENV['complain']->get_ComplainAnInfo($complain_list['ComplainList'][$key]['id']);
					$complain_list['ComplainList'][$key]['answer_loginId'] = $answer['contact'];					
				}
				$Comment = unserialize($value['comment']);							
				$complain_list['ComplainList'][$key]['reason']=$Comment['convert']['reason'];
				if(isset($Comment['convert']['from_type']))
				{
					$complain_list['ComplainList'][$key]['convertion'] = "<a href = '".$_ENV['question']->getQuestionLink($Comment['convert']['from_id'],"question")."' target='_BLANK'><来自 ".$question_type_list[$Comment['convert']['from_type']]."><a> ";
					$complain_list['ComplainList'][$key]['transformTime'] = $Comment['convert']['transformTime'];
					$complain_list['ComplainList'][$key]['transformloginId'] = $Comment['convert']['loginId'];
				}
				else
				{
					$complain_list['ComplainList'][$key]['convertion'] = "<a href = '".$_ENV['question']->getQuestionLink($Comment['convert']['to_id'],"question")."' target='_BLANK'><转成 ".$question_type_list[$Comment['convert']['to_type']]." ><a> ";
					$complain_list['ComplainList'][$key]['transformTime'] = $Comment['convert']['transformTime'];
					$complain_list['ComplainList'][$key]['transformloginId'] = $Comment['convert']['loginId'];					
				}
				if(in_array($value['status'],array(0,2,4)))
				{
					if($value['status']==2)
					{
						$complain_list['ComplainList'][$key]['title'] = '撤销理由:"'.($Comment['revoke']['revokeReason']?urldecode($Comment['revoke']['revokeReason']):'无').'"，客户端IP:'.($Comment['revoke']['ip']?$Comment['revoke']['ip']:'无');
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "用户于".date("Y-m-d H:i:s",$value['rtime'])."撤销";
					}
					else
					{
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $statusList[$value['status']];					
					}
				}
				else
				{					
					if($answer['time']>0)
					{						
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $this->timeLagToText($value['time'],$answer['time']);
					}
					else
					{
						$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "尚未回复";
					}					
				}
				$complain_list['ComplainList'][$key]['reason'] = $Comment['convert']['reason'];
				$complain_list['ComplainList'][$key]['url'] = $value['public']==1?"已隐藏":"<a href = '".$_ENV['question']->getQuestionLink($value['id'],"complain")."' target='_BLANK'><查看></a>";
				$complain_list['ComplainList'][$key]['SyncInfo'] = $_ENV['complain']->get_ComplainSyncInfo($value['id']);
				$complain_list['ComplainList'][$key]['SyncInfo']['cpid'] = $complain_list['ComplainList'][$key]['SyncInfo']['cpid']?"SC000".$complain_list['ComplainList'][$key]['SyncInfo']['cpid']:"无";				
			}
			$departstr = page($complain_list['ComplainCount'], $pagesize, $page, "admin_complain/transformedComplainView/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['complainId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']."/".$ConditionList['transformed']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_complain/transformedComplainView/".$ConditionList['ComplainStartDate']."/".$ConditionList['ComplainEndDate']."/".$ConditionList['AnswerStartDate']."/".$ConditionList['AnswerEndDate']."/".urlencode($ConditionList['author'])."/".$ConditionList['author_id']."/".$ConditionList['operator_loginId']."/".$ConditionList['complainId']."/".$ConditionList['jid']."/".$ConditionList['status']."/".$ConditionList['Assess']."/".$ConditionList['sid']."/".$ConditionList['transformed']."/".$page."/export");
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
			$FileName='SC与酷宝后台类别转换查询下载';
			$oExcel->download($FileName)->addSheet('投诉详情列表');
			//标题栏
			$title = array("SC投诉单号","交易/物品订单号","关联投诉单号","问题描述","用户名","身份类型","交易类型","投诉时间","回复时间","转单客服","转单时间","转单说明","转单理由","浏览量","是否隐藏","接手客服账号","评价","同步状态","回复客服","回复内容","撤销时间","撤销理由","撤销客户端IP","浏览器","操作系统");
			$oExcel->addRows(array($title));
			while($num >0)
			{
				$complain_list = $_ENV['complain']->getComplainList($ConditionList,$page,$pagesize);
				foreach($complain_list['ComplainList'] as $key => $value)
				{
					$value['description'] = preg_replace('/[\s"]/','',$value['description']);
					$complain_list['ComplainList'][$key]['author'] = str_replace('<x>','<?>',$value['author']);
					$value['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$value['description']);
					$complain_list['ComplainList'][$key]['description'] = strip_tags($value['description']);
					$complain_list['ComplainList'][$key]['qtypeName'] = isset($qtypeList[$value['qtype']])?$qtypeList[$value['qtype']]['name']:"其他分类";
					$complain_list['ComplainList'][$key]['publicStatus'] = $PublicStatusList[$value['public']];
					$complain_list['ComplainList'][$key]['asnum'] = $assessStatusList[$value['assess']].($value['asnum']>0?"/".$value['asnum']."次":"");				
					if($value['sync']==0)
					{
						$complain_list['ComplainList'][$key]['syncStatus'] = "未同步"; 				
					}
					elseif($value['sync']==1)
					{
						$complain_list['ComplainList'][$key]['syncStatus'] = "已同步"; 				
					}
					elseif($value['sync']<0)
					{
						$complain_list['ComplainList'][$key]['syncStatus'] = "失败".(-1*$value['sync'])."次/最大".$setting['ts_warn_maxNum']."次"; 				
					}
					if(in_array($value['status'],array(1,3)))
					{
						$answer = $_ENV['complain']->get_ComplainAnInfo($complain_list['ComplainList'][$key]['id']);
						$answer['content'] = preg_replace('/[\s"]/','',$answer['content']);
						$answer['content'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$answer['content']);
						$answer['content'] = strip_tags($answer['content']);						
					}
					$Comment = unserialize($value['comment']);
					if(isset($Comment['convert']['from_type']))
					{
						$complain_list['ComplainList'][$key]['convertion'] = "来自 ".$question_type_list[$Comment['convert']['from_type']].",问题ID:".$Comment['convert']['from_id'];
						$complain_list['ComplainList'][$key]['transformTime'] = $Comment['convert']['transformTime'];
						$complain_list['ComplainList'][$key]['transformloginId'] = $Comment['convert']['loginId'];
					}
					else
					{
						$complain_list['ComplainList'][$key]['convertion'] = "转为 ".$question_type_list[$Comment['convert']['to_type']].",问题ID:".$Comment['convert']['to_id'];
						$complain_list['ComplainList'][$key]['transformTime'] = $Comment['convert']['transformTime'];
						$complain_list['ComplainList'][$key]['transformloginId'] = $Comment['convert']['loginId'];					
					}
					$complain_list['ComplainList'][$key]['reason'] = $Comment['convert']['reason'];
					if(in_array($value['status'],array(0,2,4)))
					{
						if($value['status']==2)
						{														
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "用户已撤销";//"用户于".date("Y-m-d H:i:s",$value['rtime'])."撤销";
							$complain_list['ComplainList'][$key]['RevokeTime'] = $value['rtime']?date("Y-m-d H:i:s",$value['rtime']):"无";
							$complain_list['ComplainList'][$key]['RevokeReason'] = $Comment['revoke']['revokeReason']?urldecode($Comment['revoke']['revokeReason']):"无";
							$complain_list['ComplainList'][$key]['RevokeIP'] = $Comment['revoke']['ip']?$Comment['revoke']['ip']:"无";
							$answer['content'] = "无";
						}
						else
						{
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $statusList[$value['status']];
						}						
					}
					else
					{						
						if($answer['time']>0)
						{						
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = $this->timeLagToText($value['time'],$answer['time']);
						}
						else
						{
							$complain_list['ComplainList'][$key]['AnswerTimeLag'] = "尚未回复";
						}																		
					}
					$J = $this->cache->get("Jlist_0");
					if(false != $J) 
					{
						$J = json_decode($J,true);
					}
					else
					{
						$J = $_ENV['complain']->getJList(0);
						$this->cache->set("Jlist_0",json_encode($J),1800);
					}
					$complain_list['ComplainList'][$key]['SyncInfo'] = $_ENV['complain']->get_ComplainSyncInfo($value['id']);
					$excelArr = array();
					$S = $SellerTypeList;
					$S['0'] = "尚未处理";
					$excelArr = array("id"=>$complain_list['ComplainList'][$key]['id'],
					"order_id"=>$complain_list['ComplainList'][$key]['order_id']?$complain_list['ComplainList'][$key]['order_id']:$complain_list['ComplainList'][$key]['goods_id'],
					"ComplainId"=>$complain_list['ComplainList'][$key]['SyncInfo']['scid']?"CS000".$complain_list['ComplainList'][$key]['SyncInfo']['cpid']:"",
					"description"=>$complain_list['ComplainList'][$key]['description'],
					"author"=>$complain_list['ComplainList'][$key]['author'],
					"sName"=>isset($S[$value['sid']])?$S[$value['sid']]:"尚未处理",
					"jName"=>isset($J[$value['jid']])?$J[$value['jid']]:"其他分类",
					"time"=>date("Y-m-d H:i:s",$complain_list['ComplainList'][$key]['time']),
					"AnswerTimeLag"=>$complain_list['ComplainList'][$key]['AnswerTimeLag'],
					"transformloginId"=>$complain_list['ComplainList'][$key]['transformloginId'],
					"transformTime"=>date("Y-m-d H:i:s",$complain_list['ComplainList'][$key]['transformTime']),
					"convertion"=>$complain_list['ComplainList'][$key]['convertion'],
					"reason"=>strip_tags($complain_list['ComplainList'][$key]['reason']),	
					"view"=>$complain_list['ComplainList'][$key]['view'],
					"publicStatus"=>$complain_list['ComplainList'][$key]['publicStatus'],
					"loginId"=>$complain_list['ComplainList'][$key]['loginId'],
					"asnum"=>$complain_list['ComplainList'][$key]['asnum'],
					"syncStatus"=>$complain_list['ComplainList'][$key]['syncStatus'],
					"answer_loginId"=>trim($answer['contact']),
					"answer"=>trim($answer['content'])==""?"无回复内容":$answer['content'],
					"RevokeTime"=>$complain_list['ComplainList'][$key]['RevokeTime'],
					"RevokeReason"=>strip_tags($complain_list['ComplainList'][$key]['RevokeReason']),
					"RevokeIP"=>$complain_list['ComplainList'][$key]['RevokeIP'],
					"Browser"=>$Comment['Browser'],
					"OS"=>$Comment['OS']
					);
					$oExcel->addRows(array($excelArr));
				}
				$page++;
				$num = count($complain_list['ComplainList']);
			}			 
			$oExcel->closeSheet()->close();	

		}
		include template('transformedComplainview','admin');        
    }
    function onajaxGetSType(){
    	$category_str = '';
    	$sid = $this->post['sid'];
		$J = $this->cache->get("Jlist_".$sid);
		if(false != $J) 
		{
			$Jlist = json_decode($J,true);
		}
		else
		{
			$Jlist = $_ENV['complain']->getJList($sid);
			$this->cache->set("Jlist_".$sid,json_encode($Jlist),1800);
		}
    	foreach($Jlist as $key => $val){
    		$category_str.='<option value="'.$key.'">'.$val.'</option>';
    	}
    	exit($category_str);
    }
    /* 
		intoTransformLogView:进入查看到投诉的转单记录页面
		acceptTransformLog:审核转单到投诉 
		查看所有转单记录
	*/
    function ontransformLogView($msg='', $ty='')
	{
		//只查询转为投诉的问题
		$ConditionList['transformed']=1;
		$action = "index.php?admin_complain/transformLogView";
		$hasIntoComplainTransformLogViewPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "intoTransformLogView");
		$hasIntoComplainTransformLogViewPrivilege['url'] = "?admin_main";
		!$hasIntoComplainTransformLogViewPrivilege['return']  && __msg( $hasIntoComplainTransformLogViewPrivilege );
		
    	
		$AcceptComplainTransformLogPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "acceptTransformLog");
		$ConditionList['StartDate'] = isset($this->post['StartDate'])?$this->post['StartDate']:(isset($this->get[2])?$this->get[2]:date("Y-m-d",time()-7*86400));   	
		$ConditionList['EndDate'] = isset($this->post['EndDate'])?$this->post['EndDate']:(isset($this->get[3])?$this->get[3]:date("Y-m-d",time())); 
		
		$ConditionList['author'] = isset($this->post['author'])?urldecode(trim($this->post['author'])):(isset($this->get[4])?urldecode(trim($this->get[4])):"");
		$operator_list = $_ENV['operator']->getList(0,0);		
		$ConditionList['AcceptOperator'] = isset($this->post['AcceptOperator'])?trim($this->post['AcceptOperator']):(isset($this->get[5])?trim($this->get[5]):"");
		$ConditionList['ApplyOperator'] = isset($this->post['ApplyOperator'])?trim($this->post['ApplyOperator']):(isset($this->get[6])?trim($this->get[6]):"");
		$ConditionList['QuestionId'] = isset($this->post['QuestionId'])?intval($this->post['QuestionId']):(isset($this->get[7])?intval($this->get[7]):0);
		
		$ConditionList['TransformStatus'] = isset($this->post['TransformStatus'])?intval($this->post['TransformStatus']):(isset($this->get[8])?intval($this->get[8]):-1);
		$ConditionList['ToType'] = "complain";
		$TransformComplainStatus = $this->ask_config->getTransformComplainStatus();
		@$page = max(1, intval($this->get[9]));
		$export = trim($this->get[9])=="export"?1:0;
		$setting = $this->setting;
		if(!$export)
		{
			$pagesize = $this->setting['list_default'];
			$pagesize = 20;
			$TransformLogList = $_ENV['question']->getTransformLogList($ConditionList,$page,$pagesize);
			
			foreach($TransformLogList['LogList'] as $key => $value)
			{
				$TransformLogList['LogList'][$key]['AcceptStatus'] = $TransformComplainStatus[intval($value['transform_status'])];
				$TransformLogList['LogList'][$key]['applyTime'] = date("Y-m-d H:i",$value['applyTime']);
				$TransformLogList['LogList'][$key]['acceptTime'] = $value['acceptTime']?date("Y-m-d H:i",$value['acceptTime']):"尚未审批";
				$TransformLogList['LogList'][$key]['AcceptOperator'] = $value['AcceptOperator']?($value['AcceptOperator']=="system"?"系统自动":$value['AcceptOperator']):"尚未审批";
				$QuestionInfo = $_ENV['question']->Get($value['from_id']);
				$TransformLogList['LogList'][$key]['QuestionInfo']['author'] = str_replace('<x>','<?>',$QuestionInfo['author']);
				$QuestionInfo['description'] = preg_replace('/[&amp;&nbsp;&quot;]/','',$QuestionInfo['description']);
				$TransformLogList['LogList'][$key]['QuestionInfo']['description'] = strip_tags($QuestionInfo['description']);
				$TransformLogList['LogList'][$key]['QuestionInfo']['description_short'] = cutstr(trim($TransformLogList['LogList'][$key]['QuestionInfo']['description']),8);
				$TransformLogList['LogList'][$key]['QuestionInfo']['time'] = date("Y-m-d H:i",$QuestionInfo['time']);
				$TransformLogList['LogList'][$key]['QuestionInfo']['js_kf'] = $QuestionInfo['js_kf']==""?"尚未接单":$QuestionInfo['js_kf'];
				$TransformLogList['LogList'][$key]['QuestionInfo']['assess'] = $QuestionInfo['is_pj']!=0?($QuestionInfo['is_pj']==1?"满意":"不满意"):"尚未评价";
				$TransformLogList['LogList'][$key]['to_url'] = $value['to_id']>0?
				("<a href = '".$_ENV['question']->getQuestionLink($value['to_id'],"complain")."' target='_BLANK'><投诉></a>"):"尚未转换";
				$Comment = unserialize($value['comment']);
				$TransformLogList['LogList'][$key]['TransformReason'] = $Comment['TransformReason'];
				$cidInfoList[$Comment['CidArr']['cid']] = isset($cidInfoList[$Comment['CidArr']['cid']]['id'])?$cidInfoList[$Comment['CidArr']['cid']]:$_ENV['category']->get($Comment['CidArr']['cid']);
				$cidInfoList[$Comment['CidArr']['cid1']] = isset($cidInfoList[$Comment['CidArr']['cid1']]['id'])?$cidInfoList[$Comment['CidArr']['cid1']]:$_ENV['category']->get($Comment['CidArr']['cid1']);
				$cidInfoList[$Comment['CidArr']['cid2']] = isset($cidInfoList[$Comment['CidArr']['cid2']]['id'])?$cidInfoList[$Comment['CidArr']['cid2']]:$_ENV['category']->get($Comment['CidArr']['cid2']);
				$cidInfoList[$Comment['CidArr']['cid3']] = isset($cidInfoList[$Comment['CidArr']['cid3']]['id'])?$cidInfoList[$Comment['CidArr']['cid3']]:$_ENV['category']->get($Comment['CidArr']['cid3']);
				$cidInfoList[$Comment['CidArr']['cid4']] = isset($cidInfoList[$Comment['CidArr']['cid4']]['id'])?$cidInfoList[$Comment['CidArr']['cid4']]:$_ENV['category']->get($Comment['CidArr']['cid4']);

				$to_type = "";
				
			
				if($cidInfoList[$Comment['CidArr']['cid']]['id']) $to_type .='-'.$cidInfoList[$Comment['CidArr']['cid']]['name'].'-';
				if($cidInfoList[$Comment['CidArr']['cid1']]['id']) $to_type .='-'.$cidInfoList[$Comment['CidArr']['cid1']]['name'].'-';
				if($cidInfoList[$Comment['CidArr']['cid2']]['id']) $to_type .='-'.$cidInfoList[$Comment['CidArr']['cid2']]['name'].'-';
				if($cidInfoList[$Comment['CidArr']['cid3']]['id']) $to_type .='-'.$cidInfoList[$Comment['CidArr']['cid3']]['name'].'-';
				if($cidInfoList[$Comment['CidArr']['cid4']]['id']) $to_type .='-'.$cidInfoList[$Comment['CidArr']['cid4']]['name'].'-';
					$TransformLogList['LogList'][$key]['to_type'] = $to_type;
			}
			$departstr = page($TransformLogList['LogCount'], $pagesize, $page, "admin_complain/transformLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['AcceptOperator'])."/".urlencode($ConditionList['ApplyOperator'])."/".$ConditionList['QuestionId']."/".$ConditionList['TransformStatus']);
			$downloadstr = page_url("<下载EXCEL表格>", "admin_complain/transformLogView/".$ConditionList['StartDate']."/".$ConditionList['EndDate']."/".urlencode($ConditionList['author'])."/".urlencode($ConditionList['AcceptOperator'])."/".urlencode($ConditionList['ApplyOperator'])."/".$ConditionList['QuestionId']."/".$ConditionList['TransformStatus']."/".$page."/export");
			$msg && $message = $msg;
			$ty && $type = $ty;
		}
		else
		{


		}
		include template('transformlogview','admin');        
    }
	function ontransformLogView_acceptform()
	{
		$TransformLogId= isset($this->post['TransformLogId']);
		$AcceptComplainTransformLogPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "acceptTransformLog");
		if($AcceptComplainTransformLogPrivilege['return']==1)
		{
			$return_arr = "
			<table><tr><td width>
			审批结果：</td><td><select id = 'AcceptResult'>
			<option value = 1>通过</option>
			<option value = 2>拒绝</option>
			</select></td></tr>
			<tr><td>理由：</td><td><textarea name='AcceptReason' id='AcceptReason' cols='50' rows='3'></textarea></td></tr></table>";
		}
		else
		{
			$return_arr = $AcceptComplainTransformLogPrivilege['comment'];
		}
	   exit($return_arr);
	}
	function ontransformLogView_accept()
	{
		
		$TransformLogId= intval($this->post['TransformLogId']);
		$AcceptResult = intval($this->post['AcceptResult']);
		$AcceptResult = in_array($AcceptResult,array(1,2))?$AcceptResult:1;
		$AcceptReason = trim($this->post['AcceptReason']);
		$AcceptComplainTransformLogPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "acceptTransformLog");
		if($AcceptComplainTransformLogPrivilege['return']==1)
		{
			$LogInfo = $_ENV['question']->getTransformLogById($TransformLogId);
			
			if($LogInfo['TransformLogId']>0)
			{
				if(intval($LogInfo['transform_status'])==0)
				{
					if($AcceptResult ==1)
					{
						$comment = unserialize($LogInfo['comment']);
						$modify = $_ENV['question']->modifyQuestionCid($LogInfo['from_id'],$comment['CidArr'],$LogInfo['ApplyOperator'],$this->setting["askSuggestTransComplain"],$comment['TransformReason'],1);
						// 更新成功 
						if($modify['result'] == 1 || $modify['result'] == 7)
						{
							//转换到投诉
							$convert = $_ENV['question']->convertQuestionToComplain($LogInfo['from_id'],$modify['from_type'],$modify['to_type'],$LogInfo['ApplyOperator'],$comment['TransformReason']);
							if($convert>0)
							{
								$message = $this->ask_login_name."同意 ".$LogInfo['ApplyOperator']."将问题".$LogInfo['from_id']."改为投诉,关联投诉单号：".$convert."理由为：".$AcceptReason;
								$log = $this->sys_admin_log($LogInfo['from_id'],$this->ask_login_name,$message,5);//系统操作日志
								$comment['AcceptReason'] = $AcceptReason;
								$logInfo = array('acceptTime'=>time(),"AcceptOperator"=>$this->ask_login_name,"transform_status"=>1,"comment"=>serialize($comment),"to_id"=>$convert);
								$_ENV['question']->updateTransformLog($TransformLogId,$logInfo);							
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
									$this->delete_search($LogInfo['from_id']);
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
								$_ENV['question']->rebuildQuestionDetail($LogInfo['from_id'],"question");
								$_ENV['question']->rebuildQuestionDetail($complainInfo['id'],"complain");
								$return =array('return'=>1,'comment'=>"已通过");
									
							}
							else
							{
								$return =array('return'=>0,'comment'=>"审批失败");
							}			
						}
						else
						{
							$return =array('return'=>$modify['result'],'comment'=>$modify['comment']);
						}
					}
					else
					{
						$comment = unserialize($LogInfo['comment']);
						$comment['AcceptReason'] = $AcceptReason;
						$logInfo = array('acceptTime'=>time(),"AcceptOperator"=>$this->ask_login_name,"transform_status"=>2,"comment"=>serialize($comment));
						$update = $_ENV['question']->updateTransformLog($TransformLogId,$logInfo);
						if($update)
						{
							$message = $this->ask_login_name."拒绝 ".$LogInfo['ApplyOperator']."将问题".$LogInfo['from_id']."改为投诉，理由为：".$AcceptReason;
							$log = $this->sys_admin_log($LogInfo['from_id'],$this->ask_login_name,$message,5);//系统操作日志
							$return =array('return'=>1,'comment'=>"已拒绝");
						}
						else
						{
							$return =array('return'=>0,'comment'=>"审批失败");
						}
					}
				}
				elseif(intval($LogInfo['transform_status'])==1)
				{
					$return = array('return'=>0,'comment'=>"此单已经转换完毕，无需重复审核，审核人：".$LogInfo['AcceptOperator']);
				}
				elseif(intval($LogInfo['transform_status'])==2)
				{
					$return =array('return'=>0,'comment'=>"此单已被拒绝，审核人：".$LogInfo['AcceptOperator']);
				}
			}
			else
			{
				$return = array('return'=>0,'comment'=>"无此申请记录");
			}
		}
		else
		{
			$return = array('return'=>0,'comment'=>$AcceptComplainTransformLogPrivilege['comment']);
		}
		echo json_encode($return);
	}
    function oncomplainView_resync()
	{
		$hasResyncPrivilege = $_ENV['menu']->checkPermission($this->ask_login_name, $_SERVER['QUERY_STRING'], "complainresync");
		if($hasResyncPrivilege['return'] == 1)
		{
			$id = $this->post['id'];
			$complainInfo = $_ENV['complain']->get_ComplainInfo($id);
			if($complainInfo['id'])
			{
				if($complainInfo['sync']!=0)
				{
					$resync = $_ENV['complain']->updateComplain($id,array('sync'=>0));
					if($resync)
					{
						$return = array('return'=>1,'comment'=>'重置成功');
					}
					else
					{
						$return = array('return'=>0,'comment'=>'重置失败');	
					}
				}
				else
				{
					$return = array('return'=>1,'comment'=>'重置成功');	
				}
			}
			else
			{
				$return = array('return'=>0,'comment'=>'无此问题');
			}
		}
		else
		{
			$return = $hasResyncPrivilege;
		}
		exit(json_encode($return));
    }
}
?>
