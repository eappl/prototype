<?php

!defined('IN_TIPASK') && exit('Access Denied');

class question_hmodel {

    var $db_h;
    var $base;
    var $field = array('id','cid','cid1','id','cid','cid1','cid2','cid3','cid4','author',
    		'authorid','author_id','title','description','time','endtime','hidden','views','status','ip','revocation',
    		'rev_man','revocation_time','start_man','start_time','mark','pid','from','handle_status','tag','gameid',
    		'game_name','phone','attach','receive_time','is_hawb','js_kf','is_pj','q_handle_status','help_status','display_h','atime','r_site');

    function question_hmodel(&$base) {
        $this->base = $base;
        $this->db_h = $base->db_h;
    }
    
    //查看选择年份的历史库问题
    function Get_All_Question_H($where='',$page=true,$all_kf=0,$history_year='',$start=0, $limit=20){
    	$questionlist = array();
    	$q_table = DB_TABLEPRE.'question_h_'.$history_year;
    	$a_table = DB_TABLEPRE.'answer_h_'.$history_year;
    	$query=$this->db_h->query("SHOW TABLES LIKE '".$q_table."'");
    	$rows = $this->db_h->num_rows($query);
    	if($rows == 1){//存在此表
    		if($all_kf == 1){
    			$sql = "SELECT q.id,q.mark,q.status,q.hidden,q.revocation,q.from,
    					q.revocation,q.from,q.handle_status,q.title,q.is_pj,q.time,
    				q.views,q.author,q.description,q.cid,q.cid1,q.cid2,q.cid3,q.cid4,
    				q.receive_time,q.r_site,q.game_name,a.author AS Aauthor,a.time AS Atime FROM ". $q_table . " AS q " .
    					"LEFT JOIN ".$a_table ." AS a ON q.id = a.qid $where";
    			$page && $sql .=" LIMIT $start,$limit";//是否进行分页
    			$questionlist = $this->db_h->fetch_all($sql);
    		}else{
    			$sql = "SELECT q.id,q.mark,q.status,q.hidden,q.revocation,q.from,
    					q.revocation,q.from,q.handle_status,q.title,q.is_pj,q.time,
    				q.views,q.author,q.description,q.cid,q.cid1,q.cid2,q.cid3,q.cid4,
    				q.receive_time,q.js_kf AS Aauthor,q.atime AS Atime,q.r_site,q.game_name
    					 FROM ". $q_table . " AS q $where" ;
    			$page && $sql .=" LIMIT $start,$limit";//是否进行分页
    			$questionlist = $this->db_h->fetch_all($sql);
    		}
    		
    	}
    	return $questionlist;
    }
     
    //获取选择年份的历史库问题的数量
    function Get_Num_H($where,$all_kf=0,$history_year=''){
    	$q_table = DB_TABLEPRE.'question_h_'.$history_year;
    	$a_table = DB_TABLEPRE.'answer_h_'.$history_year;
    	$query=$this->db_h->query("SHOW TABLES LIKE '".$q_table."'");
    	$rows = $this->db_h->num_rows($query);
    	if($rows == 1){//存在此表
    		if($all_kf == 1){
    			$num = $this->db_h->fetch_all("SELECT q.id FROM ".$q_table." AS q LEFT JOIN ".$a_table ." AS a ON q.id = a.qid $where");
    			$num = count($num);
    		}else{
    			$num = $this->db_h->result_first("SELECT COUNT(*) FROM ".$q_table." AS q $where");
    		}
    	}else{
    		$num = -1; //不存在历史问题表
    	}    	    	
    	return $num;
    }
     
