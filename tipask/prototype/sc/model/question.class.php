<?php

!defined('IN_TIPASK') && exit('Access Denied');
require TIPASK_ROOT . '/lib/phpanalysis.class.php';

class questionmodel extends base
{

    var $db;
    var $base;
    var $cache;
	//var $redis;
    var $table_question = "ask_question";
	var $table_question_h = "ask_question_h";
    var $table_operator = "ask_operator";
    var $table_complain = "ask_complain";
    var $table_answer   = "ask_answer";
	var $table_answer_h   = "ask_answer_h";
    var $table_author_num    = "ask_author_num";
	var $table_transform_log = "ask_complain_transform_log";
	var $table_baoXianLog = "ask_BaoXianLog";
	var $table_h_map = "ask_histroy_map";


    var $field = array('id','cid','cid1','id','cid','cid1','cid2','cid3','cid4','author',
    		'authorid','author_id','title','description','time','endtime','hidden','views','status','ip','revocation',
    		'rev_man','revocation_time','start_man','start_time','mark','pid','from','handle_status','tag','gameid',
    		'game_name','phone','comment','attach','receive_time','is_hawb','js_kf','is_pj','q_handle_status','help_status','display_h','atime','r_site','qtype');

    function questionmodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
		//$this->redis = $base->redis;
        $this->pdo = $this->base->init_pdo($this->table_question);		
    }

    //获取问题内容
    function Get($id,$fields = "*")
    {
    	$table_name = $this->base->getDbTable($this->table_question);
    	$Question = $this->pdo->selectRow($table_name, $fields, '`id` = ?', $id);
    	return $Question;
    }
    // 更新问题内容
	function Update($id,$dataArr)
    {
		$table_name = $this->base->getDbTable($this->table_question);
		$update = $this->pdo->update($table_name, $dataArr, '`id` = ?', $id);
    	return $update;
    }
    // 插入一条新数据
    function insert($dataArr)
    {
    	$table_name = $this->getDbTable($this->table_question);
    	$result = $this->pdo->insert($table_name,$dataArr);
    	return $result;
    }
    //获取问题内容
    function GetMap($id,$question_type,$fields = "*")
    {
		$table_name = $this->base->getDbTable($this->table_h_map);
		$Mapping = $this->pdo->selectRow($table_name, $fields, '`question_type` = ? and `min` <= ? and `max` >= ?', array('ask',$id,$id));
    	return $Mapping;
    }

     //获取标准的一对多的数组
    function Fetch_List($sql,$history = 0)
    {
		$data = array();
		if($history==0)
		{
			$list = $this->pdo->getAll($sql);
		}
		else
		{
			$pdo_h = $this->base->init_pdo($this->table_question_h."_".$history);
			$list = $pdo_h->getAll($sql);
		}

    	if(!empty($list))
    	{
			foreach($list as $k => $v)
    		{
    			if($v['phone'] != "")
    			{
    				$data[$v['id']]['contact'] = $v['phone'];
    			}

    			$contactArr = unserialize($v['comment']);

    			if(!empty($contactArr))
    			{
    				foreach($contactArr['contact'] as $v1)
    				{
    					if($v1 != "")
    					{
    						$data[$v['id']]['contact'] = $v1;
    						break;
    					}
    				}
    			}
    			$v_key = array_keys($v);
    			foreach($v_key as $k_v)
    			{
    				if(in_array($k_v,$this->field))
    				{
    					$data[$v['id']][$k_v] = $v[$k_v];
    				}
    				else
    				{
    					if(!empty($v['Aid']))
    					{
    						$data[$v['id']]['answerModel'][$v['Aid']][$k_v] = $v[$k_v];
    					}

    				}
    			}
    		}
    	}
    	return $data;
    }

