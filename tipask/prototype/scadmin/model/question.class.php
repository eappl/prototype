<?php

!defined('IN_TIPASK') && exit('Access Denied');
require TIPASK_ROOT . '/lib/phpanalysis.class.php';

class questionmodel extends base{

    var $db;
    var $base;
    var $cache;

    var $field = array('id','cid','cid1','id','cid','cid1','cid2','cid3','cid4','author',
    		'authorid','author_id','title','description','time','endtime','hidden','views','status','ip','revocation',
    		'rev_man','revocation_time','start_man','start_time','mark','pid','from','handle_status','tag','gameid','operatorid','serverid','areaid',
    		'game_name','operator_name','server_name','area_name','phone','comment','attach','receive_time','is_hawb','js_kf','is_pj','q_handle_status','help_status','display_h','atime','r_site','qtype');

    function questionmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        $this->cache = $base->cache;
    }

    //获取等待处理的问题
    function Get_Handle_List($author)
    {
    	$complaiCid = $this->getType(3);
    	$handle_list = $this->db->fetch_all("SELECT id,title,help_status,description FROM ".DB_TABLEPRE."question WHERE status = 1 AND cid !=$complaiCid AND display_h=0 AND revocation = 0 AND js_kf = '".$author."' ORDER BY time");
    	return $handle_list;
    }


    //获取问题明细
    function Get($id){
    	return $this->db->fetch_first("SELECT * FROM `".DB_TABLEPRE."question` WHERE `id` = $id");
    }

    //显示问题树(后台显示用)
    function Get_Question_List($where=''){
    	$sql = 'SELECT q.id,q.pid,q.author,q.time,q.views,q.is_pj,q.revocation,q.revocation_time,q.phone,q.comment,q.status,q.hidden,
    			q.from,q.handle_status,q.title,q.cid,q.cid1,q.cid2,q.cid3,q.cid4,q.description,q.attach,q.js_kf,
    			q.receive_time,q.r_site,q.game_name,q.operator_name,q.area_name,q.server_name,a.id AS Aid,a.author AS Aauthor,'.
    		   'a.content AS Acontent,a.time AS Atime,a.Comment as Acomment,ip FROM '. DB_TABLEPRE . 'question AS q LEFT JOIN '.
    	DB_TABLEPRE ."answer AS a ON q.id = a.qid $where";
		$questionlist = $this->Fetch_List($sql);
		return $questionlist;

    }

    //查看所有问题
    function Get_All_Question($where='',$page=true,$all_kf=0,$start=0, $limit=20){
		if($all_kf == 1){
    		$sql = "SELECT q.description,q.id,q.mark,q.status,q.hidden,q.revocation,q.from,
    					q.revocation,q.from,q.handle_status,q.title,q.is_pj,q.time,
    				q.views,q.author,q.description,q.cid,q.cid1,q.cid2,q.cid3,q.cid4,
    				q.receive_time,q.r_site,q.game_name,q.is_hawb,q.help_status,q.q_handle_status,a.author AS Aauthor,a.time AS Atime,Comm_status,q.comment,q.js_kf
    			    FROM ". DB_TABLEPRE . "question AS q " .
    		    					"LEFT JOIN ".DB_TABLEPRE ."answer AS a ON q.id = a.qid $where";
									
    		$page && $sql .=" LIMIT $start,$limit";//是否进行分页
			$questionlist = $this->db->fetch_all($sql);
    	}else{
    		$sql = "SELECT q.description,q.id,q.mark,q.status,q.hidden,q.revocation,q.from,
    					q.revocation,q.from,q.handle_status,q.title,q.is_pj,q.time,
    				q.views,q.author,q.description,q.cid,q.cid1,q.cid2,q.cid3,q.cid4,
    				q.receive_time,q.js_kf AS Aauthor,q.atime AS Atime,q.r_site,q.game_name,q.is_hawb,q.help_status,q.q_handle_status,Comm_status,q.comment
    				FROM ". DB_TABLEPRE . "question AS q $where" ;
    		$page && $sql .=" LIMIT $start,$limit";//是否进行分页
			$questionlist = $this->db->fetch_all($sql);
    	}
    	return $questionlist;

    }

    //获取所有问题的数量
    function Get_Num($where,$all_kf=0){
    	if($all_kf == 1){
    		$num = $this->db->fetch_all("SELECT q.id FROM ".DB_TABLEPRE."question AS q LEFT JOIN ".DB_TABLEPRE ."answer AS a ON q.id = a.qid $where");
    		$num = count($num);
    	}else{
    		$num = $this->db->result_first("SELECT COUNT(*) FROM ".DB_TABLEPRE."question AS q $where");
    	}
    	return $num;
    }

    //获取查询的条件
    function Get_Where($where1='',$where2='',$where3='',$where4='',$where5='',$where6='',$where7='',$where8='',$where9='',$where10='',
    		$where11='',$where12='',$where13='',$where14='',$where15='',$where16='',$where17='',$where18='',$where19='',$where20=-1,$where21=0,$where22=-1
    		,$where23='-1',$where24='-1',$where25='-1',$where26='-1',$where27='-1',$where28='-1',$where29='0',$where30=''){
    	$complainId = $this->getType(3);
    	$where = " WHERE cid !=$complainId "; // 除了投诉的问题
    	$start_time = time();
    	!empty($where1) && $where.=" AND q.time >=$where1";
    	!empty($where2) && $where.=" AND q.time <=$where2";
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
    	//if($where18 != -1){
    	//	$where.=$this->get_cat($where18);
    	//}
    	$where22 !=-1 && $where.=" AND q.r_site='$where22'";

    	$where23 !=-1 && $where.=" AND q.cid=$where23";
    	$where24 !=-1 && $where.=" AND q.cid1=$where24";
    	$where25 !=-1 && $where.=" AND q.cid2=$where25";
    	$where26 !=-1 && $where.=" AND q.cid3=$where26";
    	$where27 !=-1 && $where.=" AND q.cid4=$where27";
		$where28 !=-1 && $where.=" AND Comm_status=$where28";
		if($where29=='0')
		{
			//
		}
		elseif($where29=='-1')
		{
			$where.=" AND gameid=''";
		}
		else
		{
			$where.=" AND gameid='$where29'";
		}
		$where30 !='' && $where.=" AND comment like '%$where30%'";
		
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

    //获取标准的一对多的数组
    function Fetch_List($sql){
    	$data = array();
    	$list = $this->db->fetch_all($sql);
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
							$Comment = unserialize($v['Acomment']);
							$data[$v['id']]['answerModel'][$v['Aid']]['first_operator'] = $v['Aauthor'];

							if(isset($Comment['answer_update_log']))
							{
								$data[$v['id']]['answerModel'][$v['Aid']]['first_answer'] = $Comment['answer_update_log']['0']['time'];
								$data[$v['id']]['answerModel'][$v['Aid']]['operator'] = $Comment['answer_update_log'][count($Comment['answer_update_log'])-1]['update_operator'];
								$data[$v['id']]['answerModel'][$v['Aid']]['Atime'] = $Comment['answer_update_log'][count($Comment['answer_update_log'])-1]['update_time'];
							}
							else
							{
								$data[$v['id']]['answerModel'][$v['Aid']]['first_answer'] = $v['Atime'];
								$data[$v['id']]['answerModel'][$v['Aid']]['operator'] = $v['Aauthor'];								
							}
							$data[$v['id']]['answerModel'][$v['Aid']][$k_v] = $v[$k_v];
							$max[$v['Aauthor']] = max($v['Aid'],$max[$v['Aauthor']]);
							$data[$v['id']]['first_answer'] = $data[$v['id']]['first_answer']==0?($data[$v['id']]['answerModel'][$v['Aid']]['first_answer']):min($data[$v['id']]['first_answer'],$data[$v['id']]['answerModel'][$v['Aid']]['first_answer']);
    					}
						foreach($data[$v['id']]['answerModel'] as $key => $value)
						{
							if($key != $max[$value['Aauthor']])
							{
								unset($data[$v['id']]['answerModel'][$key]);
							}
						}

    				}
    			}
				ksort($data[$v['id']]['answerModel']);
				$data[$v['id']]['comment'] = $contactArr;

    		}
    	}
    	return $data;
    }

    //根据问题分类取问题
    function get_cat($cid){
    	$where = '';
		$sql = "SELECT grade FROM `".DB_TABLEPRE."category` WHERE `id` = $cid";
	    $grade = $this->db->result_first($sql);
	    if($grade == 1){
	    	$where.=" AND q.cid ='$cid'";
	    }elseif($grade == 2){
	    	$where.=" AND q.cid1 ='$cid'";
	    }elseif($grade == 3){
	    	$where.=" AND q.cid2 ='$cid'";
	    }elseif($grade == 4){
	    	$where.=" AND q.cid3 ='$cid'";
	    }elseif($grade == 5){
	    	$where.=" AND q.cid4 ='$cid'";
	    }

    	return $where;
    }

    //获取问题列表树
    function getCid($cid=0){
		$category_str = '';
    	$sql = "SELECT * FROM " . DB_TABLEPRE . "category WHERE pid=$cid";
		$categorylist = $this->db->fetch_all($sql,"id");
		foreach($categorylist as $val){
    		$category_str.='<ul><li style="padding:3px"><a id="li_a'.$val['id'].'" href="javascript:;" onclick="getCid('.$val['id'].')">'.$val['name'].'</a></li></ul>';
    	}
    	exit($category_str);
    }

    //根据问题ID获取所有相关联的问题ID
    function get_question_tree($qid=0){
    	$arr = array();
    	if($qid > 0){
    		$pid = $this->db->result_first("SELECT pid FROM `".DB_TABLEPRE."question` WHERE `id` = $qid");
	    	if($pid > 0){
	    		$arr[] = $pid;
	    		$query = $this->db->query("SELECT id FROM " . DB_TABLEPRE . "question WHERE pid='$pid'");
	    		while($data = $this->db->fetch_array($query)) {
		             $arr[] = $data['id'];
		        }
	    	}else{
	    		$arr[] = $qid;
	    		$query = $this->db->query("SELECT id FROM " . DB_TABLEPRE . "question WHERE pid='$qid'");
	    		while($data = $this->db->fetch_array($query)) {
	    			$arr[] = $data['id'];
	    		}
	    	}
    	}

    	return $arr;
    }

    //统计月的问题数
    function getMonthQuestion(){
    	 $arr = array();
    	 $i = 1;
    	 $where = " WHERE a.time <= ".time()." AND a.time >= ".$this->_getTime(1)." AND q.status in('2','3') GROUP BY a.author ORDER BY num DESC";
    	 $sql = "SELECT a.author,COUNT(a.author) AS num FROM ". DB_TABLEPRE . "question AS q LEFT JOIN ".DB_TABLEPRE ."answer AS a ON q.id = a.qid $where";
    	 $query = $this->db->query($sql);
    	 while($row = $this->db->fetch_array($query)){
    	 	 $arr[$row['author']]['sorce'] = $i;
    	 	 $arr[$row['author']]['num'] = $row['num'];
    	 	 $i++;
    	 }
    	 return $arr;
    }

    //是否选择了分类
    function is_cat($id){
    	 $cid1 = $this->db->result_first("SELECT cid1 FROM `".DB_TABLEPRE."question` WHERE `id` = $id");
    	 if(!empty($cid1)){
    	 	  $cid1 != 0 ? exit('1') : exit('0');
    	 }
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
    function getTypeDB($type='')
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
		$cid = $this->db->result_first($sql);
    	return $cid;
    }


     // 更新qtype主分类
     function update_qtype($id,$qtype){
     	   $sql = 'UPDATE '.DB_TABLEPRE."question SET qtype=". $qtype . " WHERE id='" . $id . "'";
     	   $this->db->query($sql);
     }
     // 获取问题用户名,评价状态
     function get_question_status($id){
     	return $this->db->fetch_first("SELECT author,is_pj FROM " . DB_TABLEPRE . "question WHERE id = $id");
     }
     //根据ip获取每个小时的提问数
     function get_num_by_ip($ip){
     	  $old_time = time() - 3600;
     	  $num = $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "question WHERE ip='".$ip."' AND time<".time()." AND time>".$old_time);
     	  return $num;
     }
     //根据投诉ip获取每个小时的投诉数
     function get_complain_num_by_ip($ip){
     	  $old_time = time() - 3600;
     	  $num = $this->db->result_first("SELECT COUNT(*) FROM " . DB_TABLEPRE . "complain WHERE ip='".$ip."' AND time<".time()." AND time>".$old_time);
     	  return $num;
     }

     //获取历史提问记录
     function Get_History($author){
     	$history = array();
     	$id_arr = array();
     	if($author == '游客') return $history;
     	$sql = "SELECT id FROM ".DB_TABLEPRE."question WHERE author='".$author."' ORDER BY time DESC ";
     	$arr = $this->db->fetch_all($sql);
     	if(!empty($arr)){
     		foreach($arr as $val)
     			$id_arr[] = $val['id'];
     	}
     	if(empty($id_arr)) return $history;
     	$where = ' AND q.id IN ('.implode(',',$id_arr).') ';
     	$sql = 'SELECT
				  q.id,
				  q.title,
      			  q.views,
      			  q.pid,
      			  q.tag,
      			  q.status,
      			  q.time,
      			  q.author,
      			  q.description,
      			  q.attach,
      			  q.is_pj,
      			  a.time as Atime,
      			  a.author as Aauthor,
      			  a.content as Acontent,
      			  a.id as Aid
				FROM ' . DB_TABLEPRE . 'question AS q
				  LEFT JOIN '. DB_TABLEPRE. 'answer AS a
				    ON q.id = a.qid
      	        WHERE
				  	 q.revocation = 0' . $where.
				  	 'ORDER BY q.time DESC,a.time DESC' ;
     	$history = $this->Fetch_List($sql);
     	return $history;
     }

     //获取Solr服务器提交需要的数据
     function Get_Search_Data($qid){
     	$sql = 'SELECT
				  id,
      			  description,
      			  time,atime,hidden
				FROM ' . DB_TABLEPRE . 'question
				WHERE id = '.$qid;
     	$rs = $this->db->fetch_first($sql);
     	return $rs;
     }

     /*
      * KF-130426-01-0客服一体化V1.4报表统计
      */

     //分类数据统计
     function GetCategoryCount($start_time,$end_time,$cid,$cid1,$cid2,$cid3,$cid4,$join,$order,$game='0'){
     	$qcname = $this->_getQCName($cid,$cid1,$cid2,$cid3,$cid4);
     	$complainCid = $this->getType(3);
     	if(empty($qcname)) return $qcname; //分类为空，直接返回
     	$where = ' WHERE 1 ';
     	if($start_time != '') $where .= ' AND time>='.$start_time;
     	if($end_time != '') $where .= ' AND time<='.$end_time;
     	if($cid != -1) $where .= ' AND cid='.$cid;
     	if($cid1 != -1) $where .= ' AND cid1='.$cid1;
     	if($cid2 != -1) $where .= ' AND cid2='.$cid2;
     	if($cid3 != -1) $where .= ' AND cid3='.$cid3;
     	if($cid4 != -1) $where .= ' AND cid4='.$cid4;
        if($join == 1)  $where .= ' AND pid=0';
		if($game=='0')
		{
			//
		}
		elseif($game=='-1')
		{
			$where.=" AND gameid=''";
		}
		else
		{		
			$where.=" AND gameid='$game'";
		}
		
			
        $sql = "select id,cid,cid1,cid2,cid3,cid4,q_handle_status,is_pj FROM ".DB_TABLEPRE."question $where ";//AND qtype >0AND revocation = 0";
		$rs = $this->db->query($sql);
		$i=$j=$k=$m=$n=0;     //i:咨询量,j:处理量,k:满意,m:不满意,n:未评价
        while($data = $this->db->fetch_array($rs))
		{
        	$c_str = '';
        	if($data['cid'] != 0) $c_str .= '-'.$data['cid'].'-';
        	if($data['cid1'] != 0) $c_str .= '-'.$data['cid1'].'-';
        	if($data['cid2'] != 0) $c_str .= '-'.$data['cid2'].'-';
        	if($data['cid3'] != 0) $c_str .= '-'.$data['cid3'].'-';
        	if($data['cid4'] != 0) $c_str .= '-'.$data['cid4'].'-';
			if($c_str != '')
			{
        		foreach($qcname as $k => $v)
				{
        			
        			if(false !== strpos($k,"-".$complainCid."-"))
        			{
        				unset($qcname[$k]);
        				continue;
        			}
        			if(false !== strpos($c_str,$k))
					{
						//统计咨询量
        				++$qcname[$k]['i'];
        				//统计处理量
        				if($data['q_handle_status'] == 1) ++$qcname[$k]['j'];
        				//统计未评价量
        				if($data['is_pj'] == 0) ++$qcname[$k]['n'];
        				//统计满意量
        				if($data['is_pj'] == 1) ++$qcname[$k]['k'];
        				//统计不满意量
        				if($data['is_pj'] == 2) ++$qcname[$k]['m'];
        			}
        		}
        	}
        }
        if($order == 1) $qcname = $this->array_sort($qcname,'i');
        if($order == 2) $qcname = $this->array_sort($qcname,'i','desc');
		return $qcname;
     }

     //分类数据统计
     function GetGameGroup($start_time,$end_time,$selGame,$join,$order){
     	$where = " WHERE 1 AND gameid!='' ";
     	if($start_time != '') $where .= ' AND time>='.$start_time;
     	if($end_time != '') $where .= ' AND time<='.$end_time;
     	if($selGame != -1) $where .= " AND gameid='".$selGame."'";
     	if($join == 1)  $where .= ' AND pid=0';
     	$where .= ' GROUP BY gameid ';
     	if($order == 1){
     		$where .= ' ORDER BY zxj ASC ';
     	}elseif($order == 2){
     		$where .= ' ORDER BY zxj DESC ';
     	}
     	$sql = "SELECT
     	 		 COUNT(id) AS zxj,
     	 		 SUM(q_handle_status) AS clj,
				 SUM(CASE WHEN is_pj=1 THEN 1 ELSE 0 END) AS myj,
				 SUM(CASE WHEN is_pj=2 THEN 1 ELSE 0 END) AS bmyj,
				 SUM(CASE WHEN is_pj=0 THEN 1 ELSE 0 END) AS wpjj,
     			 gameid,game_name
     	 		 FROM ".DB_TABLEPRE."question $where";
     	$rs = $this->db->fetch_all($sql);
     	return $rs;
     }

     //数组排序算法
	 function array_sort($arr,$keys,$type='asc'){
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
		return $new_array;
	 }

     function _getCid($cid,$s_cid){
     	$category_str = '';
     	$categorylist = $this->db->fetch_all("SELECT * FROM " . DB_TABLEPRE . "category WHERE pid='$cid'","id");
     	foreach($categorylist as $val){
     		$selected = $s_cid == $val['id']?'selected':'';
     		$category_str.='<option value="'.$val['id'].'" '.$selected.'>'.$val['name'].'</option>';
     	}
     	return $category_str;
     }

     function _getCidName(){
     	$cache_data = $this->cache->get('question_category');//如果存在缓存，则读取缓存
     	if(false !== $cache_data) return $cache_data;
     	$arr = array();
     	$c_arr = array();
     	$c_list = $_ENV['category']->getNameById();
     	$rs = $this->db->query("SELECT id,name,pid FROM " . DB_TABLEPRE . "category");
     	while($data = $this->db->fetch_array($rs)){
     		$p_arr = array();
     		$p_arr = $this->_getParentCid($data['pid']);
     		$p_arr[] = $data['id'];
     		$c_arr[] = $p_arr;
     	}
     	if(!empty($c_arr)){
     		foreach($c_arr as $c_v){
     			$c_key = '';
     			$c_val = '';
     			if(!empty($c_v)){
     				foreach($c_v as $k => $v){
     					if(array_key_exists($v, $c_list)){
     						$c_key .= '-'.$v.'-';
     						if($k == 0){
     							$c_val .= $c_list[$v];
     						}else{
     							$c_val .= '--'.$c_list[$v];
     						}
     						$arr[$c_key] = $c_val;
     					}
     				}
     			}
     		}
     	}
     	if(!empty($arr)){
     		$this->cache->set('question_category',$arr,600);//写入缓存，缓存时间为10分钟
     	}
     	return $arr;
     }

     function _getQCName($cid,$cid1,$cid2,$cid3,$cid4){
		$arr = array();
     	$c_str = '';
     	$cidname = $this->_getCidName();
		if($cid != -1) $c_str = '-'.$cid.'-';
     	if($cid1 != -1) $c_str .= '-'.$cid1.'-';
     	if($cid2 != -1) $c_str .= '-'.$cid2.'-';
     	if($cid3 != -1) $c_str .= '-'.$cid3.'-';
     	if($cid4 != -1) $c_str .= '-'.$cid4.'-';
     	if($c_str != '')
		{
     		foreach($cidname as $key => $val)
			{
     			if(false === strpos($key,$c_str))
				{				
					unset($cidname[$key]);				
				}
				else
				{
					$array = array('name'=>$val,'i'=>0,'j'=>0,'k'=>0,'m'=>0,'n'=>0);
					$arr[$key] = $array;
				}
     		}
     	}
     	return $arr;
     }

     function _getParentCid($pid){
     	$p_arr = array();
     	$rs = $this->db->fetch_first("SELECT id,name,pid FROM " . DB_TABLEPRE . "category WHERE id=$pid");
     	if(!empty($rs)){
     		$p_arr = $this->_getParentCid($rs['pid']);
     		$p_arr[] = $rs['id'];
     	}
     	return $p_arr;
     }

     function _getTime($type){
     	$day = date("j");
     	if($type == 1)
     		$time = $day <= 25 ? mktime(0,0,0,date('m')-1,26,date('Y')):mktime(0,0,0,date('m'),26,date('Y'));
     	else
     		$time = mktime(23,59,59,date('m'),date('d'),date('Y'));
     	return $time;
     }

     function _getSETime($type)
	 {
     	if($type == 1)
     		$time = mktime(0,0,0,date('m'),date('j')-2,date('Y'));
     	else
     		$time = mktime(23,59,59,date('m'),date('j'),date('Y'));
     	return $time;
     }

     //日期数据统计
     function GetDateCount($start_time,$end_time,$cid,$cid1,$cid2,$cid3,$cid4,$join,$order,$operatorList,$assessOverTimeLimit){
		 $where = ' WHERE 1 ';
     	 $complainCid = $this->getType(3);
     	 if($start_time != '') $where .= ' AND time>='.$start_time;
     	 if($end_time != '') $where .= ' AND time<='.$end_time;
     	 if($cid != -1) $where .= ' AND cid='.$cid;
     	 if($cid1 != -1) $where .= ' AND cid1='.$cid1;
     	 if($cid2 != -1) $where .= ' AND cid2='.$cid2;
     	 if($cid3 != -1) $where .= ' AND cid3='.$cid3;
     	 if($cid4 != -1) $where .= ' AND cid4='.$cid4;
     	 if($join == 1)  $where .= ' AND pid=0';
     	 if( $cid == $complainCid || $cid==-1 )
     	 {
     	 	$where .= " AND cid != $complainCid";
     	 }
		 if($operatorList!="(0)")
		 {
			 $where .= ' AND js_kf in '.$operatorList;
		 }
		 else
		 {
			 $where .= ' AND js_kf = -1 ';
		 }
     
     	 $where .= ' GROUP BY date ';
     	 if($order == 1){
     	 	$where .= ' ORDER BY zxj ASC ';
     	 }elseif($order == 2){
     	 	$where .= ' ORDER BY zxj DESC ';
     	 }
		 
     	 if($start_time != '') $where2 .= ' AND q.time>='.$start_time;
     	 if($end_time != '') $where2 .= ' AND q.time<='.$end_time;
     	 if($cid != -1) $where2 .= ' AND cid='.$cid;
     	 if($cid1 != -1) $where2 .= ' AND cid1='.$cid1;
     	 if($cid2 != -1) $where2 .= ' AND cid2='.$cid2;
     	 if($cid3 != -1) $where2 .= ' AND cid3='.$cid3;
     	 if($cid4 != -1) $where2 .= ' AND cid4='.$cid4;
     	 if($join == 1)  $where2 .= ' AND pid=0';
     	 if( $cid == $complainCid || $cid==-1 )
     	 {
     	 	$where2 .= " AND cid != $complainCid";
     	 }
		 if($operatorList!="(0)")
		 {
			 $where2 .= ' AND js_kf in '.$operatorList;
		 }
		 else
		 {
			 $where2 .= ' AND js_kf = -1 ';
		 }
     
     	 $where2 .= ' GROUP BY date ';
     		$sql = "SELECT
     		     FROM_UNIXTIME(q.time,'%Y-%m-%d') AS date,
     		     COUNT(a.id) AS hfj
     		     FROM ".DB_TABLEPRE."answer AS a
     		     LEFT JOIN ".DB_TABLEPRE."question AS q
     		     ON q.id = a.qid $where2";
		$query = $this->db->query($sql);
     		
			while($row = $this->db->fetch_array($query)){
     			$rs1[$row['date']] = $row['hfj'];
     		}
     	 $sql = "SELECT
     	 		 FROM_UNIXTIME(time,'%Y-%m-%d') AS date,
     	 		 COUNT(id) AS zxj,
     	 		 SUM(q_handle_status) AS clj,
				 SUM(CASE WHEN is_pj=1 THEN 1 ELSE 0 END) AS myj,
				 SUM(CASE WHEN is_pj=2 THEN 1 ELSE 0 END) AS bmyj,
				 SUM(CASE WHEN is_pj=0 THEN 1 ELSE 0 END) AS wpjj,
				SUM(CASE WHEN ((is_pj=1) and (astime>atime) and ((astime-atime)>$assessOverTimeLimit)) THEN 1 ELSE 0 END) AS yqmyj,
				SUM(CASE WHEN ((is_pj=2) and (astime>atime) and ((astime-atime)>$assessOverTimeLimit)) THEN 1 ELSE 0 END) AS yqbmyj
     	 		 FROM ".DB_TABLEPRE."question $where";
		 $rs = $this->db->fetch_all($sql);
     	 foreach($rs as $k => $v){
			$sum = $rs[$k]['myj']+$rs[$k]['bmyj'];
			$rs[$k]['hfj'] = $rs1[$v['date']]?$rs1[$v['date']]:0;
     	 	$rs[$k]['cl_rate']  = round($rs[$k]['clj']/$rs[$k]['zxj']*100,2).'%';
     	 	$rs[$k]['my_rate']  = $sum == 0?'0%':round($rs[$k]['myj']/$sum*100,2).'%';
     	 	$rs[$k]['bmy_rate'] = $sum == 0?'0%':round($rs[$k]['bmyj']/$sum*100,2).'%';
     	 	$rs[$k]['pj_rate']  = $rs[$k]['hfj']==0?'0%':round($sum/$rs[$k]['hfj']*100,2).'%';
			$rs_total['zxj'] += $rs[$k]['zxj'];
			$rs_total['hfj'] += $rs[$k]['hfj'];
			$rs_total['clj'] += $rs[$k]['clj'];
			$rs_total['myj'] += $rs[$k]['myj'];
			$rs_total['yqmyj'] += $rs[$k]['yqmyj'];
			$rs_total['bmyj'] += $rs[$k]['bmyj'];
			$rs_total['yqbmyj'] += $rs[$k]['yqbmyj'];
			$rs_total['wpjj'] += $rs[$k]['wpjj'];
			$sum_total = $rs_total['myj']+$rs_total['bmyj'];
			$rs_total['my_rate']  = $sum_total == 0?"0%":round($rs_total['myj']/($sum_total)*100,2).'%';
			$rs_total['bmy_rate']  = $sum_total == 0?"0%":round($rs_total['bmyj']/($sum_total)*100,2).'%';
			$rs_total['cl_rate'] = $rs_total['zxj']>0?(round($rs_total['clj']/$rs_total['zxj']*100,2).'%'):"0%";
			$rs_total['pj_rate']  = $rs_total['hfj']==0?'0%':round($sum_total/$rs_total['hfj']*100,2).'%';
		 }
		 $return = array('detail'=>$rs,'total'=>$rs_total);
     	 return $return;
     }

     //客服数据统计
     function GetKeywordCount($start_time,$end_time,$cid,$cid1,$cid2,$cid3,$cid4,$operatorList,$order,$join,$assessOverTimeLimit){
		$rs2 = array();
     	$rs = $this->db->fetch_first("SELECT id,pid FROM " . DB_TABLEPRE . "department WHERE name='在线服务部'");
     	if(!empty($rs)){
     		$tsCid = $this->getType(3);

     		$where1 = ' WHERE 1 ';
     		if($start_time != '') $where1 .= ' AND time>='.$start_time;
     		if($end_time != '') $where1 .= ' AND time<='.$end_time;
     		if($cid != -1) $where1 .= ' AND cid='.$cid;
     		if($cid1 != -1) $where1 .= ' AND cid1='.$cid1;
     		if($cid2 != -1) $where1 .= ' AND cid2='.$cid2;
     		if($cid3 != -1) $where1 .= ' AND cid3='.$cid3;
     		if($cid4 != -1) $where1 .= ' AND cid4='.$cid4;
		 if($operatorList!="(0)")
		 {
			 $where1 .= ' AND js_kf in '.$operatorList;
		 }
		 else
		 {
			 $where1 .= ' AND js_kf = -1 ';
		 }
     		if($join == 1)  $where1 .= ' AND pid=0';
     		$where3 = ' WHERE 1 ';
     		if($start_time != '') $where3 .= ' AND transfer_time>='.$start_time;
     		if($end_time != '') $where3 .= ' AND transfer_time<='.$end_time;
     		if($cid != -1) $where3 .= ' AND cid='.$cid;
     		if($cid1 != -1) $where3 .= ' AND cid1='.$cid1;
     		if($cid2 != -1) $where3 .= ' AND cid2='.$cid2;
     		if($cid3 != -1) $where3 .= ' AND cid3='.$cid3;
     		if($cid4 != -1) $where3 .= ' AND cid4='.$cid4;
		 if($operatorList!="(0)")
		 {
			 $where3 .= ' AND from_operator in '.$operatorList;
		 }
		 else
		 {
			 $where3 .= ' AND from_operator = -1 ';
		 }
     		if($join == 1)  $where3 .= ' AND pid=0';
     		
     		$where2 = ' WHERE 1 ';
     		if($start_time != '') $where2 .= ' AND a.time>='.$start_time;
     		if($end_time != '') $where2 .= ' AND a.time<='.$end_time;
     		if($cid != -1) $where2 .= ' AND q.cid='.$cid;
     		if($cid1 != -1) $where2 .= ' AND q.cid1='.$cid1;
     		if($cid2 != -1) $where2 .= ' AND q.cid2='.$cid2;
     		if($cid3 != -1) $where2 .= ' AND q.cid3='.$cid3;
     		if($cid4 != -1) $where2 .= ' AND q.cid4='.$cid4;
     		if($join == 1)  $where2 .= ' AND q.pid=0';
		 if($operatorList!="(0)")
		 {
			 $where .= ' AND a.author in '.$operatorList;
		 }
		 else
		 {
			 $where .= ' AND a.author = -1 ';
		 }

     		$rs1 = array();
     		// 统计每个客服回复单量
     		$sql = "SELECT
     		     a.author,
     		     COUNT(a.id) AS hfj
     		     FROM ".DB_TABLEPRE."answer AS a
     		     LEFT JOIN ".DB_TABLEPRE."question AS q
     		     ON q.id = a.qid $where2 AND q.cid != $tsCid GROUP BY a.author";
			$query = $this->db->query($sql);
     		while($row = $this->db->fetch_array($query)){
     			$rs1[$row['author']] = $row['hfj'];
     		}
     		$sql = "SELECT
     	 		 js_kf,
     	 		 COUNT(id) AS zxj,
     	 		 SUM(q_handle_status) AS clj,
				 SUM(CASE WHEN is_pj=1 THEN 1 ELSE 0 END) AS myj,
				 SUM(CASE WHEN is_pj=2 THEN 1 ELSE 0 END) AS bmyj,
				 SUM(CASE WHEN is_pj=0 THEN 1 ELSE 0 END) AS wpjj,
				SUM(CASE WHEN ((is_pj=1) and (astime>atime) and ((astime-atime)>$assessOverTimeLimit)) THEN 1 ELSE 0 END) AS yqmyj,
				SUM(CASE WHEN ((is_pj=2) and (astime>atime) and ((astime-atime)>$assessOverTimeLimit)) THEN 1 ELSE 0 END) AS yqbmyj
     	 		 FROM ".DB_TABLEPRE."question $where1 AND cid != $tsCid GROUP BY js_kf";
			$rs2 = $this->db->fetch_all($sql);
			foreach($rs2 as $k => $v){
     			$sum = $rs2[$k]['myj'] + $rs2[$k]['bmyj'];
     			if(array_key_exists($v['js_kf'], $rs1)){
     				$rs2[$k]['hfj'] = $rs1[$v['js_kf']];
     			}else{
     				$rs2[$k]['hfj'] = 0;
     			}
     			$sum = $rs2[$k]['myj']+$rs2[$k]['bmyj'];
				$rs2[$k]['cl_rate'] = round($rs2[$k]['clj']/$rs2[$k]['zxj']*100,2).'%';
     			$rs2[$k]['my_rate'] = $sum==0?"0%":round($rs2[$k]['myj']/$sum*100,2).'%';
     			$rs2[$k]['bmy_rate'] = $sum==0?"0%":round($rs2[$k]['bmyj']/$sum*100,2).'%';
     			$rs2[$k]['pj_rate'] = $rs2[$k]['hfj']==0?"0%":round($sum/$rs2[$k]['hfj']*100,2).'%';
				$rs2[$k]['AidCount'] = 0;
				$rs_total['zxj'] += $rs2[$k]['zxj'];
				$rs_total['hfj'] += $rs2[$k]['hfj'];
				$rs_total['clj'] += $rs2[$k]['clj'];
				$rs_total['myj'] += $rs2[$k]['myj'];
				$rs_total['yqmyj'] += $rs2[$k]['yqmyj'];
				$rs_total['bmyj'] += $rs2[$k]['bmyj'];
				$rs_total['yqbmyj'] += $rs2[$k]['yqbmyj'];
				$rs_total['wpjj'] += $rs2[$k]['wpjj'];
				$sum_total = $rs_total['myj']+$rs_total['bmyj'];
				$rs_total['cl_rate'] = $rs_total['zxj']>0?(round($rs_total['clj']/$rs_total['zxj']*100,2).'%'):"0%";
				$rs_total['my_rate']  = $sum_total == 0?"0%":round($rs_total['myj']/($sum_total)*100,2).'%';
				$rs_total['bmy_rate']  = $sum_total == 0?"0%":round($rs_total['bmyj']/($sum_total)*100,2).'%';
				$rs_total['cl_rate'] = $rs_total['zxj']>0?(round($rs_total['clj']/$rs_total['zxj']*100,2).'%'):"0%";
				$rs_total['pj_rate']  = $rs_total['hfj']==0?'0%':round($sum_total/$rs_total['hfj']*100,2).'%';
				
     		}
     		if(!empty($rs2)){
     			if($order == 1) $rs2 = $this->array_sort($rs2,'hfj');
     			if($order == 2) $rs2 = $this->array_sort($rs2,'hfj','desc');
     		}
     	}
		$sql = "SELECT
			 from_operator,
			 COUNT(distinct(qid)) AS AidCount
			 FROM ".DB_TABLEPRE."aid $where3 AND cid != $tsCid GROUP BY from_operator";
			$rs3 = $this->db->fetch_all($sql);
			$rs_total['AidCount'] = 0;
			foreach($rs3 as $k => $v)
			{
				foreach($rs2 as $key => $value)
				{
					if($value['js_kf']==$v['from_operator'])
					{
						$rs2[$key]['AidCount'] = $v['AidCount'];
						$rs_total['AidCount'] += $v['AidCount'];
					}
				}
				
			}
		$return = array('detail'=>$rs2,'total'=>$rs_total);
     	return $return;
     }
    //将一个未分配的问题分配给指定客服
    //$qid：问题ID
    //$operator：客服账号
    function ApplyToOperator($qid,$operator,$force = false)
    {
		$ctype_ask = $this->getTypeDB(1);
		$ctype_suggest = $this->getTypeDB(2);
		//获取问题
        $question_info = $this->Get($qid);
		//问题存在
        if($question_info['id'])
        {
            //事务开启
            $this->db->begin();
    		//更新问题为已分配
    		$apply_sql =  "UPDATE ".DB_TABLEPRE."question SET is_hawb=1,js_kf='".$operator."',receive_time='".time()."' WHERE id='".$qid."' and is_hawb = 0 and revocation = 0 and js_kf = '' and help_status = 0 and cid in (0,$ctype_ask,$ctype_suggest) limit 1" ;
			//echo $apply_sql."<br>";
			$this->db->query($apply_sql);
            $apply = $this->db->affected_rows();
			//如果更新成功
    		if($apply)
    		{
        		if($force == true)
				//检查客服是否存在或在班
        		{
					//不要求 非忙碌状态
					$get_operator_sql = "SELECT login_name,pid FROM ".DB_TABLEPRE."operator where login_name = '".$operator."'  AND ishandle=1 AND isonjob=1 limit 1";
				}
				else
				{ 
					$get_operator_sql = "SELECT login_name,pid FROM ".DB_TABLEPRE."operator where login_name = '".$operator."'  and isbusy=0 AND ishandle=1 AND isonjob=1 limit 1";
				}
				$o= $this->db->fetch_first($get_operator_sql);
				//如果客服不存在或不在班
        		if($o['login_name']!='')
        		{
                    //如果强制分单,则将当前单量置为负值
					if($force == true)
					{
						$num_arr['num'] = -1;
						$num_arr['num_add'] = -1;
						
					}
					else
					{
						//检查客服已分配单量
						$get_num_sql = "SELECT num,num_add FROM ".DB_TABLEPRE."author_num where author = '".$operator."'";
						$num_arr = $this->db->fetch_first($get_num_sql);					
					}
					//获取分单数量限制
                    $get_limit_sql = "SELECT question_limit,question_limit_add FROM ".DB_TABLEPRE."post where id = '".$o['pid']."' limit 1";
                    $limit = $this->db->fetch_first($get_limit_sql);

                    //首问
                    if($question_info['pid']==0)
                    {
                        //首问单量小于首问最大单量
                        if(intval($num_arr['num'])<$limit['question_limit'])
                        {
        		            //更新首问数量
        		            $update_num_sql = "INSERT INTO ".DB_TABLEPRE."author_num (author,num,num_add,last_receive,last_receive_add) VALUES ('".$operator."',1,0,".time().",0)  ON DUPLICATE KEY UPDATE num = num+1,last_receive=".time();
                        }
            		    else
            		    {
            		        //单量不足，回滚
            		        $this->db->rollback();
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
        		            $update_num_sql = "INSERT INTO ".DB_TABLEPRE."author_num (author,num,num_add,last_receive,last_receive_add) VALUES ('".$operator."',0,1,0,".time().")  ON DUPLICATE KEY UPDATE num_add = num_add+1,last_receive_add=".time();
                        }
            		    else
            		    {
            		        //单量不足，回滚
            		        $this->db->rollback();
            		        return false;
            		    }
                    }
                    //更新单量
                    $this->db->query($update_num_sql);
					$update = $this->db->affected_rows();
					if($update)
		            {
		                //更新成功，提交
		                $this->db->commit();
						return true;
		            }
		            else
		            {
                        // 更新失败，回滚
                        $this->db->rollback();
						return false;
                    }
        		}
        		else
        		{
                    //客服不在班或不存在
                    $this->db->rollback();
					return false;
                }
            }
            else
            {
                //单子不存在或已被分掉
                $this->db->rollback();
				return false;
            }
        }
        else
        {
            //无此问题
            return false;
        }

    }
    //撤销一个已分单但是未回答的提问的分配
    //$qid：提问ID
    function ApplyCancel($qid)
    {
        //事务开启
        $this->db->begin();
		//获取提问内容
        $question_info = $this->Get($qid);
		//如果提问获取到
        if($question_info['id'])
        {
            //如果已经被接手
            if($question_info['js_kf']!='')
            {
                //如果尚未被回答或尚未完结
                if($question_info['status']<2)
                {
                    //标识未被分单
                    $cancel_sql =  "UPDATE ".DB_TABLEPRE."question SET is_hawb = 0,js_kf= '',receive_time= 0 WHERE id='".$qid."' and help_status = 0 limit 1" ;
					$this->db->query($cancel_sql);
                    $cancel = $this->db->affected_rows();
                    //减去单量
                    if($question_info['pid']==0)
                    {
                        $reduce_num_sql =  "UPDATE ".DB_TABLEPRE."author_num SET num = num - 1 WHERE author ='".$question_info['js_kf']."' limit 1" ;
                    }
                    else
                    {
                        $reduce_num_sql =  "UPDATE ".DB_TABLEPRE."author_num SET num_add = num_add - 1 WHERE author ='".$question_info['js_kf']."' limit 1" ;
                    }
                    $this->db->query($reduce_num_sql);
                    $reduce = $this->db->affected_rows();
					if($cancel && $reduce)
                    {
                        //事务成功，提交
                        $this->db->commit();
                        return true;
                    }
                    else
                    {
                        //事务失败，回滚
                        $this->db->rollback();
                        return false;
                    }
                }
                else
                {
                    //问题已被回答，回滚
                    $this->db->rollback();
                    return false;
                }
            }
            else
            {
                //问题无接手，回滚
                $this->db->rollback();
                return false;
            }
        }
        else
        {
            //问题未找到，回滚
            return false;
            $this->db->rollback();
        }

    }
    /*
     * $from_type 原类型
     * $to_type 要转换类型
     */
    function convertQuestionToComplain($question_id, $from_type, $to_type,$OperatorName,$reason)
    { 
        //获取原问题内容
		$questionInfo = $this->Get($question_id);

		if($questionInfo['id'])
        {
            //获取原分类主分类的详情
			$qtype = $_ENV['qtype']->GetQType($questionInfo['qtype']);
			$description = strip_tags($questionInfo['description']);
			$comment = unserialize($questionInfo['comment']);
			
			//如果已经由投诉转来
			if(intval($comment['convert']['from_id'])>0)
			{
				//获取原本投诉详情
				$complainInfo = $_ENV['complain']->get_ComplainInfo($comment['convert']['from_id'],0); 
				//如果获取到
				if(isset($complainInfo['id']))
				{
					//解包备注数组
					$complainComment = unserialize($complainInfo['comment']);
					//重写备注数组中的转换信息
					$complainComment['convert'] = array('from_type'=>$from_type,'from_id'=>$question_id,'transformTime'=>time(),'loginId'=>$OperatorName,'reason'=>$reason);
					$complainComment['OS'] = $comment['OS'];
					$complainComment['Browser'] = $comment['Browser'];
					$convert = serialize($complainComment);
				}
				else
				{
					$convert = serialize(array('convert'=>array('from_type'=>$from_type,'from_id'=>$question_id,'transformTime'=>time(),'loginId'=>$OperatorName,'reason'=>$reason),'OS'=>$comment['OS'],'OS'=>$comment['Browser']));
				}
			}
			//如果不是由投诉转来
			else
		    {
				$convert = serialize(array('convert'=>array('from_type'=>$from_type,'from_id'=>$question_id,'transformTime'=>time(),'loginId'=>$OperatorName,'reason'=>$reason),'OS'=>$comment['OS'],'Browser'=>$comment['Browser']));
			}
			if($questionInfo['pid']==0) // 父问题
			{
				$public = $questionInfo['hidden']==2?2:0;
				$complainInfo = array(
					'author'=>$questionInfo['author'],'author_id'=>$questionInfo['author_id'],'title'=>strip_tags($questionInfo['title']),'description'=>$description,'contact'=>$questionInfo['comment'],'qtype'=>$qtype['id'],'photo'=>trim($questionInfo['attach']),'time'=>$questionInfo['time'],'ip'=>$questionInfo['ip'],'jid'=>$qtype['complain_type_id'],'jname'=>$qtype['name'],'comment'=>$convert,'public'=>$public,'order_id'=>$comment['order_id']
				);
				$complainInfo1 = array('public'=>0,'sync'=>0,'assess'=>0,'astime'=>0,'asnum'=>0,'status'=>0,'comment'=>$convert,
						'qtype'=>$qtype['id'],'jid'=>$qtype['complain_type_id'],'jname'=>$qtype['name'],'public'=>$public,'order_id'=>$comment['order_id']);
			}
			else // 子问题
			{
				$parentInfo = $this->Get($questionInfo['pid']);
				$public = $parentInfo['hidden']==2?2:0;
				$qtypeParent = $_ENV['qtype']->GetQType($parentInfo['qtype']);
				if( $questionInfo['qtype'] >0) //  如果子问题有自己的qtype 就用自己的
				{					
					$complainInfo = array(
							'author'=>$questionInfo['author'],'author_id'=>$questionInfo['author_id'],'title'=>strip_tags($questionInfo['title']),'description'=>$description,'contact'=>$parentInfo['comment'],'qtype'=>$qtype['id'],'time'=>$questionInfo['time'],'ip'=>$questionInfo['ip'],'jid'=>$qtype['complain_type_id'],'jname'=>$qtype['name'],'comment'=>$convert,'public'=>$public,'order_id'=>$comment['order_id']
					);
					$complainInfo1 = array('public'=>0,'sync'=>0,'assess'=>0,'astime'=>0,'asnum'=>0,'status'=>0,'comment'=>$convert,'qtype'=>$qtype['id'],'jid'=>$qtype['complain_type_id'],'jname'=>$qtype['name'],'public'=>$public,'order_id'=>$comment['order_id']);
				}
				else // 拿父问题的qype jid jname
				{
					$public = $questionInfo['hidden']==2?2:0;
					$complainInfo = array(
							'author'=>$questionInfo['author'],'author_id'=>$questionInfo['author_id'],'title'=>strip_tags($questionInfo['title']),'description'=>$description,'contact'=>$parentInfo['comment'],'qtype'=>$qtypeParent['id'],'time'=>$questionInfo['time'],'ip'=>$questionInfo['ip'],'jid'=>$qtypeParent['complain_type_id'],'jname'=>$qtypeParent['name'],'comment'=>$convert,'public'=>$public,'order_id'=>$comment['order_id']
					);
					$complainInfo1 = array('public'=>0,'sync'=>0,'assess'=>0,'astime'=>0,'asnum'=>0,'status'=>0,'comment'=>$convert,'qtype'=>$qtypeParent['id'],'jid'=>$qtypeParent['complain_type_id'],'jname'=>$qtypeParent['name'],'public'=>$public,'order_id'=>$comment['order_id']);
				}
			}
			//如果已经由投诉转来
			if(intval($comment['convert']['from_id'])>0)
			{
				$this->db->begin(); // 开启事务
				
				$complainId = $comment['convert']['from_id'];
				// 显示投诉问题,更新 comment字段,to_type 改为from_type
				$_ENV['complain']->updateComplain( $complainId, $complainInfo1 );
				$updateComplainNum = $this->db->affected_rows();
				
				$comment['convert'] = array('to_type'=>$to_type,'to_id'=>$complainId);
				
				// 更新问题 comment字段from_type 改为to_type
				$this->updateQuestion( $question_id, array('comment'=>serialize($comment)));
				$updateQuestionNum =  $this->db->affected_rows();
				
				$resultData = $this->askTransformComplainAnswer( $questionInfo ); // 减接手客服单量
				
				if( $questionInfo['help_status'] == 1)
				{
					$_ENV['help']->updateHelp($question_id,array('display'=>1)); //隐藏协助处理单
				}
				
				if($updateComplainNum>0 && $updateQuestionNum>0 && $resultData>0)
				{
					$_ENV['answer']->deleteAnswerByQid($complainId);
					$this->db->commit();
					return $complainId;
				}
				else
				{
					$this->db->rollback();
					return false;
				}
				
			}
			else // 第一次转为投诉，插入一条新纪录到投诉表			
			{   
				$this->db->begin(); // 开启事务
				$_ENV['complain']->insertComplain( $complainInfo );
				$complain_id = $this->db->insert_id();
				//如果成功复制投诉数据
				if($complain_id>0)
				{
					if( $questionInfo['help_status'] == 1)
					{
						$_ENV['help']->updateHelp($question_id,array('display'=>1)); //协助处理单隐藏
						$updateHelpNum = $this->db->affected_rows();
					}
					//更新关联投诉ID到咨询建议表
					$comment['convert'] = array('to_type'=>$to_type,'to_id'=>$complain_id,'reason'=>$reason);
					$cid = $this->getType(3);
					$updateInfo = array('cid'=>$cid,'comment'=>serialize($comment));
					
					$this->updateQuestion( $question_id, $updateInfo );
					$update_result = $this->db->affected_rows();
					
					$resultData = $this->askTransformComplainAnswer( $questionInfo ); // 咨询建议转投诉，减单量
					 
					//如果更新成功则提交
					if($update_result>0 && $resultData>0)
					{
						$this->db->commit();
						return $complain_id;
					}
					else
					{
						$this->db->rollback();
						return false;
					}
				}
				else
				{
					$this->db->rollback();
					return false;
				}
			}
        }
    }
    // 修改8大类问题数量
    function  modifyUserQtypeNum($date,$qtype,$questionType,$count)
    {
    	$date = strtotime($date)>0? $date:date("Y-m-d",time());
    	if($count>0)
    	{
    	    $sql = "INSERT INTO ask_question_num (date,qtype,question_type,questions)
    				 VALUES ('{$date}',$qtype,'{$questionType}',1)
    			ON DUPLICATE KEY UPDATE questions=questions+$count";
    	}
        else
    	{
            $sql = "update ask_question_num set questions = questions+($count) where date = '".$date."' and question_type = '".$questionType."' and qtype = ".$qtype;
        }
    	$this->db->query($sql);
    }
    function insertQuestion($questionInfo)
    {
    	foreach($questionInfo as $key => $value)
    	{
    		$array_key[$key] = $key;
    		$array_value[$key] = "'".$value."'";
    	}
    	$sql = "insert into " .DB_TABLEPRE."question (".implode($array_key,",").") values (".implode($array_value,",").")";
    	return $this->db->query($sql);
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
	function updateSubQuestion($id,$questionInfo)
	{
		foreach($questionInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."question set ".implode($txt,",")." where pid = ".intval($id);		
		return $this->db->query($sql);
	}
	function updateQuestionGame($id,$GameInfo)
	{
		$this->db->begin();
		$updateQuestion = $this->updateQuestion($id,$GameInfo);
		$updateSubQuestion = $this->updateSubQuestion($id,$GameInfo);
		if($updateQuestion || $updateSubQuestion)
		{
			$this->db->commit();
			return true;
		}
		else
		{
			$this->db->rollback();
			return false;
		}
	}
	function insertTransformLog($logInfo)
	{
		foreach($logInfo as $key => $value)
		{
			$array_key[$key] = $key;
			$array_value[$key] = "'".$value."'";			
		}
		$sql = "insert into " .DB_TABLEPRE."complain_transform_log (".implode($array_key,",").") values (".implode($array_value,",").")";
		return $this->db->query($sql);
	}
	function updateTransformLog($id,$logInfo)
	{
		foreach($logInfo as $key => $value)
		{
			$txt[$key] = "`".$key."`='".$value."'";
		}
		$sql = "update ".DB_TABLEPRE."complain_transform_log set ".implode($txt,",")." where TransformLogId = ".intval($id);
		return $this->db->query($sql);
	}
	function getTransformLogByQuestion( $qid, $question_type)
	{
		$sql = "select * from ".DB_TABLEPRE."complain_transform_log where `from_id` = ".$qid." and `to_type` = '".$question_type."' order by applyTime desc limit 1";
		$sql = "select * from ".DB_TABLEPRE."complain_transform_log where `from_id` = ".$qid." order by applyTime desc ";
		return $this->db->fetch_all($sql);
	}
	function getTransformLogInProgess( $qid )
	{
		$sql = "select * from ".DB_TABLEPRE."complain_transform_log where `from_id` = ".$qid." and `transform_status` = 0 order by applyTime desc ";
		return $this->db->fetch_all($sql);
	}
	function getTransformLogById( $LogId)
	{
		$sql = "select * from ".DB_TABLEPRE."complain_transform_log where `TransformLogId` = $LogId";
		return $this->db->fetch_first($sql);
	}
	function getTransformLogList($ConditionList,$page,$pagesize)
	{
		$whereStartTime = $ConditionList['StartDate']?" applyTime >= ".strtotime($ConditionList['StartDate'])." ":"";
		$wherenEndTime = $ConditionList['EndDate']?" applyTime < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";
		$whereAuthor = $ConditionList['author']!=""?" AuthorName = '".$ConditionList['author']."' ":"";
		$whereApplyOperator = $ConditionList['ApplyOperator']!='0'?" ApplyOperator = '".$ConditionList['ApplyOperator']."' ":"";
		$whereAcceptOperator = $ConditionList['AcceptOperator']!='0'?($ConditionList['AcceptOperator']!='-1'?" AcceptOperator = '".$ConditionList['AcceptOperator']."' ":"AcceptOperator ='system'"):"";
		$whereStatus = $ConditionList['TransformStatus']!=-1?" transform_status = ".$ConditionList['TransformStatus']." ":"";
		$whereToType = $ConditionList['ToType']?" to_type = '".$ConditionList['ToType']."' ":"";
		$whereId = $ConditionList['QuestionId']!=0?" from_id = ".$ConditionList['QuestionId']." ":"";


		$whereCondition = array($whereStartTime,$whereEndTime,$whereStatus,$whereApplyOperator,$whereAcceptOperator,$whereAuthor,$whereId,$whereToType);
		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}				
		$count_sql = "select count(*) from " . DB_TABLEPRE . "complain_transform_log where 1 ".$where;
		$LogCount = $this->db->result_first($count_sql);
		if($LogCount>0)
		{
			$sql = "select * from " . DB_TABLEPRE . "complain_transform_log where 1 ".$where." order by applyTime desc";
			$limit = $page==0?"":" limit ".(($page-1)*$pagesize).",$pagesize";
			$sql.=$limit;
			$rs = $this->db->fetch_all($sql);
			$returnArr = array("LogCount"=>$LogCount,"LogList"=>$rs);
		}
		else
		{
			$returnArr = array("LogCount"=>0,"LogList"=>array());
		}
		return $returnArr;
	}
	// 获取所有要转成咨询或建议的数据
	function getSyncTransformLog($from_type, $limit)
	{
		$sql = "select * from ".DB_TABLEPRE."complain_transform_log where  `from_type` = '".$from_type."' and `transform_status`=0 order by transformTime desc limit $limit";
		return $this->db->fetch_all($sql);
	}
	// 减单量
	function askTransformComplainAnswer( $query )
	{
		if($query['js_kf'] != '')//接手客服不为空，新版
		{
			//接手此问题客服就是登陆客服
			if($query['js_kf'] == $this->base->ask_login_name)
			{
				//此问题还没有被回答
				if($query['status'] == 1)
				{
					//如果已经协助处理过了不减单量
					if($query['help_status'] == 0)
					{
						if($query['pid']==0)
						{
							//首问
							$sql = "UPDATE ".DB_TABLEPRE."author_num SET num = num-1 WHERE author='".$this->base->ask_login_name."'";
						}
						else
						{
							//追问
							$sql = "UPDATE ".DB_TABLEPRE."author_num SET num_add = num_add-1 WHERE author='".$this->base->ask_login_name."'" ;
						}
						$this->db->query ( $sql ); //更新客服的接单量
						$updateAuthor_numNum = $this->db->affected_rows();
												
						if ($updateAuthor_numNum>0)
						{
							$this->db->commit();
							return true;
						}
						else
						{
							$this->db->rollback();
							return false;
						}
					}
					else
					{
						return true;
					}
				}
				else
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		 }
		 else
		 {
		 	return true;
		 }
	}
    //撤销一个已分单但是未回答的提问的分配
    //$qid：提问ID
    function applyForAid($qid,$to_operator)
    {
		//获取客服信息
		$operatorInfo = $_ENV['operator']->get($to_operator);
		//客服存在
		if($operatorInfo['id'])
		{
			//客服可以接单且在班
			if(($operatorInfo['ishandle']==1) && ($operatorInfo['isonjob']==1) && ($operatorInfo['ishelp']==1))
			//首先将问题释放
			$cancel = $this->ApplyCancel($qid);
			if($cancel)
			{
				//释放成功,则强制分单
				$apply = $this->ApplyToOperator($qid,$operatorInfo['login_name'],true);
				return $apply;
			}
			else
			{
				//释放失败
				return false;
			}			
		}
		else
		{
			//客服不存在
			return false;
		}
    }
    function insertAidLog($aidInfo)
    {
    	foreach($aidInfo as $key => $value)
    	{
    		$array_key[$key] = $key;
    		$array_value[$key] = "'".$value."'";
    	}
    	$sql = "insert into " .DB_TABLEPRE."aid (".implode($array_key,",").") values (".implode($array_value,",").")";
    	return $this->db->query($sql);
    }
    function getUserInfo($author)
    {
    	if($author!='')
		{
			$url = 'http://usercenter.5173esb.com/service/GetUserInfo/'.trim($author);
			$return = file_get_contents($url);
			if($return)
			{
				$returnArr = json_decode($return,true);
			}
			else
			{
				$returnArr = false;
			}
		}
		else
		{
			$returnArr = false;
		}
		return $returnArr;
    }
    function getFCDInfo($Id,$Type)
    {
    	if($Id!='')
		{
			$url = 'http://lm.5173esb.com/Service/GetCategoryObject?type='.$Type.'&id='.$Id;
			$return = file_get_contents($url);
			if($return)
			{
				$returnArr = json_decode($return,true);
				$return = $returnArr['0']['Name'];
			}
			else
			{
				$return = false;
			}
		}
		else
		{
			$return = false;
		}
		return $return;
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
	}
	public function modifyQuestionCid($QuestionId,$CidArr,$OperatorName,$ComplainConvertSwitch,$TransformReason,$apply = 0)
	{
		//检测当前选项是否已到最下级
		$check = $_ENV['category']->check_cid($CidArr['cid'],$CidArr['cid1'],$CidArr['cid2'],$CidArr['cid3'],$CidArr['cid4']);
		if($check != true)
		{
			return array('result'=>0);
		}
		//获取问题内容
		$question_info = $this->Get($QuestionId);

		//原内容cid内容
		$quetsioncidInfo = $question_info['cid']?$_ENV['category']->get(intval($question_info['cid'])):array();
		//如果当前分类是投诉
		if($quetsioncidInfo['question_type'] == "complain")
		{
			return array('result'=>7,'comment'=>"不能对投诉单再进行处理！");
		}
		//原内容cid1内容
		$questioncid1Info =  $question_info['cid1']?$_ENV['category']->get(intval($question_info['cid1'])):array();
		//原内容cid2内容
		$questioncid2Info =  $question_info['cid2']?$_ENV['category']->get(intval($question_info['cid2'])):array();
		//原内容cid3内容
		$questioncid3Info =  $question_info['cid3']?$_ENV['category']->get(intval($question_info['cid3'])):array();
		//原内容cid4内容
		$questioncid4Info =  $question_info['cid4']? $_ENV['category']->get(intval($question_info['cid4'])):array();

		//转换后cid内容
		$cidInfo = $CidArr['cid']?$_ENV['category']->get($CidArr['cid']):array();
		//转换后cid1内容
		$cid1Info = $CidArr['cid1']?$_ENV['category']->get($CidArr['cid1']):array();		
		//转换后cid2内容
		$cid2Info = $CidArr['cid2']?$_ENV['category']->get($CidArr['cid2']):array();
		//转换后cid3内容
		$cid3Info = $CidArr['cid3']?$_ENV['category']->get($CidArr['cid3']):array();
		//转换后cid4内容
		$cid4Info = $CidArr['cid4']?$_ENV['category']->get($CidArr['cid4']):array();
		
		//如果目标分类是投诉
    	if($cidInfo['question_type'] == "complain" && $cidInfo['question_type']!=$quetsioncidInfo['question_type'])
    	{
			if(trim($TransformReason)=="")
			{
				return array('result'=>8);  // 需要填写理由
			}
			// 类型是投诉，咨询、建议转投诉开关未开;
    		if($ComplainConvertSwitch == 0)
    		{
				return array('result'=>2);  // 咨询、建议转投诉开关未开
    		}
			else
			{
				if($apply == 0)
				{
					//如果问题已回复
					if(in_array($question_info['status'],array(2,3)))
					{
						//回复的问题需要经过审批
						//加入待处理的转单记录
						$returnArr = array('result'=>6);					
						$returnArr['from_type']=$quetsioncidInfo['question_type'];
						$returnArr['to_type']=$cidInfo['question_type'];					
						return $returnArr;
					}
				}
			}		
    	}
		//开启事务
		$this->db->begin();		
		//同时更新qtype
		$CidArr['qtype'] = $cid1Info['qtype'];
		//更新问题
		if($question_info['cid']==$CidArr['cid'] && $question_info['cid1']==$CidArr['cid1'] && $question_info['cid2']==$CidArr['cid2'] && $question_info['cid3']==$CidArr['cid3'] && $question_info['cid4']==$CidArr['cid4'])
		{
			$update = 1;
		}
		else
		{
			$update = $this->updateQuestion($QuestionId,$CidArr);
		}
		
		if($cid1Info['id']&&$cidInfo['id'])
		{    		        		    
			//更改前后无变化
			if(($cidInfo['question_type']==$quetsioncidInfo['question_type'])&&($cid1Info['qtype']==$quetsioncidInfo['qtype']))
			{
			}
			else
			{
				//如果有变化 更新总问题量
				if($cid1Info['qtype']!=0 && $cidInfo['question_type'])
				{
					$newQtypeInfo = $_ENV['qtype']->GetQType($cid1Info['qtype']);
					$this->modifyUserQtypeNum(date("Y-m-d",$question_info['time']),$cid1Info['qtype'],$cidInfo['question_type'],1);
					if($newQtypeInfo['pid']>0)
					{
						$this->modifyUserQtypeNum(date("Y-m-d",$question_info['time']),$newQtypeInfo['pid'],$cidInfo['question_type'],1);        
					}
				}
				if($question_info['qtype']!=0 && $quetsioncidInfo['question_type'])
				{
					$oldQtypeInfo = $_ENV['qtype']->GetQType($question_info['qtype']);
					$this->modifyUserQtypeNum(date("Y-m-d",$question_info['time']),$question_info['qtype'],$quetsioncidInfo['question_type'],-1);    		                
					if($oldQtypeInfo['pid']>0)
					{
						$this->modifyUserQtypeNum(date("Y-m-d",$question_info['time']),$oldQtypeInfo['pid'],$quetsioncidInfo['question_type'],-1);        
					}
				}
			}
		}
		//如果更新成功，更新分类当日数据量
		if($update)
		{
			if($question_info['cid'] != $CidArr['cid'])
			{
				$_ENV['category']->modifyCategoryNum($CidArr['cid'],1);
				$_ENV['category']->modifyCategoryNum(intval($question_info['cid']),-1);    				
			}
			
			if($question_info['cid1'] != $CidArr['cid1'])
			{
				$_ENV['category']->modifyCategoryNum($CidArr['cid1'],1);
				$_ENV['category']->modifyCategoryNum(intval($question_info['cid1']),-1);    				
			}
			
			if($question_info['cid2'] != $CidArr['cid2'])
			{					
				$_ENV['category']->modifyCategoryNum($CidArr['cid2'],1);
				$_ENV['category']->modifyCategoryNum(intval($question_info['cid2']),-1);    				
			}
			
			if($question_info['cid3'] != $CidArr['cid3'])
			{
				$_ENV['category']->modifyCategoryNum($CidArr['cid3'],1);
				$_ENV['category']->modifyCategoryNum(intval($question_info['cid3']),-1);   				
			}
			
			if($question_info['cid4'] != $this->post['cid4'])
			{
				$_ENV['category']->modifyCategoryNum($CidArr['cid4'],1);
				$_ENV['category']->modifyCategoryNum(intval($question_info['cid4']),-1);   				
			}
			
			$message .= '';
			if($quetsioncidInfo['id']) $message .='-'.$quetsioncidInfo['name'].'-';
			if($questioncid1Info['id']) $message .='-'.$questioncid1Info['name'].'-';
			if($questioncid2Info['id']) $message .='-'.$questioncid2Info['name'].'-';
			if($questioncid3Info['id']) $message .='-'.$questioncid3Info['name'].'-';
			if($questioncid4Info['id']) $message .='-'.$questioncid4Info['name'].'-';
			$message .= '【修改为】';
			if($cidInfo['id']) $message .='-'.$cidInfo['name'].'-';
			if($cid1Info['id']) $message .='-'.$cid1Info['name'].'-';
			if($cid2Info['id']) $message .='-'.$cid2Info['name'].'-';
			if($cid3Info['id']) $message .='-'.$cid3Info['name'].'-';
			if($cid4Info['id']) $message .='-'.$cid4Info['name'].'-';
			$this->sys_admin_log($QuestionId,$OperatorName,$message,5);//系统操作日志			
			$this->db->commit();
			$returnArr = array('result'=>1);
			if($cidInfo['question_type'] == "complain" && $cidInfo['question_type']!=$quetsioncidInfo['question_type'])
			{
				$returnArr['from_type']=$quetsioncidInfo['question_type'];
				$returnArr['to_type']=$cidInfo['question_type'];
			}
			return $returnArr;
		}
		else
		{
			$this->db->rollBack();
			return array('result'=>0);
		}
	}
	//获取投诉列表
	function getResponseDay($ConditionList)
	{
		if($ConditionList['DepartmentId'])
		{
			$OperatorList = $_ENV['operator']->getByColumn('did',$ConditionList['DepartmentId'],1);
		}
		$O = array();
		foreach($OperatorList as $key => $OperatorInfo)
		{
			
			$O[] = $OperatorInfo['login_name'];
		}
		$DataArr  = array();
		$StartTime = strtotime($ConditionList['StartDate']);
		for($i=0;$i<24;$i++)
		{
			$DataArr[sprintf("%02d",$i)] = array('ReceiveCount'=>0,'TotalResponseTime'=>0,'AnsweredCount'=>0,'AverageResponseTime'=>0);
		}
		//查询列
		$select_fields = array('Hour'=>'from_unixtime(receive_time,"%H")','ReceiveCount'=>'count(1)','TotalResponseTime'=>'sum(if(atime>0,atime-receive_time,0))','AnsweredCount'=>'sum(if(status>1,1,0))','QtypeId'=>'qtype');
		$whereStartTime = $ConditionList['StartDate']?" receive_time >= ".strtotime($ConditionList['StartDate'])." ":"";
		$whereEndTime = $ConditionList['EndDate']?" receive_time < ".(strtotime($ConditionList['EndDate'])+86400)." ":"";
		$whereQtype = $ConditionList['QtypeId']?" qtype = ".$ConditionList['QtypeId']." ":"";
		if($ConditionList['DepartmentId'])
		{
			$t = array();
			if(count($OperatorList)>0)
			{
				foreach($OperatorList as $key => $OperatorInfo)
				{
					$t[] = "'".$OperatorInfo['login_name']."'";
					$OperatorListText = implode(",",$t);
					if($OperatorListText != "")
					{
						$WhereOperator = " js_kf in (".$OperatorListText.")";
					}
				}
			}
			else
			{
				$WhereOperator = " 0 ";
			}
		}
		else
		{
			$WhereOperator = "";
		}
		foreach($select_fields as $key => $value)
		{
			if(!is_int($key))
			{
				$select_fields[$key] = $value." as ".$key;
			}
		}
		$select_fields = implode(',',$select_fields);
		$whereCondition = array($whereStartTime,$whereEndTime,$whereQtype,$WhereOperator);

		foreach($whereCondition as $key => $value)
		{
			if(trim($value)=="")
			{
				unset($whereCondition[$key]);
			}
		}
		if(count($whereCondition)>0)
		{
			$where = "and ".implode(" and ",$whereCondition);
		}
		else
		{
			$where = "";
		}
		$sql = "select $select_fields from " . DB_TABLEPRE . "question where 1 ".$where." group by Hour,QtypeId order by Hour,QtypeId";
		$rs = $this->db->fetch_all($sql);
		foreach($rs as $key => $value)
		{
			$DataArr[$value['Hour']]['ReceiveCount'] += $value['ReceiveCount'];
			$DataArr[$value['Hour']]['TotalResponseTime'] += $value['TotalResponseTime'];
			$DataArr[$value['Hour']]['AnsweredCount'] += $value['AnsweredCount'];
			$DataArr[$value['Hour']]['QtypeDetail'][$value['QtypeId']]['ReceiveCount'] = $value['ReceiveCount'];
		}
		foreach($DataArr as $key => $value)
		{
			$DataArr[$key]['AverageResponseTime'] = $value['AnsweredCount']>0?sprintf("%10.2f",$value['TotalResponseTime']/$value['AnsweredCount']):0;
		}
		return $DataArr;
	}

}
?>