    //获取查询的条件
    function Get_Where_H($where1='',$where2='',$where3='',$where4='',$where5='',$where6='',$where7='',$where8='',$where9='',$where10='',
    		$where11='',$where12='',$where13='',$where14='',$where15='',$where16='',$where17='',$where18='',$where19='',$where20=-1,$where21=0,$where22=-1){
    	$where = " WHERE 1 ";
    	$start_time = time();
    	!empty($where1) && $where.=" AND q.time >='$where1'";
    	!empty($where2) && $where.=" AND q.time <='$where2'";
    	if($where3 != ''){
    		$time1 = $start_time-$where3*60;
    		$where.=" AND q.time <='$time1'";
    	}
    	if($where4 != ''){
    		$time2 = $start_time-$where4*60;
    		$where.=" AND q.time >='$time2'";
    	}
    	!empty($where5) && $where.=" AND q.atime >='$where5'";
    	!empty($where6) && $where.=" AND q.atime <='$where6'";
    	if($where7 != ''){
    		$time3 = $where7*60;
    		$where.=" AND q.atime >= q.time+$time3";
    	}
    	if($where8 != ''){
    		$time4 = $where8*60;
    		$where.=" AND q.atime <= q.time+$time4";
    	}
    	$where9 != -1 && $where.=" AND q.revocation ='$where9'";
    	$where10 != -1 && $where.=" AND q.q_handle_status ='$where10'";
    	$where11 != -1 && $where.=" AND q.status ='$where11'";
    	$where12 != -1 && $where.=" AND q.is_pj ='$where12'";
    	$where13 != '' && $where.=" AND q.id ='$where13'";
    	if($where21 == 1){
    		$where14 != '' && $where.=" AND a.author ='$where14'";
    	}else{
    		$where14 != '' && $where.=" AND q.js_kf ='$where14'";
    	}
    	
    	$where15 != '' && $where.=" AND q.author ='$where15'";
    	$where16 != '' && $where.=" AND q.title ='$where16'";
    	 
    	if($where17 != 1){
    		$where17 == 2 && $where.=" AND q.pid = 0";
    		$where17 == 3 && $where.=" AND q.mark = 1";
    	}
    	$where20 !=-1 && $where.=" AND q.help_status='$where20'";
    	if($where18 != -1){
    		$where.=$this->get_cat($where18);
    	}
    	$where22 !=-1 && $where.=" AND q.r_site='$where22'";
    	if($where21 == 1){
    		$where.=" GROUP BY q.id ";//按照问题ID进行分组
    	}     	
    	 
    	if($where19 != 0){
    		$where19 == 1 && $where.=" ORDER BY q.time DESC";
    		$where19 == 2 && $where.=" ORDER BY q.views DESC";
    	}else{
    		$where.=" ORDER BY q.time DESC";
    	}
    	return $where;
    }
    
    //根据问题ID获取所有相关联的问题ID
    function get_question_tree_H($qid=0,$history_year=''){
    	$arr = array();
    	$q_table = DB_TABLEPRE.'question_h_'.$history_year;
    	$query=$this->db_h->query("SHOW TABLES LIKE '".$q_table."'");
    	$rows = $this->db_h->num_rows($query);
    	if($rows == 1){//存在此表   		
    		if($qid > 0){
    			$pid = $this->db_h->result_first("SELECT pid FROM `".$q_table."` WHERE `id` = $qid");
    			if($pid > 0){
    				$arr[] = $pid;
    				$query = $this->db_h->query("SELECT id FROM " . $q_table . " WHERE pid='$pid'");
    				while($data = $this->db_h->fetch_array($query)) {
    					$arr[] = $data['id'];
    				}
    			}else{
    				$arr[] = $qid;
    				$query = $this->db_h->query("SELECT id FROM " . $q_table . " WHERE pid='$qid'");
    				while($data = $this->db_h->fetch_array($query)) {
    					$arr[] = $data['id'];
    				}
    			}
    		}
    	}
    	   	 
    	return $arr;
    }
    
    //显示问题树(后台显示用)
    function Get_Question_List_H($where='',$history_year=''){
    	$questionlist = array();
    	$q_table = DB_TABLEPRE.'question_h_'.$history_year;
    	$a_table = DB_TABLEPRE.'answer_h_'.$history_year;
    	$query=$this->db_h->query("SHOW TABLES LIKE '".$q_table."'");
    	$rows = $this->db_h->num_rows($query);
    	if($rows == 1){//存在此表  		
    		$sql = 'SELECT q.id,q.pid,q.author,q.time,q.views,q.is_pj,q.revocation,q.revocation_time,q.phone,q.status,
    				q.hidden,q.from,q.handle_status,q.title,q.cid,q.cid1,q.cid2,q.cid3,q.cid4,q.description,
    				q.attach,q.js_kf,q.receive_time,q.r_site,q.game_name,a.id AS Aid,a.author AS Aauthor,
    				a.content AS Acontent,a.time AS Atime FROM '. $q_table . ' AS q LEFT JOIN '.
    				$a_table ." AS a ON q.id = a.qid $where";
    		$questionlist = $this->Fetch_List_H($sql);  		 		
    	}   	
    	return $questionlist;  
    }
    
    //获取标准的一对多的数组
    function Fetch_List_H($sql){
    	$data = array();
    	$list = $this->db_h->fetch_all($sql);
    	if(!empty($list)){
    		foreach($list as $v){
    			$v_key = array_keys($v);
    			foreach($v_key as $k_v){
    				if(in_array($k_v,$this->field))
    					$data[$v['id']][$k_v] = $v[$k_v];
    				else{
    					if(!empty($v['Aid']))
    						$data[$v['id']]['answerModel'][$v['Aid']][$k_v] = $v[$k_v];
    				}
    			}
    		}
    	}
    	return $data;
    }

}

?>