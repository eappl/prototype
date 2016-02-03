<?php

!defined('IN_TIPASK') && exit('Access Denied');

class admin_exportcontrol extends base {

	var $c_arr = array();
	
    function admin_exportcontrol(& $get,& $post) {
        $this->base( & $get,& $post);
        $this->load("category");
        $this->load("tag");       
    }
    
    //导出标签
    function onexporttag(){
    	if($this->post['tag_add']){
    		set_time_limit(0);
    		$label = $this->db->fetch_all("SELECT * FROM i_label WHERE lid != 1");
    		foreach($label as $val){
    			$count = $this->db->fetch_total("tag","id='".$val['lid']."'");
    			if(!$count){
    				if($val['lfather'] == 1){
    					$this->db->query("insert into `ask_tag` (`id`, `letter`, `name`, `questions`, `displayorder`, `pid`)
    				values('".$val['lid']."',NULL,'".$val['lname']."','".$val['lquestionCount']."','0','0')");
    				}else{
    					$this->db->query("insert into `ask_tag` (`id`, `letter`, `name`, `questions`, `displayorder`, `pid`)
    				values('".$val['lid']."',NULL,'".$val['lname']."','".$val['lquestionCount']."','0','".$val['lfather']."')");
    				}
    				 
    			}
    		}
    		$success_info = "标签导入成功！";
    	}  	
    	@include template('atmpexport','admin');
    }
    
    //导出分类
    function onexportcategory(){
    	if($this->post['cat_add']){
    		set_time_limit(0);
    		$category = $this->db->fetch_all("SELECT * FROM i_class WHERE cid != 154");
    		foreach($category as $val){
    			$count = $this->db->fetch_total("category","id='".$val['cid']."'");
    			if(!$count){
    				if($val['cLevel'] == 1){
    					$this->db->query("INSERT INTO ask_category VALUES
    				('".$val['cid']."', '".$val['cname']."', 'default', '0', '".$val['cLevel']."', '0', '".$val['cquestionCount']."')");
    				}else{
    					$this->db->query("INSERT INTO ask_category VALUES
    				('".$val['cid']."', '".$val['cname']."', 'default', '".$val['cfather']."', '".$val['cLevel']."', '0', '".$val['cquestionCount']."')");
    				}
    				 
    			}
    		}
    		$success_info = "分类导入成功！";
       }  	
       @include template('atmpexport','admin');
    }
    
    //导出问题
    function onexportquestion(){
    	if($this->post['qustion_add']){
    		set_time_limit(0);
    		$start = intval($this->post['start']);
    		$end = intval($this->post['end']); 		
    		$question = $this->db->fetch_all("SELECT * FROM i_question LIMIT ".$start.",".$end);
    		foreach($question as $val){
    			$this->c_arr = array();
    			$cid = 0;
    			$cid1 = 0;
    			$cid2 = 0;
    			$cid3 = 0;
    			$cid4 = 0;
    			if($val['classid'] != 0){
    				$this->getcidtree($val['classid']);
    			}
    			$c_array = $this->c_arr;
    			if(!empty($c_array)){
    				$count = count($c_array);
    				if($count == 1){
    					$cid = $c_array[0];
    				}elseif($count == 2){
    					$cid = $c_array[0];
    					$cid1 = $c_array[1];
    				}elseif($count == 3){
    					$cid = $c_array[0];
    					$cid1 = $c_array[1];
    					$cid2 = $c_array[2];
    				}elseif($count == 4){
    					$cid = $c_array[0];
    					$cid1 = $c_array[1];
    					$cid2 = $c_array[2];
    					$cid3 = $c_array[3];
    				}elseif($count == 5){
    					$cid = $c_array[0];
    					$cid1 = $c_array[1];
    					$cid2 = $c_array[2];
    					$cid3 = $c_array[3];
    					$cid4 = $c_array[4];
    				}
    			}
    		
    			$tag_arr = array();
    			if(!empty($val['labelid'])){
    				$tag_arr = explode(',',$val['labelid']);
    				if(!empty($tag_arr)){
    					foreach($tag_arr as $k => &$v){
    						if($v == ''){
    							unset($tag_arr[$k]);
    						}
    					}
    				}
    				$tag_arr = array_values($tag_arr);
    			}
    			$gameid = '';
    			$all_game = $this->get_all_game();
    			if(!empty($val['gamename'])){
    				$gameid_key = array_search($val['gamename'],$all_game);
    				if($gameid_key ！== false){
    					$gameid = $gameid_key;
    				}
    			}
    		
    			$val['AswContent'] = str_replace("&nbsp;&nbsp;&nbsp;&nbsp;","<br/>",str_replace(" ", "&nbsp;", $val['AswContent']));
    		
    			$q_count = $this->db->fetch_total("question","id='".$val['qid']."'");
    			if(!$q_count){
    				$this->db->query("INSERT INTO `ask_question`(`id`,`cid`,`cid1`,`cid2`,`cid3`,`cid4`,`author`,`authorid`,`title`,`description`,`time`,`endtime`,`hidden`,`views`,`status`,`ip`,`revocation`,`rev_man`,`revocation_time`,`start_man`,`start_time`,`mark`,`pid`,`from`,`handle_status`,`tag`,`gameid`,`game_name`,`phone`,`attach`) VALUES
    				('".$val['qid']."',".$cid.",".$cid1.",".$cid2.",".$cid3.",".$cid4.",'".$val['poster']."',0,'".$val['qtitle']."','".$val['qcontent']."','".strtotime($val['postdatetime']).
    						"',0,1,'".$val['views']."',3,NULL,".$val['isCancel'].",'','".strtotime($val['cnlDatetime'])."','',0,".$val['QFlag'].",'".$val['ParentId']."',1,1,'".json_encode($tag_arr,true)."','".$gameid."','".$val['gamename']."','','')");
    			}
    		
    			$a_count = $this->db->fetch_total("answer","qid='".$val['qid']."'");
    			if(!$a_count){
    				$this->db->query("INSERT INTO `ask_answer`(`qid`,`title`,`author`,`authorid`,`time`,`content`,`status`,`support`,`against`,`isasses`,`isasses_time`,`is_delete`) VALUES
    					(".$val['qid'].",'','".$val['kfcharid']."',0,'".strtotime($val['AswDatetime'])."','".addslashes($val['AswContent'])."',1,'".$val['UseVote']."','".$val['NoUseVote']."',0,0,1)");
    			}
    			
    			if($val['ParentId'] == 0){
    				//更新Solr服务器
    				$q_search = array();
    				if(!empty($val['qid'])){
    					$q_search['id'] = $val['qid'];
    					$q_search['title'] = $val['qtitle'];
    					$q_search['description'] = $val['qcontent'];
    					$q_search['tag'] = json_encode($tag_arr,true);
    					if($val['AswDatetime'] != ''){
    						$q_search['time'] = strtotime($val['AswDatetime']);
    					}else{
    						$q_search['time'] = 0;
    					}
    					try{
    					    $this->set_search($q_search);
    					}catch (Exception $e){
						    echo $e->getMessage($val['qid']."未成功");
						}
    				}
    			}
    			
    			$success_info .= $val['qid'].'导入成功！'.'<br/>';
    		}
    		   		   		 
    		$success_info .= "问题导入成功！";
    	}
    	
    	@include template('atmpexport','admin');
    }
    
    function getcidtree($cid){
    	$this->c_arr[] = $cid;
    	$sql = "SELECT pid FROM ask_category WHERE id='".$cid."'";
    	$pid = $this->db->result_first($sql);
    	if($pid){   		
    		$this->getcidtree($pid);
    	}
    	sort($this->c_arr);
    }
   
}
?>
