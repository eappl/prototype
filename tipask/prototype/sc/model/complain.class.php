<?php
!defined('IN_TIPASK') && exit('Access Denied');
class complainmodel extends base{

    var $db;
    var $base;
    var $cache;
	var $table_complain = "ask_complain";
	var $table_revoke_reason = "ask_complain_revoke_reason";
	var $table_sync = "ask_sync";
	var $table_answer = "ask_complain_answer";
	var $table_revoke_queue = "ask_complain_revoke_queue";
	
    function complainmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
        $this->pdo = $this->base->init_pdo($this->table_complain);
    }
	
    //获取问题内容
    function Get($id,$fields = "*",$public='0,1,2')
    {
		$t = explode(",",$public);
		if(count($t)>=2)
		{
			$where =' AND public in ('.implode(',',$t).')';
		}
		else
		{
			$where = ' AND public='.$public;	
		}
		$table_name = $this->base->getDbTable($this->table_complain);
		$Question = $this->pdo->selectRow($table_name, $fields, "`id` = ? $where" , $id);
		return $Question;
    }
    // 插入一条新数据
    function insert($dataArr)
    {
    	$complainTableName = $this->getDbTable($this->table_complain);
    	$result = $this->pdo->insert($complainTableName,$dataArr);
		if($result>0)
		{
			$q_search['id'] = 'c_'.$result;
			$q_search['title'] = $dataArr['description'];
			$q_search['description'] = $dataArr['description'];
			$q_search['tag'] = json_encode(array(),true);
			$q_search['question_type'] = 'complain';
			$q_search['time'] = $dataArr['time'];
			$q_search['atime'] = 0;
			if($dataArr['public']==0)
			{
				try
				{			
					$this->set_search($q_search);
				}
				catch(Exception $e)
				{
					send_AIC('http://sc.5173.com/index.php?question/complain.html','搜索服务器异常',1,'搜索接口');
				}
			}
		}
    	return $result;
    }
    // 插入一条投诉提交过来的新问题
    function insertNewComplainBySync($DataArr,$ComplainId)
    {
		$this->pdo->begin();
		$scid = $this->insert($DataArr);
		if($scid)
		{
			$syncArr = array('cpid'=>$ComplainId,'scid'=>$scid,'sync'=>1);
			$sync = $this->insertSync($syncArr);
			if($sync)
			{
				$this->pdo->commit();
				return $scid;
			}
		}
		else
		{
			$this->pdo->rollback();
			return false;
		}		
    }
    // 插入一条新投诉回答数据
    function insertAnswer($dataArr)
    {
    	$complainAnswerTableName = $this->getDbTable($this->table_answer);
    	$result = $this->pdo->insert($complainAnswerTableName,$dataArr);
    	return $result;
    }
    // 插入一条投诉ID对应
    function insertSync($dataArr)
    {
    	$complainSyncTableName = $this->getDbTable($this->table_sync);
    	$result = $this->pdo->insert($complainSyncTableName,$dataArr);
    	return $result;
    }
    // 插入一条投诉ID的对应关系
    function getSyncByComplain($ComplainId)
    {
		$complainSyncTableName = $this->getDbTable($this->table_sync);
    	$sync = $this->pdo->selectRow($complainSyncTableName, '*', '`cpid` = ?', $ComplainId);
    	return $sync;
    }
    //获取问题内容
    function GetAnswer($qid,$fields = "*")
    {
    	$table_name = $this->base->getDbTable($this->table_answer);
    	$Answer = $this->pdo->selectRow($table_name, $fields, '`qid` = ?', $qid);
    	return $Answer;
    }
 
    //更新投诉内容
    function Update($id,$dataArr)
    {
		$table_name = $this->base->getDbTable($this->table_complain);
		$update = $this->pdo->update($table_name, $dataArr, '`id` = ?', $id);
    	return $update;
    }
    // 更新投诉回答表
    function UpdateAnswerByQid($id,$dataArr)
    {
    	$table_name = $this->base->getDbTable($this->table_answer);
    	$update = $this->pdo->update($table_name, $dataArr, '`qid` = ?', $id);
    	return $update;
    }
    // 插入一条投诉撤销队列
    function insertRevokeQueue($dataArr)
    {
    	$table_name = $this->getDbTable($this->table_revoke_queue);
    	$result = $this->pdo->insert($table_name,$dataArr);
    	return $result;
    }
    // 插入一条投诉撤销队列
    function delRevokeQueue($id)
    {
    	$table_name = $this->getDbTable($this->table_revoke_queue);
    	$result = $this->pdo->delete($table_name,'`id`=?',$id);
    	return $result;
    }
    // 插入一条投诉撤销队列
    function getRevokeQueue($dataArr)
    {
		$table_name = $this->getDbTable($this->table_revoke_queue);
    	$sql = "select * from ".$table_name." order by id desc";
		$result = $this->pdo->getAll($sql);
    	return $result;
    }
    //我的投诉条件
     function front_myComWhere($author='',$time='',$status=-1,$public='',$qtype=0,$date=''){
     	$where = '1';
    	$where .= $qtype>0? " and qtype = $qtype ":" and qtype > 0 ";
     	$t = explode(",",$public);
		if(count($t)>=2)
		{
			$where .=' AND public in ('.implode(',',$t).')';
		}
		else
		{
			$where .= ' AND public='.$public;	
		}
     	if($date == 'today')
     	{
     		$today = strtotime(date("Y-m-d",time()));
     		$where .= ' and time >='. $today;
     	}
     	else if($date == 'month')
     	{
			$startTime = strtotime(date("Y-m-01",strtotime("-1 month",time())));
			$endTime   = strtotime(date("Y-m-d",time()+86400));
			$where .= " and time>=$startTime and time<=$endTime";
     	}
		else if($date == 'all')
		{
		}
		else if($date == 'threeMonth')
		{
			$startTime = strtotime(date("Y-m-01",strtotime("-3 month",time())));
			$endTime   = strtotime(date("Y-m-d",time()+86400));
			$where .= " and time>=$startTime and time<=$endTime";
		}
     	$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
     	if ($author == '')
     	{
     		return $where;
     	}
    	if($author == '游客')
    	{
    		if(!empty($ask_type['ts']))
    	    {
    			$where .= " AND id in({$ask_type['ts']})";
    		}
    		else
    	    {
    			return false;
    		}
    	}
        else
    	{
    		$where .= " AND author='$author'";
        }

     	$now = time();
		$today = strtotime(date("Y-m-d",$now));
     	if($time == 1) {
     		$where .= " AND time>=" . ($today-604800); //1周
     	} elseif($time == 2) {
     		$where .= " AND time>=" . ($today-2592000); //1月
     	} elseif($time == 3) {
     		$where .= " AND time>=" . ($today-7776000); //3月
     	}

     	$status != -1 && $where .= " AND status=$status";
     	return $where;
     }
     //我的投诉数量
     function front_myComNum($where) {
     	$table_name = $this->base->getDbTable($this->table_complain);
     	$sql = "SELECT count(id) FROM $table_name WHERE $where";
     	return $this->pdo->getOne($sql);
     }
     //我的投诉列表
     function front_myComList($where,$start=0, $limit=20) 
     {
     	$table_name = $this->base->getDbTable($this->table_complain);
     	$sql = "SELECT id,title,time,status,atime,view,author,author_id,jname,view,resolve_photo,resolve,qtype,jid,description
     	 		 	FROM $table_name where $where 
     			ORDER BY time DESC LIMIT $start,$limit";
     	$rs = $this->pdo->getAll($sql);
     	return $rs;
     }
     //投诉相关问题
     function get_relatedComInfo($sid){
     	$sql = "SELECT id,title,view,resolve_photo,resolve,qtype FROM ".DB_TABLEPRE."complain where sid=$sid AND public=0 LIMIT 0,10";
     	return $this->db->fetch_all($sql);
     }

    function getEvaluateCount()
	{
		$return = $this->cache->get("EvaluateCount");
		if(false !== $return)
		{
		    return($return);
		}
        $count =  $this->db->result_first("SELECT v FROM " . DB_TABLEPRE . "setting WHERE k='EvaluateCount'");
		$this->cache->set('EvaluateCount',$count,3600);
    	return $count;
    }
    //获取投诉撤销理由
    function GetRevokeReason()
    {
    	$table_name = $this->base->getDbTable($this->table_revoke_reason);
    	$RevokeReason = $this->pdo->select($table_name, "*");
    	return $RevokeReason;
    }
    /**
     * @param  $id
     * @param  $assess 评价状态 1满意 2不满意
     * @return number
     */
    function updateAssess($id,$assess)
    {
    	$this->pdo->begin();
    	
    	$dataArr = array('asnum'=>'_asnum+1','astime'=>time(),'assess'=>$assess,'status'=>3);
    	$result = $this->Update($id,$dataArr);
    	if($result>0)
    	{
    		$this->pdo->commit();
    		return 1;
    	}
    	else
    	{
    		$this->pdo->rollBack();
    		return 2;
    	}
    }
    // 获取默认投诉数据
    function getAssessData($num,$fields='*')
    {
    	$table_name = $this->getDbTable($this->table_complain);
    	$sql = "select $fields from $table_name where assess=0 and atime>0 limit $num";
    	$NoAssessData = $this->pdo->getAll($sql);
    	return $NoAssessData;
    }
    /**
     * 获取要传递给complain站点的数据
     * $complianWarnNum 同步阈值
     * $num 条数 默认20条
     */
    function ScSyncComplainData($complainWarnNum,$num=20,$limitWranNum=0)
    {
    	$threeMonth = strtotime(date("Y-m-d",time()-60*24*3600));// 取60天内数据
    	$sql = "SELECT id,sid,sname, jid,jname,order_id,good_id,title,description,photo,contact,
    	real_name,author,author_id,time,resolve,resolve_photo,comment FROM ask_complain 
    	WHERE sync <= $limitWranNum AND sync >= -$complainWarnNum AND time>=$threeMonth
    	ORDER BY time LIMIT $num";
		$data =	$this->pdo->getAll($sql);
    	if (!empty($data))
    	{
    		foreach($data as $k=>$v)
    		{
    			$contact = array();
    			$contact = unserialize($v['contact']);
    			$comment = unserialize($v['comment']);
    			$syncId[] = $v['id'];
    			if(is_array($contact))
    			{
    				$data[$k]['contact'] = "{$contact['contact']['qq']};{$contact['contact']['weixin']};{$contact['contact']['mobile']};{$contact['OnceAnsweredQQ']}";
    			}
    			else
    			{
    				$data[$k]['contact'] = ";;{$v['contact']};";
    			}
    			if(is_array($comment))
    			{
    				$data[$k]['comment'] = json_encode($comment);
    			}
    			unset($contact);
    			unset($comment);
    		}
    	}
    	return $data;
    }
    /**
     * 获取投诉站点返回数据
     * @param $syncData 同步数据
     * @param $complainKey 秘钥
     * @param $complainBackUrl complain站点返回值接口
     * @return mixed
     */
    function ComplainBackData($syncData,$complainKey,$complainBackUrl)
    {
    	$postData = rawurldecode(json_encode($syncData));
    	// 数据post提交给complain站点
		$returnData = do_post($complainBackUrl,array('data'=>$postData,'key'=>md5($postData.$complainKey)));
    	$decodeReturnData = json_decode($returnData,true);
    	return $decodeReturnData;
    }
    /**
     * 解压complain 站点返回值
     * @param $resultData 解压数据
     * @param $complainKey 秘钥
     * @return array
     */
    function unpackComplainData($resultData,$complainKey)
    {
    	$idArr = array();
    	if ($resultData['id'] && $resultData['msg']==md5($resultData['ids'].$complainKey))
    	{
    		$ids = explode(';',$resultData['ids']);
    		foreach ($ids as $v)
    		{
    			$temp = explode(',',$v);
    			$idArr[$temp[0]] = $temp[1];
    		}
    	}
    	else
    	{
    	}
    	return $idArr;
    }
    /**
     * sc站点 投诉问题 同步 complain 站点后 更新状态
     * @param  $cpid 投诉站点id
     * @param  $scid sc站点投诉id
     * @return boolean
     */
    function scSyncComplainOperation($cpid,$scid)
    {
    	$this->pdo->begin();
    	$syncTableName = $this->getDbTable($this->table_sync);
    	 
    	$insertData = array('cpid'=>$cpid,'scid'=>$scid,'sync'=>1);
    	$replaceNum = $this->pdo->replace($syncTableName,$insertData); // sync表插入一条新数据
    	$updateNum  = $this->Update($scid,array('sync'=>1)); // 更新complain表sync=1
    	
    	if($replaceNum >0 && $updateNum >0)
    	{
    		$this->pdo->commit();
    		return true;
    	}
    	else
    	{
    		$this->pdo->rollBack();
    		return false;
    	}
    }
    /**
     * 获取complain站点同步数据
     * @param $complainSyncUrl complain站点问题同步sc接口
     * @return boolean
     */
    function ComplainSyncData($complainSyncUrl)
    {
    	$result = get_url_contents($complainSyncUrl);
    	return json_decode($result,true);
    }
    /**
     * 投诉站点问题 同步 sc站点操作
     * @param  $data 操作数据
     * @return String 返回给complain站点数据
     */
    function ComplainSyncScOperation($data)
    {
    	$this->load('qtype');
    	$backComplainStr = ''; // 返回complain站点数据
    	$syncTableName = $this->getDbTable($this->table_sync);
    	foreach($data as $v)
    	{
    		// [contact] => QQ号;微信号;15221018522;
    		$contactArr = explode(';',$v['contact']);
    		if(isset($contactArr['1']))
    		{
    			$contact = array('OnceAnsweredQQ'=>$contact['3'],'contact'=>array('moblie'=>$contactArr['2'],'weixin'=>$contactArr[1],'qq'=>$contactArr['0']));
    		}
    		else
    		{
    			$contact = array('OnceAnsweredQQ'=>"",'contact'=>array('mobile'=>$contactArr['0'],'weixin'=>'','qq'=>''));
    		}
    		 
    		$contact = serialize($contact);
    		$syncInfo = $this->pdo->selectRow($syncTableName,'cpid,scid','`cpid`=?',$v['id']);
    		$qtypeInfo = $_ENV['qtype']->GetQTypeByComplain($v['jid']);
    		 
    		$dataArr = array('sid'=>$v['sid'],'sname'=>$v['sname'],'jid'=>$v['jid'],'jname'=>$v['jname'],
    				'order_id'=>$v['order_id'],'good_id'=>$v['good_id'],'title'=>$v['title'],'description'=>$v['description'],
    				'photo'=>$v['photo'],'contact'=>$contact,'real_name'=>$v['real_name'],'author'=>$v['author'],
    				'author_id'=>$v['author_id'],'time'=>$v['time'],'atime'=>$v['atime'],'category'=>$v['category'],
    				'receive_time'=>$v['receive_time'],'countdown_time'=>$v['countdown_time'],'assess'=>$v['assess'],
    				'status'=>$v['status'],'sync'=>1,'qtype'=>$qtypeInfo['id'],'loginId'=>$v['madcontact']
    		);
    		$dataArr = taddslashes($dataArr); // 过滤特殊字符
    		// 未同步过插入数据
    		if (!isset($syncInfo['cpid']))
    		{
    			// 插入一条新的complain,sync数据
    			$complainId = $this->complainOperate($v['id'],$dataArr);
    			if($complainId >0)
    			{
    				$backComplainStr .= "$complainId,{$v['id']};";
    			}
    		}
    		else // 同步过更新操作
    		{
    			$result = $this->Update($syncInfo['scid'], $dataArr);
    			if($result>=0)
    			{
    				$backComplainStr .= "{$syncInfo['scid']},{$syncInfo['cpid']};";
    			}
    		}
    	}
    
    	return $backComplainStr;
    }
    /**
     * 返回给投诉站点数据
     * @param  $backComplainStr 提交给complain站点数据
     * @param  $backComplainUrl complain站点接口
     * @param  $complainKey 秘钥
     */
    function backComplainData($backComplainStr,$backComplainUrl,$complainKey)
    {
    	if($backComplainStr)
    	{
    		$data = array('data'=>$backComplainStr,'key'=>md5($backComplainStr.$complainKey));
    		$result = do_post($backComplainUrl, $data); // 返回complain站点数据
    	}
    }
    /**
     * complain 站点问题  同步sc 操作
     * @param  $cpid 投诉站点id
     * @param  $dataArr 操作数据
     * @return boolean
     */
    function complainOperate($cpid,$dataArr)
    {
    	$this->pdo->begin();
    	$syncTableName = $this->getDbTable($this->table_sync);
    	$complainId = $this->insert($dataArr);
    	 
    	if($complainId >0 )
    	{
    		$insertData = array('cpid'=>$cpid,'scid'=>$complainId,'sync'=>1);
    		$insertNum = $this->pdo->replace($syncTableName,$insertData);
    
    		if($insertNum>0)
    		{
    			$this->pdo->commit();
    			return $complainId;
    		}
    		else
    		{
    			$this->pdo->rollBack();
    			return false;
    		}
    	}
    	else
    	{
    		$this->pdo->rollBack();
    		return false;
    	}
    }
    /**
     * 传给complain站点,要同步投诉回答问题
     * 获取最近30天问题
     * @param  $syncNum 同步条数
     * @return mixed
     */
    function PostComplainData($syncNum)
    {
    	$complainTable = $this->base->getDbTable($this->table_complain);
    	$complainAnswerTable = $this->base->getDbTable($this->table_answer);
    	$time = strtotime(date("Y-m-d"))-30*86400;
    	$allSyncComplainSql = "SELECT id FROM $complainTable WHERE  time>$time AND status in(1,3) AND id NOT IN(SELECT qid FROM $complainAnswerTable) 
    	order by id desc LIMIT $syncNum";
    	 
    	$allSyncComplainData = $this->pdo->getAll($allSyncComplainSql);
    	$syncDataArr = array();
    	foreach($allSyncComplainData as $v)
    	{
    		$syncDataArr[] = $v['id'];
    	}
    	$data = implode(',',$syncDataArr);
    	
    	return $data;
    }
    /**
     * complain站点返回要同步的问题
     * @param  $data post值
     * @param  $complainAnswerUrl 接口url
     * @param  $complainKey 秘钥
     * @return mixed
     */
    function complainAnswerBackData($data,$complainAnswerUrl,$complainKey)
    {
    	$postData = json_encode($data);
    	// post数据提交给complain站点
    	$returnData = do_post($complainAnswerUrl,array('data'=>$data,'key'=>md5($data.$complainKey)));
    	
    	$decodeReturnData = json_decode($returnData,true);
    	return $decodeReturnData;
    }
    // 插入一条新的数据 到投诉回答表
    function insertComplainAnswer($data)
    {
    	$result = array();
    	$complainAnswerTable = $this->getDbTable($this->table_answer);
    	foreach($data as $v)
    	{
    		$v = taddslashes($v);
    		$dataArr = array('qid'=>$v['scid'],'csn'=>$v['csn'],'content'=>$v['content'],
    				'time'=>$v['atime'],'contact'=>$v['contact']);
    		$insert = $this->pdo->insert($complainAnswerTable,$dataArr);
    		
    		if($insert)
			{
				$complainInfo = $this->Get($dataArr['qid']);
				if($complainInfo['public']==0)
				{
					$q_search['id'] = 'c_'.$complainInfo['id'];
					$q_search['title'] = $complainInfo['description'];
					$q_search['description'] = $complainInfo['description'];
					$q_search['tag'] = json_encode(array(),true);
					$q_search['question_type'] = 'complain';
					$q_search['time'] = $complainInfo['time'];
					$q_search['atime'] = $dataArr['time'];
					
					try
					{
						$this->set_search($q_search);
					}
					catch(Exception $e)
					{
						send_AIC('http://sc.5173.com/crontab/scComplainSync.php "operation=ComplainAnswerToSc&syncNum=20','同步complain站点回答问题,更新到solr服务器失败',1,'搜索接口');
					}			
				}
				else
				{
					$this->delete_search('c_'.$complainInfo['id']);
				}
				return $insert;
			}
			else
			{
				return false;
			}
    	}
    }
    /**
     * 插入一条新的投诉回答内容,更新complain表
     * @param array $data
     * @return number 1成功 2失败 3问题不存在
     */
    function updateComplainAnswer($data)
    {
		$qid = intval($data['qid']);
    	$time = time();
    	$complainInfo = $this->Get($qid);
    	if($complainInfo['id'])
    	{
    		$answerInfo = $this->GetAnswer($qid);
    		if($answerInfo['id'])
    		{

    			$this->pdo->begin();
    			
    			$count = $this->getEvaluateCount();
    			$AnswerDataArr = array('content'=>taddslashes($data['content']),'csn'=>taddslashes($data['csn']),
    					'time'=>$time,'contact'=>taddslashes($data['contact']));
    			
    			$ComplaindataArr = array('atime'=>$time,'sync'=>1);
    			// 当前问题评价次数小于配置评价次数
    			if($complainInfo['asnum']< $count)
    			{
    				$ComplaindataArr['assess']=0;
    				$ComplaindataArr['astime']=0;
    				$ComplaindataArr['status']=1;
    			}
    			$updateAnswerNum = $this->UpdateAnswerByQid($qid,$AnswerDataArr);
    			$updateComplainNum = $this->Update($qid,$ComplaindataArr);
    			
    			if($updateAnswerNum>0 && $updateComplainNum>0)
    			{
    				$this->pdo->commit();
					return 1;
    			}
    			else
    			{
    				$this->pdo->rollBack();
    				return 2;
    			}
    			
    		}
    		else
    		{
    			$this->pdo->begin();    			
    			$dataArr = array('qid'=>$qid,'content'=>taddslashes($data['content']),
    					'csn'=>taddslashes($data['csn']),'time'=>$time,
    					'contact'=>taddslashes($data['contact']));
    			
    			$insertNum = $this->insertAnswer($dataArr);
    			$updateNum = $this->Update($qid,array('atime'=>$time,'status'=>1,'sync'=>1));
    			
    			if($insertNum>0 && $updateNum>0)
    			{
    				$this->pdo->commit();
    				$this->cache->set('ts'.$qid,$time,604800);					
    				return 1;
    			}
    			else
    			{
    				$this->pdo->rollBack();
    				return 2;
    			}
    		}
    	}
    	else
    	{
    		return 3;
    	}
    }

    //投诉转咨询，建议
    function complainQuestionTransform($post)
    {
		$qid = intval($post['qid']); //投诉id
    	$loginId = taddslashes(trim($post['loginId'])); // 操作人
    	$to_type = trim($post['to_type']); // 转换类型 suggest or ask
    	$LogName = TIPASK_ROOT."/data/logs/transformLog.txt";
    	
    	if ($this->base->setting['complainTransAskSuggest'] == 0)
    	{
    		return 3; // sc投诉转咨询、建议开关没打开
    	}
    	
		$complainInfo = $this->Get($qid,"*",'0,1,2');
		if (!isset($complainInfo['id']))
    	{
    		return 4; // 问题不存在
    	}
		$comment = unserialize($complainInfo['comment']);
		//$comment['convert']['to_id'] = 0;
		if (intval($comment['convert']['to_id'])==0) // 问题存在,未隐藏,未转过
    	{
			$categaryInfo = $_ENV['category']->getByQuestionType($to_type); // 获取问题分类信息
    		if (isset($categaryInfo['id'])) // 获取问题的 cid
    		{
    			$complainInfo['cid'] = $categaryInfo['id'];
    		}
    		else // 没有,默认cid为咨询
    		{
    			$complainInfo['cid'] = $_ENV['question']->getType(1);
    		}
    		if (isset($complainInfo['qtype']) && $complainInfo['qtype']>0)
    		{
    			$qtypeInfo = $_ENV['qtype']->GetQType($complainInfo['qtype']);// 获取问题qtype信息
    			if (isset($qtypeInfo['id']))
    			{
    				$date = date("Y-m-d",$complainInfo['time']);
    				$_ENV['question']->modifyUserQtypeNum($date,$qtypeInfo['id'],$to_type,1);
    				$_ENV['question']->modifyUserQtypeNum($date,$qtypeInfo['id'],'complain',-1);
    			}
    		}

    		$comment = unserialize($complainInfo['comment']);
    		$new_comment = serialize(array('reason'=>$post['reason']));

    		if (isset($comment['convert']['from_id'])&&$comment['convert']['from_id']>0)
			{
				$from_id = $comment['convert']['from_id'];
			}
			else
			{
				$from_id = 0;
			}
			/*
			// 问题曾经已经由咨询，或建议 转为投诉过
    		 if (isset($comment['convert']['from_id'])&&$comment['convert']['from_id']>0)
    		 {
 					$transform = array(
							'from_id'=>$qid,
							'from_type'=>'complain',
							'to_type'=>$to_type,
							'to_id'=>$comment['convert']['from_id'],
							'ApplyOperator'=>$loginId,
							'AcceptOperator'=>"system",
							'comment'=>serialize($new_comment),
							'acceptTime'=>$_SERVER['REQUEST_TIME'],
							'applyTime'=>$_SERVER['REQUEST_TIME'],
							'transform_status'=>1,
							'AuthorName'=>$complainInfo['author'],
							);	
				隐藏投诉，显示咨询、建议
    			 $result = $_ENV['question']->transformAskSuggest($complainInfo,$comment['convert']['from_id'],$transform,$to_type,$loginId,$post['reason']);
    			 if ($result>0)
    			 {
    				 return 1; // success
    			 }
    			 else
    			 {
    				 return 2; // failure
    			 }
    		 }
			else // 问题未同步过,插入一条新纪录
			*/
    		{
    			$contact = unserialize($complainInfo['contact']);
    			$new_comment = $contact;
				$new_comment['convert'] = array('from_type'=>'complain','from_id'=>$complainInfo['id'],'reason'=>$post['reason']);
				$new_comment['OS'] = $comment['OS'];
				$new_comment['Browser'] = $comment['Browser'];
				$new_comment['order_id'] = $complainInfo['order_id'];
				$hidden = $complainInfo['public']==2?2:1;
    			$questionInfo = array(
    					'author'=>$complainInfo['author'],
    					'author_id'=>$complainInfo['author_id'],
    					'title'=>$complainInfo['title'],
    					'description'=>$complainInfo['description'],
    					'comment'=>serialize($new_comment),
    					'qtype'=>$complainInfo['qtype'],
    					'attach'=>$complainInfo['photo'],
    					'time'=>$complainInfo['time'],
    					'ip'=>$complainInfo['ip'],
    					'cid'=>$complainInfo['cid'],
    					'qtype'=>$complainInfo['qtype'],
						'hidden'=>$hidden,
    				);
				
    			$this->pdo->begin();
    			$insertId = $_ENV['question']->insert($questionInfo);
				if(intval($insertId)>0)
    			{
					$transform = array(
							'from_id'=>$qid,
							'from_type'=>'complain',
							'to_type'=>$to_type,
							'to_id'=>$insertId,
							'ApplyOperator'=>$loginId,
							'AcceptOperator'=>"system",
							'comment'=>serialize($new_comment),
							'acceptTime'=>$_SERVER['REQUEST_TIME'],
							'applyTime'=>$_SERVER['REQUEST_TIME'],
							'transform_status'=>1,
							'AuthorName'=>$complainInfo['author'],
							);	
					$comment['convert'] = array(
    						'to_type'=>$to_type,
    						'to_id'=>$insertId,
    						'transformTime'=>$_SERVER['REQUEST_TIME'],
    						'loginId'=>$loginId,
							'reason'=>$post['reason'],
    						);
    				$dataArr = array('comment'=>serialize($comment),'public'=>1,'sync'=>1); // 更新关联投诉ID到投诉表,隐藏该投诉问题
    				$updateNum = $this->Update($complainInfo['id'],$dataArr );
					$transformLogId = $_ENV['question']->insertTransformLog($transform);
					if($updateNum>0 && $transformLogId>0)
    				{
    					
						$this->pdo->commit();
						$this->base->sys_admin_log($insertId,$complainInfo['author'],"投诉单转换，理由:".$post['reason'],18);
						if ($from_id>0)
						{
							$QuestionInfo = $_ENV['question']->Get($from_id);
							 $_ENV['question']->ApplyToOperator($insertId,$QuestionInfo['js_kf'],18);
						}
    					return $insertId; // success
    				}
    				else
    				{
    					$this->pdo->rollBack();
    					return 2; // failure rollback
    				}
    			}
    			else
    			{
    				$this->pdo->rollBack();
    				return 2;  // failure rollback
    			}
    		}
    	}
    	else
    	{
			return $comment['convert']['to_id']; // 问题已经转过成功
    	}
    }
}

?>
