<?php

!defined('IN_TIPASK') && exit('Access Denied');

class questioncontrol extends base {

    function questioncontrol(& $get, & $post) {
        $this->base(& $get, & $post);
        $this->load("post");
        $this->load('operator');
        $this->load("common_question");
        $this->load("question");
        $this->load("tag");
        $this->load("answer");
        $this->load("complain");
        $this->load("qtype");
        $this->load("category");
        $this->load("bind_log");
		$this->load("user");
    }

    function ondefault() {
        $this->onask();
    }

    //提问页面
    function onask()
    {    	
		$question_type = "ask";
		$question_type_list = $this->ask_config->getQuestionType();

		$title = "服务中心-我要".$question_type_list[$question_type];
    	$all_num = $_ENV['question']->total_question();
    	$qtypeId = isset($this->get[2]) ? intval($this->get[2]):(isset($this->post['qtypeId'])?intval($this->post['qtypeId']):0);
		$allQtype = $_ENV['qtype']->GetAllQType(1,"",0);
		$loginName = $this->ask_front_name;
    	if(!isset($allQtype[$qtypeId]))
    	{
    		header("Location: http://sc.5173.com/index.php?question/ask_skip.html");
    	}
		$subList = $_ENV['qtype']->GetSubList($qtypeId);
		if(!empty($subList)) // 没有下级目录跳到 对用问题提问页面
		{
			header("Location: http://sc.5173.com/index.php?question/subList/ask/{$qtypeId}.html");
		}
		$qtypeName = $allQtype[$qtypeId];
		
		$operatorInfo = ''; // 获取我的专属客服
		if($this->ask_front_name == '游客')
		{
			$selfAuthor_where = ''; // 获取专属客服条件
		}
		else
		{
			$t_cid = $_ENV['question']->getType(3);
			$operatorInfo = $_ENV['operator']->getMySelfAuthor($this->ask_front_name);
			$selfAuthor_where = $_ENV['question']->front_selfAuthor_where($operatorInfo['login_name'],'',1,$t_cid);
		}
		if($selfAuthor_where)
		{
			$selfAuthorNum = $_ENV['question']->front_mySelfAuthorNum($selfAuthor_where);
		}
		else 
		{
			$selfAuthorNum = 0;
		}
		$url = '';
		if($qtypeName['pid']>0)
		{
			$url = '<a href="http://sc.5173.com/index.php?question/subList/ask/'.$qtypeName['pid'].'.html">选择'.$allQtype[$qtypeName['pid']]['name'].'类'.$question_type_list[$question_type].'</a>&nbsp;&nbsp;&gt;&nbsp;&nbsp';
		}

    	//未登陆跳转地址
    	$login_url = "http://".config::FRONT_LOGIN_DOMAIN."/?returnUrl=".urlencode(curPageURL());
    	$display_yzm = false;
    	
    	if($this->ask_front_name != '游客')
    	{
    		$contact = $this->cache->get(md5('SJ'.$this->ask_front_id));
    		if(false === $contact)
    		{
    			$contact = get_mobile($this->ask_front_id);
    			if(!empty($contact)) $this->cache->set(md5('SJ'.$this->ask_front_id),$contact,1800);//缓存30分钟
    		}
    		if(!empty($contact)) $en_contact = substr_replace($contact,'****',3,4);
    	}

    	$J_ques_t = isset($this->post['title']) ? htmlspecialchars(trim($this->post['title'])):'';
    	$add_text = isset($this->post['description']) && $this->post['description'] != "请详细描述您要".$question_type_list[$question_type]."的内容" ? cutstr(htmlspecialchars(trim($this->post['description'])),500,''):'';
    	if($this->ask_front_name == '游客')
    	{
    		$author = isset($this->post['author']) && $this->post['author'] != '请输入5173用户名' ? trim($this->post['author']) : '';
    		$author_id = '';
    	}
    	else
    	{
    		$author_id = $this->ask_front_id;
    		$author = $this->ask_front_name;
    	}
    	$comment['OnceAnsweredQQ'] = isset($this->post['OnceAnsweredQQ']) ? trim($this->post['OnceAnsweredQQ']) : '';
    	if( isset($this->post['contact']) )
    	{
    		$comment['contact'] = $this->post['contact'];
    	}
    	else
    	{
    		$comment['contact']['mobile'] = isset($en_contact)?$en_contact:'';
    	}


    	$J_code = isset($this->post['J_code'])?strtolower(htmlspecialchars($this->post['J_code'])):'';

    	$t_yzm = tcookie('yzm');
    	if(empty($t_yzm)){
    		tcookie('yzm',time(),1800);//存放半个小时
    	}else{
    		$over_time = time() - $t_yzm; //距离现在的秒数
    		if($over_time < 1800){
    			$display_yzm = true;
    		}else{
    			tcookie('yzm','',time()-3600);//删除
    		}
    	}
    	if($this->ask_front_name != '游客') $display_yzm = false;	//登陆用户不显示验证码
    	$flag = 0;
    	if(isset($this->post['act']))
    	{
    	  if( isset($this->post['contact']) )
			{
				if($comment['contact']['mobile'] != "")
				{
					if(isset($en_contact) && $comment['contact']['mobile'] == $en_contact)
					{
						$comment['contact']['mobile'] = $contact;
					}
					if(!checkmobile($comment['contact']['mobile']))
					{
						$errorMsg['mobile'] = '手机号';
						unset($comment['contact']['mobile']);
					}
					else
					{
						$flag++;
					}
				}
				else
				{
					$errorMsg['mobile'] = '手机号';
					unset($comment['contact']['mobile']);
				}
				if($comment['contact']['qq'] != "")
				{
					if (!isQQ($comment['contact']['qq']))
					{
						//$errorMsg['qq'] = 'QQ号';
						unset($comment['contact']['qq']);
					}
					else
					{
						//$flag++;
					}
				}
				else
				{
					//$errorMsg['qq'] = 'QQ号';
					unset($comment['contact']['qq']);
				}
				if($comment['contact']['weixin'] != '')
				{
					if(strlen($comment['contact']['weixin']) > 20 || strlen($comment['contact']['weixin']) <4 || trim($comment['contact']['weixin'])=="微信号")
					{
						//$errorMsg['weixin'] = '微信号';
						unset($comment['contact']['weixin']);
					}
					else
					{
						//$flag++;
					}
				}
				else
				{
					//$errorMsg['weixin'] = '微信号';
					unset($comment['contact']['weixin']);
				}
			}


			if($flag==0 && count($errorMsg)>0)
			{
        		$comment['contact'] = $this->post['contact'];
				$error = implode("、",$errorMsg)."未填写或格式不正确";
				@include template('ask');
				echo "<script>alert('".$error."');</script>";
				exit;
			}
    		if($add_text == ''){
        		$comment['contact'] = $this->post['contact'];
    			@include template('ask');
    			echo "<script>alert('咨询内容不能为空。');</script>";
    			exit;
    		}else if(mb_strlen($add_text,'UTF-8') > 500 || mb_strlen($add_text,'UTF-8')<5){
        		$comment['contact'] = $this->post['contact'];
    			@include template('ask');
    			echo "<script>alert('咨询内容请保持在5-500字内。');</script>";
    			exit;
    		}

    		if($this->ask_front_name == '游客') //未登录提问（快捷提问）
    		{
    			if($author=="" || mb_strlen($author,'UTF-8')>20)
    			{
    				@include template('ask');
    				echo "<script>alert('请输正确格式的5173登陆用户名');</script>";
    				exit;
    			}
    			if($J_code==""||$J_code != $_SESSION['code'])
    			{
    				@include template('ask');
    				echo "<script>alert('验证码不正确！');</script>";
    				exit;
    			}
    		}

    		//提问数限制
    		$limit_question_num = intval($this->setting['limit_question_num']);
    		if(!empty($limit_question_num)){
    			$num_ip = $_ENV['question']->get_num_by_ip(getip());
    			if($num_ip >= $limit_question_num){
        		$comment['contact'] = $this->post['contact'];
    				@include template('ask');
    				echo "<script>alert('您的操作太频繁啦，让服务器休息一下，稍后再进行咨询！');</script>";
    				exit;
    			}
    		}
    		//IP黑名单
			$BlackList = explode("|",$this->setting['IpBlackList']);
			if(in_array(getip(),$BlackList))
			{
				$comment['contact'] = $this->post['contact'];
				@include template('ask');
				echo "<script>alert('您的操作太频繁啦，让服务器休息一下，稍后再咨询！');</script>";
				exit;
			}
    		

    		if($this->ask_front_name != '游客'){//登录提问
    			$GagLog = $_ENV['user']->getGag($this->ask_front_name);				
    			if(count($GagLog)>0)
				{
					$comment['contact'] = $this->post['contact'];
    				@include template('ask');
    				echo "<script>alert('很抱歉，您的帐号已被管理员禁言处理，请您自觉遵守5173言论规则。');</script>";
    				exit;
    			}
    		}
			$description = strip_tags($add_text);
    		if(md5(trim(strip_tags($description)))==$_COOKIE['last_ask'])
			{
    				@include template('ask');
    				echo "<script>alert('亲，问题提交一次就OK，不用重复提交哦！');</script>";
    				exit;
			}
    		/*
			if(!empty($_FILES['uploadfile']['name']))
			{
				@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
    			$FastDFSClient = new FastDFSClient();
    			$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
    			$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
    			$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
    			$FastDFSInfo = $FastDFSClient->upload("sk");
    			if($FastDFSInfo == -1){
        		    $comment['contact'] = $this->post['contact'];
    				@include template('ask');
    				echo "<script>alert('".$FastDFSClient->getErrorMsg()."');</script>";
    				exit;
    			}
    		}
			else
			{
				$FastDFSInfo = -1;
			}
			*/
			{
				$img_path = $this->post['imgpath'];
				$img_path = stripcslashes($img_path);
				$img_path = str_replace('"small_pic"',',"small_pic"',$img_path);
				$img_path = str_replace('"big_pic"',',"big_pic"',$img_path);
				$p1 = strpos($img_path,"big_pic");
				$path = substr($img_path,$p1+10,strlen($img_path)-$p1-10-2);
				$path = str_replace('\/','/',$path);
			}
    		if(isset($en_contact) && $comment['contact']['mobile'] == $en_contact)
    		{
    			$comment['contact']['mobile'] = $contact;
    		}
    		$attach = trim($path);
			//$attach = $FastDFSInfo != -1?$FastDFSInfo:"";
    		$cid = $_ENV['question']->getType(1); //咨询分类id
    		$cid = !empty($cid)?intval($cid):0;
    		$cid1Info = $_ENV['category']->getByQType($qtypeId,$cid); //qtype对应分类id
			$cid1 = intval($cid1Info['id']);
			$time = time();
    		$trimDescription = preg_replace('/\s+/', '', $description);
    		$description = $this->keyWordCheck($trimDescription);
			$BrowerInfo = userBrowerInfo();
			$comment['OS'] = $BrowerInfo['OS'];
			$comment['Browser'] = $BrowerInfo['Browser'];
			$questionInfo = array(
								"qtype"=>$qtypeId,
								"author"=>$author,
								"author_id"=>$author_id,
								"title"=>strip_tags($J_ques_t),
								"description"=>$description,
								"attach"=>$attach,
								"time"=>$time,
								"ip"=>getip(),
								"cid"=>$cid,
								"cid1"=>$cid1,
								"comment"=>serialize($comment)
			);
			$question_id = $_ENV['question']->insertQuestion($questionInfo);
    		//更新Solr服务器
    	    $q_search = array();
    		if($question_id > 0)
    		 {
    		 	$login_name = trim($this->post['login_name']);
    		 	if(!empty($login_name))
    		 	{
    		 		if($this->setting['selfServiceFirst']==1)
					{
						$Apply = $_ENV['question']->ApplyToOperator($question_id,$login_name);
					}
    		 	}
                setcookie('last_ask',md5(trim(strip_tags($description))),time()+3600);
					
                $date = date("Y-m-d");
                $_ENV['question']->modifyUserQtypeNum($date,$qtypeId,'ask',1);

    			if($this->ask_front_name == '游客')
    			{
    				get_que_id('zx',$question_id); //咨询id写入cookie,存一个月
    			}
    			$q_search['id'] = $question_id;
    			$q_search['title'] = $add_text;
    			$q_search['description'] = $add_text;
    			$q_search['tag'] = json_encode(array(),true);
    			$q_search['time'] = $time;
				$q_search['atime'] = 0;
    			try{
    				$this->set_search($q_search);
    			}catch(Exception $e){
    				send_AIC('http://sc.5173.com/index.php?question/ask.html','搜索服务器异常',1,'搜索接口');
    			}
    		}
    		header("Location: ".url('question/success/'.$question_id.'/'.$time,true));
    	}
		
		$telDisplay = $this->setting['telDisplay'];
		$xnDisplay = $this->setting['xnDisplay'];
		$qqDisplay = $this->setting['qqDisplay'];
    	$_ENV['question']->PageView(3,getip());
		@include template('ask');
    }
    //咨询成功页面
    function onsuccess(){
    	$title = "服务中心-咨询提交成功";
    	$all_num = $_ENV['question']->total_question();
    	$loginName = $this->ask_front_name;
    	$operatorInfo = ''; // 获取我的专属客服
    	$myAsk = 'my_ask';
    	if($loginName != '游客')
    	{
    		$t_cid = $_ENV['question']->getType(3);
    		$operatorInfo = $_ENV['operator']->getMySelfAuthor($this->ask_front_name);
    	}
    	include template('success');
    }
    //投诉成功页面
    function oncomplain_success(){
    	$title = "服务中心-投诉提交成功";
    	$all_num = $_ENV['question']->total_question();
    	$time = isset($this->get[3])?intval($this->get[3]):'0';
    	$date = date('m-d H:i:s',$time);
    	include template('complain_success');
    }
    //建议成功页面
    function onsuggest_success(){
    	$title = "服务中心-建议提交成功";
    	$all_num = $_ENV['question']->total_question();
    	$loginName = $this->ask_front_name;
    	$operatorInfo = ''; // 获取我的专属客服
    	$myAsk = 'my_suggest';
    	if($loginName != '游客')
    	{
    		$t_cid = $_ENV['question']->getType(3);
    		$operatorInfo = $_ENV['operator']->getMySelfAuthor($this->ask_front_name);
    	}
    	include template('success');
    }
    //是否满意
    function onajaxsatisfy()
	{
		if(isset($this->post['question_id']) && isset($this->post['type']))
		{
			$id = $this->post['question_id']; // 父问题id
			$Q_status = $_ENV['question']->Get($id,"author,is_pj,comment");
			$Comment = unserialize($Q_status['comment']);
			$Comment['assess_num']+=1;
			if($this->setting['limit_assess_num']==0 || $this->setting['limit_assess_num']>=$Comment['assess_num'])
			{
				// 判断问题是否评价
				if(($Q_status['is_pj'] == 0) || ($Q_status['is_pj'] == 2)) 
				{  // 未评价或者评价为不满意
					$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
					if($this->ask_front_name == '游客') 
					{
						if(!empty($ask_type))
						{ // 判断是否是自己的问题
							$all_qid = $ask_type['zx'] .','. $ask_type['jy'];
							$type = explode(',',$all_qid);
							if(in_array($id,$type)) 
							{							
								if($this->post['type'] == 1)
								{
									$this->db->query ( "UPDATE " . DB_TABLEPRE . "question SET status='3',is_pj=1,comment = '".serialize($Comment)."' WHERE id=$id or pid=$id and status !=1");
									exit('1');
								}
								else if($this->post['type'] == 2)
								{
									$this->db->query ( "UPDATE " . DB_TABLEPRE . "question SET status='3',is_pj=2,comment = '".serialize($Comment)."' WHERE id=$id or pid=$id and status !=1");
									exit('2');
								}
							}
							else
							{
								exit('4');
							}// 没权评价
						}
						else
						{
							exit('4');
						}
					} 
					elseif ($this->ask_front_name != '游客')
					{
						if(strtolower($this->ask_front_name) == strtolower($Q_status['author']))
						{
							if(!empty($ask_type))
							{ // 判断是否是自己的问题
								$all_qid = $ask_type['zx'] .','. $ask_type['jy'];
								$type = explode(',',$all_qid);
								if(in_array($id,$type)) 
								{
									if($this->post['type'] == 1)
									{
										$this->db->query ( "UPDATE " . DB_TABLEPRE . "question SET status='3',is_pj=1,comment = '".serialize($Comment)."' WHERE id=$id or pid=$id and status !=1");
										exit('1');
									}
									else if($this->post['type'] == 2)
									{
										$this->db->query ( "UPDATE " . DB_TABLEPRE . "question SET status='3',is_pj=2,comment = '".serialize($Comment)."' WHERE id=$id or pid=$id and status !=1");
										exit('2');
									}
								}
							}
							if($this->post['type'] == 1)
							{
								$this->db->query ( "UPDATE " . DB_TABLEPRE . "question SET status='3',is_pj=1,comment = '".serialize($Comment)."' WHERE id=$id or pid=$id and status !=1");
								exit('1');
							}
							elseif($this->post['type'] == 2)
							{
								$this->db->query ( "UPDATE " . DB_TABLEPRE . "question SET status='3',is_pj=2,comment = '".serialize($Comment)."' WHERE id=$id or pid=$id and status !=1");
								exit('2');
							}
						}
						else
						{
							exit('4');
						}// 没权评价
					}
				}
				else 
				{
					// 问题已经评价
					exit('3'); 
				} 				
			}
			else
			{
				// 问题已经评价
				exit('3'); 			
			}		
		}
		else 
		{
			exit('0'); 
		}
    }
    //问题搜索
    function onsearch(){
    	$title = "搜索页面";
    	 $all_num = $_ENV['question']->total_question();
    	 $search = isset($this->post['searchinput'])?trim($this->post['searchinput']):(isset($this->get[2])?str_replace("#",".", urldecode(trim($this->get[2]))):'');
    	 if($search != ''){
    	 	
    	 	 //过滤掉特殊字符+-&|!(){}[]^~*?:
    	 	 $search = search_addcslashes($search);
    	 	 $keyWords = include TIPASK_ROOT.'/lib/KeyWords.php';
    	 	 $search = preg_replace('/\s+/', '', $search);
    	
    	 	 foreach($keyWords as $v)
    	 	 {
    	 	 	if(strpos($search,$v) !==false)
    	 	 	{
    	 	 		$search_result = false;
    	 	 		$search_html = htmlspecialchars($search);
    	 	 		include template('search');
    	 	 		exit;
    	 	 	}
    	 	 }
	         $pagesize  = $this->setting['list_default'];
	         @$quePage = max(1, intval($this->get[3]));
             $startindex = ($quePage - 1) * $pagesize;
             try{
             	$search_arr = $this->get_search(array('description'=>$search),$startindex,$pagesize);
				
			 }catch(Exception $e){
             	$search_arr = array();//对搜索异常进行处理
             	send_AIC('http://sc.5173.com/index.php?question/search.html','搜索服务器异常',1,'搜索接口');
             }
	         //还原特殊字符在页面上的显示+-&|!(){}[]^~*?:
	         $search = search_stripcslashes($search);
			 $search_html = htmlspecialchars($search);
	         if(!empty($search_arr['response']['docs'])){
	         	$search_result = $search_arr['response']['docs'];
	         	$search_highlight = $search_arr['highlighting'];
	         	$tag_arr = array();
	         	foreach($search_result as $key => &$val)
				{
					if($val['question_type']=='complain')
					{
						$search_result[$key]['question_url'] = $_ENV['question']->getQuestionLink(substr($val['id'],2),"complain");
					}
					else
					{
						$search_result[$key]['question_url'] = $_ENV['question']->getQuestionLink($val['id'],"question");
					}
					//搜索标题高亮显示
	         		if(array_key_exists($val['id'],$search_highlight)){
	         			if($search_highlight[$val['id']]['title'][0] != "")
						{
							$search_result[$key]['title'] = $search_highlight[$val['id']]['title'][0];
						}
						if($search_highlight[$val['id']]['description'][0] != "")
						{
							$search_result[$key]['description'] = $search_highlight[$val['id']]['description'][0];
						}
	         		}
	         		//搜索问题标签显示
	         		$cur_tag = json_decode($val['tag'],true);
		         	if(!empty($cur_tag)){
		         		foreach($cur_tag as $tag){
		         			$tag_name = $_ENV['tag']->getById($tag);
		         			if($tag_name != ''){
		         				$search_result[$key]['tag_arr'][$tag] = $tag_name;
		         				if(count($tag_arr) <= 10){
		         					$tag_arr[$tag] = $tag_name;
		         				}
		         			}
		         		}
		         	}
	         	}
	         	$rownum = $search_arr['response']['numFound'];
	            $totalPage = @ceil($rownum / $pagesize);
    	        $quePage > $totalPage  && $quePage = $totalPage;
	         	$departstr = front_page($rownum, $pagesize, $quePage, "question/search/". urlencode(str_replace(".","#",$search)));
	         }

    	 }

    	 $common_list    =  $_ENV['common_question']->get_common_list(true);          // 常见问答
    	 include template('search');
    }
	//获取验证码
	function onget_code(){
		require TIPASK_ROOT . '/lib/validatecode/validatecode.class.php';//先把类包含进来，实际路径根据实际情况进行修改。
		$_vc = new ValidateCode();      //实例化一个对象
		$_vc->doimg();
		$_SESSION['code'] = $_vc->getCode();//验证码保存到SESSION中
	}
	//我的咨询
	function onmy_ask(){
		$title = '我的咨询';
		$all_num = $_ENV['question']->total_question();
		$ask_time   = isset($this->post['ask_time']) ? intval($this->post['ask_time']):(isset($this->get[2])?intval($this->get[2]):0);
		$ask_status = isset($this->post['ask_status']) ? intval($this->post['ask_status']):(isset($this->get[3])?intval($this->get[3]):-1);
		
		$pagesize   = $this->setting['list_default'];
		@$quePage   = max(1, intval($this->get[4]));
		$MyServiceLog = $_ENV['question']->myServiceLog($this->ask_front_name,'ask',$ask_time,$ask_status,$quePage,$pagesize);
		include template('my_ask');
	}
	//我的投诉
	function onmy_complain(){
		$title = '我的投诉';
		$all_num = $_ENV['question']->total_question();
		$ask_time   = isset($this->post['ask_time']) ? intval($this->post['ask_time']):(isset($this->get[2])?intval($this->get[2]):0);
		$ask_status = isset($this->post['ask_status']) ? intval($this->post['ask_status']):(isset($this->get[3])?intval($this->get[3]):-1);
		$comStatus  = $this->ask_config->getComStatus();
		$complainSwitch = intval($this->setting['complainSwitch']);
		$complainReasonSwitch = intval($this->setting['complainReasonSwitch']);
		$revokeResaon = $_ENV['complain']->GetRevokeReason();
		$pagesize   = $this->setting['list_default'];
		@$quePage   = max(1, intval($this->get[4]));
		$MyServiceLog = $_ENV['question']->myServiceLog($this->ask_front_name,'complain',$ask_time,$ask_status,$quePage,$pagesize);
		include template('my_complain');
	}
	//撤销我的投诉,带理由
	function onmy_comRevoke()
	{
		$id = intval($this->post['id']);
		$author = trim($this->post['author']);
		$otherReason = trim($this->post['otherReason']);
		$reasonType = intval($this->post['reasonType']);
		// 其他撤销原因
		if($reasonType == 0)
		{
			if($otherReason=="")
			{
				$returnData = 1; // 撤销原因未填写
			}
			else
			{
				$replace = array('@'=>'','/'=>'','\\'=>'','|'=>'','、'=>'');
				$revokeReason = strtr($otherReason,$replace);
			}
		}
		else
		{
			// 非其他撤销原因
			$revokeResaon = $_ENV['complain']->GetRevokeReason();
			foreach($revokeResaon as $v)
			{
				if( $v['reason_id'] == $reasonType)
				{
					$revokeReason = $v['content'];
					break;
				}				
			}
		}
		
		if(!isset($revokeReason))
		{
			$returnData = 1; // 撤销原因未填写
		}
		else
		{
			$complainSwitch = intval($this->setting['complainSwitch']);
			$complainInfo = $_ENV['complain']->Get($id,'id,time,description,status,sync,comment,author');
			
			if($complainInfo['id'])
			{
				$loginName = $this->ask_front_name;
				$hiddenRevocation = false;
				if($loginName=='游客')
				{
					if(isset($_COOKIE['quickask']))
					{
						$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
						if(isset($ask_type['ts']))
						{
							$IsFind = strpos($ask_type['ts'],$complainInfo['id']);
							if($IsFind!==false)
							{
								$hiddenRevocation = true;
							}
						}
					}
				}
				else
				{
					if(strtolower($complainInfo["author"])==strtolower($loginName))

					{
						$hiddenRevocation = true;
					}
				}				
				if($complainInfo['sync']==1&&$hiddenRevocation)
				{
					if($complainInfo['status']==2)
					{
						$returnData = 7; // 重复撤销
					}
					else
					{
						
					   if($complainInfo['status']==0 || $complainSwitch==1)
					   {
							$ip = $_SERVER["REMOTE_ADDR"];
							$rtime = $_SERVER['REQUEST_TIME'];
							$comment = unserialize($complainInfo['comment']);
							$comment['revoke'] = array('rtime'=>$rtime,'revokeReason'=>$revokeReason,'ip'=>$ip);
							
							$updateNum = $_ENV['complain']->Update($id,array('status'=>2,'rtime'=>$rtime,'comment'=>serialize($comment)));
							if($updateNum>0)
							{
								$q_search['id'] = 'c_'.$id;
								$q_search['title'] = $complainInfo['description'];
								$q_search['description'] = $complainInfo['description'];
								$q_search['tag'] = json_encode(array(),true);
								$q_search['question_type'] = 'complain';
								$q_search['time'] = $complainInfo['time'];
								$q_search['atime'] = -1;
								$this->set_search($q_search);
								$url = "http://complain.5173esb.com/Sc/PostCancel.aspx";
								$data = "scid=$id&uid=".urlencode($author)."&ip=$ip&revokeTime=$rtime&revokeReason=".urlencode($revokeReason)."&sign=".config::TS_SIGN;
								$result = do_post($url,$data);
								$result_arr = json_decode($result,true);
								if($result_arr['return']!=1)
								{
									$revokeArr = array('scid'=>$id,'uid'=>$author,'ip'=>$ip,'revokeTime'=>$rtime,'revokeReason'=>$revokeReason);
									$_ENV['complain']->insertRevokeQueue($revokeArr);
								}
																
								$returnData = 2; // 成功
								$_ENV['question']->rebuildQuestionDetail($id,"complain");
							}
							else
							{
								$this->pdo->rollBack();
								$returnData = 3; // 失败回滚
							}
					    }
						else
						{
							$returnData = 4; // 撤销开关没打开
						}
					}
				}
				else
				{
					$returnData = 5;// 系统忙，请稍后再试！
				}
			}
			else
			{
				$returnData = 6; // 问题不存在
			}
			
		}
		echo $returnData;
	}
	// 撤销我的投诉,无理由
	function onmy_revokeNoReason(){
		$id     = isset($this->get[2])?intval($this->get[2]):"";
		$author = isset($this->get[3])?trim($this->get[3]):"";
		$skipUrl = isset($this->get[4])?trim($this->get[4]):"";
		$rtime = $_SERVER['REQUEST_TIME'];
		if($id && author)
		{
			$complainSwitch = intval($this->setting['complainSwitch']);
			$complainInfo = $_ENV['complain']->Get($id,'id,time,description,status,sync,author,comment');
			
			$loginName = $this->ask_front_name;
			$hiddenRevocation = false;
			if($loginName=='游客')
			{
				if(isset($_COOKIE['quickask']))
				{
					$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
					if(isset($ask_type['ts']))
					{
						$IsFind = strpos($ask_type['ts'],$complainInfo['id']);
						if($IsFind!==false)
						{
							$hiddenRevocation = true;
						}
					}
				}
			}
			else
			{
				if(strtolower($complainInfo["author"])==strtolower($loginName))
				{
					$hiddenRevocation = true;
				}
			}
			if($complainInfo['sync']==1&&$hiddenRevocation)
			{
				if($complainInfo['status']==2)
				{
					$backReturn =  array('comment'=>'该投诉已撤销，请勿重复撤销！','url'=>"?question/$skipUrl/$id"); // 重复撤销
				}
				else
				{
					if($complainInfo['status']==0 || $complainSwitch==1)
					{
						$ip = $_SERVER["REMOTE_ADDR"];
						
						$comment = unserialize($complainInfo['comment']);
						$comment['revoke'] = array('rtime'=>$rtime,'revokeReason'=>'无理由','ip'=>$ip);
						$updateNum = $_ENV['complain']->Update($id,array('status'=>2,'rtime'=>$rtime,'comment'=>serialize($comment)));
						if($updateNum>0)
						{
							$q_search['id'] = 'c_'.$id;
							$q_search['title'] = $complainInfo['description'];
							$q_search['description'] = $complainInfo['description'];
							$q_search['tag'] = json_encode(array(),true);
							$q_search['question_type'] = 'complain';
							$q_search['time'] = $complainInfo['time'];
							$q_search['atime'] = -1;
							$this->set_search($q_search);
							$url = "http://complain.5173esb.com/Sc/PostCancel.aspx";
							$data = "scid=$id&uid=".urlencode($author)."&revokeTime=$rtime&sign=".config::TS_SIGN."&ip=$ip";
							$result = do_post($url,$data);
							$result_arr = json_decode($result,true);
							if($result_arr['return']!=1)
							{
								$revokeArr = array('scid'=>$id,'uid'=>$author,'ip'=>$ip,'revokeTime'=>$rtime,'revokeReason'=>"");
								$_ENV['complain']->insertRevokeQueue($revokeArr);
							}
							$_ENV['question']->rebuildQuestionDetail($id,"complain");
							$backReturn =  array('comment'=>'您的撤销已成功！','url'=>"?question/$skipUrl/$id");
						}
						else
						{
							$backReturn =  array('comment'=>'您的撤销失败，请刷新重试！','url'=>"?question/$skipUrl/$id");
						}
							
					}
					else
					{
						$backReturn =  array('comment'=>'对不起，您没有撤销权限！','url'=>"?question/$skipUrl/$id");
					}
				}
						
			}
			else
			{
				$backReturn =  array('comment'=>'系统忙，请稍后再试！','url'=>"?question/$skipUrl/$id");
			}
		}
		else
		{
			$backReturn =  array('comment'=>'非法参数','url'=>"?question");
		}
		__msg($backReturn);
	}
	//我的投诉详情页
	function oncomplain_detail()
	{
		$status = isset($this->get[3]) ? intval($this->get[3]) : -1;
		$id = intval($this->get[2]);
		if($id>0)
		{
			$comDetail = $_ENV['complain']->Get($id,'*','0,2');
			
			if(isset($comDetail['id']))
			{
				$loginName = $this->ask_front_name;
				if($comDetail['public']==2)
				{
					$this->delete_search('c_'.$id);
					if($loginName=='游客')
					{
						if(isset($_COOKIE['quickask']))
						{
							$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
							if(isset($ask_type['ts']))
							{
								$Idlist = explode(",",$ask_type['ts']);
								if(!in_array($comDetail['id'],$Idlist))
								{
									//id不存在跳转
									header("Location: http://sc.5173.com") && exit;
								}
							}
						}
						else
						{
							//cookie不存在跳转
							header("Location: http://sc.5173.com") && exit;						
						}
					}
					else
					{
						if(strtolower($comDetail["author"])!=strtolower($loginName))
						{
							//id不存在跳转
							header("Location: http://sc.5173.com") && exit;
						}
					}					
				}
				$hiddenRevocation = false;
				if($loginName=='游客')
				{
					if(isset($_COOKIE['quickask']))
					{
						$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
						if(isset($ask_type['ts']))
						{
							$IsFind = strpos($ask_type['ts'],$comDetail['id']);
							if($IsFind!==false)
							{
								$hiddenRevocation = true;
								$ass = 1;
							}
						}
					}
				}
				else
				{
					if($comDetail["author"]==$loginName)
					{
						$hiddenRevocation = true;
						$ass = 1;
					}
				}
				
				$title = '服务中心 - 投诉详情';
				$all_num = $_ENV['question']->total_question();
				$MyServiceLog = $_ENV['question']->myServiceLog($this->ask_front_name,'',0,$status,0,0);
				
				$complainSwitch = intval($this->setting['complainSwitch']); // 投诉撤销开关
				$complainReasonSwitch = intval($this->setting['complainReasonSwitch']); // 投诉理由开关
				$revokeResaon = $_ENV['complain']->GetRevokeReason(); // 投诉理由
				
				$call_type_list = $this->ask_config->getCallType();
				$EvaluateCount = $_ENV['complain']->getEvaluateCount();
				$o = $this->cache->get("peratorCommunication_".$comDetail['loginId']);
				if(false !== $o)
				{
					$comDetail['operator'] = json_decode($o,true);
				}
				else
				{
					$comDetail['operator'] = $_ENV['operator']->getOperatorFromVadmin($comDetail['loginId']);
					$this->cache->set("peratorCommunication_".$comDetail['loginId'],json_encode($comDetail['operator']),180);
				}
				$comp_Anlist =  $_ENV['complain']->GetAnswer($id);
				if(is_array($comp_Anlist))
				{
					$o = $this->cache->get("peratorCommunication_".$comp_Anlist['contact']);
					if(false !== $o)
					{
						$comp_Anlist['operator'] = json_decode($o,true);
					}
					else
					{
						$comp_Anlist['operator'] = $_ENV['operator']->getOperatorFromVadmin($comp_Anlist['contact']);
						$this->cache->set("peratorCommunication_".$comp_Anlist['contact'],json_encode($comp_Anlist['operator']),180);
					}
				
					$comp_Anlist['timeLag'] = $this->timeLagToText($comDetail['time'],$comp_Anlist['time']);
				}
				$qtype = $_ENV['qtype']->GetQType($comDetail['qtype']);
				$category = !empty($qtype['name']) ? '['.$qtype['name'].'] ':'[其他分类] ';
				
				$contactM = '';
				if( !empty($comDetail['contact']) )
				{
					$contact = unserialize($comDetail['contact']);
					foreach($contact['contact'] as $k=> $v)
					{
						if($k == 'mobile' && $v != '')
						{
							$contactM = substr_replace($contact['contact']['mobile'],'****',3,4);
							break;
						}
						else if( $v !='')
						{
							$contactM = $v;
							break;
						}
					}
				}
				if( !empty($comDetail['real_name']) )
				{
					$comDetail['real_name'] = en_chinese($comDetail['real_name']);
				}
				$telDisplay = $this->setting['telDisplay'];
				$xnDisplay = $this->setting['xnDisplay'];
				$qqDisplay = $this->setting['qqDisplay'];

				/* if($this->ask_front_name != $comDetail['author'])
				 {
				//未登陆跳转地址
				$login_url = "http://".config::FRONT_LOGIN_DOMAIN."/?returnUrl=http://sc.5173.com";
				header("Location: ".$login_url) && exit;
				} */
				if($this->ask_front_name != '游客')
				{
					if(strtolower($this->ask_front_name) == strtolower($comDetail['author']))
					{
						if( false !== $this->cache->get('ts'.$id) ) $this->cache->rm('ts'.$id);
					}
				}
				include template('my_comDetail');
			}
			else
			{
				$this->delete_search('c_'.$id);
				//未登陆跳转地址
				header("Location: http://sc.5173.com") && exit;
			}
		}
		else
		{
			//未登陆跳转地址
			header("Location: http://sc.5173.com") && exit;
		}
	}
	//我的投诉评价详情页
	function oncomStatisfy(){
	   $id   = isset($this->post['id'])?intval($this->post['id']):-1; // 问题id
	   $type = isset($this->post['type'])?intval($this->post['type']):-1;
	   $author_id = isset($this->post['userid'])?trim($this->post['userid']):'';

	   if($id==-1 || $type==-1 || $author_id=='') exit('0');
	   $now= time();
	   $assess = $this->db->fetch_first("SELECT assess,asnum FROM " . DB_TABLEPRE . "complain WHERE id=$id");

	   if (empty($assess['assess']))
	   {
		$url  = "http://complain.5173esb.com/Sc/PostEvaluate.aspx";
		$data = "scid=$id&iJudgeInt=$type&userid=$author_id&sign=".config::TS_SIGN;
		do_post($url,$data);
	   }

	   if($this->post['type'] == 1){
		$this->db->query( "UPDATE " . DB_TABLEPRE . "complain SET `asnum`=asnum+1,`assess`=1,`astime`=$now,`status`=3 WHERE id=$id");
		exit('1');
	   }elseif($this->post['type'] == 2){
		$this->db->query( "UPDATE " . DB_TABLEPRE . "complain SET `asnum`=asnum+1,`assess`=2,`astime`=$now,`status`=3 WHERE id=$id");
		exit('2');
	   }
	}
	//刷新投诉view
	function onrefreshView(){
		$id = isset($this->post['id'])?intval($this->post['id']):'0'; // 父问题id
		if($id){
			$this->db->query("UPDATE " . DB_TABLEPRE . "complain SET view=view+1 WHERE id=$id");
		}
	}
	//我的建议
	function onmy_suggest(){
		$title = '我的建议';
		$all_num = $_ENV['question']->total_question();
		$ask_time   = isset($this->post['ask_time']) ? intval($this->post['ask_time']):(isset($this->get[2])?intval($this->get[2]):0);
		$ask_status = isset($this->post['ask_status']) ? intval($this->post['ask_status']):(isset($this->get[3])?intval($this->get[3]):-1);

		$pagesize   = $this->setting['list_default'];
		@$quePage   = max(1, intval($this->get[4]));		
		$MyServiceLog = $_ENV['question']->myServiceLog($this->ask_front_name,'suggest',$ask_time,$ask_status,$quePage,$pagesize);
		include template('my_suggest');
	}
	//投诉
	function oncomplain()
	{	
    	$question_type = "complain";
		$question_type_list = $this->ask_config->getQuestionType();
		$title = "服务中心-我要".$question_type_list[$question_type];
		// 我的服务记录
		$all_num = $_ENV['question']->total_question();
		// 获取8大类列表
		$qtypeId = isset($this->get[2]) ? intval($this->get[2]):(isset($this->post['qtypeId'])?intval($this->post['qtypeId']):0);
		// 获取不到qtypeId 跳转
		$allQtype = $_ENV['qtype']->GetAllQType(1,"",0);
		
		if(!isset($allQtype[$qtypeId]))
		{
			header("Location: http://sc.5173.com/index.php?question/ask_skip.html");
		}
		$subList = $_ENV['qtype']->GetSubList($qtypeId);
		if(!empty($subList)) // 没有下级目录跳到 对用问题提问页面
		{
			header("Location: http://sc.5173.com/index.php?question/subList/complain/{$qtypeId}.html");
		}
		//未登陆跳转地址
		$login_url = "http://".config::FRONT_LOGIN_DOMAIN."/?returnUrl=".urlencode(curPageURL());
		$date = date("Y-m-d",time());

		$startDate = date("Y-m-01",strtotime("-1 month",time()));
		$questionsToday = $_ENV['qtype']->getQuestionsNum('complain',$qtypeId,$startDate,$date);
		if(empty($questionsToday))
		{
			$questionsToday = 1;
		}
		else
		{
			$data = array_shift($questionsToday);
			$questionsToday = $data['questions']+1;
		}

		$qtypeName = $allQtype[$qtypeId];
		$qtypeName['complain'] = unserialize($qtypeName['complain']);
		$qtypeName['trading'] = unserialize($qtypeName['trading']);
		$url = '';			
		if($qtypeName['pid']>0)
		{
			$url = '<a href="http://sc.5173.com/index.php?question/subList/complain/'.$qtypeName['pid'].'.html">选择'.$allQtype[$qtypeName['pid']]['name'].'类'.$question_type_list[$question_type].'</a>&nbsp;&nbsp;&gt;&nbsp;&nbsp';
		}
		 		 
		$side = array('seller'=>'卖家','buyer'=>'买家');
		if(in_array($qtypeName['name'],array('代练服务')))
		{
			$side['seller'] = "工作室";
			$side['buyer'] = "玩家";
		}
		$order_id  = $good_id = $buyer_order_num = $order_num = $buyer_good = $seller_good = '';
		$ts_type_id = isset($this->post['ts_type_id'])?trim($this->post['ts_type_id']):25;
		$sellShow = $buyerShow = false; // 买家、卖家订单默认不显示

		$buyChecked = $sellChecked = '';
		$loginName = $this->ask_front_name;
		if($ts_type_id == 12) // 买家投诉
		{
			$buyChecked = 'checked="checked"';
		}
		elseif($ts_type_id == 13) // 卖家投诉
		{
			$sellChecked = 'checked="checked"';
		}

		if($this->ask_front_name != '游客')
		{			
			if($ts_type_id == 12) // 买家投诉
			{
				$order_id = $buyer_order_num = isset($this->post['buyer_order_num']) && $this->post['buyer_order_num'] != "订单编号"
						&& $this->post['buyer_order_num'] != ''?htmlspecialchars(trim($this->post['buyer_order_num'])):'';
				$good_id = $buyer_good = isset($this->post['buyer_commodity_num']) && $this->post['buyer_commodity_num'] != "商品编号"
						&& $this->post['buyer_commodity_num'] != ''?htmlspecialchars(trim($this->post['buyer_commodity_num'])):'';
				$buyerShow = true; // 显示买家订单
				$buyChecked = 'checked="checked"';
			}
			elseif($ts_type_id == 13) // 卖家投诉
			{
				$order_id = $order_num = isset($this->post['order_num']) && $this->post['order_num'] != "订单编号"
						&& $this->post['order_num'] != ''?htmlspecialchars(trim($this->post['order_num'])):'';
				$good_id = $seller_good = isset($this->post['commodity_num']) && $this->post['commodity_num'] != "商品编号"
						&& $this->post['commodity_num'] != ''?htmlspecialchars(trim($this->post['commodity_num'])):'';
				$sellShow = true; // 显示卖家订单
				$sellChecked = 'checked="checked"';
			}
			$contact = $this->cache->get(md5('SJ'.$this->ask_front_id));
			if(false === $contact)
			{
				$contact = get_mobile($this->ask_front_id);
				if(!empty($contact))
				{
					$this->cache->set(md5('SJ'.$this->ask_front_id),$contact,1800);//缓存30分钟
				}
			}
			if(!empty($contact)) $en_contact = substr_replace($contact,'****',3,4);

		}
		$J_ques_t = isset($this->post['title'])?htmlspecialchars(trim($this->post['title'])):''; // 标题
		$add_text = isset($this->post['description']) && $this->post['description'] != "请详细描述您的诉求内容以及填写您的订单号"
				? htmlspecialchars(trim($this->post['description'])):''; 	// 内容
		$resolve   = isset($this->post['resolve']) && $this->post['resolve'] != "请告诉我们您的解决方案，以便我们参考，谢谢 ，为确保您的个人信息安全 ，请勿在问题内容中填写帐号、密码、联系方式等信息" ? trim($this->post['resolve']) : '';
		$J_code = isset($this->post['J_code'])?strtolower(htmlspecialchars($this->post['J_code'])):'';
		if($this->ask_front_name == '游客')
		{
			$author = isset($this->post['author']) && $this->post['author'] != '请输入5173用户名' ? trim($this->post['author']) : '';
		}
		else
		{
			$author = $this->ask_front_name;
		}
		if( isset($this->post['contact']) )
		{
			$comment['contact'] = $this->post['contact'];
		}
		else
		{
			$comment['contact']['mobile'] = isset($en_contact)?$en_contact:'';
		}
		$flag = 0;
		if(isset($this->post['act']))
		{
			//提问数限制
			$limit_question_num = intval($this->setting['limit_question_num']);
			if(!empty($limit_question_num))
			{
				$num_ip = $_ENV['question']->get_complain_num_by_ip(getip());
				if($num_ip >= $limit_question_num)
				{
					@include template('complain');
					echo "<script>alert('您的操作太频繁啦，让服务器休息一下，稍后再进行投诉！');</script>";
					exit;
				}
			}
			//IP黑名单
			$BlackList = explode("|",$this->setting['IpBlackList']);
			if(in_array(getip(),$BlackList))
			{
				$comment['contact'] = $this->post['contact'];
				@include template('complain');
				echo "<script>alert('您的操作太频繁啦，让服务器休息一下，稍后再投诉！');</script>";
				exit;
			}
			if( isset($this->post['contact']) )
			{
				$comment['contact'] = $this->post['contact'];

				if($comment['contact']['mobile'] != "")
				{
					if(isset($en_contact) && $comment['contact']['mobile'] == $en_contact)
					{
						$comment['contact']['mobile'] = $contact;
					}
					if(!checkmobile($comment['contact']['mobile']))
					{
						$errorMsg['mobile'] = '手机号';
						unset($comment['contact']['mobile']);
					}
					else
					{
						$flag++;
					}
				}
				else
				{
					$errorMsg['mobile'] = '手机号';
					unset($comment['contact']['mobile']);
				}
				if($comment['contact']['qq'] != "")
				{
					if (!isQQ($comment['contact']['qq']))
					{
						//$errorMsg['qq'] = 'QQ号';
						unset($comment['contact']['qq']);
					}
					else
					{
						//$flag++;
					}
				}
				else
				{
					//$errorMsg['qq'] = 'QQ号';
					unset($comment['contact']['qq']);
				}
				if($comment['contact']['weixin'] != '')
				{
					if(strlen($comment['contact']['weixin']) > 20 || strlen($comment['contact']['weixin']) < 4 || trim($comment['contact']['weixin'])=="微信号")
					{
						//$errorMsg['weixin'] = '微信号';
						unset($comment['contact']['weixin']);
					}
					else
					{
						//$flag++;
					}
				}
				else
				{
					//$errorMsg['weixin'] = '微信号';
					unset($comment['contact']['weixin']);
				}
			}

			if($flag==0 && count($errorMsg)>0)
			{
				$error = implode("、",$errorMsg)."未填写或格式不正确";
				$comment['contact'] = $this->post['contact'];
				@include template('complain');
				echo "<script>alert('".$error."');</script>";
				exit;
			}
			if($add_text == ''){
				$comment['contact'] = $this->post['contact'];
				@include template('complain');
				echo "<script>alert('投诉内容不能为空。');</script>";
				exit;
			}else if(mb_strlen($add_text,'UTF-8') > 500 || mb_strlen($add_text,'UTF-8')< 5){
				$comment['contact'] = $this->post['contact'];
				@include template('complain');
				echo "<script>alert('投诉内容请保持在5-500字内。');</script>";
				exit;
			}
			if($this->ask_front_name == '游客')
			{
				if($author=="" || mb_strlen($author,'UTF-8')>20)
				{
					@include template('complain');
					echo "<script>alert('请输正确格式的5173登陆用户名');</script>";
					exit;
				}
			}
			if($this->ask_front_name != '游客')
			{//登录提问
    			$GagLog = $_ENV['user']->getGag($this->ask_front_name);				
    			if(count($GagLog)>0)
				{
					$comment['contact'] = $this->post['contact'];
					@include template('complain');
					echo "<script>alert('很抱歉，您的帐号已被管理员禁言处理，请您自觉遵守5173言论规则。');</script>";
					exit;
				}
			}

			if($flag == 0)
			{
				$comment['contact'] = $this->post['contact'];
				@include template('complain');
				echo "<script>alert('对不起，您至少要输入一个有效的联系方式。');</script>";
				exit;
			}
			if($this->ask_front_name == '游客'){//未登录提问（快捷提问）
				$comment['contact'] = $this->post['contact'];
				if($J_code==""||$J_code != $_SESSION['code']){
					@include template('complain');
					echo "<script>alert('验证码不正确！');</script>";
					exit;
				}
			}

			if($order_id)
			{
				if(!$this->onexistOrderId($order_id,$qtypeId))
				{
					$comment['contact'] = $this->post['contact'];
					@include template('complain');
					echo "<script>alert('订单编号不存在');</script>";
					exit;
				}
			}
			if($good_id)
			{
				if(!$this->onexistOrderId($good_id,$qtypeId))
				{
					$comment['contact'] = $this->post['contact'];
					@include template('complain');
					echo "<script>alert('商品编号不存在');</script>";
					exit;
				}
			}
			/*
			if(!empty($_FILES['uploadfile']['name']))
			{
				@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
				$FastDFSClient = new FastDFSClient();
				$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
				$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
				$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
				$FastDFSInfo = $FastDFSClient->upload("sk");
				if($FastDFSInfo == -1)
				{
					$comment['contact'] = $this->post['contact'];
					@include template('complain');
					echo "<script>alert('".$FastDFSClient->getErrorMsg()."');</script>";
					exit;
				}
			}
			$attach = $FastDFSInfo != -1?$FastDFSInfo:'';
			*/
			{
				$img_path = $this->post['imgpath'];
				$img_path = stripcslashes($img_path);
				$img_path = str_replace('"small_pic"',',"small_pic"',$img_path);
				$img_path = str_replace('"big_pic"',',"big_pic"',$img_path);
				$p1 = strpos($img_path,"big_pic");
				$path = substr($img_path,$p1+10,strlen($img_path)-$p1-10-2);
				$path = str_replace('\/','/',$path);
				$attach = trim($path);
			}

			if(isset($en_contact) && $comment['contact']['mobile'] == $en_contact)
			{
				$comment['contact']['mobile'] = $contact;//还原联系方式
			}
			$complainInfo = $_ENV['qtype'] -> GetQType($qtypeId,'complain_type_id');
			$complain_type_id = intval($complainInfo['complain_type_id']);
			$author_id = $this->ask_front_id;
			$title = strip_tags($J_ques_t);
			
			$trimDescription = preg_replace('/\s+/', '', $add_text);
			$description = $this->keyWordCheck($trimDescription);
			
			$contact = serialize($comment);
			$jid = $complain_type_id;
			$photo = empty($attach)?'':$attach;
			$time = time();
			$ip = getip();
			$resolve_photo =  "";
			// 新版本去掉下面字段
			$real_name = '';
			$trimResolve = preg_replace('/\s+/', '', $resolve);
			$resolve = $this->keyWordCheck($trimResolve);

			if(md5(trim(strip_tags($description)))==$_COOKIE['last_complain'])
			{
				@include template('complain');
				echo "<script>alert('亲，问题提交一次就OK，不用重复提交哦！');</script>";
				exit;				
			}
			$BrowerInfo = userBrowerInfo();
			
			$ComplainArr = array('author'=>$author,'author_id'=>$author_id,'title'=>$description,
					'description'=>$description,'contact'=>$contact,'qtype'=>$qtypeId,
					'photo'=>$photo,'time'=>$time,'ip'=>$ip,'real_name'=>$real_name,
					'resolve_photo'=>$resolve_photo,'resolve'=>$resolve,'jid'=>$jid,'order_id'=>$order_id,
					'good_id'=>$good_id,'sid'=>$ts_type_id,'comment'=>serialize($BrowerInfo));
						
			$question_id = $_ENV['complain']->insert($ComplainArr);
			
			 if($question_id > 0)
			 {
				setcookie('last_complain',md5(trim(strip_tags($description))),time()+3600);
				if($this->ask_front_name == '游客') {
					get_que_id('ts',$question_id); //咨询id写入cookie,存一个月
				}				
				$date = date("Y-m-d");
				$_ENV['question']->modifyUserQtypeNum($date,$qtypeId,'complain',1);
			}
			header("Location: ".url('question/complain_success/'.$question_id.'/'.$time,true));
		}
		$_ENV['question']->PageView(5,getip());
		include template('complain');

	}
	//建议
	function onsuggest()
	{
    	$question_type = "suggest";
		$question_type_list = $this->ask_config->getQuestionType();
		$title = "服务中心-我要".$question_type_list[$question_type];
		$all_num = $_ENV['question']->total_question();
		$qtypeId = isset($this->get[2]) ? intval($this->get[2]):(isset($this->post['qtypeId'])?intval($this->post['qtypeId']):0);
		$loginName = $this->ask_front_name;

		$allQtype = $_ENV['qtype']->GetAllQType(1,"",0);
		if(!isset($allQtype[$qtypeId]))
		{
			header("Location: http://sc.5173.com/index.php?question/ask_skip.html");
		}
		$subList = $_ENV['qtype']->GetSubList($qtypeId);
		if(!empty($subList)) // 没有下级目录跳到 对用问题提问页面
		{
			header("Location: http://sc.5173.com/index.php?question/subList/suggest/{$qtypeId}.html");
		}
		$qtypeName = $allQtype[$qtypeId];
		
		$operatorInfo = ''; // 获取我的专属客服
		if($this->ask_front_name == '游客')
		{
			$selfAuthor_where = ''; // 获取专属客服条件
		}
		else
		{
			$t_cid = $_ENV['question']->getType(3);
			$operatorInfo = $_ENV['operator']->getMySelfAuthor($this->ask_front_name);
			$selfAuthor_where = $_ENV['question']->front_selfAuthor_where($operatorInfo['login_name'],'',1,$t_cid);
		}
		if($selfAuthor_where)
		{
			$selfAuthorNum = $_ENV['question']->front_mySelfAuthorNum($selfAuthor_where);
		}
		else
		{
			$selfAuthorNum = 0;
		}
		
		$url = '';
		if($qtypeName['pid']>0)
		{
			$url = '<a href="http://sc.5173.com/index.php?question/subList/suggest/'.$qtypeName['pid'].'.html">选择'.$allQtype[$qtypeName['pid']]['name'].'类'.$question_type_list[$question_type].'</a>&nbsp;&nbsp;&gt;&nbsp;&nbsp';
		}

		//未登陆跳转地址
		$login_url = "http://".config::FRONT_LOGIN_DOMAIN."/?returnUrl=".urlencode(curPageURL());
		$display_yzm = false;
		if($this->ask_front_name != '游客')
		{
			$contact = $this->cache->get(md5('SJ'.$this->ask_front_id));
			if(false === $contact)
			{
				$contact = get_mobile($this->ask_front_id);
				if(!empty($contact)) $this->cache->set(md5('SJ'.$this->ask_front_id),$contact,1800);//缓存30分钟
			}
			 if(!empty($contact)) $en_contact = substr_replace($contact,'****',3,4);

		}
		
		$suggest_title = isset($this->post['title']) ? htmlspecialchars(trim($this->post['title'])):'';

		$description = isset($this->post['description']) && $this->post['description'] != "我们非常重视您的".$question_type_list[$question_type]."，请在这里告诉我们" ? htmlspecialchars(trim($this->post['description'])):'';
		$contact_num = isset($this->post['contact_num']) ? htmlspecialchars($this->post['contact_num']):(isset($en_contact)?$en_contact:'');
		$J_code = isset($this->post['J_code'])?strtolower(htmlspecialchars($this->post['J_code'])):'';
		if($this->ask_front_name == '游客')
		{
			$author = isset($this->post['author']) && $this->post['author'] != '请输入5173用户名' ? trim($this->post['author']) : '';
			$author_id = '';
		}
		else
		{
			$author_id = $this->ask_front_id;
			$author = $this->ask_front_name;
		}
		$t_yzm = tcookie('yzm');
		if(empty($t_yzm)){
			tcookie('yzm',time(),1800);//存放半个小时
		}else{
			$over_time = time() - $t_yzm; //距离现在的秒数
			if($over_time < 1800){
				$display_yzm = true;
			}else{
				tcookie('yzm','',time()-3600);//删除
			}
		}
		if($this->ask_front_name != '游客') $display_yzm = false;//登陆用户不显示验证码
		if( isset($this->post['contact']) )
		{
			$comment['contact'] = $this->post['contact'];
		}
		else
		{
			$comment['contact']['mobile'] = isset($en_contact)?$en_contact:'';
		}

		$flag = 0;
		if(isset($this->post['act']))
		{

		 if( isset($this->post['contact']) )
			{
				$comment['contact'] = $this->post['contact'];
				if($comment['contact']['mobile'] != "")
				{
					if(isset($en_contact) && $comment['contact']['mobile'] == $en_contact)
					{
						$comment['contact']['mobile'] = $contact;
					}
					if(!checkmobile($comment['contact']['mobile']))
					{
						$errorMsg['mobile'] = '手机号';
						unset($comment['contact']['mobile']);
					}
					else
					{
						$flag++;
					}
				}
				else
				{
					$errorMsg['mobile'] = '手机号';
					unset($comment['contact']['mobile']);
				}
				if($comment['contact']['qq'] != "")
				{
					if (!isQQ($comment['contact']['qq']))
					{
						//$errorMsg['qq'] = 'QQ号';
						unset($comment['contact']['qq']);
					}
					else
					{
						//$flag++;
					}
				}
				else
				{
					//$errorMsg['qq'] = 'QQ号';
					unset($comment['contact']['qq']);
				}
				if($comment['contact']['weixin'] != '')
				{
					if(strlen($comment['contact']['weixin']) > 20 || strlen($comment['contact']['weixin']) <4  || trim($comment['contact']['weixin'])=="微信号")
					{
						//$errorMsg['weixin'] = '微信号';
						unset($comment['contact']['weixin']);
					}
					else
					{
						//$flag++;
					}
				}
				else
				{
					//$errorMsg['weixin'] = '微信号';
					unset($comment['contact']['weixin']);
				}
			}

			if($flag==0 && count($errorMsg)>0)
			{
				$error = implode("、",$errorMsg)."未填写或格式不正确";
				$comment['contact'] = $this->post['contact'];
				@include template('suggest');
				echo "<script>alert('".$error."');</script>";
				exit;
			}
			if($description == '')
			{
				$comment['contact'] = $this->post['contact'];
				@include template('suggest');
				echo "<script>alert('建议内容不能为空。');</script>";
				exit;
			}elseif(mb_strlen($description,'UTF-8') > 500 || mb_strlen($description,'UTF-8') < 5){
				$comment['contact'] = $this->post['contact'];
				@include template('suggest');
				echo "<script>alert('建议内容请保持在5-500个字内。');</script>";
				exit;
			}

			if($this->ask_front_name == '游客') // 未登录提问（快捷提问）
			{
				if($author=="" || mb_strlen($author,'UTF-8')>20)
				{
					@include template('suggest');
					echo "<script>alert('请输正确格式的5173登陆用户名');</script>";
					exit;
				}
				if($J_code=="" || $J_code != $_SESSION['code'])
				{
					$comment['contact'] = $this->post['contact'];
					@include template('suggest');
					echo "<script>alert('验证码不正确！');</script>";
					exit;
				}
			}
			//提问数限制
			$limit_question_num = intval($this->setting['limit_question_num']);
			if(!empty($limit_question_num)){
				$comment['contact'] = $this->post['contact'];
				$num_ip = $_ENV['question']->get_num_by_ip(getip());
				if($num_ip >= $limit_question_num){
					@include template('suggest');
					echo "<script>alert('您的操作太频繁啦，让服务器休息一下，稍后再进行建议！');</script>";
					exit;
				}
			}
    		//IP黑名单
			$BlackList = explode("|",$this->setting['IpBlackList']);
			if(in_array(getip(),$BlackList))
			{
				$comment['contact'] = $this->post['contact'];
				@include template('suggest');
				echo "<script>alert('您的操作太频繁啦，让服务器休息一下，稍后再建议！');</script>";
				exit;
			}
			
			if($this->ask_front_name != '游客'){//登录提问
    			$GagLog = $_ENV['user']->getGag($this->ask_front_name);				
    			if(count($GagLog)>0)
				{
					$comment['contact'] = $this->post['contact'];
					@include template('suggest');
					echo "<script>alert('很抱歉，您的帐号已被管理员禁言处理，请您自觉遵守5173言论规则。');</script>";
					exit;
				}
			}
			$description = cutstr(strip_tags($description),500,'');
			if(md5(trim(strip_tags($description)))==$_COOKIE['last_suggest'])
			{
				@include template('suggest');
				echo "<script>alert('亲，问题提交一次就OK，不用重复提交哦！');</script>";
				exit;								
			}
			/*
			if(!empty($_FILES['uploadfile']['name'])){
				@require TIPASK_ROOT . '/api/FastDFSClient/FastDFSClient.php';
				$FastDFSClient = new FastDFSClient();
				$FastDFSClient->maxSize  = 4194304 ;// 设置附件上传大小 默认为4M
				$FastDFSClient->allowExts  = array('gif','jpg','jpeg','bmp','png');// 设置附件上传类型
				$FastDFSClient->savePath =  TIPASK_ROOT .'/data/attach/'. gmdate('ym', $this->time) . '/';// 设置附件上传目录
				$FastDFSInfo = $FastDFSClient->upload("sk");
				if($FastDFSInfo == -1){
				$comment['contact'] = $this->post['contact'];
					@include template('suggest');
					echo "<script>alert('".$FastDFSClient->getErrorMsg()."');</script>";
					exit;
				}
			}
			else
			{
				$FastDFSInfo = -1;
			}
			$attach = $FastDFSInfo != -1?$FastDFSInfo:'';
			*/
			{
				$img_path = $this->post['imgpath'];
				$img_path = stripcslashes($img_path);
				$img_path = str_replace('"small_pic"',',"small_pic"',$img_path);
				$img_path = str_replace('"big_pic"',',"big_pic"',$img_path);
				$p1 = strpos($img_path,"big_pic");
				$path = substr($img_path,$p1+10,strlen($img_path)-$p1-10-2);
				$path = str_replace('\/','/',$path);
				$attach = trim($path);
			}

			if(isset($en_contact) && $comment['contact']['mobile'] == $en_contact)
			{
				$comment['contact']['mobile'] = $contact;
			}

			$cid = $_ENV['question']->getType(2); //建议分类id
			$cid = !empty($cid)?intval($cid):0;
    		$cid1Info = $_ENV['category']->getByQType($qtypeId,$cid); //qtype对应分类id
			$cid1 = intval($cid1Info['id']);
			$time = time();
			$trimDescription = preg_replace('/\s+/', '', $description);
			$description = $this->keyWordCheck($trimDescription);
			$BrowerInfo = userBrowerInfo();
			$comment['OS'] = $BrowerInfo['OS'];
			$comment['Browser'] = $BrowerInfo['Browser'];			
			$questionInfo = array(
								"qtype"=>$qtypeId,
								"author"=>$author,
								"author_id"=>$author_id,
								"title"=>$suggest_title,
								"description"=>$description,
								"attach"=>$attach,
								"time"=>$time,
								"ip"=>getip(),
								"cid"=>$cid,
								"cid1"=>$cid1,
								"comment"=>serialize($comment)
			);
			$question_id = $_ENV['question']->insertQuestion($questionInfo);
			//更新Solr服务器
			$q_search = array();
			if($question_id > 0)
			 {
			 	
				setcookie('last_suggest',md5(trim(strip_tags($description))),time()+3600);
				if($this->ask_front_name == '游客')
				{
					get_que_id('jy',$question_id); //建议id写入cookie
				}

				$date = date("Y-m-d");
				$_ENV['question']->modifyUserQtypeNum($date,$qtypeId,'suggest',1);

				$login_name = trim($this->post['login_name']);
				if(!empty($login_name))
				{
					if($this->setting['selfServiceFirst']==1)
					{
						$Apply = $_ENV['question']->ApplyToOperator($question_id,$login_name);
					}
				}
				
				$q_search['id'] = $question_id;
				$q_search['title'] = $description;
				$q_search['description'] = $description;
				$q_search['tag'] = json_encode(array(),true);
				$q_search['time'] = $time;
				$q_search['atime'] = 0;
				try{
					$this->set_search($q_search);
				}catch(Exception $e){
					send_AIC('http://sc.5173.com/index.php?question/suggest.html','搜索服务器异常',1,'搜索接口');
				}

			}
			header("Location: ".url('question/suggest_success/'.$question_id.'/'.$time,true));
		}
				$telDisplay = $this->setting['telDisplay'];
		$xnDisplay = $this->setting['xnDisplay'];
		$qqDisplay = $this->setting['qqDisplay'];
		$_ENV['question']->PageView(1,getip());
		@include template('suggest');
	}