/*
 * 前台函数部分
 */
    //前台列表显示函数（已修改）
    function front_question_show($qtype=0,$tag_arr=array(),$game='',$type='',$start=0, $limit=20)
    {
     	$table_question = $this->base->getDbTable($this->table_question);

    	$tag_str = '';
    	$cid_str = '';
    	if(!empty($tag_arr))
    	{
    		foreach($tag_arr as $tar)
    		{
    			$tag_str .= " AND tag LIKE '%$tar%' ";
    		}
    	}

    	$zx = $this->getType(1);
    	$jy = $this->getType(2);
    	$ts = $this->getType(3);
    	if($type == 1)
    	{
    		$cid = $zx; // 咨询 id
    		$cid_str = !empty($cid)?' AND cid='.$cid:'';
    	}
    	elseif($type == 3)
    	{
    		$cid = $jy; // 建议id
    		$cid_str = !empty($cid)?' AND cid='.$cid:'';
    	}
     	$whereQtype = $qtype? " qtype = $qtype and ":" qtype >0 and ";

    	$sql = "SELECT id,cid,title,time,views,status,q_handle_status,atime AS Atime,qtype,description
				FROM $table_question
				WHERE $whereQtype pid = 0
				    AND revocation = 0  $tag_str $game $cid_str
				ORDER BY time DESC
				LIMIT $start,$limit";
    	$sign = md5($sql);
    	$cache_data = $this->cache->get($sign);
    	if(false !== $cache_data) return $cache_data;

    	$list = $this->pdo->getAll($sql);
    	foreach($list as $k=>$v)
    	{
    		if($list[$k]['cid'] == $zx)
    		{
    			$list[$k]['type'] = '[咨询] ';
    		}
    		elseif($list[$k]['cid'] == $ts)
    		{
    			$list[$k]['type'] = '[投诉] ';
    		}
    		elseif($list[$k]['cid'] == $jy)
    		{
    			$list[$k]['type'] = '[建议] ';
    		}
    		else
    		{
    			$list[$k]['type'] = '[垃圾箱] ';
    		}
    		$list[$k]['time']  = $this->base->timeToText($v['time']);
    		$list[$k]['Atime'] = !empty($v['Atime']) ? $this->base->timeLagToText($v['time'],$v['Atime']): '-';
    		$list[$k]['views'] = intval($v['views']);
    	}
    	if(!empty($list)) $this->cache->set($sign,$list,600);//写入缓存，缓存时间为1分钟
    	return $list;
    }

    //前台问题的数量（已修改）
    function front_get_num($tag_arr=array(),$game='',$type='',$qtype=0){
     	$table_question = $this->base->getDbTable($this->table_question);

     	$whereQtype = $qtype? " qtype = $qtype and ":" qtype >0 and ";
    	$tag_str = '';
    	$cid_str = '';
    	if(!empty($tag_arr)){
    		foreach($tag_arr as $tar){
    			$tag_str .= " AND tag LIKE '%$tar%' ";
    		}
    	}
    	if($type == 1){
    		$cid = $this->getType(1); // 咨询id
    		$cid_str = !empty($cid)?' AND cid='.$cid:'';
    	}elseif($type == 3){
    		$cid = $this->getType(2); // 建议id
    		$cid_str = !empty($cid)?' AND cid='.$cid:'';
    	}
    	$sql = "SELECT
				  COUNT(*)
				FROM $table_question
    					WHERE $whereQtype pid = 0
    					AND revocation = 0  $tag_str $game $cid_str";
    	$sign = md5($sql);
    	$cache_data = $this->cache->get($sign);
    	if(false !== $cache_data) return $cache_data;

    	$count = $this->pdo->getOne($sql);
    	if(!empty($count)) $this->cache->set($sign,$count,600);//写入缓存 ，缓存时间为1分钟
    	return $count;
    }

    function front_getWhere($type='',$ctype='',$qtype=0,$date=''){
    	$where = '';
    	$type !='' && $where .= "AND cid=$type";
    	$where .= $qtype? " and qtype = $qtype ":" and qtype >0 ";
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
		}else if($date == 'all')
		{
		}
		else if($date == 'threeMonth')
		{
			$startTime = strtotime(date("Y-m-01",strtotime("-3 month",time())));
			$endTime   = strtotime(date("Y-m-d",time()+86400));
			$where .= " and time>=$startTime and time<=$endTime";
		}
    	return $where;
    }

    //获取问题的数量（已修改）
    function front_getNum($where){
     	$table_question = $this->base->getDbTable($this->table_question);

    	$sql =  "SELECT COUNT(id) FROM  $table_question WHERE pid = 0 AND revocation = 0 $where" ;
    	return $this->pdo->getOne($sql);

    }
    // 首页大家正在问列表
    // $type=2 是获取垃圾箱列表
    function front_get_list($where,$start=0, $limit=20,$type=1){
		$table_question = $this->base->getDbTable($this->table_question);

    	$sql = "SELECT  id, title,cid,time,views,status,q_handle_status,qtype,comment,atime AS Atime,description
				FROM $table_question WHERE  pid = 0 AND revocation = 0 $where ORDER BY time DESC LIMIT $start,$limit";
		$cache_key = md5($sql);
    	$cache_data = $this->cache->get($cache_key);
    	//if(false !== $cache_data) return $cache_data;	//如果存在缓存，则读取缓存
    	$rt = $this->pdo->getAll($sql);
    	if($type == 1)
    	{
    		foreach($rt as $k=>$v)
    		{
    			$rt[$k]['time']	  =  $this->base->timeToText($v['time']);
    			$rt[$k]['Atime']  =  !empty($v['Atime']) ? $this->base->timeLagToText($v['time'],$v['Atime']): '-';
    			$rt[$k]['views'] = intval($v['views']);
    		}
    	}
    	else if($type == 2)
    	{
    		foreach($rt as $k=>$v)
    		{
	    		$rt[$k]['time']	  =  $this->base->timeToText($v['time']);
	    		if(!empty($v['comment']))
	    		{
	    			$comment = unserialize($v['comment']);
	    			$rt[$k]['comment'] = $comment['reason'];
	    		}
	    		else
	    		{
	    			$rt[$k]['comment'] = '';
	    		}
	    		$rt[$k]['views'] = intval($v['views']);
    		}
    	}


    	if(!empty($rt))
    	{
    		$this->cache->set($cache_key,$rt,5);//写入缓存，缓存时间为3秒
    	}
    	return  $rt;
    }

    //获取咨询或投诉建议类型
    function getType($type='')
    {
    	$cid = '';
    	if($type == 1)
    	{
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE question_type='ask'";
    	}
    	elseif($type == 2)
    	{
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE question_type='suggest'";
    	}
    	elseif($type==3)
    	 {
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE question_type='complain'";
    	}
    	elseif($type==4)
    	{
    		$sql = "SELECT id FROM ". DB_TABLEPRE . "category WHERE question_type='dustbin'";
    	}
		$cache_key = md5($sql);
		$cache_data = $this->cache->get($cache_key);
		if(false !== $cache_data) return $cache_data;

		$cid = $this->db->result_first($sql);
		if(!empty($cid))
		{
			$this->cache->set($cache_key,$cid,2592000);
		}
    	return $cid;
    }
    //我的咨询或建议数量
    function front_myQueNum($where) {
    	$table_name = $this->base->getDbTable($this->table_question);
		$sql = "SELECT count(id) FROM $table_name WHERE pid = 0 AND revocation = 0 $where";
		return $this->pdo->getOne($sql);
    
	}

    /**
     * 我的咨询或建议条件
     * $ctype 咨询,建议cid
     */
    function front_myQueWhere($author="",$type,$time,$status=-1,$cid=""){
    	$where = '  ';
    	$now = time();
		$today = strtotime(date("Y-m-d",$now));
    	$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
		
		if($author == '游客') 
		{
			if(!empty($ask_type['zx']) || !empty($ask_type['jy'])) {
    			$z_cid  = $this->getType(1);
    			$j_cid  = $this->getType(2);
				$IdArr = array($ask_type['zx'],$ask_type['jy']);
				foreach($IdArr as $key => $value)
				{
					if(trim($value)=="")
					{
						unset($IdArr[$key]);
					}
				}
    			$where .= " AND id in(".implode(",",$IdArr).")";
    			$where .= " AND cid in(".($type=="zx"?$z_cid:$j_cid).")";

    		} else {
    				return false;
    		}
    	}
		else 
		{
    		$where .= " AND author='$author'";
    		$cid   != "" && $where .= " AND cid =$cid";
        }
		if($time == 1) {
    		$where .= " AND time>=" . ($today-604800); //1周
    	} elseif($time == 2) {
    		$where .= " AND time>=" . ($today-2592000); //1月
    	} elseif($time == 3) {
    		$where .= " AND time>=" . ($today-7776000); //3月
    	}

    	$status == 1  && $where .= " AND status !=1";
    	$status == 0  && $where .= " AND status  =1";
    	return $where;
    }
    // 获取我的垃圾箱 条件
    function front_myDustbinWhere($author="",$time,$cid="")
    {
    	if($author == '游客')
    	{
    		$author = 'o';
    	}
    	$where = ' ';
    	$now = time();
		$today = strtotime(date("Y-m-d",$now));
    	$where .= " AND author='$author'  AND cid =$cid";

    	if($time == 1) {
    		$where .= " AND time>=" . ($today-604800); //1周
    	} elseif($time == 2) {
    		$where .= " AND time>=" . ($today-2592000); //1月
    	} elseif($time == 3) {
    		$where .= " AND time>=" . ($today-7776000); //3月
    	}
    	return $where;

    }
    // 获取我的垃圾箱个数
    function front_myDustbinNum($where)
    {
    	$table_name = $this->base->getDbTable($this->table_question);
		$sql = "SELECT count(id) FROM $table_name WHERE pid=0  $where";
    	return $this->pdo->getOne($sql);
    }
    // 获取我的咨询,建议
     function front_myQueList($where,$start=0, $limit=20) {
		$table_name = $this->base->getDbTable($this->table_question);
		$sql = "SELECT  id,title,time,views,status,q_handle_status,atime  AS Atime,description ".
				"FROM $table_name WHERE pid=0 AND revocation=0 $where ORDER BY time DESC LIMIT $start,$limit";
		$List = $this->pdo->getAll($sql);
		foreach($List as $key => $value)
		{
			$myQueList[$value['id']] = $value;
			$myQueList[$value['id']]['QuestionUrl'] = $this->getQuestionLink($value['id'],"question");			
		}
     	if(!empty($myQueList)) 
		{
     		foreach($myQueList as $key=>$value) {
     			$myQueList[$key]['time']  = date("Y-m-d H:i", $value['time']);
     			$myQueList[$key]['Atime'] = !empty($value['Atime']) ? date("Y-m-d H:i", $value['Atime']) : '';
     			$myQueList[$key]['views'] = intval($value['views']);
     		}
     		$pid_arr = array_keys($myQueList);
     		$where = " AND pid IN (".implode(',', $pid_arr).") ";
     		$complainCid = $this->getType(3);
     		$sql = "SELECT  id,title,time,views,description,status,atime  AS Atime,pid
				FROM $table_name WHERE revocation=0  $where AND cid !=$complainCid ORDER BY time";
			$SubList = $this->pdo->getAll($sql);
			foreach($SubList as $key => $value)
			{
				$QueList[$value['id']] = $value;		
			}			
     		foreach($QueList as $k=>$v) 
			{
     			$QueList[$k]['time']  = date("Y-m-d H:i", $v['time']);
     			$QueList[$k]['Atime'] = !empty($v['Atime']) ? date("Y-m-d H:i", $v['Atime']) : '';
     			$QueList[$k]['views'] = intval($v['views']);
				$QueList[$k]['QuestionUrl'] = $this->getQuestionLink($v['id'],"question");
     			if(array_key_exists($v['pid'], $myQueList)) {
     				$myQueList[$v['pid']]['child'][] = $QueList[$k];
     			}
     		}
     		return $myQueList;
     	} else {
     		return false;
     	}
     }

      /**
       * 前台详细页-获取相关问题
       * @param  $id 问题id
       */
      function front_related_question($id) {

      	$sql = 'SELECT
				  id,
				  title
				FROM ' . DB_TABLEPRE . 'question
				WHERE cid = (SELECT
				               cid
				             FROM '.DB_TABLEPRE.'question
				             WHERE id = ' . $id . ')
				   	  AND pid = 0
		    		  AND revocation = 0
				LIMIT 0,10';
      	return $this->db->fetch_all($sql);
      }

      /**
       *  问题详细
       * @param int $id
       */
      function front_answer_question($id,$year=0) 
	  {
      	$complainCid = $this->getType(3);
      	if(intval($year)==0)
		{
			$table_question = $this->base->getDbTable($this->table_question);
			$table_answer = $this->base->getDbTable($this->table_answer);
			$sql = 'SELECT
					  q.id,
					  q.cid,
					  q.views,
					  q.pid,
					  q.tag,
					  q.status,
					  q.time,
					  q.author,
					  q.description,
					  q.attach,
					  q.is_pj,
					  q.q_handle_status,
					  q.atime,
					  q.qtype,
					  a.time as Atime,
					  a.author as Aauthor,
					  a.content as Acontent,
					  a.id as Aid,
					  q.hidden
					  
					FROM '.$table_question.' AS q
					  LEFT JOIN '.$table_answer.' AS a
						ON q.id = a.qid
					WHERE
						 q.revocation = 0 AND q.id ='.$id.' AND cid !='.$complainCid.'
					   ORDER BY a.time DESC' ;
				$rt = $this->Fetch_List($sql);		
		}
		else
		{
			$table_question = $this->base->getDbTable($this->table_question_h."_".$year);
			$table_answer = $this->base->getDbTable($this->table_answer_h."_".$year);
			$sql = 'SELECT
					  q.id,
					  q.cid,
					  q.views,
					  q.pid,
					  q.tag,
					  q.status,
					  q.time,
					  q.author,
					  q.description,
					  q.attach,
					  q.is_pj,
					  q.q_handle_status,
					  q.atime,
					  q.qtype,
					  a.time as Atime,
					  a.author as Aauthor,
					  a.content as Acontent,
					  a.id as Aid,
					  q.hidden
					  
					FROM '.$table_question.' AS q
					  LEFT JOIN '.$table_answer.' AS a
						ON q.id = a.qid
					WHERE
						 q.revocation = 0 AND q.id ='.$id.' AND cid !='.$complainCid.'
					   ORDER BY a.time DESC' ;
				$rt = $this->Fetch_List($sql,$year);
				$rt[$id]['history'] = 1;		
		}

			$rt = current($rt);
        	$categoryInfo = $_ENV['category']->get($rt['cid']);
       		$rt['type'] = isset($categoryInfo['name'])?$categoryInfo['name']:"垃圾箱";
        	if(empty($rt['atime']))
        	{
        		$rt['distantTime'] = "";
        	}
        	else
        	{
        		$rt['distantTime'] = $this->base->timeLagToText($rt['time'],$rt['atime']);
        	}
        	$rt['description'] = strip_tags($rt['description']);
        	$rt['time'] = date("Y-m-d H:i", $rt['time']);
        	$rt['views'] = intval($rt['views']);
        	if(isset($rt['answerModel']) && !empty($rt['answerModel']))
        	{
        		foreach($rt['answerModel'] as $key => &$val)
        		{
        			$name = trim($val['Aauthor']);
        			$rt['answerModel'][$key]['Atime'] = !empty($val['Atime']) ? date("Y-m-d H:i", $val['Atime']) : '';
        			if($name != '')
        			{
                        $rt['answerModel'][$key]['operatorModel']=$_ENV['operator']->getUser($name);
        			}
        		}
        	}
      	return $rt;
      }

      /**
       * 子问题列表
       * @param unknown_type $id
       * @return unknown|boolean
       */
      function front_child_answer_question($id)
      {
		$complainCid = $this->getType(3);
		$dustbinCid = $this->getType(4);
        $sql = 'SELECT
				  q.id,
				  q.description,
				  q.time,
     			  q.author,
      		      q.status,
				  a.time  AS Atime,
      		      a.author  AS Aauthor,
      		      a.content AS Acontent,
      		      a.id  AS Aid
				FROM ' . DB_TABLEPRE . 'question AS q
				  LEFT JOIN '. DB_TABLEPRE. 'answer AS a
				    ON q.id = a.qid
				WHERE q.revocation = 0 AND cid not in ('.$complainCid.','.$dustbinCid.') AND q.pid = '.$id.'
			    ORDER BY q.time,a.time DESC';
		$QueList = $this->Fetch_List($sql);
     	if(!empty($QueList))
     	{
     		foreach($QueList as $k=>$v)
     		{
     			$QueList[$k]['time'] = date("Y-m-d H:i", $v['time']);
     			$QueList[$k]['description'] = strip_tags($QueList[$k]['description']);
     			if(isset($v['answerModel']) && !empty($v['answerModel']))
     			{
     				foreach($v['answerModel'] as $key => &$val)
     				{
     					$name = trim($val['Aauthor']);
     					if(empty($val['Atime']))
     					{
     						$QueList[$k]['answerModel'][$key]['Atime'] = "";
     					}
     					else
     					{
     						$QueList[$k]['answerModel'][$key]['Atime'] = $this->base->timeLagToText($v['time'],$val['Atime']);
     					}

     					if($name != '')
     					{
                            $QueList[$k]['answerModel'][$key]['operatorModel'] = $_ENV['operator']->getUser($name);							
     					}
     				}
     			}
     		}
     		return $QueList;
     	} else {return false;}

      }

      /**
      * 获取前台可显示问题的游戏
      *
      */
      function get_question_game(){
      	  $cache_data = $this->cache->get('left_game');//如果存在缓存，则读取缓存
      	  if(false !== $cache_data) return $cache_data;

      	  $arr = array();
      	  $game = $this->get_all_game();
      	  if(empty($game)) return $arr;//如果没有获取到游戏，直接返回
      	  $game_list = $this->db->fetch_all("SELECT gameid,game_name,COUNT(*) AS num FROM ". DB_TABLEPRE . "question " .
    	       " WHERE pid=0 AND revocation=0 AND gameid<>'' GROUP BY gameid ORDER BY num DESC");
    	  $conut = count($game_list);
    	  if($conut){
    	  	   $game_id_arr = array();
    	  	   foreach($game_list as $v){
    	  	   	   $game_id_arr[] = $v['gameid'];
    	  	   }
    	  	   if($conut < 10){
    	  	   	    $num = 10 - $conut;
                    $game_key_arr = array_keys($game);
                    $game_diff = array_diff($game_key_arr,$game_id_arr);
                    $game_rand = array_rand($game_diff,$num);
                    if(is_array($game_rand)){
                    	foreach($game_rand as $key => $val){
	                    	 $game_no_question[$key]['gameid'] = $game_diff[$val];
	                    	 $game_no_question[$key]['game_name'] = $game[$game_diff[$val]];
	                    	 $game_no_question[$key]['num'] = '';
	                    }
                    }else{
                    	 $game_no_question[0]['gameid'] = $game_diff[$game_rand];
                    	 $game_no_question[0]['game_name'] = $game[$game_diff[$game_rand]];
                    	 $game_no_question[0]['num'] = '';
                    }

    	  	   	    $arr = array_merge($game_list,$game_no_question);
    	  	   }else{
    	  	   	    $arr = array_slice($game_list,0,10);
    	  	   }
    	  }else{
    	  	   $game_rand = array_rand($game,10);
    	  	   foreach($game_rand as $key => $val){
                	$arr[$key]['gameid'] = $val;
                	$arr[$key]['game_name'] = $game[$val];
                	$arr[$key]['num'] = '';
               }
    	  }

    	  if(!empty($arr)) $this->cache->set('left_game',$arr,3600);//写入缓存,缓存有有效期为1小时
    	  return $arr;

      }

      //列表页的游戏名称显示
      function get_game_list($tag_arr=array(),$game=''){
      	$sign = md5('[game]'.implode(',',$tag_arr).$game);
      	$cache_data = $this->cache->get($sign);
      	if(false !== $cache_data) return $cache_data;
      	$tag_str = '';
      	if(!empty($tag_arr)){
      		foreach($tag_arr as $tar){
      			$tag_str .= " AND tag LIKE '%$tar%' ";
      		}
      	}
      	if($game == '') $game = " AND gameid<>'' ";
      	$arr = array();
      	$total = 0;
      	$top_10 = 0;
      	$sql = "SELECT
				  gameid,
				  game_name,
				  COUNT(*)  AS num
				FROM ".DB_TABLEPRE."question
				WHERE pid=0 AND revocation=0 $tag_str $game
				GROUP BY gameid
				ORDER BY num DESC";
      	$query = $this->db->query($sql);
      	while($row = $this->db->fetch_array($query)){
      		$arr[] = $row;
      		$total+=$row['num'];
      	}

      	if(count($arr) > 10){
      		$arr = array_slice($arr,0,10);
      		foreach($arr as $val){
      			$top_10+=$val['num'];
      		}
      		$other_games = array('gameid'=>'other_games','game_name'=>'其它游戏','num'=>$total-$top_10);
      		$arr[] = $other_games;
      	}
      	if(!empty($arr)) $this->cache->set($sign,$arr,600);//写入缓存，缓存时间为10分钟
      	return $arr;
      }

      //获取子问题的父问题ID
      function get_parent_qid($qid)
	  {
		$questionInfo = $this->Get($qid,"pid");
		if($questionInfo['pid']>0)
		{
			return $questionInfo['pid'];
		}
		else
		{
			return $qid;
		}
      }

      //获取问题的浏览量
      function get_question_view($qid){
      	     $views = tcookie('views');
      	     if(!empty($views)){
      	     	 $view_arr = explode(',',$views);
      	     	 if(!in_array($qid,$view_arr))
				 {
      	     	 	 tcookie('views',$views.','.$qid);
      	     	 	 $this->db->query("UPDATE ".DB_TABLEPRE."question SET views=views+1 WHERE id='".$qid."'");
					 //$this->redis->LPUSH('view',$qid);
      	     	 }
      	     }
			 else
			 {
      	     	 tcookie('views',$qid);
      	     	 $this->db->query("UPDATE ".DB_TABLEPRE."question SET views=views+1 WHERE id='".$qid."'");
				 //$this->redis->LPUSH('view',$qid);
      	     }
			 //$this->redis->LPUSH('view',$qid);
      }

     //根据ip获取每个小时的提问数
     function get_num_by_ip($ip,$add=0)
	 {
     	  $old_time = time() - 3600;
     	  if($add==0)
		  {
			$sql = "SELECT COUNT(*) FROM " . DB_TABLEPRE . "question WHERE ip='".$ip."' AND pid = 0 AND time>".$old_time;
		  }
		  else
		  {
			$sql = "SELECT COUNT(*) FROM " . DB_TABLEPRE . "question WHERE ip='".$ip."' AND pid > 0 AND time>".$old_time;
		  }		  
		  $num = $this->db->result_first($sql);
     	  return $num;
     }
     //根据投诉ip获取每个小时的投诉数
     function get_complain_num_by_ip($ip){
     	  $old_time = time() - 3600;
     	  $sql = "SELECT COUNT(*) FROM " . DB_TABLEPRE . "complain WHERE ip='".$ip."' AND time>".$old_time;
     	  $num = $this->db->result_first($sql);
     	  return $num;
     }

     //我的问题总和
     function total_question(){
     	if($this->base->ask_front_name == '游客') {
     		$ask_type = unserialize(stripslashes($_COOKIE['quickask']));
     		return $this->getUrl($ask_type);
     	} else {
     		if(!empty($_COOKIE['logqid'])) {
     			$type = unserialize(stripslashes($_COOKIE['logqid']));
     			return $this->getUrl($type);
     		} else {
     			$zx = $this->db->result_first("SELECT GROUP_CONCAT(id) FROM " . DB_TABLEPRE . "question WHERE author='{$this->base->ask_front_name}' AND cid='".$this->getType(1)."'");
     			$jy = $this->db->result_first("SELECT GROUP_CONCAT(id) FROM " . DB_TABLEPRE . "question WHERE author='{$this->base->ask_front_name}' AND cid='".$this->getType(2)."'");
     			$ts = $this->db->result_first("SELECT GROUP_CONCAT(id) FROM " . DB_TABLEPRE . "complain WHERE author='{$this->base->ask_front_name}'");
     			empty($zx) && $zx = '0';
     			empty($jy) && $jy = '0';
     			empty($ts) && $ts = '0';
     			$type  = array('zx'=>$zx,'ts'=>$ts,'jy'=>$jy);
     			setcookie('logqid',serialize($type),time()+120);
     			return $this->getUrl($type);
     		}
     	}
     }
     // 跳到最新回复问题页
     function getUrl($ask_type) {
     	$flag = false;
     	$zx_total  = $ts_total  = $jy_total  = array();
     		if(!empty($ask_type)) {
     			$rt = '';
     			foreach(array('zx','ts','jy') as $v) {
     				if (isset($ask_type[$v])) {
     					$type = explode(',',$ask_type[$v]);
     				}
     				if(!empty($type)) {
     					foreach($type as $val) {
     						if($v == 'ts'){
     							$rt = $this->cache->get('ts'.$val);
     						}else{
     							$rt = $this->cache->get('fw'.$val);
     						}
     						if($rt) {
     							$prefix = $v.'_total';
     							${$prefix}[] = $rt;
     							$flag = 'new';
     						}
     					}
     				}
     			}
     		}
     		$zx_max = !empty($zx_total) ? max($zx_total) : 0;
     		$ts_max = !empty($ts_total) ? max($ts_total) : 0;
     		$jy_max = !empty($jy_total) ? max($jy_total) : 0;

     		$max = ($zx_max > $ts_max) ? ($zx_max > $jy_max ? $zx_max : $jy_max) : ($ts_max > $jy_max ? $ts_max : $jy_max);
     		$location = ($max == $zx_max) ? 'zx' : ($max == $ts_max ? 'ts' : 'jy');
     		switch($location){
     			case  "zx" : $url="http://sc.5173.com/index.php?question/my_ask.html";
     			break;
     			case  "ts" : $url="http://sc.5173.com/index.php?question/my_complain";
     			break;
     			case  "jy" : $url="http://sc.5173.com/index.php?question/my_suggest";
     			break;
     			default    : $url="http://sc.5173.com/index.php?question/my_ask.html";
     		}
     		return array($flag,$url);
     }
     // 首页热门问题列表
     function front_hot_question($qtype = 0,$start=0,$limit =20,$date="")
     {
     	$table_question = $this->base->getDbTable($this->table_question);

     	$zx = $this->getType(1);
     	$jy = $this->getType(2);
     	$whereQtype = $qtype? " qtype = $qtype and ":" qtype >0 and ";
     	$time = "";
     	if($date == 'today')
     	{
     		$today = strtotime(date("Y-m-d",time()));
     		$time .= ' and time >='. $today;
     	}
     	else if($date == 'month')
     	{
     		$month = strtotime(date("Y-m-01",time()));
     		$time .= ' and time >='. $month;
     	}else if($date == 'all')
		{
		}
     	$sql = "SELECT
				  id,title,cid,time,views,status,q_handle_status,atime AS Atime,qtype,description
				FROM $table_question WHERE  $whereQtype pid = 0 AND revocation = 0 AND cid in ($zx,$jy) $time ORDER BY views DESC LIMIT ".$start.",".$limit;
     	$cache_key = md5($sql);
     	$cache_data = $this->cache->get($cache_key);
     	if(false !== $cache_data) return $cache_data;//如果存在缓存，则读取缓存

     	$rt = $this->pdo->getAll($sql);
     	foreach($rt as $k=>$v){

     		$categoryInfo = $_ENV['category']->get($v['cid'],'name');
     		$rt[$k]['type'] = '['.$categoryInfo['name'].'] ';

     		$rt[$k]['time']  = $this->base->timeToText($v['time']);
     		$rt[$k]['Atime'] = !empty($v['Atime']) ? $this->base->timeLagToText($v['time'], $v['Atime']) : '-';
     		$rt[$k]['views'] = intval($v['views']);
     	}

     	if(!empty($rt)){
     		$this->cache->set($cache_key,$rt,15);//写入缓存，缓存时间为8秒
     	}
     	return  $rt;
     }
     //计算咨询投诉合并后数据总条数 added by chouto
	function front_hotQuestionRowNum($qtype = 0,$date="") {
     	$table_question = $this->base->getDbTable($this->table_question);
     	$whereQtype = $qtype? " qtype = $qtype and ":" qtype >0 and ";
		$zx = $this->getType(1);
     	$jy = $this->getType(2);
		$time = "";
     	if($date == 'today')
     	{
     		$today = strtotime(date("Y-m-d",time()));
     		$time .= ' and time >='. $today;
     	}
     	else if($date == 'month')
     	{
     		$month = strtotime(date("Y-m-01",time()));
     		$time .= ' and time >='. $month;
     	}else if($date == 'all')
		{
		}
     	
     	$sql="SELECT COUNT(id) FROM $table_question WHERE $whereQtype pid = 0 AND revocation = 0 AND cid in ($zx,$jy) $time ";
     	//$rt= $this->db->result_first($sql);
     	$cache_key = md5($sql);
     	$cache_data = $this->cache->get($cache_key);
     	if(false !== $cache_data) return $cache_data;
     	$rt= $this->pdo->getOne($sql);
     	if(!empty($rt)){
     		$this->cache->set($cache_key,$rt,15);//写入缓存，缓存时间为15秒
     	}
     	return  $rt;
     }
     // 首页最新问题列表
     function front_hot_txquestion(){
     	$sql = 'SELECT
				  id,title,time,view,status,atime
				FROM ' . DB_TABLEPRE . "complain  ORDER BY time DESC LIMIT 6";
     	$cache_key = md5($sql);
     	$cache_data = $this->cache->get($cache_key);
     	if(false !== $cache_data) return $cache_data;//如果存在缓存，则读取缓存

     	$rt = $this->db->fetch_all($sql);
     	foreach($rt as $k=>$v){
     		$rt[$k]['time']  = date("Y-m-d H:i", $v['time']);
     		$rt[$k]['atime'] = !empty($v['atime']) ? date("Y-m-d H:i", $v['atime']) : '-';
     		$rt[$k]['view'] = intval($v['view']);
     	}

     	if(!empty($rt)){
     		$this->cache->set($cache_key,$rt,8);//写入缓存，缓存时间为8秒
     	}
     	return  $rt;
     }

     //首页最新问题列表（合并咨询和投诉并分页） added by chouto
     function front_hot_newquestion($qtype=0,$start=0, $limit=20,$answered=0,$date)
     {
     	$table_question = $this->base->getDbTable($this->table_question);
     	$table_complain = $this->base->getDbTable($this->table_complain);
     	$zx = $this->getType(1);
     	$jy = $this->getType(2);
		$ts = $this->getType(3);
     	$whereQtype = $qtype? " qtype = $qtype and ":" qtype >0 and ";
     	
     	if($date == 'today')
     	{
     		$today = strtotime(date("Y-m-d",time()));
     		$time .= ' and time >='. $today;
     	}
     	else if($date == 'month')
     	{
     		$month = strtotime(date("Y-m-01",time()));
     		$time .= ' and time >='. $month;
     	}else if($date == 'all')
		{
		}
     	if($date=="")
     	{
     		$start_time = strtotime(date("Y-m-d H:00:00",time()))-259200;
     		$time = "AND TIME>=$start_time" ;
     	}
     	
		$start_time = strtotime(date("Y-m-d H:00:00",time()))-259200;
     	if($answered!=0)
		{
			$sql="SELECT * FROM (SELECT  id,title,cid,time,views,status,q_handle_status,atime AS Atime,qtype,description
					FROM $table_question WHERE  $whereQtype pid = 0 AND revocation = 0 AND status in (2,3) and cid in ($zx,$jy) $time and id in (select qid from ask_answer) and hidden = 1  
					UNION ALL
					SELECT id,title,".$ts.",time,view,status,time,atime AS Atime,qtype,description  FROM $table_complain  WHERE $whereQtype public =0  AND status in (1,3) $time and id in (select qid from ask_complain_answer))AS a
					ORDER BY TIME DESC LIMIT $start,$limit";
		}
		else
		{
			$sql="SELECT * FROM (SELECT  id,title,cid,time,views,status,q_handle_status,atime AS Atime,qtype,description
					FROM $table_question WHERE  $whereQtype pid = 0 AND revocation = 0 AND cid in ($zx,$jy) $time and hidden = 1 
					UNION ALL
					SELECT id,title,".$ts.",time,view,status,time,atime AS Atime,qtype,description  FROM $table_complain  WHERE $whereQtype public = 0 $time)AS a
					ORDER BY TIME DESC LIMIT $start,$limit";		
		}
		$cache_key = md5($sql);
     	$cache_data = $this->cache->get($cache_key);
     	if(false !== $cache_data) return $cache_data;//如果存在缓存，则读取缓存
		$rt = $this->pdo->getAll($sql);
		foreach($rt as $k=>$v)
		{
			$rt[$k]['categoryInfo'] = $_ENV['category']->get($rt[$k]['cid'],"name,question_type");
			$rt[$k]['type'] = '['.$rt[$k]['categoryInfo']['name'].'] ';

     		if($v['time'] == $v['Atime'])
     		{
     			$rt[$k]['Atime'] = '-';
     		}
     		else
     		{
     			$rt[$k]['Atime'] = !empty($v['Atime']) ? $this->base->timeLagToText($v['time'], $v['Atime']) : '-';
     		}
     		$rt[$k]['time']  = $this->base->timeToText($v['time']);
     		$rt[$k]['views'] = intval($v['views']);
     	}
     	if(!empty($rt))
		{
     		$this->cache->set($cache_key,$rt,3);//写入缓存，缓存时间为3秒
     	}
     	return  $rt;
     }
     //计算咨询投诉合并后数据总条数 added by chouto
	function front_newQuestionRowNum($qtype = 0,$date="") 
	{
     	$table_question = $this->base->getDbTable($this->table_question);
     	$table_complain = $this->base->getDbTable($this->table_complain);
		$zx = $this->getType(1);
     	$jy = $this->getType(2);
     	$whereQtype = $qtype? " qtype = $qtype and ":" qtype >0 and ";
     	$time = "";
		if($date == 'today')
		{
			$today = strtotime(date("Y-m-d",time()));
			$time .= ' and time >='. $today;
		}
		else if($date == 'month')
		{
			$month = strtotime(date("Y-m-01",time()));
			$time .= ' and time >='. $month;
		}else if($date == 'all')
		{
		}
		if($date=="")
		{
			$start_time = strtotime(date("Y-m-d H:00:00",time()))-259200;
			$time = "AND TIME>=$start_time" ;
		}
     	$sql="SELECT COUNT(id) FROM (SELECT * FROM (SELECT id,title,cid,TIME,views,STATUS,q_handle_status,atime AS Atime,qtype
				FROM $table_question WHERE $whereQtype pid = 0 AND revocation = 0 AND cid in($zx,$jy) $time UNION ALL
				SELECT id,title,jid,time,view,status,jid,atime AS Atime,qtype FROM $table_complain WHERE $whereQtype public=0 $time)AS a ) AS T";
        $cache_key = md5($sql);
     	$cache_data = $this->cache->get($cache_key);
     	if(false !== $cache_data) return $cache_data;//如果存在缓存，则读取缓存
     	$rt= $this->pdo->getOne($sql);
     	if(!empty($rt))
		{
     		$this->cache->set($cache_key,$rt,3);//写入缓存，缓存时间为3秒
     	}
     	return  $rt;
     }

    function insertQuestion($questionInfo)
    {
		$table_name = $this->base->getDbTable($this->table_question);
		return $this->pdo->insert($table_name,$questionInfo);
    }
    function updateQuestion($id,$questionInfo)
    {
    	foreach($questionInfo as $key => $value)
    	{
    		$txt[$key] = "`".$key."`='".$value."'";
    	}
    	$sql = "update ".DB_TABLEPRE."question set ".implode($txt,",")." where id = ".intval($id);
    	return $this->db->query($sql);
    }
    // 插入一条新数据
    function insertTransformLog($dataArr)
    {
    	$table_name = $this->getDbTable($this->table_transform_log);
    	$result = $this->pdo->insert($table_name,$dataArr);
    	return $result;
    }
    // 修改8大类问题数量
    function  modifyUserQtypeNum($date,$qtype,$questionType,$count)
    {
    	$date = strtotime($date)>0? $date:date("Y-m-d");
    	$qtypeInfo = $_ENV['qtype']->GetQType($qtype);
    	$logName = TIPASK_ROOT.'/data/logs/modifyUserQtypeNum'.date("Y-m-d").'.txt';
    	
    	if(isset($qtypeInfo['id']))
    	{
    		if($qtypeInfo['pid']>0)
    		{
    			$this->db->begin();
    			
    			if($count>0)
    			{
    				$updateQtypeSql = "INSERT INTO ask_question_num (date,qtype,question_type,questions)
    				VALUES ('{$date}',$qtype,'{$questionType}',$count) ON DUPLICATE KEY UPDATE questions=questions+$count";
    				
    				$updateParentQtypeSql = "INSERT INTO ask_question_num (date,qtype,question_type,questions)
    				VALUES ('{$date}',{$qtypeInfo['pid']},'{$questionType}',$count) ON DUPLICATE KEY UPDATE questions=questions+$count";
    			}
    			else
    			{
    				$updateQtypeSql = "update ask_question_num set questions=questions+($count) where date='$date' and question_type='$questionType' and qtype=$qtype";
    				$updateParentQtypeSql = "update ask_question_num set questions=questions+($count) where date='$date' and question_type='$questionType' and qtype={$qtypeInfo['pid']}";
    			}
    			
    			$this->db->query($updateQtypeSql);
    			$qtypeNum = $this->db->affected_rows(); 
    			
    			$this->db->query($updateParentQtypeSql);
    			$parentQtypeNum = $this->db->affected_rows(); 
    			    			
    			if($qtypeNum>0 && $parentQtypeNum>0)
    			{
    				$this->db->commit();
    				return true;
    			}
    			else
    			{
    				$this->db->rollBack();
    				return false;
    			}
    		}
    		else
    		{
    			if($count>0)
    			{
    				$sql = "INSERT INTO ask_question_num (date,qtype,question_type,questions)
    				VALUES ('{$date}',$qtype,'{$questionType}',$count) ON DUPLICATE KEY UPDATE questions=questions+$count";
    			}
    			else
    			{
    				$sql = "update ask_question_num set questions=questions+($count) where date='".$date."' and question_type='".$questionType."' and qtype =".$qtype;
    			}
    			
    			$result = $this->db->query($sql);
    			
    			return $result;
    		}
    	}
    	else
    	{
    		return false;
    	}
    }
    /** 专属客服条件
     * @author 接手客服
     * @status 问题状态
     * @cid 问题分类
     */
    function front_selfAuthor_where($author,$time,$status=-1,$cid="",$loginName='')
    {
    	$where = '';
    	if(empty($author))
    	{
    	}
    	else
    	{
    		$now = time();
    		$today = strtotime(date("Y-m-d",$now));
    		$where .= " AND js_kf='$author'";
    		if($time == 1) {
    			$where .= " AND time>=" . ($today-604800); //1周
    		} elseif($time == 2) {
    			$where .= " AND time>=" . ($today-2592000); //1月
    		} elseif($time == 3) {
    			$where .= " AND time>=" . ($today-7776000); //3月
    		}
    
    		$status == 1  && $where .= " AND status !=1";
    		$status == 0  && $where .= " AND status  =1";
    		$cid != "" && $where .= " AND cid !=$cid";
    		$loginName != '' && $where .= " AND author ='$loginName'";
    	}
    	return $where;
    }
    /**
     * 专属客服历史回复数量
     */
    function front_mySelfAuthorNum($where)
    {
    	$table_name = $this->base->getDbTable($this->table_question);
    	$sql = "SELECT count(id) FROM $table_name WHERE revocation=0 $where";
    	$cache_key = md5($sql);
    	$cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return $cache_data;	//如果存在缓存，则读取缓存
    	$rt= $this->pdo->getOne($sql);
    	if(!empty($rt))
    	{
    		$this->cache->set($cache_key,$rt,30);//写入缓存，缓存时间为30秒
    	}
    	return  $rt;
    }
    /**
     * 专属客服历史回复列表
     */
    function front_mySelfAuthorList($where,$start=0, $limit=20)
    {
    	$table_question = $this->base->getDbTable($this->table_question);

    	$sql = "SELECT  id,cid,time,views,status,q_handle_status,qtype,comment,atime AS Atime,description,pid
				FROM $table_question WHERE revocation = 0 $where ORDER BY time DESC LIMIT $start,$limit";
    	$cache_key = md5($sql);
    	$cache_data = $this->cache->get($cache_key);
    	if(false !== $cache_data) return $cache_data;	//如果存在缓存，则读取缓存
    	$rt = $this->pdo->getAll($sql);
    	if(!empty($rt))
    	{
    		$this->cache->set($cache_key,$rt,30);//写入缓存，缓存时间为30秒
    	}
    	return  $rt;
    }
    //将一个未分配的问题分配给指定客服
    //$qid：问题ID
    //$operator：客服账号
    function ApplyToOperator($qid,$operator,$log_type = 15)
    {
		$this->base->sys_admin_log($qid,$operator,"尝试分单于{$operator}",$log_type);
		$ctype_ask = $_ENV['category']->getTypeDB(1);
		$ctype_suggest = $_ENV['category']->getTypeDB(2);
		//获取问题
        $question_info = $this->Get($qid);
		//问题存在
        if($question_info['id'])
        {
            $this->load('post');
			//事务开启
            $this->pdo->begin();
    		//更新问题为已分配
			$table_question = $this->base->getDbTable($this->table_question);
    		$apply_sql =  "UPDATE $table_question SET is_hawb=1,js_kf='".$operator."',receive_time='".time()."' WHERE id='".$qid."' and is_hawb = 0 and revocation = 0 and js_kf = '' and help_status = 0 and cid in (0,$ctype_ask,$ctype_suggest) limit 1" ;
			//echo $apply_sql."<br>";
			$apply = $this->pdo->query($apply_sql);
			//如果更新成功
    		if($apply)
    		{
        		//检查客服是否存在或在班
        		$o = $_ENV['operator']->getByColumn('login_name',$operator);
				//echo "operator:"."<br>";
				//print_R($o);
				//如果客服不存在或不在班
        		if(($o['login_name']!='')&&($o['isbusy']==0)&&($o['ishandle']==1)&&($o['isonjob']==1))
        		{
					//获取分单数量限制
                    $limit = $_ENV['post']->get($o['pid']);
        		    //检查客服已分配单量
        		    $num_arr = $_ENV['operator']->getAuthorNum($operator);                    
					//echo "num:"."<br>";
					//print_R($num_arr);
					//首问
                    if($question_info['pid']==0)
                    {
                        //首问单量小于首问最大单量
                        if(intval($num_arr['num'])<$limit['question_limit'])
                        {
        		            //更新首问数量
							$update = $_ENV['operator']->updateAuthorNum($operator,time());
						}
            		    else
            		    {
            		        //单量不足，回滚
            		        $this->pdo->rollBack();
            		        return false;
            		    }
                    }
                    //追问
                    else
                    {
                        //追问单量小于追问最大单量
                        if(intval($num_arr['num_add'])<$limit['question_limit_add'])
                        {
        		            //更新追问数量
							$update = $_ENV['operator']->updateAuthorNum($operator,time(),1);
						}
            		    else
            		    {
            		        //单量不足，回滚
            		        $this->pdo->rollBack();
            		        return false;
            		    }
                    }
                    //更新单量
		            if($update)
		            {
		                //更新成功，提交
		                $this->pdo->commit();
		                // 写入日志
		                $this->base->sys_admin_log($qid,$o['login_name'],"成功分单于{$o['login_name']}",$log_type);
						return true;
		            }
		            else
		            {
                        // 更新失败，回滚
                        $this->pdo->rollBack();
                        return false;
                    }
        		}
        		else
        		{
                    //客服不在班或不存在
                    $this->pdo->rollBack();
                    return false;
                }
            }
            else
            {
                //单子不存在或已被分掉
                $this->pdo->rollBack();
                return false;
            }
        }
        else
        {
            //无此问题
            return false;
        }
    }
    //获取用户的服务记录
    //$author：用户账号
	function myServiceLog($author,$question_type,$ask_time,$ask_status,$Page,$pagesize)
	{
		$z_cid  = $this->getType(1);
		$j_cid  = $this->getType(2);
		$t_cid  = $this->getType(3);
		$l_cid  = $this->getType(4);
		// 获取专属客服条件
		if($author == '游客')
		{
			$selfAuthor_where = '';
		}
		else
		{
			$operatorInfo = $_ENV['operator']->getMySelfAuthor($author);
			if($question_type=="selfAuthor")
			{
				$selfAuthor_where = $this->front_selfAuthor_where($operatorInfo['login_name'],$ask_time,$ask_status,$t_cid,$author);
			}
			else
			{
				$selfAuthor_where = $this->front_selfAuthor_where($operatorInfo['login_name'],0,-1,$t_cid,$author);
			}
		}
		if($question_type=="complain")
		{
			$t_where = $_ENV['complain']->front_myComWhere($author,$ask_time,$ask_status,'0,2');
		}
		else
		{
			$t_where = $_ENV['complain']->front_myComWhere($author,0,-1,'0,2');
		}
		if($question_type=="ask")
		{
			$z_where = $this->front_myQueWhere($author,'zx',$ask_time,$ask_status,$z_cid);
		}
		else
		{
			$z_where = $this->front_myQueWhere($author,'zx','',-1,$z_cid);
		}
		if($question_type=="suggest")
		{
			$j_where = $this->front_myQueWhere($author,'jy',$ask_time,$ask_status,$j_cid);
		}
		else
		{
			$j_where = $this->front_myQueWhere($author,'jy','',-1,$j_cid);
		}
		$l_where = $this->front_myDustbinWhere($author,$ask_time,$l_cid);
		$ServiceLog['logCount']['ask'] = $z_where ? $this->front_myQueNum($z_where) : '0';
		$ServiceLog['logCount']['suggest'] = $j_where ? $this->front_myQueNum($j_where) : '0';
		$ServiceLog['logCount']['complain'] = $t_where ? $_ENV['complain']->front_myComNum($t_where) : '0';
		$ServiceLog['logCount']['dustbin'] = $l_where ? $this->front_myDustbinNum($l_where) : '0';
		$ServiceLog['logCount']['selfAuthor'] = $selfAuthor_where ? $this->front_myQueNum($selfAuthor_where) : '0';
		switch($question_type)
		{
			case "ask":
				if($z_where)
				{
					$totalPage  = @ceil($ServiceLog['logCount']['ask'] / $pagesize);
					$totalPage = $Page > $totalPage?$totalPage:$Page;
					$startindex = ($Page - 1) * $pagesize;
					$ServiceLog['question_list'] = $this->front_myQueList($z_where,$startindex, $pagesize);
					$ServiceLog['questr'] = front_page($ServiceLog['logCount']['ask'], $pagesize, $Page, "question/my_ask/$ask_time/$ask_status");
					
				}
				break;
			case "suggest":
				if($j_where)
				{
					$totalPage  = @ceil($ServiceLog['logCount']['suggest'] / $pagesize);
					$totalPage = $Page > $totalPage?$totalPage:$Page;
					$startindex = ($Page - 1) * $pagesize;
					$ServiceLog['question_list'] = $this->front_myQueList($j_where,$startindex, $pagesize);
					$ServiceLog['questr'] = front_page($ServiceLog['logCount']['suggest'], $pagesize, $Page, "question/my_suggest/$ask_time/$ask_status");
					
				}
				break;
			case "complain":
			    if($t_where)
			    {
			    	$totalPage  = @ceil($ServiceLog['logCount']['complain'] / $pagesize);
			    	$totalPage = $Page > $totalPage?$totalPage:$Page;
			    	$startindex = ($Page - 1) * $pagesize;
			    	$ServiceLog['question_list'] = $_ENV['complain']->front_myComList($t_where,$startindex, $pagesize);
			    	$ServiceLog['questr'] = front_page($ServiceLog['logCount']['complain'], $pagesize, $Page, "question/my_complain/$ask_time/$ask_status");			    	
			    }
				break;
			case "dustbin":
				if($l_where)
				{
					$totalPage  = @ceil($ServiceLog['logCount']['dustbin'] / $pagesize);
					$totalPage = $Page > $totalPage?$totalPage:$Page;
					$startindex = ($Page - 1) * $pagesize;
					$ServiceLog['question_list'] = $this->front_myQueList($l_where,$startindex, $pagesize);
					$ServiceLog['questr'] = front_page($ServiceLog['logCount']['dustbin'], $pagesize, $Page, "question/my_dustbin/$ask_time");
				}
				break;
			case "selfAuthor":
				if($selfAuthor_where)
				{
					$totalPage  = @ceil($ServiceLog['logCount']['selfAuthor'] / $pagesize);
					$totalPage = $Page > $totalPage?$totalPage:$Page;
					$startindex = ($Page - 1) * $pagesize;
					$ServiceLog['question_list'] = $this->front_myQueList($selfAuthor_where,$startindex, $pagesize);
					$ServiceLog['questr'] = front_page($ServiceLog['logCount']['selfAuthor'], $pagesize, $Page, "question/my_selfAuthor/$ask_time");
				}
				break;				
		}
		return $ServiceLog;
	}

	/*	投诉转咨询或建议 多次转换
	 *	$complainInfo 投诉信息
	* 	$qid 咨询或建议 问题id
	* 	$transform 插入 ask_transform_log 表数据
	* 	$to_type 转换类型 suggest or ask
	*   $loginId 操作人
	*/
	function transformAskSuggest($complainInfo,$qid,$transform,$to_type,$loginId,$reason)
	{	
		$questionInfo = $this->Get($qid);
		if (isset($questionInfo['id']))
		{
			$this->pdo->begin();
			
			// 如果追问要转换的类型和父问题不一致,追问变主问题
			if ($questionInfo['pid']>0)
			{
				$parentQuestionInfo = $this->Get($questionInfo['pid']);
	
				if ($parentQuestionInfo['cid']!=$complainInfo['cid'])
				{
					$updateQuestionNum1 = $this->Update($questionInfo['id'],array('pid'=>0));
				}
				else
				{
					$updateQuestionNum1 = 1;
				}
			}
			else
			{
				$updateQuestionNum1 = 1;
			}
	
			$complainComment = unserialize($complainInfo['comment']);
			$complainComment['convert'] = array(
					'to_type'=>$to_type,
					'to_id'=>$qid,
					'transformTime'=>$_SERVER['REQUEST_TIME'],
					'loginId'=>$loginId,
					'reason'=>$reason
				);
			// 隐藏该投诉问题,更新comment字段
			$updateComplainInfo = array('comment'=>serialize($complainComment),'public'=>1,'sync'=>1);
			$updateComplainNum = $_ENV['complain']->Update($complainInfo['id'],$updateComplainInfo);
			 
			$questionInfo['comment']['order_id'] = $complainInfo['order_id'];
			$questionComment = unserialize($questionInfo['comment']);
			$questionComment['convert'] = array('from_type'=>'complain','from_id'=>$complainInfo['id']);
			$dataArr = array(
					'cid'=>$complainInfo['cid'],
					'cid1'=>0,'cid2'=>0,'cid3'=>0,'cid4'=>0,
					'status'=>1,
					'display_h'=>0,
					'qtype'=>$complainInfo['qtype'],
					'comment'=>serialize($questionComment),
					'is_hawb'=>0,'js_kf'=>'','receive_time'=>0
				);
			$updateQuestionNum2 = $this->Update($qid,$dataArr); // 显示转后问题,更新comment字段
			
			$TransformLogId = $this->insertTransformLog($transform); // 记录转换日志
			$_ENV['help']->Update($qid,array('display'=>0)); // 显示协助处理单
	
			if ($updateComplainNum>0&&$updateQuestionNum2>0&&$TransformLogId>0&&$updateQuestionNum1>0)
			{
				$table_name = $this->base->getDbTable($this->table_answer);
				$this->pdo->delete($table_name, '`qid` = ?', $qid);
				$this->pdo->commit();
				// 该问题的接手客服在不忙碌就直接分给他，否则重新分单
				$result = $this->ApplyToOperator($qid,$questionInfo['js_kf']);
				//增加一条记录到搜索服务器
				$q_search['id'] = $questionInfo['id'];
				$q_search['title'] = $questionInfo['description'];
				$q_search['description'] = $questionInfo['description'];
				$q_search['tag'] = json_encode(array(),true);
				$q_search['time'] = $questionInfo['time'];
				$q_search['atime'] = 0;
				try{
					$this->set_search($q_search);
					// 从搜索服务器上该删除投诉记录
					$this->delete_search('c_'.$complainInfo['id']);
				}catch(Exception $e){
					send_AIC('http://sc.5173.com/model/question.class.php/transformAskSuggest','投诉转咨询建议搜索服务器添加失败',1,'搜索接口');
				}					
				return true;				
			}
			else
			{
				$this->pdo->rollBack();
				return false;
			}
		}
		else
		{
			return false; // 问题不存在
		}
	}
	/**
	 * 该问题的接手客服在不忙碌就直接分给他,否则重新分单
	 * @param  $qid
	 * @return boolean
	 */
	function aginMenu($qid)
	{
		$question_info = $this->Get($qid);
		$table_operator = $this->base->getDbTable($this->table_operator);
		$where = "isbusy=0 AND ishandle=1 AND isonjob=1 AND login_name='{$question_info['js_kf']}'";
		$operatorId = $this->pdo->selectRow($table_operator,'id',$where);
		// 该接手问题 在线非忙碌，直接分给他
		if (isset($operatorId['id']))
		{
			$table_author_num = $this->base->getDbTable($this->table_author_num);
			$updateQuestionNum = $this->Update($question_info['id'],array('status'=>1,'display_h'=>0,'receive_time'=>$_SERVER['REQUEST_TIME']));
			//加单量
			if ($question_info['pid']==0)
			{
				$reduce_num_sql = "UPDATE $table_author_num SET num = num + 1 WHERE author ='".$question_info['js_kf']."' limit 1" ;
			}
			else
			{
				$reduce_num_sql = "UPDATE $table_author_num SET num_add = num_add + 1 WHERE author ='".$question_info['js_kf']."' limit 1" ;
			}
				
			$updateAuthorNum = $this->pdo->query($reduce_num_sql);
				
			if ($updateAuthorNum>0)
			{
				$this->pdo->commit(); //事务成功，提交
				return true;
			}
			else
			{
				$this->pdo->rollBack(); //事务失败，回滚
				return false;
			}
		}
		else // 重新分单
		{
			$dataArr = array('is_hawb'=>0,'js_kf'=>'','receive_time'=>0,'status'=>1);
			$this->Update($question_info['id'],$dataArr);
			return true;
		}
	}
	// 插入保险订单
	function insertBaoXianLog($dataArr)
	{
		$table_name = $this->getDbTable($this->table_baoXianLog);
		$result = $this->pdo->replace($table_name,$dataArr);
		return $result;
	}
	// 插入保险订单,有更新commited=1
	function insertUpdateBaoXianLog($dataArr)
	{
		$table_name = $this->getDbTable($this->table_baoXianLog);
		$result = $this->pdo->insert_update($table_name,$dataArr,array('committed'=>-1));
		return $result;
	}
	// 查询改订单是否已经提交过
	function getBaoXianOrder($dataArr,$fields='*')
	{
		$table_name = $this->base->getDbTable($this->table_baoXianLog);
		$baoXianLog = $this->pdo->selectRow($table_name, $fields, '`author`=? AND `orderid`= ? AND `qtype`=? AND committed=1', $dataArr);
		return $baoXianLog;
	}
	/**
	 * 保险站点 回调接口
	 */
	function getBaoXianOrderPost($post)
	{
		$qtype = intval($post['qtype']);
		$author = trim($post['author']);
		$orderId = trim($post['orderid']);
		$qtypeInfo = $_ENV['qtype']->GetQType($qtype);
		if(!isset($qtypeInfo['id']))
		{
			return 2; // qtype 不存在
		}
		else
		{
			$baoXianLog = $this->getBaoXianOrder(array($author,$orderId,$qtype));
			if(isset($baoXianLog['qtype']))
			{
				return 1;	// 已经提交过,成功
			}
			else
			{
				$dataArr = array(
						'author'=>addslashes($author),
						'orderid'=>addslashes($orderId),
						'qtype'=>$qtype,
						'committed'=>1,
						'time'=>$_SERVER['REQUEST_TIME']
				);
				$result = $this->insertUpdateBaoXianLog($dataArr);
				if($result)
				{
					$date = date("Y-m-d");
					$rt = $this->modifyUserQtypeNum($date,$qtype,'complain',1);
					if($rt)
					{
						return 1; // 成功
					}
					else
					{
						return 0; // 失败
					}
				}
				else
				{
					return 0; // 失败
				}
			}
		}
	}
    //获取问题内容
    function GetUpdatedAnswer()
    {
    	$table_name = $this->base->getDbTable($this->table_answer);		
		$sql = "select * from $table_name where Comment like '%answer_update_log%'";
		$AnswerList = $this->pdo->getAll($sql);
    	return $AnswerList;
    }
	
	function restoreAnswerTime($id,$qid,$time)
	{
		if($time<=0)
		{
			return 0;
		}
		else
		{
			$this->pdo->begin();
			$table_answer = $this->base->getDbTable($this->table_answer);	
			$table_question = $this->base->getDbTable($this->table_question);
			$answer = $this->pdo->update($table_answer,array('time'=>$time), '`id` = ?', $id);
			$question = $this->pdo->update($table_question,array('atime'=>$time), '`id` = ?', $qid);
			if($answer && $question)
			{
				$this->pdo->commit();
				return 1;
				
			}
			else
			{
				$this->pdo->rollback();
				return 0;
			}
			
		}
	}
    //根据历史记录表获取表内ID范围更新到映射表内
    function UpdateHistoryMap($year)
    {
		$this->pdo_h = $this->base->init_pdo($this->table_question_h."_".$year);			
		$sql_fleids = array('min_id'=>'min(id)','max_id'=>'max(id)');
		$fields = $this->pdo_h->getSqlFields($sql_fleids);
		$table_name = $this->base->getDbTable($this->table_question_h."_".$year);
		$sql = "SELECT $fields from $table_name";
		$mapping = $this->pdo_h->getRow($sql);
		if($mapping['min_id']>=0 and $mapping['max_id']>=$mapping['min_id'])
		{
			$table_name = $this->base->getDbTable($this->table_h_map);
			$dataArr = array('year'=>$year,'min'=>$mapping['min_id'],'max'=>$mapping['max_id'],'question_type'=>'ask');
			return $this->pdo->replace($table_name,$dataArr);
		}
		else
		{
			return false;
		}
    }
    //根据历史记录表获取表内ID范围更新到映射表内
    function UpdateCurrentMap()
    {
		$sql_fleids = array('min_id'=>'min(id)');
		$fields = $this->pdo->getSqlFields($sql_fleids);
		$table_name = $this->base->getDbTable($this->table_question);
		$sql = "SELECT $fields from $table_name";
		$mapping = $this->pdo->getRow($sql);
		if($mapping['min_id']>=0)
		{
			$table_name = $this->base->getDbTable($this->table_h_map);
			$dataArr = array('year'=>0,'min'=>$mapping['min_id'],'question_type'=>'ask');
			return $this->pdo->replace($table_name,$dataArr);
		}
		else
		{
			return false;
		}
    }
	function getData($year)
	{
		$dataArr = array();
		$this->pdo_h = $this->base->init_pdo($this->table_question_h."_".$year);
		$table_name = $this->base->getDbTable($this->table_question_h."_".$year);
		$StartTime = strtotime($year."-01-01");
		$EndTime = strtotime(($year+1)."-01-01");
		$z_cid  = $this->getType(1);
		for($i=0;$i<=54;$i=$i+4)
		{
			$startM = microtime(true);
			$start = $i;
			$end = $start+3;
			$sql = 'SELECT week( from_unixtime( time, "%Y-%m-%d" ) ) AS week, year( from_unixtime( time, "%Y-%m-%d" ) ) AS year, count( * ) AS c, sum( if( q_handle_status =1, 1, 0 ) ) AS handled, sum( if( is_pj =0, 1, 0 ) ) AS none, sum( if( is_pj =1, 1, 0 ) ) AS y, sum( if( is_pj =2, 1, 0 ) ) AS n, cid, cid1,cid2,cid3,cid4
	FROM '.$table_name.'
	WHERE time >= '.$StartTime.' and time < '.$EndTime.' and cid = '.$z_cid.' and week( from_unixtime( time, "%Y-%m-%d" ) ) >= '.$start.' and week( from_unixtime( time, "%Y-%m-%d" ) ) <= '.$end.' GROUP BY week, year, cid, cid1,cid2,cid3,cid4';
			$data = $this->pdo_h->getAll($sql);
			$dataArr = $this->data_process($data,$dataArr);
		}
		
		for($i=0;$i<=54;$i=$i+4)
		{
			$startM = microtime(true);
			$start = $i;
			$end = $start+3;
			$table_name = $this->base->getDbTable($this->table_question);
			$sql = 'SELECT week( from_unixtime( time, "%Y-%m-%d" ) ) AS week, year( from_unixtime( time, "%Y-%m-%d" ) ) AS year, count( * ) AS c, sum( if( q_handle_status =1, 1, 0 ) ) AS handled, sum( if( is_pj =0, 1, 0 ) ) AS none, sum( if( is_pj =1, 1, 0 ) ) AS y, sum( if( is_pj =2, 1, 0 ) ) AS n, cid, cid1,cid2,cid3,cid4
	FROM '.$table_name.'
	WHERE time >= '.$StartTime.' and time < '.$EndTime.' and cid = '.$z_cid.' and week( from_unixtime( time, "%Y-%m-%d" ) ) >= '.$start.' and week( from_unixtime( time, "%Y-%m-%d" ) ) <= '.$end.' GROUP BY week, year, cid, cid1,cid2,cid3,cid4';
			$data = $this->pdo->getAll($sql);
			$dataArr = $this->data_process($data,$dataArr);
		}
		return $dataArr;
	}
	public function data_process($data,$dataArr)
	{
		for($i=0;$i<=54;$i++)
		{
			$t[$i] = 0;
		}
		foreach($data as $key => $value)
		{
			if(!isset($dataArr[$value['cid']]))
			{
				$c_info = $_ENV['category']->get($value['cid']);
				$dataArr[$value['cid']] = array('name' => $c_info['name'],'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
			}
			$dataArr[$value['cid']]['total_count'][$value['week']] += $value['c'];
			$dataArr[$value['cid']]['handle_count'][$value['week']] += $value['handled'];
			$dataArr[$value['cid']]['yes'][$value['week']] += $value['y'];
			$dataArr[$value['cid']]['no'][$value['week']] += $value['n'];
			$dataArr[$value['cid']]['none'][$value['week']] += $value['none'];

			if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]))
			{
				$c_sub1_info = $_ENV['category']->get($value['cid1']);
				$dataArr[$value['cid']]['sub'][$value['cid1']] = array('name' => $dataArr[$value['cid']]['name']."-".($c_sub1_info['name']?$c_sub1_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
			}
			$dataArr[$value['cid']]['sub'][$value['cid1']]['total_count'][$value['week']] += $value['c'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['handle_count'][$value['week']] += $value['handled'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['yes'][$value['week']] += $value['y'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['no'][$value['week']] += $value['n'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['none'][$value['week']] += $value['none'];
			
			if($value['cid2']>0)
			{
				if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]))
				{
					$c_sub2_info = $_ENV['category']->get($value['cid2']);
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']] = array('name' => $dataArr[$value['cid']]['sub'][$value['cid1']]['name']."-".($c_sub2_info['name']?$c_sub2_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
				}
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['total_count'][$value['week']] += $value['c'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['handle_count'][$value['week']] += $value['handled'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['yes'][$value['week']] += $value['y'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['no'][$value['week']] += $value['n'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['none'][$value['week']] += $value['none'];
				if($value['cid3']>0)
				{
					if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]))
					{
						$c_sub3_info = $_ENV['category']->get($value['cid3']);
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']] = array('name' => $dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['name']."-".($c_sub3_info['name']?$c_sub3_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
					}
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['total_count'][$value['week']] += $value['c'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['handle_count'][$value['week']] += $value['handled'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['yes'][$value['week']] += $value['y'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['no'][$value['week']] += $value['n'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['none'][$value['week']] += $value['none'];
					if($value['cid4']>0)
					{
						if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]))
						{
							$c_sub4_info = $_ENV['category']->get($value['cid4']);
							$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']] = array('name' => $dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['name']."-".($c_sub4_info['name']?$c_sub4_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t);
						}
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['total_count'][$value['week']] += $value['c'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['handle_count'][$value['week']] += $value['handled'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['yes'][$value['week']] += $value['y'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['no'][$value['week']] += $value['n'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['none'][$value['week']] += $value['none'];
					}
				}
			}
		

		}
		return $dataArr;
	}
	function getDataMonth($year)
	{
		$dataArr = array();
		$this->pdo_h = $this->base->init_pdo($this->table_question_h."_".$year);
		$table_name = $this->base->getDbTable($this->table_question_h."_".$year);
		$StartTime = strtotime($year."-01-01");
		$EndTime = strtotime(($year+1)."-01-01");
		$z_cid  = $this->getType(1);
		$sql = 'SELECT month( from_unixtime( time, "%Y-%m-%d" ) ) AS week, year( from_unixtime( time, "%Y-%m-%d" ) ) AS year, count( * ) AS c, sum( if( q_handle_status =1, 1, 0 ) ) AS handled, sum( if( is_pj =0, 1, 0 ) ) AS none, sum( if( is_pj =1, 1, 0 ) ) AS y, sum( if( is_pj =2, 1, 0 ) ) AS n, cid, cid1,cid2,cid3,cid4
FROM '.$table_name.'
WHERE time >= '.$StartTime.' and time < '.$EndTime.' and cid = '.$z_cid.' GROUP BY week, year, cid, cid1,cid2,cid3,cid4';
		$data = $this->pdo_h->getAll($sql);
		$dataArr = $this->data_process_month($data,$dataArr);
		$table_name = $this->base->getDbTable($this->table_question);
		$sql = 'SELECT month( from_unixtime( time, "%Y-%m-%d" ) ) AS week, year( from_unixtime( time, "%Y-%m-%d" ) ) AS year, count( * ) AS c, sum( if( q_handle_status =1, 1, 0 ) ) AS handled, sum( if( is_pj =0, 1, 0 ) ) AS none, sum( if( is_pj =1, 1, 0 ) ) AS y, sum( if( is_pj =2, 1, 0 ) ) AS n, cid, cid1,cid2,cid3,cid4
FROM '.$table_name.'
WHERE time >= '.$StartTime.' and time < '.$EndTime.' and cid = '.$z_cid.' GROUP BY week, year, cid, cid1,cid2,cid3,cid4';
		$data = $this->pdo->getAll($sql);
		$dataArr = $this->data_process_month($data,$dataArr);
		return $dataArr;
	}
	public function data_process_month($data,$dataArr)
	{
		for($i=1;$i<=12;$i++)
		{
			$t[$i] = 0;
		}
		foreach($data as $key => $value)
		{
			if(!isset($dataArr[$value['cid']]))
			{
				$c_info = $_ENV['category']->get($value['cid']);
				$dataArr[$value['cid']] = array('name' => $c_info['name'],'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
			}
			$dataArr[$value['cid']]['total_count'][$value['week']] += $value['c'];
			$dataArr[$value['cid']]['handle_count'][$value['week']] += $value['handled'];
			$dataArr[$value['cid']]['yes'][$value['week']] += $value['y'];
			$dataArr[$value['cid']]['no'][$value['week']] += $value['n'];
			$dataArr[$value['cid']]['none'][$value['week']] += $value['none'];

			if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]))
			{
				$c_sub1_info = $_ENV['category']->get($value['cid1']);
				$dataArr[$value['cid']]['sub'][$value['cid1']] = array('name' => $dataArr[$value['cid']]['name']."-".($c_sub1_info['name']?$c_sub1_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
			}
			$dataArr[$value['cid']]['sub'][$value['cid1']]['total_count'][$value['week']] += $value['c'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['handle_count'][$value['week']] += $value['handled'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['yes'][$value['week']] += $value['y'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['no'][$value['week']] += $value['n'];
			$dataArr[$value['cid']]['sub'][$value['cid1']]['none'][$value['week']] += $value['none'];
			
			if($value['cid2']>0)
			{
				if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]))
				{
					$c_sub2_info = $_ENV['category']->get($value['cid2']);
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']] = array('name' => $dataArr[$value['cid']]['sub'][$value['cid1']]['name']."-".($c_sub2_info['name']?$c_sub2_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
				}
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['total_count'][$value['week']] += $value['c'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['handle_count'][$value['week']] += $value['handled'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['yes'][$value['week']] += $value['y'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['no'][$value['week']] += $value['n'];
				$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['none'][$value['week']] += $value['none'];
				if($value['cid3']>0)
				{
					if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]))
					{
						$c_sub3_info = $_ENV['category']->get($value['cid3']);
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']] = array('name' => $dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['name']."-".($c_sub3_info['name']?$c_sub3_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t,'sub'=>array());
					}
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['total_count'][$value['week']] += $value['c'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['handle_count'][$value['week']] += $value['handled'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['yes'][$value['week']] += $value['y'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['no'][$value['week']] += $value['n'];
					$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['none'][$value['week']] += $value['none'];
					if($value['cid4']>0)
					{
						if(!isset($dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]))
						{
							$c_sub4_info = $_ENV['category']->get($value['cid4']);
							$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']] = array('name' => $dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['name']."-".($c_sub4_info['name']?$c_sub4_info['name']:"未分类"),'total_count'=>$t,'handle_count'=>$t,'yes'=>$t,'no'=>$t,'none'=>$t);
						}
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['total_count'][$value['week']] += $value['c'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['handle_count'][$value['week']] += $value['handled'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['yes'][$value['week']] += $value['y'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['no'][$value['week']] += $value['n'];
						$dataArr[$value['cid']]['sub'][$value['cid1']]['sub'][$value['cid2']]['sub'][$value['cid3']]['sub'][$value['cid4']]['none'][$value['week']] += $value['none'];
					}
				}
			}
		

		}
		return $dataArr;
	}
	//根据问题ID拼接出URL
	public function getQuestionLink($QuestionId,$QuestionType)
	{	
		$this->onlineConfig = require TIPASK_ROOT.'/onlineConfig.php'; // 获取配置文件
		$QuestionUrl = $this->onlineConfig['ScUrl']."/detail.aspx?QuestionId=".$QuestionId."&QuestionType=".$QuestionType;
		return $QuestionUrl;
	}
	public function rebuildQuestionDetail($QuestionId,$QuestionType)
	{
		$arr = array('QuestionId'=>intval($QuestionId),'QuestionType'=>trim($QuestionType),'Time'=>time());
		$sign = $this->check_sign($arr,'5173');
		$this->onlineConfig = require TIPASK_ROOT.'/onlineConfig.php'; // 获取配置文件		
		$url = $this->onlineConfig['ScappUrl'].'/?ctl=question&ac=rebuild.question.detail&';
		$arr2 = array();
		foreach($arr as $key => $value)
		{
			if((strlen(trim($value))==0)||(($value==0)&&(is_numeric($value))))
			{
				unset($arr[$key]);	
			}
			else
			{
				$arr2[] = $key."=".urlencode($value);
			}
		}
		$url2 = implode("&",$arr2);
		$url.=$url2."&sign=".$sign;
		$return = (file_get_contents($url));
		define('TIPASK_ROOT', substr(dirname(__FILE__), 0, -4));
	}
	public function PageView($PageId,$IP)
	{
		$arr = array('PageId'=>intval($PageId),'ViewIP'=>$IP,'Time'=>time());
		$sign = $this->check_sign($arr,'5173');
		$this->onlineConfig = require TIPASK_ROOT.'/onlineConfig.php'; // 获取配置文件		
		$url = $this->onlineConfig['ScappUrl'].'/?ctl=view&ac=page.view&';
		$arr2 = array();
		foreach($arr as $key => $value)
		{
			if((strlen(trim($value))==0)||(($value==0)&&(is_numeric($value))))
			{
				unset($arr[$key]);	
			}
			else
			{
				$arr2[] = $key."=".urlencode($value);
			}
		}
		$url2 = implode("&",$arr2);
		$url.=$url2."&sign=".$sign;
		$return = (file_get_contents($url));
	}
}
?>
