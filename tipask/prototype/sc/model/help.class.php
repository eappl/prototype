<?php
!defined('IN_TIPASK') && exit('Access Denied');

class helpmodel {

    var $db;
    var $base;
    var $table_help = "ask_help";
    function helpmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->pdo = $this->base->init_pdo($this->table_help);
    }
    // 更新协助内容
    function Update($id,$dataArr)
    {
    	$table_name = $this->base->getDbTable($this->table_help);
    	$update = $this->pdo->update($table_name, $dataArr, '`id` = ?', $id);
    	return $update;
    }
    // 获取查看协助处理查询条件
    function getWhere($start_time='',$end_time='',$status=-1,$qid=-1,$department=-1,$aid_id=-1,$overdue=-1,$applicant=''){
    	$now = time();
    	$where       = " where 1 ";
    	$start_time != '' && $end_time != ''  && $where .= "AND h.start <= '$end_time' AND  h.start >= '$start_time'";
    	$status     != -1 && $where .= " AND h.status='$status'";
    	$qid        != -1 && $where .= " AND h.qid='$qid'";
    	$department != -1 && $where .= " AND h.did='$department'";
    	$aid_id     != -1 && $where .= " AND h.aid_id='$aid_id'";
    	$applicant  !=''  && $where .= " AND h.applicant='$applicant'";
    	// 1已逾期 0未逾期
    	if($overdue == 0){
    		$where .= " AND ($now-h.start < hc.time*60)";
    	}else if($overdue == 1){
    		$where .= " AND ($now-h.start > hc.time*60)";
    	}
    	return $where;
    }
    function getNum($where){
    	$sql = 'SELECT
				  count(h.id)
				FROM ' . DB_TABLEPRE . 'help AS h
				  LEFT JOIN ' . DB_TABLEPRE . 'question AS q ON h.qid = q.id
				  LEFT JOIN ' . DB_TABLEPRE . 'hekp_config AS hc ON q.cid1 = hc.cid';
    	return $this->db->result_first($sql .  $where);
    }
    //查看协助处理列表
    function getList($start=0, $limit=20,$where='',$tag=true){
    	$now = time();
    	$sql = 'SELECT
				    h.id,
					h.qid,
					h.applicant,
					h.aid,
					h.status,
					h.start,
					h.did,
					h.aid_content,
					h.back_content,
				    h.back_time,
				    h.aid_id,
    			 	q.js_kf,
				  	hc.time
				FROM ' . DB_TABLEPRE . 'help AS h
				  LEFT JOIN ' . DB_TABLEPRE . 'question AS q ON h.qid = q.id
				  LEFT JOIN ' . DB_TABLEPRE . 'hekp_config AS hc ON q.cid1 = hc.cid';
    	$LIMIT = $tag ? " order by h.id desc  LIMIT $start,$limit" : '';
    	$helplist = $this->db->fetch_all($sql . $where . $LIMIT ,"id");
    	foreach($helplist as $key=>$value){
    		$helplist[$key]['start'] = date("Y-m-d H:i:s",$value['start']);
    		$helplist[$key]['back_time'] = !empty($value['back_time'])?date("Y-m-d H:i:s",$value['back_time']):'';
    		$helplist[$key]['overdue'] = $now - $value['start']-$value['time']*60 < 0 ? 0:1; // 如果help_config表中未配置时间的话 会逾期
    		$helplist[$key]['date'] = $value['start']+$value['time']*60 ; // 协助处理提交时间到配置时间之和
    	}
    	return $helplist;
    }
    function update_helpcontent($id, $back_content, $time){
    	$sql = 'UPDATE ' . DB_TABLEPRE . "help SET status=1,back_content='$back_content',back_time='" . $time ."' WHERE id=$id"; 
    	$this->db->query($sql);
    }    
    
    //获取协助记录
    function getAidRecord($where=''){
    	$now = time();
    	$sql = 'SELECT
				  h.*,
				  q.title
				FROM ' . DB_TABLEPRE . 'help AS h
				  LEFT JOIN ' . DB_TABLEPRE . 'question AS q ON h.qid = q.id';
    	$helplist = $this->db->fetch_all($sql . $where ,"id"); 	
    	return $helplist;
    }
    
    // 查看协助处理倒计时
    function getHelpTime($id){
    	$sql = 'SELECT * FROM ' . DB_TABLEPRE . 'help WHERE id =' . $id ;
    	
    	$helplist = $this->db->fetch_first($sql);
    	$helplist['start'] = date("Y-m-d H:i:s",$helplist['start']);
    	$helplist['back_time'] = !empty($helplist['back_time'])?date("Y-m-d H:i:s",$helplist['back_time']):'';
    	return $helplist;
    }
    // 回复时长
    function get_sorceList($start_time,$end_time,$operator='',$cid=-1,$cid1=-1,$cid2=-1,$cid3=-1,$cid4=-1,$author=''){
     	$t_author = explode("|",trim($author));
     	foreach($t_author as $key => $value)
     	{
     	    $t_author[$key] = trim($value);
     	    if($value=="")
     	    {
     	        unset($t_author[$key]);
     	    }
     	        
     	}
    	$where = "SELECT
    	COUNT(a.id) AS reply_count,
    	a.author,
    	SUM(a.time-q.receive_time) AS rs_time,
    	SUM(CASE WHEN a.time-q.receive_time<=60 THEN 1 ELSE 0 END) AS one,
    	SUM(CASE WHEN a.time-q.receive_time  BETWEEN 61 AND 120 THEN 1 ELSE 0 END) AS two,
    	SUM(CASE WHEN a.time-q.receive_time  BETWEEN 121 AND 180 THEN 1 ELSE 0 END) AS three,
    	SUM(CASE WHEN a.time-q.receive_time  BETWEEN 181 AND 240 THEN 1 ELSE 0 END) AS four,
    	SUM(CASE WHEN a.time-q.receive_time  BETWEEN 241 AND 300 THEN 1 ELSE 0 END) AS five,
    	SUM(CASE WHEN a.time-q.receive_time  BETWEEN 301 AND 600 THEN 1 ELSE 0 END) AS six,
    	SUM(CASE WHEN a.time-q.receive_time > 600 THEN 1 ELSE 0 END) AS seven
    	FROM ask_answer AS a
    	LEFT JOIN ask_question AS q
    	ON q.id = a.qid
    	WHERE a.time <= '$end_time' AND  a.time >= '$start_time'";
    	$operator != '' && $where .= " AND a.author='$operator'";
    	$cid  != -1 && $where .= " AND q.cid='$cid'";
    	$cid1 != -1 && $where .= " AND q.cid1='$cid1'";
    	$cid2 != -1 && $where .= " AND q.cid2='$cid2'";
    	$cid3 != -1 && $where .= " AND q.cid3='$cid3'";
    	$cid4 != -1 && $where .= " AND q.cid4='$cid4'";
        $where .= " AND a.author IN ($author) GROUP BY a.author";
    	$data =  $this->db->fetch_all($where);
    	foreach($data as $k=>$v){
    		$data[$k]['avg_time']    = round($data[$k]['rs_time']/$data[$k]['reply_count']).'秒';
    		$data[$k]['one_rate']    = round($data[$k]['one']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['two_rate']    = round($data[$k]['two']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['three_rate']  = round($data[$k]['three']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['four_rate']   = round($data[$k]['four']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['five_rate']   = round($data[$k]['five']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['six_rate']    = round($data[$k]['six']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['seven_rate']  = round($data[$k]['seven']/$data[$k]['reply_count']*100).'%';
    	}
    	return $data;
    }
    //获取响应时长数据
    function get_rs_list($start_time,$end_time,$operator='',$cid=-1,$cid1=-1,$cid2=-1,$cid3=-1,$cid4=-1,$author=''){
    	$where = "SELECT
	    	COUNT(a.id) AS reply_count,
	    	a.author,
	    	SUM(a.time-q.time) AS rs_time,
	    	SUM(CASE WHEN a.time-q.time<=60 THEN 1 ELSE 0 END) AS one,
	    	SUM(CASE WHEN a.time-q.time  BETWEEN 61 AND 120 THEN 1 ELSE 0 END) AS two,
	    	SUM(CASE WHEN a.time-q.time  BETWEEN 121 AND 180 THEN 1 ELSE 0 END) AS three,
	    	SUM(CASE WHEN a.time-q.time  BETWEEN 181 AND 240 THEN 1 ELSE 0 END) AS four,
	    	SUM(CASE WHEN a.time-q.time  BETWEEN 241 AND 300 THEN 1 ELSE 0 END) AS five,
	    	SUM(CASE WHEN a.time-q.time  BETWEEN 301 AND 600 THEN 1 ELSE 0 END) AS six,
	    	SUM(CASE WHEN a.time-q.time > 600 THEN 1 ELSE 0 END) AS seven
	    	FROM ask_answer AS a
	    	 LEFT JOIN ask_question AS q
	    	  ON q.id = a.qid
    	     WHERE a.time <= '$end_time' AND  a.time >= '$start_time'";
    	$operator != '' && $where .= " ";
    	$cid  != -1 && $where .= " AND q.cid='$cid'";
    	$cid1 != -1 && $where .= " AND q.cid1='$cid1'";
    	$cid2 != -1 && $where .= " AND q.cid2='$cid2'";
    	$cid3 != -1 && $where .= " AND q.cid3='$cid3'";
    	$cid4 != -1 && $where .= " AND q.cid4='$cid4'";
        $where .= " AND a.author IN ($author) GROUP BY a.author";
    	$data =  $this->db->fetch_all($where);
    	foreach($data as $k=>$v){
    		$data[$k]['avg_time']    = round($data[$k]['rs_time']/$data[$k]['reply_count']).'秒';
    		$data[$k]['one_rate']    = round($data[$k]['one']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['two_rate']    = round($data[$k]['two']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['three_rate']  = round($data[$k]['three']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['four_rate']   = round($data[$k]['four']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['five_rate']   = round($data[$k]['five']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['six_rate']    = round($data[$k]['six']/$data[$k]['reply_count']*100).'%';
    		$data[$k]['seven_rate']  = round($data[$k]['seven']/$data[$k]['reply_count']*100).'%';
    	}
    	return $data;
    }
    //协助数据统计条件获取
    function get_kwhere($start,$end_time,$operator='',$did=-1,$orderby=-1,$web_did=''){
     	$t_operator = explode("|",trim($operator));
     	foreach($t_operator as $key => $value)
     	{
     	    $t_operator[$key] = trim($value);
     	    if($value=="")
     	    {
     	        unset($t_operator[$key]);
     	    }
     	    else
     	    {
               $t_operator[$key] = "'".($value)."'"; 
            }     	        
     	}
     	$operator = implode(",",$t_operator);
    	
    	$where = "SELECT  
    			  h.aid,
				  COUNT(h.id) AS jx_count,
				  SUM(h.status) AS replay,
				  SUM(q.q_handle_status) AS handle,
				  SUM(CASE WHEN q.is_pj=1 THEN 1 ELSE 0 END) AS pj_my,
				  SUM(CASE WHEN q.is_pj=2 THEN 1 ELSE 0 END) AS pj_bmy,
				  SUM(CASE WHEN q.is_pj=0 THEN 1 ELSE 0 END) AS wpj
				FROM ask_help AS h
				  LEFT JOIN ask_question AS q
				    ON h.qid = q.id WHERE h.start <= '$end_time' AND  h.start >= '$start'";
    	$operator != '' && $where .= " ".(strlen($operator)>0?"AND h.aid in (".$operator.")":" ");
    	$did != -1 && $where .= " AND h.did='$did'";
    	$web_did != '' && $where .= " AND h.did NOT IN ($web_did)";
    	$where .= ' GROUP BY h.aid';
    	$orderby ==1 && $where .= ' ORDER BY jx_count'; 
    	$orderby ==2 && $where .= ' ORDER BY jx_count DESC';
    	return $where;
    }
    //输出 协助数据
    function get_klist($where){
    	$klist = $this->db->fetch_all($where);
    	foreach($klist as $k=>$v){
    		$satify_sum = $klist[$k]['pj_my'] + $klist[$k]['pj_bmy'];
    		$klist[$k]['handle_rate']  = round($klist[$k]['handle']/$klist[$k]['jx_count']*100).'%';
    		$klist[$k]['satify_rate']  = round($klist[$k]['pj_my']/$satify_sum*100).'%';
    		$klist[$k]['nsatify_rate'] = round($klist[$k]['pj_bmy']/$satify_sum*100).'%';
    		$klist[$k]['assess_rate']  = round($satify_sum/$klist[$k]['handle']*100).'%';
    	}
    	return $klist;
    }
    //协助相应时长统计条件获取
    function get_swhere($start,$end_time,$operator='',$did=-1,$web_did=''){
     	$t_operator = explode("|",trim($operator));
     	foreach($t_operator as $key => $value)
     	{
     	    $t_operator[$key] = trim($value);
     	    if($value=="")
     	    {
     	        unset($t_operator[$key]);
     	    }
     	    else
     	    {
               $t_operator[$key] = "'".($value)."'"; 
            }     	        
     	}
     	$operator = implode(",",$t_operator);
    	$where = "SELECT
				    h.aid,
				    SUM(h.status) AS reply,
				    SUM(h.back_time-h.start) AS reply_time,
				    SUM(CASE WHEN h.back_time-h.start<=600 THEN 1 ELSE 0 END) AS ten_reply,
				    SUM(CASE WHEN h.back_time-h.start  BETWEEN 660 AND 1200 THEN 1 ELSE 0 END) AS twenty_reply,
				    SUM(CASE WHEN h.back_time-h.start  BETWEEN 1260 AND 1800 THEN 1 ELSE 0 END) AS thirty_reply,
				    SUM(CASE WHEN h.back_time-h.start > 1800 THEN 1 ELSE 0 END) AS more_reply
				    FROM ask_help AS h
				    LEFT JOIN ask_question AS q
				    ON h.qid = q.id
    			 WHERE h.start <= '$end_time' AND  h.start >= '$start' ";
    	$operator != '' && $where .= " ".(strlen($operator)>0?"AND h.aid in (".$operator.")":" ");
    	$did != -1 && $where .= " AND h.did='$did'";
    	$web_did != '' && $where   .= " AND h.did NOT IN ($web_did) ";
    	$where .= ' GROUP BY h.aid';
    	return $where;
    }
    //输出协助相应时长数据
    function get_slist($where){
	    $klist = $this->db->fetch_all($where);
	    foreach($klist as $k=>$v){
	        $klist[$k]['avg_time']  = round($klist[$k]['reply_time']/$klist[$k]['reply']).'秒';
		    $klist[$k]['ten_rate']  = round($klist[$k]['ten_reply']/$klist[$k]['reply']*100).'%';
		    $klist[$k]['twenty_rate']  = round($klist[$k]['twenty_reply']/$klist[$k]['reply']*100).'%';
		    $klist[$k]['thirty_rate']  = round($klist[$k]['thirty_reply']/$klist[$k]['reply']*100).'%';
		    $klist[$k]['more_rate']    = round($klist[$k]['more_reply']/$klist[$k]['reply']*100).'%';
	    }
   	 return $klist;
    }
    // 获取在线服务部id
	function get_web_id($pid){
		$p_arr = $pid.',';
     	$rs = $this->db->fetch_all("SELECT id,pid FROM " . DB_TABLEPRE . "department WHERE pid=$pid");
     	if(!empty($rs)){
     		foreach($rs as $k => $v){  			
     			$p_arr .= $this->get_web_id($v['id']);     			
     		}   		
     	}	    	    	
     	return $p_arr;
	}
	/**
	 * 我的协助、我发起的处理条件 $type默认为1记我的协助处理
	 */
	function get_hwhere($start_time='',$end_time='',$status=-1,$qid='',$overdue=-1,$login_name='',$type=1){
		$now = time();
		$where = " where 1 ";
		$start_time != '' && $end_time != ''  && $where .= "AND h.start <= '$end_time' AND  h.start >= '$start_time'";
		$status != -1 && $where .= " AND h.status='$status'";
		$qid    != '' && $where .= " AND h.qid='$qid'";
		$type == 1 && $login_name !='' && $where .= " AND h.aid='$login_name'";
		$type == 2 && $login_name !='' && $where .= " AND h.applicant='$login_name'"; 
		// 1已逾期 0未逾期
		if($overdue == 0){
			$where .= " AND ($now-h.start < hc.time*60)";
		}else if($overdue == 1){
			$where .= " AND ($now-h.start > hc.time*60)";
		}
		return $where;
	}
	function get_hnum($where){
		$sql = 'SELECT
				  count(h.id)
				FROM ' . DB_TABLEPRE . 'help AS h
				  LEFT JOIN ' . DB_TABLEPRE . 'question AS q ON h.qid = q.id
				  LEFT JOIN ' . DB_TABLEPRE . 'hekp_config AS hc ON q.cid1 = hc.cid';
		return $this->db->result_first($sql .  $where);
	}
	function get_hlist($start=0, $limit=20,$where=''){
		$now = time();
		$sql = 'SELECT
				    h.id,
					h.qid,
					h.applicant,
					h.aid,
					h.status,
					h.start,
					h.did,
					h.aid_content,
					h.back_content,
				    h.back_time,
				    h.aid_id,
	    			q.js_kf,
				    hc.time
				FROM ' . DB_TABLEPRE . 'help AS h
				  LEFT JOIN ' . DB_TABLEPRE . 'question AS q ON h.qid = q.id
				  LEFT JOIN ' . DB_TABLEPRE . 'hekp_config AS hc ON q.cid1 = hc.cid ' . $where ." order by h.id desc LIMIT $start,$limit";
		$helplist = $this->db->fetch_all($sql,"id");
		foreach($helplist as $key=>$value){
			$helplist[$key]['start']     = date("Y-m-d H:i:s",$value['start']);
			$helplist[$key]['back_time'] = !empty($value['back_time'])?date("Y-m-d H:i:s",$value['back_time']):'';
			$helplist[$key]['overdue']   = $now - $value['start']-$value['time']*60 < 0 ? 0:1; //如果help_config表中未配置时间的话 会逾期
			$helplist[$key]['date']      = $value['start']+$value['time']*60 ; //协助处理提交时间到配置时间之和
		}
		return $helplist;
	}
}
?>