	//订单编号显示控制
	function onajaxpt_order(){
		if(isset($this->post['ts_id']) && isset($this->post['jy_id'])){
			if($this->post['ts_id'] == 12){//买家投诉
				if($this->post['jy_name'] == '担保交易' || $this->post['jy_name'] == '寄售交易'
					|| $this->post['jy_name'] == '帐号交易' || $this->post['jy_name'] == '配送业务'
						 || $this->post['jy_name'] == '点卡交易'){
					exit('1');
				}
			}
			if($this->post['ts_id'] == 13){//卖家投诉
				if($this->post['jy_name'] == '担保交易' || $this->post['jy_name'] == '寄售交易'
						|| $this->post['jy_name'] == '帐号交易' || $this->post['jy_name'] == '配送业务'
						|| $this->post['jy_name'] == '点卡交易'){
					exit('2');
				}
			}
			if($this->post['ts_id'] == 25){//其它投诉
				if($this->post['jy_name'] == '担保交易' || $this->post['jy_name'] == '寄售交易'
						|| $this->post['jy_name'] == '帐号交易' || $this->post['jy_name'] == '点卡交易'){
					exit('3');
				}
			}
			if($this->post['jy_id'] == 124) exit('4');//代练直接跳转
		}
		exit('0');
	}
	//订单
	function onajaxorders()
	{		
		$page = intval($this->post['page']); // 当前页
		$page = $page >0 ? $page : 1;
		$qtypeInfo = $_ENV['qtype']->GetQType(intval($this->post['jy']));
		$qtypeInfo['trading'] = unserialize($qtypeInfo['trading']);
		if($this->post['type'] == 1 || $this->post['type'] == 2)
		{//我购买的商品
			$str .= '<ul class="order_list">';
			if($this->post['type'] == 1)
			{
				$url = $qtypeInfo['trading']['buyerOrderUrl'];				
			}
			else
			{
				$url = $qtypeInfo['trading']['sellerOrderUrl'];
			}
			$url .= "&uid=".$this->ask_front_id."&ps=5&p=".$page;
			$this->post['start'] != '' && $url .= '&mindate='.$this->post['start'];
			$this->post['end'] != '' && $url .= '&maxdate='.$this->post['end'];
			$url .= '&ts='.$qtypeInfo['trading']['ServiceType'];
			$this->post['dd'] != '' && $url .= '&oc='.$this->post['dd'];
						
			$rs = get_url_contents($url);
			$result = json_decode($rs,true);
			if(!empty($result['OrderList']))
			{
				$questr = ajax_ts_page($result['TotalCount'],5, $page, $this->post['type']);
				foreach ($result['OrderList'] as $OrderList)
				{
					$OrderPayStatusValue = order_status($OrderList['OrderPayStatusValue']);
					$c_hold = $OrderPayStatusValue == '已撤单'?'c_999':'c_f60';
					$basicType = basicType($OrderList['BasicType']);
					$is_dk = $basicType == '点卡'?1:0;//1为点卡订单
					$EnsureType = isset($OrderList['EnsureType']) && $OrderList['EnsureType'] == 1?1:0;//1为购买了售后保障服务
					$str .= '<li><div class="order_numbg" onclick="show_fw(this,'.$EnsureType.','.$is_dk.',\''.$OrderList['Id'].'\')">
							<input type="radio" name="order_btn"/>
							 订单编号：<span class="ddbh">'.$OrderList['Id'].'</span>';
					$str .= '创建时间：<span>'.$OrderList['CreatedDate'].'</span><ins class="'.$c_hold.'">'.$OrderPayStatusValue.'</ins></div>';
					$str .= '<dl><dt>['.$basicType.']</dt>';
					$str .= '<dd class="con_left">'.$OrderList['Name'];
					$str .= '<span>游戏/区/服：'.$OrderList['GameName'].'/'.$OrderList['AreaName'].'/'.$OrderList['ServerName'].'</span></dd>';
					$str .= '<dd class="s_left"><ins>'.$OrderList['RawSum'].'</ins></dd></dl>';
					$zxsq_url = "http://dkjy.5173.com/Order/Gold/GoldOrderDetails.aspx?orderId=".$OrderList['Id']."&alert=&complain=1&flag=";
					$str .= '<div class="tips_zc" style="display:none;">
							<s class="ico_warning_1"></s>
							<div>
							很抱歉，您想要投诉的商品正在仲裁中，无法提交投诉。<br/><br/>
							<span>5173客服将尽快为您处理仲裁申请，</span>&nbsp;<a href="'.$zxsq_url.'" class="c_999">查看仲裁处理进度</a>
							</div>
							</div>';
					$dd_type = substr($OrderList['Id'],0,2);
					if($dd_type == 'JS')
					{//寄售
						$shbz_url = "http://consignment.5173.com/auction/buy/myorderdetail2.aspx?orderid=".$OrderList['Id']."&tradingservicetype=consignment";
					}
					elseif($dd_type == 'DB')
					{
						if(tradingType($OrderList['ServiceType']) == '担保交易' && basicType($OrderList['BasicType']) == 'ID交易')
						{
							$shbz_url = "http://gameid.5173.com/myinfo/orderdetail2.aspx?orderid=".$OrderList['Id']."&tradingservicetype=escort";
						}
						else
						{
							$shbz_url = "http://escort.5173.com/auction/buy/myorderdetail2.aspx?orderid=".$OrderList['Id']."&tradingservicetype=escort";
						}
					}
					$str .= '<div class="tips_sh" style="display:none;">
						<s class="deng"></s>
						<div>
						友情提示：该商品如果收货后有问题，请直接申请售后保障处理。 <a href="http://aid.5173.com/tese/safe/856.html" class="c_999" target="_blank">什么是售后保障？</a><br/><br/>
						<a class="btnlink_w_small" href="'.$shbz_url.'" target="_blank"><span>申请售后保障</span></a>&nbsp;&nbsp;
						</div>
						</div>';
					$str .= '</li>';
				}
				$str .='</ul>';
				$str .='<div class="pagination">'.$questr.'</div>';
				$str .='<a href="#" class="btnlink_g_small unhover c9 J_orderList_ok"><span>确&nbsp;&nbsp;定</span></a>
						<a href="#" class="btnlink_g_small J_orderList_close"><span>取&nbsp;&nbsp;消</span></a>';
			}else{
				$str .= '<!--找不到记录-->
						<div class="notfind_order">
							<div class="side_icon">
								<s class="ico_info_5"></s>
							</div>

							<div class="right_main">
								<h4>很抱歉，找不到订单记录。</h4>
								<p class="c_999">您可以在扩大时间范围，或确认交易类型是否正确。</p>
								<p class="mt10"><a href="#" class="btnlink_b_small J_orderList_ok"><span>返&nbsp;&nbsp;回</span></a></p>
							</div>
						</div>';
			}
		}
		elseif($this->post['type'] == 3)
		{//我的发布单
			$str .= '<ul class="order_list sp_list">';
			$url = $qtypeInfo['trading']['sellingOrderUrl'];
			$url .= "?uid=".$this->ask_front_id."&ps=5&p=".$page;
			$url .= '&ts='.$qtypeInfo['trading']['ServiceType'];
			$this->post['start'] != '' && $url .= '&mindate='.$this->post['start'];
			$this->post['end'] != '' && $url .= '&maxdate='.$this->post['end'];
			$rs = get_url_contents($url);
			$result = json_decode($rs,true);
			if(!empty($result['BizofferList'])){
				$questr = ajax_ts_page($result['TotalCount'],5, $page, $this->post['type']);
				foreach ($result['BizofferList'] as $BizofferList){
					$str .= '<li><div class="order_numbg"><input type="radio" name="order_btn" />商品编号：<span class="spbh">'.$BizofferList['Id'].'</span>';
					$str .= '上架时间：<span>'.$BizofferList['PublishDate'].'</span></div>';
					$str .= '<dl><dt>['.basicType($BizofferList['BasicType']).']</dt>';
					$str .= '<dd class="con_left">'.$BizofferList['Name'];
					$str .= '<span>游戏/区/服：'.$BizofferList['GameName'].'/'.$BizofferList['AreaName'].'/'.$BizofferList['ServerName'].'</span></dd>';
					$str .= '<dd class="s_left"><ins>￥'.$BizofferList['Price'].'</ins></dd></dl>';
					$str .= '</li>';
				}
				$str .='</ul>';
				$str .='<div class="pagination">'.$questr.'</div>';
				$str .='<a href="#" class="btnlink_g_small unhover c9 J_orderList_ok"><span>确&nbsp;&nbsp;定</span></a>
						<a href="#" class="btnlink_g_small J_orderList_close"><span>取&nbsp;&nbsp;消</span></a>';
			}else{
				$str .= '<!--找不到记录-->
						<div class="notfind_order">
							<div class="side_icon">
								<s class="ico_info_5"></s>
							</div>

							<div class="right_main">
								<h4>很抱歉，没有出售中的商品。</h4>
								<p class="c_999">您可以在扩大时间范围，或确认交易类型是否正确。</p>
								<p class="mt10"><a href="#" class="btnlink_b_small J_orderList_ok"><span>返&nbsp;&nbsp;回</span></a></p>
							</div>

						</div>';
			}
		}
		echo $str;
	}
	//检查订单编号或商品编号是否存在
	function onexistOrderId($id,$qtype = 0)
	{
		$qtypeInfo = $_ENV['qtype']->GetQType($qtype);
		$qtypeInfo['trading'] = unserialize($qtypeInfo['trading']);
		$url = $qtypeInfo['trading']['checkOrderUrl']."$id";
		$rs = get_url_contents($url);
		return  $rs; //0无效，1有效
	}
	//获取点卡仲裁信息
	function onajaxzc(){
		if(isset($this->post['order_id'])){
			$key = 'asSDF,dj_s675G2323,sjds';
			$sign = md5($this->ask_front_id.trim($this->post['order_id']).$key);
			$zc_url = "http://join.5173.com/arbitration/Isarbitration.aspx?".
			"orderid=".$this->post['order_id']."&userid=".$this->ask_front_id."&sign=".$sign;
			$rs = topen($zc_url);
			echo $rs;
		}
	}
	// 删除memcache对应问题id
	function onajax_remove_id(){
		$id   = isset($this->post['id']) ? intval($this->post['id']) : 0;
		$type = isset($this->post['type']) ? intval($this->post['type']) : 0;
		if($id || $type){
			if($type == 1){ // 建议咨询，问题
				if( false !== $this->cache->get('fw'.$id) ) $this->cache->rm('fw'.$id);
			}else if($type == 2){ // 投诉问题
				if( false !== $this->cache->get('ts'.$id) ) $this->cache->rm('ts'.$id);
			}
		}
	}
	function onask_skip()
	{
		$title = "选择问题类型";
		$all_num = $_ENV['question']->total_question();
		include template('ask_skip');
	}
	/**
	 * @param int $BaoXianQtype 投诉保险订单 登陆后跳转是否弹窗
	 */
	function onask_run($BaoXianQtype='')
	{

		$all_num = $_ENV['question']->total_question();
		$question_type = isset($this->get[2]) ? trim($this->get[2]) : "ask";
		$question_type_list = $this->ask_config->getQuestionType();
		if(isset($question_type_list[$question_type]))
		{
			$type['name'] = $question_type_list[$question_type];
			$type['type'] = $question_type;
			$title = "服务中心-".$question_type_list[$question_type]."类型";
		}
		else
		{
			header("Location: http://sc.5173.com/index.php?question/ask_skip.html");
		}
		$qtype = $_ENV['qtype']->GetAllQType(1,'',1);
		
		// 投诉 类型的问题 查看是否有 订单直连接口,有直接弹窗,选订单
		if($question_type=='complain')
		{
			foreach($qtype as $key => $value)
			{
				if(isset($value['trading']))
				{
					$qtype[$key]['trading'] = unserialize($value['trading']);
				}
			}
		}
			
		if($type['type']=="complain")
		{
			$startTime = date('Y-m-d',mktime(0,0,0,date('m')-1,1));
		}
		else
		{
			$startTime = date('Y-m-d',mktime(0,0,0,date('m')-1,1));
		}
		
		$endTime   = date('Y-m-d',time());
		$allQuestionsToday = $_ENV['qtype']->getQuestionsNum($type['type'],0,$startTime,$endTime);
		if(!empty($allQuestionsToday))
		{
			foreach($qtype as $key => $value)
			{
				foreach($allQuestionsToday as $key2 => $value2)
				{
					if( $value['id'] == $value2['qtype'] && $type['type'] == $value2['question_type'])
					{
						$qtype[$key]['question_type'] = $value2['question_type'];
						$qtype[$key]['questions_today'] = $value2['questions'];
						if($type['type']=="complain")
						{
							$OrderCount = $_ENV['qtype']->GetOrderCount($value['id'],'order_count');
							$qtype[$key]['order_count'] = $OrderCount['order_count'];
							$total_order += $OrderCount['order_count'];
						}
						$qtype[$key]['qtype'] = $value2['qtype'];
						unset($allQuestionsToday[$key2]);
					}
				}
			}
		}
		if($type['type']=="complain")
		{
			foreach($qtype as $key => $value)
			{
				if($value['order_count']>0)
				{
					$orderCount = $value['questions_today']/$value['order_count'];
					if($orderCount>1 || $orderCount<1/100/100)
					{
						$qtype[$key]['questions_today'] = "0.01%";
						//$qtype[$key]['questions_today'] = $value['questions_today'];						
					}
					else
					{
						$qtype[$key]['questions_today'] = sprintf("%2.2f",$orderCount*100)."%";
					}				
				}
				else
				{
					$qtype[$key]['questions_today'] = "0.01%";
					//$qtype[$key]['questions_today'] = $value['questions_today'];
				}

			}
		}
		if($BaoXianQtype!='')
		{
			$AskRunBaoXianQtype = $BaoXianQtype;
		}
		$skipWindow = $this->ask_front_name=='游客'?0:1;
		include template('ask_run');
	}
	function onmy_dustbin()
	{
		$title = '我的垃圾箱';
		$all_num = $_ENV['question']->total_question();
		$ask_status = -1;
		$ask_time   = isset($this->post['ask_time']) ? intval($this->post['ask_time']):(isset($this->get[2])?intval($this->get[2]):0);

		$pagesize   = $this->setting['list_default'];
		@$quePage   = max(1, intval($this->get[4]));
		$MyServiceLog = $_ENV['question']->myServiceLog($this->ask_front_name,'dustbin',$ask_time,$ask_status,$quePage,$pagesize);

		include template('my_dustbin');
	}
	/**
	 *  获取8大类下级目录
	 * @param int $BaoXianQtype 投诉保险订单 登陆后跳转是否弹窗
	 */
	function onsubList($BaoXianQtype='')
	{
		$question_type = isset($this->get[2]) ? trim($this->get[2]) : '';
		$qtypeId = isset($this->get[3]) ? intval($this->get[3]):0;
		$question_type_list = $this->ask_config->getQuestionType();
		$all_num = $_ENV['question']->total_question();
		if(isset($question_type_list[$question_type]) && $qtypeId >= 0)
		{
			$subListTitle = $_ENV['qtype']->GetQType($qtypeId);
			$typeName = $question_type_list[$question_type];
			
			$title = '服务中心 - '.$subListTitle['name'].'类'.$typeName;
			if($qtypeId==0)
			{
				header("Location: http://sc.5173.com/index.php?question/ask_run/{$question_type}.html");			
			}
			$skipWindow = $this->ask_front_name=='游客'?0:1;
			$subList = $_ENV['qtype']->GetSubList($qtypeId);
			
			if($question_type=="complain")
			{
				$startTime = date('Y-m-d',mktime(0,0,0,date('m')-1,1));
			}
			else
			{
				$startTime = date('Y-m-d',mktime(0,0,0,date('m')-1,1));
			}
			
			$endTime   = date('Y-m-d',time());
			foreach($subList as $key => $value)
			{
				$Num = $_ENV['qtype']->getQuestionsNum($question_type,$value['id'],$startTime,$endTime);
				$subList[$key]['questions_today'] = isset($Num[0]['questions'])?$Num[0]['questions']:0;
				if($question_type=="complain")
				{	
					$OrderCount = $_ENV['qtype']->GetOrderCount($value['id'],'order_count');
					if($OrderCount['order_count']>0)
					{
						$OrderCount = $subList[$key]['questions_today']/$OrderCount['order_count'];
						if($OrderCount>1 || $OrderCount< 1/100/100)
						{
							$subList[$key]['questions_today'] = "0.01%";
							//$subList[$key]['questions_today'] = $subList[$key]['questions_today'];							
						}
						else
						{
							$subList[$key]['questions_today'] = sprintf("%2.2f",$OrderCount*100)."%";
						}
					}
					else
					{
						$subList[$key]['questions_today'] = "0.01%";
						//$subList[$key]['questions_today'] = $subList[$key]['questions_today'];
					}
				}
			}
			if(empty($subList)) // 没有下级目录跳到 对用问题提问页面
			{
				header("Location: http://sc.5173.com/?question/{$question_type}/{$qtypeId}.html");
			}
			else
			{
				// 投诉 类型的问题 查看是否有 订单直连接口,有直接弹窗,选订单
				if($question_type=='complain')
				{
					foreach($subList as $key => $value)
					{
						if(isset($value['trading']))
						{
							$subList[$key]['trading'] = unserialize($value['trading']);
						}
					}
				}
				if($BaoXianQtype!='')
				{
					$SubListBaoXianQtype = $BaoXianQtype;
				}
				include template('subList');
			}
		}
		else
		{
			header("Location: http://sc.5173.com/index.php?question/ask_skip.html");
		}
	}
	
	/**
	 * 我的服务记录 - 专属客服
	 */
	function onmy_selfAuthor()
	{
		$title = '专属客服';
		$all_num = $_ENV['question']->total_question();
		$loginName = $this->ask_front_name;
		$ask_time   = isset($this->post['ask_time']) ? intval($this->post['ask_time']):(isset($this->get[2])?intval($this->get[2]):0);
		$ask_status = isset($this->post['ask_status']) ? intval($this->post['ask_status']):(isset($this->get[3])?intval($this->get[3]):-1);

		$pagesize   = $this->setting['list_default'];
		@$quePage   = max(1, intval($this->get[4]));
		$MyServiceLog = $_ENV['question']->myServiceLog($loginName,'selfAuthor',$ask_time,$ask_status,$quePage,$pagesize);
		if($loginName !='游客')
		{
			$operatorInfo = $_ENV['operator']->getMySelfAuthor($loginName);
		}
		include template('my_selfAuthor');
	}
	/**
	 * 我的专属客服历史回复记录
	 */
	function onselfHistoryQuestion()
	{
		$title = "专属客服历史回复记录";
		$all_num = $_ENV['question']->total_question();
		$ask_status = isset($this->post['ask_status']) ? intval($this->post['ask_status']):(isset($this->get[3])?intval($this->get[3]):-2);
		$js_kf =  isset($this->post['js_kf']) && !empty($this->post['js_kf'])? trim($this->post['js_kf']):(isset($this->get[2])?trim($this->get[2]):'');
		$t_cid  = $_ENV['question']->getType(3);
		
		$selfAuthor_where = $_ENV['question']->front_selfAuthor_where(urldecode(trim($js_kf)),'',$ask_status,$t_cid);
		$selfAuthorNum = 0;
		$operatorInfo = $_ENV['operator']->getUser(urldecode(trim($js_kf)));
		
		if($selfAuthor_where)
		{
			$selfAuthorNum = $_ENV['question']->front_mySelfAuthorNum($selfAuthor_where);
			if(empty($selfAuthorNum))
			{
				$question_list = array();
			}
			else
			{
				$pagesize  = $this->setting['list_default'];
				@$quePage  = max(1, intval($this->get[4]));
				$totalPage = @ceil($selfAuthorNum / $pagesize);
				$quePage > $totalPage  && $quePage = $totalPage;
				$startindex = ($quePage - 1) * $pagesize;
				
				$question_list = $_ENV['question']->front_mySelfAuthorList($selfAuthor_where, $startindex, $pagesize);
				foreach($question_list as $k=>$v)
				{
					if($question_list[$k]['pid']>0)
					{
						$parentInfo = $_ENV['question']->Get($question_list[$k]['pid']);
						$qtype = $_ENV['qtype']->GetQType($parentInfo['qtype']);
						$categoryInfo = $_ENV['category']->get($parentInfo['cid'],'name');
						$question_list[$k]['type'] = $categoryInfo['name'];
					}
					else
					{
						$qtype = $_ENV['qtype']->GetQType($v['qtype']);
						$categoryInfo = $_ENV['category']->get($v['cid'],'name');
						$question_list[$k]['type'] = $categoryInfo['name'];
					}
				
					if(!empty($qtype))
					{
						$question_list[$k]['category'] = $qtype['name'];
					}
					else
					{
						$otherQtypeId = $this->db->fetch_first("SELECT id FROM ".DB_TABLEPRE."qtype WHERE name='其他问题'");
						$question_list[$k]['qtype'] = $otherQtypeId['id']; // 其他交易
						$question_list[$k]['category'] = '其他问题 ';
					}
					$question_list[$k]['Atime'] =  !empty($v['Atime']) ? date('Y-m-d',$v['Atime']): '-';
					$question_list[$k]['views'] = intval($v['views']);
					$question_list[$k]['QuestionUrl'] = $_ENV['question']->getQuestionLink($question_list[$k]['id'],"question");
				}
				$questr = front_page($selfAuthorNum, $pagesize, $quePage, "question/selfHistoryQuestion/$js_kf/$ask_status");
			}
		}
		include template('selfHistoryQuestion');
	}
	/**
	 * 选择我的专属客服
	 */
	function onchoiceMyselfAuthor()
	{
		$title = '专属客服经理选择';
		$all_num = $_ENV['question']->total_question();
		if($this->ask_front_name == '游客')
		{
			//未登陆跳转地址
			$login_url = "http://".config::FRONT_LOGIN_DOMAIN."/?returnUrl=".urlencode(curPageURL());
			header("Location:$login_url");
		}
		
		include template('choiceMyselfAuthor');
	}
	/**
	 * 配置8大类的 订单直连接口 	投诉直接提交接口就可以使用
	 * 后期会用到
	 * 获取保险订单列表
	 */
	function ongetDirectOrderUrl()
	{
		$qtypeId = intval($this->post['qtypeId']);
		$qtypeInfo = $_ENV['qtype']->GetQType($qtypeId);
				
		if(isset($qtypeInfo['id']))
		{
			if($this->ask_front_name=='游客')
			{
				if($qtypeInfo['pid']>0)
				{
					$qtype = $qtypeInfo['pid'];
				}
				else
				{
					$qtype = $qtypeInfo['id'];
				}
				if(strpos($_SERVER['HTTP_REFERER'],'ask_run')!==false)
				{
					//未登陆跳转地址
					$referer = SITE_URL."?question/askRunDirectPost/complain/{$qtypeInfo['id']}";
				}
				else if(strpos($_SERVER['HTTP_REFERER'],'subList')!==false)
				{
					$referer = SITE_URL."?question/subListDirectPost/complain/$qtype/{$qtypeInfo['id']}";
				}
				$url = "http://".config::FRONT_LOGIN_DOMAIN."/?returnUrl=$referer";
				$backData = array('msg'=>$url,'type'=>5); //  未登录跳转
			}
			else
			{
				$ps = 5; // 分页数
				$page = intval($this->post['page']); // 当前页
				$page = $page >0 ? $page : 1;
				$qtypeInfo['trading'] = unserialize($qtypeInfo['trading']);
					
				if(isset($qtypeInfo['trading']['directOrderUrl'])&&($qtypeInfo['trading']['directOrderUrl']!=''))
				{
					$mindate = $maxdate ='';
					$uid = trim($this->ask_front_id);
					$this->post['start'] != '' && $mindate = $this->post['start'];
					$this->post['end']   != '' && $maxdate = $this->post['end'];
						
					$url = "{$qtypeInfo['trading']['directOrderUrl']}&uid=$uid&mindate=$mindate&maxdate=$maxdate&ps=$ps&p=$page";
					$result = $this->getBaoXianData($url,$ps,$page,$qtypeId);
				
					$backData = array('msg'=>$result,'type'=>1);
				
				}
				else
				{
					$backData = array('msg'=>'订单直连接口不存在','type'=>2);
				}
			}
		}
		else
		{
			$backData = array('msg'=>'qtypeId不存在','type'=>3);
		}
		echo json_encode($backData);
	}
	
	/**
	 * 配置8大类的 订单直连接口 	投诉直接提交接口就可以使用
	 * 后期会用到
	 * 获取保险接口返回值
	 * @param  $url 接口地址
	 * @param  $ps 分页数
	 * @param  $page 当前页
	 * @param  $qtypeId
	 * @return string
	 */
	function getBaoXianData($url,$ps,$page,$qtypeId)
	{
		$rs = trim(get_url_contents($url));
		
		$str = '';
		$result = json_decode($rs,true);
		if(empty($result['OrderList']))
		{
			$str =  '<ul class="order_list">
					<div class="notfind_order">
					<div class="side_icon"><s class="ico_info_5"></s></div>
					<div class="right_main">
						<h4>很抱歉，找不到订单记录。</h4><p class="c_999">您可以在扩大时间范围，或确认交易类型是否正确。</p>
						<p class="mt10"><a href="javascript:void 0" onclick="J_close();" class="btnlink_b_small J_orderList_ok"><span>返&nbsp;&nbsp;回</span></a></p>
					</div>
				</div></ul>';
		}
		else
		{
				$questr = ajax_ts_page($result['TotalCount'],$ps,$page,$qtypeId);
				$str .= '<ul class="order_list">';
				foreach ($result['OrderList'] as $OrderList)
				{
					$OrderPayStatusValue = order_status($OrderList['OrderPayStatusValue']);
					$basicType = basicType($OrderList['BasicType']);
			   		 $str .= '<li>
								<div class="order_numbg">
										<input name="order_btn" type="radio" value="'.$OrderList['Id'].'"/> 订单编号：<span class="ddbh">'.$OrderList['Id'].'</span>创建时间：'.$OrderList['CreatedDate'].'</span>
										<ins class="c_f60">'.$OrderPayStatusValue.' </ins>
								</div>
								<dl>
									<dt>['.$basicType.']</dt>
									<dd class="con_left">'.$OrderList['Name'].'<span>游戏/区/服：'.$OrderList['GameName'].'/'.$OrderList['AreaName'].'/'.$OrderList['ServerName'].'</span></dd>
									<dd class="s_left"><ins>'.$OrderList['RawSum'].'</ins></dd>
								</dl>
							</li>';
				}		
				$str .= '</ul><div class="pagination">'.$questr.'</div>
					 <input type="hidden" value="'.$qtypeId.'" id="qtypeId"/>
					 <a href="javascript:void 0" onclick="submitForm();" class="btnlink_g_small unhover c9 J_orderList_ok"><span>确&nbsp;&nbsp;定</span></a>
					 <a href="javascript:void 0" onclick="J_close();" class="btnlink_g_small J_orderList_close"><span>取&nbsp;&nbsp;消</span></a>';
		}
		return $str;
	}
	/**
	 * 配置8大类的 订单直连接口 	投诉直接提交接口就可以使用
	 * 后期会用到
	 * 提交保险订单
	 */
	function onpostBaoXian()
	{
		$orderId = trim($this->post['orderId']);
		$qtypeId = intval($this->post['qtypeId']);
		$uid = trim($this->ask_front_id);
		if ($uid == '')
		{
			$backData = array('msg'=>'请您先登录在提交','type'=>5);
		}
		else
		{
			if($qtypeId>0)
			{
				$qtypeInfo = $_ENV['qtype']->GetQType($qtypeId);
				if(isset($qtypeInfo['id']))
				{
					$qtypeInfo['trading'] = unserialize($qtypeInfo['trading']);
					if(isset($qtypeInfo['trading']['directPostOrderUrl']) && $qtypeInfo['trading']['directPostOrderUrl']!='')
					{
						$baoXianLog = $_ENV['question']->getBaoXianOrder(array($this->ask_front_name,$orderId,$qtypeId));
						if(isset($baoXianLog['qtype']))
						{
							$backData = array('msg'=>'已经提交过该笔单子','type'=>6);
						} 
						else
						{
							$dataArr = array(
									'author'=>$this->ask_front_name,
									'orderid'=>$orderId,
									'qtype'=>$qtypeId,
									'time'=>$_SERVER['REQUEST_TIME']
							);
							
							$result = $_ENV['question']->insertBaoXianLog($dataArr);
							$url = $qtypeInfo['trading']['directPostOrderUrl']."&uid=$uid&id=$orderId&qtype=$qtypeId";
														
							$backData = array('msg'=>$url,'type'=>1);
						}
						
					}
					else
					{
						$backData = array('msg'=>'投诉直接提交接口不存在','type'=>2);
					}
				}
				else
				{
					$backData = array('msg'=>'非法参数qtypeId','type'=>3);
				}
			}
			else
			{
				$backData = array('msg'=>'qtypeId不存在','type'=>4);
			}
		}
		echo json_encode($backData);
	}
	/**
	 * 8大类 投诉订单 用户登陆后 直接弹窗
	 */
	function onaskRunDirectPost()
	{
		if(isset($this->get[3]))
		{
			$AskRunBaoXianQtype = intval($this->get[3]);
		}
		$this->onask_run($AskRunBaoXianQtype);
	}
	/**
	 * 获取8大类下级目录 投诉订单 用户登陆后 直接弹窗
	 */
	function onsubListDirectPost()
	{
		if(isset($this->get[4]))
		{
			$SubListBaoXianQtype = intval($this->get[4]);
		}
		$this->onsubList($SubListBaoXianQtype);
	}
	function ondata()
	{
		set_time_limit(0);
		$year = trim($this->get[2]);
		$data_arr = $_ENV['question']->getData($year);

		require TIPASK_ROOT . '/lib/Excel.php';
		$oExcel = new Excel();
		$FileName=$year."年每周数据";
		$oExcel->download($FileName);
		$sheetArr = array('total_count'=>'总咨询量','handle_count'=>'已处理','yes'=>'满意','no'=>'不满意','none'=>'未评价');
		foreach($sheetArr as $key => $value)
		{
			$oExcel->addSheet($value);
			$title = array('-1'=>$value);
			for($i=0;$i<=54;$i++)
			{
				$title[$i] = "第".$i."周";
			}
			$oExcel->addRows(array($title));
			ksort($data_arr);
			foreach($data_arr as $cid => $cid_info)
			{
				$d = array('-1'=>$cid_info['name']);
				for($i=0;$i<=54;$i++)
				{
					$d[$i] = $cid_info[$key][$i];
				}
				$oExcel->addRows(array($d));
				ksort($cid_info['sub']);
				foreach($cid_info['sub'] as $cid1 => $cid1_info)
				{
					$d = array('-1'=>$cid1_info['name']);
					for($i=0;$i<=54;$i++)
					{
						$d[$i] = $cid1_info[$key][$i];
					}
					$oExcel->addRows(array($d));
					ksort($cid1_info['sub']);
					foreach($cid1_info['sub'] as $cid2 => $cid2_info)
					{
						$d = array('-1'=>$cid2_info['name']);
						for($i=0;$i<=54;$i++)
						{
							$d[$i] = $cid2_info[$key][$i];
						}
						$oExcel->addRows(array($d));
						ksort($cid2_info['sub']);
						foreach($cid2_info['sub'] as $cid3 => $cid3_info)
						{
							$d = array('-1'=>$cid3_info['name']);
							for($i=0;$i<=54;$i++)
							{
								$d[$i] = $cid3_info[$key][$i];
							}
							$oExcel->addRows(array($d));
							
							ksort($cid3_info['sub']);
							foreach($cid3_info['sub'] as $cid4 => $cid4_info)
							{
								$d = array('-1'=>$cid4_info['name']);
								for($i=0;$i<=54;$i++)
								{
									$d[$i] = $cid4_info[$key][$i];
								}
								$oExcel->addRows(array($d));
							}
						}
					}	
				}												
			}
			$oExcel->closeSheet();				
		}
		$oExcel->close();
	}
	function ondatamonth()
	{
		set_time_limit(0);
		$year = trim($this->get[2]);
		$data_arr = $_ENV['question']->getDataMonth($year);
		require TIPASK_ROOT . '/lib/Excel.php';
		$oExcel = new Excel();
		$FileName=$year."年每月数据";
		$oExcel->download($FileName);
		$sheetArr = array('total_count'=>'总咨询量','handle_count'=>'已处理','yes'=>'满意','no'=>'不满意','none'=>'未评价');
		foreach($sheetArr as $key => $value)
		{
			$oExcel->addSheet($value);
			$title = array('-1'=>$value);
			for($i=1;$i<=12;$i++)
			{
				$title[$i] = "第".$i."月";
			}
			$oExcel->addRows(array($title));
			ksort($data_arr);
			foreach($data_arr as $cid => $cid_info)
			{
				$d = array('-1'=>$cid_info['name']);
				for($i=1;$i<=12;$i++)
				{
					$d[$i] = $cid_info[$key][$i];
				}
				$oExcel->addRows(array($d));
				ksort($cid_info['sub']);
				foreach($cid_info['sub'] as $cid1 => $cid1_info)
				{
					$d = array('-1'=>$cid1_info['name']);
					for($i=1;$i<=12;$i++)
					{
						$d[$i] = $cid1_info[$key][$i];
					}
					$oExcel->addRows(array($d));
					ksort($cid1_info['sub']);
					foreach($cid1_info['sub'] as $cid2 => $cid2_info)
					{
						$d = array('-1'=>$cid2_info['name']);
						for($i=1;$i<=12;$i++)
						{
							$d[$i] = $cid2_info[$key][$i];
						}
						$oExcel->addRows(array($d));
						ksort($cid2_info['sub']);
						foreach($cid2_info['sub'] as $cid3 => $cid3_info)
						{
							$d = array('-1'=>$cid3_info['name']);
							for($i=1;$i<=12;$i++)
							{
								$d[$i] = $cid3_info[$key][$i];
							}
							$oExcel->addRows(array($d));
							
							ksort($cid3_info['sub']);
							foreach($cid3_info['sub'] as $cid4 => $cid4_info)
							{
								$d = array('-1'=>$cid4_info['name']);
								for($i=1;$i<=12;$i++)
								{
									$d[$i] = $cid4_info[$key][$i];
								}
								$oExcel->addRows(array($d));
							}
						}
					}	
				}												
			}
			$oExcel->closeSheet();				
		}
		$oExcel->close();
	}
}

?>